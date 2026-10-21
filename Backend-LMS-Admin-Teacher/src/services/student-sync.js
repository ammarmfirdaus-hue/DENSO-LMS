const mysql = require('mysql2/promise');
const { sql, pool } = require('../config/database');
const env = require('../config/env');

let studentPool;
let running = false;
let inFlight = false;

async function syncOnce() {
  if (!running || env.STUDENT_SYNC_ENABLED !== 'true') return;
  const mssqlCourses = await pool.request().query(`SELECT course_code,title,description,category,status,updated_at FROM dbo.courses`);
  const mysqlConn = await studentPool.getConnection();
  try {
    await mysqlConn.beginTransaction();
    const upsertCourse = `INSERT INTO courses(course_code,title,description,category,status,updated_at)
      VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),description=VALUES(description),
      category=VALUES(category),status=VALUES(status),updated_at=VALUES(updated_at)`;
    for (const course of mssqlCourses.recordset) {
      await mysqlConn.execute(upsertCourse, [course.course_code, course.title, course.description || '',
        ['Wajib', 'Pilihan'].includes(course.category) ? course.category : 'Wajib', course.status === 'active' ? 'active' : 'inactive', course.updated_at]);
    }
    await mysqlConn.commit();
  } catch (error) { await mysqlConn.rollback(); throw error; }
  finally { mysqlConn.release(); }

  const [rows] = await studentPool.execute(`SELECT s.npk AS student_npk,c.course_code,e.progress,e.enrollment_status,e.updated_at
    FROM course_enrollments e JOIN student_accounts s ON s.student_id=e.student_id
    JOIN courses c ON c.course_id=e.course_id`);
  for (const row of rows) {
    await pool.request().input('npk', sql.VarChar(50), row.student_npk).input('code', sql.VarChar(30), row.course_code)
      .input('progress', sql.TinyInt, row.progress).input('status', sql.VarChar(20), row.enrollment_status)
      .input('updated', sql.DateTime2, row.updated_at)
      .query(`MERGE dbo.student_progress_snapshots AS target
        USING (SELECT @npk AS student_npk,@code AS course_code) AS source
        ON target.student_npk=source.student_npk AND target.course_code=source.course_code
        WHEN MATCHED AND target.source_updated_at<=@updated THEN UPDATE SET progress=@progress,enrollment_status=@status,source_updated_at=@updated,synced_at=SYSUTCDATETIME()
        WHEN NOT MATCHED THEN INSERT(student_npk,course_code,progress,enrollment_status,source_updated_at) VALUES(@npk,@code,@progress,@status,@updated);`);
  }
}

async function startStudentSync() {
  if (env.STUDENT_SYNC_ENABLED !== 'true') return () => {};
  if (!env.STUDENT_DB_PASSWORD) throw new Error('STUDENT_DB_PASSWORD is required when student sync is enabled.');
  studentPool = mysql.createPool({ host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT, database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD, connectionLimit: 4, charset: 'utf8mb4', decimalNumbers: true, timezone: 'Z' });
  await studentPool.query('SELECT 1');
  running = true;
  const tick = async () => {
    if (inFlight) return;
    inFlight = true;
    try { await syncOnce(); }
    catch (error) { console.error('Student database sync failed:', error.message); }
    finally { inFlight = false; }
  };
  await tick();
  const timer = setInterval(tick, env.STUDENT_SYNC_INTERVAL_MS);
  return async () => {
    running = false;
    clearInterval(timer);
    while (inFlight) await new Promise(resolve => setTimeout(resolve, 50));
    await studentPool.end();
  };
}

module.exports = { startStudentSync };
