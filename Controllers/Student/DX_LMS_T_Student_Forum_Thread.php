<?php
/**
 * Kelas Pengontrol Rincian Thread Forum (Forum Thread Detail Controller).
 *
 * Bertanggung jawab menyajikan isi diskusi, tanggapan (replies), serta
 * memproses pengiriman balasan, suka (like), dan solusi.
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Models/DX_LMS_M_Forum.php';

class DX_LMS_T_Student_Forum_Thread extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Rincian Topik Diskusi.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $topicId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
        $isRiwayat = isset($_GET['is_riwayat']) && $_GET['is_riwayat'] === 'true';

        $forumModel = new DX_LMS_M_Forum();
        $topic = $forumModel->getTopic($topicId);

        // Fallback jika ID topik tidak ditemukan
        if (!$topic) {
            $allTopics = $forumModel->getTopics('1');
            if (!empty($allTopics)) {
                $topic = $allTopics[0];
                $topicId = $topic['topic_id'];
            }
        }

        $replies = $topic ? $forumModel->getReplies($topicId) : [];

        $user = getPortalUser('student') ?? [
            'npk' => '12345',
            'name' => 'Budi Santoso',
            'role' => 'student',
            'jabatan' => 'Operator',
            'department' => 'Manufacturing'
        ];

        $backUrl = '?route=/student/course/detail&id=' . urlencode($topic['course_id'] ?? '1') . '&tab=forum';

        $this->view('DX_LMS_T_Student_Forum_Thread', [
            'topic' => $topic,
            'replies' => $replies,
            'isRiwayat' => $isRiwayat,
            'user' => $user,
            'userRole' => 'student',
            'backUrl' => $backUrl
        ]);
    }

    /**
     * Memproses Pengiriman Balasan via AJAX / Form POST.
     */
    public function reply()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $user = getPortalUser('student');
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
            exit();
        }

        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($topicId <= 0 || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Pesan balasan tidak boleh kosong.']);
            exit();
        }

        $forumModel = new DX_LMS_M_Forum();
        $authorDept = ($user['jabatan'] ?? 'Staff') . ' · ' . ($user['department'] ?? 'Manufacturing');
        $replyId = $forumModel->createReply([
            'topic_id' => $topicId,
            'author_npk' => $user['npk'] ?? '12345',
            'author_name' => $user['name'] ?? ($user['fullname'] ?? 'Student'),
            'author_role' => 'student',
            'author_avatar' => '',
            'author_dept' => $authorDept,
            'content' => $content
        ]);

        echo json_encode([
            'success' => true,
            'reply_id' => $replyId,
            'message' => 'Balasan berhasil dikirim!',
            'reply' => [
                'reply_id' => $replyId,
                'author_name' => $user['name'] ?? ($user['fullname'] ?? 'Student'),
                'author_role' => 'student',
                'author_dept' => $authorDept,
                'content' => $content,
                'time_formatted' => 'Baru saja',
                'likes_count' => 0,
                'is_solution' => 0
            ]
        ]);
        exit();
    }

    /**
     * Toggle Like pada Topik atau Balasan via AJAX.
     */
    public function toggleLike()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $user = getPortalUser('student');
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
            exit();
        }

        $topicId = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : null;
        $replyId = isset($_POST['reply_id']) ? (int) $_POST['reply_id'] : null;
        $npk = $user['npk'] ?? '12345';

        $forumModel = new DX_LMS_M_Forum();
        $result = $forumModel->toggleLike($topicId, $replyId, $npk);

        echo json_encode([
            'success' => true,
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count']
        ]);
        exit();
    }
}
