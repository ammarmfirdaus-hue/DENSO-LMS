<?php
/**
 * Student Profil Controller
 */
class DX_LMS_M_Student_Profil extends DX_LMS_Controller {
    
    /**
     * Menampilkan halaman Profil Student
     */
    public function index() {
        session_start();
        $this->view('DX_LMS_M_Student_Profil');
    }
}
