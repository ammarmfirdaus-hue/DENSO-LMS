const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate } = require('../middleware/auth');
const { getStudentIdentity } = require('../services/student-auth');
const { supportChatEvents, publishSupportChatEvent } = require('../services/support-chat-events');

const router = express.Router();
router.use(authenticate);

const activeChatStreams = new Map();
const PRESENCE_TTL_SECONDS = 45;
const PRESENCE_CACHE_MS = 5000;
let presenceCache = { expiresAt: 0, data: { online: false, online_count: 0, admins: [] } };

const messageBodySchema = z.object({
  student_id: z.coerce.number().int().positive().optional(),
  message_text: z.string().trim().min(1, 'Pesan tidak boleh kosong.').max(1000, 'Pesan maksimal 1000 karakter.'),
});

function roleOf(req) {
  return String(req.user?.role || '').toLowerCase();
}

function isAdmin(req) {
  return roleOf(req) === 'admin';
}

function isStudent(req) {
  return roleOf(req) === 'student' && Number(req.user?.student_id) > 0;
}

function trustedWriteOrigin(req, res, next) {
  const origin = req.get('origin');
  const allowed = [env.FRONTEND_ORIGIN, env.STUDENT_APP_URL]
    .map(value => {
      try { return new URL(value).origin; } catch { return ''; }
    })
    .filter(Boolean);
  if (!origin || !allowed.includes(origin)) {
    return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan chat ditolak.' } });
  }
  return next();
}

function sanitizeMessageText(raw) {
  return String(raw || '')
    .replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g, '')
    .trim()
    .slice(0, 1000);
}

async function getAdminPresence(forceFresh = false) {
  const now = Date.now();
  if (!forceFresh && presenceCache.expiresAt > now) {
    return presenceCache.data;
  }
  const result = await pool.request()
    .input('ttl', sql.Int, PRESENCE_TTL_SECONDS)
    .query(`
      SELECT admin_npk, admin_name, last_seen_at
      FROM dbo.support_admin_presence
      WHERE last_seen_at >= DATEADD(SECOND, -@ttl, SYSUTCDATETIME())
      ORDER BY last_seen_at DESC
    `);
  const admins = result.recordset.map(row => ({
    npk: row.admin_npk,
    name: row.admin_name,
    last_seen_at: row.last_seen_at,
  }));
  const data = {
    online: admins.length > 0,
    online_count: admins.length,
    admins,
  };
  presenceCache = { expiresAt: now + PRESENCE_CACHE_MS, data };
  return data;
}

router.get('/presence', async (_req, res, next) => {
  try {
    const presence = await getAdminPresence(false);
    return res.json({ data: presence });
  } catch (err) {
    return next(err);
  }
});

router.post('/heartbeat', trustedWriteOrigin, async (req, res, next) => {
  try {
    if (!isAdmin(req)) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Hanya admin yang dapat mengirim status online.' } });
    }
    const adminNpk = String(req.user.sub || '').trim();
    const adminName = String(req.user.name || req.user.sub || 'Admin LMS').trim().slice(0, 120);
    const prevOnline = presenceCache.data?.online;

    await pool.request()
      .input('npk', sql.VarChar(50), adminNpk)
      .input('name', sql.NVarChar(120), adminName)
      .query(`
        MERGE dbo.support_admin_presence AS target
        USING (SELECT @npk AS admin_npk) AS src
        ON target.admin_npk = src.admin_npk
        WHEN MATCHED THEN
          UPDATE SET admin_name = @name, last_seen_at = SYSUTCDATETIME()
        WHEN NOT MATCHED THEN
          INSERT (admin_npk, admin_name, last_seen_at) VALUES (@npk, @name, SYSUTCDATETIME());
      `);

    const presence = await getAdminPresence(true);
    if (prevOnline !== presence.online) {
      publishSupportChatEvent({ type: 'presence', presence });
    }
    return res.json({ data: presence });
  } catch (err) {
    return next(err);
  }
});

router.get('/conversations', async (req, res, next) => {
  try {
    if (!isAdmin(req)) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Hanya admin yang dapat melihat daftar percakapan.' } });
    }
    const result = await pool.request().query(`
      WITH latest AS (
        SELECT
          student_id,
          student_npk,
          student_name,
          message_text,
          sender_role,
          created_at,
          ROW_NUMBER() OVER (PARTITION BY student_id ORDER BY message_id DESC) AS rn
        FROM dbo.support_chat_messages
      ),
      unread AS (
        SELECT student_id, COUNT(*) AS unread_count
        FROM dbo.support_chat_messages
        WHERE sender_role = 'student' AND is_read = 0
        GROUP BY student_id
      )
      SELECT
        l.student_id,
        l.student_npk,
        l.student_name,
        l.message_text AS last_message,
        l.sender_role AS last_sender_role,
        l.created_at AS last_message_at,
        ISNULL(u.unread_count, 0) AS unread_count
      FROM latest l
      LEFT JOIN unread u ON u.student_id = l.student_id
      WHERE l.rn = 1
      ORDER BY ISNULL(u.unread_count, 0) DESC, l.created_at DESC
    `);
    return res.json({ data: result.recordset });
  } catch (err) {
    return next(err);
  }
});

router.get('/messages', async (req, res, next) => {
  try {
    let targetStudentId = 0;
    if (isStudent(req)) {
      targetStudentId = Number(req.user.student_id);
      if (req.query.student_id && Number(req.query.student_id) !== targetStudentId) {
        return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses percakapan ditolak.' } });
      }
    } else if (isAdmin(req)) {
      targetStudentId = Number(req.query.student_id || 0);
      if (!Number.isInteger(targetStudentId) || targetStudentId <= 0) {
        return res.status(400).json({ error: { code: 'VALIDATION_ERROR', message: 'student_id wajib valid.' } });
      }
    } else {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses chat ditolak.' } });
    }

    const afterId = Math.max(0, Number(req.query.after_id || 0) || 0);
    const limit = Math.min(100, Math.max(1, Number(req.query.limit || 50) || 50));

    const result = await pool.request()
      .input('student_id', sql.BigInt, targetStudentId)
      .input('after_id', sql.BigInt, afterId)
      .input('limit', sql.Int, limit)
      .query(`
        SELECT TOP (@limit)
          message_id, student_id, student_npk, student_name,
          sender_role, sender_npk, sender_name, message_text, is_read, created_at
        FROM dbo.support_chat_messages
        WHERE student_id = @student_id AND message_id > @after_id
        ORDER BY message_id ASC
      `);

    if (String(req.query.mark_read || '') === '1') {
      const incomingRole = isAdmin(req) ? 'student' : 'admin';
      await pool.request()
        .input('student_id', sql.BigInt, targetStudentId)
        .input('incoming_role', sql.VarChar(20), incomingRole)
        .query(`
          UPDATE dbo.support_chat_messages
          SET is_read = 1
          WHERE student_id = @student_id AND sender_role = @incoming_role AND is_read = 0
        `);
    }

    const presence = await getAdminPresence(false);
    return res.json({ data: result.recordset, presence });
  } catch (err) {
    return next(err);
  }
});

router.post('/messages', trustedWriteOrigin, async (req, res, next) => {
  try {
    const parsed = messageBodySchema.safeParse(req.body || {});
    if (!parsed.success) {
      return res.status(400).json({ error: { code: 'VALIDATION_ERROR', message: parsed.error.issues[0]?.message || 'Input tidak valid.' } });
    }
    const cleanText = sanitizeMessageText(parsed.data.message_text);
    if (!cleanText) {
      return res.status(400).json({ error: { code: 'VALIDATION_ERROR', message: 'Pesan tidak boleh kosong.' } });
    }

    let targetStudentId = 0;
    let studentNpk = '';
    let studentName = '';
    let senderRole = '';
    let senderNpk = '';
    let senderName = '';

    if (isStudent(req)) {
      targetStudentId = Number(req.user.student_id);
      if (parsed.data.student_id && parsed.data.student_id !== targetStudentId) {
        return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Tidak dapat mengirim pesan atas nama siswa lain.' } });
      }
      const identity = await getStudentIdentity(targetStudentId);
      studentNpk = String(identity?.npk || req.user.sub || '').slice(0, 50);
      studentName = String(identity?.name || req.user.name || 'Siswa').slice(0, 120);
      senderRole = 'student';
      senderNpk = studentNpk;
      senderName = studentName;
    } else if (isAdmin(req)) {
      targetStudentId = Number(parsed.data.student_id || 0);
      if (!Number.isInteger(targetStudentId) || targetStudentId <= 0) {
        return res.status(400).json({ error: { code: 'VALIDATION_ERROR', message: 'Pilih siswa tujuan terlebih dahulu.' } });
      }
      const identity = await getStudentIdentity(targetStudentId);
      studentNpk = String(identity?.npk || `STU-${targetStudentId}`).slice(0, 50);
      studentName = String(identity?.name || 'Siswa').slice(0, 120);
      senderRole = 'admin';
      senderNpk = String(req.user.sub || 'ADMIN').slice(0, 50);
      senderName = String(req.user.name || 'Admin LMS').slice(0, 120);
    } else {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses chat ditolak.' } });
    }

    const inserted = await pool.request()
      .input('student_id', sql.BigInt, targetStudentId)
      .input('student_npk', sql.VarChar(50), studentNpk)
      .input('student_name', sql.NVarChar(120), studentName)
      .input('sender_role', sql.VarChar(20), senderRole)
      .input('sender_npk', sql.VarChar(50), senderNpk)
      .input('sender_name', sql.NVarChar(120), senderName)
      .input('message_text', sql.NVarChar(1000), cleanText)
      .query(`
        INSERT INTO dbo.support_chat_messages
          (student_id, student_npk, student_name, sender_role, sender_npk, sender_name, message_text, is_read)
        OUTPUT INSERTED.*
        VALUES
          (@student_id, @student_npk, @student_name, @sender_role, @sender_npk, @sender_name, @message_text, 0)
      `);

    const message = inserted.recordset[0];
    publishSupportChatEvent({ type: 'message', student_id: targetStudentId, message });
    return res.status(201).json({ data: message });
  } catch (err) {
    return next(err);
  }
});

router.delete('/conversations/:studentId', trustedWriteOrigin, async (req, res, next) => {
  try {
    if (!isAdmin(req)) {
      return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Hanya admin yang dapat menghapus riwayat percakapan.' } });
    }
    const targetStudentId = Number(req.params.studentId || 0);
    if (!Number.isInteger(targetStudentId) || targetStudentId <= 0) {
      return res.status(400).json({ error: { code: 'VALIDATION_ERROR', message: 'studentId wajib valid.' } });
    }

    const result = await pool.request()
      .input('student_id', sql.BigInt, targetStudentId)
      .query(`
        DELETE FROM dbo.support_chat_messages
        WHERE student_id = @student_id;
      `);

    publishSupportChatEvent({ type: 'cleared', student_id: targetStudentId });
    return res.json({
      deleted: true,
      student_id: targetStudentId,
      deleted_count: Number(result.rowsAffected?.[0] || 0),
    });
  } catch (err) {
    return next(err);
  }
});

router.get('/stream', async (req, res) => {
  if (!isStudent(req) && !isAdmin(req)) {
    return res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Akses stream ditolak.' } });
  }
  const userKey = `${roleOf(req)}:${req.user.sub || req.user.student_id}`;
  const currentCount = activeChatStreams.get(userKey) || 0;
  if (currentCount >= 3) {
    return res.status(429).json({ error: { code: 'TOO_MANY_STREAMS', message: 'Terlalu banyak koneksi chat aktif.' } });
  }
  activeChatStreams.set(userKey, currentCount + 1);

  res.setHeader('Content-Type', 'text/event-stream; charset=utf-8');
  res.setHeader('Cache-Control', 'no-cache, no-transform');
  res.setHeader('Connection', 'keep-alive');
  res.setHeader('X-Accel-Buffering', 'no');
  res.flushHeaders?.();

  const sendEvent = (name, payload) => {
    res.write(`event: ${name}\n`);
    res.write(`data: ${JSON.stringify(payload)}\n\n`);
  };

  try {
    const presence = await getAdminPresence(false);
    sendEvent('ready', { presence });
  } catch {
    sendEvent('ready', { presence: { online: false, online_count: 0, admins: [] } });
  }

  const listener = event => {
    if (event.type === 'presence') {
      sendEvent('presence', event.presence);
      return;
    }
    if (event.type === 'message') {
      if (isAdmin(req) || Number(event.student_id) === Number(req.user.student_id)) {
        sendEvent('message', event.message);
      }
      return;
    }
    if (event.type === 'cleared') {
      if (isAdmin(req) || Number(event.student_id) === Number(req.user.student_id)) {
        sendEvent('cleared', { student_id: event.student_id });
      }
    }
  };

  supportChatEvents.on('change', listener);
  const keepalive = setInterval(() => {
    res.write(': keepalive\n\n');
  }, 25000);

  req.on('close', () => {
    clearInterval(keepalive);
    supportChatEvents.off('change', listener);
    const nextCount = Math.max(0, (activeChatStreams.get(userKey) || 1) - 1);
    if (nextCount === 0) activeChatStreams.delete(userKey);
    else activeChatStreams.set(userKey, nextCount);
  });
});

module.exports = router;

