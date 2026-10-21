<?php
/**
 * Kelas Pengontrol Penyaring Pelatihan (Student Filters Controller).
 *
 * Bertanggung jawab dalam menyajikan antarmuka pencarian dan penyaringan (filter)
 * pelatihan tingkat lanjut, memungkinkan pengguna memfilter kursus berdasarkan kategori spesifik.
 */
class DX_LMS_M_Student_Filters extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Filter Pelatihan.
     *
     * Menyediakan antarmuka dengan semua kategori (tag) pelatihan yang tersedia di sistem.
     * Metode ini akan memuat seluruh pelatihan aktif yang belum diselesaikan oleh siswa,
     * kemudian menyajikannya ke tampilan beserta daftar kategori penyaring.
     *
     * @return void Merender antarmuka pemilihan kategori penyaring kursus.
     */
    public function index()
    {
        startAppSession();
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'student') {
            header('Location: ?route=/login');
            exit();
        }

        $allCategories = [
            'Soft Skill',
            'Hard Skill',
            'Leadership',
            'Technical',
            'Safety',
            'Quality',
            'IT',
            'Management',
            'Engineering',
        ];

        $allCourses = DX_LMS_M_Student_Data::getCourses();
        $activeCourses = array_values(
            array_filter($allCourses, function ($c) {
                return $c['status'] !== 'selesai';
            }),
        );

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'categories' => $allCategories,
            'courses' => $activeCourses,
        ];

        $this->view('DX_LMS_M_Student_Filters', $data);
    }
}
