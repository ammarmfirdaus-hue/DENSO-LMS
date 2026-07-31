<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Mulai Topik Baru - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }

        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Mulai Topik Baru';
    $show_back = true;
    $back_url = '?route=/student/course/detail&tab=forum';
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 flex flex-col min-w-0 pb-16 lg:pb-0 lg:ml-[240px] pt-[72px] px-container-margin w-full">
        <div class="max-w-3xl mx-auto w-full py-stack-lg">
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
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col-reverse sm:flex-row sm:space-x-4 sm:justify-end pt-4 border-t border-outline-variant mt-8 gap-y-4 sm:gap-y-0">
                        <a href="?route=/student/course/detail&tab=forum" class="px-6 py-3 border border-outline-variant rounded-xl font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-high transition-colors text-center flex-1 sm:flex-none">
                            Batal
                        </a>
                        <button onclick="Swal.fire({title: 'Berhasil!', text: 'Topik berhasil diposting!', icon: 'success', confirmButtonColor: '#b4000b'}).then(() => { window.location.href='?route=/student/course/detail&tab=forum'; });" class="px-6 py-3 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-[0_4px_12px_0_rgba(226,6,19,0.2)] flex-1 sm:flex-none" type="button">
                            Posting Topik
                            <span class="material-symbols-outlined ml-2 text-[18px]">send</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation Shared Component -->
    <?php
    $active_tab = 'course';
    include 'includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
