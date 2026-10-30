const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate, allowRoles } = require('../middleware/auth');
const { publishNotificationEvent } = require('../services/notification-events');
const { listActiveStudents, createTeacherRecommendation, listEnrollmentRequests, decideEnrollmentRequest } = require('../services/course-enrollments');
const { paginateCollection } = require('../middleware/pagination');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));

function trustedStaffOrigin(req, res, next) {
  const origin = req.get('origin');
  let allowedOrigin = '';
  try { allowedOrigin = new URL(env.FRONTEND_ORIGIN).origin; } catch { /* env schema already validates it */ }
  if (!origin || origin !== allowedOrigin) {
    return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan pengelolaan enrollment ditolak.' } });
  }
  next();
}

router.get('/options', allowRoles('teacher'), async (req, res, next) => {
  try {
    const courses = await pool.request().input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT course_code,title FROM dbo.courses
        WHERE status='active' AND (created_by=@npk OR assigned_trainer_npk=@npk)
        ORDER BY title`);
    const students = await listActiveStudents(req.query.studentSearch || '', 50);
    res.json({ courses: courses.recordset, students });
  } catch (error) { next(error); }
});

router.get('/requests', async (req, res, next) => {
  try {
    const status = ['all', 'pending', 'approved', 'rejected'].includes(req.query.status) ? req.query.status : 'all';
    const requests = await listEnrollmentRequests({ isAdmin: req.user.role === 'admin', teacherNpk: req.user.sub, status });
    const page = paginateCollection(req, requests);
    res.json({ requests: page.data, pagination: page.pagination });
  } catch (error) { next(error); }
});

router.post('/recommendations', allowRoles('teacher'), trustedStaffOrigin, async (req, res, next) => {
  const parsed = z.object({
    studentNpk: z.string().trim().min(1).max(50),
    courseCode: z.string().trim().min(2).max(30),
    note: z.string().trim().max(1000).optional().default('')
  }).strict().safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Pilih Student dan course yang valid.' } });
  try {
    const { studentNpk, courseCode, note } = parsed.data;
    const accessible = await pool.request().input('npk', sql.VarChar(50), req.user.sub)
      .input('code', sql.VarChar(30), courseCode)
      .query(`SELECT TOP 1 course_code FROM dbo.courses
        WHERE course_code=@code AND status='active' AND (created_by=@npk OR assigned_trainer_npk=@npk)`);
    if (!accessible.recordset.length) return res.status(404).json({ error: { code: 'COURSE_NOT_FOUND', message: 'Course tidak ditemukan atau tidak berada dalam tanggung jawab Anda.' } });
    const requestId = await createTeacherRecommendation({ studentNpk, courseCode, teacherNpk: req.user.sub, note });
    res.status(201).json({ requestId, status: 'pending' });
  } catch (error) { next(error); }
});

router.post('/:requestId/decision', allowRoles('admin'), trustedStaffOrigin, async (req, res, next) => {
  const requestId = z.coerce.number().int().positive().safeParse(req.params.requestId);
  const parsed = z.object({ action: z.enum(['approved', 'rejected']), note: z.string().trim().max(1000).optional().default('') })
    .strict().safeParse(req.body);
  if (!requestId.success || !parsed.success || (parsed.data.action === 'rejected' && parsed.data.note.length < 3)) {
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Pilih approve atau reject. Alasan penolakan minimal 3 karakter.' } });
  }
  try {
    const decision = await decideEnrollmentRequest({ requestId: requestId.data, action: parsed.data.action, adminNpk: req.user.sub, note: parsed.data.note });
    try {
      await pool.request().input('npk', sql.VarChar(50), decision.studentNpk)
        .input('title', sql.NVarChar(180), parsed.data.action === 'approved' ? 'Akses Course Disetujui' : 'Permintaan Course Ditolak')
        .input('message', sql.NVarChar(2000), parsed.data.action === 'approved'
          ? `Akses ke course ${decision.courseTitle} (${decision.courseCode}) telah disetujui Admin. Course sekarang tersedia di My Course.`
          : `Permintaan akses ke course ${decision.courseTitle} (${decision.courseCode}) ditolak Admin. Buka detail course untuk melihat alasan dan mengajukan kembali.`)
        .input('senderNpk', sql.VarChar(50), req.user.sub)
        .input('senderName', sql.NVarChar(150), String(req.user.name || req.user.sub).slice(0, 150))
        .query(`INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
          VALUES('student',@npk,@title,@message,'course','important',@senderNpk,'admin',@senderName,0)`);
      publishNotificationEvent({ roles: ['student'], recipientNpk: decision.studentNpk, type: 'notification_created' });
    } catch (notificationError) {
      console.error('Course enrollment decision notification failed:', notificationError.code || 'NOTIFICATION_ERROR');
    }
    res.json({ requestId: requestId.data, status: parsed.data.action });
  } catch (error) { next(error); }
});

module.exports = router;
