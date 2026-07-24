<?php
/**
 * Kelas Pengontrol Profil Profil Pengajar (Teacher Profile Controller).
 */
class DX_LMS_T_Teacher_Profile extends DX_LMS_M_Controller
{
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';
        $stats = DX_LMS_M_Teacher_MockData::getTeacherStats();

        $profile = [
            'name' => $_SESSION['user']['fullname'] ?? 'Budi Santoso',
            'npk' => $_SESSION['user']['npk'] ?? '987654321',
            'role' => 'Teacher',
            'department' => 'Manufacturing Training',
            'division' => 'Operation',
            'section' => 'Production A',
            'position' => $_SESSION['user']['jabatan'] ?? 'Leader',
            'golongan' => 'Gol 3',
            'photo_url' =>
                'https://lh3.googleusercontent.com/aida-public/AB6AXuC9cdk8ogJL3w5_NK_GyqBiVTRDCvukSi1wBuUwvyCk4aYPtCY0cq1TqwcHgbQL_oM8zIGtY6Qc9HxNMF6ntprsZAOrebYyekTQ08a6liWME8Mbb84YFlne--pGRw1PuxTtapDDG5vwnrRa4WjzAkxEeFhSj0RlYKQUO04cC-0mb29AzlccPjtNlf0v6vY07qlVED0ce0prmKp_lgKoHDzZjYAQHAOLj8-8tsUeW6lwWTIKJ8KztRWlr5aUBvQcaQkNW-1XTqgHgUA',
        ];

        $data = [
            'active_tab' => 'profil',
            'base_path' => '',
            'profile' => $profile,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_T_Teacher_Profile', $data);
    }

    private function getCommonData()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        require_once 'includes/DX_LMS_M_Teacher_MockData.php';
        $stats = DX_LMS_M_Teacher_MockData::getTeacherStats();

        $profile = [
            'name' => $_SESSION['user']['fullname'] ?? 'Budi Santoso',
            'npk' => $_SESSION['user']['npk'] ?? '987654321',
            'role' => 'Teacher',
            'department' => 'Manufacturing Training',
            'division' => 'Operation',
            'section' => 'Production A',
            'position' => $_SESSION['user']['jabatan'] ?? 'Leader',
            'golongan' => 'Gol 3',
            'photo_url' =>
                'https://lh3.googleusercontent.com/aida-public/AB6AXuC9cdk8ogJL3w5_NK_GyqBiVTRDCvukSi1wBuUwvyCk4aYPtCY0cq1TqwcHgbQL_oM8zIGtY6Qc9HxNMF6ntprsZAOrebYyekTQ08a6liWME8Mbb84YFlne--pGRw1PuxTtapDDG5vwnrRa4WjzAkxEeFhSj0RlYKQUO04cC-0mb29AzlccPjtNlf0v6vY07qlVED0ce0prmKp_lgKoHDzZjYAQHAOLj8-8tsUeW6lwWTIKJ8KztRWlr5aUBvQcaQkNW-1XTqgHgUA',
        ];

        return [
            'profile' => $profile,
            'stats' => $stats,
        ];
    }

    public function detail()
    {
        $common = $this->getCommonData();
        $data = [
            // Add mock elements specific to detail
            'active_tab' => 'profil',
            'base_path' => '',
            'profile' => $common['profile'],
            'stats' => $common['stats'],
            'careerHistory' => [
                [
                    'type' => 'promosi',
                    'position' => 'Senior Teacher',
                    'golongan' => 'Gol 4',
                    'department' => 'Global Training Center',
                    'division' => 'Corporate HR',
                    'section' => 'Leadership Dev',
                    'start_date' => '2024-05-15',
                    'end_date' => null,
                    'description' =>
                        'Dipromosikan dan diangkat menjadi instruktur senior yang menangani pelatihan skala global.',
                ],
                [
                    'type' => 'mutasi',
                    'position' => 'Teacher',
                    'golongan' => 'Gol 3',
                    'department' => 'Manufacturing Training',
                    'division' => 'Operation',
                    'section' => 'Production A',
                    'start_date' => '2023-01-01',
                    'end_date' => '2024-05-14',
                    'description' =>
                        'Rotasi posisi intstuktur dari devisi Quality ke Operation untuk menyelaraskan keahlian.',
                ],
                [
                    'type' => 'promosi',
                    'position' => 'Instructor (QC)',
                    'golongan' => 'Gol 2',
                    'department' => 'Quality Control',
                    'division' => 'Process Audit',
                    'section' => 'Quality Assy',
                    'start_date' => '2021-08-01',
                    'end_date' => '2023-01-01',
                    'description' => 'Menjadi Trainer di ranah mutu atau Quality Control.',
                ],
                [
                    'type' => 'penempatan_awal',
                    'position' => 'Operator Analyst',
                    'golongan' => 'Gol 1',
                    'department' => 'Manufacturing',
                    'division' => 'Operation',
                    'section' => 'Production A',
                    'start_date' => '2020-03-01',
                    'end_date' => '2021-08-01',
                    'description' => 'Bergabung sebagai staf analis proses operasional standar.',
                ],
            ],
            'learningStats' => [
                'active_count' => $common['stats']['active_students'], // Repurposed for UI layout
                'completed_count' => 88, // Custom proxy metric for Teacher
                'avg_score' => $common['stats']['avg_score'],
            ],
        ];
        $this->view('DX_LMS_T_Teacher_Profile_Detail', $data);
    }

    public function pengaturan()
    {
        $common = $this->getCommonData();
        $data = [
            'active_tab' => 'profil',
            'base_path' => '',
            'profile' => $common['profile'],
            'stats' => $common['stats'],
            'loginHistory' => [
                [
                    'device' => 'Chrome - Windows',
                    'time' => 'Baru saja',
                    'location' => 'Jakarta, ID',
                    'ip' => '114.122.95.12',
                    'current' => true,
                ],
                [
                    'device' => 'Safari - iPhone',
                    'time' => '2 jam yang lalu',
                    'location' => 'Jakarta, ID',
                    'ip' => '114.122.95.15',
                    'current' => false,
                ],
            ],
        ];
        $this->view('DX_LMS_T_Teacher_Profile_Pengaturan', $data);
    }

    public function bantuan()
    {
        $common = $this->getCommonData();
        $data = [
            'active_tab' => 'profil',
            'base_path' => '',
            'profile' => $common['profile'],
            'stats' => $common['stats'],
        ];
        $this->view('DX_LMS_T_Teacher_Profile_Bantuan', $data);
    }
}
