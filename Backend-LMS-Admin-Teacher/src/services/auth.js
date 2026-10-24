const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticateStudent } = require('./student-auth');

// Keep the password verification cost similar for unknown and known accounts.
const DUMMY_PASSWORD_HASH = bcrypt.hashSync('DLS-invalid-account-password', 12);

async function login(npk, password) {
  const result = await pool.request().input('identifier', sql.NVarChar(254), npk)
    .query(`SELECT TOP 1 employee_npk, fullname, email, password_hash, role, status
            FROM dbo.employee_accounts WHERE (employee_npk = @identifier OR email = @identifier) AND status = 'active'`);
  let user = result.recordset[0];
  if (user) {
    if (!(await bcrypt.compare(password, user.password_hash))) return null;
  } else {
    user = await authenticateStudent(npk, password);
    if (!user) {
      if (env.STUDENT_AUTH_ENABLED !== 'true') await bcrypt.compare(password, DUMMY_PASSWORD_HASH);
      return null;
    }
  }
  const claims = { sub: user.employee_npk, role: user.role, name: user.fullname };
  if (user.role === 'student') claims.student_id = user.student_id;
  const token = jwt.sign(claims, env.JWT_SECRET, {
    algorithm: 'HS256', expiresIn: env.JWT_TTL, issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
  });
  return { token, user: { npk: user.employee_npk, name: user.fullname, email: user.email, role: user.role } };
}

function setSsoCookie(res, token) {
  const decoded = jwt.decode(token);
  const { iat: _iat, exp: _exp, aud: _aud, iss: _iss, ...claims } = decoded || {};
  const sessionToken = jwt.sign(claims, env.JWT_SECRET, {
    algorithm: 'HS256', expiresIn: '8h', issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
  });
  res.cookie(env.SSO_COOKIE_NAME, sessionToken, {
    httpOnly: true,
    secure: env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    ...(env.SSO_COOKIE_DOMAIN ? { domain: env.SSO_COOKIE_DOMAIN } : {}),
    maxAge: 8 * 60 * 60 * 1000
  });
}

function clearSsoCookie(res) {
  res.clearCookie(env.SSO_COOKIE_NAME, {
    httpOnly: true,
    secure: env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    ...(env.SSO_COOKIE_DOMAIN ? { domain: env.SSO_COOKIE_DOMAIN } : {})
  });
}

module.exports = { login, setSsoCookie, clearSsoCookie };
