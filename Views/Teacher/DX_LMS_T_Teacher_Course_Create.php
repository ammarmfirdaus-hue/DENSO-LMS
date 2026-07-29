<?php
/**
 * Tampilan Wizard Buat/Edit Course (Teacher Course Create).
 *
 * Menggunakan konsep Wizard 4-Step:
 * 1. Info Course
 * 2. Struktur Materi
 * 3. Target Audience (Recommendation Filter)
 * 4. Review & Submit
 */
$isEdit = isset($course) && !empty($course);
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = ($isEdit ? 'Edit Course' : 'Buat Course Baru') . ' - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'course'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = $isEdit ? 'Edit Course' : 'Buat Course';
        $show_back_button = true;
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header -->
    <div class="hidden lg:flex lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md items-center gap-3">
        <button onclick="window.location.href='?route=/teacher/courses'" class="p-2 -ml-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95 flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
        <h1 class="font-headline-6 font-bold text-on-surface"><?= $isEdit ? 'Edit Course' : 'Buat Course Baru' ?></h1>
    </div>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[120px] pt-4 lg:pt-6">
        <div class="max-w-4xl mx-auto space-y-stack-xl relative">
            
            <?php if (($course['status'] ?? '') === 'Submitted' || ($course['status'] ?? '') === 'Approved'): ?>
                <div class="bg-warning-container/20 border border-warning/30 p-4 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-warning mt-0.5">lock</span>
                    <div>
                        <p class="font-title-sm font-bold text-on-surface">Target Audience Terkunci</p>
                        <p class="font-body-sm text-on-surface-variant">Karena course ini sudah disubmit/diapprove, Anda tidak dapat mengubah data Target Audience. Rekomendasi sedang diproses oleh Admin.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Wizard Stepper (Horizontal) -->
            <div class="bg-surface-container-lowest rounded-xl border border-surface-variant p-4 shadow-sm w-full overflow-x-auto hide-scrollbar sticky top-[68px] lg:top-[74px] z-20">
                <div class="flex items-center min-w-[500px]">
                    <?php
                    $steps = [
                        1 => ['title' => 'Informasi', 'icon' => 'info'],
                        2 => ['title' => 'Materi', 'icon' => 'library_books'],
                        3 => ['title' => 'Target Audience', 'icon' => 'groups'],
                        4 => ['title' => 'Review', 'icon' => 'done_all'],
                    ];

                    for ($i = 1; $i <= 4; $i++):

                        $isActive = $step === $i;
                        $isCompleted = $step > $i;
                        $colorClass = $isActive
                            ? 'text-primary'
                            : ($isCompleted
                                ? 'text-success'
                                : 'text-on-surface-variant');
                        $bgClass = $isActive
                            ? 'bg-primary-container text-on-primary-container'
                            : ($isCompleted
                                ? 'bg-success text-on-success'
                                : 'bg-surface-container text-on-surface-variant border border-outline-variant');
                        ?>
                        <!-- Step Bubble -->
                        <div class="flex flex-col items-center gap-1.5 relative z-10 w-24">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-[12px] <?= $bgClass ?> transition-colors">
                                <?php if ($isCompleted): ?>
                                    <span class="material-symbols-outlined text-[16px] font-bold">check</span>
                                <?php else: ?>
                                    <?= $i ?>
                                <?php endif; ?>
                            </div>
                            <span class="font-label-sm text-[10px] sm:text-[11px] font-semibold text-center leading-tight <?= $colorClass ?>">
                                <?= $steps[$i]['title'] ?>
                            </span>
                        </div>
                        
                        <!-- Connector Line -->
                        <?php if ($i < 4): ?>
                            <div class="flex-1 h-[2px] <?= $isCompleted
                                ? 'bg-success'
                                : 'bg-surface-variant' ?> -mt-5 mx-[-20px] z-0 transition-colors"></div>
                        <?php endif; ?>
                    <?php
                    endfor;
                    ?>
                </div>
            </div>

            <!-- Formulir (Simulasi State dengan Method GET Step) -->
            <form action="?route=/teacher/course/create" method="GET" class="space-y-stack-xl">
                <!-- Keep route & edit id -->
                <input type="hidden" name="route" value="/teacher/course/create">
                <?php $currentCourseId = $_GET['course_id'] ?? null; ?>
                <?php if ($currentCourseId): ?>
                    <input type="hidden" name="course_id" value="<?= e((string) $currentCourseId) ?>">
                <?php endif; ?>
                
                <!-- ====== STEP 1: INFORMASI COURSE ====== -->
                <?php if ($step === 1): ?>
                <section class="bg-surface-container-lowest border border-surface-variant rounded-2xl p-stack-lg shadow-sm">
                    <h3 class="font-headline-sm font-bold text-on-surface mb-6">Informasi Dasar Course</h3>
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="font-label-md font-bold text-on-surface">Nama Course <span class="text-error">*</span></label>
                            <input type="text" class="w-full bg-surface border border-outline-variant rounded-xl p-3 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all font-body-md" placeholder="Contoh: Fundamental 5S & K3" value="<?= e(
                                $course['title'] ?? '',
                            ) ?>">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="font-label-md font-bold text-on-surface">Kode Course</label>
                                <input type="text" class="w-full bg-surface border border-outline-variant rounded-xl p-3 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all font-body-md" placeholder="Contoh: K3-101" value="<?= e(
                                    $course['code'] ?? '',
                                ) ?>">
                            </div>
                            <div class="space-y-1.5">
                                <label class="font-label-md font-bold text-on-surface">Kategori</label>
                                <select class="w-full bg-surface border border-outline-variant rounded-xl p-3 focus:outline-none focus:border-primary transition-all font-body-md">
                                    <option>HSE</option>
                                    <option>Quality</option>
                                    <option>Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="font-label-md font-bold text-on-surface">Deskripsi Course</label>
                            <textarea rows="4" class="w-full bg-surface border border-outline-variant rounded-xl p-3 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all font-body-md" placeholder="Jelaskan tujuan dan isi course ini..."><?= e(
                                $course['description'] ?? '',
                            ) ?></textarea>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="font-label-md font-bold text-on-surface mb-2 block">Pilih Icon/Visual</label>
                            <div class="flex gap-3 flex-wrap">
                                <?php
                                $icons = [
                                    'menu_book',
                                    'health_and_safety',
                                    'manufacturing',
                                    'fact_check',
                                    'engineering',
                                    'computer',
                                ];
                                $selected = $course['icon'] ?? 'menu_book';
                                foreach ($icons as $icon):
                                    $isSel = $icon === $selected; ?>
                                <button type="button" class="w-12 h-12 rounded-xl flex items-center justify-center border <?= $isSel
                                    ? 'bg-primary-container border-primary text-primary'
                                    : 'bg-surface border-outline-variant text-on-surface-variant hover:bg-surface-variant' ?> transition-colors relative">
                                    <span class="material-symbols-outlined"><?= $icon ?></span>
                                    <?php if (
                                        $isSel
                                    ): ?><span class="absolute -top-1 -right-1 w-3 h-3 bg-primary rounded-full border-2 border-surface-container-lowest"></span><?php endif; ?>
                                </button>
                                <?php
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- ====== STEP 2: STRUKTUR MATERI ====== -->
                <?php if ($step === 2): ?>
                <section class="bg-surface-container-lowest border border-surface-variant rounded-2xl p-stack-lg shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-headline-sm font-bold text-on-surface">Struktur Materi</h3>
                        <button type="button" class="text-primary font-label-md font-bold flex items-center gap-1 hover:underline">
                            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Sesi
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Sesi 1 Dummy -->
                        <div class="border border-outline-variant/60 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-surface-container-low px-4 py-3 border-b border-outline-variant/60 flex justify-between items-center cursor-move">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-on-surface-variant hover:text-on-surface cursor-grab">drag_indicator</span>
                                    <h4 class="font-title-sm font-bold text-on-surface">Sesi 1: Dasar Materi</h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="material-symbols-outlined text-[20px] text-on-surface-variant hover:text-primary">edit</button>
                                </div>
                            </div>
                            <div class="bg-surface p-0 flex flex-col divide-y divide-surface-variant/50">
                                <div class="px-5 py-3 flex items-center gap-3 hover:bg-surface-container-lowest transition-colors">
                                    <span class="material-symbols-outlined text-primary text-[20px]">description</span>
                                    <span class="font-body-sm text-on-surface flex-1">Materi PDF Basic</span>
                                    <button type="button" class="material-symbols-outlined text-[18px] text-error hover:text-error-600 opacity-50 hover:opacity-100">delete</button>
                                </div>
                                <div class="px-5 py-3 flex items-center gap-3 bg-surface-container-lowest/50 text-center justify-center border-t-0">
                                    <button type="button" class="font-label-sm font-semibold text-secondary flex items-center gap-1 hover:underline">
                                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Modul/Quiz
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- ====== STEP 3: TARGET AUDIENCE (RECOMMENDATION) ====== -->
                <?php if ($step === 3): ?>
                <section class="bg-surface-container-lowest border border-surface-variant rounded-2xl p-stack-lg shadow-sm">
                    <div class="mb-5">
                        <h3 class="font-headline-sm font-bold text-on-surface mb-1">Usulkan Target Audience</h3>
                        <p class="font-body-md text-on-surface-variant">Gunakan filter untuk merekomendasikan karyawan ke course ini kepada Admin sesuai scope permission Anda.</p>
                    </div>

                    <?php if (($course['status'] ?? '') === 'Submitted' || ($course['status'] ?? '') === 'Approved'): ?>
                        <!-- Readonly mode -->
                        <div class="p-6 text-center border-2 border-dashed border-outline-variant rounded-xl">
                            <span class="material-symbols-outlined text-[40px] text-surface-variant mb-2">lock</span>
                            <p class="font-bold text-on-surface">Mode Hanya-Baca</p>
                            <p class="font-body-sm text-on-surface-variant">Anda telah submit 12 karyawan. Menunggu assign by Admin.</p>
                        </div>
                    <?php
                        // Dummy results
                        // Dummy results
                        // Dummy results
                        // Dummy results
                        // Dummy results
                        // Dummy results
                        // Dummy results
                        // Dummy results
                        else: ?>
                        <!-- Interactive Filter Flow -->
                        <div class="space-y-4">
                            <!-- Filter Builder -->
                            <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant/60 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($filters as $type => $options): ?>
                                    <div class="flex flex-col gap-1">
                                        <label class="font-label-sm font-bold text-on-surface capitalize"><?= $type ?></label>
                                        <select class="w-full bg-surface border border-outline-variant rounded-lg p-2 focus:outline-none focus:border-primary text-body-sm font-body-sm text-on-surface-variant">
                                            <option value="">-- Semua <?= ucfirst($type) ?> --</option>
                                            <?php foreach ($options as $opt): ?>
                                                <option value="<?= $opt ?>"><?= $opt ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                                <div class="md:col-span-2 lg:col-span-3 flex justify-end mt-2">
                                    <button type="button" class="bg-secondary text-on-secondary px-5 py-2 rounded-lg font-label-md font-bold shadow-sm hover:bg-secondary-600 transition-colors flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[18px]">search</span> Cari Karyawan
                                    </button>
                                </div>
                            </div>

                            <!-- Dummy Result List -->
                            <div class="bg-surface border border-surface-variant rounded-xl overflow-hidden">
                                <div class="bg-primary-container/20 px-4 py-2 border-b border-surface-variant flex items-center justify-between">
                                    <label class="flex items-center gap-2 font-label-sm font-bold text-on-surface cursor-pointer">
                                        <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary" checked>
                                        Pilih Semua (5 Kandidat Ditemukan)
                                    </label>
                                </div>
                                <div class="divide-y divide-surface-variant font-body-sm max-h-[300px] overflow-y-auto">
                                    <?php
                                    $emps = [
                                        [
                                            'name' => 'Budi Santoso',
                                            'npk' => '12345',
                                            'jabatan' => 'Operator',
                                            'dept' => 'Manufacturing',
                                        ],
                                        [
                                            'name' => 'Citra Lestari',
                                            'npk' => '11223',
                                            'jabatan' => 'Supervisor',
                                            'dept' => 'Quality',
                                        ],
                                        [
                                            'name' => 'Ahmad Rizal',
                                            'npk' => '67890',
                                            'jabatan' => 'Leader',
                                            'dept' => 'Manufacturing',
                                        ],
                                    ];
                                    foreach ($emps as $emp): ?>
                                    <label class="flex items-start gap-3 px-4 py-3 hover:bg-surface-container-lowest cursor-pointer transition-colors">
                                        <input type="checkbox" class="w-4 h-4 mt-0.5 rounded text-primary border-outline-variant focus:ring-primary" checked>
                                        <div>
                                            <p class="font-title-sm font-bold text-on-surface"><?= $emp[
                                                'name'
                                            ] ?> <span class="font-label-sm font-normal text-on-surface-variant">(NPK: <?= $emp[
     'npk'
 ] ?>)</span></p>
                                            <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= $emp[
                                                'jabatan'
                                            ] ?> • <?= $emp['dept'] ?></p>
                                        </div>
                                    </label>
                                    <?php endforeach;
                                    ?>
                                </div>
                                <div class="bg-surface-container-lowest px-4 py-2 text-right border-t border-surface-variant font-label-sm text-primary">
                                    3 Karyawan direkomendasikan
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ====== STEP 4: REVIEW & SUBMIT ====== -->
                <?php if ($step === 4): ?>
                <section class="bg-surface-container-lowest border border-surface-variant rounded-2xl p-stack-lg shadow-sm space-y-6">
                    <h3 class="font-headline-sm font-bold text-on-surface">Review & Submit</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-lg">
                        <div class="space-y-4">
                            <!-- Info Box -->
                            <div class="border border-outline-variant/50 rounded-xl p-4 bg-surface-container-low/30">
                                <h4 class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider mb-2">Info Course</h4>
                                <p class="font-title-md font-bold text-on-surface mb-0.5"><?= e(
                                    $course['title'] ?? 'Nama Course',
                                ) ?></p>
                                <p class="font-body-sm text-on-surface-variant"><?= e(
                                    $course['category'] ?? 'Umum',
                                ) ?> • <?= e($course['code'] ?? '-') ?></p>
                            </div>
                            <!-- Sesi Box -->
                            <div class="border border-outline-variant/50 rounded-xl p-4 bg-surface-container-low/30 flex items-center justify-between">
                                <div>
                                    <h4 class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider mb-1">Materi</h4>
                                    <p class="font-title-md font-bold text-on-surface">1 Sesi (1 Modul)</p>
                                </div>
                                <span class="material-symbols-outlined text-primary text-[32px]">library_books</span>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Target Box -->
                            <div class="border border-outline-variant/50 rounded-xl p-4 bg-surface-container-low/30">
                                <h4 class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider mb-2">Usulan Target Audience</h4>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-secondary-container text-secondary rounded-full flex items-center justify-center">
                                        <span class="material-symbols-outlined">groups</span>
                                    </div>
                                    <div>
                                        <p class="font-title-md font-bold text-on-surface">3 Karyawan</p>
                                        <p class="font-label-sm text-secondary">Telah direkomendasikan</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Info Box -->
                            <div class="bg-info-container/20 border border-info/30 rounded-xl p-4 flex gap-2 items-start mt-2">
                                <span class="material-symbols-outlined text-info text-[20px]">info</span>
                                <p class="font-label-sm text-on-surface-variant leading-snug">Menekan "Submit Recommendation" berarti mengirim draft ini ke Admin. Draft tidak bisa diubah hingga ada keputusan Admin.</p>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Wizard Navigation Actions (Sticky Bottom on Desktop, Fixed Below on Mobile) -->
                <div class="fixed bottom-0 left-0 lg:left-[240px] right-0 p-4 bg-surface border-t border-surface-variant shadow-[0_-4px_12px_rgba(0,0,0,0.02)] flex justify-between gap-3 z-40 pb-safe lg:pb-4">
                    <div class="max-w-4xl mx-auto flex w-full justify-between items-center">
                        <!-- Left Action -->
                        <div>
                            <?php if ($step > 1): ?>
                                <button type="submit" name="step" value="<?= $step -
                                    1 ?>" class="px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface font-label-md font-bold hover:bg-surface-container transition-colors">
                                    Kembali
                                </button>
                            <?php else: ?>
                                <button type="button" onclick="window.history.back()" class="px-5 py-2.5 rounded-lg text-error hover:bg-error-container hover:text-on-error-container font-label-md font-bold transition-colors">
                                    Batal
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Right Action -->
                        <div class="flex gap-2">
                            <?php if ($step === 4): ?>
                                <button type="button" onclick="window.location.href='?route=/teacher/courses'" class="px-5 py-2.5 rounded-lg text-primary font-label-md font-bold hover:bg-primary-container transition-colors hidden sm:block">
                                    Simpan Draft
                                </button>
                                <button type="button" onclick="alert('Recommendation Submitted!'); window.location.href='?route=/teacher/course/detail&course_id=<?= e(
                                    $course['id'] ?? 1,
                                ) ?>';" class="px-5 py-2.5 rounded-lg bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-sm transition-colors flex items-center gap-2">
                                    Submit Recommendation <span class="material-symbols-outlined text-[18px]">send</span>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="step" value="<?= $step +
                                    1 ?>" class="px-7 py-2.5 rounded-lg bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-sm transition-colors flex items-center gap-1">
                                    Selanjutnya <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
    </main>

    <!-- JS untuk handle form interaktif simulasi -->
    <script>
        // Form wizard normally doesn't submit standard POST inside GET mock, 
        // using browser GET params is fine for mockup stepping.
    </script>
</body>
</html>
