<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';

/**
 * Mengambil daftar katalog kursus aktif beserta progres enrollment student dari MySQL.
 *
 * @param int $studentId ID unik student.
 * @return array<array<string, mixed>> Daftar kursus aktif.
 */
function apiCourses(int $studentId): array
{
    $query = db()->prepare("SELECT c.course_id, c.course_code, c.title, c.description, c.category,
      c.thumbnail_path, COALESCE(e.progress, 0) AS progress, COALESCE(e.enrollment_status, 'available') AS enrollment_status
      FROM courses c LEFT JOIN course_enrollments e ON e.course_id=c.course_id AND e.student_id=:student_id
      WHERE c.status='active' ORDER BY c.updated_at DESC");
    $query->execute(['student_id' => $studentId]);
    return $query->fetchAll();
}

/**
 * Mengambil ringkasan statistik pembelajaran student (total kursus, kursus aktif, selesai, rata-rata progres).
 *
 * @param int $studentId ID unik student.
 * @return array<string, mixed> Statistik pembelajaran student.
 */
function apiDashboard(int $studentId): array
{
    $query = db()->prepare("SELECT COUNT(*) AS total_courses,
      SUM(CASE WHEN e.enrollment_status='active' THEN 1 ELSE 0 END) AS active_courses,
      SUM(CASE WHEN e.enrollment_status='completed' THEN 1 ELSE 0 END) AS completed_courses,
      COALESCE(AVG(CASE WHEN e.enrollment_status='completed' THEN e.progress END),0) AS average_progress
      FROM course_enrollments e WHERE e.student_id=:student_id");
    $query->execute(['student_id' => $studentId]);
    return $query->fetch() ?: ['total_courses' => 0, 'active_courses' => 0, 'completed_courses' => 0, 'average_progress' => 0];
}

/**
 * Mengambil detail kursus terbit lengkap beserta sesi dan materi terbit untuk student yang berhak.
 *
 * @param string $courseCode Kode unik kursus.
 * @param int $studentId ID unik student.
 * @return array<string, mixed>|null Data detail kursus atau null jika tidak ditemukan/tidak aktif.
 */
function publishedStudentCourse(string $courseCode, int $studentId): ?array
{
    if ($studentId < 1) return null;
    $query = db()->prepare("SELECT c.course_id,c.course_code,c.title,c.description,c.category,
      COALESCE(e.progress,0) AS progress,COALESCE(e.enrollment_status,'available') AS enrollment_status
      FROM courses c LEFT JOIN course_enrollments e ON e.course_id=c.course_id AND e.student_id=:student_id
      WHERE c.course_code=:code AND c.status='active' AND (e.enrollment_status IS NULL OR e.enrollment_status IN ('active','completed')) LIMIT 1");
    $query->execute(['code' => $courseCode, 'student_id' => $studentId]);
    $row = $query->fetch();
    if (!$row) return null;

    // Fetch all active sessions and their materials together to avoid one query
    // for every session on a course detail page.
    $sessionQuery = db()->prepare("SELECT s.session_id,s.source_session_id,s.title AS session_title,s.sort_order AS session_sort,
        m.material_id,m.title AS material_title,m.material_type,m.content,m.file_path,m.sort_order AS material_sort
      FROM course_sessions s LEFT JOIN course_materials m ON m.session_id=s.session_id
      WHERE s.course_id=:course_id AND s.status='active'
      ORDER BY s.sort_order,s.session_id,m.sort_order,m.material_id");
    $sessionQuery->execute(['course_id' => $row['course_id']]);
    $sessionsById = [];
    foreach ($sessionQuery->fetchAll() as $item) {
        $sessionKey = (string) $item['session_id'];
        if (!isset($sessionsById[$sessionKey])) {
            $sessionsById[$sessionKey] = [
                'id' => (string) ($item['source_session_id'] ?: $item['session_id']),
                'title' => $item['session_title'], 'status' => 'sedang_dipelajari', 'materials' => [],
                'pre_test' => ['status' => 'terkunci', 'questions' => []],
                'post_test' => ['status' => 'terkunci', 'questions' => []],
            ];
        }
        if ($item['material_id'] === null) continue;
        $material = [
            'material_id' => $item['material_id'], 'title' => $item['material_title'],
            'material_type' => $item['material_type'], 'content' => $item['content'],
            'file_path' => $item['file_path'], 'sort_order' => $item['material_sort'],
        ];
            $type = $material['material_type'] === 'document' ? 'office' : $material['material_type'];
            $content = (string) ($material['content'] ?? '');
            $displayUrl = $content;
            if ($type === 'video') {
                $parts = parse_url($content);
                $host = strtolower((string) ($parts['host'] ?? ''));
                $videoId = '';
                if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
                    $videoId = trim((string) ($parts['path'] ?? ''), '/');
                } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
                    parse_str((string) ($parts['query'] ?? ''), $queryParams);
                    $videoId = (string) ($queryParams['v'] ?? '');
                    if ($videoId === '' && preg_match('~^/embed/([A-Za-z0-9_-]+)~', (string) ($parts['path'] ?? ''), $matches)) $videoId = $matches[1];
                }
                if (preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId)) $displayUrl = 'https://www.youtube-nocookie.com/embed/' . $videoId;
            }
            $sessionsById[$sessionKey]['materials'][] = [
                'id' => (string) $material['material_id'], 'title' => $material['title'], 'type' => $type,
                'content' => $content, 'file_url' => (string) ($material['file_path'] ?? ''),
                'file_path' => (string) ($material['file_path'] ?? ''), 'filename' => $material['title'],
                'url' => in_array($type, ['video', 'link'], true) ? $displayUrl : '',
                'status' => 'belum_dimulai', 'duration' => $type === 'pdf' || $type === 'office' ? 'Dokumen' : 'Materi',
                'content_blocks' => $type === 'text' ? [['type' => 'text', 'content' => $content]] : ($type === 'link' ? [['type' => 'external_link', 'url' => $displayUrl, 'title' => 'Buka tautan materi']] : []),
            ];
    }
    $sessions = array_values($sessionsById);
    $progress = (int) $row['progress'];
    $status = $row['enrollment_status'] === 'completed' ? 'selesai' : ($progress > 0 ? 'sedang_dipelajari' : 'belum_dimulai');
    return [
        'id' => $row['course_code'], 'title' => $row['title'], 'description' => $row['description'] ?? '',
        'thumbnail' => null, 'thumbnail_icon' => 'school', 'category' => $row['category'],
        'filters' => [$row['category']], 'modules_count' => count($sessions), 'status' => $status,
        'progress' => $progress, 'last_accessed' => date('Y-m-d H:i:s'), 'sessions' => $sessions,
        'quiz' => ['status' => 'terkunci', 'history' => []], 'final_assessment' => ['status' => 'terkunci', 'history' => []],
        'forums' => ['materi' => [], 'quiz' => ['threads' => []], 'final_assessment' => ['threads' => []]],
    ];
}

/**
 * Mengambil seluruh daftar kursus terbit lengkap beserta sesi dan materinya untuk student tertentu.
 *
 * @param int $studentId ID unik student.
 * @return array<array<string, mixed>> Daftar objek kursus terbit lengkap.
 */
function publishedStudentCourses(int $studentId): array
{
    if ($studentId < 1) return [];
    $query = db()->query("SELECT course_code FROM courses WHERE status='active' ORDER BY updated_at DESC");
    $courses = [];
    foreach ($query->fetchAll() as $row) {
        $course = publishedStudentCourse((string) $row['course_code'], $studentId);
        if ($course) $courses[] = $course;
    }
    return $courses;
}
