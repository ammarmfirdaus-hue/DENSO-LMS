<?php
declare(strict_types=1);

/**
 * Mengambil nilai konfigurasi dari environment file (.env).
 *
 * @param string $key Kunci konfigurasi yang dicari.
 * @param string|null $default Nilai bawaan jika kunci tidak ditemukan.
 * @return string|null
 */
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

/**
 * Read the API URL selected by an Admin from the backend's public bootstrap
 * endpoint. SSO_API_URL in the Student .env is only used to discover that
 * setting (and as a fallback while the backend is unreachable).
 */
function resolveConfiguredApiUrl(string $bootstrapUrl): string
{
    $normalize = static function (string $value): ?string {
        $value = trim($value);
        $parts = parse_url($value);
        if (!is_array($parts)
            || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }
        $path = rtrim((string) ($parts['path'] ?? ''), '/');
        $base = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) $base .= ':' . $parts['port'];
        $base .= $path;
        return str_ends_with($path, '/api/v1') ? $base : $base . '/api/v1';
    };

    $fallback = $normalize($bootstrapUrl) ?? 'http://127.0.0.1:3001/api/v1';
    $cacheFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
        . 'dls_student_api_' . hash('sha256', APP_ROOT) . '.json';
    $cached = null;
    if (is_file($cacheFile)) {
        $cacheData = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($cacheData) && isset($cacheData['url'], $cacheData['savedAt'])) {
            $cacheAge = time() - (int) $cacheData['savedAt'];
            $cached = $normalize((string) $cacheData['url']);
            if ($cached !== null && $cacheAge >= 0 && $cacheAge < 60) return $cached;
            if ($cacheAge < 0 || $cacheAge >= 86400) $cached = null;
        }
    }

    $bootstrap = rtrim($fallback, '/');
    $bootstrap = preg_replace('~/api/v1$~', '', $bootstrap) ?: $bootstrap;
    $context = stream_context_create(['http' => [
        'method' => 'GET',
        'timeout' => 2.0,
        'ignore_errors' => true,
        'header' => "Accept: application/json\r\n",
    ], 'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ]]);
    $response = @file_get_contents($bootstrap . '/api/v1/settings/runtime-config', false, $context);
    if (is_string($response)) {
        $config = json_decode($response, true);
        $configured = is_array($config) && is_string($config['apiBaseUrl'] ?? null)
            ? $normalize($config['apiBaseUrl']) : null;
        if ($configured !== null) {
            $cacheData = json_encode(['url' => $configured, 'savedAt' => time()], JSON_UNESCAPED_SLASHES);
            if ($cacheData !== false && @file_put_contents($cacheFile, $cacheData, LOCK_EX) !== false) {
                @chmod($cacheFile, 0600);
            }
            return $configured;
        }
    }

    return $cached ?? $fallback;
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
define('SSO_API_URL', resolveConfiguredApiUrl(rtrim((string) envValue('SSO_API_URL', 'http://127.0.0.1:3001/api/v1'), '/')));
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
