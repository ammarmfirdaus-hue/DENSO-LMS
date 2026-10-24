const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const express = require('express');
const jwt = require('jsonwebtoken');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate, allowRoles } = require('../middleware/auth');
const { materialUpload, uploadDirectory, hasExpectedFileSignature } = require('../middleware/material-upload');
const { uploadLimiter } = require('../middleware/rate-limit');
const { paginateCollection } = require('../middleware/pagination');
const { sendReviewInvitation } = require('../services/review-mail');
const { syncStudentNow, deleteStudentMaterial } = require('../services/student-sync');
const { studentHasCourseAccess } = require('../services/student-auth');

const router = express.Router();
const materialTypes = ['text', 'video', 'pdf', 'image', 'link', 'audio', 'document'];
const officeExtensions = new Set(['.doc', '.docx', '.ppt', '.pptx', '.xls', '.xlsx', '.odt', '.ods', '.odp']);
const typeExtensions = {
  pdf: new Set(['.pdf']), image: new Set(['.png', '.jpg', '.jpeg', '.gif', '.webp']),
  video: new Set(['.mp4', '.webm', '.mov', '.ogv']), audio: new Set(['.mp3', '.wav', '.ogg', '.m4a']),
  document: officeExtensions
};

function isYouTubeUrl(value) {
  try {
    const url = new URL(value);
    return ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com', 'youtube-nocookie.com'].includes(url.hostname.toLowerCase())
      && url.protocol === 'https:';
  } catch { return false; }
}

function cleanFile(file) {
  if (file?.path) fs.promises.unlink(file.path).catch(() => {});
}

function fileOnDisk(filePath) {
  if (!filePath || path.basename(filePath) !== filePath) return null;
  const resolved = path.resolve(uploadDirectory, filePath);
  return resolved.startsWith(`${uploadDirectory}${path.sep}`) ? resolved : null;
}

function sendStoredFile(res, record) {
  const filePath = fileOnDisk(record.file_path);
  if (!filePath || !fs.existsSync(filePath)) return res.status(404).json({ error: { code: 'FILE_NOT_FOUND', message: 'File materi tidak ditemukan.' } });
  const extension = path.extname(filePath).toLowerCase();
  const inline = ['.pdf','.png','.jpg','.jpeg','.gif','.webp','.mp4','.webm','.mov','.ogv','.mp3','.wav','.ogg','.m4a'].includes(extension);
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('Cross-Origin-Resource-Policy', 'cross-origin');
  res.removeHeader('X-Frame-Options');
  res.removeHeader('Content-Security-Policy');
  res.setHeader('Content-Disposition', `${inline ? 'inline' : 'attachment'}; filename="${path.basename(filePath)}"`);
  res.type(extension);
  return res.sendFile(filePath);
}

function isReviewSession(req) {
  return req.user?.role === 'reviewer' && req.user?.scope === 'reviewer_portal';
}

router.get('/reviewers', authenticate, allowRoles('admin'), async (_req, res, next) => {
  try {
    const result = await pool.request().query(`SELECT employee_npk,fullname,email,department,position,phone_number FROM dbo.employee_accounts
      WHERE role='reviewer' AND status='active' ORDER BY fullname`);
    res.json({ reviewers: result.recordset });
  } catch (error) { next(error); }
});

router.get('/trainers', authenticate, allowRoles('admin'), async (_req, res, next) => {
  try {
    const result = await pool.request().query(`SELECT employee_npk,fullname FROM dbo.employee_accounts
      WHERE role='teacher' AND status='active' ORDER BY fullname`);
    res.json({ trainers: result.recordset });
  } catch (error) { next(error); }
});

/**
 * Endpoint pengajuan materi baru ke dalam sesi kursus oleh Admin atau Teacher.
 * Status awal disetel ke 'pending_admin' dan menunggu persetujuan Admin.
 */
router.post('/sessions/:sessionId/submissions', authenticate, allowRoles('admin', 'teacher'), uploadLimiter, materialUpload.single('file'), async (req, res, next) => {
  const sessionId = z.coerce.number().int().positive().safeParse(req.params.sessionId);
  const input = z.object({
    title: z.string().trim().min(2).max(200),
    type: z.enum(materialTypes),
    content: z.string().max(20000).optional().default(''),
    sortOrder: z.coerce.number().int().min(0).max(10000).default(0)
  }).safeParse(req.body);
  if (!sessionId.success || !input.success) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Periksa judul, tipe, dan konten materi.' } });
  }

  const { title, type, content, sortOrder } = input.data;
  const extension = req.file ? path.extname(req.file.filename).toLowerCase() : '';
  if (req.file) {
    try {
      if (!await hasExpectedFileSignature(req.file.path, extension)) {
        cleanFile(req.file);
        return res.status(415).json({ error: { code: 'INVALID_FILE_CONTENT', message: 'Isi file tidak cocok dengan format yang dipilih.' } });
      }
    } catch (error) {
      cleanFile(req.file);
      return next(error);
    }
  }
  const contentIsUrl = /^https:\/\//i.test(content);
  if (type === 'text' && !content.trim()) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'CONTENT_REQUIRED', message: 'Isi materi teks wajib diisi.' } });
  }
  if (type === 'link' && (!contentIsUrl || !z.string().url().safeParse(content).success)) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'INVALID_LINK', message: 'Masukkan URL HTTPS yang valid.' } });
  }
  if (type === 'video') {
    if (!req.file && !isYouTubeUrl(content)) {
      cleanFile(req.file);
      return res.status(400).json({ error: { code: 'VIDEO_REQUIRED', message: 'Masukkan link YouTube HTTPS yang valid atau unggah file video.' } });
    }
    if (req.file && !typeExtensions.video.has(extension)) {
      cleanFile(req.file);
      return res.status(400).json({ error: { code: 'INVALID_VIDEO_FILE', message: 'Format file video harus MP4, WebM, MOV, atau OGV.' } });
    }
  } else if (typeExtensions[type] && (!req.file || !typeExtensions[type].has(extension))) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'FILE_REQUIRED', message: `Unggah file yang sesuai untuk materi ${type}.` } });
  }
  try {
    const access = await pool.request().input('session', sql.BigInt, sessionId.data)
      .input('npk', sql.VarChar(50), req.user.sub).input('isAdmin', sql.Bit, req.user.role === 'admin')
      .query(`SELECT s.session_id FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id
        WHERE s.session_id=@session AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)`);
    if (!access.recordset.length) {
      cleanFile(req.file);
      return res.status(404).json({ error: { code: 'SESSION_NOT_FOUND', message: 'Sesi tidak ditemukan atau tidak dapat diakses.' } });
    }
    const result = await pool.request()
      .input('session', sql.BigInt, sessionId.data).input('title', sql.NVarChar(200), title)
      .input('type', sql.VarChar(20), type).input('content', sql.NVarChar(sql.MAX), content || null)
      .input('filePath', sql.NVarChar(500), req.file?.filename || null)
      .input('originalName', sql.NVarChar(255), req.file?.originalname || null)
      .input('sortOrder', sql.Int, sortOrder).input('submitter', sql.VarChar(50), req.user.sub)
      .query(`INSERT INTO dbo.material_submissions(session_id,title,material_type,content,file_path,original_file_name,sort_order,submitted_by,status)
        OUTPUT INSERTED.submission_id,INSERTED.title,INSERTED.material_type,INSERTED.status,INSERTED.created_at
        VALUES(@session,@title,@type,@content,@filePath,@originalName,@sortOrder,@submitter,'pending_admin')`);
    res.status(201).json({ submission: result.recordset[0], message: 'Materi dikirim untuk persetujuan Admin.' });
  } catch (error) { cleanFile(req.file); next(error); }
});

router.get('/submissions', authenticate, allowRoles('admin', 'teacher'), async (req, res, next) => {
  try {
    const request = pool.request().input('isAdmin', sql.Bit, req.user.role === 'admin').input('npk', sql.VarChar(50), req.user.sub);
    const result = await request.query(`SELECT ms.submission_id,ms.title,ms.material_type,ms.content,ms.original_file_name,
        ms.status,ms.submitted_by,submitter.fullname AS submitter_name,ms.reviewer_npk,reviewer.fullname AS reviewer_name,
        ms.reviewer_comment,ms.admin_comment,ms.created_at,c.course_id,c.course_code,c.title AS course_title,
        s.session_id,s.title AS session_title,ms.published_material_id
      FROM dbo.material_submissions ms
      JOIN dbo.course_sessions s ON s.session_id=ms.session_id JOIN dbo.courses c ON c.course_id=s.course_id
      JOIN dbo.employee_accounts submitter ON submitter.employee_npk=ms.submitted_by
      LEFT JOIN dbo.employee_accounts reviewer ON reviewer.employee_npk=ms.reviewer_npk
      WHERE (@isAdmin=1 OR ms.submitted_by=@npk) ORDER BY ms.created_at DESC`);
    const page = paginateCollection(req, result.recordset);
    res.json({ submissions: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

/**
 * Endpoint bagi Admin untuk menolak materi saat berstatus 'pending_admin' disertai alasan perbaikan.
 */
router.post('/submissions/:id/admin-reject', authenticate, allowRoles('admin'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  const input = z.object({
    comment: z.string().trim().min(3, 'Alasan penolakan minimal 3 karakter.').max(2000)
  }).safeParse(req.body);

  if (!id.success || !input.success) {
    const message = input.error?.issues?.[0]?.message || 'Alasan penolakan tidak valid.';
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message } });
  }

  try {
    const result = await pool.request()
      .input('id', sql.BigInt, id.data)
      .input('comment', sql.NVarChar(2000), input.data.comment)
      .query(`
        UPDATE dbo.material_submissions
        SET status = 'rejected', admin_comment = @comment, updated_at = SYSUTCDATETIME()
        WHERE submission_id = @id AND status = 'pending_admin';
        SELECT @@ROWCOUNT AS affected;
      `);

    if (!result.recordset[0]?.affected) {
      return res.status(409).json({
        error: { code: 'NOT_PENDING_ADMIN', message: 'Materi tidak berstatus menunggu persetujuan Admin atau sudah diproses.' }
      });
    }

    res.json({
      updated: true,
      status: 'rejected',
      message: 'Materi berhasil ditolak dan dikembalikan untuk perbaikan.'
    });
  } catch (error) { next(error); }
});

/**
 * Endpoint edit dan pengajuan ulang materi oleh Admin atau Teacher.
 * Memvalidasi izin edit: Teacher hanya saat 'rejected', Admin saat 'pending_admin' atau 'rejected'.
 * Materi yang berhasil diubah akan kembali ke status 'pending_admin'.
 */
router.put('/submissions/:id', authenticate, allowRoles('admin', 'teacher'), uploadLimiter, materialUpload.single('file'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  const input = z.object({
    title: z.string().trim().min(2).max(200),
    type: z.enum(materialTypes),
    content: z.string().max(20000).optional().default(''),
    sortOrder: z.coerce.number().int().min(0).max(10000).default(0)
  }).safeParse(req.body);

  if (!id.success || !input.success) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Periksa judul, tipe, dan konten materi.' } });
  }

  const { title, type, content, sortOrder } = input.data;
  const extension = req.file ? path.extname(req.file.filename).toLowerCase() : '';

  if (req.file) {
    try {
      if (!await hasExpectedFileSignature(req.file.path, extension)) {
        cleanFile(req.file);
        return res.status(415).json({ error: { code: 'INVALID_FILE_CONTENT', message: 'Isi file tidak cocok dengan format yang dipilih.' } });
      }
    } catch (error) {
      cleanFile(req.file);
      return next(error);
    }
  }

  const contentIsUrl = /^https:\/\//i.test(content);
  if (type === 'text' && !content.trim()) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'CONTENT_REQUIRED', message: 'Isi materi teks wajib diisi.' } });
  }
  if (type === 'link' && (!contentIsUrl || !z.string().url().safeParse(content).success)) {
    cleanFile(req.file);
    return res.status(400).json({ error: { code: 'INVALID_LINK', message: 'Masukkan URL HTTPS yang valid.' } });
  }

  try {
    const existingResult = await pool.request()
      .input('id', sql.BigInt, id.data)
      .query(`
        SELECT ms.submission_id, ms.session_id, ms.title, ms.material_type, ms.content, ms.file_path, 
               ms.original_file_name, ms.status, ms.submitted_by
        FROM dbo.material_submissions ms
        WHERE ms.submission_id = @id
      `);

    const existing = existingResult.recordset[0];
    if (!existing) {
      cleanFile(req.file);
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Materi tidak ditemukan.' } });
    }

    // Role-based state machine guards:
    // 1. Teacher cannot edit while pending_admin or pending_review
    if (req.user.role === 'teacher') {
      if (existing.submitted_by !== req.user.sub) {
        cleanFile(req.file);
        return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Hanya pengaju materi yang dapat mengubah pengajuan ini.' } });
      }
      if (existing.status === 'pending_admin') {
        cleanFile(req.file);
        return res.status(403).json({
          error: { code: 'CANNOT_EDIT_PENDING_APPROVAL', message: 'Materi sedang menunggu persetujuan Admin dan tidak dapat diedit saat ini.' }
        });
      }
      if (existing.status === 'pending_review') {
        cleanFile(req.file);
        return res.status(403).json({
          error: { code: 'CANNOT_EDIT_IN_REVIEW', message: 'Materi sedang direview oleh Reviewer dan terkunci.' }
        });
      }
      if (existing.status === 'approved' || existing.status === 'published') {
        cleanFile(req.file);
        return res.status(403).json({
          error: { code: 'CANNOT_EDIT_FINAL', message: 'Materi yang sudah disetujui atau dipublikasikan tidak dapat diedit.' }
        });
      }
      // Allowed: status === 'rejected'
    } else if (req.user.role === 'admin') {
      // 2. Admin cannot edit while pending_review
      if (existing.status === 'pending_review') {
        cleanFile(req.file);
        return res.status(403).json({
          error: { code: 'CANNOT_EDIT_IN_REVIEW', message: 'Materi sudah dikirim ke Reviewer dan tidak dapat diedit sebelum ada keputusan dari Reviewer.' }
        });
      }
      if (existing.status === 'approved' || existing.status === 'published') {
        cleanFile(req.file);
        return res.status(403).json({
          error: { code: 'CANNOT_EDIT_FINAL', message: 'Materi yang sudah disetujui atau dipublikasikan tidak dapat diedit.' }
        });
      }
      // Allowed: status === 'pending_admin' OR status === 'rejected'
    }

    let newFilePath = existing.file_path;
    let newOriginalName = existing.original_file_name;

    if (req.file) {
      if (existing.file_path) {
        const oldFile = fileOnDisk(existing.file_path);
        if (oldFile) fs.promises.unlink(oldFile).catch(() => {});
      }
      newFilePath = req.file.filename;
      newOriginalName = req.file.originalname;
    } else if (['text', 'link'].includes(type) || (type === 'video' && isYouTubeUrl(content))) {
      if (existing.file_path) {
        const oldFile = fileOnDisk(existing.file_path);
        if (oldFile) fs.promises.unlink(oldFile).catch(() => {});
      }
      newFilePath = null;
      newOriginalName = null;
    }

    if (type === 'video') {
      if (!newFilePath && !isYouTubeUrl(content)) {
        cleanFile(req.file);
        return res.status(400).json({ error: { code: 'VIDEO_REQUIRED', message: 'Masukkan link YouTube HTTPS yang valid atau unggah file video.' } });
      }
      if (req.file && !typeExtensions.video.has(extension)) {
        cleanFile(req.file);
        return res.status(400).json({ error: { code: 'INVALID_VIDEO_FILE', message: 'Format file video harus MP4, WebM, MOV, atau OGV.' } });
      }
    } else if (typeExtensions[type] && !newFilePath) {
      cleanFile(req.file);
      return res.status(400).json({ error: { code: 'FILE_REQUIRED', message: `Unggah file yang sesuai untuk materi bertipe ${type}.` } });
    }

    // Revoke any pending review links since the material is being modified & resubmitted
    await pool.request().input('id', sql.BigInt, id.data)
      .query(`UPDATE dbo.material_review_links SET revoked_at = SYSUTCDATETIME() WHERE submission_id = @id AND revoked_at IS NULL`);

    // Resets status to pending_admin so it waits for Admin approval
    const updateResult = await pool.request()
      .input('id', sql.BigInt, id.data)
      .input('title', sql.NVarChar(200), title)
      .input('type', sql.VarChar(20), type)
      .input('content', sql.NVarChar(sql.MAX), content || null)
      .input('filePath', sql.NVarChar(500), newFilePath)
      .input('originalName', sql.NVarChar(255), newOriginalName)
      .input('sortOrder', sql.Int, sortOrder)
      .query(`
        UPDATE dbo.material_submissions
        SET 
          title = @title,
          material_type = @type,
          content = @content,
          file_path = @filePath,
          original_file_name = @originalName,
          sort_order = @sortOrder,
          status = 'pending_admin',
          reviewer_npk = NULL,
          updated_at = SYSUTCDATETIME()
        OUTPUT INSERTED.submission_id, INSERTED.title, INSERTED.material_type, 
               INSERTED.status, INSERTED.updated_at
        WHERE submission_id = @id;
      `);

    res.json({
      submission: updateResult.recordset[0],
      message: 'Materi berhasil diperbarui dan dikirim ulang untuk persetujuan Admin.'
    });
  } catch (error) {
    cleanFile(req.file);
    next(error);
  }
});

router.post('/submissions/:id/send-review', authenticate, allowRoles('admin'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  const body = z.object({ reviewerEmail: z.string().trim().email().max(254).transform(value => value.toLowerCase()) }).safeParse(req.body);
  if (!id.success || !body.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Masukkan alamat email reviewer yang valid.' } });

  let reviewer;
  let submission;
  const rawToken = crypto.randomBytes(32).toString('base64url');
  const tokenHash = crypto.createHash('sha256').update(rawToken).digest('hex');
  const expiresAt = new Date(Date.now() + 48 * 60 * 60 * 1000);
  const transaction = new sql.Transaction(pool);
  try {
    await transaction.begin();
    const request = new sql.Request(transaction).input('id', sql.BigInt, id.data)
      .input('reviewerEmail', sql.NVarChar(254), body.data.reviewerEmail);
    const loaded = await request.query(`SELECT ms.submission_id,ms.title,ms.status,ea.employee_npk,ea.fullname,ea.email,
        c.title AS course_title
      FROM dbo.material_submissions ms JOIN dbo.course_sessions s ON s.session_id=ms.session_id
      JOIN dbo.courses c ON c.course_id=s.course_id CROSS JOIN dbo.employee_accounts ea
      WHERE ms.submission_id=@id AND ms.status='pending_admin'
        AND LOWER(LTRIM(RTRIM(ea.email)))=@reviewerEmail
        AND ea.role='reviewer' AND ea.status='active'`);
    submission = loaded.recordset[0];
    if (!submission) {
      await transaction.rollback();
      return res.status(409).json({ error: { code: 'NOT_READY', message: 'Email tidak cocok dengan akun reviewer aktif, atau materi tidak lagi menunggu persetujuan Admin.' } });
    }
    reviewer = submission;
    await new sql.Request(transaction).input('id', sql.BigInt, id.data).input('reviewer', sql.VarChar(50), reviewer.employee_npk)
      .input('hash', sql.Char(64), tokenHash).input('expires', sql.DateTime2, expiresAt)
      .query(`UPDATE dbo.material_review_links SET revoked_at=SYSUTCDATETIME() WHERE submission_id=@id AND revoked_at IS NULL;
        INSERT INTO dbo.material_review_links(submission_id,reviewer_npk,token_hash,expires_at)
        VALUES(@id,@reviewer,@hash,@expires);
        UPDATE dbo.material_submissions SET reviewer_npk=@reviewer,status='pending_review',updated_at=SYSUTCDATETIME()
        WHERE submission_id=@id AND status='pending_admin'`);
    await transaction.commit();
  } catch (error) {
    if (transaction._aborted !== true) await transaction.rollback().catch(() => {});
    return next(error);
  }

  const reviewUrl = `${env.REVIEW_PORTAL_URL.replace(/\/$/, '')}/review/${rawToken}`;
  try {
    await sendReviewInvitation({ to: reviewer.email, reviewerName: reviewer.fullname, materialTitle: reviewer.title,
      courseTitle: reviewer.course_title, reviewUrl, expiresAt });
    res.json({ sent: true, expiresAt, message: `Tautan review dikirim ke ${reviewer.email}.` });
  } catch (error) {
    await pool.request().input('id', sql.BigInt, id.data).input('hash', sql.Char(64), tokenHash)
      .query(`UPDATE dbo.material_review_links SET revoked_at=SYSUTCDATETIME() WHERE token_hash=@hash;
        UPDATE dbo.material_submissions SET status='pending_admin',reviewer_npk=NULL,updated_at=SYSUTCDATETIME()
        WHERE submission_id=@id AND status='pending_review'`);
    console.error('Reviewer invitation email failed:', error.message);
    res.status(503).json({ error: { code: 'EMAIL_FAILED', message: 'Email reviewer gagal dikirim. Periksa konfigurasi SMTP lalu coba lagi.' } });
  }
});

router.post('/links/:token/consume', async (req, res, next) => {
  const token = z.string().regex(/^[A-Za-z0-9_-]{40,60}$/).safeParse(req.params.token);
  if (!token.success) return res.status(404).json({ error: { code: 'INVALID_REVIEW_LINK', message: 'Link review tidak valid atau sudah kedaluwarsa.' } });
  const tokenHash = crypto.createHash('sha256').update(token.data).digest('hex');
  try {
    const result = await pool.request().input('hash', sql.Char(64), tokenHash)
      .query(`UPDATE dbo.material_review_links SET consumed_at=SYSUTCDATETIME()
        OUTPUT INSERTED.reviewer_npk WHERE token_hash=@hash AND consumed_at IS NULL AND revoked_at IS NULL AND expires_at>SYSUTCDATETIME()`);
    const reviewerNpk = result.recordset[0]?.reviewer_npk;
    if (!reviewerNpk) return res.status(410).json({ error: { code: 'REVIEW_LINK_EXPIRED', message: 'Link review sudah dipakai atau kedaluwarsa. Minta Admin mengirim link baru.' } });
    const reviewerResult = await pool.request().input('npk', sql.VarChar(50), reviewerNpk)
      .query(`SELECT employee_npk,fullname,role,status FROM dbo.employee_accounts WHERE employee_npk=@npk AND role='reviewer' AND status='active'`);
    const reviewer = reviewerResult.recordset[0];
    if (!reviewer) return res.status(410).json({ error: { code: 'REVIEWER_INACTIVE', message: 'Akun reviewer tidak aktif.' } });
    const sessionToken = jwt.sign({ sub: reviewer.employee_npk, role: 'reviewer', name: reviewer.fullname, scope: 'reviewer_portal' }, env.JWT_SECRET, {
      algorithm: 'HS256', expiresIn: '2h', issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
    });
    res.json({ token: sessionToken, reviewer: { npk: reviewer.employee_npk, name: reviewer.fullname }, expiresIn: 7200 });
  } catch (error) { next(error); }
});

router.get('/reviewer/submissions', authenticate, allowRoles('reviewer'), async (req, res, next) => {
  if (!isReviewSession(req)) return res.status(403).json({ error: { code: 'REVIEW_SESSION_REQUIRED', message: 'Buka halaman dari link email reviewer.' } });
  try {
    const result = await pool.request().input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT ms.submission_id,ms.title,ms.material_type,ms.content,ms.original_file_name,ms.status,
        ms.reviewer_comment,ms.created_at,c.course_code,c.title AS course_title,s.title AS session_title,
        submitter.fullname AS submitter_name
        FROM dbo.material_submissions ms JOIN dbo.course_sessions s ON s.session_id=ms.session_id
        JOIN dbo.courses c ON c.course_id=s.course_id JOIN dbo.employee_accounts submitter ON submitter.employee_npk=ms.submitted_by
        WHERE ms.reviewer_npk=@npk ORDER BY CASE WHEN ms.status='pending_review' THEN 0 ELSE 1 END,ms.created_at DESC`);
    const page = paginateCollection(req, result.recordset);
    res.json({ submissions: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

router.get('/reviewer/submissions/:id', authenticate, allowRoles('reviewer'), async (req, res, next) => {
  if (!isReviewSession(req)) return res.status(403).json({ error: { code: 'REVIEW_SESSION_REQUIRED', message: 'Sesi reviewer tidak valid.' } });
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID materi tidak valid.' } });
  try {
    const result = await pool.request().input('id', sql.BigInt, id.data).input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT ms.submission_id,ms.title,ms.material_type,ms.content,ms.file_path,ms.original_file_name,ms.status,
        ms.reviewer_comment,ms.created_at,c.course_code,c.title AS course_title,s.title AS session_title,
        submitter.fullname AS submitter_name
        FROM dbo.material_submissions ms JOIN dbo.course_sessions s ON s.session_id=ms.session_id
        JOIN dbo.courses c ON c.course_id=s.course_id JOIN dbo.employee_accounts submitter ON submitter.employee_npk=ms.submitted_by
        WHERE ms.submission_id=@id AND ms.reviewer_npk=@npk`);
    if (!result.recordset[0]) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Materi tidak ditemukan.' } });
    const submission = result.recordset[0];
    submission.fileUrl = submission.file_path ? `/material-workflow/reviewer/submissions/${submission.submission_id}/file` : null;
    delete submission.file_path;
    res.json({ submission });
  } catch (error) { next(error); }
});

router.get('/reviewer/submissions/:id/file', authenticate, allowRoles('reviewer'), async (req, res, next) => {
  if (!isReviewSession(req)) return res.status(403).json({ error: { code: 'REVIEW_SESSION_REQUIRED', message: 'Sesi reviewer tidak valid.' } });
  try {
    const result = await pool.request().input('id', sql.BigInt, Number(req.params.id)).input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT file_path FROM dbo.material_submissions WHERE submission_id=@id AND reviewer_npk=@npk`);
    if (!result.recordset[0]) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'File tidak ditemukan.' } });
    return sendStoredFile(res, result.recordset[0]);
  } catch (error) { next(error); }
});

/**
 * Endpoint bagi Reviewer untuk memberikan keputusan: 'approved' (disetujui) atau 'rejected' (ditolak).
 */
router.post('/reviewer/submissions/:id/decision', authenticate, allowRoles('reviewer'), async (req, res, next) => {
  if (!isReviewSession(req)) return res.status(403).json({ error: { code: 'REVIEW_SESSION_REQUIRED', message: 'Sesi reviewer tidak valid.' } });
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  const input = z.object({ decision: z.enum(['approved', 'rejected']), comment: z.string().trim().max(2000).optional().default('') }).safeParse(req.body);
  if (!id.success || !input.success || (input.data.decision === 'rejected' && input.data.comment.length < 3)) {
    return res.status(400).json({ error: { code: 'INVALID_DECISION', message: 'Isi alasan minimal 3 karakter saat menolak materi.' } });
  }
  try {
    const result = await pool.request().input('id', sql.BigInt, id.data).input('npk', sql.VarChar(50), req.user.sub)
      .input('status', sql.VarChar(24), input.data.decision).input('comment', sql.NVarChar(2000), input.data.comment || null)
      .query(`UPDATE dbo.material_submissions SET status=@status,reviewer_comment=@comment,
        reviewed_at=SYSUTCDATETIME(),updated_at=SYSUTCDATETIME()
        WHERE submission_id=@id AND reviewer_npk=@npk AND status='pending_review'; SELECT @@ROWCOUNT AS affected`);
    if (!result.recordset[0]?.affected) return res.status(409).json({ error: { code: 'ALREADY_REVIEWED', message: 'Materi sudah diputuskan atau tidak lagi menunggu review.' } });
    res.json({ updated: true, status: input.data.decision, message: input.data.decision === 'approved' ? 'Materi disetujui. Menunggu Admin mempublikasikannya ke student.' : 'Materi ditolak dan dapat diperbaiki oleh trainer.' });
  } catch (error) { next(error); }
});

router.get('/submissions/:id/file', authenticate, allowRoles('admin', 'teacher'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID materi tidak valid.' } });
  try {
    const result = await pool.request().input('id', sql.BigInt, id.data).input('npk', sql.VarChar(50), req.user.sub)
      .input('isAdmin', sql.Bit, req.user.role === 'admin')
      .query(`SELECT ms.file_path FROM dbo.material_submissions ms WHERE ms.submission_id=@id
        AND (@isAdmin=1 OR ms.submitted_by=@npk)`);
    if (!result.recordset[0]) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'File materi tidak ditemukan.' } });
    return sendStoredFile(res, result.recordset[0]);
  } catch (error) { next(error); }
});

/**
 * Endpoint bagi Admin untuk mempublikasikan materi berstatus 'approved' ke tabel course_materials
 * dan memicu sinkronisasi langsung ke database MySQL student.
 */
router.post('/submissions/:id/publish', authenticate, allowRoles('admin'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID materi tidak valid.' } });
  const transaction = new sql.Transaction(pool);
  try {
    await transaction.begin();
    const loaded = await new sql.Request(transaction).input('id', sql.BigInt, id.data)
      .query(`SELECT session_id,title,material_type,content,file_path,sort_order FROM dbo.material_submissions WITH (UPDLOCK,ROWLOCK)
        WHERE submission_id=@id AND status='approved'`);
    const item = loaded.recordset[0];
    if (!item) {
      await transaction.rollback();
      return res.status(409).json({ error: { code: 'NOT_APPROVED', message: 'Materi hanya dapat dipublikasikan setelah reviewer menyetujuinya.' } });
    }
    const created = await new sql.Request(transaction).input('session', sql.BigInt, item.session_id)
      .input('title', sql.NVarChar(200), item.title).input('type', sql.VarChar(20), item.material_type)
      .input('content', sql.NVarChar(sql.MAX), item.content).input('filePath', sql.NVarChar(500), item.file_path)
      .input('order', sql.Int, item.sort_order)
      .query(`INSERT INTO dbo.course_materials(session_id,title,material_type,content,file_path,sort_order)
        OUTPUT INSERTED.material_id VALUES(@session,@title,@type,@content,@filePath,@order)`);
    const materialId = created.recordset[0].material_id;
    await new sql.Request(transaction).input('id', sql.BigInt, id.data).input('material', sql.BigInt, materialId)
      .query(`UPDATE dbo.material_submissions SET status='published',published_material_id=@material,updated_at=SYSUTCDATETIME() WHERE submission_id=@id`);
    await transaction.commit();
    let synced = false;
    try { synced = await syncStudentNow(); }
    catch (syncError) { console.error('Material published; student sync will retry:', syncError.message); }
    res.json({ published: true, materialId, synced, message: synced ? 'Materi dipublikasikan dan langsung disinkronkan ke database student.' : 'Materi dipublikasikan. Sinkronisasi student akan berjalan berkala jika diaktifkan.' });
  } catch (error) {
    if (transaction._aborted !== true) await transaction.rollback().catch(() => {});
    next(error);
  }
});

router.get('/published', authenticate, allowRoles('admin', 'teacher'), async (req, res, next) => {
  try {
    const result = await pool.request().query(`
      SELECT 
        m.material_id,
        m.session_id,
        m.title,
        m.material_type,
        m.content,
        m.file_path,
        m.sort_order,
        m.created_at,
        ms.submission_id,
        ms.original_file_name,
        ms.submitted_by,
        submitter.fullname AS submitter_name,
        c.course_id,
        c.course_code,
        c.title AS course_title,
        c.assigned_trainer_npk,
        c.created_by AS course_created_by,
        s.title AS session_title
      FROM dbo.course_materials m
      JOIN dbo.course_sessions s ON s.session_id = m.session_id
      JOIN dbo.courses c ON c.course_id = s.course_id
      LEFT JOIN dbo.material_submissions ms ON ms.published_material_id = m.material_id AND ms.status = 'published'
      LEFT JOIN dbo.employee_accounts submitter ON submitter.employee_npk = ms.submitted_by
      ORDER BY m.created_at DESC, m.material_id DESC
    `);

    const materials = result.recordset.map(item => ({
      ...item,
      fileUrl: item.file_path ? `/material-workflow/published/${item.material_id}/file` : null,
      canDelete: req.user.role === 'admin' ||
        item.submitted_by === req.user.sub ||
        item.assigned_trainer_npk === req.user.sub ||
        item.course_created_by === req.user.sub
    }));

    res.json({ materials });
  } catch (error) { next(error); }
});

router.delete('/published/:materialId', authenticate, allowRoles('admin', 'teacher'), async (req, res, next) => {
  const materialId = z.coerce.number().int().positive().safeParse(req.params.materialId);
  if (!materialId.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID materi tidak valid.' } });

  try {
    const findQuery = await pool.request().input('id', sql.BigInt, materialId.data).query(`
      SELECT m.material_id, m.title, m.file_path, ms.submission_id, ms.file_path AS sub_file_path,
             ms.submitted_by, c.assigned_trainer_npk, c.created_by AS course_created_by
      FROM dbo.course_materials m
      JOIN dbo.course_sessions s ON s.session_id = m.session_id
      JOIN dbo.courses c ON c.course_id = s.course_id
      LEFT JOIN dbo.material_submissions ms ON ms.published_material_id = m.material_id AND ms.status = 'published'
      WHERE m.material_id = @id
    `);

    const item = findQuery.recordset[0];
    if (!item) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Materi tidak ditemukan.' } });

    const canDelete = req.user.role === 'admin' ||
      item.submitted_by === req.user.sub ||
      item.assigned_trainer_npk === req.user.sub ||
      item.course_created_by === req.user.sub;

    if (!canDelete) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Hanya Admin atau pengajar pembuat materi yang berhak menghapus materi final ini.' } });
    }

    const transaction = new sql.Transaction(pool);
    await transaction.begin();
    try {
      await new sql.Request(transaction).input('matId', sql.BigInt, materialId.data)
        .query(`DELETE FROM dbo.material_submissions WHERE published_material_id = @matId`);

      await new sql.Request(transaction).input('matId', sql.BigInt, materialId.data)
        .query(`DELETE FROM dbo.course_materials WHERE material_id = @matId`);

      await transaction.commit();
    } catch (txErr) {
      if (transaction._aborted !== true) await transaction.rollback().catch(() => {});
      throw txErr;
    }

    const filePathToDelete = item.file_path || item.sub_file_path;
    if (filePathToDelete) {
      const local = fileOnDisk(filePathToDelete);
      if (local && fs.existsSync(local)) {
        fs.promises.unlink(local).catch(() => {});
      }
    }

    await deleteStudentMaterial(materialId.data);

    pool.request()
      .input('npk', sql.VarChar(50), req.user.sub)
      .input('type', sql.VarChar(50), 'DELETE_FINAL_MATERIAL')
      .input('desc', sql.NVarChar(500), `Materi final "${item.title}" (ID: ${materialId.data}) dihapus oleh ${req.user.sub}`)
      .query(`INSERT INTO dbo.activity_logs(employee_npk, activity_type, description) VALUES(@npk, @type, @desc)`)
      .catch(() => {});

    res.json({ success: true, message: 'Materi final berhasil dihapus dari LMS dan student.' });
  } catch (error) { next(error); }
});

router.delete('/submissions/:id', authenticate, allowRoles('admin', 'teacher'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_ID', message: 'ID pengajuan tidak valid.' } });
  try {
    const findQuery = await pool.request().input('id', sql.BigInt, id.data)
      .query(`SELECT submission_id, title, status, submitted_by, file_path, published_material_id FROM dbo.material_submissions WHERE submission_id = @id`);
    const item = findQuery.recordset[0];
    if (!item) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Pengajuan materi tidak ditemukan.' } });

    if (req.user.role !== 'admin' && item.submitted_by !== req.user.sub) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Anda tidak memiliki hak untuk menghapus pengajuan ini.' } });
    }

    const transaction = new sql.Transaction(pool);
    await transaction.begin();
    try {
      if (item.published_material_id) {
        await new sql.Request(transaction).input('matId', sql.BigInt, item.published_material_id)
          .query(`DELETE FROM dbo.course_materials WHERE material_id = @matId`);
      }
      await new sql.Request(transaction).input('id', sql.BigInt, id.data)
        .query(`DELETE FROM dbo.material_submissions WHERE submission_id = @id`);
      await transaction.commit();
    } catch (err) {
      if (transaction._aborted !== true) await transaction.rollback().catch(() => {});
      throw err;
    }

    if (item.file_path) {
      const local = fileOnDisk(item.file_path);
      if (local && fs.existsSync(local)) fs.promises.unlink(local).catch(() => {});
    }
    if (item.published_material_id) {
      await deleteStudentMaterial(item.published_material_id);
    }
    res.json({ success: true, message: 'Pengajuan materi berhasil dihapus.' });
  } catch (error) { next(error); }
});

router.get('/published/:materialId/file', authenticate, allowRoles('student', 'admin', 'teacher'), async (req, res, next) => {
  const materialId = z.coerce.number().int().positive().safeParse(req.params.materialId);
  if (!materialId.success) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Materi tidak ditemukan.' } });
  try {
    const result = await pool.request().input('id', sql.BigInt, materialId.data)
      .query(`SELECT ms.file_path,c.course_code,c.created_by,c.assigned_trainer_npk FROM dbo.material_submissions ms
        JOIN dbo.course_sessions s ON s.session_id=ms.session_id JOIN dbo.courses c ON c.course_id=s.course_id
        WHERE ms.published_material_id=@id AND ms.status='published'`);
    if (!result.recordset[0]) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'File materi tidak tersedia.' } });
    const material = result.recordset[0];
    if (req.user.role === 'student') {
      if (!await studentHasCourseAccess(Number(req.user.student_id), material.course_code)) {
        return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'File materi tidak tersedia.' } });
      }
    } else if (req.user.role !== 'admin' && req.user.role !== 'teacher') {
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'File materi tidak tersedia.' } });
    }
    return sendStoredFile(res, material);
  } catch (error) { next(error); }
});

module.exports = router;

