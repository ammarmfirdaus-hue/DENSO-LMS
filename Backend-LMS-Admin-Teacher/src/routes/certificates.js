const fs = require('node:fs');
const path = require('node:path');
const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate, allowRoles } = require('../middleware/auth');
const { materialUpload, uploadDirectory, hasExpectedFileSignature } = require('../middleware/material-upload');
const { uploadLimiter } = require('../middleware/rate-limit');
const { publishNotificationEvent } = require('../services/notification-events');
const { getStudentManagementPool } = require('../services/student-auth');
const { convertDiskImageToWebP } = require('../services/image-converter');

const router = express.Router();
router.use(authenticate);

const allowedExtensions = new Set([
  '.pdf', '.png', '.jpg', '.jpeg', '.webp',
  '.doc', '.docx', '.ppt', '.pptx', '.xls', '.xlsx'
]);
const allowedTypes = new Set(['certificate', 'award', 'document']);

const certificateBodySchema = z.object({
  student_id: z.coerce.number().int().positive('Student wajib dipilih.'),
  course_id: z.preprocess(v => (v === '' || v === null || v === undefined ? null : v), z.coerce.number().int().positive().nullable()),
  award_type: z.enum(['certificate', 'award', 'document']).default('certificate'),
  title: z.string().trim().min(3, 'Judul minimal 3 karakter.').max(200),
  certificate_no: z.string().trim().max(80).optional().default(''),
  description: z.string().trim().max(1000).optional().default(''),
});

function cleanFile(file) {
  if (file?.path) fs.promises.unlink(file.path).catch(() => {});
}

function fileOnDisk(filePath) {
  if (!filePath || path.basename(filePath) !== filePath) return null;
  const resolved = path.resolve(uploadDirectory, filePath);
  return resolved.startsWith(`${uploadDirectory}${path.sep}`) ? resolved : null;
}

function trustedWriteOrigin(req, res, next) {
  const origin = req.get('origin');
  const allowed = [env.FRONTEND_ORIGIN, env.STUDENT_APP_URL]
    .map(value => {
      try { return new URL(value).origin; } catch { return ''; }
    })
    .filter(Boolean);
  if (!origin || !allowed.includes(origin)) {
    return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Asal permintaan tidak dipercaya.' } });
  }
  return next();
}

function sanitizeFilename(name) {
  return String(name || 'dokumen')
    .replace(/[^\w.\-() ]+/g, '_')
    .slice(0, 180) || 'dokumen';
}

router.get('/students', allowRoles('admin', 'teacher'), async (_req, res, next) => {
  try {
    const studentPool = getStudentManagementPool();
    const [rows] = await studentPool.query(`
      SELECT student_id, npk AS employee_npk, fullname, email, status
      FROM student_accounts
      WHERE account_type = 'student' AND status = 'active'
      ORDER BY fullname ASC
    `);
    return res.json({ items: rows, data: rows });
  } catch (err) {
    return next(err);
  }
});

router.get('/', allowRoles('admin', 'teacher', 'student'), async (req, res, next) => {
  try {
    const role = String(req.user?.role || '').toLowerCase();
    const request = pool.request();
    const clauses = [];

    if (role === 'student') {
      const studentId = Number(req.user?.student_id || 0);
      if (!Number.isInteger(studentId) || studentId <= 0) {
        return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Identitas student tidak valid.' } });
      }
      request.input('student_id', sql.BigInt, studentId);
      clauses.push('student_id = @student_id');
    } else if (req.query.student_id) {
      const filterStudentId = Number(req.query.student_id);
      if (Number.isInteger(filterStudentId) && filterStudentId > 0) {
        request.input('student_id', sql.BigInt, filterStudentId);
        clauses.push('student_id = @student_id');
      }
    }

    if (req.query.award_type && allowedTypes.has(String(req.query.award_type))) {
      request.input('award_type', sql.VarChar(40), String(req.query.award_type));
      clauses.push('award_type = @award_type');
    }

    const search = String(req.query.search || '').trim();
    if (search) {
      request.input('search', sql.NVarChar(120), `%${search}%`);
      clauses.push('(title LIKE @search OR student_name LIKE @search OR student_npk LIKE @search OR certificate_no LIKE @search)');
    }

    const whereSql = clauses.length ? `WHERE ${clauses.join(' AND ')}` : '';
    const result = await request.query(`
      SELECT
        certificate_id, student_id, student_npk, student_name,
        course_id, course_title, title, award_type, certificate_no,
        description, original_filename, mime_type, file_size,
        issued_by_npk, issued_by_name, issued_at
      FROM dbo.student_certificates
      ${whereSql}
      ORDER BY issued_at DESC, certificate_id DESC
    `);

    return res.json({ items: result.recordset, data: result.recordset });
  } catch (err) {
    return next(err);
  }
});

router.post('/', trustedWriteOrigin, allowRoles('admin', 'teacher'), uploadLimiter, materialUpload.single('file'), async (req, res, next) => {
  try {
    if (!req.file) {
      return res.status(400).json({ error: { code: 'VALIDATION_ERROR', message: 'Dokumen sertifikat/penghargaan wajib diunggah.' } });
    }

    const ext = path.extname(req.file.originalname || '').toLowerCase();
    if (!allowedExtensions.has(ext)) {
      cleanFile(req.file);
      return res.status(400).json({ error: { code: 'INVALID_FILE', message: 'Format file harus PDF, PNG, JPG, WEBP, atau dokumen Office.' } });
    }

    const sigCategory = ext === '.pdf' ? 'pdf' : (['.png', '.jpg', '.jpeg', '.webp'].includes(ext) ? 'image' : 'document');
    if (!(await hasExpectedFileSignature(req.file.path, sigCategory, ext))) {
      cleanFile(req.file);
      return res.status(400).json({ error: { code: 'INVALID_FILE_SIGNATURE', message: 'Isi file tidak sesuai dengan ekstensi dokumen yang diizinkan.' } });
    }

    if (sigCategory === 'image') {
      const webpConversion = await convertDiskImageToWebP(req.file.path);
      if (webpConversion) {
        req.file.path = webpConversion.filePath;
        req.file.filename = webpConversion.filename;
        req.file.mimetype = webpConversion.mimeType;
        req.file.size = webpConversion.size;
      }
    }

    const parsed = certificateBodySchema.safeParse(req.body || {});
    if (!parsed.success) {
      cleanFile(req.file);
      return res.status(400).json({
        error: { code: 'VALIDATION_ERROR', message: parsed.error.issues[0]?.message || 'Data sertifikat tidak valid.' }
      });
    }

    const { student_id, course_id, award_type, title, certificate_no, description } = parsed.data;
    const studentPool = getStudentManagementPool();
    const [studentRows] = await studentPool.execute(
      "SELECT student_id, npk AS employee_npk, fullname FROM student_accounts WHERE student_id = ? AND account_type = 'student' LIMIT 1",
      [Number(student_id)]
    );
    const student = studentRows[0];
    if (!student) {
      cleanFile(req.file);
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Student tidak ditemukan.' } });
    }

    let courseTitle = null;
    if (course_id) {
      const courseRes = await pool.request()
        .input('course_id', sql.BigInt, course_id)
        .query('SELECT course_id, title FROM dbo.courses WHERE course_id = @course_id');
      if (courseRes.recordset[0]) {
        courseTitle = courseRes.recordset[0].title;
      }
    }

    const issuerNpk = String(req.user?.sub || '').trim() || 'STAFF';
    const issuerName = String(req.user?.name || req.user?.sub || 'Pengajar / Admin').trim().slice(0, 150);
    const storedFileName = path.basename(req.file.filename);
    const safeOriginal = sanitizeFilename(req.file.originalname);

    const inserted = await pool.request()
      .input('student_id', sql.BigInt, student.student_id)
      .input('student_npk', sql.VarChar(50), String(student.employee_npk))
      .input('student_name', sql.NVarChar(150), String(student.fullname))
      .input('course_id', sql.BigInt, course_id || null)
      .input('course_title', sql.NVarChar(200), courseTitle)
      .input('title', sql.NVarChar(200), title)
      .input('award_type', sql.VarChar(40), award_type)
      .input('certificate_no', sql.VarChar(80), certificate_no || null)
      .input('description', sql.NVarChar(1000), description || null)
      .input('file_path', sql.NVarChar(500), storedFileName)
      .input('original_filename', sql.NVarChar(255), safeOriginal)
      .input('mime_type', sql.VarChar(100), String(req.file.mimetype || 'application/octet-stream').slice(0, 100))
      .input('file_size', sql.BigInt, Number(req.file.size || 0))
      .input('issued_by_npk', sql.VarChar(50), issuerNpk)
      .input('issued_by_name', sql.NVarChar(150), issuerName)
      .query(`
        INSERT INTO dbo.student_certificates (
          student_id, student_npk, student_name, course_id, course_title,
          title, award_type, certificate_no, description, file_path,
          original_filename, mime_type, file_size, issued_by_npk, issued_by_name
        )
        OUTPUT INSERTED.*
        VALUES (
          @student_id, @student_npk, @student_name, @course_id, @course_title,
          @title, @award_type, @certificate_no, @description, @file_path,
          @original_filename, @mime_type, @file_size, @issued_by_npk, @issued_by_name
        )
      `);

    const typeLabel = award_type === 'award' ? 'Penghargaan' : (award_type === 'document' ? 'Dokumen' : 'Sertifikat');
    try {
      await pool.request()
        .input('target_role', sql.VarChar(20), 'student')
        .input('target_npk', sql.VarChar(50), String(student.employee_npk))
        .input('category', sql.VarChar(30), 'course')
        .input('title', sql.NVarChar(200), `${typeLabel} Baru: ${title}`)
        .input('message', sql.NVarChar(1000), `Anda menerima ${typeLabel.toLowerCase()} "${title}" dari ${issuerName}. Buka menu Riwayat untuk melihat dan mengunduh dokumen.`)
        .input('sender_npk', sql.VarChar(50), issuerNpk)
        .input('sender_name', sql.NVarChar(150), issuerName)
        .query(`
          INSERT INTO dbo.lms_notifications (
            target_role, target_npk, category, title, message,
            is_important, delivery_mode, sender_npk, sender_name
          )
          VALUES (
            @target_role, @target_npk, @category, @title, @message,
            1, 'normal', @sender_npk, @sender_name
          )
        `);
      publishNotificationEvent({ targetRole: 'student', targetNpk: String(student.employee_npk) });
    } catch (_notifErr) {
      // Non-blocking notification failure
    }

    return res.status(201).json({ data: inserted.recordset[0] });
  } catch (err) {
    cleanFile(req.file);
    return next(err);
  }
});

router.get('/:id/file', allowRoles('admin', 'teacher', 'student'), async (req, res, next) => {
  const certId = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!certId.success) {
    return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sertifikat tidak ditemukan.' } });
  }
  try {
    const result = await pool.request()
      .input('id', sql.BigInt, certId.data)
      .query('SELECT student_id, file_path, original_filename, mime_type FROM dbo.student_certificates WHERE certificate_id = @id');
    const row = result.recordset[0];
    if (!row) {
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sertifikat tidak ditemukan.' } });
    }
    const role = String(req.user?.role || '').toLowerCase();
    if (role === 'student' && Number(row.student_id) !== Number(req.user?.student_id)) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Anda tidak berhak mengakses sertifikat ini.' } });
    }
    const fullPath = fileOnDisk(row.file_path);
    if (!fullPath || !fs.existsSync(fullPath)) {
      return res.status(404).json({ error: { code: 'FILE_MISSING', message: 'File dokumen tidak tersedia di server.' } });
    }
    const disposition = String(req.query.download || '') === '1' ? 'attachment' : 'inline';
    res.setHeader('Content-Type', row.mime_type || 'application/octet-stream');
    res.setHeader('Content-Disposition', `${disposition}; filename="${sanitizeFilename(row.original_filename)}"`);
    res.setHeader('X-Content-Type-Options', 'nosniff');
    return res.sendFile(fullPath);
  } catch (err) {
    return next(err);
  }
});

router.delete('/:id', trustedWriteOrigin, allowRoles('admin', 'teacher'), async (req, res, next) => {
  const certId = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!certId.success) {
    return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sertifikat tidak ditemukan.' } });
  }
  try {
    const existing = await pool.request()
      .input('id', sql.BigInt, certId.data)
      .query('SELECT certificate_id, file_path, issued_by_npk FROM dbo.student_certificates WHERE certificate_id = @id');
    const row = existing.recordset[0];
    if (!row) {
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sertifikat tidak ditemukan.' } });
    }
    if (String(req.user?.role || '').toLowerCase() !== 'admin' && row.issued_by_npk !== String(req.user?.sub || '')) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Anda hanya dapat menghapus sertifikat yang Anda kirim.' } });
    }

    await pool.request().input('id', sql.BigInt, certId.data).query('DELETE FROM dbo.student_certificates WHERE certificate_id = @id');
    const fullPath = fileOnDisk(row.file_path);
    if (fullPath) fs.promises.unlink(fullPath).catch(() => {});
    return res.json({ data: { deleted: true, certificate_id: certId.data } });
  } catch (err) {
    return next(err);
  }
});

module.exports = router;

