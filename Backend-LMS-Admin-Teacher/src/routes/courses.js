const express = require('express');
const fs = require('node:fs/promises');
const path = require('node:path');
const crypto = require('node:crypto');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, requirePermission, requestHasPermission } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');
const { courseCoverUpload } = require('../middleware/forum-upload');
const { uploadLimiter } = require('../middleware/rate-limit');
const { parseImageBuffer } = require('../services/forum-image');
const { syncStudentNow } = require('../services/student-sync');

const router = express.Router();
const courseUploadDir = path.resolve(__dirname, '../../uploads/courses');
const THUMBNAIL_PATH_PREFIX = '/courses/thumbnails/';
const THUMBNAIL_FILENAME_REGEX = /^[0-9a-f-]{36}\.(jpg|png|gif|webp)$/i;

/**
 * Memastikan path file sampul yang disimpan berada di dalam direktori uploads/courses.
 */
function resolveStoredThumbnailPath(publicPath) {
  if (typeof publicPath !== 'string' || !publicPath.startsWith(THUMBNAIL_PATH_PREFIX)) return null;
  const filename = publicPath.slice(THUMBNAIL_PATH_PREFIX.length);
  if (!THUMBNAIL_FILENAME_REGEX.test(filename)) return null;
  const resolved = path.resolve(courseUploadDir, filename);
  return resolved.startsWith(`${courseUploadDir}${path.sep}`) ? resolved : null;
}

/**
 * Menyajikan file gambar sampul course aktif untuk portal Admin/Teacher maupun Native Student.
 * Dipasang sebelum middleware role admin/teacher agar tag <img> lintas portal dapat memuat sampul dengan aman.
 */
router.get('/thumbnails/:filename', async (req, res, next) => {
  const filename = z.string().regex(THUMBNAIL_FILENAME_REGEX).safeParse(req.params.filename);
  if (!filename.success) return res.sendStatus(404);
  try {
    const publicPath = `${THUMBNAIL_PATH_PREFIX}${filename.data}`;
    const found = await pool.request()
      .input('path', sql.NVarChar(500), publicPath)
      .query(`SELECT TOP 1 course_id FROM dbo.courses WHERE thumbnail_path=@path AND status='active'`);
    if (!found.recordset[0]) return res.sendStatus(404);
    const filePath = resolveStoredThumbnailPath(publicPath);
    if (!filePath) return res.sendStatus(404);
    await fs.access(filePath);
    res.set({
      'Cross-Origin-Resource-Policy': 'cross-origin',
      'Cache-Control': 'public, max-age=300',
      'X-Content-Type-Options': 'nosniff'
    });
    res.type(path.extname(filePath));
    res.sendFile(filePath, error => { if (error && !res.headersSent) next(error); });
  } catch (error) {
    if (error.code === 'ENOENT') return res.sendStatus(404);
    next(error);
  }
});

router.use(authenticate);

async function courseAccess(courseId, user) {
  const isWideScope = user.role !== 'teacher';
  const request = pool.request().input('id', sql.BigInt, courseId).input('npk', sql.VarChar(50), user.sub)
    .input('isAdmin', sql.Bit, isWideScope);
  const result = await request.query('SELECT 1 AS allowed FROM dbo.courses WHERE course_id=@id AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)');
  return result.recordset.length > 0;
}

const courseInput = z.object({
  courseCode: z.string().trim().min(2).max(30),
  title: z.string().trim().min(2).max(200),
  description: z.string().trim().max(4000).default(''),
  category: z.enum(['Wajib', 'Pilihan']).default('Wajib'),
  trainerNpk: z.string().trim().max(50).optional().default('')
});

router.get('/', requirePermission('courses.view'), async (req, res, next) => {
  try {
    const result = await pool.request()
      .input('npk', sql.VarChar(50), req.user.sub)
      .input('isAdmin', sql.Bit, req.user.role !== 'teacher')
      .query(`SELECT course_id,course_code,title,description,category,thumbnail_path,status,assigned_trainer_npk
        FROM dbo.courses
        WHERE status='active' AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)
        ORDER BY title`);
    const page = paginateCollection(req, result.recordset);
    res.json({ courses: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

router.post('/', requirePermission('courses.create'), async (req, res, next) => {
  const parsed = courseInput.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Periksa kode, judul, deskripsi, dan kategori course.' } });
  try {
    const { courseCode, title, description, category, trainerNpk } = parsed.data;
    if (trainerNpk && !(await requestHasPermission(req, 'courses.assign_trainer'))) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Peran Anda tidak memiliki hak untuk menugaskan trainer.' } });
    }
    if (trainerNpk) {
      const trainer = await pool.request().input('npk', sql.VarChar(50), trainerNpk)
        .query(`SELECT 1 AS valid FROM dbo.employee_accounts WHERE employee_npk=@npk AND role='teacher' AND status='active'`);
      if (!trainer.recordset.length) return res.status(400).json({ error: { code: 'INVALID_TRAINER', message: 'Pilih akun trainer aktif.' } });
    }
    const result = await pool.request().input('code', sql.VarChar(30), courseCode).input('title', sql.NVarChar(200), title)
      .input('description', sql.NVarChar(4000), description).input('category', sql.VarChar(20), category)
      .input('creator', sql.VarChar(50), req.user.sub).input('trainer', sql.VarChar(50), trainerNpk || null)
      .query(`INSERT INTO dbo.courses(course_code,title,description,category,status,created_by,updated_by,assigned_trainer_npk)
              OUTPUT INSERTED.course_id, INSERTED.course_code, INSERTED.title, INSERTED.thumbnail_path VALUES(@code,@title,@description,@category,'active',@creator,@creator,@trainer);
              INSERT INTO dbo.activity_logs(employee_npk,activity_type,description)
              VALUES(@creator,'course_created',CONCAT(N'Membuat course ',@code));`);
    await syncStudentNow().catch(() => {});
    res.status(201).json({ course: result.recordset[0] });
  } catch (error) { if (error.number === 2627 || error.number === 2601) error.status = 409; next(error); }
});

/**
 * Mengunggah atau memperbarui foto sampul course (JPG, PNG, GIF, WebP maks. 5 MB)
 * dan menyinkronkannya langsung ke database portal Student.
 */
router.post('/:id/thumbnail', requirePermission('courses.manage_cover'), uploadLimiter, courseCoverUpload.single('thumbnail'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID course tidak valid.' } });
  let savedPath = null;
  try {
    if (!(await courseAccess(id.data, req.user))) {
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });
    }
    if (!req.file) {
      return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Pilih file gambar sampul course terlebih dahulu.' } });
    }
    const parsed = parseImageBuffer(req.file.mimetype, req.file.buffer);
    if (!parsed) {
      return res.status(415).json({ error: { code: 'INVALID_IMAGE', message: 'Isi file tidak sesuai dengan format gambar yang diizinkan (JPG, PNG, GIF, atau WebP).' } });
    }
    const existing = await pool.request()
      .input('id', sql.BigInt, id.data)
      .query(`SELECT course_code, thumbnail_path FROM dbo.courses WHERE course_id=@id`);
    const courseRow = existing.recordset[0];
    if (!courseRow) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });

    const filename = `${crypto.randomUUID()}${parsed.extension}`;
    const publicPath = `${THUMBNAIL_PATH_PREFIX}${filename}`;
    savedPath = path.join(courseUploadDir, filename);
    await fs.mkdir(courseUploadDir, { recursive: true });
    await fs.writeFile(savedPath, req.file.buffer, { flag: 'wx', mode: 0o640 });

    await pool.request()
      .input('id', sql.BigInt, id.data)
      .input('path', sql.NVarChar(500), publicPath)
      .input('updater', sql.VarChar(50), req.user.sub)
      .input('code', sql.VarChar(30), courseRow.course_code)
      .query(`UPDATE dbo.courses SET thumbnail_path=@path, updated_by=@updater, updated_at=SYSUTCDATETIME() WHERE course_id=@id;
              INSERT INTO dbo.activity_logs(employee_npk,activity_type,description)
              VALUES(@updater,'course_thumbnail_updated',CONCAT(N'Memperbarui foto sampul course ',@code));`);

    const previousFile = resolveStoredThumbnailPath(courseRow.thumbnail_path);
    if (previousFile && previousFile !== savedPath) {
      await fs.unlink(previousFile).catch(() => {});
    }
    await syncStudentNow().catch(() => {});
    res.json({ thumbnail_path: publicPath, course: { course_id: id.data, thumbnail_path: publicPath } });
  } catch (error) {
    if (savedPath) await fs.unlink(savedPath).catch(() => {});
    next(error);
  }
});

/**
 * Menghapus foto sampul course sehingga kembali ke tampilan ikon bawaan.
 */
router.delete('/:id/thumbnail', requirePermission('courses.manage_cover'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID course tidak valid.' } });
  try {
    if (!(await courseAccess(id.data, req.user))) {
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });
    }
    const existing = await pool.request()
      .input('id', sql.BigInt, id.data)
      .query(`SELECT course_code, thumbnail_path FROM dbo.courses WHERE course_id=@id`);
    const courseRow = existing.recordset[0];
    if (!courseRow) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan.' } });

    await pool.request()
      .input('id', sql.BigInt, id.data)
      .input('updater', sql.VarChar(50), req.user.sub)
      .input('code', sql.VarChar(30), courseRow.course_code)
      .query(`UPDATE dbo.courses SET thumbnail_path=NULL, updated_by=@updater, updated_at=SYSUTCDATETIME() WHERE course_id=@id;
              INSERT INTO dbo.activity_logs(employee_npk,activity_type,description)
              VALUES(@updater,'course_thumbnail_removed',CONCAT(N'Menghapus foto sampul course ',@code));`);

    const previousFile = resolveStoredThumbnailPath(courseRow.thumbnail_path);
    if (previousFile) {
      await fs.unlink(previousFile).catch(() => {});
    }
    await syncStudentNow().catch(() => {});
    res.json({ thumbnail_path: null, course: { course_id: id.data, thumbnail_path: null } });
  } catch (error) { next(error); }
});

router.put('/:id/trainer', requirePermission('courses.assign_trainer'), async (req, res, next) => {
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

router.get('/:id/sessions', requirePermission('courses.view'), async (req, res, next) => {
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

router.get('/:id/detail', requirePermission('courses.view'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID course tidak valid.' } });
  try {
    if (!(await courseAccess(id.data, req.user))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan atau tidak ditugaskan kepada Anda.' } });
    const courseResult = await pool.request().input('id', sql.BigInt, id.data)
      .query(`SELECT c.course_id,c.course_code,c.title,c.description,c.category,c.thumbnail_path,c.status,c.created_at,c.updated_at,
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

router.post('/:id/sessions', requirePermission('courses.manage_sessions'), async (req, res, next) => {
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

router.post('/sessions/:sessionId/materials', async (_req, res) => {
  return res.status(410).json({ error: { code: 'APPROVAL_REQUIRED', message: 'Pengajuan materi langsung ke course telah dinonaktifkan. Kirim materi melalui /material-workflow agar disetujui reviewer terlebih dahulu.' } });
});

module.exports = router;
module.exports.resolveStoredThumbnailPath = resolveStoredThumbnailPath;
