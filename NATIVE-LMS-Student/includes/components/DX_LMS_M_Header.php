<?php
/**
 * Komponen Bilah Atas Aplikasi (Top App Bar) — DLS.
 *
 * Menyajikan header navigasi atas yang sesuai dengan desain referensi Student.
 * Tata letak: [Ikon Bisnis] --- [Judul LMS] --- [Lonceng Notifikasi].
 *
 * Variabel yang Diterima:
 *   $base_path       - string : Jalur relatif menuju direktori root (misal: '' atau '../').
 *   $show_back       - bool   : (opsional) Tampilkan tombol kembali, bukan ikon bisnis.
 *   $header_title    - string : (opsional) Judul kustom (bawaan: 'LMS').
 *   $back_url        - string : (opsional) URL tujuan tombol kembali.
 *   $notif_count     - int    : (opsional) Jumlah badge notifikasi.
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
        <?php $back_link = isset($back_url) && $back_url ? htmlspecialchars($back_url) : 'javascript:history.back()'; ?>
        <a href="<?= $back_link ?>" class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center" aria-label="Kembali">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <?php else: ?>
        <button class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center" aria-label="Company" type="button">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">business</span>
        </button>
        <?php endif; ?>

        <!-- Center Title -->
        <div class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed tracking-tight"><?= htmlspecialchars(
            $header_title,
        ) ?></div>

        <!-- Right Action: Notification Bell -->
        <a href="<?= $base_path ?>?route=/student/notifikasi" class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center relative" aria-label="Notifikasi">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">notifications</span>
            <?php if ($notif_count > 0): ?>
            <span class="notification-dot absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 min-w-[18px] h-[18px] bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 border-2 border-surface dark:border-on-background">
                <?= $notif_count > 99 ? '99+' : $notif_count ?>
            </span>
            <?php endif; ?>
        </a>
    </div>
</header>
