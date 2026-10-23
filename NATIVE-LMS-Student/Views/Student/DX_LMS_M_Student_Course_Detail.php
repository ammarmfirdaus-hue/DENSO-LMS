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
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
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

<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Detail Course';
    $show_back = true;
    $back_url = '?route=/student/courses';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <main class="dls-main flex-1 w-full bg-surface-container-low">
        <!-- Hero Banner -->
        <div class="relative w-full h-56 md:h-72 bg-surface-variant">
            <?php if (!empty($course['thumbnail'])): ?>
            <img alt="<?= e(
                $course['title'],
            ) ?> banner" class="absolute inset-0 w-full h-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e(
     $course['thumbnail'],
 ) ?>">
            <?php endif; ?>
            <?php
            $iconGradients = [
                'build' => 'from-orange-400 to-orange-600',
                'health_and_safety' => 'from-green-400 to-emerald-600',
                'cleaning_services' => 'from-teal-400 to-cyan-600',
                'precision_manufacturing' => 'from-blue-400 to-blue-600',
                'trending_up' => 'from-violet-400 to-purple-600',
                'verified' => 'from-sky-400 to-indigo-600',
                'forum' => 'from-pink-400 to-rose-600',
                'groups' => 'from-amber-400 to-yellow-600',
                'gavel' => 'from-red-400 to-rose-700',
                'eco' => 'from-lime-400 to-green-600',
                'school' => 'from-primary/70 to-primary',
            ];
            $icon = $course['thumbnail_icon'] ?? 'school';
            $grad = $iconGradients[$icon] ?? 'from-primary/70 to-primary';
            $showFallback = empty($course['thumbnail']) ? 'flex' : 'none';
            ?>
            <div class="absolute inset-0 w-full h-full bg-gradient-to-br <?= $grad ?> items-center justify-center<?= !empty(
     $course['thumbnail']
 )
     ? ' hidden'
     : '' ?>" style="display:<?= $showFallback ?>">
                <span class="material-symbols-outlined text-white/60" style="font-size: 80px; font-variation-settings: 'FILL' 1;"><?= e(
                    $icon,
                ) ?></span>
            </div>
            <div class="absolute inset-0 bg-black/50 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-full p-container-margin text-on-primary">
                <h2 class="font-headline-lg text-headline-lg text-on-primary mb-4 shadow-sm"><?= e(
                    $course['title'],
                ) ?></h2>
                <div class="w-full">
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-label-md text-label-md text-surface-container-high">Progress Pembelajaran</span>
                        <span class="font-headline-sm text-headline-sm"><?= (int) $course['progress'] ?>%</span>
                    </div>
                    <div class="w-full bg-surface-container-highest/40 h-2 rounded-full overflow-hidden">
                        <div class="bg-tertiary-container h-full rounded-full" style="width: <?= (int) $course[
                            'progress'
                        ] ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description and Tags Section -->
        <div class="w-full bg-surface px-container-margin py-5 border-b border-surface-variant shadow-sm relative z-20 flex flex-col gap-4">
            <?php if (!empty($course['description'])): ?>
            <div class="flex flex-col gap-2">
                <h3 class="font-label-lg text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-[20px]" style="font-variation-settings: 'FILL' 1;">info</span> Deskripsi Course
                </h3>
                <p class="font-body-md text-on-surface-variant leading-relaxed text-justify">
                    <?= nl2br(e($course['description'])) ?>
                </p>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['filters'])): ?>
            <div class="flex items-center flex-wrap gap-2 pt-1">
                <span class="font-label-md text-on-surface font-semibold mr-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">sell</span> Tags:
                </span>
                <?php foreach ($course['filters'] as $tag): ?>
                <a href="?route=/student/courses/filters&filter=<?= urlencode(
                    $tag,
                ) ?>" class="px-3 py-1 bg-surface-container-lowest text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all duration-200 font-label-sm rounded-full cursor-pointer border border-outline-variant active:scale-95 flex items-center">
                    <?= e($tag) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs -->
        <div class="w-full bg-surface border-b border-surface-variant sticky top-14 z-30 shadow-sm">
            <nav class="flex overflow-x-auto hide-scrollbar px-container-margin">
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=materi" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'materi'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Materi</span>
                    <?php if ($tab === 'materi'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=quiz" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'quiz'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Quiz</span>
                    <?php if ($tab === 'quiz'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=forum" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'forum'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Forum</span>
                    <?php if ($tab === 'forum'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=riwayat" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'riwayat'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Riwayat</span>
                    <?php if ($tab === 'riwayat'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=capaian" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'capaian'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Capaian</span>
                    <?php if ($tab === 'capaian'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
            </nav>
        </div>

        <?php if ($tab === 'materi'): ?>
        <!-- Content Area: Materi -->
        <div class="p-container-margin flex flex-col gap-stack-md mt-4">
            <div class="flex flex-col gap-stack-lg">
                <?php if (!empty($course['sessions'])): ?>
                <?php foreach ($course['sessions'] as $index => $session): ?>
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant/50">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Sesi <?= $index +
                                1 ?></span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface mt-1"><?= e(
                                $session['title'],
                            ) ?></h3>
                        </div>
                        <?php if ($session['status'] === 'selesai'): ?>
                        <div class="flex items-center gap-1 text-tertiary px-2 py-1 bg-tertiary-container/10 rounded-full">
                            <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span class="font-label-sm text-label-sm">Selesai</span>
                        </div>
                        <?php elseif ($session['status'] === 'sedang_dipelajari'): ?>
                        <div class="flex items-center gap-1 text-on-surface-variant px-2 py-1 bg-surface-container-high rounded-full">
                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                            <span class="font-label-sm text-label-sm">In Progress</span>
                        </div>
                        <?php else: ?>
                        <div class="flex items-center gap-1 text-on-surface-variant px-2 py-1 bg-surface-container-high rounded-full opacity-70">
                            <span class="material-symbols-outlined text-[16px]">lock</span>
                            <span class="font-label-sm text-label-sm">Terkunci</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pre Test -->
                    <?php if (isset($session['pre_test'])): ?>
                    <?php $preTest = $session['pre_test']; ?>
                    <div class="flex flex-col border-b border-surface-variant/30 pb-3 mb-3">
                        <a href="<?= $preTest['status'] !== 'terkunci'
                            ? '?route=/student/quiz&id=' .
                                e($courseId) .
                                '&session_id=' .
                                e($session['id']) .
                                '&type=pre_test'
                            : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-2 <?= $preTest['status'] ===
'terkunci'
    ? 'opacity-50 cursor-not-allowed'
    : 'hover:bg-surface-container/50 transition-colors' ?>">
                            <div class="w-10 h-10 rounded-lg <?= $preTest['status'] === 'terkunci'
                                ? 'bg-surface-variant text-on-surface-variant'
                                : 'bg-primary-container/20 text-primary' ?> flex items-center justify-center shrink-0 overflow-hidden">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $preTest[
                                    'status'
                                ] === 'selesai'
                                    ? '1'
                                    : '0' ?>;">assignment</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-body-md text-on-surface">Pre-Test</p>
                                <p class="font-label-sm text-on-surface-variant">
                                    <?= count($preTest['questions']) ?> Soal
                                    <?= $preTest['status'] === 'selesai'
                                        ? "• Skor Tertinggi: {$preTest['score']}"
                                        : '' ?>
                                </p>
                            </div>
                            <?php if ($preTest['status'] === 'selesai'): ?>
                            <span class="material-symbols-outlined text-tertiary">check_circle</span>
                            <?php elseif ($preTest['status'] === 'terkunci'): ?>
                            <span class="material-symbols-outlined text-on-surface-variant">lock</span>
                            <?php else: ?>
                            <span class="material-symbols-outlined text-secondary">play_arrow</span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Materials -->
                    <div class="pl-4 border-l-2 border-surface-variant/50 ml-5 my-2 flex flex-col gap-1 relative">
                        <!-- Dot connector top -->
                        <div class="absolute -top-3 -left-[5px] w-2 h-2 rounded-full bg-surface-variant/50"></div>
                        <?php foreach ($session['materials'] as $mIndex => $material): ?>
                        <a href="<?= $material['status'] !== 'terkunci'
                            ? '?route=/student/learning&id=' .
                                e($courseId) .
                                '&session_id=' .
                                e($session['id']) .
                                '&material_index=' .
                                $mIndex
                            : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-2 <?= $material['status'] ===
'terkunci'
    ? 'opacity-50 cursor-not-allowed'
    : 'hover:bg-surface-container/50 transition-colors' ?> rounded-lg px-2 -ml-2">
                            <div class="w-8 h-8 rounded-lg <?= $material['status'] === 'terkunci'
                                ? 'bg-surface-variant text-on-surface-variant'
                                : 'bg-secondary-container/20 text-secondary' ?> flex items-center justify-center shrink-0 overflow-hidden">
                                <?php
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
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' <?= $material[
                                    'status'
                                ] === 'selesai'
                                    ? '1'
                                    : '0' ?>;"><?= $icon ?></span>
                            </div>
                            <div class="flex-1">
                                <p class="font-body-sm text-on-surface"><?= e($material['title']) ?></p>
                                <p class="font-label-sm text-on-surface-variant"><?= e($material['duration']) ?></p>
                            </div>
                            <?php if ($material['status'] === 'selesai'): ?>
                            <span class="material-symbols-outlined text-tertiary text-sm">check_circle</span>
                            <?php elseif ($material['status'] === 'terkunci'): ?>
                            <span class="material-symbols-outlined text-on-surface-variant text-sm">lock</span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                        <!-- Dot connector bottom -->
                        <div class="absolute -bottom-3 -left-[5px] w-2 h-2 rounded-full bg-surface-variant/50"></div>
                    </div>

                    <!-- Post Test -->
                    <?php if (isset($session['post_test'])): ?>
                    <?php $postTest = $session['post_test']; ?>
                    <div class="flex flex-col border-t border-surface-variant/30 mt-3 pt-3">
                        <a href="<?= $postTest['status'] !== 'terkunci'
                            ? '?route=/student/quiz&id=' .
                                e($courseId) .
                                '&session_id=' .
                                e($session['id']) .
                                '&type=post_test'
                            : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-2 <?= $postTest['status'] ===
'terkunci'
    ? 'opacity-50 cursor-not-allowed'
    : 'hover:bg-surface-container/50 transition-colors' ?>">
                            <div class="w-10 h-10 rounded-lg <?= $postTest['status'] === 'terkunci'
                                ? 'bg-surface-variant text-on-surface-variant'
                                : 'bg-primary-container/20 text-primary' ?> flex items-center justify-center shrink-0 overflow-hidden">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $postTest[
                                    'status'
                                ] === 'selesai'
                                    ? '1'
                                    : '0' ?>;">assignment_turned_in</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-body-md text-on-surface">Post-Test</p>
                                <p class="font-label-sm text-on-surface-variant">
                                    <?= count($postTest['questions']) ?> Soal
                                    <?= $postTest['status'] === 'selesai'
                                        ? "• Skor Tertinggi: {$postTest['score']}"
                                        : '' ?>
                                </p>
                            </div>
                            <?php if ($postTest['status'] === 'selesai'): ?>
                            <span class="material-symbols-outlined text-tertiary">check_circle</span>
                            <?php elseif ($postTest['status'] === 'terkunci'): ?>
                            <span class="material-symbols-outlined text-on-surface-variant">lock</span>
                            <?php else: ?>
                            <span class="material-symbols-outlined text-secondary">play_arrow</span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
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
        <!-- Content Area: Riwayat -->
        <div class="p-container-margin flex flex-col gap-stack-lg mt-4 pb-8 animate-fade-in-up">
            <div class="flex items-center justify-between px-2">
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Riwayat Aktivitas & Evaluasi</h2>
                    <p class="font-body-md text-on-surface-variant">Log komprehensif perjalanan pembelajaran Anda di course ini.</p>
                </div>
            </div>

            <?php
            $activities = [];
            $pre_tests = [];
            $post_tests = [];
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
                                    'color' => 'secondary',
                                ];
                            }
                        }
                    }

                    if (!empty($session['pre_test']['history'])) {
                        $pre_tests[] = [
                            'session_title' => $session['title'],
                            'history' => $session['pre_test']['history'],
                        ];
                        foreach ($session['pre_test']['history'] as $h) {
                            $activities[] = [
                                'title' => 'Mengerjakan Pre-Test',
                                'desc' => $session['title'] . ' (Percobaan ' . $h['attempt'] . ')',
                                'date' => $h['date'],
                                'icon' => 'assignment',
                                'color' => 'primary',
                            ];
                        }
                    }

                    if (!empty($session['post_test']['history'])) {
                        $post_tests[] = [
                            'session_title' => $session['title'],
                            'history' => $session['post_test']['history'],
                        ];
                        foreach ($session['post_test']['history'] as $h) {
                            $activities[] = [
                                'title' => 'Mengerjakan Post-Test',
                                'desc' => $session['title'] . ' (Percobaan ' . $h['attempt'] . ')',
                                'date' => $h['date'],
                                'icon' => 'assignment_turned_in',
                                'color' => 'tertiary',
                            ];
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
                        'color' => 'error',
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
                        'color' => 'error',
                    ];
                }
            }

            usort($activities, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            ?>

            <!-- 1. Aktivitas Student -->
            <section class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/60 overflow-hidden">
                <button type="button" onclick="toggleAccordion('riwayat-aktivitas')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary shrink-0" style="font-variation-settings: 'FILL' 1;">timeline</span>
                            <span class="font-label-lg sm:font-headline-sm text-on-surface font-bold">1. Aktivitas</span>
                        </div>
                        <span class="font-label-sm text-on-surface-variant font-normal ml-6 sm:ml-0">(<?= count(
                            $activities,
                        ) ?> aktivitas)</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="riwayat-aktivitas-chevron">expand_more</span>
                </button>
                <div id="riwayat-aktivitas" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <?php if (empty($activities)): ?>
                        <div class="text-center py-6 text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">history</span>
                            <p class="font-body-md">Belum ada aktivitas yang tercatat.</p>
                        </div>
                        <?php else: ?>
                        <div class="relative pl-6" id="riwayat-aktivitas-list">
                            <div class="absolute left-[11px] top-4 bottom-4 w-[2px] bg-surface-variant/50"></div>
                            <?php foreach ($activities as $idx => $act): ?>
                            <div class="relative mb-6 last:mb-0 riwayat-item <?= $idx >= 5
                                ? 'riwayat-hidden'
                                : '' ?>" <?= $idx >= 5 ? 'style="display:none;"' : '' ?>>
                                <div class="absolute -left-6 top-1 w-[10px] h-[10px] rounded-full bg-<?= $act[
                                    'color'
                                ] ?> ring-4 ring-surface-container-lowest"></div>
                                <div class="ml-4 flex flex-col">
                                    <p class="font-label-md font-bold text-on-surface leading-tight"><?= e(
                                        $act['title'],
                                    ) ?></p>
                                    <p class="font-body-sm text-on-surface-variant"><?= e($act['desc']) ?></p>
                                    <span class="font-label-sm text-on-surface-variant flex items-center gap-1 mt-1 opacity-80">
                                        <span class="material-symbols-outlined text-[12px]">schedule</span>
                                        <?= date('d M Y, H:i', strtotime($act['date'])) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($activities) > 5): ?>
                        <button type="button" onclick="toggleShowAll('riwayat-aktivitas-list', this)" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-outline-variant/50 text-secondary font-label-md font-bold hover:bg-secondary-container/10 transition-colors duration-200">
                            <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                            <span class="btn-text">Tampilkan Semua (<?= count($activities) - 5 ?> lainnya)</span>
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 2. Riwayat Pengulangan Pre Test -->
            <section class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/60 overflow-hidden">
                <button type="button" onclick="toggleAccordion('riwayat-pretest')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary shrink-0" style="font-variation-settings: 'FILL' 1;">assignment</span>
                            <span class="font-label-lg sm:font-headline-sm text-on-surface font-bold">2. Riwayat Pre-Test</span>
                        </div>
                        <?php $preTestCount = array_sum(
                            array_map(function ($pt) {
                                return count($pt['history']);
                            }, $pre_tests),
                        ); ?>
                        <span class="font-label-sm text-on-surface-variant font-normal ml-6 sm:ml-0">(<?= $preTestCount ?> percobaan)</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="riwayat-pretest-chevron">expand_more</span>
                </button>
                <div id="riwayat-pretest" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <?php if (empty($pre_tests)): ?>
                        <div class="text-center py-6 text-on-surface-variant">
                            <p class="font-body-md">Belum ada riwayat pengerjaan Pre-Test.</p>
                        </div>
                        <?php else: ?>
                        <div class="flex flex-col gap-4" id="riwayat-pretest-list">
                            <?php
                            $preIdx = 0;
                            foreach ($pre_tests as $pt): ?>
                            <div class="border border-outline-variant/40 rounded-lg p-4 bg-surface-container-lowest/50 riwayat-item <?= $preIdx >=
                            5
                                ? 'riwayat-hidden'
                                : '' ?>" <?= $preIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                <h4 class="font-label-md font-bold text-on-surface mb-3"><?= e(
                                    $pt['session_title'],
                                ) ?></h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php foreach ($pt['history'] as $h): ?>
                                    <div class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30">
                                        <div>
                                            <p class="font-label-sm font-bold text-on-surface">Percobaan <?= $h[
                                                'attempt'
                                            ] ?></p>
                                            <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                'd M Y, H:i',
                                                strtotime($h['date']),
                                            ) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <?php
                                            $_s = $h['score'] ?? $h['nilai'];
                                            $_sc =
                                                $_s >= 75
                                                    ? 'text-tertiary'
                                                    : ($_s >= 50
                                                        ? 'text-[#D4A017]'
                                                        : 'text-error');
                                            ?>
                                            <span class="font-headline-sm font-bold <?= $_sc ?>"><?= $_s ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $preIdx++;endforeach;
                            ?>
                        </div>
                        <?php if (count($pre_tests) > 5): ?>
                        <button type="button" onclick="toggleShowAll('riwayat-pretest-list', this)" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-outline-variant/50 text-primary font-label-md font-bold hover:bg-primary-container/10 transition-colors duration-200">
                            <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                            <span class="btn-text">Tampilkan Semua (<?= count($pre_tests) - 5 ?> lainnya)</span>
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 3. Riwayat Pengulangan Post Test -->
            <section class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/60 overflow-hidden">
                <button type="button" onclick="toggleAccordion('riwayat-posttest')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-tertiary shrink-0" style="font-variation-settings: 'FILL' 1;">assignment_turned_in</span>
                            <span class="font-label-lg sm:font-headline-sm text-on-surface font-bold">3. Riwayat Post-Test</span>
                        </div>
                        <?php $postTestCount = array_sum(
                            array_map(function ($pt) {
                                return count($pt['history']);
                            }, $post_tests),
                        ); ?>
                        <span class="font-label-sm text-on-surface-variant font-normal ml-6 sm:ml-0">(<?= $postTestCount ?> percobaan)</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="riwayat-posttest-chevron">expand_more</span>
                </button>
                <div id="riwayat-posttest" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <?php if (empty($post_tests)): ?>
                        <div class="text-center py-6 text-on-surface-variant">
                            <p class="font-body-md">Belum ada riwayat pengerjaan Post-Test.</p>
                        </div>
                        <?php else: ?>
                        <div class="flex flex-col gap-4" id="riwayat-posttest-list">
                            <?php
                            $postIdx = 0;
                            foreach ($post_tests as $pt): ?>
                            <div class="border border-outline-variant/40 rounded-lg p-4 bg-surface-container-lowest/50 riwayat-item <?= $postIdx >=
                            5
                                ? 'riwayat-hidden'
                                : '' ?>" <?= $postIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                <h4 class="font-label-md font-bold text-on-surface mb-3"><?= e(
                                    $pt['session_title'],
                                ) ?></h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php foreach ($pt['history'] as $h): ?>
                                    <div class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30">
                                        <div>
                                            <p class="font-label-sm font-bold text-on-surface">Percobaan <?= $h[
                                                'attempt'
                                            ] ?></p>
                                            <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                'd M Y, H:i',
                                                strtotime($h['date']),
                                            ) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <?php
                                            $_s = $h['score'] ?? $h['nilai'];
                                            $_sc =
                                                $_s >= 75
                                                    ? 'text-tertiary'
                                                    : ($_s >= 50
                                                        ? 'text-[#D4A017]'
                                                        : 'text-error');
                                            ?>
                                            <span class="font-headline-sm font-bold <?= $_sc ?>"><?= $_s ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $postIdx++;endforeach;
                            ?>
                        </div>
                        <?php if (count($post_tests) > 5): ?>
                        <button type="button" onclick="toggleShowAll('riwayat-posttest-list', this)" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-outline-variant/50 text-tertiary font-label-md font-bold hover:bg-tertiary-container/10 transition-colors duration-200">
                            <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                            <span class="btn-text">Tampilkan Semua (<?= count($post_tests) - 5 ?> lainnya)</span>
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 4. Riwayat Quiz dan Assessment -->
            <section class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/60 overflow-hidden">
                <button type="button" onclick="toggleAccordion('riwayat-qa')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-error shrink-0" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                            <span class="font-label-lg sm:font-headline-sm text-on-surface font-bold">4. Quiz &amp; Assessment</span>
                        </div>
                        <?php $qaCount = count($assessments['quiz'] ?? []) + count($assessments['final'] ?? []); ?>
                        <span class="font-label-sm text-on-surface-variant font-normal ml-6 sm:ml-0">(<?= $qaCount ?> percobaan)</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="riwayat-qa-chevron">expand_more</span>
                </button>
                <div id="riwayat-qa" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <div class="flex flex-col gap-6">
                            <!-- Quiz Course -->
                            <div class="border border-outline-variant/40 rounded-lg p-4 bg-surface-container-lowest/50">
                                <h4 class="font-label-md font-bold text-on-surface mb-3 border-b border-surface-variant/30 pb-2">Quiz Course</h4>
                                <?php if (empty($assessments['quiz'])): ?>
                                <p class="font-body-sm text-on-surface-variant py-2">Belum ada riwayat Quiz Course.</p>
                                <?php else: ?>
                                <div class="flex flex-col gap-2" id="riwayat-quiz-list">
                                    <?php foreach ($assessments['quiz'] as $qIdx => $h): ?>
                                    <div class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30 riwayat-item <?= $qIdx >=
                                    5
                                        ? 'riwayat-hidden'
                                        : '' ?>" <?= $qIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-error-container/20 text-error rounded-full flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[16px]">quiz</span>
                                            </div>
                                            <div>
                                                <p class="font-label-sm font-bold text-on-surface">Percobaan <?= $h[
                                                    'attempt'
                                                ] ?></p>
                                                <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                    'd M Y, H:i',
                                                    strtotime($h['date']),
                                                ) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-3">
                                            <?php
                                            $_s = $h['score'] ?? $h['nilai'];
                                            $_sc =
                                                $_s >= 75
                                                    ? 'text-tertiary'
                                                    : ($_s >= 50
                                                        ? 'text-[#D4A017]'
                                                        : 'text-error');
                                            ?>
                                            <span class="font-headline-sm font-bold <?= $_sc ?>"><?= $_s ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['quiz']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('riwayat-quiz-list', this)" class="mt-3 w-full flex items-center justify-center gap-2 py-2 rounded-lg border border-outline-variant/50 text-error font-label-md font-bold hover:bg-error-container/10 transition-colors duration-200">
                                    <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                                    <span class="btn-text">Tampilkan Semua (<?= count($assessments['quiz']) -
                                        5 ?> lainnya)</span>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Final Assessment -->
                            <div class="border border-outline-variant/40 rounded-lg p-4 bg-surface-container-lowest/50">
                                <h4 class="font-label-md font-bold text-on-surface mb-3 border-b border-surface-variant/30 pb-2">Final Assessment</h4>
                                <?php if (empty($assessments['final'])): ?>
                                <p class="font-body-sm text-on-surface-variant py-2">Belum ada riwayat Final Assessment.</p>
                                <?php else: ?>
                                <div class="flex flex-col gap-2" id="riwayat-final-list">
                                    <?php foreach ($assessments['final'] as $fIdx => $h): ?>
                                    <?php
                                    $val = $h['score'] ?? $h['nilai'];
                                    $isPassed = $val >= 75;
                                    ?>
                                    <div class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30 riwayat-item <?= $fIdx >=
                                    5
                                        ? 'riwayat-hidden'
                                        : '' ?>" <?= $fIdx >= 5 ? 'style="display:none;"' : '' ?>>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 <?= $isPassed
                                                ? 'bg-tertiary-container/30 text-tertiary'
                                                : 'bg-error-container/20 text-error' ?> rounded-full flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[16px]"><?= $isPassed
                                                    ? 'check_circle'
                                                    : 'cancel' ?></span>
                                            </div>
                                            <div>
                                                <p class="font-label-sm font-bold text-on-surface">Percobaan <?= $h[
                                                    'attempt'
                                                ] ?></p>
                                                <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                    'd M Y, H:i',
                                                    strtotime($h['date']),
                                                ) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-3">
                                            <?php $_sc2 =
                                                $val >= 75
                                                    ? 'text-tertiary'
                                                    : ($val >= 50
                                                        ? 'text-[#D4A017]'
                                                        : 'text-error'); ?>
                                            <?php if ($isPassed): ?>
                                            <span class="px-2 py-0.5 bg-tertiary-container/30 text-tertiary text-[10px] font-bold rounded-full uppercase tracking-wider">Lulus</span>
                                            <?php else: ?>
                                            <span class="px-2 py-0.5 bg-error-container/30 text-error text-[10px] font-bold rounded-full uppercase tracking-wider">Remedial</span>
                                            <?php endif; ?>
                                            <span class="font-headline-sm font-bold <?= $_sc2 ?>"><?= $val ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['final']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('riwayat-final-list', this)" class="mt-3 w-full flex items-center justify-center gap-2 py-2 rounded-lg border border-outline-variant/50 text-error font-label-md font-bold hover:bg-error-container/10 transition-colors duration-200">
                                    <span class="material-symbols-outlined text-[18px]">unfold_more</span>
                                    <span class="btn-text">Tampilkan Semua (<?= count($assessments['final']) -
                                        5 ?> lainnya)</span>
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
        <!-- Content Area: Quiz -->
        <div class="p-container-margin flex flex-col gap-stack-md mt-4">
            <?php if (!empty($course['quiz'])): ?>
            <?php $quiz = $course['quiz']; ?>
            <!-- Quiz -->
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col gap-3 w-full">
                <a href="<?= $quiz['status'] !== 'terkunci'
                    ? '?route=/student/quiz&id=' . e($courseId) . '&type=quiz'
                    : 'javascript:void(0)' ?>" class="flex items-center gap-4 text-left w-full <?= $quiz['status'] ===
'terkunci'
    ? 'opacity-70 cursor-not-allowed'
    : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
                    <div class="<?= $quiz['status'] === 'terkunci'
                        ? 'bg-surface-variant text-on-surface-variant'
                        : 'bg-primary-container/20 text-primary' ?> rounded-lg p-3 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $quiz[
                            'status'
                        ] === 'selesai'
                            ? '1'
                            : '0' ?>;">quiz</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Quiz Course</h3>
                            <?php if ($quiz['status'] === 'terkunci'): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-error/10 text-error rounded-full font-bold uppercase tracking-wider">Terkunci</span>
                            <?php endif; ?>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            <?= count($quiz['questions']) ?> Pertanyaan
                            <?= $quiz['status'] === 'selesai' ? "• Skor Tertinggi: {$quiz['score']}" : '' ?>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <?php if ($quiz['status'] === 'selesai'): ?>
                        <span class="material-symbols-outlined text-tertiary">check_circle</span>
                        <?php elseif ($quiz['status'] === 'terkunci'): ?>
                        <span class="material-symbols-outlined text-on-surface-variant">lock</span>
                        <?php else: ?>
                        <div class="bg-secondary text-on-secondary rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
            <?php $fa = $course['final_assessment']; ?>
            <?php
            $isQuizCompleted = empty($course['quiz']) || $course['quiz']['status'] === 'selesai';
            $faStatus =
                $all_sessions_completed && $isQuizCompleted
                    ? ($fa['status'] === 'terkunci'
                        ? 'belum_dimulai'
                        : $fa['status'])
                    : 'terkunci';
            $isPassed = $fa['nilai'] !== null && $fa['nilai'] >= 75;
            ?>
            <!-- Final Assessment -->
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col gap-3 w-full <?= $faStatus ===
            'terkunci'
                ? 'opacity-70'
                : '' ?>">
                <a href="<?= $faStatus !== 'terkunci' && !$isPassed
                    ? '?route=/student/quiz&id=' . e($courseId) . '&type=final_assessment'
                    : 'javascript:void(0)' ?>" class="flex items-center gap-4 text-left w-full <?= $faStatus ===
    'terkunci' || $isPassed
    ? 'cursor-not-allowed'
    : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
                    <div class="<?= $faStatus === 'terkunci'
                        ? 'bg-surface-variant text-on-surface-variant'
                        : 'bg-tertiary-container/20 text-tertiary' ?> rounded-lg p-3 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $isPassed
                            ? '1'
                            : '0' ?>;">military_tech</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Final Assessment</h3>
                            <?php if ($faStatus === 'terkunci'): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-error/10 text-error rounded-full font-bold uppercase tracking-wider">Terkunci</span>
                            <?php elseif ($isPassed): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-tertiary/10 text-tertiary rounded-full font-bold uppercase tracking-wider">Lulus</span>
                            <?php elseif ($fa['nilai'] !== null && $fa['nilai'] < 75): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-error/10 text-error rounded-full font-bold uppercase tracking-wider">Remedial</span>
                            <?php endif; ?>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            <?= count($fa['questions']) ?> Pertanyaan
                            <?= $fa['nilai'] !== null ? "• Nilai Akhir: {$fa['nilai']}" : '' ?>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <?php if ($isPassed): ?>
                        <span class="material-symbols-outlined text-tertiary">verified</span>
                        <?php elseif ($faStatus === 'terkunci'): ?>
                        <span class="material-symbols-outlined text-on-surface-variant">lock</span>
                        <?php else: ?>
                        <div class="bg-secondary text-on-secondary rounded-full w-8 h-8 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (empty($course['quiz']) && empty($course['final_assessment'])): ?>
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">assignment</span>
                <p class="font-body-md">Tidak ada quiz atau assessment untuk course ini.</p>
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
        <div class="p-container-margin space-y-stack-md mt-4">

            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 shadow-sm mb-6 flex items-start gap-4">
                <span class="material-symbols-outlined text-secondary text-4xl">forum</span>
                <div>
                    <h3 class="font-headline-sm font-bold text-on-surface mb-1">Forum Diskusi Course</h3>
                    <p class="font-body-md text-on-surface-variant leading-relaxed">
                        Pilih kategori diskusi di bawah ini untuk melihat topik, bertanya, atau berdiskusi dengan peserta lain dan pengajar.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Session Forums -->
                <?php if (!empty($course['sessions'])): ?>
                <?php foreach ($course['sessions'] as $session):

                    $sessId = $session['id'];
                    $forumData = $forumsMateri[$sessId] ?? ['threads' => []];
                    $threadCount = count($forumData['threads']);
                    $isLocked = $session['status'] !== 'selesai';
                    ?>
                <a href="<?= $isLocked
                    ? '#'
                    : '?route=/student/forum/category&course_id=' .
                        urlencode($course['id']) .
                        '&category_type=materi&session_id=' .
                        urlencode(
                            $sessId,
                        ) ?>" class="block bg-surface rounded-2xl border border-outline-variant/40 p-5 group relative overflow-hidden <?= $isLocked
    ? 'opacity-70 cursor-not-allowed'
    : 'hover:border-secondary/50 hover:shadow-md transition-all duration-300' ?>">

                    <?php if (!$isLocked): ?>
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-secondary/0 group-hover:bg-secondary transition-colors duration-300 rounded-l-2xl"></div>
                    <?php endif; ?>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="font-label-lg font-bold text-on-surface mb-1 <?= !$isLocked
                                ? 'group-hover:text-secondary transition-colors'
                                : '' ?> flex items-center gap-2">
                                <span class="material-symbols-outlined <?= $isLocked
                                    ? 'text-on-surface-variant'
                                    : 'text-secondary' ?> text-[20px]">auto_stories</span>
                                Forum <?= e($session['title']) ?>
                            </h4>
                            <p class="font-body-sm text-on-surface-variant mb-3 line-clamp-2">Diskusi materi dan tugas terkait sesi ini.</p>

                            <div class="flex items-center gap-2 font-label-sm text-on-surface-variant">
                                <span class="bg-surface-variant/50 px-2 py-0.5 rounded text-xs font-semibold"><?= $threadCount ?> Diskusi</span>
                            </div>
                        </div>

                        <div class="shrink-0 w-10 h-10 rounded-full <?= $isLocked
                            ? 'bg-surface-variant/30 text-on-surface-variant'
                            : 'bg-secondary-container/50 text-secondary group-hover:bg-secondary group-hover:text-on-secondary' ?> flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined"><?= $isLocked ? 'lock' : 'arrow_forward' ?></span>
                        </div>
                    </div>
                </a>
                <?php
                endforeach; ?>
                <?php endif; ?>

                <!-- Quiz Forum -->
                <?php
                $qLocked = $quizStatus !== 'selesai';
                $qThreads = $forumQuiz['threads'] ?? [];
                $qThreadCount = count($qThreads);
                ?>
                <a href="<?= $qLocked
                    ? '#'
                    : '?route=/student/forum/category&course_id=' .
                        urlencode($course['id']) .
                        '&category_type=quiz' ?>" class="block bg-surface rounded-2xl border border-outline-variant/40 p-5 group relative overflow-hidden <?= $qLocked
    ? 'opacity-70 cursor-not-allowed'
    : 'hover:border-error/50 hover:shadow-md transition-all duration-300' ?>">

                    <?php if (!$qLocked): ?>
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-error/0 group-hover:bg-error transition-colors duration-300 rounded-l-2xl"></div>
                    <?php endif; ?>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="font-label-lg font-bold text-on-surface mb-1 <?= !$qLocked
                                ? 'group-hover:text-error transition-colors'
                                : '' ?> flex items-center gap-2">
                                <span class="material-symbols-outlined <?= $qLocked
                                    ? 'text-on-surface-variant'
                                    : 'text-error' ?> text-[20px]">quiz</span>
                                Forum Quiz
                            </h4>
                            <p class="font-body-sm text-on-surface-variant mb-3 line-clamp-2">Pembahasan dan pertanyaan seputar quiz course.</p>

                            <div class="flex items-center gap-2 font-label-sm text-on-surface-variant">
                                <span class="bg-surface-variant/50 px-2 py-0.5 rounded text-xs font-semibold"><?= $qThreadCount ?> Diskusi</span>
                            </div>
                        </div>

                        <div class="shrink-0 w-10 h-10 rounded-full <?= $qLocked
                            ? 'bg-surface-variant/30 text-on-surface-variant'
                            : 'bg-error-container/50 text-error group-hover:bg-error group-hover:text-on-error' ?> flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined"><?= $qLocked ? 'lock' : 'arrow_forward' ?></span>
                        </div>
                    </div>
                </a>
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

        $scoreColorClass = 'text-error';
        $scoreStrokeColor = '#BA1A1A';
        if ($nilaiFa >= 75) {
            $scoreColorClass = 'text-tertiary';
            $scoreStrokeColor = '#4A6546';
        } elseif ($nilaiFa >= 50) {
            $scoreColorClass = 'text-[#D4A017]';
            $scoreStrokeColor = '#D4A017';
        }
        ?>
        <div class="p-container-margin flex flex-col gap-stack-lg mt-4 pb-8 animate-fade-in-up">

            <!-- Hero Banner Status -->
            <div class="rounded-xl p-5 flex items-center gap-4 <?= $isCoursePassed
                ? 'bg-tertiary-container text-on-tertiary-container'
                : 'bg-surface-variant text-on-surface-variant' ?> shadow-sm">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 <?= $isCoursePassed
                    ? 'bg-tertiary text-on-tertiary'
                    : 'bg-surface text-on-surface-variant' ?>">
                    <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;"><?= $isCoursePassed
                        ? 'emoji_events'
                        : 'pending_actions' ?></span>
                </div>
                <div class="flex-1">
                    <h3 class="font-headline-sm text-headline-sm font-bold mb-1">
                        <?= $isCoursePassed ? 'Selamat! Anda Lulus.' : 'Sedang Berjalan...' ?>
                    </h3>
                    <p class="font-body-sm text-body-sm opacity-90">
                        <?= $isCoursePassed
                            ? 'Anda telah menyelesaikan course ini dengan baik.'
                            : 'Selesaikan materi dan assessment untuk meraih sertifikat.' ?>
                    </p>
                </div>
            </div>

            <!-- Kriteria Kelulusan Checklist -->
            <section class="flex flex-col gap-stack-sm">
                <h3 class="font-label-lg text-label-lg text-on-surface font-bold">Kriteria Kelulusan</h3>
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                    <div class="p-4 flex items-center gap-3 border-b border-surface-variant">
                        <span class="material-symbols-outlined <?= $is100
                            ? 'text-tertiary'
                            : 'text-on-surface-variant' ?>" style="font-variation-settings: 'FILL' <?= $is100
    ? '1'
    : '0' ?>;">
                            <?= $is100 ? 'check_circle' : 'radio_button_unchecked' ?>
                        </span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md text-on-surface font-bold">Menyelesaikan Semua Materi (100%)</p>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Progress saat ini: <?= $progress ?>%</p>
                        </div>
                    </div>
                    <div class="p-4 flex items-center gap-3">
                        <span class="material-symbols-outlined <?= $isFaLulus
                            ? 'text-tertiary'
                            : 'text-on-surface-variant' ?>" style="font-variation-settings: 'FILL' <?= $isFaLulus
    ? '1'
    : '0' ?>;">
                            <?= $isFaLulus ? 'check_circle' : 'radio_button_unchecked' ?>
                        </span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md text-on-surface font-bold">Lulus Final Assessment (Min. Skor 75)</p>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Skor saat ini: <?= $nilaiFa ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary stats -->
            <section class="flex flex-col gap-stack-sm">
                <h3 class="font-label-lg text-label-lg text-on-surface font-bold">Statistik Capaian</h3>
                <div class="grid grid-cols-2 gap-stack-md">
                    <!-- Score Card (Gauge) -->
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                        <div class="relative w-24 h-16 flex items-end justify-center overflow-hidden">
                            <!-- Semi-circle SVG gauge -->
                            <svg class="w-24 h-24 absolute top-0 transform -rotate-90" viewBox="0 0 36 36">
                                <!-- Background track -->
                                <path class="text-surface-variant" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="50, 100"></path>
                                <!-- Progress track -->
                                <path stroke="<?= $scoreStrokeColor ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="4" stroke-dasharray="<?= $nilaiFa /
    2 ?>, 100" stroke-linecap="round"></path>
                            </svg>
                            <div class="absolute bottom-1 flex flex-col items-center justify-center">
                                <span class="font-headline-md text-headline-md <?= $scoreColorClass ?> font-bold leading-none"><?= $nilaiFa ?></span>
                            </div>
                        </div>
                        <div class="mt-1">
                            <p class="font-label-md text-label-md text-on-surface font-bold">Skor Akhir</p>
                            <p class="font-label-sm text-label-sm <?= $scoreColorClass ?> mt-0.5 font-medium"><?= $isFaLulus
     ? 'Lulus'
     : 'Belum Lulus' ?></p>
                        </div>
                    </div>

                    <!-- Attendance Card -->
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                        <div class="w-14 h-14 rounded-full <?= $is100
                            ? 'bg-tertiary-container text-on-tertiary-container'
                            : 'bg-surface-variant text-on-surface-variant' ?> flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' <?= $is100
                                ? '1'
                                : '0' ?>;"><?= $is100 ? 'task_alt' : 'incomplete_circle' ?></span>
                        </div>
                        <div>
                            <p class="font-headline-sm text-headline-sm text-on-surface font-bold"><?= $progress ?>%</p>
                            <p class="font-label-sm text-label-sm <?= $is100
                                ? 'text-tertiary'
                                : 'text-on-surface-variant' ?> mt-0.5 font-medium"><?= $is100
     ? 'Tuntas'
     : 'Belum Tuntas' ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sertifikat Section -->
            <section class="flex flex-col gap-stack-sm mt-stack-sm">
                <h3 class="font-label-lg text-label-lg text-on-surface font-bold">Sertifikat Digital</h3>
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant overflow-hidden relative group <?= !$isCoursePassed
                    ? 'opacity-80'
                    : '' ?>">
                    <!-- Certificate Thumbnail -->
                    <div class="h-44 bg-surface-variant relative overflow-hidden flex items-center justify-center">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                        <?php if ($isCoursePassed): ?>
                        <img alt="Sertifikat Dasar Keselamatan Kerja" class="w-full h-full object-cover mix-blend-multiply" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEiwMCnV-HX6w_f8JunDkUk9s6x2HogDwemAHq1-GmjG9JyL-k3jIWk6rvo1ASGhQiw_b6BzibQbFoY0MSTnaQ0MxGzyn_Ut7HOMl3z8YMDzsjaXc_HGvy6ekLvm8YNJbBJUhHDEomR8Cm14d2Wp5SGU2IXMPzwoT-N5N5iSIf8Z1vQTZbdO9A2l_ce_ae-nKN-_uyJoVWKH3092sWO0sIQC-xkeT8chFxvFdL3cdpHZbJM4d0aDkUJGOYPUNhUPjCXicBdF2ZzY">
                        <!-- Ribbon -->
                        <div class="absolute top-4 right-4 bg-tertiary text-on-tertiary text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">verified</span> Valid
                        </div>
                        <?php else: ?>
                        <img alt="Sertifikat Dasar Keselamatan Kerja" class="w-full h-full object-cover mix-blend-multiply blur-sm grayscale" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEiwMCnV-HX6w_f8JunDkUk9s6x2HogDwemAHq1-GmjG9JyL-k3jIWk6rvo1ASGhQiw_b6BzibQbFoY0MSTnaQ0MxGzyn_Ut7HOMl3z8YMDzsjaXc_HGvy6ekLvm8YNJbBJUhHDEomR8Cm14d2Wp5SGU2IXMPzwoT-N5N5iSIf8Z1vQTZbdO9A2l_ce_ae-nKN-_uyJoVWKH3092sWO0sIQC-xkeT8chFxvFdL3cdpHZbJM4d0aDkUJGOYPUNhUPjCXicBdF2ZzY">
                        <div class="absolute inset-0 bg-surface/40 flex items-center justify-center backdrop-blur-[2px]">
                            <div class="bg-surface text-on-surface rounded-full w-16 h-16 flex items-center justify-center shadow-md">
                                <span class="material-symbols-outlined text-[32px]">lock</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Action Area -->
                    <div class="p-stack-md bg-surface-container-lowest flex flex-col gap-stack-md relative z-10">
                        <div>
                            <p class="font-label-lg text-label-lg text-on-surface font-bold">Certificate of Completion</p>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                                <?= $isCoursePassed
                                    ? 'Diterbitkan secara otomatis oleh sistem LMS. Dapat digunakan sebagai bukti kelulusan.'
                                    : 'Selesaikan seluruh materi (100%) dan capai nilai Final Assessment minimal 75 untuk membuka sertifikat.' ?>
                            </p>
                        </div>
                        <button class="w-full <?= $isCoursePassed
                            ? 'bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant'
                            : 'bg-surface-variant text-on-surface-variant cursor-not-allowed' ?> font-label-md text-label-md h-[44px] rounded-lg flex items-center justify-center gap-2 shadow-sm transition-colors" <?= !$isCoursePassed
     ? 'disabled'
     : '' ?>>
                            <?php if ($isCoursePassed): ?>
                            <span class="material-symbols-outlined">download</span>
                            Unduh Sertifikat (PDF)
                            <?php else: ?>
                            <span class="material-symbols-outlined">lock</span>
                            Sertifikat Terkunci
                            <?php endif; ?>
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
    include 'includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <script>
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
