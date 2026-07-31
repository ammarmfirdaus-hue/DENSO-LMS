<?php
/**
 * Tampilan Forum Pengajar (Teacher Forum).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Forum Diskusi - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'course'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Forum Diskusi';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[72px]">
        <div class="max-w-4xl mx-auto space-y-stack-xl">
            
            <div class="bg-surface-container-low p-4 rounded-xl border border-surface-variant flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-headline-sm font-bold text-on-surface">Q&A Peserta</h2>
                    <p class="font-body-sm text-on-surface-variant">Jawab pertanyaan peserta atau buat thread diskusi baru.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-label-sm text-secondary bg-secondary-container/20 px-2.5 py-1 rounded-md font-bold">Terdapat 1 pertanyaan baru</span>
                    <button class="bg-primary hover:bg-primary-600 text-on-primary font-label-md font-bold px-4 py-2 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add_comment</span> Buat Pengumuman
                    </button>
                </div>
            </div>

            <!-- Thread List -->
            <div class="space-y-4">
                <?php foreach ($threads ?? [] as $thread): ?>
                <div class="bg-surface-container-lowest border border-surface-variant rounded-xl p-stack-sm hover:border-outline-variant transition-colors group flex flex-col md:flex-row gap-4">
                    <div class="flex items-center gap-3 md:hidden border-b border-surface-variant/50 pb-3">
                        <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
                            <span class="font-title-sm font-bold"><?= substr(
                                e($thread['author'] ?? 'U'),
                                0,
                                1,
                            ) ?></span>
                        </div>
                        <div class="flex-1">
                            <p class="font-label-md font-bold text-on-surface"><?= e($thread['author']) ?></p>
                            <p class="font-body-sm text-on-surface-variant text-[11px]"><?= e($thread['time']) ?></p>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex flex-col items-center gap-2 w-16 shrink-0 shrink">
                        <div class="w-12 h-12 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center">
                            <span class="font-title-sm font-bold text-[18px]"><?= substr(
                                e($thread['author'] ?? 'U'),
                                0,
                                1,
                            ) ?></span>
                        </div>
                        <p class="font-label-sm font-bold text-on-surface text-center line-clamp-1 w-full text-[11px]"><?= e(
                            $thread['author'],
                        ) ?></p>
                        <p class="font-body-sm text-on-surface-variant text-[10px] text-center w-full"><?= e(
                            $thread['time'],
                        ) ?></p>
                    </div>

                    <div class="flex-1 flex flex-col gap-2">
                        <h3 class="font-title-lg font-bold text-on-surface group-hover:text-primary transition-colors"><?= e(
                            $thread['title'],
                        ) ?></h3>
                        <p class="font-body-md text-on-surface-variant line-clamp-2 md:line-clamp-none whitespace-pre-wrap"><?= e(
                            $thread['content'],
                        ) ?></p>
                        
                        <div class="mt-2 pt-3 flex justify-between items-center border-t border-surface-variant/50">
                            <div class="flex gap-4">
                                <div class="flex items-center gap-1.5 text-secondary">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">question_answer</span>
                                    <span class="font-label-sm font-bold"><?= e(
                                        (string) ($thread['replies'] ?? '0'),
                                    ) ?> Balasan</span>
                                </div>
                            </div>
                            <button class="bg-primary text-on-primary font-label-md font-bold py-1.5 px-4 rounded-lg shadow-sm hover:bg-primary-600 transition-colors text-[13px]">
                                Balas Topik
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
