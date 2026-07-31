<?php
/**
 * Tampilan Sentra Notifikasi (Notification Center) DLS Admin.
 */

$header_title = 'Notifikasi Operasional';
$active_tab   = 'notification';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Pusat Notifikasi Admin';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        /* ── Shell ─────────────────────────────────────── */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }

        /* ── Header Card ───────────────────────────────── */
        .notif-header-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            padding: 20px;
            margin-bottom: 16px;
        }
        .notif-header-inner {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .notif-header-icon-wrap {
            width: 56px; height: 56px;
            background: #FEF2F2;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .notif-header-icon-wrap .material-symbols-outlined {
            font-size: 28px; color: #F87171;
        }
        .notif-header-text { flex: 1; min-width: 0; }
        .notif-header-text h1 { font-size: 15px; font-weight: 700; color: #111827; margin: 0; }
        .notif-header-text p  { font-size: 13px; color: #6B7280; margin: 4px 0 0; }
        .notif-count-badge {
            font-size: 11px; font-weight: 700;
            padding: 2px 8px; border-radius: 6px; display: inline-block;
        }
        .badge-unread  { background:#F3F4F6; color:#374151; }
        .badge-penting-hdr { background:#FEE2E2; color:#DC2626; }

        /* ── Controls (search, date, button) ──────────── */
        .notif-controls {
            display: flex; align-items: center; gap: 8px;
            flex-wrap: wrap;
        }
        .notif-search-wrap { position: relative; }
        .notif-search-wrap .material-symbols-outlined {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            font-size: 16px; color: #9CA3AF; pointer-events: none;
        }
        .notif-search-input {
            padding: 8px 12px 8px 34px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            background: #F9FAFB;
            font-size: 13px;
            color: #374151;
            width: 200px;
            transition: border-color .15s, box-shadow .15s;
        }
        .notif-search-input:focus {
            outline: none;
            border-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,.1);
        }
        .notif-date-input {
            padding: 8px 10px;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            background: #F9FAFB;
            font-size: 13px;
            color: #374151;
            transition: border-color .15s, box-shadow .15s;
        }
        .notif-date-input:focus {
            outline: none;
            border-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,.1);
        }
        .btn-tandai-dibaca {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-size: 13px; font-weight: 500; color: #374151;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }
        .btn-tandai-dibaca:hover { background: #F9FAFB; }
        .btn-tandai-dibaca .material-symbols-outlined { font-size: 16px; color: #9CA3AF; }


        /* ── Tab Pills ─────────────────────────────────── */
        .tab-pills-wrap {
            display: flex; align-items: center; gap: 4px;
            margin-bottom: 16px;
            overflow-x: auto; padding-bottom: 4px;
        }
        .tab-pill-btn {
            padding: 8px 18px;
            border-radius: 9999px;
            font-size: 14px; font-weight: 500;
            border: none; background: none;
            color: #6B7280; cursor: pointer;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        .tab-pill-btn:hover { background: #F3F4F6; color: #374151; }
        .tab-pill-btn.is-active { background: #DC2626; color: #fff; }

        /* ── Section label ─────────────────────────────── */
        .section-lbl {
            font-size: 11px; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: #9CA3AF;
            padding: 20px 0 10px;
        }

        /* ── Notification Card ─────────────────────────── */
        .notif-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: background .15s;
        }
        .notif-card:hover { background: #FAFAFA; }
        .notif-icon-box {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 2px;
        }
        .notif-icon-box .material-symbols-outlined { font-size: 22px; }
        .icon-warning { background:#FEE2E2; color:#DC2626; }
        .icon-info    { background:#E0E7FF; color:#4338CA; }
        .icon-success { background:#D1FAE5; color:#059669; }
        .icon-system  { background:#F3F4F6; color:#6B7280; }

        .notif-body { flex: 1; min-width: 0; }
        .notif-title-row {
            display: flex; align-items: center; gap: 8px;
            flex-wrap: wrap; margin-bottom: 4px;
        }
        .notif-title { font-size: 14px; font-weight: 700; color: #111827; }
        .badge-penting {
            background: #FEE2E2; color: #DC2626;
            font-size: 10px; font-weight: 700; letter-spacing: .5px;
            padding: 1px 7px; border-radius: 4px; text-transform: uppercase;
        }
        .notif-msg { font-size: 13px; color: #6B7280; line-height: 1.55; margin-bottom: 8px; }
        .notif-tag {
            display: inline-flex; align-items: center; gap: 4px;
            background: #F9FAFB; border: 1px solid #E5E7EB;
            color: #6B7280; font-size: 12px;
            padding: 3px 10px; border-radius: 20px;
            margin-bottom: 8px;
        }
        .notif-tag .material-symbols-outlined { font-size: 13px; color: #9CA3AF; }
        .notif-time { font-size: 12px; color: #9CA3AF; }

        .notif-action { flex-shrink: 0; align-self: flex-end; padding-bottom: 2px; }
        .notif-action a {
            display: inline-flex; align-items: center; gap: 2px;
            font-size: 13px; font-weight: 700; color: #DC2626;
            text-decoration: none; transition: color .15s;
            white-space: nowrap;
        }
        .notif-action a:hover { color: #B91C1C; }
        .notif-action a .material-symbols-outlined { font-size: 17px; }

        /* ── Empty State ───────────────────────────────── */
        .notif-empty {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            padding: 56px 20px;
            text-align: center;
            margin-top: 12px;
            display: none;
        }
        .notif-empty .material-symbols-outlined { font-size: 40px; color: #D1D5DB; display: block; margin-bottom: 12px; }
        .notif-empty h3 { font-size: 15px; font-weight: 600; color: #374151; margin: 0; }
        .notif-empty p  { font-size: 13px; color: #9CA3AF; margin: 6px 0 0; }
        .notif-empty button { margin-top: 12px; color: #EF4444; font-weight: 500; background: none; border: none; cursor: pointer; font-size: 13px; text-decoration: underline; }
    </style>
</head>
<body class="dls-app-shell bg-gray-50 text-gray-900 font-sans antialiased min-h-screen flex flex-col pt-safe">

    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-4 md:px-6 pb-[88px] lg:ml-[240px]">

        <?php
        $total_notif   = count($notifications);
        $penting_count = 0;
        foreach ($notifications as $n) {
            if (!empty($n['is_penting'])) $penting_count++;
        }
        $groups = [];
        foreach ($notifications as $n) {
            $groups[$n['group'] ?? 'hari_ini'][] = $n;
        }
        $group_labels = [
            'hari_ini'    => 'Hari Ini',
            'minggu_lalu' => 'Minggu Lalu',
            'bulan_lalu'  => 'Bulan Lalu',
        ];
        $type_cfg = [
            'Warning' => ['icon_class' => 'icon-warning'],
            'Info'    => ['icon_class' => 'icon-info'],
            'Success' => ['icon_class' => 'icon-success'],
        ];
        ?>

        <!-- ── HEADER CARD ──────────────────────────────────────── -->
        <div class="notif-header-card">
            <div class="notif-header-inner">

                <!-- Kiri: Icon + Judul + Summary -->
                <div class="notif-header-icon-wrap">
                    <span class="material-symbols-outlined">notifications</span>
                </div>
                <div class="notif-header-text">
                    <h1>Pusat Notifikasi</h1>
                    <p>
                        Anda memiliki
                        <span class="notif-count-badge badge-unread"><?= $total_notif ?> belum dibaca</span>
                        dan
                        <span class="notif-count-badge badge-penting-hdr"><?= $penting_count ?> penting</span>
                    </p>
                </div>

                <!-- Kanan: Search + Date + Tandai Dibaca -->
                <div class="notif-controls" style="margin-left:auto;">
                    <!-- Search -->
                    <div class="notif-search-wrap">
                        <span class="material-symbols-outlined">search</span>
                        <input
                            type="text"
                            id="search-input"
                            class="notif-search-input"
                            placeholder="Cari pemberitahuan..."
                            oninput="applyAllFilters()"
                        >
                    </div>

                    <!-- Date Picker -->
                    <input
                        type="date"
                        id="filter-date"
                        class="notif-date-input"
                        onchange="applyAllFilters()"
                    >

                    <!-- Tandai Dibaca -->
                    <button
                        type="button"
                        class="btn-tandai-dibaca"
                        onclick="if(typeof DLS!=='undefined'&&DLS.toast){DLS.toast('Semua notifikasi telah ditandai sebagai dibaca','success');}else{alert('Semua notifikasi telah ditandai sebagai dibaca');}"
                    >
                        <span class="material-symbols-outlined">done_all</span>
                        Tandai Dibaca
                    </button>
                </div>
            </div>

        </div>
        <!-- ────────────────────────────────────────────────────── -->


        <!-- ── TAB PILLS ───────────────────────────────────────── -->
        <div class="tab-pills-wrap">
            <?php
            $tabs = [
                'semua'      => 'Semua',
                'penting'    => 'Penting',
                'assignment' => 'Assignment',
                'course'     => 'Course',
                'assessment' => 'Assessment',
                'system'     => 'Sistem',
            ];
            foreach ($tabs as $key => $label): ?>
                <button
                    type="button"
                    data-tab="<?= $key ?>"
                    onclick="setTab('<?= $key ?>')"
                    class="tab-pill-btn<?= $key === 'semua' ? ' is-active' : '' ?>"
                ><?= $label ?></button>
            <?php endforeach; ?>
        </div>
        <!-- ────────────────────────────────────────────────────── -->


        <!-- ── NOTIFICATION LIST ───────────────────────────────── -->
        <?php if (empty($notifications)): ?>
            <div style="background:#fff;border:1px solid #E5E7EB;border-radius:16px;padding:64px 20px;text-align:center;">
                <span class="material-symbols-outlined" style="font-size:40px;color:#D1D5DB;display:block;margin-bottom:12px;">notifications_off</span>
                <h3 style="font-size:15px;font-weight:600;color:#374151;margin:0;">Tidak Ada Notifikasi</h3>
                <p style="font-size:13px;color:#9CA3AF;margin-top:6px;">Seluruh peringatan dan aktivitas sistem dalam keadaan bersih.</p>
            </div>
        <?php else: ?>

            <div id="notif-list">

                <?php foreach ($group_labels as $group_key => $group_name): ?>
                    <?php if (empty($groups[$group_key])) continue; ?>

                    <div class="group-section" data-group-section="<?= $group_key ?>">

                        <div class="section-lbl"><?= $group_name ?></div>

                        <?php foreach ($groups[$group_key] as $notif): ?>
                            <?php
                            $cfg     = $type_cfg[$notif['type']] ?? $type_cfg['Info'];
                            $icon    = $notif['icon'] ?? 'notifications';
                            $cat     = $notif['category'] ?? 'system';
                            $isPenting = !empty($notif['is_penting']) ? 'true' : 'false';
                            $notifDate = $notif['date'] ?? '';
                            $titleLow  = strtolower($notif['title'] ?? '');
                            $msgLow    = strtolower($notif['message'] ?? '');
                            $tagLow    = strtolower($notif['tag'] ?? '');
                            ?>
                            <div
                                class="notif-card"
                                data-category="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"
                                data-penting="<?= $isPenting ?>"
                                data-date="<?= htmlspecialchars($notifDate, ENT_QUOTES) ?>"
                                data-title="<?= htmlspecialchars($titleLow, ENT_QUOTES) ?>"
                                data-message="<?= htmlspecialchars($msgLow, ENT_QUOTES) ?>"
                                data-tag="<?= htmlspecialchars($tagLow, ENT_QUOTES) ?>"
                            >
                                <!-- Icon -->
                                <div class="notif-icon-box <?= $cfg['icon_class'] ?>">
                                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;"><?= htmlspecialchars($icon, ENT_QUOTES) ?></span>
                                </div>

                                <!-- Body -->
                                <div class="notif-body">
                                    <div class="notif-title-row">
                                        <span class="notif-title"><?= htmlspecialchars($notif['title'] ?? '', ENT_QUOTES) ?></span>
                                        <?php if (!empty($notif['is_penting'])): ?>
                                            <span class="badge-penting">PENTING</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="notif-msg"><?= htmlspecialchars($notif['message'] ?? '', ENT_QUOTES) ?></p>
                                    <?php if (!empty($notif['tag'])): ?>
                                        <div class="notif-tag">
                                            <span class="material-symbols-outlined">label</span>
                                            <?= htmlspecialchars($notif['tag'], ENT_QUOTES) ?>
                                        </div><br>
                                    <?php endif; ?>
                                    <span class="notif-time"><?= htmlspecialchars($notif['time'] ?? '', ENT_QUOTES) ?></span>
                                </div>

                                <!-- Action -->
                                <div class="notif-action">
                                    <a href="<?= htmlspecialchars($notif['action_href'] ?? '#', ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($notif['action_label'] ?? 'Lihat', ENT_QUOTES) ?>
                                        <span class="material-symbols-outlined">chevron_right</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>

                <!-- Empty State (JS controlled) -->
                <div id="empty-state" class="notif-empty">
                    <span class="material-symbols-outlined">search_off</span>
                    <h3>Tidak Ada Hasil</h3>
                    <p>Tidak ada notifikasi yang cocok dengan filter saat ini.</p>
                    <button onclick="resetFilters()">Reset Filter</button>
                </div>

            </div>

        <?php endif; ?>
        <!-- ────────────────────────────────────────────────────── -->

    </main>

    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script>
    var activeTab = 'semua';

    function setTab(tab) {
        activeTab = tab;
        // Update pill appearance
        var pills = document.querySelectorAll('.tab-pill-btn');
        pills.forEach(function(el) {
            el.classList.remove('is-active');
        });
        var active = document.querySelector('[data-tab="' + tab + '"]');
        if (active) active.classList.add('is-active');
        applyAllFilters();
    }

    function applyAllFilters() {
        var query      = (document.getElementById('search-input').value || '').toLowerCase().trim();
        var filterDate = document.getElementById('filter-date').value; // YYYY-MM-DD

        var visibleTotal = 0;
        var cards = document.querySelectorAll('.notif-card');

        cards.forEach(function(card) {
            var category = card.getAttribute('data-category') || '';
            var penting  = card.getAttribute('data-penting') === 'true';
            var date     = card.getAttribute('data-date') || '';
            var title    = card.getAttribute('data-title') || '';
            var message  = card.getAttribute('data-message') || '';
            var tag      = card.getAttribute('data-tag') || '';

            // 1. Tab filter
            var tabOk = false;
            if (activeTab === 'semua')        tabOk = true;
            else if (activeTab === 'penting') tabOk = penting;
            else                              tabOk = (category === activeTab);

            // 2. Search filter
            var searchOk = !query || title.indexOf(query) >= 0 || message.indexOf(query) >= 0 || tag.indexOf(query) >= 0;

            // 3. Date filter
            var dateOk = !filterDate || (date === filterDate);

            var show = tabOk && searchOk && dateOk;
            card.style.display = show ? '' : 'none';
            if (show) visibleTotal++;
        });

        // Show/hide group sections
        document.querySelectorAll('.group-section').forEach(function(section) {
            var visible = 0;
            section.querySelectorAll('.notif-card').forEach(function(c) {
                if (c.style.display !== 'none') visible++;
            });
            section.style.display = visible > 0 ? '' : 'none';
        });

        // Empty state
        var emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.style.display = (visibleTotal === 0) ? 'block' : 'none';


    }

    function resetFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-date').value  = '';
        activeTab = 'semua';
        document.querySelectorAll('.tab-pill-btn').forEach(function(el) {
            el.classList.remove('is-active');
        });
        var semua = document.querySelector('[data-tab="semua"]');
        if (semua) semua.classList.add('is-active');
        applyAllFilters();
    }
    </script>

</body>
</html>
