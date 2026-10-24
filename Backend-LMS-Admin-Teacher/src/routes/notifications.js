const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate, allowRoles } = require('../middleware/auth');
const { buildLimiter } = require('../middleware/rate-limit');
const { listActiveStudentRecipients } = require('../services/student-auth');
const { isMailConfigured } = require('../services/notification-mail');
const { notificationEvents, publishNotificationEvent } = require('../services/notification-events');
const { permittedRoles, notificationInputSchema, canSendToRoles, audienceLabel, emailBatches } = require('../services/notification-rules');

const router = express.Router();
const sendLimiter = buildLimiter({
  windowMs: 60 * 60 * 1000,
  limit: 10,
  message: 'Batas pengiriman notifikasi tercapai. Coba kembali nanti.',
  keyGenerator: req => `${req.user?.role || 'unknown'}:${String(req.user?.sub || 'unknown')}`
});

function trustedWriteOrigin(req, res, next) {
  const origin = req.get('origin');
  const allowed = [env.FRONTEND_ORIGIN, env.STUDENT_APP_URL].map(value => {
    try { return new URL(value).origin; } catch { return ''; }
  });
  if (!origin || !allowed.includes(origin)) {
    return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan notifikasi ditolak.' } });
  }
  next();
}

router.use(authenticate, allowRoles(...permittedRoles));

router.get('/unread-count', async (req, res, next) => {
  try {
    const result = await pool.request().input('role', sql.VarChar(20), req.user.role)
      .input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT COUNT_BIG(*) AS unread_count FROM dbo.lms_notifications n
        WHERE n.audience_role=@role AND NOT EXISTS (
          SELECT 1 FROM dbo.lms_notification_reads r
          WHERE r.notification_id=n.notification_id AND r.recipient_role=@role AND r.recipient_npk=@npk
        )`);
    res.set('Cache-Control', 'private, no-store');
    res.json({ unreadCount: Number(result.recordset[0]?.unread_count || 0) });
  } catch (error) { next(error); }
});

const activeStreams = new Map();
router.get('/events', (req, res) => {
  const streamKey = `${req.user.role}:${req.user.sub}`;
  const current = activeStreams.get(streamKey) || 0;
  if (current >= 3) return res.status(429).json({ error: { code: 'STREAM_LIMIT', message: 'Batas koneksi realtime tercapai.' } });
  activeStreams.set(streamKey, current + 1);
  res.status(200).set({
    'Content-Type': 'text/event-stream; charset=utf-8',
    'Cache-Control': 'no-cache, no-transform',
    Connection: 'keep-alive',
    'X-Accel-Buffering': 'no'
  });
  res.flushHeaders?.();
  res.write('event: ready\ndata: {}\n\n');
  const send = event => {
    if (!event.roles.includes(req.user.role)) return;
    if (event.recipientNpk && event.recipientNpk !== String(req.user.sub)) return;
    res.write(`id: ${event.id}\nevent: notification\ndata: ${JSON.stringify({ type: event.type })}\n\n`);
  };
  const heartbeat = setInterval(() => res.write(': keep-alive\n\n'), 25000);
  const remainingSessionMs = Math.max(1000, ((Number(req.user.exp) || Math.floor(Date.now() / 1000) + 8 * 60 * 60) * 1000) - Date.now());
  const expiry = setTimeout(() => res.end(), remainingSessionMs);
  notificationEvents.on('change', send);
  res.on('close', () => {
    clearInterval(heartbeat);
    clearTimeout(expiry);
    notificationEvents.off('change', send);
    const nextCount = Math.max(0, (activeStreams.get(streamKey) || 1) - 1);
    if (nextCount) activeStreams.set(streamKey, nextCount);
    else activeStreams.delete(streamKey);
  });
});

router.get('/', async (req, res, next) => {
  const parsed = z.coerce.number().int().min(1).max(100).default(50).safeParse(req.query.limit ?? 50);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Batas notifikasi tidak valid.' } });
  try {
    const [items, count] = await Promise.all([
      pool.request().input('role', sql.VarChar(20), req.user.role).input('npk', sql.VarChar(50), req.user.sub)
        .input('limit', sql.Int, parsed.data)
        .query(`SELECT TOP (@limit) n.notification_id,n.title,n.message,n.category,n.priority,
            n.sender_npk,n.sender_name,n.sender_role,n.created_at,
            CAST(CASE WHEN r.notification_id IS NULL THEN 0 ELSE 1 END AS BIT) AS is_read
          FROM dbo.lms_notifications n
          LEFT JOIN dbo.lms_notification_reads r ON r.notification_id=n.notification_id
            AND r.recipient_role=@role AND r.recipient_npk=@npk
          WHERE n.audience_role=@role ORDER BY n.created_at DESC,n.notification_id DESC`),
      pool.request().input('role', sql.VarChar(20), req.user.role).input('npk', sql.VarChar(50), req.user.sub)
        .query(`SELECT COUNT_BIG(*) AS unread_count FROM dbo.lms_notifications n
          WHERE n.audience_role=@role AND NOT EXISTS (
            SELECT 1 FROM dbo.lms_notification_reads r
            WHERE r.notification_id=n.notification_id AND r.recipient_role=@role AND r.recipient_npk=@npk
          )`)
    ]);
    res.set('Cache-Control', 'private, no-store');
    res.json({ notifications: items.recordset, unreadCount: Number(count.recordset[0]?.unread_count || 0) });
  } catch (error) { next(error); }
});

router.post('/', allowRoles('admin', 'teacher'), trustedWriteOrigin, sendLimiter, async (req, res, next) => {
  const parsed = notificationInputSchema.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Lengkapi judul, isi, kategori, dan penerima notifikasi.' } });
  const input = parsed.data;
  const targetRoles = [...new Set(input.targetRoles)];
  if (!canSendToRoles(req.user.role, targetRoles)) {
    return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Teacher hanya dapat mengirim notifikasi kepada Student.' } });
  }
  if (input.sendEmail && !isMailConfigured()) {
    return res.status(503).json({ error: { code: 'EMAIL_UNAVAILABLE', message: 'SMTP belum dikonfigurasi; notifikasi belum dikirim.' } });
  }

  try {
    let recipients = [];
    if (input.sendEmail && targetRoles.includes('student')) {
      recipients.push(...await listActiveStudentRecipients(5001));
    }
    const employeeRoles = targetRoles.filter(role => role === 'admin' || role === 'teacher');
    if (input.sendEmail && employeeRoles.length) {
      const employeeQuery = pool.request();
      const roleParameters = employeeRoles.map((role, index) => {
        employeeQuery.input(`role${index}`, sql.VarChar(20), role);
        return `@role${index}`;
      });
      const employees = await employeeQuery.query(`SELECT TOP (5001) employee_npk AS npk,fullname,email,role
        FROM dbo.employee_accounts WHERE status='active' AND role IN (${roleParameters.join(',')})
          AND email IS NOT NULL AND LTRIM(RTRIM(email))<>'' ORDER BY employee_npk`);
      recipients.push(...employees.recordset.map(row => ({ npk: row.npk, fullname: row.fullname, email: row.email, role: row.role })));
    }
    recipients = emailBatches(recipients);
    if (input.sendEmail && recipients.length >= 5001) {
      return res.status(413).json({ error: { code: 'EMAIL_RECIPIENT_LIMIT', message: 'Jumlah penerima email melebihi batas 5.000 akun per pengiriman.' } });
    }

    const transaction = new sql.Transaction(pool);
    await transaction.begin();
    const created = [];
    try {
      for (const role of targetRoles) {
        const row = await new sql.Request(transaction)
          .input('audience', sql.VarChar(20), role)
          .input('title', sql.NVarChar(180), input.title)
          .input('message', sql.NVarChar(2000), input.message)
          .input('category', sql.VarChar(30), input.category)
          .input('priority', sql.VarChar(20), input.priority)
          .input('npk', sql.VarChar(50), req.user.sub)
          .input('senderRole', sql.VarChar(20), req.user.role)
          .input('senderName', sql.NVarChar(150), String(req.user.name || req.user.sub).slice(0, 150))
          .input('emailRequested', sql.Bit, input.sendEmail)
          .query(`INSERT INTO dbo.lms_notifications(audience_role,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
            OUTPUT INSERTED.notification_id,INSERTED.audience_role
            VALUES(@audience,@title,@message,@category,@priority,@npk,@senderRole,@senderName,@emailRequested)`);
        created.push(row.recordset[0]);
      }

      if (input.sendEmail && recipients.length) {
        const idByRole = new Map(created.map(row => [row.audience_role, row.notification_id]));
        for (let offset = 0; offset < recipients.length; offset += 500) {
          const request = new sql.Request(transaction);
          const values = recipients.slice(offset, offset + 500).map((recipient, index) => {
            const notificationId = idByRole.get(recipient.role || 'student') || idByRole.get('student');
            request.input(`notification${index}`, sql.BigInt, notificationId)
              .input(`email${index}`, sql.NVarChar(254), recipient.email)
              .input(`name${index}`, sql.NVarChar(150), String(recipient.fullname || recipient.name || recipient.npk || 'Pengguna').slice(0, 150));
            return `(@notification${index},@email${index},@name${index})`;
          });
          await request.query(`INSERT INTO dbo.lms_notification_email_outbox(notification_id,recipient_email,recipient_name)
            VALUES ${values.join(',')}`);
        }
      }
      await transaction.commit();
    } catch (error) { await transaction.rollback().catch(() => {}); throw error; }

    publishNotificationEvent({ roles: targetRoles });
    res.status(201).json({
      created: targetRoles.length,
      recipients: targetRoles.map(audienceLabel),
      emailQueued: input.sendEmail ? recipients.length : 0
    });
  } catch (error) { next(error); }
});

router.post('/read-all', trustedWriteOrigin, async (req, res, next) => {
  try {
    await pool.request().input('role', sql.VarChar(20), req.user.role).input('npk', sql.VarChar(50), req.user.sub)
      .query(`MERGE dbo.lms_notification_reads WITH (HOLDLOCK) AS target
        USING (SELECT n.notification_id,@role AS recipient_role,@npk AS recipient_npk
          FROM dbo.lms_notifications n WHERE n.audience_role=@role) AS source
        ON target.notification_id=source.notification_id AND target.recipient_role=source.recipient_role AND target.recipient_npk=source.recipient_npk
        WHEN NOT MATCHED THEN INSERT(notification_id,recipient_role,recipient_npk)
          VALUES(source.notification_id,source.recipient_role,source.recipient_npk);`);
    publishNotificationEvent({ roles: [req.user.role], recipientNpk: req.user.sub });
    res.json({ read: true });
  } catch (error) { next(error); }
});

router.post('/:id/read', trustedWriteOrigin, async (req, res, next) => {
  const id = z.coerce.number().int().positive().max(Number.MAX_SAFE_INTEGER).safeParse(req.params.id);
  if (!id.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'ID notifikasi tidak valid.' } });
  try {
    const result = await pool.request().input('id', sql.BigInt, id.data)
      .input('role', sql.VarChar(20), req.user.role).input('npk', sql.VarChar(50), req.user.sub)
      .query(`IF NOT EXISTS(SELECT 1 FROM dbo.lms_notifications WHERE notification_id=@id AND audience_role=@role)
        SELECT CAST(0 AS BIT) AS available;
      ELSE BEGIN
        MERGE dbo.lms_notification_reads WITH (HOLDLOCK) AS target
        USING (SELECT @id AS notification_id,@role AS recipient_role,@npk AS recipient_npk) AS source
          ON target.notification_id=source.notification_id AND target.recipient_role=source.recipient_role AND target.recipient_npk=source.recipient_npk
        WHEN NOT MATCHED THEN INSERT(notification_id,recipient_role,recipient_npk) VALUES(source.notification_id,source.recipient_role,source.recipient_npk);
        SELECT CAST(1 AS BIT) AS available;
      END`);
    if (!result.recordset[0]?.available) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Notifikasi tidak ditemukan.' } });
    publishNotificationEvent({ roles: [req.user.role], recipientNpk: req.user.sub });
    res.json({ read: true });
  } catch (error) { next(error); }
});

module.exports = router;
