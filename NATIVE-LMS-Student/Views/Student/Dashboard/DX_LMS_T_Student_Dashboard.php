<?php
/**
 * Tampilan Dasbor Siswa DLS (DLS Student Dashboard View).
 *
 * Halaman utama (beranda) untuk siswa yang menyajikan ringkasan
 * aktivitas belajar, pelatihan aktif, serta kemajuan (progress).
 */

/** Menyiapkan status dan nilai awal variabel sistem yang dibutuhkan. */
$userName = $userName ?? ($_SESSION['student_name'] ?? ($_SESSION['user']['name'] ?? 'Siswa DENSO'));
$userRole = 'Peserta DENSO';

/** Hak akses atau peranan bawaan yang berlaku untuk antarmuka terkait. */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Beranda | DENSO Learning';
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

<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Beranda';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 overflow-y-auto space-y-[24px] pt-[72px] px-[18px] pb-[96px]" data-student-dashboard>
        <p data-api-status hidden role="status" class="rounded-[12px] bg-[#FEE2E2] p-[12px] text-[14px] font-medium text-[#EF4444]"></p>
        <?php
        $quickResumeCourse = null;
        foreach ($courses as $candidateCourse) {
            if (($candidateCourse['status'] ?? '') === 'sedang_dipelajari') {
                $quickResumeCourse = $candidateCourse;
                break;
            }
        }
        if ($quickResumeCourse === null && !empty($courses)) {
            $quickResumeCourse = $courses[0];
        }
        ?>
        <!-- FoodDash Hero Banner (Single Clean Banner, Poppins h1) -->
        <section class="dls-welcome-hero fd-card-elevated rounded-[12px] bg-white p-[18px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] sm:p-[24px] min-w-0">
            <div class="flex flex-col gap-[18px] sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1 space-y-[6px]">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F5F5F5] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#525252]">RUANG BELAJAR KARYAWAN</span>
                    <h1 class="fd-h1 text-[26px] font-bold leading-[32px] text-[#171717] sm:text-[28px] sm:leading-[34px]">Selamat <span id="dynamic-greeting">...</span>, <?= e($userName) ?></h1>
                    <p class="fd-body text-[#525252]"><?= e($userRole) ?> · Akses materi, course, dan progres belajar Anda dengan cepat.</p>
                </div>
                <?php if ($quickResumeCourse): ?>
                <div class="shrink-0">
                    <a href="?route=/student/course/detail&amp;id=<?= urlencode((string) $quickResumeCourse['id']) ?>" class="fd-btn fd-btn-primary fd-btn-md rounded-full w-full sm:w-auto">
                        <span class="material-symbols-outlined" style="font-size:18px;">play_arrow</span>
                        <span><?= ($quickResumeCourse['status'] ?? '') === 'sedang_dipelajari' ? 'Lanjutkan Belajar' : 'Mulai Belajar' ?></span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
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

        <!-- Summary Cards (FoodDash 6px Grid, Borderless White Cards) -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-[12px] sm:gap-[18px]">
            <div class="dls-stat-card dls-stat-card-featured fd-card col-span-2 bg-white p-[18px] rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex items-center gap-[12px]">
                <div class="w-12 h-12 bg-[#FEF2F2] rounded-[12px] flex shrink-0 items-center justify-center text-[#DC2626]">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1;">play_lesson</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[12px] font-medium leading-[18px] text-[#525252] truncate">Sedang Dipelajari</p>
                    <p data-api-active-courses class="fd-h2 text-[20px] font-semibold leading-[26px] text-[#171717] truncate"><?= e(
                        (string) $stats['active_count'],
                    ) ?> Course Aktif</p>
                </div>
            </div>
            <div class="dls-stat-card fd-card bg-white p-[18px] rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col justify-center items-center text-center gap-[6px]">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#DCFCE7] text-[#16A34A]">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1;">verified</span>
                </span>
                <div>
                    <p data-api-completed-courses class="fd-h2 text-[22px] font-semibold text-[#171717]"><?= e(
                        (string) $stats['completed_count'],
                    ) ?></p>
                    <p class="text-[12px] font-medium text-[#525252]">Course Selesai</p>
                </div>
            </div>
            <div class="dls-stat-card fd-card bg-white p-[18px] rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex flex-col justify-center items-center text-center gap-[6px]">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#DBEAFE] text-[#2563EB]">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1;">analytics</span>
                </span>
                <div>
                    <p class="fd-h2 text-[22px] font-semibold text-[#171717]"><?= e(
                        (string) $stats['avg_score'],
                    ) ?>%</p>
                    <p class="text-[12px] font-medium text-[#525252]">Skor Rata-rata</p>
                </div>
            </div>
        </section>
        <!-- Lanjutkan Belajar Section -->
        <section class="space-y-[12px]">
            <div class="flex justify-between items-center gap-[12px]">
                <h2 class="fd-h2 text-[#171717]">Lanjutkan Belajar</h2>
                <a class="fd-btn fd-btn-ghost fd-btn-sm rounded-full text-[13px] font-semibold text-[#525252] hover:text-[#171717]" href="?route=/student/courses">Lihat Semua <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span></a>
            </div>
            <label class="relative block"><span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#737373]" style="font-size:20px;">search</span><input id="dashboardCourseSearch" type="search" class="fd-input fd-search-input w-full rounded-full pl-11 pr-4" placeholder="Cari course yang sedang dipelajari..." aria-label="Cari course di dashboard"></label>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[18px] pt-[6px]" id="course-results" data-api-course-list>
                <?php foreach ($courses as $c): ?>
                <div class="course-card fd-card flex flex-col h-full bg-white rounded-[12px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] overflow-hidden" data-filters="<?= htmlspecialchars(
                    json_encode($c['filters'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                ) ?>">
                    <div class="course-card-cover aspect-video bg-[#171717] relative overflow-hidden rounded-t-[12px]">
                        <?php if (!empty($c['thumbnail'])): ?>
                        <img alt="<?= e($c['title']) ?>" class="w-full h-full object-cover" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" src="<?= e(
    $c['thumbnail'],
) ?>">
                        <?php endif; ?>
                        <?php
                        $icon = $c['thumbnail_icon'] ?? 'school';
                        $showFallback = empty($c['thumbnail']) ? 'flex' : 'none';
                        ?>
                        <div class="fd-cover-fallback<?= !empty($c['thumbnail']) ? ' hidden' : '' ?>" style="display:<?= $showFallback ?>">
                            <span class="fd-cover-icon-badge">
                                <span class="material-symbols-outlined" style="font-size: 32px; font-variation-settings: 'FILL' 1;"><?= e(
                                    $icon,
                                ) ?></span>
                            </span>
                        </div>
                        <?php if ($c['status'] !== 'selesai'): ?>
                        <div class="fd-chip fd-chip-promo absolute top-[12px] left-[12px] text-[11px]">Wajib</div>
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
                                <?php if ($c['status'] === 'sedang_dipelajari'): ?>
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
                                <div class="<?= $c['status'] === 'selesai' ? 'bg-[#16A34A]' : 'bg-[#DC2626]' ?> h-[6px] rounded-full" style="width: <?= (int) $c[
                                    'progress'
                                ] ?>%"></div>
                            </div>
                        </div>
                        <?php if ($c['status'] === 'sedang_dipelajari'): ?>
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
            </div>
            <p data-dashboard-course-empty class="hidden rounded-[12px] bg-[#FAFAFA] p-[24px] text-center text-[14px] text-[#525252] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">Tidak ada course yang cocok dengan pencarian.</p>
        </section>

        <!-- Pagination Controls -->
        <?php include APP_ROOT . '/includes/components/DX_LMS_M_Pagination.php'; ?>
    </main>
    <?php if (!empty($quickResumeCourse)): ?>
    <a href="?route=/student/course/detail&amp;id=<?= urlencode((string) $quickResumeCourse['id']) ?>" class="fd-floating-cta fd-btn fd-btn-primary fd-btn-md rounded-full lg:hidden" aria-label="Lanjutkan course aktif">
        <span class="material-symbols-outlined" style="font-size:18px;">bolt</span>
        <span>Lanjut Belajar</span>
    </a>
    <?php endif; ?>
    <!-- BottomNavBar Shared Component -->
    <?php
    $active_tab = 'beranda';
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

        const dashboardSearch = document.getElementById('dashboardCourseSearch');
        const dashboardEmpty = document.querySelector('[data-dashboard-course-empty]');
        const pageSizeSelect = document.getElementById('pageSizeSelect');
        let filteredCards = [];
        let currentPage = 1;

        function updateUI() {
            const itemsPerPage = Number(pageSizeSelect?.value) || 10;
            const query = (dashboardSearch?.value || '').trim().toLocaleLowerCase('id');
            const courseCards = Array.from(document.querySelectorAll('#course-results .course-card'));
            filteredCards = courseCards.filter(card => card.textContent.toLocaleLowerCase('id').includes(query));
            if (dashboardEmpty) {
                dashboardEmpty.textContent = query ? 'Tidak ada course yang cocok dengan pencarian.' : 'Belum ada course aktif untuk dilanjutkan.';
                dashboardEmpty.classList.toggle('hidden', filteredCards.length > 0);
            }

            /** Mengembalikan antarmuka ke posisi parameter netral atau konfigurasi awal. */
            courseCards.forEach(card => card.classList.add('dls-hidden'));

            /** Logika untuk sistem tata halaman (Pagination). */
            const maxPage = Math.ceil(filteredCards.length / itemsPerPage);
            if (currentPage > maxPage) currentPage = maxPage;
            if (currentPage < 1) currentPage = 1;

            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = startIdx + itemsPerPage;

            filteredCards.slice(startIdx, endIdx).forEach(card => {
                card.classList.remove('dls-hidden');
            });

            const pagContainer = document.getElementById('paginationContainer');
            if (filteredCards.length > 0) {
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

        /** Menangani perubahan ukuran layar untuk memperbarui batasan item per halaman. */
        window.addEventListener('resize', updateUI);
        pageSizeSelect?.addEventListener('change', () => { currentPage = 1; updateUI(); });
        dashboardSearch?.addEventListener('input', () => { currentPage = 1; updateUI(); });

        document.getElementById('prevPageBtn').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                updateUI();
                document.querySelector('#course-results').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });

        document.getElementById('nextPageBtn').addEventListener('click', () => {
            const itemsPerPage = Number(pageSizeSelect?.value) || 10;
            const maxPage = Math.ceil(filteredCards.length / itemsPerPage);
            if (currentPage < maxPage) {
                currentPage++;
                updateUI();
                document.querySelector('#course-results').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });

        updateUI();
        const courseList = document.getElementById('course-results');
        if (courseList && 'MutationObserver' in window) {
            new MutationObserver(() => updateUI()).observe(courseList, { childList: true });
        }
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
