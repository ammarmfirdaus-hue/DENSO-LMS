<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Filter Course - LMS';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
</head>

<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
    <!-- TopAppBar Shared Component -->
    <?php
    $show_back = true;
    $header_title = 'Semua Filter';
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <main class="dls-main filter-page flex flex-col pt-[72px] pb-32 lg:ml-[240px] px-container-margin transition-all duration-300" data-course-filter-page>
        <section class="flex-none pt-4">
            <div class="filter-page-intro">
                <div>
                    <p class="filter-eyebrow">KATALOG PEMBELAJARAN</p>
                    <h1 class="font-headline-md text-headline-md font-bold text-on-surface">Temukan course yang tepat</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-4">Pilih kategori untuk menyaring course. Semua course ditampilkan secara otomatis.</p>
                </div>
                <a class="filter-back-link" href="?route=/student/courses"><span class="material-symbols-outlined">arrow_back</span><span>Kembali ke Course</span></a>
            </div>

            <label class="relative mb-4 block"><span class="material-symbols-outlined absolute left-4 top-3 text-on-surface-variant">search</span><input id="courseSearch" type="search" class="w-full rounded-full border border-outline-variant bg-surface-container-lowest py-3 pl-12 pr-4 text-on-surface" placeholder="Cari judul, kode, atau deskripsi course..." aria-label="Cari course"></label>
            <div class="filter-toolbar flex justify-between items-center mb-3">
                <span class="font-label-md text-label-md text-on-surface-variant font-medium"><span id="active-count">0</span> Filter Aktif</span>
                <button id="reset-btn" class="font-label-md text-label-md text-primary font-bold hidden transition-opacity active:opacity-50">Reset Semua</button>
            </div>

            <!-- Filter Chips -->
            <div class="filter-chip-list flex flex-wrap gap-2 pb-2 relative" role="group" aria-label="Filter kategori course">
                <button type="button" class="filter-chip px-5 py-2 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-lg whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="Semua" aria-pressed="true">Semua</button>
                <?php foreach ($categories as $cat): ?>
                <button type="button" class="filter-chip px-5 py-2 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-lg whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="<?= e(
                    $cat,
                ) ?>" aria-pressed="false"><?= e($cat) ?></button>
                <?php endforeach; ?>
            </div>

            <!-- Sorting & Results Summary -->
            <div class="filter-results-toolbar flex justify-between items-center mt-4 border-b border-outline-variant pb-3 mb-2">
                <div><h2 class="font-headline-sm text-headline-sm font-bold text-on-surface"><span id="result-count"><?= count($courses) ?></span> Course</h2><p class="filter-results-caption">Course yang tersedia untuk Anda</p></div>
                <select id="sort-select" class="bg-surface border border-outline-variant text-on-surface text-label-md rounded-lg px-2 py-1 focus:outline-none focus:border-primary">
                    <option value="relevance">Relevansi</option>
                    <option value="newest">Terbaru</option>
                    <option value="progress">Progress Tertinggi</option>
                </select>
            </div>
        </section>

        <!-- Hasil Course dinamis -->
        <section id="course-results" class="filter-course-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter pt-2 content-start pb-4" aria-live="polite" aria-label="Daftar course">
            <?php foreach ($courses as $course): ?>
            <?php
                $filterIcons = [
                    'build' => 'from-orange-400 to-orange-600', 'health_and_safety' => 'from-emerald-400 to-green-600',
                    'cleaning_services' => 'from-teal-400 to-cyan-600', 'precision_manufacturing' => 'from-blue-400 to-indigo-600',
                    'trending_up' => 'from-violet-400 to-purple-600', 'verified' => 'from-sky-400 to-indigo-600',
                    'forum' => 'from-pink-400 to-rose-600', 'groups' => 'from-amber-400 to-yellow-600',
                    'gavel' => 'from-red-400 to-rose-700', 'eco' => 'from-lime-400 to-green-600',
                ];
                $courseIcon = $course['thumbnail_icon'] ?? 'school';
                $courseGradient = $filterIcons[$courseIcon] ?? 'from-blue-500 to-indigo-600';
            ?>
            <article class="filter-course-card course-card" data-filters="<?= e(json_encode($course['filters'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>" data-course-status="<?= e($course['status']) ?>" data-progress="<?= (int) $course['progress'] ?>">
                <a class="filter-course-media bg-[#1e252d] border-b border-slate-700/60" href="?route=/student/course/detail&id=<?= urlencode($course['id']) ?>" aria-label="Buka course <?= e($course['title']) ?>">
                    <span class="filter-course-icon material-symbols-outlined"><?= e($courseIcon) ?></span>
                    <?php if ($course['status'] !== 'selesai'): ?><span class="filter-course-required">Wajib</span><?php endif; ?>
                </a>
                <div class="filter-course-body">
                    <div class="filter-course-meta"><span><?= (int) $course['modules_count'] ?> Modul</span><span class="filter-course-status"><?= $course['status'] === 'sedang_dipelajari' ? 'Sedang dipelajari' : 'Belum dimulai' ?></span></div>
                    <h3 class="filter-course-title"><?= e($course['title']) ?></h3>
                    <p class="filter-course-description"><?= e($course['description']) ?></p>
                    <div class="filter-course-tags"><?php foreach ($course['filters'] ?? [] as $filter): ?><span><?= e($filter) ?></span><?php endforeach; ?></div>
                    <div class="filter-course-progress"><div><span><?= $course['status'] === 'sedang_dipelajari' ? 'Progress belajar' : 'Progress' ?></span><strong><?= (int) $course['progress'] ?>%</strong></div><span class="filter-progress-track"><span style="width: <?= max(0, min(100, (int) $course['progress'])) ?>%"></span></span></div>
                    <a class="filter-course-action" href="?route=/student/course/detail&id=<?= urlencode($course['id']) ?>"><?= $course['status'] === 'sedang_dipelajari' ? 'Lanjutkan belajar' : 'Mulai course' ?><span class="material-symbols-outlined">arrow_forward</span></a>
                </div>
            </article>
            <?php endforeach; ?>
        </section>

        <!-- Pagination Controls -->
        <?php include APP_ROOT . '/includes/components/DX_LMS_M_Pagination.php'; ?>
    </main>

    <!-- BottomNavBar Shared Component -->
    <?php
    $active_tab = 'course';
    include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <script>
        function markNotificationsRead() {
            localStorage.setItem('hasUnreadNotifications', 'false');
            document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
        }

        const coursesData = <?= json_encode($courses, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        // Filter page opens on the complete catalog; only an explicit URL filter narrows it.
        let activeFilters = [];

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
            const searchInput = document.getElementById('courseSearch');
            const pageSizeSelect = document.getElementById('pageSizeSelect');

            let currentFilteredCourses = [];
            let currentPage = 1;

            function getItemsPerPage() {
                return Number(pageSizeSelect?.value) || 10;
            }

            function renderCourses(resetPage = false) {
                const resultsContainer = document.getElementById('course-results');

                /** Mengeksekusi penyaringan data dengan operator perbandingan ganda. */
                const query = searchInput.value.trim().toLocaleLowerCase('id');
                let filtered = coursesData.filter(c => {
                    const matchesSearch = `${c.title || ''} ${c.course_code || c.code || ''} ${c.description || ''} ${(c.filters || []).join(' ')}`.toLocaleLowerCase('id').includes(query);
                    if (!matchesSearch) return false;
                    if (activeFilters.length === 0) return true;
                    const cFilters = c.filters || [];
                    return activeFilters.some(f => cFilters.includes(f));
                });

                /** Menerapkan logika pengurutan pada himpunan data. */
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
                const itemsPerPage = getItemsPerPage();
                const maxPage = Math.ceil(currentFilteredCourses.length / itemsPerPage);
                if (currentPage > maxPage && maxPage > 0) currentPage = maxPage;
                if (currentPage < 1) currentPage = 1;

                const startIdx = (currentPage - 1) * itemsPerPage;
                const paginated = currentFilteredCourses.slice(startIdx, startIdx + itemsPerPage);

                const resultsContainer = document.getElementById('course-results');
                resultsContainer.innerHTML = paginated.map(c => {
                    const badgeText = 'Wajib';
                    const badgeClass = 'bg-error text-on-error';

                    const iconGradients = {
                        'build': 'from-orange-400 to-orange-600',
                        'health_and_safety': 'from-green-400 to-emerald-600',
                        'cleaning_services': 'from-teal-400 to-cyan-600',
                        'precision_manufacturing': 'from-blue-400 to-blue-600',
                        'trending_up': 'from-violet-400 to-purple-600',
                        'verified': 'from-sky-400 to-indigo-600',
                        'forum': 'from-pink-400 to-rose-600',
                        'groups': 'from-amber-400 to-yellow-600',
                        'gavel': 'from-red-400 to-rose-700',
                        'eco': 'from-lime-400 to-green-600',
                        'school': 'from-primary/70 to-primary'
                    };
                    const icon = c.thumbnail_icon || 'school';
                    const grad = iconGradients[icon] || 'from-primary/70 to-primary';

                    let mediaHtml = c.thumbnail ?
                        `<img alt="${c.title}" class="w-full h-full object-cover" src="${c.thumbnail}">` :
                        `<div class="w-full h-full bg-[#1e252d] border-b border-slate-700/60 relative flex items-center justify-center">
                           <span class="material-symbols-outlined text-white/80" style="font-size: 56px; font-variation-settings: 'FILL' 1;">${icon}</span>
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
                    } else {
                        progressHtml = `<div class="font-label-sm text-label-sm text-on-surface-variant mt-2">Belum dimulai</div>`;
                    }

                    let btnHtml = '';
                    if (c.status === 'sedang_dipelajari') {
                        btnHtml = `<button onclick="window.location.href='?route=/student/course/detail&id=${c.id}'" class="w-full mt-stack-md bg-secondary text-on-secondary font-label-md text-label-md py-3 rounded-lg shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] active:scale-95 transition-transform">Lanjutkan Belajar</button>`;
                    } else if (c.status === 'belum_dimulai') {
                        btnHtml = `<button onclick="window.location.href='?route=/student/course/detail&id=${c.id}'" class="w-full mt-stack-md border-2 border-secondary text-secondary bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-secondary-container/10">Mulai Course</button>`;
                    } else {
                        btnHtml = `<button onclick="window.location.href='?route=/student/riwayat/detail&id=${c.id}'" class="w-full mt-stack-md border border-outline-variant text-on-surface-variant bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-surface-container-low">Lihat Detail</button>`;
                    }

                    const tagsHtml = (c.filters || []).map(f => `<span class="bg-surface-variant text-on-surface-variant text-[10px] px-2 py-0.5 rounded-sm font-medium tracking-wide uppercase">${f}</span>`).join('');

                    return `
                <div class="course-card flex flex-col h-full bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden cursor-pointer group" onclick="window.location.href='?route=/student/course/detail&id=${c.id}'" data-filters='${JSON.stringify(c.filters||[])}'>
                    <div class="h-32 bg-surface-container-high relative">
                        ${mediaHtml}
                        ${c.status !== 'selesai' ? `<div class="absolute top-2 left-2 bg-error text-on-error px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm">Wajib</div>` : ''}
                    </div>
                    <div class="p-stack-md space-y-stack-sm flex flex-col flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight group-hover:text-primary transition-colors">${c.title}</h3>
                                <span class="font-label-sm text-label-sm text-on-surface-variant mt-1 block">${c.modules_count} Modul</span>
                            </div>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2">${c.description}</p>
                        <div class="flex gap-1.5 flex-wrap pt-2 mt-auto">
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

                /** Mengatur komponen visual untuk fitur pembagian halaman. */
                const pagContainer = document.getElementById('paginationContainer');
                if (currentFilteredCourses.length > 0) {
                    pagContainer.classList.remove('hidden');
                    document.getElementById('pageIndicator').textContent = 'Halaman ' + currentPage + ' dari ' + maxPage;
                    document.getElementById('prevPageBtn').disabled = currentPage === 1;
                    document.getElementById('nextPageBtn').disabled = currentPage === maxPage;
                } else {
                    pagContainer.classList.add('hidden');
                    document.getElementById('prevPageBtn').disabled = true;
                    document.getElementById('nextPageBtn').disabled = true;
                }
            }

            document.getElementById('prevPageBtn').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayPage();
                    document.getElementById('course-results').scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });

            document.getElementById('nextPageBtn').addEventListener('click', () => {
                const itemsPerPage = getItemsPerPage();
                const maxPage = Math.ceil(currentFilteredCourses.length / itemsPerPage);
                if (currentPage < maxPage) {
                    currentPage++;
                    displayPage();
                    document.getElementById('course-results').scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
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
                    chip.setAttribute('aria-pressed', String(f === 'Semua' ? activeFilters.length === 0 : activeFilters.includes(f)));
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
            pageSizeSelect?.addEventListener('change', () => renderCourses(true));
            searchInput.addEventListener('input', () => renderCourses(true));

            /** Menangani perubahan ukuran layar untuk memperbarui batasan item per halaman. */
            window.addEventListener('resize', () => {
                renderCourses(true);
            });

            /** Mendeteksi dan memproses parameter filter melalui alamat URL. */
            const urlParams = new URLSearchParams(window.location.search);
            const urlFilter = urlParams.get('filter');
            if (urlFilter) {
                if (urlFilter === 'Semua') {
                    activeFilters = [];
                } else {
                    activeFilters = [urlFilter];
                }
                saveFilters();

                /** Memeriksa dan membersihkan struktur URL untuk stabilitas pengalaman pengguna. */
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?route=/student/courses/filters";
                window.history.replaceState({
                    path: newUrl
                }, '', newUrl);
            }

            /** Melaksanakan tahap inisialisasi pada saat pemuatan awal sistem. */
            updateUI();
        });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
