<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    $page_title = 'Detail Diskusi - LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php'; 
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-background text-on-background antialiased selection:bg-primary-container selection:text-on-primary-container min-h-screen flex flex-col pb-[140px] md:pb-[80px]">
    <!-- TopAppBar -->
    <header class="bg-surface border-b border-outline-variant flex justify-between items-center w-full px-container-margin h-14 sticky top-0 z-40 transition-all duration-200">
        <a href="?route=/student/course/detail&tab=forum" class="text-on-surface-variant hover:bg-surface-container-low rounded-full p-2 -ml-2 transition-colors flex items-center justify-center">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="font-headline-sm text-headline-sm font-bold tracking-tight text-on-background flex-1 text-center pr-8">Forum Diskusi</h1>
        <div class="w-10"></div>
    </header>

    <!-- Main Canvas -->
    <main class="flex-1 w-full max-w-3xl mx-auto px-container-margin py-stack-md flex flex-col gap-stack-lg relative">
        <!-- Original Post Section -->
        <section class="bg-surface-container-lowest rounded-xl p-stack-md shadow-[0_4px_12px_0_rgba(0,0,0,0.05)] border border-surface-container-low relative">
            <div class="flex items-start gap-stack-sm mb-stack-sm">
                <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 bg-surface-container-high">
                    <img alt="Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBcxdFJtm3SkkyZqJHjGg4n8VPnczbPIzQMg3gJIRz4371f7plXL5myrEC7SlzsrZGcPdg244cytQx4uDEFrGPY_TvI0P8gnoERiZ00kX8-EMrlyPlfrhPf83RsdoVu8G_6RWeUN38Mue_yxAnQv4EimeD7MZCn-5NPKhAZBqJ_US7bNKOgFOiyh6a8VmOAjy3xXMdjWBETVJysi90pHzcqU8gGjOZtvEwhBRd8cxFMtfEtqSnG32wSDIVyA9gBZ7d344Xf8q412SM">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-baseline">
                        <h2 class="font-label-md text-label-md text-on-background font-bold">Budi Santoso</h2>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">2 jam yang lalu</span>
                    </div>
                    <p class="font-label-sm text-label-sm text-secondary">Operator Produksi Line A</p>
                </div>
            </div>
            <div class="mt-stack-sm mb-stack-md">
                <h3 class="font-headline-sm text-headline-sm text-on-background mb-stack-sm">Bagaimana penerapan 5S yang efektif di area perakitan akhir?</h3>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    Saya sering menemukan beberapa alat kerja tidak dikembalikan ke tempat asalnya (Seiton) setelah pergantian shift. Apakah ada tips atau metode visual management yang lebih baik agar semua anggota tim lebih disiplin menerapkan standar 5S?
                </p>
            </div>
            <!-- Attachment -->
            <div class="flex items-center gap-stack-sm p-stack-sm rounded-lg bg-surface-container-low border border-surface-container-high w-fit mb-stack-md cursor-pointer hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-primary">image</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant">foto_area_perakitan.jpg</span>
            </div>
            <!-- Interaction Bar -->
            <div class="flex gap-stack-md border-t border-surface-container pt-stack-sm">
                <button class="flex items-center gap-base text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">thumb_up</span>
                    <span class="font-label-sm text-label-sm">12 Suka</span>
                </button>
                <div class="flex items-center gap-base text-on-surface-variant">
                    <span class="material-symbols-outlined text-[20px]">chat_bubble_outline</span>
                    <span class="font-label-sm text-label-sm">3 Balasan</span>
                </div>
            </div>
        </section>

        <!-- Replies Section -->
        <section class="flex flex-col gap-stack-md">
            <h4 class="font-label-md text-label-md text-on-surface-variant ml-2">Balasan</h4>
            
            <!-- Reply 1 (Instructor/Teacher) -->
            <div class="flex gap-stack-sm ml-4 relative">
                <div class="absolute left-[-20px] top-8 bottom-[-24px] w-[2px] bg-surface-container"></div>
                <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-surface-container-high mt-1 border-2 border-primary-container relative z-10">
                    <img alt="Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCKKMOQBgMu2jtO2W_vJLcTDx1RkMNOXaTey4koMPo30RM10tatDr6NpDxeLetctKdJvUUdJNg2ln1CMlhYGXIOIasVATW8UIvJrIfPomf9AVqsvKSZyfzTQlPU4jgBrH-2meNKaurF5Rea9YCA27XN3wW-ONzgx3kr8_P-wLaw7XZIin-EseQie2A4Frouh-mfr8aRL5giz1LCezWOoyZ1Wj70DBlaPti4jMImAX5c6Q34yNp1N_KU1X_2YJLAum_R_AapEwS912E">
                </div>
                <div class="flex-1 bg-surface-container-lowest rounded-xl rounded-tl-none p-stack-sm shadow-sm border border-surface-container-low">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-label-md text-label-md text-on-background font-bold">Ibu Ratna</span>
                        <span class="bg-primary-container text-on-primary-container px-2 py-0.5 rounded-full font-label-sm text-[10px] uppercase font-bold tracking-wider">Instruktur</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant ml-auto">1 jam yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Halo Budi, pertanyaan yang sangat bagus. Untuk masalah Seiton, kita bisa menerapkan "Shadow Board" di area perakitan. Dengan memberikan siluet alat pada papan penyimpanan, akan langsung terlihat jika ada alat yang hilang atau tidak dikembalikan.
                    </p>
                </div>
            </div>

            <!-- Reply 2 (Peer) -->
            <div class="flex gap-stack-sm ml-4 relative">
                <div class="absolute left-[-20px] top-8 bottom-[-24px] w-[2px] bg-surface-container"></div>
                <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-surface-container-high mt-1 relative z-10">
                    <img alt="Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAmf-mwkzzI_wz5LC-aNoZF1w-Gvec_CXFnJbhQ3Tvy9HRrxL07g3Ee6maQx2E7EHZplliebcVoM5Z5mOYFN174j-0vo-DdONjM6MQDZVmHJhbkAf16kY9MWAUVbFCs9eRAwCFbJq6uiwq3vrEcmOqQ9vP4CZBSwyrfCCGV7kWEczqL7pUPeh45Zl-UWvrhkSCM42PqSMuUNfpZq6mxs2bMIUErTLlohVoTQf5H_ARKTv-QMHl1ZGSnTF1oU9-arjUbfRiZpxrXr7U">
                </div>
                <div class="flex-1 bg-surface-container-lowest rounded-xl rounded-tl-none p-stack-sm shadow-sm border border-surface-container-low">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-label-md text-label-md text-on-background font-bold">Andi Wibowo</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant ml-auto">45 menit yang lalu</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Setuju dengan Bu Ratna. Di Line B kami sudah pakai Shadow Board dan sangat membantu saat serah terima shift. Tinggal foto saja papannya.
                    </p>
                </div>
            </div>

            <!-- Reply 3 (Peer) -->
            <div class="flex gap-stack-sm ml-4 relative">
                <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-surface-container-high mt-1 relative z-10">
                    <img alt="Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC_MPo6insXDvewqKhY1pv1y2NXTm7o_JztPEfxPs1wJcpOMAq_QGlsp8bX6yZHjuELTnAyu--1LRAwi4PUVZHIbVBfzt84q-Zfd8R2VS6JZpY7IwgjPreRRRJbe7ROS1dDS2rOO0UrOsc8gaE7w-Df2mt5rPIihdU4E4OEu5Rv8pUdcX0Za4U6kxVbDw7ZVJkedIQWt5ihaY9JIOgXUyfuwTEsrFs0VVkxjrTH70uUb1L4dS8RU8MvnBHLkbIjeM4YH8M-8SAXKTI">
                </div>
                <div class="flex-1 bg-surface-container-lowest rounded-xl rounded-tl-none p-stack-sm shadow-sm border border-surface-container-low">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-label-md text-label-md text-on-background font-bold">Budi Santoso</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant ml-auto">Baru saja</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Terima kasih masukannya Bu Ratna dan Andi. Akan saya usulkan ke supervisor untuk membuat Shadow Board di area kami.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- Fixed Input Bar -->
    <div class="fixed bottom-0 left-0 w-full bg-surface-container-lowest border-t border-surface-container-high p-stack-sm px-container-margin z-40 pb-safe md:pb-stack-sm">
        <div class="max-w-3xl mx-auto flex items-end gap-stack-sm">
            <button class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors shrink-0 mb-1">
                <span class="material-symbols-outlined text-[24px]">attach_file</span>
            </button>
            <div class="flex-1 bg-surface-container-low rounded-2xl border border-surface-container-high focus-within:border-primary-container focus-within:bg-surface-container-lowest transition-colors flex items-center px-4 py-2 min-h-[44px]">
                <textarea class="w-full bg-transparent border-none focus:ring-0 resize-none font-body-md text-body-md text-on-background placeholder-on-surface-variant p-0 m-0 leading-tight outline-none max-h-32" placeholder="Tulis balasan..." rows="1"></textarea>
            </div>
            <button class="p-2 bg-primary-container text-on-primary-container hover:bg-primary rounded-full transition-colors shrink-0 mb-1 shadow-sm flex items-center justify-center h-10 w-10">
                <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
        </div>
    </div>
</body>
</html>
