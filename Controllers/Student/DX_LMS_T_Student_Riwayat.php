<?php
/**
 * Kelas Pengontrol Riwayat Pembelajaran (Student History Controller).
 *
 * Menangani rekapitulasi data pelatihan yang telah diselesaikan (lulus) oleh siswa
 * beserta representasi statistik performa belajarnya.
 */
class DX_LMS_T_Student_Riwayat extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Riwayat Belajar.
     *
     * Mengambil daftar pelatihan dengan status penyelesaian 100% (selesai)
     * dari basis data siswa, dan menampilkan antarmuka riwayat lengkap.
     *
     * @return void
     */
    public function index()
    {
        session_start();

        /** Mengambil daftar kursus yang telah diselesaikan beserta statistik dari basis data. */
        $completedCourses = DX_LMS_M_Student_Data::getCompletedCourses();
        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'active_tab' => 'riwayat',
            'completedCourses' => $completedCourses,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_T_Student_Riwayat', $data);
    }
}
