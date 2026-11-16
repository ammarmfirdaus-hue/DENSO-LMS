import { useEffect, useMemo, useRef, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import {
  ChevronDown,
  ChevronUp,
  Heart,
  ImagePlus,
  Lock,
  LockOpen,
  MessageSquare,
  MessagesSquare,
  Plus,
  Search,
  Send,
  X
} from 'lucide-react';
import { api, API_BASE } from '../../api/client';
import { compressForumImage } from '../../utils/compress-forum-image';
import { hasPermission } from '../../utils/access-control';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import SearchableSelect from '../../components/SearchableSelect';
import StatusBadge from '../../components/StatusBadge';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

const FIELD_CLASS =
  'h-10 w-full rounded-lg border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';
const TEXTAREA_CLASS =
  'w-full resize-y rounded-lg border border-outline bg-surface-lowest p-3 text-sm leading-6 text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

const THREAD_STATUS_META = {
  visible: { badge: 'open', label: 'Terbuka' },
  locked: { badge: 'locked', label: 'Ditutup' },
  hidden: { badge: 'hidden', label: 'Disembunyikan' }
};

/**
 * Halaman Forum Diskusi untuk manajemen sesi forum dan interaksi realtime bersama peserta.
 */
export default function ForumPage() {
  const user = useOutletContext();
  const canParticipate = hasPermission(user, 'forum.participate');
  const canModerate = hasPermission(user, 'forum.moderate');
  const [sessions, setSessions] = useState([]);
  const [selectedKey, setSelectedKey] = useState('');
  const [threads, setThreads] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [composerOpen, setComposerOpen] = useState(false);
  const [composerPreview, setComposerPreview] = useState('');
  const [details, setDetails] = useState({});
  const [expanded, setExpanded] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);

  const selectedSession = sessions.find((item) => sessionKey(item) === selectedKey);
  const expandedRef = useRef(expanded);
  useEffect(() => {
    expandedRef.current = expanded;
  }, [expanded]);

  useEffect(() => {
    return () => {
      if (composerPreview) URL.revokeObjectURL(composerPreview);
    };
  }, [composerPreview]);

  const filteredThreads = useMemo(() => {
    const q = searchQuery.trim().toLowerCase();
    return threads.filter((t) => {
      if (statusFilter === 'unanswered' && (Number(t.replies_count || 0) > 0 || t.status !== 'visible')) {
        return false;
      }
      if (statusFilter !== 'all' && statusFilter !== 'unanswered' && t.status !== statusFilter) {
        return false;
      }
      if (!q) return true;
      return (
        String(t.title || '').toLowerCase().includes(q) ||
        String(t.body || '').toLowerCase().includes(q) ||
        String(t.author_name || '').toLowerCase().includes(q)
      );
    });
  }, [threads, searchQuery, statusFilter]);

  const threadPage = usePagination(filteredThreads, 10);

  const summary = useMemo(() => {
    const total = threads.length;
    const unanswered = threads.filter((t) => t.status === 'visible' && Number(t.replies_count || 0) === 0).length;
    const locked = threads.filter((t) => t.status === 'locked').length;
    return { total, unanswered, locked };
  }, [threads]);

  async function loadSessions() {
    try {
      const result = await api('/forum/management/courses');
      const next = result.sessions || [];
      setSessions(next);
      setSelectedKey((current) =>
        next.some((item) => sessionKey(item) === current) ? current : sessionKey(next[0]) || ''
      );
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Sesi Forum', e.message);
    }
  }

  async function loadThreads() {
    if (!selectedSession) {
      setThreads([]);
      return;
    }
    try {
      const query = new URLSearchParams({
        courseCode: selectedSession.course_code,
        sessionCode: String(selectedSession.session_code)
      });
      const result = await api(`/forum/management/threads?${query}`);
      setThreads(result.threads || []);
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Diskusi Forum', e.message);
    }
  }

  async function loadDetails(threadId) {
    try {
      const result = await api(`/forum/threads/${threadId}`);
      setDetails((current) => ({ ...current, [threadId]: result }));
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Detail Diskusi', e.message);
    }
  }

  useEffect(() => {
    loadSessions();
  }, []);

  useEffect(() => {
    loadThreads();
  }, [selectedKey]);

  useEffect(() => {
    if (!selectedSession) return undefined;
    const activeSession = selectedSession;
    const query = new URLSearchParams({
      courseCode: activeSession.course_code,
      sessionCode: String(activeSession.session_code)
    });
    const events = new EventSource(`${API_BASE}/forum/events?${query}`, {
      withCredentials: true
    });
    events.addEventListener('forum', (event) => {
      loadThreads();
      if (expandedRef.current) loadDetails(expandedRef.current);
      if (JSON.parse(event.data || '{}').type === 'session_status') loadSessions();
    });
    events.addEventListener('ready', () => {
      loadThreads();
      if (expandedRef.current) loadDetails(expandedRef.current);
    });
    events.onerror = () => {
      /* EventSource otomatis mencoba koneksi ulang selama sesi aktif. */
    };
    return () => events.close();
  }, [selectedSession?.course_code, selectedSession?.session_code]);

  async function toggleSession() {
    if (!selectedSession) return;
    setBusy(true);
    try {
      const next = !Boolean(selectedSession.is_open);
      await api(
        `/forum/management/sessions/${encodeURIComponent(selectedSession.course_code)}/${encodeURIComponent(selectedSession.session_code)}`,
        {
          method: 'PATCH',
          body: JSON.stringify({ isOpen: next })
        }
      );
      setSessions((current) =>
        current.map((item) =>
          sessionKey(item) === selectedKey ? { ...item, is_open: next } : item
        )
      );
      const msg = next
        ? 'Forum sesi dibuka.'
        : 'Forum sesi ditutup. Student tetap dapat membaca diskusi yang sudah ada.';
      setNotice(msg);
      showAlert.success(next ? 'Forum Sesi Dibuka' : 'Forum Sesi Ditutup', msg);
      window.setTimeout(() => setNotice(''), 3500);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengubah Status Forum', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function createThread(event) {
    event.preventDefault();
    if (!selectedSession) return;
    const form = event.currentTarget;
    setBusy(true);
    try {
      const data = new FormData(form);
      const image = data.get('image');
      if (image instanceof File && image.size > 0) {
        data.set('image', await compressForumImage(image));
      }
      const body =
        image instanceof File && image.size > 0
          ? data
          : JSON.stringify({
              courseCode: selectedSession.course_code,
              sessionCode: String(selectedSession.session_code),
              title: data.get('title'),
              body: data.get('body')
            });
      if (body instanceof FormData) {
        body.set('courseCode', selectedSession.course_code);
        body.set('sessionCode', String(selectedSession.session_code));
      }
      await api('/forum/management/threads', { method: 'POST', body });
      form.reset();
      if (composerPreview) {
        URL.revokeObjectURL(composerPreview);
        setComposerPreview('');
      }
      setComposerOpen(false);
      setNotice('Diskusi berhasil dikirim.');
      showAlert.success('Diskusi Terkirim', 'Topik diskusi baru berhasil dipublikasikan.');
      await loadThreads();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengirim Diskusi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function replyToThread(event, threadId) {
    event.preventDefault();
    const form = event.currentTarget;
    setBusy(true);
    try {
      const data = new FormData(form);
      const image = data.get('image');
      if (image instanceof File && image.size > 0) {
        data.set('image', await compressForumImage(image));
      }
      const body =
        image instanceof File && image.size > 0
          ? data
          : JSON.stringify({ body: data.get('body') });
      await api(`/forum/threads/${threadId}/replies`, { method: 'POST', body });
      form.reset();
      showAlert.success('Balasan Terkirim', 'Balasan Anda berhasil ditambahkan ke diskusi.');
      await Promise.all([loadDetails(threadId), loadThreads()]);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengirim Balasan', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function toggleLike(targetType, targetId, parentId) {
    try {
      const result = await api('/forum/likes', {
        method: 'POST',
        body: JSON.stringify({ targetType, targetId })
      });
      if (targetType === 'thread') {
        setThreads((current) =>
          current.map((item) =>
            item.thread_id === targetId
              ? { ...item, likes_count: result.likes_count, liked_by_me: result.liked }
              : item
          )
        );
      } else {
        setDetails((current) => {
          const detail = current[parentId];
          if (!detail) return current;
          return {
            ...current,
            [parentId]: {
              ...detail,
              replies: detail.replies.map((item) =>
                item.reply_id === targetId
                  ? { ...item, likes_count: result.likes_count, liked_by_me: result.liked }
                  : item
              )
            }
          };
        });
      }
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memproses Apresiasi', e.message);
    }
  }

  async function updateThreadStatus(thread, status) {
    try {
      await api(`/forum/${thread.thread_id}/status`, {
        method: 'PATCH',
        body: JSON.stringify({ status })
      });
      showAlert.success(
        'Status Diskusi Diperbarui',
        `Diskusi berhasil diubah ke status "${THREAD_STATUS_META[status]?.label || status}".`
      );
      await loadThreads();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengubah Status Diskusi', e.message);
    }
  }

  const grouped = useMemo(() => {
    const courses = new Map();
    sessions.forEach((item) => {
      const current = courses.get(item.course_code) || { title: item.course_title, sessions: [] };
      current.sessions.push(item);
      courses.set(item.course_code, current);
    });
    return [...courses.entries()].map(([code, value]) => ({ code, ...value }));
  }, [sessions]);

  return (
    <div className="min-w-0 space-y-6">
      <PageHeader
        eyebrow="Ruang Diskusi"
        title="Forum Diskusi"
        description="Dampingi peserta belajar, jawab pertanyaan tiap sesi, dan kelola keterbukaan ruang diskusi dengan nyaman."
        actions={
          selectedSession && (
            <StatusBadge
              status={selectedSession.is_open ? 'active' : 'inactive'}
              label={selectedSession.is_open ? 'Forum Sesi Dibuka' : 'Forum Sesi Ditutup'}
              dot
            />
          )
        }
      />

      {error && (
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          {error}
        </div>
      )}

      {notice && (
        <div
          role="status"
          className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
          {notice}
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-[310px_minmax(0,1fr)] lg:items-start">
        {/* Sidebar Konteks Course & Sesi */}
        <aside className="space-y-4 lg:sticky lg:top-20">
          <Card title="Pilih Ruang Sesi" subtitle="Tentukan course dan sesi yang ingin dipantau">
            <div className="space-y-3.5">
              <label className="block text-xs font-semibold text-on-surface-variant">
                Course
                <SearchableSelect
                  title="Pilih Course Forum"
                  className={`${FIELD_CLASS} mt-1.5`}
                  value={selectedSession?.course_code || ''}
                  onChange={(event) => {
                    const next = sessions.find((item) => item.course_code === event.target.value);
                    setSelectedKey(next ? sessionKey(next) : '');
                    setExpanded(null);
                  }}
                  options={grouped.map((course) => ({ value: course.code, label: course.title }))}
                  placeholder="Cari course..."
                />
              </label>

              <label className="block text-xs font-semibold text-on-surface-variant">
                Sesi Pembelajaran
                <SearchableSelect
                  title="Pilih Sesi Forum"
                  className={`${FIELD_CLASS} mt-1.5`}
                  value={selectedKey}
                  onChange={(event) => {
                    setSelectedKey(event.target.value);
                    setExpanded(null);
                  }}
                  options={sessions
                    .filter((item) => item.course_code === selectedSession?.course_code)
                    .map((item) => ({ value: sessionKey(item), label: item.session_title }))}
                  placeholder="Cari sesi..."
                />
              </label>

              {selectedSession && (
                <div className="rounded-lg border border-outline/60 bg-surface-low/60 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                  <div className="grid grid-cols-3 gap-2 text-center">
                    <div>
                      <span className="block text-[11px] text-muted">Topik</span>
                      <strong className="font-mono text-base font-bold text-on-surface">
                        {summary.total}
                      </strong>
                    </div>
                    <div>
                      <span className="block text-[11px] text-muted">Belum Dibalas</span>
                      <strong className="font-mono text-base font-bold text-amber-600 dark:text-amber-400">
                        {summary.unanswered}
                      </strong>
                    </div>
                    <div>
                      <span className="block text-[11px] text-muted">Ditutup</span>
                      <strong className="font-mono text-base font-bold text-on-surface-variant">
                        {summary.locked}
                      </strong>
                    </div>
                  </div>
                </div>
              )}

              {canModerate && selectedSession && (
                <Button
                  type="button"
                  variant={selectedSession.is_open ? 'outline' : 'primary'}
                  disabled={busy}
                  onClick={toggleSession}
                  className="w-full"
                  icon={
                    selectedSession.is_open ? (
                      <Lock className="size-4" />
                    ) : (
                      <LockOpen className="size-4" />
                    )
                  }
                >
                  {selectedSession.is_open ? 'Tutup Forum Sesi' : 'Buka Forum Sesi'}
                </Button>
              )}
            </div>
          </Card>

          {/* Filter Cepat */}
          {selectedSession && (
            <Card title="Saring Diskusi" subtitle="Fokus ke topik yang perlu perhatian">
              <div className="flex flex-col gap-1.5">
                {[
                  { key: 'all', label: 'Semua Topik', count: summary.total },
                  { key: 'unanswered', label: 'Belum Dijawab', count: summary.unanswered },
                  { key: 'visible', label: 'Terbuka', count: threads.filter((t) => t.status === 'visible').length },
                  { key: 'locked', label: 'Ditutup', count: summary.locked },
                  { key: 'hidden', label: 'Disembunyikan', count: threads.filter((t) => t.status === 'hidden').length }
                ].map((tab) => {
                  const active = statusFilter === tab.key;
                  return (
                    <button
                      key={tab.key}
                      type="button"
                      onClick={() => setStatusFilter(tab.key)}
                      className={`flex items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold transition ${
                        active
                          ? 'bg-primary/10 text-primary'
                          : 'text-on-surface-variant hover:bg-surface-low dark:hover:bg-slate-800/70'
                      }`}
                    >
                      <span>{tab.label}</span>
                      <span className="rounded-full bg-surface-lowest px-2 py-0.5 font-mono text-[11px] text-muted dark:bg-slate-900">
                        {tab.count}
                      </span>
                    </button>
                  );
                })}
              </div>
            </Card>
          )}
        </aside>

        {/* Area Feed Utama */}
        <section className="min-w-0 space-y-4">
          {!selectedSession ? (
            <Card className="border-dashed py-14 text-center">
              <MessagesSquare className="mx-auto mb-3 size-10 text-muted opacity-50" />
              <p className="text-sm font-semibold text-on-surface">Belum ada ruang sesi tersedia</p>
              <p className="mt-1 text-xs text-muted">
                Course dan sesi yang dapat Anda kelola akan tampil di sini.
              </p>
            </Card>
          ) : (
            <>
              {/* Header Sesi + Cari Topik + Tombol Mulai Diskusi */}
              <div className="dls-card-surface rounded-xl p-4 sm:p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div className="min-w-0">
                    <p className="text-[11px] font-bold uppercase tracking-wider text-primary">
                      {selectedSession.course_code}
                    </p>
                    <h2 className="mt-0.5 truncate text-base font-bold text-on-surface sm:text-lg">
                      {selectedSession.course_title}
                    </h2>
                    <p className="truncate text-xs text-muted">{selectedSession.session_title}</p>
                  </div>

                  {canParticipate && (
                    <Button
                      type="button"
                      variant={composerOpen ? 'outline' : 'primary'}
                      onClick={() => setComposerOpen((v) => !v)}
                      icon={composerOpen ? <X className="size-4" /> : <Plus className="size-4" />}
                    >
                      {composerOpen ? 'Tutup Form' : 'Mulai Diskusi Baru'}
                    </Button>
                  )}
                </div>

                <div className="relative mt-4">
                  <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
                  <input
                    type="search"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Cari judul, isi diskusi, atau nama pengirim..."
                    className={`${FIELD_CLASS} pl-9`}
                  />
                </div>
              </div>

              {/* Composer Diskusi Baru (Collapsible agar feed tetap lega) */}
              {canParticipate && composerOpen && (
                <div id="new-thread">
                  <Card
                    title="Tulis Topik Diskusi Baru"
                    subtitle="Bagikan pengumuman, panduan belajar, atau buka sesi tanya jawab."
                  >
                    <form onSubmit={createThread} className="space-y-3">
                      <input
                        name="title"
                        required
                        minLength="3"
                        maxLength="200"
                        placeholder="Judul topik yang jelas dan mudah dipahami..."
                        className={FIELD_CLASS}
                      />
                      <textarea
                        name="body"
                        required
                        maxLength="2000"
                        rows="3"
                        placeholder="Tulis isi pesan atau arahan untuk peserta pada sesi ini..."
                        className={TEXTAREA_CLASS}
                      />

                      {composerPreview && (
                        <div className="relative inline-block">
                          <img
                            src={composerPreview}
                            alt="Pratinjau lampiran"
                            className="max-h-44 rounded-lg border border-outline/60 object-contain"
                          />
                          <button
                            type="button"
                            onClick={() => {
                              URL.revokeObjectURL(composerPreview);
                              setComposerPreview('');
                            }}
                            className="absolute -right-2 -top-2 grid size-6 place-items-center rounded-full bg-slate-900 text-white shadow"
                            aria-label="Hapus pratinjau"
                          >
                            <X className="size-3.5" />
                          </button>
                        </div>
                      )}

                      <div className="flex flex-col gap-3 border-t border-outline/40 pt-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                        <label className="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-on-surface-variant hover:text-primary">
                          <ImagePlus className="size-4 text-primary" />
                          <span>Lampirkan gambar (maks. 5 MB)</span>
                          <input
                            name="image"
                            type="file"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            onChange={(e) => {
                              const file = e.target.files?.[0];
                              if (composerPreview) URL.revokeObjectURL(composerPreview);
                              setComposerPreview(file ? URL.createObjectURL(file) : '');
                            }}
                            className="sr-only"
                          />
                        </label>

                        <div className="flex items-center justify-end gap-2">
                          <Button
                            type="button"
                            variant="outline"
                            onClick={() => setComposerOpen(false)}
                          >
                            Batal
                          </Button>
                          <Button
                            type="submit"
                            variant="primary"
                            disabled={busy}
                            loading={busy}
                            icon={<Send className="size-4" />}
                          >
                            Publikasikan Topik
                          </Button>
                        </div>
                      </div>
                    </form>
                  </Card>
                </div>
              )}

              {/* Daftar Thread */}
              <div id="forum-threads" className="space-y-3.5">
                {filteredThreads.length ? (
                  threadPage.pagedItems.map((thread) => {
                    const isExpanded = expanded === thread.thread_id;
                    const statusMeta = THREAD_STATUS_META[thread.status] || THREAD_STATUS_META.visible;
                    const needsReply =
                      thread.status === 'visible' && Number(thread.replies_count || 0) === 0;

                    return (
                      <article
                        key={thread.thread_id}
                        className="dls-card-surface dls-card-interactive min-w-0 rounded-xl p-5"
                      >
                        <div className="flex min-w-0 flex-wrap items-start justify-between gap-3">
                          <div className="flex min-w-0 items-start gap-3">
                            <AuthorAvatar name={thread.author_name} role={thread.author_role} />
                            <div className="min-w-0">
                              <div className="flex min-w-0 flex-wrap items-center gap-2">
                                <h3 className="min-w-0 break-words text-[15px] font-bold text-on-surface">
                                  {thread.title}
                                </h3>
                                <StatusBadge
                                  status={statusMeta.badge}
                                  label={statusMeta.label}
                                  dot
                                />
                                {needsReply && (
                                  <StatusBadge status="pending" label="Belum dijawab" />
                                )}
                              </div>
                              <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">
                                <span className="font-semibold text-on-surface-variant">
                                  {thread.author_name}
                                </span>
                                <RoleBadge role={thread.author_role} />
                                <span>·</span>
                                <time title={formatDate(thread.created_at)}>
                                  {formatRelativeDate(thread.created_at)}
                                </time>
                              </div>
                            </div>
                          </div>

                          {canModerate && (
                            <select
                              aria-label={`Status ${thread.title}`}
                              value={thread.status}
                              onChange={(event) => updateThreadStatus(thread, event.target.value)}
                              className="h-9 w-full rounded-lg border border-outline bg-surface-lowest px-2.5 text-xs font-medium text-on-surface outline-none focus:border-primary sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                            >
                              <option value="visible">Terlihat · Terbuka</option>
                              <option value="locked">Terlihat · Ditutup</option>
                              <option value="hidden">Disembunyikan</option>
                            </select>
                          )}
                        </div>

                        <p className="mt-3.5 whitespace-pre-wrap break-words text-sm leading-relaxed text-on-surface-variant">
                          {thread.body}
                        </p>

                        {thread.attachment_path && (
                          <img
                            crossOrigin="use-credentials"
                            src={`${API_BASE}${thread.attachment_path}`}
                            alt="Lampiran diskusi"
                            loading="lazy"
                            className="mt-3 max-h-80 w-full max-w-xl rounded-lg border border-outline/40 object-contain dark:border-slate-700"
                          />
                        )}

                        <div className="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-outline/40 pt-3 text-xs dark:border-slate-800">
                          <div className="flex flex-wrap items-center gap-2">
                            <button
                              type="button"
                              data-dls-action-ignore="true"
                              disabled={!canParticipate}
                              onClick={() => canParticipate && toggleLike('thread', thread.thread_id)}
                              className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-semibold transition ${
                                !canParticipate
                                  ? 'cursor-not-allowed text-muted/60'
                                  : thread.liked_by_me
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-muted hover:bg-surface-low hover:text-primary dark:hover:bg-slate-800'
                              }`}
                            >
                              <Heart
                                className={`size-3.5 ${thread.liked_by_me ? 'fill-current' : ''}`}
                              />
                              <span>{thread.likes_count || 0} Suka</span>
                            </button>

                            <button
                              type="button"
                              data-dls-action-ignore="true"
                              onClick={() => {
                                const next = isExpanded ? null : thread.thread_id;
                                setExpanded(next);
                                if (next && !details[next]) loadDetails(next);
                              }}
                              className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-semibold transition ${
                                isExpanded
                                  ? 'bg-secondary/10 text-secondary dark:bg-slate-800 dark:text-slate-100'
                                  : 'text-muted hover:bg-surface-low hover:text-on-surface dark:hover:bg-slate-800'
                              }`}
                            >
                              <MessageSquare className="size-3.5" />
                              <span>{thread.replies_count || 0} Balasan</span>
                              {isExpanded ? (
                                <ChevronUp className="size-3.5" />
                              ) : (
                                <ChevronDown className="size-3.5" />
                              )}
                            </button>
                          </div>
                        </div>

                        {isExpanded && (
                          <div className="mt-4 space-y-3 border-t border-outline/40 pt-4 dark:border-slate-800">
                            <div className="space-y-3 border-l-2 border-outline/60 pl-4 dark:border-slate-700">
                              {details[thread.thread_id]?.replies?.length ? (
                                details[thread.thread_id].replies.map((reply) => (
                                  <div
                                    key={reply.reply_id}
                                    className="rounded-lg border border-outline/40 bg-surface-low/50 p-3.5 dark:border-slate-800 dark:bg-slate-800/40"
                                  >
                                    <div className="flex min-w-0 flex-wrap items-center justify-between gap-2">
                                      <div className="flex min-w-0 flex-wrap items-center gap-2">
                                        <strong className="break-words text-xs font-bold text-on-surface">
                                          {reply.author_name}
                                        </strong>
                                        <RoleBadge role={reply.author_role} />
                                      </div>
                                      <time
                                        className="text-[11px] text-muted"
                                        title={formatDate(reply.created_at)}
                                      >
                                        {formatRelativeDate(reply.created_at)}
                                      </time>
                                    </div>
                                    <p className="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-on-surface-variant">
                                      {reply.body}
                                    </p>
                                    {reply.attachment_path && (
                                      <img
                                        crossOrigin="use-credentials"
                                        src={`${API_BASE}${reply.attachment_path}`}
                                        alt="Lampiran balasan"
                                        loading="lazy"
                                        className="mt-2 max-h-72 w-full max-w-lg rounded-lg border border-outline/40 object-contain dark:border-slate-700"
                                      />
                                    )}
                                    <button
                                      type="button"
                                      data-dls-action-ignore="true"
                                      disabled={!canParticipate}
                                      onClick={() =>
                                        canParticipate &&
                                        toggleLike('reply', reply.reply_id, thread.thread_id)
                                      }
                                      className={`mt-2 inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold transition ${
                                        !canParticipate
                                          ? 'cursor-not-allowed text-muted/60'
                                          : reply.liked_by_me
                                            ? 'bg-primary/10 text-primary'
                                            : 'text-muted hover:bg-surface-low hover:text-primary dark:hover:bg-slate-700'
                                      }`}
                                    >
                                      <Heart
                                        className={`size-3 ${reply.liked_by_me ? 'fill-current' : ''}`}
                                      />
                                      <span>{reply.likes_count || 0} Suka</span>
                                    </button>
                                  </div>
                                ))
                              ) : (
                                <p className="text-xs italic text-muted">
                                  Belum ada balasan. Jadilah yang pertama menanggapi topik ini.
                                </p>
                              )}
                            </div>

                            {canParticipate && selectedSession.is_open && thread.status === 'visible' ? (
                              <form
                                onSubmit={(event) => replyToThread(event, thread.thread_id)}
                                className="rounded-lg border border-outline/50 bg-surface-lowest p-3 dark:border-slate-800 dark:bg-slate-900/50"
                              >
                                <textarea
                                  name="body"
                                  required
                                  maxLength="2000"
                                  rows="2"
                                  placeholder="Tulis balasan yang jelas dan membantu peserta..."
                                  className={TEXTAREA_CLASS}
                                />
                                <div className="mt-2.5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                  <label className="min-w-0 text-xs text-muted">
                                    Lampiran gambar (maks. 5 MB)
                                    <input
                                      name="image"
                                      type="file"
                                      accept="image/jpeg,image/png,image/gif,image/webp"
                                      className="mt-1 block max-w-full text-xs text-on-surface-variant file:mr-2.5 file:rounded-md file:border-0 file:bg-primary/10 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-primary"
                                    />
                                  </label>
                                  <Button
                                    type="submit"
                                    size="sm"
                                    variant="primary"
                                    disabled={busy}
                                    icon={<Send className="size-3.5" />}
                                  >
                                    Kirim Balasan
                                  </Button>
                                </div>
                              </form>
                            ) : (
                              <p className="pt-1 text-xs text-muted">
                                Balasan dinonaktifkan karena forum atau diskusi sedang ditutup.
                              </p>
                            )}
                          </div>
                        )}
                      </article>
                    );
                  })
                ) : (
                  <Card className="border-dashed py-12 text-center">
                    <MessagesSquare className="mx-auto mb-2.5 size-9 text-muted opacity-50" />
                    <p className="text-sm font-semibold text-on-surface">
                      {threads.length ? 'Tidak ada topik yang cocok dengan filter' : 'Belum ada diskusi untuk sesi ini'}
                    </p>
                    <p className="mt-1 text-xs text-muted">
                      {threads.length
                        ? 'Coba ubah kata kunci pencarian atau pilih filter status lainnya.'
                        : 'Klik "Mulai Diskusi Baru" untuk menyapa peserta pada sesi ini.'}
                    </p>
                  </Card>
                )}
              </div>

              {Boolean(filteredThreads.length) && (
                <Card noPadding className="overflow-hidden">
                  <Pagination
                    {...threadPage}
                    onPageChange={threadPage.setPage}
                    onPageSizeChange={threadPage.setPageSize}
                    pageSizes={[10, 20, 50]}
                  />
                </Card>
              )}
            </>
          )}
        </section>
      </div>
    </div>
  );
}

function sessionKey(item) {
  return item ? `${item.course_code}:${item.session_code}` : '';
}

function formatDate(value) {
  const date = new Date(value);
  return Number.isNaN(date.valueOf()) ? '' : date.toLocaleString('id-ID');
}

function formatRelativeDate(value) {
  const date = new Date(value);
  if (Number.isNaN(date.valueOf())) return '';
  const diffSec = Math.floor((Date.now() - date.getTime()) / 1000);
  if (diffSec < 60) return 'Baru saja';
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `${diffMin} menit lalu`;
  const diffHour = Math.floor(diffMin / 60);
  if (diffHour < 24) return `${diffHour} jam lalu`;
  const diffDay = Math.floor(diffHour / 24);
  if (diffDay < 7) return `${diffDay} hari lalu`;
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function AuthorAvatar({ name, role }) {
  const tone =
    role === 'admin'
      ? 'bg-red-50 text-primary border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-900/60'
      : role === 'teacher'
        ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900/60'
        : role === 'reviewer'
          ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900/60'
          : 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700';
  return (
    <span
      className={`grid size-10 shrink-0 place-items-center rounded-full border text-sm font-bold ${tone}`}
      aria-hidden="true"
    >
      {String(name || '?').trim().slice(0, 1).toLocaleUpperCase('id')}
    </span>
  );
}

function RoleBadge({ role }) {
  const label =
    role === 'admin'
      ? 'Admin'
      : role === 'teacher'
        ? 'Teacher'
        : role === 'reviewer'
          ? 'Reviewer'
          : 'Student';
  return <StatusBadge status={role || 'student'} label={label} />;
}
