const nodemailer = require('nodemailer');
const { sql, pool } = require('../config/database');
const env = require('../config/env');

let transporter;
let running = false;
let inFlight = false;
let lastQueueErrorAt = 0;

function isMailConfigured() {
  return Boolean(env.SMTP_HOST && env.SMTP_USER && env.SMTP_PASSWORD);
}

function getTransporter() {
  if (!isMailConfigured()) throw new Error('SMTP belum dikonfigurasi. Isi SMTP_HOST, SMTP_USER, dan SMTP_PASSWORD.');
  transporter ||= nodemailer.createTransport({
    host: env.SMTP_HOST,
    port: env.SMTP_PORT,
    secure: env.SMTP_SECURE === 'true',
    auth: { user: env.SMTP_USER, pass: env.SMTP_PASSWORD },
    connectionTimeout: 10000,
    greetingTimeout: 10000,
    socketTimeout: 20000,
    pool: true,
    maxConnections: 3,
    maxMessages: 50
  });
  return transporter;
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character]);
}

async function sendNotificationEmail(job) {
  const safeName = escapeHtml(job.recipient_name || 'Pengguna DENSO');
  const safeTitle = escapeHtml(job.notification_title);
  const safeMessage = escapeHtml(job.notification_message).replace(/\r?\n/g, '<br>');
  const safeSender = escapeHtml(job.sender_name || 'DENSO LMS');
  const safeCategory = escapeHtml(job.notification_category);
  await getTransporter().sendMail({
    from: env.SMTP_FROM,
    to: job.recipient_email,
    subject: `[DENSO LMS] ${job.notification_title}`,
    text: `Halo ${job.recipient_name || 'Pengguna DENSO'},\n\n${job.notification_title}\n\n${job.notification_message}\n\nDikirim oleh ${job.sender_name || 'DENSO LMS'} melalui DENSO LMS.`,
    html: `<div style="font-family:Arial,sans-serif;line-height:1.65;color:#20252b;max-width:600px;margin:auto"><p>Halo ${safeName},</p><p><strong>${safeTitle}</strong></p><p>${safeMessage}</p><p style="color:#68727d;font-size:13px">${safeCategory} · Dikirim oleh ${safeSender} melalui DENSO LMS.</p></div>`
  });
}

async function claimEmailBatch() {
  const result = await pool.request().query(`
    DECLARE @claimed TABLE (
      email_id BIGINT, notification_id BIGINT, recipient_email NVARCHAR(254),
      recipient_name NVARCHAR(150), attempts INT
    );
    ;WITH next_jobs AS (
      SELECT TOP (12) email_id FROM dbo.lms_notification_email_outbox WITH (UPDLOCK, READPAST, ROWLOCK)
      WHERE (status IN ('pending','retry') AND next_attempt_at <= SYSUTCDATETIME())
        OR (status='processing' AND locked_at < DATEADD(MINUTE,-10,SYSUTCDATETIME()))
      ORDER BY email_id
    )
    UPDATE outbox SET status='processing', attempts=attempts+1, locked_at=SYSUTCDATETIME()
    OUTPUT INSERTED.email_id, INSERTED.notification_id, INSERTED.recipient_email,
      INSERTED.recipient_name, INSERTED.attempts INTO @claimed
    FROM dbo.lms_notification_email_outbox AS outbox
    JOIN next_jobs ON next_jobs.email_id=outbox.email_id;

    SELECT claimed.email_id,claimed.recipient_email,claimed.recipient_name,claimed.attempts,
      notification.notification_title,notification.notification_message,
      notification.category AS notification_category,notification.sender_name
    FROM @claimed AS claimed
    JOIN dbo.lms_notifications AS notification ON notification.notification_id=claimed.notification_id
    ORDER BY claimed.email_id;
  `);
  return result.recordset;
}

async function updateJob(jobId, status, errorMessage = null, attempts = 0) {
  const retryMinutes = Math.min(60, 2 ** Math.min(attempts, 6));
  await pool.request().input('id', sql.BigInt, jobId).input('status', sql.VarChar(20), status)
    .input('error', sql.NVarChar(500), errorMessage).input('retry', sql.Int, retryMinutes)
    .query(`UPDATE dbo.lms_notification_email_outbox
      SET status=@status, sent_at=CASE WHEN @status='sent' THEN SYSUTCDATETIME() ELSE sent_at END,
        locked_at=NULL, last_error=@error,
        next_attempt_at=CASE WHEN @status='retry' THEN DATEADD(MINUTE,@retry,SYSUTCDATETIME()) ELSE next_attempt_at END
      WHERE email_id=@id`);
}

async function drainEmailQueue() {
  if (!running || inFlight || !isMailConfigured()) return;
  inFlight = true;
  try {
    const jobs = await claimEmailBatch();
    for (const job of jobs) {
      try {
        await sendNotificationEmail(job);
        await updateJob(job.email_id, 'sent');
      } catch (error) {
        const attempts = Number(job.attempts || 1);
        const status = attempts >= 5 ? 'failed' : 'retry';
        const safeError = String(error.code || 'SMTP_ERROR').slice(0, 500);
        await updateJob(job.email_id, status, safeError, attempts).catch(() => {});
        console.error('Notification email delivery failed:', error.code || 'SMTP_ERROR');
      }
    }
  } catch (error) {
    if (Date.now() - lastQueueErrorAt > 60000) {
      lastQueueErrorAt = Date.now();
      console.error('Notification email queue unavailable:', error.code || 'DATABASE_ERROR');
    }
  } finally { inFlight = false; }
}

function startNotificationMailWorker() {
  if (!isMailConfigured()) return () => {};
  running = true;
  const timer = setInterval(drainEmailQueue, 5000);
  timer.unref?.();
  drainEmailQueue();
  return async () => {
    running = false;
    clearInterval(timer);
    while (inFlight) await new Promise(resolve => setTimeout(resolve, 50));
    if (transporter) await transporter.close();
    transporter = undefined;
  };
}

module.exports = { isMailConfigured, startNotificationMailWorker };
