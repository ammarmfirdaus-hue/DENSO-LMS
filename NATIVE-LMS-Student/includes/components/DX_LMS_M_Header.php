<?php
/**
 * Header komponen bersama — Student Portal DENSO Learning.
 * Redesign: bersih, tanpa teks "LMS", layout full-width, konsisten di mobile & desktop.
 */
$show_back    = (bool)   ($show_back    ?? false);
$header_title = (string) ($header_title ?? 'Beranda');
$notif_count  = max(0, (int) ($notif_count ?? 0));
$base_path    = (string) ($base_path    ?? '');
$back_url     = (string) ($back_url     ?? '?route=/student');
$headerTitleEscaped = htmlspecialchars($header_title, ENT_QUOTES, 'UTF-8');
$backUrlEscaped     = htmlspecialchars($back_url,     ENT_QUOTES, 'UTF-8');
?>
<header class="dls-app-header" id="topAppBar" role="banner">
    <div class="dls-app-header-inner">

        <!-- LEFT: back button + title OR page title -->
        <div class="dls-header-context">
            <button type="button"
                    class="dls-desktop-sidebar-toggle"
                    id="dlsDesktopSidebarToggle"
                    onclick="window.toggleStudentSidebar()"
                    aria-label="Kecilkan sidebar"
                    title="Kecilkan sidebar">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>
            <?php if ($show_back): ?>
                <a class="dls-header-back" href="<?= $backUrlEscaped ?>" aria-label="Kembali">
                    <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                </a>
            <?php endif; ?>
            <div class="dls-header-title-group">
                <span class="dls-header-title"><?= $headerTitleEscaped ?></span>
            </div>
        </div>

        <!-- CENTER brand (mobile only, when no back button) -->
        <?php if (!$show_back): ?>
        <a class="dls-mobile-brand" href="?route=/student" aria-label="DENSO Learning — Beranda">
            <img src="<?= htmlspecialchars($base_path . 'assets/img/icon/denso-vector-logo.png', ENT_QUOTES, 'UTF-8') ?>"
                 alt="DENSO" width="28" height="28">
            <span>DENSO Learning</span>
        </a>
        <?php endif; ?>

        <!-- RIGHT: notification bell -->
        <a href="?route=/student/notifikasi"
           class="dls-header-notifications"
           aria-label="Notifikasi<?= $notif_count > 0 ? ', ' . $notif_count . ' belum dibaca' : '' ?>"
           title="Notifikasi">
            <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            <?php if ($notif_count > 0): ?>
                <span class="dls-notification-count notification-dot" aria-hidden="true"><?= $notif_count > 99 ? '99+' : $notif_count ?></span>
            <?php endif; ?>
        </a>

    </div>
</header>
