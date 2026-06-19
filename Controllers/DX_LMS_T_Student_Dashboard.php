<?php
/**
 * Kelas Pengontrol Dasbor Siswa (Student Dashboard Controller).
 * 
 * Berperan sebagai pusat informasi utama (Beranda) setelah siswa berhasil masuk (login).
 * Menyajikan statistik belajar ringkas serta daftar pelatihan yang sedang aktif dipelajari.
 */
class DX_LMS_T_Student_Dashboard extends DX_LMS_Controller {
    
    /**
     * Menampilkan Halaman Beranda (Dashboard).
     * 
     * Mengambil matriks statistik pembelajaran siswa dan daftar pelatihan
     * berstatus aktif (sedang dipelajari) dari basis data utama, lalu merendernya
     * ke antarmuka beranda siswa. Memvalidasi bahwa pengguna yang mengakses memiliki peran 'student'.
     * 
     * @return void Merender tampilan dasbor siswa atau mengalihkan ke login jika sesi tidak valid.
     */
    public function index() {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'student') {
            header('Location: ?route=/login');
            exit;
        }

        // Ambil data dari Single Source of Truth
        $stats = DX_StudentData::getStats();
        $activeCourses = DX_StudentData::getActiveCourses();

        $data = [
            'active_tab'     => 'beranda',
            'base_path'      => '',
            'stats'          => $stats,
            'activeCourses'  => $activeCourses,
        ];

        $this->view('DX_LMS_T_Student_Dashboard', $data);
    }
}
