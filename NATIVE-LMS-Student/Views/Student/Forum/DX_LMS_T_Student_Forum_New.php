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
            <p class="break-words text-sm text-on-surface-variant"><?= e($course['title']) ?> <span class="px-1">/</span> <?= e($session['title']) ?></p>
            <h1 class="mt-2 text-2xl font-bold">Mulai diskusi</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Ajukan pertanyaan atau bagikan pengalaman yang relevan dengan sesi ini.</p>
            <div data-forum-view="new" data-course-code="<?= e((string) $courseId) ?>" data-session-code="<?= e((string) $sessionId) ?>" data-category-url="<?= e($categoryUrl) ?>" data-read-only="false" class="mt-5 rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-4 sm:p-6">
                <p data-forum-message hidden class="mb-4"></p>
                <form data-forum-new-form>
                    <label class="block text-sm font-semibold" for="forum-title">Judul diskusi</label>
                    <input id="forum-title" name="title" type="text" required minlength="3" maxlength="200" class="mt-2 h-11 w-full min-w-0 rounded-lg border border-outline-variant/50 bg-surface px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" placeholder="Contoh: Cara menerapkan standar 5S di area kerja">
                    <label class="mt-4 block text-sm font-semibold" for="forum-body">Isi diskusi</label>
                    <textarea id="forum-body" name="body" required maxlength="2000" rows="6" class="mt-2 w-full min-w-0 resize-y rounded-lg border border-outline-variant/50 bg-surface px-3 py-2.5 text-sm leading-6 outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" placeholder="Jelaskan pertanyaan atau pengalaman Anda..."></textarea>
                    <label class="mt-4 block text-sm font-medium text-on-surface-variant">Lampiran gambar (maks. 5 MB setelah kompresi; GIF animasi tetap asli)
                        <input name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="mt-2 block max-w-full text-sm">
                    </label>
                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <a href="<?= e($categoryUrl) ?>" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-outline-variant/50 px-4 text-sm font-semibold text-on-surface-variant sm:min-w-28">Batal</a>
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white hover:opacity-90 sm:min-w-40">Kirim diskusi</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php $active_tab = 'course'; include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
