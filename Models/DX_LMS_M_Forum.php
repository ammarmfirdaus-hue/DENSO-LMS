<?php
/**
 * Kelas Model Forum Diskusi (Forum Model).
 *
 * Mengelola interaksi basis data untuk modul Forum Diskusi (DX_LMS_T_Forum_Topics & DX_LMS_T_Forum_Replies).
 * Mendukung akses multi-role (Student, Teacher, Admin) dengan enkapsulasi kueri SQL Server.
 */

require_once __DIR__ . '/../Core/DX_LMS_M_Model.php';
require_once __DIR__ . '/../includes/DX_LMS_M_Db.php';

class DX_LMS_M_Forum extends DX_LMS_M_Model
{
    protected $table = 'DX_LMS_T_Forum_Topics';
    protected $primaryKey = 'topic_id';

    /**
     * Mengambil daftar topik diskusi berdasarkan kriteria filter.
     *
     * @param string|int|null $courseId
     * @param array $filters (category_type, session_id, search, status)
     * @return array
     */
    public function getTopics($courseId = null, array $filters = [])
    {
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active') AS total_replies,
                       (SELECT TOP 1 r.author_name FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active' ORDER BY r.created_at DESC) AS latest_replier,
                       (SELECT TOP 1 r.created_at FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active' ORDER BY r.created_at DESC) AS latest_reply_at
                FROM DX_LMS_T_Forum_Topics t
                WHERE t.moderation_status != 'hidden'";
        $params = [];

        if ($courseId !== null && $courseId !== '' && $courseId !== 'all') {
            $sql .= " AND t.course_id = ?";
            $params[] = (string) $courseId;
        }

        if (!empty($filters['category_type']) && $filters['category_type'] !== 'all') {
            $sql .= " AND t.category_type = ?";
            $params[] = $filters['category_type'];
        }

        if (!empty($filters['session_id']) && $filters['session_id'] !== 'all') {
            $sql .= " AND t.session_id = ?";
            $params[] = (string) $filters['session_id'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.content LIKE ? OR t.author_name LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql .= " ORDER BY t.is_pinned DESC, t.updated_at DESC";

        $rows = dbQuery($sql, $params);
        foreach ($rows as &$row) {
            $row['time_formatted'] = self::formatTimeAgo($row['created_at']);
            $row['updated_formatted'] = self::formatTimeAgo($row['updated_at']);
        }
        return $rows;
    }

    /**
     * Mengambil satu data topik berdasarkan ID.
     *
     * @param int|string $topicId
     * @return array|false
     */
    public function getTopic($topicId)
    {
        $sql = "SELECT t.*,
                       (SELECT COUNT(*) FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active') AS total_replies
                FROM DX_LMS_T_Forum_Topics t
                WHERE t.topic_id = ?";
        $topic = dbQueryOne($sql, [(int) $topicId]);
        if ($topic) {
            $topic['time_formatted'] = self::formatTimeAgo($topic['created_at']);
            $topic['updated_formatted'] = self::formatTimeAgo($topic['updated_at']);
        }
        return $topic;
    }

    /**
     * Mengambil seluruh balasan untuk suatu topik.
     *
     * @param int|string $topicId
     * @return array
     */
    public function getReplies($topicId)
    {
        $sql = "SELECT r.*
                FROM DX_LMS_T_Forum_Replies r
                WHERE r.topic_id = ? AND r.status = 'active'
                ORDER BY r.is_solution DESC, r.created_at ASC";
        $replies = dbQuery($sql, [(int) $topicId]);
        foreach ($replies as &$reply) {
            $reply['time_formatted'] = self::formatTimeAgo($reply['created_at']);
        }
        return $replies;
    }

    /**
     * Membuat topik diskusi baru.
     *
     * @param array $data
     * @return int Topic ID
     */
    public function createTopic(array $data)
    {
        $sql = "INSERT INTO DX_LMS_T_Forum_Topics 
                (course_id, session_id, category_type, author_npk, author_name, author_role, author_avatar, author_dept, 
                 title, content, attachment_path, status, moderation_status, created_at, updated_at, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open', 'approved', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?)";

        $courseId = (string) ($data['course_id'] ?? '1');
        $sessionId = !empty($data['session_id']) ? (string) $data['session_id'] : null;
        $categoryType = $data['category_type'] ?? 'materi';
        $authorNpk = $data['author_npk'] ?? '00000';
        $authorName = $data['author_name'] ?? 'User';
        $authorRole = $data['author_role'] ?? 'student';
        $authorAvatar = $data['author_avatar'] ?? '';
        $authorDept = $data['author_dept'] ?? '';
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $attachment = $data['attachment_path'] ?? null;

        dbExecute($sql, [
            $courseId,
            $sessionId,
            $categoryType,
            $authorNpk,
            $authorName,
            $authorRole,
            $authorAvatar,
            $authorDept,
            $title,
            $content,
            $attachment,
            $authorNpk
        ]);

        $inserted = dbQueryOne("SELECT TOP 1 topic_id FROM DX_LMS_T_Forum_Topics ORDER BY topic_id DESC");
        return (int) ($inserted['topic_id'] ?? 0);
    }

    /**
     * Membuat balasan baru pada topik.
     *
     * @param array $data
     * @return int Reply ID
     */
    public function createReply(array $data)
    {
        $topicId = (int) ($data['topic_id'] ?? 0);
        $parentReplyId = !empty($data['parent_reply_id']) ? (int) $data['parent_reply_id'] : null;
        $authorNpk = $data['author_npk'] ?? '00000';
        $authorName = $data['author_name'] ?? 'User';
        $authorRole = $data['author_role'] ?? 'student';
        $authorAvatar = $data['author_avatar'] ?? '';
        $authorDept = $data['author_dept'] ?? '';
        $content = trim($data['content'] ?? '');
        $attachment = $data['attachment_path'] ?? null;

        $sql = "INSERT INTO DX_LMS_T_Forum_Replies
                (topic_id, parent_reply_id, author_npk, author_name, author_role, author_avatar, author_dept, content, attachment_path, status, created_at, updated_at, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?)";

        dbExecute($sql, [
            $topicId,
            $parentReplyId,
            $authorNpk,
            $authorName,
            $authorRole,
            $authorAvatar,
            $authorDept,
            $content,
            $attachment,
            $authorNpk
        ]);

        // Update topic reply count & updated_at
        dbExecute(
            "UPDATE DX_LMS_T_Forum_Topics 
             SET replies_count = replies_count + 1, updated_at = CURRENT_TIMESTAMP 
             WHERE topic_id = ?",
            [$topicId]
        );

        $inserted = dbQueryOne("SELECT TOP 1 reply_id FROM DX_LMS_T_Forum_Replies ORDER BY reply_id DESC");
        return (int) ($inserted['reply_id'] ?? 0);
    }

    /**
     * Menandai balasan sebagai solusi (Hanya Teacher atau Admin).
     *
     * @param int $replyId
     * @param int $topicId
     * @return bool
     */
    public function toggleSolution($replyId, $topicId)
    {
        $reply = dbQueryOne("SELECT is_solution FROM DX_LMS_T_Forum_Replies WHERE reply_id = ?", [(int) $replyId]);
        if (!$reply) return false;

        $newVal = $reply['is_solution'] ? 0 : 1;

        // Reset other solutions if setting to 1
        if ($newVal === 1) {
            dbExecute("UPDATE DX_LMS_T_Forum_Replies SET is_solution = 0 WHERE topic_id = ?", [(int) $topicId]);
        }

        dbExecute("UPDATE DX_LMS_T_Forum_Replies SET is_solution = ? WHERE reply_id = ?", [$newVal, (int) $replyId]);

        // Update topic status
        $topicStatus = ($newVal === 1) ? 'resolved' : 'open';
        dbExecute("UPDATE DX_LMS_T_Forum_Topics SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE topic_id = ?", [$topicStatus, (int) $topicId]);

        return true;
    }

    /**
     * Toggle Like pada topik atau balasan.
     *
     * @param int|null $topicId
     * @param int|null $replyId
     * @param string $employeeNpk
     * @return array ['liked' => bool, 'likes_count' => int]
     */
    public function toggleLike($topicId, $replyId, $employeeNpk)
    {
        if ($replyId) {
            $existing = dbQueryOne("SELECT like_id FROM DX_LMS_T_Forum_Likes WHERE reply_id = ? AND employee_npk = ?", [(int) $replyId, $employeeNpk]);
            if ($existing) {
                dbExecute("DELETE FROM DX_LMS_T_Forum_Likes WHERE like_id = ?", [(int) $existing['like_id']]);
                dbExecute("UPDATE DX_LMS_T_Forum_Replies SET likes_count = CASE WHEN likes_count > 0 THEN likes_count - 1 ELSE 0 END WHERE reply_id = ?", [(int) $replyId]);
                $liked = false;
            } else {
                dbExecute("INSERT INTO DX_LMS_T_Forum_Likes (reply_id, employee_npk, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)", [(int) $replyId, $employeeNpk]);
                dbExecute("UPDATE DX_LMS_T_Forum_Replies SET likes_count = likes_count + 1 WHERE reply_id = ?", [(int) $replyId]);
                $liked = true;
            }
            $row = dbQueryOne("SELECT likes_count FROM DX_LMS_T_Forum_Replies WHERE reply_id = ?", [(int) $replyId]);
            return ['liked' => $liked, 'likes_count' => (int) ($row['likes_count'] ?? 0)];
        } else {
            $existing = dbQueryOne("SELECT like_id FROM DX_LMS_T_Forum_Likes WHERE topic_id = ? AND employee_npk = ?", [(int) $topicId, $employeeNpk]);
            if ($existing) {
                dbExecute("DELETE FROM DX_LMS_T_Forum_Likes WHERE like_id = ?", [(int) $existing['like_id']]);
                dbExecute("UPDATE DX_LMS_T_Forum_Topics SET likes_count = CASE WHEN likes_count > 0 THEN likes_count - 1 ELSE 0 END WHERE topic_id = ?", [(int) $topicId]);
                $liked = false;
            } else {
                dbExecute("INSERT INTO DX_LMS_T_Forum_Likes (topic_id, employee_npk, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)", [(int) $topicId, $employeeNpk]);
                dbExecute("UPDATE DX_LMS_T_Forum_Topics SET likes_count = likes_count + 1 WHERE topic_id = ?", [(int) $topicId]);
                $liked = true;
            }
            $row = dbQueryOne("SELECT likes_count FROM DX_LMS_T_Forum_Topics WHERE topic_id = ?", [(int) $topicId]);
            return ['liked' => $liked, 'likes_count' => (int) ($row['likes_count'] ?? 0)];
        }
    }

    /**
     * Cek apakah user telah me-like topik atau reply.
     */
    public function hasUserLiked($topicId, $replyId, $employeeNpk)
    {
        if ($replyId) {
            $res = dbQueryOne("SELECT 1 FROM DX_LMS_T_Forum_Likes WHERE reply_id = ? AND employee_npk = ?", [(int) $replyId, $employeeNpk]);
        } else {
            $res = dbQueryOne("SELECT 1 FROM DX_LMS_T_Forum_Likes WHERE topic_id = ? AND employee_npk = ?", [(int) $topicId, $employeeNpk]);
        }
        return (bool) $res;
    }

    /**
     * Memformat waktu relatif dalam bahasa Indonesia.
     *
     * @param string|DateTime $datetime
     * @return string
     */
    public static function formatTimeAgo($datetime)
    {
        if (!$datetime) return '-';
        $time = is_numeric($datetime) ? (int) $datetime : strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $min = max(1, floor($diff / 60));
            return $min . ' menit lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari lalu';
        } else {
            return date('d M Y, H:i', $time) . ' WIB';
        }
    }
}
