<?php
/** Pengaturan properti basis evaluasi diturunkan oleh Pengontrol (Controller). */

$title = '';
if ($type === 'pre_test') {
    $title = 'Pre-Test: ' . $session['title'];
} elseif ($type === 'post_test') {
    $title = 'Post-Test: ' . $session['title'];
} elseif ($type === 'quiz') {
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

if ($isHistoryMode) {
    if ($type === 'pre_test' || $type === 'post_test') {
        $title = 'Riwayat ' . $title;
    } else {
        $title = 'Riwayat ' . $title;
    }
}
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
