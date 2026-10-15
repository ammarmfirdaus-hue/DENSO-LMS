<?php
/**
 * Tampilan Manajemen Course Admin DLS (DLS Admin Course View).
 */

$header_title = 'Manajemen Course';
$active_tab = 'course';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Course LMS';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col pt-safe">

    <!-- Sidebar & Header -->
    <?php include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
    <?php include 'includes/components/DX_LMS_M_Header.php'; ?>

    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[88px]">
        
        <?php
        $current_filter = strtolower($_GET['status'] ?? 'all');

        $filteredCourses = [];
        foreach ($courses as $c) {
            // Because we're using Teacher_MockData, we use 'status'.
            // But user said: "karena di admin jadi tidak perlu ada rejected dan private"
            if ($c['status'] === 'Rejected' || $c['status'] === 'Private') {
                continue;
            }

            // Map teacher statuses into display statuses for Admin if needed,
            // but we'll stick to Teacher's wording for styling compatibility.
            $displayStat = $c['status'];

            if ($current_filter === 'all' || strtolower($displayStat) === $current_filter) {
                $filteredCourses[] = $c;
            }
        }

        // Custom sort when "all" is active: pending review (needs action) -> In Progress -> Completed -> Archived
        if ($current_filter === 'all') {
            $priority = [
                'Pending Review' => 1,
                'In Progress' => 2,
                'Completed' => 3,
                'Archived' => 4,
            ];
            usort($filteredCourses, function ($a, $b) use ($priority) {
                $pA = $priority[$a['status']] ?? 99;
                $pB = $priority[$b['status']] ?? 99;
                return $pA <=> $pB;
            });
        }
        ?>

        <section class="w-full space-y-stack-md">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <div>
                    <h1 class="font-headline-lg font-bold text-on-surface">Data Course & Pelatihan</h1>
                    <p class="font-body-md text-on-surface-variant">Manajemen materi, pelacakan sesi, dan pengaturan publikasi.</p>
                </div>
                
                <button onclick="Swal.fire({title: 'Segera Hadir!', text: 'Fitur tambah course muncul!', icon: 'info', confirmButtonColor: '#b4000b'})" class="inline-flex items-center gap-1.5 bg-white text-error border border-error font-label-md font-bold px-4 py-2 rounded-lg hover:bg-error/5 hover:border-error transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Buat Course Baru
                </button>
            </div>

            <!-- Filter Tabs -->
            <div class="relative w-full -mx-container-margin px-container-margin md:mx-0 md:px-0 mb-4 sticky lg:relative top-14 lg:top-0 bg-surface z-20 py-2">
                <div class="flex gap-2 overflow-x-auto no-scrollbar snap-x py-1">
                    <?php
                    $tabsInfo = [
                        ['id' => 'all', 'label' => 'Semua'],
                        ['id' => 'Pending Review', 'label' => 'Pending Review'],
                        ['id' => 'In Progress', 'label' => 'In Progress'],
                        ['id' => 'Completed', 'label' => 'Completed'],
                        ['id' => 'Archived', 'label' => 'Archived'],
                    ];

                    foreach ($tabsInfo as $tab):

                        $isActive = strtolower($current_filter) === strtolower($tab['id']);
                        $baseClass =
                            'snap-start whitespace-nowrap px-4 py-2 rounded-full font-label-md transition-all active:scale-95 border ';
                        $activeClass = 'bg-primary text-white border-primary font-bold';
                        $inactiveClass =
                            'bg-surface text-on-surface-variant hover:bg-surface-variant border-outline-variant hover:border-outline';
                        ?>
                        <a href="?route=/admin/course&status=<?= e(urlencode($tab['id'])) ?>" 
                           class="<?= $baseClass ?> <?= $isActive ? $activeClass : $inactiveClass ?>">
                            <?= e($tab['label']) ?>
                        </a>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- Course Grid -->
            <?php if (empty($filteredCourses)): ?>
                <div class="w-full bg-white py-12 px-6 rounded-xl border border-outline-variant/40 flex flex-col items-center justify-center text-center shadow-sm">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-on-surface-variant mb-4">
                        <span class="material-symbols-outlined text-[32px]">menu_book</span>
                    </div>
                    <h3 class="font-headline-sm font-bold text-on-surface mb-2">Tidak Ada Course</h3>
                    <p class="font-body-sm text-on-surface-variant max-w-sm mb-6">Course dengan status filter tersebut tidak ditemukan.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4">
                    <?php foreach ($filteredCourses as $course): ?>
                        <?php
                        $displayStat = $course['status'];
                        $progress = (int) ($course['progress'] ?? 0);

                        $badgeCls = match ($displayStat) {
                            'Pending Review' => 'text-amber-700 bg-amber-50 border-amber-200 uppercase',
                            'In Progress' => 'text-title bg-emerald-50 border-emerald-200 uppercase text-emerald-700',
                            'Completed' => 'text-blue-700 bg-blue-50 border-blue-200 uppercase',
                            'Archived' => 'text-gray-500 bg-gray-100 border-gray-200 uppercase',
                            default => 'text-gray-700 bg-gray-50 border-gray-300 shadow-sm uppercase',
                        };

                        $progressBarHex = match ($displayStat) {
                            'In Progress' => '#10B981',
                            'Completed' => '#3B82F6',
                            'Pending Review' => '#F59E0B',
                            'Archived' => '#6B7280',
                            default => '#9CA3AF',
                        };

                        $progressTextColor = match ($displayStat) {
                            'In Progress' => 'text-emerald-600',
                            'Completed' => 'text-blue-600',
                            'Pending Review' => 'text-amber-600',
                            'Archived' => 'text-gray-500',
                            default => 'text-on-surface-variant',
                        };

                        $contextLine = match ($displayStat) {
                            'Pending Review' => 'Butuh tindak lanjut Anda',
                            'Completed' => 'Course telah selesai',
                            'Archived' => 'Course telah diarsipkan',
                            'In Progress' => 'Course sedang berjalan',
                            default => 'Belum diajukan oleh pengajar',
                        };

                        $ctaText = match ($displayStat) {
                            'Pending Review' => 'Review & Publish',
                            'In Progress' => 'Manajemen Sesi',
                            'Completed' => 'Lihat Laporan',
                            'Archived' => 'Lihat Arsip',
                            default => 'Lihat Detail',
                        };

                        $ctaHref = match ($displayStat) {
                            default => '?route=/admin/course/detail&id=' . e((string) $course['id']),
                        };

                        $ctaCls = match ($displayStat) {
                            'Pending Review'
                                => 'text-amber-700 border-amber-300 hover:bg-amber-50 hover:border-amber-400',
                            'In Progress'
                                => 'text-emerald-700 border-emerald-300 hover:bg-emerald-50 hover:border-emerald-400',
                            'Completed' => 'text-blue-700 border-blue-300 hover:bg-blue-50 hover:border-blue-400',
                            'Archived' => 'text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300',
                            default => 'text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400',
                        };
                        ?>
                        <div class="course-card flex flex-col h-full bg-white rounded-xl border border-outline-variant/60 overflow-hidden hover:border-outline-variant hover:shadow-[0_4px_24px_rgba(0,0,0,0.06)] transition-all duration-300">
                            
                            <!-- Card Body -->
                            <div class="p-5 flex flex-col flex-1 gap-3">
                                
                                <!-- 1. Status Badge -->
                                <div class="flex items-start">
                                    <span class="inline-flex items-center text-[10px] font-bold tracking-widest px-2.5 py-[3px] rounded-full border <?= $badgeCls ?>">
                                        <?= e($displayStat) ?>
                                    </span>
                                </div>

                                <!-- 2. Course Title -->
                                <h3 class="font-headline-sm font-bold text-[16px] text-on-surface leading-snug line-clamp-2 min-h-[2.75rem] mt-1" title="<?= e(
                                    $course['title'],
                                ) ?>">
                                    <?= e($course['title']) ?>
                                </h3>

                                <!-- 3. Category (No Code) matching requested screenshot exactly -->
                                <p class="text-[12px] text-on-surface-variant font-medium">
                                    <?= e($course['category'] ?? 'Umum') ?>
                                </p>

                                <!-- 4. Operational Metrics (3 columns precisely matching teacher screenshot) -->
                                <div class="grid grid-cols-3 border-t border-b border-outline-variant/30 py-3 mt-1.5">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[16px] font-black text-on-surface leading-none"><?= e(
                                            (string) ($course['participants'] ?? 0),
                                        ) ?></span>
                                        <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Peserta</span>
                                    </div>
                                    <div class="flex flex-col items-center border-x border-outline-variant/30">
                                        <span class="text-[16px] font-black text-on-surface leading-none"><?= e(
                                            (string) ($course['sessions'] ?? 0),
                                        ) ?></span>
                                        <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Sesi</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[16px] font-black text-on-surface leading-none"><?= e(
                                            (string) ($course['assessments'] ?? 0),
                                        ) ?></span>
                                        <span class="text-[9px] text-on-surface-variant font-bold mt-1 uppercase tracking-widest">Assesment</span>
                                    </div>
                                </div>

                                <!-- 5. Progress Bar just like Teacher Dashboard -->
                                <div class="space-y-2 mt-1.5">
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Progress Kelas</span>
                                        <div class="flex items-center gap-1.5">
                                            <?php
                                            $totalPeserta = (int) ($course['participants'] ?? 0);
                                            $selesai = floor(($progress / 100) * $totalPeserta);
                                            ?>
                                            <span class="text-[9px] text-on-surface-variant font-medium">(<?= $selesai ?> dari <?= $totalPeserta ?> Selesai)</span>
                                            <span class="text-[12px] font-bold <?= $progressTextColor ?>"><?= $progress ?>%</span>
                                        </div>
                                    </div>
                                    <div class="w-full rounded-full h-1.5 overflow-hidden bg-surface-variant/40">
                                        <div class="h-full rounded-full transition-all duration-500" style="width: <?= $progress ?>%; background-color: <?= $progressBarHex ?>;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer CTA - matching Teacher screenshot -->
                            <div class="px-5 pb-5 mt-auto">
                                <div class="border-t border-outline-variant/30 pt-4 flex items-center justify-between">
                                    <span class="font-body-sm text-[11px] text-on-surface-variant italic">
                                        <?= e($contextLine) ?>
                                    </span>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="<?= $ctaHref ?>" class="inline-flex items-center justify-center font-label-md font-bold text-on-surface border border-outline-variant hover:bg-surface-variant px-3 py-1.5 rounded-lg transition-colors">
                                            <?= e($ctaText) ?>
                                        </a>
                                        <button type="button" onclick="confirmDeleteCourseAdminCard('<?= e((string)$course['id']) ?>', '<?= e(addslashes($course['title'])) ?>')" class="w-8 h-8 rounded-lg border border-outline-variant/60 text-outline hover:text-error hover:border-error/40 hover:bg-error/5 flex items-center justify-center transition-colors" title="Hapus Course Permanen">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Bottom Nav untuk Handphone -->
    <?php include 'includes/components/DX_LMS_M_Admin_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js"></script>
    <script src="assets/js/DX_LMS_M_Helpers.js"></script>
    <script>
        function confirmDeleteCourseAdminCard(courseId, title) {
            const doDelete = () => {
                fetch('?route=/admin/course/delete&id=' + encodeURIComponent(courseId))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Berhasil Dihapus!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#E20613'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert(data.message);
                                window.location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Gagal Menghapus', data.error || data.message, 'error');
                            } else {
                                alert(data.error || data.message);
                            }
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan saat menghapus course.');
                    });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Course Permanen?',
                    text: 'PERINGATAN: Course "' + title + '" dan seluruh sesi, kuis, materi, serta relasinya akan DIHAPUS TOTAL dari database SSMS. Tindakan ini tidak dapat dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            } else if (confirm('PERINGATAN: Course "' + title + '" akan dihapus total secara permanen dari database. Lanjutkan?')) {
                doDelete();
            }
        }
    </script>
</body>
</html>
