<?php
$tab = $tab ?? ($_GET['tab'] ?? 'materi');
$courseId = $course['id'] ?? 'CRS001';
$completionDate = formatDateID($course['completion_date'] ?? '2025-06-20');
$nilaiFa = (int) ($course['quiz_score'] ?? 100);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $active_tab = 'riwayat';
    $page_title = 'Detail Riwayat - DENSO Learning';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        .animate-fade-in-up {
            animation: fadeInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(16px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="dls-app-shell bg-[#FAFAFA] text-[#171717] antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Detail Riwayat';
    $show_back = true;
    $back_url = '?route=/student/riwayat';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <main class="dls-main flex-1 w-full bg-[#FAFAFA]">
        <!-- Hero Banner (FoodDash Dark Charcoal Cover) -->
        <div class="dls-course-hero relative w-full min-h-[210px] md:min-h-[240px] rounded-[12px] overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,0.12)] bg-[#171717]">
            <?php if (!empty($course['thumbnail'])): ?>
            <img alt="<?= e($course['title']) ?> banner" class="absolute inset-0 w-full h-full object-cover" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e($course['thumbnail']) ?>">
            <?php endif; ?>
            <?php
            $icon = $course['thumbnail_icon'] ?? 'school';
            $showFallback = empty($course['thumbnail']) ? 'flex' : 'none';
            ?>
            <div class="dls-course-hero-fallback absolute inset-0 w-full h-full items-center justify-center<?= !empty($course['thumbnail']) ? ' hidden' : '' ?>" style="display:<?= $showFallback ?>">
                <span class="material-symbols-outlined text-white/25" style="font-variation-settings: 'FILL' 1;"><?= e($icon) ?></span>
            </div>
            <div class="dls-course-hero-overlay absolute inset-0"></div>
            <div class="absolute bottom-0 left-0 right-0 z-10 flex flex-col justify-end p-[18px] sm:p-[24px] text-white">
                <div class="fd-chip fd-chip-success mb-[10px] w-fit shadow-sm">
                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <span>Selesai pada <?= e($completionDate) ?></span>
                </div>
                <h2 class="fd-h1 text-[22px] sm:text-[26px] leading-tight text-white mb-[12px]"><?= e($course['title']) ?></h2>
                <div class="w-full">
                    <div class="flex justify-between items-center mb-[6px]">
                        <span class="text-[12px] font-medium text-white/80">Course Tuntas · Sertifikat Siap</span>
                        <span class="fd-mono text-[14px] font-bold text-white">100%</span>
                    </div>
                    <div class="w-full bg-white/20 h-[6px] rounded-full overflow-hidden">
                        <div class="bg-[#16A34A] h-full rounded-full" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description and Tags Section -->
        <div class="dls-course-summary fd-card mt-[18px] w-full rounded-[12px] bg-white p-[18px] sm:p-[24px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col gap-[14px]">
            <?php if (!empty($course['description'])): ?>
            <div class="flex flex-col gap-[6px]">
                <h3 class="fd-h4 text-[#171717] flex items-center gap-[8px]">
                    <span class="material-symbols-outlined text-[#16A34A] text-[20px]" style="font-variation-settings: 'FILL' 1;">info</span> Deskripsi Course
                </h3>
                <p class="fd-body text-[#525252] leading-[22px]">
                    <?= nl2br(e($course['description'])) ?>
                </p>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['filters'])): ?>
            <div class="flex items-center flex-wrap gap-[8px] pt-[4px] border-t border-[#F5F5F5]">
                <span class="text-[12px] text-[#737373] font-semibold mr-1 flex items-center gap-1 pt-2">
                    <span class="material-symbols-outlined text-[16px]">sell</span> Tags:
                </span>
                <?php foreach ($course['filters'] as $tag): ?>
                <a href="?route=/student/courses/filters&filter=<?= urlencode($tag) ?>" class="mt-2 px-3 py-1 bg-[#F5F5F5] text-[#525252] hover:bg-[#DC2626] hover:text-white transition-colors duration-200 text-[12px] font-medium rounded-full cursor-pointer flex items-center">
                    <?= e($tag) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs (FoodDash Pill Tabs) -->
        <div class="dls-course-tabs w-full bg-[#F5F5F5] rounded-full sticky top-[66px] z-30 p-[6px] mt-[18px]">
            <nav class="flex overflow-x-auto hide-scrollbar gap-[6px]">
                <?php foreach (['materi' => 'Materi', 'quiz' => 'Quiz', 'forum' => 'Forum', 'riwayat' => 'Riwayat', 'capaian' => 'Capaian'] as $tabKey => $tabLabel): ?>
                <a href="?route=/student/riwayat/detail&id=<?= e($courseId) ?>&tab=<?= e($tabKey) ?>" class="dls-course-tab flex-1 py-[10px] px-[14px] text-center text-[14px] rounded-full whitespace-nowrap transition-all <?= $tab === $tabKey
                    ? 'is-active bg-[#DC2626] text-white font-semibold'
                    : 'text-[#525252] hover:text-[#171717]' ?>">
                    <span class="truncate"><?= e($tabLabel) ?></span>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if ($tab === 'capaian'): ?>
        <!-- Content Area: Capaian Pembelajaran -->
        <?php
        $isFaLulus = $nilaiFa >= 75;
        $scoreColorClass = 'text-[#EF4444]';
        $scoreStrokeColor = '#EF4444';
        if ($nilaiFa >= 75) {
            $scoreColorClass = 'text-[#16A34A]';
            $scoreStrokeColor = '#16A34A';
        } elseif ($nilaiFa >= 50) {
            $scoreColorClass = 'text-[#D97706]';
            $scoreStrokeColor = '#D97706';
        }
        ?>
        <div class="p-[18px] sm:p-[24px] flex flex-col gap-[18px] mt-[6px] pb-8 animate-fade-in-up">

            <!-- Hero Banner Status -->
            <div class="fd-card rounded-[12px] p-[18px] sm:p-[24px] flex items-center gap-[14px] bg-[#DCFCE7] text-[#15803D] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-[#16A34A] text-white">
                    <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">emoji_events</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="fd-h3 font-bold mb-[2px]">Lulus &amp; Bersertifikat</h3>
                    <p class="fd-body text-[#15803D]">Diselesaikan pada <?= e($completionDate) ?>. Selamat!</p>
                </div>
            </div>

            <!-- Kriteria Kelulusan Checklist -->
            <section class="flex flex-col gap-[12px]">
                <h3 class="fd-h4 text-[#171717]">Kriteria Kelulusan</h3>
                <div class="fd-card bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden">
                    <div class="p-[16px] flex items-center gap-[12px] border-b border-[#F5F5F5]">
                        <span class="material-symbols-outlined text-[#16A34A]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <div class="flex-1">
                            <p class="text-[14px] font-semibold text-[#171717]">Menyelesaikan Semua Materi (100%)</p>
                            <p class="text-[12px] text-[#525252] mt-0.5">Progress tercapai: <strong class="fd-mono text-[#171717]">100%</strong></p>
                        </div>
                    </div>
                    <div class="p-[16px] flex items-center gap-[12px]">
                        <span class="material-symbols-outlined text-[#16A34A]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <div class="flex-1">
                            <p class="text-[14px] font-semibold text-[#171717]">Lulus Final Assessment (Min. Skor 75)</p>
                            <p class="text-[12px] text-[#525252] mt-0.5">Skor tercapai: <strong class="fd-mono text-[#171717]"><?= $nilaiFa ?></strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary stats -->
            <section class="flex flex-col gap-[12px]">
                <h3 class="fd-h4 text-[#171717]">Statistik Capaian</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[18px]">
                    <div class="fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col items-center justify-center gap-[8px] text-center">
                        <div class="relative w-20 h-20 flex items-center justify-center mx-auto">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#F5F5F5]" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                                <path stroke="<?= $scoreStrokeColor ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="3" stroke-dasharray="<?= $nilaiFa ?>, 100" stroke-linecap="round"></path>
                            </svg>
                            <div class="absolute flex flex-col items-center justify-center">
                                <span class="fd-mono text-[22px] <?= $scoreColorClass ?> font-bold leading-none"><?= $nilaiFa ?></span>
                            </div>
                        </div>
                        <div class="mt-1">
                            <p class="text-[14px] font-semibold text-[#171717]">Skor Akhir</p>
                            <p class="text-[12px] <?= $scoreColorClass ?> mt-0.5 font-semibold"><?= $isFaLulus ? 'Lulus' : 'Belum Lulus' ?></p>
                        </div>
                    </div>

                    <div class="fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col items-center justify-center gap-[8px] text-center">
                        <div class="w-14 h-14 rounded-full bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                        </div>
                        <div>
                            <p class="fd-mono text-[22px] text-[#171717] font-bold">100%</p>
                            <p class="text-[12px] text-[#16A34A] mt-0.5 font-semibold">Tuntas</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sertifikat Section -->
            <section class="flex flex-col gap-[12px]">
                <h3 class="fd-h4 text-[#171717]">Sertifikat Digital</h3>
                <div class="fd-card bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden relative">
                    <div class="h-44 fd-cover-fallback relative overflow-hidden">
                        <div class="flex flex-col items-center justify-center text-white/90 gap-[8px]">
                            <span class="fd-cover-icon-badge">
                                <span class="material-symbols-outlined" style="font-size:32px; font-variation-settings:'FILL' 1;">workspace_premium</span>
                            </span>
                            <span class="text-[12px] font-medium text-white/85">Sertifikat Kelulusan Resmi</span>
                        </div>
                        <div class="fd-chip fd-chip-success absolute top-[12px] right-[12px] shadow-sm">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">verified</span> Valid
                        </div>
                    </div>
                    <div class="p-[18px] bg-white flex flex-col gap-[12px]">
                        <div>
                            <p class="fd-h4 text-[#171717]">Certificate of Completion</p>
                            <p class="fd-body text-[#525252] mt-1">Diterbitkan secara otomatis oleh sistem LMS. Dapat digunakan sebagai bukti kelulusan.</p>
                        </div>
                        <button type="button" class="fd-btn fd-btn-primary fd-btn-md w-full rounded-full">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            <span>Unduh Sertifikat (PDF)</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <?php elseif ($tab === 'materi'): ?>
        <!-- Content Area: Materi (Read Only, All Completed) -->
        <div class="p-[18px] sm:p-[24px] flex flex-col gap-[18px] mt-[6px] animate-fade-in-up">
            <div class="flex flex-col gap-[18px]">
                <?php if (!empty($course['sessions'])): ?>
                <label class="relative block"><span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#737373]" style="font-size:20px;">search</span><input id="historySessionSearch" type="search" class="fd-input fd-search-input w-full rounded-full pl-11 pr-4" placeholder="Cari sesi atau materi yang sudah selesai..." aria-label="Cari sesi riwayat"></label>
                <?php foreach ($course['sessions'] as $index => $session): ?>
                <div class="history-session-card fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex justify-between items-start mb-[12px] gap-[12px]">
                        <div>
                            <span class="text-[11px] font-semibold text-[#DC2626] uppercase tracking-[0.12em]">Sesi <?= $index + 1 ?></span>
                            <h3 class="fd-h3 text-[#171717] mt-1"><?= e($session['title']) ?></h3>
                        </div>
                        <div class="fd-chip fd-chip-success shrink-0">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Selesai</span>
                        </div>
                    </div>

                    <!-- Materials -->
                    <div class="fd-list rounded-[10px] bg-[#FAFAFA] overflow-hidden mt-[8px]">
                        <?php foreach ($session['materials'] as $mIndex => $material): ?>
                        <?php
                        $icon = 'article';
                        if ($material['type'] === 'video') $icon = 'play_circle';
                        if ($material['type'] === 'pdf') $icon = 'picture_as_pdf';
                        if ($material['type'] === 'image') $icon = 'image';
                        ?>
                        <a href="?route=/student/learning&id=<?= e($courseId) ?>&session_id=<?= e($session['id']) ?>&material_index=<?= $mIndex ?>" class="fd-list-row group">
                            <div class="fd-list-thumb bg-[#DCFCE7] text-[#16A34A] shrink-0">
                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;"><?= $icon ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[14px] font-semibold text-[#171717] truncate group-hover:text-[#DC2626] transition-colors"><?= e($material['title']) ?></p>
                                <p class="text-[12px] text-[#737373] mt-0.5"><span class="fd-mono"><?= e($material['duration']) ?></span> · Selesai</p>
                            </div>
                            <div class="flex items-center gap-[8px] shrink-0">
                                <span class="fd-chip fd-chip-neutral text-[11px]">Buka Lagi</span>
                                <span class="material-symbols-outlined text-[#16A34A] text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="fd-card rounded-[12px] bg-white p-[32px] text-center text-[#737373] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <span class="material-symbols-outlined mb-2 text-4xl opacity-45">menu_book</span>
                    <p class="fd-body">Materi course belum tersedia.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($tab === 'quiz'): ?>
        <!-- Content Area: Quiz (Read Only, Completed) -->
        <div class="p-[18px] sm:p-[24px] flex flex-col gap-[12px] mt-[6px] animate-fade-in-up">
            <?php if (!empty($course['quiz']) || !empty($course['final_assessment'])): ?>

            <?php if (!empty($course['quiz'])): ?>
            <?php $quiz = $course['quiz']; ?>
            <div class="fd-list rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden w-full">
                <a href="?route=/student/quiz&id=<?= e($courseId) ?>&type=quiz" class="fd-list-row w-full text-left group">
                    <div class="fd-list-thumb bg-[#DCFCE7] text-[#16A34A]">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">quiz</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="fd-h4 text-[#171717] mb-[2px]">Quiz Course</h3>
                        <p class="fd-body text-[#525252]">
                            <span class="fd-mono"><?= count($quiz['questions'] ?? []) ?></span> Pertanyaan · Skor Tertinggi: <strong class="fd-mono text-[#171717]"><?= e((string) ($quiz['score'] ?? '100')) ?></strong>
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-[8px]">
                        <span class="fd-btn fd-btn-secondary fd-btn-sm rounded-full">Ulangi</span>
                        <span class="material-symbols-outlined text-[#16A34A] text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
            <?php $fa = $course['final_assessment']; $isPassed = ($fa['nilai'] ?? null) !== null && $fa['nilai'] >= 75; ?>
            <div class="fd-list rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden w-full <?= $isPassed ? 'opacity-90' : '' ?>">
                <a href="<?= !$isPassed ? '?route=/student/quiz&id=' . e($courseId) . '&type=final_assessment' : 'javascript:void(0)' ?>" class="fd-list-row w-full text-left <?= $isPassed ? 'cursor-default' : '' ?>">
                    <div class="fd-list-thumb <?= $isPassed ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF2F2] text-[#DC2626]' ?>">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' <?= $isPassed ? '1' : '0' ?>;">military_tech</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-[8px] mb-[2px]">
                            <h3 class="fd-h4 text-[#171717]">Final Assessment</h3>
                            <?php if ($isPassed): ?>
                            <span class="fd-chip fd-chip-success text-[10px]">Lulus</span>
                            <?php elseif (($fa['nilai'] ?? null) !== null && $fa['nilai'] < 75): ?>
                            <span class="fd-chip fd-chip-error text-[10px]">Remedial</span>
                            <?php endif; ?>
                        </div>
                        <p class="fd-body text-[#525252]">
                            <span class="fd-mono"><?= count($fa['questions'] ?? []) ?></span> Pertanyaan · Nilai: <strong class="fd-mono text-[#171717]"><?= e((string) ($fa['nilai'] ?? '100')) ?></strong> (Min. 75)
                        </p>
                    </div>
                    <div class="shrink-0">
                        <?php if ($isPassed): ?>
                        <span class="material-symbols-outlined text-[#16A34A] text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <?php else: ?>
                        <span class="w-9 h-9 rounded-full bg-[#DC2626] text-white flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="fd-card rounded-[12px] bg-white p-[32px] text-center text-[#737373] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-45">quiz</span>
                <p class="fd-body">Tidak ada quiz untuk course ini.</p>
            </div>
            <?php endif; ?>
        </div>

        <?php elseif ($tab === 'forum'): ?>
        <?php
        $forumsMateri = $course['forums']['materi'] ?? [];
        $forumQuiz = $course['forums']['quiz'] ?? ['threads' => []];
        $totalForumsMateri = 0;
        foreach ($forumsMateri as $f) {
            $totalForumsMateri += count($f['threads']);
        }
        $totalForumsQuiz = count($forumQuiz['threads']);
        $totalForums = $totalForumsMateri + $totalForumsQuiz;
        ?>
        <!-- Content Area: Forum (Read Only) -->
        <div class="p-[18px] sm:p-[24px] space-y-[18px] mt-[6px] animate-fade-in-up pb-8">
            <div class="flex items-center justify-between">
                <h2 class="fd-h2 text-[#171717]">Forum Diskusi</h2>
                <span class="fd-chip fd-chip-neutral text-[12px]"><span class="fd-mono mr-1"><?= $totalForums ?></span> Topik Tersedia</span>
            </div>

            <!-- Banner Read-Only -->
            <div class="fd-card bg-[#DBEAFE] text-[#1E40AF] rounded-[12px] p-[16px] flex items-start gap-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                <span class="material-symbols-outlined mt-0.5 text-[20px]">info</span>
                <p class="text-[13px] leading-[20px]">Course ini telah diselesaikan. Tab Forum kini bersifat <strong>Read-Only</strong>. Anda tetap dapat membaca riwayat diskusi yang ada.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-[18px]">
                <?php if (!empty($course['sessions'])): ?>
                <?php foreach ($course['sessions'] as $session):
                    $sessId = $session['id'];
                    $forumData = $forumsMateri[$sessId] ?? ['threads' => []];
                    $threadCount = count($forumData['threads']);
                ?>
                <a href="?route=/student/forum/category&course_id=<?= urlencode($course['id']) ?>&category_type=materi&session_id=<?= urlencode($sessId) ?>&is_riwayat=true" class="fd-card fd-card-interactive block bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] group">
                    <div class="flex items-start justify-between gap-[12px]">
                        <div class="min-w-0">
                            <h4 class="fd-h4 text-[#171717] mb-[4px] group-hover:text-[#DC2626] transition-colors flex items-center gap-[8px]">
                                <span class="material-symbols-outlined text-[#DC2626] text-[20px]">auto_stories</span>
                                <span class="truncate">Forum <?= e($session['title']) ?></span>
                            </h4>
                            <p class="fd-body text-[#525252] mb-[12px] line-clamp-2">Diskusi materi dan tugas terkait sesi ini.</p>
                            <span class="fd-chip fd-chip-neutral text-[11px]"><span class="fd-mono mr-1"><?= $threadCount ?></span> Diskusi</span>
                        </div>
                        <div class="shrink-0 w-10 h-10 rounded-full bg-[#FEF2F2] text-[#DC2626] group-hover:bg-[#DC2626] group-hover:text-white flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- Quiz Forum -->
                <?php
                $qThreads = $forumQuiz['threads'] ?? [];
                $qThreadCount = count($qThreads);
                ?>
                <a href="?route=/student/forum/category&course_id=<?= urlencode($course['id']) ?>&category_type=quiz&is_riwayat=true" class="fd-card fd-card-interactive block bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] group">
                    <div class="flex items-start justify-between gap-[12px]">
                        <div class="min-w-0">
                            <h4 class="fd-h4 text-[#171717] mb-[4px] group-hover:text-[#DC2626] transition-colors flex items-center gap-[8px]">
                                <span class="material-symbols-outlined text-[#DC2626] text-[20px]">quiz</span>
                                <span>Forum Quiz</span>
                            </h4>
                            <p class="fd-body text-[#525252] mb-[12px] line-clamp-2">Pembahasan dan pertanyaan seputar quiz course.</p>
                            <span class="fd-chip fd-chip-neutral text-[11px]"><span class="fd-mono mr-1"><?= $qThreadCount ?></span> Diskusi</span>
                        </div>
                        <div class="shrink-0 w-10 h-10 rounded-full bg-[#FEF2F2] text-[#DC2626] group-hover:bg-[#DC2626] group-hover:text-white flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <?php elseif ($tab === 'riwayat'): ?>
        <!-- Content Area: Riwayat (FoodDash Borderless Cards) -->
        <div class="p-[18px] sm:p-[24px] flex flex-col gap-[18px] mt-[6px] pb-8 animate-fade-in-up">
            <div>
                <h2 class="fd-h2 text-[#171717]">Riwayat Aktivitas &amp; Evaluasi</h2>
                <p class="fd-body text-[#525252]">Log komprehensif perjalanan pembelajaran Anda di course ini.</p>
            </div>

            <?php
            $activities = [];
            $assessments = [];

            if (!empty($course['sessions'])) {
                foreach ($course['sessions'] as $session) {
                    if (!empty($session['materials'])) {
                        foreach ($session['materials'] as $mat) {
                            if ($mat['status'] === 'selesai') {
                                $matDate = date(
                                    'Y-m-d H:i:s',
                                    strtotime(
                                        '-' . rand(1, 48) . ' hours',
                                        strtotime($course['last_accessed'] ?? date('Y-m-d H:i:s')),
                                    ),
                                );
                                $activities[] = [
                                    'title' => 'Menyelesaikan Materi: ' . $mat['title'],
                                    'desc' => $session['title'],
                                    'date' => $matDate,
                                    'dot_class' => 'bg-[#16A34A]',
                                ];
                            }
                        }
                    }
                }
            }

            if (!empty($course['quiz']['history'])) {
                $assessments['quiz'] = $course['quiz']['history'];
                foreach ($course['quiz']['history'] as $h) {
                    $activities[] = [
                        'title' => 'Mengerjakan Quiz Course',
                        'desc' => 'Percobaan ' . $h['attempt'],
                        'date' => $h['date'],
                        'dot_class' => 'bg-[#DC2626]',
                    ];
                }
            }

            if (!empty($course['final_assessment']['history'])) {
                $assessments['final'] = $course['final_assessment']['history'];
                foreach ($course['final_assessment']['history'] as $h) {
                    $activities[] = [
                        'title' => 'Mengerjakan Final Assessment',
                        'desc' => 'Percobaan ' . $h['attempt'],
                        'date' => $h['date'],
                        'dot_class' => 'bg-[#EA580C]',
                    ];
                }
            }

            usort($activities, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            ?>

            <!-- 1. Aktivitas Student -->
            <section class="fd-card rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden">
                <button type="button" onclick="toggleAccordion('rd-riwayat-aktivitas')" class="w-full flex items-center justify-between p-[18px] text-left hover:bg-[#FAFAFA] transition-colors duration-200 gap-[12px]">
                    <div class="flex flex-wrap items-center gap-[10px] min-w-0">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-[10px] bg-[#DBEAFE] text-[#2563EB]">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">timeline</span>
                        </span>
                        <span class="fd-h3 text-[#171717]">1. Aktivitas</span>
                        <span class="fd-chip fd-chip-neutral text-[11px]"><?= count($activities) ?> aktivitas</span>
                    </div>
                    <span class="material-symbols-outlined text-[#737373] transition-transform duration-300 shrink-0 rotate-[-90deg]" id="rd-riwayat-aktivitas-chevron">expand_more</span>
                </button>
                <div id="rd-riwayat-aktivitas" class="accordion-body" style="display: none;">
                    <div class="px-[18px] pb-[18px] border-t border-[#F5F5F5] pt-[18px]">
                        <?php if (empty($activities)): ?>
                        <div class="text-center py-6 text-[#737373]">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">history</span>
                            <p class="fd-body">Belum ada aktivitas yang tercatat.</p>
                        </div>
                        <?php else: ?>
                        <div class="relative pl-6" id="rd-riwayat-aktivitas-list">
                            <div class="absolute left-[11px] top-4 bottom-4 w-[2px] bg-[#E5E5E5]"></div>
                            <?php foreach ($activities as $idx => $act): ?>
                            <div class="relative mb-5 last:mb-0 riwayat-item <?= $idx >= 5 ? 'riwayat-hidden' : '' ?>" <?= $idx >= 5 ? 'style="display:none;"' : '' ?>>
                                <div class="absolute -left-6 top-1.5 w-[10px] h-[10px] rounded-full <?= e($act['dot_class'] ?? 'bg-[#DC2626]') ?> ring-4 ring-white"></div>
                                <div class="ml-4 flex flex-col">
                                    <p class="text-[14px] font-semibold text-[#171717] leading-tight"><?= e($act['title']) ?></p>
                                    <p class="text-[13px] text-[#525252] mt-0.5"><?= e($act['desc']) ?></p>
                                    <span class="fd-mono text-[11px] text-[#737373] flex items-center gap-1 mt-1">
                                        <span class="material-symbols-outlined text-[13px]">schedule</span>
                                        <?= date('d M Y, H:i', strtotime($act['date'])) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($activities) > 5): ?>
                        <button type="button" onclick="toggleShowAll('rd-riwayat-aktivitas-list', this)" class="fd-btn fd-btn-secondary fd-btn-md mt-[18px] w-full rounded-full">
                            <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                            <span class="btn-text">Tampilkan Semua (<?= count($activities) - 5 ?> lainnya)</span>
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 2. Riwayat Quiz dan Assessment -->
            <section class="fd-card rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden">
                <button type="button" onclick="toggleAccordion('rd-riwayat-qa')" class="w-full flex items-center justify-between p-[18px] text-left hover:bg-[#FAFAFA] transition-colors duration-200 gap-[12px]">
                    <div class="flex flex-wrap items-center gap-[10px] min-w-0">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-[10px] bg-[#FEF2F2] text-[#DC2626]">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                        </span>
                        <span class="fd-h3 text-[#171717]">2. Quiz &amp; Assessment</span>
                        <?php $qaCount = count($assessments['quiz'] ?? []) + count($assessments['final'] ?? []); ?>
                        <span class="fd-chip fd-chip-neutral text-[11px]"><?= $qaCount ?> percobaan</span>
                    </div>
                    <span class="material-symbols-outlined text-[#737373] transition-transform duration-300 shrink-0 rotate-[-90deg]" id="rd-riwayat-qa-chevron">expand_more</span>
                </button>
                <div id="rd-riwayat-qa" class="accordion-body" style="display: none;">
                    <div class="px-[18px] pb-[18px] border-t border-[#F5F5F5] pt-[18px]">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-[18px]">
                            <!-- Quiz Course -->
                            <div class="rounded-[12px] bg-[#FAFAFA] p-[16px]">
                                <h4 class="fd-h4 text-[#171717] mb-[12px] pb-[8px] border-b border-[#E5E5E5]">Quiz Course</h4>
                                <?php if (empty($assessments['quiz'])): ?>
                                <p class="text-[13px] text-[#737373] py-2">Belum ada riwayat Quiz Course.</p>
                                <?php else: ?>
                                <div class="flex flex-col gap-[8px]" id="rd-riwayat-quiz-list">
                                    <?php foreach ($assessments['quiz'] as $qIdx => $h): ?>
                                    <a href="?route=/student/quiz&id=<?= urlencode($courseId) ?>&type=quiz&attempt=<?= e((string) $h['attempt']) ?>" class="bg-white rounded-[10px] p-[12px] flex justify-between items-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] hover:shadow-[0_2px_8px_rgba(0,0,0,0.1)] transition-shadow riwayat-item <?= $qIdx >= 5 ? 'riwayat-hidden' : '' ?> group" <?= $qIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                        <div class="flex items-center gap-[10px]">
                                            <div class="w-8 h-8 bg-[#FEF2F2] text-[#DC2626] rounded-full flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]">quiz</span>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-[#171717] group-hover:text-[#DC2626] transition-colors">Percobaan <?= e((string) $h['attempt']) ?></p>
                                                <p class="fd-mono text-[#737373] text-[11px] mt-0.5"><?= date('d M Y, H:i', strtotime($h['date'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-[6px]">
                                            <?php
                                            $_s = $h['score'] ?? $h['nilai'];
                                            $_sc = $_s >= 75 ? 'text-[#16A34A]' : ($_s >= 50 ? 'text-[#D97706]' : 'text-[#EF4444]');
                                            ?>
                                            <span class="fd-mono text-[16px] font-bold <?= $_sc ?>"><?= e((string) $_s) ?></span>
                                            <span class="material-symbols-outlined text-[#A3A3A3] text-[16px] group-hover:text-[#DC2626]">chevron_right</span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['quiz']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('rd-riwayat-quiz-list', this)" class="fd-btn fd-btn-secondary fd-btn-sm mt-[12px] w-full rounded-full">
                                    <span class="material-symbols-outlined text-[16px]">unfold_more</span>
                                    <span class="btn-text">Tampilkan Semua (<?= count($assessments['quiz']) - 5 ?> lainnya)</span>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Final Assessment -->
                            <div class="rounded-[12px] bg-[#FAFAFA] p-[16px]">
                                <h4 class="fd-h4 text-[#171717] mb-[12px] pb-[8px] border-b border-[#E5E5E5]">Final Assessment</h4>
                                <?php if (empty($assessments['final'])): ?>
                                <p class="text-[13px] text-[#737373] py-2">Belum ada riwayat Final Assessment.</p>
                                <?php else: ?>
                                <div class="flex flex-col gap-[8px]" id="rd-riwayat-final-list">
                                    <?php foreach ($assessments['final'] as $fIdx => $h): ?>
                                    <?php
                                    $val = $h['score'] ?? $h['nilai'];
                                    $isPassed = $val >= 75;
                                    $_sc2 = $val >= 75 ? 'text-[#16A34A]' : ($val >= 50 ? 'text-[#D97706]' : 'text-[#EF4444]');
                                    ?>
                                    <a href="?route=/student/quiz&id=<?= urlencode($courseId) ?>&type=final_assessment&attempt=<?= e((string) $h['attempt']) ?>" class="bg-white rounded-[10px] p-[12px] flex justify-between items-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] hover:shadow-[0_2px_8px_rgba(0,0,0,0.1)] transition-shadow riwayat-item <?= $fIdx >= 5 ? 'riwayat-hidden' : '' ?> group" <?= $fIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                        <div class="flex items-center gap-[10px]">
                                            <div class="w-8 h-8 <?= $isPassed ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEE2E2] text-[#EF4444]' ?> rounded-full flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]"><?= $isPassed ? 'check_circle' : 'cancel' ?></span>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-[#171717] group-hover:text-[#DC2626] transition-colors">Percobaan <?= e((string) $h['attempt']) ?></p>
                                                <p class="fd-mono text-[#737373] text-[11px] mt-0.5"><?= date('d M Y, H:i', strtotime($h['date'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-[6px]">
                                            <span class="fd-chip <?= $isPassed ? 'fd-chip-success' : 'fd-chip-error' ?> text-[10px]"><?= $isPassed ? 'Lulus' : 'Remedial' ?></span>
                                            <span class="fd-mono text-[16px] font-bold <?= $_sc2 ?>"><?= e((string) $val) ?></span>
                                            <span class="material-symbols-outlined text-[#A3A3A3] text-[16px] group-hover:text-[#DC2626]">chevron_right</span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['final']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('rd-riwayat-final-list', this)" class="fd-btn fd-btn-secondary fd-btn-sm mt-[12px] w-full rounded-full">
                                    <span class="material-symbols-outlined text-[16px]">unfold_more</span>
                                    <span class="btn-text">Tampilkan Semua (<?= count($assessments['final']) - 5 ?> lainnya)</span>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php endif; ?>
    </main>

    <!-- BottomNavBar -->
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('historySessionSearch');
            const sessions = Array.from(document.querySelectorAll('.history-session-card'));
            const pageSize = document.getElementById('pageSizeSelect');
            const pagination = document.getElementById('paginationContainer');
            let page = 1;
            const renderSessions = () => {
                const query = (search?.value || '').trim().toLocaleLowerCase('id');
                const matching = sessions.filter(session => session.textContent.toLocaleLowerCase('id').includes(query));
                const size = Number(pageSize?.value) || 10;
                const pages = Math.max(1, Math.ceil(matching.length / size));
                page = Math.min(Math.max(page, 1), pages);
                sessions.forEach(session => { session.hidden = true; });
                matching.slice((page - 1) * size, page * size).forEach(session => { session.hidden = false; });
                if (pagination) {
                    pagination.classList.toggle('hidden', matching.length === 0);
                    document.getElementById('pageIndicator').textContent = `Halaman ${page} dari ${pages}`;
                    document.getElementById('prevPageBtn').disabled = page === 1;
                    document.getElementById('nextPageBtn').disabled = page === pages;
                }
            };
            search?.addEventListener('input', () => { page = 1; renderSessions(); });
            pageSize?.addEventListener('change', () => { page = 1; renderSessions(); });
            document.getElementById('prevPageBtn')?.addEventListener('click', () => { page--; renderSessions(); });
            document.getElementById('nextPageBtn')?.addEventListener('click', () => { page++; renderSessions(); });
            renderSessions();
        });

        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('riwayatDetailScrollPos', window.scrollY);
        });

        window.addEventListener('load', () => {
            const scrollPos = sessionStorage.getItem('riwayatDetailScrollPos');
            if (scrollPos !== null) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('riwayatDetailScrollPos');
            }
        });
        function toggleAccordion(elementOrId) {
            if (typeof elementOrId === 'string') {
                const body = document.getElementById(elementOrId);
                const chevron = document.getElementById(elementOrId + '-chevron');
                if (!body || !chevron) return;

                if (body.style.display === 'none') {
                    body.style.display = 'block';
                    chevron.style.transform = 'rotate(0deg)';
                } else {
                    body.style.display = 'none';
                    chevron.style.transform = 'rotate(-90deg)';
                }
            } else {
                const header = elementOrId;
                const body = header.nextElementSibling;
                const chevron = header.querySelector('.material-symbols-outlined:last-child');

                if (body && chevron) {
                    if (body.style.display === 'none') {
                        body.style.display = 'block';
                        chevron.style.transform = 'rotate(180deg)';
                    } else {
                        body.style.display = 'none';
                        chevron.style.transform = 'rotate(0deg)';
                    }
                }
            }
        }

        function toggleShowAll(listId, btn) {
            const list = document.getElementById(listId);
            if (!list) return;
            const hiddenItems = list.querySelectorAll('.riwayat-hidden');
            const btnText = btn.querySelector('.btn-text');
            const icon = btn.querySelector('.material-symbols-outlined');
            const isExpanded = btn.dataset.expanded === 'true';

            hiddenItems.forEach(function(item) {
                item.style.display = isExpanded ? 'none' : '';
            });

            if (isExpanded) {
                btn.dataset.expanded = 'false';
                btnText.textContent = btnText.dataset.originalText || 'Tampilkan Semua';
                if (icon) icon.textContent = 'unfold_more';
            } else {
                if (!btnText.dataset.originalText) {
                    btnText.dataset.originalText = btnText.textContent;
                }
                btn.dataset.expanded = 'true';
                btnText.textContent = 'Sembunyikan';
                if (icon) icon.textContent = 'unfold_less';
            }
        }
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
