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

define('APP_ENV', envValue('APP_ENV', 'development'));
define('APP_URL', envValue('APP_URL', 'http://127.0.0.1:8082'));
define('DB_HOST', envValue('DB_HOST', '127.0.0.1'));
define('DB_PORT', envValue('DB_PORT', '3306'));
define('DB_NAME', envValue('DB_NAME', 'dls_student'));
define('DB_USER', envValue('DB_USER', 'dls_student_app'));
define('DB_PASSWORD', envValue('DB_PASSWORD', ''));
define('SESSION_NAME', envValue('SESSION_NAME', 'DLS_STUDENT'));

if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
