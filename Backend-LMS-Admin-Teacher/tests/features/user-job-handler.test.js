const assert = require('node:assert/strict');
const Module = require('node:module');
const path = require('node:path');
const test = require('node:test');
const bcrypt = require('bcryptjs');

const calls = { transactions: 0, deletes: 0, deactivations: 0, commits: 0, rollbacks: 0 };
const fakePool = { request: () => new FakeRequest(null) };
let studentDelete = async () => true;
let studentDeactivate = async () => true;
let studentState = null;
let studentAccountUpdate = async () => true;
let employeePasswordHash = '';
const sentCredentials = [];

class FakeTransaction {
  constructor() { calls.transactions += 1; }
  async begin() {}
  async commit() { calls.commits += 1; }
  async rollback() { calls.rollbacks += 1; }
}

class FakeRequest {
  constructor(transaction) { this.transaction = transaction; this.inputs = {}; }
  input(name, _type, value) { this.inputs[name] = value; return this; }
  async query(statement) {
    const query = statement.replace(/\s+/g, ' ').trim();
    if (query.startsWith('SELECT fullname,email,role,status FROM dbo.employee_accounts')) {
      return { recordset: [{ fullname: 'QA Teacher', email: 'qa@example.test', role: 'teacher', status: 'active' }] };
    }
    if (query.startsWith('UPDATE dbo.employee_accounts SET password_hash=')) {
      employeePasswordHash = this.inputs.hash;
      return { rowsAffected: [1] };
    }
    if (query.startsWith('SELECT role,status FROM dbo.employee_accounts')) {
      return { recordset: [{ role: 'teacher', status: 'active' }] };
    }
    if (query.startsWith('DELETE FROM dbo.employee_accounts')) {
      calls.deletes += 1;
      throw Object.assign(new Error('foreign key reference'), { number: 547 });
    }
    if (query.startsWith('UPDATE dbo.employee_accounts SET status=\'inactive\'')) {
      calls.deactivations += 1;
      return { recordset: [] };
    }
    throw new Error(`Unexpected user job query: ${query}`);
  }
}

const fakeSql = {
  Transaction: FakeTransaction,
  Request: FakeRequest,
  ISOLATION_LEVEL: { SERIALIZABLE: 'SERIALIZABLE' },
  VarChar: length => ({ type: 'VarChar', length })
};
const originalLoad = Module._load;
Module._load = function loadWithUserHandlerMocks(request, parent, isMain) {
  if (request === '../config/database' && parent?.filename.endsWith(`${path.sep}services${path.sep}user-job-handler.js`)) {
    return { sql: fakeSql, pool: fakePool };
  }
  if (request === './student-auth' && parent?.filename.endsWith(`${path.sep}services${path.sep}user-job-handler.js`)) {
    return {
      createStudentAccount: async () => {}, updateStudentAccount: (...args) => studentAccountUpdate(...args), getStudentAccountState: async () => studentState,
      deactivateStudentAccount: (...args) => studentDeactivate(...args), deleteStudentAccount: (...args) => studentDelete(...args)
    };
  }
  if (request === './account-mail' && parent?.filename.endsWith(`${path.sep}services${path.sep}user-job-handler.js`)) {
    return { sendAccountCredentials: async credentials => { sentCredentials.push(credentials); } };
  }
  return originalLoad.call(this, request, parent, isMain);
};
const { registerUserJobHandler } = require('../../src/services/user-job-handler');
Module._load = originalLoad;

let handleUserJob;
registerUserJobHandler((type, handler) => {
  assert.equal(type, 'users');
  handleUserJob = handler;
});

test('delete of an employee account with FK history falls back to deactivation and succeeds', async () => {
  Object.keys(calls).forEach(key => { calls[key] = 0; });

  const result = await handleUserJob({ action: 'delete', npk: 'DLS-QA-TEACHER', role: 'teacher' }, { ownerNpk: 'DLS-QA-ADMIN' });

  assert.equal(calls.deletes, 1, 'the job first attempts permanent deletion');
  assert.equal(calls.deactivations, 1, 'a foreign-key history conflict triggers account deactivation');
  assert.equal(calls.commits, 1, 'the fallback status change commits');
  assert.match(result, /Penghapusan permanen dilewati karena akun memiliki riwayat/);
  assert.match(result, /dinonaktifkan dan riwayat tetap tersimpan/);
});

test('delete of a Student account with course history falls back to deactivation and succeeds', async () => {
  let deactivationCount = 0;
  studentDelete = async () => { throw Object.assign(new Error('student account has history'), { code: 'USER_HAS_HISTORY' }); };
  studentDeactivate = async npk => { assert.equal(npk, 'DLS-QA-STUDENT'); deactivationCount += 1; return true; };
  try {
    const result = await handleUserJob({ action: 'delete', npk: 'DLS-QA-STUDENT', role: 'student' }, { ownerNpk: 'DLS-QA-ADMIN' });
    assert.equal(deactivationCount, 1);
    assert.match(result, /Penghapusan permanen dilewati karena akun memiliki riwayat/);
    assert.match(result, /akun Student dinonaktifkan dan riwayat tetap tersimpan/);
  } finally {
    studentDelete = async () => true;
    studentDeactivate = async () => true;
  }
});

test('emailing employee account saves the new password hash and sends the actual password', async () => {
  employeePasswordHash = '';
  sentCredentials.length = 0;
  const password = 'Temporary-Access-123!';

  const result = await handleUserJob({ action: 'email', npk: 'DLS-QA-TEACHER', role: 'teacher', password }, { ownerNpk: 'DLS-QA-ADMIN' });

  assert.ok(employeePasswordHash.startsWith('$2'), 'database receives only a bcrypt hash');
  assert.equal(await bcrypt.compare(password, employeePasswordHash), true);
  assert.equal(sentCredentials[0].password, password);
  assert.equal(sentCredentials[0].to, 'qa@example.test');
  assert.match(result, /kata sandi baru terkirim ke email/);
});

test('emailing Student account updates its hash and sends the actual password', async () => {
  studentState = { fullname: 'QA Student', email: 'student@example.test', role: 'student', status: 'active', hasPassword: true };
  let update = null;
  studentAccountUpdate = async (npk, data) => { update = { npk, ...data }; return true; };
  sentCredentials.length = 0;
  const password = 'Temporary-Student-123!';
  try {
    const result = await handleUserJob({ action: 'email', npk: 'DLS-QA-STUDENT', role: 'student', password }, { ownerNpk: 'DLS-QA-ADMIN' });

    assert.equal(update.npk, 'DLS-QA-STUDENT');
    assert.ok(update.passwordHash.startsWith('$2'));
    assert.equal(await bcrypt.compare(password, update.passwordHash), true);
    assert.equal(sentCredentials[0].password, password);
    assert.equal(sentCredentials[0].to, 'student@example.test');
    assert.match(result, /kata sandi baru terkirim ke email/);
  } finally {
    studentState = null;
    studentAccountUpdate = async () => true;
  }
});

