<?php
/**
 * Tampilan Bantuan - Employee Account Center
 *
 * Halaman bantuan yang berisi: pencarian FAQ, kategori bantuan,
 * FAQ accordion, panduan penggunaan, dan kontak admin.
 */
$active_tab = 'profil'; ?>
<!DOCTYPE html>
<html lang="id" style="">

<head>
    <?php
    $page_title = 'Bantuan - Learning Management System';
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

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .faq-answer.open {
            max-height: 500px;
        }

        .faq-chevron {
            transition: transform 0.3s ease;
        }

        .faq-chevron.open {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="dls-app-shell bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased pt-safe">
    <!-- TopAppBar -->
    <?php
    $header_title = 'Bantuan';
    $show_back = true;
    $back_url = '?route=/student/profil';
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include APP_ROOT . '/includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Bottom Nav -->
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 px-container-margin py-5 flex flex-col gap-5 pb-32 mt-14 lg:ml-[240px] lg:w-[calc(100%-240px)]">
        <div class="max-w-[800px] w-full mx-auto flex flex-col gap-5">

            <!-- ===== HEADER ===== -->
            <section class="relative bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/30">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -right-16 -top-16 w-48 h-48 bg-secondary/5 rounded-full blur-3xl"></div>
                </div>
                <div class="relative z-10 p-5 sm:p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary shrink-0">
                            <span class="material-symbols-outlined text-[28px]" style="font-variation-settings:'FILL' 1;">support_agent</span>
                        </div>
                        <div>
                            <h1 class="font-headline-md text-on-surface font-bold tracking-tight">Pusat Bantuan</h1>
                            <p class="font-body-md text-on-surface-variant text-[14px]">Temukan jawaban dan panduan penggunaan LMS</p>
                        </div>
                    </div>
                    <!-- Search -->
                    <div class="relative mt-3">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[14px]">search</span>
                        <input type="text" id="faqSearch" placeholder="Cari bantuan atau FAQ..." class="w-full bg-surface-container border border-outline-variant/40 pl-10 pr-4 py-3 rounded-2xl text-[14px] text-on-surface outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors placeholder:text-on-surface-variant/50">
                    </div>
                </div>
            </section>

            <!-- ===== SECTION 2: KATEGORI BANTUAN (QUICK LINKS) ===== -->
            <section>
                <h2 class="font-label-md text-on-surface-variant font-semibold uppercase tracking-wider text-[14px] mb-3 px-1">Kategori Bantuan</h2>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                    <?php
                    $categories = [
                        ['icon' => 'school', 'label' => 'Memulai Course', 'color' => 'text-primary bg-primary/8'],
                        [
                            'icon' => 'assignment',
                            'label' => 'Quiz & Assessment',
                            'color' => 'text-secondary bg-secondary/8',
                        ],
                        [
                            'icon' => 'workspace_premium',
                            'label' => 'Sertifikat',
                            'color' => 'text-tertiary bg-tertiary/8',
                        ],
                        ['icon' => 'key', 'label' => 'Akun & Keamanan', 'color' => 'text-error bg-error/8'],
                        [
                            'icon' => 'forum',
                            'label' => 'Forum Diskusi',
                            'color' => 'text-on-surface-variant bg-surface-container',
                        ],
                        ['icon' => 'settings', 'label' => 'Pengaturan', 'color' => 'text-secondary bg-secondary/8'],
                    ];
                    foreach ($categories as $cat): ?>
                    <button class="faq-category flex flex-col items-center gap-2 p-3 bg-surface-container-lowest rounded-xl border border-outline-variant/25 hover:border-primary/30 hover:shadow-sm transition-all active:scale-95 group" data-category="<?= strtolower(
                        str_replace(' ', '_', $cat['label']),
                    ) ?>">
                        <div class="w-10 h-10 rounded-lg <?= $cat[
                            'color'
                        ] ?> flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;"><?= $cat[
                                'icon'
                            ] ?></span>
                        </div>
                        <span class="text-[14px] text-on-surface font-medium text-center leading-tight"><?= $cat[
                            'label'
                        ] ?></span>
                    </button>
                    <?php endforeach;
                    ?>
                </div>
            </section>

            <!-- ===== SECTION 3: FAQ (ACCORDION) ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">quiz</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Pertanyaan yang Sering Diajukan</h2>
                </div>
                <div class="faq-list">
                    <?php
                    $faqs = [
                        [
                            'q' => 'Bagaimana cara memulai course baru?',
                            'a' =>
                                'Buka menu <strong>Course</strong>, pilih course yang tersedia, lalu buka sesi dan materi yang ingin dipelajari. Kemajuan course ditampilkan pada halaman Course.',
                            'cat' => 'memulai_course',
                        ],
                        [
                            'q' => 'Bagaimana cara mengubah password?',
                            'a' =>
                                'Akun peserta memakai login terpusat DENSO. Perubahan password dilakukan melalui portal identitas perusahaan atau dengan bantuan Admin LMS.',
                            'cat' => 'akun_&_keamanan',
                        ],
                        [
                            'q' => 'Bagaimana cara mengerjakan quiz?',
                            'a' =>
                                'Setelah menyelesaikan seluruh materi pada suatu sesi, tombol <strong>"Post-Test"</strong> akan muncul. Tekan tombol tersebut untuk memulai quiz. Baca setiap pertanyaan dengan seksama sebelum menjawab. Anda dapat melihat hasil setelah selesai.',
                            'cat' => 'quiz_&_assessment',
                        ],
                        [
                            'q' => 'Apa itu Final Assessment?',
                            'a' =>
                                'Final Assessment adalah evaluasi akhir dari sebuah course. Berbeda dengan quiz sesi, assessment ini mencakup seluruh materi dari semua sesi. Anda harus menyelesaikan seluruh sesi terlebih dahulu sebelum dapat mengerjakan Final Assessment.',
                            'cat' => 'quiz_&_assessment',
                        ],
                        [
                            'q' => 'Bagaimana cara melihat sertifikat?',
                            'a' =>
                                'Setelah menyelesaikan course dan lulus Final Assessment, sertifikat akan tersedia di halaman <strong>Riwayat</strong>. Tekan tombol <strong>"Lihat Sertifikat"</strong> pada detail course yang telah selesai. Fitur sertifikat akan aktif sepenuhnya pada versi mendatang.',
                            'cat' => 'sertifikat',
                        ],
                        [
                            'q' => 'Bagaimana cara membuka forum diskusi?',
                            'a' =>
                                'Buka tab <strong>Forum</strong> pada detail course untuk melihat diskusi yang tersedia. Pengiriman topik dan balasan belum aktif pada portal peserta.',
                            'cat' => 'forum_diskusi',
                        ],
                        [
                            'q' => 'Bagaimana cara menghubungi Admin?',
                            'a' =>
                                'Hubungi Admin LMS melalui kanal komunikasi internal resmi perusahaan. Detail kontak dikelola oleh perusahaan.',
                            'cat' => 'akun_&_keamanan',
                        ],
                        [
                            'q' => 'Bagaimana cara melaporkan masalah teknis?',
                            'a' =>
                                'Catat halaman, waktu, dan langkah yang memicu masalah, lalu kirimkan informasinya melalui kanal bantuan internal perusahaan.',
                            'cat' => 'pengaturan',
                        ],
                        [
                            'q' => 'Apakah data Nama dan NPK bisa diubah sendiri?',
                            'a' =>
                                'Tidak. Data <strong>Nama</strong> dan <strong>NPK</strong> adalah data master yang dikelola oleh Admin HR. Jika terdapat kesalahan, silakan ajukan perubahan melalui menu Bantuan atau hubungi Admin secara langsung.',
                            'cat' => 'akun_&_keamanan',
                        ],
                        [
                            'q' => 'Bagaimana cara melihat riwayat belajar?',
                            'a' =>
                                'Tekan menu <strong>Riwayat</strong> di navigasi bawah. Di sana Anda dapat melihat semua course yang sudah selesai, termasuk skor, tanggal selesai, dan detail sesi yang telah dipelajari.',
                            'cat' => 'memulai_course',
                        ],
                    ];
                    foreach ($faqs as $idx => $faq): ?>
                    <div class="faq-item border-b border-outline-variant/15 last:border-b-0" data-category="<?= $faq[
                        'cat'
                    ] ?>">
                        <button class="faq-toggle w-full flex items-center justify-between p-4 text-left hover:bg-surface-container/20 transition-colors group" onclick="toggleFaq(this)">
                            <span class="text-[14px] text-on-surface font-semibold pr-4 group-hover:text-primary transition-colors"><?= $faq[
                                'q'
                            ] ?></span>
                            <span class="material-symbols-outlined text-on-surface-variant/60 faq-chevron shrink-0 text-[14px]">expand_more</span>
                        </button>
                        <div class="faq-answer">
                            <div class="px-4 pb-4 text-[14px] text-on-surface-variant leading-relaxed">
                                <?= $faq['a'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;
                    ?>
                </div>
            </section>

            <!-- ===== SECTION 4: PANDUAN PENGGUNAAN ===== -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">menu_book</span>
                    <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Panduan Penggunaan</h2>
                </div>
                <div class="p-4 flex flex-col gap-2.5">
                    <?php
                    $guides = [
                        [
                            'icon' => 'auto_stories',
                            'title' => 'Panduan Lengkap LMS',
                            'desc' =>
                                'Pelajari semua fitur dan alur penggunaan Learning Management System secara menyeluruh.',
                        ],
                        [
                            'icon' => 'play_circle',
                            'title' => 'Cara Memulai & Menyelesaikan Course',
                            'desc' => 'Langkah demi langkah mulai dari mendaftar course hingga mendapatkan sertifikat.',
                        ],
                        [
                            'icon' => 'assignment',
                            'title' => 'Cara Mengerjakan Quiz & Final Assessment',
                            'desc' => 'Pahami jenis soal, batas waktu, dan tips mengerjakan evaluasi dengan optimal.',
                        ],
                        [
                            'icon' => 'forum',
                            'title' => 'Cara Menggunakan Forum Diskusi',
                            'desc' => 'Berdiskusi, bertanya, dan berbagi pengetahuan dengan sesama peserta learning.',
                        ],
                    ];
                    foreach ($guides as $g): ?>
                    <div class="flex items-start gap-3 p-3.5 bg-surface-container/20 rounded-xl border border-outline-variant/15 hover:border-primary/20 hover:bg-primary/[0.02] transition-all cursor-pointer group">
                        <div class="w-10 h-10 rounded-lg bg-primary/8 flex items-center justify-center text-primary shrink-0 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;"><?= $g[
                                'icon'
                            ] ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] text-on-surface font-semibold group-hover:text-primary transition-colors"><?= $g[
                                'title'
                            ] ?></p>
                            <p class="text-[14px] text-on-surface-variant mt-0.5 leading-relaxed"><?= $g['desc'] ?></p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant/40 group-hover:text-primary transition-colors shrink-0 mt-1">chevron_right</span>
                    </div>
                    <?php endforeach;
                    ?>
                </div>
            </section>

            <!-- ===== SECTION 5: KONTAK & BANTUAN LANJUTAN ===== -->
            <section class="flex flex-col gap-4">
                <!-- Hubungi Admin -->
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                    <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">contact_phone</span>
                        <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Hubungi Admin</h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="flex items-center gap-3 p-3.5 bg-surface-container/30 rounded-xl">
                                <span class="material-symbols-outlined text-[22px] text-secondary" style="font-variation-settings:'FILL' 1;">mail</span>
                                <div>
                                    <p class="text-[14px] text-on-surface-variant font-medium">Kanal bantuan</p>
                                    <p class="text-[14px] text-on-surface font-semibold">Admin melalui kanal internal perusahaan</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3.5 bg-surface-container/30 rounded-xl">
                                <span class="material-symbols-outlined text-[22px] text-secondary" style="font-variation-settings:'FILL' 1;">call</span>
                                <div>
                                <p class="text-[14px] text-on-surface-variant font-medium">Informasi kontak</p>
                                <p class="text-[14px] text-on-surface font-semibold">Mengikuti kebijakan perusahaan</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-[14px] text-on-surface-variant mt-3">Gunakan kontak dan jam layanan resmi yang berlaku di perusahaan Anda.</p>
                    </div>
                </div>

                <!-- Laporkan Masalah -->
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                    <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">bug_report</span>
                        <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Laporkan Masalah</h2>
                    </div>
                    <div class="p-4">
                        <div class="dls-feature-note" role="status">Form tiket belum tersambung ke sistem bantuan. Hubungi Admin LMS melalui kanal internal resmi perusahaan.</div>
                    </div>
                </div>

                <!-- Riwayat Tiket -->
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
                    <div class="p-4 border-b border-outline-variant/20 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">receipt_long</span>
                        <h2 class="font-headline-sm text-on-surface font-bold text-[14px]">Riwayat Tiket Bantuan</h2>
                    </div>
                    <div class="p-4 flex flex-col gap-2.5">
                        <?php
                        $tickets = [];
                        ?>
                        <?php if (empty($tickets)): ?><p class="dls-feature-note">Belum ada tiket bantuan.</p><?php endif; ?>
                        <?php foreach ($tickets as $t): ?>
                        <div class="flex items-center gap-3 p-3 bg-surface-container/20 rounded-xl border border-outline-variant/15">
                            <span class="material-symbols-outlined text-[14px] text-on-surface-variant shrink-0" style="font-variation-settings:'FILL' 0;">confirmation_number</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="text-[14px] text-on-surface font-semibold truncate"><?= $t['title'] ?></p>
                                </div>
                                <p class="text-[14px] text-on-surface-variant"><?= $t['id'] ?> · <?= $t['date'] ?></p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 <?= $t[
                                'statusColor'
                            ] ?>"><?= $t['status'] ?></span>
                        </div>
                        <?php endforeach;
                        ?>
                    </div>
                </div>
            </section>

        </div><!-- /max-w-3xl -->
    </main>

    <script>
        /** Komponen antarmuka tanya-jawab yang dapat diperluas. */
        function toggleFaq(btn) {
            const item = btn.closest('.faq-item');
            const answer = item.querySelector('.faq-answer');
            const chevron = btn.querySelector('.faq-chevron');

            /** Menutup jendela informasi lainnya yang sedang terbuka. */
            document.querySelectorAll('.faq-item').forEach(fi => {
                if (fi !== item) {
                    fi.querySelector('.faq-answer')?.classList.remove('open');
                    fi.querySelector('.faq-chevron')?.classList.remove('open');
                }
            });

            answer.classList.toggle('open');
            chevron.classList.toggle('open');
        }

        /** Fitur penelusuran spesifik untuk topik panduan (FAQ). */
        document.getElementById('faqSearch')?.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('.faq-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
        });

        /** Mekanisme untuk menyaring direktori berdasarkan pengelompokan kategori. */
        document.querySelectorAll('.faq-category').forEach(btn => {
            btn.addEventListener('click', function() {
                const cat = this.dataset.category;

                /** Merubah mode antarmuka elemen aktif. */
                document.querySelectorAll('.faq-category').forEach(b => b.classList.remove('ring-2', 'ring-primary'));
                this.classList.add('ring-2', 'ring-primary');

                document.querySelectorAll('.faq-item').forEach(item => {
                    item.style.display = (item.dataset.category === cat) ? '' : 'none';
                });

                /** Mengarahkan kursor halaman secara otomatis ke segmen informasi. */
                document.querySelector('.faq-list')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            });
        });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
