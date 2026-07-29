<?php
/**
 * Tampilan Dashboard Pengajar (Teacher Dashboard).
 * Workspace harian pengajar — pusat kerja, operasional, informatif.
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
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'beranda'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'LMS';
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>
    
    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[72px] bg-[#FAFAFA]">

        <section class="max-w-[1400px] mx-auto space-y-stack-lg">
            
            <!-- Greeting Section -->
            <section class="flex flex-col gap-1">
                <?php
                $hour = (int) date('H');
                $sapaan = 'malam';
                if ($hour >= 4 && $hour < 11) {
                    $sapaan = 'pagi';
                } elseif ($hour >= 11 && $hour < 15) {
                    $sapaan = 'siang';
                } elseif ($hour >= 15 && $hour < 18) {
                    $sapaan = 'sore';
                }
                ?>
                <h1 class="font-headline-lg font-bold text-[clamp(16px,5.5vw,26px)] tracking-tight text-on-surface">
                    Selamat <?= e($sapaan) ?>, <?= e($_SESSION['user']['fullname'] ?? 'Hermawan') ?> 👋
                </h1>
                <p class="font-body-md text-on-surface-variant font-medium">
                    Hari ini Anda mengelola <span class="text-primary font-bold"><?= count(
                        $courses,
                    ) ?> course aktif</span><?php if (
     !empty($pending_actions)
 ): ?> dan memiliki <span class="text-error font-bold"><?= count(
     $pending_actions,
 ) ?> tugas prioritas</span><?php endif; ?>.
                </p>
            </section>

            <!-- Summary Cards (Bento Grid) -->
            <section class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
                <!-- Course Diampu (Lebar) -->
                <div class="col-span-2 bg-surface p-stack-md rounded-xl shadow-sm border border-surface-variant flex items-center space-x-stack-md hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-primary-container rounded-full flex shrink-0 items-center justify-center text-primary shadow-sm border border-primary/10">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px] font-bold">Total Course Diampu</p>
                        <p class="font-headline-sm text-on-surface font-black mt-1"><?= e(
                            (string) ($stats['total_course'] ?? 0),
                        ) ?> Course Aktif</p>
                    </div>
                    <span class="hidden sm:inline-block text-[11px] font-bold text-success bg-success/10 px-2.5 py-1 rounded-full border border-success/20">+1 Bulan Ini</span>
                </div>
                <!-- Peserta Aktif -->
                <div class="bg-surface p-stack-md rounded-xl shadow-sm border border-surface-variant flex flex-col justify-center items-center text-center space-y-2 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-secondary-container/50 rounded-full flex items-center justify-center text-secondary mb-1">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">groups</span>
                    </div>
                    <div class="flex flex-col">
                        <p class="font-headline-sm font-black text-on-surface leading-tight"><?= e(
                            (string) ($stats['active_students'] ?? 0),
                        ) ?></p>
                        <p class="font-label-sm text-on-surface-variant mt-0.5">Peserta Aktif</p>
                    </div>
                </div>
                <!-- Pending Review -->
                <div class="bg-surface p-stack-md rounded-xl shadow-sm border border-surface-variant flex flex-col justify-center items-center text-center space-y-2 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-error-container/50 rounded-full flex items-center justify-center text-error mb-1">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">assignment_late</span>
                    </div>
                    <div class="flex flex-col">
                        <p class="font-headline-sm font-black text-on-surface leading-tight"><?= e(
                            (string) ($stats['pending_review'] ?? 0),
                        ) ?></p>
                        <p class="font-label-sm text-on-surface-variant mt-0.5 max-w-[80px] leading-tight mx-auto truncate text-clip">Pending Review</p>
                    </div>
                </div>
            </section>

            <!-- COMMAND CENTER: Prioritas Harian & Aktivitas (Moved UP) -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mt-8">
                
                <!-- Perlu Tindak Lanjut -->
                <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-end pb-1 border-b-2 border-surface-variant">
                        <h2 class="font-headline-sm font-bold text-error flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[22px]">emergency_home</span>
                            Perlu Tindak Lanjut
                        </h2>
                    </div>
                    <?php if (!empty($pending_actions)): ?>
                    <div class="bg-surface rounded-xl shadow-sm border border-outline-variant/30 divide-y divide-surface-variant">
                        <?php foreach ($pending_actions as $act): ?>
                            <a href="<?= e(
                                $act['link'] ?? '#',
                            ) ?>" class="flex items-start gap-4 p-4 group hover:bg-error/5 transition-colors first:rounded-t-xl last:rounded-b-xl border-l-2 border-transparent hover:border-error">
                                <div class="w-9 h-9 rounded-full shrink-0 bg-error-container flex items-center justify-center text-error">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;"><?= e(
                                        $act['icon'] ?? 'assignment_late',
                                    ) ?></span>
                                </div>
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <p class="font-label-md font-bold text-on-surface group-hover:text-error transition-colors truncate"><?= e(
                                        $act['title'],
                                    ) ?></p>
                                    <p class="text-[12px] text-on-surface-variant leading-snug line-clamp-2 mt-1"><?= e(
                                        $act['message'],
                                    ) ?></p>
                                </div>
                                <span class="material-symbols-outlined text-outline text-[18px] mt-1 shrink-0 group-hover:text-error transition-colors group-hover:translate-x-1 duration-200">arrow_forward</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="bg-surface rounded-xl shadow-sm border border-outline-variant/30 p-8 flex flex-col items-center justify-center text-center">
                        <span class="material-symbols-outlined text-[48px] text-surface-variant mb-3">task_alt</span>
                        <p class="font-headline-sm font-bold text-on-surface">Pekerjaan Tuntas</p>
                        <p class="text-[13px] text-on-surface-variant mt-1">Tidak ada tugas mendesak saat ini.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-end pb-1 border-b-2 border-surface-variant">
                        <h2 class="font-headline-sm font-bold text-on-surface flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[20px] text-secondary">history</span>
                            Aktivitas Terbaru
                        </h2>
                        <a class="font-label-sm font-bold text-secondary hover:underline" href="?route=/teacher/aktivitas">Lihat Log</a>
                    </div>
                    <div class="bg-surface rounded-xl shadow-sm border border-outline-variant/30 p-4">
                        <div class="relative border-l-2 border-surface-variant ml-4 space-y-6 py-2">
                            <?php foreach ($activities as $i => $act): ?>
                                <div class="relative pl-6">
                                    <!-- Timeline Dot -->
                                    <div class="absolute -left-[5px] top-1 w-[8px] h-[8px] rounded-full border-2 border-surface bg-outline-variant ring-4 ring-surface-variant shadow-sm z-10 <?php if (
                                        $i === 0
                                    ) {
                                        echo 'bg-secondary ring-secondary/20';
                                    } ?>"></div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg shrink-0 <?= e(
                                            $act['color_class'] ?? 'bg-surface-container',
                                        ) ?> flex items-center justify-center border border-outline-variant/20 shadow-sm mt-0.5">
                                            <span class="material-symbols-outlined text-[16px]"><?= e(
                                                $act['icon'] ?? 'bolt',
                                            ) ?></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-label-md font-bold text-on-surface truncate leading-tight"><?= e(
                                                $act['action'],
                                            ) ?></p>
                                            <p class="text-[12px] text-on-surface-variant truncate mt-0.5"><?= e(
                                                $act['target'],
                                            ) ?></p>
                                            <p class="text-[10px] font-bold text-outline uppercase tracking-wider mt-1.5"><?= e(
                                                $act['time'],
                                            ) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </section>

            <!-- COURSE ARSENAL (Preview Course) -->
            <section class="space-y-4 pt-6">
                <!-- Heading with Inline CTAs -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-outline-variant/40 pb-3">
                    <h2 class="font-headline-sm font-bold text-on-surface text-[20px] tracking-tight">
                        Course yang Diampu
                    </h2>
                    
                    <div class="flex items-center gap-2">
                        <!-- Primary CTA — Outline Red Style -->
                        <a href="?route=/teacher/course/create" class="inline-flex items-center gap-1.5 bg-white text-error border border-error font-label-md font-bold px-4 py-2 rounded-lg hover:bg-error/5 hover:border-error transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Buat Course Baru
                        </a>
                        <!-- Lihat Semua -->
                        <a href="?route=/teacher/courses" class="inline-flex items-center gap-1 text-on-surface-variant font-label-md font-bold px-3 py-2 rounded-lg hover:bg-surface-container hover:text-on-surface transition-colors">
                            Lihat Semua
                        </a>
                    </div>
                </div>

                <!-- 3x2 Grid for Courses -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                    <?php
                    // Filter courses to ONLY show 'In Progress' and 'Completed'
                    $inProgressCourses = [];
                    $completedCourses = [];

                    foreach ($courses as $course) {
                        $rawStat = $course['status'] ?? '';
                        if (in_array($rawStat, ['Running', 'Submitted', 'Approved'])) {
                            $inProgressCourses[] = $course;
                        } elseif (in_array($rawStat, ['Completed', 'Archived'])) {
                            $completedCourses[] = $course;
                        }
                    }
                    // Sort: In Progress first, then Completed
                    $dashboardCourses = array_merge($inProgressCourses, $completedCourses);

                    if (empty($dashboardCourses)): ?>
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white py-12 px-6 rounded-xl border border-outline-variant/40 flex flex-col items-center justify-center text-center shadow-sm">
                        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-on-surface-variant mb-4">
                            <span class="material-symbols-outlined text-[32px]">menu_book</span>
                        </div>
                        <p class="font-headline-sm font-bold text-on-surface mb-1">Belum Ada Course Aktif</p>
                        <p class="text-[13px] text-on-surface-variant mb-5 max-w-md">Anda belum memiliki course yang sedang berjalan atau sudah selesai. Silakan buat course baru.</p>
                        <a href="?route=/teacher/course/create" class="inline-flex items-center gap-1.5 bg-white text-error border border-error font-label-md font-bold px-5 py-2.5 rounded-lg hover:bg-error/5 transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Buat Course Baru
                        </a>
                    </div>
                    <?php else: ?>
                    <?php foreach ($dashboardCourses as $course): ?>
                    <?php
                    $rawStat = $course['status'] ?? 'Draft';
                    $displayStat = match ($rawStat) {
                        'Running', 'Submitted', 'Approved' => 'In Progress',
                        'Completed', 'Archived' => 'Completed',
                        default => 'Draft',
                    };

                    $badgeCls = match ($displayStat) {
                        'In Progress' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                        'Completed' => 'text-blue-700 bg-blue-50 border-blue-200',
                        default => 'text-gray-700 bg-gray-50 border-gray-300 shadow-sm',
                    };

                    $progressBarHex = match ($displayStat) {
                        'In Progress' => '#10B981',
                        'Completed' => '#3B82F6',
                        default => '#9CA3AF',
                    };
                    $progressTextColor = match ($displayStat) {
                        'In Progress' => 'text-emerald-600',
                        'Completed' => 'text-blue-600',
                        default => 'text-on-surface-variant',
                    };

                    $progress = (int) ($course['progress'] ?? 0);

                    $contextLine = match ($rawStat) {
                        'Completed' => 'Course telah selesai',
                        'Archived' => 'Course telah diarsipkan',
                        default => 'Terakhir diperbarui: ' . e($course['updated_at'] ?? 'Baru saja'),
                    };

                    $ctaText = match ($displayStat) {
                        'In Progress' => 'Kelola Penilaian',
                        'Completed' => 'Lihat Detail',
                        default => 'Edit Course',
                    };
                    $ctaHref = match ($displayStat) {
                        'In Progress' => '?route=/teacher/grade&course_id=' . e((string) $course['id']),
                        default => '?route=/teacher/course/detail&course_id=' . e((string) $course['id']),
                    };
                    $ctaCls = match ($displayStat) {
                        'In Progress'
                            => 'text-emerald-700 border-emerald-300 hover:bg-emerald-50 hover:border-emerald-400',
                        'Completed' => 'text-blue-700 border-blue-300 hover:bg-blue-50 hover:border-blue-400',
                        default => 'text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400',
                    };
                    ?>
                    <div class="course-card flex flex-col h-full bg-white rounded-xl border border-outline-variant/60 overflow-hidden hover:border-outline-variant hover:shadow-[0_4px_24px_rgba(0,0,0,0.06)] transition-all duration-300">
                        
                        <!-- Card Body -->
                        <div class="p-5 flex flex-col flex-1 gap-3">

                            <!-- 1. Status Badge -->
                            <div class="flex items-start">
                                <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-widest px-2.5 py-[3px] rounded-full border <?= $badgeCls ?>">
                                    <?= e($displayStat) ?>
                                </span>
                            </div>

                            <!-- 2. Course Title -->
                            <h3 class="font-headline-sm font-bold text-[16px] text-on-surface leading-snug line-clamp-2 min-h-[2.75rem] mt-1" title="<?= e(
                                $course['title'],
                            ) ?>">
                                <?= e($course['title']) ?>
                            </h3>

                            <!-- 3. Category (No Code) -->
                            <p class="text-[12px] text-on-surface-variant font-medium">
                                <?= e($course['category'] ?? 'Umum') ?>
                            </p>

                            <!-- 4. Operational Metrics -->
                            <div class="grid grid-cols-3 border-t border-b border-outline-variant/30 py-3 mt-1.5">
                                <div class="flex flex-col items-center">
                                    <span class="text-[16px] font-black text-on-surface leading-none"><?= e(
                                        (string) ($course['participants'] ?? 0),
                                    ) ?></span>
                                    <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Peserta</span>
                                </div>
                                <div class="flex flex-col items-center border-x border-outline-variant/30">
                                    <span class="text-[16px] font-black text-on-surface leading-none"><?= e(
                                        (string) ($course['sessions'] ?? 0),
                                    ) ?></span>
                                    <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Sesi</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[16px] font-black text-on-surface leading-none"><?= e(
                                        (string) ($course['assessments'] ?? 0),
                                    ) ?></span>
                                    <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Asesmen</span>
                                </div>
                            </div>

                            <!-- 5. Progress Bar -->
                            <div class="space-y-2 mt-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Progress Kelas</span>
                                    <div class="flex items-center gap-1.5">
                                        <?php
                                        $totalPeserta = (int) ($course['participants'] ?? 0);
                                        $selesai = floor(($progress / 100) * $totalPeserta);
                                        ?>
                                        <span class="text-[9px] text-on-surface-variant font-medium">(<?= $selesai ?> dari <?= $totalPeserta ?> Selesai)</span>
                                        <span class="text-[12px] font-bold <?= $progressTextColor ?>"><?= $progress ?>%</span>
                                    </div>
                                </div>
                                <div class="w-full rounded-full h-1.5 overflow-hidden bg-surface-variant/40">
                                    <div class="h-full rounded-full transition-all duration-500" style="width: <?= $progress ?>%; background-color: <?= $progressBarHex ?>;"></div>
                                </div>
                            </div>

                        </div>

                        <!-- 6 & 7. Footer: Context (left) + Action (right) -->
                        <div class="px-5 py-4 bg-[#FAFAFA] flex items-center justify-between gap-3 mt-auto border-t border-outline-variant/40">
                            <!-- Left: Context Info -->
                            <p class="text-[11px] text-on-surface-variant font-medium leading-snug flex-1 min-w-0 line-clamp-1 italic">
                                <?= $contextLine ?>
                            </p>
                            <!-- Right: Action Button -->
                            <a href="<?= $ctaHref ?>" class="inline-flex items-center shrink-0 text-[12px] font-bold px-3.5 py-1.5 rounded-md border bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)] transition-colors <?= $ctaCls ?>">
                                <?= $ctaText ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif;
                    ?>
                </div>
            </section>

        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-white text-primary h-14 w-14 rounded-full shadow-[0_4px_16px_rgba(0,0,0,0.12)] border border-primary/30 flex items-center justify-center hover:scale-105 active:scale-95 transition-all lg:hidden hover:border-primary hover:shadow-[0_4px_20px_rgba(0,0,0,0.15)]">
        <span class="material-symbols-outlined text-[28px]">add</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
