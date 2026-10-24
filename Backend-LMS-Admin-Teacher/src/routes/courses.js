const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));
async function courseAccess(courseId, user) {
  const request = pool.request().input('id', sql.BigInt, courseId).input('npk', sql.VarChar(50), user.sub)
    .input('isAdmin', sql.Bit, user.role === 'admin');
  const result = await request.query('SELECT 1 AS allowed FROM dbo.courses WHERE course_id=@id AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)');
  return result.recordset.length > 0;
}
const courseInput = z.object({ courseCode: z.string().trim().min(2).max(30), title: z.string().trim().min(2).max(200),
  description: z.string().trim().max(4000).default(''), category: z.enum(['Wajib', 'Pilihan']).default('Wajib'),
  trainerNpk: z.string().trim().max(50).optional().default('') });

router.get('/', async (req, res, next) => {
  try {
    const result = await pool.request()
      .input('npk', sql.VarChar(50), req.user.sub)
      .input('isAdmin', sql.Bit, req.user.role === 'admin')
      .query(`SELECT course_id,course_code,title,description,category,status,assigned_trainer_npk
        FROM dbo.courses
        WHERE status='active' AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)
        ORDER BY title`);
    const page = paginateCollection(req, result.recordset);
    res.json({ courses: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

router.post('/', allowRoles('admin', 'teacher'), async (req, res, next) => {
  const parsed = courseInput.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Periksa kode, judul, deskripsi, dan kategori course.' } });
  try {
    const { courseCode, title, description, category, trainerNpk } = parsed.data;
    if (trainerNpk && req.user.role !== 'admin') return res.status(403).json({ error: { code: 'ADMIN_REQUIRED', message: 'Hanya Admin yang dapat menugaskan trainer.' } });
    if (trainerNpk) {
      const trainer = await pool.request().input('npk', sql.VarChar(50), trainerNpk)
        .query(`SELECT 1 AS valid FROM dbo.employee_accounts WHERE employee_npk=@npk AND role='teacher' AND status='active'`);
      if (!trainer.recordset.length) return res.status(400).json({ error: { code: 'INVALID_TRAINER', message: 'Pilih akun trainer aktif.' } });
    }
    const result = await pool.request().input('code', sql.VarChar(30), courseCode).input('title', sql.NVarChar(200), title)
      .input('description', sql.NVarChar(4000), description).input('category', sql.VarChar(20), category)
      .input('creator', sql.VarChar(50), req.user.sub).input('trainer', sql.VarChar(50), trainerNpk || null)
      .query(`INSERT INTO dbo.courses(course_code,title,description,category,status,created_by,updated_by,assigned_trainer_npk)
              OUTPUT INSERTED.course_id, INSERTED.course_code, INSERTED.title VALUES(@code,@title,@description,@category,'active',@creator,@creator,@trainer);
              INSERT INTO dbo.activity_logs(employee_npk,activity_type,description)
              VALUES(@creator,'course_created',CONCAT(N'Membuat course ',@code));`);
    res.status(201).json({ course: result.recordset[0] });
  } catch (error) { if (error.number === 2627 || error.number === 2601) error.status = 409; next(error); }
});

router.put('/:id/trainer', allowRoles('admin'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  const input = z.object({ trainerNpk: z.string().trim().max(50).optional().default('') }).safeParse(req.body);
  if (!id.success || !input.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Pilih trainer yang valid.' } });
  try {
    const trainerNpk = input.data.trainerNpk || null;
    if (trainerNpk) {
      const trainer = await pool.request().input('npk', sql.VarChar(50), trainerNpk)
        .query(`SELECT 1 AS valid FROM dbo.employee_accounts WHERE employee_npk=@npk AND role='teacher' AND status='active'`);
      if (!trainer.recordset.length) return res.status(400).json({ error: { code: 'INVALID_TRAINER', message: 'Pilih akun trainer aktif.' } });
    }
    const result = await pool.request().input('id', sql.BigInt, id.data).input('trainer', sql.VarChar(50), trainerNpk)
      .query(`UPDATE dbo.courses SET assigned_trainer_npk=@trainer,updated_at=SYSUTCDATETIME() WHERE course_id=@id; SELECT @@ROWCOUNT AS affected`);
    if (!result.recordset[0]?.affected) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });
    res.json({ updated: true });
  } catch (error) { next(error); }
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
    const page = paginateCollection(req, result.recordset);
    res.json({ sessions: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

router.get('/:id/detail', async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID course tidak valid.' } });
  try {
    if (!(await courseAccess(id.data, req.user))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan atau tidak ditugaskan kepada Anda.' } });
    const courseResult = await pool.request().input('id', sql.BigInt, id.data)
      .query(`SELECT c.course_id,c.course_code,c.title,c.description,c.category,c.status,c.created_at,c.updated_at,
        c.assigned_trainer_npk,trainer.fullname AS assigned_trainer_name,creator.fullname AS created_by_name
        FROM dbo.courses c LEFT JOIN dbo.employee_accounts trainer ON trainer.employee_npk=c.assigned_trainer_npk
        JOIN dbo.employee_accounts creator ON creator.employee_npk=c.created_by WHERE c.course_id=@id`);
    const course = courseResult.recordset[0];
    if (!course) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });

    const sessionsResult = await pool.request().input('id', sql.BigInt, id.data)
      .query(`SELECT session_id,title,sort_order,status,created_at FROM dbo.course_sessions WHERE course_id=@id ORDER BY sort_order,session_id`);
    const materialResult = await pool.request().input('id', sql.BigInt, id.data)
      .query(`SELECT m.material_id,m.session_id,m.title,m.material_type,m.content,m.file_path,m.sort_order,m.created_at,
        ms.submission_id,ms.original_file_name
        FROM dbo.course_materials m JOIN dbo.course_sessions s ON s.session_id=m.session_id
        LEFT JOIN dbo.material_submissions ms ON ms.published_material_id=m.material_id AND ms.status='published'
        WHERE s.course_id=@id ORDER BY s.sort_order,s.session_id,m.sort_order,m.material_id`);
    const submissionsResult = await pool.request().input('id', sql.BigInt, id.data)
      .input('npk', sql.VarChar(50), req.user.sub).input('isAdmin', sql.Bit, req.user.role === 'admin')
      .query(`SELECT ms.submission_id,ms.session_id,ms.title,ms.material_type,ms.content,ms.original_file_name,
        ms.status,ms.submitted_by,submitter.fullname AS submitter_name,ms.reviewer_npk,reviewer.fullname AS reviewer_name,
        ms.reviewer_comment,ms.admin_comment,ms.created_at,ms.published_material_id
        FROM dbo.material_submissions ms JOIN dbo.course_sessions s ON s.session_id=ms.session_id
        JOIN dbo.employee_accounts submitter ON submitter.employee_npk=ms.submitted_by
        LEFT JOIN dbo.employee_accounts reviewer ON reviewer.employee_npk=ms.reviewer_npk
        WHERE s.course_id=@id AND (@isAdmin=1 OR ms.submitted_by=@npk) ORDER BY ms.created_at DESC`);

    const sessions = sessionsResult.recordset.map(session => ({
      ...session,
      materials: materialResult.recordset.filter(material => material.session_id === session.session_id).map(material => ({
        material_id: material.material_id, title: material.title, material_type: material.material_type,
        content: material.content, sort_order: material.sort_order, created_at: material.created_at,
        original_file_name: material.original_file_name || null,
        fileUrl: material.file_path && material.submission_id ? `/material-workflow/published/${material.material_id}/file` : null
      }))
    }));
    res.json({ course, sessions, submissions: submissionsResult.recordset });
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
  return res.status(410).json({ error: { code: 'APPROVAL_REQUIRED', message: 'Pengajuan materi langsung ke course telah dinonaktifkan. Kirim materi melalui /material-workflow agar disetujui reviewer terlebih dahulu.' } });
});

module.exports = router;
