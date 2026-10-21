<?php
declare(strict_types=1);
require_once __DIR__ . '/Config.php';

function startAppSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/'
    ]);
    session_start();
}

function requireStudent(): void
{
    startAppSession();
    if (empty($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'student') {
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            jsonResponse(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Silakan masuk kembali.']], 401);
        }
        header('Location: /?route=/login', true, 303);
        exit;
    }
}

function csrfToken(): string
{
    startAppSession();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool
{
    startAppSession();
    $candidate = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
    return is_string($candidate) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $candidate);
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}
