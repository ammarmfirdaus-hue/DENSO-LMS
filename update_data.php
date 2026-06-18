<?php
$file = 'C:\Users\Aspire 7\Documents\Project TEFA WebWeavers\DENSO-LMS\includes\DX_LMS_M_StudentData.php';
$content = file_get_contents($file);

$sessionsMock = "
                'sessions' => [
                    [
                        'id' => 'SES001',
                        'title' => 'Sesi 1: Pengenalan',
                        'status' => 'selesai',
                        'pre_test' => ['score' => 80],
                        'materials' => [
                            ['id' => 'MAT01', 'title' => 'Video Pengantar', 'type' => 'video', 'duration' => '5 min', 'status' => 'selesai'],
                            ['id' => 'MAT02', 'title' => 'Buku Panduan', 'type' => 'pdf', 'duration' => '10 halaman', 'status' => 'selesai']
                        ],
                        'post_test' => ['score' => 90]
                    ],
                    [
                        'id' => 'SES002',
                        'title' => 'Sesi 2: Implementasi Dasar',
                        'status' => 'sedang_dipelajari',
                        'pre_test' => ['score' => 100],
                        'materials' => [
                            ['id' => 'MAT03', 'title' => 'Studi Kasus 1', 'type' => 'text', 'duration' => '10 min', 'status' => 'selesai'],
                            ['id' => 'MAT04', 'title' => 'Materi Praktik', 'type' => 'video', 'duration' => '15 min', 'status' => 'terkunci']
                        ],
                        'post_test' => null
                    ],
                    [
                        'id' => 'SES003',
                        'title' => 'Sesi 3: Evaluasi',
                        'status' => 'terkunci',
                        'pre_test' => null,
                        'materials' => [
                            ['id' => 'MAT05', 'title' => 'Ringkasan', 'type' => 'text', 'duration' => '5 min', 'status' => 'terkunci']
                        ],
                        'post_test' => null
                    ]
                ],
                'quiz' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0
                ],
                'final_assessment' => [
                    'status' => 'terkunci',
                    'score' => null,
                    'attempts' => 0
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
                ],";

$content = preg_replace("/('attendance'\s*=>\s*[0-9]+,)/", "$1".$sessionsMock, $content);

file_put_contents($file, $content);
echo "Done!";
