<?php
/**
 * DLS Student Course List View
 * Learning Management System
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'Daftar Course - LMS';
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
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
<main class="flex-1 px-container-margin pt-[56px] pb-[88px] overflow-y-auto space-y-stack-md mt-stack-sm">
<!-- Header & Filters Section -->
<section class="space-y-stack-sm">
<h1 class="font-headline-md text-headline-md text-on-surface">Daftar Course</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Pilih dan temukan course yang sesuai untuk pengembangan Anda.</p>
<!-- Search Bar -->
<div class="relative pt-2">
    <span class="absolute left-4 top-[22px] material-symbols-outlined text-on-surface-variant">search</span>
    <input type="text" id="searchInput" class="w-full bg-surface-container-lowest border border-outline-variant text-on-surface rounded-full py-3 pl-12 pr-4 font-body-md shadow-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Cari judul course...">
</div>
<!-- Filter Chips -->
<div class="flex flex-wrap gap-2 pb-2 pt-2">
    <button class="filter-chip px-4 py-1.5 rounded-full bg-primary text-on-primary font-label-md shadow-sm whitespace-nowrap" data-filter="Semua">Semua</button>
    <?php foreach ($filters as $f): ?>
    <button class="filter-chip px-4 py-1.5 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-md whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="<?= e($f) ?>"><?= e($f) ?></button>
    <?php endforeach; ?>
    <a href="?route=/student/courses/filters" class="px-4 py-1.5 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-md whitespace-nowrap">Selengkapnya</a>
</div>
</section>

<!-- Courses List -->
<section id="course-results" class="space-y-stack-md pt-2">
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
<div class="absolute top-2 left-2 bg-error text-on-error px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm">Wajib</div>
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
</section>

<!-- Pagination Controls -->
<div id="paginationContainer" class="flex justify-between items-center mt-6 hidden pb-4">
    <button id="prevPageBtn" class="bg-surface-container-high text-on-surface px-4 py-2 rounded-lg active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100">Sebelumnya</button>
    <span id="pageIndicator" class="font-label-md text-on-surface-variant">Halaman 1</span>
    <button id="nextPageBtn" class="bg-surface-container-high text-on-surface px-4 py-2 rounded-lg active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100">Selanjutnya</button>
</div>
</main>

<!-- BottomNavBar Shared Component -->
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe shadow-lg dark:shadow-none bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] rounded-t-xl md:hidden border-t border-outline-variant">
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">home</span>
<span class="font-label-sm text-label-sm">Beranda</span>
</a>
<a class="flex flex-col items-center justify-center text-secondary dark:text-secondary-fixed bg-secondary-container/20 rounded-xl px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/courses">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 1;">school</span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const courseCards = document.querySelectorAll('.course-card');
    const chips = document.querySelectorAll('.filter-chip');

    let activeFilters = [];
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const filterParam = urlParams.get('filter');
        if (filterParam) {
            activeFilters = [filterParam];
            sessionStorage.setItem('studentFilters', JSON.stringify(activeFilters));
            // Hapus parameter filter dari URL agar jika reload tidak terus ke-set
            window.history.replaceState({}, document.title, "?route=/student/courses");
        } else {
            const stored = sessionStorage.getItem('studentFilters');
            if (stored) {
                activeFilters = JSON.parse(stored);
            }
        }
    } catch (e) {
        console.error("Gagal membaca filter dari session:", e);
    }

    let currentPage = 1;
    const itemsPerPage = 5;

    function updateUI() {
        // Update tampilan chip
        chips.forEach(chip => {
            const f = chip.dataset.filter;
            if (f === 'Semua') {
                if (activeFilters.length === 0) {
                    chip.classList.remove('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                    chip.classList.add('bg-primary', 'text-on-primary', 'border-primary', 'shadow-md');
                } else {
                    chip.classList.add('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                    chip.classList.remove('bg-primary', 'text-on-primary', 'border-primary', 'shadow-md');
                }
                return;
            }
            if (activeFilters.includes(f)) {
                chip.classList.remove('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                chip.classList.add('bg-primary', 'text-on-primary', 'border-primary', 'shadow-md');
            } else {
                chip.classList.add('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                chip.classList.remove('bg-primary', 'text-on-primary', 'border-primary', 'shadow-md');
            }
        });

        // Filter tampilan card (Kombinasi Text Search & Chip Filter dengan logika OR)
        const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');
        
        let visibleCards = [];

        courseCards.forEach(card => {
            let showBySearch = true;
            if (query) {
                const titleEl = card.querySelector('.course-title');
                if (titleEl && !titleEl.textContent.toLowerCase().includes(query)) {
                    showBySearch = false;
                }
            }

            let showByFilter = true;
            if (activeFilters.length > 0) {
                const cardFilters = JSON.parse(card.dataset.filters || "[]");
                showByFilter = activeFilters.some(f => cardFilters.includes(f));
            }

            if (showBySearch && showByFilter) {
                visibleCards.push(card);
            }
            card.style.display = 'none'; // Sembunyikan semua dulu
        });

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

    // Event listener untuk klik chip
    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            const f = chip.dataset.filter;
            if (f === 'Semua') {
                activeFilters = [];
            } else {
                if (activeFilters.includes(f)) {
                    activeFilters = activeFilters.filter(x => x !== f);
                } else {
                    activeFilters.push(f);
                }
            }
            sessionStorage.setItem('studentFilters', JSON.stringify(activeFilters));
            currentPage = 1;
            updateUI();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentPage = 1;
            updateUI();
        });
    }

    document.getElementById('prevPageBtn').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updateUI();
            document.getElementById('course-results').scrollIntoView({ behavior: 'smooth' });
        }
    });

    document.getElementById('nextPageBtn').addEventListener('click', () => {
        currentPage++;
        updateUI();
        document.getElementById('course-results').scrollIntoView({ behavior: 'smooth' });
    });

    // Eksekusi updateUI pertama kali untuk apply filter dari sessionStorage
    updateUI();
});
</script>

</body>
</html>
