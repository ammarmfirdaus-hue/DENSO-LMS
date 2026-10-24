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
<link rel="apple-touch-icon" href="<?= htmlspecialchars($icon_path, ENT_QUOTES, 'UTF-8') ?>">
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
</style>

<!-- =====================================================
     Tailwind CSS - Dimuat secara SYNCHRONOUS
     Mencegah FOUC (Flash of Unstyled Content) secara alami
     ===================================================== -->
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Tokens.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Layout.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Components.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Tailwind_Output.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Responsive_Redesign.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Coursera_Theme.css">
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Student_Api.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Helpers.js" defer></script>
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Notifications_Badge.js" defer></script>

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

