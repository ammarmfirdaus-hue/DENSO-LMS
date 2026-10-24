const assert = require('node:assert/strict');
const test = require('node:test');
const { parseForumImageBuffer } = require('../../src/services/forum-image');
const { forumEvents, publishForumEvent } = require('../../src/services/forum-events');
const { notificationEvents, publishNotificationEvent } = require('../../src/services/notification-events');

test('forum image validation checks format signatures and MIME type', async t => {
  const valid = [
    ['image/jpeg', Buffer.from([0xff, 0xd8, 0xff, 0x00]), '.jpg'],
    ['image/png', Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]), '.png'],
    ['image/gif', Buffer.from('GIF89a-image'), '.gif'],
    ['image/webp', Buffer.from('RIFFxxxxWEBP'), '.webp']
  ];
  for (const [mime, content, extension] of valid) {
    await t.test(mime, () => assert.deepEqual(parseForumImageBuffer(mime, content), { extension }));
  }
  assert.equal(parseForumImageBuffer('image/png', Buffer.from('<script>')), null);
  assert.equal(parseForumImageBuffer('image/jpeg', Buffer.alloc(0)), null);
  assert.equal(parseForumImageBuffer('image/svg+xml', Buffer.from('<svg/>')), null);
});

test('forum event is delivered to active stream subscribers with an event id', () => {
  const event = new Promise(resolve => {
    forumEvents.once('change', resolve);
    publishForumEvent({ courseCode: 'QA-COURSE', sessionCode: '1', type: 'thread_created', threadId: 17 });
  });
  return event.then(value => {
    assert.match(value.id, /^\d+-[a-z0-9]+$/);
    assert.equal(value.courseCode, 'QA-COURSE');
    assert.equal(value.sessionCode, '1');
    assert.equal(value.type, 'thread_created');
    assert.equal(value.threadId, 17);
  });
});

test('notification events normalize roles and scope user-specific changes', () => {
  const event = new Promise(resolve => {
    notificationEvents.once('change', resolve);
    publishNotificationEvent({ roles: ['student', 'student'], recipientNpk: 42, type: 'read' });
  });
  return event.then(value => {
    assert.deepEqual(value.roles, ['student']);
    assert.equal(value.recipientNpk, '42');
    assert.equal(value.type, 'read');
    assert.equal(Number.isFinite(value.id), true);
  });
});
