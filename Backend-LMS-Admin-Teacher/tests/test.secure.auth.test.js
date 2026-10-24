const assert = require('node:assert/strict');
const test = require('node:test');
const jwt = require('jsonwebtoken');
const { invokeMiddleware } = require('./helpers/security-test-helpers');
const env = require('../src/config/env');
const { authenticate } = require('../src/middleware/auth');

function bearer(token) {
  return { get: name => name.toLowerCase() === 'authorization' ? `Bearer ${token}` : '' };
}

function signedToken(overrides = {}, options = {}) {
  return jwt.sign({ sub: 'employee-17', role: 'admin', ...overrides }, env.JWT_SECRET, {
    algorithm: 'HS256', issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE,
    expiresIn: '5m', ...options
  });
}

test('protected API rejects requests without credentials', () => {
  const { res, nextCalled } = invokeMiddleware(authenticate, { get: () => '' });
  assert.equal(nextCalled, false);
  assert.equal(res.statusCode, 401);
  assert.equal(res.body.error.code, 'UNAUTHENTICATED');
});

test('accepts a correctly signed token and attaches its claims', () => {
  const req = bearer(signedToken());
  const { res, nextCalled } = invokeMiddleware(authenticate, req);
  assert.equal(nextCalled, true);
  assert.equal(req.user.sub, 'employee-17');
  assert.equal(res.statusCode, 200);
});

test('rejects tampered, expired, incorrectly scoped, and weak-algorithm tokens', async t => {
  const valid = signedToken();
  const [header, payload, signature] = valid.split('.');
  const tampered = `${header}.${Buffer.from(JSON.stringify({ sub: 'attacker', role: 'admin' })).toString('base64url')}.${signature}`;
  const cases = [
    ['tampered claims', tampered],
    ['expired token', signedToken({}, { expiresIn: -1 })],
    ['wrong issuer', signedToken({}, { issuer: 'attacker.local' })],
    ['wrong audience', signedToken({}, { audience: 'other-app' })],
    ['wrong signature', jwt.sign({ sub: 'attacker', role: 'admin' }, 'attacker-secret', { algorithm: 'HS256', issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE, expiresIn: '5m' })],
    ['HS512 algorithm', signedToken({}, { algorithm: 'HS512' })],
    ['malformed token', `${header}..not-a-signature`]
  ];

  for (const [name, token] of cases) {
    await t.test(name, () => {
      const { res, nextCalled } = invokeMiddleware(authenticate, bearer(token));
      assert.equal(nextCalled, false);
      assert.equal(res.statusCode, 401);
      assert.equal(res.body.error.code, 'INVALID_TOKEN');
    });
  }
});

test('invalid encoded SSO cookie is rejected without crashing the request', () => {
  const req = { get: name => name.toLowerCase() === 'cookie' ? 'dls_sso=%E0%A4%A' : '' };
  const { res, nextCalled } = invokeMiddleware(authenticate, req);
  assert.equal(nextCalled, false);
  assert.equal(res.statusCode, 401);
});
