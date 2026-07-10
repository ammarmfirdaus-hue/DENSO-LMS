<?php
/**
 * Tampilan Sesi Course Pengajar (Teacher Course Session).
 *
 * Sesuai desain referensi kelola_sesi_course_dengan_akses_forum_teacher/code.html
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <?php
    $page_title = 'Sesi Materi - LMS Learning System';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(884px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased pb-20">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin h-14 sticky top-0 z-40 transition-all duration-200">
        <div class="flex items-center gap-4">
            <button onclick="history.back()" class="text-primary hover:bg-surface-container-low p-1 rounded-full transition-all duration-200 active:scale-95 flex items-center justify-center">
                <span class="material-symbols-outlined" style="font-size: 32px;">arrow_back</span>
            </button>
        </div>
        <div class="absolute left-1/2 -translate-x-1/2">
            <span class="text-primary font-bold text-headline-md tracking-widest">LMS</span>
        </div>
        <button class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-all duration-200 active:scale-95 flex items-center justify-center relative">
            <span class="material-symbols-outlined" style="font-size: 32px;">notifications</span>
            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
        </button>
    </header>

    <!-- Main Content -->
    <main class="flex-grow px-container-margin py-stack-lg flex flex-col gap-stack-lg max-w-3xl mx-auto w-full">
        <!-- Header Section -->
        <section class="flex flex-col gap-stack-sm">
            <h2 class="font-headline-sm text-headline-sm text-on-surface text-center">Kelola Sesi Course</h2>
            <p class="font-body-md text-body-md text-on-surface-variant text-center">Kelola sesi materi untuk kursus yang Anda ampu.</p>
        </section>

        <!-- Sessions List (Bento Grid Style) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
            <?php foreach ($sessions as $session): ?>
                <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container flex flex-col gap-stack-md hover:shadow-md transition-shadow duration-300 group">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-1">
                            <span class="font-label-sm text-label-sm text-secondary bg-secondary-container/20 px-2 py-1 rounded-md inline-block w-max">Course ID <?= e($courseId) ?></span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface group-hover:text-primary transition-colors duration-200"><?= e($session['title']) ?></h3>
                        </div>
                        <button class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-full hover:bg-surface-container-low">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-on-surface-variant font-body-md text-body-md">
                            <span class="material-symbols-outlined" style="font-size: 18px;">description</span>
                            <span><?= e((string)$session['materials_count']) ?> Materi</span>
                        </div>
                        <div class="flex items-center gap-2 text-on-surface-variant font-body-md text-body-md">
                            <span class="material-symbols-outlined" style="font-size: 18px;">assignment</span>
                            <span><?= e((string)$session['quiz_count']) ?> Kuis Terjadwal</span>
                        </div>
                    </div>
                    <div class="mt-auto pt-stack-sm border-t border-surface-container flex justify-between items-center">
                        <?php if (true): // active status dummy ?>
                            <span class="font-label-sm text-label-sm text-tertiary bg-tertiary-container/10 px-2 py-1 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span> Aktif
                            </span>
                        <?php else: ?>
                            <span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container-high px-2 py-1 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-on-surface-variant"></span> Draf
                            </span>
                        <?php endif; ?>
                        
                        <div class="flex gap-2">
                            <a href="?route=/teacher/forum&course_id=<?= urlencode($courseId) ?>&session_id=<?= urlencode((string)$session['id']) ?>" class="border border-primary text-primary hover:bg-primary/5 font-label-md text-label-md py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 16px;">forum</span> Forum
                            </a>
                            <a href="?route=/teacher/course/material&session_id=<?= urlencode((string)$session['id']) ?>" class="bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                                Kelola Materi <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add New FAB -->
        <div class="flex justify-end mt-stack-md">
            <button class="bg-secondary hover:bg-secondary-fixed-dim text-on-secondary font-label-md text-label-md py-3 px-6 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2 transform active:scale-95">
                <span class="material-symbols-outlined">add</span> Buat Sesi Baru
            </button>
        </div>
    </main>

    <?php 
    $active_tab = 'course';
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>
</body>
</html>
