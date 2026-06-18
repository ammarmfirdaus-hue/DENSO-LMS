<?php
/**
 * DLS Student Riwayat View
 * Sistem Pembelajaran Denso
 */
?>
<!DOCTYPE html>
<html lang="id" style=""><head>
    <?php 
    $page_title = 'Riwayat Belajar - LMS';
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col">
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
<main class="flex-1 pt-[72px] pb-32">
<!-- Header & Stats Section -->
<section class="px-container-margin py-6">
<h1 class="font-headline-lg text-headline-lg text-on-background mb-1">Riwayat Belajar</h1>
<p class="font-body-md text-body-md text-on-surface-variant mb-6">Pantau pencapaian dan unduh sertifikat pelatihan Anda.</p>
<!-- Stats Card -->
<div class="bg-surface-container-lowest rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] p-8 flex flex-col items-center justify-center mb-10 border border-surface-container-low transition-all duration-300 hover:shadow-[0_8px_40px_rgb(0,0,0,0.1)]">
<span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-[0.2em] mb-3 opacity-80">Total Kursus Selesai</span>
<div class="flex items-center gap-3">
<span class="font-headline-lg text-[48px] leading-[48px] text-tertiary font-bold tracking-tight"><?= e((string)$stats['completed_count']) ?></span>
<span class="material-symbols-outlined text-tertiary text-[32px]" style='font-variation-settings: "FILL" 1;'>verified</span>
</div>
</div>
<!-- Search Bar -->
<div class="relative pb-4">
    <span class="absolute left-4 top-[14px] material-symbols-outlined text-on-surface-variant">search</span>
    <input type="text" id="searchInput" class="w-full bg-surface-container-lowest border border-outline-variant text-on-surface rounded-full py-3 pl-12 pr-4 font-body-md shadow-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Cari riwayat course...">
</div>

<!-- Filter Tabs -->
<div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
</div>
</section>
<!-- Course List -->
<section class="px-container-margin flex flex-col gap-4">
<?php foreach ($completedCourses as $c): ?>
<div onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e($c['id']) ?>'" class="course-card bg-surface rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden flex flex-col cursor-pointer">
<div class="h-32 bg-surface-container-high relative">
<?php if ($c['thumbnail']): ?>
<img alt="<?= e($c['title']) ?>" class="w-full h-full object-cover" src="<?= e($c['thumbnail']) ?>"/>
<?php else: ?>
<div class="w-full h-full bg-secondary-container flex items-center justify-center">
    <span class="material-symbols-outlined text-[64px] text-on-secondary-container opacity-50"><?= e($c['thumbnail_icon'] ?? 'school') ?></span>
</div>
<?php endif; ?>
<div class="absolute top-2 right-2 bg-tertiary-container text-on-tertiary-container px-2 py-1 rounded-full font-label-sm text-label-sm shadow-sm backdrop-blur-sm flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]" style='font-variation-settings: "FILL" 1;'>check_circle</span>Selesai
</div>
</div>
<div class="p-stack-md space-y-stack-sm">
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight course-title"><?= e($c['title']) ?></h3>
</div>
</div>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2"><?= e($c['description']) ?></p>
</div>
</div>
<?php endforeach; ?>
</section>
</main>
<!-- BottomNavBar Shared Component -->
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe shadow-lg dark:shadow-none bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] rounded-t-xl md:hidden">
<!-- Inactive Tabs -->
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">home</span>
<span class="font-label-sm text-label-sm">Beranda</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/courses">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">school</span>
<span class="font-label-sm text-label-sm">Course</span>
</a>
<!-- Active Tab: Riwayat -->
<a class="flex flex-col items-center justify-center text-secondary dark:text-secondary-fixed bg-secondary-container/20 rounded-xl px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/riwayat">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 1;">history</span>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const courseCards = document.querySelectorAll('.course-card');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            courseCards.forEach(card => {
                const titleEl = card.querySelector('.course-title');
                if (titleEl) {
                    const titleText = titleEl.textContent.toLowerCase();
                    if (titleText.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    }
});
</script>

</body>
</html>
