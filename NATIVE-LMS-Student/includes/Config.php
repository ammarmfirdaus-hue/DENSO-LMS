<?php
declare(strict_types=1);

function envValue(string $key, ?string $default = null): ?string
{
    static $loaded = false;
    if (!$loaded) {
        $envFile = dirname(__DIR__) . '/.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim(trim($value), "\"'");
                if (getenv($name) === false) putenv($name . '=' . $value);
            }
        }
        $loaded = true;
    }
    $value = getenv($key);
    return $value === false ? $default : $value;
}

if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', envValue('APP_ENV', 'development'));
define('APP_URL', envValue('APP_URL', 'http://127.0.0.1:8082'));
define('DB_HOST', envValue('DB_HOST', '127.0.0.1'));
define('DB_PORT', envValue('DB_PORT', '3306'));
define('DB_NAME', envValue('DB_NAME', 'dls_student'));
define('DB_USER', envValue('DB_USER', 'dls_student_app'));
define('DB_PASSWORD', envValue('DB_PASSWORD', ''));
define('SESSION_NAME', envValue('SESSION_NAME', 'DLS_STUDENT'));
define('SSO_API_URL', rtrim((string) envValue('SSO_API_URL', 'http://127.0.0.1:3001/api/v1'), '/'));
define('SSO_JWT_SECRET', (string) envValue('SSO_JWT_SECRET', ''));
define('SSO_JWT_ISSUER', (string) envValue('SSO_JWT_ISSUER', 'dls-admin-teacher-api'));
define('SSO_JWT_AUDIENCE', (string) envValue('SSO_JWT_AUDIENCE', 'dls-admin-teacher-web'));
define('SSO_COOKIE_NAME', (string) envValue('SSO_COOKIE_NAME', 'dls_sso'));
define('SSO_COOKIE_DOMAIN', (string) envValue('SSO_COOKIE_DOMAIN', ''));
define('SSO_FRONTEND_URL', rtrim((string) envValue('SSO_FRONTEND_URL', 'http://127.0.0.1:5173'), '/'));

if (APP_ENV === 'production') {
    if (strlen(DB_PASSWORD) < 20) {
        error_log('Production configuration rejected: DB_PASSWORD must contain at least 20 characters.');
        http_response_code(500);
        exit('Konfigurasi server belum aman.');
    }
    if (parse_url(APP_URL, PHP_URL_SCHEME) !== 'https') {
        error_log('Production configuration rejected: APP_URL must use HTTPS.');
        http_response_code(500);
        exit('Konfigurasi server belum aman.');
    }
    if (strlen(SSO_JWT_SECRET) < 32 || parse_url(SSO_API_URL, PHP_URL_SCHEME) !== 'https' || parse_url(SSO_FRONTEND_URL, PHP_URL_SCHEME) !== 'https') {
        error_log('Production configuration rejected: SSO secret and HTTPS URLs are required.');
        http_response_code(500);
        exit('Konfigurasi SSO belum aman.');
    }
    if (SSO_COOKIE_DOMAIN !== '' && !preg_match('/^\.?[A-Za-z0-9.-]+$/', SSO_COOKIE_DOMAIN)) {
        error_log('Production configuration rejected: SSO_COOKIE_DOMAIN must be a valid parent hostname.');
        http_response_code(500);
        exit('Konfigurasi SSO belum aman.');
    }
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
