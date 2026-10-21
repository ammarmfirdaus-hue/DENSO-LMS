import { Outlet } from 'react-router-dom';
import { useState } from 'react';
import Sidebar from '../components/Sidebar';

export default function AppLayout({ user, onLogout }) {
  const [open, setOpen] = useState(false);
  return <div className="min-h-screen">
    <Sidebar user={user} onLogout={onLogout} open={open} onClose={() => setOpen(false)} />
    <div className="min-h-screen lg:pl-64">
      <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-outline/50 bg-white/90 px-4 backdrop-blur md:px-8">
        <button onClick={() => setOpen(true)} className="rounded-lg p-2 lg:hidden" aria-label="Buka menu"><span className="material-symbol">menu</span></button>
        <div className="hidden text-sm text-muted sm:block">DLS <span className="mx-2">/</span> Portal Pengelola</div>
        <div className="ml-auto flex items-center gap-3"><span className="hidden text-sm text-muted md:block">Selamat datang, {user?.name}</span><button aria-label="Notifikasi" className="relative rounded-full p-2 hover:bg-surface-low"><span className="material-symbol">notifications</span><i className="absolute right-2 top-2 size-2 rounded-full bg-primary" /></button></div>
      </header>
      <main className="mx-auto max-w-[1440px] p-4 md:p-8"><Outlet /></main>
    </div>
  </div>;
}
