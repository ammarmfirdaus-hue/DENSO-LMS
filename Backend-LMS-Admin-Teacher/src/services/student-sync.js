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
    const [courseRows] = await mysqlConn.query('SELECT course_id,course_code FROM courses');
    const courseIds = new Map(courseRows.map(course => [course.course_code, course.course_id]));
    const sourceCourseCodes = mssqlCourses.recordset.map(course => course.course_code);
    if (sourceCourseCodes.length) {
      await mysqlConn.execute(`UPDATE courses SET status='inactive' WHERE course_code NOT IN (${sourceCourseCodes.map(() => '?').join(',')})`, sourceCourseCodes);
    } else {
      await mysqlConn.execute("UPDATE courses SET status='inactive'");
    }
    const sessionsToSync = await pool.request().query(`SELECT s.session_id AS source_session_id,s.title,s.sort_order,s.status,c.course_code
      FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id ORDER BY c.course_code,s.sort_order,s.session_id`);
    for (const session of sessionsToSync.recordset) {
      const courseId = courseIds.get(session.course_code);
      if (!courseId) continue;
      await mysqlConn.execute(`INSERT INTO course_sessions(source_session_id,course_id,title,sort_order,status)
        VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE course_id=VALUES(course_id),title=VALUES(title),sort_order=VALUES(sort_order),status=VALUES(status)`,
      [session.source_session_id, courseId, session.title, session.sort_order, session.status === 'active' ? 'active' : 'inactive']);
    }
    const [sessionRows] = await mysqlConn.query('SELECT session_id,source_session_id FROM course_sessions');
    const sessionIds = new Map(sessionRows.map(session => [String(session.source_session_id), session.session_id]));
    const publishedMaterials = await pool.request().query(`SELECT s.session_id AS source_session_id,s.title AS session_title,
        s.sort_order AS session_sort,s.status AS session_status,c.course_code,m.material_id AS source_material_id,m.title AS material_title,
        m.material_type,m.content,m.file_path,m.sort_order AS material_sort
      FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id
      JOIN dbo.course_materials m ON m.session_id=s.session_id ORDER BY c.course_code,s.sort_order,s.session_id,m.sort_order,m.material_id`);
    for (const row of publishedMaterials.recordset) {
      const courseId = courseIds.get(row.course_code);
      const studentSessionId = sessionIds.get(String(row.source_session_id));
      if (!courseId || !studentSessionId) continue;
      const publicApiUrl = env.STUDENT_PUBLIC_API_URL.replace(/\/+$/, '');
      const fileUrl = row.file_path ? `${publicApiUrl}/material-workflow/published/${row.source_material_id}/file` : null;
      await mysqlConn.execute(`INSERT INTO course_materials(source_material_id,session_id,title,material_type,content,file_path,sort_order)
        VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE session_id=VALUES(session_id),title=VALUES(title),material_type=VALUES(material_type),
        content=VALUES(content),file_path=VALUES(file_path),sort_order=VALUES(sort_order)`,
      [row.source_material_id, studentSessionId, row.material_title, row.material_type, row.content, fileUrl, row.material_sort]);
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

/**
 * Memulai timer sinkronisasi berkala antara SQL Server (Admin/Teacher) dan MySQL (Student).
 * Mengirim course/sesi/materi ke MySQL dan mengambil snapshot progres ke SQL Server.
 *
 * @returns {Promise<() => Promise<void>>} Fungsi shutdown untuk menghentikan timer dan menutup pool.
 */
async function startStudentSync() {
  if (env.STUDENT_SYNC_ENABLED !== 'true') return () => {};
  if (!env.STUDENT_DB_PASSWORD) throw new Error('STUDENT_DB_PASSWORD is required when student sync is enabled.');
  studentPool = mysql.createPool({ host: env.STUDENT_DB_HOST, port: env.STUDENT_DB_PORT, database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER, password: env.STUDENT_DB_PASSWORD, connectionLimit: 4, charset: 'utf8mb4', decimalNumbers: true, timezone: 'Z' });
  try {
    await studentPool.query('SELECT 1');
  } catch (error) {
    await studentPool.end().catch(() => {});
    studentPool = undefined;
    throw error;
  }
  running = true;
  const syncNow = async () => {
    if (!running || inFlight) return false;
    inFlight = true;
    try { await syncOnce(); return true; }
    finally { inFlight = false; }
  };
  const tick = async () => {
    try { await syncNow(); }
    catch (error) { console.error('Student database sync failed:', error.message); }
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

/**
 * Memicu sinkronisasi instan (on-demand), misalnya segera setelah materi baru dipublikasikan.
 *
 * @returns {Promise<boolean>} True jika sinkronisasi berhasil dijalankan, false jika dinonaktifkan/sedang berjalan.
 */
async function syncStudentNow() {
  if (!running || env.STUDENT_SYNC_ENABLED !== 'true' || inFlight) return false;
  inFlight = true;
  try { await syncOnce(); return true; }
  finally { inFlight = false; }
}

/**
 * Menghapus materi dari database MySQL student secara langsung saat materi dihapus dari LMS.
 * @param {number|string} sourceMaterialId ID material dari SQL Server
 * @returns {Promise<boolean>}
 */
async function deleteStudentMaterial(sourceMaterialId) {
  if (!studentPool || env.STUDENT_SYNC_ENABLED !== 'true') return false;
  try {
    await studentPool.execute('DELETE FROM course_materials WHERE source_material_id = ?', [Number(sourceMaterialId)]);
    return true;
  } catch (err) {
    console.error('Failed to delete student material from MySQL:', err.message);
    return false;
  }
}

module.exports = { startStudentSync, syncStudentNow, deleteStudentMaterial };
