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
        session_start();

        /** Mendapatkan identitas kursus dari parameter URL dengan nilai bawaan. */
        $courseId = $_GET['id'] ?? 'CRS005';
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);

        /** Menerapkan identitas kursus bawaan jika parameter tidak tersedia. */
        if (!$course) {
            $course = DX_LMS_M_Student_Data::getCourseById('CRS005');
        }

        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'course' => $course,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_T_Student_Riwayat_Detail', $data);
    }
}
