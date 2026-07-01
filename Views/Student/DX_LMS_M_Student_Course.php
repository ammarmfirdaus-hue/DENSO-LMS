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
    $page_title = 'Daftar Course - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
</head>

<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'LMS';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 overflow-y-auto space-y-stack-md pb-[88px] pt-[72px] px-container-margin">
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
            <div id="chip-container" class="flex flex-wrap gap-2 pb-2 pt-2">
                <button class="filter-chip px-4 py-1.5 rounded-full border border-primary bg-primary text-on-primary font-label-md shadow-sm whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="Semua">Semua</button>
                <?php foreach ($filters as $f): ?>
                <button class="filter-chip px-4 py-1.5 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-md whitespace-nowrap transition-all duration-200 active:scale-95" data-filter="<?= e(
                    $f,
                ) ?>"><?= e($f) ?></button>
                <?php endforeach; ?>
                <a href="?route=/student/courses/filters" class="px-4 py-1.5 rounded-full border border-outline-variant bg-surface text-on-surface-variant font-label-md whitespace-nowrap">Selengkapnya</a>
            </div>
        </section>

        <!-- Courses List -->
        <section id="course-results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter pt-2">
            <?php foreach ($courses as $c): ?>
            <div class="course-card flex flex-col h-full bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden" data-filters="<?= htmlspecialchars(
                json_encode($c['filters'] ?? []),
            ) ?>">
                <div class="h-32 bg-surface-container-high relative">
                    <?php if ($c['thumbnail']): ?>
                    <img alt="<?= e(
                        $c['title'],
                    ) ?>" class="w-full h-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e(
    $c['thumbnail'],
) ?>">
                    <?php endif; ?>
                    <?php

                     $iconGradients = [
                         'build' => 'from-orange-400 to-orange-600',
                         'health_and_safety' => 'from-green-400 to-emerald-600',
                         'cleaning_services' => 'from-teal-400 to-cyan-600',
                         'precision_manufacturing' => 'from-blue-400 to-blue-600',
                         'trending_up' => 'from-violet-400 to-purple-600',
                         'verified' => 'from-sky-400 to-indigo-600',
                         'forum' => 'from-pink-400 to-rose-600',
                         'groups' => 'from-amber-400 to-yellow-600',
                         'gavel' => 'from-red-400 to-rose-700',
                         'eco' => 'from-lime-400 to-green-600',
                         'school' => 'from-primary/70 to-primary',
                     ];
                     $icon = $c['thumbnail_icon'] ?? 'school';
                     $grad = $iconGradients[$icon] ?? 'from-primary/70 to-primary';
                     $showFallback = empty($c['thumbnail']) ? 'flex' : 'none';
                     ?>
                    <div class="w-full h-full bg-gradient-to-br <?= $grad ?> relative items-center justify-center<?= $c[
     'thumbnail'
 ]
     ? ' hidden'
     : '' ?>" id="fallback-<?= e($c['id']) ?>" style="display:<?= $showFallback ?>">
                        <span class="material-symbols-outlined text-white/80" style="font-size: 56px; font-variation-settings: 'FILL' 1;"><?= e(
                            $icon,
                        ) ?></span>
                    </div>
                    <?php if ($c['status'] !== 'selesai'): ?>
                    <div class="absolute top-2 left-2 bg-error text-on-error px-2 py-1 rounded-sm font-label-sm shadow-sm backdrop-blur-sm">Wajib</div>
                    <?php endif; ?>
                    <?php if ($c['status'] === 'selesai'): ?>
                    <div class="absolute top-2 right-2 bg-tertiary-container text-on-tertiary-container px-2 py-1 rounded-full font-label-sm text-label-sm shadow-sm backdrop-blur-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>Selesai
                    </div>
                    <?php endif; ?>
                </div>
                <div class="p-stack-md space-y-stack-sm flex flex-col flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight course-title line-clamp-2" title="<?= e(
                                $c['title'],
                            ) ?>" style="min-height: 2.5rem;"><?= e($c['title']) ?></h3>
                            <span class="font-label-sm text-label-sm text-on-surface-variant mt-1 block"><?= e(
                                (string) $c['modules_count'],
                            ) ?> Modul</span>
                        </div>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2"><?= e(
                        $c['description'],
                    ) ?></p>

                    <div class="flex gap-1.5 flex-wrap pt-2 mt-auto h-[44px] overflow-hidden items-start">
                        <?php foreach ($c['filters'] ?? [] as $f): ?>
                        <span class="bg-surface-variant text-on-surface-variant text-[10px] px-2 py-0.5 rounded-sm font-medium tracking-wide uppercase whitespace-nowrap"><?= e(
                            $f,
                        ) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="pt-stack-sm space-y-2">
                        <div class="flex justify-between font-label-sm text-label-sm">
                            <?php if ($c['status'] === 'sedang_dipelajari'): ?>
                            <span class="text-on-surface-variant">Progress</span>
                            <span class="text-tertiary font-bold"><?= e((string) $c['progress']) ?>%</span>
                            <?php elseif ($c['status'] === 'selesai'): ?>
                            <span class="text-on-surface-variant">Selesai</span>
                            <span class="text-tertiary font-bold">100%</span>
                            <?php else: ?>
                            <span class="text-on-surface-variant">Belum dimulai</span>
                            <?php endif; ?>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-2 overflow-hidden">
                            <div class="bg-tertiary h-2 rounded-full" style="width: <?= (int) $c['progress'] ?>%"></div>
                        </div>
                    </div>
                    <?php if ($c['status'] === 'sedang_dipelajari'): ?>
                    <button onclick="window.location.href='?route=/student/course/detail&id=<?= e(
                        $c['id'],
                    ) ?>'" class="w-full mt-stack-md bg-secondary text-on-secondary font-label-md text-label-md py-3 rounded-lg shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] active:scale-95 transition-transform">
                        Lanjutkan Belajar
                    </button>
                    <?php elseif ($c['status'] === 'belum_dimulai'): ?>
                    <button onclick="window.location.href='?route=/student/course/detail&id=<?= e(
                        $c['id'],
                    ) ?>'" class="w-full mt-stack-md border-2 border-secondary text-secondary bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-secondary-container/10">
                        Mulai Course
                    </button>
                    <?php else: ?>
                    <button onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e(
                        $c['id'],
                    ) ?>'" class="w-full mt-stack-md border border-outline-variant text-on-surface-variant bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-surface-container-low">
                        Lihat Detail
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
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
    $active_tab = 'course';
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
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const courseCards = document.querySelectorAll('.course-card');
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
                        activeFilters = JSON.parse(stored);
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
                            activeFilters = JSON.parse(stored);
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
                const itemsPerPage = window.innerWidth >= 768 ? 9999 : 5;
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
                if (maxPage > 1) {
                    pagContainer.classList.remove('hidden');
                    document.getElementById('pageIndicator').textContent = 'Halaman ' + currentPage + ' dari ' + maxPage;
                    document.getElementById('prevPageBtn').disabled = currentPage === 1;
                    document.getElementById('nextPageBtn').disabled = currentPage === maxPage;
                } else {
                    pagContainer.classList.add('hidden');
                }
            }

            /** Menangani perubahan ukuran layar untuk memperbarui batasan item per halaman. */
            window.addEventListener('resize', updateUI);

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

            document.getElementById('prevPageBtn').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateUI();
                    document.getElementById('course-results').scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });

            document.getElementById('nextPageBtn').addEventListener('click', () => {
                currentPage++;
                updateUI();
                document.getElementById('course-results').scrollIntoView({
                    behavior: 'smooth'
                });
            });

            /** Menjalankan fungsi pembaruan antarmuka awal guna menerapkan filter aktif dari sesi lokal. */
            updateUI();
        });
    </script>

</body>

</html>