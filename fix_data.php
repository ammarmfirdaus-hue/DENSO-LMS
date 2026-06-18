<?php
$file = 'C:\Users\Aspire 7\Documents\Project TEFA WebWeavers\DENSO-LMS\includes\DX_LMS_M_StudentData.php';
$content = file_get_contents($file);

// We need to replace the old faulty 'sessions' string with a correct one.
// The old one starts at "'sessions' => [" and ends at "]," before "'attendance'".
// Actually, it's easier to just re-run the script with preg_replace over the whole added block.
// Wait, the previous script replaced 'attendance' => [0-9]+, with the entire block + the attendance.
// Actually, the previous script replaced `('attendance'\s*=>\s*[0-9]+,)` with `$1` + `$sessionsMock`.
// That means the file now has `attendance => X, 'sessions' => ...` for each course.
// I can just replace the whole 'sessions' => ... 'forums' => [...] block with the correct one.

$pattern = "/'sessions'\s*=>\s*\[.*?],\s*'quiz'\s*=>\s*\[.*?],\s*'final_assessment'\s*=>\s*\[.*?],\s*'forums'\s*=>\s*\[.*?],/s";

$newSessionsMock = "
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
                ],";

$content = preg_replace($pattern, trim($newSessionsMock).",", $content);
file_put_contents($file, $content);
echo "Fixed!";
