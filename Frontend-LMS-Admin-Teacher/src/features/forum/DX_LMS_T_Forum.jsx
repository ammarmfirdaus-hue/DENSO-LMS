import { useEffect, useMemo, useRef, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import { Heart, Lock, LockOpen, MessageSquare, MessagesSquare, Send } from 'lucide-react';
import { api, API_BASE } from '../../api/client';
import { compressForumImage } from '../../utils/compress-forum-image';
import { hasPermission } from '../../utils/access-control';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

const FIELD_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';
const TEXTAREA_CLASS =
  'w-full resize-y rounded-md border border-outline bg-surface-lowest p-3 text-sm leading-6 text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

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
  const threadPage = usePagination(threads, 10);
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
        `Diskusi berhasil diubah ke status "${status}".`
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
        eyebrow="Komunitas Pembelajaran"
        title="Forum Diskusi"
        description="Kelola diskusi per course dan sesi, kendalikan status keterbukaan sesi, serta tanggapi pertanyaan peserta."
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

      {/* Pemilih Course & Sesi */}
      <Card>
        <div className="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div className="grid min-w-0 flex-1 gap-3 sm:grid-cols-2">
            <label className="min-w-0 text-xs font-semibold text-on-surface-variant">
              Course
              <select
                className={`${FIELD_CLASS} mt-1.5 min-w-0`}
                value={selectedSession?.course_code || ''}
                onChange={(event) => {
                  const next = sessions.find((item) => item.course_code === event.target.value);
                  setSelectedKey(next ? sessionKey(next) : '');
                  setExpanded(null);
                }}
              >
                <option value="">Pilih course</option>
                {grouped.map((course) => (
                  <option key={course.code} value={course.code}>
                    {course.title}
                  </option>
                ))}
              </select>
            </label>

            <label className="min-w-0 text-xs font-semibold text-on-surface-variant">
              Sesi
              <select
                className={`${FIELD_CLASS} mt-1.5 min-w-0`}
                value={selectedKey}
                onChange={(event) => {
                  setSelectedKey(event.target.value);
                  setExpanded(null);
                }}
              >
                <option value="">Pilih sesi</option>
                {sessions
                  .filter((item) => item.course_code === selectedSession?.course_code)
                  .map((item) => (
                    <option key={sessionKey(item)} value={sessionKey(item)}>
                      {item.session_title}
                    </option>
                  ))}
              </select>
            </label>
          </div>

          {canModerate && (
            <Button
              type="button"
              variant={selectedSession?.is_open ? 'outline' : 'primary'}
              disabled={!selectedSession || busy}
              onClick={toggleSession}
              icon={
                selectedSession?.is_open ? (
                  <Lock className="size-4" />
                ) : (
                  <LockOpen className="size-4" />
                )
              }
            >
              {selectedSession?.is_open ? 'Tutup Forum Sesi' : 'Buka Forum Sesi'}
            </Button>
          )}
        </div>
      </Card>

      {!selectedSession ? (
        <Card className="border-dashed py-12 text-center text-sm text-muted">
          Belum ada course dan sesi yang dapat dikelola.
        </Card>
      ) : (
        <>
          <div className="flex min-w-0 flex-wrap items-center justify-between gap-3">
            <div className="min-w-0">
              <h2 className="break-words text-lg font-bold text-on-surface">
                {selectedSession.course_title}
              </h2>
              <p className="mt-0.5 break-words text-sm text-muted">
                {selectedSession.session_title}
              </p>
            </div>
            <span className="rounded-full bg-surface-low px-3 py-1 text-xs font-semibold text-on-surface-variant dark:bg-slate-800 dark:text-slate-300">
              {threads.length} topik diskusi
            </span>
          </div>

          {/* Form Buat Topik Diskusi */}
          {canParticipate && (
            <div id="new-thread">
              <Card title="Mulai Diskusi Baru">
                <form onSubmit={createThread} className="space-y-3">
                  <input
                    name="title"
                    required
                    minLength="3"
                    maxLength="200"
                    placeholder="Judul diskusi"
                    className={FIELD_CLASS}
                  />
                  <textarea
                    name="body"
                    required
                    maxLength="2000"
                    rows="3"
                    placeholder="Tulis pertanyaan, pengumuman, atau arahan untuk peserta pada sesi ini..."
                    className={TEXTAREA_CLASS}
                  />
                  <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label className="text-xs text-muted">
                      Lampirkan gambar (maks. 5 MB setelah kompresi)
                      <input
                        name="image"
                        type="file"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        className="mt-1 block max-w-full text-xs text-on-surface-variant file:mr-2.5 file:rounded-md file:border-0 file:bg-primary/10 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-primary"
                      />
                    </label>
                    <Button
                      type="submit"
                      variant="primary"
                      disabled={busy}
                      loading={busy}
                      icon={<Send className="size-4" />}
                    >
                      Kirim Diskusi
                    </Button>
                  </div>
                </form>
              </Card>
            </div>
          )}

          {/* Daftar Thread */}
          <div id="forum-threads" className="space-y-4">
            {threads.length ? (
              threadPage.pagedItems.map((thread) => (
                <article key={thread.thread_id} className="dls-card-surface min-w-0 rounded-xl p-5">
                  <div className="flex min-w-0 flex-wrap items-start justify-between gap-3">
                    <div className="flex min-w-0 items-start gap-3">
                      <span className="grid size-9 shrink-0 place-items-center rounded-full bg-secondary/10 text-sm font-bold text-secondary dark:bg-slate-700 dark:text-slate-100">
                        {String(thread.author_name || '?')
                          .trim()
                          .slice(0, 1)
                          .toLocaleUpperCase('id')}
                      </span>
                      <div className="min-w-0">
                        <div className="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
                          <h3 className="min-w-0 break-words font-bold text-on-surface">
                            {thread.title}
                          </h3>
                          <RoleBadge role={thread.author_role} />
                        </div>
                        <p className="mt-0.5 break-words text-xs text-muted">
                          {thread.author_name} · {formatDate(thread.created_at)}
                        </p>
                      </div>
                    </div>

                    {canModerate && (
                      <select
                        aria-label={`Status ${thread.title}`}
                        value={thread.status}
                        onChange={(event) => updateThreadStatus(thread, event.target.value)}
                        className="h-9 w-full rounded-md border border-outline bg-surface-lowest px-2.5 text-xs font-medium text-on-surface outline-none focus:border-primary sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                      >
                        <option value="visible">Terlihat · Terbuka</option>
                        <option value="locked">Terlihat · Ditutup</option>
                        <option value="hidden">Disembunyikan</option>
                      </select>
                    )}
                  </div>

                  <p className="mt-4 whitespace-pre-wrap break-words text-sm leading-6 text-on-surface-variant">
                    {thread.body}
                  </p>

                  {thread.attachment_path && (
                    <img
                      crossOrigin="use-credentials"
                      src={`${API_BASE}${thread.attachment_path}`}
                      alt="Lampiran diskusi"
                      loading="lazy"
                      className="mt-3 max-h-96 w-full max-w-2xl rounded-lg border border-outline/40 object-contain dark:border-slate-700"
                    />
                  )}

                  <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-outline/40 pt-3 text-xs dark:border-slate-800">
                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      disabled={!canParticipate}
                      onClick={() => canParticipate && toggleLike('thread', thread.thread_id)}
                      className={`inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 font-semibold transition ${
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
                      <span>{thread.likes_count || 0} suka</span>
                    </button>

                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      onClick={() => {
                        const next = expanded === thread.thread_id ? null : thread.thread_id;
                        setExpanded(next);
                        if (next && !details[next]) loadDetails(next);
                      }}
                      className="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 font-semibold text-muted transition hover:bg-surface-low hover:text-on-surface dark:hover:bg-slate-800"
                    >
                      <MessageSquare className="size-3.5" />
                      <span>{thread.replies_count || 0} balasan</span>
                    </button>
                  </div>

                  {expanded === thread.thread_id && (
                    <div className="mt-3 space-y-3 border-t border-outline/40 pt-3 dark:border-slate-800">
                      {details[thread.thread_id]?.replies?.map((reply) => (
                        <div
                          key={reply.reply_id}
                          className="rounded-lg bg-surface-low/50 p-3 dark:bg-slate-800/50"
                        >
                          <div className="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
                            <strong className="break-words text-xs font-bold text-on-surface">
                              {reply.author_name}
                            </strong>
                            <RoleBadge role={reply.author_role} />
                            <time className="text-[11px] text-muted">
                              {formatDate(reply.created_at)}
                            </time>
                          </div>
                          <p className="mt-1.5 whitespace-pre-wrap break-words text-sm leading-6 text-on-surface-variant">
                            {reply.body}
                          </p>
                          {reply.attachment_path && (
                            <img
                              crossOrigin="use-credentials"
                              src={`${API_BASE}${reply.attachment_path}`}
                              alt="Lampiran balasan"
                              loading="lazy"
                              className="mt-2 max-h-80 w-full max-w-xl rounded-lg border border-outline/40 object-contain dark:border-slate-700"
                            />
                          )}
                          <button
                            type="button"
                            data-dls-action-ignore="true"
                            disabled={!canParticipate}
                            onClick={() => canParticipate && toggleLike('reply', reply.reply_id, thread.thread_id)}
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
                            <span>{reply.likes_count || 0} suka</span>
                          </button>
                        </div>
                      ))}

                      {canParticipate && selectedSession.is_open && thread.status === 'visible' ? (
                        <form
                          onSubmit={(event) => replyToThread(event, thread.thread_id)}
                          className="border-t border-outline/40 pt-3 dark:border-slate-800"
                        >
                          <textarea
                            name="body"
                            required
                            maxLength="2000"
                            rows="2"
                            placeholder="Tulis balasan..."
                            className={TEXTAREA_CLASS}
                          />
                          <div className="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <label className="min-w-0 text-xs text-muted">
                              Lampiran gambar (maks. 5 MB)
                              <input
                                name="image"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                className="mt-1 block max-w-full text-xs text-on-surface-variant file:mr-2.5 file:rounded-md file:border-0 file:bg-primary/10 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-primary"
                              />
                            </label>
                            <Button type="submit" size="sm" variant="primary" disabled={busy}>
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
              ))
            ) : (
              <Card className="border-dashed py-10 text-center">
                <MessagesSquare className="mx-auto mb-2 size-8 text-muted opacity-50" />
                <p className="text-sm text-muted">Belum ada diskusi untuk sesi ini.</p>
              </Card>
            )}
          </div>

          {Boolean(threads.length) && (
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
