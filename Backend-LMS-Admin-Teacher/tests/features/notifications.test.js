const assert = require('node:assert/strict');
const test = require('node:test');
const { notificationInputSchema, canSendToRoles, canSendToMembers, emailBatches, audienceLabel } = require('../../src/services/notification-rules');

test('notification payload validation applies defaults and rejects unsafe extra fields', async t => {
  await t.test('trims content and applies normal priority defaults', () => {
    const result = notificationInputSchema.safeParse({ title: '  Jadwal baru  ', message: '  Periksa jadwal Anda.  ', targetRoles: ['student'] });
    assert.equal(result.success, true);
    assert.equal(result.data.title, 'Jadwal baru');
    assert.equal(result.data.message, 'Periksa jadwal Anda.');
    assert.equal(result.data.category, 'announcement');
    assert.equal(result.data.priority, 'normal');
    assert.equal(result.data.targetMode, 'role');
    assert.equal(result.data.sendEmail, false);
    assert.equal(result.data.showPopup, false);

    const withPopup = notificationInputSchema.safeParse({
      title: 'Pemberitahuan Mendesak',
      message: 'Harap segera cek modul.',
      targetRoles: ['student', 'teacher'],
      showPopup: true,
      imageUrl: '/notifications/uploads/example.webp'
    });
    assert.equal(withPopup.success, true);
    assert.equal(withPopup.data.showPopup, true);
    assert.equal(withPopup.data.imageUrl, '/notifications/uploads/example.webp');

    const withMembers = notificationInputSchema.safeParse({
      title: 'Pemberitahuan Khusus',
      message: 'Khusus anggota terpilih.',
      targetMode: 'members',
      targetMembers: [
        { npk: 'STD001', name: 'Budi Santoso', role: 'student', email: 'budi@example.com' },
        { npk: 'STD002', name: 'Ani Wijaya', role: 'student' }
      ]
    });
    assert.equal(withMembers.success, true);
    assert.equal(withMembers.data.targetMode, 'members');
    assert.equal(withMembers.data.targetMembers.length, 2);
  });

  await t.test('rejects blank, multiline, too long, and unknown fields', () => {
    assert.equal(notificationInputSchema.safeParse({ title: ' ', message: 'x', targetRoles: ['student'] }).success, false);
    assert.equal(notificationInputSchema.safeParse({ title: 'Baris 1\nBaris 2', message: 'x', targetRoles: ['student'] }).success, false);
    assert.equal(notificationInputSchema.safeParse({ title: 'Penting', message: 'x'.repeat(2001), targetRoles: ['student'] }).success, false);
    assert.equal(notificationInputSchema.safeParse({ title: 'Penting', message: 'x', targetRoles: ['student'], html: '<script>' }).success, false);
  });
});

test('notification audience rules enforce teacher and admin boundaries', () => {
  assert.equal(canSendToRoles('teacher', ['student']), true);
  assert.equal(canSendToRoles('teacher', ['student', 'admin']), false);
  assert.equal(canSendToRoles('teacher', ['teacher']), false);
  assert.equal(canSendToRoles('admin', ['student', 'teacher', 'admin']), true);
  assert.equal(canSendToRoles('admin', []), false);
  assert.equal(canSendToRoles('reviewer', ['student']), false);
  assert.equal(audienceLabel('student'), 'Student');
  assert.equal(audienceLabel('teacher'), 'Teacher');
  assert.equal(audienceLabel('admin'), 'Admin');

  // Member-targeted rules
  assert.equal(canSendToMembers('teacher', [{ npk: '123', role: 'student' }]), true);
  assert.equal(canSendToMembers('teacher', [{ npk: '123', role: 'admin' }]), false);
  assert.equal(canSendToMembers('admin', [{ npk: '123', role: 'admin' }, { npk: '456', role: 'student' }]), true);
  assert.equal(canSendToMembers('teacher', []), false);
});

test('email recipients are validated, normalized, deduplicated, and bounded', () => {
  const result = emailBatches([
    { email: '  User@Example.test ', role: 'student' },
    { email: 'user@example.test', role: 'teacher' },
    { email: 'invalid-address' },
    { email: 'second@example.test' },
    { email: 'third@example.test' }
  ], 2);
  assert.equal(result.length, 3, 'one over the configured maximum is retained so caller can reject the batch');
  assert.deepEqual(result.map(item => item.email), ['user@example.test', 'second@example.test', 'third@example.test']);
});
