<?php
declare(strict_types=1);
require_once __DIR__ . '/Config.php';

/**
 * Memulai sesi PHP aplikasi dengan konfigurasi pengerasan keamanan (strict ID, HttpOnly, SameSite Lax).
 */
function startAppSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => APP_ENV === 'production' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/'
    ]);
    session_start();
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');
}

/**
 * Mendekode string format Base64Url menjadi string byte biner.
 *
 * @param string $value String format base64url.
 * @return string|null Hasil dekode atau null jika format tidak valid.
 */
function decodeBase64Url(string $value): ?string
{
    $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    return $decoded === false ? null : $decoded;
}

/**
 * Memverifikasi integritas, klaim, dan masa berlaku token SSO JWT (HS256) dari Backend.
 *
 * @param string $token Token JWT yang dikirimkan via cookie bersama.
 * @return array|null Klaim payload terverifikasi atau null jika token ditolak/kedaluwarsa.
 */
function verifySsoToken(string $token): ?array
{
    if (SSO_JWT_SECRET === '' || strlen(SSO_JWT_SECRET) < 32) return null;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
    $headerJson = decodeBase64Url($encodedHeader);
    $payloadJson = decodeBase64Url($encodedPayload);
    $signature = decodeBase64Url($encodedSignature);
    if ($headerJson === null || $payloadJson === null || $signature === null) return null;
    $header = json_decode($headerJson, true);
    $claims = json_decode($payloadJson, true);
    if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256' || !is_array($claims)) return null;
    $expected = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, SSO_JWT_SECRET, true);
    if (!hash_equals($expected, $signature)) return null;
    $audience = $claims['aud'] ?? null;
    $audienceValid = is_string($audience) ? hash_equals(SSO_JWT_AUDIENCE, $audience)
        : (is_array($audience) && in_array(SSO_JWT_AUDIENCE, $audience, true));
    if (!$audienceValid || !isset($claims['iss']) || !hash_equals(SSO_JWT_ISSUER, (string) $claims['iss'])
        || (int) ($claims['exp'] ?? 0) <= time() || (int) ($claims['iat'] ?? 0) > time() + 60
        || empty($claims['sub']) || !in_array(($claims['role'] ?? ''), ['admin', 'teacher', 'reviewer', 'student'], true)
        || (($claims['role'] ?? '') === 'student' && (int) ($claims['student_id'] ?? 0) < 1)) return null;
    return $claims;
}

/**
 * Menerbitkan cookie browser untuk sesi SSO.
 *
 * @param string $token Token JWT yang disematkan ke cookie HttpOnly.
 */
function setSsoBrowserCookie(string $token): void
{
    if (!preg_match('/^[A-Za-z0-9_-]{3,64}$/', SSO_COOKIE_NAME)) return;
    $options = [
        'expires' => time() + 28800,
        'path' => '/',
        'secure' => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (SSO_COOKIE_DOMAIN !== '') $options['domain'] = SSO_COOKIE_DOMAIN;
    setcookie(SSO_COOKIE_NAME, $token, $options);
}

/**
 * Menghapus cookie browser SSO saat pengguna logout.
 */
function clearSsoBrowserCookie(): void
{
    $options = [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (SSO_COOKIE_DOMAIN !== '') $options['domain'] = SSO_COOKIE_DOMAIN;
    setcookie(SSO_COOKIE_NAME, '', $options);
}

/**
 * Menginisialisasi variabel sesi $_SESSION untuk student yang berhasil masuk.
 *
 * @param array $claims Klaim payload terverifikasi dari token JWT.
 */
function createStudentSession(array $claims): void
{
    startAppSession();
    session_regenerate_id(true);
    $_SESSION['student_id'] = (int) $claims['student_id'];
    $_SESSION['student_npk'] = (string) $claims['sub'];
    $_SESSION['student_name'] = (string) ($claims['name'] ?? 'Student');
    $_SESSION['role'] = 'student';
    $_SESSION['user'] = ['npk' => $_SESSION['student_npk'], 'name' => $_SESSION['student_name'], 'role' => 'student'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Guard otorisasi halaman student: memastikan sesi aktif dan token SSO cookie valid.
 * Jika tidak valid, mengalihkan pengguna ke form login.
 */
function requireStudent(): void
{
    startAppSession();
    $claims = verifySsoToken((string) ($_COOKIE[SSO_COOKIE_NAME] ?? ''));
    if (empty($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'student'
        || !$claims || ($claims['role'] ?? '') !== 'student'
        || (int) $claims['student_id'] !== (int) $_SESSION['student_id']) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            jsonResponse(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Silakan masuk kembali.']], 401);
        }
        header('Location: /?route=/login', true, 303);
        exit;
    }
}

/**
 * Menghasilkan atau mengambil token proteksi CSRF untuk form state-changing.
 *
 * @return string Token CSRF heksadesimal 32 byte.
 */
function csrfToken(): string
{
    startAppSession();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Memvalidasi token CSRF yang dikirim via header X-CSRF-Token atau post field _csrf.
 *
 * @return bool True jika token cocok dengan token sesi aktif.
 */
function verifyCsrf(): bool
{
    startAppSession();
    $candidate = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
    return is_string($candidate) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $candidate);
}

/**
 * Mengirimkan respons HTTP JSON standar dengan header keamanan dan mengakhiri script.
 *
 * @param array $payload Data respons yang di-encode ke JSON.
 * @param int $status Kode status HTTP (default: 200).
 * @return never
 */
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
