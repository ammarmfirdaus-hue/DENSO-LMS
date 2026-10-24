<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/Config.php';
require_once __DIR__ . '/includes/Security.php';
require_once __DIR__ . '/Core/DX_LMS_M_Request_Middleware.php';
require_once __DIR__ . '/Core/DX_LMS_M_Route_Catalog.php';
require_once __DIR__ . '/includes/DX_LMS_M_Functions.php';
require_once __DIR__ . '/Models/Student/DX_LMS_M_Student_Data.php';
require_once __DIR__ . '/Core/DX_LMS_M_Router.php';
require_once __DIR__ . '/Core/DX_LMS_M_Controller.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
$apiParts = parse_url(SSO_API_URL);
$apiOrigin = isset($apiParts['scheme'], $apiParts['host'])
    ? $apiParts['scheme'] . '://' . $apiParts['host'] . (isset($apiParts['port']) ? ':' . $apiParts['port'] : '')
    : "'self'";
header("Content-Security-Policy: base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; connect-src 'self' {$apiOrigin}; img-src 'self' data: {$apiOrigin}; frame-src 'self' https://youtube.com https://www.youtube.com https://youtube-nocookie.com https://www.youtube-nocookie.com https://youtu.be https://www.youtu.be https://vimeo.com https://www.vimeo.com https://player.vimeo.com");
if (APP_ENV === 'production') header('Strict-Transport-Security: max-age=31536000');
DX_LMS_M_Request_Middleware::handle();

date_default_timezone_set('Asia/Jakarta');
$router = new DX_LMS_M_Router();
$router->add('/', 'DX_LMS_M_Auth@login');
$router->add('/login', 'DX_LMS_M_Auth@login');
$router->add('/auth/process', 'DX_LMS_M_Auth@processLogin');
$router->add('/auth/logout', 'DX_LMS_M_Auth@logout');
$routes = studentRouteCatalog();
foreach ($routes as $path => $action) $router->add($path, $action);

$route = $_GET['route'] ?? '/';
if (!is_string($route) || $route === '' || strlen($route) > 160) {
    http_response_code(404);
    echo '404 — Halaman Tidak Ditemukan';
    exit;
}
if (isset($routes[$route])) requireStudent();
$router->dispatch($route);
