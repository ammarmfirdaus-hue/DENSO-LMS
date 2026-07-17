<?php
/**
 * Tampilan Manajemen Course Admin DLS (DLS Admin Course View).
 */

$header_title = 'Kelola Course';
$active_tab = 'course';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Course LMS';
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

    <main class="dls-main flex-1 overflow-y-auto pt-[72px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Data Course & Pelatihan</h1>
                <p class="font-body-md text-on-surface-variant">Manajemen materi, pelacakan sesi, dan pengaturan publikasi.</p>
            </div>
            
            <button class="bg-primary text-on-primary font-label-md px-4 py-2.5 rounded-lg active:scale-95 transition-transform hover:shadow-md flex items-center gap-2" onclick="alert('Fitur tambah course muncul!')">
                <span class="material-symbols-outlined">add_box</span> Buat Course Baru
            </button>
        </div>

        <!-- Filter Tools (Dummy UI) -->
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm border border-surface-variant mb-stack-md flex gap-2 overflow-x-auto flex-nowrap">
            <button class="border border-outline-variant bg-surface px-3 py-1.5 rounded-lg font-label-sm flex items-center gap-2 hover:bg-surface-variant min-w-max">
                <span class="material-symbols-outlined text-[18px]">verified</span> Status
            </button>
            <button class="border border-outline-variant bg-surface px-3 py-1.5 rounded-lg font-label-sm flex items-center gap-2 hover:bg-surface-variant min-w-max">
                <span class="material-symbols-outlined text-[18px]">sell</span> Tags
            </button>
            <button class="border border-outline-variant bg-surface px-3 py-1.5 rounded-lg font-label-sm flex items-center gap-2 hover:bg-surface-variant min-w-max">
                <span class="material-symbols-outlined text-[18px]">person</span> Teacher
            </button>
            <div class="border-l border-outline-variant mx-1"></div>
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute top-1.5 left-2 text-on-surface-variant text-[20px]">search</span>
                <input type="text" placeholder="Cari course..." class="w-full bg-surface pl-9 pr-3 py-1.5 rounded-lg border border-outline-variant font-body-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary h-[34px]">
            </div>
        </div>

        <!-- Course Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-gutter mb-safe">
            <?php foreach ($courses as $c): ?>
                <?php
                $iconGradients = [
                    'build' => 'from-orange-400 to-orange-600',
                    'health_and_safety' => 'from-emerald-400 to-green-600',
                    'verified' => 'from-sky-400 to-indigo-600',
                    'eco' => 'from-lime-400 to-green-600',
                    'school' => 'from-primary/70 to-primary',
                ];
                $icon = $c['thumbnail_icon'] ?? 'school';
                $grad = $iconGradients[$icon] ?? 'from-primary/70 to-primary';
                ?>
                <div class="flex flex-col h-full bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden hover:shadow-md transition-shadow group">
                    <div class="h-32 relative bg-gradient-to-br <?= $grad ?> flex items-center justify-center">
                        <span class="material-symbols-outlined text-white/80" style="font-size: 56px; font-variation-settings: 'FILL' 1;"><?= e(
                            $icon,
                        ) ?></span>
                        
                        <?php if ($c['status'] === 'Verified' || $c['status'] === 'Published'): ?>
                        <div class="absolute top-2 right-2 bg-success text-on-success px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm flex gap-1 items-center">
                            <span class="material-symbols-outlined text-[14px]">public</span> <?= e($c['status']) ?>
                        </div>
                        <?php elseif ($c['status'] === 'Completed'): ?>
                        <div class="absolute top-2 right-2 bg-secondary text-white px-2 py-1 rounded-sm font-label-sm shadow-sm flex gap-1 items-center">
                            <span class="material-symbols-outlined text-[14px]">archive</span> Arsip
                        </div>
                        <?php else: ?>
                        <div class="absolute top-2 right-2 bg-warning text-on-warning px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm flex gap-1 items-center">
                            <span class="material-symbols-outlined text-[14px]">edit_document</span> <?= e(
                                $c['status'],
                            ) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-stack-md space-y-stack-sm flex flex-col flex-1">
                        <div>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight line-clamp-2 mb-1"><?= e(
                                $c['title'],
                            ) ?></h3>
                            <p class="font-body-sm text-on-surface-variant line-clamp-2"><?= e($c['description']) ?></p>
                        </div>
                        
                        <div class="flex gap-1.5 flex-wrap">
                            <?php foreach ($c['tags'] as $tag): ?>
                                <span class="bg-surface-variant text-on-surface-variant text-[10px] px-2 py-0.5 rounded-sm font-medium tracking-wide uppercase"><?= e(
                                    $tag,
                                ) ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Mini Stats -->
                        <div class="grid grid-cols-2 gap-2 py-2 mb-2 border-y border-outline-variant/50 mt-auto">
                            <div>
                                <span class="block font-label-sm text-[10px] text-on-surface-variant">Teacher</span>
                                <span class="font-label-md font-bold text-on-surface truncate"><?= e(
                                    $c['teacher'],
                                ) ?></span>
                            </div>
                            <div>
                                <span class="block font-label-sm text-[10px] text-on-surface-variant">Sesi & Peserta</span>
                                <span class="font-label-md font-bold text-on-surface truncate"><?= e(
                                    (string) $c['sessions'],
                                ) ?> Sesi &middot; <?= e((string) $c['participants']) ?> Peserta</span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2 mt-auto pt-2">
                            <button class="bg-surface-container-high hover:bg-surface-variant text-on-surface font-label-md py-2 rounded-lg transition-colors border border-outline-variant">
                                Edit Info
                            </button>
                            <button class="border border-outline-variant hover:bg-surface-variant text-on-surface-variant font-label-md py-2 rounded-lg transition-colors flex items-center justify-center gap-1" onclick="window.location.href='?route=/admin/assignment'">
                                <span class="material-symbols-outlined text-[18px]">group_add</span> Assign
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
