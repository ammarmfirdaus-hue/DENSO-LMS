<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/includes/StudentApi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    jsonResponse(['error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'API ini hanya menerima GET.']], 405);
}
requireStudent();

try {
    $resource = $_GET['resource'] ?? '';
    if ($resource === 'courses') jsonResponse(['data' => apiCourses((int) $_SESSION['student_id'])]);
    if ($resource === 'dashboard') jsonResponse(['data' => apiDashboard((int) $_SESSION['student_id'])]);
    if ($resource === 'me') jsonResponse(['data' => ['npk' => $_SESSION['student_npk'], 'fullname' => $_SESSION['student_name']]]);
    jsonResponse(['error' => ['code' => 'NOT_FOUND', 'message' => 'Resource tidak tersedia.']], 404);
} catch (Throwable $error) {
    error_log($error->__toString());
    jsonResponse(['error' => ['code' => 'INTERNAL_ERROR', 'message' => 'Data belum dapat dimuat.']], 500);
}
