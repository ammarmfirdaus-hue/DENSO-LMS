<?php
/**
 * DX_LMS_M_StudentData (Data Statis Siswa).
 * Factory-based Dummy Data Generator.
 *
 * Sumber Kebenaran Tunggal (Single Source of Truth) untuk pengelolaan
 * data percobaan (dummy/mock data) pada modul LMS Siswa.
 */

class DX_LMS_M_Student_Data
{
    private static $courses = null;
    private static $forums = null;
    private static $notifications = null;
    private static $notices = null;

    private static function initData()
    {
        if (self::$courses !== null) {
            return;
        }

        /** Inisialisasi penghasil angka acak agar data sementara tetap konsisten. */
        mt_srand(12345);
        srand(12345);

        self::$courses = [];
        self::$forums = [];
        self::$notifications = [];
        self::$notices = [
            [
                'title' => 'Pemeliharaan Sistem Terjadwal pada Akhir Pekan',
                'date' => date('Y-m-d', strtotime('-1 days')),
                'is_new' => true,
            ],
            [
                'title' => 'Pembaruan Kebijakan K3 Global',
                'date' => date('Y-m-d', strtotime('-3 days')),
                'is_new' => false,
            ],
        ];

        /** Status 1: Telah Selesai. */
        self::generateCourse(
            'CRS-TPM-001',
            'Total Productive Maintenance (TPM) Basics',
            'Modul komprehensif mengenai dasar penerapan TPM di area produksi. Course ini akan memandu Anda untuk memahami 8 pilar TPM, meminimalisasi downtime, mengurangi defect, dan pada akhirnya meningkatkan Overall Equipment Effectiveness (OEE). Target Role: Operator & Leader.',
            'Wajib',
            ['Engineering', 'Maintenance', 'Core'],
            'selesai',
            100,
        );
        self::generateCourse(
            'CRS-K3-002',
            'Dasar-dasar K3 Produksi & Penanganan Darurat',
            'Pelatihan esensial yang mencakup standar keselamatan, kesehatan kerja, dan lingkungan (K3L) di area shopfloor. Mempelajari prosedur lockout/tagout (LOTO), penggunaan APD, dan evakuasi. Target Role: Seluruh Karyawan (All Levels).',
            'Wajib',
            ['Safety', 'Compliance'],
            'selesai',
            100,
        );
        self::generateCourse(
            'CRS-5S-003',
            'Penerapan Budaya 5S Tingkat Lanjut',
            'Tidak sekadar teori, course ini membahas studi kasus nyata implementasi Seiri, Seiton, Seiso, Seiketsu, Shitsuke di lingkungan manufaktur. Bagaimana membangun kebiasaan dan kedisiplinan tingkat tinggi. Target Role: Staff & Leader.',
            'Pilihan',
            ['Quality', 'Culture'],
            'selesai',
            100,
        );

        /** Status 2: Sedang Dipelajari. */
        self::generateCourse(
            'CRS-AUT-004',
            'Pengenalan PLC & Otomasi Industri Terapan',
            'Membahas arsitektur Programmable Logic Controller (PLC) dan penerapannya pada mesin otomasi lini produksi modern. Di akhir sesi terdapat studi kasus programming logika dasar ladder diagram. Target Role: Engineering Staff & Technician.',
            'Wajib',
            ['Engineering', 'Automation'],
            'sedang_dipelajari',
            45,
        );
        self::generateCourse(
            'CRS-LNM-005',
            'Konsep Lean Manufacturing & Continuous Kaizen',
            'Strategi mendalam untuk meningkatkan produktivitas dengan mengeliminasi 7 bentuk pemborosan (Muda). Memberikan kerangka kerja untuk menginisiasi project Kaizen pertama Anda di divisi. Target Role: Leader & Supervisor.',
            'Wajib',
            ['Productivity', 'Management'],
            'sedang_dipelajari',
            80,
        );
        self::generateCourse(
            'CRS-QCC-006',
            'Metodologi Quality Control Circle (QCC)',
            'Panduan praktis menyelesaikan masalah kualitas di line menggunakan siklus PDCA (Plan-Do-Check-Action) dan penggunaan efektif 7 QC Tools. Target Role: Operator Gol. 2 & Leader.',
            'Pilihan',
            ['Quality', 'Problem Solving'],
            'sedang_dipelajari',
            15,
        );
        self::generateCourse(
            'CRS-COM-007',
            'Effective Business Communication & Negotiation',
            'Pelatihan intensif teknik komunikasi profesional, negosiasi antar-departemen, tata cara email perusahaan (email etiquette), serta keterampilan menyajikan presentasi yang persuasif. Target Role: Staff, Leader, & Supervisor.',
            'Pilihan',
            ['Soft Skill', 'HR'],
            'sedang_dipelajari',
            60,
        );

        /** Status 3: Belum Dimulai. */
        self::generateCourse(
            'CRS-LDR-008',
            'Shopfloor Leadership Skills: The Frontline Manager',
            'Program pengembangan kepemimpinan bagi supervisor dan leader di area produksi. Membahas penanganan konflik, pembagian tugas (delegation), dan motivasi tim. Target Role: Leader & Supervisor.',
            'Pilihan',
            ['Leadership', 'Management'],
            'belum_dimulai',
            0,
        );
        self::generateCourse(
            'CRS-ISO-009',
            'Awareness ISO 9001:2015 Quality Management',
            'Pemahaman fundamental terkait klausul-klausul dalam Sistem Manajemen Mutu ISO 9001:2015. Persiapan perusahaan menghadapi audit eksternal. Target Role: All Staff & Management.',
            'Wajib',
            ['Compliance', 'Quality'],
            'belum_dimulai',
            0,
        );
        self::generateCourse(
            'CRS-ENV-010',
            'Environmental Awareness ISO 14001',
            'Standar Sistem Manajemen Lingkungan untuk mewujudkan operasional perusahaan yang ramah lingkungan dan berkelanjutan (sustainable). Target Role: All Employees.',
            'Wajib',
            ['Compliance', 'Safety'],
            'belum_dimulai',
            0,
        );

        /** Tambahan Data Dummy Baru */
        self::generateCourse(
            'CRS-SPC-011',
            'Statistical Process Control (SPC) Dasar',
            'Memahami teknik statistik untuk memonitor dan mengontrol proses agar sesuai standar. Belajar membaca control chart dan menghitung kapabilitas proses (Cp, Cpk).',
            'Wajib',
            ['Quality', 'Engineering'],
            'selesai',
            100,
        );
        self::generateCourse(
            'CRS-SMED-012',
            'Single Minute Exchange of Die (SMED)',
            'Teknik mengurangi waktu setup dan changeover mesin untuk meningkatkan fleksibilitas produksi tanpa mengorbankan kualitas.',
            'Pilihan',
            ['Productivity', 'Maintenance'],
            'sedang_dipelajari',
            35,
        );
        self::generateCourse(
            'CRS-PKB-013',
            'Pencegahan Kebakaran di Area Produksi',
            'Pelatihan esensial penanganan bahan mudah terbakar, penggunaan APAR, dan rute evakuasi. Simulasi pemadaman api secara virtual.',
            'Wajib',
            ['Safety', 'Compliance'],
            'sedang_dipelajari',
            90,
        );
        self::generateCourse(
            'CRS-EHS-014',
            'Environmental, Health, and Safety (EHS) Awareness',
            'Sistem terintegrasi mengenai kesehatan kerja dan kepedulian lingkungan. Pemahaman dasar pengelolaan limbah B3 di pabrik.',
            'Wajib',
            ['Safety', 'Compliance'],
            'belum_dimulai',
            0,
        );
        self::generateCourse(
            'CRS-DMAIC-015',
            'Metodologi Six Sigma DMAIC',
            'Define, Measure, Analyze, Improve, Control. Metodologi terstruktur untuk mengurangi cacat produk hingga titik paling minim.',
            'Pilihan',
            ['Quality', 'Problem Solving'],
            'sedang_dipelajari',
            50,
        );
        self::generateCourse(
            'CRS-PKA-016',
            'Pelatihan Keterampilan Assembling',
            'Standar prosedur assembling komponen otomotif. Fokus pada ergonomi kerja, kecepatan, dan menghindari kesalahan perakitan (poka-yoke).',
            'Wajib',
            ['Core', 'Productivity'],
            'selesai',
            100,
        );
        self::generateCourse(
            'CRS-TQM-017',
            'Total Quality Management (TQM)',
            'Membangun komitmen perusahaan secara keseluruhan untuk secara berkesinambungan memperbaiki kualitas proses dan produk demi kepuasan pelanggan.',
            'Wajib',
            ['Management', 'Quality'],
            'sedang_dipelajari',
            75,
        );
        self::generateCourse(
            'CRS-SCM-018',
            'Supply Chain Management Basic',
            'Aliran material dari supplier hingga barang jadi ke customer. Memahami Just-In-Time (JIT) dan pengaturan Kanban system.',
            'Pilihan',
            ['Management', 'Productivity'],
            'belum_dimulai',
            0,
        );
        self::generateCourse(
            'CRS-IoT-019',
            'Industrial IoT & Industry 4.0',
            'Pengenalan sensor cerdas dan analisis data langsung di line produksi (Real-time monitoring).',
            'Pilihan',
            ['Automation', 'Engineering'],
            'sedang_dipelajari',
            25,
        );
        self::generateCourse(
            'CRS-GMP-020',
            'Good Manufacturing Practices (GMP)',
            'Standar praktik manufaktur yang baik untuk mencegah kontaminasi dan mempertahankan kualitas produk.',
            'Wajib',
            ['Quality', 'Compliance'],
            'sedang_dipelajari',
            10,
        );
    }

    private static function generateCourse($id, $title, $description, $category, $filters, $status, $progress)
    {
        /** Memetakan ikon visual yang relevan berdasarkan ID Kursus. */
        /** Menonaktifkan aset eksternal dan beralih sepenuhnya ke ikon lokal. */
        $courseIcons = [
            'CRS-TPM-001' => 'build', /** Pemetaan ikon untuk kategori Perawatan (Maintenance). */
            'CRS-K3-002' => 'health_and_safety', /** Pemetaan ikon untuk kategori Keselamatan Kerja. */
            'CRS-5S-003' => 'cleaning_services', /** Pemetaan ikon untuk kategori Tata Graha dan Kebersihan. */
            'CRS-AUT-004' => 'precision_manufacturing', /** Pemetaan ikon untuk kategori Otomasi Manufaktur. */
            'CRS-LNM-005' => 'trending_up', /** Pemetaan ikon untuk kategori Produksi Efisien. */
            'CRS-QCC-006' => 'verified', /** Pemetaan ikon untuk kategori Pengendalian Mutu. */
            'CRS-COM-007' => 'forum', /** Pemetaan ikon untuk kategori Komunikasi. */
            'CRS-LDR-008' => 'groups', /** Pemetaan ikon untuk kategori Kepemimpinan. */
            'CRS-ISO-009' => 'gavel', /** Pemetaan ikon untuk kategori Kepatuhan Regulasi. */
            'CRS-ENV-010' => 'eco', /** Pemetaan ikon untuk kategori Manajemen Lingkungan. */
            'CRS-SPC-011' => 'bar_chart',
            'CRS-SMED-012' => 'timer',
            'CRS-PKB-013' => 'local_fire_department',
            'CRS-EHS-014' => 'health_and_safety',
            'CRS-DMAIC-015' => 'timeline',
            'CRS-PKA-016' => 'precision_manufacturing',
            'CRS-TQM-017' => 'verified',
            'CRS-SCM-018' => 'inventory',
            'CRS-IoT-019' => 'router',
            'CRS-GMP-020' => 'factory',
        ];

        $thumbnail = null; /** Membersihkan referensi menuju miniatur (thumbnail) eksternal. */
        $thumbnailIcon = $courseIcons[$id] ?? 'school';

        $sessions = self::generateSessions($id, $status, $progress);

        $isCompleted = $status === 'selesai';
        $quizScore = $isCompleted ? rand(75, 100) : ($progress > 80 ? rand(60, 90) : null);

        $course = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'thumbnail' => $thumbnail,
            'thumbnail_icon' => $thumbnailIcon,
            'category' => $category,
            'filters' => $filters,
            'modules_count' => count($sessions),
            'status' => $status,
            'progress' => $progress,
            'last_accessed' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')),
            'sessions' => $sessions,
        ];

        if ($isCompleted) {
            $course['completion_date'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
            $course['quiz_score'] = $quizScore;
            $course['attendance'] = 100;
            
            $quizData = self::generateFinalAssessment(true, $quizScore);
            $quizData['type'] = 'quiz';
            $course['quiz'] = $quizData;
            
            $faData = self::generateFinalAssessment(true, $quizScore);
            $faData['nilai'] = $quizScore;
            $course['final_assessment'] = $faData;
        } elseif ($progress > 0) {
            $quizData = self::generateFinalAssessment(false);
            $quizData['type'] = 'quiz';
            $course['quiz'] = $quizData;
            
            $faData = self::generateFinalAssessment(false);
            $faData['nilai'] = null;
            $course['final_assessment'] = $faData;
        } else {
            $quizData = self::generateFinalAssessment(false, null, true);
            $quizData['type'] = 'quiz';
            $course['quiz'] = $quizData;
            
            $faData = self::generateFinalAssessment(false, null, true);
            $faData['nilai'] = null;
            $course['final_assessment'] = $faData; /** Kondisi saat modul belum dimulai. */
        }

        $course['forums'] = self::generateForumData($id, $title, $sessions, $progress > 0);

        self::$courses[] = $course;

        self::generateNotificationsForCourse($id, $title, $status, $quizScore);
    }

    private static function generateSessions($courseId, $courseStatus, $progress)
    {
        $sessionsCount = rand(2, 4);
        $sessions = [];

        $completedSessions = floor(($progress / 100) * $sessionsCount);

        for ($i = 1; $i <= $sessionsCount; $i++) {
            $sessStatus = 'belum_dimulai';
            if ($i <= $completedSessions || $courseStatus === 'selesai') {
                $sessStatus = 'selesai';
            } elseif ($i == $completedSessions + 1 && $progress > 0) {
                $sessStatus = 'sedang_dipelajari';
            }

            $materials = self::generateMaterials($courseId . '-S' . $i, $sessStatus);

            $session = [
                'id' => $courseId . '-S' . $i,
                'title' => 'Sesi ' . $i . ': ' . ['Pengantar', 'Konsep Inti', 'Implementasi', 'Evaluasi'][$i - 1],
                'status' => $sessStatus,
                'materials' => $materials,
            ];

            $session['pre_test'] = self::generateTest($sessStatus === 'selesai' || $sessStatus === 'sedang_dipelajari');
            $session['post_test'] = self::generateTest($sessStatus === 'selesai');

            $sessions[] = $session;
        }

        return $sessions;
    }

    private static function generateMaterials($sessionId, $sessStatus)
    {
        $materialCount = rand(2, 4);
        $materials = [];

        $patterns = ['text_heavy', 'video_interactive', 'pdf_reference', 'office_presentation', 'mixed_media'];

        for ($i = 1; $i <= $materialCount; $i++) {
            $pattern = $patterns[array_rand($patterns)];
            $type = 'text'; /** Menyediakan ikon pengganti bawaan untuk antarmuka pengguna. */

            if ($pattern === 'video_interactive') {
                $type = 'video';
            }
            if ($pattern === 'pdf_reference') {
                $type = 'pdf';
            }
            if ($pattern === 'office_presentation') {
                $type = 'office';
            }

            $matStatus = 'belum_dimulai';
            if ($sessStatus === 'selesai') {
                $matStatus = 'selesai';
            } elseif ($sessStatus === 'sedang_dipelajari' && $i == 1) {
                $matStatus = 'selesai';
            } elseif ($sessStatus === 'sedang_dipelajari' && $i == 2) {
                $matStatus = 'sedang_dipelajari';
            }

            $material = [
                'id' => $sessionId . '-M' . $i,
                'title' =>
                    'Materi ' .
                    $i .
                    ': ' .
                    ['Dasar Teori', 'Studi Kasus', 'Panduan Praktis', 'Regulasi', 'Review'][$i - 1],
                'type' => $type,
                'status' => $matStatus,
                'duration' => rand(10, 45) . ($type === 'pdf' || $type === 'office' ? ' halaman' : ' min'),
            ];

            $blocks = [];

            if ($pattern === 'text_heavy') {
                $blocks = [
                    ['type' => 'heading', 'content' => 'Pengenalan Konsep Esensial'],
                    [
                        'type' => 'text',
                        'content' =>
                            'Dalam lingkungan kerja manufaktur yang dinamis, pemahaman komprehensif terkait prosedur kerja tidak hanya meningkatkan efisiensi, tetapi juga menjamin keselamatan seluruh elemen perusahaan.',
                    ],
                    ['type' => 'subheading', 'content' => 'Poin-poin Penting'],
                    [
                        'type' => 'checklist',
                        'items' => [
                            'Identifikasi potensi bahaya',
                            'Gunakan APD yang sesuai',
                            'Patuhi instruksi kerja (IK)',
                            'Lakukan pelaporan jika ada anomali',
                        ],
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Prosedur ini berlaku untuk seluruh area shopfloor tanpa terkecuali.',
                    ],
                    [
                        'type' => 'image',
                        'url' =>
                            'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&auto=format&fit=crop',
                        'caption' => 'Ilustrasi proses manufaktur dan penerapan standar',
                    ],
                    ['type' => 'quote', 'content' => 'Safety First, Quality Always. Tidak ada kompromi untuk standar.'],
                ];
            } elseif ($pattern === 'video_interactive') {
                $blocks = [
                    ['type' => 'heading', 'content' => 'Video Panduan Pelaksanaan'],
                    ['type' => 'video_embed', 'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'],
                    [
                        'type' => 'warning',
                        'content' =>
                            'Pastikan Anda menonton video ini hingga selesai. Sistem akan mencatat durasi tontonan Anda.',
                    ],
                    [
                        'type' => 'text',
                        'content' =>
                            'Video di atas mendemonstrasikan langkah demi langkah pelaksanaan inspeksi harian. Anda wajib mempraktekkannya di area kerja masing-masing.',
                    ],
                    ['type' => 'divider', 'content' => ''],
                    [
                        'type' => 'external_link',
                        'url' => 'https://example.com/panduan-lengkap',
                        'title' => 'Baca Panduan Lengkap di Portal Internal',
                    ],
                ];
            } elseif ($pattern === 'pdf_reference') {
                $blocks = [
                    ['type' => 'heading', 'content' => 'Buku Panduan & Regulasi'],
                    [
                        'type' => 'text',
                        'content' =>
                            'Silakan baca modul PDF terlampir. Modul ini merupakan referensi utama untuk regulasi yang berlaku secara global.',
                    ],
                    [
                        'type' => 'file_attachment',
                        'filename' => 'Standard_Operating_Procedure_V2.pdf',
                        'size' => '4.2 MB',
                    ],
                    ['type' => 'subheading', 'content' => 'Ringkasan Eksekutif'],
                    [
                        'type' => 'text',
                        'content' =>
                            '<table class="w-full text-sm text-left"><tr class="border-b"><th class="py-2">Bab</th><th class="py-2">Fokus Utama</th></tr><tr class="border-b"><td class="py-2">Bab 1</td><td class="py-2">Kebijakan Umum</td></tr><tr><td class="py-2">Bab 2</td><td class="py-2">Instruksi Spesifik</td></tr></table>',
                    ],
                    [
                        'type' => 'checklist',
                        'items' => [
                            'Wajib dibaca sebelum turun ke lapangan',
                            'Cetak jika diperlukan',
                            'Diskusikan dengan Leader jika ada kendala',
                        ],
                    ],
                ];
            } elseif ($pattern === 'office_presentation') {
                $blocks = [
                    ['type' => 'heading', 'content' => 'Materi Presentasi Kelas'],
                    [
                        'type' => 'text',
                        'content' =>
                            'Unduh presentasi berikut untuk dipelajari secara offline. File ini mencakup seluruh slide yang digunakan saat training offline.',
                    ],
                    ['type' => 'file_attachment', 'filename' => 'Training_Deck_Management.pptx', 'size' => '8.5 MB'],
                    [
                        'type' => 'info',
                        'content' =>
                            'File presentasi bersifat rahasia perusahaan (Confidential). Dilarang menyebarkan ke pihak eksternal.',
                    ],
                ];
            } elseif ($pattern === 'mixed_media') {
                $blocks = [
                    ['type' => 'heading', 'content' => 'Studi Kasus & Analisis'],
                    [
                        'type' => 'image',
                        'url' =>
                            'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=800&auto=format&fit=crop',
                        'caption' => 'Diagram Analisis Masalah',
                    ],
                    [
                        'type' => 'text',
                        'content' =>
                            'Berdasarkan diagram di atas, kita dapat menyimpulkan bahwa faktor terbesar dari downtime mesin adalah kurangnya preventive maintenance rutin.',
                    ],
                    ['type' => 'video_embed', 'url' => 'https://www.youtube.com/embed/ScMzIvxBSi4'],
                    [
                        'type' => 'warning',
                        'content' =>
                            'Perhatikan bagaimana teknisi pada video menangani anomali mesin tanpa mematikan sistem utama.',
                    ],
                    ['type' => 'file_attachment', 'filename' => 'Form_Laporan_Anomali.xlsx', 'size' => '120 KB'],
                ];
            }

            $material['content_blocks'] = $blocks;
            $materials[] = $material;
        }

        return $materials;
    }

    private static function generateTest($isDone)
    {
        /** Menggunakan format Pilihan Ganda secara eksklusif untuk evaluasi awal dan akhir. */
        $pgQuestionPool = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
        shuffle($pgQuestionPool);
        $questions = array_slice($pgQuestionPool, 0, 5); /** Memilih 5 soal Pilihan Ganda secara acak. */

        if (!$isDone) {
            return [
                'status' => 'belum_dimulai',
                'score' => 0,
                'attempts' => 0,
                'history' => [],
                'questions' => $questions,
            ];
        }

        $attempts = rand(1, 3);
        $history = [];
        $finalScore = 0;

        for ($a = 1; $a <= $attempts; $a++) {
            $score = $a === $attempts ? rand(75, 100) : rand(40, 70);

            /** Memetakan jawaban sementara pengguna berdasarkan referensi bank soal. */
            /** Menyediakan set data pengujian yang akan dievaluasi oleh sistem antarmuka. */
            $userAnswers = [];
            foreach ($questions as $qId) {
                $userAnswers[$qId] = ['A', 'B', 'C', 'D'][rand(0, 3)];
            }

            $history[] = [
                'attempt' => $a,
                'date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' days')),
                'score' => $score,
                'user_answers' => $userAnswers,
            ];
            $finalScore = max($finalScore, $score);
        }

        return [
            'status' => 'selesai',
            'score' => $finalScore,
            'attempts' => $attempts,
            'history' => $history,
            'questions' => $questions,
        ];
    }

    private static function generateFinalAssessment($isDone, $finalScore = null, $isLocked = false)
    {
        /** Penilaian akhir mengkombinasikan tipe Pilihan Ganda dan soal Uraian. */
        $questionPool = range(1, 20);
        shuffle($questionPool);
        $questions = array_slice($questionPool, 0, 10); /** Memilih 10 soal kombinasi secara acak untuk keperluan evaluasi. */

        if ($isLocked) {
            return [
                'status' => 'terkunci',
                'type' => 'final_assessment',
                'questions' => $questions,
            ];
        }

        if (!$isDone) {
            return [
                'status' => 'tersedia',
                'type' => 'final_assessment',
                'questions' => $questions,
                'time_limit' => 60,
            ];
        }

        $userAnswers = [];
        foreach ($questions as $qId) {
            $userAnswers[$qId] = 'Jawaban dummy untuk riwayat'; /** Tipe format masukan: teks untuk uraian, dan karakter abjad untuk pilihan ganda. */
        }

        /** Menetapkan status peninjauan (review) secara acak. */
        $reviewStatuses = ['under_review', 'reviewed', 'published'];
        $reviewStatus = $reviewStatuses[array_rand($reviewStatuses)];

        return [
            'status' => 'selesai',
            'type' => 'final_assessment',
            'score' => $finalScore,
            'questions' => $questions,
            'time_limit' => 60,
            'passed' => $finalScore >= 70,
            'completion_date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 20) . ' days')),
            'feedback' =>
                'Hasil sangat memuaskan, terus pertahankan performa Anda. Pastikan untuk selalu menerapkan prinsip yang telah diajarkan di area kerja.',
            'history' => [
                [
                    'attempt' => 1,
                    'date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 20) . ' days')),
                    'score' => $finalScore,
                    'user_answers' => $userAnswers,
                    'review_status' => $reviewStatus,
                ],
            ],
        ];
    }

    private static function generateForumData($courseId, $courseTitle, $sessions, $hasProgress)
    {
        $topicIdeas = [
            'Pertanyaan terkait detail parameter setting mesin pada modul ini',
            'Sharing pengalaman Kaizen di divisi Assembling',
            'Apakah form inspeksi harian wajib diisi manual jika sistem error?',
            'Kendala penerapan 5S di area gudang bahan baku',
            'Mohon contoh riil defect rate pada bulan lalu',
            'Diskusi: Mengapa OEE sering drop di shift malam?',
            'Mohon arahan terkait standar APD terbaru',
            'Bagaimana cara perhitungan Control Chart jika datanya outlier?',
            'Ada yang punya form checklist LOTO versi excel terbaru?',
            'Batas toleransi downtime mesin per shift idealnya berapa?',
            'Saran penerapan Poka-Yoke di perakitan part B-34',
            'Menanggapi video materi sesi 2, apakah prakteknya mudah?',
            'Tips menghafal 8 pilar TPM bagi operator baru',
            'Bagaimana mengatasi resistensi tim saat implementasi 5S?',
            'Sharing: Penggunaan IoT sensor suhu pada mesin injection'
        ];

        $forums = [
            'materi' => [],
            'quiz' => ['threads' => [], 'has_created_thread' => false],
            'final_assessment' => ['threads' => [], 'has_created_thread' => false],
        ];

        if (!$hasProgress) {
            foreach ($sessions as $session) {
                $forums['materi'][$session['id']] = ['threads' => [], 'has_created_thread' => false];
            }
            return $forums;
        }

        $createThreads = function($prefix) use ($courseId, $courseTitle, $topicIdeas) {
            $threads = [];
            $threadCount = rand(2, 5);
            for ($i = 0; $i < $threadCount; $i++) {
                $hasUnread = rand(0, 1) === 1;
                $thread = [
                    'id' => 'FRM-' . $courseId . '-' . $prefix . '-' . $i,
                    'course_id' => $courseId,
                    'course_title' => $courseTitle,
                    'title' => $topicIdeas[array_rand($topicIdeas)],
                    'author' => ['Budi Santoso', 'Siti Aminah', 'Rudi Hermawan', 'Dewi Lestari', 'Agus Supriyadi', 'Eko Prasetyo', 'Nurul Huda', 'Iwan Fals', 'Rina Nose', 'Joko Widodo'][rand(0, 9)],
                    'role' => ['Teacher', 'Student', 'Student', 'Student', 'Admin'][rand(0, 4)],
                    'replies_count' => rand(5, 45),
                    'last_reply_time' => date('Y-m-d H:i', strtotime('-' . rand(1, 72) . ' hours')),
                    'is_unread' => $hasUnread,
                ];
                $threads[] = $thread;
                // Add to global forums array for backward compatibility
                self::$forums[] = $thread;
            }
            return $threads;
        };

        foreach ($sessions as $session) {
            $forums['materi'][$session['id']] = [
                'has_created_thread' => rand(0, 1) === 1,
                'threads' => $createThreads('S' . $session['id'])
            ];
        }

        $forums['quiz'] = [
            'has_created_thread' => rand(0, 1) === 1,
            'threads' => $createThreads('QZ')
        ];

        $forums['final_assessment'] = [
            'has_created_thread' => rand(0, 1) === 1,
            'threads' => $createThreads('FA')
        ];

        return $forums;
    }

    private static function generateNotificationsForCourse($courseId, $courseTitle, $status, $quizScore)
    {
        if ($status === 'selesai') {
            self::$notifications[] = [
                'id' => 'NOTIF-' . $courseId . '-1',
                'title' => 'Course Selesai & Sertifikat Tersedia!',
                'message' =>
                    'Selamat, Anda telah menuntaskan course <span class="font-label-md text-label-md">"' .
                    $courseTitle .
                    '"</span>. Sertifikat kelulusan kini sudah bisa diunduh di halaman riwayat.',
                'time' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 5) . ' days')),
                'is_read' => true,
                'priority' => 'normal',
                'action_url' => '?route=/student/riwayat/detail&id=' . $courseId,
                'type' => 'course',
                'time_group' => 'minggu_lalu',
            ];

            /** Mensimulasikan kondisi di mana sebagian soal uraian telah ditinjau. */
            if (rand(0, 1) === 1) {
                self::$notifications[] = [
                    'id' => 'NOTIF-' . $courseId . '-2',
                    'title' => 'Penilaian Essay Selesai',
                    'message' =>
                        'Instruktur telah memberikan nilai untuk jawaban essay Anda pada course <span class="font-label-md text-label-md">"' .
                        $courseTitle .
                        '"</span>.',
                    'time' => date('Y-m-d H:i:s', strtotime('-' . rand(6, 10) . ' days')),
                    'is_read' => true,
                    'priority' => 'penting',
                    'action_url' => '?route=/student/riwayat/detail&id=' . $courseId,
                    'type' => 'quiz',
                    'time_group' => 'minggu_lalu',
                ];
            }
        } elseif ($status === 'sedang_dipelajari') {
            self::$notifications[] = [
                'id' => 'NOTIF-' . $courseId . '-3',
                'title' => 'Materi Baru Tersedia',
                'message' =>
                    'Instruktur baru saja mengunggah PDF dan Video simulasi terbaru untuk course <span class="font-label-md text-label-md">"' .
                    $courseTitle .
                    '"</span>.',
                'time' => '10 menit yang lalu',
                'is_read' => false,
                'priority' => 'normal',
                'action_url' => '?route=/student/course/detail&id=' . $courseId,
                'type' => 'materi',
                'time_group' => 'hari_ini',
            ];

            /** Menghasilkan notifikasi balasan forum secara acak. */
            if (rand(0, 1) === 1) {
                self::$notifications[] = [
                    'id' => 'NOTIF-' . $courseId . '-4',
                    'title' => 'Balasan Forum Baru',
                    'message' =>
                        'Ada balasan baru pada diskusi yang Anda ikuti di course <span class="font-label-md text-label-md">"' .
                        $courseTitle .
                        '"</span>.',
                    'time' => '1 jam yang lalu',
                    'is_read' => false,
                    'priority' => 'normal',
                    'action_url' => '?route=/student/forum',
                    'type' => 'materi',
                    'time_group' => 'hari_ini',
                ];
            }
        }
    }

    public static function getCourses(): array
    {
        self::initData();
        return self::$courses;
    }

    public static function getActiveCourses(): array
    {
        return array_values(
            array_filter(self::getCourses(), function ($c) {
                return $c['status'] === 'sedang_dipelajari';
            }),
        );
    }

    public static function getCompletedCourses(): array
    {
        return array_values(
            array_filter(self::getCourses(), function ($c) {
                return $c['status'] === 'selesai';
            }),
        );
    }

    public static function getCourseById($id): ?array
    {
        self::initData();
        foreach (self::$courses as $c) {
            if ($c['id'] === $id) {
                return $c;
            }
        }
        return null;
    }

    public static function getNotices(): array
    {
        self::initData();
        return self::$notices;
    }

    public static function getForumThreads(): array
    {
        self::initData();
        return self::$forums;
    }

    public static function getNotifications(): array
    {
        return [
            [
                'id' => 'N-1',
                'title' => 'Final Assessment Tersedia',
                'message' => 'Final assessment untuk course ini telah dibuka. Segera kerjakan sebelum batas waktu.',
                'time' => '10 menit yang lalu',
                'is_read' => false,
                'priority' => 'penting',
                'type' => 'assessment',
                'time_group' => 'hari_ini',
                'context' => 'Dasar-dasar K3 Produksi',
                'action_label' => 'Buka Assessment',
                'action_url' => '?route=/student/quiz',
            ],
            [
                'id' => 'N-2',
                'title' => 'Pengumuman Sistem',
                'message' =>
                    'Akan ada pemeliharaan server pada pukul 22:00 WIB selama kurang lebih 2 jam. Harap simpan semua pekerjaan Anda.',
                'time' => '1 jam yang lalu',
                'is_read' => true,
                'priority' => 'normal',
                'type' => 'system',
                'time_group' => 'hari_ini',
                'context' => 'Maintenance Server',
                'action_label' => '',
                'action_url' => '',
            ],
            [
                'id' => 'N-3',
                'title' => 'Komentar Baru di Forum',
                'message' => 'Budi Santoso membalas diskusi yang Anda ikuti.',
                'time' => '3 jam yang lalu',
                'is_read' => true,
                'priority' => 'normal',
                'type' => 'forum',
                'time_group' => 'hari_ini',
                'context' => 'Forum: Prosedur Kesela...',
                'action_label' => 'Buka Forum',
                'action_url' => '?route=/student/forum',
            ],
            [
                'id' => 'N-4',
                'title' => 'Course Baru Ditugaskan',
                'message' => 'Anda ditugaskan untuk menyelesaikan course baru berdasarkan jabatan dan golongan Anda.',
                'time' => '5 jam yang lalu',
                'is_read' => false,
                'priority' => 'penting',
                'type' => 'course',
                'time_group' => 'hari_ini',
                'context' => 'Advanced Lean Manufa...',
                'action_label' => 'Lihat Course',
                'action_url' => '?route=/student/course/detail&id=CRS002',
            ],
            [
                'id' => 'N-5',
                'title' => 'Sesi Baru Dibuka',
                'message' => 'Teacher Ahmad Hidayat baru saja membuka Sesi 2 untuk dipelajari.',
                'time' => '18 jam yang lalu',
                'is_read' => true,
                'priority' => 'normal',
                'type' => 'course',
                'time_group' => 'minggu_lalu',
                'context' => 'Sesi 2: Bagian Pembelaj...',
                'action_label' => 'Pelajari Sesi',
                'action_url' => '?route=/student/learning&id=CRS001&session=SES002',
            ],
            [
                'id' => 'N-6',
                'title' => 'Hasil Kuis Keluar',
                'message' => 'Anda mendapatkan nilai 85. Kuis telah berhasil diselesaikan.',
                'time' => 'Kemarin, 14:20',
                'is_read' => true,
                'priority' => 'normal',
                'type' => 'assessment',
                'time_group' => 'minggu_lalu',
                'context' => 'Kuis: Sesi 1',
                'action_label' => 'Lihat Hasil',
                'action_url' => '?route=/student/quiz/result',
            ],
            [
                'id' => 'N-7',
                'title' => 'Materi Video Tersedia',
                'message' => 'Materi video terbaru telah diunggah ke dalam sesi Anda.',
                'time' => 'Selasa, 09:00',
                'is_read' => true,
                'priority' => 'normal',
                'type' => 'video',
                'time_group' => 'minggu_lalu',
                'context' => 'Materi: Safety Protocol ...',
                'action_label' => 'Tonton Video',
                'action_url' => '?route=/student/learning',
            ],
            [
                'id' => 'N-8',
                'title' => 'Reminder Aktivitas Belajar',
                'message' => 'Anda belum menyelesaikan Sesi 1. Jangan lupa untuk melanjutkannya agar tetap on track.',
                'time' => 'Senin, 08:30',
                'is_read' => true,
                'priority' => 'penting',
                'type' => 'course',
                'time_group' => 'minggu_lalu',
                'context' => 'Dasar-dasar K3 Produksi',
                'action_label' => 'Lanjutkan Course',
                'action_url' => '?route=/student/learning',
            ],
            [
                'id' => 'N-9',
                'title' => 'Course Berhasil Diselesaikan',
                'message' => 'Selamat! Anda telah menyelesaikan seluruh sesi dan lulus assessment untuk course ini.',
                'time' => '12 Mei 2026',
                'is_read' => true,
                'priority' => 'normal',
                'type' => 'course',
                'time_group' => 'bulan_lalu',
                'context' => 'Dasar-dasar Manajeme...',
                'action_label' => 'Lihat Riwayat',
                'action_url' => '?route=/student/riwayat',
            ],
        ];
    }

    public static function getStats(): array
    {
        $all = self::getCourses();
        $active = self::getActiveCourses();
        $completed = self::getCompletedCourses();

        $scores = array_filter(array_column($completed, 'quiz_score'), function ($s) {
            return $s !== null;
        });
        $avgScore = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;

        $notifications = self::getNotifications();
        $unreadNotifs = count(
            array_filter($notifications, function ($n) {
                return !$n['is_read'];
            }),
        );

        return [
            'active_count' => count($active),
            'completed_count' => count($completed),
            'avg_score' => round($avgScore),
            'total_certificates' => count($completed),
            'unread_notifs' => $unreadNotifs,
        ];
    }

    public static function getQuestionBank(): array
    {
        return [
            /** Kelompok Data: Soal Pilihan Ganda. */
            1 => [
                'id' => 1,
                'type' => 'pg',
                'question' => 'Apa kepanjangan dari TPM?',
                'options' => [
                    'A' => 'Total Productive Maintenance',
                    'B' => 'Total Preventive Maintenance',
                    'C' => 'Time Productive Maintenance',
                    'D' => 'Total Process Management',
                ],
                'correct_answer' => 'A',
                'weight' => 5,
            ],
            2 => [
                'id' => 2,
                'type' => 'pg',
                'question' => 'Manakah yang bukan termasuk dari 5S?',
                'options' => [
                    'A' => 'Seiri',
                    'B' => 'Seiton',
                    'C' => 'Seiketsu',
                    'D' => 'Seishin',
                ],
                'correct_answer' => 'D',
                'weight' => 5,
            ],
            4 => [
                'id' => 4,
                'type' => 'pg',
                'question' => 'Standard operating procedure (SOP) dibuat untuk?',
                'options' => [
                    'A' => 'Mempersulit pekerjaan',
                    'B' => 'Menjaga konsistensi dan standar kualitas',
                    'C' => 'Hiasan dinding',
                    'D' => 'Meningkatkan biaya',
                ],
                'correct_answer' => 'B',
                'weight' => 5,
            ],
            5 => [
                'id' => 5,
                'type' => 'pg',
                'question' => 'Alat Pelindung Diri (APD) harus dipakai saat?',
                'options' => [
                    'A' => 'Istirahat',
                    'B' => 'Memasuki area berbahaya/produksi',
                    'C' => 'Makan',
                    'D' => 'Pulang',
                ],
                'correct_answer' => 'B',
                'weight' => 5,
            ],
            6 => [
                'id' => 6,
                'type' => 'pg_image',
                'question' => 'Perhatikan gambar rambu keselamatan berikut. Apa arti dari rambu tersebut?',
                'image_url' =>
                    'https://images.unsplash.com/photo-1571589335687-70e6481cc8cc?w=600&auto=format&fit=crop',
                'options' => [
                    'A' => 'Wajib Menggunakan Masker',
                    'B' => 'Kawasan Wajib Sepatu Safety',
                    'C' => 'Bahaya Tegangan Tinggi',
                    'D' => 'Titik Kumpul Evakuasi',
                ],
                'correct_answer' => 'C',
                'weight' => 5,
            ],
            7 => [
                'id' => 7,
                'type' => 'pg',
                'question' => 'Apa tujuan utama dari proses "Check" dalam siklus PDCA?',
                'options' => [
                    'A' => 'Membuat rencana perbaikan',
                    'B' => 'Melaksanakan perbaikan',
                    'C' => 'Membandingkan hasil eksekusi dengan rencana',
                    'D' => 'Menjadikan perbaikan sebagai standar baru',
                ],
                'correct_answer' => 'C',
                'weight' => 5,
            ],
            8 => [
                'id' => 8,
                'type' => 'pg',
                'question' =>
                    'Dalam konsep lean manufacturing, waste yang berupa penumpukan barang setengah jadi disebut?',
                'options' => [
                    'A' => 'Overproduction',
                    'B' => 'Waiting',
                    'C' => 'Inventory',
                    'D' => 'Defect',
                ],
                'correct_answer' => 'C',
                'weight' => 5,
            ],
            9 => [
                'id' => 9,
                'type' => 'pg_image',
                'question' =>
                    'Berikut adalah grafik defect bulan ini. Pada minggu ke berapa defect rate paling tinggi?',
                'image_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&auto=format&fit=crop',
                'options' => [
                    'A' => 'Minggu ke-1',
                    'B' => 'Minggu ke-2',
                    'C' => 'Minggu ke-3',
                    'D' => 'Minggu ke-4',
                ],
                'correct_answer' => 'B',
                'weight' => 5,
            ],
            10 => [
                'id' => 10,
                'type' => 'pg',
                'question' => 'Siapa yang bertanggung jawab mengisi logbook harian mesin (Check Sheet)?',
                'options' => [
                    'A' => 'Hanya Supervisor',
                    'B' => 'Operator mesin yang bersangkutan',
                    'C' => 'Tim Maintenance saja',
                    'D' => 'Manajer Pabrik',
                ],
                'correct_answer' => 'B',
                'weight' => 5,
            ],
            11 => [
                'id' => 11,
                'type' => 'pg',
                'question' =>
                    'Jika Anda menemukan genangan oli di area jalur jalan, langkah pertama yang sesuai standar K3 adalah?',
                'options' => [
                    'A' => 'Membiarkannya karena tugas petugas kebersihan',
                    'B' => 'Melompati genangan tersebut',
                    'C' => 'Mengamankan area dan melaporkan/membersihkan segera',
                    'D' => 'Menunggu sampai shift selesai',
                ],
                'correct_answer' => 'C',
                'weight' => 5,
            ],

            /** Kelompok Data: Soal Uraian. */
            3 => [
                'id' => 3,
                'type' => 'essay',
                'question' => 'Jelaskan secara singkat tujuan dari penerapan Kaizen di area kerja Anda!',
                'weight' => 10,
            ],
            12 => [
                'id' => 12,
                'type' => 'essay',
                'question' =>
                    'Sebutkan 3 jenis pemborosan (waste) yang paling sering Anda temui di area kerja, dan berikan usulan solusinya.',
                'weight' => 15,
            ],
            13 => [
                'id' => 13,
                'type' => 'essay',
                'question' =>
                    'Jelaskan langkah-langkah yang harus dilakukan jika mesin mengalami anomali suara secara tiba-tiba.',
                'weight' => 10,
            ],
            14 => [
                'id' => 14,
                'type' => 'essay_image',
                'question' =>
                    'Berdasarkan denah evakuasi di bawah ini, jelaskan jalur evakuasi terbaik jika terjadi kebakaran di Area Assembling 1!',
                'image_url' =>
                    'https://images.unsplash.com/photo-1502920514313-52581002a659?w=600&auto=format&fit=crop',
                'weight' => 15,
            ],
            15 => [
                'id' => 15,
                'type' => 'essay',
                'question' =>
                    'Mengapa komunikasi saat serah terima (handover) shift sangat kritikal untuk kelancaran produksi?',
                'weight' => 10,
            ],
            16 => [
                'id' => 16,
                'type' => 'essay',
                'question' =>
                    'Deskripsikan alur pelaporan jika Anda menemukan cacat (defect) pada komponen yang berasal dari supplier.',
                'weight' => 10,
            ],
            17 => [
                'id' => 17,
                'type' => 'essay_image',
                'question' =>
                    'Perhatikan foto penempatan barang berikut. Analisis apakah penempatan tersebut sudah sesuai standar 5S (Seiton). Berikan alasan Anda.',
                'image_url' =>
                    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600&auto=format&fit=crop',
                'weight' => 15,
            ],
            18 => [
                'id' => 18,
                'type' => 'essay',
                'question' =>
                    'Apa peran Anda sebagai operator dalam mendukung pencapaian target efisiensi energi di perusahaan?',
                'weight' => 10,
            ],
            19 => [
                'id' => 19,
                'type' => 'essay',
                'question' => 'Berikan satu contoh riil penerapan Poka-Yoke (Mistake Proofing) di seksi Anda.',
                'weight' => 10,
            ],
            20 => [
                'id' => 20,
                'type' => 'essay',
                'question' =>
                    'Bagaimana sikap Anda jika diminta melakukan pekerjaan yang SOP-nya belum pernah Anda terima pelatihannya?',
                'weight' => 10,
            ],
        ];
    }

    /** ========================================================================= */
    /** DATA PROFIL KARYAWAN & PUSAT AKUN. */
    /** ========================================================================= */

    /**
     * Mengembalikan data profil lengkap karyawan (dummy).
     * Saat migrasi ke database, ganti isi method ini dengan query ke
     * DX_LMS_M_Employees JOIN DX_LMS_M_Positions, DX_LMS_M_Golongan, dst.
     */
    public static function getEmployeeProfile(): array
    {
        $sessionUser = getPortalUser('student') ?? [];
        $fullname = $sessionUser['fullname'] ?? ($sessionUser['name'] ?? 'Budi Santoso');
        $npk = $sessionUser['npk'] ?? '12345';
        $role = ucfirst($sessionUser['role'] ?? 'Student');
        $position = $sessionUser['jabatan'] ?? ($sessionUser['position'] ?? 'Operator');
        $golongan = $sessionUser['golongan'] ?? 'Gol 1';
        $department = !empty($sessionUser['department']) ? $sessionUser['department'] : 'Manufacturing';
        $division = !empty($sessionUser['division']) ? $sessionUser['division'] : 'Operation';
        $section = !empty($sessionUser['section']) ? $sessionUser['section'] : 'Production A';

        return [
            'npk' => $npk,
            'fullname' => $fullname,
            'name' => $fullname,
            'email' => $sessionUser['email'] ?? 'user@perusahaan.co.id',
            'phone' => '0812-3456-7890',
            'photo_url' => '',
            'role' => $role,
            'position' => $position,
            'golongan' => $golongan,
            'golongan_label' => $position . ' ' . $golongan,
            'department' => $department,
            'division' => $division,
            'section' => $section,
            'status' => 'Aktif',
            'join_date' => '2019-03-15',
            'direct_supervisor' => 'Ahmad Hidayat, SPV',
            'work_location' => 'Plant 1 — Bekasi',
        ];
    }

    /**
     * Mengembalikan riwayat karir karyawan (timeline, dummy).
     * Saat migrasi ke database, query dari tabel riwayat jabatan.
     */
    public static function getCareerHistory(): array
    {
        return [
            [
                'position' => 'Leader',
                'golongan' => 'Gol 2',
                'department' => 'Produksi',
                'division' => 'Manufacturing',
                'section' => 'Assembly Line A',
                'start_date' => '2024-01-10',
                'end_date' => null,
                'type' => 'promosi',
                'description' => 'Promosi dari Leader Gol 1 ke Leader Gol 2 atas pencapaian target produksi.',
            ],
            [
                'position' => 'Leader',
                'golongan' => 'Gol 1',
                'department' => 'Produksi',
                'division' => 'Manufacturing',
                'section' => 'Assembly Line A',
                'start_date' => '2022-06-01',
                'end_date' => '2024-01-09',
                'type' => 'promosi',
                'description' => 'Promosi dari Operator Gol 3 ke Leader berdasarkan evaluasi kinerja tahunan.',
            ],
            [
                'position' => 'Operator',
                'golongan' => 'Gol 3',
                'department' => 'Produksi',
                'division' => 'Manufacturing',
                'section' => 'Machining Line B',
                'start_date' => '2021-02-15',
                'end_date' => '2022-05-31',
                'type' => 'mutasi',
                'description' => 'Mutasi dari Assembly Line A ke Machining Line B untuk pengembangan kompetensi.',
            ],
            [
                'position' => 'Operator',
                'golongan' => 'Gol 2',
                'department' => 'Produksi',
                'division' => 'Manufacturing',
                'section' => 'Assembly Line A',
                'start_date' => '2020-03-01',
                'end_date' => '2021-02-14',
                'type' => 'promosi',
                'description' => 'Kenaikan golongan reguler setelah evaluasi 1 tahun.',
            ],
            [
                'position' => 'Operator',
                'golongan' => 'Gol 1',
                'department' => 'Produksi',
                'division' => 'Manufacturing',
                'section' => 'Assembly Line A',
                'start_date' => '2019-03-15',
                'end_date' => '2020-02-29',
                'type' => 'penempatan_awal',
                'description' => 'Penempatan awal setelah masa orientasi karyawan baru.',
            ],
        ];
    }

    /**
     * Mapping golongan per jabatan sesuai aturan bisnis DLS.
     * Pola: Gol 1 = Junior, Gol 2 = Middle, Gol 3 = Senior.
     */
    public static function getGolonganMap(): array
    {
        return [
            'Operator' => [
                ['id' => 'gol_1', 'label' => 'Gol 1', 'name' => 'Operator Junior'],
                ['id' => 'gol_2', 'label' => 'Gol 2', 'name' => 'Operator Middle'],
                ['id' => 'gol_3', 'label' => 'Gol 3', 'name' => 'Operator Senior'],
            ],
            'Staff' => [
                ['id' => 'gol_1', 'label' => 'Gol 1', 'name' => 'Staff Junior'],
                ['id' => 'gol_2', 'label' => 'Gol 2', 'name' => 'Staff Middle'],
                ['id' => 'gol_3', 'label' => 'Gol 3', 'name' => 'Staff Senior'],
            ],
            'Leader' => [
                ['id' => 'gol_1', 'label' => 'Gol 1', 'name' => 'Leader Junior'],
                ['id' => 'gol_2', 'label' => 'Gol 2', 'name' => 'Leader Middle'],
                ['id' => 'gol_3', 'label' => 'Gol 3', 'name' => 'Leader Senior'],
            ],
            'SPV' => [
                ['id' => 'gol_1', 'label' => 'Gol 1', 'name' => 'Supervisor Junior'],
                ['id' => 'gol_2', 'label' => 'Gol 2', 'name' => 'Supervisor Middle'],
                ['id' => 'gol_3', 'label' => 'Gol 3', 'name' => 'Supervisor Senior'],
            ],
        ];
    }

    /**
     * Learning summary terperinci untuk profil.
     */
    public static function getLearningStats(): array
    {
        $stats = self::getStats();
        return array_merge($stats, [
            'mandatory_incomplete' => 1,
            'total_study_hours' => 48,
        ]);
    }

    /**
     * Riwayat login dummy untuk keamanan akun.
     */
    public static function getLoginHistory(): array
    {
        return [
            [
                'device' => 'Chrome — Windows 11',
                'ip' => '192.168.1.45',
                'time' => '2026-06-26 07:32',
                'location' => 'Plant 1 — Bekasi',
                'current' => true,
            ],
            [
                'device' => 'Safari — iPhone 14',
                'ip' => '10.0.0.112',
                'time' => '2026-06-25 18:15',
                'location' => 'Mobile Network',
                'current' => false,
            ],
            [
                'device' => 'Chrome — Windows 11',
                'ip' => '192.168.1.45',
                'time' => '2026-06-25 07:45',
                'location' => 'Plant 1 — Bekasi',
                'current' => false,
            ],
            [
                'device' => 'Edge — Windows 11',
                'ip' => '192.168.1.102',
                'time' => '2026-06-24 08:10',
                'location' => 'Plant 1 — Bekasi',
                'current' => false,
            ],
            [
                'device' => 'Chrome — Android 14',
                'ip' => '10.0.0.88',
                'time' => '2026-06-23 19:30',
                'location' => 'Mobile Network',
                'current' => false,
            ],
        ];
    }
}
