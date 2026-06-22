<?php
/**
 * DLS Learning Management System
 * Unified Head Component — Tailwind + SweetAlert2 + Local Fonts
 *
 * Usage: include this in every page after setting $page_title
 * Variables expected:
 *   $page_title  - string: Title for <title> tag
 *   $base_path   - string (optional): Relative path to root
 */

$page_title = $page_title ?? 'LMS - Learning Management System';
$base_path = $base_path ?? '';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
<meta name="description" content="Learning Management System">
<title><?= htmlspecialchars($page_title) ?></title>

<!-- Local Fonts (@font-face) -->
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

<script>
    if (localStorage.getItem('hasUnreadNotifications') === 'false') {
        document.documentElement.classList.add('hide-notifications');
    }
    
    // Auto-detect Timezone (WIB/WITA/WIT)
    (function() {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (!document.cookie.includes('user_timezone')) {
            document.cookie = "user_timezone=" + tz + "; path=/; max-age=31536000";
            if (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'].includes(tz)) {
                window.location.reload();
            }
        }
    })();
</script>

<!-- DLS FOUC Prevention System v4 — Hide Until Tailwind Done -->
<style id="dls-fouc-preventer">
    /* Sembunyikan SELURUH konten body sampai Tailwind selesai memproses */
    /* visibility:hidden tetap memungkinkan layout calculation tapi user tidak melihat apapun */
    body:not(.dls-ready) {
        visibility: hidden !important;
    }
    
    /* Background html tetap tampil agar tidak ada flash putih */
    html {
        background-color: #fcf9f8;
    }

    .hide-notifications .notification-dot { display: none !important; }
    
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
(function() {
    // Tunggu sampai Tailwind Engine benar-benar selesai memproses SEMUA class.
    // Tailwind Engine menambahkan/memodifikasi <style> di <head>.
    // Kita menggunakan debounced MutationObserver — baru reveal body
    // setelah <head> BERHENTI berubah selama 80ms (artinya Tailwind sudah selesai).
    
    var revealTimer = null;
    
    function revealPage() {
        if (document.body) {
            document.body.classList.add('dls-ready');
        }
        if (headObserver) headObserver.disconnect();
    }
    
    var headObserver = new MutationObserver(function() {
        // Setiap kali Tailwind menambah/mengubah style, reset timer
        clearTimeout(revealTimer);
        revealTimer = setTimeout(revealPage, 20);
    });
    
    // Mulai observe <head> untuk mendeteksi kapan Tailwind selesai inject styles
    if (document.head) {
        headObserver.observe(document.head, { childList: true, subtree: true, characterData: true });
    }
    
    // Fallback: jika tidak ada perubahan di head sama sekali (misal cache), reveal setelah 300ms
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (!document.body.classList.contains('dls-ready')) {
                revealPage();
            }
        }, 100);
    });
    
    // Fallback absolut: jangan pernah biarkan halaman tersembunyi lebih dari 1 detik
    setTimeout(function() {
        if (document.body && !document.body.classList.contains('dls-ready')) {
            revealPage();
        }
    }, 500);
    
    // Fix BFCache: pastikan halaman terlihat saat kembali dari cache dan refresh untuk mencegah DOM corruption
    window.addEventListener('pageshow', function(e) {
        if (e.persisted && document.body) {
            document.body.classList.add('dls-ready');
            window.location.reload();
        }
    });
})();
</script>

<!-- Tailwind Offline Script -->
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Tailwind_Engine.js"></script>

<!-- Tailwind Config -->
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  safelist: [
    'grid',
    'grid-cols-1',
    'md:grid-cols-2',
    'lg:grid-cols-3',
    'gap-gutter',
    'hidden',
    'dls-hidden'
  ],
  theme: {
    extend: {
      colors: {
        "tertiary-container": "#178229",
        "tertiary-fixed": "#94f990",
        "on-secondary-fixed": "#00105c",
        "on-tertiary-fixed-variant": "#005313",
        "error": "#ba1a1a",
        "tertiary-fixed-dim": "#78dc77",
        "on-primary-fixed-variant": "#930007",
        "on-primary-fixed": "#410001",
        "primary-container": "#e20613",
        "on-tertiary-container": "#deffd6",
        "on-background": "#1b1c1c",
        "on-tertiary-fixed": "#002204",
        "on-error": "#ffffff",
        "on-error-container": "#93000a",
        "on-primary": "#ffffff",
        "surface-container-lowest": "#ffffff",
        "inverse-primary": "#ffb4aa",
        "primary": "#b4000b",
        "surface-container-low": "#f6f3f2",
        "on-tertiary": "#ffffff",
        "on-secondary-container": "#11278e",
        "surface": "#fcf9f8",
        "primary-fixed-dim": "#ffb4aa",
        "secondary-fixed": "#dee0ff",
        "outline-variant": "#e9bcb6",
        "surface-variant": "#e5e2e1",
        "secondary-fixed-dim": "#bac3ff",
        "error-container": "#ffdad6",
        "surface-container": "#f0eded",
        "secondary-container": "#8596ff",
        "secondary": "#4355b9",
        "primary-fixed": "#ffdad5",
        "on-surface-variant": "#5e3f3b",
        "inverse-surface": "#303030",
        "on-secondary-fixed-variant": "#293ca0",
        "surface-container-high": "#eae7e7",
        "inverse-on-surface": "#f3f0ef",
        "surface-bright": "#fcf9f8",
        "tertiary": "#00671a",
        "surface-dim": "#dcd9d9",
        "on-primary-container": "#fff3f2",
        "surface-tint": "#c0000d",
        "on-secondary": "#ffffff",
        "background": "#fcf9f8",
        "outline": "#936e69",
        "on-surface": "#1b1c1c",
        "surface-container-highest": "#e5e2e1"
      },
      borderRadius: {
        "DEFAULT": "0.25rem",
        "lg": "0.5rem",
        "xl": "0.75rem",
        "2xl": "1rem",
        "3xl": "1.5rem",
        "full": "9999px"
      },
      spacing: {
        "container-margin": "16px",
        "stack-md": "16px",
        "stack-sm": "8px",
        "stack-lg": "24px",
        "gutter": "16px",
        "base": "8px"
      },
      fontFamily: {
        "sans": ["Inter", "system-ui", "-apple-system", "sans-serif"],
        "headline-lg": ["Inter", "sans-serif"],
        "headline-md": ["Inter", "sans-serif"],
        "body-lg": ["Inter", "sans-serif"],
        "headline-sm": ["Inter", "sans-serif"],
        "body-md": ["Inter", "sans-serif"],
        "label-sm": ["Inter", "sans-serif"],
        "label-md": ["Inter", "sans-serif"]
      },
      fontSize: {
        "headline-lg": ["28px", {"lineHeight": "34px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
        "headline-md": ["22px", {"lineHeight": "28px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
        "headline-sm": ["18px", {"lineHeight": "24px", "fontWeight": "700"}],
        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "500"}],
        "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "500"}]
      },
      screens: {
        "xs": "375px",
        "sm": "640px",
        "md": "768px",
        "lg": "1024px",
        "xl": "1280px"
      }
    }
  }
}
</script>

<!-- SweetAlert2 Offline -->
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

<style type="text/tailwindcss">
    @layer utilities {
        .dls-main {
            @apply flex-1 px-container-margin pt-[56px] pb-[88px] overflow-y-auto space-y-stack-lg lg:ml-[240px] lg:w-[calc(100%-240px)];
        }
        .dls-main-sub {
            @apply flex-1 px-container-margin pt-[56px] pb-[88px] overflow-y-auto space-y-stack-lg lg:ml-[240px] lg:w-[calc(100%-240px)];
        }
    }
</style>
