<?php
$accessStatus = (string) ($course['access_status'] ?? 'not_requested');
$isPending = $accessStatus === 'pending';
$isRejected = $accessStatus === 'rejected';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Akses Course - DENSO LMS'; include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
</head>
<body class="dls-app-shell min-h-screen bg-surface font-body-md text-on-surface antialiased">
    <?php
    $header_title = 'Persetujuan Course';
    $notif_count = (int) ($stats['unread_notifs'] ?? 0);
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>
    <main data-student-access-page class="dls-main-sub flex w-full min-w-0 flex-1 flex-col">
      <div class="dls-access-layout mx-auto flex w-full max-w-4xl min-w-0 flex-col gap-5">
        <a href="?route=/student/courses" class="inline-flex w-fit max-w-full items-center gap-2 text-sm font-semibold text-secondary hover:text-primary"><span class="material-symbols-outlined shrink-0" aria-hidden="true">arrow_back</span><span>Kembali ke My Course</span></a>
        <article class="min-w-0 overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-sm">
            <div class="border-b border-outline-variant bg-surface-container-low p-6 sm:p-8">
                <span class="dls-access-status-badge inline-flex max-w-full items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold <?= $isRejected ? 'bg-error-container text-on-error-container' : ($isPending ? 'bg-amber-100 text-amber-900' : 'bg-secondary-container text-on-secondary-container') ?>">
                    <span class="material-symbols-outlined text-base" aria-hidden="true"><?= $isRejected ? 'cancel' : ($isPending ? 'schedule' : 'lock') ?></span>
                    <?= $isRejected ? 'Permintaan ditolak' : ($isPending ? 'Menunggu persetujuan Admin' : 'Akses perlu disetujui') ?>
                </span>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-primary"><?= e((string) ($course['category'] ?? 'Course')) ?> · Akses Course</p>
                <h1 class="mt-2 text-2xl font-bold leading-tight text-on-surface sm:text-3xl"><?= e((string) ($course['title'] ?? 'Course')) ?></h1>
                <p class="mt-2 text-sm text-on-surface-variant">Kode: <?= e((string) ($course['id'] ?? '')) ?></p>
                <?php if (!empty($course['description'])): ?><p class="mt-4 max-w-3xl whitespace-pre-wrap text-sm leading-6 text-on-surface-variant"><?= e((string) $course['description']) ?></p><?php endif; ?>
            </div>
            <div class="space-y-5 p-6 sm:p-8">
                <?php if ($request_error !== ''): ?><p role="alert" class="rounded-xl border border-error/30 bg-error-container/50 p-4 text-sm text-on-error-container"><?= e($request_error) ?></p><?php endif; ?>
                <?php if ($isPending): ?>
                    <div class="dls-access-status-note dls-access-status-pending rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950">
                        <?php if (($course['request_type'] ?? '') === 'teacher_recommendation'): ?>Teacher Anda telah merekomendasikan course ini. Materi baru terbuka setelah Admin menyetujui permintaan tersebut.<?php else: ?>Permintaan Anda sudah tercatat. Materi akan terbuka setelah Admin memberi persetujuan.<?php endif; ?>
                        <?php if (!empty($course['request_note'])): ?><p class="mt-2 border-t border-amber-200 pt-2"><strong>Catatan:</strong> <?= e((string) $course['request_note']) ?></p><?php endif; ?>
                    </div>
                <?php elseif ($isRejected): ?>
                    <div class="dls-access-status-note dls-access-status-rejected rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-900">
                        <p>Permintaan akses sebelumnya ditolak Admin. Anda dapat mengirim permintaan baru setelah membaca alasannya.</p>
                        <?php if (!empty($course['decision_note'])): ?><p class="mt-2 border-t border-red-200 pt-2"><strong>Alasan:</strong> <?= e((string) $course['decision_note']) ?></p><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="dls-access-status-note dls-access-status-information rounded-xl border border-outline-variant bg-surface-container-low p-4 text-sm leading-6 text-on-surface-variant">Course ini hanya dapat dipelajari setelah Admin menyetujui akses. Anda dapat meminta akses di bawah ini.</div>
                <?php endif; ?>

                <?php if (!$isPending): ?>
                <form method="post" action="?route=/student/course/detail&amp;id=<?= rawurlencode((string) ($course['id'] ?? '')) ?>" class="space-y-3">
                    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
                    <label for="access-request-note" class="block text-sm font-semibold">Catatan permintaan <span class="font-normal text-muted">(opsional)</span></label>
                    <textarea id="access-request-note" name="request_note" maxlength="1000" rows="3" class="dls-access-note w-full resize-y rounded-xl border border-outline-variant bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" placeholder="Jelaskan kebutuhan Anda mengikuti course ini."></textarea>
                    <div class="dls-access-form-actions flex min-w-0 flex-col items-stretch gap-2 pt-1 sm:flex-row sm:items-center">
                        <button type="submit" class="dls-course-access-submit inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold sm:w-auto"><span class="material-symbols-outlined" aria-hidden="true">send</span><span><?= $isRejected ? 'Ajukan Akses Kembali' : 'Minta Akses Course' ?></span></button>
                        <span id="access-request-count" class="text-xs text-on-surface-variant" aria-live="polite">0/1000 karakter</span>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </article>
      </div>
    </main>
    <?php $active_tab = 'course'; include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
    <script>
        (() => {
            const note = document.getElementById('access-request-note');
            const counter = document.getElementById('access-request-count');
            if (!note || !counter) return;
            const updateCount = () => { counter.textContent = `${note.value.length}/1000 karakter`; };
            note.addEventListener('input', updateCount);
            updateCount();
        })();
    </script>
</body>
</html>
