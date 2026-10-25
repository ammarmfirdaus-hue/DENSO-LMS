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
    }))
  ];
  items.sort((left, right) => {
    const timeDelta = new Date(right.created_at).getTime() - new Date(left.created_at).getTime();
    if (timeDelta) return timeDelta;
    return String(right.activity_id).localeCompare(String(left.activity_id), undefined, { numeric: true });
  });
  return items.slice(0, safeLimit);
}

module.exports = { getActivityFeed };
