<?php
/**
 * Tampilan Manajemen User Admin DLS (DLS Admin User View).
 */

$header_title = 'Kelola User';
$active_tab = 'user';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola User LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar (Desktop) / Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Data Karyawan & User</h1>
                <p class="font-body-md text-on-surface-variant">Atur pendaftaran, deaktivasi, serta pemberian akses Role LMS.</p>
            </div>
            
            <button class="bg-primary text-on-primary font-label-md px-4 py-2.5 rounded-lg active:scale-95 transition-transform hover:shadow-md flex items-center gap-2" onclick="alert('Fitur tambah form muncul!')">
                <span class="material-symbols-outlined">person_add</span> Tambah User
            </button>
        </div>

        <!-- Filter Tools (Dummy UI) -->
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm border border-surface-variant mb-stack-md flex gap-2 overflow-x-auto flex-nowrap">
            <button class="border border-outline-variant bg-surface px-3 py-1.5 rounded-lg font-label-sm flex items-center gap-2 hover:bg-surface-variant min-w-max">
                <span class="material-symbols-outlined text-[18px]">filter_list</span> Departemen
            </button>
            <button class="border border-outline-variant bg-surface px-3 py-1.5 rounded-lg font-label-sm flex items-center gap-2 hover:bg-surface-variant min-w-max">
                <span class="material-symbols-outlined text-[18px]">badge</span> Golongan
            </button>
            <button class="border border-outline-variant bg-surface px-3 py-1.5 rounded-lg font-label-sm flex items-center gap-2 hover:bg-surface-variant min-w-max">
                <span class="material-symbols-outlined text-[18px]">security</span> Role Utama
            </button>
            <div class="border-l border-outline-variant mx-1"></div>
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute top-1.5 left-2 text-on-surface-variant text-[20px]">search</span>
                <input type="text" placeholder="Cari nama atau NPK..." class="w-full bg-surface pl-9 pr-3 py-1.5 rounded-lg border border-outline-variant font-body-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary h-[34px]">
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden mb-stack-lg table-responsive">
            <table class="w-full text-left font-body-md min-w-[800px]">
                <thead class="bg-surface-container-high border-b border-surface-variant">
                    <tr>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface w-[60px]">ID</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface">Nama & NPK</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface">Jabatan (Dept)</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface">Role Status</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface">Akses Tambahan</th>
                        <th class="px-4 py-3 font-label-md font-bold text-on-surface text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-surface transition-colors group">
                                <td class="px-4 py-3 text-on-surface-variant font-label-sm text-[12px] bg-surface-container-lowest">USR-<?= $user[
                                    'id'
                                ] ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-label-md font-bold text-on-surface"><?= e($user['name']) ?></div>
                                    <div class="font-body-sm text-on-surface-variant">NPK: <?= e($user['npk']) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-label-sm text-on-surface"><?= e($user['jabatan']) ?> - <?= e(
     $user['golongan'],
 ) ?></div>
                                    <div class="font-body-sm text-on-surface-variant"><?= e(
                                        $user['departemen'],
                                    ) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 font-label-md <?= $user['role'] ===
                                    'Admin'
                                        ? 'text-error'
                                        : ($user['role'] === 'Teacher'
                                            ? 'text-secondary'
                                            : 'text-on-surface') ?>">
                                        <span class="material-symbols-outlined text-[16px]"><?= $user['role'] ===
                                        'Admin'
                                            ? 'security'
                                            : ($user['role'] === 'Teacher'
                                                ? 'history_edu'
                                                : 'person') ?></span>
                                        <?= e($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (count($user['access']) > 0): ?>
                                        <div class="flex gap-1 flex-wrap">
                                            <?php foreach ($user['access'] as $acc): ?>
                                                <span class="bg-secondary-container text-on-secondary-container text-[10px] px-2 py-0.5 rounded-sm font-medium border border-secondary/20"><?= e(
                                                    $acc,
                                                ) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-on-surface-variant/50 text-[12px] italic">Hanya Utama</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button class="hover:bg-surface-variant text-on-surface-variant p-2 rounded-full transition-colors mx-1" aria-label="Edit Profile">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button class="hover:bg-primary/10 text-primary p-2 rounded-full transition-colors mx-1" aria-label="Manage Role" onclick="window.location.href='?route=/admin/role'">
                                        <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center p-6 text-on-surface-variant">Tidak ada data ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
