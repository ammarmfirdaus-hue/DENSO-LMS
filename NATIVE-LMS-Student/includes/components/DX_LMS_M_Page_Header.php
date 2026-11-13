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
<header class="dls-page-banner mb-[18px] flex min-w-0 flex-col gap-[12px] rounded-[12px] bg-white p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] sm:flex-row sm:items-end sm:justify-between sm:p-[24px]">
    <div class="min-w-0 flex-1">
        <?php if ($page_eyebrow): ?>
            <p class="inline-flex items-center gap-1.5 rounded-full bg-[#F5F5F5] px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#525252]">
                <?= htmlspecialchars((string) $page_eyebrow, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
        
        <div class="mt-[6px] flex flex-wrap items-center gap-[12px]">
            <h1 class="fd-h1 text-[24px] font-bold leading-[30px] text-[#171717] sm:text-[28px] sm:leading-[34px]">
                <?= htmlspecialchars((string) $page_title_heading, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <?php if ($page_badge_html): ?>
                <div class="shrink-0">
                    <?= $page_badge_html ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($page_description): ?>
            <p class="mt-[6px] max-w-3xl text-[14px] leading-[22px] text-[#525252]">
                <?= htmlspecialchars((string) $page_description, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($page_actions_html): ?>
        <div class="flex shrink-0 flex-wrap items-center gap-[12px] sm:self-end">
            <?= $page_actions_html ?>
        </div>
    <?php endif; ?>
</header>

