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
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="description" content="Learning Management System">
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
     Menggunakan font-display: block untuk MENCEGAH FOUT
     ===================================================== -->
<style>
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 400;
        font-display: block; /* Mencegah FOUT dengan menahan teks hingga font dimuat */
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 500;
        font-display: block;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Medium.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 700;
        font-display: block;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Bold.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Material Symbols Outlined';
        font-style: normal;
        font-weight: 400;
        font-display: block;
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
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Tokens.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Layout.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Components.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Tailwind_Output.css">
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Student_Api.js" defer></script>

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
        if (localStorage.getItem('hasUnreadNotifications') === 'false') {
            document.documentElement.classList.add('hide-notifications');
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
