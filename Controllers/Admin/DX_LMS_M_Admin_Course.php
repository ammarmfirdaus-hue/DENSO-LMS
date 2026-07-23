<?php
/**
 * Controller untuk Manajemen Course (Tahap 1 MVP).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Course extends DX_LMS_M_Controller
{
    public function index()
    {
        $courses = DX_LMS_M_Admin_MockData::getCourses();

        $this->view('Admin/DX_LMS_M_Admin_Course', [
            'courses' => $courses,
        ]);
    }
}
