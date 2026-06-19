<?php
/**
 * DLS TopAppBar Component
 * Learning Management System
 * 
 * Matches: TopAppBar dari semua desain referensi Student
 * Layout: [Business Icon] --- [LMS Title] --- [Notification Bell]
 * 
 * Variables expected:
 *   $base_path       - string: Relative path to root (e.g., '' or '../')
 *   $show_back       - bool (optional): Show back button instead of business icon
 *   $header_title    - string (optional): Custom title (default: 'LMS')
 *   $notif_count     - int (optional): Notification count badge
 */

$show_back = $show_back ?? false;
$header_title = $header_title ?? 'LMS';
$notif_count = $notif_count ?? 0;
$base_path = $base_path ?? '';
?>
<header class="dls-topbar" id="topAppBar">
    <!-- Left Action -->
    <?php if ($show_back): ?>
    <a href="javascript:history.back()" class="dls-topbar-btn" aria-label="Kembali">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <?php else: ?>
    <button class="dls-topbar-btn" aria-label="Company" type="button">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">business</span>
    </button>
    <?php endif; ?>

    <!-- Center Title -->
    <span class="dls-topbar-title"><?= htmlspecialchars($header_title) ?></span>

    <!-- Right Action: Notification Bell -->
    <a href="<?= $base_path ?>student/notifikasi.php" class="dls-topbar-btn" aria-label="Notifikasi">
        <span class="material-symbols-outlined">notifications</span>
        <?php if ($notif_count > 0): ?>
        <span class="dls-notif-dot"><?= $notif_count ?></span>
        <?php endif; ?>
    </a>
</header>
