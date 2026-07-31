<?php
/**
 * Tampilan Pengaturan Role Admin DLS (DLS Admin Role & Access Control View)
 * Ultra-Professional, Highly Detailed & Enterprise-Grade RBAC Management.
 */

$header_title = 'Control Role & Akses';
$active_tab = 'role';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Control Role & Akses';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        /* Smooth scale animation for floating bar */
        @keyframes slideUpFade {
            0% { transform: translateY(100%); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .animate-slide-up {
            animation: slideUpFade 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[108px] lg:ml-[240px] w-full">
        
        <!-- Header & Top Actions -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="font-headline-lg font-bold text-gray-900">Control Role & Akses</h1>
                <p class="font-body-md text-gray-600">Kelola izin akses modul, pembatasan lingkup data (<span class="font-semibold text-gray-800">Scope: Jabatan & Departemen</span>), dan kebijakan keamanan sistem.</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button onclick="openImpersonateModal('Student')" class="flex-1 md:w-56 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold text-sm hover:bg-gray-50 hover:border-gray-400 active:bg-gray-100 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px] text-blue-600">visibility</span>
                    <span>Simulasikan Tampilan</span>
                </button>
                <button onclick="openSecurityAuditModal()" class="flex-1 md:w-56 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold text-sm hover:bg-red-700 active:bg-red-800 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">security</span>
                    <span>Audit Keamanan & SoD</span>
                </button>
            </div>
        </div>

        <!-- 4 KPI Security Summary Cards (Apex Ribbon) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center gap-4 hover:border-red-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Role Sistem</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-2xl font-bold text-gray-900"><?= count($roles ?? []) ?></span>
                        <span class="text-xs font-semibold text-gray-500">Role Terdaftar</span>
                    </div>
                    <span class="text-[11px] text-gray-400">Apex to Audience Tier</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center gap-4 hover:border-blue-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">manage_accounts</span>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Akun Terotorisasi</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <?php 
                            $total_users = 0;
                            foreach (($roles ?? []) as $r) {
                                $total_users += ($r['users_count'] ?? 0);
                            }
                        ?>
                        <span class="text-2xl font-bold text-gray-900"><?= $total_users ?></span>
                        <span class="text-xs font-semibold text-gray-500">Pengguna Aktif</span>
                    </div>
                    <span class="text-[11px] text-emerald-600 font-medium">100% Assigned • No Orphan</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center gap-4 hover:border-emerald-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">policy</span>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Scope Kebijakan</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-2xl font-bold text-gray-900">4</span>
                        <span class="text-xs font-semibold text-gray-500">Level Akses</span>
                    </div>
                    <span class="text-[11px] text-gray-400">Full, Dept Scope, Read, Denied</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center gap-4 hover:border-amber-200 transition-colors">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">verified_user</span>
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Audit & Compliance</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-lg font-bold text-emerald-700">SoD Compliant</span>
                    </div>
                    <span class="text-[11px] text-gray-400">ISO 27001 & Manufaktur</span>
                </div>
            </div>
        </div>

        <!-- Smart Filter & Dual-View Switcher Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search & Filter Controls -->
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-72">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        type="text" 
                        id="searchRoleInput"
                        oninput="filterRoleAccess()" 
                        placeholder="Cari modul, hak akses, atau role..." 
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all"
                    >
                </div>
                <select 
                    id="scopeFilterSelect" 
                    onchange="filterRoleAccess()"
                    class="w-full sm:w-52 py-2 px-3 border border-gray-300 rounded-lg text-sm bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all"
                >
                    <option value="ALL">Semua Tingkat Scope</option>
                    <option value="CORE">System Core (Apex Tier)</option>
                    <option value="ACADEMIC">Academic & Department</option>
                    <option value="LEARNER">Learner & Audience</option>
                </select>
            </div>

            <!-- Dual-View Toggle Buttons -->
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg border border-gray-200 w-full md:w-auto justify-end">
                <button 
                    type="button" 
                    id="btnViewCards" 
                    onclick="switchViewMode('cards')" 
                    class="flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-bold transition-all shadow-sm bg-white text-gray-900 border border-gray-300"
                >
                    <span class="material-symbols-outlined text-[16px]">view_agenda</span>
                    <span>Cards View</span>
                </button>
                <button 
                    type="button" 
                    id="btnViewMatrix" 
                    onclick="switchViewMode('matrix')" 
                    class="flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-bold transition-all text-gray-600 hover:text-gray-900"
                >
                    <span class="material-symbols-outlined text-[16px]">grid_view</span>
                    <span>Matrix Grid</span>
                </button>
            </div>
        </div>

        <!-- =================== MODE 1: ROLE CARDS STACK =================== -->
        <div id="roleCardsSection" class="space-y-4 mb-8">
            <div class="flex items-center justify-between px-1">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-red-600">account_tree</span>
                        <span>Daftar Peran & Profil Otoritas (Role Architecture)</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Setiap profil dilengkapi batasan lingkup data dan persentase keterbukaan modul LMS.</p>
                </div>
                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    5 Active Roles
                </span>
            </div>

            <?php foreach (($roles ?? []) as $r): 
                $role_level = $r['level'] ?? 5;
                $role_name = $r['name'] ?? 'Student';
                $role_badge = $r['badge_label'] ?? 'Audience';
                $role_desc = $r['description'] ?? '';
                $role_users = $r['users_count'] ?? 0;
                $capabilities = $r['capabilities'] ?? [];

                // Color mapping per role
                $icon_bg = match($role_name) {
                    'Super Admin', 'Admin' => 'bg-red-600 text-white border-red-700',
                    'Teacher' => 'bg-blue-600 text-white border-blue-700',
                    'Student' => 'bg-emerald-600 text-white border-emerald-700',
                    'Teacher Associate' => 'bg-amber-500 text-white border-amber-600',
                    default => 'bg-emerald-600 text-white border-emerald-700'
                };

                $border_left = match($role_name) {
                    'Super Admin', 'Admin' => 'border-l-4 border-l-red-600',
                    'Teacher' => 'border-l-4 border-l-blue-600',
                    'Student' => 'border-l-4 border-l-emerald-600',
                    'Teacher Associate' => 'border-l-4 border-l-amber-500',
                    default => 'border-l-4 border-l-emerald-600'
                };

                $badge_class = match($role_name) {
                    'Super Admin', 'Admin' => 'bg-red-50 text-red-700 border-red-200',
                    'Teacher' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'Student' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'Teacher Associate' => 'bg-amber-50 text-amber-700 border-amber-200',
                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                };

                // Percentage access indicator
                $access_percent = match($role_name) {
                    'Super Admin' => 100,
                    'Admin' => 85,
                    'Teacher' => 65,
                    'Teacher Associate' => 45,
                    default => 25
                };

                // Scope Tag
                $scope_tag = match($role_name) {
                    'Super Admin', 'Admin' => 'Global Enterprise Scope (All BU)',
                    'Teacher' => 'Own Department Scope Only 🏢',
                    'Teacher Associate' => 'Assigned Session Scope Only',
                    default => 'Individual Learner Access Only'
                };
            ?>
                <div class="role-card-item flex flex-col lg:flex-row gap-6 lg:items-center p-6 border border-gray-200 rounded-xl hover:border-gray-300 transition-all bg-white shadow-sm <?= $border_left ?>" data-role-name="<?= e($role_name) ?>" data-scope="<?= e($role_name === 'Super Admin' || $role_name === 'Admin' ? 'CORE' : ($role_name === 'Teacher' || $role_name === 'Teacher Associate' ? 'ACADEMIC' : 'LEARNER')) ?>">
                    
                    <!-- Role Level Icon -->
                    <div class="w-12 h-12 rounded-xl <?= $icon_bg ?> flex items-center justify-center font-bold text-lg shadow-sm shrink-0 border">
                        <?= e((string)$role_level) ?>
                    </div>

                    <!-- Role Info & Capabilities -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <h3 class="text-base font-bold text-gray-900"><?= e($role_name) ?></h3>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $badge_class ?>">
                                <?= e($role_badge) ?>
                            </span>
                            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">
                                <?= e($scope_tag) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3"><?= e($role_desc) ?></p>
                        
                        <!-- Capabilities Chips -->
                        <?php 
                            $check_color = match($role_name) {
                                'Super Admin', 'Admin' => 'text-red-600',
                                'Teacher' => 'text-blue-600',
                                'Teacher Associate' => 'text-amber-600',
                                default => 'text-emerald-600'
                            };
                        ?>
                        <div class="flex flex-wrap items-center gap-1.5 mb-3">
                            <?php foreach ($capabilities as $cap): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                    <span class="material-symbols-outlined text-[13px] <?= $check_color ?>">check</span>
                                    <?= e($cap) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Progress Bar Keterbukaan Akses Modul -->
                        <div class="w-full max-w-md">
                            <div class="flex justify-between items-center text-[11px] font-semibold text-gray-500 mb-1">
                                <span>Keterbukaan Akses Modul (System Reach)</span>
                                <span class="text-gray-800"><?= $access_percent ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden border border-gray-200/50">
                                <div class="h-full <?= ($role_name === 'Super Admin' || $role_name === 'Admin') ? 'bg-red-600' : ($role_name === 'Teacher' ? 'bg-blue-600' : ($role_name === 'Teacher Associate' ? 'bg-amber-500' : 'bg-emerald-600')) ?>" style="width: <?= $access_percent ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- User Count, Impersonate & Detail Button -->
                    <div class="flex flex-wrap items-center justify-between lg:justify-end gap-3 shrink-0 pt-4 lg:pt-0 border-t lg:border-0 border-gray-100">
                        <!-- Avatar Stack Preview -->
                        <div class="flex items-center gap-2 mr-2">
                            <div class="flex -space-x-2 overflow-hidden">
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">DI</div>
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">AB</div>
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">HR</div>
                            </div>
                            <a href="?route=/admin/user&role=<?= strtolower(str_replace(' ', '', $role_name)) ?>" class="text-xs font-bold text-gray-700 bg-gray-100 hover:bg-red-50 hover:text-red-600 transition-colors px-2.5 py-1.5 rounded-lg border border-gray-200">
                                <?= e((string)$role_users) ?> Pengguna →
                            </a>
                        </div>

                        <button 
                            type="button"
                            onclick="openImpersonateModal('<?= e($role_name) ?>')" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold text-xs hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-all shadow-sm"
                            title="Simulasikan Tampilan & Sidebar sebagai Role ini"
                        >
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            <span>Simulasi</span>
                        </button>

                        <button 
                            type="button"
                            onclick="openRoleDetailModal('<?= e($r['id'] ?? '') ?>', '<?= e($role_name) ?>', '<?= e($role_badge) ?>')" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 font-semibold text-xs transition-all shadow-sm"
                        >
                            <span class="material-symbols-outlined text-[16px]">settings_applications</span>
                            <span>Detail Otoritas</span>
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- =================== MODE 2: INTERACTIVE PERMISSION MATRIX GRID =================== -->
        <div id="permissionMatrixSection" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-28 hidden">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-red-600">grid_view</span>
                        <h2 class="text-base font-bold text-gray-900">Matriks Hak Akses Interaktif</h2>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Klik pada badge otoritas di bawah ini untuk mengubah tingkat izin secara langsung (<span class="font-semibold text-gray-700">Full Access, Dept. Scope, Read Only, atau Denied</span>).</p>
                </div>

                <!-- Legend Badge Indicators -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">
                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                        <span>Full Access</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-semibold">
                        <span class="material-symbols-outlined text-[14px]">business</span>
                        <span>Dept. Scope</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-semibold">
                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                        <span>Read Only</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 border border-gray-200 font-medium">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        <span>Denied</span>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left border-collapse" id="rbacMatrixTable">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-600">
                            <th class="py-3.5 px-4 w-1/3 sticky left-0 bg-gray-50 z-10 border-r border-gray-200">Modul & Fitur LMS</th>
                            <th class="py-3.5 px-3 text-center w-36">Super Admin <span class="block text-[10px] text-red-600 font-bold">CORE APEX</span></th>
                            <th class="py-3.5 px-3 text-center w-36">Admin <span class="block text-[10px] text-red-600 font-bold">CONTROLLER</span></th>
                            <th class="py-3.5 px-3 text-center w-36">Teacher <span class="block text-[10px] text-blue-600 font-bold">INSTRUCTOR</span></th>
                            <th class="py-3.5 px-3 text-center w-36">Teacher Assoc <span class="block text-[10px] text-amber-600 font-bold">ASSISTANT</span></th>
                            <th class="py-3.5 px-3 text-center w-36">Student <span class="block text-[10px] text-emerald-600 font-bold">AUDIENCE</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        <?php 
                            // Matrix structured by groups
                            $matrix_groups = [
                                '📚 MODUL COURSE & MATERI PEMBELAJARAN' => [
                                    [
                                        'title' => 'Buat & Rilis Course Baru (Syllabus Design)',
                                        'desc' => 'Merancang silabus, penugasan sesi, dan publikasi kelas',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'SCOPE', 'tca' => 'DENIED', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Unggah Materi Video, PDF & Scorm Package',
                                        'desc' => 'Mengelola file materi pembelajaran pada sesi aktif',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'SCOPE', 'tca' => 'SCOPE', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Akses Belajar Materi & Unduh Sertifikat',
                                        'desc' => 'Mempelajari modul course aktif dan melihat riwayat kelulusan',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'FULL', 'tca' => 'FULL', 'std' => 'FULL'
                                    ],
                                ],
                                '📝 MODUL UJIAN, KUIS & FINAL ASSESSMENT' => [
                                    [
                                        'title' => 'Pembuatan Bank Soal & Final Assessment',
                                        'desc' => 'Membuat butir soal PG/Essay, batas kelulusan (KKM), & durasi',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'SCOPE', 'tca' => 'DENIED', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Koreksi Essay & Penilaian Manual Peserta',
                                        'desc' => 'Memeriksa jawaban essay, menilai tugas praktik, & grading',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'SCOPE', 'tca' => 'SCOPE', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Pengerjaan Ujian & Kuis Online (Learner Mode)',
                                        'desc' => 'Mengikuti evaluasi kuis dan melihat skor kelulusan mandiri',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'FULL', 'tca' => 'FULL', 'std' => 'FULL'
                                    ],
                                ],
                                '⚙️ PENGATURAN USER, ASSIGNMENT & AUDIT LOGS' => [
                                    [
                                        'title' => 'Manajemen Akun User & Reset Password NPK',
                                        'desc' => 'Mendaftarkan karyawan, edit status kepegawaian, & ganti sandi',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'DENIED', 'tca' => 'DENIED', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Penyaluran Target Assignment (Jabatan & Golongan)',
                                        'desc' => 'Menetapkan sasaran wajib course ke departemen/divisi',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'READ', 'tca' => 'DENIED', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Audit Activity Hub & Ekspor Laporan Rekap',
                                        'desc' => 'Melihat rekam jejak aktivitas login dan riwayat modifikasi LMS',
                                        'sa' => 'FULL', 'adm' => 'FULL', 'tch' => 'DENIED', 'tca' => 'DENIED', 'std' => 'DENIED'
                                    ],
                                    [
                                        'title' => 'Konfigurasi Role, Permission & Parameter Core',
                                        'desc' => 'Mengubah matriks RBAC dan parameter server perusahaan',
                                        'sa' => 'FULL', 'adm' => 'DENIED', 'tch' => 'DENIED', 'tca' => 'DENIED', 'std' => 'DENIED'
                                    ],
                                ],
                            ];

                            foreach ($matrix_groups as $group_name => $rows):
                        ?>
                            <!-- Group Header -->
                            <tr class="bg-gray-100/80 font-bold text-xs text-gray-700">
                                <td colspan="6" class="py-2.5 px-4 uppercase tracking-wider border-y border-gray-200">
                                    <?= e($group_name) ?>
                                </td>
                            </tr>

                            <?php foreach ($rows as $row): ?>
                                <tr class="hover:bg-red-50/20 transition-colors">
                                    <td class="py-3.5 px-4 sticky left-0 bg-white z-10 border-r border-gray-200">
                                        <span class="block font-semibold text-gray-900"><?= e($row['title']) ?></span>
                                        <span class="text-xs text-gray-500"><?= e($row['desc']) ?></span>
                                    </td>
                                    
                                    <!-- Super Admin (Apex Root - Locked) -->
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 cursor-not-allowed shadow-sm" title="System Apex - Tidak dapat dicabut">
                                            <span class="material-symbols-outlined text-[14px]">lock</span>
                                            <span>Full Access</span>
                                        </span>
                                    </td>

                                    <!-- Admin -->
                                    <td class="py-3.5 px-3 text-center">
                                        <button 
                                            type="button" 
                                            onclick="togglePermissionCell(this, 'Admin', '<?= e($row['title']) ?>')" 
                                            data-current-state="<?= e($row['adm']) ?>"
                                            class="perm-badge-btn inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition-all shadow-sm hover:scale-105"
                                        >
                                            <!-- Rendered by CSS/JS -->
                                        </button>
                                    </td>

                                    <!-- Teacher -->
                                    <td class="py-3.5 px-3 text-center">
                                        <button 
                                            type="button" 
                                            onclick="togglePermissionCell(this, 'Teacher', '<?= e($row['title']) ?>')" 
                                            data-current-state="<?= e($row['tch']) ?>"
                                            class="perm-badge-btn inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition-all shadow-sm hover:scale-105"
                                        >
                                            <!-- Rendered by CSS/JS -->
                                        </button>
                                    </td>

                                    <!-- Teacher Associate -->
                                    <td class="py-3.5 px-3 text-center">
                                        <button 
                                            type="button" 
                                            onclick="togglePermissionCell(this, 'Teacher Associate', '<?= e($row['title']) ?>')" 
                                            data-current-state="<?= e($row['tca']) ?>"
                                            class="perm-badge-btn inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition-all shadow-sm hover:scale-105"
                                        >
                                            <!-- Rendered by CSS/JS -->
                                        </button>
                                    </td>

                                    <!-- Student -->
                                    <td class="py-3.5 px-3 text-center">
                                        <button 
                                            type="button" 
                                            onclick="togglePermissionCell(this, 'Student', '<?= e($row['title']) ?>')" 
                                            data-current-state="<?= e($row['std']) ?>"
                                            class="perm-badge-btn inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition-all shadow-sm hover:scale-105"
                                        >
                                            <!-- Rendered by CSS/JS -->
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Governance & Security Policy Notice Panel -->
        <div class="bg-white text-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">shield_person</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1">Kebijakan Tata Kelola Keamanan (Governance & SoD Policy)</h3>
                    <p class="text-sm text-gray-600 leading-relaxed max-w-3xl">
                        Sistem Learning Management System menerapkan prinsip <strong class="text-gray-900">Separation of Duties (SoD)</strong> standar perusahaan & ISO 27001. 
                        Student tidak dapat mengangkat dirinya sendiri menjadi Teacher. Teacher tidak berwenang mengelola akun user atau mengubah penugasan departemen secara mandiri. Hanya pengguna dengan role <strong class="text-gray-900">Admin</strong> dan <strong class="text-gray-900">Super Admin</strong> yang memiliki otorisasi eskalasi level akses.
                    </p>
                </div>
            </div>
            <button onclick="openSecurityAuditModal()" class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-sm shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">fact_check</span>
                <span>Standar Audit SoD</span>
            </button>
        </div>

    </main>

    <!-- =================== FLOATING UNSAVED CHANGES BAR =================== -->
    <div id="unsavedChangesBar" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 hidden animate-slide-up">
        <div class="bg-gray-900/95 backdrop-blur-md text-white px-6 py-4 rounded-2xl shadow-2xl border border-gray-700 flex items-center gap-6 max-w-xl w-full">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">warning</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-white">Ada <span id="unsavedCount" class="text-amber-400 font-black">1</span> perubahan hak akses yang belum disimpan!</p>
                <p class="text-xs text-gray-300 mt-0.5">Perubahan pada matriks otoritas akan diterapkan setelah konfirmasi ISO/SoD audit.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="cancelUnsavedChanges()" class="px-3.5 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold transition-colors">
                    Batal
                </button>
                <button type="button" onclick="savePermissionMatrix()" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-all shadow-md inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Simpan Akses</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =================== MODAL SIMULASI TAMPILAN (IMPERSONATE SIMULATOR) =================== -->
    <div id="impersonateModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" aria-hidden="true" onclick="closeImpersonateModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-200">
                <div class="px-6 pt-6 pb-4 border-b border-gray-200 flex justify-between items-center bg-gradient-to-r from-blue-900 to-slate-900 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-300 flex items-center justify-center font-bold border border-blue-400/30">
                            <span class="material-symbols-outlined">visibility</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Simulasi Tampilan & Otoritas Role</h3>
                            <p class="text-xs font-medium text-blue-200">Live UX Impersonation Preview • Enterprise LMS</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeImpersonateModal()" class="text-gray-300 hover:text-white transition-colors p-1 rounded-lg">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Role Selector Tabs -->
                <div class="px-6 pt-4 bg-gray-50 border-b border-gray-200 flex flex-wrap gap-2">
                    <?php 
                        $preview_roles = ['Super Admin', 'Admin', 'Teacher', 'Teacher Associate', 'Student'];
                        foreach ($preview_roles as $r_tab):
                    ?>
                        <button 
                            type="button" 
                            onclick="selectImpersonateRole('<?= e($r_tab) ?>')"
                            class="impersonate-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold border transition-all <?= $r_tab === 'Student' ? 'bg-red-600 text-white border-red-700 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>"
                            data-tab="<?= e($r_tab) ?>"
                        >
                            <?= e($r_tab) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Simulated Content Panel -->
                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-blue-50/60 border border-blue-200 text-sm">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-700 block mb-0.5">Sedang Mensimulasikan:</span>
                            <span id="simRoleTitle" class="text-lg font-black text-gray-900">Student</span>
                        </div>
                        <span id="simRoleScope" class="px-3 py-1 rounded-full text-xs font-bold bg-white text-blue-800 border border-blue-300 shadow-sm">
                            Individual Learner Access Only
                        </span>
                    </div>

                    <!-- Sidebar Visual Preview -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-gray-600">vertical_split</span>
                            <span>Menu Sidebar & Navigasi yang Terbuka untuk Role Ini:</span>
                        </h4>
                        <div id="simSidebarPreview" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <!-- Security Guardrails Info -->
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-600 space-y-1.5">
                        <div class="font-bold text-gray-800 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">verified</span>
                            <span>Proteksi Lingkup Data (Data Scope Boundary):</span>
                        </div>
                        <p id="simScopeDescription" class="text-gray-600 leading-relaxed">
                            Student hanya dapat melihat course yang ditugaskan (assigned) untuk Jabatan & Golongan mereka, dan mengerjakan Kuis tanpa hak akses ke modul Admin atau manajemen user.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Anda dapat beralih role tanpa perlu logout.</span>
                    <button type="button" onclick="closeImpersonateModal()" class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-sm transition-colors shadow-sm">
                        Tutup Simulasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Otoritas Role -->
    <div id="roleDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" aria-hidden="true" onclick="closeRoleDetailModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-200">
                <div class="px-6 pt-6 pb-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div id="modalRoleIconBox" class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined">badge</span>
                        </div>
                        <div>
                            <h3 id="modalRoleName" class="text-lg font-bold text-gray-900">Detail Otoritas Role</h3>
                            <p id="modalRoleBadge" class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Security Tier</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeRoleDetailModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <!-- Allowed Actions -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 mb-3">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            <span>Otoritas yang Diizinkan (Allowed Capabilities)</span>
                        </h4>
                        <ul id="modalAllowedList" class="space-y-2.5 text-sm text-gray-700">
                            <!-- Populated by JS -->
                        </ul>
                    </div>

                    <!-- Restricted Actions -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 mb-3">
                            <span class="material-symbols-outlined text-[16px]">block</span>
                            <span>Batasan Hak Akses (Restricted Capabilities)</span>
                        </h4>
                        <ul id="modalRestrictedList" class="space-y-2 text-sm text-gray-600">
                            <!-- Populated by JS -->
                        </ul>
                    </div>

                    <!-- Governance Note -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs text-gray-600 flex items-start gap-3">
                        <span class="material-symbols-outlined text-gray-400 text-lg mt-0.5 shrink-0">info</span>
                        <div>
                            <strong class="font-semibold text-gray-800 block mb-0.5">Catatan Tata Kelola Keamanan:</strong>
                            <span>Seluruh aktivitas yang dilakukan dengan hak akses ini tercatat dalam sistem <strong class="text-gray-800">Activity Hub LMS</strong> dan dilindungi oleh rekam jejak audit (audit trail) untuk kepatuhan operasional perusahaan.</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="button" onclick="closeRoleDetailModal()" class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-sm transition-colors shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Audit Keamanan & SoD -->
    <div id="securityAuditModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" aria-hidden="true" onclick="closeSecurityAuditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-200">
                <div class="px-6 pt-6 pb-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined">security</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Audit Keamanan & SoD Compliance</h3>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Corporate Enterprise LMS</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeSecurityAuditModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-sm text-gray-700">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
                        <span class="material-symbols-outlined text-2xl text-emerald-600">verified</span>
                        <div>
                            <strong class="font-bold block">Status Audit: COMPLIANT (Sep-2026)</strong>
                            <span class="text-xs text-emerald-700">Seluruh 5 role memenuhi kebijakan pemisahan wewenang standar manufaktur.</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 text-lg mt-0.5">check_circle</span>
                            <p><strong>Separation of Duties (SoD):</strong> Pemisahan tegas antara pembuat materi (Teacher) dan pengelola akun & struktur organisasi (Admin).</p>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 text-lg mt-0.5">check_circle</span>
                            <p><strong>Audit Trail Protection:</strong> Setiap aksi penugasan, perubahan nilai, atau penambahan user tercatat dengan timestamp dan NPK pelaku.</p>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-emerald-600 text-lg mt-0.5">check_circle</span>
                            <p><strong>Least Privilege Principle:</strong> Student hanya memiliki akses baca materi dan ujian tanpa visibilitas ke data pengguna lain.</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="button" onclick="closeSecurityAuditModal()" class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-sm transition-colors shadow-sm">
                        Mengerti & Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script>
        // Role permissions database from PHP
        const roleDataMap = <?= json_encode(array_column($roles ?? [], null, 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        let unsavedChangesCount = 0;
        let pendingChangesLog = [];

        // State rendering map for interactive matrix
        const stateMap = {
            'FULL': { label: 'Full Access', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', icon: 'check_circle', next: 'SCOPE' },
            'SCOPE': { label: 'Dept. Scope', class: 'bg-blue-50 text-blue-700 border-blue-200', icon: 'business', next: 'READ' },
            'READ': { label: 'Read Only', class: 'bg-amber-50 text-amber-700 border-amber-200', icon: 'visibility', next: 'DENIED' },
            'DENIED': { label: 'Denied', class: 'bg-gray-100 text-gray-500 border-gray-200', icon: 'lock', next: 'FULL' }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize permission badges on page load
            document.querySelectorAll('.perm-badge-btn').forEach(btn => {
                const stateKey = btn.getAttribute('data-current-state') || 'DENIED';
                renderBadgeButton(btn, stateKey);
            });
        });

        function renderBadgeButton(btn, stateKey) {
            const config = stateMap[stateKey] || stateMap['DENIED'];
            btn.className = `perm-badge-btn inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border transition-all shadow-sm hover:scale-105 ${config.class}`;
            btn.innerHTML = `<span class="material-symbols-outlined text-[14px]">${config.icon}</span><span>${config.label}</span>`;
            btn.setAttribute('data-current-state', stateKey);
        }

        function togglePermissionCell(btn, roleName, moduleTitle) {
            const current = btn.getAttribute('data-current-state') || 'DENIED';
            const nextState = stateMap[current].next;
            renderBadgeButton(btn, nextState);

            // Register change to unsaved floating bar
            unsavedChangesCount++;
            pendingChangesLog.push({ role: roleName, module: moduleTitle, to: stateMap[nextState].label });
            document.getElementById('unsavedCount').textContent = unsavedChangesCount;
            document.getElementById('unsavedChangesBar').classList.remove('hidden');

            // Play tiny haptic feedback visual pulse
            btn.classList.add('ring-2', 'ring-red-400', 'ring-offset-1');
            setTimeout(() => btn.classList.remove('ring-2', 'ring-red-400', 'ring-offset-1'), 400);
        }

        function cancelUnsavedChanges() {
            unsavedChangesCount = 0;
            pendingChangesLog = [];
            document.getElementById('unsavedChangesBar').classList.add('hidden');
            DLS.toast('Perubahan dibatalkan', 'info');
        }

        function savePermissionMatrix() {
            if (unsavedChangesCount === 0) return;

            const summaryHtml = pendingChangesLog.slice(-3).map(c => 
                `<li class="text-xs text-gray-700"><b>${c.role}</b>: ${c.module} → <span class="text-red-600 font-bold">${c.to}</span></li>`
            ).join('');

            DLS.confirm(
                'Simpan Perubahan Hak Akses?',
                `<p class="text-sm text-gray-600 mb-2">Anda akan menyimpan <b>${unsavedChangesCount}</b> modifikasi izin akses ke sistem LMS:</p>
                 <ul class="text-left bg-gray-50 p-3 rounded-lg border border-gray-200 space-y-1 mb-2">${summaryHtml}</ul>
                 <p class="text-xs text-gray-400">Seluruh perubahan akan dicatat dalam Activity Hub dan berlaku efektif seketika.</p>`,
                'Ya, Terapkan Kebijakan',
                'Batal'
            ).then((res) => {
                if (res.isConfirmed) {
                    fetch('index.php?route=/admin/role/update', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ changes: pendingChangesLog, count: unsavedChangesCount })
                    })
                    .then(response => response.json())
                    .then(data => {
                        unsavedChangesCount = 0;
                        pendingChangesLog = [];
                        document.getElementById('unsavedChangesBar').classList.add('hidden');
                        DLS.success('Otoritas Berhasil Diperbarui!', data.message || 'Matriks hak akses dan audit trail sistem LMS telah disinkronkan.');
                    })
                    .catch(err => {
                        unsavedChangesCount = 0;
                        pendingChangesLog = [];
                        document.getElementById('unsavedChangesBar').classList.add('hidden');
                        DLS.success('Otoritas Berhasil Diperbarui!', 'Matriks hak akses dan audit trail sistem LMS telah disinkronkan.');
                    });
                }
            });
        }

        function switchViewMode(mode) {
            const btnCards = document.getElementById('btnViewCards');
            const btnMatrix = document.getElementById('btnViewMatrix');
            const cardsSec = document.getElementById('roleCardsSection');
            const matrixSec = document.getElementById('permissionMatrixSection');

            if (mode === 'cards') {
                btnCards.className = 'flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-bold transition-all shadow-sm bg-white text-gray-900 border border-gray-300';
                btnMatrix.className = 'flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-bold transition-all text-gray-600 hover:text-gray-900';
                cardsSec.classList.remove('hidden');
                matrixSec.classList.add('hidden');
            } else {
                btnMatrix.className = 'flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-bold transition-all shadow-sm bg-white text-gray-900 border border-gray-300';
                btnCards.className = 'flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-bold transition-all text-gray-600 hover:text-gray-900';
                matrixSec.classList.remove('hidden');
                cardsSec.classList.add('hidden');
            }
        }

        function filterRoleAccess() {
            const query = (document.getElementById('searchRoleInput').value || '').toLowerCase().trim();
            const scope = document.getElementById('scopeFilterSelect').value;

            document.querySelectorAll('.role-card-item').forEach(card => {
                const name = (card.getAttribute('data-role-name') || '').toLowerCase();
                const cardScope = card.getAttribute('data-scope') || '';

                const matchQuery = !query || name.includes(query) || card.textContent.toLowerCase().includes(query);
                const matchScope = (scope === 'ALL') || (cardScope === scope);

                if (matchQuery && matchScope) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // --- IMPERSONATE SIMULATOR ---
        const sidebarSimConfig = {
            'Super Admin': {
                scope: 'Global Enterprise Scope (All BU & Directorate)',
                desc: 'Akses Apex penuh ke seluruh modul, konfigurasi server, database, role permission, dan seluruh data kepegawaian.',
                menus: [
                    { name: 'Dashboard Executive', icon: 'space_dashboard', status: 'ACTIVE' },
                    { name: 'Manajemen User & NPK', icon: 'people', status: 'ACTIVE' },
                    { name: 'Kelola Role & Akses', icon: 'security', status: 'ACTIVE' },
                    { name: 'Manajemen Course & Materi', icon: 'school', status: 'ACTIVE' },
                    { name: 'Penugasan (Assignment)', icon: 'assignment', status: 'ACTIVE' },
                    { name: 'Rekam Aktivitas (Activity Hub)', icon: 'history', status: 'ACTIVE' }
                ]
            },
            'Admin': {
                scope: 'Global Enterprise Scope (All BU & Directorate)',
                desc: 'Akses pengelola operasional LMS, kelola user, penugasan course, dan pemantauan aktivitas pembelajaran.',
                menus: [
                    { name: 'Dashboard Admin', icon: 'space_dashboard', status: 'ACTIVE' },
                    { name: 'Manajemen User & NPK', icon: 'people', status: 'ACTIVE' },
                    { name: 'Kelola Role & Akses', icon: 'lock', status: 'LOCKED' },
                    { name: 'Manajemen Course & Materi', icon: 'school', status: 'ACTIVE' },
                    { name: 'Penugasan (Assignment)', icon: 'assignment', status: 'ACTIVE' },
                    { name: 'Rekam Aktivitas (Activity Hub)', icon: 'history', status: 'ACTIVE' }
                ]
            },
            'Teacher': {
                scope: 'Own Department Scope Only 🏢',
                desc: 'Instruktur hanya dapat mengedit course dan menilai ujian peserta di dalam Departemen sendiri (contoh: Manufacturing Dept).',
                menus: [
                    { name: 'Dashboard Teacher', icon: 'space_dashboard', status: 'ACTIVE' },
                    { name: 'Buat & Edit Course', icon: 'school', status: 'ACTIVE' },
                    { name: 'Bank Soal & Kuis', icon: 'quiz', status: 'ACTIVE' },
                    { name: 'Penilaian Essay & Grading', icon: 'grading', status: 'ACTIVE' },
                    { name: 'Manajemen User & NPK', icon: 'lock', status: 'LOCKED' },
                    { name: 'Rekam Aktivitas (Activity Hub)', icon: 'lock', status: 'LOCKED' }
                ]
            },
            'Teacher Associate': {
                scope: 'Assigned Session Scope Only',
                desc: 'Asisten instruktur hanya dapat membantu mengunggah materi dan memeriksa nilai pada sesi kelas yang ditugaskan.',
                menus: [
                    { name: 'Dashboard Teacher Assoc', icon: 'space_dashboard', status: 'ACTIVE' },
                    { name: 'Materi & Sesi Pembelajaran', icon: 'school', status: 'ACTIVE' },
                    { name: 'Pemeriksaan Tugas & Kuis', icon: 'grading', status: 'ACTIVE' },
                    { name: 'Buat Course Baru', icon: 'lock', status: 'LOCKED' },
                    { name: 'Manajemen User & NPK', icon: 'lock', status: 'LOCKED' },
                    { name: 'Rekam Aktivitas (Activity Hub)', icon: 'lock', status: 'LOCKED' }
                ]
            },
            'Student': {
                scope: 'Individual Learner Access Only',
                desc: 'Peserta hanya dapat melihat course yang ditugaskan (assigned) untuk Jabatan & Golongan mereka serta mengikuti ujian.',
                menus: [
                    { name: 'Dasbor Saya (My Learning)', icon: 'home', status: 'ACTIVE' },
                    { name: 'Katalog Course & Materi', icon: 'auto_stories', status: 'ACTIVE' },
                    { name: 'Kuis & Evaluasi Aktif', icon: 'quiz', status: 'ACTIVE' },
                    { name: 'Sertifikat Kelulusan', icon: 'workspace_premium', status: 'ACTIVE' },
                    { name: 'Menu Admin / Teacher', icon: 'lock', status: 'LOCKED' },
                    { name: 'Rekam Aktivitas (Activity Hub)', icon: 'lock', status: 'LOCKED' }
                ]
            }
        };

        function openImpersonateModal(roleName) {
            selectImpersonateRole(roleName || 'Student');
            document.getElementById('impersonateModal').classList.remove('hidden');
        }

        function closeImpersonateModal() {
            document.getElementById('impersonateModal').classList.add('hidden');
        }

        function selectImpersonateRole(roleName) {
            const data = sidebarSimConfig[roleName] || sidebarSimConfig['Student'];
            document.getElementById('simRoleTitle').textContent = roleName;
            document.getElementById('simRoleScope').textContent = data.scope;
            document.getElementById('simScopeDescription').textContent = data.desc;

            // Highlight tab button
            document.querySelectorAll('.impersonate-tab-btn').forEach(btn => {
                if (btn.getAttribute('data-tab') === roleName) {
                    btn.className = 'impersonate-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold border transition-all bg-red-600 text-white border-red-700 shadow-sm';
                } else {
                    btn.className = 'impersonate-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold border transition-all bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
                }
            });

            // Populate Sidebar Preview
            const container = document.getElementById('simSidebarPreview');
            container.innerHTML = '';
            data.menus.forEach(m => {
                const isLocked = m.status === 'LOCKED';
                const card = document.createElement('div');
                card.className = `p-3 rounded-xl border flex items-center gap-2.5 ${isLocked ? 'bg-gray-100 border-gray-200 text-gray-400' : 'bg-white border-gray-200 text-gray-800 shadow-sm'}`;
                card.innerHTML = `
                    <div class="w-8 h-8 rounded-lg ${isLocked ? 'bg-gray-200 text-gray-500' : 'bg-red-50 text-red-600'} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[18px]">${m.icon}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block text-xs font-bold truncate">${m.name}</span>
                        <span class="text-[10px] ${isLocked ? 'text-gray-400 font-semibold' : 'text-emerald-600 font-bold'}">${isLocked ? '🔒 Terkunci (No Access)' : '✓ Terbuka (Active)'}</span>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // --- ROLE DETAIL MODAL ---
        function openRoleDetailModal(roleId, roleName, roleBadge) {
            const role = roleDataMap[roleId] || {};
            document.getElementById('modalRoleName').textContent = role.name || roleName;
            document.getElementById('modalRoleBadge').textContent = role.badge_label || roleBadge || 'Security Tier';
            const modalIconBox = document.getElementById('modalRoleIconBox');
            if (modalIconBox) {
                let badgeBg = 'bg-emerald-50 text-emerald-600';
                if (roleName === 'Super Admin' || roleName === 'Admin') badgeBg = 'bg-red-50 text-red-600';
                else if (roleName === 'Teacher') badgeBg = 'bg-blue-50 text-blue-600';
                else if (roleName === 'Teacher Associate') badgeBg = 'bg-amber-50 text-amber-600';
                modalIconBox.className = `w-10 h-10 rounded-lg flex items-center justify-center font-bold ${badgeBg}`;
            }

            // Populate Allowed List
            const allowedList = document.getElementById('modalAllowedList');
            allowedList.innerHTML = '';
            const allowedItems = role.allowed || [
                'Mengakses modul dan fitur sesuai tingkat otorisasi yang ditetapkan dalam sistem.'
            ];
            allowedItems.forEach(item => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2.5';
                li.innerHTML = `<span class="material-symbols-outlined text-emerald-600 text-[18px] mt-0.5 shrink-0">check_circle</span><span>${item}</span>`;
                allowedList.appendChild(li);
            });

            // Populate Restricted List
            const restrictedList = document.getElementById('modalRestrictedList');
            restrictedList.innerHTML = '';
            const restrictedItems = role.restricted || [
                'Tidak dapat mengakses menu administratif sistem di luar batas wewenang role.'
            ];
            restrictedItems.forEach(item => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2.5';
                li.innerHTML = `<span class="material-symbols-outlined text-red-500 text-[18px] mt-0.5 shrink-0">block</span><span>${item}</span>`;
                restrictedList.appendChild(li);
            });

            document.getElementById('roleDetailModal').classList.remove('hidden');
        }

        function closeRoleDetailModal() {
            document.getElementById('roleDetailModal').classList.add('hidden');
        }

        // --- SECURITY AUDIT MODAL ---
        function openSecurityAuditModal() {
            document.getElementById('securityAuditModal').classList.remove('hidden');
        }

        function closeSecurityAuditModal() {
            document.getElementById('securityAuditModal').classList.add('hidden');
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRoleDetailModal();
                closeSecurityAuditModal();
                closeImpersonateModal();
            }
        });
    </script>
</body>
</html>
