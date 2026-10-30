<?php
/**
 * Komponen Header Halaman Bersama — Student Portal DENSO LMS.
 *
 * Menyediakan layout judul halaman standar dengan hierarki visual yang konsisten:
 * eyebrow (kicker uppercase), judul utama (H1), deskripsi, dan slot aksi/badge responsif.
 *
 * Variabel yang didukung:
 *   $page_eyebrow       - string|null : Label kategori uppercase (contoh: "PEMBELAJARAN").
 *   $page_title_heading - string      : Teks judul utama halaman.
 *   $page_description   - string|null : Keterangan atau subjudul halaman.
 *   $page_actions_html  - string|null : HTML elemen aksi atau counter di sebelah kanan.
 *   $page_badge_html    - string|null : HTML badge status di samping judul.
 */

$page_eyebrow       = $page_eyebrow ?? null;
$page_title_heading = $page_title_heading ?? ($page_title ?? 'DENSO Learning');
$page_description   = $page_description ?? null;
$page_actions_html  = $page_actions_html ?? null;
$page_badge_html    = $page_badge_html ?? null;
?>
<header class="dls-page-banner mb-6 flex min-w-0 flex-col gap-4 border-b border-outline-variant/40 pb-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0 flex-1">
        <?php if ($page_eyebrow): ?>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-primary">
                <?= htmlspecialchars((string) $page_eyebrow, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
        
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-on-surface sm:text-3xl">
                <?= htmlspecialchars((string) $page_title_heading, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <?php if ($page_badge_html): ?>
                <div class="shrink-0">
                    <?= $page_badge_html ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($page_description): ?>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-on-surface-variant">
                <?= htmlspecialchars((string) $page_description, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($page_actions_html): ?>
        <div class="flex shrink-0 flex-wrap items-center gap-2.5 sm:self-end">
            <?= $page_actions_html ?>
        </div>
    <?php endif; ?>
</header>

