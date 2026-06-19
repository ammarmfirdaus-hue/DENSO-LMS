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
<header class="bg-surface dark:bg-on-background fixed top-0 w-full z-40 border-b border-outline-variant dark:border-outline pt-safe lg:w-[calc(100%-240px)] lg:ml-[240px] transition-all duration-300" id="topAppBar">
    <div class="flex justify-between items-center w-full px-container-margin h-14">
        <!-- Left Action -->
        <?php if ($show_back): ?>
        <a href="javascript:history.back()" class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center" aria-label="Kembali">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <?php else: ?>
        <button class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center" aria-label="Company" type="button">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">business</span>
        </button>
        <?php endif; ?>

        <!-- Center Title -->
        <div class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed tracking-tight"><?= htmlspecialchars($header_title) ?></div>

        <!-- Right Action: Notification Bell -->
        <a href="<?= $base_path ?>?route=/student/notifikasi" class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center relative" aria-label="Notifikasi">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">notifications</span>
            <?php if ($notif_count > 0): ?>
            <span class="notification-dot absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
            <?php endif; ?>
        </a>
    </div>
</header>
