import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';

const categories = [
  ['all', 'Semua'], ['unread', 'Belum dibaca'], ['important', 'Penting'],
  ['announcement', 'Pengumuman'], ['course', 'Course'], ['assessment', 'Assessment'], ['forum', 'Forum'], ['system', 'System']
];
const audiences = [['student', 'Student'], ['teacher', 'Teacher'], ['admin', 'Admin']];
const categoryName = Object.fromEntries(categories.filter(([key]) => key !== 'all' && key !== 'unread' && key !== 'important'));

export default function NotificationsPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [items, setItems] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [targetRoles, setTargetRoles] = useState(['student']);
  const [sendEmail, setSendEmail] = useState(false);

  async function loadNotifications() {
    try {
      const result = await api('/notifications?limit=100');
      setItems(result.notifications || []);
      setUnreadCount(result.unreadCount || 0);
      setError('');
    } catch (e) { setError(e.message); }
    finally { setLoading(false); }
  }

  useEffect(() => { loadNotifications(); }, []);
  useEffect(() => {
    window.addEventListener('dls_notifications_changed', loadNotifications);
    return () => window.removeEventListener('dls_notifications_changed', loadNotifications);
  }, []);

  const filtered = useMemo(() => {
    const query = search.trim().toLocaleLowerCase('id');
    return items.filter(item => {
      const matchesFilter = filter === 'all' || (filter === 'unread' ? !item.is_read : filter === 'important' ? item.priority === 'important' : item.category === filter);
      const matchesSearch = !query || `${item.title} ${item.message} ${item.sender_name} ${item.category}`.toLocaleLowerCase('id').includes(query);
      return matchesFilter && matchesSearch;
    });
  }, [items, filter, search]);

  function toggleAudience(role) {
    setTargetRoles(current => current.includes(role) ? current.filter(value => value !== role) : [...current, role]);
  }

  async function sendNotification(event) {
    event.preventDefault();
    const form = event.currentTarget;
    if (!targetRoles.length) { setError('Pilih setidaknya satu role penerima.'); return; }
    setBusy(true); setError(''); setNotice('');
    try {
      const data = new FormData(form);
      const result = await api('/notifications', {
        method: 'POST',
        body: JSON.stringify({
          title: data.get('title'), message: data.get('message'),
          category: data.get('category'), priority: data.get('priority'),
          targetRoles, sendEmail
        })
      });
      form.reset(); setTargetRoles(['student']); setSendEmail(false);
      const emailNote = sendEmail ? ` ${result.emailQueued || 0} email masuk antrean pengiriman.` : '';
      setNotice(`Notifikasi terkirim ke ${result.recipients.join(', ')}.${emailNote}`);
      await loadNotifications();
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function markRead(id) {
    try {
      await api(`/notifications/${id}/read`, { method: 'POST', body: '{}' });
      await loadNotifications();
    } catch (e) { setError(e.message); }
  }

  async function markAllRead() {
    setBusy(true);
    try {
      await api('/notifications/read-all', { method: 'POST', body: '{}' });
      await loadNotifications();
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  return <div className="mx-auto min-w-0 max-w-6xl">
    <header className="mb-6 border-b border-outline/50 pb-5">
      <p className="text-xs font-semibold uppercase tracking-wider text-primary">PUSAT INFORMASI</p>
      <div className="mt-2 flex flex-wrap items-end justify-between gap-3">
        <div><h1 className="text-2xl font-bold text-on-surface sm:text-3xl">Notifikasi</h1><p className="mt-2 text-sm text-muted">Pengumuman dan pengingat untuk akun LMS.</p></div>
        <span className="rounded-lg border border-outline/50 bg-white px-3 py-2 text-sm text-secondary">{unreadCount} belum dibaca</span>
      </div>
    </header>

    {error && <p role="alert" className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{error}</p>}
    {notice && <p role="status" className="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</p>}

    <form onSubmit={sendNotification} className="mb-6 rounded-xl border border-outline/50 bg-white p-4 sm:p-5">
      <div className="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-outline/30 pb-3">
        <div><h2 className="font-semibold text-on-surface">Kirim notifikasi</h2><p className="mt-1 text-xs text-muted">Penerima melihat pesan di pusat notifikasi mereka.</p></div>
        <span className="rounded-md bg-surface-low px-2 py-1 text-xs font-semibold capitalize text-secondary">{user?.role}</span>
      </div>
      <div className="grid min-w-0 gap-3 sm:grid-cols-2">
        <label className="min-w-0 text-xs font-semibold text-muted sm:col-span-2">Judul
          <input name="title" required minLength="3" maxLength="180" className="mt-1.5 h-11 w-full min-w-0 rounded-lg border border-outline/60 bg-white px-3 text-sm font-normal text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" placeholder="Contoh: Pengingat penyelesaian materi sesi 2" />
        </label>
        <label className="min-w-0 text-xs font-semibold text-muted sm:col-span-2">Isi pesan
          <textarea name="message" required maxLength="2000" rows="4" className="mt-1.5 w-full min-w-0 resize-y rounded-lg border border-outline/60 bg-white p-3 text-sm font-normal leading-6 text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" placeholder="Tulis informasi yang perlu diketahui penerima." />
        </label>
        <label className="text-xs font-semibold text-muted">Kategori
          <select name="category" className="mt-1.5 h-11 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm font-normal text-on-surface">
            <option value="announcement">Pengumuman</option><option value="course">Course</option><option value="assessment">Assessment</option><option value="forum">Forum</option><option value="system">System</option>
          </select>
        </label>
        <label className="text-xs font-semibold text-muted">Prioritas
          <select name="priority" className="mt-1.5 h-11 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm font-normal text-on-surface">
            <option value="normal">Normal</option><option value="important">Penting</option>
          </select>
        </label>
      </div>

      <fieldset className="mt-4 min-w-0">
        <legend className="mb-2 text-xs font-semibold text-muted">Kirim kepada</legend>
        <div className="flex flex-wrap gap-2">
          {audiences.filter(([role]) => isAdmin || role === 'student').map(([role, label]) => <label key={role} className={`inline-flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border px-3 text-sm ${targetRoles.includes(role) ? 'border-primary/30 bg-primary/5 text-primary' : 'border-outline/50 bg-white text-secondary'}`}>
            <input type="checkbox" checked={targetRoles.includes(role)} onChange={() => toggleAudience(role)} className="accent-primary" />{label}
          </label>)}
        </div>
      </fieldset>

      <label className="mt-4 flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-outline/40 bg-surface-low/50 p-3 text-sm text-secondary">
        <input type="checkbox" checked={sendEmail} onChange={event => setSendEmail(event.target.checked)} className="mt-0.5 accent-primary" />
        <span><strong className="font-semibold text-on-surface">Kirim juga melalui email</strong><span className="mt-0.5 block text-xs text-muted">Email akan masuk antrean untuk akun penerima yang memiliki alamat email.</span></span>
      </label>
      <div className="mt-4 flex justify-end"><button disabled={busy} className="min-h-11 w-full rounded-lg bg-primary px-5 text-sm font-semibold text-white hover:opacity-90 disabled:cursor-wait disabled:opacity-60 sm:w-auto">{busy ? 'Mengirim…' : 'Kirim notifikasi'}</button></div>
    </form>

    <section>
      <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div><h2 className="font-semibold text-on-surface">Notifikasi terbaru</h2><p className="mt-1 text-xs text-muted">Menampilkan sampai 100 notifikasi terakhir.</p></div>
        <button type="button" disabled={busy || unreadCount === 0} onClick={markAllRead} className="min-h-10 rounded-lg border border-outline/60 bg-white px-3 text-sm font-semibold text-secondary hover:bg-surface-low disabled:opacity-50">Tandai semua dibaca</button>
      </div>
      <div className="mb-4 min-w-0 space-y-3">
        <input type="search" value={search} onChange={event => setSearch(event.target.value)} placeholder="Cari notifikasi..." className="h-11 w-full min-w-0 rounded-lg border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />
        <div className="max-w-full overflow-x-auto overscroll-x-contain pb-1">
          <div className="flex w-max min-w-full gap-1 rounded-lg border border-outline/40 bg-white p-1" role="tablist" aria-label="Filter kategori notifikasi">
            {categories.map(([key, label]) => <button key={key} type="button" role="tab" aria-selected={filter === key} onClick={() => setFilter(key)} className={`min-h-9 shrink-0 whitespace-nowrap rounded-md px-3 text-xs font-semibold transition ${filter === key ? 'bg-primary text-white' : 'text-muted hover:bg-surface-low hover:text-on-surface'}`}>{label}</button>)}
          </div>
        </div>
      </div>
      <div className="space-y-3" aria-live="polite">
        {loading ? <div className="rounded-xl border border-outline/50 bg-white p-8 text-center text-sm text-muted">Memuat notifikasi…</div>
          : filtered.length ? filtered.map(item => <article key={item.notification_id} className={`min-w-0 rounded-xl border bg-white p-4 sm:p-5 ${item.is_read ? 'border-outline/50' : 'border-primary/30 bg-primary/[0.02]'}`}>
            <div className="flex min-w-0 items-start gap-3">
              <span className={`material-symbol mt-0.5 text-xl ${item.is_read ? 'text-muted' : 'text-primary'}`} aria-hidden="true">{item.category === 'course' ? 'school' : item.category === 'assessment' ? 'assignment' : item.category === 'forum' ? 'forum' : item.category === 'system' ? 'settings' : 'campaign'}</span>
              <div className="min-w-0 flex-1">
                <div className="flex min-w-0 flex-wrap items-start justify-between gap-2">
                  <div className="min-w-0"><div className="flex flex-wrap items-center gap-2"><h3 className="break-words font-semibold text-on-surface">{item.title}</h3>{item.priority === 'important' && <span className="rounded bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase text-primary">Penting</span>}</div><p className="mt-1 text-xs text-muted">Dari {item.sender_name} · {new Date(item.created_at).toLocaleString('id-ID')}</p></div>
                  {!item.is_read && <span className="mt-1 size-2 shrink-0 rounded-full bg-primary" aria-label="Belum dibaca" />}
                </div>
                <p className="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-secondary">{item.message}</p>
                <div className="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-outline/30 pt-3">
                  <span className="rounded-md bg-surface-low px-2 py-1 text-xs text-muted">{categoryName[item.category] || item.category}</span>
                  {!item.is_read && <button type="button" onClick={() => markRead(item.notification_id)} className="min-h-9 rounded-md px-2 text-xs font-semibold text-primary hover:bg-primary/5">Tandai dibaca</button>}
                </div>
              </div>
            </div>
          </article>)
          : <div className="rounded-xl border border-dashed border-outline/60 bg-white p-10 text-center"><p className="font-semibold text-on-surface">{items.length ? 'Tidak ada hasil' : 'Belum ada notifikasi'}</p><p className="mt-1 text-sm text-muted">{items.length ? 'Coba kata kunci atau kategori lain.' : 'Notifikasi baru akan muncul di sini.'}</p></div>}
      </div>
    </section>
  </div>;
}
