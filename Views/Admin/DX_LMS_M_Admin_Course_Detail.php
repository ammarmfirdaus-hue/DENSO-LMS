<?php
/**
 * Tampilan Detail Course Admin (Mensinkronisasi Tampilan Teacher).
 *
 * Menampilkan informasi lengkap suatu course dengan UI yang sama persis
 * seperti Teacher Hub untuk memastikan operational consistency.
 */

// Lifecycle UI Logic
$courseStatus = $course['status'] ?? 'Draft';
$badgeColor = 'bg-surface-container-high text-on-surface-variant';
$canEditRecommendation = false; // Admin biasanya memantau, bukan create draft

switch ($courseStatus) {
    case 'Draft':
    case 'Private':
        $badgeColor = 'bg-surface-container-high text-on-surface-variant';
        break;
    case 'Pending Review':
    case 'Submitted':
        $badgeColor = 'bg-info-container text-on-info-container';
        break;
    case 'Approved':
        $badgeColor = 'bg-success-container text-on-success-container';
        break;
    case 'In Progress':
    case 'Running':
        $badgeColor = 'bg-primary-container text-on-primary-container';
        break;
    case 'Completed':
    case 'Archived':
        $badgeColor = 'bg-surface-container text-outline';
        break;
}

$header_title = 'Detail Course';
$active_tab = 'course';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Detail Course - ' . e($course['title'] ?? 'Course');
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <!-- Sidebar & Header untuk Admin -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-24 pt-4 lg:pt-24 bg-[#FAFAFA]">
        
        <div class="mb-4">
            <a href="javascript:history.back()" class="text-secondary font-label-md hover:underline flex items-center gap-1 mb-2">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
            </a>
        </div>

        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-stack-2xl lg:gap-8 items-start">
            
            <!-- Left Column: Primary Content -->
            <div class="lg:col-span-2 space-y-stack-xl lg:space-y-8">
                
                <!-- Course Hero Banner -->
                <div class="bg-white rounded-3xl border border-outline-variant/50 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden relative">
                    <!-- Gradient Banner Header -->
                    <div class="h-32 bg-gradient-to-r from-primary-container/80 via-surface-variant/30 to-surface-container-highest/20 relative border-b border-outline-variant/30">
                        <div class="absolute top-5 right-5 flex gap-2">
                            <span class="bg-white/90 backdrop-blur-sm font-label-sm text-[10px] text-secondary px-3 py-1.5 rounded-full border border-white shadow-[0_2px_8px_rgba(0,0,0,0.04)] tracking-wide">
                                <?= e($course['code'] ?? '') ?>
                            </span>
                            <div class="<?= $badgeColor ?> px-3 py-1.5 rounded-full font-label-sm text-[11px] font-bold shadow-[0_2px_8px_rgba(0,0,0,0.04)] whitespace-nowrap border border-white/50 backdrop-blur-sm tracking-wide">
                                <?= e($courseStatus) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hero Content -->
                    <div class="px-6 lg:px-8 pb-8 relative">
                        <!-- Overlapping Icon -->
                        <div class="absolute left-6 lg:left-8 w-24 h-24 bg-white rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.06)] border border-outline-variant/40 flex items-center justify-center p-2 z-10 transition-transform hover:scale-105" style="top: -3rem;">
                            <div class="w-full h-full bg-primary/5 rounded-xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-[44px] text-primary" style="font-variation-settings: 'FILL' 1;">
                                    <?= e($course['icon'] ?? 'menu_book') ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Title & Description -->
                        <div class="pl-2" style="padding-top: 80px;">
                            <h2 class="font-headline-lg font-bold text-on-surface mb-3 leading-tight tracking-tight">
                                <?= e($course['title'] ?? '') ?>
                            </h2>
                            <p class="font-body-lg text-on-surface-variant max-w-2xl mb-8 leading-relaxed opacity-90">
                                <?= e($course['description'] ?? 'Tidak ada deskripsi rinci untuk course ini.') ?>
                            </p>
                            
                            <!-- Key Metrics Horizontal -->
                            <div class="flex flex-wrap items-center gap-x-8 gap-y-4 text-label-md text-on-surface-variant border-t border-outline-variant/30 pt-6">
                                <div class="flex items-center gap-3 group">
                                    <div class="w-10 h-10 rounded-full bg-surface-container-high group-hover:bg-primary/10 flex items-center justify-center text-on-surface group-hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">category</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold uppercase tracking-widest opacity-60">Kategori</span>
                                        <span class="font-bold text-on-surface"><?= e(
                                            $course['category'] ?? 'Umum',
                                        ) ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 group">
                                    <div class="w-10 h-10 rounded-full bg-surface-container-high group-hover:bg-tertiary/10 flex items-center justify-center text-on-surface group-hover:text-tertiary transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">groups</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold uppercase tracking-widest opacity-60">Pendaftaran</span>
                                        <span class="font-bold text-on-surface"><?= e(
                                            (string) ($course['participants'] ?? '0'),
                                        ) ?> Peserta Aktif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Syllabus / Sesi & Materi Panel -->
                <section class="bg-white rounded-3xl p-6 lg:p-8 border border-outline-variant/50 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-headline-sm font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[24px]">view_timeline</span> 
                            Silabus & Materi
                        </h3>
                    </div>
                    
                    <div class="space-y-4">
                        <?php if (empty($syllabus)): ?>
                            <div class="bg-surface-container-lowest p-10 rounded-2xl border-2 border-dashed border-outline-variant/60 flex flex-col items-center justify-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px] mb-3 opacity-40">data_alert</span>
                                <p class="font-body-md text-center">Belum ada silabus atau materi yang ditambahkan.</p>
                            </div>
                        <?php else: ?>
                            <div class="flex flex-col gap-3">
                                <?php foreach ($syllabus as $index => $sess): ?>
                                    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 hover:border-primary/30 hover:shadow-[0_4px_16px_rgba(0,0,0,0.03)] transition-all group">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <h4 class="font-title-md font-semibold text-on-surface"><?= e(
                                                $sess['title'],
                                            ) ?></h4>
                                            
                                            <!-- Pill Metrics for Session -->
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface text-on-surface-variant font-label-sm border border-outline-variant/40 tooltip">
                                                    <span class="material-symbols-outlined text-[16px] text-primary">article</span>
                                                    <?= e((string) ($sess['materials'] ?? 0)) ?> Materi
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface text-on-surface-variant font-label-sm border border-outline-variant/40">
                                                    <span class="material-symbols-outlined text-[16px] text-orange-500">quiz</span>
                                                    <?= e((string) ($sess['assesments'] ?? 0)) ?> Assessment
                                                </span>
                                                <button class="w-8 h-8 rounded-full hover:bg-surface-variant flex items-center justify-center text-on-surface-variant transition-colors ml-1">
                                                    <span class="material-symbols-outlined text-[20px]">expand_more</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Peserta & Target Audience -->
                <section class="bg-white rounded-3xl p-6 lg:p-8 border border-outline-variant/50 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-headline-sm font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-tertiary text-[24px]">group</span> 
                            Monitoring Peserta
                        </h3>
                    </div>

                    <?php if ($courseStatus === 'Pending Review' || $courseStatus === 'Submitted'): ?>
                        <div class="bg-info/10 border-l-4 border-info p-5 rounded-r-xl flex items-start gap-4 mb-6">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0 shadow-sm text-info">
                                <span class="material-symbols-outlined">info</span>
                            </div>
                            <div>
                                <h4 class="font-title-sm font-bold text-info-700 mb-1">Course Dalam Tahap Review</h4>
                                <p class="font-body-md text-info-900/80">Rekomendasi target peserta telah disubmit oleh instuktur dan menunggu validasi.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($participants)): ?>
                        <!-- Modern Participant List -->
                        <div class="flex flex-col gap-3">
                            <?php foreach ($participants as $p): ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl border border-outline-variant/40 hover:border-primary/30 bg-surface-container-lowest transition-colors gap-4">
                                <div class="flex items-center gap-4">
                                    <?php
                                    $bgColors = [
                                        'bg-primary/20 text-primary',
                                        'bg-tertiary/20 text-tertiary',
                                        'bg-secondary/20 text-secondary',
                                        'bg-error/20 text-error',
                                    ];
                                    $randColor = $bgColors[crc32($p['nama']) % count($bgColors)];
                                    ?>
                                    <div class="w-12 h-12 rounded-full <?= $randColor ?> flex items-center justify-center font-headline-sm font-bold shrink-0">
                                        <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                                    </div>
                                    <div class="flex flex-col">
                                        <h4 class="font-title-sm font-bold text-on-surface"><?= e($p['nama']) ?></h4>
                                        <p class="font-body-sm text-on-surface-variant flex items-center gap-1">
                                            <?php if (
                                                $courseStatus === 'Pending Review' ||
                                                $courseStatus === 'Submitted' ||
                                                $courseStatus === 'Private' ||
                                                $courseStatus === 'Draft'
                                            ): ?>
                                                <span class="italic opacity-80">Kandidat Rekomendasi</span>
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-[14px]">military_tech</span> Skor Akhir: <span class="font-bold text-on-surface"><?= e(
                                                    (string) $p['score'],
                                                ) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:items-end gap-1.5" style="min-width: 150px;">
                                    <?php if (
                                        $courseStatus === 'Pending Review' ||
                                        $courseStatus === 'Submitted' ||
                                        $courseStatus === 'Private' ||
                                        $courseStatus === 'Draft'
                                    ): ?>
                                        <span class="px-3 py-1 bg-surface-container-high rounded-full font-label-sm text-[11px] text-on-surface-variant font-medium">Pending Approval</span>
                                    <?php else: ?>
                                        <?php
                                        $pg = (int) $p['progress'];
                                        $isFull = $pg === 100;
                                        ?>
                                        <div class="flex items-center gap-2 md:justify-end">
                                            <span class="font-label-sm font-bold <?= $isFull
                                                ? 'text-emerald-600'
                                                : 'text-primary' ?>"><?= $pg ?>% Selesai</span>
                                        </div>
                                        <div class="w-full h-2 bg-surface-variant/50 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-1000 <?= $isFull
                                                ? 'bg-emerald-500'
                                                : 'bg-primary' ?>" style="width: <?= $pg ?>%;"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($participants) > 2): ?>
                                <button class="w-full py-3 mt-2 rounded-xl border border-dashed border-outline-variant text-primary font-label-md font-bold hover:bg-primary/5 transition-colors">
                                    Lihat Semua <?= e((string) count($participants)) ?> Peserta
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <div class="h-8"></div> <!-- spacer -->
            </div>

            <!-- Right Column: Interactive Widgets & Context -->
            <div class="space-y-6">
                
                <!-- Action Widgets -->
                <div class="flex flex-col gap-4">
                    <!-- Buka Forum Widget -->
                    <button class="group block text-left bg-white border border-outline-variant/60 rounded-2xl p-6 shadow-sm hover:shadow-[0_8px_24px_rgba(99,102,241,0.08)] hover:border-indigo-200 transition-all duration-300 relative overflow-hidden w-full">
                        <!-- Widget Background Art -->
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-50 rounded-full mix-blend-multiply opacity-50 group-hover:scale-110 transition-transform"></div>
                        
                        <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center mb-5 shrink-0 relative z-10 group-hover:-translate-y-1 transition-transform">
                            <span class="material-symbols-outlined text-[24px]">forum</span>
                        </div>
                        <h4 class="font-title-md font-bold text-on-surface mb-1.5 relative z-10">Buka Ruang Forum</h4>
                        <p class="font-body-sm text-on-surface-variant relative z-10 leading-snug">Berinteraksi (read-only), pantau Q&A dan isu terkait course (2 diskusi baru).</p>
                    </button>
                    
                    <!-- Kelola Nilai Widget -->
                    <?php if (
                        $courseStatus === 'Running' ||
                        $courseStatus === 'In Progress' ||
                        $courseStatus === 'Completed'
                    ): ?>
                    <a href="?route=/admin/score" class="group block bg-white border border-outline-variant/60 rounded-2xl p-6 shadow-sm hover:shadow-[0_8px_24px_rgba(16,185,129,0.08)] hover:border-emerald-200 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-50 rounded-full mix-blend-multiply opacity-50 group-hover:scale-110 transition-transform"></div>
                        
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-5 shrink-0 relative z-10 group-hover:-translate-y-1 transition-transform">
                            <span class="material-symbols-outlined text-[24px]">grading</span>
                        </div>
                        <h4 class="font-title-md font-bold text-on-surface mb-1.5 relative z-10">Kelola Nilai</h4>
                        <p class="font-body-sm text-on-surface-variant relative z-10 leading-snug">Evaluasi administratif terhadap nilai akhir participant di course ini.</p>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Timeline Activity Widget -->
                <section class="bg-white border border-outline-variant/60 p-6 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-2 mb-6 border-b border-outline-variant/40 pb-4">
                        <span class="material-symbols-outlined text-secondary">history</span>
                        <h3 class="font-title-md font-bold text-on-surface">Aktivitas Course</h3>
                    </div>
                    
                    <?php if (empty($activities)): ?>
                        <div class="py-4 text-center">
                            <p class="font-body-sm text-on-surface-variant italic">Belum ada historical log.</p>
                        </div>
                    <?php else: ?>
                        <div class="relative before:absolute before:inset-0 before:ml-4 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-surface-variant before:to-transparent">
                            <div class="space-y-6">
                                <?php foreach ($activities as $idx => $act): ?>
                                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 border-white <?= e(
                                            $act['color_class'] ?? 'bg-surface-container-high',
                                        ) ?> shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-sm relative z-10">
                                            <span class="material-symbols-outlined text-[14px] text-white" style="font-variation-settings: 'FILL' 1;"><?= e(
                                                $act['icon'] ?? 'history',
                                            ) ?></span>
                                        </div>
                                        <div class="w-[calc(100%-3rem)] md:w-[calc(50%-2rem)]">
                                            <div class="flex flex-col">
                                                <h5 class="font-label-md font-semibold text-on-surface leading-snug"><?= e(
                                                    $act['action'],
                                                ) ?></h5>
                                                <time class="font-label-sm text-[10px] text-on-surface-variant mt-1"><?= e(
                                                    $act['time'],
                                                ) ?></time>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
                
            </div>
        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>
</body>
</html>
