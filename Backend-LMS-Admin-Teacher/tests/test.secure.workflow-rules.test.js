const assert = require('node:assert/strict');
const test = require('node:test');
const { z } = require('zod');

// Validation schemas matching reviewer route and workflow
const reviewerSchema = z.object({
  npk: z.string().trim().min(1).max(50),
  fullname: z.string().trim().min(2).max(150),
  email: z.string().trim().email().max(254),
  password: z.string().min(12, 'Kata sandi minimal 12 karakter.').max(200),
  department: z.string().trim().max(100).optional().default(''),
  position: z.string().trim().max(100).optional().default(''),
  phoneNumber: z.string().trim().max(30).optional().default('')
});

function canEditMaterialSubmission({ userRole, isSubmitter, status }) {
  if (userRole === 'teacher') {
    if (!isSubmitter) return { allowed: false, code: 'FORBIDDEN' };
    if (status === 'pending_admin') return { allowed: false, code: 'CANNOT_EDIT_PENDING_APPROVAL' };
    if (status === 'pending_review') return { allowed: false, code: 'CANNOT_EDIT_IN_REVIEW' };
    if (['approved', 'published'].includes(status)) return { allowed: false, code: 'CANNOT_EDIT_FINAL' };
    if (status === 'rejected') return { allowed: true };
    return { allowed: false, code: 'INVALID_STATUS' };
  }
  if (userRole === 'admin') {
    if (status === 'pending_review') return { allowed: false, code: 'CANNOT_EDIT_IN_REVIEW' };
    if (['approved', 'published'].includes(status)) return { allowed: false, code: 'CANNOT_EDIT_FINAL' };
    if (['pending_admin', 'rejected'].includes(status)) return { allowed: true };
    return { allowed: false, code: 'INVALID_STATUS' };
  }
  return { allowed: false, code: 'FORBIDDEN' };
}

test('enforces material edit permission rules according to workflow status', async t => {
  await t.test('teacher cannot edit when submission is pending_admin', () => {
    const result = canEditMaterialSubmission({ userRole: 'teacher', isSubmitter: true, status: 'pending_admin' });
    assert.equal(result.allowed, false);
    assert.equal(result.code, 'CANNOT_EDIT_PENDING_APPROVAL');
  });

  await t.test('teacher cannot edit when submission is pending_review', () => {
    const result = canEditMaterialSubmission({ userRole: 'teacher', isSubmitter: true, status: 'pending_review' });
    assert.equal(result.allowed, false);
    assert.equal(result.code, 'CANNOT_EDIT_IN_REVIEW');
  });

  await t.test('teacher can edit when submission is rejected', () => {
    const result = canEditMaterialSubmission({ userRole: 'teacher', isSubmitter: true, status: 'rejected' });
    assert.equal(result.allowed, true);
  });

  await t.test('admin can edit when submission is pending_admin', () => {
    const result = canEditMaterialSubmission({ userRole: 'admin', isSubmitter: false, status: 'pending_admin' });
    assert.equal(result.allowed, true);
  });

  await t.test('admin cannot edit when submission is pending_review', () => {
    const result = canEditMaterialSubmission({ userRole: 'admin', isSubmitter: false, status: 'pending_review' });
    assert.equal(result.allowed, false);
    assert.equal(result.code, 'CANNOT_EDIT_IN_REVIEW');
  });

  await t.test('admin can edit when submission is rejected', () => {
    const result = canEditMaterialSubmission({ userRole: 'admin', isSubmitter: false, status: 'rejected' });
    assert.equal(result.allowed, true);
  });

  await t.test('neither teacher nor admin can edit approved or published submissions', () => {
    for (const status of ['approved', 'published']) {
      assert.equal(canEditMaterialSubmission({ userRole: 'teacher', isSubmitter: true, status }).allowed, false);
      assert.equal(canEditMaterialSubmission({ userRole: 'admin', isSubmitter: false, status }).allowed, false);
    }
  });
});

test('validates reviewer account fields including HRD profile data', async t => {
  await t.test('accepts valid reviewer with department and position', () => {
    const parsed = reviewerSchema.safeParse({
      npk: 'REV-001',
      fullname: 'Budi Santoso, S.Psi',
      email: 'budi.santoso@denso.co.id',
      password: 'StrongPassword123!',
      department: 'Human Resources',
      position: 'HRD Manager',
      phoneNumber: '08123456789'
    });
    assert.equal(parsed.success, true);
    assert.equal(parsed.data.department, 'Human Resources');
    assert.equal(parsed.data.position, 'HRD Manager');
  });

  await t.test('rejects weak reviewer password under 12 characters', () => {
    const parsed = reviewerSchema.safeParse({
      npk: 'REV-002',
      fullname: 'Siti Aminah',
      email: 'siti@denso.co.id',
      password: 'short'
    });
    assert.equal(parsed.success, false);
  });

  await t.test('rejects invalid email address', () => {
    const parsed = reviewerSchema.safeParse({
      npk: 'REV-003',
      fullname: 'Ahmad Dahlan',
      email: 'not-an-email',
      password: 'SecureLongPassword2026!'
    });
    assert.equal(parsed.success, false);
  });
});
