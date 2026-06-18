<?php
// $course, $session, $material, $materialIndex, $totalMaterials, $prevUrl, $nextUrl dilemparkan dari controller
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'PT. LMS - Pembelajaran: ' . e($material['title']);
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        
        /* Custom scrollbar for content */
        .content-scroll::-webkit-scrollbar { width: 6px; }
        .content-scroll::-webkit-scrollbar-track { background: transparent; }
        .content-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="bg-surface text-on-surface antialiased h-screen flex flex-col pt-safe overflow-hidden">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex items-center w-full px-container-margin h-14 shrink-0">
        <a href="?route=/student/course/detail&id=<?= e($course['id']) ?>" class="p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-all duration-200 active:scale-95 text-on-surface-variant shrink-0">
            <span class="material-symbols-outlined">close</span>
        </a>
        <div class="flex-1 min-w-0 ml-2">
            <h1 class="font-headline-sm text-headline-sm font-bold text-on-surface truncate"><?= e($material['title']) ?></h1>
            <p class="font-label-sm text-label-sm text-on-surface-variant truncate"><?= e($course['title']) ?> • <?= e($session['title']) ?></p>
        </div>
    </header>

    <!-- Progress Indicator -->
    <div class="w-full bg-surface-container-highest/30 h-1 shrink-0">
        <?php 
            $progressPercent = (($materialIndex + 1) / $totalMaterials) * 100;
        ?>
        <div class="bg-primary h-full transition-all duration-300" style="width: <?= $progressPercent ?>%;"></div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 w-full bg-surface-container-lowest overflow-y-auto content-scroll relative flex flex-col">
        <div class="w-full max-w-4xl mx-auto flex-1 flex flex-col">
            <?php if ($material['type'] === 'video'): ?>
                <div class="w-full aspect-video bg-black flex items-center justify-center relative">
                    <span class="material-symbols-outlined text-[64px] text-white/50">play_circle</span>
                    <p class="absolute bottom-4 left-4 text-white font-label-md bg-black/50 px-2 py-1 rounded">Placeholder Video Player</p>
                </div>
                <div class="p-container-margin mt-4 flex-1">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2"><?= e($material['title']) ?></h2>
                    <div class="flex items-center gap-2 mb-6">
                        <span class="px-2 py-1 bg-secondary-container/20 text-secondary rounded font-label-sm text-label-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">schedule</span> <?= e($material['duration']) ?>
                        </span>
                        <span class="px-2 py-1 bg-surface-container text-on-surface-variant rounded font-label-sm text-label-sm">Video</span>
                    </div>
                    <div class="font-body-md text-on-surface-variant leading-relaxed">
                        <p>Simak video pembelajaran berikut untuk memahami materi <?= e($material['title']) ?>. Pastikan Anda memperhatikan setiap detail karena poin-poin penting akan diujikan pada Post-Test sesi ini.</p>
                    </div>
                </div>

            <?php elseif ($material['type'] === 'pdf'): ?>
                <div class="p-container-margin border-b border-surface-variant bg-surface">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2"><?= e($material['title']) ?></h2>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-1 bg-primary-container/20 text-primary rounded font-label-sm text-label-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span> <?= e($material['duration']) ?>
                        </span>
                    </div>
                    <p class="font-body-md text-on-surface-variant">Silakan baca dokumen di bawah ini.</p>
                </div>
                <div class="flex-1 w-full bg-surface-variant p-4 flex items-center justify-center min-h-[400px]">
                    <div class="bg-surface w-full max-w-3xl aspect-[1/1.4] shadow-lg flex flex-col items-center justify-center text-on-surface-variant p-8 text-center border border-outline-variant rounded-sm">
                        <span class="material-symbols-outlined text-[64px] text-primary/40 mb-4">picture_as_pdf</span>
                        <h3 class="font-headline-sm">Dokumen PDF Tersemat</h3>
                        <p class="font-body-sm mt-2">Dalam implementasi nyata, ini akan menampilkan viewer PDF menggunakan tag &lt;iframe&gt; atau PDF.js</p>
                    </div>
                </div>

            <?php elseif ($material['type'] === 'image'): ?>
                <div class="p-container-margin border-b border-surface-variant bg-surface">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2"><?= e($material['title']) ?></h2>
                </div>
                <div class="flex-1 w-full bg-surface-variant p-4 flex items-center justify-center">
                    <div class="bg-surface rounded-lg shadow max-w-3xl w-full aspect-video flex items-center justify-center">
                        <span class="material-symbols-outlined text-[64px] text-on-surface-variant/40">image</span>
                        <p class="ml-2 font-body-md text-on-surface-variant">Image Placeholder</p>
                    </div>
                </div>

            <?php else: ?>
                <!-- Text / Article -->
                <div class="p-container-margin pt-6 flex-1">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-4"><?= e($material['title']) ?></h2>
                    <div class="flex items-center gap-2 mb-8 border-b border-surface-variant pb-4">
                        <span class="px-2 py-1 bg-surface-container text-on-surface-variant rounded font-label-sm text-label-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">schedule</span> <?= e($material['duration']) ?>
                        </span>
                        <span class="px-2 py-1 bg-surface-container text-on-surface-variant rounded font-label-sm text-label-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">article</span> Teks
                        </span>
                    </div>
                    <div class="prose prose-slate max-w-none font-body-md text-on-surface leading-relaxed space-y-4">
                        <p>Ini adalah area konten materi teks. Dalam LMS nyata, bagian ini akan diisi dengan konten rich text dari database yang disisipkan oleh pembuat materi (Teacher).</p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        <div class="bg-primary-container/10 border-l-4 border-primary p-4 rounded-r-lg my-6">
                            <p class="font-medium text-primary m-0">Catatan Penting:</p>
                            <p class="text-on-surface-variant text-sm mt-1">Pastikan Anda memahami poin-poin di atas sebelum melanjutkan ke halaman berikutnya.</p>
                        </div>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bottom Navigation Bar for Learning -->
    <footer class="bg-surface border-t border-outline-variant px-container-margin py-3 pb-safe shrink-0 shadow-[0_-4px_12px_0_rgba(0,0,0,0.02)]">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            
            <a href="<?= $prevUrl ?>" class="flex items-center gap-1 px-4 py-2 rounded-full hover:bg-surface-container-low transition-colors active:scale-95 text-on-surface-variant">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span class="font-label-md hidden sm:inline">Kembali</span>
            </a>

            <div class="font-label-sm text-on-surface-variant bg-surface-container px-3 py-1 rounded-full">
                <?= $materialIndex + 1 ?> / <?= $totalMaterials ?>
            </div>

            <a href="<?= $nextUrl ?>" class="flex items-center gap-1 px-5 py-2 rounded-full bg-primary text-on-primary hover:bg-primary/90 transition-colors active:scale-95 shadow-sm">
                <span class="font-label-md font-medium"><?= ($materialIndex === $totalMaterials - 1) ? 'Post-Test' : 'Selanjutnya' ?></span>
                <span class="material-symbols-outlined text-lg"><?= ($materialIndex === $totalMaterials - 1) ? 'assignment_turned_in' : 'arrow_forward' ?></span>
            </a>
            
        </div>
    </footer>
</body>
</html>
