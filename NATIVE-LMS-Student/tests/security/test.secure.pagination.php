<?php
declare(strict_types=1);

securityTest('pagination rejects injection, arrays, and oversized integer values', static function (): void {
    foreach ([
        ['page' => '1 OR 1=1'],
        ['page' => '-1'],
        ['page' => '1000001'],
        ['limit' => '101'],
        ['page' => ['1', '2']],
        ['limit' => '999999999999999999999999'],
    ] as $query) {
        $parsed = parseApiPagination($query);
        assertTrueValue(isset($parsed['error']), 'query must be rejected: ' . json_encode($query));
    }
    assertNullValue(parseApiPagination([]));
});

securityTest('pagination safely clamps a very high valid page and caps result size', static function (): void {
    $parsed = parseApiPagination(['page' => '1000000', 'limit' => '100']);
    assertSameValue(['page' => 1000000, 'limit' => 100], $parsed);
    $_GET = ['page' => '1000000', 'limit' => '100'];
    $result = paginateApiRows(range(1, 250));
    assertSameValue(range(201, 250), $result['data']);
    assertSameValue(3, $result['pagination']['totalPages']);
    assertSameValue(3, $result['pagination']['page']);
});
