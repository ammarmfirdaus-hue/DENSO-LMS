<?php
/**
 * Tampilan Detail Pengelolaan Materi (Teacher Course Material).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Materi - LMS Teacher';
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

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'Kelola Materi';
        $show_back_button = true;
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header -->
    <div class="hidden lg:flex lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md items-center gap-3">
        <button onclick="history.back()" class="p-2 -ml-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95 flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <h1 class="font-headline-6 font-bold text-on-surface">Kelola Materi</h1>
    </div>

    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-4 lg:pt-6">
        <div class="max-w-4xl mx-auto space-y-stack-xl">
            <!-- Info Sesi -->
            <section class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">topic</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <h2 class="font-title-lg font-bold text-on-surface leading-tight">Sesi 1: Pengenalan 5S</h2>
                            <span class="px-2 py-0.5 bg-tertiary-container/30 text-tertiary rounded-full font-label-sm text-[11px] font-bold">Aktif</span>
                        </div>
                        <p class="font-body-sm text-on-surface-variant mb-4">
                            Materi dasar untuk menciptakan lingkungan kerja yang terorganisir.
                        </p>
                        <div class="flex items-center gap-4 text-on-surface-variant text-label-sm">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">description</span>
                                <span>3 Materi</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">play_circle</span>
                                <span>1 Video</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Daftar Materi -->
            <section class="space-y-stack-md">
                <div class="flex justify-between items-center border-b border-surface-variant pb-2">
                    <h3 class="font-headline-sm font-bold text-on-surface">Daftar Materi Saat Ini</h3>
                    <a href="?route=/teacher/course/add-material" class="text-primary font-label-md font-bold px-3 py-1.5 hover:bg-surface-container rounded-lg transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">add</span> Tambah Data
                    </a>
                </div>
                
                <div class="space-y-3">
                    <!-- Item 1 -->
                    <div class="bg-surface-container-lowest rounded-xl p-3 border border-surface-variant shadow-sm flex items-center gap-3 hover:border-outline-variant transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-error-container text-on-error-container flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">picture_as_pdf</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-label-md font-bold text-on-surface truncate">Modul_Dasar_5S_LMS.pdf</p>
                            <p class="font-body-sm text-on-surface-variant text-[11px]">Dokumen PDF • 2.4 MB</p>
                        </div>
                        <div class="flex items-center md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                            <a href="?route=/teacher/course/add-material" class="p-2 text-secondary hover:bg-secondary-container/30 rounded-full transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                            <button class="p-2 text-error hover:bg-error-container/30 rounded-full transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="bg-surface-container-lowest rounded-xl p-3 border border-surface-variant shadow-sm flex items-center gap-3 hover:border-outline-variant transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">play_circle</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-label-md font-bold text-on-surface truncate">Video_Implementasi_Seiri.mp4</p>
                            <p class="font-body-sm text-on-surface-variant text-[11px]">Video • Menit 05:20</p>
                        </div>
                        <div class="flex items-center md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                            <a href="?route=/teacher/course/add-material" class="p-2 text-secondary hover:bg-secondary-container/30 rounded-full transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                            <button class="p-2 text-error hover:bg-error-container/30 rounded-full transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
