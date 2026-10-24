const jwt = require('jsonwebtoken');
const env = require('../config/env');

/**
 * Membaca nilai cookie tertentu dari header request HTTP.
 *
 * @param {import('express').Request} req Objek request Express.
 * @param {string} name Nama cookie yang dicari.
 * @returns {string} Nilai cookie yang telah didecode, atau string kosong jika tidak ada.
 */
function readCookie(req, name) {
  const cookieHeader = req.get('cookie') || '';
  for (const part of cookieHeader.split(';')) {
    const separator = part.indexOf('=');
    if (separator < 0 || part.slice(0, separator).trim() !== name) continue;
    try { return decodeURIComponent(part.slice(separator + 1).trim()); } catch { return ''; }
  }
  return '';
}

/**
 * Middleware autentikasi JWT terpusat (Bearer header dan cookie SSO HttpOnly).
 * Memvalidasi signature HS256, issuer, audience, dan menyematkan payload ke `req.user`.
 *
 * @param {import('express').Request} req Objek request Express.
 * @param {import('express').Response} res Objek response Express.
 * @param {import('express').NextFunction} next Fungsi middleware berikutnya.
 */
function authenticate(req, res, next) {
  const value = req.get('authorization') || '';
  const [scheme, token] = value.split(' ');
  const cookieToken = readCookie(req, env.SSO_COOKIE_NAME);
  const tokens = [scheme === 'Bearer' ? token : '', cookieToken].filter(Boolean);
  if (!tokens.length) return res.status(401).json({ error: { code: 'UNAUTHENTICATED', message: 'Silakan masuk kembali.' } });
  for (const candidate of tokens) {
    try {
      req.user = jwt.verify(candidate, env.JWT_SECRET, {
        algorithms: ['HS256'], issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
      });
      return next();
    } catch { /* Fall back to the SSO cookie after the short bearer token expires. */ }
  }
  return res.status(401).json({ error: { code: 'INVALID_TOKEN', message: 'Sesi tidak valid atau telah berakhir.' } });
}

/**
 * Middleware otorisasi berbasis Role-Based Access Control (RBAC).
 * Memastikan role pengguna dalam `req.user` termasuk dalam daftar role yang diizinkan.
 *
 * @param {...string} roles Daftar role yang diperbolehkan mengakses route (mis. 'admin', 'teacher').
 * @returns {import('express').RequestHandler} Fungsi middleware filter role.
 */
function allowRoles(...roles) {
  return (req, res, next) => roles.includes(req.user?.role)
    ? next()
    : res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Anda tidak memiliki akses.' } });
}

module.exports = { authenticate, allowRoles };
