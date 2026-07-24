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
        $courses = self::getStorage('courses', null);
        if ($courses === null) {
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
                    'avg_score' => 88,
                    'updated_at' => '2 Jam yang lalu',
                ],
                [
                    'id' => 2,
                    'code' => 'PROD-201',
                    'title' => 'Pengenalan TPM (Total Productive Maintenance)',
                    'description' => 'Pelatihan pemeliharaan mesin produksi tahap awal.',
                    'category' => 'Maintenance',
                    'status' => self::STATUS_SUBMITTED,
                    'icon' => 'manufacturing',
                    'participants' => 30, // Recommended count
                    'pending_reviews' => 0,
                    'avg_score' => 0,
                    'updated_at' => 'Kemarin',
                ],
                [
                    'id' => 3,
                    'code' => 'QC-301',
                    'title' => 'Quality Control Basic',
                    'description' => 'Materi standar QC untuk inspektur baru.',
                    'category' => 'Quality',
                    'status' => self::STATUS_DRAFT,
                    'icon' => 'fact_check',
                    'participants' => 0,
                    'pending_reviews' => 0,
                    'avg_score' => 0,
                    'updated_at' => '3 Hari yang lalu',
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
        if ($notifications === null) {
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
}
