import { Outlet, useLocation } from 'react-router-dom';
import { useState } from 'react';
import Sidebar from '../components/Sidebar';

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

  const pageTitle = pathname === '/'
    ? 'Dashboard'
    : pathname === '/courses'
      ? 'Kelola Course'
      : pathname.startsWith('/courses/')
        ? 'Detail Course'
        : pathname === '/materials'
          ? 'Materi & Persetujuan'
          : pathname === '/activity'
            ? 'Aktivitas'
            : pathname === '/grades'
              ? 'Kelola Nilai'
              : pathname === '/forum'
                ? 'Forum Diskusi'
                : pathname === '/reviewers'
                  ? 'Kelola Reviewer'
                  : pathname === '/users'
                    ? 'Manajemen Pengguna'
                    : 'Dashboard';

  function toggleDesktopSidebar() {
    setDesktopCollapsed(prev => {
      const next = !prev;
      try {
        localStorage.setItem('dls_desktop_sidebar_collapsed', String(next));
      } catch {}
      return next;
    });
  }

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
            <span className="hidden text-xs font-medium text-muted md:block">
              Selamat datang, <strong className="text-secondary dark:text-slate-100">{user?.name}</strong>
            </span>
            <button
              type="button"
              aria-label="Notifikasi"
              className="relative rounded-full p-2 text-secondary hover:bg-surface-low cursor-pointer"
            >
              <span className="material-symbol">notifications</span>
              <i className="absolute right-2 top-2 size-2 rounded-full bg-primary" />
            </button>
          </div>
        </header>
        <main className="mx-auto max-w-7xl p-4 md:p-8">
          <Outlet context={user} />
        </main>
      </div>
    </div>
  );
}
