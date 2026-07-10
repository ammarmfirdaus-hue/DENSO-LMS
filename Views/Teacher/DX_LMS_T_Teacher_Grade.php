<?php
/**
 * Tampilan Kelola Nilai (Teacher Grade).
 *
 * Mengacu pada kelola_nilai_teacher_updated_nav/code.html
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <?php
    $page_title = 'Kelola Nilai - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body {
            background-color: theme('colors.background');
            color: theme('colors.on-background');
            min-height: max(884px, 100dvh);
        }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md min-h-screen flex flex-col antialiased pb-24">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin h-14 sticky top-0 z-40 transition-all duration-200">
        <div class="flex items-center gap-2">
            <button onclick="history.back()" class="p-2 -ml-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 class="font-headline-sm text-headline-sm text-on-surface">Kelola Nilai</h1>
        </div>
        <button class="p-2 -mr-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95 relative">
            <span class="material-symbols-outlined">notifications</span>
            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
        </button>
    </header>

    <main class="flex-1 overflow-y-auto px-container-margin py-stack-md space-y-stack-lg pb-32">
        <!-- Course Summary Card -->
        <section class="bg-surface-container-lowest rounded-xl p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container-high relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
            <div class="flex justify-between items-start mb-4 pl-2">
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface mb-1">Sesi 1: Pengenalan 5S</h2>
                    <div class="flex items-center gap-2 text-tertiary-container bg-tertiary-container/10 px-2 py-1 rounded-full w-fit">
                        <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                        <span class="font-label-sm text-label-sm">Selesai (100% Peserta)</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="font-label-sm text-label-sm text-on-surface-variant block">Rata-rata Kelas</span>
                    <span class="font-headline-md text-headline-md text-primary">88<span class="font-body-md text-body-md text-on-surface-variant">/100</span></span>
                </div>
            </div>
        </section>

        <!-- Performance Overview (Bento Grid) -->
        <section class="grid grid-cols-3 gap-3">
            <div class="bg-surface-container-lowest rounded-xl p-3 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container-high flex flex-col items-center text-center justify-center">
                <span class="material-symbols-outlined text-secondary mb-1">groups</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">Total Peserta</span>
                <span class="font-headline-sm text-headline-sm text-on-surface mt-1">45</span>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-3 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container-high flex flex-col items-center text-center justify-center">
                <span class="material-symbols-outlined text-tertiary mb-1">trending_up</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">Tingkat Kelulusan</span>
                <span class="font-headline-sm text-headline-sm text-on-surface mt-1">95%</span>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-3 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container-high flex flex-col items-center text-center justify-center">
                <span class="material-symbols-outlined text-primary mb-1">workspace_premium</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">Nilai Tertinggi</span>
                <span class="font-headline-sm text-headline-sm text-on-surface mt-1">100</span>
            </div>
        </section>

        <!-- Search Bar -->
        <section>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                <input class="w-full bg-surface-container-lowest border border-surface-container-high rounded-xl py-3 pl-10 pr-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition-colors shadow-sm" placeholder="Cari nama atau departemen..." type="text">
            </div>
        </section>

        <!-- Participant Grades List -->
        <section class="space-y-3">
            <h3 class="font-label-md text-label-md text-on-surface-variant">Daftar Nilai Peserta</h3>
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container-high divide-y divide-surface-container-high">
                <!-- Entry 1 -->
                <div class="p-4 flex items-center justify-between hover:bg-surface-container-low transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center font-headline-sm text-headline-sm">
                            A
                        </div>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface">Ahmad Hidayat</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Production Dept.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="font-headline-sm text-headline-sm text-on-surface block">92</span>
                            <span class="font-label-sm text-label-sm text-tertiary">Lulus</span>
                        </div>
                        <button class="p-2 text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                        </button>
                    </div>
                </div>
                <!-- Entry 2 -->
                <div class="p-4 flex items-center justify-between hover:bg-surface-container-low transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center font-headline-sm text-headline-sm">
                            B
                        </div>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface">Budi Santoso</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">QC Dept.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="font-headline-sm text-headline-sm text-on-surface block">85</span>
                            <span class="font-label-sm text-label-sm text-tertiary">Lulus</span>
                        </div>
                        <button class="p-2 text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                        </button>
                    </div>
                </div>
                <!-- Entry 3 -->
                <div class="p-4 flex items-center justify-between hover:bg-surface-container-low transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center font-headline-sm text-headline-sm">
                            C
                        </div>
                        <div>
                            <p class="font-label-md text-label-md text-on-surface">Citra Lestari</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Maintenance Dept.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="font-headline-sm text-headline-sm text-error block">65</span>
                            <span class="font-label-sm text-label-sm text-error">Tidak Lulus</span>
                        </div>
                        <button class="p-2 text-on-surface-variant hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Primary Action Button -->
        <section class="pt-12">
            <button class="w-full bg-secondary text-on-secondary py-3 px-4 rounded-xl font-label-md text-label-md flex items-center justify-center gap-2 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] active:scale-95 transition-all">
                <span class="material-symbols-outlined" style="font-size: 20px;">download</span>
                Unduh Rekap Nilai
            </button>
        </section>
    </main>

    <?php 
    $active_tab = 'course';
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>
</body>
</html>
