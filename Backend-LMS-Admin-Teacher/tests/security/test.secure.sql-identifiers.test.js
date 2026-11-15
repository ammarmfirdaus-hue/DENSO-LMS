const test = require('node:test');
const assert = require('node:assert/strict');
const { quoteBackupIdentifier } = require('../../src/services/backup-datasets');

test('backup SQL identifiers accept only simple allowlisted-style names', () => {
  assert.equal(quoteBackupIdentifier('course_materials'), '[course_materials]');
  assert.throws(() => quoteBackupIdentifier("courses]; DROP TABLE users;--"));
  assert.throws(() => quoteBackupIdentifier('dbo.courses'));
  assert.throws(() => quoteBackupIdentifier('1_courses'));
});
