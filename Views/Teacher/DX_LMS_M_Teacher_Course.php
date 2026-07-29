<?php
/**
 * Tampilan Daftar Course Pengajar (Teacher Course List).
 *
 * Menampilkan rekomendasi dan memonitor lifecycle course.
 */
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

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'Kelola Course';
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header Extension (Only visible on lg) -->
    <div class="hidden lg:block lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md relative">
        <div class="flex justify-between items-center max-w-5xl mx-auto">
            <h1 class="font-headline-6 font-bold text-on-surface">Kelola Course</h1>
            <a href="?route=/teacher/course/create" class="bg-primary text-on-primary h-10 px-4 rounded-xl shadow-sm text-label-md font-bold flex items-center gap-2 hover:bg-primary-600 transition-colors">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Buat Course Baru
            </a>
        </div>
    </div>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-4">
        
        <section class="max-w-5xl mx-auto space-y-stack-md">
            
            <!-- Mobile View Intro / Create Button -->
            <div class="lg:hidden flex flex-col gap-4 mb-4">
                <div class="flex flex-col gap-1">
                    <h2 class="font-headline-sm font-bold text-on-surface">Course Saya</h2>
                    <p class="font-body-sm text-on-surface-variant">Buat materi dan usulkan rekomendasi target peserta.</p>
                </div>
                <!-- Button moved to FAB -->
            </div>

            <!-- Filter Tabs -->
            <div class="relative w-full -mx-container-margin px-container-margin md:mx-0 md:px-0 mb-4 sticky lg:relative top-14 lg:top-0 bg-surface z-20 py-2">
                <div class="flex gap-2 overflow-x-auto no-scrollbar snap-x py-1">
                    <?php
                    $tabsInfo = [
                        ['id' => 'all', 'label' => 'Semua'],
                        ['id' => 'Draft', 'label' => 'Draft'],
                        ['id' => 'Submitted', 'label' => 'Submitted'],
                        ['id' => 'Approved', 'label' => 'Approved'],
                        ['id' => 'Running', 'label' => 'Running'],
                        ['id' => 'Completed', 'label' => 'Completed'],
                    ];

                    foreach ($tabsInfo as $tab):

                        $isActive = strtolower($current_filter) === strtolower($tab['id']);
                        $baseClass =
                            'snap-start whitespace-nowrap px-4 py-2 rounded-full font-label-md transition-all active:scale-95 border ';
                        $activeClass = 'bg-primary-container text-on-primary-container border-primary font-bold';
                        $inactiveClass =
                            'bg-surface text-on-surface-variant hover:bg-surface-variant border-outline-variant hover:border-outline';
                        ?>
                        <a href="?route=/teacher/courses&status=<?= e($tab['id']) ?>" 
                           class="<?= $baseClass ?> <?= $isActive ? $activeClass : $inactiveClass ?>">
                            <?= e($tab['label']) ?>
                        </a>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- Course Grid -->
            <?php if (empty($courses)): ?>
                <div class="flex flex-col items-center justify-center p-8 bg-surface-container-lowest rounded-xl border border-dashed border-outline text-center h-[300px]">
                    <span class="material-symbols-outlined text-[64px] text-surface-variant mb-4">search_off</span>
                    <h3 class="font-headline-sm font-bold text-on-surface mb-2">Tidak Ada Course</h3>
                    <p class="font-body-sm text-on-surface-variant max-w-sm mb-6">Course dengan status filter tersebut tidak ditemukan atau Anda belum membuat course apapun.</p>
                    <a href="?route=/teacher/course/create" class="text-primary font-label-md font-bold px-4 py-2 bg-primary-container rounded-lg hover:brightness-95 transition-all">
                        Buat Course Baru
                    </a>
                </div>
            <?php
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Extract exactly mapped UI labels
                // We assume it's one of the 6 exact status mappings

                // Badge styling
                // Private

                // Progress bar & text colors

                // Context line — status-aware

                // CTA — Status-aware actions
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Extract exactly mapped UI labels
                // We assume it's one of the 6 exact status mappings
                // Badge styling
                // Private
                // Progress bar & text colors
                // Context line — status-aware
                // CTA — Status-aware actions
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Extract exactly mapped UI labels
                // We assume it's one of the 6 exact status mappings

                // Badge styling
                // Private

                // Progress bar & text colors

                // Context line — status-aware

                // CTA — Status-aware actions
                // Lifecycle styling logic
                // Lifecycle styling logic
                // Extract exactly mapped UI labels
                // We assume it's one of the 6 exact status mappings
                // Badge styling
                // Private
                // Progress bar & text colors
                // Context line — status-aware
                // CTA — Status-aware actions
                else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-gutter lg:gap-stack-lg pb-4">
                    <?php foreach ($courses as $course): ?>
                        <?php
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
                                $badgeColor = 'bg-surface-container text-on-surface-variant line-through opacity-70';
                                $borderStyle = 'border-surface-container-high';
                                break;
                        }
                        ?>
                        <a href="?route=/teacher/course/detail&course_id=<?= e((string) $course['id']) ?>" 
                           class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border <?= $borderStyle ?> overflow-hidden flex flex-col group hover:-translate-y-1 transition-all duration-300 relative h-full">
                            
                            <!-- Gradient Header area -->
                            <div class="h-[120px] bg-gradient-to-br <?= $gradientFrom ?> to-surface relative p-4 flex justify-between items-start">
                                <div class="w-12 h-12 bg-surface rounded-xl shadow-sm border border-outline-variant/30 flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined text-[28px] text-on-surface" style="font-variation-settings: 'FILL' 1;"><?= e(
                                        $course['icon'] ?? 'menu_book',
                                    ) ?></span>
                                </div>
                                
                                <div class="<?= $badgeColor ?> px-2 py-0.5 rounded-full font-label-sm text-[11px] font-medium border border-outline/10 shadow-sm relative z-10 flex items-center gap-1">
                                    <?= e($course['status'] ?? 'Draft') ?>
                                </div>
                            </div>
                            
                            <div class="p-4 flex-1 flex flex-col -mt-4 relative z-10 bg-surface rounded-t-2xl shadow-[0_-8px_16px_0_rgba(0,0,0,0.02)] border-t border-surface-variant">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-label-sm text-xs bg-secondary-container/30 text-secondary px-1.5 py-0.5 rounded-md">
                                        <?= e($course['code']) ?>
                                    </span>
                                    <span class="font-label-sm text-xs bg-surface-container text-on-surface-variant px-1.5 py-0.5 rounded-md">
                                        <?= e($course['category'] ?? 'Umum') ?>
                                    </span>
                                </div>
                                <h4 class="font-title-md font-semibold text-on-surface mb-2 line-clamp-2 md:line-clamp-1 group-hover:text-primary transition-colors"><?= e(
                                    $course['title'],
                                ) ?></h4>
                                <p class="font-body-sm text-on-surface-variant line-clamp-2 mb-4">
                                    <?= e($course['description'] ?? 'Tidak ada deskripsi.') ?>
                                </p>
                                
                                <div class="flex items-center justify-between text-on-surface-variant mt-auto text-label-sm pt-4 border-t border-outline-variant/30">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">record_voice_over</span>
                                        <span>Peserta: <?= e((string) ($course['participants'] ?? '0')) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[11px]">
                                        <span>Update: <?= e($course['updated_at'] ?? 'Baru') ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-primary text-on-primary h-14 px-5 rounded-2xl shadow-lg flex items-center gap-2 hover:bg-primary-600 transition-all duration-200 active:scale-95 lg:hidden">
        <span class="material-symbols-outlined text-[24px]">add</span>
        <span class="font-label-md font-bold">Buat</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
