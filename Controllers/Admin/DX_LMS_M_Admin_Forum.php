<?php
/**
 * Kelas Pengontrol Forum Admin (Admin Forum & Community Moderation Controller).
 *
 * Mengelola interaksi, pengawasan, dan partisipasi Administrator pada Forum LMS:
 * - Menampilkan daftar topik diskusi lintas course dengan filter
 * - Memberikan fasilitas moderasi dan tanggapan resmi dari Admin
 * - Menandai solusi terverifikasi resmi bersama Pengajar
 * - Berpartisipasi dalam diskusi thread (terintegrasi dengan Student & Teacher)
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Models/DX_LMS_M_Forum.php';
require_once 'includes/DX_LMS_M_Teacher_MockData.php';

class DX_LMS_M_Admin_Forum extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Dasbor Forum & Moderasi untuk Admin.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Pastikan sesi admin aktif atau sediakan fallback admin
        $user = $_SESSION['user'] ?? [
            'npk' => '99999',
            'name' => 'Admin System',
            'fullname' => 'Admin System',
            'role' => 'admin',
            'jabatan' => 'Super Admin',
            'department' => 'HR & People Development'
        ];

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

        // Course aktif
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
            'user' => $user
        ];

        $this->view('Admin/DX_LMS_M_Admin_Forum', $data);
    }

    /**
     * Menampilkan Detail Thread Diskusi untuk Admin.
     */
    public function thread()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $topicId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
        $forumModel = new DX_LMS_M_Forum();
        $topic = $forumModel->getTopic($topicId);

        if (!$topic) {
            header('Location: ?route=/admin/forum');
            exit();
        }

        $replies = $forumModel->getReplies($topicId);
        $user = $_SESSION['user'] ?? [
            'npk' => '99999',
            'name' => 'Admin System',
            'fullname' => 'Admin System',
            'role' => 'admin',
            'jabatan' => 'Super Admin',
            'department' => 'HR & People Development'
        ];

        $backUrl = '?route=/admin/forum&course_id=' . urlencode($topic['course_id'] ?? '1');

        $this->view('Student/DX_LMS_T_Student_Forum_Thread', [
            'topic' => $topic,
            'replies' => $replies,
            'isRiwayat' => false,
            'user' => $user,
            'userRole' => 'admin',
            'backUrl' => $backUrl
        ]);
    }

    /**
     * Memproses Pengiriman Balasan / Tanggapan Resmi Admin (AJAX).
     */
    public function reply()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $user = $_SESSION['user'] ?? [
            'npk' => '99999',
            'name' => 'Admin System',
            'fullname' => 'Admin System',
            'role' => 'admin',
            'jabatan' => 'Super Admin',
            'department' => 'HR & People Development'
        ];

        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($topicId <= 0 || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Pesan tanggapan tidak boleh kosong.']);
            exit();
        }

        $forumModel = new DX_LMS_M_Forum();
        $authorDept = 'Super Admin · HR & People Development';
        $replyId = $forumModel->createReply([
            'topic_id' => $topicId,
            'author_npk' => $user['npk'] ?? '99999',
            'author_name' => $user['fullname'] ?? ($user['name'] ?? 'Admin System'),
            'author_role' => 'admin',
            'author_avatar' => '',
            'author_dept' => $authorDept,
            'content' => $content
        ]);

        echo json_encode([
            'success' => true,
            'reply_id' => $replyId,
            'message' => 'Tanggapan Resmi Admin berhasil diposting!'
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
     * Toggle Like pada Topik atau Tanggapan via AJAX.
     */
    public function toggleLike()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $user = $_SESSION['user'] ?? [
            'npk' => '99999',
            'name' => 'Admin System'
        ];

        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : null;
        $replyId = isset($_POST['reply_id']) ? (int) $_POST['reply_id'] : null;
        $npk = $user['npk'] ?? '99999';

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
     * Membuat Pengumuman Resmi / Topik Baru oleh Admin.
     */
    public function newTopic()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? [
            'npk' => '99999',
            'name' => 'Admin System',
            'fullname' => 'Admin System'
        ];

        $courseId = $_POST['course_id'] ?? '1';
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = $_POST['category_type'] ?? 'umum';
        $sessionId = !empty($_POST['session_id']) ? $_POST['session_id'] : null;

        if (!empty($title) && !empty($content)) {
            $forumModel = new DX_LMS_M_Forum();
            $topicId = $forumModel->createTopic([
                'course_id' => $courseId,
                'session_id' => $sessionId,
                'category_type' => $category,
                'author_npk' => $user['npk'] ?? '99999',
                'author_name' => $user['fullname'] ?? ($user['name'] ?? 'Admin System'),
                'author_role' => 'admin',
                'author_avatar' => '',
                'author_dept' => 'Super Admin · People Development',
                'title' => $title,
                'content' => $content
            ]);

            header('Location: ?route=/admin/forum/thread&id=' . $topicId);
            exit();
        }

        header('Location: ?route=/admin/forum&course_id=' . urlencode($courseId));
        exit();
    }
}
