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
        .tinymce-content table { border-collapse: collapse; width: 100%; margin: .8em 0; font-size: .9em; }
        .tinymce-content td,.tinymce-content th { border: 1px solid #e2e8f0; padding: 8px 12px; }
        .tinymce-content th { background: #f8fafc; font-weight: 600; }
        .tinymce-content img { max-width: 100%; border-radius: .5rem; }
        .tinymce-content pre,.tinymce-content code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: .88em; }
    </style>
</head>

<body class="dls-app-shell bg-slate-50 text-slate-800 antialiased h-screen flex flex-col pt-safe overflow-hidden">
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
    <div class="fixed top-14 w-full lg:w-[calc(100%-240px)] lg:ml-[240px] bg-slate-100 h-1 z-30 transition-all duration-300">
        <?php $progressPercent = (($materialIndex + 1) / $totalMaterials) * 100; ?>
        <div class="bg-primary h-full transition-all duration-500 ease-out" style="width: <?= $progressPercent ?>%;"></div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full lg:w-[calc(100%-240px)] lg:ml-[240px] mt-[60px] mb-[72px] sm:mb-[80px] bg-slate-50 overflow-y-auto content-scroll relative flex flex-col items-center transition-all duration-300">
        <div class="w-full xl:max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col fade-in pb-10">
            <?php if ($materialType === 'video'): ?>
            <!-- Video Container -->
            <div class="w-full aspect-video bg-slate-900 sm:rounded-b-2xl shadow-xl overflow-hidden">
                <?php if ($materialUrl !== '' && $isVideoEmbed): ?>
                <iframe src="<?= e($materialUrl) ?>" title="<?= e($material['title']) ?>" class="w-full h-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
                <?php elseif ($materialUrl !== ''): ?>
                <video class="w-full h-full" controls playsinline preload="metadata">
                    <source src="<?= e($materialUrl) ?>"<?= $materialExtension !== '' ? ' type="video/' . e($materialExtension === 'ogv' ? 'ogg' : $materialExtension) . '"' : '' ?>>
                    Browser Anda tidak mendukung pemutar video. <a href="<?= e($materialUrl) ?>">Buka video</a>.
                </video>
                <?php else: ?>
                <div class="w-full h-full flex flex-col items-center justify-center gap-3 p-6 text-center text-white">
                    <span class="material-symbols-outlined text-5xl">video_file</span>
                    <p>File video belum ditambahkan pada materi ini.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="p-5 sm:p-10 flex-1 w-full mx-auto bg-white sm:mt-6 sm:rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-indigo-100/50">
                        <span class="material-symbols-outlined text-[16px]">smart_display</span> Video Pembelajaran
                    </span>
                    <span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg font-medium text-xs flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px]">schedule</span> <?= e(
                            $material['duration'],
                        ) ?>
                    </span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-6 tracking-tight leading-tight"><?= e(
                    $material['title'],
                ) ?></h2>

                <div class="prose prose-slate prose-lg max-w-none text-slate-600 leading-relaxed font-body-md border-t border-slate-100 pt-6 !text-justify">
                    <p class="text-[17px] text-slate-700">Simak video pembelajaran berikut untuk memahami materi <strong><?= e(
                        $material['title'],
                    ) ?></strong> secara visual. Pastikan Anda memperhatikan setiap detail karena poin-poin penting akan diujikan pada Post-Test sesi ini.</p>

                    <div class="mt-8 bg-slate-50 p-6 rounded-xl border border-slate-200">
                        <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500">lightbulb</span> Poin Utama Pembahasan
                        </h4>
                        <ul class="space-y-3 text-[15px] text-slate-600 list-disc list-inside marker:text-primary">
                            <li>Pemahaman mendasar terkait fungsi dan tujuan.</li>
                            <li>Langkah-langkah operasional yang sesuai standar keselamatan perusahaan.</li>
                            <li>Bagaimana mengidentifikasi anomali mesin atau proses sejak dini.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php elseif ($materialType === 'pdf'): ?>
            <div class="p-6 sm:p-10 border-b border-slate-100 bg-white sm:mt-8 sm:rounded-t-2xl shadow-sm z-10 relative w-full">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1.5 bg-rose-50 text-rose-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-rose-100/50">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Dokumen PDF
                    </span>
                    <?php if ($materialFilename !== ''): ?><span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg font-medium text-xs border border-slate-200 truncate max-w-[45vw]"><?= e($materialFilename) ?></span><?php endif; ?>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-3 tracking-tight"><?= e(
                    $material['title'],
                ) ?></h2>
                <p class="font-body-md text-slate-500 text-[17px]">Silakan baca dan pelajari dokumen standar operasional di bawah ini secara saksama.</p>
            </div>

            <div class="w-full bg-slate-100 p-3 sm:p-6 sm:rounded-b-2xl shadow-inner">
                <?php if ($materialUrl !== ''): ?>
                <div class="flex justify-end gap-2 mb-3">
                    <a href="<?= e($materialUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span> Buka di tab baru
                    </a>
                    <a href="<?= e($materialUrl) ?>" download class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <span class="material-symbols-outlined text-[18px]">download</span> Unduh
                    </a>
                </div>
                <iframe src="<?= e($materialUrl) ?>#toolbar=1&navpanes=0&view=FitH" title="Pratinjau PDF: <?= e($material['title']) ?>" class="w-full h-[75vh] min-h-[520px] rounded-xl border border-slate-300 bg-white" loading="lazy">
                    Browser tidak dapat menampilkan PDF. <a href="<?= e($materialUrl) ?>">Unduh PDF</a>.
                </iframe>
                <?php else: ?>
                <div class="mx-auto flex min-h-[320px] max-w-3xl flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <span class="material-symbols-outlined mb-3 text-5xl text-rose-500">picture_as_pdf</span>
                    <h3 class="text-lg font-bold text-slate-800">File PDF belum tersedia</h3>
                    <p class="mt-2 max-w-lg text-sm leading-relaxed text-slate-500">Viewer siap menampilkan PDF. Tambahkan URL file pada <code>file_path</code>, <code>file_url</code>, atau lampiran materi agar dokumen dapat dibuka di sini.</p>
                    <?php if ($materialFilename !== ''): ?><p class="mt-3 text-sm font-medium text-slate-600">Nama lampiran: <?= e($materialFilename) ?></p><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($materialType === 'image'): ?>
            <div class="p-6 sm:p-10 border-b border-slate-100 bg-white sm:mt-8 sm:rounded-t-2xl shadow-sm w-full">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-emerald-100/50">
                        <span class="material-symbols-outlined text-[16px]">image</span> Infografis / Gambar
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight leading-tight"><?= e(
                    $material['title'],
                ) ?></h2>
                <p class="font-body-md text-slate-500 mt-3 text-[17px]">Perhatikan infografis berikut untuk panduan visual.</p>
            </div>
            <div class="w-full bg-slate-100 p-4 sm:p-8 sm:rounded-b-2xl shadow-inner">
                <?php if ($materialUrl !== ''): ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-2 sm:p-4 shadow-xl"><img src="<?= e($materialUrl) ?>" class="mx-auto max-h-[78vh] w-full rounded-xl object-contain" alt="<?= e($materialFilename ?: $material['title']) ?>"></div>
                <?php else: ?>
                <div class="mx-auto flex min-h-[300px] max-w-3xl flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <span class="material-symbols-outlined mb-3 text-5xl text-emerald-600">image</span>
                    <h3 class="text-lg font-bold text-slate-800">File gambar belum tersedia</h3>
                    <p class="mt-2 text-sm text-slate-500">Tambahkan URL gambar pada data materi agar gambar ditampilkan di sini.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($materialType === 'audio'): ?>
            <section class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:mt-8 sm:p-10">
                <span class="inline-flex items-center gap-2 rounded-lg border border-violet-100 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700"><span class="material-symbols-outlined text-[16px]">audio_file</span> Audio Pembelajaran</span>
                <h2 class="mt-4 text-2xl font-bold text-slate-800"><?= e($material['title']) ?></h2>
                <?php if ($materialUrl !== ''): ?><audio class="mt-6 w-full" controls preload="metadata"><source src="<?= e($materialUrl) ?>"<?= $materialExtension !== '' ? ' type="audio/' . e($materialExtension === 'm4a' ? 'mp4' : $materialExtension) . '"' : '' ?>>Browser Anda tidak mendukung pemutar audio. <a href="<?= e($materialUrl) ?>">Buka file audio</a>.</audio><?php else: ?><p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">File audio belum ditambahkan pada materi ini.</p><?php endif; ?>
            </section>

            <?php elseif ($materialType === 'office'): ?>
            <section class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:mt-8 sm:p-10">
                <span class="inline-flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700"><span class="material-symbols-outlined text-[16px]">draft</span> Dokumen / Presentasi</span>
                <h2 class="mt-4 text-2xl font-bold text-slate-800"><?= e($material['title']) ?></h2>
                <p class="mt-2 text-slate-600">File Office dapat dibuka dengan aplikasi yang sesuai atau diunduh ke perangkat Anda.</p>
                <?php if ($materialUrl !== ''): ?>
                <div class="mt-6 flex flex-wrap gap-3"><a href="<?= e($materialUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 font-semibold text-white"><span class="material-symbols-outlined">open_in_new</span> Buka file</a><a href="<?= e($materialUrl) ?>" download class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-3 font-semibold text-slate-700"><span class="material-symbols-outlined">download</span> Unduh file</a></div>
                <?php else: ?><p class="mt-5 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">File belum tersedia. Tambahkan URL atau path file pada materi agar dapat dibuka atau diunduh.</p><?php endif; ?>
                <?php if ($materialFilename !== ''): ?><p class="mt-4 text-sm text-slate-500">Nama file: <?= e($materialFilename) ?></p><?php endif; ?>
            </section>

            <?php else: ?>
            <!-- Text / Article -->
            <div class="pt-6 pb-2 px-6 sm:p-14 w-full mx-auto bg-white sm:mt-8 sm:mb-4 sm:rounded-2xl sm:shadow-lg sm:border border-slate-100 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-primary"></div>

                <div class="flex items-center gap-3 mb-8 mt-2">
                    <span class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-primary/20">
                        <span class="material-symbols-outlined text-[16px]">article</span> Materi Bacaan
                    </span>
                    <span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg font-medium text-xs flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px]">schedule</span> <?= e(
                            $material['duration'],
                        ) ?> Est. Waktu
                    </span>
                </div>

                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-8 tracking-tight leading-[1.25]"><?= e(
                    $material['title'],
                ) ?></h2>

                <div class="space-y-6 text-slate-700 leading-relaxed font-body-md">
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
                    <figure><img src="<?= e($blockUrl) ?>" alt="<?= e($block['caption'] ?? 'Gambar materi') ?>" class="mx-auto max-h-[70vh] w-full rounded-xl object-contain"><figcaption class="mt-2 text-center text-sm text-slate-500"><?= e($block['caption'] ?? '') ?></figcaption></figure>
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

            <!-- Bottom Navigation Card (Placed at the end of the page) -->
            <?php $isCompleted = $course['status'] === 'selesai'; ?>
            <div class="mt-2 sm:mt-4 mb-6 mx-4 sm:mx-0">
                <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/60 rounded-2xl p-3 sm:p-5 flex items-center justify-between transition-all hover:shadow-[0_8px_40px_rgb(0,0,0,0.08)]">

                    <?php if ($materialIndex === 0 && !$isCompleted): ?>
                    <a href="<?= e($prevUrl) ?>" class="group flex items-center justify-center px-3 py-2.5 sm:px-5 sm:py-2.5 rounded-xl hover:bg-slate-50 transition-all duration-300 active:scale-95 text-slate-600 font-semibold opacity-50 pointer-events-none" aria-disabled="true" tabindex="-1">
                        <span class="material-symbols-outlined text-[20px] mr-1.5 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                        <span class="hidden sm:inline">Materi Sebelumnya</span>
                        <span class="sm:hidden">Kembali</span>
                    </a>
                    <?php else: ?>
                    <a href="<?= e($prevUrl) ?>" class="group flex items-center justify-center px-3 py-2.5 sm:px-5 sm:py-2.5 rounded-xl hover:bg-slate-50 transition-all duration-300 active:scale-95 text-slate-600 font-semibold">
                        <span class="material-symbols-outlined text-[20px] mr-1.5 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                        <?php if ($materialIndex === 0 && $isCompleted): ?>
                        <span class="hidden sm:inline">Kembali ke Riwayat</span>
                        <span class="sm:hidden">Riwayat</span>
                        <?php else: ?>
                        <span class="hidden sm:inline">Materi Sebelumnya</span>
                        <span class="sm:hidden">Kembali</span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>

                    <div class="flex items-center justify-center px-2">
                        <div class="font-bold text-slate-800 text-[14px] sm:text-[15px] whitespace-nowrap bg-slate-50 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg border border-slate-100">
                            <span class="hidden sm:inline">Materi </span><span class="text-primary text-[16px] sm:text-[18px] mx-0.5"><?= $materialIndex +
                                1 ?></span>
                            <span class="text-slate-400 font-medium text-[13px] sm:text-[14px] mx-1">/</span> <?= $totalMaterials ?>
                        </div>
                    </div>

                    <a href="<?= e($nextUrl) ?>" class="group flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-2.5 rounded-xl bg-primary text-white hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 active:scale-95 font-semibold">
                        <?php if ($materialIndex === $totalMaterials - 1): ?>
                        <?php if ($isCompleted): ?>
                        <span class="hidden sm:inline">Kembali ke Riwayat</span>
                        <span class="sm:hidden">Riwayat</span>
                        <span class="material-symbols-outlined text-[20px] ml-1.5 group-hover:translate-x-1 transition-transform">history</span>
                        <?php else: ?>
                        <span class="hidden sm:inline">Selesai &amp; Post-Test</span>
                        <span class="sm:hidden">Post-Test</span>
                        <span class="material-symbols-outlined text-[20px] ml-1.5 group-hover:translate-x-1 transition-transform">assignment_turned_in</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="hidden sm:inline">Materi Selanjutnya</span>
                        <span class="sm:hidden">Lanjut</span>
                        <span class="material-symbols-outlined text-[20px] ml-1.5 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        <?php endif; ?>
                    </a>

                </div>
            </div>

        </div>
    </main>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
