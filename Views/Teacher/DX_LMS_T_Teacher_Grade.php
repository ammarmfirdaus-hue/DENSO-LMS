<?php
/**
 * Tampilan Pengelolaan Nilai (Teacher Grade).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Nilai - LMS Teacher';
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
    $header_title = 'Pengelolaan Nilai';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[72px]">
        <div class="max-w-5xl mx-auto space-y-stack-xl">
            
            <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-end bg-surface-container-lowest p-stack-md rounded-2xl border border-surface-variant">
                <div>
                    <h2 class="font-headline-sm font-bold text-on-surface">Data Penilaian Course</h2>
                    <p class="font-body-sm text-on-surface-variant mt-1 max-w-lg">Pastikan semua essay dan tugas akhir telah direview sebelum mencetak rekap nilai peserta.</p>
                </div>
                <div class="flex gap-2">
                    <button class="bg-surface border border-outline-variant text-on-surface font-label-md font-bold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-surface-container transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">download</span> Export Excel
                    </button>
                </div>
            </div>

            <!-- Grade List Table -->
            <div class="bg-surface-container-lowest border border-surface-variant rounded-xl overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,0.02)]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap min-w-[700px]">
                        <thead class="bg-surface-container-low text-on-surface-variant font-label-sm border-b border-surface-variant">
                            <tr>
                                <th class="px-4 py-3 font-semibold rounded-tl-xl">Nama Peserta (NPK)</th>
                                <th class="px-4 py-3 font-semibold text-center">Post-Test</th>
                                <th class="px-4 py-3 font-semibold text-center">Final Assessment</th>
                                <th class="px-4 py-3 font-semibold">Status Kelulusan</th>
                                <th class="px-4 py-3 font-semibold rounded-tr-xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-variant font-body-sm">
                            <?php foreach ($students ?? [] as $student): ?>
                            <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-title-sm font-bold text-on-surface"><?= e($student['name']) ?></p>
                                    <p class="font-label-sm text-on-surface-variant text-[11px]">NPK: <?= e(
                                        $student['npk'],
                                    ) ?></p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-body-md <?= $student['post_test'] >= 75
                                        ? 'text-success font-semibold'
                                        : 'text-error' ?>">
                                        <?= e((string) $student['post_test']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-body-md <?= $student['final_assessment'] >= 75
                                        ? 'text-success font-semibold'
                                        : 'text-warning font-semibold' ?>">
                                        <?= e((string) $student['final_assessment']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $badgeBg = 'bg-surface-container-high text-on-surface-variant';
                                    if ($student['status'] === 'Lulus') {
                                        $badgeBg = 'bg-success-container/70 text-on-success-container';
                                    }
                                    if ($student['status'] === 'Tidak Lulus') {
                                        $badgeBg = 'bg-error-container/70 text-on-error-container';
                                    }
                                    if ($student['status'] === 'Menunggu Validasi') {
                                        $badgeBg = 'bg-warning-container/70 text-on-warning-container';
                                    }
                                    ?>
                                    <span class="font-label-sm text-[11px] font-bold px-2.5 py-1 rounded-full <?= $badgeBg ?>">
                                        <?= e($student['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($student['status'] === 'Menunggu Validasi'): ?>
                                    <button class="bg-primary text-on-primary font-label-sm py-1.5 px-3 rounded text-[11px] font-bold shadow-sm hover:bg-primary-600 transition-colors">
                                        Review Essay
                                    </button>
                                    <?php else: ?>
                                    <button class="text-secondary font-label-sm hover:underline font-semibold text-[12px]">
                                        Lihat Detail
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
