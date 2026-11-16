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
$courses = array_map(static function (array $course): array {
    $course['public_id'] = publicCourseId((string) ($course['id'] ?? ''));
    return $course;
}, $courses ?? []);

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
    <main class="dls-main flex-1 overflow-y-auto space-y-[22px] pt-[72px] px-[18px] pb-[96px]" data-student-dashboard>
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
        $quickResumeCoursePublicId = $quickResumeCourse ? publicCourseId((string) ($quickResumeCourse['id'] ?? '')) : '';

        $topbar_title = 'Halo, ' . trim((string) explode(' ', trim((string) $userName))[0]) . ' 👋';
        $topbar_subtitle = 'Mari pelajari hal baru hari ini!';
        $topbar_search_id = 'dashboardCourseSearch';
        $topbar_search_placeholder = 'Cari course yang sedang dipelajari...';
        include APP_ROOT . '/includes/components/DX_LMS_M_Workspace_Topbar.php';
        ?>

        <!-- Mobile Greeting Banner (Compact, only shown below lg) -->
        <section class="lms-card p-4 lg:hidden">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#DC2626]">RUANG BELAJAR KARYAWAN</p>
            <h1 class="mt-1 text-[20px] font-bold text-[#181824] dark:text-white">Selamat <span id="dynamic-greeting">belajar</span>, <?= e($userName) ?></h1>
            <p class="mt-0.5 text-[12.5px] text-[#64748B]">Akses materi, course, dan progres belajar Anda.</p>
            <label class="relative mt-3 block">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-[#64748B]">search</span>
                <input id="dashboardCourseSearchMobile" type="text" oninput="var d=document.getElementById('dashboardCourseSearch');if(d){d.value=this.value;d.dispatchEvent(new Event('input'));}" class="h-10 w-full rounded-xl border border-slate-200 bg-[#F8FAFC] pl-10 pr-4 text-[13px]" placeholder="Cari course..." aria-label="Cari course di dashboard">
            </label>
        </section>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const el = document.getElementById('dynamic-greeting');
                if (!el) return;
                const hour = new Date().getHours();
                let greeting = 'malam';
                if (hour >= 4 && hour < 11) greeting = 'pagi';
                else if (hour >= 11 && hour < 15) greeting = 'siang';
                else if (hour >= 15 && hour < 18) greeting = 'sore';
                el.textContent = greeting;
            });
        </script>

        <!-- Course Saya Section (Reference Image 3-Card Row + Underline Tabs) -->
        <section class="space-y-3.5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-[19px] font-bold text-[#181824] dark:text-white">Course Saya</h2>
                    <div class="lms-tab-bar mt-1.5" role="tablist" aria-label="Filter status course">
                        <button type="button" class="lms-tab-btn is-active" data-dash-tab="all" role="tab" aria-selected="true">Semua</button>
                        <button type="button" class="lms-tab-btn" data-dash-tab="active" role="tab" aria-selected="false">Aktif</button>
                        <button type="button" class="lms-tab-btn" data-dash-tab="completed" role="tab" aria-selected="false">Selesai</button>
                    </div>
                </div>
                <a class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-[#64748B] hover:text-[#DC2626] transition" href="?route=/student/courses">
                    <span>Lihat Semua</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[18px] pt-1" id="course-results" data-api-course-list>
                <?php
                $card_context = 'dashboard';
                foreach ($courses as $c):
                    include APP_ROOT . '/includes/components/DX_LMS_M_Course_Card.php';
                endforeach;
                ?>
            </div>
            <p data-dashboard-course-empty class="hidden rounded-2xl bg-white p-6 text-center text-[13.5px] text-[#64748B]">Tidak ada course yang cocok dengan filter atau pencarian.</p>
        </section>

        <!-- Pagination Controls -->
        <?php include APP_ROOT . '/includes/components/DX_LMS_M_Pagination.php'; ?>

        <!-- Aktivitas Belajar Horizontal Row (Matching "Assignments" Row in Reference) -->
        <?php if ($quickResumeCourse): ?>
        <section class="space-y-2.5">
            <div class="flex items-center justify-between">
                <h2 class="text-[17px] font-bold text-[#181824] dark:text-white">Lanjutkan Aktivitas</h2>
                <a href="?route=/student/courses" class="text-[12px] font-semibold text-[#64748B] hover:text-[#DC2626]">Katalog →</a>
            </div>
            <div class="lms-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="flex shrink-0 items-center justify-center text-[#DC2626]">
                        <span class="material-symbols-outlined text-[32px]" style="font-variation-settings:'FILL' 1;">play_circle</span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-[14px] font-bold text-[#181824] dark:text-white"><?= e((string) $quickResumeCourse['title']) ?></p>
                        <p class="mt-0.5 text-[12px] text-[#64748B]">Progres saat ini <?= e((string) ($quickResumeCourse['progress'] ?? 0)) ?>% · <?= e((string) ($quickResumeCourse['modules_count'] ?? 0)) ?> Modul</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="?route=/student/courses" class="inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-[12px] font-semibold text-[#181824] transition hover:border-red-200 hover:text-[#DC2626]">Semua Course</a>
                    <a href="?route=/student/course/detail&amp;id=<?= urlencode($quickResumeCoursePublicId) ?>" class="inline-flex h-9 items-center justify-center rounded-full bg-[#FEF2F2] px-4 text-[12px] font-bold text-[#DC2626] transition hover:bg-[#DC2626] hover:text-white">
                        <?= ($quickResumeCourse['status'] ?? '') === 'sedang_dipelajari' ? 'Lanjutkan' : 'Mulai Belajar' ?>
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Ringkasan & Evaluasi 2-Column Feature Cards (Dark + Soft DENSO Red, Matching Bottom Row in Reference) -->
        <section class="space-y-2.5">
            <div class="flex items-center justify-between">
                <h2 class="text-[17px] font-bold text-[#181824] dark:text-white">Ringkasan Belajar</h2>
                <a href="?route=/student/nilai" class="text-[12px] font-semibold text-[#64748B] hover:text-[#DC2626]">Lihat Nilai →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Left Deep Indigo-Charcoal Contrast Card -->
                <div class="rounded-[20px] p-5 text-white shadow-sm flex flex-col justify-between gap-4" style="background: linear-gradient(135deg, #1E1B4B 0%, #181824 65%, #2E1025 100%);">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[15.5px] font-bold text-white">Progres Pelatihan</h3>
                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-[#FCA5A5]">Aktif ↗</span>
                        </div>
                        <p class="mt-2 text-[12.5px] leading-relaxed text-slate-300">
                            Anda sedang mempelajari <strong data-api-active-courses class="text-white"><?= e((string) $stats['active_count']) ?> Course Aktif</strong> dan telah menuntaskan <strong data-api-completed-courses class="text-white"><?= e((string) $stats['completed_count']) ?></strong> pelatihan wajib.
                        </p>
                    </div>
                    <div class="flex items-center justify-between border-t border-white/10 pt-3 text-[12px]">
                        <a href="?route=/student/courses" class="font-semibold text-white underline decoration-white/30 underline-offset-4 hover:text-[#FCA5A5]">Kelola Course</a>
                        <span class="text-slate-400">PT DENSO LMS</span>
                    </div>
                </div>

                <!-- Right Warm DENSO Rose-Coral Gradient Card -->
                <div class="rounded-[20px] p-5 text-[#181824] shadow-sm flex flex-col justify-between gap-4 border border-rose-200/80" style="background: linear-gradient(135deg, #FFF1F2 0%, #FFE4E6 55%, #FECDD3 100%);">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[15.5px] font-bold text-[#181824]">Evaluasi &amp; Sertifikat</h3>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-[#E11D48] shadow-xs">Rata-rata <?= e((string) $stats['avg_score']) ?>%</span>
                        </div>
                        <p class="mt-2 text-[12.5px] leading-relaxed text-[#881337]">
                            Pantau hasil nilai Quiz Anda secara berkala dan unduh sertifikat pelatihan yang telah selesai.
                        </p>
                    </div>
                    <div class="flex items-center justify-between border-t border-rose-300/60 pt-3 text-[12px]">
                        <a href="?route=/student/riwayat" class="font-bold text-[#BE123C] underline decoration-rose-300 underline-offset-4 hover:text-[#181824]">Riwayat &amp; Sertifikat</a>
                        <a href="?route=/student/nilai" class="font-bold text-[#181824] hover:text-[#BE123C]">Buka Nilai →</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
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
        const dashTabButtons = Array.from(document.querySelectorAll('[data-dash-tab]'));
        let activeDashTab = 'all';
        let filteredCards = [];
        let currentPage = 1;

        dashTabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                activeDashTab = btn.getAttribute('data-dash-tab') || 'all';
                dashTabButtons.forEach(b => {
                    const isSel = b === btn;
                    b.classList.toggle('is-active', isSel);
                    b.setAttribute('aria-selected', isSel ? 'true' : 'false');
                });
                currentPage = 1;
                updateUI();
            });
        });

        function updateUI() {
            const itemsPerPage = Number(pageSizeSelect?.value) || 10;
            const query = (dashboardSearch?.value || '').trim().toLocaleLowerCase('id');
            const courseCards = Array.from(document.querySelectorAll('#course-results .course-card'));
            filteredCards = courseCards.filter(card => {
                const status = card.getAttribute('data-course-status') || '';
                const matchesTab = activeDashTab === 'all'
                    || (activeDashTab === 'active' && status !== 'selesai')
                    || (activeDashTab === 'completed' && status === 'selesai');
                const matchesQuery = card.textContent.toLocaleLowerCase('id').includes(query);
                return matchesTab && matchesQuery;
            });
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
