<?php
/**
 * Komponen Navigasi Samping Khusus Teacher (Sidebar).
 * Hanya tampil pada breakpoint Desktop (lg ke atas).
 */

$active_tab = $active_tab ?? 'beranda';
$base_path = $base_path ?? '';
$userSession = $_SESSION['user'] ?? [];
$userName = $userSession['fullname'] ?? ($userSession['name'] ?? 'Teacher Dummy');
$userJabatan = $userSession['jabatan'] ?? 'Leader';

// Cek tipe teacher (jika ada flag)
$teacherType = $_SESSION['teacher_type'] ?? 'Teacher Associate';

$nav_groups = [
    [
        'title' => 'Utama',
        'items' => [['id' => 'beranda', 'label' => 'Dashboard', 'icon' => 'home', 'href' => '?route=/teacher']],
    ],
    [
        'title' => 'Pengajaran',
        'items' => [
            ['id' => 'course', 'label' => 'Kelola Course', 'icon' => 'school', 'href' => '?route=/teacher/courses'],
            ['id' => 'grade', 'label' => 'Penilaian & Capaian', 'icon' => 'fact_check', 'href' => '?route=/teacher/grade'],
            ['id' => 'forum', 'label' => 'Forum Diskusi', 'icon' => 'forum', 'href' => '?route=/teacher/forum'],
        ],
    ],
    [
        'title' => 'Komunikasi & Log',
        'items' => [
            [
                'id' => 'aktivitas',
                'label' => 'Aktivitas Terbaru',
                'icon' => 'history',
                'href' => '?route=/teacher/aktivitas',
            ],
            [
                'id' => 'notifikasi',
                'label' => 'Notifikasi',
                'icon' => 'notifications',
                'href' => '?route=/teacher/notifikasi',
            ],
        ],
    ],
    [
        'title' => 'Akun',
        'items' => [
            [
                'id' => 'profil',
                'label' => 'Profil',
                'icon' => 'person',
                'href' => '?route=/teacher/profil',
            ],
        ],
    ],
];
?>

<aside class="hidden lg:flex flex-col w-[240px] bg-surface-container fixed left-0 top-0 h-screen border-r border-outline-variant z-50">


    <!-- Profil Mini -->
    <div class="px-4 py-6 border-b border-outline-variant">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-label-lg shrink-0">
                <?= substr(htmlspecialchars($userName), 0, 1) ?>
            </div>
            <div class="min-w-0">
                <p class="font-label-md font-bold text-on-surface truncate"><?= htmlspecialchars($userName) ?></p>
                <div class="flex flex-col gap-0.5 mt-0.5">
                    <p class="font-label-sm text-[11px] text-on-surface-variant truncate"><?= htmlspecialchars(
                        $userJabatan,
                    ) ?></p>
                    <p class="font-label-sm text-[10px] text-secondary truncate flex items-center gap-1">
                        <span class="material-symbols-outlined text-[10px]" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                        <?= htmlspecialchars($teacherType) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigasi -->
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-6">
        <?php foreach ($nav_groups as $group): ?>
            <div class="space-y-1">
                <h3 class="px-2 font-label-sm font-semibold text-on-surface-variant text-[11px] uppercase tracking-wider mb-2">
                    <?= htmlspecialchars($group['title']) ?>
                </h3>
                <?php foreach ($group['items'] as $item): ?>
                    <?php
                    $isActive = $active_tab === $item['id'];
                    $activeClasses = $isActive
                        ? 'bg-primary-container text-on-primary-container font-bold'
                        : 'text-on-surface hover:bg-surface-variant transition-colors';
                    ?>
                    <a href="<?= htmlspecialchars(
                        $item['href'],
                    ) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $activeClasses ?>">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' <?= $isActive
                            ? '1'
                            : '0' ?>;">
                            <?= htmlspecialchars($item['icon']) ?>
                        </span>
                        <span class="font-label-md"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>
</aside>
