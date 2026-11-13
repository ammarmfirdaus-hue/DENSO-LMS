import { Link, Outlet, useLocation } from 'react-router-dom';
import { Suspense, useEffect, useRef, useState } from 'react';
import { Bell, Calendar, GraduationCap, Menu, PanelLeftOpen, History, BellRing, Maximize2, Minimize2, X, HelpCircle } from 'lucide-react';
import { API_BASE, api } from '../api/client';
import Sidebar from '../components/Sidebar';
import JobsCenter from '../components/JobsCenter';
import FloatingGuideBubble from '../components/FloatingGuideBubble';
import showAlert from '../utils/alert';
import ModalNotif from '../components/ModalNotif';
import useSubmenuAutoScroll from '../utils/useSubmenuAutoScroll';
import { startUserGuide } from '../utils/userGuide';
import { hasPermission } from '../utils/access-control';

const ROUTE_TITLES = [
  ['/', 'Dashboard'],
  ['/courses', 'Kelola Course'],
  ['/materials', 'Materi & Persetujuan'],
  ['/quizzes', 'Kelola Quiz'],
  ['/activity', 'Aktivitas'],
  ['/grades', 'Kelola Nilai'],
  ['/forum', 'Forum Diskusi'],
  ['/notifications', 'Notifikasi'],
  ['/reviewers', 'Kelola Reviewer'],
  ['/users', 'Kelola Pengguna'],
  ['/enrollments', 'Persetujuan Course'],
  ['/roles', 'Role & Hak Akses'],
  ['/settings/api', 'Atur API']
];

export default function AppLayout({ user: initialUser, onLogout }) {
  const { pathname } = useLocation();
  useSubmenuAutoScroll();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [desktopCollapsed, setDesktopCollapsed] = useState(() => {
    try { return localStorage.getItem('dls_desktop_sidebar_collapsed') === 'true'; } catch { return false; }
  });
  const [user, setUser] = useState(initialUser);

  useEffect(() => {
    setUser(initialUser);
  }, [initialUser]);
  const [notificationCount, setNotificationCount] = useState(0);
  const [notificationToast, setNotificationToast] = useState(null);
  const [modalNotification, setModalNotification] = useState(null);
  const [notificationToastWide, setNotificationToastWide] = useState(false);
  const [activityToast, setActivityToast] = useState(null);
  const [activityToastWide, setActivityToastWide] = useState(false);
  const notificationToastTimer = useRef(null);
  const toastTimer = useRef(null);

  const pageTitle = pathname.startsWith('/courses/')
    ? 'Detail Course'
    : (ROUTE_TITLES.find(([prefix]) => prefix === pathname)?.[1] || 'Dashboard');

  useEffect(() => {
    document.title = `${pageTitle} | DENSO LMS`;
  }, [pageTitle]);

  async function enterStudentMode() {
    try {
      const result = await api('/auth/student-mode', { method: 'POST', body: '{}' });
      window.location.assign(result.redirectUrl);
    } catch (error) {
      showAlert.error('Mode Belajar Tidak Dapat Dibuka', error.message);
    }
  }

  function toggleDesktopSidebar() {
    setDesktopCollapsed(prev => {
      const next = !prev;
      try { localStorage.setItem('dls_desktop_sidebar_collapsed', String(next)); } catch {}
      return next;
    });
  }

  const handleCloseModalNotification = async () => {
    if (modalNotification) {
      const notifId = modalNotification.notification_id;
      sessionStorage.setItem(`dls_dismissed_popup_${notifId}`, '1');
      try {
        await api(`/notifications/${notifId}/read`, { method: 'POST', body: '{}' });
        setNotificationCount(count => Math.max(0, count - 1));
        window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { type: 'notification_read' } }));
      } catch {}
    }
    setModalNotification(null);
  };

  const canViewNotifications = hasPermission(user, 'notifications.view');
  const canViewActivity = hasPermission(user, 'activity.view');

  useEffect(() => {
    if (!canViewNotifications) {
      setNotificationCount(0);
      return;
    }
    let mounted = true;
    let latestNotificationId = null;
    const refreshCount = async () => {
      try {
        const result = await api('/notifications/unread-count');
        if (mounted) setNotificationCount(Number(result.unreadCount || 0));
      } catch {
        if (mounted) setNotificationCount(0);
      }
    };
    const primeLatestNotification = async () => {
      try {
        const result = await api('/notifications?limit=1');
        const notification = result.notifications?.[0];
        if (!mounted || !notification) return;
        if (latestNotificationId === null) latestNotificationId = notification.notification_id ?? null;
        if (!notification.is_read && notification.show_popup) {
          const dismissedKey = `dls_dismissed_popup_${notification.notification_id}`;
          if (!sessionStorage.getItem(dismissedKey)) setModalNotification(notification);
        }
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
        if (notification.show_popup) {
          setModalNotification(notification);
        } else {
          setNotificationToast(previous => ({ notification, count: previous ? previous.count + 1 : 1 }));
          window.clearTimeout(notificationToastTimer.current);
          notificationToastTimer.current = window.setTimeout(() => setNotificationToast(null), 7000);
        }
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
  }, [user?.role, user?.npk, canViewNotifications]);

  useEffect(() => {
    if (!canViewActivity) return;
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
  }, [user?.role, user?.npk, canViewActivity]);

  return (
    <div className="min-h-screen overflow-x-clip bg-surface text-on-surface transition-colors duration-200">
      <Sidebar
        user={user}
        onLogout={onLogout}
        open={mobileOpen}
        onClose={() => setMobileOpen(false)}
        collapsed={desktopCollapsed}
        onToggleCollapse={toggleDesktopSidebar}
        onUserUpdated={updated => setUser(prev => ({ ...prev, ...updated }))}
      />

      <div className={`min-h-screen transition-[padding] duration-200 ease-in-out ${desktopCollapsed ? 'lg:pl-16' : 'lg:pl-64'}`}>
        {/* Sticky 64px Dark Frosted Application Header (design.md shell) */}
        <header className="dls-shell-chrome sticky top-0 z-20 flex h-16 items-center justify-between border-b border-white/10 bg-[#10141a]/92 px-4 text-white backdrop-blur-md">
          <div className="flex min-w-0 items-center gap-3">
            <button
              type="button"
              data-dls-action-ignore="true"
              onClick={() => setMobileOpen(true)}
              className="grid size-10 shrink-0 cursor-pointer place-items-center rounded-lg text-white/75 transition hover:bg-white/10 hover:text-white lg:hidden"
              aria-label="Buka menu navigasi"
              title="Buka menu"
            >
              <Menu className="h-5 w-5" />
            </button>

            <button
              type="button"
              data-dls-action-ignore="true"
              onClick={toggleDesktopSidebar}
              className="hidden h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-white/75 transition hover:bg-white/10 hover:text-white lg:inline-flex"
              aria-label={desktopCollapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar'}
              title={desktopCollapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar'}
            >
              {desktopCollapsed ? <PanelLeftOpen className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>

            <div className="flex min-w-0 items-center gap-2 text-sm">
              <span className="truncate font-semibold text-white">{pageTitle}</span>
              <span className="hidden text-white/30 md:inline" aria-hidden="true">/</span>
              <span className="hidden truncate text-xs font-medium text-white/60 md:inline">
                Portal Pengelola &amp; Pengajar
              </span>
            </div>
          </div>

          <div className="ml-auto flex shrink-0 items-center gap-2 sm:gap-2.5">
            {user?.role === 'teacher' && (
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={enterStudentMode}
                className="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 text-xs font-semibold text-white transition hover:border-primary hover:bg-primary"
              >
                <GraduationCap className="h-4 w-4" aria-hidden="true" />
                <span className="hidden sm:inline">Mode Belajar</span>
              </button>
            )}

            <div className="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-white/15 bg-white/[0.07] px-2.5 py-1 text-[11px] font-medium text-white/85 sm:px-3 sm:py-1.5 sm:text-xs">
              <Calendar className="hidden h-3.5 w-3.5 text-white/65 sm:block" aria-hidden="true" />
              <span className="whitespace-nowrap">
                {new Date().toLocaleDateString('id-ID', {
                  weekday: 'long',
                  day: 'numeric',
                  month: 'short',
                  year: 'numeric'
                })}
              </span>
            </div>

            <span className="hidden text-xs text-white/70 md:block">
              Halo, <strong className="font-semibold text-white">{user?.name}</strong>
            </span>

            {canViewNotifications && (
              <Link
                to="/notifications"
                aria-label={`Notifikasi${notificationCount ? `, ${notificationCount} belum dibaca` : ''}`}
                title="Notifikasi"
                className="relative grid size-10 place-items-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-white"
              >
                <Bell className="h-5 w-5" />
                {notificationCount > 0 && (
                  <span
                    aria-hidden="true"
                    className="absolute right-1 top-1 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold leading-none text-white ring-2 ring-[#10141a]"
                  >
                    {notificationCount > 99 ? '99+' : notificationCount}
                  </span>
                )}
              </Link>
            )}
          </div>
        </header>

        {/* Workspace Container: 1400px max-width, 32px desktop padding */}
        <main className="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
          <Suspense fallback={<PageSkeleton />}>
            <Outlet context={user} />
          </Suspense>
        </main>
      </div>

      {/* Live Toast Notification */}
      {notificationToast && (
        <div
          className={`fixed right-3 top-20 z-[61] w-[calc(100vw-1.5rem)] sm:right-6 ${
            notificationToastWide ? 'max-w-xl' : 'max-w-sm'
          }`}
          role="status"
          aria-live="polite"
        >
          <div className="dls-card-surface overflow-hidden rounded-xl border-l-4 border-l-primary shadow-[0_12px_28px_rgba(0,0,0,0.16)]">
            <div className="flex items-start gap-3 p-3.5 sm:p-4">
              <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary">
                <BellRing className="h-4 w-4" aria-hidden="true" />
              </span>
              <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-2">
                  <p className="pt-0.5 text-sm font-bold text-on-surface">
                    Notifikasi baru{notificationToast.count > 1 ? ` · ${notificationToast.count}` : ''}
                  </p>
                  <div className="flex shrink-0 items-center gap-1">
                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      onClick={() => setNotificationToastWide(v => !v)}
                      className="grid size-7 cursor-pointer place-items-center rounded-md border border-outline text-muted hover:bg-surface-low"
                      aria-label={notificationToastWide ? 'Perkecil notifikasi' : 'Lebarkan notifikasi'}
                    >
                      {notificationToastWide ? <Minimize2 className="h-3.5 w-3.5" /> : <Maximize2 className="h-3.5 w-3.5" />}
                    </button>
                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      onClick={() => setNotificationToast(null)}
                      className="grid size-7 cursor-pointer place-items-center rounded-md border border-outline text-muted hover:bg-surface-low"
                      aria-label="Tutup notifikasi"
                    >
                      <X className="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
                <p className="mt-1 break-words text-sm font-semibold text-on-surface">
                  {notificationToast.notification.title}
                </p>
                <p className="mt-1 line-clamp-2 break-words text-xs leading-5 text-muted">
                  {notificationToast.notification.message}
                </p>
                <Link
                  to="/notifications"
                  onClick={() => setNotificationToast(null)}
                  className="mt-2 inline-flex h-8 items-center rounded-md border border-primary/30 px-2.5 text-xs font-semibold text-primary hover:bg-primary/5"
                >
                  Lihat notifikasi
                </Link>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Live Activity Toast */}
      {activityToast && (
        <div
          className={`fixed right-3 ${notificationToast ? 'top-64' : 'top-20'} z-[60] w-[calc(100vw-1.5rem)] sm:right-6 ${
            activityToastWide ? 'max-w-xl' : 'max-w-sm'
          }`}
          role="status"
          aria-live="polite"
        >
          <div className="dls-card-surface overflow-hidden rounded-xl border-l-4 border-l-secondary shadow-[0_12px_28px_rgba(0,0,0,0.16)]">
            <div className="flex items-start gap-3 p-3.5 sm:p-4">
              <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-secondary/10 text-secondary">
                <History className="h-4 w-4" aria-hidden="true" />
              </span>
              <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-2">
                  <p className="pt-0.5 text-sm font-bold text-on-surface">
                    Aktivitas baru{activityToast.count > 1 ? ` · ${activityToast.count}` : ''}
                  </p>
                  <div className="flex shrink-0 items-center gap-1">
                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      onClick={() => setActivityToastWide(v => !v)}
                      className="grid size-7 cursor-pointer place-items-center rounded-md border border-outline text-muted hover:bg-surface-low"
                      aria-label={activityToastWide ? 'Perkecil pemberitahuan aktivitas' : 'Lebarkan pemberitahuan aktivitas'}
                    >
                      {activityToastWide ? <Minimize2 className="h-3.5 w-3.5" /> : <Maximize2 className="h-3.5 w-3.5" />}
                    </button>
                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      onClick={() => setActivityToast(null)}
                      className="grid size-7 cursor-pointer place-items-center rounded-md border border-outline text-muted hover:bg-surface-low"
                      aria-label="Tutup pemberitahuan aktivitas"
                    >
                      <X className="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
                <p className="mt-1 line-clamp-2 break-words text-sm text-on-surface">
                  {activityToast.activity.description || 'Aktivitas baru tercatat'}
                </p>
                <p className="mt-1 text-xs text-muted">
                  {activityToast.activity.actor_name ? `${activityToast.activity.actor_name} · ` : ''}
                  {activityToast.activity.activity_type || 'Aktivitas'}
                </p>
                <Link
                  to="/activity"
                  onClick={() => setActivityToast(null)}
                  className="mt-2 inline-flex h-8 items-center rounded-md border border-outline px-2.5 text-xs font-semibold text-on-surface hover:bg-surface-low"
                >
                  Lihat aktivitas
                </Link>
              </div>
            </div>
          </div>
        </div>
      )}

      <ModalNotif
        isOpen={Boolean(modalNotification)}
        notification={modalNotification}
        onClose={handleCloseModalNotification}
      />
      {canViewActivity && <JobsCenter role={user?.role} />}
      <FloatingGuideBubble />
    </div>
  );
}

function PageSkeleton() {
  return (
    <div className="space-y-6 animate-pulse" aria-busy="true" aria-label="Memuat konten halaman...">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="space-y-2">
          <div className="h-3.5 w-28 rounded bg-surface-container" />
          <div className="h-7 w-52 rounded-md bg-surface-container" />
        </div>
        <div className="h-10 w-36 rounded-lg bg-surface-container" />
      </div>
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div className="h-36 rounded-xl bg-surface-container" />
        <div className="h-36 rounded-xl bg-surface-container" />
        <div className="h-36 rounded-xl bg-surface-container" />
        <div className="h-36 rounded-xl bg-surface-container" />
      </div>
      <div className="h-80 rounded-xl bg-surface-container" />
    </div>
  );
}
