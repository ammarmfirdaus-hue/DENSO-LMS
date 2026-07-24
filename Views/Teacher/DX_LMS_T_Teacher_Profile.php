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

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[100px] pt-4 lg:pt-6">
        <div class="max-w-3xl mx-auto space-y-stack-lg">
            
            <!-- User Info Card -->
            <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-colors"></div>
                
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">
                    <div class="w-24 h-24 rounded-full bg-surface shadow-sm border-2 border-surface flex items-center justify-center shrink-0">
                        <img src="https://ui-avatars.com/api/?name=B+S&background=e2e8f0&color=0f172a&size=100" alt="Avatar" class="w-full h-full rounded-full">
                    </div>
                    <div class="text-center sm:text-left pt-2 flex-1">
                        <h2 class="font-headline-md font-bold text-on-surface"><?= e($teacher['name']) ?></h2>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 mt-1 justify-center sm:justify-start">
                            <span class="font-label-md text-primary font-bold px-2 py-0.5 bg-primary-container rounded-md inline-block"><?= e(
                                $teacher['role'],
                            ) ?></span>
                            <span class="font-body-sm text-on-surface-variant">NPK: <?= e($teacher['npk']) ?></span>
                        </div>
                        <p class="font-body-sm text-on-surface-variant mt-3 bg-surface/80 rounded-md px-3 py-1.5 inline-block border border-outline-variant/50">
                            Departemen: <?= e($teacher['department']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-lg">
                <!-- Teaching Stats -->
                <div class="bg-surface-container-lowest border border-surface-variant rounded-xl p-5 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                    <h3 class="font-title-md font-bold text-on-surface mb-4 border-b border-surface-variant pb-2">Statistik Mengajar</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <span class="material-symbols-outlined text-secondary text-[24px]">school</span>
                            <span class="font-headline-sm font-bold text-on-surface">3</span>
                            <span class="font-body-sm text-on-surface-variant">Course Diampu</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="material-symbols-outlined text-tertiary text-[24px]">groups</span>
                            <span class="font-headline-sm font-bold text-on-surface">45</span>
                            <span class="font-body-sm text-on-surface-variant">Total Peserta Diajar</span>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="bg-surface-container-lowest border border-surface-variant rounded-xl p-5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col justify-between">
                    <div>
                        <h3 class="font-title-md font-bold text-on-surface mb-4 border-b border-surface-variant pb-2">Pengaturan Akses</h3>
                        <p class="font-body-sm text-on-surface-variant mb-4">Anda login sebagai Pengajar. Beberapa pengaturan data akun disinkronkan langsung dari HRIS.</p>
                    </div>
                    <?php
                    $roles = $_SESSION['available_roles'] ?? ['student', 'teacher'];
                    if (count($roles) > 1): ?>
                    <div class="bg-surface-container-low border border-outline-variant/50 rounded-lg p-3">
                        <p class="font-label-sm font-bold text-on-surface mb-2">Switch Akses Dashboard</p>
                        <form action="?route=/login/switch" method="POST" class="flex gap-2">
                            <input type="hidden" name="target_role" value="student">
                            <button type="submit" class="w-full bg-secondary text-on-secondary px-3 py-2 rounded-md font-label-sm font-bold hover:bg-secondary-600 transition-colors flex items-center justify-center gap-1 shadow-sm">
                                <span class="material-symbols-outlined text-[16px]">swap_horiz</span> Ke Dashboard Student
                            </button>
                        </form>
                    </div>
                    <?php endif;
                    ?>
                </div>
            </div>

            <!-- General Options -->
            <div class="bg-surface-container-lowest border border-surface-variant rounded-xl overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <div class="divide-y divide-surface-variant">
                    <a href="#" class="flex items-center justify-between p-4 bg-surface hover:bg-surface-container-low transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center group-hover:bg-primary-container group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[18px]">help</span>
                            </div>
                            <span class="font-title-sm font-medium text-on-surface">Pusat Bantuan / FAQ</span>
                        </div>
                        <span class="material-symbols-outlined text-surface-variant group-hover:translate-x-1 transition-transform">chevron_right</span>
                    </a>
                    
                    <a href="?route=/logout" class="flex items-center justify-between p-4 bg-surface hover:bg-error-container/20 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center group-hover:bg-error text-error group-hover:text-on-error transition-colors">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                            </div>
                            <span class="font-title-sm font-bold text-error">Logout</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="text-center pb-4 text-on-surface-variant opacity-60">
                <p class="font-label-sm text-[11px]">DENSO LMS v2.1.0 • Teacher Modul</p>
            </div>

        </div>
    </main>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>
</body>
</html>
