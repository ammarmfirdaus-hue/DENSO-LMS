<?php
$tab = $tab ?? ($_GET['tab'] ?? 'materi');
$courseId = $course['id'] ?? 'CRS001';
$courseId = $course['id'] ?? 'CRS001';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $active_tab = 'riwayat';
    $page_title = 'Detail Riwayat - LMS';
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
    $header_title = 'Detail Riwayat';
    $show_back = true;
    $back_url = '?route=/student/riwayat';
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
                <div class="inline-flex items-center gap-2 bg-tertiary-container text-on-tertiary-container px-3 py-1 rounded-full self-start mb-3 shadow-sm backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <span class="font-label-sm text-label-sm font-bold">Selesai pada <?= e(
                        formatDateID($course['completion_date'] ?? '2025-06-20'),
                    ) ?></span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-primary mb-4 shadow-sm"><?= e(
                    $course['title'],
                ) ?></h2>
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
                <a href="?route=/student/riwayat/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=materi" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'materi'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Materi</span>
                    <?php if ($tab === 'materi'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/riwayat/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=quiz" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'quiz'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Quiz</span>
                    <?php if ($tab === 'quiz'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/riwayat/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=forum" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'forum'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Forum</span>
                    <?php if ($tab === 'forum'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/riwayat/detail&id=<?= e(
                    $courseId,
                ) ?>&tab=riwayat" class="flex-1 py-4 px-1 relative text-center <?php echo $tab === 'riwayat'
    ? 'text-secondary'
    : 'text-on-surface-variant'; ?>">
                    <span class="font-label-md text-label-md truncate">Riwayat</span>
                    <?php if ($tab === 'riwayat'): ?>
                    <div class="absolute bottom-0 left-0 w-full h-[3px] bg-secondary rounded-t-full"></div>
                    <?php endif; ?>
                </a>
                <a href="?route=/student/riwayat/detail&id=<?= e(
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

        <?php if ($tab === 'capaian'): ?>
        <!-- Content Area: Capaian Pembelajaran -->
        <?php
        $nilaiFa = (int) ($course['quiz_score'] ?? 100);
        $isFaLulus = $nilaiFa >= 75;
        $progress = 100;
        $is100 = true;
        $isCoursePassed = true;
        $completionDate = formatDateID($course['completion_date'] ?? '2025-06-20');

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
            <div class="rounded-xl p-5 flex items-center gap-4 bg-tertiary-container text-on-tertiary-container shadow-sm">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-tertiary text-on-tertiary">
                    <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">emoji_events</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-headline-sm text-headline-sm font-bold mb-1">
                        Lulus & Bersertifikat
                    </h3>
                    <p class="font-body-sm text-body-sm opacity-90">
                        Diselesaikan pada <?= e($completionDate) ?>. Selamat!
                    </p>
                </div>
            </div>

            <!-- Kriteria Kelulusan Checklist -->
            <section class="flex flex-col gap-stack-sm">
                <h3 class="font-label-lg text-label-lg text-on-surface font-bold">Kriteria Kelulusan</h3>
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                    <div class="p-4 flex items-center gap-3 border-b border-surface-variant">
                        <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">
                            check_circle
                        </span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md text-on-surface font-bold">Menyelesaikan Semua Materi (100%)</p>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Progress tercapai: 100%</p>
                        </div>
                    </div>
                    <div class="p-4 flex items-center gap-3">
                        <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">
                            check_circle
                        </span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md text-on-surface font-bold">Lulus Final Assessment (Min. Skor 75)</p>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Skor tercapai: <?= $nilaiFa ?></p>
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
                        <div class="relative w-20 h-20 flex items-center justify-center mx-auto">
                            <!-- Full-circle SVG gauge -->
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <!-- Background track -->
                                <path class="text-surface-variant" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                                <!-- Progress track -->
                                <path stroke="<?= $scoreStrokeColor ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="3" stroke-dasharray="<?= $nilaiFa ?>, 100" stroke-linecap="round"></path>
                            </svg>
                            <div class="absolute flex flex-col items-center justify-center">
                                <span class="font-headline-md text-headline-md <?= $scoreColorClass ?> font-bold leading-none"><?= $nilaiFa ?></span>
                            </div>
                        </div>
                        <div class="mt-1">
                            <p class="font-label-md text-label-md text-on-surface font-bold">Skor Akhir</p>
                            <p class="font-label-sm text-label-sm <?= $scoreColorClass ?> mt-0.5 font-medium"><?= $nilaiFa >=
 75
     ? 'Lulus'
     : 'Belum Lulus' ?></p>
                        </div>
                    </div>

                    <!-- Attendance Card -->
                    <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                        <div class="w-14 h-14 rounded-full bg-tertiary-container text-on-tertiary-container flex items-center justify-center mb-1">
                            <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                        </div>
                        <div>
                            <p class="font-headline-sm text-headline-sm text-on-surface font-bold">100%</p>
                            <p class="font-label-sm text-label-sm text-tertiary mt-0.5 font-medium">Tuntas</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sertifikat Section -->
            <section class="flex flex-col gap-stack-sm mt-stack-sm">
                <h3 class="font-label-lg text-label-lg text-on-surface font-bold">Sertifikat Digital</h3>
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant overflow-hidden relative group">
                    <!-- Certificate Thumbnail -->
                    <div class="h-44 bg-surface-variant relative overflow-hidden flex items-center justify-center">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                        <img alt="Sertifikat Dasar Keselamatan Kerja" class="w-full h-full object-cover mix-blend-multiply" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEiwMCnV-HX6w_f8JunDkUk9s6x2HogDwemAHq1-GmjG9JyL-k3jIWk6rvo1ASGhQiw_b6BzibQbFoY0MSTnaQ0MxGzyn_Ut7HOMl3z8YMDzsjaXc_HGvy6ekLvm8YNJbBJUhHDEomR8Cm14d2Wp5SGU2IXMPzwoT-N5N5iSIf8Z1vQTZbdO9A2l_ce_ae-nKN-_uyJoVWKH3092sWO0sIQC-xkeT8chFxvFdL3cdpHZbJM4d0aDkUJGOYPUNhUPjCXicBdF2ZzY">
                        <!-- Ribbon -->
                        <div class="absolute top-4 right-4 bg-tertiary text-on-tertiary text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">verified</span> Valid
                        </div>
                    </div>
                    <!-- Action Area -->
                    <div class="p-stack-md bg-surface-container-lowest flex flex-col gap-stack-md relative z-10">
                        <div>
                            <p class="font-label-lg text-label-lg text-on-surface font-bold">Certificate of Completion</p>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Diterbitkan secara otomatis oleh sistem LMS. Dapat digunakan sebagai bukti kelulusan.</p>
                        </div>
                        <button class="w-full bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant font-label-md text-label-md h-[44px] rounded-lg flex items-center justify-center gap-2 shadow-[0_4px_12px_0_rgba(67,85,185,0.2)] transition-colors active:scale-95">
                            <span class="material-symbols-outlined">download</span>
                            Unduh Sertifikat (PDF)
                        </button>
                    </div>
                </div>
            </section>
        </div>
        <?php elseif ($tab === 'materi'): ?>
        <!-- Content Area: Materi (Read Only, All Completed) -->
        <div class="p-container-margin flex flex-col gap-stack-md mt-4 animate-fade-in-up">
            <div class="flex flex-col gap-stack-lg">
                <?php if (!empty($course['sessions'])): ?>
                <?php foreach ($course['sessions'] as $index => $session): ?>
                <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-tertiary/20">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Sesi <?= $index +
                                1 ?></span>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface mt-1"><?= e(
                                $session['title'],
                            ) ?></h3>
                        </div>
                        <div class="flex items-center gap-1 text-tertiary px-2 py-1 bg-tertiary-container/20 rounded-full border border-tertiary/30">
                            <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span class="font-label-sm text-label-sm font-bold">Selesai</span>
                        </div>
                    </div>

                    <!-- Pre Test -->
                    <?php $preTest = $session['pre_test'] ?? []; ?>
                    <?php if (!empty($preTest)): ?>
                    <div class="flex flex-col border-t border-surface-variant/30 pt-3">
                        <a href="?route=/student/quiz&id=<?= e($courseId) ?>&session_id=<?= e(
    $session['id'],
) ?>&type=pre_test" class="flex items-center gap-4 py-2 hover:bg-surface-container-low transition-colors rounded-lg px-2 -ml-2 group">
                            <div class="w-10 h-10 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center shrink-0 overflow-hidden group-hover:bg-primary-container/20 group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">assignment</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-body-md text-on-surface font-medium">Pre-Test</p>
                                <p class="font-label-sm text-on-surface-variant">
                                    Selesai • Skor: <?= $preTest['score'] ?? '100' ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 border border-outline-variant/50 bg-surface-container text-on-surface-variant text-[9px] sm:text-[10px] rounded font-bold uppercase tracking-wider">Ulangi</span>
                                <span class="material-symbols-outlined text-tertiary">check_circle</span>
                            </div>
                        </a>
                        <?php  ?>
                    </div>
                    <?php endif; ?>

                    <!-- Materials -->
                    <div class="pl-4 border-l-2 border-tertiary/30 ml-5 my-2 flex flex-col gap-1 relative">
                        <?php if (!empty($preTest)): ?>
                        <div class="absolute -top-3 -left-[5px] w-2 h-2 rounded-full bg-tertiary/50"></div>
                        <?php endif; ?>
                        <?php foreach ($session['materials'] as $mIndex => $material): ?>
                        <a href="?route=/student/learning&id=<?= e($courseId) ?>&session_id=<?= e(
    $session['id'],
) ?>&material_index=<?= $mIndex ?>" class="flex items-center gap-4 py-2 rounded-lg px-2 -ml-2 hover:bg-surface-container-low transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center shrink-0 overflow-hidden group-hover:bg-secondary-container/20 group-hover:text-secondary transition-colors">
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
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;"><?= $icon ?></span>
                            </div>
                            <div class="flex-1">
                                <p class="font-body-sm text-on-surface font-medium"><?= e($material['title']) ?></p>
                                <p class="font-label-sm text-on-surface-variant"><?= e(
                                    $material['duration'],
                                ) ?> • Selesai</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 border border-outline-variant/50 bg-surface-container text-on-surface-variant text-[9px] sm:text-[10px] rounded font-bold uppercase tracking-wider">Buka Lagi</span>
                                <span class="material-symbols-outlined text-tertiary text-sm">check_circle</span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php $postTest = $session['post_test'] ?? []; ?>
                        <?php if (!empty($postTest)): ?>
                        <div class="absolute -bottom-3 -left-[5px] w-2 h-2 rounded-full bg-tertiary/50"></div>
                        <?php endif; ?>
                    </div>

                    <!-- Post Test -->
                    <?php $postTest = $session['post_test'] ?? []; ?>
                    <?php if (!empty($postTest)): ?>
                    <div class="flex flex-col border-t border-surface-variant/30 mt-3 pt-3">
                        <a href="?route=/student/quiz&id=<?= e($courseId) ?>&session_id=<?= e(
    $session['id'],
) ?>&type=post_test" class="flex items-center gap-4 py-2 hover:bg-surface-container-low transition-colors rounded-lg px-2 -ml-2 group">
                            <div class="w-10 h-10 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center shrink-0 overflow-hidden group-hover:bg-primary-container/20 group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">assignment_turned_in</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-body-md text-on-surface font-medium">Post-Test</p>
                                <p class="font-label-sm text-on-surface-variant">
                                    Selesai • Skor: <?= $postTest['score'] ?? '100' ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 border border-outline-variant/50 bg-surface-container text-on-surface-variant text-[9px] sm:text-[10px] rounded font-bold uppercase tracking-wider">Ulangi</span>
                                <span class="material-symbols-outlined text-tertiary">check_circle</span>
                            </div>
                        </a>
                        <?php  ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-8 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">hourglass_empty</span>
                    <p class="font-body-md">Data sesi tidak ditemukan.</p>
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
                <a href="?route=/student/quiz&id=<?= e(
                    $courseId,
                ) ?>&type=quiz" class="flex items-center gap-4 text-left w-full hover:bg-surface-container-low transition-colors duration-200">
                    <div class="bg-primary-container/20 text-primary rounded-lg p-3 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">quiz</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Quiz Course</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            <?= count($quiz['questions']) ?> Pertanyaan
                            • Skor Tertinggi: <?= $quiz['score'] ?>
                        </p>
                    </div>
                    <div class="shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-surface-container-high text-on-surface-variant rounded border border-outline-variant/50 text-[10px] font-bold uppercase tracking-wider">Ulangi</span>
                            <span class="material-symbols-outlined text-tertiary">check_circle</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
            <?php $fa = $course['final_assessment']; ?>
            <?php $isPassed = $fa['nilai'] !== null && $fa['nilai'] >= 75; ?>
            <!-- Final Assessment -->
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant/30 flex flex-col gap-3 w-full <?= $isPassed
                ? 'opacity-80'
                : '' ?>">
                <a href="<?= !$isPassed
                    ? '?route=/student/quiz&id=' . e($courseId) . '&type=final_assessment'
                    : 'javascript:void(0)' ?>" class="flex items-center gap-4 text-left w-full <?= $isPassed
    ? 'cursor-not-allowed'
    : 'hover:bg-surface-container-low transition-colors duration-200' ?>">
                    <div class="bg-tertiary-container/20 text-tertiary rounded-lg p-3 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $isPassed
                            ? '1'
                            : '0' ?>;">military_tech</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface">Final Assessment</h3>
                            <?php if ($isPassed): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-tertiary/10 text-tertiary rounded-full font-bold uppercase tracking-wider">Lulus</span>
                            <?php elseif ($fa['nilai'] !== null && $fa['nilai'] < 75): ?>
                            <span class="px-2 py-0.5 text-[10px] bg-error/10 text-error rounded-full font-bold uppercase tracking-wider">Remedial</span>
                            <?php endif; ?>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            1 Pertanyaan • Nilai Minimum Kelulusan: 75
                        </p>
                    </div>
                    <div class="shrink-0">
                        <?php if ($isPassed): ?>
                        <span class="material-symbols-outlined text-tertiary">check_circle</span>
                        <?php else: ?>
                        <div class="bg-secondary text-on-secondary rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
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
        <div class="p-container-margin space-y-stack-md mt-4 animate-fade-in-up pb-8">
            <div class="flex items-center justify-between px-2">
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Forum Diskusi</h2>
                <span class="font-label-sm text-label-sm text-on-surface-variant"><?= $totalForums ?> Forum Tersedia</span>
            </div>

            <!-- Banner Read-Only -->
            <div class="bg-primary-container/20 border border-primary/30 rounded-xl p-4 flex items-start gap-3 shadow-sm mb-4">
                <span class="material-symbols-outlined text-primary mt-0.5">info</span>
                <p class="font-body-sm text-on-surface-variant leading-relaxed">Course ini telah diselesaikan. Tab Forum kini bersifat <b>Read-Only</b>. Anda tetap dapat membaca riwayat diskusi yang ada, namun sudah tidak dapat membuat topik diskusi atau memberikan komentar baru.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Session Forums -->
                <?php if (!empty($course['sessions'])): ?>
                <?php foreach ($course['sessions'] as $session):

                    $sessId = $session['id'];
                    $forumData = $forumsMateri[$sessId] ?? ['threads' => []];
                    $threadCount = count($forumData['threads']);
                    ?>
                <a href="?route=/student/forum/category&course_id=<?= urlencode(
                    $course['id'],
                ) ?>&category_type=materi&session_id=<?= urlencode(
    $sessId,
) ?>&is_riwayat=true" class="block bg-surface rounded-2xl border border-outline-variant/40 p-5 group relative overflow-hidden hover:border-error/50 hover:shadow-md transition-all duration-300">

                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-error/0 group-hover:bg-error transition-colors duration-300 rounded-l-2xl"></div>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="font-label-lg font-bold text-on-surface mb-1 group-hover:text-error transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-error text-[20px]">auto_stories</span>
                                Forum <?= e($session['title']) ?>
                            </h4>
                            <p class="font-body-sm text-on-surface-variant mb-3 line-clamp-2">Diskusi materi dan tugas terkait sesi ini.</p>

                            <div class="flex items-center gap-2 font-label-sm text-on-surface-variant">
                                <span class="bg-surface-variant/50 px-2 py-0.5 rounded text-xs font-semibold"><?= $threadCount ?> Diskusi</span>
                            </div>
                        </div>

                        <div class="shrink-0 w-10 h-10 rounded-full bg-error-container/50 text-error group-hover:bg-error group-hover:text-on-error flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </div>
                    </div>
                </a>
                <?php
                endforeach; ?>
                <?php endif; ?>

                <!-- Quiz Forum -->
                <?php
                $qThreads = $forumQuiz['threads'] ?? [];
                $qThreadCount = count($qThreads);
                ?>
                <a href="?route=/student/forum/category&course_id=<?= urlencode(
                    $course['id'],
                ) ?>&category_type=quiz&is_riwayat=true" class="block bg-surface rounded-2xl border border-outline-variant/40 p-5 group relative overflow-hidden hover:border-error/50 hover:shadow-md transition-all duration-300">

                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-error/0 group-hover:bg-error transition-colors duration-300 rounded-l-2xl"></div>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="font-label-lg font-bold text-on-surface mb-1 group-hover:text-error transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-error text-[20px]">quiz</span>
                                Forum Quiz
                            </h4>
                            <p class="font-body-sm text-on-surface-variant mb-3 line-clamp-2">Pembahasan dan pertanyaan seputar quiz course.</p>

                            <div class="flex items-center gap-2 font-label-sm text-on-surface-variant">
                                <span class="bg-surface-variant/50 px-2 py-0.5 rounded text-xs font-semibold"><?= $qThreadCount ?> Diskusi</span>
                            </div>
                        </div>

                        <div class="shrink-0 w-10 h-10 rounded-full bg-error-container/50 text-error group-hover:bg-error group-hover:text-on-error flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <?php elseif ($tab === 'riwayat'): ?>
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
                            'session_id' => $session['id'],
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
                            'session_id' => $session['id'],
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
                <button type="button" onclick="toggleAccordion('rd-riwayat-aktivitas')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary shrink-0" style="font-variation-settings: 'FILL' 1;">timeline</span>
                            <span class="font-label-lg sm:font-headline-sm text-on-surface font-bold">1. Aktivitas</span>
                        </div>
                        <span class="font-label-sm text-on-surface-variant font-normal ml-6 sm:ml-0">(<?= count(
                            $activities,
                        ) ?> aktivitas)</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="rd-riwayat-aktivitas-chevron">expand_more</span>
                </button>
                <div id="rd-riwayat-aktivitas" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <?php if (empty($activities)): ?>
                        <div class="text-center py-6 text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">history</span>
                            <p class="font-body-md">Belum ada aktivitas yang tercatat.</p>
                        </div>
                        <?php else: ?>
                        <div class="relative pl-6" id="rd-riwayat-aktivitas-list">
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
                        <button type="button" onclick="toggleShowAll('rd-riwayat-aktivitas-list', this)" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-outline-variant/50 text-secondary font-label-md font-bold hover:bg-secondary-container/10 transition-colors duration-200">
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
                <button type="button" onclick="toggleAccordion('rd-riwayat-pretest')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
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
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="rd-riwayat-pretest-chevron">expand_more</span>
                </button>
                <div id="rd-riwayat-pretest" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <?php if (empty($pre_tests)): ?>
                        <div class="text-center py-6 text-on-surface-variant">
                            <p class="font-body-md">Belum ada riwayat pengerjaan Pre-Test.</p>
                        </div>
                        <?php else: ?>
                        <div class="flex flex-col gap-4" id="rd-riwayat-pretest-list">
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
                                    <a href="?route=/student/quiz&id=<?= urlencode(
                                        $courseId,
                                    ) ?>&session_id=<?= urlencode($pt['session_id']) ?>&type=pre_test&attempt=<?= $h[
    'attempt'
] ?>" class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30 hover:bg-surface-container-high/40 hover:border-outline-variant/60 transition-colors cursor-pointer group">
                                        <div>
                                            <p class="font-label-sm font-bold text-on-surface group-hover:text-primary transition-colors">Percobaan <?= $h[
                                                'attempt'
                                            ] ?></p>
                                            <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                'd M Y, H:i',
                                                strtotime($h['date']),
                                            ) ?></p>
                                        </div>
                                        <div class="text-right flex items-center gap-2">
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
                                            <span class="material-symbols-outlined text-on-surface-variant/50 text-[16px] group-hover:text-primary transition-colors">chevron_right</span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $preIdx++;endforeach;
                            ?>
                        </div>
                        <?php if (count($pre_tests) > 5): ?>
                        <button type="button" onclick="toggleShowAll('rd-riwayat-pretest-list', this)" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-outline-variant/50 text-primary font-label-md font-bold hover:bg-primary-container/10 transition-colors duration-200">
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
                <button type="button" onclick="toggleAccordion('rd-riwayat-posttest')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
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
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="rd-riwayat-posttest-chevron">expand_more</span>
                </button>
                <div id="rd-riwayat-posttest" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <?php if (empty($post_tests)): ?>
                        <div class="text-center py-6 text-on-surface-variant">
                            <p class="font-body-md">Belum ada riwayat pengerjaan Post-Test.</p>
                        </div>
                        <?php else: ?>
                        <div class="flex flex-col gap-4" id="rd-riwayat-posttest-list">
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
                                    <a href="?route=/student/quiz&id=<?= urlencode(
                                        $courseId,
                                    ) ?>&session_id=<?= urlencode($pt['session_id']) ?>&type=post_test&attempt=<?= $h[
    'attempt'
] ?>" class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30 hover:bg-surface-container-high/40 hover:border-outline-variant/60 transition-colors cursor-pointer group">
                                        <div>
                                            <p class="font-label-sm font-bold text-on-surface group-hover:text-tertiary transition-colors">Percobaan <?= $h[
                                                'attempt'
                                            ] ?></p>
                                            <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                'd M Y, H:i',
                                                strtotime($h['date']),
                                            ) ?></p>
                                        </div>
                                        <div class="text-right flex items-center gap-2">
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
                                            <span class="material-symbols-outlined text-on-surface-variant/50 text-[16px] group-hover:text-tertiary transition-colors">chevron_right</span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $postIdx++;endforeach;
                            ?>
                        </div>
                        <?php if (count($post_tests) > 5): ?>
                        <button type="button" onclick="toggleShowAll('rd-riwayat-posttest-list', this)" class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-outline-variant/50 text-tertiary font-label-md font-bold hover:bg-tertiary-container/10 transition-colors duration-200">
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
                <button type="button" onclick="toggleAccordion('rd-riwayat-qa')" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-surface-container-low/30 transition-colors duration-200 gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-error shrink-0" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                            <span class="font-label-lg sm:font-headline-sm text-on-surface font-bold">4. Quiz &amp; Assessment</span>
                        </div>
                        <?php $qaCount = count($assessments['quiz'] ?? []) + count($assessments['final'] ?? []); ?>
                        <span class="font-label-sm text-on-surface-variant font-normal ml-6 sm:ml-0">(<?= $qaCount ?> percobaan)</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-300 shrink-0 rotate-[-90deg]" id="rd-riwayat-qa-chevron">expand_more</span>
                </button>
                <div id="rd-riwayat-qa" class="accordion-body" style="display: none;">
                    <div class="px-5 pb-5">
                        <div class="flex flex-col gap-6">
                            <!-- Quiz Course -->
                            <div class="border border-outline-variant/40 rounded-lg p-4 bg-surface-container-lowest/50">
                                <h4 class="font-label-md font-bold text-on-surface mb-3 border-b border-surface-variant/30 pb-2">Quiz Course</h4>
                                <?php if (empty($assessments['quiz'])): ?>
                                <p class="font-body-sm text-on-surface-variant py-2">Belum ada riwayat Quiz Course.</p>
                                <?php else: ?>
                                <div class="flex flex-col gap-2" id="rd-riwayat-quiz-list">
                                    <?php foreach ($assessments['quiz'] as $qIdx => $h): ?>
                                    <a href="?route=/student/quiz&id=<?= urlencode(
                                        $courseId,
                                    ) ?>&type=quiz&attempt=<?= $h[
    'attempt'
] ?>" class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30 riwayat-item <?= $qIdx >=
5
    ? 'riwayat-hidden'
    : '' ?> hover:bg-surface-container-high/40 hover:border-outline-variant/60 transition-colors cursor-pointer group" <?= $qIdx >=
 5
     ? 'style="display:none;"'
     : '' ?>>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-error-container/20 text-error group-hover:bg-error-container/40 rounded-full flex items-center justify-center transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">quiz</span>
                                            </div>
                                            <div>
                                                <p class="font-label-sm font-bold text-on-surface group-hover:text-error transition-colors">Percobaan <?= $h[
                                                    'attempt'
                                                ] ?></p>
                                                <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                    'd M Y, H:i',
                                                    strtotime($h['date']),
                                                ) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-2">
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
                                            <span class="material-symbols-outlined text-on-surface-variant/50 text-[16px] group-hover:text-error transition-colors">chevron_right</span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['quiz']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('rd-riwayat-quiz-list', this)" class="mt-3 w-full flex items-center justify-center gap-2 py-2 rounded-lg border border-outline-variant/50 text-error font-label-md font-bold hover:bg-error-container/10 transition-colors duration-200">
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
                                <div class="flex flex-col gap-2" id="rd-riwayat-final-list">
                                    <?php foreach ($assessments['final'] as $fIdx => $h): ?>
                                    <?php
                                    $val = $h['score'] ?? $h['nilai'];
                                    $isPassed = $val >= 75;
                                    ?>
                                    <a href="?route=/student/quiz&id=<?= urlencode(
                                        $courseId,
                                    ) ?>&type=final_assessment&attempt=<?= $h[
    'attempt'
] ?>" class="bg-surface-container-high/20 rounded-md p-3 flex justify-between items-center border border-surface-variant/30 riwayat-item <?= $fIdx >=
5
    ? 'riwayat-hidden'
    : '' ?> hover:bg-surface-container-high/40 hover:border-outline-variant/60 transition-colors cursor-pointer group" <?= $fIdx >=
 5
     ? 'style="display:none;"'
     : '' ?>>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 <?= $isPassed
                                                ? 'bg-tertiary-container/30 text-tertiary group-hover:bg-tertiary-container/50'
                                                : 'bg-error-container/20 text-error group-hover:bg-error-container/40' ?> rounded-full flex items-center justify-center transition-colors">
                                                <span class="material-symbols-outlined text-[16px]"><?= $isPassed
                                                    ? 'check_circle'
                                                    : 'cancel' ?></span>
                                            </div>
                                            <div>
                                                <p class="font-label-sm font-bold text-on-surface <?= $isPassed
                                                    ? 'group-hover:text-tertiary'
                                                    : 'group-hover:text-error' ?> transition-colors">Percobaan <?= $h[
     'attempt'
 ] ?></p>
                                                <p class="font-label-sm text-on-surface-variant text-[11px] mt-0.5"><?= date(
                                                    'd M Y, H:i',
                                                    strtotime($h['date']),
                                                ) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-2">
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
                                            <span class="material-symbols-outlined text-on-surface-variant/50 text-[16px] <?= $isPassed
                                                ? 'group-hover:text-tertiary'
                                                : 'group-hover:text-error' ?> transition-colors">chevron_right</span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($assessments['final']) > 5): ?>
                                <button type="button" onclick="toggleShowAll('rd-riwayat-final-list', this)" class="mt-3 w-full flex items-center justify-center gap-2 py-2 rounded-lg border border-outline-variant/50 text-error font-label-md font-bold hover:bg-error-container/10 transition-colors duration-200">
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
        <!-- Content Area: Quiz (Read Only, Completed) -->
        <div class="p-container-margin flex flex-col gap-stack-md mt-4 animate-fade-in-up">
            <?php if (!empty($course['quiz']) || !empty($course['final_assessment'])): ?>

            <?php if (!empty($course['quiz'])): ?>
            <?php $quiz = $course['quiz']; ?>
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-tertiary/20 flex flex-col gap-3 w-full">
                <div class="flex items-center gap-4 text-left w-full">
                    <div class="bg-tertiary-container/30 text-tertiary rounded-lg p-3 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">quiz</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1 font-bold">Quiz Course</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            <?= count($quiz['questions'] ?? []) ?> Pertanyaan
                            • Skor: <?= $quiz['score'] ?? '100' ?>
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-1 text-tertiary bg-tertiary-container/20 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span class="font-label-sm text-label-sm font-bold">Lulus</span>
                    </div>
                </div>
                <?php  ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($course['final_assessment'])): ?>
            <?php $fa = $course['final_assessment']; ?>
            <div class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-tertiary/20 flex flex-col gap-3 w-full mt-2">
                <div class="flex items-center gap-4 text-left w-full">
                    <div class="bg-tertiary-container/30 text-tertiary rounded-lg p-3 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1 font-bold">Final Assessment</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            <?= count($fa['questions'] ?? []) ?> Pertanyaan
                            • Nilai: <?= $fa['nilai'] ?? '100' ?>
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-1 text-tertiary bg-tertiary-container/20 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span class="font-label-sm text-label-sm font-bold">Lulus</span>
                    </div>
                </div>
                <?php  ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">quiz</span>
                <p class="font-body-md text-body-md">Tidak ada quiz untuk course ini.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- BottomNavBar -->
    <?php include 'includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>

    <script>
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

        function switchForumTab(tabName) {
            const btnMateri = document.getElementById('btn-forum-materi');
            const btnQuiz = document.getElementById('btn-forum-quiz');
            const containerMateri = document.getElementById('container-forum-materi');
            const containerQuiz = document.getElementById('container-forum-quiz');

            if (tabName === 'materi') {
                btnMateri.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 bg-surface text-on-surface shadow-sm';
                btnQuiz.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-highest';
                containerMateri.style.display = 'flex';
                containerQuiz.style.display = 'none';
            } else {
                btnQuiz.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 bg-surface text-on-surface shadow-sm';
                btnMateri.className = 'flex-1 py-2 px-4 rounded-full font-label-md font-bold transition-all duration-300 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-highest';
                containerQuiz.style.display = 'flex';
                containerMateri.style.display = 'none';
            }
        }

        function searchForum() {
            const input = document.getElementById('search-forum-input').value.toLowerCase();
            const forumItems = document.querySelectorAll('.forum-thread-item');

            forumItems.forEach(item => {
                const title = item.querySelector('.forum-title').textContent.toLowerCase();
                if (title.includes(input)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });

            document.querySelectorAll('.forum-section-wrapper').forEach(wrapper => {
                const visibleItems = wrapper.querySelectorAll('.forum-thread-item[style="display: flex;"], .forum-thread-item:not([style*="display: none"])');
                let emptyState = wrapper.querySelector('.empty-state-search');
                const listContainer = wrapper.querySelector('.forum-thread-list');

                if (visibleItems.length === 0 && listContainer.querySelectorAll('.forum-thread-item').length > 0) {
                    if (!emptyState) {
                        emptyState = document.createElement('div');
                        emptyState.className = 'empty-state-search text-center py-6 bg-surface-container-lowest/50 rounded-lg border border-dashed border-outline-variant';
                        emptyState.innerHTML = '<p class="font-body-sm text-on-surface-variant italic">Topik diskusi tidak ditemukan.</p>';
                        listContainer.appendChild(emptyState);
                    }
                    emptyState.style.display = 'block';
                } else if (emptyState) {
                    emptyState.style.display = 'none';
                }
            });
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
