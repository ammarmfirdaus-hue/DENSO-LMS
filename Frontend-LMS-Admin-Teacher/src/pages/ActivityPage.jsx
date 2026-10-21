import { useEffect, useState } from 'react';
import { api } from '../api/client';

export default function ActivityPage() {
  const [items, setItems] = useState([]); const [error, setError] = useState('');
  useEffect(() => { api('/dashboard/activity').then(value => setItems(value.activities)).catch(e => setError(e.message)); }, []);
  return <div><p className="text-sm font-semibold text-primary">PEMANTAUAN</p><h1 className="mt-2 text-3xl font-bold">Aktivitas</h1><p className="mt-2 text-muted">Aktivitas terbaru pada portal pengelola.</p>{error && <p role="alert" className="mt-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}<div className="mt-7 divide-y divide-outline/40 rounded-2xl border border-outline/50 bg-white">{items.length ? items.map(item => <article key={item.activity_id} className="flex gap-4 p-5"><span className="material-symbol grid size-10 shrink-0 place-items-center rounded-xl bg-secondary-container/30 text-secondary">history</span><div><p className="font-semibold">{item.description}</p><p className="mt-1 text-sm text-muted">{item.activity_type} · {new Date(item.created_at).toLocaleString('id-ID')}</p></div></article>) : <p className="p-8 text-center text-muted">Belum ada catatan aktivitas.</p>}</div></div>;
}
