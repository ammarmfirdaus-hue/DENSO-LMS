const express = require('express');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));

router.get('/summary', async (req, res, next) => {
  try {
    const result = await pool.request().input('isAdmin', sql.Bit, req.user.role === 'admin').input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT (SELECT COUNT_BIG(*) FROM dbo.courses c WHERE c.status = 'active' AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)) AS active_courses,
                     CASE WHEN @isAdmin=1 THEN (SELECT COUNT_BIG(*) FROM dbo.employee_accounts WHERE status='active')
                       ELSE (SELECT COUNT_BIG(DISTINCT p.student_npk) FROM dbo.student_progress_snapshots p
                         JOIN dbo.courses c ON c.course_code=p.course_code WHERE c.created_by=@npk OR c.assigned_trainer_npk=@npk) END AS active_employees,
                     (SELECT COUNT_BIG(*) FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id
                       WHERE s.status='active' AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)) AS active_sessions`);
    res.json({ summary: result.recordset[0] });
  } catch (error) { next(error); }
});

router.get('/courses', async (_req, res, next) => {
  try {
    const request = pool.request().input('isAdmin', sql.Bit, _req.user.role === 'admin').input('npk', sql.VarChar(50), _req.user.sub);
    const result = await request.query(`SELECT c.course_id, c.course_code, c.title, c.description, c.category,c.assigned_trainer_npk,
      trainer.fullname AS assigned_trainer_name,
      c.status, c.updated_at, COUNT(DISTINCT s.session_id) AS session_count,
      COUNT(DISTINCT m.material_id) AS material_count
      FROM dbo.courses c LEFT JOIN dbo.course_sessions s ON s.course_id = c.course_id
      LEFT JOIN dbo.course_materials m ON m.session_id = s.session_id
      LEFT JOIN dbo.employee_accounts trainer ON trainer.employee_npk=c.assigned_trainer_npk
      WHERE (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)
      GROUP BY c.course_id, c.course_code, c.title, c.description, c.category,c.assigned_trainer_npk,trainer.fullname,c.status,c.updated_at
      ORDER BY c.updated_at DESC`);
    const page = paginateCollection(_req, result.recordset);
    res.json({ courses: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

router.get('/activity', async (_req, res, next) => {
  try {
    const request = pool.request().input('isAdmin', sql.Bit, _req.user.role === 'admin').input('npk', sql.VarChar(50), _req.user.sub);
    const result = await request.query(`SELECT TOP 10 activity_id, activity_type, description, created_at
      FROM dbo.activity_logs WHERE (@isAdmin=1 OR employee_npk=@npk) ORDER BY created_at DESC`);
    const page = paginateCollection(_req, result.recordset);
    res.json({ activities: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

module.exports = router;
