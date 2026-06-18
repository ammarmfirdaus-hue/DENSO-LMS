<?php
/**
 * Student Notifikasi Controller
 */
class DX_LMS_M_Student_Notifikasi extends DX_LMS_Controller {
    
    /**
     * Menampilkan halaman Notifikasi Student
     */
    public function index() {
        session_start();

        // Ambil notifikasi dari Single Source of Truth
        $notifications = DX_StudentData::getNotifications();

        $data = [
            'active_tab'    => 'notifikasi',
            'notifications' => $notifications,
        ];

        $this->view('DX_LMS_M_Student_Notifikasi', $data);
    }
}
