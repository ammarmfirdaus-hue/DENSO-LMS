<?php
/**
 * Komponen Unified Head — Learning Management System.
 *
 * Menyediakan elemen <head> standar untuk seluruh halaman aplikasi,
 * termasuk pemuatan Tailwind CSS (build dari CLI) dan konfigurasi font lokal (Inter, Material Symbols).
 *
 * Variabel yang Diterima:
 *   $page_title  - string  : Judul halaman untuk elemen <title>.
 *   $base_path   - string  : (opsional) Jalur relatif menuju direktori root.
 *
 * CATATAN DEVELOPER:
 * File ini menggunakan Tailwind CLI (bukan CDN).
 * Jika Anda menambahkan class Tailwind baru yang di-assign secara dinamis via PHP variable,
 * tambahkan class tersebut ke dalam "safelist" di tailwind.config.js,
 * lalu jalankan: npm run build
 */

$page_title = $page_title ?? 'LMS - Learning Management System';
$base_path = $base_path ?? '';
$icon_path = $base_path . 'assets/img/icon/denso-vector-logo.png';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="description" content="Learning Management System DENSO untuk pembelajaran dan pelatihan.">
<meta name="application-name" content="DENSO LMS">
<meta name="theme-color" content="#d71920">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="DENSO LMS">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="dls-api-base" content="<?= htmlspecialchars(SSO_API_URL, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="DENSO LMS">
<meta property="og:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="Learning Management System DENSO untuk pembelajaran dan pelatihan.">
<meta property="og:image" content="<?= htmlspecialchars($icon_path, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="Learning Management System DENSO untuk pembelajaran dan pelatihan.">
<meta name="twitter:image" content="<?= htmlspecialchars($icon_path, ENT_QUOTES, 'UTF-8') ?>">
<link rel="icon" type="image/png" href="<?= htmlspecialchars($icon_path, ENT_QUOTES, 'UTF-8') ?>">
<link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($icon_path, ENT_QUOTES, 'UTF-8') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars($base_path . 'assets/img/icon/dls-student-pwa-180.png', ENT_QUOTES, 'UTF-8') ?>">
<link rel="manifest" href="<?= htmlspecialchars($base_path . 'manifest.webmanifest', ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="<?= htmlspecialchars($base_path . 'assets/css/DX_LMS_M_Pwa_Install.css', ENT_QUOTES, 'UTF-8') ?>">
<title><?= htmlspecialchars($page_title) ?></title>

<!-- =====================================================
     CRITICAL RESOURCE PRELOADING
     Mencegah FOUT (Flash of Unstyled Text)
     ===================================================== -->
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Medium.ttf" as="font" type="font/ttf" crossorigin="anonymous">
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Bold.ttf" as="font" type="font/ttf" crossorigin="anonymous">
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_MaterialSymbolsOutlined.ttf" as="font" type="font/ttf" crossorigin="anonymous">

<!-- =====================================================
     FONT FACE DECLARATIONS
     Font swap menjaga teks tetap terbaca sebelum font lokal selesai dimuat
     ===================================================== -->
<style>
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Medium.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Bold.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Material Symbols Outlined';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_MaterialSymbolsOutlined.ttf') format('truetype');
    }

    /* Sembunyikan tombol cancel/clear bawaan browser (Chromium/Safari/Edge) agar tidak terjadi duplikasi tombol 'X' */
    input[type="search"]::-webkit-search-cancel-button,
    input[type="search"]::-webkit-search-decoration,
    input[type="search"]::-webkit-search-results-button,
    input[type="search"]::-webkit-search-results-decoration {
        -webkit-appearance: none !important;
        appearance: none !important;
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    input[type="search"]::-ms-clear,
    input[type="search"]::-ms-reveal {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>

<!-- =====================================================
     Tailwind CSS - Dimuat secara SYNCHRONOUS
     Mencegah FOUC (Flash of Unstyled Content) secara alami
     ===================================================== -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Poppins:wght@500;600;700&family=Roboto+Mono:wght@400;500&display=swap">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Tokens.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Layout.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Components.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Tailwind_Output.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Responsive_Redesign.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Coursera_Theme.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Student_Experience.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_FoodDash_Theme.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Modern_Lms_Theme.css?v=<?= (int) @filemtime(APP_ROOT . '/public/assets/css/DX_LMS_M_Modern_Lms_Theme.css') ?>">
<link rel="stylesheet" href="<?= $base_path ?>assets/vendor/driverjs/driver.css">
<script src="<?= $base_path ?>assets/vendor/driverjs/driver.js.iife.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Student_User_Guide.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Network.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Student_Api.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Helpers.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Notifications_Badge.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Action_Controls.js" defer></script>
<script src="<?= htmlspecialchars($base_path . 'assets/js/DX_LMS_M_Pwa_Install.js', ENT_QUOTES, 'UTF-8') ?>" defer></script>

<!-- =====================================================
     Dark Mode Early Initialization (Prevent FOUC) & DLS Theme Controller
     ===================================================== -->
<script>
(function() {
    'use strict';
    try {
        var savedTheme = localStorage.getItem('dls_theme') || localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    } catch(e) {}

    window.DLS_Theme = {
        get: function() {
            return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        },
        toggle: function() {
            var next = this.get() === 'dark' ? 'light' : 'dark';
            this.set(next);
            return next;
        },
        set: function(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                document.documentElement.setAttribute('data-theme', 'light');
            }
            localStorage.setItem('dls_theme', theme);
            localStorage.setItem('theme', theme);
            window.dispatchEvent(new CustomEvent('dls_theme_change', { detail: { theme: theme } }));
        }
    };
})();
</script>

<!-- =====================================================
     Non-blocking Initialization Scripts
     ===================================================== -->
<script>
(function() {
    'use strict';
    
    function initializeApp() {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (!document.cookie.includes('user_timezone')) {
            document.cookie = "user_timezone=" + tz + "; path=/; max-age=31536000";
        }
    }
    
    if ('requestIdleCallback' in window) {
        requestIdleCallback(initializeApp);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeApp, 1);
        });
    } else {
        setTimeout(initializeApp, 1);
    }
})();
</script>
<?php
$__devHost = strtolower((string) parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
$__isDevAutoReload = getenv('APP_ENV') !== 'production'
    && in_array((string) ($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1', '::1'], true)
    && in_array($__devHost, ['127.0.0.1', 'localhost'], true);
if ($__isDevAutoReload):
?>
<script>
(function() {
    var currentVersion = null;
    var timer = null;
    function checkReload() {
        if (document.visibilityState === 'hidden') return;
        fetch('/__dev_reload_stamp', { cache: 'no-store' })
            .then(function(res) { return res.ok ? res.json() : null; })
            .then(function(data) {
                if (!data || !data.version) return;
                if (currentVersion === null) {
                    currentVersion = data.version;
                } else if (data.version !== currentVersion) {
                    clearInterval(timer);
                    window.location.reload();
                }
            })
            .catch(function() {});
    }
    checkReload();
    timer = setInterval(checkReload, 1500);
})();
</script>
<?php endif; ?>
<style>
.dls-page-skeleton {
    position: fixed;
    top: 64px;
    right: 0;
    bottom: 76px;
    left: 0;
    z-index: 40;
    padding: 20px 16px;
    background: #F8FAFC;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.28s cubic-bezier(0.22, 1, 0.36, 1), visibility 0.28s ease;
}
html.dark .dls-page-skeleton {
    background: #0F141C;
}
html.dls-page-loading .dls-page-skeleton {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
main.dls-main {
    transition: opacity 0.24s cubic-bezier(0.22, 1, 0.36, 1), transform 0.24s cubic-bezier(0.22, 1, 0.36, 1);
}
html.dls-page-loading main.dls-main {
    opacity: 0;
    transform: translateY(4px);
}
@media (min-width: 1024px) {
    .dls-page-skeleton {
        top: 64px;
        bottom: 0;
        left: 240px;
        padding: 24px 28px;
    }
}
.dls-skel-inner {
    max-width: 1180px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
    animation: dlsSkelPulse 1.6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes dlsSkelPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.48; }
}
.dls-skel-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.dls-skel-box {
    background: #E2E8F0;
    border-radius: 10px;
}
html.dark .dls-skel-box {
    background: #1E293B;
}
.dls-skel-kpis {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}
@media (min-width: 1024px) {
    .dls-skel-kpis {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
.dls-skel-panel {
    background: #E2E8F0;
    border-radius: 14px;
}
html.dark .dls-skel-panel {
    background: #1E293B;
}
@media (prefers-reduced-motion: reduce) {
    .dls-skel-inner { animation: none; }
    .dls-page-skeleton, main.dls-main { transition: none; }
}
</style>
<script>
(function() {
    var root = document.documentElement;
    root.classList.add('dls-page-loading');
    var shownAt = Date.now();
    var safetyTimer = setTimeout(hideSkeleton, 900);

    function hideSkeleton() {
        clearTimeout(safetyTimer);
        var elapsed = Date.now() - shownAt;
        var remaining = Math.max(0, 180 - elapsed);
        setTimeout(function() {
            requestAnimationFrame(function() {
                root.classList.remove('dls-page-loading');
            });
        }, remaining);
    }

    if (document.readyState === 'complete') {
        hideSkeleton();
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(hideSkeleton, 60);
        });
        window.addEventListener('load', hideSkeleton);
    }

    window.addEventListener('pageshow', function() {
        clearTimeout(safetyTimer);
        root.classList.remove('dls-page-loading');
    });

    document.addEventListener('click', function(evt) {
        var anchor = evt.target && evt.target.closest ? evt.target.closest('a[href]') : null;
        if (!anchor) return;
        if (evt.metaKey || evt.ctrlKey || evt.shiftKey || evt.altKey) return;
        if (anchor.target && anchor.target !== '_self') return;
        var href = anchor.getAttribute('href') || '';
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
        if (href.indexOf('?route=') !== -1 || href.charAt(0) === '/') {
            shownAt = Date.now();
            root.classList.add('dls-page-loading');
            clearTimeout(safetyTimer);
            safetyTimer = setTimeout(function() {
                root.classList.remove('dls-page-loading');
            }, 1800);
        }
    });
})();
</script>


