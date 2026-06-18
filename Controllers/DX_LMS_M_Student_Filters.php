<?php
/**
 * Student Filters Controller
 */
class DX_LMS_M_Student_Filters extends DX_LMS_Controller {
    
    /**
     * Menampilkan halaman filter course lengkap dengan semua kategori tag.
     * Memungkinkan Student memilih filter untuk menyaring daftar course.
     */
    public function index() {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'student') {
            header('Location: ?route=/login');
            exit;
        }

        $allCategories = ['Soft Skill', 'Hard Skill', 'Leadership', 'Technical', 'Safety', 'Quality', 'IT', 'Management', 'Engineering'];
        
        $allCourses = DX_StudentData::getCourses();
        $activeCourses = array_values(array_filter($allCourses, function($c) {
            return $c['status'] !== 'selesai';
        }));

        $data = [
            'active_tab' => 'course',
            'base_path'  => '',
            'categories' => $allCategories,
            'courses'    => $activeCourses
        ];

        $this->view('DX_LMS_M_Student_Filters', $data);
    }
}
