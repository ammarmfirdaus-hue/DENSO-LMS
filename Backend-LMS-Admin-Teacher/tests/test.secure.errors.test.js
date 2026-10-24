const assert = require('node:assert/strict');
const test = require('node:test');
const { mockResponse } = require('./helpers/security-test-helpers');
const { errorHandler } = require('../src/middleware/errors');

test('unexpected server errors do not disclose stack traces or database details', () => {
  const secretMessage = 'SQL password=do-not-disclose; stack trace at private/path.js';
  const res = mockResponse();
  const originalConsoleError = console.error;
  console.error = () => {};
  try {
    errorHandler(new Error(secretMessage), {}, res, () => {});
  } finally {
    console.error = originalConsoleError;
  }
  assert.equal(res.statusCode, 500);
  assert.equal(res.body.error.code, 'INTERNAL_ERROR');
  assert.equal(res.body.error.message.includes('password'), false);
  assert.equal(res.body.error.message.includes('private/path'), false);
});

test('oversized upload errors are returned as a safe 413 response', () => {
  const res = mockResponse();
  errorHandler(Object.assign(new Error('large'), { code: 'LIMIT_FILE_SIZE' }), {}, res, () => {});
  assert.equal(res.statusCode, 413);
  assert.equal(res.body.error.code, 'REQUEST_ERROR');
  assert.match(res.body.error.message, /Ukuran file/);
});
