<?php
/**
 * Kelas Pengontrol Rincian Riwayat (History Detail Controller).
 * 
 * Berfungsi untuk merender antarmuka evaluasi (sertifikat, rekap nilai kuis)
 * dari sebuah pelatihan yang telah diselesaikan sebelumnya oleh siswa.
 */
class DX_LMS_T_Student_Riwayat_Detail extends DX_LMS_Controller {

    /**
     * Menampilkan Halaman Rincian Riwayat Pelatihan.
     * 
     * Memuat kembali seluruh histori pembelajaran pelatihan (course) spesifik 
     * berdasarkan ID. Menyajikan status kelulusan, nilai akhir (skor), dan umpan balik.
     * 
     * @return void Merender tampilan detail pelatihan yang telah rampung.
     */
    public function index() {
        session_start();

        // Ambil course_id dari query string, default CRS005
        $courseId = $_GET['id'] ?? 'CRS005';
        $course = DX_StudentData::getCourseById($courseId);

        // Fallback ke CRS005 jika ID tidak ditemukan
        if (!$course) {
            $course = DX_StudentData::getCourseById('CRS005');
        }

        $stats = DX_StudentData::getStats();

        $data = [
            'course' => $course,
            'stats'  => $stats,
        ];

        $this->view('DX_LMS_T_Student_Riwayat_Detail', $data);
    }
}
