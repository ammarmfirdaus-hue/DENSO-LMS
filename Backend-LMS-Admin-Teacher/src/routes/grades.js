const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');
const { getActiveStudentRecipient } = require('../services/student-auth');
const { isMailConfigured } = require('../services/notification-mail');
const { publishNotificationEvent } = require('../services/notification-events');
const { syncStudentGradeNow } = require('../services/student-sync');
const env = require('../config/env');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));

function queueStudentGradeSync(npk, courseCode) {
  syncStudentGradeNow(npk, courseCode).catch(error => console.error('Student grade sync failed:', error.message));
}

function trustedWriteOrigin(req, res, next) {
  const origin = req.get('origin');
  const allowed = [env.FRONTEND_ORIGIN, env.STUDENT_APP_URL].map(value => {
    try { return new URL(value).origin; } catch { return ''; }
  });
  if (!origin || !allowed.includes(origin)) return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan nilai ditolak.' } });
  next();
}

const identitySchema = z.object({
  npk: z.string().trim().min(1).max(50).regex(/^[A-Za-z0-9._-]+$/),
  courseCode: z.string().trim().min(1).max(30).regex(/^[A-Za-z0-9._-]+$/)
});
const gradeSchema = z.object({
  score: z.coerce.number().min(0).max(100),
  feedback: z.string().trim().max(1000).default('')
}).strict();
const notificationSchema = z.object({
  title: z.string().trim().min(3).max(180).regex(/^[^\r\n\0]+$/),
  message: z.string().trim().min(1).max(2000),
  sendEmail: z.boolean().default(false)
}).strict();

function bindIdentity(request, npk, courseCode, user) {
  return request.input('npk', sql.VarChar(50), npk)
    .input('courseCode', sql.VarChar(30), courseCode)
    .input('isAdmin', sql.Bit, user.role === 'admin')
    .input('teacher', sql.VarChar(50), user.sub);
}

router.get('/', async (req, res, next) => {
  try {
    const request = pool.request().input('isAdmin', sql.Bit, req.user.role === 'admin')
      .input('teacher', sql.VarChar(50), req.user.sub);
    const npk = String(req.query.npk || '').trim();
    const course = String(req.query.course || '').trim();
    let filter = '';
    if (npk) { request.input('npk', sql.VarChar(50), npk); filter += ' AND p.student_npk=@npk'; }
    if (course) { request.input('course', sql.VarChar(30), course); filter += ' AND p.course_code=@course'; }
    const result = await request.query(`SELECT TOP 500 p.student_npk,p.course_code,c.title AS course_title,
      p.progress,p.enrollment_status,p.score,p.quiz_score,p.grade_feedback,p.graded_by,p.graded_at,p.source_updated_at,p.synced_at
      FROM dbo.student_progress_snapshots p LEFT JOIN dbo.courses c ON c.course_code=p.course_code
      WHERE (@isAdmin=1 OR c.created_by=@teacher OR c.assigned_trainer_npk=@teacher) ${filter}
      ORDER BY p.source_updated_at DESC,p.student_npk,p.course_code`);
    const page = paginateCollection(req, result.recordset);
    res.json({ grades: page.data, source: 'student-mysql-sync', ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

router.patch('/:npk/:courseCode', trustedWriteOrigin, async (req, res, next) => {
  const identity = identitySchema.safeParse(req.params);
  const grade = gradeSchema.safeParse(req.body);
  if (!identity.success || !grade.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Nilai harus antara 0 dan 100.' } });
  try {
    const request = bindIdentity(pool.request(), identity.data.npk, identity.data.courseCode, req.user)
      .input('score', sql.Decimal(5, 2), grade.data.score)
      .input('feedback', sql.NVarChar(1000), grade.data.feedback || null)
      .input('gradedBy', sql.VarChar(50), req.user.sub);
    const result = await request.query(`UPDATE p SET score=@score,grade_feedback=@feedback,
        graded_by=@gradedBy,graded_at=SYSUTCDATETIME(),synced_at=SYSUTCDATETIME()
      FROM dbo.student_progress_snapshots p JOIN dbo.courses c ON c.course_code=p.course_code
      WHERE p.student_npk=@npk AND p.course_code=@courseCode
        AND (@isAdmin=1 OR c.created_by=@teacher OR c.assigned_trainer_npk=@teacher);
      SELECT @@ROWCOUNT AS affected;`);
    if (!result.recordset[0]?.affected) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Data nilai tidak ditemukan atau course di luar akses Anda.' } });
    queueStudentGradeSync(identity.data.npk, identity.data.courseCode);
    res.json({ saved: true, score: grade.data.score });
  } catch (error) { next(error); }
});

router.delete('/:npk/:courseCode', allowRoles('admin'), trustedWriteOrigin, async (req, res, next) => {
  const identity = identitySchema.safeParse(req.params);
  if (!identity.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Identitas nilai tidak valid.' } });
  try {
    const result = await pool.request().input('npk', sql.VarChar(50), identity.data.npk)
      .input('courseCode', sql.VarChar(30), identity.data.courseCode)
      .query(`UPDATE dbo.student_progress_snapshots SET score=NULL,grade_feedback=NULL,graded_by=NULL,graded_at=NULL,synced_at=SYSUTCDATETIME()
        WHERE student_npk=@npk AND course_code=@courseCode; SELECT @@ROWCOUNT AS affected;`);
    if (!result.recordset[0]?.affected) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Data nilai tidak ditemukan.' } });
    queueStudentGradeSync(identity.data.npk, identity.data.courseCode);
    res.json({ deleted: true });
  } catch (error) { next(error); }
});

router.post('/:npk/:courseCode/notify', trustedWriteOrigin, async (req, res, next) => {
  const identity = identitySchema.safeParse(req.params);
  const notification = notificationSchema.safeParse(req.body);
  if (!identity.success || !notification.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Lengkapi judul dan pesan notifikasi.' } });
  if (notification.data.sendEmail && !isMailConfigured()) return res.status(503).json({ error: { code: 'EMAIL_UNAVAILABLE', message: 'SMTP belum dikonfigurasi.' } });
  try {
    const recipient = await getActiveStudentRecipient(identity.data.npk);
    if (!recipient) return res.status(404).json({ error: { code: 'STUDENT_NOT_FOUND', message: 'Student aktif tidak ditemukan.' } });
    if (notification.data.sendEmail && !recipient.email) return res.status(422).json({ error: { code: 'EMAIL_MISSING', message: 'Student belum memiliki alamat email.' } });
    const access = await bindIdentity(pool.request(), identity.data.npk, identity.data.courseCode, req.user)
      .query(`SELECT TOP 1 p.score,c.title AS course_title FROM dbo.student_progress_snapshots p
        JOIN dbo.courses c ON c.course_code=p.course_code
        WHERE p.student_npk=@npk AND p.course_code=@courseCode
          AND (@isAdmin=1 OR c.created_by=@teacher OR c.assigned_trainer_npk=@teacher)`);
    if (!access.recordset.length) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan atau di luar akses Anda.' } });

    const transaction = new sql.Transaction(pool);
    await transaction.begin();
    let notificationId;
    try {
      const created = await new sql.Request(transaction)
        .input('npk', sql.VarChar(50), identity.data.npk)
        .input('title', sql.NVarChar(180), notification.data.title)
        .input('message', sql.NVarChar(2000), notification.data.message)
        .input('senderNpk', sql.VarChar(50), req.user.sub)
        .input('senderRole', sql.VarChar(20), req.user.role)
        .input('senderName', sql.NVarChar(150), String(req.user.name || req.user.sub).slice(0, 150))
        .input('email', sql.Bit, notification.data.sendEmail)
        .query(`INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
          OUTPUT INSERTED.notification_id VALUES('student',@npk,@title,@message,'assessment','normal',@senderNpk,@senderRole,@senderName,@email)`);
      notificationId = created.recordset[0].notification_id;
      if (notification.data.sendEmail) {
        await new sql.Request(transaction).input('id', sql.BigInt, notificationId)
          .input('email', sql.NVarChar(254), recipient.email)
          .input('name', sql.NVarChar(150), String(recipient.fullname || recipient.npk).slice(0, 150))
          .query(`INSERT INTO dbo.lms_notification_email_outbox(notification_id,recipient_email,recipient_name) VALUES(@id,@email,@name)`);
      }
      await transaction.commit();
    } catch (error) { await transaction.rollback().catch(() => {}); throw error; }

    publishNotificationEvent({ roles: ['student'], recipientNpk: identity.data.npk, type: 'notification_created' });
    res.status(201).json({ sent: true, emailQueued: notification.data.sendEmail ? 1 : 0, notificationId });
  } catch (error) { next(error); }
});

module.exports = router;
