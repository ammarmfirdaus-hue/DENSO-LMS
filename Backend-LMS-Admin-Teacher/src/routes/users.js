const express = require('express');
const bcrypt = require('bcryptjs');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');
const { createStudentAccount, listStudentAccounts, updateStudentAccount, getStudentAccountState, deleteStudentAccount,
  getStudentAccountForPromotion, promoteStudentAccountToTeacherShadow } = require('../services/student-auth');
const { sendAccountCredentials } = require('../services/account-mail');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));
router.get('/', async (req, res, next) => {
  try {
    const students = await listStudentAccounts();
    const employees = req.user.role === 'admin'
      ? (await pool.request().query(`SELECT employee_npk,fullname,email,role,status,department,position,phone_number,created_at FROM dbo.employee_accounts`)).recordset
      : [];
    const combined = [...employees, ...students].sort((a, b) => String(a.fullname).localeCompare(String(b.fullname)));
    const page = paginateCollection(req, combined);
    res.json({ users: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

const userUpdateSchema = z.object({
  fullname: z.string().trim().min(2).max(150),
  email: z.string().trim().email().max(254),
  role: z.enum(['student', 'admin', 'teacher', 'reviewer']),
  status: z.enum(['active', 'inactive']),
  password: z.union([z.string().min(12).max(200), z.literal('')]).optional().default(''),
  sendEmail: z.boolean().optional().default(false)
}).strict();

router.put('/:npk', async (req, res, next) => {
  const npk = z.string().trim().min(1).max(50).safeParse(req.params.npk);
  const parsed = userUpdateSchema.safeParse(req.body);
  if (!npk.success || !parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Data akun tidak valid. Kata sandi baru minimal 12 karakter.' } });
  const data = parsed.data;
  if (req.user.role === 'teacher' && data.role !== 'student') return res.status(403).json({ error: { code: 'TEACHER_USER_SCOPE', message: 'Teacher hanya dapat mengubah data akun Student.' } });
  if (req.user.role === 'teacher' && data.sendEmail) return res.status(403).json({ error: { code: 'TEACHER_EMAIL_FORBIDDEN', message: 'Pengiriman data kredensial hanya dapat dilakukan Admin.' } });
  if (data.sendEmail && data.status !== 'active') return res.status(400).json({ error: { code: 'EMAIL_REQUIRES_ACTIVE_ACCOUNT', message: 'Email data akun hanya dapat dikirim untuk akun aktif.' } });
  if (npk.data === req.user.sub && (data.status !== 'active' || data.role !== 'admin')) {
    return res.status(409).json({ error: { code: 'CANNOT_DISABLE_SELF', message: 'Anda tidak dapat menonaktifkan atau mengubah role akun yang sedang digunakan.' } });
  }
  try {
    if (data.role === 'student') {
      const studentState = data.sendEmail ? await getStudentAccountState(npk.data) : null;
      if (data.sendEmail && studentState?.status !== 'active') return res.status(400).json({ error: { code: 'EMAIL_REQUIRES_ACTIVE_ACCOUNT', message: 'Email data akun hanya dapat dikirim untuk akun aktif.' } });
      if (data.sendEmail && !data.password && !studentState?.hasPassword) {
        return res.status(400).json({ error: { code: 'PASSWORD_REQUIRED', message: 'Akun belum memiliki kata sandi. Isi kata sandi baru sebelum mengirim email.' } });
      }
      const hash = data.password ? await bcrypt.hash(data.password, 12) : null;
      const updated = await updateStudentAccount(npk.data, { fullname: data.fullname, email: data.email, status: data.status, passwordHash: hash });
      if (!updated) return res.status(404).json({ error: { code: 'USER_NOT_FOUND', message: 'Pengguna Student tidak ditemukan.' } });
      const email = data.sendEmail ? await sendCredentialsEmail({ ...data, npk: npk.data, role: 'student' }) : { sent: false };
      return res.json({ user: { employee_npk: npk.data, fullname: data.fullname, email: data.email, role: 'student', status: data.status }, email });
    }
    const current = await pool.request().input('npk', sql.VarChar(50), npk.data)
      .query(`SELECT role,status,password_hash FROM dbo.employee_accounts WHERE employee_npk=@npk`);
    const previous = current.recordset[0];
    if (!previous && req.user.role === 'admin' && data.role === 'teacher') {
      const student = await getStudentAccountForPromotion(npk.data);
      if (!student) return res.status(404).json({ error: { code: 'USER_NOT_FOUND', message: 'Akun Student tidak ditemukan untuk dipromosikan.' } });
      if (data.sendEmail && !data.password) return res.status(400).json({ error: { code: 'PASSWORD_REQUIRED', message: 'Isi kata sandi baru agar email kredensial Teacher berisi kata sandi yang dapat digunakan.' } });
      const passwordHash = data.password ? await bcrypt.hash(data.password, 12) : student.password_hash;
      const tx = new sql.Transaction(pool);
      await tx.begin(sql.ISOLATION_LEVEL.SERIALIZABLE);
      try {
        const insert = await new sql.Request(tx).input('npk', sql.VarChar(50), npk.data)
          .input('name', sql.NVarChar(150), data.fullname).input('email', sql.NVarChar(254), data.email)
          .input('hash', sql.VarChar(255), passwordHash).input('status', sql.VarChar(20), data.status)
          .query(`INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role,status)
            OUTPUT INSERTED.employee_npk,INSERTED.fullname,INSERTED.email,INSERTED.role,INSERTED.status
            VALUES(@npk,@name,@email,@hash,'teacher',@status)`);
        await tx.commit();
        const converted = await promoteStudentAccountToTeacherShadow(npk.data, {
          fullname: data.fullname, email: data.email, passwordHash, status: data.status
        });
        if (!converted) {
          await pool.request().input('npk', sql.VarChar(50), npk.data)
            .query(`DELETE FROM dbo.employee_accounts WHERE employee_npk=@npk AND role='teacher'`);
          return res.status(409).json({ error: { code: 'PROMOTION_CONFLICT', message: 'Akun Student berubah saat promosi berlangsung. Tidak ada role yang diubah; muat ulang data lalu coba kembali.' } });
        }
        const user = insert.recordset[0];
        const email = data.sendEmail ? await sendCredentialsEmail({ ...data, npk: npk.data, role: 'teacher' }) : { sent: false };
        return res.json({ user, email, promotedFrom: 'student' });
      } catch (error) {
        await tx.rollback().catch(() => {});
        // The two databases do not share a transaction. Compensate the SQL Server
        // insert if the MySQL identity conversion failed after the insert committed.
        await pool.request().input('npk', sql.VarChar(50), npk.data)
          .query(`DELETE FROM dbo.employee_accounts WHERE employee_npk=@npk AND role='teacher'`)
          .catch(() => {});
        throw error;
      }
    }
    if (!previous) return res.status(404).json({ error: { code: 'USER_NOT_FOUND', message: 'Pengguna tidak ditemukan.' } });
    if (data.sendEmail && !data.password && !previous.password_hash) return res.status(400).json({ error: { code: 'PASSWORD_REQUIRED', message: 'Akun belum memiliki kata sandi. Isi kata sandi baru sebelum mengirim email.' } });
    if (previous.role === 'reviewer' && previous.status === 'active' && (data.role !== 'reviewer' || data.status !== 'active')) {
      const pending = await pool.request().input('npk', sql.VarChar(50), npk.data)
        .query(`SELECT TOP 1 1 AS has_pending FROM dbo.material_submissions WHERE reviewer_npk=@npk AND status='pending_review'`);
      if (pending.recordset.length) return res.status(409).json({ error: { code: 'REVIEWER_HAS_PENDING_WORK', message: 'Reviewer masih memiliki materi yang menunggu review. Selesaikan atau alihkan tugas tersebut terlebih dahulu.' } });
    }
    if (previous.role === 'admin' && previous.status === 'active' && (data.role !== 'admin' || data.status !== 'active')) {
      const admins = await pool.request().query(`SELECT COUNT(*) AS total FROM dbo.employee_accounts WHERE role='admin' AND status='active'`);
      if (admins.recordset[0].total <= 1) return res.status(409).json({ error: { code: 'LAST_ADMIN', message: 'Role atau status Admin aktif terakhir tidak dapat diubah.' } });
    }
    const hash = data.password ? await bcrypt.hash(data.password, 12) : null;
    const result = await pool.request().input('npk', sql.VarChar(50), npk.data)
      .input('name', sql.NVarChar(150), data.fullname).input('email', sql.NVarChar(254), data.email)
      .input('role', sql.VarChar(20), data.role).input('status', sql.VarChar(20), data.status)
      .input('hasPassword', sql.Bit, Boolean(hash)).input('hash', sql.VarChar(255), hash)
      .query(`UPDATE dbo.employee_accounts SET fullname=@name,email=@email,role=@role,status=@status,
        password_hash=CASE WHEN @hasPassword=1 THEN @hash ELSE password_hash END,updated_at=SYSUTCDATETIME()
        OUTPUT INSERTED.employee_npk,INSERTED.fullname,INSERTED.email,INSERTED.role,INSERTED.status
        WHERE employee_npk=@npk;`);
    const user = result.recordset[0];
    if (!user) return res.status(404).json({ error: { code: 'USER_NOT_FOUND', message: 'Pengguna tidak ditemukan.' } });
    const email = data.sendEmail ? await sendCredentialsEmail({ ...data, npk: npk.data }) : { sent: false };
    res.json({ user, email });
  } catch (error) {
    if (error.number === 2627 || error.number === 2601 || error.code === 'ER_DUP_ENTRY') error.status = 409;
    next(error);
  }
});

// Permanently remove accounts only when their history/relations do not block deletion.
router.delete('/:npk', async (req, res, next) => {
  const npk = z.string().trim().min(1).max(50).safeParse(req.params.npk);
  if (!npk.success) return res.status(400).json({ error: { code: 'INVALID_NPK', message: 'NPK tidak valid.' } });
  if (npk.data === req.user.sub) return res.status(409).json({ error: { code: 'CANNOT_DISABLE_SELF', message: 'Anda tidak dapat menghapus akun yang sedang digunakan.' } });
  try {
    if (await deleteStudentAccount(npk.data)) return res.json({ deleted: true, deactivated: false });
    if (req.user.role === 'teacher') return res.status(404).json({ error: { code: 'STUDENT_NOT_FOUND', message: 'Akun Student tidak ditemukan.' } });
    const tx = new sql.Transaction(pool);
    await tx.begin(sql.ISOLATION_LEVEL.SERIALIZABLE);
    try {
      const request = new sql.Request(tx).input('npk', sql.VarChar(50), npk.data);
      const target = await request.query(`SELECT role,status FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE employee_npk=@npk`);
      if (!target.recordset.length) { await tx.rollback(); return res.status(404).json({ error: { code: 'USER_NOT_FOUND', message: 'Pengguna tidak ditemukan.' } }); }
      if (target.recordset[0].role === 'admin' && target.recordset[0].status === 'active') {
        const admins = await new sql.Request(tx).query(`SELECT COUNT(*) AS total FROM dbo.employee_accounts WITH (UPDLOCK,HOLDLOCK) WHERE role='admin' AND status='active'`);
        if (admins.recordset[0].total <= 1) { await tx.rollback(); return res.status(409).json({ error: { code: 'LAST_ADMIN', message: 'Admin aktif terakhir tidak dapat dinonaktifkan.' } }); }
      }
      if (target.recordset[0].role === 'reviewer') {
        const pending = await new sql.Request(tx).input('npk', sql.VarChar(50), npk.data)
          .query(`SELECT TOP 1 1 AS has_pending FROM dbo.material_submissions WHERE reviewer_npk=@npk AND status='pending_review'`);
        if (pending.recordset.length) { await tx.rollback(); return res.status(409).json({ error: { code: 'REVIEWER_HAS_PENDING_WORK', message: 'Reviewer masih memiliki materi yang menunggu review.' } }); }
      }
      await new sql.Request(tx).input('npk', sql.VarChar(50), npk.data)
        .query(`DELETE FROM dbo.employee_accounts WHERE employee_npk=@npk`);
      await tx.commit();
      res.json({ deleted: true, deactivated: false });
    } catch (error) { await tx.rollback().catch(() => {}); throw error; }
  } catch (error) {
    if (error.number === 547 || error.code === 'ER_ROW_IS_REFERENCED_2') {
      return res.status(409).json({ error: { code: 'USER_HAS_HISTORY', message: 'Akun memiliki riwayat atau data terkait. Gunakan Nonaktifkan agar data tersebut tetap tersimpan.' } });
    }
    next(error);
  }
});

router.post('/', allowRoles('admin'), async (req, res, next) => {
  const parsed = z.object({ npk: z.string().trim().min(1).max(50), fullname: z.string().trim().min(2).max(150),
    email: z.string().trim().email().max(254), password: z.string().min(12).max(200), role: z.enum(['student','admin','teacher','reviewer']),
    sendEmail: z.boolean().optional().default(false) }).safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Data akun tidak valid; kata sandi minimal 12 karakter.' } });
  try {
    const data = parsed.data;
    const hash = await bcrypt.hash(data.password, 12);
    if (data.role === 'student') {
      await createStudentAccount({ npk: data.npk, fullname: data.fullname, email: data.email, passwordHash: hash });
      const email = data.sendEmail ? await sendCredentialsEmail(data) : { sent: false };
      return res.status(201).json({ user: { npk: data.npk, fullname: data.fullname, email: data.email, role: 'student', status: 'active' }, email });
    }
    await pool.request().input('npk', sql.VarChar(50), data.npk).input('name', sql.NVarChar(150), data.fullname)
      .input('email', sql.NVarChar(254), data.email).input('hash', sql.VarChar(255), hash).input('role', sql.VarChar(20), data.role)
      .query(`INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role) VALUES(@npk,@name,@email,@hash,@role)`);
    const email = data.sendEmail ? await sendCredentialsEmail(data) : { sent: false };
    res.status(201).json({ user: { npk: data.npk, fullname: data.fullname, email: data.email, role: data.role }, email });
  } catch (error) { if (error.number === 2627 || error.number === 2601 || error.code === 'ER_DUP_ENTRY') error.status = 409; next(error); }
});

async function sendCredentialsEmail(data) {
  try {
    await sendAccountCredentials({ to: data.email, fullname: data.fullname, npk: data.npk, password: data.password, role: data.role });
    return { sent: true };
  } catch (error) {
    console.error('Account email delivery failed:', error.code || 'SMTP_ERROR');
    return { sent: false, reason: error.code === 'EMAIL_NOT_CONFIGURED' ? 'SMTP belum dikonfigurasi.' : 'Email gagal dikirim. Periksa konfigurasi SMTP.' };
  }
}

module.exports = router;
