<?php
/**
 * Tampilan Daftar Course Pengajar (Teacher Course List).
 *
 * Menampilkan rekomendasi dan memonitor lifecycle course.
 */

// Handle PHP Filter Processing Before Setting HTML variables
$courses = $courses ?? [];
$current_filter_raw = $_GET['status'] ?? 'all';
$current_filter = strtolower($current_filter_raw) === 'all' ? 'all' : urldecode($current_filter_raw);

$filteredCourses = [];
foreach ($courses as $course) {
    // Map raw status safely to UI Status using unified helper
    $rawStat = $course['status'] ?? 'Private';
    $displayStat = getCourseDisplayStatus($rawStat, $course['raw_updated_at'] ?? null);
    $course['_mapped_status'] = $displayStat;

    if (
        $current_filter === 'all' || 
        strtolower($current_filter) === strtolower($displayStat) ||
        (strtolower($current_filter) === 'in progress' && $displayStat === 'Approved')
    ) {
        $filteredCourses[] = $course;
    }
}

// Custom sort when "all" is active
if ($current_filter === 'all') {
    $priority = [
        'Rejected' => 1,
        'Pending Review' => 2,
        'Private' => 3,
        'Approved' => 4,
        'In Progress' => 5,
        'Completed' => 6,
        'Archived' => 7,
    ];
    usort($filteredCourses, function ($a, $b) use ($priority) {
        $pA = $priority[$a['_mapped_status']] ?? 99;
        $pB = $priority[$b['_mapped_status']] ?? 99;
        return $pA <=> $pB;
    });
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Kelola Course - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        /* Hide scrollbar for category tabs */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="dls-app-shell bg-surface text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'course'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Kelola Course';
    $show_create_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content Canvas -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-container-margin pb-[88px] pt-[88px]">
        
        <section class="w-full space-y-stack-md">
            
            <!-- Page Header with Create Course Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <div>
                    <h1 class="font-headline-md font-bold text-on-surface tracking-tight">Daftar Course Saya</h1>
                    <p class="font-body-md text-on-surface-variant mt-0.5">Kelola kurikulum, pengajuan materi, dan pantau status publikasi course Anda.</p>
                </div>
                <a href="?route=/teacher/course/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-label-md font-bold hover:bg-primary-600 shadow-sm hover:shadow transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    <span>Buat Course</span>
                </a>
            </div>

            <!-- Filter Tabs -->
            <div class="relative w-full -mx-container-margin px-container-margin md:mx-0 md:px-0 mb-4 sticky lg:relative top-14 lg:top-0 bg-surface z-20 py-2">
                <div class="flex gap-2 overflow-x-auto no-scrollbar snap-x py-1">
                    <?php
                    $tabsInfo = [
                        ['id' => 'all', 'label' => 'Semua'],
                        ['id' => 'Private', 'label' => 'Private'],
                        ['id' => 'Pending Review', 'label' => 'Pending Review'],
                        ['id' => 'Rejected', 'label' => 'Rejected'],
                        ['id' => 'In Progress', 'label' => 'In Progress'],
                        ['id' => 'Completed', 'label' => 'Completed'],
                        ['id' => 'Archived', 'label' => 'Archived'],
                    ];

                    foreach ($tabsInfo as $tab):

                        $isActive = strtolower($current_filter) === strtolower($tab['id']);
                        $baseClass =
                            'snap-start whitespace-nowrap px-4 py-2 rounded-full font-label-md transition-all active:scale-95 border ';
                        // Active = Emerald theme matching the primary system UI or a clean dark shade. Let's use a standard primary shade
                        $activeClass = 'bg-primary text-white border-primary font-bold';
                        $inactiveClass =
                            'bg-surface text-on-surface-variant hover:bg-surface-variant border-outline-variant hover:border-outline';
                        ?>
                        <a href="?route=/teacher/courses&status=<?= e(urlencode($tab['id'])) ?>" 
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
                    <a href="?route=/teacher/course/create" class="inline-flex items-center gap-1.5 bg-white text-error border border-error font-label-md font-bold px-5 py-2.5 rounded-lg hover:bg-error/5 transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Buat Course Baru
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4">
                    <?php foreach ($filteredCourses as $course): ?>
                        <?php
                        $displayStat = $course['_mapped_status'];
                        $badgeInfo = getCourseStatusBadgeInfo($displayStat);
                        $actionInfo = getCourseActionInfo($displayStat, $course['id']);

                        $badgeCls = $badgeInfo['badgeCls'];
                        $progressBarHex = $badgeInfo['progressBarHex'];
                        $progressTextColor = $badgeInfo['progressTextColor'];

                        $progress = (int) ($course['progress'] ?? 0);

                        $contextLine = match ($displayStat) {
                            'Private' => 'Belum diajukan ke admin',
                            'Pending Review' => 'Menunggu review admin',
                            'Rejected' => !empty($course['rejection_note'])
                                ? 'Catatan: "' . e(mb_strimwidth($course['rejection_note'], 0, 36, '...')) . '"'
                                : 'Perlu revisi segera',
                            'Completed' => 'Course telah selesai',
                            'Archived' => 'Course telah diarsipkan',
                            default => 'Terakhir diperbarui: ' . e($course['updated_at'] ?? 'Baru saja'),
                        };

                        $ctaText = $actionInfo['text'];
                        $ctaHref = $actionInfo['href'];
                        $ctaCls = $actionInfo['cls'];
                        ?>
                        <div class="course-card flex flex-col h-full bg-white rounded-xl border border-outline-variant/60 overflow-hidden hover:border-outline-variant hover:shadow-[0_4px_24px_rgba(0,0,0,0.06)] transition-all duration-300">
                            
                            <!-- Card Body -->
                            <div class="p-5 flex flex-col flex-1 gap-3">
                                
                                <!-- 1. Status Badge -->
                                <div class="flex items-start">
                                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-widest px-2.5 py-[3px] rounded-full border <?= $badgeCls ?>">
                                        <?= e($displayStat) ?>
                                    </span>
                                </div>

                                <!-- 2. Course Title -->
                                <h3 class="font-headline-sm font-bold text-[16px] text-on-surface leading-snug line-clamp-2 min-h-[2.75rem] mt-1" title="<?= e(
                                    $course['title'],
                                ) ?>">
                                    <a href="<?= $ctaHref ?>" class="hover:text-primary transition-colors focus:outline-none focus:underline"><?= e(
    $course['title'],
) ?></a>
                                </h3>

                                <!-- 3. Category (No Code) -->
                                <p class="text-[12px] text-on-surface-variant font-medium">
                                    <?= e($course['category'] ?? 'Umum') ?>
                                </p>

                                <!-- 4. Operational Metrics -->
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

                                <!-- 5. Progress Bar -->
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

                            <!-- 6 & 7. Footer: Context (left) + Action (right) -->
                            <div class="px-5 py-4 flex items-center justify-between mt-auto border-t border-outline-variant/30">
                                <!-- Left: Context Info -->
                                <span class="font-body-sm text-[11px] text-on-surface-variant italic leading-snug flex-1">
                                    <?= e($contextLine) ?>
                                </span>
                                <!-- Right: Action Buttons -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="<?= $ctaHref ?>" class="inline-flex items-center justify-center font-label-md font-bold text-on-surface border border-outline-variant hover:bg-surface-variant px-3 py-1.5 rounded-lg transition-colors cursor-pointer <?= $ctaCls ?>">
                                        <?= e($ctaText) ?>
                                    </a>
                                    <button type="button" data-id="<?= e((string) $course['id']) ?>" data-title="<?= e(
    $course['title'],
) ?>" onclick="confirmDeleteCourseCard(this)" class="w-8 h-8 rounded-lg border border-outline-variant/60 text-outline hover:text-error hover:border-error/40 hover:bg-error/5 flex items-center justify-center transition-colors cursor-pointer" title="Hapus Course Permanen">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Floating Action Button (Mobile Only) -->
    <a href="?route=/teacher/course/create" class="fixed bottom-[88px] right-4 z-40 bg-white text-error h-14 px-5 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.12)] border border-error/50 flex items-center gap-2 hover:bg-error/5 hover:border-error transition-all duration-200 active:scale-95 lg:hidden box-border">
        <span class="material-symbols-outlined text-[24px]">add</span>
        <span class="font-label-md font-bold">Buat</span>
    </a>

    <?php include 'includes/components/DX_LMS_M_Teacher_Bottom_Nav.php'; ?>

    <script src="assets/js/DX_LMS_M_SweetAlert2.min.js"></script>
    <script src="assets/js/DX_LMS_M_Helpers.js"></script>
    <script>
        function confirmDeleteCourseCard(elOrId, rawTitle) {
            let courseId = elOrId;
            let title = rawTitle;
            if (typeof elOrId === 'object' && elOrId !== null) {
                courseId = elOrId.getAttribute('data-id');
                title = elOrId.getAttribute('data-title');
            }
            if (!courseId) return;

            const doDelete = () => {
                fetch('?route=/teacher/course/delete&id=' + encodeURIComponent(courseId))
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
                    text: 'PERINGATAN: Course "' + title + '" dan seluruh sesi serta materinya akan DIHAPUS TOTAL dari database SSMS. Tindakan ini tidak dapat dibatalkan!',
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
            } else if (confirm('PERINGATAN: Course "' + title + '" akan dihapus total dari database. Lanjutkan?')) {
                doDelete();
            }
        }
    </script>
</body>
</html>
