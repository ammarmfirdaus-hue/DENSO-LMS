const express = require('express');
const bcrypt = require('bcryptjs');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');

const router = express.Router();
router.use(authenticate, allowRoles('admin'));

const reviewerSchema = z.object({
  npk: z.string().trim().min(1).max(50),
  fullname: z.string().trim().min(2).max(150),
  email: z.string().trim().email().max(254).transform(val => val.toLowerCase()),
  password: z.string().min(12, 'Kata sandi minimal 12 karakter.').max(200),
  department: z.string().trim().max(100).optional().default(''),
  position: z.string().trim().max(100).optional().default(''),
  phoneNumber: z.string().trim().max(30).optional().default('')
});

const reviewerUpdateSchema = z.object({
  fullname: z.string().trim().min(2).max(150),
  email: z.string().trim().email().max(254).transform(val => val.toLowerCase()),
  department: z.string().trim().max(100).optional().default(''),
  position: z.string().trim().max(100).optional().default(''),
  phoneNumber: z.string().trim().max(30).optional().default(''),
  status: z.enum(['active', 'inactive']).default('active'),
  password: z.string().min(12, 'Kata sandi baru minimal 12 karakter.').max(200).optional().or(z.literal(''))
});

/**
 * GET /api/v1/reviewers
 * Mengambil seluruh daftar reviewer beserta statistik review aktif. Hanya untuk Admin.
 */
router.get('/', async (req, res, next) => {
  try {
    const result = await pool.request().query(`
      SELECT 
        ea.employee_npk,
        ea.fullname,
        ea.email,
        ea.role,
        ea.department,
        ea.position,
        ea.phone_number,
        ea.status,
        ea.created_at,
        ea.updated_at,
        (SELECT COUNT(*) FROM dbo.material_submissions ms WHERE ms.reviewer_npk = ea.employee_npk) AS total_reviews,
        (SELECT COUNT(*) FROM dbo.material_submissions ms WHERE ms.reviewer_npk = ea.employee_npk AND ms.status = 'pending_review') AS active_reviews
      FROM dbo.employee_accounts ea
      WHERE ea.role = 'reviewer'
      ORDER BY ea.status ASC, ea.fullname ASC
    `);

    const page = paginateCollection(req, result.recordset);
    res.json({
      reviewers: page.data,
      ...(page.pagination ? { pagination: page.pagination } : {})
    });
  } catch (error) {
    next(error);
  }
});

/**
 * POST /api/v1/reviewers
 * Mendaftarkan akun reviewer baru dengan profil HRD/eksekutif. Hanya untuk Admin.
 */
router.post('/', async (req, res, next) => {
  const parsed = reviewerSchema.safeParse(req.body);
  if (!parsed.success) {
    const message = parsed.error.issues?.[0]?.message || 'Data akun reviewer tidak valid.';
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message } });
  }

  const { npk, fullname, email, password, department, position, phoneNumber } = parsed.data;

  try {
    const hash = await bcrypt.hash(password, 12);
    await pool.request()
      .input('npk', sql.VarChar(50), npk)
      .input('name', sql.NVarChar(150), fullname)
      .input('email', sql.NVarChar(254), email)
      .input('hash', sql.VarChar(255), hash)
      .input('dept', sql.NVarChar(100), department || null)
      .input('pos', sql.NVarChar(100), position || null)
      .input('phone', sql.VarChar(30), phoneNumber || null)
      .query(`
        INSERT INTO dbo.employee_accounts (
          employee_npk, fullname, email, password_hash, role, department, position, phone_number, status
        ) VALUES (
          @npk, @name, @email, @hash, 'reviewer', @dept, @pos, @phone, 'active'
        )
      `);

    res.status(201).json({
      reviewer: {
        employee_npk: npk,
        fullname,
        email,
        department,
        position,
        phone_number: phoneNumber,
        status: 'active'
      },
      message: `Akun reviewer ${fullname} berhasil didaftarkan.`
    });
  } catch (error) {
    if (error.number === 2627 || error.number === 2601) {
      return res.status(409).json({
        error: { code: 'DUPLICATE_ACCOUNT', message: 'NPK atau alamat email reviewer sudah terdaftar.' }
      });
    }
    next(error);
  }
});

/**
 * PUT /api/v1/reviewers/:npk
 * Memperbarui data profil, status akun, atau mereset kata sandi reviewer. Hanya untuk Admin.
 */
router.put('/:npk', async (req, res, next) => {
  const npk = z.string().trim().min(1).max(50).safeParse(req.params.npk);
  if (!npk.success) {
    return res.status(400).json({ error: { code: 'INVALID_NPK', message: 'NPK tidak valid.' } });
  }

  const parsed = reviewerUpdateSchema.safeParse(req.body);
  if (!parsed.success) {
    const message = parsed.error.issues?.[0]?.message || 'Data perubahan tidak valid.';
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message } });
  }

  const { fullname, email, department, position, phoneNumber, status, password } = parsed.data;

  try {
    let passwordHash = null;
    if (password && password.trim().length >= 12) {
      passwordHash = await bcrypt.hash(password.trim(), 12);
    }

    const request = pool.request()
      .input('npk', sql.VarChar(50), npk.data)
      .input('name', sql.NVarChar(150), fullname)
      .input('email', sql.NVarChar(254), email)
      .input('dept', sql.NVarChar(100), department || null)
      .input('pos', sql.NVarChar(100), position || null)
      .input('phone', sql.VarChar(30), phoneNumber || null)
      .input('status', sql.VarChar(20), status)
      .input('hasNewPassword', sql.Bit, passwordHash !== null)
      .input('newHash', sql.VarChar(255), passwordHash);

    const result = await request.query(`
      UPDATE dbo.employee_accounts
      SET 
        fullname = @name,
        email = @email,
        department = @dept,
        position = @pos,
        phone_number = @phone,
        status = @status,
        password_hash = CASE WHEN @hasNewPassword = 1 THEN @newHash ELSE password_hash END,
        updated_at = SYSUTCDATETIME()
      OUTPUT INSERTED.employee_npk, INSERTED.fullname, INSERTED.email, INSERTED.department, 
             INSERTED.position, INSERTED.phone_number, INSERTED.status
      WHERE employee_npk = @npk AND role = 'reviewer';
    `);

    const updated = result.recordset[0];
    if (!updated) {
      return res.status(404).json({
        error: { code: 'REVIEWER_NOT_FOUND', message: 'Akun reviewer tidak ditemukan.' }
      });
    }

    res.json({
      reviewer: updated,
      message: `Data reviewer ${fullname} berhasil diperbarui.`
    });
  } catch (error) {
    if (error.number === 2627 || error.number === 2601) {
      return res.status(409).json({
        error: { code: 'DUPLICATE_EMAIL', message: 'Alamat email sudah digunakan oleh akun lain.' }
      });
    }
    next(error);
  }
});

module.exports = router;
