import { useEffect, useRef, useState } from 'react';
import { NavLink } from 'react-router-dom';
import ProfileModal from './ProfileModal';
import showAlert from '../utils/alert';
import { getInitialTheme, toggleTheme } from '../utils/theme';

const MENU_GROUPS = [
  {
    title: 'Pembelajaran',
    links: [
      { to: '/', icon: 'grid_view', label: 'Dashboard' },
      { to: '/courses', icon: 'menu_book', label: 'Kelola Course' },
      { to: '/materials', icon: 'fact_check', label: 'Materi & Persetujuan' },
      { to: '/grades', icon: 'grade', label: 'Kelola Nilai' },
    ]
  },
  {
    title: 'Komunikasi & Forum',
    links: [
      { to: '/activity', icon: 'monitoring', label: 'Aktivitas' },
      { to: '/forum', icon: 'forum', label: 'Forum Diskusi' },
      { to: '/notifications', icon: 'notifications', label: 'Notifikasi' },
    ]
  },
  {
    title: 'Administrasi',
    adminOnly: true,
    links: [
      { to: '/reviewers', icon: 'verified_user', label: 'Kelola Reviewer' },
      { to: '/users', icon: 'groups', label: 'Manajemen Pengguna' },
    ]
  }
];

export default function Sidebar({ user, onLogout, open, onClose, collapsed, onToggleCollapse, onUserUpdated }) {
  const isAdmin = user?.role === 'admin';
  const [showDropup, setShowDropup] = useState(false);
  const [showProfileModal, setShowProfileModal] = useState(false);
  const [isDark, setIsDark] = useState(() => getInitialTheme() === 'dark');
  const [isHovered, setIsHovered] = useState(false);
  const dropupRef = useRef(null);

  // Desktop expanded state is true if NOT collapsed OR when hovered over collapsed sidebar
  const isExpanded = !collapsed || isHovered;

  // Close dropup when clicking outside
  useEffect(() => {
    function handleClickOutside(e) {
      if (dropupRef.current && !dropupRef.current.contains(e.target)) {
        setShowDropup(false);
      }
    }
    if (showDropup) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [showDropup]);

  // Listen to theme changes from other components
  useEffect(() => {
    function handleThemeChange(e) {
      setIsDark(e.detail.theme === 'dark');
    }
    window.addEventListener('dls_theme_change', handleThemeChange);
    return () => window.removeEventListener('dls_theme_change', handleThemeChange);
  }, []);

  function handleThemeSwitch() {
    const next = toggleTheme();
    setIsDark(next === 'dark');
  }

  async function handleConfirmLogout() {
    setShowDropup(false);
    const confirmed = await showAlert.confirm({
      title: 'Keluar dari Sistem?',
      text: 'Apakah Anda yakin ingin mengakhiri sesi masuk pada DENSO Learning Management System?',
      confirmText: 'Ya, Keluar',
      cancelText: 'Tetap Masuk',
      danger: true,
      icon: 'question'
    });
    if (confirmed) {
      onLogout?.();
    }
  }

  return (
    <>
      {open && (
        <button
          type="button"
          aria-label="Tutup menu"
          className="fixed inset-0 z-30 bg-black/50 lg:hidden cursor-pointer"
          onClick={onClose}
        />
      )}

      <aside
        onMouseEnter={() => {
          if (collapsed) setIsHovered(true);
        }}
        onMouseLeave={() => {
          setIsHovered(false);
          setShowDropup(false);
        }}
        className={`fixed inset-y-0 left-0 z-40 flex flex-col border-r border-outline/60 bg-white transition-all duration-200 ease-in-out dark:bg-[#1e293b] ${
          open ? 'translate-x-0' : '-translate-x-full'
        } lg:translate-x-0 ${
          isExpanded ? 'w-64' : 'w-18'
        } ${
          collapsed && isHovered ? 'shadow-2xl ring-1 ring-black/10 z-50' : 'shadow-sm'
        }`}
      >
        {/* Logo & Brand Header */}
        <div className={`flex h-16 items-center border-b border-outline/40 px-3.5 ${
          isExpanded ? 'justify-between' : 'justify-center'
        }`}>
          <div className="flex items-center gap-2.5 min-w-0">
            <img
              src="/assets/img/icon/denso-vector-logo.png"
              alt="Logo DENSO"
              className="size-9 shrink-0 rounded-lg bg-white p-0.5 object-contain border border-outline/40 shadow-xs"
            />
            {isExpanded && (
              <div className="min-w-0 transition-opacity duration-150">
                <p className="font-bold text-sm tracking-wide text-on-surface truncate">
                  DENSO <span className="text-primary">LMS</span>
                </p>
                <p className="text-[10px] text-muted truncate">Pengelola &amp; Trainer</p>
              </div>
            )}
          </div>

          {/* Desktop collapse / pin button */}
          {isExpanded && (
            <button
              type="button"
              onClick={onToggleCollapse}
              className="hidden lg:flex items-center justify-center rounded-lg p-1.5 text-muted hover:text-secondary hover:bg-surface-low dark:hover:bg-slate-800 transition cursor-pointer"
              title={collapsed ? "Sematkan / Pin Sidebar Terbuka" : "Kecilkan Sidebar ke Ikon"}
              aria-label="Toggle sidebar"
            >
              <span className="material-symbol text-lg">
                {collapsed ? 'push_pin' : 'chevron_left'}
              </span>
            </button>
          )}

          {/* Mobile close button */}
          <button
            type="button"
            onClick={onClose}
            className="lg:hidden flex items-center justify-center rounded-lg p-1.5 text-muted hover:text-secondary hover:bg-surface-low transition cursor-pointer"
            title="Tutup menu"
            aria-label="Tutup menu"
          >
            <span className="material-symbol text-lg">close</span>
          </button>
        </div>

        {/* Grouped Navigation Links */}
        <nav className="flex-1 overflow-y-auto px-2.5 py-4 space-y-4">
          {MENU_GROUPS.map(group => {
            if (group.adminOnly && !isAdmin) return null;
            return (
              <div key={group.title} className="space-y-1">
                {isExpanded ? (
                  <p className="px-3 text-[10px] font-bold uppercase tracking-wider text-muted/80 truncate">
                    {group.title}
                  </p>
                ) : (
                  <div className="mx-2 my-2 border-t border-outline/40" title={group.title} />
                )}
                {group.links.map(link => (
                  <NavLink
                    key={link.to}
                    to={link.to}
                    end={link.to === '/'}
                    onClick={onClose}
                    title={!isExpanded ? link.label : undefined}
                    className={({ isActive }) =>
                      `flex items-center rounded-xl text-xs font-semibold transition ${
                        isExpanded ? 'gap-3 px-3.5 py-2.5' : 'justify-center px-0 py-2.5'
                      } ${
                        isActive
                          ? 'bg-primary/10 text-primary ring-1 ring-primary/20 dark:bg-primary/20'
                          : 'text-secondary hover:bg-surface-low dark:hover:bg-[#0f172a]'
                      }`
                    }
                  >
                    <span className="material-symbol text-lg shrink-0" aria-hidden="true">
                      {link.icon}
                    </span>
                    {isExpanded && <span className="truncate">{link.label}</span>}
                  </NavLink>
                ))}
              </div>
            );
          })}
        </nav>

        {/* ── BOTTOM PROFILE SECTION WITH DROPUP ── */}
        <div ref={dropupRef} className="relative border-t border-outline/40 p-2.5 bg-surface-low/40 dark:bg-[#0f172a]">
          
          {/* Dropup Menu (muncul ke atas saat profil diklik) */}
          {showDropup && (
            <div className={`absolute bottom-full mb-2 rounded-2xl border border-outline/60 bg-white p-3 shadow-2xl ring-1 ring-black/5 animate-in fade-in slide-in-from-bottom-2 duration-150 dark:bg-[#1e293b] ${
              isExpanded ? 'left-2.5 right-2.5' : 'left-2 w-64'
            }`}>
              
              {/* Profile Card Header */}
              <div className="flex items-center gap-3 border-b border-outline/30 pb-3">
                <div className="grid size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-base font-bold text-primary">
                  {user?.name?.charAt(0).toUpperCase() || 'U'}
                </div>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-xs font-bold text-on-surface">{user?.name}</p>
                  <p className="text-[11px] text-muted truncate">NPK: {user?.npk}</p>
                  <span className="inline-block mt-0.5 rounded-full bg-primary/10 px-2 py-0.2 text-[10px] font-bold uppercase tracking-wider text-primary">
                    {user?.role}
                  </span>
                </div>
              </div>

              {/* Theme Toggle Button */}
              <div className="mt-2.5 flex items-center justify-between rounded-xl p-2 hover:bg-surface-low dark:hover:bg-[#0f172a] transition">
                <div className="flex items-center gap-2">
                  <span className={`material-symbol text-base ${isDark ? 'text-amber-400' : 'text-zinc-600'}`}>
                    {isDark ? 'dark_mode' : 'light_mode'}
                  </span>
                  <span className="text-xs font-medium text-secondary">
                    {isDark ? 'Mode Gelap' : 'Mode Terang'}
                  </span>
                </div>
                <button
                  type="button"
                  onClick={handleThemeSwitch}
                  className={`relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                    isDark ? 'bg-primary' : 'bg-zinc-300'
                  }`}
                  aria-label="Ganti Tema"
                >
                  <span
                    className={`pointer-events-none inline-block size-4 rounded-full bg-white shadow-md transform ring-0 transition duration-200 ease-in-out ${
                      isDark ? 'translate-x-4' : 'translate-x-0'
                    }`}
                  />
                </button>
              </div>

              {/* Edit Profil Button */}
              <button
                type="button"
                onClick={() => {
                  setShowDropup(false);
                  setShowProfileModal(true);
                }}
                className="mt-1 flex w-full items-center gap-2 rounded-xl p-2 text-xs font-medium text-secondary hover:bg-surface-low dark:hover:bg-[#0f172a] transition cursor-pointer"
              >
                <span className="material-symbol text-base text-primary" aria-hidden="true">manage_accounts</span>
                Edit Profil &amp; Password
              </button>

              {/* Logout Button */}
              <button
                type="button"
                onClick={handleConfirmLogout}
                className="mt-1 flex w-full items-center gap-2 rounded-xl border-t border-outline/30 p-2 pt-2.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition cursor-pointer"
              >
                <span className="material-symbol text-base" aria-hidden="true">logout</span>
                Keluar dari Akun
              </button>
            </div>
          )}

          {/* Trigger Bar Profil di Bawah Sidebar */}
          <button
            type="button"
            onClick={() => setShowDropup(prev => !prev)}
            aria-expanded={showDropup}
            aria-label="Menu Pengguna & Pengaturan"
            title={!isExpanded ? `${user?.name} (${user?.role})` : undefined}
            className={`flex w-full items-center rounded-xl transition hover:bg-white dark:hover:bg-[#1e293b] cursor-pointer ${
              isExpanded ? 'gap-3 p-2 text-left' : 'justify-center p-2'
            }`}
          >
            <div className="grid size-9 shrink-0 place-items-center rounded-xl bg-secondary-container/50 font-bold text-secondary dark:text-white">
              {user?.name?.slice(0, 1).toUpperCase() || 'U'}
            </div>
            {isExpanded && (
              <>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-xs font-bold text-on-surface">{user?.name}</p>
                  <p className="text-[11px] capitalize text-muted truncate">{user?.role} · NPK {user?.npk}</p>
                </div>
                <span
                  className={`material-symbol text-muted transition-transform duration-200 ${
                    showDropup ? 'rotate-180 text-primary' : ''
                  }`}
                  aria-hidden="true"
                >
                  expand_less
                </span>
              </>
            )}
          </button>
        </div>
      </aside>

      {/* ── MODAL EDIT PROFIL ── */}
      {showProfileModal && (
        <ProfileModal
          user={user}
          onClose={() => setShowProfileModal(false)}
          onUserUpdated={onUserUpdated}
        />
      )}
    </>
  );
}
