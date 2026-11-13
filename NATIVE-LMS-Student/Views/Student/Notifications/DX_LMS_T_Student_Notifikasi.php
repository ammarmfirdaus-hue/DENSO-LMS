<?php
$page_title = 'Notifikasi - DENSO Learning';
$header_title = 'Notifikasi';
$active_tab = 'notifikasi';
$notif_count = (int) ($stats['unread_notifs'] ?? 0);
?>
<!doctype html>
<html lang="id">
<head>
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
    <script src="assets/js/DX_LMS_M_Notifications_Client.js" defer></script>
</head>
<body class="dls-app-shell min-h-screen bg-surface text-on-surface antialiased">
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main min-w-0 px-4 pb-24 pt-20 lg:ml-[240px] lg:px-8 lg:pb-10">
        <div class="mx-auto min-w-0 max-w-5xl">
            <?php
            $page_eyebrow = 'PUSAT INFORMASI';
            $page_title_heading = 'Notifikasi';
            $page_description = 'Pengumuman dan pengingat terkait pembelajaran Anda.';
            $page_actions_html = '<span class="inline-flex min-h-9 shrink-0 items-center rounded-full bg-[#FEF2F2] px-3.5 text-xs font-semibold text-[#DC2626]"><span class="fd-mono mr-1 font-bold" data-notification-unread-count>' . $notif_count . '</span> belum dibaca</span>';
            include APP_ROOT . '/includes/components/DX_LMS_M_Page_Header.php';
            ?>

            <div class="mb-[18px] grid min-w-0 gap-[12px] md:grid-cols-[minmax(0,1fr)_auto_auto]">
                <label class="relative block min-w-0">
                    <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[20px] text-[#737373]" aria-hidden="true">search</span>
                    <span class="sr-only">Cari notifikasi</span>
                    <input type="search" id="notificationSearch" placeholder="Cari notifikasi..." class="fd-input fd-search-input h-[42px] w-full min-w-0">
                </label>
                <label class="block min-w-0">
                    <span class="sr-only">Filter berdasarkan tanggal</span>
                    <input type="date" id="notificationDate" class="fd-input h-[42px] w-full min-w-0">
                </label>
                <button type="button" id="notificationMarkAll" class="fd-btn fd-btn-secondary fd-btn-md shrink-0 whitespace-nowrap disabled:cursor-not-allowed disabled:opacity-40">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">done_all</span>
                    Tandai semua dibaca
                </button>
            </div>

            <div class="mb-[18px] -mx-4 max-w-full px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0">
                <div class="max-w-full overflow-x-auto overscroll-x-contain pb-1 hide-scrollbar" role="region" aria-label="Kategori notifikasi" tabindex="0">
                    <div class="flex w-max min-w-full gap-[6px] rounded-full bg-[#F5F5F5] p-[6px]" role="tablist" aria-label="Filter kategori">
                        <?php foreach ([
                            'all' => 'Semua', 'unread' => 'Belum Dibaca', 'important' => 'Penting',
                            'announcement' => 'Pengumuman', 'course' => 'Course', 'assessment' => 'Assessment',
                            'forum' => 'Forum', 'system' => 'System',
                        ] as $filter => $label): ?>
                        <button type="button" class="notification-filter fd-filter-chip shrink-0 whitespace-nowrap" data-filter="<?= e($filter) ?>" aria-selected="false" role="tab">
                            <?= e($label) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <p class="mb-[12px] text-xs font-medium text-[#737373]" id="notificationResultCount" aria-live="polite">Memuat notifikasi...</p>
            <p id="notificationMessage" hidden class="mb-4"></p>
            <div id="studentNotifications" class="min-w-0 space-y-[12px]" aria-live="polite">
                <div class="fd-card rounded-[12px] bg-white p-8 text-center text-sm text-[#525252] shadow-[0_1px_3px_rgba(0,0,0,0.08)]">Memuat notifikasi...</div>
            </div>
            <div class="mt-4 flex justify-center">
                <button type="button" id="notificationLoadMore" class="fd-btn fd-btn-secondary fd-btn-md hidden">Muat lainnya</button>
            </div>
        </div>
    </main>

    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
