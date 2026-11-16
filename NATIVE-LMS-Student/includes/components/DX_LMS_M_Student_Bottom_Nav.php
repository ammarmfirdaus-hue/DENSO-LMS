<?php
/**
 * Komponen Navigasi Bawah (Bottom Navigation Bar) â€” DLS.
 *
 * Menyajikan bilah navigasi bawah dengan 5 tab utama yang sesuai
 * dengan desain referensi Student: Beranda, Course, Riwayat, Notifikasi, Profil.
 *
 * Variabel yang Diterima:
 *   $active_tab  - string : Nama tab aktif ('beranda'|'course'|'riwayat'|'notifikasi'|'profil').
 *   $base_path   - string : Jalur relatif menuju direktori root (misal: '' atau '../').
 */

$active_tab = $active_tab ?? 'beranda';
$base_path = $base_path ?? '';
$route_prefix = '/student';
$student_name = $_SESSION['student_name'] ?? ($_SESSION['user']['name'] ?? 'Siswa DENSO');
$student_npk = $_SESSION['student_npk'] ?? ($_SESSION['user']['npk'] ?? 'Student');

$tabs = [
    [
        'id' => 'beranda',
        'icon' => 'home',
        'label' => 'Beranda',
        'href' => '?route=' . $route_prefix,
    ],
    [
        'id' => 'course',
        'icon' => 'school',
        'label' => 'Course',
        'href' => '?route=' . $route_prefix . '/courses',
    ],
    [
        'id' => 'nilai',
        'icon' => 'grade',
        'label' => 'Nilai',
        'href' => '?route=' . $route_prefix . '/nilai',
    ],
    [
        'id' => 'riwayat',
        'icon' => 'history',
        'label' => 'Riwayat',
        'href' => '?route=' . $route_prefix . '/riwayat',
    ],
    [
        'id' => 'notifikasi',
        'icon' => 'notifications',
        'label' => 'Notifikasi',
        'href' => '?route=' . $route_prefix . '/notifikasi',
    ],
    [
        'id' => 'profil',
        'icon' => 'person',
        'label' => 'Profil',
        'href' => '?route=' . $route_prefix . '/profil',
    ],
];

$hari_list = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan_list = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$current_day_name = $hari_list[(int)date('w')];
$current_day_num  = date('j');
$current_month_name = $bulan_list[(int)date('n')];
$current_year     = date('Y');
$current_date_str = "{$current_day_name}, {$current_day_num} {$current_month_name} {$current_year}";
?>
<form id="studentNavLogoutForm" class="hidden" method="POST" action="?route=/auth/logout" aria-hidden="true">
    <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
</form>
<nav class="fixed bottom-0 left-0 w-full z-50 flex items-stretch px-0 h-[76px] shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] dark:shadow-none bg-surface dark:bg-on-background rounded-t-xl lg:h-screen lg:flex-col lg:justify-start lg:top-0 lg:left-0 lg:rounded-none border-t border-outline-variant dark:border-outline lg:border-t-0 lg:p-5 lg:gap-1.5 transition-all duration-300" id="bottomNavBar">

    <!-- Brand untuk sidebar desktop -->
    <a href="?route=/student" class="dls-nav-brand hidden lg:flex items-center gap-3 px-2 pb-6 pt-1 text-decoration-none" id="dlsNavBrand">
        <img src="<?= htmlspecialchars($base_path . 'assets/img/icon/denso-vector-logo.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo DENSO" class="w-9 h-9 object-contain shrink-0 dark:hidden">
        <img src="<?= htmlspecialchars($base_path . 'assets/img/icon/denso-vector-logo-dark.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo DENSO" class="w-9 h-9 object-contain shrink-0 hidden dark:block">
        <span class="flex flex-col leading-tight">
            <strong class="text-[15px] font-bold tracking-tight text-[#181824] dark:text-white" style="font-family:'Poppins',sans-serif;">DENSO</strong>
            <small class="text-[11px] font-bold tracking-[0.08em] text-[#DC2626] uppercase">Learning</small>
        </span>
    </a>

    <?php foreach ($tabs as $tab): ?>
    <?php
    $is_active = $active_tab === $tab['id'];
    $is_mobile_profile_active = $tab['id'] === 'profil' && $active_tab === 'profil';
    $active_class = $is_active
        ? 'is-active text-primary dark:text-primary-fixed bg-primary-container-light lg:bg-transparent font-semibold'
        : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-variant';
    ?>
    <?php if ($tab['id'] === 'profil'): ?>
    <button type="button"
       class="dls-mobile-profile-trigger flex-1 flex flex-col items-center justify-center rounded-xl py-2 transition-transform duration-200 active:scale-95 relative <?= $is_mobile_profile_active ? 'text-primary bg-primary-container-light font-semibold' : 'text-on-surface-variant' ?>"
       id="nav-profil"
       onclick="window.toggleStudentMobileProfileMenu()"
       title="Profil"
       aria-label="Menu Profil"
       aria-haspopup="true"
       aria-expanded="false"
       aria-controls="studentMobileProfileDropup">
        <span class="material-symbols-outlined mb-1" style="font-variation-settings: 'FILL' <?= $is_mobile_profile_active ? '1' : '0' ?>;">person</span>
        <span class="dls-nav-label font-label-sm text-label-sm">Profil</span>
    </button>
    <a href="<?= $tab['href'] ?>"
       class="dls-desktop-profile-link dls-nav-item flex-1 lg:flex-none hidden lg:flex flex-col lg:flex-row items-center lg:justify-start justify-center rounded-xl py-2 lg:w-full transition-transform duration-200 active:scale-95 relative <?= $active_class ?>"
       id="nav-profil-desktop"
       title="Profil"
       aria-label="Profil"<?= $is_active ? ' aria-current="page"' : '' ?>>
        <span class="material-symbols-outlined text-[20px] mb-1 lg:mb-0" style="font-variation-settings: 'FILL' <?= $is_active ? '1' : '0' ?>;">person</span>
        <span class="dls-nav-label font-label-sm text-label-sm lg:text-[13.5px]">Profil</span>
    </a>
    <?php else: ?>
    <a href="<?= $tab['href'] ?>" 
       class="<?= $tab['id'] === 'notifikasi' ? 'dls-mobile-nav-notifications ' : '' ?>dls-nav-item flex-1 lg:flex-none flex flex-col lg:flex-row items-center lg:justify-start justify-center rounded-xl py-2 lg:w-full transition-transform duration-200 active:scale-95 relative <?= $active_class ?>"
       id="nav-<?= $tab['id'] ?>"
       title="<?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8') ?>"
       aria-label="<?= $tab['label'] ?>"<?= $is_active ? ' aria-current="page"' : '' ?>>
        
        <span class="material-symbols-outlined text-[20px] mb-1 lg:mb-0"
              style="font-variation-settings: 'FILL' <?= $is_active ? '1' : '0' ?>;"
        ><?= $tab['icon'] ?></span>
        
        <span class="dls-nav-label font-label-sm text-label-sm lg:text-[13.5px]"><?= $tab['label'] ?></span>

        <?php if ($tab['id'] === 'notifikasi'): ?>
        <span data-notification-count class="dls-sidebar-notif-pill" aria-hidden="true"<?= ((int) ($notif_count ?? 0) > 0 && $active_tab !== 'notifikasi') ? '' : ' hidden' ?>><?= (int) ($notif_count ?? 0) > 99 ? '99+' : (int) ($notif_count ?? 0) ?></span>
        <?php endif; ?>
    </a>
    <?php endif; ?>
    <?php endforeach; ?>

    <!-- Mobile profile menu opens upward from the bottom navigation. -->
    <div id="studentMobileProfileDropup" class="dls-mobile-profile-menu hidden absolute bottom-[calc(100%+10px)] right-3 z-[60] w-[min(280px,calc(100vw-24px))] flex-col gap-1 rounded-2xl border border-outline-variant bg-white p-2 shadow-xl dark:bg-slate-900" role="menu" aria-label="Menu Profil">
        <div class="mb-1 rounded-xl bg-surface-container-low px-3 py-2.5">
            <p class="truncate text-sm font-bold text-on-surface"><?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-0.5 truncate text-xs text-on-surface-variant">NPK <?= htmlspecialchars($student_npk, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a href="?route=/student/notifikasi" class="dls-mobile-profile-menu-item flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container-low" role="menuitem"<?= $active_tab === 'notifikasi' ? ' aria-current="page"' : '' ?>>
            <span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">notifications</span>
            <span class="flex-1">Notifikasi</span>
            <?php if ((int) ($notif_count ?? 0) > 0 && $active_tab !== 'notifikasi'): ?><span class="dls-mobile-profile-unread"><?= (int) $notif_count > 99 ? '99+' : (int) $notif_count ?></span><?php endif; ?>
        </a>
        <a href="?route=/student/profil" class="dls-mobile-profile-menu-item flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container-low" role="menuitem"<?= $active_tab === 'profil' ? ' aria-current="page"' : '' ?>>
            <span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">person</span>
            <span>Profil Saya</span>
        </a>
    </div>

    <!-- Desktop Bottom Help Center Card & Account Controls -->
    <div class="hidden lg:flex flex-col gap-3 mt-auto w-full pt-2">
        <!-- Help Center Card (Reference Image Style with DENSO Red Badge) -->
        <div class="dls-help-card">
            <div class="dls-help-card-badge" aria-hidden="true">
                <span class="material-symbols-outlined text-[20px]">help</span>
            </div>
            <p class="text-[14px] font-bold text-white" style="font-family:'Poppins',sans-serif;">Pusat Bantuan</p>
            <p class="mt-1 text-[11px] leading-relaxed text-slate-300">Ada kendala belajar? Hubungi tim kami!</p>
            <a href="?route=/student/profil/bantuan" class="dls-help-card-btn">Buka Bantuan</a>
        </div>

        <!-- Compact Theme & Logout Row -->
        <div class="flex items-center justify-between gap-2 pt-1">
            <button type="button" id="btnStudentThemeToggle" onclick="window.toggleStudentTheme()" class="flex items-center gap-1.5 rounded-xl px-2.5 py-2 text-[11.5px] font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 transition cursor-pointer" title="Mode Tampilan">
                <span class="material-symbols-outlined text-[17px]" id="themeIcon">dark_mode</span>
                <span id="themeStatusBadge">Normal</span>
            </button>
            <button type="button" onclick="window.confirmStudentLogout()" class="flex items-center gap-1.5 rounded-xl px-2.5 py-2 text-[11.5px] font-bold text-[#DC2626] hover:bg-[#FEF2F2] dark:hover:bg-red-950/40 transition cursor-pointer" title="Keluar Akun">
                <span class="material-symbols-outlined text-[17px]">logout</span>
                <span>Keluar</span>
            </button>
        </div>
    </div>
</nav>
<?php if (empty($hide_right_panel)): ?>
<?php include APP_ROOT . '/includes/components/DX_LMS_M_Right_Panel.php'; ?>
<script>document.body.classList.add('dls-has-right-panel');</script>
<?php endif; ?>
<?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Footer.php'; ?>
<?php include APP_ROOT . '/includes/components/DX_LMS_M_Support_Chat_Widget.php'; ?>

<script>
/* ── Student Sidebar Mini-Rail Toggle ── */
(function() {
    var STORAGE_KEY = 'dls_student_sidebar_collapsed';
    var nav = document.getElementById('bottomNavBar');
    var isDesktop = function() { return window.innerWidth >= 1024; };
    var isCollapsed = false;

    function applyState(collapsed, animate) {
        if (!nav || !isDesktop()) return;
        if (!animate) nav.style.transition = 'none';
        if (collapsed) {
            nav.classList.add('dls-sidebar-collapsed');
        } else {
            nav.classList.remove('dls-sidebar-collapsed');
        }
        if (!animate) { nav.offsetHeight; nav.style.transition = ''; } // force reflow
        // Update main area padding
        var mains = document.querySelectorAll('.dls-main, .dls-main-sub');
        mains.forEach(function(m) {
            m.classList.toggle('dls-main-sidebar-mini', collapsed);
        });
        var progressTrack = document.getElementById('learningProgressBar');
        if (progressTrack) progressTrack.classList.toggle('dls-progress-sidebar-mini', collapsed);
        // Update top app bar left offset
        var header = document.getElementById('topAppBar');
        if (header) header.classList.toggle('dls-header-sidebar-mini', collapsed);
        var toggle = document.getElementById('dlsDesktopSidebarToggle');
        if (toggle) {
            var label = collapsed ? 'Tampilkan sidebar' : 'Kecilkan sidebar';
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', label);
            var icon = toggle.querySelector('.material-symbols-outlined');
            if (icon) icon.textContent = collapsed ? 'menu_open' : 'menu';
        }
    }

    function restoreState() {
        try { isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true'; } catch(e) {}
        applyState(isCollapsed, false);
    }

    window.toggleStudentSidebar = function() {
        isCollapsed = !isCollapsed;
        try { localStorage.setItem(STORAGE_KEY, isCollapsed ? 'true' : 'false'); } catch(e) {}
        applyState(isCollapsed, true);
    };

    // Hover expand when collapsed
    if (nav) {
        nav.addEventListener('mouseenter', function() {
            if (isCollapsed && isDesktop()) nav.classList.add('dls-sidebar-hover-expand');
        });
        nav.addEventListener('mouseleave', function() {
            nav.classList.remove('dls-sidebar-hover-expand');
        });
    }

    document.addEventListener('DOMContentLoaded', restoreState);
    // Also restore if DOMContentLoaded already fired
    if (document.readyState !== 'loading') restoreState();
})();

/* ── Student Menu Search Filtering ── */
window.filterStudentMenu = function(query) {
    query = (query || '').toLowerCase().trim();
    var clearBtn = document.getElementById('btnStudentClearSearch');
    if (clearBtn) {
        clearBtn.classList.toggle('hidden', !query);
    }
    var nav = document.getElementById('bottomNavBar');
    if (!nav) return;
    
    var linkIds = ['nav-beranda', 'nav-course', 'nav-nilai', 'nav-riwayat', 'nav-notifikasi', 'nav-profil-desktop'];
    var matchedCount = 0;
    
    linkIds.forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var text = (el.textContent || '').replace(/[\r\n\t]+/g, ' ').toLowerCase();
        var isMatch = !query || text.indexOf(query) !== -1;
        if (isMatch) {
            el.style.display = '';
            matchedCount++;
        } else {
            el.style.display = 'none';
        }
    });

    var emptyMsg = document.getElementById('studentMenuNoResult');
    if (emptyMsg) {
        emptyMsg.classList.toggle('hidden', !query || matchedCount > 0);
    }
};

window.clearStudentMenuSearch = function() {
    var input = document.getElementById('studentMenuSearch');
    if (input) {
        input.value = '';
        window.filterStudentMenu('');
        input.focus();
    }
};

window.toggleStudentProfileDropup = function() {
    var menu = document.getElementById('studentProfileDropup');
    var chevron = document.getElementById('profileDropupChevron');
    if (!menu) return;
    var isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        menu.classList.remove('hidden');
        menu.classList.add('flex');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        menu.classList.remove('flex');
        if (chevron) chevron.classList.remove('rotate-180');
    }
};

window.toggleStudentMobileProfileMenu = function(forceOpen) {
    var menu = document.getElementById('studentMobileProfileDropup');
    var trigger = document.getElementById('nav-profil');
    if (!menu || !trigger) return;
    var shouldOpen = typeof forceOpen === 'boolean'
        ? forceOpen
        : menu.classList.contains('hidden');
    menu.classList.toggle('hidden', !shouldOpen);
    menu.classList.toggle('flex', shouldOpen);
    trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
};

window.toggleStudentTheme = function() {
    if (window.DLS_Theme) {
        var next = window.DLS_Theme.toggle();
        window.updateThemeUI(next);
    }
};

window.updateThemeUI = function(theme) {
    var current = theme || (window.DLS_Theme ? window.DLS_Theme.get() : (document.documentElement.classList.contains('dark') ? 'dark' : 'light'));
    var isDark = current === 'dark';
    var badge = document.getElementById('themeStatusBadge');
    var icon = document.getElementById('themeIcon');
    if (badge) badge.textContent = isDark ? 'Gelap' : 'Normal';
    if (icon) icon.textContent = isDark ? 'light_mode' : 'dark_mode';
};

window.submitStudentLogout = function() {
    // Profile has its own form; other pages use the shared navigation form.
    var form = document.getElementById('logoutForm') || document.getElementById('studentNavLogoutForm');
    if (!form) {
        console.error('Form logout aman tidak tersedia pada halaman ini.');
        return;
    }
    if (typeof form.requestSubmit === 'function') form.requestSubmit();
    else form.submit();
};

window.confirmStudentLogout = function() {
    if (window.DLS && window.DLS.confirm) {
        window.DLS.confirm({
            title: 'Konfirmasi Keluar',
            text: 'Apakah Anda yakin ingin mengakhiri sesi pembelajaran ini?',
            confirmText: 'Ya, Keluar',
            cancelText: 'Tetap di Sini',
            danger: true
        }).then(function(confirmed) {
            if (confirmed) window.submitStudentLogout();
        });
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Konfirmasi Keluar',
            text: 'Apakah Anda yakin ingin mengakhiri sesi pembelajaran ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626'
        }).then(function(r) {
            if (r.isConfirmed) window.submitStudentLogout();
        });
    } else {
        window.addEventListener('DLSReady', function() {
            window.confirmStudentLogout();
        }, { once: true });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.updateThemeUI();
    document.addEventListener('click', function(e) {
        var mobileMenu = document.getElementById('studentMobileProfileDropup');
        var mobileTrigger = document.getElementById('nav-profil');
        if (mobileMenu && !mobileMenu.classList.contains('hidden') &&
            !mobileMenu.contains(e.target) && mobileTrigger && !mobileTrigger.contains(e.target)) {
            window.toggleStudentMobileProfileMenu(false);
        }

        var menu = document.getElementById('studentProfileDropup');
        var trigger = document.getElementById('btnStudentProfileTrigger');
        if (menu && trigger && !menu.classList.contains('hidden')) {
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                menu.classList.add('hidden');
                menu.classList.remove('flex');
                var chevron = document.getElementById('profileDropupChevron');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') window.toggleStudentMobileProfileMenu(false);
    });
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) window.toggleStudentMobileProfileMenu(false);
    });
});
</script>
<div id="dlsPageSkeleton" class="dls-page-skeleton" aria-busy="true" aria-label="Memuat konten halaman...">
    <div class="dls-skel-inner">
        <div class="dls-skel-header">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div class="dls-skel-box" style="width:112px;height:14px;border-radius:6px;"></div>
                <div class="dls-skel-box" style="width:210px;height:28px;border-radius:8px;"></div>
            </div>
            <div class="dls-skel-box" style="width:144px;height:40px;border-radius:10px;"></div>
        </div>
        <div class="dls-skel-kpis">
            <div class="dls-skel-panel" style="height:136px;"></div>
            <div class="dls-skel-panel" style="height:136px;"></div>
            <div class="dls-skel-panel" style="height:136px;"></div>
            <div class="dls-skel-panel" style="height:136px;"></div>
        </div>
        <div class="dls-skel-panel" style="height:300px;"></div>
    </div>
</div>
