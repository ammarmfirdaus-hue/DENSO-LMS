<?php
/**
 * Tampilan Detail Pengelolaan Materi (Teacher Course Material).
 *
 * Mengacu pada detail_pengelolaan_materi_teacher/code.html
 * Menampilkan daftar materi dan form tambah/update materi dalam satu halaman.
 */
?>
<!DOCTYPE html>
<html class="light" lang="id" style="">
<head>
    <?php
    $page_title = 'Detail Pengelolaan Materi - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body {
            background-color: theme('colors.background');
            color: theme('colors.on-background');
            min-height: max(884px, 100dvh);
        }
    </style>
</head>
<body class="antialiased min-h-screen pb-24">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant fixed top-0 w-full z-50 transition-all duration-200">
        <div class="flex justify-between items-center w-full px-container-margin h-14">
            <button onclick="history.back()" aria-label="Kembali" class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-all duration-200 active:scale-95">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 class="font-headline-sm text-headline-sm text-on-surface text-center flex-1 mx-4 truncate"><br></h1>
            <button aria-label="Notifikasi" class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-all duration-200 active:scale-95">
            </button>
        </div>
    </header>

    <main class="pt-20 px-container-margin max-w-3xl mx-auto space-y-stack-lg">
        <!-- Info Sesi -->
        <section class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">topic</span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Sesi 1: Pengenalan 5S</h2>
                        <span class="px-2 py-1 bg-tertiary-container/10 text-tertiary-container rounded-full font-label-sm text-label-sm border border-tertiary-container/20">Aktif</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-4">
                        Pemahaman dasar mengenai metodologi 5S (Seiri, Seiton, Seiso, Seiketsu, Shitsuke) untuk menciptakan lingkungan kerja yang terorganisir dan efisien di area produksi LMS.
                    </p>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-surface-variant text-[20px]">description</span>
                            <span class="font-label-md text-label-md text-on-surface">3 Materi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-surface-variant text-[20px]">play_circle</span>
                            <span class="font-label-md text-label-md text-on-surface">1 Video</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Daftar Materi -->
        <section class="space-y-stack-md">
            <h3 class="font-headline-sm text-headline-sm text-on-surface border-b border-surface-variant pb-2">Daftar Materi Saat Ini</h3>
            <div class="space-y-stack-sm">
                <!-- Item 1 -->
                <div class="bg-surface-container-lowest rounded-lg p-3 border border-surface-variant shadow-[0_2px_8px_0_rgba(0,0,0,0.03)] flex items-center gap-4 hover:border-outline-variant transition-colors group">
                    <div class="w-10 h-10 rounded bg-error-container text-on-error-container flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">picture_as_pdf</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-label-md text-label-md text-on-surface truncate"><?= e($material['file_name'] ?? 'Modul_Dasar_5S_LMS.pdf') ?></p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">Dokumen PDF • <?= e($material['size'] ?? '2.4 MB') ?></p>
                    </div>
                    <div class="flex items-center gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                        <button aria-label="Edit" class="p-2 text-secondary hover:bg-secondary-container/20 rounded-full transition-colors">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button aria-label="Hapus" class="p-2 text-error hover:bg-error-container/20 rounded-full transition-colors">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
                <!-- Item 2 -->
                <div class="bg-surface-container-lowest rounded-lg p-3 border border-surface-variant shadow-[0_2px_8px_0_rgba(0,0,0,0.03)] flex items-center gap-4 hover:border-outline-variant transition-colors group">
                    <div class="w-10 h-10 rounded bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">play_circle</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-label-md text-label-md text-on-surface truncate">Video_Implementasi_Seiri.mp4</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">Video URL • Menit 05:20</p>
                    </div>
                    <div class="flex items-center gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                        <button aria-label="Edit" class="p-2 text-secondary hover:bg-secondary-container/20 rounded-full transition-colors">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button aria-label="Hapus" class="p-2 text-error hover:bg-error-container/20 rounded-full transition-colors">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tambah/Update Konten -->
        <section class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant mt-stack-lg">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-stack-md">Tambah Konten Baru</h3>
            <form class="space-y-stack-md">
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-2" for="judulMateri">Judul Materi</label>
                    <input class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent transition-all placeholder:text-on-surface-variant/50" id="judulMateri" placeholder="Masukkan judul materi yang jelas" type="text">
                </div>
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-2" for="tipeMateri">Tipe Materi</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent transition-all appearance-none pr-10" id="tipeMateri">
                            <option value="dokumen">Dokumen (PDF/Word/Excel)</option>
                            <option value="video">Video URL</option>
                            <option value="teks">Teks / Artikel</option>
                        </select>
                    </div>
                </div>
                <!-- Area Upload (Conditional based on select, showing file upload by default) -->
                <div class="border-2 border-dashed border-outline-variant rounded-xl p-stack-lg text-center bg-surface-container-low/50 hover:bg-surface-container-low transition-colors cursor-pointer group">
                    <div class="w-12 h-12 bg-primary-fixed text-on-primary-fixed rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">upload_file</span>
                    </div>
                    <p class="font-label-md text-label-md text-on-surface mb-1">Klik untuk mengunggah file atau seret file ke sini</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Mendukung format PDF, DOCX, XLSX (Maks 10MB)</p>
                    <input accept=".pdf,.doc,.docx,.xls,.xlsx" class="hidden" type="file">
                </div>
            </form>
        </section>

        <!-- Tombol Aksi Bawah -->
        <div class="pt-stack-md flex gap-3 pb-24 w-full">
            <button class="flex-1 px-6 py-3 bg-surface-container rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-highest transition-colors" type="button" onclick="history.back()">
                Batal
            </button>
            <button class="flex-[2] px-6 py-3 bg-primary text-on-primary rounded-lg font-label-md text-label-md shadow-md hover:bg-primary/90 transition-colors flex items-center justify-center gap-2" type="button">
                <span class="material-symbols-outlined text-[20px]">save</span>
                Simpan Perubahan
            </button>
        </div>
    </main>
    <?php 
    $active_tab = 'course'; 
    include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; 
    ?>
</body>
</html>
