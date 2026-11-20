(() => {
  'use strict';
  const apiBase = String(document.querySelector('meta[name="dls-api-base"]')?.content || '').replace(/\/$/, '');
  const list = document.getElementById('studentNotifications');
  if (!apiBase || !list) return;

  const search = document.getElementById('notificationSearch');
  const dateFilter = document.getElementById('notificationDate');
  const countLabel = document.getElementById('notificationResultCount');
  const unreadLabel = document.querySelector('[data-notification-unread-count]');
  const markAll = document.getElementById('notificationMarkAll');
  const clearAll = document.getElementById('notificationClearAll');
  const pagContainer = document.getElementById('paginationContainer');
  const pageSizeSelect = document.getElementById('pageSizeSelect');
  const pageIndicator = document.getElementById('pageIndicator');
  const prevPageBtn = document.getElementById('prevPageBtn');
  const nextPageBtn = document.getElementById('nextPageBtn');
  const tabs = Array.from(document.querySelectorAll('.notification-filter'));
  const messageBox = document.getElementById('notificationMessage');
  const labels = { announcement: 'Pengumuman', course: 'Course', assessment: 'Assessment', forum: 'Forum', system: 'System' };
  const icons = { announcement: 'campaign', course: 'school', assessment: 'assignment', forum: 'forum', system: 'settings' };
  let notifications = [];
  let activeFilter = 'all';
  let currentPage = 1;
  let busy = false;

  async function request(path, options = {}) {
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');
    if (options.body) headers.set('Content-Type', 'application/json');
    const response = await window.dlsFetch(`${apiBase}${path}`, { ...options, headers, credentials: 'include', cache: 'no-store' });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(result?.error?.message || 'Notifikasi tidak dapat dimuat.');
    return result;
  }

  function showMessage(text, isError = false) {
    if (!messageBox) return;
    messageBox.textContent = text;
    messageBox.hidden = !text;
    messageBox.className = `mb-4 rounded-lg border px-3 py-2.5 text-sm ${isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'}`;
  }

  function make(tag, className, text = '') {
    const node = document.createElement(tag);
    if (className) node.className = className;
    node.textContent = text;
    return node;
  }

  function isRead(item) { return item.is_read === true || item.is_read === 1; }
  function filteredItems() {
    const term = (search?.value || '').trim().toLocaleLowerCase('id');
    const day = dateFilter?.value || '';
    return notifications.filter(item => {
      const read = isRead(item);
      const categoryMatch = activeFilter === 'all'
        || (activeFilter === 'unread' ? !read : activeFilter === 'important' ? item.priority === 'important' : item.category === activeFilter);
      const haystack = `${item.title || ''} ${item.message || ''} ${item.sender_name || ''} ${item.category || ''}`.toLocaleLowerCase('id');
      const searchMatch = !term || haystack.includes(term);
      const created = new Date(item.created_at);
      const dayMatch = !day || (!Number.isNaN(created.valueOf()) && created.toLocaleDateString('en-CA') === day);
      return categoryMatch && searchMatch && dayMatch;
    });
  }

  function renderCard(item) {
    const read = isRead(item);
    const card = make('article', `fd-list-row min-w-0 rounded-[12px] bg-white p-[18px] shadow-[0_1px_3px_rgba(0,0,0,0.08)] !border-0 ${read ? '' : 'is-selected !border-l-[3px] !border-l-[#DC2626]'}`);
    const row = make('div', 'flex min-w-0 w-full items-start gap-[12px]');
    const thumb = make('div', `fd-list-thumb shrink-0 ${read ? 'bg-[#F5F5F5] text-[#525252]' : 'bg-[#FEF2F2] text-[#DC2626]'}`);
    const icon = make('span', 'material-symbols-outlined text-[22px]', icons[item.category] || 'notifications');
    icon.setAttribute('aria-hidden', 'true');
    thumb.append(icon);
    row.append(thumb);
    const body = make('div', 'min-w-0 flex-1');
    const heading = make('div', 'flex min-w-0 flex-wrap items-start justify-between gap-[8px]');
    const titleGroup = make('div', 'min-w-0');
    const titleLine = make('div', 'flex min-w-0 flex-wrap items-center gap-[8px]');
    titleLine.append(make('h2', 'fd-h4 break-words text-[#171717]', item.title || 'Notifikasi'));
    if (item.priority === 'important') titleLine.append(make('span', 'fd-chip fd-chip-promo shrink-0 text-[10px] uppercase', 'Penting'));
    titleGroup.append(titleLine);
    const sender = item.sender_name ? `Dari ${item.sender_name} · ` : '';
    const date = new Date(item.created_at);
    titleGroup.append(make('p', 'fd-mono mt-[3px] break-words text-[12px] text-[#737373]', sender + (Number.isNaN(date.valueOf()) ? '' : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }))));
    heading.append(titleGroup);
    if (!read) heading.append(make('span', 'mt-1 size-2.5 shrink-0 rounded-full bg-[#DC2626]'));
    body.append(heading);
    body.append(make('p', 'mt-[10px] whitespace-pre-wrap break-words text-[14px] leading-[22px] text-[#525252]', item.message || ''));
    if (item.image_url) {
      const imgWrap = make('div', 'mt-[10px] max-w-md overflow-hidden rounded-[8px] border border-[#E5E5E5] bg-[#FAFAFA]');
      const img = document.createElement('img');
      const rawUrl = item.image_url;
      const fullImgUrl = /^https?:\/\//i.test(rawUrl)
        ? rawUrl
        : `${apiBase}${rawUrl.startsWith('/') ? '' : '/'}${rawUrl}`;
      img.src = fullImgUrl;
      img.alt = item.title || 'Lampiran notifikasi';
      img.className = 'max-h-56 w-full object-contain';
      img.loading = 'lazy';
      imgWrap.append(img);
      body.append(imgWrap);
    }
    const footer = make('div', 'mt-[12px] flex flex-wrap items-center justify-between gap-[8px] border-t border-[#F5F5F5] pt-[10px]');
    const footerLeft = make('div', 'flex items-center gap-[6px]');
    footerLeft.append(make('span', 'fd-chip fd-chip-neutral text-[11px]', labels[item.category] || 'Notifikasi'));
    if (item.show_popup === true || item.show_popup === 1) {
      const popupBadge = make('span', 'fd-chip fd-chip-warning text-[11px]', 'Popup Modal');
      footerLeft.append(popupBadge);
    }
    footer.append(footerLeft);
    const footerActions = make('div', 'flex items-center gap-[6px]');
    if (!read) {
      const readButton = make('button', 'fd-btn fd-btn-ghost fd-btn-sm rounded-full text-[#DC2626] font-semibold hover:bg-[#FEF2F2]', 'Tandai dibaca');
      readButton.type = 'button';
      readButton.addEventListener('click', () => markRead(item.notification_id));
      footerActions.append(readButton);
    }
    const deleteButton = make('button', 'fd-btn fd-btn-ghost fd-btn-sm rounded-full text-[#737373] font-semibold hover:bg-[#FEF2F2] hover:text-[#DC2626]', 'Hapus');
    deleteButton.type = 'button';
    deleteButton.setAttribute('aria-label', `Hapus notifikasi ${item.title || ''}`.trim());
    deleteButton.addEventListener('click', () => deleteNotification(item.notification_id));
    footerActions.append(deleteButton);
    footer.append(footerActions);
    body.append(footer);
    row.append(body);
    card.append(row);
    return card;
  }

  function render() {
    const result = filteredItems();
    const pageSize = Number(pageSizeSelect?.value) || 10;
    const totalPages = Math.ceil(result.length / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;
    const shown = result.slice(start, end);

    list.replaceChildren();
    if (!shown.length) {
      const empty = make('div', 'rounded-xl border border-dashed border-outline-variant/50 bg-surface-container-lowest p-10 text-center');
      empty.append(make('p', 'font-semibold text-on-surface', notifications.length ? 'Tidak ada hasil' : 'Belum ada notifikasi'));
      empty.append(make('p', 'mt-1 text-sm text-on-surface-variant', notifications.length ? 'Coba kata kunci atau kategori lain.' : 'Notifikasi baru akan muncul di sini.'));
      list.append(empty);
    } else {
      shown.forEach(item => list.append(renderCard(item)));
    }

    if (countLabel) {
      countLabel.textContent = result.length
        ? `Menampilkan ${start + 1}–${start + shown.length} dari ${result.length} notifikasi`
        : `${notifications.length} notifikasi`;
    }

    if (pagContainer) {
      if (result.length > 0) {
        pagContainer.classList.remove('hidden');
        if (pageIndicator) pageIndicator.textContent = `Halaman ${currentPage} dari ${totalPages}`;
        if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
        if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages;
      } else {
        pagContainer.classList.add('hidden');
      }
    }

    const unread = notifications.filter(item => !isRead(item)).length;
    if (unreadLabel) unreadLabel.textContent = String(unread);
    if (markAll) markAll.disabled = busy || unread === 0;
    if (clearAll) clearAll.disabled = busy || notifications.length === 0;
  }

  async function refresh() {
    try {
      const result = await request('/notifications?limit=200');
      notifications = result.notifications || [];
      showMessage('');
      render();
    } catch (error) {
      showMessage(error.message, true);
      list.replaceChildren(make('div', 'rounded-xl border border-red-200 bg-red-50 p-6 text-center text-sm text-red-800', error.message));
      if (window.DLS?.error) window.DLS.error('Gagal Memuat Notifikasi', error.message);
    }
  }

  async function markRead(id) {
    try {
      await request(`/notifications/${encodeURIComponent(id)}/read`, { method: 'POST', body: '{}' });
      if (window.DLS?.toast) window.DLS.toast('Notifikasi ditandai sudah dibaca', 'success');
      await refresh();
    } catch (error) {
      showMessage(error.message, true);
      if (window.DLS?.error) window.DLS.error('Gagal Memperbarui Notifikasi', error.message);
    }
  }

  async function deleteNotification(id) {
    try {
      await request(`/notifications/${encodeURIComponent(id)}`, { method: 'DELETE' });
      if (window.DLS?.toast) window.DLS.toast('Notifikasi dihapus dari riwayat Anda', 'success');
      window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { type: 'notification_deleted' } }));
      await refresh();
    } catch (error) {
      showMessage(error.message, true);
      if (window.DLS?.error) window.DLS.error('Gagal Menghapus Notifikasi', error.message);
    }
  }

  clearAll?.addEventListener('click', async () => {
    const confirmed = window.DLS?.confirm
      ? await window.DLS.confirm({
          title: 'Hapus Semua Riwayat Notifikasi?',
          text: 'Seluruh notifikasi di halaman Anda akan dihapus dari riwayat akun Anda. Notifikasi milik pengguna lain tetap aman.',
          confirmText: 'Ya, Hapus Riwayat',
          cancelText: 'Batal',
          danger: true,
          icon: 'warning'
        })
      : window.confirm('Hapus semua riwayat notifikasi Anda?');
    if (!confirmed) return;
    busy = true; render();
    try {
      await request('/notifications', { method: 'DELETE' });
      if (window.DLS?.success) window.DLS.success('Riwayat Dihapus', 'Seluruh riwayat notifikasi Anda telah dibersihkan.');
      window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { type: 'notification_cleared' } }));
      await refresh();
    } catch (error) {
      showMessage(error.message, true);
      if (window.DLS?.error) window.DLS.error('Gagal Menghapus Riwayat', error.message);
    } finally {
      busy = false; render();
    }
  });

  tabs.forEach(tab => tab.addEventListener('click', () => {
    activeFilter = tab.dataset.filter || 'all';
    currentPage = 1;
    tabs.forEach(item => {
      const active = item === tab;
      item.setAttribute('aria-selected', String(active));
      item.classList.toggle('bg-primary', active);
      item.classList.toggle('text-on-primary', active);
      item.classList.toggle('font-bold', active);
      item.classList.toggle('text-on-surface-variant', !active);
    });
    render();
  }));

  search?.addEventListener('input', () => { currentPage = 1; render(); });
  dateFilter?.addEventListener('change', () => { currentPage = 1; render(); });

  pageSizeSelect?.addEventListener('change', () => {
    currentPage = 1;
    render();
  });

  prevPageBtn?.addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      render();
      list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });

  nextPageBtn?.addEventListener('click', () => {
    const pageSize = Number(pageSizeSelect?.value) || 10;
    const totalPages = Math.ceil(filteredItems().length / pageSize) || 1;
    if (currentPage < totalPages) {
      currentPage++;
      render();
      list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });

  markAll?.addEventListener('click', async () => {
    busy = true; render();
    try {
      await request('/notifications/read-all', { method: 'POST', body: '{}' });
      if (window.DLS?.success) window.DLS.success('Semua Dibaca', 'Seluruh notifikasi Anda telah ditandai sudah dibaca.');
      await refresh();
    } catch (error) {
      showMessage(error.message, true);
      if (window.DLS?.error) window.DLS.error('Gagal Menandai Semua Dibaca', error.message);
    }
    finally { busy = false; render(); }
  });

  const firstTab = tabs.find(tab => tab.dataset.filter === 'all');
  if (firstTab) firstTab.click();
  refresh();
  window.addEventListener('dls_notifications_changed', refresh);
})();
