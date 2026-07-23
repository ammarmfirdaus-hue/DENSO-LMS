<?php
/**
 * Tampilan Sentra Notifikasi (Notification Center) DLS Admin.
 */

$header_title = 'Notifikasi Operasional';
$active_tab = 'notification';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Pusat Notifikasi Admin';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg max-w-4xl mx-auto">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Pusat Peringatan & Notifikasi</h1>
                <p class="font-body-md text-on-surface-variant">Prioritaskan notifikasi yang membutuhkan tindakan (actionable alerts).</p>
            </div>
            
            <button class="bg-surface hover:bg-surface-variant border border-outline-variant text-on-surface-variant font-label-md px-4 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">done_all</span> Tandai Semua Dibaca
            </button>
        </div>

        <div class="max-w-4xl mx-auto space-y-3">
            <?php foreach ($notifications as $notif): ?>
                <?php
                $colors = [
                    'Warning' => [
                        'bg' => 'bg-warning-container',
                        'text' => 'text-on-warning-container',
                        'border' => 'border-warning/50',
                        'icon' => 'warning',
                        'iconColor' => 'text-warning',
                    ],
                    'Info' => [
                        'bg' => 'bg-surface-container-high',
                        'text' => 'text-on-surface',
                        'border' => 'border-outline-variant',
                        'icon' => 'info',
                        'iconColor' => 'text-primary',
                    ],
                    'Success' => [
                        'bg' => 'bg-success-container/40',
                        'text' => 'text-on-surface',
                        'border' => 'border-success/30',
                        'icon' => 'check_circle',
                        'iconColor' => 'text-success',
                    ],
                ];
                $t = $colors[$notif['type']] ?? $colors['Info'];
                ?>
                <div class="<?= $t['bg'] ?> <?= $t[
     'border'
 ] ?> rounded-xl p-4 border flex gap-4 items-start shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group cursor-pointer hover:border-primary/50" onclick="window.location.href='?route=/admin/course/detail&id=101'">
                    <div class="mt-1">
                        <span class="material-symbols-outlined text-[28px] <?= $t[
                            'iconColor'
                        ] ?>" style="font-variation-settings: 'FILL' 1;"><?= e($t['icon']) ?></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <h3 class="font-label-lg font-bold text-on-surface"><?= e($notif['title']) ?></h3>
                            <span class="font-label-sm text-[10px] bg-surface/50 px-2 py-0.5 rounded-sm border border-outline-variant/50"><?= e(
                                $notif['time'],
                            ) ?></span>
                        </div>
                        <p class="font-body-md <?= $t['text'] ?>"><?= e($notif['message']) ?></p>
                        
                        <?php if ($notif['type'] === 'Warning'): ?>
                            <div class="mt-3">
                                <button class="font-label-md bg-warning text-on-warning px-4 py-1.5 rounded-lg active:scale-95 transition-transform" onclick="window.location.href='?route=/admin/assignment'">Tindak Lanjuti Sekarang</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

</body>
</html>
