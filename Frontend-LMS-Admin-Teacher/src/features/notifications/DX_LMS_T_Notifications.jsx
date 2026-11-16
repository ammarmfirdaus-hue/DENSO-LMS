import { useEffect, useMemo, useState } from 'react';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';
import {
  Bell,
  BookOpen,
  CheckCheck,
  ClipboardCheck,
  Eye,
  Image as ImageIcon,
  Megaphone,
  MessageSquare,
  Plus,
  Search,
  Send,
  Settings,
  Trash2,
  Users,
  X
} from 'lucide-react';
import { api, resolveNotificationImageUrl } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import Modal from '../../components/Modal';
import ModalNotif from '../../components/ModalNotif';
import StatusBadge from '../../components/StatusBadge';
import Pagination, { usePagination } from '../../components/Pagination';
import { useQueryParamState } from '../../utils/useQueryParamState';
import showAlert from '../../utils/alert';
import { hasPermission } from '../../utils/access-control';
import { compressImageToWebP } from '../../utils/compress-forum-image';

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
  const location = useLocation();
  const navigate = useNavigate();
  const isAdmin = user?.role === 'admin';
  const canSendNotifications = hasPermission(user, 'notifications.send');
  const [items, setItems] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [filter, setFilter] = useQueryParamState('filter', 'all');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [sendModalOpen, setSendModalOpen] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  // Target modes: 'role' atau 'members' (pilihan anggota checklist)
  const [targetMode, setTargetMode] = useState('role');
  const [targetRoles, setTargetRoles] = useState(['student']);
  const [membersList, setMembersList] = useState([]);
  const [membersLoading, setMembersLoading] = useState(false);
  const [selectedMembers, setSelectedMembers] = useState([]);
  const [memberSearch, setMemberSearch] = useState('');
  const [viewingMemberDetail, setViewingMemberDetail] = useState(null);

  // Image attachment state
  const [imageFile, setImageFile] = useState(null);
  const [imagePreview, setImagePreview] = useState(null);
  const [imageCompressing, setImageCompressing] = useState(false);

  // Popup & Email
  const [sendEmail, setSendEmail] = useState(false);
  const [showPopup, setShowPopup] = useState(false);

  // Modal detail preview
  const [selectedDetail, setSelectedDetail] = useState(null);

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

  async function fetchMembers() {
    if (membersList.length > 0 || membersLoading) return;
    setMembersLoading(true);
    try {
      const res = await api('/notifications/members');
      setMembersList(res.members || []);
    } catch (err) {
      showAlert.error('Gagal Memuat Daftar Anggota', err.message);
    } finally {
      setMembersLoading(false);
    }
  }

  useEffect(() => {
    loadNotifications();
  }, []);

  useEffect(() => {
    window.addEventListener('dls_notifications_changed', loadNotifications);
    return () => window.removeEventListener('dls_notifications_changed', loadNotifications);
  }, []);

  useEffect(() => {
    if (canSendNotifications && location.hash === '#send-notification') {
      setSendModalOpen(true);
      navigate(`${location.pathname}${location.search}`, { replace: true });
    }
  }, [canSendNotifications, location.hash, location.pathname, location.search, navigate]);

  useEffect(() => {
    if (sendModalOpen && targetMode === 'members') {
      fetchMembers();
    }
  }, [sendModalOpen, targetMode]);

  const filteredMembers = useMemo(() => {
    const q = memberSearch.trim().toLowerCase();
    if (!q) return membersList;
    return membersList.filter(
      (m) =>
        (m.name && m.name.toLowerCase().includes(q)) ||
        (m.npk && String(m.npk).toLowerCase().includes(q)) ||
        (m.department && m.department.toLowerCase().includes(q)) ||
        (m.role && m.role.toLowerCase().includes(q))
    );
  }, [membersList, memberSearch]);

  function selectAllFilteredMembers() {
    setSelectedMembers((prev) => {
      const currentNpks = new Set(prev.map((m) => m.npk));
      const newlyAdded = filteredMembers.filter((m) => !currentNpks.has(m.npk));
      return [...prev, ...newlyAdded];
    });
  }

  function deselectAllMembers() {
    setSelectedMembers([]);
  }

  function toggleMember(member) {
    setSelectedMembers((prev) => {
      const exists = prev.some((m) => m.npk === member.npk);
      if (exists) {
        return prev.filter((m) => m.npk !== member.npk);
      }
      return [...prev, member];
    });
  }

  async function handleImageSelect(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      showAlert.warning('Format Tidak Didukung', 'Hanya menerima file gambar (JPG, PNG, GIF, WebP).');
      return;
    }
    setImageCompressing(true);
    try {
      const webpFile = await compressImageToWebP(file, { forceWebp: true });
      setImageFile(webpFile);
      if (imagePreview) URL.revokeObjectURL(imagePreview);
      setImagePreview(URL.createObjectURL(webpFile));
    } catch (err) {
      showAlert.error('Gagal Mengonversi Gambar', err.message);
    } finally {
      setImageCompressing(false);
    }
  }

  function removeImage() {
    if (imagePreview) URL.revokeObjectURL(imagePreview);
    setImageFile(null);
    setImagePreview(null);
  }

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
    if (targetMode === 'role' && !targetRoles.length) {
      const msg = 'Pilih setidaknya satu role penerima.';
      setError(msg);
      showAlert.warning('Penerima Belum Dipilih', msg);
      return;
    }
    if (targetMode === 'members' && !selectedMembers.length) {
      const msg = 'Pilih setidaknya satu anggota penerima notifikasi.';
      setError(msg);
      showAlert.warning('Anggota Belum Dipilih', msg);
      return;
    }

    setBusy(true);
    setError('');
    setNotice('');
    try {
      let uploadedImageUrl = null;
      if (imageFile) {
        const imgData = new FormData();
        imgData.append('image', imageFile);
        const uploadRes = await api('/notifications/upload-image', {
          method: 'POST',
          body: imgData
        });
        uploadedImageUrl = uploadRes.imageUrl;
      }

      const data = new FormData(form);
      const payload = {
        title: data.get('title'),
        message: data.get('message'),
        category: data.get('category'),
        priority: data.get('priority'),
        targetMode,
        targetRoles: targetMode === 'role' ? targetRoles : undefined,
        targetMembers:
          targetMode === 'members'
            ? selectedMembers.map((m) => ({
                npk: m.npk,
                name: m.name,
                role: m.role || 'student',
                email: m.email || undefined
              }))
            : undefined,
        sendEmail,
        showPopup,
        imageUrl: uploadedImageUrl
      };

      const result = await api('/notifications', {
        method: 'POST',
        body: JSON.stringify(payload)
      });

      form.reset();
      setTargetRoles(['student']);
      setSelectedMembers([]);
      removeImage();
      setSendEmail(false);
      setShowPopup(false);
      setSendModalOpen(false);
      const emailNote = sendEmail
        ? ` ${result.emailQueued || 0} email masuk antrean pengiriman.`
        : '';
      const recipientCount = targetMode === 'members' ? selectedMembers.length : result.recipients.length;
      const successMsg = `Notifikasi terkirim ke ${recipientCount} penerima.${emailNote}`;
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

  async function deleteNotification(id) {
    try {
      await api(`/notifications/${id}`, { method: 'DELETE' });
      showAlert.toast('Notifikasi dihapus dari riwayat Anda', 'success');
      window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { type: 'notification_deleted' } }));
      await loadNotifications();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Notifikasi', e.message);
    }
  }

  async function clearNotificationHistory() {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Semua Riwayat Notifikasi?',
      text: 'Seluruh notifikasi di halaman Anda akan dihapus dari riwayat akun Anda. Notifikasi milik pengguna lain tetap aman.',
      confirmText: 'Ya, Hapus Riwayat',
      cancelText: 'Batal',
      danger: true,
      icon: 'warning'
    });
    if (!confirmed) return;

    setBusy(true);
    try {
      await api('/notifications', { method: 'DELETE' });
      showAlert.success('Riwayat Dihapus', 'Seluruh riwayat notifikasi Anda telah dibersihkan.');
      window.dispatchEvent(new CustomEvent('dls_notifications_changed', { detail: { type: 'notification_cleared' } }));
      await loadNotifications();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Riwayat', e.message);
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
          <div className="flex flex-wrap items-center gap-2.5">
            <span className="inline-flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-lowest px-3.5 py-2 text-xs font-semibold text-on-surface-variant dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
              <Bell className="size-3.5 text-primary" />
              {unreadCount} pesan belum dibaca
            </span>
            {canSendNotifications && (
              <Button
                type="button"
                variant="primary"
                icon={<Plus className="size-4" />}
                onClick={() => setSendModalOpen(true)}
              >
                Kirim Notifikasi
              </Button>
            )}
          </div>
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

      {/* Modal Kirim Notifikasi */}
      {canSendNotifications && (
        <Modal
          isOpen={sendModalOpen}
          onClose={() => !busy && setSendModalOpen(false)}
          title="Kirim Notifikasi Baru"
          subtitle="Penerima akan langsung melihat pesan di pusat notifikasi portal mereka."
          maxWidth="3xl"
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

            {/* Mode Pemilihan Penerima: Role vs Checklist Anggota */}
            <div className="space-y-2">
              <label className="text-xs font-semibold text-on-surface-variant">
                Penerima Notifikasi *
              </label>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setTargetMode('role')}
                  className={`flex-1 rounded-lg border px-3 py-2 text-xs font-semibold transition cursor-pointer ${
                    targetMode === 'role'
                      ? 'border-primary bg-primary/10 text-primary dark:bg-red-950/40 dark:text-red-300'
                      : 'border-outline/60 bg-surface-lowest text-muted hover:bg-surface-low'
                  }`}
                >
                  Kirim Berdasarkan Role
                </button>
                <button
                  type="button"
                  onClick={() => setTargetMode('members')}
                  className={`flex-1 rounded-lg border px-3 py-2 text-xs font-semibold transition cursor-pointer ${
                    targetMode === 'members'
                      ? 'border-primary bg-primary/10 text-primary dark:bg-red-950/40 dark:text-red-300'
                      : 'border-outline/60 bg-surface-lowest text-muted hover:bg-surface-low'
                  }`}
                >
                  Pilih Anggota Spesifik ({selectedMembers.length} dipilih)
                </button>
              </div>

              {targetMode === 'role' ? (
                <div className="flex flex-wrap gap-2 pt-1">
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
              ) : (
                <div className="space-y-2 rounded-lg border border-outline/60 bg-surface-lowest p-3 dark:border-slate-800 dark:bg-slate-900/50">
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <div className="relative flex-1 min-w-[200px]">
                      <Search className="pointer-events-none absolute left-2.5 top-1/2 size-3.5 -translate-y-1/2 text-muted" />
                      <input
                        type="text"
                        value={memberSearch}
                        onChange={(e) => setMemberSearch(e.target.value)}
                        placeholder="Cari nama, NPK, atau divisi..."
                        className="h-8 w-full rounded-md border border-outline bg-white pl-8 pr-2.5 text-xs text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-800"
                      />
                    </div>
                    <div className="flex items-center gap-1.5 text-xs">
                      <button
                        type="button"
                        onClick={selectAllFilteredMembers}
                        className="rounded px-2 py-1 text-xs font-semibold text-primary hover:bg-primary/10 transition cursor-pointer"
                      >
                        Pilih Semua
                      </button>
                      <span className="text-muted">·</span>
                      <button
                        type="button"
                        onClick={deselectAllMembers}
                        className="rounded px-2 py-1 text-xs font-semibold text-muted hover:text-on-surface transition cursor-pointer"
                      >
                        Kosongkan
                      </button>
                    </div>
                  </div>

                  <div className="text-[11px] text-muted">
                    {selectedMembers.length} anggota terpilih dari {membersList.length} total anggota
                  </div>

                  <div className="max-h-48 overflow-y-auto space-y-1 rounded border border-outline/40 bg-surface-low/30 p-1.5 dark:border-slate-800">
                    {membersLoading ? (
                      <p className="p-4 text-center text-xs text-muted">Memuat daftar anggota...</p>
                    ) : filteredMembers.length ? (
                      filteredMembers.map((member) => {
                        const isChecked = selectedMembers.some((m) => m.npk === member.npk);
                        return (
                          <label
                            key={member.npk}
                            className={`flex cursor-pointer items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-xs transition ${
                              isChecked
                                ? 'bg-primary/10 text-primary dark:bg-red-950/30 dark:text-red-300'
                                : 'hover:bg-surface-low text-on-surface'
                            }`}
                          >
                            <div className="flex items-center gap-2 min-w-0">
                              <input
                                type="checkbox"
                                checked={isChecked}
                                onChange={() => toggleMember(member)}
                                className="size-3.5 accent-primary"
                              />
                              <div className="min-w-0">
                                <span className="font-semibold truncate block">{member.name}</span>
                                <span className="text-[11px] text-muted truncate block">NPK: {member.npk}</span>
                              </div>
                            </div>
                            <div className="flex items-center gap-1.5 shrink-0">
                              <span className="rounded bg-surface-low px-1.5 py-0.5 text-[10px] font-medium text-muted dark:bg-slate-800">
                                {member.department || member.role}
                              </span>
                              <button
                                type="button"
                                title="Lihat detail anggota"
                                onClick={(e) => {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  setViewingMemberDetail(member);
                                }}
                                className="rounded p-1 text-muted hover:bg-surface-lowest hover:text-primary transition cursor-pointer"
                              >
                                <Eye className="size-3.5" />
                              </button>
                            </div>
                          </label>
                        );
                      })
                    ) : (
                      <p className="p-4 text-center text-xs text-muted">Tidak ada anggota yang cocok dengan pencarian.</p>
                    )}
                  </div>
                </div>
              )}
            </div>

            {/* Lampiran Gambar (Auto WebP) */}
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-semibold text-on-surface-variant">Lampiran Gambar</span>
                <span className="text-[11px] text-muted">Otomatis dioptimalkan ke WebP</span>
              </div>

              {imagePreview ? (
                <div className="relative overflow-hidden rounded-lg border border-outline bg-surface-low/40 p-3">
                  <div className="flex items-start gap-3">
                    <img
                      src={imagePreview}
                      alt="Preview lampiran"
                      className="h-20 w-28 rounded-md object-cover border border-outline/50 bg-white"
                    />
                    <div className="min-w-0 flex-1">
                      <p className="text-xs font-semibold text-on-surface truncate">
                        {imageFile?.name || 'lampiran.webp'}
                      </p>
                      <p className="text-[11px] text-muted mt-0.5">
                        Ukuran: {imageFile ? `${(imageFile.size / 1024).toFixed(1)} KB` : '-'} · Format WebP
                      </p>
                      <button
                        type="button"
                        onClick={removeImage}
                        className="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 transition cursor-pointer"
                      >
                        <Trash2 className="size-3" />
                        Hapus Gambar
                      </button>
                    </div>
                  </div>
                </div>
              ) : (
                <label className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-outline/70 p-4 text-center hover:border-primary/50 hover:bg-surface-low/40 transition">
                  <ImageIcon className="size-6 text-muted mb-1" />
                  <span className="text-xs font-medium text-on-surface">
                    {imageCompressing ? 'Mengonversi gambar ke WebP...' : 'Klik untuk unggah gambar lampiran'}
                  </span>
                  <span className="text-[11px] text-muted mt-0.5">JPG, PNG, GIF, atau WebP</span>
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    onChange={handleImageSelect}
                    disabled={imageCompressing}
                    className="hidden"
                  />
                </label>
              )}
            </div>

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

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button
                type="button"
                variant="secondary"
                disabled={busy}
                onClick={() => setSendModalOpen(false)}
              >
                Batal
              </Button>
              <Button
                type="submit"
                variant="primary"
                disabled={busy || imageCompressing}
                loading={busy}
                icon={<Send className="size-4" />}
              >
                {busy ? 'Mengirim...' : 'Kirim Notifikasi'}
              </Button>
            </div>
          </form>
        </Modal>
      )}

      {/* Daftar Notifikasi */}
      <section id="notifications-list" className="scroll-mt-20 space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 className="text-lg font-bold text-on-surface">Notifikasi Terbaru</h2>
            <p className="text-xs text-muted">Menampilkan sampai 100 notifikasi terakhir.</p>
          </div>
          <div className="flex flex-wrap items-center gap-2">
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
            <Button
              type="button"
              size="sm"
              variant="outline"
              disabled={busy || items.length === 0}
              icon={<Trash2 className="size-4" />}
              onClick={clearNotificationHistory}
              className="text-red-600 hover:border-red-300 hover:bg-red-50 dark:text-red-400 dark:hover:border-red-900 dark:hover:bg-red-950/40"
            >
              Hapus Riwayat
            </Button>
          </div>
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
                  onClick={(e) => {
                    if (e.target.closest('button, a, input, select, img')) return;
                    setSelectedDetail(item);
                  }}
                  title="Klik untuk melihat detail notifikasi"
                  className={`dls-card-surface dls-card-interactive min-w-0 cursor-pointer rounded-xl p-5 transition ${
                    !item.is_read ? 'ring-1 ring-primary/35 dark:ring-primary/45' : ''
                  }`}
                >
                  <div className="flex min-w-0 items-start gap-3.5">
                    <span className="mt-0.5 flex shrink-0 items-center justify-center">
                      <IconComp
                        className={`size-6 shrink-0 ${
                          item.is_read
                            ? 'text-muted'
                            : 'text-primary dark:text-red-400'
                        }`}
                      />
                    </span>

                    <div className="min-w-0 flex-1">
                      <div className="flex min-w-0 flex-wrap items-start justify-between gap-2">
                        <div className="min-w-0">
                          <div className="flex flex-wrap items-center gap-2">
                            <h3 className="break-words font-bold text-on-surface">{item.title}</h3>
                            {item.priority === 'important' && (
                              <StatusBadge status="important" label="Penting" dot />
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

                      {item.image_url && (
                        <div className="mt-3 max-w-sm overflow-hidden rounded-lg border border-outline bg-surface-lowest">
                          <img
                            src={resolveNotificationImageUrl(item.image_url)}
                            alt={item.title}
                            className="max-h-48 w-full object-contain bg-slate-50 dark:bg-slate-900/50 cursor-pointer hover:opacity-90 transition"
                            onClick={() => setSelectedDetail(item)}
                            loading="lazy"
                          />
                        </div>
                      )}

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

                        <div className="flex flex-wrap items-center gap-1.5">
                          <Button
                            type="button"
                            size="xs"
                            variant="ghost"
                            icon={<Eye className="size-3.5" />}
                            onClick={() => setSelectedDetail(item)}
                            className="text-on-surface-variant hover:bg-surface-low"
                          >
                            Detail
                          </Button>
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
                          <Button
                            type="button"
                            size="xs"
                            variant="ghost"
                            icon={<Trash2 className="size-3.5" />}
                            onClick={() => deleteNotification(item.notification_id)}
                            className="text-muted hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                            aria-label={`Hapus notifikasi ${item.title}`}
                          >
                            Hapus
                          </Button>
                        </div>
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

      {/* Modal Popup Detail Notifikasi */}
      <ModalNotif
        isOpen={Boolean(selectedDetail)}
        notification={selectedDetail}
        onClose={() => setSelectedDetail(null)}
      />

      {/* Modal Detail Anggota (Penerima Notifikasi) */}
      {viewingMemberDetail && (
        <Modal
          isOpen={Boolean(viewingMemberDetail)}
          onClose={() => setViewingMemberDetail(null)}
          title="Detail Anggota"
          size="md"
        >
          <div className="space-y-4">
            <div className="flex items-center gap-3 rounded-lg border border-outline/40 bg-surface-low p-4 dark:border-slate-800 dark:bg-slate-800/40">
              <span className="grid size-12 shrink-0 place-items-center rounded-full bg-primary/10 font-mono text-base font-bold text-primary">
                {String(viewingMemberDetail.name || 'U').charAt(0).toUpperCase()}
              </span>
              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="font-semibold text-on-surface">{viewingMemberDetail.name}</span>
                  <StatusBadge
                    status={viewingMemberDetail.role || 'student'}
                    label={viewingMemberDetail.role || 'Student'}
                  />
                </div>
                <p className="mt-0.5 font-mono text-xs text-muted">
                  NPK: {viewingMemberDetail.npk || '—'}
                </p>
              </div>
            </div>

            <div className="grid grid-cols-1 gap-3 rounded-lg border border-outline/40 bg-surface-low p-3.5 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-800/40">
              <div>
                <span className="text-xs text-muted">Departemen / Divisi</span>
                <p className="mt-0.5 text-sm font-semibold text-on-surface">
                  {viewingMemberDetail.department || '—'}
                </p>
              </div>
              <div>
                <span className="text-xs text-muted">Email</span>
                <p className="mt-0.5 break-all text-xs font-semibold text-on-surface">
                  {viewingMemberDetail.email || '—'}
                </p>
              </div>
              <div>
                <span className="text-xs text-muted">Peran Sistem</span>
                <p className="mt-0.5 text-sm font-semibold capitalize text-on-surface">
                  {viewingMemberDetail.role || 'Student'}
                </p>
              </div>
              <div>
                <span className="text-xs text-muted">Status Akun</span>
                <p className="mt-0.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                  Aktif
                </p>
              </div>
            </div>

            <div className="flex items-center justify-between border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button
                type="button"
                variant={
                  selectedMembers.some((m) => m.npk === viewingMemberDetail.npk)
                    ? 'outline'
                    : 'primary'
                }
                size="sm"
                onClick={() => {
                  toggleMember(viewingMemberDetail);
                }}
              >
                {selectedMembers.some((m) => m.npk === viewingMemberDetail.npk)
                  ? 'Batalkan Pilihan'
                  : 'Pilih Anggota Ini'}
              </Button>
              <Button
                type="button"
                variant="secondary"
                size="sm"
                onClick={() => setViewingMemberDetail(null)}
              >
                Tutup
              </Button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
