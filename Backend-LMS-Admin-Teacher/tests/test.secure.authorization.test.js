const assert = require('node:assert/strict');
const test = require('node:test');
const { invokeMiddleware } = require('./helpers/security-test-helpers');
const { allowRoles } = require('../src/middleware/auth');

test('allows only the explicitly permitted role', () => {
  const { res, nextCalled } = invokeMiddleware(allowRoles('admin', 'reviewer'), { user: { role: 'reviewer' } });
  assert.equal(nextCalled, true);
  assert.equal(res.statusCode, 200);
});

test('rejects privilege escalation and missing identity claims', async t => {
  for (const [name, user] of [
    ['teacher requesting admin-only access', { role: 'teacher' }],
    ['invented superuser role', { role: 'superadmin' }],
    ['missing user claim', undefined],
    ['empty role claim', { sub: 'employee-17' }]
  ]) {
    await t.test(name, () => {
      const { res, nextCalled } = invokeMiddleware(allowRoles('admin'), { user });
      assert.equal(nextCalled, false);
      assert.equal(res.statusCode, 403);
      assert.equal(res.body.error.code, 'FORBIDDEN');
    });
  }
});
