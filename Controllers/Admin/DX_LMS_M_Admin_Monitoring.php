<?php
/**
 * Controller untuk Learning Monitoring (Stage 2).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Monitoring extends DX_LMS_M_Controller
{
    public function index()
    {
        $monitoring_data = DX_LMS_M_Admin_MockData::getMonitoringData();
        $stats = DX_LMS_M_Admin_MockData::getDashboardStats();

        $this->view('Admin/DX_LMS_M_Admin_Monitoring', [
            'monitoring_data' => $monitoring_data,
            'stats' => $stats,
        ]);
    }
}
