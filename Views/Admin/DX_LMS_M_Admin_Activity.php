<?php
/**
 * Tampilan Activity Hub LMS (Operational Control Log).
 * Dilengkapi: filter role, filter tanggal, pencarian keyword, statistik agregat, export CSV.
 */

$header_title = 'Activity Center';
$active_tab = 'activity';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Rekam Aktivitas Sistem';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }

        /* Filter Tab Active */
        .role-tab.active {
            background-color: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }
        .role-tab {
            transition: all 0.15s ease;
        }
        /* Log item hover */
        .log-item { transition: background-color 0.12s ease; }
        /* Search highlight */
        mark { background-color: #fef08a; border-radius: 2px; padding: 0 2px; }
        /* Skeleton shimmer */
        @keyframes shimmer { 0%{background-position:-400px 0} 100%{background-position:400px 0} }
        .skeleton { background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%); background-size:400px 100%; animation:shimmer 1.2s infinite; border-radius:4px; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[88px] lg:ml-[240px]">

        <!-- ================================================
             PAGE HEADER
             ================================================ -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="font-headline-lg font-bold text-gray-900">Activity Hub LMS</h1>
                    <p class="text-sm text-gray-500 mt-1">Rekaman operasional seluruh interaksi pengguna lintas role secara kronologis.</p>
                </div>
                <!-- Export CSV Button -->
                <button onclick="exportToCSV()" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:border-gray-400 hover:bg-gray-50 px-4 py-2.5 rounded-lg transition-colors shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>Export CSV</span>
                </button>
            </div>
        </div>

        <!-- ================================================
             STATISTIK SUMMARY (4 KPI Card)
             ================================================ -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <a href="?route=/admin/activity&role=all<?= !empty($filter_date) ? '&date=' . e($filter_date) : '' ?>" 
               class="bg-white border <?= $filter_role === 'all' ? 'border-red-300 ring-2 ring-red-100' : 'border-gray-200 hover:border-gray-300' ?> rounded-xl p-4 text-center transition-all group shadow-sm">
                <div class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></div>
                <div class="text-xs text-gray-500 mt-0.5 font-medium">Semua Aktivitas</div>
                <div class="flex items-center justify-center gap-1 mt-2">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    <span class="text-[11px] text-gray-400">Seluruh Role</span>
                </div>
            </a>
            <a href="?route=/admin/activity&role=admin<?= !empty($filter_date) ? '&date=' . e($filter_date) : '' ?>" 
               class="bg-white border <?= $filter_role === 'admin' ? 'border-red-300 ring-2 ring-red-100' : 'border-gray-200 hover:border-gray-300' ?> rounded-xl p-4 text-center transition-all group shadow-sm">
                <div class="text-2xl font-bold text-red-600"><?= $stats['admin'] ?></div>
                <div class="text-xs text-gray-500 mt-0.5 font-medium">Admin</div>
                <div class="flex items-center justify-center gap-1 mt-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="text-[11px] text-gray-400">Operasi Sistem</span>
                </div>
            </a>
            <a href="?route=/admin/activity&role=teacher<?= !empty($filter_date) ? '&date=' . e($filter_date) : '' ?>" 
               class="bg-white border <?= $filter_role === 'teacher' ? 'border-blue-300 ring-2 ring-blue-50' : 'border-gray-200 hover:border-gray-300' ?> rounded-xl p-4 text-center transition-all group shadow-sm">
                <div class="text-2xl font-bold text-blue-600"><?= $stats['teacher'] ?></div>
                <div class="text-xs text-gray-500 mt-0.5 font-medium">Teacher</div>
                <div class="flex items-center justify-center gap-1 mt-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-[11px] text-gray-400">Kelola Materi</span>
                </div>
            </a>
            <a href="?route=/admin/activity&role=student<?= !empty($filter_date) ? '&date=' . e($filter_date) : '' ?>" 
               class="bg-white border <?= $filter_role === 'student' ? 'border-green-300 ring-2 ring-green-50' : 'border-gray-200 hover:border-gray-300' ?> rounded-xl p-4 text-center transition-all group shadow-sm">
                <div class="text-2xl font-bold text-green-600"><?= $stats['student'] ?></div>
                <div class="text-xs text-gray-500 mt-0.5 font-medium">Student</div>
                <div class="flex items-center justify-center gap-1 mt-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-[11px] text-gray-400">Aktivitas Belajar</span>
                </div>
            </a>
        </div>

        <!-- ================================================
             TOOLBAR: SEARCH + FILTER ROLE TABS + DATE
             ================================================ -->
        <div class="mb-5">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <!-- Baris 1: Search + Date -->
                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <!-- Search -->
                    <form method="GET" action="index.php" class="flex gap-2 flex-1">
                        <input type="hidden" name="route" value="/admin/activity">
                        <input type="hidden" name="role" value="<?= e($filter_role) ?>">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 text-[18px]">search</span>
                            <input 
                                type="text" 
                                name="q" 
                                id="searchInput"
                                value="<?= e($filter_keyword) ?>"
                                placeholder="Cari nama pengguna, aksi, atau objek..."
                                class="w-full pl-10 pr-4 py-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400 transition-colors"
                            >
                        </div>
                        <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shrink-0">Cari</button>
                    </form>
                    <!-- Date Picker -->
                    <form method="GET" action="index.php" class="flex gap-2">
                        <input type="hidden" name="route" value="/admin/activity">
                        <input type="hidden" name="role" value="<?= e($filter_role) ?>">
                        <input type="hidden" name="q" value="<?= e($filter_keyword) ?>">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 text-[18px]">calendar_today</span>
                            <input 
                                type="date" 
                                name="date"
                                value="<?= e($filter_date) ?>"
                                max="<?= date('Y-m-d') ?>"
                                class="pl-10 pr-4 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400 transition-colors"
                                onchange="this.form.submit()"
                            >
                        </div>
                        <?php if (!empty($filter_date)): ?>
                            <a href="?route=/admin/activity&role=<?= e($filter_role) ?>&q=<?= urlencode($filter_keyword) ?>" 
                               class="px-3 py-2.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors flex items-center"
                               title="Hapus filter tanggal">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Baris 2: Role Filter Tabs -->
                <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter berdasarkan Role">
                    <?php
                    $role_tabs = [
                        'all'     => ['label' => 'Semua Role',  'count' => $stats['total'],   'icon' => 'group'],
                        'admin'   => ['label' => 'Admin',       'count' => $stats['admin'],   'icon' => 'admin_panel_settings'],
                        'teacher' => ['label' => 'Teacher',     'count' => $stats['teacher'], 'icon' => 'school'],
                        'student' => ['label' => 'Student',     'count' => $stats['student'], 'icon' => 'person'],
                    ];
                    // Warna aksen per role: semua/admin=merah, teacher=biru, student=hijau
                    $tab_active_class = [
                        'all'     => 'bg-red-600   text-white border-red-600   shadow-sm',
                        'admin'   => 'bg-red-600   text-white border-red-600   shadow-sm',
                        'teacher' => 'bg-blue-600  text-white border-blue-600  shadow-sm',
                        'student' => 'bg-green-600 text-white border-green-600 shadow-sm',
                    ];
                    $tab_badge_active_class = [
                        'all'     => 'bg-red-500   text-white',
                        'admin'   => 'bg-red-500   text-white',
                        'teacher' => 'bg-blue-500  text-white',
                        'student' => 'bg-green-500 text-white',
                    ];
                    foreach ($role_tabs as $role_key => $tab):
                        $is_active = $filter_role === $role_key;
                        $base_url = '?route=/admin/activity&role=' . $role_key;
                        if (!empty($filter_date))    $base_url .= '&date=' . urlencode($filter_date);
                        if (!empty($filter_keyword)) $base_url .= '&q=' . urlencode($filter_keyword);
                        $active_cls = $tab_active_class[$role_key] ?? 'bg-red-600 text-white border-red-600 shadow-sm';
                        $badge_cls  = $tab_badge_active_class[$role_key] ?? 'bg-red-500 text-white';
                    ?>
                        <a href="<?= $base_url ?>" 
                           role="tab" 
                           aria-selected="<?= $is_active ? 'true' : 'false' ?>"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium border transition-all
                               <?= $is_active ? $active_cls : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50' ?>">
                            <span class="material-symbols-outlined text-[16px]"><?= $tab['icon'] ?></span>
                            <span><?= $tab['label'] ?></span>
                            <span class="<?= $is_active ? $badge_cls : 'bg-gray-100 text-gray-500' ?> text-[11px] font-bold px-1.5 py-0.5 rounded-full min-w-[22px] text-center">
                                <?= $tab['count'] ?>
                            </span>
                        </a>
                    <?php endforeach; ?>

                    <!-- Separator + Clear All Filters -->
                    <?php if ($filter_role !== 'all' || !empty($filter_date) || !empty($filter_keyword)): ?>
                        <span class="w-px h-8 bg-gray-200 mx-1 self-center"></span>
                        <a href="?route=/admin/activity" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-500 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors border border-transparent hover:border-red-200">
                            <span class="material-symbols-outlined text-[16px]">filter_alt_off</span>
                            <span>Reset Filter</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Active Filter Summary Badge -->
                <?php 
                $active_filters = [];
                if ($filter_role !== 'all') $active_filters[] = 'Role: ' . ucfirst($filter_role);
                if (!empty($filter_date)) $active_filters[] = 'Tanggal: ' . date('d M Y', strtotime($filter_date));
                if (!empty($filter_keyword)) $active_filters[] = 'Kata kunci: "' . e($filter_keyword) . '"';
                ?>
                <?php if (!empty($active_filters)): ?>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-2 items-center">
                        <span class="text-xs text-gray-400 font-medium">Filter aktif:</span>
                        <?php foreach ($active_filters as $f): ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                <?= $f ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ================================================
             ACTIVITY TIMELINE LIST
             ================================================ -->
        <div class="w-full">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- Header List -->
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-400 text-[20px]">format_list_bulleted</span>
                        <span class="text-sm font-semibold text-gray-700">
                            <?php
                            $count = count($activities);
                            if ($count === 0) echo 'Tidak ada hasil';
                            elseif ($count === 1) echo '1 aktivitas ditemukan';
                            else echo $count . ' aktivitas ditemukan';
                            ?>
                        </span>
                        <?php if (!empty($filter_date)): ?>
                            <span class="text-xs text-gray-400">· <?= date('d M Y', strtotime($filter_date)) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-400">Diurutkan: Terbaru</span>
                </div>

                <?php if (count($activities) === 0): ?>
                    <!-- Empty State -->
                    <div class="py-16 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-[64px]">inbox</span>
                        <p class="mt-3 text-sm font-semibold text-gray-500">Tidak ada aktivitas ditemukan</p>
                        <p class="mt-1 text-xs text-gray-400">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        <a href="?route=/admin/activity" class="inline-flex items-center gap-2 mt-4 text-sm font-semibold text-red-600">
                            <span class="material-symbols-outlined text-[16px]">refresh</span> Reset semua filter
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Timeline Items -->
                    <div id="activityList" class="px-6 py-2">
                        <?php 
                        $total_acts = count($activities);
                        $prev_date = null;
                        foreach ($activities as $idx => $act):
                            // Date group separator
                            $act_date = $act['date'] ?? date('Y-m-d');
                            $today = date('Y-m-d');
                            $yesterday = date('Y-m-d', strtotime('-1 day'));
                            if ($act_date !== $prev_date):
                                $prev_date = $act_date;
                                $date_label = match($act_date) {
                                    $today     => 'Hari Ini · ' . date('d M Y'),
                                    $yesterday => 'Kemarin · ' . date('d M Y', strtotime('-1 day')),
                                    default    => date('l, d M Y', strtotime($act_date)),
                                };
                        ?>
                            <div class="flex items-center gap-3 py-3 <?= $idx > 0 ? 'mt-2' : '' ?>">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap"><?= $date_label ?></span>
                                <span class="flex-1 h-px bg-gray-100"></span>
                            </div>
                        <?php
                            endif;
                            // Role color mapping
                            $role_lower = strtolower($act['role']);
                            $role_colors = [
                                'admin'   => 'bg-red-50 text-red-700 border-red-200',
                                'teacher' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'student' => 'bg-green-50 text-green-700 border-green-200',
                            ];
                            $role_dot_color = [
                                'admin'   => 'bg-red-400',
                                'teacher' => 'bg-blue-400',
                                'student' => 'bg-green-400',
                            ];
                            $role_badge_class = $role_colors[$role_lower] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                            $role_dot_class   = $role_dot_color[$role_lower] ?? 'bg-gray-400';
                            $is_last = ($idx === $total_acts - 1);

                            // Check if next item is a new date group (no line after last in group)
                            $next_act_date = isset($activities[$idx + 1]['date']) ? $activities[$idx + 1]['date'] : null;
                            $show_line = !$is_last && ($next_act_date === $act_date);
                        ?>
                            <!-- Log item: flex layout agar icon tidak menimpa teks -->
                            <div class="log-item flex gap-3 py-3.5 border-b border-gray-100 last:border-0 relative group hover:bg-gray-50 -mx-6 px-6 transition-colors" data-role="<?= strtolower($act['role']) ?>">

                                <!-- Icon Node (flex item, bukan absolute) -->
                                <div class="w-9 h-9 rounded-full shrink-0 bg-white border border-gray-200 text-gray-400 flex items-center justify-center shadow-sm z-10 group-hover:border-gray-300 group-hover:text-gray-600 transition-colors mt-0.5">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 0;"><?= e($act['icon']) ?></span>
                                </div>

                                <!-- Content (flex-1, tidak tertimpa icon) -->
                                <div class="flex-1 min-w-0 flex justify-between items-start gap-4">
                                    <div class="text-sm text-gray-600 flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0 leading-relaxed">
                                        <!-- User Name -->
                                        <a href="?route=/admin/user/detail&id=<?= $act['user_id'] ?? 1 ?>" 
                                           class="font-semibold text-gray-900 hover:text-red-600 transition-colors whitespace-nowrap"><?= e($act['user']) ?></a>
                                        <!-- Role Badge (warna per role) -->
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border <?= $role_badge_class ?> whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $role_dot_class ?>"></span>
                                            <?= e($act['role']) ?>
                                        </span>
                                        <!-- Action -->
                                        <span><?= e($act['action']) ?></span>
                                        <!-- Target Entity -->
                                        <?php if (isset($act['target_type']) && in_array($act['target_type'], ['course','quiz','material'])): ?>
                                            <a href="?route=/admin/<?= $act['target_type'] ?>/detail&id=<?= $act['target_id'] ?? 1 ?>" 
                                               class="font-semibold text-gray-900 hover:text-red-600 transition-colors"><?= e($act['target']) ?></a>
                                        <?php else: ?>
                                            <strong class="font-semibold text-gray-900"><?= e($act['target']) ?></strong>
                                        <?php endif; ?>
                                        <!-- Score badge (jika ada) -->
                                        <?php if (!empty($act['score'])): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                                <span class="material-symbols-outlined text-[12px]">grade</span>
                                                <?= e($act['score']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Timestamp -->
                                    <span class="font-mono text-xs text-gray-400 whitespace-nowrap pt-0.5 shrink-0"><?= e($act['time']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Load More Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-gray-400">Menampilkan <?= count($activities) ?> dari <?= $stats['total'] ?> total aktivitas</span>
                        <button class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-red-600 border border-gray-200 hover:border-red-200 bg-white hover:bg-red-50 px-4 py-2 rounded-lg transition-colors">
                            <span>Muat Lebih Banyak</span>
                            <span class="material-symbols-outlined text-[18px]">expand_more</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script>
    // =====================================================
    // Export to CSV
    // =====================================================
    function exportToCSV() {
        const rows = [
            ['Nama Pengguna', 'Role', 'Aksi', 'Objek', 'Waktu', 'Tanggal']
        ];

        <?php 
        // Generate JS array from PHP activities
        $all_for_export = [];
        foreach ($activities as $a) {
            $all_for_export[] = [
                addslashes($a['user']),
                addslashes($a['role']),
                addslashes($a['action']),
                addslashes($a['target']),
                addslashes($a['time']),
                addslashes($a['date'] ?? ''),
            ];
        }
        $js_array = json_encode($all_for_export, JSON_UNESCAPED_UNICODE);
        ?>
        const data = <?= $js_array ?>;
        data.forEach(row => rows.push(row));

        const csvContent = rows.map(r => r.map(v => '"' + v.replace(/"/g, '""') + '"').join(',')).join('\n');
        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'activity_log_<?= date('Ymd') ?>.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // =====================================================
    // Quick Date Shortcuts (Hari ini / Minggu ini)
    // =====================================================
    function setQuickDate(offset) {
        const d = new Date();
        d.setDate(d.getDate() + offset);
        const dateStr = d.toISOString().split('T')[0];
        const url = new URL(window.location.href);
        url.searchParams.set('date', dateStr);
        window.location.href = url.toString();
    }
    </script>

</body>
</html>
