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
            <?php
            $page_eyebrow = (string) ($course['title'] ?? '') . ' / ' . (string) $categoryTitle;
            $page_title_heading = 'Diskusi Sesi';
            $page_description = 'Tanya, berbagi pengalaman, dan saling berdiskusi bersama instruktur dan rekan kerja.';
            $page_actions_html = '<span data-forum-status class="fd-chip fd-chip-neutral shrink-0">Memuat status…</span>';
            include APP_ROOT . '/includes/components/DX_LMS_M_Page_Header.php';
            ?>
            <div class="mb-[18px] flex flex-wrap items-center gap-[12px] text-[12px] text-[#525252]"><span data-thread-count class="font-semibold text-[#171717]">Memuat diskusi…</span><span aria-hidden="true">·</span><span>Sesi: <?= e($categoryTitle) ?></span></div>

            <?php if ($isRiwayat): ?>
            <div class="fd-card mb-[18px] flex items-center gap-[12px] rounded-[12px] bg-[#F5F5F5] p-[18px] text-[14px] text-[#525252]">
                <span class="material-symbols-outlined shrink-0 text-[#737373]" style="font-size:20px;">lock_clock</span>
                <span>Course telah selesai. Forum ini hanya dapat dibaca.</span>
            </div>
            <?php elseif ($isLocked): ?>
            <div class="fd-card mb-[18px] flex items-center gap-[12px] rounded-[12px] bg-[#FEF3C7] p-[18px] text-[14px] text-[#B45309]">
                <span class="material-symbols-outlined shrink-0" style="font-size:20px;">lock</span>
                <span>Selesaikan materi sesi ini terlebih dahulu untuk berpartisipasi.</span>
            </div>
            <?php endif; ?>

            <div class="space-y-[18px]"
                 data-forum-view="category"
                 data-course-code="<?= e((string) $courseId) ?>"
                 data-session-code="<?= e((string) $sessionId) ?>"
                 data-read-only="<?= $isRiwayat ? 'true' : 'false' ?>"
                 data-locked="<?= $isLocked ? 'true' : 'false' ?>"
                 data-category-url="<?= e($categoryUrl) ?>">
                <p data-forum-message hidden class="mb-[12px]"></p>
                <div class="flex flex-wrap items-center justify-between gap-[12px]">
                    <p class="text-[13px] font-medium text-[#737373]">Pilih topik untuk membaca balasan atau mulai diskusi baru.</p>
                    <?php if (!$isRiwayat && !$isLocked): ?>
                    <a data-new-topic-link href="?route=/student/forum/new&amp;course_id=<?= rawurlencode((string) $courseId) ?>&amp;category_type=<?= rawurlencode((string) $categoryType) ?>&amp;session_id=<?= rawurlencode((string) $sessionId) ?>" class="fd-btn fd-btn-primary fd-btn-md w-full rounded-full sm:w-auto"><span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">add</span>Mulai diskusi</a>
                    <?php endif; ?>
                </div>
                <div data-forum-list class="min-w-0 space-y-[12px]" aria-live="polite">
                    <div class="fd-card rounded-[12px] bg-white p-[32px] text-center text-[14px] text-[#525252] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">Memuat diskusi…</div>
                </div>
            </div>
        </div>
    </main>
    <?php $active_tab = $isRiwayat ? 'riwayat' : 'course'; include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
