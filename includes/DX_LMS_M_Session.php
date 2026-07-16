<?php
/**
 * Manajemen Sesi Pengguna DLS (DLS Session Management).
 *
 * Bertanggung jawab atas pengelolaan data sesi pengguna (login/logout),
 * hak akses peran, serta pemeriksaan keamanan autentikasi pada sistem LMS.
 */

require_once __DIR__ . '/DX_LMS_M_Config.php';

/** Memulai layanan sesi jika belum berjalan. */
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Memeriksa Status Log Masuk Pengguna.
 *
 * Mengevaluasi apakah pengguna saat ini telah memiliki sesi aktif dan ID yang valid.
 *
 * @return bool `true` jika pengguna sudah login, `false` jika sebaliknya.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Mendapatkan Identitas Pengguna Aktif.
 *
 * Menarik kumpulan data pribadi dan akses terkait pengguna yang sedang login.
 * Mengambil informasi langsung dari variabel `$_SESSION`.
 *
 * @return array|null Mengembalikan struktur data pengguna, atau `null` jika tidak ada sesi aktif.
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'npk' => $_SESSION['user_npk'] ?? '',
        'nama' => $_SESSION['user_nama'] ?? '',
        'jabatan' => $_SESSION['user_jabatan'] ?? '',
        'departemen' => $_SESSION['user_departemen'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'student',
        'avatar' => $_SESSION['user_avatar'] ?? '',
    ];
}

/**
 * Menginisialisasi Sesi Pasca-Autentikasi.
 *
 * Mengatur seluruh parameter sesi yang dibutuhkan sistem setelah pengguna
 * dinyatakan lolos verifikasi kredensial saat login.
 *
 * @param array $user_data Data lengkap pengguna dari basis data.
 * @return void
 */
function setUserSession(array $user_data): void
{
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_npk'] = $user_data['npk'] ?? '';
    $_SESSION['user_nama'] = $user_data['nama'] ?? '';
    $_SESSION['user_jabatan'] = $user_data['jabatan'] ?? '';
    $_SESSION['user_departemen'] = $user_data['departemen'] ?? '';
    $_SESSION['user_role'] = $user_data['role'] ?? 'student';
    $_SESSION['user_avatar'] = $user_data['avatar'] ?? '';
    $_SESSION['login_time'] = time();
}

/**
 * Mengganti Peran Sesi Pengguna (Switch Role).
 *
 * Mengalihkan mode hak akses pengguna, misalnya dari 'student' ke 'teacher'.
 * Hanya menerima nilai peran yang diizinkan oleh sistem.
 *
 * @param string $role Peran baru yang akan diaktifkan ('student' atau 'teacher').
 * @return void
 */
function switchRole(string $role): void
{
    if (in_array($role, ['student', 'teacher'])) {
        $_SESSION['user_role'] = $role;
    }
}

/**
 * Mendapatkan Peran Sesi Aktif.
 *
 * @return string Teks jenis peran ('student' atau 'teacher'). Default 'student'.
 */
function getCurrentRole(): string
{
    return $_SESSION['user_role'] ?? 'student';
}

/**
 * Menghentikan Sesi Pengguna (Logout).
 *
 * Menghapus seluruh variabel yang terkait dengan sesi pengguna saat ini
 * dan memusnahkan sesi dari peladen.
 *
 * @return void
 */
function logout(): void
{
    session_unset();
    session_destroy();
}

/**
 * Memaksa Autentikasi Pengguna (Require Login Gate).
 *
 * Melindungi rute dari pengunjung anonim. Jika tidak ditemukan sesi aktif,
 * skrip akan dihentikan dan sistem mengarahkan pengunjung ke halaman login.
 *
 * @param string $redirect_url (Opsional) Rute modifikasi untuk URL dasar.
 * @return void
 */
function requireLogin(string $redirect_url = ''): void
{
    if (!isLoggedIn()) {
        $base = $redirect_url ?: (defined('BASE_URL') ? BASE_URL : '/');
        header('Location: ' . $base . 'login');
        exit();
    }
}
