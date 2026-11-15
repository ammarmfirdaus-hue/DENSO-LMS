const express = require('express');
const { z } = require('zod');
const env = require('../config/env');
const { pool } = require('../config/database');
const { authenticate, requirePermission } = require('../middleware/auth');
const { getApiBaseUrl, saveApiBaseUrl } = require('../services/system-settings');
const { syncStudentNow, getStudentSyncStatus } = require('../services/student-sync');
const { createJob } = require('../services/admin-jobs');
const { BACKUP_DATASETS, quoteBackupIdentifier } = require('../services/backup-datasets');
const multer = require('multer');

const router = express.Router();

// Hanya tabel berikut yang boleh diekspor. Nama tabel tidak pernah diterima
// langsung dari client sehingga tidak dapat dipakai untuk SQL injection.
const backupUpload = multer({ storage: multer.memoryStorage(), limits: { fileSize: 25 * 1024 * 1024, files: 1 } });

// Bootstrap endpoint lets the admin frontend discover its database-persisted API URL.
router.get('/runtime-config', (_req, res) => res.json({ apiBaseUrl: getApiBaseUrl() }));

router.use(authenticate, requirePermission('settings.api'));

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

router.post('/backup', async (req, res, next) => {
  const parsed = z.object({
    scope: z.enum(['all', 'selected']).default('all'),
    datasets: z.array(z.string().trim().max(40)).max(Object.keys(BACKUP_DATASETS).length).default([])
  }).strict().safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_BACKUP_REQUEST', message: 'Pilihan backup tidak valid.' } });
  const keys = parsed.data.scope === 'all'
    ? Object.keys(BACKUP_DATASETS)
    : [...new Set(parsed.data.datasets)].filter(key => Object.prototype.hasOwnProperty.call(BACKUP_DATASETS, key));
  if (!keys.length) return res.status(400).json({ error: { code: 'BACKUP_DATASET_REQUIRED', message: 'Pilih minimal satu data untuk dibackup.' } });

  try {
    const datasets = {};
    for (const key of keys) {
      const table = BACKUP_DATASETS[key].table;
      // SQL Server cannot parameterize table names.  The table still comes
      // exclusively from the server-side allowlist and is quoted/validated by
      // the shared helper before being included in the statement.
      const result = await pool.request().query(`SELECT * FROM dbo.${quoteBackupIdentifier(table)}`);
      datasets[key] = { table, count: result.recordset.length, rows: result.recordset };
    }
    const payload = JSON.stringify({
      format: 'dls-lms-json-backup', version: 1, exportedAt: new Date().toISOString(),
      exportedBy: req.user.sub, scope: parsed.data.scope, datasets
    });
    const stamp = new Date().toISOString().replace(/[:.]/g, '-');
    res.status(200).set({
      'Content-Type': 'application/json; charset=utf-8',
      'Content-Disposition': `attachment; filename="dls-lms-backup-${stamp}.json"`,
      'Cache-Control': 'no-store'
    }).send(payload);
  } catch (error) { return next(error); }
});

router.post('/backup/restore', backupUpload.single('backup'), async (req, res, next) => {
  if (!req.file) return res.status(400).json({ error: { code: 'BACKUP_FILE_REQUIRED', message: 'Pilih file backup JSON.' } });
  let parsed;
  try { parsed = JSON.parse(req.file.buffer.toString('utf8')); }
  catch { return res.status(400).json({ error: { code: 'INVALID_BACKUP_FILE', message: 'File backup bukan JSON yang valid.' } }); }
  if (!parsed || parsed.format !== 'dls-lms-json-backup' || !parsed.datasets || typeof parsed.datasets !== 'object') {
    return res.status(400).json({ error: { code: 'INVALID_BACKUP_FILE', message: 'Format file backup tidak dikenali.' } });
  }
  const items = [];
  for (const [dataset, value] of Object.entries(parsed.datasets)) {
    if (!Object.prototype.hasOwnProperty.call(BACKUP_DATASETS, dataset) || !value || !Array.isArray(value.rows)) continue;
    if (value.rows.length > 100000) return res.status(413).json({ error: { code: 'BACKUP_TOO_LARGE', message: 'Jumlah baris backup terlalu besar.' } });
    for (let index = 0; index < value.rows.length; index += 50) {
      const rows = value.rows.slice(index, index + 50);
      if (rows.some(row => !row || typeof row !== 'object' || Array.isArray(row))) return res.status(400).json({ error: { code: 'INVALID_BACKUP_ROWS', message: 'Isi baris backup tidak valid.' } });
      items.push({ dataset, rows, label: `${dataset} · ${index + 1}-${index + rows.length}` });
    }
  }
  if (!items.length || items.length > 5000) return res.status(400).json({ error: { code: 'BACKUP_JOB_SIZE_INVALID', message: 'Backup tidak memiliki data yang dapat dipulihkan atau terlalu besar.' } });
  try {
    const jobId = await createJob({ ownerNpk: req.user.sub, ownerRole: req.user.role, type: 'backup_restore', items });
    return res.status(202).json({ jobId, status: 'queued', total: items.length, message: 'Restore masuk antrean.' });
  } catch (error) { return next(error); }
});

module.exports = router;
