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
        <section class="flex-none pt-[12px]">
            <div class="fd-card mb-[18px] p-[18px] sm:p-[24px]">
                <div class="filter-page-intro flex flex-col gap-[12px] sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="inline-block rounded-full bg-[#F5F5F5] px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#525252]">KATALOG PEMBELAJARAN</p>
                        <h1 class="fd-h1 mt-[6px] text-[#171717]">Temukan course yang tepat</h1>
                        <p class="mt-[6px] text-[14px] leading-[22px] text-[#525252]">Pilih kategori untuk menyaring course secara cepat dan akurat.</p>
                    </div>
                    <a class="fd-btn fd-btn-ghost fd-btn-sm rounded-full shrink-0" href="?route=/student/courses"><span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span><span>Kembali ke Course</span></a>
                </div>

                <label class="relative mt-[18px] mb-[12px] block">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#737373]" style="font-size:20px;">search</span>
                    <input id="courseSearch" type="search" class="fd-input fd-search-input w-full pl-11 pr-4" placeholder="Cari judul, kode, atau deskripsi course..." aria-label="Cari course">
                </label>

                <div class="filter-toolbar flex justify-between items-center mb-[12px]">
                    <span class="text-[13px] font-medium text-[#525252]"><span id="active-count" class="fd-mono font-semibold text-[#171717]">0</span> Filter Aktif</span>
                    <button id="reset-btn" class="fd-btn fd-btn-ghost fd-btn-sm text-[#DC2626] font-semibold hidden">Reset Semua</button>
                </div>

                <!-- Filter Chips -->
                <div class="filter-chip-list flex flex-wrap gap-[6px] pb-[6px] relative" role="group" aria-label="Filter kategori course">
                    <button type="button" class="filter-chip" data-filter="Semua" aria-pressed="true">Semua</button>
                    <?php foreach ($categories as $cat): ?>
                    <button type="button" class="filter-chip" data-filter="<?= e($cat) ?>" aria-pressed="false"><?= e($cat) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sorting & Results Summary -->
            <div class="filter-results-toolbar flex justify-between items-center pb-[12px] mb-[12px] border-b border-[#F5F5F5]">
                <div><h2 class="fd-h2 text-[#171717]"><span id="result-count"><?= count($courses) ?></span> Course</h2><p class="text-[13px] text-[#525252]">Course yang tersedia untuk Anda</p></div>
                <select id="sort-select" class="fd-select min-h-[38px] text-[13px]">
                    <option value="relevance">Relevansi</option>
                    <option value="newest">Terbaru</option>
                    <option value="progress">Progress Tertinggi</option>
                </select>
            </div>
        </section>

        <!-- Hasil Course dinamis -->
        <section id="course-results" class="filter-course-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[18px] pt-[6px] content-start pb-[24px]" aria-live="polite" aria-label="Daftar course">
            <?php foreach ($courses as $course): ?>
            <?php
                $courseIcon = $course['thumbnail_icon'] ?? 'school';
                $progress = max(0, min(100, (int) ($course['progress'] ?? 0)));
                $isInProgress = ($course['status'] ?? '') === 'sedang_dipelajari';
                $isCompleted = ($course['status'] ?? '') === 'selesai';
            ?>
            <article class="filter-course-card course-card fd-card flex flex-col overflow-hidden" data-filters="<?= e(json_encode($course['filters'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>" data-course-status="<?= e($course['status']) ?>" data-progress="<?= $progress ?>">
                <a class="course-card-cover group relative flex items-center justify-center overflow-hidden" href="?route=/student/course/detail&id=<?= urlencode($course['id']) ?>" aria-label="Buka course <?= e($course['title']) ?>">
                    <?php if (!empty($course['thumbnail'])): ?>
                    <img alt="<?= e($course['title']) ?>" class="w-full h-full object-cover" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';" src="<?= e($course['thumbnail']) ?>">
                    <?php endif; ?>
                    <span class="fd-cover-icon-badge transition-transform duration-200 group-hover:scale-110" style="display:<?= empty($course['thumbnail']) ? 'inline-flex' : 'none' ?>">
                        <span class="material-symbols-outlined" style="font-size:32px; font-variation-settings: 'FILL' 1;"><?= e($courseIcon) ?></span>
                    </span>
                    <?php if (!$isCompleted): ?><span class="fd-chip fd-chip-promo absolute top-[12px] left-[12px] text-[11px]">Wajib</span><?php endif; ?>
                    <span class="fd-chip absolute bottom-[12px] right-[12px] bg-white/95 text-[#171717] shadow-sm">
                        <span class="material-symbols-outlined text-[#EA580C]" style="font-size:15px;">menu_book</span>
                        <span class="font-medium"><?= (int) $course['modules_count'] ?> Modul</span>
                    </span>
                </a>
                <div class="flex flex-1 flex-col p-[18px]">
                    <div class="mb-[6px] flex items-center justify-between gap-[6px]">
                        <span class="fd-chip <?= $isCompleted ? 'fd-chip-success' : ($isInProgress ? 'fd-chip-info' : 'fd-chip-neutral') ?>">
                            <?= $isCompleted ? 'Selesai' : ($isInProgress ? 'Sedang dipelajari' : 'Belum dimulai') ?>
                        </span>
                        <span class="fd-mono text-[13px] font-semibold text-[#171717]"><?= $progress ?>%</span>
                    </div>
                    <h3 class="fd-h3 line-clamp-2 text-[#171717]"><?= e($course['title']) ?></h3>
                    <p class="mt-[6px] line-clamp-2 flex-1 text-[14px] leading-[22px] text-[#525252]"><?= e($course['description']) ?></p>
                    <div class="mt-[12px] flex flex-wrap gap-[6px]"><?php foreach ($course['filters'] ?? [] as $filter): ?><span class="rounded-[6px] bg-[#F5F5F5] px-2 py-0.5 text-[11px] font-medium text-[#525252]"><?= e($filter) ?></span><?php endforeach; ?></div>
                    <div class="mt-[12px]">
                        <div class="h-[6px] w-full overflow-hidden rounded-full bg-[#F5F5F5]"><span class="block h-full rounded-full <?= $isCompleted ? 'bg-[#16A34A]' : 'bg-[#DC2626]' ?>" style="width: <?= $progress ?>%"></span></div>
                    </div>
                    <a class="fd-btn fd-btn-primary fd-btn-md mt-[18px] w-full rounded-full" href="?route=/student/course/detail&id=<?= urlencode($course['id']) ?>">
                        <span><?= $isInProgress ? 'Lanjutkan Belajar' : 'Mulai Course' ?></span>
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </a>
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
                    <div class="col-span-full fd-card rounded-[12px] bg-white px-[24px] py-[48px] text-center shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
                        <span class="mx-auto mb-[12px] inline-flex size-16 items-center justify-center rounded-full bg-[#F5F5F5] text-[#737373]">
                            <span class="material-symbols-outlined" style="font-size:34px;">search_off</span>
                        </span>
                        <h3 class="fd-h3 text-[#171717] mb-[6px]">Course Tidak Ditemukan</h3>
                        <p class="fd-body text-[#525252] max-w-[320px] mx-auto">Coba ubah atau kurangi filter yang dipilih untuk melihat hasil.</p>
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
                    const icon = c.thumbnail_icon || 'school';
                    const progress = Math.max(0, Math.min(100, Number(c.progress || 0)));
                    const isInProgress = c.status === 'sedang_dipelajari';
                    const isCompleted = c.status === 'selesai';
                    const targetUrl = isCompleted ? `?route=/student/riwayat/detail&id=${c.id}` : `?route=/student/course/detail&id=${c.id}`;

                    const mediaHtml = c.thumbnail ?
                        `<img alt="${c.title}" class="w-full h-full object-cover" src="${c.thumbnail}">` :
                        `<div class="fd-cover-fallback">
                           <span class="fd-cover-icon-badge">
                               <span class="material-symbols-outlined" style="font-size: 32px; font-variation-settings: 'FILL' 1;">${icon}</span>
                           </span>
                       </div>`;

                    const statusLabel = isCompleted ? 'Selesai' : (isInProgress ? 'Sedang dipelajari' : 'Belum dimulai');
                    const statusChipClass = isCompleted ? 'fd-chip-success' : (isInProgress ? 'fd-chip-info' : 'fd-chip-neutral');
                    const btnLabel = isCompleted ? 'Lihat Detail' : (isInProgress ? 'Lanjutkan Belajar' : 'Mulai Course');
                    const btnClass = isCompleted ? 'fd-btn-secondary' : 'fd-btn-primary';
                    const tagsHtml = (c.filters || []).map(f => `<span class="rounded-[6px] bg-[#F5F5F5] px-2 py-0.5 text-[11px] font-medium text-[#525252]">${f}</span>`).join('');

                    return `
                <article class="filter-course-card course-card fd-card flex flex-col overflow-hidden" data-filters='${JSON.stringify(c.filters||[])}' data-course-status="${c.status || ''}" data-progress="${progress}">
                    <a class="course-card-cover group flex items-center justify-center" href="${targetUrl}" aria-label="Buka course ${c.title}">
                        ${mediaHtml}
                        ${!isCompleted ? `<span class="fd-chip fd-chip-promo absolute top-[12px] left-[12px] text-[11px]">Wajib</span>` : ''}
                        <span class="fd-chip absolute bottom-[12px] right-[12px] bg-white/95 text-[#171717] shadow-sm">
                            <span class="material-symbols-outlined text-[#EA580C]" style="font-size:15px;">menu_book</span>
                            <span class="font-medium">${c.modules_count} Modul</span>
                        </span>
                    </a>
                    <div class="flex flex-1 flex-col p-[18px]">
                        <div class="mb-[6px] flex items-center justify-between gap-[6px]">
                            <span class="fd-chip ${statusChipClass}">${statusLabel}</span>
                            <span class="fd-mono text-[13px] font-semibold text-[#171717]">${progress}%</span>
                        </div>
                        <h3 class="fd-h3 line-clamp-2 text-[#171717]">${c.title}</h3>
                        <p class="mt-[6px] line-clamp-2 flex-1 text-[14px] leading-[22px] text-[#525252]">${c.description || ''}</p>
                        <div class="mt-[12px] flex flex-wrap gap-[6px]">${tagsHtml}</div>
                        <div class="mt-[12px]">
                            <div class="h-[6px] w-full overflow-hidden rounded-full bg-[#F5F5F5]"><span class="block h-full rounded-full ${isCompleted ? 'bg-[#16A34A]' : 'bg-[#DC2626]'}" style="width: ${progress}%"></span></div>
                        </div>
                        <a class="fd-btn ${btnClass} fd-btn-md mt-[18px] w-full rounded-full" href="${targetUrl}">
                            <span>${btnLabel}</span>
                            <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                        </a>
                    </div>
                </article>`;
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
