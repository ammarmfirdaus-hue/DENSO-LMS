import { useEffect, useMemo, useRef, useState } from 'react';
import { api } from '../../api/client';
import { compressForumImage } from '../../utils/compress-forum-image';

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';
const fieldClass = 'h-10 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10';
const messageClass = 'w-full resize-y rounded-lg border border-outline/60 bg-white p-3 text-sm leading-6 outline-none focus:border-primary focus:ring-2 focus:ring-primary/10';

export default function ForumPage() {
  const [sessions, setSessions] = useState([]);
  const [selectedKey, setSelectedKey] = useState('');
  const [threads, setThreads] = useState([]);
  const [details, setDetails] = useState({});
  const [expanded, setExpanded] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const selectedSession = sessions.find(item => sessionKey(item) === selectedKey);
  const expandedRef = useRef(expanded);
  useEffect(() => { expandedRef.current = expanded; }, [expanded]);

  async function loadSessions() {
    try {
      const result = await api('/forum/management/courses');
      const next = result.sessions || [];
      setSessions(next);
      setSelectedKey(current => next.some(item => sessionKey(item) === current) ? current : sessionKey(next[0]) || '');
      setError('');
    } catch (e) { setError(e.message); }
  }

  async function loadThreads() {
    if (!selectedSession) { setThreads([]); return; }
    try {
      const query = new URLSearchParams({ courseCode: selectedSession.course_code, sessionCode: String(selectedSession.session_code) });
      const result = await api(`/forum/management/threads?${query}`);
      setThreads(result.threads || []);
      setError('');
    } catch (e) { setError(e.message); }
  }

  async function loadDetails(threadId) {
    try {
      const result = await api(`/forum/threads/${threadId}`);
      setDetails(current => ({ ...current, [threadId]: result }));
    } catch (e) { setError(e.message); }
  }

  useEffect(() => { loadSessions(); }, []);
  useEffect(() => { loadThreads(); }, [selectedKey]);
  useEffect(() => {
    if (!selectedSession) return undefined;
    const activeSession = selectedSession;
    const query = new URLSearchParams({ courseCode: activeSession.course_code, sessionCode: String(activeSession.session_code) });
    const events = new EventSource(`${API_BASE}/forum/events?${query}`, { withCredentials: true });
    events.addEventListener('forum', event => {
      loadThreads();
      if (expandedRef.current) loadDetails(expandedRef.current);
      if (JSON.parse(event.data || '{}').type === 'session_status') loadSessions();
    });
    events.addEventListener('ready', () => { loadThreads(); if (expandedRef.current) loadDetails(expandedRef.current); });
    events.onerror = () => { /* EventSource retries automatically while the session remains active. */ };
    return () => events.close();
  }, [selectedSession?.course_code, selectedSession?.session_code]);

  async function toggleSession() {
    if (!selectedSession) return;
    setBusy(true);
    try {
      const next = !Boolean(selectedSession.is_open);
      await api(`/forum/management/sessions/${encodeURIComponent(selectedSession.course_code)}/${encodeURIComponent(selectedSession.session_code)}`, {
        method: 'PATCH', body: JSON.stringify({ isOpen: next })
      });
      setSessions(current => current.map(item => sessionKey(item) === selectedKey ? { ...item, is_open: next } : item));
      setNotice(next ? 'Forum sesi dibuka.' : 'Forum sesi ditutup. Student tetap dapat membaca diskusi.');
      window.setTimeout(() => setNotice(''), 3500);
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function createThread(event) {
    event.preventDefault();
    if (!selectedSession) return;
    const form = event.currentTarget;
    setBusy(true);
    try {
      const data = new FormData(form);
      const image = data.get('image');
      if (image instanceof File && image.size > 0) data.set('image', await compressForumImage(image));
      const body = image instanceof File && image.size > 0 ? data : JSON.stringify({
        courseCode: selectedSession.course_code, sessionCode: String(selectedSession.session_code),
        title: data.get('title'), body: data.get('body')
      });
      if (body instanceof FormData) {
        body.set('courseCode', selectedSession.course_code);
        body.set('sessionCode', String(selectedSession.session_code));
      }
      await api('/forum/management/threads', { method: 'POST', body });
      form.reset();
      setNotice('Diskusi berhasil dikirim.');
      await loadThreads();
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function replyToThread(event, threadId) {
    event.preventDefault();
    const form = event.currentTarget;
    setBusy(true);
    try {
      const data = new FormData(form);
      const image = data.get('image');
      if (image instanceof File && image.size > 0) data.set('image', await compressForumImage(image));
      const body = image instanceof File && image.size > 0 ? data : JSON.stringify({ body: data.get('body') });
      await api(`/forum/threads/${threadId}/replies`, { method: 'POST', body });
      form.reset();
      await Promise.all([loadDetails(threadId), loadThreads()]);
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function toggleLike(targetType, targetId, parentId) {
    try {
      const result = await api('/forum/likes', { method: 'POST', body: JSON.stringify({ targetType, targetId }) });
      if (targetType === 'thread') {
        setThreads(current => current.map(item => item.thread_id === targetId ? { ...item, likes_count: result.likes_count, liked_by_me: result.liked } : item));
      } else {
        setDetails(current => {
          const detail = current[parentId];
          if (!detail) return current;
          return { ...current, [parentId]: { ...detail, replies: detail.replies.map(item => item.reply_id === targetId ? { ...item, likes_count: result.likes_count, liked_by_me: result.liked } : item) } };
        });
      }
    } catch (e) { setError(e.message); }
  }

  async function updateThreadStatus(thread, status) {
    try {
      await api(`/forum/${thread.thread_id}/status`, { method: 'PATCH', body: JSON.stringify({ status }) });
      await loadThreads();
    } catch (e) { setError(e.message); }
  }

  const grouped = useMemo(() => {
    const courses = new Map();
    sessions.forEach(item => {
      const current = courses.get(item.course_code) || { title: item.course_title, sessions: [] };
      current.sessions.push(item);
      courses.set(item.course_code, current);
    });
    return [...courses.entries()].map(([code, value]) => ({ code, ...value }));
  }, [sessions]);

  return <div className="mx-auto min-w-0 max-w-6xl">
    <header className="mb-6 border-b border-outline/50 pb-5">
      <p className="text-xs font-semibold uppercase tracking-wider text-primary">KOMUNITAS PEMBELAJARAN</p>
      <h1 className="mt-2 text-2xl font-bold text-on-surface sm:text-3xl">Forum diskusi</h1>
      <p className="mt-2 text-sm text-muted">Kelola forum per course dan sesi, lalu ikut berdiskusi bersama peserta.</p>
    </header>

    {error && <p role="alert" className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{error}</p>}
    {notice && <p role="status" className="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{notice}</p>}

    <section className="mb-5 flex min-w-0 flex-col gap-3 rounded-xl border border-outline/50 bg-white p-4 sm:flex-row sm:items-end sm:justify-between">
      <div className="grid min-w-0 flex-1 gap-3 sm:grid-cols-2">
        <label className="min-w-0 text-xs font-semibold text-muted">Course<select className={`${fieldClass} mt-1.5 min-w-0`} value={selectedSession?.course_code || ''} onChange={event => {
          const next = sessions.find(item => item.course_code === event.target.value);
          setSelectedKey(next ? sessionKey(next) : '');
          setExpanded(null);
        }}><option value="">Pilih course</option>{grouped.map(course => <option key={course.code} value={course.code}>{course.title}</option>)}</select></label>
        <label className="min-w-0 text-xs font-semibold text-muted">Sesi<select className={`${fieldClass} mt-1.5 min-w-0`} value={selectedKey} onChange={event => { setSelectedKey(event.target.value); setExpanded(null); }}><option value="">Pilih sesi</option>{sessions.filter(item => item.course_code === selectedSession?.course_code).map(item => <option key={sessionKey(item)} value={sessionKey(item)}>{item.session_title}</option>)}</select></label>
      </div>
      <button type="button" onClick={toggleSession} disabled={!selectedSession || busy} className={`min-h-10 rounded-lg px-4 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-50 ${selectedSession?.is_open ? 'border border-outline/60 bg-white text-secondary hover:bg-surface-low dark:bg-slate-900' : 'bg-primary text-white hover:opacity-90'}`}>
        {selectedSession?.is_open ? 'Tutup forum sesi' : 'Buka forum sesi'}
      </button>
    </section>

    {!selectedSession ? <div className="rounded-xl border border-dashed border-outline/60 bg-white p-10 text-center text-sm text-muted">Belum ada course dan sesi yang dapat dikelola.</div> : <>
      <div className="mb-4 flex min-w-0 flex-wrap items-center justify-between gap-3">
        <div className="min-w-0"><h2 className="break-words font-semibold text-on-surface">{selectedSession.course_title}</h2><p className="mt-1 break-words text-sm text-muted">{selectedSession.session_title}</p></div>
        <span className={`shrink-0 rounded-md px-2.5 py-1 text-xs font-semibold ${selectedSession.is_open ? 'bg-green-50 text-green-800 dark:bg-green-950/40 dark:text-green-300' : 'bg-surface-low text-muted'}`}>{selectedSession.is_open ? 'Forum dibuka' : 'Forum ditutup'}</span>
      </div>

      <form onSubmit={createThread} className="mb-5 rounded-xl border border-outline/50 bg-white p-4 sm:p-5">
        <h3 className="text-sm font-semibold">Mulai diskusi</h3>
        <input name="title" required minLength="3" maxLength="200" placeholder="Judul diskusi" className={`${fieldClass} mt-3`} />
        <textarea name="body" required maxLength="2000" rows="3" placeholder="Tulis pertanyaan, pengumuman, atau tanggapan untuk sesi ini..." className={`${messageClass} mt-2`} />
        <div className="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <label className="text-xs text-muted">Lampirkan gambar (maks. 5 MB setelah kompresi; GIF animasi tetap asli)<input name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" className="mt-1 block max-w-full text-xs" /></label>
          <button disabled={busy} className="h-10 rounded-lg bg-primary px-5 text-sm font-semibold text-white disabled:opacity-50">Kirim diskusi</button>
        </div>
      </form>

      <div className="space-y-4">
        {threads.length ? threads.map(thread => <article key={thread.thread_id} className="min-w-0 rounded-xl border border-outline/50 bg-white p-4 sm:p-5">
          <div className="flex min-w-0 flex-wrap items-start justify-between gap-3">
            <div className="min-w-0">
              <div className="flex min-w-0 items-start gap-3"><span className="grid size-9 shrink-0 place-items-center rounded-full bg-surface-low text-sm font-semibold text-secondary">{String(thread.author_name || '?').trim().slice(0, 1).toLocaleUpperCase('id')}</span><div className="min-w-0"><div className="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1"><h3 className="min-w-0 break-words font-semibold text-on-surface">{thread.title}</h3><RoleBadge role={thread.author_role} /></div><p className="mt-1 break-words text-xs text-muted">{thread.author_name} · {formatDate(thread.created_at)}</p></div></div>
            </div>
            <select aria-label={`Status ${thread.title}`} value={thread.status} onChange={event => updateThreadStatus(thread, event.target.value)} className="h-9 w-full rounded-lg border border-outline/60 bg-white px-2 text-xs sm:w-auto dark:bg-slate-900">
              <option value="visible">Terlihat · terbuka</option><option value="locked">Terlihat · ditutup</option><option value="hidden">Disembunyikan</option>
            </select>
          </div>
          <p className="mt-4 whitespace-pre-wrap break-words text-sm leading-6 text-secondary">{thread.body}</p>
          {thread.attachment_path && <img crossOrigin="use-credentials" src={`${API_BASE}${thread.attachment_path}`} alt="Lampiran diskusi" loading="lazy" className="mt-3 max-h-96 w-full max-w-2xl rounded-lg border border-outline/40 object-contain" />}
          <div className="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-outline/30 pt-3 text-sm">
            <button type="button" onClick={() => toggleLike('thread', thread.thread_id)} className={`min-h-9 rounded-md px-2 ${thread.liked_by_me ? 'font-semibold text-primary' : 'text-muted hover:bg-surface-low hover:text-primary'}`}>♥ {thread.likes_count || 0} suka</button>
            <button type="button" onClick={() => {
              const next = expanded === thread.thread_id ? null : thread.thread_id;
              setExpanded(next);
              if (next && !details[next]) loadDetails(next);
            }} className="text-muted hover:text-on-surface">{thread.replies_count || 0} balasan</button>
          </div>
          {expanded === thread.thread_id && <div className="mt-3 border-t border-outline/30 pt-3">
            {details[thread.thread_id]?.replies?.map(reply => <div key={reply.reply_id} className="border-b border-outline/20 py-3 last:border-0">
              <div className="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1"><strong className="break-words text-sm">{reply.author_name}</strong><RoleBadge role={reply.author_role} /><time className="text-xs text-muted">{formatDate(reply.created_at)}</time></div>
              <p className="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-secondary">{reply.body}</p>
              {reply.attachment_path && <img crossOrigin="use-credentials" src={`${API_BASE}${reply.attachment_path}`} alt="Lampiran balasan" loading="lazy" className="mt-2 max-h-80 w-full max-w-xl rounded-lg border border-outline/40 object-contain" />}
              <button type="button" onClick={() => toggleLike('reply', reply.reply_id, thread.thread_id)} className={`mt-2 min-h-9 rounded-md px-2 text-xs ${reply.liked_by_me ? 'font-semibold text-primary' : 'text-muted hover:bg-surface-low hover:text-primary'}`}>♥ {reply.likes_count || 0} suka</button>
            </div>)}
            {selectedSession.is_open && thread.status === 'visible' ? <form onSubmit={event => replyToThread(event, thread.thread_id)} className="mt-3 border-t border-outline/30 pt-3">
              <textarea name="body" required maxLength="2000" rows="2" placeholder="Tulis balasan..." className={messageClass} />
              <div className="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><label className="min-w-0 text-xs text-muted">Lampiran gambar (maks. 5 MB setelah kompresi; GIF animasi tetap asli)<input name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" className="mt-1 block max-w-full text-xs" /></label><button disabled={busy} className="min-h-10 w-full rounded-lg bg-primary px-4 text-xs font-semibold text-white disabled:opacity-50 sm:w-auto">Kirim balasan</button></div>
            </form> : <p className="mt-3 text-xs text-muted">Balasan dinonaktifkan karena forum atau diskusi ditutup.</p>}
          </div>}
        </article>) : <div className="rounded-xl border border-dashed border-outline/60 bg-white p-8 text-center text-sm text-muted">Belum ada diskusi untuk sesi ini.</div>}
      </div>
    </>}
  </div>;
}

function sessionKey(item) { return item ? `${item.course_code}:${item.session_code}` : ''; }
function formatDate(value) { const date = new Date(value); return Number.isNaN(date.valueOf()) ? '' : date.toLocaleString('id-ID'); }
function RoleBadge({ role }) {
  const label = role === 'admin' ? 'Admin' : role === 'teacher' ? 'Teacher' : 'Student';
  const style = role === 'admin'
    ? 'bg-surface-low text-secondary'
    : role === 'teacher'
      ? 'bg-red-50 text-primary dark:bg-red-950/40 dark:text-red-300'
      : 'bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300';
  return <span className={`shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold ${style}`}>{label}</span>;
}
