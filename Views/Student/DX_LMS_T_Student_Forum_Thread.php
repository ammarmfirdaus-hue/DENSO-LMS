<?php
/**
 * DLS Forum Thread Detail View — Social Media Style (X / Threads inspired)
 * Terintegrasi penuh dengan basis data SQL Server dan mendukung role Student, Teacher, Admin.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback jika controller belum menyertakan data
if (!isset($topic) || empty($topic)) {
    require_once 'Models/DX_LMS_M_Forum.php';
    $fm = new DX_LMS_M_Forum();
    $tid = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    $topic = $fm->getTopic($tid);
    $replies = $topic ? $fm->getReplies($tid) : [];
}

$userSession = $_SESSION['user'] ?? [];
$activeRole = $_SESSION['active_role'] ?? ($userSession['role'] ?? 'student');
$userName = $userSession['fullname'] ?? ($userSession['name'] ?? 'Pengguna');
$userNpk = $userSession['npk'] ?? '00000';
$userJabatan = $userSession['jabatan'] ?? 'Peserta';

$page_title = 'Detail Diskusi - LMS';
include 'includes/components/DX_LMS_M_Tailwind_Head.php';
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 44px); }
        body { min-height: max(884px, 100dvh); }
        .fade-in { animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(8px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .interact-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 500;
            color: #5e3f3b;
            transition: all 0.15s ease;
            cursor: pointer;
            border: none;
            background: none;
        }
        .interact-btn:hover { background: rgba(0, 0, 0, 0.04); }
        .interact-btn.like:hover { color: #e20613; background: rgba(226, 6, 19, 0.06); }
        .interact-btn.like:hover .material-symbols-outlined { color: #e20613; }
        .interact-btn.like.active { color: #e20613; }
        .interact-btn.like.active .material-symbols-outlined { font-variation-settings: 'FILL' 1; color: #e20613; }
        .interact-btn .material-symbols-outlined { font-size: 18px; transition: color 0.15s ease; }
        .composer-area::-webkit-scrollbar { width: 4px; }
        .composer-area::-webkit-scrollbar-track { background: transparent; }
        .composer-area::-webkit-scrollbar-thumb { background-color: rgba(0, 0, 0, 0.08); border-radius: 10px; }
    </style>
</head>

<body class="dls-app-shell bg-surface-container-lowest text-on-surface antialiased min-h-screen flex flex-col">

    <!-- Navigasi & Sidebar Berdasarkan Role -->
    <?php if ($activeRole === 'teacher'): ?>
        <?php $active_tab = 'course'; include 'includes/components/DX_LMS_M_Teacher_Sidebar.php'; ?>
        <?php
        $header_title = 'Detail Forum Diskusi';
        $show_back_button = true;
        include 'includes/components/DX_LMS_M_Teacher_Header.php';
        ?>
    <?php elseif ($activeRole === 'admin'): ?>
        <?php $active_tab = 'course'; include 'includes/components/DX_LMS_M_Admin_Sidebar.php'; ?>
        <?php
        $header_title = 'Detail Forum (Moderasi)';
        $show_back = true;
        include 'includes/components/DX_LMS_M_Header.php';
        ?>
    <?php else: ?>
        <?php
        $header_title = 'Detail Diskusi';
        $show_back = true;
        $back_url = $backUrl ?? ('?route=/student/course/detail&id=' . urlencode($topic['course_id'] ?? '1') . '&tab=forum');
        include 'includes/components/DX_LMS_M_Header.php';
        ?>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <div class="flex flex-col lg:flex-row items-start <?= in_array($activeRole, ['teacher', 'admin']) ? 'lg:ml-[240px]' : 'lg:ml-[240px]' ?> pt-2 md:pt-4 lg:pl-8 lg:pr-6 xl:pl-12 gap-8 w-full <?= in_array($activeRole, ['teacher', 'admin']) ? 'lg:w-[calc(100%-240px)]' : 'lg:w-[calc(100%-240px)]' ?>" style="margin-top: 56px;">

        <!-- Feed Container (Left) -->
        <main class="flex-1 w-full max-w-[760px] mx-auto lg:mx-0 px-4 md:px-0 flex flex-col fade-in pb-[160px] lg:pb-[140px]">

            <!-- Breadcrumb Navigation -->
            <div class="flex items-center gap-2 mb-4 text-xs font-semibold text-on-surface-variant/80">
                <?php if ($activeRole === 'teacher'): ?>
                    <a href="?route=/teacher/courses" class="hover:text-primary">Course</a>
                    <span>/</span>
                    <a href="?route=/teacher/course/detail&course_id=<?= urlencode($topic['course_id'] ?? '1') ?>&tab=forum" class="hover:text-primary">Forum Course</a>
                <?php elseif ($activeRole === 'admin'): ?>
                    <a href="?route=/admin/course" class="hover:text-primary">Manajemen Course</a>
                    <span>/</span>
                    <a href="?route=/admin/forum&course_id=<?= urlencode($topic['course_id'] ?? '1') ?>" class="hover:text-primary">Forum Diskusi</a>
                <?php else: ?>
                    <a href="?route=/student/courses" class="hover:text-secondary">Course</a>
                    <span>/</span>
                    <a href="?route=/student/course/detail&id=<?= urlencode($topic['course_id'] ?? '1') ?>&tab=forum" class="hover:text-secondary">Forum</a>
                <?php endif; ?>
                <span>/</span>
                <span class="text-on-surface truncate max-w-[200px]"><?= htmlspecialchars($topic['title'] ?? 'Topik') ?></span>
            </div>

            <!-- ============================================ -->
            <!-- ORIGINAL POST (OP) -->
            <!-- ============================================ -->
            <article class="relative bg-surface rounded-2xl border border-outline-variant/40 p-5 md:p-6 shadow-sm mb-6">
                <!-- Header: Avatar + Info -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <?php
                        $authorName = $topic['author_name'] ?? 'User';
                        $authorInitial = strtoupper(substr($authorName, 0, 1));
                        $authorRole = $topic['author_role'] ?? 'student';
                        $avatarClass = match ($authorRole) {
                            'teacher' => 'bg-primary text-white shadow-sm',
                            'admin' => 'bg-indigo-600 text-white shadow-sm',
                            default => 'bg-surface-container-high text-on-surface border border-outline-variant/40',
                        };
                        ?>
                        <div class="w-11 h-11 md:w-12 md:h-12 rounded-full <?= $avatarClass ?> font-bold text-[16px] md:text-[18px] flex items-center justify-center shrink-0">
                            <?= $authorInitial ?>
                        </div>
                        <!-- Info -->
                        <div class="flex flex-col">
                            <span class="font-bold text-on-surface text-[15px] md:text-[16px] leading-tight flex items-center gap-2">
                                <?= htmlspecialchars($authorName) ?>
                                <?php if ($authorRole === 'teacher'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-primary/10 text-primary font-bold border border-primary/20 tracking-wide inline-flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">verified</span> PENGAJAR
                                    </span>
                                <?php elseif ($authorRole === 'admin'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 tracking-wide inline-flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[12px]">admin_panel_settings</span> ADMIN
                                    </span>
                                <?php else: ?>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-secondary/10 text-secondary font-bold border border-secondary/20 tracking-wide">
                                        PESERTA
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="text-on-surface-variant text-[12px] leading-tight mt-1">
                                <?= htmlspecialchars($topic['author_dept'] ?? 'Divisi Manufaktur') ?> · NPK <?= htmlspecialchars($topic['author_npk'] ?? '-') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Topic Status Pill -->
                    <div>
                        <?php if (($topic['status'] ?? 'open') === 'resolved'): ?>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-300 text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1 shadow-sm">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Solusi Terverifikasi
                            </span>
                        <?php else: ?>
                            <span class="bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-bold px-3 py-1 rounded-full inline-flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span> Diskusi Aktif
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Content -->
                <div class="w-full">
                    <h1 class="font-bold text-on-surface text-[18px] md:text-[22px] leading-snug mb-3">
                        <?= htmlspecialchars($topic['title'] ?? 'Topik Diskusi') ?>
                    </h1>

                    <div class="text-on-surface text-[15px] leading-relaxed mb-4 whitespace-pre-wrap">
                        <?= nl2br(htmlspecialchars($topic['content'] ?? '')) ?>
                    </div>

                    <!-- Tags -->
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="px-2.5 py-1 bg-surface-container text-on-surface-variant text-[12px] font-medium rounded-lg border border-outline-variant/40 uppercase">
                            <?= htmlspecialchars($topic['category_type'] ?? 'materi') ?>
                        </span>
                        <?php if (!empty($topic['session_id'])): ?>
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-[12px] font-semibold rounded-lg border border-indigo-200">
                                Sesi <?= htmlspecialchars($topic['session_id']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($topic['is_hot'])): ?>
                            <span class="px-2.5 py-1 bg-error/10 text-error text-[12px] font-bold rounded-lg flex items-center gap-1 border border-error/20">
                                <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">local_fire_department</span> Hot
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Metadata (Date/Time & Counter) -->
                    <div class="text-on-surface-variant text-[13px] flex items-center flex-wrap gap-3 pt-3 border-t border-outline-variant/30">
                        <span><?= htmlspecialchars($topic['time_formatted'] ?? 'Baru saja') ?></span>
                        <span>·</span>
                        <span class="font-semibold text-on-surface"><span id="op-replies-count"><?= count($replies ?? []) ?></span> Balasan</span>
                        <span>·</span>
                        <button type="button" onclick="toggleTopicLike(<?= (int)$topic['topic_id'] ?>)" class="interact-btn like inline-flex items-center gap-1 hover:text-primary">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                            <span id="topic-like-count"><?= (int)($topic['likes_count'] ?? 0) ?></span> Suka
                        </button>
                    </div>
                </div>
            </article>

            <!-- ============================================ -->
            <!-- DAFTAR BALASAN (REPLIES FEED) -->
            <!-- ============================================ -->
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="font-headline-sm font-bold text-on-surface text-[17px] flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">question_answer</span>
                    Tanggapan & Diskusi (<?= count($replies ?? []) ?>)
                </h3>
                <span class="text-xs text-on-surface-variant">Terintegrasi Student · Teacher · Admin</span>
            </div>

            <div id="replies-list" class="space-y-4">
                <?php if (empty($replies)): ?>
                    <div id="empty-replies" class="bg-surface rounded-2xl border border-dashed border-outline-variant/60 p-8 text-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[36px] mb-2 opacity-50">forum</span>
                        <p class="font-body-md font-medium">Belum ada tanggapan pada diskusi ini.</p>
                        <p class="font-body-sm text-[12px] opacity-75 mt-0.5">Tuliskan tanggapan Anda melalui kotak di bawah untuk memulai interaksi.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($replies as $reply): ?>
                        <?php
                        $rRole = $reply['author_role'] ?? 'student';
                        $rInitial = strtoupper(substr($reply['author_name'] ?? 'U', 0, 1));
                        $isSolution = !empty($reply['is_solution']);
                        $cardBg = $isSolution ? 'bg-emerald-50/40 border-emerald-300' : 'bg-surface border-outline-variant/40';
                        $rAvatarClass = match ($rRole) {
                            'teacher' => 'bg-primary text-white shadow-sm',
                            'admin' => 'bg-indigo-600 text-white shadow-sm',
                            default => 'bg-surface-container-high text-on-surface border border-outline-variant/40',
                        };
                        ?>
                        <article id="reply-card-<?= (int)$reply['reply_id'] ?>" class="relative rounded-2xl border <?= $cardBg ?> p-4 md:p-5 transition-all shadow-sm">
                            <div class="flex gap-3 md:gap-4 items-start">
                                <!-- Avatar -->
                                <div class="shrink-0">
                                    <div class="w-10 h-10 rounded-full <?= $rAvatarClass ?> font-bold text-[15px] flex items-center justify-center">
                                        <?= $rInitial ?>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center flex-wrap justify-between gap-2 mb-2">
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="font-bold text-on-surface text-[14px] md:text-[15px]">
                                                <?= htmlspecialchars($reply['author_name']) ?>
                                            </span>

                                            <?php if ($rRole === 'teacher'): ?>
                                                <span class="inline-flex items-center gap-0.5 bg-primary/10 text-primary px-2 py-0.5 rounded-full text-[10px] font-bold border border-primary/20">
                                                    <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">verified</span> PENGAJAR
                                                </span>
                                            <?php elseif ($rRole === 'admin'): ?>
                                                <span class="inline-flex items-center gap-0.5 bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full text-[10px] font-bold border border-indigo-200">
                                                    <span class="material-symbols-outlined text-[12px]">admin_panel_settings</span> ADMIN
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[10px] bg-secondary/10 text-secondary px-2 py-0.5 rounded-full font-bold border border-secondary/20">
                                                    PESERTA
                                                </span>
                                            <?php endif; ?>

                                            <span class="text-on-surface-variant text-[12px]">· <?= htmlspecialchars($reply['time_formatted'] ?? 'Baru saja') ?></span>
                                        </div>

                                        <!-- Solusi Badge & Action -->
                                        <div class="flex items-center gap-2">
                                            <?php if ($isSolution): ?>
                                                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-300">
                                                    <span class="material-symbols-outlined text-[13px]">check_circle</span> Solusi Disetujui
                                                </span>
                                            <?php endif; ?>

                                            <!-- Otoritas Menandai Solusi untuk Teacher & Admin -->
                                            <?php if (in_array($activeRole, ['teacher', 'admin'])): ?>
                                                <button type="button" onclick="toggleSolution(<?= (int)$reply['reply_id'] ?>, <?= (int)$topic['topic_id'] ?>)" class="text-[11px] font-bold px-2 py-1 rounded-lg border border-outline-variant hover:bg-surface-variant transition-colors flex items-center gap-1 <?= $isSolution ? 'text-amber-700 border-amber-300' : 'text-emerald-700 border-emerald-300' ?>">
                                                    <span class="material-symbols-outlined text-[13px]"><?= $isSolution ? 'cancel' : 'check' ?></span>
                                                    <?= $isSolution ? 'Batalkan Solusi' : 'Tandai Solusi' ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="text-on-surface text-[14px] leading-relaxed mb-3 whitespace-pre-wrap">
                                        <?= nl2br(htmlspecialchars($reply['content'])) ?>
                                    </div>

                                    <div class="flex items-center gap-3 pt-2 border-t border-outline-variant/20 text-xs text-on-surface-variant">
                                        <button type="button" onclick="toggleReplyLike(<?= (int)$reply['reply_id'] ?>)" class="interact-btn like inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">favorite</span>
                                            <span id="reply-like-<?= (int)$reply['reply_id'] ?>"><?= (int)($reply['likes_count'] ?? 0) ?></span>
                                        </button>
                                        <span><?= htmlspecialchars($reply['author_dept'] ?? 'Manufacturing') ?></span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <!-- ============================================ -->
    <!-- STICKY BOTTOM COMPOSER -->
    <!-- ============================================ -->
    <div class="fixed bottom-0 left-0 right-0 bg-surface border-t border-outline-variant/40 px-4 py-3 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] z-40 <?= in_array($activeRole, ['teacher', 'admin']) ? 'lg:pl-[260px]' : 'lg:pl-[260px]' ?>">
        <div class="max-w-[760px] mx-auto flex items-end gap-3">
            <!-- Current user avatar -->
            <div class="w-10 h-10 rounded-full <?= $activeRole === 'teacher' ? 'bg-primary text-white' : ($activeRole === 'admin' ? 'bg-indigo-600 text-white' : 'bg-secondary text-white') ?> font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>

            <!-- Composer Input Box -->
            <div class="flex-1 bg-surface-container-low rounded-2xl border border-outline-variant/50 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all p-2 flex items-end gap-2">
                <textarea id="replyContentInput" rows="1" class="w-full bg-transparent border-none outline-none resize-none text-[14px] text-on-surface placeholder:text-on-surface-variant/60 max-h-32 p-1.5 leading-relaxed composer-area" placeholder="Tuliskan balasan atau solusi Anda sebagai <?= htmlspecialchars($userName) ?> (<?= ucfirst($activeRole) ?>)..." oninput="autoExpand(this)"></textarea>
                
                <button type="button" id="btnSubmitReply" onclick="sendReply()" class="bg-primary hover:bg-primary-600 text-white rounded-xl px-4 py-2 font-label-md font-bold text-xs flex items-center gap-1.5 shadow-sm active:scale-95 transition-all shrink-0">
                    <span>Kirim</span>
                    <span class="material-symbols-outlined text-[16px]">send</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        const activeRole = "<?= $activeRole ?>";
        const currentTopicId = <?= (int)($topic['topic_id'] ?? 1) ?>;

        function autoExpand(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        // Kirim Balasan via AJAX
        function sendReply() {
            const input = document.getElementById('replyContentInput');
            const content = input.value.trim();
            if (!content) {
                if (typeof DLS !== 'undefined') {
                    DLS.toast('Mohon ketikkan pesan balasan terlebih dahulu.', 'warning');
                } else {
                    alert('Mohon ketikkan pesan balasan terlebih dahulu.');
                }
                return;
            }

            const btn = document.getElementById('btnSubmitReply');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px]">sync</span> Mengirim...';

            let endpoint = '?route=/student/forum/reply';
            if (activeRole === 'teacher') endpoint = '?route=/teacher/forum/reply';
            if (activeRole === 'admin') endpoint = '?route=/admin/forum/reply';

            const formData = new FormData();
            formData.append('topic_id', currentTopicId);
            formData.append('content', content);

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<span>Kirim</span><span class="material-symbols-outlined text-[16px]">send</span>';

                if (data.success) {
                    input.value = '';
                    input.style.height = 'auto';

                    if (typeof DLS !== 'undefined') {
                        DLS.toast('Balasan berhasil diposting!', 'success');
                    }

                    // Reload halaman agar thread & keterhubungan data termutakhir
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    if (typeof DLS !== 'undefined') {
                        DLS.error('Gagal', data.message || 'Terjadi kesalahan saat mengirim balasan.');
                    } else {
                        alert(data.message || 'Gagal mengirim balasan.');
                    }
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<span>Kirim</span><span class="material-symbols-outlined text-[16px]">send</span>';
                console.error(err);
                if (typeof DLS !== 'undefined') {
                    DLS.error('Gagal Jaringan', 'Tidak dapat terhubung ke server.');
                }
            });
        }

        // Toggle Like Topik
        function toggleTopicLike(topicId) {
            let endpoint = '?route=/student/forum/like';
            if (activeRole === 'teacher') endpoint = '?route=/teacher/forum/like';
            if (activeRole === 'admin') endpoint = '?route=/admin/forum/like';

            const formData = new FormData();
            formData.append('topic_id', topicId);

            fetch(endpoint, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('topic-like-count').textContent = data.likes_count;
                }
            });
        }

        // Toggle Like Reply
        function toggleReplyLike(replyId) {
            let endpoint = '?route=/student/forum/like';
            if (activeRole === 'teacher') endpoint = '?route=/teacher/forum/like';
            if (activeRole === 'admin') endpoint = '?route=/admin/forum/like';

            const formData = new FormData();
            formData.append('reply_id', replyId);

            fetch(endpoint, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('reply-like-' + replyId);
                    if (el) el.textContent = data.likes_count;
                }
            });
        }

        // Toggle Solusi Terverifikasi (Teacher / Admin)
        function toggleSolution(replyId, topicId) {
            let endpoint = '?route=/teacher/forum/solution';
            if (activeRole === 'admin') endpoint = '?route=/admin/forum/solution';

            const formData = new FormData();
            formData.append('reply_id', replyId);
            formData.append('topic_id', topicId);

            fetch(endpoint, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof DLS !== 'undefined') {
                        DLS.success('Berhasil', 'Status solusi diskusi berhasil diperbarui!');
                    }
                    setTimeout(() => { window.location.reload(); }, 600);
                } else {
                    if (typeof DLS !== 'undefined') {
                        DLS.error('Gagal', data.message || 'Gagal mengubah status solusi.');
                    }
                }
            });
        }
    </script>
</body>
</html>
