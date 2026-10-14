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
                COUNT(DISTINCT s.session_id) AS sessions_count,
                COUNT(DISTINCT m.material_id) AS materials_count,
                COUNT(DISTINCT q.quiz_id) AS quizzes_count,
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
            return dbQueryOne($sql, [$courseId]);
        } catch (Throwable $e) {
            error_log("DX_LMS_M_Courses::getCourseDetail Error: " . $e->getMessage());
            return null;
        }
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
            $category = trim($data['category'] ?? 'Umum');
            if (empty($category) && !empty($data['tags'])) {
                $category = is_array($data['tags']) ? implode(', ', $data['tags']) : $data['tags'];
            }
            if (empty($category)) {
                $category = 'Engineering';
            }

            $status = in_array(($data['status'] ?? ''), ['Draft', 'Submitted', 'Approved', 'Active'])
                ? $data['status']
                : 'Submitted';

            $passingGrade = (int) ($data['passing_grade'] ?? 75);
            if ($passingGrade < 0 || $passingGrade > 100) {
                $passingGrade = 75;
            }

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

            // 2. Simpan Target Audience / Rekomendasi Filter
            $targets = $data['targets'] ?? [];
            $targetPositions = $targets['positions'] ?? ($targets['jabatan'] ?? []);
            $targetGolongans = $targets['golongans'] ?? ($targets['golongan'] ?? []);
            $targetDepts = $targets['departments'] ?? ($targets['departemen'] ?? []);

            $stmtTarget = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Targets 
                (course_id, position_id, golongan_id, department_id, assigned_by, target_type, target_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'Target_Audience', 'Active', GETDATE(), GETDATE())
            ");
            // Buat record target awal
            $stmtTarget->execute([$courseId, null, null, null, $teacherEmployeeId]);

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
                $modules = $sessData['modules'] ?? [];

                foreach ($modules as $mod) {
                    $modType = strtolower(trim($mod['type'] ?? 'materi'));
                    $modTitle = trim($mod['title'] ?? 'Modul ' . $materialOrder);

                    if ($modType === 'materi') {
                        // Simpan Modul Materi
                        $contentPages = isset($mod['pages']) ? (is_array($mod['pages']) ? json_encode($mod['pages']) : $mod['pages']) : ($mod['content'] ?? '');
                        $matType = $mod['material_type'] ?? 'text';
                        $filePath = $mod['file_path'] ?? null;
                        $duration = $mod['duration'] ?? '15 Menit';

                        $stmtMaterial->execute([
                            $sessionId,
                            $modTitle,
                            $contentPages,
                            $matType,
                            $filePath,
                            $duration,
                            $materialOrder++
                        ]);
                    } elseif ($modType === 'pre-test' || $modType === 'pre_test') {
                        // Simpan Kuis Pre-Test
                        $stmtQuiz->execute([
                            $courseId,
                            $sessionId,
                            'pre_test',
                            $modTitle,
                            $mod['instructions'] ?? 'Kerjakan pre-test untuk mengukur pemahaman awal.',
                            (int) ($mod['duration'] ?? 15),
                            (int) ($mod['attempt_limit'] ?? 1),
                            (int) ($mod['passing_grade'] ?? 60)
                        ]);
                        $quizId = (int) $pdo->lastInsertId();
                        $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $quizId, $mod['questions'] ?? []);
                    } elseif ($modType === 'post-test' || $modType === 'post_test') {
                        // Simpan Kuis Post-Test
                        $stmtQuiz->execute([
                            $courseId,
                            $sessionId,
                            'post_test',
                            $modTitle,
                            $mod['instructions'] ?? 'Kerjakan post-test untuk mengukur pencapaian kompetensi.',
                            (int) ($mod['duration'] ?? 20),
                            (int) ($mod['attempt_limit'] ?? 3),
                            (int) ($mod['passing_grade'] ?? 75)
                        ]);
                        $quizId = (int) $pdo->lastInsertId();
                        $this->saveQuizQuestions($pdo, $stmtQuestion, $stmtChoice, $quizId, $mod['questions'] ?? []);
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
                        (int) ($qData['duration'] ?? 20),
                        (int) ($qData['attempt_limit'] ?? 3),
                        (int) ($qData['passing_grade'] ?? 75)
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
            $stmtLog = $pdo->prepare("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'create_course', ?, ?, ?, ?, GETDATE())
            ");
            $stmtLog->execute([
                $teacherEmployeeId,
                (string) $courseId,
                "Pengajar membuat course '{$title}' ({$courseCode}) dengan status {$status}",
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
                'message' => "Course '{$title}' berhasil disimpan di database SQL Server."
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
}
