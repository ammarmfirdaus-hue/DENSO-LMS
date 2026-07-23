<?php
/**
 * Tampilan Pengaturan Role Admin DLS (DLS Admin Role View).
 */

$header_title = 'Kelola Role';
$active_tab = 'role';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Akses & Role LMS';
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
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Hierarki Role & Permission</h1>
                <p class="font-body-md text-on-surface-variant">Atur batasan hak akses antar profil pengguna.</p>
            </div>
            <button class="hidden lg:block border text-secondary font-label-md px-4 py-2.5 rounded-lg transition-transform hover:shadow-md" disabled>
                Fitur Terkunci (MVP)
            </button>
        </div>

        <!-- Role Architecture Visualizer -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant p-stack-lg mb-stack-2xl">
            <h2 class="font-headline-sm font-bold text-on-surface mb-stack-md flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_tree</span>
                Struktur Keamanan
            </h2>
            <div class="space-y-4">
                <?php foreach ($roles as $r): ?>
                    <div class="flex flex-col md:flex-row gap-4 md:items-center p-4 border border-outline-variant rounded-lg hover:border-primary/50 transition-colors bg-surface relative">
                        <!-- Connecting Line for hierarchy feeling on desktop -->
                        <?php if ($r['level'] > 1): ?>
                            <div class="hidden md:block absolute left-[30px] -top-4 bottom-[50%] w-0.5 bg-outline-variant z-0"></div>
                        <?php endif; ?>
                        
                        <div class="w-12 h-12 rounded-full relative z-10 flex items-center justify-center font-bold shadow-sm shrink-0 <?= $r[
                            'name'
                        ] === 'Super Admin'
                            ? 'bg-error text-on-error'
                            : ($r['name'] === 'Admin'
                                ? 'bg-primary text-on-primary'
                                : ($r['name'] === 'Teacher'
                                    ? 'bg-secondary-container text-on-secondary-container'
                                    : 'bg-surface-variant text-on-surface')) ?>">
                            <?= $r['level'] ?>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-label-lg font-bold text-on-surface"><?= e($r['name']) ?></h3>
                            <p class="font-body-sm text-on-surface-variant"><?= e($r['description']) ?></p>
                        </div>
                        <div class="flex gap-4 md:items-center w-full md:w-auto">
                            <div class="bg-surface-variant rounded-lg px-3 py-1 flex flex-col items-center min-w-[80px]">
                                <span class="font-headline-sm font-bold text-on-surface"><?= e(
                                    (string) $r['users_count'],
                                ) ?></span>
                                <span class="font-label-sm text-[10px] text-on-surface-variant">Akun Aktif</span>
                            </div>
                            <button class="border border-outline-variant hover:bg-surface-variant text-on-surface-variant p-2 rounded-lg transition-colors ms-auto" aria-label="Setting Permissions">
                                <span class="material-symbols-outlined">settings_applications</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Important Warning Note -->
            <div class="mt-stack-lg p-stack-md rounded-lg bg-error/10 border border-error/20 flex gap-3 text-error">
                <span class="material-symbols-outlined pointer-events-none mt-0.5">warning</span>
                <div>
                    <h4 class="font-label-md font-bold mb-1">Aturan Baku (Hardcoded)</h4>
                    <p class="font-body-sm text-on-surface-variant text-error/80">Student tidak dapat meregistrasi dirinya menjadi pengajar. Teacher tidak memiliki otoritas mengangkat murid ke role Teacher secara mandiri. Hanya pengguna berstatus Admin yang dapat memindahkan access level.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
