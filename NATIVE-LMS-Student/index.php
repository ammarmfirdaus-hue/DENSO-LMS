<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/Config.php';
require_once __DIR__ . '/includes/Security.php';
require_once __DIR__ . '/includes/DX_LMS_M_Functions.php';
require_once __DIR__ . '/includes/DX_LMS_M_Student_Data.php';
require_once __DIR__ . '/Core/DX_LMS_M_Router.php';
require_once __DIR__ . '/Core/DX_LMS_M_Controller.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');

date_default_timezone_set('Asia/Jakarta');
$router = new DX_LMS_M_Router();
$router->add('/', 'DX_LMS_M_Auth@login');
$router->add('/login', 'DX_LMS_M_Auth@login');
$router->add('/auth/process', 'DX_LMS_M_Auth@processLogin');
$router->add('/auth/logout', 'DX_LMS_M_Auth@logout');
$routes = [
  '/student' => 'DX_LMS_T_Student_Dashboard@index', '/student/courses' => 'DX_LMS_M_Student_Course@index',
  '/student/courses/filters' => 'DX_LMS_M_Student_Filters@index', '/student/riwayat' => 'DX_LMS_T_Student_Riwayat@index',
  '/student/notifikasi' => 'DX_LMS_T_Student_Notifikasi@index', '/student/profil' => 'DX_LMS_M_Student_Profil@index',
  '/student/profil/detail' => 'DX_LMS_M_Student_Profil@detail', '/student/profil/pengaturan' => 'DX_LMS_M_Student_Profil@pengaturan',
  '/student/profil/bantuan' => 'DX_LMS_M_Student_Profil@bantuan', '/student/course/detail' => 'DX_LMS_M_Student_Course_Detail@index',
  '/student/forum/category' => 'DX_LMS_T_Student_Forum_Category@index', '/student/forum/thread' => 'DX_LMS_T_Student_Forum_Thread@index',
  '/student/forum/new' => 'DX_LMS_T_Student_Forum_New@index', '/student/riwayat/detail' => 'DX_LMS_T_Student_Riwayat_Detail@index',
  '/student/learning' => 'DX_LMS_T_Student_Learning@index', '/student/quiz' => 'DX_LMS_T_Student_Quiz@index'
];
foreach ($routes as $path => $action) $router->add($path, $action);

$route = $_GET['route'] ?? '/';
if (isset($routes[$route])) requireStudent();
$router->dispatch($route);
