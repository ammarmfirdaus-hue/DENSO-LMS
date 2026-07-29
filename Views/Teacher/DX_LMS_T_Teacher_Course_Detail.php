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
$canEditRecommendation = false;

switch ($courseStatus) {
    case 'Draft':
        $badgeColor = 'bg-surface-container-high text-on-surface-variant';
        $canEditRecommendation = true;
        break;
    case 'Submitted':
        $badgeColor = 'bg-info-container text-on-info-container';
        break;
    case 'Approved':
        $badgeColor = 'bg-success-container text-on-success-container';
        break;
    case 'Running':
        $badgeColor = 'bg-primary-container text-on-primary-container';
        break;
    case 'Completed':
    case 'Archived':
        $badgeColor = 'bg-surface-container text-outline';
        break;
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Detail Course - ' . htmlspecialchars($course['title'] ?? 'Course');
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

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'Detail Course';
        $show_back_button = true;
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header -->
    <div class="hidden lg:flex lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md justify-between items-center">
        <div class="flex items-center gap-3">
            <button onclick="history.back()" class="p-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95 flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </button>
            <h1 class="font-headline-6 font-bold text-on-surface line-clamp-1">Detail Course</h1>
        </div>
        
        <div class="flex gap-2">
            <?php if ($canEditRecommendation): ?>
            <a href="?route=/teacher/course/create&course_id=<?= e(
                (string) $course['id'],
            ) ?>" class="bg-primary text-on-primary h-10 px-4 rounded-xl shadow-sm text-label-md font-bold flex items-center gap-2 hover:bg-primary-600 transition-colors">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                Edit Course
            </a>
            <?php elseif ($courseStatus === 'Running'): ?>
            <a href="?route=/teacher/grade&course_id=<?= e(
                (string) $course['id'],
            ) ?>" class="bg-secondary text-on-secondary h-10 px-4 rounded-xl shadow-sm text-label-md font-bold flex items-center gap-2 hover:bg-secondary-600 transition-colors">
                <span class="material-symbols-outlined text-[20px]">grading</span>
                Kelola Nilai
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-4 lg:pt-6">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-stack-xl lg:gap-gutter items-start">
            
            <!-- Left Column: Primary Content -->
            <div class="lg:col-span-2 space-y-stack-xl lg:space-y-stack-2xl">
                
                <!-- Course Hero Section -->
                <div class="bg-surface-container-lowest rounded-2xl border border-surface-variant shadow-[0_4px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="md:flex">
                        <div class="h-32 md:h-auto md:w-32 bg-primary-container/20 border-b md:border-b-0 md:border-r border-outline-variant/30 flex items-center justify-center relative">
                            <span class="material-symbols-outlined text-[48px] text-primary" style="font-variation-settings: 'FILL' 1;">
                                <?= e($course['icon'] ?? 'menu_book') ?>
                            </span>
                        </div>
                        <div class="p-stack-md lg:p-stack-lg flex-1">
                            <div class="flex justify-between items-start gap-4 mb-2">
                                <span class="font-label-sm text-xs bg-secondary-container/30 text-secondary px-2 py-0.5 rounded-full inline-block">
                                    <?= e($course['code'] ?? '') ?>
                                </span>
                                <div class="<?= $badgeColor ?> px-3 py-1 rounded-full font-label-sm text-[12px] font-bold shadow-sm whitespace-nowrap border border-current/10">
                                    <?= e($courseStatus) ?>
                                </div>
                            </div>
                            
                            <h2 class="font-headline-md font-bold text-on-surface mb-2 leading-tight">
                                <?= e($course['title'] ?? '') ?>
                            </h2>
                            <p class="font-body-md text-on-surface-variant mb-4">
                                <?= e($course['description'] ?? 'Tidak ada deskripsi.') ?>
                            </p>
                            
                            <div class="flex flex-wrap items-center gap-4 text-label-md text-on-surface-variant border-t border-outline-variant/30 pt-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">category</span>
                                    <span><?= e($course['category'] ?? 'Umum') ?></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>
                                    <span><?= e((string) ($course['participants'] ?? '0')) ?> Peserta</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Syllabus / Sesi & Materi -->
                <section class="space-y-stack-md">
                    <div class="flex justify-between items-end border-b border-outline-variant/50 pb-2">
                        <h3 class="font-headline-sm font-bold text-on-surface">Silabus & Materi</h3>
                        <?php if ($canEditRecommendation): ?>
                        <button class="text-primary font-label-md font-bold hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Sesi
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="space-y-3">
                        <?php if (empty($syllabus)): ?>
                            <div class="bg-surface-container-lowest p-6 rounded-xl border border-dashed border-outline text-center text-on-surface-variant">
                                Belum ada materi.
                            </div>
                        <?php else: ?>
                            <?php foreach ($syllabus as $index => $sess): ?>
                                <div class="bg-surface-container-lowest rounded-xl border border-surface-variant overflow-hidden">
                                    <div class="bg-surface-container-low/40 px-4 py-3 flex justify-between items-center border-b border-surface-variant">
                                        <h4 class="font-title-md font-semibold text-on-surface"><?= e(
                                            $sess['title'],
                                        ) ?></h4>
                                        <button class="material-symbols-outlined text-on-surface-variant">expand_more</button>
                                    </div>
                                    <div class="p-4 grid grid-cols-2 gap-3 text-body-sm text-on-surface-variant bg-surface">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px] text-primary">description</span>
                                            <?= e((string) $sess['materials']) ?> Materi
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px] text-tertiary">quiz</span>
                                            <?= e((string) $sess['assesments']) ?> Assessment
                                        </div>
                                    </div>
                                    <?php if ($canEditRecommendation): ?>
                                    <div class="bg-surface-container-lowest px-4 py-2 border-t border-surface-variant flex justify-end gap-2">
                                        <button class="text-primary font-label-sm hover:underline">Edit Sesi</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Peserta & Target Audience -->
                <section class="space-y-stack-md">
                    <div class="flex justify-between items-end border-b border-outline-variant/50 pb-2">
                        <h3 class="font-headline-sm font-bold text-on-surface">Target Peserta</h3>
                        <?php if ($canEditRecommendation): ?>
                        <a href="?route=/teacher/course/create&course_id=<?= e(
                            (string) $course['id'],
                        ) ?>" class="text-primary font-label-md font-bold hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[18px]">edit</span> Revisi Target
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($courseStatus === 'Submitted'): ?>
                        <div class="bg-info-container/20 border border-info/30 p-4 rounded-xl flex items-start gap-3 mb-4">
                            <span class="material-symbols-outlined text-info mt-0.5">info</span>
                            <div>
                                <p class="font-title-sm font-bold text-on-surface">Menunggu Review Admin</p>
                                <p class="font-body-sm text-on-surface-variant">Rekomendasi target peserta telah disubmit. Anda tidak dapat mengubah data ini sampai direview oleh Admin.</p>
                            </div>
                        </div>
                    <?php elseif ($courseStatus === 'Draft' && empty($participants)): ?>
                        <div class="bg-warning-container/20 border border-warning/30 p-4 rounded-xl flex flex-col items-center justify-center gap-2 text-center">
                            <span class="material-symbols-outlined text-warning text-[32px]">group_add</span>
                            <p class="font-title-sm font-bold text-on-surface">Target Peserta Belum Ditentukan</p>
                            <p class="font-body-sm text-on-surface-variant mb-2">Usulkan siapa saja yang harus mengikuti course ini via fitur recommendation.</p>
                            <a href="?route=/teacher/course/create&course_id=<?= e(
                                (string) $course['id'],
                            ) ?>#target" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md font-bold hover:bg-primary-600 transition-colors">Usulkan Target Audience</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($participants)): ?>
                        <div class="bg-surface-container-lowest border border-surface-variant rounded-xl overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-surface-container-low text-on-surface-variant font-label-sm border-b border-surface-variant">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Nama Peserta</th>
                                        <th class="px-4 py-3 font-semibold">Status / Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-variant font-body-sm">
                                    <?php foreach ($participants as $p): ?>
                                    <tr class="hover:bg-surface-container-lowest/50">
                                        <td class="px-4 py-3 text-on-surface font-medium"><?= e($p['nama']) ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($courseStatus === 'Submitted' || $courseStatus === 'Draft'): ?>
                                                <span class="text-on-surface-variant italic">Kandidat Rekomendasi</span>
                                            <?php else: ?>
                                                <span class="text-<?= $p['progress'] === 100
                                                    ? 'success'
                                                    : 'primary' ?> font-semibold"><?= e(
     (string) $p['progress'],
 ) ?>%</span>
                                                <span class="text-on-surface-variant text-[11px] ml-1">(Skor: <?= e(
                                                    (string) $p['score'],
                                                ) ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (count($participants) > 2): ?>
                                <div class="px-4 py-2 border-t border-surface-variant text-center">
                                    <button class="text-primary font-label-md hover:underline">Lihat Semua <?= e(
                                        (string) count($participants),
                                    ) ?> Peserta</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <div class="h-8"></div> <!-- spacer -->
            </div>

            <!-- Right Column: Context/Secondary Content -->
            <div class="space-y-stack-xl">
                
                <!-- Action Cards specific to Course -->
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-1">
                    <a href="?route=/teacher/forum&course_id=<?= e(
                        (string) $course['id'],
                    ) ?>" class="bg-surface-container border border-outline-variant p-4 rounded-xl flex items-center gap-3 hover:bg-surface-container-high hover:border-primary/50 transition-colors group">
                        <div class="w-10 h-10 bg-primary-container text-primary rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined">forum</span>
                        </div>
                        <div>
                            <p class="font-title-sm font-bold text-on-surface group-hover:text-primary">Buka Forum</p>
                            <p class="font-body-sm text-on-surface-variant text-[11px]">2 diskusi baru</p>
                        </div>
                    </a>
                    
                    <?php if ($courseStatus === 'Running' || $courseStatus === 'Completed'): ?>
                    <a href="?route=/teacher/grade&course_id=<?= e(
                        (string) $course['id'],
                    ) ?>" class="bg-surface-container border border-outline-variant p-4 rounded-xl flex items-center gap-3 hover:bg-surface-container-high hover:border-secondary/50 transition-colors group">
                        <div class="w-10 h-10 bg-secondary-container text-secondary rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined">grading</span>
                        </div>
                        <div>
                            <p class="font-title-sm font-bold text-on-surface group-hover:text-secondary">Kelola Nilai</p>
                            <p class="font-body-sm text-on-surface-variant text-[11px]">Lihat hasil assessment</p>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Recent Activities (Course Context Only) -->
                <section class="space-y-stack-sm bg-surface-container-lowest border border-surface-variant p-stack-md rounded-xl">
                    <h3 class="font-title-md font-bold text-on-surface mb-3">Aktivitas Course</h3>
                    
                    <?php if (empty($activities)): ?>
                        <p class="font-body-sm text-on-surface-variant">Belum ada aktivitas terkait course ini.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($activities as $act): ?>
                                <div class="flex gap-3 items-start border-b border-surface-variant/50 pb-3 last:border-0 last:pb-0">
                                    <div class="w-8 h-8 rounded-full <?= e(
                                        $act['color_class'] ?? 'bg-surface-container-high',
                                    ) ?> border border-surface-variant shrink-0 flex items-center justify-center mt-0.5">
                                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;"><?= e(
                                            $act['icon'] ?? 'history',
                                        ) ?></span>
                                    </div>
                                    <div>
                                        <p class="font-label-sm font-semibold text-on-surface leading-snug"><?= e(
                                            $act['action'],
                                        ) ?></p>
                                        <p class="font-label-sm text-[10px] text-on-surface-variant mt-0.5"><?= e(
                                            $act['time'],
                                        ) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
            </div>
        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
