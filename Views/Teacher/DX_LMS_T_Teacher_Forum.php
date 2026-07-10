<?php
/**
 * Tampilan Forum Diskusi Pengajar (Teacher Forum).
 *
 * Mengacu pada forum_diskusi_teacher_aligned_with_student_view/code.html
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <?php
    $page_title = 'Forum Diskusi - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
            min-height: max(884px, 100dvh);
        }
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col font-sans antialiased pb-24">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-surface-variant docked full-width top-0 sticky z-50 flex justify-between items-center px-container-margin h-14 w-full transition-colors duration-200">
        <button onclick="history.back()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low active:scale-95 text-on-surface-variant transition-transform">
            <span class="material-symbols-outlined text-primary text-[28px]" data-icon="arrow_back">arrow_back</span>
        </button>
        <div class="font-headline-md text-headline-md font-bold tracking-tight text-primary">LMS</div>
        <button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low active:scale-95 text-on-surface-variant transition-transform relative">
            <span class="material-symbols-outlined text-primary text-[28px]" data-icon="notifications">notifications</span>
            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
        </button>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto px-container-margin py-stack-md pb-24">
        <div class="relative w-full h-[200px] bg-surface-container-highest overflow-hidden group mb-stack-lg rounded-xl">
            <div class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 bg-secondary flex items-center justify-center">
                 <!-- Placeholder for Hero Image -->
                 <span class="material-symbols-outlined text-white text-6xl">forum</span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-full p-container-margin">
                <h1 class="font-headline-lg text-headline-lg text-white mb-1 drop-shadow-md">Course K3</h1>
                <p class="font-body-md text-body-md text-white/90">Pelatihan Keselamatan Kerja Dasar</p>
            </div>
        </div>
        
        <div class="mb-stack-lg">
            <h2 class="font-headline-lg text-headline-lg text-on-surface mb-stack-sm">Forum Diskusi</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Bergabunglah dalam diskusi untuk memperdalam pemahaman materi.</p>
        </div>

        <!-- Section 1 -->
        <div class="mb-stack-lg">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-stack-md border-l-4 border-primary pl-3 py-1">Sesi 1: Pengenalan 5S</h3>
            <div class="space-y-stack-md">
                <!-- Discussion Card 1 -->
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] p-stack-md border border-surface-variant active:scale-[0.98] transition-transform cursor-pointer">
                    <div class="flex justify-between items-start mb-base">
                        <h4 class="font-label-md text-label-md font-bold text-on-surface">Tanya Jawab K3</h4>
                        <span class="bg-primary-container text-on-primary-container font-label-sm text-label-sm px-2 py-1 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">chat</span> 12
                        </span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mb-stack-sm">
                        Apakah ada standar khusus untuk penempatan alat pelindung diri di area produksi perakitan?
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-secondary text-on-secondary flex items-center justify-center font-label-sm text-label-sm">
                            B
                        </div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Budi Santoso • 2 jam lalu</span>
                    </div>
                </div>

                <!-- Discussion Card 2 -->
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] p-stack-md border border-surface-variant active:scale-[0.98] transition-transform cursor-pointer">
                    <div class="flex justify-between items-start mb-base">
                        <h4 class="font-label-md text-label-md font-bold text-on-surface">Implementasi Seiri</h4>
                        <span class="bg-surface-container-high text-on-surface font-label-sm text-label-sm px-2 py-1 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">chat</span> 5
                        </span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mb-stack-sm">
                        Bagaimana cara memilah barang yang jarang digunakan namun bernilai tinggi di gudang?
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-tertiary text-on-tertiary flex items-center justify-center font-label-sm text-label-sm">
                            S
                        </div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Siti Aminah • 5 jam lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2 -->
        <div class="mb-stack-lg">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-stack-md border-l-4 border-secondary pl-3 py-1">Sesi 2: Prosedur Evakuasi</h3>
            <div class="space-y-stack-md">
                <!-- Discussion Card 3 -->
                <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] p-stack-md border border-surface-variant active:scale-[0.98] transition-transform cursor-pointer">
                    <div class="flex justify-between items-start mb-base">
                        <h4 class="font-label-md text-label-md font-bold text-on-surface">Jalur Evakuasi Area B</h4>
                        <span class="bg-surface-container-high text-on-surface font-label-sm text-label-sm px-2 py-1 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">chat</span> 0
                        </span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mb-stack-sm">
                        Mengingat ada perbaikan jalur di Area B, ke mana titik kumpul alternatif saat keadaan darurat?
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-secondary text-on-secondary flex items-center justify-center font-label-sm text-label-sm">
                            A
                        </div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Agus Pratama • 1 hari lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Action Button for New Topic -->
        <div class="pt-4 flex justify-center pb-8">
            <button class="flex items-center gap-2 bg-white border border-secondary text-secondary px-6 py-3 rounded-full font-label-md text-label-md hover:bg-secondary-fixed/30 active:scale-95 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[20px]" data-icon="add_comment">add_comment</span> Mulai Topik Baru
            </button>
        </div>
    </main>

    <?php 
    $active_tab = 'course';
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>
</body>
</html>
