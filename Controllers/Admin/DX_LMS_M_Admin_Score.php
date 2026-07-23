<?php
/**
 * Controller untuk Score & Attendance (Stage 2).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Score extends DX_LMS_M_Controller
{
    public function index()
    {
        $score_data = DX_LMS_M_Admin_MockData::getScoreData();
        $courses = DX_LMS_M_Admin_MockData::getCourses();

        $this->view('Admin/DX_LMS_M_Admin_Score', [
            'score_data' => $score_data,
            'courses' => $courses,
        ]);
    }
}
