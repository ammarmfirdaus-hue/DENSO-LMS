function notFound(_req, res) {
  res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Endpoint tidak ditemukan.' } });
}

function errorHandler(error, _req, res, _next) {
  const status = error.code === 'LIMIT_FILE_SIZE' || error.type === 'entity.too.large' ? 413
    : error instanceof SyntaxError && error.status === 400 ? 400
      : (Number.isInteger(error.status) ? error.status : 500);
  if (status >= 500) console.error(error);
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
