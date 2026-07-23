<?php
/**
 * Controller untuk Assignment Flow (Tahap 1 MVP).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Assignment extends DX_LMS_M_Controller
{
    public function index()
    {
        $assignments = DX_LMS_M_Admin_MockData::getAssignments();

        $this->view('Admin/DX_LMS_M_Admin_Assignment', [
            'assignments' => $assignments,
        ]);
    }
}
