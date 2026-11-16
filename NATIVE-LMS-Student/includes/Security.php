<?php
declare(strict_types=1);
require_once __DIR__ . '/Config.php';

/**
 * Membuat referensi publik opaque untuk parameter URL. Nilai asli (misalnya
 * course_code) tidak pernah ditampilkan di URL baru; token ini hanya dapat
 * dibuka oleh server yang memiliki SSO_JWT_SECRET dan dilindungi AES-GCM.
 */
function publicRouteId(string $type, string $value): string
{
    $key = hash('sha256', SSO_JWT_SECRET . '|dls-student-public-route', true);
    $iv = random_bytes(12);
    $cipher = openssl_encrypt($type . ':' . $value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($cipher === false) throw new RuntimeException('Token URL tidak dapat dibuat.');
    return rtrim(strtr(base64_encode($iv), '+/', '-_'), '=') . '.'
        . rtrim(strtr(base64_encode($cipher), '+/', '-_'), '=') . '.'
        . rtrim(strtr(base64_encode($tag), '+/', '-_'), '=');
}

/** Mengurai token URL publik dan menolak token rusak/tampered tanpa fallback SQL. */
function resolvePublicRouteId(string $type, mixed $token): ?string
{
    if (!is_string($token) || strlen($token) < 20 || strlen($token) > 240
        || !preg_match('/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/D', $token)) return null;
    [$encodedIv, $encodedCipher, $encodedTag] = explode('.', $token, 3);
    $decode = static function (string $part): string|false {
        return base64_decode(strtr($part, '-_', '+/') . str_repeat('=', (4 - strlen($part) % 4) % 4), true);
    };
    $iv = $decode($encodedIv); $cipher = $decode($encodedCipher); $tag = $decode($encodedTag);
    if ($iv === false || $cipher === false || $tag === false || strlen($iv) !== 12 || strlen($tag) !== 16) return null;
    $key = hash('sha256', SSO_JWT_SECRET . '|dls-student-public-route', true);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '');
    $prefix = $type . ':';
    if (!is_string($plain) || !str_starts_with($plain, $prefix)) return null;
    $value = substr($plain, strlen($prefix));
    return $value !== '' && strlen($value) <= 120 ? $value : null;
}

function publicCourseId(mixed $courseId): string
{
    return publicRouteId('course', (string) $courseId);
}

function resolveCourseId(mixed $token): ?string
{
    $resolved = resolvePublicRouteId('course', $token);
    if ($resolved !== null) return $resolved;
    if (is_string($token) && preg_match('/^[A-Za-z0-9_-]{1,80}$/D', $token)) return $token;
    return null;
}

function publicSessionId(mixed $sessionId): string
{
    return publicRouteId('session', (string) $sessionId);
}

function resolveSessionId(mixed $token): ?string
{
    $resolved = resolvePublicRouteId('session', $token);
    if ($resolved !== null) return $resolved;
    if (is_string($token) && preg_match('/^[A-Za-z0-9_-]{1,80}$/D', $token)) return $token;
    return null;
}

function publicThreadId(mixed $threadId): string
{
    return publicRouteId('thread', (string) $threadId);
}

function resolveThreadId(mixed $token): ?string
{
    $resolved = resolvePublicRouteId('thread', $token);
    if ($resolved !== null) return $resolved;
    if (is_string($token) && ctype_digit($token) && (int) $token > 0) return $token;
    return null;
}

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
    if (isset($claims['mode']) || isset($claims['source_role'])) {
        if (($claims['mode'] ?? '') !== 'teacher_student' || ($claims['source_role'] ?? '') !== 'teacher'
            || ($claims['role'] ?? '') !== 'student') return null;
    }
    return $claims;
}

/** Pastikan role pada cookie masih sesuai dengan tipe/status identitas Student di MySQL. */
function ssoStudentAccountState(array $claims): ?array
{
    if (($claims['role'] ?? '') !== 'student' || (int) ($claims['student_id'] ?? 0) < 1 || empty($claims['sub'])) return null;
    require_once __DIR__ . '/Database.php';
    try {
        $query = db()->prepare('SELECT student_id,npk,account_type,status FROM student_accounts WHERE student_id=:id AND npk=:npk LIMIT 1');
        $query->execute(['id' => (int) $claims['student_id'], 'npk' => (string) $claims['sub']]);
        $row = $query->fetch();
        if (!$row || $row['status'] !== 'active') return null;
        $expectedType = ($claims['mode'] ?? '') === 'teacher_student' ? 'teacher_shadow' : 'student';
        if (($row['account_type'] ?? 'student') !== $expectedType) return null;
        return $row;
    } catch (Throwable $error) {
        error_log('SSO Student account check failed: ' . $error->getMessage());
        return null;
    }
}

function isSsoStudentIdentityActive(array $claims): bool
{
    return ssoStudentAccountState($claims) !== null;
}

function studentSsoCookieName(): string
{
    return SSO_COOKIE_NAME . '_student';
}

function readStudentSsoToken(): string
{
    $studentCookie = (string) ($_COOKIE[studentSsoCookieName()] ?? '');
    if ($studentCookie !== '') {
        return $studentCookie;
    }
    $sharedCookie = (string) ($_COOKIE[SSO_COOKIE_NAME] ?? '');
    if ($sharedCookie !== '') {
        $claims = verifySsoToken($sharedCookie);
        if ($claims && ($claims['role'] ?? '') === 'student') {
            return $sharedCookie;
        }
    }
    return '';
}

/**
 * Menerbitkan cookie browser untuk sesi SSO Student (terpisah dari sesi Admin/Teacher).
 *
 * @param string $token Token JWT yang disematkan ke cookie HttpOnly.
 */
function setSsoBrowserCookie(string $token): void
{
    $cookieName = studentSsoCookieName();
    if (!preg_match('/^[A-Za-z0-9_-]{3,64}$/', $cookieName)) return;
    $claims = verifySsoToken($token);
    $maxAge = ($claims['mode'] ?? '') === 'teacher_student' ? 1800 : 28800;
    $options = [
        'expires' => time() + $maxAge,
        'path' => '/',
        'secure' => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (SSO_COOKIE_DOMAIN !== '') $options['domain'] = SSO_COOKIE_DOMAIN;
    setcookie($cookieName, $token, $options);
}

/**
 * Menghapus cookie browser SSO Student saat pengguna logout.
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
    setcookie(studentSsoCookieName(), '', $options);

    $sharedCookie = (string) ($_COOKIE[SSO_COOKIE_NAME] ?? '');
    if ($sharedCookie !== '') {
        $claims = verifySsoToken($sharedCookie);
        if (!$claims || ($claims['role'] ?? '') === 'student') {
            setcookie(SSO_COOKIE_NAME, '', $options);
        }
    }
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
    $_SESSION['sso_mode'] = (string) ($claims['mode'] ?? '');
    $_SESSION['user'] = ['npk' => $_SESSION['student_npk'], 'name' => $_SESSION['student_name'], 'role' => 'student', 'mode' => $_SESSION['sso_mode']];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['_last_activity'] = time();
    $_SESSION['_session_created'] = time();
    $_SESSION['_fingerprint'] = hash_hmac('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'none'), SSO_JWT_SECRET);
}

/**
 * Guard otorisasi halaman student: memastikan sesi aktif dan token SSO cookie valid.
 * Jika tidak valid, mengalihkan pengguna ke form login.
 */
function requireStudent(): void
{
    startAppSession();
    $now = time();
    $inactivityLimit = 1800; // 30 minutes
    $isTimedOut = isset($_SESSION['_last_activity']) && ($now - (int) $_SESSION['_last_activity']) > $inactivityLimit;
    $isFingerprintTampered = isset($_SESSION['_fingerprint']) && !hash_equals((string) $_SESSION['_fingerprint'], hash_hmac('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'none'), SSO_JWT_SECRET));

    $claims = verifySsoToken(readStudentSsoToken());
    $identity = $claims && ($claims['role'] ?? '') === 'student' ? ssoStudentAccountState($claims) : null;
    $promotedToTeacher = $identity === null && $claims && ($claims['role'] ?? '') === 'student'
        && ($claims['mode'] ?? '') !== 'teacher_student'
        && (int) ($claims['student_id'] ?? 0) > 0;
    if ($promotedToTeacher) {
        try {
            require_once __DIR__ . '/Database.php';
            $query = db()->prepare("SELECT 1 FROM student_accounts WHERE student_id=:id AND npk=:npk AND account_type='teacher_shadow' AND status='active' LIMIT 1");
            $query->execute(['id' => (int) $claims['student_id'], 'npk' => (string) $claims['sub']]);
            $promotedToTeacher = (bool) $query->fetchColumn();
        } catch (Throwable $error) { $promotedToTeacher = false; }
    }
    if (empty($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'student'
        || !$claims || ($claims['role'] ?? '') !== 'student'
        || (int) $claims['student_id'] !== (int) $_SESSION['student_id']
        || (string) ($claims['mode'] ?? '') !== (string) ($_SESSION['sso_mode'] ?? '')
        || !$identity || $isTimedOut || $isFingerprintTampered) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
        if (!$claims || ($claims['role'] ?? '') === 'student') {
            clearSsoBrowserCookie();
        }
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            jsonResponse(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Silakan masuk kembali.']], 401);
        }
        if ($promotedToTeacher) {
            header('Location: ' . SSO_FRONTEND_URL, true, 303);
            exit;
        }
        $errorCode = $isTimedOut ? 'timeout' : 'unauthorized';
        header('Location: /?route=/login&error=' . $errorCode, true, 303);
        exit;
    }
    $_SESSION['_last_activity'] = $now;
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
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
