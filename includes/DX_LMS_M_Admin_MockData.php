<?php
/**
 * Penyedia Data Dummy Global (Mock) untuk Admin MVP (Stage 1).
 * Memastikan interaksi antar menu menggunakan data yang tersinkronisasi.
 */

class DX_LMS_M_Admin_MockData
{
    public static function getDashboardStats()
    {
        return [
            'total_users' => 124,
            'total_students' => 110,
            'total_teachers' => 12,
            'total_teacher_associates' => 2,
            'course_active' => 15,
            'course_draft' => 5,
            'course_published' => 10,
            'course_completed' => 45,
            'pending_assignment' => 8,
            'unread_notifs' => 3,
        ];
    }

    public static function getUsers()
    {
        return [
            [
                'id' => 1,
                'npk' => '12345',
                'name' => 'Budi Santoso',
                'email' => 'budi.s@perusahaan.co.id',
                'role' => 'Student',
                'access' => [],
                'jabatan' => 'Operator',
                'golongan' => 'Gol 1',
                'departemen' => 'Manufacturing',
                'divisi' => 'Operation',
                'seksi' => 'Production A',
                'status' => 'Active',
                'last_login' => '2 Jam yang lalu',
            ],
            [
                'id' => 2,
                'npk' => '67890',
                'name' => 'Ahmad Rizal',
                'email' => 'ahmad.r@perusahaan.co.id',
                'role' => 'Teacher',
                'access' => ['Student'],
                'jabatan' => 'Leader',
                'golongan' => 'Gol 3',
                'departemen' => 'Manufacturing',
                'divisi' => 'Operation',
                'seksi' => 'Production A',
                'status' => 'Active',
                'last_login' => '5 Menit yang lalu',
            ],
            [
                'id' => 3,
                'npk' => '11223',
                'name' => 'Siti Aminah',
                'email' => 'siti.a@perusahaan.co.id',
                'role' => 'Student',
                'access' => [],
                'jabatan' => 'Staff',
                'golongan' => 'Gol 2',
                'departemen' => 'Quality Control',
                'divisi' => 'QA',
                'seksi' => 'Inspection',
                'status' => 'Inactive',
                'last_login' => '3 Hari yang lalu',
            ],
            [
                'id' => 4,
                'npk' => '99887',
                'name' => 'Tono Wijaya',
                'email' => 'tono.w@perusahaan.co.id',
                'role' => 'Admin',
                'access' => ['Student', 'Teacher'],
                'jabatan' => 'SPV',
                'golongan' => 'Gol 4',
                'departemen' => 'HR',
                'divisi' => 'Management',
                'seksi' => 'Training',
                'status' => 'Active',
                'last_login' => 'Sekarang',
            ],
            [
                'id' => 5,
                'npk' => '44556',
                'name' => 'Rina Kartika',
                'email' => 'rina.k@perusahaan.co.id',
                'role' => 'Teacher Associate',
                'access' => ['Student'],
                'jabatan' => 'Staff',
                'golongan' => 'Gol 2',
                'departemen' => 'Engineering',
                'divisi' => 'Maintenance',
                'seksi' => 'Machine',
                'status' => 'Active',
                'last_login' => '1 Jam yang lalu',
            ],
        ];
    }

    public static function getCourses()
    {
        return [
            [
                'id' => 101,
                'title' => 'Dasar Keselamatan Kerja (K3)',
                'description' => 'Materi pengenalan area pabrik dan tata tertib keselamatan.',
                'thumbnail_icon' => 'health_and_safety',
                'tags' => ['Wajib', 'All Level'],
                'status' => 'Published',
                'sessions' => 3,
                'participants' => 110,
                'teacher' => 'Ahmad Rizal',
                'progress' => 85,
                'last_update' => '1 Hari yang lalu',
            ],
            [
                'id' => 102,
                'title' => 'Pengenalan ISO 9001:2015',
                'description' => 'Standar manajemen mutu yang berlaku di seluruh area operasional.',
                'thumbnail_icon' => 'verified',
                'tags' => ['Opsional', 'Staff'],
                'status' => 'Draft',
                'sessions' => 5,
                'participants' => 0,
                'teacher' => 'Belum ada',
                'progress' => 0,
                'last_update' => '5 Jam yang lalu',
            ],
            [
                'id' => 103,
                'title' => 'Basic Programmable Logic Controller',
                'description' => 'Instruksi dasar untuk perawatan mesin-mesin industri.',
                'thumbnail_icon' => 'build',
                'tags' => ['Wajib', 'Engineering'],
                'status' => 'Published',
                'sessions' => 8,
                'participants' => 24,
                'teacher' => 'Tono Wijaya',
                'progress' => 40,
                'last_update' => '2 Minggu yang lalu',
            ],
            [
                'id' => 104,
                'title' => '5S & Kaizen Fundamentals',
                'description' => 'Konsep dasar continuous improvement.',
                'thumbnail_icon' => 'eco',
                'tags' => ['Wajib', 'All Level'],
                'status' => 'Completed',
                'sessions' => 4,
                'participants' => 120,
                'teacher' => 'Rina Kartika',
                'progress' => 100,
                'last_update' => '1 Bulan yang lalu',
            ],
        ];
    }

    public static function getRoles()
    {
        return [
            [
                'id' => 'role_1',
                'name' => 'Super Admin',
                'level' => 1,
                'description' => 'Akses penuh seluruh sistem. Otoritas tertinggi.',
                'users_count' => 1,
                'status' => 'Active',
            ],
            [
                'id' => 'role_2',
                'name' => 'Admin',
                'level' => 2,
                'description' => 'Akses kelola user, course, dan operasional utama.',
                'users_count' => 5,
                'status' => 'Active',
            ],
            [
                'id' => 'role_3',
                'name' => 'Teacher',
                'level' => 3,
                'description' => 'Akses pembuat materi, kuis, dan pemantau nilai.',
                'users_count' => 12,
                'status' => 'Active',
            ],
            [
                'id' => 'role_4',
                'name' => 'Teacher Associate',
                'level' => 4,
                'description' => 'Asisten Teacher untuk memeriksa quiz.',
                'users_count' => 2,
                'status' => 'Active',
            ],
            [
                'id' => 'role_5',
                'name' => 'Student',
                'level' => 5,
                'description' => 'Akses default untuk pembelajaran LMS.',
                'users_count' => 104,
                'status' => 'Active',
            ],
        ];
    }

    public static function getAssignments()
    {
        return [
            [
                'id' => 'asg_1',
                'course_title' => 'Dasar Keselamatan Kerja (K3)',
                'target_type' => 'Jabatan & Golongan',
                'target_value' => 'Operator (Gol 1-3)',
                'assigned_by' => 'Tono Wijaya',
                'date' => '12 Jul 2026',
                'status' => 'Active',
            ],
            [
                'id' => 'asg_2',
                'course_title' => 'Basic Programmable Logic Controller',
                'target_type' => 'Departemen',
                'target_value' => 'Engineering',
                'assigned_by' => 'Tono Wijaya',
                'date' => '05 Jul 2026',
                'status' => 'Active',
            ],
            [
                'id' => 'asg_3',
                'course_title' => 'Pengenalan ISO 9001:2015',
                'target_type' => 'Spesifik User',
                'target_value' => '24 Peserta Terpilih',
                'assigned_by' => 'Pending',
                'date' => '-',
                'status' => 'Pending',
            ],
        ];
    }
    public static function getMonitoringData()
    {
        return [
            [
                'id' => 'mon_1',
                'course_id' => 101,
                'course_title' => 'Dasar Keselamatan Kerja (K3)',
                'teacher' => 'Ahmad Rizal',
                'enrolled' => 110,
                'in_progress' => 30,
                'completed' => 80,
                'lagging' => 5,
                'completion_rate' => 72,
                'status' => 'Active',
            ],
            [
                'id' => 'mon_2',
                'course_id' => 103,
                'course_title' => 'Basic Programmable Logic Controller',
                'teacher' => 'Tono Wijaya',
                'enrolled' => 24,
                'in_progress' => 14,
                'completed' => 10,
                'lagging' => 4,
                'completion_rate' => 41,
                'status' => 'Active',
            ],
            [
                'id' => 'mon_3',
                'course_id' => 104,
                'course_title' => '5S & Kaizen Fundamentals',
                'teacher' => 'Rina Kartika',
                'enrolled' => 120,
                'in_progress' => 0,
                'completed' => 120,
                'lagging' => 0,
                'completion_rate' => 100,
                'status' => 'Completed',
            ],
        ];
    }

    public static function getScoreData()
    {
        return [
            [
                'id' => 'scr_1',
                'course_id' => 101,
                'course_title' => 'Dasar Keselamatan Kerja (K3)',
                'student_name' => 'Budi Santoso',
                'student_npk' => '12345',
                'attendance' => '100%',
                'pre_test' => 70,
                'quiz' => 85,
                'post_test' => 90,
                'final_score' => 88,
                'status' => 'Lulus',
            ],
            [
                'id' => 'scr_2',
                'course_id' => 101,
                'course_title' => 'Dasar Keselamatan Kerja (K3)',
                'student_name' => 'Asep Sudrajat',
                'student_npk' => '54321',
                'attendance' => '80%',
                'pre_test' => 60,
                'quiz' => 65,
                'post_test' => 70,
                'final_score' => 68,
                'status' => 'Remedial',
            ],
            [
                'id' => 'scr_3',
                'course_id' => 102,
                'course_title' => 'Basic Programmable Logic Controller',
                'student_name' => 'Siti Aminah',
                'student_npk' => '11223',
                'attendance' => '100%',
                'pre_test' => 85,
                'quiz' => 90,
                'post_test' => 95,
                'final_score' => 92,
                'status' => 'Lulus',
            ],
        ];
    }

    public static function getActivityLogs()
    {
        return [
            [
                'id' => 'act_1',
                'user' => 'Budi Santoso',
                'role' => 'Student',
                'action' => 'menyelesaikan quiz',
                'target' => 'Safety Quiz 1',
                'time' => '10 menit lalu',
                'icon' => 'task_alt',
                'color' => 'success',
            ],
            [
                'id' => 'act_2',
                'user' => 'Tono Wijaya',
                'role' => 'Admin',
                'action' => 'mendesain assignment',
                'target' => 'Engineering Dept',
                'time' => '1 jam lalu',
                'icon' => 'assignment_ind',
                'color' => 'primary',
            ],
            [
                'id' => 'act_3',
                'user' => 'Ahmad Rizal',
                'role' => 'Teacher',
                'action' => 'memperbarui materi',
                'target' => 'Video Safety',
                'time' => '3 jam lalu',
                'icon' => 'update',
                'color' => 'secondary',
            ],
            [
                'id' => 'act_4',
                'user' => 'Siti Aminah',
                'role' => 'Student',
                'action' => 'bertanya di forum',
                'target' => 'PLC Logic Issue',
                'time' => '1 hari lalu',
                'icon' => 'forum',
                'color' => 'warning',
            ],
        ];
    }

    public static function getNotifications()
    {
        return [
            [
                'id' => 'not_1',
                'type' => 'Warning',
                'title' => 'Pending Assignment',
                'message' => 'Course "Pengenalan ISO 9001:2015" menunggu persetujuan delegasi audiens.',
                'time' => '5 Jam lalu',
            ],
            [
                'id' => 'not_2',
                'type' => 'Info',
                'title' => 'Course Selesai',
                'message' => 'Course "5S & Kaizen" telah diarsipkan otomatis setelah seluruh peserta tuntas 100%.',
                'time' => '1 Hari lalu',
            ],
            [
                'id' => 'not_3',
                'type' => 'Success',
                'title' => 'Assessment Direview',
                'message' => 'Teacher Rina Kartika telah menyelesaikan scoring untuk 24 siswa PLC.',
                'time' => '2 Hari lalu',
            ],
        ];
    }

    // ======== DRILL DOWN CONTEXT PROVIDERS (STAGE 2) ========

    public static function getUserDetail($id)
    {
        $users = self::getUsers();
        $user = null;
        foreach ($users as $u) {
            if ((string) $u['id'] === (string) $id) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            return null;
        }

        // Dummy associations based on role
        if ($user['role'] === 'Student') {
            $user['enrolled_courses'] = [
                [
                    'title' => 'Dasar Keselamatan Kerja (K3)',
                    'progress' => 80,
                    'status' => 'In Progress',
                    'course_id' => 101,
                ],
                ['title' => '5S & Kaizen Fundamentals', 'progress' => 100, 'status' => 'Completed', 'course_id' => 104],
            ];
            $user['recent_activities'] = [
                ['action' => 'Menyelesaikan Quiz', 'target' => 'Safety Quiz 1', 'time' => '10 menit lalu'],
                ['action' => 'Bertanya di Forum', 'target' => 'Materi K3', 'time' => '1 hari lalu'],
            ];
        } else {
            $user['teaching_courses'] = [
                ['title' => 'Dasar Keselamatan Kerja (K3)', 'students' => 110, 'course_id' => 101],
            ];
            $user['recent_activities'] = [
                ['action' => 'Mengupdate Materi', 'target' => 'Video Safety', 'time' => '3 jam lalu'],
            ];
        }

        return $user;
    }

    public static function getCourseDetail($id)
    {
        $courses = self::getCourses();
        $course = null;
        foreach ($courses as $c) {
            if ((string) $c['id'] === (string) $id) {
                $course = $c;
                break;
            }
        }

        if (!$course) {
            return null;
        }

        // Dummy contextual drill-down data
        $course['syllabus'] = [
            ['title' => 'Pendahuluan & Konsep Dasar', 'type' => 'Video', 'duration' => '15 Menit'],
            ['title' => 'Quiz Modul 1', 'type' => 'Quiz', 'duration' => '10 Soal'],
            ['title' => 'Studi Kasus Keselamatan', 'type' => 'Reading', 'duration' => '20 Menit'],
        ];

        $course['top_students'] = [
            ['name' => 'Budi Santoso', 'progress' => 100, 'score' => 90, 'user_id' => 1],
            ['name' => 'Siti Aminah', 'progress' => 80, 'score' => 'N/A', 'user_id' => 3],
        ];

        $course['lagging_students'] = [
            ['name' => 'Asep Sudrajat', 'progress' => 10, 'lag_reason' => 'Belum Login 5 Hari', 'user_id' => 999],
        ];

        return $course;
    }
}
