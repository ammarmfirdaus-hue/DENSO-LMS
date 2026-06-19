<?php
/**
 * Tampilan Profil Siswa DLS (DLS Student Profil View).
 * 
 * Halaman antarmuka untuk menampilkan data diri siswa, pengaturan akun,
 * serta opsi untuk berganti peran (role switching) dan keluar aplikasi.
 */
$active_tab = 'profil';
?>
<!DOCTYPE html><html lang="id" style=""><head>
    <?php 
    $page_title = 'Profile &amp; Role Switching - PT. LMS LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="dls-app-shell bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased pt-safe">
<!-- TopAppBar Shared Component -->
<header class="dls-topbar bg-surface dark:bg-on-background fixed top-0 w-full z-40 border-b border-outline-variant dark:border-outline pt-safe">
<div class="flex justify-between items-center w-full px-container-margin h-14">
<button class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center">
<span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 0;">business</span>
</button>
<div class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed tracking-tight">LMS</div>
<a href="?route=/student/notifikasi" onclick="markNotificationsRead()" class="text-primary dark:text-primary-fixed transition-all duration-200 active:scale-95 hover:bg-surface-container-low dark:hover:bg-surface-variant p-2 rounded-full flex items-center justify-center relative">
<span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 0;">notifications</span>
<span class="notification-dot absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
</a>
</div>
</header>
<!-- Main Content Canvas -->
<main class="dls-main flex-1 px-container-margin py-stack-lg flex flex-col gap-stack-lg pb-32 mt-14">
<!-- Profile Card (Glassmorphism/Premium) -->
<section class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex items-center gap-stack-md relative overflow-hidden">
<!-- Subtle gradient background element for premium feel -->
<div class="absolute -right-10 -top-10 w-32 h-32 bg-secondary-container opacity-20 rounded-full blur-2xl pointer-events-none"></div>
<div class="w-16 h-16 rounded-full overflow-hidden shrink-0 border-2 border-surface-container-high">
<img alt="Budi Santoso Avatar" class="w-full h-full object-cover" data-alt="A professional headshot of an Asian male employee in his 30s. He is wearing a crisp, clean corporate uniform reflecting a modern manufacturing or engineering setting. The lighting is soft and flattering studio lighting against a bright, pure white background. He has a warm, confident smile, conveying reliability and professionalism within a corporate modern aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9cdk8ogJL3w5_NK_GyqBiVTRDCvukSi1wBuUwvyCk4aYPtCY0cq1TqwcHgbQL_oM8zIGtY6Qc9HxNMF6ntprsZAOrebYyekTQ08a6liWME8Mbb84YFlne--pGRw1PuxTtapDDG5vwnrRa4WjzAkxEeFhSj0RlYKQUO04cC-0mb29AzlccPjtNlf0v6vY07qlVED0ce0prmKp_lgKoHDzZjYAQHAOLj8-8tsUeW6lwWTIKJ8KztRWlr5aUBvQcaQkNW-1XTqgHgUA">
</div>
<div class="flex-1 flex flex-col justify-center z-10">
<h1 class="font-headline-sm text-headline-sm text-on-surface mb-1">Hermawan</h1>
<p class="font-body-md text-body-md text-on-surface-variant mb-2">NPK: 19827364</p>
<div class="inline-flex items-center gap-1 bg-surface-container-high px-3 py-1 rounded-full w-fit">
<span class="material-symbols-outlined text-sm text-secondary" style="font-variation-settings: 'FILL' 1;">badge</span>
<span class="font-label-sm text-label-sm text-on-surface">Student</span>
</div>
</div>
</section>
<!-- Menu List -->
<section class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-variant flex flex-col overflow-hidden">
<!-- Profil Saya -->
<button class="flex items-center justify-between w-full p-stack-md bg-surface-container-lowest hover:bg-surface-container-low transition-colors border-b border-surface-variant text-left group">
<div class="flex items-center gap-stack-md">
<div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-secondary group-hover:bg-secondary-container/20 transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">person</span>
</div>
<span class="font-label-md text-label-md text-on-surface">Profil Saya</span>
</div>
<span class="material-symbols-outlined text-on-surface-variant" style="font-variation-settings: 'FILL' 0;">chevron_right</span>
</button>
<!-- Ubah Peran (Highlighted) -->
<a href="?route=/pilih-peran" class="flex items-center justify-between w-full p-stack-md bg-secondary-container/10 hover:bg-secondary-container/20 transition-colors border-b border-surface-variant text-left group relative overflow-hidden">
<div class="absolute left-0 top-0 bottom-0 w-1 bg-secondary"></div>
<div class="flex items-center gap-stack-md">
<div class="w-10 h-10 rounded-full bg-secondary-container/30 flex items-center justify-center text-secondary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">swap_horiz</span>
</div>
<span class="font-label-md text-label-md text-on-surface font-bold">Ubah Peran</span>
</div>
<div class="flex items-center gap-2">

<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 0;">chevron_right</span>
</div>
</a>
<!-- Pengaturan -->
<button class="flex items-center justify-between w-full p-stack-md bg-surface-container-lowest hover:bg-surface-container-low transition-colors border-b border-surface-variant text-left group">
<div class="flex items-center gap-stack-md">
<div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-secondary group-hover:bg-secondary-container/20 transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">settings</span>
</div>
<span class="font-label-md text-label-md text-on-surface">Pengaturan</span>
</div>
<span class="material-symbols-outlined text-on-surface-variant" style="font-variation-settings: 'FILL' 0;">chevron_right</span>
</button>
<!-- Bantuan -->
<button class="flex items-center justify-between w-full p-stack-md bg-surface-container-lowest hover:bg-surface-container-low transition-colors border-b border-surface-variant text-left group">
<div class="flex items-center gap-stack-md">
<div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-secondary group-hover:bg-secondary-container/20 transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">help</span>
</div>
<span class="font-label-md text-label-md text-on-surface">Bantuan</span>
</div>
<span class="material-symbols-outlined text-on-surface-variant" style="font-variation-settings: 'FILL' 0;">chevron_right</span>
</button>
<!-- Keluar -->
<a href="?route=/auth/logout" class="flex items-center justify-between w-full p-stack-md bg-surface-container-lowest hover:bg-error-container/20 transition-colors text-left group">
<div class="flex items-center gap-stack-md">
<div class="w-10 h-10 rounded-full bg-error-container/30 flex items-center justify-center text-error group-hover:bg-error-container/50 transition-colors">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">logout</span>
</div>
<span class="font-label-md text-label-md text-error">Keluar</span>
</div>
</a>
</section>
</main>
<!-- BottomNavBar Shared Component -->
<?php include 'includes/components/DX_LMS_M_Bottom_Nav.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('hasUnreadNotifications') === 'false') {
            document.querySelectorAll('.notification-dot').forEach(dot => dot.classList.add('hidden'));
        }
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
</body></html>
