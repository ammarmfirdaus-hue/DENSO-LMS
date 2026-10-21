import { NavLink } from 'react-router-dom';

const links = [
  { to: '/', icon: 'grid_view', label: 'Dashboard' },
  { to: '/courses', icon: 'menu_book', label: 'Course & Materi' },
  { to: '/activity', icon: 'monitoring', label: 'Aktivitas' },
  { to: '/grades', icon: 'fact_check', label: 'Kelola Nilai' },
  { to: '/forum', icon: 'forum', label: 'Forum Diskusi' },
  { to: '/users', icon: 'groups', label: 'Pengguna' }
];

export default function Sidebar({ user, onLogout, open, onClose }) {
  const visibleLinks = user?.role === 'admin' ? links : links.filter(link => link.to !== '/users');
  return <>
    {open && <button aria-label="Tutup menu" className="fixed inset-0 z-30 bg-black/30 lg:hidden" onClick={onClose} />}
    <aside className={`fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-outline/50 bg-white transition-transform lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
      <div className="flex h-20 items-center gap-3 border-b border-outline/40 px-6">
        <div className="grid size-10 place-items-center rounded-xl bg-primary text-lg font-bold text-white">D</div>
        <div><p className="font-bold tracking-wide">DENSO <span className="text-primary">LMS</span></p><p className="text-xs text-muted">Learning Management</p></div>
      </div>
      <nav className="flex-1 space-y-1 px-3 py-5">{visibleLinks.map(link => <NavLink key={link.to} to={link.to} end={link.to === '/'} onClick={onClose} className={({ isActive }) => `flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition ${isActive ? 'bg-[#fbe9e7] text-primary' : 'text-[#635d5c] hover:bg-surface-low'}`}><span className="material-symbol">{link.icon}</span>{link.label}</NavLink>)}</nav>
      <div className="border-t border-outline/40 p-4"><div className="flex items-center gap-3"><div className="grid size-10 place-items-center rounded-full bg-secondary-container/30 font-bold text-secondary">{user?.name?.slice(0,1) || 'U'}</div><div className="min-w-0 flex-1"><p className="truncate text-sm font-semibold">{user?.name}</p><p className="text-xs capitalize text-muted">{user?.role}</p></div><button onClick={onLogout} className="rounded-lg p-2 text-muted hover:bg-surface-low" title="Keluar"><span className="material-symbol">logout</span></button></div></div>
    </aside>
  </>;
}
