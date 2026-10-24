const express = require('express');
const crypto = require('node:crypto');
const { ipKeyGenerator } = require('express-rate-limit');
const { z } = require('zod');
const { buildLimiter } = require('../middleware/rate-limit');
const { login, setSsoCookie, clearSsoCookie } = require('../services/auth');
const { authenticate } = require('../middleware/auth');

const router = express.Router();
const loginLimiter = buildLimiter({
  windowMs: 15 * 60 * 1000,
  limit: 8,
  message: 'Terlalu banyak percobaan masuk untuk akun ini. Coba lagi setelah 15 menit.',
  keyGenerator: req => `${ipKeyGenerator(req.ip)}:${crypto.createHash('sha256').update(String(req.body?.npk || '').trim().toLowerCase()).digest('hex')}`
});
const loginSchema = z.object({ npk: z.string().trim().min(1).max(254), password: z.string().min(1).max(200) });

router.post('/login', loginLimiter, async (req, res, next) => {
  const parsed = loginSchema.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'NPK dan kata sandi wajib diisi.' } });
  try {
    const result = await login(parsed.data.npk, parsed.data.password);
    if (!result) return res.status(401).json({ error: { code: 'INVALID_CREDENTIALS', message: 'NPK atau kata sandi salah.' } });
    setSsoCookie(res, result.token);
    res.json(result);
  } catch (error) { next(error); }
});

router.get('/me', authenticate, async (req, res, next) => {
  try {
    const { pool, sql } = require('../config/database');
    const result = await pool.request()
      .input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT employee_npk, fullname, email, role, status FROM dbo.employee_accounts WHERE employee_npk = @npk`);
    const record = result.recordset[0];
    res.json({
      user: {
        npk: req.user.sub,
        name: record?.fullname || req.user.name,
        email: record?.email || '',
        role: record?.role || req.user.role
      }
    });
  } catch (error) {
    res.json({ user: { npk: req.user.sub, name: req.user.name, role: req.user.role } });
  }
});

router.put('/profile', authenticate, async (req, res, next) => {
  const bcrypt = require('bcryptjs');
  const { pool, sql } = require('../config/database');
  const profileSchema = z.object({
    fullname: z.string().trim().min(2).max(150),
    email: z.string().trim().email().max(254).optional(),
    currentPassword: z.string().optional(),
    newPassword: z.string().min(8).max(200).optional(),
  });

  const parsed = profileSchema.safeParse(req.body);
  if (!parsed.success) {
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Data yang dikirim tidak valid.' } });
  }

  const { fullname, email, currentPassword, newPassword } = parsed.data;
  const npk = req.user.sub;

  try {
    // Cari akun sekarang
    const accountResult = await pool.request()
      .input('npk', sql.VarChar(50), npk)
      .query(`SELECT password_hash FROM dbo.employee_accounts WHERE employee_npk = @npk`);
    
    if (!accountResult.recordset.length) {
      return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Akun tidak ditemukan.' } });
    }

    const currentHash = accountResult.recordset[0].password_hash;
    let newHash = null;

    if (newPassword) {
      if (!currentPassword) {
        return res.status(400).json({ error: { code: 'PASSWORD_REQUIRED', message: 'Kata sandi saat ini harus diisi untuk mengubah kata sandi baru.' } });
      }
      const match = await bcrypt.compare(currentPassword, currentHash);
      if (!match) {
        return res.status(401).json({ error: { code: 'INVALID_PASSWORD', message: 'Kata sandi saat ini salah.' } });
      }
      newHash = await bcrypt.hash(newPassword, 12);
    }

    const reqUpdate = pool.request()
      .input('npk', sql.VarChar(50), npk)
      .input('name', sql.NVarChar(150), fullname);

    let query = `UPDATE dbo.employee_accounts SET fullname = @name`;
    if (email) {
      reqUpdate.input('email', sql.NVarChar(254), email);
      query += `, email = @email`;
    }
    if (newHash) {
      reqUpdate.input('hash', sql.VarChar(255), newHash);
      query += `, password_hash = @hash`;
    }
    query += ` WHERE employee_npk = @npk`;

    await reqUpdate.query(query);

    res.json({
      message: 'Profil berhasil diperbarui.',
      user: {
        npk,
        name: fullname,
        email: email || '',
        role: req.user.role
      }
    });
  } catch (error) {
    next(error);
  }
});

router.post('/logout', (_req, res) => {
  clearSsoCookie(res);
  res.status(204).end();
});

module.exports = router;

