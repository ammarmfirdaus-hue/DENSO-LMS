<?php
$page_title = 'Nilai Saya - DENSO Learning';
$header_title = 'Nilai Saya';
$active_tab = 'nilai';
$notif_count = (int) ($stats['unread_notifs'] ?? 0);
$grades = is_array($grades ?? null) ? $grades : [];
?>
<!doctype html>
<html lang="id">
<head><?php include APP_ROOT . '/includes/components/DX_LMS_M_Tailwind_Head.php'; ?></head>
<body class="dls-app-shell min-h-screen bg-surface text-on-surface antialiased">
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Header.php'; ?>
    <main class="dls-main min-w-0 px-4 pb-24 pt-20 lg:ml-[240px] lg:px-8 lg:pb-10">
        <div class="mx-auto min-w-0 max-w-5xl">
            <header class="mb-6 border-b border-outline-variant/40 pb-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">PEMBELAJARAN</p>
                <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Nilai Saya</h1>
                <p class="mt-2 text-sm text-on-surface-variant">Lihat hasil Quiz Course dan progres belajar Anda.</p>
            </header>
            <?php if (!empty($grade_error)): ?><p role="alert" class="mb-4 rounded-xl border border-error/30 bg-error-container/50 p-4 text-sm text-on-error-container"><?= e($grade_error) ?></p><?php endif; ?>
            <div class="dls-grade-table-scroll max-w-full overflow-x-auto rounded-xl border border-outline-variant/50 bg-surface-container-lowest" role="region" aria-label="Tabel nilai saya" tabindex="0"<?= $grades ? ' aria-describedby="grade-table-scroll-hint"' : '' ?>>
                <?php if ($grades): ?><p id="grade-table-scroll-hint" class="dls-grade-table-hint"><span class="material-symbols-outlined" aria-hidden="true">swipe</span><span>Geser tabel ke samping untuk melihat semua kolom.</span></p><?php endif; ?>
                <table class="dls-grade-table w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant"><tr>
                        <th scope="col" class="px-4 py-3">Course</th><th scope="col" class="px-4 py-3">Kategori</th><th scope="col" class="px-4 py-3">Nilai Quiz</th><th scope="col" class="px-4 py-3">Progres</th>
                    </tr></thead>
                    <tbody class="divide-y divide-outline-variant/30">
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td class="px-4 py-4"><p class="font-semibold text-on-surface"><?= e($grade['course_title']) ?></p><p class="mt-0.5 text-xs text-on-surface-variant"><?= e($grade['course_code']) ?></p></td>
                            <td class="px-4 py-4"><?= e($grade['category']) ?></td>
                            <td class="px-4 py-4"><?php if (($grade['quiz_score'] ?? null) === null): ?><span class="text-xs text-on-surface-variant">Belum mengerjakan</span><?php else: ?><span class="text-lg font-bold text-primary"><?= e(number_format((float) $grade['quiz_score'], 2, ',', '.')) ?></span><?php endif; ?></td>
                            <td class="px-4 py-4"><?= (int) $grade['progress'] ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!$grades): ?><p class="p-10 text-center text-sm text-on-surface-variant">Belum ada course atau nilai untuk ditampilkan.</p><?php endif; ?>
            </div>
        </div>
    </main>
    <?php include APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php'; ?>
</body>
</html>
