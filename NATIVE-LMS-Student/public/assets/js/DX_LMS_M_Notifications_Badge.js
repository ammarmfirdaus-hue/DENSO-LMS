(() => {
  'use strict';
  const badges = Array.from(document.querySelectorAll('[data-notification-count]'));
  const apiBase = String(document.querySelector('meta[name="dls-api-base"]')?.content || '').replace(/\/$/, '');
  if (!badges.length || !apiBase) return;
  let latestNotificationId = null;
  let toastElement = null;
  let toastCount = 0;
  let toastExpanded = false;
  let toastTimer = null;

  async function getLatestNotification() {
    const response = await window.dlsFetch(`${apiBase}/notifications?limit=1`, {
      credentials: 'include', headers: { Accept: 'application/json' }, cache: 'no-store'
    });
    if (!response.ok) return null;
    const result = await response.json();
    return result.notifications?.[0] || null;
  }

  function showNotificationToast(notification) {
    toastCount += 1;
    if (!toastElement) {
      toastElement = document.createElement('section');
      toastElement.setAttribute('role', 'status');
      toastElement.setAttribute('aria-live', 'polite');
      toastElement.className = 'fixed right-3 top-20 z-[9999] w-[calc(100vw-1.5rem)] max-w-sm overflow-hidden rounded-xl border border-red-200 bg-white text-slate-900 shadow-xl sm:right-6';

      const row = document.createElement('div');
      row.className = 'flex items-start gap-3 border-l-4 border-red-600 p-3 sm:p-4';
      const iconBox = document.createElement('span');
      iconBox.className = 'grid size-9 shrink-0 place-items-center rounded-lg bg-red-50 text-red-700';
      iconBox.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">notifications_active</span>';
      const body = document.createElement('div');
      body.className = 'min-w-0 flex-1';
      const heading = document.createElement('div');
      heading.className = 'flex items-start justify-between gap-2';
      const title = document.createElement('p');
      title.dataset.toastHeading = 'true';
      title.className = 'pt-1 text-sm font-bold';
      const actions = document.createElement('div');
      actions.className = 'flex shrink-0 items-center gap-1';
      const resize = document.createElement('button');
      resize.type = 'button';
      resize.className = 'grid size-8 place-items-center rounded-lg border border-outline-variant/50 bg-white text-on-surface-variant hover:bg-surface-container-low';
      resize.setAttribute('aria-label', 'Lebarkan notifikasi');
      resize.title = 'Lebarkan';
      resize.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">open_in_full</span>';
      resize.addEventListener('click', () => {
        toastExpanded = !toastExpanded;
        toastElement.classList.toggle('max-w-sm', !toastExpanded);
        toastElement.classList.toggle('max-w-xl', toastExpanded);
        resize.setAttribute('aria-label', toastExpanded ? 'Perkecil notifikasi' : 'Lebarkan notifikasi');
        resize.title = toastExpanded ? 'Perkecil' : 'Lebarkan';
        resize.innerHTML = `<span class="material-symbols-outlined" aria-hidden="true">${toastExpanded ? 'close_fullscreen' : 'open_in_full'}</span>`;
      });
      const close = document.createElement('button');
      close.type = 'button';
      close.className = 'grid size-8 place-items-center rounded-lg border border-outline-variant/50 bg-white text-on-surface-variant hover:bg-surface-container-low';
      close.setAttribute('aria-label', 'Tutup notifikasi');
      close.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">close</span>';
      close.addEventListener('click', () => {
        window.clearTimeout(toastTimer);
        toastElement?.remove();
        toastElement = null;
        toastCount = 0;
        toastExpanded = false;
      });
      actions.append(resize, close);
      heading.append(title, actions);
      const itemTitle = document.createElement('p');
      itemTitle.dataset.toastTitle = 'true';
      itemTitle.className = 'mt-1 break-words text-sm font-semibold text-on-surface';
      const message = document.createElement('p');
      message.dataset.toastMessage = 'true';
      message.className = 'mt-1 line-clamp-2 break-words text-xs leading-5 text-on-surface-variant';
      const link = document.createElement('a');
      link.href = '?route=/student/notifikasi';
      link.className = 'mt-2 inline-flex min-h-8 items-center rounded-md border border-red-200 px-2.5 text-xs font-semibold text-red-700 hover:bg-red-50';
      link.textContent = 'Lihat notifikasi';
      body.append(heading, itemTitle, message, link);
      row.append(iconBox, body);
      toastElement.append(row);
      document.body.append(toastElement);
    }

    const heading = toastElement.querySelector('[data-toast-heading]');
    const title = toastElement.querySelector('[data-toast-title]');
    const message = toastElement.querySelector('[data-toast-message]');
    if (heading) heading.textContent = toastCount > 1 ? `Notifikasi baru · ${toastCount}` : 'Notifikasi baru';
    if (title) title.textContent = notification.title || 'Pemberitahuan';
    if (message) message.textContent = notification.message || '';
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => {
      toastElement?.remove();
      toastElement = null;
      toastCount = 0;
      toastExpanded = false;
    }, 7000);
  }

  function updateBadges(value, notify = true) {
    const count = Math.max(0, Number(value) || 0);
    badges.forEach(badge => {
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.hidden = count === 0;
      const link = badge.closest('a');
      if (link) link.setAttribute('aria-label', count ? `Notifikasi, ${count} belum dibaca` : 'Notifikasi');
    });
    if (notify) window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { unreadCount: count } }));
  }

  async function refresh(notify = true) {
    try {
      const response = await window.dlsFetch(`${apiBase}/notifications/unread-count`, {
        credentials: 'include', headers: { Accept: 'application/json' }, cache: 'no-store'
      });
      if (!response.ok) return;
      const result = await response.json();
      updateBadges(result.unreadCount, notify);
    } catch { /* The server count is refreshed when the API is reachable. */ }
  }

  refresh(false);
  const events = new EventSource(`${apiBase}/notifications/events`, { withCredentials: true });
  events.addEventListener('ready', async () => {
    refresh(false);
    try { latestNotificationId = (await getLatestNotification())?.notification_id ?? null; } catch { /* Retry through the event stream later. */ }
  });
  events.addEventListener('notification', event => {
    refresh();
    let detail = {};
    try { detail = JSON.parse(event.data || '{}'); } catch {}
    if (detail.type !== 'notification_created') return;
    getLatestNotification().then(notification => {
      if (!notification) return;
      const currentId = String(notification.notification_id);
      if (latestNotificationId !== null && String(latestNotificationId) === currentId) return;
      latestNotificationId = currentId;
      showNotificationToast(notification);
    }).catch(() => {});
  });
  window.addEventListener('focus', refresh);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') refresh();
  });
  window.addEventListener('pagehide', () => events.close(), { once: true });
})();
