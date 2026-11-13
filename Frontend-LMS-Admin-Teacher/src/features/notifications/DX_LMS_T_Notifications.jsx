import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import {
  Bell,
  BookOpen,
  CheckCheck,
  ClipboardCheck,
  Megaphone,
  MessageSquare,
  Search,
  Send,
  Settings
} from 'lucide-react';
import { api } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import Pagination, { usePagination } from '../../components/Pagination';
import { useQueryParamState } from '../../utils/useQueryParamState';
import showAlert from '../../utils/alert';
import { hasPermission } from '../../utils/access-control';

const CATEGORIES = [
  ['all', 'Semua'],
  ['unread', 'Belum dibaca'],
  ['important', 'Penting'],
  ['announcement', 'Pengumuman'],
  ['course', 'Course'],
  ['assessment', 'Assessment'],
  ['forum', 'Forum'],
  ['system', 'System']
];

const AUDIENCES = [
  ['student', 'Student'],
  ['teacher', 'Teacher'],
  ['admin', 'Admin']
];

const CATEGORY_NAME = Object.fromEntries(
  CATEGORIES.filter(([key]) => key !== 'all' && key !== 'unread' && key !== 'important')
);

const CATEGORY_ICON = {
  course: BookOpen,
  assessment: ClipboardCheck,
  forum: MessageSquare,
  system: Settings,
  announcement: Megaphone
};

const FIELD_CLASS =
  'mt-1.5 h-10 w-full min-w-0 rounded-md border border-outline bg-surface-lowest px-3 text-sm font-normal text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Halaman Pusat Notifikasi untuk mengirim pengumuman dan membaca pemberitahuan portal.
 */
export default function NotificationsPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canSendNotifications = hasPermission(user, 'notifications.send');
  const [items, setItems] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [filter, setFilter] = useQueryParamState('filter', 'all');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [targetRoles, setTargetRoles] = useState(['student']);
  const [sendEmail, setSendEmail] = useState(false);
  const [showPopup, setShowPopup] = useState(false);

  async function loadNotifications() {
    try {
      const result = await api('/notifications?limit=50');
      setItems(result.notifications || []);
      setUnreadCount(result.unreadCount || 0);
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Notifikasi', e.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadNotifications();
  }, []);

  useEffect(() => {
    window.addEventListener('dls_notifications_changed', loadNotifications);
    return () => window.removeEventListener('dls_notifications_changed', loadNotifications);
  }, []);

  const filtered = useMemo(() => {
    const query = search.trim().toLocaleLowerCase('id');
    return items.filter((item) => {
      const matchesFilter =
        filter === 'all' ||
        (filter === 'unread'
          ? !item.is_read
          : filter === 'important'
            ? item.priority === 'important'
            : item.category === filter);
      const matchesSearch =
        !query ||
        `${item.title} ${item.message} ${item.sender_name} ${item.category}`
          .toLocaleLowerCase('id')
          .includes(query);
      return matchesFilter && matchesSearch;
    });
  }, [items, filter, search]);

  const page = usePagination(filtered, 10);

  function toggleAudience(role) {
    setTargetRoles((current) =>
      current.includes(role) ? current.filter((value) => value !== role) : [...current, role]
    );
  }

  async function sendNotification(event) {
    event.preventDefault();
    const form = event.currentTarget;
    if (!targetRoles.length) {
      const msg = 'Pilih setidaknya satu role penerima.';
      setError(msg);
      showAlert.warning('Penerima Belum Dipilih', msg);
      return;
    }
    setBusy(true);
    setError('');
    setNotice('');
    try {
      const data = new FormData(form);
      const result = await api('/notifications', {
        method: 'POST',
        body: JSON.stringify({
          title: data.get('title'),
          message: data.get('message'),
          category: data.get('category'),
          priority: data.get('priority'),
          targetRoles,
          sendEmail,
          showPopup
        })
      });
      form.reset();
      setTargetRoles(['student']);
      setSendEmail(false);
      setShowPopup(false);
      const emailNote = sendEmail
        ? ` ${result.emailQueued || 0} email masuk antrean pengiriman.`
        : '';
      const successMsg = `Notifikasi terkirim ke ${result.recipients.join(', ')}.${emailNote}`;
      setNotice(successMsg);
      showAlert.success('Notifikasi Terkirim', successMsg);
      await loadNotifications();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengirim Notifikasi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function markRead(id) {
    try {
      await api(`/notifications/${id}/read`, { method: 'POST', body: '{}' });
      showAlert.toast('Notifikasi ditandai sudah dibaca', 'success');
      await loadNotifications();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memperbarui Notifikasi', e.message);
    }
  }

  async function markAllRead() {
    setBusy(true);
    try {
      await api('/notifications/read-all', { method: 'POST', body: '{}' });
      showAlert.success('Semua Sudah Dibaca', 'Seluruh notifikasi Anda telah ditandai sebagai dibaca.');
      await loadNotifications();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menandai Semua Dibaca', e.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="min-w-0 space-y-6">
      <PageHeader
        eyebrow="Pusat Informasi"
        title="Notifikasi"
        description="Kirim pengumuman penting, pengingat penyelesaian materi, serta pantau pemberitahuan portal secara langsung."
        actions={
          <span className="inline-flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-lowest px-3.5 py-2 text-xs font-semibold text-on-surface-variant dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <Bell className="size-3.5 text-primary" />
            {unreadCount} pesan belum dibaca
          </span>
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

      {/* Form Kirim Notifikasi */}
      {canSendNotifications && (
        <div id="send-notification" className="scroll-mt-20">
        <Card
          title="Kirim Notifikasi Baru"
          subtitle="Penerima akan langsung melihat pesan di pusat notifikasi portal mereka."
          action={<StatusBadge status={user?.role || 'teacher'} label={user?.role} />}
        >
          <form onSubmit={sendNotification} className="space-y-4">
            <div className="grid min-w-0 gap-4 sm:grid-cols-2">
              <label className="min-w-0 text-xs font-semibold text-on-surface-variant sm:col-span-2">
                Judul *
                <input
                  name="title"
                  required
                  minLength="3"
                  maxLength="180"
                  className={FIELD_CLASS}
                  placeholder="Contoh: Pengingat penyelesaian materi sesi 2"
                />
              </label>

              <label className="min-w-0 text-xs font-semibold text-on-surface-variant sm:col-span-2">
                Isi Pesan *
                <textarea
                  name="message"
                  required
                  maxLength="2000"
                  rows="4"
                  className="mt-1.5 w-full min-w-0 resize-y rounded-md border border-outline bg-surface-lowest p-3 text-sm font-normal leading-6 text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                  placeholder="Tulis informasi yang perlu diketahui penerima..."
                />
              </label>

              <label className="text-xs font-semibold text-on-surface-variant">
                Kategori
                <select name="category" className={FIELD_CLASS}>
                  <option value="announcement">Pengumuman</option>
                  <option value="course">Course</option>
                  <option value="assessment">Assessment</option>
                  <option value="forum">Forum</option>
                  <option value="system">System</option>
                </select>
              </label>

              <label className="text-xs font-semibold text-on-surface-variant">
                Prioritas
                <select name="priority" className={FIELD_CLASS}>
                  <option value="normal">Normal</option>
                  <option value="important">Penting</option>
                </select>
              </label>
            </div>

            <fieldset className="min-w-0">
              <legend className="mb-2 text-xs font-semibold text-on-surface-variant">
                Kirim Kepada
              </legend>
              <div className="flex flex-wrap gap-2">
                {AUDIENCES.filter(([role]) => isAdmin || role === 'student').map(([role, label]) => (
                  <label
                    key={role}
                    className={`inline-flex min-h-9 cursor-pointer items-center gap-2 rounded-lg border px-3 text-xs font-semibold transition ${
                      targetRoles.includes(role)
                        ? 'border-primary/40 bg-primary/10 text-primary dark:bg-red-950/40 dark:text-red-300'
                        : 'border-outline/60 bg-surface-lowest text-on-surface-variant hover:bg-surface-low dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                    }`}
                  >
                    <input
                      type="checkbox"
                      checked={targetRoles.includes(role)}
                      onChange={() => toggleAudience(role)}
                      className="size-4 accent-primary"
                    />
                    {label}
                  </label>
                ))}
              </div>
            </fieldset>

            <div className="grid gap-3 sm:grid-cols-2">
              <label className="flex cursor-pointer items-start gap-3 rounded-lg border border-outline/50 bg-surface-low/50 p-3 text-xs text-on-surface-variant dark:border-slate-800 dark:bg-slate-800/40">
                <input
                  type="checkbox"
                  checked={sendEmail}
                  onChange={(event) => setSendEmail(event.target.checked)}
                  className="mt-0.5 size-4 accent-primary"
                />
                <span>
                  <strong className="font-semibold text-on-surface">Kirim juga melalui email</strong>
                  <span className="mt-0.5 block text-muted">
                    Email masuk antrean untuk akun yang memiliki alamat email valid.
                  </span>
                </span>
              </label>

              {isAdmin && (
                <label className="flex cursor-pointer items-start gap-3 rounded-lg border border-outline/50 bg-surface-low/50 p-3 text-xs text-on-surface-variant dark:border-slate-800 dark:bg-slate-800/40">
                  <input
                    type="checkbox"
                    checked={showPopup}
                    onChange={(event) => setShowPopup(event.target.checked)}
                    className="mt-0.5 size-4 accent-primary"
                  />
                  <span>
                    <strong className="font-semibold text-on-surface">Munculkan Popup Modal</strong>
                    <span className="mt-0.5 block text-muted">
                      Tampil langsung sebagai modal pengumuman realtime di layar penerima.
                    </span>
                  </span>
                </label>
              )}
            </div>

            <div className="flex justify-end">
              <Button
                type="submit"
                variant="primary"
                disabled={busy}
                loading={busy}
                icon={<Send className="size-4" />}
                className="w-full sm:w-auto"
              >
                {busy ? 'Mengirim...' : 'Kirim Notifikasi'}
              </Button>
            </div>
          </form>
        </Card>
        </div>
      )}

      {/* Daftar Notifikasi */}
      <section id="notifications-list" className="scroll-mt-20 space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 className="text-lg font-bold text-on-surface">Notifikasi Terbaru</h2>
            <p className="text-xs text-muted">Menampilkan sampai 100 notifikasi terakhir.</p>
          </div>
          <Button
            type="button"
            size="sm"
            variant="outline"
            disabled={busy || unreadCount === 0}
            icon={<CheckCheck className="size-4" />}
            onClick={markAllRead}
          >
            Tandai Semua Dibaca
          </Button>
        </div>

        <div className="space-y-3">
          <div className="relative">
            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
            <input
              type="search"
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Cari judul, isi pesan, atau pengirim notifikasi..."
              className="h-10 w-full min-w-0 rounded-md border border-outline bg-surface-lowest pl-9 pr-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
          </div>

          <div className="max-w-full overflow-x-auto overscroll-x-contain pb-1">
            <div
              className="flex w-max min-w-full gap-1 rounded-lg border border-outline/50 bg-surface-lowest p-1 dark:border-slate-800 dark:bg-slate-900"
              role="tablist"
              aria-label="Filter kategori notifikasi"
            >
              {CATEGORIES.map(([key, label]) => (
                <button
                  key={key}
                  type="button"
                  role="tab"
                  data-dls-action-ignore="true"
                  aria-selected={filter === key}
                  onClick={() => setFilter(key)}
                  className={`min-h-8 shrink-0 whitespace-nowrap rounded-md px-3 text-xs font-semibold transition ${
                    filter === key
                      ? 'bg-primary text-white shadow-xs'
                      : 'text-muted hover:bg-surface-low hover:text-on-surface dark:hover:bg-slate-800'
                  }`}
                >
                  {label}
                </button>
              ))}
            </div>
          </div>
        </div>

        <div className="space-y-3" aria-live="polite">
          {loading ? (
            <Card className="py-10 text-center text-sm text-muted">Memuat notifikasi...</Card>
          ) : filtered.length ? (
            page.pagedItems.map((item) => {
              const IconComp = CATEGORY_ICON[item.category] || Megaphone;
              return (
                <article
                  key={item.notification_id}
                  className={`dls-card-surface min-w-0 rounded-xl p-5 transition ${
                    !item.is_read ? 'ring-1 ring-primary/35 dark:ring-primary/45' : ''
                  }`}
                >
                  <div className="flex min-w-0 items-start gap-3.5">
                    <span
                      className={`grid size-10 shrink-0 place-items-center rounded-lg ${
                        item.is_read
                          ? 'bg-surface-low text-muted dark:bg-slate-800'
                          : 'bg-primary/10 text-primary dark:bg-red-950/60 dark:text-red-300'
                      }`}
                    >
                      <IconComp className="size-5" />
                    </span>

                    <div className="min-w-0 flex-1">
                      <div className="flex min-w-0 flex-wrap items-start justify-between gap-2">
                        <div className="min-w-0">
                          <div className="flex flex-wrap items-center gap-2">
                            <h3 className="break-words font-bold text-on-surface">{item.title}</h3>
                            {item.priority === 'important' && (
                              <StatusBadge status="rejected" label="Penting" />
                            )}
                          </div>
                          <p className="mt-1 text-xs text-muted">
                            Dari {item.sender_name} ·{' '}
                            {new Date(item.created_at).toLocaleString('id-ID')}
                          </p>
                        </div>

                        {!item.is_read && (
                          <span
                            className="mt-1 size-2.5 shrink-0 rounded-full bg-primary"
                            aria-label="Belum dibaca"
                          />
                        )}
                      </div>

                      <p className="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-on-surface-variant">
                        {item.message}
                      </p>

                      <div className="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="rounded-md bg-surface-low px-2.5 py-1 text-xs font-medium text-muted dark:bg-slate-800 dark:text-slate-300">
                            {CATEGORY_NAME[item.category] || item.category}
                          </span>
                          {Boolean(item.show_popup) && (
                            <span className="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300">
                              <Megaphone className="size-3" />
                              Popup Modal
                            </span>
                          )}
                        </div>

                        {!item.is_read && (
                          <Button
                            type="button"
                            size="xs"
                            variant="ghost"
                            onClick={() => markRead(item.notification_id)}
                            className="text-primary hover:bg-primary/10"
                          >
                            Tandai Dibaca
                          </Button>
                        )}
                      </div>
                    </div>
                  </div>
                </article>
              );
            })
          ) : (
            <Card className="border-dashed py-10 text-center">
              <p className="font-bold text-on-surface">
                {items.length ? 'Tidak ada hasil' : 'Belum ada notifikasi'}
              </p>
              <p className="mt-1 text-xs text-muted">
                {items.length
                  ? 'Coba ubah kata kunci pencarian atau pilih kategori lain.'
                  : 'Notifikasi baru akan tampil di sini.'}
              </p>
            </Card>
          )}
        </div>

        {Boolean(filtered.length) && (
          <Card noPadding className="overflow-hidden">
            <Pagination
              {...page}
              onPageChange={page.setPage}
              onPageSizeChange={page.setPageSize}
              pageSizes={[10, 25, 50]}
            />
          </Card>
        )}
      </section>
    </div>
  );
}
