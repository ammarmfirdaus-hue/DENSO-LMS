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
        <h1 class="font-headline-sm text-headline-sm font-bold text-primary tracking-tight leading-tight px-2 text-center flex-1"><?= e(
            $title,
        ) ?></h1>
        <div class="w-10"></div> <!-- Spacer -->
    </header>

    <main class="dls-main dls-quiz-main flex-1 w-full bg-surface-container-low p-container-margin">
        <div class="max-w-3xl mx-auto mt-4 mb-8">
            <?php if ($isRealCourseQuiz): ?>
            <div class="mb-5 rounded-2xl border border-outline-variant/50 bg-surface p-5 shadow-sm sm:p-7">
                <div class="flex items-start gap-4"><span class="material-symbols-outlined grid size-12 shrink-0 place-items-center rounded-xl bg-primary text-2xl text-white">quiz</span><div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-primary"><?= e($course['title']) ?></p><h2 class="mt-1 text-2xl font-bold text-on-surface"><?= e($realQuiz['title'] ?? 'Quiz Course') ?></h2><p class="mt-2 text-sm leading-6 text-on-surface-variant"><?= e($realQuiz['description'] ?? '') ?></p></div></div>
                <?php if (!empty($realQuiz['has_quiz'])): ?><div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 border-t border-outline-variant/40 pt-4 text-sm text-on-surface-variant"><span><strong class="text-on-surface"><?= (int) ($realQuiz['question_count'] ?? 0) ?></strong> pertanyaan</span><span>Nilai kelulusan <strong class="text-on-surface"><?= e($realQuiz['passing_score'] ?? 75) ?></strong></span><span>Satu kali pengerjaan</span></div><?php endif; ?>
            </div>
            <?php if ($quizError !== ''): ?><p role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($quizError) ?></p><?php endif; ?>
            <?php if (empty($realQuiz['has_quiz'])): ?>
            <section class="rounded-2xl border border-outline-variant/50 bg-surface p-7 text-center"><span class="material-symbols-outlined text-4xl text-on-surface-variant">assignment_late</span><h3 class="mt-2 text-lg font-bold">Quiz belum tersedia</h3><p class="mt-1 text-sm text-on-surface-variant">Pengajar belum menerbitkan quiz untuk course ini.</p></section>
            <?php elseif (!empty($realQuiz['attempt'])): ?>
            <?php $attempt = $realQuiz['attempt']; $passed = (float) $attempt['score'] >= (float) ($realQuiz['passing_score'] ?? 75); ?>
            <section class="rounded-2xl border border-outline-variant/50 bg-surface p-5 shadow-sm sm:p-7"><div class="flex flex-col items-center gap-3 text-center"><span class="material-symbols-outlined grid size-16 place-items-center rounded-full <?= $passed ? 'bg-primary text-white' : 'bg-surface-container-high text-on-surface' ?> text-3xl"><?= $passed ? 'task_alt' : 'fact_check' ?></span><div><p class="text-sm font-semibold text-on-surface-variant">Quiz sudah dikirim</p><p class="mt-1 text-4xl font-bold text-primary"><?= e(number_format((float) $attempt['score'], 2, ',', '.')) ?></p><p class="mt-1 text-sm font-bold <?= $passed ? 'text-primary' : 'text-on-surface-variant' ?>"><?= $passed ? 'Memenuhi nilai kelulusan' : 'Belum mencapai nilai kelulusan' ?></p></div></div><div class="mt-6 grid gap-3 border-t border-outline-variant/40 pt-5 text-center sm:grid-cols-2"><p class="rounded-xl bg-surface-container-low p-3 text-sm"><strong class="block text-lg text-on-surface"><?= (int) $attempt['correct_count'] ?> / <?= (int) $attempt['total_questions'] ?></strong>Jawaban benar</p><p class="rounded-xl bg-surface-container-low p-3 text-sm"><strong class="block text-lg text-on-surface"><?= e(date('d M Y H:i', strtotime((string) $attempt['submitted_at']))) ?></strong>Waktu pengiriman</p></div><p class="mt-4 text-center text-sm text-on-surface-variant">Percobaan quiz sudah digunakan dan tidak dapat diulang.</p><div class="mt-5 flex justify-center"><a href="<?= e($backUrl) ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-5 font-semibold text-white"><span class="material-symbols-outlined">arrow_back</span>Kembali ke Course</a></div></section>
            <?php elseif (($realQuiz['status'] ?? 'terkunci') !== 'belum_dimulai'): ?>
            <section class="rounded-2xl border border-outline-variant/50 bg-surface p-7 text-center"><span class="material-symbols-outlined text-4xl text-on-surface-variant">lock</span><h3 class="mt-2 text-lg font-bold"><?= in_array(($realQuiz['publication_status'] ?? ''), ['draft', 'inactive'], true) ? 'Quiz belum dapat dikerjakan' : 'Quiz masih terkunci' ?></h3><p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-on-surface-variant"><?php if (in_array(($realQuiz['publication_status'] ?? ''), ['draft', 'inactive'], true)): ?>Pengajar belum menerbitkan quiz ini.<?php else: ?>Selesaikan dan tandai seluruh <?= (int) ($realQuiz['total_materials'] ?? 0) ?> materi course sebagai selesai. Saat ini <?= (int) ($realQuiz['completed_materials'] ?? 0) ?> materi sudah selesai.<?php endif; ?></p><a href="<?= e($backUrl) ?>" class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-outline/60 bg-white px-5 font-semibold text-on-surface hover:bg-surface-container-low"><span class="material-symbols-outlined">menu_book</span>Kembali ke Materi</a></section>
            <?php else: ?>
            <form id="courseQuizForm" method="post" action="?route=/student/quiz&amp;id=<?= rawurlencode((string) $course['id']) ?>&amp;type=quiz" class="space-y-4">
                <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
                <?php foreach ($realQuiz['questions'] as $index => $question): ?>
                <fieldset class="rounded-2xl border border-outline-variant/50 bg-surface p-5 shadow-sm sm:p-6"><legend class="sr-only">Pertanyaan <?= $index + 1 ?></legend><div class="mb-4 flex items-start gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-primary text-sm font-bold text-white"><?= $index + 1 ?></span><p class="pt-1 font-semibold leading-6 text-on-surface"><?= e($question['question']) ?><span class="ml-1 text-primary" aria-label="Wajib diisi">*</span></p></div><div class="space-y-2 pl-0 sm:pl-11"><?php foreach ($question['options'] as $optionIndex => $option): ?><label class="flex cursor-pointer items-start gap-3 rounded-xl border border-outline-variant/50 p-3 transition hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary/5"><input type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $optionIndex ?>" required class="mt-1 size-4 shrink-0 accent-primary" <?= (string) ($submittedAnswers[(string) $question['id']] ?? '') === (string) $optionIndex ? 'checked' : '' ?>><span class="text-sm leading-6 text-on-surface"><?= e($option) ?></span></label><?php endforeach; ?></div></fieldset>
                <?php endforeach; ?>
                <div class="sticky bottom-2 flex flex-col gap-3 rounded-2xl border border-outline-variant/50 bg-surface p-4 shadow-lg sm:flex-row sm:items-center sm:justify-between"><p class="text-xs leading-5 text-on-surface-variant">Periksa kembali jawaban sebelum dikirim. Quiz hanya dapat dikerjakan satu kali.</p><button type="submit" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-5 font-bold text-white hover:bg-primary/90"><span class="material-symbols-outlined">send</span>Kirim Jawaban</button></div>
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
            <!-- Active Quiz Header -->
            <div class="bg-surface rounded-2xl p-6 shadow-sm border border-outline-variant/30 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full <?= $type === 'final_assessment'
                        ? 'bg-tertiary text-white'
                        : 'bg-primary text-white' ?> flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">
                            <?= $type === 'final_assessment' ? 'military_tech' : 'assignment' ?>
                        </span>
                    </div>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Kerjakan Ujian</h2>
                        <p class="font-label-sm text-on-surface-variant mt-1">Total <?= $totalQuestions ?> Pertanyaan &bull; Bobot Maks: 100</p>
                    </div>
                </div>
                <div class="bg-secondary-container/20 border-l-4 border-secondary p-4 rounded-r-lg font-body-md text-on-surface-variant">
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
                <div class="bg-surface rounded-xl p-5 shadow-[0_4px_12px_0_rgba(0,0,0,0.02)] border <?= $isHistoryMode &&
                $isPg
                    ? ($isCorrect
                        ? 'border-green-300'
                        : 'border-red-300')
                    : 'border-outline-variant/50' ?>">

                    <!-- Header Soal -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex gap-2 items-center">
                            <span class="bg-surface-container text-on-surface-variant text-xs font-bold px-2 py-1 rounded-md">Soal <?= $index +
                                1 ?></span>
                            <?php if ($isPg): ?>
                            <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 rounded-md border border-blue-200">Pilihan Ganda</span>
                            <?php else: ?>
                            <span class="bg-purple-50 text-purple-700 text-xs font-bold px-2 py-1 rounded-md border border-purple-200">Essay</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-on-surface-variant text-xs font-bold px-2 py-1 rounded-md bg-surface-container-low border border-outline-variant">
                            Bobot: <?= $q['weight'] ?>
                        </div>
                    </div>

                    <!-- Pertanyaan -->
                    <p class="font-headline-sm text-on-surface mb-4 leading-relaxed">
                        <?= e($q['question']) ?>
                    </p>

                    <!-- Gambar Soal -->
                    <?php if ($hasImage && !empty($q['image_url'])): ?>
                    <div class="mb-5 rounded-lg overflow-hidden border border-outline-variant bg-surface-container-lowest">
                        <img src="<?= e(
                            $q['image_url'],
                        ) ?>" alt="Ilustrasi Soal" class="w-full h-auto object-cover max-h-[300px]">
                    </div>
                    <?php endif; ?>

                    <!-- Opsi Jawaban (PG) -->
                    <?php if ($isPg && !empty($q['options'])): ?>
                    <div class="flex flex-col gap-3">
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
                                ? 'flex-col items-start gap-3'
                                : 'items-center' ?> p-4 border border-outline-variant rounded-lg transition-all duration-200 <?= !$isHistoryMode
     ? 'hover:bg-surface-container-lowest'
     : '' ?>">
                                <div class="flex items-center w-full">
                                    <div class="w-5 h-5 rounded-full border-2 border-outline-variant mr-3 flex items-center justify-center shrink-0 transition-colors <?= !$isHistoryMode
                                        ? 'group-hover:border-primary peer-checked:border-primary'
                                        : '' ?>">
                                        <div class="radio-inner w-2.5 h-2.5 rounded-full bg-transparent transform scale-0 transition-transform duration-200"></div>
                                    </div>
                                    <span class="font-body-md text-on-surface <?= $isImageOption ? '' : 'flex-1' ?>">
                                        <span class="font-bold mr-2"><?= e($optKey) ?>.</span>
                                        <?php if (!$isImageOption): ?>
                                        <?= e($option) ?>
                                        <?php endif; ?>
                                    </span>

                                    <?php if ($isHistoryMode && $optKey === $q['correct_answer']): ?>
                                    <span class="material-symbols-outlined text-green-600 shrink-0 ml-auto">check_circle</span>
                                    <?php elseif ($isHistoryMode && $checked && !$isCorrect): ?>
                                    <span class="material-symbols-outlined text-red-600 shrink-0 ml-auto">cancel</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isImageOption): ?>
                                <div class="pl-8 w-full mt-1">
                                    <img src="<?= e($option) ?>" alt="Opsi <?= e(
    $optKey,
) ?>" class="w-full max-w-[200px] h-auto object-cover rounded-md border border-outline-variant shadow-sm transition-transform duration-200 <?= !$isHistoryMode
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
                    <div class="mt-2">
                        <textarea name="q_<?= e(
                            $qId,
                        ) ?>" rows="4" class="w-full p-4 border border-outline-variant rounded-lg bg-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md text-on-surface resize-y" placeholder="Ketik jawaban essay Anda di sini..." <?= $isHistoryMode
    ? 'disabled'
    : 'required' ?>><?= e($userAnswer ?? '') ?></textarea>

                        <?php if ($isHistoryMode && isset($historyAttempt['review_status'])): ?>
                        <div class="mt-3 flex items-center gap-2 text-sm <?= $historyAttempt['review_status'] ===
                        'under_review'
                            ? 'text-orange-600'
                            : 'text-green-600' ?>">
                            <span class="material-symbols-outlined text-[18px]">
                                <?= $historyAttempt['review_status'] === 'under_review' ? 'schedule' : 'verified' ?>
                            </span>
                            <span>Status: <?= $historyAttempt['review_status'] === 'under_review'
                                ? 'Menunggu Penilaian (Under Review)'
                                : 'Sudah Dinilai' ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>

                <?php if (!$isHistoryMode): ?>
                <div class="pt-6 pb-10">
                    <p class="dls-feature-note" role="status">Pengiriman dan penyimpanan jawaban belum tersedia. Jawaban pada halaman ini tidak akan tersimpan.</p>
                </div>
                <?php else: ?>
                <div class="pt-6 pb-10 flex justify-center">
                    <a href="<?= $backUrl ?>" class="px-6 py-3 border border-outline text-on-surface rounded-xl font-label-lg font-bold hover:bg-surface-container transition-all flex items-center gap-2">
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
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
