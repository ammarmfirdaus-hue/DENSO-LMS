<?php
/**
 * Tampilan Tambah Materi (Teacher Add Material View).
 *
 * Form untuk mengunggah materi baru ke dalam sesi course.
 * Mengacu pada tambah_materi_teacher_view.png.
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">

<head>
    <?php
    $page_title = 'Tambah Materi - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>

<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
    <!-- TopAppBar -->
    <header class="fixed top-0 left-0 right-0 h-[72px] bg-surface/90 backdrop-blur-md z-40 px-4 flex items-center border-b border-outline-variant">
        <button onclick="history.back()" class="w-12 h-12 flex items-center justify-center rounded-full hover:bg-surface-container-high transition-colors text-on-surface">
            <span class="material-symbols-outlined text-[24px]">close</span>
        </button>
        <div class="ml-2 flex-1 min-w-0">
            <h1 class="font-title-lg font-semibold text-on-surface truncate">Tambah Materi Baru</h1>
        </div>
    </header>

    <main class="dls-main flex-1 overflow-y-auto pt-[88px] px-container-margin pb-[88px]">
        <form class="space-y-6">
            
            <!-- Judul Materi -->
            <div class="space-y-1">
                <label class="font-label-md font-semibold text-on-surface">Judul Materi <span class="text-error">*</span></label>
                <input type="text" placeholder="Masukkan judul materi" class="w-full bg-surface-container-lowest border border-outline rounded-xl px-4 py-3 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary font-body-md transition-shadow">
            </div>

            <!-- Deskripsi -->
            <div class="space-y-1">
                <label class="font-label-md font-semibold text-on-surface">Deskripsi (Opsional)</label>
                <textarea rows="4" placeholder="Tuliskan deskripsi singkat" class="w-full bg-surface-container-lowest border border-outline rounded-xl px-4 py-3 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary font-body-md transition-shadow resize-none"></textarea>
            </div>

            <!-- Tipe Materi -->
            <div class="space-y-1">
                <label class="font-label-md font-semibold text-on-surface">Tipe Materi <span class="text-error">*</span></label>
                <div class="relative">
                    <select class="w-full bg-surface-container-lowest border border-outline rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary font-body-md transition-shadow">
                        <option value="dokumen">Dokumen (PDF, PPTX, DOCX)</option>
                        <option value="video">Video (MP4)</option>
                        <option value="tautan">Tautan Web</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
                </div>
            </div>

            <!-- Upload File -->
            <div class="space-y-1">
                <label class="font-label-md font-semibold text-on-surface">Unggah Berkas <span class="text-error">*</span></label>
                <div class="border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center text-center bg-surface-container-lowest hover:bg-surface-container-low transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[40px] text-primary mb-2">cloud_upload</span>
                    <p class="font-label-lg font-semibold text-on-surface">Pilih file untuk diunggah</p>
                    <p class="font-body-sm text-on-surface-variant mt-1">Maks. ukuran 50MB</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 pb-24">
                <button type="button" onclick="history.back()" class="w-full py-3.5 bg-primary text-on-primary rounded-xl font-label-lg font-semibold shadow-sm hover:bg-primary-600 transition-colors">
                    Simpan Materi
                </button>
            </div>
            
        </form>
    </main>

    <?php 
    $active_tab = 'course'; 
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>

</body>
</html>
