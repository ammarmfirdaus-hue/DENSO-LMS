<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';

/**
 * Mengubah nilai thumbnail_path course dari MySQL menjadi URL gambar lengkap yang dapat dimuat browser.
 */
function resolveCourseThumbnailUrl(?string $thumbnailPath): ?string
{
    $value = trim((string) $thumbnailPath);
    if ($value === '') return null;
    if (preg_match('/^https?:\/\//i', $value) === 1) {
        return $value;
    }
    $apiBase = defined('SSO_API_URL') ? rtrim((string) SSO_API_URL, '/') : '';
    if ($apiBase === '') return null;
    return $apiBase . (str_starts_with($value, '/') ? $value : '/' . $value);
}

/**
 * Mengambil daftar katalog kursus aktif beserta progres enrollment student dari MySQL.
 *
 * @param int $studentId ID unik student.
 * @return array<array<string, mixed>> Daftar kursus aktif.
 */
function apiCourses(int $studentId): array
{
    if ($studentId < 1) return [];
    $query = db()->prepare("SELECT c.course_id,c.course_code,c.title,c.description,c.category,c.thumbnail_path,
      COALESCE(e.progress,0) AS progress,COALESCE(e.enrollment_status,'available') AS enrollment_status,
      CASE WHEN e.enrollment_status IN ('active','completed') THEN 1 ELSE 0 END AS has_access,
      access_request.status AS access_status,access_request.request_type,access_request.request_note,access_request.decision_note
      FROM courses c
      JOIN student_accounts account ON account.student_id=:account_student_id AND account.status='active'
      LEFT JOIN course_enrollments e ON e.course_id=c.course_id AND e.student_id=:enrollment_student_id
      LEFT JOIN course_enrollment_requests access_request ON access_request.request_id=(
        SELECT MAX(request_id) FROM course_enrollment_requests WHERE student_id=:request_student_id AND course_id=c.course_id
      )
      WHERE c.status='active' ORDER BY c.updated_at DESC");
    $query->execute(['account_student_id' => $studentId, 'enrollment_student_id' => $studentId, 'request_student_id' => $studentId]);
    return array_map(static function (array $row): array {
        $resolved = resolveCourseThumbnailUrl($row['thumbnail_path'] ?? null);
        $row['thumbnail_path'] = $resolved;
        $row['thumbnail'] = $resolved;
        return $row;
    }, $query->fetchAll());
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
 * Nilai course milik student aktif saja. Identitas diambil dari sesi SSO yang tervalidasi.
 * @param int $studentId ID akun student.
 * @return array<array<string, mixed>>
 */
function apiStudentGrades(int $studentId): array
{
    if ($studentId < 1) return [];
    $query = db()->prepare("SELECT c.course_code,c.title AS course_title,c.category,
        e.score,e.quiz_score,e.grade_feedback,e.graded_at,e.progress,e.enrollment_status
      FROM course_enrollments e JOIN courses c ON c.course_id=e.course_id
      WHERE e.student_id=:student_id
      ORDER BY e.graded_at DESC,c.title ASC");
    $query->execute(['student_id' => $studentId]);
    return $query->fetchAll();
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
    $query = db()->prepare("SELECT c.course_id,c.course_code,c.title,c.description,c.category,c.thumbnail_path,
      COALESCE(e.progress,0) AS progress,COALESCE(e.enrollment_status,'available') AS enrollment_status,e.quiz_score,
      CASE WHEN e.enrollment_status IN ('active','completed') THEN 1 ELSE 0 END AS has_access,
      access_request.status AS access_status,access_request.request_type,access_request.request_note,access_request.decision_note
      FROM courses c
      JOIN student_accounts account ON account.student_id=:account_student_id AND account.status='active'
      LEFT JOIN course_enrollments e ON e.course_id=c.course_id AND e.student_id=:enrollment_student_id
      LEFT JOIN course_enrollment_requests access_request ON access_request.request_id=(
        SELECT MAX(request_id) FROM course_enrollment_requests WHERE student_id=:request_student_id AND course_id=c.course_id
      )
      WHERE c.course_code=:code AND c.status='active' LIMIT 1");
    $query->execute(['account_student_id' => $studentId, 'enrollment_student_id' => $studentId, 'request_student_id' => $studentId, 'code' => $courseCode]);
    $row = $query->fetch();
    if (!$row) return null;

    $thumbnailUrl = resolveCourseThumbnailUrl($row['thumbnail_path'] ?? null);
    $accessStatus = (string) ($row['access_status'] ?? 'not_requested');
    if ((int) $row['has_access'] !== 1) {
        return [
            'id' => $row['course_code'], 'title' => $row['title'], 'description' => $row['description'] ?? '',
            'thumbnail' => $thumbnailUrl, 'thumbnail_icon' => 'school', 'category' => $row['category'],
            'filters' => [$row['category']], 'modules_count' => 0, 'status' => 'belum_dimulai',
            'progress' => 0, 'last_accessed' => null, 'sessions' => [], 'is_live' => true,
            'has_access' => false, 'access_status' => $accessStatus,
            'request_type' => $row['request_type'] ?? null, 'request_note' => $row['request_note'] ?? null,
            'decision_note' => $row['decision_note'] ?? null, 'enrollment_status' => $row['enrollment_status'],
            'quiz' => ['has_quiz' => false, 'status' => 'terkunci', 'questions' => [], 'question_count' => 0, 'score' => null, 'attempt' => null],
            'quiz_score' => null, 'final_assessment' => ['status' => 'terkunci', 'history' => []],
            'forums' => ['materi' => [], 'quiz' => ['threads' => []], 'final_assessment' => ['threads' => []]],
        ];
    }

    // Fetch all active sessions and their materials together to avoid one query
    // for every session on a course detail page.
    $sessionQuery = db()->prepare("SELECT s.session_id,s.source_session_id,s.title AS session_title,s.sort_order AS session_sort,
        m.material_id,m.title AS material_title,m.material_type,m.content,m.file_path,m.sort_order AS material_sort,
        mp.progress_status AS material_progress_status
      FROM course_sessions s LEFT JOIN course_materials m ON m.session_id=s.session_id AND m.is_active=1
      LEFT JOIN material_progress mp ON mp.material_id=m.material_id AND mp.student_id=:student_id
      WHERE s.course_id=:course_id AND s.status='active'
      ORDER BY s.sort_order,s.session_id,m.sort_order,m.material_id");
    $sessionQuery->execute(['student_id' => $studentId, 'course_id' => $row['course_id']]);
    $sessionsById = [];
    foreach ($sessionQuery->fetchAll() as $item) {
        $sessionKey = (string) $item['session_id'];
        if (!isset($sessionsById[$sessionKey])) {
            $sessionsById[$sessionKey] = [
                'id' => (string) ($item['source_session_id'] ?: $item['session_id']),
                'title' => $item['session_title'], 'status' => 'sedang_dipelajari', 'materials' => [],
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
                'status' => ($item['material_progress_status'] ?? '') === 'completed' ? 'selesai' : (($item['material_progress_status'] ?? '') === 'in_progress' ? 'sedang_dipelajari' : 'belum_dimulai'),
                'material_id' => (int) $item['material_id'], 'duration' => $type === 'pdf' || $type === 'office' ? 'Dokumen' : 'Materi',
                'content_blocks' => $type === 'text' ? [['type' => 'text', 'content' => $content]] : ($type === 'link' ? [['type' => 'external_link', 'url' => $displayUrl, 'title' => 'Buka tautan materi']] : []),
            ];
    }
    $sessions = array_values($sessionsById);
    $courseMaterials = [];
    foreach ($sessions as &$courseSession) {
        $materialCount = count($courseSession['materials']);
        $completedCount = count(array_filter($courseSession['materials'], static fn($material) => $material['status'] === 'selesai'));
        $courseSession['status'] = $materialCount > 0 && $completedCount === $materialCount
            ? 'selesai'
            : ($completedCount > 0 ? 'sedang_dipelajari' : 'belum_dimulai');
        foreach ($courseSession['materials'] as $courseMaterial) $courseMaterials[] = $courseMaterial;
    }
    unset($courseSession);
    $completedMaterials = count(array_filter($courseMaterials, static fn($material) => $material['status'] === 'selesai'));
    $allMaterialsCompleted = count($courseMaterials) > 0 && $completedMaterials === count($courseMaterials);
    $progress = (int) $row['progress'];
    $status = $row['enrollment_status'] === 'completed' ? 'selesai' : ($progress > 0 ? 'sedang_dipelajari' : 'belum_dimulai');
    $quiz = apiStudentCourseQuizSummary((string) $row['course_code'], $studentId, $allMaterialsCompleted);
    return [
        'id' => $row['course_code'], 'title' => $row['title'], 'description' => $row['description'] ?? '',
        'thumbnail' => $thumbnailUrl, 'thumbnail_icon' => 'school', 'category' => $row['category'],
        'filters' => [$row['category']], 'modules_count' => count($sessions), 'status' => $status,
        'progress' => $progress, 'last_accessed' => date('Y-m-d H:i:s'), 'sessions' => $sessions, 'is_live' => true,
        'has_access' => true, 'access_status' => $accessStatus,
        'quiz' => $quiz, 'quiz_score' => $row['quiz_score'] === null ? null : (float) $row['quiz_score'],
        'final_assessment' => ['status' => 'terkunci', 'history' => []],
        'forums' => ['materi' => [], 'quiz' => ['threads' => []], 'final_assessment' => ['threads' => []]],
    ];
}

/** Ringkasan quiz course tanpa membocorkan kunci jawaban ke halaman Student. */
function apiStudentCourseQuizSummary(string $courseCode, int $studentId, bool $allMaterialsCompleted = false): array
{
    $quiz = ['has_quiz' => false, 'status' => 'terkunci', 'questions' => [], 'question_count' => 0, 'score' => null, 'attempt' => null];
    if ($studentId < 1) return $quiz;
    $query = db()->prepare("SELECT q.quiz_id,q.title,q.description,q.passing_score,q.status,
        (SELECT COUNT(*) FROM course_quiz_questions question WHERE question.quiz_id=q.quiz_id) AS question_count,
        quiz_attempt.attempt_id,quiz_attempt.score,quiz_attempt.correct_count,quiz_attempt.total_questions,quiz_attempt.submitted_at
      FROM course_quizzes q JOIN courses c ON c.course_id=q.course_id
      LEFT JOIN course_quiz_attempts quiz_attempt ON quiz_attempt.quiz_id=q.quiz_id AND quiz_attempt.student_id=:student_id
      WHERE c.course_code=:course_code LIMIT 1");
    $query->execute(['student_id' => $studentId, 'course_code' => $courseCode]);
    $row = $query->fetch();
    if (!$row) return $quiz;
    $quiz = [
        'has_quiz' => true, 'quiz_id' => (int) $row['quiz_id'], 'title' => $row['title'],
        'description' => $row['description'] ?? '', 'passing_score' => (float) $row['passing_score'],
        'question_count' => (int) $row['question_count'], 'questions' => [], 'score' => null, 'attempt' => null,
        'status' => 'terkunci',
    ];
    if ($row['attempt_id'] !== null) {
        $quiz['status'] = 'selesai';
        $quiz['score'] = (float) $row['score'];
        $quiz['attempt'] = [
            'attempt_id' => (int) $row['attempt_id'], 'score' => (float) $row['score'],
            'correct_count' => (int) $row['correct_count'], 'total_questions' => (int) $row['total_questions'],
            'submitted_at' => $row['submitted_at'],
        ];
    } elseif ($row['status'] === 'published' && (int) $row['question_count'] > 0 && $allMaterialsCompleted) {
        $quiz['status'] = 'belum_dimulai';
    }
    return $quiz;
}

/** Detail quiz yang sudah terbit, berisi opsi tanpa kolom jawaban benar. */
function apiStudentCourseQuizDetails(string $courseCode, int $studentId): ?array
{
    $courseQuery = db()->prepare("SELECT c.course_id FROM courses c
      JOIN student_accounts student ON student.student_id=:student_id AND student.status='active'
      JOIN course_enrollments enrollment ON enrollment.course_id=c.course_id AND enrollment.student_id=student.student_id
        AND enrollment.enrollment_status IN ('active','completed')
      WHERE c.course_code=:course_code AND c.status='active'
      LIMIT 1");
    $courseQuery->execute(['student_id' => $studentId, 'course_code' => $courseCode]);
    $course = $courseQuery->fetch();
    if (!$course) return null;
    $quizQuery = db()->prepare("SELECT quiz_id,title,description,passing_score,status FROM course_quizzes
      WHERE course_id=:course_id LIMIT 1");
    $quizQuery->execute(['course_id' => $course['course_id']]);
    $quiz = $quizQuery->fetch();
    if (!$quiz) return ['has_quiz' => false, 'status' => 'terkunci', 'questions' => [], 'attempt' => null, 'all_materials_completed' => false];

    $materialState = db()->prepare("SELECT COUNT(*) AS total,
        SUM(CASE WHEN mp.progress_status='completed' THEN 1 ELSE 0 END) AS completed
      FROM courses c JOIN course_sessions cs ON cs.course_id=c.course_id AND cs.status='active'
      JOIN course_materials m ON m.session_id=cs.session_id AND m.is_active=1
      LEFT JOIN material_progress mp ON mp.material_id=m.material_id AND mp.student_id=:student_id
      WHERE c.course_id=:course_id");
    $materialState->execute(['student_id' => $studentId, 'course_id' => $course['course_id']]);
    $materialCounts = $materialState->fetch() ?: ['total' => 0, 'completed' => 0];
    $allMaterialsCompleted = (int) $materialCounts['total'] > 0 && (int) $materialCounts['completed'] === (int) $materialCounts['total'];
    $summary = apiStudentCourseQuizSummary($courseCode, $studentId, $allMaterialsCompleted);
    $questionQuery = db()->prepare("SELECT question_id,question_text,options,sort_order
      FROM course_quiz_questions WHERE quiz_id=:quiz_id ORDER BY sort_order,question_id");
    $questionQuery->execute(['quiz_id' => $quiz['quiz_id']]);
    $questions = [];
    foreach ($questionQuery->fetchAll() as $question) {
        $options = is_array($question['options']) ? $question['options'] : json_decode((string) $question['options'], true);
        if (!is_array($options)) $options = [];
        $questions[] = [
            'id' => (int) $question['question_id'], 'question' => $question['question_text'],
            'options' => array_values($options), 'type' => 'pg', 'weight' => 1,
        ];
    }
    return [
        'has_quiz' => true, 'quiz_id' => (int) $quiz['quiz_id'], 'title' => $quiz['title'],
        'description' => $quiz['description'] ?? '', 'passing_score' => (float) $quiz['passing_score'],
        'status' => $summary['status'], 'publication_status' => $quiz['status'], 'questions' => $questions, 'question_count' => count($questions),
        'attempt' => $summary['attempt'], 'score' => $summary['score'], 'all_materials_completed' => $allMaterialsCompleted,
        'total_materials' => (int) $materialCounts['total'], 'completed_materials' => (int) $materialCounts['completed'],
    ];
}

/** Student mengajukan akses course; materi tetap terkunci sampai Admin menyetujui. */
function apiRequestStudentCourseAccess(string $courseCode, int $studentId, string $studentNpk, string $requestNote = '', string $requesterRole = 'student'): array
{
    if ($studentId < 1 || !in_array($requesterRole, ['student', 'teacher'], true)) {
        throw new RuntimeException('Sesi Student tidak valid. Silakan masuk kembali.');
    }
    $connection = db();
    $connection->beginTransaction();
    try {
        $accountQuery = $connection->prepare("SELECT student_id,account_type FROM student_accounts WHERE student_id=:id AND npk=:npk AND status='active' LIMIT 1 FOR UPDATE");
        $accountQuery->execute(['id' => $studentId, 'npk' => $studentNpk]);
        $account = $accountQuery->fetch();
        if (!$account || ($account['account_type'] === 'teacher_shadow' && $requesterRole !== 'teacher')) {
            throw new RuntimeException('Akun Student tidak aktif atau tidak valid.');
        }
        $courseQuery = $connection->prepare("SELECT course_id FROM courses WHERE course_code=:code AND status='active' LIMIT 1 FOR UPDATE");
        $courseQuery->execute(['code' => $courseCode]);
        $course = $courseQuery->fetch();
        if (!$course) throw new RuntimeException('Course tidak tersedia atau sudah tidak aktif.');
        $enrollmentQuery = $connection->prepare('SELECT enrollment_status FROM course_enrollments WHERE student_id=:student_id AND course_id=:course_id LIMIT 1 FOR UPDATE');
        $enrollmentQuery->execute(['student_id' => $studentId, 'course_id' => $course['course_id']]);
        $enrollment = $enrollmentQuery->fetch();
        if ($enrollment && in_array($enrollment['enrollment_status'], ['active', 'completed'], true)) {
            throw new RuntimeException('Anda sudah memiliki akses ke course ini.');
        }
        $pendingQuery = $connection->prepare("SELECT request_id FROM course_enrollment_requests WHERE pending_key=CONCAT(:student_id,':',:course_id) LIMIT 1");
        $pendingQuery->execute(['student_id' => $studentId, 'course_id' => $course['course_id']]);
        if ($pendingQuery->fetch()) {
            $connection->commit();
            return ['status' => 'pending', 'already_pending' => true];
        }
        $requestQuery = $connection->prepare("INSERT INTO course_enrollment_requests(student_id,course_id,request_type,requested_by_npk,requested_by_role,request_note)
          VALUES(:student_id,:course_id,'student_request',:npk,:requester_role,:note)");
        $requestQuery->execute([
            'student_id' => $studentId, 'course_id' => $course['course_id'], 'npk' => $studentNpk,
            'requester_role' => $requesterRole, 'note' => $requestNote !== '' ? $requestNote : null,
        ]);
        $connection->commit();
        return ['status' => 'pending', 'already_pending' => false];
    } catch (Throwable $error) {
        if ($connection->inTransaction()) $connection->rollBack();
        if ($error instanceof PDOException && (string) ($error->errorInfo[1] ?? '') === '1062') {
            return ['status' => 'pending', 'already_pending' => true];
        }
        throw $error;
    }
}

/** Tandai satu materi selesai dan perbarui persentase progres course siswa. */
function apiCompleteStudentMaterial(string $courseCode, int $materialId, int $studentId): bool
{
    if ($studentId < 1 || $materialId < 1) return false;
    $connection = db();
    $connection->beginTransaction();
    try {
        $courseQuery = $connection->prepare("SELECT c.course_id FROM courses c JOIN student_accounts student
          ON student.student_id=:student_id AND student.status='active'
          JOIN course_enrollments enrollment ON enrollment.course_id=c.course_id AND enrollment.student_id=student.student_id
            AND enrollment.enrollment_status IN ('active','completed')
          WHERE c.course_code=:course_code AND c.status='active'
          LIMIT 1");
        $courseQuery->execute(['student_id' => $studentId, 'course_code' => $courseCode]);
        $course = $courseQuery->fetch();
        if (!$course) { $connection->rollBack(); return false; }

        $materialQuery = $connection->prepare("SELECT m.material_id FROM course_materials m
          JOIN course_sessions cs ON cs.session_id=m.session_id AND cs.status='active'
          WHERE cs.course_id=:course_id AND m.material_id=:material_id AND m.is_active=1 LIMIT 1");
        $materialQuery->execute(['course_id' => $course['course_id'], 'material_id' => $materialId]);
        if (!$materialQuery->fetch()) { $connection->rollBack(); return false; }

        $completeQuery = $connection->prepare("INSERT INTO material_progress(student_id,material_id,progress_status,completed_at)
          VALUES(:student_id,:material_id,'completed',UTC_TIMESTAMP())
          ON DUPLICATE KEY UPDATE progress_status='completed',completed_at=COALESCE(completed_at,UTC_TIMESTAMP())");
        $completeQuery->execute(['student_id' => $studentId, 'material_id' => $materialId]);

        $countsQuery = $connection->prepare("SELECT COUNT(*) AS total,
            SUM(CASE WHEN mp.progress_status='completed' THEN 1 ELSE 0 END) AS completed
          FROM course_sessions cs JOIN course_materials m ON m.session_id=cs.session_id
          LEFT JOIN material_progress mp ON mp.material_id=m.material_id AND mp.student_id=:student_id
          WHERE cs.course_id=:course_id AND cs.status='active' AND m.is_active=1");
        $countsQuery->execute(['student_id' => $studentId, 'course_id' => $course['course_id']]);
        $counts = $countsQuery->fetch() ?: ['total' => 0, 'completed' => 0];
        $progress = (int) $counts['total'] > 0 ? (int) floor(((int) $counts['completed'] / (int) $counts['total']) * 100) : 0;
        $updateQuery = $connection->prepare('UPDATE course_enrollments SET progress=:progress WHERE student_id=:student_id AND course_id=:course_id');
        $updateQuery->execute(['progress' => $progress, 'student_id' => $studentId, 'course_id' => $course['course_id']]);
        $connection->commit();
        return true;
    } catch (Throwable $error) {
        if ($connection->inTransaction()) $connection->rollBack();
        throw $error;
    }
}

/** Simpan dan nilai satu-satunya attempt quiz dengan constraint unik di database. */
function apiSubmitStudentCourseQuiz(string $courseCode, int $studentId, array $answers): array
{
    if ($studentId < 1) throw new RuntimeException('Sesi Student tidak valid. Silakan masuk kembali.');
    $connection = db();
    $connection->beginTransaction();
    try {
        $quizQuery = $connection->prepare("SELECT quiz.quiz_id,quiz.course_id,quiz.status FROM course_quizzes quiz
          JOIN courses course ON course.course_id=quiz.course_id AND course.status='active'
          JOIN student_accounts student ON student.student_id=:student_id AND student.status='active'
          JOIN course_enrollments enrollment ON enrollment.course_id=course.course_id AND enrollment.student_id=student.student_id
            AND enrollment.enrollment_status IN ('active','completed')
          WHERE course.course_code=:course_code LIMIT 1");
        $quizQuery->execute(['student_id' => $studentId, 'course_code' => $courseCode]);
        $quiz = $quizQuery->fetch();
        if (!$quiz || $quiz['status'] !== 'published') throw new RuntimeException('Quiz belum tersedia untuk course ini.');

        $attemptQuery = $connection->prepare('SELECT attempt_id FROM course_quiz_attempts WHERE quiz_id=:quiz_id AND student_id=:student_id LIMIT 1');
        $attemptQuery->execute(['quiz_id' => $quiz['quiz_id'], 'student_id' => $studentId]);
        if ($attemptQuery->fetch()) throw new RuntimeException('Quiz ini sudah pernah Anda kerjakan. Percobaan ulang tidak tersedia.');

        $materialQuery = $connection->prepare("SELECT COUNT(*) AS total,
            SUM(CASE WHEN mp.progress_status='completed' THEN 1 ELSE 0 END) AS completed
          FROM course_sessions cs JOIN course_materials m ON m.session_id=cs.session_id
          LEFT JOIN material_progress mp ON mp.material_id=m.material_id AND mp.student_id=:student_id
          WHERE cs.course_id=:course_id AND cs.status='active' AND m.is_active=1");
        $materialQuery->execute(['student_id' => $studentId, 'course_id' => $quiz['course_id']]);
        $materials = $materialQuery->fetch() ?: ['total' => 0, 'completed' => 0];
        if ((int) $materials['total'] < 1 || (int) $materials['completed'] !== (int) $materials['total']) {
            throw new RuntimeException('Selesaikan dan tandai semua materi course sebelum mengerjakan quiz.');
        }

        $questionQuery = $connection->prepare('SELECT question_id,options,correct_option,points FROM course_quiz_questions WHERE quiz_id=:quiz_id ORDER BY sort_order,question_id');
        $questionQuery->execute(['quiz_id' => $quiz['quiz_id']]);
        $questions = $questionQuery->fetchAll();
        if (!$questions) throw new RuntimeException('Quiz belum memiliki pertanyaan.');
        if (count($answers) !== count($questions)) throw new RuntimeException('Jawab semua pertanyaan sebelum mengirim quiz.');
        $answerSnapshot = [];
        $correctCount = 0;
        $earnedPoints = 0.0;
        $totalPoints = 0.0;
        foreach ($questions as $question) {
            $questionId = (string) $question['question_id'];
            if (!array_key_exists($questionId, $answers)) throw new RuntimeException('Jawab semua pertanyaan sebelum mengirim quiz.');
            $selected = $answers[$questionId];
            if (!is_string($selected) || !ctype_digit($selected)) throw new RuntimeException('Format jawaban tidak valid.');
            $options = is_array($question['options']) ? $question['options'] : json_decode((string) $question['options'], true);
            $selectedIndex = (int) $selected;
            if (!is_array($options) || !array_key_exists($selectedIndex, $options)) throw new RuntimeException('Pilih salah satu opsi untuk setiap pertanyaan.');
            $points = (float) $question['points'];
            $totalPoints += $points;
            $answerSnapshot[$questionId] = $selectedIndex;
            if ($selectedIndex === (int) $question['correct_option']) {
                $correctCount++;
                $earnedPoints += $points;
            }
        }
        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0.0;
        $saveQuery = $connection->prepare("INSERT INTO course_quiz_attempts(quiz_id,student_id,score,correct_count,total_questions,answers,submitted_at)
          VALUES(:quiz_id,:student_id,:score,:correct_count,:total_questions,:answers,UTC_TIMESTAMP())");
        $saveQuery->execute([
            'quiz_id' => $quiz['quiz_id'], 'student_id' => $studentId, 'score' => $score,
            'correct_count' => $correctCount, 'total_questions' => count($questions),
            'answers' => json_encode($answerSnapshot, JSON_THROW_ON_ERROR),
        ]);
        $gradeQuery = $connection->prepare('UPDATE course_enrollments SET quiz_score=:score WHERE student_id=:student_id AND course_id=:course_id');
        $gradeQuery->execute(['score' => $score, 'student_id' => $studentId, 'course_id' => $quiz['course_id']]);
        $connection->commit();
        return ['score' => $score, 'correct_count' => $correctCount, 'total_questions' => count($questions)];
    } catch (Throwable $error) {
        if ($connection->inTransaction()) $connection->rollBack();
        if ($error instanceof PDOException && (string) ($error->errorInfo[1] ?? '') === '1062') {
            throw new RuntimeException('Quiz ini sudah pernah Anda kerjakan. Percobaan ulang tidak tersedia.', 0, $error);
        }
        throw $error;
    }
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
