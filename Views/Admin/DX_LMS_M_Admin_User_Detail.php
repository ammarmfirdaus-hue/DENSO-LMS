<?php
/**
 * Tampilan Detail User (Admin MVP Stage 2 Drill-Down).
 */
$header_title = 'Profil Pengguna';
$active_tab = 'user';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Detail Pengguna - ' . e($user['name']);
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
        </div>

        <!-- Profil Header -->
        <div class="bg-surface-container-lowest p-stack-lg rounded-xl shadow-sm border border-surface-variant flex flex-col md:flex-row gap-6 items-center md:items-start mb-stack-xl">
            <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center text-primary text-[40px] font-bold shrink-0">
                <?= substr(e($user['name']), 0, 1) ?>
            </div>
            <div class="flex-1 text-center md:text-left">
                <div class="flex flex-wrap gap-2 justify-center md:justify-start items-center mb-2">
                    <h1 class="font-headline-lg font-bold text-on-surface"><?= e($user['name']) ?></h1>
                    <span class="bg-<?= $user['status'] === 'Active' ? 'success' : 'error' ?>/10 text-<?= $user[
    'status'
] === 'Active'
    ? 'success'
    : 'error' ?> px-2 py-0.5 rounded text-[10px] uppercase font-bold border border-<?= $user['status'] === 'Active'
     ? 'success'
     : 'error' ?>/20"><?= e($user['status']) ?></span>
                </div>
                <p class="font-body-md text-on-surface-variant mb-4">NPK: <span class="font-bold text-on-surface"><?= e(
                    $user['npk'],
                ) ?></span> • <?= e($user['departemen']) ?> • <?= e($user['jabatan']) ?></p>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                    <span class="bg-primary-container text-on-primary-container gap-1 px-3 py-1 rounded-lg font-label-sm border border-primary/20 flex items-center">
                        <span class="material-symbols-outlined text-[16px]">badge</span> Hak Akses: <?= e(
                            $user['role'],
                        ) ?>
                    </span>
                    <button class="bg-surface border border-outline-variant text-on-surface font-label-sm px-4 py-1 rounded-lg hover:bg-surface-variant transition-colors">Edit Profil</button>
                    <button class="bg-error/10 text-error font-label-sm px-4 py-1 rounded-lg hover:bg-error/20 transition-colors">Suspend</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-safe">
            <!-- Learning/Teaching Context -->
            <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant">
                <h2 class="font-headline-sm font-bold text-on-surface mb-stack-sm flex justify-between items-center">
                    Konteks Keterlibatan
                    <span class="material-symbols-outlined text-secondary text-[20px]">school</span>
                </h2>
                
                <?php if ($user['role'] === 'Student'): ?>
                    <ul class="divide-y divide-surface-variant border-t border-surface-variant">
                        <?php foreach ($user['enrolled_courses'] ?? [] as $crs): ?>
                            <li class="py-3 flex justify-between items-center group">
                                <div>
                                    <h3 class="font-label-md font-bold text-on-surface group-hover:text-primary transition-colors cursor-pointer" onclick="window.location.href='?route=/admin/course/detail&id=<?= $crs[
                                        'course_id'
                                    ] ?>'"><?= e($crs['title']) ?></h3>
                                    <p class="font-body-sm opacity-80 text-on-surface-variant">Status: <?= e(
                                        $crs['status'],
                                    ) ?></p>
                                </div>
                                <span class="font-bold text-on-surface <?= $crs['progress'] == 100
                                    ? 'text-success'
                                    : 'text-primary' ?>"><?= e((string) $crs['progress']) ?>%</span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($user['enrolled_courses'])): ?>
                            <li class="py-4 text-center text-on-surface-variant text-body-sm">Belum ada course diassign</li>
                        <?php endif; ?>
                    </ul>
                <?php else: ?>
                    <ul class="divide-y divide-surface-variant border-t border-surface-variant">
                        <?php foreach ($user['teaching_courses'] ?? [] as $crs): ?>
                            <li class="py-3 flex justify-between items-center group">
                                <div>
                                    <h3 class="font-label-md font-bold text-on-surface group-hover:text-primary transition-colors cursor-pointer" onclick="window.location.href='?route=/admin/course/detail&id=<?= $crs[
                                        'course_id'
                                    ] ?>'"><?= e($crs['title']) ?></h3>
                                </div>
                                <span class="font-label-sm text-on-surface-variant"><?= $crs['students'] ?> Siswa</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <div class="mt-4 pt-3 border-t border-outline-variant/30 text-center">
                    <button class="font-label-sm text-primary hover:bg-primary/5 px-4 py-2 rounded-lg" onclick="window.location.href='?route=/admin/assignment'">+ Tambah Assignment</button>
                </div>
            </div>

            <!-- Activity Context -->
            <div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-sm border border-surface-variant">
                <h2 class="font-headline-sm font-bold text-on-surface mb-stack-sm flex justify-between items-center">
                    Aktivitas Terbaru
                    <span class="material-symbols-outlined text-tertiary text-[20px]">history</span>
                </h2>
                
                <ul class="space-y-4">
                    <?php foreach ($user['recent_activities'] ?? [] as $act): ?>
                        <li class="flex gap-3 items-start">
                            <span class="material-symbols-outlined text-outline text-[18px] mt-0.5">check_circle</span>
                            <div>
                                <p class="font-body-sm text-on-surface"><span class="font-bold"><?= e(
                                    $act['action'],
                                ) ?></span> pada <?= e($act['target']) ?></p>
                                <span class="font-label-sm text-[10px] text-on-surface-variant"><?= e(
                                    $act['time'],
                                ) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </main>
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>
</body>
</html>
