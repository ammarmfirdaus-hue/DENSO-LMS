(() => {
  'use strict';
  const badges = Array.from(document.querySelectorAll('[data-notification-count]'));
  const apiBase = String(document.querySelector('meta[name="dls-api-base"]')?.content || '').replace(/\/$/, '');
  if (!badges.length || !apiBase) return;

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
      const response = await fetch(`${apiBase}/notifications/unread-count`, {
        credentials: 'include', headers: { Accept: 'application/json' }, cache: 'no-store'
      });
      if (!response.ok) return;
      const result = await response.json();
      updateBadges(result.unreadCount, notify);
    } catch { /* The server count is refreshed when the API is reachable. */ }
  }

  refresh(false);
  const events = new EventSource(`${apiBase}/notifications/events`, { withCredentials: true });
  events.addEventListener('ready', () => refresh(false));
  events.addEventListener('notification', refresh);
  window.addEventListener('focus', refresh);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') refresh();
  });
  window.addEventListener('pagehide', () => events.close(), { once: true });
})();
