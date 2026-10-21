const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { sql, pool } = require('../config/database');
const env = require('../config/env');

async function login(npk, password) {
  const result = await pool.request().input('npk', sql.VarChar(50), npk)
    .query(`SELECT TOP 1 employee_npk, fullname, email, password_hash, role, status
            FROM dbo.employee_accounts WHERE employee_npk = @npk AND status = 'active'`);
  const user = result.recordset[0];
  if (!user || !(await bcrypt.compare(password, user.password_hash))) return null;
  const token = jwt.sign({ sub: user.employee_npk, role: user.role, name: user.fullname }, env.JWT_SECRET, {
    algorithm: 'HS256', expiresIn: env.JWT_TTL, issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
  });
  return { token, user: { npk: user.employee_npk, name: user.fullname, email: user.email, role: user.role } };
}

module.exports = { login };
