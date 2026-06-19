<?php
// $course, $session, $quizData, $type dilemparkan dari controller

$title = '';
if ($type === 'pre_test') $title = 'Pre-Test: ' . $session['title'];
elseif ($type === 'post_test') $title = 'Post-Test: ' . $session['title'];
elseif ($type === 'quiz') $title = 'Quiz Course: ' . $course['title'];
elseif ($type === 'final_assessment') $title = 'Final Assessment: ' . $course['title'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'LMS - ' . e($title);
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        
        .radio-custom:checked + div {
            border-color: #2563EB; /* primary */
            background-color: #EFF6FF; /* primary-container/20 roughly */
        }
        .radio-custom:checked + div .radio-inner {
            background-color: #2563EB;
            transform: scale(1);
        }
    </style>
</head>
<body class="bg-surface text-on-surface antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin h-14 sticky top-0 z-40 shadow-sm">
        <a href="?route=/student/course/detail&id=<?= e($course['id']) ?>" class="p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-all duration-200 active:scale-95 text-on-surface-variant">
            <span class="material-symbols-outlined">close</span>
        </a>
        <h1 class="font-headline-sm text-headline-sm font-bold text-primary tracking-tight truncate px-2 text-center flex-1"><?= e($title) ?></h1>
        <div class="w-10"></div> <!-- Spacer -->
    </header>

    <main class="flex-1 w-full bg-surface-container-low p-container-margin">
        <div class="max-w-3xl mx-auto mt-4 mb-8">
            <div class="bg-surface rounded-2xl p-6 shadow-sm border border-outline-variant/30 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full <?= $type === 'final_assessment' ? 'bg-tertiary-container text-tertiary' : 'bg-primary-container text-primary' ?> flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">
                            <?= $type === 'final_assessment' ? 'military_tech' : 'assignment' ?>
                        </span>
                    </div>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Kerjakan Ujian</h2>
                        <p class="font-label-sm text-on-surface-variant mt-1">Total <?= count($quizData['questions']) ?> Pertanyaan</p>
                    </div>
                </div>
                <div class="bg-secondary-container/20 border-l-4 border-secondary p-4 rounded-r-lg font-body-md text-on-surface-variant">
                    <p>Pilih salah satu jawaban yang paling tepat untuk setiap pertanyaan. Nilai <?= $type === 'final_assessment' ? 'kelulusan' : 'minimal' ?> adalah 75. Selamat mengerjakan!</p>
                </div>
            </div>

            <!-- Formulir Soal -->
            <form id="quizForm" class="space-y-6" onsubmit="submitQuiz(event)">
                <?php foreach ($quizData['questions'] as $index => $q): ?>
                    <?php 
                        $isMock = !is_array($q);
                        $questionText = $isMock ? "Pertanyaan evaluasi nomor ".($index+1)." untuk bagian ini?" : $q['question'];
                        $options = $isMock ? ["Sangat Setuju", "Setuju", "Kurang Setuju", "Tidak Setuju"] : $q['options'];
                        $qId = $isMock ? $q : $q['id'];
                    ?>
                    <div class="bg-surface rounded-xl p-5 shadow-[0_4px_12px_0_rgba(0,0,0,0.02)] border border-outline-variant/50">
                        <p class="font-headline-sm text-on-surface mb-4">
                            <span class="font-bold text-primary mr-1"><?= $index + 1 ?>.</span> 
                            <?= e($questionText) ?>
                        </p>
                        <div class="flex flex-col gap-3">
                            <?php foreach ($options as $optIndex => $option): ?>
                                <label class="relative flex cursor-pointer group">
                                    <input type="radio" name="q_<?= e($qId) ?>" value="<?= e($option) ?>" class="peer sr-only radio-custom" required>
                                    <div class="w-full flex items-center p-4 border border-outline-variant rounded-lg transition-all duration-200 hover:bg-surface-container-lowest">
                                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant mr-3 flex items-center justify-center shrink-0 transition-colors group-hover:border-primary peer-checked:border-primary">
                                            <div class="radio-inner w-2.5 h-2.5 rounded-full bg-transparent transform scale-0 transition-transform duration-200"></div>
                                        </div>
                                        <span class="font-body-md text-on-surface"><?= e($option) ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="pt-6 pb-10">
                    <button type="submit" class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-lg font-bold shadow-md hover:bg-primary/90 hover:shadow-lg transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <span>Kumpulkan Jawaban</span>
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function submitQuiz(e) {
            e.preventDefault();
            
            Swal.fire({
                icon: 'success',
                title: 'Selesai!',
                text: 'Jawaban Anda telah berhasil dikirim. Anda akan diarahkan kembali ke halaman course.',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then(() => {
                window.location.href = "?route=/student/course/detail&id=<?= e($course['id']) ?>";
            });
        }
    </script>
</body>
</html>
