<?php
/**
 * Kelas Pengontrol Pembuatan Topik Forum Baru (New Forum Topic Controller).
 *
 * Mengelola proses interaksi ketika siswa ingin membuat ruang diskusi
 * atau topik forum yang baru terkait dengan suatu materi atau kursus.
 */
class DX_LMS_T_Student_Forum_New extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Formulir Topik Baru.
     *
     * Merender antarmuka pengguna yang memuat formulir masukan (input)
     * untuk judul dan konten diskusi (thread) baru.
     *
     * @return void
     */
    public function index()
    {
        $courseId = $_GET['course_id'] ?? '';
        $sessionId = $_GET['session_id'] ?? '';
        $categoryType = $_GET['category_type'] ?? 'materi';
        if (!is_string($courseId) || $courseId === '' || strlen($courseId) > 80
            || !is_string($sessionId) || $sessionId === '' || strlen($sessionId) > 80
            || !is_string($categoryType) || !in_array($categoryType, ['materi', 'quiz', 'final_assessment'], true)) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);
        if (!$course) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $session = null;
        foreach (($course['sessions'] ?? []) as $candidate) {
            if ((string) ($candidate['id'] ?? '') === $sessionId) { $session = $candidate; break; }
        }
        if (!$session) {
            header('Location: ?route=/student/forum/category&course_id=' . rawurlencode($courseId)
                . '&category_type=' . rawurlencode($categoryType) . '&session_id=' . rawurlencode($sessionId), true, 303);
            exit;
        }
        $categoryUrl = '?route=/student/forum/category&course_id=' . rawurlencode($courseId)
            . '&category_type=' . rawurlencode($categoryType) . '&session_id=' . rawurlencode($sessionId);
        $this->view('DX_LMS_T_Student_Forum_New', compact('courseId', 'sessionId', 'categoryType', 'course', 'session', 'categoryUrl'));
    }
}
