import { useEffect, useMemo, useState } from 'react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';

function activityIcon(type = '') {
  const value = type.toLowerCase();
  if (value.includes('login')) return 'login';
  if (value.includes('course')) return 'menu_book';
  if (value.includes('material')) return 'description';
  if (value.includes('user') || value.includes('account')) return 'person';
  if (value.includes('quiz') || value.includes('grade')) return 'quiz';
  return 'history';
}

function formatDate(value) {
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? 'Waktu tidak tersedia' : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
}

export default function ActivityPage() {
  const [items, setItems] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [type, setType] = useState('all');

  useEffect(() => {
    api('/dashboard/activity')
      .then(value => setItems(value.activities || []))
      .catch(e => setError(e.message))
      .finally(() => setLoading(false));
  }, []);

  const types = useMemo(() => [...new Set(items.map(item => item.activity_type).filter(Boolean))].sort(), [items]);
  const filtered = useMemo(() => items.filter(item => (type === 'all' || item.activity_type === type) && `${item.description || ''} ${item.activity_type || ''} ${item.created_at || ''}`.toLowerCase().includes(search.trim().toLowerCase())), [items, type, search]);
  const page = usePagination(filtered, 10);
  const todayCount = items.filter(item => new Date(item.created_at).toDateString() === new Date().toDateString()).length;

  return <div className="mx-auto max-w-7xl space-y-6">
    <header className="flex flex-wrap items-end justify-between gap-4">
      <div><p className="text-xs font-bold uppercase tracking-[.18em] text-primary">Pemantauan</p><h1 className="mt-2 text-3xl font-bold tracking-tight">Aktivitas</h1><p className="mt-2 text-sm leading-6 text-muted">Pantau catatan aktivitas terbaru pada portal pengelola.</p></div>
      <div className="flex items-center gap-3 rounded-2xl border border-outline/50 bg-white px-4 py-3 shadow-sm"><span className="grid size-10 place-items-center rounded-xl bg-primary/10 text-primary"><span className="material-symbol">event_available</span></span><div><p className="text-xl font-bold leading-none">{todayCount}</p><p className="mt-1 text-xs text-muted">Aktivitas hari ini</p></div></div>
    </header>

    {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{error}</p>}

    <section className="overflow-hidden rounded-2xl border border-outline/50 bg-white shadow-sm">
      <div className="border-b border-outline/40 p-4 sm:p-6">
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3"><div><h2 className="text-lg font-bold">Log aktivitas</h2><p className="mt-1 text-sm text-muted">Menampilkan {filtered.length} dari {items.length} aktivitas terbaru.</p></div><span className="rounded-full bg-surface-low px-3 py-1.5 text-xs font-semibold text-secondary">{types.length} jenis aktivitas</span></div>
        <ListFilterBar search={search} onSearch={setSearch} placeholder="Cari aktivitas atau tanggal..." filter={type} onFilter={setType} label="Filter jenis aktivitas" options={[{ value: 'all', label: 'Semua jenis' }, ...types.map(value => ({ value, label: value }))]} />
      </div>

      {page.pagedItems.length ? <div className="divide-y divide-outline/40">
        {page.pagedItems.map(item => <article key={item.activity_id} className="flex gap-3 px-4 py-4 transition hover:bg-surface-low/50 sm:gap-4 sm:px-6 sm:py-5">
          <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary sm:size-11"><span className="material-symbol">{activityIcon(item.activity_type)}</span></span>
          <div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><p className="break-words text-sm font-semibold leading-6 text-on-surface">{item.description || 'Aktivitas tercatat'}</p><span className="rounded-full bg-surface-low px-2.5 py-1 text-[11px] font-semibold capitalize text-secondary">{item.activity_type || 'Umum'}</span></div><p className="mt-1 flex flex-wrap items-center gap-x-1.5 text-xs text-muted"><span className="material-symbol text-sm" aria-hidden="true">schedule</span>{formatDate(item.created_at)}</p></div>
        </article>)}
      </div> : <div className="p-10 text-center sm:p-14"><span className="material-symbol mx-auto grid size-14 place-items-center rounded-2xl bg-surface-low text-3xl text-muted">{loading ? 'hourglass_top' : 'history'}</span><h3 className="mt-4 font-bold">{loading ? 'Memuat aktivitas…' : items.length ? 'Tidak ada aktivitas yang cocok' : 'Belum ada aktivitas'}</h3><p className="mt-1 text-sm text-muted">{loading ? 'Mohon tunggu sebentar.' : 'Catatan aktivitas akan tampil di sini saat tersedia.'}</p></div>}

      <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
    </section>
  </div>;
}
