<?php
/**
 * Kelas Pengontrol Riwayat Pembelajaran (Student History Controller).
 * 
 * Menangani rekapitulasi data pelatihan yang telah diselesaikan (lulus) oleh siswa
 * beserta representasi statistik performa belajarnya.
 */
class DX_LMS_T_Student_Riwayat extends DX_LMS_Controller {
    
    /**
     * Menampilkan Halaman Riwayat Belajar.
     * 
     * Mengambil daftar pelatihan dengan status penyelesaian 100% (selesai) 
     * dari basis data siswa, dan menampilkan antarmuka riwayat lengkap.
     * 
     * @return void
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

        $this->view('DX_LMS_T_Student_Riwayat', $data);
    }
}
