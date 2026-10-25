(function () {
    'use strict';

    var actionIcons = [
        [/hapus|delete|remove|nonaktifkan|keluar|logout/i, 'delete'],
        [/edit|ubah|perbarui|update/i, 'edit'],
        [/tambah|buat|create|add|baru/i, 'add'],
        [/simpan|save|perbarui akun/i, 'save'],
        [/kirim|send|publikasi|ajukan/i, 'send'],
        [/export|ekspor|download|unduh/i, 'download'],
        [/upload|unggah|pilih berkas/i, 'upload'],
        [/cari|search/i, 'search'],
        [/filter/i, 'filter_list'],
        [/tutup|close|batal|cancel/i, 'close'],
        [/masuk|login/i, 'login'],
        [/lihat|buka|detail|preview|pratinjau/i, 'visibility'],
        [/notifikasi|notification/i, 'notifications'],
        [/forum|diskusi|balas|reply/i, 'forum'],
        [/course|kursus|materi|pelajaran/i, 'menu_book'],
        [/nilai|grade|skor/i, 'grade'],
        [/lebih|selengkapnya|tampilkan semua|sembunyikan/i, 'expand_more'],
        [/quiz|ujian|asesmen|assessment/i, 'quiz'],
        [/tandai.*dibaca|selesai|setuju|approved|lulus/i, 'check_circle'],
        [/password|kata sandi|tampilkan password/i, 'visibility'],
        [/sebelumnya|kembali|back/i, 'arrow_back'],
        [/berikutnya|lanjut|next/i, 'arrow_forward'],
        [/profil|pengguna|akun|user/i, 'person']
    ];
    var actionSelector = 'button,a[role="button"],input[type="submit"],input[type="button"]';

    function iconFor(label) {
        for (var i = 0; i < actionIcons.length; i++) {
            if (actionIcons[i][0].test(label)) return actionIcons[i][1];
        }
        return '';
    }

    function decorate(control) {
        if (control.dataset.dlsActionDecorated === 'true') return;
        control.dataset.dlsActionDecorated = 'true';
        control.classList.add('dls-action-control');
        var label = [control.getAttribute('aria-label'), control.getAttribute('title'), control.innerText, control.value]
            .filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
        var hasIcon = control.querySelector('svg,img,.material-symbol,.material-symbols-outlined,.material-icons');
        if (!hasIcon && label && control.tagName !== 'INPUT') {
            var iconName = iconFor(label);
            if (iconName) {
                var icon = document.createElement('span');
                icon.className = 'material-symbols-outlined dls-action-icon';
                icon.setAttribute('aria-hidden', 'true');
                icon.textContent = iconName;
                control.prepend(icon);
            }
        }
        var match = getComputedStyle(control).backgroundColor.match(/rgba?\(([^)]+)\)/);
        var channels = match ? match[1].split(',').map(Number) : null;
        var alpha = channels && channels.length === 4 ? channels[3] : 1;
        if (!channels || alpha < 0.85) {
            var text = getComputedStyle(control).color.match(/rgba?\(([^)]+)\)/);
            var rgb = text ? text[1].split(',').map(Number) : [];
            var lightText = rgb.length >= 3 && rgb.slice(0, 3).every(function (value) { return value > 220; });
            control.classList.add(lightText ? 'dls-action-solid-primary' : 'dls-action-solid-neutral');
        }
    }

    function isActionVisible(control) {
        for (var element = control; element && element.nodeType === Node.ELEMENT_NODE; element = element.parentElement) {
            var style = getComputedStyle(element);
            if (element.hidden || style.display === 'none' || style.visibility === 'hidden') return false;
        }
        return true;
    }

    function updateActionGroup(group) {
        if (!group || group.nodeType !== Node.ELEMENT_NODE) return;

        var controls = Array.prototype.slice.call(group.children);
        var containsOnlyActions = controls.length > 0 && controls.every(function (control) {
            return control.matches(actionSelector);
        });
        var visibleCount = containsOnlyActions ? controls.filter(isActionVisible).length : 0;
        group.classList.toggle('dls-action-grid-2x2', visibleCount === 4);
    }

    function scan(root) {
        root = root || document;
        var controls = Array.prototype.slice.call(root.querySelectorAll(actionSelector));
        if (root.matches && root.matches(actionSelector)) controls.unshift(root);
        controls.forEach(decorate);
        controls.forEach(function (control) { updateActionGroup(control.parentElement); });
    }

    scan();
    new MutationObserver(function (records) {
        records.forEach(function (record) {
            updateActionGroup(record.target);
            record.addedNodes.forEach(function (node) {
                if (node.nodeType === Node.ELEMENT_NODE) scan(node);
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true });
}());
