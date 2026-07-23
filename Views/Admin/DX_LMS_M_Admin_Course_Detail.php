<?php
/**
 * Tampilan Detail Course (Admin MVP Stage 2 Drill-Down).
 */
$header_title = 'Course Hub';
$active_tab = 'course';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Detail Course - ' . e($course['title']);
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
        
        <div class="mb-stack-lg">
            <a href="javascript:history.back()" class="text-secondary font-label-md hover:underline flex items-center gap-1 mb-2">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
            </a>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div>
                    <div class="flex gap-2 items-center mb-2">
                        <span class="bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded-sm font-label-sm text-[10px] tracking-wide uppercase"><?= e(
                            $course['status'],
                        ) ?></span>
                        <span class="font-label-sm text-on-surface-variant font-medium">Course ID-<?= e(
                            $course['id'],
                        ) ?></span>
                    </div>
                    <h1 class="font-headline-lg font-bold text-on-surface cursor-pointer"><?= e(
                        $course['title'],
                    ) ?></h1>
                    <p class="font-body-md text-on-surface-variant max-w-2xl mt-2"><?= e($course['description']) ?></p>
                </div>
                
                <div class="flex gap-2">
                    <button class="bg-surface hover:bg-surface-variant border border-outline-variant font-label-md px-4 py-2 rounded-lg transition-colors flex items-center gap-2" onclick="window.location.href='?route=/admin/score'">
                        <span class="material-symbols-outlined text-[18px]">fact_check</span> Kelola Nilai
                    </button>
                    <button class="bg-primary text-on-primary font-label-md px-4 py-2 rounded-lg hover:shadow-md transition-shadow">
                        Edit Course
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-safe">
            <!-- Left Panel: General Info -->
            <div class="lg:col-span-2 space-y-gutter">
                <!-- Syllabus Section -->
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant">
                    <h2 class="font-headline-sm font-bold text-on-surface mb-stack-md">Struktur Silabus (<?= e(
                        $course['sessions'],
                    ) ?> Sesi)</h2>
                    <ul class="space-y-3">
                        <?php foreach ($course['syllabus'] as $syl): ?>
                            <li class="flex items-center gap-3 p-3 rounded-lg border border-surface-variant bg-surface group hover:border-primary/50 transition-colors">
                                <div class="w-10 h-10 bg-primary-container text-on-primary-container rounded-lg flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined"><?= $syl['type'] === 'Video'
                                        ? 'play_circle'
                                        : ($syl['type'] === 'Quiz'
                                            ? 'quiz'
                                            : 'menu_book') ?></span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-label-md font-bold text-on-surface"><?= e($syl['title']) ?></h3>
                                    <p class="font-label-sm text-on-surface-variant"><?= e($syl['type']) ?> • <?= e(
     $syl['duration'],
 ) ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Right Panel: People Context -->
            <div class="space-y-gutter">
                <!-- Quick Stats -->
                <div class="bg-surface p-stack-md rounded-xl border border-outline-variant shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                    <p class="font-label-sm text-on-surface-variant uppercase tracking-wider mb-2">Penanggung Jawab</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-secondary/10 rounded-full flex items-center justify-center text-secondary font-bold"><?= substr(
                            e($course['teacher']),
                            0,
                            1,
                        ) ?></div>
                        <div>
                            <p class="font-label-md font-bold text-on-surface"><?= e($course['teacher']) ?></p>
                            <p class="font-body-sm opacity-80">Teacher</p>
                        </div>
                    </div>
                </div>

                <!-- Peserta Alert Section -->
                <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant">
                    <h2 class="font-headline-sm font-bold text-on-surface mb-stack-md">Pemantauan Peserta</h2>
                    <div class="mb-4">
                        <span class="text-[32px] font-bold text-on-surface"><?= $course['participants'] ?></span>
                        <span class="font-body-sm text-on-surface-variant">Siswa Terdaftar</span>
                    </div>
                    
                    <!-- Top Student Snippets -->
                    <div class="mb-4">
                        <p class="font-label-sm font-bold text-on-surface-variant mb-2">Peserta Unggulan:</p>
                        <ul class="space-y-2">
                            <?php foreach ($course['top_students'] as $stu): ?>
                            <li class="flex justify-between items-center hover:bg-surface-variant p-2 -mx-2 rounded-lg cursor-pointer transition-colors" onclick="window.location.href='?route=/admin/user/detail&id=<?= $stu[
                                'user_id'
                            ] ?>'">
                                <span class="font-body-sm font-medium text-on-surface text-primary hover:underline"><?= e(
                                    $stu['name'],
                                ) ?></span>
                                <span class="font-label-sm text-success font-bold"><?= $stu['progress'] ?>%</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Lagging Alert Snippets -->
                    <div>
                        <p class="font-label-sm font-bold text-error mb-2">Butuh Perhatian (Lagging):</p>
                        <ul class="space-y-2">
                            <?php foreach ($course['lagging_students'] as $lag): ?>
                            <li class="flex justify-between items-center hover:bg-error/5 p-2 -mx-2 rounded-lg cursor-pointer transition-colors" onclick="window.location.href='?route=/admin/user/detail&id=<?= $lag[
                                'user_id'
                            ] ?>'">
                                <div>
                                    <span class="font-body-sm font-medium text-on-surface text-primary hover:underline block"><?= e(
                                        $lag['name'],
                                    ) ?></span>
                                    <span class="font-label-sm text-on-surface-variant/80 text-[10px]"><?= e(
                                        $lag['lag_reason'],
                                    ) ?></span>
                                </div>
                                <span class="font-label-sm bg-error text-on-error px-2 py-0.5 rounded-full font-bold"><?= $lag[
                                    'progress'
                                ] ?>%</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>

    </main>
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>
</body>
</html>
