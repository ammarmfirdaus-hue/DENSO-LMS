<?php
/**
 * DLS Forum Thread Detail View — Social Media Style (X / Threads inspired)
 *
 * Layout mengikuti pola feed media sosial modern:
 * - Avatar di kiri, konten di kanan
 * - Thread line vertikal menghubungkan postingan
 * - Interaksi inline (like, reply) di bawah konten
 * - Composer sticky di bagian bawah
 */
$pageTitle = 'Detail Diskusi - LMS';
include 'includes/components/DX_LMS_M_Tailwind_Head.php';
?>
<style>
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom, 20px);
    }

    .pt-safe {
        padding-top: env(safe-area-inset-top, 44px);
    }



    /* Animations */
    .fade-in {
        animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: translateY(8px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Thread line */
    .thread-line {
        position: absolute;
        left: 19px;
        top: 48px;
        bottom: 0;
        width: 2px;
        background: var(--thread-line-color, #e5e2e1);
    }

    @media (min-width: 768px) {
        .thread-line {
            left: 23px;
        }
    }

    /* Interaction button hover circle effect (like X/Twitter) */
    .interact-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 500;
        color: #5e3f3b;
        transition: all 0.15s ease;
        cursor: pointer;
        border: none;
        background: none;
    }

    .interact-btn:hover {
        background: rgba(0, 0, 0, 0.04);
    }

    .interact-btn.like:hover {
        color: #e20613;
        background: rgba(226, 6, 19, 0.06);
    }

    .interact-btn.like:hover .material-symbols-outlined {
        color: #e20613;
    }

    .interact-btn.like.active {
        color: #e20613;
    }

    .interact-btn.like.active .material-symbols-outlined {
        font-variation-settings: 'FILL' 1;
        color: #e20613;
    }

    .interact-btn.reply:hover {
        color: #4355b9;
        background: rgba(67, 85, 185, 0.06);
    }

    .interact-btn.reply:hover .material-symbols-outlined {
        color: #4355b9;
    }

    .interact-btn.share:hover {
        color: #00671a;
        background: rgba(0, 103, 26, 0.06);
    }

    .interact-btn.share:hover .material-symbols-outlined {
        color: #00671a;
    }

    .interact-btn .material-symbols-outlined {
        font-size: 18px;
        transition: color 0.15s ease;
    }

    /* Composer scrollbar */
    .composer-area::-webkit-scrollbar {
        width: 4px;
    }

    .composer-area::-webkit-scrollbar-track {
        background: transparent;
    }

    .composer-area::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.08);
        border-radius: 10px;
    }

    /* Attachment menu animation */
    .attach-menu {
        transform-origin: bottom left;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .attach-menu.show {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    .attach-menu.hide {
        opacity: 0;
        transform: translateY(6px) scale(0.96);
        pointer-events: none;
    }

    /* File preview chip */
    .file-chip {
        animation: fadeIn 0.25s ease forwards;
    }
</style>
</head>

<body class="dls-app-shell bg-surface-container-lowest text-on-surface antialiased selection:bg-secondary-container selection:text-on-secondary-container min-h-screen flex flex-col">

    <!-- TopAppBar -->
    <?php
    $header_title = 'Detail Diskusi';
    $show_back = true;
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- 2-Column Layout Wrapper -->
    <div class="flex flex-col lg:flex-row items-start lg:ml-[240px] pt-2 md:pt-4 lg:pl-8 lg:pr-6 xl:pl-12 gap-8 lg:gap-10 xl:gap-16 w-full lg:w-[calc(100%-240px)]" style="margin-top: 56px;">

        <!-- Feed Container (Left) -->
        <main class="flex-1 w-full max-w-[680px] mx-auto lg:mx-0 px-0 md:px-0 flex flex-col fade-in <?= !$isRiwayat
            ? 'pb-[160px] lg:pb-[140px]'
            : 'pb-12' ?>"> <?php if ($isRiwayat): ?>
            <!-- Archived Banner -->
            <div class="mx-4 md:mx-0 mb-4 bg-error-container/10 border border-error/15 rounded-2xl p-4 flex items-center gap-3">
                <span class="material-symbols-outlined text-error text-[20px]">lock</span>
                <p class="font-body-md text-on-surface-variant leading-snug flex-1">
                    Diskusi ini telah diarsipkan dan bersifat <strong class="text-on-surface">read-only</strong>.
                </p>
            </div>
            <?php endif; ?>

            <!-- ============================================ -->
            <!-- ORIGINAL POST (OP) -->
            <!-- ============================================ -->
            <article class="relative px-4 md:px-6 pt-2 pb-3">
                <!-- Header: Avatar + Info -->
                <div class="flex items-center gap-3 mb-3">
                    <!-- Avatar -->
                    <div class="w-11 h-11 md:w-12 md:h-12 rounded-full bg-surface-container-high text-on-surface font-bold text-[16px] md:text-[18px] flex items-center justify-center border border-outline-variant/40 shrink-0">
                        B
                    </div>
                    <!-- Info -->
                    <div class="flex flex-col">
                        <span class="font-bold text-on-surface text-[15px] md:text-[16px] leading-tight flex items-center gap-1.5">
                            Budi Santoso
                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-primary/10 text-primary font-bold border border-primary/20 tracking-wide">AUTHOR</span>
                        </span>
                        <span class="text-on-surface-variant text-[13px] leading-tight mt-0.5">Operator Produksi · Line A</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="w-full">
                    <!-- Topic Title -->
                    <h1 class="font-bold text-on-surface text-[18px] md:text-[22px] leading-snug mb-3">
                        Bagaimana penerapan 5S yang efektif di area perakitan akhir?
                    </h1>

                    <!-- Body -->
                    <div class="text-on-surface text-[15px] md:text-[16px] leading-relaxed space-y-3 mb-4">
                        <p>Saya sering menemukan beberapa alat kerja tidak dikembalikan ke tempat asalnya (Seiton) setelah pergantian shift.</p>
                        <p>Apakah ada tips atau metode visual management yang lebih baik agar semua anggota tim lebih disiplin menerapkan standar 5S? Karena ini juga berkaitan dengan safety dan efisiensi waktu kerja kita.</p>
                    </div>

                    <!-- Tags -->
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="px-2.5 py-1 bg-surface-container text-on-surface-variant text-[12px] font-medium rounded-lg border border-outline-variant/40">
                            5S & Safety
                        </span>
                        <span class="px-2.5 py-1 bg-error/8 text-error text-[12px] font-bold rounded-lg flex items-center gap-1 border border-error/15">
                            <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">local_fire_department</span> Hot
                        </span>
                    </div>

                    <!-- Attachment (Image preview inline, like Twitter media) -->
                    <div class="rounded-2xl border border-outline-variant/30 overflow-hidden mb-4 cursor-pointer hover:opacity-90 transition-opacity">
                        <div class="bg-surface-container-high aspect-video flex items-center justify-center relative">
                            <span class="material-symbols-outlined text-[48px] text-on-surface-variant/40">image</span>
                            <div class="absolute bottom-3 left-3 bg-on-surface/60 text-white text-[11px] font-bold px-2.5 py-1 rounded-lg backdrop-blur-sm">
                                foto_area_perakitan.jpg
                            </div>
                        </div>
                    </div>

                    <!-- Metadata (Date/Time) -->
                    <div class="text-on-surface-variant text-[14px] flex items-center flex-wrap gap-2 mb-3">
                        <span>10:30 WIB</span>
                        <span>·</span>
                        <span>23 Jun 2026</span>
                        <span>·</span>
                        <span class="font-medium text-on-surface">3 Balasan</span>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-outline-variant/30 my-1"></div>

                    <!-- Interaction Row -->
                    <div class="flex items-center justify-start gap-8 md:gap-12 py-1">
                        <button type="button" class="interact-btn like group px-3 py-2 -ml-3 hover:bg-error/10 rounded-full transition-colors flex items-center gap-2" onclick="toggleLike(this)" <?= $isRiwayat
                            ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                            : '' ?>>
                            <span class="material-symbols-outlined text-on-surface-variant group-hover:text-error text-[22px] md:text-[24px]">favorite</span>
                            <span class="like-count text-on-surface-variant group-hover:text-error text-[13px] md:text-[14px] font-medium">12</span>
                        </button>
                        <button type="button" class="interact-btn share group px-3 py-2 hover:bg-tertiary/10 rounded-full transition-colors flex items-center gap-2" <?= $isRiwayat
                            ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                            : '' ?>>
                            <span class="material-symbols-outlined text-on-surface-variant group-hover:text-tertiary text-[22px] md:text-[24px]">share</span>
                            <span class="text-on-surface-variant group-hover:text-tertiary text-[13px] md:text-[14px] font-medium">Bagikan</span>
                        </button>
                    </div>
                </div>
            </article>

            <!-- Thick Divider between OP and Replies -->
            <div class="h-2 md:h-3 bg-surface-container-low border-y border-outline-variant/20 mb-2"></div>

            <!-- ============================================ -->
            <!-- REPLIES -->
            <!-- ============================================ -->

            <!-- Reply 1: Teacher -->
            <article class="relative px-4 md:px-6 py-4">
                <div class="flex gap-3 md:gap-4">
                    <!-- Avatar (Teacher = Primary red) -->
                    <div class="shrink-0 flex flex-col items-center">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-primary text-on-primary font-bold text-[16px] md:text-[18px] flex items-center justify-center shadow-sm">
                            R
                        </div>
                        <!-- Thread line to next reply -->
                        <div class="flex-1 w-[2px] bg-outline-variant/30 mt-2 min-h-[16px]"></div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 pb-1">
                        <!-- Header row -->
                        <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mb-2">
                            <span class="font-bold text-on-surface text-[15px] leading-tight">Ratnasari</span>
                            <!-- Verified-style Teacher badge -->
                            <span class="inline-flex items-center gap-0.5 bg-primary/10 text-primary px-2 py-0.5 rounded-full text-[11px] font-bold border border-primary/15">
                                <span class="material-symbols-outlined text-[13px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                                Teacher
                            </span>
                            <span class="text-on-surface-variant text-[13px]">·</span>
                            <span class="text-on-surface-variant text-[13px]">1j</span>
                        </div>

                        <!-- Reply context -->
                        <div class="text-on-surface-variant text-[13px] mb-2 flex items-center gap-1">
                            <span>Membalas</span>
                            <span class="text-primary font-medium">@Budi Santoso</span>
                        </div>

                        <!-- Body -->
                        <div class="text-on-surface text-[15px] leading-relaxed mb-3">
                            Halo Budi, pertanyaan yang sangat bagus. Untuk masalah Seiton, kita bisa menerapkan <strong>"Shadow Board"</strong> di area perakitan. Dengan memberikan siluet alat pada papan penyimpanan, akan langsung terlihat jika ada alat yang hilang atau tidak dikembalikan.
                        </div>

                        <!-- Interaction Row -->
                        <div class="flex items-center gap-1 -ml-2.5">
                            <button type="button" class="interact-btn like" onclick="toggleLike(this)" <?= $isRiwayat
                                ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                                : '' ?>>
                                <span class="material-symbols-outlined">favorite</span>
                                <span class="like-count">8</span>
                            </button>
                            <button type="button" class="interact-btn share" <?= $isRiwayat
                                ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                                : '' ?>>
                                <span class="material-symbols-outlined">share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Divider -->
            <div class="border-b border-outline-variant/20"></div>

            <!-- Reply 2: Peer Student -->
            <article class="relative px-4 md:px-6 py-4">
                <div class="flex gap-3 md:gap-4">
                    <!-- Avatar (Student = neutral) -->
                    <div class="shrink-0 flex flex-col items-center">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-surface-container-high text-on-surface font-bold text-[16px] md:text-[18px] flex items-center justify-center border border-outline-variant/40">
                            A
                        </div>
                        <!-- Thread line to next reply -->
                        <div class="flex-1 w-[2px] bg-outline-variant/30 mt-2 min-h-[16px]"></div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 pb-1">
                        <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mb-2">
                            <span class="font-bold text-on-surface text-[15px] leading-tight">Andi Wibowo</span>
                            <span class="text-on-surface-variant text-[13px]">·</span>
                            <span class="text-on-surface-variant text-[13px]">45m</span>
                        </div>

                        <div class="text-on-surface-variant text-[13px] mb-2 flex items-center gap-1">
                            <span>Membalas</span>
                            <span class="text-primary font-medium">@Ratnasari</span>
                        </div>

                        <div class="text-on-surface text-[15px] leading-relaxed mb-3">
                            Setuju dengan Bu Ratna. Di Line B kami sudah pakai Shadow Board dan sangat membantu saat serah terima shift. Tinggal foto saja papannya, jadi kita tahu alat mana yang kurang saat mulai dan selesai kerja.
                        </div>

                        <!-- Inline attachment: document type -->
                        <div class="rounded-xl border border-outline-variant/30 overflow-hidden mb-3 flex items-center gap-3 p-3 bg-surface hover:bg-surface-container-low transition-colors cursor-pointer group">
                            <div class="w-10 h-10 rounded-lg bg-secondary-container/20 text-secondary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[22px]">description</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-on-surface text-[13px] truncate">Checklist_Shadow_Board_Line_B.pdf</div>
                                <div class="text-on-surface-variant text-[11px]">PDF · 340 KB</div>
                            </div>
                            <span class="material-symbols-outlined text-on-surface-variant text-[18px] opacity-0 group-hover:opacity-100 transition-opacity">download</span>
                        </div>

                        <div class="flex items-center gap-1 -ml-2.5">
                            <button type="button" class="interact-btn like" onclick="toggleLike(this)" <?= $isRiwayat
                                ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                                : '' ?>>
                                <span class="material-symbols-outlined">favorite</span>
                                <span class="like-count">5</span>
                            </button>
                            <button type="button" class="interact-btn share" <?= $isRiwayat
                                ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                                : '' ?>>
                                <span class="material-symbols-outlined">share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Divider -->
            <div class="border-b border-outline-variant/20"></div>

            <!-- Reply 3: OP replies back -->
            <article class="relative px-4 md:px-6 py-4">
                <div class="flex gap-3 md:gap-4">
                    <!-- Avatar (OP again) -->
                    <div class="shrink-0">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-surface-container-high text-on-surface font-bold text-[16px] md:text-[18px] flex items-center justify-center border border-outline-variant/40">
                            B
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 pb-1">
                        <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mb-2">
                            <span class="font-bold text-on-surface text-[15px] leading-tight">Budi Santoso</span>
                            <span class="inline-flex items-center bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full text-[11px] font-medium border border-outline-variant/30">
                                OP
                            </span>
                            <span class="text-on-surface-variant text-[13px]">·</span>
                            <span class="text-on-surface-variant text-[13px]">Baru saja</span>
                        </div>

                        <div class="text-on-surface-variant text-[13px] mb-2 flex items-center gap-1">
                            <span>Membalas</span>
                            <span class="text-primary font-medium">@Andi Wibowo</span>
                        </div>

                        <div class="text-on-surface text-[15px] leading-relaxed mb-3">
                            Terima kasih masukannya Bu Ratna dan Andi. Akan saya usulkan ke supervisor untuk membuat Shadow Board di area kami sore ini. 🙏
                        </div>

                        <div class="flex items-center gap-1 -ml-2.5">
                            <button type="button" class="interact-btn like active" onclick="toggleLike(this)" <?= $isRiwayat
                                ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                                : '' ?>>
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
                                <span class="like-count">3</span>
                            </button>
                            <button type="button" class="interact-btn share" <?= $isRiwayat
                                ? 'disabled style="opacity:0.5;cursor:not-allowed"'
                                : '' ?>>
                                <span class="material-symbols-outlined">share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <?php if ($isRiwayat): ?>
            <!-- Archived End Notice -->
            <div class="border-t border-outline-variant/20"></div>
            <div class="flex flex-col items-center py-12 px-6 text-center">
                <div class="w-14 h-14 bg-surface-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-on-surface-variant text-[28px]">lock</span>
                </div>
                <p class="font-bold text-on-surface text-[15px] mb-1">Diskusi Ditutup</p>
                <p class="text-on-surface-variant text-[13px] max-w-sm">
                    Course telah selesai. Anda tidak dapat mengirim balasan baru.
                </p>
            </div>
            <?php endif; ?>

        </main>

        <!-- Right Sidebar (Widgets) -->
        <aside class="hidden lg:flex flex-col w-[300px] xl:w-[340px] shrink-0 sticky top-[88px] space-y-4 fade-in pb-4 self-start h-fit">

            <!-- Widget 1: Info Course -->
            <div class="bg-surface-container-low rounded-2xl p-4 md:p-5">
                <div class="text-[11px] font-bold tracking-wider text-primary uppercase mb-1.5">Dari Course</div>
                <h3 class="font-bold text-on-surface text-[15px] leading-snug mb-1">Menerapkan 5S di Area Produksi</h3>
                <div class="flex items-center gap-1.5 text-on-surface-variant text-[12px] mb-3">
                    <span class="material-symbols-outlined text-[15px]">person</span>
                    <span>Ratnasari (Teacher)</span>
                </div>
                <!-- Mini Progress -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full" style="width: 60%"></div>
                    </div>
                    <span class="text-[11px] font-bold text-on-surface">60%</span>
                </div>
            </div>

            <!-- Widget 2: Topik & Peserta -->
            <div class="bg-surface-container-low rounded-2xl p-4 md:p-5">
                <h3 class="font-bold text-on-surface text-[15px] mb-3">Info Topik</h3>

                <div class="flex items-center gap-2 mb-4">
                    <span class="px-2 py-1 rounded-md bg-secondary-container/30 text-secondary-dark text-[11px] font-bold uppercase tracking-wide">
                        Tanya Jawab
                    </span>
                    <span class="px-2 py-1 rounded-md bg-surface-container-high text-on-surface-variant text-[11px] font-bold uppercase tracking-wide">
                        Sesi 1
                    </span>
                </div>

                <div class="flex items-center justify-between text-[13px] text-on-surface-variant mb-4">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">chat</span>
                        <span>3 Balasan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">favorite</span>
                        <span>5 Suka</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-outline-variant/20">
                    <p class="text-[12px] font-medium text-on-surface-variant mb-2">Peserta Diskusi</p>
                    <div class="flex items-center gap-1 flex-wrap">
                        <div class="w-8 h-8 rounded-full bg-primary text-on-primary text-[11px] font-bold flex items-center justify-center border-2 border-surface" title="Budi Santoso (Teacher)">BS</div>
                        <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface text-[11px] font-bold flex items-center justify-center border-2 border-surface -ml-2" title="Ratna Sari (OP)">RS</div>
                        <div class="w-8 h-8 rounded-full bg-surface-container-highest text-on-surface text-[11px] font-bold flex items-center justify-center border-2 border-surface -ml-2" title="Andi Wibowo">AW</div>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Diskusi Terkait -->
            <div class="bg-surface-container-low rounded-2xl p-4 md:p-5">
                <h3 class="font-bold text-on-surface text-[15px] mb-3">Diskusi Terkait</h3>
                <div class="space-y-3">
                    <a href="#" class="group block">
                        <h4 class="text-[13px] font-medium text-on-surface group-hover:text-primary transition-colors line-clamp-2 leading-snug">Bagaimana membedakan Seiton dan Seiso dalam prakteknya?</h4>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">12 Balasan · Sesi 2</p>
                    </a>
                    <a href="#" class="group block">
                        <h4 class="text-[13px] font-medium text-on-surface group-hover:text-primary transition-colors line-clamp-2 leading-snug">Contoh form checklist kebersihan harian</h4>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">8 Balasan · Sesi 1</p>
                    </a>
                    <a href="#" class="group block">
                        <h4 class="text-[13px] font-medium text-on-surface group-hover:text-primary transition-colors line-clamp-2 leading-snug">Sanksi jika melanggar standar 5S?</h4>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">4 Balasan · Umum</p>
                    </a>
                </div>
                <a href="#" class="block text-center text-[13px] font-medium text-primary mt-3 pt-2 border-t border-outline-variant/20 hover:text-primary-dark transition-colors">Lihat Semua Diskusi</a>
            </div>

        </aside>
    </div> <!-- End 2-Column Layout Wrapper -->

    <?php if (!$isRiwayat): ?>
    <!-- ============================================ -->
    <!-- COMPOSER (Floating Card on Desktop) -->
    <!-- ============================================ -->
    <div class="fixed bottom-[80px] lg:bottom-0 left-0 lg:left-[240px] w-full lg:w-[680px] lg:ml-8 xl:ml-12 bg-surface/95 backdrop-blur-md border-t lg:border border-outline-variant/25 z-40 pb-safe md:pb-3 lg:pb-0 lg:mb-4 lg:rounded-[24px] lg:shadow-[0_4px_24px_rgba(0,0,0,0.06)] transition-all" id="composerBar">
        <div class="w-full px-4 md:px-6 lg:px-4 lg:py-1 relative">

            <!-- File Preview Chips (dynamic) -->
            <div id="filePreviewContainer" class="hidden gap-2 overflow-x-auto pb-2 pt-3 flex-nowrap" style="scrollbar-width: none;">
                <!-- chips injected by JS -->
            </div>

            <!-- Attachment Menu (Popover) — 3 options only -->
            <div id="attachMenu" class="attach-menu hide absolute bottom-full left-4 mb-3 bg-surface rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] border border-outline-variant/30 w-56 overflow-hidden z-50">
                <div class="p-1.5">
                    <button type="button" class="flex items-center gap-3 w-full px-3.5 py-3 hover:bg-surface-container-low rounded-xl text-left transition-colors" onclick="simulateUpload('doc', 'Dokumen')">
                        <div class="w-9 h-9 rounded-xl bg-secondary-container/20 text-secondary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">description</span>
                        </div>
                        <span class="text-on-surface text-[14px] font-medium">Dokumen</span>
                    </button>
                    <button type="button" class="flex items-center gap-3 w-full px-3.5 py-3 hover:bg-surface-container-low rounded-xl text-left transition-colors" onclick="simulateUpload('media', 'Foto/Video')">
                        <div class="w-9 h-9 rounded-xl bg-tertiary-container/20 text-tertiary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">perm_media</span>
                        </div>
                        <span class="text-on-surface text-[14px] font-medium">Foto / Video</span>
                    </button>
                    <button type="button" class="flex items-center gap-3 w-full px-3.5 py-3 hover:bg-surface-container-low rounded-xl text-left transition-colors" onclick="simulateUpload('camera', 'Kamera')">
                        <div class="w-9 h-9 rounded-xl bg-primary-container/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                        </div>
                        <span class="text-on-surface text-[14px] font-medium">Kamera</span>
                    </button>
                </div>
            </div>

            <!-- Input Row — Premium Composer -->
            <div class="flex items-end gap-3 pt-3 pb-1">
                <!-- User avatar with online indicator -->
                <div class="relative shrink-0 mb-1">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-surface-container-high to-surface-variant text-on-surface font-bold text-[13px] flex items-center justify-center border-2 border-surface shadow-sm">
                        B
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-tertiary rounded-full border-2 border-surface"></div>
                </div>

                <!-- Composer container -->
                <div class="flex-1 bg-surface rounded-[22px] border border-outline-variant/30 shadow-[0_1px_4px_rgba(0,0,0,0.04),inset_0_1px_2px_rgba(0,0,0,0.02)] focus-within:border-primary/40 focus-within:shadow-[0_1px_8px_rgba(180,0,11,0.08),inset_0_1px_2px_rgba(0,0,0,0.02)] transition-all duration-200">
                    <!-- Textarea -->
                    <div class="px-4 pt-3 pb-1">
                        <textarea id="replyInput" class="w-full bg-transparent border-none focus:ring-0 resize-none text-on-surface text-[15px] placeholder:text-on-surface-variant/40 p-0 m-0 leading-relaxed outline-none max-h-36 min-h-[24px] composer-area" placeholder="Tulis balasan..." rows="1" oninput="autoResize(this); validateSend();"></textarea>
                    </div>

                    <!-- Bottom toolbar -->
                    <div class="flex items-center justify-between px-2 pb-2 pt-0.5">
                        <!-- Left action icons -->
                        <div class="flex items-center">
                            <button type="button" id="attachBtn" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant/60 hover:text-primary hover:bg-primary/6 active:scale-90 transition-all" title="Lampirkan file">
                                <span class="material-symbols-outlined text-[22px]">add_circle</span>
                            </button>
                            <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant/60 hover:text-tertiary hover:bg-tertiary/6 active:scale-90 transition-all" title="Format teks">
                                <span class="material-symbols-outlined text-[20px]">text_format</span>
                            </button>
                            <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant/60 hover:text-secondary hover:bg-secondary/6 active:scale-90 transition-all" title="Mention">
                                <span class="material-symbols-outlined text-[20px]">alternate_email</span>
                            </button>
                        </div>
                        <!-- Send button -->
                        <button type="button" id="sendBtn" class="bg-primary text-on-primary rounded-full h-9 w-9 flex items-center justify-center transition-all duration-200 disabled:opacity-20 disabled:cursor-not-allowed disabled:scale-100 hover:scale-105 hover:shadow-md active:scale-95 shadow-[0_2px_8px_rgba(180,0,11,0.25)]" disabled>
                            <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bottom NavBar -->
    <?php
    $active_tab = 'course';
    include 'includes/components/DX_LMS_M_Student_Bottom_Nav.php';
    ?>

    <?php if (!$isRiwayat): ?>
    <script>
        /** ========================================================================= */
        /** Memuat rujukan atau referensi pemilih (selector) dokumen DOM (Document Object Model). */
        /** ========================================================================= */
        const replyInput = document.getElementById('replyInput');
        const sendBtn = document.getElementById('sendBtn');
        const attachBtn = document.getElementById('attachBtn');
        const attachMenu = document.getElementById('attachMenu');
        const filePreview = document.getElementById('filePreviewContainer');
        let fileCount = 0;

        /** ========================================================================= */
        /** Fungsionalitas ekspansi bidang teks otomatis berdasarkan kapasitas masukan. */
        /** ========================================================================= */
        function autoResize(el) {
            el.style.height = '';
            el.style.height = el.scrollHeight + 'px';
        }

        /** ========================================================================= */
        /** Melakukan validasi pengiriman guna memastikan ketersediaan tombol aksi. */
        /** ========================================================================= */
        function validateSend() {
            const hasText = replyInput.value.trim().length > 0;
            const hasFiles = fileCount > 0;
            sendBtn.disabled = !(hasText || hasFiles);
        }

        /** ========================================================================= */
        /** Peralihan menu lampiran berkas terintegrasi. */
        /** ========================================================================= */
        attachBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (attachMenu.classList.contains('hide')) {
                attachMenu.classList.remove('hide');
                attachMenu.classList.add('show');
            } else {
                closeMenu();
            }
        });

        document.addEventListener('click', (e) => {
            if (!attachMenu.contains(e.target) && !attachBtn.contains(e.target)) {
                closeMenu();
            }
        });

        function closeMenu() {
            attachMenu.classList.remove('show');
            attachMenu.classList.add('hide');
        }

        /** ========================================================================= */
        /** Memberikan pengalaman visual simulasi unggahan berkas. */
        /** ========================================================================= */
        function simulateUpload(type, label) {
            closeMenu();
            fileCount++;
            const id = 'fp_' + Date.now();
            let icon = 'description',
                color = 'text-secondary bg-secondary-container/20';
            if (type === 'media') {
                icon = 'perm_media';
                color = 'text-tertiary bg-tertiary-container/20';
            }
            if (type === 'camera') {
                icon = 'photo_camera';
                color = 'text-primary bg-primary-container/10';
            }
            const name = label + '_' + fileCount + (type === 'doc' ? '.pdf' : type === 'media' ? '.jpg' : '.jpg');

            const chip = document.createElement('div');
            chip.id = id;
            chip.className = 'file-chip flex items-center gap-2 px-3 py-2 rounded-xl bg-surface border border-outline-variant/40 shrink-0';
            chip.innerHTML = `
            <div class="w-7 h-7 rounded-lg ${color} flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[16px]">${icon}</span>
            </div>
            <span class="text-on-surface text-[12px] font-medium truncate max-w-[120px]">${name}</span>
            <button type="button" onclick="removeChip('${id}')" class="w-5 h-5 rounded-full flex items-center justify-center text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors ml-0.5 shrink-0">
                <span class="material-symbols-outlined text-[14px]">close</span>
            </button>
        `;
            filePreview.appendChild(chip);
            filePreview.classList.remove('hidden');
            filePreview.classList.add('flex');
            validateSend();
        }

        function removeChip(id) {
            const el = document.getElementById(id);
            if (el) {
                el.remove();
                fileCount--;
            }
            if (fileCount === 0) {
                filePreview.classList.add('hidden');
                filePreview.classList.remove('flex');
            }
            validateSend();
        }

        /** ========================================================================= */
        /** Fungsionalitas pengubah mode indikator apresiasi (like). */
        /** ========================================================================= */
        function toggleLike(btn) {
            if (btn.disabled) return;
            const countEl = btn.querySelector('.like-count');
            let count = parseInt(countEl.textContent) || 0;
            const icon = btn.querySelector('.material-symbols-outlined');

            if (btn.classList.contains('active')) {
                btn.classList.remove('active');
                icon.style.fontVariationSettings = "'FILL' 0";
                count = Math.max(0, count - 1);
            } else {
                btn.classList.add('active');
                icon.style.fontVariationSettings = "'FILL' 1";
                count++;
                /** Mengaktifkan animasi berukuran mikro (denyut/pulse) untuk peningkatan kualitas antarmuka. */
                icon.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    icon.style.transform = 'scale(1)';
                }, 150);
            }
            countEl.textContent = count;
        }
    </script>
    <?php endif; ?>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
