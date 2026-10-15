<?php
/**
 * Tampilan Forum Pengajar (Teacher Forum).
 * Desain modern, clean, dan sejalan dengan pendekatan tampilan forum Student.
 */
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <?php
    $page_title = 'Forum Diskusi - LMS Teacher';
    include 'includes/components/DX_LMS_M_Tailwind_Head.php';
    ?>
    <style>
        body { min-height: max(600px, 100dvh); }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="dls-app-shell bg-[#FAFAFA] text-on-surface antialiased min-h-screen flex flex-col font-sans">
    
    <?php $active_tab = 'forum'; ?>
    <?php include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>

    <!-- Universal Enterprise Teacher Top Bar -->
    <?php
    $header_title = 'Forum Diskusi';
    $show_back_button = true;
    include 'includes/components/DX_LMS_M_Teacher_Header.php';
    ?>

    <!-- Main Content -->
    <main class="dls-main flex-1 w-full lg:ml-[240px] px-4 sm:px-6 lg:px-8 pb-[88px] pt-[88px]">
        <div class="max-w-5xl mx-auto space-y-6">
            
            <!-- Course Selector & Welcome Banner -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-outline-variant/50 shadow-[0_4px_20px_rgba(0,0,0,0.03)] flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-md bg-primary/10 text-primary font-mono text-[11px] font-bold">
                            <?= htmlspecialchars($activeCourse['code'] ?? 'K3-101') ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-md bg-surface-container text-on-surface-variant font-label-sm text-[11px] font-semibold">
                            <?= htmlspecialchars($activeCourse['category'] ?? 'HSE') ?>
                        </span>
                    </div>
                    <h1 class="font-headline-md font-bold text-on-surface tracking-tight">
                        Forum Diskusi: <?= htmlspecialchars($activeCourse['title'] ?? 'Fundamental 5S & K3') ?>
                    </h1>
                    <p class="font-body-sm text-on-surface-variant">
                        Pantau pertanyaan peserta, berikan bimbingan teknis, dan verifikasi solusi resmi pengajar.
                    </p>
                </div>

                <!-- Course Switcher & Action -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                    <!-- Dropdown Pilih Course -->
                    <div class="relative">
                        <select onchange="window.location.href='?route=/teacher/forum&course_id=' + this.value" class="appearance-none bg-surface border border-outline-variant/60 rounded-xl px-4 py-2.5 pr-10 font-label-md text-on-surface font-semibold text-xs focus:outline-none focus:border-primary transition-colors cursor-pointer w-full">
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>" <?= (string)$c['id'] === (string)$selectedCourseId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-on-surface-variant text-[18px]">expand_more</span>
                    </div>

                    <button onclick="openNewTopicModal()" class="bg-primary hover:bg-primary-600 text-on-primary font-label-md font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-xs shrink-0 active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">add_comment</span> Buat Pengumuman / Topik
                    </button>
                </div>
            </div>

            <!-- Bento Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">forum</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Total Diskusi</span>
                        <span class="text-2xl font-bold text-on-surface"><?= $totalTopics ?></span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">pending</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Menunggu Solusi</span>
                        <span class="text-2xl font-bold text-amber-600"><?= $unresolvedCount ?></span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-outline-variant/40 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[26px]">task_alt</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block">Solusi Terverifikasi</span>
                        <span class="text-2xl font-bold text-emerald-600"><?= $resolvedCount ?></span>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-outline-variant/40 shadow-sm flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
                <!-- Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 hide-scrollbar">
                    <a href="?route=/teacher/forum&course_id=<?= urlencode($selectedCourseId) ?>&category=all" class="px-3.5 py-1.5 rounded-xl font-label-sm font-bold text-xs transition-colors <?= $filterCategory === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' ?>">
                        Semua
                    </a>
                    <a href="?route=/teacher/forum&course_id=<?= urlencode($selectedCourseId) ?>&category=materi" class="px-3.5 py-1.5 rounded-xl font-label-sm font-bold text-xs transition-colors <?= $filterCategory === 'materi' ? 'bg-primary text-white shadow-sm' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' ?>">
                        Materi Sesi
                    </a>
                    <a href="?route=/teacher/forum&course_id=<?= urlencode($selectedCourseId) ?>&category=quiz" class="px-3.5 py-1.5 rounded-xl font-label-sm font-bold text-xs transition-colors <?= $filterCategory === 'quiz' ? 'bg-primary text-white shadow-sm' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' ?>">
                        Kuis & Ujian
                    </a>
                    <a href="?route=/teacher/forum&course_id=<?= urlencode($selectedCourseId) ?>&status=resolved" class="px-3.5 py-1.5 rounded-xl font-label-sm font-bold text-xs transition-colors <?= $filterStatus === 'resolved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant' ?>">
                        Tuntas Diselesaikan
                    </a>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-64">
                    <input type="text" id="teacher-forum-search" placeholder="Cari pertanyaan peserta..." value="<?= htmlspecialchars($searchKeyword) ?>" class="w-full bg-surface border border-outline-variant/60 rounded-xl pl-9 pr-4 py-2 font-body-sm text-xs text-on-surface focus:outline-none focus:border-primary transition-colors">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-on-surface-variant text-[16px]">search</span>
                </div>
            </div>

            <!-- Thread List -->
            <div id="thread-cards-container" class="space-y-4">
                <?php if (empty($topics)): ?>
                    <div class="bg-white rounded-3xl border border-dashed border-outline-variant p-12 text-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[48px] mb-3 opacity-40">forum</span>
                        <h3 class="font-headline-sm font-bold text-on-surface mb-1">Belum Ada Topik Diskusi</h3>
                        <p class="font-body-sm text-on-surface-variant max-w-md mx-auto">
                            Belum ada peserta yang mengajukan pertanyaan pada kategori ini. Anda juga bisa mengawali diskusi dengan membuat pengumuman pertama.
                        </p>
                        <button onclick="openNewTopicModal()" class="mt-4 px-4 py-2 bg-primary text-white rounded-xl font-label-sm font-bold inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">add</span> Mulai Topik Pertama
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($topics as $t): ?>
                        <?php
                        $authorInitial = strtoupper(substr($t['author_name'] ?? 'U', 0, 1));
                        $isResolved = ($t['status'] ?? 'open') === 'resolved';
                        $authorRole = $t['author_role'] ?? 'student';
                        $avatarClass = match ($authorRole) {
                            'teacher' => 'bg-primary text-white shadow-sm',
                            'admin' => 'bg-indigo-600 text-white shadow-sm',
                            default => 'bg-surface-container-high text-on-surface border border-outline-variant/40',
                        };
                        ?>
                        <div class="forum-thread-card bg-white rounded-2xl border border-outline-variant/50 p-5 md:p-6 shadow-sm hover:border-primary/40 hover:shadow-md transition-all group" data-title="<?= htmlspecialchars(strtolower($t['title'])) ?>">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="w-12 h-12 rounded-full <?= $avatarClass ?> font-bold text-[16px] flex items-center justify-center shrink-0">
                                        <?= $authorInitial ?>
                                    </div>
                                    <div class="space-y-2 flex-1 min-w-0">
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="font-bold text-on-surface text-[15px]"><?= htmlspecialchars($t['author_name']) ?></span>
                                            
                                            <?php if ($authorRole === 'teacher'): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-primary/10 text-primary font-bold border border-primary/20">
                                                    PENGAJAR
                                                </span>
                                            <?php elseif ($authorRole === 'admin'): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                                                    ADMIN
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-secondary/10 text-secondary font-bold border border-secondary/20">
                                                    PESERTA
                                                </span>
                                            <?php endif; ?>

                                            <span class="text-xs text-on-surface-variant">· <?= htmlspecialchars($t['time_formatted'] ?? 'Baru saja') ?></span>
                                        </div>

                                        <a href="?route=/teacher/forum/thread&id=<?= (int)$t['topic_id'] ?>" class="block group-hover:text-primary transition-colors">
                                            <h3 class="font-title-md font-bold text-on-surface group-hover:text-primary transition-colors leading-snug">
                                                <?= htmlspecialchars($t['title']) ?>
                                            </h3>
                                        </a>

                                        <p class="font-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
                                            <?= htmlspecialchars(mb_substr($t['content'], 0, 160)) ?>...
                                        </p>

                                        <!-- Tags & Indicators -->
                                        <div class="flex items-center flex-wrap gap-2 pt-1">
                                            <span class="px-2 py-0.5 rounded-md bg-surface-container text-on-surface-variant text-[11px] font-semibold uppercase">
                                                <?= htmlspecialchars($t['category_type']) ?>
                                            </span>
                                            <?php if (!empty($t['session_id'])): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-semibold">
                                                    Sesi <?= htmlspecialchars($t['session_id']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($isResolved): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200 flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[13px]">check_circle</span> Solusi Disetujui
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action & Replies Metric -->
                                <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-outline-variant/30 shrink-0">
                                    <div class="flex items-center gap-1.5 text-secondary font-semibold text-xs">
                                        <span class="material-symbols-outlined text-[18px]">question_answer</span>
                                        <span><?= (int)$t['total_replies'] ?> Balasan</span>
                                    </div>

                                    <a href="?route=/teacher/forum/thread&id=<?= (int)$t['topic_id'] ?>" class="bg-primary/10 hover:bg-primary hover:text-white text-primary font-label-sm font-bold py-2 px-4 rounded-xl transition-all text-xs flex items-center gap-1">
                                        <span>Buka Thread</span>
                                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- Modal Buat Topik / Pengumuman Baru -->
    <div id="newTopicModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeNewTopicModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-outline-variant/40 relative z-10 p-6 sm:p-8">
                
                <div class="flex items-center justify-between pb-4 border-b border-outline-variant/30 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">add_comment</span>
                        </div>
                        <div>
                            <h3 class="font-headline-sm font-bold text-on-surface">Buat Topik / Pengumuman</h3>
                            <p class="font-body-sm text-on-surface-variant text-xs">Diskusi ini akan langsung tampil di forum peserta.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeNewTopicModal()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-surface-container text-on-surface-variant">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="?route=/teacher/forum/new" method="POST" class="space-y-4">
                    <input type="hidden" name="course_id" value="<?= htmlspecialchars($selectedCourseId) ?>">

                    <div>
                        <label class="block font-label-sm font-bold text-on-surface mb-1 text-xs">Kategori & Sesi</label>
                        <select name="category_type" class="w-full bg-surface border border-outline-variant/60 rounded-xl px-4 py-2.5 font-body-sm text-xs text-on-surface focus:outline-none focus:border-primary">
                            <option value="materi">Materi Pelatihan Sesi</option>
                            <option value="quiz">Instruksi Kuis & Penilaian</option>
                            <option value="umum">Pengumuman Umum Kursus</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-label-sm font-bold text-on-surface mb-1 text-xs">Judul Topik / Pengumuman</label>
                        <input type="text" name="title" required placeholder="Contoh: Pengumuman Jadwal Praktek 5S di Line Produksi" class="w-full bg-surface border border-outline-variant/60 rounded-xl px-4 py-2.5 font-body-sm text-xs text-on-surface focus:outline-none focus:border-primary">
                    </div>

                    <div>
                        <label class="block font-label-sm font-bold text-on-surface mb-1 text-xs">Isi Penjelasan Lengkap</label>
                        <textarea name="content" required rows="5" placeholder="Tuliskan petunjuk, penjelasan, atau poin diskusi di sini..." class="w-full bg-surface border border-outline-variant/60 rounded-xl p-3 font-body-sm text-xs text-on-surface focus:outline-none focus:border-primary resize-y min-h-[100px]"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/30">
                        <button type="button" onclick="closeNewTopicModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant text-on-surface-variant font-label-md font-semibold text-xs hover:bg-surface-variant transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-600 text-white font-label-md font-bold text-xs shadow-sm flex items-center gap-1.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">send</span> Publikasikan Topik
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function openNewTopicModal() {
            document.getElementById('newTopicModal').classList.remove('hidden');
        }
        function closeNewTopicModal() {
            document.getElementById('newTopicModal').classList.add('hidden');
        }

        // Live text filter
        document.getElementById('teacher-forum-search')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.forum-thread-card').forEach(card => {
                const title = card.getAttribute('data-title') || '';
                card.style.display = title.includes(query) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
