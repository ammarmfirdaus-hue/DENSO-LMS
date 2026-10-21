import { useEffect, useState } from 'react';
import { api } from '../api/client';

const stats = [
  { key: 'active_courses', label: 'Course aktif', icon: 'menu_book', tone: 'bg-[#e9edff] text-secondary' },
  { key: 'active_employees', label: 'Pengguna aktif', icon: 'groups', tone: 'bg-[#e4f5e5] text-tertiary' },
  { key: 'active_sessions', label: 'Sesi belajar', icon: 'event_note', tone: 'bg-[#fff0e4] text-[#a84c00]' }
];

export default function DashboardPage() {
  const [data, setData] = useState(null); const [error, setError] = useState('');
  useEffect(() => { api('/dashboard/summary').then(result => setData(result.summary)).catch(e => setError(e.message)); }, []);
  return <div>
    <div className="mb-8 flex flex-wrap items-end justify-between gap-4"><div><p className="text-sm font-semibold text-primary">RINGKASAN</p><h1 className="mt-2 text-3xl font-bold tracking-tight">Dashboard</h1><p className="mt-2 text-muted">Pantau aktivitas pembelajaran dan pengelolaan LMS.</p></div><span className="rounded-full border border-outline/70 bg-white px-4 py-2 text-sm text-muted">Tahun 2026 <span className="material-symbol ml-1 align-middle">expand_more</span></span></div>
    {error && <p role="alert" className="mb-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}
    <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">{stats.map(item => <article key={item.key} className="rounded-2xl border border-outline/50 bg-white p-5 shadow-[0_4px_18px_rgba(25,20,20,.04)]"><div className="flex items-center justify-between"><div><p className="text-sm text-muted">{item.label}</p><p className="mt-3 text-3xl font-bold">{data ? Number(data[item.key]).toLocaleString('id-ID') : '—'}</p></div><span className={`grid size-12 place-items-center rounded-xl ${item.tone}`}><span className="material-symbol">{item.icon}</span></span></div><p className="mt-4 text-xs text-muted">Data dari database pusat</p></article>)}</section>
    <section className="mt-8 grid gap-6 xl:grid-cols-[1.5fr_1fr]"><div className="rounded-2xl border border-outline/50 bg-white p-6"><div className="flex items-center justify-between"><div><h2 className="text-lg font-bold">Aktivitas LMS</h2><p className="mt-1 text-sm text-muted">Ringkasan aktivitas terbaru.</p></div><span className="material-symbol text-secondary">query_stats</span></div><div className="mt-8 grid h-48 place-items-center rounded-xl bg-surface-low text-center"><div><span className="material-symbol text-4xl text-secondary">bar_chart</span><p className="mt-2 text-sm text-muted">Grafik aktivitas akan muncul setelah data tersedia.</p></div></div></div>
      <div className="rounded-2xl bg-[#292526] p-6 text-white"><p className="text-sm font-medium text-white/60">PINTASAN</p><h2 className="mt-2 text-xl font-bold">Kelola pembelajaran</h2><p className="mt-2 text-sm leading-6 text-white/70">Buat course dan susun sesi materi untuk peserta.</p><a href="/courses" className="mt-6 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-[#292526]">Lihat course <span className="material-symbol">arrow_forward</span></a></div></section>
  </div>;
}
