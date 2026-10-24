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
    const response = await fetch(`${apiBase}${path}`, { ...options, headers, credentials: 'include', cache: 'no-store' });
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
    const card = make('article', `min-w-0 rounded-xl border bg-surface-container-lowest p-4 sm:p-5 ${read ? 'border-outline-variant/40' : 'border-primary/30 bg-primary/[0.02]'}`);
    const row = make('div', 'flex min-w-0 items-start gap-3');
    const icon = make('span', `material-symbols-outlined mt-0.5 text-[22px] ${read ? 'text-on-surface-variant' : 'text-primary'}`, icons[item.category] || 'notifications');
    icon.setAttribute('aria-hidden', 'true');
    row.append(icon);
    const body = make('div', 'min-w-0 flex-1');
    const heading = make('div', 'flex min-w-0 flex-wrap items-start justify-between gap-2');
    const titleGroup = make('div', 'min-w-0');
    const titleLine = make('div', 'flex min-w-0 flex-wrap items-center gap-2');
    titleLine.append(make('h2', 'break-words text-sm font-semibold text-on-surface sm:text-base', item.title || 'Notifikasi'));
    if (item.priority === 'important') titleLine.append(make('span', 'shrink-0 rounded bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase text-primary', 'Penting'));
    titleGroup.append(titleLine);
    const sender = item.sender_name ? `Dari ${item.sender_name} · ` : '';
    const date = new Date(item.created_at);
    titleGroup.append(make('p', 'mt-1 break-words text-xs text-on-surface-variant', sender + (Number.isNaN(date.valueOf()) ? '' : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }))));
    heading.append(titleGroup);
    if (!read) heading.append(make('span', 'mt-1 size-2 shrink-0 rounded-full bg-primary'));
    body.append(heading);
    body.append(make('p', 'mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-on-surface-variant', item.message || ''));
    const footer = make('div', 'mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/30 pt-3');
    footer.append(make('span', 'rounded-md bg-surface-container-low px-2 py-1 text-xs text-on-surface-variant', labels[item.category] || 'Notifikasi'));
    if (!read) {
      const readButton = make('button', 'min-h-9 rounded-md px-2 text-xs font-semibold text-primary hover:bg-primary/5', 'Tandai dibaca');
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
    }
  }

  async function markRead(id) {
    try { await request(`/notifications/${encodeURIComponent(id)}/read`, { method: 'POST', body: '{}' }); await refresh(); }
    catch (error) { showMessage(error.message, true); }
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
    try { await request('/notifications/read-all', { method: 'POST', body: '{}' }); await refresh(); }
    catch (error) { showMessage(error.message, true); }
    finally { busy = false; render(); }
  });

  const firstTab = tabs.find(tab => tab.dataset.filter === 'all');
  if (firstTab) firstTab.click();
  refresh();
  window.addEventListener('dls_notifications_changed', refresh);
})();
