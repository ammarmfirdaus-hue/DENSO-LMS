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
}
