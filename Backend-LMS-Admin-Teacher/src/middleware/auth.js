const jwt = require('jsonwebtoken');
const env = require('../config/env');

function authenticate(req, res, next) {
  const value = req.get('authorization') || '';
  const [scheme, token] = value.split(' ');
  if (scheme !== 'Bearer' || !token) return res.status(401).json({ error: { code: 'UNAUTHENTICATED', message: 'Silakan masuk kembali.' } });
  try {
    req.user = jwt.verify(token, env.JWT_SECRET, {
      algorithms: ['HS256'], issuer: env.JWT_ISSUER, audience: env.JWT_AUDIENCE
    });
    return next();
  } catch {
    return res.status(401).json({ error: { code: 'INVALID_TOKEN', message: 'Sesi tidak valid atau telah berakhir.' } });
  }
}

function allowRoles(...roles) {
  return (req, res, next) => roles.includes(req.user?.role)
    ? next()
    : res.status(403).json({ error: { code: 'FORBIDDEN', message: 'Anda tidak memiliki akses.' } });
}

module.exports = { authenticate, allowRoles };
