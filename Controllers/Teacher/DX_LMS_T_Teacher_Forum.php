<?php
/**
 * Kelas Pengontrol Forum Pengajar (Teacher Forum Controller).
 *
 * Mengelola interaksi pengajar di dalam forum diskusi:
 * - Menampilkan daftar topik diskusi course
 * - Menampilkan rincian thread percakapan
 * - Membalas pesan peserta dengan badge Pengajar Terverifikasi
 * - Menandai atau membatalkan jawaban sebagai Solusi Resmi
 * - Membuat pengumuman / topik baru
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Models/DX_LMS_M_Forum.php';
require_once 'includes/DX_LMS_M_Teacher_MockData.php';

class DX_LMS_T_Teacher_Forum extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Forum Pengajar.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        $forumModel = new DX_LMS_M_Forum();
        $managedCourses = DX_LMS_M_Teacher_MockData::getManagedCourses();

        $selectedCourseId = $_GET['course_id'] ?? (string)($managedCourses[0]['id'] ?? '1');
        $filterCategory = $_GET['category'] ?? 'all';
        $filterStatus = $_GET['status'] ?? 'all';
        $searchKeyword = trim($_GET['q'] ?? '');

        $filters = [];
        if ($filterCategory !== 'all') $filters['category_type'] = $filterCategory;
        if ($filterStatus !== 'all') $filters['status'] = $filterStatus;
        if (!empty($searchKeyword)) $filters['search'] = $searchKeyword;

        $topics = $forumModel->getTopics($selectedCourseId, $filters);

        // Cari course aktif
        $activeCourse = null;
        foreach ($managedCourses as $c) {
            if ((string)$c['id'] === (string)$selectedCourseId) {
                $activeCourse = $c;
                break;
            }
        }
        if (!$activeCourse && !empty($managedCourses)) {
            $activeCourse = $managedCourses[0];
        }

        // Metrik Forum Course
        $totalTopics = count($topics);
        $unresolvedCount = 0;
        $resolvedCount = 0;
        foreach ($topics as $t) {
            if (($t['status'] ?? 'open') === 'resolved') {
                $resolvedCount++;
            } else {
                $unresolvedCount++;
            }
        }

        $data = [
            'active_tab' => 'forum',
            'base_path' => '',
            'courses' => $managedCourses,
            'selectedCourseId' => $selectedCourseId,
            'activeCourse' => $activeCourse,
            'topics' => $topics,
            'totalTopics' => $totalTopics,
            'unresolvedCount' => $unresolvedCount,
            'resolvedCount' => $resolvedCount,
            'filterCategory' => $filterCategory,
            'filterStatus' => $filterStatus,
            'searchKeyword' => $searchKeyword,
        ];

        $this->view('Teacher/DX_LMS_T_Teacher_Forum', $data);
    }

    /**
     * Menampilkan Detail Thread Percakapan untuk Pengajar.
     */
    public function thread()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        $topicId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
        $forumModel = new DX_LMS_M_Forum();
        $topic = $forumModel->getTopic($topicId);

        if (!$topic) {
            header('Location: ?route=/teacher/forum');
            exit();
        }

        $replies = $forumModel->getReplies($topicId);
        $user = $_SESSION['user'];

        $backUrl = '?route=/teacher/course/detail&course_id=' . urlencode($topic['course_id'] ?? '1') . '&tab=forum';

        $this->view('Student/DX_LMS_T_Student_Forum_Thread', [
            'topic' => $topic,
            'replies' => $replies,
            'isRiwayat' => false,
            'user' => $user,
            'userRole' => 'teacher',
            'backUrl' => $backUrl
        ]);
    }

    /**
     * Memproses Pengiriman Balasan dari Pengajar (AJAX).
     */
    public function reply()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Otoritas tidak sah.']);
            exit();
        }

        $user = $_SESSION['user'];
        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($topicId <= 0 || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Pesan tanggapan tidak boleh kosong.']);
            exit();
        }

        $forumModel = new DX_LMS_M_Forum();
        $authorDept = ($user['jabatan'] ?? 'Leader') . ' · ' . ($user['department'] ?? 'Manufacturing');
        $replyId = $forumModel->createReply([
            'topic_id' => $topicId,
            'author_npk' => $user['npk'] ?? '67890',
            'author_name' => $user['fullname'] ?? ($user['name'] ?? 'Ahmad Rizal'),
            'author_role' => 'teacher',
            'author_avatar' => '',
            'author_dept' => $authorDept,
            'content' => $content
        ]);

        echo json_encode([
            'success' => true,
            'reply_id' => $replyId,
            'message' => 'Tanggapan Pengajar berhasil diposting!'
        ]);
        exit();
    }

    /**
     * Menandai atau Membatalkan Solusi Diskusi (AJAX).
     */
    public function toggleSolution()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Otoritas pengajar diperlukan.']);
            exit();
        }

        $replyId = isset($_POST['reply_id']) ? (int) $_POST['reply_id'] : 0;
        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0;

        if ($replyId <= 0 || $topicId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            exit();
        }

        $forumModel = new DX_LMS_M_Forum();
        $ok = $forumModel->toggleSolution($replyId, $topicId);

        echo json_encode(['success' => $ok]);
        exit();
    }

    /**
     * Toggle Like (AJAX).
     */
    public function toggleLike()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
            exit();
        }

        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : null;
        $replyId = isset($_POST['reply_id']) ? (int) $_POST['reply_id'] : null;
        $npk = $user['npk'] ?? '67890';

        $forumModel = new DX_LMS_M_Forum();
        $result = $forumModel->toggleLike($topicId, $replyId, $npk);

        echo json_encode([
            'success' => true,
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count']
        ]);
        exit();
    }

    /**
     * Membuat Topik / Pengumuman Baru oleh Pengajar.
     */
    public function newTopic()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        $user = $_SESSION['user'];
        $courseId = $_POST['course_id'] ?? '1';
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = $_POST['category_type'] ?? 'materi';
        $sessionId = !empty($_POST['session_id']) ? $_POST['session_id'] : null;

        if (!empty($title) && !empty($content)) {
            $forumModel = new DX_LMS_M_Forum();
            $topicId = $forumModel->createTopic([
                'course_id' => $courseId,
                'session_id' => $sessionId,
                'category_type' => $category,
                'author_npk' => $user['npk'] ?? '67890',
                'author_name' => $user['fullname'] ?? ($user['name'] ?? 'Ahmad Rizal'),
                'author_role' => 'teacher',
                'author_avatar' => '',
                'author_dept' => ($user['jabatan'] ?? 'Leader') . ' · Production Engineering',
                'title' => $title,
                'content' => $content
            ]);

            header('Location: ?route=/teacher/forum/thread&id=' . $topicId);
            exit();
        }

        header('Location: ?route=/teacher/forum&course_id=' . urlencode($courseId));
        exit();
    }
}
