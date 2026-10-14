<?php
/**
 * Tampilan Detail Course Pengajar (Teacher Course Detail Pusat Kendali).
 *
 * Menampilkan informasi lengkap suatu course dan bertindak sebagai
 * pusat kendali untuk mengelola sesi, peserta rekomen, dan forum.
 */

// Lifecycle UI Logic
$courseStatus = $course['status'] ?? 'Draft';
$badgeColor = 'bg-surface-container-high text-on-surface-variant';
$badgeBorder = 'border-outline-variant/40';
$badgeDot = 'bg-on-surface-variant';
$canEditRecommendation = false;

switch ($courseStatus) {
    case 'Draft':
    case 'Private':
        $badgeColor = 'bg-surface-container-high text-on-surface-variant';
        $badgeBorder = 'border-outline-variant/50';
        $badgeDot = 'bg-on-surface-variant';
        $canEditRecommendation = $courseStatus === 'Draft';
        break;
    case 'Pending Review':
    case 'Submitted':
        $badgeColor = 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
        $badgeBorder = 'border-amber-300 dark:border-amber-700';
        $badgeDot = 'bg-amber-500 animate-pulse';
        break;
    case 'Approved':
        $badgeColor = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
        $badgeBorder = 'border-emerald-300 dark:border-emerald-700';
        $badgeDot = 'bg-emerald-500';
        break;
    case 'In Progress':
    case 'Running':
        $badgeColor = 'bg-primary-container text-on-primary-container';
        $badgeBorder = 'border-primary/30';
        $badgeDot = 'bg-primary animate-pulse';
        break;
    case 'Completed':
    case 'Archived':
        $badgeColor = 'bg-surface-container text-outline';
        $badgeBorder = 'border-outline-variant/40';
        $badgeDot = 'bg-outline';
        break;
}

// Executive Analytics Calculation
$avgProgress = 0;
$avgScore = 0;
if (!empty($participants)) {
    $totalProg = 0;
    $totalScore = 0;
    foreach ($participants as $p) {
        $totalProg += (int) ($p['progress'] ?? 0);
        $totalScore += (int) ($p['score'] ?? 0);
    }
    $avgProgress = round($totalProg / count($participants));
    $avgScore = round($totalScore / count($participants), 1);
}
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
    
    <?php $active_tab = 'course'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Detail Course';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content Canvas (Full-Width Fluid Layout Tanpa Ruang Kosong Berlebih) -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-4 sm:px-6 lg:px-8 pb-24 pt-[72px] bg-[#FAFAFA]">
        
        <!-- Full-Width Fluid Grid (2 Kolom Kiri, 1 Kolom Kanan) -->
        <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
            
            <!-- Left Column: Primary Course Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Integrated Enterprise Hero Profile Card -->
                <div class="bg-white rounded-3xl border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden mb-8">
                    <!-- Gradient Accent Top Strip -->
                    <div class="h-2.5 w-full bg-gradient-to-r from-primary via-tertiary to-secondary"></div>
                    
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 mb-6">
                            <div class="flex items-start gap-5">
                                <div class="w-16 h-16 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0 shadow-sm">
                                    <span class="material-symbols-outlined text-[36px]" style="font-variation-settings: 'FILL' 1;">
                                        <?= e($course['icon'] ?? 'menu_book') ?>
                                    </span>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="px-2.5 py-1 rounded-md bg-surface-container text-on-surface-variant font-mono text-[11px] font-bold border border-outline-variant/40">
                                            <?= e($course['code'] ?? 'MGT-501') ?>
                                        </span>
                                        <span class="px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-[11px] font-bold">
                                            <?= e($course['category'] ?? 'Leadership') ?>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full <?= $badgeColor ?> border <?= $badgeBorder ?> font-label-sm text-[11px] font-bold">
                                            <span class="w-2 h-2 rounded-full <?= $badgeDot ?>"></span>
                                            <?= e($courseStatus) ?>
                                        </span>
                                    </div>
                                    <h2 class="font-headline-md sm:font-headline-lg font-bold text-on-surface tracking-tight leading-tight">
                                        <?= e($course['title'] ?? '') ?>
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <!-- Course Description -->
                        <p class="font-body-lg text-on-surface-variant leading-relaxed mb-8 max-w-3xl">
                            <?= e($course['description'] ?? 'Tidak ada deskripsi rinci untuk course ini.') ?>
                        </p>
                        
                        <!-- Horizontal Meta Badges Strip -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-outline-variant/30">
                            <div class="bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/40">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70 block mb-1">Target Peserta</span>
                                <span class="font-title-sm font-bold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-tertiary">engineering</span>
                                    Leader & SPV
                                </span>
                            </div>
                            <div class="bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/40">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70 block mb-1">Total Modul Sesi</span>
                                <span class="font-title-sm font-bold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-primary">view_timeline</span>
                                    <?= e((string) count($syllabus)) ?> Sesi
                                </span>
                            </div>
                            <div class="bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/40">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70 block mb-1">Total Materi</span>
                                <?php
                                $totalMat = 0;
                                foreach ($syllabus as $s) {
                                    $totalMat += (int) ($s['materials'] ?? 0);
                                }
                                ?>
                                <span class="font-title-sm font-bold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-indigo-500">article</span>
                                    <?= $totalMat ?> Materi
                                </span>
                            </div>
                            <div class="bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/40">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70 block mb-1">Peserta Aktif</span>
                                <span class="font-title-sm font-bold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-emerald-600">group</span>
                                    <?= e((string) ($course['participants'] ?? '0')) ?> Peserta
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs (Silabus, Forum Diskusi, Monitoring Peserta) -->
                <?php $tab = $tab ?? 'silabus'; ?>
                <div class="bg-white rounded-2xl border border-outline-variant/50 p-1.5 flex gap-2 mb-8 shadow-sm">
                    <a href="?route=/teacher/course/detail&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>&tab=silabus" class="flex-1 py-3 px-4 rounded-xl text-center font-label-md font-bold text-xs sm:text-sm transition-all flex items-center justify-center gap-2 <?= $tab === 'silabus' ? 'bg-primary text-white shadow-sm' : 'text-on-surface-variant hover:bg-surface-container' ?>">
                        <span class="material-symbols-outlined text-[18px]">auto_stories</span>
                        <span>Silabus & Materi</span>
                    </a>
                    <a href="?route=/teacher/course/detail&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>&tab=forum" class="flex-1 py-3 px-4 rounded-xl text-center font-label-md font-bold text-xs sm:text-sm transition-all flex items-center justify-center gap-2 <?= $tab === 'forum' ? 'bg-primary text-white shadow-sm' : 'text-on-surface-variant hover:bg-surface-container' ?>">
                        <span class="material-symbols-outlined text-[18px]">forum</span>
                        <span>Forum Diskusi</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'forum' ? 'bg-white text-primary' : 'bg-primary/10 text-primary' ?>">
                            <?= count($forumTopics ?? []) ?>
                        </span>
                    </a>
                    <a href="?route=/teacher/course/detail&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>&tab=peserta" class="flex-1 py-3 px-4 rounded-xl text-center font-label-md font-bold text-xs sm:text-sm transition-all flex items-center justify-center gap-2 <?= $tab === 'peserta' ? 'bg-primary text-white shadow-sm' : 'text-on-surface-variant hover:bg-surface-container' ?>">
                        <span class="material-symbols-outlined text-[18px]">fact_check</span>
                        <span>Capaian & Peserta</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $tab === 'peserta' ? 'bg-white text-primary' : 'bg-primary/10 text-primary' ?>">
                            <?= count($participants ?? []) ?>
                        </span>
                    </a>
                </div>

                <?php if ($tab === 'forum'): ?>
                <!-- Forum Diskusi Panel (Aligned with Student Forum View) -->
                <section class="bg-white rounded-3xl p-6 lg:p-8 border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] mb-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-outline-variant/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[22px]">forum</span>
                            </div>
                            <div>
                                <h3 class="font-headline-sm font-bold text-on-surface">Forum Diskusi Course</h3>
                                <p class="font-body-sm text-on-surface-variant">Tanya jawab dan diskusi materi pelatihan bersama peserta</p>
                            </div>
                        </div>
                        <a href="?route=/teacher/forum&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-xl font-label-md font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-[16px]">open_in_new</span> Buka Ruang Forum Penuh
                        </a>
                    </div>

                    <!-- Forum Threads List -->
                    <div class="space-y-3">
                        <?php if (empty($forumTopics)): ?>
                            <div class="py-12 text-center text-on-surface-variant bg-surface-container-low/50 rounded-2xl border border-dashed border-outline-variant/60">
                                <span class="material-symbols-outlined text-[40px] mb-2 opacity-40">chat</span>
                                <p class="font-body-md font-bold">Belum ada topik diskusi pada course ini.</p>
                                <p class="font-body-sm text-xs opacity-75 mt-0.5">Peserta dapat mengajukan pertanyaan melalui tab forum course.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($forumTopics as $ft): ?>
                                <?php
                                $isResolved = ($ft['status'] ?? 'open') === 'resolved';
                                $authorInitial = strtoupper(substr($ft['author_name'] ?? 'U', 0, 1));
                                ?>
                                <div class="p-4 sm:p-5 rounded-2xl border border-outline-variant/40 bg-surface hover:border-primary/40 hover:shadow-sm transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                        <div class="w-10 h-10 rounded-full bg-surface-container-high text-on-surface border border-outline-variant/40 font-bold text-sm flex items-center justify-center shrink-0">
                                            <?= $authorInitial ?>
                                        </div>
                                        <div class="space-y-1 min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-on-surface text-xs sm:text-sm"><?= htmlspecialchars($ft['author_name']) ?></span>
                                                <span class="text-[11px] text-on-surface-variant">· <?= htmlspecialchars($ft['time_formatted'] ?? 'Baru saja') ?></span>
                                                <?php if ($isResolved): ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200 flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[12px]">check_circle</span> Solusi Disetujui
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="?route=/teacher/forum/thread&id=<?= (int)$ft['topic_id'] ?>" class="block font-title-sm font-bold text-on-surface hover:text-primary transition-colors text-sm line-clamp-1">
                                                <?= htmlspecialchars($ft['title']) ?>
                                            </a>
                                            <p class="text-xs text-on-surface-variant line-clamp-1">
                                                <?= htmlspecialchars(mb_substr($ft['content'], 0, 110)) ?>...
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-outline-variant/30">
                                        <span class="text-xs font-semibold text-secondary flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">chat</span>
                                            <?= (int)$ft['total_replies'] ?> Balasan
                                        </span>
                                        <a href="?route=/teacher/forum/thread&id=<?= (int)$ft['topic_id'] ?>" class="px-3.5 py-1.5 rounded-xl bg-primary/10 hover:bg-primary hover:text-white text-primary font-label-sm font-bold text-xs transition-colors flex items-center gap-1">
                                            <span>Buka</span>
                                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php if ($tab === 'silabus'): ?>
                <!-- Structured Learning Tree (Silabus & Materi Panel) -->
                <section class="bg-white rounded-3xl p-6 lg:p-8 border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] mb-8">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-outline-variant/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[22px]">auto_stories</span>
                            </div>
                            <div>
                                <h3 class="font-headline-sm font-bold text-on-surface">Silabus & Materi Kurikulum</h3>
                                <p class="font-body-sm text-on-surface-variant">Struktur sesi pemotongan kompetensi dan dokumen pelatihan</p>
                            </div>
                        </div>
                        <?php if ($canEditRecommendation): ?>
                        <button class="bg-primary/10 text-primary px-4 py-2 rounded-xl font-label-md font-bold hover:bg-primary/20 transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[20px]">add</span> Tambah Sesi
                        </button>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-surface-container rounded-full font-label-sm font-semibold text-on-surface-variant">
                            <?= e((string) count($syllabus)) ?> Modul
                        </span>
                        <?php endif; ?>
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
                                    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 hover:border-primary/40 hover:shadow-[0_4px_16px_rgba(0,0,0,0.03)] transition-all group">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-primary text-on-primary font-bold font-mono text-sm flex items-center justify-center shrink-0 shadow-sm">
                                                    <?= sprintf('%02d', $index + 1) ?>
                                                </div>
                                                <div>
                                                    <h4 class="font-title-md font-bold text-on-surface group-hover:text-primary transition-colors">
                                                        <?= e($sess['title']) ?>
                                                    </h4>
                                                    <span class="font-label-sm text-on-surface-variant">Modul Komponen Terverifikasi</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Pill Metrics for Session -->
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 shrink-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-on-surface-variant font-label-sm border border-outline-variant/60 shadow-sm">
                                                        <span class="material-symbols-outlined text-[16px] text-primary">article</span>
                                                        <?= e((string) ($sess['materials'] ?? 0)) ?> Materi
                                                    </span>
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-on-surface-variant font-label-sm border border-outline-variant/60 shadow-sm">
                                                        <span class="material-symbols-outlined text-[16px] text-orange-500">quiz</span>
                                                        <?= e((string) ($sess['assesments'] ?? 0)) ?> Assessment
                                                    </span>
                                                </div>
                                                
                                                <?php if ($canEditRecommendation): ?>
                                                <button class="text-primary font-label-sm font-bold hover:underline flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity ml-2">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span> Edit Konten
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php if ($tab === 'peserta'): ?>
                <!-- Executive Analytics & Monitoring Peserta -->
                <section class="bg-white rounded-3xl p-6 lg:p-8 border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-outline-variant/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-tertiary/10 flex items-center justify-center text-tertiary">
                                <span class="material-symbols-outlined text-[22px]">group</span>
                            </div>
                            <div>
                                <h3 class="font-headline-sm font-bold text-on-surface">Monitoring Peserta & Executive Progress</h3>
                                <p class="font-body-sm text-on-surface-variant">Analisis partisipasi aktif karyawan terhadap modul course</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <?php if ($canEditRecommendation): ?>
                            <a href="?route=/teacher/course/create&course_id=<?= e(
                                (string) $course['id'],
                            ) ?>#target" class="text-primary font-label-md font-bold hover:bg-primary/10 transition-colors flex items-center gap-1.5 px-4 py-2 rounded-xl border border-primary/20 bg-surface">
                                <span class="material-symbols-outlined text-[18px]">edit</span> Revisi Target
                            </a>
                            <?php else: ?>
                            <!-- Executive Summary Pills -->
                            <span class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl font-label-sm text-on-surface">
                                Rata-rata Selesai: <strong class="text-primary"><?= $avgProgress ?>%</strong>
                            </span>
                            <span class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl font-label-sm text-on-surface">
                                Rata-rata Skor: <strong class="text-emerald-600"><?= $avgScore ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($courseStatus === 'Pending Review' || $courseStatus === 'Submitted'): ?>
                        <div class="bg-info/10 border-l-4 border-info p-5 rounded-r-xl flex items-start gap-4 mb-6">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0 shadow-sm text-info">
                                <span class="material-symbols-outlined">info</span>
                            </div>
                            <div>
                                <h4 class="font-title-sm font-bold text-info-700 mb-1">Menunggu Review Admin</h4>
                                <p class="font-body-md text-info-900/80">Rekomendasi target peserta telah disubmit. Anda tidak dapat menyunting data ini sampai status berubah.</p>
                            </div>
                        </div>
                    <?php elseif ($courseStatus === 'Draft' && empty($participants)): ?>
                        <div class="bg-warning/10 border-l-4 border-warning p-6 rounded-r-xl flex flex-col sm:flex-row items-center sm:items-start gap-4 mb-4 text-center sm:text-left">
                            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center shrink-0 shadow-sm text-warning relative top-1">
                                <span class="material-symbols-outlined text-[24px]">group_add</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-title-md font-bold text-warning-800 mb-1">Target Peserta Belum Ditentukan</h4>
                                <p class="font-body-md text-warning-900/80 mb-4 max-w-md">Usulkan siapa saja dari department atau level yang wajib mengikuti course ini via sistem recommendation.</p>
                                <a href="?route=/teacher/course/create&course_id=<?= e(
                                    (string) $course['id'],
                                ) ?>#target" class="inline-block px-5 py-2.5 bg-primary text-on-primary rounded-xl font-label-md font-bold shadow-sm hover:bg-primary-600 transition-colors">Setup Target Audience</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($participants)): ?>
                        <!-- Modern Participant List -->
                        <div class="flex flex-col gap-3">
                            <?php foreach ($participants as $p): ?>
                            <?php
                            $pName = (string) ($p['nama'] ?? $p['name'] ?? $p['fullname'] ?? 'Peserta');
                            $pScore = $p['score'] ?? 0;
                            $pProgress = (int) ($p['progress'] ?? 0);
                            $bgColors = [
                                'bg-primary/10 text-primary border border-primary/20',
                                'bg-tertiary/10 text-tertiary border border-tertiary/20',
                                'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                'bg-indigo-100 text-indigo-700 border border-indigo-200',
                            ];
                            $randColor = $bgColors[abs(crc32($pName)) % count($bgColors)];
                            $initial = strtoupper(mb_substr($pName, 0, 1));
                            ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl border border-outline-variant/40 hover:border-primary/40 bg-surface-container-lowest transition-all gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-full <?= $randColor ?> flex items-center justify-center font-title-sm font-bold shrink-0 shadow-sm">
                                        <?= $initial ?>
                                    </div>
                                    <div class="flex flex-col">
                                        <h4 class="font-title-sm font-bold text-on-surface"><?= e($pName) ?></h4>
                                        <p class="font-body-sm text-on-surface-variant flex items-center gap-1 mt-0.5">
                                            <?php if (
                                                $courseStatus === 'Pending Review' ||
                                                $courseStatus === 'Submitted' ||
                                                $courseStatus === 'Private' ||
                                                $courseStatus === 'Draft'
                                            ): ?>
                                                <span class="italic opacity-80">Kandidat Rekomendasi<?= !empty($p['department']) ? ' · ' . e($p['department']) : '' ?></span>
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-[14px] text-amber-500">military_tech</span> Skor Akhir: <span class="font-bold text-on-surface"><?= e((string) $pScore) ?></span>
                                                <?php if (!empty($p['department'])): ?>
                                                    <span class="text-outline-variant mx-1">•</span>
                                                    <span><?= e($p['department']) ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:items-end gap-1.5" style="min-width: 180px;">
                                    <?php if (
                                        $courseStatus === 'Pending Review' ||
                                        $courseStatus === 'Submitted' ||
                                        $courseStatus === 'Private' ||
                                        $courseStatus === 'Draft'
                                    ): ?>
                                        <span class="px-3 py-1 bg-surface-container-high rounded-full font-label-sm text-[11px] text-on-surface-variant font-medium">Pending Approval</span>
                                    <?php else: ?>
                                        <?php
                                        $isFull = $pProgress === 100;
                                        ?>
                                        <div class="flex items-center gap-2 md:justify-end">
                                            <span class="font-label-sm font-bold <?= $isFull ? 'text-emerald-600' : 'text-primary' ?>"><?= $pProgress ?>% Selesai</span>
                                        </div>
                                        <div class="w-full h-2 bg-surface-variant/60 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-1000 <?= $isFull ? 'bg-emerald-500' : 'bg-primary' ?>" style="width: <?= $pProgress ?>%;"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="pt-2 flex flex-col sm:flex-row gap-3">
                                <a href="?route=/teacher/grade&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>" class="flex-1 py-3 px-4 rounded-xl border border-primary/30 bg-primary/5 hover:bg-primary/10 text-primary font-label-md font-bold transition-all shadow-sm flex items-center justify-center gap-2 text-center text-xs sm:text-sm">
                                    <span class="material-symbols-outlined text-[18px]">fact_check</span>
                                    <span>Kelola Nilai & Capaian Lengkap (<?= e((string) count($participants)) ?> Peserta) →</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>
            </div>

            <!-- Right Column: Teacher Control Center & Interactive Widgets -->
            <div class="space-y-6">
                
                <!-- Card 1: Pusat Kendali Instruktur -->
                <div class="bg-white rounded-3xl border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-6">
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-outline-variant/30">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
                        </div>
                        <div>
                            <h4 class="font-title-md font-bold text-on-surface">Pusat Kendali Pengajar</h4>
                            <p class="font-label-sm text-on-surface-variant">Operasional course & kelas</p>
                        </div>
                    </div>

                    <!-- Lifecycle State Explainer -->
                    <div class="bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/40 mb-5">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant/70 block mb-1.5">Status Lifecycle Saat Ini</span>
                        <div class="flex items-center">
                            <span class="font-title-sm font-bold text-on-surface flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full <?= $badgeDot ?>"></span>
                                <?= e($courseStatus) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Teacher Action Buttons List -->
                    <div class="space-y-2.5">
                        <?php if ($canEditRecommendation): ?>
                            <a href="?route=/teacher/course/create&course_id=<?= e(
                                (string) $course['id'],
                            ) ?>" class="w-full py-3 px-4 rounded-xl bg-primary hover:bg-primary-600 text-white font-label-md font-bold flex items-center justify-center gap-2 shadow-sm transition-all text-center">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                Edit Detail Course
                            </a>
                        <?php elseif ($courseStatus === 'Pending Review' || $courseStatus === 'Submitted'): ?>
                            <button type="button" disabled class="w-full py-2.5 px-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 font-label-sm font-bold flex items-center justify-center gap-2 cursor-not-allowed text-center opacity-90">
                                <span class="material-symbols-outlined text-[18px] text-amber-600">lock</span>
                                Terkunci (Sedang Direview Admin)
                            </button>
                            <button type="button" onclick="confirmRevertDraft('<?= e((string)$course['id']) ?>')" class="w-full py-2.5 px-4 rounded-xl border border-outline-variant bg-surface hover:bg-surface-container text-on-surface font-label-sm font-bold flex items-center justify-center gap-2 transition-all text-center shadow-sm">
                                <span class="material-symbols-outlined text-[18px] text-primary">undo</span>
                                Tarik Pengajuan ke Draft
                            </button>
                        <?php elseif ($courseStatus === 'Rejected'): ?>
                            <a href="?route=/teacher/course/create&course_id=<?= e(
                                (string) $course['id'],
                            ) ?>" class="w-full py-3 px-4 rounded-xl bg-error hover:bg-error-600 text-white font-label-md font-bold flex items-center justify-center gap-2 shadow-sm transition-all text-center">
                                <span class="material-symbols-outlined text-[18px]">build</span>
                                Perbaiki & Edit Course
                            </a>
                        <?php elseif ($courseStatus === 'Running' || $courseStatus === 'Completed'): ?>
                            <a href="?route=/teacher/grade&course_id=<?= e(
                                (string) $course['id'],
                            ) ?>" class="w-full py-3 px-4 rounded-xl border border-emerald-500/40 text-emerald-600 hover:bg-emerald-50 font-label-md font-bold flex items-center justify-center gap-2 transition-all text-center">
                                <span class="material-symbols-outlined text-[18px]">fact_check</span>
                                Kelola Penilaian & Capaian
                            </a>
                        <?php endif; ?>
                            <button onclick="handleTeacherAction('preview')" class="w-full py-2.5 px-4 rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-variant/40 font-label-md font-semibold flex items-center justify-center gap-2 transition-all mt-2">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                Preview Mode Peserta
                            </button>
                            <button type="button" onclick="confirmDeleteCourse('<?= e((string)$course['id']) ?>')" class="w-full py-2.5 px-4 rounded-xl border border-error/30 text-error hover:bg-error/10 font-label-sm font-bold flex items-center justify-center gap-2 transition-all text-center mt-2">
                                <span class="material-symbols-outlined text-[18px]">delete_forever</span>
                                Hapus Course (Permanen)
                            </button>
                    </div>
                </div>

                <!-- Card 2: Buka Ruang Forum Widget -->
                <a href="?route=/teacher/course/detail&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>&tab=forum" class="group block text-left bg-white border border-outline-variant/60 rounded-3xl p-6 shadow-sm hover:shadow-[0_8px_24px_rgba(99,102,241,0.08)] hover:border-indigo-200 transition-all duration-300 relative overflow-hidden w-full">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-50 rounded-full mix-blend-multiply opacity-50 group-hover:scale-110 transition-transform"></div>
                    
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-5 shrink-0 relative z-10 group-hover:-translate-y-1 transition-transform">
                        <span class="material-symbols-outlined text-[24px]">forum</span>
                    </div>
                    <h4 class="font-title-md font-bold text-on-surface mb-1.5 relative z-10">Buka Ruang Forum Course</h4>
                    <p class="font-body-sm text-on-surface-variant relative z-10 leading-snug">Berinteraksi, pantau Q&A dan isu terkait course ini (<?= count($forumTopics ?? []) ?> diskusi aktif).</p>
                </a>

                <!-- Card 3: Timeline Activity Widget -->
                <section class="bg-white border border-outline-variant/60 p-6 rounded-3xl shadow-sm">
                    <div class="flex items-center gap-2 mb-6 border-b border-outline-variant/40 pb-4">
                        <span class="material-symbols-outlined text-secondary">history</span>
                        <h3 class="font-title-md font-bold text-on-surface">Aktivitas & Audit Trail</h3>
                    </div>
                    
                    <?php if (empty($activities)): ?>
                        <div class="py-6 text-center">
                            <span class="material-symbols-outlined text-[32px] text-outline-variant block mb-2">history_toggle_off</span>
                            <p class="font-body-sm text-on-surface-variant italic">Belum ada aktivitas atau audit log.</p>
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

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>
    <script>
        function handleTeacherAction(actionType) {
            if (typeof DLS === 'undefined') {
                alert("Fitur sedang memuat, silakan coba beberapa saat lagi.");
                return;
            }

            if (actionType === 'preview') {
                DLS.toast('Membuka Preview Mode...', 'info');
            } else if (actionType === 'forum') {
                window.location.href = '?route=/teacher/course/detail&course_id=<?= urlencode((string)($course['id'] ?? 1)) ?>&tab=forum';
            } else if (actionType === 'view_all_participants') {
                DLS.toast('Menampilkan seluruh daftar peserta terdaftar', 'info');
            }
        }

        function confirmRevertDraft(courseId) {
            const doRevert = () => {
                window.location.href = '?route=/teacher/course/revert-draft&course_id=' + encodeURIComponent(courseId);
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Tarik Pengajuan ke Draft?',
                    text: 'Status course akan dikembalikan ke Draft sehingga Anda dapat menyunting kembali kurikulum, materi, dan peserta sebelum dikirim ulang ke Admin.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#E20613',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Kembalikan ke Draft',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doRevert();
                    }
                });
            } else if (confirm('Kembalikan course ke status Draft agar dapat diedit kembali?')) {
                doRevert();
            }
        }

        function confirmDeleteCourse(courseId) {
            const doDelete = () => {
                fetch('?route=/teacher/course/delete&id=' + encodeURIComponent(courseId))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Berhasil Dihapus!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#E20613'
                                }).then(() => {
                                    window.location.href = '?route=/teacher/courses';
                                });
                            } else {
                                alert(data.message);
                                window.location.href = '?route=/teacher/courses';
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Gagal Menghapus', data.error || data.message, 'error');
                            } else {
                                alert(data.error || data.message);
                            }
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan saat menghapus course.');
                    });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Course Permanen?',
                    text: 'PERINGATAN: Course ini dan seluruh materi, kuis, sesi, serta relasinya akan DIHAPUS TOTAL dari database SSMS. Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            } else if (confirm('PERINGATAN: Course ini akan dihapus total secara permanen dari database. Lanjutkan?')) {
                doDelete();
            }
        }
    </script>
</body>
</html>
