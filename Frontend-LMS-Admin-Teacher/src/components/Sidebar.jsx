import { useEffect, useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import {
  LayoutGrid,
  BookOpen,
  FileCheck2,
  HelpCircle,
  Award,
  ShieldCheck,
  KeyRound,
  Users,
  UserCheck,
  Sliders,
  Activity,
  MessagesSquare,
  Bell,
  ChevronDown,
  ChevronLeft,
  Pin,
  Search,
  X,
  LogOut,
  UserCog,
  Sun,
  Moon,
  ChevronUp
} from 'lucide-react';
import ProfileModal from './ProfileModal';
import showAlert from '../utils/alert';
import { getInitialTheme, toggleTheme } from '../utils/theme';
import { scrollToTargetById } from '../utils/useSubmenuAutoScroll';
import { hasPermission, resolveRoleLabel } from '../utils/access-control';

const MENU_GROUPS = [
  {
    title: '',
    standalone: true,
    links: [
      { id: 'dashboard', to: '/', Icon: LayoutGrid, label: 'Dashboard', permission: 'dashboard.view' }
    ]
  },
  {
    title: 'Pembelajaran',
    links: [
      {
        id: 'courses',
        baseTo: '/courses',
        Icon: BookOpen,
        label: 'Kelola Course',
        permission: 'courses.view',
        children: [
          { to: '/courses#courses-list', label: 'Semua Course' },
          { to: '/courses?category=Wajib#courses-list', label: 'Course Wajib' },
          { to: '/courses?category=Opsional#courses-list', label: 'Course Opsional' },
          { to: '/courses#create-course', label: '+ Buat Course Baru', permission: 'courses.create' }
        ]
      },
      {
        id: 'materials',
        baseTo: '/materials',
        Icon: FileCheck2,
        label: 'Kelola Materi',
        permission: 'materials.view',
        children: [
          { to: '/materials#materials-submissions', label: 'Semua Pengajuan' },
          { to: '/materials?status=pending_admin#materials-submissions', label: 'Menunggu Admin' },
          { to: '/materials?status=pending_review#materials-submissions', label: 'Sedang Direview' },
          { to: '/materials?view=published#published-materials-section', label: 'Materi Final Terbit' },
          { to: '/materials?action=submit#submit-material', label: '+ Ajukan Materi Baru', permission: 'materials.submit' }
        ]
      },
      {
        id: 'quizzes',
        baseTo: '/quizzes',
        Icon: HelpCircle,
        label: 'Kelola Quiz',
        permission: 'quizzes.view',
        children: [
          { to: '/quizzes#quizzes-list', label: 'Semua Quiz' },
          { to: '/quizzes?status=published#quizzes-list', label: 'Quiz Terbit' },
          { to: '/quizzes?status=draft#quizzes-list', label: 'Draft / Belum Dibuat' }
        ]
      },
      {
        id: 'grades-teacher',
        baseTo: '/grades',
        Icon: Award,
        label: 'Kelola Nilai',
        teacherOnly: true,
        permission: 'grades.view',
        children: [
          { to: '/grades#grades-list', label: 'Rekap Semua Nilai' },
          { to: '/grades?status=completed#grades-list', label: 'Course Selesai' },
          { to: '/grades?status=in_progress#grades-list', label: 'Sedang Berjalan' }
        ]
      }
    ]
  },
  {
    title: 'Manajemen',
    links: [
      {
        id: 'grades-admin',
        baseTo: '/grades',
        Icon: Award,
        label: 'Kelola Nilai',
        nonTeacherOnly: true,
        permission: 'grades.view',
        children: [
          { to: '/grades#grades-list', label: 'Rekap Semua Nilai' },
          { to: '/grades?status=completed#grades-list', label: 'Course Selesai' },
          { to: '/grades?status=in_progress#grades-list', label: 'Sedang Berjalan' }
        ]
      },
      {
        id: 'reviewers',
        baseTo: '/reviewers',
        Icon: ShieldCheck,
        label: 'Kelola Reviewer',
        permission: ['reviewers.view', 'reviewers.manage'],
        children: [
          { to: '/reviewers#reviewers-list', label: 'Semua Reviewer' },
          { to: '/reviewers?status=active#reviewers-list', label: 'Reviewer Aktif' },
          { to: '/reviewers?status=inactive#reviewers-list', label: 'Reviewer Nonaktif' },
          { to: '/reviewers?action=create#create-reviewer', label: '+ Tambah Reviewer Baru', permission: 'reviewers.manage' }
        ]
      },
      {
        id: 'users',
        baseTo: '/users',
        Icon: Users,
        label: 'Kelola Pengguna',
        permission: ['users.view', 'users.manage'],
        children: [
          { to: '/users#users-list', label: 'Semua Pengguna' },
          { to: '/users?role=student#users-list', label: 'Daftar Student' },
          { to: '/users?role=teacher#users-list', label: 'Daftar Teacher' },
          { to: '/users?role=reviewer#users-list', label: 'Daftar Reviewer' },
          { to: '/users?role=admin#users-list', label: 'Daftar Admin' },
          { to: '/users#add-user', label: '+ Tambah Pengguna', permission: 'users.manage' }
        ]
      },
      {
        id: 'enrollments',
        baseTo: '/enrollments',
        Icon: UserCheck,
        label: 'Persetujuan Course',
        permission: ['enrollments.view', 'enrollments.decide', 'enrollments.recommend'],
        children: [
          { to: '/enrollments#enrollments-list', label: 'Semua Permintaan' },
          { to: '/enrollments?status=pending#enrollments-list', label: 'Menunggu Keputusan' },
          { to: '/enrollments?status=approved#enrollments-list', label: 'Sudah Disetujui' },
          { to: '/enrollments?status=rejected#enrollments-list', label: 'Ditolak' },
          { to: '/enrollments#recommend-student', label: '+ Rekomendasikan Student', permission: 'enrollments.recommend' }
        ]
      },
      { id: 'roles', to: '/roles', Icon: KeyRound, label: 'Role & Hak Akses', permission: 'roles.manage' },
      { id: 'settings-api', to: '/settings/api', Icon: Sliders, label: 'Atur API', permission: 'settings.api' }
    ]
  },
  {
    title: 'Komunikasi & Forum',
    links: [
      {
        id: 'activity',
        baseTo: '/activity',
        Icon: Activity,
        label: 'Aktivitas',
        permission: 'activity.view',
        children: [
          { to: '/activity#activity-list', label: 'Semua Log Aktivitas' },
          { to: '/activity?tab=jobs#activity-list', label: 'Log Antrean & Job' },
          { to: '/activity?tab=failed#activity-list', label: 'Kendala / Gagal' }
        ]
      },
      {
        id: 'forum',
        baseTo: '/forum',
        Icon: MessagesSquare,
        label: 'Forum Diskusi',
        permission: 'forum.view',
        children: [
          { to: '/forum#forum-threads', label: 'Semua Diskusi' },
          { to: '/forum#new-thread', label: '+ Mulai Diskusi Baru', permission: 'forum.participate' }
        ]
      },
      {
        id: 'notifications',
        baseTo: '/notifications',
        Icon: Bell,
        label: 'Notifikasi',
        permission: 'notifications.view',
        children: [
          { to: '/notifications#notifications-list', label: 'Semua Notifikasi' },
          { to: '/notifications?filter=unread#notifications-list', label: 'Belum Dibaca' },
          { to: '/notifications?filter=important#notifications-list', label: 'Notifikasi Penting' },
          { to: '/notifications?filter=announcement#notifications-list', label: 'Pengumuman' },
          { to: '/notifications#send-notification', label: '+ Kirim Notifikasi', permission: 'notifications.send' }
        ]
      }
    ]
  }
];

function isSubmenuMatch(targetTo, location, allSiblings = []) {
  const [pathAndSearch, targetHash = ''] = targetTo.split('#');
  const [targetPath, targetSearch = ''] = pathAndSearch.split('?');

  const pathMatches =
    targetPath === '/'
      ? location.pathname === '/'
      : location.pathname === targetPath || location.pathname.startsWith(`${targetPath}/`);
  if (!pathMatches) return false;

  const currentParams = new URLSearchParams(location.search);
  const currentHash = (location.hash || '').replace(/^#/, '');

  // 1. Target yang memiliki query param spesifik
  if (targetSearch) {
    const targetParams = new URLSearchParams(targetSearch);
    for (const [key, value] of targetParams.entries()) {
      if (currentParams.get(key) !== value) return false;
    }
    if (targetHash && currentHash && targetHash !== currentHash) {
      return false;
    }
    return true;
  }

  // 2. Jika URL saat ini memiliki search params, target tanpa query jangan ikut aktif jika ada sibling yang cocok
  if (location.search && !targetSearch) {
    const hasMatchingSiblingWithQuery = allSiblings.some(sibling => {
      const [sibPathAndSearch] = sibling.to.split('#');
      const [, sibSearch = ''] = sibPathAndSearch.split('?');
      if (!sibSearch) return false;
      const sibParams = new URLSearchParams(sibSearch);
      let match = true;
      for (const [k, v] of sibParams.entries()) {
        if (currentParams.get(k) !== v) { match = false; break; }
      }
      return match;
    });
    if (hasMatchingSiblingWithQuery) return false;
  }

  // 3. Khusus aksi khusus (action item ber-hash target khusus seperti #create-course, #add-user, dll)
  const isActionItem = [
    'create-course',
    'add-user',
    'submit-material',
    'new-thread',
    'send-notification',
    'create-reviewer',
    'recommend-student'
  ].includes(targetHash);

  if (isActionItem) {
    return currentHash === targetHash;
  }

  // 4. Jika URL saat ini sedang membuka hash aksi, maka default item (misal 'Semua Course') tidak aktif
  if (currentHash && allSiblings.some(s => s.to.endsWith(`#${currentHash}`))) {
    return targetHash === currentHash;
  }

  // 5. Default item list aktif saat tidak ada query dan tidak ada hash aksi
  return !location.search;
}

function isParentRouteActive(item, location) {
  const base = item.baseTo || item.to;
  if (!base) return false;
  return base === '/'
    ? location.pathname === '/'
    : location.pathname === base || location.pathname.startsWith(`${base}/`);
}

export default function Sidebar({ user, onLogout, open, onClose, collapsed, onToggleCollapse, onUserUpdated }) {
  const location = useLocation();
  const isAdmin = user?.role === 'admin';
  const [showDropup, setShowDropup] = useState(false);
  const [showProfileModal, setShowProfileModal] = useState(false);
  const [isDark, setIsDark] = useState(() => getInitialTheme() === 'dark');
  const [isHovered, setIsHovered] = useState(false);
  const [menuSearch, setMenuSearch] = useState('');
  const [openMenus, setOpenMenus] = useState(() => {
    const initial = {};
    MENU_GROUPS.forEach((group) => {
      group.links.forEach((link) => {
        if (link.children?.length && isParentRouteActive(link, location)) {
          initial[link.id] = true;
        }
      });
    });
    return initial;
  });

  const isExpanded = !collapsed || isHovered || open;

  useEffect(() => {
    setOpenMenus((prev) => {
      let changed = false;
      const next = { ...prev };
      MENU_GROUPS.forEach((group) => {
        group.links.forEach((link) => {
          if (link.children?.length && isParentRouteActive(link, location) && !next[link.id]) {
            next[link.id] = true;
            changed = true;
          }
        });
      });
      return changed ? next : prev;
    });
  }, [location.pathname]);

  function toggleMenu(id) {
    setOpenMenus((prev) => ({ ...prev, [id]: !prev[id] }));
  }

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
    if (confirmed) onLogout?.();
  }

  const query = menuSearch.trim().toLowerCase();
  const isItemAllowed = item => {
    if (item.teacherOnly && user?.role !== 'teacher') return false;
    if (item.nonTeacherOnly && user?.role === 'teacher') return false;
    if (item.adminOnly && !isAdmin) return false;
    if (item.permission && !hasPermission(user, item.permission)) return false;
    return true;
  };
  const filteredGroups = MENU_GROUPS.map(group => {
    if (group.adminOnly && !isAdmin) return null;
    const links = group.links
      .filter(isItemAllowed)
      .map(link => {
        if (!link.children?.length) {
          return !query || link.label.toLowerCase().includes(query) ? link : null;
        }
        const roleChildren = link.children.filter(isItemAllowed);
        if (!query) return { ...link, children: roleChildren };
        const parentMatches = link.label.toLowerCase().includes(query);
        const matchingChildren = roleChildren.filter(child =>
          child.label.toLowerCase().includes(query)
        );
        if (parentMatches) return { ...link, children: roleChildren };
        if (matchingChildren.length > 0) return { ...link, children: matchingChildren };
        return null;
      })
      .filter(Boolean);
    if (links.length === 0) return null;
    return { ...group, links };
  }).filter(Boolean);

  return (
    <>
      {open && (
        <button
          type="button"
          aria-label="Tutup menu"
          data-dls-action-ignore="true"
          className="fixed inset-0 z-30 cursor-pointer bg-black/55 backdrop-blur-xs lg:hidden"
          onClick={onClose}
        />
      )}

      <aside
        data-mobile-open={open ? 'true' : 'false'}
        onMouseEnter={() => { if (collapsed) setIsHovered(true); }}
        onMouseLeave={() => { setIsHovered(false); setShowDropup(false); }}
        className={`dls-shell-chrome-strong fixed inset-y-0 left-0 z-40 flex flex-col rounded-none border-r border-white/10 bg-[#0e1218]/95 text-white backdrop-blur-md transition-all duration-200 ease-in-out ${
          open ? 'translate-x-0' : '-translate-x-full'
        } lg:translate-x-0 ${
          isExpanded ? 'w-64' : 'w-16'
        } ${
          collapsed && isHovered ? 'shadow-2xl z-50' : ''
        }`}
      >
        {/* 64px Header matching shell header height */}
        <div
          className={`flex h-16 shrink-0 items-center border-b border-white/10 px-4 ${
            isExpanded ? 'justify-between' : 'justify-center'
          }`}
        >
          <Link to="/" onClick={() => onClose?.()} className="flex min-w-0 items-center gap-2.5">
            <img
              src="/assets/img/icon/denso-vector-logo.png"
              alt="Logo DENSO"
              className="size-8 shrink-0 rounded-md bg-white p-0.5 object-contain"
            />
            {isExpanded && (
              <div className="min-w-0">
                <p className="truncate text-sm font-bold tracking-tight text-white">
                  DENSO <span className="text-red-400">LMS</span>
                </p>
                <p className="truncate text-[11px] text-white/60">Pengelola &amp; Pengajar</p>
              </div>
            )}
          </Link>

          {isExpanded && (
            <button
              type="button"
              data-dls-action-ignore="true"
              onClick={onToggleCollapse}
              className="hidden h-8 w-8 cursor-pointer items-center justify-center rounded-lg text-white/70 transition hover:bg-white/10 hover:text-white lg:flex"
              title={collapsed ? 'Sematkan sidebar terbuka' : 'Ciutkan sidebar ke ikon'}
              aria-label="Toggle sidebar"
            >
              {collapsed ? <Pin className="h-4 w-4" /> : <ChevronLeft className="h-4 w-4" />}
            </button>
          )}

          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg text-white/70 transition hover:bg-white/10 hover:text-white lg:hidden"
            title="Tutup menu"
            aria-label="Tutup menu"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        {/* Shell translucent menu search */}
        {isExpanded && (
          <div className="px-4 pt-4 pb-1">
            <div className="relative">
              <Search
                className="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-white/55"
                aria-hidden="true"
              />
              <input
                type="search"
                placeholder="Cari menu..."
                value={menuSearch}
                onChange={e => setMenuSearch(e.target.value)}
                className="dls-shell-input h-9 w-full rounded-md pl-8 pr-7 text-xs outline-none transition placeholder:text-white/50"
              />
              {menuSearch && (
                <button
                  type="button"
                  data-dls-action-ignore="true"
                  onClick={() => setMenuSearch('')}
                  aria-label="Hapus pencarian menu"
                  className="absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer rounded p-0.5 text-white/60 hover:text-white"
                >
                  <X className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              )}
            </div>
          </div>
        )}

        {/* Navigation Links (design.md: white/70 inactive, solid primary red active) */}
        <nav className="dls-scrollbar flex-1 space-y-4 overflow-y-auto px-3 py-4">
          {filteredGroups.length === 0 && query ? (
            <div className="px-3 py-6 text-center text-white/60">
              <p className="text-xs">Menu &quot;{menuSearch}&quot; tidak ditemukan</p>
            </div>
          ) : (
            filteredGroups.map(group => (
              <div key={group.title || 'dashboard'} className="space-y-1">
                {!group.standalone &&
                  (isExpanded ? (
                    <p className="truncate px-3 pb-1 text-xs font-semibold uppercase tracking-[0.05em] text-white/45">
                      {group.title}
                    </p>
                  ) : (
                    <div className="mx-2 my-2 border-t border-white/10" title={group.title} />
                  ))}

                {group.links.map(link => {
                  const Icon = link.Icon;

                  if (!link.children?.length) {
                    return (
                      <NavLink
                        key={link.id || link.to}
                        to={link.to}
                        end={link.to === '/'}
                        onClick={() => {
                          if (menuSearch) setMenuSearch('');
                          onClose?.();
                        }}
                        title={!isExpanded ? link.label : undefined}
                        className={({ isActive }) =>
                          `flex items-center rounded-md text-[13px] font-semibold transition-colors ${
                            isExpanded ? 'gap-3 px-3 py-2' : 'justify-center px-0 py-2'
                          } ${
                            isActive
                              ? 'bg-primary text-white shadow-xs'
                              : 'text-white/70 hover:bg-white/10 hover:text-white'
                          }`
                        }
                      >
                        <Icon className="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
                        {isExpanded && <span className="truncate">{link.label}</span>}
                      </NavLink>
                    );
                  }

                  const parentActive = isParentRouteActive(link, location);
                  const submenuOpen = Boolean(query) || Boolean(openMenus[link.id]);

                  if (!isExpanded) {
                    return (
                      <Link
                        key={link.id}
                        to={link.baseTo}
                        onClick={() => onClose?.()}
                        title={link.label}
                        className={`flex items-center justify-center rounded-md px-0 py-2 text-[13px] font-semibold transition-colors ${
                          parentActive
                            ? 'bg-primary text-white shadow-xs'
                            : 'text-white/70 hover:bg-white/10 hover:text-white'
                        }`}
                      >
                        <Icon className="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
                      </Link>
                    );
                  }

                  return (
                    <div key={link.id} className="space-y-0.5">
                      <button
                        type="button"
                        data-dls-action-ignore="true"
                        aria-expanded={submenuOpen}
                        onClick={() => toggleMenu(link.id)}
                        className={`flex w-full cursor-pointer items-center justify-between gap-2 rounded-md px-3 py-2 text-left text-[13px] font-semibold transition-colors ${
                          parentActive
                            ? 'bg-white/12 text-white'
                            : 'text-white/75 hover:bg-white/10 hover:text-white'
                        }`}
                      >
                        <span className="flex min-w-0 items-center gap-3">
                          <Icon
                            className={`h-[18px] w-[18px] shrink-0 ${parentActive ? 'text-red-400' : ''}`}
                            aria-hidden="true"
                          />
                          <span className="truncate">{link.label}</span>
                        </span>
                        <ChevronDown
                          className={`h-4 w-4 shrink-0 text-white/55 transition-transform duration-150 ${
                            submenuOpen ? 'rotate-180 text-white' : ''
                          }`}
                          aria-hidden="true"
                        />
                      </button>

                      {submenuOpen && (
                        <div className="ml-[19px] mt-1 space-y-0.5 border-l border-white/15 pl-3">
                          {link.children.map(child => {
                            const childActive = isSubmenuMatch(child.to, location, link.children);
                            const [, hashTarget = ''] = child.to.split('#');
                            return (
                              <Link
                                key={child.to}
                                to={child.to}
                                state={{ submenuScrollTs: Date.now() }}
                                onClick={() => {
                                  if (menuSearch) setMenuSearch('');
                                  onClose?.();
                                  if (hashTarget) {
                                    window.requestAnimationFrame(() => scrollToTargetById(hashTarget));
                                  }
                                }}
                                className={`flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[12.5px] transition-colors ${
                                  childActive
                                    ? 'bg-primary font-semibold text-white shadow-xs'
                                    : 'font-medium text-white/65 hover:bg-white/10 hover:text-white'
                                }`}
                              >
                                <span
                                  className={`size-1.5 shrink-0 rounded-full ${
                                    childActive ? 'bg-white' : 'bg-white/35'
                                  }`}
                                  aria-hidden="true"
                                />
                                <span className="truncate">{child.label}</span>
                              </Link>
                            );
                          })}
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            ))
          )}
        </nav>

        {/* Bottom Profile & Settings Dropup */}
        <div className="relative border-t border-white/10 p-3">
          {showDropup && (
            <div
              className={`absolute bottom-full mb-2 rounded-xl border border-outline bg-white p-3 text-on-surface shadow-[0_20px_25px_rgba(0,0,0,0.20)] dark:border-slate-700 dark:bg-[#171e29] dark:text-slate-100 ${
                isExpanded ? 'left-3 right-3' : 'left-2 w-64'
              }`}
            >
              <div className="flex items-center gap-3 border-b border-outline pb-3 dark:border-slate-700/70">
                <div className="grid size-10 shrink-0 place-items-center rounded-lg bg-primary text-sm font-bold text-white">
                  {user?.name?.charAt(0).toUpperCase() || 'U'}
                </div>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-xs font-bold text-on-surface">{user?.name}</p>
                  <p className="truncate font-mono text-[11px] text-muted">NPK: {user?.npk}</p>
                  <span className="mt-1 inline-block rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.05em] text-primary">
                    {resolveRoleLabel(user?.role)}
                  </span>
                </div>
              </div>

              <div className="mt-2 flex items-center justify-between rounded-lg p-2 transition hover:bg-surface-low">
                <div className="flex items-center gap-2 text-xs font-medium text-on-surface">
                  {isDark ? (
                    <Moon className="h-4 w-4 text-amber-500" />
                  ) : (
                    <Sun className="h-4 w-4 text-muted" />
                  )}
                  <span>{isDark ? 'Mode Gelap' : 'Mode Terang'}</span>
                </div>
                <button
                  type="button"
                  data-dls-action-ignore="true"
                  onClick={handleThemeSwitch}
                  className={`relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors ${
                    isDark ? 'bg-primary' : 'bg-slate-300'
                  }`}
                  aria-label="Ganti Tema"
                >
                  <span
                    className={`pointer-events-none inline-block size-4 transform rounded-full bg-white shadow transition ${
                      isDark ? 'translate-x-4' : 'translate-x-0'
                    }`}
                  />
                </button>
              </div>

              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => {
                  setShowDropup(false);
                  setShowProfileModal(true);
                }}
                className="mt-1 flex w-full cursor-pointer items-center gap-2 rounded-lg p-2 text-xs font-medium text-on-surface transition hover:bg-surface-low"
              >
                <UserCog className="h-4 w-4 text-primary" aria-hidden="true" />
                Edit Profil &amp; Password
              </button>

              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={handleConfirmLogout}
                className="mt-1 flex w-full cursor-pointer items-center gap-2 rounded-lg border-t border-outline p-2 pt-2.5 text-xs font-semibold text-primary transition hover:bg-red-50 dark:border-slate-700/70 dark:hover:bg-red-950/30"
              >
                <LogOut className="h-4 w-4" aria-hidden="true" />
                Keluar dari Akun
              </button>
            </div>
          )}

          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={() => setShowDropup(prev => !prev)}
            aria-expanded={showDropup}
            aria-label="Menu Pengguna & Pengaturan"
            title={!isExpanded ? `${user?.name} (${user?.role})` : undefined}
            className={`flex w-full cursor-pointer items-center rounded-lg text-white transition hover:bg-white/10 ${
              isExpanded ? 'gap-2.5 p-2 text-left' : 'justify-center p-2'
            }`}
          >
            <div className="grid size-8 shrink-0 place-items-center rounded-md bg-primary text-xs font-bold text-white">
              {user?.name?.slice(0, 1).toUpperCase() || 'U'}
            </div>
            {isExpanded && (
              <>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-xs font-semibold text-white">{user?.name}</p>
                  <p className="truncate text-[11px] text-white/60">
                    {resolveRoleLabel(user?.role)} · {user?.npk}
                  </p>
                </div>
                <ChevronUp
                  className={`h-4 w-4 text-white/60 transition-transform ${showDropup ? 'rotate-180 text-white' : ''}`}
                  aria-hidden="true"
                />
              </>
            )}
          </button>
        </div>
      </aside>

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
