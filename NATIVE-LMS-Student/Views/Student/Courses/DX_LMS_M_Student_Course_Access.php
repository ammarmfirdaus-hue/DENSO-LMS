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
    $show_back = true;
    $back_url = '?route=/student/courses';
    $notif_count = (int) ($stats['unread_notifs'] ?? 0);
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>
    <main data-student-access-page class="dls-main-sub flex w-full min-w-0 flex-1 flex-col">
      <div class="dls-access-layout mx-auto flex w-full max-w-4xl min-w-0 flex-col gap-[18px]">
        <a href="?route=/student/courses" class="fd-btn fd-btn-ghost fd-btn-sm w-fit max-w-full rounded-full text-[13px] font-semibold text-[#525252] hover:text-[#171717]"><span class="material-symbols-outlined shrink-0" aria-hidden="true">arrow_back</span><span>Kembali ke My Course</span></a>
        <article class="fd-card-elevated min-w-0 overflow-hidden rounded-[12px] bg-white shadow-[0_4px_12px_rgba(0,0,0,0.12)]">
            <div class="fd-on-dark relative overflow-hidden bg-[#171717] px-[18px] py-[24px] text-white sm:px-[24px]" style="background-image: radial-gradient(circle at 88% 18%, rgba(220,38,38,0.28), transparent 52%), linear-gradient(135deg, #262626 0%, #171717 100%);">
                <div class="flex flex-wrap items-center justify-between gap-[12px]">
                    <span class="dls-access-status-badge fd-chip <?= $isRejected ? 'fd-chip-error' : ($isPending ? 'fd-chip-warning' : 'fd-chip-info') ?>">
                        <span class="material-symbols-outlined text-base" aria-hidden="true"><?= $isRejected ? 'cancel' : ($isPending ? 'schedule' : 'lock') ?></span>
                        <?= $isRejected ? 'Permintaan ditolak' : ($isPending ? 'Menunggu persetujuan Admin' : 'Akses perlu disetujui') ?>
                    </span>
                    <span class="fd-mono inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-[11px] font-medium text-white/85">Kode: <?= e((string) ($course['id'] ?? '')) ?></span>
                </div>
                <div class="mt-[18px] flex flex-col gap-[12px] sm:flex-row sm:items-start">
                    <?php if (!empty($course['thumbnail'])): ?>
                    <img src="<?= e((string) $course['thumbnail']) ?>" alt="<?= e((string) ($course['title'] ?? 'Course')) ?>" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" class="h-24 w-full shrink-0 rounded-[10px] border border-white/15 object-cover sm:h-24 sm:w-40">
                    <span class="fd-cover-icon-badge shrink-0" style="display:none">
                        <span class="material-symbols-outlined" style="font-size:28px; font-variation-settings:'FILL' 1;"><?= e((string) ($course['thumbnail_icon'] ?? 'school')) ?></span>
                    </span>
                    <?php else: ?>
                    <span class="fd-cover-icon-badge shrink-0">
                        <span class="material-symbols-outlined" style="font-size:28px; font-variation-settings:'FILL' 1;"><?= e((string) ($course['thumbnail_icon'] ?? 'school')) ?></span>
                    </span>
                    <?php endif; ?>
                    <div class="min-w-0 flex-1">
                        <p class="inline-block rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-white/80"><?= e((string) ($course['category'] ?? 'Course')) ?> · Akses Course</p>
                        <h1 class="fd-h1 mt-[8px] text-[24px] font-bold leading-[30px] text-white sm:text-[28px] sm:leading-[34px]"><?= e((string) ($course['title'] ?? 'Course')) ?></h1>
                        <?php if (!empty($course['description'])): ?><p class="mt-[8px] max-w-3xl whitespace-pre-wrap text-[14px] leading-[22px] text-white/80"><?= e((string) $course['description']) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- 3-Step Access Workflow -->
            <div class="grid grid-cols-1 gap-[12px] border-b border-[#F5F5F5] bg-[#FAFAFA] px-[18px] py-[14px] sm:grid-cols-3 sm:px-[24px]">
                <div class="flex items-center gap-[10px]">
                    <span class="fd-mono inline-flex size-7 shrink-0 items-center justify-center rounded-full <?= $isPending ? 'bg-[#16A34A] text-white' : 'bg-[#DC2626] text-white' ?> text-[12px] font-bold">1</span>
                    <div class="min-w-0">
                        <p class="text-[12px] font-semibold text-[#171717]">Ajukan Akses</p>
                        <p class="text-[11px] text-[#737373] truncate"><?= $isPending ? 'Sudah dikirim' : 'Kirim permintaan Anda' ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-[10px]">
                    <span class="fd-mono inline-flex size-7 shrink-0 items-center justify-center rounded-full <?= $isPending ? 'bg-[#D97706] text-white' : ($isRejected ? 'bg-[#EF4444] text-white' : 'bg-[#E5E5E5] text-[#525252]') ?> text-[12px] font-bold">2</span>
                    <div class="min-w-0">
                        <p class="text-[12px] font-semibold text-[#171717]">Tinjauan Admin</p>
                        <p class="text-[11px] text-[#737373] truncate"><?= $isPending ? 'Sedang ditinjau' : ($isRejected ? 'Perlu pengajuan ulang' : 'Verifikasi kelayakan') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-[10px]">
                    <span class="fd-mono inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-[#E5E5E5] text-[#525252] text-[12px] font-bold">3</span>
                    <div class="min-w-0">
                        <p class="text-[12px] font-semibold text-[#171717]">Mulai Belajar</p>
                        <p class="text-[11px] text-[#737373] truncate">Akses materi &amp; quiz terbuka</p>
                    </div>
                </div>
            </div>
            <div class="space-y-[18px] p-[18px] sm:p-[24px]">
                <?php if ($request_error !== ''): ?><p role="alert" class="rounded-[12px] bg-[#FEE2E2] p-[12px] text-[14px] font-medium text-[#EF4444]"><?= e($request_error) ?></p><?php endif; ?>
                <?php if ($isPending): ?>
                    <div class="dls-access-status-note dls-access-status-pending rounded-[12px] bg-[#FEF3C7] p-[18px] text-[14px] leading-[22px] text-[#92400E]">
                        <?php if (($course['request_type'] ?? '') === 'teacher_recommendation'): ?>Teacher Anda telah merekomendasikan course ini. Materi baru terbuka setelah Admin menyetujui permintaan tersebut.<?php else: ?>Permintaan Anda sudah tercatat. Materi akan terbuka setelah Admin memberi persetujuan.<?php endif; ?>
                        <?php if (!empty($course['request_note'])): ?><p class="mt-[12px] border-t border-[#FDE68A] pt-[12px]"><strong>Catatan:</strong> <?= e((string) $course['request_note']) ?></p><?php endif; ?>
                    </div>
                <?php elseif ($isRejected): ?>
                    <div class="dls-access-status-note dls-access-status-rejected rounded-[12px] bg-[#FEE2E2] p-[18px] text-[14px] leading-[22px] text-[#B91C1C]">
                        <p>Permintaan akses sebelumnya ditolak Admin. Anda dapat mengirim permintaan baru setelah membaca alasannya.</p>
                        <?php if (!empty($course['decision_note'])): ?><p class="mt-[12px] border-t border-[#FECACA] pt-[12px]"><strong>Alasan:</strong> <?= e((string) $course['decision_note']) ?></p><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="dls-access-status-note dls-access-status-information rounded-[12px] bg-[#DBEAFE] p-[18px] text-[14px] leading-[22px] text-[#1E40AF]">Course ini hanya dapat dipelajari setelah Admin menyetujui akses. Anda dapat meminta akses di bawah ini.</div>
                <?php endif; ?>

                <?php if (!$isPending): ?>
                <form method="post" action="?route=/student/course/detail&amp;id=<?= rawurlencode((string) ($course['id'] ?? '')) ?>" class="space-y-[12px]">
                    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
                    <div>
                        <label for="access-request-note" class="fd-label">Catatan permintaan <span class="font-normal text-[#737373]">(opsional)</span></label>
                        <textarea id="access-request-note" name="request_note" maxlength="1000" rows="3" class="fd-textarea dls-access-note w-full resize-y" placeholder="Jelaskan kebutuhan Anda mengikuti course ini."></textarea>
                    </div>
                    <div class="dls-access-form-actions flex min-w-0 flex-col items-stretch gap-[12px] pt-[6px] sm:flex-row sm:items-center">
                        <button type="submit" class="dls-course-access-submit fd-btn fd-btn-primary fd-btn-lg w-full sm:w-auto"><span class="material-symbols-outlined" aria-hidden="true">send</span><span><?= $isRejected ? 'Ajukan Akses Kembali' : 'Minta Akses Course' ?></span></button>
                        <span id="access-request-count" class="fd-mono text-[12px] text-[#737373]" aria-live="polite">0/1000 karakter</span>
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
            if (note && counter) {
                const updateCount = () => { counter.textContent = `${note.value.length}/1000 karakter`; };
                note.addEventListener('input', updateCount);
                updateCount();
            }

            const onDls = (fn) => {
                if (window.DLS) fn(window.DLS);
                else window.addEventListener('DLSReady', () => fn(window.DLS), { once: true });
            };

            <?php if ($request_error !== ''): ?>
            onDls((DLS) => {
                DLS.error('Permintaan Akses Gagal', <?= json_encode($request_error, JSON_UNESCAPED_UNICODE) ?>);
            });
            <?php elseif (($_GET['requested'] ?? '') === '1'): ?>
            onDls((DLS) => {
                DLS.success('Permintaan Akses Terkirim', 'Permintaan Anda sudah tercatat dan menunggu persetujuan Admin LMS.');
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('requested');
                    window.history.replaceState({}, '', url.toString());
                } catch (_) {}
            });
            <?php endif; ?>
        })();
    </script>
</body>
</html>
