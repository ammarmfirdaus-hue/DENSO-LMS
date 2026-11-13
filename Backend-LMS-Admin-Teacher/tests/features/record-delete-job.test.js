require('../helpers/security-test-helpers');
const assert = require('node:assert/strict');
const test = require('node:test');
const Module = require('node:module');
const path = require('node:path');

test('records_delete job handler safely syncs student grade without iterable error', async () => {
  let mockSqlQueried = false;
  let mockMysqlUpdated = false;

  const fakePool = {
    request() {
      const inputs = {};
      return {
        input(name, _type, value) { inputs[name] = value; return this; },
        async query(statement) {
          mockSqlQueried = true;
          if (statement.includes('UPDATE dbo.student_progress_snapshots')) {
            return { recordset: [{ affected: 1 }] };
          }
          if (statement.includes('SELECT score,grade_feedback,graded_at FROM dbo.student_progress_snapshots')) {
            // node-mssql returns an object with recordset array (NOT an iterable array itself!)
            return {
              recordset: [{ score: null, grade_feedback: null, graded_at: null }],
              rowsAffected: [1]
            };
          }
          return { recordset: [] };
        }
      };
    }
  };

  const fakeMysql = {
    createConnection: async () => ({
      execute: async (statement, params) => {
        mockMysqlUpdated = true;
        assert.ok(statement.includes('UPDATE course_enrollments'));
        assert.equal(params[3], 'DLS-DEMO-STU-091');
        assert.equal(params[4], 'MOD-039');
        return [{ affectedRows: 1 }, []];
      },
      end: async () => {}
    })
  };

  const originalLoad = Module._load;
  Module._load = function (request, parent, isMain) {
    if (request === '../config/database') {
      return { sql: { VarChar: () => {}, BigInt: 'BigInt' }, pool: fakePool };
    }
    if (request === 'mysql2/promise') {
      return fakeMysql;
    }
    return originalLoad.call(this, request, parent, isMain);
  };

  let recordDeleteModule;
  try {
    delete require.cache[require.resolve('../../src/services/student-sync')];
    delete require.cache[require.resolve('../../src/services/record-delete-job-handler')];
    recordDeleteModule = require('../../src/services/record-delete-job-handler');
  } finally {
    Module._load = originalLoad;
  }

  let registeredHandler = null;
  recordDeleteModule.registerRecordDeleteJobHandler((type, handler) => {
    if (type === 'records_delete') registeredHandler = handler;
  });

  assert.ok(registeredHandler, 'Handler records_delete must be registered');

  // Test item matching user's exact scenario:
  const resultMessage = await registeredHandler(
    { kind: 'grade', npk: 'DLS-DEMO-STU-091', courseCode: 'MOD-039' },
    { ownerRole: 'admin', ownerNpk: 'DLS-DEMO-ADM-001' }
  );

  assert.equal(resultMessage, 'Nilai dihapus; progres course tetap tersimpan.');
  assert.ok(mockSqlQueried, 'MSSQL query should have run');
  assert.ok(mockMysqlUpdated, 'MySQL student sync should have run');
});
