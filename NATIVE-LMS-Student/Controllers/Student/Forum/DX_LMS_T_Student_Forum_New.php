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
        $rawCourseId = $_GET['course_id'] ?? '';
        $courseId = resolveCourseId($rawCourseId) ?? '';
        $sessionId = resolveSessionId($_GET['session_id'] ?? '') ?? '';
        $categoryType = $_GET['category_type'] ?? 'materi';
        if (!is_string($courseId) || $courseId === '' || !preg_match('/^[A-Za-z0-9_-]{1,80}$/', $courseId)
            || !is_string($sessionId) || $sessionId === ''
            || !is_string($categoryType) || !in_array($categoryType, ['materi', 'quiz', 'final_assessment'], true)) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);
        if (!$course || empty($course['has_access'])) {
            header('Location: ?route=/student/courses', true, 303);
            exit;
        }
        $session = null;
        foreach (($course['sessions'] ?? []) as $candidate) {
            if ((string) ($candidate['id'] ?? '') === $sessionId) { $session = $candidate; break; }
        }
        if (!$session) {
            header('Location: ?route=/student/forum/category&course_id=' . rawurlencode(publicCourseId($courseId))
                . '&category_type=' . rawurlencode($categoryType) . '&session_id=' . rawurlencode(publicSessionId($sessionId)), true, 303);
            exit;
        }
        $categoryUrl = '?route=/student/forum/category&course_id=' . rawurlencode(publicCourseId($courseId))
            . '&category_type=' . rawurlencode($categoryType) . '&session_id=' . rawurlencode(publicSessionId($sessionId));
        $this->view('DX_LMS_T_Student_Forum_New', compact('courseId', 'sessionId', 'categoryType', 'course', 'session', 'categoryUrl'));
    }
}
