const express = require('express');
const { z } = require('zod');
const fs = require('node:fs/promises');
const path = require('node:path');
const crypto = require('node:crypto');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate } = require('../middleware/auth');
const { forumImageUpload } = require('../middleware/forum-upload');
const { forumUploadLimiter } = require('../middleware/rate-limit');
const { studentHasCourseAccess, studentHasSessionAccess, getStudentIdentity } = require('../services/student-auth');
const { forumEvents, publishForumEvent } = require('../services/forum-events');
const { parseForumImageBuffer } = require('../services/forum-image');

const router = express.Router();
router.use(authenticate);
const uploadDirectory = path.resolve(__dirname, '../../uploads/forum');
const activeForumStreams = new Map();
const courseCodeSchema = z.string().trim().min(2).max(30);
const sessionCodeSchema = z.string().trim().min(1).max(50);
const roleOf = req => req.user?.role;
const isEmployee = req => ['admin', 'teacher'].includes(roleOf(req));

function trustedWriteOrigin(req, res, next) {
  const origin = req.get('origin');
  const allowed = [env.FRONTEND_ORIGIN, env.STUDENT_APP_URL].map(value => {
    try { return new URL(value).origin; } catch { return ''; }
  });
  if (!origin || !allowed.includes(origin)) return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan forum ditolak.' } });
  next();
}

async function canAccessCourse(req, courseCode) {
  if (isEmployee(req)) {
    const result = await pool.request()
      .input('code', sql.VarChar(30), courseCode)
      .input('npk', sql.VarChar(50), req.user.sub)
      .input('role', sql.VarChar(20), roleOf(req))
      .input('isAdmin', sql.Bit, roleOf(req) === 'admin')
      .query(`SELECT 1 AS allowed FROM dbo.courses WHERE course_code=@code AND status='active'
        AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)
        AND EXISTS(SELECT 1 FROM dbo.employee_accounts ea WHERE ea.employee_npk=@npk AND ea.role=@role AND ea.status='active')`);
    return result.recordset.length > 0;
  }
  if (roleOf(req) === 'student') return studentHasCourseAccess(Number(req.user.student_id), courseCode);
  return false;
}

async function canAccessSession(req, courseCode, sessionCode) {
  if (roleOf(req) === 'student') return studentHasSessionAccess(Number(req.user.student_id), courseCode, sessionCode);
  if (!isEmployee(req)) return false;
  const result = await pool.request().input('course', sql.VarChar(30), courseCode)
    .input('session', sql.VarChar(50), sessionCode)
    .input('npk', sql.VarChar(50), req.user.sub).input('role', sql.VarChar(20), roleOf(req))
    .input('isAdmin', sql.Bit, roleOf(req) === 'admin')
    .query(`SELECT 1 AS allowed FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id
      WHERE c.course_code=@course AND CONVERT(VARCHAR(50),s.session_id)=@session
      AND s.status='active' AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)
      AND EXISTS(SELECT 1 FROM dbo.employee_accounts ea WHERE ea.employee_npk=@npk AND ea.role=@role AND ea.status='active')`);
  return result.recordset.length > 0;
}

async function getIdentity(req) {
  if (roleOf(req) === 'student') {
    const student = await getStudentIdentity(Number(req.user.student_id));
    if (!student) return null;
    return { npk: student.npk, name: student.fullname, role: 'student' };
  }
  if (isEmployee(req)) {
    const result = await pool.request().input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT employee_npk AS npk,fullname AS name,role FROM dbo.employee_accounts
        WHERE employee_npk=@npk AND status='active' AND role IN ('admin','teacher')`);
    return result.recordset[0] || null;
  }
  return null;
}

function parseImage(file) {
  if (!file) return null;
  const parsed = parseForumImageBuffer(file.mimetype, file.buffer);
  return parsed ? { ...parsed, name: `${crypto.randomUUID()}${parsed.extension}` } : null;
}

async function storeImage(file) {
  if (!file) return null;
  const parsed = parseImage(file);
  if (!parsed) {
    const error = new Error('Isi file tidak sesuai dengan format gambar yang diizinkan.');
    error.status = 415;
    throw error;
  }
  await fs.mkdir(uploadDirectory, { recursive: true });
  await fs.writeFile(path.join(uploadDirectory, parsed.name), file.buffer, { flag: 'wx', mode: 0o640 });
  return `/forum/uploads/${parsed.name}`;
}

function inputError(res, message) {
  return res.status(400).json({ error: { code: 'INVALID_INPUT', message } });
}

function sessionInput(req, res, next) {
  const courseCode = courseCodeSchema.safeParse(req.params.courseCode || req.body?.courseCode || req.query.courseCode);
  const sessionCode = sessionCodeSchema.safeParse(req.params.sessionCode || req.body?.sessionCode || req.query.sessionCode);
  if (!courseCode.success || !sessionCode.success) return inputError(res, 'Course atau sesi forum tidak valid.');
  req.forumContext = { courseCode: courseCode.data, sessionCode: sessionCode.data };
  next();
}

async function getThreadContext(req, threadId) {
  const result = await pool.request().input('id', sql.BigInt, threadId)
    .query(`SELECT thread_id,course_code,session_code,status FROM dbo.forum_threads WHERE thread_id=@id`);
  const thread = result.recordset[0];
  if (!thread || !(await canAccessCourse(req, thread.course_code))) return null;
  if (roleOf(req) === 'student' && !(await canAccessSession(req, thread.course_code, thread.session_code || ''))) return null;
  return thread;
}

const threadSummarySql = `SELECT t.thread_id,t.course_code,t.session_code,t.title,t.body,t.attachment_path,
  COALESCE(NULLIF(t.author_name,N''),t.author_npk) AS author_name,COALESCE(t.author_role,'student') AS author_role,
  t.author_npk,t.status,t.created_at,
  (SELECT COUNT_BIG(*) FROM dbo.forum_replies r WHERE r.thread_id=t.thread_id AND r.status='active') AS replies_count,
  (SELECT COUNT_BIG(*) FROM dbo.forum_likes l WHERE l.target_type='thread' AND l.target_id=t.thread_id) AS likes_count,
  CAST(CASE WHEN EXISTS(SELECT 1 FROM dbo.forum_likes l WHERE l.target_type='thread' AND l.target_id=t.thread_id AND l.author_npk=@viewer) THEN 1 ELSE 0 END AS BIT) AS liked_by_me
  FROM dbo.forum_threads t`;

router.get('/events', async (req, res) => {
  const parsed = z.object({ courseCode: courseCodeSchema, sessionCode: sessionCodeSchema }).safeParse(req.query);
  if (!parsed.success || !(await canAccessSession(req, parsed.data.courseCode, parsed.data.sessionCode))) {
    return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi forum tidak ditemukan.' } });
  }
  const streamKey = `${roleOf(req)}:${req.user.sub}`;
  const streamCount = activeForumStreams.get(streamKey) || 0;
  if (streamCount >= 3) return res.status(429).json({ error: { code: 'STREAM_LIMIT', message: 'Batas koneksi realtime tercapai.' } });
  activeForumStreams.set(streamKey, streamCount + 1);
  res.status(200).set({
    'Content-Type': 'text/event-stream; charset=utf-8',
    'Cache-Control': 'no-cache, no-transform',
    Connection: 'keep-alive', 'X-Accel-Buffering': 'no'
  });
  res.flushHeaders?.();
  res.write(`event: ready\ndata: ${JSON.stringify({ courseCode: parsed.data.courseCode, sessionCode: parsed.data.sessionCode })}\n\n`);
  const send = event => {
    if (event.courseCode !== parsed.data.courseCode || event.sessionCode !== parsed.data.sessionCode) return;
    res.write(`id: ${event.id}\nevent: forum\ndata: ${JSON.stringify(event)}\n\n`);
  };
  const heartbeat = setInterval(() => res.write(': keep-alive\n\n'), 25000);
  forumEvents.on('change', send);
  const remainingSessionMs = Math.max(1000, ((Number(req.user.exp) || Math.floor(Date.now() / 1000) + 8 * 60 * 60) * 1000) - Date.now());
  const sessionExpiry = setTimeout(() => res.end(), remainingSessionMs);
  res.on('close', () => {
    clearInterval(heartbeat); clearTimeout(sessionExpiry); forumEvents.off('change', send);
    const remaining = (activeForumStreams.get(streamKey) || 1) - 1;
    if (remaining > 0) activeForumStreams.set(streamKey, remaining); else activeForumStreams.delete(streamKey);
  });
});

router.get('/management/courses', async (req, res, next) => {
  if (!isEmployee(req)) return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses hanya untuk admin dan teacher.' } });
  try {
    const result = await pool.request().input('npk', sql.VarChar(50), req.user.sub)
      .input('role', sql.VarChar(20), roleOf(req))
      .input('isAdmin', sql.Bit, roleOf(req) === 'admin')
      .query(`SELECT c.course_code,c.title AS course_title,s.session_id AS session_code,s.title AS session_title,
        CAST(COALESCE(fs.is_open,1) AS BIT) AS is_open
        FROM dbo.courses c JOIN dbo.course_sessions s ON s.course_id=c.course_id
        LEFT JOIN dbo.forum_session_settings fs ON fs.course_code=c.course_code AND fs.session_code=CONVERT(VARCHAR(50),s.session_id)
        WHERE c.status='active' AND s.status='active' AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)
        AND EXISTS(SELECT 1 FROM dbo.employee_accounts ea WHERE ea.employee_npk=@npk AND ea.role=@role AND ea.status='active')
        ORDER BY c.title,s.sort_order,s.session_id`);
    res.json({ sessions: result.recordset });
  } catch (error) { next(error); }
});

router.get('/management/threads', async (req, res, next) => {
  if (!isEmployee(req)) return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses hanya untuk admin dan teacher.' } });
  const parsed = z.object({ courseCode: courseCodeSchema, sessionCode: sessionCodeSchema }).safeParse(req.query);
  if (!parsed.success) return inputError(res, 'Pilih course dan sesi terlebih dahulu.');
  try {
    if (!(await canAccessSession(req, parsed.data.courseCode, parsed.data.sessionCode))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi tidak ditemukan.' } });
    const result = await pool.request().input('course', sql.VarChar(30), parsed.data.courseCode)
      .input('session', sql.VarChar(50), parsed.data.sessionCode).input('viewer', sql.VarChar(50), req.user.sub)
      .query(`${threadSummarySql} WHERE t.course_code=@course AND t.session_code=@session ORDER BY t.created_at DESC,t.thread_id DESC OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY`);
    res.json({ threads: result.recordset });
  } catch (error) { next(error); }
});

router.get('/courses/:courseCode/sessions/:sessionCode', sessionInput, async (req, res, next) => {
  try {
    const { courseCode, sessionCode } = req.forumContext;
    if (!(await canAccessSession(req, courseCode, sessionCode))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi forum tidak ditemukan.' } });
    const status = await pool.request().input('course', sql.VarChar(30), courseCode).input('session', sql.VarChar(50), sessionCode)
      .query(`SELECT CAST(COALESCE((SELECT TOP 1 is_open FROM dbo.forum_session_settings WHERE course_code=@course AND session_code=@session),1) AS BIT) AS is_open`);
    const request = pool.request().input('course', sql.VarChar(30), courseCode).input('session', sql.VarChar(50), sessionCode)
      .input('viewer', sql.VarChar(50), req.user.sub).input('isStudent', sql.Bit, roleOf(req) === 'student');
    const result = await request.query(`${threadSummarySql} WHERE t.course_code=@course AND t.session_code=@session
      AND (@isStudent=0 OR t.status IN ('visible','locked')) ORDER BY t.created_at DESC,t.thread_id DESC OFFSET 0 ROWS FETCH NEXT 100 ROWS ONLY`);
    res.json({ isOpen: Boolean(status.recordset[0]?.is_open), threads: result.recordset });
  } catch (error) { next(error); }
});

router.patch('/management/sessions/:courseCode/:sessionCode', trustedWriteOrigin, sessionInput, async (req, res, next) => {
  if (!isEmployee(req)) return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses hanya untuk admin dan teacher.' } });
  const parsed = z.object({ isOpen: z.boolean() }).safeParse(req.body);
  if (!parsed.success) return inputError(res, 'Status forum tidak valid.');
  try {
    const { courseCode, sessionCode } = req.forumContext;
    if (!(await canAccessSession(req, courseCode, sessionCode))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi tidak ditemukan.' } });
    const transaction = new sql.Transaction(pool);
    await transaction.begin();
    try {
      await new sql.Request(transaction).input('course', sql.VarChar(30), courseCode).input('session', sql.VarChar(50), sessionCode)
        .input('open', sql.Bit, parsed.data.isOpen).input('npk', sql.VarChar(50), req.user.sub)
        .query(`UPDATE dbo.forum_session_settings WITH (UPDLOCK,SERIALIZABLE) SET is_open=@open,updated_by=@npk,updated_at=SYSUTCDATETIME()
          WHERE course_code=@course AND session_code=@session;
          IF @@ROWCOUNT=0 INSERT INTO dbo.forum_session_settings(course_code,session_code,is_open,updated_by) VALUES(@course,@session,@open,@npk);`);
      await transaction.commit();
    } catch (error) { await transaction.rollback().catch(() => {}); throw error; }
    publishForumEvent({ courseCode, sessionCode, type: 'session_status' });
    res.json({ isOpen: parsed.data.isOpen });
  } catch (error) { next(error); }
});

router.post('/management/threads', trustedWriteOrigin, forumUploadLimiter, forumImageUpload.single('image'), async (req, res, next) => {
  if (!isEmployee(req)) return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses hanya untuk admin dan teacher.' } });
  const input = z.object({ courseCode: courseCodeSchema, sessionCode: sessionCodeSchema, title: z.string().trim().min(3).max(200), body: z.string().trim().min(1).max(2000) }).safeParse(req.body);
  if (!input.success) return inputError(res, 'Judul dan isi topik wajib diisi.');
  let imagePath;
  try {
    const { courseCode, sessionCode, title, body } = input.data;
    if (!(await canAccessSession(req, courseCode, sessionCode))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi tidak ditemukan.' } });
    const identity = await getIdentity(req);
    if (!identity) return res.status(401).json({ error: { code: 'UNAUTHENTICATED', message: 'Akun tidak aktif.' } });
    imagePath = await storeImage(req.file);
    const result = await pool.request().input('npk', sql.VarChar(50), identity.npk).input('name', sql.NVarChar(150), identity.name)
      .input('role', sql.VarChar(20), identity.role).input('course', sql.VarChar(30), courseCode).input('session', sql.VarChar(50), sessionCode)
      .input('title', sql.NVarChar(200), title).input('body', sql.NVarChar(2000), body).input('image', sql.NVarChar(260), imagePath)
      .query(`INSERT INTO dbo.forum_threads(author_npk,author_name,author_role,course_code,session_code,title,body,attachment_path,status)
        OUTPUT INSERTED.thread_id VALUES(@npk,@name,@role,@course,@session,@title,@body,@image,'visible')`);
    publishForumEvent({ courseCode, sessionCode, type: 'thread_created', threadId: result.recordset[0].thread_id });
    res.status(201).json({ thread_id: result.recordset[0].thread_id });
  } catch (error) { if (imagePath) await fs.unlink(path.join(uploadDirectory, path.basename(imagePath))).catch(() => {}); next(error); }
});

router.post('/courses/:courseCode/sessions/:sessionCode/threads', trustedWriteOrigin, sessionInput, forumUploadLimiter, forumImageUpload.single('image'), async (req, res, next) => {
  if (roleOf(req) !== 'student') return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Endpoint ini hanya untuk student.' } });
  const input = z.object({ title: z.string().trim().min(3).max(200), body: z.string().trim().min(1).max(2000) }).safeParse(req.body);
  if (!input.success) return inputError(res, 'Judul dan isi diskusi wajib diisi.');
  let imagePath;
  try {
    const { courseCode, sessionCode } = req.forumContext;
    if (!(await canAccessSession(req, courseCode, sessionCode))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Sesi tidak ditemukan.' } });
    const open = await pool.request().input('course', sql.VarChar(30), courseCode).input('session', sql.VarChar(50), sessionCode)
      .query(`SELECT COALESCE((SELECT TOP 1 is_open FROM dbo.forum_session_settings WHERE course_code=@course AND session_code=@session),1) AS is_open`);
    if (!open.recordset[0]?.is_open) return res.status(423).json({ error: { code: 'FORUM_CLOSED', message: 'Forum sesi ini sedang ditutup.' } });
    const identity = await getIdentity(req);
    if (!identity) return res.status(401).json({ error: { code: 'UNAUTHENTICATED', message: 'Sesi student tidak aktif.' } });
    imagePath = await storeImage(req.file);
    const result = await pool.request().input('npk', sql.VarChar(50), identity.npk).input('name', sql.NVarChar(150), identity.name)
      .input('role', sql.VarChar(20), identity.role).input('course', sql.VarChar(30), courseCode).input('session', sql.VarChar(50), sessionCode)
      .input('title', sql.NVarChar(200), input.data.title).input('body', sql.NVarChar(2000), input.data.body).input('image', sql.NVarChar(260), imagePath)
      .query(`INSERT INTO dbo.forum_threads(author_npk,author_name,author_role,course_code,session_code,title,body,attachment_path,status)
        OUTPUT INSERTED.thread_id VALUES(@npk,@name,@role,@course,@session,@title,@body,@image,'visible')`);
    publishForumEvent({ courseCode, sessionCode, type: 'thread_created', threadId: result.recordset[0].thread_id });
    res.status(201).json({ thread_id: result.recordset[0].thread_id });
  } catch (error) { if (imagePath) await fs.unlink(path.join(uploadDirectory, path.basename(imagePath))).catch(() => {}); next(error); }
});

router.patch('/:id/status', trustedWriteOrigin, async (req, res, next) => {
  if (!isEmployee(req)) return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses hanya untuk admin dan teacher.' } });
  const id = z.coerce.number().int().positive().max(Number.MAX_SAFE_INTEGER).safeParse(req.params.id);
  const parsed = z.object({ status: z.enum(['visible', 'hidden', 'locked']) }).safeParse(req.body);
  if (!id.success || !parsed.success) return inputError(res, 'ID atau status topik tidak valid.');
  try {
    const thread = await getThreadContext(req, id.data);
    if (!thread) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Topik tidak ditemukan.' } });
    await pool.request().input('id', sql.BigInt, id.data).input('status', sql.VarChar(20), parsed.data.status)
      .query(`UPDATE dbo.forum_threads SET status=@status WHERE thread_id=@id`);
    publishForumEvent({ courseCode: thread.course_code, sessionCode: thread.session_code, type: 'thread_status', threadId: id.data });
    res.json({ updated: true });
  } catch (error) { next(error); }
});

router.get('/threads/:id', async (req, res, next) => {
  const id = z.coerce.number().int().positive().max(Number.MAX_SAFE_INTEGER).safeParse(req.params.id);
  if (!id.success) return inputError(res, 'ID diskusi tidak valid.');
  try {
    const thread = await getThreadContext(req, id.data);
    if (!thread || (roleOf(req) === 'student' && !['visible', 'locked'].includes(thread.status))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Diskusi tidak ditemukan.' } });
    const header = await pool.request().input('id', sql.BigInt, id.data).input('viewer', sql.VarChar(50), req.user.sub)
      .query(`${threadSummarySql} WHERE t.thread_id=@id`);
    const replies = await pool.request().input('id', sql.BigInt, id.data).input('viewer', sql.VarChar(50), req.user.sub)
      .query(`SELECT r.reply_id,r.thread_id,r.author_npk,r.author_name,r.author_role,r.body,r.attachment_path,r.created_at,
        (SELECT COUNT_BIG(*) FROM dbo.forum_likes l WHERE l.target_type='reply' AND l.target_id=r.reply_id) AS likes_count,
        CAST(CASE WHEN EXISTS(SELECT 1 FROM dbo.forum_likes l WHERE l.target_type='reply' AND l.target_id=r.reply_id AND l.author_npk=@viewer) THEN 1 ELSE 0 END AS BIT) AS liked_by_me
        FROM dbo.forum_replies r WHERE r.thread_id=@id AND r.status='active'
          AND r.reply_id IN (SELECT TOP 200 latest.reply_id FROM dbo.forum_replies latest
            WHERE latest.thread_id=@id AND latest.status='active' ORDER BY latest.created_at DESC,latest.reply_id DESC)
        ORDER BY r.created_at,r.reply_id`);
    res.json({ thread: header.recordset[0], replies: replies.recordset });
  } catch (error) { next(error); }
});

router.post('/threads/:id/replies', trustedWriteOrigin, forumUploadLimiter, forumImageUpload.single('image'), async (req, res, next) => {
  const id = z.coerce.number().int().positive().max(Number.MAX_SAFE_INTEGER).safeParse(req.params.id);
  const input = z.object({ body: z.string().trim().min(1).max(2000) }).safeParse(req.body);
  if (!id.success || !input.success) return inputError(res, 'Isi balasan wajib diisi dan maksimal 2.000 karakter.');
  let imagePath;
  try {
    const thread = await getThreadContext(req, id.data);
    if (!thread || !['student', 'admin', 'teacher'].includes(roleOf(req))) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Diskusi tidak ditemukan.' } });
    if (thread.status !== 'visible') return res.status(423).json({ error: { code: 'THREAD_CLOSED', message: 'Diskusi ini sudah ditutup untuk balasan.' } });
    const open = await pool.request().input('course', sql.VarChar(30), thread.course_code).input('session', sql.VarChar(50), thread.session_code || '')
      .query(`SELECT COALESCE((SELECT TOP 1 is_open FROM dbo.forum_session_settings WHERE course_code=@course AND session_code=@session),1) AS is_open`);
    if (!open.recordset[0]?.is_open) return res.status(423).json({ error: { code: 'FORUM_CLOSED', message: 'Forum sesi ini sedang ditutup.' } });
    const identity = await getIdentity(req);
    if (!identity) return res.status(401).json({ error: { code: 'UNAUTHENTICATED', message: 'Sesi tidak aktif.' } });
    imagePath = await storeImage(req.file);
    await pool.request().input('id', sql.BigInt, id.data).input('npk', sql.VarChar(50), identity.npk)
      .input('name', sql.NVarChar(150), identity.name).input('role', sql.VarChar(20), identity.role)
      .input('body', sql.NVarChar(2000), input.data.body).input('image', sql.NVarChar(260), imagePath)
      .query(`INSERT INTO dbo.forum_replies(thread_id,author_npk,author_name,author_role,body,attachment_path)
        VALUES(@id,@npk,@name,@role,@body,@image)`);
    publishForumEvent({ courseCode: thread.course_code, sessionCode: thread.session_code, type: 'reply_created', threadId: id.data });
    res.status(201).json({ created: true });
  } catch (error) { if (imagePath) await fs.unlink(path.join(uploadDirectory, path.basename(imagePath))).catch(() => {}); next(error); }
});

router.post('/likes', trustedWriteOrigin, async (req, res, next) => {
  const input = z.object({ targetType: z.enum(['thread', 'reply']), targetId: z.coerce.number().int().positive().max(Number.MAX_SAFE_INTEGER) }).safeParse(req.body);
  if (!input.success || !['student', 'admin', 'teacher'].includes(roleOf(req))) return inputError(res, 'Data like tidak valid.');
  try {
    let threadId = input.data.targetId;
    if (input.data.targetType === 'reply') {
      const parent = await pool.request().input('id', sql.BigInt, input.data.targetId).query(`SELECT thread_id FROM dbo.forum_replies WHERE reply_id=@id AND status='active'`);
      if (!parent.recordset.length) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Balasan tidak ditemukan.' } });
      threadId = parent.recordset[0].thread_id;
    }
    const thread = await getThreadContext(req, threadId);
    if (!thread || (roleOf(req) === 'student' && thread.status === 'hidden')) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Diskusi tidak ditemukan.' } });
    const transaction = new sql.Transaction(pool);
    await transaction.begin();
    let result;
    try {
      result = await new sql.Request(transaction).input('type', sql.VarChar(10), input.data.targetType).input('target', sql.BigInt, input.data.targetId)
      .input('npk', sql.VarChar(50), req.user.sub)
      .query(`
        IF EXISTS(SELECT 1 FROM dbo.forum_likes WITH (UPDLOCK,HOLDLOCK) WHERE target_type=@type AND target_id=@target AND author_npk=@npk)
          DELETE FROM dbo.forum_likes WHERE target_type=@type AND target_id=@target AND author_npk=@npk;
        ELSE INSERT INTO dbo.forum_likes(target_type,target_id,author_npk) VALUES(@type,@target,@npk);
        SELECT CAST(CASE WHEN EXISTS(SELECT 1 FROM dbo.forum_likes WHERE target_type=@type AND target_id=@target AND author_npk=@npk) THEN 1 ELSE 0 END AS BIT) AS liked,
          (SELECT COUNT_BIG(*) FROM dbo.forum_likes WHERE target_type=@type AND target_id=@target) AS likes_count;`);
      await transaction.commit();
    } catch (error) { await transaction.rollback().catch(() => {}); throw error; }
    publishForumEvent({ courseCode: thread.course_code, sessionCode: thread.session_code, type: 'like_changed', threadId });
    res.json(result.recordset[result.recordset.length - 1]);
  } catch (error) { next(error); }
});

router.get('/uploads/:filename', async (req, res, next) => {
  const filename = z.string().regex(/^[0-9a-f-]{36}\.(jpg|png|gif|webp)$/i).safeParse(req.params.filename);
  if (!filename.success) return res.sendStatus(404);
  try {
    const publicPath = `/forum/uploads/${filename.data}`;
    const found = await pool.request().input('path', sql.NVarChar(260), publicPath)
      .query(`SELECT TOP 1 course_code,session_code,status FROM dbo.forum_threads WHERE attachment_path=@path
        UNION ALL SELECT TOP 1 t.course_code,t.session_code,t.status FROM dbo.forum_replies r JOIN dbo.forum_threads t ON t.thread_id=r.thread_id WHERE r.attachment_path=@path`);
    const attachment = found.recordset[0];
    if (!attachment || (roleOf(req) === 'student' && !['visible', 'locked'].includes(attachment.status))
      || !(await canAccessCourse(req, attachment.course_code))) return res.sendStatus(404);
    if (roleOf(req) === 'student' && !(await canAccessSession(req, attachment.course_code, attachment.session_code || ''))) return res.sendStatus(404);
    const filePath = path.join(uploadDirectory, filename.data);
    await fs.access(filePath);
    res.set({ 'Cache-Control': 'private, max-age=300', 'X-Content-Type-Options': 'nosniff' });
    res.type(path.extname(filePath));
    res.sendFile(filePath, error => { if (error && !res.headersSent) next(error); });
  } catch (error) { if (error.code === 'ENOENT') return res.sendStatus(404); next(error); }
});

module.exports = router;
