require('../helpers/security-test-helpers');
const assert = require('node:assert/strict');
const test = require('node:test');
const Module = require('node:module');
const path = require('node:path');

test('enrollments_decision job handler approves enrollment and sends notification safely', async () => {
  let enrollmentDecided = false;
  let notificationInserted = false;

  const fakeEnrollmentsService = {
    decideEnrollmentRequest: async ({ requestId, action, adminNpk, note }) => {
      enrollmentDecided = true;
      assert.equal(requestId, 42);
      assert.equal(action, 'approved');
      assert.equal(adminNpk, 'DLS-DEMO-ADM-001');
      return {
        studentNpk: 'DLS-DEMO-STU-100',
        studentName: 'Budi Santoso',
        courseCode: 'MOD-039',
        courseTitle: 'Modul Operasional 039'
      };
    }
  };

  const fakePool = {
    request() {
      const inputs = {};
      return {
        input(name, _type, value) { inputs[name] = value; return this; },
        async query(statement) {
          if (statement.includes('INSERT INTO dbo.lms_notifications')) {
            notificationInserted = true;
            assert.equal(inputs.npk, 'DLS-DEMO-STU-100');
            return { recordset: [{ notification_id: 888 }] };
          }
          return { recordset: [] };
        }
      };
    }
  };

  const originalLoad = Module._load;
  Module._load = function (request, parent, isMain) {
    if (request === '../config/database') {
      return { sql: { VarChar: () => {}, NVarChar: () => {}, BigInt: 'BigInt' }, pool: fakePool };
    }
    if (request === './course-enrollments') {
      return fakeEnrollmentsService;
    }
    if (request === './notification-events') {
      return { publishNotificationEvent: () => {} };
    }
    return originalLoad.call(this, request, parent, isMain);
  };

  let handlerModule;
  try {
    delete require.cache[require.resolve('../../src/services/enrollment-job-handler')];
    handlerModule = require('../../src/services/enrollment-job-handler');
  } finally {
    Module._load = originalLoad;
  }

  let registeredHandler = null;
  handlerModule.registerEnrollmentJobHandler((type, handler) => {
    if (type === 'enrollments_decision') registeredHandler = handler;
  });

  assert.ok(registeredHandler, 'Handler enrollments_decision must be registered');

  const resultMessage = await registeredHandler(
    { requestId: 42, action: 'approved', note: 'Disetujui untuk kelas perbaikan' },
    { ownerRole: 'admin', ownerNpk: 'DLS-DEMO-ADM-001', jobId: 123 }
  );

  assert.equal(resultMessage, 'Akses untuk Budi Santoso (MOD-039) disetujui.');
  assert.ok(enrollmentDecided, 'decideEnrollmentRequest must be called');
  assert.ok(notificationInserted, 'Notification record must be inserted');
});

