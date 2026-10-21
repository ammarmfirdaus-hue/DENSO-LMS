const express = require('express');
const bcrypt = require('bcryptjs');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');

const router = express.Router();
router.use(authenticate, allowRoles('admin'));
router.get('/', async (_req, res, next) => {
  try {
    const result = await pool.request().query(`SELECT employee_npk,fullname,email,role,status,created_at FROM dbo.employee_accounts ORDER BY fullname`);
    res.json({ users: result.recordset });
  } catch (error) { next(error); }
});

router.post('/', async (req, res, next) => {
  const parsed = z.object({ npk: z.string().trim().min(1).max(50), fullname: z.string().trim().min(2).max(150),
    email: z.string().trim().email().max(254), password: z.string().min(12).max(200), role: z.enum(['admin','teacher']) }).safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Data akun tidak valid; kata sandi minimal 12 karakter.' } });
  try {
    const data = parsed.data;
    const hash = await bcrypt.hash(data.password, 12);
    await pool.request().input('npk', sql.VarChar(50), data.npk).input('name', sql.NVarChar(150), data.fullname)
      .input('email', sql.NVarChar(254), data.email).input('hash', sql.VarChar(255), hash).input('role', sql.VarChar(20), data.role)
      .query(`INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role) VALUES(@npk,@name,@email,@hash,@role)`);
    res.status(201).json({ user: { npk: data.npk, fullname: data.fullname, email: data.email, role: data.role } });
  } catch (error) { if (error.number === 2627 || error.number === 2601) error.status = 409; next(error); }
});

module.exports = router;
