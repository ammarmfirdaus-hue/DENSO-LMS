<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Mulai Topik Baru - LMS';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background antialiased min-h-screen flex flex-col pt-safe pb-[80px]">

    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Mulai Topik Baru';
    $show_back = true;
    $back_url = '?route=/student/course/detail&tab=forum';
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
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
                        Forum diskusi akan menjadi ruang untuk berbagi pertanyaan dan masukan terkait materi kursus.
                    </p>
                </div>

                <div class="dls-feature-note" role="status">Fitur forum belum terhubung ke basis data siswa. Pembuatan topik, balasan, dan lampiran belum tersedia.</div>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation Shared Component -->
    <?php
    $active_tab = 'course';
    include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
