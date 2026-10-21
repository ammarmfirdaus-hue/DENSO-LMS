<?php
/**
 * Kelas Pengontrol Rincian Pelatihan (Course Detail Controller).
 *
 * Bertanggung jawab untuk menyajikan antarmuka rincian dari sebuah pelatihan spesifik,
 * mengelola navigasi antar tab (Materi, Kuis, Forum), dan memastikan kelengkapan sesi
 * sebelum memperbolehkan akses ke fitur evaluasi (Kuis/Assessment).
 */
class DX_LMS_M_Student_Course_Detail extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Rincian Pelatihan (Detail Course).
     *
     * Memuat objek data pelatihan secara spesifik berdasarkan parameter ID (course_id).
     * Metode ini juga mengevaluasi kemajuan (progress) dari seluruh sesi pembelajaran
     * dan meneruskan konfirmasi (flag) ke View untuk mengontrol visibilitas tab.
     *
     * @return void Merender antarmuka detail pelatihan lengkap dengan sesi dan materi.
     */
    public function index()
    {
        startAppSession();

        /** Mengambil ID kursus dari parameter URL, menggunakan nilai bawaan jika tidak tersedia. */
        $courseId = $_GET['id'] ?? 'CRS-TPM-001';
        $tab = $_GET['tab'] ?? 'materi';
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);

        /** Menggunakan ID kursus bawaan jika parameter ID tidak ditemukan. */
        if (!$course) {
            $course = DX_LMS_M_Student_Data::getCourseById('CRS-TPM-001');
        }

        /** Memeriksa apakah seluruh sesi pembelajaran telah diselesaikan. */
        $all_sessions_completed = true;
        if (!empty($course['sessions'])) {
            foreach ($course['sessions'] as $session) {
                if ($session['status'] !== 'selesai') {
                    $all_sessions_completed = false;
                    break;
                }
            }
        } else {
            $all_sessions_completed = false; /** Jika tidak terdapat sesi, sistem menganggap pembelajaran belum selesai sesuai logika bisnis. */
        }

        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'course' => $course,
            'tab' => $tab,
            'all_sessions_completed' => $all_sessions_completed,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_M_Student_Course_Detail', $data);
    }
}
