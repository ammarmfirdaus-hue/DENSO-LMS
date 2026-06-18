<?php
/**
 * DLS Student Dashboard View
 * Sistem Pembelajaran Denso
 */

// Inisialisasi variabel jika diperlukan
$userName = $_SESSION['user']['name'] ?? 'Budi Santoso';
$userRole = 'Student'; // Peran default pada view ini
?>
<!DOCTYPE html><html class="light" lang="id" style=""><head>
    <?php 
    $page_title = 'Dashboard LMS Karyawan';
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
<!-- TopAppBar Shared Component -->
<header class="bg-surface dark:bg-on-background fixed top-0 w-full z-40 border-b border-outline-variant dark:border-outline pt-safe">
<div class="flex justify-between items-center w-full px-container-margin h-14">
<button class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center">
<span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 0;">business</span>
</button>
<div class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed tracking-tight">LMS</div>
<a href="?route=/student/notifikasi" onclick="markNotificationsRead()" class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center relative">
<span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 0;">notifications</span>
<span class="notification-dot absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
</a>
</div>
</header>
<!-- Main Content Canvas -->
<main class="flex-1 px-container-margin pt-[56px] pb-[88px] overflow-y-auto space-y-stack-lg mt-stack-md">
<!-- Greeting Section -->
<section class="space-y-stack-sm min-w-0">
<h1 class="font-headline-lg font-bold text-[clamp(16px,5.5vw,26px)] tracking-tight leading-tight sm:text-headline-lg text-on-surface whitespace-nowrap overflow-hidden">
    Selamat <span id="dynamic-greeting">...</span>, <?= e($userName) ?> 👋
</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Leader • Departemen Produksi</p>
</section>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const hour = new Date().getHours();
        let greeting = 'malam';
        if (hour >= 4 && hour < 11) {
            greeting = 'pagi';
        } else if (hour >= 11 && hour < 15) {
            greeting = 'siang';
        } else if (hour >= 15 && hour < 18) {
            greeting = 'sore';
        }
        document.getElementById('dynamic-greeting').textContent = greeting;
    });
</script>

<!-- Summary Cards (Bento Grid Style) -->
<section class="grid grid-cols-2 gap-gutter">
<div class="col-span-2 bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex items-center space-x-stack-md">
<div class="w-12 h-12 bg-secondary-container rounded-full flex items-center justify-center text-on-secondary-container">
<span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1;">play_lesson</span>
</div>
<div>
<p class="font-label-sm text-label-sm text-on-surface-variant">Sedang Dipelajari</p>
<p class="font-headline-sm text-headline-sm text-on-surface"><?= e((string)$stats['active_count']) ?> Course Aktif</p>
</div>
</div>
<div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex flex-col justify-center items-center text-center space-y-stack-sm">
<span class="material-symbols-outlined text-tertiary" style="font-variation-settings: &quot;FILL&quot; 1;">verified</span>
<div>
<p class="font-headline-sm text-headline-sm text-on-surface"><?= e((string)$stats['completed_count']) ?></p>
<p class="font-label-sm text-label-sm text-on-surface-variant">Selesai</p>
</div>
</div>
<div class="bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex flex-col justify-center items-center text-center space-y-stack-sm">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: &quot;FILL&quot; 1;">analytics</span>
<div>
<p class="font-headline-sm text-headline-sm text-on-surface"><?= e((string)$stats['avg_score']) ?>%</p>
<p class="font-label-sm text-label-sm text-on-surface-variant">Skor Rata-rata</p>
</div>
</div>
</section>
<!-- Lanjutkan Belajar Section -->
<section class="space-y-stack-md">
<div class="flex justify-between items-center">
<h2 class="font-headline-sm text-headline-sm text-on-surface">Lanjutkan Belajar</h2>
<a class="font-label-md text-label-md text-secondary" href="?route=/student/courses">Lihat Semua</a>
</div>
<?php if (!empty($activeCourses)): $ac = $activeCourses[0]; ?>
<!-- Course Card (dari data terpusat) -->
<div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden">
<div class="h-32 bg-surface-container-high relative">
<?php if ($ac['thumbnail']): ?>
<img alt="<?= e($ac['title']) ?>" class="w-full h-full object-cover" src="<?= e($ac['thumbnail']) ?>">
<?php else: ?>
<div class="w-full h-full flex items-center justify-center bg-secondary-container">
    <span class="material-symbols-outlined text-[64px] text-on-secondary-container opacity-50"><?= e($ac['thumbnail_icon'] ?? 'school') ?></span>
</div>
<?php endif; ?>
</div>
<div class="p-stack-md space-y-stack-sm">
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight"><?= e($ac['title']) ?></h3>
</div>
</div>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2"><?= e($ac['description']) ?></p>
<div class="pt-stack-sm space-y-2">
<div class="flex justify-between font-label-sm text-label-sm">
<span class="text-on-surface-variant">Progress</span>
<span class="text-tertiary font-bold"><?= e((string)$ac['progress']) ?>%</span>
</div>
<div class="w-full bg-surface-variant rounded-full h-2 overflow-hidden">
<div class="bg-tertiary h-2 rounded-full" style="width: <?= (int)$ac['progress'] ?>%"></div>
</div>
</div>
<button onclick="window.location.href='?route=/student/course/detail&id=<?= e($ac['id']) ?>'" class="w-full mt-stack-md bg-secondary text-on-secondary font-label-md text-label-md py-3 rounded-lg shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] active:scale-95 transition-transform">
                        Lanjutkan Belajar
                    </button>
</div>
</div>
<?php endif; ?>
</section>
</main>
<!-- BottomNavBar Shared Component -->
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe shadow-lg dark:shadow-none bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] rounded-t-xl md:hidden">
<!-- Active Tab: Beranda -->
<a class="flex flex-col items-center justify-center text-secondary dark:text-secondary-fixed bg-secondary-container/20 rounded-xl px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 1;">home</span>
<span class="font-label-sm text-label-sm">Beranda</span>
</a>
<!-- Inactive Tabs -->
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/courses">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">school</span>
<span class="font-label-sm text-label-sm">Course</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/riwayat">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">history</span>
<span class="font-label-sm text-label-sm">Riwayat</span>
</a>
<a onclick="markNotificationsRead()" class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant relative" href="?route=/student/notifikasi">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">notifications</span>
<span class="font-label-sm text-label-sm">Notifikasi</span>
<span class="notification-dot absolute top-1 right-4 w-2 h-2 bg-error rounded-full"></span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/profil">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">person</span>
<span class="font-label-sm text-label-sm">Profil</span>
</a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('hasUnreadNotifications') === 'false') {
            document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
        }
    });

    /**
     * markNotificationsRead (Fungsi JS Inline)
     * Didokumentasikan sesuai standar
     */
    function markNotificationsRead() {
        localStorage.setItem('hasUnreadNotifications', 'false');
        document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
    }
</script>
</body></html>
