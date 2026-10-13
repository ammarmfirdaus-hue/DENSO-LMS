<?php
/**
 * Kelas Pengontrol Manajemen Course Pengajar (Teacher Course Controller).
 *
 * Mengelola course list, detail course, dan flow penambahan (create wizard).
 */
class DX_LMS_M_Teacher_Course extends DX_LMS_M_Controller
{
    /**
     * Menampilkan daftar course yang diampu.
     */
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';

        $courses = DX_LMS_M_Teacher_MockData::getManagedCourses();
        // Optional filter based on GET param handling
        $filterStatus = $_GET['status'] ?? 'all';
        if ($filterStatus !== 'all') {
            $courses = array_filter($courses, function ($c) use ($filterStatus) {
                return strtolower($c['status']) === strtolower($filterStatus);
            });
        }

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'courses' => $courses,
            'current_filter' => $filterStatus,
        ];

        $this->view('DX_LMS_M_Teacher_Course', $data);
    }

    /**
     * Menampilkan Detail Course (Pusat Kendali Pengajaran)
     */
    public function detail()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';

        $courseId = $_GET['course_id'] ?? 1;
        $course = DX_LMS_M_Teacher_MockData::getCourseDetail($courseId);

        if (!$course) {
            header('Location: ?route=/teacher/courses');
            exit();
        }

        // Dummy data for detail syllabus and participants
        $syllabus = [
            ['id' => 1, 'title' => 'Sesi 1: Pengenalan 5S', 'materials' => 2, 'assesments' => 1],
            ['id' => 2, 'title' => 'Sesi 2: Sort (Seiri)', 'materials' => 1, 'assesments' => 0],
        ];

        $participants = DX_LMS_M_Teacher_MockData::getCourseParticipants($courseId);

        $activities = DX_LMS_M_Teacher_MockData::getActivities();
        $courseActivities = array_filter($activities, function ($a) use ($course) {
            return strpos(strtolower($a['target']), strtolower($course['title'])) !== false;
        });

        $tab = $_GET['tab'] ?? 'silabus';

        require_once 'Models/DX_LMS_M_Forum.php';
        $forumModel = new DX_LMS_M_Forum();
        $forumTopics = $forumModel->getTopics($courseId);

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'course' => $course,
            'syllabus' => $syllabus,
            'participants' => $participants,
            'activities' => $courseActivities,
            'tab' => $tab,
            'forumTopics' => $forumTopics,
        ];

        $this->view('DX_LMS_T_Teacher_Course_Detail', $data);
    }

    /**
     * Menampilkan Form Create Course / Edit (Wizard)
     */
    public function create()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';

        $courseId = $_GET['course_id'] ?? null;
        $course = null;
        if ($courseId) {
            $course = DX_LMS_M_Teacher_MockData::getCourseDetail($courseId);
        }

        $filters = DX_LMS_M_Teacher_MockData::getTargetFilterOptions();

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'course' => $course, // null if new course
            'filters' => $filters,
        ];

        $this->view('DX_LMS_T_Teacher_Course_Create', $data);
    }

    /**
     * Sisa method lama yang belum dipolish phase ini
     *
     */
    public function session()
    {
        $this->redirectIfNotTeacher();
        $data = ['active_tab' => 'course', 'base_path' => ''];
        $this->view('DX_LMS_M_Teacher_Course_Session', $data);
    }

    public function material()
    {
        $this->redirectIfNotTeacher();
        $data = ['active_tab' => 'course', 'base_path' => ''];
        $this->view('DX_LMS_M_Teacher_Course_Material', $data);
    }

    public function addMaterial()
    {
        $this->redirectIfNotTeacher();
        $data = ['active_tab' => 'course', 'base_path' => ''];
        $this->view('DX_LMS_M_Teacher_Course_Add_Material', $data);
    }

    private function redirectIfNotTeacher()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }
    }
}
