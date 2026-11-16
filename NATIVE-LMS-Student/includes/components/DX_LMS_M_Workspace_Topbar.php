<?php
/**
 * Topbar Workspace Desktop di dalam kanvas tengah (#F4F6FA).
 * Menampilkan sapaan/judul halaman di kiri, serta kolom search interaktif (dengan dropdown suggestion)
 * dan tombol notifikasi merah tetap di pojok kanan atas tanpa background kontainer.
 */
$wtUser = $_SESSION['user'] ?? [];
$wtFirstName = trim((string) explode(' ', trim((string) ($wtUser['name'] ?? 'Siswa')))[0]);
$wtTitle = $topbar_title ?? ('Halo, ' . $wtFirstName . ' 👋');
$wtSubtitle = $topbar_subtitle ?? 'Mari pelajari hal baru hari ini!';
$wtShowSearch = $topbar_show_search ?? true;
$wtSearchId = $topbar_search_id ?? 'workspaceQuickSearch';
$wtSearchPlaceholder = $topbar_search_placeholder ?? 'Cari course atau menu...';
$wtNotifCount = (int) ($notif_count ?? ($stats['unread_notifs'] ?? 0));
$wtCanReturnTeacher = !empty($_SESSION['can_return_teacher']) || (
    in_array(strtolower(trim((string) ($wtUser['role'] ?? ''))), ['admin', 'teacher', 'reviewer'], true)
    || !empty($_SESSION['original_teacher_user'])
);
?>
<div class="dls-workspace-topbar hidden lg:flex <?= $wtShowSearch ? 'has-search' : 'no-search' ?>">
    <div class="min-w-0">
        <h1 class="text-[22px] font-bold tracking-tight text-[#181824] dark:text-white leading-tight"><?= htmlspecialchars((string) $wtTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="mt-0.5 text-[13px] text-[#64748B]"><?= htmlspecialchars((string) $wtSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div id="dlsWorkspaceFloatingTools" class="dls-workspace-floating-tools hidden lg:flex items-center gap-2.5 shrink-0">
        <?php if ($wtCanReturnTeacher): ?>
        <form method="POST" action="?route=/auth/switch-to-teacher" class="inline-flex">
            <input type="hidden" name="_csrf" value="<?= e(csrfToken()) ?>">
            <button type="submit" class="inline-flex h-[42px] items-center gap-1.5 rounded-[14px] border border-red-200 bg-white px-3.5 text-[12px] font-bold text-[#DC2626] shadow-sm transition hover:bg-[#FEF2F2] cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">swap_horiz</span>
                <span>Mode Teacher</span>
            </button>
        </form>
        <?php endif; ?>

        <?php if ($wtShowSearch): ?>
        <div class="dls-workspace-search" id="dlsTopbarSearchWrap">
            <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[19px] text-[#DC2626]" aria-hidden="true">search</span>
            <input type="search"
                   id="<?= htmlspecialchars($wtSearchId, ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="<?= htmlspecialchars($wtSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                   aria-label="<?= htmlspecialchars($wtSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                   aria-autocomplete="list"
                   aria-controls="dlsTopbarSuggestions"
                   autocomplete="off">
            <span id="dlsTopbarSearchKbd" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md border border-red-200 bg-[#FEF2F2] px-1.5 py-0.5 text-[10px] font-bold text-[#DC2626]">/</span>
            <button type="button"
                    id="dlsTopbarSearchClear"
                    class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 inline-flex h-6 w-6 items-center justify-center rounded-lg text-[#DC2626] hover:bg-[#FEF2F2] cursor-pointer"
                    aria-label="Hapus pencarian">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
            <div id="dlsTopbarSuggestions" class="dls-workspace-search-dropdown hidden" role="listbox" aria-label="Saran pencarian"></div>
        </div>
        <?php endif; ?>

        <a href="?route=/student/notifikasi"
           class="dls-workspace-icon-btn"
           title="Lihat Notifikasi"
           aria-label="Lihat Notifikasi">
            <span class="material-symbols-outlined text-[21px]" style="font-variation-settings:'FILL' 1;">notifications</span>
            <span data-notification-count
                  class="dls-topbar-notif-badge"
                  <?= $wtNotifCount > 0 ? '' : 'hidden' ?>><?= $wtNotifCount > 99 ? '99+' : $wtNotifCount ?></span>
        </a>
    </div>
</div>
<script>
(function() {
    var tools = document.getElementById('dlsWorkspaceFloatingTools');
    if (tools && tools.parentElement !== document.body) {
        document.body.appendChild(tools);
    }

    var searchWrap = document.getElementById('dlsTopbarSearchWrap');
    var input = searchWrap ? searchWrap.querySelector('input[type="search"]') : null;
    var dropdown = document.getElementById('dlsTopbarSuggestions');
    var clearBtn = document.getElementById('dlsTopbarSearchClear');
    var kbdHint = document.getElementById('dlsTopbarSearchKbd');
    if (!input || !dropdown) return;

    var menuShortcuts = [
        { type: 'menu', title: 'Beranda', subtitle: 'Ringkasan belajar & progres', icon: 'home', href: '?route=/student' },
        { type: 'menu', title: 'Katalog Course', subtitle: 'Daftar pelatihan wajib & pilihan', icon: 'school', href: '?route=/student/courses' },
        { type: 'menu', title: 'Nilai Saya', subtitle: 'Rekap nilai Quiz & evaluasi', icon: 'grade', href: '?route=/student/nilai' },
        { type: 'menu', title: 'Riwayat Belajar', subtitle: 'Course selesai & sertifikat', icon: 'history', href: '?route=/student/riwayat' },
        { type: 'menu', title: 'Notifikasi', subtitle: 'Pengumuman & pengingat terbaru', icon: 'notifications', href: '?route=/student/notifikasi' },
        { type: 'menu', title: 'Profil Saya', subtitle: 'Data diri & pengaturan akun', icon: 'person', href: '?route=/student/profil' },
        { type: 'menu', title: 'Pusat Bantuan', subtitle: 'Panduan penggunaan & FAQ', icon: 'help', href: '?route=/student/profil/bantuan' }
    ];

    var cachedCourses = null;
    var activeIndex = -1;

    function getDomSuggestions() {
        var items = [];
        document.querySelectorAll('#course-results .course-card').forEach(function(card) {
            var titleEl = card.querySelector('.course-title');
            var title = titleEl ? titleEl.textContent.trim() : '';
            if (!title) return;
            var btn = card.querySelector('button[onclick]');
            var onclickAttr = btn ? btn.getAttribute('onclick') || '' : '';
            var m = onclickAttr.match(/window\.location\.href='([^']+)'/);
            items.push({
                type: 'course',
                title: title,
                subtitle: 'Course di halaman ini',
                icon: 'menu_book',
                href: m ? m[1] : null,
                queryValue: title
            });
        });
        document.querySelectorAll('.grade-row').forEach(function(row) {
            var nameEl = row.querySelector('td p.font-semibold');
            var title = nameEl ? nameEl.textContent.trim() : '';
            if (title) {
                items.push({ type: 'grade', title: title, subtitle: 'Nilai Course', icon: 'grade', queryValue: title });
            }
        });
        return items;
    }

    async function ensureApiCourses() {
        if (cachedCourses !== null) return cachedCourses;
        cachedCourses = [];
        try {
            var res = await fetch('/api/index.php?resource=courses', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
            if (res.ok) {
                var json = await res.json();
                var rows = Array.isArray(json.data) ? json.data : [];
                cachedCourses = rows.map(function(c) {
                    return {
                        type: 'course',
                        title: String(c.title || ''),
                        subtitle: String(c.category || 'Course DENSO') + ' · ' + Number(c.progress || 0) + '%',
                        icon: 'school',
                        href: c.public_id ? ('?route=/student/course/detail&id=' + encodeURIComponent(c.public_id)) : '?route=/student/courses',
                        queryValue: String(c.title || '')
                    };
                }).filter(function(item) { return item.title !== ''; });
            }
        } catch (e) {}
        return cachedCourses;
    }

    function syncIndicators() {
        var hasVal = input.value.trim().length > 0;
        if (clearBtn) clearBtn.classList.toggle('hidden', !hasVal);
        if (kbdHint) kbdHint.classList.toggle('hidden', hasVal);
    }

    function renderSuggestions(items) {
        dropdown.innerHTML = '';
        activeIndex = -1;
        if (!items.length) {
            dropdown.classList.add('hidden');
            return;
        }
        items.slice(0, 7).forEach(function(item, idx) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dls-workspace-search-item';
            btn.setAttribute('role', 'option');
            btn.dataset.index = String(idx);

            var iconWrap = document.createElement('span');
            iconWrap.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#FEF2F2] text-[#DC2626]';
            var icon = document.createElement('span');
            icon.className = 'material-symbols-outlined text-[17px]';
            icon.textContent = item.icon || 'search';
            iconWrap.appendChild(icon);

            var copy = document.createElement('span');
            copy.className = 'min-w-0 flex-1';
            var title = document.createElement('span');
            title.className = 'block truncate font-semibold text-[#181824] dark:text-white';
            title.textContent = item.title;
            var sub = document.createElement('span');
            sub.className = 'block truncate text-[11px] text-[#64748B]';
            sub.textContent = item.subtitle || '';
            copy.appendChild(title);
            copy.appendChild(sub);

            btn.appendChild(iconWrap);
            btn.appendChild(copy);

            btn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                if (item.href) {
                    window.location.href = item.href;
                } else if (item.queryValue) {
                    input.value = item.queryValue;
                    syncIndicators();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    dropdown.classList.add('hidden');
                }
            });
            dropdown.appendChild(btn);
        });
        dropdown.classList.remove('hidden');
    }

    async function updateSuggestions() {
        syncIndicators();
        var q = input.value.trim().toLowerCase();
        if (!q) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }
        var domItems = getDomSuggestions();
        var pool = domItems.length ? domItems : await ensureApiCourses();
        var seen = Object.create(null);
        var matches = pool.filter(function(item) {
            var key = item.type + ':' + item.title.toLowerCase();
            if (seen[key]) return false;
            seen[key] = true;
            return item.title.toLowerCase().indexOf(q) !== -1 || (item.subtitle || '').toLowerCase().indexOf(q) !== -1;
        });
        renderSuggestions(matches);
    }

    input.addEventListener('focus', function() {
        if (input.value.trim()) updateSuggestions();
    });
    input.addEventListener('input', updateSuggestions);
    input.addEventListener('search', function() {
        syncIndicators();
        updateSuggestions();
    });
    input.addEventListener('keydown', function(e) {
        var opts = Array.from(dropdown.querySelectorAll('.dls-workspace-search-item'));
        if (e.key === 'Escape') {
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            } else if (input.value) {
                input.value = '';
                syncIndicators();
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
            return;
        }
        if (!opts.length || dropdown.classList.contains('hidden')) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % opts.length;
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = (activeIndex - 1 + opts.length) % opts.length;
        } else if (e.key === 'Enter' && activeIndex >= 0 && opts[activeIndex]) {
            e.preventDefault();
            opts[activeIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
            return;
        } else {
            return;
        }
        opts.forEach(function(o, i) { o.classList.toggle('is-highlighted', i === activeIndex); });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            input.value = '';
            syncIndicators();
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
        });
    }

    document.addEventListener('click', function(e) {
        if (!searchWrap.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            var tag = (document.activeElement && document.activeElement.tagName || '').toLowerCase();
            if (tag !== 'input' && tag !== 'textarea' && tag !== 'select') {
                e.preventDefault();
                input.focus();
            }
        }
    });
    syncIndicators();
})();
</script>
