<?php
/**
 * DX_LMS_M_StudentData (Data Statis Siswa).
 * 
 * Sumber Kebenaran Tunggal (Single Source of Truth) untuk pengelolaan 
 * data percobaan (dummy/mock data) pada modul LMS Siswa. File ini 
 * menjadi fondasi data yang digunakan oleh berbagai Halaman Siswa seperti 
 * Dasbor, Kursus, Riwayat, Pemberitahuan, dsb. sebelum sepenuhnya 
 * bermigrasi ke basis data dinamis.
 */

class DX_StudentData
{
    public static function getCourses(): array
    {
        return [
            [
                'id' => 'CRS001',
                'title' => 'Dasar-dasar K3 Produksi',
                'description' => 'Modul pelatihan profesional tentang dasar-dasar k3 produksi yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Management', 'Hard Skill', 'IT'],
                'modules_count' => 3,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-05-26 16:38:03',
                'completion_date' => '2026-06-09 16:38:03',
                'quiz_score' => 76,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0011',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 97,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 17:38:03',
                                    'score' => 43
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 18:38:03',
                                    'score' => 85
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0011A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0011B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 86,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 09:38:03',
                                    'score' => 82
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0012',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 07:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 19:38:03',
                                    'score' => 43
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-10 10:38:03',
                                    'score' => 53
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0012A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0012B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 16:38:03',
                                    'score' => 63
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 02:38:03',
                                    'score' => 48
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-19 02:38:03',
                                    'score' => 59
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0013',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 74,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 21:38:03',
                                    'score' => 97
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0013A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0013B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 84,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 11:38:03',
                                    'score' => 83
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 76,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-10 06:38:03',
                            'score' => 99
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 91,
                    'nilai' => 78,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-10 11:38:03',
                            'nilai' => 49
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-11 09:38:03',
                            'nilai' => 76
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM001A',
                        'title' => 'Diskusi Modul',
                        'replies' => 9,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS002',
                'title' => 'Internet of Things (IoT) di Industri 4.0',
                'description' => 'Modul pelatihan profesional tentang internet of things (iot) di industri 4.0 yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Hard Skill', 'Soft Skill', 'Leadership', 'Technical'],
                'modules_count' => 5,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-18 16:38:03',
                'completion_date' => '2026-06-06 16:38:03',
                'quiz_score' => 87,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0021',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 20:38:03',
                                    'score' => 100
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0021A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0021B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 03:38:03',
                                    'score' => 49
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 00:38:03',
                                    'score' => 67
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-12 08:38:03',
                                    'score' => 91
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0022',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 01:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-09 18:38:03',
                                    'score' => 66
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-11 08:38:03',
                                    'score' => 69
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0022A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0022B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 14:38:03',
                                    'score' => 56
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 12:38:03',
                                    'score' => 66
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0023',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 77,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 21:38:03',
                                    'score' => 49
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 16:38:03',
                                    'score' => 61
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-18 20:38:03',
                                    'score' => 58
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0023A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0023B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 85,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 13:38:03',
                                    'score' => 47
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 17:38:03',
                                    'score' => 41
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-10 12:38:03',
                                    'score' => 95
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0024',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 01:38:03',
                                    'score' => 52
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0024A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0024B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 83,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 03:38:03',
                                    'score' => 66
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 22:38:03',
                                    'score' => 71
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0025',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 81,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 00:38:03',
                                    'score' => 76
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0025A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0025B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 20:38:03',
                                    'score' => 65
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 08:38:03',
                                    'score' => 43
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-14 15:38:03',
                                    'score' => 78
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 87,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-15 16:38:03',
                            'score' => 84
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 79,
                    'nilai' => 90,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-15 02:38:03',
                            'nilai' => 57
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM002A',
                        'title' => 'Diskusi Modul',
                        'replies' => 7,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS003',
                'title' => 'Dasar Robotika Industri',
                'description' => 'Modul pelatihan profesional tentang dasar robotika industri yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Technical', 'Safety', 'Automation'],
                'modules_count' => 4,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-05-30 16:38:03',
                'completion_date' => '2026-06-02 16:38:03',
                'quiz_score' => 96,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0031',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 96,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 01:38:03',
                                    'score' => 41
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-09 19:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-12 10:38:03',
                                    'score' => 81
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0031A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0031B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 72,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 10:38:03',
                                    'score' => 47
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-09 17:38:03',
                                    'score' => 83
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0032',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 05:38:03',
                                    'score' => 66
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 22:38:03',
                                    'score' => 97
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0032A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0032B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 84,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 04:38:03',
                                    'score' => 41
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 16:38:03',
                                    'score' => 54
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-16 14:38:03',
                                    'score' => 95
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0033',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 86,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 14:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 01:38:03',
                                    'score' => 59
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0033A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0033B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 95,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 07:38:03',
                                    'score' => 67
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 09:38:03',
                                    'score' => 72
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-14 21:38:03',
                                    'score' => 95
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0034',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 05:38:03',
                                    'score' => 59
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 22:38:03',
                                    'score' => 68
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-15 21:38:03',
                                    'score' => 90
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0034A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0034B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 89,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 04:38:03',
                                    'score' => 51
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 02:38:03',
                                    'score' => 47
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-13 10:38:03',
                                    'score' => 100
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 96,
                    'attempts' => 3,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-09 17:38:03',
                            'score' => 72
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-17 08:38:03',
                            'score' => 67
                        ],
                        [
                            'attempt' => 3,
                            'date' => '2026-06-10 19:38:03',
                            'score' => 71
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 83,
                    'nilai' => 79,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-10 11:38:03',
                            'nilai' => 94
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM003A',
                        'title' => 'Diskusi Modul',
                        'replies' => 5,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS004',
                'title' => 'Pengukuran Kepuasan Pelanggan Internal',
                'description' => 'Modul pelatihan profesional tentang pengukuran kepuasan pelanggan internal yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Soft Skill', 'Communication', 'Compliance', 'Automation'],
                'modules_count' => 4,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-19 16:38:03',
                'completion_date' => '2026-06-17 16:38:03',
                'quiz_score' => 93,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0041',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 93,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 06:38:03',
                                    'score' => 49
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 05:38:03',
                                    'score' => 55
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0041A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0041B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 15:38:03',
                                    'score' => 74
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0042',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 75,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 16:38:03',
                                    'score' => 53
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 00:38:03',
                                    'score' => 74
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-12 12:38:03',
                                    'score' => 68
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0042A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0042B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 06:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 10:38:03',
                                    'score' => 56
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-11 08:38:03',
                                    'score' => 87
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0043',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 22:38:03',
                                    'score' => 65
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0043A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0043B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 88,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 03:38:03',
                                    'score' => 47
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 08:38:03',
                                    'score' => 68
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0044',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 89,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 15:38:03',
                                    'score' => 72
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 00:38:03',
                                    'score' => 52
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-12 01:38:03',
                                    'score' => 95
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0044A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0044B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 88,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 18:38:03',
                                    'score' => 59
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 21:38:03',
                                    'score' => 77
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 93,
                    'attempts' => 4,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-16 15:38:03',
                            'score' => 62
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-12 15:38:03',
                            'score' => 65
                        ],
                        [
                            'attempt' => 3,
                            'date' => '2026-06-14 02:38:03',
                            'score' => 50
                        ],
                        [
                            'attempt' => 4,
                            'date' => '2026-06-15 19:38:03',
                            'score' => 100
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 91,
                    'nilai' => 97,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-11 04:38:03',
                            'nilai' => 47
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-15 22:38:03',
                            'nilai' => 52
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM004A',
                        'title' => 'Diskusi Modul',
                        'replies' => 3,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS005',
                'title' => 'Pelayanan Prima (Service Excellence)',
                'description' => 'Modul pelatihan profesional tentang pelayanan prima (service excellence) yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['HR', 'Compliance'],
                'modules_count' => 5,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-02 16:38:03',
                'completion_date' => '2026-06-17 16:38:03',
                'quiz_score' => 85,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0051',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 06:38:03',
                                    'score' => 43
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 07:38:03',
                                    'score' => 55
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-19 03:38:03',
                                    'score' => 67
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0051A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0051B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 77,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 07:38:03',
                                    'score' => 52
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0052',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 89,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 04:38:03',
                                    'score' => 57
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 07:38:03',
                                    'score' => 72
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0052A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0052B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 93,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 00:38:03',
                                    'score' => 64
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 16:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-16 19:38:03',
                                    'score' => 82
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0053',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 84,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 11:38:03',
                                    'score' => 68
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0053A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0053B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 90,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 15:38:03',
                                    'score' => 62
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-09 21:38:03',
                                    'score' => 83
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0054',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 05:38:03',
                                    'score' => 98
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0054A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0054B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 07:38:03',
                                    'score' => 64
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0055',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 20:38:03',
                                    'score' => 61
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 07:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-16 11:38:03',
                                    'score' => 97
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0055A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0055B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 01:38:03',
                                    'score' => 72
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 08:38:03',
                                    'score' => 100
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 85,
                    'attempts' => 3,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-18 21:38:03',
                            'score' => 48
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-10 01:38:03',
                            'score' => 49
                        ],
                        [
                            'attempt' => 3,
                            'date' => '2026-06-14 08:38:03',
                            'score' => 83
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 83,
                    'nilai' => 98,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-11 22:38:03',
                            'nilai' => 60
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-14 13:38:03',
                            'nilai' => 98
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM005A',
                        'title' => 'Diskusi Modul',
                        'replies' => 10,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS006',
                'title' => 'Advanced Leadership Program',
                'description' => 'Modul pelatihan profesional tentang advanced leadership program yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Communication', 'Compliance', 'Soft Skill', 'Safety'],
                'modules_count' => 3,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-06 16:38:03',
                'completion_date' => '2026-06-03 16:38:03',
                'quiz_score' => 83,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0061',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 81,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 00:38:03',
                                    'score' => 72
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-19 07:38:03',
                                    'score' => 74
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0061A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0061B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 94,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 01:38:03',
                                    'score' => 60
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-19 15:38:03',
                                    'score' => 53
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0062',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 90,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 11:38:03',
                                    'score' => 53
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-19 15:38:03',
                                    'score' => 81
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0062A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0062B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 83,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 21:38:03',
                                    'score' => 52
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-14 03:38:03',
                                    'score' => 79
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0063',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 14:38:03',
                                    'score' => 48
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-14 13:38:03',
                                    'score' => 56
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0063A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0063B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 81,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 01:38:03',
                                    'score' => 68
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 06:38:03',
                                    'score' => 54
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-10 15:38:03',
                                    'score' => 62
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 83,
                    'attempts' => 3,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-12 10:38:03',
                            'score' => 66
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-17 09:38:03',
                            'score' => 69
                        ],
                        [
                            'attempt' => 3,
                            'date' => '2026-06-10 18:38:03',
                            'score' => 80
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 92,
                    'nilai' => 88,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-14 20:38:03',
                            'nilai' => 45
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-18 22:38:03',
                            'nilai' => 58
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM006A',
                        'title' => 'Diskusi Modul',
                        'replies' => 1,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS007',
                'title' => 'Cybersecurity Awareness untuk Karyawan',
                'description' => 'Modul pelatihan profesional tentang cybersecurity awareness untuk karyawan yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['IT', 'Automation', 'Leadership', 'Technical'],
                'modules_count' => 5,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-05 16:38:03',
                'completion_date' => '2026-06-19 16:38:03',
                'quiz_score' => 79,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0071',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 91,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 12:38:03',
                                    'score' => 51
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-14 05:38:03',
                                    'score' => 77
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0071A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0071B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 07:38:03',
                                    'score' => 68
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 08:38:03',
                                    'score' => 67
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0072',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 15:38:03',
                                    'score' => 100
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0072A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0072B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 23:38:03',
                                    'score' => 68
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0073',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 98,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 22:38:03',
                                    'score' => 70
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0073A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0073B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 76,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 17:38:03',
                                    'score' => 59
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0074',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 00:38:03',
                                    'score' => 55
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 15:38:03',
                                    'score' => 87
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0074A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0074B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 93,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-09 21:38:03',
                                    'score' => 59
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0075',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 17:38:03',
                                    'score' => 49
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 03:38:03',
                                    'score' => 52
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0075A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0075B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 95,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 11:38:03',
                                    'score' => 62
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 04:38:03',
                                    'score' => 62
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 79,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-18 11:38:03',
                            'score' => 63
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-16 21:38:03',
                            'score' => 88
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 86,
                    'nilai' => 88,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-19 14:38:03',
                            'nilai' => 56
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-17 19:38:03',
                            'nilai' => 73
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM007A',
                        'title' => 'Diskusi Modul',
                        'replies' => 6,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS008',
                'title' => 'Audit Lingkungan dan Pengolahan Limbah B3',
                'description' => 'Modul pelatihan profesional tentang audit lingkungan dan pengolahan limbah b3 yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Hard Skill', 'Management'],
                'modules_count' => 4,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-05-22 16:38:03',
                'completion_date' => '2026-06-03 16:38:03',
                'quiz_score' => 83,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0081',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 08:38:03',
                                    'score' => 64
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 18:38:03',
                                    'score' => 40
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-12 06:38:03',
                                    'score' => 90
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0081A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0081B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 94,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 00:38:03',
                                    'score' => 51
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-19 03:38:03',
                                    'score' => 46
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-11 04:38:03',
                                    'score' => 69
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0082',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 06:38:03',
                                    'score' => 63
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 10:38:03',
                                    'score' => 57
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-14 20:38:03',
                                    'score' => 65
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0082A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0082B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-09 22:38:03',
                                    'score' => 62
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-19 15:38:03',
                                    'score' => 59
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0083',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 90,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 06:38:03',
                                    'score' => 51
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 19:38:03',
                                    'score' => 47
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-10 00:38:03',
                                    'score' => 98
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0083A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0083B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 17:38:03',
                                    'score' => 56
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 09:38:03',
                                    'score' => 60
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0084',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 23:38:03',
                                    'score' => 45
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 12:38:03',
                                    'score' => 73
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-10 09:38:03',
                                    'score' => 89
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0084A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0084B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 93,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 00:38:03',
                                    'score' => 55
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 83,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-09 20:38:03',
                            'score' => 75
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 93,
                    'nilai' => 100,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-13 22:38:03',
                            'nilai' => 52
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-18 17:38:03',
                            'nilai' => 91
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM008A',
                        'title' => 'Diskusi Modul',
                        'replies' => 3,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS009',
                'title' => 'Pengenalan Lean Manufacturing',
                'description' => 'Modul pelatihan profesional tentang pengenalan lean manufacturing yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Communication', 'Automation', 'Engineering'],
                'modules_count' => 3,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-15 16:38:03',
                'completion_date' => '2026-06-03 16:38:03',
                'quiz_score' => 85,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0091',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 95,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 03:38:03',
                                    'score' => 63
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 06:38:03',
                                    'score' => 55
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0091A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0091B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 98,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 20:38:03',
                                    'score' => 80
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0092',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 74,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 08:38:03',
                                    'score' => 86
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0092A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0092B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 02:38:03',
                                    'score' => 46
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 21:38:03',
                                    'score' => 77
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0093',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 03:38:03',
                                    'score' => 52
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 01:38:03',
                                    'score' => 73
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0093A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0093B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 81,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 10:38:03',
                                    'score' => 62
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 08:38:03',
                                    'score' => 55
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 85,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-14 01:38:03',
                            'score' => 62
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 100,
                    'nilai' => 85,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-15 10:38:03',
                            'nilai' => 45
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-19 05:38:03',
                            'nilai' => 57
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM009A',
                        'title' => 'Diskusi Modul',
                        'replies' => 8,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS010',
                'title' => 'Total Productive Maintenance (TPM)',
                'description' => 'Modul pelatihan profesional tentang total productive maintenance (tpm) yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Soft Skill', 'Engineering', 'Automation', 'IT'],
                'modules_count' => 4,
                'status' => 'selesai',
                'progress' => 100,
                'last_accessed' => '2026-06-06 16:38:03',
                'completion_date' => '2026-06-06 16:38:03',
                'quiz_score' => 96,
                'attendance' => 100,
                'sessions' => [
                    [
                        'id' => 'SES0101',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 10:38:03',
                                    'score' => 45
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 10:38:03',
                                    'score' => 88
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0101A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0101B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 20:38:03',
                                    'score' => 84
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0102',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 75,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 16:38:03',
                                    'score' => 70
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 01:38:03',
                                    'score' => 52
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0102A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0102B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 19:38:03',
                                    'score' => 40
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 13:38:03',
                                    'score' => 55
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0103',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 86,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 14:38:03',
                                    'score' => 40
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 16:38:03',
                                    'score' => 61
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-12 02:38:03',
                                    'score' => 72
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0103A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0103B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 96,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 05:38:03',
                                    'score' => 69
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 15:38:03',
                                    'score' => 40
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-11 02:38:03',
                                    'score' => 89
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0104',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 3,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 15:38:03',
                                    'score' => 54
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 14:38:03',
                                    'score' => 54
                                ],
                                [
                                    'attempt' => 3,
                                    'date' => '2026-06-13 19:38:03',
                                    'score' => 79
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0104A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0104B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 83,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 18:38:03',
                                    'score' => 92
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'selesai',
                    'score' => 96,
                    'attempts' => 2,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-16 03:38:03',
                            'score' => 66
                        ],
                        [
                            'attempt' => 2,
                            'date' => '2026-06-17 21:38:03',
                            'score' => 60
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'selesai',
                    'score' => 87,
                    'nilai' => 76,
                    'attempts' => 1,
                    'history' => [
                        [
                            'attempt' => 1,
                            'date' => '2026-06-15 01:38:03',
                            'nilai' => 68
                        ]
                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM010A',
                        'title' => 'Diskusi Modul',
                        'replies' => 7,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS011',
                'title' => 'Manajemen Rantai Pasok (Supply Chain)',
                'description' => 'Modul pelatihan profesional tentang manajemen rantai pasok (supply chain) yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Soft Skill', 'HR', 'Compliance', 'Communication'],
                'modules_count' => 5,
                'status' => 'sedang_dipelajari',
                'progress' => 45,
                'last_accessed' => '2026-05-26 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 87,
                'sessions' => [
                    [
                        'id' => 'SES0111',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 92,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 02:38:03',
                                    'score' => 100
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0111A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0111B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 86,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 05:38:03',
                                    'score' => 93
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0112',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 96,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 04:38:03',
                                    'score' => 60
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 17:38:03',
                                    'score' => 56
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0112A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0112B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 80,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 05:38:03',
                                    'score' => 93
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0113',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 03:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 14:38:03',
                                    'score' => 63
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0113A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0113B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 91,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 18:38:03',
                                    'score' => 53
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0114',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 13:38:03',
                                    'score' => 60
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0114A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0114B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 23:38:03',
                                    'score' => 58
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0115',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 81,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 18:38:03',
                                    'score' => 71
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 02:38:03',
                                    'score' => 91
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0115A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0115B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 89,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 21:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 18:38:03',
                                    'score' => 74
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM011A',
                        'title' => 'Diskusi Modul',
                        'replies' => 8,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS012',
                'title' => 'Teknik Pemecahan Masalah (Problem Solving)',
                'description' => 'Modul pelatihan profesional tentang teknik pemecahan masalah (problem solving) yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Hard Skill', 'Communication'],
                'modules_count' => 5,
                'status' => 'sedang_dipelajari',
                'progress' => 20,
                'last_accessed' => '2026-06-19 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 90,
                'sessions' => [
                    [
                        'id' => 'SES0121',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 80,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 22:38:03',
                                    'score' => 74
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0121A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0121B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 88,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 12:38:03',
                                    'score' => 74
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 18:38:03',
                                    'score' => 91
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0122',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 91,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 04:38:03',
                                    'score' => 78
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0122A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0122B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 00:38:03',
                                    'score' => 85
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0123',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 83,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 20:38:03',
                                    'score' => 76
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0123A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0123B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 89,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 09:38:03',
                                    'score' => 64
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 20:38:03',
                                    'score' => 56
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0124',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 80,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 01:38:03',
                                    'score' => 52
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0124A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0124B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 15:38:03',
                                    'score' => 61
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 12:38:03',
                                    'score' => 78
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0125',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 92,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 15:38:03',
                                    'score' => 68
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 13:38:03',
                                    'score' => 91
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0125A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0125B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 97,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 09:38:03',
                                    'score' => 91
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM012A',
                        'title' => 'Diskusi Modul',
                        'replies' => 4,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS013',
                'title' => '5S di Lingkungan Kerja',
                'description' => 'Modul pelatihan profesional tentang 5s di lingkungan kerja yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Automation', 'Soft Skill', 'Hard Skill'],
                'modules_count' => 5,
                'status' => 'sedang_dipelajari',
                'progress' => 60,
                'last_accessed' => '2026-05-26 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 58,
                'sessions' => [
                    [
                        'id' => 'SES0131',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 08:38:03',
                                    'score' => 61
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 13:38:03',
                                    'score' => 69
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0131A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0131B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-09 20:38:03',
                                    'score' => 79
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0132',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 15:38:03',
                                    'score' => 51
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0132A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0132B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 05:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 21:38:03',
                                    'score' => 62
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0133',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 72,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 05:38:03',
                                    'score' => 79
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0133A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0133B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 95,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 01:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 02:38:03',
                                    'score' => 60
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0134',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 83,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 01:38:03',
                                    'score' => 67
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0134A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0134B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 18:38:03',
                                    'score' => 85
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0135',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 22:38:03',
                                    'score' => 47
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 10:38:03',
                                    'score' => 67
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0135A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0135B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 92,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 12:38:03',
                                    'score' => 57
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM013A',
                        'title' => 'Diskusi Modul',
                        'replies' => 7,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS014',
                'title' => 'Pengendalian Kualitas Terpadu (TQC)',
                'description' => 'Modul pelatihan profesional tentang pengendalian kualitas terpadu (tqc) yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Management', 'Safety', 'Leadership', 'HR'],
                'modules_count' => 5,
                'status' => 'sedang_dipelajari',
                'progress' => 80,
                'last_accessed' => '2026-05-30 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 89,
                'sessions' => [
                    [
                        'id' => 'SES0141',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 12:38:03',
                                    'score' => 40
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 02:38:03',
                                    'score' => 75
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0141A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0141B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 84,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 12:38:03',
                                    'score' => 94
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0142',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 94,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 23:38:03',
                                    'score' => 44
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 03:38:03',
                                    'score' => 63
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0142A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0142B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 21:38:03',
                                    'score' => 98
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0143',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 00:38:03',
                                    'score' => 61
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 11:38:03',
                                    'score' => 62
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0143A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0143B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 72,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 04:38:03',
                                    'score' => 71
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 06:38:03',
                                    'score' => 68
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0144',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 92,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 07:38:03',
                                    'score' => 50
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0144A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0144B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 76,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 01:38:03',
                                    'score' => 95
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0145',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 17:38:03',
                                    'score' => 72
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0145A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0145B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 76,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 02:38:03',
                                    'score' => 55
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 16:38:03',
                                    'score' => 97
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM014A',
                        'title' => 'Diskusi Modul',
                        'replies' => 5,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS015',
                'title' => 'Komunikasi Efektif Antar Departemen',
                'description' => 'Modul pelatihan profesional tentang komunikasi efektif antar departemen yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Leadership', 'Compliance'],
                'modules_count' => 4,
                'status' => 'sedang_dipelajari',
                'progress' => 45,
                'last_accessed' => '2026-06-13 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 70,
                'sessions' => [
                    [
                        'id' => 'SES0151',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 97,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 03:38:03',
                                    'score' => 73
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-12 20:38:03',
                                    'score' => 52
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0151A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0151B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 80,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 09:38:03',
                                    'score' => 72
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-14 04:38:03',
                                    'score' => 59
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0152',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 75,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 06:38:03',
                                    'score' => 54
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 03:38:03',
                                    'score' => 69
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0152A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0152B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 05:38:03',
                                    'score' => 56
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 22:38:03',
                                    'score' => 82
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0153',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 94,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 14:38:03',
                                    'score' => 73
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0153A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0153B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 92,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 09:38:03',
                                    'score' => 88
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0154',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 95,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 13:38:03',
                                    'score' => 71
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 08:38:03',
                                    'score' => 90
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0154A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0154B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-09 16:38:03',
                                    'score' => 70
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-19 06:38:03',
                                    'score' => 80
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM015A',
                        'title' => 'Diskusi Modul',
                        'replies' => 3,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS016',
                'title' => 'Analisis Data Menggunakan Power BI',
                'description' => 'Modul pelatihan profesional tentang analisis data menggunakan power bi yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Leadership', 'Safety'],
                'modules_count' => 4,
                'status' => 'sedang_dipelajari',
                'progress' => 20,
                'last_accessed' => '2026-06-04 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 59,
                'sessions' => [
                    [
                        'id' => 'SES0161',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 77,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 19:38:03',
                                    'score' => 62
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0161A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0161B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 80,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 19:38:03',
                                    'score' => 55
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 18:38:03',
                                    'score' => 69
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0162',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 87,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 12:38:03',
                                    'score' => 72
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0162A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0162B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 18:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-11 12:38:03',
                                    'score' => 82
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0163',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 76,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 23:38:03',
                                    'score' => 67
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 08:38:03',
                                    'score' => 78
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0163A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0163B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 74,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 04:38:03',
                                    'score' => 86
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0164',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 17:38:03',
                                    'score' => 65
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-17 10:38:03',
                                    'score' => 88
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0164A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0164B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-10 00:38:03',
                                    'score' => 54
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 22:38:03',
                                    'score' => 64
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM016A',
                        'title' => 'Diskusi Modul',
                        'replies' => 9,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS017',
                'title' => 'Pengenalan ISO 9001:2015',
                'description' => 'Modul pelatihan profesional tentang pengenalan iso 9001:2015 yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Leadership', 'HR', 'Safety'],
                'modules_count' => 5,
                'status' => 'sedang_dipelajari',
                'progress' => 20,
                'last_accessed' => '2026-05-23 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 81,
                'sessions' => [
                    [
                        'id' => 'SES0171',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 92,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 02:38:03',
                                    'score' => 42
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-10 08:38:03',
                                    'score' => 75
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0171A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0171B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 93,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 03:38:03',
                                    'score' => 64
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0172',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 88,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 03:38:03',
                                    'score' => 46
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 13:38:03',
                                    'score' => 100
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0172A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0172B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 84,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 06:38:03',
                                    'score' => 60
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 13:38:03',
                                    'score' => 66
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0173',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 12:38:03',
                                    'score' => 51
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0173A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0173B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 84,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 11:38:03',
                                    'score' => 67
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0174',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 15:38:03',
                                    'score' => 68
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0174A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0174B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 97,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 12:38:03',
                                    'score' => 65
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0175',
                        'title' => 'Sesi 5: Bagian Pembelajaran 5',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 15:38:03',
                                    'score' => 72
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0175A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0175B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 75,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-19 12:38:03',
                                    'score' => 90
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM017A',
                        'title' => 'Diskusi Modul',
                        'replies' => 6,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS018',
                'title' => 'Sistem Manajemen Mutu Terpadu',
                'description' => 'Modul pelatihan profesional tentang sistem manajemen mutu terpadu yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Automation', 'Hard Skill', 'Management'],
                'modules_count' => 4,
                'status' => 'sedang_dipelajari',
                'progress' => 45,
                'last_accessed' => '2026-05-28 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 60,
                'sessions' => [
                    [
                        'id' => 'SES0181',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 76,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 23:38:03',
                                    'score' => 69
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-09 18:38:03',
                                    'score' => 73
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0181A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0181B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 98,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 08:38:03',
                                    'score' => 45
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 03:38:03',
                                    'score' => 58
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0182',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 72,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 09:38:03',
                                    'score' => 56
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0182A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0182B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 94,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 02:38:03',
                                    'score' => 70
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-18 05:38:03',
                                    'score' => 60
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0183',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 93,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 07:38:03',
                                    'score' => 70
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0183A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0183B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 75,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 17:38:03',
                                    'score' => 69
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0184',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 75,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-17 08:38:03',
                                    'score' => 52
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 15:38:03',
                                    'score' => 92
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0184A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0184B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 23:38:03',
                                    'score' => 46
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 19:38:03',
                                    'score' => 76
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM018A',
                        'title' => 'Diskusi Modul',
                        'replies' => 10,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS019',
                'title' => 'Kecerdasan Buatan dalam Manufaktur',
                'description' => 'Modul pelatihan profesional tentang kecerdasan buatan dalam manufaktur yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Pilihan',
                'filters' => ['Engineering', 'Technical', 'Automation', 'IT'],
                'modules_count' => 3,
                'status' => 'sedang_dipelajari',
                'progress' => 20,
                'last_accessed' => '2026-06-07 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 76,
                'sessions' => [
                    [
                        'id' => 'SES0191',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 11:38:03',
                                    'score' => 91
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0191A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0191B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 100,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-15 05:38:03',
                                    'score' => 51
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-13 16:38:03',
                                    'score' => 91
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0192',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 76,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 19:38:03',
                                    'score' => 73
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0192A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0192B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 91,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-16 22:38:03',
                                    'score' => 73
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-14 09:38:03',
                                    'score' => 90
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0193',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 70,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 05:38:03',
                                    'score' => 54
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0193A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0193B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 95,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 08:38:03',
                                    'score' => 57
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM019A',
                        'title' => 'Diskusi Modul',
                        'replies' => 10,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS020',
                'title' => 'Manajemen Stres dan Kesejahteraan Karyawan',
                'description' => 'Modul pelatihan profesional tentang manajemen stres dan kesejahteraan karyawan yang dirancang khusus untuk meningkatkan kapabilitas teknis dan manajerial karyawan PT Denso Indonesia dalam mencapai target industri global.',
                'thumbnail' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Soft Skill', 'Automation'],
                'modules_count' => 4,
                'status' => 'sedang_dipelajari',
                'progress' => 80,
                'last_accessed' => '2026-05-21 16:38:03',
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 78,
                'sessions' => [
                    [
                        'id' => 'SES0201',
                        'title' => 'Sesi 1: Bagian Pembelajaran 1',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 71,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 03:38:03',
                                    'score' => 68
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 23:38:03',
                                    'score' => 60
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0201A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0201B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 21:38:03',
                                    'score' => 69
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0202',
                        'title' => 'Sesi 2: Bagian Pembelajaran 2',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 73,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-11 19:38:03',
                                    'score' => 71
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-14 00:38:03',
                                    'score' => 64
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0202A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0202B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 78,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-18 08:38:03',
                                    'score' => 68
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-15 14:38:03',
                                    'score' => 77
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0203',
                        'title' => 'Sesi 3: Bagian Pembelajaran 3',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 85,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 07:38:03',
                                    'score' => 77
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0203A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0203B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 79,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-13 02:38:03',
                                    'score' => 52
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'SES0204',
                        'title' => 'Sesi 4: Bagian Pembelajaran 4',
                        'status' => 'selesai',
                        'pre_test' => [
                            'status' => 'selesai',
                            'score' => 99,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 2,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-14 04:38:03',
                                    'score' => 73
                                ],
                                [
                                    'attempt' => 2,
                                    'date' => '2026-06-16 18:38:03',
                                    'score' => 75
                                ]
                            ]
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT0204A',
                                'title' => 'Video Pembelajaran',
                                'type' => 'video',
                                'duration' => '10 min',
                                'status' => 'selesai'
                            ],
                            [
                                'id' => 'MAT0204B',
                                'title' => 'Modul PDF',
                                'type' => 'pdf',
                                'duration' => '15 halaman',
                                'status' => 'selesai'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'selesai',
                            'score' => 82,
                            'questions' => [1, 2, 3, 4, 5],
                            'attempts' => 1,
                            'history' => [
                                [
                                    'attempt' => 1,
                                    'date' => '2026-06-12 05:38:03',
                                    'score' => 79
                                ]
                            ]
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'belum_dimulai',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [

                    ],
                    'questions' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                'forums' => [
                    [
                        'id' => 'FRM020A',
                        'title' => 'Diskusi Modul',
                        'replies' => 2,
                        'last_active' => '2 jam yang lalu'
                    ]
                ]
            ],
            [
                'id' => 'CRS999',
                'title' => 'Pengenalan AI dan Robotika',
                'description' => 'Course pengenalan dasar-dasar Artificial Intelligence dan Robotika untuk efisiensi produksi yang belum dimulai sama sekali.',
                'thumbnail' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?q=80&w=2070&auto=format&fit=crop',
                'category' => 'Wajib',
                'filters' => ['Technology', 'Hard Skill', 'New'],
                'modules_count' => 5,
                'status' => 'belum_dimulai',
                'progress' => 0,
                'last_accessed' => null,
                'completion_date' => null,
                'quiz_score' => null,
                'attendance' => 0,
                'sessions' => [
                    [
                        'id' => 'SES9991',
                        'title' => 'Sesi 1: Dasar Robotika',
                        'status' => 'terkunci',
                        'pre_test' => [
                            'status' => 'terkunci',
                            'score' => null,
                            'questions' => [1, 2, 3],
                            'attempts' => 0,
                            'history' => []
                        ],
                        'materials' => [
                            [
                                'id' => 'MAT9991A',
                                'title' => 'Video Pengantar',
                                'type' => 'video',
                                'duration' => '15 min',
                                'status' => 'terkunci'
                            ]
                        ],
                        'post_test' => [
                            'status' => 'terkunci',
                            'score' => null,
                            'questions' => [1, 2, 3],
                            'attempts' => 0,
                            'history' => []
                        ]
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0,
                    'history' => [],
                    'questions' => [1, 2, 3]
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'nilai' => null,
                    'attempts' => 0,
                    'history' => [],
                    'questions' => [1, 2, 3]
                ],
                'forums' => []
            ]
        ];
    }

    public static function getActiveCourses(): array
    {
        return array_values(array_filter(self::getCourses(), function ($c) {
            return $c['status'] === 'sedang_dipelajari';
        }));
    }

    public static function getCompletedCourses(): array
    {
        return array_values(array_filter(self::getCourses(), function ($c) {
            return $c['status'] === 'selesai';
        }));
    }

    public static function getCourseById(string $id): ?array
    {
        $courses = self::getCourses();
        foreach ($courses as $c) {
            if ($c['id'] === $id) return $c;
        }
        return null;
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

        return [
            'active_count' => count($active),
            'completed_count' => count($completed),
            'avg_score' => round($avgScore),
            'total_certificates' => count($completed)
        ];
    }

    public static function getNotifications(): array
    {
        return [
            [
                'id' => 'NOTIF1',
                'time_group' => 'hari_ini',
                'message' => '<span class="font-label-md text-label-md">Budi Santoso</span> telah menambahkan materi baru: <span class="font-label-md text-label-md">"Advanced Lean Manufacturing"</span> pada Sesi 1.',
                'time' => '5 menit yang lalu',
                'is_read' => false,
                'type' => 'materi'
            ],
            [
                'id' => 'NOTIF2',
                'time_group' => 'hari_ini',
                'message' => 'Course baru <span class="font-label-md text-label-md">"Dasar-Dasar Logistik"</span> telah tersedia untuk departemen Anda.',
                'time' => '30 menit yang lalu',
                'is_read' => false,
                'type' => 'course'
            ],
            [
                'id' => 'NOTIF3',
                'time_group' => 'hari_ini',
                'message' => '<span class="font-label-md text-label-md">Teacher Ahmad Hidayat</span> baru saja mempublikasikan kuis untuk Sesi 2.',
                'time' => '1 jam yang lalu',
                'is_read' => true,
                'type' => 'quiz'
            ],
            [
                'id' => 'NOTIF4',
                'time_group' => 'kemarin',
                'message' => 'Materi Video <span class="font-label-md text-label-md">"Safety Protocol 2024"</span> sudah dapat diakses.',
                'time' => '2 jam yang lalu',
                'is_read' => true,
                'type' => 'video'
            ]
        ];
    }
}
