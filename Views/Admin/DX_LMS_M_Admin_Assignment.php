<?php
/**
 * Tampilan Pengaturan Penugasan (Assignment Flow) Admin DLS.
 */

$header_title = 'Kelola Penugasan';
$active_tab = 'assignment';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Assignment LMS';
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
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-xl">
            <div>
                <h1 class="font-headline-lg font-bold text-on-surface">Pusat Penugasan Course</h1>
                <p class="font-body-md text-on-surface-variant">Assign target partisipan per course sesuai jabatan dan golongan.</p>
            </div>
            
            <button class="bg-secondary text-on-secondary font-label-md px-4 py-2.5 rounded-lg active:scale-95 transition-transform hover:shadow-md flex items-center gap-2" onclick="Swal.fire({title: 'Buat Penugasan Baru', html: '<div class=\'text-left mt-4\'><label class=\'font-label-sm text-on-surface mb-2 block\'>Pilih Course Target</label><select class=\'swal2-select w-full m-0 p-2\'><option>Dasar Keselamatan Kerja & K3</option><option>Pengenalan ISO 9001:2015</option></select></div><div class=\'text-left mt-4\'><label class=\'font-label-sm text-on-surface mb-2 block\'>Tentukan Audiens / Area</label><select class=\'swal2-select w-full m-0 p-2\'><option>Seluruh Operator Line A</option><option>Tim Maintenance (Staff & Leader)</option></select></div>', confirmButtonText: 'Beri Penugasan', customClass: {confirmButton: 'bg-primary text-white rounded-lg px-6 py-2 border-0', cancelButton: 'bg-surface border border-outline-variant text-on-surface px-6 py-2 rounded-lg ml-2'}, showCancelButton: true, cancelButtonText: 'Batal'})">
                <span class="material-symbols-outlined">add_task</span> Buat Penugasan Baru
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-gutter">
            
            <!-- Left Side: Table of Recent assignments -->
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden">
                <div class="p-stack-md border-b border-surface-variant flex justify-between items-center">
                    <h2 class="font-headline-sm font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined">list_alt</span> Daftar Distribusi Aktif
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left font-body-md min-w-[600px]">
                        <thead class="bg-surface-container-high border-b border-surface-variant">
                            <tr>
                                <th class="px-4 py-3 font-label-md font-bold text-on-surface">Course</th>
                                <th class="px-4 py-3 font-label-md font-bold text-on-surface">Target Mapping</th>
                                <th class="px-4 py-3 font-label-md font-bold text-on-surface">Dibuat Oleh</th>
                                <th class="px-4 py-3 font-label-md font-bold text-on-surface text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-variant">
                            <?php foreach ($assignments as $asg): ?>
                                <tr class="hover:bg-surface group transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="font-label-md font-bold text-primary group-hover:underline cursor-pointer truncate max-w-[200px]" onclick="window.location.href='?route=/admin/course/detail&id=101'"><?= e(
                                            $asg['course_title'],
                                        ) ?></div>
                                        <div class="font-body-sm text-on-surface-variant mt-1"><?= e(
                                            $asg['date'],
                                        ) ?></div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-label-sm text-on-surface">By: <span class="uppercase tracking-wide font-bold text-secondary"><?= e(
                                            $asg['target_type'],
                                        ) ?></span></div>
                                        <div class="font-body-sm text-on-surface-variant truncate"><?= e(
                                            $asg['target_value'],
                                        ) ?></div>
                                    </td>
                                    <td class="px-4 py-4 text-on-surface font-label-md"><?= e(
                                        $asg['assigned_by'],
                                    ) ?></td>
                                    <td class="px-4 py-4 text-center">
                                        <?php if ($asg['status'] === 'Active'): ?>
                                            <span class="bg-success-container/30 text-success border border-success/30 px-3 py-1 text-[11px] font-bold rounded-full uppercase tracking-wider">Tersalurkan</span>
                                        <?php else: ?>
                                            <span class="bg-warning-container text-on-warning-container border border-warning/50 px-3 py-1 text-[11px] font-bold rounded-full flex gap-1 items-center justify-center uppercase tracking-wider w-fit mx-auto"><span class="material-symbols-outlined text-[14px]">pending_actions</span> Draft</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right side: Assignment quick context (Flow Rules Diagram) -->
            <div class="bg-surface-container-high rounded-xl border border-outline-variant p-stack-md flex flex-col gap-4">
                <h3 class="font-label-lg font-bold text-on-surface flex items-center gap-2 border-b border-outline-variant pb-2">
                    <span class="material-symbols-outlined text-secondary">info</span> Skema Assignment
                </h3>
                
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center">1</div>
                    <p class="font-body-sm text-on-surface flex-1">Pilih <b>Course</b> dari master data.</p>
                </div>
                
                <div class="flex justify-center -my-2"><span class="material-symbols-outlined text-outline">arrow_downward</span></div>
                
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center">2</div>
                    <p class="font-body-sm text-on-surface flex-1">Tentukan filter <b>Jabatan & Golongan</b> untuk Target.</p>
                </div>

                <div class="flex justify-center -my-2"><span class="material-symbols-outlined text-outline">arrow_downward</span></div>

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center">3</div>
                    <p class="font-body-sm text-on-surface flex-1">Admin konfirmasi <b>Publish</b> ke akun Student.</p>
                </div>
                
                <button class="mt-stack-md font-label-md text-primary border border-outline-variant bg-surface hover:bg-surface-variant w-full py-2 rounded-lg transition-colors" onclick="window.location.href='?route=/admin/user'">Target Spesifik User</button>
            </div>
            
        </div>
    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
