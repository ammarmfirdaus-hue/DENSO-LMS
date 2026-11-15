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
        $threadId = resolveThreadId($_GET['id'] ?? '');
        $rawCourseId = $_GET['course_id'] ?? '';
        $courseId = resolveCourseId($rawCourseId) ?? '';
        $sessionId = resolveSessionId($_GET['session_id'] ?? '') ?? '';
        if (!$threadId || !is_string($courseId) || $courseId === '' || $sessionId === '') {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);
        if (!$course || empty($course['has_access'])) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $this->view('DX_LMS_T_Student_Forum_Thread', [
            'isRiwayat' => $isRiwayat, 'threadId' => $threadId,
            'courseId' => $courseId, 'sessionId' => $sessionId, 'course' => $course
        ]);
    }
}
