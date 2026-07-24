<?php
/**
 * Kelas Pengontrol Notifikasi Pengajar (Teacher Notification Controller).
 */
class DX_LMS_T_Teacher_Notification extends DX_LMS_M_Controller
{
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';
        $notifications = DX_LMS_M_Teacher_MockData::getNotifications();

        $data = [
            'active_tab' => 'notifikasi',
            'base_path' => '',
            'notifications' => $notifications,
        ];

        $this->view('DX_LMS_T_Teacher_Notification', $data);
    }
}
