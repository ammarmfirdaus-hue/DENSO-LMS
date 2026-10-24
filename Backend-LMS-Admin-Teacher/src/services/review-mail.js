const nodemailer = require('nodemailer');
const env = require('../config/env');

async function sendReviewInvitation({ to, reviewerName, materialTitle, courseTitle, reviewUrl, expiresAt }) {
  if (!env.SMTP_HOST || !env.SMTP_USER || !env.SMTP_PASSWORD) {
    throw new Error('SMTP belum dikonfigurasi. Isi SMTP_HOST, SMTP_USER, dan SMTP_PASSWORD.');
  }

  const transporter = nodemailer.createTransport({
    host: env.SMTP_HOST,
    port: env.SMTP_PORT,
    secure: env.SMTP_SECURE === 'true',
    auth: { user: env.SMTP_USER, pass: env.SMTP_PASSWORD },
    connectionTimeout: 10000,
    greetingTimeout: 10000,
    socketTimeout: 20000
  });

  const escapeHtml = value => String(value).replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[char]);
  const safeTitle = escapeHtml(materialTitle);
  const safeCourse = escapeHtml(courseTitle);
  const safeName = escapeHtml(reviewerName);
  const expiry = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(expiresAt);

  await transporter.sendMail({
    from: env.SMTP_FROM,
    to,
    subject: `[DENSO LMS] Review materi: ${materialTitle}`,
    text: `Halo ${reviewerName},\n\nMohon review materi "${materialTitle}" untuk course ${courseTitle}. Buka link aman berikut untuk melihat daftar materi yang ditugaskan: ${reviewUrl}\n\nLink berlaku sampai ${expiry} dan hanya dapat digunakan satu kali untuk memulai sesi reviewer.`,
    html: `<p>Halo ${safeName},</p><p>Mohon review materi <strong>${safeTitle}</strong> untuk course <strong>${safeCourse}</strong>.</p><p><a href="${escapeHtml(reviewUrl)}">Buka halaman reviewer</a></p><p>Link berlaku sampai ${escapeHtml(expiry)} dan hanya dapat digunakan satu kali untuk memulai sesi reviewer.</p><p>Jika Anda tidak mengharapkan email ini, abaikan pesan ini.</p>`
  });
}

module.exports = { sendReviewInvitation };
