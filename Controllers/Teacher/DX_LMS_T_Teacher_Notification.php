<?php
/**
 * Kelas Pengontrol Notifikasi Pengajar (Teacher Notification Controller).
 */
class DX_LMS_T_Teacher_Notification extends DX_LMS_M_Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherUser = getPortalUser('teacher');
        if (!$teacherUser) {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';
        $notifications = DX_LMS_M_Teacher_MockData::getNotifications();

        $data = [
            'active_tab' => 'notifikasi',
            'base_path' => '',
            'user' => $teacherUser,
            'notifications' => $notifications,
        ];

        $this->view('DX_LMS_T_Teacher_Notification', $data);
    }
}
