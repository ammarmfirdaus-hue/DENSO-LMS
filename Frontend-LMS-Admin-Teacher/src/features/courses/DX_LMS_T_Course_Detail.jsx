import { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useOutletContext, useParams } from 'react-router-dom';
import {
  ArrowLeft,
  BookOpen,
  CalendarDays,
  Clock,
  ExternalLink,
  Eye,
  File,
  FileBadge,
  FileText,
  Headphones,
  Image,
  ImagePlus,
  Link2,
  Lock,
  PlayCircle,
  Plus,
  Send,
  Trash2,
  Upload
} from 'lucide-react';
import { api, API_BASE, fetchApi, resolveCourseThumbnailUrl } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import Modal from '../../components/Modal';
import EditMaterialModal from '../materials/EditMaterialModal';
import SubmitMaterialModal from '../materials/SubmitMaterialModal';
import FinalMaterialDetailModal from '../materials/FinalMaterialDetailModal';
import showAlert from '../../utils/alert';
import { hasPermission } from '../../utils/access-control';

const STATUS_LABEL = {
  pending_admin: 'Menunggu admin',
  pending_review: 'Menunggu reviewer',
  approved: 'Disetujui reviewer',
  rejected: 'Perlu diperbaiki',
  published: 'Terbit untuk student'
};

const INPUT_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

const TYPE_ICON = {
  text: FileText,
  pdf: FileBadge,
  video: PlayCircle,
  image: Image,
  document: File,
  audio: Headphones,
  link: Link2
};

function youtubeEmbed(value = '') {
  try {
    const url = new URL(value);
    let id = url.hostname.includes('youtu.be') ? url.pathname.slice(1) : url.searchParams.get('v');
    if (!id && url.pathname.startsWith('/embed/')) id = url.pathname.split('/')[2];
    return id && /^[A-Za-z0-9_-]{6,20}$/.test(id)
      ? `https://www.youtube-nocookie.com/embed/${id}`
      : null;
  } catch {
    return null;
  }
}

/**
 * Halaman Detail Course untuk mengelola sesi pertemuan, materi terbit,
 * serta pengajuan materi yang sedang berada dalam alur persetujuan.
 */
export default function CourseDetailPage() {
  const { id } = useParams();
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canManageCover = hasPermission(user, 'courses.manage_cover');
  const canManageSessions = hasPermission(user, 'courses.manage_sessions');
  const canSubmitMaterial = hasPermission(user, 'materials.submit');
  const canSendToReviewer = hasPermission(user, 'materials.send_to_reviewer');
  const canPublishMaterial = hasPermission(user, 'materials.publish');
  const canDeleteMaterial = hasPermission(user, 'materials.delete');
  const [detail, setDetail] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [contentSearch, setContentSearch] = useState('');
  const [submissionStatus, setSubmissionStatus] = useState('all');

  const [editingSubmission, setEditingSubmission] = useState(null);
  const [submitModalSessionId, setSubmitModalSessionId] = useState(null);
  const [rejectingSubmission, setRejectingSubmission] = useState(null);
  const [rejectComment, setRejectComment] = useState('');
  const [selectedDetailMaterial, setSelectedDetailMaterial] = useState(null);
  const [uploadingCover, setUploadingCover] = useState(false);
  const [coverImgFailed, setCoverImgFailed] = useState(false);
  const coverInputRef = useRef(null);

  async function deleteFinalMaterial(mat) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Materi Final?',
      text: `Apakah Anda yakin ingin menghapus materi final "${mat.title}"? Materi ini akan ditarik dari tampilan student dan dihapus secara permanen.`,
      confirmText: 'Ya, Hapus Materi',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/material-workflow/published/${mat.material_id}`, { method: 'DELETE' });
      setNotice(res.message || 'Materi berhasil dihapus.');
      showAlert.success('Materi Final Dihapus', res.message || 'Materi berhasil ditarik dari student.');
      setSelectedDetailMaterial(null);
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function load() {
    try {
      const response = await api(`/courses/${id}/detail`);
      setDetail(response);
      setCoverImgFailed(false);
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Detail Course', e.message);
    }
  }

  async function uploadCover(event) {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
      showAlert.error('Format Tidak Didukung', 'Pilih file gambar JPG, PNG, GIF, atau WebP.');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showAlert.error('Ukuran Terlalu Besar', 'Ukuran foto sampul maksimal 5 MB.');
      return;
    }
    setUploadingCover(true);
    setError('');
    setNotice('');
    try {
      const formData = new FormData();
      formData.append('thumbnail', file);
      await api(`/courses/${id}/thumbnail`, { method: 'POST', body: formData });
      setNotice('Foto sampul course berhasil diperbarui dan disinkronkan ke portal student.');
      showAlert.success('Foto Sampul Diperbarui', 'Sampul course kini tampil di katalog admin maupun student.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengunggah Sampul', e.message);
    } finally {
      setUploadingCover(false);
    }
  }

  async function removeCover() {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Foto Sampul Course?',
      text: 'Course akan kembali menggunakan tampilan sampul ikon bawaan di portal Admin dan Student.',
      confirmText: 'Ya, Hapus Sampul',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setUploadingCover(true);
    setError('');
    setNotice('');
    try {
      await api(`/courses/${id}/thumbnail`, { method: 'DELETE' });
      setNotice('Foto sampul course berhasil dihapus.');
      showAlert.success('Foto Sampul Dihapus', 'Course kembali menggunakan tampilan bawaan.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Sampul', e.message);
    } finally {
      setUploadingCover(false);
    }
  }

  useEffect(() => {
    load();
  }, [id]);

  const submissionsBySession = useMemo(() => {
    const result = {};
    for (const item of detail?.submissions || []) {
      (result[item.session_id] ||= []).push(item);
    }
    return result;
  }, [detail]);

  const publishedCount =
    detail?.sessions?.reduce((sum, session) => sum + session.materials.length, 0) || 0;
  const pendingCount =
    detail?.submissions?.filter((item) =>
      ['pending_admin', 'pending_review', 'approved'].includes(item.status)
    ).length || 0;

  const visibleSessions = useMemo(
    () =>
      (detail?.sessions || []).filter((session) =>
        `${session.title} ${session.materials
          .map((material) => `${material.title} ${material.material_type} ${material.content || ''}`)
          .join(' ')}`
          .toLowerCase()
          .includes(contentSearch.trim().toLowerCase())
      ),
    [detail, contentSearch]
  );

  const submissionStatuses = useMemo(
    () => [...new Set((detail?.submissions || []).map((item) => item.status).filter(Boolean))],
    [detail]
  );

  const sessionPage = usePagination(visibleSessions, 5);

  async function addSession(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const title = new FormData(form).get('title');
    setBusy(true);
    setError('');
    try {
      await api(`/courses/${id}/sessions`, { method: 'POST', body: JSON.stringify({ title }) });
      showAlert.success(
        'Sesi Pembelajaran Dibuat',
        `Sesi "${title}" telah berhasil ditambahkan ke course ini.`
      );
      form.reset();
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menambah Sesi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function submitMaterial(event, sessionId) {
    event.preventDefault();
    const form = event.currentTarget;
    setBusy(true);
    setError('');
    setNotice('');
    try {
      await api(`/material-workflow/sessions/${sessionId}/submissions`, {
        method: 'POST',
        body: new FormData(form)
      });
      form.reset();
      setNotice('Materi berhasil diajukan untuk persetujuan Admin.');
      showAlert.success(
        'Materi Berhasil Diajukan',
        'Materi telah dikirimkan ke antrean persetujuan Admin LMS.'
      );
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengajukan Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function openSubmissionFile(submissionId) {
    try {
      const response = await fetchApi(`/material-workflow/submissions/${submissionId}/file`, {
        credentials: 'include',
        timeoutMs: 60000
      });
      if (!response.ok) throw new Error('File tidak dapat dibuka.');
      const url = URL.createObjectURL(await response.blob());
      window.open(url, '_blank', 'noopener');
      setTimeout(() => URL.revokeObjectURL(url), 60000);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuka File', e.message);
    }
  }

  async function sendReview(event, submissionId) {
    event.preventDefault();
    const form = event.currentTarget;
    const reviewerEmail = new FormData(form).get('reviewerEmail');
    setBusy(true);
    setError('');
    setNotice('');
    try {
      const response = await api(`/material-workflow/submissions/${submissionId}/send-review`, {
        method: 'POST',
        body: JSON.stringify({ reviewerEmail })
      });
      setNotice(response.message);
      showAlert.success(
        'Undangan Review Terkirim',
        response.message || 'Tautan peninjauan materi telah dikirimkan ke reviewer.'
      );
      form.reset();
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengirim Review', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function publish(submissionId) {
    const confirmed = await showAlert.confirm({
      title: 'Terbitkan Materi ke Siswa?',
      text: 'Materi ini akan disinkronisasikan ke portal siswa dan langsung dapat dipelajari.',
      confirmText: 'Ya, Terbitkan Sekarang',
      cancelText: 'Batal',
      icon: 'question'
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const response = await api(`/material-workflow/submissions/${submissionId}/publish`, {
        method: 'POST',
        body: '{}'
      });
      setNotice(response.message);
      showAlert.success(
        'Materi Diterbitkan',
        response.message || 'Materi berhasil dipublikasikan untuk student.'
      );
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menerbitkan Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleAdminReject(e) {
    e.preventDefault();
    if (!rejectingSubmission || !rejectComment.trim()) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api(
        `/material-workflow/submissions/${rejectingSubmission.submission_id}/admin-reject`,
        {
          method: 'POST',
          body: JSON.stringify({ comment: rejectComment.trim() })
        }
      );
      setNotice(result.message);
      showAlert.success(
        'Materi Dikembalikan',
        result.message || 'Catatan perbaikan telah dikirim ke trainer.'
      );
      setRejectingSubmission(null);
      setRejectComment('');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menolak Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  if (error && !detail) {
    return (
      <div className="space-y-4">
        <Link
          to="/courses"
          className="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline"
        >
          <ArrowLeft className="size-4" />
          Kembali ke daftar course
        </Link>
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          {error}
        </div>
      </div>
    );
  }

  if (!detail) {
    return (
      <div className="dls-card-surface grid min-h-64 place-items-center rounded-xl text-sm text-muted">
        Memuat detail course...
      </div>
    );
  }

  const { course, sessions } = detail;
  const canDeleteAny =
    canDeleteMaterial &&
    (isAdmin ||
      canSendToReviewer ||
      course.assigned_trainer_npk === user?.npk ||
      course.created_by === user?.npk);

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <Link
          to="/courses"
          className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted transition hover:text-primary"
        >
          <ArrowLeft className="size-4" />
          Semua Course
        </Link>
        <span className="text-xs font-semibold text-muted">
          Kelola Course / Detail Course
        </span>
      </div>

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

      {/* Banner Course */}
      <section className="dls-shell-chrome rounded-xl border border-slate-800/80 bg-[#10141a]/95 p-6 text-white shadow-sm backdrop-blur-md md:p-7">
        <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0 flex-1">
            <div className="flex flex-wrap items-center gap-2 text-xs font-semibold tracking-wider text-slate-300">
              <span className="rounded-md bg-primary/25 px-2.5 py-0.5 font-bold text-red-200">
                Detail Course
              </span>
              <span className="text-slate-500">/</span>
              <span className="font-mono text-slate-200">{course.course_code}</span>
            </div>

            <h1 className="mt-3 max-w-3xl text-2xl font-bold leading-tight text-white md:text-3xl">
              {course.title}
            </h1>
            <p className="mt-2 max-w-3xl text-sm leading-relaxed text-slate-300">
              {course.description || 'Belum ada deskripsi ringkas untuk course ini.'}
            </p>

            <div className="mt-5 flex flex-wrap items-center gap-2">
              <span className="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-slate-100">
                Kategori: {course.category}
              </span>
              <span className="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-slate-100">
                Status: {course.status}
              </span>
              <span className="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-slate-100">
                Trainer: {course.assigned_trainer_name || 'Belum ditugaskan'}
              </span>
            </div>
          </div>

          {/* Sampul Course & Kontrol Upload */}
          <div className="flex w-full shrink-0 flex-col gap-2.5 sm:w-72">
            <div className="relative aspect-video w-full overflow-hidden rounded-xl border border-white/15 bg-slate-900/90 shadow-inner">
              {course.thumbnail_path && !coverImgFailed ? (
                <img
                  src={resolveCourseThumbnailUrl(course.thumbnail_path)}
                  alt={`Sampul ${course.title}`}
                  onError={() => setCoverImgFailed(true)}
                  className="h-full w-full object-cover"
                />
              ) : (
                <div className="flex h-full w-full flex-col items-center justify-center gap-1.5 text-slate-400">
                  <BookOpen className="size-8 opacity-60" />
                  <span className="text-[11px] font-medium">Belum ada foto sampul</span>
                </div>
              )}
            </div>

            {canManageCover && (
              <div className="flex flex-wrap items-center gap-2">
                <input
                  ref={coverInputRef}
                  type="file"
                  accept="image/jpeg,image/png,image/gif,image/webp"
                  onChange={uploadCover}
                  className="sr-only"
                />
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={uploadingCover || busy}
                  loading={uploadingCover}
                  icon={<ImagePlus className="size-3.5" />}
                  onClick={() => coverInputRef.current?.click()}
                  className="flex-1 border-white/25 bg-white/10 text-white hover:bg-white/20"
                >
                  {course.thumbnail_path ? 'Ganti Sampul' : 'Unggah Sampul'}
                </Button>
                {course.thumbnail_path && (
                  <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    disabled={uploadingCover || busy}
                    icon={<Trash2 className="size-3.5" />}
                    onClick={removeCover}
                    className="border-red-400/40 bg-red-500/15 text-red-200 hover:bg-red-500/30"
                  >
                    Hapus
                  </Button>
                )}
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Ringkasan KPI Sesi & Materi */}
      <section className="grid gap-4 sm:grid-cols-3">
        <StatCard
          label="Sesi Pembelajaran"
          value={sessions.length}
          icon={<CalendarDays className="size-5" />}
          tone="secondary"
          subtext="Pertemuan aktif pada course"
        />
        <StatCard
          label="Materi Terbit"
          value={publishedCount}
          icon={<BookOpen className="size-5" />}
          tone="tertiary"
          subtext="Sudah tampil di portal student"
        />
        <StatCard
          label="Dalam Persetujuan"
          value={pendingCount}
          icon={<Clock className="size-5" />}
          tone="warning"
          subtext="Menunggu review atau terbit"
        />
      </section>

      {/* Daftar Sesi & Materi */}
      <section className="space-y-4">
        <div>
          <p className="text-[11px] font-bold uppercase tracking-[0.14em] text-primary">
            Struktur Pembelajaran
          </p>
          <h2 className="mt-1 text-xl font-bold text-on-surface">Sesi &amp; Materi Course</h2>
          <p className="mt-1 text-sm text-muted">
            Materi terbit langsung tersinkron ke student. Pengajuan dengan status perlu perbaikan dapat diedit kembali.
          </p>
        </div>

        <ListFilterBar
          search={contentSearch}
          onSearch={setContentSearch}
          placeholder="Cari nama sesi, judul materi, atau tipe konten..."
        />

        {visibleSessions.length ? (
          <div className="space-y-5">
            {sessionPage.pagedItems.map((session, index) => {
              const visibleMaterials = session.materials.filter((material) =>
                `${material.title} ${material.material_type} ${material.content || ''}`
                  .toLowerCase()
                  .includes(contentSearch.trim().toLowerCase())
              );
              const visibleSubmissions = (submissionsBySession[session.session_id] || []).filter(
                (item) =>
                  (submissionStatus === 'all' || item.status === submissionStatus) &&
                  `${item.title} ${item.material_type} ${item.submitter_name} ${item.status}`
                    .toLowerCase()
                    .includes(contentSearch.trim().toLowerCase())
              );

              return (
                <article
                  key={session.session_id}
                  className="dls-card-surface overflow-hidden rounded-xl"
                >
                  <div className="flex flex-wrap items-center justify-between gap-3 border-b border-outline/40 bg-surface-low/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/60">
                    <div className="flex items-center gap-3">
                      <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-primary/10 text-sm font-bold text-primary dark:bg-red-950/50 dark:text-red-300">
                        {String((sessionPage.page - 1) * sessionPage.pageSize + index + 1).padStart(
                          2,
                          '0'
                        )}
                      </span>
                      <div>
                        <h3 className="text-base font-bold text-on-surface">{session.title}</h3>
                        <p className="text-xs text-muted">
                          {visibleMaterials.length} materi terbit · {visibleSubmissions.length}{' '}
                          pengajuan tampil
                        </p>
                      </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                      <StatusBadge status={session.status} dot />
                      {canSubmitMaterial && (
                        <Button
                          type="button"
                          size="sm"
                          variant="primary"
                          icon={<Plus className="size-3.5" />}
                          onClick={() => setSubmitModalSessionId(String(session.session_id))}
                        >
                          Tambah Materi
                        </Button>
                      )}
                    </div>
                  </div>

                  {/* Materi Terbit */}
                  <div className="divide-y divide-outline/30 dark:divide-slate-800">
                    {visibleMaterials.map((material) => (
                      <MaterialRow
                        key={material.material_id}
                        material={material}
                        onDetail={(mat) =>
                          setSelectedDetailMaterial({
                            ...mat,
                            course_title: detail?.course?.title,
                            session_title: session.title
                          })
                        }
                        onDelete={deleteFinalMaterial}
                        canDelete={canDeleteAny}
                      />
                    ))}
                    {!visibleMaterials.length && (
                      <p className="px-5 py-4 text-sm text-muted">
                        {contentSearch
                          ? 'Tidak ada materi yang cocok dengan kata kunci pencarian.'
                          : 'Belum ada materi yang diterbitkan pada sesi ini.'}
                      </p>
                    )}
                  </div>

                  {/* Riwayat Pengajuan Materi */}
                  {!!(submissionsBySession[session.session_id] || []).length && (
                    <div className="border-t border-outline/40 bg-surface-low/30 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/30">
                      <div className="flex flex-wrap items-center justify-between gap-2">
                        <h4 className="text-sm font-bold text-on-surface">
                          Alur Persetujuan &amp; Pengajuan Materi
                        </h4>
                        <select
                          value={submissionStatus}
                          onChange={(event) => setSubmissionStatus(event.target.value)}
                          className="h-9 rounded-md border border-outline bg-surface-lowest px-2.5 text-xs text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                          <option value="all">Semua status</option>
                          {submissionStatuses.map((value) => (
                            <option key={value} value={value}>
                              {STATUS_LABEL[value] || value}
                            </option>
                          ))}
                        </select>
                      </div>

                      <div className="mt-3 space-y-3">
                        {visibleSubmissions.map((item) => (
                          <SubmissionRow
                            key={item.submission_id}
                            item={item}
                            currentUser={user}
                            isAdmin={isAdmin}
                            canSendToReviewer={canSendToReviewer}
                            canPublishMaterial={canPublishMaterial}
                            busy={busy}
                            onFile={openSubmissionFile}
                            onSendReview={sendReview}
                            onPublish={publish}
                            onEdit={(sub) => setEditingSubmission(sub)}
                            onReject={(sub) => {
                              setRejectingSubmission(sub);
                              setRejectComment('');
                            }}
                          />
                        ))}
                      </div>
                      {!visibleSubmissions.length && (
                        <p className="py-3 text-sm text-muted">
                          Tidak ada pengajuan materi yang cocok dengan filter status.
                        </p>
                      )}
                    </div>
                  )}
                </article>
              );
            })}
          </div>
        ) : (
          <Card className="border-dashed text-center">
            <div className="mx-auto flex max-w-md flex-col items-center py-8">
              <div className="mb-3 grid size-12 place-items-center rounded-xl bg-primary/10 text-primary">
                <CalendarDays className="size-6" />
              </div>
              <h3 className="font-bold text-on-surface">
                {sessions.length ? 'Tidak ada sesi atau materi yang cocok' : 'Belum ada sesi'}
              </h3>
              <p className="mt-1 text-sm text-muted">
                {sessions.length
                  ? 'Ubah kata kunci pencarian untuk melihat sesi lainnya.'
                  : 'Tambahkan sesi pertama agar trainer dapat mulai mengajukan materi.'}
              </p>
            </div>
          </Card>
        )}

        <Card noPadding className="overflow-hidden">
          <Pagination
            {...sessionPage}
            onPageChange={sessionPage.setPage}
            onPageSizeChange={sessionPage.setPageSize}
            pageSizes={[5, 10, 20, 50]}
          />
        </Card>

        {/* Form Tambah Sesi Baru */}
        {canManageSessions && (
          <Card title="Tambah Sesi Baru" subtitle="Buat pertemuan baru pada struktur course ini.">
            <form onSubmit={addSession} className="flex flex-col gap-3 sm:flex-row">
              <input
                name="title"
                required
                maxLength="200"
                placeholder="Nama sesi baru, mis. Sesi 1: Pengenalan Keselamatan Kerja"
                className={INPUT_CLASS}
              />
              <Button
                type="submit"
                variant="secondary"
                disabled={busy}
                loading={busy}
                icon={<Plus className="size-4" />}
                className="shrink-0"
              >
                Tambah Sesi
              </Button>
            </form>
          </Card>
        )}
      </section>

      {/* Modal Tambah / Ajukan Materi Baru */}
      {submitModalSessionId && (
        <SubmitMaterialModal
          initialCourseId={detail?.course?.course_id || id}
          initialSessionId={submitModalSessionId}
          onClose={() => setSubmitModalSessionId(null)}
          onSuccess={() => {
            load();
          }}
        />
      )}

      {/* Modal Edit Pengajuan */}
      {editingSubmission && (
        <EditMaterialModal
          submission={editingSubmission}
          onClose={() => setEditingSubmission(null)}
          onSuccess={(msg) => {
            setNotice(msg);
            setEditingSubmission(null);
            load();
          }}
        />
      )}

      {/* Modal Detail Materi Terbit */}
      {selectedDetailMaterial && (
        <FinalMaterialDetailModal
          material={selectedDetailMaterial}
          onClose={() => setSelectedDetailMaterial(null)}
          onDelete={deleteFinalMaterial}
          canDelete={canDeleteAny}
        />
      )}

      {/* Modal Penolakan Admin */}
      <Modal
        isOpen={Boolean(rejectingSubmission)}
        onClose={() => setRejectingSubmission(null)}
        title="Tolak Materi & Minta Perbaikan"
        subtitle={
          rejectingSubmission
            ? `Kembalikan "${rejectingSubmission.title}" ke pengaju dengan catatan revisi.`
            : undefined
        }
        maxWidth="max-w-md"
      >
        <form onSubmit={handleAdminReject} className="space-y-4">
          <div>
            <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
              Alasan Penolakan / Catatan Perbaikan *
            </label>
            <textarea
              required
              rows={3}
              value={rejectComment}
              onChange={(e) => setRejectComment(e.target.value)}
              placeholder="Tuliskan bagian yang perlu dilengkapi atau diperbaiki trainer..."
              className="w-full rounded-md border border-outline bg-surface-lowest p-3 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
          </div>
          <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
            <Button type="button" variant="outline" onClick={() => setRejectingSubmission(null)}>
              Batal
            </Button>
            <Button
              type="submit"
              variant="danger"
              loading={busy}
              disabled={busy || rejectComment.trim().length < 3}
            >
              Tolak &amp; Kembalikan
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}

function MaterialRow({ material, onDetail, onDelete, canDelete }) {
  const video = material.material_type === 'video' ? youtubeEmbed(material.content) : null;
  const url = material.fileUrl
    ? `${API_BASE}${material.fileUrl}`
    : material.material_type === 'link'
      ? material.content
      : '';
  const IconComp = TYPE_ICON[material.material_type] || FileText;

  return (
    <div className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
      <div className="flex min-w-0 flex-1 items-start gap-3">
        <span className="grid size-10 shrink-0 place-items-center rounded-lg border border-outline/40 bg-surface-low text-secondary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
          <IconComp className="size-5" />
        </span>
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <p className="font-semibold text-on-surface">{material.title}</p>
            <span className="rounded-md bg-surface-low px-2 py-0.5 text-[11px] font-semibold uppercase text-muted dark:bg-slate-800 dark:text-slate-300">
              {material.material_type}
            </span>
            <StatusBadge status="published" label="Terbit (Final)" dot />
          </div>

          {material.material_type === 'text' && material.content && (
            <div
              className="prose prose-sm prose-slate mt-2 line-clamp-2 max-w-none text-xs text-muted dark:prose-invert"
              dangerouslySetInnerHTML={{ __html: material.content }}
            />
          )}

          {video && (
            <div className="mt-3 aspect-video max-w-xl overflow-hidden rounded-xl bg-black">
              <iframe src={video} title={material.title} className="size-full" allowFullScreen />
            </div>
          )}

          {material.material_type === 'video' && !video && (
            <p className="mt-1 text-xs text-muted">Video diunggah sebagai berkas lokal.</p>
          )}
        </div>
      </div>

      <div className="grid w-full max-w-[220px] shrink-0 grid-cols-2 gap-1.5 pt-1 sm:pt-0">
        <Button
          size="xs"
          variant="info"
          icon={<Eye className="size-3.5" />}
          onClick={() => onDetail(material)}
          className="w-full justify-center"
        >
          Detail
        </Button>

        {url && (
          <a
            href={url}
            target="_blank"
            rel="noreferrer"
            className="inline-flex h-7 w-full items-center justify-center gap-1 rounded-md bg-indigo-600 px-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-indigo-700"
          >
            Buka
            <ExternalLink className="size-3" />
          </a>
        )}

        {canDelete && (
          <Button
            size="xs"
            variant="danger"
            onClick={() => onDelete(material)}
            icon={<Trash2 className="size-3.5" />}
            title="Hapus materi final ini"
            aria-label={`Hapus materi ${material.title}`}
            className="w-full justify-center"
          >
            Hapus
          </Button>
        )}
      </div>
    </div>
  );
}

function SubmissionRow({
  item,
  currentUser,
  isAdmin,
  canSendToReviewer,
  canPublishMaterial,
  busy,
  onFile,
  onSendReview,
  onPublish,
  onEdit,
  onReject
}) {
  const allowSendReview = canSendToReviewer ?? isAdmin;
  const allowPublish = canPublishMaterial ?? isAdmin;
  const previewText =
    item.material_type === 'text'
      ? item.content
      : item.material_type === 'video' || item.material_type === 'link'
        ? item.content
        : '';
  const isSubmitter = item.submitted_by === currentUser?.npk;
  const canTeacherEdit = !allowSendReview && isSubmitter && item.status === 'rejected';
  const canAdminEdit = allowSendReview && (item.status === 'pending_admin' || item.status === 'rejected');
  const canEdit = canAdminEdit || canTeacherEdit;

  return (
    <div className="flex flex-col gap-3 rounded-xl border border-outline/50 bg-surface-lowest p-4 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800 dark:bg-slate-900/70">
      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-2">
          <p className="font-semibold text-on-surface">{item.title}</p>
          <StatusBadge status={item.status} label={STATUS_LABEL[item.status] || item.status} dot />
        </div>

        <p className="mt-1 text-xs text-muted">
          {item.material_type} · diajukan {item.submitter_name}
          {item.reviewer_name ? ` · reviewer ${item.reviewer_name}` : ''}
        </p>

        {previewText &&
          (item.material_type === 'text' ? (
            <div
              className="mt-2 line-clamp-3 text-sm text-muted [&_*]:inline"
              dangerouslySetInnerHTML={{ __html: previewText }}
            />
          ) : (
            <p className="mt-2 line-clamp-3 text-sm text-muted">{previewText}</p>
          ))}

        {item.admin_comment && (
          <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
            <span className="font-bold">Catatan Admin: </span>
            {item.admin_comment}
          </div>
        )}

        {item.reviewer_comment && (
          <div className="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
            <span className="font-bold">Catatan Reviewer: </span>
            {item.reviewer_comment}
          </div>
        )}

        {!allowSendReview && item.status === 'pending_admin' && (
          <p className="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300">
            <Lock className="size-3.5" />
            Terkunci: Menunggu persetujuan Admin sebelum materi dapat diedit kembali.
          </p>
        )}

        {item.status === 'pending_review' && (
          <p className="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300">
            <Lock className="size-3.5" />
            Terkunci: Sedang ditinjau oleh Reviewer.
          </p>
        )}
      </div>

      <div className="flex min-w-0 flex-col gap-2">
        <div className="grid w-full max-w-[230px] grid-cols-2 gap-1.5">
          {item.original_file_name && (
            <Button
              size="xs"
              variant="info"
              icon={<Eye className="size-3.5" />}
              onClick={() => onFile(item.submission_id)}
              className="w-full justify-center"
            >
              Pratinjau
            </Button>
          )}

          {canEdit && (
            <Button
              size="xs"
              variant={item.status === 'rejected' ? 'warning' : 'indigo'}
              onClick={() => onEdit(item)}
              className="w-full justify-center"
            >
              {item.status === 'rejected' ? 'Perbaiki' : 'Edit Materi'}
            </Button>
          )}

          {allowSendReview && item.status === 'pending_admin' && (
            <Button
              size="xs"
              variant="danger"
              onClick={() => onReject(item)}
              className="w-full justify-center"
            >
              Tolak
            </Button>
          )}
        </div>

        {allowSendReview && item.status === 'pending_admin' && (
          <form
            onSubmit={(event) => onSendReview(event, item.submission_id)}
            className="flex min-w-0 flex-col gap-2 rounded-lg border border-outline/50 bg-surface-low/80 p-1.5 sm:flex-row dark:border-slate-700 dark:bg-slate-800/70"
          >
            <input
              type="email"
              name="reviewerEmail"
              autoComplete="email"
              required
              maxLength="254"
              placeholder="Email reviewer"
              aria-label={`Email reviewer untuk ${item.title}`}
              className="h-8 min-w-0 rounded-md border border-outline bg-surface-lowest px-2.5 text-xs text-on-surface outline-none focus:border-primary sm:w-44 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            <Button
              type="submit"
              size="xs"
              variant="secondary"
              disabled={busy}
              icon={<Send className="size-3" />}
            >
              Kirim Review
            </Button>
          </form>
        )}

        {allowPublish && item.status === 'approved' && (
          <Button
            size="xs"
            variant="primary"
            disabled={busy}
            loading={busy}
            icon={<Upload className="size-3.5" />}
            onClick={() => onPublish(item.submission_id)}
          >
            Terbitkan ke Student
          </Button>
        )}
      </div>
    </div>
  );
}
