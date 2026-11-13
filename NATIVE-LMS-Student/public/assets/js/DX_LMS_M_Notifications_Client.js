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
  const moreButton = document.getElementById('notificationLoadMore');
  const tabs = Array.from(document.querySelectorAll('.notification-filter'));
  const messageBox = document.getElementById('notificationMessage');
  const labels = { announcement: 'Pengumuman', course: 'Course', assessment: 'Assessment', forum: 'Forum', system: 'System' };
  const icons = { announcement: 'campaign', course: 'school', assessment: 'assignment', forum: 'forum', system: 'settings' };
  let notifications = [];
  let activeFilter = 'all';
  let limit = 20;
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
    const footer = make('div', 'mt-[12px] flex flex-wrap items-center justify-between gap-[8px] border-t border-[#F5F5F5] pt-[10px]');
    const footerLeft = make('div', 'flex items-center gap-[6px]');
    footerLeft.append(make('span', 'fd-chip fd-chip-neutral text-[11px]', labels[item.category] || 'Notifikasi'));
    if (item.show_popup === true || item.show_popup === 1) {
      const popupBadge = make('span', 'fd-chip fd-chip-warning text-[11px]', 'Popup Modal');
      footerLeft.append(popupBadge);
    }
    footer.append(footerLeft);
    if (!read) {
      const readButton = make('button', 'fd-btn fd-btn-ghost fd-btn-sm rounded-full text-[#DC2626] font-semibold hover:bg-[#FEF2F2]', 'Tandai dibaca');
      readButton.type = 'button';
      readButton.addEventListener('click', () => markRead(item.notification_id));
      footer.append(readButton);
    }
    body.append(footer);
    row.append(body);
    card.append(row);
    return card;
  }

  function render() {
    const result = filteredItems();
    const shown = result.slice(0, limit);
    list.replaceChildren();
    if (!shown.length) {
      const empty = make('div', 'rounded-xl border border-dashed border-outline-variant/50 bg-surface-container-lowest p-10 text-center');
      empty.append(make('p', 'font-semibold text-on-surface', notifications.length ? 'Tidak ada hasil' : 'Belum ada notifikasi'));
      empty.append(make('p', 'mt-1 text-sm text-on-surface-variant', notifications.length ? 'Coba kata kunci atau kategori lain.' : 'Notifikasi baru akan muncul di sini.'));
      list.append(empty);
    } else shown.forEach(item => list.append(renderCard(item)));
    if (countLabel) countLabel.textContent = result.length ? `Menampilkan ${shown.length} dari ${result.length} notifikasi` : `${notifications.length} notifikasi`;
    if (moreButton) moreButton.classList.toggle('hidden', shown.length >= result.length);
    const unread = notifications.filter(item => !isRead(item)).length;
    if (unreadLabel) unreadLabel.textContent = String(unread);
    if (markAll) markAll.disabled = busy || unread === 0;
  }

  async function refresh() {
    try {
      const result = await request('/notifications?limit=100');
      notifications = result.notifications || [];
      limit = Math.max(20, limit);
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

  tabs.forEach(tab => tab.addEventListener('click', () => {
    activeFilter = tab.dataset.filter || 'all';
    limit = 20;
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
  search?.addEventListener('input', () => { limit = 20; render(); });
  dateFilter?.addEventListener('change', () => { limit = 20; render(); });
  moreButton?.addEventListener('click', () => { limit += 20; render(); });
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
