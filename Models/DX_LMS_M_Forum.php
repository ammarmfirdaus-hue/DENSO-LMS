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
                       e.fullname AS author_name,
                       e.npk AS author_npk,
                       e.role AS author_role,
                       '' AS author_avatar,
                       COALESCE(dept.department_name, '') AS author_dept,
                       (SELECT COUNT(*) FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active') AS total_replies,
                       (SELECT TOP 1 re.fullname FROM DX_LMS_T_Forum_Replies r JOIN DX_LMS_M_Employees re ON re.employee_id = r.employee_id WHERE r.topic_id = t.topic_id AND r.status = 'active' ORDER BY r.created_at DESC) AS latest_replier,
                       (SELECT TOP 1 r.created_at FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active' ORDER BY r.created_at DESC) AS latest_reply_at
                FROM DX_LMS_T_Forum_Topics t
                LEFT JOIN DX_LMS_M_Employees e ON e.employee_id = t.employee_id
                LEFT JOIN DX_LMS_M_Departments dept ON dept.department_id = e.department_id
                WHERE t.moderation_status != 'hidden'";
        $params = [];

        if ($courseId !== null && $courseId !== '' && $courseId !== 'all') {
            if (!is_numeric($courseId)) {
                $cRow = dbQueryOne("SELECT course_id FROM DX_LMS_M_Courses WHERE course_code = ?", [(string) $courseId]);
                $resolvedCourseId = $cRow ? (int) $cRow['course_id'] : null;
            } else {
                $resolvedCourseId = (int) $courseId;
            }

            if ($resolvedCourseId !== null) {
                $sql .= " AND t.course_id = ?";
                $params[] = $resolvedCourseId;
            } else {
                // Course code not in DB, return empty result safely
                $sql .= " AND 1 = 0";
            }
        }

        if (!empty($filters['category_type']) && $filters['category_type'] !== 'all') {
            $sql .= " AND t.category_type = ?";
            $params[] = $filters['category_type'];
        }

        if (!empty($filters['session_id']) && $filters['session_id'] !== 'all') {
            if (is_numeric($filters['session_id'])) {
                $sql .= " AND t.session_id = ?";
                $params[] = (int) $filters['session_id'];
            }
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.content LIKE ? OR e.fullname LIKE ? OR e.npk LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
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
                       e.fullname AS author_name,
                       e.npk AS author_npk,
                       e.role AS author_role,
                       '' AS author_avatar,
                       COALESCE(dept.department_name, '') AS author_dept,
                       (SELECT COUNT(*) FROM DX_LMS_T_Forum_Replies r WHERE r.topic_id = t.topic_id AND r.status = 'active') AS total_replies
                FROM DX_LMS_T_Forum_Topics t
                LEFT JOIN DX_LMS_M_Employees e ON e.employee_id = t.employee_id
                LEFT JOIN DX_LMS_M_Departments dept ON dept.department_id = e.department_id
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
        $sql = "SELECT r.*,
                       e.fullname AS author_name,
                       e.npk AS author_npk,
                       e.role AS author_role,
                       '' AS author_avatar,
                       COALESCE(dept.department_name, '') AS author_dept
                FROM DX_LMS_T_Forum_Replies r
                LEFT JOIN DX_LMS_M_Employees e ON e.employee_id = r.employee_id
                LEFT JOIN DX_LMS_M_Departments dept ON dept.department_id = e.department_id
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
        $authorNpk = $data['author_npk'] ?? '00000';
        $employeeId = $data['employee_id'] ?? null;
        if (!$employeeId) {
            $emp = dbQueryOne("SELECT employee_id FROM DX_LMS_M_Employees WHERE npk = ?", [$authorNpk]);
            $employeeId = $emp['employee_id'] ?? 1;
        }

        $courseId = !empty($data['course_id']) ? (int) $data['course_id'] : 1;
        $sessionId = !empty($data['session_id']) ? (int) $data['session_id'] : null;
        $categoryType = $data['category_type'] ?? 'materi';
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $attachment = $data['attachment_path'] ?? null;

        $sql = "INSERT INTO DX_LMS_T_Forum_Topics 
                (course_id, session_id, employee_id, category_type, title, content, attachment_path, status, moderation_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'open', 'approved', GETDATE(), GETDATE())";

        dbExecute($sql, [
            $courseId,
            $sessionId,
            $employeeId,
            $categoryType,
            $title,
            $content,
            $attachment
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
        $employeeId = $data['employee_id'] ?? null;
        if (!$employeeId) {
            $emp = dbQueryOne("SELECT employee_id FROM DX_LMS_M_Employees WHERE npk = ?", [$authorNpk]);
            $employeeId = $emp['employee_id'] ?? 1;
        }
        $content = trim($data['content'] ?? '');
        $attachment = $data['attachment_path'] ?? null;

        $sql = "INSERT INTO DX_LMS_T_Forum_Replies
                (topic_id, parent_reply_id, employee_id, content, attachment_path, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'active', GETDATE(), GETDATE())";

        dbExecute($sql, [
            $topicId,
            $parentReplyId,
            $employeeId,
            $content,
            $attachment
        ]);

        // Update topic reply count & updated_at
        dbExecute(
            "UPDATE DX_LMS_T_Forum_Topics 
             SET replies_count = replies_count + 1, updated_at = GETDATE() 
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
            dbExecute("UPDATE DX_LMS_T_Forum_Replies SET likes_count = likes_count + 1 WHERE reply_id = ?", [(int) $replyId]);
            $row = dbQueryOne("SELECT likes_count FROM DX_LMS_T_Forum_Replies WHERE reply_id = ?", [(int) $replyId]);
            return ['liked' => true, 'likes_count' => (int) ($row['likes_count'] ?? 0)];
        } else {
            dbExecute("UPDATE DX_LMS_T_Forum_Topics SET likes_count = likes_count + 1 WHERE topic_id = ?", [(int) $topicId]);
            $row = dbQueryOne("SELECT likes_count FROM DX_LMS_T_Forum_Topics WHERE topic_id = ?", [(int) $topicId]);
            return ['liked' => true, 'likes_count' => (int) ($row['likes_count'] ?? 0)];
        }
    }

    /**
     * Cek apakah user telah me-like topik atau reply.
     */
    public function hasUserLiked($topicId, $replyId, $employeeNpk)
    {
        return false;
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
