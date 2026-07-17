<?php
/**
 * Controller untuk Notification Center (Stage 2).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Notification extends DX_LMS_M_Controller
{
    public function index()
    {
        $notifications = DX_LMS_M_Admin_MockData::getNotifications();

        $this->view('Admin/DX_LMS_M_Admin_Notification', [
            'notifications' => $notifications,
        ]);
    }
}
