<?php
/**
 * DX_LMS_M_Teacher_MockData (Data Statis Pengajar).
 * Factory-based Dummy Data Generator.
 *
 * Sumber Kebenaran Tunggal (Single Source of Truth) untuk pengelolaan
 * data percobaan (dummy/mock data) pada modul LMS Teacher.
 */
class DX_LMS_M_Teacher_MockData
{
    /** Peta Status Lifecycle Course */
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_RUNNING = 'Running';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_ARCHIVED = 'Archived';

    /**
     * Data sesi tersimpan (Singleton Mock)
     */
    private static function getStorage($key, $default = [])
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['teacher_mock_data'])) {
            $_SESSION['teacher_mock_data'] = [];
        }
        if (!isset($_SESSION['teacher_mock_data'][$key])) {
            $_SESSION['teacher_mock_data'][$key] = $default;
        }
        return $_SESSION['teacher_mock_data'][$key];
    }

    private static function setStorage($key, $data)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['teacher_mock_data'][$key] = $data;
    }

    /**
     * Initialize Mock Data
     */
    public static function initData()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['teacher_mock_version']) || $_SESSION['teacher_mock_version'] < 3) {
            unset($_SESSION['teacher_mock_data']);
            $_SESSION['teacher_mock_version'] = 3;
        }

        $courses = self::getStorage('courses', null);
        if ($courses === null || count($courses) < 6) {
            $defaultCourses = [
                [
                    'id' => 1,
                    'code' => 'K3-101',
                    'title' => 'Fundamental 5S & K3',
                    'description' => 'Dasar-dasar keselamatan kerja dan 5S di lingkungan produksi.',
                    'category' => 'HSE',
                    'status' => self::STATUS_RUNNING,
                    'icon' => 'health_and_safety',
                    'participants' => 45,
                    'pending_reviews' => 2,
                    'sessions' => 6,
                    'assessments' => 1,
                    'progress' => 68,
                    'avg_score' => 84,
                    'updated_at' => '1 Jam yang lalu',
                ],
                [
                    'id' => 3,
                    'code' => 'QC-201',
                    'title' => 'Quality Control Basic',
                    'description' => 'Materi standar QC untuk inspektur baru.',
                    'category' => 'Quality',
                    'status' => self::STATUS_DRAFT,
                    'icon' => 'fact_check',
                    'participants' => 0,
                    'pending_reviews' => 0,
                    'sessions' => 2,
                    'assessments' => 1,
                    'progress' => 0,
                    'avg_score' => 0,
                    'updated_at' => '3 Hari yang lalu',
                ],
                [
                    'id' => 4,
                    'code' => 'LEAN-401',
                    'title' => 'Lean Manufacturing Essentials',
                    'description' => 'Mewujudkan sistem produksi bebas waste.',
                    'category' => 'Production',
                    'status' => self::STATUS_COMPLETED,
                    'icon' => 'build_circle',
                    'participants' => 120,
                    'pending_reviews' => 0,
                    'sessions' => 8,
                    'assessments' => 2,
                    'progress' => 100,
                    'avg_score' => 92,
                    'updated_at' => 'Bulan lalu',
                ],
                [
                    'id' => 5,
                    'code' => 'SAFE-102',
                    'title' => 'Advanced Safety Protocols',
                    'description' => 'Penanganan insiden kimia dan pemadaman api tingkat lanjut.',
                    'category' => 'HSE',
                    'status' => self::STATUS_RUNNING,
                    'icon' => 'local_fire_department',
                    'participants' => 22,
                    'pending_reviews' => 5,
                    'sessions' => 4,
                    'assessments' => 1,
                    'progress' => 45,
                    'avg_score' => 81,
                    'updated_at' => '4 Jam yang lalu',
                ],
                [
                    'id' => 6,
                    'code' => 'MGT-501',
                    'title' => 'Shopfloor Leadership',
                    'description' => 'Pelatihan kepemimpinan dan komunikasi untuk Leader produksi.',
                    'category' => 'Leadership',
                    'status' => self::STATUS_APPROVED,
                    'icon' => 'campaign',
                    'participants' => 300,
                    'pending_reviews' => 0,
                    'sessions' => 5,
                    'assessments' => 2,
                    'progress' => 0,
                    'avg_score' => 0,
                    'updated_at' => 'Kemarin',
                ],
                [
                    'id' => 7,
                    'code' => 'K3-202',
                    'title' => 'Dasar Keselamatan Listrik',
                    'description' => 'Mengenal risiko kelistrikan dan penggunaan APD listrik.',
                    'category' => 'HSE',
                    'status' => self::STATUS_RUNNING,
                    'icon' => 'electric_bolt',
                    'participants' => 35,
                    'pending_reviews' => 0,
                    'sessions' => 3,
                    'assessments' => 1,
                    'progress' => 15,
                    'avg_score' => 0,
                    'updated_at' => 'Bulan lalu',
                ],
                [
                    'id' => 8,
                    'code' => 'ISO-001',
                    'title' => 'Implementasi ISO 9001:2015',
                    'description' => 'Revisi silabus ISO untuk departemen QA bersertifikat baru.',
                    'category' => 'Quality',
                    'status' => self::STATUS_REJECTED,
                    'icon' => 'gpp_bad',
                    'participants' => 0,
                    'pending_reviews' => 0,
                    'sessions' => 3,
                    'assessments' => 0,
                    'progress' => 0,
                    'avg_score' => 0,
                    'updated_at' => '2 Hari yang lalu',
                ],
                [
                    'id' => 9,
                    'code' => 'LGC-2023',
                    'title' => 'Legacy Training SOP Produksi 2023',
                    'description' => 'SOP Lama, tidak berlaku sejak Januari 2024.',
                    'category' => 'Production',
                    'status' => self::STATUS_ARCHIVED,
                    'icon' => 'inventory_2',
                    'participants' => 420,
                    'pending_reviews' => 0,
                    'sessions' => 12,
                    'assessments' => 4,
                    'progress' => 100,
                    'avg_score' => 88,
                    'updated_at' => 'Tahun lalu',
                ],
            ];
            self::setStorage('courses', $defaultCourses);
        }

        $activities = self::getStorage('activities', null);
        if ($activities === null) {
            $defaultActivities = [
                [
                    'id' => 101,
                    'type' => 'recommendation',
                    'action' => 'Submit Recommendation Target Audience',
                    'target' => 'Pengenalan TPM',
                    'time' => 'Kemarin',
                    'icon' => 'send',
                    'color_class' => 'bg-secondary-container/20 text-secondary',
                ],
                [
                    'id' => 102,
                    'type' => 'grade',
                    'action' => 'Menilai Essay Peserta (Ahmad Rizal)',
                    'target' => 'Fundamental 5S',
                    'time' => '1 Jam yang lalu',
                    'icon' => 'edit_document',
                    'color_class' => 'bg-tertiary-container/20 text-tertiary',
                ],
                [
                    'id' => 103,
                    'type' => 'forum',
                    'action' => 'Membalas Forum Diskusi',
                    'target' => 'Area Jalur Darurat B',
                    'time' => '3 Jam yang lalu',
                    'icon' => 'forum',
                    'color_class' => 'bg-primary-container/20 text-primary',
                ],
            ];
            self::setStorage('activities', $defaultActivities);
        }

        $notifications = self::getStorage('notifications', null);
        if ($notifications === null || count($notifications) < 4) {
            $defaultNotifications = [
                [
                    'id' => 201,
                    'title' => 'Essay Menunggu Review',
                    'message' => '5 peserta telah mengumpulkan tugas essay pada course Fundamental 5S.',
                    'time' => '1 Jam yang lalu',
                    'icon' => 'assignment_late',
                    'is_read' => false,
                    'link' => '?route=/teacher/grade&course_id=1',
                ],
                [
                    'id' => 202,
                    'title' => 'Rekomendasi Butuh Revisi',
                    'message' => 'Rekomendasi peserta untuk course Quality Control Basic ditolak Admin.',
                    'time' => 'Kemarin',
                    'icon' => 'assignment_return',
                    'is_read' => true,
                    'link' => '?route=/teacher/course/detail&course_id=3',
                ],
                [
                    'id' => 3,
                    'title' => 'Pertanyaan Forum Diskusi',
                    'message' => 'Terdapat 3 balasan peserta yang butuh tanggapan Anda.',
                    'time' => '3 Jam yang lalu',
                    'icon' => 'forum',
                    'is_read' => false,
                    'link' => '?route=/teacher/forum',
                ],
                [
                    'id' => 4,
                    'title' => 'Course Kadaluarsa',
                    'message' => 'Materi pada course Shopfloor Leadership butuh pembaruan berkala.',
                    'time' => '1 Hari yang lalu',
                    'icon' => 'warning',
                    'is_read' => false,
                    'link' => '?route=/teacher/course/detail&course_id=6',
                ],
            ];
            self::setStorage('notifications', $defaultNotifications);
        }
    }

    /**
     * Dapatkan Statistik Dashboard Teacher
     */
    public static function getTeacherStats()
    {
        self::initData();
        $courses = self::getStorage('courses', []);

        $activeCourses = array_filter($courses, function ($c) {
            return $c['status'] !== self::STATUS_DRAFT && $c['status'] !== self::STATUS_ARCHIVED;
        });

        $totalActiveParticipants = array_reduce(
            $activeCourses,
            function ($carry, $item) {
                return $carry + ($item['status'] === self::STATUS_RUNNING ? $item['participants'] : 0);
            },
            0,
        );

        $totalPending = array_reduce(
            $activeCourses,
            function ($carry, $item) {
                return $carry + $item['pending_reviews'];
            },
            0,
        );

        $runningCourses = array_filter($activeCourses, function ($c) {
            return $c['status'] === self::STATUS_RUNNING;
        });
        $avgScore = 0;
        if (count($runningCourses) > 0) {
            $scoreSum = array_reduce(
                $runningCourses,
                function ($carry, $item) {
                    return $carry + $item['avg_score'];
                },
                0,
            );
            $avgScore = round($scoreSum / count($runningCourses));
        }

        return [
            'total_course' => count($courses),
            'active_students' => $totalActiveParticipants,
            'pending_review' => $totalPending,
            'avg_score' => $avgScore,
        ];
    }

    /**
     * Dapatkan List Course Terkelola
     */
    public static function getManagedCourses()
    {
        self::initData();
        return self::getStorage('courses', []);
    }

    /**
     * Dapatkan Detail Course berdasarkan ID
     */
    public static function getCourseDetail($id)
    {
        self::initData();
        $courses = self::getStorage('courses', []);
        foreach ($courses as $c) {
            if ((int) $c['id'] === (int) $id) {
                return $c;
            }
        }
        return null; // Not found
    }

    /**
     * Update/Simpan Course Baru & Edit Status
     */
    public static function saveCourse($courseData)
    {
        self::initData();
        $courses = self::getStorage('courses', []);

        if (isset($courseData['id'])) {
            // Update existing
            foreach ($courses as &$c) {
                if ((int) $c['id'] === (int) $courseData['id']) {
                    $c = array_merge($c, $courseData);
                    $c['updated_at'] = 'Baru saja';
                    break;
                }
            }
        } else {
            // Create new
            $newId = count($courses) > 0 ? max(array_column($courses, 'id')) + 1 : 1;
            $courseData['id'] = $newId;
            $courseData['participants'] = $courseData['participants'] ?? 0;
            $courseData['pending_reviews'] = 0;
            $courseData['avg_score'] = 0;
            $courseData['updated_at'] = 'Baru saja';
            $courses[] = $courseData;
        }

        self::setStorage('courses', $courses);
        return $courseData['id'] ?? $newId;
    }

    /**
     * Dapatkan Aktivitas Teacher
     */
    public static function getActivities()
    {
        self::initData();
        return self::getStorage('activities', []);
    }

    /**
     * Rekam Aktivitas Baru
     */
    public static function addActivity($activity)
    {
        self::initData();
        $activities = self::getStorage('activities', []);

        $newId = count($activities) > 0 ? max(array_column($activities, 'id')) + 1 : 1;
        $activity['id'] = $newId;
        $activity['time'] = 'Baru saja';

        array_unshift($activities, $activity); // Prepend to top
        self::setStorage('activities', $activities);
    }

    /**
     * Dapatkan Notifikasi Teacher
     */
    public static function getNotifications()
    {
        self::initData();
        return self::getStorage('notifications', []);
    }

    /**
     * Mengambil Opsi Filter Target Audience
     */
    public static function getTargetFilterOptions()
    {
        return [
            'jabatan' => ['Operator', 'Leader', 'Supervisor', 'Manager', 'General Manager'],
            'golongan' => ['Gol 1', 'Gol 2', 'Gol 3', 'Gol 4', 'Gol 5'],
            'departemen' => ['Manufacturing', 'Quality', 'Maintenance', 'Engineering', 'HRD'],
            'divisi' => ['Operation', 'Corporate', 'R&D'],
            'seksi' => ['Production A', 'Production B', 'QC Line 1', 'Building Maintenance'],
        ];
    }

    /**
     * Pencarian/Filter Karyawan untuk Recommendation
     */
    public static function filterEmployees($filters)
    {
        // 10 Dummy employees
        $employees = [
            [
                'id' => 1,
                'npk' => '12345',
                'name' => 'Budi Santoso',
                'jabatan' => 'Operator',
                'golongan' => 'Gol 1',
                'departemen' => 'Manufacturing',
                'divisi' => 'Operation',
                'seksi' => 'Production A',
            ],
            [
                'id' => 2,
                'npk' => '12346',
                'name' => 'Andi Pratama',
                'jabatan' => 'Operator',
                'golongan' => 'Gol 1',
                'departemen' => 'Manufacturing',
                'divisi' => 'Operation',
                'seksi' => 'Production B',
            ],
            [
                'id' => 3,
                'npk' => '67890',
                'name' => 'Ahmad Rizal',
                'jabatan' => 'Leader',
                'golongan' => 'Gol 3',
                'departemen' => 'Manufacturing',
                'divisi' => 'Operation',
                'seksi' => 'Production A',
            ],
            [
                'id' => 4,
                'npk' => '11223',
                'name' => 'Citra Lestari',
                'jabatan' => 'Supervisor',
                'golongan' => 'Gol 4',
                'departemen' => 'Quality',
                'divisi' => 'Corporate',
                'seksi' => 'QC Line 1',
            ],
            [
                'id' => 5,
                'npk' => '33445',
                'name' => 'Dwi Handoko',
                'jabatan' => 'Manager',
                'golongan' => 'Gol 5',
                'departemen' => 'Maintenance',
                'divisi' => 'Operation',
                'seksi' => 'Building Maintenance',
            ],
        ];

        // Simulasi fungsi filter dinamis
        $result = array_filter($employees, function ($emp) use ($filters) {
            foreach (['jabatan', 'golongan', 'departemen', 'divisi', 'seksi'] as $key) {
                if (!empty($filters[$key]) && !in_array($emp[$key], $filters[$key])) {
                    return false;
                }
            }
            return true;
        });

        // Reset associative numerical index to linear
        return array_values($result);
    }

    /**
     * Dapatkan Daftar Peserta dan Capaian Belajar per Course
     */
    public static function getCourseParticipants($courseId)
    {
        self::initData();
        $courseId = (int) $courseId;

        $participantSets = [
            1 => [ // Fundamental 5S & K3
                ['id' => 1, 'nama' => 'Budi Santoso', 'npk' => '12345', 'jabatan' => 'Operator', 'department' => 'Manufacturing', 'progress' => 100, 'score' => 92, 'status' => 'Lulus', 'last_active' => '2 Jam yang lalu', 'completed_at' => '10 Okt 2026'],
                ['id' => 2, 'nama' => 'Ahmad Rizal', 'npk' => '67890', 'jabatan' => 'Leader', 'department' => 'Manufacturing', 'progress' => 65, 'score' => 75, 'status' => 'In Progress', 'last_active' => 'Kemarin', 'completed_at' => '-'],
                ['id' => 3, 'nama' => 'Siti Aminah', 'npk' => '65432', 'jabatan' => 'Staff', 'department' => 'HSE', 'progress' => 100, 'score' => 88, 'status' => 'Lulus', 'last_active' => '3 Jam yang lalu', 'completed_at' => '11 Okt 2026'],
                ['id' => 4, 'nama' => 'Citra Lestari', 'npk' => '11223', 'jabatan' => 'Inspector', 'department' => 'Quality', 'progress' => 95, 'score' => 78, 'status' => 'Menunggu Validasi', 'last_active' => '4 Jam yang lalu', 'completed_at' => '-'],
                ['id' => 5, 'nama' => 'Dwi Handoko', 'npk' => '33445', 'jabatan' => 'Operator', 'department' => 'Maintenance', 'progress' => 40, 'score' => 60, 'status' => 'In Progress', 'last_active' => '2 Hari yang lalu', 'completed_at' => '-'],
                ['id' => 6, 'nama' => 'Eko Prasetyo', 'npk' => '55667', 'jabatan' => 'Operator', 'department' => 'Production A', 'progress' => 30, 'score' => 45, 'status' => 'Tidak Lulus', 'last_active' => '3 Hari yang lalu', 'completed_at' => '-'],
            ],
            4 => [ // Lean Manufacturing Essentials
                ['id' => 11, 'nama' => 'Rian Hidayat', 'npk' => '44551', 'jabatan' => 'Leader', 'department' => 'Production', 'progress' => 100, 'score' => 96, 'status' => 'Lulus', 'last_active' => '1 Minggu yang lalu', 'completed_at' => '05 Okt 2026'],
                ['id' => 12, 'nama' => 'Agus Setiawan', 'npk' => '44552', 'jabatan' => 'Staff', 'department' => 'Engineering', 'progress' => 100, 'score' => 90, 'status' => 'Lulus', 'last_active' => '1 Minggu yang lalu', 'completed_at' => '06 Okt 2026'],
                ['id' => 13, 'nama' => 'Dewi Anggraini', 'npk' => '44553', 'jabatan' => 'Supervisor', 'department' => 'Operation', 'progress' => 100, 'score' => 94, 'status' => 'Lulus', 'last_active' => '2 Minggu yang lalu', 'completed_at' => '02 Okt 2026'],
                ['id' => 14, 'nama' => 'Hendra Wijaya', 'npk' => '44554', 'jabatan' => 'Operator', 'department' => 'Production B', 'progress' => 100, 'score' => 88, 'status' => 'Lulus', 'last_active' => '3 Minggu yang lalu', 'completed_at' => '28 Sep 2026'],
            ],
            5 => [ // Advanced Safety Protocols
                ['id' => 21, 'nama' => 'Fajar Nugraha', 'npk' => '77881', 'jabatan' => 'Staff', 'department' => 'HSE', 'progress' => 85, 'score' => 82, 'status' => 'Menunggu Validasi', 'last_active' => '30 Menit yang lalu', 'completed_at' => '-'],
                ['id' => 22, 'nama' => 'Bambang Pamungkas', 'npk' => '77882', 'jabatan' => 'Leader', 'department' => 'Maintenance', 'progress' => 50, 'score' => 70, 'status' => 'In Progress', 'last_active' => '5 Jam yang lalu', 'completed_at' => '-'],
                ['id' => 23, 'nama' => 'Hendra Wijaya', 'npk' => '77883', 'jabatan' => 'Operator', 'department' => 'Production', 'progress' => 25, 'score' => 55, 'status' => 'In Progress', 'last_active' => '2 Hari yang lalu', 'completed_at' => '-'],
                ['id' => 24, 'nama' => 'Slamet Riyadi', 'npk' => '77884', 'jabatan' => 'Operator', 'department' => 'Quality', 'progress' => 100, 'score' => 90, 'status' => 'Lulus', 'last_active' => '1 Jam yang lalu', 'completed_at' => '12 Okt 2026'],
            ],
            7 => [ // Dasar Keselamatan Listrik
                ['id' => 31, 'nama' => 'Wahyudi Santoso', 'npk' => '88991', 'jabatan' => 'Technician', 'department' => 'Maintenance', 'progress' => 20, 'score' => 0, 'status' => 'In Progress', 'last_active' => 'Kemarin', 'completed_at' => '-'],
                ['id' => 32, 'nama' => 'Dian Kusuma', 'npk' => '88992', 'jabatan' => 'Operator', 'department' => 'Manufacturing', 'progress' => 15, 'score' => 0, 'status' => 'In Progress', 'last_active' => '3 Hari yang lalu', 'completed_at' => '-'],
            ]
        ];

        return $participantSets[$courseId] ?? [
            ['id' => 99, 'nama' => 'Budi Santoso', 'npk' => '12345', 'jabatan' => 'Operator', 'department' => 'Manufacturing', 'progress' => 100, 'score' => 90, 'status' => 'Lulus', 'last_active' => 'Kemarin', 'completed_at' => '10 Okt 2026'],
            ['id' => 98, 'nama' => 'Ahmad Rizal', 'npk' => '67890', 'jabatan' => 'Leader', 'department' => 'Manufacturing', 'progress' => 50, 'score' => 60, 'status' => 'In Progress', 'last_active' => '2 Hari yang lalu', 'completed_at' => '-'],
        ];
    }

    /**
     * Dapatkan Rekap Penilaian Peserta per Course
     */
    public static function getCourseGrades($courseId, $filterStatus = 'all')
    {
        $participants = self::getCourseParticipants($courseId);
        $grades = [];

        foreach ($participants as $p) {
            $preTest = max(40, (int)$p['score'] - 15);
            $postTest = (int)$p['score'];
            $finalAssessment = (int)$p['score'];

            if ($p['status'] === 'Menunggu Validasi') {
                $status = 'Menunggu Validasi';
            } elseif ($p['score'] >= 75) {
                $status = 'Lulus';
            } else {
                $status = ($p['progress'] < 50) ? 'In Progress' : 'Tidak Lulus';
            }

            $grades[] = [
                'id' => $p['id'],
                'name' => $p['nama'],
                'npk' => $p['npk'],
                'department' => $p['department'],
                'jabatan' => $p['jabatan'],
                'progress' => $p['progress'],
                'pre_test' => $preTest,
                'post_test' => $postTest,
                'final_assessment' => $finalAssessment,
                'status' => $status,
                'last_active' => $p['last_active'],
                'completed_at' => $p['completed_at'] ?? '-',
            ];
        }

        if ($filterStatus !== 'all') {
            $grades = array_filter($grades, function ($g) use ($filterStatus) {
                return strtolower($g['status']) === strtolower($filterStatus);
            });
        }

        return array_values($grades);
    }
}

