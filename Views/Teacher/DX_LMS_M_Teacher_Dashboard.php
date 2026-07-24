<?php
/**
 * Tampilan Dashboard Pengajar (Teacher Dashboard).
 *
 * Mengacu pada detail implementasi workspace pengajaran yang profesional.
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Dashboard Teacher - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(884px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'beranda'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'Teacher Workspace';
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-4 lg:pt-[72px]">
        <!-- Desktop Quick Header (Only visible on lg) -->
        <div class="hidden lg:flex justify-between items-center mb-stack-lg border-b border-outline-variant/30 pb-4">
            <h1 class="font-headline-6 font-bold text-on-surface">Dashboard Harian</h1>
            <a href="?route=/teacher/notifikasi" class="relative p-2 text-on-surface-variant hover:bg-surface-container hover:text-primary rounded-full transition-colors group">
                <span class="material-symbols-outlined text-[24px]">notifications</span>
                <?php if (!empty($pending_actions)): ?>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
                <?php endif; ?>
            </a>
        </div>

        <section class="max-w-5xl mx-auto space-y-stack-xl">
            <!-- Header Welcome Section -->
            <div class="flex flex-col gap-1">
                <h2 class="font-headline-md font-bold text-on-surface">Selamat datang, <?= e(
                    $_SESSION['user']['fullname'] ?? 'Budi Santoso',
                ) ?></h2>
                <p class="font-body-md text-on-surface-variant">Pantau aktivitas pengajaran, usulan rekomendasi, dan status course Anda.</p>
            </div>

            <!-- Bento Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
                <!-- Stat 1: Course Diampu -->
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant flex flex-col gap-3 group hover:border-primary/50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-primary-container/30 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[24px]">school</span>
                    </div>
                    <div>
                        <div class="font-headline-md font-bold text-on-surface"><?= e(
                            (string) ($stats['total_course'] ?? 0),
                        ) ?></div>
                        <div class="font-label-sm text-on-surface-variant">Course Diampu</div>
                    </div>
                </div>
                <!-- Stat 2: Peserta Aktif -->
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant flex flex-col gap-3 group hover:border-secondary/50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[24px]">groups</span>
                    </div>
                    <div>
                        <div class="font-headline-md font-bold text-on-surface"><?= e(
                            (string) ($stats['active_students'] ?? 0),
                        ) ?></div>
                        <div class="font-label-sm text-on-surface-variant">Peserta Aktif</div>
                    </div>
                </div>
                <!-- Stat 3: Pending Review (Task) -->
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant flex flex-col gap-3 group hover:border-tertiary/50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-tertiary-container/30 flex items-center justify-center text-tertiary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[24px]">rule</span>
                    </div>
                    <div>
                        <div class="font-headline-md font-bold text-on-surface"><?= e(
                            (string) ($stats['pending_review'] ?? 0),
                        ) ?></div>
                        <div class="font-label-sm text-on-surface-variant">Pending Review</div>
                    </div>
                </div>
                <!-- Stat 4: Avg Score -->
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant flex flex-col gap-3 group hover:border-info/50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-info-container/30 flex items-center justify-center text-info group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[24px]">leaderboard</span>
                    </div>
                    <div>
                        <div class="font-headline-md font-bold text-on-surface"><?= e(
                            (string) ($stats['avg_score'] ?? 0),
                        ) ?></div>
                        <div class="font-label-sm text-on-surface-variant">Rata-rata Skor</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Main Area: Split layout on Desktop -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-stack-xl lg:gap-gutter items-start">
                
                <!-- Left Column: Courses -->
                <div class="lg:col-span-2 space-y-stack-md">
                    <div class="flex justify-between items-center bg-surface sticky top-0 z-10 py-1">
                        <h3 class="font-headline-sm font-bold text-on-surface">Course yang Diampu</h3>
                        <a href="?route=/teacher/courses" class="text-secondary font-label-md hover:underline flex items-center gap-1 group">
                            Lihat Semua <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-stack-md">
                        <?php foreach ($courses as $course): ?>
                            <?php
                            // Lifecycle styling logic
                            $badgeColor = 'bg-surface-container-high text-on-surface-variant';
                            $borderStyle = 'border-surface-variant';
                            $gradientFrom = 'from-surface-container-high/40';

                            switch ($course['status'] ?? 'Draft') {
                                case 'Submitted':
                                    $badgeColor = 'bg-info-container/80 text-on-info-container';
                                    $borderStyle = 'border-info/30 hover:border-info';
                                    $gradientFrom = 'from-info-container/20';
                                    break;
                                case 'Running':
                                    $badgeColor = 'bg-primary-container/80 text-on-primary-container';
                                    $borderStyle = 'border-primary/30 hover:border-primary';
                                    $gradientFrom = 'from-primary/10';
                                    break;
                                case 'Draft':
                                    $badgeColor = 'bg-surface-container-high text-on-surface-variant';
                                    $borderStyle = 'border-outline-variant hover:border-outline';
                                    $gradientFrom = 'from-surface-container-highest/30';
                                    break;
                                case 'Approved':
                                    $badgeColor = 'bg-success-container/80 text-on-success-container';
                                    $borderStyle = 'border-success/30 hover:border-success';
                                    $gradientFrom = 'from-success-container/20';
                                    break;
                                case 'Completed':
                                case 'Archived':
                                    $badgeColor =
                                        'bg-surface-container text-on-surface-variant line-through opacity-70';
                                    $borderStyle = 'border-surface-container-high';
                                    break;
                            }
                            ?>
                            <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border <?= $borderStyle ?> overflow-hidden flex flex-col group transition-all">
                                <!-- Gradient Header area -->
                                <div class="h-24 bg-gradient-to-br <?= $gradientFrom ?> to-transparent relative p-4 flex justify-between items-start">
                                    <div class="w-12 h-12 bg-surface rounded-xl shadow-sm border border-outline-variant/30 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[28px] text-on-surface"><?= e(
                                            $course['icon'] ?? 'menu_book',
                                        ) ?></span>
                                    </div>
                                    <div class="<?= $badgeColor ?> px-2 py-0.5 rounded-full font-label-sm text-[11px] font-medium border border-outline/10 shadow-sm">
                                        <?= e($course['status'] ?? 'Draft') ?>
                                    </div>
                                </div>
                                
                                <div class="p-4 flex-1 flex flex-col">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-label-sm text-xs bg-secondary-container/30 text-secondary px-1.5 py-0.5 rounded-md">
                                            <?= e($course['code']) ?>
                                        </span>
                                    </div>
                                    <h4 class="font-title-md font-semibold text-on-surface mb-2 line-clamp-1 group-hover:text-primary transition-colors"><?= e(
                                        $course['title'],
                                    ) ?></h4>
                                    
                                    <div class="flex items-center justify-between text-on-surface-variant mt-auto text-label-sm pt-4 border-t border-outline-variant/30">
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">groups</span>
                                            <span><?= e((string) ($course['participants'] ?? '0')) ?></span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">update</span>
                                            <span><?= e($course['updated_at'] ?? 'Baru saja') ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Action section based on status -->
                                    <div class="mt-4 pt-3 border-t border-outline-variant/20 flex gap-2">
                                        <a href="?route=/teacher/course/detail&course_id=<?= e(
                                            (string) $course['id'],
                                        ) ?>" class="flex-1 text-center py-2 bg-surface-container border border-outline-variant text-on-surface font-label-md rounded-lg hover:bg-surface-container-high transition-colors text-[13px] font-semibold">
                                            Buka Detail
                                        </a>
                                        <?php if (($course['status'] ?? 'Draft') === 'Draft'): ?>
                                            <a href="?route=/teacher/course/create&course_id=<?= e(
                                                (string) $course['id'],
                                            ) ?>" class="flex-1 text-center py-2 bg-primary text-on-primary font-label-md rounded-lg shadow-sm hover:shadow-md hover:bg-primary-600 transition-all text-[13px] font-semibold">
                                                Edit Data
                                            </a>
                                        <?php elseif (($course['status'] ?? '') === 'Running'): ?>
                                            <a href="?route=/teacher/grade&course_id=<?= e(
                                                (string) $course['id'],
                                            ) ?>" class="flex-1 text-center py-2 bg-secondary text-on-secondary font-label-md rounded-lg shadow-sm hover:shadow-md hover:bg-secondary-600 transition-all text-[13px] font-semibold">
                                                Kelola Nilai
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Ghost Create Placeholder if < 4 courses -->
                        <?php if (count($courses) < 4): ?>
                            <a href="?route=/teacher/course/create" class="bg-surface-container-lowest/50 rounded-xl border-2 border-dashed border-outline-variant p-6 flex flex-col items-center justify-center gap-2 hover:bg-surface-container-low hover:border-primary/50 transition-colors min-h-[220px]">
                                <div class="w-12 h-12 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center mb-1">
                                    <span class="material-symbols-outlined text-[24px]">add</span>
                                </div>
                                <span class="font-label-md font-bold text-on-surface text-center">Buat Course Baru</span>
                                <span class="font-body-sm text-on-surface-variant text-center max-w-[200px]">Mulai susun materi dan usulkan target peserta.</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Timeline & Actions -->
                <div class="space-y-stack-xl">
                    
                    <!-- Pending Actions / Tindak Lanjut -->
                    <?php if (!empty($pending_actions)): ?>
                    <div class="space-y-stack-sm">
                        <h3 class="font-headline-sm font-bold text-on-surface">Perlu Tindak Lanjut</h3>
                        <div class="bg-warning-container/20 rounded-xl border border-warning/30 p-4 space-y-3">
                            <?php foreach ($pending_actions as $act): ?>
                                <a href="<?= e(
                                    $act['link'] ?? '#',
                                ) ?>" class="flex items-start gap-3 group active:scale-[0.98] transition-transform">
                                    <div class="w-8 h-8 shrink-0 bg-warning/20 text-warning rounded-full flex items-center justify-center mt-0.5">
                                        <span class="material-symbols-outlined text-[16px]"><?= e(
                                            $act['icon'] ?? 'warning',
                                        ) ?></span>
                                    </div>
                                    <div>
                                        <p class="font-label-md font-bold text-on-surface group-hover:text-primary transition-colors"><?= e(
                                            $act['title'],
                                        ) ?></p>
                                        <p class="font-body-sm text-on-surface-variant leading-snug line-clamp-2 mt-0.5"><?= e(
                                            $act['message'],
                                        ) ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Activity -->
                    <div class="space-y-stack-sm">
                        <div class="flex justify-between items-center">
                            <h3 class="font-headline-sm font-bold text-on-surface">Riwayat Aktivitas</h3>
                            <a href="?route=/teacher/aktivitas" class="text-secondary font-label-sm hover:underline">Semua</a>
                        </div>
                        
                        <div class="bg-surface-container-lowest rounded-xl border border-surface-variant p-4">
                            <div class="relative before:content-[''] before:absolute before:inset-y-0 before:left-[19px] before:w-[2px] before:bg-outline-variant/40 before:z-0 ml-1">
                                <?php foreach ($activities as $act): ?>
                                    <div class="relative pl-10 py-3 group -ml-1 z-10 border-b border-surface-variant/50 last:border-0 last:pb-0">
                                        <div class="absolute w-10 h-10 rounded-full left-0 bg-surface border-2 border-outline-variant flex items-center justify-center top-2 shadow-sm">
                                            <div class="w-8 h-8 rounded-full <?= e(
                                                $act['color_class'] ?? 'bg-surface-container-high',
                                            ) ?> flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;"><?= e(
                                                    $act['icon'] ?? 'history',
                                                ) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-start gap-4">
                                            <div>
                                                <p class="font-label-sm font-semibold text-on-surface"><?= e(
                                                    $act['action'],
                                                ) ?></p>
                                                <p class="font-body-sm text-on-surface-variant mt-0.5"><?= e(
                                                    $act['target'],
                                                ) ?></p>
                                                <p class="font-label-sm text-[10px] text-outline mt-1.5"><?= e(
                                                    $act['time'],
                                                ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($activities)): ?>
                                    <p class="font-body-sm text-on-surface-variant px-4 py-2">Belum ada aktivitas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-primary text-on-primary h-14 px-5 rounded-2xl shadow-lg flex items-center gap-2 hover:bg-primary-600 transition-all duration-200 active:scale-95 lg:hidden">
        <span class="material-symbols-outlined text-[24px]">add</span>
        <span class="font-label-md font-bold">Buat Course</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
