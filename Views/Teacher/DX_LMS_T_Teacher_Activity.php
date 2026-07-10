<?php
/**
 * Tampilan Aktivitas Pengajar (Teacher Activity).
 *
 * Sesuai desain referensi aktivitas_teacher_lms_denso/code.html
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <?php
    $page_title = 'Teacher Activity - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { background-color: #fcf9f8; } /* surface */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>
<body class="bg-surface text-on-surface antialiased flex flex-col min-h-screen">
    <!-- TopAppBar -->
    <header class="fixed top-0 w-full bg-surface z-50 border-b border-outline-variant flex justify-between items-center px-container-margin h-14 transition-all duration-200">
        <div class="flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full cursor-pointer active:scale-95">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0; color: rgb(226, 6, 19);">business</span>
        </div>
        <h1 class="font-headline-md text-headline-md font-bold text-primary tracking-tight">LMS</h1>
        <div class="flex items-center gap-2 text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full cursor-pointer active:scale-95">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0; color: rgb(226, 6, 19);">notifications</span>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-20 pb-24 px-container-margin md:max-w-2xl md:mx-auto w-full">
        <!-- Summary Cards Section -->
        <section class="grid grid-cols-2 gap-4 mb-stack-lg">
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-full bg-secondary-container/20 flex items-center justify-center mb-2">
                    <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">group</span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-1">85</h2>
                <p class="font-label-sm text-label-sm text-on-surface-variant">Peserta Aktif</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-full bg-primary-container/10 flex items-center justify-center mb-2">
                    <span class="material-symbols-outlined text-primary-container" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-1">12</h2>
                <p class="font-label-sm text-label-sm text-on-surface-variant">Total Course</p>
            </div>
        </section>

        <!-- Activity Feed Section -->
        <section>
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-stack-md">Aktivitas Terbaru</h3>
            <div class="flex flex-col gap-3">
                <?php foreach ($activities as $act): 
                    // Map icon color slightly differently based on provided color, or hardcode styles as in html
                    $bg_color = 'bg-surface-container-high'; // default
                    $icon_color = 'text-on-surface';
                    if ($act['icon'] === 'check_circle' || $act['icon'] === 'task_alt') {
                        $bg_color = 'bg-tertiary-container/20';
                        $icon_color = 'text-tertiary';
                    } elseif ($act['icon'] === 'play_circle') {
                        $bg_color = 'bg-secondary-container/20';
                        $icon_color = 'text-secondary';
                    } elseif ($act['icon'] === 'forum') {
                        $bg_color = 'bg-[#fce0cc]';
                        $icon_color = 'text-[#d96616]';
                    } else {
                        $bg_color = 'bg-secondary-container/20';
                        $icon_color = 'text-secondary';
                    }
                ?>
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] flex gap-3 items-start">
                    <div class="mt-1 w-8 h-8 rounded-full <?= $bg_color ?> flex-shrink-0 flex items-center justify-center">
                        <span class="material-symbols-outlined <?= $icon_color ?> text-[18px]" style="font-variation-settings: 'FILL' 1;"><?= e($act['icon']) ?></span>
                    </div>
                    <div class="flex-grow">
                        <p class="font-body-md text-body-md text-on-surface"><span class="font-bold"><?= e($act['student_name']) ?></span> <?= e($act['action']) ?></p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1"><?= e($act['target']) ?></p>
                        <p class="font-label-sm text-label-sm text-outline mt-2"><?= e($act['time']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php 
    $active_tab = 'aktivitas';
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>
</body>
</html>
