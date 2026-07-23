<?php
/**
 * Bilah Navigasi Bawah (Bottom Navigation) untuk Admin LMS
 * Menyesuaikan dengan standar desain Student/Teacher, khusus tampilan mobile.
 *
 * Variabel bawaan yang dapat dikonfigurasi:
 * - $active_tab: String (beranda|user|course|assignment|role)
 */

$active_tab = $active_tab ?? 'beranda'; ?>
<!-- Navigation Bar Bottom (Mobile Only) -->
<nav class="bg-surface-container border-t border-surface-variant fixed bottom-0 left-0 w-full z-40 lg:hidden pb-safe">
    <div class="flex justify-around items-center h-16 px-2">
        
        <a href="?route=/admin" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors group <?= $active_tab ===
        'beranda'
            ? 'text-primary'
            : 'text-on-surface-variant hover:text-primary' ?>" aria-label="Beranda Admin">
            <div class="<?= $active_tab === 'beranda'
                ? 'bg-primary-container text-on-primary-container'
                : 'group-hover:bg-surface-variant' ?> rounded-full px-4 py-1 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined icon-filled" style="font-variation-settings: 'FILL' <?= $active_tab ===
                'beranda'
                    ? '1'
                    : '0' ?>;">home</span>
            </div>
            <span class="font-label-sm text-[11px] font-medium transition-colors <?= $active_tab === 'beranda'
                ? 'text-primary font-bold'
                : '' ?>">Beranda</span>
        </a>

        <a href="?route=/admin/user" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors group <?= $active_tab ===
        'user'
            ? 'text-primary'
            : 'text-on-surface-variant hover:text-primary' ?>" aria-label="Manajemen User">
            <div class="<?= $active_tab === 'user'
                ? 'bg-primary-container text-on-primary-container'
                : 'group-hover:bg-surface-variant' ?> rounded-full px-4 py-1 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
                'user'
                    ? '1'
                    : '0' ?>;">group</span>
            </div>
            <span class="font-label-sm text-[11px] font-medium transition-colors <?= $active_tab === 'user'
                ? 'text-primary font-bold'
                : '' ?>">User</span>
        </a>

        <a href="?route=/admin/course" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors group <?= $active_tab ===
        'course'
            ? 'text-primary'
            : 'text-on-surface-variant hover:text-primary' ?>" aria-label="Manajemen Course">
            <div class="<?= $active_tab === 'course'
                ? 'bg-primary-container text-on-primary-container'
                : 'group-hover:bg-surface-variant' ?> rounded-full px-4 py-1 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
                'course'
                    ? '1'
                    : '0' ?>;">school</span>
            </div>
            <span class="font-label-sm text-[11px] font-medium transition-colors <?= $active_tab === 'course'
                ? 'text-primary font-bold'
                : '' ?>">Course</span>
        </a>

        <a href="?route=/admin/assignment" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors group <?= $active_tab ===
        'assignment'
            ? 'text-primary'
            : 'text-on-surface-variant hover:text-primary' ?>" aria-label="Manajemen Assignment">
            <div class="<?= $active_tab === 'assignment'
                ? 'bg-primary-container text-on-primary-container'
                : 'group-hover:bg-surface-variant' ?> rounded-full px-4 py-1 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
                'assignment'
                    ? '1'
                    : '0' ?>;">assignment_ind</span>
            </div>
            <span class="font-label-sm text-[11px] font-medium transition-colors <?= $active_tab === 'assignment'
                ? 'text-primary font-bold'
                : '' ?>">Assign</span>
        </a>

        <a href="?route=/admin/role" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors group <?= $active_tab ===
        'role'
            ? 'text-primary'
            : 'text-on-surface-variant hover:text-primary' ?>" aria-label="Role Pengguna">
            <div class="<?= $active_tab === 'role'
                ? 'bg-primary-container text-on-primary-container'
                : 'group-hover:bg-surface-variant' ?> rounded-full px-4 py-1 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $active_tab ===
                'role'
                    ? '1'
                    : '0' ?>;">admin_panel_settings</span>
            </div>
            <span class="font-label-sm text-[11px] font-medium transition-colors <?= $active_tab === 'role'
                ? 'text-primary font-bold'
                : '' ?>">Role</span>
        </a>

    </div>
</nav>
