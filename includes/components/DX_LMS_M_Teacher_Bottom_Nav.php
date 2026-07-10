<?php
/**
 * Komponen Navigasi Bawah Khusus Teacher (Bottom Navigation Bar).
 */

$active_tab = $active_tab ?? 'beranda';
$base_path = $base_path ?? '';
$route_prefix = '/teacher';

$tabs = [
    [
        'id' => 'beranda',
        'icon' => 'home',
        'label' => 'Beranda',
        'href' => '?route=' . $route_prefix,
    ],
    [
        'id' => 'course',
        'icon' => 'school',
        'label' => 'Course',
        'href' => '?route=' . $route_prefix . '/courses',
    ],
    [
        'id' => 'aktivitas',
        'icon' => 'history',
        'label' => 'Aktivitas',
        'href' => '?route=' . $route_prefix . '/aktivitas',
    ],
    [
        'id' => 'notifikasi',
        'icon' => 'notifications',
        'label' => 'Notifikasi',
        'href' => '?route=' . $route_prefix . '/notifikasi', // if any
    ],
    [
        'id' => 'profil',
        'icon' => 'person',
        'label' => 'Profil',
        'href' => '?route=' . $route_prefix . '/profil', // if any
    ],
];
?>

<!-- BottomNavBar -->
<nav class="bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] dark:shadow-none fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe rounded-t-xl">
    <?php foreach ($tabs as $tab): ?>
        <?php $isActive = ($active_tab === $tab['id']); ?>
        <a href="<?= htmlspecialchars($tab['href']) ?>" class="flex flex-col items-center justify-center <?= $isActive ? 'text-secondary dark:text-secondary-fixed bg-secondary-container/20' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-variant' ?> px-4 py-1 transition-transform duration-200 active:scale-90 rounded-xl">
            <span class="material-symbols-outlined mb-1" <?= $isActive ? 'style="font-variation-settings: \'FILL\' 1;"' : '' ?>><?= htmlspecialchars($tab['icon']) ?></span>
            <span class="font-label-sm text-label-sm"><?= htmlspecialchars($tab['label']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<!-- SafeArea Padding for Mobile -->
<div class="h-[env(safe-area-inset-bottom)] bg-surface w-full fixed bottom-0 z-40"></div>
