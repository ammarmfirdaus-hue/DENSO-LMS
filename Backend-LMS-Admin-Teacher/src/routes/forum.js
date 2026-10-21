const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');

const router = express.Router();
router.use(authenticate, allowRoles('admin','teacher'));
router.get('/', async (req, res, next) => {
  try {
    const request = pool.request().input('isAdmin', sql.Bit, req.user.role === 'admin').input('teacher', sql.VarChar(50), req.user.sub);
    const result = await request.query(`SELECT TOP 100 thread_id,author_npk,course_code,title,body,status,created_at
      FROM dbo.forum_threads WHERE (@isAdmin=1 OR course_code IN (SELECT course_code FROM dbo.courses WHERE created_by=@teacher)) ORDER BY created_at DESC`);
    res.json({ threads: result.recordset });
  } catch (error) { next(error); }
});
router.patch('/:id/status', async (req, res, next) => {
  const id = Number(req.params.id);
  const parsed = z.object({ status: z.enum(['visible','hidden','locked']) }).safeParse(req.body);
  if (!Number.isSafeInteger(id) || id < 1 || !parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'ID atau status topik tidak valid.' } });
  try {
    const request = pool.request().input('id', sql.BigInt, id).input('status', sql.VarChar(20), parsed.data.status)
      .input('isAdmin', sql.Bit, req.user.role === 'admin').input('teacher', sql.VarChar(50), req.user.sub);
    const result = await request
      .query(`UPDATE dbo.forum_threads SET status=@status WHERE thread_id=@id AND (@isAdmin=1 OR course_code IN (SELECT course_code FROM dbo.courses WHERE created_by=@teacher)); SELECT @@ROWCOUNT AS affected`);
    if (!result.recordset[0]?.affected) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Topik tidak ditemukan.' } });
    res.json({ updated: true });
  } catch (error) { next(error); }
});

module.exports = router;
