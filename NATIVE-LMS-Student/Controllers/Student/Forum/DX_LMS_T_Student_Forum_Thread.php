<?php
/**
 * Kelas Pengontrol Rincian Thread Forum (Forum Thread Detail Controller).
 *
 * Berfungsi untuk mengambil dan menyajikan keseluruhan isi percakapan
 * pada sebuah topik diskusi, termasuk kiriman utama (parent post) beserta
 * daftar seluruh tanggapan (replies) dari peserta lain.
 */
class DX_LMS_T_Student_Forum_Thread extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Rincian Topik Diskusi.
     *
     * Memuat file tampilan khusus untuk membaca seluruh percakapan
     * dalam satu topik (thread) secara terurut.
     *
     * @return void
     */
    public function index()
    {
        $isRiwayat = isset($_GET['is_riwayat']) && $_GET['is_riwayat'] === 'true';
        $threadId = $_GET['id'] ?? '';
        $courseId = $_GET['course_id'] ?? '';
        $sessionId = $_GET['session_id'] ?? '';
        if (!is_string($threadId) || !ctype_digit($threadId) || (int) $threadId < 1
            || !is_string($courseId) || strlen($courseId) > 80
            || !is_string($sessionId) || $courseId === '' || $sessionId === '' || strlen($sessionId) > 80) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);
        if (!$course) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $this->view('DX_LMS_T_Student_Forum_Thread', [
            'isRiwayat' => $isRiwayat, 'threadId' => $threadId,
            'courseId' => $courseId, 'sessionId' => $sessionId, 'course' => $course
        ]);
    }
}
