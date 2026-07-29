<?php
/**
 * Tampilan Tambah Materi (Teacher Course Add Material).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Tambah Materi - LMS Teacher';
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
        $header_title = 'Tambah Materi';
        $show_back_button = true;
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header -->
    <div class="hidden lg:flex lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md items-center gap-3">
        <button onclick="history.back()" class="p-2 -ml-2 text-primary hover:bg-surface-container-low rounded-full transition-all active:scale-95 flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <h1 class="font-headline-6 font-bold text-on-surface">Tambah Materi</h1>
    </div>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-4 lg:pt-6">
        <div class="max-w-2xl mx-auto">
            
            <form action="?route=/teacher/course/material" method="POST" class="bg-surface-container-lowest rounded-2xl p-stack-lg shadow-[0_4px_12px_0_rgba(0,0,0,0.03)] border border-surface-variant space-y-5">
                <div class="mb-2">
                    <p class="font-label-sm text-xs bg-secondary-container/20 text-secondary px-2 py-0.5 rounded-md inline-block">Sesi 1: Dasar Materi</p>
                    <h2 class="font-headline-sm font-bold text-on-surface mt-2">Form Tambah Materi</h2>
                </div>

                <!-- Tipe Materi -->
                <div class="space-y-1.5 border-t border-surface-variant pt-4">
                    <label class="font-label-md font-bold text-on-surface block mb-2">Tipe Materi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Option PDF -->
                        <label class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 border-primary bg-primary-container/10 cursor-pointer">
                            <input type="radio" name="tipe_materi" value="document" class="hidden" checked>
                            <span class="material-symbols-outlined text-primary text-[32px]">picture_as_pdf</span>
                            <span class="font-label-sm text-primary font-bold">Dokumen (PDF)</span>
                        </label>
                        <!-- Option Video -->
                        <label class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border border-surface-variant bg-surface hover:bg-surface-container-low cursor-pointer transition-colors">
                            <input type="radio" name="tipe_materi" value="video" class="hidden">
                            <span class="material-symbols-outlined text-on-surface-variant text-[32px]">play_circle</span>
                            <span class="font-label-sm text-on-surface-variant font-bold">Video Link</span>
                        </label>
                    </div>
                </div>

                <!-- Nama File / Judul Materi -->
                <div class="space-y-1.5">
                    <label class="font-label-md font-bold text-on-surface">Judul Materi</label>
                    <input type="text" class="w-full bg-surface border border-outline-variant rounded-xl p-3 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all font-body-md" placeholder="Contoh: Modul Dasar 01" required>
                </div>

                <!-- Unggah File (Simulasi) -->
                <div class="space-y-1.5">
                    <label class="font-label-md font-bold text-on-surface">Pilih File</label>
                    <div class="w-full border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center gap-2 bg-surface-container-lowest/50 hover:bg-surface-container-low transition-colors cursor-pointer">
                        <div class="w-12 h-12 bg-primary-container text-on-primary-container rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined">upload_file</span>
                        </div>
                        <span class="font-label-md text-on-surface font-semibold">Klik untuk memilih file PDF</span>
                        <span class="font-body-sm text-on-surface-variant">Maksimal ukuran 10MB</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="history.back()" class="flex-1 bg-surface-container border border-outline-variant text-on-surface px-4 py-3 rounded-lg font-label-md font-bold hover:bg-surface-container-high transition-colors text-center">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-primary text-on-primary px-4 py-3 rounded-lg font-label-md font-bold shadow-sm hover:bg-primary-600 transition-colors text-center flex justify-center items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">save</span> Simpan
                    </button>
                </div>
            </form>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
