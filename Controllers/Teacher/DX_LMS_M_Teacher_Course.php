<?php
/**
 * Kelas Pengontrol Manajemen Course Pengajar (Teacher Course Controller).
 *
 * Mengelola course list, detail course, dan flow penambahan (create wizard)
 * dengan persistensi penuh ke Microsoft SQL Server (SSMS).
 */

require_once 'Models/DX_LMS_M_Courses.php';
require_once 'Models/DX_LMS_M_Employees.php';

class DX_LMS_M_Teacher_Course extends DX_LMS_M_Controller
{
    /**
     * Menampilkan daftar course yang diampu.
     */
    public function index()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $filterStatus = $_GET['status'] ?? 'all';

        // Ambil course dari Database SQL Server khusus milik pengajar yang sedang login
        $dbCourses = $courseModel->getAllForTeacher($teacherId, $filterStatus);

        // Format dbCourses agar sesuai dengan UI Teacher List
        $formattedDbCourses = [];
        foreach ($dbCourses as $dbc) {
            $isRej = strtolower($dbc['status'] ?? '') === 'rejected';
            $formattedDbCourses[] = [
                'id' => $dbc['id'],
                'code' => $dbc['code'],
                'title' => $dbc['title'],
                'description' => $dbc['description'],
                'category' => $dbc['category'],
                'status' => $dbc['status'],
                'icon' => 'school',
                'participants' => (int) $dbc['participants'],
                'pending_reviews' => (int) $dbc['pending_reviews'],
                'sessions' => (int) $dbc['sessions'],
                'assessments' => (int) $dbc['assessments'],
                'progress' => (int) $dbc['progress'],
                'avg_score' => (int) $dbc['avg_score'],
                'updated_at' => timeAgo($dbc['updated_at'] ?? date('Y-m-d H:i:s')),
                'rejection_note' => $isRej ? ($courseModel->getCourseRejectionNote($dbc['id'])['note'] ?? 'Perlu revisi segera') : null,
            ];
        }

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'user' => $teacherUser,
            'courses' => $formattedDbCourses,
            'current_filter' => $filterStatus,
        ];

        $this->view('DX_LMS_M_Teacher_Course', $data);
    }

    /**
     * Menampilkan Detail Course (Pusat Kendali Pengajaran)
     */
    public function detail()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseId = !empty($_GET['course_id']) ? (int)$_GET['course_id'] : 1;
        $courseModel = new DX_LMS_M_Courses();
        $course = $courseModel->getCourseDetail($courseId);

        // Keamanan: Validasi bahwa course ada dan dimiliki oleh pengajar ini
        if (!$course || (int)$course['created_by'] !== $teacherId) {
            header('Location: ?route=/teacher/courses');
            exit();
        }

        // Ambil silabus / sesi dari database jika ada
        $dbSessions = $courseModel->getCourseSessionsWithContent($courseId);
        $syllabus = [];
        if (!empty($dbSessions)) {
            foreach ($dbSessions as $s) {
                $syllabus[] = [
                    'id' => $s['session_id'],
                    'session_id' => $s['session_id'],
                    'title' => $s['session_title'],
                    'description' => $s['session_description'] ?? '',
                    'order' => $s['session_order'] ?? 1,
                    'materials' => count($s['materials'] ?? []),
                    'materials_list' => $s['materials'] ?? [],
                    'assesments' => count($s['quizzes'] ?? []),
                    'quizzes_list' => $s['quizzes'] ?? [],
                ];
            }
        }

        // Ambil peserta riil dari database
        $dbParticipants = $courseModel->getCourseParticipants($courseId);
        $participants = !empty($dbParticipants) ? $dbParticipants : [];

        // Ambil riwayat audit log aktivitas course
        $dbLogs = dbQuery("SELECT TOP 10 activity_type, description, created_at FROM DX_LMS_T_Activity_Logs WHERE reference_id = ? ORDER BY log_id DESC", [(string)$courseId]);
        $courseActivities = [];
        if (!empty($dbLogs)) {
            foreach ($dbLogs as $l) {
                $courseActivities[] = [
                    'action' => $l['description'],
                    'time' => date('d M Y, H:i', strtotime($l['created_at'])),
                    'icon' => 'history',
                    'color_class' => 'bg-primary'
                ];
            }
        }

        $tab = $_GET['tab'] ?? 'silabus';

        require_once 'Models/DX_LMS_M_Forum.php';
        $forumModel = new DX_LMS_M_Forum();
        $forumTopics = $forumModel->getTopics($courseId);

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'user' => $teacherUser,
            'course' => $course,
            'syllabus' => $syllabus,
            'participants' => $participants,
            'activities' => $courseActivities,
            'tab' => $tab,
            'forumTopics' => $forumTopics,
        ];

        $this->view('DX_LMS_T_Teacher_Course_Detail', $data);
    }

    /**
     * Menampilkan Form Create Course / Edit (Wizard 4-Step)
     */
    public function create()
    {
        $teacherUser = $this->redirectIfNotTeacher();

        $courseId = !empty($_GET['course_id']) ? (int) $_GET['course_id'] : null;
        $course = null;
        $existingSessions = [];
        $courseModel = new DX_LMS_M_Courses();
        $empModel = new DX_LMS_M_Employees();

        if ($courseId) {
            $course = $courseModel->getCourseDetail($courseId);
            if ($course) {
                // Keamanan: Validasi kepemilikan course
                $teacherId = (int) ($teacherUser['employee_id'] ?? 2);
                if ((int) $course['created_by'] !== $teacherId && ($teacherUser['role'] ?? '') !== 'admin') {
                    header('Location: ?route=/teacher/courses');
                    exit();
                }
                $existingSessions = $courseModel->getCourseSessionsWithContent($courseId);
            }
        }

        // Ambil filter dan kandidat karyawan riil dari SSMS database
        $filters = $empModel->getTargetFilterOptions();
        $candidates = $empModel->getCandidatesForTargeting();

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'user' => $teacherUser,
            'course' => $course,
            'existing_sessions' => $existingSessions,
            'filters' => $filters,
            'candidates' => $candidates,
        ];

        $this->view('DX_LMS_T_Teacher_Course_Create', $data);
    }

    /**
     * Menyimpan Course Baru ke Database SQL Server (SSMS).
     * Endpoint API POST /teacher/course/store
     */
    public function store()
    {
        $teacherUser = $this->redirectIfNotTeacher();

        header('Content-Type: application/json; charset=utf-8');

        // Terima input JSON mentah (raw body) atau POST parameter
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!$payload && !empty($_POST['course_data_json'])) {
            $payload = json_decode($_POST['course_data_json'], true);
        }

        if (!$payload) {
            $payload = $_POST;
        }

        if (empty($payload) || !is_array($payload)) {
            echo json_encode([
                'success' => false,
                'message' => 'Data formulir tidak valid atau kosong.'
            ]);
            exit();
        }

        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->createCourse($payload, $teacherId);

        echo json_encode($result);
        exit();
    }

    public function session()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $data = ['active_tab' => 'course', 'base_path' => '', 'user' => $teacherUser];
        $this->view('DX_LMS_M_Teacher_Course_Session', $data);
    }

    public function material()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $data = ['active_tab' => 'course', 'base_path' => '', 'user' => $teacherUser];
        $this->view('DX_LMS_M_Teacher_Course_Material', $data);
    }

    /**
     * Membatalkan pengajuan course dari 'Submitted' kembali ke 'Draft' (Membuka kembali hak edit pengajar).
     */
    public function revertDraft()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $courseId = $_GET['course_id'] ?? ($_POST['course_id'] ?? null);

        if (!$courseId) {
            header('Location: ?route=/teacher/courses');
            exit();
        }

        try {
            $course = dbQueryOne("SELECT course_id, title, course_status, created_by FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
            if ($course && ($course['course_status'] === 'Submitted' || $course['course_status'] === 'Rejected')) {
                dbExecute("UPDATE DX_LMS_M_Courses SET course_status = 'Draft', updated_at = GETDATE() WHERE course_id = ?", [$courseId]);

                dbExecute("
                    INSERT INTO DX_LMS_T_Activity_Logs 
                    (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                    VALUES (?, 'revert_draft', ?, ?, ?, ?, GETDATE())
                ", [
                    $teacherUser['employee_id'] ?? 2,
                    (string)$courseId,
                    "Pengajar menarik kembali pengajuan course '{$course['title']}' ke status Draft untuk penyuntingan",
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Teacher Portal'
                ]);
            }
        } catch (Throwable $e) {
            error_log("revertDraft error: " . $e->getMessage());
        }

        header('Location: ?route=/teacher/course/detail&course_id=' . urlencode((string)$courseId));
        exit();
    }

    /**
     * Menghapus Course milik Pengajar secara permanen (Hard Delete).
     */
    public function delete()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = $_GET['id'] ?? ($input['id'] ?? ($_POST['id'] ?? null));

        if (!$courseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID Course tidak valid.'], 400);
            return;
        }

        $course = dbQueryOne("SELECT course_id, created_by, course_status, title FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
        if (!$course) {
            $this->jsonResponse(['success' => false, 'error' => 'Course tidak ditemukan di database.'], 404);
            return;
        }

        // Validasi hak akses: hanya pembuat atau admin
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);
        if ((int)$course['created_by'] !== $teacherId && ($teacherUser['role'] ?? '') !== 'admin') {
            $this->jsonResponse(['success' => false, 'error' => 'Anda tidak memiliki hak untuk menghapus course ini.'], 403);
            return;
        }

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->deleteCourse($courseId);

        if ($result['success']) {
            $this->jsonResponse($result);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['message']], 500);
        }
    }

    /**
     * API Menyimpan Sesi (In-Place)
     */
    public function saveSessionApi()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $courseId = (int) ($input['course_id'] ?? 0);
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->saveCourseSession($courseId, $teacherId, $input);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * API Menghapus Sesi
     */
    public function deleteSessionApi()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $sessionId = (int) ($input['session_id'] ?? ($_GET['session_id'] ?? 0));
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->deleteCourseSession($sessionId, $teacherId);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * API Menyimpan Materi (In-Place)
     */
    public function saveMaterialApi()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $sessionId = (int) ($input['session_id'] ?? 0);
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->saveCourseMaterial($sessionId, $teacherId, $input);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * API Menghapus Materi
     */
    public function deleteMaterialApi()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $materialId = (int) ($input['material_id'] ?? ($_GET['material_id'] ?? 0));
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->deleteCourseMaterial($materialId, $teacherId);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * API Menyimpan Kuis (In-Place)
     */
    public function saveQuizApi()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $courseId = (int) ($input['course_id'] ?? 0);
        $sessionId = (int) ($input['session_id'] ?? 0);
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->saveCourseQuiz($courseId, $sessionId, $teacherId, $input);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * API Menghapus Kuis
     */
    public function deleteQuizApi()
    {
        $teacherUser = $this->redirectIfNotTeacher();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $quizId = (int) ($input['quiz_id'] ?? ($_GET['quiz_id'] ?? 0));
        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->deleteCourseQuiz($quizId, $teacherId);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    private function redirectIfNotTeacher()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherUser = getPortalUser('teacher');
        if (!$teacherUser) {
            header('Location: ?route=/login');
            exit();
        }
        if (empty($teacherUser['employee_id'])) {
            $emp = dbQueryOne("SELECT employee_id, role, jabatan, golongan FROM DX_LMS_M_Employees WHERE npk = ?", [$teacherUser['npk'] ?? '']);
            $teacherUser['employee_id'] = (int) ($emp['employee_id'] ?? 2);
            $teacherUser['id'] = $teacherUser['employee_id'];
            $_SESSION['user']['employee_id'] = $teacherUser['employee_id'];
            $_SESSION['user']['id'] = $teacherUser['employee_id'];
            if (isset($_SESSION['roles']['teacher'])) {
                $_SESSION['roles']['teacher']['employee_id'] = $teacherUser['employee_id'];
                $_SESSION['roles']['teacher']['id'] = $teacherUser['employee_id'];
            }
        }
        return $teacherUser;
    }
}
