const assert = require('node:assert/strict');
const test = require('node:test');
const { getSubmissionEditDecision } = require('../../src/services/material-workflow-rules');
const { reviewerSchema, reviewerUpdateSchema } = require('../../src/services/reviewer-rules');

test('material edit permissions use the production workflow rules', async t => {
  const cases = [
    ['teacher owns rejected submission', { userRole: 'teacher', isSubmitter: true, status: 'rejected' }, { allowed: true }],
    ['teacher owns pending admin submission', { userRole: 'teacher', isSubmitter: true, status: 'pending_admin' }, { allowed: false, code: 'CANNOT_EDIT_PENDING_APPROVAL' }],
    ['teacher owns pending reviewer submission', { userRole: 'teacher', isSubmitter: true, status: 'pending_review' }, { allowed: false, code: 'CANNOT_EDIT_IN_REVIEW' }],
    ['teacher owns approved submission', { userRole: 'teacher', isSubmitter: true, status: 'approved' }, { allowed: false, code: 'CANNOT_EDIT_FINAL' }],
    ['teacher owns published submission', { userRole: 'teacher', isSubmitter: true, status: 'published' }, { allowed: false, code: 'CANNOT_EDIT_FINAL' }],
    ['teacher tries another submitter material', { userRole: 'teacher', isSubmitter: false, status: 'rejected' }, { allowed: false, code: 'FORBIDDEN' }],
    ['admin edits pending admin submission', { userRole: 'admin', isSubmitter: false, status: 'pending_admin' }, { allowed: true }],
    ['admin edits rejected submission', { userRole: 'admin', isSubmitter: false, status: 'rejected' }, { allowed: true }],
    ['admin cannot edit during review', { userRole: 'admin', isSubmitter: false, status: 'pending_review' }, { allowed: false, code: 'CANNOT_EDIT_IN_REVIEW' }],
    ['admin cannot edit final submission', { userRole: 'admin', isSubmitter: false, status: 'published' }, { allowed: false, code: 'CANNOT_EDIT_FINAL' }],
    ['unexpected workflow status is denied', { userRole: 'teacher', isSubmitter: true, status: 'archived' }, { allowed: false, code: 'INVALID_STATUS' }],
    ['non-editor role is denied', { userRole: 'student', isSubmitter: true, status: 'rejected' }, { allowed: false, code: 'FORBIDDEN' }]
  ];

  for (const [name, input, expected] of cases) {
    await t.test(name, () => assert.deepEqual(getSubmissionEditDecision(input), expected));
  }
});

test('reviewer creation and update use production validation schemas', async t => {
  await t.test('normalizes email and keeps reviewer profile fields', () => {
    const parsed = reviewerSchema.safeParse({
      npk: 'REV-001', fullname: 'Budi Santoso', email: 'BUDI@DENSO.CO.ID',
      password: 'StrongPassword123!', department: 'Human Resources', position: 'HRD Manager'
    });
    assert.equal(parsed.success, true);
    assert.equal(parsed.data.email, 'budi@denso.co.id');
    assert.equal(parsed.data.department, 'Human Resources');
    assert.equal(parsed.data.position, 'HRD Manager');
  });

  await t.test('rejects short password and malformed email', () => {
    assert.equal(reviewerSchema.safeParse({ npk: 'REV-002', fullname: 'Siti Aminah', email: 'siti@example.test', password: 'short' }).success, false);
    assert.equal(reviewerSchema.safeParse({ npk: 'REV-003', fullname: 'Ahmad Dahlan', email: 'not-an-email', password: 'SecureLongPassword2026!' }).success, false);
  });

  await t.test('allows profile update without password and validates status', () => {
    const parsed = reviewerUpdateSchema.safeParse({ fullname: 'Siti Aminah', email: 'SITI@DENSO.CO.ID', status: 'inactive' });
    assert.equal(parsed.success, true);
    assert.equal(parsed.data.email, 'siti@denso.co.id');
    assert.equal(parsed.data.password, undefined);
    assert.equal(reviewerUpdateSchema.safeParse({ fullname: 'Siti Aminah', email: 'siti@denso.co.id', status: 'root' }).success, false);
  });
});
