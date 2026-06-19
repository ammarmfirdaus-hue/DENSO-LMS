<?php
/**
 * DLS BottomNavBar Component
 * Learning Management System
 * 
 * Matches: BottomNavBar dari semua desain referensi Student
 * 5 tabs: Beranda, Course, Riwayat, Notifikasi, Profil
 * 
 * Variables expected:
 *   $active_tab  - string: Active tab name ('beranda'|'course'|'riwayat'|'notifikasi'|'profil')
 *   $base_path   - string: Relative path to root (e.g., '' or '../')
 */

$active_tab = $active_tab ?? 'beranda';
$base_path = $base_path ?? '';

$tabs = [
    [
        'id'   => 'beranda',
        'icon' => 'home',
        'label' => 'Beranda',
        'href' => '?route=/student',
    ],
    [
        'id'   => 'course',
        'icon' => 'menu_book',
        'label' => 'Course',
        'href' => '?route=/student/courses',
    ],
    [
        'id'   => 'riwayat',
        'icon' => 'history',
        'label' => 'Riwayat',
        'href' => '?route=/student/history',
    ],
    [
        'id'   => 'notifikasi',
        'icon' => 'notifications',
        'label' => 'Notifikasi',
        'href' => '?route=/student/notifications',
    ],
    [
        'id'   => 'profil',
        'icon' => 'person',
        'label' => 'Profil',
        'href' => '?route=/student/profile',
    ],
];
?>
<nav class="dls-bottomnav dls-mobile-only" id="bottomNavBar">
    <?php foreach ($tabs as $tab): ?>
    <?php $is_active = ($active_tab === $tab['id']); ?>
    <a href="<?= $tab['href'] ?>" 
       class="dls-bottomnav-item <?= $is_active ? 'active' : '' ?>" 
       id="nav-<?= $tab['id'] ?>"
       aria-label="<?= $tab['label'] ?>">
        <span class="material-symbols-outlined"
              <?php if ($is_active): ?>style="font-variation-settings: 'FILL' 1;"<?php endif; ?>
        ><?= $tab['icon'] ?></span>
        <span><?= $tab['label'] ?></span>
        <?php if ($tab['id'] === 'notifikasi' && isset($notif_count) && $notif_count > 0 && !$is_active): ?>
        <span class="position-absolute" style="top:4px;right:12px;width:8px;height:8px;background:var(--dls-primary);border-radius:50%;"></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</nav>
