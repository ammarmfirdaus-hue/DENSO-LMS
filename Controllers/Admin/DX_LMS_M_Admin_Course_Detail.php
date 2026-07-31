<?php
/**
 * Controller untuk Detail Course (Drill-down).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Teacher_MockData.php'; // Updated to use Teacher MockData

class DX_LMS_M_Admin_Course_Detail extends DX_LMS_M_Controller
{
    public function index()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID Course tidak valid. <a href='?route=/admin/course'>Kembali</a>";
            return;
        }

        $course = DX_LMS_M_Teacher_MockData::getCourseDetail($id);

        if (!$course) {
            echo "Data Course tidak ditemukan. <a href='?route=/admin/course'>Kembali</a>";
            return;
        }

        // Dummy data for detail syllabus and participants to mirror Teacher view exactly
        $syllabus = [
            [
                'id' => 1,
                'title' => 'Sesi 1: Pengenalan 5S',
                'materials' => 2,
                'assesments' => 1,
                'type' => 'Video',
                'duration' => '45 Menit',
            ],
            [
                'id' => 2,
                'title' => 'Sesi 2: Sort (Seiri)',
                'materials' => 1,
                'assesments' => 0,
                'type' => 'Document',
                'duration' => '30 Menit',
            ],
        ];

        $participants = [
            ['nama' => 'Budi Santoso', 'progress' => 100, 'score' => 90, 'status' => 'Lulus'],
            ['nama' => 'Ahmad Rizal', 'progress' => 50, 'score' => 0, 'status' => 'In Progress'],
        ];

        $activities = DX_LMS_M_Teacher_MockData::getActivities();
        $courseActivities = array_filter($activities, function ($a) use ($course) {
            return strpos(strtolower($a['target']), strtolower($course['title'])) !== false;
        });

        $this->view('Admin/DX_LMS_M_Admin_Course_Detail', [
            'course' => $course,
            'syllabus' => $syllabus,
            'participants' => $participants,
            'activities' => $courseActivities,
        ]);
    }
}
