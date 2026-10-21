<?php
/**
 * Tampilan Pengaturan Penugasan (Assignment Flow) Admin DLS - E.
 */

$header_title = 'Penugasan & Assignment';
$active_tab = 'assignment';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Assignment LMS - Pusat Penugasan Course';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 overflow-y-auto pt-[96px] px-container-margin pb-[88px] lg:ml-[240px]">
        
        <!-- Welcome Section & Actions -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg font-bold text-[clamp(16px,5.5vw,26px)] tracking-tight leading-tight text-on-surface">Pusat Penugasan Course</h1>
                <p class="font-body-md text-on-surface-variant">Assign target partisipan per course sesuai jabatan dan golongan.</p>
            </div>
            
            <div class="flex items-center gap-2.5 w-full md:w-auto justify-end">
                <!-- Tombol Panduan Assignment (Modal alur 1-2-3) -->
                <button class="bg-surface text-on-surface border border-outline-variant hover:bg-surface-variant font-label-md px-4 py-2.5 rounded-lg active:scale-95 transition-all flex items-center gap-2 shadow-sm" onclick="showAssignmentGuide()">
                    <span class="material-symbols-outlined text-secondary">help</span> Panduan Penugasan
                </button>

                <!-- Tombol Buat Penugasan Baru (Denso Corporate Red) -->
                <button class="bg-red-600 hover:bg-red-700 text-white font-label-md px-5 py-2.5 rounded-lg active:scale-95 transition-all shadow-md hover:shadow-lg flex items-center gap-2" onclick="showNewAssignmentModal()">
                    <span class="material-symbols-outlined">add_task</span> Buat Penugasan Baru
                </button>
            </div>
        </div>

        <!-- 1. Enterprise KPI Bento Grid (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Card 1: Total Course Ditugaskan -->
            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.04)] border border-surface-variant flex items-center justify-between hover:shadow-md transition-shadow">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Total Course Ditugaskan</span>
                    <div class="text-2xl font-bold text-on-surface mt-1">18 <span class="text-xs font-normal text-gray-500">Course</span></div>
                    <span class="text-[11px] text-gray-400 mt-1 block">Dari total 24 master course</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center border border-red-100">
                    <span class="material-symbols-outlined text-2xl">menu_book</span>
                </div>
            </div>

            <!-- Card 2: Total Karyawan Terjangkau -->
            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.04)] border border-surface-variant flex items-center justify-between hover:shadow-md transition-shadow">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Karyawan Terjangkau</span>
                    <div class="text-2xl font-bold text-on-surface mt-1">390 <span class="text-xs font-normal text-gray-500">Karyawan</span></div>
                    <span class="text-[11px] text-emerald-600 font-medium mt-1 block">▲ 100% dari target aktif</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <span class="material-symbols-outlined text-2xl">groups</span>
                </div>
            </div>

            <!-- Card 3: Penugasan Aktif (Tersalurkan) -->
            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.04)] border border-surface-variant flex items-center justify-between hover:shadow-md transition-shadow">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Tersalurkan (Active)</span>
                    <div class="text-2xl font-bold text-on-surface mt-1">14 <span class="text-xs font-normal text-gray-500">Aturan</span></div>
                    <span class="text-[11px] text-gray-400 mt-1 block">Berjalan di akun Student</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <span class="material-symbols-outlined text-2xl">task_alt</span>
                </div>
            </div>

            <!-- Card 4: Draft Penugasan -->
            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.04)] border border-surface-variant flex items-center justify-between hover:shadow-md transition-shadow">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Draft Penugasan</span>
                    <div class="text-2xl font-bold text-on-surface mt-1">4 <span class="text-xs font-normal text-gray-500">Draft</span></div>
                    <span class="text-[11px] text-amber-600 font-medium mt-1 block">Menunggu verifikasi admin</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <span class="material-symbols-outlined text-2xl">pending_actions</span>
                </div>
            </div>
        </div>

        <!-- 2. Smart Toolbar (Search & Filters) -->
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.04)] border border-surface-variant mb-6 flex flex-col md:flex-row gap-3 items-center justify-between">
            <!-- Search Bar with shortcut hint -->
            <div class="relative w-full md:w-80">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
                <input type="text" placeholder="Cari course, target, atau admin..." class="w-full pl-9 pr-14 py-2 bg-gray-50/50 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-red-600 focus:bg-white transition-colors" />
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-0.5 pointer-events-none">
                    <kbd class="px-1.5 py-0.5 text-[10px] text-gray-400 bg-white border border-gray-200 rounded font-sans">Ctrl</kbd>
                    <kbd class="px-1.5 py-0.5 text-[10px] text-gray-400 bg-white border border-gray-200 rounded font-sans">K</kbd>
                </div>
            </div>

            <!-- Filter Pills -->
            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap mr-1">Filter :</span>
                
                <div class="relative">
                    <select class="appearance-none bg-gray-50/70 border border-gray-200 text-gray-700 text-xs rounded-lg pl-3 pr-8 py-2 focus:outline-none focus:border-red-600 cursor-pointer font-medium">
                        <option>Tipe Target: Semua</option>
                        <option>Jabatan & Golongan</option>
                        <option>Departemen</option>
                        <option>Spesifik User</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">expand_more</span>
                </div>

                <div class="relative">
                    <select class="appearance-none bg-gray-50/70 border border-gray-200 text-gray-700 text-xs rounded-lg pl-3 pr-8 py-2 focus:outline-none focus:border-red-600 cursor-pointer font-medium">
                        <option>Status: Semua</option>
                        <option>Tersalurkan (Active)</option>
                        <option>Draft</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">expand_more</span>
                </div>

                <button class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg border border-red-100 transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[16px]">tune</span> Filter (2)
                </button>
            </div>
        </div>

        <!-- 3. Full-Width Enterprise Assignment Table -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_4px_12px_0_rgba(0,0,0,0.04)] border border-surface-variant overflow-hidden">
            <div class="p-stack-md border-b border-surface-variant flex justify-between items-center bg-gray-50/50">
                <h2 class="font-headline-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600">list_alt</span> Daftar Distribusi Penugasan
                </h2>
                <span class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-900"><?= count($assignments) ?></span> aturan distribusi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left font-body-md min-w-[850px]">
                    <thead class="bg-surface-container-high border-b border-surface-variant text-xs uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                            </th>
                            <th class="px-4 py-3.5 font-bold">Course & Waktu</th>
                            <th class="px-4 py-3.5 font-bold">Target Mapping</th>
                            <th class="px-4 py-3.5 font-bold">Estimasi Peserta</th>
                            <th class="px-4 py-3.5 font-bold">Dibuat Oleh</th>
                            <th class="px-4 py-3.5 font-bold text-center">Status</th>
                            <th class="px-4 py-3.5 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-variant">
                        <?php foreach ($assignments as $idx => $asg): ?>
                            <?php
                            // Mapping warna badge target type
                            $badge_class = 'bg-gray-50 text-gray-700 border-gray-200';
                            $badge_icon = 'label';
                            if ($asg['target_type'] === 'Jabatan & Golongan') {
                                $badge_class = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                $badge_icon = 'work';
                            } elseif ($asg['target_type'] === 'Departemen') {
                                $badge_class = 'bg-teal-50 text-teal-700 border-teal-200';
                                $badge_icon = 'domain';
                            } elseif ($asg['target_type'] === 'Spesifik User') {
                                $badge_class = 'bg-amber-50 text-amber-700 border-amber-200';
                                $badge_icon = 'person_add';
                            }

                            // Course icon
                            $icons = ['menu_book', 'verified', 'build', 'health_and_safety', 'engineering', 'school'];
                            $course_icon = $icons[$idx % count($icons)];
                            
                            // Estimasi peserta fallback
                            $est_participants = isset($asg['est_participants']) ? $asg['est_participants'] : 30;
                            ?>
                            <tr class="hover:bg-red-50/30 group transition-colors relative">
                                <!-- Checkbox -->
                                <td class="px-4 py-4 text-center">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <input type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                                </td>

                                <!-- Course & Waktu -->
                                <td class="px-4 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center text-red-600 flex-shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                                            <span class="material-symbols-outlined text-[20px]"><?= $course_icon ?></span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 group-hover:text-red-600 cursor-pointer transition-colors" onclick="window.location.href='?route=/admin/course/detail&id=101'">
                                                <?= e($asg['course_title']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                                    <?= e($asg['date']) ?>
                                                </span>
                                                <?php if (!empty($asg['teacher_name'])): ?>
                                                    <span class="text-indigo-600 font-semibold flex items-center gap-0.5">
                                                        <span class="material-symbols-outlined text-[13px]">person</span>
                                                        PIC: <?= e($asg['teacher_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Target Mapping -->
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold border rounded-full <?= $badge_class ?> mb-1">
                                        <span class="material-symbols-outlined text-[14px]"><?= $badge_icon ?></span>
                                        <?= e($asg['target_type']) ?>
                                    </span>
                                    <div class="text-xs font-bold text-gray-800 tracking-tight mt-0.5">
                                        <?= e($asg['target_value']) ?>
                                    </div>
                                </td>

                                <!-- Estimasi Peserta -->
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-lg">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">group</span>
                                        ~<?= $est_participants ?> Karyawan
                                    </span>
                                </td>

                                <!-- Dibuat Oleh -->
                                <td class="px-4 py-4 text-xs font-semibold text-gray-800">
                                    <?php if ($asg['assigned_by'] === 'Pending' || $asg['assigned_by'] === '-'): ?>
                                        <span class="italic text-gray-400">Pending</span>
                                    <?php else: ?>
                                        <?= e($asg['assigned_by']) ?>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-4 text-center">
                                    <?php if ($asg['status'] === 'Active'): ?>
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 text-[11px] font-bold rounded-full uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Tersalurkan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 text-[11px] font-bold rounded-full uppercase tracking-wider">
                                            <span class="material-symbols-outlined text-[14px]">edit_document</span>
                                            Draft
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-red-600 transition-colors shadow-sm" aria-label="Edit Penugasan">
                                            <span class="material-symbols-outlined text-[15px]">edit</span> Edit
                                        </button>
                                        <button class="inline-flex items-center justify-center w-7 h-7 text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm" title="Opsi Lainnya">
                                            <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-3">
                <span class="text-xs text-gray-500">Menampilkan <span class="font-semibold text-gray-900">1 - 20</span> dari <span class="font-semibold text-gray-900">18</span> Penugasan</span>
                <div class="flex items-center gap-1">
                    <button class="px-2 py-1 text-gray-400 border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50" disabled>
                        <span class="material-symbols-outlined text-[18px] block">chevron_left</span>
                    </button>
                    <button class="px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded">1</button>
                    <button class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded">2</button>
                    <button class="px-2 py-1 text-gray-600 border border-gray-200 rounded hover:bg-gray-100">
                        <span class="material-symbols-outlined text-[18px] block">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script>
        // Modal Panduan Alur Assignment (Simple & Professional)
        function showAssignmentGuide() {
            Swal.fire({
                title: false,
                width: 520,
                padding: 0,
                showConfirmButton: true,
                confirmButtonText: 'Tutup',
                customClass: {
                    popup: 'rounded-xl overflow-hidden border border-gray-200 shadow-xl text-left bg-white',
                    confirmButton: 'bg-gray-900 hover:bg-gray-800 text-white rounded-lg px-5 py-2 font-semibold text-xs border-0 shadow-sm my-4 mx-6'
                },
                html: `
                    <div class="px-6 pt-6 pb-4 border-b border-gray-200">
                        <h3 class="text-base font-bold text-gray-900">Panduan Penugasan Course</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Alur kerja distribusi pembelajaran karyawan PT Denso Indonesia</p>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-red-50 border border-red-200 text-red-600 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-xs">Pilih Master Course</h4>
                                <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">Pilih course dari katalog utama yang telah diverifikasi (K3, Technical, ISO, Soft Skills) dan siap disalurkan.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-red-50 border border-red-200 text-red-600 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-xs">Tentukan Target Karyawan</h4>
                                <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">Hubungkan course berdasarkan Jabatan & Golongan, Departemen / Line Produksi, atau Spesifik Karyawan.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-red-50 border border-red-200 text-red-600 font-bold flex items-center justify-center flex-shrink-0 text-xs">3</div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-xs">Penyaluran Otomatis (HR Sync)</h4>
                                <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">Sistem mendistribusikan penugasan dan notifikasi ke akun Student secara otomatis tanpa input manual.</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-700">
                            <span class="font-semibold text-gray-900 block mb-0.5">Catatan Kepatuhan (Compliance)</span>
                            Pastikan penugasan keselamatan kerja (K3/ISO) disalurkan kepada seluruh operator sebelum jadwal assessment berkala.
                        </div>
                    </div>
                `
            });
        }

        // Data master untuk modal assignment
        const teachersList = <?= json_encode($teachers ?? []) ?>;
        const departmentsList = <?= json_encode($departments ?? []) ?>;
        const positionsList = <?= json_encode($positions ?? []) ?>;
        const golongansList = <?= json_encode($golongans ?? []) ?>;

        // Modal Buat Penugasan Baru (Connected to SQL Server)
        function showNewAssignmentModal() {
            let teacherOptions = '<option value="">-- Pilih Pengajar (Teacher PIC) --</option>';
            teachersList.forEach(t => {
                const sub = (t.teacher_type ? `[${t.teacher_type}] ` : '') + (t.department ? ` - ${t.department}` : '');
                teacherOptions += `<option value="${t.employee_id}">${t.fullname} (${t.jabatan || 'Teacher'})${sub}</option>`;
            });

            let deptOptions = '<option value="">-- Semua Departemen --</option>';
            departmentsList.forEach(d => {
                deptOptions += `<option value="${d.department_id}">${d.department_name}</option>`;
            });

            let posOptions = '<option value="">-- Semua Jabatan --</option>';
            positionsList.forEach(p => {
                posOptions += `<option value="${p.position_id}">${p.position_name}</option>`;
            });

            let golOptions = '<option value="">-- Semua Golongan --</option>';
            golongansList.forEach(g => {
                golOptions += `<option value="${g.golongan_id}">${g.golongan_name}</option>`;
            });

            Swal.fire({
                title: false,
                width: 560,
                padding: 0,
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Kirimkan Penugasan',
                cancelButtonText: 'Batal',
                focusConfirm: false,
                customClass: {
                    popup: 'rounded-2xl overflow-hidden border border-gray-200 shadow-2xl text-left bg-white',
                    confirmButton: 'bg-red-600 hover:bg-red-700 text-white rounded-xl px-5 py-2.5 font-bold text-xs border-0 shadow-sm ml-2 transition-all',
                    cancelButton: 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl font-bold text-xs transition-all'
                },
                html: `
                    <div class="px-6 pt-6 pb-4 border-b border-gray-200 bg-gray-50/60">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-bold">
                                <span class="material-symbols-outlined text-[20px]">add_task</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 leading-tight">Buat Penugasan Course Baru</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Tugaskan Teacher menyusun materi & atur kriteria peserta</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <!-- Judul Course -->
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-red-600">title</span> Judul Course yang Ditugaskan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="asg-title" placeholder="Contoh: Standarisasi K3 Produksi & APD Plant 1" class="w-full p-2.5 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-gray-900" />
                        </div>

                        <!-- Pilih Teacher PIC -->
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-red-600">person_pin</span> Pengajar (Teacher PIC) <span class="text-red-500">*</span>
                            </label>
                            <select id="asg-teacher" class="w-full p-2.5 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-gray-900 font-medium">
                                ${teacherOptions}
                            </select>
                        </div>

                        <!-- Grid Kategori & SLA -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 mb-1.5">Kategori Pelatihan</label>
                                <select id="asg-category" class="w-full p-2.5 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-gray-800">
                                    <option value="Mandat Korporat">Mandat Korporat</option>
                                    <option value="K3 & Safety">K3 & Keselamatan Kerja</option>
                                    <option value="Teknik & Manufaktur">Teknik & Manufaktur</option>
                                    <option value="Quality & ISO">Quality & ISO</option>
                                    <option value="Soft Skills">Soft Skills & Leadership</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-800 mb-1.5">Target Penyusunan (SLA)</label>
                                <select id="asg-sla" class="w-full p-2.5 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-gray-800">
                                    <option value="14">14 Hari Kerja (Standard)</option>
                                    <option value="7">7 Hari Kerja (Mendesak / Safety)</option>
                                    <option value="30">30 Hari Kerja (Reguler)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kriteria Target Audiens Peserta -->
                        <div class="p-4 bg-red-50/40 rounded-xl border border-red-100 space-y-3">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-red-600 text-[18px]">group_add</span>
                                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Kriteria Target Peserta Mandat</h4>
                            </div>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Sistem akan memfilter peserta otomatis di Step 3 Pembuatan Course Pengajar berdasarkan kriteria ini.
                            </p>

                            <div class="space-y-2.5 pt-1">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Departemen Sasaran</label>
                                    <select id="asg-dept" class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-500 bg-white text-gray-800">
                                        ${deptOptions}
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Jabatan Sasaran</label>
                                        <select id="asg-pos" class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-500 bg-white text-gray-800">
                                            ${posOptions}
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Golongan Sasaran</label>
                                        <select id="asg-gol" class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-500 bg-white text-gray-800">
                                            ${golOptions}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan / Arahan Admin -->
                        <div>
                            <label class="block text-xs font-bold text-gray-800 mb-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-red-600">note_alt</span> Catatan / Arahan Khusus ke Pengajar
                            </label>
                            <textarea id="asg-notes" rows="2" placeholder="Tulis instruksi khusus (misal: harap sertakan video operasional dan pre-test)..." class="w-full p-2.5 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-gray-900"></textarea>
                        </div>
                    </div>
                `,
                preConfirm: () => {
                    const title = document.getElementById('asg-title')?.value.trim();
                    const teacherId = document.getElementById('asg-teacher')?.value;
                    const category = document.getElementById('asg-category')?.value;
                    const slaDays = document.getElementById('asg-sla')?.value;
                    const deptId = document.getElementById('asg-dept')?.value;
                    const posId = document.getElementById('asg-pos')?.value;
                    const golId = document.getElementById('asg-gol')?.value;
                    const notes = document.getElementById('asg-notes')?.value.trim();

                    if (!title) {
                        Swal.showValidationMessage('Judul course wajib diisi');
                        return false;
                    }
                    if (!teacherId) {
                        Swal.showValidationMessage('Pilih Pengajar (Teacher PIC) yang ditugaskan');
                        return false;
                    }

                    return {
                        title: title,
                        teacher_id: teacherId,
                        category: category,
                        sla_days: slaDays,
                        department_id: deptId || null,
                        position_id: posId || null,
                        golongan_id: golId || null,
                        notes: notes
                    };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Menyimpan Penugasan...',
                        text: 'Meneruskan penugasan dan mengirim notifikasi ke pengajar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('?route=/admin/assignment/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(result.value)
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Penugasan Berhasil!',
                                text: res.message,
                                confirmButtonText: 'Selesai',
                                customClass: {
                                    confirmButton: 'bg-red-600 hover:bg-red-700 text-white rounded-xl px-5 py-2 font-bold text-xs'
                                }
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan',
                                text: res.message || 'Terjadi kesalahan sistem.',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Error assigning course:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Gagal',
                            text: 'Gagal menghubungi server untuk menyimpan penugasan.',
                            confirmButtonText: 'Tutup'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
