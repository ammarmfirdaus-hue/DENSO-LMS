<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/Database.php';

$demoPassword = getenv('STUDENT_PASSWORD') ?: '';
if (strlen($demoPassword) < 12) {
    fwrite(STDERR, "Set STUDENT_PASSWORD to a demo-only password with at least 12 characters before running this seeder.\n");
    exit(2);
}

$students = [
    ['DLS-DEMO-STU-001', 'Dina Demo Operator', 'dina.demo@example.test', 'Produksi', 'Operator'],
    ['DLS-DEMO-STU-002', 'Bima Demo Quality', 'bima.demo@example.test', 'Quality Assurance', 'Staff'],
    ['DLS-DEMO-STU-003', 'Sari Demo Engineering', 'sari.demo@example.test', 'Engineering', 'Technician'],
];

$courses = [
    ['DLS-DEMO-SAFE-001', 'Keselamatan Kerja Manufaktur', 'Materi demo K3, APD, identifikasi bahaya, dan tanggap darurat.', 'Wajib', 'active'],
    ['DLS-DEMO-QUAL-002', 'Dasar Quality Control dan 5S', 'Materi demo pemeriksaan kualitas, standar proses, dan 5S.', 'Wajib', 'active'],
    ['DLS-DEMO-LEAN-003', 'Lean Manufacturing dan Kaizen', 'Materi demo identifikasi pemborosan dan perbaikan berkelanjutan.', 'Pilihan', 'active'],
    ['DLS-DEMO-PLC-004', 'Pengenalan Otomasi Industri', 'Materi demo sensor, PLC, dan keselamatan otomasi.', 'Pilihan', 'active'],
];

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
        ['Komponen otomasi dasar', [
            ['Sensor dan aktuator', 'text', 'Pengenalan fungsi sensor, aktuator, dan interlock keselamatan.'],
            ['Peta komponen demo', 'image', 'https://example.test/demo/automation-map'],
        ]],
        ['Logika PLC dasar', [
            ['Latihan ladder diagram', 'pdf', 'Contoh latihan logika PLC dasar tanpa koneksi ke mesin nyata.'],
            ['Video simulasi PLC', 'video', 'https://example.test/demo/plc-simulation'],
        ]],
    ],
];

$enrollments = [
    ['DLS-DEMO-STU-001', 'DLS-DEMO-SAFE-001', 100, 'completed'],
    ['DLS-DEMO-STU-001', 'DLS-DEMO-QUAL-002', 70, 'active'],
    ['DLS-DEMO-STU-001', 'DLS-DEMO-LEAN-003', 25, 'active'],
    ['DLS-DEMO-STU-002', 'DLS-DEMO-SAFE-001', 45, 'active'],
    ['DLS-DEMO-STU-002', 'DLS-DEMO-LEAN-003', 10, 'active'],
    ['DLS-DEMO-STU-002', 'DLS-DEMO-PLC-004', 0, 'active'],
    ['DLS-DEMO-STU-003', 'DLS-DEMO-QUAL-002', 100, 'completed'],
    ['DLS-DEMO-STU-003', 'DLS-DEMO-PLC-004', 60, 'active'],
];

$pdo = db();
$pdo->beginTransaction();

try {
    $studentIds = [];
    $studentExists = $pdo->prepare('SELECT 1 FROM student_accounts WHERE npk=:npk');
    $studentInsert = $pdo->prepare(
        'INSERT INTO student_accounts(npk,fullname,email,password_hash,department,position_name,status)
         VALUES(:npk,:fullname,:email,:password_hash,:department,:position_name,\'active\')'
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
            ]);
        }
        $studentFind->execute(['npk' => $npk]);
        $studentIds[$npk] = (int) $studentFind->fetchColumn();
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
            $materialStatus = $status === 'completed' || $progress >= (($index + 1) * 50)
                ? 'completed'
                : ($progress > 0 ? 'in_progress' : 'not_started');
            $materialCompletedAt = $materialStatus === 'completed' ? $completedAt ?? date('Y-m-d H:i:s', strtotime('-1 day')) : null;
            $progressStatement->execute(['student_id' => $studentId, 'material_id' => $materialId, 'status' => $materialStatus, 'completed_at' => $materialCompletedAt]);
        }
    }

    $activityRows = [
        ['DLS-DEMO-STU-001', 'course_completed', 'Data demo: menyelesaikan course keselamatan kerja.'],
        ['DLS-DEMO-STU-001', 'material_completed', 'Data demo: menyelesaikan materi pengenalan APD.'],
        ['DLS-DEMO-STU-002', 'course_started', 'Data demo: memulai course keselamatan kerja.'],
        ['DLS-DEMO-STU-003', 'course_completed', 'Data demo: menyelesaikan course quality control.'],
    ];
    $activityFind = $pdo->prepare('SELECT activity_id FROM activity_logs WHERE student_id=:student_id AND activity_type=:type AND description=:description LIMIT 1');
    $activityInsert = $pdo->prepare('INSERT INTO activity_logs(student_id,activity_type,description) VALUES(:student_id,:type,:description)');
    foreach ($activityRows as [$npk, $type, $description]) {
        $activityFind->execute(['student_id' => $studentIds[$npk], 'type' => $type, 'description' => $description]);
        if ($activityFind->fetchColumn() === false) {
            $activityInsert->execute(['student_id' => $studentIds[$npk], 'type' => $type, 'description' => $description]);
        }
    }

    $pdo->commit();
    fwrite(STDOUT, "Demo student data seeded: 3 accounts, 4 courses, sessions, materials, enrollments, material progress, and activity history.\n");
    fwrite(STDOUT, "New demo student accounts use STUDENT_PASSWORD. Existing account passwords are unchanged.\n");
    fwrite(STDOUT, "login_rate_limits was left empty because it stores live security throttling state.\n");
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Demo seed failed: ' . $error->getMessage() . "\n");
    exit(1);
}
