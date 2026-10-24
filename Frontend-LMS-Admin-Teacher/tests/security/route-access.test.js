import assert from 'node:assert/strict';
import test from 'node:test';
import { canAccessAdminManagement } from '../../src/utils/access-control.js';

test('admin-only management pages remain hidden from other roles', () => {
  assert.equal(canAccessAdminManagement('admin'), true);
  assert.equal(canAccessAdminManagement('teacher'), false);
  assert.equal(canAccessAdminManagement('reviewer'), false);
  assert.equal(canAccessAdminManagement('student'), false);
  assert.equal(canAccessAdminManagement(undefined), false);
});
