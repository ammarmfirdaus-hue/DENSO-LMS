const rateLimit = require('express-rate-limit');

function buildLimiter({ windowMs, limit, message, keyGenerator }) {
  return rateLimit({
    windowMs,
    limit,
    standardHeaders: 'draft-8',
    legacyHeaders: false,
    ...(keyGenerator ? { keyGenerator } : {}),
    handler: (_req, res, _next, options) => res.status(options.statusCode).json({
      error: { code: 'RATE_LIMITED', message },
      retryAfter: Math.max(1, Math.ceil(options.windowMs / 1000))
    })
  });
}

const apiLimiter = buildLimiter({
  windowMs: 60 * 1000,
  limit: 300,
  message: 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.'
});

const writeLimiter = buildLimiter({
  windowMs: 15 * 60 * 1000,
  limit: 90,
  message: 'Batas perubahan data tercapai. Tunggu sebelum mencoba kembali.'
});

const uploadLimiter = buildLimiter({
  windowMs: 15 * 60 * 1000,
  limit: 20,
  message: 'Batas upload tercapai. Coba kembali beberapa saat lagi.'
});

module.exports = { buildLimiter, apiLimiter, writeLimiter, uploadLimiter };
