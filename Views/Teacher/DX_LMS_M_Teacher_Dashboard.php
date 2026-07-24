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

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'LMS';
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-2 lg:pt-4 bg-[#FAFAFA]">

        <!-- Desktop Quick Header -->
        <div class="hidden lg:flex items-center justify-between mb-4 pb-2 border-b border-outline-variant/30 sticky top-0 bg-[#FAFAFA]/90 backdrop-blur-md z-30">
            <div class="w-10"></div> <!-- Spacer for centering -->
            <h1 class="font-bold text-primary flex-1 text-center tracking-tight text-[22px]">LMS</h1>
            <a href="?route=/teacher/notifikasi" class="relative w-10 h-10 flex items-center justify-center text-on-surface-variant bg-surface hover:bg-surface-container hover:text-primary rounded-full transition-colors border border-outline-variant/30 shadow-sm group">
                <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">notifications</span>
                <?php if (!empty($pending_actions)): ?>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface animate-pulse"></span>
                <?php endif; ?>
            </a>
        </div>

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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 border-b border-surface-variant pb-3 sticky top-[72px] bg-[#FAFAFA] z-20">
                    <h2 class="font-headline-sm font-bold text-on-surface flex items-center gap-2 text-[22px]">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">folder_managed</span>
                        Course yang Diampu
                    </h2>
                    
                    <div class="flex items-center gap-2">
                        <!-- Primary CTA (Compact Button) -->
                        <a href="?route=/teacher/course/create" class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-md font-bold px-4 py-2 rounded-lg shadow-sm hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            Buat Course
                        </a>
                        <!-- Lihat Semua -->
                        <a href="?route=/teacher/courses" class="inline-flex items-center gap-1 bg-surface-container text-on-surface font-label-md font-bold px-4 py-2 rounded-lg border border-outline-variant/30 hover:bg-surface-variant transition-colors">
                            Lihat Semua
                        </a>
                    </div>
                </div>

                <!-- 3x2 Grid for 6 Courses -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter pt-2">
                    <?php foreach ($courses as $course): ?>
                    <?php
                    // Lifecycle badge styling
                    $stat = $course['status'] ?? 'Draft';
                    $badgeCls = match ($stat) {
                        'Running' => 'bg-primary-container text-on-primary-container border-primary/20',
                        'Submitted' => 'bg-info-container text-on-info-container border-info/20',
                        'Completed' => 'bg-surface text-on-surface border-surface-variant',
                        'Approved' => 'bg-success-container text-on-success-container border-success/20',
                        default => 'bg-surface-container-high text-on-surface-variant border-outline-variant',
                    };

                    // Icon gradient – mengadaptasi kehalusan visual
                    $iconGradients = [
                        'health_and_safety' => 'from-emerald-400 to-green-600',
                        'manufacturing' => 'from-blue-400 to-indigo-600',
                        'fact_check' => 'from-orange-400 to-amber-600',
                        'precision_manufacturing' => 'from-purple-400 to-fuchsia-600',
                        'fire_extinguisher' => 'from-rose-400 to-red-600',
                        'campaign' => 'from-yellow-400 to-amber-600',
                    ];
                    $icon = $course['icon'] ?? 'menu_book';
                    $grad = $iconGradients[$icon] ?? 'from-primary/70 to-primary';

                    $progress = (int) ($course['progress'] ?? 0);
                    ?>
                    <div class="course-card flex flex-col h-full bg-surface rounded-2xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden group hover:border-primary/40 hover:shadow-md transition-all duration-300">
                        
                        <!-- Visual Header -->
                        <div class="h-[100px] bg-gradient-to-br <?= $grad ?> relative flex items-center px-4 overflow-hidden">
                            <!-- Large background icon watermark -->
                            <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-white/20 transform -rotate-12" style="font-size: 110px; font-variation-settings: 'FILL' 1;"><?= e(
                                $icon,
                            ) ?></span>
                            
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/30 z-10 shrink-0">
                                <span class="material-symbols-outlined text-white text-[24px]" style="font-variation-settings: 'FILL' 1;"><?= e(
                                    $icon,
                                ) ?></span>
                            </div>

                            <!-- Course Code & Lifecycle Badge -->
                            <div class="absolute top-3 right-3 flex flex-col items-end gap-1.5 z-10">
                                <span class="font-label-sm text-[10px] font-extrabold uppercase tracking-widest text-white/90 bg-black/20 px-2 py-0.5 rounded shadow-sm backdrop-blur-sm border border-white/10">
                                    <?= e($course['code']) ?>
                                </span>
                                <span class="font-label-sm text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full border shadow-sm backdrop-blur-sm <?= $badgeCls ?>">
                                    <?= e($stat) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 space-y-4 flex flex-col flex-1 relative">
                            <!-- Title -->
                            <h3 class="font-headline-sm font-bold text-on-surface leading-tight line-clamp-2 group-hover:text-primary transition-colors" title="<?= e(
                                $course['title'],
                            ) ?>" style="min-height: 2.75rem;">
                                <?= e($course['title']) ?>
                            </h3>
                            
                            <!-- Detailed Metrics (Micro-stats) grid layout -->
                            <div class="grid grid-cols-3 gap-2 bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-2.5">
                                <div class="flex flex-col items-center justify-center border-r border-outline-variant/30 group/stat">
                                    <span class="material-symbols-outlined text-[16px] text-on-surface-variant group-hover/stat:text-primary transition-colors mb-0.5">group</span>
                                    <span class="font-label-sm text-[13px] font-black text-on-surface"><?= e(
                                        (string) ($course['participants'] ?? 0),
                                    ) ?></span>
                                    <span class="text-[9px] font-bold text-outline uppercase tracking-wide">Peserta</span>
                                </div>
                                <div class="flex flex-col items-center justify-center border-r border-outline-variant/30 group/stat">
                                    <span class="material-symbols-outlined text-[16px] text-on-surface-variant group-hover/stat:text-secondary transition-colors mb-0.5">event_note</span>
                                    <span class="font-label-sm text-[13px] font-black text-on-surface"><?= e(
                                        (string) ($course['sessions'] ?? 0),
                                    ) ?></span>
                                    <span class="text-[9px] font-bold text-outline uppercase tracking-wide">Sesi</span>
                                </div>
                                <div class="flex flex-col items-center justify-center group/stat">
                                    <span class="material-symbols-outlined text-[16px] text-on-surface-variant group-hover/stat:text-error transition-colors mb-0.5">quiz</span>
                                    <span class="font-label-sm text-[13px] font-black text-on-surface"><?= e(
                                        (string) ($course['assessments'] ?? 0),
                                    ) ?></span>
                                    <span class="text-[9px] font-bold text-outline uppercase tracking-wide">Asesmen</span>
                                </div>
                            </div>

                            <!-- Progress bar -->
                            <div class="pt-2 mt-auto">
                                <?php if (in_array($stat, ['Running', 'Completed'])): ?>
                                    <div class="flex justify-between font-label-sm mb-1.5 text-[11px] uppercase font-bold tracking-wide text-on-surface-variant">
                                        <span>Progress Kelas</span>
                                        <span class="<?= $stat === 'Completed'
                                            ? 'text-success'
                                            : 'text-primary' ?>"><?= $progress ?>%</span>
                                    </div>
                                    <div class="w-full bg-surface-container-highest rounded-full h-1.5 overflow-hidden">
                                        <?php $barColor = $stat === 'Completed' ? 'bg-success' : 'bg-primary'; ?>
                                        <div class="<?= $barColor ?> h-1.5 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
                                    </div>
                                <?php elseif ($stat === 'Submitted'): ?>
                                    <div class="flex items-center gap-1.5 text-[12px] font-bold text-info">
                                        <span class="material-symbols-outlined text-[16px]">hourglass_top</span>
                                        Menunggu Persetujuan Admin
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-on-surface-variant uppercase tracking-wide">
                                        <span class="material-symbols-outlined text-[14px]">edit_calendar</span>
                                        Diperbarui: <?= e($course['updated_at'] ?? 'Baru Saja') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- CTA Button inside card -->
                            <div class="pt-3 border-t border-outline-variant/30">
                                <?php if ($stat === 'Running'): ?>
                                    <a href="?route=/teacher/grade&course_id=<?= e(
                                        (string) $course['id'],
                                    ) ?>" class="w-full font-label-md font-bold text-on-primary bg-primary border-b-4 border-primary-600 hover:border-b-2 hover:-translate-y-0.5 hover:mt-0.5 active:mt-1 active:border-b-0 py-2.5 rounded-lg text-center flex items-center justify-center gap-2 transition-all">
                                        Kelola Penilaian <span class="material-symbols-outlined text-[18px]">fact_check</span>
                                    </a>
                                <?php elseif ($stat === 'Draft'): ?>
                                    <a href="?route=/teacher/course/create&course_id=<?= e(
                                        (string) $course['id'],
                                    ) ?>" class="w-full bg-surface-container text-on-surface border border-outline-variant/50 hover:bg-surface-variant font-label-md font-bold py-2 rounded-lg text-center flex items-center justify-center gap-1 transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">edit</span> Edit Draft
                                    </a>
                                <?php else: ?>
                                    <a href="?route=/teacher/course/detail&course_id=<?= e(
                                        (string) $course['id'],
                                    ) ?>" class="w-full hover:bg-surface-container-low text-on-surface font-label-md font-bold py-2 rounded-lg text-center block transition-colors border border-transparent hover:border-outline-variant/30">
                                        Buka Detail Course
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-primary text-on-primary h-14 w-14 rounded-full shadow-[0_8px_24px_rgba(0,0,0,0.2)] flex items-center justify-center hover:scale-105 active:scale-95 transition-all lg:hidden hover:bg-primary-600">
        <span class="material-symbols-outlined text-[28px]">add</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
