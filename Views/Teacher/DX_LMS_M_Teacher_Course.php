<?php
/**
 * Tampilan Daftar Course Pengajar (Teacher Course List).
 *
 * Menampilkan rekomendasi dan memonitor lifecycle course.
 */

// Handle PHP Filter Processing Before Setting HTML variables
$courses = DX_LMS_M_Teacher_MockData::getManagedCourses();
$current_filter_raw = $_GET['status'] ?? 'all';
$current_filter = strtolower($current_filter_raw) === 'all' ? 'all' : urldecode($current_filter_raw);

$filteredCourses = [];
foreach ($courses as $course) {
    // Map raw status safely to UI Status
    $rawStat = $course['status'] ?? 'Draft';
    $displayStat = match ($rawStat) {
        'Draft' => 'Private',
        'Submitted' => 'Pending Review',
        'Rejected' => 'Rejected',
        'Approved', 'Running' => 'In Progress',
        'Completed' => 'Completed',
        'Archived' => 'Archived',
        default => 'Private',
    };
    $course['_mapped_status'] = $displayStat;

    if ($current_filter === 'all' || strtolower($current_filter) === strtolower($displayStat)) {
        $filteredCourses[] = $course;
    }
}

// Custom sort when "all" is active
if ($current_filter === 'all') {
    $priority = [
        'Rejected' => 1,
        'Pending Review' => 2,
        'Private' => 3,
        'In Progress' => 4,
        'Completed' => 5,
        'Archived' => 6,
    ];
    usort($filteredCourses, function ($a, $b) use ($priority) {
        $pA = $priority[$a['_mapped_status']] ?? 99;
        $pB = $priority[$b['_mapped_status']] ?? 99;
        return $pA <=> $pB;
    });
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Course - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        /* Hide scrollbar for category tabs */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'course'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Kelola Course';
    $show_create_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[72px]">
        
        <section class="w-full space-y-stack-md">
            
            <!-- Page Header with Create Course Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <div>
                    <h1 class="font-headline-md font-bold text-on-surface tracking-tight">Daftar Course Saya</h1>
                    <p class="font-body-md text-on-surface-variant mt-0.5">Kelola kurikulum, pengajuan materi, dan pantau status publikasi course Anda.</p>
                </div>
                <a href="?route=/teacher/course/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-sm hover:shadow transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    <span>Buat Course</span>
                </a>
            </div>

            <!-- Filter Tabs -->
            <div class="relative w-full -mx-container-margin px-container-margin md:mx-0 md:px-0 mb-4 sticky lg:relative top-14 lg:top-0 bg-surface z-20 py-2">
                <div class="flex gap-2 overflow-x-auto no-scrollbar snap-x py-1">
                    <?php
                    $tabsInfo = [
                        ['id' => 'all', 'label' => 'Semua'],
                        ['id' => 'Private', 'label' => 'Private'],
                        ['id' => 'Pending Review', 'label' => 'Pending Review'],
                        ['id' => 'Rejected', 'label' => 'Rejected'],
                        ['id' => 'In Progress', 'label' => 'In Progress'],
                        ['id' => 'Completed', 'label' => 'Completed'],
                        ['id' => 'Archived', 'label' => 'Archived'],
                    ];

                    foreach ($tabsInfo as $tab):

                        $isActive = strtolower($current_filter) === strtolower($tab['id']);
                        $baseClass =
                            'snap-start whitespace-nowrap px-4 py-2 rounded-full font-label-md transition-all active:scale-95 border ';
                        // Active = Emerald theme matching the primary system UI or a clean dark shade. Let's use a standard primary shade
                        $activeClass = 'bg-primary text-white border-primary font-bold';
                        $inactiveClass =
                            'bg-surface text-on-surface-variant hover:bg-surface-variant border-outline-variant hover:border-outline';
                        ?>
                        <a href="?route=/teacher/courses&status=<?= e(urlencode($tab['id'])) ?>" 
                           class="<?= $baseClass ?> <?= $isActive ? $activeClass : $inactiveClass ?>">
                            <?= e($tab['label']) ?>
                        </a>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- Course Grid -->
            <?php if (empty($filteredCourses)): ?>
                <div class="w-full bg-white py-12 px-6 rounded-xl border border-outline-variant/40 flex flex-col items-center justify-center text-center shadow-sm">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-on-surface-variant mb-4">
                        <span class="material-symbols-outlined text-[32px]">menu_book</span>
                    </div>
                    <h3 class="font-headline-sm font-bold text-on-surface mb-2">Tidak Ada Course</h3>
                    <p class="font-body-sm text-on-surface-variant max-w-sm mb-6">Course dengan status filter tersebut tidak ditemukan.</p>
                    <a href="?route=/teacher/course/create" class="inline-flex items-center gap-1.5 bg-white text-error border border-error font-label-md font-bold px-5 py-2.5 rounded-lg hover:bg-error/5 transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Buat Course Baru
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4">
                    <?php foreach ($filteredCourses as $course): ?>
                        <?php
                        $displayStat = $course['_mapped_status'];
                        $progress = (int) ($course['progress'] ?? 0);

                        $badgeCls = match ($displayStat) {
                            'Private' => 'text-gray-700 bg-gray-50 border-gray-300 shadow-sm',
                            'Pending Review' => 'text-amber-700 bg-amber-50 border-amber-200',
                            'Rejected' => 'text-red-700 bg-red-50 border-red-200',
                            'In Progress' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                            'Completed' => 'text-blue-700 bg-blue-50 border-blue-200',
                            'Archived' => 'text-gray-500 bg-gray-100 border-gray-200',
                            default => 'text-gray-700 bg-gray-50 border-gray-300 shadow-sm',
                        };

                        $progressBarHex = match ($displayStat) {
                            'In Progress' => '#10B981',
                            'Completed' => '#3B82F6',
                            'Pending Review', 'Rejected' => '#F59E0B',
                            'Archived' => '#6B7280',
                            default => '#9CA3AF',
                        };

                        $progressTextColor = match ($displayStat) {
                            'In Progress' => 'text-emerald-600',
                            'Completed' => 'text-blue-600',
                            'Pending Review', 'Rejected' => 'text-amber-600',
                            'Archived' => 'text-gray-500',
                            default => 'text-on-surface-variant',
                        };

                        $contextLine = match ($displayStat) {
                            'Private' => 'Belum diajukan ke admin',
                            'Pending Review' => 'Menunggu review admin',
                            'Rejected' => 'Perlu revisi segera',
                            'Completed' => 'Course telah selesai',
                            'Archived' => 'Course telah diarsipkan',
                            default => 'Terakhir diperbarui: ' . e($course['updated_at'] ?? 'Baru saja'),
                        };

                        $ctaText = match ($displayStat) {
                            'Private' => 'Edit Course',
                            'Pending Review' => 'Lihat Detail',
                            'Rejected' => 'Perbaiki Course',
                            'In Progress' => 'Kelola Penilaian',
                            'Completed' => 'Lihat Detail',
                            'Archived' => 'Lihat Arsip',
                            default => 'Detail',
                        };

                        $ctaHref = match ($displayStat) {
                            'Private', 'Rejected' => '?route=/teacher/course/create&course_id=' .
                                e((string) $course['id']),
                            'In Progress' => '?route=/teacher/grade&course_id=' . e((string) $course['id']),
                            default => '?route=/teacher/course/detail&course_id=' . e((string) $course['id']),
                        };

                        $ctaCls = match ($displayStat) {
                            'Private' => 'text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400',
                            'Pending Review'
                                => 'text-amber-700 border-amber-300 hover:bg-amber-50 hover:border-amber-400',
                            'Rejected' => 'text-red-700 border-red-300 hover:bg-red-50 hover:border-red-400',
                            'In Progress'
                                => 'text-emerald-700 border-emerald-300 hover:bg-emerald-50 hover:border-emerald-400',
                            'Completed' => 'text-blue-700 border-blue-300 hover:bg-blue-50 hover:border-blue-400',
                            'Archived' => 'text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300',
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
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-white text-error h-14 px-5 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.12)] border border-error/50 flex items-center gap-2 hover:bg-error/5 hover:border-error transition-all duration-200 active:scale-95 lg:hidden box-border">
        <span class="material-symbols-outlined text-[24px]">add</span>
        <span class="font-label-md font-bold">Buat</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
