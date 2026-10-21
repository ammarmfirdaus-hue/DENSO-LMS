const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));
async function courseAccess(courseId, user) {
  const request = pool.request().input('id', sql.BigInt, courseId).input('npk', sql.VarChar(50), user.sub)
    .input('isAdmin', sql.Bit, user.role === 'admin');
  const result = await request.query('SELECT 1 AS allowed FROM dbo.courses WHERE course_id=@id AND (@isAdmin=1 OR created_by=@npk)');
  return result.recordset.length > 0;
}
const courseInput = z.object({ courseCode: z.string().trim().min(2).max(30), title: z.string().trim().min(2).max(200),
  description: z.string().trim().max(4000).default(''), category: z.enum(['Wajib', 'Pilihan']).default('Wajib') });

router.post('/', allowRoles('admin', 'teacher'), async (req, res, next) => {
  const parsed = courseInput.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Periksa kode, judul, deskripsi, dan kategori course.' } });
  try {
    const { courseCode, title, description, category } = parsed.data;
    const result = await pool.request().input('code', sql.VarChar(30), courseCode).input('title', sql.NVarChar(200), title)
      .input('description', sql.NVarChar(4000), description).input('category', sql.VarChar(20), category)
      .input('creator', sql.VarChar(50), req.user.sub)
      .query(`INSERT INTO dbo.courses(course_code,title,description,category,status,created_by,updated_by)
              OUTPUT INSERTED.course_id, INSERTED.course_code, INSERTED.title VALUES(@code,@title,@description,@category,'active',@creator,@creator);
              INSERT INTO dbo.activity_logs(employee_npk,activity_type,description)
              VALUES(@creator,'course_created',CONCAT(N'Membuat course ',@code));`);
    res.status(201).json({ course: result.recordset[0] });
  } catch (error) { if (error.number === 2627 || error.number === 2601) error.status = 409; next(error); }
});

router.get('/:id/sessions', async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID course tidak valid.' } });
  try {
    if (!(await courseAccess(id.data, req.user))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });
    const result = await pool.request().input('id', sql.BigInt, id.data)
      .query(`SELECT s.session_id, s.title, s.sort_order, s.status, COUNT(m.material_id) AS material_count
        FROM dbo.course_sessions s LEFT JOIN dbo.course_materials m ON m.session_id=s.session_id
        WHERE s.course_id=@id GROUP BY s.session_id,s.title,s.sort_order,s.status ORDER BY s.sort_order,s.session_id`);
    res.json({ sessions: result.recordset });
  } catch (error) { next(error); }
});

router.post('/:id/sessions', async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  const input = z.object({ title: z.string().trim().min(2).max(200), sortOrder: z.number().int().min(0).max(10000).default(0) }).safeParse(req.body);
  if (!id.success || !input.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Data sesi tidak valid.' } });
  try {
    if (!(await courseAccess(id.data, req.user))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });
    const result = await pool.request().input('course', sql.BigInt, id.data).input('title', sql.NVarChar(200), input.data.title)
      .input('order', sql.Int, input.data.sortOrder)
      .query(`INSERT INTO dbo.course_sessions(course_id,title,sort_order) OUTPUT INSERTED.session_id,INSERTED.title VALUES(@course,@title,@order)`);
    res.status(201).json({ session: result.recordset[0] });
  } catch (error) { if (error.number === 547) error.status = 404; next(error); }
});

router.post('/sessions/:sessionId/materials', async (req, res, next) => {
  const sessionId = z.coerce.number().int().positive().safeParse(req.params.sessionId);
  const input = z.object({ title: z.string().trim().min(2).max(200), type: z.enum(['text','video','pdf','image','link']),
    content: z.string().max(20000).optional(), sortOrder: z.number().int().min(0).max(10000).default(0) }).safeParse(req.body);
  if (!sessionId.success || !input.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Data materi tidak valid.' } });
  try {
    const access = await pool.request().input('session', sql.BigInt, sessionId.data).input('npk', sql.VarChar(50), req.user.sub)
      .input('isAdmin', sql.Bit, req.user.role === 'admin')
      .query(`SELECT 1 AS allowed FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id
        WHERE s.session_id=@session AND (@isAdmin=1 OR c.created_by=@npk)`);
    if (!access.recordset.length) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi tidak ditemukan.' } });
    const item = input.data;
    const result = await pool.request().input('session', sql.BigInt, sessionId.data).input('title', sql.NVarChar(200), item.title)
      .input('type', sql.VarChar(20), item.type).input('content', sql.NVarChar(sql.MAX), item.content || null)
      .input('path', sql.NVarChar(500), null).input('order', sql.Int, item.sortOrder)
      .query(`INSERT INTO dbo.course_materials(session_id,title,material_type,content,file_path,sort_order)
        OUTPUT INSERTED.material_id,INSERTED.title,INSERTED.material_type VALUES(@session,@title,@type,@content,@path,@order)`);
    res.status(201).json({ material: result.recordset[0] });
  } catch (error) { if (error.number === 547) error.status = 404; next(error); }
});

module.exports = router;
