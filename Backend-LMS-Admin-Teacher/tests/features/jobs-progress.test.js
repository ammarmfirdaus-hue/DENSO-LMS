const assert = require('node:assert/strict');
const Module = require('node:module');
const path = require('node:path');
const test = require('node:test');

let scenario;
let createdJob = null;
const fakeSql = {
  BigInt: 'BigInt',
  Bit: 'Bit',
  VarChar: length => ({ type: 'VarChar', length }),
  NVarChar: length => ({ type: 'NVarChar', length })
};
const fakePool = {
  request() {
    const inputs = {};
    return {
      input(name, _type, value) { inputs[name] = value; return this; },
      async query(statement) {
        const query = statement.replace(/\s+/g, ' ').trim();
        if (query.includes('SELECT job_id FROM dbo.lms_admin_jobs')) {
          scenario.ownerLookup = { query, inputs };
          return { recordset: scenario.jobExists ? [{ job_id: scenario.jobId }] : [] };
        }
        if (query.includes('SELECT status,total_items,processed_items,success_count,failure_count,current_item,updated_at')) {
          scenario.statusReads += 1;
          const index = Math.min(scenario.statusReads - 1, scenario.snapshots.length - 1);
          return { recordset: [scenario.snapshots[index]] };
        }
        if (query.includes('SELECT event_id,item_no,item_label,item_status,message,created_at FROM dbo.lms_admin_job_events')) {
          scenario.eventQueries.push({ query, inputs });
          const events = scenario.eventsByRead[Math.min(scenario.eventQueries.length - 1, scenario.eventsByRead.length - 1)];
          return { recordset: events };
        }
        throw new Error(`Unexpected jobs progress query: ${query}`);
      }
    };
  }
};

const originalLoad = Module._load;
Module._load = function loadWithJobMocks(request, parent, isMain) {
  if (request === '../config/database' && parent?.filename.endsWith(`${path.sep}routes${path.sep}jobs.js`)) {
    return { sql: fakeSql, pool: fakePool };
  }
  if (request === '../middleware/auth' && parent?.filename.endsWith(`${path.sep}routes${path.sep}jobs.js`)) {
    return { authenticate: (_req, _res, next) => next(), allowRoles: () => (_req, _res, next) => next() };
  }
  if (request === '../services/admin-jobs' && parent?.filename.endsWith(`${path.sep}routes${path.sep}jobs.js`)) {
    return { createJob: async job => { createdJob = job; return 700; }, stopJob() {}, resumeJob() {} };
  }
  if (request === '../services/student-auth' && parent?.filename.endsWith(`${path.sep}routes${path.sep}jobs.js`)) {
    return { listStudentAccounts: async () => [] };
  }
  return originalLoad.call(this, request, parent, isMain);
};
const router = require('../../src/routes/jobs');
Module._load = originalLoad;

const streamHandler = router.stack.find(layer => layer.route?.path === '/:id/stream').route.stack[0].handle;
const createUsersHandler = router.stack.find(layer => layer.route?.path === '/users').route.stack[0].handle;

function makeResponse() {
  const chunks = [];
  const listeners = {};
  return {
    chunks,
    headers: {},
    writableEnded: false,
    destroyed: false,
    set(values) { Object.assign(this.headers, values); return this; },
    flushHeaders() {},
    write(value) { chunks.push(value); return true; },
    end() { this.writableEnded = true; },
    on(event, listener) { listeners[event] = listener; return this; },
    status(code) { this.statusCode = code; return this; },
    emit(event) { listeners[event]?.(); }
  };
}

function waitFor(predicate, timeoutMs = 2500) {
  const deadline = Date.now() + timeoutMs;
  return new Promise((resolve, reject) => {
    const poll = () => {
      if (predicate()) return resolve();
      if (Date.now() >= deadline) return reject(new Error('Timed out waiting for the jobs progress stream.'));
      setTimeout(poll, 10);
    };
    poll();
  });
}

function makeRequest({ role = 'admin', owner = 'QA-ADMIN', lastEventId = '12', id = '41' } = {}) {
  return {
    params: { id },
    query: {},
    user: { role, sub: owner },
    get(name) { return name.toLowerCase() === 'last-event-id' ? lastEventId : undefined; },
    on() {}
  };
}

function makeScenario(overrides = {}) {
  return {
    jobId: 41,
    jobExists: true,
    ownerLookup: null,
    statusReads: 0,
    eventQueries: [],
    snapshots: [{
      status: 'completed_with_errors', total_items: 2, processed_items: 2,
      success_count: 1, failure_count: 1, current_item: 'DLS-QA-ITEM-2', updated_at: '2026-10-25T00:00:02.000Z'
    }],
    eventsByRead: [[
      { event_id: 13, item_no: 1, item_label: 'DLS-QA-ITEM-1', item_status: 'completed', message: 'Selesai.', created_at: '2026-10-25T00:00:01.000Z' },
      { event_id: 14, item_no: 2, item_label: 'DLS-QA-ITEM-2', item_status: 'failed', message: 'Data tidak ditemukan.', created_at: '2026-10-25T00:00:02.000Z' }
    ]],
    ...overrides
  };
}

test('job progress SSE resumes from Last-Event-ID and emits ordered items and terminal status', async () => {
  scenario = makeScenario();
  const req = makeRequest();
  const res = makeResponse();

  await streamHandler(req, res, error => { throw error; });
  await waitFor(() => res.writableEnded);

  assert.equal(res.statusCode, undefined);
  assert.match(res.headers['Content-Type'], /text\/event-stream/);
  assert.equal(scenario.ownerLookup.inputs.id, 41);
  assert.equal(scenario.eventQueries[0].inputs.after, 12);
  const output = res.chunks.join('');
  assert.match(output, /retry: 1500/);
  assert.match(output, /event: progress\ndata: .*"status":"completed_with_errors"/);
  assert.match(output, /id: 13\nevent: item\ndata: .*"item_status":"completed"/);
  assert.match(output, /id: 14\nevent: item\ndata: .*"item_status":"failed"/);
  assert.match(output, /event: settled/);
  assert.ok(output.indexOf('id: 13') < output.indexOf('id: 14'), 'item events are emitted in database order');
});

test('email credential jobs require a new password and queue it for delivery', async () => {
  const req = {
    user: { role: 'admin', sub: 'QA-ADMIN' },
    body: { items: [{ action: 'email', npk: 'DLS-QA-USER', label: 'QA User', fullname: 'QA User', email: 'qa@example.test', role: 'teacher', status: 'active' }] }
  };
  const res = {
    status(code) { this.statusCode = code; return this; },
    json(body) { this.body = body; return this; }
  };
  createdJob = null;

  await createUsersHandler(req, res, error => { throw error; });

  assert.equal(res.statusCode, 400);
  assert.equal(res.body.error.code, 'EMAIL_PASSWORD_REQUIRED');
  assert.equal(createdJob, null, 'job is rejected before it enters the queue');

  req.body.items[0].password = 'Temporary-Access-123!';
  await createUsersHandler(req, res, error => { throw error; });
  assert.equal(res.statusCode, 202);
  assert.equal(createdJob.items[0].password, 'Temporary-Access-123!');
});

test('live SSE stream pushes later progress and item results without reconnecting', async () => {
  scenario = makeScenario({
    snapshots: [
      { status: 'running', total_items: 2, processed_items: 0, success_count: 0, failure_count: 0, current_item: 'DLS-QA-ITEM-1', updated_at: '2026-10-25T00:00:00.000Z' },
      { status: 'completed_with_errors', total_items: 2, processed_items: 2, success_count: 1, failure_count: 1, current_item: 'DLS-QA-ITEM-2', updated_at: '2026-10-25T00:00:02.000Z' }
    ],
    eventsByRead: [[], [
      { event_id: 13, item_no: 1, item_label: 'DLS-QA-ITEM-1', item_status: 'completed', message: 'Selesai.', created_at: '2026-10-25T00:00:01.000Z' },
      { event_id: 14, item_no: 2, item_label: 'DLS-QA-ITEM-2', item_status: 'failed', message: 'Data tidak ditemukan.', created_at: '2026-10-25T00:00:02.000Z' }
    ]]
  });
  const res = makeResponse();

  await streamHandler(makeRequest({ lastEventId: '0' }), res, error => { throw error; });
  await waitFor(() => res.writableEnded);

  const output = res.chunks.join('');
  assert.equal(scenario.statusReads, 2, 'the server polls again while the job is active');
  assert.equal(scenario.eventQueries[0].inputs.after, 0);
  assert.equal(scenario.eventQueries[1].inputs.after, 0);
  assert.equal((output.match(/event: progress/g) || []).length, 2, 'the stream includes both the initial and changed job snapshots');
  assert.match(output, /event: progress\ndata: .*"status":"running"/);
  assert.match(output, /event: progress\ndata: .*"status":"completed_with_errors"/);
  assert.match(output, /id: 13\nevent: item/);
  assert.match(output, /id: 14\nevent: item/);
  assert.match(output, /event: settled/);
});

test('teacher stream queries are scoped to the authenticated job owner', async () => {
  scenario = makeScenario({ snapshots: [{
    status: 'completed', total_items: 1, processed_items: 1, success_count: 1,
    failure_count: 0, current_item: 'DLS-QA-ITEM', updated_at: '2026-10-25T00:00:01.000Z'
  }], eventsByRead: [[]] });
  const req = makeRequest({ role: 'teacher', owner: 'QA-TEACHER' });
  const res = makeResponse();

  await streamHandler(req, res, error => { throw error; });
  await waitFor(() => res.writableEnded);

  assert.match(scenario.ownerLookup.query, /created_by=@owner/);
  assert.equal(scenario.ownerLookup.inputs.owner, 'QA-TEACHER');
});

test('closing a live progress stream stops its polling timer', async () => {
  scenario = makeScenario({ snapshots: [{
    status: 'running', total_items: 2, processed_items: 0, success_count: 0,
    failure_count: 0, current_item: 'DLS-QA-ITEM-1', updated_at: '2026-10-25T00:00:01.000Z'
  }], eventsByRead: [[]] });
  const res = makeResponse();

  await streamHandler(makeRequest(), res, error => { throw error; });
  await waitFor(() => scenario.statusReads === 1);
  res.emit('close');
  await new Promise(resolve => setTimeout(resolve, 1100));

  assert.equal(scenario.statusReads, 1, 'no extra poll runs after the browser closes the stream');
  assert.equal(res.writableEnded, false, 'a live job does not close its stream before settlement');
});

