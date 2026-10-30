const { sql, pool } = require('../config/database');
const { listStudentActivityLogs } = require('./student-auth');

const MAX_ACTIVITY_ITEMS = 100;

async function getActivityFeed(role, limit = MAX_ACTIVITY_ITEMS) {
  const safeLimit = Math.max(1, Math.min(MAX_ACTIVITY_ITEMS, Number(limit) || MAX_ACTIVITY_ITEMS));
  const employeeQuery = pool.request()
    .input('role', sql.VarChar(20), role)
    .input('limit', sql.Int, safeLimit);
  const employeeResult = await employeeQuery.query(`SELECT TOP (@limit)
      a.activity_id, a.activity_type, a.description, a.created_at,
      a.employee_npk, e.fullname AS actor_name, COALESCE(e.role, 'system') AS actor_role,
      'employee' AS activity_source
    FROM dbo.activity_logs a
    LEFT JOIN dbo.employee_accounts e ON e.employee_npk=a.employee_npk
    WHERE @role='admin' OR e.role='teacher'
    ORDER BY a.created_at DESC, a.activity_id DESC`);

  let studentRows = [];
  try {
    studentRows = await listStudentActivityLogs(safeLimit);
  } catch (error) {
    // Keep the employee activity feed available during a temporary Student DB outage.
    console.warn('[activity-feed] Student activity source unavailable:', error.message);
  }

  let jobRows = [];
  try {
    const jobQuery = pool.request()
      .input('role', sql.VarChar(20), role)
      .input('limit', sql.Int, safeLimit);
    const jobResult = await jobQuery.query(`SELECT TOP (@limit)
        e.event_id, e.job_id, e.item_no, e.item_label, e.item_status, e.message, e.created_at,
        j.job_type, j.created_by AS employee_npk, j.created_by_role,
        COALESCE(emp.fullname, j.created_by) AS actor_name
      FROM dbo.lms_admin_job_events e
      JOIN dbo.lms_admin_jobs j ON j.job_id = e.job_id
      LEFT JOIN dbo.employee_accounts emp ON emp.employee_npk = j.created_by
      WHERE (@role = 'admin' OR j.created_by_role = 'teacher')
        AND e.item_status IN ('completed', 'failed', 'stopped')
      ORDER BY e.created_at DESC, e.event_id DESC`);

    jobRows = jobResult.recordset.map(row => {
      const isFailed = row.item_status === 'failed';
      const isStopped = row.item_status === 'stopped';
      const statusLabel = isFailed ? 'Gagal' : isStopped ? 'Dihentikan' : 'Berhasil';
      const jobTypeLabel = row.job_type === 'demo_account_email'
        ? 'Email Akun Demo'
        : row.job_type === 'users'
          ? 'Kelola Akun'
          : row.job_type === 'records_delete'
            ? 'Hapus Data Massal'
            : (row.job_type || 'Antrean');
      const detailMsg = row.message ? `: ${row.message}` : '';
      return {
        activity_id: `job-event-${row.event_id}`,
        activity_type: isFailed ? 'Job Gagal' : isStopped ? 'Job Dihentikan' : 'Job Berhasil',
        description: `Antrean #${row.job_id} (${jobTypeLabel}) · Item ${row.item_label} ${statusLabel}${detailMsg}`,
        created_at: row.created_at,
        employee_npk: row.employee_npk,
        actor_name: row.actor_name || 'System',
        actor_role: row.created_by_role || 'admin',
        activity_source: 'job',
        job_id: row.job_id,
        job_type: row.job_type,
        item_no: row.item_no,
        item_label: row.item_label,
        item_status: row.item_status,
        message: row.message
      };
    });
  } catch (error) {
    console.warn('[activity-feed] Job activity source unavailable:', error.message);
  }

  const items = [
    ...employeeResult.recordset.map(row => ({
      ...row,
      activity_id: `employee-${row.activity_id}`,
      activity_source: 'employee'
    })),
    ...studentRows.map(row => ({
      ...row,
      activity_id: `student-${row.activity_id}`,
      activity_source: 'student'
    })),
    ...jobRows
  ];
  items.sort((left, right) => {
    const timeDelta = new Date(right.created_at).getTime() - new Date(left.created_at).getTime();
    if (timeDelta) return timeDelta;
    return String(right.activity_id).localeCompare(String(left.activity_id), undefined, { numeric: true });
  });
  return items.slice(0, safeLimit);
}

module.exports = { getActivityFeed };
