<?php
/**
 * Kelas Pengontrol Dasbor Pengajar (Teacher Dashboard Controller).
 *
 * Berperan sebagai pusat informasi utama bagi pengajar. Menampilkan statistik
 * ringkas dan daftar course yang diampu.
 */
class DX_LMS_M_Teacher_Dashboard extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Beranda (Dashboard) Pengajar.
     *
     * @return void
     */
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';

        $stats = DX_LMS_M_Teacher_MockData::getTeacherStats();
        $courses = DX_LMS_M_Teacher_MockData::getManagedCourses();
        $activities = DX_LMS_M_Teacher_MockData::getActivities();
        $notifications = DX_LMS_M_Teacher_MockData::getNotifications();

        // Get recent actions (e.g., pending reviews, rejected recommendations)
        $pendingActions = array_filter($notifications, function ($n) {
            return !$n['is_read'];
        });

        $data = [
            'active_tab' => 'beranda',
            'base_path' => '',
            'stats' => $stats,
            'courses' => array_slice($courses, 0, 3), // Show max 3 on dashboard
            'activities' => array_slice($activities, 0, 4),
            'pending_actions' => array_slice($pendingActions, 0, 2),
        ];

        $this->view('DX_LMS_M_Teacher_Dashboard', $data);
    }
}
