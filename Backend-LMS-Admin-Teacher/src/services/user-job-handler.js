const bcrypt = require('bcryptjs');
const { sql, pool } = require('../config/database');
const { createStudentAccount, updateStudentAccount, getStudentAccountState, deactivateStudentAccount, deleteStudentAccount } = require('./student-auth');
const { sendAccountCredentials } = require('./account-mail');

async function deactivateEmployeeWithHistory(npk) {
  const tx = new sql.Transaction(pool);
  await tx.begin(sql.ISOLATION_LEVEL.SERIALIZABLE);
  try {
    const target = await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
      .query(`SELECT role,status FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE employee_npk=@npk`);
    if (!target.recordset.length) throw new Error('Pengguna tidak ditemukan.');
    const { role, status } = target.recordset[0];
    if (role === 'admin' && status === 'active') {
      const admins = await new sql.Request(tx)
        .query(`SELECT COUNT(*) AS total FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE role='admin' AND status='active'`);
      if (admins.recordset[0].total <= 1) throw new Error('Admin aktif terakhir tidak dapat dinonaktifkan.');
    }
    await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
      .query(`UPDATE dbo.employee_accounts SET status='inactive',updated_at=SYSUTCDATETIME() WHERE employee_npk=@npk`);
    await tx.commit();
  } catch (error) {
    await tx.rollback().catch(() => {});
    throw error;
  }
}

function registerUserJobHandler(register) {
  register('users', async (item, context) => {
    const npk = String(item.npk || '').trim();
    if (item.action === 'email') {
      if (!item.password || item.password.length < 12) {
        throw new Error('Isi kata sandi baru minimal 12 karakter agar email memuat kata sandi yang dapat langsung digunakan.');
      }
      const passwordHash = await bcrypt.hash(item.password, 12);
      let account;
      if (item.role === 'student') account = await getStudentAccountState(npk);
      else {
        const result = await pool.request().input('npk', sql.VarChar(50), npk)
          .query(`SELECT fullname,email,role,status FROM dbo.employee_accounts WHERE employee_npk=@npk`);
        account = result.recordset[0] || null;
      }
      if (!account || account.status !== 'active') throw new Error('Akun aktif tidak ditemukan.');
      if (account.role !== item.role) throw new Error('Role akun berubah. Muat ulang daftar pengguna lalu coba lagi.');
      if (!account.email) throw new Error('Akun belum memiliki alamat email.');
      if (item.role === 'student') {
        const updated = await updateStudentAccount(npk, {
          fullname: account.fullname, email: account.email, status: 'active', passwordHash
        });
        if (!updated) throw new Error('Akun Student tidak ditemukan saat memperbarui kata sandi.');
      } else {
        const updated = await pool.request().input('npk', sql.VarChar(50), npk)
          .input('role', sql.VarChar(20), account.role).input('hash', sql.VarChar(255), passwordHash)
          .query(`UPDATE dbo.employee_accounts SET password_hash=@hash,updated_at=SYSUTCDATETIME()
            WHERE employee_npk=@npk AND role=@role AND status='active'`);
        if (updated.rowsAffected?.[0] === 0) throw new Error('Akun berubah atau nonaktif sebelum kata sandi diperbarui.');
      }
      await sendAccountCredentials({ to: account.email, fullname: account.fullname, npk, password: item.password, role: account.role });
      return 'Kata sandi diperbarui; NPK dan kata sandi baru terkirim ke email.';
    }
    if (item.action === 'deactivate') {
      if (npk === context.ownerNpk) throw new Error('Akun yang sedang digunakan tidak dapat dihapus.');
      if (item.role === 'student') {
        if (await deactivateStudentAccount(npk)) return 'Akun Student dinonaktifkan.';
        throw new Error('Akun Student tidak ditemukan.');
      }
      const tx = new sql.Transaction(pool);
      await tx.begin(sql.ISOLATION_LEVEL.SERIALIZABLE);
      try {
        const target = await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
          .query(`SELECT role,status FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE employee_npk=@npk`);
        if (!target.recordset.length) { await tx.rollback(); throw new Error('Pengguna tidak ditemukan atau sudah tidak aktif.'); }
        if (target.recordset[0].role === 'admin' && target.recordset[0].status === 'active') {
          const admins = await new sql.Request(tx).query(`SELECT COUNT(*) AS total FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE role='admin' AND status='active'`);
          if (admins.recordset[0].total <= 1) { await tx.rollback(); throw new Error('Admin aktif terakhir tidak dapat dinonaktifkan.'); }
        }
        await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
          .query(`UPDATE dbo.employee_accounts SET status='inactive',updated_at=SYSUTCDATETIME() WHERE employee_npk=@npk`);
        await tx.commit();
        return 'Akun dinonaktifkan; riwayat tetap disimpan.';
      } catch (error) { await tx.rollback().catch(() => {}); throw error; }
    }
    if (item.action === 'delete') {
      if (npk === context.ownerNpk) throw new Error('Akun yang sedang digunakan tidak dapat dihapus.');
      if (item.role === 'student') {
        try {
          if (await deleteStudentAccount(npk)) return 'Akun Student dihapus permanen.';
          throw new Error('Akun Student tidak ditemukan.');
        } catch (error) {
          if (error.code !== 'USER_HAS_HISTORY') throw error;
          await deactivateStudentAccount(npk);
          return 'Penghapusan permanen dilewati karena akun memiliki riwayat; akun Student dinonaktifkan dan riwayat tetap tersimpan.';
        }
      }
      const tx = new sql.Transaction(pool);
      await tx.begin(sql.ISOLATION_LEVEL.SERIALIZABLE);
      try {
        const target = await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
          .query(`SELECT role,status FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE employee_npk=@npk`);
        if (!target.recordset.length) { await tx.rollback(); throw new Error('Pengguna tidak ditemukan.'); }
        const { role, status } = target.recordset[0];
        if (role === 'admin' && status === 'active') {
          const admins = await new sql.Request(tx).query(`SELECT COUNT(*) AS total FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE role='admin' AND status='active'`);
          if (admins.recordset[0].total <= 1) { await tx.rollback(); throw new Error('Admin aktif terakhir tidak dapat dihapus.'); }
        }
        if (role === 'reviewer') {
          const pending = await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
            .query(`SELECT TOP 1 1 AS has_pending FROM dbo.material_submissions WHERE reviewer_npk=@npk AND status='pending_review'`);
          if (pending.recordset.length) { await tx.rollback(); throw new Error('Reviewer memiliki materi yang masih menunggu review. Selesaikan atau alihkan tugasnya terlebih dahulu.'); }
        }
        await new sql.Request(tx).input('npk', sql.VarChar(50), npk)
          .query(`DELETE FROM dbo.employee_accounts WHERE employee_npk=@npk`);
        await tx.commit();
        return 'Akun dihapus permanen.';
      } catch (error) {
        await tx.rollback().catch(() => {});
        if (error.number === 547) {
          await deactivateEmployeeWithHistory(npk);
          return 'Penghapusan permanen dilewati karena akun memiliki riwayat; akun dinonaktifkan dan riwayat tetap tersimpan.';
        }
        throw error;
      }
    }
    if (item.action !== 'upsert') throw new Error('Operasi pengguna tidak didukung.');

    const data = item;
    if (npk === context.ownerNpk && (data.status !== 'active' || data.role !== 'admin')) {
      throw new Error('Anda tidak dapat menonaktifkan atau mengubah role akun yang sedang digunakan.');
    }
    if (data.sendEmail && data.status !== 'active') throw new Error('Email data akun hanya dapat dikirim untuk akun aktif.');
    if (data.sendEmail && (!data.password || data.password.length < 12)) {
      throw new Error('Isi kata sandi baru minimal 12 karakter agar email memuat kata sandi yang dapat langsung digunakan.');
    }
    const passwordHash = data.password ? await bcrypt.hash(data.password, 12) : null;
    if (data.role === 'student') {
      const studentState = await getStudentAccountState(npk);
      const hasPassword = Boolean(studentState?.hasPassword);
      if (!hasPassword && !passwordHash) throw new Error('Akun baru atau akun tanpa kata sandi wajib memiliki password minimal 12 karakter.');
      const updated = await updateStudentAccount(npk, { fullname: data.fullname, email: data.email, status: data.status, passwordHash });
      if (!updated) await createStudentAccount({ npk, fullname: data.fullname, email: data.email, passwordHash, status: data.status });
    } else {
      const request = pool.request().input('npk', sql.VarChar(50), npk);
      const current = await request.query(`SELECT employee_npk,role,status,password_hash FROM dbo.employee_accounts WHERE employee_npk=@npk`);
      if (current.recordset.length) {
        const previous = current.recordset[0];
        if (data.sendEmail && !data.password && !previous.password_hash) throw new Error('Akun belum memiliki kata sandi. Isi kata sandi baru sebelum mengirim email.');
        if (previous.role === 'reviewer' && previous.status === 'active' && (data.role !== 'reviewer' || data.status !== 'active')) {
          const pending = await pool.request().input('npk', sql.VarChar(50), npk)
            .query(`SELECT TOP 1 1 AS has_pending FROM dbo.material_submissions WHERE reviewer_npk=@npk AND status='pending_review'`);
          if (pending.recordset.length) throw new Error('Reviewer memiliki materi yang masih menunggu review.');
        }
        if (previous.role === 'admin' && previous.status === 'active' && (data.role !== 'admin' || data.status !== 'active')) {
          const admins = await pool.request().query(`SELECT COUNT(*) AS total FROM dbo.employee_accounts WHERE role='admin' AND status='active'`);
          if (admins.recordset[0].total <= 1) throw new Error('Admin aktif terakhir tidak dapat dinonaktifkan atau diubah rolenya.');
        }
        await pool.request().input('npk', sql.VarChar(50), npk).input('name', sql.NVarChar(150), data.fullname)
          .input('email', sql.NVarChar(254), data.email).input('role', sql.VarChar(20), data.role)
          .input('status', sql.VarChar(20), data.status).input('hasPassword', sql.Bit, Boolean(passwordHash))
          .input('hash', sql.VarChar(255), passwordHash)
          .query(`UPDATE dbo.employee_accounts SET fullname=@name,email=@email,role=@role,status=@status,
            password_hash=CASE WHEN @hasPassword=1 THEN @hash ELSE password_hash END,updated_at=SYSUTCDATETIME() WHERE employee_npk=@npk`);
      } else {
        if (!passwordHash) throw new Error('Akun baru wajib memiliki password minimal 12 karakter.');
        await pool.request().input('npk', sql.VarChar(50), npk).input('name', sql.NVarChar(150), data.fullname)
          .input('email', sql.NVarChar(254), data.email).input('hash', sql.VarChar(255), passwordHash)
          .input('role', sql.VarChar(20), data.role).input('status', sql.VarChar(20), data.status)
          .query(`INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role,status)
            VALUES(@npk,@name,@email,@hash,@role,@status)`);
      }
    }
    if (data.sendEmail) await sendAccountCredentials({ to: data.email, fullname: data.fullname, npk, password: data.password, role: data.role });
    return data.sendEmail ? 'Akun disimpan dan email terkirim.' : 'Akun berhasil disimpan.';
  });
}

module.exports = { registerUserJobHandler };
