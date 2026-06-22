<?php
/**
 * Kelas Pengontrol Modul Pembelajaran (Student Course Controller).
 * 
 * Menangani logika tampilan dan pengelolaan daftar pelatihan (course)
 * yang dapat diakses oleh pengguna dengan peran sebagai 'Student'.
 */
class DX_LMS_M_Student_Course extends DX_LMS_Controller {
    
    /**
     * Menampilkan Halaman Utama Daftar Pelatihan (Course).
     * 
     * Mengambil daftar seluruh pelatihan yang tersedia (atau yang terdaftar untuk siswa),
     * menyaring pelatihan yang belum selesai, dan menyiapkan filter pencarian secara acak
     * sebelum merender tampilan utama daftar kelas.
     * 
     * @return void Merender antarmuka daftar pelatihan untuk entitas siswa.
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

        // Urutkan: 'sedang_dipelajari' di atas, lalu 'belum_dimulai'
        usort($courses, function($a, $b) {
            $statusOrder = ['sedang_dipelajari' => 1, 'belum_dimulai' => 2];
            $orderA = $statusOrder[$a['status']] ?? 3;
            $orderB = $statusOrder[$b['status']] ?? 3;
            
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }
            // Jika status sama, urutkan berdasarkan progress menurun
            return ($b['progress'] ?? 0) <=> ($a['progress'] ?? 0);
        });

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

        $stats = DX_StudentData::getStats();

        $data = [
            'active_tab' => 'course',
            'base_path'  => '',
            'filters'    => $quickFilters,
            'courses'    => $courses,
            'stats'      => $stats
        ];

        $this->view('DX_LMS_M_Student_Course', $data);
    }
}
