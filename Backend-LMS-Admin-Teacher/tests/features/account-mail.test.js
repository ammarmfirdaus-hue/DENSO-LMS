const assert = require('node:assert/strict');
const Module = require('node:module');
const path = require('node:path');
const test = require('node:test');

let lastMessage = null;
const originalLoad = Module._load;
Module._load = function loadWithMailMocks(request, parent, isMain) {
  if (request === 'nodemailer' && parent?.filename.endsWith(`${path.sep}services${path.sep}account-mail.js`)) {
    return { createTransport: () => ({ sendMail: async message => { lastMessage = message; } }) };
  }
  if (request === '../config/env' && parent?.filename.endsWith(`${path.sep}services${path.sep}account-mail.js`)) {
    return {
      SMTP_HOST: 'smtp.example.test', SMTP_PORT: 587, SMTP_SECURE: 'false', SMTP_USER: 'lms@example.test',
      SMTP_PASSWORD: 'test-only-password', SMTP_FROM: 'lms@example.test'
    };
  }
  return originalLoad.call(this, request, parent, isMain);
};
const { sendAccountCredentials } = require('../../src/services/account-mail');
Module._load = originalLoad;

test('account email contains the usable password in both plain text and HTML', async () => {
  lastMessage = null;
  const password = 'Temp<Password>&123!';

  await sendAccountCredentials({ to: 'student@example.test', fullname: 'QA User', npk: 'DLS-QA-1', role: 'student', password });

  assert.match(lastMessage.text, new RegExp(`Kata sandi: ${password.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`));
  assert.match(lastMessage.html, /Kata sandi<\/th>/);
  assert.match(lastMessage.html, /Temp&lt;Password&gt;&amp;123!/);
  assert.doesNotMatch(lastMessage.text, /Gunakan kata sandi yang sebelumnya/);
});

test('account email refuses to send without an actual password', async () => {
  await assert.rejects(sendAccountCredentials({ to: 'student@example.test', fullname: 'QA User', npk: 'DLS-QA-1', role: 'student' }), {
    code: 'EMAIL_PASSWORD_REQUIRED'
  });
});

