<?php
/**
 * Komponen Header Terpadu Khusus Teacher (Teacher Header).
 *
 * Unified header pattern. Pada mobile tampil penuh dengan judul & notifikasi.
 * Pada desktop, memberikan offset (margin-left) karena ada sidebar aktif.
 */

$header_title = $header_title ?? 'LMS';
$show_back_button = $show_back_button ?? false;
$base_path = $base_path ?? '';
?>

<!-- TopAppBar -->
<header class="bg-surface border-b border-outline-variant fixed top-0 w-full z-40 lg:w-[calc(100%-240px)] lg:ml-[240px] transition-all duration-200">
    <div class="flex justify-between items-center w-full px-container-margin h-14">
        <!-- Left Action -->
        <?php if ($show_back_button): ?>
            <button onclick="history.back()" class="p-2 -ml-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95 flex items-center justify-center">
                <span class="material-symbols-outlined" style="font-size: 24px;">arrow_back</span>
            </button>
        <?php else: ?>
            <a href="?route=/teacher" class="text-primary hover:bg-surface-container-low p-2 -ml-2 rounded-full transition-all duration-200 active:scale-95 flex items-center justify-center">
                <span class="material-symbols-outlined" style="font-size: 24px; font-variation-settings: 'FILL' 1;">business</span>
            </a>
        <?php endif; ?>

        <!-- Center Title -->
        <div class="font-headline-md text-headline-md font-bold text-primary tracking-tight"><?= htmlspecialchars(
            $header_title,
        ) ?></div>

        <!-- Right Action: Notification Bell -->
        <a href="?route=/teacher/notifikasi" class="text-primary hover:bg-surface-container-low p-2 -mr-2 rounded-full transition-all duration-200 active:scale-95 flex items-center justify-center relative">
            <span class="material-symbols-outlined" style="font-size: 24px;">notifications</span>
            <?php
            // Logic dummy unread notification
            $hasUnread = true; // Hardcoded dummy
            if ($hasUnread): ?>
            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
            <?php endif;
            ?>
        </a>
    </div>
</header>
