<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'Mulai Topik Baru - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-background text-on-background antialiased min-h-screen flex flex-col font-body-lg pt-safe">
    <!-- Top Navigation Area -->
    <header class="bg-surface border-b border-outline-variant sticky top-0 z-10">
        <div class="flex items-center w-full px-container-margin h-14 max-w-3xl mx-auto">
            <a href="?route=/student/course/detail&tab=forum" class="w-10 h-10 flex items-center justify-center text-on-surface-variant hover:bg-surface-container-low rounded-full transition-colors active:scale-95">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="font-headline-sm text-headline-sm font-bold tracking-tight text-on-background flex-1 text-center pr-10">Mulai Topik Baru</h1>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-grow px-container-margin py-stack-lg max-w-3xl mx-auto w-full">
        <!-- Form Container -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] p-6 space-y-6">
            <!-- Context Hint -->
            <div class="bg-surface-container-low rounded-lg p-4 flex items-start space-x-3">
                <span class="material-symbols-outlined text-secondary">info</span>
                <p class="font-body-md text-body-md text-on-surface-variant pt-0.5">
                    Buat diskusi baru untuk membagikan pertanyaan, insight, atau masukan terkait materi kursus. Pastikan topik relevan dengan sesi yang dipilih.
                </p>
            </div>
            
            <form class="space-y-6">
                <!-- Sesi Dropdown -->
                <div class="space-y-2">
                    <label class="block font-label-md text-label-md text-on-surface" for="sesi">Pilih Sesi</label>
                    <div class="relative">
                        <select class="w-full appearance-none bg-surface border border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary transition-colors" id="sesi" name="sesi">
                            <option disabled="" selected="" value="">Pilih Sesi Terkait...</option>
                            <option value="1">Sesi 1: Pengenalan K3</option>
                            <option value="2">Sesi 2: Prosedur Keadaan Darurat</option>
                            <option value="3">Sesi 3: Instruksi Kerja</option>
                            <option value="umum">Diskusi Umum Course</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-on-surface-variant">
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                    </div>
                </div>

                <!-- Judul Topik -->
                <div class="space-y-2">
                    <label class="block font-label-md text-label-md text-on-surface" for="judul">Judul Topik</label>
                    <input class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary transition-colors" id="judul" name="judul" placeholder="Contoh: Pertanyaan mengenai SOP di Jalur Perakitan" type="text">
                </div>

                <!-- Pesan/Konten -->
                <div class="space-y-2">
                    <label class="block font-label-md text-label-md text-on-surface" for="pesan">Pesan</label>
                    <textarea class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary transition-colors resize-y min-h-[120px]" id="pesan" name="pesan" placeholder="Tuliskan pertanyaan atau pendapat Anda di sini secara detail..." rows="6"></textarea>
                </div>

                <!-- Attachment Area -->
                <div class="space-y-2">
                    <span class="block font-label-md text-label-md text-on-surface">Lampiran (Opsional)</span>
                    <div class="border-2 border-dashed border-outline-variant rounded-lg p-6 bg-surface hover:bg-surface-container-low transition-colors text-center cursor-pointer flex flex-col items-center justify-center space-y-2">
                        <span class="material-symbols-outlined text-secondary text-3xl">upload_file</span>
                        <p class="font-body-md text-body-md text-on-surface-variant">Klik untuk mengunggah atau tarik file ke sini</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant/70">Maksimal ukuran file: 5MB (JPG, PNG, PDF)</p>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Bottom Action Bar (Sticky) -->
    <div class="sticky bottom-0 bg-surface border-t border-outline-variant p-container-margin pb-safe z-10 w-full">
        <div class="max-w-3xl mx-auto flex space-x-4 justify-between">
            <a href="?route=/student/course/detail&tab=forum" class="px-6 py-3 border border-outline-variant rounded-xl font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-high transition-colors">
                Batal
            </a>
            <button onclick="alert('Topik berhasil diposting!'); window.location.href='?route=/student/course/detail&tab=forum';" class="px-6 py-3 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-[0_4px_12px_0_rgba(226,6,19,0.2)]" type="button">
                Posting Topik
                <span class="material-symbols-outlined ml-2 text-[18px]">send</span>
            </button>
        </div>
    </div>
</body>
</html>
