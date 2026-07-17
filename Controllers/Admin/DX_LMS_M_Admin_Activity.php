<?php
/**
 * Controller untuk Activity Center (Stage 2).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Activity extends DX_LMS_M_Controller
{
    public function index()
    {
        $activities = DX_LMS_M_Admin_MockData::getActivityLogs();

        $this->view('Admin/DX_LMS_M_Admin_Activity', [
            'activities' => $activities,
        ]);
    }
}
