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

        <?php
        $scheduleSource = array_values(
            array_filter($all_courses ?? $courses, static fn($sc) => !empty($sc['has_access']))
        );
        $scheduleItems = [];
        foreach ($scheduleSource as $sc) {
            $rawStart = $sc['start_date'] ?? (!empty($sc['created_at']) ? substr((string) $sc['created_at'], 0, 10) : date('Y-m-d'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $rawStart)) {
                $rawStart = date('Y-m-d');
            }
            $hasExplicitEnd = !empty($sc['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $sc['end_date']);
            if ($hasExplicitEnd) {
                $rawEnd = (string) $sc['end_date'];
            } else {
                $days = strcasecmp((string) ($sc['category'] ?? ''), 'Wajib') === 0 ? 30 : 45;
                $rawEnd = date('Y-m-d', strtotime($rawStart . ' +' . $days . ' days'));
            }
            $sessions = is_array($sc['sessions'] ?? null) ? $sc['sessions'] : [];
            $totalSessions = (int) ($sc['modules_count'] ?? count($sessions));
            $completedSessions = count(array_filter($sessions, static fn($s) => ($s['status'] ?? '') === 'selesai'));
            $quizInfo = is_array($sc['quiz'] ?? null) ? $sc['quiz'] : [];
            $scheduleItems[] = [
                'id' => (string) ($sc['id'] ?? ''),
                'title' => (string) ($sc['title'] ?? 'Course'),
                'category' => (string) ($sc['category'] ?? 'Umum'),
                'status' => (string) ($sc['status'] ?? 'belum_dimulai'),
                'progress' => (int) ($sc['progress'] ?? 0),
                'has_access' => true,
                'modules_count' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'quiz_status' => (string) ($quizInfo['status'] ?? 'terkunci'),
                'quiz_score' => $quizInfo['score'] ?? ($sc['quiz_score'] ?? null),
                'start_date' => $rawStart,
                'deadline' => $rawEnd,
                'is_estimated_deadline' => !$hasExplicitEnd,
                'url' => '?route=/student/course/detail&id=' . urlencode((string) ($sc['id'] ?? '')),
            ];
        }
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
            <!-- Filter Chips & Jadwal Button -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div id="chip-container" class="flex flex-wrap items-center gap-[8px]">
                    <button type="button" class="filter-chip is-active bg-primary text-on-primary border-primary shadow-sm" data-filter="Semua" aria-pressed="true">Semua</button>
                    <?php foreach ($filters as $f): ?>
                    <button type="button" class="filter-chip bg-surface text-on-surface-variant border-outline-variant" data-filter="<?= e(
                        $f,
                    ) ?>" aria-pressed="false"><?= e($f) ?></button>
                    <?php endforeach; ?>
                </div>
                <button
                    type="button"
                    id="btnOpenCourseSchedule"
                    data-dls-action-ignore="true"
                    class="inline-flex h-9 shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-[12.5px] font-semibold text-slate-700 shadow-2xs transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[18px] text-[#DC2626]" aria-hidden="true">calendar_month</span>
                    <span>Jadwal Saya</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10.5px] font-bold leading-none text-slate-600 dark:bg-slate-700 dark:text-slate-200"><?= count($scheduleItems) ?></span>
                </button>
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

    <!-- Modal Jadwal & Kalender Course -->
    <style>
        #courseScheduleModal {
            position: fixed;
            inset: 0;
            z-index: 10070;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(3px);
        }
        #courseScheduleModal.is-open {
            display: flex;
        }
        #courseScheduleModal .sched-dialog {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 980px;
            max-height: 90vh;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #1f2937;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        }
        html.dark #courseScheduleModal .sched-dialog {
            border-color: #273244;
            background: #151c28;
            color: #e5e7eb;
        }
        #courseScheduleModal .sched-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
        }
        html.dark #courseScheduleModal .sched-header {
            border-bottom-color: #232d3f;
        }
        #courseScheduleModal .sched-close-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #6b7280;
            cursor: pointer;
            flex-shrink: 0;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        #courseScheduleModal .sched-close-btn:hover {
            background: #f3f4f6;
            color: #111827;
        }
        html.dark #courseScheduleModal .sched-close-btn {
            border-color: #2e3b50;
            background: #1c2536;
            color: #9ca3af;
        }
        html.dark #courseScheduleModal .sched-close-btn:hover {
            background: #273244;
            color: #f3f4f6;
        }
        #courseScheduleModal .sched-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            padding: 12px 22px;
            background: #f9fafb;
            border-bottom: 1px solid #f1f5f9;
        }
        @media (min-width: 640px) {
            #courseScheduleModal .sched-summary {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        html.dark #courseScheduleModal .sched-summary {
            background: #111722;
            border-bottom-color: #232d3f;
        }
        #courseScheduleModal .sched-stat-card {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 10px 12px;
        }
        html.dark #courseScheduleModal .sched-stat-card {
            border-color: #232d3f;
            background: #192231;
        }
        #courseScheduleModal .sched-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 16px 22px 22px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }
        #courseScheduleModal .sched-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        #courseScheduleModal .sched-cal-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }
        html.dark #courseScheduleModal .sched-cal-wrap {
            border-color: #232d3f;
            background: #151c28;
        }
        #courseScheduleModal .sched-weekdays {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        html.dark #courseScheduleModal .sched-weekdays {
            background: #111722;
            border-bottom-color: #232d3f;
        }
        #courseScheduleModal .sched-weekdays > div {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #6b7280;
            padding: 9px 4px;
            border-right: 1px solid #eef2f6;
        }
        #courseScheduleModal .sched-weekdays > div:last-child {
            border-right: none;
        }
        html.dark #courseScheduleModal .sched-weekdays > div {
            color: #9ca3af;
            border-right-color: #232d3f;
        }
        #courseScheduleModal .sched-week-row {
            position: relative;
            border-bottom: 1px solid #e5e7eb;
        }
        #courseScheduleModal .sched-week-row:last-child {
            border-bottom: none;
        }
        html.dark #courseScheduleModal .sched-week-row {
            border-bottom-color: #232d3f;
        }
        #courseScheduleModal .sched-week-bg {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
        #courseScheduleModal .sched-day-cell {
            min-height: 82px;
            padding: 6px 8px;
            border-right: 1px solid #eef2f6;
            font-size: 11.5px;
            font-weight: 600;
            color: #374151;
            background: #ffffff;
            cursor: pointer;
            transition: background-color 0.12s ease;
        }
        #courseScheduleModal .sched-day-cell:last-child {
            border-right: none;
        }
        #courseScheduleModal .sched-day-cell:hover {
            background: #f8fafc;
        }
        #courseScheduleModal .sched-day-cell.is-outside {
            background: #f3f4f6;
            color: #9ca3af;
        }
        #courseScheduleModal .sched-day-cell.is-today {
            background: #fff8f8;
            color: #dc2626;
        }
        #courseScheduleModal .sched-day-cell.is-selected {
            background: #f1f5f9;
            box-shadow: inset 0 0 0 1.5px #334155;
        }
        html.dark #courseScheduleModal .sched-day-cell {
            border-right-color: #232d3f;
            background: #151c28;
            color: #d1d5db;
        }
        html.dark #courseScheduleModal .sched-day-cell:hover {
            background: #1a2333;
        }
        html.dark #courseScheduleModal .sched-day-cell.is-outside {
            background: #101520;
            color: #64748b;
        }
        html.dark #courseScheduleModal .sched-day-cell.is-today {
            background: rgba(220, 38, 38, 0.1);
            color: #fca5a5;
        }
        html.dark #courseScheduleModal .sched-day-cell.is-selected {
            background: #1e293b;
            box-shadow: inset 0 0 0 1.5px #94a3b8;
        }
        #courseScheduleModal .sched-week-events {
            position: relative;
            z-index: 2;
            margin-top: -54px;
            padding-bottom: 6px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            pointer-events: none;
        }
        #courseScheduleModal .sched-event-lane {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
        #courseScheduleModal .sched-event-bar {
            pointer-events: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            height: 24px;
            padding: 0 10px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            border: none;
            transition: filter 0.15s ease, transform 0.12s ease;
        }
        #courseScheduleModal .sched-event-bar:hover {
            filter: brightness(1.06);
        }
        #courseScheduleModal .sched-detail-strip {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 14px 16px;
        }
        html.dark #courseScheduleModal .sched-detail-strip {
            border-color: #232d3f;
            background: #111722;
        }
        #courseScheduleModal .sched-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        @media (min-width: 768px) {
            #courseScheduleModal .sched-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        #courseScheduleModal .sched-course-card {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 14px;
        }
        html.dark #courseScheduleModal .sched-course-card {
            border-color: #232d3f;
            background: #192231;
        }
        #courseScheduleModal .sched-steps {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
            font-size: 10.5px;
            font-weight: 600;
        }
        html.dark #courseScheduleModal .sched-steps {
            border-top-color: #232d3f;
        }
        #courseScheduleModal .sched-step-bar {
            display: block;
            width: 100%;
            height: 4px;
            margin-bottom: 5px;
        }
        #courseScheduleModal .sched-steps > div:first-child .sched-step-bar {
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
        }
        #courseScheduleModal .sched-steps > div:last-child .sched-step-bar {
            border-top-right-radius: 9999px;
            border-bottom-right-radius: 9999px;
        }
        #courseScheduleModal .sched-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            min-width: 30px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        html.dark #courseScheduleModal .sched-nav-btn {
            border-color: #2e3b50;
            background: #151c28;
            color: #cbd5e1;
        }
        #courseScheduleModal .sched-nav-btn:hover {
            background: #f3f4f6;
        }
        html.dark #courseScheduleModal .sched-nav-btn:hover {
            background: #232d3f;
        }
        #courseScheduleModal .sched-tab-btn {
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #4b5563;
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        html.dark #courseScheduleModal .sched-tab-btn {
            border-color: #2e3b50;
            background: #192231;
            color: #cbd5e1;
        }
        #courseScheduleModal .sched-tab-btn.is-active {
            border-color: #1f2937;
            background: #1f2937;
            color: #ffffff;
        }
        html.dark #courseScheduleModal .sched-tab-btn.is-active {
            border-color: #e2e8f0;
            background: #e2e8f0;
            color: #0f172a;
        }
    </style>

    <div id="courseScheduleModal" role="dialog" aria-modal="true" aria-labelledby="scheduleModalTitle">
        <div class="sched-dialog">
            <!-- Header -->
            <div class="sched-header">
                <div class="min-w-0">
                    <h2 id="scheduleModalTitle" class="text-[17px] font-bold tracking-tight">Jadwal Belajar Saya</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Lihat tanggal mulai, target selesai, dan sudah sampai mana progres course yang Anda ikuti.</p>
                </div>
                <button
                    type="button"
                    id="btnCloseCourseSchedule"
                    data-dls-action-ignore="true"
                    aria-label="Tutup modal jadwal"
                    class="sched-close-btn"
                >
                    <span class="material-symbols-outlined text-[19px]" aria-hidden="true">close</span>
                </button>
            </div>

            <!-- Summary Strip -->
            <div id="scheduleSummaryStrip" class="sched-summary"></div>

            <!-- Body -->
            <div class="sched-body">
                <!-- Toolbar View Schedule -->
                <div class="sched-toolbar">
                    <div id="scheduleStatusTabs" style="display:flex;flex-wrap:wrap;gap:6px;">
                        <button type="button" data-sched-tab="all" data-dls-action-ignore="true" class="sched-tab sched-tab-btn is-active">Semua</button>
                        <button type="button" data-sched-tab="sedang_dipelajari" data-dls-action-ignore="true" class="sched-tab sched-tab-btn">Berjalan</button>
                        <button type="button" data-sched-tab="belum_dimulai" data-dls-action-ignore="true" class="sched-tab sched-tab-btn">Belum Mulai</button>
                        <button type="button" data-sched-tab="selesai" data-dls-action-ignore="true" class="sched-tab sched-tab-btn">Selesai</button>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                        <div style="display:flex;align-items:center;gap:4px;">
                            <button type="button" id="schedulePrevMonth" data-dls-action-ignore="true" class="sched-nav-btn" aria-label="Bulan sebelumnya">
                                <span class="material-symbols-outlined text-[17px]">chevron_left</span>
                            </button>
                            <span id="scheduleMonthLabel" style="min-width:110px;text-align:center;font-size:13px;font-weight:700;"></span>
                            <button type="button" id="scheduleNextMonth" data-dls-action-ignore="true" class="sched-nav-btn" aria-label="Bulan berikutnya">
                                <span class="material-symbols-outlined text-[17px]">chevron_right</span>
                            </button>
                            <button type="button" id="scheduleTodayBtn" data-dls-action-ignore="true" class="sched-nav-btn">
                                Hari Ini
                            </button>
                        </div>

                        <div style="display:inline-flex;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
                            <button type="button" id="btnViewCalendar" data-dls-action-ignore="true" class="sched-nav-btn" style="border:none;border-radius:0;background:#1f2937;color:#ffffff;">
                                Calendar View
                            </button>
                            <button type="button" id="btnViewTimeline" data-dls-action-ignore="true" class="sched-nav-btn" style="border:none;border-radius:0;">
                                Timeline View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Calendar View (Continuous Spanning Bars like Schedule Matrix) -->
                <div id="scheduleCalendarView">
                    <div class="sched-cal-wrap">
                        <div class="sched-weekdays">
                            <div>MIN</div><div>SEN</div><div>SEL</div><div>RAB</div><div>KAM</div><div>JUM</div><div>SAB</div>
                        </div>
                        <div id="scheduleCalendarGrid"></div>
                    </div>
                </div>

                <!-- Selected Day / Course Detail Strip OR Full Timeline View -->
                <div id="scheduleDetailSection" class="sched-detail-strip">
                    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;">
                        <div>
                            <h3 class="text-sm font-bold">Progres Course Saya</h3>
                            <p id="scheduleFilterNote" class="text-[11.5px] text-slate-500 dark:text-slate-400">Klik baris course atau tanggal pada kalender untuk melihat detail progresnya</p>
                        </div>
                        <button type="button" id="scheduleResetDay" data-dls-action-ignore="true" class="hidden sched-nav-btn" style="height:26px;font-size:11px;">
                            Tampilkan Semua
                        </button>
                    </div>
                    <div id="scheduleCourseList" class="sched-list"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const scheduleCourses = <?= json_encode($scheduleItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const modal = document.getElementById('courseScheduleModal');
            const openBtn = document.getElementById('btnOpenCourseSchedule');
            const closeBtn = document.getElementById('btnCloseCourseSchedule');
            const summaryStrip = document.getElementById('scheduleSummaryStrip');
            const monthLabel = document.getElementById('scheduleMonthLabel');
            const calGrid = document.getElementById('scheduleCalendarGrid');
            const listEl = document.getElementById('scheduleCourseList');
            const filterNote = document.getElementById('scheduleFilterNote');
            const resetDayBtn = document.getElementById('scheduleResetDay');
            const prevBtn = document.getElementById('schedulePrevMonth');
            const nextBtn = document.getElementById('scheduleNextMonth');
            const todayBtn = document.getElementById('scheduleTodayBtn');
            const btnViewCal = document.getElementById('btnViewCalendar');
            const btnViewTime = document.getElementById('btnViewTimeline');
            const calViewWrap = document.getElementById('scheduleCalendarView');
            const tabBtns = Array.from(document.querySelectorAll('.sched-tab'));

            if (!modal || !openBtn) return;

            const MONTHS_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const BAR_COLORS = ['#4f46e5', '#0d9488', '#d97706', '#e11d48', '#2563eb', '#059669', '#7c3aed'];
            const now = new Date();
            const todayIso = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            let viewYear = now.getFullYear();
            let viewMonth = now.getMonth();
            let selectedDate = null;
            let selectedCourseId = null;
            let activeTab = 'all';
            let viewMode = 'calendar';

            function toIso(d) {
                return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            }

            function formatIdDate(iso) {
                if (!iso) return '—';
                const parts = String(iso).split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) return iso;
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            }

            function daysDiffFromToday(iso) {
                if (!iso) return null;
                const [y, m, d] = iso.split('-').map(Number);
                const target = new Date(y, m - 1, d);
                const base = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                return Math.round((target - base) / 86400000);
            }

            function esc(str) {
                const div = document.createElement('div');
                div.textContent = String(str ?? '');
                return div.innerHTML;
            }

            function renderSummary() {
                const activeCount = scheduleCourses.filter(c => c.status === 'sedang_dipelajari').length;
                const pendingCount = scheduleCourses.filter(c => c.status === 'belum_dimulai').length;
                const doneCount = scheduleCourses.filter(c => c.status === 'selesai').length;
                const upcoming = scheduleCourses
                    .filter(c => c.status !== 'selesai' && c.deadline >= todayIso)
                    .sort((a, b) => a.deadline.localeCompare(b.deadline))[0];

                summaryStrip.innerHTML = `
                    <div class="sched-stat-card">
                        <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Sedang Berjalan</p>
                        <p class="mt-0.5 text-base font-bold text-amber-600">${activeCount} Course</p>
                    </div>
                    <div class="sched-stat-card">
                        <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Belum Dimulai</p>
                        <p class="mt-0.5 text-base font-bold">${pendingCount} Course</p>
                    </div>
                    <div class="sched-stat-card">
                        <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Selesai</p>
                        <p class="mt-0.5 text-base font-bold text-emerald-600">${doneCount} Course</p>
                    </div>
                    <div class="sched-stat-card">
                        <p class="text-[10.5px] font-semibold uppercase tracking-wide text-slate-400">Deadline Terdekat</p>
                        <p class="mt-0.5 truncate text-xs font-bold text-[#DC2626]">${upcoming ? esc(formatIdDate(upcoming.deadline)) : 'Tidak ada'}</p>
                    </div>
                `;
            }

            function renderCalendar() {
                monthLabel.textContent = `${MONTHS_ID[viewMonth]} ${viewYear}`;
                const firstOfMonth = new Date(viewYear, viewMonth, 1);
                const startGridDate = new Date(viewYear, viewMonth, 1 - firstOfMonth.getDay());
                const lastOfMonth = new Date(viewYear, viewMonth + 1, 0);
                const totalCells = Math.ceil((firstOfMonth.getDay() + lastOfMonth.getDate()) / 7) * 7;

                const visibleCourses = (activeTab === 'all'
                    ? scheduleCourses
                    : scheduleCourses.filter(c => c.status === activeTab)
                ).map((c, idx) => ({ ...c, barColor: BAR_COLORS[idx % BAR_COLORS.length] }));

                let html = '';
                for (let w = 0; w < totalCells; w += 7) {
                    const weekDays = [];
                    for (let d = 0; d < 7; d++) {
                        const cur = new Date(startGridDate.getFullYear(), startGridDate.getMonth(), startGridDate.getDate() + w + d);
                        weekDays.push({
                            iso: toIso(cur),
                            dayNum: cur.getDate(),
                            isOutside: cur.getMonth() !== viewMonth
                        });
                    }
                    const weekStart = weekDays[0].iso;
                    const weekEnd = weekDays[6].iso;

                    const bgHtml = weekDays.map(wd => {
                        const cls = [
                            'sched-day-cell',
                            wd.isOutside ? 'is-outside' : '',
                            wd.iso === todayIso ? 'is-today' : '',
                            wd.iso === selectedDate ? 'is-selected' : ''
                        ].filter(Boolean).join(' ');
                        return `<div class="${cls}" data-cal-date="${wd.iso}">${wd.dayNum}</div>`;
                    }).join('');

                    const weekCourses = visibleCourses.filter(c => c.start_date <= weekEnd && c.deadline >= weekStart);
                    const lanesHtml = weekCourses.slice(0, 3).map(c => {
                        const startIdx = Math.max(0, weekDays.findIndex(wd => wd.iso >= c.start_date));
                        let endIdx = 6;
                        for (let i = 6; i >= 0; i--) {
                            if (weekDays[i].iso <= c.deadline) { endIdx = i; break; }
                        }
                        const span = Math.max(1, endIdx - startIdx + 1);
                        const startsInWeek = c.start_date >= weekStart;
                        const endsInWeek = c.deadline <= weekEnd;
                        const radius = `${startsInWeek ? '6px' : '0'} ${endsInWeek ? '6px' : '0'} ${endsInWeek ? '6px' : '0'} ${startsInWeek ? '6px' : '0'}`;
                        const activeRing = selectedCourseId === c.id ? 'outline:2px solid #ffffff;outline-offset:-2px;' : '';
                        return `
                            <div class="sched-event-lane">
                                <button
                                    type="button"
                                    data-cal-course="${esc(c.id)}"
                                    data-dls-action-ignore="true"
                                    class="sched-event-bar"
                                    style="grid-column:${startIdx + 1} / span ${span};background:${c.barColor};border-radius:${radius};${activeRing}"
                                    title="${esc(c.title)} (${c.progress}%)"
                                >
                                    <span style="overflow:hidden;text-overflow:ellipsis;">${esc(c.title)}</span>
                                    <span style="opacity:0.9;font-size:10px;shrink:0;">${c.progress}%</span>
                                </button>
                            </div>
                        `;
                    }).join('');

                    const extraCount = weekCourses.length > 3 ? `<div style="padding:0 8px;font-size:10px;font-weight:600;color:#64748b;">+${weekCourses.length - 3} course lainnya</div>` : '';

                    html += `
                        <div class="sched-week-row">
                            <div class="sched-week-bg">${bgHtml}</div>
                            ${weekCourses.length ? `<div class="sched-week-events">${lanesHtml}${extraCount}</div>` : ''}
                        </div>
                    `;
                }

                calGrid.innerHTML = html;

                calGrid.querySelectorAll('[data-cal-date]').forEach(cell => {
                    cell.addEventListener('click', () => {
                        const d = cell.getAttribute('data-cal-date');
                        selectedCourseId = null;
                        selectedDate = selectedDate === d ? null : d;
                        renderCalendar();
                        renderCourses();
                    });
                });

                calGrid.querySelectorAll('[data-cal-course]').forEach(bar => {
                    bar.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const cid = bar.getAttribute('data-cal-course');
                        selectedDate = null;
                        selectedCourseId = selectedCourseId === cid ? null : cid;
                        renderCalendar();
                        renderCourses();
                    });
                });
            }

            function renderCourses() {
                let items = scheduleCourses.slice();
                if (activeTab !== 'all') {
                    items = items.filter(c => c.status === activeTab);
                }
                if (selectedCourseId) {
                    items = items.filter(c => c.id === selectedCourseId);
                    filterNote.textContent = items[0] ? `Detail progres: ${items[0].title}` : 'Detail course';
                    resetDayBtn.classList.remove('hidden');
                } else if (selectedDate) {
                    items = items.filter(c =>
                        c.start_date === selectedDate ||
                        c.deadline === selectedDate ||
                        (c.start_date <= selectedDate && c.deadline >= selectedDate)
                    );
                    filterNote.textContent = `Jadwal pada ${formatIdDate(selectedDate)} (${items.length} course)`;
                    resetDayBtn.classList.remove('hidden');
                } else {
                    filterNote.textContent = `Menampilkan ${items.length} course yang Anda ikuti`;
                    resetDayBtn.classList.add('hidden');
                }

                if (!items.length) {
                    listEl.innerHTML = `
                        <div class="sched-course-card" style="text-align:center;padding:24px 16px;color:#64748b;font-size:12.5px;">
                            ${scheduleCourses.length === 0
                                ? 'Anda belum mengikuti course apa pun. Daftar course terlebih dahulu untuk melihat jadwal belajar Anda.'
                                : 'Tidak ada jadwal course pada tanggal atau filter ini.'}
                        </div>
                    `;
                    return;
                }

                listEl.innerHTML = items.map(c => {
                    const diff = daysDiffFromToday(c.deadline);
                    let deadlineBadge = '';
                    if (c.status === 'selesai') {
                        deadlineBadge = '<span style="border-radius:9999px;background:rgba(16,185,129,0.12);padding:2px 8px;font-size:10.5px;font-weight:600;color:#059669;">Tuntas</span>';
                    } else if (diff !== null) {
                        if (diff < 0) deadlineBadge = `<span style="border-radius:9999px;background:rgba(225,29,72,0.1);padding:2px 8px;font-size:10.5px;font-weight:600;color:#be123c;">Lewat ${Math.abs(diff)} hr</span>`;
                        else if (diff === 0) deadlineBadge = '<span style="border-radius:9999px;background:rgba(225,29,72,0.12);padding:2px 8px;font-size:10.5px;font-weight:600;color:#be123c;">Hari Ini</span>';
                        else if (diff <= 7) deadlineBadge = `<span style="border-radius:9999px;background:rgba(217,119,6,0.12);padding:2px 8px;font-size:10.5px;font-weight:600;color:#b45309;">H-${diff}</span>`;
                        else deadlineBadge = `<span style="border-radius:9999px;background:rgba(148,163,184,0.14);padding:2px 8px;font-size:10.5px;font-weight:600;color:#64748b;">H-${diff}</span>`;
                    }

                    const statusBadge = c.status === 'selesai'
                        ? '<span style="border-radius:9999px;background:rgba(16,185,129,0.12);padding:2px 8px;font-size:10.5px;font-weight:600;color:#059669;">Selesai</span>'
                        : c.status === 'sedang_dipelajari'
                            ? '<span style="border-radius:9999px;background:rgba(217,119,6,0.12);padding:2px 8px;font-size:10.5px;font-weight:600;color:#b45309;">Sedang Dipelajari</span>'
                            : '<span style="border-radius:9999px;background:rgba(148,163,184,0.14);padding:2px 8px;font-size:10.5px;font-weight:600;color:#64748b;">Belum Dimulai</span>';

                    const step1 = c.has_access;
                    const step2 = c.progress > 0 || c.completed_sessions > 0;
                    const step2Done = c.modules_count > 0 ? c.completed_sessions >= c.modules_count : c.progress >= 100;
                    const step3 = c.quiz_status === 'selesai';
                    const step4 = c.status === 'selesai';

                    return `
                        <div class="sched-course-card">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                                <div class="min-w-0">
                                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;">
                                        <span style="border-radius:6px;background:rgba(148,163,184,0.14);padding:2px 6px;font-size:10px;font-weight:600;color:#64748b;">${esc(c.category)}</span>
                                        ${statusBadge}
                                        ${deadlineBadge}
                                    </div>
                                    <h4 class="mt-1.5 text-[13.5px] font-bold leading-snug">${esc(c.title)}</h4>
                                </div>
                                <span style="font-size:12px;font-weight:700;color:${step4 ? '#059669' : '#334155'};">${c.progress}%</span>
                            </div>

                            <div style="margin-top:10px;height:6px;width:100%;overflow:hidden;border-radius:9999px;background:rgba(148,163,184,0.18);">
                                <div style="height:100%;border-radius:9999px;background:${step4 ? '#16a34a' : '#e11d48'};width:${Math.min(100, Math.max(0, c.progress))}%;transition:width 0.25s ease;"></div>
                            </div>

                            <div style="margin-top:10px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;font-size:11px;color:#64748b;">
                                <span>Mulai: <strong>${esc(formatIdDate(c.start_date))}</strong></span>
                                <span>${c.is_estimated_deadline ? 'Target' : 'Deadline'}: <strong style="color:#e11d48;">${esc(formatIdDate(c.deadline))}</strong></span>
                            </div>

                            <div class="sched-steps">
                                <div style="color:${step1 ? '#059669' : '#94a3b8'};">
                                    <span class="sched-step-bar" style="background:${step1 ? '#16a34a' : '#cbd5e1'}"></span>
                                    <span>Mulai</span>
                                </div>
                                <div style="color:${step2Done ? '#059669' : step2 ? '#d97706' : '#94a3b8'};">
                                    <span class="sched-step-bar" style="background:${step2Done ? '#16a34a' : step2 ? '#d97706' : '#cbd5e1'}"></span>
                                    <span>Materi (${c.completed_sessions}/${c.modules_count})</span>
                                </div>
                                <div style="color:${step3 ? '#059669' : '#94a3b8'};">
                                    <span class="sched-step-bar" style="background:${step3 ? '#16a34a' : '#cbd5e1'}"></span>
                                    <span>Quiz</span>
                                </div>
                                <div style="color:${step4 ? '#059669' : '#94a3b8'};">
                                    <span class="sched-step-bar" style="background:${step4 ? '#16a34a' : '#cbd5e1'}"></span>
                                    <span>Selesai</span>
                                </div>
                            </div>

                            <div style="margin-top:10px;display:flex;justify-content:flex-end;">
                                <a href="${esc(c.url)}" style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;color:#e11d48;text-decoration:none;">
                                    ${step4 ? 'Lihat Detail' : 'Lanjutkan'}
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function openModal() {
                renderSummary();
                renderCalendar();
                renderCourses();
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            openBtn.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal(); });

            prevBtn?.addEventListener('click', () => {
                viewMonth -= 1;
                if (viewMonth < 0) { viewMonth = 11; viewYear -= 1; }
                renderCalendar();
            });
            nextBtn?.addEventListener('click', () => {
                viewMonth += 1;
                if (viewMonth > 11) { viewMonth = 0; viewYear += 1; }
                renderCalendar();
            });
            todayBtn?.addEventListener('click', () => {
                viewYear = now.getFullYear();
                viewMonth = now.getMonth();
                selectedDate = todayIso;
                renderCalendar();
                renderCourses();
            });
            resetDayBtn?.addEventListener('click', () => {
                selectedDate = null;
                selectedCourseId = null;
                renderCalendar();
                renderCourses();
            });
            function setViewMode(mode) {
                viewMode = mode;
                const isCal = mode === 'calendar';
                if (calViewWrap) calViewWrap.style.display = isCal ? 'block' : 'none';
                if (btnViewCal) {
                    btnViewCal.style.background = isCal ? '#1f2937' : '';
                    btnViewCal.style.color = isCal ? '#ffffff' : '';
                }
                if (btnViewTime) {
                    btnViewTime.style.background = !isCal ? '#1f2937' : '';
                    btnViewTime.style.color = !isCal ? '#ffffff' : '';
                }
            }
            btnViewCal?.addEventListener('click', () => setViewMode('calendar'));
            btnViewTime?.addEventListener('click', () => setViewMode('timeline'));
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    activeTab = btn.getAttribute('data-sched-tab') || 'all';
                    tabBtns.forEach(b => {
                        b.classList.toggle('is-active', b === btn);
                    });
                    renderCalendar();
                    renderCourses();
                });
            });
        })();
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
