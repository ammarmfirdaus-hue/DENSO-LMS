<?php
/**
 * Tampilan Notifikasi Pengajar (Teacher Notification).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Notifikasi - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'notifikasi'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'Notifikasi';
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header -->
    <div class="hidden lg:flex lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md items-center justify-between">
        <h1 class="font-headline-6 font-bold text-on-surface">Notifikasi</h1>
        <button class="text-primary font-label-md hover:underline font-bold transition-all px-2">Tandai semua dibaca</button>
    </div>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-4 lg:pt-6">
        <div class="max-w-3xl mx-auto space-y-stack-xl">
            
            <section class="flex justify-between items-end mb-2 lg:hidden">
                <div>
                    <h2 class="font-headline-sm font-bold text-on-surface">Pemberitahuan</h2>
                </div>
                <button class="text-primary font-label-sm font-bold">Baca Semua</button>
            </section>

            <div class="bg-surface-container-lowest border border-surface-variant rounded-2xl overflow-hidden shadow-sm">
                <div class="divide-y divide-surface-variant/50">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-12 px-4">
                            <div class="bg-surface-container-highest/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="material-symbols-outlined text-[32px] text-surface-variant">notifications_off</span>
                            </div>
                            <p class="font-title-md font-bold text-on-surface mb-1">Tidak Ada Notifikasi Baru</p>
                            <p class="font-body-sm text-on-surface-variant">Pemberitahuan terkait rekomendasi atau update course akan muncul di sini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <?php
                            $isUnread = $notif['is_unread'] ?? false;
                            $colorCode = str_replace(
                                'text-on-',
                                '',
                                str_replace('bg-', '', $notif['color_class'] ?? 'bg-surface-high'),
                            );
                            $bgClass = $isUnread ? 'bg-surface-container-low' : 'bg-surface';
                            ?>
                            <a href="<?= e(
                                $notif['link'] ?? '#',
                            ) ?>" class="block p-4 sm:p-5 hover:bg-surface-container-low transition-colors <?= $bgClass ?> relative">
                                <?php if ($isUnread): ?>
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary rounded-r-lg"></div>
                                <?php endif; ?>
                                
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-full <?= e(
                                        $notif['color_class'] ?? 'bg-surface-container-high',
                                    ) ?> border border-surface-variant flex items-center justify-center shrink-0 shadow-sm mt-1">
                                        <span class="material-symbols-outlined <?= $isUnread && isset($colorCode)
                                            ? 'text-on-' . $colorCode
                                            : 'text-on-surface-variant' ?>" style="font-variation-settings: 'FILL' 1;"><?= e(
    $notif['icon'] ?? 'notifications',
) ?></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start gap-2 mb-1">
                                            <p class="font-title-sm font-bold text-on-surface line-clamp-1 <?= $isUnread
                                                ? 'text-primary'
                                                : '' ?>">
                                                <?= e($notif['title']) ?>
                                            </p>
                                            <span class="font-label-sm text-[11px] text-on-surface-variant whitespace-nowrap pt-1">
                                                <?= e($notif['time']) ?>
                                            </span>
                                        </div>
                                        <p class="font-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
                                            <?= e($notif['message']) ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
