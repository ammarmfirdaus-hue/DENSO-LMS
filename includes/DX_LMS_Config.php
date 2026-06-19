<?php
/**
 * DLS Configuration
 * Learning Management System
 */

// Mencegah akses langsung
if (!defined('DLS_ROOT')) {
    define('DLS_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// ============================================================
// Konfigurasi Database (SQL Server)
// ============================================================
define('DB_HOST', 'localhost');           // Host SQL Server
define('DB_PORT', '1433');               // Port SQL Server
define('DB_NAME', 'DX_LMS');          // Nama Database (standar: DX_[NamaSistem])
define('DB_USER', 'sa');                 // Pengguna Database
define('DB_PASS', '');                   // Password Database

// ============================================================
// Konfigurasi Aplikasi
// ============================================================
define('APP_NAME', 'Learning Management System');
define('APP_SHORT', 'DLS');
define('APP_VERSION', '1.0.0');

// Base URL (sesuaikan dengan environment)
define('BASE_URL', '/DENSO-LMS/');

// ============================================================
// Konfigurasi Session
// ============================================================
define('SESSION_LIFETIME', 3600);        // 1 hour
define('SESSION_NAME', 'DLS_SESSION');

// ============================================================
// Konfigurasi Upload File
// ============================================================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);  // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// ============================================================
// Pelaporan Error (atur ke 0 untuk production)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zona Waktu
date_default_timezone_set('Asia/Jakarta');
