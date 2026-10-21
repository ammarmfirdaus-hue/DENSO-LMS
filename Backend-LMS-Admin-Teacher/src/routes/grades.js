const express = require('express');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');

const router = express.Router();
router.use(authenticate, allowRoles('admin','teacher'));
router.get('/', async (req, res, next) => {
  try {
    const request = pool.request().input('isAdmin', sql.Bit, req.user.role === 'admin').input('teacher', sql.VarChar(50), req.user.sub);
    const npk = String(req.query.npk || '').trim();
    const course = String(req.query.course || '').trim();
    let filter = '';
    if (npk) { request.input('npk', sql.VarChar(50), npk); filter += ' AND p.student_npk=@npk'; }
    if (course) { request.input('course', sql.VarChar(30), course); filter += ' AND p.course_code=@course'; }
    const result = await request.query(`SELECT TOP 500 p.student_npk,p.course_code,c.title AS course_title,
      p.progress,p.enrollment_status,p.source_updated_at,p.synced_at
      FROM dbo.student_progress_snapshots p LEFT JOIN dbo.courses c ON c.course_code=p.course_code
      WHERE 1=1 AND (@isAdmin=1 OR c.created_by=@teacher) ${filter} ORDER BY p.source_updated_at DESC`);
    res.json({ grades: result.recordset, source: 'student-mysql-sync' });
  } catch (error) { next(error); }
});

module.exports = router;
