<?php
/**
 * Tampilan Pengelolaan Nilai & Capaian Belajar Peserta (Teacher Grade & Achievement).
 * Desain modern, terintegrasi dengan filter course dan metrik kelulusan peserta.
 */
$stats = $stats ?? [
    'total_students' => 0,
    'passed_count' => 0,
    'pending_count' => 0,
    'failed_count' => 0,
    'avg_score' => 0,
];
$filterStatus = $filterStatus ?? 'all';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Penilaian & Capaian Siswa - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="dls-app-shell bg-[#FAFAFA] text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'grade'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Penilaian & Capaian Siswa';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-4 sm:px-6 lg:px-8 pb-[88px] pt-[72px]">
        <div class="max-w-6xl mx-auto space-y-6">
            
            <!-- Course Selector & Banner -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-md bg-primary/10 text-primary font-mono text-[11px] font-bold">
                            <?= htmlspecialchars($activeCourse['code'] ?? 'K3-101') ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-md bg-surface-container text-on-surface-variant font-label-sm text-[11px] font-semibold">
                            <?= htmlspecialchars($activeCourse['category'] ?? 'HSE') ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200">
                            <?= htmlspecialchars($activeCourse['status'] ?? 'Running') ?>
                        </span>
                    </div>
                    <h1 class="font-headline-md font-bold text-on-surface tracking-tight">
                        Capaian Peserta: <?= htmlspecialchars($activeCourse['title'] ?? 'Fundamental 5S & K3') ?>
                    </h1>
                    <p class="font-body-sm text-on-surface-variant">
                        Pantau progres belajar, riwayat penyelesaian modul, dan lakukan validasi nilai akhir peserta pelatihan.
                    </p>
                </div>

                <!-- Course Switcher & Shortcut -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                    <!-- Dropdown Pilih Course -->
                    <div class="relative">
                        <select onchange="window.location.href='?route=/teacher/grade&course_id=' + this.value" class="appearance-none bg-surface border border-outline-variant/60 rounded-xl px-4 py-2.5 pr-10 font-label-md text-on-surface font-semibold text-xs focus:outline-none focus:border-primary transition-colors cursor-pointer w-full">
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>" <?= (string)$c['id'] === (string)$selectedCourseId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-on-surface-variant text-[18px]">expand_more</span>
                    </div>

                    <a href="?route=/teacher/course/detail&course_id=<?= urlencode((string)$selectedCourseId) ?>" class="bg-surface hover:bg-surface-container-high border border-outline-variant/60 text-on-surface font-label-md font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-xs shrink-0">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span> Detail Silabus
                    </a>

                    <button onclick="exportGradeExcel()" class="bg-primary hover:bg-primary-600 text-on-primary font-label-md font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-xs shrink-0 active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">download</span> Export Excel
                    </button>
                </div>
            </div>

            <!-- Bento Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">groups</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Total Peserta</span>
                        <span class="text-2xl font-bold text-on-surface"><?= (int)$stats['total_students'] ?></span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">military_tech</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Rata-rata Skor</span>
                        <span class="text-2xl font-bold text-on-surface"><?= (int)$stats['avg_score'] ?></span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">check_circle</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Telah Lulus</span>
                        <span class="text-2xl font-bold text-emerald-600"><?= (int)$stats['passed_count'] ?></span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">assignment_late</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Perlu Validasi</span>
                        <span class="text-2xl font-bold text-rose-600"><?= (int)$stats['pending_count'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Filter Status & Search -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar w-full sm:w-auto pb-1 sm:pb-0">
                    <?php
                    $filterTabs = [
                        'all' => 'Semua Peserta',
                        'lulus' => 'Lulus',
                        'menunggu validasi' => 'Menunggu Validasi',
                        'in progress' => 'Dalam Proses',
                        'tidak lulus' => 'Tidak Lulus',
                    ];
                    foreach ($filterTabs as $fKey => $fLabel):
                        $isActive = strtolower($filterStatus) === $fKey;
                    ?>
                        <a href="?route=/teacher/grade&course_id=<?= urlencode((string)$selectedCourseId) ?>&status=<?= urlencode($fKey) ?>" class="px-4 py-2 rounded-xl font-label-md font-bold text-xs whitespace-nowrap transition-all <?= $isActive ? 'bg-primary text-white shadow-sm' : 'bg-white text-on-surface-variant border border-outline-variant/50 hover:bg-surface-container' ?>">
                            <?= htmlspecialchars($fLabel) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="text-xs text-on-surface-variant font-medium">
                    Menampilkan <span class="font-bold text-on-surface"><?= count($students) ?></span> data peserta
                </div>
            </div>

            <!-- Grade List Table -->
            <div class="bg-white border border-outline-variant/50 rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap min-w-[760px]">
                        <thead class="bg-surface-container-low text-on-surface-variant font-label-sm text-[12px] border-b border-outline-variant/40">
                            <tr>
                                <th class="px-5 py-4 font-bold">Identitas Peserta</th>
                                <th class="px-4 py-4 font-bold">Progres Belajar</th>
                                <th class="px-4 py-4 font-bold text-center">Pre-Test</th>
                                <th class="px-4 py-4 font-bold text-center">Post-Test</th>
                                <th class="px-4 py-4 font-bold text-center">Final Exam</th>
                                <th class="px-4 py-4 font-bold">Status Kelulusan</th>
                                <th class="px-5 py-4 font-bold text-right">Aksi & Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30 font-body-sm text-xs sm:text-sm">
                            <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[40px] mb-2 opacity-40">group_off</span>
                                    <p class="font-bold font-title-sm text-on-surface">Tidak ada data peserta ditemukan.</p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">Silakan ubah filter status atau periksa course lain.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($students as $student): ?>
                            <?php
                            $prog = (int)($student['progress'] ?? 0);
                            $stat = $student['status'] ?? 'In Progress';
                            $statBadgeClass = match($stat) {
                                'Lulus' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'Menunggu Validasi' => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse',
                                'Tidak Lulus' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                            ?>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs shrink-0">
                                            <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-title-sm font-bold text-on-surface"><?= htmlspecialchars($student['name']) ?></p>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-on-surface-variant">
                                                <span>NPK: <?= htmlspecialchars($student['npk']) ?></span>
                                                <span>·</span>
                                                <span><?= htmlspecialchars($student['department'] ?? 'Manufacturing') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 w-44">
                                    <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                        <span class="<?= $prog === 100 ? 'text-emerald-600' : 'text-primary' ?>"><?= $prog ?>% Selesai</span>
                                        <span class="text-on-surface-variant font-normal text-[10px]"><?= htmlspecialchars($student['last_active'] ?? 'Aktif') ?></span>
                                    </div>
                                    <div class="w-full h-2 bg-surface-variant/60 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full <?= $prog === 100 ? 'bg-emerald-500' : 'bg-primary' ?>" style="width: <?= $prog ?>%;"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center font-mono font-bold text-on-surface-variant">
                                    <?= htmlspecialchars((string)($student['pre_test'] ?? 0)) ?>
                                </td>
                                <td class="px-4 py-4 text-center font-mono font-bold <?= ($student['post_test'] ?? 0) >= 75 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= htmlspecialchars((string)($student['post_test'] ?? 0)) ?>
                                </td>
                                <td class="px-4 py-4 text-center font-mono font-bold <?= ($student['final_assessment'] ?? 0) >= 75 ? 'text-emerald-600' : 'text-amber-600' ?>">
                                    <?= htmlspecialchars((string)($student['final_assessment'] ?? 0)) ?>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border <?= $statBadgeClass ?> inline-flex items-center gap-1">
                                        <?php if ($stat === 'Lulus'): ?>
                                            <span class="material-symbols-outlined text-[13px]">check</span>
                                        <?php elseif ($stat === 'Menunggu Validasi'): ?>
                                            <span class="material-symbols-outlined text-[13px]">pending</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($stat) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <?php if ($stat === 'Menunggu Validasi'): ?>
                                        <button onclick="validateStudentEssay('<?= htmlspecialchars(addslashes($student['name'])) ?>')" class="bg-primary hover:bg-primary-600 text-white font-label-md font-bold text-xs px-3.5 py-1.5 rounded-xl shadow-sm transition-all inline-flex items-center gap-1.5 active:scale-95">
                                            <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                            <span>Validasi Essay</span>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="viewStudentSummary('<?= htmlspecialchars(addslashes($student['name'])) ?>', '<?= htmlspecialchars($student['npk']) ?>', <?= (int)$student['post_test'] ?>, <?= (int)$student['final_assessment'] ?>, '<?= htmlspecialchars($stat) ?>')" class="text-primary hover:text-primary-700 bg-primary/5 hover:bg-primary/10 font-label-md font-bold text-xs px-3 py-1.5 rounded-xl transition-colors inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[15px]">visibility</span>
                                            <span>Rincian</span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>

    <script>
    function exportGradeExcel() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Export Rekapitulasi Nilai',
                text: 'Mengunduh berkas Excel untuk course: <?= htmlspecialchars(addslashes($activeCourse['title'] ?? 'Course')) ?>...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                confirmButtonColor: '#E20613'
            });
        } else {
            alert('Mengunduh berkas Excel rekap nilai...');
        }
    }

    function validateStudentEssay(studentName) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Validasi Jawaban Essay',
                text: 'Berikan persetujuan atau skor tambahan untuk ' + studentName + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Setujui & Luluskan',
                cancelButtonText: 'Tinjau Nanti',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Tervalidasi!',
                        text: 'Nilai essay ' + studentName + ' telah disetujui dan status kelulusan diperbarui.',
                        icon: 'success',
                        confirmButtonColor: '#E20613'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
    }

    function viewStudentSummary(name, npk, postTest, finalExam, status) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Rincian Capaian Peserta',
                html: `
                    <div class="text-left space-y-2 text-sm">
                        <p><strong>Nama:</strong> ${name}</p>
                        <p><strong>NPK:</strong> ${npk}</p>
                        <hr class="my-2 border-slate-200">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2 bg-slate-50 rounded"><strong>Post-Test:</strong> ${postTest}</div>
                            <div class="p-2 bg-slate-50 rounded"><strong>Final Exam:</strong> ${finalExam}</div>
                        </div>
                        <p class="mt-2 text-xs"><strong>Status Akhir:</strong> <span class="font-bold text-primary">${status}</span></p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#E20613'
            });
        }
    }
    </script>
</body>
</html>
