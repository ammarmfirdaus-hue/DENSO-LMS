<?php
/**
 * Kelas Pengontrol Rincian Riwayat (History Detail Controller).
 *
 * Berfungsi untuk merender antarmuka evaluasi (sertifikat, rekap nilai kuis)
 * dari sebuah pelatihan yang telah diselesaikan sebelumnya oleh siswa.
 */
class DX_LMS_T_Student_Riwayat_Detail extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Rincian Riwayat Pelatihan.
     *
     * Memuat kembali seluruh histori pembelajaran pelatihan (course) spesifik
     * berdasarkan ID. Menyajikan status kelulusan, nilai akhir (skor), dan umpan balik.
     *
     * @return void Merender tampilan detail pelatihan yang telah rampung.
     */
    public function index()
    {
        startAppSession();

        /** Mendapatkan identitas course dari parameter URL. */
        $courseId = $_GET['id'] ?? '';
        if (!is_string($courseId) || $courseId === '' || strlen($courseId) > 80) {
            header('Location: ?route=/student/riwayat');
            exit();
        }
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);

        /** Kembali ke daftar jika course tidak tersedia bagi peserta saat ini. */
        if (!$course) {
            header('Location: ?route=/student/riwayat');
            exit();
        }

        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'course' => $course,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_T_Student_Riwayat_Detail', $data);
    }
}
