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
            $disp = getCourseDisplayStatus($dbc['status'] ?? 'Private', $dbc['updated_at'] ?? null);
            if ($disp === 'In Progress' || $disp === 'Approved') {
                $totalActiveCourses++;
            } elseif ($disp === 'Pending Review') {
                $totalSubmittedCourses++;
            } elseif ($disp === 'Private') {
                $totalDraftCourses++;
            } elseif ($disp === 'Rejected') {
                $totalRejectedCourses++;
            } elseif ($disp === 'Completed') {
                $totalCompletedCourses++;
            } elseif ($disp === 'Archived') {
                $totalArchivedCourses++;
            }

            $isRej = $disp === 'Rejected';
            $courses[] = [
                'id' => $dbc['id'],
                'code' => $dbc['code'],
                'title' => $dbc['title'],
                'description' => $dbc['description'],
                'category' => $dbc['category'],
                'status' => $dbc['status'],
                'raw_updated_at' => $dbc['updated_at'] ?? null,
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

        // Hitung notifikasi belum dibaca untuk lonceng header
        $unreadNotifRow = dbQueryOne("SELECT COUNT(*) AS total FROM DX_LMS_T_Notifications WHERE employee_id = ? AND is_read = 0", [$teacherId]);
        $unreadNotifs = (int) ($unreadNotifRow['total'] ?? 0);
        $stats['unread_notifs'] = $unreadNotifs;

        // 3. Tugas Prioritas / Perlu Tindak Lanjut Riil
        $pendingActions = [];

        // Deteksi course MANDAT PENUGASAN DARI ADMIN (Prioritas Tertinggi)
        $mandateCourses = dbQuery("
            SELECT 
                c.course_id, 
                c.course_code, 
                c.title, 
                c.description, 
                c.created_at, 
                c.updated_at,
                ct.assigned_by, 
                admin_emp.fullname AS admin_name,
                d.department_name, 
                p.position_name, 
                g.golongan_name
            FROM DX_LMS_M_Courses c
            INNER JOIN DX_LMS_M_Course_Targets ct ON c.course_id = ct.course_id AND ct.target_type = 'Corporate_Mandate'
            LEFT JOIN DX_LMS_M_Employees admin_emp ON ct.assigned_by = admin_emp.employee_id
            LEFT JOIN DX_LMS_M_Departments d ON ct.department_id = d.department_id
            LEFT JOIN DX_LMS_M_Positions p ON ct.position_id = p.position_id
            LEFT JOIN DX_LMS_M_Golongan g ON ct.golongan_id = g.golongan_id
            WHERE c.created_by = ? AND LOWER(c.course_status) = 'draft'
            ORDER BY c.created_at DESC
        ", [$teacherId]);

        foreach ($mandateCourses as $mc) {
            $targetDesc = [];
            if (!empty($mc['department_name'])) $targetDesc[] = $mc['department_name'];
            if (!empty($mc['position_name'])) $targetDesc[] = $mc['position_name'];
            if (!empty($mc['golongan_name'])) $targetDesc[] = $mc['golongan_name'];
            $targetStr = !empty($targetDesc) ? implode(' • ', $targetDesc) : 'Seluruh Target Terkait';

            $pendingActions[] = [
                'type' => 'corporate_mandate',
                'title' => 'Tugas Penugasan Course: ' . $mc['title'],
                'message' => 'Mandat resmi dari Admin LMS (' . ($mc['admin_name'] ?? 'Admin') . '). Sasaran: ' . $targetStr . '. ' . $mc['description'],
                'raw_note' => $mc['description'],
                'admin_name' => $mc['admin_name'] ?? 'Admin LMS',
                'time_ago' => timeAgo($mc['created_at']),
                'link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $mc['course_id']),
                'edit_link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $mc['course_id']),
                'icon' => 'campaign',
                'badge' => 'Mandat Korporat',
                'cta_text' => 'Mulai Susun Course',
            ];
        }

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
                'cta_text' => 'Perbaiki Course Sekarang',
            ];
        }

        // Deteksi course berstatus Private biasa (Bukan mandat) yang belum diajukan
        $draftCourses = dbQuery("
            SELECT TOP 2 c.course_id, c.title, c.updated_at 
            FROM DX_LMS_M_Courses c
            WHERE c.created_by = ? AND LOWER(c.course_status) IN ('draft', 'private')
              AND c.course_id NOT IN (
                  SELECT course_id FROM DX_LMS_M_Course_Targets WHERE target_type = 'Corporate_Mandate'
              )
            ORDER BY c.updated_at DESC
        ", [$teacherId]);

        foreach ($draftCourses as $dc) {
            $pendingActions[] = [
                'type' => 'private',
                'title' => 'Course Private Belum Diajukan: ' . $dc['title'],
                'message' => 'Lengkapi silabus atau target peserta lalu kirimkan ke Admin untuk kurasi.',
                'raw_note' => '',
                'admin_name' => '',
                'time_ago' => !empty($dc['updated_at']) ? 'Private ' . date('d M Y', strtotime($dc['updated_at'])) : 'Private tersimpan',
                'link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $dc['course_id']),
                'edit_link' => '?route=/teacher/course/create&course_id=' . urlencode((string) $dc['course_id']),
                'icon' => 'edit_document',
                'cta_text' => 'Lanjutkan Edit Course',
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

                if (str_contains($actType, 'assign')) {
                    $icon = 'assignment_turned_in';
                    $colorClass = 'bg-red-600 text-white';
                } elseif (str_contains($actType, 'create')) {
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
            'notif_count' => $unreadNotifs,
        ];

        $this->view('DX_LMS_M_Teacher_Dashboard', $data);
    }
}
