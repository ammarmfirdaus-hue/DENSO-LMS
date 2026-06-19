<?php
/**
 * Controller for Forum Thread Details
 * Menampilkan detail thread diskusi beserta balasan-balasannya.
 */
class DX_LMS_T_Student_Forum_Thread extends DX_LMS_Controller {
    /**
     * Menampilkan halaman detail thread forum dan daftar reply.
     */
    public function index() {
        require_once __DIR__ . '/../Views/DX_LMS_T_Student_Forum_Thread.php';
    }
}
