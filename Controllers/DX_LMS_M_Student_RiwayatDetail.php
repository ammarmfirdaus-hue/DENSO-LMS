<?php
/**
 * Controller for Riwayat Detail
 */
class DX_LMS_M_Student_RiwayatDetail extends DX_LMS_Controller {

    /**
     * Menampilkan halaman detail riwayat course yang telah diselesaikan Student.
     * Memuat data course, skor akhir, dan status penyelesaian berdasarkan ID.
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

        $data = [
            'course' => $course,
        ];

        $this->view('DX_LMS_M_Student_RiwayatDetail', $data);
    }
}
