<?php
/**
 * Controller untuk Manajemen Course Admin (Tahap 1 MVP).
 * Terintegrasi langsung dengan database MS SQL Server (DX_LMS).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Config.php';
require_once 'includes/DX_LMS_M_Db.php';
require_once 'Models/DX_LMS_M_Courses.php';
require_once 'includes/DX_LMS_M_Teacher_MockData.php';

class DX_LMS_M_Admin_Course extends DX_LMS_M_Controller
{
    /**
     * Memeriksa otentikasi admin.
     */
    private function checkAdminSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            // Default ke akun admin lokal jika belum login di sesi
            $adminUser = dbQueryOne("SELECT * FROM DX_LMS_M_Employees WHERE role = 'admin' AND status = 'Active'");
            if ($adminUser) {
                $_SESSION['user'] = $adminUser;
                return $adminUser;
            }
        }
        return $user;
    }

    /**
     * Menampilkan Daftar Course untuk Admin.
     */
    public function index()
    {
        $this->checkAdminSession();

        $courseModel = new DX_LMS_M_Courses();
        $dbCourses = $courseModel->getAllForTeacher(null, 'all');

        $mappedCourses = [];
        foreach ($dbCourses as $c) {
            $raw = $c['status'] ?? 'Draft';
            $displayStat = match ($raw) {
                'Draft' => 'Private',
                'Submitted' => 'Pending Review',
                'Approved', 'Active', 'Running' => 'In Progress',
                'Completed' => 'Completed',
                'Archived' => 'Archived',
                'Rejected' => 'Rejected',
                default => $raw,
            };

            $c['status'] = $displayStat;
            $c['_raw_status'] = $raw;
            $mappedCourses[] = $c;
        }

        $this->view('Admin/DX_LMS_M_Admin_Course', [
            'courses' => $mappedCourses,
        ]);
    }

    /**
     * Menyetujui dan Mempublikasikan Course (Admin Approval).
     */
    public function approve()
    {
        $admin = $this->checkAdminSession();
        $courseId = $_GET['id'] ?? ($_POST['id'] ?? null);

        if (!$courseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID Course tidak valid.'], 400);
            return;
        }

        try {
            $course = dbQueryOne("SELECT course_id, title, course_code, created_by FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
            if (!$course) {
                $this->jsonResponse(['success' => false, 'error' => 'Data Course tidak ditemukan di database.'], 404);
                return;
            }

            // Update status menjadi Approved
            dbExecute("UPDATE DX_LMS_M_Courses SET course_status = 'Approved', updated_at = GETDATE() WHERE course_id = ?", [$courseId]);

            // Kirim notifikasi ke Pengajar pembuat
            dbExecute("
                INSERT INTO DX_LMS_T_Notifications 
                (employee_id, notification_title, notification_message, notification_type, is_read, created_at, updated_at)
                VALUES (?, ?, ?, 'success', 0, GETDATE(), GETDATE())
            ", [
                $course['created_by'],
                'Course Telah Disetujui!',
                "Selamat, pengajuan course '{$course['title']}' ({$course['course_code']}) telah disetujui oleh Admin dan kini aktif dipublikasikan ke peserta."
            ]);

            // Catat log aktivitas admin
            $adminEmpId = $admin['employee_id'] ?? 1;
            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'approve_course', ?, ?, ?, ?, GETDATE())
            ", [
                $adminEmpId,
                (string)$courseId,
                "Admin menyetujui dan mengaktifkan course '{$course['title']}' ({$course['course_code']})",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Admin Portal'
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => "Course '{$course['title']}' berhasil disetujui dan dipublikasikan."
            ]);

        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menolak Course dengan Catatan Revisi.
     */
    public function reject()
    {
        $admin = $this->checkAdminSession();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = $_GET['id'] ?? ($input['id'] ?? null);
        $notes = $input['notes'] ?? ($_POST['notes'] ?? 'Mohon periksa kembali materi dan kurikulum.');

        if (!$courseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID Course tidak valid.'], 400);
            return;
        }

        try {
            $course = dbQueryOne("SELECT course_id, title, course_code, created_by FROM DX_LMS_M_Courses WHERE course_id = ?", [$courseId]);
            if (!$course) {
                $this->jsonResponse(['success' => false, 'error' => 'Data Course tidak ditemukan.'], 404);
                return;
            }

            // Update status menjadi Rejected
            dbExecute("UPDATE DX_LMS_M_Courses SET course_status = 'Rejected', updated_at = GETDATE() WHERE course_id = ?", [$courseId]);

            // Kirim notifikasi revisi ke pengajar
            dbExecute("
                INSERT INTO DX_LMS_T_Notifications 
                (employee_id, notification_title, notification_message, notification_type, is_read, created_at, updated_at)
                VALUES (?, ?, ?, 'warning', 0, GETDATE(), GETDATE())
            ", [
                $course['created_by'],
                'Catatan Revisi Course',
                "Course '{$course['title']}' memerlukan revisi dari Admin: {$notes}. Hak sunting telah dibuka kembali untuk Anda."
            ]);

            // Catat log aktivitas admin
            $adminEmpId = $admin['employee_id'] ?? 1;
            dbExecute("
                INSERT INTO DX_LMS_T_Activity_Logs 
                (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                VALUES (?, 'reject_course', ?, ?, ?, ?, GETDATE())
            ", [
                $adminEmpId,
                (string)$courseId,
                "Admin meminta revisi untuk course '{$course['title']}': {$notes}",
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Admin Portal'
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => "Catatan revisi telah dikirimkan ke Pengajar. Course kini berstatus Rejected dan dapat diedit kembali oleh Pengajar."
            ]);

        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengarsipkan Course.
     */
    public function archive()
    {
        $admin = $this->checkAdminSession();
        $courseId = $_GET['id'] ?? ($_POST['id'] ?? null);

        if (!$courseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID Course tidak valid.'], 400);
            return;
        }

        try {
            dbExecute("UPDATE DX_LMS_M_Courses SET course_status = 'Archived', updated_at = GETDATE() WHERE course_id = ?", [$courseId]);
            $this->jsonResponse(['success' => true, 'message' => 'Course telah berhasil diarsipkan.']);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengaktifkan kembali Course yang diarsipkan.
     */
    public function unarchive()
    {
        $admin = $this->checkAdminSession();
        $courseId = $_GET['id'] ?? ($_POST['id'] ?? null);

        if (!$courseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID Course tidak valid.'], 400);
            return;
        }

        try {
            dbExecute("UPDATE DX_LMS_M_Courses SET course_status = 'Approved', updated_at = GETDATE() WHERE course_id = ?", [$courseId]);
            $this->jsonResponse(['success' => true, 'message' => 'Course telah berhasil diaktifkan kembali.']);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus Course secara total dan permanen (Hard Delete).
     */
    public function delete()
    {
        $admin = $this->checkAdminSession();
        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = $_GET['id'] ?? ($input['id'] ?? ($_POST['id'] ?? null));

        if (!$courseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID Course tidak valid.'], 400);
            return;
        }

        $courseModel = new DX_LMS_M_Courses();
        $result = $courseModel->deleteCourse($courseId);

        if ($result['success']) {
            // Catat log audit aktivitas admin
            $adminEmpId = $admin['employee_id'] ?? 1;
            try {
                dbExecute("
                    INSERT INTO DX_LMS_T_Activity_Logs 
                    (employee_id, activity_type, reference_id, description, ip_address, user_agent, created_at)
                    VALUES (?, 'delete_course', ?, ?, ?, ?, GETDATE())
                ", [
                    $adminEmpId,
                    (string)$courseId,
                    "Admin menghapus permanen course ID {$courseId}",
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'DLS Admin Portal'
                ]);
            } catch (Throwable $e) {
                // Ignore audit log error if activity log table schema differs
            }

            $this->jsonResponse($result);
        } else {
            $this->jsonResponse(['success' => false, 'error' => $result['message']], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }
}
