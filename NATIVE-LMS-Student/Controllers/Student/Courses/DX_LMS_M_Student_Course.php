<?php
/**
 * Kelas Pengontrol Modul Pembelajaran (Student Course Controller).
 *
 * Menangani logika tampilan dan pengelolaan daftar pelatihan (course)
 * yang dapat diakses oleh pengguna dengan peran sebagai 'Student'.
 */
class DX_LMS_M_Student_Course extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Utama Daftar Pelatihan (Course).
     *
     * Mengambil daftar seluruh pelatihan yang tersedia (atau yang terdaftar untuk siswa),
     * menyaring pelatihan yang belum selesai, dan menyiapkan filter pencarian secara acak
     * sebelum merender tampilan utama daftar kelas.
     *
     * @return void Merender antarmuka daftar pelatihan untuk entitas siswa.
     */
    public function index()
    {
        startAppSession();
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'student') {
            header('Location: ?route=/login');
            exit();
        }

        /** Mengambil data kursus yang belum selesai dari basis data terpusat. */
        $allCourses = DX_LMS_M_Student_Data::getCourses();
        $courses = array_values(
            array_filter($allCourses, function ($c) {
                return $c['status'] !== 'selesai';
            }),
        );

        /** Mengurutkan daftar: status 'sedang_dipelajari' berada di urutan atas, dilanjutkan dengan 'belum_dimulai'. */
        usort($courses, function ($a, $b) {
            $statusOrder = ['sedang_dipelajari' => 1, 'belum_dimulai' => 2];
            $orderA = $statusOrder[$a['status']] ?? 3;
            $orderB = $statusOrder[$b['status']] ?? 3;

            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }
            /** Jika status sama, urutkan berdasarkan tingkat kemajuan (progress) secara menurun (descending). */
            return ($b['progress'] ?? 0) <=> ($a['progress'] ?? 0);
        });

        /** Menyiapkan filter unik dengan urutan stabil agar navigasi tidak berubah setiap kali halaman dimuat. */
        $allFilters = [];
        foreach ($allCourses as $c) {
            if (!empty($c['filters'])) {
                foreach ($c['filters'] as $f) {
                    $allFilters[] = $f;
                }
            }
        }
        $allFilters = array_unique($allFilters);
        $allFilters = array_filter($allFilters, function ($f) {
            return strtolower($f) !== 'semua';
        });
        sort($allFilters, SORT_NATURAL | SORT_FLAG_CASE);
        $quickFilters = array_slice(array_values($allFilters), 0, 6);

        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'filters' => $quickFilters,
            'courses' => $courses,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_M_Student_Course', $data);
    }
}
