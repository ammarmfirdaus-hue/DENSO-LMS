<?php
/**
 * Tampilan Daftar Course Pengajar (Teacher Course List View).
 *
 * Halaman untuk melihat semua course yang diampu.
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">

<head>
    <?php
    $page_title = 'My Course (Teacher) - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>

<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
    <?php
    $header_title = 'Kelola Course';
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <main class="dls-main flex-1 overflow-y-auto space-y-stack-lg pt-[72px] px-container-margin pb-[88px]">
        <section class="space-y-stack-sm min-w-0">
            <h1 class="font-headline-md font-bold text-on-surface">Daftar Course</h1>
            <p class="font-body-md text-on-surface-variant">Kelola sesi, materi, dan evaluasi course Anda.</p>
        </section>

        <!-- Search Bar Dummy -->
        <section class="mt-4">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                <input type="text" placeholder="Cari course..." class="w-full bg-surface-container-lowest border border-outline-variant rounded-full py-2.5 pl-10 pr-4 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary font-body-md">
            </div>
        </section>

        <section class="space-y-stack-md mt-6">
            <?php if (empty($courses)): ?>
                <div class="text-center py-8 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2">inbox</span>
                    <p class="font-body-md">Belum ada course yang Anda ampu.</p>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <div class="bg-surface-container-lowest border border-surface-variant rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] flex flex-col gap-3">
                        <div class="flex items-start justify-between min-w-0">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-label-sm text-label-sm px-2 py-0.5 rounded-sm bg-secondary-container text-on-secondary-container">
                                        <?= e($course['code'] ?? 'KODE') ?>
                                    </span>
                                </div>
                                <h3 class="font-title-md text-title-md font-semibold text-on-surface line-clamp-2">
                                    <?= e($course['title'] ?? 'Judul Course') ?>
                                </h3>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-2 mt-auto border-t border-outline-variant/30">
                            <a href="?route=/teacher/course/session&id=<?= urlencode($course['id'] ?? '') ?>" class="font-label-md text-on-primary bg-primary px-4 py-2 rounded-full flex items-center hover:bg-primary-600 transition-colors w-full justify-center shadow-sm">
                                <span class="material-symbols-outlined text-[18px] mr-2">settings</span> Kelola Course
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
