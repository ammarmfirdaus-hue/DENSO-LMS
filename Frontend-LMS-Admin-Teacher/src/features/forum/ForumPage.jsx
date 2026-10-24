import { useEffect, useMemo, useState } from 'react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

export default function ForumPage() {
  const [threads, setThreads] = useState([]); const [error, setError] = useState(''); const [search, setSearch] = useState(''); const [status, setStatus] = useState('all');
  async function load() { try { setThreads((await api('/forum')).threads); setError(''); } catch (e) { setError(e.message); } }
  useEffect(() => { load(); }, []);
  async function update(id, nextStatus) {
    try {
      await api(`/forum/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status: nextStatus }) });
      await load();
      showAlert.toast(`Status topik diubah menjadi ${nextStatus.toUpperCase()}`, 'success');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengubah Status Forum', e.message || 'Terjadi kesalahan sistem.');
    }
  }
  const filtered = useMemo(() => threads.filter(thread => (status === 'all' || thread.status === status) && `${thread.title} ${thread.body} ${thread.author_npk} ${thread.status}`.toLowerCase().includes(search.trim().toLowerCase())), [threads, status, search]);
  const page = usePagination(filtered);
  return <div><p className="text-sm font-semibold text-primary">KOMUNITAS</p><h1 className="mt-2 text-3xl font-bold">Forum Diskusi</h1><p className="mt-2 text-muted">Tinjau topik dan moderasi visibilitas diskusi.</p>{error && <p role="alert" className="mt-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}<div className="mt-7"><p className="mb-3 text-sm text-muted">Menampilkan {filtered.length} dari {threads.length} topik</p><ListFilterBar search={search} onSearch={setSearch} placeholder="Cari topik, isi diskusi, atau NPK..." filter={status} onFilter={setStatus} label="Filter status" options={[{value:'all',label:'Semua status'},{value:'visible',label:'Visible'},{value:'hidden',label:'Hidden'},{value:'locked',label:'Locked'}]}/><div className="space-y-4">{page.pagedItems.length ? page.pagedItems.map(thread => <article key={thread.thread_id} className="rounded-2xl border border-outline/50 bg-white p-5"><div className="flex flex-wrap justify-between gap-3"><div><h2 className="font-bold">{thread.title}</h2><p className="mt-1 text-xs text-muted">{thread.author_npk} · {new Date(thread.created_at).toLocaleString('id-ID')}</p></div><select aria-label={`Status ${thread.title}`} value={thread.status} onChange={e => update(thread.thread_id,e.target.value)} className="h-10 rounded-lg border border-outline bg-white px-3"><option value="visible">Visible</option><option value="hidden">Hidden</option><option value="locked">Locked</option></select></div><p className="mt-4 whitespace-pre-wrap text-sm leading-6">{thread.body}</p></article>) : <div className="rounded-2xl border border-outline/50 bg-white p-8 text-center text-muted">{threads.length ? 'Tidak ada topik yang cocok dengan pencarian/filter.' : 'Belum ada topik forum.'}</div>}</div><div className="mt-4 rounded-2xl border border-outline/50 bg-white"><Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize}/></div></div></div>;
}
