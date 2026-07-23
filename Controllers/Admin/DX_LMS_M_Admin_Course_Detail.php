<?php
/**
 * Controller untuk Detail Course (Drill-down).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Course_Detail extends DX_LMS_M_Controller
{
    public function index()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID Course tidak valid. <a href='?route=/admin/course'>Kembali</a>";
            return;
        }

        $course = DX_LMS_M_Admin_MockData::getCourseDetail($id);

        if (!$course) {
            echo "Data Course tidak ditemukan. <a href='?route=/admin/course'>Kembali</a>";
            return;
        }

        $this->view('Admin/DX_LMS_M_Admin_Course_Detail', [
            'course' => $course,
        ]);
    }
}
