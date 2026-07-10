<?php
/**
 * Tampilan Riwayat Belajar Siswa DLS (DLS Student History View).
 *
 * Menyajikan rekam jejak pelatihan yang telah diselesaikan oleh siswa
 * beserta sertifikat (jika ada) dan pencapaian nilainya.
 */
$active_tab = 'riwayat'; ?>
<!DOCTYPE html>
<html lang="id" style="">

<head>
    <?php
    $page_title = 'Riwayat Belajar - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }

        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background font-body-md min-h-screen flex flex-col">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'LMS';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include 'includes/components/DX_LMS_M_Header.php';
    ?>
    <main class="dls-main flex-1 pt-[72px] pb-32 lg:ml-[240px] transition-all duration-300">
        <!-- Header & Stats Section -->
        <section class="px-container-margin py-6">
            <h1 class="font-headline-lg text-headline-lg text-on-background mb-1">Riwayat Belajar</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mb-6">Pantau pencapaian dan unduh sertifikat pelatihan Anda.</p>
            <!-- Stats Card -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] p-8 flex flex-col items-center justify-center mb-10 border border-surface-container-low transition-all duration-300 hover:shadow-[0_8px_40px_rgb(0,0,0,0.1)]">
                <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-[0.2em] mb-3 opacity-80">Total Kursus Selesai</span>
                <div class="flex items-center gap-3">
                    <span class="font-headline-lg text-[48px] leading-[48px] text-tertiary font-bold tracking-tight"><?= e(
                        (string) $stats['completed_count'],
                    ) ?></span>
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
        <section id="course-results" class="px-container-margin grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter pt-2">
            <?php foreach ($completedCourses as $c): ?>
            <div class="course-card flex flex-col h-full bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant overflow-hidden" data-filters="<?= htmlspecialchars(
                json_encode($c['filters'] ?? []),
            ) ?>">
                <div class="h-32 bg-surface-container-high relative shrink-0">
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
     : '' ?>" style="display:<?= $showFallback ?>">
                        <span class="material-symbols-outlined text-white/80" style="font-size: 56px; font-variation-settings: 'FILL' 1;"><?= e(
                            $icon,
                        ) ?></span>
                    </div>
                    <div class="absolute top-2 right-2 bg-tertiary-container text-on-tertiary-container px-2 py-1 rounded-full font-label-sm text-label-sm shadow-sm backdrop-blur-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>Selesai
                    </div>
                </div>
                <div class="p-stack-md flex flex-col flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface leading-tight course-title"><?= e(
                                $c['title'],
                            ) ?></h3>
                            <span class="font-label-sm text-label-sm text-on-surface-variant mt-1 block"><?= e(
                                (string) $c['modules_count'],
                            ) ?> Modul</span>
                        </div>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mb-3"><?= e(
                        $c['description'],
                    ) ?></p>

                    <div class="flex gap-1.5 flex-wrap">
                        <?php foreach ($c['filters'] ?? [] as $f): ?>
                        <span class="bg-surface-variant text-on-surface-variant text-[10px] px-2 py-0.5 rounded-sm font-medium tracking-wide uppercase"><?= e(
                            $f,
                        ) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-auto pt-4 space-y-2">
                        <div class="flex justify-between font-label-sm text-label-sm">
                            <span class="text-on-surface-variant">Selesai</span>
                            <span class="text-tertiary font-bold">100%</span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-2 overflow-hidden">
                            <div class="bg-tertiary h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <button onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e(
                            $c['id'],
                        ) ?>'" class="w-full mt-3 border border-outline-variant text-on-surface-variant bg-surface-container-lowest font-label-md text-label-md py-2.5 rounded-lg active:scale-95 transition-transform hover:bg-surface-container-low flex items-center justify-center gap-2">
                            Lihat Riwayat
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- Pagination Controls -->
        <div id="paginationContainer" class="flex md:hidden justify-between items-center mt-6 hidden pb-4 px-container-margin">
            <button id="prevPageBtn" class="bg-surface-container-high text-on-surface px-4 py-2 rounded-lg active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100 font-label-md">Prev</button>
            <span id="pageIndicator" class="font-label-md text-on-surface-variant">Halaman 1</span>
            <button id="nextPageBtn" class="bg-surface-container-high text-on-surface px-4 py-2 rounded-lg active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100 font-label-md">Next</button>
        </div>
    </main>
    <!-- BottomNavBar Shared Component -->
    <?php include 'includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
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

            let currentPage = 1;

            function updateUI() {
                const itemsPerPage = window.innerWidth >= 768 ? 9999 : 5;
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

                    if (showBySearch) {
                        visibleCards.push(card);
                    }
                    card.style.display = 'none'; /** Mengembalikan ke konfigurasi dasar dimana elemen dinamis dirahasiakan terlebih dahulu. */
                });

                /** Pengendalian skema tata halaman navigasi basis data. */
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

            /** Pendaftaran pemantauan elemen berdasar aksi klik/baca interaktif. */
            window.addEventListener('resize', updateUI);

            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
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

            /** Fase pengerjaan susunan gambar, kerangka utama pada halaman pertama kali dimuat. */
            updateUI();
        });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
