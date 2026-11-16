const { logAppEvent, inferActionAndResource } = require('../services/app-logger');

function notFound(_req, res) {
  res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Endpoint tidak ditemukan.' } });
}

function errorHandler(error, req, res, _next) {
  const status = error.code === 'LIMIT_FILE_SIZE' || error.type === 'entity.too.large' ? 413
    : error instanceof SyntaxError && error.status === 400 ? 400
      : (Number.isInteger(error.status) ? error.status : 500);
  const { action, resource, cleanPath } = inferActionAndResource(req?.method || 'GET', req?.originalUrl || '/');
  if (status >= 500) {
    logAppEvent({
      level: 'ERROR',
      action,
      resource,
      message: `${req?.method || 'REQ'} ${cleanPath} failed: ${error.message || 'Internal error'}`,
      requestId: req?.requestId,
      user: req?.user?.npk || req?.user?.id,
      error
    });
  } else if (status === 400 || status === 413 || status === 422) {
    logAppEvent({
      level: 'WARNING',
      action: 'VALIDATION',
      resource,
      message: `${req?.method || 'REQ'} ${cleanPath} (${status}): ${error.message || 'Validation error'}`,
      requestId: req?.requestId,
      user: req?.user?.npk || req?.user?.id
    });
  }
  const message = error.code === 'LIMIT_FILE_SIZE'
    ? 'Ukuran file melebihi batas upload.'
    : status === 413
      ? 'Ukuran request melebihi batas.'
      : status === 400
        ? 'Format JSON/request tidak valid.'
        : (status >= 500 ? 'Terjadi kesalahan pada server.' : error.message);
  res.status(status).json({
    error: { code: status >= 500 ? 'INTERNAL_ERROR' : 'REQUEST_ERROR', message }
  });
}

module.exports = { notFound, errorHandler };
