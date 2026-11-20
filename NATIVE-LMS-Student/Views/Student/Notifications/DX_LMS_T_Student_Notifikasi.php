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
            $topbar_title = 'Notifikasi';
            $topbar_subtitle = 'Pengumuman dan pengingat terkait pembelajaran Anda.';
            $topbar_show_search = false;
            include APP_ROOT . '/includes/components/DX_LMS_M_Workspace_Topbar.php';
            ?>
            <div class="lg:hidden">
                <?php
                $page_eyebrow = 'PUSAT INFORMASI';
                $page_title_heading = 'Notifikasi';
                $page_description = 'Pengumuman dan pengingat terkait pembelajaran Anda.';
                $page_actions_html = '<span class="inline-flex min-h-9 shrink-0 items-center rounded-full bg-[#FEF2F2] px-3.5 text-xs font-semibold text-[#DC2626]"><span class="fd-mono mr-1 font-bold" data-notification-unread-count>' . $notif_count . '</span> belum dibaca</span>';
                include APP_ROOT . '/includes/components/DX_LMS_M_Page_Header.php';
                ?>
            </div>
                    
        <section class="w-full max-w-5xl">
            <div class="flex flex-nowrap items-center gap-3 overflow-x-auto rounded-xl border border-[#E5E7EB] bg-white p-3 shadow-sm">

                <!-- Search -->
                <label class="relative block min-w-[240px] flex-1">
                    <span
                        class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-[#737373]"
                        aria-hidden="true">
                        search
                    </span>
                    <span class="sr-only">Cari notifikasi</span>
                    <input
                        type="search"
                        id="notificationSearch"
                        placeholder="Cari notifikasi..."
                        class="fd-input fd-search-input h-[42px] w-full rounded-lg pl-11 text-sm">
                </label>

                <!-- Date Filter -->
                <label class="block w-[165px] shrink-0">
                    <span class="sr-only">Filter berdasarkan tanggal</span>
                    <input
                        type="date"
                        id="notificationDate"
                        class="fd-input h-[42px] w-full rounded-lg text-sm">
                </label>

                <!-- Mark All as Read -->
                <button
                    type="button"
                    id="notificationMarkAll"
                    class="fd-btn fd-btn-secondary fd-btn-md inline-flex h-[42px] shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-3.5 text-sm disabled:cursor-not-allowed disabled:opacity-40">
                    <span class="material-symbols-outlined text-[19px]" aria-hidden="true">
                        done_all
                    </span>
                    Tandai semua dibaca
                </button>

                <!-- Clear History -->
                <button
                    type="button"
                    id="notificationClearAll"
                    class="fd-btn fd-btn-secondary fd-btn-md inline-flex h-[42px] shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-3.5 text-sm text-[#DC2626] hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-40">
                    <span class="material-symbols-outlined text-[19px]" aria-hidden="true">
                        delete
                    </span>
                    Hapus Riwayat
                </button>

            </div>
        </section>
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
            <div class="mt-4">
                <?php include APP_ROOT . '/includes/components/DX_LMS_M_Pagination.php'; ?>
            </div>
        </div>
    </main>

    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
    <script>
        (function() {
            var input = document.getElementById('notificationSearch');
            if (!input || !input.parentElement) return;
            var wrap = input.parentElement;
            wrap.classList.add('relative');
            var box = document.createElement('div');
            box.className = 'dls-workspace-search-dropdown hidden';
            box.setAttribute('role', 'listbox');
            wrap.appendChild(box);

            function render() {
                var q = input.value.trim().toLowerCase();
                if (!q) {
                    box.classList.add('hidden');
                    box.innerHTML = '';
                    return;
                }
                var seen = Object.create(null);
                var items = [];
                document.querySelectorAll('#studentNotifications h2, #studentNotifications h3, #studentNotifications strong').forEach(function(el) {
                    var txt = (el.textContent || '').replace(/\s+/g, ' ').trim();
                    if (!txt || txt.length < 2) return;
                    var key = txt.toLowerCase();
                    if (seen[key] || key.indexOf(q) === -1) return;
                    seen[key] = true;
                    items.push(txt);
                });
                box.innerHTML = '';
                if (!items.length) {
                    box.classList.add('hidden');
                    return;
                }
                items.slice(0, 6).forEach(function(label) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'dls-workspace-search-item';
                    btn.textContent = label;
                    btn.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        input.value = label;
                        box.classList.add('hidden');
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    box.appendChild(btn);
                });
                box.classList.remove('hidden');
            }

            input.addEventListener('input', render);
            input.addEventListener('focus', function() { if (input.value.trim()) render(); });
            document.addEventListener('click', function(e) { if (!wrap.contains(e.target)) box.classList.add('hidden'); });
        })();
    </script>
</body>
</html>
