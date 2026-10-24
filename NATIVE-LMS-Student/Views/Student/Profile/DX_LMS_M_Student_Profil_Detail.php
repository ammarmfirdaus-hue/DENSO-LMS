<?php
/**
 * Tampilan Detail Profil Karyawan - Employee Account Center
 *
 * Halaman lengkap yang menampilkan: informasi pribadi, informasi pekerjaan,
 * riwayat karir (timeline), learning summary, dan keamanan akun.
 * Field master (Nama, NPK, Jabatan, dll.) bersifat read-only.
 */
$active_tab = 'profil';
$p = $profile ?? [];
$ch = $careerHistory ?? [];
$gm = $golonganMap ?? [];
$ls = $learningStats ?? [];

/** Utilitas pembantu pelabelan tanggal, diamankan dari duplikasi deklarasi fungsi. */
if (!function_exists('formatDateId')) {
    function formatDateId($dateStr)
    {
        if (!$dateStr) {
            return 'Sekarang';
        }
        $months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        $ts = strtotime($dateStr);
        return date('d', $ts) . ' ' . $months[date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
}
if (!function_exists('formatMonthYear')) {
    function formatMonthYear($dateStr)
    {
        if (!$dateStr) {
            return 'Sekarang';
        }
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $ts = strtotime($dateStr);
        return $months[date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="id" style="">

<head>
    <?php
    $page_title = 'Detail Profil - Learning Management System';
    include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
        }

        body *,
        body *::before,
        body *::after {
            font-family: inherit;
        }

        .timeline-line {
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--color-outline-variant, #e9bcb6) 0%, transparent 100%);
        }

        .read-only-field {
            position: relative;
        }

        .read-only-field::after {
            content: '';
            position: absolute;
            inset: 0;
            cursor: not-allowed;
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased pt-safe">
    <!-- TopAppBar -->
    <?php
    $header_title = 'Detail Profil';
    $show_back = true;
    $back_url = '?route=/student/profil';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Bottom Nav -->
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 px-container-margin py-5 flex flex-col gap-5 pb-32 mt-14 lg:ml-[240px] lg:w-[calc(100%-240px)]">

        <!-- Max width container for larger screens -->
        <div class="max-w-[900px] w-full mx-auto flex flex-col gap-5">

            <!-- ===== SECTION 1: PROFILE HEADER ===== -->
            <section class="relative bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_4px_24px_0_rgba(0,0,0,0.06)] border border-outline-variant/40">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -right-20 -top-20 w-56 h-56 bg-primary/5 rounded-full blur-3xl"></div>
                    <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-secondary/5 rounded-full blur-2xl"></div>
                </div>
                <div class="relative z-10 p-6 flex flex-col items-center text-center">
                    <!-- Photo with edit button -->
                    <div class="relative mb-4">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-[3px] border-surface-container-high shadow-lg">
                            <img alt="<?= e($p['name'] ?? '') ?>" class="w-full h-full object-cover" src="<?= e(
    $p['photo_url'] ?? '',
) ?>">
                        </div>
                        <button class="absolute bottom-0 right-0 w-8 h-8 bg-primary text-on-primary rounded-full flex items-center justify-center shadow-md hover:bg-primary/90 transition-colors active:scale-95" title="Ubah Foto Profil">
                            <span class="material-symbols-outlined text-[16px]">photo_camera</span>
                        </button>
                    </div>
                    <h1 class="font-headline-md text-on-surface font-bold tracking-tight"><?= e(
                        $p['name'] ?? '',
                    ) ?></h1>
                    <p class="font-body-md text-on-surface-variant mt-0.5">NPK: <?= e($p['npk'] ?? '') ?></p>
                    <div class="flex items-center gap-2 mt-3 flex-wrap justify-center">
                        <span class="inline-flex items-center gap-1 bg-secondary-container/20 text-secondary px-3 py-1 rounded-full font-label-sm font-semibold">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">badge</span>
                            <?= e($p['role'] ?? 'Student') ?>
                        </span>
                        <span class="inline-flex items-center gap-1 bg-tertiary/10 text-tertiary px-3 py-1 rounded-full font-label-sm font-semibold">
                            <span class="material-symbols-outlined text-[12px]" style="font-variation-settings:'FILL' 1;">verified</span>
                            <?= e($p['status'] ?? 'Aktif') ?>
                        </span>
                    </div>
                    <p class="font-label-sm text-on-surface-variant mt-3">
                        <?= e($p['position'] ?? '') ?> · <?= e($p['golongan'] ?? '') ?> - <?= e(
     $p['golongan_label'] ?? '',
 ) ?>
                    </p>
                </div>
            </section>

            <!-- ===== SECTION 2: INFORMASI PRIBADI ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings:'FILL' 1;">person</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[15px]">Informasi Pribadi</h2>
                </div>
                <div class="p-4 flex flex-col gap-4">
                    <?php
                    $personalFields = [
                        ['label' => 'Nama Lengkap', 'value' => $p['name'] ?? '', 'icon' => 'id_card', 'locked' => true],
                        ['label' => 'NPK', 'value' => $p['npk'] ?? '', 'icon' => 'tag', 'locked' => true],
                        [
                            'label' => 'Email Perusahaan',
                            'value' => $p['email'] ?? '',
                            'icon' => 'mail',
                            'locked' => true,
                        ],
                        ['label' => 'No. Telepon', 'value' => $p['phone'] ?? '-', 'icon' => 'phone', 'locked' => false],
                        [
                            'label' => 'Tanggal Bergabung',
                            'value' => formatDateId($p['join_date'] ?? ''),
                            'icon' => 'calendar_month',
                            'locked' => true,
                        ],
                    ];
                    foreach ($personalFields as $f): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant" style="font-variation-settings:'FILL' 0;"><?= $f[
                                'icon'
                            ] ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] text-on-surface-variant font-medium uppercase tracking-wider mb-0.5"><?= $f[
                                'label'
                            ] ?></p>
                            <div class="flex items-center gap-2">
                                <p class="text-on-surface font-semibold text-[14px] truncate"><?= e($f['value']) ?></p>
                                <?php if ($f['locked']): ?>
                                <span class="material-symbols-outlined text-[14px] text-on-surface-variant/40" title="Dikelola oleh Admin" style="font-variation-settings:'FILL' 1;">lock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;
                    ?>

                    <!-- Admin notice -->
                    <div class="flex items-start gap-2.5 bg-surface-container/50 rounded-xl p-3 mt-1">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant/60 mt-0.5 shrink-0">info</span>
                        <p class="text-[12px] text-on-surface-variant leading-relaxed">
                            Data bertanda <span class="material-symbols-outlined text-[12px] align-middle">lock</span> dikelola oleh Admin HR.
                            Untuk perubahan data, silakan <a href="?route=/student/profil/bantuan" class="text-primary font-semibold hover:underline">hubungi Admin</a>
                            atau ajukan permintaan koreksi melalui menu Bantuan.
                        </p>
                    </div>
                </div>
            </section>

            <!-- ===== SECTION 3: INFORMASI PEKERJAAN ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings:'FILL' 1;">work</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[15px]">Informasi Pekerjaan</h2>
                </div>
                <div class="p-4 flex flex-col gap-4">
                    <?php
                    $jobFields = [
                        ['label' => 'Jabatan', 'value' => $p['position'] ?? '', 'icon' => 'work'],
                        [
                            'label' => 'Golongan',
                            'value' => ($p['golongan'] ?? '') . ' - ' . ($p['golongan_label'] ?? ''),
                            'icon' => 'military_tech',
                        ],
                        ['label' => 'Departemen', 'value' => $p['department'] ?? '', 'icon' => 'apartment'],
                        ['label' => 'Divisi', 'value' => $p['division'] ?? '', 'icon' => 'domain'],
                        ['label' => 'Seksi', 'value' => $p['section'] ?? '', 'icon' => 'schema'],
                        [
                            'label' => 'Atasan Langsung',
                            'value' => $p['direct_supervisor'] ?? '',
                            'icon' => 'supervisor_account',
                        ],
                        ['label' => 'Lokasi Kerja', 'value' => $p['work_location'] ?? '', 'icon' => 'location_on'],
                    ];
                    foreach ($jobFields as $f): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant" style="font-variation-settings:'FILL' 0;"><?= $f[
                                'icon'
                            ] ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] text-on-surface-variant font-medium uppercase tracking-wider mb-0.5"><?= $f[
                                'label'
                            ] ?></p>
                            <div class="flex items-center gap-2">
                                <p class="text-on-surface font-semibold text-[14px] truncate"><?= e($f['value']) ?></p>
                                <span class="material-symbols-outlined text-[14px] text-on-surface-variant/40" title="Dikelola oleh Admin" style="font-variation-settings:'FILL' 1;">lock</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;
                    ?>

                    <!-- Golongan hierarchy for current position -->
                    <?php $currentPositionGolongan = $gm[$p['position'] ?? ''] ?? []; ?>
                    <?php if (!empty($currentPositionGolongan)): ?>
                    <div class="mt-2 bg-surface-container/40 rounded-xl p-4">
                        <p class="text-[12px] text-on-surface-variant font-semibold uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">stairs</span>
                            Struktur Golongan - <?= e($p['position'] ?? '') ?>
                        </p>
                        <div class="flex flex-col gap-2">
                            <?php foreach ($currentPositionGolongan as $gol):
                                $isCurrentGol = $gol['label'] === ($p['golongan'] ?? ''); ?>
                            <div class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $isCurrentGol
                                ? 'bg-primary/8 border border-primary/20'
                                : 'bg-surface-container-lowest border border-outline-variant/20' ?>">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 <?= $isCurrentGol
                                    ? 'bg-primary text-on-primary'
                                    : 'bg-surface-container text-on-surface-variant' ?>">
                                    <?php if ($isCurrentGol): ?>
                                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">check</span>
                                    <?php else: ?>
                                    <span class="text-[10px] font-bold"><?= substr($gol['label'], -1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold <?= $isCurrentGol
                                        ? 'text-primary'
                                        : 'text-on-surface' ?>"><?= e($gol['label']) ?></p>
                                    <p class="text-[11px] <?= $isCurrentGol
                                        ? 'text-primary/70'
                                        : 'text-on-surface-variant' ?>"><?= e($gol['name']) ?></p>
                                </div>
                                <?php if ($isCurrentGol): ?>
                                <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">SAAT INI</span>
                                <?php endif; ?>
                            </div>
                            <?php
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ===== SECTION 4: RIWAYAT KARIR (TIMELINE) ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings:'FILL' 1;">timeline</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[15px]">Riwayat Karir</h2>
                </div>
                <div class="p-4">
                    <div class="relative">
                        <?php foreach ($ch as $idx => $entry):

                            $isFirst = $idx === 0;
                            $isLast = $idx === count($ch) - 1;

                            /** Definisi palet gaya dan tampilan visual. */
                            $typeConfig = [
                                'promosi' => [
                                    'icon' => 'trending_up',
                                    'color' => 'bg-tertiary text-white',
                                    'badge' => 'Promosi',
                                    'badgeColor' => 'bg-tertiary/10 text-tertiary',
                                ],
                                'mutasi' => [
                                    'icon' => 'swap_horiz',
                                    'color' => 'bg-secondary text-white',
                                    'badge' => 'Mutasi',
                                    'badgeColor' => 'bg-secondary/10 text-secondary',
                                ],
                                'penempatan_awal' => [
                                    'icon' => 'flag',
                                    'color' => 'bg-on-surface-variant text-white',
                                    'badge' => 'Penempatan Awal',
                                    'badgeColor' => 'bg-surface-container text-on-surface-variant',
                                ],
                            ];
                            $tc = $typeConfig[$entry['type']] ?? $typeConfig['penempatan_awal'];
                            ?>
                        <div class="relative flex gap-4 <?= !$isLast ? 'pb-6' : '' ?>">
                            <!-- Timeline line -->
                            <?php if (!$isLast): ?>
                            <div class="absolute left-[19px] top-[40px] bottom-0 w-[2px] bg-outline-variant/40"></div>
                            <?php endif; ?>

                            <!-- Timeline dot -->
                            <div class="w-10 h-10 rounded-full <?= $tc[
                                'color'
                            ] ?> flex items-center justify-center shrink-0 z-10 shadow-sm <?= $isFirst
     ? 'ring-4 ring-primary/10'
     : '' ?>">
                                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;"><?= $tc[
                                    'icon'
                                ] ?></span>
                            </div>

                            <!-- Timeline content -->
                            <div class="flex-1 min-w-0 bg-surface-container/30 rounded-xl p-3.5 border border-outline-variant/20 <?= $isFirst
                                ? 'border-primary/20 bg-primary/[0.02]'
                                : '' ?>">
                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                    <h3 class="font-semibold text-[14px] text-on-surface"><?= e(
                                        $entry['position'],
                                    ) ?> - <?= e($entry['golongan']) ?></h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 <?= $tc[
                                        'badgeColor'
                                    ] ?>"><?= $tc['badge'] ?></span>
                                </div>
                                <p class="text-[12px] text-on-surface-variant flex items-center gap-1 mb-1">
                                    <span class="material-symbols-outlined text-[14px]">apartment</span>
                                    <?= e($entry['department']) ?> · <?= e($entry['division']) ?> · <?= e(
     $entry['section'],
 ) ?>
                                </p>
                                <p class="text-[12px] text-on-surface-variant flex items-center gap-1 mb-2">
                                    <span class="material-symbols-outlined text-[14px]">date_range</span>
                                    <?= formatMonthYear($entry['start_date']) ?> - <?= $entry['end_date']
     ? formatMonthYear($entry['end_date'])
     : '<span class="text-primary font-semibold">Sekarang</span>' ?>
                                </p>
                                <?php if (!empty($entry['description'])): ?>
                                <p class="text-[12px] text-on-surface-variant/80 leading-relaxed italic"><?= e(
                                    $entry['description'],
                                ) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- ===== SECTION 5: LEARNING SUMMARY ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings:'FILL' 1;">school</span>
                        <h2 class="font-headline-sm text-on-surface font-bold text-[15px]">Learning Summary</h2>
                    </div>
                    <a href="?route=/student/riwayat" class="font-label-sm text-primary font-semibold hover:underline flex items-center gap-1">
                        Riwayat <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <?php
                        $learnItems = [
                            [
                                'icon' => 'auto_stories',
                                'label' => 'Aktif',
                                'value' => $ls['active_count'] ?? 0,
                                'color' => 'text-primary',
                            ],
                            [
                                'icon' => 'task_alt',
                                'label' => 'Selesai',
                                'value' => $ls['completed_count'] ?? 0,
                                'color' => 'text-tertiary',
                            ],
                            [
                                'icon' => 'analytics',
                                'label' => 'Skor',
                                'value' => ($ls['avg_score'] ?? 0) . '%',
                                'color' => 'text-secondary',
                            ],
                        ];
                        foreach ($learnItems as $li): ?>
                        <div class="text-center p-3 bg-surface-container/40 rounded-xl">
                            <span class="material-symbols-outlined text-[24px] <?= $li[
                                'color'
                            ] ?> mb-1" style="font-variation-settings:'FILL' 1;"><?= $li['icon'] ?></span>
                            <p class="font-bold text-[18px] text-on-surface"><?= $li['value'] ?></p>
                            <p class="text-[11px] text-on-surface-variant"><?= $li['label'] ?></p>
                        </div>
                        <?php endforeach;
                        ?>
                    </div>

                    <?php if (($ls['mandatory_incomplete'] ?? 0) > 0): ?>
                    <div class="flex items-center gap-3 bg-error/[0.04] border border-error/15 rounded-xl p-3">
                        <span class="material-symbols-outlined text-error text-[20px]" style="font-variation-settings:'FILL' 1;">warning</span>
                        <div class="flex-1">
                            <p class="text-[13px] text-on-surface font-semibold"><?= $ls[
                                'mandatory_incomplete'
                            ] ?> Course Wajib Belum Selesai</p>
                            <p class="text-[11px] text-on-surface-variant">Segera selesaikan untuk memenuhi target pembelajaran Anda.</p>
                        </div>
                        <a href="?route=/student/courses" class="shrink-0 text-primary font-label-sm font-bold hover:underline">Lihat</a>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center gap-2 mt-3 text-[12px] text-on-surface-variant">
                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                        Total Jam Belajar: <span class="font-semibold text-on-surface"><?= $ls['total_study_hours'] ??
                            0 ?> jam</span>
                    </div>
                </div>
            </section>

            <!-- ===== SECTION 6: KEAMANAN AKUN ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings:'FILL' 1;">shield</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[15px]">Keamanan Akun</h2>
                </div>
                <div class="p-4 flex flex-col gap-3">
                    <a href="?route=/student/profil/pengaturan" class="flex items-center justify-between p-3 bg-surface-container/30 rounded-xl hover:bg-surface-container/50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-secondary" style="font-variation-settings:'FILL' 0;">key</span>
                            <div>
                                <p class="text-[13px] text-on-surface font-semibold">Ubah Password</p>
                                <p class="text-[11px] text-on-surface-variant">Perbarui password akun Anda secara berkala</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant/60 group-hover:text-primary transition-colors">chevron_right</span>
                    </a>

                    <button class="flex items-center justify-between w-full p-3 bg-surface-container/30 rounded-xl hover:bg-surface-container/50 transition-colors group text-left">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-on-surface-variant" style="font-variation-settings:'FILL' 0;">contact_support</span>
                            <div>
                                <p class="text-[13px] text-on-surface font-semibold">Hubungi Admin</p>
                                <p class="text-[11px] text-on-surface-variant">Ajukan perubahan data atau koreksi informasi</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant/60 group-hover:text-primary transition-colors">chevron_right</span>
                    </button>
                </div>
            </section>

        </div><!-- /max-w-3xl -->
    </main>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
