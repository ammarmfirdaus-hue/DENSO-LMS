<?php
/**
 * Tampilan Manajemen User Admin DLS (DLS Admin User View).
 */

$header_title = 'Manajemen User';
$active_tab = 'user';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola User LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar (Desktop) / Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="font-headline-lg font-bold text-gray-900">Data Karyawan & User</h1>
                <p class="text-sm text-gray-500 mt-1">Atur pendaftaran, deaktivasi, serta pemberian akses Role LMS.</p>
            </div>
            
            <button class="bg-red-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg active:scale-95 transition-all hover:bg-red-700 hover:shadow-md flex items-center gap-2 shadow-sm shrink-0" onclick="alert('Fitur tambah form muncul!')">
                <span class="material-symbols-outlined text-[18px]">person_add</span> Tambah User Baru
            </button>
        </div>

        <!-- KPI Cards (Bento Grid) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">group</span>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900"><?= count($users) ?></div>
                    <div class="text-xs font-medium text-gray-500">Total Karyawan</div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">school</span>
                </div>
                <div>
                    <?php 
                    $teacher_count = 0;
                    foreach($users as $u) if($u['role'] === 'Teacher') $teacher_count++;
                    ?>
                    <div class="text-2xl font-bold text-gray-900"><?= $teacher_count ?></div>
                    <div class="text-xs font-medium text-gray-500">Active Teachers</div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">person</span>
                </div>
                <div>
                    <?php 
                    $student_count = 0;
                    foreach($users as $u) if($u['role'] === 'Student') $student_count++;
                    ?>
                    <div class="text-2xl font-bold text-gray-900"><?= $student_count ?></div>
                    <div class="text-xs font-medium text-gray-500">Active Students</div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">how_to_reg</span>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">0</div>
                    <div class="text-xs font-medium text-gray-500">Menunggu Approval</div>
                </div>
            </div>
        </div>

        <!-- Smart Toolbar -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-5">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 text-[20px]">search</span>
                    <input type="text" placeholder="Cari berdasarkan nama atau NPK..." class="w-full bg-gray-50 pl-10 pr-16 py-2.5 rounded-lg border border-gray-200 text-sm text-gray-900 focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-100 transition-colors">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                        <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 bg-white border border-gray-200 rounded">Ctrl</kbd>
                        <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 bg-white border border-gray-200 rounded">K</kbd>
                    </div>
                </div>
                <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-[18px]">tune</span> Filter
                    <span class="flex items-center justify-center w-5 h-5 ml-1 text-[10px] font-bold text-white bg-red-600 rounded-full">3</span>
                </button>
            </div>
            <!-- Active Filter Pills (Mockup) -->
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg">
                    Departemen: <span class="font-bold">Manufacturing</span>
                    <button class="hover:text-red-600 transition-colors flex"><span class="material-symbols-outlined text-[14px]">close</span></button>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg">
                    Role: <span class="font-bold">Semua</span>
                    <button class="hover:text-red-600 transition-colors flex"><span class="material-symbols-outlined text-[14px]">close</span></button>
                </span>
            </div>
        </div>

        <!-- Tabel Data Revamp -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 flex flex-col">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse min-w-[850px]">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-4 py-3.5 border-b border-gray-200 w-[40px] text-center">
                                <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            </th>
                            <th class="px-4 py-3.5 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-4 py-3.5 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jabatan & Dept</th>
                            <th class="px-4 py-3.5 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role Status</th>
                            <th class="px-4 py-3.5 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">Akses Tambahan</th>
                            <th class="px-4 py-3.5 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($users)): ?>
                            <?php 
                            // Warna background acak untuk avatar
                            $avatar_colors = ['bg-amber-100 text-amber-700', 'bg-emerald-100 text-emerald-700', 'bg-indigo-100 text-indigo-700', 'bg-pink-100 text-pink-700', 'bg-cyan-100 text-cyan-700'];
                            foreach ($users as $index => $user): 
                                $color = $avatar_colors[$index % count($avatar_colors)];
                                $initials = substr($user['name'], 0, 1);
                                if(strpos($user['name'], ' ') !== false) {
                                    $parts = explode(' ', $user['name']);
                                    $initials = substr($parts[0], 0, 1) . substr($parts[1], 0, 1);
                                }
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors group relative">
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full <?= $color ?> flex items-center justify-center font-bold text-sm shrink-0 border border-black/5">
                                                <?= strtoupper($initials) ?>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-900 text-sm leading-tight"><?= e($user['name']) ?></span>
                                                <span class="text-xs text-gray-500 mt-0.5">NPK: <?= e($user['npk']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="text-sm font-medium text-gray-900"><?= e($user['jabatan']) ?> <span class="text-gray-400 font-normal ml-1">&bull; <?= e($user['golongan']) ?></span></div>
                                        <div class="text-xs text-gray-500 mt-0.5"><?= e($user['departemen']) ?></div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <?php 
                                        $role_cls = '';
                                        $dot_cls = '';
                                        if($user['role'] === 'Admin') {
                                            $role_cls = 'bg-red-50 text-red-700 ring-red-200';
                                            $dot_cls = 'bg-red-500';
                                        } elseif($user['role'] === 'Teacher') {
                                            $role_cls = 'bg-blue-50 text-blue-700 ring-blue-200';
                                            $dot_cls = 'bg-blue-500';
                                        } else {
                                            $role_cls = 'bg-green-50 text-green-700 ring-green-200';
                                            $dot_cls = 'bg-green-500';
                                        }
                                        ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset <?= $role_cls ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $dot_cls ?>"></span>
                                            <?= e($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <?php if (count($user['access']) > 0): ?>
                                            <div class="flex flex-wrap gap-1.5">
                                                <?php foreach ($user['access'] as $acc): ?>
                                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-600 border border-gray-200"><?= e($acc) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-red-600 transition-colors shadow-sm" aria-label="Edit Profile">
                                                <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                                            </button>
                                            <button class="inline-flex items-center justify-center w-7 h-7 text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm" aria-label="More Options">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <span class="material-symbols-outlined text-[48px] text-gray-300">search_off</span>
                                    <p class="mt-2 text-sm font-semibold text-gray-900">Tidak ada pengguna ditemukan</p>
                                    <p class="mt-1 text-xs text-gray-500">Coba sesuaikan kata kunci pencarian atau filter yang aktif.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-3">
                <span class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-900">1 - 20</span> dari <span class="font-semibold text-gray-900">245</span> Karyawan</span>
                <div class="flex items-center gap-1">
                    <button class="px-2 py-1 text-gray-400 border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50" disabled>
                        <span class="material-symbols-outlined text-[18px] block">chevron_left</span>
                    </button>
                    <button class="px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded">1</button>
                    <button class="px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 rounded">2</button>
                    <button class="px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 rounded">3</button>
                    <span class="px-2 text-gray-400">...</span>
                    <button class="px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 rounded">25</button>
                    <button class="px-2 py-1 text-gray-500 border border-gray-200 rounded hover:bg-gray-100 bg-white shadow-sm">
                        <span class="material-symbols-outlined text-[18px] block">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>

    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
</body>
</html>
