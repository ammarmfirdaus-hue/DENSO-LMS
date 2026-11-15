<?php
declare(strict_types=1);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($path === '/__dev_reload_stamp') {
    $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $hostName = strtolower((string) parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
    $isLocalDev = getenv('APP_ENV') !== 'production'
        && in_array($remoteAddr, ['127.0.0.1', '::1'], true)
        && in_array($hostName, ['127.0.0.1', 'localhost'], true);
    if (!$isLocalDev) {
        http_response_code(404);
        return true;
    }
    $root = dirname(__DIR__);
    $watchDirs = ['Views', 'Controllers', 'Models', 'Core', 'includes', 'public/assets/css', 'public/assets/js'];
    $maxMtime = 0;
    foreach ($watchDirs as $dir) {
        $fullDir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
        if (!is_dir($fullDir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullDir, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile()) {
                $mtime = $fileInfo->getMTime();
                if ($mtime > $maxMtime) {
                    $maxMtime = $mtime;
                }
            }
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode(['version' => $maxMtime]);
    return true;
}
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
    if ($decodedPath === '/manifest.webmanifest') {
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: no-cache');
    } elseif ($decodedPath === '/sw.js') {
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: no-cache');
        header('Service-Worker-Allowed: /');
    }
    return false;
}
require dirname(__DIR__) . '/index.php';
