<?php
/**
 * Tampilan Kehadiran & Nilai (Attendance & Score) DLS Admin.
 * Pusat pengelolaan operasional hasil belajar.
 */

$header_title = 'Data Kehadiran & Nilai';
$active_tab = 'score';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kehadiran & Nilai';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[72px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Data Kehadiran & Nilai Akhir</h1>
                <p class="font-body-md text-on-surface-variant">Laporan nilai pre-test, kuis, post-test beserta status kelulusan peserta.</p>
            </div>
            
            <button class="bg-secondary text-on-secondary font-label-md px-5 py-2.5 rounded-lg active:scale-95 transition-transform hover:shadow-md flex items-center gap-2">
                <span class="material-symbols-outlined">print</span> Cetak Laporan
            </button>
        </div>

        <!-- Filter Tools Area -->
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm border border-surface-variant mb-stack-md flex gap-2 flex-wrap md:flex-nowrap">
            <div class="relative w-full md:w-64 max-w-sm">
                <span class="material-symbols-outlined absolute top-2 left-3 text-on-surface-variant text-[20px]">menu_book</span>
                <select class="w-full bg-surface pl-10 pr-3 py-2 rounded-lg border border-outline-variant font-body-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                    <option value="all">-- Filter Berdasarkan Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="material-symbols-outlined absolute top-2 right-2 text-on-surface-variant text-[20px] pointer-events-none">arrow_drop_down</span>
            </div>
            
            <div class="hidden md:block border-l border-outline-variant mx-2"></div>
            
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute top-2 left-3 text-on-surface-variant text-[20px]">search</span>
                <input type="text" placeholder="Cari nama atau NPK siswa..." class="w-full bg-surface pl-10 pr-3 py-2 rounded-lg border border-outline-variant font-body-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
        </div>

        <!-- Score Data Table -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden mb-safe table-responsive">
            <table class="w-full text-left font-body-md min-w-[900px]">
                <thead class="bg-surface-container-high border-b border-surface-variant">
                    <tr>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface">Data Peserta & Course</th>
                        <th class="px-3 py-3 font-label-md font-bold text-on-surface text-center">Kehadiran</th>
                        <th class="px-3 py-3 font-label-md font-bold text-on-surface text-center">Pre-Test</th>
                        <th class="px-3 py-3 font-label-md font-bold text-on-surface text-center">Quiz</th>
                        <th class="px-3 py-3 font-label-md font-bold text-on-surface text-center">Post-Test</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface text-center">Nilai Akhir</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant">
                    <?php if (empty($score_data)): ?>
                        <tr><td colspan="7" class="text-center py-8 text-on-surface-variant">Tidak ada data penilaian untuk filter ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($score_data as $score): ?>
                            <tr class="hover:bg-surface transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-label-md font-bold text-on-surface flex items-center gap-2">
                                        <a href="?route=/admin/user/detail&id=1" class="hover:text-primary hover:underline"><?= e(
                                            $score['student_name'],
                                        ) ?></a> <span class="bg-surface-variant text-on-surface-variant px-1.5 py-0.5 rounded text-[10px]">NPK <?= e(
    $score['student_npk'],
) ?></span>
                                    </div>
                                    <div class="font-label-sm mt-1 truncate max-w-xs" title="<?= e(
                                        $score['course_title'],
                                    ) ?>">
                                        <a href="?route=/admin/course/detail&id=<?= $score[
                                            'course_id'
                                        ] ?>" class="text-on-surface-variant opacity-80 hover:text-primary hover:opacity-100 hover:underline"><?= e(
    $score['course_title'],
) ?></a>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 font-label-md font-bold text-primary">
                                        <?= e($score['attendance']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center font-label-md <?= $score['pre_test'] < 70
                                    ? 'text-error'
                                    : 'text-on-surface' ?>"><?= e((string) $score['pre_test']) ?></td>
                                <td class="px-3 py-3 text-center font-label-md <?= $score['quiz'] < 70
                                    ? 'text-error'
                                    : 'text-on-surface' ?>"><?= e((string) $score['quiz']) ?></td>
                                <td class="px-3 py-3 text-center font-label-md <?= $score['post_test'] < 70
                                    ? 'text-error'
                                    : 'text-on-surface' ?>"><?= e((string) $score['post_test']) ?></td>
                                <td class="px-4 py-3 text-center bg-surface-variant/30">
                                    <span class="font-headline-sm font-bold text-on-surface"><?= e(
                                        (string) $score['final_score'],
                                    ) ?></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($score['status'] === 'Lulus'): ?>
                                        <span class="bg-success-container text-on-success-container border border-success/30 px-3 py-1 text-[11px] font-bold rounded-full uppercase tracking-wider flex items-center gap-1 justify-center w-full">
                                            <span class="material-symbols-outlined text-[14px]">verified</span> Lulus
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-error-container text-on-error-container border border-error/50 px-3 py-1 text-[11px] font-bold rounded-full flex gap-1 items-center justify-center w-full uppercase tracking-wider">
                                            <span class="material-symbols-outlined text-[14px]">warning</span> Remedial
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
