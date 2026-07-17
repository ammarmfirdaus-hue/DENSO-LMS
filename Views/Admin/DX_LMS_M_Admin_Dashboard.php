<?php
/**
 * Tampilan Dasbor Admin DLS (DLS Admin Dashboard View).
 * Pusat ringkasan sistem, flow control, dan metrik aplikasi.
 */

// Menyiapkan variabel header
$header_title = 'Admin Dashboard';
$notif_count = $stats['unread_notifs'] ?? 0;
$active_tab = 'beranda';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Dashboard Admin LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar untuk Web/Desktop -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>

    <!-- Header (Responsif: margin kiri disesuaikan dengan sidebar pada desktop) -->
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 overflow-y-auto pt-[72px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <!-- Welcome Section -->
        <section class="space-y-stack-sm min-w-0 mb-stack-lg">
            <h1 class="font-headline-lg font-bold text-[clamp(16px,5.5vw,26px)] tracking-tight leading-tight text-on-surface">
                Control Center LMS ⚙️
            </h1>
            <p class="font-body-md text-on-surface-variant">Ringkasan aktivitas operasional dan pengelolaan platform Denso Learning System.</p>
        </section>

        <!-- Keseluruhan Metrik Operasional (Bento Grid) -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-gutter mb-stack-xl">
            <!-- Total Users -->
            <div class="col-span-2 lg:col-span-1 bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex flex-col justify-center items-center text-center space-y-stack-sm hover:shadow-md transition-shadow">
                <span class="material-symbols-outlined text-primary" style="font-size: 36px; font-variation-settings: 'FILL' 1;">group</span>
                <div>
                    <h2 class="font-headline-md text-headline-md font-bold text-on-surface"><?= $stats[
                        'total_users'
                    ] ?></h2>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Terdaftar</p>
                </div>
            </div>

            <!-- Total Courses Active -->
            <div class="col-span-2 lg:col-span-1 bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex flex-col justify-center items-center text-center space-y-stack-sm hover:shadow-md transition-shadow">
                <span class="material-symbols-outlined text-secondary" style="font-size: 36px; font-variation-settings: 'FILL' 1;">school</span>
                <div>
                    <h2 class="font-headline-md text-headline-md font-bold text-on-surface"><?= $stats[
                        'course_active'
                    ] ?></h2>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Course Berjalan</p>
                </div>
            </div>

            <!-- Role Distribution Breakdown -->
            <div class="col-span-2 bg-surface-container-lowest py-3 px-4 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex items-center justify-between gap-2 overflow-x-auto hover:shadow-md transition-shadow">
                <div class="flex-1 min-w-[70px] text-center border-r border-outline-variant pr-2">
                    <span class="font-headline-sm font-bold text-primary"><?= $stats['total_students'] ?></span>
                    <span class="block font-label-sm text-[10px] text-on-surface-variant">Student</span>
                </div>
                <div class="flex-1 min-w-[70px] text-center border-r border-outline-variant px-2">
                    <span class="font-headline-sm font-bold text-secondary"><?= $stats['total_teachers'] ?></span>
                    <span class="block font-label-sm text-[10px] text-on-surface-variant">Teacher</span>
                </div>
                <div class="flex-1 min-w-[70px] text-center px-1">
                    <span class="font-headline-sm font-bold text-tertiary"><?= $stats[
                        'total_teacher_associates'
                    ] ?></span>
                    <span class="block font-label-sm text-[10px] text-on-surface-variant uppercase tracking-tight">T-Assoc.</span>
                </div>
            </div>
        </section>

        <!-- Course Summary & Assignment Needs -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-stack-xl">
            <!-- Status Dist Courses -->
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant p-stack-md flex flex-col">
                <div class="flex justify-between items-center mb-stack-md">
                    <h2 class="font-headline-sm font-bold text-on-surface">Distribusi Course</h2>
                    <a href="?route=/admin/course" class="font-label-md text-secondary">Kelola</a>
                </div>
                <!-- Mini Progress Visuals -->
                <div class="flex-1 flex flex-col gap-3 justify-center">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="font-label-sm text-on-surface-variant">Published</span>
                            <span class="font-label-sm font-bold text-tertiary"><?= $stats['course_published'] ?></span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-1.5"><div class="bg-tertiary h-1.5 rounded-full" style="width: 70%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="font-label-sm text-on-surface-variant">Draft</span>
                            <span class="font-label-sm font-bold text-error"><?= $stats['course_draft'] ?></span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-1.5"><div class="bg-error h-1.5 rounded-full" style="width: 25%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="font-label-sm text-on-surface-variant">Completed (Arsip)</span>
                            <span class="font-label-sm font-bold text-secondary"><?= $stats[
                                'course_completed'
                            ] ?></span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-1.5"><div class="bg-secondary h-1.5 rounded-full" style="width: 100%"></div></div>
                    </div>
                </div>
            </div>

            <!-- Action Alerts: Pending Assignments -->
            <div class="bg-primary/5 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-primary/20 p-stack-md relative overflow-hidden">
                <!-- Background decor -->
                <span class="material-symbols-outlined text-[120px] text-primary/5 absolute -bottom-4 -right-4 pointer-events-none" style="font-variation-settings: 'FILL' 1;">assignment_late</span>
                
                <h2 class="font-headline-sm font-bold text-primary mb-2 relative z-10">Pending Assignment !</h2>
                <p class="font-body-md text-on-surface-variant mb-6 relative z-10">Ada <b><?= $stats[
                    'pending_assignment'
                ] ?> tugas delegasi course</b> yang membutuhkan eksekusi akhir oleh Admin sebelum student dapat mengakses materi.</p>
                <div class="relative z-10 flex gap-2">
                    <a href="?route=/admin/assignment" class="bg-primary text-on-primary font-label-md py-2 px-5 rounded-lg active:scale-95 transition-transform hover:shadow-md">Lihat Antrean</a>
                </div>
            </div>
        </section>

        <!-- Preview Registrasi Terbaru dll -->
        <section class="space-y-stack-md">
            <div class="flex justify-between items-center">
                <h2 class="font-headline-sm font-bold text-on-surface">Pendaftaran User Terbaru</h2>
                <a href="?route=/admin/user" class="font-label-md text-secondary">Semua User</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                <?php foreach ($recent_users as $user): ?>
                    <div class="bg-surface-container-lowest p-4 rounded-xl border border-surface-variant shadow-sm flex gap-4 items-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 flex-shrink-0 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold">
                            <?= substr($user['name'], 0, 1) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-label-md font-bold text-on-surface truncate"><?= $user['name'] ?></h3>
                            <p class="font-body-sm text-on-surface-variant truncate"><?= $user[
                                'jabatan'
                            ] ?> • <?= $user['role'] ?></p>
                            <p class="font-label-sm text-[10px] bg-secondary/10 text-secondary border border-secondary/20 rounded-md px-2 py-0.5 inline-block mt-1">L Login: <?= $user[
                                'last_login'
                            ] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
