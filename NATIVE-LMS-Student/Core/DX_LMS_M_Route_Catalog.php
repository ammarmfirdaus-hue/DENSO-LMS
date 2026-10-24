<?php
declare(strict_types=1);

/**
 * Returns the supported page-to-controller map for the native Student portal.
 * Keeping the map in one place lets route wiring be checked without booting the database.
 */
function studentRouteCatalog(): array
{
    return [
        '/student' => 'DX_LMS_T_Student_Dashboard@index',
        '/student/courses' => 'DX_LMS_M_Student_Course@index',
        '/student/courses/filters' => 'DX_LMS_M_Student_Filters@index',
        '/student/riwayat' => 'DX_LMS_T_Student_Riwayat@index',
        '/student/notifikasi' => 'DX_LMS_T_Student_Notifikasi@index',
        '/student/profil' => 'DX_LMS_M_Student_Profil@index',
        '/student/profil/detail' => 'DX_LMS_M_Student_Profil@detail',
        '/student/profil/pengaturan' => 'DX_LMS_M_Student_Profil@pengaturan',
        '/student/profil/bantuan' => 'DX_LMS_M_Student_Profil@bantuan',
        '/student/course/detail' => 'DX_LMS_M_Student_Course_Detail@index',
        '/student/forum/category' => 'DX_LMS_T_Student_Forum_Category@index',
        '/student/forum/thread' => 'DX_LMS_T_Student_Forum_Thread@index',
        '/student/forum/new' => 'DX_LMS_T_Student_Forum_New@index',
        '/student/riwayat/detail' => 'DX_LMS_T_Student_Riwayat_Detail@index',
        '/student/learning' => 'DX_LMS_T_Student_Learning@index',
        '/student/quiz' => 'DX_LMS_T_Student_Quiz@index',
    ];
}
