<?php
$pageTitle = 'Forum Diskusi - ' . e($course['title']);
$categoryUrl = '?route=/student/forum/category&course_id=' . rawurlencode((string) $courseId)
    . '&category_type=' . rawurlencode((string) $categoryType) . '&session_id=' . rawurlencode((string) $sessionId)
    . ($isRiwayat ? '&is_riwayat=true' : '');
$backUrl = '?route=/student/course/detail&id=' . rawurlencode((string) $courseId) . '&tab=forum'
    . ($isRiwayat ? '&is_riwayat=true' : '');
?>
<!doctype html>
<html lang="id">
<head>
    <?php $page_title = $pageTitle; include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
    <script>window.DLS_FORUM_API_BASE = <?= json_encode(SSO_API_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
    <script src="assets/js/DX_LMS_M_Forum_Client.js" defer></script>
</head>
<body class="dls-app-shell min-h-screen bg-surface text-on-surface antialiased">
    <?php $header_title = 'Forum Diskusi'; $show_back = true; $back_url = $backUrl; include APP_ROOT . '/includes/components/DX_LMS_M_Header.php'; ?>
    <main class="dls-main min-w-0 px-4 pb-24 pt-20 lg:ml-[240px] lg:px-8 lg:pb-10">
        <div class="mx-auto max-w-4xl">
            <header class="mb-5 min-w-0 border-b border-outline-variant/40 pb-5">
                <p class="break-words text-sm font-medium text-on-surface-variant"><?= e($course['title']) ?> <span class="px-1">/</span> <?= e($categoryTitle) ?></p>
                <div class="mt-2 flex min-w-0 flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0"><h1 class="text-2xl font-bold">Diskusi sesi</h1><p class="mt-1 text-sm text-on-surface-variant">Tanya, berbagi pengalaman, dan saling menanggapi.</p></div>
                    <span data-forum-status class="inline-flex shrink-0 rounded-md bg-surface-container px-2.5 py-1.5 text-xs font-semibold text-on-surface-variant">Memuat status…</span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-on-surface-variant"><span data-thread-count>Memuat diskusi…</span><span aria-hidden="true">·</span><span>Sesi: <?= e($categoryTitle) ?></span></div>
            </header>

            <?php if ($isRiwayat): ?>
            <div class="mb-4 rounded-lg border border-outline-variant/40 bg-surface-container-low p-3 text-sm text-on-surface-variant">Course telah selesai. Forum ini hanya dapat dibaca.</div>
            <?php elseif ($isLocked): ?>
            <div class="mb-4 rounded-lg border border-outline-variant/40 bg-surface-container-low p-3 text-sm text-on-surface-variant">Selesaikan materi sesi ini terlebih dahulu untuk berpartisipasi.</div>
            <?php endif; ?>

            <div data-forum-view="category"
                 data-course-code="<?= e((string) $courseId) ?>"
                 data-session-code="<?= e((string) $sessionId) ?>"
                 data-read-only="<?= $isRiwayat ? 'true' : 'false' ?>"
                 data-locked="<?= $isLocked ? 'true' : 'false' ?>"
                 data-category-url="<?= e($categoryUrl) ?>">
                <p data-forum-message hidden class="mb-4"></p>
                <div class="mb-4 flex justify-end">
                    <?php if (!$isRiwayat && !$isLocked): ?>
                    <a data-new-topic-link href="?route=/student/forum/new&amp;course_id=<?= rawurlencode((string) $courseId) ?>&amp;category_type=<?= rawurlencode((string) $categoryType) ?>&amp;session_id=<?= rawurlencode((string) $sessionId) ?>" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90 sm:w-auto"><span class="material-symbols-outlined text-lg" aria-hidden="true">add</span>Mulai diskusi</a>
                    <?php endif; ?>
                </div>
                <div data-forum-list class="min-w-0 space-y-3" aria-live="polite">
                    <div class="rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-8 text-center text-sm text-on-surface-variant">Memuat diskusi…</div>
                </div>
            </div>
        </div>
    </main>
    <?php $active_tab = $isRiwayat ? 'riwayat' : 'course'; include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
