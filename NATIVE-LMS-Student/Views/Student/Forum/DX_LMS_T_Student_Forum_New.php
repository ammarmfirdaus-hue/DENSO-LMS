<?php
$pageTitle = 'Mulai Diskusi - LMS';
$backUrl = $categoryUrl;
?>
<!doctype html>
<html lang="id">
<head>
    <?php $page_title = $pageTitle; include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
    <script>window.DLS_FORUM_API_BASE = <?= json_encode(SSO_API_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
    <script src="assets/js/DX_LMS_M_Forum_Client.js" defer></script>
</head>
<body class="dls-app-shell min-h-screen bg-surface text-on-surface antialiased">
    <?php $header_title = 'Mulai Diskusi'; $show_back = true; $back_url = $backUrl; include APP_ROOT . '/includes/components/DX_LMS_M_Header.php'; ?>
    <main class="dls-main min-w-0 px-4 pb-24 pt-20 lg:ml-[240px] lg:px-8 lg:pb-10">
        <div class="mx-auto min-w-0 max-w-2xl">
            <p class="break-words text-[13px] font-medium text-[#525252]"><?= e($course['title']) ?> <span class="px-1">/</span> <?= e($session['title']) ?></p>
            <h1 class="fd-h1 mt-[6px] text-[#171717]">Mulai diskusi</h1>
            <p class="fd-body mt-[6px] text-[#525252]">Ajukan pertanyaan atau bagikan pengalaman yang relevan dengan sesi ini.</p>
            <div data-forum-view="new" data-course-code="<?= e((string) $courseId) ?>" data-session-code="<?= e((string) $sessionId) ?>" data-category-url="<?= e($categoryUrl) ?>" data-read-only="false" class="fd-card-elevated mt-[18px] rounded-[12px] bg-white p-[18px] sm:p-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.12)]">
                <p data-forum-message hidden class="mb-[12px]"></p>
                <form data-forum-new-form class="space-y-[18px]">
                    <div>
                        <label class="fd-label" for="forum-title">Judul diskusi</label>
                        <input id="forum-title" name="title" type="text" required minlength="3" maxlength="200" class="fd-input w-full" placeholder="Contoh: Cara menerapkan standar 5S di area kerja">
                    </div>
                    <div>
                        <label class="fd-label" for="forum-body">Isi diskusi</label>
                        <textarea id="forum-body" name="body" required maxlength="2000" rows="6" class="fd-textarea w-full resize-y" placeholder="Jelaskan pertanyaan atau pengalaman Anda..."></textarea>
                    </div>
                    <div>
                        <label class="fd-label">Lampiran gambar <span class="font-normal text-[#737373]">(maks. 5 MB setelah kompresi; GIF animasi tetap asli)</span>
                            <input name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="mt-[6px] block max-w-full text-[13px] text-[#525252]">
                        </label>
                    </div>
                    <div class="pt-[6px] flex flex-col-reverse gap-[12px] sm:flex-row sm:justify-end">
                        <a href="<?= e($categoryUrl) ?>" class="fd-btn fd-btn-ghost fd-btn-md rounded-full bg-[#F5F5F5] sm:min-w-28">Batal</a>
                        <button type="submit" class="fd-btn fd-btn-primary fd-btn-md rounded-full sm:min-w-40">Kirim diskusi</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php $active_tab = 'course'; include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
