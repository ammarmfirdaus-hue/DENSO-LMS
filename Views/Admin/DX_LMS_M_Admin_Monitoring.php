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
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Learning Monitoring</h1>
                <p class="font-body-md text-on-surface-variant">Pantau kepatuhan dan kelancaran program pelatihan secara real-time.</p>
            </div>
            
            <button class="bg-primary text-on-primary font-label-md px-4 py-2.5 rounded-lg active:scale-95 transition-transform hover:shadow-md flex items-center gap-2">
                <span class="material-symbols-outlined">download</span> Ekspor Rekap (Excel)
            </button>
        </div>

        <!-- Monitoring Overview Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-stack-xl">
            <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant flex gap-4 items-center">
                <div class="w-14 h-14 bg-secondary/10 text-secondary rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-[32px]">school</span>
                </div>
                <div>
                    <span class="font-headline-sm font-bold text-on-surface"><?= e(
                        $stats['course_active'],
                    ) ?> Course</span>
                    <p class="font-label-sm text-on-surface-variant">Sedang Berjalan</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant flex gap-4 items-center">
                <div class="w-14 h-14 bg-tertiary/10 text-tertiary rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-[32px]">groups</span>
                </div>
                <div>
                    <span class="font-headline-sm font-bold text-on-surface">254 Peserta</span>
                    <p class="font-label-sm text-on-surface-variant">Aktif Mengikuti</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant flex gap-4 items-center">
                <div class="w-14 h-14 bg-error/10 text-error rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-[32px]">warning</span>
                </div>
                <div>
                    <span class="font-headline-sm font-bold text-on-surface">9 Peserta</span>
                    <p class="font-label-sm text-on-surface-variant">Tertinggal (Lagging)</p>
                </div>
            </div>
        </div>

        <!-- Monitoring Details List -->
        <h2 class="font-headline-sm font-bold text-on-surface mb-stack-md">Maturitas Course</h2>
        <div class="space-y-stack-md">
            <?php foreach ($monitoring_data as $mon): ?>
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant flex flex-col md:flex-row gap-6 md:items-center hover:shadow-md transition-shadow">
                    
                    <div class="flex-1">
                        <div class="flex gap-2 items-center mb-1">
                            <?php if ($mon['status'] === 'Completed'): ?>
                                <span class="bg-success-container/40 text-success border border-success/30 px-2 py-0.5 rounded-sm font-label-sm text-[10px] tracking-wide uppercase">Tuntas Selesai</span>
                            <?php else: ?>
                                <span class="bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded-sm font-label-sm text-[10px] tracking-wide uppercase">Berjalan Aktif</span>
                            <?php endif; ?>
                            <span class="font-label-sm text-on-surface-variant">ID-<?= e($mon['course_id']) ?></span>
                        </div>
                        <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface line-clamp-1 truncate mb-2">
                            <a href="?route=/admin/course/detail&id=<?= e(
                                $mon['course_id'],
                            ) ?>" class="hover:text-primary hover:underline"><?= e($mon['course_title']) ?></a>
                        </h3>
                        <p class="font-body-sm text-on-surface-variant">Penanggung Jawab (Teacher): <span class="font-label-sm font-bold opacity-80"><?= e(
                            $mon['teacher'],
                        ) ?></span></p>
                    </div>

                    <!-- Progress Stats -->
                    <div class="flex-1 w-full bg-surface-variant rounded-lg p-3">
                        <div class="flex justify-between font-label-sm text-[11px] mb-2 uppercase tracking-wide">
                            <span class="text-on-surface-variant">Target Selesai</span>
                            <span class="font-bold text-<?= $mon['completion_rate'] == 100
                                ? 'success'
                                : 'primary' ?>"><?= e((string) $mon['completion_rate']) ?>%</span>
                        </div>
                        <div class="h-2 w-full bg-outline-variant/30 rounded-full overflow-hidden mb-3">
                            <div class="h-full <?= $mon['completion_rate'] == 100
                                ? 'bg-success'
                                : 'bg-primary' ?> rounded-full" style="width: <?= $mon['completion_rate'] ?>%"></div>
                        </div>
                        <div class="flex justify-between items-end gap-2 text-center divide-x divide-outline-variant/50">
                            <div class="flex-1">
                                <span class="block font-headline-sm text-on-surface"><?= $mon['enrolled'] ?></span>
                                <span class="block font-label-sm text-[10px] text-on-surface-variant">Enroll</span>
                            </div>
                            <div class="flex-1 pl-2">
                                <span class="block font-headline-sm text-on-surface text-secondary"><?= $mon[
                                    'completed'
                                ] ?></span>
                                <span class="block font-label-sm text-[10px] text-on-surface-variant">Tuntas</span>
                            </div>
                            <div class="flex-1 pl-2">
                                <span class="block font-headline-sm text-on-surface text-error"><?= $mon[
                                    'lagging'
                                ] ?></span>
                                <span class="block font-label-sm text-[10px] text-on-surface-variant">Tertinggal</span>
                            </div>
                        </div>
                    </div>

                    <div class="md:w-32 flex justify-end md:block">
                        <button class="bg-surface border border-outline-variant hover:bg-surface-variant text-primary font-label-md w-full py-2.5 rounded-lg transition-colors shadow-sm" onclick="window.location.href='?route=/admin/score'">Atur Nilai</button>
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
