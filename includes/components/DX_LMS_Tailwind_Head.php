<?php
/**
 * DLS Learning Management System
 * Tailwind Setup Component
 */

$page_title = $page_title ?? 'LMS - Learning Management System';
$base_path = $base_path ?? '';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="description" content="Learning Management System">
<title><?= htmlspecialchars($page_title) ?></title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=block" rel="stylesheet">
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
<style>
    .hide-notifications .notification-dot { display: none !important; }
    
    /* Hide scrollbar for Chrome, Safari and Opera */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>

<!-- Tailwind Offline Script -->
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Tailwind_Engine.js"></script>

<!-- Tailwind Config -->
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
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
      }
    }
  }
}
</script>

