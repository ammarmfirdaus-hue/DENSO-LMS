const bcrypt = require('bcryptjs');
const mysql = require('mysql2/promise');
const crypto = require('node:crypto');
const env = require('../config/env');

let pool;
let managementPool;
const DUMMY_PASSWORD_HASH = bcrypt.hashSync('DLS-invalid-account-password', 12);

function getStudentPool() {
  if (env.STUDENT_AUTH_ENABLED !== 'true') return null;
  pool ||= mysql.createPool({
    host: env.STUDENT_DB_HOST,
    port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER,
    password: env.STUDENT_DB_PASSWORD,
    waitForConnections: true,
    connectionLimit: 3,
    queueLimit: 20,
    charset: 'utf8mb4',
    enableKeepAlive: true,
    decimalNumbers: true,
    timezone: 'Z'
  });
  return pool;
}

function getStudentManagementPool() {
  // A blank password is supported only by local development setups that use
  // passwordless MySQL. config/env.js enforces a strong password in production.
  managementPool ||= mysql.createPool({
    host: env.STUDENT_DB_HOST,
    port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER,
    password: env.STUDENT_DB_PASSWORD,
    waitForConnections: true,
    connectionLimit: 3,
    queueLimit: 30,
    charset: 'utf8mb4',
    enableKeepAlive: true,
    decimalNumbers: true,
    timezone: 'Z'
  });
  return managementPool;
}

async function authenticateStudent(npk, password) {
  const studentPool = getStudentPool();
  if (!studentPool) return null;

  const [rows] = await studentPool.execute(
    `SELECT student_id,npk,fullname,email,password_hash,status
     FROM student_accounts WHERE account_type='student' AND (npk=? OR email=?) LIMIT 1`,
    [npk, npk]
  );
  const student = rows[0];
  const passwordMatches = await bcrypt.compare(password, student?.password_hash || DUMMY_PASSWORD_HASH);
  if (!student || student.status !== 'active' || !passwordMatches) return null;

  return {
    employee_npk: student.npk,
    fullname: student.fullname,
    email: student.email,
    role: 'student',
    student_id: Number(student.student_id)
  };
}

async function isStudentSessionActive({ studentId, npk, mode = '', sourceRole = '' }) {
  const safeStudentId = Number(studentId);
  if (!Number.isSafeInteger(safeStudentId) || safeStudentId < 1 || !String(npk || '').trim()) return false;
  const isTeacherMode = mode === 'teacher_student' && sourceRole === 'teacher';
  if ((mode || sourceRole) && !isTeacherMode) return false;
  const accountType = isTeacherMode ? 'teacher_shadow' : 'student';
  const [rows] = await getStudentManagementPool().execute(
    "SELECT 1 FROM student_accounts WHERE student_id=? AND npk=? AND account_type=? AND status='active' LIMIT 1",
    [safeStudentId, String(npk), accountType]
  );
  return rows.length > 0;
}

async function studentHasCourseAccess(studentId, courseCode, accountType = 'student') {
  const studentPool = getStudentPool();
  if (!studentPool || !['student', 'teacher_shadow'].includes(accountType)
    || !Number.isSafeInteger(Number(studentId)) || Number(studentId) < 1) return false;
  const [rows] = await studentPool.execute(
    `SELECT 1 FROM student_accounts s
     JOIN course_enrollments e ON e.student_id=s.student_id
     JOIN courses c ON c.course_id=e.course_id
     WHERE s.student_id=? AND s.account_type=? AND c.course_code=? AND s.status='active'
       AND c.status='active' AND e.enrollment_status IN ('active','completed') LIMIT 1`,
    [Number(studentId), accountType, courseCode]
  );
  return rows.length > 0;
}

async function studentHasSessionAccess(studentId, courseCode, sessionCode, accountType = 'student') {
  const studentPool = getStudentPool();
  if (!studentPool || !['student', 'teacher_shadow'].includes(accountType)
    || !Number.isSafeInteger(Number(studentId)) || Number(studentId) < 1) return false;
  const [rows] = await studentPool.execute(
    `SELECT 1 FROM student_accounts s
     JOIN course_enrollments e ON e.student_id=s.student_id
     JOIN courses c ON c.course_id=e.course_id
     JOIN course_sessions cs ON cs.course_id=c.course_id
     WHERE s.student_id=? AND s.account_type=? AND c.course_code=? AND s.status='active' AND c.status='active'
       AND e.enrollment_status IN ('active','completed') AND cs.status='active'
       AND (CAST(cs.source_session_id AS CHAR)=? OR CAST(cs.session_id AS CHAR)=?) LIMIT 1`,
    [Number(studentId), accountType, courseCode, sessionCode, sessionCode]
  );
  return rows.length > 0;
}

async function getStudentIdentity(studentId) {
  const studentPool = getStudentPool();
  if (!studentPool || !Number.isSafeInteger(Number(studentId)) || Number(studentId) < 1) return null;
  const [rows] = await studentPool.execute(
    "SELECT npk,fullname FROM student_accounts WHERE student_id=? AND status='active' LIMIT 1",
    [Number(studentId)]
  );
  return rows[0] || null;
}

async function listActiveStudentRecipients(limit = 5001) {
  const studentPool = getStudentPool();
  if (!studentPool) {
    const error = new Error('Layanan database Student belum dikonfigurasi untuk mengirim email.');
    error.status = 503;
    throw error;
  }
  const safeLimit = Math.max(1, Math.min(5001, Number(limit) || 5001));
  const [rows] = await studentPool.query(
    `SELECT npk,fullname,email FROM student_accounts
     WHERE account_type='student' AND status='active' AND email IS NOT NULL AND TRIM(email)<>''
     ORDER BY student_id LIMIT ${safeLimit}`
  );
  return rows;
}

async function getActiveStudentRecipient(npk) {
  const studentPool = getStudentPool();
  if (!studentPool) {
    const error = new Error('Layanan database Student belum dikonfigurasi untuk mengirim notifikasi terarah.');
    error.status = 503;
    throw error;
  }
  const [rows] = await studentPool.execute(
    "SELECT npk,fullname,email FROM student_accounts WHERE npk=? AND account_type='student' AND status='active' LIMIT 1",
    [String(npk)]
  );
  return rows[0] || null;
}

async function createStudentAccount({ npk, fullname, email, passwordHash, status = 'active' }) {
  if (env.NODE_ENV === 'production' && !env.STUDENT_DB_PASSWORD) {
    const error = new Error('Kredensial database Student belum dikonfigurasi.');
    error.status = 503;
    throw error;
  }
  const connection = await mysql.createConnection({
    host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT, database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD,
    charset: 'utf8mb4', timezone: 'Z'
  });
  try {
    await connection.execute(
      `INSERT INTO student_accounts(npk,fullname,email,password_hash,status) VALUES(?,?,?,?,?)`,
      [npk, fullname, email, passwordHash, status]
    );
  } finally { await connection.end(); }
}

async function listStudentAccounts() {
  const connection = await mysql.createConnection({
    host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT, database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD,
    charset: 'utf8mb4', timezone: 'Z'
  });
  try {
    const [rows] = await connection.query(`SELECT npk AS employee_npk,fullname,email,'student' AS role,status,created_at
      FROM student_accounts WHERE account_type='student' ORDER BY fullname`);
    return rows;
  } finally { await connection.end(); }
}

async function updateStudentAccount(npk, { fullname, email, status, passwordHash }) {
  const connection = await mysql.createConnection({ host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME, user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD, charset: 'utf8mb4' });
  try {
    const [existing] = await connection.execute("SELECT student_id FROM student_accounts WHERE npk=? AND account_type='student' LIMIT 1", [npk]);
    if (!existing.length) return false;
    const [result] = await connection.execute(`UPDATE student_accounts SET fullname=?,email=?,status=?,
      password_hash=COALESCE(?,password_hash),updated_at=CURRENT_TIMESTAMP WHERE npk=? AND account_type='student'`,
    [fullname, email, status, passwordHash, npk]);
    return true;
  } finally { await connection.end(); }
}

async function getStudentAccountState(npk) {
  const connection = await mysql.createConnection({ host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME, user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD, charset: 'utf8mb4' });
  try {
    const [rows] = await connection.execute("SELECT status,password_hash,fullname,email FROM student_accounts WHERE npk=? AND account_type='student' LIMIT 1", [npk]);
    return rows[0] ? { status: rows[0].status, hasPassword: Boolean(rows[0].password_hash), fullname: rows[0].fullname, email: rows[0].email, role: 'student' } : null;
  } finally { await connection.end(); }
}

async function deactivateStudentAccount(npk) {
  const connection = await mysql.createConnection({ host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME, user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD, charset: 'utf8mb4' });
  try {
    const [result] = await connection.execute(`UPDATE student_accounts SET status='inactive',updated_at=CURRENT_TIMESTAMP WHERE npk=? AND account_type='student'`, [npk]);
    return result.affectedRows > 0;
  } finally { await connection.end(); }
}

async function deleteStudentAccount(npk) {
  const connection = await mysql.createConnection({ host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME, user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD, charset: 'utf8mb4' });
  try {
    const [result] = await connection.execute("DELETE FROM student_accounts WHERE npk=? AND account_type='student'", [npk]);
    return result.affectedRows > 0;
  } catch (error) {
    if (error.code === 'ER_ROW_IS_REFERENCED_2' || error.errno === 1451) {
      const blocked = new Error('Akun Student memiliki riwayat course, progres, atau aktivitas. Gunakan Nonaktifkan agar riwayat tetap tersimpan.');
      blocked.code = 'USER_HAS_HISTORY';
      throw blocked;
    }
    throw error;
  } finally { await connection.end(); }
}

async function getStudentAccountForPromotion(npk) {
  const [rows] = await getStudentManagementPool().execute(
    "SELECT student_id,npk,fullname,email,password_hash,status FROM student_accounts WHERE npk=? AND account_type='student' LIMIT 1",
    [npk]
  );
  return rows[0] || null;
}

async function promoteStudentAccountToTeacherShadow(npk, { fullname, email, passwordHash, status }) {
  const connection = await getStudentManagementPool().getConnection();
  try {
    await connection.beginTransaction();
    const [rows] = await connection.execute(
      "SELECT student_id,account_type FROM student_accounts WHERE npk=? FOR UPDATE",
      [npk]
    );
    if (!rows.length || rows[0].account_type !== 'student') {
      await connection.rollback();
      return false;
    }
    await connection.execute(
      `UPDATE student_accounts SET account_type='teacher_shadow',fullname=?,email=?,password_hash=?,status=?,updated_at=CURRENT_TIMESTAMP
       WHERE student_id=?`,
      [fullname, email, passwordHash, status, rows[0].student_id]
    );
    await connection.commit();
    return true;
  } catch (error) {
    await connection.rollback().catch(() => {});
    throw error;
  } finally { connection.release(); }
}

async function ensureTeacherShadowAccount({ npk, fullname }) {
  const connection = await getStudentManagementPool().getConnection();
  try {
    await connection.beginTransaction();
    const [rows] = await connection.execute(
      'SELECT student_id,account_type,status FROM student_accounts WHERE npk=? FOR UPDATE',
      [npk]
    );
    if (rows.length) {
      if (rows[0].account_type !== 'teacher_shadow') {
        await connection.rollback();
        const error = new Error('Akun Student dengan NPK ini perlu dipromosikan melalui Kelola Pengguna terlebih dahulu.');
        error.status = 409;
        error.code = 'STUDENT_PROMOTION_REQUIRED';
        throw error;
      }
      if (rows[0].status !== 'active') {
        await connection.rollback();
        const error = new Error('Akun belajar teacher tidak aktif pada database Student. Hubungi Admin untuk menyinkronkan akun.');
        error.status = 403;
        error.code = 'TEACHER_SHADOW_INACTIVE';
        throw error;
      }
      await connection.execute('UPDATE student_accounts SET fullname=?,updated_at=CURRENT_TIMESTAMP WHERE student_id=?', [fullname, rows[0].student_id]);
      await connection.commit();
      return Number(rows[0].student_id);
    }
    const stableId = crypto.createHash('sha256').update(String(npk).toLowerCase()).digest('hex').slice(0, 32);
    const email = `teacher-${stableId}@teacher-shadow.invalid`;
    const passwordHash = await bcrypt.hash(crypto.randomBytes(48).toString('base64url'), 12);
    const [result] = await connection.execute(
      `INSERT INTO student_accounts(npk,fullname,email,password_hash,status,account_type)
       VALUES(?,?,?,?,'active','teacher_shadow')`,
      [npk, fullname, email, passwordHash]
    );
    await connection.commit();
    return Number(result.insertId);
  } catch (error) {
    await connection.rollback().catch(() => {});
    throw error;
  } finally { connection.release(); }
}

async function closeStudentAuthPool() {
  const pools = [pool, managementPool].filter(Boolean);
  pool = null;
  managementPool = null;
  await Promise.all(pools.map(activePool => activePool.end()));
}

module.exports = { authenticateStudent, isStudentSessionActive, studentHasCourseAccess, studentHasSessionAccess, getStudentIdentity, listActiveStudentRecipients, getActiveStudentRecipient, createStudentAccount, listStudentAccounts, updateStudentAccount, getStudentAccountState, deactivateStudentAccount, deleteStudentAccount, getStudentAccountForPromotion, promoteStudentAccountToTeacherShadow, ensureTeacherShadowAccount, getStudentManagementPool, closeStudentAuthPool };
