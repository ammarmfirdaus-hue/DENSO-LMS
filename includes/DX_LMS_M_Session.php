<?php
/**
 * Manajemen Session DLS
 * Sistem Pembelajaran Denso
 */

require_once __DIR__ . '/config.php';

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Memeriksa apakah pengguna sudah login
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Mendapatkan data pengguna saat ini dari session
 * @return array|null
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) return null;

    return [
        'id'         => $_SESSION['user_id'] ?? null,
        'npk'        => $_SESSION['user_npk'] ?? '',
        'nama'       => $_SESSION['user_nama'] ?? '',
        'jabatan'    => $_SESSION['user_jabatan'] ?? '',
        'departemen' => $_SESSION['user_departemen'] ?? '',
        'role'       => $_SESSION['user_role'] ?? 'student',
        'avatar'     => $_SESSION['user_avatar'] ?? '',
    ];
}

/**
 * Mengatur session pengguna setelah login
 * @param array $user_data
 */
function setUserSession(array $user_data): void
{
    $_SESSION['user_id']         = $user_data['id'];
    $_SESSION['user_npk']        = $user_data['npk'] ?? '';
    $_SESSION['user_nama']       = $user_data['nama'] ?? '';
    $_SESSION['user_jabatan']    = $user_data['jabatan'] ?? '';
    $_SESSION['user_departemen'] = $user_data['departemen'] ?? '';
    $_SESSION['user_role']       = $user_data['role'] ?? 'student';
    $_SESSION['user_avatar']     = $user_data['avatar'] ?? '';
    $_SESSION['login_time']      = time();
}

/**
 * Mengganti peran pengguna
 * @param string $role ('student' or 'teacher')
 */
function switchRole(string $role): void
{
    if (in_array($role, ['student', 'teacher'])) {
        $_SESSION['user_role'] = $role;
    }
}

/**
 * Mendapatkan peran saat ini
 * @return string
 */
function getCurrentRole(): string
{
    return $_SESSION['user_role'] ?? 'student';
}

/**
 * Menghancurkan session (logout)
 */
function logout(): void
{
    session_unset();
    session_destroy();
}

/**
 * Mewajibkan login - arahkan ke halaman login jika belum login
 * @param string $redirect_url
 */
function requireLogin(string $redirect_url = ''): void
{
    if (!isLoggedIn()) {
        $base = $redirect_url ?: (defined('BASE_URL') ? BASE_URL : '/');
        header('Location: ' . $base . 'login.php');
        exit;
    }
}
