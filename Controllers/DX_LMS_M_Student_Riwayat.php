<?php
/**
 * Student Riwayat Controller
 */
class DX_LMS_M_Student_Riwayat extends DX_LMS_M_Controller {
    
    /**
     * Menampilkan halaman Riwayat Belajar Student
     */
    public function index() {
        session_start();

        // Ambil course yang selesai dan statistik dari Single Source of Truth
        $completedCourses = DX_StudentData::getCompletedCourses();
        $stats = DX_StudentData::getStats();

        $data = [
            'active_tab'       => 'riwayat',
            'completedCourses' => $completedCourses,
            'stats'            => $stats,
        ];

        $this->view('DX_LMS_M_Student_Riwayat', $data);
    }
}
