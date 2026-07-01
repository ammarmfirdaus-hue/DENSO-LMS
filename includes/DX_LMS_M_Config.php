<?php
/**
 * Berkas Konfigurasi Utama (Main Configuration File).
 *
 * Memuat semua definisi konstanta lingkungan yang digunakan oleh sistem
 * Learning Management System (LMS). Termasuk pengaturan basis data, sesi,
 * hingga aturan unggah berkas.
 */

/** Mencegah akses langsung ke berkas guna menjaga keamanan sistem. */
if (!defined('DLS_ROOT')) {
    define('DLS_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

/** ============================================================ */
/** Konfigurasi parameter koneksi basis data. */
/** ============================================================ */
define('DB_HOST', 'localhost'); /** Alamat host basis data. */
define('DB_PORT', '1433'); /** Nomor port koneksi basis data. */
define('DB_NAME', 'DX_LMS'); /** Nama basis data yang digunakan. */
define('DB_USER', 'sa'); /** Nama pengguna untuk autentikasi basis data. */
define('DB_PASS', ''); /** Kata sandi otorisasi basis data. */

/** ============================================================ */
/** Pengaturan global sistem aplikasi. */
/** ============================================================ */
define('APP_NAME', 'Learning Management System');
define('APP_SHORT', 'DLS');
define('APP_VERSION', '1.0.0');

/** URL dasar sistem yang dapat disesuaikan menurut lingkungan pengembangan. */
define('BASE_URL', '/DENSO-LMS/');

/** ============================================================ */
/** Parameter pengaturan untuk manajemen sesi pengguna. */
/** ============================================================ */
define('SESSION_LIFETIME', 3600); /** Jangka waktu (batas kedaluwarsa) diatur selama 1 jam. */
define('SESSION_NAME', 'DLS_SESSION');

/** ============================================================ */
/** Pengaturan batasan dan parameter untuk pengunggahan berkas. */
/** ============================================================ */
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); /** Batas ukuran maksimal berkas sebesar 5 Megabyte. */
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

/** ============================================================ */
/** Mengelola pelaporan kesalahan (sebaiknya dimatikan pada lingkungan produksi). */
/** ============================================================ */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/** Mengatur zona waktu bawaan sistem. */
date_default_timezone_set('Asia/Jakarta');
