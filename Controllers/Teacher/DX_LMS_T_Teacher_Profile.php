<?php
/**
 * Kelas Pengontrol Profil Profil Pengajar (Teacher Profile Controller).
 */
class DX_LMS_T_Teacher_Profile extends DX_LMS_M_Controller
{
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        // Just simulating data retrieval. No mockdata needed for simple static UI presentation
        // but we can pass basic dummy info
        $teacherInfo = [
            'name' => 'Budi Sudarsono',
            'npk' => '987654321',
            'role' => 'Teacher TRE',
            'department' => 'Manufacturing Training',
        ];

        $data = [
            'active_tab' => 'profil',
            'base_path' => '',
            'teacher' => $teacherInfo,
        ];

        $this->view('DX_LMS_T_Teacher_Profile', $data);
    }
}
