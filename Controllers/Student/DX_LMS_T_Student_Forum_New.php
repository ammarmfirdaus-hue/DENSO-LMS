<?php
/**
 * Kelas Pengontrol Pembuatan Topik Forum Baru (New Forum Topic Controller).
 *
 * Mengelola proses interaksi ketika siswa ingin membuat ruang diskusi
 * atau topik forum yang baru terkait dengan suatu materi atau kursus.
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Models/DX_LMS_M_Forum.php';

class DX_LMS_T_Student_Forum_New extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Formulir Topik Baru.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $courseId = $_GET['course_id'] ?? '1';
        $sessionId = $_GET['session_id'] ?? '';
        $categoryType = $_GET['category_type'] ?? 'materi';

        $this->view('DX_LMS_T_Student_Forum_New', [
            'courseId' => $courseId,
            'sessionId' => $sessionId,
            'categoryType' => $categoryType,
        ]);
    }

    /**
     * Memproses Penyimpanan Topik Baru ke Database.
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? [
            'npk' => '12345',
            'name' => 'Budi Santoso',
            'jabatan' => 'Operator Produksi',
            'department' => 'Manufacturing',
            'role' => 'student'
        ];

        $courseId = $_POST['course_id'] ?? '1';
        $sessionId = !empty($_POST['sesi']) ? $_POST['sesi'] : null;
        $title = trim($_POST['judul'] ?? '');
        $content = trim($_POST['pesan'] ?? '');
        $categoryType = $_POST['category_type'] ?? ($sessionId === 'quiz' ? 'quiz' : 'materi');

        if (empty($title) || empty($content)) {
            header('Location: ?route=/student/forum/new&course_id=' . urlencode($courseId) . '&error=empty');
            exit();
        }

        $authorDept = ($user['jabatan'] ?? 'Operator') . ' · Line A';

        $forumModel = new DX_LMS_M_Forum();
        $topicId = $forumModel->createTopic([
            'course_id' => $courseId,
            'session_id' => ($sessionId === 'umum' ? null : $sessionId),
            'category_type' => $categoryType,
            'author_npk' => $user['npk'] ?? '12345',
            'author_name' => $user['name'] ?? ($user['fullname'] ?? 'Student'),
            'author_role' => 'student',
            'author_avatar' => '',
            'author_dept' => $authorDept,
            'title' => $title,
            'content' => $content,
            'attachment_path' => null
        ]);

        header('Location: ?route=/student/forum/thread&id=' . $topicId . '&created=1');
        exit();
    }
}
