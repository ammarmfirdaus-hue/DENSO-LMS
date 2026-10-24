<?php
declare(strict_types=1);

/**
 * Memotong array data berdasarkan query string `page` dan `limit`.
 *
 * @param array $rows Kumpulan data mentah sebelum dipaginasi.
 * @return array Array berisi `data` slice dan metadata `pagination`.
 */
function paginateApiRows(array $rows): array
{
    $pagination = parseApiPagination($_GET);
    if ($pagination === null) return ['data' => $rows, 'pagination' => null];
    if (isset($pagination['error'])) {
        jsonResponse(['error' => ['code' => 'INVALID_PAGINATION', 'message' => $pagination['error']]], 400);
    }

    $page = $pagination['page'];
    $limit = $pagination['limit'];

    $total = count($rows);
    $totalPages = max(1, (int) ceil($total / $limit));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;
    return [
        'data' => array_slice($rows, $offset, $limit),
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $totalPages,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => $total > 0 ? min($offset + $limit, $total) : 0,
            'previousPage' => $page > 1 ? $page - 1 : null,
            'nextPage' => $page < $totalPages ? $page + 1 : null,
        ],
    ];
}

/** Validate untrusted page/limit query values without terminating the request. */
function parseApiPagination(array $query): ?array
{
    $hasPage = array_key_exists('page', $query);
    $hasLimit = array_key_exists('limit', $query);
    if (!$hasPage && !$hasLimit) return null;

    $page = $hasPage ? filter_var($query['page'], FILTER_VALIDATE_INT) : 1;
    $limit = $hasLimit ? filter_var($query['limit'], FILTER_VALIDATE_INT) : 10;
    if ($page === false || $page < 1 || $page > 1000000 || $limit === false || $limit < 1 || $limit > 100) {
        return ['error' => 'Parameter page harus positif dan limit harus antara 1 sampai 100.'];
    }
    return ['page' => $page, 'limit' => $limit];
}
