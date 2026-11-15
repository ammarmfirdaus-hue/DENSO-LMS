const test = require('node:test');
const assert = require('node:assert/strict');
require('../helpers/security-test-helpers');
const { encodePublicId, decodePublicId } = require('../../src/services/public-route-id');

test('public route IDs are opaque, random per issuance, and tamper resistant', () => {
  const first = encodePublicId('course', 42);
  const second = encodePublicId('course', 42);
  assert.notEqual(first, second);
  assert.equal(decodePublicId('course', first), 42);
  assert.equal(decodePublicId('session', first), null);
  const parts = first.split('.');
  parts[1] = `${parts[1].slice(0, -1)}${parts[1].endsWith('A') ? 'B' : 'A'}`;
  assert.equal(decodePublicId('course', parts.join('.')), null);
  assert.equal(decodePublicId('course', '42'), null);
});
