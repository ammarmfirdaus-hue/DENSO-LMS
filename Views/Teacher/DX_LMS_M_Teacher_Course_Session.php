<?php
/**
 * Tampilan Sesi Course Pengajar (Teacher Course Session).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Sesi Materi - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'course'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Kelola Sesi Course';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[72px]">
        <div class="max-w-4xl mx-auto space-y-stack-xl">
            <!-- Header Section -->
            <section class="flex flex-col gap-1 mb-6">
                <h2 class="font-headline-sm font-bold text-on-surface">Daftar Sesi Pembelajaran</h2>
                <p class="font-body-sm text-on-surface-variant">Kelola sesi materi dan tahapan kurikulum untuk kursus ini.</p>
            </section>

            <!-- Sessions List (Bento Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
                <?php foreach ($sessions ?? [] as $session): ?>
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant flex flex-col gap-3 group">
                        <div class="flex justify-between items-start">
                            <div class="flex flex-col gap-1">
                                <span class="font-label-sm text-xs bg-secondary-container/20 text-secondary px-2 py-0.5 rounded-md inline-block w-max">Course ID <?= e(
                                    $courseId ?? '1',
                                ) ?></span>
                                <h3 class="font-title-md font-bold text-on-surface group-hover:text-primary transition-colors"><?= e(
                                    $session['title'] ?? 'Sesi -',
                                ) ?></h3>
                            </div>
                            <button class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-full hover:bg-surface-container-low">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                        </div>
                        <div class="flex flex-col gap-2 mt-2">
                            <div class="flex items-center gap-2 text-on-surface-variant font-body-sm">
                                <span class="material-symbols-outlined text-[18px]">description</span>
                                <span><?= e((string) ($session['materials_count'] ?? '0')) ?> Materi</span>
                            </div>
                            <div class="flex items-center gap-2 text-on-surface-variant font-body-sm">
                                <span class="material-symbols-outlined text-[18px]">assignment</span>
                                <span><?= e((string) ($session['quiz_count'] ?? '0')) ?> Kuis</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-surface-variant flex justify-between items-center gap-2">
                            <span class="font-label-sm text-[11px] text-tertiary bg-tertiary-container/10 px-2 py-1 rounded-full flex items-center gap-1">
                                Aktif
                            </span>
                            
                            <div class="flex gap-2">
                                <a href="?route=/teacher/forum&course_id=<?= urlencode(
                                    $courseId ?? '',
                                ) ?>&session_id=<?= urlencode(
    (string) ($session['id'] ?? ''),
) ?>" class="border border-outline-variant text-on-surface font-label-md py-2 px-3 hover:bg-surface-container transition-colors rounded-lg flex items-center gap-1 text-[13px]">
                                    <span class="material-symbols-outlined text-[16px]">forum</span> Forum
                                </a>
                                <a href="?route=/teacher/course/material&session_id=<?= urlencode(
                                    (string) ($session['id'] ?? ''),
                                ) ?>" class="bg-primary text-on-primary font-label-md font-bold py-2 px-3 hover:bg-primary-600 shadow-sm transition-colors rounded-lg flex items-center gap-1 text-[13px]">
                                    Kelola Materi
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($sessions)): ?>
                    <!-- Empty State Dummy -->
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant flex flex-col gap-3 group">
                        <div class="flex flex-col gap-1">
                            <span class="font-label-sm text-xs bg-secondary-container/20 text-secondary px-2 py-0.5 rounded-md inline-block w-max">Course ID 1</span>
                            <h3 class="font-title-md font-bold text-on-surface group-hover:text-primary transition-colors">Sesi 1: Dasar Materi</h3>
                        </div>
                        <div class="flex flex-col gap-2 mt-2">
                            <div class="flex items-center gap-2 text-on-surface-variant font-body-sm">
                                <span class="material-symbols-outlined text-[18px]">description</span>
                                <span>2 Materi</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-surface-variant flex justify-end gap-2">
                            <a href="?route=/teacher/course/material" class="bg-primary text-on-primary font-label-md font-bold py-2 px-3 hover:bg-primary-600 shadow-sm transition-colors rounded-lg flex items-center gap-1 text-[13px]">
                                Kelola Materi
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex justify-end pt-4">
                <button class="bg-secondary text-on-secondary font-label-md py-2.5 px-6 rounded-lg font-bold transition-all shadow-sm hover:bg-secondary-600 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span> Buat Sesi Baru
                </button>
            </div>
        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>

</body>
</html>
