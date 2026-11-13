<?php
$pageTitle = 'Diskusi - ' . e($course['title']);
$backUrl = '?route=/student/forum/category&course_id=' . rawurlencode((string) $courseId)
    . '&category_type=materi&session_id=' . rawurlencode((string) $sessionId)
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
    <?php $header_title = 'Diskusi'; $show_back = true; $back_url = $backUrl; include APP_ROOT . '/includes/components/DX_LMS_M_Header.php'; ?>
    <main class="dls-main min-w-0 px-4 pb-24 pt-20 lg:ml-[240px] lg:px-8 lg:pb-10">
        <div class="mx-auto min-w-0 max-w-3xl">
            <p class="mb-[18px] break-words text-[13px] font-medium text-[#525252]"><?= e($course['title']) ?> <span class="px-1">/</span> Diskusi sesi</p>
            <div data-forum-view="thread" data-thread-id="<?= e((string) $threadId) ?>" data-course-code="<?= e((string) $courseId) ?>" data-session-code="<?= e((string) $sessionId) ?>" data-read-only="<?= $isRiwayat ? 'true' : 'false' ?>">
                <p data-forum-message hidden class="mb-[12px]"></p>
                <div data-thread-content aria-live="polite">
                    <div class="fd-card rounded-[12px] bg-white p-[24px] text-center text-[14px] text-[#525252] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">Memuat percakapan…</div>
                </div>
            </div>
        </div>
    </main>
    <?php $active_tab = $isRiwayat ? 'riwayat' : 'course'; include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
