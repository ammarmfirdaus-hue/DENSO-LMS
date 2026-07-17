<?php
/**
 * Controller untuk Dasbor Admin (Tahap 1 & 2).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Dashboard extends DX_LMS_M_Controller
{
    public function index()
    {
        // Ambil mock data untuk visualisasi
        $stats = DX_LMS_M_Admin_MockData::getDashboardStats();
        $recent_users = array_slice(DX_LMS_M_Admin_MockData::getUsers(), 0, 3);
        $recent_courses = array_slice(DX_LMS_M_Admin_MockData::getCourses(), 0, 3);

        // Stage 2 Mock Data
        $recent_activities = array_slice(DX_LMS_M_Admin_MockData::getActivityLogs(), 0, 4);
        $notifications = array_slice(DX_LMS_M_Admin_MockData::getNotifications(), 0, 2);

        $this->view('Admin/DX_LMS_M_Admin_Dashboard', [
            'stats' => $stats,
            'recent_users' => $recent_users,
            'recent_courses' => $recent_courses,
            'recent_activities' => $recent_activities,
            'notifications' => $notifications,
        ]);
    }
}
