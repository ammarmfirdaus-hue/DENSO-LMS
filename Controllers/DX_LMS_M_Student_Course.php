<?php
/**
 * Student Course Controller
 */
class DX_LMS_M_Student_Course extends DX_LMS_M_Controller {
    
    /**
     * Menampilkan halaman Daftar Course Student
     */
    public function index() {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'student') {
            header('Location: ?route=/login');
            exit;
        }

        // Ambil course yang belum selesai dari Single Source of Truth
        $allCourses = DX_StudentData::getCourses();
        $courses = array_values(array_filter($allCourses, function($c) {
            return $c['status'] !== 'selesai';
        }));

        // Extract filter unik dan acak (maks 6)
        $allFilters = [];
        foreach ($allCourses as $c) {
            if (!empty($c['filters'])) {
                foreach ($c['filters'] as $f) {
                    $allFilters[] = $f;
                }
            }
        }
        $allFilters = array_unique($allFilters);
        $allFilters = array_filter($allFilters, function($f) { return strtolower($f) !== 'semua'; });
        shuffle($allFilters);
        $quickFilters = array_slice($allFilters, 0, 6);

        $data = [
            'active_tab' => 'course',
            'base_path'  => '',
            'courses'    => $courses,
            'filters'    => $quickFilters
        ];

        $this->view('DX_LMS_M_Student_Course', $data);
    }
}
