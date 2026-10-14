<?php
/**
 * Controller untuk Detail Course Admin (Drill-down).
 * Memuat informasi kurikulum, materi, kuis, dan usulan peserta langsung dari database SQL Server.
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Config.php';
require_once 'includes/DX_LMS_M_Db.php';
require_once 'Models/DX_LMS_M_Courses.php';
require_once 'includes/DX_LMS_M_Teacher_MockData.php';

class DX_LMS_M_Admin_Course_Detail extends DX_LMS_M_Controller
{
    public function index()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID Course tidak valid. <a href='?route=/admin/course'>Kembali</a>";
            return;
        }

        $courseModel = new DX_LMS_M_Courses();
        $course = $courseModel->getCourseDetail($id);

        if (!$course) {
            $course = DX_LMS_M_Teacher_MockData::getCourseDetail($id);
        }

        if (!$course) {
            echo "Data Course tidak ditemukan. <a href='?route=/admin/course'>Kembali</a>";
            return;
        }

        // Map status to match Manajemen Course display status
        $raw = $course['status'] ?? 'Draft';
        $course['status'] = match ($raw) {
            'Draft' => 'Private',
            'Submitted' => 'Pending Review',
            'Approved', 'Active', 'Running' => 'In Progress',
            'Completed' => 'Completed',
            'Archived' => 'Archived',
            'Rejected' => 'Rejected',
            default => $raw,
        };

        // Ambil silabus & modul sesi riil dari database jika ada
        $dbSessions = $courseModel->getCourseSessionsWithContent($id);
        $syllabus = [];
        if (!empty($dbSessions)) {
            foreach ($dbSessions as $s) {
                $firstMat = $s['materials'][0] ?? null;
                $syllabus[] = [
                    'id' => $s['session_id'],
                    'title' => $s['session_title'],
                    'materials' => count($s['materials'] ?? []),
                    'assesments' => count($s['quizzes'] ?? []),
                    'type' => $firstMat ? ucfirst($firstMat['material_type']) : 'Dokumen',
                    'duration' => '30 Menit',
                ];
            }
        }

        // Ambil peserta riil dari database jika ada
        $dbParticipants = $courseModel->getCourseParticipants($id);
        $participants = !empty($dbParticipants) ? $dbParticipants : [];

        // Ambil aktivitas audit riil
        $dbLogs = dbQuery("SELECT TOP 10 activity_type, description, created_at FROM DX_LMS_T_Activity_Logs WHERE reference_id = ? ORDER BY log_id DESC", [(string)$id]);
        $activities = [];
        if (!empty($dbLogs)) {
            foreach ($dbLogs as $l) {
                $activities[] = [
                    'action' => $l['description'],
                    'time' => date('d M Y, H:i', strtotime($l['created_at'])),
                    'icon' => 'history',
                    'color_class' => 'bg-primary'
                ];
            }
        }

        $this->view('Admin/DX_LMS_M_Admin_Course_Detail', [
            'course' => $course,
            'syllabus' => $syllabus,
            'participants' => $participants,
            'activities' => $activities,
        ]);
    }
}
