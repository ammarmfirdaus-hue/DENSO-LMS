<?php
/**
 * Kelas Pengontrol Dasbor Pengajar (Teacher Dashboard Controller).
 *
 * Berperan sebagai pusat informasi utama bagi pengajar. Menampilkan statistik
 * ringkas dan daftar course yang diampu.
 */
class DX_LMS_M_Teacher_Dashboard extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Beranda (Dashboard) Pengajar.
     *
     * @return void
     */
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        /** Mengambil data dari basis data terpusat (Single Source of Truth). */
        // Untuk MVP kita ambil seluruh data course sementara
        $allCourses = DX_LMS_M_Student_Data::getCourses();
        
        // Asumsi Teacher mengampu 3 course pertama (sebagai data dummy)
        $managedCourses = array_slice($allCourses, 0, 3);

        $stats = [
            'total_course' => count($managedCourses),
            'total_student' => 125, // Dummy
            'need_grading' => 5 // Dummy
        ];

        $data = [
            'active_tab' => 'beranda',
            'base_path' => '',
            'stats' => $stats,
            'courses' => $managedCourses,
        ];

        $this->view('DX_LMS_M_Teacher_Dashboard', $data);
    }
}
