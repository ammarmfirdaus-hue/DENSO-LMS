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
    $header_title = 'Dashboard';
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>
    
    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[88px] bg-[#FAFAFA]">

        <section class="w-full space-y-stack-lg">
            
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
                $teacherUser = $user ?? getPortalUser('teacher');
                $teacherName = $teacherUser['fullname'] ?? ($teacherUser['name'] ?? 'Teacher');
                ?>
                <h1 class="font-headline-lg font-bold text-[clamp(16px,5.5vw,26px)] tracking-tight text-on-surface">
                    Selamat <?= e($sapaan) ?>, <?= e($teacherName) ?> 
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
                <div class="col-span-2 bg-white p-stack-md rounded-xl shadow-sm border border-outline-variant/60 flex items-center space-x-stack-md hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-primary-container rounded-full flex shrink-0 items-center justify-center text-white shadow-sm border border-primary/10">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px] font-bold">Total Course Diampu</p>
                        <p class="font-headline-sm text-on-surface font-black mt-1"><?= e(
                            (string) ($stats['total_all_courses'] ?? $stats['total_course'] ?? 0),
                        ) ?> Course</p>
                        <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-on-surface-variant flex-wrap">
                            <span class="text-emerald-700 font-bold"><?= (int)($stats['total_active_courses'] ?? 0) ?> In Progress</span> &bull; 
                            <span class="text-gray-700 font-medium"><?= (int)($stats['total_draft_courses'] ?? 0) ?> Private</span> &bull; 
                            <span class="text-amber-700 font-medium"><?= (int)($stats['pending_review'] ?? 0) ?> Review</span>
                        </div>
                    </div>
                </div>
                <!-- Peserta Aktif -->
                <div class="bg-white p-stack-md rounded-xl shadow-sm border border-outline-variant/60 flex flex-col justify-center items-center text-center space-y-2 hover:shadow-md transition-shadow">
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
                <div class="bg-white p-stack-md rounded-xl shadow-sm border border-outline-variant/60 flex flex-col justify-center items-center text-center space-y-2 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-error-container/50 rounded-full flex items-center justify-center text-error mb-1">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">assignment_late</span>
                    </div>
                    <div class="flex flex-col">
                        <p class="font-headline-sm font-black text-on-surface leading-tight"><?= e(
                            (string) ($stats['pending_review'] ?? 0),
                        ) ?></p>
                        <p class="font-label-sm text-on-surface-variant mt-0.5">Pending Review</p>
                    </div>
                </div>
            </section>

            <!-- COMMAND CENTER: Prioritas Harian & Aktivitas (Moved UP) -->
            <!-- COMMAND CENTER: Prioritas Harian & Aktivitas (Equal Height & Balanced Grid) -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mt-8 items-stretch">
                
                <!-- Perlu Tindak Lanjut (Priority Action Timeline) -->
                <div class="flex flex-col gap-3 h-full">
                    <div class="flex justify-between items-end pb-1 border-b-2 border-surface-variant">
                        <h2 class="font-headline-sm font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-rose-600">assignment_late</span>
                            Perlu Tindak Lanjut
                        </h2>
                        <?php $totalActionCount = $total_pending_actions ?? count($pending_actions); ?>
                        <?php if ($totalActionCount > 0): ?>
                        <span class="text-[11px] font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200/70 shadow-2xs">
                            <?= $totalActionCount ?> Perlu Tindakan
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($pending_actions)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/60 p-4 flex-1 flex flex-col justify-between overflow-hidden">
                        <div class="relative border-l-2 border-surface-variant ml-4 space-y-6 py-2">
                            <?php foreach ($pending_actions as $act): ?>
                                <div class="relative pl-6">
                                    <!-- Timeline Dot -->
                                    <div class="absolute -left-[5px] top-1.5 w-[8px] h-[8px] rounded-full border-2 border-white <?= ($act['type'] ?? '') === 'rejected' ? 'bg-rose-500 ring-4 ring-rose-100' : 'bg-secondary ring-4 ring-secondary/20' ?> shadow-2xs z-10"></div>
                                    
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg shrink-0 <?= ($act['type'] ?? '') === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-surface-container text-on-surface-variant border border-outline-variant/30' ?> flex items-center justify-center shadow-2xs mt-0.5">
                                            <span class="material-symbols-outlined text-[16px]"><?= e($act['icon'] ?? 'edit_note') ?></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="font-label-md font-bold text-on-surface truncate leading-tight"><?= e($act['title']) ?></p>
                                                <?php if (!empty($act['time_ago'])): ?>
                                                <span class="text-[10px] font-semibold text-outline shrink-0 uppercase tracking-wider"><?= e($act['time_ago']) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($act['raw_note'])): ?>
                                            <!-- Catatan Admin Box with soft comfortable contrast -->
                                            <div class="mt-2 p-2.5 bg-surface-container-low/70 border-l-2 border-rose-400 rounded-r-lg">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-800/80 mb-0.5 flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[12px]">feedback</span>
                                                    Catatan Admin (<?= e($act['admin_name'] ?? 'Admin LMS') ?>):
                                                </p>
                                                <p class="text-[12px] font-semibold text-on-surface italic leading-snug">"<?= e($act['raw_note']) ?>"</p>
                                            </div>
                                            <?php else: ?>
                                            <p class="text-[12px] text-on-surface-variant leading-snug mt-1"><?= e($act['message']) ?></p>
                                            <?php endif; ?>

                                            <!-- Single Direct CTA Button (No confusing Lihat Detail) -->
                                            <div class="mt-3">
                                                <a href="<?= e($act['edit_link'] ?? $act['link'] ?? '#') ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#930007] hover:bg-[#780005] text-white font-label-sm font-semibold rounded-lg transition-all shadow-xs text-[11.5px] group">
                                                    <span class="material-symbols-outlined text-[14px] group-hover:rotate-12 transition-transform">edit</span>
                                                    <span><?= ($act['type'] ?? '') === 'rejected' ? 'Perbaiki Course Sekarang' : 'Lanjutkan Edit Course' ?></span>
                                                    <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (($total_pending_actions ?? count($pending_actions)) > 3): ?>
                                <div class="relative pl-6 pt-1">
                                    <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 flex items-center justify-between text-[11px] text-on-surface-variant">
                                        <span class="flex items-center gap-1.5 font-medium">
                                            <span class="material-symbols-outlined text-[15px] text-outline">playlist_add_check</span>
                                            +<?= (($total_pending_actions ?? count($pending_actions)) - 3) ?> tugas revisi lainnya dalam antrean
                                        </span>
                                        <span class="text-[10px] text-outline italic hidden sm:inline">Otomatis naik setelah tugas di atas selesai</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Info to Balance Height -->
                        <div class="px-4 py-2.5 -mx-4 -mb-4 bg-gray-50/70 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
                            <span class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                Prioritaskan revisi sebelum mengajukan kembali
                            </span>
                            <a href="?route=/teacher/courses" class="text-[11px] font-bold text-on-surface-variant hover:text-primary transition-colors flex items-center gap-0.5">
                                Kelola Course &rarr;
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/60 flex-1 flex flex-col items-center justify-center text-center p-8">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 border border-emerald-100">
                            <span class="material-symbols-outlined text-[32px]">task_alt</span>
                        </div>
                        <p class="font-headline-sm font-bold text-on-surface">Pekerjaan Tuntas</p>
                        <p class="text-[13px] text-on-surface-variant mt-1 max-w-xs">Tidak ada tugas mendesak atau revisi yang perlu ditindaklanjuti saat ini.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="flex flex-col gap-3 h-full">
                    <div class="flex justify-between items-end pb-1 border-b-2 border-surface-variant">
                        <h2 class="font-headline-sm font-bold text-on-surface flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[20px] text-secondary">history</span>
                            Aktivitas Terbaru
                        </h2>
                        <a class="font-label-sm font-bold text-secondary hover:underline" href="?route=/teacher/aktivitas">Lihat Log</a>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/60 p-4 flex-1 flex flex-col justify-between">
                        <div class="relative border-l-2 border-surface-variant ml-4 space-y-6 py-2">
                            <?php foreach ($activities as $i => $act): ?>
                                <div class="relative pl-6">
                                    <!-- Timeline Dot -->
                                    <div class="absolute -left-[5px] top-1 w-[8px] h-[8px] rounded-full border-2 border-white bg-outline-variant ring-4 ring-surface-variant shadow-sm z-10 <?php if (
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
                    // Tampilkan seluruh course riil milik pengajar (hingga 6 course)
                    $dashboardCourses = $courses ?? [];

                    if (empty($dashboardCourses)): ?>
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white py-12 px-6 rounded-xl border border-outline-variant/40 flex flex-col items-center justify-center text-center shadow-sm">
                        <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-on-surface-variant mb-4">
                            <span class="material-symbols-outlined text-[32px]">menu_book</span>
                        </div>
                        <p class="font-headline-sm font-bold text-on-surface mb-1">Belum Ada Course</p>
                        <p class="text-[13px] text-on-surface-variant mb-5 max-w-md">Anda belum memiliki course di sistem LMS. Silakan buat course baru untuk memulai proses pembelajaran.</p>
                        <a href="?route=/teacher/course/create" class="inline-flex items-center gap-1.5 bg-white text-error border border-error font-label-md font-bold px-5 py-2.5 rounded-lg hover:bg-error/5 transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Buat Course Baru
                        </a>
                    </div>
                    <?php else: ?>
                    <?php foreach ($dashboardCourses as $course): ?>
                    <?php
                    $rawStat = $course['status'] ?? 'Draft';
                    $displayStat = match (strtolower($rawStat)) {
                        'running', 'active' => 'In Progress',
                        'approved' => 'Approved',
                        'submitted' => 'Pending Review',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                        'archived' => 'Archived',
                        default => 'Draft',
                    };

                    $badgeCls = match ($displayStat) {
                        'In Progress' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                        'Approved' => 'text-teal-700 bg-teal-50 border-teal-200',
                        'Pending Review' => 'text-amber-700 bg-amber-50 border-amber-200',
                        'Rejected' => 'text-red-700 bg-red-50 border-red-200',
                        'Completed' => 'text-blue-700 bg-blue-50 border-blue-200',
                        default => 'text-gray-700 bg-gray-50 border-gray-300 shadow-sm',
                    };

                    $progressBarHex = match ($displayStat) {
                        'In Progress' => '#10B981',
                        'Approved' => '#0D9488',
                        'Completed' => '#3B82F6',
                        'Pending Review' => '#F59E0B',
                        'Rejected' => '#EF4444',
                        default => '#9CA3AF',
                    };
                    $progressTextColor = match ($displayStat) {
                        'In Progress' => 'text-emerald-600',
                        'Approved' => 'text-teal-600',
                        'Completed' => 'text-blue-600',
                        'Pending Review' => 'text-amber-600',
                        'Rejected' => 'text-red-600',
                        default => 'text-on-surface-variant',
                    };

                    $progress = (int) ($course['progress'] ?? 0);

                    $contextLine = match ($displayStat) {
                        'Draft' => 'Belum diajukan ke admin',
                        'Pending Review' => 'Menunggu review admin',
                        'Rejected' => !empty($course['rejection_note']) ? 'Catatan: "' . e(mb_strimwidth($course['rejection_note'], 0, 36, '...')) . '"' : 'Perlu revisi segera',
                        'Completed' => 'Course telah selesai',
                        'Archived' => 'Course telah diarsipkan',
                        default => 'Terakhir diperbarui: ' . e($course['updated_at'] ?? 'Baru saja'),
                    };

                    $ctaText = match ($displayStat) {
                        'Draft' => 'Edit Course',
                        'Pending Review', 'Approved' => 'Lihat Detail',
                        'Rejected' => 'Perbaiki Course',
                        'In Progress' => 'Kelola Penilaian',
                        'Completed' => 'Lihat Detail',
                        default => 'Detail',
                    };

                    $ctaHref = match ($displayStat) {
                        'Draft', 'Rejected' => '?route=/teacher/course/create&course_id=' . e((string) $course['id']),
                        'In Progress' => '?route=/teacher/grade&course_id=' . e((string) $course['id']),
                        default => '?route=/teacher/course/detail&course_id=' . e((string) $course['id']),
                    };

                    $ctaCls = match ($displayStat) {
                        'Draft' => 'text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400',
                        'Pending Review' => 'text-amber-700 border-amber-300 hover:bg-amber-50 hover:border-amber-400',
                        'Approved' => 'text-teal-700 border-teal-300 hover:bg-teal-50 hover:border-teal-400',
                        'Rejected' => 'text-red-700 border-red-300 hover:bg-red-50 hover:border-red-400',
                        'In Progress' => 'text-emerald-700 border-emerald-300 hover:bg-emerald-50 hover:border-emerald-400',
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
                                <a href="<?= $ctaHref ?>" class="hover:text-primary transition-colors focus:outline-none focus:underline"><?= e($course['title']) ?></a>
                            </h3>

                            <!-- 3. Category -->
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
                                    <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Assesment</span>
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
                        <div class="px-5 py-4 flex items-center justify-between mt-auto border-t border-outline-variant/30">
                            <!-- Left: Context Info -->
                            <span class="font-body-sm text-[11px] text-on-surface-variant italic leading-snug flex-1">
                                <?= e($contextLine) ?>
                            </span>
                            <!-- Right: Action Buttons -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="<?= $ctaHref ?>" class="inline-flex items-center justify-center font-label-md font-bold text-on-surface border border-outline-variant hover:bg-surface-variant px-3 py-1.5 rounded-lg transition-colors cursor-pointer <?= $ctaCls ?>">
                                    <?= e($ctaText) ?>
                                </a>
                                <button type="button" data-id="<?= e((string)$course['id']) ?>" data-title="<?= e($course['title']) ?>" onclick="confirmDeleteCourseCard(this)" class="w-8 h-8 rounded-lg border border-outline-variant/60 text-outline hover:text-error hover:border-error/40 hover:bg-error/5 flex items-center justify-center transition-colors cursor-pointer" title="Hapus Course Permanen">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-white text-primary h-14 w-14 rounded-full shadow-[0_4px_16px_rgba(0,0,0,0.12)] border border-primary/30 flex items-center justify-center hover:scale-105 active:scale-95 transition-all lg:hidden hover:border-primary hover:shadow-[0_4px_20px_rgba(0,0,0,0.15)]">
        <span class="material-symbols-outlined text-[28px]">add</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js"></script>
    <script src="assets/js/DX_LMS_M_Helpers.js"></script>
    <script>
        function confirmDeleteCourseCard(elOrId, rawTitle) {
            let courseId = elOrId;
            let title = rawTitle;
            if (typeof elOrId === 'object' && elOrId !== null) {
                courseId = elOrId.getAttribute('data-id');
                title = elOrId.getAttribute('data-title');
            }
            if (!courseId) return;

            const doDelete = () => {
                fetch('?route=/teacher/course/delete&id=' + encodeURIComponent(courseId))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Berhasil Dihapus!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#E20613'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert(data.message);
                                window.location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Gagal Menghapus', data.error || data.message, 'error');
                            } else {
                                alert(data.error || data.message);
                            }
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan saat menghapus course.');
                    });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Course Permanen?',
                    text: 'PERINGATAN: Course "' + title + '" dan seluruh sesi serta materinya akan DIHAPUS TOTAL dari database SSMS. Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            } else if (confirm('PERINGATAN: Course "' + title + '" akan dihapus total dari database. Lanjutkan?')) {
                doDelete();
            }
        }
    </script>
</body>
</html>
