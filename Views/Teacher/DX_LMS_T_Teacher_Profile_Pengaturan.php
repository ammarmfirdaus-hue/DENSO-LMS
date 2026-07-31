<?php
/**
 * Tampilan Pengaturan Akun - Employee Account Center
 *
 * Halaman pengaturan yang berisi: keamanan akun (ubah password, riwayat login),
 * preferensi notifikasi, tampilan, dan privasi.
 */
$active_tab = 'profil';
$p = $profile ?? [];
$lh = $loginHistory ?? [];
?>
<!DOCTYPE html>
<html lang="id" style="">

<head>
    <?php
    $page_title = 'Pengaturan - Learning Management System';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 44px);
        }

        body {
            min-height: max(884px, 100dvh);
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
        }

        body *,
        body *::before,
        body *::after {
            font-family: inherit;
        }

        /* Custom toggle */
        .toggle-track {
            width: 44px;
            height: 24px;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .toggle-track.active {
            background-color: var(--color-primary, #b4000b);
        }

        .toggle-track:not(.active) {
            background-color: var(--color-outline-variant, #e9bcb6);
        }

        .toggle-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .toggle-track.active .toggle-thumb {
            transform: translateX(20px);
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased pt-safe">
    <!-- TopAppBar -->
    <?php
    $header_title = 'Pengaturan';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Sidebar Component (Desktop) -->
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-32 pt-[72px] flex flex-col gap-5">
        <div class="max-w-[800px] w-full mx-auto flex flex-col gap-5">

            <!-- ===== SECTION 1: KEAMANAN AKUN ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">shield</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Keamanan Akun</h2>
                </div>
                <div class="p-4 flex flex-col gap-4">
                    <!-- Change Password Form -->
                    <div class="bg-surface-container/30 rounded-xl p-4">
                        <h3 class="text-[14px] font-semibold text-on-surface mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px] text-secondary">key</span>
                            Ubah Password
                        </h3>
                        <form class="flex flex-col gap-3" onsubmit="event.preventDefault(); Swal.fire({title: 'Segera Hadir!', text: 'Fitur ini akan aktif saat terhubung ke database.', icon: 'info', confirmButtonColor: '#b4000b'});">
                            <div>
                                <label class="text-[14px] text-on-surface-variant font-medium block mb-1">Password Lama</label>
                                <input type="password" placeholder="Masukkan password lama" class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-3.5 py-2.5 text-[14px] text-on-surface outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors placeholder:text-on-surface-variant/50">
                            </div>
                            <div>
                                <label class="text-[14px] text-on-surface-variant font-medium block mb-1">Password Baru</label>
                                <input type="password" placeholder="Masukkan password baru" class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-3.5 py-2.5 text-[14px] text-on-surface outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors placeholder:text-on-surface-variant/50">
                            </div>
                            <div>
                                <label class="text-[14px] text-on-surface-variant font-medium block mb-1">Konfirmasi Password Baru</label>
                                <input type="password" placeholder="Ulangi password baru" class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-xl px-3.5 py-2.5 text-[14px] text-on-surface outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors placeholder:text-on-surface-variant/50">
                            </div>
                            <button type="submit" class="w-full sm:w-auto self-end bg-primary text-on-primary font-label-md font-bold px-6 py-2.5 rounded-xl hover:bg-primary/90 transition-colors active:scale-95 shadow-sm">
                                Simpan Password
                            </button>
                        </form>
                    </div>

                    <!-- Logout All Devices -->
                    <div class="flex items-center justify-between p-3.5 bg-surface-container/30 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[14px] text-error" style="font-variation-settings:'FILL' 0;">devices</span>
                            <div>
                                <p class="text-[14px] text-on-surface font-semibold">Logout Semua Perangkat</p>
                                <p class="text-[14px] text-on-surface-variant">Keluar dari semua perangkat yang terhubung</p>
                            </div>
                        </div>
                        <button onclick="Swal.fire({title: 'Sesi Diakhiri', text: 'Semua sesi pada perangkat lain akan diakhiri.', icon: 'success', confirmButtonColor: '#b4000b'})" class="text-error font-label-sm font-bold bg-error/8 px-3 py-1.5 rounded-lg hover:bg-error/12 transition-colors active:scale-95">
                            Logout
                        </button>
                    </div>

                    <!-- Login History -->
                    <div>
                        <h3 class="text-[14px] font-semibold text-on-surface mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant">history</span>
                            Riwayat Login (5 Terakhir)
                        </h3>
                        <div class="flex flex-col gap-2">
                            <?php foreach ($lh as $login): ?>
                            <div class="flex items-start gap-3 p-3 bg-surface-container/20 rounded-xl border border-outline-variant/15 <?= $login[
                                'current'
                            ]
                                ? 'border-primary/20 bg-primary/[0.02]'
                                : '' ?>">
                                <span class="material-symbols-outlined text-[14px] mt-0.5 shrink-0 <?= $login['current']
                                    ? 'text-primary'
                                    : 'text-on-surface-variant' ?>" style="font-variation-settings:'FILL' 0;">
                                    <?= str_contains($login['device'], 'iPhone') ||
                                    str_contains($login['device'], 'Android')
                                        ? 'smartphone'
                                        : 'computer' ?>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[14px] text-on-surface font-semibold truncate"><?= e(
                                            $login['device'],
                                        ) ?></p>
                                        <?php if ($login['current']): ?>
                                        <span class="text-[9px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded-full shrink-0">SAAT INI</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[14px] text-on-surface-variant mt-0.5"><?= e(
                                        $login['time'],
                                    ) ?> · <?= e($login['location']) ?></p>
                                    <p class="text-[14px] text-on-surface-variant/60">IP: <?= e($login['ip']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== SECTION 2: PREFERENSI NOTIFIKASI ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">notifications</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Preferensi Notifikasi</h2>
                </div>
                <div class="p-4 flex flex-col gap-1">
                    <?php
                    $notifPrefs = [
                        [
                            'id' => 'notif_course',
                            'icon' => 'school',
                            'label' => 'Notifikasi Kelas/Course Baru',
                            'desc' => 'Diberitahu saat status usulan kelas di setujui Admin',
                            'default' => true,
                        ],
                        [
                            'id' => 'notif_remind',
                            'icon' => 'alarm',
                            'label' => 'Pengingat Evaluasi Tugas',
                            'desc' => 'Kewajiban periksa materi dari peserta didik',
                            'default' => true,
                        ],
                        [
                            'id' => 'notif_quiz',
                            'icon' => 'assignment_turned_in',
                            'label' => 'Submisi Materi/Assignment',
                            'desc' => 'Pemberitahuan masuk saat peserta mengunggah dokumen baru',
                            'default' => true,
                        ],
                        [
                            'id' => 'notif_system',
                            'icon' => 'campaign',
                            'label' => 'Pengumuman Sistem',
                            'desc' => 'Pemeliharaan server, update, dan info relevan pengajar',
                            'default' => true,
                        ],
                        [
                            'id' => 'notif_forum',
                            'icon' => 'forum',
                            'label' => 'Forum Diskusi Kelas',
                            'desc' => 'Tanda pertanyaan atau diskusi partisipan pada forum modul',
                            'default' => false,
                        ],
                    ];
                    foreach ($notifPrefs as $np): ?>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-surface-container/20 transition-colors">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant shrink-0" style="font-variation-settings:'FILL' 0;"><?= $np[
                                'icon'
                            ] ?></span>
                            <div class="min-w-0">
                                <p class="text-[14px] text-on-surface font-semibold"><?= $np['label'] ?></p>
                                <p class="text-[14px] text-on-surface-variant truncate"><?= $np['desc'] ?></p>
                            </div>
                        </div>
                        <div class="toggle-track <?= $np['default']
                            ? 'active'
                            : '' ?> shrink-0 ml-3" onclick="this.classList.toggle('active')" role="switch" aria-label="<?= $np[
     'label'
 ] ?>">
                            <div class="toggle-thumb"></div>
                        </div>
                    </div>
                    <?php endforeach;
                    ?>
                </div>
            </section>

            <!-- ===== SECTION 3: TAMPILAN ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">palette</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Tampilan</h2>
                </div>
                <div class="p-4 flex flex-col gap-1">
                    <!-- Language -->
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-surface-container/20 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant" style="font-variation-settings:'FILL' 0;">translate</span>
                            <div>
                                <p class="text-[14px] text-on-surface font-semibold">Bahasa Tampilan</p>
                                <p class="text-[14px] text-on-surface-variant">Pilih bahasa antarmuka sistem</p>
                            </div>
                        </div>
                        <select class="bg-surface-container border border-outline-variant/40 rounded-lg px-3 py-1.5 text-[14px] text-on-surface font-semibold outline-none focus:border-primary cursor-pointer">
                            <option selected>Bahasa Indonesia</option>
                            <option>English</option>
                        </select>
                    </div>
                    <!-- Dark Mode -->
                    <div class="flex items-center justify-between p-3 rounded-xl opacity-50">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant" style="font-variation-settings:'FILL' 0;">dark_mode</span>
                            <div>
                                <p class="text-[14px] text-on-surface font-semibold">Mode Gelap</p>
                                <p class="text-[14px] text-on-surface-variant">Segera hadir pada versi berikutnya</p>
                            </div>
                        </div>
                        <div class="toggle-track shrink-0 ml-3" role="switch" aria-disabled="true">
                            <div class="toggle-thumb"></div>
                        </div>
                    </div>
                </div>
            </section>

        </div><!-- /max-w-3xl -->
    </main>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
