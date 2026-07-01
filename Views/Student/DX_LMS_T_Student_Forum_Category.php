<?php
/**
 * Tampilan Kategori Forum Diskusi (List of Threads for a specific session/quiz)
 */
$pageTitle = 'Forum - ' . e($course['title']); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = $pageTitle;
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }

        body {
            min-height: max(884px, 100dvh);
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
    $header_title = $isRiwayat ? 'Detail Riwayat' : 'Detail Course';
    $show_back = true;
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <main class="dls-main flex-1 flex flex-col min-w-0 pb-16">
        <div class="max-w-[1200px] w-full mx-auto px-4 md:px-8 py-6 space-y-6">


            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-surface-container-lowest rounded-3xl border border-outline-variant/30 p-6 md:p-8 mb-8 shadow-sm">
                <!-- Background Decoration -->
                <div class="absolute right-0 top-0 w-64 h-64 <?= $isRiwayat
                    ? 'bg-error/5'
                    : 'bg-secondary/5' ?> rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                <div class="absolute right-20 bottom-0 w-40 h-40 <?= $isRiwayat
                    ? 'bg-error-container/20'
                    : 'bg-tertiary/5' ?> rounded-full blur-2xl -mb-10 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-2.5 py-1 bg-surface-variant/40 text-on-surface-variant text-[11px] font-bold rounded-md border border-outline-variant/30 uppercase tracking-wider">
                                <?= e($course['title']) ?>
                            </span>
                        </div>
                        <h2 class="font-headline-lg font-bold text-on-surface flex items-center gap-3 mb-2">
                            <span class="material-symbols-outlined <?= $isRiwayat
                                ? 'text-error bg-error/10'
                                : 'text-secondary bg-secondary/10' ?> p-2 rounded-xl text-[28px] shrink-0">forum</span>
                            <span class="truncate"><?= e($categoryTitle) ?></span>
                        </h2>
                        <p class="font-body-md text-on-surface-variant max-w-2xl leading-relaxed">
                            Area diskusi untuk bertukar pikiran, bertanya, dan berbagi pemahaman terkait materi pada <strong><?= e(
                                $categoryTitle,
                            ) ?></strong>.
                        </p>

                        <div class="flex flex-wrap items-center gap-4 mt-4">
                            <div class="flex items-center gap-1.5 text-on-surface font-medium text-[14px]">
                                <span class="material-symbols-outlined text-[18px] text-tertiary">chat</span>
                                <?= count($threads) ?> Topik Diskusi
                            </div>
                            <div class="w-1.5 h-1.5 rounded-full bg-outline-variant"></div>
                            <div class="flex items-center gap-1.5 text-on-surface-variant text-[14px]">
                                <span class="material-symbols-outlined text-[18px]">group</span>
                                <?= count($threads) > 0 ? 'Diskusi Aktif' : 'Belum Ada Diskusi' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Search Bar in Header -->
                    <?php if (!$isLocked && count($threads) > 0): ?>
                    <div class="w-full lg:w-[320px] xl:w-[400px] shrink-0 mt-4 lg:mt-0">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-on-surface-variant text-[20px] group-focus-within:text-secondary transition-colors">search</span>
                            </div>
                            <input type="text" id="forumSearch" placeholder="Cari topik diskusi..." class="w-full pl-11 pr-4 py-3.5 bg-surface border-2 border-outline-variant/40 rounded-2xl focus:outline-none focus:border-secondary focus:ring-4 focus:ring-secondary/10 transition-all font-body-md text-on-surface shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] placeholder:text-on-surface-variant/60" autocomplete="off">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isRiwayat): ?>
            <div class="bg-error-container/20 border border-error/20 rounded-xl p-4 mb-6 flex items-start gap-3">
                <span class="material-symbols-outlined text-error mt-0.5">info</span>
                <div class="font-body-sm text-on-surface-variant leading-relaxed">
                    Course ini telah diselesaikan. Forum ini bersifat <strong>Read-Only</strong>. Anda tetap dapat membaca riwayat diskusi yang ada, namun sudah tidak dapat membuat topik diskusi atau memberikan komentar baru.
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isLocked): ?>
            <!-- Locked State -->
            <div class="bg-surface rounded-2xl border border-outline-variant/30 p-8 md:p-12 flex flex-col items-center justify-center text-center shadow-sm">
                <div class="w-20 h-20 bg-surface-variant/30 rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-on-surface-variant/60">lock_clock</span>
                </div>
                <h3 class="font-headline-sm font-bold text-on-surface mb-2">Forum Terkunci</h3>
                <p class="font-body-md text-on-surface-variant max-w-md">Selesaikan materi pada sesi ini terlebih dahulu untuk dapat mengakses forum diskusinya.</p>
            </div>
              /**  */<?php
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                /**  */
                /**  */

                /**  */

                /**  */

                /**  */

                /**  */
                /**  */
                /**  */

                /**  */
                /**  */
                /**  */
                /**  */
                else: ?>
            <!-- Thread List Header -->
            <div class="flex items-center justify-between mb-4 mt-8">
                <h3 class="font-headline-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-tertiary">list_alt</span>
                    Daftar Diskusi
                </h3>
                <div class="flex items-center gap-2 text-on-surface-variant font-label-md bg-surface-container px-3 py-1.5 rounded-lg border border-outline-variant/30 hover:bg-surface-variant transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">sort</span>
                    <select class="bg-transparent border-none outline-none focus:ring-0 cursor-pointer text-on-surface-variant hover:text-on-surface transition-colors font-medium appearance-none pr-4">
                        <option>Terbaru</option>
                        <option>Terpopuler</option>
                        <option>Belum Dibalas</option>
                    </select>
                    <span class="material-symbols-outlined text-[16px] -ml-4 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Thread List Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6" id="threadList">
                <?php if (empty($threads)): ?>
                <div class="col-span-1 lg:col-span-2 text-center py-16 empty-state bg-surface-container-lowest/50 rounded-3xl border border-dashed border-outline-variant">
                    <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3">speaker_notes_off</span>
                    <p class="font-body-lg font-medium text-on-surface">Belum ada diskusi</p>
                    <p class="font-body-sm text-on-surface-variant mt-1 mb-6">Jadilah yang pertama untuk memulai diskusi di forum ini.</p>

                    <?php if (!$isRiwayat && !$hasCreatedThread && $categoryType === 'materi'): ?>
                    <a href="?route=/student/forum/new&session_id=<?= e(
                        $sessionId,
                    ) ?>" class="inline-flex items-center gap-2 bg-secondary text-on-secondary px-6 py-3 rounded-full font-label-md font-bold shadow-md hover:bg-on-secondary-fixed-variant hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <span class="material-symbols-outlined text-[20px]">add_comment</span>
                        Buat Diskusi Baru
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <!-- Empty State for Search -->
                <div id="searchEmptyState" class="hidden col-span-1 lg:col-span-2 text-center py-16 bg-surface-container-lowest/50 rounded-3xl border border-dashed border-outline-variant">
                    <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3">search_off</span>
                    <p class="font-body-lg font-medium text-on-surface">Tidak ada hasil pencarian</p>
                    <p class="font-body-sm text-on-surface-variant mt-1">Coba gunakan kata kunci lain.</p>
                </div>

                <?php foreach ($threads as $th): ?>
                <?php
                $initial = strtoupper(substr($th['author'], 0, 1));
                $isHot = isset($th['is_hot']) && $th['is_hot'];
                $likes = isset($th['likes']) ? $th['likes'] : mt_rand(2, 25);
                $role = isset($th['role']) ? $th['role'] : 'Operator Produksi &middot; Line A';

                $cardClass =
                    'forum-thread-item flex flex-col bg-surface rounded-2xl border border-outline-variant/40 p-5 md:p-6 group relative overflow-hidden transition-all duration-300';
                $hoverBorder = $isRiwayat
                    ? 'hover:border-error/40 hover:shadow-[0_8px_24px_-8px_rgba(186,26,26,0.15)]'
                    : 'hover:border-secondary/40 hover:shadow-[0_8px_24px_-8px_rgba(0,0,0,0.1)]';

                $edgeBgColor = $isRiwayat ? 'bg-error' : 'bg-secondary';

                $avatarGradient = $isRiwayat
                    ? 'from-error-container to-error/20'
                    : 'from-secondary-container to-secondary/20';
                $avatarText = $isRiwayat ? 'text-error' : 'text-secondary';
                $avatarBorder = $isRiwayat ? 'border-error/10' : 'border-secondary/10';

                $threadUrl = '?route=/student/forum/thread&id=' . e($th['id']) . ($isRiwayat ? '&is_riwayat=true' : '');
                ?>
                <a href="<?= $threadUrl ?>" class="<?= $cardClass ?> <?= $hoverBorder ?>" data-title="<?= htmlspecialchars(
    strtolower($th['title']),
) ?>">
                    <!-- Left Edge Highlight -->
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-secondary/0 group-hover:<?= $edgeBgColor ?> transition-colors duration-300 rounded-l-2xl"></div>

                    <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-6">
                        <!-- Author Info (Left side on desktop, top on mobile) -->
                        <div class="flex items-center md:flex-col md:items-center gap-3 md:w-24 shrink-0">
                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-full bg-gradient-to-br <?= $avatarGradient ?> <?= $avatarText ?> font-headline-sm font-bold flex items-center justify-center shadow-sm border <?= $avatarBorder ?> group-hover:scale-105 transition-transform duration-300">
                                <?= $initial ?>
                            </div>
                            <div class="md:text-center">
                                <div class="font-label-md font-bold text-on-surface line-clamp-1"><?= e(
                                    $th['author'],
                                ) ?></div>
                                <div class="font-label-sm text-on-surface-variant text-[11px] truncate"><?= $role ?></div>
                            </div>
                        </div>

                        <!-- Thread Content (Right side) -->
                        <div class="flex-1 min-w-0 flex flex-col justify-between h-full">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h4 class="font-headline-sm font-bold text-on-surface line-clamp-2 group-hover:<?= $avatarText ?> transition-colors duration-300 flex-1"><?= e(
     $th['title'],
 ) ?></h4>
                                    <?php if ($isHot): ?>
                                    <span class="px-2.5 py-1 bg-error-container/50 text-on-error-container text-[11px] font-bold rounded-md flex items-center gap-1 shrink-0 border border-error/10">
                                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">local_fire_department</span> Hot Topic
                                    </span>
                                    <?php elseif ($th['replies'] == 0): ?>
                                    <span class="px-2.5 py-1 bg-surface-variant text-on-surface-variant text-[11px] font-bold rounded-md shrink-0 border border-outline-variant/30">
                                        Baru
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($th['preview'])): ?>
                                <p class="font-body-md text-on-surface-variant line-clamp-2 mb-4 leading-relaxed"><?= e(
                                    $th['preview'],
                                ) ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Footer / Metadata -->
                            <div class="flex items-center justify-between flex-wrap gap-4 mt-auto pt-4 border-t border-surface-variant/40">
                                <div class="flex items-center gap-4 md:gap-6 font-label-md text-on-surface-variant">
                                    <div class="flex items-center gap-1.5" title="Waktu Posting">
                                        <span class="material-symbols-outlined text-[18px]">schedule</span>
                                        <span class="text-[13px]"><?= e($th['last_active']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 <?= $th['replies'] > 0
                                        ? 'text-tertiary'
                                        : '' ?>" title="Balasan">
                                        <span class="material-symbols-outlined text-[18px] <?= $th['replies'] > 0
                                            ? 'text-tertiary'
                                            : '' ?>">forum</span>
                                        <span class="font-bold <?= $th['replies'] > 0 ? 'text-tertiary' : '' ?>"><?= e(
    $th['replies'],
) ?></span>
                                        <span class="text-[13px] hidden sm:inline">Balasan</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 <?= $likes > 0
                                        ? 'text-error'
                                        : '' ?>" title="Suka">
                                        <span class="material-symbols-outlined text-[18px] <?= $likes > 0
                                            ? 'text-error'
                                            : '' ?>">favorite</span>
                                        <span class="font-bold <?= $likes > 0
                                            ? 'text-error'
                                            : '' ?>"><?= $likes ?></span>
                                        <span class="text-[13px] hidden sm:inline">Suka</span>
                                    </div>
                                </div>

                                <!-- Action Arrow -->
                                <div class="w-8 h-8 rounded-full bg-surface-container hover:<?= $isRiwayat
                                    ? 'bg-error/10 text-error'
                                    : 'bg-secondary/10 text-secondary' ?> flex items-center justify-center transition-colors text-on-surface-variant group-hover:translate-x-1 duration-300">
                                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Action Button at the bottom -->
            <?php if (!empty($threads)): ?>
            <?php if (!$isRiwayat && !$hasCreatedThread && $categoryType === 'materi'): ?>
            <div class="pt-6 mt-4 flex justify-center lg:justify-end">
                <a href="?route=/student/forum/new&session_id=<?= e(
                    $sessionId,
                ) ?>" class="flex items-center gap-2 bg-secondary text-on-secondary px-6 py-3 rounded-full font-label-md font-bold shadow-md hover:bg-on-secondary-fixed-variant hover:shadow-lg hover:-translate-y-0.5 transition-all w-full md:w-auto justify-center">
                    <span class="material-symbols-outlined text-[20px]">add_comment</span>
                    Buat Diskusi Sesi Ini
                </a>
            </div>
            <?php elseif (!$isRiwayat && $categoryType === 'materi'): ?>
            <div class="pt-4 mt-2 flex justify-center">
                <span class="px-4 py-2 bg-surface-container text-on-surface-variant text-sm font-medium rounded-full italic border border-outline-variant/30">
                    ✓ Kesempatan 1x membuat diskusi telah Anda gunakan
                </span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

        </div>
    </main>

    <!-- Bottom Navigation Shared Component -->
    <?php
    $active_tab = $isRiwayat ? 'riwayat' : 'courses';
    include 'includes/components/DX_LMS_M_Bottom_Nav.php';
    ?>

    <?php if (!$isLocked && count($threads) > 0): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('forumSearch');
            const threadItems = document.querySelectorAll('.forum-thread-item');
            const emptyState = document.getElementById('searchEmptyState');

            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    let hasVisibleItems = false;

                    threadItems.forEach(item => {
                        const title = item.getAttribute('data-title');
                        if (title.includes(searchTerm)) {
                            item.style.display = 'flex';
                            hasVisibleItems = true;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    <!--  -->
                    if (!hasVisibleItems && searchTerm !== '') {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    <?php endif; ?>
</body>

</html>