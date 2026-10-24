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
?>
<form id="studentNavLogoutForm" class="hidden" method="POST" action="?route=/auth/logout" aria-hidden="true">
    <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
</form>
<nav class="fixed bottom-0 left-0 w-full z-50 flex items-stretch px-0 h-[76px] shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] dark:shadow-none bg-surface dark:bg-on-background rounded-t-xl lg:h-screen lg:flex-col lg:justify-start lg:top-0 lg:left-0 lg:rounded-none lg:border-r border-t border-outline-variant dark:border-outline lg:border-t-0 lg:p-4 lg:pt-8 lg:gap-2 transition-all duration-300" id="bottomNavBar">

    <!-- Brand untuk sidebar desktop -->
    <div class="dls-nav-brand" id="dlsNavBrand">
        <img src="<?= htmlspecialchars($base_path . 'assets/img/icon/denso-vector-logo.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo DENSO" class="w-9 h-9 object-contain shrink-0">
        <span class="dls-nav-brand-copy dls-sidebar-text"><strong>DENSO</strong><small>Learning</small></span>
        <!-- Collapse button only visible when expanded -->
        <button type="button" id="dlsSidebarCollapseBtn" onclick="window.toggleStudentSidebar()" class="dls-sidebar-text ml-auto flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition cursor-pointer" title="Kecilkan sidebar" aria-label="Kecilkan sidebar">
            <span class="material-symbols-outlined" style="font-size:18px">chevron_left</span>
        </button>
    </div>

    <p class="dls-nav-section-label dls-sidebar-text">MENU UTAMA</p>

    <?php foreach ($tabs as $tab): ?>
    <?php
    $is_active = $active_tab === $tab['id'];
    $active_class = $is_active
        ? 'text-primary dark:text-primary-fixed bg-primary-container-light lg:bg-primary-container-light font-semibold'
        : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-variant';
    ?>
    <a href="<?= $tab['href'] ?>" 
       class="flex-1 lg:flex-none flex flex-col lg:flex-row items-center lg:justify-start justify-center rounded-xl py-2 lg:px-4 lg:py-3 lg:w-full transition-transform duration-200 active:scale-95 relative <?= $active_class ?>" 
       id="nav-<?= $tab['id'] ?>"
       aria-label="<?= $tab['label'] ?>"<?= $is_active ? ' aria-current="page"' : '' ?>>
        
        <span class="material-symbols-outlined mb-1 lg:mb-0 lg:mr-3"
              style="font-variation-settings: 'FILL' <?= $is_active ? '1' : '0' ?>;"
        ><?= $tab['icon'] ?></span>
        
        <span class="font-label-sm text-label-sm lg:text-body-md"><?= $tab['label'] ?></span>

        <?php if ($tab['id'] === 'notifikasi'): ?>
        <span data-notification-count class="dls-bottom-notification-count" aria-hidden="true"<?= (int) ($notif_count ?? 0) > 0 ? '' : ' hidden' ?>><?= (int) ($notif_count ?? 0) > 99 ? '99+' : (int) ($notif_count ?? 0) ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>

    <?php
    $student_name = $_SESSION['student_name'] ?? ($_SESSION['user']['name'] ?? 'Siswa DENSO');
    $student_npk = $_SESSION['student_npk'] ?? ($_SESSION['user']['npk'] ?? 'Student');
    ?>
    <!-- Desktop Profile Dropup Section -->
    <div class="hidden lg:block relative mt-auto pt-3 border-t border-outline-variant/50 dark:border-slate-800 w-full">
        <!-- Dropup Menu (Initially hidden) -->
        <div id="studentProfileDropup" class="hidden absolute bottom-full left-0 mb-3 w-full bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-2.5 z-50 flex-col gap-1.5 transition-all">
            <!-- User summary card -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-100 dark:border-slate-700/60 mb-1">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate"><?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="flex items-center justify-between gap-1 mt-0.5">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono"><?= htmlspecialchars($student_npk, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-400 uppercase">Student</span>
                </div>
            </div>

            <!-- Theme Toggle Button -->
            <button type="button" id="btnStudentThemeToggle" onclick="window.toggleStudentTheme()" class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-amber-500" id="themeIcon">dark_mode</span>
                    <span>Mode Tampilan</span>
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300" id="themeStatusBadge">Normal</span>
            </button>

            <!-- Edit / View Profil Link -->
            <a href="?route=/student/profil" class="flex items-center gap-2 px-2.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <span class="material-symbols-outlined text-[18px] text-slate-500 dark:text-slate-400">person</span>
                <span>Lihat Profil Saya</span>
            </a>

            <!-- Logout Button with SweetAlert2 Confirm -->
            <button type="button" onclick="window.confirmStudentLogout()" class="w-full flex items-center gap-2 px-2.5 py-2 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer">
                <span class="material-symbols-outlined text-[18px] text-red-600">logout</span>
                <span>Keluar Akun</span>
            </button>
        </div>

        <!-- Trigger Button -->
        <button type="button" id="btnStudentProfileTrigger" onclick="window.toggleStudentProfileDropup()" class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition cursor-pointer text-left">
            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-fixed flex items-center justify-center font-bold text-xs shrink-0">
                <?= strtoupper(substr($student_name, 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate"><?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">Siswa DENSO</p>
            </div>
            <span class="material-symbols-outlined text-slate-400 text-[18px] transition-transform duration-200" id="profileDropupChevron">expand_less</span>
        </button>
    </div>
</nav>

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
        if (confirm('Apakah Anda yakin ingin keluar?')) window.submitStudentLogout();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.updateThemeUI();
    document.addEventListener('click', function(e) {
        var menu = document.getElementById('studentProfileDropup');
        var trigger = document.getElementById('btnStudentProfileTrigger');
        if (menu && !menu.classList.contains('hidden')) {
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                menu.classList.add('hidden');
                menu.classList.remove('flex');
                var chevron = document.getElementById('profileDropupChevron');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }
    });
});
</script>
