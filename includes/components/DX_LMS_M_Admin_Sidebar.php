<?php
/**
 * Komponen Sidebar Admin untuk tampilan layat lebar (desktop/laptop).
 *
 * Variabel bawaan:
 * - $active_tab: String (beranda|user|course|assignment|role|monitoring|score|activity|notification)
 */

$active_tab = $active_tab ?? 'beranda';
$adminSession = $user ?? getPortalUser('admin');
$adminName = $adminSession['fullname'] ?? ($adminSession['name'] ?? 'Admin System');
$adminRole = $adminSession['position'] ?? 'Super Admin';
$adminInitial = mb_substr($adminName, 0, 1, 'UTF-8');
?>
<!-- Navigation Sidebar (Desktop/Tablet Only) -->
<aside class="hidden lg:flex flex-col w-[240px] bg-surface-container fixed left-0 top-0 h-screen border-r border-outline-variant z-50">

    <!-- User Info Mini -->
    <div class="p-4 border-b border-outline-variant/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-body-lg">
                <?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="flex flex-col min-w-0">
                <span class="font-label-md font-bold text-on-surface truncate"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="font-body-sm text-on-surface-variant truncate"><?= htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-4 px-3 flex flex-col gap-1">
        <div class="px-3 pb-2 text-[11px] font-bold tracking-wider uppercase text-on-surface-variant/70">Utama</div>
        
        <a href="?route=/admin" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'beranda'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab === 'beranda'
                ? '1'
                : '0' ?>;">home</span>
            <span class="font-label-md">Dashboard</span>
        </a>

        <div class="px-3 pb-2 pt-4 text-[11px] font-bold tracking-wider uppercase text-on-surface-variant/70">Manajemen LMS</div>

        <a href="?route=/admin/user" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'user'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab === 'user'
                ? '1'
                : '0' ?>;">group</span>
            <span class="font-label-md">Manajemen User</span>
        </a>
        <a href="?route=/admin/course" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'course'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab === 'course'
                ? '1'
                : '0' ?>;">school</span>
            <span class="font-label-md">Manajemen Course</span>
        </a>
        <a href="?route=/admin/assignment" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'assignment'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
            'assignment'
                ? '1'
                : '0' ?>;">assignment_ind</span>
            <span class="font-label-md">Penugasan & Assignment</span>
        </a>
        <a href="?route=/admin/role" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'role'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab === 'role'
                ? '1'
                : '0' ?>;">admin_panel_settings</span>
            <span class="font-label-md">Control Role & Akses</span>
        </a>
        <a href="?route=/admin/forum" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'forum'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab === 'forum'
                ? '1'
                : '0' ?>;">forum</span>
            <span class="font-label-md">Forum Diskusi</span>
        </a>

        <div class="px-3 pb-2 pt-4 text-[11px] font-bold tracking-wider uppercase text-on-surface-variant/70">Operasional & Laporan</div>

        <a href="?route=/admin/monitoring" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'monitoring'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
            'monitoring'
                ? '1'
                : '0' ?>;">insert_chart</span>
            <span class="font-label-md">Learning Monitoring</span>
        </a>
        <a href="?route=/admin/score" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'score'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab === 'score'
                ? '1'
                : '0' ?>;">fact_check</span>
            <span class="font-label-md">Attendance & Score</span>
        </a>
        <a href="?route=/admin/activity" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'activity'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
            'activity'
                ? '1'
                : '0' ?>;">history</span>
            <span class="font-label-md">Activity Log</span>
        </a>
        <a href="?route=/admin/notification" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors <?= $active_tab ===
        'notification'
            ? 'bg-primary-container text-on-primary-container font-bold'
            : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface' ?>">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
            'notification'
                ? '1'
                : '0' ?>;">notifications</span>
            <span class="font-label-md">Pusat Notifikasi</span>
        </a>
    </nav>
    
    <div class="p-3 border-t border-outline-variant flex flex-col gap-1">
        <a href="?route=/pilih-peran" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors text-on-surface-variant hover:bg-surface-variant hover:text-on-surface">
            <span class="material-symbols-outlined">swap_horiz</span>
            <span class="font-label-md font-medium">Beralih Peran</span>
        </a>
        <a href="?route=/auth/logout&role=admin" class="flex items-center gap-4 px-3 py-2.5 rounded-lg transition-colors text-error hover:bg-error/10 hover:text-error">
            <span class="material-symbols-outlined">logout</span>
            <span class="font-label-md font-bold">Keluar Sistem</span>
        </a>
    </div>
</aside>
