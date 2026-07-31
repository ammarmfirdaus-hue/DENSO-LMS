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
                Control Center LMS
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
                    <a href="?route=/admin/user/detail&id=<?= $user[
                        'id'
                    ] ?>" class="bg-surface-container-lowest p-4 rounded-xl border border-surface-variant shadow-sm flex gap-4 items-center hover:shadow-md hover:border-primary/50 transition-all group">
                        <div class="w-12 h-12 flex-shrink-0 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold group-hover:bg-primary group-hover:text-on-primary transition-colors">
                            <?= substr($user['name'], 0, 1) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-label-md font-bold text-on-surface group-hover:text-primary transition-colors truncate"><?= $user[
                                'name'
                            ] ?></h3>
                            <p class="font-body-sm text-on-surface-variant truncate"><?= $user[
                                'jabatan'
                            ] ?> • <?= $user['role'] ?></p>
                            <p class="font-label-sm text-[10px] bg-secondary/10 text-secondary border border-secondary/20 rounded-md px-2 py-0.5 inline-block mt-1">L Login: <?= $user[
                                'last_login'
                            ] ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Notification Highlights (Stage 2 Integration) -->
        <?php if (!empty($notifications)): ?>
        <section class="mt-stack-xl space-y-stack-md">
            <div class="flex justify-between items-center">
                <h2 class="font-headline-sm font-bold text-on-surface">Peringatan Operasional (Top Priority)</h2>
                <a href="?route=/admin/notification" class="font-label-md text-secondary">Lihat Semua</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                <?php foreach ($notifications as $notif): ?>
                    <?php
                    $t_color =
                        $notif['type'] === 'Warning'
                            ? 'bg-warning-container text-on-warning-container border-warning/50'
                            : 'bg-surface-container-high text-on-surface border-outline-variant';
                    $icon = $notif['type'] === 'Warning' ? 'warning' : 'info';
                    $icon_color = $notif['type'] === 'Warning' ? 'text-warning' : 'text-primary';
                    ?>
                    <div class="<?= $t_color ?> border rounded-xl p-4 flex gap-3 items-start shadow-sm hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined <?= $icon_color ?> mt-0.5" style="font-variation-settings: 'FILL' 1;"><?= $icon ?></span>
                        <div class="flex-1">
                            <h3 class="font-label-md font-bold mb-1"><?= e($notif['title']) ?></h3>
                            <p class="font-body-sm opacity-90"><?= e($notif['message']) ?></p>
                            <span class="font-label-sm text-[10px] bg-surface/50 px-2 py-0.5 rounded-sm border border-outline-variant/50 inline-block mt-2"><?= e(
                                $notif['time'],
                            ) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Activity Timeline Snippet (Operational Control Log) -->
        <section class="mt-stack-xl space-y-stack-md">
            <!-- Section Header -->
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="font-headline-sm font-bold text-gray-900">Timeline Sistem Terkini</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Aktivitas dan log perubahan real-time di platform</p>
                </div>
                <a href="?route=/admin/activity" class="inline-flex items-center gap-1.5 text-sm font-semibold text-red-600 hover:text-red-700 transition-colors group">
                    <span>Lihat Semua Log</span>
                    <span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-0.5">arrow_forward</span>
                </a>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Filter Role Tabs -->
                <div class="px-5 pt-4 pb-3 border-b border-gray-100 flex flex-wrap gap-2" id="dashTimelineTabs">
                    <?php
                    $dash_tabs = [
                        'all'     => 'Semua',
                        'admin'   => 'Admin',
                        'teacher' => 'Teacher',
                        'student' => 'Student',
                    ];
                    $dash_role_icons = [
                        'all'     => 'group',
                        'admin'   => 'admin_panel_settings',
                        'teacher' => 'school',
                        'student' => 'person',
                    ];
                    // Warna aksen awal: all/admin=merah, teacher=biru, student=hijau
                    $dash_active_init = [
                        'all'     => 'bg-red-600   text-white border-red-600',
                        'admin'   => 'bg-red-600   text-white border-red-600',
                        'teacher' => 'bg-blue-600  text-white border-blue-600',
                        'student' => 'bg-green-600 text-white border-green-600',
                    ];
                    $dash_badge_init = [
                        'all'     => 'bg-red-500   text-white',
                        'admin'   => 'bg-red-500   text-white',
                        'teacher' => 'bg-blue-500  text-white',
                        'student' => 'bg-green-500 text-white',
                    ];
                    foreach ($dash_tabs as $tab_key => $tab_label):
                        if ($tab_key === 'all') {
                            $cnt = count($recent_activities);
                        } else {
                            $cnt = count(array_filter($recent_activities, fn($a) => strtolower($a['role']) === $tab_key));
                        }
                        $is_init_active = ($tab_key === 'all');
                    ?>
                        <button 
                            onclick="filterDashTimeline('<?= $tab_key ?>')" 
                            id="dash-tab-<?= $tab_key ?>"
                            data-role-key="<?= $tab_key ?>"
                            class="dash-timeline-tab inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-all
                                <?= $is_init_active ? ($dash_active_init[$tab_key] ?? 'bg-red-600 text-white border-red-600') : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:bg-gray-50' ?>">
                            <span class="material-symbols-outlined text-[14px]"><?= $dash_role_icons[$tab_key] ?></span>
                            <?= $tab_label ?>
                            <span class="<?= $is_init_active ? ($dash_badge_init[$tab_key] ?? 'bg-red-500 text-white') : 'bg-gray-100 text-gray-500' ?> text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                <?= $cnt ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Timeline Items -->
                <div class="px-5 py-2 space-y-0" id="dashTimelineList">
                    <?php 
                    $total_acts = count($recent_activities);
                    foreach ($recent_activities as $idx => $act):
                        $role_lower = strtolower($act['role']);
                        $role_badge_map = [
                            'admin'   => 'bg-red-50 text-red-700 border-red-200',
                            'teacher' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'student' => 'bg-green-50 text-green-700 border-green-200',
                        ];
                        $role_dot_map = [
                            'admin'   => 'bg-red-400',
                            'teacher' => 'bg-blue-400',
                            'student' => 'bg-green-400',
                        ];
                        $badge_class = $role_badge_map[$role_lower] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                        $dot_class   = $role_dot_map[$role_lower]   ?? 'bg-gray-400';
                    ?>
                        <div class="dash-log-item flex gap-3 py-3.5 border-b border-gray-100 last:border-0 relative group hover:bg-gray-50 -mx-5 px-5 transition-colors" data-role="<?= $role_lower ?>">
                            
                            <!-- Icon node (flex item, tidak absolute) -->
                            <div class="w-9 h-9 rounded-full shrink-0 bg-white border border-gray-200 text-gray-400 flex items-center justify-center shadow-sm z-10 group-hover:border-gray-300 group-hover:text-gray-600 transition-colors mt-0.5">
                                <span class="material-symbols-outlined text-[17px]" style="font-variation-settings: 'FILL' 0;"><?= e($act['icon']) ?></span>
                            </div>

                            <!-- Content (flex-1, tidak tertimpa icon) -->
                            <div class="flex-1 min-w-0 flex justify-between items-start gap-4">
                                <div class="text-sm text-gray-600 flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0 leading-relaxed">
                                    <a href="?route=/admin/user/detail&id=<?= $act['user_id'] ?? 1 ?>" class="font-semibold text-gray-900 hover:text-red-600 transition-colors whitespace-nowrap"><?= e($act['user']) ?></a>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border <?= $badge_class ?> whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $dot_class ?>"></span>
                                        <?= e($act['role']) ?>
                                    </span>
                                    <span><?= e($act['action']) ?></span>
                                    <?php if (isset($act['target_type']) && in_array($act['target_type'], ['course','quiz','material'])): ?>
                                        <a href="?route=/admin/course/detail&id=<?= $act['target_id'] ?? 101 ?>" class="font-semibold text-gray-900 hover:text-red-600 transition-colors"><?= e($act['target']) ?></a>
                                    <?php else: ?>
                                        <strong class="font-semibold text-gray-900"><?= e($act['target']) ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($act['score'])): ?>
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                            <span class="material-symbols-outlined text-[11px]">grade</span>
                                            <?= e($act['score']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="font-mono text-xs text-gray-400 whitespace-nowrap pt-0.5 shrink-0"><?= e($act['time']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Empty state (hidden by default, shown by JS) -->
                <div id="dashEmptyState" class="hidden py-10 text-center">
                    <span class="material-symbols-outlined text-gray-300 text-[48px]">inbox</span>
                    <p class="mt-2 text-sm text-gray-400">Tidak ada aktivitas untuk filter ini</p>
                </div>

                <!-- Footer -->
                <div class="px-5 py-3 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-xs text-gray-400" id="dashFilterCount">Menampilkan <?= count($recent_activities) ?> aktivitas terbaru</span>
                    <a href="?route=/admin/activity" class="text-xs font-semibold text-gray-500 hover:text-red-600 transition-colors inline-flex items-center gap-1">
                        Lihat log lengkap
                        <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script>
    // ============================================================
    // Dashboard Timeline Role Filter (Client-side)
    // ============================================================
    function filterDashTimeline(role) {
        const items   = document.querySelectorAll('.dash-log-item');
        const tabs    = document.querySelectorAll('.dash-timeline-tab');
        const empty   = document.getElementById('dashEmptyState');
        const counter = document.getElementById('dashFilterCount');

        // Peta warna aksen per role
        const roleColors = {
            all:     { bg: 'bg-red-600',   border: 'border-red-600',   badge: 'bg-red-500   text-white' },
            admin:   { bg: 'bg-red-600',   border: 'border-red-600',   badge: 'bg-red-500   text-white' },
            teacher: { bg: 'bg-blue-600',  border: 'border-blue-600',  badge: 'bg-blue-500  text-white' },
            student: { bg: 'bg-green-600', border: 'border-green-600', badge: 'bg-green-500 text-white' },
        };
        const inactiveClass = 'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:bg-gray-50';

        // Update active tab styles per-role color
        tabs.forEach(tab => {
            const tabRole = tab.getAttribute('data-role-key');
            const isActive = tabRole === role;
            const colors = roleColors[tabRole] || roleColors.all;

            // Reset semua class warna
            tab.classList.remove(
                'bg-red-600','bg-blue-600','bg-green-600',
                'border-red-600','border-blue-600','border-green-600',
                'text-white',
                'bg-white','text-gray-500','border-gray-200',
                'hover:border-gray-300','hover:bg-gray-50'
            );

            if (isActive) {
                colors.bg.split(' ').forEach(c => tab.classList.add(c));
                colors.border.split(' ').forEach(c => tab.classList.add(c));
                tab.classList.add('text-white');
                const badge = tab.querySelector('span:last-child');
                if (badge) { badge.className = colors.badge + ' text-[10px] font-bold px-1.5 py-0.5 rounded-full'; }
            } else {
                inactiveClass.split(' ').forEach(c => tab.classList.add(c));
                const badge = tab.querySelector('span:last-child');
                if (badge) { badge.className = 'bg-gray-100 text-gray-500 text-[10px] font-bold px-1.5 py-0.5 rounded-full'; }
            }
        });



        // Show/hide items
        let visibleCount = 0;
        let visibleItems = [];
        items.forEach(item => {
            const itemRole = item.getAttribute('data-role');
            const show = (role === 'all' || itemRole === role);
            item.style.display = show ? '' : 'none';
            if (show) {
                visibleItems.push(item);
                visibleCount++;
            }
        });


        // Empty state
        empty.classList.toggle('hidden', visibleCount > 0);

        // Update counter
        counter.textContent = 'Menampilkan ' + visibleCount + ' aktivitas' + (role !== 'all' ? ' · Filter: ' + role.charAt(0).toUpperCase() + role.slice(1) : ' terbaru');
    }
    </script>
</body>
</html>
