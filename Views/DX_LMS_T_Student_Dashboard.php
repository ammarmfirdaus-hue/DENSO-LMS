<?php
/**
 * Tampilan Dasbor Siswa DLS (DLS Student Dashboard View).
 * 
 * Halaman utama (beranda) untuk siswa yang menyajikan ringkasan 
 * aktivitas belajar, pelatihan aktif, serta kemajuan (progress).
 */

// Inisialisasi variabel jika diperlukan
$userName = $_SESSION['user']['name'] ?? 'Budi Santoso';
$userRole = 'Student'; // Peran default pada view ini
?>
<!DOCTYPE html><html class="light" lang="id" style=""><head>
    <?php 
    $page_title = 'Dashboard LMS Karyawan';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
<!-- TopAppBar Shared Component -->
<?php 
$header_title = 'LMS';
$notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
include 'includes/components/DX_LMS_M_Header.php'; 
?>

<!-- Main Content Canvas -->
<main class="dls-main flex-1 overflow-y-auto space-y-stack-lg">
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
<section class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
<div class="col-span-2 bg-surface-container-lowest p-stack-md rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex items-center space-x-stack-md">
<div class="w-12 h-12 bg-secondary-container rounded-full flex shrink-0 items-center justify-center text-on-secondary-container">
<span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1;">play_lesson</span>
</div>
<div class="min-w-0">
<p class="font-label-sm text-label-sm text-on-surface-variant truncate">Sedang Dipelajari</p>
<p class="font-headline-sm text-headline-sm text-on-surface truncate"><?= e((string)$stats['active_count']) ?> Course Aktif</p>
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter pt-2" id="course-results">
<?php foreach ($courses as $c): ?>
<div class="course-card bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden" data-filters="<?= htmlspecialchars(json_encode($c['filters'] ?? [])) ?>">
<div class="h-32 bg-surface-container-high relative">
<?php if ($c['thumbnail']): ?>
<img alt="<?= e($c['title']) ?>" class="w-full h-full object-cover" src="<?= e($c['thumbnail']) ?>">
<?php else: ?>
<div class="w-full h-full bg-secondary-container relative flex items-center justify-center">
    <span class="material-symbols-outlined text-[64px] text-on-secondary-container opacity-50"><?= e($c['thumbnail_icon'] ?? 'school') ?></span>
</div>
<?php endif; ?>
<?php if (($c['category'] ?? '') === 'Wajib'): ?>
<div class="absolute top-2 left-2 bg-error text-on-error px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm">Wajib</div>
<?php endif; ?>
<?php if ($c['status'] === 'selesai'): ?>
<div class="absolute top-2 right-2 bg-tertiary-container text-on-tertiary-container px-2 py-1 rounded-full font-label-sm text-label-sm shadow-sm backdrop-blur-sm flex items-center gap-1">
    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>Selesai
</div>
<?php endif; ?>
</div>
<div class="p-stack-md space-y-stack-sm">
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight course-title"><?= e($c['title']) ?></h3>
<span class="font-label-sm text-label-sm text-on-surface-variant mt-1 block"><?= e((string)$c['modules_count']) ?> Modul</span>
</div>
</div>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2"><?= e($c['description']) ?></p>

<div class="flex gap-1.5 flex-wrap pt-2">
    <?php foreach ($c['filters'] ?? [] as $f): ?>
    <span class="bg-surface-variant text-on-surface-variant text-[10px] px-2 py-0.5 rounded-sm font-medium tracking-wide uppercase"><?= e($f) ?></span>
    <?php endforeach; ?>
</div>
<div class="pt-stack-sm space-y-2">
<div class="flex justify-between font-label-sm text-label-sm">
<?php if ($c['status'] === 'sedang_dipelajari'): ?>
<span class="text-on-surface-variant">Progress</span>
<span class="text-tertiary font-bold"><?= e((string)$c['progress']) ?>%</span>
<?php elseif ($c['status'] === 'selesai'): ?>
<span class="text-on-surface-variant">Selesai</span>
<span class="text-tertiary font-bold">100%</span>
<?php else: ?>
<span class="text-on-surface-variant">Belum dimulai</span>
<?php endif; ?>
</div>
<div class="w-full bg-surface-variant rounded-full h-2 overflow-hidden">
<div class="bg-tertiary h-2 rounded-full" style="width: <?= (int)$c['progress'] ?>%"></div>
</div>
</div>
<?php if ($c['status'] === 'sedang_dipelajari'): ?>
<button onclick="window.location.href='?route=/student/course/detail&id=<?= e($c['id']) ?>'" class="w-full mt-stack-md bg-secondary text-on-secondary font-label-md text-label-md py-3 rounded-lg shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] active:scale-95 transition-transform">
    Lanjutkan Belajar
</button>
<?php elseif ($c['status'] === 'belum_dimulai'): ?>
<button onclick="window.location.href='?route=/student/course/detail&id=<?= e($c['id']) ?>'" class="w-full mt-stack-md border-2 border-secondary text-secondary bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-secondary-container/10">
    Mulai Course
</button>
<?php else: ?>
<button onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e($c['id']) ?>'" class="w-full mt-stack-md border border-outline-variant text-on-surface-variant bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-surface-container-low">
    Lihat Detail
</button>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</section>

<!-- Pagination Controls -->
<div id="paginationContainer" class="flex md:hidden justify-between items-center mt-6 hidden pb-4">
    <button id="prevPageBtn" class="bg-surface-container-high text-on-surface px-4 py-2 rounded-lg active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100">Sebelumnya</button>
    <span id="pageIndicator" class="font-label-md text-on-surface-variant">Halaman 1</span>
    <button id="nextPageBtn" class="bg-surface-container-high text-on-surface px-4 py-2 rounded-lg active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100">Selanjutnya</button>
</div>
</main>
<!-- BottomNavBar Shared Component -->
<?php 
$active_tab = 'beranda';
include 'includes/components/DX_LMS_M_Bottom_Nav.php'; 
?>

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

    const courseCards = document.querySelectorAll('#course-results .course-card');
    let currentPage = 1;

    function updateUI() {
        const itemsPerPage = window.innerWidth >= 768 ? 9999 : 5;
        
        let visibleCards = Array.from(courseCards);

        // Sembunyikan semua dulu
        courseCards.forEach(card => card.style.display = 'none');

        // Pagination
        const maxPage = Math.ceil(visibleCards.length / itemsPerPage);
        if (currentPage > maxPage) currentPage = maxPage;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * itemsPerPage;
        const endIdx = startIdx + itemsPerPage;

        visibleCards.slice(startIdx, endIdx).forEach(card => {
            card.style.display = '';
        });

        const pagContainer = document.getElementById('paginationContainer');
        if (maxPage > 1) {
            pagContainer.classList.remove('hidden');
            document.getElementById('pageIndicator').textContent = 'Halaman ' + currentPage + ' dari ' + maxPage;
            document.getElementById('prevPageBtn').disabled = currentPage === 1;
            document.getElementById('nextPageBtn').disabled = currentPage === maxPage;
        } else {
            pagContainer.classList.add('hidden');
        }
    }

    // Handle resize untuk update itemsPerPage
    window.addEventListener('resize', updateUI);

    document.getElementById('prevPageBtn').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updateUI();
            document.querySelector('#course-results').scrollIntoView({ behavior: 'smooth' });
        }
    });

    document.getElementById('nextPageBtn').addEventListener('click', () => {
        const itemsPerPage = window.innerWidth >= 768 ? 9999 : 5;
        const maxPage = Math.ceil(courseCards.length / itemsPerPage);
        if (currentPage < maxPage) {
            currentPage++;
            updateUI();
            document.querySelector('#course-results').scrollIntoView({ behavior: 'smooth' });
        }
    });

    updateUI();
</script>
</body>
</html>
