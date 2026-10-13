<?php
/**
 * Kelas Pengontrol Dasbor Siswa (Student Dashboard Controller).
 *
 * Berperan sebagai pusat informasi utama (Beranda) setelah siswa berhasil masuk (login).
 * Menyajikan statistik belajar ringkas serta daftar pelatihan yang sedang aktif dipelajari.
 */
class DX_LMS_T_Student_Dashboard extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Beranda (Dashboard).
     *
     * Mengambil matriks statistik pembelajaran siswa dan daftar pelatihan
     * berstatus aktif (sedang dipelajari) dari basis data utama, lalu merendernya
     * ke antarmuka beranda siswa. Memvalidasi bahwa pengguna yang mengakses memiliki peran 'student'.
     *
     * @return void Merender tampilan dasbor siswa atau mengalihkan ke login jika sesi tidak valid.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $studentUser = getPortalUser('student');
        if (!$studentUser) {
            header('Location: ?route=/login');
            exit();
        }

        /** Mengambil data dari basis data terpusat (Single Source of Truth). */
        $stats = DX_LMS_M_Student_Data::getStats();
        $allCourses = DX_LMS_M_Student_Data::getCourses();

        /** Memfilter tampilan untuk hanya menampilkan kursus yang sedang aktif atau sedang dipelajari. */
        $progressCourses = array_values(
            array_filter($allCourses, function ($c) {
                return $c['status'] === 'sedang_dipelajari';
            }),
        );

        /** Mengurutkan daftar kursus berdasarkan waktu akses terakhir secara menurun (descending). */
        usort($progressCourses, function ($a, $b) {
            $timeA = $a['last_accessed'] ? strtotime($a['last_accessed']) : 0;
            $timeB = $b['last_accessed'] ? strtotime($b['last_accessed']) : 0;

            /** Memprioritaskan kursus dengan kemajuan belajar jika waktu akses terakhir sama. */
            if ($timeA === $timeB) {
                return $b['progress'] <=> $a['progress'];
            }

            return $timeB <=> $timeA;
        });

        $userName = $studentUser['fullname'] ?? ($studentUser['name'] ?? 'Budi Santoso');
        $userJabatan = $studentUser['jabatan'] ?? ($studentUser['position'] ?? 'Operator');
        $userDepartment = !empty($studentUser['department']) ? $studentUser['department'] : 'Departemen Produksi';

        $data = [
            'active_tab' => 'beranda',
            'base_path' => '',
            'user' => $studentUser,
            'stats' => $stats,
            'courses' => $progressCourses,
            'userName' => $userName,
            'userJabatan' => $userJabatan,
            'userDepartment' => $userDepartment,
        ];

        $this->view('DX_LMS_T_Student_Dashboard', $data);
    }
}
