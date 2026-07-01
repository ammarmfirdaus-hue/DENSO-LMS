<?php
/**
 * Komponen Unified Head — Learning Management System.
 *
 * Menyediakan elemen <head> standar untuk seluruh halaman aplikasi,
 * termasuk pemuatan Tailwind CSS (build dari CLI), SweetAlert2,
 * dan konfigurasi font lokal (Inter, Material Symbols).
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
     Tailwind CSS (Build dari Tailwind CLI)
     ===================================================== -->
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Tailwind_Output.css">

<!-- =====================================================
     Preload Fonts (Diletakkan di bawah CSS agar CSS diunduh pertama)
     ===================================================== -->
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Medium.ttf" as="font" type="font/ttf" crossorigin="anonymous">
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Bold.ttf" as="font" type="font/ttf" crossorigin="anonymous">
<link rel="preload" href="<?= $base_path ?>assets/fonts/DX_LMS_M_MaterialSymbolsOutlined.ttf" as="font" type="font/ttf" crossorigin="anonymous">

<!-- =====================================================
     Local Fonts (@font-face)
     ===================================================== -->
<style>
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 400;
        font-display: optional;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 500;
        font-display: optional;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Medium.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Inter';
        font-style: normal;
        font-weight: 700;
        font-display: optional;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_Inter_Bold.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Material Symbols Outlined';
        font-style: normal;
        font-weight: 400;
        font-display: block;
        src: url('<?= $base_path ?>assets/fonts/DX_LMS_M_MaterialSymbolsOutlined.ttf') format('truetype');
    }

    /* Material Symbols icon class — required for ligature rendering */
    .material-symbols-outlined {
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        font-size: 24px;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
</style>

<!-- =====================================================
     Tailwind CSS (Build dari Tailwind CLI)
     Sudah dimuat di atas bersama dengan preload.
     ===================================================== -->

<!-- =====================================================
     SweetAlert2 CSS (Offline)
     ===================================================== -->
<script src="<?= $base_path ?>assets/js/DX_LMS_M_SweetAlert2.min.js"></script>

<!-- SweetAlert2 DLS Theme Override -->
<style>
    /* Match SweetAlert2 with DLS Design Tokens */
    .swal2-popup {
        font-family: 'Inter', system-ui, sans-serif !important;
        border-radius: 24px !important;
        padding: 32px 24px 24px !important;
    }
    .swal2-title {
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #1b1c1c !important;
    }
    .swal2-html-container {
        font-size: 14px !important;
        color: #5e3f3b !important;
    }
    .swal2-confirm {
        background-color: #4355b9 !important;
        border-radius: 9999px !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 500 !important;
        font-size: 14px !important;
        padding: 12px 24px !important;
        box-shadow: 0 4px 12px rgba(67, 85, 185, 0.2) !important;
    }
    .swal2-cancel {
        background-color: transparent !important;
        color: #1b1c1c !important;
        border: 1px solid #e9bcb6 !important;
        border-radius: 9999px !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 500 !important;
        font-size: 14px !important;
        padding: 12px 24px !important;
    }
    .swal2-deny {
        background-color: #e20613 !important;
        border-radius: 9999px !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 500 !important;
    }
    /* Toast style */
    .swal2-popup.swal2-toast {
        border-radius: 12px !important;
        padding: 12px 16px !important;
    }
</style>

<script>
    /**
     * DLS SweetAlert2 Helper Functions
     * Wrapper functions for consistent dialogs across the LMS
     */
    const DLS = {
        confirm: function(options) {
            return Swal.fire({
                icon: options.icon || 'question',
                title: options.title || 'Konfirmasi',
                text: options.text || '',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
                cancelButtonText: options.cancelText || 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'dls-swal-popup'
                }
            });
        },
        success: function(title, text) {
            return Swal.fire({
                icon: 'success',
                title: title || 'Berhasil!',
                text: text || '',
                confirmButtonText: 'OK'
            });
        },
        error: function(title, text) {
            return Swal.fire({
                icon: 'error',
                title: title || 'Gagal!',
                text: text || '',
                confirmButtonText: 'OK'
            });
        },
        toast: function(title, icon) {
            return Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon || 'info',
                title: title,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    };
</script>

<!-- Auto-detect Timezone (WIB/WITA/WIT) -->
<script>
(function() {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
    if (!document.cookie.includes('user_timezone')) {
        document.cookie = "user_timezone=" + tz + "; path=/; max-age=31536000";
        /** Pembaruan halaman dinonaktifkan untuk mencegah kilatan visual (flicker). */
    }
    if (localStorage.getItem('hasUnreadNotifications') === 'false') {
        document.documentElement.classList.add('hide-notifications');
    }

    /** Menangani masalah cache peramban agar desain tidak hilang saat tombol kembali digunakan. */
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
})();
</script>
