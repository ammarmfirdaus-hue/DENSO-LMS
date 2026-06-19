<?php
/**
 * Tampilan Pemberitahuan Siswa DLS (DLS Student Notification View).
 * 
 * Menampilkan daftar notifikasi terbaru yang berkaitan dengan 
 * aktivitas pelatihan, penilaian, dan pesan sistem untuk siswa.
 */
$active_tab = 'notifikasi';
?>
<!DOCTYPE html><html lang="id" style=""><head>
    <?php 
    $page_title = 'Notifikasi - LMS Learning System';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="dls-app-shell bg-background text-on-background min-h-screen font-body-md pt-safe flex flex-col">
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
<main class="dls-main flex-1 pt-[72px] pb-[90px] px-container-margin">
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
<?php include 'includes/components/DX_LMS_M_Bottom_Nav.php'; ?>

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
