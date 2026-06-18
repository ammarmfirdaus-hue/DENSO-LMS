<?php
/**
 * DLS Learning Management System
 * 
 * Usage: include this in every page after setting $page_title
 * Variables expected:
 *   $page_title  - string: Title for <title> tag
 *   $page_css    - array (optional): Additional CSS files to load
 */

// Nilai default
$page_title = $page_title ?? 'LMS - Learning Management System';
$page_css = $page_css ?? [];

// Menentukan path dasar relatif terhadap file saat ini
$base_path = $base_path ?? '';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

<!-- Bootstrap 5 Offline -->
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Bootstrap.min.css">

<!-- DLS Design System -->
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Tokens.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Components.css">
<link rel="stylesheet" href="<?= $base_path ?>assets/css/DX_LMS_M_Dls_Layout.css">

<!-- Page-specific CSS -->
<?php foreach ($page_css as $css): ?>
<link rel="stylesheet" href="<?= $base_path . $css ?>">
<?php endforeach; ?>

<!-- Global Page Transition Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Menangkap klik pada link untuk efek fade-out yang mulus
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const target = this.getAttribute('target');
                
                // Hanya menangkap link standar internal
                if (href && !href.startsWith('#') && !href.startsWith('javascript:') && target !== '_blank') {
                    e.preventDefault();
                    document.body.classList.add('dls-navigating');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 150);
                }
            });
        });
        
        // Juga menangkap pengiriman form (seperti login/pilih peran)
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                document.body.classList.add('dls-navigating');
            });
        });
    });
</script>
