const MAX_PAGE_SIZE = 100;
const MAX_PAGE = 1_000_000;

function parsePositiveInteger(value, name, max) {
  if (typeof value !== 'string' || !/^\d+$/.test(value)) return { error: `${name} harus berupa bilangan bulat positif.` };
  const number = Number(value);
  if (!Number.isSafeInteger(number) || number < 1 || number > max) return { error: `${name} berada di luar batas yang diizinkan.` };
  return { value: number };
}

function paginationMiddleware(req, res, next) {
  const hasPage = Object.hasOwn(req.query, 'page');
  const hasLimit = Object.hasOwn(req.query, 'limit');
  if (!hasPage && !hasLimit) {
    req.pagination = { enabled: false, page: 1, limit: MAX_PAGE_SIZE };
    return next();
  }

  const page = hasPage ? parsePositiveInteger(req.query.page, 'page', MAX_PAGE) : { value: 1 };
  const limit = hasLimit ? parsePositiveInteger(req.query.limit, 'limit', MAX_PAGE_SIZE) : { value: 10 };
  const error = page.error || limit.error;
  if (error) return res.status(400).json({ error: { code: 'INVALID_PAGINATION', message: error } });
  req.pagination = { enabled: true, page: page.value, limit: limit.value };
  return next();
}

function paginateCollection(req, rows) {
  if (!req.pagination?.enabled) return { data: rows, pagination: null };
  const total = rows.length;
  const totalPages = Math.max(1, Math.ceil(total / req.pagination.limit));
  const page = Math.min(req.pagination.page, totalPages);
  const startIndex = (page - 1) * req.pagination.limit;
  return {
    data: rows.slice(startIndex, startIndex + req.pagination.limit),
    pagination: {
      page,
      limit: req.pagination.limit,
      total,
      totalPages,
      from: total ? startIndex + 1 : 0,
      to: total ? Math.min(startIndex + req.pagination.limit, total) : 0,
      previousPage: page > 1 ? page - 1 : null,
      nextPage: page < totalPages ? page + 1 : null
    }
  };
}

module.exports = { paginationMiddleware, paginateCollection, MAX_PAGE_SIZE };
