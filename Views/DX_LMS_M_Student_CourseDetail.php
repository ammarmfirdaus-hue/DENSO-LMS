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
<body class="bg-surface text-on-surface antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin h-14 sticky top-0 z-40">
        <a href="?route=/student/courses" class="p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-all duration-200 active:scale-95 text-on-surface-variant">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="font-headline-md text-headline-md font-bold text-primary tracking-tight">Detail Course</h1>
        <a href="?route=/student/notifikasi" class="p-2 -mr-2 rounded-full hover:bg-surface-container-low transition-all duration-200 active:scale-95 text-primary">
            <span class="material-symbols-outlined">notifications</span>
        </a>
    </header>

    <main class="flex-1 w-full bg-surface-container-low">
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

        <!-- Tags Section -->
        <?php if (!empty($course['filters'])): ?>
        <div class="w-full bg-surface px-container-margin py-4 border-b border-surface-variant flex items-center flex-wrap gap-2 shadow-sm relative z-20">
            <span class="font-label-md text-on-surface font-semibold mr-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">sell</span> Tags:
            </span>
            <?php foreach ($course['filters'] as $tag): ?>
                <a href="?route=/student/courses&filter=<?= urlencode($tag) ?>" class="px-3 py-1 bg-surface text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition-all duration-200 font-label-sm rounded-full cursor-pointer shadow-sm border border-outline-variant active:scale-95 flex items-center">
                    <?= e($tag) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="w-full bg-surface border-b border-surface-variant sticky top-14 z-30 shadow-sm">
            <nav class="flex overflow-x-auto hide-scrollbar px-container-margin">
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=materi" class="flex-1 py-4 px-2 relative text-center <?php echo $tab === 'materi' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md">Materi</span>
                    <?php if ($tab === 'materi'): ?>
                        <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=quiz" class="flex-1 py-4 px-2 relative text-center <?php echo $tab === 'quiz' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md">Quiz</span>
                    <?php if ($tab === 'quiz'): ?>
                        <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/course/detail&id=<?= e($courseId) ?>&tab=forum" class="flex-1 py-4 px-2 relative text-center <?php echo $tab === 'forum' ? 'text-secondary' : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md">Forum</span>
                    <?php if ($tab === 'forum'): ?>
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
                            <a href="<?= $preTest['status'] !== 'terkunci' ? '?route=/student/quiz&id='.e($courseId).'&session_id='.e($session['id']).'&type=pre_test' : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-3 border-t border-surface-variant/30 <?= $preTest['status'] === 'terkunci' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container/50 transition-colors' ?>">
                                <div class="w-10 h-10 rounded-lg <?= $preTest['status'] === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-primary-container/20 text-primary' ?> flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $preTest['status'] === 'selesai' ? '1' : '0' ?>;">assignment</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-body-md text-on-surface">Pre-Test</p>
                                    <p class="font-label-sm text-on-surface-variant">
                                        <?= count($preTest['questions']) ?> Soal 
                                        <?= $preTest['status'] === 'selesai' ? "• Skor: {$preTest['score']}" : '' ?>
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
                            <a href="<?= $postTest['status'] !== 'terkunci' ? '?route=/student/quiz&id='.e($courseId).'&session_id='.e($session['id']).'&type=post_test' : 'javascript:void(0)' ?>" class="flex items-center gap-4 py-3 border-t border-surface-variant/30 mt-3 <?= $postTest['status'] === 'terkunci' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container/50 transition-colors' ?>">
                                <div class="w-10 h-10 rounded-lg <?= $postTest['status'] === 'terkunci' ? 'bg-surface-variant text-on-surface-variant' : 'bg-primary-container/20 text-primary' ?> flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $postTest['status'] === 'selesai' ? '1' : '0' ?>;">assignment_turned_in</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-body-md text-on-surface">Post-Test</p>
                                    <p class="font-label-sm text-on-surface-variant">
                                        <?= count($postTest['questions']) ?> Soal
                                        <?= $postTest['status'] === 'selesai' ? "• Skor: {$postTest['score']}" : '' ?>
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
                <a href="<?= $quiz['status'] !== 'terkunci' ? '?route=/student/quiz&id='.e($courseId).'&type=quiz' : 'javascript:void(0)' ?>" class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex items-center gap-4 text-left w-full <?= $quiz['status'] === 'terkunci' ? 'opacity-70 cursor-not-allowed' : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
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
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
                <?php $fa = $course['final_assessment']; ?>
                <?php 
                    $faStatus = $all_sessions_completed ? ($fa['status'] === 'terkunci' ? 'belum_dimulai' : $fa['status']) : 'terkunci';
                    $isPassed = $fa['nilai'] >= 75;
                ?>
                <!-- Final Assessment -->
                <a href="<?= $faStatus !== 'terkunci' && !$isPassed ? '?route=/student/quiz&id='.e($courseId).'&type=final_assessment' : 'javascript:void(0)' ?>" class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex items-center gap-4 text-left w-full <?= ($faStatus === 'terkunci' || $isPassed) ? 'opacity-70 cursor-not-allowed' : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
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
        <?php endif; ?>
    </main>

    <!-- BottomNavBar -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] rounded-t-xl md:hidden border-t border-outline-variant/30">
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student">
            <span class="material-symbols-outlined" data-icon="home">home</span>
            <span class="font-label-sm text-label-sm mt-1">Beranda</span>
        </a>
        <a class="flex flex-col items-center justify-center text-secondary dark:text-secondary-fixed bg-secondary-container/20 rounded-xl px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/courses">
            <span class="material-symbols-outlined" data-icon="school" data-weight="fill">school</span>
            <span class="font-label-sm text-label-sm mt-1">Course</span>
        </a>
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/riwayat">
            <span class="material-symbols-outlined" data-icon="history">history</span>
            <span class="font-label-sm text-label-sm mt-1">Riwayat</span>
        </a>
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/notifikasi">
            <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
            <span class="font-label-sm text-label-sm mt-1">Notifikasi</span>
        </a>
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/profil">
            <span class="material-symbols-outlined" data-icon="person">person</span>
            <span class="font-label-sm text-label-sm mt-1">Profil</span>
        </a>
    </nav>
</body>
</html>
