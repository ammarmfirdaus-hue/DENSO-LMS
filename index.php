<?php
/**
 * Front Controller Utama
 * PT Denso Standard Program MVC
 */

// Jika me-request file statis, biarkan web server yang menangani (untuk php -S)
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}

require_once 'includes/DX_LMS_M_Config.php';
require_once 'Core/DX_LMS_M_Router.php';
require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Core/DX_LMS_M_Model.php';
require_once 'includes/DX_LMS_M_Functions.php';
require_once 'includes/DX_LMS_M_StudentData.php';

// Auto-detect and set Timezone from cookie if available
$userTz = $_COOKIE['user_timezone'] ?? 'Asia/Jakarta';
if (in_array($userTz, ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])) {
    date_default_timezone_set($userTz);
} else {
    date_default_timezone_set('Asia/Jakarta');
}

// Instansiasi Router
$router = new DX_LMS_M_Router();

// Mendefinisikan Routes
// Format: URL rute => Controller@Metode
$router->add('/', 'DX_LMS_M_Auth@login');
$router->add('/login', 'DX_LMS_M_Auth@login');
$router->add('/auth/process', 'DX_LMS_M_Auth@processLogin');
$router->add('/auth/logout', 'DX_LMS_M_Auth@logout');
$router->add('/pilih-peran', 'DX_LMS_M_Auth@roleSelection');
$router->add('/auth/set-role', 'DX_LMS_M_Auth@setRole');

// Routes untuk Student
$router->add('/student', 'DX_LMS_M_Student_Dashboard@index');
$router->add('/student/courses', 'DX_LMS_M_Student_Course@index');
$router->add('/student/courses/filters', 'DX_LMS_M_Student_Filters@index');
$router->add('/student/riwayat', 'DX_LMS_M_Student_Riwayat@index');
$router->add('/student/notifikasi', 'DX_LMS_M_Student_Notifikasi@index');
$router->add('/student/profil', 'DX_LMS_M_Student_Profil@index');
$router->add('/student/course/detail', 'DX_LMS_M_Student_CourseDetail@index');
$router->add('/student/forum/thread', 'DX_LMS_M_Student_ForumThread@index');
$router->add('/student/forum/new', 'DX_LMS_M_Student_ForumNew@index');
$router->add('/student/riwayat/detail', 'DX_LMS_M_Student_RiwayatDetail@index');
$router->add('/student/learning', 'DX_LMS_M_Student_Learning@index');
$router->add('/student/quiz', 'DX_LMS_M_Student_Quiz@index');

// Menjalankan request
$route = $_GET['route'] ?? '/';
$router->dispatch($route);
