const express = require('express');
const { z } = require('zod');
const env = require('../config/env');
const { authenticate, allowRoles } = require('../middleware/auth');
const { getApiBaseUrl, saveApiBaseUrl } = require('../services/system-settings');
const { syncStudentNow, getStudentSyncStatus } = require('../services/student-sync');

const router = express.Router();

// Bootstrap endpoint lets the admin frontend discover its database-persisted API URL.
router.get('/runtime-config', (_req, res) => res.json({ apiBaseUrl: getApiBaseUrl() }));

router.use(authenticate, allowRoles('admin'));

router.get('/api', (_req, res) => res.json({
  apiBaseUrl: getApiBaseUrl(),
  studentSync: {
    enabled: env.STUDENT_SYNC_ENABLED === 'true',
    configured: getStudentSyncStatus().connected,
    host: env.STUDENT_DB_HOST,
    database: env.STUDENT_DB_NAME,
    intervalMs: env.STUDENT_SYNC_INTERVAL_MS,
    ...getStudentSyncStatus()
  }
}));

router.put('/api', async (req, res, next) => {
  const parsed = z.object({ apiBaseUrl: z.string().trim().url().max(500) }).strict().safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_API_URL', message: 'Masukkan URL API backend yang valid.' } });
  try {
    const saved = await saveApiBaseUrl(parsed.data.apiBaseUrl, req.user.sub);
    res.json({ saved: true, ...saved });
  } catch (error) { res.status(400).json({ error: { code: 'INVALID_API_URL', message: error.message } }); }
});

router.post('/api/student-sync', async (_req, res, next) => {
  try {
    const started = await syncStudentNow();
    if (!started) return res.status(409).json({ error: { code: 'STUDENT_SYNC_UNAVAILABLE', message: 'Sinkronisasi sedang berjalan atau koneksi MySQL Student belum dikonfigurasi. Periksa STUDENT_DB_* di .env backend.' } });
    res.json({ synced: true, status: getStudentSyncStatus() });
  } catch (error) { next(error); }
});

module.exports = router;
