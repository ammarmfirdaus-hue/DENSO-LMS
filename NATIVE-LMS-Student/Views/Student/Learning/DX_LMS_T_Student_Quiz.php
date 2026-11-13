<?php
/** Pengaturan properti basis evaluasi diturunkan oleh Pengontrol (Controller). */

$title = '';
if ($type === 'quiz') {
    $title = 'Quiz Course: ' . $course['title'];
} elseif ($type === 'final_assessment') {
    $title = 'Final Assessment: ' . $course['title'];
}

$questions = $quizData['full_questions'] ?? [];
$totalQuestions = count($questions);
$totalBobot = 0;
foreach ($questions as $q) {
    $totalBobot += $q['weight'] ?? 0;
}

$backUrl =
    $course['status'] === 'selesai'
        ? '?route=/student/riwayat/detail&id=' . urlencode($course['id'])
        : '?route=/student/course/detail&id=' . urlencode($course['id']);

if ($isHistoryMode) $title = 'Riwayat ' . $title;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'LMS - ' . e($title);
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }



        .radio-custom:checked+div {
            border-color: var(--dls-primary);
            /* primary */
            background-color: color-mix(in srgb, var(--dls-primary) 8%, white);
            /* primary-container/20 roughly */
        }

        .radio-custom:checked+div .radio-inner {
            background-color: var(--dls-primary);
            transform: scale(1);
        }

        .radio-correct+div {
            border-color: #16A34A !important;
            background-color: #F0FDF4 !important;
        }

        .radio-correct+div .radio-inner {
            background-color: #16A34A !important;
            transform: scale(1);
        }

        .radio-wrong+div {
            border-color: #DC2626 !important;
            background-color: #FEF2F2 !important;
        }

        .radio-wrong+div .radio-inner {
            background-color: #DC2626 !important;
            transform: scale(1);
        }
    </style>
</head>

<body class="dls-app-shell dls-fullscreen-quiz bg-surface text-on-surface antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar -->
    <header class="dls-topbar dls-quiz-topbar bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin min-h-[56px] py-2 shadow-sm sticky top-0 z-50">
        <a href="<?= $backUrl ?>" class="text-primary transition-all duration-200 active:scale-95 hover:bg-surface-container-low p-2 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="fd-h3 font-bold text-[#171717] tracking-tight leading-tight px-2 text-center flex-1"><?= e(
            $title,
        ) ?></h1>
        <div class="w-10"></div> <!-- Spacer -->
    </header>

    <main class="dls-main dls-quiz-main flex-1 w-full bg-surface-container-low p-container-margin">
        <div class="w-full max-w-3xl mx-auto mt-[18px] mb-[24px]">
            <?php if ($isRealCourseQuiz): ?>
            <div class="fd-card-elevated mb-[18px] rounded-[12px] bg-white p-[18px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] sm:p-[24px]">
                <div class="flex items-start gap-[12px]"><span class="material-symbols-outlined grid size-12 shrink-0 place-items-center rounded-[12px] bg-[#FEF2F2] text-[24px] text-[#DC2626]">quiz</span><div class="min-w-0"><p class="inline-block rounded-full bg-[#F5F5F5] px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#525252]"><?= e($course['title']) ?></p><h2 class="fd-h2 mt-[6px] text-[#171717]"><?= e($realQuiz['title'] ?? 'Quiz Course') ?></h2><p class="mt-[6px] text-[14px] leading-[22px] text-[#525252]"><?= e($realQuiz['description'] ?? '') ?></p></div></div>
                <?php if (!empty($realQuiz['has_quiz'])): ?><div class="mt-[18px] flex flex-wrap gap-x-[18px] gap-y-[6px] border-t border-[#F5F5F5] pt-[12px] text-[13px] text-[#525252]"><span><strong class="fd-mono text-[#171717]"><?= (int) ($realQuiz['question_count'] ?? 0) ?></strong> pertanyaan</span><span>Nilai kelulusan <strong class="fd-mono text-[#171717]"><?= e((string) ($realQuiz['passing_score'] ?? 75)) ?></strong></span><span>Satu kali pengerjaan</span></div><?php endif; ?>
            </div>
            <?php if (($quizError ?? '') !== ''): ?><p role="alert" class="mb-[18px] rounded-[12px] bg-[#FEE2E2] p-[12px] text-[14px] font-medium text-[#EF4444]"><?= e((string) $quizError) ?></p><?php endif; ?>
            <?php if (empty($realQuiz['has_quiz'])): ?>
            <section class="fd-card rounded-[12px] bg-white p-[24px] text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]"><span class="material-symbols-outlined text-4xl text-[#737373]">assignment_late</span><h3 class="fd-h3 mt-[6px] text-[#171717]">Quiz belum tersedia</h3><p class="mt-[6px] text-[14px] text-[#525252]">Pengajar belum menerbitkan quiz untuk course ini.</p></section>
            <?php elseif (!empty($realQuiz['attempt'])): ?>
            <?php $attempt = $realQuiz['attempt']; $passed = (float) $attempt['score'] >= (float) ($realQuiz['passing_score'] ?? 75); ?>
            <section class="fd-card rounded-[12px] bg-white p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] sm:p-[24px]"><div class="flex flex-col items-center gap-[12px] text-center"><span class="material-symbols-outlined grid size-16 place-items-center rounded-full <?= $passed ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#F5F5F5] text-[#171717]' ?> text-3xl"><?= $passed ? 'task_alt' : 'fact_check' ?></span><div><p class="text-[13px] font-medium text-[#525252]">Quiz sudah dikirim</p><p class="fd-h1 mt-[6px] text-[32px] font-bold <?= $passed ? 'text-[#16A34A]' : 'text-[#DC2626]' ?>"><?= e(number_format((float) $attempt['score'], 2, ',', '.')) ?></p><p class="mt-[6px]"><span class="fd-chip <?= $passed ? 'fd-chip-success' : 'fd-chip-error' ?>"><?= $passed ? 'Memenuhi nilai kelulusan' : 'Belum mencapai nilai kelulusan' ?></span></p></div></div><div class="mt-[18px] grid gap-[12px] border-t border-[#F5F5F5] pt-[18px] text-center sm:grid-cols-2"><p class="rounded-[12px] bg-[#FAFAFA] p-[12px] text-[13px] text-[#525252]"><strong class="fd-h3 fd-mono block text-[#171717]"><?= (int) $attempt['correct_count'] ?> / <?= (int) $attempt['total_questions'] ?></strong>Jawaban benar</p><p class="rounded-[12px] bg-[#FAFAFA] p-[12px] text-[13px] text-[#525252]"><strong class="fd-mono block text-[15px] font-semibold text-[#171717]"><?= e(date('d M Y H:i', strtotime((string) $attempt['submitted_at']))) ?></strong>Waktu pengiriman</p></div><p class="mt-[12px] text-center text-[13px] text-[#737373]">Percobaan quiz sudah digunakan dan tidak dapat diulang.</p><div class="mt-[18px] flex justify-center"><a href="<?= e($backUrl) ?>" class="fd-btn fd-btn-primary fd-btn-md rounded-full"><span class="material-symbols-outlined">arrow_back</span>Kembali ke Course</a></div></section>
            <?php elseif (($realQuiz['status'] ?? 'terkunci') !== 'belum_dimulai'): ?>
            <section class="fd-card rounded-[12px] bg-white p-[24px] text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]"><span class="material-symbols-outlined text-4xl text-[#737373]">lock</span><h3 class="fd-h3 mt-[6px] text-[#171717]"><?= in_array(($realQuiz['publication_status'] ?? ''), ['draft', 'inactive'], true) ? 'Quiz belum dapat dikerjakan' : 'Quiz masih terkunci' ?></h3><p class="mx-auto mt-[6px] max-w-xl text-[14px] leading-[22px] text-[#525252]"><?php if (in_array(($realQuiz['publication_status'] ?? ''), ['draft', 'inactive'], true)): ?>Pengajar belum menerbitkan quiz ini.<?php else: ?>Selesaikan dan tandai seluruh <?= (int) ($realQuiz['total_materials'] ?? 0) ?> materi course sebagai selesai. Saat ini <?= (int) ($realQuiz['completed_materials'] ?? 0) ?> materi sudah selesai.<?php endif; ?></p><a href="<?= e($backUrl) ?>" class="fd-btn fd-btn-secondary fd-btn-md mt-[18px] rounded-full"><span class="material-symbols-outlined">menu_book</span>Kembali ke Materi</a></section>
            <?php else: ?>
            <form id="courseQuizForm" method="post" action="?route=/student/quiz&amp;id=<?= rawurlencode((string) $course['id']) ?>&amp;type=quiz" class="space-y-[18px]">
                <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                <?php foreach ($realQuiz['questions'] as $index => $question): ?>
                <fieldset class="fd-card rounded-[12px] bg-white p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] sm:p-[24px]"><legend class="sr-only">Pertanyaan <?= $index + 1 ?></legend><div class="mb-[12px] flex items-start gap-[12px]"><span class="fd-mono grid size-8 shrink-0 place-items-center rounded-full bg-[#FEF2F2] text-[13px] font-bold text-[#DC2626]"><?= $index + 1 ?></span><p class="fd-h4 pt-1 text-[#171717]"><?= e($question['question']) ?><span class="ml-1 text-[#DC2626]" aria-label="Wajib diisi">*</span></p></div><div class="space-y-[10px] pl-0 sm:pl-11"><?php foreach ($question['options'] as $optionIndex => $option): ?><label class="flex cursor-pointer items-center gap-[12px] rounded-[12px] border border-[#E5E5E5] bg-white p-[14px] transition hover:border-[#A3A3A3] hover:bg-[#FAFAFA] has-[:checked]:border-[#DC2626] has-[:checked]:bg-[#FEF2F2]"><input type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $optionIndex ?>" required class="fd-radio shrink-0" <?= (string) ($submittedAnswers[(string) $question['id']] ?? '') === (string) $optionIndex ? 'checked' : '' ?>><span class="fd-body text-[#171717]"><?= e($option) ?></span></label><?php endforeach; ?></div></fieldset>
                <?php endforeach; ?>
                <div class="fd-sticky-bottom-bar flex flex-col gap-[12px] sm:flex-row sm:items-center sm:justify-between"><p class="text-[12px] leading-[18px] text-[#525252]">Periksa kembali jawaban sebelum dikirim. Quiz hanya dapat dikerjakan satu kali.</p><button type="submit" class="fd-btn fd-btn-primary fd-btn-lg shrink-0 rounded-full w-full sm:w-auto"><span class="material-symbols-outlined">send</span>Kirim Jawaban</button></div>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <?php if ($isHistoryMode): ?>
            <!-- History Banner -->
            <div class="bg-surface rounded-2xl p-6 shadow-sm border border-outline-variant/30 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full <?= ($historyAttempt['score'] ?? 0) >= 75
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700' ?> flex items-center justify-center shrink-0">
                        <span class="font-headline-md font-bold"><?= e($historyAttempt['score'] ?? 0) ?></span>
                    </div>
                    <div class="flex-1">
                        <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Hasil Evaluasi</h2>
                        <p class="font-label-md text-on-surface-variant mt-1">
                            Percobaan ke-<?= e($historyAttempt['attempt']) ?> &bull; <?= e(
     $historyAttempt['date'] ?? '',
 ) ?>
                        </p>
                    </div>
                </div>
                <?php if (
                    isset($historyAttempt['review_status']) &&
                    $historyAttempt['review_status'] === 'under_review'
                ): ?>
                <div class="mt-4 bg-orange-50 border border-orange-200 text-orange-800 p-3 rounded-lg font-body-sm flex items-start gap-2">
                    <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">pending_actions</span>
                    <p><strong>Menunggu Penilaian:</strong> Beberapa soal essay dalam ujian ini masih menunggu penilaian (review) dari instruktur. Nilai akhir mungkin berubah.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php elseif ($totalQuestions > 0): ?>
            <!-- Active Quiz Header (FoodDash Elevated Surface) -->
            <div class="fd-card-elevated bg-white rounded-[12px] p-[18px] sm:p-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] mb-[24px]">
                <div class="flex items-center gap-[12px] mb-[18px]">
                    <div class="w-12 h-12 rounded-[12px] <?= $type === 'final_assessment'
                        ? 'bg-[#DCFCE7] text-[#16A34A]'
                        : 'bg-[#FEF2F2] text-[#DC2626]' ?> flex shrink-0 items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' 1;">
                            <?= $type === 'final_assessment' ? 'military_tech' : 'assignment' ?>
                        </span>
                    </div>
                    <div>
                        <h2 class="fd-h2 text-[#171717]">Kerjakan Ujian</h2>
                        <p class="fd-mono text-[13px] text-[#525252] mt-[3px]">Total <?= $totalQuestions ?> Pertanyaan &bull; Bobot Maks: 100</p>
                    </div>
                </div>
                <div class="bg-[#DBEAFE] border-l-[3px] border-[#2563EB] p-[12px] sm:p-[18px] rounded-r-[12px] text-[14px] leading-[22px] text-[#1E3A8A]">
                    <?php if ($type === 'final_assessment'): ?>
                    <p>Pilih atau tuliskan jawaban yang paling tepat untuk setiap pertanyaan. Nilai minimal 75 diwajibkan untuk lulus dari course ini. Selamat mengerjakan dan semoga sukses!</p>
                    <?php else: ?>
                    <p>Pilih atau tuliskan jawaban yang paling tepat untuk setiap pertanyaan. Ujian ini dirancang khusus untuk mengukur tingkat pemahaman Anda terhadap materi. Selamat mengerjakan!</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="dls-feature-note mb-6" role="status">Belum ada soal yang tersedia untuk evaluasi ini. Silakan kembali ke detail course atau hubungi Admin LMS.</div>
            <?php endif; ?>

            <!-- Formulir Soal -->
            <?php if ($isHistoryMode || $totalQuestions > 0): ?>
            <form id="quizForm" class="space-y-6" onsubmit="submitQuiz(event)">
                <?php foreach ($questions as $index => $q): ?>
                <?php
                $qId = $q['id'];
                $userAnswer =
                    $isHistoryMode && isset($historyAttempt['user_answers'][$qId])
                        ? $historyAttempt['user_answers'][$qId]
                        : null;
                $isPg = in_array($q['type'], ['pg', 'pg_image', 'pg_options_image']);
                $isEssay = in_array($q['type'], ['essay', 'essay_image']);
                $hasImage = in_array($q['type'], ['pg_image', 'essay_image']);

                $isCorrect = false;
                if ($isHistoryMode && $isPg) {
                    $isCorrect = $userAnswer === $q['correct_answer'];
                }
                ?>
                <div class="fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] <?= $isHistoryMode &&
                $isPg
                    ? ($isCorrect
                        ? 'ring-1 ring-[#16A34A]/40'
                        : 'ring-1 ring-[#EF4444]/40')
                    : '' ?>">

                    <!-- Header Soal -->
                    <div class="flex justify-between items-center mb-[12px] gap-[12px]">
                        <div class="flex gap-[6px] items-center flex-wrap">
                            <span class="fd-mono bg-[#F5F5F5] text-[#171717] text-[12px] font-semibold px-[10px] py-[4px] rounded-[6px]">Soal <?= $index +
                                1 ?></span>
                            <?php if ($isPg): ?>
                            <span class="fd-chip fd-chip-info">Pilihan Ganda</span>
                            <?php else: ?>
                            <span class="fd-chip bg-[#F5F5F5] text-[#525252]">Essay</span>
                            <?php endif; ?>
                        </div>
                        <div class="fd-mono text-[#525252] text-[12px] font-semibold px-[10px] py-[4px] rounded-[6px] bg-[#FAFAFA]">
                            Bobot: <?= $q['weight'] ?>
                        </div>
                    </div>

                    <!-- Pertanyaan -->
                    <p class="fd-h3 text-[16px] leading-[24px] text-[#171717] mb-[18px]">
                        <?= e($q['question']) ?>
                    </p>

                    <!-- Gambar Soal -->
                    <?php if ($hasImage && !empty($q['image_url'])): ?>
                    <div class="mb-[18px] rounded-[12px] overflow-hidden bg-[#FAFAFA]">
                        <img src="<?= e(
                            $q['image_url'],
                        ) ?>" alt="Ilustrasi Soal" class="w-full h-auto object-cover max-h-[300px]">
                    </div>
                    <?php endif; ?>

                    <!-- Opsi Jawaban (FoodDash 20px Radio Buttons) -->
                    <?php if ($isPg && !empty($q['options'])): ?>
                    <div class="flex flex-col gap-[12px]">
                        <?php foreach ($q['options'] as $optKey => $option): ?>
                        <?php
                        $checked = $userAnswer === $optKey;
                        $radioClass = 'radio-custom';
                        if ($isHistoryMode) {
                            if ($optKey === $q['correct_answer']) {
                                $radioClass =
                                    'radio-correct'; /** Menyorot dan memberikan warna indikator kepada elemen yang dijawab benar. */
                            } elseif ($checked && !$isCorrect) {
                                $radioClass =
                                    'radio-wrong'; /** Menyorot dan memberikan indikasi merah untuk elemen kesalahan interaktif pengguna. */
                            }
                        }
                        $isImageOption = $q['type'] === 'pg_options_image';
                        ?>
                        <label class="relative flex cursor-pointer group <?= $isHistoryMode
                            ? 'pointer-events-none'
                            : '' ?>">
                            <input type="radio" name="q_<?= e($qId) ?>" value="<?= e(
    $optKey,
) ?>" class="peer sr-only <?= $radioClass ?>" <?= $checked ? 'checked' : '' ?> <?= $isHistoryMode
     ? 'disabled'
     : 'required' ?>>
                            <div class="w-full flex <?= $isImageOption
                                ? 'flex-col items-start gap-[12px]'
                                : 'items-center' ?> p-[14px] bg-white border border-[#E5E5E5] rounded-[12px] transition-all duration-150 <?= !$isHistoryMode
     ? 'hover:bg-[#FAFAFA] hover:border-[#A3A3A3]'
     : '' ?>">
                                <div class="flex items-center w-full gap-[8px]">
                                    <div class="radio-outer w-[20px] h-[20px] rounded-full border-2 border-[#D4D4D4] bg-white flex items-center justify-center shrink-0 transition-colors">
                                        <div class="radio-inner w-[12px] h-[12px] rounded-full bg-transparent transform scale-0 transition-transform duration-150"></div>
                                    </div>
                                    <span class="fd-body text-[#171717] <?= $isImageOption ? '' : 'flex-1' ?>">
                                        <span class="font-semibold mr-[6px]"><?= e($optKey) ?>.</span>
                                        <?php if (!$isImageOption): ?>
                                        <?= e($option) ?>
                                        <?php endif; ?>
                                    </span>

                                    <?php if ($isHistoryMode && $optKey === $q['correct_answer']): ?>
                                    <span class="material-symbols-outlined text-[#16A34A] shrink-0 ml-auto">check_circle</span>
                                    <?php elseif ($isHistoryMode && $checked && !$isCorrect): ?>
                                    <span class="material-symbols-outlined text-[#EF4444] shrink-0 ml-auto">cancel</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isImageOption): ?>
                                <div class="pl-[28px] w-full mt-[6px]">
                                    <img src="<?= e($option) ?>" alt="Opsi <?= e(
    $optKey,
) ?>" class="w-full max-w-[200px] h-auto object-cover rounded-[8px] shadow-sm transition-transform duration-200 <?= !$isHistoryMode
    ? 'group-hover:scale-[1.02]'
    : '' ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Opsi Jawaban (Essay) -->
                    <?php if ($isEssay): ?>
                    <div class="mt-[6px]">
                        <textarea name="q_<?= e(
                            $qId,
                        ) ?>" rows="4" class="fd-textarea w-full resize-y" placeholder="Ketik jawaban essay Anda di sini..." <?= $isHistoryMode
    ? 'disabled'
    : 'required' ?>><?= e($userAnswer ?? '') ?></textarea>

                        <?php if ($isHistoryMode && isset($historyAttempt['review_status'])): ?>
                        <div class="mt-[12px]">
                            <span class="fd-chip <?= $historyAttempt['review_status'] === 'under_review'
                                ? 'fd-chip-warning'
                                : 'fd-chip-success' ?>">
                                <span class="material-symbols-outlined text-[16px]">
                                    <?= $historyAttempt['review_status'] === 'under_review' ? 'schedule' : 'verified' ?>
                                </span>
                                <span>Status: <?= $historyAttempt['review_status'] === 'under_review'
                                    ? 'Menunggu Penilaian (Under Review)'
                                    : 'Sudah Dinilai' ?></span>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>

                <?php if (!$isHistoryMode): ?>
                <div class="pt-[12px] pb-[24px]">
                    <p class="dls-feature-note rounded-[12px] bg-[#FEF3C7] p-[14px] text-[13px] font-medium text-[#92400E]" role="status">Pengiriman dan penyimpanan jawaban belum tersedia. Jawaban pada halaman ini tidak akan tersimpan.</p>
                </div>
                <?php else: ?>
                <div class="pt-[12px] pb-[24px] flex justify-center">
                    <a href="<?= $backUrl ?>" class="fd-btn fd-btn-secondary fd-btn-lg rounded-full">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        <span>Kembali ke Riwayat</span>
                    </a>
                </div>
                <?php endif; ?>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- BottomNavBar Shared Component (Desktop Sidebar + Mobile Bottom Nav) -->
    <?php
    $active_tab = isset($course['status']) && $course['status'] === 'selesai' ? 'riwayat' : 'course';
    include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <?php if (!$isHistoryMode): ?>
    <script>
        let formSubmitting = false;

        /** Menerapkan penjaga (guard) antarmuka agar pemuatan ulang browser tidak menghilangkan data. */
        window.addEventListener('beforeunload', function(e) {
            const hasAnswers = document.querySelector('#quizForm input:checked, #quizForm textarea:not(:placeholder-shown)');
            if (!formSubmitting && hasAnswers) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        /** Mengamankan proses form akibat aksi klik tidak disengaja dari baris navigasi lainnya. */
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            const hasAnswers = document.querySelector('#quizForm input:checked, #quizForm textarea:not(:placeholder-shown)');
            if (link && link.href && !link.href.includes('#') && link.target !== '_blank' && hasAnswers) {
                if (!formSubmitting) {
                    e.preventDefault();
                    if (window.DLS && window.DLS.confirm) {
                        window.DLS.confirm({
                            title: 'Yakin Ingin Meninggalkan Kuis?',
                            text: 'Jawaban yang belum disubmit akan hilang jika Anda meninggalkan halaman ini.',
                            icon: 'warning',
                            confirmText: 'Ya, Tinggalkan Kuis',
                            cancelText: 'Tetap Lanjutkan Kuis',
                            danger: true
                        }).then(function(confirmed) {
                            if (confirmed) {
                                formSubmitting = true;
                                window.location.href = link.href;
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Yakin Ingin Keluar?',
                            text: 'Jawaban yang belum tersimpan akan hilang jika Anda meninggalkan halaman ini.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d71920',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Keluar',
                            cancelButtonText: 'Tetap Disini'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                formSubmitting = true;
                                window.location.href = link.href;
                            }
                        });
                    }
                }
            }
        });

        function submitQuiz(e) {
            e.preventDefault();
            if (window.DLS && window.DLS.info) {
                window.DLS.info(
                    'Pengiriman Kuis Tersimpan',
                    'Kuis Anda telah direkam. Seluruh hasil evaluasi akan diproses dan sinkron ke dashboard kemajuan belajar Anda.'
                );
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Pengiriman Kuis Diproses',
                    text: 'Jawaban kuis Anda telah dicatat dalam sesi pembelajaran ini.',
                    confirmButtonColor: '#d71920'
                });
            }
        }
    </script>
    <?php endif; ?>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script>
        (function() {
            function onDlsReady(fn) {
                if (window.DLS) fn(window.DLS);
                else window.addEventListener('DLSReady', function() { fn(window.DLS); }, { once: true });
            }

            document.addEventListener('DOMContentLoaded', function() {
                var courseQuizForm = document.getElementById('courseQuizForm');
                if (courseQuizForm) {
                    var confirmedSubmit = false;
                    courseQuizForm.addEventListener('submit', function(e) {
                        if (confirmedSubmit) return;
                        e.preventDefault();
                        onDlsReady(function(DLS) {
                            DLS.confirm({
                                title: 'Kirim Jawaban Quiz?',
                                text: 'Pastikan seluruh jawaban sudah diperiksa. Quiz hanya dapat dikerjakan satu kali.',
                                confirmText: 'Ya, Kirim Sekarang',
                                cancelText: 'Periksa Lagi',
                                icon: 'question'
                            }).then(function(ok) {
                                if (ok) {
                                    confirmedSubmit = true;
                                    if (typeof courseQuizForm.requestSubmit === 'function') courseQuizForm.requestSubmit();
                                    else courseQuizForm.submit();
                                }
                            });
                        });
                    });
                }

                <?php if (($quizError ?? '') !== ''): ?>
                onDlsReady(function(DLS) {
                    DLS.error('Gagal Memproses Quiz', <?= json_encode((string) $quizError, JSON_UNESCAPED_UNICODE) ?>);
                });
                <?php elseif (($_GET['submitted'] ?? '') === '1' && !empty($realQuiz['attempt'])): ?>
                onDlsReady(function(DLS) {
                    <?php
                    $att = $realQuiz['attempt'];
                    $passedFlag = (float) $att['score'] >= (float) ($realQuiz['passing_score'] ?? 75);
                    $scoreFormatted = number_format((float) $att['score'], 2, ',', '.');
                    ?>
                    DLS.<?= $passedFlag ? 'success' : 'info' ?>(
                        <?= json_encode($passedFlag ? 'Selamat! Quiz Lulus' : 'Quiz Berhasil Dikirim', JSON_UNESCAPED_UNICODE) ?>,
                        <?= json_encode("Skor Anda: {$scoreFormatted} ({$att['correct_count']}/{$att['total_questions']} benar). " . ($passedFlag ? 'Nilai memenuhi standar kelulusan.' : 'Belum mencapai nilai kelulusan.'), JSON_UNESCAPED_UNICODE) ?>
                    );
                    try {
                        var url = new URL(window.location.href);
                        url.searchParams.delete('submitted');
                        window.history.replaceState({}, '', url.toString());
                    } catch (_) {}
                });
                <?php endif; ?>
            });
        })();
    </script>
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
