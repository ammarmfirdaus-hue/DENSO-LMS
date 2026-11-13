const test = require('node:test');
const assert = require('node:assert/strict');
const {
  buildDateWindow,
  normalizeDaysWindow,
  parseMaterialTypes
} = require('../../src/services/dashboard-analytics-helpers');

test('normalizeDaysWindow restricts timeframe to supported chart windows', () => {
  assert.equal(normalizeDaysWindow('7'), 7);
  assert.equal(normalizeDaysWindow(14), 14);
  assert.equal(normalizeDaysWindow(30), 30);
  assert.equal(normalizeDaysWindow('999'), 7);
  assert.equal(normalizeDaysWindow('invalid'), 7);
});

test('buildDateWindow produces ordered contiguous UTC date buckets with Indonesian labels', () => {
  const anchor = new Date('2026-11-12T08:30:00Z');
  const window7 = buildDateWindow(7, anchor);
  assert.equal(window7.length, 7);
  assert.equal(window7[0].isoDate, '2026-11-06');
  assert.equal(window7[0].label, '6 Nov');
  assert.equal(window7[6].isoDate, '2026-11-12');
  assert.equal(window7[6].label, '12 Nov');

  const window14 = buildDateWindow(14, anchor);
  assert.equal(window14.length, 14);
  assert.equal(window14[0].isoDate, '2026-10-30');
  assert.equal(window14[0].label, '30 Okt');
});

test('parseMaterialTypes aggregates counts and preserves core display categories', () => {
  const result = parseMaterialTypes([
    { material_type: 'text', total: 14 },
    { material_type: 'video', total: 7 },
    { material_type: 'pdf', total: 3 },
    { material_type: 'document', total: 2 }
  ]);
  const map = new Map(result.map(item => [item.type, item.count]));
  assert.equal(map.get('text'), 14);
  assert.equal(map.get('video'), 7);
  assert.equal(map.get('pdf'), 3);
  assert.equal(map.get('document'), 2);
  assert.equal(map.get('link'), 0);
});
