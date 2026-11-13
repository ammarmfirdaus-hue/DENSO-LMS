const actionIcons = [
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
  [/profil|pengguna|akun|user/i, 'person'],
];

const actionSelector = 'button, a[role="button"], input[type="submit"], input[type="button"]';

function iconFor(label) {
  return actionIcons.find(([pattern]) => pattern.test(label))?.[1] || '';
}

function decorateAction(control) {
  if (control.dataset.dlsActionIgnore === 'true') return;
  if (control.closest('.dls-shell-chrome, .dls-shell-chrome-strong')) return;
  if (control.dataset.dlsActionDecorated === 'true') return;
  control.dataset.dlsActionDecorated = 'true';
  control.classList.add('dls-action-control');

  const label = [control.getAttribute('aria-label'), control.getAttribute('title'), control.innerText, control.value]
    .filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
  const hasIcon = control.querySelector('svg, img, .material-symbol, .material-symbols-outlined, .material-icons');
  if (!hasIcon && label && control.tagName !== 'INPUT') {
    const iconName = iconFor(label);
    if (iconName) {
      const icon = document.createElement('span');
      icon.className = 'material-symbol dls-action-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.textContent = iconName;
      control.prepend(icon);
    }
  }

  const background = getComputedStyle(control).backgroundColor.match(/rgba?\(([^)]+)\)/)?.[1].split(',').map(Number);
  const alpha = background?.length === 4 ? background[3] : 1;
  if (!background || alpha < 0.85) {
    const color = getComputedStyle(control).color.match(/rgba?\(([^)]+)\)/)?.[1].split(',').map(Number) || [];
    const isLightText = color.length >= 3 && color.slice(0, 3).every(channel => channel > 220);
    control.classList.add(isLightText ? 'dls-action-solid-primary' : 'dls-action-solid-neutral');
  }
}

function isActionVisible(control) {
  for (let element = control; element instanceof Element; element = element.parentElement) {
    if (element.hidden || getComputedStyle(element).display === 'none' || getComputedStyle(element).visibility === 'hidden') {
      return false;
    }
  }
  return true;
}

function updateActionGroup(group) {
  if (!(group instanceof Element)) return;
  if (group.dataset.dlsActionLayoutIgnore === 'true') {
    group.classList.remove('dls-action-grid-2x2');
    return;
  }

  const controls = Array.from(group.children);
  const containsOnlyActions = controls.length > 0 && controls.every(control =>
    control.matches(actionSelector) && control.dataset.dlsActionIgnore !== 'true'
  );
  const visibleCount = containsOnlyActions ? controls.filter(isActionVisible).length : 0;
  group.classList.toggle('dls-action-grid-2x2', visibleCount === 4);
}

function scan(root = document) {
  const controls = Array.from(root.querySelectorAll?.(actionSelector) || []);
  if (root.matches?.(actionSelector)) controls.unshift(root);

  controls.forEach(decorateAction);
  controls.forEach(control => updateActionGroup(control.parentElement));
}

scan();
new MutationObserver(records => records.forEach(record => {
  updateActionGroup(record.target);
  record.addedNodes.forEach(node => {
    if (node.nodeType === Node.ELEMENT_NODE) scan(node);
  });
})).observe(document.documentElement, { childList: true, subtree: true });
