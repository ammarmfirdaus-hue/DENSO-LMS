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
        <?php
        $topbar_title = 'Katalog Course';
        $topbar_subtitle = 'Pilih dan temukan course yang sesuai untuk pengembangan kompetensi Anda.';
        $topbar_search_id = 'searchInput';
        $topbar_search_placeholder = 'Cari judul course...';
        include APP_ROOT . '/includes/components/DX_LMS_M_Workspace_Topbar.php';
        ?>

        <!-- Mobile Header & Filter Section -->
        <section class="dls-course-list-heading lms-card p-[18px] sm:p-[20px] space-y-[12px]">
            <div class="lg:hidden">
                <h1 class="fd-h1 text-[20px] font-bold text-[#181824] dark:text-white">My Course</h1>
                <p class="mt-1 text-[12.5px] text-[#64748B]">Pilih dan temukan course yang sesuai untuk pengembangan kompetensi Anda.</p>
                <div class="relative pt-3">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 mt-[6px] material-symbols-outlined text-[#64748B]" style="font-size:18px;">search</span>
                    <input type="text" oninput="var d=document.getElementById('searchInput');if(d){d.value=this.value;d.dispatchEvent(new Event('input'));}" class="h-10 w-full rounded-xl border border-slate-200 bg-[#F8FAFC] pl-10 pr-4 text-[13px]" placeholder="Cari judul course...">
                </div>
            </div>
            <!-- Filter Chips -->
            <div id="chip-container" class="flex flex-wrap items-center gap-[8px]">
                <button type="button" class="filter-chip is-active bg-primary text-on-primary border-primary shadow-sm" data-filter="Semua" aria-pressed="true">Semua</button>
                <?php foreach ($filters as $f): ?>
                <button type="button" class="filter-chip bg-surface text-on-surface-variant border-outline-variant" data-filter="<?= e(
                    $f,
                ) ?>" aria-pressed="false"><?= e($f) ?></button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Courses List -->
        <section id="course-results" data-api-course-list class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[18px] pt-[6px]">
            <?php
            $card_context = 'course';
            foreach ($courses as $c):
                include APP_ROOT . '/includes/components/DX_LMS_M_Course_Card.php';
            endforeach;
            ?>
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
                    const isSelected = f === 'Semua' ? activeFilters.length === 0 : activeFilters.includes(f);
                    chip.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                    chip.classList.toggle('is-active', isSelected);
                    if (isSelected) {
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
