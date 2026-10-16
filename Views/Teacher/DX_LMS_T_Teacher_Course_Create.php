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
    <main class="dls-main overflow-y-visible flex-1 w-full lg:ml-[240px] px-4 sm:px-6 lg:px-8 pb-[120px] pt-[88px]">
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
                        $isActive = ($i === 1);
                        $colorClass = $isActive ? 'text-primary font-bold' : 'text-on-surface-variant font-medium';
                        $bgClass = $isActive ? 'bg-primary text-on-primary shadow-md shadow-primary/25 ring-4 ring-primary/10' : 'bg-surface-container text-on-surface-variant border border-outline-variant';
                    ?>
                        <!-- Step Item -->
                        <div id="stepper-item-<?= $i ?>" onclick="wizardGoToStep(<?= $i ?>)" class="flex flex-col items-center gap-1.5 relative z-10 w-32 group cursor-pointer select-none">
                            <div id="stepper-badge-<?= $i ?>" class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-[14px] <?= $bgClass ?> transition-all duration-300">
                                <span class="stepper-icon material-symbols-outlined text-[20px]"><?= $steps[$i]['icon'] ?></span>
                            </div>
                            <div class="text-center mt-1">
                                <p id="stepper-title-<?= $i ?>" class="font-label-sm text-[12px] sm:text-[13px] leading-tight <?= $colorClass ?>">
                                    <?= $i ?>. <?= $steps[$i]['title'] ?>
                                </p>
                                <p class="text-[10px] sm:text-[11px] text-on-surface-variant/80 mt-0.5 leading-none">
                                    <?= $steps[$i]['subtitle'] ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Connector Line -->
                        <?php if ($i < 4): ?>
                            <div id="stepper-line-<?= $i ?>" class="flex-1 h-[3px] rounded-full bg-surface-variant/60 -mt-8 mx-[-16px] z-0 transition-colors duration-300"></div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Unified Multi-Step Course Creator Container -->
            <div id="course-form-wizard" class="space-y-stack-xl">
                <?php $currentCourseId = $_GET['course_id'] ?? null; ?>
                <?php if ($currentCourseId): ?>
                    <input type="hidden" id="edit-course-id" value="<?= e((string) $currentCourseId) ?>">
                <?php endif; ?>
                
                <?php if (!empty($course['status']) && strtolower($course['status']) === 'rejected'): ?>
                <!-- Rejection Notice Banner from Admin (Option A: Slim Collapsible Banner) -->
                <div id="rejection-notice-wrapper" class="bg-error/5 border border-error/25 hover:border-error/40 rounded-2xl p-3 sm:px-4 sm:py-3 shadow-xs transition-all duration-300 mb-6">
                    <!-- Slim Header Bar (Always visible across all 4 steps without pushing content down) -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer select-none" onclick="toggleRejectionDrawer()">
                            <div class="w-8 h-8 rounded-xl bg-error text-white flex items-center justify-center shrink-0 shadow-xs">
                                <span class="material-symbols-outlined text-[18px]">assignment_late</span>
                            </div>
                            <div class="min-w-0 flex items-center gap-2 flex-wrap">
                                <span class="font-label-sm font-bold text-error uppercase tracking-wider text-[12px] sm:text-[13px] shrink-0 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-error animate-pulse"></span>
                                    Revisi Admin:
                                </span>
                                <span class="font-body-md text-on-surface font-semibold italic text-[13px] sm:text-[14px] truncate max-w-[240px] sm:max-w-md lg:max-w-xl">
                                    "<?= e($course['rejection_note']['note'] ?? 'Mohon periksa kembali materi dan kurikulum.') ?>"
                                </span>
                            </div>
                        </div>

                        <!-- Actions & Metadata -->
                        <div class="flex items-center gap-2 shrink-0">
                            <?php if (!empty($course['rejection_note']['date'])): ?>
                            <span class="hidden md:inline-flex items-center gap-1 text-[11px] font-medium text-on-surface-variant bg-surface px-2.5 py-1 rounded-lg border border-outline-variant/60 shadow-2xs">
                                <span class="material-symbols-outlined text-[13px] text-error">schedule</span>
                                <?= e($course['rejection_note']['date']) ?>
                            </span>
                            <?php endif; ?>
                            <button type="button" onclick="toggleRejectionDrawer()" id="rejection-toggle-btn" class="inline-flex items-center gap-1 text-[12px] font-bold text-error hover:text-white hover:bg-error bg-surface border border-error/30 px-3 py-1.5 rounded-xl transition-all shadow-2xs select-none cursor-pointer" title="Buka/Tutup Catatan Lengkap">
                                <span id="rejection-toggle-text">Detail Catatan</span>
                                <span id="rejection-toggle-icon" class="material-symbols-outlined text-[16px] transition-transform duration-200">expand_more</span>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Drawer Content -->
                    <div id="rejection-drawer-content" class="hidden border-t border-error/20 mt-3 pt-3 space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-[12px] font-bold text-error flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">account_circle</span>
                                Reviewer: <?= e($course['rejection_note']['admin_name'] ?? 'Admin LMS') ?>
                            </span>
                            <?php if (!empty($course['rejection_note']['date'])): ?>
                            <span class="md:hidden text-[11px] font-medium text-on-surface-variant flex items-center gap-1 bg-surface px-2 py-0.5 rounded border border-outline-variant/40">
                                <span class="material-symbols-outlined text-[12px] text-error">schedule</span>
                                <?= e($course['rejection_note']['date']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bg-surface p-3.5 rounded-xl border border-error/20 shadow-2xs">
                            <p class="font-label-sm font-bold text-error uppercase tracking-wider text-[10px] mb-1">Catatan Perbaikan Lengkap:</p>
                            <p class="font-body-md font-bold text-on-surface italic text-[14px] leading-relaxed whitespace-pre-line">
                                "<?= e($course['rejection_note']['note'] ?? 'Mohon periksa kembali materi dan kurikulum.') ?>"
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-1 text-[12px] text-on-surface-variant">
                            <p class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-error shrink-0">info</span>
                                <span>Sesuaikan kurikulum/materi sesuai arahan di atas, lalu ajukan kembali di <strong>Step 4 (Review)</strong>.</span>
                            </p>
                            <button type="button" onclick="toggleRejectionDrawer()" class="text-[11px] font-bold text-error hover:underline shrink-0">
                                Sembunyikan
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ====== STEP 1: INFORMASI COURSE ====== -->
                <section id="wizard-step-panel-1" class="wizard-step-panel bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm">
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
                                <input type="text" id="course-title-input" class="w-full bg-surface border border-outline-variant rounded-xl p-3.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50 shadow-sm" placeholder="Contoh: Fundamental 5S & K3" value="<?= e($course['title'] ?? '') ?>">
                                <p class="text-[12px] text-on-surface-variant/80 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-primary">info</span> Disertai dengan standar kompetensi operasional PT Denso Indonesia.
                                </p>
                            </div>
                            
                            <!-- Deskripsi Course Field -->
                            <div class="space-y-1.5">
                                <label class="font-label-md font-bold text-on-surface flex items-center gap-1">
                                    Deskripsi Course
                                </label>
                                <textarea id="course-desc-input" rows="4" class="w-full bg-surface border border-outline-variant rounded-xl p-3.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50 shadow-sm leading-relaxed" placeholder="Jelaskan tujuan kompetensi, sasaran pembelajaran, dan manfaat setelah menyelesaikan course ini..."><?= e(
                                    $course['description'] ?? '',
                                ) ?></textarea>
                                <p class="text-[12px] text-on-surface-variant/80 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">description</span> Ringkasan akan dipratinjau pada katalog dan halaman detail peserta.
                                </p>
                            </div>
                            
                            <!-- Tags Multi-Select Field -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3 md:col-span-2 pt-1">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <label class="font-label-md font-bold text-on-surface flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px] text-primary">sell</span>
                                            Tags / Kategori Course
                                            <span class="text-on-surface-variant font-normal text-xs">(Pilih kategori kompetensi yang relevan)</span>
                                        </label>
                                        <div class="flex items-center gap-2 self-start sm:self-auto">
                                            <span id="tags-count-badge" class="text-xs font-semibold px-2.5 py-0.5 rounded-full border border-outline-variant/60 bg-surface-container-low text-on-surface-variant transition-colors">
                                                0 Terpilih
                                            </span>
                                            <button type="button" id="tags-reset-btn" onclick="resetAllTags()" class="text-xs font-semibold text-rose-700 hover:text-rose-900 hover:underline hidden transition-colors">
                                                Reset Pilihan
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Inline Selectable Chips Container -->
                                    <div class="p-4 bg-white border border-outline-variant/80 rounded-2xl shadow-2xs space-y-3">
                                        <p class="text-[12px] text-on-surface-variant/90 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[15px] text-primary">touch_app</span>
                                            Klik kategori di bawah ini untuk memilih kompetensi course:
                                        </p>
                                        
                                        <div class="flex flex-wrap gap-2.5" id="tags-chips-wrapper">
                                            <?php
                                            $savedCategory = $course['category'] ?? '';
                                            $savedTags = array_map('trim', explode(',', strtolower($savedCategory)));
                                            
                                            // 14 Kategori Standar Industri LMS PT Denso Indonesia
                                            $availableTags = [
                                                ['name' => 'Safety', 'icon' => 'health_and_safety'],
                                                ['name' => 'Quality', 'icon' => 'verified'],
                                                ['name' => 'Core', 'label' => 'Core (5S & K3)', 'icon' => 'stars'],
                                                ['name' => 'Maintenance', 'icon' => 'build'],
                                                ['name' => 'Engineering', 'icon' => 'precision_manufacturing'],
                                                ['name' => 'Productivity', 'icon' => 'trending_up'],
                                                ['name' => 'Automation', 'icon' => 'smart_toy'],
                                                ['name' => 'Problem Solving', 'icon' => 'psychology'],
                                                ['name' => 'Compliance', 'icon' => 'gavel'],
                                                ['name' => 'Leadership', 'icon' => 'supervisor_account'],
                                                ['name' => 'Management', 'icon' => 'work'],
                                                ['name' => 'Culture', 'icon' => 'diversity_3'],
                                                ['name' => 'HR', 'icon' => 'badge'],
                                                ['name' => 'Soft Skill', 'icon' => 'lightbulb'],
                                            ];

                                            foreach ($availableTags as $tagItem):
                                                $tagName = $tagItem['name'];
                                                $tagLabel = $tagItem['label'] ?? $tagName;
                                                $tagIcon = $tagItem['icon'];
                                                $isTagChecked = in_array(strtolower($tagName), $savedTags) || in_array(strtolower($tagLabel), $savedTags);
                                            ?>
                                            <label class="tag-chip group inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border text-xs sm:text-[13px] font-semibold cursor-pointer select-none transition-all duration-200 <?= $isTagChecked ? 'bg-rose-50 text-rose-800 border-primary ring-2 ring-primary/10 shadow-xs' : 'bg-white text-on-surface border-outline-variant/70 hover:border-primary/60 hover:bg-rose-50/40 hover:text-primary shadow-2xs' ?>" data-default-icon="<?= e($tagIcon) ?>">
                                                <input type="checkbox" class="tag-checkbox sr-only" value="<?= e($tagName) ?>" <?= $isTagChecked ? 'checked' : '' ?>>
                                                <span class="material-symbols-outlined tag-chip-icon text-[16px] transition-transform group-hover:scale-110 <?= $isTagChecked ? 'text-primary' : 'text-outline group-hover:text-primary' ?>">
                                                    <?= $isTagChecked ? 'check_circle' : $tagIcon ?>
                                                </span>
                                                <span class="tag-chip-label"><?= e($tagLabel) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <p class="text-[11.5px] text-on-surface-variant/80 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px] text-emerald-600">info</span>
                                        Kategori yang dipilih akan otomatis tercantum pada katalog course karyawan dan ringkasan pengajuan review.
                                    </p>
                                </div>
                            </div>
                    </div>
                </section>

                <!-- ====== STEP 2: STRUKTUR COURSE ====== -->
                <section id="wizard-step-panel-2" class="wizard-step-panel hidden bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm">
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
                        <?php
                        $renderSessions = !empty($existing_sessions) ? $existing_sessions : [
                            [
                                'session_id' => 0,
                                'session_title' => 'Sesi 1: Dasar Materi',
                                'materials' => [
                                    [
                                        'material_title' => 'Materi Pengantar Kompetensi',
                                        'material_type' => 'document',
                                        'material_description' => 'Silakan pelajari materi pengantar berikut sebelum melanjutkan ke kuis.',
                                    ]
                                ],
                                'quizzes' => []
                            ]
                        ];
                        foreach ($renderSessions as $sIdx => $sess):
                            $sNum = $sIdx + 1;
                            $sTitle = $sess['session_title'] ?? ('Sesi ' . $sNum . ': Modul Pembelajaran');
                            $sMats = $sess['materials'] ?? [];
                            $sQuizzes = $sess['quizzes'] ?? [];
                        ?>
                        <div class="session-block border border-outline-variant/60 rounded-xl overflow-hidden shadow-sm" data-session-id="<?= e((string)($sess['session_id'] ?? 0)) ?>">
                            <div class="bg-surface-container-low px-4 py-3 border-b border-outline-variant/60 flex justify-between items-center cursor-move">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-on-surface-variant hover:text-on-surface cursor-grab">drag_indicator</span>
                                    <h4 class="font-title-sm font-bold text-on-surface"><?= e($sTitle) ?></h4>
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
                                    <?php foreach ($sMats as $mIdx => $mat): 
                                        $mTitle = $mat['material_title'] ?? ('Materi ' . ($mIdx + 1));
                                        $mType = strtolower($mat['material_type'] ?? 'document');
                                        $mDesc = $mat['material_description'] ?? '';
                                        $isPreTest = in_array($mType, ['pre-test', 'pre_test']);
                                        $isPostTest = in_array($mType, ['post-test', 'post_test']);

                                        if ($isPreTest) {
                                            $badgeTitle = 'Pre-Test';
                                            $badgeBg = 'bg-secondary-container/40 text-secondary';
                                            $badgeText = 'text-secondary';
                                            $icon = 'quiz';
                                            $kelolaClass = 'bg-secondary/10 text-secondary hover:bg-secondary hover:text-white';
                                            $kelolaOnClick = 'openAssessmentBuilder(this)';
                                        } elseif ($isPostTest) {
                                            $badgeTitle = 'Post-Test';
                                            $badgeBg = 'bg-warning-container/40 text-warning-800 dark:text-warning';
                                            $badgeText = 'text-warning-800 dark:text-warning';
                                            $icon = 'assignment_turned_in';
                                            $kelolaClass = 'bg-warning/15 text-warning-800 dark:text-warning hover:bg-warning hover:text-white';
                                            $kelolaOnClick = 'openAssessmentBuilder(this)';
                                        } else {
                                            $badgeTitle = 'Materi';
                                            $badgeBg = 'bg-primary-container text-white';
                                            $badgeText = 'text-primary';
                                            $icon = ($mType === 'video') ? 'play_circle' : (($mType === 'pdf') ? 'picture_as_pdf' : 'description');
                                            $kelolaClass = 'bg-primary/10 text-primary hover:bg-primary hover:text-white';
                                            $kelolaOnClick = 'openMaterialBuilder(this)';
                                        }
                                    ?>
                                    <div class="module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group">
                                        <div class="w-10 h-10 rounded-lg <?= $badgeBg ?> flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined"><?= $icon ?></span>
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <p class="font-label-sm <?= $badgeText ?> mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold"><?= $badgeTitle ?></p>
                                            <div class="relative flex items-center group/title max-w-lg">
                                                <input type="text" onkeydown="handleModuleTitleEnter(event, this)" onblur="handleModuleTitleEnter(null, this, true)" class="w-full bg-transparent border-b border-outline-variant/40 hover:border-primary focus:border-primary focus:bg-surface-container-low/50 rounded-sm px-1.5 py-0.5 outline-none font-title-sm font-bold text-on-surface text-[13px] sm:text-[14px] transition-all pr-7 cursor-text placeholder:font-normal placeholder:text-on-surface-variant/50" value="<?= e($mTitle) ?>" placeholder="Klik untuk isi judul <?= strtolower($badgeTitle) ?>..." title="Klik untuk mengubah judul modul">
                                                <span class="material-symbols-outlined text-[15px] text-on-surface-variant/40 absolute right-1.5 pointer-events-none group-hover/title:text-primary transition-colors" title="Dapat diedit langsung">edit</span>
                                            </div>
                                        </div>
                                        <div class="mb-saved-state hidden" data-pages="<?= e($mDesc) ?>"></div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" onclick="<?= $kelolaOnClick ?>" class="flex items-center gap-1.5 px-3 py-1.5 <?= $kelolaClass ?> rounded-lg font-label-sm font-bold transition-all shadow-xs" title="Kelola <?= $badgeTitle ?>">
                                                <span class="material-symbols-outlined text-[16px]">edit</span> Kelola
                                            </button>
                                            <button type="button" onclick="const list = this.closest('.materi-builder-list'); this.closest('.module-block').remove(); checkEmptyHintList(list);" class="material-symbols-outlined text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors p-1.5 rounded-lg text-[20px]" title="Hapus Modul">delete</button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Empty State Hint -->
                                <div class="materi-empty-hint <?= empty($sMats) ? 'flex' : 'hidden' ?> flex-col items-center justify-center py-6 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[48px] text-surface-variant mb-2">auto_awesome_mosaic</span>
                                    <p class="font-bold font-title-md text-on-surface">Belum ada modul</p>
                                    <p class="font-body-sm max-w-sm mt-1">Tambahkan pre-test, materi, atau post-test di bawah ini untuk menyusun sesi pembelajaran.</p>
                                </div>

                                <!-- Action Toolbar -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-dashed border-outline-variant">
                                    <button type="button" onclick="appendModuleBlock(this, 'Pre-Test', 'quiz', 'secondary')" class="flex flex-col items-center justify-center gap-1.5 p-3 border border-outline-variant rounded-lg hover:bg-secondary/5 hover:border-secondary transition-all group">
                                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-secondary group-hover:scale-110 transition-transform">quiz</span>
                                        <span class="font-label-sm font-bold text-on-surface group-hover:text-secondary">Pre-Test</span>
                                    </button>
                                    <button type="button" onclick="appendModuleBlock(this, 'Materi', 'description', 'primary')" class="flex flex-col items-center justify-center gap-1.5 p-3 border border-outline-variant rounded-lg hover:bg-primary/5 hover:border-primary transition-all group">
                                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary group-hover:scale-110 transition-transform">description</span>
                                        <span class="font-label-sm font-bold text-on-surface group-hover:text-primary">Materi</span>
                                    </button>
                                    <button type="button" onclick="appendModuleBlock(this, 'Post-Test', 'assignment_turned_in', 'warning')" class="flex flex-col items-center justify-center gap-1.5 p-3 border border-outline-variant rounded-lg hover:bg-warning/10 hover:border-warning transition-all group">
                                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-warning group-hover:scale-110 transition-transform">assignment_turned_in</span>
                                        <span class="font-label-sm font-bold text-on-surface group-hover:text-warning">Post-Test</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tab Content: Quiz Workspace -->
                            <div class="bg-surface-container-lowest p-5 flex-col hidden" data-workspace="quiz">
                                <!-- Quiz Items List -->
                                <div class="quiz-items-list space-y-3 mb-5">
                                    <?php
                                    $hasCustomQuiz = !empty($sQuizzes);
                                    $quizCount = 0;
                                    $quizTitle = 'Quiz Course';
                                    if ($hasCustomQuiz) {
                                        $qz0 = $sQuizzes[0];
                                        $quizTitle = $qz0['quiz_title'] ?? 'Quiz Course';
                                        $quizCount = count($qz0['questions'] ?? []);
                                    }
                                    ?>
                                    <!-- Quiz Course -->
                                    <div class="quiz-module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group">
                                        <div class="w-10 h-10 rounded-lg bg-primary-container text-white flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined">assignment</span>
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <p class="font-label-sm text-primary mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold"><?= e($quizTitle) ?></p>
                                            <p class="font-body-sm text-on-surface-variant"><span class="qz-question-count font-bold text-on-surface"><?= $quizCount ?></span> Pertanyaan</p>
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
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ====== STEP 3: TARGET AUDIENCE (RECOMMENDATION) ====== -->
                <section id="wizard-step-panel-3" class="wizard-step-panel hidden bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm">
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
                                        <input type="checkbox" id="select-all-candidates" onchange="toggleSelectAllCandidates(this)" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary" checked>
                                        <span id="emp-count-display">Pilih Semua (Kandidat Relevan Ditemukan)</span>
                                    </label>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-primary/10 text-primary">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Target Aktif
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-4 font-body-sm max-h-[460px] overflow-y-auto" id="candidate-list-container">
                                    <?php
                                    $candidateList = !empty($candidates) ? $candidates : [
                                        ['employee_id' => 1, 'name' => 'Budi Santoso', 'npk' => '12345', 'jabatan' => 'Operator', 'golongan' => 'I-1', 'departemen' => 'Manufacturing', 'divisi' => 'Produksi', 'seksi' => 'Assembling'],
                                        ['employee_id' => 6, 'name' => 'Citra Lestari', 'npk' => '11223', 'jabatan' => 'Supervisor', 'golongan' => 'III-1', 'departemen' => 'Quality', 'divisi' => 'QA', 'seksi' => 'Pemeriksaan'],
                                        ['employee_id' => 2, 'name' => 'Ahmad Rizal', 'npk' => '67890', 'jabatan' => 'Leader', 'golongan' => 'II-2', 'departemen' => 'Manufacturing', 'divisi' => 'Produksi', 'seksi' => 'Assembling'],
                                        ['employee_id' => 7, 'name' => 'Dian Sastro', 'npk' => '23456', 'jabatan' => 'Manager', 'golongan' => 'IV-1', 'departemen' => 'Engineering', 'divisi' => 'PE', 'seksi' => 'Desain'],
                                        ['employee_id' => 8, 'name' => 'Eko Prasetyo', 'npk' => '34567', 'jabatan' => 'Operator', 'golongan' => 'I-2', 'departemen' => 'Manufacturing', 'divisi' => 'Produksi', 'seksi' => 'Machining'],
                                        ['employee_id' => 9, 'name' => 'Fani Amelia', 'npk' => '45678', 'jabatan' => 'Staff', 'golongan' => 'II-1', 'departemen' => 'HR', 'divisi' => 'Personalia', 'seksi' => 'Rekrutmen'],
                                        ['employee_id' => 10, 'name' => 'Gilang Ramadhan', 'npk' => '56789', 'jabatan' => 'Staff', 'golongan' => 'II-2', 'departemen' => 'Finance', 'divisi' => 'Akuntansi', 'seksi' => 'Pajak'],
                                    ];
                                    foreach ($candidateList as $emp):
                                        $empName = $emp['fullname'] ?? ($emp['name'] ?? 'Karyawan');
                                        $empId = $emp['employee_id'] ?? 0;
                                        $initials = strtoupper(substr($empName, 0, 1));
                                        $jabatanVal = strtolower($emp['jabatan'] ?? '');
                                        $golonganVal = strtolower($emp['golongan'] ?? '');
                                        $deptVal = strtolower($emp['department'] ?? ($emp['departemen'] ?? ''));
                                        $divVal = strtolower($emp['division'] ?? ($emp['divisi'] ?? ''));
                                        $seksiVal = strtolower($emp['section'] ?? ($emp['seksi'] ?? ''));
                                    ?>
                                    <label class="emp-card flex items-center gap-3 p-4 rounded-xl border border-outline-variant/60 bg-surface hover:border-primary/50 hover:bg-surface-container-lowest cursor-pointer transition-all shadow-xs group"
                                           data-emp-id="<?= e($empId) ?>"
                                           data-npk="<?= e($emp['npk']) ?>"
                                           data-name="<?= e($empName) ?>"
                                           data-jabatan="<?= e($jabatanVal) ?>" 
                                           data-golongan="<?= e($golonganVal) ?>" 
                                           data-departemen="<?= e($deptVal) ?>" 
                                           data-divisi="<?= e($divVal) ?>" 
                                           data-seksi="<?= e($seksiVal) ?>">
                                        <input type="checkbox" onchange="updateCandidateCount()" class="candidate-check emp-checkbox w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary shrink-0" value="<?= e($empId) ?>" data-npk="<?= e($emp['npk']) ?>" data-name="<?= e($empName) ?>" checked>
                                        <div class="w-10 h-10 rounded-xl bg-primary-container/60 text-primary font-bold flex items-center justify-center shrink-0">
                                            <?= e($initials) ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="font-title-sm font-bold text-on-surface group-hover:text-primary transition-colors truncate max-w-[130px]"><?= e($empName) ?></p>
                                                <span class="font-label-xs px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-medium shrink-0">NPK: <?= e($emp['npk']) ?></span>
                                            </div>
                                            <p class="font-label-sm text-on-surface-variant text-[12px] mt-0.5 flex items-center gap-1.5 truncate">
                                                <span class="material-symbols-outlined text-[14px]">work</span> <?= e($emp['jabatan']) ?> • <?= e($emp['department'] ?? ($emp['departemen'] ?? '')) ?>
                                            </p>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="bg-surface-container-low px-5 py-3 border-t border-outline-variant/60 flex items-center justify-between font-label-sm">
                                    <span class="text-on-surface-variant text-[12px]">Kandidat akan diusulkan pada saat pengiriman course ke Admin</span>
                                    <span id="emp-count-bottom" class="font-bold text-primary flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">how_to_reg</span> <span id="emp-count-num"><?= count($candidateList) ?></span> Karyawan Terpilih
                                    </span>
                                </div>
                            </div>
                            
                            <script>
                            function toggleSelectAllCandidates(masterEl) {
                                const isChecked = masterEl.checked;
                                const cards = document.querySelectorAll('.emp-card');
                                cards.forEach(card => {
                                    if (card.style.display !== 'none') {
                                        const cb = card.querySelector('.candidate-check');
                                        if (cb) cb.checked = isChecked;
                                    }
                                });
                                updateCandidateCount();
                            }

                            function updateCandidateCount() {
                                const allChecks = document.querySelectorAll('.emp-card .candidate-check:checked, .emp-card .emp-checkbox:checked');
                                const count = allChecks.length;
                                const numEl = document.getElementById('emp-count-num');
                                if (numEl) numEl.textContent = count;
                                const bottomEl = document.getElementById('emp-count-bottom');
                                if (bottomEl) {
                                    bottomEl.innerHTML = `<span class="material-symbols-outlined text-[16px]">how_to_reg</span> <span id="emp-count-num">${count}</span> Karyawan Terpilih`;
                                }
                                const reviewCountEl = document.getElementById('review-candidates-count');
                                if (reviewCountEl) {
                                    reviewCountEl.textContent = `${count} Karyawan Terpilih`;
                                }
                            }

                            function applyCandidateFilter() {
                                const filters = {
                                    jabatan: document.getElementById('filter-jabatan')?.value.toLowerCase().trim() || '',
                                    golongan: document.getElementById('filter-golongan')?.value.toLowerCase().trim() || '',
                                    departemen: document.getElementById('filter-departemen')?.value.toLowerCase().trim() || '',
                                    divisi: document.getElementById('filter-divisi')?.value.toLowerCase().trim() || '',
                                    seksi: document.getElementById('filter-seksi')?.value.toLowerCase().trim() || ''
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

                                const displayEl = document.getElementById('emp-count-display');
                                if (displayEl) {
                                    displayEl.innerText = `Pilih Semua (${visibleCount} Kandidat Relevan Ditemukan)`;
                                }
                                updateCandidateCount();
                            }
                            </script>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ====== STEP 4: REVIEW & SUBMIT ====== -->
                <section id="wizard-step-panel-4" class="wizard-step-panel hidden bg-surface-container-lowest border border-outline-variant/80 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
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
                                <h4 id="review-title" class="font-title-md font-bold text-on-surface mb-1 line-clamp-2"><?= e($course['title'] ?? 'Nama Course') ?></h4>
                                <p id="review-desc" class="font-body-sm text-on-surface-variant/80 line-clamp-2"><?= e($course['description'] ?? 'Deskripsi course belum diisi.') ?></p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between font-label-sm">
                                <span class="text-on-surface-variant">Kategori</span>
                                <span id="review-category-code" class="font-bold text-on-surface"><?= e($course['category'] ?? 'Umum') ?></span>
                            </div>
                        </div>

                        <!-- Card 2: Struktur Kurikulum -->
                        <div class="border border-outline-variant/70 rounded-2xl p-5 bg-surface-container-low/40 flex flex-col justify-between hover:border-primary/50 transition-all shadow-sm">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-label-xs font-bold text-on-surface-variant uppercase tracking-wider px-2.5 py-1 rounded-full bg-surface-container">Kurikulum</span>
                                    <span class="material-symbols-outlined text-primary text-[22px]">library_books</span>
                                </div>
                                <h4 id="review-sessions-count" class="font-title-md font-bold text-on-surface mb-1">1 Sesi Pembelajaran</h4>
                                <p id="review-sessions-desc" class="font-body-sm text-on-surface-variant/80">Terdiri dari modul materi pembelajaran dan evaluasi kuis.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between font-label-sm">
                                <span class="text-on-surface-variant">Status Materi</span>
                                <span id="review-materials-status" class="font-bold text-success flex items-center gap-1">
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
                                <h4 id="review-candidates-count" class="font-title-md font-bold text-on-surface mb-1"><?= count($candidateList) ?> Karyawan Terpilih</h4>
                                <p id="review-candidates-desc" class="font-body-sm text-on-surface-variant/80">Diusulkan berdasarkan kesesuaian departemen, jabatan, dan golongan kompetensi.</p>
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
                            <h5 class="font-title-sm font-bold text-on-surface"><?= (!empty($course['status']) && strtolower($course['status']) === 'rejected') ? 'Pengajuan Ulang Perbaikan Course ke Admin' : 'Konfirmasi Pengiriman ke Admin' ?></h5>
                            <p class="font-body-sm text-on-surface-variant mt-1 leading-relaxed">
                                <?= (!empty($course['status']) && strtolower($course['status']) === 'rejected') ? 'Course ini telah disesuaikan berdasarkan catatan instruksi revisi Admin. Menekan tombol <span class="font-bold text-primary">"Submit ke Admin"</span> akan mengirimkan revisi terbaru ke sistem untuk peninjauan ulang dan persetujuan.' : 'Menekan tombol <span class="font-bold text-primary">"Submit ke Admin"</span> berarti mengajukan course ini secara resmi ke database Denso LMS untuk peninjauan (review) dan aktivasi kelas oleh Admin. Anda juga dapat memilih <span class="font-bold text-on-surface">"Simpan Perubahan"</span> jika masih ingin menyempurnakan kurikulum.' ?>
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Wizard Navigation Actions (Sticky Bottom on Desktop, Fixed Below on Mobile) -->
                <div class="fixed bottom-0 left-0 lg:left-[240px] right-0 bg-surface shadow-[0_-8px_20px_-10px_rgba(0,0,0,0.08)] border-t border-outline-variant/60 z-40 transition-all drop-shadow-sm pb-safe">
                    <div class="w-full flex justify-between items-center px-4 sm:px-6 lg:px-8 h-20 lg:h-24">
                        <!-- Left Action -->
                        <div>
                            <button type="button" id="wizard-btn-prev" onclick="wizardPrevStep()" class="hidden px-5 py-2.5 rounded-xl border border-outline-variant bg-surface text-on-surface font-label-md font-bold hover:bg-surface-container hover:border-on-surface-variant/40 shadow-sm transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
                            </button>
                            <button type="button" id="wizard-btn-cancel" onclick="window.history.back()" class="px-5 py-2.5 rounded-xl border border-outline-variant bg-surface text-on-surface font-label-md font-bold hover:bg-surface-container hover:border-on-surface-variant/40 shadow-sm transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">close</span> Batal
                            </button>
                        </div>

                        <!-- Right Action -->
                        <div class="flex items-center gap-3">
                            <button type="button" id="wizard-btn-draft" onclick="submitCourseToDatabase('Draft')" class="px-5 py-2.5 rounded-xl border border-outline-variant bg-white text-on-surface font-label-md font-bold hover:bg-surface-container shadow-sm transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">save</span> Simpan Perubahan
                            </button>
                            <button type="button" id="wizard-btn-next" onclick="wizardNextStep()" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-md shadow-primary/25 hover:shadow-lg transition-all flex items-center gap-2">
                                Selanjutnya <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                            </button>
                            <button type="button" id="wizard-btn-submit" onclick="submitCourseToDatabase('Submitted')" class="hidden px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-md shadow-primary/25 hover:shadow-lg transition-all flex items-center gap-2">
                                Submit ke Admin <span class="material-symbols-outlined text-[18px]">send</span>
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
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
                            <span class="material-symbols-outlined text-[20px]"></span> Tambah Blok Konten
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
                            <button type="button" onclick="mbPromptAddVideoBlock()" class="flex-1 min-w-[120px] max-w-[150px] flex flex-col items-center gap-3 p-5 border border-outline-variant/60 bg-surface rounded-2xl hover:bg-primary/5 hover:border-primary/40 hover:shadow-md transition-all group hover:-translate-y-1">
                                <div class="w-12 h-12 rounded-full bg-surface-container-high/50 group-hover:bg-primary/10 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[26px] text-on-surface-variant group-hover:text-primary transition-colors">play_circle</span>
                                </div>
                                <span class="font-label-sm font-bold text-on-surface-variant group-hover:text-primary transition-colors">Video (YouTube / MP4)</span>
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
    // ==============================================
    // Multi-Step Wizard Engine & SQL Server Persistence
    // ==============================================
    let currentWizardStep = 1;

    // Toggle Rejection Notice Drawer (Option A: Slim Collapsible Banner)
    function toggleRejectionDrawer() {
        const drawer = document.getElementById('rejection-drawer-content');
        const icon = document.getElementById('rejection-toggle-icon');
        const text = document.getElementById('rejection-toggle-text');
        if (!drawer) return;
        
        const isHidden = drawer.classList.contains('hidden');
        if (isHidden) {
            drawer.classList.remove('hidden');
            if (icon) icon.textContent = 'expand_less';
            if (text) text.textContent = 'Tutup Catatan';
        } else {
            drawer.classList.add('hidden');
            if (icon) icon.textContent = 'expand_more';
            if (text) text.textContent = 'Detail Catatan';
        }
    }

    function wizardGoToStep(step) {
        if (step < 1 || step > 4) return;
        
        // If attempting to go past Step 1, validate that Course Name is filled
        if (step > 1) {
            const titleInput = document.getElementById('course-title-input');
            if (!titleInput || !titleInput.value.trim()) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Judul Wajib Diisi',
                        text: 'Silakan isi Nama Course pada Langkah 1 terlebih dahulu.',
                        icon: 'warning',
                        confirmButtonColor: '#E20613'
                    });
                } else {
                    alert('Silakan isi Nama Course terlebih dahulu.');
                }
                if (titleInput) titleInput.focus();
                return;
            }
        }

        currentWizardStep = step;

        // Toggle panels
        for (let i = 1; i <= 4; i++) {
            const panel = document.getElementById('wizard-step-panel-' + i);
            if (panel) {
                if (i === step) {
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                }
            }
        }

        // Update stepper header indicators
        const stepIcons = { 1: 'info', 2: 'library_books', 3: 'groups', 4: 'done_all' };
        for (let i = 1; i <= 4; i++) {
            const badge = document.getElementById('stepper-badge-' + i);
            const title = document.getElementById('stepper-title-' + i);
            const line = document.getElementById('stepper-line-' + i);
            const iconSpan = badge ? badge.querySelector('.stepper-icon') : null;

            if (badge) {
                badge.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-[14px] transition-all duration-300';
                
                if (i < step) {
                    // Completed
                    badge.classList.add('bg-success', 'text-on-success');
                    if (iconSpan) iconSpan.textContent = 'check';
                    if (title) {
                        title.className = 'font-label-sm text-[12px] sm:text-[13px] leading-tight text-success font-bold';
                    }
                } else if (i === step) {
                    // Active
                    badge.classList.add('bg-primary', 'text-on-primary', 'shadow-md', 'shadow-primary/25', 'ring-4', 'ring-primary/10');
                    if (iconSpan) iconSpan.textContent = stepIcons[i];
                    if (title) {
                        title.className = 'font-label-sm text-[12px] sm:text-[13px] leading-tight text-primary font-bold';
                    }
                } else {
                    // Upcoming
                    badge.classList.add('bg-surface-container', 'text-on-surface-variant', 'border', 'border-outline-variant');
                    if (iconSpan) iconSpan.textContent = stepIcons[i];
                    if (title) {
                        title.className = 'font-label-sm text-[12px] sm:text-[13px] leading-tight text-on-surface-variant font-medium';
                    }
                }
            }

            if (line) {
                line.className = 'flex-1 h-[3px] rounded-full -mt-8 mx-[-16px] z-0 transition-colors duration-300 ' + (i < step ? 'bg-success' : 'bg-surface-variant/60');
            }
        }

        // Toggle navigation buttons
        const btnPrev = document.getElementById('wizard-btn-prev');
        const btnCancel = document.getElementById('wizard-btn-cancel');
        const btnNext = document.getElementById('wizard-btn-next');
        const btnSubmit = document.getElementById('wizard-btn-submit');

        if (btnPrev && btnCancel) {
            if (step > 1) {
                btnPrev.classList.remove('hidden');
                btnCancel.classList.add('hidden');
            } else {
                btnPrev.classList.add('hidden');
                btnCancel.classList.remove('hidden');
            }
        }

        if (btnNext && btnSubmit) {
            if (step === 4) {
                btnNext.classList.add('hidden');
                btnSubmit.classList.remove('hidden');
                renderReviewStep4();
            } else {
                btnNext.classList.remove('hidden');
                btnSubmit.classList.add('hidden');
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function wizardNextStep() {
        if (currentWizardStep < 4) {
            wizardGoToStep(currentWizardStep + 1);
        }
    }

    function wizardPrevStep() {
        if (currentWizardStep > 1) {
            wizardGoToStep(currentWizardStep - 1);
        }
    }

    function renderReviewStep4() {
        // 1. Identity
        const titleInput = document.getElementById('course-title-input');
        const descInput = document.getElementById('course-desc-input');
        const title = titleInput ? titleInput.value.trim() : '';
        const desc = descInput ? descInput.value.trim() : '';
        
        const reviewTitle = document.getElementById('review-title');
        const reviewDesc = document.getElementById('review-desc');
        const reviewCategory = document.getElementById('review-category-code');

        if (reviewTitle) reviewTitle.textContent = title || 'Nama Course Belum Diisi';
        if (reviewDesc) reviewDesc.textContent = desc || 'Deskripsi course belum diisi.';

        // Selected Tags
        const selectedTags = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(cb => cb.value);
        if (reviewCategory) {
            reviewCategory.textContent = selectedTags.length > 0 ? selectedTags.join(', ') : 'Umum';
        }

        // 2. Curriculum
        const sessions = document.querySelectorAll('#sessions-container .session-block');
        const sessionCount = sessions.length;
        let materialCount = 0;
        let assessmentCount = 0;
        sessions.forEach(sess => {
            sess.querySelectorAll('.materi-builder-list .module-block').forEach(m => {
                const label = m.querySelector('.font-label-sm')?.textContent.trim().toLowerCase() || '';
                if (label.includes('test')) assessmentCount++;
                else materialCount++;
            });
            sess.querySelectorAll('.quiz-items-list .quiz-module-block').forEach(qb => {
                const count = parseInt(qb.querySelector('.qz-question-count')?.textContent.trim() || '0');
                if (count > 0) assessmentCount++;
            });
        });

        const reviewSessionsCount = document.getElementById('review-sessions-count');
        const reviewSessionsDesc = document.getElementById('review-sessions-desc');
        const reviewMaterialsStatus = document.getElementById('review-materials-status');
        
        let summaryParts = [];
        if (materialCount > 0) summaryParts.push(materialCount + ' Modul');
        if (assessmentCount > 0) summaryParts.push(assessmentCount + ' Evaluasi/Tes');
        const totalItems = materialCount + assessmentCount;

        if (reviewSessionsCount) {
            reviewSessionsCount.textContent = sessionCount + ' Sesi Pembelajaran' + (totalItems > 0 ? ' (' + summaryParts.join(', ') + ')' : ' (0 Modul)');
        }
        if (reviewSessionsDesc) {
            reviewSessionsDesc.textContent = 'Mencakup ' + sessionCount + ' sesi dengan materi kurikulum dan evaluasi kompetensi.';
        }
        if (reviewMaterialsStatus) {
            if (totalItems > 0) {
                reviewMaterialsStatus.className = 'font-bold text-success flex items-center gap-1';
                reviewMaterialsStatus.innerHTML = '<span class="material-symbols-outlined text-[16px]">check_circle</span> Siap Digunakan';
            } else {
                reviewMaterialsStatus.className = 'font-bold text-warning flex items-center gap-1';
                reviewMaterialsStatus.innerHTML = '<span class="material-symbols-outlined text-[16px]">pending</span> Belum Ada Modul';
            }
        }

        // 3. Candidates
        const selectedEmps = document.querySelectorAll('.candidate-check:checked, .emp-checkbox:checked');
        const uniqueEmpIds = new Set(Array.from(selectedEmps).map(cb => cb.value));
        const empCount = uniqueEmpIds.size;
        const reviewCandidatesCount = document.getElementById('review-candidates-count');
        const reviewCandidatesDesc = document.getElementById('review-candidates-desc');
        if (reviewCandidatesCount) {
            reviewCandidatesCount.textContent = empCount + ' Karyawan Terpilih';
        }
        if (reviewCandidatesDesc) {
            reviewCandidatesDesc.textContent = empCount > 0 
                ? 'Kandidat telah diseleksi berdasarkan jabatan, golongan, dan departemen Denso.'
                : 'Belum ada karyawan terpilih (course terbuka untuk target audience rekomendasi).';
        }
    }

    function collectCoursePayload(status) {
        const editCourseId = document.getElementById('edit-course-id')?.value || null;
        const title = document.getElementById('course-title-input')?.value.trim() || 'Untitled Course';
        const description = document.getElementById('course-desc-input')?.value.trim() || '';
        const selectedTags = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(cb => cb.value);
        const category = selectedTags.length > 0 ? selectedTags.join(', ').substring(0, 50) : 'Umum';

        // Candidate IDs
        const candidateEmployeeIds = Array.from(new Set(Array.from(document.querySelectorAll('.emp-checkbox:checked, .candidate-check:checked')).map(cb => cb.value)));

        // Filter selections for targeting
        const targetJabatan = document.getElementById('filter-jabatan')?.value || '';
        const targetGolongan = document.getElementById('filter-golongan')?.value || '';
        const targetDepartemen = document.getElementById('filter-departemen')?.value || '';

        // Helper to extract assessment questions from .ab-saved-state
        function extractAbQuestions(stateDiv) {
            if (!stateDiv) return [];
            const questions = [];
            stateDiv.querySelectorAll('.ab-question-block').forEach((qEl, qIdx) => {
                const qText = qEl.querySelector('textarea')?.value || qEl.querySelector('textarea')?.textContent || ('Pertanyaan ' + (qIdx + 1));
                const weight = parseInt(qEl.querySelector('.ab-weight')?.value || qEl.querySelector('.ab-weight')?.getAttribute('value') || '5');
                const choices = [];
                qEl.querySelectorAll('.ab-option-row').forEach((optRow, oIdx) => {
                    const optInput = optRow.querySelector('input[type="text"]');
                    const optText = optInput ? (optInput.value || optInput.getAttribute('value') || '') : '';
                    const isCorrect = !!(optRow.querySelector('input[type="radio"]:checked') || optRow.querySelector('input[type="radio"][checked]'));
                    choices.push({
                        choice_text: optText || ('Pilihan ' + String.fromCharCode(65 + oIdx)),
                        is_correct: isCorrect ? 1 : 0,
                        choice_order: oIdx + 1
                    });
                });
                questions.push({
                    question_text: qText,
                    question_type: 'multiple_choice',
                    question_order: qIdx + 1,
                    score_weight: weight,
                    choices: choices
                });
            });
            return questions;
        }

        // Helper to extract quiz questions from .qb-saved-state
        function extractQbQuestions(stateDiv) {
            if (!stateDiv) return [];
            const questions = [];
            stateDiv.querySelectorAll('.qb-question-block').forEach((qEl, qIdx) => {
                const qText = qEl.querySelector('textarea')?.value || qEl.querySelector('textarea')?.textContent || ('Pertanyaan ' + (qIdx + 1));
                const choices = [];
                qEl.querySelectorAll('.qb-option-row, .ab-option-row').forEach((optRow, oIdx) => {
                    const optInput = optRow.querySelector('input[type="text"]');
                    const optText = optInput ? (optInput.value || optInput.getAttribute('value') || '') : '';
                    const isCorrect = !!(optRow.querySelector('input[type="radio"]:checked') || optRow.querySelector('input[type="radio"][checked]'));
                    choices.push({
                        choice_text: optText || ('Pilihan ' + String.fromCharCode(65 + oIdx)),
                        is_correct: isCorrect ? 1 : 0,
                        choice_order: oIdx + 1
                    });
                });
                questions.push({
                    question_text: qText,
                    question_type: 'multiple_choice',
                    score_weight: 10,
                    question_order: qIdx + 1,
                    choices: choices
                });
            });
            return questions;
        }

        let courseFinalAssessment = null;

        // Sessions & Materials
        const sessions = [];
        const sessionBlocks = document.querySelectorAll('#sessions-container .session-block');
        sessionBlocks.forEach((sess, sIdx) => {
            const titleEl = sess.querySelector('h4');
            const sessionTitle = titleEl ? titleEl.textContent.trim() : ('Sesi ' + (sIdx + 1));

            const materials = [];
            sess.querySelectorAll('.materi-builder-list .module-block').forEach((mod, mIdx) => {
                const modTitleInput = mod.querySelector('input[type="text"]');
                const modTitle = modTitleInput ? (modTitleInput.value.trim() || ('Modul ' + (mIdx + 1))) : ('Modul ' + (mIdx + 1));
                const typeLabel = mod.querySelector('.font-label-sm')?.textContent.trim().toLowerCase() || 'materi';
                
                if (typeLabel.includes('pre-test') || typeLabel.includes('pre_test')) {
                    const abState = mod.querySelector('.ab-saved-state');
                    const questions = extractAbQuestions(abState);
                    materials.push({
                        title: modTitle,
                        type: 'pre_test',
                        instructions: abState ? (abState.getAttribute('data-instruction') || '') : '',
                        duration: 15,
                        attempt_limit: 1,
                        passing_grade: 60,
                        questions: questions,
                        sequence_order: mIdx + 1
                    });
                } else if (typeLabel.includes('post-test') || typeLabel.includes('post_test')) {
                    const abState = mod.querySelector('.ab-saved-state');
                    const questions = extractAbQuestions(abState);
                    materials.push({
                        title: modTitle,
                        type: 'post_test',
                        instructions: abState ? (abState.getAttribute('data-instruction') || '') : '',
                        duration: 20,
                        attempt_limit: 3,
                        passing_grade: 75,
                        questions: questions,
                        sequence_order: mIdx + 1
                    });
                } else {
                    let matType = 'document';
                    const iconName = mod.querySelector('.material-symbols-outlined')?.textContent.trim() || '';
                    if (iconName === 'play_circle') matType = 'video';
                    else if (iconName === 'picture_as_pdf') matType = 'pdf';

                    const mbState = mod.querySelector('.mb-saved-state');
                    const content = mbState ? (mbState.getAttribute('data-pages') || '') : '';

                    materials.push({
                        title: modTitle,
                        type: matType,
                        content: content,
                        estimated_duration: 15,
                        sequence_order: mIdx + 1
                    });
                }
            });

            // Session Quiz & Final Assessment from quiz workspace
            let quiz = null;
            const qzList = sess.querySelector('.quiz-items-list');
            if (qzList) {
                // Find regular quiz block (item 0)
                const regularQuizBlock = qzList.children[0];
                if (regularQuizBlock) {
                    const qState = regularQuizBlock.querySelector('.qb-saved-state');
                    const qQuestions = extractQbQuestions(qState);
                    if (qQuestions.length > 0) {
                        quiz = {
                            title: 'Quiz ' + sessionTitle,
                            passing_score: 70,
                            duration_minutes: 20,
                            questions: qQuestions
                        };
                    }
                }
                // Find final assessment block (item 1)
                const finalBlock = qzList.children[1];
                if (finalBlock) {
                    const fState = finalBlock.querySelector('.qb-saved-state');
                    const fQuestions = extractQbQuestions(fState);
                    if (fQuestions.length > 0) {
                        courseFinalAssessment = {
                            title: 'Final Assessment: ' + title,
                            passing_score: 75,
                            duration_minutes: 45,
                            questions: fQuestions
                        };
                    }
                }
            }

            sessions.push({
                title: sessionTitle,
                description: '',
                sequence_order: sIdx + 1,
                materials: materials,
                quiz: quiz
            });
        });

        return {
            course_id: editCourseId,
            title: title,
            description: description,
            category: category,
            tags: selectedTags,
            level: 'Beginner',
            status: status, // 'Draft' or 'Submitted'
            target_jabatan: targetJabatan,
            target_golongan: targetGolongan,
            target_departemen: targetDepartemen,
            candidate_employee_ids: candidateEmployeeIds,
            sessions: sessions,
            final_assessment: courseFinalAssessment
        };
    }

    function submitCourseToDatabase(status) {
        const titleInput = document.getElementById('course-title-input');
        if (!titleInput || !titleInput.value.trim()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Judul Diperlukan',
                    text: 'Nama Course wajib diisi sebelum menyimpan data ke database.',
                    icon: 'warning',
                    confirmButtonColor: '#E20613'
                });
            } else {
                alert('Nama Course wajib diisi.');
            }
            wizardGoToStep(1);
            if (titleInput) titleInput.focus();
            return;
        }

        const payload = collectCoursePayload(status);

        const isSubmitted = (status === 'Submitted');
        const loadingTitle = isSubmitted ? 'Mengirim Course ke Admin...' : 'Menyimpan Perubahan Course...';
        const loadingText = 'Sedang memproses penyimpanan ke database MS SQL Server...';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: loadingTitle,
                text: loadingText,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        fetch('?route=/teacher/course/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: isSubmitted ? 'Berhasil Diajukan!' : 'Perubahan Berhasil Disimpan!',
                        text: data.message || 'Data course berhasil disimpan ke database SQL Server.',
                        icon: 'success',
                        confirmButtonColor: '#E20613',
                        confirmButtonText: 'Buka Halaman Course'
                    }).then(() => {
                        window.location.href = data.redirect_url || ('?route=/teacher/course/detail&course_id=' + (data.course_id || ''));
                    });
                } else {
                    alert('Course berhasil disimpan!');
                    window.location.href = data.redirect_url || '?route=/teacher/courses';
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Gagal Menyimpan',
                        text: data.error || 'Terjadi kendala saat menyimpan course.',
                        icon: 'error',
                        confirmButtonColor: '#E20613'
                    });
                } else {
                    alert('Gagal: ' + (data.error || 'Error'));
                }
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Koneksi Terputus',
                    text: 'Gagal menghubungi server database. Periksa koneksi lokal SQL Server Anda.',
                    icon: 'error',
                    confirmButtonColor: '#E20613'
                });
            } else {
                alert('Koneksi database error.');
            }
        });
    }

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
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Minimal harus ada 1 sesi dalam course.',
                    icon: 'warning',
                    confirmButtonColor: '#E20613'
                });
            } else {
                alert('Minimal harus ada 1 sesi dalam course.');
            }
            return;
        }

        const session = btn.closest('.session-block');
        const sessionTitle = session.querySelector('.session-title-input')?.value || 'Sesi ini';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Sesi Course?',
                text: `Apakah Anda yakin ingin menghapus "${sessionTitle}" beserta seluruh materi, kuis, dan asesmen di dalamnya?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus Sesi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    session.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                    setTimeout(() => {
                        session.remove();
                        updateSessionNumbers();
                    }, 300);
                }
            });
        } else if (confirm('Apakah Anda yakin ingin menghapus sesi ini beserta semua materinya?')) {
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
        
        let badgeBgClass = '', badgeTextClass = '', iconName = icon;
        let kelolaBtnClass = '';
        let kelolaOnClick = 'openMaterialBuilder(this)';
        let defaultPlaceholder = 'Isi judul modul baru...';
        
        if (colorContext === 'secondary' || title.toLowerCase().includes('pre-test')) {
            badgeBgClass = 'bg-secondary-container/40 text-secondary';
            badgeTextClass = 'text-secondary';
            iconName = 'quiz';
            kelolaBtnClass = 'bg-secondary/10 text-secondary hover:bg-secondary hover:text-white';
            kelolaOnClick = 'openAssessmentBuilder(this)';
            defaultPlaceholder = 'Contoh: Pre-Test Pemahaman Awal';
        } else if (colorContext === 'warning' || title.toLowerCase().includes('post-test')) {
            badgeBgClass = 'bg-warning-container/40 text-warning-800 dark:text-warning';
            badgeTextClass = 'text-warning-800 dark:text-warning';
            iconName = 'assignment_turned_in';
            kelolaBtnClass = 'bg-warning/15 text-warning-800 dark:text-warning hover:bg-warning hover:text-white';
            kelolaOnClick = 'openAssessmentBuilder(this)';
            defaultPlaceholder = 'Contoh: Post-Test Evaluasi Akhir Sesi';
        } else {
            badgeBgClass = 'bg-primary-container text-white';
            badgeTextClass = 'text-primary';
            iconName = icon || 'description';
            kelolaBtnClass = 'bg-primary/10 text-primary hover:bg-primary hover:text-white';
            kelolaOnClick = 'openMaterialBuilder(this)';
            defaultPlaceholder = 'Contoh: SOP Standar Operasional Kerja';
        }

        const template = `
            <div class="module-block border border-outline-variant rounded-xl p-3 flex gap-3 items-center bg-surface hover:border-primary transition-colors cursor-default shadow-sm group animate-fade-in-down">
                <div class="w-10 h-10 rounded-lg ${badgeBgClass} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined">${iconName}</span>
                </div>
                <div class="flex-1 min-w-0 flex flex-col justify-center">
                    <p class="font-label-sm ${badgeTextClass} mb-0.5 uppercase tracking-wider text-[10px] sm:text-[11px] font-bold">${title}</p>
                    <div class="relative flex items-center group/title max-w-lg">
                        <input type="text" onkeydown="handleModuleTitleEnter(event, this)" onblur="handleModuleTitleEnter(null, this, true)" class="w-full bg-transparent border-b border-outline-variant/40 hover:border-primary focus:border-primary focus:bg-surface-container-low/50 rounded-sm px-1.5 py-0.5 outline-none font-title-sm font-bold text-on-surface text-[13px] sm:text-[14px] transition-all pr-7 cursor-text placeholder:font-normal placeholder:text-on-surface-variant/50" value="" placeholder="${defaultPlaceholder}" title="Klik untuk mengubah judul modul">
                        <span class="material-symbols-outlined text-[15px] text-on-surface-variant/40 absolute right-1.5 pointer-events-none group-hover/title:text-primary transition-colors" title="Dapat diedit langsung">edit</span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="${kelolaOnClick}" class="flex items-center gap-1.5 px-3 py-1.5 ${kelolaBtnClass} rounded-lg font-label-sm font-bold transition-all shadow-xs" title="Kelola ${title}">
                        <span class="material-symbols-outlined text-[16px]">edit</span> Kelola
                    </button>
                    <button type="button" onclick="const list = this.closest('.materi-builder-list'); this.closest('.module-block').remove(); checkEmptyHintList(list);" class="material-symbols-outlined text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors p-1.5 rounded-lg text-[20px]" title="Hapus Modul">delete</button>
                </div>
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
        const block = btn.closest('.ab-question-block');
        const qIndex = block ? (block.getAttribute('data-index') || '') : '';
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Soal ' + (qIndex ? qIndex : '') + '?',
                text: 'Pertanyaan dan seluruh opsi pilihan jawaban ini akan dihapus dari modul tes.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus Soal',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    block.classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-200');
                    setTimeout(() => {
                        block.remove();
                        abRenumberQuestions();
                        abCalculateTotal();
                    }, 200);
                }
            });
        } else if (confirm('Hapus soal ini?')) {
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
                <div class="p-6 space-y-5">
                    <div class="group relative">
                        <textarea class="w-full bg-transparent border border-outline-variant/40 hover:border-outline focus:border-primary focus:bg-surface-container-lowest rounded-xl font-body-md text-on-surface outline-none transition-all resize-y min-h-[80px] p-4 shadow-sm" placeholder="Tuliskan pertanyaan Anda secara spesifik di sini..."></textarea>
                    </div>
                    
                    <!-- Media Support (Image & Video) Side by Side -->
                    <div class="qb-media-attach-container grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="qb-image-attach">
                            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-primary hover:bg-primary/5 hover:text-primary transition-all cursor-pointer group bg-surface-container-lowest" onclick="this.querySelector('input[type=file]').click()">
                                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-primary">add_photo_alternate</span>
                                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Gambar (Opsional)</span>
                                <input type="file" accept="image/*" class="hidden" onchange="qbPreviewQuestionImage(this)">
                            </div>
                        </div>
                        <div class="qb-video-attach">
                            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-secondary hover:bg-secondary/5 hover:text-secondary transition-all cursor-pointer group bg-surface-container-lowest" onclick="qbAttachVideoPrompt(this)">
                                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-secondary">video_library</span>
                                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Video (Opsional)</span>
                                <input type="file" accept="video/mp4,video/webm" class="hidden qb-video-file-input" onchange="qbPreviewQuestionVideoFile(this)">
                            </div>
                        </div>
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

    function mbAddBlock(type, videoMode = 'youtube') {
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
        } else if (type === 'youtube' || type === 'video' || type === 'video_local') {
            const initialMode = (type === 'video_local' || videoMode === 'local') ? 'local' : 'youtube';
            markup = `
            <div class="mb-content-block bg-surface-container-lowest border border-outline-variant/60 rounded-2xl overflow-hidden shadow-sm hover:border-outline-variant transition-colors animate-fade-in-down" id="${blockId}">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant/40 bg-surface-container-low/20">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">play_circle</span>
                        <span class="font-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Video Pembelajaran</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="mbMoveBlock(this, -1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Atas">arrow_upward</button>
                        <button type="button" onclick="mbMoveBlock(this, 1)" class="material-symbols-outlined text-on-surface-variant hover:text-primary p-1 rounded transition-colors" title="Pindah Bawah">arrow_downward</button>
                        <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                        <button type="button" onclick="this.closest('.mb-content-block').remove()" class="material-symbols-outlined text-on-surface-variant hover:text-error p-1 rounded transition-colors" title="Hapus">delete</button>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Segmented Button Mode Switcher -->
                    <div class="flex items-center gap-2 p-1 bg-surface-container-low rounded-xl w-fit border border-outline-variant/50">
                        <button type="button" onclick="mbSwitchVideoMode(this, 'youtube')" class="mb-vtab-yt px-3.5 py-1.5 rounded-lg font-label-sm font-bold transition-all flex items-center gap-1.5 ${initialMode === 'youtube' ? 'bg-surface text-primary shadow-xs' : 'text-on-surface-variant hover:text-on-surface'}">
                            <span class="material-symbols-outlined text-[16px] text-error">play_circle</span> Link YouTube
                        </button>
                        <button type="button" onclick="mbSwitchVideoMode(this, 'local')" class="mb-vtab-local px-3.5 py-1.5 rounded-lg font-label-sm font-bold transition-all flex items-center gap-1.5 ${initialMode === 'local' ? 'bg-surface text-secondary shadow-xs' : 'text-on-surface-variant hover:text-on-surface'}">
                            <span class="material-symbols-outlined text-[16px] text-secondary">upload_file</span> Upload Video Lokal (MP4)
                        </button>
                    </div>

                    <!-- Panel 1: YouTube Mode -->
                    <div class="mb-video-yt-panel space-y-4 ${initialMode === 'youtube' ? '' : 'hidden'}">
                        <div class="flex gap-3">
                            <div class="flex-1 relative">
                                <input type="url" class="mb-yt-url w-full bg-transparent border border-outline-variant/60 focus:border-primary rounded-xl font-body-md text-on-surface outline-none transition-all px-4 py-3 pr-24" placeholder="Tempel URL YouTube di sini (contoh: https://youtube.com/watch?v=...)">
                                <button type="button" onclick="mbEmbedYoutube(this)" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-primary text-on-primary rounded-lg font-label-sm font-bold hover:bg-primary-600 transition-colors">Muat</button>
                            </div>
                        </div>
                        <div class="mb-yt-preview rounded-xl overflow-hidden bg-surface-container-low border border-outline-variant/40 aspect-video flex items-center justify-center">
                            <div class="text-center text-on-surface-variant p-6">
                                <span class="material-symbols-outlined text-[48px] mb-2 opacity-40">smart_display</span>
                                <p class="font-body-sm">Pratinjau video YouTube akan muncul di sini</p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Local Video Upload Mode -->
                    <div class="mb-video-local-panel space-y-4 ${initialMode === 'local' ? '' : 'hidden'}">
                        <div class="mb-video-local-dropzone border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center gap-3 text-on-surface-variant hover:border-secondary hover:bg-secondary/5 transition-all cursor-pointer group" onclick="this.querySelector('input[type=file]').click()">
                            <div class="w-14 h-14 rounded-full bg-secondary/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-[32px] text-secondary">video_file</span>
                            </div>
                            <p class="font-label-md font-bold text-on-surface group-hover:text-secondary">Klik untuk unggah file video</p>
                            <p class="font-body-sm text-on-surface-variant/70">Mendukung MP4, WebM (Maks 100MB) • Dapat diputar secara offline</p>
                            <input type="file" accept="video/mp4,video/webm" class="hidden mb-video-file-input" onchange="mbPreviewVideoFile(this)">
                        </div>
                        <div class="mb-video-local-preview hidden"></div>
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
        return blockId;
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

    function mbPromptAddVideoBlock() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Tambah Video Pembelajaran',
                text: 'Pilih metode untuk menambahkan media video pada materi:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '🔗 Link YouTube',
                denyButtonText: '📁 Upload File Video',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#E20613',
                denyButtonColor: '#4355B9',
                cancelButtonColor: '#6B7280',
                width: '560px',
                hideClass: {
                    popup: '',
                    backdrop: ''
                },
                customClass: {
                    actions: '!flex !flex-row !flex-nowrap !items-center !justify-center !gap-2.5',
                    confirmButton: '!m-0 !px-4 !py-2.5 !text-sm !font-bold !rounded-xl !whitespace-nowrap',
                    denyButton: '!m-0 !px-4 !py-2.5 !text-sm !font-bold !rounded-xl !whitespace-nowrap',
                    cancelButton: '!m-0 !px-4 !py-2.5 !text-sm !font-bold !rounded-xl !whitespace-nowrap'
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    mbAddBlock('video', 'youtube');
                } else if (res.isDenied) {
                    Swal.close();
                    setTimeout(() => {
                        const blockId = mbAddBlock('video', 'local');
                        const newBlock = document.getElementById(blockId);
                        const fileInput = newBlock ? newBlock.querySelector('.mb-video-file-input') : null;
                        if (fileInput) {
                            fileInput.value = '';
                            fileInput.click();
                        }
                    }, 20);
                }
            });
        } else {
            mbAddBlock('video', 'youtube');
        }
    }

    function mbSwitchVideoMode(btn, mode) {
        const block = btn.closest('.mb-content-block');
        const ytPanel = block.querySelector('.mb-video-yt-panel');
        const localPanel = block.querySelector('.mb-video-local-panel');
        const ytTab = block.querySelector('.mb-vtab-yt');
        const localTab = block.querySelector('.mb-vtab-local');
        
        if (mode === 'youtube') {
            ytPanel.classList.remove('hidden');
            localPanel.classList.add('hidden');
            ytTab.className = 'mb-vtab-yt px-3.5 py-1.5 rounded-lg font-label-sm font-bold transition-all flex items-center gap-1.5 bg-surface text-primary shadow-xs';
            localTab.className = 'mb-vtab-local px-3.5 py-1.5 rounded-lg font-label-sm font-bold transition-all flex items-center gap-1.5 text-on-surface-variant hover:text-on-surface';
        } else {
            ytPanel.classList.add('hidden');
            localPanel.classList.remove('hidden');
            ytTab.className = 'mb-vtab-yt px-3.5 py-1.5 rounded-lg font-label-sm font-bold transition-all flex items-center gap-1.5 text-on-surface-variant hover:text-on-surface';
            localTab.className = 'mb-vtab-local px-3.5 py-1.5 rounded-lg font-label-sm font-bold transition-all flex items-center gap-1.5 bg-surface text-secondary shadow-xs';
        }
    }

    function mbPreviewVideoFile(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const fileUrl = URL.createObjectURL(file);
        const block = input.closest('.mb-content-block');
        const dropzone = block.querySelector('.mb-video-local-dropzone');
        const previewDiv = block.querySelector('.mb-video-local-preview');
        
        dropzone.classList.add('hidden');
        previewDiv.classList.remove('hidden');
        previewDiv.innerHTML = `
            <div class="relative rounded-xl overflow-hidden border border-outline-variant/60 bg-black shadow-md group">
                <video src="${fileUrl}" controls class="w-full max-h-[380px] object-contain rounded-xl bg-black"></video>
                <div class="absolute top-3 right-3 flex items-center gap-2 z-10">
                    <span class="bg-black/60 backdrop-blur-md text-white font-label-sm text-xs px-2.5 py-1 rounded-md border border-white/20 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px] text-success">offline_bolt</span> Offline Ready
                    </span>
                    <button type="button" onclick="mbResetLocalVideo(this)" class="bg-surface/90 hover:bg-surface text-on-surface hover:text-error backdrop-blur-sm border border-outline-variant/80 rounded-lg p-1.5 material-symbols-outlined transition-colors text-[18px] shadow-sm" title="Hapus / Ganti Video">close</button>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs text-on-surface-variant mt-2 px-1">
                <span class="font-bold flex items-center gap-1 truncate max-w-[70%]">
                    <span class="material-symbols-outlined text-[16px] text-secondary">movie</span> ${file.name}
                </span>
                <span>${(file.size / 1024 / 1024).toFixed(2)} MB</span>
            </div>
        `;
    }

    function mbResetLocalVideo(btn) {
        const block = btn.closest('.mb-content-block');
        const dropzone = block.querySelector('.mb-video-local-dropzone');
        const previewDiv = block.querySelector('.mb-video-local-preview');
        const fileInput = block.querySelector('.mb-video-file-input');
        
        if (fileInput) fileInput.value = '';
        previewDiv.innerHTML = '';
        previewDiv.classList.add('hidden');
        dropzone.classList.remove('hidden');
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
        const qIndex = block ? (block.getAttribute('data-index') || '') : '';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Soal ' + (qIndex ? qIndex : '') + '?',
                text: 'Pertanyaan dan seluruh pilihan jawaban ini akan dihapus dari daftar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus Soal',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    block.classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-200');
                    setTimeout(() => {
                        block.remove();
                        qbRenumberQuestions();
                        qbCalculateTotal();
                    }, 200);
                }
            });
        } else if (confirm('Hapus soal ini?')) {
            block.classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-200');
            setTimeout(() => {
                block.remove();
                qbRenumberQuestions();
                qbCalculateTotal();
            }, 200);
        }
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
                    
                    <!-- Media Support (Image & Video) Side by Side -->
                    <div class="qb-media-attach-container grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="qb-image-attach">
                            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-primary hover:bg-primary/5 hover:text-primary transition-all cursor-pointer group bg-surface-container-lowest" onclick="this.querySelector('input[type=file]').click()">
                                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-primary">add_photo_alternate</span>
                                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Gambar (Opsional)</span>
                                <input type="file" accept="image/*" class="hidden" onchange="qbPreviewQuestionImage(this)">
                            </div>
                        </div>
                        <div class="qb-video-attach">
                            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-secondary hover:bg-secondary/5 hover:text-secondary transition-all cursor-pointer group bg-surface-container-lowest" onclick="qbAttachVideoPrompt(this)">
                                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-secondary">video_library</span>
                                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Video (Opsional)</span>
                                <input type="file" accept="video/mp4,video/webm" class="hidden qb-video-file-input" onchange="qbPreviewQuestionVideoFile(this)">
                            </div>
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
                    
                    <!-- Media Support (Image & Video) Side by Side for Essay -->
                    <div class="qb-media-attach-container grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="qb-image-attach">
                            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-primary hover:bg-primary/5 hover:text-primary transition-all cursor-pointer group bg-surface-container-lowest" onclick="this.querySelector('input[type=file]').click()">
                                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-primary">add_photo_alternate</span>
                                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Gambar (Opsional)</span>
                                <input type="file" accept="image/*" class="hidden" onchange="qbPreviewQuestionImage(this)">
                            </div>
                        </div>
                        <div class="qb-video-attach">
                            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-secondary hover:bg-secondary/5 hover:text-secondary transition-all cursor-pointer group bg-surface-container-lowest" onclick="qbAttachVideoPrompt(this)">
                                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-secondary">video_library</span>
                                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Video (Opsional)</span>
                                <input type="file" accept="video/mp4,video/webm" class="hidden qb-video-file-input" onchange="qbPreviewQuestionVideoFile(this)">
                            </div>
                        </div>
                    </div>
                    
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
                <div class="relative rounded-xl overflow-hidden border border-outline-variant/40 bg-surface-container-low shadow-sm">
                    <img src="${e.target.result}" class="w-full max-h-[220px] object-contain rounded-xl" alt="Question Image">
                    <div class="absolute top-2 right-2 z-10">
                        <button type="button" onclick="qbRemoveQuestionImage(this)" class="bg-surface/90 backdrop-blur-sm border border-outline-variant/80 rounded-lg p-1.5 material-symbols-outlined text-on-surface hover:text-error transition-colors text-[18px] shadow-sm" title="Hapus Gambar">close</button>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }

    function qbRemoveQuestionImage(btn) {
        const attachDiv = btn.closest('.qb-image-attach');
        attachDiv.innerHTML = `
            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-primary hover:bg-primary/5 hover:text-primary transition-all cursor-pointer group bg-surface-container-lowest" onclick="this.querySelector('input[type=file]').click()">
                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-primary">add_photo_alternate</span>
                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Gambar (Opsional)</span>
                <input type="file" accept="image/*" class="hidden" onchange="qbPreviewQuestionImage(this)">
            </div>
        `;
    }

    function qbAttachVideoPrompt(triggerEl) {
        const attachDiv = triggerEl.classList.contains('qb-video-attach') ? triggerEl : triggerEl.closest('.qb-video-attach');
        if (!attachDiv) return;
        
        let fileInput = attachDiv.querySelector('.qb-video-file-input');
        if (!fileInput) {
            fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'video/mp4,video/webm';
            fileInput.className = 'hidden qb-video-file-input';
            fileInput.onchange = function() { qbPreviewQuestionVideoFile(this); };
            attachDiv.appendChild(fileInput);
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Lampirkan Video Pendukung',
                text: 'Pilih metode untuk menambahkan media video pada soal ini:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '🔗 Link YouTube',
                denyButtonText: '📁 Upload File Video',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#E20613',
                denyButtonColor: '#4355B9',
                cancelButtonColor: '#6B7280',
                width: '560px',
                hideClass: {
                    popup: '',
                    backdrop: ''
                },
                customClass: {
                    actions: '!flex !flex-row !flex-nowrap !items-center !justify-center !gap-2.5',
                    confirmButton: '!m-0 !px-4 !py-2.5 !text-sm !font-bold !rounded-xl !whitespace-nowrap',
                    denyButton: '!m-0 !px-4 !py-2.5 !text-sm !font-bold !rounded-xl !whitespace-nowrap',
                    cancelButton: '!m-0 !px-4 !py-2.5 !text-sm !font-bold !rounded-xl !whitespace-nowrap'
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Masukkan URL YouTube',
                        input: 'url',
                        inputPlaceholder: 'https://www.youtube.com/watch?v=... atau https://youtu.be/...',
                        showCancelButton: true,
                        confirmButtonText: 'Terapkan Video',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#E20613',
                        cancelButtonColor: '#6B7280',
                        inputValidator: (value) => {
                            if (!value) return 'URL video wajib diisi!';
                            if (!value.includes('youtube.com') && !value.includes('youtu.be')) {
                                return 'Harap masukkan link YouTube yang valid.';
                            }
                        }
                    }).then((ytRes) => {
                        if (ytRes.isConfirmed && ytRes.value) {
                            qbEmbedYoutubeVideo(attachDiv, ytRes.value);
                        }
                    });
                } else if (res.isDenied) {
                    Swal.close();
                    setTimeout(() => {
                        if (fileInput) {
                            fileInput.value = '';
                            fileInput.click();
                        }
                    }, 20);
                }
            });
        } else {
            const yt = prompt('Masukkan URL video YouTube:');
            if (yt) qbEmbedYoutubeVideo(attachDiv, yt);
        }
    }

    function qbEmbedYoutubeVideo(attachDiv, url) {
        let videoId = '';
        const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([\w-]{11})/);
        if (match) videoId = match[1];
        
        if (!videoId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('URL Tidak Valid', 'Format link YouTube tidak dikenali.', 'error');
            } else {
                alert('URL YouTube tidak dikenali.');
            }
            return;
        }

        attachDiv.innerHTML = `
            <div class="relative rounded-xl overflow-hidden border border-outline-variant/60 bg-black shadow-sm group">
                <iframe class="w-full h-[220px] rounded-xl" src="https://www.youtube.com/embed/${videoId}" allowfullscreen></iframe>
                <div class="absolute top-2 right-2 z-10">
                    <button type="button" onclick="qbRemoveQuestionVideo(this)" class="bg-surface/90 backdrop-blur-sm border border-outline-variant/80 rounded-lg p-1.5 material-symbols-outlined text-on-surface hover:text-error transition-colors text-[18px] shadow-sm" title="Hapus Video">close</button>
                </div>
            </div>
            <input type="file" accept="video/mp4,video/webm" class="hidden qb-video-file-input" onchange="qbPreviewQuestionVideoFile(this)">
        `;
    }

    function qbPreviewQuestionVideoFile(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const fileUrl = URL.createObjectURL(file);
        const attachDiv = input.closest('.qb-video-attach');
        if (!attachDiv) return;
        
        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
            Swal.close();
        }
        
        attachDiv.innerHTML = `
            <div class="relative rounded-xl overflow-hidden border border-outline-variant/60 bg-black/95 shadow-sm group">
                <video src="${fileUrl}" controls class="w-full max-h-[220px] object-contain rounded-xl"></video>
                <div class="absolute top-2 right-2 z-10 flex items-center gap-1.5">
                    <button type="button" onclick="qbRemoveQuestionVideo(this)" class="bg-surface/90 hover:bg-surface text-on-surface hover:text-error backdrop-blur-sm border border-outline-variant/80 rounded-lg p-1.5 material-symbols-outlined transition-colors text-[18px] shadow-sm" title="Hapus Video">close</button>
                </div>
            </div>
            <input type="file" accept="video/mp4,video/webm" class="hidden qb-video-file-input" onchange="qbPreviewQuestionVideoFile(this)">
        `;
    }

    function qbRemoveQuestionVideo(btn) {
        const attachDiv = btn.closest('.qb-video-attach');
        if (!attachDiv) return;
        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
            Swal.close();
        }
        attachDiv.innerHTML = `
            <div class="border border-dashed border-outline-variant/80 rounded-xl p-3 flex items-center justify-center gap-2 text-on-surface-variant hover:border-secondary hover:bg-secondary/5 hover:text-secondary transition-all cursor-pointer group bg-surface-container-lowest" onclick="qbAttachVideoPrompt(this)">
                <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-secondary">video_library</span>
                <span class="font-label-sm font-bold text-xs sm:text-[13px]">Lampirkan Video (Opsional)</span>
                <input type="file" accept="video/mp4,video/webm" class="hidden qb-video-file-input" onchange="qbPreviewQuestionVideoFile(this)">
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

        // Inline Tags Selectable Chips Handler
        function updateTagChipsState() {
            const chips = document.querySelectorAll('.tag-chip');
            const countBadge = document.getElementById('tags-count-badge');
            const resetBtn = document.getElementById('tags-reset-btn');
            
            let checkedCount = 0;
            chips.forEach(chip => {
                const cb = chip.querySelector('.tag-checkbox');
                const icon = chip.querySelector('.tag-chip-icon');
                const defaultIcon = chip.getAttribute('data-default-icon') || 'sell';
                
                if (cb && cb.checked) {
                    checkedCount++;
                    chip.classList.add('bg-rose-50', 'text-rose-800', 'border-primary', 'ring-2', 'ring-primary/10', 'shadow-xs');
                    chip.classList.remove('bg-white', 'text-on-surface', 'border-outline-variant/70', 'shadow-2xs');
                    if (icon) {
                        icon.textContent = 'check_circle';
                        icon.classList.add('text-primary');
                        icon.classList.remove('text-outline');
                    }
                } else if (cb) {
                    chip.classList.remove('bg-rose-50', 'text-rose-800', 'border-primary', 'ring-2', 'ring-primary/10', 'shadow-xs');
                    chip.classList.add('bg-white', 'text-on-surface', 'border-outline-variant/70', 'shadow-2xs');
                    if (icon) {
                        icon.textContent = defaultIcon;
                        icon.classList.remove('text-primary');
                        icon.classList.add('text-outline');
                    }
                }
            });

            if (countBadge) {
                countBadge.textContent = checkedCount > 0 ? (checkedCount + ' Terpilih') : '0 Terpilih';
                if (checkedCount > 0) {
                    countBadge.classList.add('bg-primary/10', 'text-primary', 'border-primary/30');
                    countBadge.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
                } else {
                    countBadge.classList.remove('bg-primary/10', 'text-primary', 'border-primary/30');
                    countBadge.classList.add('bg-surface-container-low', 'text-on-surface-variant');
                }
            }

            if (resetBtn) {
                resetBtn.classList.toggle('hidden', checkedCount === 0);
            }
        }

        window.resetAllTags = function() {
            document.querySelectorAll('.tag-checkbox').forEach(cb => {
                cb.checked = false;
            });
            updateTagChipsState();
        };

        // Bind change events to tag checkboxes
        document.querySelectorAll('.tag-checkbox').forEach(cb => {
            cb.addEventListener('change', updateTagChipsState);
        });

        // Initial state execution
        updateTagChipsState();
    });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>
</body>
</html>
