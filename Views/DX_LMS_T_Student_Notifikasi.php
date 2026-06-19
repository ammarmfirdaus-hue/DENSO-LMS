<?php
/**
 * DLS Student Notifikasi View
 * Learning Management System
 */
?>
<!DOCTYPE html><html lang="id" style=""><head>
    <?php 
    $page_title = 'Notifikasi - LMS Learning System';
    include 'includes/components/DX_LMS_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen font-body-md pt-safe flex flex-col">
<!-- TopAppBar Shared Component -->
<header class="bg-surface dark:bg-on-background fixed top-0 w-full z-40 border-b border-outline-variant dark:border-outline pt-safe">
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
<main class="flex-1 pt-[72px] pb-[90px] px-container-margin">
<div class="flex flex-col gap-stack-md">
<?php
// Kelompokkan notifikasi berdasarkan time_group
$grouped = [];
foreach ($notifications as $n) {
    $grouped[$n['time_group']][] = $n;
}

// Petakan time_group ke label tampilan
$groupLabels = ['hari_ini' => 'Hari Ini', 'kemarin' => 'Kemarin', 'minggu_ini' => 'Minggu Ini'];

// Petakan tipe notifikasi ke ikon/gaya
$typeStyles = [
    'materi' => ['icon' => 'menu_book', 'bg' => 'bg-secondary-container/20', 'text' => 'text-secondary'],
    'course' => ['icon' => 'school', 'bg' => 'bg-tertiary-container/10', 'text' => 'text-tertiary'],
    'quiz'   => ['icon' => 'assignment_turned_in', 'bg' => 'bg-primary-container/10', 'text' => 'text-primary'],
    'video'  => ['icon' => 'play_circle', 'bg' => 'bg-secondary-container/20', 'text' => 'text-secondary'],
];
?>

<?php foreach ($grouped as $group => $items): ?>
<div class="flex flex-col gap-stack-sm <?= $group !== array_key_first($grouped) ? 'mt-4' : '' ?>">
<h2 class="font-label-sm text-label-sm text-on-surface-variant px-2 uppercase tracking-wider"><?= e($groupLabels[$group] ?? ucfirst($group)) ?></h2>

<?php foreach ($items as $n):
    $style = $typeStyles[$n['type']] ?? $typeStyles['materi'];
?>
<div class="flex gap-3 w-full">
<div class="flex-shrink-0 w-10 h-10 <?= $style['bg'] ?> rounded-full flex items-center justify-center <?= $style['text'] ?>">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;"><?= e($style['icon']) ?></span>
</div>
<div class="bg-surface-container-lowest rounded-2xl rounded-tl-sm p-4 shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] flex-1 relative border border-surface-variant">
<?php if (!$n['is_read']): ?>
<div class="absolute top-4 right-4 w-2 h-2 bg-primary rounded-full"></div>
<?php endif; ?>
<p class="font-body-md text-on-surface pr-4"><?= $n['message'] ?></p>
<p class="font-label-sm text-label-sm text-on-surface-variant mt-2"><?= e($n['time']) ?></p>
</div>
</div>
<?php endforeach; ?>

</div>
<?php endforeach; ?>

</div>
</main>
<!-- BottomNavBar Shared Component -->
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 pb-safe shadow-lg dark:shadow-none bg-surface dark:bg-on-background shadow-[0_-4px_12px_0_rgba(0,0,0,0.05)] rounded-t-xl md:hidden border-t border-outline-variant">
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">home</span>
<span class="font-label-sm text-label-sm">Beranda</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/courses">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">school</span>
<span class="font-label-sm text-label-sm">Course</span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/riwayat">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">history</span>
<span class="font-label-sm text-label-sm">Riwayat</span>
</a>
<!-- Active Tab: Notifikasi -->
<a onclick="markNotificationsRead()" class="flex flex-col items-center justify-center text-secondary dark:text-secondary-fixed bg-secondary-container/20 rounded-xl px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant relative" href="?route=/student/notifikasi">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 1;">notifications</span>
<span class="font-label-sm text-label-sm">Notifikasi</span>
<span class="notification-dot absolute top-1 right-4 w-2 h-2 bg-error rounded-full"></span>
</a>
<a class="flex flex-col items-center justify-center text-on-surface-variant dark:text-surface-variant px-4 py-1 transition-transform duration-200 active:scale-90 hover:bg-surface-container-high dark:hover:bg-surface-variant" href="?route=/student/profil">
<span class="material-symbols-outlined mb-1" style="font-variation-settings: &quot;FILL&quot; 0;">person</span>
<span class="font-label-sm text-label-sm">Profil</span>
</a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Tandai otomatis sebagai telah dibaca saat mengunjungi halaman notifikasi
        markNotificationsRead();

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
