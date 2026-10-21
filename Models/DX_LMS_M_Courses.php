<?php
/**
 * Kelas Model Course (Course Model).
 *
 * Mengelola persistensi data Course dan seluruh relasinya ke Microsoft SQL Server:
 * - Master Course (`DX_LMS_M_Courses`)
 * - Sesi Pembelajaran (`DX_LMS_M_Course_Sessions`)
 * - Materi Pembelajaran (`DX_LMS_M_Course_Materials`)
 * - Kuis, Pre-Test, Post-Test, Final Assessment (`DX_LMS_M_Quizzes`)
 * - Butir Soal (`DX_LMS_M_Quiz_Questions`)
 * - Pilihan Jawaban (`DX_LMS_M_Quiz_Choices`)
 * - Rekomendasi Target Audience (`DX_LMS_M_Course_Targets`)
 * - Pendaftaran Peserta (`DX_LMS_T_Course_Enrollments`)
 * - Riwayat Belajar Peserta (`DX_LMS_T_Course_Histories`)
 * - Log Aktivitas & Notifikasi
 */

require_once __DIR__ . '/../Core/DX_LMS_M_Model.php';
require_once __DIR__ . '/../includes/DX_LMS_M_Db.php';

class DX_LMS_M_Courses extends DX_LMS_M_Model
{
    protected $table = 'DX_LMS_M_Courses';
    protected $primaryKey = 'course_id';

    /**
     * Mengambil seluruh course untuk pengajar tertentu atau seluruh sistem.
     *
     * @param int|null $teacherEmployeeId ID karyawan pengajar.
     * @param string $statusFilter Filter status ('all', 'draft', 'submitted', 'active', dll.)
     * @return array Daftar course lengkap dengan agregasi peserta dan sesi.
     */
    public function getAllForTeacher($teacherEmployeeId = null, $statusFilter = 'all')
    {
        $params = [];
        $whereSql = "1=1";

        if ($teacherEmployeeId !== null) {
            $whereSql .= " AND c.created_by = ?";
            $params[] = $teacherEmployeeId;
        }

        if ($statusFilter !== 'all') {
            $whereSql .= " AND LOWER(c.course_status) = ?";
            $params[] = strtolower($statusFilter);
        }

        $sql = "
            SELECT 
                c.course_id AS id,
                c.course_code AS code,
                c.title,
                c.description,
                c.category,
                c.course_status AS status,
                c.passing_grade,
                c.created_at,
                c.updated_at,
                u.fullname AS teacher_name,
                COUNT(DISTINCT s.session_id) AS sessions,
                COUNT(DISTINCT m.material_id) AS materials_count,
                COUNT(DISTINCT q.quiz_id) AS assessments,
                COUNT(DISTINCT e.enrollment_id) AS participants,
                ISNULL(AVG(ch.progress_percentage), 0) AS progress,
                ISNULL(AVG(ch.final_score), 0) AS avg_score,
                0 AS pending_reviews
            FROM {$this->table} c
            LEFT JOIN DX_LMS_M_Employees u ON c.created_by = u.employee_id
            LEFT JOIN DX_LMS_M_Course_Sessions s ON c.course_id = s.course_id
            LEFT JOIN DX_LMS_M_Course_Materials m ON s.session_id = m.session_id
            LEFT JOIN DX_LMS_M_Quizzes q ON c.course_id = q.course_id
            LEFT JOIN DX_LMS_T_Course_Enrollments e ON c.course_id = e.course_id
            LEFT JOIN DX_LMS_T_Course_Histories ch ON c.course_id = ch.course_id
            WHERE {$whereSql}
            GROUP BY 
                c.course_id, c.course_code, c.title, c.description, c.category,
                c.course_status, c.passing_grade, c.created_at, c.updated_at, u.fullname
            ORDER BY c.course_id DESC
        ";

        try {
            return dbQuery($sql, $params);
        } catch (Throwable $e) {
            error_log("DX_LMS_M_Courses::getAllForTeacher Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mengambil detail course berdasarkan ID berserta informasi pembuatnya.
     *
     * @param int $courseId ID Course.
     * @return array|null Data detail course.
     */
    public function getCourseDetail($courseId)
    {
        $sql = "
            SELECT 
                c.course_id AS id,
                c.course_code AS code,
                c.title,
                c.description,
                c.category,
                c.course_status AS status,
                c.passing_grade,
                c.created_at,
                c.updated_at,
                c.created_by,
                u.fullname AS teacher_name,
                u.npk AS teacher_npk,
                COUNT(DISTINCT s.session_id) AS sessions,
                COUNT(DISTINCT s.session_id) AS sessions_count,
                COUNT(DISTINCT m.material_id) AS materials_count,
                COUNT(DISTINCT q.quiz_id) AS assessments,
                COUNT(DISTINCT q.quiz_id) AS quizzes_count,
                COUNT(DISTINCT e.enrollment_id) AS participants,
                COUNT(DISTINCT e.enrollment_id) AS participants_count,
                ISNULL(AVG(ch.progress_percentage), 0) AS avg_progress,
                ISNULL(AVG(ch.final_score), 0) AS avg_score
            FROM {$this->table} c
            LEFT JOIN DX_LMS_M_Employees u ON c.created_by = u.employee_id
            LEFT JOIN DX_LMS_M_Course_Sessions s ON c.course_id = s.course_id
            LEFT JOIN DX_LMS_M_Course_Materials m ON s.session_id = m.session_id
            LEFT JOIN DX_LMS_M_Quizzes q ON c.course_id = q.course_id
            LEFT JOIN DX_LMS_T_Course_Enrollments e ON c.course_id = e.course_id
            LEFT JOIN DX_LMS_T_Course_Histories ch ON c.course_id = ch.course_id
            WHERE c.course_id = ?
            GROUP BY 
                c.course_id, c.course_code, c.title, c.description, c.category,
                c.course_status, c.passing_grade, c.created_at, c.updated_at, c.created_by,
                u.fullname, u.npk
        ";

        try {
            $course = dbQueryOne($sql, [$courseId]);
            if ($course) {
                // Ambil target posisi, departemen & golongan riil dari SSMS
                $targets = dbQuery("
                    SELECT p.position_name, d.department_name, g.golongan_name
                    FROM DX_LMS_M_Course_Targets ct
                    LEFT JOIN DX_LMS_M_Positions p ON ct.position_id = p.position_id
                    LEFT JOIN DX_LMS_M_Departments d ON ct.department_id = d.department_id
                    LEFT JOIN DX_LMS_M_Golongan g ON ct.golongan_id = g.golongan_id
                    WHERE ct.course_id = ?
                ", [$courseId]);

                $positions = array_filter(array_unique(array_column($targets, 'position_name')));
                $depts = array_filter(array_unique(array_column($targets, 'department_name')));
                $course['target_positions'] = !empty($positions) ? implode(', ', $positions) : 'Semua Posisi';
                $course['target_departments'] = !empty($depts) ? implode(', ', $depts) : 'Semua Departemen';
                $course['rejection_note'] = $this->getCourseRejectionNote($courseId);
                $course['corporate_mandate'] = $this->getCorporateMandateForCourse($courseId);
            }
            return $course;
        } catch (Throwable $e) {
            error_log("DX_LMS_M_Courses::getCourseDetail Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Memeriksa dan mengambil informasi Penugasan Mandat Korporat (Corporate Mandate) dari Admin.
     *
     * @param int $courseId ID Course.
     * @return array|null Detail mandat penugasan atau null jika bukan mandat korporat.
     */
    public function getCorporateMandateForCourse($courseId)
    {
        try {
            $sql = "
                SELECT 
                    ct.course_target_id,
                    ct.course_id,
                    ct.position_id,
                    ct.golongan_id,
                    ct.department_id,
                    ct.assigned_by,
                    ct.target_type,
                    ct.target_status,
                    ct.created_at,
                    admin_emp.fullname AS admin_name,
                    admin_emp.npk AS admin_npk,
                    p.position_name,
                    g.golongan_name,
                    d.department_name,
                    c.title AS course_title,
                    c.description AS course_description
                FROM DX_LMS_M_Course_Targets ct
                INNER JOIN DX_LMS_M_Courses c ON ct.course_id = c.course_id
                LEFT JOIN DX_LMS_M_Employees admin_emp ON ct.assigned_by = admin_emp.employee_id
                LEFT JOIN DX_LMS_M_Positions p ON ct.position_id = p.position_id
                LEFT JOIN DX_LMS_M_Golongan g ON ct.golongan_id = g.golongan_id
                LEFT JOIN DX_LMS_M_Departments d ON ct.department_id = d.department_id
                WHERE ct.course_id = ? AND ct.target_type = 'Corporate_Mandate'
            ";
            return dbQueryOne($sql, [$courseId]);
        } catch (\Throwable $e) {
            error_log("DX_LMS_M_Courses::getCorporateMandateForCourse Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil catatan penolakan/revisi terbaru dari Admin untuk course tertentu.
     *
     * @param int $courseId ID Course.
     * @return array|null Informasi catatan revisi, tanggal, dan pengirim.
     */
    public function getCourseRejectionNote($courseId)
    {
        $log = dbQueryOne("
            SELECT TOP 1 l.description, l.created_at, e.fullname AS admin_name
            FROM DX_LMS_T_Activity_Logs l
            LEFT JOIN DX_LMS_M_Employees e ON l.employee_id = e.employee_id
            WHERE l.activity_type = 'reject_course' AND l.reference_id = ?
            ORDER BY l.log_id DESC
        ", [(string) $courseId]);

        if (!$log) {
            return null;
        }

        $desc = $log['description'] ?? '';
        $note = $desc;
        if (strpos($desc, ': ') !== false) {
            $parts = explode(': ', $desc, 2);
            $note = trim($parts[1] ?? $desc);
        }

        return [
            'note' => $note,
            'full_description' => $desc,
            'admin_name' => $log['admin_name'] ?? 'Admin LMS',
            'created_at' => $log['created_at'],
            'date' => date('d M Y, H:i', strtotime($log['created_at'])),
            'time_ago' => timeAgo($log['created_at']),
        ];
    }

    /**
     * Mengambil daftar sesi, materi, dan kuis dalam suatu course.
     *
     * @param int $courseId ID Course.
     * @return array Daftar struktur sesi pembelajaran.
     */
    public function getCourseSessionsWithContent($courseId)
    {
        try {
            $sessions = dbQuery(
                "SELECT * FROM DX_LMS_M_Course_Sessions WHERE course_id = ? ORDER BY session_order ASC, session_id ASC",
                [$courseId]
            );

            foreach ($sessions as &$session) {
                $sessionId = $session['session_id'];

                // Ambil Materi
                $session['materials'] = dbQuery(
                    "SELECT * FROM DX_LMS_M_Course_Materials WHERE session_id = ? ORDER BY material_order ASC, material_id ASC",
                    [$sessionId]
                );

                // Ambil Kuis per Sesi (Pre-Test, Post-Test, Quiz Course)
                $session['quizzes'] = dbQuery(
                    "SELECT * FROM DX_LMS_M_Quizzes WHERE session_id = ? ORDER BY quiz_id ASC",
                    [$sessionId]
                );

                // Ambil pertanyaan untuk tiap kuis
                foreach ($session['quizzes'] as &$quiz) {
                    $quiz['questions'] = dbQuery(
                        "SELECT * FROM DX_LMS_M_Quiz_Questions WHERE quiz_id = ? ORDER BY question_order ASC, question_id ASC",
                        [$quiz['quiz_id']]
                    );
                    foreach ($quiz['questions'] as &$question) {
                        $question['choices'] = dbQuery(
                            "SELECT * FROM DX_LMS_M_Quiz_Choices WHERE question_id = ? ORDER BY choice_order ASC, choice_id ASC",
                            [$question['question_id']]
                        );
                    }
                }
            }

            return $sessions;
        } catch (Throwable $e) {
            error_log("DX_LMS_M_Courses::getCourseSessionsWithContent Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mengambil Final Assessment dari Course.
     *
     * @param int $courseId ID Course.
     * @return array|null Data Final Assessment dan butir soal.
     */
    public function getCourseFinalAssessment($courseId)
    {
        try {
            $quiz = dbQueryOne(
                "SELECT * FROM DX_LMS_M_Quizzes WHERE course_id = ? AND quiz_type = 'final_assessment'",
                [$courseId]
            );

            if ($quiz) {
                $quiz['questions'] = dbQuery(
                    "SELECT * FROM DX_LMS_M_Quiz_Questions WHERE quiz_id = ? ORDER BY question_order ASC, question_id ASC",
                    [$quiz['quiz_id']]
                );
                foreach ($quiz['questions'] as &$question) {
                    $question['choices'] = dbQuery(
                        "SELECT * FROM DX_LMS_M_Quiz_Choices WHERE question_id = ? ORDER BY choice_order ASC, choice_id ASC",
                        [$question['question_id']]
                    );
                }
            }

            return $quiz;
        } catch (Throwable $e) {
            error_log("DX_LMS_M_Courses::getCourseFinalAssessment Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil daftar peserta yang terdaftar pada suatu course.
     *
     * @param int $courseId ID Course.
     * @return array Daftar peserta dengan detail profil dan progres.
     */
    public function getCourseParticipants($courseId)
    {
        $sql = "
            SELECT 
                e.enrollment_id,
                e.course_id,
                e.employee_id,
                e.enrollment_status,
                e.enrolled_at,
                u.npk,
                u.fullname AS name,
                u.fullname AS nama,
                u.fullname,
                u.jabatan,
                u.golongan,
                u.department,
                u.division,
                u.section,
                ISNULL(ch.progress_percentage, 0) AS progress,
                ISNULL(ch.final_score, 0) AS score,
                ISNULL(ch.completion_status, 'In_Progress') AS completion_status
            FROM DX_LMS_T_Course_Enrollments e
            INNER JOIN DX_LMS_M_Employees u ON e.employee_id = u.employee_id
            LEFT JOIN DX_LMS_T_Course_Histories ch ON e.course_id = ch.course_id AND e.employee_id = ch.employee_id
            WHERE e.course_id = ?
            ORDER BY u.fullname ASC
        ";

        try {
            return dbQuery($sql, [$courseId]);
        } catch (Throwable $e) {
            error_log("DX_LMS_M_Courses::getCourseParticipants Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Membuat Course Baru Lengkap dengan Seluruh Relasinya ke SSMS (ACID Transaction).
     *
     * Menyimpan secara hierarkis:
     * 1. Header Course (`DX_LMS_M_Courses`)
     * 2. Target Rekomendasi (`DX_LMS_M_Course_Targets`)
     * 3. Sesi Kurikulum (`DX_LMS_M_Course_Sessions`)
     * 4. Modul Materi (`DX_LMS_M_Course_Materials`)
     * 5. Pre-Test, Post-Test, Quiz Sesi (`DX_LMS_M_Quizzes`)
     * 6. Final Assessment (`DX_LMS_M_Quizzes`)
     * 7. Butir Soal & Pilihan Jawaban (`DX_LMS_M_Quiz_Questions`, `DX_LMS_M_Quiz_Choices`)
     * 8. Pendaftaran Peserta (`DX_LMS_T_Course_Enrollments`, `DX_LMS_T_Course_Histories`)
     * 9. Notifikasi Admin & Log Aktivitas
     *
     * @param array $data Struktur data lengkap dari wizard create.
     * @param int $teacherEmployeeId ID karyawan pengajar.
     * @return array Hasil eksekusi ['success' => bool, 'course_id' => int, 'message' => string]
     * @throws Exception Jika terjadi kegagalan transaksi.
     */
    public function createCourse($data, $teacherEmployeeId)
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        try {
            // 1. Ekstraksi dan Validasi Data Header
            $title = trim($data['title'] ?? '');
            if (empty($title)) {
                throw new Exception("Nama course wajib diisi.");
            }

            $description = trim($data['description'] ?? '');
            $category = trim($data['category'] ?? '');
            if ((empty($category) || $category === 'Umum') && !empty($data['tags'])) {
                $category = is_array($data['tags']) ? implode(', ', $data['tags']) : $data['tags'];
            }
            if (empty($category)) {
                $category = 'Umum';
            }
            $category = substr($category, 0, 50);

            $status = in_array(($data['status'] ?? ''), ['Draft', 'Submitted', 'Approved', 'Active'])
                ? $data['status']
                : 'Submitted';

            $passingGrade = (int) ($data['passing_grade'] ?? 75);
            if ($passingGrade < 0 || $passingGrade > 100) {
                $passingGrade = 75;
            }

            $isUpdate = false;
            $courseId = (int) ($data['course_id'] ?? 0);
            $courseCode = '';
            $existingMandate = null;

            if ($courseId > 0) {
                $existing = dbQueryOne("SELECT course_id, course_code, created_by, course_status FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
                if (!$existing) {
                    throw new Exception("Course dengan ID {$courseId} tidak ditemukan.");
                }
                // Validasi hak akses keamanan
                if ((int) $existing['created_by'] !== (int) $teacherEmployeeId) {
                    throw new Exception("Akses Ditolak: Anda tidak memiliki hak akses untuk mengubah course ini.");
                }

                // Cek apakah course ini merupakan Mandat Korporat dari Admin
                $existingMandate = dbQueryOne("SELECT * FROM DX_LMS_M_Course_Targets WHERE course_id = ? AND target_type = 'Corporate_Mandate'", [$courseId]);

                $courseCode = $existing['course_code'];
                $isUpdate = true;

                $stmtUpdate = $pdo->prepare("
                    UPDATE DX_LMS_M_Courses 
                    SET title = ?, description = ?, category = ?, course_status = ?, passing_grade = ?, updated_at = GETDATE()
                    WHERE course_id = ?
                ");
                $stmtUpdate->execute([$title, $description, $category, $status, $passingGrade, $courseId]);

                // Bersihkan sesi lama jika wizard mengirimkan struktur baru yang lengkap
                if (!empty($data['sessions'])) {
                    $pdo->prepare("DELETE FROM DX_LMS_M_Quiz_Choices WHERE question_id IN (SELECT question_id FROM DX_LMS_M_Quiz_Questions WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE course_id = ?))")->execute([$courseId]);
                    $pdo->prepare("DELETE FROM DX_LMS_M_Quiz_Questions WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE course_id = ?)")->execute([$courseId]);
                    $pdo->prepare("DELETE FROM DX_LMS_M_Quizzes WHERE course_id = ?")->execute([$courseId]);
                    $pdo->prepare("DELETE FROM DX_LMS_M_Course_Materials WHERE session_id IN (SELECT session_id FROM DX_LMS_M_Course_Sessions WHERE course_id = ?)")->execute([$courseId]);
                    $pdo->prepare("DELETE FROM DX_LMS_M_Course_Sessions WHERE course_id = ?")->execute([$courseId]);
                }

                $pdo->prepare("DELETE FROM DX_LMS_M_Course_Targets WHERE course_id = ?")->execute([$courseId]);
            } else {
                // Generate Kode Course Unik
                $prefix = 'CRS-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 3));
                if (strlen($prefix) < 7) {
                    $prefix = str_pad($prefix, 7, 'X');
                }
                $randomNum = rand(100, 999);
                $courseCode = $prefix . '-' . $randomNum;

                // Pastikan tidak ada duplikasi course_code
                $codeCheck = dbQueryOne("SELECT course_id FROM DX_LMS_M_Courses WHERE course_code = ?", [$courseCode]);
                if ($codeCheck) {
                    $courseCode = $prefix . '-' . rand(1000, 9999);
                }

                // Simpan ke DX_LMS_M_Courses
                $stmtCourse = $pdo->prepare("
                    INSERT INTO DX_LMS_M_Courses 
                    (course_code, title, description, category, course_status, passing_grade, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE())
                ");
                $stmtCourse->execute([
                    $courseCode,
                    $title,
                    $description,
                    $category,
                    $status,
                    $passingGrade,
                    $teacherEmployeeId
                ]);
                $courseId = (int) $pdo->lastInsertId();
            }

            // 2. Simpan Target Audience / Rekomendasi Filter
            if ($existingMandate) {
                // Pertahankan Mandat Korporat dari Admin
                $posId = $existingMandate['position_id'];
                $golId = $existingMandate['golongan_id'];
                $deptId = $existingMandate['department_id'];
                $targetType = 'Corporate_Mandate';
                $assignedBy = (int) $existingMandate['assigned_by'];
            } else {
                $targetJabatan = trim($data['target_jabatan'] ?? '');
                $targetGolongan = trim($data['target_golongan'] ?? '');
                $targetDept = trim($data['target_departemen'] ?? '');

                $posId = null;
                if (!empty($targetJabatan)) {
                    $posRow = dbQueryOne("SELECT position_id FROM DX_LMS_M_Positions WHERE LOWER(position_name) = LOWER(?)", [$targetJabatan]);
                    if ($posRow) $posId = (int) $posRow['position_id'];
                }

                $golId = null;
                if (!empty($targetGolongan)) {
                    $golRow = dbQueryOne("SELECT golongan_id FROM DX_LMS_M_Golongan WHERE LOWER(golongan_name) = LOWER(?)", [$targetGolongan]);
                    if ($golRow) $golId = (int) $golRow['golongan_id'];
                }

                $deptId = null;
                if (!empty($targetDept)) {
                    $deptRow = dbQueryOne("SELECT department_id FROM DX_LMS_M_Departments WHERE LOWER(department_name) = LOWER(?)", [$targetDept]);
                    if ($deptRow) $deptId = (int) $deptRow['department_id'];
                }

                $targetType = 'Target_Audience';
                $assignedBy = $teacherEmployeeId;
            }

            $stmtTarget = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Targets 
                (course_id, position_id, golongan_id, department_id, assigned_by, target_type, target_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");
            $stmtTarget->execute([$courseId, $posId, $golId, $deptId, $assignedBy, $targetType]);

            // 3. Simpan Sesi Kurikulum & Konten Modul
            $sessions = $data['sessions'] ?? [];
            if (empty($sessions)) {
                // Sesi default jika pengajar tidak menambahkan sesi khusus
                $sessions = [
                    [
                        'title' => 'Sesi 1: Dasar Materi & Pengantar',
                        'order' => 1,
                        'modules' => [
                            [
                                'type' => 'Materi',
                                'title' => 'Materi Panduan Standar Operasional',
                                'content' => $description
                            ]
                        ]
                    ]
                ];
            }

            $stmtSession = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Sessions 
                (course_id, session_title, session_description, session_order, session_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");

            $stmtMaterial = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Materials 
                (session_id, material_title, material_description, material_type, file_path, duration, material_order, material_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");

            $stmtQuiz = $pdo->prepare("
                INSERT INTO DX_LMS_M_Quizzes 
                (course_id, session_id, quiz_type, quiz_title, quiz_description, quiz_duration, quiz_attempt_limit, quiz_passing_grade, quiz_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");

            $stmtQuestion = $pdo->prepare("
                INSERT INTO DX_LMS_M_Quiz_Questions 
                (quiz_id, question_text, question_type, question_order, question_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");

            $stmtChoice = $pdo->prepare("
                INSERT INTO DX_LMS_M_Quiz_Choices 
                (question_id, choice_text, is_correct, choice_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, GETDATE(), GETDATE())
            ");

            $sessionOrder = 1;
            foreach ($sessions as $sessData) {
                $sessTitle = trim($sessData['title'] ?? ('Sesi ' . $sessionOrder));
                $sessDesc = trim($sessData['description'] ?? '');

                $stmtSession->execute([$courseId, $sessTitle, $sessDesc, $sessionOrder]);
                $sessionId = (int) $pdo->lastInsertId();

                $materialOrder = 1;
                $modules = $sessData['modules'] ?? ($sessData['materials'] ?? []);

                foreach ($modules as $mod) {
                    $rawType = strtolower(trim($mod['type'] ?? 'materi'));
                    $modTitle = trim($mod['title'] ?? 'Modul ' . $materialOrder);

                    if (in_array($rawType, ['pre-test', 'pre_test'])) {
                        // Simpan Kuis Pre-Test
                        $stmtQuiz->execute([
                            $courseId,
                            $sessionId,
                            'pre_test',
                            $modTitle,
                            $mod['instructions'] ?? ($mod['description'] ?? 'Kerjakan pre-test untuk mengukur pemahaman awal.'),
                            (int) ($mod['duration'] ?? 15),
                            (int) ($mod['attempt_limit'] ?? 1),
                            (int) ($mod['passing_grade'] ?? ($mod['passing_score'] ?? 60))
                        ]);
                        $quizId = (int) $pdo->lastInsertId();
                        $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $quizId, $mod['questions'] ?? []);
                    } elseif (in_array($rawType, ['post-test', 'post_test', 'final_assessment', 'final'])) {
                        // Simpan Kuis Post-Test
                        $stmtQuiz->execute([
                            $courseId,
                            $sessionId,
                            'post_test',
                            $modTitle,
                            $mod['instructions'] ?? ($mod['description'] ?? 'Kerjakan post-test untuk mengukur pencapaian kompetensi.'),
                            (int) ($mod['duration'] ?? 20),
                            (int) ($mod['attempt_limit'] ?? 3),
                            (int) ($mod['passing_grade'] ?? ($mod['passing_score'] ?? 75))
                        ]);
                        $quizId = (int) $pdo->lastInsertId();
                        $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $quizId, $mod['questions'] ?? []);
                    } else {
                        // Simpan Modul Materi (materi, document, pdf, video, text, link, etc.)
                        $contentPages = isset($mod['pages']) 
                            ? (is_array($mod['pages']) ? json_encode($mod['pages']) : $mod['pages']) 
                            : ($mod['content'] ?? ($mod['description'] ?? ''));
                        $matType = in_array($rawType, ['video', 'pdf', 'text', 'document', 'link']) ? $rawType : ($mod['material_type'] ?? 'document');
                        $filePath = $mod['file_path'] ?? null;
                        $duration = !empty($mod['duration']) ? (string)$mod['duration'] : (!empty($mod['estimated_duration']) ? $mod['estimated_duration'] . ' Menit' : '15 Menit');

                        $stmtMaterial->execute([
                            $sessionId,
                            $modTitle,
                            $contentPages,
                            $matType,
                            $filePath,
                            $duration,
                            $materialOrder++
                        ]);
                    }
                }

                // Cek jika ada Sesi Quiz Mandiri
                if (!empty($sessData['quiz'])) {
                    $qData = $sessData['quiz'];
                    $stmtQuiz->execute([
                        $courseId,
                        $sessionId,
                        'quiz',
                        $qData['title'] ?? ('Quiz Sesi ' . $sessionOrder),
                        $qData['description'] ?? 'Latihan evaluasi mandiri sesi.',
                        (int) ($qData['duration_minutes'] ?? ($qData['duration'] ?? 20)),
                        (int) ($qData['attempt_limit'] ?? 3),
                        (int) ($qData['passing_grade'] ?? ($qData['passing_score'] ?? 70))
                    ]);
                    $quizId = (int) $pdo->lastInsertId();
                    $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $quizId, $qData['questions'] ?? []);
                }

                $sessionOrder++;
            }

            // 4. Simpan Pre-Test Level Course (jika ada)
            if (!empty($data['pre_test'])) {
                $pt = $data['pre_test'];
                $ptTitle = trim($pt['title'] ?? ('Pre-Test: ' . $title));
                $stmtQuiz->execute([
                    $courseId,
                    null, // NULL menandakan Pre-Test berlaku untuk seluruh Course
                    'pre_test',
                    $ptTitle,
                    $pt['description'] ?? 'Evaluasi pemahaman awal sebelum memulai pelatihan.',
                    (int) ($pt['duration_minutes'] ?? ($pt['duration'] ?? 15)),
                    (int) ($pt['attempt_limit'] ?? 1),
                    (int) ($pt['passing_grade'] ?? ($pt['passing_score'] ?? 60))
                ]);
                $preQuizId = (int) $pdo->lastInsertId();
                $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $preQuizId, $pt['questions'] ?? []);
            }

            // 5. Simpan Final Assessment / Post-Test Level Course (jika ada)
            $finalAssessmentData = $data['final_assessment'] ?? ($data['post_test'] ?? null);
            if (!empty($finalAssessmentData)) {
                $fa = $finalAssessmentData;
                $faTitle = trim($fa['title'] ?? ('Final Assessment: ' . $title));
                $stmtQuiz->execute([
                    $courseId,
                    null, // NULL menandakan Final Assessment berlaku untuk seluruh Course
                    'final_assessment',
                    $faTitle,
                    $fa['description'] ?? 'Ujian akhir penentu sertifikasi kompetensi.',
                    (int) ($fa['duration_minutes'] ?? ($fa['duration'] ?? 45)),
                    (int) ($fa['attempt_limit'] ?? 2),
                    (int) ($fa['passing_grade'] ?? ($fa['passing_score'] ?? $passingGrade))
                ]);
                $finalQuizId = (int) $pdo->lastInsertId();
                $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $finalQuizId, $fa['questions'] ?? []);
            }

            // 6. Simpan Peserta / Enrollments
            $participants = !empty($data['participants']) ? $data['participants'] : ($data['candidate_employee_ids'] ?? []);
            if (!empty($participants)) {
                if ($isUpdate) {
                    $pdo->prepare("DELETE FROM DX_LMS_T_Course_Enrollments WHERE course_id = ?")->execute([$courseId]);
                    $pdo->prepare("DELETE FROM DX_LMS_T_Course_Histories WHERE course_id = ?")->execute([$courseId]);
                }

                $stmtEnroll = $pdo->prepare("
                    INSERT INTO DX_LMS_T_Course_Enrollments 
                    (employee_id, course_id, enrollment_status, enrolled_at, created_at, updated_at)
                    VALUES (?, ?, 'enrolled', GETDATE(), GETDATE(), GETDATE())
                ");

                $stmtHistory = $pdo->prepare("
                    INSERT INTO DX_LMS_T_Course_Histories 
                    (employee_id, course_id, progress_percentage, completion_status, completed_session, last_accessed_at, created_at, updated_at)
                    VALUES (?, ?, 0, 'In_Progress', 0, GETDATE(), GETDATE(), GETDATE())
                ");

                foreach ($participants as $p) {
                    $empId = null;
                    if (is_numeric($p)) {
                        $empId = (int) $p;
                    } elseif (is_string($p)) {
                        // Cek apakah NPK
                        $empRow = dbQueryOne("SELECT employee_id FROM DX_LMS_M_Employees WHERE npk = ? OR npk = ?", [
                            $p,
                            str_pad($p, 7, '0', STR_PAD_LEFT)
                        ]);
                        if ($empRow) {
                            $empId = (int) $empRow['employee_id'];
                        }
                    } elseif (is_array($p) && !empty($p['employee_id'])) {
                        $empId = (int) $p['employee_id'];
                    }

                    if ($empId) {
                        try {
                            $stmtEnroll->execute([$empId, $courseId]);
                            $stmtHistory->execute([$empId, $courseId]);
                        } catch (Throwable $ignoreDup) {
                            // Abaikan jika sudah terdaftar
                        }
                    }
                }
            }

            // 6. Notifikasi ke Admin & Pengajar
            $stmtNotif = $pdo->prepare("
                INSERT INTO DX_LMS_T_Notifications 
                (employee_id, notification_title, notification_message, notification_type, is_read, created_at, updated_at)
                VALUES (?, ?, ?, 'info', 0, GETDATE(), GETDATE())
            ");

            // Cari Admin untuk dinotifikasi
            $admins = dbQuery("SELECT employee_id FROM DX_LMS_M_Employees WHERE role = 'admin' AND status = 'Active'");
            foreach ($admins as $adm) {
                $stmtNotif->execute([
                    $adm['employee_id'],
                    'Pengajuan Course Baru Menunggu Persetujuan',
                    "Course '{$title}' ({$courseCode}) telah diajukan dan siap ditinjau oleh Admin."
                ]);
            }

            // Notifikasi ke Pengajar Pembuat
            $stmtNotif->execute([
                $teacherEmployeeId,
                'Course Berhasil Dibuat',
                "Course '{$title}' ({$courseCode}) berhasil disimpan dengan status '{$status}'."
            ]);

            // 7. Log Aktivitas
            $actType = $isUpdate ? 'update_course' : 'create_course';
            $actDesc = $isUpdate 
                ? "Pengajar memperbarui course '{$title}' ({$courseCode}) dengan status {$status}"
                : "Pengajar membuat course '{$title}' ({$courseCode}) dengan status {$status}";

            $stmtLog = $pdo->prepare("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, GETDATE())
            ");
            $stmtLog->execute([
                $teacherEmployeeId,
                $actType,
                (string) $courseId,
                $actDesc,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Internal DLS App'
            ]);

            // Commit transaksi
            $pdo->commit();

            return [
                'success' => true,
                'course_id' => $courseId,
                'course_code' => $courseCode,
                'title' => $title,
                'message' => $isUpdate
                    ? "Course '{$title}' berhasil diperbarui di database SQL Server."
                    : "Course '{$title}' berhasil disimpan di database SQL Server."
            ];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("DX_LMS_M_Courses::createCourse Transaction Rollback: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal menyimpan course: " . $e->getMessage()
            ];
        }
    }

    /**
     * Menghapus Course secara total dan permanen (Hard Delete) dari database MS SQL Server.
     * Menghapus seluruh relasi anak secara bertingkat (cascading) sesuai integritas foreign key.
     *
     * @param int $courseId ID Course yang akan dihapus.
     * @return array Status keberhasilan dan pesan.
     */
    public function deleteCourse($courseId)
    {
        $courseId = (int) $courseId;
        if ($courseId <= 0) {
            return ['success' => false, 'message' => 'ID Course tidak valid.'];
        }

        $course = dbQueryOne("SELECT course_id, course_code, title FROM {$this->table} WHERE course_id = ?", [$courseId]);
        if (!$course) {
            return ['success' => false, 'message' => 'Data Course tidak ditemukan di database.'];
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();

        try {
            // 1. Forum: Hapus replies (putus hierarki parent_reply_id dulu) lalu topics
            dbExecute("UPDATE DX_LMS_T_Forum_Replies SET parent_reply_id = NULL WHERE topic_id IN (SELECT topic_id FROM DX_LMS_T_Forum_Topics WHERE course_id = ?)", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_T_Forum_Replies WHERE topic_id IN (SELECT topic_id FROM DX_LMS_T_Forum_Topics WHERE course_id = ?)", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_T_Forum_Topics WHERE course_id = ?", [$courseId]);

            // 2. Kuis: Hapus jawaban peserta, riwayat attempt, opsi pilihan, butir soal, dan kuis
            dbExecute("DELETE FROM DX_LMS_T_Quiz_Answers WHERE attempt_id IN (SELECT attempt_id FROM DX_LMS_T_Quiz_Attempts WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE course_id = ?))", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_T_Quiz_Attempts WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE course_id = ?)", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Quiz_Choices WHERE question_id IN (SELECT question_id FROM DX_LMS_M_Quiz_Questions WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE course_id = ?))", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Quiz_Questions WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE course_id = ?)", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Quizzes WHERE course_id = ?", [$courseId]);

            // 3. Tugas / Assignment: Hapus submission tugas dan master assignment per sesi
            dbExecute("DELETE FROM DX_LMS_T_Assignment_Submissions WHERE assignment_id IN (SELECT assignment_id FROM DX_LMS_M_Session_Assignments WHERE session_id IN (SELECT session_id FROM DX_LMS_M_Course_Sessions WHERE course_id = ?))", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Session_Assignments WHERE session_id IN (SELECT session_id FROM DX_LMS_M_Course_Sessions WHERE course_id = ?)", [$courseId]);

            // 4. Kelompok / Groups: Hapus submission tugas kelompok, anggota kelompok, dan master kelompok
            dbExecute("DELETE FROM DX_LMS_T_Assignment_Submissions WHERE group_id IN (SELECT group_id FROM DX_LMS_M_Course_Groups WHERE course_id = ?)", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Group_Members WHERE group_id IN (SELECT group_id FROM DX_LMS_M_Course_Groups WHERE course_id = ?)", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Course_Groups WHERE course_id = ?", [$courseId]);

            // 5. Materi: Hapus progres belajar materi siswa, lalu materi per sesi
            dbExecute("DELETE FROM DX_LMS_T_Material_Progress WHERE material_id IN (SELECT material_id FROM DX_LMS_M_Course_Materials WHERE session_id IN (SELECT session_id FROM DX_LMS_M_Course_Sessions WHERE course_id = ?))", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Course_Materials WHERE session_id IN (SELECT session_id FROM DX_LMS_M_Course_Sessions WHERE course_id = ?)", [$courseId]);

            // 6. Absensi, Sertifikat, Target Peserta, Enrollment, Riwayat Course
            dbExecute("DELETE FROM DX_LMS_T_Attendance WHERE course_id = ?", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_T_Certificates WHERE course_id = ?", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_M_Course_Targets WHERE course_id = ?", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_T_Course_Enrollments WHERE course_id = ?", [$courseId]);
            dbExecute("DELETE FROM DX_LMS_T_Course_Histories WHERE course_id = ?", [$courseId]);

            // 7. Sesi Pembelajaran
            dbExecute("DELETE FROM DX_LMS_M_Course_Sessions WHERE course_id = ?", [$courseId]);

            // 8. Hapus Master Course Utama
            dbExecute("DELETE FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);

            $pdo->commit();

            return [
                'success' => true,
                'message' => "Course '{$course['title']}' ({$course['course_code']}) dan seluruh relasi datanya berhasil dihapus permanen dari sistem."
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("DX_LMS_M_Courses::deleteCourse Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghapus course: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Helper privat untuk menyimpan butir soal kuis dan opsi jawabannya.
     */
    private function saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $quizId, $questions)
    {
        if (empty($questions) || !is_array($questions)) {
            // Berikan soal sampel jika kosong
            $questions = [
                [
                    'text' => 'Apakah tujuan utama implementasi 5S di tempat kerja?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['text' => 'Meningkatkan efisiensi, kerapian, dan keselamatan kerja', 'is_correct' => 1],
                        ['text' => 'Menghabiskan waktu jam kerja', 'is_correct' => 0],
                        ['text' => 'Mengurangi jumlah karyawan di lini produksi', 'is_correct' => 0],
                        ['text' => 'Hanya sekadar formalitas audit manajemen', 'is_correct' => 0]
                    ]
                ]
            ];
        }

        $qOrder = 1;
        foreach ($questions as $q) {
            $qText = trim($q['text'] ?? ($q['question_text'] ?? ''));
            if (empty($qText)) continue;

            $qType = trim($q['type'] ?? ($q['question_type'] ?? 'multiple_choice'));
            if ($qType !== 'essay') {
                $qType = 'multiple_choice';
            }

            $stmtQuestion->execute([$quizId, $qText, $qType, $qOrder++]);
            $questionId = (int) $pdo->lastInsertId();

            if ($qType === 'multiple_choice') {
                $options = $q['options'] ?? ($q['choices'] ?? []);
                $cOrder = 1;
                foreach ($options as $opt) {
                    $optText = trim(is_array($opt) ? ($opt['text'] ?? ($opt['choice_text'] ?? '')) : $opt);
                    if (empty($optText)) continue;

                    $isCorrect = is_array($opt) ? (!empty($opt['is_correct']) ? 1 : 0) : 0;
                    $stmtChoice->execute([$questionId, $optText, $isCorrect, $cOrder++]);
                }
            }
        }
    }

    /**
     * Menyimpan Sesi Pembelajaran (Tambah baru atau update)
     */
    public function saveCourseSession($courseId, $teacherEmployeeId, $data)
    {
        $courseId = (int) $courseId;
        $course = dbQueryOne("SELECT course_id, created_by, course_status FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
        if (!$course) {
            return ['success' => false, 'message' => 'Course tidak ditemukan.'];
        }
        if ((int) $course['created_by'] !== (int) $teacherEmployeeId) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik course ini.'];
        }

        $sessionId = (int) ($data['session_id'] ?? 0);
        $title = trim($data['session_title'] ?? ($data['title'] ?? ''));
        $description = trim($data['session_description'] ?? ($data['description'] ?? ''));

        if (empty($title)) {
            return ['success' => false, 'message' => 'Judul sesi wajib diisi.'];
        }

        if ($sessionId > 0) {
            dbExecute("
                UPDATE DX_LMS_M_Course_Sessions 
                SET session_title = ?, session_description = ?, updated_at = GETDATE()
                WHERE session_id = ? AND course_id = ?
            ", [$title, $description, $sessionId, $courseId]);

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'edit_session', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $courseId,
                "Pengajar memperbarui sesi '{$title}'",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            return ['success' => true, 'session_id' => $sessionId, 'message' => "Sesi '{$title}' berhasil diperbarui."];
        } else {
            $maxOrderRow = dbQueryOne("SELECT ISNULL(MAX(session_order), 0) + 1 AS next_order FROM DX_LMS_M_Course_Sessions WHERE course_id = ?", [$courseId]);
            $nextOrder = (int) ($maxOrderRow['next_order'] ?? 1);

            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Sessions 
                (course_id, session_title, session_description, session_order, session_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");
            $stmt->execute([$courseId, $title, $description, $nextOrder]);
            $newSessionId = (int) $pdo->lastInsertId();

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'add_session', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $courseId,
                "Pengajar menambahkan sesi baru '{$title}'",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            return ['success' => true, 'session_id' => $newSessionId, 'message' => "Sesi '{$title}' berhasil ditambahkan ke course."];
        }
    }

    /**
     * Menghapus Sesi Pembelajaran secara bertingkat
     */
    public function deleteCourseSession($sessionId, $teacherEmployeeId)
    {
        $sessionId = (int) $sessionId;
        $session = dbQueryOne("
            SELECT s.session_id, s.session_title, s.course_id, c.created_by 
            FROM DX_LMS_M_Course_Sessions s
            INNER JOIN DX_LMS_M_Courses c ON s.course_id = c.course_id
            WHERE s.session_id = ?
        ", [$sessionId]);

        if (!$session) {
            return ['success' => false, 'message' => 'Sesi tidak ditemukan.'];
        }
        if ((int) $session['created_by'] !== (int) $teacherEmployeeId) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik course ini.'];
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            // Hapus kuis sesi & butir soal
            dbExecute("DELETE FROM DX_LMS_M_Quiz_Choices WHERE question_id IN (SELECT question_id FROM DX_LMS_M_Quiz_Questions WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE session_id = ?))", [$sessionId]);
            dbExecute("DELETE FROM DX_LMS_M_Quiz_Questions WHERE quiz_id IN (SELECT quiz_id FROM DX_LMS_M_Quizzes WHERE session_id = ?)", [$sessionId]);
            dbExecute("DELETE FROM DX_LMS_M_Quizzes WHERE session_id = ?", [$sessionId]);

            // Hapus materi progres & materi
            dbExecute("DELETE FROM DX_LMS_T_Material_Progress WHERE material_id IN (SELECT material_id FROM DX_LMS_M_Course_Materials WHERE session_id = ?)", [$sessionId]);
            dbExecute("DELETE FROM DX_LMS_M_Course_Materials WHERE session_id = ?", [$sessionId]);

            // Hapus sesi
            dbExecute("DELETE FROM DX_LMS_M_Course_Sessions WHERE session_id = ?", [$sessionId]);

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'delete_session', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $session['course_id'],
                "Pengajar menghapus sesi '{$session['session_title']}'",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            $pdo->commit();
            return ['success' => true, 'message' => "Sesi '{$session['session_title']}' berhasil dihapus."];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'message' => 'Gagal menghapus sesi: ' . $e->getMessage()];
        }
    }

    /**
     * Menyimpan Materi Pembelajaran (Tambah baru atau update)
     */
    public function saveCourseMaterial($sessionId, $teacherEmployeeId, $data)
    {
        $sessionId = (int) $sessionId;
        $session = dbQueryOne("
            SELECT s.session_id, s.course_id, c.created_by 
            FROM DX_LMS_M_Course_Sessions s
            INNER JOIN DX_LMS_M_Courses c ON s.course_id = c.course_id
            WHERE s.session_id = ?
        ", [$sessionId]);

        if (!$session) {
            return ['success' => false, 'message' => 'Sesi tidak ditemukan.'];
        }
        if ((int) $session['created_by'] !== (int) $teacherEmployeeId) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik course ini.'];
        }

        $materialId = (int) ($data['material_id'] ?? 0);
        $title = trim($data['material_title'] ?? ($data['title'] ?? ''));
        $type = trim($data['material_type'] ?? ($data['type'] ?? 'text'));
        $duration = trim($data['duration'] ?? '15 Menit');
        $filePath = trim($data['file_path'] ?? '');
        $description = trim($data['material_description'] ?? ($data['content'] ?? ''));

        if (empty($title)) {
            return ['success' => false, 'message' => 'Judul materi wajib diisi.'];
        }

        if ($materialId > 0) {
            dbExecute("
                UPDATE DX_LMS_M_Course_Materials 
                SET material_title = ?, material_description = ?, material_type = ?, file_path = ?, duration = ?, updated_at = GETDATE()
                WHERE material_id = ? AND session_id = ?
            ", [$title, $description, $type, $filePath, $duration, $materialId, $sessionId]);

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'edit_material', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $session['course_id'],
                "Pengajar memperbarui materi '{$title}' ({$type})",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            return ['success' => true, 'material_id' => $materialId, 'message' => "Materi '{$title}' berhasil diperbarui."];
        } else {
            $maxOrderRow = dbQueryOne("SELECT ISNULL(MAX(material_order), 0) + 1 AS next_order FROM DX_LMS_M_Course_Materials WHERE session_id = ?", [$sessionId]);
            $nextOrder = (int) ($maxOrderRow['next_order'] ?? 1);

            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Materials 
                (session_id, material_title, material_description, material_type, file_path, duration, material_order, material_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
            ");
            $stmt->execute([$sessionId, $title, $description, $type, $filePath, $duration, $nextOrder]);
            $newMaterialId = (int) $pdo->lastInsertId();

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'add_material', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $session['course_id'],
                "Pengajar menambahkan materi baru '{$title}' ({$type})",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            return ['success' => true, 'material_id' => $newMaterialId, 'message' => "Materi '{$title}' berhasil ditambahkan ke sesi."];
        }
    }

    /**
     * Menghapus Materi Pembelajaran
     */
    public function deleteCourseMaterial($materialId, $teacherEmployeeId)
    {
        $materialId = (int) $materialId;
        $mat = dbQueryOne("
            SELECT m.material_id, m.material_title, s.course_id, c.created_by 
            FROM DX_LMS_M_Course_Materials m
            INNER JOIN DX_LMS_M_Course_Sessions s ON m.session_id = s.session_id
            INNER JOIN DX_LMS_M_Courses c ON s.course_id = c.course_id
            WHERE m.material_id = ?
        ", [$materialId]);

        if (!$mat) {
            return ['success' => false, 'message' => 'Materi tidak ditemukan.'];
        }
        if ((int) $mat['created_by'] !== (int) $teacherEmployeeId) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik course ini.'];
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            dbExecute("DELETE FROM DX_LMS_T_Material_Progress WHERE material_id = ?", [$materialId]);
            dbExecute("DELETE FROM DX_LMS_M_Course_Materials WHERE material_id = ?", [$materialId]);

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'delete_material', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $mat['course_id'],
                "Pengajar menghapus materi '{$mat['material_title']}'",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            $pdo->commit();
            return ['success' => true, 'message' => "Materi '{$mat['material_title']}' berhasil dihapus."];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'message' => 'Gagal menghapus materi: ' . $e->getMessage()];
        }
    }

    /**
     * Menyimpan Kuis / Assessment (Tambah baru atau update)
     */
    public function saveCourseQuiz($courseId, $sessionId, $teacherEmployeeId, $data)
    {
        $courseId = (int) $courseId;
        $course = dbQueryOne("SELECT course_id, created_by FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
        if (!$course) {
            return ['success' => false, 'message' => 'Course tidak ditemukan.'];
        }
        if ((int) $course['created_by'] !== (int) $teacherEmployeeId) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik course ini.'];
        }

        $quizId = (int) ($data['quiz_id'] ?? 0);
        $title = trim($data['quiz_title'] ?? ($data['title'] ?? 'Kuis Pemahaman'));
        $desc = trim($data['quiz_description'] ?? ($data['description'] ?? ''));
        $quizType = trim($data['quiz_type'] ?? 'quiz');
        $duration = (int) ($data['quiz_duration'] ?? ($data['duration'] ?? 20));
        $passingGrade = (int) ($data['quiz_passing_grade'] ?? ($data['passing_grade'] ?? 75));
        $attemptLimit = (int) ($data['quiz_attempt_limit'] ?? 3);
        $sessId = $sessionId > 0 ? (int) $sessionId : null;

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            if ($quizId > 0) {
                dbExecute("
                    UPDATE DX_LMS_M_Quizzes 
                    SET quiz_title = ?, quiz_description = ?, quiz_duration = ?, quiz_passing_grade = ?, quiz_attempt_limit = ?, updated_at = GETDATE()
                    WHERE quiz_id = ? AND course_id = ?
                ", [$title, $desc, $duration, $passingGrade, $attemptLimit, $quizId, $courseId]);

                if (!empty($data['questions'])) {
                    dbExecute("DELETE FROM DX_LMS_M_Quiz_Choices WHERE question_id IN (SELECT question_id FROM DX_LMS_M_Quiz_Questions WHERE quiz_id = ?)", [$quizId]);
                    dbExecute("DELETE FROM DX_LMS_M_Quiz_Questions WHERE quiz_id = ?", [$quizId]);
                    $stmtQ = $pdo->prepare("INSERT INTO DX_LMS_M_Quiz_Questions (quiz_id, question_text, question_type, question_order, question_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'Active', GETDATE(), GETDATE())");
                    $stmtC = $pdo->prepare("INSERT INTO DX_LMS_M_Quiz_Choices (question_id, choice_text, is_correct, choice_order, created_at, updated_at) VALUES (?, ?, ?, ?, GETDATE(), GETDATE())");
                    $this->saveQuizQuestions($pdo, $stmtQ, $stmtC, $quizId, $data['questions']);
                }

                dbExecute("
                    INSERT INTO DX_LMS_T_Activity_Logs 
                    (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                    VALUES (?, 'edit_quiz', ?, ?, ?, ?, GETDATE())
                ", [
                    $teacherEmployeeId,
                    (string) $courseId,
                    "Pengajar memperbarui kuis '{$title}'",
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
                ]);

                $pdo->commit();
                return ['success' => true, 'quiz_id' => $quizId, 'message' => "Kuis '{$title}' berhasil diperbarui."];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO DX_LMS_M_Quizzes 
                    (course_id, session_id, quiz_type, quiz_title, quiz_description, quiz_duration, quiz_attempt_limit, quiz_passing_grade, quiz_status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', GETDATE(), GETDATE())
                ");
                $stmt->execute([$courseId, $sessId, $quizType, $title, $desc, $duration, $attemptLimit, $passingGrade]);
                $newQuizId = (int) $pdo->lastInsertId();

                $stmtQ = $pdo->prepare("INSERT INTO DX_LMS_M_Quiz_Questions (quiz_id, question_text, question_type, question_order, question_status, created_at, updated_at) VALUES (?, ?, ?, ?, 'Active', GETDATE(), GETDATE())");
                $stmtC = $pdo->prepare("INSERT INTO DX_LMS_M_Quiz_Choices (question_id, choice_text, is_correct, choice_order, created_at, updated_at) VALUES (?, ?, ?, ?, GETDATE(), GETDATE())");
                $this->saveQuizQuestions($pdo, $stmtQ, $stmtC, $newQuizId, $data['questions'] ?? []);

                dbExecute("
                    INSERT INTO DX_LMS_T_Activity_Logs 
                    (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                    VALUES (?, 'add_quiz', ?, ?, ?, ?, GETDATE())
                ", [
                    $teacherEmployeeId,
                    (string) $courseId,
                    "Pengajar menambahkan kuis baru '{$title}'",
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
                ]);

                $pdo->commit();
                return ['success' => true, 'quiz_id' => $newQuizId, 'message' => "Kuis '{$title}' berhasil ditambahkan."];
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'message' => 'Gagal menyimpan kuis: ' . $e->getMessage()];
        }
    }

    /**
     * Menghapus Kuis
     */
    public function deleteCourseQuiz($quizId, $teacherEmployeeId)
    {
        $quizId = (int) $quizId;
        $quiz = dbQueryOne("
            SELECT q.quiz_id, q.quiz_title, q.course_id, c.created_by 
            FROM DX_LMS_M_Quizzes q
            INNER JOIN DX_LMS_M_Courses c ON q.course_id = c.course_id
            WHERE q.quiz_id = ?
        ", [$quizId]);

        if (!$quiz) {
            return ['success' => false, 'message' => 'Kuis tidak ditemukan.'];
        }
        if ((int) $quiz['created_by'] !== (int) $teacherEmployeeId) {
            return ['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik course ini.'];
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();
        try {
            dbExecute("DELETE FROM DX_LMS_T_Quiz_Answers WHERE attempt_id IN (SELECT attempt_id FROM DX_LMS_T_Quiz_Attempts WHERE quiz_id = ?)", [$quizId]);
            dbExecute("DELETE FROM DX_LMS_T_Quiz_Attempts WHERE quiz_id = ?", [$quizId]);
            dbExecute("DELETE FROM DX_LMS_M_Quiz_Choices WHERE question_id IN (SELECT question_id FROM DX_LMS_M_Quiz_Questions WHERE quiz_id = ?)", [$quizId]);
            dbExecute("DELETE FROM DX_LMS_M_Quiz_Questions WHERE quiz_id = ?", [$quizId]);
            dbExecute("DELETE FROM DX_LMS_M_Quizzes WHERE quiz_id = ?", [$quizId]);

            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'delete_quiz', ?, ?, ?, ?, GETDATE())
            ", [
                $teacherEmployeeId,
                (string) $quiz['course_id'],
                "Pengajar menghapus kuis '{$quiz['quiz_title']}'",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher'
            ]);

            $pdo->commit();
            return ['success' => true, 'message' => "Kuis '{$quiz['quiz_title']}' berhasil dihapus."];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'message' => 'Gagal menghapus kuis: ' . $e->getMessage()];
        }
    }
}
