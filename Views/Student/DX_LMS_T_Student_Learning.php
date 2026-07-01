<?php
/** Menerima parameter data dari sistem pengontrol (Controller) untuk rendering visual. */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $page_title = 'Pembelajaran: ' . e($material['title']);
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .pt-safe {
            padding-top: env(safe-area-inset-top, 0px);
        }

        body {
            min-height: max(884px, 100dvh);
        }

        /* Custom scrollbar for content (only for desktop to avoid gaps on mobile) */
        @media (min-width: 1024px) {
            .content-scroll::-webkit-scrollbar {
                width: 8px;
            }

            .content-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .content-scroll::-webkit-scrollbar-thumb {
                background: rgba(203, 213, 225, 0.5);
                border-radius: 8px;
            }

            .content-scroll::-webkit-scrollbar-thumb:hover {
                background: rgba(148, 163, 184, 0.8);
            }
        }

        /* Smooth page transition */
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="dls-app-shell bg-slate-50 text-slate-800 antialiased h-screen flex flex-col pt-safe overflow-hidden">
    <!-- TopAppBar Shared Component -->
    <?php
    $header_title = e($material['title']);
    $show_back = true;
    $back_url =
        $course['status'] === 'selesai'
            ? '?route=/student/riwayat/detail&id=' . urlencode($course['id'])
            : '?route=/student/course/detail&id=' . urlencode($course['id']);
    $notif_count = isset($stats['unread_notifs']) ? $stats['unread_notifs'] : 0;
    /** Memanggil kop antarmuka basis untuk menyajikan tampilan seragam. */
    include 'includes/components/DX_LMS_M_Header.php';
    ?>

    <!-- Sidebar / Bottom Nav Component -->
    <?php
    $active_tab = isset($course['status']) && $course['status'] === 'selesai' ? 'riwayat' : 'course';
    include 'includes/components/DX_LMS_M_Bottom_Nav.php';
    ?>

    <!-- Progress Indicator (Di bawah header standar) -->
    <div class="fixed top-14 w-full lg:w-[calc(100%-240px)] lg:ml-[240px] bg-slate-100 h-1 z-30 transition-all duration-300">
        <?php $progressPercent = (($materialIndex + 1) / $totalMaterials) * 100; ?>
        <div class="bg-primary h-full transition-all duration-500 ease-out" style="width: <?= $progressPercent ?>%;"></div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full lg:w-[calc(100%-240px)] lg:ml-[240px] mt-[60px] mb-[72px] sm:mb-[80px] bg-slate-50 overflow-y-auto content-scroll relative flex flex-col items-center transition-all duration-300">
        <div class="w-full xl:max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col fade-in pb-10">
            <?php if ($material['type'] === 'video'): ?>
            <!-- Video Container -->
            <div class="w-full aspect-video bg-slate-900 sm:rounded-b-2xl shadow-xl flex flex-col relative overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2070&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-40 transition-opacity duration-500" alt="Video Thumbnail">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent opacity-90"></div>

                <div class="absolute inset-0 flex items-center justify-center">
                    <button class="w-20 h-20 bg-primary/90 hover:bg-primary text-white rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(var(--color-primary-rgb),0.5)] hover:scale-110 transition-all duration-300 backdrop-blur-md">
                        <span class="material-symbols-outlined text-[40px] ml-1">play_arrow</span>
                    </button>
                </div>

                <!-- Video Controls Mockup -->
                <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center gap-4 text-white">
                    <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">pause</span></button>
                    <div class="flex-1 h-1.5 bg-white/30 rounded-full relative cursor-pointer group/bar">
                        <div class="absolute top-0 left-0 bottom-0 w-1/3 bg-primary rounded-full group-hover/bar:bg-blue-500 transition-colors"></div>
                        <div class="absolute top-1/2 left-1/3 -translate-y-1/2 -translate-x-1/2 w-3 h-3 bg-white rounded-full shadow opacity-0 group-hover/bar:opacity-100 transition-opacity"></div>
                    </div>
                    <span class="text-xs sm:text-sm font-medium tracking-wide">02:14 / <?= e(
                        $material['duration'],
                    ) ?></span>
                    <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">volume_up</span></button>
                    <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">fullscreen</span></button>
                </div>
            </div>

            <div class="p-5 sm:p-10 flex-1 w-full mx-auto bg-white sm:mt-6 sm:rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-indigo-100/50">
                        <span class="material-symbols-outlined text-[16px]">smart_display</span> Video Pembelajaran
                    </span>
                    <span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg font-medium text-xs flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px]">schedule</span> <?= e(
                            $material['duration'],
                        ) ?>
                    </span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-6 tracking-tight leading-tight"><?= e(
                    $material['title'],
                ) ?></h2>

                <div class="prose prose-slate prose-lg max-w-none text-slate-600 leading-relaxed font-body-md border-t border-slate-100 pt-6 !text-justify">
                    <p class="text-[17px] text-slate-700">Simak video pembelajaran berikut untuk memahami materi <strong><?= e(
                        $material['title'],
                    ) ?></strong> secara visual. Pastikan Anda memperhatikan setiap detail karena poin-poin penting akan diujikan pada Post-Test sesi ini.</p>

                    <div class="mt-8 bg-slate-50 p-6 rounded-xl border border-slate-200">
                        <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500">lightbulb</span> Poin Utama Pembahasan
                        </h4>
                        <ul class="space-y-3 text-[15px] text-slate-600 list-disc list-inside marker:text-primary">
                            <li>Pemahaman mendasar terkait fungsi dan tujuan.</li>
                            <li>Langkah-langkah operasional yang sesuai standar keselamatan perusahaan.</li>
                            <li>Bagaimana mengidentifikasi anomali mesin atau proses sejak dini.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php elseif ($material['type'] === 'pdf'): ?>
            <div class="p-6 sm:p-10 border-b border-slate-100 bg-white sm:mt-8 sm:rounded-t-2xl shadow-sm z-10 relative w-full">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1.5 bg-rose-50 text-rose-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-rose-100/50">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Dokumen PDF
                    </span>
                    <span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg font-medium text-xs flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px]">description</span> 12 Halaman
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-3 tracking-tight"><?= e(
                    $material['title'],
                ) ?></h2>
                <p class="font-body-md text-slate-500 text-[17px]">Silakan baca dan pelajari dokumen standar operasional di bawah ini secara saksama.</p>
            </div>

            <div class="flex-1 w-full bg-slate-100 p-4 sm:p-8 flex items-start justify-center sm:min-h-[650px] sm:rounded-b-2xl shadow-inner relative">
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 20px 20px;"></div>

                <div class="bg-white w-full max-w-3xl aspect-[1/1.4] flex flex-col items-center justify-center text-slate-400 p-8 text-center border border-slate-200 rounded-xl relative overflow-hidden group z-10">
                    <div class="absolute top-0 left-0 right-0 h-14 bg-slate-800 flex items-center justify-between px-6 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <span class="text-sm font-medium opacity-80">Hal 1 dari 12</span>
                        <div class="flex gap-1.5">
                            <button class="hover:bg-white/20 p-2 rounded-lg transition-colors"><span class="material-symbols-outlined text-[20px]">zoom_out</span></button>
                            <button class="hover:bg-white/20 p-2 rounded-lg transition-colors"><span class="material-symbols-outlined text-[20px]">zoom_in</span></button>
                            <div class="w-px h-5 bg-white/20 mx-2 self-center"></div>
                            <button class="hover:bg-white/20 p-2 rounded-lg transition-colors" title="Unduh"><span class="material-symbols-outlined text-[20px]">download</span></button>
                        </div>
                    </div>

                    <div class="w-28 h-28 bg-rose-50 rounded-full flex items-center justify-center mb-6 shadow-inner border border-rose-100">
                        <span class="material-symbols-outlined text-[56px] text-rose-500">description</span>
                    </div>
                    <h3 class="font-headline-md text-slate-800 font-bold mb-3">Penampil Dokumen</h3>
                    <p class="font-body-md text-slate-500 max-w-sm leading-relaxed">Dalam implementasi sesungguhnya, area ini akan me-render PDF secara interaktif.</p>

                    <button class="mt-10 px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white text-[15px] font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all active:scale-95 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">open_in_new</span> Buka Layar Penuh
                    </button>
                </div>
            </div>

            <?php elseif ($material['type'] === 'image'): ?>
            <div class="p-6 sm:p-10 border-b border-slate-100 bg-white sm:mt-8 sm:rounded-t-2xl shadow-sm w-full">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-emerald-100/50">
                        <span class="material-symbols-outlined text-[16px]">image</span> Infografis / Gambar
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight leading-tight"><?= e(
                    $material['title'],
                ) ?></h2>
                <p class="font-body-md text-slate-500 mt-3 text-[17px]">Perhatikan infografis berikut untuk panduan visual.</p>
            </div>
            <div class="flex-1 w-full bg-slate-100 p-4 sm:p-10 flex items-start justify-center sm:rounded-b-2xl shadow-inner">
                <div class="bg-white rounded-2xl shadow-xl w-full p-2 sm:p-4 flex items-center justify-center relative overflow-hidden group border border-slate-200">
                    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=2073&auto=format&fit=crop" class="w-full rounded-xl object-contain max-h-[70vh] transition-transform duration-700 group-hover:scale-[1.02]" alt="Materi Gambar">
                    <div class="absolute bottom-8 right-8 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <button class="bg-slate-900/90 backdrop-blur-md text-white p-4 rounded-full shadow-2xl hover:bg-slate-800 hover:scale-110 transition-all">
                            <span class="material-symbols-outlined text-[24px]">zoom_in_map</span>
                        </button>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Text / Article -->
            <div class="pt-6 pb-2 px-6 sm:p-14 w-full mx-auto bg-white sm:mt-8 sm:mb-4 sm:rounded-2xl sm:shadow-lg sm:border border-slate-100 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-primary"></div>

                <div class="flex items-center gap-3 mb-8 mt-2">
                    <span class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg font-semibold text-xs flex items-center gap-1.5 border border-primary/20">
                        <span class="material-symbols-outlined text-[16px]">article</span> Materi Bacaan
                    </span>
                    <span class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg font-medium text-xs flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px]">schedule</span> <?= e(
                            $material['duration'],
                        ) ?> Est. Waktu
                    </span>
                </div>

                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-8 tracking-tight leading-[1.25]"><?= e(
                    $material['title'],
                ) ?></h2>

                <div class="prose prose-slate prose-lg max-w-none text-slate-600 leading-[1.85] font-body-md !text-justify">
                    <p class="text-xl text-slate-800 font-medium leading-relaxed mb-8">Ini adalah pengantar materi untuk <strong><?= e(
                        $material['title'],
                    ) ?></strong>. Diharapkan partisipan mampu memahami landasan operasional secara utuh sebelum mengaplikasikannya di lapangan.</p>

                    <p>Dalam dunia industri manufaktur yang bergerak sangat cepat, efisiensi dan kualitas adalah dua sisi koin yang tidak bisa dipisahkan. Konsep dasar yang diajarkan pada sesi ini telah diterapkan secara global dan terbukti menekan angka <em>defect</em> hingga di bawah batas toleransi perusahaan.</p>

                    <figure class="my-12">
                        <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop" class="rounded-2xl shadow-xl w-full object-cover max-h-[400px]" alt="Ilustrasi Industri">
                        <figcaption class="text-center text-sm text-slate-500 mt-4 font-medium">Gambar 1: Lini produksi terotomatisasi sesuai standar industri 4.0.</figcaption>
                    </figure>

                    <h3 class="text-2xl font-bold text-slate-800 mt-12 mb-6">Landasan Utama</h3>
                    <p>Ada beberapa poin kritis yang harus dijadikan pegangan oleh setiap operator maupun tim <em>engineering</em> di lapangan:</p>
                    <ul class="space-y-3 mt-4 mb-10 marker:text-primary">
                        <li class="pl-2"><strong>Konsistensi Standar:</strong> Jangan pernah menurunkan standar keselamatan demi mengejar kecepatan. Kecepatan tanpa akurasi adalah pemborosan (muda).</li>
                        <li class="pl-2"><strong>Pelaporan Aktual:</strong> Temuan sekecil apapun di line produksi (abnormalitas) harus segera dilaporkan kepada atasan.</li>
                        <li class="pl-2"><strong>Perawatan Preventif:</strong> Mesin yang dirawat sebelum rusak akan memberikan <em>output</em> yang jauh lebih konsisten dibandingkan perawatan reaktif.</li>
                    </ul>

                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50/50 border-l-[6px] border-primary p-6 sm:p-8 rounded-r-2xl mt-10 mb-2 sm:my-12 relative overflow-hidden shadow-sm">
                        <span class="material-symbols-outlined absolute -right-6 -bottom-6 text-[140px] text-primary/5 select-none pointer-events-none">lightbulb</span>
                        <div class="flex items-start gap-4 relative z-10">
                            <span class="material-symbols-outlined text-primary text-[32px] mt-1 drop-shadow-sm">info</span>
                            <div>
                                <h4 class="font-bold text-slate-900 m-0 text-xl tracking-tight">Insight Industri</h4>
                                <p class="text-slate-700 mt-3 text-[17px] leading-relaxed font-medium">Tahukah Anda? Implementasi prosedur yang disiplin dapat mengurangi potensi kecelakaan kerja hingga 80% pada bulan pertama penerapan penuh.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Bottom Navigation Card (Placed at the end of the page) -->
            <?php $isCompleted = $course['status'] === 'selesai'; ?>
            <div class="mt-2 sm:mt-4 mb-6 mx-4 sm:mx-0">
                <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200/60 rounded-2xl p-3 sm:p-5 flex items-center justify-between transition-all hover:shadow-[0_8px_40px_rgb(0,0,0,0.08)]">

                    <?php if ($materialIndex === 0 && !$isCompleted): ?>
                    <a href="<?= $prevUrl ?>" class="group flex items-center justify-center px-3 py-2.5 sm:px-5 sm:py-2.5 rounded-xl hover:bg-slate-50 transition-all duration-300 active:scale-95 text-slate-600 font-semibold opacity-50 pointer-events-none">
                        <span class="material-symbols-outlined text-[20px] mr-1.5 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                        <span class="hidden sm:inline">Materi Sebelumnya</span>
                        <span class="sm:hidden">Kembali</span>
                    </a>
                    <?php else: ?>
                    <a href="<?= $prevUrl ?>" class="group flex items-center justify-center px-3 py-2.5 sm:px-5 sm:py-2.5 rounded-xl hover:bg-slate-50 transition-all duration-300 active:scale-95 text-slate-600 font-semibold">
                        <span class="material-symbols-outlined text-[20px] mr-1.5 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                        <?php if ($materialIndex === 0 && $isCompleted): ?>
                        <span class="hidden sm:inline">Kembali ke Riwayat</span>
                        <span class="sm:hidden">Riwayat</span>
                        <?php else: ?>
                        <span class="hidden sm:inline">Materi Sebelumnya</span>
                        <span class="sm:hidden">Kembali</span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>

                    <div class="flex items-center justify-center px-2">
                        <div class="font-bold text-slate-800 text-[14px] sm:text-[15px] whitespace-nowrap bg-slate-50 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg border border-slate-100">
                            <span class="hidden sm:inline">Materi </span><span class="text-primary text-[16px] sm:text-[18px] mx-0.5"><?= $materialIndex +
                                1 ?></span>
                            <span class="text-slate-400 font-medium text-[13px] sm:text-[14px] mx-1">/</span> <?= $totalMaterials ?>
                        </div>
                    </div>

                    <a href="<?= $nextUrl ?>" class="group flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-2.5 rounded-xl bg-primary text-white hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 active:scale-95 font-semibold">
                        <?php if ($materialIndex === $totalMaterials - 1): ?>
                        <?php if ($isCompleted): ?>
                        <span class="hidden sm:inline">Kembali ke Riwayat</span>
                        <span class="sm:hidden">Riwayat</span>
                        <span class="material-symbols-outlined text-[20px] ml-1.5 group-hover:translate-x-1 transition-transform">history</span>
                        <?php else: ?>
                        <span class="hidden sm:inline">Selesai &amp; Post-Test</span>
                        <span class="sm:hidden">Post-Test</span>
                        <span class="material-symbols-outlined text-[20px] ml-1.5 group-hover:translate-x-1 transition-transform">assignment_turned_in</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="hidden sm:inline">Materi Selanjutnya</span>
                        <span class="sm:hidden">Lanjut</span>
                        <span class="material-symbols-outlined text-[20px] ml-1.5 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        <?php endif; ?>
                    </a>

                </div>
            </div>

        </div>
    </main>
</body>

</html>