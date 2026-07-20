<?php
/**
 * Tampilan Timeline Aktivitas Sistem (Activity Center) DLS Admin.
 */

$header_title = 'Activity Center';
$active_tab = 'activity';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Rekam Aktivitas Sistem';
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

    <main class="dls-main flex-1 overflow-y-auto pt-[72px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <div class="mb-stack-lg max-w-4xl mx-auto">
            <h1 class="font-headline-lg font-bold text-on-surface text-center md:text-left">Activity Hub LMS</h1>
            <p class="font-body-md text-on-surface-variant text-center md:text-left">Semua catatan operasional interaksi dari seluruh pengguna lintas Role teregistrasi di sini.</p>
        </div>

        <!-- Activity Timeline / List -->
        <div class="max-w-4xl mx-auto bg-surface-container-lowest shadow-sm border border-surface-variant p-stack-md rounded-2xl">
            <div class="relative before:content-[''] before:absolute before:inset-y-0 before:left-[19px] before:w-[2px] before:bg-outline-variant before:z-0 ml-1">
                <?php foreach ($activities as $act): ?>
                    <div class="relative pl-10 py-4 group hover:bg-surface/50 rounded-xl transition-colors -ml-1 pr-3 z-10">
                        <div class="absolute w-10 h-10 rounded-full left-0 bg-surface border-2 border-<?= $act[
                            'color'
                        ] ?> text-<?= $act[
     'color'
 ] ?> flex items-center justify-center top-4 shadow-sm group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;"><?= e(
                                $act['icon'],
                            ) ?></span>
                        </div>
                        
                        <div class="bg-surface-container p-stack-sm rounded-lg border border-surface-variant shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                            <div class="flex justify-between items-start gap-4 mb-1">
                                <div class="font-body-sm text-on-surface max-w-[85%]">
                                    <a href="?route=/admin/user/detail&id=1" class="font-label-md font-bold text-on-surface hover:text-primary hover:underline"><?= e(
                                        $act['user'],
                                    ) ?></a> 
                                    <span class="bg-<?= $act['color'] ?>/10 text-<?= $act[
    'color'
] ?> px-1.5 py-0.5 rounded text-[10px] uppercase font-bold mx-1 border border-<?= $act['color'] ?>/20"><?= e(
    $act['role'],
) ?></span> 
                                    <?= e(
                                        $act['action'],
                                    ) ?> pada <a href="?route=/admin/course/detail&id=101" class="font-label-sm font-bold text-on-surface-variant/80 italic hover:text-primary hover:underline">"<?= e(
     $act['target'],
 ) ?>"</a>
                                </div>
                                <span class="font-label-sm text-[10px] text-on-surface-variant shrink-0 bg-surface px-2 py-1 rounded-full border border-outline-variant/30"><?= e(
                                    $act['time'],
                                ) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 pt-4 border-t border-outline-variant text-center">
                <button class="font-label-md text-primary hover:bg-primary/10 px-4 py-2 rounded-lg transition-colors">Tampilkan Lebih Banyak (Load More)</button>
            </div>
        </div>
        
    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

</body>
</html>
