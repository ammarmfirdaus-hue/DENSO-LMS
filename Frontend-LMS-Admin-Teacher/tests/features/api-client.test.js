import assert from 'node:assert/strict';
import test from 'node:test';
import { API_BASE, fetchApi } from '../../src/api/client.js';

const response = (status = 200) => new Response('{}', { status, headers: { 'Content-Type': 'application/json' } });

test('GET retries transient network failures and then returns the response', async () => {
  const originalFetch = globalThis.fetch;
  let attempts = 0;
  globalThis.fetch = async url => {
    attempts += 1;
    assert.equal(url, `${API_BASE}/health`);
    if (attempts === 1) throw new TypeError('Failed to fetch');
    return response();
  };

  try {
    const result = await fetchApi('/health');
    assert.equal(result.status, 200);
    assert.equal(attempts, 2);
  } finally {
    globalThis.fetch = originalFetch;
  }
});

test('write requests are never retried after a network failure', async () => {
  const originalFetch = globalThis.fetch;
  let attempts = 0;
  globalThis.fetch = async () => {
    attempts += 1;
    throw new TypeError('Failed to fetch');
  };

  try {
    await assert.rejects(fetchApi('/users', { method: 'POST', body: '{}' }), /Pastikan Backend LMS aktif/);
    assert.equal(attempts, 1);
  } finally {
    globalThis.fetch = originalFetch;
  }
});

test('GET retries temporary gateway responses up to the configured limit', async () => {
  const originalFetch = globalThis.fetch;
  let attempts = 0;
  globalThis.fetch = async () => {
    attempts += 1;
    return response(503);
  };

  try {
    const result = await fetchApi('/health');
    assert.equal(result.status, 503);
    assert.equal(attempts, 3);
  } finally {
    globalThis.fetch = originalFetch;
  }
});

test('request timeout returns a readable connection message', async () => {
  const originalFetch = globalThis.fetch;
  let attempts = 0;
  globalThis.fetch = async (_url, options) => new Promise((_, reject) => {
    attempts += 1;
    options.signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')), { once: true });
  });

  try {
    await assert.rejects(fetchApi('/health', { timeoutMs: 10 }), /Server LMS tidak merespons/);
    assert.equal(attempts, 1);
  } finally {
    globalThis.fetch = originalFetch;
  }
});
