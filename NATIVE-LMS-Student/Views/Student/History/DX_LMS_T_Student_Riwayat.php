<?php
/**
 * Tampilan Riwayat Belajar Siswa DLS (DLS Student History View).
 *
 * Menyajikan rekam jejak pelatihan yang telah diselesaikan oleh siswa
 * beserta sertifikat (jika ada) dan pencapaian nilainya.
 */
$active_tab = 'riwayat'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Riwayat Belajar - LMS';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background font-body-md min-h-screen flex flex-col">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Riwayat';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>
    <main class="dls-main flex-1 pt-[72px] pb-32 lg:ml-[240px] transition-all duration-300">
        <!-- Header & Stats Section -->
        <section class="px-container-margin py-6">
            <h1 class="fd-h1 text-[#171717] mb-[6px]">Riwayat Belajar</h1>
            <p class="fd-body text-[#525252] mb-[18px]">Pantau pencapaian dan unduh sertifikat pelatihan Anda.</p>
            <!-- Stats Card -->
            <div class="fd-card-elevated rounded-[12px] bg-white shadow-[0_4px_12px_rgba(0,0,0,0.12)] p-[24px] flex flex-col items-center justify-center mb-[24px]">
                <span class="rounded-full bg-[#DCFCE7] px-3 py-1 text-[11px] font-semibold text-[#16A34A] uppercase tracking-wider mb-[12px]">Total Kursus Selesai</span>
                <div class="flex items-center gap-[12px]">
                    <span class="fd-mono text-[42px] leading-[44px] text-[#171717] font-bold tracking-tight"><?= e(
                        (string) $stats['completed_count'],
                    ) ?></span>
                    <span class="material-symbols-outlined text-[#16A34A] text-[32px]" style='font-variation-settings: "FILL" 1;'>verified</span>
                </div>
            </div>
            <!-- Search Bar -->
            <div class="relative pb-[12px]">
                <span class="material-symbols-outlined pointer-events-none absolute left-4 top-[11px] text-[#737373]">search</span>
                <input type="text" id="searchInput" class="fd-input fd-search-input w-full" placeholder="Cari riwayat course...">
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-[6px] overflow-x-auto pb-2 scrollbar-hide">
            </div>
        </section>
        <!-- Course List -->
        <section id="course-results" class="px-container-margin grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[18px] pt-2">
            <?php foreach ($completedCourses as $c): ?>
            <div class="course-card fd-card flex flex-col h-full bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden" data-filters="<?= htmlspecialchars(
                json_encode($c['filters'] ?? []),
            ) ?>">
                <div class="course-card-cover aspect-video relative shrink-0 bg-[#171717]">
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
                    <div class="fd-cover-fallback<?= $c['thumbnail'] ? ' hidden' : '' ?>" style="display:<?= $showFallback ?>">
                        <span class="fd-cover-icon-badge">
                            <span class="material-symbols-outlined" style="font-size: 32px; font-variation-settings: 'FILL' 1;"><?= e(
                                $icon,
                            ) ?></span>
                        </span>
                    </div>
                    <div class="fd-chip fd-chip-success absolute top-3 right-3 shadow-sm">
                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>Selesai
                    </div>
                </div>
                <div class="p-[18px] flex flex-col flex-1">
                    <div class="flex justify-between items-start mb-[6px]">
                        <div>
                            <h3 class="fd-h4 text-[#171717] leading-tight course-title"><?= e(
                                $c['title'],
                            ) ?></h3>
                            <span class="text-[12px] font-medium text-[#737373] mt-1 block"><strong class="fd-mono text-[#171717]"><?= e(
                                (string) $c['modules_count'],
                            ) ?></strong> Modul</span>
                        </div>
                    </div>
                    <p class="fd-body text-[#525252] line-clamp-2 mb-[12px]"><?= e(
                        $c['description'],
                    ) ?></p>

                    <div class="flex gap-[6px] flex-wrap">
                        <?php foreach ($c['filters'] ?? [] as $f): ?>
                        <span class="bg-[#F5F5F5] text-[#525252] text-[11px] px-2.5 py-0.5 rounded-full font-medium"><?= e(
                            $f,
                        ) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-auto pt-[18px] space-y-[6px]">
                        <div class="flex justify-between text-[12px]">
                            <span class="text-[#525252]">Selesai</span>
                            <span class="fd-mono text-[#16A34A] font-bold">100%</span>
                        </div>
                        <div class="w-full bg-[#F5F5F5] rounded-full h-[6px] overflow-hidden">
                            <div class="bg-[#16A34A] h-[6px] rounded-full" style="width: 100%"></div>
                        </div>
                        <button onclick="window.location.href='?route=/student/riwayat/detail&id=<?= e(
                            $c['id'],
                        ) ?>'" class="fd-btn fd-btn-secondary fd-btn-md w-full mt-[12px]">
                            Lihat Riwayat
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- Pagination Controls -->
        <?php include APP_ROOT . '/includes/components/DX_LMS_M_Pagination.php'; ?>
    </main>
    <!-- BottomNavBar Shared Component -->
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
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
            const pageSizeSelect = document.getElementById('pageSizeSelect');

            let currentPage = 1;

            function updateUI() {
                const itemsPerPage = Number(pageSizeSelect?.value) || 10;
                const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');

                let visibleCards = [];

                courseCards.forEach(card => {
                    let showBySearch = true;
                    if (query) {
                        if (!card.textContent.toLocaleLowerCase('id').includes(query)) {
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
                if (visibleCards.length > 0) {
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

            /** Pendaftaran pemantauan elemen berdasar aksi klik/baca interaktif. */
            window.addEventListener('resize', updateUI);
            pageSizeSelect?.addEventListener('change', () => { currentPage = 1; updateUI(); });

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
