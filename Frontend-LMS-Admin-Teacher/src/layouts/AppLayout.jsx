import { Link, Outlet, useLocation } from 'react-router-dom';
import { useEffect, useRef, useState } from 'react';
import { API_BASE, api } from '../api/client';
import Sidebar from '../components/Sidebar';
import JobsCenter from '../components/JobsCenter';
import showAlert from '../utils/alert';

export default function AppLayout({ user: initialUser, onLogout }) {
  const { pathname } = useLocation();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [desktopCollapsed, setDesktopCollapsed] = useState(() => {
    try {
      return localStorage.getItem('dls_desktop_sidebar_collapsed') === 'true';
    } catch {
      return false;
    }
  });
  const [user, setUser] = useState(initialUser);
  const [notificationCount, setNotificationCount] = useState(0);
  const [notificationToast, setNotificationToast] = useState(null);
  const [notificationToastWide, setNotificationToastWide] = useState(false);
  const [activityToast, setActivityToast] = useState(null);
  const [activityToastWide, setActivityToastWide] = useState(false);
  const notificationToastTimer = useRef(null);
  const toastTimer = useRef(null);

  const pageTitle = pathname === '/'
    ? 'Dashboard'
    : pathname === '/courses'
      ? 'Kelola Course'
      : pathname.startsWith('/courses/')
        ? 'Detail Course'
        : pathname === '/materials'
          ? 'Materi & Persetujuan'
        : pathname === '/quizzes'
          ? 'Kelola Quiz'
          : pathname === '/activity'
            ? 'Aktivitas'
            : pathname === '/grades'
              ? 'Kelola Nilai'
              : pathname === '/forum'
                ? 'Forum Diskusi'
                : pathname === '/notifications'
                  ? 'Notifikasi'
                : pathname === '/reviewers'
                  ? 'Kelola Reviewer'
                  : pathname === '/users'
                    ? 'Kelola Pengguna'
                    : pathname === '/enrollments'
                      ? 'Persetujuan Course'
                      : pathname === '/settings/api'
                        ? 'Atur API'
                        : 'Dashboard';

  async function enterStudentMode() {
    try {
      const result = await api('/auth/student-mode', { method: 'POST', body: '{}' });
      window.location.assign(result.redirectUrl);
    } catch (error) { showAlert.error('Mode Belajar Tidak Dapat Dibuka', error.message); }
  }

  function toggleDesktopSidebar() {
    setDesktopCollapsed(prev => {
      const next = !prev;
      try {
        localStorage.setItem('dls_desktop_sidebar_collapsed', String(next));
      } catch {}
      return next;
    });
  }

  useEffect(() => {
    let mounted = true;
    let latestNotificationId = null;
    const refreshCount = async () => {
      try {
        const result = await api('/notifications/unread-count');
        if (mounted) setNotificationCount(Number(result.unreadCount || 0));
      } catch { if (mounted) setNotificationCount(0); }
    };
    const primeLatestNotification = async () => {
      try {
        const result = await api('/notifications?limit=1');
        if (mounted && latestNotificationId === null) latestNotificationId = result.notifications?.[0]?.notification_id ?? null;
      } catch {}
    };
    const showIncomingNotification = async () => {
      try {
        const result = await api('/notifications?limit=1');
        const notification = result.notifications?.[0];
        if (!mounted || !notification) return;
        const notificationId = String(notification.notification_id);
        if (latestNotificationId !== null && String(latestNotificationId) === notificationId) return;
        latestNotificationId = notificationId;
        setNotificationToast(previous => ({ notification, count: previous ? previous.count + 1 : 1 }));
        window.clearTimeout(notificationToastTimer.current);
        notificationToastTimer.current = window.setTimeout(() => setNotificationToast(null), 7000);
      } catch {}
    };
    const syncNotifications = event => {
      refreshCount();
      window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { type: event.type || 'notification_changed' } }));
      if (event.type === 'notification_created') showIncomingNotification();
    };
    refreshCount();
    const events = new EventSource(`${API_BASE}/notifications/events`, { withCredentials: true });
    events.addEventListener('ready', () => { refreshCount(); primeLatestNotification(); });
    events.addEventListener('notification', syncNotifications);
    window.addEventListener('focus', refreshCount);
    return () => {
      mounted = false;
      events.close();
      window.clearTimeout(notificationToastTimer.current);
      window.removeEventListener('focus', refreshCount);
    };
  }, [user?.role, user?.npk]);

  useEffect(() => {
    const events = new EventSource(`${API_BASE}/dashboard/activity/events`, { withCredentials: true });
    const handleActivity = event => {
      let activity;
      try { activity = JSON.parse(event.data); } catch { return; }
      window.dispatchEvent(new CustomEvent('dls_activity_changed', { detail: activity }));
      setActivityToast(previous => ({ activity, count: previous ? previous.count + 1 : 1 }));
      window.clearTimeout(toastTimer.current);
      toastTimer.current = window.setTimeout(() => setActivityToast(null), 6000);
    };
    events.addEventListener('activity', handleActivity);
    return () => {
      events.close();
      window.clearTimeout(toastTimer.current);
    };
  }, [user?.role, user?.npk]);

  return (
    <div className="min-h-screen bg-surface dark:bg-[#0f172a] text-on-surface transition-colors duration-200">
      <Sidebar
        user={user}
        onLogout={onLogout}
        open={mobileOpen}
        onClose={() => setMobileOpen(false)}
        collapsed={desktopCollapsed}
        onToggleCollapse={toggleDesktopSidebar}
        onUserUpdated={updated => setUser(prev => ({ ...prev, ...updated }))}
      />
      <div className={`min-h-screen transition-[padding] duration-200 ease-in-out ${desktopCollapsed ? 'lg:pl-18' : 'lg:pl-64'}`}>
        <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-outline/60 bg-white px-4 dark:bg-[#1e293b] md:px-8 shadow-xs">
          <div className="flex items-center gap-3">
            {/* Mobile menu button */}
            <button
              type="button"
              onClick={() => setMobileOpen(true)}
              className="rounded-lg p-2 text-secondary hover:bg-surface-low cursor-pointer lg:hidden border border-outline/40"
              aria-label="Buka menu navigasi"
              title="Buka menu"
            >
              <span className="material-symbol">menu</span>
            </button>

            {/* Desktop toggle sidebar button */}
            <button
              type="button"
              onClick={toggleDesktopSidebar}
              className="hidden lg:inline-flex items-center justify-center rounded-lg p-2 text-secondary hover:bg-surface-low cursor-pointer transition border border-outline/50 dark:hover:bg-slate-800"
              aria-label={desktopCollapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar'}
              title={desktopCollapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar'}
            >
              <span className="material-symbol text-xl">{desktopCollapsed ? 'menu_open' : 'menu'}</span>
            </button>

            <div className="flex min-w-0 items-center gap-2 text-xs font-semibold tracking-wide text-secondary sm:text-sm">
              <span className="truncate font-bold text-on-surface">{pageTitle}</span>
              <span className="hidden text-outline md:inline" aria-hidden="true">/</span>
              <span className="hidden truncate text-muted md:inline">Portal Pengelola &amp; Pengajar</span>
            </div>
          </div>

          <div className="ml-auto flex items-center gap-3">
            {user?.role === 'teacher' && <button type="button" onClick={enterStudentMode} className="inline-flex min-h-10 items-center gap-2 rounded-lg border border-primary/30 bg-white px-3 text-xs font-semibold text-primary hover:bg-primary/5 sm:text-sm"><span className="material-symbol" aria-hidden="true">school</span><span className="hidden sm:inline">Mode Belajar</span></button>}
            <span className="hidden text-xs font-medium text-muted md:block">
              Selamat datang, <strong className="text-secondary dark:text-slate-100">{user?.name}</strong>
            </span>
            <Link
              to="/notifications"
              aria-label={`Notifikasi${notificationCount ? `, ${notificationCount} belum dibaca` : ''}`}
              title="Notifikasi"
              className="relative rounded-full border border-outline/50 p-2 text-secondary hover:bg-surface-low"
            >
              <span className="material-symbol">notifications</span>
              {notificationCount > 0 && <span aria-hidden="true" className="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full border-2 border-white bg-red-600 px-1 text-[10px] font-bold leading-none text-white dark:border-slate-800">{notificationCount > 99 ? '99+' : notificationCount}</span>}
            </Link>
          </div>
        </header>
        <main className="mx-auto max-w-7xl p-4 md:p-8">
          <Outlet context={user} />
        </main>
      </div>
      {notificationToast && <div className={`fixed right-3 top-20 z-[61] w-[calc(100vw-1.5rem)] sm:right-6 ${notificationToastWide ? 'max-w-xl' : 'max-w-sm'}`} role="status" aria-live="polite">
        <div className="overflow-hidden rounded-xl border border-red-200 bg-white text-slate-900 shadow-xl dark:border-red-900 dark:bg-slate-900 dark:text-slate-100">
          <div className="flex items-start gap-2 border-l-4 border-red-600 p-3 sm:gap-3 sm:p-4">
            <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300"><span className="material-symbol" aria-hidden="true">notifications_active</span></span>
            <div className="min-w-0 flex-1">
              <div className="flex items-start justify-between gap-2"><p className="pt-1 text-sm font-bold">Notifikasi baru{notificationToast.count > 1 ? ` · ${notificationToast.count}` : ''}</p><div className="flex shrink-0 items-center gap-1"><button type="button" onClick={() => setNotificationToastWide(value => !value)} className="grid size-8 place-items-center rounded-lg border border-outline/50 text-muted hover:bg-surface-low" aria-label={notificationToastWide ? 'Perkecil notifikasi' : 'Lebarkan notifikasi'} title={notificationToastWide ? 'Perkecil' : 'Lebarkan'}><span className="material-symbol" aria-hidden="true">{notificationToastWide ? 'close_fullscreen' : 'open_in_full'}</span></button><button type="button" onClick={() => setNotificationToast(null)} className="grid size-8 place-items-center rounded-lg border border-outline/50 text-muted hover:bg-surface-low" aria-label="Tutup notifikasi"><span className="material-symbol" aria-hidden="true">close</span></button></div></div>
              <p className="mt-1 break-words text-sm font-semibold text-secondary dark:text-slate-200">{notificationToast.notification.title}</p>
              <p className="mt-1 line-clamp-2 break-words text-xs leading-5 text-muted">{notificationToast.notification.message}</p>
              <Link to="/notifications" onClick={() => setNotificationToast(null)} className="mt-2 inline-flex min-h-8 items-center rounded-md border border-red-200 px-2.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">Lihat notifikasi</Link>
            </div>
          </div>
        </div>
      </div>}
      {activityToast && <div className={`fixed right-3 ${notificationToast ? 'top-64' : 'top-20'} z-[60] w-[calc(100vw-1.5rem)] sm:right-6 ${activityToastWide ? 'max-w-xl' : 'max-w-sm'}`} role="status" aria-live="polite">
        <div className="overflow-hidden rounded-xl border border-red-200 bg-white text-slate-900 shadow-xl dark:border-red-900 dark:bg-slate-900 dark:text-slate-100">
          <div className="flex items-start gap-2 border-l-4 border-red-600 p-3 sm:gap-3 sm:p-4">
            <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300"><span className="material-symbol" aria-hidden="true">history</span></span>
            <div className="min-w-0 flex-1">
              <div className="flex items-start justify-between gap-2"><p className="pt-1 text-sm font-bold">Aktivitas baru{activityToast.count > 1 ? ` · ${activityToast.count}` : ''}</p><div className="flex shrink-0 items-center gap-1"><button type="button" onClick={() => setActivityToastWide(value => !value)} className="grid size-8 place-items-center rounded-lg border border-outline/50 text-muted hover:bg-surface-low" aria-label={activityToastWide ? 'Perkecil pemberitahuan aktivitas' : 'Lebarkan pemberitahuan aktivitas'} title={activityToastWide ? 'Perkecil' : 'Lebarkan'}><span className="material-symbol" aria-hidden="true">{activityToastWide ? 'close_fullscreen' : 'open_in_full'}</span></button><button type="button" onClick={() => setActivityToast(null)} className="grid size-8 place-items-center rounded-lg border border-outline/50 text-muted hover:bg-surface-low" aria-label="Tutup pemberitahuan aktivitas"><span className="material-symbol" aria-hidden="true">close</span></button></div></div>
              <p className="mt-1 line-clamp-2 break-words text-sm text-secondary dark:text-slate-300">{activityToast.activity.description || 'Aktivitas baru tercatat'}</p>
              <p className="mt-1 text-xs text-muted">{activityToast.activity.actor_name ? `${activityToast.activity.actor_name} · ` : ''}{activityToast.activity.activity_type || 'Aktivitas'}</p>
              <Link to="/activity" onClick={() => setActivityToast(null)} className="mt-2 inline-flex min-h-8 items-center rounded-md border border-red-200 px-2.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950">Lihat aktivitas</Link>
            </div>
          </div>
        </div>
      </div>}
      <JobsCenter role={user?.role} />
    </div>
  );
}
