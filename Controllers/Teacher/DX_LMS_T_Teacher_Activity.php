<?php
/**
 * Kelas Pengontrol Aktivitas Pengajar (Teacher Activity Controller).
 *
 * Menampilkan linimasa (timeline) aktivitas dari murid-murid yang 
 * mengambil course yang diampu oleh pengajar ini.
 */
class DX_LMS_T_Teacher_Activity extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Aktivitas Pengajar.
     *
     * @return void
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherUser = getPortalUser('teacher');
        if (!$teacherUser) {
            header('Location: ?route=/login');
            exit();
        }

        /** Data dummy aktivitas murid untuk MVP */
        $activities = [
            [
                'student_name' => 'Siti Aminah',
                'action' => 'menyelesaikan kuis',
                'target' => 'Fundamental 5S - Post Test',
                'time' => '10 menit yang lalu',
                'icon' => 'task_alt',
                'color' => 'text-success'
            ],
            [
                'student_name' => 'Budi Santoso',
                'action' => 'memulai course',
                'target' => 'Dasar-dasar K3 Produksi',
                'time' => '1 jam yang lalu',
                'icon' => 'play_circle',
                'color' => 'text-primary'
            ],
            [
                'student_name' => 'Ahmad Rizal',
                'action' => 'bertanya di forum',
                'target' => 'TPM Basics - Modul 1',
                'time' => '2 jam yang lalu',
                'icon' => 'forum',
                'color' => 'text-secondary'
            ],
            [
                'student_name' => 'Dwi Handoko',
                'action' => 'mengumpulkan tugas',
                'target' => 'Studi Kasus QCC',
                'time' => 'Kemarin',
                'icon' => 'upload_file',
                'color' => 'text-warning'
            ]
        ];

        $data = [
            'active_tab' => 'aktivitas',
            'base_path' => '',
            'user' => $teacherUser,
            'activities' => $activities,
        ];

        $this->view('DX_LMS_T_Teacher_Activity', $data);
    }
}
