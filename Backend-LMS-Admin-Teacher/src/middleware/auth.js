const jwt = require('jsonwebtoken');
const env = require('../config/env');
const { isStudentSessionActive } = require('../services/student-auth');
const { hasRolePermission } = require('../services/role-permissions');

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
async function authenticate(req, res, next) {
  const value = req.get('authorization') || '';
  const [scheme, token] = value.split(' ');
  const cookieToken = readCookie(req, env.SSO_COOKIE_NAME);
  const tokens = [scheme === 'Bearer' ? token : '', cookieToken].filter(Boolean);
  if (!tokens.length) return res.status(401).json({ error: { code: 'UNAUTHENTICATED', message: 'Silakan masuk kembali.' } });
  for (const candidate of tokens) {
    try {
      const claims = jwt.verify(candidate, env.JWT_SECRET, {
        algorithms: ['HS256'], issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
      });
      if (claims.role === 'student') {
        if (!Number.isSafeInteger(Number(claims.student_id)) || Number(claims.student_id) < 1
          || ((claims.mode || claims.source_role)
            && (claims.mode !== 'teacher_student' || claims.source_role !== 'teacher'))) {
          return res.status(401).json({ error: { code: 'INVALID_STUDENT_SESSION', message: 'Sesi Student tidak valid. Silakan masuk kembali.' } });
        }
        try {
          const active = await isStudentSessionActive({
            studentId: claims.student_id, npk: claims.sub, mode: claims.mode || '', sourceRole: claims.source_role || ''
          });
          if (!active) return res.status(401).json({ error: { code: 'STUDENT_SESSION_REVOKED', message: 'Akun atau sesi Student sudah tidak aktif. Silakan masuk kembali.' } });
        } catch (error) {
          console.error('Student SSO identity check failed:', error.code || 'STUDENT_DB_UNAVAILABLE');
          return res.status(503).json({ error: { code: 'STUDENT_IDENTITY_UNAVAILABLE', message: 'Identitas Student belum dapat diverifikasi. Coba kembali sebentar lagi.' } });
        }
      }
      req.user = claims;
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

/**
 * Mengecek secara terprogram apakah request saat ini memenuhi minimal salah satu izin RBAC.
 * Token magic-link reviewer portal tetap terkurung pada izin 'materials.review' saja.
 *
 * @param {import('express').Request} req
 * @param {...string} permissions
 * @returns {Promise<boolean>}
 */
async function requestHasPermission(req, ...permissions) {
  const role = req.user?.role;
  if (!role) return false;
  if (req.user?.scope === 'reviewer_portal') {
    return role === 'reviewer' && permissions.includes('materials.review');
  }
  return hasRolePermission(role, permissions);
}

/**
 * Middleware otorisasi dinamis berbasis izin (RBAC) dari konfigurasi role di database.
 * Lolos apabila peran pengguna aktif memiliki minimal satu dari daftar izin yang diminta.
 *
 * @param {...string} permissions Daftar kunci izin yang diperbolehkan.
 * @returns {import('express').RequestHandler}
 */
function requirePermission(...permissions) {
  return async (req, res, next) => {
    try {
      const allowed = await requestHasPermission(req, ...permissions);
      if (!allowed) {
        return res.status(403).json({
          error: {
            code: 'FORBIDDEN',
            message: 'Peran akun Anda tidak memiliki hak akses untuk melakukan tindakan ini.'
          }
        });
      }
      return next();
    } catch (error) {
      return next(error);
    }
  };
}

module.exports = { authenticate, allowRoles, requirePermission, requestHasPermission };
