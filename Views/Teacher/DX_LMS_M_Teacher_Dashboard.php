<?php
/**
 * Tampilan Dashboard Pengajar (Teacher Dashboard).
 *
 * Mengacu persis pada folder dashboard_teacher_integrated_role_switcher.
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <?php
    $page_title = 'Dashboard Teacher - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>
<body class="bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans pb-[84px]">
    <!-- Status Bar iOS -->
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin h-14 sticky z-40 transition-all duration-200 top-0">
        <button class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-all duration-200 active:scale-95 flex items-center justify-center">
            <span class="material-symbols-outlined text-[28px]">business</span>
        </button>
        <h1 class="font-headline-md text-headline-md font-bold text-primary tracking-tight">LMS</h1>
        <button class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-all duration-200 active:scale-95 flex items-center justify-center">
            <span class="material-symbols-outlined text-[28px]">notifications</span>
        </button>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-1 flex flex-col w-full max-w-3xl mx-auto pb-safe">
        <!-- Header Section -->
        <section class="px-container-margin pb-stack-md flex flex-col gap-stack-sm pt-4">
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Selamat pagi, <?= e($_SESSION['user']['fullname'] ?? 'Budi Santoso') ?></h2>
            <p class="font-body-md text-body-md text-on-surface-variant"><?= e($_SESSION['user']['jabatan'] ?? 'Leader') ?> • <?= e($_SESSION['user']['department'] ?? 'Departemen Produksi') ?></p>
        </section>

        <!-- Role Toggle Pills -->
        <section class="px-container-margin flex justify-center py-2">
            <div class="flex bg-surface-container-low rounded-full p-1 shadow-sm w-fit">
                <a href="?route=/auth/switch-role&role=teacher" class="px-6 py-2 rounded-full bg-secondary text-on-secondary font-label-md text-label-md text-center shadow-sm transition-all cursor-pointer">
                    Teacher
                </a>
                <a href="?route=/auth/switch-role&role=student" class="px-6 py-2 rounded-full text-on-surface-variant font-label-md text-label-md text-center transition-all cursor-pointer hover:bg-surface-container-highest">
                    Student
                </a>
            </div>
        </section>

        <!-- Summary Metrics Bento Grid -->
        <section class="px-container-margin grid grid-cols-2 gap-gutter py-2">
            <!-- Metric 1 -->
            <div class="bg-surface-container-lowest p-4 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] flex flex-col gap-2">
                <div class="w-8 h-8 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[20px]">school</span>
                </div>
                <div>
                    <div class="font-headline-md text-headline-md text-on-surface"><?= e((string)($stats['total_course'] ?? 0)) ?></div>
                    <div class="font-label-sm text-label-sm text-on-surface-variant">Course Diampu</div>
                </div>
            </div>
            <!-- Metric 2 -->
            <div class="bg-surface-container-lowest p-4 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] flex flex-col gap-2">
                <div class="w-8 h-8 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                </div>
                <div>
                    <div class="font-headline-md text-headline-md text-on-surface"><?= e((string)($stats['total_student'] ?? 0)) ?></div>
                    <div class="font-label-sm text-label-sm text-on-surface-variant">Peserta</div>
                </div>
            </div>
        </section>

        <!-- Managed Courses Section -->
        <section class="px-container-margin flex flex-col gap-stack-md py-2">
            <div class="flex justify-between items-center">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Course yang Anda Kelola</h3>
                <a href="?route=/teacher/courses" class="text-secondary font-label-md text-label-md hover:underline">Lihat Semua</a>
            </div>

            <?php foreach ($courses as $course): ?>
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] overflow-hidden flex flex-col active:scale-[0.98] transition-transform duration-200">
                    <div class="h-32 bg-surface-container-high relative w-full" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDQb7UJk2i2CM8OC2bpNgql4EOCePBNc1iDn2_0vRQWzwkqu-MvoOkcrNxLlRZJ6hu_wpsk5Q2LPDUiYJtga5CEtG7HifiaFjwCjYpwccSm_o_Dbhs_wMSQGyOczze704u_J6w5NFvCBmfBqUL2L9AEXD6VYc8CnToIt9g1GNREdVdQnNdXPZ4a2KOG6IQYRjyI4oLLais9yvSdF3YgGfYdP93mYSwsO5MSDlfXWj0BNONjTvErzXEQZhuPqO-lsQqPJ_QaVdimrvg'); background-size: cover; background-position: center center;">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <?php if ($course['status'] === 'active'): ?>
                            <div class="absolute bottom-3 left-3 bg-tertiary-container text-on-tertiary px-2 py-1 rounded-md font-label-sm text-label-sm flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Aktif
                            </div>
                        <?php else: ?>
                            <div class="absolute bottom-3 left-3 bg-surface-container border border-outline-variant text-on-surface px-2 py-1 rounded-md font-label-sm text-label-sm flex items-center gap-1">
                                Draft
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex flex-col gap-3">
                        <h4 class="font-headline-sm text-headline-sm text-on-surface"><?= e($course['title']) ?></h4>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5 text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">groups</span>
                                <span class="font-label-md text-label-md"><?= e((string)($course['participants'] ?? '0')) ?> Peserta</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">update</span>
                                <span class="font-label-md text-label-md">Diperbarui <?= e($course['updated_at'] ?? 'Baru saja') ?></span>
                            </div>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <?php if ($course['status'] === 'active'): ?>
                                <a href="?route=/teacher/grade&course_id=<?= e((string)$course['id']) ?>" class="flex-1 bg-surface-container border border-outline-variant text-on-surface py-2 rounded-lg font-label-md text-label-md font-semibold text-center hover:bg-surface-container-high transition-colors">
                                    Kelola Nilai
                                </a>
                                <a href="?route=/teacher/course/session&course_id=<?= e((string)$course['id']) ?>" class="flex-1 bg-secondary text-on-secondary py-2 rounded-lg font-label-md text-label-md font-semibold text-center hover:bg-secondary-container hover:text-on-secondary-container transition-colors shadow-[0_2px_8px_0_rgba(67,85,185,0.2)]">
                                    Materi
                                </a>
                            <?php else: ?>
                                <button class="flex-1 bg-surface-container border border-outline-variant text-on-surface py-2 rounded-lg font-label-md text-label-md font-semibold text-center hover:bg-surface-container-high transition-colors">
                                    Edit Draft
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- Floating Action Button (FAB) -->
    <button class="fixed bottom-24 right-4 z-40 bg-secondary text-on-secondary h-14 px-5 rounded-2xl shadow-[0_4px_12px_0_rgba(67,85,185,0.3)] flex items-center gap-2 hover:bg-secondary-container hover:text-on-secondary-container transition-all duration-200 active:scale-95 md:right-8">
        <span class="material-symbols-outlined text-[24px]">add</span>
        <span class="font-label-md text-label-md font-semibold">Tambah Materi</span>
    </button>

    <?php 
    $active_tab = 'beranda';
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>
</body>
</html>
