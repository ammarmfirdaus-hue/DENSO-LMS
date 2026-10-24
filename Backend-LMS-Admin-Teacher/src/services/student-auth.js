const bcrypt = require('bcryptjs');
const mysql = require('mysql2/promise');
const env = require('../config/env');

let pool;
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

async function authenticateStudent(npk, password) {
  const studentPool = getStudentPool();
  if (!studentPool) return null;

  const [rows] = await studentPool.execute(
    `SELECT student_id,npk,fullname,email,password_hash,status
     FROM student_accounts WHERE npk=? OR email=? LIMIT 1`,
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

async function studentHasCourseAccess(studentId, courseCode) {
  const studentPool = getStudentPool();
  if (!studentPool || !Number.isSafeInteger(Number(studentId)) || Number(studentId) < 1) return false;
  const [rows] = await studentPool.execute(
    `SELECT 1 FROM student_accounts s
     JOIN course_enrollments e ON e.student_id=s.student_id
     JOIN courses c ON c.course_id=e.course_id
     WHERE s.student_id=? AND c.course_code=? AND s.status='active'
       AND c.status='active' AND e.enrollment_status IN ('active','completed') LIMIT 1`,
    [Number(studentId), courseCode]
  );
  return rows.length > 0;
}

async function closeStudentAuthPool() {
  if (!pool) return;
  await pool.end();
  pool = null;
}

module.exports = { authenticateStudent, studentHasCourseAccess, closeStudentAuthPool };
