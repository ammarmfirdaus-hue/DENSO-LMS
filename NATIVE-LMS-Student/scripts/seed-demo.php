<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (getenv('SEED_DEMO') !== 'true') {
    fwrite(STDERR, "Refusing to seed: run Start-All-DataDummy.ps1 or set SEED_DEMO=true for a demo-only environment.\n");
    exit(2);
}

require_once dirname(__DIR__) . '/includes/Database.php';

if (preg_match('/(^|[_-])(prod|production|live)([_-]|$)/i', DB_NAME)) {
    fwrite(STDERR, "Refusing to seed a production-like database. Use a separate staging database.\n");
    exit(2);
}

$demoPassword = getenv('STUDENT_PASSWORD') ?: '';
if (strlen($demoPassword) < 12) {
    fwrite(STDERR, "Set STUDENT_PASSWORD to a demo-only password with at least 12 characters before running this seeder.\n");
    exit(2);
}

$students = [
    ['DLS-DEMO-STU-001', 'Dina Demo Operator', 'dina.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-002', 'Bima Demo Quality', 'bima.demo@example.test', 'Quality Assurance', 'Staff'],
    ['DLS-DEMO-STU-003', 'Sari Demo Engineering', 'sari.demo@example.test', 'Engineering', 'Technician'],
    ['DLS-DEMO-STU-004', 'Rizky Demo Produksi', 'rizky.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-005', 'Nadia Demo Logistik', 'nadia.demo@example.test', 'Logistik', 'Staff'],
    ['DLS-DEMO-STU-006', 'Ilham Demo Teknisi', 'ilham.demo@example.test', 'Engineering', 'Technician'],
    ['DLS-DEMO-STU-007', 'Putri Demo QA', 'putri.demo@example.test', 'Quality Assurance', 'Staff'],
    ['DLS-DEMO-STU-008', 'Arif Demo Line Leader', 'arif.demo@example.test', 'Produksi', 'Line Leader'],
    ['DLS-DEMO-STU-009', 'Lina Demo Planning', 'lina.demo@example.test', 'Planning', 'Staff'],
    ['DLS-DEMO-STU-010', 'Yoga Demo Maintenance', 'yoga.demo@example.test', 'Maintenance', 'Technician'],
    ['DLS-DEMO-STU-011', 'Dewi Demo Safety', 'dewi.demo@example.test', 'Safety', 'Staff'],
    ['DLS-DEMO-STU-012', 'Rian Demo Operator', 'rian.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-013', 'Mira Demo Engineer', 'mira.demo@example.test', 'Engineering', 'Engineer'],
    ['DLS-DEMO-STU-014', 'Doni Demo Quality', 'doni.demo@example.test', 'Quality Assurance', 'Staff'],
    ['DLS-DEMO-STU-015', 'Ayu Demo Production', 'ayu.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-016', 'Eka Demo Trainer', 'eka.demo@example.test', 'Training', 'Trainer'],
    ['DLS-DEMO-STU-017', 'Tono Demo Warehouse', 'tono.demo@example.test', 'Logistik', 'Staff'],
    ['DLS-DEMO-STU-018', 'Wulan Demo Analyst', 'wulan.demo@example.test', 'Planning', 'Analyst'],
    ['DLS-DEMO-STU-019', 'Bayu Demo Technician', 'bayu.demo@example.test', 'Maintenance', 'Technician'],
    ['DLS-DEMO-STU-020', 'Intan Demo Staff', 'intan.demo@example.test', 'Produksi', 'Staff'],
    ['DLS-DEMO-STU-021', 'Rudi Demo Operator', 'rudi.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-022', 'Citra Demo QA', 'citra.demo@example.test', 'Quality Assurance', 'Staff'],
    ['DLS-DEMO-STU-023', 'Fikri Demo Engineer', 'fikri.demo@example.test', 'Engineering', 'Engineer'],
    ['DLS-DEMO-STU-024', 'Nia Demo Safety', 'nia.demo@example.test', 'Safety', 'Staff'],
    ['DLS-DEMO-STU-025', 'Andi Demo Production', 'andi.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-026', 'Salsa Demo Planner', 'salsa.demo@example.test', 'Planning', 'Staff'],
    ['DLS-DEMO-STU-027', 'Hendra Demo Maintenance', 'hendra.demo@example.test', 'Maintenance', 'Technician'],
    ['DLS-DEMO-STU-028', 'Meli Demo Quality', 'meli.demo@example.test', 'Quality Assurance', 'Staff'],
    ['DLS-DEMO-STU-029', 'Dika Demo Warehouse', 'dika.demo@example.test', 'Logistik', 'Staff'],
    ['DLS-DEMO-STU-030', 'Tari Demo Analyst', 'tari.demo@example.test', 'Planning', 'Analyst'],
];
for ($studentNumber = 31; $studentNumber <= 100; $studentNumber++) {
    $studentCode = sprintf('DLS-DEMO-STU-%03d', $studentNumber);
    $students[] = [$studentCode, sprintf('Peserta Demo %03d', $studentNumber),
        sprintf('student%03d.demo@example.test', $studentNumber),
        ['Produksi', 'Quality Assurance', 'Engineering', 'Logistik', 'Maintenance'][$studentNumber % 5],
        ['Operator', 'Staff', 'Technician', 'Analyst', 'Line Leader'][$studentNumber % 5]];
}

$courses = [
    ['DLS-DEMO-SAFE-001', 'Keselamatan Kerja Manufaktur', 'Materi demo K3, APD, identifikasi bahaya, dan tanggap darurat.', 'Wajib', 'active'],
    ['DLS-DEMO-QUAL-002', 'Dasar Quality Control dan 5S', 'Materi demo pemeriksaan kualitas, standar proses, dan 5S.', 'Wajib', 'active'],
    ['DLS-DEMO-LEAN-003', 'Lean Manufacturing dan Kaizen', 'Materi demo identifikasi pemborosan dan perbaikan berkelanjutan.', 'Pilihan', 'active'],
    ['DLS-DEMO-PLC-004', 'Pengenalan Otomasi Industri', 'Materi demo sensor, PLC, dan keselamatan otomasi.', 'Pilihan', 'active'],
    ['DLS-DEMO-ENVI-005', 'Pengelolaan Lingkungan Kerja', 'Data demo pemilahan limbah, efisiensi energi, dan praktik lingkungan.', 'Wajib', 'active'],
    ['DLS-DEMO-LEAD-006', 'Komunikasi dan Kepemimpinan Tim', 'Data demo komunikasi kerja, coaching, dan koordinasi tim.', 'Pilihan', 'active'],
    ['DLS-DEMO-MAINT-007', 'Perawatan Mesin Dasar', 'Data demo inspeksi mesin, autonomous maintenance, dan pencatatan gangguan.', 'Wajib', 'active'],
    ['DLS-DEMO-ERG-008', 'Ergonomi dan Kesehatan Kerja', 'Data demo postur kerja, ergonomi, dan pencegahan kelelahan.', 'Wajib', 'active'],
];

$demoSubjects = [
    'keselamatan kerja dan identifikasi risiko', 'quality control dan audit proses', 'lean manufacturing dan Kaizen',
    'otomasi industri dan sensor', 'lingkungan kerja dan pengelolaan limbah', 'komunikasi dan kepemimpinan tim',
    'perawatan mesin dan pencatatan gangguan', 'ergonomi dan kesehatan kerja', 'analisis data produksi dan pemecahan masalah',
    'standar kerja, dokumentasi, dan perbaikan berkelanjutan',
];
for ($courseNumber = 9; $courseNumber <= 100; $courseNumber++) {
    $subject = $demoSubjects[($courseNumber - 9) % count($demoSubjects)];
    $title = sprintf('Modul Operasional %03d', $courseNumber);
    $courses[] = [sprintf('DLS-DEMO-OPS-%03d', $courseNumber), $title,
        "Data demo untuk uji alur produksi: {$subject}.", $courseNumber % 3 === 0 ? 'Pilihan' : 'Wajib', 'active'];
}

$sessions = [
    'DLS-DEMO-SAFE-001' => [
        ['Pengenalan K3 dan APD', [
            ['Panduan APD area produksi', 'text', 'Kenali APD wajib, cara inspeksi, dan aturan penggunaan di area produksi.'],
            ['Video briefing keselamatan', 'video', 'https://example.test/demo/safety-briefing'],
        ]],
        ['Identifikasi bahaya dan LOTO', [
            ['Checklist LOTO dasar', 'pdf', 'Materi demo. Ganti dengan dokumen pelatihan yang telah disetujui.'],
            ['Latihan identifikasi bahaya', 'text', 'Catat bahaya, nilai risiko, dan tindakan pengendalian.'],
        ]],
    ],
    'DLS-DEMO-QUAL-002' => [
        ['Standar kualitas proses', [
            ['Pemeriksaan kualitas awal', 'text', 'Periksa spesifikasi, alat ukur, dan kondisi proses sebelum produksi.'],
            ['Referensi standar kualitas', 'link', 'https://example.test/demo/quality-standard'],
        ]],
        ['Praktik 5S', [
            ['Lembar audit 5S', 'pdf', 'Lembar latihan audit area kerja untuk data dummy.'],
            ['Contoh visual 5S', 'image', 'https://example.test/demo/5s-visual'],
        ]],
    ],
    'DLS-DEMO-LEAN-003' => [
        ['Mengenali pemborosan', [
            ['Delapan pemborosan proses', 'text', 'Pelajari contoh pemborosan dan amati aliran kerja.'],
            ['Studi kasus Kaizen', 'video', 'https://example.test/demo/kaizen-case'],
        ]],
        ['Siklus PDCA', [
            ['Template rencana PDCA', 'pdf', 'Template latihan PDCA untuk data dummy.'],
            ['Sumber belajar Kaizen', 'link', 'https://example.test/demo/kaizen-resources'],
        ]],
    ],
    'DLS-DEMO-PLC-004' => [
        ['Dasar Pengenalan Otomasi Industri', [
            ['Panduan Pengenalan Otomasi Industri', 'text', 'Materi simulasi tentang sensor, PLC, interlock keselamatan, dan otomasi dasar.'],
            ['Video pengantar Pengenalan Otomasi Industri', 'video', 'https://example.test/demo/dls-demo-plc-004/intro'],
        ]],
        ['Praktik Pengenalan Otomasi Industri', [
            ['Checklist Pengenalan Otomasi Industri', 'pdf', 'Lembar latihan simulasi sensor, PLC, interlock keselamatan, dan otomasi dasar.'],
            ['Studi kasus Pengenalan Otomasi Industri', 'text', 'Diskusikan penerapan otomasi dasar pada skenario latihan.'],
        ]],
    ],
    'DLS-DEMO-ENVI-005' => [
        ['Dasar Pengelolaan Lingkungan Kerja', [
            ['Panduan Pengelolaan Lingkungan Kerja', 'text', 'Materi simulasi pemilahan limbah, efisiensi energi, dan praktik lingkungan.'],
            ['Video pengantar Pengelolaan Lingkungan Kerja', 'video', 'https://example.test/demo/dls-demo-envi-005/intro'],
        ]],
        ['Praktik Pengelolaan Lingkungan Kerja', [
            ['Checklist Pengelolaan Lingkungan Kerja', 'pdf', 'Lembar latihan simulasi pemilahan limbah dan efisiensi energi.'],
            ['Studi kasus Pengelolaan Lingkungan Kerja', 'text', 'Diskusikan penerapan praktik lingkungan pada skenario latihan.'],
        ]],
    ],
    'DLS-DEMO-LEAD-006' => [
        ['Dasar Komunikasi dan Kepemimpinan Tim', [
            ['Panduan Komunikasi dan Kepemimpinan Tim', 'text', 'Materi simulasi komunikasi kerja, coaching, dan koordinasi tim.'],
            ['Video pengantar Komunikasi dan Kepemimpinan Tim', 'video', 'https://example.test/demo/dls-demo-lead-006/intro'],
        ]],
        ['Praktik Komunikasi dan Kepemimpinan Tim', [
            ['Checklist Komunikasi dan Kepemimpinan Tim', 'pdf', 'Lembar latihan komunikasi kerja dan coaching.'],
            ['Studi kasus Komunikasi dan Kepemimpinan Tim', 'text', 'Diskusikan koordinasi tim pada skenario latihan.'],
        ]],
    ],
    'DLS-DEMO-MAINT-007' => [
        ['Dasar Perawatan Mesin Dasar', [
            ['Panduan Perawatan Mesin Dasar', 'text', 'Materi simulasi inspeksi mesin dan autonomous maintenance.'],
            ['Video pengantar Perawatan Mesin Dasar', 'video', 'https://example.test/demo/dls-demo-maint-007/intro'],
        ]],
        ['Praktik Perawatan Mesin Dasar', [
            ['Checklist Perawatan Mesin Dasar', 'pdf', 'Lembar latihan inspeksi dan pencatatan gangguan.'],
            ['Studi kasus Perawatan Mesin Dasar', 'text', 'Diskusikan perawatan mesin pada skenario latihan.'],
        ]],
    ],
    'DLS-DEMO-ERG-008' => [
        ['Dasar Ergonomi dan Kesehatan Kerja', [
            ['Panduan Ergonomi dan Kesehatan Kerja', 'text', 'Materi simulasi postur kerja, ergonomi, dan pencegahan kelelahan.'],
            ['Video pengantar Ergonomi dan Kesehatan Kerja', 'video', 'https://example.test/demo/dls-demo-erg-008/intro'],
        ]],
        ['Praktik Ergonomi dan Kesehatan Kerja', [
            ['Checklist Ergonomi dan Kesehatan Kerja', 'pdf', 'Lembar latihan ergonomi dan pencegahan kelelahan.'],
            ['Studi kasus Ergonomi dan Kesehatan Kerja', 'text', 'Diskusikan penerapan ergonomi pada skenario latihan.'],
        ]],
    ],
];

for ($courseNumber = 9; $courseNumber <= 100; $courseNumber++) {
    $subject = $demoSubjects[($courseNumber - 9) % count($demoSubjects)];
    $title = sprintf('Modul Operasional %03d', $courseNumber);
    $sessions[sprintf('DLS-DEMO-OPS-%03d', $courseNumber)] = [
        ["Dasar {$title}", [
            ["Panduan {$title}", 'text', "Materi simulasi tentang {$subject}. Tidak berisi prosedur operasional resmi."],
            ["Video pengantar {$title}", 'video', sprintf('https://example.test/demo/dls-demo-ops-%03d/intro', $courseNumber)],
        ]],
        ["Praktik {$title}", [
            ["Checklist {$title}", 'pdf', "Lembar latihan simulasi {$subject}."],
            ["Studi kasus {$title}", 'text', "Diskusikan penerapan {$subject} pada skenario latihan."],
        ]],
    ];
}

$enrollments = [];
$progressValues = [0, 25, 50, 75, 100];
foreach ($students as $studentIndex => [$npk]) {
    for ($slot = 0; $slot < 6; $slot++) {
        $courseIndex = ($studentIndex * 13 + $slot * 17) % count($courses);
        $progress = $progressValues[($studentIndex * 7 + $courseIndex * 3 + $slot) % count($progressValues)];
        $status = $progress === 100 ? 'completed' : ((($studentIndex + $courseIndex) % 7) === 1 ? 'dropped' : 'active');
        $enrollments[] = [$npk, $courses[$courseIndex][0], $progress, $status];
    }
}
// Pertahankan tiga pasangan course awal yang dibuat seeder demo versi sebelumnya.
foreach ([
    ['DLS-DEMO-STU-001', 'DLS-DEMO-SAFE-001', 100, 'completed'],
    ['DLS-DEMO-STU-002', 'DLS-DEMO-LEAN-003', 25, 'active'],
    ['DLS-DEMO-STU-003', 'DLS-DEMO-QUAL-002', 100, 'completed'],
] as $legacyEnrollment) {
    $exists = false;
    foreach ($enrollments as $enrollment) {
        if ($enrollment[0] === $legacyEnrollment[0] && $enrollment[1] === $legacyEnrollment[1]) { $exists = true; break; }
    }
    if (!$exists) $enrollments[] = $legacyEnrollment;
}

$pdo = db();
$pdo->beginTransaction();

try {
    $studentIds = [];
    $studentExists = $pdo->prepare('SELECT 1 FROM student_accounts WHERE npk=:npk');
    $studentInsert = $pdo->prepare(
        'INSERT INTO student_accounts(npk,fullname,email,password_hash,department,position_name,status)
         VALUES(:npk,:fullname,:email,:password_hash,:department,:position_name,:status)'
    );
    $studentFind = $pdo->prepare('SELECT student_id FROM student_accounts WHERE npk=:npk');
    $passwordHash = password_hash($demoPassword, PASSWORD_DEFAULT);
    foreach ($students as [$npk, $fullname, $email, $department, $position]) {
        $studentExists->execute(['npk' => $npk]);
        if ($studentExists->fetchColumn() === false) {
            $studentInsert->execute([
                'npk' => $npk,
                'fullname' => $fullname,
                'email' => $email,
                'password_hash' => $passwordHash,
                'department' => $department,
                'position_name' => $position,
                'status' => str_ends_with($npk, '030') ? 'inactive' : 'active',
            ]);
        }
        $studentFind->execute(['npk' => $npk]);
        $studentIds[$npk] = (int) $studentFind->fetchColumn();
    }

    $teacherShadowInsert = $pdo->prepare(
        'INSERT INTO student_accounts(npk,fullname,email,password_hash,department,position_name,status,account_type)
         VALUES(:npk,:fullname,:email,:password_hash,:department,:position_name,\'active\',\'teacher_shadow\')'
    );
    $teacherShadows = [
        ['DLS-DEMO-TCH-001', 'Tara Demo Teacher', 'teacher1.demo@example.test'],
        ['DLS-DEMO-TCH-002', 'Raka Demo Instructor', 'teacher2.demo@example.test'],
        ['DLS-DEMO-TCH-003', 'Maya Demo Trainer', 'teacher3.demo@example.test'],
        ['DLS-DEMO-TCH-004', 'Budi Demo Coach', 'teacher4.demo@example.test'],
        ['DLS-DEMO-TCH-005', 'Nina Demo Mentor', 'teacher5.demo@example.test'],
        ['DLS-DEMO-TCH-006', 'Fajar Demo Tutor', 'teacher6.demo@example.test'],
    ];
    for ($teacherNumber = 7; $teacherNumber <= 100; $teacherNumber++) {
        $teacherShadows[] = [sprintf('DLS-DEMO-TCH-%03d', $teacherNumber),
            sprintf('Demo Instructor %03d', $teacherNumber), sprintf('teacher%d.demo@example.test', $teacherNumber)];
    }
    foreach ($teacherShadows as [$npk, $fullname, $email]) {
        $studentExists->execute(['npk' => $npk]);
        if ($studentExists->fetchColumn() === false) {
            $teacherShadowInsert->execute(['npk' => $npk, 'fullname' => $fullname, 'email' => $email,
                'password_hash' => $passwordHash, 'department' => 'Training', 'position_name' => 'Teacher Study Mode']);
        }
    }

    $courseInsert = $pdo->prepare(
        'INSERT INTO courses(course_code,title,description,category,status)
         VALUES(:code,:title,:description,:category,:status)
         ON DUPLICATE KEY UPDATE title=VALUES(title),description=VALUES(description),category=VALUES(category),status=VALUES(status)'
    );
    $courseFind = $pdo->prepare('SELECT course_id FROM courses WHERE course_code=:code');
    $courseIds = [];
    foreach ($courses as [$code, $title, $description, $category, $status]) {
        $courseInsert->execute(['code' => $code, 'title' => $title, 'description' => $description, 'category' => $category, 'status' => $status]);
        $courseFind->execute(['code' => $code]);
        $courseIds[$code] = (int) $courseFind->fetchColumn();
    }

    $sessionFind = $pdo->prepare('SELECT session_id FROM course_sessions WHERE course_id=:course_id AND title=:title LIMIT 1');
    $sessionInsert = $pdo->prepare('INSERT INTO course_sessions(course_id,title,sort_order,status) VALUES(:course_id,:title,:sort_order,\'active\')');
    $materialFind = $pdo->prepare('SELECT material_id FROM course_materials WHERE session_id=:session_id AND title=:title LIMIT 1');
    $materialInsert = $pdo->prepare('INSERT INTO course_materials(session_id,title,material_type,content,sort_order) VALUES(:session_id,:title,:type,:content,:sort_order)');
    $materialIdsByCourse = [];
    foreach ($sessions as $courseCode => $courseSessions) {
        foreach ($courseSessions as $sessionIndex => [$sessionTitle, $materials]) {
            $sessionFind->execute(['course_id' => $courseIds[$courseCode], 'title' => $sessionTitle]);
            $sessionId = $sessionFind->fetchColumn();
            if ($sessionId === false) {
                $sessionInsert->execute(['course_id' => $courseIds[$courseCode], 'title' => $sessionTitle, 'sort_order' => $sessionIndex + 1]);
                $sessionId = (int) $pdo->lastInsertId();
            }
            foreach ($materials as $materialIndex => [$title, $type, $content]) {
                $materialFind->execute(['session_id' => $sessionId, 'title' => $title]);
                $materialId = $materialFind->fetchColumn();
                if ($materialId === false) {
                    $materialInsert->execute(['session_id' => $sessionId, 'title' => $title, 'type' => $type, 'content' => $content, 'sort_order' => $materialIndex + 1]);
                    $materialId = (int) $pdo->lastInsertId();
                }
                $materialIdsByCourse[$courseCode][] = (int) $materialId;
            }
        }
    }

    // Include older demo content too, so 100% completions unlock the quiz after a rerun.
    $activeMaterialsForCourse = $pdo->prepare(
        'SELECT material.material_id FROM course_sessions session
         JOIN course_materials material ON material.session_id=session.session_id
         WHERE session.course_id=:course_id AND session.status=\'active\' AND material.is_active=1
         ORDER BY session.sort_order,session.session_id,material.sort_order,material.material_id'
    );
    foreach ($courseIds as $courseCode => $courseId) {
        $activeMaterialsForCourse->execute(['course_id' => $courseId]);
        $materialIdsByCourse[$courseCode] = array_map('intval', $activeMaterialsForCourse->fetchAll(PDO::FETCH_COLUMN));
    }

    $enrollmentStatement = $pdo->prepare(
        'INSERT INTO course_enrollments(student_id,course_id,progress,enrollment_status,completed_at)
         VALUES(:student_id,:course_id,:progress,:status,:completed_at)
         ON DUPLICATE KEY UPDATE progress=VALUES(progress),enrollment_status=VALUES(enrollment_status),completed_at=VALUES(completed_at)'
    );
    $progressStatement = $pdo->prepare(
        'INSERT INTO material_progress(student_id,material_id,progress_status,completed_at)
         VALUES(:student_id,:material_id,:status,:completed_at)
         ON DUPLICATE KEY UPDATE progress_status=VALUES(progress_status),completed_at=VALUES(completed_at)'
    );
    foreach ($enrollments as [$npk, $courseCode, $progress, $status]) {
        $studentId = $studentIds[$npk];
        $courseId = $courseIds[$courseCode];
        $completedAt = $status === 'completed' ? date('Y-m-d H:i:s', strtotime('-2 days')) : null;
        $enrollmentStatement->execute(['student_id' => $studentId, 'course_id' => $courseId, 'progress' => $progress, 'status' => $status, 'completed_at' => $completedAt]);

        foreach ($materialIdsByCourse[$courseCode] ?? [] as $index => $materialId) {
            $completedMaterialCount = (int) floor(($progress / 100) * count($materialIdsByCourse[$courseCode] ?? []));
            $materialStatus = $index < $completedMaterialCount
                ? 'completed'
                : ($progress > 0 ? 'in_progress' : 'not_started');
            $materialCompletedAt = $materialStatus === 'completed' ? $completedAt ?? date('Y-m-d H:i:s', strtotime('-1 day')) : null;
            $progressStatement->execute(['student_id' => $studentId, 'material_id' => $materialId, 'status' => $materialStatus, 'completed_at' => $materialCompletedAt]);
        }
    }

    $quizQuestionTexts = [
        'Manakah langkah awal yang tepat sebelum memulai pekerjaan sesuai materi?',
        'Apa tujuan utama pemeriksaan proses secara berkala?',
        'Tindakan mana yang paling sesuai ketika menemukan kondisi tidak normal?',
        'Informasi apa yang perlu dicatat setelah menyelesaikan pemeriksaan?',
        'Kapan prosedur eskalasi perlu digunakan dalam skenario latihan?',
    ];
    $quizOptions = [
        ['Ikuti prosedur dan periksa kondisi kerja', 'Lewati pemeriksaan', 'Ubah standar sendiri', 'Abaikan instruksi'],
        ['Menjaga proses tetap sesuai standar', 'Mempercepat tanpa pemeriksaan', 'Menghapus catatan', 'Menghindari komunikasi'],
        ['Hentikan langkah berisiko dan laporkan', 'Teruskan pekerjaan tanpa catatan', 'Sembunyikan temuan', 'Minta rekan mengabaikan'],
        ['Hasil, waktu, dan tindak lanjut', 'Hanya nama area', 'Data tidak perlu dicatat', 'Catat setelah beberapa bulan'],
        ['Saat kondisi di luar kewenangan atau batas aman', 'Setiap akhir bulan saja', 'Tidak pernah', 'Setelah pekerjaan dilupakan'],
    ];
    $quizFind = $pdo->prepare('SELECT quiz_id FROM course_quizzes WHERE course_id=:course_id LIMIT 1');
    $quizInsert = $pdo->prepare(
        'INSERT INTO course_quizzes(course_id,title,description,passing_score,status,created_by,updated_by)
         VALUES(:course_id,:title,:description,75,:status,:created_by,:updated_by)
         ON DUPLICATE KEY UPDATE title=VALUES(title),description=VALUES(description),passing_score=75,status=VALUES(status),updated_by=VALUES(updated_by)'
    );
    $questionFind = $pdo->prepare('SELECT question_id FROM course_quiz_questions WHERE quiz_id=:quiz_id AND sort_order=:sort_order LIMIT 1');
    $questionInsert = $pdo->prepare(
        'INSERT INTO course_quiz_questions(quiz_id,question_text,options,correct_option,points,sort_order)
         VALUES(:quiz_id,:question_text,:options,0,1,:sort_order)
         ON DUPLICATE KEY UPDATE question_text=VALUES(question_text),options=VALUES(options),correct_option=0,points=1'
    );
    $quizIds = [];
    $quizQuestionIds = [];
    $quizStatuses = [];
    foreach ($courses as $courseIndex => [$courseCode, $courseTitle]) {
        $courseId = $courseIds[$courseCode];
        $creatorNpk = sprintf('DLS-DEMO-TCH-%03d', ($courseIndex % 6) + 1);
        $quizStatus = $courseIndex === 6 ? 'inactive' : ($courseIndex === 7 ? 'draft' : 'published');
        $quizInsert->execute(['course_id' => $courseId, 'title' => 'Evaluasi: ' . $courseTitle,
            'description' => 'Quiz pilihan ganda demo dengan satu kali percobaan.', 'status' => $quizStatus,
            'created_by' => $creatorNpk, 'updated_by' => $creatorNpk]);
        $quizFind->execute(['course_id' => $courseId]);
        $quizId = (int) $quizFind->fetchColumn();
        $quizIds[$courseCode] = $quizId;
        $quizStatuses[$courseCode] = $quizStatus;
        $quizQuestionIds[$courseCode] = [];
        foreach ($quizQuestionTexts as $questionIndex => $questionText) {
            $order = $questionIndex + 1;
            $questionInsert->execute(['quiz_id' => $quizId, 'question_text' => $questionText,
                'options' => json_encode($quizOptions[$questionIndex], JSON_THROW_ON_ERROR), 'sort_order' => $order]);
            $questionFind->execute(['quiz_id' => $quizId, 'sort_order' => $order]);
            $quizQuestionIds[$courseCode][] = (int) $questionFind->fetchColumn();
        }
    }

    $attemptFind = $pdo->prepare('SELECT attempt_id FROM course_quiz_attempts WHERE quiz_id=:quiz_id AND student_id=:student_id LIMIT 1');
    $attemptInsert = $pdo->prepare(
        'INSERT INTO course_quiz_attempts(quiz_id,student_id,score,correct_count,total_questions,answers,submitted_at)
         VALUES(:quiz_id,:student_id,:score,:correct_count,:total_questions,:answers,:submitted_at)
         ON DUPLICATE KEY UPDATE score=VALUES(score),correct_count=VALUES(correct_count),total_questions=VALUES(total_questions),answers=VALUES(answers)'
    );
    $quizGradeUpdate = $pdo->prepare('UPDATE course_enrollments SET quiz_score=:score WHERE student_id=:student_id AND course_id=:course_id');
    $seededQuizAttempts = 0;
    foreach ($enrollments as $enrollmentIndex => [$npk, $courseCode, $progress, $status]) {
        if ($status !== 'completed' || $quizStatuses[$courseCode] !== 'published') continue;
        $questionIds = $quizQuestionIds[$courseCode];
        $studentIndex = (int) substr($npk, -3) - 1;
        $courseIndex = array_search($courseCode, array_column($courses, 0), true);
        $correctCount = 2 + (($studentIndex + $courseIndex) % 4);
        $answers = [];
        foreach ($questionIds as $questionIndex => $questionId) {
            $answers[(string) $questionId] = $questionIndex < $correctCount ? 0 : (($questionIndex + 1) % 4);
        }
        $score = round(($correctCount / count($questionIds)) * 100, 2);
        $studentId = $studentIds[$npk];
        $attemptInsert->execute(['quiz_id' => $quizIds[$courseCode], 'student_id' => $studentId, 'score' => $score,
            'correct_count' => $correctCount, 'total_questions' => count($questionIds),
            'answers' => json_encode($answers, JSON_THROW_ON_ERROR), 'submitted_at' => date('Y-m-d H:i:s', strtotime('-' . (($enrollmentIndex % 20) + 1) . ' days'))]);
        $quizGradeUpdate->execute(['score' => $score, 'student_id' => $studentId, 'course_id' => $courseIds[$courseCode]]);
        $seededQuizAttempts++;
    }

    $accessRequests = [
        ['DLS-DEMO-STU-001', 'DLS-DEMO-PLC-004', 'student_request', 'student', 'pending', null],
        ['DLS-DEMO-STU-002', 'DLS-DEMO-LEAN-003', 'teacher_recommendation', 'teacher', 'pending', 'DLS-DEMO-TCH-003'],
        ['DLS-DEMO-STU-003', 'DLS-DEMO-QUAL-002', 'student_request', 'student', 'rejected', null],
        ['DLS-DEMO-STU-004', 'DLS-DEMO-ERG-008', 'teacher_recommendation', 'teacher', 'approved', 'DLS-DEMO-TCH-002'],
        ['DLS-DEMO-STU-005', 'DLS-DEMO-LEAN-003', 'student_request', 'student', 'pending', null],
        ['DLS-DEMO-STU-006', 'DLS-DEMO-QUAL-002', 'teacher_recommendation', 'teacher', 'rejected', 'DLS-DEMO-TCH-002'],
        ['DLS-DEMO-STU-007', 'DLS-DEMO-QUAL-002', 'student_request', 'student', 'approved', null],
        ['DLS-DEMO-STU-008', 'DLS-DEMO-LEAN-003', 'student_request', 'student', 'pending', null],
        ['DLS-DEMO-STU-009', 'DLS-DEMO-ERG-008', 'teacher_recommendation', 'teacher', 'pending', 'DLS-DEMO-TCH-002'],
        ['DLS-DEMO-STU-010', 'DLS-DEMO-SAFE-001', 'student_request', 'student', 'rejected', null],
        ['DLS-DEMO-STU-011', 'DLS-DEMO-LEAN-003', 'student_request', 'student', 'pending', null],
        ['DLS-DEMO-STU-012', 'DLS-DEMO-LEAD-006', 'teacher_recommendation', 'teacher', 'approved', 'DLS-DEMO-TCH-006'],
    ];
for ($requestIndex = 12; $requestIndex < 100; $requestIndex++) {
    $studentNpk = sprintf('DLS-DEMO-STU-%03d', $requestIndex + 1);
    $courseIndex = ($requestIndex * 7) % count($courses);
    $requestStatus = ['pending', 'approved', 'rejected'][$requestIndex % 3];
    $isTeacherRecommendation = $requestIndex % 3 === 0;
    $accessRequests[] = [$studentNpk, $courses[$courseIndex][0],
        $isTeacherRecommendation ? 'teacher_recommendation' : 'student_request',
        $isTeacherRecommendation ? 'teacher' : 'student', $requestStatus,
        $isTeacherRecommendation ? sprintf('DLS-DEMO-TCH-%03d', ($requestIndex % 6) + 1) : null];
}
    $requestFind = $pdo->prepare('SELECT request_id FROM course_enrollment_requests WHERE student_id=:student_id AND course_id=:course_id AND status=:status AND request_note=:note LIMIT 1');
    $requestInsert = $pdo->prepare(
        'INSERT INTO course_enrollment_requests(student_id,course_id,request_type,requested_by_npk,requested_by_role,request_note,status,decision_by_npk,decision_note,decided_at)
         VALUES(:student_id,:course_id,:request_type,:requested_by,:requested_role,:note,:status,:decision_by,:decision_note,:decided_at)'
    );
    foreach ($accessRequests as $requestIndex => [$studentNpk, $courseCode, $requestType, $requestedRole, $requestStatus, $teacherNpk]) {
        $studentId = $studentIds[$studentNpk];
        $courseId = $courseIds[$courseCode];
        $note = 'Data demo: mohon akses untuk pengujian alur persetujuan.';
        $requestFind->execute(['student_id' => $studentId, 'course_id' => $courseId, 'status' => $requestStatus, 'note' => $note]);
        if ($requestFind->fetchColumn() !== false) continue;
        $decisionBy = $requestStatus === 'pending' ? null : 'DLS-DEMO-ADM-001';
        $decisionNote = $requestStatus === 'rejected' ? 'Data demo: lengkapi prasyarat course terlebih dahulu.' : ($requestStatus === 'approved' ? 'Data demo: akses disetujui.' : null);
        $requestInsert->execute(['student_id' => $studentId, 'course_id' => $courseId, 'request_type' => $requestType,
            'requested_by' => $teacherNpk ?? $studentNpk, 'requested_role' => $requestedRole, 'note' => $note,
            'status' => $requestStatus, 'decision_by' => $decisionBy, 'decision_note' => $decisionNote,
            'decided_at' => $requestStatus === 'pending' ? null : date('Y-m-d H:i:s', strtotime('-' . ($requestIndex + 1) . ' days'))]);
        if ($requestStatus === 'approved') {
            $pdo->prepare("INSERT IGNORE INTO course_enrollments(student_id,course_id,progress,enrollment_status)
                VALUES(:student_id,:course_id,0,'active')")
                ->execute(['student_id' => $studentId, 'course_id' => $courseId]);
        }
    }

    $activityRows = [
        ['DLS-DEMO-STU-001', 'course_completed', 'Data demo: menyelesaikan course keselamatan kerja.'],
        ['DLS-DEMO-STU-001', 'material_completed', 'Data demo: menyelesaikan materi pengenalan APD.'],
        ['DLS-DEMO-STU-002', 'course_started', 'Data demo: memulai course keselamatan kerja.'],
        ['DLS-DEMO-STU-003', 'course_completed', 'Data demo: menyelesaikan course quality control.'],
    ];
    $activityTypes = ['course_started', 'material_completed', 'quiz_completed', 'course_completed', 'course_access_requested'];
    for ($activityIndex = 0; $activityIndex < 100; $activityIndex++) {
        $activityStudent = $students[$activityIndex % count($students)][0];
        $activityType = $activityTypes[$activityIndex % count($activityTypes)];
        $activityRows[] = [$activityStudent, $activityType,
            'Data demo #' . str_pad((string) ($activityIndex + 1), 3, '0', STR_PAD_LEFT) . ': aktivitas simulasi untuk uji riwayat belajar.'];
    }
    $activityFind = $pdo->prepare('SELECT activity_id FROM activity_logs WHERE student_id=:student_id AND activity_type=:type AND description=:description LIMIT 1');
    $activityInsert = $pdo->prepare('INSERT INTO activity_logs(student_id,activity_type,description) VALUES(:student_id,:type,:description)');
    foreach ($activityRows as [$npk, $type, $description]) {
        $activityFind->execute(['student_id' => $studentIds[$npk], 'type' => $type, 'description' => $description]);
        if ($activityFind->fetchColumn() === false) {
            $activityInsert->execute(['student_id' => $studentIds[$npk], 'type' => $type, 'description' => $description]);
        }
    }

    $pdo->commit();
    $publishedQuizCount = count(array_filter($quizStatuses, static fn(string $status): bool => $status === 'published'));
    fwrite(STDOUT, sprintf("Demo student data seeded: %d student accounts, %d courses, %d enrollments, course materials/progress, %d published quizzes, %d quiz attempts, %d access requests, and activity history.\n",
        count($students), count($courses), count($enrollments), $publishedQuizCount, $seededQuizAttempts, count($accessRequests)));
    fwrite(STDOUT, "New demo student accounts use STUDENT_PASSWORD. Existing account passwords are unchanged.\n");
    fwrite(STDOUT, "login_rate_limits was left empty because it stores live security throttling state.\n");
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Demo seed failed: ' . $error->getMessage() . "\n");
    exit(1);
}
