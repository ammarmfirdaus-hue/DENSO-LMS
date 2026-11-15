const express = require('express');
const bcrypt = require('bcryptjs');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, requirePermission } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');
const { reviewerSchema, reviewerUpdateSchema } = require('../services/reviewer-rules');
const { sendReviewInvitation } = require('../services/review-mail');
const { transferReviewerTasks } = require('../services/review-transfer');
const env = require('../config/env');
const crypto = require('crypto');

const router = express.Router();
router.use(authenticate);

/**
 * GET /api/v1/reviewers
 * Mengambil seluruh daftar reviewer beserta statistik review aktif.
 */
router.get('/', requirePermission('reviewers.view'), async (req, res, next) => {
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
router.post('/', requirePermission('reviewers.manage'), async (req, res, next) => {
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
router.put('/:npk', requirePermission('reviewers.manage'), async (req, res, next) => {
  const npk = z.string().trim().min(1).max(50).safeParse(req.params.npk);
  if (!npk.success) {
    return res.status(400).json({ error: { code: 'INVALID_NPK', message: 'NPK tidak valid.' } });
  }

  const parsed = reviewerUpdateSchema.safeParse(req.body);
  if (!parsed.success) {
    const message = parsed.error.issues?.[0]?.message || 'Data perubahan tidak valid.';
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message } });
  }

  const { fullname, email, department, position, phoneNumber, status, password, targetReviewerNpk } = parsed.data;

  try {
    if (status === 'inactive') {
      const pending = await pool.request().input('npk', sql.VarChar(50), npk.data)
        .query(`SELECT TOP 1 1 AS exists_pending FROM dbo.material_submissions WHERE reviewer_npk=@npk AND status='pending_review'`);
      if (pending.recordset.length) {
        if (!targetReviewerNpk) {
          return res.status(409).json({
            error: {
              code: 'REVIEWER_HAS_PENDING_WORK',
              message: 'Reviewer masih memiliki materi yang menunggu review. Selesaikan atau alihkan tugas tersebut terlebih dahulu.'
            }
          });
        }
        await transferReviewerTasks({
          sourceNpk: npk.data,
          targetReviewerNpk,
          pool
        });
      }
    }

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

/**
 * POST /api/v1/reviewers/:npk/transfer-review-tasks
 * Memindahkan seluruh antrean review aktif ke reviewer aktif lain. Tautan lama
 * dicabut agar reviewer asal tidak dapat lagi membuka portal review.
 */
router.post('/:npk/transfer-review-tasks', requirePermission('reviewers.manage'), async (req, res, next) => {
  const sourceNpk = z.string().trim().min(1).max(50).safeParse(req.params.npk);
  const body = z.object({ targetReviewerNpk: z.string().trim().min(1).max(50) }).safeParse(req.body);
  if (!sourceNpk.success || !body.success) {
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Pilih reviewer tujuan yang valid.' } });
  }

  try {
    const result = await transferReviewerTasks({
      sourceNpk: sourceNpk.data,
      targetReviewerNpk: body.data.targetReviewerNpk,
      pool
    });
    res.json(result);
  } catch (error) {
    if (error.code && error.status) {
      return res.status(error.status).json({ error: { code: error.code, message: error.message } });
    }
    next(error);
  }
});

// Keep the reviewer record for review history; removing access is implemented as deactivation.
router.delete('/:npk', requirePermission('reviewers.manage'), async (req, res, next) => {
  const npk = z.string().trim().min(1).max(50).safeParse(req.params.npk);
  if (!npk.success) return res.status(400).json({ error: { code: 'INVALID_NPK', message: 'NPK tidak valid.' } });
  const targetReviewerNpk = (req.body?.targetReviewerNpk || req.query?.targetReviewerNpk || '').toString().trim();

  try {
    const pending = await pool.request().input('npk', sql.VarChar(50), npk.data)
      .query(`SELECT TOP 1 1 AS exists_pending FROM dbo.material_submissions WHERE reviewer_npk=@npk AND status='pending_review'`);

    if (pending.recordset.length) {
      if (!targetReviewerNpk) {
        return res.status(409).json({
          error: {
            code: 'REVIEWER_HAS_PENDING_WORK',
            message: 'Reviewer masih memiliki materi yang menunggu review. Selesaikan atau alihkan tugas tersebut terlebih dahulu.'
          }
        });
      }
      await transferReviewerTasks({
        sourceNpk: npk.data,
        targetReviewerNpk,
        pool
      });
    }

    const result = await pool.request().input('npk', sql.VarChar(50), npk.data)
      .query(`UPDATE dbo.employee_accounts SET status='inactive',updated_at=SYSUTCDATETIME()
        OUTPUT INSERTED.employee_npk WHERE employee_npk=@npk AND role='reviewer'`);

    if (!result.recordset.length) {
      return res.status(404).json({ error: { code: 'REVIEWER_NOT_FOUND', message: 'Akun reviewer tidak ditemukan.' } });
    }

    res.json({
      deleted: true,
      deactivated: true,
      transferred: Boolean(targetReviewerNpk),
      message: 'Akun reviewer dinonaktifkan; riwayat review tetap tersimpan.'
    });
  } catch (error) {
    if (error.code && error.status) {
      return res.status(error.status).json({ error: { code: error.code, message: error.message } });
    }
    next(error);
  }
});

module.exports = router;
