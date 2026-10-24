<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/Core/DX_LMS_M_Request_Middleware.php';
require_once dirname(__DIR__) . '/includes/StudentApi.php';
require_once dirname(__DIR__) . '/includes/Pagination.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    jsonResponse(['error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'API ini hanya menerima GET.']], 405);
}
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
if (APP_ENV === 'production') header('Strict-Transport-Security: max-age=31536000');
DX_LMS_M_Request_Middleware::handle();
requireStudent();

try {
    $resource = $_GET['resource'] ?? '';
    if ($resource === 'courses') {
        $page = paginateApiRows(apiCourses((int) $_SESSION['student_id']));
        $payload = ['data' => $page['data']];
        if ($page['pagination'] !== null) $payload['pagination'] = $page['pagination'];
        jsonResponse($payload);
    }
    if ($resource === 'dashboard') jsonResponse(['data' => apiDashboard((int) $_SESSION['student_id'])]);
    if ($resource === 'me') jsonResponse(['data' => ['npk' => $_SESSION['student_npk'], 'fullname' => $_SESSION['student_name']]]);
    jsonResponse(['error' => ['code' => 'NOT_FOUND', 'message' => 'Resource tidak tersedia.']], 404);
} catch (Throwable $error) {
    error_log($error->__toString());
    jsonResponse(['error' => ['code' => 'INTERNAL_ERROR', 'message' => 'Data belum dapat dimuat.']], 500);
}
