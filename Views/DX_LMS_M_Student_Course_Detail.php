<?php
// $course, $tab, dan $all_sessions_completed dilemparkan dari controller via extract($data)
$tab = $tab ?? $_GET['tab'] ?? 'materi';
$courseId = $course['id'] ?? 'CRS001';
$all_sessions_completed = $all_sessions_completed ?? false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'PT. LMS - Course Detail';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        .animate-fade-in-up { animation: fadeInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeInUp { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar Shared Component -->
    <?php 
    $header_title = 'Detail Course';
    $show_back = true;
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include 'includes/components/DX_LMS_M_Header.php'; 
    ?>

    <main class="dls-main flex-1 w-full bg-surface-container-low">
        <!-- Hero Banner -->
        <div class="relative w-full h-56 md:h-72 bg-surface-variant">
            <?php if (!empty($course['thumbnail'])): ?>
            <img alt="<?= e($course['title']) ?> banner" class="absolute inset-0 w-full h-full object-cover" src="<?= e($course['thumbnail']) ?>">
            <?php else: ?>
            <div class="absolute inset-0 w-full h-full bg-secondary-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[80px] text-on-secondary-container opacity-40"><?= e($course['thumbnail_icon'] ?? 'school') ?></span>
            </div>
            <?php endif; ?>
            <div class="absolute inset-0 bg-black/50 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-full p-container-margin text-on-primary">
                <h2 class="font-headline-lg text-headline-lg text-on-primary mb-4 shadow-sm"><?= e($course['title']) ?></h2>
                <div class="w-full">
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-label-md text-label-md text-surface-container-high">Progress Pembelajaran</span>
                        <span class="font-headline-sm text-headline-sm"><?= (int)$course['progress'] ?>%</span>
                    </div>
                    <div class="w-full bg-surface-container-highest/40 h-2 rounded-full overflow-hidden">
                        <div class="bg-tertiary-container h-full rounded-full" style="width: <?= (int)$course['progress'] ?>%;"></div>
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
                    <a href="?route=/student/courses/filters&filter=<?= urlencode($tag) ?>" class="px-3 py-1 bg-surface-container-lowest text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all duration-200 font-label-sm rounded-full cursor-pointer border border-outline-variant active:scale-95 flex items-center">
                        <?= e($tag) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs -->
        <div class="w-full bg-surface border-b border-surface-variant sticky top-14 z-30 shadow-sm">
            <nav class="flex overflow-x-auto hide-scrollbar px-container-margin">
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=materi" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'materi' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Materi</span>
                    <?php if ($tab === 'materi'): ?>
                        <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=quiz" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'quiz' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Quiz</span>
                    <?php if ($tab === 'quiz'): ?>
                        <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=forum" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'forum' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Forum</span>
                    <?php if ($tab === 'forum'): ?>
                        <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=capaian" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'capaian' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
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
                                    <span class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Sesi <?= $index + 1 ?></span>
                                    <h3 class="font-headline-sm text-headline-sm text-on-surface mt-1"><?= e($session['title']) ?></h3>
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
                            <?php $preTest = $session['pre_test']; ?>
                            <div class="flex flex-col border-b border-surface-variant/30 pb-3 mb-3">
                                <a href="<?= $preTest['status'] !== 'terkunci' ? '?route=/student/quiz&id='.e($courseId).'&session_id='.e($session['id']).'&type=pre_test' : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-2 <?= $preTest['status'] === 'terkunci' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container/50 transition-colors' ?>">
                                    <div class="w-10 h-10 rounded-lg <?= $preTest['status'] === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-primary-container/20 text-primary' ?> flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $preTest['status'] === 'selesai' ? '1' : '0' ?>;">assignment</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-body-md text-on-surface">Pre-Test</p>
                                        <p class="font-label-sm text-on-surface-variant">
                                            <?= count($preTest['questions']) ?> Soal 
                                            <?= $preTest['status'] === 'selesai' ? "• Skor Tertinggi: {$preTest['score']}" : '' ?>
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
                                <?php if (!empty($preTest['history'])): ?>
                                    <div class="pl-14 pt-1">
                                        <p class="font-label-sm text-on-surface-variant mb-1 font-bold">Riwayat Percobaan:</p>
                                        <ul class="space-y-1">
                                            <?php foreach($preTest['history'] as $h): ?>
                                                <li class="font-label-sm text-on-surface-variant flex justify-between">
                                                    <span>Percobaan <?= $h['attempt'] ?> (<?= date('d M Y H:i', strtotime($h['date'])) ?>)</span>
                                                    <span class="font-bold">Skor: <?= $h['score'] ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Materials -->
                            <div class="pl-4 border-l-2 border-surface-variant/50 ml-5 my-2 flex flex-col gap-1 relative">
                                <!-- Dot connector top -->
                                <div class="absolute -top-3 -left-[5px] w-2 h-2 rounded-full bg-surface-variant/50"></div>
                                <?php foreach ($session['materials'] as $mIndex => $material): ?>
                                    <a href="<?= $material['status'] !== 'terkunci' ? '?route=/student/learning&id='.e($courseId).'&session_id='.e($session['id']).'&material_index='.$mIndex : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-2 <?= $material['status'] === 'terkunci' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container/50 transition-colors' ?> rounded-lg px-2 -ml-2">
                                        <div class="w-8 h-8 rounded-lg <?= $material['status'] === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-secondary-container/20 text-secondary' ?> flex items-center justify-center shrink-0">
                                            <?php 
                                                $icon = 'article';
                                                if ($material['type'] === 'video') $icon = 'play_circle';
                                                if ($material['type'] === 'pdf') $icon = 'picture_as_pdf';
                                                if ($material['type'] === 'image') $icon = 'image';
                                            ?>
                                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' <?= $material['status'] === 'selesai' ? '1' : '0' ?>;"><?= $icon ?></span>
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
                            <?php $postTest = $session['post_test']; ?>
                            <div class="flex flex-col border-t border-surface-variant/30 mt-3 pt-3">
                                <a href="<?= $postTest['status'] !== 'terkunci' ? '?route=/student/quiz&id='.e($courseId).'&session_id='.e($session['id']).'&type=post_test' : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-2 <?= $postTest['status'] === 'terkunci' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container/50 transition-colors' ?>">
                                    <div class="w-10 h-10 rounded-lg <?= $postTest['status'] === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-primary-container/20 text-primary' ?> flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $postTest['status'] === 'selesai' ? '1' : '0' ?>;">assignment_turned_in</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-body-md text-on-surface">Post-Test</p>
                                        <p class="font-label-sm text-on-surface-variant">
                                            <?= count($postTest['questions']) ?> Soal
                                            <?= $postTest['status'] === 'selesai' ? "• Skor Tertinggi: {$postTest['score']}" : '' ?>
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
                                <?php if (!empty($postTest['history'])): ?>
                                    <div class="pl-14 pt-1">
                                        <p class="font-label-sm text-on-surface-variant mb-1 font-bold">Riwayat Percobaan:</p>
                                        <ul class="space-y-1">
                                            <?php foreach($postTest['history'] as $h): ?>
                                                <li class="font-label-sm text-on-surface-variant flex justify-between">
                                                    <span>Percobaan <?= $h['attempt'] ?> (<?= date('d M Y H:i', strtotime($h['date'])) ?>)</span>
                                                    <span class="font-bold">Skor: <?= $h['score'] ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

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

        <?php elseif ($tab === 'quiz'): ?>
        <!-- Content Area: Quiz -->
        <div class="p-container-margin flex flex-col gap-stack-md mt-4">
            <?php if (!empty($course['quiz'])): ?>
                <?php $quiz = $course['quiz']; ?>
                <!-- Quiz -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col gap-3 w-full">
                    <a href="<?= $quiz['status'] !== 'terkunci' ? '?route=/student/quiz&id='.e($courseId).'&type=quiz' : 'javascript:void(0)' ?>" class="flex items-center gap-4 text-left w-full <?= $quiz['status'] === 'terkunci' ? 'opacity-70 cursor-not-allowed' : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
                        <div class="<?= $quiz['status'] === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-primary-container/20 text-primary' ?> rounded-lg p-3 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $quiz['status'] === 'selesai' ? '1' : '0' ?>;">quiz</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Quiz Course</h3>
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
                                <div class="bg-secondary text-on-secondary rounded-full w-8 h-8 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php if (!empty($quiz['history'])): ?>
                        <div class="pl-16 pt-2 border-t border-surface-variant/30">
                            <p class="font-label-sm text-on-surface-variant mb-2 font-bold">Riwayat Percobaan:</p>
                            <ul class="space-y-1.5">
                                <?php foreach($quiz['history'] as $h): ?>
                                    <li class="font-label-sm text-on-surface-variant flex justify-between">
                                        <span>Percobaan <?= $h['attempt'] ?> (<?= date('d M Y H:i', strtotime($h['date'])) ?>)</span>
                                        <span class="font-bold">Skor: <?= $h['score'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
                <?php $fa = $course['final_assessment']; ?>
                <?php 
                    $isQuizCompleted = empty($course['quiz']) || ($course['quiz']['status'] === 'selesai');
                    $faStatus = ($all_sessions_completed && $isQuizCompleted) ? ($fa['status'] === 'terkunci' ? 'belum_dimulai' : $fa['status']) : 'terkunci';
                    $isPassed = $fa['nilai'] !== null && $fa['nilai'] >= 75;
                ?>
                <!-- Final Assessment -->
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col gap-3 w-full <?= ($faStatus === 'terkunci') ? 'opacity-70' : '' ?>">
                    <a href="<?= $faStatus !== 'terkunci' && !$isPassed ? '?route=/student/quiz&id='.e($courseId).'&type=final_assessment' : 'javascript:void(0)' ?>" class="flex items-center gap-4 text-left w-full <?= ($faStatus === 'terkunci' || $isPassed) ? 'cursor-not-allowed' : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
                        <div class="<?= $faStatus === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-tertiary-container/20 text-tertiary' ?> rounded-lg p-3 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $isPassed ? '1' : '0' ?>;">military_tech</span>
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
                    <?php if (!empty($fa['history'])): ?>
                        <div class="pl-16 pt-2 border-t border-surface-variant/30">
                            <p class="font-label-sm text-on-surface-variant mb-2 font-bold">Riwayat Percobaan:</p>
                            <ul class="space-y-1.5">
                                <?php foreach($fa['history'] as $h): ?>
                                    <li class="font-label-sm text-on-surface-variant flex justify-between">
                                        <span>Percobaan <?= $h['attempt'] ?> (<?= date('d M Y H:i', strtotime($h['date'])) ?>)</span>
                                        <span class="font-bold">Nilai: <?= $h['nilai'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
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
        <!-- Content Area: Forum -->
        <div class="p-container-margin space-y-stack-md mt-4">
            <div class="flex items-center justify-between px-2">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Forum Diskusi</h2>
                <span class="font-label-sm text-label-sm text-on-surface-variant"><?= isset($course['forums']) ? count($course['forums']) : 0 ?> Forum Tersedia</span>
            </div>
            
            <?php if (!empty($course['forums'])): ?>
                <?php foreach ($course['forums'] as $forum): ?>
                <!-- Forum Card -->
                <a href="?route=/student/forum/thread&id=<?= e($forum['id']) ?>" class="w-full text-left bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant hover:bg-surface-container-low transition-all duration-200 active:scale-[0.98] group flex gap-4 block">
                    <div class="w-12 h-12 rounded-full bg-secondary-container/20 flex items-center justify-center shrink-0 group-hover:bg-secondary-container/40 transition-colors">
                        <span class="material-symbols-outlined text-secondary">forum</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-label-md text-label-md text-on-surface truncate pr-2"><?= e($forum['title']) ?></h3>
                            <span class="font-label-sm text-label-sm text-on-surface-variant shrink-0"><?= e($forum['last_active']) ?></span>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mb-3">
                            Topik diskusi resmi untuk course ini. Silakan berpartisipasi dan bertanya kepada instruktur atau rekan Anda.
                        </p>
                        <div class="flex items-center gap-3">
                            <span class="font-label-sm text-label-sm text-secondary font-medium bg-secondary-fixed/50 px-2 py-0.5 rounded-full"><?= e($forum['replies']) ?> Balasan</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">forum</span>
                    <p class="font-body-md">Belum ada forum diskusi untuk course ini.</p>
                </div>
            <?php endif; ?>
            
            <!-- Create New Discussion FAB (Inline) -->
            <div class="pt-4 flex justify-center pb-8">
                <a href="?route=/student/forum/new" class="flex items-center gap-2 bg-surface border border-secondary text-secondary px-6 py-3 rounded-full font-label-md text-label-md hover:bg-secondary-fixed/30 active:scale-95 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">add_comment</span>
                    Mulai Topik Baru
                </a>
            </div>
        </div>

        <?php elseif ($tab === 'capaian'): ?>
        <!-- Content Area: Capaian Pembelajaran -->
        <div class="p-container-margin flex flex-col gap-stack-lg mt-4 pb-8 animate-fade-in-up">
            <!-- Summary stats -->
            <section class="flex flex-col gap-stack-md">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Capaian Sementara</h3>
                <div class="grid grid-cols-2 gap-stack-md">
                    <!-- Score Card -->
                    <?php 
                        $fa = $course['final_assessment'] ?? ['nilai' => 0];
                        $nilaiFa = $fa['nilai'] ?? 0;
                        $isFaLulus = $nilaiFa >= 75;
                    ?>
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                        <div class="relative w-20 h-20 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-surface-container-high" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                                <path class="<?= $isFaLulus ? 'text-tertiary' : 'text-error' ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="<?= $nilaiFa ?>, 100" stroke-width="3"></path>
                            </svg>
                            <div class="absolute flex flex-col items-center justify-center">
                                <span class="font-headline-md text-headline-md text-on-surface font-bold"><?= $nilaiFa ?></span>
                            </div>
                        </div>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface font-bold">Skor Final Assessment</p>
                            <p class="font-label-sm text-label-sm <?= $isFaLulus ? 'text-tertiary' : 'text-error' ?> mt-1 font-medium"><?= $isFaLulus ? 'Lulus' : 'Belum Lulus' ?></p>
                        </div>
                    </div>

                    <!-- Attendance Card -->
                    <?php 
                        $progress = (int)($course['progress'] ?? 0);
                        $is100 = $progress === 100;
                    ?>
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                        <div class="w-16 h-16 rounded-full <?= $is100 ? 'bg-tertiary-container text-on-tertiary-container' : 'bg-surface-variant text-on-surface-variant' ?> flex items-center justify-center mb-2">
                            <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' <?= $is100 ? '1' : '0' ?>;"><?= $is100 ? 'task_alt' : 'incomplete_circle' ?></span>
                        </div>
                        <div>
                            <p class="font-headline-sm text-headline-sm text-on-surface font-bold"><?= $progress ?>% Tuntas</p>
                            <p class="font-label-sm text-label-sm <?= $is100 ? 'text-tertiary' : 'text-on-surface-variant' ?> mt-1 font-medium"><?= $is100 ? 'Seluruh materi diselesaikan' : 'Materi belum diselesaikan semua' ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sertifikat Section -->
            <section class="flex flex-col gap-stack-md mt-stack-sm">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Sertifikat</h3>
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant overflow-hidden relative group <?= !$is100 || !$isFaLulus ? 'opacity-60' : '' ?>">
                    <!-- Certificate Thumbnail -->
                    <div class="h-40 bg-surface-variant relative overflow-hidden flex items-center justify-center">
                        <?php if ($is100 && $isFaLulus): ?>
                            <img alt="Sertifikat Dasar Keselamatan Kerja" class="w-full h-full object-cover opacity-80 mix-blend-multiply" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEiwMCnV-HX6w_f8JunDkUk9s6x2HogDwemAHq1-GmjG9JyL-k3jIWk6rvo1ASGhQiw_b6BzibQbFoY0MSTnaQ0MxGzyn_Ut7HOMl3z8YMDzsjaXc_HGvy6ekLvm8YNJbBJUhHDEomR8Cm14d2Wp5SGU2IXMPzwoT-N5N5iSIf8Z1vQTZbdO9A2l_ce_ae-nKN-_uyJoVWKH3092sWO0sIQC-xkeT8chFxvFdL3cdpHZbJM4d0aDkUJGOYPUNhUPjCXicBdF2ZzY">
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-container-highest to-transparent opacity-60"></div>
                        <?php endif; ?>
                        <span class="material-symbols-outlined absolute text-on-surface/40 text-[64px]">workspace_premium</span>
                    </div>
                    <!-- Action Area -->
                    <div class="p-stack-md bg-surface-container-lowest flex flex-col gap-stack-md">
                        <div>
                            <p class="font-label-md text-label-md text-on-surface font-bold">Sertifikat Kelulusan</p>
                            <p class="font-body-md text-body-md text-on-surface-variant">
                                <?= ($is100 && $isFaLulus) ? 'Diterbitkan oleh LMS' : 'Syarat belum terpenuhi (Progress 100% & Lulus Assessment)' ?>
                            </p>
                        </div>
                        <button class="w-full <?= ($is100 && $isFaLulus) ? 'bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant' : 'bg-surface-variant text-on-surface-variant cursor-not-allowed' ?> font-label-md text-label-md h-[44px] rounded-lg flex items-center justify-center gap-2 shadow-sm transition-colors" <?= (!$is100 || !$isFaLulus) ? 'disabled' : '' ?>>
                            <?php if ($is100 && $isFaLulus): ?>
                                <span class="material-symbols-outlined">download</span>
                                Unduh Sertifikat
                            <?php else: ?>
                                <span class="material-symbols-outlined">lock</span>
                                Terkunci
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
    include 'includes/components/DX_LMS_M_Bottom_Nav.php'; 
    ?>

    <script>
        // Mempertahankan posisi scroll saat berpindah tab
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('courseDetailScrollPos', window.scrollY);
        });

        window.addEventListener('load', () => {
            const scrollPos = sessionStorage.getItem('courseDetailScrollPos');
            if (scrollPos !== null) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('courseDetailScrollPos');
            }
        });
    </script>
</body>
</html>
