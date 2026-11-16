<?php
/**
 * Panel Kanan Modern LMS (Profil, Mini Kalender, Jadwal Belajar, Pengingat).
 * Ditampilkan otomatis pada desktop lebar (xl+) di semua halaman menu utama.
 */
$rpUser = $_SESSION['user'] ?? [];
$rpName = trim((string) ($rpUser['name'] ?? ($student_name ?? 'Siswa DENSO')));
$rpNpk = trim((string) ($rpUser['npk'] ?? ($student_npk ?? '-')));
$rpPhoto = trim((string) ($profile['photo_url'] ?? ($rpUser['photo_url'] ?? '')));
$rpNotif = (int) ($notif_count ?? ($stats['unread_notifs'] ?? 0));

$rpInitials = '';
foreach (array_slice(explode(' ', $rpName), 0, 2) as $part) {
    if ($part !== '') {
        $rpInitials .= strtoupper(substr($part, 0, 1));
    }
}
if ($rpInitials === '') {
    $rpInitials = 'DS';
}

// Ambil maksimal 2 course aktif/tersedia untuk ringkasan Jadwal Belajar
$rpCourses = [];
if (isset($courses) && is_array($courses)) {
    foreach ($courses as $item) {
        if (($item['status'] ?? '') !== 'selesai') {
            $rpCourses[] = $item;
        }
        if (count($rpCourses) >= 2) {
            break;
        }
    }
}
?>
<aside id="dlsRightPanel" aria-label="Panel Profil dan Jadwal">
    <!-- Profile Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-[16px] font-bold text-[#181824] dark:text-white">Profil</h2>
        <a href="?route=/student/profil/detail"
           class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200/80 bg-white text-slate-600 transition hover:border-red-200 hover:text-red-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
           title="Lihat & Edit Profil"
           aria-label="Lihat dan Edit Profil">
            <span class="material-symbols-outlined text-[17px]">edit</span>
        </a>
    </div>

    <!-- Centered Avatar & Identity -->
    <div class="flex flex-col items-center text-center">
        <a href="?route=/student/profil" class="group relative mb-3 block">
            <?php if ($rpPhoto !== ''): ?>
            <img src="<?= htmlspecialchars($rpPhoto, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($rpName, ENT_QUOTES, 'UTF-8') ?>"
                 class="h-20 w-20 rounded-full object-cover ring-4 ring-[#FEF2F2] transition group-hover:ring-red-200"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="hidden h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-[#EF4444] to-[#B91C1C] text-xl font-bold text-white ring-4 ring-[#FEF2F2]">
                <?= htmlspecialchars($rpInitials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php else: ?>
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-[#EF4444] to-[#B91C1C] text-xl font-bold text-white ring-4 ring-[#FEF2F2] shadow-sm transition group-hover:scale-[1.02]">
                <?= htmlspecialchars($rpInitials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
            <span class="absolute bottom-0.5 right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500" title="Aktif"></span>
        </a>
        <p class="text-[15px] font-bold text-[#181824] dark:text-white leading-snug"><?= htmlspecialchars($rpName, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="mt-0.5 text-[12px] text-[#64748B]">NPK <?= htmlspecialchars($rpNpk, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <!-- Mini Calendar Card -->
    <div class="rounded-2xl border border-slate-100 bg-[#F8FAFC] p-3.5 dark:border-slate-800 dark:bg-slate-800/60" id="dlsMiniCalendar">
        <div class="mb-2.5 flex items-center justify-between">
            <button type="button" id="dlsCalPrev" class="inline-flex h-6 w-6 items-center justify-center rounded-lg text-slate-500 hover:bg-white hover:text-red-600 cursor-pointer" aria-label="Minggu sebelumnya">
                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
            </button>
            <span id="dlsCalTitle" class="text-[12.5px] font-bold text-[#181824] dark:text-white"></span>
            <button type="button" id="dlsCalNext" class="inline-flex h-6 w-6 items-center justify-center rounded-lg text-slate-500 hover:bg-white hover:text-red-600 cursor-pointer" aria-label="Minggu berikutnya">
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            </button>
        </div>
        <div id="dlsCalDays" class="dls-mini-cal-grid"></div>
    </div>

    <!-- Schedule / Jadwal Belajar -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between">
            <h3 class="text-[14.5px] font-bold text-[#181824] dark:text-white">Jadwal Belajar</h3>
            <a href="?route=/student/courses" class="text-[11.5px] font-semibold text-[#64748B] hover:text-[#DC2626]">Lihat semua</a>
        </div>
        <?php if (!empty($rpCourses)): ?>
            <?php foreach ($rpCourses as $idx => $rc): ?>
            <?php $rcPublicId = function_exists('publicCourseId') ? publicCourseId((string) ($rc['id'] ?? '')) : (string) ($rc['id'] ?? ''); ?>
            <a href="?route=/student/course/detail&id=<?= htmlspecialchars(urlencode($rcPublicId), ENT_QUOTES, 'UTF-8') ?>"
               class="dls-schedule-card <?= $idx % 2 === 1 ? 'is-neutral' : '' ?>">
                <div class="shrink-0 text-center min-w-[44px]">
                    <p class="text-[13px] font-bold text-[#181824] dark:text-white"><?= (int) ($rc['progress'] ?? 0) ?>%</p>
                    <p class="text-[10px] font-medium text-[#64748B]">Progres</p>
                </div>
                <div class="h-8 w-[1.5px] shrink-0 rounded-full <?= $idx % 2 === 0 ? 'bg-red-200' : 'bg-slate-300' ?>"></div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[12.5px] font-bold text-[#181824] dark:text-white"><?= htmlspecialchars((string) ($rc['title'] ?? 'Course'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="truncate text-[11px] text-[#64748B]"><?= htmlspecialchars((string) ($rc['category'] ?? 'Pelatihan Wajib'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <a href="?route=/student/courses" class="dls-schedule-card">
                <div class="shrink-0 text-center min-w-[44px]">
                    <span class="material-symbols-outlined text-[20px] text-[#DC2626]">menu_book</span>
                </div>
                <div class="h-8 w-[1.5px] shrink-0 rounded-full bg-red-200"></div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[12.5px] font-bold text-[#181824] dark:text-white">Jelajahi Katalog</p>
                    <p class="truncate text-[11px] text-[#64748B]">Pilih materi pelatihan baru</p>
                </div>
            </a>
            <a href="?route=/student/riwayat" class="dls-schedule-card is-neutral">
                <div class="shrink-0 text-center min-w-[44px]">
                    <span class="material-symbols-outlined text-[20px] text-[#475569]">history</span>
                </div>
                <div class="h-8 w-[1.5px] shrink-0 rounded-full bg-slate-300"></div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[12.5px] font-bold text-[#181824] dark:text-white">Riwayat & Sertifikat</p>
                    <p class="truncate text-[11px] text-[#64748B]">Cek capaian pelatihan Anda</p>
                </div>
            </a>
        <?php endif; ?>
    </div>

    <!-- Reminders / Pengingat -->
    <div class="space-y-2.5 mt-auto pt-1">
        <h3 class="text-[14.5px] font-bold text-[#181824] dark:text-white">Pengingat</h3>
        <a href="?route=/student/notifikasi" class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-[#F8FAFC] p-3 transition hover:border-red-200 dark:border-slate-800 dark:bg-slate-800/60">
            <span class="flex shrink-0 items-center justify-center text-[#DC2626]">
                <span class="material-symbols-outlined text-[24px]">notifications_active</span>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12.5px] font-bold text-[#181824] dark:text-white">
                    <?= $rpNotif > 0 ? $rpNotif . ' Notifikasi Belum Dibaca' : 'Semua Notifikasi Terbaca' ?>
                </p>
                <p class="truncate text-[11px] text-[#64748B]">Klik untuk melihat info terbaru</p>
            </div>
        </a>
        <a href="?route=/student/nilai" class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-[#F8FAFC] p-3 transition hover:border-red-200 dark:border-slate-800 dark:bg-slate-800/60">
            <span class="flex shrink-0 items-center justify-center text-[#0F2744] dark:text-slate-300">
                <span class="material-symbols-outlined text-[24px]">assignment_turned_in</span>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12.5px] font-bold text-[#181824] dark:text-white">Evaluasi & Nilai Quiz</p>
                <p class="truncate text-[11px] text-[#64748B]">Pantau skor kelulusan Anda</p>
            </div>
        </a>
    </div>
</aside>

<script>
(function() {
    var titleEl = document.getElementById('dlsCalTitle');
    var daysEl = document.getElementById('dlsCalDays');
    var prevBtn = document.getElementById('dlsCalPrev');
    var nextBtn = document.getElementById('dlsCalNext');
    if (!titleEl || !daysEl) return;

    var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    var dayNames = ['MIN','SEN','SEL','RAB','KAM','JUM','SAB'];
    var today = new Date();
    var anchor = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    function renderWeek() {
        var start = new Date(anchor);
        start.setDate(anchor.getDate() - anchor.getDay());
        titleEl.textContent = months[anchor.getMonth()] + ' ' + anchor.getFullYear();
        var html = '';
        for (var i = 0; i < 7; i++) {
            var d = new Date(start);
            d.setDate(start.getDate() + i);
            var isToday = d.getFullYear() === today.getFullYear() && d.getMonth() === today.getMonth() && d.getDate() === today.getDate();
            html += '<div class="dls-mini-cal-day' + (isToday ? ' is-today' : '') + '">';
            html += '<span class="text-[9.5px] font-semibold uppercase tracking-wide opacity-75">' + dayNames[i] + '</span>';
            html += '<span class="mt-1 text-[12px] font-bold">' + d.getDate() + '</span>';
            html += '</div>';
        }
        daysEl.innerHTML = html;
    }

    if (prevBtn) prevBtn.addEventListener('click', function() { anchor.setDate(anchor.getDate() - 7); renderWeek(); });
    if (nextBtn) nextBtn.addEventListener('click', function() { anchor.setDate(anchor.getDate() + 7); renderWeek(); });
    renderWeek();
})();
</script>

