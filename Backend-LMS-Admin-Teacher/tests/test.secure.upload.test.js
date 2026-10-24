const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const os = require('node:os');
const path = require('node:path');
const test = require('node:test');
require('./helpers/security-test-helpers');
const { hasExpectedFileSignature, uploadDirectory } = require('../src/middleware/material-upload');

async function withTempFile(t, name, content) {
  const directory = await fs.mkdtemp(path.join(os.tmpdir(), 'dls-security-'));
  t.after(() => fs.rm(directory, { recursive: true, force: true }));
  const filePath = path.join(directory, name);
  await fs.writeFile(filePath, content);
  return filePath;
}

test('checks uploaded content signatures instead of trusting the filename', async t => {
  const validPdf = await withTempFile(t, 'material.pdf', Buffer.from('%PDF-1.7\n'));
  const validPng = await withTempFile(t, 'material.png', Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]));
  const htmlDisguisedAsPdf = await withTempFile(t, 'invoice.pdf', Buffer.from('<script>alert(1)</script>'));
  const emptyFile = await withTempFile(t, 'empty.pdf', Buffer.alloc(0));

  assert.equal(await hasExpectedFileSignature(validPdf, '.pdf'), true);
  assert.equal(await hasExpectedFileSignature(validPng, '.png'), true);
  assert.equal(await hasExpectedFileSignature(htmlDisguisedAsPdf, '.pdf'), false);
  assert.equal(await hasExpectedFileSignature(emptyFile, '.pdf'), false);
  assert.equal(await hasExpectedFileSignature(validPdf, '.php'), false);
});

test('material uploads resolve to the configured server-side storage directory', () => {
  assert.equal(path.isAbsolute(uploadDirectory), true);
  assert.match(uploadDirectory, /uploads[\\/]materials$/);
});
