<?php
/**
 * Tampilan Learning Monitoring Admins (DLS Admin Monitoring View).
 * Pusat pemantauan jalannya proses belajar.
 */

$header_title = 'Learning Monitoring';
$active_tab = 'monitoring';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Monitoring Pembelajaran';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
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
                <h1 class="text-2xl font-bold text-gray-900">Learning Monitoring</h1>
                <p class="text-sm text-gray-600 mt-1">Pantau kepatuhan dan kelancaran program pelatihan secara real-time.</p>
            </div>
            
            <button 
                type="button"
                onclick="window.location.href='#'" 
                class="bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow flex items-center gap-2 active:scale-95"
            >
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span>Ekspor Rekap (Excel)</span>
            </button>
        </div>

        <!-- Monitoring Overview Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- Course Sedang Berjalan -->
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md border border-gray-200/80 flex gap-5 items-center transition-all duration-200">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[32px]">school</span>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900"><?= e(
                        (string) ($stats['course_active'] ?? 0),
                    ) ?> Course</div>
                    <p class="text-sm font-medium text-gray-500 mt-0.5">Sedang Berjalan</p>
                </div>
            </div>

            <!-- Peserta Aktif Mengikuti -->
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md border border-gray-200/80 flex gap-5 items-center transition-all duration-200">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[32px]">groups</span>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">254 Peserta</div>
                    <p class="text-sm font-medium text-gray-500 mt-0.5">Aktif Mengikuti</p>
                </div>
            </div>

            <!-- Peserta Tertinggal (Lagging) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md border border-gray-200/80 flex gap-5 items-center transition-all duration-200">
                <div class="w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[32px]">warning</span>
                </div>
                <div>
                    <div class="text-2xl font-bold text-red-600">9 Peserta</div>
                    <p class="text-sm font-medium text-gray-500 mt-0.5">Tertinggal (Lagging)</p>
                </div>
            </div>
        </div>

        <!-- Monitoring Details List -->
        <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">Maturitas Course</h2>
        <div class="space-y-5">
            <?php foreach ($monitoring_data as $mon): ?>
                <?php $is_completed =
                    $mon['status'] === 'Completed' ||
                    $mon['completion_rate'] == 100; ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md border border-gray-200/80 flex flex-col md:flex-row gap-6 md:gap-8 md:items-center transition-all duration-200">
                    
                    <!-- Left: Course Information -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <?php if ($is_completed): ?>
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2.5 py-0.5 rounded-md text-[11px] font-bold tracking-wide uppercase inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Tuntas Selesai</span>
                                </span>
                            <?php else: ?>
                                <span class="bg-red-50 text-red-600 border border-red-200/80 px-2.5 py-0.5 rounded-md text-[11px] font-bold tracking-wide uppercase inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                    <span>Berjalan Aktif</span>
                                </span>
                            <?php endif; ?>
                            <span class="text-xs font-semibold text-gray-500 ml-1">ID-<?= e(
                                (string) $mon['course_id'],
                            ) ?></span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-1 truncate mb-2">
                            <a href="?route=/admin/course/detail&id=<?= e(
                                (string) $mon['course_id'],
                            ) ?>" class="hover:text-red-600 transition-colors">
                                <?= e($mon['course_title']) ?>
                            </a>
                        </h3>
                        
                        <div class="text-xs text-gray-600 flex items-center gap-1.5">
                            <span>Penanggung Jawab (Teacher):</span>
                            <span class="font-bold text-blue-700 bg-blue-50/80 px-2 py-0.5 rounded border border-blue-200/50">
                                <?= e($mon['teacher']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Middle: Progress Stats Box -->
                    <div class="flex-1 w-full bg-gray-50 rounded-xl p-4 border border-gray-200/70">
                        <div class="flex justify-between items-center text-xs font-semibold mb-2">
                            <span class="text-gray-500 uppercase tracking-wider text-[11px]">Target Selesai</span>
                            <span class="font-bold text-sm <?= $is_completed
                                ? 'text-emerald-700'
                                : 'text-red-600' ?>"><?= e(
    (string) $mon['completion_rate'],
) ?>%</span>
                        </div>
                        <div class="h-2.5 w-full bg-gray-200 rounded-full overflow-hidden mb-3">
                            <div class="h-full <?= $is_completed
                                ? 'bg-emerald-600'
                                : 'bg-red-600' ?> rounded-full transition-all duration-500" style="width: <?= $mon[
    'completion_rate'
] ?>%"></div>
                        </div>
                        <div class="flex justify-between items-end gap-2 text-center divide-x divide-gray-200 pt-1">
                            <div class="flex-1">
                                <span class="block text-base font-bold text-gray-900"><?= (int) $mon[
                                    'enrolled'
                                ] ?></span>
                                <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mt-0.5">Enroll</span>
                            </div>
                            <div class="flex-1 pl-2">
                                <span class="block text-base font-bold text-emerald-700"><?= (int) $mon[
                                    'completed'
                                ] ?></span>
                                <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mt-0.5">Tuntas</span>
                            </div>
                            <div class="flex-1 pl-2">
                                <span class="block text-base font-bold text-red-600"><?= (int) $mon[
                                    'lagging'
                                ] ?></span>
                                <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mt-0.5">Tertinggal</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Atur Nilai Action Button -->
                    <div class="md:w-36 flex justify-end md:block shrink-0">
                        <button 
                            type="button"
                            class="bg-white border border-gray-300 hover:border-red-500 hover:text-red-600 hover:bg-red-50/50 text-gray-700 font-semibold text-sm w-full py-2.5 rounded-xl transition-all duration-200 shadow-sm active:scale-95" 
                            onclick="window.location.href='?route=/admin/score'"
                        >
                            Atur Nilai
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
