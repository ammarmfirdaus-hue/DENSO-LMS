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

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = $isEdit ? 'Edit Course' : 'Buat Course Baru';
    $show_back_button = true;
    $back_url = '?route=/teacher/courses';
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content Canvas (Full-Width Fluid Layout without Empty Margins) -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-4 sm:px-6 lg:px-8 pb-[120px] pt-[72px]">
        <div class="w-full space-y-stack-xl relative">
            
            <?php if (($course['status'] ?? '') === 'Submitted' || ($course['status'] ?? '') === 'Approved'): ?>
                <div class="bg-warning-container/20 border border-warning/30 p-4 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-warning mt-0.5">lock</span>
                    <div>
                        <p class="font-title-sm font-bold text-on-surface">Target Audience Terkunci</p>
                        <p class="font-body-sm text-on-surface-variant">Karena course ini sudah disubmit/diapprove, Anda tidak dapat mengubah data Target Audience. Rekomendasi sedang diproses oleh Admin.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Wizard Stepper (Horizontal - Enterprise Grade) -->
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/80 p-5 shadow-sm w-full overflow-x-auto hide-scrollbar relative z-20 mb-8 mt-8">
                <div class="flex items-center justify-between min-w-[640px] px-2">
                    <?php
                    $steps = [
                        1 => ['title' => 'Informasi', 'subtitle' => 'Identitas & Meta', 'icon' => 'info'],
                        2 => ['title' => 'Course', 'subtitle' => 'Struktur & Kuis', 'icon' => 'library_books'],
                        3 => ['title' => 'Peserta Course', 'subtitle' => 'Rekomendasi Target', 'icon' => 'groups'],
                        4 => ['title' => 'Review', 'subtitle' => 'Verifikasi & Kirim', 'icon' => 'done_all'],
                    ];

                    for ($i = 1; $i <= 4; $i++):

                        $isActive = $step === $i;
                        $isCompleted = $step > $i;
                        $colorClass = $isActive
                            ? 'text-primary font-bold'
                            : ($isCompleted
                                ? 'text-success font-bold'
                                : 'text-on-surface-variant font-medium');
                        $bgClass = $isActive
                            ? 'bg-primary text-on-primary shadow-md shadow-primary/25 ring-4 ring-primary/10'
                            : ($isCompleted
                                ? 'bg-success text-on-success shadow-sm'
                                : 'bg-surface-container text-on-surface-variant border border-outline-variant');
                        ?>
                        <!-- Step Item -->
                        <div class="flex flex-col items-center gap-1.5 relative z-10 w-32 group cursor-default">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-[14px] <?= $bgClass ?> transition-all duration-300">
                                <?php if ($isCompleted): ?>
                                    <span class="material-symbols-outlined text-[20px] font-bold">check</span>
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-[20px]"><?= $steps[$i]['icon'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mt-1">
                                <p class="font-label-sm text-[12px] sm:text-[13px] leading-tight <?= $colorClass ?>">
                                    <?= $i ?>. <?= $steps[$i]['title'] ?>
                                </p>
                                <p class="text-[10px] sm:text-[11px] text-on-surface-variant/80 mt-0.5 leading-none">
                                    <?= $steps[$i]['subtitle'] ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Connector Line -->
                        <?php if ($i < 4): ?>
                            <div class="flex-1 h-[3px] rounded-full <?= $isCompleted
                                ? 'bg-success'
                                : 'bg-surface-variant/60' ?> -mt-8 mx-[-16px] z-0 transition-colors duration-300"></div>
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
                <section class="bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm">
                    <!-- Section Header -->
                    <div class="flex items-start gap-4 mb-6 pb-6 border-b border-outline-variant/60">
                        <div class="w-12 h-12 rounded-2xl bg-primary-container text-white flex items-center justify-center shrink-0 shadow-sm ring-4 ring-primary/5">
                            <span class="material-symbols-outlined text-[26px]">auto_stories</span>
                        </div>
                        <div>
                            <h3 class="font-headline-sm font-bold text-on-surface">Informasi Dasar Course</h3>
                            <p class="font-body-sm text-on-surface-variant mt-0.5">Lengkapi identitas utama, deskripsi kompetensi, serta klasifikasi kategori untuk mempermudah identifikasi materi.</p>
                        </div>
                    </div>
                    <div class="w-full">
                        <div class="space-y-6">
                            <!-- Nama Course Field -->
                            <div class="space-y-1.5">
                                <label class="font-label-md font-bold text-on-surface flex items-center gap-1">
                                    Nama Course <span class="text-error">*</span>
                                </label>
                                <input type="text" class="w-full bg-surface border border-outline-variant rounded-xl p-3.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50 shadow-sm" placeholder="Contoh: Fundamental 5S & K3" value="<?= e(
                                    $course['title'] ?? '',
                                ) ?>">
                                <p class="text-[12px] text-on-surface-variant/80 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-primary">info</span> Disertai dengan standar kompetensi operasional PT Denso Indonesia.
                                </p>
                            </div>
                            
                            <!-- Deskripsi Course Field -->
                            <div class="space-y-1.5">
                                <label class="font-label-md font-bold text-on-surface flex items-center gap-1">
                                    Deskripsi Course
                                </label>
                                <textarea rows="4" class="w-full bg-surface border border-outline-variant rounded-xl p-3.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50 shadow-sm leading-relaxed" placeholder="Jelaskan tujuan kompetensi, sasaran pembelajaran, dan manfaat setelah menyelesaikan course ini..."><?= e(
                                    $course['description'] ?? '',
                                ) ?></textarea>
                                <p class="text-[12px] text-on-surface-variant/80 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">description</span> Ringkasan akan dipratinjau pada katalog dan halaman detail peserta.
                                </p>
                            </div>
                            
                            <!-- Tags Multi-Select Field -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="font-label-md font-bold text-on-surface flex items-center gap-2">
                                        Tags <span class="text-on-surface-variant font-normal text-xs">(Pilih kategori relevan)</span>
                                    </label>
                                    
                                    <!-- Custom Searchable Dropdown Multi-Select -->
                                    <div class="relative" id="tags-dropdown-container">
                                        <!-- Selected Tags Display & Trigger -->
                                        <div id="tags-trigger" class="min-h-[48px] bg-surface-container-lowest border border-outline-variant rounded-xl p-2.5 flex flex-wrap gap-2 items-center cursor-pointer hover:border-primary transition-all shadow-sm">
                                            <!-- Selected tags will be injected here by JS -->
                                            <div id="tags-placeholder" class="text-on-surface-variant font-body-md px-2 py-1">Pilih tags...</div>
                                            <div class="ml-auto flex items-center pr-2">
                                                <span class="material-symbols-outlined text-on-surface-variant text-[20px]">expand_more</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Dropdown Menu -->
                                        <div id="tags-menu" class="absolute left-0 w-full mt-2 bg-surface-container border border-outline-variant rounded-xl shadow-lg z-50 hidden flex flex-col max-h-[300px] overflow-hidden">
                                            <div class="p-3 border-b border-outline-variant sticky top-0 bg-surface-container rounded-t-xl z-20">
                                                <div class="relative">
                                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                                                    <input type="text" id="tags-search" class="w-full bg-surface border border-outline-variant rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-primary transition-all" placeholder="Cari tag, misal: Quality, Safety...">
                                                </div>
                                            </div>
                                            <div class="overflow-y-auto p-2 flex flex-col gap-1 flex-1 overscroll-contain" id="tags-options">
                                                <?php
                                                $availableTags = [
                                                    'Automation',
                                                    'Compliance',
                                                    'Core',
                                                    'Culture',
                                                    'Engineering',
                                                    'HR',
                                                    'Leadership',
                                                    'Maintenance',
                                                    'Management',
                                                    'Problem Solving',
                                                    'Productivity',
                                                    'Quality',
                                                    'Safety',
                                                    'Soft Skill',
                                                ];
                                                foreach ($availableTags as $tag): ?>
                                                <label class="tag-option flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-surface-container-low cursor-pointer transition-colors" data-value="<?= strtolower(
                                                    $tag,
                                                ) ?>">
                                                    <input type="checkbox" class="tag-checkbox w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary" value="<?= $tag ?>">
                                                    <span class="font-body-md text-on-surface flex-1"><?= $tag ?></span>
                                                </label>
                                                <?php endforeach;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[12px] text-on-surface-variant/80 mt-1">
                                        Pilih satu atau lebih kategori yang relevan agar mempermudah kurasi dan filter course.
                                    </p>
                                </div>
                            </div>
                        </div>
                </section>
                <?php endif; ?>

                <!-- ====== STEP 2: STRUKTUR COURSE ====== -->
                <?php if ($step === 2): ?>
                <section class="bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm">
                    <!-- Section Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-6 border-b border-outline-variant/60">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-primary-container text-white flex items-center justify-center shrink-0 shadow-sm ring-4 ring-primary/5">
                                <span class="material-symbols-outlined text-[26px]">account_tree</span>
                            </div>
                            <div>
                                <h3 class="font-headline-sm font-bold text-on-surface">Struktur Kurikulum & Materi</h3>
                                <p class="font-body-sm text-on-surface-variant mt-0.5">Susun sesi pembelajaran, unggah materi SOP/PDF, dan konfigurasi soal evaluasi kuis.</p>
                            </div>
                        </div>
                        <button type="button" onclick="addSessionBlock()" class="px-4 py-2.5 bg-primary/10 text-primary hover:bg-primary hover:text-on-primary rounded-xl font-label-md font-bold flex items-center gap-2 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[20px]">add_circle</span> Tambah Sesi
                        </button>
                    </div>
                    
                    <div class="space-y-4" id="sessions-container">
                        <!-- Sesi 1 Dummy -->
                        <div class="session-block border border-outline-variant/60 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-surface-container-low px-4 py-3 border-b border-outline-variant/60 flex justify-between items-center cursor-move">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-on-surface-variant hover:text-on-surface cursor-grab">drag_indicator</span>
                                    <h4 class="font-title-sm font-bold text-on-surface">Sesi 1: Dasar Materi</h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="editSessionTitle(this)" class="material-symbols-outlined text-[20px] text-on-surface-variant hover:text-primary transition-colors" title="Edit Judul Sesi">edit</button>
                                    <button type="button" onclick="deleteSessionBlock(this)" class="material-symbols-outlined text-[20px] text-on-surface-variant hover:text-error transition-colors" title="Hapus Sesi">delete</button>
                                </div>
                            </div>
                            <div class="bg-surface border-b border-surface-variant flex w-full overflow-hidden session-tabs">
                                <button type="button" onclick="switchSessionTab(this, 'materi')" class="session-tab-btn flex-1 py-3 px-4 border-b-2 border-primary text-primary font-label-sm font-bold whitespace-nowrap text-center transition-colors flex items-center justify-center gap-2" data-tab="materi">
                                    <span class="material-symbols-outlined text-[18px]">menu_book</span>
                                    <span>Materi Pembelajaran</span>
                                </button>
                                <button type="button" onclick="switchSessionTab(this, 'quiz')" class="session-tab-btn flex-1 py-3 px-4 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-colors font-label-sm font-bold whitespace-nowrap text-center flex items-center justify-center gap-2" data-tab="quiz">
                                    <span class="material-symbols-outlined text-[18px]">quiz</span>
                                    <span>Quiz & Evaluasi</span>
                                </button>
                            </div>
                            
                            <!-- Tab Content: Materi Workspace -->
                            <div class="bg-surface-container-lowest p-5 flex flex-col session-workspace" data-workspace="materi">
                                <!-- Dynamic List -->
                                <div class="materi-builder-list space-y-3 mb-5 empty:hidden">
                                    <!-- Pre-filled Sample -->
                                    <div class="module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group">
                                        <div class="w-10 h-10 rounded-lg bg-secondary-container text-secondary flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined">quiz</span>
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <p class="font-label-sm text-secondary mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold">Pre-Test</p>
                                            <input type="text" onkeydown="handleModuleTitleEnter(event, this)" onblur="handleModuleTitleEnter(null, this, true)" class="w-full bg-transparent border-b border-transparent hover:border-outline focus:border-primary outline-none font-title-sm font-bold text-on-surface pb-1 text-[13px] sm:text-[14px] transition-all" value="Pre-Test Dasar Evaluasi" placeholder="Judul Modul...">
                                        </div>
                                        <button type="button" onclick="openAssessmentBuilder(this)" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Edit Isi Soal">settings</button>
                                        <button type="button" onclick="const list = this.closest('.materi-builder-list'); this.closest('.module-block').remove(); checkEmptyHintList(list);" class="material-symbols-outlined text-on-surface-variant hover:text-error transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Hapus Modul">delete</button>
                                    </div>
                                    <div class="module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group">
                                        <div class="w-10 h-10 rounded-lg bg-primary-container text-white flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined">description</span>
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <p class="font-label-sm text-primary mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold">Materi</p>
                                            <input type="text" onkeydown="handleModuleTitleEnter(event, this)" onblur="handleModuleTitleEnter(null, this, true)" class="w-full bg-transparent border-b border-transparent hover:border-outline focus:border-primary outline-none font-title-sm font-bold text-on-surface pb-1 text-[13px] sm:text-[14px] transition-all" value="Materi PDF SOP Standar" placeholder="Judul Modul...">
                                        </div>
                                        <button type="button" onclick="openMaterialBuilder(this)" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Edit Isi Materi">settings</button>
                                        <button type="button" onclick="const list = this.closest('.materi-builder-list'); this.closest('.module-block').remove(); checkEmptyHintList(list);" class="material-symbols-outlined text-on-surface-variant hover:text-error transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Hapus Modul">delete</button>
                                    </div>
                                </div>
                                
                                <!-- Empty State Hint -->
                                <div class="materi-empty-hint hidden flex-col items-center justify-center py-6 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[48px] text-surface-variant mb-2">auto_awesome_mosaic</span>
                                    <p class="font-bold font-title-md text-on-surface">Belum ada modul</p>
                                    <p class="font-body-sm max-w-sm mt-1">Tambahkan pre-test, materi, atau post-test di bawah ini untuk menyusun sesi pembelajaran.</p>
                                </div>

                                <!-- Action Toolbar -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-dashed border-outline-variant">
                                    <button type="button" onclick="appendModuleBlock(this, 'Pre-Test', 'quiz', 'secondary')" class="flex flex-col items-center justify-center gap-1.5 p-3 border border-outline-variant rounded-lg hover:bg-secondary/5 hover:border-secondary transition-all group">
                                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-secondary group-hover:scale-110 transition-transform">add_circle</span>
                                        <span class="font-label-sm font-bold text-on-surface group-hover:text-secondary">Pre-Test</span>
                                    </button>
                                    <button type="button" onclick="appendModuleBlock(this, 'Materi', 'description', 'primary')" class="flex flex-col items-center justify-center gap-1.5 p-3 border border-outline-variant rounded-lg hover:bg-primary/5 hover:border-primary transition-all group">
                                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary group-hover:scale-110 transition-transform">add_circle</span>
                                        <span class="font-label-sm font-bold text-on-surface group-hover:text-primary">Materi</span>
                                    </button>
                                    <button type="button" onclick="appendModuleBlock(this, 'Post-Test', 'task', 'warning')" class="flex flex-col items-center justify-center gap-1.5 p-3 border border-outline-variant rounded-lg hover:bg-warning/5 hover:border-warning transition-all group">
                                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-warning group-hover:scale-110 transition-transform">add_circle</span>
                                        <span class="font-label-sm font-bold text-on-surface group-hover:text-warning">Post-Test</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tab Content: Quiz Workspace -->
                            <div class="bg-surface-container-lowest p-5 flex-col hidden" data-workspace="quiz">
                                <!-- Quiz Items List -->
                                <div class="quiz-items-list space-y-3 mb-5">
                                    <!-- Quiz Course -->
                                    <div class="quiz-module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group">
                                        <div class="w-10 h-10 rounded-lg bg-primary-container text-white flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined">assignment</span>
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <p class="font-label-sm text-primary mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold">Quiz Course</p>
                                            <p class="font-body-sm text-on-surface-variant"><span class="qz-question-count font-bold text-on-surface">0</span> Pertanyaan</p>
                                        </div>
                                        <button type="button" onclick="openQuizBuilder(this, 'quiz')" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary text-on-primary rounded-lg font-label-sm font-bold hover:bg-primary-600 transition-colors shadow-sm lg:opacity-0 group-hover:opacity-100 focus:opacity-100">
                                            <span class="material-symbols-outlined text-[16px]">edit</span> Kelola
                                        </button>
                                    </div>
                                    <!-- Final Assessment -->
                                    <div class="quiz-module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group">
                                        <div class="w-10 h-10 rounded-lg bg-error-container text-error flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined">verified</span>
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <p class="font-label-sm text-error mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold">Final Assessment</p>
                                            <p class="font-body-sm text-on-surface-variant"><span class="qz-question-count font-bold text-on-surface">0</span> Pertanyaan</p>
                                        </div>
                                        <button type="button" onclick="openQuizBuilder(this, 'final')" class="flex items-center gap-1.5 px-3 py-1.5 bg-error text-on-error rounded-lg font-label-sm font-bold hover:bg-error/80 transition-colors shadow-sm lg:opacity-0 group-hover:opacity-100 focus:opacity-100">
                                            <span class="material-symbols-outlined text-[16px]">edit</span> Kelola
                                        </button>
                                    </div>
                                </div>

                                <!-- Info Note -->
                                <div class="flex items-start gap-3 p-4 bg-primary/5 border border-primary/20 rounded-xl mt-2">
                                    <span class="material-symbols-outlined text-primary text-[20px] mt-0.5 shrink-0">info</span>
                                    <div>
                                        <p class="font-body-sm text-on-surface leading-relaxed"><span class="font-bold">Quiz Course</span> dapat diakses peserta kapan saja sebagai latihan evaluasi mandiri, sedangkan <span class="font-bold">Final Assessment</span> merupakan ujian akhir yang menentukan kelulusan kompetensi.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- ====== STEP 3: TARGET AUDIENCE (RECOMMENDATION) ====== -->
                <?php if ($step === 3): ?>
                <section class="bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm">
                    <!-- Section Header -->
                    <div class="flex items-start gap-4 mb-6 pb-6 border-b border-outline-variant/60">
                        <div class="w-12 h-12 rounded-2xl bg-primary-container text-white flex items-center justify-center shrink-0 shadow-sm ring-4 ring-primary/5">
                            <span class="material-symbols-outlined text-[26px]">groups</span>
                        </div>
                        <div>
                            <h3 class="font-headline-sm font-bold text-on-surface">Usulkan Peserta Course</h3>
                            <p class="font-body-sm text-on-surface-variant mt-0.5">Filter dan rekomendasikan kandidat karyawan kepada Admin sesuai dengan scope departemen & jabatan Anda.</p>
                        </div>
                    </div>

                    <!-- Approval Workflow Guidance Notice -->
                    <!-- <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-start gap-3 mb-6">
                        <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">verified_user</span>
                        <div class="font-body-sm text-on-surface-variant leading-relaxed">
                            <span class="font-bold text-on-surface">Alur Persetujuan (Approval Workflow):</span> Rekomendasi peserta dari Pengajar (Teacher) akan diteruskan ke Admin untuk verifikasi akhir dan pendaftaran otomatis ke kelas (Enrollment).
                        </div>
                    </div> -->

                    <?php if (($course['status'] ?? '') === 'Submitted' || ($course['status'] ?? '') === 'Approved'): ?>
                        <!-- Readonly mode -->
                        <div class="p-8 text-center border-2 border-dashed border-outline-variant rounded-2xl bg-surface-container-low/40">
                            <div class="w-16 h-16 rounded-2xl bg-surface-variant/40 text-on-surface-variant mx-auto flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-[36px]">lock</span>
                            </div>
                            <p class="font-bold text-on-surface font-title-md">Mode Hanya-Baca</p>
                            <p class="font-body-sm text-on-surface-variant mt-1">Anda telah submit 12 karyawan. Menunggu proses assign & approval oleh Admin.</p>
                        </div>
                    <?php else: ?>
                        <!-- Interactive Filter Flow -->
                        <div class="space-y-6">
                            <!-- Filter Builder -->
                            <div class="bg-surface-container-low/50 p-5 rounded-2xl border border-outline-variant/60 flex flex-col gap-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                    <?php foreach ($filters as $type => $options): ?>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="font-label-sm font-bold text-on-surface capitalize flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[16px] text-primary">filter_alt</span>
                                                <?= ucfirst($type) ?>
                                            </label>
                                            <select id="filter-<?= strtolower(
                                                $type,
                                            ) ?>" class="w-full bg-surface border border-outline-variant rounded-xl p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm font-body-sm text-on-surface shadow-sm transition-all">
                                                <option value="">-- Semua <?= ucfirst($type) ?> --</option>
                                                <?php foreach ($options as $opt): ?>
                                                    <option value="<?= $opt ?>"><?= $opt ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="border-outline-variant/40 mt-1">
                                    <button type="button" onclick="applyCandidateFilter()" class="bg-primary text-on-primary px-6 py-2.5 rounded-xl font-label-md font-bold shadow-sm hover:bg-primary/90 transition-all flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[18px]">search</span> Tampilkan Kandidat
                                    </button>
                                </div>
                            </div>

                            <!-- Candidate Grid View -->
                            <div class="bg-surface border border-outline-variant rounded-2xl overflow-hidden shadow-sm">
                                <div class="bg-primary-container/30 px-5 py-3 border-b border-outline-variant flex flex-wrap items-center justify-between gap-2">
                                    <label class="flex items-center gap-2.5 font-label-sm font-bold text-on-surface cursor-pointer select-none">
                                        <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary" checked>
                                        <span id="emp-count-display">Pilih Semua (7 Kandidat Relevan Ditemukan)</span>
                                    </label>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-primary/10 text-primary">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Target Aktif
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-4 font-body-sm max-h-[460px] overflow-y-auto" id="candidate-list-container">
                                    <?php
                                    $emps = [
                                        [
                                            'name' => 'Budi Santoso',
                                            'npk' => '12345',
                                            'jabatan' => 'Operator',
                                            'golongan' => 'Gol 1',
                                            'departemen' => 'Manufacturing',
                                            'divisi' => 'Produksi',
                                            'seksi' => 'Assembling',
                                        ],
                                        [
                                            'name' => 'Citra Lestari',
                                            'npk' => '11223',
                                            'jabatan' => 'Supervisor',
                                            'golongan' => 'Gol 3',
                                            'departemen' => 'Quality',
                                            'divisi' => 'QA',
                                            'seksi' => 'Pemeriksaan',
                                        ],
                                        [
                                            'name' => 'Ahmad Rizal',
                                            'npk' => '67890',
                                            'jabatan' => 'Leader',
                                            'golongan' => 'Gol 2',
                                            'departemen' => 'Manufacturing',
                                            'divisi' => 'Produksi',
                                            'seksi' => 'Assembling',
                                        ],
                                        [
                                            'name' => 'Dian Sastro',
                                            'npk' => '23456',
                                            'jabatan' => 'Manager',
                                            'golongan' => 'Gol 4',
                                            'departemen' => 'Engineering',
                                            'divisi' => 'PE',
                                            'seksi' => 'Desain',
                                        ],
                                        [
                                            'name' => 'Eko Prasetyo',
                                            'npk' => '34567',
                                            'jabatan' => 'Operator',
                                            'golongan' => 'Gol 1',
                                            'departemen' => 'Manufacturing',
                                            'divisi' => 'Produksi',
                                            'seksi' => 'Machining',
                                        ],
                                        [
                                            'name' => 'Fani Amelia',
                                            'npk' => '45678',
                                            'jabatan' => 'Staff',
                                            'golongan' => 'Gol 2',
                                            'departemen' => 'HR',
                                            'divisi' => 'Personalia',
                                            'seksi' => 'Rekrutmen',
                                        ],
                                        [
                                            'name' => 'Gilang Ramadhan',
                                            'npk' => '56789',
                                            'jabatan' => 'Staff',
                                            'golongan' => 'Gol 2',
                                            'departemen' => 'Finance',
                                            'divisi' => 'Akuntansi',
                                            'seksi' => 'Pajak',
                                        ],
                                    ];
                                    foreach ($emps as $emp):
                                        $initials = strtoupper(substr($emp['name'], 0, 1)); ?>
                                    <label class="emp-card flex items-center gap-3 p-4 rounded-xl border border-outline-variant/60 bg-surface hover:border-primary/50 hover:bg-surface-container-lowest cursor-pointer transition-all shadow-xs group"
                                           data-jabatan="<?= e(strtolower($emp['jabatan'])) ?>" 
                                           data-golongan="<?= e(strtolower($emp['golongan'])) ?>" 
                                           data-departemen="<?= e(strtolower($emp['departemen'])) ?>" 
                                           data-divisi="<?= e(strtolower($emp['divisi'])) ?>" 
                                           data-seksi="<?= e(strtolower($emp['seksi'])) ?>">
                                        <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary shrink-0" checked>
                                        <div class="w-10 h-10 rounded-xl bg-primary-container/60 text-primary font-bold flex items-center justify-center shrink-0">
                                            <?= e($initials) ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="font-title-sm font-bold text-on-surface group-hover:text-primary transition-colors truncate max-w-[130px]"><?= e(
                                                    $emp['name'],
                                                ) ?></p>
                                                <span class="font-label-xs px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-medium shrink-0">NPK: <?= e(
                                                    $emp['npk'],
                                                ) ?></span>
                                            </div>
                                            <p class="font-label-sm text-on-surface-variant text-[12px] mt-0.5 flex items-center gap-1.5 truncate">
                                                <span class="material-symbols-outlined text-[14px]">work</span> <?= e(
                                                    $emp['jabatan'],
                                                ) ?> • <?= e($emp['dept'] ?? $emp['departemen']) ?>
                                            </p>
                                        </div>
                                    </label>
                                    <?php
                                    endforeach;
                                    ?>
                                </div>
                                <div class="bg-surface-container-low px-5 py-3 border-t border-outline-variant/60 flex items-center justify-between font-label-sm">
                                    <span class="text-on-surface-variant text-[12px]">Kandidat akan diusulkan pada saat pengiriman course ke Admin</span>
                                    <span id="emp-count-bottom" class="font-bold text-primary flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">how_to_reg</span> 7 Karyawan Terpilih
                                    </span>
                                </div>
                            </div>
                            
                            <script>
                            function applyCandidateFilter() {
                                const filters = {
                                    jabatan: document.getElementById('filter-jabatan')?.value.toLowerCase() || '',
                                    golongan: document.getElementById('filter-golongan')?.value.toLowerCase() || '',
                                    departemen: document.getElementById('filter-departemen')?.value.toLowerCase() || '',
                                    divisi: document.getElementById('filter-divisi')?.value.toLowerCase() || '',
                                    seksi: document.getElementById('filter-seksi')?.value.toLowerCase() || ''
                                };
                                
                                let visibleCount = 0;
                                const cards = document.querySelectorAll('.emp-card');
                                
                                cards.forEach(card => {
                                    let isMatch = true;
                                    for (const [key, val] of Object.entries(filters)) {
                                        if (val && card.dataset[key] !== val) {
                                            isMatch = false;
                                            break;
                                        }
                                    }
                                    if (isMatch) {
                                        card.style.display = 'flex';
                                        visibleCount++;
                                    } else {
                                        card.style.display = 'none';
                                    }
                                });

                                document.getElementById('emp-count-display').innerText = `Pilih Semua (${visibleCount} Kandidat Relevan Ditemukan)`;
                                document.getElementById('emp-count-bottom').innerHTML = `<span class="material-symbols-outlined text-[16px]">how_to_reg</span> ${visibleCount} Karyawan Terpilih`;
                            }
                            </script>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ====== STEP 4: REVIEW & SUBMIT ====== -->
                <?php if ($step === 4): ?>
                <section class="bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
                    <!-- Section Header -->
                    <div class="flex items-start gap-4 mb-6 pb-6 border-b border-outline-variant/60">
                        <div class="w-12 h-12 rounded-2xl bg-primary-container text-white flex items-center justify-center shrink-0 shadow-sm ring-4 ring-primary/5">
                            <span class="material-symbols-outlined text-[26px]">done_all</span>
                        </div>
                        <div>
                            <h3 class="font-headline-sm font-bold text-on-surface">Verifikasi & Kirim Course</h3>
                            <p class="font-body-sm text-on-surface-variant mt-0.5">Periksa kembali ringkasan informasi course, struktur kurikulum, dan usulan target peserta sebelum dikirim ke Admin.</p>
                        </div>
                    </div>

                    <!-- Executive Summary Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Card 1: Informasi Course -->
                        <div class="border border-outline-variant/70 rounded-2xl p-5 bg-surface-container-low/40 flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-label-xs font-bold text-on-surface-variant uppercase tracking-wider px-2.5 py-1 rounded-full bg-surface-container">Identitas Course</span>
                                    <span class="material-symbols-outlined text-primary text-[22px]">info</span>
                                </div>
                                <h4 class="font-title-md font-bold text-on-surface mb-1 line-clamp-2"><?= e(
                                    $course['title'] ?? 'Nama Course',
                                ) ?></h4>
                                <p class="font-body-sm text-on-surface-variant/80 line-clamp-2"><?= e(
                                    $course['description'] ?? 'Deskripsi course belum diisi.',
                                ) ?></p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between font-label-sm">
                                <span class="text-on-surface-variant">Kategori</span>
                                <span class="font-bold text-on-surface"><?= e($course['category'] ?? 'Umum') ?> • <?= e(
     $course['code'] ?? '-',
 ) ?></span>
                            </div>
                        </div>

                        <!-- Card 2: Struktur Kurikulum -->
                        <div class="border border-outline-variant/70 rounded-2xl p-5 bg-surface-container-low/40 flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-label-xs font-bold text-on-surface-variant uppercase tracking-wider px-2.5 py-1 rounded-full bg-surface-container">Kurikulum</span>
                                    <span class="material-symbols-outlined text-primary text-[22px]">library_books</span>
                                </div>
                                <h4 class="font-title-md font-bold text-on-surface mb-1">1 Sesi Pembelajaran</h4>
                                <p class="font-body-sm text-on-surface-variant/80">Terdiri dari modul materi PDF, evaluasi latihan kuis, dan ujian akhir kompetensi.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between font-label-sm">
                                <span class="text-on-surface-variant">Status Materi</span>
                                <span class="font-bold text-success flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">check_circle</span> Siap Digunakan
                                </span>
                            </div>
                        </div>

                        <!-- Card 3: Target Audience -->
                        <div class="border border-outline-variant/70 rounded-2xl p-5 bg-surface-container-low/40 flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-label-xs font-bold text-on-surface-variant uppercase tracking-wider px-2.5 py-1 rounded-full bg-surface-container">Rekomendasi</span>
                                    <span class="material-symbols-outlined text-secondary text-[22px]">groups</span>
                                </div>
                                <h4 class="font-title-md font-bold text-on-surface mb-1">3 Karyawan Terpilih</h4>
                                <p class="font-body-sm text-on-surface-variant/80">Diusulkan berdasarkan kesesuaian departemen, jabatan, dan golongan kompetensi.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between font-label-sm">
                                <span class="text-on-surface-variant">Persetujuan</span>
                                <span class="font-bold text-primary flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">pending_actions</span> Menunggu Admin
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Enterprise Compliance & Submission Box -->
                    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-5 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[22px]">gavel</span>
                        </div>
                        <div>
                            <h5 class="font-title-sm font-bold text-on-surface">Konfirmasi Pengiriman ke Admin</h5>
                            <p class="font-body-sm text-on-surface-variant mt-1 leading-relaxed">
                                Menekan tombol <span class="font-bold text-primary">"Submit"</span> berarti mengajukan course ini kepada tim Admin untuk peninjauan (review) dan aktivasi kelas. Setelah diajukan, course akan berstatus <span class="font-bold text-on-surface">Submitted</span> hingga disetujui.
                            </p>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Wizard Navigation Actions (Sticky Bottom on Desktop, Fixed Below on Mobile) -->
                <div class="fixed bottom-0 left-0 lg:left-[240px] right-0 bg-surface shadow-[0_-8px_20px_-10px_rgba(0,0,0,0.08)] border-t border-outline-variant/60 z-40 transition-all drop-shadow-sm pb-safe">
                    <div class="w-full flex justify-between items-center px-4 sm:px-6 lg:px-8 h-20 lg:h-24">
                        <!-- Left Action -->
                        <div>
                            <?php if ($step > 1): ?>
                                <button type="submit" name="step" value="<?= $step -
                                    1 ?>" class="px-5 py-2.5 rounded-xl border border-outline-variant bg-surface text-on-surface font-label-md font-bold hover:bg-surface-container hover:border-on-surface-variant/40 shadow-sm transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
                                </button>
                            <?php else: ?>
                                <button type="button" onclick="window.history.back()" class="px-5 py-2.5 rounded-xl border border-outline-variant bg-surface text-on-surface font-label-md font-bold hover:bg-surface-container hover:border-on-surface-variant/40 shadow-sm transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">close</span> Batal
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Right Action -->
                        <div class="flex gap-2">
                            <?php if ($step === 4): ?>
                                <button type="button" onclick="Swal.fire({title: 'Submit Berhasil!', text: 'Course berhasil disubmit ke Admin.', icon: 'success', confirmButtonColor: '#E20613'}).then(() => { window.location.href='?route=/teacher/course/detail&course_id=<?= e(
                                    $course['id'] ?? 1,
                                ) ?>';});" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-md shadow-primary/25 hover:shadow-lg transition-all flex items-center gap-2">
                                    Submit ke Admin <span class="material-symbols-outlined text-[18px]">send</span>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="step" value="<?= $step +
                                    1 ?>" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-md shadow-primary/25 hover:shadow-lg transition-all flex items-center gap-2">
                                    Selanjutnya <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
    </main>

    <!-- ============================================== -->
    <!-- OVERLAY: ASSESSMENT BUILDER (Pre-Test / Post-Test) -->
    <!-- ============================================== -->
    <div id="assessment-builder-overlay" class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full z-[100] bg-surface hidden flex-col transition-opacity duration-300 opacity-0 overflow-y-auto" style="top: 0; left: 0; right: 0; bottom: 0; z-index: 100;">
        <!-- Header -->
        <header class="bg-surface border-b border-outline-variant sticky top-0 z-20 shadow-sm px-4 sm:px-6 h-16 flex items-center shrink-0">
            <div class="flex-1 flex justify-start">
                <button type="button" onclick="closeAssessmentBuilder()" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-2 transition-colors rounded-full hover:bg-surface-container" title="Kembali">arrow_back</button>
            </div>
            <div class="flex-none flex justify-center">
                <h2 id="ab-title" class="font-title-lg font-bold text-on-surface truncate px-4">Pre-Test: Modul Soal</h2>
            </div>
            <div class="flex-1 flex justify-end">
                <button type="button" onclick="saveAssessmentBuilder()" class="px-3 sm:px-5 py-2 bg-primary text-on-primary rounded-lg font-label-md font-bold hover:bg-primary-600 transition-colors shadow-sm whitespace-nowrap">Simpan <span class="hidden sm:inline">Soal</span></button>
            </div>
        </header>
        
        <!-- Content container -->
        <div class="max-w-6xl mx-auto w-full p-4 sm:p-6 space-y-6 pb-32">
            <!-- Summary Box -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4 pb-4 border-b border-outline-variant/60">
                    <div class="w-12 h-12 bg-primary-container text-white rounded-full flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">assignment</span>
                    </div>
                    <div>
                        <h3 class="font-title-lg font-bold text-on-surface">Instruksi & Pengaturan Soal</h3>
                        <p class="font-body-sm text-on-surface-variant flex gap-2"><span id="ab-counter">Total 0 Pertanyaan</span> • <span class="font-bold">Bobot Maks: <span id="ab-max-score" class="text-primary">0</span></span></p>
                    </div>
                </div>
                <textarea id="ab-instruction" class="w-full bg-surface-container-lowest border border-outline hover:border-primary focus:border-primary rounded-xl p-4 font-body-sm text-on-surface outline-none transition-all resize-y min-h-[90px]" placeholder="Masukkan instruksi pengerjaan soal untuk  (Contoh: Pilih satu jawaban yang paling tepat."></textarea>
            </div>

            <!-- Question List -->
            <div id="ab-questions-list" class="space-y-6">
                <!-- Questions injected here via JS -->
            </div>

            <!-- Add Question Button -->
            <button type="button" onclick="abAddQuestion()" class="w-full py-6 border-2 border-dashed border-outline-variant hover:border-primary rounded-2xl flex flex-col items-center justify-center gap-2 text-on-surface-variant hover:text-primary transition-all group bg-transparent hover:bg-primary/5">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform">add_circle</span>
                <span class="font-label-md font-bold">Tambah Pertanyaan Baru</span>
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- OVERLAY: MATERIAL BUILDER (Materi Content Pages) -->
    <!-- ============================================== -->
    <div id="material-builder-overlay" class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full z-[100] bg-surface hidden flex-col transition-opacity duration-300 opacity-0" style="top: 0; left: 0; right: 0; bottom: 0; z-index: 100;">
        <!-- Header -->
        <header class="bg-surface border-b border-outline-variant sticky top-0 z-20 shadow-sm px-4 sm:px-6 h-16 flex items-center shrink-0">
            <div class="flex-1 flex justify-start">
                <button type="button" onclick="closeMaterialBuilder()" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-2 transition-colors rounded-full hover:bg-surface-container" title="Kembali">arrow_back</button>
            </div>
            <div class="flex-none flex justify-center">
                <h2 id="mb-title" class="font-title-lg font-bold text-on-surface truncate px-4">Materi: Modul Konten</h2>
            </div>
            <div class="flex-1 flex justify-end">
                <button type="button" onclick="saveMaterialBuilder()" class="px-3 sm:px-5 py-2 bg-primary text-on-primary rounded-lg font-label-md font-bold hover:bg-primary-600 transition-colors shadow-sm whitespace-nowrap">Simpan <span class="hidden sm:inline">Materi</span></button>
            </div>
        </header>
        
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto" id="mb-scroll-area">
            <div class="max-w-6xl mx-auto w-full p-4 sm:p-6 space-y-5 pb-32">
                <!-- Page Info Card -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 bg-primary-container text-white rounded-full flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">auto_stories</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-title-md font-bold text-on-surface">Editor Halaman Materi</h3>
                            <p class="font-body-sm text-on-surface-variant">Susun konten pembelajaran dengan teks, gambar, video, dan dokumen.</p>
                        </div>
                        <div class="bg-surface-container-low border border-outline-variant/60 rounded-xl px-4 py-2 flex items-center gap-2 shadow-sm">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">layers</span>
                            <span class="font-label-md font-bold text-on-surface">Hal. <span id="mb-current-page" class="text-primary">1</span> / <span id="mb-total-pages">1</span></span>
                        </div>
                    </div>
                </div>

                <!-- Page Canvas (content blocks go here) -->
                <div id="mb-page-canvas" class="space-y-4 min-h-[200px]">
                    <!-- Content blocks injected here -->
                </div>

                <!-- Add Content Block Toolbar -->
                <div class="max-w-4xl mx-auto mt-8">
                    <div class="bg-surface-container-low/40 border-2 border-dashed border-outline-variant/60 rounded-2xl p-6 flex flex-col items-center">
                        <p class="font-label-md font-bold text-on-surface-variant flex items-center gap-2 mb-5">
                            <span class="material-symbols-outlined text-[20px]">add_circle</span> Tambah Blok Konten
                        </p>
                        <div class="flex flex-wrap justify-center gap-4 w-full">
                            <button type="button" onclick="mbAddBlock('text')" class="flex-1 min-w-[120px] max-w-[150px] flex flex-col items-center gap-3 p-5 border border-outline-variant/60 bg-surface rounded-2xl hover:bg-primary/5 hover:border-primary/40 hover:shadow-md transition-all group hover:-translate-y-1">
                                <div class="w-12 h-12 rounded-full bg-surface-container-high/50 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant group-hover:text-primary transition-colors">article</span>
                                </div>
                                <span class="font-label-sm font-bold text-on-surface-variant group-hover:text-primary transition-colors">Teks Judul & Paragraf</span>
                            </button>
                            <button type="button" onclick="mbAddBlock('image')" class="flex-1 min-w-[120px] max-w-[150px] flex flex-col items-center gap-3 p-5 border border-outline-variant/60 bg-surface rounded-2xl hover:bg-primary/5 hover:border-primary/40 hover:shadow-md transition-all group hover:-translate-y-1">
                                <div class="w-12 h-12 rounded-full bg-surface-container-high/50 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant group-hover:text-primary transition-colors">image</span>
                                </div>
                                <span class="font-label-sm font-bold text-on-surface-variant group-hover:text-primary transition-colors">Gambar / Ilustrasi</span>
                            </button>
                            <button type="button" onclick="mbAddBlock('youtube')" class="flex-1 min-w-[120px] max-w-[150px] flex flex-col items-center gap-3 p-5 border border-outline-variant/60 bg-surface rounded-2xl hover:bg-primary/5 hover:border-primary/40 hover:shadow-md transition-all group hover:-translate-y-1">
                                <div class="w-12 h-12 rounded-full bg-surface-container-high/50 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant group-hover:text-primary transition-colors">play_circle</span>
                                </div>
                                <span class="font-label-sm font-bold text-on-surface-variant group-hover:text-primary transition-colors">Video YouTube</span>
                            </button>
                            <button type="button" onclick="mbAddBlock('document')" class="flex-1 min-w-[120px] max-w-[150px] flex flex-col items-center gap-3 p-5 border border-outline-variant/60 bg-surface rounded-2xl hover:bg-primary/5 hover:border-primary/40 hover:shadow-md transition-all group hover:-translate-y-1">
                                <div class="w-12 h-12 rounded-full bg-surface-container-high/50 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant group-hover:text-primary transition-colors">picture_as_pdf</span>
                                </div>
                                <span class="font-label-sm font-bold text-on-surface-variant group-hover:text-primary transition-colors">Dokumen PDF</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Pagination Footer -->
        <footer class="bg-surface border-t border-outline-variant sticky bottom-0 z-20 shadow-[0_-2px_8px_rgba(0,0,0,0.06)] px-4 sm:px-6 h-16 flex items-center shrink-0">
            <div class="flex-1 flex justify-start">
                <button type="button" onclick="mbPrevPage()" id="mb-btn-prev" class="flex items-center gap-1 text-on-surface-variant hover:text-primary font-label-md font-bold transition-colors disabled:opacity-40 disabled:cursor-not-allowed px-3 py-2 rounded-lg hover:bg-surface-container" disabled>
                    <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    <span class="hidden sm:inline">Materi Sebelumnya</span>
                </button>
            </div>
            <div class="flex-none flex justify-center">
                <div class="bg-surface-container-low border border-outline-variant/60 rounded-full px-5 py-1.5 flex items-center gap-1 shadow-sm">
                    <span class="font-label-md font-bold text-on-surface">Materi</span>
                    <span id="mb-footer-current" class="font-title-sm font-bold text-primary">1</span>
                    <span class="font-label-md text-on-surface-variant">/</span>
                    <span id="mb-footer-total" class="font-title-sm font-bold text-on-surface">1</span>
                </div>
            </div>
            <div class="flex-1 flex justify-end">
                <button type="button" onclick="mbNextPage()" id="mb-btn-next" class="flex items-center gap-1 bg-primary text-on-primary font-label-md font-bold transition-colors px-4 py-2 rounded-lg hover:bg-primary-600 shadow-sm">
                    <span class="hidden sm:inline">Materi Selanjutnya</span>
                    <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </button>
            </div>
        </footer>
    </div>

    <!-- ============================================== -->
    <!-- OVERLAY: QUIZ BUILDER (Quiz Course / Final Assessment) -->
    <!-- ============================================== -->
    <div id="quiz-builder-overlay" class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full z-[100] bg-surface hidden flex-col transition-opacity duration-300 opacity-0 overflow-y-auto" style="top: 0; left: 0; right: 0; bottom: 0; z-index: 100;">
        <!-- Header -->
        <header class="bg-surface border-b border-outline-variant sticky top-0 z-20 shadow-sm px-4 sm:px-6 h-16 flex items-center shrink-0">
            <div class="flex-1 flex justify-start">
                <button type="button" onclick="closeQuizBuilder()" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-2 transition-colors rounded-full hover:bg-surface-container" title="Kembali">arrow_back</button>
            </div>
            <div class="flex-none flex justify-center">
                <h2 id="qb-title" class="font-title-lg font-bold text-on-surface truncate px-4">Quiz Builder</h2>
            </div>
            <div class="flex-1 flex justify-end">
                <button type="button" onclick="saveQuizBuilder()" class="px-3 sm:px-5 py-2 bg-primary text-on-primary rounded-lg font-label-md font-bold hover:bg-primary-600 transition-colors shadow-sm whitespace-nowrap">Simpan <span class="hidden sm:inline">Quiz</span></button>
            </div>
        </header>
        
        <!-- Content -->
        <div class="max-w-6xl mx-auto w-full p-4 sm:p-6 space-y-6 pb-32">
            <!-- Summary & Config -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4 pb-4 border-b border-outline-variant/60">
                    <div id="qb-icon-wrap" class="w-12 h-12 bg-primary-container text-white rounded-full flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">assignment</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-title-lg font-bold text-on-surface">Pengaturan & Instruksi Quiz</h3>
                        <p class="font-body-sm text-on-surface-variant flex gap-2"><span id="qb-counter">Total 0 Pertanyaan</span> • <span class="font-bold">Bobot Maks: <span id="qb-max-score" class="text-primary">0</span></span></p>
                    </div>
                </div>
                <textarea id="qb-instruction" class="w-full bg-surface-container-lowest border border-outline hover:border-primary focus:border-primary rounded-xl p-4 font-body-sm text-on-surface outline-none transition-all resize-y min-h-[80px]" placeholder="Masukkan instruksi pengerjaan quiz (Contoh: Jawab semua pertanyaan berikut.)"></textarea>
            </div>

            <!-- Question List -->
            <div id="qb-questions-list" class="space-y-6">
                <!-- Questions injected here via JS -->
            </div>

            <!-- Add Question Toolbar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button type="button" onclick="qbAddQuestion('mc')" class="py-5 border-2 border-dashed border-outline-variant hover:border-primary rounded-2xl flex flex-col items-center justify-center gap-2 text-on-surface-variant hover:text-primary transition-all group bg-transparent hover:bg-primary/5">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform">checklist</span>
                    <span class="font-label-md font-bold">Tambah Pilihan Ganda</span>
                </button>
                <button type="button" onclick="qbAddQuestion('essay')" class="py-5 border-2 border-dashed border-outline-variant hover:border-secondary rounded-2xl flex flex-col items-center justify-center gap-2 text-on-surface-variant hover:text-secondary transition-all group bg-transparent hover:bg-secondary/5">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform">edit_note</span>
                    <span class="font-label-md font-bold">Tambah Isian / Essay</span>
                </button>
            </div>
        </div>
    </div>

    <script>
    function updateSessionNumbers() {
        const container = document.getElementById('sessions-container');
        if(!container) return;
        const sessions = container.querySelectorAll('.session-block');
        sessions.forEach((sess, index) => {
            const titleEl = sess.querySelector('h4');
            if(titleEl) {
                const parts = titleEl.textContent.split(':');
                if(parts.length > 1) {
                    titleEl.textContent = 'Sesi ' + (index + 1) + ':' + parts.slice(1).join(':');
                } else {
                    titleEl.textContent = 'Sesi ' + (index + 1);
                }
            }
        });
    }

    function addSessionBlock() {
        const container = document.getElementById('sessions-container');
        if(!container) return;
        
        const sessions = container.querySelectorAll('.session-block');
        const nextIndex = sessions.length + 1;
        
        // Find the first session block as a template to clone
        const firstSession = sessions[0];
        if(!firstSession) return;
        
        const newSession = firstSession.cloneNode(true);
        
        // Update Title
        const titleEl = newSession.querySelector('h4');
        if(titleEl) {
            titleEl.textContent = 'Sesi ' + nextIndex + ': Modul Baru';
        }
        
        // Clear out builder list content
        const list = newSession.querySelector('.materi-builder-list');
        if(list) {
            list.innerHTML = '';
        }
        
        // Append to container 
        container.appendChild(newSession);
        
        // Re-evaluate empty state for the new clone
        checkEmptyHintList(list);

        // Clear quiz tab content in cloned session
        const quizList = newSession.querySelector('.quiz-items-list');
        if (quizList) {
            quizList.querySelectorAll('.qb-saved-state').forEach(el => el.remove());
            quizList.querySelectorAll('.qz-question-count').forEach(el => el.textContent = '0');
        }

        // Reset tab state to Materi
        const firstTab = newSession.querySelector('.session-tab-btn[data-tab="materi"]');
        if (firstTab) switchSessionTab(firstTab, 'materi');
    }
    
    function deleteSessionBlock(btn) {
        const container = document.getElementById('sessions-container');
        const sessions = container.querySelectorAll('.session-block');
        
        if (sessions.length <= 1) {
            Swal.fire({title: 'Perhatian', text: 'Minimal harus ada 1 sesi dalam course.', icon: 'warning', confirmButtonColor: '#E20613'});
            return;
        }
        
        if (confirm('Apakah Anda yakin ingin menghapus sesi ini beserta semua materinya?')) {
            const session = btn.closest('.session-block');
            
            // Add a small fade out animation
            session.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                session.remove();
                updateSessionNumbers();
            }, 300);
        }
    }

    function handleModuleTitleEnter(e, el, forceSave = false) {
        if(forceSave || (e && e.key === 'Enter')) {
            if(e) e.preventDefault();
            el.blur();
            // Flash green border to simulate saving
            el.classList.remove('border-transparent');
            el.classList.add('border-success', 'text-success');
            setTimeout(() => {
                el.classList.remove('border-success', 'text-success');
                el.classList.add('border-transparent');
            }, 600);
        }
    }

    // Module Builder logic
    function appendModuleBlock(btnEl, title, icon, colorContext) {
        const workspace = btnEl.closest('.session-workspace');
        if(!workspace) return;
        
        const list = workspace.querySelector('.materi-builder-list');
        
        // Define color classes based on context
        let bagdeBgClass = '', bagdeTextClass = '';
        if(colorContext === 'secondary') {
            bagdeBgClass = 'bg-secondary-container';
            bagdeTextClass = 'text-secondary';
        } else if (colorContext === 'warning') {
            bagdeBgClass = 'bg-warning-container/30';
            bagdeTextClass = 'text-warning';
        } else {
            bagdeBgClass = 'bg-primary-container';
            bagdeTextClass = 'text-primary';
        }

        // Construct template with gear based on type
        let actionButtons = `<button type="button" onclick="const list = this.closest('.materi-builder-list'); this.closest('.module-block').remove(); checkEmptyHintList(list);" class="material-symbols-outlined text-on-surface-variant hover:text-error transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Hapus Modul">delete</button>`;
        
        if (colorContext === 'secondary' || colorContext === 'warning') { // pre-test or post-test
            actionButtons = `
                <button type="button" onclick="openAssessmentBuilder(this)" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Edit Isi Soal">settings</button>
                ${actionButtons}
            `;
        } else { // materi type
            actionButtons = `
                <button type="button" onclick="openMaterialBuilder(this)" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors p-2 lg:opacity-0 group-hover:opacity-100 focus:opacity-100" title="Edit Isi Materi">settings</button>
                ${actionButtons}
            `;
        }

        const iconColorClass = (colorContext === 'primary') ? 'text-white' : bagdeTextClass;
        const template = `
            <div class="module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group animate-fade-in-down">
                <div class="w-10 h-10 rounded-lg ${bagdeBgClass} ${iconColorClass} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined">${icon}</span>
                </div>
                <div class="flex-1 min-w-0 flex flex-col justify-center">
                    <p class="font-label-sm ${bagdeTextClass} mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold">${title}</p>
                    <input type="text" onkeydown="handleModuleTitleEnter(event, this)" onblur="handleModuleTitleEnter(null, this, true)" class="w-full bg-transparent border-b border-transparent hover:border-outline focus:border-primary outline-none font-title-sm font-bold text-on-surface pb-1 text-[13px] sm:text-[14px] transition-all" value="" placeholder="Isi judul modifikasi baru...">
                </div>
                ${actionButtons}
            </div>
        `;
        
        list.insertAdjacentHTML('beforeend', template);
        checkEmptyHintList(list);
        
        // Auto focus new input
        const inputs = list.querySelectorAll('input[type="text"]');
        if(inputs.length > 0) {
            inputs[inputs.length - 1].focus();
        }
    }

    function checkEmptyHintList(list) {
        if(!list) return;
        const workspace = list.closest('.session-workspace');
        if(!workspace) return;
        
        const emptyHint = workspace.querySelector('.materi-empty-hint');
        if(list && emptyHint) {
            if(list.children.length === 0) {
                emptyHint.classList.remove('hidden');
                emptyHint.classList.add('flex');
            } else {
                emptyHint.classList.add('hidden');
                emptyHint.classList.remove('flex');
            }
        }
    }

    // ==============================================
    // Assessment Builder Engine Logics
    // ==============================================

    let currentEditingModule = null;

    function openAssessmentBuilder(btnEl) {
        currentEditingModule = btnEl.closest('.module-block');
        const modal = document.getElementById('assessment-builder-overlay');
        const sessionTitleEl = btnEl.closest('.session-block').querySelector('h4');
        const moduleInputEl = currentEditingModule.querySelector('input[type="text"]');
        
        // Assemble Title
        let moduleType = currentEditingModule.querySelector('.font-label-sm').textContent.trim();
        let sessionPrefix = sessionTitleEl ? sessionTitleEl.textContent.trim().split(':')[0] : 'Sesi X';
        let displayTitle = moduleType + ': ' + sessionPrefix;
        if(moduleInputEl && moduleInputEl.value.trim() !== '') {
            displayTitle = displayTitle + ' - ' + moduleInputEl.value.trim();
        }
        document.getElementById('ab-title').textContent = displayTitle;

        // Restore State or Initialize Empty
        const list = document.getElementById('ab-questions-list');
        const instructionTa = document.getElementById('ab-instruction');
        const stateDiv = currentEditingModule.querySelector('.ab-saved-state');
        
        if (stateDiv) {
            list.innerHTML = stateDiv.innerHTML;
            instructionTa.value = stateDiv.getAttribute('data-instruction') || '';
        } else {
            list.innerHTML = '';
            instructionTa.value = '';
            abAddQuestion();
        }

        // Display Modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Trigger opacity for animation
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
        }, 10);
        
        abCalculateTotal();
        document.body.style.overflow = 'hidden'; // prevent background scrolling
    }

    function closeAssessmentBuilder() {
        const modal = document.getElementById('assessment-builder-overlay');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 300);
    }

    function saveAssessmentBuilder() {
        const list = document.getElementById('ab-questions-list');
        const instructionTa = document.getElementById('ab-instruction');
        
        // Sync JS property states to HTML attributes so innerHTML captures them
        list.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
            input.setAttribute('value', input.value);
        });
        list.querySelectorAll('textarea').forEach(ta => {
            ta.textContent = ta.value;
        });
        list.querySelectorAll('input[type="radio"]').forEach(radio => {
            if (radio.checked) radio.setAttribute('checked', 'checked');
            else radio.removeAttribute('checked');
        });

        // Save serialized state into current editing module
        if (currentEditingModule) {
            let stateDiv = currentEditingModule.querySelector('.ab-saved-state');
            if (!stateDiv) {
                stateDiv = document.createElement('div');
                stateDiv.className = 'ab-saved-state hidden';
                currentEditingModule.appendChild(stateDiv);
            }
            stateDiv.innerHTML = list.innerHTML;
            stateDiv.setAttribute('data-instruction', instructionTa.value);
        }
        
        // Flash success logic
        const saveBtn = document.querySelector('#assessment-builder-overlay header button.bg-primary');
        const ogText = saveBtn.innerHTML;
        saveBtn.classList.replace('bg-primary', 'bg-success');
        saveBtn.classList.replace('hover:bg-primary-600', 'hover:bg-success');
        saveBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">check</span> Tersimpan';
        
        setTimeout(() => {
            saveBtn.classList.replace('bg-success', 'bg-primary');
            saveBtn.classList.replace('hover:bg-success', 'hover:bg-primary-600');
            saveBtn.innerHTML = ogText;
            closeAssessmentBuilder();
        }, 600);
    }

    function abOptionSelected(radioBtn) {
        // Clear all sibling label classes in this group
        const container = radioBtn.closest('.space-y-3');
        container.querySelectorAll('label').forEach(lbl => {
            lbl.classList.remove('border-success', 'bg-success-container/10');
            lbl.classList.add('border-outline-variant');
        });
        
        // Highlight active
        const activeLabel = radioBtn.closest('label');
        activeLabel.classList.remove('border-outline-variant');
        activeLabel.classList.add('border-success', 'bg-success-container/10');
    }

    function abCalculateTotal() {
        let total = 0;
        document.querySelectorAll('.ab-weight').forEach(input => {
            let val = parseInt(input.value) || 0;
            total += val;
        });
        document.getElementById('ab-max-score').textContent = total;
        
        const count = document.querySelectorAll('.ab-question-block').length;
        document.getElementById('ab-counter').textContent = 'Total ' + count + ' Pertanyaan';
    }

    function abDeleteQuestion(btn) {
        if(confirm('Hapus soal ini?')) {
            const block = btn.closest('.ab-question-block');
            block.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => {
                block.remove();
                abRenumberQuestions();
                abCalculateTotal();
            }, 300);
        }
    }

    function abRenumberQuestions() {
        const questions = document.querySelectorAll('.ab-question-block');
        questions.forEach((block, idx) => {
            let qIndex = idx + 1;
            block.setAttribute('data-index', qIndex);
            // Label
            const headerBadge = block.querySelector('.font-label-md');
            if(headerBadge) headerBadge.textContent = 'Soal ' + qIndex;
            
            // Re-bind radio names so they don't break grouping
            const radios = block.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                radio.setAttribute('name', 'ab_q' + qIndex + '_correct');
            });
        });
    }

    function abAddQuestion() {
        const list = document.getElementById('ab-questions-list');
        const qIndex = list.children.length + 1;
        
        const markup = `
            <div class="ab-question-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-0 shadow-sm overflow-hidden animate-fade-in-down transition-colors hover:border-outline-variant" data-index="${qIndex}">
                <div class="bg-surface-container-lowest border-b border-outline-variant/40 px-5 py-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="font-label-md font-bold text-on-surface bg-surface-container-low border border-outline-variant/60 px-3 py-1 rounded-lg">Soal ${qIndex}</span>
                        <span class="font-label-sm font-bold text-primary bg-primary/5 border border-primary/20 px-2 py-0.5 rounded-lg flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">checklist</span>Pilihan Ganda</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 bg-surface px-3 py-1.5 rounded-lg border border-outline/50 shadow-sm hover:border-primary transition-colors focus-within:border-primary">
                            <label class="font-label-sm font-bold text-on-surface-variant">Bobot</label>
                            <div class="w-px h-4 bg-outline-variant/60"></div>
                            <input type="number" onchange="abCalculateTotal()" class="ab-weight font-title-sm font-bold text-primary w-12 text-center outline-none bg-transparent" value="5" min="1">
                        </div>
                        <div class="w-px h-6 bg-outline-variant/50 mx-1"></div>
                        <button type="button" onclick="abDeleteQuestion(this)" class="material-symbols-outlined text-on-surface-variant hover:bg-error-container hover:text-error transition-colors p-1.5 rounded-lg" title="Hapus Soal">delete</button>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <div class="group relative">
                        <textarea class="w-full bg-transparent border border-outline-variant/40 hover:border-outline focus:border-primary focus:bg-surface-container-lowest rounded-xl font-body-md text-on-surface outline-none transition-all resize-y min-h-[80px] p-4 shadow-sm" placeholder="Tuliskan pertanyaan Anda secara spesifik di sini..."></textarea>
                    </div>
                    
                    <div class="ab-options-container grid gap-3 pl-0 sm:pl-4 transition-all">
                        ${['A', 'B', 'C', 'D'].map((opt, i) => `
                        <label class="ab-option-row group/opt flex items-center gap-3 p-3 border border-outline-variant/60 rounded-xl cursor-pointer hover:bg-surface-container-lowest transition-colors shadow-sm relative overflow-hidden">
                            <div class="absolute inset-y-0 left-0 w-1 bg-transparent group-has-[:checked]/opt:bg-success transition-colors"></div>
                            <input type="radio" onchange="abOptionSelected(this)" name="ab_q${qIndex}_correct" value="${opt}" class="ml-2 w-5 h-5 text-success border-outline-variant focus:ring-success cursor-pointer" ${i===0?'checked':''}>
                            <span class="ab-opt-letter font-title-sm font-bold w-7 h-7 flex items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant border border-outline-variant/40 group-has-[:checked]/opt:bg-success group-has-[:checked]/opt:text-on-success group-has-[:checked]/opt:border-success transition-all">${opt}</span>
                            <input type="text" class="flex-1 bg-transparent py-1 border-b border-transparent outline-none font-body-md text-on-surface focus:border-primary pointer-events-auto transition-colors" placeholder="Ketik pilihan jawaban ${opt}...">
                            ${i >= 2 ? `<button type="button" onclick="const container = this.closest('.ab-options-container'); this.closest('.ab-option-row').remove(); abRenumberOptions(container)" class="material-symbols-outlined text-on-surface-variant hover:text-error opacity-0 lg:group-hover/opt:opacity-100 transition-opacity p-1 relative z-10" title="Hapus Opsi">close</button>` : ''}
                        </label>
                        `).join('')}
                    </div>
                    
                    <div class="pl-0 sm:pl-4 mt-2">
                        <button type="button" onclick="abAddOption(this)" class="flex items-center gap-2 text-primary font-label-sm font-bold hover:bg-primary/5 px-4 py-2 rounded-lg transition-colors border border-transparent hover:border-primary/20 w-fit">
                            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Pilihan Baru
                        </button>
                    </div>
                </div>
            </div>
        `;
        list.insertAdjacentHTML('beforeend', markup);
        abCalculateTotal();
    }

    function abAddOption(btn) {
        const block = btn.closest('.ab-question-block');
        const qIndex = block.getAttribute('data-index');
        const container = block.querySelector('.ab-options-container');
        
        const count = container.children.length;
        if (count >= 10) {
            Swal.fire({title: 'Batas Maksimum', text: 'Maksimal 10 pilihan (A-J).', icon: 'info', confirmButtonColor: '#E20613'});
            return;
        }
        
        const letter = String.fromCharCode(65 + count); // A=65
        
        const markup = `
            <label class="ab-option-row group/opt flex items-center gap-3 p-3 border border-outline-variant/60 rounded-xl cursor-pointer hover:bg-surface-container-lowest transition-colors shadow-sm relative overflow-hidden animate-fade-in-down">
                <div class="absolute inset-y-0 left-0 w-1 bg-transparent group-has-[:checked]/opt:bg-success transition-colors"></div>
                <input type="radio" onchange="abOptionSelected(this)" name="ab_q${qIndex}_correct" value="${letter}" class="ml-2 w-5 h-5 text-success border-outline-variant focus:ring-success cursor-pointer">
                <span class="ab-opt-letter font-title-sm font-bold w-7 h-7 flex items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant border border-outline-variant/40 group-has-[:checked]/opt:bg-success group-has-[:checked]/opt:text-on-success group-has-[:checked]/opt:border-success transition-all">${letter}</span>
                <input type="text" class="flex-1 bg-transparent py-1 border-b border-transparent outline-none font-body-md text-on-surface focus:border-primary pointer-events-auto transition-colors" placeholder="Ketik pilihan jawaban ${letter}...">
                <button type="button" onclick="const c = this.closest('.ab-options-container'); this.closest('.ab-option-row').remove(); abRenumberOptions(c)" class="material-symbols-outlined text-on-surface-variant hover:text-error opacity-0 lg:group-hover/opt:opacity-100 transition-opacity p-1 relative z-10" title="Hapus Opsi">close</button>
            </label>
        `;
        container.insertAdjacentHTML('beforeend', markup);
    }

    function abRenumberOptions(container) {
        if (!container) return;
        const rows = container.querySelectorAll('.ab-option-row');
        rows.forEach((row, i) => {
            const letter = String.fromCharCode(65 + i);
            const optLetterBadge = row.querySelector('.ab-opt-letter');
            if(optLetterBadge) optLetterBadge.textContent = letter;
            
            const radio = row.querySelector('input[type="radio"]');
            if(radio) radio.value = letter;
            
            const textInput = row.querySelector('input[type="text"]');
            if(textInput) textInput.placeholder = 'Ketik pilihan jawaban ' + letter + '...';
        });
    }

    // ==============================================
    // Session Tab Switching
    // ==============================================

    function switchSessionTab(btn, tabName) {
        const sessionBlock = btn.closest('.session-block');
        if (!sessionBlock) return;
        
        // Update tab button styles
        sessionBlock.querySelectorAll('.session-tab-btn').forEach(t => {
            t.classList.remove('border-primary', 'text-primary');
            t.classList.add('border-transparent', 'text-on-surface-variant');
        });
        btn.classList.add('border-primary', 'text-primary');
        btn.classList.remove('border-transparent', 'text-on-surface-variant');
        
        // Toggle workspace panels
        sessionBlock.querySelectorAll('[data-workspace]').forEach(ws => {
            if (ws.getAttribute('data-workspace') === tabName) {
                ws.classList.remove('hidden');
                ws.classList.add('flex');
            } else {
                ws.classList.add('hidden');
                ws.classList.remove('flex');
            }
        });
    }

    // ==============================================
    // Session Title Inline Editor
    // ==============================================

    function editSessionTitle(btn) {
        const header = btn.closest('.flex.justify-between');
        const h4 = header.querySelector('h4');
        if (!h4) return;
        
        const currentText = h4.textContent.trim();
        const input = document.createElement('input');
        input.type = 'text';
        input.value = currentText;
        input.className = 'font-title-sm font-bold text-on-surface bg-transparent border-b-2 border-primary outline-none w-full max-w-[280px] pb-0.5 transition-all';
        
        h4.replaceWith(input);
        input.focus();
        input.select();
        
        const save = () => {
            const newH4 = document.createElement('h4');
            newH4.className = 'font-title-sm font-bold text-on-surface';
            newH4.textContent = input.value.trim() || currentText;
            input.replaceWith(newH4);
            
            // Flash confirm
            newH4.classList.add('text-success');
            setTimeout(() => newH4.classList.remove('text-success'), 600);
        };
        
        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { input.value = currentText; input.blur(); }
        });
    }

    // ==============================================
    // Material Builder Engine
    // ==============================================

    let currentEditingMateriModule = null;
    let mbPages = []; // array of HTML strings, one per page
    let mbCurrentPage = 0;

    function openMaterialBuilder(btnEl) {
        currentEditingMateriModule = btnEl.closest('.module-block');
        const modal = document.getElementById('material-builder-overlay');
        const sessionTitleEl = btnEl.closest('.session-block').querySelector('h4');
        const moduleInputEl = currentEditingMateriModule.querySelector('input[type="text"]');
        
        let moduleType = currentEditingMateriModule.querySelector('.font-label-sm').textContent.trim();
        let sessionPrefix = sessionTitleEl ? sessionTitleEl.textContent.trim().split(':')[0] : 'Sesi X';
        let displayTitle = moduleType + ': ' + sessionPrefix;
        if (moduleInputEl && moduleInputEl.value.trim() !== '') {
            displayTitle = displayTitle + ' - ' + moduleInputEl.value.trim();
        }
        document.getElementById('mb-title').textContent = displayTitle;
        
        // Restore or Init
        const stateDiv = currentEditingMateriModule.querySelector('.mb-saved-state');
        if (stateDiv) {
            try { mbPages = JSON.parse(stateDiv.getAttribute('data-pages')); } catch(e) { mbPages = ['']; }
        } else {
            mbPages = [''];
        }
        mbCurrentPage = 0;
        mbLoadPage(0);
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => { modal.classList.remove('opacity-0'); modal.classList.add('opacity-100'); }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeMaterialBuilder() {
        const modal = document.getElementById('material-builder-overlay');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 300);
    }

    function mbSaveCurrentPageState() {
        const canvas = document.getElementById('mb-page-canvas');
        // Sync input values to attributes
        canvas.querySelectorAll('input[type="text"], input[type="url"]').forEach(i => i.setAttribute('value', i.value));
        canvas.querySelectorAll('textarea').forEach(t => t.textContent = t.value);
        mbPages[mbCurrentPage] = canvas.innerHTML;
    }

    function mbLoadPage(pageIdx) {
        const canvas = document.getElementById('mb-page-canvas');
        if (pageIdx < 0 || pageIdx >= mbPages.length) return;
        mbCurrentPage = pageIdx;
        canvas.innerHTML = mbPages[pageIdx] || '';
        mbUpdatePaginationUI();
        
        // Scroll to top
        const scrollArea = document.getElementById('mb-scroll-area');
        if (scrollArea) scrollArea.scrollTop = 0;
    }

    function mbUpdatePaginationUI() {
        const total = mbPages.length;
        const current = mbCurrentPage + 1;
        document.getElementById('mb-current-page').textContent = current;
        document.getElementById('mb-total-pages').textContent = total;
        document.getElementById('mb-footer-current').textContent = current;
        document.getElementById('mb-footer-total').textContent = total;
        
        const prevBtn = document.getElementById('mb-btn-prev');
        const nextBtn = document.getElementById('mb-btn-next');
        prevBtn.disabled = mbCurrentPage === 0;
        
        // Change next button text if last page
        if (mbCurrentPage >= mbPages.length - 1) {
            nextBtn.innerHTML = '<span class="hidden sm:inline">Tambah Halaman</span><span class="material-symbols-outlined text-[18px]">add</span>';
        } else {
            nextBtn.innerHTML = '<span class="hidden sm:inline">Materi Selanjutnya</span><span class="material-symbols-outlined text-[18px]">chevron_right</span>';
        }
    }

    function mbPrevPage() {
        if (mbCurrentPage <= 0) return;
        mbSaveCurrentPageState();
        mbLoadPage(mbCurrentPage - 1);
    }

    function mbNextPage() {
        mbSaveCurrentPageState();
        if (mbCurrentPage >= mbPages.length - 1) {
            // Create new page
            mbPages.push('');
            mbLoadPage(mbPages.length - 1);
        } else {
            mbLoadPage(mbCurrentPage + 1);
        }
    }

    function mbDeletePage() {
        if (mbPages.length <= 1) {
            Swal.fire({title: 'Perhatian', text: 'Minimal harus ada 1 halaman materi.', icon: 'warning', confirmButtonColor: '#E20613'});
            return;
        }
        if (!confirm('Hapus halaman ' + (mbCurrentPage + 1) + '?')) return;
        mbPages.splice(mbCurrentPage, 1);
        if (mbCurrentPage >= mbPages.length) mbCurrentPage = mbPages.length - 1;
        mbLoadPage(mbCurrentPage);
    }

    function saveMaterialBuilder() {
        mbSaveCurrentPageState();
        
        if (currentEditingMateriModule) {
            let stateDiv = currentEditingMateriModule.querySelector('.mb-saved-state');
            if (!stateDiv) {
                stateDiv = document.createElement('div');
                stateDiv.className = 'mb-saved-state hidden';
                currentEditingMateriModule.appendChild(stateDiv);
            }
            stateDiv.setAttribute('data-pages', JSON.stringify(mbPages));
        }
        
        const saveBtn = document.querySelector('#material-builder-overlay header button.bg-primary');
        const ogText = saveBtn.innerHTML;
        saveBtn.classList.replace('bg-primary', 'bg-success');
        saveBtn.classList.replace('hover:bg-primary-600', 'hover:bg-success');
        saveBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">check</span> Tersimpan';
        setTimeout(() => {
            saveBtn.classList.replace('bg-success', 'bg-primary');
            saveBtn.classList.replace('hover:bg-success', 'hover:bg-primary-600');
            saveBtn.innerHTML = ogText;
            closeMaterialBuilder();
        }, 600);
    }

    function mbAddBlock(type) {
        const canvas = document.getElementById('mb-page-canvas');
        let markup = '';
        const blockId = 'mb-blk-' + Date.now();
        
        if (type === 'text') {
            markup = `
            <div class="mb-content-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl overflow-hidden shadow-sm hover:border-outline-variant transition-colors animate-fade-in-down" id="${blockId}">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">article</span>
                        <span class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Blok Teks</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="mbMoveBlock(this, -1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Atas">arrow_upward</button>
                        <button type="button" onclick="mbMoveBlock(this, 1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Bawah">arrow_downward</button>
                        <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                        <button type="button" onclick="this.closest('.mb-content-block').remove()" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1 rounded transition-colors" title="Hapus">delete</button>
                    </div>
                </div>
                <div class="p-5">
                    <textarea class="w-full bg-transparent border border-outline-variant/40 hover:border-outline focus:border-primary rounded-xl font-body-md text-on-surface outline-none transition-all resize-y min-h-[140px] p-4" placeholder="Tulis konten materi pembelajaran Anda di sini. Anda bisa menjelaskan teori, memberikan contoh, atau menyertakan instruksi langkah demi langkah..."></textarea>
                </div>
            </div>`;
        } else if (type === 'image') {
            markup = `
            <div class="mb-content-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl overflow-hidden shadow-sm hover:border-outline-variant transition-colors animate-fade-in-down" id="${blockId}">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">image</span>
                        <span class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Blok Gambar</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="mbMoveBlock(this, -1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Atas">arrow_upward</button>
                        <button type="button" onclick="mbMoveBlock(this, 1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Bawah">arrow_downward</button>
                        <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                        <button type="button" onclick="this.closest('.mb-content-block').remove()" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1 rounded transition-colors" title="Hapus">delete</button>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <div class="border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center gap-3 text-on-surface-variant hover:border-primary hover:bg-primary/5 transition-all cursor-pointer group" onclick="this.querySelector('input[type=file]').click()">
                        <span class="material-symbols-outlined text-[40px] group-hover:text-primary transition-colors">cloud_upload</span>
                        <p class="font-label-md font-bold group-hover:text-primary">Klik untuk unggah gambar</p>
                        <p class="font-body-sm text-on-surface-variant/70">Mendukung JPG, PNG, WebP (Maks 5MB)</p>
                        <input type="file" accept="image/*" class="hidden" onchange="mbPreviewImage(this)">
                    </div>
                    <input type="text" class="w-full bg-transparent border-b border-outline-variant/40 focus:border-primary outline-none font-body-sm text-on-surface-variant py-2 transition-colors" placeholder="Keterangan gambar (opsional)...">
                </div>
            </div>`;
        } else if (type === 'youtube') {
            markup = `
            <div class="mb-content-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl overflow-hidden shadow-sm hover:border-outline-variant transition-colors animate-fade-in-down" id="${blockId}">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">play_circle</span>
                        <span class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Video YouTube</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="mbMoveBlock(this, -1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Atas">arrow_upward</button>
                        <button type="button" onclick="mbMoveBlock(this, 1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Bawah">arrow_downward</button>
                        <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                        <button type="button" onclick="this.closest('.mb-content-block').remove()" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1 rounded transition-colors" title="Hapus">delete</button>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-1 relative">
                            <input type="url" class="mb-yt-url w-full bg-transparent border border-outline-variant/60 focus:border-primary rounded-xl font-body-md text-on-surface outline-none transition-all px-4 py-3 pr-24" placeholder="Tempel URL YouTube di sini (contoh: https://youtube.com/watch?v=...)">
                            <button type="button" onclick="mbEmbedYoutube(this)" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-primary text-on-primary rounded-lg font-label-sm font-bold hover:bg-primary-600 transition-colors">Muat</button>
                        </div>
                    </div>
                    <div class="mb-yt-preview rounded-xl overflow-hidden bg-surface-container-low border border-outline-variant/40 aspect-video flex items-center justify-center">
                        <div class="text-center text-on-surface-variant p-6">
                            <span class="material-symbols-outlined text-[48px] mb-2 opacity-40">smart_display</span>
                            <p class="font-body-sm">Pratinjau video akan muncul di sini</p>
                        </div>
                    </div>
                </div>
            </div>`;
        } else if (type === 'document') {
            markup = `
            <div class="mb-content-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl overflow-hidden shadow-sm hover:border-outline-variant transition-colors animate-fade-in-down" id="${blockId}">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">picture_as_pdf</span>
                        <span class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Dokumen</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="mbMoveBlock(this, -1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Atas">arrow_upward</button>
                        <button type="button" onclick="mbMoveBlock(this, 1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Bawah">arrow_downward</button>
                        <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                        <button type="button" onclick="this.closest('.mb-content-block').remove()" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1 rounded transition-colors" title="Hapus">delete</button>
                    </div>
                </div>
                <div class="p-5">
                    <div class="border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center gap-3 text-on-surface-variant hover:border-primary hover:bg-primary/5 transition-all cursor-pointer group" onclick="this.querySelector('input[type=file]').click()">
                        <span class="material-symbols-outlined text-[40px] group-hover:text-primary transition-colors">upload_file</span>
                        <p class="font-label-md font-bold group-hover:text-primary">Klik untuk unggah dokumen</p>
                        <p class="font-body-sm text-on-surface-variant/70">Mendukung PDF, DOCX, PPTX (Maks 25MB)</p>
                        <input type="file" accept=".pdf,.doc,.docx,.ppt,.pptx" class="hidden" onchange="mbPreviewDocument(this)">
                    </div>
                </div>
            </div>`;
        }
        
        canvas.insertAdjacentHTML('beforeend', markup);
        
        // Smooth scroll to new block
        const scrollArea = document.getElementById('mb-scroll-area');
        setTimeout(() => { scrollArea.scrollTo({ top: scrollArea.scrollHeight, behavior: 'smooth' }); }, 100);
    }

    function mbMoveBlock(btn, direction) {
        const block = btn.closest('.mb-content-block');
        const canvas = document.getElementById('mb-page-canvas');
        if (!block) return;
        
        if (direction === -1 && block.previousElementSibling) {
            canvas.insertBefore(block, block.previousElementSibling);
        } else if (direction === 1 && block.nextElementSibling) {
            canvas.insertBefore(block.nextElementSibling, block);
        }
    }

    function mbPreviewImage(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            const dropZone = input.closest('.border-dashed');
            dropZone.outerHTML = `
                <div class="relative rounded-xl overflow-hidden border border-outline-variant/40">
                    <img src="${e.target.result}" class="w-full max-h-[400px] object-contain bg-surface-container-low" alt="Uploaded">
                    <div class="absolute top-3 right-3 flex gap-2">
                        <button type="button" onclick="this.closest('.mb-content-block').querySelector('.border-dashed') || this.closest('div.relative').outerHTML = '<div class=\'border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center gap-3 text-on-surface-variant hover:border-primary hover:bg-primary/5 transition-all cursor-pointer group\' onclick=\'this.querySelector(input[type=file]).click()\'><span class=\'material-symbols-outlined text-[40px] group-hover:text-primary transition-colors\'>cloud_upload</span><p class=\'font-label-md font-bold group-hover:text-primary\'>Klik untuk unggah gambar</p><p class=\'font-body-sm text-on-surface-variant/70\'>Mendukung JPG, PNG, WebP (Maks 5MB)</p><input type=\'file\' accept=\'image/*\' class=\'hidden\' onchange=\'mbPreviewImage(this)\'></div>'" class="bg-surface/80 backdrop-blur-sm border border-outline-variant rounded-lg p-1.5 material-symbols-outlined text-on-surface-variant hover:text-error transition-colors" title="Hapus Gambar">close</button>
                    </div>
                </div>`;
        };
        reader.readAsDataURL(file);
    }

    function mbPreviewDocument(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const dropZone = input.closest('.border-dashed');
        const ext = file.name.split('.').pop().toUpperCase();
        dropZone.outerHTML = `
            <div class="flex items-center gap-4 p-4 bg-surface-container-low rounded-xl border border-outline-variant/40">
                <div class="w-12 h-12 bg-primary-container text-white rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-title-sm font-bold text-on-surface truncate">${file.name}</p>
                    <p class="font-body-sm text-on-surface-variant">${ext} • ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                </div>
                <span class="material-symbols-outlined text-success text-[28px]">check_circle</span>
            </div>`;
    }

    function mbEmbedYoutube(btn) {
        const block = btn.closest('.mb-content-block');
        const urlInput = block.querySelector('.mb-yt-url');
        const previewDiv = block.querySelector('.mb-yt-preview');
        let url = urlInput.value.trim();
        
        // Extract video ID
        let videoId = '';
        const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([\w-]{11})/);
        if (match) videoId = match[1];
        
        if (!videoId) {
            previewDiv.innerHTML = '<div class="text-center text-error p-6"><span class="material-symbols-outlined text-[32px]">error</span><p class="font-body-sm mt-2">URL YouTube tidak valid. Pastikan formatnya benar.</p></div>';
            return;
        }
        
        previewDiv.innerHTML = `<iframe class="w-full h-full rounded-xl" src="https://www.youtube.com/embed/${videoId}" title="YouTube Video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
    }

    // ==============================================
    // Quiz Builder Engine (Pilihan Ganda + Essay)
    // ==============================================

    let currentEditingQuizModule = null;
    let qbQuestionCounter = 0;

    function openQuizBuilder(btnEl, quizType) {
        currentEditingQuizModule = btnEl.closest('.quiz-module-block');
        const modal = document.getElementById('quiz-builder-overlay');
        const sessionTitleEl = btnEl.closest('.session-block').querySelector('h4');
        const iconWrap = document.getElementById('qb-icon-wrap');
        
        let typeName = quizType === 'final' ? 'Final Assessment' : 'Quiz Course';
        let sessionPrefix = sessionTitleEl ? sessionTitleEl.textContent.trim() : 'Sesi';
        document.getElementById('qb-title').textContent = typeName + ': ' + sessionPrefix;
        
        // Style based on type
        if (quizType === 'final') {
            iconWrap.className = 'w-12 h-12 bg-error-container text-error rounded-full flex items-center justify-center shrink-0';
            iconWrap.querySelector('.material-symbols-outlined').textContent = 'verified';
        } else {
            iconWrap.className = 'w-12 h-12 bg-primary-container text-white rounded-full flex items-center justify-center shrink-0';
            iconWrap.querySelector('.material-symbols-outlined').textContent = 'assignment';
        }
        
        // Restore state or init
        const list = document.getElementById('qb-questions-list');
        const instructionTa = document.getElementById('qb-instruction');
        const stateDiv = currentEditingQuizModule.querySelector('.qb-saved-state');
        
        if (stateDiv) {
            list.innerHTML = stateDiv.innerHTML;
            instructionTa.value = stateDiv.getAttribute('data-instruction') || '';
        } else {
            list.innerHTML = '';
            instructionTa.value = '';
            qbAddQuestion('mc');
        }
        qbQuestionCounter = list.querySelectorAll('.qb-question-block').length;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => { modal.classList.remove('opacity-0'); modal.classList.add('opacity-100'); }, 10);
        document.body.style.overflow = 'hidden';
        qbCalculateTotal();
    }

    function closeQuizBuilder() {
        const modal = document.getElementById('quiz-builder-overlay');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 300);
    }

    function saveQuizBuilder() {
        const list = document.getElementById('qb-questions-list');
        const instructionTa = document.getElementById('qb-instruction');
        
        // Sync DOM states
        list.querySelectorAll('input[type="text"], input[type="number"], input[type="url"]').forEach(i => i.setAttribute('value', i.value));
        list.querySelectorAll('textarea').forEach(t => t.textContent = t.value);
        list.querySelectorAll('input[type="radio"]').forEach(r => {
            if (r.checked) r.setAttribute('checked', 'checked');
            else r.removeAttribute('checked');
        });
        list.querySelectorAll('select').forEach(s => {
            s.querySelectorAll('option').forEach(o => {
                if (o.value === s.value) o.setAttribute('selected', 'selected');
                else o.removeAttribute('selected');
            });
        });
        
        if (currentEditingQuizModule) {
            let stateDiv = currentEditingQuizModule.querySelector('.qb-saved-state');
            if (!stateDiv) {
                stateDiv = document.createElement('div');
                stateDiv.className = 'qb-saved-state hidden';
                currentEditingQuizModule.appendChild(stateDiv);
            }
            stateDiv.innerHTML = list.innerHTML;
            stateDiv.setAttribute('data-instruction', instructionTa.value);
            
            // Update count badge
            const countEl = currentEditingQuizModule.querySelector('.qz-question-count');
            if (countEl) countEl.textContent = list.querySelectorAll('.qb-question-block').length;
        }
        
        const saveBtn = document.querySelector('#quiz-builder-overlay header button.bg-primary');
        const ogText = saveBtn.innerHTML;
        saveBtn.classList.replace('bg-primary', 'bg-success');
        saveBtn.classList.replace('hover:bg-primary-600', 'hover:bg-success');
        saveBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">check</span> Tersimpan';
        setTimeout(() => {
            saveBtn.classList.replace('bg-success', 'bg-primary');
            saveBtn.classList.replace('hover:bg-success', 'hover:bg-primary-600');
            saveBtn.innerHTML = ogText;
            closeQuizBuilder();
        }, 600);
    }

    function qbCalculateTotal() {
        const list = document.getElementById('qb-questions-list');
        const blocks = list.querySelectorAll('.qb-question-block');
        let total = 0;
        blocks.forEach(b => {
            const w = b.querySelector('.qb-weight-input');
            if (w) total += parseInt(w.value) || 0;
        });
        document.getElementById('qb-counter').textContent = 'Total ' + blocks.length + ' Pertanyaan';
        document.getElementById('qb-max-score').textContent = total;
    }

    function qbDeleteQuestion(btn) {
        const block = btn.closest('.qb-question-block');
        block.classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-200');
        setTimeout(() => {
            block.remove();
            qbRenumberQuestions();
            qbCalculateTotal();
        }, 200);
    }

    function qbRenumberQuestions() {
        const list = document.getElementById('qb-questions-list');
        list.querySelectorAll('.qb-question-block').forEach((block, i) => {
            block.setAttribute('data-index', i + 1);
            const label = block.querySelector('.qb-index-label');
            if (label) label.textContent = 'Soal ' + (i + 1);
        });
    }

    function qbAddQuestion(type) {
        const list = document.getElementById('qb-questions-list');
        qbQuestionCounter = list.querySelectorAll('.qb-question-block').length + 1;
        const qIndex = qbQuestionCounter;
        let markup = '';
        
        if (type === 'mc') {
            const optHtml = ['A','B','C','D'].map((opt, i) => `
                <label class="qb-option-row group/opt flex items-center gap-3 p-3 border border-outline-variant/60 rounded-xl cursor-pointer hover:bg-surface-container-lowest transition-colors shadow-sm relative overflow-hidden">
                    <div class="absolute inset-y-0 left-0 w-1 bg-transparent group-has-[:checked]/opt:bg-success transition-colors"></div>
                    <input type="radio" onchange="qbOptionSelected(this)" name="qb_q${qIndex}_correct" value="${opt}" ${i===0?'checked':''} class="ml-2 w-5 h-5 text-success border-outline-variant focus:ring-success cursor-pointer">
                    <span class="qb-opt-letter font-title-sm font-bold w-7 h-7 flex items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant border border-outline-variant/40 group-has-[:checked]/opt:bg-success group-has-[:checked]/opt:text-on-success group-has-[:checked]/opt:border-success transition-all">${opt}</span>
                    <input type="text" class="flex-1 bg-transparent py-1 border-b border-transparent outline-none font-body-md text-on-surface focus:border-primary pointer-events-auto transition-colors" placeholder="Ketik pilihan jawaban ${opt}...">
                    ${i >= 2 ? '<button type="button" onclick="const container = this.closest(\'.qb-options-container\'); this.closest(\'.qb-option-row\').remove(); qbRenumberOptions(container)" class="material-symbols-outlined text-on-surface-variant hover:text-error opacity-0 lg:group-hover/opt:opacity-100 transition-opacity p-1 relative z-10" title="Hapus Opsi">close</button>' : ''}
                </label>
            `).join('');
            
            markup = `
            <div class="qb-question-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-0 shadow-sm overflow-hidden animate-fade-in-down transition-colors hover:border-outline-variant" data-index="${qIndex}" data-type="mc">
                <div class="bg-surface-container-lowest border-b border-outline-variant/40 px-5 py-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="qb-index-label font-label-md font-bold text-on-surface bg-surface-container-low border border-outline-variant/60 px-3 py-1 rounded-lg">Soal ${qIndex}</span>
                        <span class="font-label-sm font-bold text-primary bg-primary/5 border border-primary/20 px-2 py-0.5 rounded-lg flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">checklist</span>Pilihan Ganda</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 bg-surface px-3 py-1.5 rounded-lg border border-outline/50 shadow-sm hover:border-primary transition-colors focus-within:border-primary">
                            <span class="font-label-sm text-on-surface-variant">Bobot</span>
                            <input type="number" class="qb-weight-input w-12 text-center font-title-sm font-bold text-on-surface bg-transparent outline-none" value="5" min="1" max="100" oninput="qbCalculateTotal()">
                        </div>
                        <button type="button" onclick="qbDeleteQuestion(this)" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1.5 rounded-lg hover:bg-error/10 transition-all" title="Hapus Soal">delete</button>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <textarea class="qb-question-body w-full bg-transparent border border-outline-variant/40 hover:border-outline focus:border-primary rounded-xl font-body-md text-on-surface outline-none resize-y min-h-[80px] p-4 transition-all" placeholder="Tuliskan pertanyaan Anda secara spesifik di sini..."></textarea>
                    
                    <!-- Image Support for MC -->
                    <div class="qb-image-attach">
                        <div class="border border-dashed border-outline-variant rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-primary hover:bg-primary/5 transition-all cursor-pointer group" onclick="this.querySelector('input[type=file]').click()">
                            <span class="material-symbols-outlined text-[20px] group-hover:text-primary">add_photo_alternate</span>
                            <span class="font-label-sm font-bold group-hover:text-primary">Lampirkan Gambar Pendukung (Opsional)</span>
                            <input type="file" accept="image/*" class="hidden" onchange="qbPreviewQuestionImage(this)">
                        </div>
                    </div>
                    
                    <div class="qb-options-container space-y-2">
                        ${optHtml}
                    </div>
                    
                    <div class="pl-0 sm:pl-4 mt-2">
                        <button type="button" onclick="qbAddOption(this)" class="flex items-center gap-2 text-primary font-label-sm font-bold hover:bg-primary/5 px-4 py-2 rounded-lg transition-colors border border-transparent hover:border-primary/20 w-fit">
                            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Pilihan Baru
                        </button>
                    </div>
                </div>
            </div>`;
        } else if (type === 'essay') {
            markup = `
            <div class="qb-question-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-0 shadow-sm overflow-hidden animate-fade-in-down transition-colors hover:border-outline-variant" data-index="${qIndex}" data-type="essay">
                <div class="bg-surface-container-lowest border-b border-outline-variant/40 px-5 py-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="qb-index-label font-label-md font-bold text-on-surface bg-surface-container-low border border-outline-variant/60 px-3 py-1 rounded-lg">Soal ${qIndex}</span>
                        <span class="font-label-sm font-bold text-secondary bg-secondary/5 border border-secondary/20 px-2 py-0.5 rounded-lg flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">edit_note</span>Isian / Essay</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 bg-surface px-3 py-1.5 rounded-lg border border-outline/50 shadow-sm hover:border-primary transition-colors focus-within:border-primary">
                            <span class="font-label-sm text-on-surface-variant">Bobot</span>
                            <input type="number" class="qb-weight-input w-12 text-center font-title-sm font-bold text-on-surface bg-transparent outline-none" value="10" min="1" max="100" oninput="qbCalculateTotal()">
                        </div>
                        <button type="button" onclick="qbDeleteQuestion(this)" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1.5 rounded-lg hover:bg-error/10 transition-all" title="Hapus Soal">delete</button>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <textarea class="qb-question-body w-full bg-transparent border border-outline-variant/40 hover:border-outline focus:border-primary rounded-xl font-body-md text-on-surface outline-none resize-y min-h-[80px] p-4 transition-all" placeholder="Tuliskan pertanyaan essay / isian Anda di sini..."></textarea>
                    
                    <!-- Answer Guidance -->
                    <div class="bg-surface-container-low border border-outline-variant/40 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-secondary">lightbulb</span>
                            <span class="font-label-sm font-bold text-on-surface uppercase tracking-wider">Panduan Jawaban (Untuk Penilaian)</span>
                        </div>
                        <textarea class="qb-answer-key w-full bg-transparent border border-outline-variant/40 hover:border-outline focus:border-primary rounded-xl font-body-sm text-on-surface outline-none resize-y min-h-[60px] p-3 transition-all" placeholder="Tuliskan kunci jawaban atau poin-poin penting yang harus ada dalam jawaban peserta"></textarea>
                        <div class="flex items-center gap-3">
                            <span class="font-label-sm text-on-surface-variant">Panjang jawaban:</span>
                            <select class="bg-surface border border-outline-variant/60 rounded-lg px-3 py-1.5 font-body-sm text-on-surface outline-none focus:border-primary transition-colors cursor-pointer">
                                <option value="short">Pendek (1-2 kalimat)</option>
                                <option value="medium" selected>Sedang (1 paragraf)</option>
                                <option value="long">Panjang (Multi-paragraf)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>`;
        }
        
        list.insertAdjacentHTML('beforeend', markup);
        qbCalculateTotal();
        
        // Scroll into view
        setTimeout(() => { list.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
    }

    function qbOptionSelected(radio) {
        // Visual feedback handled by CSS group-has-[:checked]
    }

    function qbAddOption(btn) {
        const block = btn.closest('.qb-question-block');
        const qIndex = block.getAttribute('data-index');
        const container = block.querySelector('.qb-options-container');
        const count = container.children.length;
        if (count >= 10) { Swal.fire({title: 'Batas Maksimum', text: 'Maksimal 10 pilihan (A-J).', icon: 'info', confirmButtonColor: '#E20613'}); return; }
        
        const letter = String.fromCharCode(65 + count);
        const markup = `
            <label class="qb-option-row group/opt flex items-center gap-3 p-3 border border-outline-variant/60 rounded-xl cursor-pointer hover:bg-surface-container-lowest transition-colors shadow-sm relative overflow-hidden animate-fade-in-down">
                <div class="absolute inset-y-0 left-0 w-1 bg-transparent group-has-[:checked]/opt:bg-success transition-colors"></div>
                <input type="radio" onchange="qbOptionSelected(this)" name="qb_q${qIndex}_correct" value="${letter}" class="ml-2 w-5 h-5 text-success border-outline-variant focus:ring-success cursor-pointer">
                <span class="qb-opt-letter font-title-sm font-bold w-7 h-7 flex items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant border border-outline-variant/40 group-has-[:checked]/opt:bg-success group-has-[:checked]/opt:text-on-success group-has-[:checked]/opt:border-success transition-all">${letter}</span>
                <input type="text" class="flex-1 bg-transparent py-1 border-b border-transparent outline-none font-body-md text-on-surface focus:border-primary pointer-events-auto transition-colors" placeholder="Ketik pilihan jawaban ${letter}...">
                <button type="button" onclick="const c = this.closest('.qb-options-container'); this.closest('.qb-option-row').remove(); qbRenumberOptions(c)" class="material-symbols-outlined text-on-surface-variant hover:text-error opacity-0 lg:group-hover/opt:opacity-100 transition-opacity p-1 relative z-10" title="Hapus Opsi">close</button>
            </label>
        `;
        container.insertAdjacentHTML('beforeend', markup);
    }

    function qbRenumberOptions(container) {
        if (!container) return;
        container.querySelectorAll('.qb-option-row').forEach((row, i) => {
            const letter = String.fromCharCode(65 + i);
            const badge = row.querySelector('.qb-opt-letter');
            if (badge) badge.textContent = letter;
            const radio = row.querySelector('input[type="radio"]');
            if (radio) radio.value = letter;
            const textInput = row.querySelector('input[type="text"]');
            if (textInput) textInput.placeholder = 'Ketik pilihan jawaban ' + letter + '...';
        });
    }

    function qbPreviewQuestionImage(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            const attachDiv = input.closest('.qb-image-attach');
            attachDiv.innerHTML = `
                <div class="relative rounded-xl overflow-hidden border border-outline-variant/40 bg-surface-container-low">
                    <img src="${e.target.result}" class="w-full max-h-[300px] object-contain" alt="Question Image">
                    <div class="absolute top-2 right-2">
                        <button type="button" onclick="qbRemoveQuestionImage(this)" class="bg-surface/80 backdrop-blur-sm border border-outline-variant rounded-lg p-1.5 material-symbols-outlined text-on-surface-variant hover:text-error transition-colors text-[18px]" title="Hapus Gambar">close</button>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }

    function qbRemoveQuestionImage(btn) {
        const attachDiv = btn.closest('.qb-image-attach');
        attachDiv.innerHTML = `
            <div class="border border-dashed border-outline-variant rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-primary hover:bg-primary/5 transition-all cursor-pointer group" onclick="this.querySelector('input[type=file]').click()">
                <span class="material-symbols-outlined text-[20px] group-hover:text-primary">add_photo_alternate</span>
                <span class="font-label-sm font-bold group-hover:text-primary">Lampirkan Gambar Pendukung (Opsional)</span>
                <input type="file" accept="image/*" class="hidden" onchange="qbPreviewQuestionImage(this)">
            </div>
        `;
    }

    // ==============================================
    // Initialize Logic
    // ==============================================
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.materi-builder-list').forEach(list => {
            checkEmptyHintList(list);
        });

        // Existing tags dropdown logic below...

        const container = document.getElementById('tags-dropdown-container');
        if(!container) return;
        
        const trigger = document.getElementById('tags-trigger');
        const menu = document.getElementById('tags-menu');
        const searchInput = document.getElementById('tags-search');
        const checkboxes = document.querySelectorAll('.tag-checkbox');
        const optionsContainer = document.getElementById('tags-options');
        
        // Toggle Dropdown
        trigger.addEventListener('click', (e) => {
            // Prevent toggling if clicked on a "close" icon inside the trigger
            if(e.target.textContent === 'close') return;
            menu.classList.toggle('hidden');
            if(!menu.classList.contains('hidden')) {
                searchInput.focus();
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                menu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        // Search Filter
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const options = optionsContainer.querySelectorAll('.tag-option');
            
            options.forEach(opt => {
                const val = opt.getAttribute('data-value');
                if (val.includes(term)) {
                    opt.style.display = 'flex';
                } else {
                    opt.style.display = 'none';
                }
            });
        });

        // Update selected tags display
        function updateDisplay() {
            const selected = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            // Clear old tags except for the chevron icon logic
            const tagsAndPlaceholder = Array.from(trigger.children).filter(el => !el.classList.contains('ml-auto'));
            tagsAndPlaceholder.forEach(el => el.remove());

            if (selected.length === 0) {
                const p = document.createElement('div');
                p.id = 'tags-placeholder';
                p.className = 'text-on-surface-variant font-body-md px-2 py-1';
                p.textContent = 'Pilih tags...';
                trigger.insertBefore(p, trigger.lastElementChild);
            } else {
                selected.forEach(tagText => {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center gap-1 pl-3 pr-2 py-1 bg-primary-container text-on-primary-container border border-primary rounded-full text-xs font-semibold shadow-sm';
                    
                    const text = document.createElement('span');
                    text.textContent = tagText;
                    
                    const removeBtn = document.createElement('span');
                    removeBtn.className = 'material-symbols-outlined text-[14px] cursor-pointer hover:bg-black/10 rounded-full w-4 h-4 flex items-center justify-center transition-colors font-bold z-10 relative';
                    removeBtn.textContent = 'close';
                    removeBtn.onclick = (e) => {
                        e.preventDefault();
                        e.stopPropagation(); // Stop event so dropdown toggle doesn't trigger
                        const cb = Array.from(checkboxes).find(c => c.value === tagText);
                        if(cb) {
                            cb.checked = false;
                            updateDisplay();
                        }
                    };
                    
                    badge.appendChild(text);
                    badge.appendChild(removeBtn);
                    trigger.insertBefore(badge, trigger.lastElementChild);
                });
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateDisplay);
        });
        
        // Initial state
        updateDisplay();
    });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>
</body>
</html>
