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
<body class="dls-app-shell bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-4 md:px-8 pb-[88px] lg:ml-[240px]">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Data Kehadiran & Nilai Akhir</h1>
                <p class="text-sm text-gray-600 mt-1">Laporan nilai pre-test, kuis, post-test beserta status kelulusan peserta.</p>
            </div>
            
            <button 
                type="button"
                onclick="window.print()"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2 active:scale-95"
            >
                <span class="material-symbols-outlined text-[20px]">print</span> 
                <span>Cetak Laporan</span>
            </button>
        </div>

        <!-- Filter Tools Area -->
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-200/80 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-80">
                <span class="material-symbols-outlined absolute top-2.5 left-3 text-gray-400 text-[20px]">menu_book</span>
                <select class="w-full bg-gray-50/80 hover:bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl py-2.5 pl-10 pr-9 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 appearance-none cursor-pointer transition-colors">
                    <option value="all">-- Filter Berdasarkan Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e(
    $c['title'],
) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="material-symbols-outlined absolute top-2.5 right-3 text-gray-400 text-[20px] pointer-events-none">arrow_drop_down</span>
            </div>
            
            <div class="hidden md:block w-px h-8 bg-gray-200"></div>
            
            <div class="relative w-full md:flex-1">
                <span class="material-symbols-outlined absolute top-2.5 left-3 text-gray-400 text-[20px]">search</span>
                <input 
                    type="text" 
                    placeholder="Cari nama atau NPK siswa..." 
                    class="w-full bg-gray-50/80 hover:bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl py-2.5 pl-10 pr-4 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 transition-colors"
                >
            </div>
        </div>

        <!-- Score Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden mb-8 table-responsive">
            <table class="w-full text-left min-w-[950px]">
                <thead class="bg-gray-50/90 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Data Peserta & Course</th>
                        <th class="px-4 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Kehadiran</th>
                        <th class="px-4 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Pre-Test</th>
                        <th class="px-4 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Quiz</th>
                        <th class="px-4 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Post-Test</th>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Nilai Akhir</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($score_data)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-sm text-gray-500 font-medium">
                                Tidak ada data penilaian untuk filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($score_data as $score): ?>
                            <?php
                            $att_val = (int) str_replace(
                                '%',
                                '',
                                (string) $score['attendance'],
                            );
                            $att_class =
                                $att_val >= 80
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200/70'
                                    : 'bg-red-50 text-red-600 border-red-200/70';
                            ?>
                            <tr class="hover:bg-red-50/30 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="?route=/admin/user/detail&id=1" class="font-bold text-gray-900 text-base hover:text-red-600 no-underline transition-colors">
                                            <?= e($score['student_name']) ?>
                                        </a> 
                                        <span class="bg-gray-100 text-gray-700 font-semibold px-2 py-0.5 rounded-md text-xs border border-gray-200/70">
                                            NPK <?= e($score['student_npk']) ?>
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1 truncate max-w-sm" title="<?= e(
                                        $score['course_title'],
                                    ) ?>">
                                        <a href="?route=/admin/course/detail&id=<?= $score[
                                            'course_id'
                                        ] ?>" class="hover:text-red-600 no-underline transition-colors">
                                            <?= e($score['course_title']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg border font-bold text-sm <?= $att_class ?>">
                                        <?= e($score['attendance']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center text-sm font-semibold <?= $score[
                                    'pre_test'
                                ] < 70
                                    ? 'text-red-600 font-bold'
                                    : 'text-gray-700' ?>">
                                    <?= e((string) $score['pre_test']) ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm font-semibold <?= $score[
                                    'quiz'
                                ] < 70
                                    ? 'text-red-600 font-bold'
                                    : 'text-gray-700' ?>">
                                    <?= e((string) $score['quiz']) ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm font-semibold <?= $score[
                                    'post_test'
                                ] < 70
                                    ? 'text-red-600 font-bold'
                                    : 'text-gray-700' ?>">
                                    <?= e((string) $score['post_test']) ?>
                                </td>
                                <td class="px-5 py-4 text-center bg-gray-50/50">
                                    <span class="inline-flex items-center justify-center min-w-[44px] px-2.5 py-1 rounded-xl bg-white border border-gray-200/80 shadow-sm font-bold text-base text-gray-900">
                                        <?= e(
                                            (string) $score['final_score'],
                                        ) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($score['status'] === 'Lulus'): ?>
                                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-bold text-xs uppercase tracking-wider shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">verified</span> 
                                            <span>LULUS</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center gap-1 px-3 py-1 rounded-full bg-red-50 text-red-600 border border-red-200/80 font-bold text-xs uppercase tracking-wider shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">warning</span> 
                                            <span>REMEDIAL</span>
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
