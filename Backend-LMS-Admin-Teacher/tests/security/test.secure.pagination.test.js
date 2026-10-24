const assert = require('node:assert/strict');
const test = require('node:test');
const { invokeMiddleware } = require('../helpers/security-test-helpers');
const { paginationMiddleware, paginateCollection, MAX_PAGE_SIZE } = require('../../src/middleware/pagination');

test('rejects hostile and oversized pagination query values', async t => {
  const attacks = [
    ['SQL-like input', { page: '1 OR 1=1', limit: '10' }],
    ['scientific notation', { page: '1e9', limit: '10' }],
    ['negative page', { page: '-1', limit: '10' }],
    ['zero page size', { page: '1', limit: '0' }],
    ['oversized page size', { page: '1', limit: '1000000' }],
    ['repeated query parameter', { page: ['1', '2'], limit: '10' }],
    ['object-shaped query parameter', { page: { value: '1' }, limit: '10' }],
    ['integer overflow', { page: '9007199254740992', limit: '10' }]
  ];

  for (const [name, query] of attacks) {
    await t.test(name, () => {
      const { res, nextCalled } = invokeMiddleware(paginationMiddleware, { query });
      assert.equal(nextCalled, false);
      assert.equal(res.statusCode, 400);
      assert.equal(res.body.error.code, 'INVALID_PAGINATION');
    });
  }
});

test('caps page size and returns only the requested slice', () => {
  const req = { query: { page: '2', limit: String(MAX_PAGE_SIZE) } };
  const { nextCalled } = invokeMiddleware(paginationMiddleware, req);
  assert.equal(nextCalled, true);
  const result = paginateCollection(req, Array.from({ length: 250 }, (_, index) => index));
  assert.equal(result.data.length, MAX_PAGE_SIZE);
  assert.deepEqual(result.data.slice(0, 3), [100, 101, 102]);
  assert.equal(result.pagination.total, 250);
});
