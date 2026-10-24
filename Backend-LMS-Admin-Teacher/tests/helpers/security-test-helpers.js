require('dotenv').config();

// Keep security tests isolated from local and production credentials.
Object.assign(process.env, {
  NODE_ENV: 'test',
  FRONTEND_ORIGIN: 'http://127.0.0.1:5173',
  JWT_SECRET: 'test-only-secret-value-that-is-at-least-32-characters',
  JWT_ISSUER: 'dls-test-suite',
  JWT_AUDIENCE: 'dls-test-suite',
  DB_SERVER: '127.0.0.1',
  DB_NAME: 'dls_test_only',
  DB_USER: 'dls_test_only',
  DB_PASSWORD: 'test-only-password'
});

function mockResponse() {
  return {
    statusCode: 200,
    body: null,
    status(code) { this.statusCode = code; return this; },
    json(body) { this.body = body; return this; }
  };
}

function invokeMiddleware(middleware, req) {
  const res = mockResponse();
  let nextCalled = false;
  middleware(req, res, () => { nextCalled = true; });
  return { res, nextCalled };
}

module.exports = { mockResponse, invokeMiddleware };
