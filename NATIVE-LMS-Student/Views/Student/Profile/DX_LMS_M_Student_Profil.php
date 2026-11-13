<?php
/**
 * Tampilan Profil Siswa DLS (DLS Student Profil View).
 *
 * Halaman antarmuka untuk menampilkan data diri siswa, pengaturan akun,
 * serta opsi untuk berganti peran (role switching) dan keluar aplikasi.
 */
$active_tab = 'profil'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Profile & Role Switching - Learning Management System';
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

<body class="dls-app-shell bg-[#fcfcfc] dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col font-body-md antialiased pt-safe transition-colors duration-200">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = 'Profil Saya';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>
    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 px-4 lg:px-8 py-6 lg:py-8 flex flex-col pb-32 mt-14 lg:ml-[240px] lg:w-[calc(100%-240px)]">

        <div class="w-full max-w-3xl mx-auto flex flex-col gap-5">

            <!-- Top Profile Card -->
            <section class="bg-surface rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <!-- Top Section with Gradient -->
                <div class="relative p-6 lg:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6 border-b border-outline-variant/30">
                    <!-- Subtle background gradient inside card -->
                    <div class="absolute right-0 top-0 w-64 h-64 bg-error-container/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>

                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-surface shadow-sm relative z-10">
                        <?php
                        $photoUrl = $profile['photo_url'] ?? '';
                        $profileName = $profile['name'] ?? 'U';
                        $initials = '';
                        $nameParts = explode(' ', trim($profileName));
                        foreach (array_slice($nameParts, 0, 2) as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                        ?>
                        <?php if (!empty($photoUrl)): ?>
                        <img alt="<?= e($profileName) ?>" class="w-full h-full object-cover" 
                             src="<?= e($photoUrl) ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full bg-gradient-to-br from-primary/80 to-primary flex items-center justify-center" style="display:none;">
                            <span class="text-white font-bold text-2xl"><?= e($initials) ?></span>
                        </div>
                        <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-primary/80 to-primary flex items-center justify-center">
                            <span class="text-white font-bold text-2xl"><?= e($initials) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 flex flex-col justify-center relative z-10 text-center sm:text-left mt-2 sm:mt-0">
                        <h1 class="font-headline-sm text-on-surface font-bold tracking-tight mb-1"><?= e(
                            $profile['name'] ?? 'Budi Santoso',
                        ) ?></h1>
                        <p class="font-body-md text-on-surface-variant mb-4">NPK: <?= e($profile['npk'] ?? '-') ?></p>

                        <div class="flex items-center justify-center sm:justify-start gap-3">
                            <div class="inline-flex items-center gap-1.5 bg-primary-container-light px-3 py-1.5 rounded-full">
                                <span class="font-label-sm text-primary font-bold tracking-wide"><?= e(
                                    $profile['role'] ?? 'Student',
                                ) ?></span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 bg-[#e8f5e9] px-3 py-1.5 rounded-full">
                                <span class="material-symbols-outlined text-[16px] text-success" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                <span class="font-label-sm text-success font-bold tracking-wide">Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section Data -->
                <?php
                $position = $profile['position'] ?? 'Operator';
                $userGolongan = $profile['golongan'] ?? 'Gol 1'; /** Contoh struktur data hierarki. */
                $golList = $golonganMap[$position] ?? [];
                ?>
                <div class="bg-surface-container-lowest/50 p-5 lg:px-8 lg:py-6 flex flex-col sm:flex-row gap-6">

                    <!-- LEFT: Info Departemen & Divisi -->
                    <div class="flex flex-col gap-4 min-w-0 sm:w-1/3">
                        <div>
                            <p class="text-[11px] uppercase font-bold text-on-surface-variant tracking-wider mb-1">DEPARTEMEN</p>
                            <p class="font-body-md text-on-surface font-semibold"><?= e(
                                $profile['department'] ?? '-',
                            ) ?></p>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase font-bold text-on-surface-variant tracking-wider mb-1">DIVISI / SEKSI</p>
                            <p class="font-body-md text-on-surface font-semibold truncate" title="<?= e(
                                $profile['division'] ?? '-',
                            ) ?> / <?= e($profile['section'] ?? '-') ?>">
                                <?= e($profile['division'] ?? '-') ?> / <?= e($profile['section'] ?? '-') ?>
                            </p>
                        </div>
                    </div>

                    <!-- DIVIDER -->
                    <div class="hidden sm:block w-px bg-outline-variant/30 self-stretch"></div>
                    <div class="block sm:hidden h-px bg-outline-variant/30 w-full"></div>

                    <!-- RIGHT: Jabatan + Golongan Hierarchy -->
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] uppercase font-bold text-on-surface-variant tracking-wider mb-3">JABATAN &amp; GOLONGAN</p>
                        <div class="flex items-center gap-2 mb-4 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-1 rounded-full font-label-sm font-bold">
                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">badge</span>
                                <?= e($position) ?>
                            </span>
                            <?php
                            $activeGolLabel = '';
                            $activeGolName = '';
                            foreach ($golList as $g) {
                                if ($g['label'] === $userGolongan) {
                                    $activeGolLabel = $g['label'];
                                    $activeGolName = $g['name'];
                                    break;
                                }
                            }
                            /** Melakukan kalkulasi indeks aktif untuk menentukan tahapan progres yang telah terlampaui. */
                            $activeIdx = 0;
                            foreach ($golList as $ki => $kg) {
                                if ($kg['label'] === $userGolongan) {
                                    $activeIdx = $ki;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($activeGolLabel): ?>
                            <span class="inline-flex items-center gap-1 bg-secondary/10 text-secondary px-3 py-1 rounded-full font-label-sm font-bold">
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">military_tech</span>
                                <?= e($activeGolLabel) ?> — <?= e($activeGolName) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Golongan Step Ladder -->
                        <?php if (!empty($golList)): ?>
                        <div class="relative flex items-start">
                            <!-- Background track line (full width, centered on the dots) -->
                            <div class="absolute left-0 right-0 top-[14px] h-[3px] bg-outline-variant/25 z-0"></div>
                            <!-- Filled track up to active step (blue) -->
                            <?php if ($activeIdx > 0): ?>
                            <div class="absolute left-0 top-[14px] h-[3px] bg-primary/60 z-0" style="width: calc(<?= round(
                                ($activeIdx / (count($golList) - 1)) * 100,
                                2,
                            ) ?>% - 14px)"></div>
                            <?php endif; ?>

                            <?php foreach ($golList as $idx => $gol):

                                $isActive = $idx === $activeIdx;
                                $isPast = $idx < $activeIdx;
                                ?>
                            <!-- Step item -->
                            <div class="flex-1 flex flex-col items-center relative z-10">
                                <!-- Circle: active = solid blue dot, past = solid blue + checkmark, future = outline -->
                                <div class="w-7 h-7 rounded-full flex items-center justify-center transition-all
                                <?= $isActive || $isPast
                                    ? 'bg-primary ring-4 ring-primary/20'
                                    : 'bg-surface border-2 border-outline-variant/40' ?>">
                                    <?php if ($isActive): ?>
                                    <span class="block w-2.5 h-2.5 rounded-full bg-white"></span>
                                    <?php elseif ($isPast): ?>
                                    <span class="material-symbols-outlined text-white" style="font-size:16px;font-variation-settings:'FILL' 1;line-height:1;display:block;">check</span>
                                    <?php endif; ?>
                                </div>
                                <!-- Labels -->
                                <div class="mt-2 text-center w-full px-1">
                                    <p class="text-[12px] font-bold leading-tight
                                    <?= $isActive || $isPast ? 'text-primary' : 'text-on-surface-variant/40' ?>">
                                        <?= e($gol['label']) ?>
                                    </p>
                                    <p class="text-[12px] leading-tight mt-0.5
                                    <?= $isActive
                                        ? 'text-on-surface font-semibold'
                                        : ($isPast
                                            ? 'text-primary/70'
                                            : 'text-on-surface-variant/50') ?>">
                                        <?= e($gol['name']) ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                            endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </section>


            <!-- Ringkasan Pembelajaran (FoodDash Bento Cards) -->
            <section class="flex flex-col gap-[12px]">
                <h2 class="fd-h2 text-slate-800 dark:text-slate-100">Ringkasan Pembelajaran</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-[12px] sm:gap-[18px]">
                    <div class="fd-card bg-white dark:bg-slate-900 rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] dark:shadow-none border border-slate-100 dark:border-slate-800/80 flex items-center gap-[12px] transition-colors">
                        <span class="w-11 h-11 rounded-[10px] bg-[#FEF2F2] dark:bg-red-950/40 text-[#DC2626] dark:text-red-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">menu_book</span>
                        </span>
                        <div class="flex flex-col">
                            <span class="fd-h2 text-slate-900 dark:text-white"><?= (int)($learningStats['active_courses'] ?? $learningStats['active_count'] ?? 0) ?></span>
                            <span class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Course Aktif</span>
                        </div>
                    </div>
                    <div class="fd-card bg-white dark:bg-slate-900 rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] dark:shadow-none border border-slate-100 dark:border-slate-800/80 flex items-center gap-[12px] transition-colors">
                        <span class="w-11 h-11 rounded-[10px] bg-[#DCFCE7] dark:bg-emerald-950/40 text-[#16A34A] dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">check_circle</span>
                        </span>
                        <div class="flex flex-col">
                            <span class="fd-h2 text-slate-900 dark:text-white"><?= (int)($learningStats['completed_courses'] ?? $learningStats['completed_count'] ?? 0) ?></span>
                            <span class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Selesai</span>
                        </div>
                    </div>
                    <div class="fd-card bg-white dark:bg-slate-900 rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] dark:shadow-none border border-slate-100 dark:border-slate-800/80 flex items-center gap-[12px] transition-colors">
                        <span class="w-11 h-11 rounded-[10px] bg-[#DBEAFE] dark:bg-blue-950/40 text-[#2563EB] dark:text-blue-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">bar_chart</span>
                        </span>
                        <div class="flex flex-col">
                            <span class="fd-h2 text-slate-900 dark:text-white"><?= round((float)($learningStats['avg_score'] ?? 0)) ?>%</span>
                            <span class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Skor Rata-rata</span>
                        </div>
                    </div>
                    <div class="fd-card bg-white dark:bg-slate-900 rounded-[12px] p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] dark:shadow-none border border-slate-100 dark:border-slate-800/80 flex items-center gap-[12px] transition-colors">
                        <span class="w-11 h-11 rounded-[10px] bg-[#FFF7ED] dark:bg-orange-950/40 text-[#EA580C] dark:text-orange-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">workspace_premium</span>
                        </span>
                        <div class="flex flex-col">
                            <span class="fd-h2 text-slate-900 dark:text-white"><?= (int)($learningStats['certificates'] ?? $learningStats['total_certificates'] ?? 0) ?></span>
                            <span class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Sertifikat</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pengaturan & Bantuan (FoodDash 64px List) -->
            <section class="flex flex-col gap-[12px]">
                <h2 class="fd-h2 text-slate-800 dark:text-slate-100 mt-[12px]">Pengaturan &amp; Bantuan</h2>

                <div class="fd-list rounded-[12px] bg-white dark:bg-slate-900 shadow-[0_1px_3px_rgba(0,0,0,0.08)] dark:shadow-none border border-slate-100 dark:border-slate-800/80 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">

                    <!-- Profil Saya -->
                    <a href="?route=/student/profil/detail" class="fd-list-row justify-between w-full group hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-center gap-[12px] min-w-0">
                            <div class="fd-list-thumb bg-[#FEF2F2] dark:bg-red-950/40 text-[#DC2626] dark:text-red-400">
                                <span class="material-symbols-outlined text-[22px]">person</span>
                            </div>
                            <div class="flex flex-col text-left min-w-0">
                                <span class="fd-h4 text-slate-900 dark:text-slate-100 group-hover:text-[#DC2626] dark:group-hover:text-red-400 transition-colors">Profil Saya</span>
                                <span class="text-[13px] text-slate-500 dark:text-slate-400 truncate">Informasi pribadi, pekerjaan, dan riwayat karir</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 group-hover:text-[#DC2626] dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all text-[20px]">chevron_right</span>
                    </a>

                    <!-- Akses Peran -->
                    <button type="button" id="btn-ubah-peran" class="fd-list-row justify-between w-full group text-left cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-center gap-[12px] min-w-0">
                            <div class="fd-list-thumb bg-[#FFF7ED] dark:bg-orange-950/40 text-[#EA580C] dark:text-orange-400">
                                <span class="material-symbols-outlined text-[22px]">swap_horiz</span>
                            </div>
                            <div class="flex flex-col text-left min-w-0">
                                <span class="fd-h4 text-slate-900 dark:text-slate-100 group-hover:text-[#DC2626] dark:group-hover:text-red-400 transition-colors">Akses Peran</span>
                                <span class="text-[13px] text-slate-500 dark:text-slate-400 truncate">Perubahan hak akses dikelola Admin LMS</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 group-hover:text-[#DC2626] dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all text-[20px]">chevron_right</span>
                    </button>

                    <!-- Pengaturan -->
                    <a href="?route=/student/profil/pengaturan" class="fd-list-row justify-between w-full group hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-center gap-[12px] min-w-0">
                            <div class="fd-list-thumb bg-[#DBEAFE] dark:bg-blue-950/40 text-[#2563EB] dark:text-blue-400">
                                <span class="material-symbols-outlined text-[22px]">settings</span>
                            </div>
                            <div class="flex flex-col text-left min-w-0">
                                <span class="fd-h4 text-slate-900 dark:text-slate-100 group-hover:text-[#DC2626] dark:group-hover:text-red-400 transition-colors">Pengaturan</span>
                                <span class="text-[13px] text-slate-500 dark:text-slate-400 truncate">Keamanan akun, notifikasi, dan preferensi</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 group-hover:text-[#DC2626] dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all text-[20px]">chevron_right</span>
                    </a>

                    <!-- Tema Tampilan (Mode Gelap / Normal) -->
                    <button type="button" id="btn-toggle-tema-profil" class="fd-list-row justify-between w-full group text-left cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-center gap-[12px] min-w-0">
                            <div class="fd-list-thumb bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400">
                                <span class="material-symbols-outlined text-[22px]" id="profilThemeIcon">dark_mode</span>
                            </div>
                            <div class="flex flex-col text-left min-w-0">
                                <span class="fd-h4 text-slate-900 dark:text-slate-100 group-hover:text-[#DC2626] dark:group-hover:text-red-400 transition-colors">Tema Tampilan</span>
                                <span class="text-[13px] text-slate-500 dark:text-slate-400" id="profilThemeDesc">Beralih antara Mode Gelap dan Normal</span>
                            </div>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200" id="profilThemeBadge">Normal</span>
                    </button>

                    <!-- Bantuan -->
                    <a href="?route=/student/profil/bantuan" class="fd-list-row justify-between w-full group hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-center gap-[12px] min-w-0">
                            <div class="fd-list-thumb bg-[#DCFCE7] dark:bg-emerald-950/40 text-[#16A34A] dark:text-emerald-400">
                                <span class="material-symbols-outlined text-[22px]">help</span>
                            </div>
                            <div class="flex flex-col text-left min-w-0">
                                <span class="fd-h4 text-slate-900 dark:text-slate-100 group-hover:text-[#DC2626] dark:group-hover:text-red-400 transition-colors">Bantuan</span>
                                <span class="text-[13px] text-slate-500 dark:text-slate-400 truncate">FAQ, panduan penggunaan, dan hubungi Admin</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 group-hover:text-[#DC2626] dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all text-[20px]">chevron_right</span>
                    </a>

                    <!-- Keluar -->
                    <button type="button" id="btn-keluar" class="fd-list-row justify-between w-full group text-left cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <div class="flex items-center gap-[12px] min-w-0">
                            <div class="fd-list-thumb bg-[#FEE2E2] dark:bg-red-950/40 text-[#DC2626] dark:text-red-400">
                                <span class="material-symbols-outlined text-[22px]">logout</span>
                            </div>
                            <div class="flex flex-col text-left min-w-0">
                                <span class="fd-h4 text-slate-900 dark:text-slate-100 group-hover:text-[#DC2626] dark:group-hover:text-red-400 transition-colors">Keluar</span>
                                <span class="text-[13px] text-slate-500 dark:text-slate-400 truncate">Akhiri sesi Anda di perangkat ini</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 group-hover:text-[#DC2626] dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all text-[20px]">chevron_right</span>
                    </button>

                </div>
            </section>

        </div>
    </main>
    <!-- BottomNavBar Shared Component -->
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
    <form id="logoutForm" class="hidden" method="POST" action="?route=/auth/logout">
        <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
    </form>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /** Menghilangkan penanda titik peringatan notifikasi. */
            if (localStorage.getItem('hasUnreadNotifications') === 'false') {
                document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
            }

            /** ── Modul Konfirmasi: Pembaruan Otoritas Peran ──────────────────────────────────────────── */
            // Pengatur tema tampilan (Gelap / Normal)
            function updateProfilThemeUI(theme) {
                var current = theme || (window.DLS_Theme ? window.DLS_Theme.get() : (document.documentElement.classList.contains('dark') ? 'dark' : 'light'));
                var isDark = current === 'dark';
                var badge = document.getElementById('profilThemeBadge');
                var icon = document.getElementById('profilThemeIcon');
                var desc = document.getElementById('profilThemeDesc');
                if (badge) badge.textContent = isDark ? 'Mode Gelap' : 'Mode Normal';
                if (icon) icon.textContent = isDark ? 'light_mode' : 'dark_mode';
                if (desc) desc.textContent = isDark ? 'Tema gelap aktif untuk kenyamanan visual malam' : 'Beralih antara Mode Gelap dan Normal';
            }
            updateProfilThemeUI();

            document.getElementById('btn-toggle-tema-profil')?.addEventListener('click', function() {
                if (window.DLS_Theme) {
                    var next = window.DLS_Theme.toggle();
                    updateProfilThemeUI(next);
                    if (window.DLS && window.DLS.toast) {
                        window.DLS.toast('Mode ' + (next === 'dark' ? 'Gelap' : 'Normal') + ' diaktifkan', 'info');
                    }
                }
            });

            document.getElementById('btn-ubah-peran')?.addEventListener('click', function() {
                if (window.DLS && window.DLS.info) {
                    window.DLS.info('Akses dikelola Admin LMS', 'Hubungi Admin LMS jika Anda memerlukan penyesuaian hak akses.');
                } else {
                    Swal.fire({ title: 'Akses dikelola Admin LMS', text: 'Hubungi Admin LMS jika Anda memerlukan perubahan hak akses.', icon: 'info', confirmButtonText: 'Mengerti', confirmButtonColor: '#174a70' });
                }
            });
            /** ── Modul Konfirmasi: Mengakhiri Sesi (Logout) ──────────────────────────────────────────── */
            document.getElementById('btn-keluar')?.addEventListener('click', function() {
                if (typeof window.confirmStudentLogout === 'function') {
                    window.confirmStudentLogout();
                    return;
                }
                if (window.DLS && window.DLS.confirm) {
                    window.DLS.confirm({
                        title: 'Keluar dari Akun?',
                        text: 'Sesi Anda di perangkat ini akan diakhiri. Anda perlu login kembali untuk mengakses sistem.',
                        confirmText: 'Ya, Keluar',
                        cancelText: 'Batal',
                        danger: true
                    }).then(function(confirmed) {
                        if (confirmed) document.getElementById('logoutForm')?.requestSubmit();
                    });
                }
            });
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

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
