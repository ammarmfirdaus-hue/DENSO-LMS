<?php
$tab = $tab ?? ($_GET['tab'] ?? 'materi');
$courseId = $course['id'] ?? 'CRS001';
$all_sessions_completed = $all_sessions_completed ?? false;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Learning Management System - Course Detail';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }



        .animate-fade-in-up {
            animation: fadeInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="dls-app-shell <?= $tab === 'quiz' ? 'dls-course-quiz' : '' ?> bg-surface text-on-surface antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Detail Course';
    $show_back = true;
    $back_url = '?route=/student/courses';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <main class="dls-main flex-1 w-full bg-[#FAFAFA]">
        <!-- Hero Banner (FoodDash Dark Charcoal Cover) -->
        <div class="dls-course-hero relative w-full min-h-[210px] md:min-h-[240px] rounded-[12px] overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,0.12)] bg-[#171717]">
            <?php if (!empty($course['thumbnail'])): ?>
            <img alt="<?= e(
                $course['title'],
            ) ?> banner" class="absolute inset-0 w-full h-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e(
     $course['thumbnail'],
 ) ?>">
            <?php endif; ?>
            <?php
            $icon = $course['thumbnail_icon'] ?? 'school';
            $showFallback = empty($course['thumbnail']) ? 'flex' : 'none';
            $courseProgress = max(0, min(100, (int) ($course['progress'] ?? 0)));
            ?>
            <div class="dls-course-hero-fallback absolute inset-0 w-full h-full items-center justify-center<?= !empty($course['thumbnail']) ? ' hidden' : '' ?>" style="display:<?= $showFallback ?>">
                <span class="material-symbols-outlined text-white/25" style="font-variation-settings: 'FILL' 1;"><?= e($icon) ?></span>
            </div>
            <div class="dls-course-hero-overlay absolute inset-0"></div>
            <div class="absolute bottom-0 left-0 right-0 z-10 flex flex-col justify-end p-[18px] sm:p-[24px] text-white">
                <span class="fd-chip fd-chip-promo mb-[8px] w-fit text-[11px]"><?= e((string) ($course['category'] ?? 'Course')) ?></span>
                <h2 class="fd-h1 text-[22px] sm:text-[26px] leading-tight text-white mb-[12px]"><?= e($course['title']) ?></h2>
                <div class="w-full">
                    <div class="flex justify-between items-center mb-[6px]">
                        <span class="text-[12px] font-medium text-white/80">Progress Pembelajaran</span>
                        <span class="fd-mono text-[14px] font-bold text-white"><?= $courseProgress ?>%</span>
                    </div>
                    <div class="w-full bg-white/20 h-[6px] rounded-full overflow-hidden">
                        <div class="<?= $courseProgress === 100 ? 'bg-[#16A34A]' : 'bg-[#DC2626]' ?> h-full rounded-full transition-all duration-300" style="width: <?= $courseProgress ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description and Tags Section -->
        <div class="dls-course-summary fd-card mt-[18px] w-full rounded-[12px] bg-white p-[18px] sm:p-[24px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col gap-[14px]">
            <?php if (!empty($course['description'])): ?>
            <div class="flex flex-col gap-[6px]">
                <h3 class="fd-h4 text-[#171717] flex items-center gap-[8px]">
                    <span class="material-symbols-outlined text-[#DC2626] text-[20px]" style="font-variation-settings: 'FILL' 1;">info</span> Deskripsi Course
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
                <a href="?route=/student/courses/filters&filter=<?= urlencode(
                    $tag,
                ) ?>" class="mt-2 px-3 py-1 bg-[#F5F5F5] text-[#525252] hover:bg-[#DC2626] hover:text-white transition-colors duration-200 text-[12px] font-medium rounded-full cursor-pointer flex items-center">
                    <?= e($tag) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs (FoodDash Pill Tabs) -->
        <div class="dls-course-tabs w-full bg-[#F5F5F5] rounded-full sticky top-[66px] z-30 p-[6px] mt-[18px]">
            <nav class="flex overflow-x-auto hide-scrollbar gap-[6px]">
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=materi" class="dls-course-tab flex-1 py-[10px] px-[14px] text-center text-[14px] rounded-full whitespace-nowrap transition-all <?= $tab === 'materi'
    ? 'is-active bg-[#DC2626] text-white font-semibold'
    : 'text-[#525252] hover:text-[#171717]' ?>">
                    <span class="truncate">Materi</span>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=quiz" class="dls-course-tab flex-1 py-[10px] px-[14px] text-center text-[14px] rounded-full whitespace-nowrap transition-all <?= $tab === 'quiz'
    ? 'is-active bg-[#DC2626] text-white font-semibold'
    : 'text-[#525252] hover:text-[#171717]' ?>">
                    <span class="truncate">Quiz</span>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=forum" class="dls-course-tab flex-1 py-[10px] px-[14px] text-center text-[14px] rounded-full whitespace-nowrap transition-all <?= $tab === 'forum'
    ? 'is-active bg-[#DC2626] text-white font-semibold'
    : 'text-[#525252] hover:text-[#171717]' ?>">
                    <span class="truncate">Forum</span>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=riwayat" class="dls-course-tab flex-1 py-[10px] px-[14px] text-center text-[14px] rounded-full whitespace-nowrap transition-all <?= $tab === 'riwayat'
    ? 'is-active bg-[#DC2626] text-white font-semibold'
    : 'text-[#525252] hover:text-[#171717]' ?>">
                    <span class="truncate">Riwayat</span>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=capaian" class="dls-course-tab flex-1 py-[10px] px-[14px] text-center text-[14px] rounded-full whitespace-nowrap transition-all <?= $tab === 'capaian'
    ? 'is-active bg-[#DC2626] text-white font-semibold'
    : 'text-[#525252] hover:text-[#171717]' ?>">
                    <span class="truncate">Capaian</span>
                </a>
            </nav>
        </div>

        <?php if ($tab === 'materi'): ?>
        <!-- Content Area: Materi (FoodDash 64px List Rows Inside Session Cards) -->
        <div class="p-[18px] flex flex-col gap-[18px] mt-[6px]">
            <div class="flex flex-col gap-[18px]">
                <?php if (!empty($course['sessions'])): ?>
                <label class="relative block"><span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#737373]" style="font-size:20px;">search</span><input id="courseMaterialSearch" type="search" class="fd-input fd-search-input w-full rounded-full pl-11 pr-4" placeholder="Cari sesi atau materi..." aria-label="Cari sesi dan materi"></label>
                <?php foreach ($course['sessions'] as $index => $session): ?>
                <div class="course-session-card fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                    <div class="flex justify-between items-start mb-[12px] gap-[12px]">
                        <div>
                            <span class="fd-mono text-[12px] font-medium text-[#737373] uppercase tracking-wider">Sesi <?= $index +
                                1 ?></span>
                            <h3 class="fd-h3 text-[#171717] mt-[3px]"><?= e(
                                $session['title'],
                            ) ?></h3>
                        </div>
                        <?php if ($session['status'] === 'selesai'): ?>
                        <span class="fd-chip fd-chip-success">
                            <span class="material-symbols-outlined text-[15px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Selesai</span>
                        </span>
                        <?php elseif ($session['status'] === 'sedang_dipelajari'): ?>
                        <span class="fd-chip fd-chip-info">
                            <span class="material-symbols-outlined text-[15px]">schedule</span>
                            <span>In Progress</span>
                        </span>
                        <?php else: ?>
                        <span class="fd-chip fd-chip-neutral">
                            <span class="material-symbols-outlined text-[15px]">lock</span>
                            <span>Terkunci</span>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Materials (FoodDash 64px List Rows, 48px Square Radius 8px Thumbnail Slots) -->
                    <div class="fd-list divide-y divide-[#F5F5F5] rounded-[12px] overflow-hidden border border-[#F5F5F5]">
                        <?php foreach ($session['materials'] as $mIndex => $material): ?>
                        <?php
                        $isLockedMat = ($material['status'] ?? '') === 'terkunci';
                        $isDoneMat = ($material['status'] ?? '') === 'selesai';
                        $isCurrentMat = !$isLockedMat && !$isDoneMat;
                        $icon = 'article';
                        if ($material['type'] === 'video') {
                            $icon = 'play_circle';
                        }
                        if ($material['type'] === 'pdf') {
                            $icon = 'picture_as_pdf';
                        }
                        if ($material['type'] === 'image') {
                            $icon = 'image';
                        }
                        ?>
                        <a href="<?= !$isLockedMat
                            ? '?route=/student/learning&id=' .
                                e($courseId) .
                                '&session_id=' .
                                e($session['id']) .
                                '&material_index=' .
                                $mIndex
                            : 'javascript:void(0)' ?>" class="fd-list-row <?= $isCurrentMat ? 'is-selected' : '' ?> <?= $isLockedMat ? 'opacity-45 cursor-not-allowed' : '' ?>">
                            <div class="fd-list-thumb <?= $isLockedMat
                                ? 'bg-[#F5F5F5] text-[#737373]'
                                : ($isDoneMat ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF2F2] text-[#DC2626]') ?>">
                                <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' <?= $isDoneMat ? '1' : '0' ?>;"><?= $icon ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-[14px] leading-[20px] text-[#171717] truncate"><?= e($material['title']) ?></p>
                                <p class="fd-mono text-[12px] leading-[18px] text-[#737373] mt-[2px]"><?= e($material['duration']) ?></p>
                            </div>
                            <?php if ($isDoneMat): ?>
                            <span class="fd-chip fd-chip-success shrink-0">Selesai</span>
                            <?php elseif ($isCurrentMat): ?>
                            <span class="fd-chip fd-chip-info shrink-0">Buka</span>
                            <?php else: ?>
                            <span class="material-symbols-outlined text-[#737373] text-[18px] shrink-0">lock</span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>



                </div>
                <?php endforeach; ?>
                <?php include APP_ROOT . '/includes/components/DX_LMS_M_Pagination.php'; ?>
                <?php else: ?>
                <div class="text-center py-8 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">hourglass_empty</span>
                    <p class="font-body-md">Sesi belum tersedia untuk course ini.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
































































































            elseif ($tab === 'riwayat'): ?>
        <!-- Content Area: Riwayat (FoodDash Borderless Cards) -->
        <div class="p-[18px] sm:p-[24px] flex flex-col gap-[18px] mt-[6px] pb-8 animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="fd-h2 text-[#171717]">Riwayat Aktivitas &amp; Evaluasi</h2>
                    <p class="fd-body text-[#525252]">Log komprehensif perjalanan pembelajaran Anda di course ini.</p>
                </div>
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
                                    'icon' =>
                                        $mat['type'] === 'video'
                                            ? 'play_circle'
                                            : ($mat['type'] === 'pdf'
                                                ? 'picture_as_pdf'
                                                : 'article'),
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
                        'icon' => 'quiz',
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
                        'icon' => 'military_tech',
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
                <button type="button" onclick="toggleAccordion('riwayat-aktivitas')" class="w-full flex items-center justify-between p-[18px] text-left hover:bg-[#FAFAFA] transition-colors duration-200 gap-[12px]">
                    <div class="flex flex-wrap items-center gap-[10px] min-w-0">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-[10px] bg-[#DBEAFE] text-[#2563EB]">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">timeline</span>
                        </span>
                        <span class="fd-h3 text-[#171717]">1. Aktivitas</span>
                        <span class="fd-chip fd-chip-neutral text-[11px]"><?= count($activities) ?> aktivitas</span>
                    </div>
                    <span class="material-symbols-outlined text-[#737373] transition-transform duration-300 shrink-0 rotate-[-90deg]" id="riwayat-aktivitas-chevron">expand_more</span>
                </button>
                <div id="riwayat-aktivitas" class="accordion-body" style="display: none;">
                    <div class="px-[18px] pb-[18px] border-t border-[#F5F5F5] pt-[18px]">
                        <?php if (empty($activities)): ?>
                        <div class="text-center py-6 text-[#737373]">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">history</span>
                            <p class="fd-body">Belum ada aktivitas yang tercatat.</p>
                        </div>
                        <?php else: ?>
                        <div class="relative pl-6" id="riwayat-aktivitas-list">
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
                        <button type="button" onclick="toggleShowAll('riwayat-aktivitas-list', this)" class="fd-btn fd-btn-secondary fd-btn-md mt-[18px] w-full rounded-full">
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
                <button type="button" onclick="toggleAccordion('riwayat-qa')" class="w-full flex items-center justify-between p-[18px] text-left hover:bg-[#FAFAFA] transition-colors duration-200 gap-[12px]">
                    <div class="flex flex-wrap items-center gap-[10px] min-w-0">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-[10px] bg-[#FEF2F2] text-[#DC2626]">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                        </span>
                        <span class="fd-h3 text-[#171717]">2. Quiz &amp; Assessment</span>
                        <?php $qaCount = count($assessments['quiz'] ?? []) + count($assessments['final'] ?? []); ?>
                        <span class="fd-chip fd-chip-neutral text-[11px]"><?= $qaCount ?> percobaan</span>
                    </div>
                    <span class="material-symbols-outlined text-[#737373] transition-transform duration-300 shrink-0 rotate-[-90deg]" id="riwayat-qa-chevron">expand_more</span>
                </button>
                <div id="riwayat-qa" class="accordion-body" style="display: none;">
                    <div class="px-[18px] pb-[18px] border-t border-[#F5F5F5] pt-[18px]">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-[18px]">
                            <!-- Quiz Course -->
                            <div class="rounded-[12px] bg-[#FAFAFA] p-[16px]">
                                <h4 class="fd-h4 text-[#171717] mb-[12px] pb-[8px] border-b border-[#E5E5E5]">Quiz Course</h4>
                                <?php if (empty($assessments['quiz'])): ?>
                                <p class="text-[13px] text-[#737373] py-2">Belum ada riwayat Quiz Course.</p>
                                <?php else: ?>
                                <div class="flex flex-col gap-[8px]" id="riwayat-quiz-list">
                                    <?php foreach ($assessments['quiz'] as $qIdx => $h): ?>
                                    <div class="bg-white rounded-[10px] p-[12px] flex justify-between items-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] riwayat-item <?= $qIdx >= 5 ? 'riwayat-hidden' : '' ?>" <?= $qIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                        <div class="flex items-center gap-[10px]">
                                            <div class="w-8 h-8 bg-[#FEF2F2] text-[#DC2626] rounded-full flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]">quiz</span>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-[#171717]">Percobaan <?= e((string) $h['attempt']) ?></p>
                                                <p class="fd-mono text-[#737373] text-[11px] mt-0.5"><?= date('d M Y, H:i', strtotime($h['date'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-3">
                                            <?php
                                            $_s = $h['score'] ?? $h['nilai'];
                                            $_sc = $_s >= 75 ? 'text-[#16A34A]' : ($_s >= 50 ? 'text-[#D97706]' : 'text-[#EF4444]');
                                            ?>
                                            <span class="fd-mono text-[16px] font-bold <?= $_sc ?>"><?= e((string) $_s) ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['quiz']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('riwayat-quiz-list', this)" class="fd-btn fd-btn-secondary fd-btn-sm mt-[12px] w-full rounded-full">
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
                                <div class="flex flex-col gap-[8px]" id="riwayat-final-list">
                                    <?php foreach ($assessments['final'] as $fIdx => $h): ?>
                                    <?php
                                    $val = $h['score'] ?? $h['nilai'];
                                    $isPassed = $val >= 75;
                                    $_sc2 = $val >= 75 ? 'text-[#16A34A]' : ($val >= 50 ? 'text-[#D97706]' : 'text-[#EF4444]');
                                    ?>
                                    <div class="bg-white rounded-[10px] p-[12px] flex justify-between items-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] riwayat-item <?= $fIdx >= 5 ? 'riwayat-hidden' : '' ?>" <?= $fIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                        <div class="flex items-center gap-[10px]">
                                            <div class="w-8 h-8 <?= $isPassed ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEE2E2] text-[#EF4444]' ?> rounded-full flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-[16px]"><?= $isPassed ? 'check_circle' : 'cancel' ?></span>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-[#171717]">Percobaan <?= e((string) $h['attempt']) ?></p>
                                                <p class="fd-mono text-[#737373] text-[11px] mt-0.5"><?= date('d M Y, H:i', strtotime($h['date'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-2">
                                            <span class="fd-chip <?= $isPassed ? 'fd-chip-success' : 'fd-chip-error' ?> text-[10px]"><?= $isPassed ? 'Lulus' : 'Remedial' ?></span>
                                            <span class="fd-mono text-[16px] font-bold <?= $_sc2 ?>"><?= e((string) $val) ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['final']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('riwayat-final-list', this)" class="fd-btn fd-btn-secondary fd-btn-sm mt-[12px] w-full rounded-full">
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

        <?php elseif ($tab === 'quiz'): ?>
        <!-- Content Area: Quiz (FoodDash Borderless White Cards & Semantic Status Chips) -->
        <div class="p-[18px] flex flex-col gap-[12px] mt-[6px]">
            <?php if (!empty($course['quiz'])): ?>
            <?php $quiz = $course['quiz']; ?>
            <?php $quizQuestionCount = (int) ($quiz['question_count'] ?? count($quiz['questions'] ?? [])); ?>
            <!-- Quiz -->
            <div class="fd-list rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden w-full">
                <a href="<?= $quiz['status'] !== 'terkunci'
                    ? '?route=/student/quiz&id=' . e($courseId) . '&type=quiz'
                    : 'javascript:void(0)' ?>" class="fd-list-row w-full text-left <?= $quiz['status'] === 'terkunci'
    ? 'opacity-50 cursor-not-allowed'
    : 'is-selected' ?>">
                    <div class="fd-list-thumb <?= $quiz['status'] === 'terkunci'
                        ? 'bg-[#F5F5F5] text-[#737373]'
                        : ($quiz['status'] === 'selesai' ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF2F2] text-[#DC2626]') ?>">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $quiz[
                            'status'
                        ] === 'selesai'
                            ? '1'
                            : '0' ?>;">quiz</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-[8px] flex-wrap">
                            <h3 class="fd-h4 text-[#171717]"><?= e($quiz['title'] ?? 'Quiz Course') ?></h3>
                            <?php if ($quiz['status'] === 'terkunci'): ?>
                            <span class="fd-chip fd-chip-error">Terkunci</span>
                            <?php elseif ($quiz['status'] === 'selesai'): ?>
                            <span class="fd-chip fd-chip-success">Selesai</span>
                            <?php else: ?>
                            <span class="fd-chip fd-chip-promo">Siap Dikerjakan</span>
                            <?php endif; ?>
                        </div>
                        <p class="fd-mono text-[13px] text-[#525252] mt-[3px]">
                            <?= $quizQuestionCount > 0 ? $quizQuestionCount . ' Pertanyaan' : (!empty($quiz['has_quiz']) ? 'Soal belum tersedia' : 'Quiz belum dibuat') ?>
                            <?= $quiz['status'] === 'selesai' && $quiz['score'] !== null ? ' · Nilai: ' . e($quiz['score']) : '' ?>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <?php if ($quiz['status'] === 'selesai'): ?>
                        <span class="material-symbols-outlined text-[#16A34A]">check_circle</span>
                        <?php elseif ($quiz['status'] === 'terkunci'): ?>
                        <span class="material-symbols-outlined text-[#737373]">lock</span>
                        <?php else: ?>
                        <span class="fd-btn fd-btn-primary fd-btn-sm rounded-full">Mulai <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
            <?php $fa = $course['final_assessment']; ?>
            <?php
            $isQuizCompleted = empty($course['quiz']['has_quiz']) || $course['quiz']['status'] === 'selesai';
            $faStatus =
                $all_sessions_completed && $isQuizCompleted
                    ? ($fa['status'] === 'terkunci'
                        ? 'belum_dimulai'
                        : $fa['status'])
                    : 'terkunci';
            $finalScore = $fa['nilai'] ?? null;
            $isPassed = $finalScore !== null && $finalScore >= 75;
            ?>
            <!-- Final Assessment -->
            <div class="fd-list rounded-[12px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden w-full <?= $faStatus ===
            'terkunci'
                ? 'opacity-65'
                : '' ?>">
                <a href="<?= $faStatus !== 'terkunci' && !$isPassed
                    ? '?route=/student/quiz&id=' . e($courseId) . '&type=final_assessment'
                    : 'javascript:void(0)' ?>" class="fd-list-row w-full text-left <?= $faStatus === 'terkunci' || $isPassed
    ? 'cursor-not-allowed'
    : 'is-selected' ?>">
                    <div class="fd-list-thumb <?= $faStatus === 'terkunci'
                        ? 'bg-[#F5F5F5] text-[#737373]'
                        : ($isPassed ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF2F2] text-[#DC2626]') ?>">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $isPassed
                            ? '1'
                            : '0' ?>;">military_tech</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-[8px] flex-wrap">
                            <h3 class="fd-h4 text-[#171717]">Final Assessment</h3>
                            <?php if ($faStatus === 'terkunci'): ?>
                            <span class="fd-chip fd-chip-error">Terkunci</span>
                            <?php elseif ($isPassed): ?>
                            <span class="fd-chip fd-chip-success">Lulus</span>
                            <?php elseif ($finalScore !== null && $finalScore < 75): ?>
                            <span class="fd-chip fd-chip-error">Remedial</span>
                            <?php else: ?>
                            <span class="fd-chip fd-chip-info">Siap Dikerjakan</span>
                            <?php endif; ?>
                        </div>
                        <p class="fd-mono text-[13px] text-[#525252] mt-[3px]">
                            <?= count($fa['questions']) > 0 ? count($fa['questions']) . ' Pertanyaan' : 'Soal belum tersedia' ?>
                            <?= $finalScore !== null ? ' · Nilai akhir: ' . e($finalScore) : '' ?>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <?php if ($isPassed): ?>
                        <span class="material-symbols-outlined text-[#16A34A]">verified</span>
                        <?php elseif ($faStatus === 'terkunci'): ?>
                        <span class="material-symbols-outlined text-[#737373]">lock</span>
                        <?php else: ?>
                        <span class="fd-btn fd-btn-primary fd-btn-sm rounded-full">Mulai <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (empty($course['quiz']) && empty($course['final_assessment'])): ?>
            <div class="fd-card rounded-[12px] bg-white p-[24px] text-center text-[#525252]">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">assignment</span>
                <p class="fd-body">Tidak ada quiz atau assessment untuk course ini.</p>
            </div>
            <?php endif; ?>

        </div>
        <?php elseif ($tab === 'forum'): ?>
        <?php
        $isCoursePassed = $course['status'] === 'selesai';
        $forumsMateri = $course['forums']['materi'] ?? [];
        $forumQuiz = $course['forums']['quiz'] ?? [];
        $quizStatus = $course['quiz']['status'] ?? 'terkunci';
        ?>
        <!-- Content Area: Forum -->
        <div class="p-[18px] space-y-[18px] mt-[6px]">

            <div class="fd-card bg-white rounded-[12px] p-[18px] sm:p-[24px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex items-start gap-[12px]">
                <div class="w-12 h-12 rounded-[12px] bg-[#DBEAFE] text-[#2563EB] flex shrink-0 items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">forum</span>
                </div>
                <div>
                    <h3 class="fd-h3 text-[#171717] mb-[3px]">Forum Diskusi Course</h3>
                    <p class="fd-body text-[#525252]">
                        Pilih kategori diskusi di bawah ini untuk melihat topik, bertanya, atau berdiskusi dengan peserta lain dan pengajar.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-[18px]">
                <!-- Session Forums -->
                <?php if (!empty($course['sessions'])): ?>
                <?php foreach ($course['sessions'] as $session):
                    $sessId = $session['id'];
                    $isLocked = false;
                ?>
                <a href="<?= $isLocked
                    ? '#'
                    : '?route=/student/forum/category&course_id=' .
                        urlencode($course['id']) .
                        '&category_type=materi&session_id=' .
                        urlencode($sessId) ?>" class="fd-card fd-card-interactive block rounded-[12px] bg-white p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] group relative overflow-hidden <?= $isLocked ? 'opacity-70 cursor-not-allowed' : '' ?>">
                    <div class="flex items-start justify-between gap-[12px]">
                        <div class="min-w-0">
                            <h4 class="fd-h4 text-[#171717] mb-[6px] flex items-center gap-[8px] group-hover:text-[#DC2626] transition-colors">
                                <span class="material-symbols-outlined text-[20px] <?= $isLocked ? 'text-[#737373]' : 'text-[#DC2626]' ?>">auto_stories</span>
                                <span class="truncate">Forum <?= e($session['title']) ?></span>
                            </h4>
                            <p class="fd-body text-[#525252] mb-[12px] line-clamp-2">Diskusi materi dan tugas terkait sesi ini.</p>
                            <span class="fd-chip fd-chip-neutral text-[11px]">Forum sesi</span>
                        </div>
                        <div class="shrink-0 w-10 h-10 rounded-full <?= $isLocked ? 'bg-[#F5F5F5] text-[#737373]' : 'bg-[#FEF2F2] text-[#DC2626] group-hover:bg-[#DC2626] group-hover:text-white' ?> flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[20px]"><?= $isLocked ? 'lock' : 'arrow_forward' ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

                <div class="fd-card rounded-[12px] bg-[#FAFAFA] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.06)]">
                    <h4 class="fd-h4 text-[#171717]">Diskusi per sesi</h4>
                    <p class="mt-[6px] fd-body text-[#525252]">Setiap ruang diskusi mengikuti sesi course dan dapat dibuka atau ditutup oleh Admin atau Teacher.</p>
                </div>
            </div>
        </div>

        </div>

        <?php elseif ($tab === 'capaian'): ?>
        <!-- Content Area: Capaian Pembelajaran -->
        <?php
        $fa = $course['final_assessment'] ?? ['nilai' => 0];
        $nilaiFa = $fa['nilai'] ?? 0;
        $isFaLulus = $nilaiFa >= 75;
        $progress = (int) ($course['progress'] ?? 0);
        $is100 = $progress === 100;
        $isCoursePassed = $isFaLulus && $is100;

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
            <div class="fd-card rounded-[12px] p-[18px] sm:p-[24px] flex items-center gap-[14px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] <?= $isCoursePassed
                ? 'bg-[#DCFCE7] text-[#15803D]'
                : 'bg-white text-[#171717]' ?>">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 <?= $isCoursePassed
                    ? 'bg-[#16A34A] text-white'
                    : 'bg-[#FEF2F2] text-[#DC2626]' ?>">
                    <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;"><?= $isCoursePassed
                        ? 'emoji_events'
                        : 'pending_actions' ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="fd-h3 font-bold mb-[2px]">
                        <?= $isCoursePassed ? 'Selamat! Anda Lulus.' : 'Sedang Berjalan...' ?>
                    </h3>
                    <p class="fd-body <?= $isCoursePassed ? 'text-[#15803D]' : 'text-[#525252]' ?>">
                        <?= $isCoursePassed
                            ? 'Anda telah menyelesaikan course ini dengan baik.'
                            : 'Selesaikan materi dan assessment untuk meraih sertifikat.' ?>
                    </p>
                </div>
            </div>

            <!-- Kriteria Kelulusan Checklist -->
            <section class="flex flex-col gap-[12px]">
                <h3 class="fd-h4 text-[#171717]">Kriteria Kelulusan</h3>
                <div class="fd-card bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden">
                    <div class="p-[16px] flex items-center gap-[12px] border-b border-[#F5F5F5]">
                        <span class="material-symbols-outlined <?= $is100 ? 'text-[#16A34A]' : 'text-[#A3A3A3]' ?>" style="font-variation-settings: 'FILL' <?= $is100 ? '1' : '0' ?>;">
                            <?= $is100 ? 'check_circle' : 'radio_button_unchecked' ?>
                        </span>
                        <div class="flex-1">
                            <p class="text-[14px] font-semibold text-[#171717]">Menyelesaikan Semua Materi (100%)</p>
                            <p class="text-[12px] text-[#525252] mt-0.5">Progress saat ini: <strong class="fd-mono text-[#171717]"><?= $progress ?>%</strong></p>
                        </div>
                    </div>
                    <div class="p-[16px] flex items-center gap-[12px]">
                        <span class="material-symbols-outlined <?= $isFaLulus ? 'text-[#16A34A]' : 'text-[#A3A3A3]' ?>" style="font-variation-settings: 'FILL' <?= $isFaLulus ? '1' : '0' ?>;">
                            <?= $isFaLulus ? 'check_circle' : 'radio_button_unchecked' ?>
                        </span>
                        <div class="flex-1">
                            <p class="text-[14px] font-semibold text-[#171717]">Lulus Final Assessment (Min. Skor 75)</p>
                            <p class="text-[12px] text-[#525252] mt-0.5">Skor saat ini: <strong class="fd-mono text-[#171717]"><?= e((string) $nilaiFa) ?></strong></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary stats -->
            <section class="flex flex-col gap-[12px]">
                <h3 class="fd-h4 text-[#171717]">Statistik Capaian</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-[18px]">
                    <!-- Score Card (Gauge) -->
                    <div class="fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col items-center justify-center gap-[8px] text-center">
                        <div class="relative w-24 h-16 flex items-end justify-center overflow-hidden">
                            <svg class="w-24 h-24 absolute top-0 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#F5F5F5]" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="50, 100"></path>
                                <path stroke="<?= $scoreStrokeColor ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="4" stroke-dasharray="<?= $nilaiFa / 2 ?>, 100" stroke-linecap="round"></path>
                            </svg>
                            <div class="absolute bottom-1 flex flex-col items-center justify-center">
                                <span class="fd-mono text-[22px] <?= $scoreColorClass ?> font-bold leading-none"><?= e((string) $nilaiFa) ?></span>
                            </div>
                        </div>
                        <div class="mt-1">
                            <p class="text-[14px] font-semibold text-[#171717]">Skor Akhir</p>
                            <p class="text-[12px] <?= $scoreColorClass ?> mt-0.5 font-semibold"><?= $isFaLulus ? 'Lulus' : 'Belum Lulus' ?></p>
                        </div>
                    </div>

                    <!-- Attendance Card -->
                    <div class="fd-card bg-white rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col items-center justify-center gap-[8px] text-center">
                        <div class="w-14 h-14 rounded-full <?= $is100 ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#F5F5F5] text-[#737373]' ?> flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' <?= $is100 ? '1' : '0' ?>;"><?= $is100 ? 'task_alt' : 'incomplete_circle' ?></span>
                        </div>
                        <div>
                            <p class="fd-mono text-[22px] text-[#171717] font-bold"><?= $progress ?>%</p>
                            <p class="text-[12px] <?= $is100 ? 'text-[#16A34A]' : 'text-[#737373]' ?> mt-0.5 font-semibold"><?= $is100 ? 'Tuntas' : 'Belum Tuntas' ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sertifikat Section -->
            <section class="flex flex-col gap-[12px]">
                <h3 class="fd-h4 text-[#171717]">Sertifikat Digital</h3>
                <div class="fd-card bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden relative <?= !$isCoursePassed ? 'opacity-85' : '' ?>">
                    <div class="h-44 fd-cover-fallback relative overflow-hidden">
                        <div class="flex flex-col items-center justify-center text-white/90 gap-[8px]">
                            <span class="fd-cover-icon-badge">
                                <span class="material-symbols-outlined" style="font-size:32px; font-variation-settings:'FILL' 1;"><?= $isCoursePassed ? 'workspace_premium' : 'lock' ?></span>
                            </span>
                            <span class="text-[12px] font-medium text-white/80"><?= $isCoursePassed ? 'Sertifikat Siap Diunduh' : 'Terkunci Hingga Lulus' ?></span>
                        </div>
                        <?php if ($isCoursePassed): ?>
                        <div class="fd-chip fd-chip-success absolute top-[12px] right-[12px] shadow-sm">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">verified</span> Valid
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-[18px] bg-white flex flex-col gap-[12px]">
                        <div>
                            <p class="fd-h4 text-[#171717]">Certificate of Completion</p>
                            <p class="fd-body text-[#525252] mt-1">
                                <?= $isCoursePassed
                                    ? 'Diterbitkan secara otomatis oleh sistem LMS. Dapat digunakan sebagai bukti kelulusan.'
                                    : 'Selesaikan seluruh materi (100%) dan capai nilai Final Assessment minimal 75 untuk membuka sertifikat.' ?>
                            </p>
                        </div>
                        <button type="button" class="fd-btn <?= $isCoursePassed ? 'fd-btn-primary' : 'fd-btn-secondary' ?> fd-btn-md w-full rounded-full" <?= !$isCoursePassed ? 'disabled' : '' ?>>
                            <span class="material-symbols-outlined text-[18px]"><?= $isCoursePassed ? 'download' : 'lock' ?></span>
                            <span><?= $isCoursePassed ? 'Unduh Sertifikat (PDF)' : 'Sertifikat Terkunci' ?></span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
        <?php endif; ?>
    </main>

    <!-- BottomNavBar Shared Component -->
    <?php
    $active_tab = 'course';
    include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('courseMaterialSearch');
            const sessions = Array.from(document.querySelectorAll('.course-session-card'));
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

        // Save scroll position saat klik tab
        document.addEventListener('DOMContentLoaded', function() {
            var tabLinks = document.querySelectorAll('nav a[href*="tab="]');

            tabLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    sessionStorage.setItem('courseDetailScrollPos', window.scrollY.toString());
                });
            });
        });

        function toggleAccordion(id) {
            const body = document.getElementById(id);
            const chevron = document.getElementById(id + '-chevron');
            if (!body || !chevron) return;

            if (body.style.display === 'none') {
                body.style.display = 'block';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                body.style.display = 'none';
                chevron.style.transform = 'rotate(-90deg)';
            }
        }

        function switchForumTab(tabName) {
            const btnMateri = document.getElementById('btn-forum-materi');
            const btnQuiz = document.getElementById('btn-forum-quiz');
            const containerMateri = document.getElementById('container-forum-materi');
            const containerQuiz = document.getElementById('container-forum-quiz');

            if (tabName === 'materi') {
                btnMateri.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 bg-secondary text-on-secondary shadow-md transform scale-100';
                btnQuiz.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-highest';
                containerMateri.style.display = 'flex';
                containerQuiz.style.display = 'none';
            } else {
                btnQuiz.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 bg-secondary text-on-secondary shadow-md transform scale-100';
                btnMateri.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-highest';
                containerQuiz.style.display = 'flex';
                containerMateri.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('forum-search-input');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    const term = e.target.value.toLowerCase();
                    const forumSections = document.querySelectorAll('.forum-section-wrapper');

                    forumSections.forEach(section => {
                        const threads = section.querySelectorAll('.forum-thread-item');
                        let hasVisibleThread = false;

                        threads.forEach(thread => {
                            const title = thread.querySelector('.forum-title').textContent.toLowerCase();
                            if (term === '' || title.includes(term)) {
                                thread.style.display = 'flex';
                                hasVisibleThread = true;
                            } else {
                                thread.style.display = 'none';
                            }
                        });

                        const emptyState = section.querySelector('.empty-state');
                        if (emptyState) {
                            if (term === '') {
                                emptyState.style.display = threads.length === 0 ? 'block' : 'none';
                            } else {
                                emptyState.style.display = hasVisibleThread ? 'none' : 'block';
                            }
                        }
                    });
                });
            }
        });

        function toggleShowAll(listId, btn) {
            const list = document.getElementById(listId);
            if (!list) return;

            const hiddenItems = list.querySelectorAll('.riwayat-hidden');
            const isShowingAll = btn.getAttribute('data-showing') === 'true';

            if (isShowingAll) {
                hiddenItems.forEach(item => {
                    item.style.display = 'none';
                });
                btn.setAttribute('data-showing', 'false');
                btn.querySelector('.material-symbols-outlined').textContent = 'unfold_more';

                if (!btn.hasAttribute('data-original-text')) {
                    btn.setAttribute('data-original-text', btn.querySelector('.btn-text').textContent);
                }
                btn.querySelector('.btn-text').textContent = btn.getAttribute('data-original-text');
            } else {
                hiddenItems.forEach(item => {
                    item.style.display = ''; /** Mengembalikan konfigurasi properti tampilan CSS ke setelan semula. */
                });
                btn.setAttribute('data-showing', 'true');
                btn.querySelector('.material-symbols-outlined').textContent = 'unfold_less';

                if (!btn.hasAttribute('data-original-text')) {
                    btn.setAttribute('data-original-text', btn.querySelector('.btn-text').textContent);
                }
                btn.querySelector('.btn-text').textContent = 'Sembunyikan';
            }
        }
    </script>
</body>

</html>
