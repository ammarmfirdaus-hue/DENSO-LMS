<?php
/**
 * Front Controller Utama
 * DLS Standard Program MVC — Learning Management System
 */

/** Membiarkan server menangani permintaan berkas statis secara langsung. */
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}

require_once 'includes/DX_LMS_M_Config.php';
require_once 'Core/DX_LMS_M_Router.php';
require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Core/DX_LMS_M_Model.php';
require_once 'includes/DX_LMS_M_Functions.php';
require_once 'includes/DX_LMS_M_Student_Data.php';

/** Mendeteksi dan menetapkan zona waktu secara otomatis berdasarkan kuki (cookie). */
$userTz = $_COOKIE['user_timezone'] ?? 'Asia/Jakarta';
if (in_array($userTz, ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])) {
    date_default_timezone_set($userTz);
} else {
    date_default_timezone_set('Asia/Jakarta');
}

/** Melakukan inisialisasi pada objek pengatur rute (Router). */
$router = new DX_LMS_M_Router();

/** Mendefinisikan daftar rute aplikasi yang tersedia. */
/** Format definisi: Tautan Rute => Pengontrol@Fungsi. */
$router->add('/', 'DX_LMS_M_Auth@login');
$router->add('/login', 'DX_LMS_M_Auth@login');
$router->add('/auth/process', 'DX_LMS_M_Auth@processLogin');
$router->add('/auth/logout', 'DX_LMS_M_Auth@logout');
$router->add('/pilih-peran', 'DX_LMS_M_Auth@roleSelection');
$router->add('/auth/set-role', 'DX_LMS_M_Auth@setRole');

/** Mendefinisikan rute khusus untuk pengguna Siswa. */
$router->add('/student', 'DX_LMS_T_Student_Dashboard@index');
$router->add('/student/courses', 'DX_LMS_M_Student_Course@index');
$router->add('/student/courses/filters', 'DX_LMS_M_Student_Filters@index');
$router->add('/student/riwayat', 'DX_LMS_T_Student_Riwayat@index');
$router->add('/student/notifikasi', 'DX_LMS_T_Student_Notifikasi@index');
$router->add('/student/profil', 'DX_LMS_M_Student_Profil@index');
$router->add('/student/profil/detail', 'DX_LMS_M_Student_Profil@detail');
$router->add('/student/profil/pengaturan', 'DX_LMS_M_Student_Profil@pengaturan');
$router->add('/student/profil/bantuan', 'DX_LMS_M_Student_Profil@bantuan');
$router->add('/student/course/detail', 'DX_LMS_M_Student_Course_Detail@index');
$router->add('/student/forum/category', 'DX_LMS_T_Student_Forum_Category@index');
$router->add('/student/forum/thread', 'DX_LMS_T_Student_Forum_Thread@index');
$router->add('/student/forum/new', 'DX_LMS_T_Student_Forum_New@index');
$router->add('/student/riwayat/detail', 'DX_LMS_T_Student_Riwayat_Detail@index');
$router->add('/student/learning', 'DX_LMS_T_Student_Learning@index');
$router->add('/student/quiz', 'DX_LMS_T_Student_Quiz@index');

/** Mendefinisikan rute khusus untuk pengguna Pengajar (Teacher). */
$router->add('/teacher', 'DX_LMS_M_Teacher_Dashboard@index');
$router->add('/teacher/aktivitas', 'DX_LMS_T_Teacher_Activity@index');
$router->add('/teacher/courses', 'DX_LMS_M_Teacher_Course@index');
$router->add('/teacher/course/session', 'DX_LMS_M_Teacher_Course@session');
$router->add('/teacher/course/material', 'DX_LMS_M_Teacher_Course@material');
$router->add('/teacher/course/add-material', 'DX_LMS_M_Teacher_Course@addMaterial');
$router->add('/teacher/grade', 'DX_LMS_T_Teacher_Grade@index');
$router->add('/teacher/forum', 'DX_LMS_T_Teacher_Forum@index');
$router->add('/teacher/notifikasi', 'DX_LMS_T_Student_Notifikasi@index');
$router->add('/teacher/profil', 'DX_LMS_M_Student_Profil@index');
$router->add('/teacher/profil/detail', 'DX_LMS_M_Student_Profil@detail');
$router->add('/teacher/profil/pengaturan', 'DX_LMS_M_Student_Profil@pengaturan');
$router->add('/teacher/profil/bantuan', 'DX_LMS_M_Student_Profil@bantuan');

/** Mendefinisikan rute khusus untuk pengguna Admin (MVP Tahap 1 & 2). */
$router->add('/admin', 'DX_LMS_M_Admin_Dashboard@index');
$router->add('/admin/user', 'DX_LMS_M_Admin_User@index');
$router->add('/admin/course', 'DX_LMS_M_Admin_Course@index');
$router->add('/admin/role', 'DX_LMS_M_Admin_Role@index');
$router->add('/admin/assignment', 'DX_LMS_M_Admin_Assignment@index');
$router->add('/admin/monitoring', 'DX_LMS_M_Admin_Monitoring@index');
$router->add('/admin/score', 'DX_LMS_M_Admin_Score@index');
$router->add('/admin/activity', 'DX_LMS_M_Admin_Activity@index');
$router->add('/admin/notification', 'DX_LMS_M_Admin_Notification@index');

/** Menjalankan permintaan yang masuk berdasarkan rute yang sesuai. */
$route = $_GET['route'] ?? '/';
$router->dispatch($route);
