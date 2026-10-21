<?php
/**
 * Controller untuk Penugasan Course Admin ke Teacher (Assignment Flow).
 *
 * Mengelola alur penugasan dari Admin ke Pengajar:
 * - Pemilihan Course dan Pengajar PIC
 * - Penentuan Kriteria Target Audiens (Departemen, Jabatan, Golongan)
 * - Persistensi Transaksi ke SQL Server (Courses, Course_Targets, Notifications, Activity_Logs)
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'Models/DX_LMS_M_Employees.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';
require_once 'includes/DX_LMS_M_Db.php';

class DX_LMS_M_Admin_Assignment extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Pusat Penugasan Course.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $adminUser = getPortalUser('admin');
        if (!$adminUser) {
            if (isset($_SESSION['user'])) {
                header('Location: ?route=/pilih-peran');
            } else {
                header('Location: ?route=/login');
            }
            exit();
        }

        $empModel = new DX_LMS_M_Employees();
        $teachers = $empModel->getTeachers();

        // Ambil data referensi master untuk modal
        $departments = dbQuery("SELECT department_id, department_name FROM DX_LMS_M_Departments WHERE department_status = 'Active' ORDER BY department_name ASC");
        $positions = dbQuery("SELECT position_id, position_name FROM DX_LMS_M_Positions WHERE position_status = 'Active' ORDER BY position_id ASC");
        $golongans = dbQuery("SELECT golongan_id, golongan_name FROM DX_LMS_M_Golongan WHERE golongan_status = 'Active' ORDER BY golongan_level ASC");

        // Ambil data riil penugasan dari database
        $dbAssignments = [];
        try {
            $sql = "
                SELECT 
                    ct.course_target_id AS id,
                    c.course_id,
                    c.course_code,
                    c.title AS course_title,
                    c.course_status,
                    ct.target_type,
                    ct.target_status AS status,
                    ct.created_at,
                    teacher_emp.fullname AS teacher_name,
                    admin_emp.fullname AS assigned_by,
                    p.position_name,
                    g.golongan_name,
                    d.department_name,
                    (
                        SELECT COUNT(*) FROM DX_LMS_T_Course_Enrollments en WHERE en.course_id = c.course_id
                    ) AS est_participants
                FROM DX_LMS_M_Course_Targets ct
                INNER JOIN DX_LMS_M_Courses c ON ct.course_id = c.course_id
                LEFT JOIN DX_LMS_M_Employees teacher_emp ON c.created_by = teacher_emp.employee_id
                LEFT JOIN DX_LMS_M_Employees admin_emp ON ct.assigned_by = admin_emp.employee_id
                LEFT JOIN DX_LMS_M_Positions p ON ct.position_id = p.position_id
                LEFT JOIN DX_LMS_M_Golongan g ON ct.golongan_id = g.golongan_id
                LEFT JOIN DX_LMS_M_Departments d ON ct.department_id = d.department_id
                ORDER BY ct.course_target_id DESC
            ";
            $rawDbRows = dbQuery($sql);

            foreach ($rawDbRows as $r) {
                $targetParts = [];
                if (!empty($r['department_name'])) $targetParts[] = $r['department_name'];
                if (!empty($r['position_name'])) $targetParts[] = $r['position_name'];
                if (!empty($r['golongan_name'])) $targetParts[] = $r['golongan_name'];
                $targetVal = !empty($targetParts) ? implode(' • ', $targetParts) : 'Seluruh Target Karyawan';

                $dbAssignments[] = [
                    'id' => 'db_' . $r['id'],
                    'course_id' => $r['course_id'],
                    'course_title' => $r['course_title'],
                    'teacher_name' => $r['teacher_name'] ?? 'Pengajar',
                    'target_type' => ($r['target_type'] === 'Corporate_Mandate' ? 'Mandat Korporat' : 'Audience'),
                    'target_value' => $targetVal,
                    'assigned_by' => $r['assigned_by'] ?? 'Admin LMS',
                    'date' => date('d M Y', strtotime($r['created_at'])),
                    'status' => ($r['course_status'] === 'Draft' ? 'Draft' : 'Active'),
                    'est_participants' => (int) $r['est_participants'],
                ];
            }
        } catch (\Throwable $e) {
            error_log("DX_LMS_M_Admin_Assignment::index DB Error: " . $e->getMessage());
        }

        // Gabungkan dengan data mock agar tabel selalu memiliki visualisasi komprehensif
        $mockAssignments = DX_LMS_M_Admin_MockData::getAssignments();
        $assignments = array_merge($dbAssignments, $mockAssignments);

        $this->view('Admin/DX_LMS_M_Admin_Assignment', [
            'user' => $adminUser,
            'assignments' => $assignments,
            'teachers' => $teachers,
            'departments' => $departments,
            'positions' => $positions,
            'golongans' => $golongans,
        ]);
    }

    /**
     * Menyimpan Penugasan Baru dari Admin ke Teacher.
     * Endpoint API POST ?route=/admin/assignment/store
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');

        $adminUser = getPortalUser('admin');
        if (!$adminUser) {
            echo json_encode([
                'success' => false,
                'message' => 'Akses ditolak: Hanya pengguna dengan peran Admin yang berhak membuat penugasan.'
            ]);
            exit();
        }

        $adminId = (int) ($adminUser['employee_id'] ?? 4);

        // Ambil payload JSON atau POST
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) {
            $data = $_POST;
        }

        $title = trim($data['title'] ?? '');
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        $category = trim($data['category'] ?? 'Mandat Korporat');
        $slaDays = (int) ($data['sla_days'] ?? 14);
        $notes = trim($data['notes'] ?? '');

        $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
        $posId = !empty($data['position_id']) ? (int) $data['position_id'] : null;
        $golId = !empty($data['golongan_id']) ? (int) $data['golongan_id'] : null;

        // Validasi input
        if (empty($title) || strlen($title) < 3) {
            echo json_encode(['success' => false, 'message' => 'Judul course wajib diisi minimal 3 karakter.']);
            exit();
        }

        if ($teacherId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Pilih Pengajar (Teacher) yang ditugaskan.']);
            exit();
        }

        // Verifikasi pengajar valid di database
        $teacher = dbQueryOne("SELECT employee_id, fullname, email, npk FROM DX_LMS_M_Employees WHERE employee_id = ? AND status = 'Active'", [$teacherId]);
        if (!$teacher) {
            echo json_encode(['success' => false, 'message' => 'Pengajar yang dipilih tidak ditemukan dalam sistem.']);
            exit();
        }

        if ($slaDays <= 0 || $slaDays > 365) {
            $slaDays = 14;
        }

        $pdo = getDBConnection();
        $pdo->beginTransaction();

        try {
            // 1. Generate Kode Course Unik
            $cleanTitle = preg_replace('/[^A-Za-z0-9]/', '', $title);
            $prefix = 'MND-' . strtoupper(substr($cleanTitle, 0, 3));
            if (strlen($prefix) < 7) {
                $prefix = str_pad($prefix, 7, 'X');
            }
            $courseCode = $prefix . '-' . rand(100, 999);
            $check = dbQueryOne("SELECT course_id FROM DX_LMS_M_Courses WHERE course_code = ?", [$courseCode]);
            if ($check) {
                $courseCode = $prefix . '-' . rand(1000, 9999);
            }

            $description = !empty($notes) 
                ? "Mandat Penugasan Admin (SLA {$slaDays} Hari): " . $notes 
                : "Penugasan Course Mandat Korporat dari Admin LMS (SLA {$slaDays} Hari).";

            // 2. Simpan Course berstatus 'Draft' atas nama Pengajar
            $stmtCourse = $pdo->prepare("
                INSERT INTO DX_LMS_M_Courses 
                (course_code, title, description, category, course_status, passing_grade, created_by, updated_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'Draft', 75, ?, ?, GETDATE(), GETDATE())
            ");
            $stmtCourse->execute([
                $courseCode,
                $title,
                $description,
                $category,
                $teacherId,
                $adminId
            ]);
            $courseId = (int) $pdo->lastInsertId();

            // 3. Simpan Kriteria Target Mandat Korporat ke DX_LMS_M_Course_Targets
            $stmtTarget = $pdo->prepare("
                INSERT INTO DX_LMS_M_Course_Targets 
                (course_id, position_id, golongan_id, department_id, assigned_by, target_type, target_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'Corporate_Mandate', 'Active', GETDATE(), GETDATE())
            ");
            $stmtTarget->execute([
                $courseId,
                $posId,
                $golId,
                $deptId,
                $adminId
            ]);

            // 4. Kirimkan Notifikasi ke Pengajar
            $notifTitle = "Penugasan Baru dari Admin: {$title}";
            $notifMsg = "Admin telah menugaskan Anda untuk menyusun materi course '{$title}'. Target SLA: {$slaDays} hari kerja." . (!empty($notes) ? " Catatan: \"{$notes}\"" : "");

            $stmtNotif = $pdo->prepare("
                INSERT INTO DX_LMS_T_Notifications 
                (employee_id, notification_title, notification_message, notification_type, is_read, created_at, updated_at)
                VALUES (?, ?, ?, 'assignment', 0, GETDATE(), GETDATE())
            ");
            $stmtNotif->execute([$teacherId, $notifTitle, $notifMsg]);

            // 5. Catat Log Aktivitas Pengajar & Admin
            $stmtLog = $pdo->prepare("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, GETDATE())
            ");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Internal DLS App', 0, 200);

            // Log untuk Pengajar
            $stmtLog->execute([
                $teacherId,
                'assigned_course',
                (string) $courseId,
                "Menerima mandat penugasan course dari Admin: '{$title}'",
                $ip,
                $ua
            ]);

            // Log untuk Admin
            $stmtLog->execute([
                $adminId,
                'create_assignment',
                (string) $courseId,
                "Menugaskan course '{$title}' kepada {$teacher['fullname']} (SLA {$slaDays} Hari)",
                $ip,
                $ua
            ]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Penugasan course '{$title}' berhasil dibuat. Notifikasi telah terkirim ke {$teacher['fullname']}.",
                'course_id' => $courseId,
                'course_code' => $courseCode
            ]);
            exit();

        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log("DX_LMS_M_Admin_Assignment::store Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Gagal membuat penugasan ke basis data: ' . $e->getMessage()
            ]);
            exit();
        }
    }
}
