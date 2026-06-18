<?php
/**
 * DX_LMS_M_StudentData
 * Single Source of Truth untuk seluruh data dummy Student LMS.
 * 
 * File ini menjadi satu-satunya sumber data yang dipakai oleh semua halaman Student:
 * Dashboard, Course, Riwayat, Notifikasi, CourseDetail, dan RiwayatDetail.
 * 
 * Ketika nanti diganti ke database asli, cukup ganti isi method-method di class ini.
 */

    /**
     * Kelas DX_StudentData
     * Didokumentasikan sesuai Panduan Program Standar.
     */
class DX_StudentData
{
    /**
     * Mendapatkan seluruh daftar course.
     * Ini adalah data master yang direferensikan oleh semua halaman.
     *
     * @return array
     */
    public static function getCourses(): array
    {
        return [
            [
                'id'               => 'CRS001',
                'title'            => 'Dasar-dasar K3 Produksi',
                'description'      => 'Pelajari prosedur Keselamatan dan Kesehatan Kerja (K3) standar yang berlaku di seluruh area produksi.',
                'thumbnail'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAiRV2Li9BIGOpv0Km6rF-20aWJBG8EEIyvIXbnT54btkPKnKo8vnxBMo7j7rPbYExTKJAhReZSrMMNZyvKokC4v-uFsvM-fzXVNpRrVG_Y328Aa2WteHU4K0QmfguLzX-5Uu61S84rYo_h6-reiOq_foZCdFswCLovNNrKvsYx6slO8zezYtdRbc_7FvP2LTv1d8I6jxNqWO6XSLafhKUIkkfMUvFN-SyhFjfe-YKRUlpfgLtaBDyeCC0nq8ztKPdzao-1Fqsse1I',
                'category'         => 'Wajib',
                'filters'          => ['Hard Skill', 'Safety', 'Technical'],
                'modules_count'    => 4,
                'status'           => 'sedang_dipelajari',
                'progress'         => 65,
                'last_accessed'    => '2025-06-10 14:30:00',
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 75,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS002',
                'title'            => 'Pengenalan 5S Area Produksi',
                'description'      => 'Materi pengenalan 5S (Seiri, Seiton, Seiso, Seiketsu, Shitsuke) untuk budaya kerja yang lebih baik.',
                'thumbnail'        => null, // Menggunakan icon placeholder
                'thumbnail_icon'   => 'precision_manufacturing',
                'category'         => 'Wajib',
                'filters'          => ['Soft Skill', 'Management', 'Quality'],
                'modules_count'    => 2,
                'status'           => 'belum_dimulai',
                'progress'         => 0,
                'last_accessed'    => null,
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 0,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS003',
                'title'            => 'Quality Control Basic',
                'description'      => 'Dasar-dasar pengendalian kualitas produk sesuai dengan standar Perusahaan.',
                'thumbnail'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD-XQBb8vaUq4XtbvXn4rFJ4HhWf6gmgXidOMH0HgAdDCtY3J_mtzxLGQEuWPSSQyRNey1xCsZsplG2WxOJwjbo8SmEswrT_HnA4KMdILsw5RFSSWZt2oSGC5J7rf_2He-KZwzWONcgwrm-je-SUy6SF0AQ1Hn2UmHY1EBHYihXK68a_mUfDNAYUBWgEoNgOvVUfeZErJBdI5UFZK7G0_gDzXxL-nMtzQg5DfySaiKKDPfDHW4i200YsgCYkiNowXxWsHoovCiH1Fw',
                'category'         => 'Wajib',
                'filters'          => ['Hard Skill', 'Quality', 'Technical'],
                'modules_count'    => 3,
                'status'           => 'selesai',
                'progress'         => 100,
                'last_accessed'    => '2025-06-05 09:00:00',
                'completion_date'  => '2025-06-05',
                'quiz_score'       => 88,
                'attendance'       => 100,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS004',
                'title'            => 'Production System',
                'description'      => 'Pelajari filosofi monozukuri dan sistem produksi efisien.',
                'thumbnail'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBWyQL0ji0nP7bRKeShqCA1ircNDVY8x7Ybh2Oiqw10CTr8OBpvd8hpFXaVhC7DRbT4DpYzXUAwXahdzRAHiGXnxXq-ScIo_UHhrBcaEy8BDQVtw5YY2fFfhr580Hr1gdnl5XcbGeDlXOD9l3wN6yluYNh784CSWpxWgIcdu2MLaq8KKvbhqzJX4jMZvluNRZ7gtjkmJXbvio77ssVuHU9WEfS-z0OCN-TDbVwhwZOm-wjnm6nVuCfm7gtyK5NcR9kMb66Uzh-59tU',
                'category'         => 'Wajib',
                'filters'          => ['Hard Skill', 'Management', 'Engineering'],
                'modules_count'    => 5,
                'status'           => 'selesai',
                'progress'         => 100,
                'last_accessed'    => '2025-05-28 16:00:00',
                'completion_date'  => '2025-05-28',
                'quiz_score'       => 92,
                'attendance'       => 100,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS005',
                'title'            => 'Safety Awareness Training',
                'description'      => 'Pahami prosedur keselamatan dasar di area produksi untuk meminimalisir risiko kecelakaan kerja.',
                'thumbnail'        => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDo82B72H4r9ssnhe7RS3Smf8PvEQRRjEIr465LZfNy4AKhbarW3lk4teJafdpV6telDUS5YQGHBH54FySi8MOTQVcMKy5UqzU-hMIIZvYNmFPlYhVppyI7zCRfaYgLZlXgzW10dUfF21TbwUssyTOsMF6ubm7_IKOhbJKDn_byoxR4sLBFVijnEhmCvVSwDrr1ylY2isMvi_UMv2VyXRlxLOElIijUv8Rjv-cP1NuoWvaDobAZU24Tl4t1YUT5IwcSAmxL83fP7rA',
                'category'         => 'Wajib',
                'filters'          => ['Soft Skill', 'Safety', 'Leadership'],
                'modules_count'    => 3,
                'status'           => 'selesai',
                'progress'         => 100,
                'last_accessed'    => '2025-06-20 11:00:00',
                'completion_date'  => '2025-06-20',
                'quiz_score'       => 100,
                'attendance'       => 100,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS006',
                'title'            => 'Dasar Pemrograman Web',
                'description'      => 'Pengenalan teknologi dasar pengembangan web untuk kebutuhan sistem otomasi industri.',
                'thumbnail'        => null,
                'thumbnail_icon'   => 'code',
                'category'         => 'Wajib',
                'filters'          => ['IT', 'Technical', 'Engineering'],
                'modules_count'    => 6,
                'status'           => 'belum_dimulai',
                'progress'         => 0,
                'last_accessed'    => null,
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 0,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS007',
                'title'            => 'Advanced Leadership Program',
                'description'      => 'Program pengembangan kepemimpinan lanjutan untuk menyiapkan calon supervisor yang tangguh.',
                'thumbnail'        => null,
                'thumbnail_icon'   => 'groups',
                'category'         => 'Wajib',
                'filters'          => ['Leadership', 'Soft Skill', 'Management'],
                'modules_count'    => 5,
                'status'           => 'sedang_dipelajari',
                'progress'         => 45,
                'last_accessed'    => date('Y-m-d H:i:s', strtotime('-1 days')),
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 50,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS008',
                'title'            => 'Lean Manufacturing Principles',
                'description'      => 'Prinsip-prinsip manufaktur ramping (Lean) untuk meminimalisir pemborosan dalam proses produksi.',
                'thumbnail'        => null,
                'thumbnail_icon'   => 'trending_up',
                'category'         => 'Wajib',
                'filters'          => ['Management', 'Quality', 'Hard Skill'],
                'modules_count'    => 4,
                'status'           => 'belum_dimulai',
                'progress'         => 0,
                'last_accessed'    => null,
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 0,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS009',
                'title'            => 'Basic Data Analytics',
                'description'      => 'Belajar mengolah data produksi menggunakan tools analisis data dasar untuk pengambilan keputusan.',
                'thumbnail'        => null,
                'thumbnail_icon'   => 'bar_chart',
                'category'         => 'Wajib',
                'filters'          => ['IT', 'Technical'],
                'modules_count'    => 3,
                'status'           => 'sedang_dipelajari',
                'progress'         => 20,
                'last_accessed'    => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 33,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
            [
                'id'               => 'CRS010',
                'title'            => 'Komunikasi Efektif di Lingkungan Kerja',
                'description'      => 'Teknik komunikasi asertif antar departemen demi terciptanya lingkungan kerja yang harmonis.',
                'thumbnail'        => null,
                'thumbnail_icon'   => 'forum',
                'category'         => 'Wajib',
                'filters'          => ['Soft Skill', 'Leadership'],
                'modules_count'    => 2,
                'status'           => 'belum_dimulai',
                'progress'         => 0,
                'last_accessed'    => null,
                'completion_date'  => null,
                'quiz_score'       => null,
                'attendance'       => 0,
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['status' => 'selesai', 'score' => 80, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['status' => 'selesai', 'score' => 90, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['status' => 'selesai', 'score' => 100, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]],
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => ['status' => 'terkunci', 'score' => null, 'questions' => [1,2,3,4,5]]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'questions' => [1,2,3,4,5,6,7,8,9,10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM01',
                        'title' => 'Diskusi Materi Sesi 1',
                        'replies' => 5,
                        'last_active' => '1 jam yang lalu'
                    ],
                    [
                        'id' => 'FRM02',
                        'title' => 'Pertanyaan Seputar Tugas Akhir',
                        'replies' => 2,
                        'last_active' => 'Kemarin'
                    ]
                ],
            ],
        ];
    }


    /**
     * Mendapatkan course yang sedang aktif dipelajari.
     *
     * @return array
     */
    public static function getActiveCourses(): array
    {
        return array_values(array_filter(self::getCourses(), function ($c) {
            return $c['status'] === 'sedang_dipelajari';
        }));
    }

    /**
     * Mendapatkan course yang sudah selesai (untuk Riwayat).
     *
     * @return array
     */
    public static function getCompletedCourses(): array
    {
        return array_values(array_filter(self::getCourses(), function ($c) {
            return $c['status'] === 'selesai';
        }));
    }

    /**
     * Mendapatkan satu course berdasarkan ID.
     *
     * @param string $id
     * @return array|null
     */
    public static function getCourseById(string $id): ?array
    {
        foreach (self::getCourses() as $course) {
            if ($course['id'] === $id) {
                return $course;
            }
        }
        return null;
    }

    /**
     * Mendapatkan statistik agregat Student yang dihitung dari data course.
     *
     * @return array
     */
    public static function getStats(): array
    {
        $courses = self::getCourses();
        $active = self::getActiveCourses();
        $completed = self::getCompletedCourses();

        // Hitung rata-rata skor quiz dari course yang selesai
        $scores = array_filter(array_column($completed, 'quiz_score'), function ($s) {
            return $s !== null;
        });
        $avgScore = count($scores) > 0 ? (int) round(array_sum($scores) / count($scores)) : 0;

        return [
            'total_courses'    => count($courses),
            'active_count'     => count($active),
            'completed_count'  => count($completed),
            'avg_score'        => $avgScore,
        ];
    }

    /**
     * Mendapatkan daftar notifikasi yang mereferensikan course dari daftar master.
     * Setiap notifikasi terhubung secara logis dengan course yang ada.
     *
     * @return array
     */
    public static function getNotifications(): array
    {
        return [
            // === Hari Ini ===
            [
                'id'           => 'NTF001',
                'type'         => 'materi',
                'course_id'    => 'CRS001',
                'course_title' => 'Dasar-dasar K3 Produksi',
                'message'      => 'Materi baru telah ditambahkan pada course <strong>Dasar-dasar K3 Produksi</strong> — Sesi 3: Prosedur Evakuasi Darurat.',
                'time'         => '5 menit yang lalu',
                'time_group'   => 'hari_ini',
                'is_read'      => false,
            ],
            [
                'id'           => 'NTF002',
                'type'         => 'course',
                'course_id'    => 'CRS002',
                'course_title' => 'Pengenalan 5S Area Produksi',
                'message'      => 'Course baru <strong>Pengenalan 5S Area Produksi</strong> telah tersedia untuk departemen Anda.',
                'time'         => '30 menit yang lalu',
                'time_group'   => 'hari_ini',
                'is_read'      => false,
            ],
            [
                'id'           => 'NTF003',
                'type'         => 'quiz',
                'course_id'    => 'CRS003',
                'course_title' => 'Quality Control Basic',
                'message'      => 'Hasil quiz <strong>Quality Control Basic</strong> telah dinilai. Skor Anda: <strong>88/100</strong>.',
                'time'         => '1 jam yang lalu',
                'time_group'   => 'hari_ini',
                'is_read'      => true,
            ],
            // === Kemarin ===
            [
                'id'           => 'NTF004',
                'type'         => 'video',
                'course_id'    => 'CRS001',
                'course_title' => 'Dasar-dasar K3 Produksi',
                'message'      => 'Video materi <strong>Dasar-dasar K3 Produksi</strong> Sesi 2 sudah dapat diakses.',
                'time'         => 'Kemarin, 14:30',
                'time_group'   => 'kemarin',
                'is_read'      => true,
            ],
            [
                'id'           => 'NTF005',
                'type'         => 'course',
                'course_id'    => 'CRS005',
                'course_title' => 'Safety Awareness Training',
                'message'      => 'Selamat! Anda telah menyelesaikan course <strong>Safety Awareness Training</strong> dengan skor sempurna.',
                'time'         => 'Kemarin, 11:00',
                'time_group'   => 'kemarin',
                'is_read'      => true,
            ],
        ];
    }
}
