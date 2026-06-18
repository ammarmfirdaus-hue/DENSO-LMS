<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'Filter Course - LMS';
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
<!-- TopAppBar Shared Component -->
<header class="bg-surface dark:bg-on-background fixed top-0 w-full z-40 border-b border-outline-variant dark:border-outline pt-safe">
    <div class="flex items-center w-full px-container-margin h-14">
        <a href="?route=/student/courses" class="text-on-surface hover:bg-surface-container-low p-2 rounded-full flex items-center justify-center mr-2">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="font-headline-sm text-headline-sm font-bold tracking-tight">Semua Filter</h1>
    </div>
</header>

<main class="flex-1 pt-[72px] pb-[88px] px-container-margin flex flex-col h-full mt-stack-sm">
    <section class="flex-none">
        <p class="font-body-md text-body-md text-on-surface-variant mb-4">Pilih kategori untuk menampilkan course yang spesifik.</p>
        
        <div class="flex justify-between items-center mb-3">
            <span class="font-label-md text-label-md text-on-surface-variant font-medium"><span id="active-count">0</span> Filter Aktif</span>
            <button id="reset-btn" class="font-label-md text-label-md text-primary font-bold hidden transition-opacity active:opacity-50">Reset Semua</button>
        </div>

        <!-- Filter Chips -->
        <div class="flex flex-wrap gap-2 max-h-[160px] overflow-y-auto hide-scrollbar pb-2 relative">
            <button type="button" class="filter-chip px-5 py-2 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-lg whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="Semua">Semua</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="filter-chip px-5 py-2 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-lg whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
            <?php endforeach; ?>
        </div>
        
        <!-- Sorting & Results Summary -->
        <div class="flex justify-between items-center mt-4 border-b border-outline-variant pb-3 mb-2">
            <span class="font-headline-sm text-headline-sm font-bold text-on-surface"><span id="result-count">0</span> Hasil</span>
            <select id="sort-select" class="bg-surface border border-outline-variant text-on-surface text-label-md rounded-lg px-2 py-1 focus:outline-none focus:border-primary">
                <option value="relevance">Relevansi</option>
                <option value="newest">Terbaru</option>
                <option value="progress">Progress Tertinggi</option>
            </select>
        </div>
    </section>

    <!-- Hasil Course dinamis -->
    <section id="course-results" class="flex-1 overflow-y-auto space-y-stack-md pt-2 hide-scrollbar pb-4">
        <!-- JS akan me-render course card di sini -->
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
    function markNotificationsRead() {
        localStorage.setItem('hasUnreadNotifications', 'false');
        document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
    }

    const coursesData = <?= json_encode($courses) ?>;
    let activeFilters = [];
    
    // Load from session storage to maintain state between pages
    try {
        const stored = sessionStorage.getItem('studentFilters');
        if (stored) {
            activeFilters = JSON.parse(stored);
        }
    } catch (e) {
        console.error("Gagal membaca filter dari session:", e);
    }
    
    function saveFilters() {
        sessionStorage.setItem('studentFilters', JSON.stringify(activeFilters));
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('hasUnreadNotifications') === 'false') {
            document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
        }

        const chips = document.querySelectorAll('.filter-chip');
        const resetBtn = document.getElementById('reset-btn');
        const activeCountEl = document.getElementById('active-count');
        const resultCountEl = document.getElementById('result-count');
        const sortSelect = document.getElementById('sort-select');

        let currentFilteredCourses = [];
        let currentPage = 1;
        const itemsPerPage = 5;

        function renderCourses(resetPage = false) {
            const resultsContainer = document.getElementById('course-results');
            
            // Filter logic (OR logic)
            let filtered = coursesData.filter(c => {
                if (activeFilters.length === 0) return true;
                const cFilters = c.filters || [];
                return activeFilters.some(f => cFilters.includes(f));
            });

            // Sorting logic
            const sortVal = sortSelect.value;
            if (sortVal === 'newest') {
                filtered.sort((a, b) => new Date(b.last_accessed || 0) - new Date(a.last_accessed || 0));
            } else if (sortVal === 'progress') {
                filtered.sort((a, b) => (b.progress || 0) - (a.progress || 0));
            }

            resultCountEl.textContent = filtered.length;

            if (filtered.length === 0) {
                document.getElementById('paginationContainer').classList.add('hidden');
                resultsContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-64 text-center px-4">
                        <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-30 mb-4">search_off</span>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2 font-bold">Course Tidak Ditemukan</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-[280px]">Coba ubah atau kurangi filter yang dipilih untuk melihat hasil.</p>
                    </div>
                `;
                return;
            }

            currentFilteredCourses = filtered;
            if (resetPage) currentPage = 1;
            displayPage();
        }

        function displayPage() {
            const maxPage = Math.ceil(currentFilteredCourses.length / itemsPerPage);
            if (currentPage > maxPage) currentPage = maxPage;
            if (currentPage < 1) currentPage = 1;

            const startIdx = (currentPage - 1) * itemsPerPage;
            const paginated = currentFilteredCourses.slice(startIdx, startIdx + itemsPerPage);

            const resultsContainer = document.getElementById('course-results');
            resultsContainer.innerHTML = paginated.map(c => {
                const badgeText = 'Wajib';
                const badgeClass = 'bg-error text-on-error';
                
                let mediaHtml = c.thumbnail 
                    ? `<img alt="${c.title}" class="w-full h-full object-cover" src="${c.thumbnail}">`
                    : `<div class="w-full h-full bg-secondary-container relative flex items-center justify-center">
                           <span class="material-symbols-outlined text-[64px] text-on-secondary-container opacity-50">${c.thumbnail_icon || 'school'}</span>
                       </div>`;
                       
                let statusBadge = '';
                let progressHtml = '';
                if (c.status === 'sedang_dipelajari') {
                    progressHtml = `
                    <div class="flex justify-between font-label-sm text-label-sm">
                        <span class="text-on-surface-variant">Progress</span>
                        <span class="text-tertiary font-bold">${c.progress}%</span>
                    </div>
                    <div class="w-full bg-surface-variant rounded-full h-2 overflow-hidden mt-1">
                        <div class="bg-tertiary h-2 rounded-full" style="width: ${c.progress}%"></div>
                    </div>`;
                    
                    statusBadge = `<div class="absolute top-2 right-2 bg-secondary-container text-on-secondary-container px-2 py-1 rounded-full font-label-sm shadow-sm backdrop-blur-sm flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">play_circle</span>Sedang dipelajari
                                  </div>`;
                } else {
                    progressHtml = `<div class="font-label-sm text-label-sm text-on-surface-variant mt-2">Belum dimulai</div>`;
                }

                let btnHtml = '';
                if (c.status === 'sedang_dipelajari') {
                    btnHtml = `<button onclick="window.location.href='?route=/student/course/detail&id=${c.id}'" class="w-full mt-stack-md bg-secondary text-on-secondary font-label-md text-label-md py-3 rounded-lg shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] active:scale-95 transition-transform">Lanjutkan Belajar</button>`;
                } else if (c.status === 'belum_dimulai') {
                    btnHtml = `<button onclick="window.location.href='?route=/student/course/detail&id=${c.id}'" class="w-full mt-stack-md border-2 border-secondary text-secondary bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-secondary-container/10">Mulai Course</button>`;
                }

                const tagsHtml = (c.filters || []).map(f => `<span class="bg-surface-variant text-on-surface-variant text-[10px] px-2 py-0.5 rounded-sm font-medium tracking-wide uppercase">${f}</span>`).join('');

                return `
                <div class="course-card bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant transition-all hover:shadow-md cursor-pointer group" onclick="window.location.href='?route=/student/course/detail&id=${c.id}'" data-filters='${JSON.stringify(c.filters||[])}'>
                    <div class="relative h-40 w-full overflow-hidden">
                        ${mediaHtml}
                        <div class="absolute top-2 left-2 ${badgeClass} px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm">${badgeText}</div>
                        ${statusBadge}
                    </div>
                    <div class="p-stack-md space-y-stack-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight group-hover:text-primary transition-colors">${c.title}</h3>
                                <span class="font-label-sm text-label-sm text-on-surface-variant mt-1 block">${c.modules_count} Modul</span>
                            </div>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2">${c.description}</p>
                        <div class="flex gap-1.5 flex-wrap pt-2">
                            ${tagsHtml}
                        </div>
                        <div class="pt-stack-sm space-y-2">
                            ${progressHtml}
                            ${btnHtml}
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            // Setup pagination UI
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

        document.getElementById('prevPageBtn').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                displayPage();
                document.getElementById('course-results').scrollTo({top: 0, behavior: 'smooth'});
            }
        });

        document.getElementById('nextPageBtn').addEventListener('click', () => {
            const maxPage = Math.ceil(currentFilteredCourses.length / itemsPerPage);
            if (currentPage < maxPage) {
                currentPage++;
                displayPage();
                document.getElementById('course-results').scrollTo({top: 0, behavior: 'smooth'});
            }
        });

        function updateUI() {
            activeCountEl.textContent = activeFilters.length;
            if (activeFilters.length > 0) {
                resetBtn.classList.remove('hidden');
            } else {
                resetBtn.classList.add('hidden');
            }

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

            renderCourses();
        }

        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                const filter = chip.dataset.filter;
                if (filter === 'Semua') {
                    activeFilters = [];
                } else {
                    if (activeFilters.includes(filter)) {
                        activeFilters = activeFilters.filter(f => f !== filter);
                    } else {
                        activeFilters.push(filter);
                    }
                }
                saveFilters();
                updateUI();
            });
        });

        resetBtn.addEventListener('click', () => {
            activeFilters = [];
            saveFilters();
            updateUI();
        });

        
        sortSelect.addEventListener('change', () => {
            renderCourses();
        });

        // Init pertama kali
        updateUI();
    });
</script>

</body>
</html>
