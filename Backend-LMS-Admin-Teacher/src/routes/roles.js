const express = require('express');
const { z } = require('zod');
const env = require('../config/env');
const { authenticate, requirePermission } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');
const {
  ROLE_KEYS,
  PERMISSION_KEY_SET,
  ROLE_PRESETS,
  getRoleAccessProfile,
  getRoleManagementSnapshot,
  getRoleAuditLogs,
  updateRolePermissions
} = require('../services/role-permissions');

const router = express.Router();

function trustedStaffOrigin(req, res, next) {
  const origin = req.get('origin');
  let allowedOrigin = '';
  try { allowedOrigin = new URL(env.FRONTEND_ORIGIN).origin; } catch { /* env schema already validates it */ }
  if (!origin || origin !== allowedOrigin) {
    return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan pengelolaan hak akses ditolak.' } });
  }
  next();
}

const roleUpdateSchema = z.object({
  displayName: z.string().trim().min(2).max(100),
  description: z.string().trim().max(500).default(''),
  canAccessStaffPortal: z.boolean(),
  permissions: z.array(z.string().trim().min(2).max(80)).refine(
    items => items.every(key => PERMISSION_KEY_SET.has(key)),
    { message: 'Terdapat kunci izin yang tidak terdaftar pada katalog sistem.' }
  ),
  note: z.string().trim().max(240).optional().default('')
}).strict();

/**
 * Mengambil profil izin efektif untuk pengguna yang sedang masuk.
 */
router.get('/me', authenticate, async (req, res, next) => {
  try {
    if (req.user?.scope === 'reviewer_portal') {
      return res.json({
        role_key: 'reviewer',
        canAccessStaffPortal: false,
        permissions: ['materials.review']
      });
    }
    const profile = await getRoleAccessProfile(req.user.role);
    res.json(profile);
  } catch (error) { next(error); }
});

/**
 * Mengambil seluruh katalog permission, matriks role, preset, dan audit log perubahan hak akses.
 */
router.get('/', authenticate, requirePermission('roles.manage'), async (_req, res, next) => {
  try {
    const snapshot = await getRoleManagementSnapshot();
    res.set('Cache-Control', 'private, no-store');
    res.json(snapshot);
  } catch (error) { next(error); }
});

/**
 * Mengambil riwayat audit perubahan hak akses role dengan dukungan paginasi.
 */
router.get('/audit', authenticate, requirePermission('roles.manage'), async (req, res, next) => {
  try {
    const logs = await getRoleAuditLogs(200);
    const page = paginateCollection(req, logs);
    res.json({ auditLogs: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

/**
 * Memperbarui definisi dan daftar izin aktif milik suatu role.
 */
router.put('/:roleKey', authenticate, requirePermission('roles.manage'), trustedStaffOrigin, async (req, res, next) => {
  const roleKey = String(req.params.roleKey || '').toLowerCase();
  if (!ROLE_KEYS.includes(roleKey)) {
    return res.status(404).json({ error: { code: 'ROLE_NOT_FOUND', message: 'Role tidak ditemukan.' } });
  }
  const parsed = roleUpdateSchema.safeParse(req.body);
  if (!parsed.success) {
    return res.status(400).json({
      error: {
        code: 'INVALID_INPUT',
        message: parsed.error.issues?.[0]?.message || 'Konfigurasi role dan hak akses tidak valid.'
      }
    });
  }
  try {
    const outcome = await updateRolePermissions({
      roleKey,
      ...parsed.data,
      actorNpk: req.user.sub
    });
    const snapshot = await getRoleManagementSnapshot();
    res.json({
      updated: true,
      diff: outcome.diff,
      summary: outcome.summary,
      ...snapshot
    });
  } catch (error) { next(error); }
});

/**
 * Menerapkan template preset cepat ke role target dan langsung mencatat ke audit log.
 */
router.post('/:roleKey/preset', authenticate, requirePermission('roles.manage'), trustedStaffOrigin, async (req, res, next) => {
  const roleKey = String(req.params.roleKey || '').toLowerCase();
  if (!ROLE_KEYS.includes(roleKey)) {
    return res.status(404).json({ error: { code: 'ROLE_NOT_FOUND', message: 'Role tidak ditemukan.' } });
  }
  const parsed = z.object({ presetKey: z.string().trim().min(2).max(50) }).safeParse(req.body);
  if (!parsed.success || !ROLE_PRESETS[parsed.data.presetKey]) {
    return res.status(400).json({ error: { code: 'INVALID_PRESET', message: 'Preset role tidak tersedia.' } });
  }
  const preset = ROLE_PRESETS[parsed.data.presetKey];
  if (!preset.applicableRoles.includes(roleKey)) {
    return res.status(400).json({ error: { code: 'PRESET_MISMATCH', message: 'Preset tidak berlaku untuk role ini.' } });
  }
  try {
    const resolved = preset.resolve(roleKey);
    const currentSnapshot = await getRoleManagementSnapshot();
    const roleMeta = currentSnapshot.roles.find(r => r.role_key === roleKey);
    const outcome = await updateRolePermissions({
      roleKey,
      displayName: roleMeta?.display_name || roleKey,
      description: roleMeta?.description || '',
      canAccessStaffPortal: resolved.canAccessStaffPortal,
      permissions: resolved.permissions,
      actorNpk: req.user.sub,
      note: `Preset: ${preset.label}`
    });
    const snapshot = await getRoleManagementSnapshot();
    res.json({
      updated: true,
      diff: outcome.diff,
      summary: outcome.summary,
      ...snapshot
    });
  } catch (error) { next(error); }
});

module.exports = router;

