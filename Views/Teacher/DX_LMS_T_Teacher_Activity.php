<?php
/**
 * Tampilan Aktivitas Pengajar (Teacher Activity).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Aktivitas Terbaru - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'aktivitas'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Log Aktivitas Mengajar';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[72px]">
        <div class="max-w-3xl mx-auto space-y-stack-xl">
            
            <section class="flex flex-col gap-1">
                <h2 class="font-headline-sm font-bold text-on-surface">Aktivitas Mengajar</h2>
                <p class="font-body-sm text-on-surface-variant">Log riwayat pekerjaan, usulan target peserta, dan pengelolaan course.</p>
            </section>

            <!-- Comprehensive Timeline from MockData -->
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-variant p-4 md:p-6 pb-2">
                <div class="relative before:content-[''] before:absolute before:inset-y-0 before:left-[19px] md:before:left-[39px] before:w-[2px] before:bg-outline-variant/40 before:z-0 ml-1 md:ml-0">
                    <?php // activities are passed from controller
                    foreach ($activities ?? [] as $act): ?>
                        <div class="relative pl-10 md:pl-20 py-4 group -ml-1 md:ml-0 z-10 border-b border-surface-variant/50 last:border-0 last:pb-0">
                            <!-- Timeline Dot / Icon -->
                            <div class="absolute w-10 h-10 md:w-14 md:h-14 rounded-full left-0 bg-surface border-[3px] border-surface flex items-center justify-center md:-mt-2 shadow-sm">
                                <div class="w-8 h-8 md:w-12 md:h-12 rounded-full <?= e(
                                    $act['color_class'] ?? 'bg-surface-container-high',
                                ) ?> border border-surface-variant flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[16px] md:text-[24px]" style="font-variation-settings: 'FILL' 1;"><?= e(
                                        $act['icon'] ?? 'history',
                                    ) ?></span>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="bg-transparent hover:bg-surface-container rounded-xl md:-mt-1 md:p-3 -m-2 p-2 transition-colors flex justify-between items-start">
                                <div>
                                    <p class="font-body-sm md:font-body-md text-on-surface mt-0.5 max-w-sm">
                                        <?php if (!empty($act['student_name'])): ?>
                                            <strong class="text-primary font-bold"><?= e($act['student_name']) ?></strong>
                                        <?php endif; ?>
                                        <span class="text-on-surface-variant font-medium"><?= e($act['action']) ?></span>
                                    </p>
                                    <p class="font-body-md text-on-surface font-medium hidden">...</p>
                                    <div class="bg-surface-container-low border border-surface-variant px-3 py-1.5 rounded-lg inline-block mt-2 font-label-sm text-[12px] text-on-surface">
                                        Topik: <b><?= e($act['target']) ?></b>
                                    </div>
                                    <p class="font-label-sm text-[10px] md:text-[11px] text-outline mt-3 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">schedule</span> <?= e(
                                            $act['time'],
                                        ) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($activities)): ?>
                        <div class="text-center py-10 px-4">
                            <span class="material-symbols-outlined text-[48px] text-surface-variant">history</span>
                            <p class="font-body-md text-on-surface-variant mt-2">Belum ada catatan aktivitas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
