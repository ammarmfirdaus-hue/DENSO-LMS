<?php
/**
 * Kelas Pengontrol Dasbor Pengajar (Teacher Dashboard Controller).
 *
 * Berperan sebagai pusat informasi utama bagi pengajar. Menampilkan statistik
 * ringkas dan daftar course yang diampu langsung tersinkronisasi dengan Microsoft SQL Server (SSMS).
 */

require_once 'Models/DX_LMS_M_Courses.php';

class DX_LMS_M_Teacher_Dashboard extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Beranda (Dashboard) Pengajar dengan data riil SSMS.
     *
     * @return void
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherUser = getPortalUser('teacher');
        if (!$teacherUser) {
            if (isset($_SESSION['user'])) {
                header('Location: ?route=/student');
            } else {
                header('Location: ?route=/login');
            }
            exit();
        }

        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);
        $courseModel = new DX_LMS_M_Courses();

        // 1. Ambil daftar course riil milik pengajar dari SSMS
        $rawCourses = $courseModel->getAllForTeacher($teacherId);
        $courses = [];
        $totalAllCourses = count($rawCourses);
        $totalActiveCourses = 0;    // In Progress (Active, Running, Approved)
        $totalDraftCourses = 0;     // Private (Draft)
        $totalSubmittedCourses = 0; // Pending Review (Submitted)
        $totalRejectedCourses = 0;  // Rejected (Butuh Revisi)
        $totalCompletedCourses = 0; // Selesai
        $totalArchivedCourses = 0;  // Diarsipkan

        foreach ($rawCourses as $dbc) {
            $st = strtolower($dbc['status'] ?? 'draft');
            if (in_array($st, ['active', 'running', 'approved'])) {
                $totalActiveCourses++;
            } elseif ($st === 'submitted') {
                $totalSubmittedCourses++;
            } elseif ($st === 'draft') {
                $totalDraftCourses++;
            } elseif ($st === 'rejected') {
                $totalRejectedCourses++;
            } elseif ($st === 'completed') {
                $totalCompletedCourses++;
            } elseif ($st === 'archived') {
                $totalArchivedCourses++;
            }

            $isRej = $st === 'rejected';
            $courses[] = [
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

        // 2. Hitung jumlah peserta aktif riil yang terdaftar pada seluruh course milik pengajar ini
        $activeStudentsRow = dbQueryOne("
            SELECT COUNT(DISTINCT e.employee_id) AS total_students
            FROM DX_LMS_T_Course_Enrollments e
            INNER JOIN DX_LMS_M_Courses c ON e.course_id = c.course_id
            WHERE c.created_by = ?
        ", [$teacherId]);
        $activeStudentsCount = (int) ($activeStudentsRow['total_students'] ?? 0);

        // Ringkasan metrik statistik riil
        $stats = [
            'total_all_courses' => $totalAllCourses,
            'total_course' => $totalAllCourses,
            'total_active_courses' => $totalActiveCourses,
            'total_draft_courses' => $totalDraftCourses,
            'total_rejected_courses' => $totalRejectedCourses,
            'total_completed_courses' => $totalCompletedCourses,
            'active_students' => $activeStudentsCount,
            'pending_review' => $totalSubmittedCourses,
        ];

        // 3. Tugas Prioritas / Perlu Tindak Lanjut Riil
        $pendingActions = [];

        // Deteksi course yang ditolak (Rejected) -> butuh revisi segera
        $rejectedCourses = dbQuery("
            SELECT course_id, title, updated_at 
            FROM DX_LMS_M_Courses 
            WHERE created_by = ? AND LOWER(course_status) = 'rejected'
            ORDER BY updated_at DESC
        ", [$teacherId]);

        foreach ($rejectedCourses as $rc) {
            $rej = $courseModel->getCourseRejectionNote($rc['course_id']);
            $noteText = !empty($rej['note']) ? $rej['note'] : 'Mohon periksa kembali materi dan kurikulum.';

            $pendingActions[] = [
                'type' => 'rejected',
                'title' => 'Revisi Diperlukan: ' . $rc['title'],
                'message' => 'Catatan Admin: "' . $noteText . '"',
                'raw_note' => $noteText,
                'admin_name' => $rej['admin_name'] ?? 'Admin LMS',
                'time_ago' => !empty($rej['date']) ? $rej['date'] : 'Perlu tindakan',
                'link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $rc['course_id']),
                'edit_link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $rc['course_id']),
                'icon' => 'assignment_late',
            ];
        }

        // Deteksi course berstatus Draft yang belum diajukan
        $draftCourses = dbQuery("
            SELECT TOP 2 course_id, title, updated_at 
            FROM DX_LMS_M_Courses 
            WHERE created_by = ? AND LOWER(course_status) = 'draft'
            ORDER BY updated_at DESC
        ", [$teacherId]);

        foreach ($draftCourses as $dc) {
            $pendingActions[] = [
                'type' => 'draft',
                'title' => 'Draft Belum Diajukan: ' . $dc['title'],
                'message' => 'Lengkapi silabus atau target peserta lalu kirimkan ke Admin untuk kurasi.',
                'raw_note' => '',
                'admin_name' => '',
                'time_ago' => !empty($dc['updated_at']) ? 'Draft ' . date('d M Y', strtotime($dc['updated_at'])) : 'Draft tersimpan',
                'link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $dc['course_id']),
                'edit_link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $dc['course_id']),
                'icon' => 'edit_document',
            ];
        }

        $totalPendingActionsCount = count($pendingActions);
        $displayedPendingActions = array_slice($pendingActions, 0, 3);
        $dbLogs = dbQuery("
            SELECT TOP 4 activity_type, reference_id, description, created_at 
            FROM DX_LMS_T_Activity_Logs 
            WHERE employee_id = ? 
            ORDER BY log_id DESC
        ", [$teacherId]);

        $activities = [];
        if (!empty($dbLogs)) {
            foreach ($dbLogs as $l) {
                $actType = strtolower($l['activity_type'] ?? '');
                $icon = 'history';
                $colorClass = 'bg-primary text-white';

                if (str_contains($actType, 'create')) {
                    $icon = 'add_circle';
                    $colorClass = 'bg-emerald-600 text-white';
                } elseif (str_contains($actType, 'delete')) {
                    $icon = 'delete';
                    $colorClass = 'bg-red-600 text-white';
                } elseif (str_contains($actType, 'revert')) {
                    $icon = 'undo';
                    $colorClass = 'bg-amber-600 text-white';
                } elseif (str_contains($actType, 'material') || str_contains($actType, 'session')) {
                    $icon = 'library_books';
                    $colorClass = 'bg-indigo-600 text-white';
                } elseif (str_contains($actType, 'quiz')) {
                    $icon = 'quiz';
                    $colorClass = 'bg-purple-600 text-white';
                }

                $activities[] = [
                    'action' => $l['description'],
                    'target' => 'ID Ref: ' . ($l['reference_id'] ?? '-'),
                    'time' => timeAgo($l['created_at']),
                    'icon' => $icon,
                    'color_class' => $colorClass,
                ];
            }
        }

        $data = [
            'active_tab' => 'beranda',
            'base_path' => '',
            'user' => $teacherUser,
            'stats' => $stats,
            'courses' => array_slice($courses, 0, 6), // Tampilkan 6 course riil terbaru
            'activities' => $activities,
            'pending_actions' => $displayedPendingActions,
            'total_pending_actions' => $totalPendingActionsCount,
        ];

        $this->view('DX_LMS_M_Teacher_Dashboard', $data);
    }
}
