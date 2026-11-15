<?php
/**
 * Komponen Kartu Course Bersama (Konsisten di Beranda, Course, dan Riwayat).
 *
 * Variabel input:
 *   $c            - array  : Data course
 *   $card_context - string : 'dashboard' | 'course' | 'riwayat' (default: 'course')
 */
$cardContext = $card_context ?? 'course';
$publicCourseId = (string) ($c['public_id'] ?? publicCourseId((string) ($c['id'] ?? '')));
$cStatus = (string) ($c['status'] ?? ($cardContext === 'riwayat' ? 'selesai' : 'belum_dimulai'));
$hasAccess = $cardContext === 'dashboard'
    ? !array_key_exists('has_access', $c) || !empty($c['has_access'])
    : !empty($c['has_access']);
$accessStatus = (string) ($c['access_status'] ?? '');
$cProgress = $cardContext === 'riwayat' ? 100 : (int) ($c['progress'] ?? 0);
$cIcon = (string) ($c['thumbnail_icon'] ?? 'school');
$cTitle = (string) ($c['title'] ?? 'Course');
$cDesc = (string) ($c['description'] ?? '');
$cModulesCount = (string) ($c['modules_count'] ?? 0);
$cCategory = (string) ($c['category'] ?? 'Course');
$cFilters = is_array($c['filters'] ?? null) ? $c['filters'] : [];
$cThumb = (string) ($c['thumbnail'] ?? '');
?>
<article class="course-card dls-react-course-card flex flex-col h-full overflow-hidden rounded-xl border border-[#CBD5E1] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.06),0_1px_2px_rgba(0,0,0,0.04)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-[#334155] dark:bg-[#171E29]"
         data-course-status="<?= e($cStatus) ?>"
         data-filters="<?= htmlspecialchars(json_encode($cFilters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
    <div class="course-card-cover aspect-video bg-[#0F2744] relative overflow-hidden">
        <?php if ($cThumb !== ''): ?>
        <img alt="<?= e($cTitle) ?>" class="w-full h-full object-cover" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e($cThumb) ?>">
        <?php endif; ?>
        <?php $showFallback = $cThumb === '' ? 'flex' : 'none'; ?>
        <div class="fd-cover-fallback<?= $cThumb !== '' ? ' hidden' : '' ?>" style="display:<?= $showFallback ?>">
            <span class="fd-cover-icon-badge">
                <span class="material-symbols-outlined" style="font-size: 32px; font-variation-settings: 'FILL' 1;"><?= e($cIcon) ?></span>
            </span>
        </div>
        <?php if ($cardContext !== 'riwayat' && !$hasAccess): ?>
        <?php $accessLabel = $accessStatus === 'pending' ? 'Menunggu Admin' : ($accessStatus === 'rejected' ? 'Ditolak · Ajukan ulang' : 'Perlu persetujuan'); ?>
        <div class="fd-chip <?= $accessStatus === 'rejected' ? 'fd-chip-error' : ($accessStatus === 'pending' ? 'fd-chip-warning' : 'fd-chip-info') ?> absolute top-[12px] left-[12px] shadow-sm"><?= e($accessLabel) ?></div>
        <?php elseif ($cStatus !== 'selesai'): ?>
        <div class="fd-chip fd-chip-promo absolute top-[12px] left-[12px] text-[11px] shadow-sm"><?= e($cardContext === 'dashboard' ? 'Wajib' : $cCategory) ?></div>
        <?php endif; ?>
        <?php if ($cStatus === 'selesai'): ?>
        <div class="fd-chip fd-chip-success absolute top-[12px] right-[12px] shadow-sm">
            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>Selesai
        </div>
        <?php endif; ?>
        <div class="fd-chip absolute bottom-[12px] right-[12px] bg-white/95 text-[#181824] shadow-sm">
            <span class="material-symbols-outlined text-[#DC2626]" style="font-size:15px;">menu_book</span>
            <span class="font-medium"><?= e($cModulesCount) ?> Modul</span>
        </div>
    </div>

    <div class="p-5 space-y-3 flex flex-col flex-1">
        <div>
            <h3 class="text-[16px] font-bold leading-snug text-[#0F172A] dark:text-[#F1F5F9] course-title line-clamp-2" title="<?= e($cTitle) ?>" style="min-height: 2.75rem;"><?= e($cTitle) ?></h3>
        </div>
        <p class="text-[13.5px] leading-relaxed text-[#475569] dark:text-[#94A3B8] line-clamp-2"><?= e($cDesc) ?></p>

        <div class="flex gap-1.5 flex-wrap pt-1 mt-auto h-[40px] overflow-hidden items-start">
            <?php foreach ($cFilters as $f): ?>
            <span class="border border-[#E2E8F0] bg-[#F1F5F9] text-[#334155] dark:border-[#334155] dark:bg-[#1E293B] dark:text-[#CBD5E1] text-[11px] px-2.5 py-0.5 rounded-md font-semibold whitespace-nowrap"><?= e((string) $f) ?></span>
            <?php endforeach; ?>
        </div>

        <div class="pt-1.5 space-y-1.5">
            <div class="flex justify-between items-center text-[13px]">
                <?php if ($cardContext !== 'riwayat' && !$hasAccess): ?>
                <span class="text-[#475569] dark:text-[#94A3B8] font-medium">Akses</span>
                <span class="fd-chip <?= $accessStatus === 'rejected' ? 'fd-chip-error' : ($accessStatus === 'pending' ? 'fd-chip-warning' : 'fd-chip-neutral') ?>"><?= $accessStatus === 'pending' ? 'Menunggu Admin' : ($accessStatus === 'rejected' ? 'Ditolak' : 'Belum diminta') ?></span>
                <?php elseif ($cStatus === 'sedang_dipelajari'): ?>
                <span class="fd-chip fd-chip-info">Sedang Dipelajari</span>
                <span class="fd-mono font-semibold text-[#0F172A] dark:text-white"><?= e((string) $cProgress) ?>%</span>
                <?php elseif ($cStatus === 'selesai'): ?>
                <span class="fd-chip fd-chip-success">Tuntas</span>
                <span class="fd-mono font-semibold text-[#16A34A]">100%</span>
                <?php else: ?>
                <span class="fd-chip fd-chip-neutral">Belum dimulai</span>
                <span class="fd-mono font-semibold text-[#64748B] dark:text-[#94A3B8]">0%</span>
                <?php endif; ?>
            </div>
            <div class="w-full bg-[#E2E8F0] dark:bg-[#334155] rounded-full h-[6px] overflow-hidden">
                <div class="<?= $cStatus === 'selesai' ? 'bg-[#16A34A]' : 'bg-[#0F2744] dark:bg-[#DC2626]' ?> h-[6px] rounded-full" style="width: <?= $cProgress ?>%"></div>
            </div>
        </div>

        <?php if ($cardContext !== 'riwayat' && !$hasAccess): ?>
        <button onclick="window.location.href='?route=/student/course/detail&id=<?= e($publicCourseId) ?>'" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0F2744] px-4 py-2.5 text-[13.5px] font-semibold text-white transition hover:bg-[#DC2626] mt-1.5 cursor-pointer">
            <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">lock_open</span><?= $accessStatus === 'pending' ? 'Lihat Status Permintaan' : ($accessStatus === 'rejected' ? 'Ajukan Ulang' : 'Minta Akses Course') ?>
        </button>
        <?php elseif ($cStatus === 'sedang_dipelajari'): ?>
        <button onclick="window.location.href='?route=/student/course/detail&id=<?= e($publicCourseId) ?>'" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#DC2626] px-4 py-2.5 text-[13.5px] font-semibold text-white transition hover:bg-[#B91C1C] mt-1.5 cursor-pointer">
            Lanjutkan Belajar
        </button>
        <?php elseif ($cStatus === 'belum_dimulai'): ?>
        <button onclick="window.location.href='?route=/student/course/detail&id=<?= e($publicCourseId) ?>'" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0F2744] px-4 py-2.5 text-[13.5px] font-semibold text-white transition hover:bg-[#DC2626] mt-1.5 cursor-pointer">
            Mulai Course
        </button>
        <?php else: ?>
        <button onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e($publicCourseId) ?>'" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0F2744] px-4 py-2.5 text-[13.5px] font-semibold text-white transition hover:bg-[#DC2626] mt-1.5 cursor-pointer">
            <?= $cardContext === 'riwayat' ? 'Lihat Riwayat' : 'Lihat Detail' ?>
        </button>
        <?php endif; ?>
    </div>
</article>

