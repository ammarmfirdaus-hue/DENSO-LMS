<?php
/**
 * Kelas Pengontrol Course Pengajar (Teacher Course Controller).
 *
 * Mengelola fungsionalitas bagi pengajar untuk melihat daftar course, 
 * mengelola sesi, dan mengelola materi di dalam course tersebut.
 */
class DX_LMS_M_Teacher_Course extends DX_LMS_M_Controller
{
    private function checkAuth()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }
    }

    /**
     * Menampilkan daftar semua course yang diampu.
     */
    public function index()
    {
        $this->checkAuth();
        
        // MVP: Ambil semua course dummy 
        $courses = DX_LMS_M_Student_Data::getCourses();
        $managedCourses = array_slice($courses, 0, 3); // Dummy filter

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'courses' => $managedCourses,
        ];

        $this->view('DX_LMS_M_Teacher_Course', $data);
    }

    /**
     * Menampilkan halaman kelola sesi dari sebuah course.
     */
    public function session()
    {
        $this->checkAuth();
        $courseId = $_GET['id'] ?? '';
        
        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'courseId' => $courseId,
            // Dummy sessions
            'sessions' => [
                [
                    'id' => 1,
                    'title' => 'Sesi 1: Pengenalan TPM',
                    'date' => '20 Okt 2023',
                    'materials_count' => 3,
                    'quiz_count' => 1
                ],
                [
                    'id' => 2,
                    'title' => 'Sesi 2: OEE Calculation',
                    'date' => '25 Okt 2023',
                    'materials_count' => 2,
                    'quiz_count' => 0
                ]
            ]
        ];

        $this->view('DX_LMS_M_Teacher_Course_Session', $data);
    }

    /**
     * Menampilkan halaman detail/edit materi.
     */
    public function material()
    {
        $this->checkAuth();
        
        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'material' => [
                'id' => 101,
                'title' => 'Slide Presentasi TPM Basics',
                'type' => 'pdf',
                'file_name' => 'TPM_Basics_Modul1.pdf',
                'size' => '2.4 MB'
            ]
        ];

        $this->view('DX_LMS_M_Teacher_Course_Material', $data);
    }

    /**
     * Menampilkan form tambah materi baru.
     */
    public function addMaterial()
    {
        $this->checkAuth();
        
        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'sessionId' => $_GET['session_id'] ?? ''
        ];

        $this->view('DX_LMS_M_Teacher_Course_Add_Material', $data);
    }
}
