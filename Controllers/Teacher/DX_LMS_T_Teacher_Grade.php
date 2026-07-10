<?php
/**
 * Kelas Pengontrol Penilaian Pengajar (Teacher Grade Controller).
 *
 * Mengelola fungsionalitas bagi pengajar untuk melihat dan memberikan nilai
 * kelulusan kepada murid yang mengikuti course mereka.
 */
class DX_LMS_T_Teacher_Grade extends DX_LMS_M_Controller
{
    /**
     * Menampilkan daftar murid dan nilai mereka.
     *
     * @return void
     */
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        // Data dummy untuk murid beserta nilainya
        $students = [
            [
                'id' => 1,
                'name' => 'Siti Aminah',
                'npk' => '65432',
                'post_test' => 85,
                'final_assessment' => 90,
                'status' => 'Lulus',
                'color' => 'text-success'
            ],
            [
                'id' => 2,
                'name' => 'Budi Santoso',
                'npk' => '12345',
                'post_test' => 70,
                'final_assessment' => 75,
                'status' => 'Menunggu Validasi',
                'color' => 'text-warning'
            ],
            [
                'id' => 3,
                'name' => 'Ahmad Rizal',
                'npk' => '67890',
                'post_test' => 40,
                'final_assessment' => 50,
                'status' => 'Tidak Lulus',
                'color' => 'text-error'
            ]
        ];

        $data = [
            'active_tab' => 'course', // Assuming grading is accessed from Course list or it has its own place? Actually, let's keep it under course management
            'base_path' => '',
            'students' => $students,
        ];

        $this->view('DX_LMS_T_Teacher_Grade', $data);
    }
}
