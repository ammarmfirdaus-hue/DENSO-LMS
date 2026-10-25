const assert = require('node:assert/strict');
const Module = require('node:module');
const path = require('node:path');
const test = require('node:test');

let recovery;
const fakePool = {
  request() {
    return {
      async query(statement) {
        const query = statement.replace(/\s+/g, ' ').trim();
        if (query.includes("WHERE status='running'")) {
          recovery.started = true;
          await recovery.gate;
          recovery.finished = true;
          return { recordset: [] };
        }
        if (query.includes('WITH (UPDLOCK,READPAST,ROWLOCK)')) {
          recovery.claims += 1;
          return { recordset: [] };
        }
        throw new Error(`Unexpected admin job worker query: ${query}`);
      }
    };
  }
};

const originalLoad = Module._load;
Module._load = function loadWithWorkerMocks(request, parent, isMain) {
  if (request === '../config/database' && parent?.filename.endsWith(`${path.sep}services${path.sep}admin-jobs.js`)) {
    return { sql: {}, pool: fakePool };
  }
  if (request === '../config/env' && parent?.filename.endsWith(`${path.sep}services${path.sep}admin-jobs.js`)) {
    return { JWT_SECRET: 'test-only-secret-for-admin-jobs-recovery' };
  }
  return originalLoad.call(this, request, parent, isMain);
};
const { startAdminJobWorker } = require('../../src/services/admin-jobs');
Module._load = originalLoad;

function waitFor(predicate, timeoutMs = 2000) {
  const deadline = Date.now() + timeoutMs;
  return new Promise((resolve, reject) => {
    const poll = () => {
      if (predicate()) return resolve();
      if (Date.now() >= deadline) return reject(new Error('Timed out waiting for the admin job worker.'));
      setTimeout(poll, 10);
    };
    poll();
  });
}

test('worker waits for interrupted-job recovery before claiming queued jobs', async () => {
  let releaseRecovery;
  recovery = {
    started: false,
    finished: false,
    claims: 0,
    gate: new Promise(resolve => { releaseRecovery = resolve; })
  };

  const workerReady = startAdminJobWorker();
  let stopWorker;
  try {
    await waitFor(() => recovery.started);
    await new Promise(resolve => setTimeout(resolve, 40));
    assert.equal(recovery.claims, 0, 'no queue claim starts while recovery is pending');

    releaseRecovery();
    stopWorker = await workerReady;
    await waitFor(() => recovery.claims > 0);
    assert.equal(recovery.finished, true);
  } finally {
    releaseRecovery();
    if (stopWorker) await stopWorker();
  }
});

