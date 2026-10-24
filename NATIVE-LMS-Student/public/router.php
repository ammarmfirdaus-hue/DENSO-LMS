<?php
declare(strict_types=1);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($path, '/api/')) {
    require dirname(__DIR__) . '/api/index.php';
    return true;
}
$publicRoot = realpath(__DIR__);
$decodedPath = rawurldecode($path);
$file = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($decodedPath, '/\\'));
if ($path !== '/' && $publicRoot !== false && $file !== false
    && str_starts_with($file, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($file)
    && !preg_match('~(^|[\\/])\.[^\\/]+~', $decodedPath)) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    return false;
}
require dirname(__DIR__) . '/index.php';
