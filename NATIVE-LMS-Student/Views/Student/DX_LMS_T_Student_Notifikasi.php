<?php
/**
 * Tampilan Pusat Pemberitahuan Siswa DLS (DLS Student Notification Center View).
 *
 * Menampilkan daftar notifikasi terbaru yang berkaitan dengan
 * aktivitas pelatihan, penilaian, dan pesan sistem untuk siswa.
 */
$active_tab = 'notifikasi';

/** Proses kalkulasi statistik ringkas dari himpunan data yang diterima. */
$unread_count = 0;
$important_count = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) {
        $unread_count++;
        if (isset($n['priority']) && $n['priority'] === 'penting') {
            $important_count++;
        }
    }
}

/** Melakukan klasterisasi peringatan (notifikasi) berdasarkan satuan periode waktu. */
$grouped = [];
foreach ($notifications as $n) {
    $group = $n['time_group'] ?? 'sebelumnya';
    $grouped[$group][] = $n;
}

/** Melakukan pemetaan klasifikasi jadwal ke teks label informatif visual. */
$groupLabels = [
    'hari_ini' => 'Hari Ini',
    'minggu_lalu' => 'Minggu Lalu',
    'bulan_lalu' => 'Bulan Lalu',
];

/** Menghubungkan tipe pemberitahuan pengguna ke gaya tampilan maupun ikon responsif yang selaras. */
$typeStyles = [
    'course' => ['icon' => 'school', 'bg' => 'bg-tertiary-container/30 text-tertiary'],
    'assessment' => ['icon' => 'assignment', 'bg' => 'bg-primary-container/30 text-primary'],
    'forum' => ['icon' => 'forum', 'bg' => 'bg-secondary-container/30 text-secondary'],
    'system' => ['icon' => 'campaign', 'bg' => 'bg-surface-variant/50 text-on-surface-variant'],
    'video' => ['icon' => 'play_circle', 'bg' => 'bg-tertiary-container/30 text-tertiary'],
];
?>
<!DOCTYPE html>
<html lang="id" style="">

<head>
    <?php
    $page_title = 'Notification Center - LMS Learning System';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }



        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background min-h-screen font-body-md pt-safe flex flex-col">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Notifikasi';
    $notif_count = $unread_count; /** Konfigurasi properti variabel disesuaikan dengan perubahan parameter berjalan. */
    include 'includes/components/DX_LMS_M_Header.php';
    ?>
    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 pt-[72px] pb-[90px] px-container-margin w-full lg:ml-[240px] transition-all duration-300">
        <!-- Summary & Controls Panel -->
        <div class="relative overflow-hidden bg-gradient-to-br from-surface-container-lowest to-surface-container-low rounded-[2rem] p-6 md:p-8 mb-8 shadow-sm border border-outline-variant/30 flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center">
            <!-- Decor pattern -->
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-secondary/5 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex gap-4 items-center">
                <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary shrink-0 shadow-inner">
                    <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">notifications_active</span>
                </div>
                <div>
                    <h1 class="font-headline-md md:font-headline-lg font-bold text-on-surface mb-1.5 tracking-tight">Pusat Notifikasi</h1>
                    <p class="font-body-md text-on-surface-variant leading-loose mt-2">
                        Anda memiliki <span class="inline-block font-bold text-primary unread-count-text px-2 py-0.5 bg-primary/10 rounded-md"><?= $unread_count ?> belum dibaca</span> dan <span class="inline-block font-bold text-error important-count-text px-2 py-0.5 bg-error/10 rounded-md"><?= $important_count ?> penting</span>.
                    </p>
                </div>
            </div>

            <div class="relative z-10 flex flex-col sm:flex-row w-full lg:w-auto gap-3">
                <div class="relative flex-1 lg:w-72">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input type="text" id="searchNotif" placeholder="Cari pemberitahuan..." class="w-full bg-surface-container-highest/30 pl-11 pr-4 py-3 rounded-2xl border border-outline-variant/50 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/60">
                </div>
                <div class="relative w-full sm:w-auto">
                    <input type="date" id="dateFilterNotif" class="w-full sm:w-auto bg-surface-container-highest/30 px-4 py-3 rounded-2xl border border-outline-variant/50 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-body-md text-on-surface text-on-surface-variant/80" title="Filter Tanggal">
                </div>
                <button id="markAllRead" class="bg-surface text-on-surface font-label-md font-bold px-5 py-3 rounded-2xl border border-outline-variant/50 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center gap-2 justify-center whitespace-nowrap group active:scale-95">
                    <span class="material-symbols-outlined text-[20px] text-primary group-hover:scale-110 transition-transform">done_all</span>
                    Tandai Dibaca
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="overflow-x-auto pb-4 mb-6 -mx-4 px-4 md:mx-0 md:px-0 scrollbar-hide">
            <div class="flex gap-1.5 min-w-max p-1.5 bg-surface-container-lowest/60 rounded-2xl border border-outline-variant/30 w-fit shadow-sm">
                <button class="filter-tab active px-5 py-2.5 rounded-xl font-label-md font-bold transition-all bg-primary text-on-primary shadow-sm" data-filter="all">Semua</button>
                <button class="filter-tab px-5 py-2.5 rounded-xl font-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/80" data-filter="unread">Belum Dibaca</button>
                <button class="filter-tab px-5 py-2.5 rounded-xl font-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/80 flex items-center gap-1.5" data-filter="important">
                    <span class="material-symbols-outlined text-[16px]">error</span> Penting
                </button>
                <div class="w-px bg-outline-variant/30 mx-1 self-stretch my-2"></div>
                <button class="filter-tab px-5 py-2.5 rounded-xl font-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/80" data-filter="course">Course</button>
                <button class="filter-tab px-5 py-2.5 rounded-xl font-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/80" data-filter="assessment">Assessment</button>
                <button class="filter-tab px-5 py-2.5 rounded-xl font-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/80" data-filter="forum">Forum</button>
                <button class="filter-tab px-5 py-2.5 rounded-xl font-label-md font-medium transition-all text-on-surface-variant hover:text-on-surface hover:bg-surface-variant/80" data-filter="system">System</button>
            </div>
        </div>

        <!-- Notifications List -->
        <div id="notificationsContainer" class="flex flex-col gap-6">

            <?php if (empty($notifications)): ?>
            <!-- Empty State No Data -->
            <div class="text-center py-20 bg-surface-container-lowest/50 rounded-3xl border border-dashed border-outline-variant">
                <span class="material-symbols-outlined text-6xl text-on-surface-variant/30 mb-4">notifications_off</span>
                <p class="font-headline-sm font-bold text-on-surface">Belum Ada Notifikasi</p>
                <p class="font-body-md text-on-surface-variant mt-2 max-w-sm mx-auto">Anda belum memiliki notifikasi apapun saat ini. Aktivitas penting akan muncul di sini.</p>
            </div>
            <?php else: ?>
            <?php foreach ($grouped as $group => $items): ?>
            <div class="time-group-container flex flex-col gap-3">
                <h2 class="font-label-md text-label-md text-on-surface-variant px-2 uppercase tracking-widest font-bold mt-2"><?= e(
                    $groupLabels[$group] ?? ucfirst($group),
                ) ?></h2>

                <div class="flex flex-col gap-3">
                    <?php foreach ($items as $n):

                        $type = $n['type'] ?? 'course';
                        $style = $typeStyles[$type] ?? $typeStyles['course'];
                        $isRead = $n['is_read'] ?? true;
                        $priority = $n['priority'] ?? 'normal';
                        $title = $n['title'] ?? 'Notifikasi Baru';
                        $desc = $n['message'] ?? ($n['description'] ?? '');

                        $context = $n['context'] ?? '';
                        if (empty($context)) {
                            if ($type === 'course') {
                                $context = 'System - Course';
                            } elseif ($type === 'materi') {
                                $context = 'Materi';
                            } elseif ($type === 'quiz') {
                                $context = 'Assessment';
                            } elseif ($type === 'video') {
                                $context = 'Video';
                            } else {
                                $context = ucfirst($type);
                            }
                        }

                        $time = $n['time'] ?? '';

                        $actionLabel = $n['action_label'] ?? '';
                        if (empty($actionLabel)) {
                            if ($type === 'course') {
                                $actionLabel = 'Lihat Detail';
                            } elseif ($type === 'materi' || $type === 'video') {
                                $actionLabel = 'Lihat Materi';
                            } elseif ($type === 'quiz') {
                                $actionLabel = 'Lihat Hasil';
                            } else {
                                $actionLabel = 'Buka Forum';
                            }
                        }

                        $actionUrl = $n['action_url'] ?? '';
                        ?>
                    <div class="notification-card bg-surface-container-lowest rounded-2xl md:rounded-[1.5rem] p-4 md:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border <?= !$isRead
                        ? 'border-primary/40 bg-primary/[0.02]'
                        : 'border-outline-variant/30' ?> flex gap-4 w-full transition-all duration-300 hover:shadow-lg hover:border-primary/30 cursor-pointer group relative overflow-hidden" id="<?= e(
     $n['id'] ?? uniqid(),
 ) ?>" data-type="<?= e($type) ?>" data-read="<?= $isRead ? 'true' : 'false' ?>" data-priority="<?= e(
    $priority,
) ?>" data-search="<?= htmlspecialchars(strtolower($title . ' ' . $desc . ' ' . $context)) ?>">

                        <!-- Left Icon -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center <?= $style[
                            'bg'
                        ] ?> shadow-sm">
                            <span class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' <?= !$isRead
                                ? '1'
                                : '0' ?>;"><?= e($style['icon']) ?></span>
                        </div>

                        <!-- Content Area -->
                        <div class="flex-1 flex flex-col min-w-0">
                            <!-- Center Text -->
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-headline-sm font-bold <?= !$isRead
                                        ? 'text-on-surface'
                                        : 'text-on-surface-variant' ?> truncate"><?= $title ?></h4>
                                    <?php if ($priority === 'penting'): ?>
                                    <span class="px-2 py-0.5 bg-error/10 text-error rounded-md font-label-sm font-bold text-[10px] flex items-center gap-1 shrink-0">
                                        PENTING
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($desc)): ?>
                                <p class="font-body-md <?= !$isRead
                                    ? 'text-on-surface font-medium'
                                    : 'text-on-surface-variant' ?> leading-snug mb-3">
                                    <?= $desc ?>
                                </p>
                                <?php endif; ?>

                                <div class="flex items-center gap-2 mt-auto">
                                    <?php if ($context): ?>
                                    <span class="px-2.5 py-1 bg-surface-container rounded-lg font-label-sm text-on-surface-variant border border-outline-variant/30 flex items-center gap-1.5 w-fit">
                                        <span class="material-symbols-outlined text-[14px]">sell</span>
                                        <span class="truncate max-w-[200px]"><?= e($context) ?></span>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Bottom Action & Time -->
                            <div class="flex items-center justify-between mt-3 pt-2">
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-label-sm text-on-surface-variant/80 text-[12px] whitespace-nowrap"><?= e(
                                        $time,
                                    ) ?></span>
                                </div>

                                <?php if ($actionLabel && $actionUrl): ?>
                                <a href="<?= e(
                                    $actionUrl,
                                ) ?>" class="inline-flex items-center justify-center gap-1.5 font-label-sm md:font-label-md font-bold text-primary bg-primary/5 hover:bg-primary/10 px-4 py-2 rounded-full transition-all shrink-0 group">
                                    <span><?= e($actionLabel) ?></span>
                                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Empty State Search -->
            <div id="searchEmptyState" class="hidden text-center py-20 bg-surface-container-lowest/50 rounded-3xl border border-dashed border-outline-variant">
                <span class="material-symbols-outlined text-6xl text-on-surface-variant/30 mb-4">search_off</span>
                <p class="font-headline-sm font-bold text-on-surface">Tidak Ditemukan</p>
                <p class="font-body-md text-on-surface-variant mt-2">Tidak ada notifikasi yang cocok dengan filter atau pencarian Anda.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- BottomNavBar Shared Component -->
    <?php include 'includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchNotif');
            const filterTabs = document.querySelectorAll('.filter-tab');
            const cards = document.querySelectorAll('.notification-card');
            const groups = document.querySelectorAll('.time-group-container');
            const emptyState = document.getElementById('searchEmptyState');
            const markAllBtn = document.getElementById('markAllRead');

            let currentFilter = 'all';
            let searchTerm = '';

            /** Modul kapabilitas untuk penyortiran ragam data terstruktur. */
            filterTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    /** Sinkronisasi informasi terkini mengenai kondisi aktif sistem. */
                    filterTabs.forEach(t => {
                        t.classList.remove('bg-primary', 'text-on-primary', 'shadow-sm', 'font-bold');
                        t.classList.add('text-on-surface-variant', 'hover:text-on-surface', 'hover:bg-surface-variant/80', 'font-medium');
                    });
                    tab.classList.remove('text-on-surface-variant', 'hover:text-on-surface', 'hover:bg-surface-variant/80', 'font-medium');
                    tab.classList.add('bg-primary', 'text-on-primary', 'shadow-sm', 'font-bold');

                    currentFilter = tab.dataset.filter;
                    applyFilters();
                });
            });

            /** Modul kapabilitas sistem pencarian dinamis pada koleksi antarmuka. */
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    searchTerm = e.target.value.toLowerCase().trim();
                    applyFilters();
                });
            }

            /** Pengaplikasian variabel filter terhadap koleksi elemen aktif. */
            function applyFilters() {
                let visibleCount = 0;

                cards.forEach(card => {
                    const type = card.dataset.type;
                    const isRead = card.dataset.read;
                    const priority = card.dataset.priority;
                    const searchText = card.dataset.search;

                    let matchesFilter = false;
                    if (currentFilter === 'all') matchesFilter = true;
                    else if (currentFilter === 'unread') matchesFilter = (isRead === 'false');
                    else if (currentFilter === 'important') matchesFilter = (priority === 'penting');
                    else matchesFilter = (type === currentFilter);

                    let matchesSearch = searchText.includes(searchTerm);

                    if (matchesFilter && matchesSearch) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                /** Menyembunyikan kelompok direktori jika hasil masukan bernilai kosong. */
                groups.forEach(group => {
                    const visibleCards = group.querySelectorAll('.notification-card[style="display: flex;"], .notification-card:not([style*="display: none"])');
                    if (visibleCards.length === 0) {
                        group.style.display = 'none';
                    } else {
                        group.style.display = 'flex';
                    }
                });

                /** Menampilkan peringatan informatif saat ruang masukan bernilai kosong. */
                if (emptyState) {
                    if (visibleCount === 0) {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }
                }
            }

            /** Menandai keseluruhan kumpulan notifikasi dengan penanda status telah dibaca. */
            if (markAllBtn) {
                markAllBtn.addEventListener('click', () => {
                    cards.forEach(card => {
                        if (card.dataset.read === 'false') {
                            card.dataset.read = 'true';
                            /** Menyelaraskan seluruh status komponen pada antarmuka pengguna terbaru. */
                            card.classList.remove('border-primary/40', 'bg-primary/[0.02]');
                            card.classList.add('border-outline-variant/30');

                            const title = card.querySelector('h4');
                            if (title) {
                                title.classList.remove('text-on-surface');
                                title.classList.add('text-on-surface-variant');
                            }

                            const desc = card.querySelector('p.font-body-md');
                            if (desc) {
                                desc.classList.remove('text-on-surface', 'font-medium');
                                desc.classList.add('text-on-surface-variant');
                            }

                            const dot = card.querySelector('.unread-dot');
                            if (dot) dot.remove();

                            const icon = card.querySelector('.material-symbols-outlined[style*="FILL\' 1"]');
                            if (icon) icon.style.fontVariationSettings = "'FILL' 0";

                            /** Perekaman metadata pengaturan langsung menuju sesi memori penyimpan (Local Storage). */
                            if (card.id && !readNotifs.includes(card.id)) {
                                readNotifs.push(card.id);
                            }
                        }
                    });

                    localStorage.setItem('dls_read_notifs', JSON.stringify(readNotifs));

                    /** Melakukan pemutakhiran tampilan jumlah pemberitahuan tekstual secara interaktif. */
                    const unreadTexts = document.querySelectorAll('.unread-count-text');
                    unreadTexts.forEach(text => {
                        text.textContent = '0 belum dibaca';
                    });

                    const impTexts = document.querySelectorAll('.important-count-text');
                    impTexts.forEach(text => {
                        text.textContent = '0 penting';
                    });

                    /** Menyembunyikan emblem pada papan navigasi. */
                    const navBadge = document.querySelector('.notification-dot');
                    if (navBadge) navBadge.style.display = 'none';

                    /** Merender ulang tampilan filter jika antarmuka berada dalam kondisi pencarian. */
                    if (currentFilter === 'unread') {
                        applyFilters();
                    }
                });
            }

            /** Membaca parameter catatan penyelesaian notifikasi pada sistem basis lokal. */
            const readNotifs = JSON.parse(localStorage.getItem('dls_read_notifs') || '[]');

            /** Menyelaraskan struktur halaman berbasis elemen interaktif pada kondisi pembukaan peramban pertama. */
            if (readNotifs.length > 0) {
                let actualUnread = parseInt(document.querySelector('.unread-count-text')?.textContent) || 0;
                let actualImportant = parseInt(document.querySelector('.important-count-text')?.textContent) || 0;

                cards.forEach(card => {
                    if (card.dataset.read === 'false' && card.id && readNotifs.includes(card.id)) {
                        card.dataset.read = 'true';
                        card.classList.remove('border-primary/40', 'bg-primary/[0.02]');
                        card.classList.add('border-outline-variant/30');

                        const title = card.querySelector('h4');
                        if (title) {
                            title.classList.remove('text-on-surface');
                            title.classList.add('text-on-surface-variant');
                        }

                        const desc = card.querySelector('p.font-body-md');
                        if (desc) {
                            desc.classList.remove('text-on-surface', 'font-medium');
                            desc.classList.add('text-on-surface-variant');
                        }

                        const dot = card.querySelector('.unread-dot');
                        if (dot) dot.remove();

                        const icon = card.querySelector('.material-symbols-outlined[style*="FILL\' 1"]');
                        if (icon) icon.style.fontVariationSettings = "'FILL' 0";

                        actualUnread--;
                        if (card.dataset.priority === 'penting') {
                            actualImportant--;
                        }
                    }
                });

                /** Menghitung mutasi pada elemen pemberitahuan untuk akurasi data. */
                if (actualUnread >= 0) {
                    const unreadTexts = document.querySelectorAll('.unread-count-text');
                    unreadTexts.forEach(text => {
                        text.textContent = actualUnread + ' belum dibaca';
                    });

                    const navBadge = document.querySelector('.notification-dot');
                    if (navBadge) {
                        if (actualUnread === 0) {
                            navBadge.style.display = 'none';
                        } else {
                            navBadge.textContent = actualUnread > 99 ? '99+' : actualUnread;
                        }
                    }
                }

                if (actualImportant >= 0) {
                    const impTexts = document.querySelectorAll('.important-count-text');
                    impTexts.forEach(text => {
                        text.textContent = actualImportant + ' penting';
                    });
                }
            }

            /** Menyematkan tanda centang visual kepada kartu interaktif setelah elemen terpilih. */
            cards.forEach(card => {
                card.addEventListener('click', (e) => {
                    /** Pengendalian penguncian akses pada saat eksekusi tombol instruksi dijalankan. */ 
                    /** Arah tujuan tautan berlanjut sembari penanda interaktif menyala optimal. */
                    if (card.dataset.read === 'false') {
                        card.dataset.read = 'true';
                        card.classList.remove('border-primary/40', 'bg-primary/[0.02]');
                        card.classList.add('border-outline-variant/30');

                        const title = card.querySelector('h4');
                        if (title) {
                            title.classList.remove('text-on-surface');
                            title.classList.add('text-on-surface-variant');
                        }

                        const desc = card.querySelector('p.font-body-md');
                        if (desc) {
                            desc.classList.remove('text-on-surface', 'font-medium');
                            desc.classList.add('text-on-surface-variant');
                        }

                        const dot = card.querySelector('.unread-dot');
                        if (dot) dot.remove();

                        const icon = card.querySelector('.material-symbols-outlined[style*="FILL\' 1"]');
                        if (icon) icon.style.fontVariationSettings = "'FILL' 0";

                        if (card.id && !readNotifs.includes(card.id)) {
                            readNotifs.push(card.id);
                            localStorage.setItem('dls_read_notifs', JSON.stringify(readNotifs));
                        }

                        /** Menyelaraskan perhitungan elemen ringkas notifikasi menggunakan parameter sederhana logis. */
                        const unreadTexts = document.querySelectorAll('.unread-count-text');
                        let currentCount = parseInt(unreadTexts[0].textContent) || 0;
                        if (currentCount > 0) {
                            currentCount--;
                            unreadTexts.forEach(text => {
                                text.textContent = currentCount + ' belum dibaca';
                            });

                            const navBadge = document.querySelector('.notification-dot');
                            if (navBadge) {
                                if (currentCount === 0) {
                                    navBadge.style.display = 'none';
                                } else {
                                    navBadge.textContent = currentCount > 99 ? '99+' : currentCount;
                                }
                            }
                        }

                        if (card.dataset.priority === 'penting') {
                            const impTexts = document.querySelectorAll('.important-count-text');
                            let currentImp = parseInt(impTexts[0]?.textContent) || 0;
                            if (currentImp > 0) {
                                currentImp--;
                                impTexts.forEach(text => {
                                    text.textContent = currentImp + ' penting';
                                });
                            }
                        }
                    }
                });
            });
        });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
