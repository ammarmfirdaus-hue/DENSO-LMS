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
            <header class="mb-6 flex min-w-0 flex-wrap items-end justify-between gap-4 border-b border-outline-variant/40 pb-5">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary">PUSAT INFORMASI</p>
                    <h1 class="mt-2 text-2xl font-bold text-on-surface sm:text-3xl">Notifikasi</h1>
                    <p class="mt-2 text-sm text-on-surface-variant">Pengumuman dan pengingat terkait pembelajaran Anda.</p>
                </div>
                <span class="inline-flex min-h-9 shrink-0 items-center rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 text-sm font-semibold text-on-surface-variant">
                    <span data-notification-unread-count><?= $notif_count ?></span>&nbsp;belum dibaca
                </span>
            </header>

            <div class="mb-4 grid min-w-0 gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto]">
                <label class="relative block min-w-0">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-on-surface-variant" aria-hidden="true">search</span>
                    <span class="sr-only">Cari notifikasi</span>
                    <input type="search" id="notificationSearch" placeholder="Cari notifikasi..." class="h-11 w-full min-w-0 rounded-lg border border-outline-variant/50 bg-surface-container-lowest pl-10 pr-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
                </label>
                <label class="block min-w-0">
                    <span class="sr-only">Filter berdasarkan tanggal</span>
                    <input type="date" id="notificationDate" class="h-11 w-full min-w-0 rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 text-sm text-on-surface-variant outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
                </label>
                <button type="button" id="notificationMarkAll" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-4 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low disabled:cursor-not-allowed disabled:opacity-50">
                    <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">done_all</span>
                    Tandai semua dibaca
                </button>
            </div>

            <div class="mb-5 -mx-4 max-w-full px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0">
                <div class="max-w-full overflow-x-auto overscroll-x-contain pb-1 [scrollbar-width:thin]" role="region" aria-label="Kategori notifikasi" tabindex="0">
                    <div class="flex w-max min-w-full gap-1 rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-1.5" role="tablist" aria-label="Filter kategori">
                        <?php foreach ([
                            'all' => 'Semua', 'unread' => 'Belum Dibaca', 'important' => 'Penting',
                            'announcement' => 'Pengumuman', 'course' => 'Course', 'assessment' => 'Assessment',
                            'forum' => 'Forum', 'system' => 'System',
                        ] as $filter => $label): ?>
                        <button type="button" class="notification-filter min-h-10 shrink-0 whitespace-nowrap rounded-lg px-3 text-xs font-semibold text-on-surface-variant transition hover:bg-surface-container-low hover:text-on-surface sm:px-4 sm:text-sm" data-filter="<?= e($filter) ?>" aria-selected="false" role="tab">
                            <?= e($label) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <p class="mb-3 text-xs text-on-surface-variant" id="notificationResultCount" aria-live="polite">Memuat notifikasi...</p>
            <p id="notificationMessage" hidden class="mb-4"></p>
            <div id="studentNotifications" class="min-w-0 space-y-3" aria-live="polite">
                <div class="rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-8 text-center text-sm text-on-surface-variant">Memuat notifikasi...</div>
            </div>
            <div class="mt-4 flex justify-center">
                <button type="button" id="notificationLoadMore" class="hidden min-h-10 rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-4 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low">Muat lainnya</button>
            </div>
        </div>
    </main>

    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
