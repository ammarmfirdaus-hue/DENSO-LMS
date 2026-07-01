<?php
/**
 * Komponen Navigasi Bawah (Bottom Navigation Bar) — DLS.
 *
 * Menyajikan bilah navigasi bawah dengan 5 tab utama yang sesuai
 * dengan desain referensi Student: Beranda, Course, Riwayat, Notifikasi, Profil.
 *
 * Variabel yang Diterima:
 *   $active_tab  - string : Nama tab aktif ('beranda'|'course'|'riwayat'|'notifikasi'|'profil').
 *   $base_path   - string : Jalur relatif menuju direktori root (misal: '' atau '../').
 */

$active_tab = $active_tab ?? 'beranda';
$base_path = $base_path ?? '';

$tabs = [
    [
        'id' => 'beranda',
        'icon' => 'home',
        'label' => 'Beranda',
        'href' => '?route=/student',
    ],
    [
        'id' => 'course',
        'icon' => 'school',
        'label' => 'Course',
        'href' => '?route=/student/courses',
    ],
    [
        'id' => 'riwayat',
        'icon' => 'history',
        'label' => 'Riwayat',
        'href' => '?route=/student/riwayat',
    ],
    [
        'id' => 'notifikasi',
        'icon' => 'notifications',
        'label' => 'Notifikasi',
        'href' => '?route=/student/notifikasi',
    ],
    [
        'id' => 'profil',
        'icon' => 'person',
        'label' => 'Profil',
        'href' => '?route=/student/profil',
    ],
];
?>
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] dark:shadow-none bg-surface dark:bg-on-background rounded-t-xl lg:flex-col lg:justify-start lg:w-[240px] lg:h-screen lg:top-0 lg:left-0 lg:rounded-none lg:border-r border-t border-outline-variant dark:border-outline lg:border-t-0 lg:p-4 lg:pt-8 lg:gap-2 transition-all duration-300" id="bottomNavBar">
    
    <!-- Logo placeholder for Desktop only -->
    <div class="hidden lg:flex items-center space-x-2 mb-8 px-4 w-full">
        <span class="material-symbols-outlined text-primary text-[32px]">business</span>
        <span class="font-bold text-2xl tracking-tight text-primary">LMS</span>
    </div>

    <?php foreach ($tabs as $tab): ?>
    <?php
    $is_active = $active_tab === $tab['id'];
    $active_class = $is_active
        ? 'text-secondary dark:text-secondary-fixed bg-secondary-container/20 lg:bg-secondary-container/30 font-semibold'
        : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-variant';
    ?>
    <a href="<?= $tab['href'] ?>" 
       class="flex flex-col lg:flex-row items-center lg:justify-start justify-center rounded-xl px-4 py-1 lg:py-3 lg:w-full transition-transform duration-200 active:scale-95 relative <?= $active_class ?>" 
       id="nav-<?= $tab['id'] ?>"
       aria-label="<?= $tab['label'] ?>">
        
        <span class="material-symbols-outlined mb-1 lg:mb-0 lg:mr-3"
              style="font-variation-settings: 'FILL' <?= $is_active ? '1' : '0' ?>;"
        ><?= $tab['icon'] ?></span>
        
        <span class="font-label-sm text-label-sm lg:text-body-md"><?= $tab['label'] ?></span>

        <?php if ($tab['id'] === 'notifikasi' && isset($notif_count) && $notif_count > 0 && !$is_active): ?>
        <span class="notification-dot absolute top-1 right-4 lg:top-3 lg:right-3 w-2 h-2 bg-error rounded-full"></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</nav>
