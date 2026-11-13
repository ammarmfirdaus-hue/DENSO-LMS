require('../helpers/security-test-helpers');
const assert = require('node:assert/strict');
const path = require('node:path');
const test = require('node:test');
const { parseImageBuffer } = require('../../src/services/forum-image');
const { resolveStoredThumbnailPath } = require('../../src/routes/courses');
const { resolveStudentThumbnailUrl } = require('../../src/services/student-sync');

test('course cover image validation checks real magic bytes and MIME type', () => {
  assert.deepEqual(parseImageBuffer('image/png', Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a])), { extension: '.png' });
  assert.deepEqual(parseImageBuffer('image/jpeg', Buffer.from([0xff, 0xd8, 0xff, 0xe0])), { extension: '.jpg' });
  assert.equal(parseImageBuffer('image/png', Buffer.from('<?php echo 1; ?>')), null);
  assert.equal(parseImageBuffer('image/svg+xml', Buffer.from('<svg></svg>')), null);
});

test('resolveStoredThumbnailPath validates UUID filename and prevents directory traversal', () => {
  const validPath = '/courses/thumbnails/123e4567-e89b-12d3-a456-426614174000.png';
  const resolved = resolveStoredThumbnailPath(validPath);
  assert.ok(resolved);
  assert.equal(path.basename(resolved), '123e4567-e89b-12d3-a456-426614174000.png');

  assert.equal(resolveStoredThumbnailPath('/courses/thumbnails/../../.env'), null);
  assert.equal(resolveStoredThumbnailPath('/courses/thumbnails/custom.png'), null);
  assert.equal(resolveStoredThumbnailPath('/courses/thumbnails/123e4567-e89b-12d3-a456-426614174000.svg'), null);
  assert.equal(resolveStoredThumbnailPath('/forum/uploads/123e4567-e89b-12d3-a456-426614174000.png'), null);
  assert.equal(resolveStoredThumbnailPath(null), null);
});

test('resolveStudentThumbnailUrl builds full API URL for Student sync', () => {
  assert.equal(
    resolveStudentThumbnailUrl('/courses/thumbnails/123e4567-e89b-12d3-a456-426614174000.webp', 'http://127.0.0.1:3001/api/v1/'),
    'http://127.0.0.1:3001/api/v1/courses/thumbnails/123e4567-e89b-12d3-a456-426614174000.webp'
  );
  assert.equal(
    resolveStudentThumbnailUrl('https://cdn.example.com/cover.jpg', 'http://127.0.0.1:3001/api/v1'),
    'https://cdn.example.com/cover.jpg'
  );
  assert.equal(resolveStudentThumbnailUrl('', 'http://127.0.0.1:3001/api/v1'), null);
  assert.equal(resolveStudentThumbnailUrl(null, 'http://127.0.0.1:3001/api/v1'), null);
});
