function notFound(_req, res) {
  res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Endpoint tidak ditemukan.' } });
}

function errorHandler(error, _req, res, _next) {
  const status = error.code === 'LIMIT_FILE_SIZE' ? 413 : (Number.isInteger(error.status) ? error.status : 500);
  if (status >= 500) console.error(error);
  res.status(status).json({
    error: { code: status >= 500 ? 'INTERNAL_ERROR' : 'REQUEST_ERROR', message: status === 413 ? 'Ukuran file melebihi batas upload.' : (status >= 500 ? 'Terjadi kesalahan pada server.' : error.message) }
  });
}

module.exports = { notFound, errorHandler };
