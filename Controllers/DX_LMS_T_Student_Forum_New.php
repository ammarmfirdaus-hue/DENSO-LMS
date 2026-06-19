<?php
/**
 * Controller for Creating New Forum Topic
 * Menangani pembuatan topik diskusi baru pada forum course.
 */
class DX_LMS_T_Student_Forum_New extends DX_LMS_Controller {
    /**
     * Menampilkan halaman form pembuatan topik forum baru.
     */
    public function index() {
        require_once __DIR__ . '/../Views/DX_LMS_T_Student_Forum_New.php';
    }
}
