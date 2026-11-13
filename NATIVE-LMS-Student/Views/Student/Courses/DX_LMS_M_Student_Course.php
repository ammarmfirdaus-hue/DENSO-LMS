<?php
/**
 * Tampilan Daftar Pelatihan Siswa (DLS Student Courses View).
 * 
 * Menampilkan antarmuka katalog pelatihan yang sedang diikuti atau 
 * tersedia untuk dipelajari oleh siswa.
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'My Course - LMS';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
</head>

<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'My Course';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 overflow-y-auto space-y-[18px] pb-[96px] pt-[72px] px-[18px]" data-student-courses>
        <p data-api-status hidden role="status" class="rounded-[12px] bg-[#FEE2E2] p-[12px] text-[14px] font-medium text-[#EF4444]"></p>
        <!-- Header & Filters Section (FoodDash Elevated Card) -->
        <section class="dls-course-list-heading fd-card bg-white rounded-[12px] p-[18px] sm:p-[24px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] space-y-[12px]">
            <div>
                <h1 class="fd-h1 text-[#171717]">My Course</h1>
                <p class="fd-body mt-[6px] text-[#525252]">Pilih dan temukan course yang sesuai untuk pengembangan kompetensi Anda.</p>
            </div>
            <!-- Search Bar (Pill) -->
            <div class="relative pt-[6px]">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 mt-[3px] material-symbols-outlined text-[#737373]" style="font-size:20px;">search</span>
                <input type="text" id="searchInput" class="fd-input fd-search-input w-full rounded-full pl-11 pr-4" placeholder="Cari judul course...">
            </div>
            <!-- Filter Chips (FoodDash Pills) -->
            <div id="chip-container" class="flex flex-wrap gap-[6px] pt-[6px]">
                <button class="filter-chip is-active bg-primary text-on-primary border-primary shadow-sm" data-filter="Semua">Semua</button>
                <?php foreach ($filters as $f): ?>
                <button class="filter-chip bg-surface text-on-surface-variant border-outline-variant" data-filter="<?= e(
                    $f,
                ) ?>"><?= e($f) ?></button>
                <?php endforeach; ?>
                <a href="?route=/student/courses/filters" class="fd-btn fd-btn-ghost fd-btn-sm rounded-full text-[13px] font-semibold">Selengkapnya <span class="material-symbols-outlined" style="font-size:15px;">tune</span></a>
            </div>
        </section>

        <!-- Courses List -->
        <section id="course-results" data-api-course-list class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[18px] pt-[6px]">
            <?php foreach ($courses as $c): ?>
            <div class="course-card fd-card flex flex-col h-full bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden" data-filters="<?= htmlspecialchars(
                json_encode($c['filters'] ?? []),
            ) ?>">
                <div class="course-card-cover aspect-video bg-[#171717] relative overflow-hidden rounded-t-[12px]">
                    <?php if ($c['thumbnail']): ?>
                    <img alt="<?= e(
                        $c['title'],
                    ) ?>" class="w-full h-full object-cover" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e(
    $c['thumbnail'],
) ?>">
                    <?php endif; ?>
                    <?php
                     $icon = $c['thumbnail_icon'] ?? 'school';
                     $showFallback = empty($c['thumbnail']) ? 'flex' : 'none';
                     ?>
                    <div class="fd-cover-fallback<?= $c['thumbnail'] ? ' hidden' : '' ?>" id="fallback-<?= e($c['id']) ?>" style="display:<?= $showFallback ?>">
                        <span class="fd-cover-icon-badge">
                            <span class="material-symbols-outlined" style="font-size: 32px; font-variation-settings: 'FILL' 1;"><?= e(
                                $icon,
                            ) ?></span>
                        </span>
                    </div>
                    <?php if (empty($c['has_access'])): ?>
                    <?php $accessLabel = ($c['access_status'] ?? '') === 'pending' ? 'Menunggu Admin' : ((($c['access_status'] ?? '') === 'rejected') ? 'Ditolak · Ajukan ulang' : 'Perlu persetujuan'); ?>
                    <div class="fd-chip <?= ($c['access_status'] ?? '') === 'rejected' ? 'fd-chip-error' : (($c['access_status'] ?? '') === 'pending' ? 'fd-chip-warning' : 'fd-chip-info') ?> absolute top-[12px] left-[12px] shadow-sm"><?= e($accessLabel) ?></div>
                    <?php elseif ($c['status'] !== 'selesai'): ?>
                    <div class="fd-chip fd-chip-promo absolute top-[12px] left-[12px] text-[11px] shadow-sm"><?= e((string) ($c['category'] ?? 'Course')) ?></div>
                    <?php endif; ?>
                    <?php if ($c['status'] === 'selesai'): ?>
                    <div class="fd-chip fd-chip-success absolute top-[12px] right-[12px] shadow-sm">
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>Selesai
                    </div>
                    <?php endif; ?>
                    <div class="fd-chip absolute bottom-[12px] right-[12px] bg-white/95 text-[#171717] shadow-sm">
                        <span class="material-symbols-outlined text-[#EA580C]" style="font-size:15px;">menu_book</span>
                        <span class="font-medium"><?= e((string) $c['modules_count']) ?> Modul</span>
                    </div>
                </div>
                <div class="p-[18px] space-y-[12px] flex flex-col flex-1">
                    <div>
                        <h3 class="fd-h3 text-[#171717] leading-[24px] course-title line-clamp-2" title="<?= e(
                            $c['title'],
                        ) ?>" style="min-height: 3rem;"><?= e($c['title']) ?></h3>
                    </div>
                    <p class="fd-body text-[#525252] line-clamp-2"><?= e(
                        $c['description'],
                    ) ?></p>

                    <div class="flex gap-[6px] flex-wrap pt-[6px] mt-auto h-[40px] overflow-hidden items-start">
                        <?php foreach ($c['filters'] ?? [] as $f): ?>
                        <span class="bg-[#F5F5F5] text-[#525252] text-[11px] px-2.5 py-0.5 rounded-[6px] font-medium whitespace-nowrap"><?= e(
                            $f,
                        ) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="pt-[6px] space-y-[6px]">
                        <div class="flex justify-between items-center text-[13px]">
                            <?php if (empty($c['has_access'])): ?>
                            <span class="text-[#525252] font-medium">Akses</span>
                            <span class="fd-chip <?= ($c['access_status'] ?? '') === 'rejected' ? 'fd-chip-error' : (($c['access_status'] ?? '') === 'pending' ? 'fd-chip-warning' : 'fd-chip-neutral') ?>"><?= ($c['access_status'] ?? '') === 'pending' ? 'Menunggu Admin' : ((($c['access_status'] ?? '') === 'rejected') ? 'Ditolak' : 'Belum diminta') ?></span>
                            <?php elseif ($c['status'] === 'sedang_dipelajari'): ?>
                            <span class="fd-chip fd-chip-info">Sedang Dipelajari</span>
                            <span class="fd-mono font-semibold text-[#171717]"><?= e((string) $c['progress']) ?>%</span>
                            <?php elseif ($c['status'] === 'selesai'): ?>
                            <span class="fd-chip fd-chip-success">Tuntas</span>
                            <span class="fd-mono font-semibold text-[#16A34A]">100%</span>
                            <?php else: ?>
                            <span class="fd-chip fd-chip-neutral">Belum dimulai</span>
                            <span class="fd-mono font-semibold text-[#737373]">0%</span>
                            <?php endif; ?>
                        </div>
                        <div class="w-full bg-[#F5F5F5] rounded-full h-[6px] overflow-hidden">
                            <div class="<?= $c['status'] === 'selesai' ? 'bg-[#16A34A]' : 'bg-[#DC2626]' ?> h-[6px] rounded-full" style="width: <?= (int) $c['progress'] ?>%"></div>
                        </div>
                    </div>
                    <?php if (empty($c['has_access'])): ?>
                    <button onclick="window.location.href='?route=/student/course/detail&id=<?= e($c['id']) ?>'" class="fd-btn fd-btn-secondary fd-btn-md w-full mt-[6px] rounded-full">
                        <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">lock_open</span><?= ($c['access_status'] ?? '') === 'pending' ? 'Lihat Status Permintaan' : ((($c['access_status'] ?? '') === 'rejected') ? 'Ajukan Ulang' : 'Minta Akses Course') ?>
                    </button>
                    <?php elseif ($c['status'] === 'sedang_dipelajari'): ?>
                    <button onclick="window.location.href='?route=/student/course/detail&id=<?= e(
                        $c['id'],
                    ) ?>'" class="fd-btn fd-btn-primary fd-btn-md w-full mt-[6px] rounded-full">
                        Lanjutkan Belajar
                    </button>
                    <?php elseif ($c['status'] === 'belum_dimulai'): ?>
                    <button onclick="window.location.href='?route=/student/course/detail&id=<?= e(
                        $c['id'],
                    ) ?>'" class="fd-btn fd-btn-secondary fd-btn-md w-full mt-[6px] rounded-full">
                        Mulai Course
                    </button>
                    <?php else: ?>
                    <button onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e(
                        $c['id'],
                    ) ?>'" class="fd-btn fd-btn-ghost fd-btn-md w-full mt-[6px] rounded-full bg-[#F5F5F5]">
                        Lihat Detail
                    </button>
                    <?php endif; ?>
                </div>
            </div>
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
            const pageSizeSelect = document.getElementById('pageSizeSelect');
            let chips = document.querySelectorAll('.filter-chip');

            let activeFilters = [];
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const filterParam = urlParams.get('filter');
                if (filterParam) {
                    activeFilters = [filterParam];
                    sessionStorage.setItem('studentFilters', JSON.stringify(activeFilters));
                    /** Membersihkan parameter dari tautan agar nilai penyaringan tidak terus melekat saat halaman dimuat ulang. */
                    window.history.replaceState({}, document.title, "?route=/student/courses");
                } else {
                    const stored = sessionStorage.getItem('studentFilters');
                    if (stored) {
                        const parsed = JSON.parse(stored);
                        activeFilters = Array.isArray(parsed) ? parsed.filter(value => typeof value === 'string') : [];
                    }
                }
            } catch (e) {
                console.error("Gagal membaca filter dari session:", e);
            }

            const chipContainer = document.getElementById('chip-container');
            const semuaChip = chipContainer.querySelector('[data-filter="Semua"]');

            function syncChipsFromActiveFilters() {
                activeFilters.forEach(filter => {
                    if (filter !== 'Semua') {
                        let targetChip = Array.from(chips).find(chip => chip.dataset.filter === filter);

                        if (!targetChip) {
                            targetChip = document.createElement('button');
                            targetChip.className = 'filter-chip px-4 py-1.5 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-md whitespace-nowrap transition-all duration-200 active:scale-95';
                            targetChip.dataset.filter = filter;
                            targetChip.textContent = filter;

                            targetChip.addEventListener('click', () => {
                                const f = targetChip.dataset.filter;
                                if (activeFilters.includes(f)) {
                                    activeFilters = activeFilters.filter(item => item !== f);
                                } else {
                                    activeFilters = [f];
                                }
                                sessionStorage.setItem('studentFilters', JSON.stringify(activeFilters));
                                updateUI();
                            });
                        }

                        if (semuaChip && semuaChip.nextSibling) {
                            chipContainer.insertBefore(targetChip, semuaChip.nextSibling);
                        } else {
                            chipContainer.appendChild(targetChip);
                        }
                    }
                });
                chips = document.querySelectorAll('.filter-chip');
            }

            syncChipsFromActiveFilters();

            /** Menjaga integritas data filter saat pengguna melakukan navigasi mundur menggunakan cache peramban. */
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.location.reload();
                } else {
                    try {
                        const stored = sessionStorage.getItem('studentFilters');
                        if (stored) {
                            const parsed = JSON.parse(stored);
                            activeFilters = Array.isArray(parsed) ? parsed.filter(value => typeof value === 'string') : [];
                            syncChipsFromActiveFilters();
                            updateUI();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }
            });

            let currentPage = 1;

            function updateUI() {
                const itemsPerPage = Number(pageSizeSelect?.value) || 10;
                /** Memperbarui antarmuka penanda (chip) filter. */
                chips.forEach(chip => {
                    const f = chip.dataset.filter;
                    if (f === 'Semua') {
                        if (activeFilters.length === 0) {
                            chip.classList.remove('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                            chip.classList.add('bg-primary', 'text-on-primary', 'border-primary', 'shadow-sm');
                        } else {
                            chip.classList.add('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                            chip.classList.remove('bg-primary', 'text-on-primary', 'border-primary', 'shadow-sm');
                        }
                        return;
                    }
                    if (activeFilters.includes(f)) {
                        chip.classList.remove('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                        chip.classList.add('bg-primary', 'text-on-primary', 'border-primary', 'shadow-sm');
                    } else {
                        chip.classList.add('bg-surface', 'text-on-surface-variant', 'border-outline-variant');
                        chip.classList.remove('bg-primary', 'text-on-primary', 'border-primary', 'shadow-sm');
                    }
                });

                /** Menyaring tampilan kartu berdasarkan kombinasi penelusuran teks dan filter chip. */
                const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');

                let visibleCards = [];

                document.querySelectorAll('.course-card').forEach(card => {
                    let showBySearch = true;
                    if (query) {
                        if (!card.textContent.toLocaleLowerCase('id').includes(query)) {
                            showBySearch = false;
                        }
                    }

                    let showByFilter = true;
                    if (activeFilters.length > 0) {
                        let cardFilters = [];
                        try {
                            const parsedFilters = JSON.parse(card.dataset.filters || "[]");
                            cardFilters = Array.isArray(parsedFilters) ? parsedFilters : [];
                        } catch (_) {
                            cardFilters = [];
                        }
                        showByFilter = activeFilters.some(f => cardFilters.includes(f));
                    }

                    if (showBySearch && showByFilter) {
                        visibleCards.push(card);
                    }
                    card.style.display = 'none'; /** Menyembunyikan seluruh elemen sebelum melakukan penyaringan. */
                });

                /** Logika untuk sistem tata halaman (Pagination). */
                const maxPage = Math.ceil(visibleCards.length / itemsPerPage);
                if (currentPage > maxPage) currentPage = maxPage;
                if (currentPage < 1) currentPage = 1;

                const startIdx = (currentPage - 1) * itemsPerPage;
                const endIdx = startIdx + itemsPerPage;

                visibleCards.slice(startIdx, endIdx).forEach(card => {
                    card.style.display = '';
                });

                const pagContainer = document.getElementById('paginationContainer');
                if (visibleCards.length > 0) {
                    pagContainer?.classList.remove('hidden');
                    const pageIndicator = document.getElementById('pageIndicator');
                    const prevPageBtn = document.getElementById('prevPageBtn');
                    const nextPageBtn = document.getElementById('nextPageBtn');
                    if (pageIndicator) pageIndicator.textContent = 'Halaman ' + currentPage + ' dari ' + maxPage;
                    if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
                    if (nextPageBtn) nextPageBtn.disabled = currentPage === maxPage;
                } else {
                    pagContainer?.classList.add('hidden');
                    const prevPageBtn = document.getElementById('prevPageBtn');
                    const nextPageBtn = document.getElementById('nextPageBtn');
                    if (prevPageBtn) prevPageBtn.disabled = true;
                    if (nextPageBtn) nextPageBtn.disabled = true;
                }
            }

            /** Menangani perubahan ukuran layar untuk memperbarui batasan item per halaman. */
            window.addEventListener('resize', updateUI);
            pageSizeSelect?.addEventListener('change', () => { currentPage = 1; updateUI(); });

            /** Mendaftarkan fungsi penanganan (event listener) untuk setiap aksi klik pada chip. */
            chips.forEach(chip => {
                chip.addEventListener('click', () => {
                    const filter = chip.dataset.filter;
                    if (filter === 'Semua') {
                        activeFilters = [];
                    } else {
                        if (activeFilters.includes(filter)) {
                            activeFilters = activeFilters.filter(f => f !== filter);
                        } else {
                            activeFilters = [filter];
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

            document.getElementById('prevPageBtn')?.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateUI();
                    document.getElementById('course-results').scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });

            document.getElementById('nextPageBtn')?.addEventListener('click', () => {
                currentPage++;
                updateUI();
                document.getElementById('course-results').scrollIntoView({
                    behavior: 'smooth'
                });
            });

            /** Menjalankan fungsi pembaruan antarmuka awal guna menerapkan filter aktif dari sesi lokal. */
            updateUI();
            const courseList = document.getElementById('course-results');
            if (courseList && 'MutationObserver' in window) {
                new MutationObserver(() => updateUI()).observe(courseList, { childList: true });
            }
        });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
