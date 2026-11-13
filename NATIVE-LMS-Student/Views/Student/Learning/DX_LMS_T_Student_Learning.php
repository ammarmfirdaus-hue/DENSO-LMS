<?php
/** Menerima parameter data dari sistem pengontrol (Controller) untuk rendering visual. */
$contentBlocks = is_array($material['content_blocks'] ?? null) ? $material['content_blocks'] : [];
$materialUrl = trim((string) ($material['file_url'] ?? ''));
if ($materialUrl === '') $materialUrl = trim((string) ($material['file_path'] ?? ''));
if ($materialUrl === '') $materialUrl = trim((string) ($material['url'] ?? ''));
$materialFilename = (string) ($material['filename'] ?? '');
$declaredMaterialType = strtolower((string) ($material['type'] ?? 'text'));

if ($materialUrl === '') {
    foreach ($contentBlocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        $blockType = strtolower((string) ($block['type'] ?? ''));
        $isRelevantBlock = $blockType === 'file_attachment'
            || ($declaredMaterialType === 'video' && $blockType === 'video_embed')
            || ($declaredMaterialType === 'image' && $blockType === 'image');
        if (!$isRelevantBlock) {
            continue;
        }

        $candidate = $block['file_url'] ?? $block['file_path'] ?? $block['url'] ?? '';
        if ($candidate !== '') {
            $materialUrl = trim((string) $candidate);
            $materialFilename = $materialFilename ?: (string) ($block['filename'] ?? '');
            break;
        }
        $materialFilename = $materialFilename ?: (string) ($block['filename'] ?? '');
    }
}

// Public file paths may be relative to the web root; reject filesystem paths and unsafe schemes.
if (str_starts_with($materialUrl, 'public/')) {
    $materialUrl = substr($materialUrl, strlen('public/'));
}
if (preg_match('~^(assets|uploads|storage)/~i', $materialUrl)) {
    $materialUrl = '/' . $materialUrl;
}
$parsedMaterialUrl = $materialUrl !== '' ? parse_url($materialUrl) : false;
$isHttpMaterialUrl = is_array($parsedMaterialUrl)
    && in_array(strtolower($parsedMaterialUrl['scheme'] ?? ''), ['http', 'https'], true)
    && filter_var($materialUrl, FILTER_VALIDATE_URL);
$isLocalMaterialUrl = str_starts_with($materialUrl, '/')
    && !str_starts_with($materialUrl, '//')
    && !str_contains($materialUrl, '..')
    && !str_contains($materialUrl, "\\");
if (!$isHttpMaterialUrl && !$isLocalMaterialUrl) {
    $materialUrl = '';
}

$materialPath = $materialUrl !== '' ? (parse_url($materialUrl, PHP_URL_PATH) ?: '') : '';
$materialExtension = strtolower(pathinfo($materialUrl !== '' ? ($materialFilename ?: $materialPath) : '', PATHINFO_EXTENSION));
$materialType = strtolower((string) ($material['type'] ?? 'text'));
$extensionTypes = [
    'pdf' => 'pdf',
    'png' => 'image', 'jpg' => 'image', 'jpeg' => 'image', 'gif' => 'image', 'webp' => 'image', 'svg' => 'image',
    'mp4' => 'video', 'webm' => 'video', 'ogv' => 'video', 'mov' => 'video',
    'mp3' => 'audio', 'wav' => 'audio', 'ogg' => 'audio', 'm4a' => 'audio',
    'doc' => 'office', 'docx' => 'office', 'ppt' => 'office', 'pptx' => 'office',
    'xls' => 'office', 'xlsx' => 'office', 'odt' => 'office', 'ods' => 'office', 'odp' => 'office',
];
if (isset($extensionTypes[$materialExtension])) {
    $materialType = $extensionTypes[$materialExtension];
}
$videoHost = strtolower((string) ($parsedMaterialUrl['host'] ?? ''));
$trustedVideoHosts = [
    'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com',
    'youtu.be', 'www.youtu.be', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com',
];
$isVideoEmbed = $materialType === 'video'
    && in_array($videoHost, $trustedVideoHosts, true)
    && strtolower((string) ($parsedMaterialUrl['scheme'] ?? '')) === 'https';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Pembelajaran: ' . e($material['title']);
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 0px);
        }



        /* Custom scrollbar for content (only for desktop to avoid gaps on mobile) */
        @media (min-width: 1024px) {
            .content-scroll::-webkit-scrollbar {
                width: 8px;
            }

            .content-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .content-scroll::-webkit-scrollbar-thumb {
                background: rgba(203, 213, 225, 0.5);
                border-radius: 8px;
            }

            .content-scroll::-webkit-scrollbar-thumb:hover {
                background: rgba(148, 163, 184, 0.8);
            }
        }

        /* Smooth page transition */
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* TinyMCE HTML content prose styling */
        .tinymce-content h1,.tinymce-content h2,.tinymce-content h3 {
            font-weight: 700; color: #0f172a; margin: 1em 0 .4em;
        }
        .tinymce-content h1 { font-size: 1.75rem; }
        .tinymce-content h2 { font-size: 1.375rem; }
        .tinymce-content h3 { font-size: 1.125rem; }
        .tinymce-content p  { margin: .6em 0; }
        .tinymce-content ul,.tinymce-content ol { padding-left: 1.5rem; margin: .6em 0; }
        .tinymce-content ul { list-style-type: disc; }
        .tinymce-content ol { list-style-type: decimal; }
        .tinymce-content li { margin: .25em 0; }
        .tinymce-content a  { color: #d71920; text-decoration: underline; }
        .tinymce-content strong,.tinymce-content b { font-weight: 700; }
        .tinymce-content em,.tinymce-content i { font-style: italic; }
        .tinymce-content blockquote { border-left: 4px solid #d71920; padding-left: 1rem; color: #475569; font-style: italic; margin: .8em 0; }
        .tinymce-content table { display: block; border-collapse: collapse; width: max-content; min-width: 100%; max-width: 100%; overflow-x: auto; overscroll-behavior-x: contain; -webkit-overflow-scrolling: touch; margin: .8em 0; font-size: .9em; }
        .tinymce-content td,.tinymce-content th { border: 1px solid #e2e8f0; padding: 8px 12px; }
        .tinymce-content th { background: #f8fafc; font-weight: 600; }
        .tinymce-content img { max-width: 100%; border-radius: .5rem; }
        .tinymce-content pre,.tinymce-content code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: .88em; }
    </style>
</head>

<body class="dls-app-shell bg-[#FAFAFA] text-[#171717] antialiased h-screen flex flex-col pt-safe overflow-hidden">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = (string) $material['title'];
    $show_back = true;
    $back_url =
        $course['status'] === 'selesai'
            ? '?route=/student/riwayat/detail&id=' . urlencode($course['id'])
            : '?route=/student/course/detail&id=' . urlencode($course['id']);
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    /** Memanggil kop antarmuka basis untuk menyajikan tampilan seragam. */
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Sidebar / Bottom Nav Component -->
    <?php
    $active_tab = isset($course['status']) && $course['status'] === 'selesai' ? 'riwayat' : 'course';
    include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <!-- Progress Indicator (Di bawah header standar) -->
    <div id="learningProgressBar" class="dls-learning-progress-track fixed top-14 left-0 w-full lg:left-[240px] lg:w-[calc(100%-240px)] bg-[#F5F5F5] dark:bg-slate-800 h-1.5 z-30 transition-all duration-300">
        <?php $progressPercent = !empty($isLiveCourse)
            ? max(0, min(100, (int) ($course['progress'] ?? 0)))
            : ((($displayMaterialIndex ?? ($materialIndex + 1)) / max(1, ($totalCourseMaterials ?? $totalMaterials))) * 100); ?>
        <div class="bg-[#DC2626] h-full rounded-r-full transition-all duration-500 ease-out shadow-[0_0_8px_rgba(220,38,38,0.5)]" style="width: <?= $progressPercent ?>%;"></div>
    </div>

    <!-- Main Content Area -->
    <main class="dls-main flex-1 w-full bg-[#FAFAFA] dark:bg-slate-900 overflow-y-auto content-scroll relative flex flex-col items-center transition-all duration-300 pt-[68px] pb-[88px]">
        <div class="w-full xl:max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col fade-in pb-10">
            <?php if (!empty($completionError)): ?><p role="alert" class="mt-[18px] rounded-[12px] bg-[#FEE2E2] p-[14px] text-[14px] font-medium text-[#B91C1C]"><?= e($completionError) ?></p><?php endif; ?>
            <?php if (($_GET['completed'] ?? '') === '1'): ?><p role="status" class="mt-[18px] rounded-[12px] bg-[#DCFCE7] p-[14px] text-[14px] font-medium text-[#15803D]"><span class="material-symbols-outlined mr-1.5 align-middle text-[#16A34A]">check_circle</span>Materi ditandai selesai. Lanjutkan ke materi berikutnya.</p><?php endif; ?>
            <?php if ($materialType === 'video'): ?>
            <!-- Video Container -->
            <div class="w-full aspect-video bg-[#171717] sm:rounded-[12px] sm:mt-[18px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] overflow-hidden">
                <?php if ($materialUrl !== '' && $isVideoEmbed): ?>
                <iframe src="<?= e($materialUrl) ?>" title="<?= e($material['title']) ?>" class="w-full h-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
                <?php elseif ($materialUrl !== ''): ?>
                <video class="w-full h-full" controls playsinline preload="metadata">
                    <source src="<?= e($materialUrl) ?>"<?= $materialExtension !== '' ? ' type="video/' . e($materialExtension === 'ogv' ? 'ogg' : $materialExtension) . '"' : '' ?>>
                    Browser Anda tidak mendukung pemutar video. <a href="<?= e($materialUrl) ?>">Buka video</a>.
                </video>
                <?php else: ?>
                <div class="fd-cover-fallback flex-col gap-3 p-6 text-center text-white">
                    <span class="fd-cover-icon-badge"><span class="material-symbols-outlined text-[32px]">video_file</span></span>
                    <p class="text-white/85">File video belum ditambahkan pada materi ini.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="fd-card-elevated p-[18px] sm:p-[32px] flex-1 w-full mx-auto bg-white sm:mt-[18px] rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <div class="flex flex-wrap items-center gap-[10px] mb-[16px]">
                    <span class="fd-chip fd-chip-promo">
                        <span class="material-symbols-outlined text-[15px]">smart_display</span> Video Pembelajaran
                    </span>
                    <span class="fd-chip bg-[#F5F5F5] text-[#171717] font-semibold">
                        <span class="material-symbols-outlined text-[15px] text-[#EA580C]">schedule</span> <span class="fd-mono"><?= e($material['duration']) ?></span>
                    </span>
                </div>

                <h2 class="fd-h1 text-[24px] sm:text-[28px] text-[#171717] mb-[18px] leading-tight"><?= e($material['title']) ?></h2>

                <div class="space-y-[18px] text-[#525252] leading-[24px] fd-body border-t border-[#F5F5F5] pt-[18px]">
                    <p class="text-[16px] text-[#171717]">Simak video pembelajaran berikut untuk memahami materi <strong><?= e($material['title']) ?></strong> secara visual. Pastikan Anda memperhatikan setiap detail agar materi dapat dipahami dengan baik.</p>

                    <div class="bg-[#FAFAFA] p-[18px] rounded-[12px]">
                        <h4 class="fd-h4 text-[#171717] mb-[12px] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#EA580C]">lightbulb</span> Poin Utama Pembahasan
                        </h4>
                        <ul class="space-y-2.5 text-[14px] text-[#525252] list-disc list-inside marker:text-[#DC2626]">
                            <li>Pemahaman mendasar terkait fungsi dan tujuan.</li>
                            <li>Langkah-langkah operasional yang sesuai standar keselamatan perusahaan.</li>
                            <li>Bagaimana mengidentifikasi anomali mesin atau proses sejak dini.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php elseif ($materialType === 'pdf'): ?>
            <div class="fd-card rounded-t-[12px] p-[18px] sm:p-[28px] border-b border-[#F5F5F5] bg-white sm:mt-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] w-full">
                <div class="flex flex-wrap items-center gap-[10px] mb-[14px]">
                    <span class="fd-chip fd-chip-error">
                        <span class="material-symbols-outlined text-[15px]">picture_as_pdf</span> Dokumen PDF
                    </span>
                    <?php if ($materialFilename !== ''): ?><span class="fd-mono rounded-full bg-[#F5F5F5] px-3 py-1 text-[12px] font-medium text-[#525252] truncate max-w-[45vw]"><?= e($materialFilename) ?></span><?php endif; ?>
                </div>
                <h2 class="fd-h1 text-[24px] sm:text-[28px] text-[#171717] mb-[8px]"><?= e($material['title']) ?></h2>
                <p class="fd-body text-[#525252]">Silakan baca dan pelajari dokumen standar operasional di bawah ini secara saksama.</p>
            </div>

            <div class="w-full bg-[#F5F5F5] p-[14px] sm:p-[24px] rounded-b-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <?php if ($materialUrl !== ''): ?>
                <div class="flex flex-wrap justify-end gap-[10px] mb-[12px]">
                    <a href="<?= e($materialUrl) ?>" target="_blank" rel="noopener noreferrer" class="fd-btn fd-btn-primary fd-btn-sm rounded-full">
                        <span class="material-symbols-outlined text-[16px]">open_in_new</span> Buka di tab baru
                    </a>
                    <a href="<?= e($materialUrl) ?>" download class="fd-btn fd-btn-secondary fd-btn-sm rounded-full">
                        <span class="material-symbols-outlined text-[16px]">download</span> Unduh
                    </a>
                </div>
                <iframe src="<?= e($materialUrl) ?>#toolbar=1&navpanes=0&view=FitH" title="Pratinjau PDF: <?= e($material['title']) ?>" class="w-full h-[75vh] min-h-[520px] rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]" loading="lazy">
                    Browser tidak dapat menampilkan PDF. <a href="<?= e($materialUrl) ?>">Unduh PDF</a>.
                </iframe>
                <?php else: ?>
                <div class="mx-auto flex min-h-[300px] max-w-3xl flex-col items-center justify-center rounded-[12px] bg-white p-[32px] text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <span class="inline-flex size-14 items-center justify-center rounded-full bg-[#FEE2E2] text-[#DC2626] mb-3">
                        <span class="material-symbols-outlined text-[28px]">picture_as_pdf</span>
                    </span>
                    <h3 class="fd-h3 text-[#171717]">File PDF belum tersedia</h3>
                    <p class="mt-2 max-w-lg text-[14px] leading-relaxed text-[#525252]">Viewer siap menampilkan PDF. Tambahkan URL file pada <code>file_path</code>, <code>file_url</code>, atau lampiran materi agar dokumen dapat dibuka di sini.</p>
                    <?php if ($materialFilename !== ''): ?><p class="fd-mono mt-3 text-[12px] font-medium text-[#737373]">Nama lampiran: <?= e($materialFilename) ?></p><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($materialType === 'image'): ?>
            <div class="fd-card rounded-t-[12px] p-[18px] sm:p-[28px] border-b border-[#F5F5F5] bg-white sm:mt-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] w-full">
                <div class="flex items-center gap-[10px] mb-[14px]">
                    <span class="fd-chip fd-chip-success">
                        <span class="material-symbols-outlined text-[15px]">image</span> Infografis / Gambar
                    </span>
                </div>
                <h2 class="fd-h1 text-[24px] sm:text-[28px] text-[#171717]"><?= e($material['title']) ?></h2>
                <p class="fd-body text-[#525252] mt-2">Perhatikan infografis berikut untuk panduan visual.</p>
            </div>
            <div class="w-full bg-[#F5F5F5] p-[14px] sm:p-[24px] rounded-b-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <?php if ($materialUrl !== ''): ?>
                <div class="rounded-[12px] bg-white p-[12px] shadow-[0_4px_12px_rgba(0,0,0,0.12)]"><img src="<?= e($materialUrl) ?>" loading="lazy" decoding="async" class="mx-auto max-h-[78vh] w-full rounded-[10px] object-contain" alt="<?= e($materialFilename ?: $material['title']) ?>"></div>
                <?php else: ?>
                <div class="mx-auto flex min-h-[280px] max-w-3xl flex-col items-center justify-center rounded-[12px] bg-white p-[32px] text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <span class="inline-flex size-14 items-center justify-center rounded-full bg-[#DCFCE7] text-[#16A34A] mb-3">
                        <span class="material-symbols-outlined text-[28px]">image</span>
                    </span>
                    <h3 class="fd-h3 text-[#171717]">File gambar belum tersedia</h3>
                    <p class="mt-2 text-[14px] text-[#525252]">Tambahkan URL gambar pada data materi agar gambar ditampilkan di sini.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($materialType === 'audio'): ?>
            <section class="fd-card-elevated w-full rounded-[12px] bg-white p-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] sm:mt-[18px] sm:p-[32px]">
                <span class="fd-chip fd-chip-promo"><span class="material-symbols-outlined text-[15px]">audio_file</span> Audio Pembelajaran</span>
                <h2 class="fd-h1 mt-[14px] text-[24px] sm:text-[28px] text-[#171717]"><?= e($material['title']) ?></h2>
                <?php if ($materialUrl !== ''): ?><audio class="mt-[18px] w-full" controls preload="metadata"><source src="<?= e($materialUrl) ?>"<?= $materialExtension !== '' ? ' type="audio/' . e($materialExtension === 'm4a' ? 'mp4' : $materialExtension) . '"' : '' ?>>Browser Anda tidak mendukung pemutar audio. <a href="<?= e($materialUrl) ?>">Buka file audio</a>.</audio><?php else: ?><p class="mt-[14px] rounded-[12px] bg-[#FAFAFA] p-[16px] text-[14px] text-[#525252]">File audio belum ditambahkan pada materi ini.</p><?php endif; ?>
            </section>

            <?php elseif ($materialType === 'office'): ?>
            <section class="fd-card-elevated w-full rounded-[12px] bg-white p-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] sm:mt-[18px] sm:p-[32px]">
                <span class="fd-chip fd-chip-info"><span class="material-symbols-outlined text-[15px]">draft</span> Dokumen / Presentasi</span>
                <h2 class="fd-h1 mt-[14px] text-[24px] sm:text-[28px] text-[#171717]"><?= e($material['title']) ?></h2>
                <p class="fd-body mt-[8px] text-[#525252]">File Office dapat dibuka dengan aplikasi yang sesuai atau diunduh ke perangkat Anda.</p>
                <?php if ($materialUrl !== ''): ?>
                <div class="mt-[18px] flex flex-wrap gap-[12px]"><a href="<?= e($materialUrl) ?>" target="_blank" rel="noopener noreferrer" class="fd-btn fd-btn-primary fd-btn-md rounded-full"><span class="material-symbols-outlined text-[18px]">open_in_new</span> Buka file</a><a href="<?= e($materialUrl) ?>" download class="fd-btn fd-btn-secondary fd-btn-md rounded-full"><span class="material-symbols-outlined text-[18px]">download</span> Unduh file</a></div>
                <?php else: ?><p class="mt-[16px] rounded-[12px] bg-[#FEF3C7] p-[16px] text-[14px] font-medium text-[#B45309]">File belum tersedia. Tambahkan URL atau path file pada materi agar dapat dibuka atau diunduh.</p><?php endif; ?>
                <?php if ($materialFilename !== ''): ?><p class="fd-mono mt-[14px] text-[12px] text-[#737373]">Nama file: <?= e($materialFilename) ?></p><?php endif; ?>
            </section>

            <?php else: ?>
            <!-- Text / Article (FoodDash Elevated Surface) -->
            <div class="fd-card-elevated pt-[24px] pb-[24px] px-[18px] sm:p-[36px] w-full mx-auto bg-white sm:mt-[18px] sm:mb-[18px] rounded-[12px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] relative overflow-hidden">
                <div class="flex flex-wrap items-center gap-[12px] mb-[18px]">
                    <span class="fd-chip fd-chip-info">
                        <span class="material-symbols-outlined text-[15px]">article</span> Materi Bacaan
                    </span>
                    <span class="fd-chip bg-[#F5F5F5] text-[#171717] font-semibold">
                        <span class="material-symbols-outlined text-[15px] text-[#EA580C]">schedule</span> <?= e(
                            $material['duration'],
                        ) ?> Est. Waktu
                    </span>
                </div>

                <h2 class="fd-h1 text-[24px] sm:text-[28px] text-[#171717] mb-[24px]"><?= e(
                    $material['title'],
                ) ?></h2>

                <div class="space-y-[18px] text-[#525252] leading-[24px] fd-body">
                    <?php if (!$contentBlocks): ?>
                    <p class="text-slate-500">Belum ada konten materi yang ditambahkan.</p>
                    <?php endif; ?>
                    <?php foreach ($contentBlocks as $block): ?>
                    <?php
                    if (!is_array($block)) {
                        continue;
                    }
                    $blockType = strtolower((string) ($block['type'] ?? 'text'));
                    $blockContent = (string) ($block['content'] ?? '');
                    $blockUrl = trim((string) ($block['file_url'] ?? $block['file_path'] ?? $block['url'] ?? ''));
                    if (str_starts_with($blockUrl, 'public/')) {
                        $blockUrl = substr($blockUrl, strlen('public/'));
                    }
                    if (preg_match('~^(assets|uploads|storage)/~i', $blockUrl)) {
                        $blockUrl = '/' . $blockUrl;
                    }
                    $blockParts = $blockUrl !== '' ? parse_url($blockUrl) : false;
                    $blockIsHttp = is_array($blockParts)
                        && in_array(strtolower($blockParts['scheme'] ?? ''), ['http', 'https'], true)
                        && filter_var($blockUrl, FILTER_VALIDATE_URL);
                    $blockIsLocal = str_starts_with($blockUrl, '/')
                        && !str_starts_with($blockUrl, '//')
                        && !str_contains($blockUrl, '..')
                        && !str_contains($blockUrl, "\\");
                    if (!$blockIsHttp && !$blockIsLocal) {
                        $blockUrl = '';
                    }
                    ?>
                    <?php if ($blockType === 'heading'): ?>
                    <h3 class="text-2xl font-bold text-slate-900"><?= e($blockContent) ?></h3>
                    <?php elseif ($blockType === 'subheading'): ?>
                    <h4 class="text-xl font-bold text-slate-800"><?= e($blockContent) ?></h4>
                    <?php elseif ($blockType === 'text'): ?>
                    <?php
                    /* Deteksi apakah konten adalah HTML dari TinyMCE atau plain text.
                     * Jika mengandung tag HTML, render dengan sanitasi strip_tags allowlist.
                     * Jika plain text, tampilkan dengan escape biasa. */
                    $isHtml = $blockContent !== strip_tags($blockContent);
                    $allowedTags = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote><table><thead><tbody><tr><td><th><img><hr><pre><code><span><div>';
                    $safeHtml = strip_tags($blockContent, $allowedTags);
                    ?>
                    <?php if ($isHtml): ?>
                    <div class="tinymce-content"><?= $safeHtml ?></div>
                    <?php else: ?>
                    <p class="whitespace-pre-line"><?= e($blockContent) ?></p>
                    <?php endif; ?>
                    <?php elseif ($blockType === 'image' && $blockUrl !== ''): ?>
                    <figure><img src="<?= e($blockUrl) ?>" loading="lazy" decoding="async" alt="<?= e($block['caption'] ?? 'Gambar materi') ?>" class="mx-auto max-h-[70vh] w-full rounded-xl object-contain"><figcaption class="mt-2 text-center text-sm text-slate-500"><?= e($block['caption'] ?? '') ?></figcaption></figure>
                    <?php elseif ($blockType === 'video_embed' && $blockUrl !== ''): ?>
                    <?php $blockHost = strtolower((string) ($blockParts['host'] ?? '')); ?>
                    <?php if (in_array($blockHost, $trustedVideoHosts, true) && strtolower((string) ($blockParts['scheme'] ?? '')) === 'https'): ?>
                    <iframe src="<?= e($blockUrl) ?>" title="Video materi" class="aspect-video w-full rounded-xl bg-slate-900" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
                    <?php else: ?>
                    <video controls playsinline preload="metadata" class="aspect-video w-full rounded-xl bg-slate-900"><source src="<?= e($blockUrl) ?>">Browser Anda tidak mendukung pemutar video.</video>
                    <?php endif; ?>
                    <?php elseif ($blockType === 'file_attachment'): ?>
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex min-w-0 items-center gap-3"><span class="material-symbols-outlined text-2xl text-primary">attach_file</span><div class="min-w-0"><p class="truncate font-semibold text-slate-800"><?= e($block['filename'] ?? 'Lampiran materi') ?></p><?php if (!empty($block['size'])): ?><p class="text-sm text-slate-500"><?= e($block['size']) ?></p><?php endif; ?></div></div>
                        <?php if ($blockUrl !== ''): ?><a href="<?= e($blockUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white"><span class="material-symbols-outlined text-[18px]">download</span> Buka / unduh</a><?php else: ?><span class="text-sm text-amber-700">File belum tersedia</span><?php endif; ?>
                    </div>
                    <?php elseif ($blockType === 'checklist' && is_array($block['items'] ?? null)): ?>
                    <ul class="list-disc space-y-2 pl-6 marker:text-primary"><?php foreach ($block['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul>
                    <?php elseif (in_array($blockType, ['info', 'warning'], true)): ?>
                    <aside class="rounded-xl border-l-4 <?= $blockType === 'warning' ? 'border-amber-500 bg-amber-50' : 'border-primary bg-blue-50' ?> p-4"><?= e($blockContent) ?></aside>
                    <?php elseif ($blockType === 'quote'): ?>
                    <blockquote class="border-l-4 border-primary pl-4 italic text-slate-600"><?= e($blockContent) ?></blockquote>
                    <?php elseif ($blockType === 'external_link' && $blockUrl !== ''): ?>
                    <a href="<?= e($blockUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 font-semibold text-primary underline"><?= e($block['title'] ?? $blockUrl) ?><span class="material-symbols-outlined text-[18px]">open_in_new</span></a>
                    <?php elseif ($blockType === 'divider'): ?>
                    <hr class="border-slate-200">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Bottom Navigation Card (FoodDash Sticky One-Thumb Reachable Bar) -->
            <?php $isCompleted = $course['status'] === 'selesai'; ?>
            <div class="mt-[18px] mb-[24px] mx-[12px] sm:mx-0">
                <div class="fd-sticky-bottom-bar flex items-center justify-between gap-[12px]">

                    <?php if (($isFirstCourseMaterial ?? ($materialIndex === 0)) && !$isCompleted): ?>
                    <a href="<?= e($prevUrl) ?>" class="fd-btn fd-btn-ghost fd-btn-md rounded-full opacity-40 pointer-events-none" aria-disabled="true" tabindex="-1">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        <span class="hidden sm:inline">Materi Sebelumnya</span>
                        <span class="sm:hidden">Kembali</span>
                    </a>
                    <?php else: ?>
                    <a href="<?= e($prevUrl) ?>" class="fd-btn fd-btn-ghost fd-btn-md rounded-full">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        <?php if (($isFirstCourseMaterial ?? ($materialIndex === 0)) && $isCompleted): ?>
                        <span class="hidden sm:inline">Kembali ke Riwayat</span>
                        <span class="sm:hidden">Riwayat</span>
                        <?php else: ?>
                        <span class="hidden sm:inline">Materi Sebelumnya</span>
                        <span class="sm:hidden">Kembali</span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>

                    <div class="flex items-center justify-center px-2">
                        <div class="fd-mono rounded-full bg-[#F5F5F5] px-[14px] py-[6px] text-[13px] font-semibold text-[#171717] whitespace-nowrap">
                            <span class="hidden sm:inline font-sans font-medium text-[#525252]">Materi </span><span class="text-[#DC2626]"><?= $displayMaterialIndex ?? ($materialIndex + 1) ?></span> / <?= $totalCourseMaterials ?? $totalMaterials ?>
                        </div>
                    </div>

                    <?php if (!empty($isLiveCourse) && !$materialIsCompleted && !$isCompleted): ?>
                    <form method="post" action="?route=/student/learning&amp;id=<?= rawurlencode((string) $course['id']) ?>&amp;session_id=<?= rawurlencode((string) $session['id']) ?>&amp;material_index=<?= (int) $materialIndex ?>">
                        <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                        <button type="submit" class="fd-btn fd-btn-primary fd-btn-md rounded-full"><span class="material-symbols-outlined text-[20px]">task_alt</span><span>Tandai Selesai</span></button>
                    </form>
                    <?php else: ?>
                    <a href="<?= e($nextUrl) ?>" class="fd-btn fd-btn-primary fd-btn-md rounded-full">
                        <?php if (($isLastCourseMaterial ?? ($materialIndex === $totalMaterials - 1))): ?>
                        <?php if ($isCompleted): ?>
                        <span class="hidden sm:inline">Kembali ke Riwayat</span>
                        <span class="sm:hidden">Riwayat</span>
                        <span class="material-symbols-outlined text-[20px]">history</span>
                        <?php elseif (!empty($isLiveCourse)): ?>
                        <span>Lanjut ke Quiz</span>
                        <span class="material-symbols-outlined text-[20px]">quiz</span>
                        <?php else: ?>
                        <span class="hidden sm:inline">Kembali ke Course</span>
                        <span class="sm:hidden">Course</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="hidden sm:inline">Materi Selanjutnya</span>
                        <span class="sm:hidden">Lanjut</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </main>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script>
        (function() {
            function onDlsReady(fn) {
                if (window.DLS) fn(window.DLS);
                else window.addEventListener('DLSReady', function() { fn(window.DLS); }, { once: true });
            }
            <?php if (!empty($completionError)): ?>
            onDlsReady(function(DLS) {
                DLS.error('Gagal Menyimpan Progres', <?= json_encode((string) $completionError, JSON_UNESCAPED_UNICODE) ?>);
            });
            <?php elseif (($_GET['completed'] ?? '') === '1'): ?>
            onDlsReady(function(DLS) {
                DLS.success('Materi Selesai!', 'Materi berhasil ditandai selesai. Silakan lanjutkan ke materi berikutnya.');
                try {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('completed');
                    window.history.replaceState({}, '', url.toString());
                } catch (_) {}
            });
            <?php endif; ?>
        })();
    </script>
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
