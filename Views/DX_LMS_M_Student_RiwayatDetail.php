<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'Detail Riwayat - LMS';
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-background text-on-background antialiased min-h-screen flex flex-col pt-safe pb-[80px]">
    <!-- TopAppBar -->
    <header class="bg-surface flex justify-between items-center w-full px-container-margin h-14 border-b border-outline-variant sticky top-0 z-40 transition-all duration-200">
        <a href="?route=/student/riwayat" class="text-on-surface p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-colors duration-200 active:scale-95 flex items-center justify-center">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="font-headline-md text-headline-md font-bold text-primary tracking-tight">Detail Riwayat</h1>
        <div class="w-10"></div> <!-- Spacer for centering -->
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-grow px-container-margin py-stack-md flex flex-col gap-stack-lg">
        <!-- Course Header Info -->
        <section class="flex flex-col gap-stack-sm">
            <div class="inline-flex items-center gap-2 bg-tertiary-container/10 text-tertiary px-3 py-1 rounded-full self-start">
                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-label-sm text-label-sm">Selesai pada <?= e(formatDateID($course['completion_date'] ?? '2025-06-20')) ?></span>
            </div>
            <h2 class="font-headline-md text-headline-md text-on-surface"><?= e($course['title']) ?></h2>
        </section>

        <!-- Capaian Pembelajaran (Bento Grid Style) -->
        <section class="flex flex-col gap-stack-md">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Capaian Pembelajaran</h3>
            <div class="grid grid-cols-2 gap-stack-md">
                <!-- Score Card -->
                <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                    <div class="relative w-20 h-20 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-surface-container-high" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                            <path class="text-tertiary" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="100, 100" stroke-width="3"></path>
                        </svg>
                        <div class="absolute flex flex-col items-center justify-center">
                            <span class="font-headline-md text-headline-md text-on-surface"><?= (int)($course['quiz_score'] ?? 100) ?></span>
                        </div>
                    </div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface">Skor Quiz</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1"><?= (int)($course['quiz_score'] ?? 100) >= 80 ? 'Lulus' : 'Belum Lulus' ?><?= (int)($course['quiz_score'] ?? 100) === 100 ? ' Sempurna' : '' ?></p>
                    </div>
                </div>

                <!-- Attendance Card -->
                <div class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant flex flex-col items-center justify-center gap-stack-sm text-center">
                    <div class="w-16 h-16 rounded-full bg-secondary-container/20 flex items-center justify-center text-secondary mb-2">
                        <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">how_to_reg</span>
                    </div>
                    <div>
                        <p class="font-headline-sm text-headline-sm text-on-surface"><?= (int)($course['attendance'] ?? 100) ?>%</p>
                        <p class="font-label-md text-label-md text-on-surface">Kehadiran</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1"><?= (int)($course['attendance'] ?? 100) === 100 ? 'Tercatat Hadir' : 'Sebagian Hadir' ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sertifikat Section (Glassmorphism inspired card) -->
        <section class="flex flex-col gap-stack-md mt-stack-sm pb-8">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Sertifikat</h3>
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-outline-variant overflow-hidden relative group">
                <!-- Certificate Thumbnail -->
                <div class="h-40 bg-surface-variant relative overflow-hidden flex items-center justify-center">
                    <img alt="Sertifikat Dasar Keselamatan Kerja" class="w-full h-full object-cover opacity-80 mix-blend-multiply" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWEiwMCnV-HX6w_f8JunDkUk9s6x2HogDwemAHq1-GmjG9JyL-k3jIWk6rvo1ASGhQiw_b6BzibQbFoY0MSTnaQ0MxGzyn_Ut7HOMl3z8YMDzsjaXc_HGvy6ekLvm8YNJbBJUhHDEomR8Cm14d2Wp5SGU2IXMPzwoT-N5N5iSIf8Z1vQTZbdO9A2l_ce_ae-nKN-_uyJoVWKH3092sWO0sIQC-xkeT8chFxvFdL3cdpHZbJM4d0aDkUJGOYPUNhUPjCXicBdF2ZzY">
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-container-highest to-transparent opacity-60"></div>
                    <span class="material-symbols-outlined absolute text-on-surface/40 text-[64px]">workspace_premium</span>
                </div>
                <!-- Action Area -->
                <div class="p-stack-md bg-surface-container-lowest flex flex-col gap-stack-md">
                    <div>
                        <p class="font-label-md text-label-md text-on-surface">Sertifikat Kelulusan</p>
                        <p class="font-body-md text-body-md text-on-surface-variant">Diterbitkan oleh LMS PT. Denso</p>
                    </div>
                    <button class="w-full bg-secondary text-on-secondary font-label-md text-label-md h-[44px] rounded-lg flex items-center justify-center gap-2 shadow-[0_4px_12px_0_rgba(67,85,185,0.2)] hover:bg-on-secondary-fixed-variant transition-colors active:scale-95">
                        <span class="material-symbols-outlined">download</span>
                        Unduh Sertifikat
                    </button>
                </div>
            </div>
        </section>
    </main>

    <!-- BottomNavBar -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] rounded-t-xl md:hidden">
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student">
            <span class="material-symbols-outlined" data-icon="home">home</span>
            <span class="font-label-sm text-label-sm mt-1">Beranda</span>
        </a>
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/courses">
            <span class="material-symbols-outlined" data-icon="school">school</span>
            <span class="font-label-sm text-label-sm mt-1">Course</span>
        </a>
        <a class="flex flex-col items-center justify-center text-secondary dark:text-secondary-fixed bg-secondary-container/20 rounded-xl px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/riwayat">
            <span class="material-symbols-outlined" data-icon="history" data-weight="fill">history</span>
            <span class="font-label-sm text-label-sm mt-1">Riwayat</span>
        </a>
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/notifikasi">
            <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
            <span class="font-label-sm text-label-sm mt-1">Notifikasi</span>
        </a>
        <a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 hover:bg-surface-container-high dark:hover:bg-surface-variant transition-transform duration-200 active:scale-90" href="?route=/student/profil">
            <span class="material-symbols-outlined" data-icon="person">person</span>
            <span class="font-label-sm text-label-sm mt-1">Profil</span>
        </a>
    </nav>
</body>
</html>
