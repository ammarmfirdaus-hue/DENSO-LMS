<?php
/**
 * Student Dashboard Controller
 */
class DX_LMS_M_Student_Dashboard extends DX_LMS_M_Controller {
    
    /**
     * Menampilkan halaman Beranda (Dashboard) Student
     */
    public function index() {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'student') {
            header('Location: ?route=/login');
            exit;
        }

        // Ambil data dari Single Source of Truth
        $stats = DX_StudentData::getStats();
        $activeCourses = DX_StudentData::getActiveCourses();

        $data = [
            'active_tab'     => 'beranda',
            'base_path'      => '',
            'stats'          => $stats,
            'activeCourses'  => $activeCourses,
        ];

        $this->view('DX_LMS_M_Student_Dashboard', $data);
    }
}
