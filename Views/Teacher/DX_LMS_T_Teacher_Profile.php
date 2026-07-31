<?php
/**
 * Tampilan Profil Pengajar (Teacher Profile).
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Profil - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'profil'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <?php
        $header_title = 'Menu Lainnya';
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    </div>
    
    <!-- Desktop Header -->
    <div class="hidden lg:flex lg:ml-[240px] px-container-margin py-4 border-b border-outline-variant/30 sticky top-0 z-30 bg-surface/90 backdrop-blur-md items-center gap-3">
        <h1 class="font-headline-6 font-bold text-on-surface">Data Diri & Pengaturan</h1>
    </div>

    <!-- Main Content Canvas (Match Student Structure) -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin py-4 lg:py-8 flex flex-col pb-32">
        <div class="w-full max-w-3xl mx-auto flex flex-col gap-5">
            
            <!-- Top Profile Card -->
            <section class="bg-surface rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-outline-variant/30 overflow-hidden">
                <!-- Top Section with Gradient -->
                <div class="relative p-6 lg:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6 border-b border-outline-variant/30">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-error-container/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>

                    <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border-4 border-surface shadow-sm relative z-10 bg-surface">
                        <?php if (!empty($profile['photo_url'])): ?>
                            <img src="<?= e(
                                $profile['photo_url'],
                            ) ?>" alt="Profile Photo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php
                            $profileName = $profile['name'] ?? 'U';
                            $initials = '';
                            $nameParts = explode(' ', trim($profileName));
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                            ?>
                            <div class="w-full h-full bg-gradient-to-br from-primary/80 to-primary flex items-center justify-center">
                                <span class="text-white font-bold text-2xl"><?= e($initials) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 flex flex-col justify-center relative z-10 text-center sm:text-left mt-2 sm:mt-0">
                        <h1 class="font-headline-sm text-on-surface font-bold tracking-tight mb-1"><?= e(
                            $profile['name'],
                        ) ?></h1>
                        <p class="font-body-md text-on-surface-variant mb-4">NPK: <?= e($profile['npk']) ?></p>

                        <div class="flex items-center justify-center sm:justify-start gap-3">
                            <div class="inline-flex items-center gap-1.5 bg-[#e8eaf6] px-3 py-1.5 rounded-full">
                                <span class="font-label-sm text-primary font-bold tracking-wide"><?= e(
                                    $profile['role'],
                                ) ?></span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 bg-[#e8f5e9] px-3 py-1.5 rounded-full">
                                <span class="material-symbols-outlined text-[16px] text-success" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                <span class="font-label-sm text-success font-bold tracking-wide">Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section Data -->
                <?php
                $position = $profile['position'] ?? 'Leader';
                $userGolongan = $profile['golongan'] ?? 'Gol 3';
                $golList = [
                    ['label' => 'Gol 1', 'name' => 'Operator'],
                    ['label' => 'Gol 2', 'name' => 'Senior Op'],
                    ['label' => 'Gol 3', 'name' => 'Leader'],
                    // ['label' => 'Gol 4', 'name' => 'Supervisor'],
                    // ['label' => 'Gol 5', 'name' => 'Manager'],
                ];
                ?>
                <div class="bg-surface-container-lowest/50 p-5 lg:px-8 lg:py-6 flex flex-col sm:flex-row gap-6">

                    <!-- LEFT: Info Departemen & Divisi -->
                    <div class="flex flex-col gap-4 min-w-0 sm:w-1/3">
                        <div>
                            <p class="text-[11px] uppercase font-bold text-on-surface-variant tracking-wider mb-1">DEPARTEMEN</p>
                            <p class="font-body-md text-on-surface font-semibold"><?= e(
                                $profile['department'] ?? '-',
                            ) ?></p>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase font-bold text-on-surface-variant tracking-wider mb-1">DIVISI / SEKSI</p>
                            <p class="font-body-md text-on-surface font-semibold truncate" title="<?= e(
                                $profile['division'] ?? '-',
                            ) ?> / <?= e($profile['section'] ?? '-') ?>">
                                <?= e($profile['division'] ?? '-') ?> / <?= e($profile['section'] ?? '-') ?>
                            </p>
                        </div>
                    </div>

                    <!-- DIVIDER -->
                    <div class="hidden sm:block w-px bg-outline-variant/30 self-stretch"></div>
                    <div class="block sm:hidden h-px bg-outline-variant/30 w-full"></div>

                    <!-- RIGHT: Jabatan + Golongan Hierarchy -->
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] uppercase font-bold text-on-surface-variant tracking-wider mb-3">JABATAN &amp; GOLONGAN</p>
                        <div class="flex items-center gap-2 mb-4 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary px-3 py-1 rounded-full font-label-sm font-bold">
                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">badge</span>
                                <?= e($position) ?>
                            </span>
                            <?php
                            $activeGolLabel = '';
                            $activeGolName = '';
                            $activeIdx = 0;
                            foreach ($golList as $ki => $kg) {
                                if ($kg['label'] === $userGolongan) {
                                    $activeGolLabel = $kg['label'];
                                    $activeGolName = $kg['name'];
                                    $activeIdx = $ki;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($activeGolLabel): ?>
                            <span class="inline-flex items-center gap-1 bg-secondary/10 text-secondary px-3 py-1 rounded-full font-label-sm font-bold">
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">military_tech</span>
                                <?= e($activeGolLabel) ?> — <?= e($activeGolName) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Golongan Step Ladder -->
                        <?php if (!empty($golList)): ?>
                        <div class="relative flex items-start">
                            <div class="absolute left-0 right-0 top-[14px] h-[3px] bg-outline-variant/25 z-0"></div>
                            <?php if ($activeIdx > 0): ?>
                            <div class="absolute left-0 top-[14px] h-[3px] bg-primary/60 z-0" style="width: calc(<?= round(
                                ($activeIdx / (count($golList) - 1)) * 100,
                                2,
                            ) ?>% - 14px)"></div>
                            <?php endif; ?>

                            <?php foreach ($golList as $idx => $gol):

                                $isActive = $idx === $activeIdx;
                                $isPast = $idx < $activeIdx;
                                ?>
                            <!-- Step item -->
                            <div class="flex-1 flex flex-col items-center relative z-10">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center transition-all <?= $isActive ||
                                $isPast
                                    ? 'bg-primary ring-4 ring-primary/20'
                                    : 'bg-surface border-2 border-outline-variant/40' ?>">
                                    <?php if ($isActive): ?>
                                    <span class="block w-2.5 h-2.5 rounded-full bg-white"></span>
                                    <?php elseif ($isPast): ?>
                                    <span class="material-symbols-outlined text-white" style="font-size:16px;font-variation-settings:'FILL' 1;line-height:1;display:block;">check</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 text-center w-full px-1">
                                    <p class="text-[12px] font-bold leading-tight <?= $isActive || $isPast
                                        ? 'text-primary'
                                        : 'text-on-surface-variant/40' ?>"><?= e($gol['label']) ?></p>
                                    <p class="text-[12px] leading-tight mt-0.5 <?= $isActive
                                        ? 'text-on-surface font-semibold'
                                        : ($isPast
                                            ? 'text-primary/70'
                                            : 'text-on-surface-variant/50') ?>"><?= e($gol['name']) ?></p>
                                </div>
                            </div>
                            <?php
                            endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>


            <!-- Ringkasan Pembelajaran Teacher -->
            <section class="flex flex-col gap-4">
                <h2 class="text-[13px] font-bold text-on-surface-variant tracking-wider uppercase pl-2">RINGKASAN MENGAJAR</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-surface rounded-xl p-4 border border-outline-variant/30 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-error text-[24px]">school</span>
                        <div class="flex flex-col">
                            <span class="font-headline-sm font-bold text-on-surface"><?= $stats['total_course'] ??
                                0 ?></span>
                            <span class="text-[14px] text-on-surface-variant">Course Diampu</span>
                        </div>
                    </div>
                    <div class="bg-surface rounded-xl p-4 border border-outline-variant/30 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-success text-[24px] bg-success/10 rounded-full p-1">groups</span>
                        <div class="flex flex-col">
                            <span class="font-headline-sm font-bold text-on-surface"><?= $stats['active_students'] ??
                                0 ?></span>
                            <span class="text-[14px] text-on-surface-variant">Peserta Aktif</span>
                        </div>
                    </div>
                    <div class="bg-surface rounded-xl p-4 border border-outline-variant/30 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-warning text-[24px]">pending_actions</span>
                        <div class="flex flex-col">
                            <span class="font-headline-sm font-bold text-on-surface"><?= $stats['pending_review'] ??
                                0 ?></span>
                            <span class="text-[14px] text-on-surface-variant">Pending Review</span>
                        </div>
                    </div>
                    <div class="bg-surface rounded-xl p-4 border border-outline-variant/30 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-secondary text-[24px]">bar_chart</span>
                        <div class="flex flex-col">
                            <span class="font-headline-sm font-bold text-on-surface"><?= $stats['avg_score'] ??
                                0 ?>%</span>
                            <span class="text-[14px] text-on-surface-variant">Rata-rata Skor</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pengaturan & Bantuan Teacher -->
            <section class="flex flex-col gap-4">
                <h2 class="text-[13px] font-bold text-on-surface-variant tracking-wider uppercase pl-2 mt-4">PENGATURAN & BANTUAN</h2>

                <div class="flex flex-col gap-3">

                    <!-- Profil Saya -->
                    <a href="?route=/teacher/profil/detail" class="dls-menu-card group flex items-center justify-between w-full p-4 bg-surface rounded-xl border border-outline-variant/30 shadow-sm hover:shadow-md hover:border-primary/20 hover:bg-primary/5 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-container-highest/30 group-hover:bg-primary/10 flex items-center justify-center text-on-surface-variant group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 0;">person</span>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-label-lg text-on-surface group-hover:text-primary font-bold transition-colors">Profil Saya</span>
                                <span class="font-body-sm text-on-surface-variant">Informasi pribadi Teacher, riwayat mengajar, data identitas</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary group-hover:translate-x-1 transition-all text-[20px]" style="font-variation-settings:'FILL' 0;">chevron_right</span>
                    </a>

                    <!-- Ubah Peran -->
                    <?php
                    $roles = $_SESSION['available_roles'] ?? ['student', 'teacher'];
                    if (count($roles) > 1): ?>
                    <button type="button" id="btn-ubah-peran" class="dls-menu-card group flex items-center justify-between w-full p-4 bg-surface rounded-xl border border-outline-variant/30 shadow-sm hover:bg-error/5 hover:border-error/30 hover:shadow-md transition-all text-left">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-container-highest/30 group-hover:bg-error/10 flex items-center justify-center text-on-surface-variant group-hover:text-error transition-colors">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 0;">swap_horiz</span>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-label-lg text-on-surface group-hover:text-error font-bold transition-colors">Ubah Peran</span>
                                <span class="font-body-sm text-on-surface-variant">Beralih ke tampilan Student (jika memiliki akses)</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-error group-hover:translate-x-1 transition-all text-[20px]" style="font-variation-settings:'FILL' 0;">chevron_right</span>
                    </button>
                    <?php endif;
                    ?>

                    <!-- Pengaturan -->
                    <a href="?route=/teacher/profil/pengaturan" class="dls-menu-card group flex items-center justify-between w-full p-4 bg-surface rounded-xl border border-outline-variant/30 shadow-sm hover:shadow-md hover:border-primary/20 hover:bg-primary/5 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-container-highest/30 group-hover:bg-primary/10 flex items-center justify-center text-on-surface-variant group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 0;">settings</span>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-label-lg text-on-surface group-hover:text-primary font-bold transition-colors">Pengaturan</span>
                                <span class="font-body-sm text-on-surface-variant">Keamanan akun, notifikasi pengajaran, preferensi workspace</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary group-hover:translate-x-1 transition-all text-[20px]" style="font-variation-settings:'FILL' 0;">chevron_right</span>
                    </a>

                    <!-- Bantuan -->
                    <a href="?route=/teacher/profil/bantuan" class="dls-menu-card group flex items-center justify-between w-full p-4 bg-surface rounded-xl border border-outline-variant/30 shadow-sm hover:shadow-md hover:border-primary/20 hover:bg-primary/5 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-container-highest/30 group-hover:bg-primary/10 flex items-center justify-center text-on-surface-variant group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 0;">help</span>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-label-lg text-on-surface group-hover:text-primary font-bold transition-colors">Bantuan</span>
                                <span class="font-body-sm text-on-surface-variant">Panduan pengelolaan course, FAQ teacher, Hubungi Admin</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary group-hover:translate-x-1 transition-all text-[20px]" style="font-variation-settings:'FILL' 0;">chevron_right</span>
                    </a>

                    <!-- Keluar -->
                    <button type="button" id="btn-keluar" class="dls-menu-card group flex items-center justify-between w-full p-4 bg-surface rounded-xl border border-outline-variant/30 shadow-sm hover:bg-error/5 hover:border-error/30 hover:shadow-md transition-all text-left">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-surface-container-highest/30 group-hover:bg-error/10 flex items-center justify-center text-on-surface-variant group-hover:text-error transition-colors">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 0;">logout</span>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-label-lg text-on-surface group-hover:text-error font-bold transition-colors">Keluar</span>
                                <span class="font-body-sm text-on-surface-variant group-hover:text-error/70 transition-colors">Akhiri sesi Anda di perangkat ini</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-error group-hover:translate-x-1 transition-all text-[20px]" style="font-variation-settings:'FILL' 0;">chevron_right</span>
                    </button>

                </div>
            </section>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if(document.getElementById('btn-ubah-peran')) {
                document.getElementById('btn-ubah-peran').addEventListener('click', function() {
                    Swal.fire({
                        title: 'Ubah Peran?',
                        text: 'Anda akan beralih dari tampilan Teacher ke Student. Pastikan Anda memiliki akses sebagai Student.',
                        icon: 'warning',
                        iconColor: '#ef4444',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ubah Peran',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-2xl shadow-xl',
                            confirmButton: 'rounded-lg font-semibold px-5 py-2.5',
                            cancelButton: 'rounded-lg font-semibold px-5 py-2.5',
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '?route=/pilih-peran';
                        }
                    });
                });
            }

            document.getElementById('btn-keluar').addEventListener('click', function() {
                Swal.fire({
                    title: 'Keluar dari Akun?',
                    text: 'Sesi Anda di perangkat ini akan diakhiri. Anda perlu login kembali untuk mengakses sistem.',
                    icon: 'question',
                    iconColor: '#ef4444',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl',
                        confirmButton: 'rounded-lg font-semibold px-5 py-2.5',
                        cancelButton: 'rounded-lg font-semibold px-5 py-2.5',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '?route=/auth/logout';
                    }
                });
            });
        });
    </script>
</body>
</html>
