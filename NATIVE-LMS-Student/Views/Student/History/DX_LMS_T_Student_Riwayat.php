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
        <?php
        $topbar_title = 'Riwayat Belajar';
        $topbar_subtitle = 'Pantau pencapaian dan unduh sertifikat pelatihan Anda.';
        $topbar_search_id = 'searchInput';
        $topbar_search_placeholder = 'Cari riwayat course...';
        include APP_ROOT . '/includes/components/DX_LMS_M_Workspace_Topbar.php';
        ?>
        <!-- Header & Stats Section -->
        <section class="px-container-margin py-2 lg:py-0 mb-5">
            <div class="lg:hidden mb-4">
                <h1 class="fd-h1 text-[22px] font-bold text-[#181824] dark:text-white mb-[4px]">Riwayat Belajar</h1>
                <p class="fd-body text-[13px] text-[#64748B]">Pantau pencapaian dan unduh sertifikat pelatihan Anda.</p>
                <div class="relative mt-3">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-[#64748B]">search</span>
                    <input type="text" oninput="var d=document.getElementById('searchInput');if(d){d.value=this.value;d.dispatchEvent(new Event('input'));}" class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-[13px]" placeholder="Cari riwayat course...">
                </div>
            </div>
            <!-- Stats Card -->
            <div class="lms-card p-[20px] flex items-center justify-between gap-4">
                <div>
                    <span class="rounded-full bg-[#DCFCE7] px-3 py-1 text-[11px] font-bold text-[#16A34A] uppercase tracking-wider">Total Kursus Selesai</span>
                    <p class="mt-2 text-[13px] text-[#64748B]">Semua pelatihan wajib yang berhasil Anda tuntaskan.</p>
                </div>
                <div class="flex items-center gap-[10px] shrink-0">
                    <span class="fd-mono text-[34px] leading-none text-[#181824] dark:text-white font-bold tracking-tight"><?= e(
                        (string) $stats['completed_count'],
                    ) ?></span>
                    <span class="material-symbols-outlined text-[#16A34A] text-[30px]" style='font-variation-settings: "FILL" 1;'>verified</span>
                </div>
            </div>
        </section>
        <!-- Sertifikat & Penghargaan Diterima -->
        <section id="student-certificates-section" class="px-container-margin mb-6">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[#E60012] text-[22px]">workspace_premium</span>
                    <h2 class="text-[16px] font-bold text-[#181824] dark:text-white">Sertifikat &amp; Penghargaan Diterima</h2>
                </div>
                <span id="student-certificates-count" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                    Memuat...
                </span>
            </div>
            <div id="student-certificates-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[14px]">
                <div class="lms-card p-4 text-[13px] text-[#64748B]">Memuat sertifikat &amp; penghargaan Anda...</div>
            </div>
        </section>

        <!-- Course List -->
        <section id="course-results" class="px-container-margin grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[18px] pt-2">
            <?php
            $card_context = 'riwayat';
            foreach ($completedCourses as $c):
                include APP_ROOT . '/includes/components/DX_LMS_M_Course_Card.php';
            endforeach;
            ?>
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
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const grid = document.getElementById('student-certificates-grid');
            const countBadge = document.getElementById('student-certificates-count');
            const apiMeta = document.querySelector('meta[name="dls-api-base"]');
            const apiBase = (apiMeta ? apiMeta.getAttribute('content') : '').replace(/\/+$/, '');
            if (!grid || !apiBase) {
                if (countBadge) countBadge.textContent = '0 Item';
                return;
            }

            const typeMeta = {
                certificate: { label: 'Sertifikat', cls: 'bg-red-50 text-[#E60012] border-red-200' },
                award: { label: 'Penghargaan', cls: 'bg-amber-50 text-amber-700 border-amber-200' },
                document: { label: 'Dokumen', cls: 'bg-blue-50 text-blue-700 border-blue-200' }
            };

            function formatCertDate(val) {
                if (!val) return '-';
                const d = new Date(val);
                if (Number.isNaN(d.getTime())) return '-';
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            }

            try {
                const fetcher = typeof window.dlsFetch === 'function'
                    ? (url) => window.dlsFetch(url, { credentials: 'include' })
                    : (url) => fetch(url, { credentials: 'include' });
                const res = await fetcher(`${apiBase}/certificates`);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                const items = Array.isArray(data.items) ? data.items : [];

                if (countBadge) countBadge.textContent = `${items.length} Item`;
                grid.innerHTML = '';

                if (items.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'lms-card p-5 col-span-full text-center text-[13px] text-[#64748B]';
                    empty.textContent = 'Belum ada sertifikat, penghargaan, atau dokumen yang dikirimkan kepada Anda.';
                    grid.appendChild(empty);
                    return;
                }

                items.forEach((item) => {
                    const meta = typeMeta[item.award_type] || typeMeta.certificate;
                    const card = document.createElement('article');
                    card.className = 'lms-card p-4 flex flex-col justify-between gap-3';

                    const top = document.createElement('div');
                    top.className = 'space-y-1.5';

                    const headerRow = document.createElement('div');
                    headerRow.className = 'flex items-center justify-between gap-2';

                    const badge = document.createElement('span');
                    badge.className = `inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-bold ${meta.cls}`;
                    badge.textContent = meta.label;
                    headerRow.appendChild(badge);

                    const dateEl = document.createElement('span');
                    dateEl.className = 'text-[11px] text-[#64748B]';
                    dateEl.textContent = formatCertDate(item.issued_at);
                    headerRow.appendChild(dateEl);

                    top.appendChild(headerRow);

                    const titleEl = document.createElement('h3');
                    titleEl.className = 'text-[14px] font-bold text-[#181824] dark:text-white leading-snug';
                    titleEl.textContent = item.title || 'Sertifikat';
                    top.appendChild(titleEl);

                    if (item.certificate_no) {
                        const noEl = document.createElement('p');
                        noEl.className = 'text-[11px] font-medium text-[#64748B]';
                        noEl.textContent = `No: ${item.certificate_no}`;
                        top.appendChild(noEl);
                    }

                    if (item.course_title) {
                        const courseEl = document.createElement('p');
                        courseEl.className = 'text-[12px] text-[#475569] dark:text-slate-300';
                        courseEl.textContent = `Course: ${item.course_title}`;
                        top.appendChild(courseEl);
                    }

                    if (item.description) {
                        const descEl = document.createElement('p');
                        descEl.className = 'text-[12px] text-[#64748B] line-clamp-2';
                        descEl.textContent = item.description;
                        top.appendChild(descEl);
                    }

                    const issuerEl = document.createElement('p');
                    issuerEl.className = 'text-[11px] text-[#64748B] pt-1';
                    issuerEl.textContent = `Dikirim oleh: ${item.issued_by_name || 'Pengajar / Admin'}`;
                    top.appendChild(issuerEl);

                    card.appendChild(top);

                    const actions = document.createElement('div');
                    actions.className = 'flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800';

                    const viewLink = document.createElement('a');
                    viewLink.href = `${apiBase}/certificates/${encodeURIComponent(item.certificate_id)}/file`;
                    viewLink.target = '_blank';
                    viewLink.rel = 'noopener noreferrer';
                    viewLink.className = 'flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[12px] font-semibold py-2 px-3 transition-colors';
                    viewLink.textContent = 'Lihat';
                    actions.appendChild(viewLink);

                    const downloadLink = document.createElement('a');
                    downloadLink.href = `${apiBase}/certificates/${encodeURIComponent(item.certificate_id)}/file?download=1`;
                    downloadLink.setAttribute('download', item.original_filename || 'sertifikat');
                    downloadLink.className = 'flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-semibold py-2 px-3 transition-colors';
                    downloadLink.textContent = 'Unduh';
                    actions.appendChild(downloadLink);

                    card.appendChild(actions);
                    grid.appendChild(card);
                });
            } catch (err) {
                if (countBadge) countBadge.textContent = '0 Item';
                grid.innerHTML = '';
                const errBox = document.createElement('div');
                errBox.className = 'lms-card p-4 col-span-full text-[13px] text-[#64748B]';
                errBox.textContent = 'Gagal memuat daftar sertifikat & penghargaan.';
                grid.appendChild(errBox);
            }
        });
    </script>

</body>

</html>
