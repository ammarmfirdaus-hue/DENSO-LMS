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

  let modalElement = null;

  function showNotificationModal(notification) {
    if (modalElement) {
      modalElement.remove();
      modalElement = null;
    }
    const id = notification.notification_id;
    if (id) {
      sessionStorage.setItem(`dls_seen_modal_${id}`, '1');
    }

    const backdrop = document.createElement('div');
    backdrop.className = 'fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs transition-opacity duration-200';
    backdrop.setAttribute('role', 'dialog');
    backdrop.setAttribute('aria-modal', 'true');
    backdrop.setAttribute('aria-labelledby', 'modalNotifTitle');

    const card = document.createElement('div');
    card.className = 'relative flex w-full max-w-lg flex-col overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-2xl ring-1 ring-black/10';

    const isImportant = notification.priority === 'important';
    const header = document.createElement('div');
    header.className = `flex items-center justify-between border-b border-slate-100 p-5 sm:p-6 ${isImportant ? 'bg-red-50/80' : 'bg-slate-50/80'}`;

    const headerLeft = document.createElement('div');
    headerLeft.className = 'flex items-center gap-3 min-w-0';

    const iconSpan = document.createElement('span');
    iconSpan.className = `grid size-11 shrink-0 place-items-center rounded-2xl ${isImportant ? 'bg-red-600 text-white shadow-md shadow-red-200' : 'bg-red-50 text-red-700'}`;
    const iconName = isImportant ? 'notifications_active' : (notification.category === 'course' ? 'school' : notification.category === 'assessment' ? 'assignment' : notification.category === 'forum' ? 'forum' : notification.category === 'system' ? 'settings' : 'campaign');
    iconSpan.innerHTML = `<span class="material-symbols-outlined text-2xl" aria-hidden="true">${iconName}</span>`;

    const meta = document.createElement('div');
    meta.className = 'min-w-0';

    const badgesWrap = document.createElement('div');
    badgesWrap.className = 'flex flex-wrap items-center gap-2';

    const catBadge = document.createElement('span');
    catBadge.className = 'rounded-md bg-white/90 px-2.5 py-0.5 text-xs font-semibold text-slate-700 shadow-xs border border-slate-200';
    catBadge.textContent = notification.category === 'announcement' ? 'Pengumuman' : notification.category === 'course' ? 'Course' : notification.category === 'assessment' ? 'Assessment' : notification.category === 'forum' ? 'Forum' : notification.category === 'system' ? 'Sistem' : 'Pemberitahuan';
    badgesWrap.append(catBadge);

    if (isImportant) {
      const impBadge = document.createElement('span');
      impBadge.className = 'rounded-md bg-red-600 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white';
      impBadge.textContent = 'Penting';
      badgesWrap.append(impBadge);
    }

    const popupBadge = document.createElement('span');
    popupBadge.className = 'rounded-md bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-semibold flex items-center gap-1';
    popupBadge.innerHTML = '<span class="material-symbols-outlined text-xs">campaign</span> Pemberitahuan';
    badgesWrap.append(popupBadge);

    const senderText = document.createElement('p');
    senderText.className = 'mt-1 text-xs text-slate-500 truncate';
    const dateStr = notification.created_at ? new Date(notification.created_at).toLocaleString('id-ID') : 'Baru saja';
    senderText.textContent = `Dari: ${notification.sender_name || 'Admin'} · ${dateStr}`;

    meta.append(badgesWrap, senderText);
    headerLeft.append(iconSpan, meta);

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'grid size-9 shrink-0 place-items-center rounded-xl text-slate-400 hover:bg-white hover:text-slate-700 transition cursor-pointer border border-transparent hover:border-slate-200';
    closeBtn.setAttribute('aria-label', 'Tutup');
    closeBtn.innerHTML = '<span class="material-symbols-outlined text-xl">close</span>';

    header.append(headerLeft, closeBtn);

    const body = document.createElement('div');
    body.className = 'p-5 sm:p-6 space-y-4 max-h-[60vh] overflow-y-auto';

    const titleEl = document.createElement('h3');
    titleEl.id = 'modalNotifTitle';
    titleEl.className = 'text-lg sm:text-xl font-bold text-slate-900 break-words leading-snug';
    titleEl.textContent = notification.title || 'Pemberitahuan';

    const msgBox = document.createElement('div');
    msgBox.className = 'rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 sm:p-5 text-sm sm:text-base leading-relaxed text-slate-700 whitespace-pre-wrap break-words';
    msgBox.textContent = notification.message || '';

    body.append(titleEl, msgBox);

    const footer = document.createElement('div');
    footer.className = 'flex flex-col-reverse sm:flex-row items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 p-4 sm:px-6';

    const viewAllLink = document.createElement('a');
    viewAllLink.href = '?route=/student/notifikasi';
    viewAllLink.className = 'text-xs font-semibold text-red-600 hover:text-red-700 hover:underline py-2';
    viewAllLink.textContent = 'Lihat Semua Notifikasi';

    const ackBtn = document.createElement('button');
    ackBtn.type = 'button';
    ackBtn.className = 'w-full sm:w-auto min-h-11 rounded-xl bg-red-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-red-200 hover:bg-red-700 active:scale-[0.98] transition cursor-pointer';
    ackBtn.textContent = 'Saya Mengerti';

    footer.append(viewAllLink, ackBtn);

    card.append(header, body, footer);
    backdrop.append(card);

    function closeModal() {
      if (id) {
        window.dlsFetch(`${apiBase}/notifications/${encodeURIComponent(id)}/read`, {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: '{}'
        }).then(() => refresh(true)).catch(() => {});
      }
      backdrop.remove();
      modalElement = null;
      window.removeEventListener('keydown', onKeyDown);
    }

    const onKeyDown = e => {
      if (e.key === 'Escape') closeModal();
    };

    closeBtn.addEventListener('click', closeModal);
    ackBtn.addEventListener('click', closeModal);
    viewAllLink.addEventListener('click', closeModal);
    backdrop.addEventListener('click', e => {
      if (e.target === backdrop) closeModal();
    });
    window.addEventListener('keydown', onKeyDown);

    document.body.append(backdrop);
    modalElement = backdrop;
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
    try {
      const latest = await getLatestNotification();
      if (latest) {
        if (latestNotificationId === null) latestNotificationId = latest.notification_id ?? null;
        if ((latest.show_popup === true || latest.show_popup === 1) && !latest.is_read) {
          const seenKey = `dls_seen_modal_${latest.notification_id}`;
          if (!sessionStorage.getItem(seenKey)) {
            showNotificationModal(latest);
          }
        }
      }
    } catch { /* Retry through the event stream later. */ }
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
      if (notification.show_popup === true || notification.show_popup === 1) {
        showNotificationModal(notification);
      } else {
        showNotificationToast(notification);
      }
    }).catch(() => {});
  });
  window.addEventListener('focus', refresh);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') refresh();
  });
  window.addEventListener('pagehide', () => events.close(), { once: true });
})();
