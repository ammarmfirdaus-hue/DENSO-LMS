const nodemailer = require('nodemailer');
const env = require('../config/env');

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character]);
}

async function sendAccountCredentials({ to, fullname, npk, password, role }) {
  if (!password) {
    const error = new Error('Kata sandi baru wajib disertakan agar email memuat kredensial yang dapat langsung digunakan.');
    error.code = 'EMAIL_PASSWORD_REQUIRED';
    throw error;
  }
  if (!env.SMTP_HOST || !env.SMTP_USER || !env.SMTP_PASSWORD) {
    const error = new Error('SMTP belum dikonfigurasi. Isi SMTP_HOST, SMTP_USER, dan SMTP_PASSWORD.');
    error.code = 'EMAIL_NOT_CONFIGURED';
    throw error;
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
  const safeName = escapeHtml(fullname);
  const safeNpk = escapeHtml(npk);
  const passwordSection = `Kata sandi: ${password}`;
  const safePasswordSection = escapeHtml(passwordSection);
  const safeRole = escapeHtml(role);
  await transporter.sendMail({
    from: env.SMTP_FROM,
    to,
    subject: '[DENSO LMS] Informasi akun Anda',
    text: `Halo ${fullname},\n\nInformasi akun DENSO LMS Anda.\nNPK: ${npk}\n${passwordSection}\nRole: ${role}\n\nSimpan informasi ini dengan aman.`,
    html: `<div style="font-family:Arial,sans-serif;line-height:1.6;color:#20252b;max-width:600px;margin:auto"><h2>Informasi akun DENSO LMS</h2><p>Halo ${safeName}, berikut informasi akun Anda.</p><table style="border-collapse:collapse;width:100%"><tr><th style="text-align:left;padding:10px;border:1px solid #d9dee4;background:#f0f2f5">NPK</th><td style="padding:10px;border:1px solid #d9dee4">${safeNpk}</td></tr><tr><th style="text-align:left;padding:10px;border:1px solid #d9dee4;background:#f0f2f5">Kata sandi</th><td style="padding:10px;border:1px solid #d9dee4">${safePasswordSection}</td></tr><tr><th style="text-align:left;padding:10px;border:1px solid #d9dee4;background:#f0f2f5">Role</th><td style="padding:10px;border:1px solid #d9dee4">${safeRole}</td></tr></table><p>Simpan informasi ini dengan aman.</p></div>`
  });
}

module.exports = { sendAccountCredentials };
