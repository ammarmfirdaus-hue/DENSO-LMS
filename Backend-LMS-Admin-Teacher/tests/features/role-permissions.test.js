const assert = require('node:assert/strict');
const test = require('node:test');

// Ensure isolated test environment variables before importing auth/db modules.
require('../helpers/security-test-helpers');

const {
  PERMISSION_CATALOG,
  PERMISSION_KEYS,
  ROLE_KEYS,
  DEFAULT_ROLE_PERMISSIONS,
  ROLE_PRESETS,
  enforceSystemSafeguards,
  getRoleAccessProfile,
  hasRolePermission,
  computePermissionDiff,
  invalidateRoleCache
} = require('../../src/services/role-permissions');
const { requirePermission, requestHasPermission } = require('../../src/middleware/auth');

async function invokeAsyncMiddleware(middleware, req) {
  let nextCalled = false;
  const res = {
    statusCode: 200,
    body: null,
    status(code) { this.statusCode = code; return this; },
    json(body) { this.body = body; return this; }
  };
  await middleware(req, res, () => { nextCalled = true; });
  return { res, nextCalled };
}

test('permission catalog covers unique keys across core modules and roles', () => {
  assert.ok(PERMISSION_CATALOG.length >= 25);
  assert.equal(new Set(PERMISSION_KEYS).size, PERMISSION_CATALOG.length);
  assert.deepEqual(ROLE_KEYS, ['admin', 'teacher', 'reviewer', 'student']);
  assert.equal(DEFAULT_ROLE_PERMISSIONS.admin.length, PERMISSION_CATALOG.length);
  assert.ok(DEFAULT_ROLE_PERMISSIONS.reviewer.includes('materials.review'));
  assert.ok(!DEFAULT_ROLE_PERMISSIONS.reviewer.includes('users.manage'));
});

test('enforceSystemSafeguards prevents admin lockout', () => {
  const result = enforceSystemSafeguards('admin', {
    role_key: 'admin',
    display_name: 'Administrator',
    description: 'Admin',
    can_access_staff_portal: false,
    permissions: new Set(['dashboard.view'])
  });
  assert.equal(result.can_access_staff_portal, true);
  assert.equal(result.permissions.has('roles.manage'), true);
  assert.equal(result.permissions.has('dashboard.view'), true);

  const reviewerResult = enforceSystemSafeguards('reviewer', {
    role_key: 'reviewer',
    can_access_staff_portal: false,
    permissions: new Set(['materials.review'])
  });
  assert.equal(reviewerResult.can_access_staff_portal, false);
  assert.equal(reviewerResult.permissions.has('roles.manage'), false);
});

test('computePermissionDiff detects added and removed keys in catalog order', () => {
  const diff = computePermissionDiff(
    ['dashboard.view', 'materials.review'],
    ['dashboard.view', 'courses.view', 'forum.view']
  );
  assert.deepEqual(diff.added, ['courses.view', 'forum.view']);
  assert.deepEqual(diff.removed, ['materials.review']);
});

test('presets generate valid permission bundles for target roles', () => {
  const reviewerExpanded = ROLE_PRESETS.reviewer_executive.resolve('reviewer');
  assert.equal(reviewerExpanded.canAccessStaffPortal, true);
  assert.ok(reviewerExpanded.permissions.includes('materials.review'));
  assert.ok(reviewerExpanded.permissions.includes('courses.view'));

  const studentDefault = ROLE_PRESETS.default.resolve('student');
  assert.equal(studentDefault.canAccessStaffPortal, false);
  assert.ok(studentDefault.permissions.includes('forum.participate'));
});

test('fallback role matrix resolves default access when database is disconnected', async () => {
  invalidateRoleCache();
  const teacherProfile = await getRoleAccessProfile('teacher');
  assert.equal(teacherProfile.canAccessStaffPortal, true);
  assert.equal(await hasRolePermission('teacher', 'courses.create'), true);
  assert.equal(await hasRolePermission('teacher', 'roles.manage'), false);
  assert.equal(await hasRolePermission('reviewer', 'materials.review'), true);
  assert.equal(await hasRolePermission('unknown_role', 'dashboard.view'), false);
});

test('requirePermission enforces dynamic permissions and isolates reviewer magic links', async () => {
  const teacherAllowed = await invokeAsyncMiddleware(
    requirePermission('courses.create'),
    { user: { role: 'teacher', sub: 'T-01' } }
  );
  assert.equal(teacherAllowed.nextCalled, true);

  const reviewerDeniedAdmin = await invokeAsyncMiddleware(
    requirePermission('roles.manage'),
    { user: { role: 'reviewer', sub: 'REV-01' } }
  );
  assert.equal(reviewerDeniedAdmin.nextCalled, false);
  assert.equal(reviewerDeniedAdmin.res.statusCode, 403);
  assert.equal(reviewerDeniedAdmin.res.body.error.code, 'FORBIDDEN');

  // Magic link tokens with scope reviewer_portal remain strictly sandboxed to materials.review
  const magicReviewAllowed = await requestHasPermission(
    { user: { role: 'reviewer', scope: 'reviewer_portal', sub: 'REV-01' } },
    'materials.review'
  );
  assert.equal(magicReviewAllowed, true);

  const magicOtherDenied = await requestHasPermission(
    { user: { role: 'reviewer', scope: 'reviewer_portal', sub: 'REV-01' } },
    'dashboard.view',
    'courses.view'
  );
  assert.equal(magicOtherDenied, false);
});
