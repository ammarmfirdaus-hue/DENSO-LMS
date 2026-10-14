<?php
/**
 * Tampilan Detail Course Admin (Enterprise Control Center).
 *
 * Menampilkan informasi lengkap suatu course dengan UI penuh (full-width),
 * kontrol persetujuan (approval), dan pemantauan eksekutif.
 */

// Lifecycle UI Logic
$courseStatus = $course['status'] ?? 'Draft';
$badgeColor = 'bg-surface-container-high text-on-surface-variant';
$badgeBorder = 'border-outline-variant/40';
$badgeDot = 'bg-on-surface-variant';

switch ($courseStatus) {
    case 'Draft':
    case 'Private':
        $badgeColor = 'bg-surface-container-high text-on-surface-variant';
        $badgeBorder = 'border-outline-variant/50';
        $badgeDot = 'bg-on-surface-variant';
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
        $totalProg += (int)($p['progress'] ?? 0);
        $totalScore += (int)($p['score'] ?? 0);
    }
    $avgProgress = round($totalProg / count($participants));
    $avgScore = round($totalScore / count($participants), 1);
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
                                foreach ($syllabus as $s) { $totalMat += (int)($s['materials'] ?? 0); }
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
                        <span class="px-3 py-1 bg-surface-container rounded-full font-label-sm font-semibold text-on-surface-variant">
                            <?= e((string) count($syllabus)) ?> Modul
                        </span>
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
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-on-surface-variant font-label-sm border border-outline-variant/60 shadow-sm">
                                                    <span class="material-symbols-outlined text-[16px] text-primary">article</span>
                                                    <?= e((string) ($sess['materials'] ?? 0)) ?> Materi
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-on-surface-variant font-label-sm border border-outline-variant/60 shadow-sm">
                                                    <span class="material-symbols-outlined text-[16px] text-orange-500">quiz</span>
                                                    <?= e((string) ($sess['assesments'] ?? 0)) ?> Assessment
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

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
                        
                        <!-- Executive Summary Pills -->
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl font-label-sm text-on-surface">
                                Rata-rata Selesai: <strong class="text-primary"><?= $avgProgress ?>%</strong>
                            </span>
                            <span class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl font-label-sm text-on-surface">
                                Rata-rata Skor: <strong class="text-emerald-600"><?= $avgScore ?></strong>
                            </span>
                        </div>
                    </div>

                    <?php if ($courseStatus === 'Pending Review' || $courseStatus === 'Submitted'): ?>
                        <div class="bg-info/10 border-l-4 border-info p-5 rounded-r-xl flex items-start gap-4 mb-6">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0 shadow-sm text-info">
                                <span class="material-symbols-outlined">info</span>
                            </div>
                            <div>
                                <h4 class="font-title-sm font-bold text-info-700 mb-1">Course Dalam Tahap Review</h4>
                                <p class="font-body-md text-info-900/80">Rekomendasi target peserta telah disubmit oleh instruktur dan menunggu validasi Admin.</p>
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
                            
                            <?php if (count($participants) > 2): ?>
                                <button onclick="handleAdminAction('view_all_participants')" class="w-full py-3.5 mt-2 rounded-2xl border border-outline-variant/60 bg-white hover:bg-surface-variant/40 text-primary font-label-md font-bold transition-colors shadow-sm">
                                    Lihat Semua <?= e((string) count($participants)) ?> Peserta Terdaftar →
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Right Column: Admin Control Center & Interactive Widgets -->
            <div class="space-y-6">
                
                <!-- Card 1: Pusat Approval & Kendali Operasional Admin -->
                <div class="bg-white rounded-3xl border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-6">
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-outline-variant/30">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
                        </div>
                        <div>
                            <h4 class="font-title-md font-bold text-on-surface">Pusat Kendali Admin</h4>
                            <p class="font-label-sm text-on-surface-variant">Kendali siklus & persetujuan</p>
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

                    <!-- Admin Action Buttons List -->
                    <div class="space-y-2.5">
                        <?php if ($courseStatus === 'Pending Review' || $courseStatus === 'Submitted'): ?>
                            <button onclick="handleAdminAction('approve')" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-label-md font-bold flex items-center justify-center gap-2 shadow-sm transition-all">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                Setujui & Publikasikan Course
                            </button>
                            <button onclick="handleAdminAction('reject')" class="w-full py-3 px-4 rounded-xl border border-error/40 text-error hover:bg-error/10 font-label-md font-bold flex items-center justify-center gap-2 transition-all">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Tolak dengan Catatan Revisi
                            </button>
                        <?php elseif ($courseStatus === 'Archived'): ?>
                            <button onclick="handleAdminAction('unarchive')" class="w-full py-3 px-4 rounded-xl border border-primary/40 text-primary hover:bg-primary/10 font-label-md font-semibold flex items-center justify-center gap-2 transition-all">
                                <span class="material-symbols-outlined text-[18px]">unarchive</span>
                                Aktifkan Kembali (Unarchive)
                            </button>
                        <?php else: ?>
                            <button onclick="handleAdminAction('archive')" class="w-full py-3 px-4 rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-variant/40 font-label-md font-semibold flex items-center justify-center gap-2 transition-all">
                                <span class="material-symbols-outlined text-[18px]">archive</span>
                                Arsipkan / Nonaktifkan Course
                            </button>
                        <?php endif; ?>

                        <button onclick="handleAdminAction('delete')" class="w-full py-2.5 px-4 rounded-xl border border-error/30 text-error hover:bg-error/10 font-label-sm font-bold flex items-center justify-center gap-2 transition-all mt-2">
                            <span class="material-symbols-outlined text-[18px]">delete_forever</span>
                            Hapus Course Permanen
                        </button>
                    </div>
                </div>

                <!-- Card 2: Buka Ruang Forum Widget -->
                <button onclick="handleAdminAction('forum')" class="group block text-left bg-white border border-outline-variant/60 rounded-3xl p-6 shadow-sm hover:shadow-[0_8px_24px_rgba(99,102,241,0.08)] hover:border-indigo-200 transition-all duration-300 relative overflow-hidden w-full">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-50 rounded-full mix-blend-multiply opacity-50 group-hover:scale-110 transition-transform"></div>
                    
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-5 shrink-0 relative z-10 group-hover:-translate-y-1 transition-transform">
                        <span class="material-symbols-outlined text-[24px]">forum</span>
                    </div>
                    <h4 class="font-title-md font-bold text-on-surface mb-1.5 relative z-10">Buka Ruang Forum Course</h4>
                    <p class="font-body-sm text-on-surface-variant relative z-10 leading-snug">Berinteraksi (read-only), pantau Q&A dan isu terkait course ini (2 diskusi baru).</p>
                </button>
                


                <!-- Card 4: Timeline Activity Widget -->
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
                                        <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 border-white <?= e($act['color_class'] ?? 'bg-surface-container-high') ?> shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-sm relative z-10">
                                            <span class="material-symbols-outlined text-[14px] text-white" style="font-variation-settings: 'FILL' 1;"><?= e($act['icon'] ?? 'history') ?></span>
                                        </div>
                                        <div class="w-[calc(100%-3rem)] md:w-[calc(50%-2rem)]">
                                            <div class="flex flex-col">
                                                <h5 class="font-label-md font-semibold text-on-surface leading-snug"><?= e($act['action']) ?></h5>
                                                <time class="font-label-sm text-[10px] text-on-surface-variant mt-1"><?= e($act['time']) ?></time>
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

        <!-- Modal Catatan Revisi Course (Tolak dengan Catatan) -->
        <div id="rejectionNotesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeRejectionModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-outline-variant/40 relative z-10">
                    
                    <!-- Modal Header -->
                    <div class="p-6 sm:p-8 bg-surface-container-lowest border-b border-outline-variant/30 flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-error/10 text-error flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[26px]">rate_review</span>
                            </div>
                            <div>
                                <h3 class="font-headline-sm font-bold text-on-surface">Catatan Revisi Course</h3>
                                <p class="font-body-sm text-on-surface-variant">Berikan instruksi perbaikan untuk Instruktur</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeRejectionModal()" class="w-8 h-8 rounded-full bg-surface-container hover:bg-surface-variant flex items-center justify-center text-on-surface-variant transition-colors">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 sm:p-8 space-y-6">
                        <!-- Kategori Revisi -->
                        <div>
                            <label class="font-label-md font-bold text-on-surface block mb-3">Pilih Fokus Revisi (Bisa lebih dari satu):</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/60 hover:bg-surface-container-lowest cursor-pointer transition-colors">
                                    <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary/30" value="Silabus & Kurikulum">
                                    <span class="font-label-sm font-semibold text-on-surface">Silabus & Kurikulum</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/60 hover:bg-surface-container-lowest cursor-pointer transition-colors">
                                    <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary/30" value="Materi Video / Dokumen">
                                    <span class="font-label-sm font-semibold text-on-surface">Materi Video / Dokumen</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/60 hover:bg-surface-container-lowest cursor-pointer transition-colors">
                                    <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary/30" value="Soal Assessment / Quiz">
                                    <span class="font-label-sm font-semibold text-on-surface">Soal Assessment / Quiz</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/60 hover:bg-surface-container-lowest cursor-pointer transition-colors">
                                    <input type="checkbox" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary/30" value="Standar Kompetensi & SOP">
                                    <span class="font-label-sm font-semibold text-on-surface">Standar Kompetensi & SOP</span>
                                </label>
                            </div>
                        </div>

                        <!-- Textarea Instruksi Revisi -->
                        <div>
                            <label for="rejectionNoteText" class="font-label-md font-bold text-on-surface block mb-2">Instruksi Detail Perbaikan <span class="text-error">*</span></label>
                            <textarea id="rejectionNoteText" rows="4" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 focus:border-error focus:ring-2 focus:ring-error/20 font-body-sm text-on-surface placeholder:text-on-surface-variant/50 outline-none transition-all resize-none" placeholder="Contoh: Mohon lengkapi studi kasus pada Sesi 2 dan pastikan soal evaluasi mencakup minimal 5 pertanyaan standar K3..."></textarea>
                            <p class="font-label-sm text-[11px] text-on-surface-variant mt-1.5">Instruktur akan menerima email dan notifikasi berisi catatan di atas.</p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 sm:px-8 py-5 bg-surface-container-lowest border-t border-outline-variant/30 flex items-center justify-end gap-3">
                        <button type="button" onclick="closeRejectionModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-variant/40 font-label-md font-semibold transition-all">
                            Batal
                        </button>
                        <button type="button" onclick="submitRejectionNotes()" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-error hover:bg-error/90 text-white font-label-md font-bold shadow-sm hover:shadow transition-all">
                            <span class="material-symbols-outlined text-[18px]">send</span>
                            Kirim Catatan Revisi
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>
    <script>
        function handleAdminAction(actionType) {
            if (typeof DLS === 'undefined') {
                alert("Fitur sedang memuat, silakan coba beberapa saat lagi.");
                return;
            }

            const courseId = '<?= e((string)$course['id']) ?>';

            if (actionType === 'approve') {
                DLS.confirm(
                    'Setujui Course Ini?',
                    'Course akan langsung aktif dan dapat diakses oleh seluruh peserta terdaftar di portal Student.',
                    'Ya, Publikasikan',
                    'Batal',
                    'question'
                ).then((result) => {
                    if (result && (result.isConfirmed || result === true)) {
                        fetch('?route=/admin/course/approve&id=' + encodeURIComponent(courseId))
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    DLS.success('Berhasil Disetujui', data.message || 'Course berhasil dipublikasikan ke katalog aktif LMS.').then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    DLS.error('Gagal', data.error || 'Terjadi kesalahan sistem.');
                                }
                            })
                            .catch(err => {
                                DLS.error('Error', 'Gagal menghubungi server.');
                            });
                    }
                });
            } else if (actionType === 'reject') {
                openRejectionModal();
            } else if (actionType === 'export') {
                DLS.success('Sedang Mengunduh', 'Rekap progress peserta dalam format .XLSX berhasil diproses.');
            } else if (actionType === 'archive') {
                DLS.confirm(
                    'Arsipkan Course?',
                    'Course yang diarsipkan tidak lagi muncul di katalog aktif peserta.',
                    'Ya, Arsipkan',
                    'Batal',
                    'warning'
                ).then((result) => {
                    if (result && (result.isConfirmed || result === true)) {
                        fetch('?route=/admin/course/archive&id=' + encodeURIComponent(courseId))
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    DLS.success('Diarsipkan', data.message).then(() => window.location.reload());
                                } else {
                                    DLS.error('Gagal', data.error);
                                }
                            });
                    }
                });
            } else if (actionType === 'unarchive') {
                DLS.confirm(
                    'Aktifkan Kembali Course?',
                    'Course akan diaktifkan kembali dan muncul di katalog.',
                    'Ya, Aktifkan',
                    'Batal',
                    'question'
                ).then((result) => {
                    if (result && (result.isConfirmed || result === true)) {
                        fetch('?route=/admin/course/unarchive&id=' + encodeURIComponent(courseId))
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    DLS.success('Diaktifkan', data.message).then(() => window.location.reload());
                                } else {
                                    DLS.error('Gagal', data.error);
                                }
                            });
                    }
                });
            } else if (actionType === 'delete') {
                DLS.confirm(
                    'Hapus Course Permanen?',
                    'PERINGATAN: Course ini dan seluruh materi, kuis, sesi, serta relasinya akan DIHAPUS TOTAL dari database SSMS. Tindakan ini tidak dapat dibatalkan!',
                    'Ya, Hapus Permanen',
                    'Batal',
                    'error'
                ).then((result) => {
                    if (result && (result.isConfirmed || result === true)) {
                        fetch('?route=/admin/course/delete&id=' + encodeURIComponent(courseId))
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    DLS.success('Berhasil Dihapus', data.message).then(() => {
                                        window.location.href = '?route=/admin/course';
                                    });
                                } else {
                                    DLS.error('Gagal Menghapus', data.error);
                                }
                            })
                            .catch(() => DLS.error('Error', 'Terjadi kesalahan sistem saat menghapus course.'));
                    }
                });
            } else if (actionType === 'forum') {
                window.location.href = '?route=/admin/forum&course_id=<?= urlencode((string)($course['id'] ?? '1')) ?>';
            } else if (actionType === 'view_all_participants') {
                DLS.toast('Menampilkan seluruh daftar peserta terdaftar', 'info');
            }
        }

        function openRejectionModal() {
            const modal = document.getElementById('rejectionNotesModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('block');
            }
        }

        function closeRejectionModal() {
            const modal = document.getElementById('rejectionNotesModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('block');
            }
        }

        function submitRejectionNotes() {
            const noteText = document.getElementById('rejectionNoteText')?.value?.trim();
            if (!noteText) {
                if (typeof DLS !== 'undefined') {
                    DLS.toast('Mohon isi instruksi detail perbaikan terlebih dahulu!', 'warning');
                } else {
                    alert('Mohon isi instruksi detail perbaikan terlebih dahulu!');
                }
                return;
            }

            closeRejectionModal();

            const courseId = '<?= e((string)$course['id']) ?>';
            if (typeof DLS !== 'undefined') {
                DLS.confirm(
                    'Kirim Catatan Revisi?',
                    'Catatan penolakan dan instruksi perbaikan akan dikirimkan ke akun Instruktur.',
                    'Ya, Kirim Sekarang',
                    'Batal',
                    'warning'
                ).then((result) => {
                    if (result && (result.isConfirmed || result === true)) {
                        fetch('?route=/admin/course/reject', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: courseId, notes: noteText })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                DLS.success('Revisi Diminta', data.message).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                DLS.error('Gagal', data.error);
                            }
                        })
                        .catch(() => DLS.error('Error', 'Gagal mengirim catatan revisi.'));
                    } else {
                        openRejectionModal();
                    }
                });
            }
        }
    </script>
</body>
</html>
