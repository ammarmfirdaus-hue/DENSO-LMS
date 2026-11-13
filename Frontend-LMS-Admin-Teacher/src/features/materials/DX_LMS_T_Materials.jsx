import { useEffect, useMemo, useState } from 'react';
import { useLocation, useOutletContext } from 'react-router-dom';
import {
  AlertTriangle,
  CheckCircle2,
  Clock,
  Eye,
  File,
  FileBadge,
  FileText,
  Headphones,
  Image,
  Inbox,
  Link2,
  PlayCircle,
  Plus,
  RotateCcw,
  Search,
  Trash2,
  UploadCloud,
  User
} from 'lucide-react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import Modal from '../../components/Modal';
import EditMaterialModal from './EditMaterialModal';
import FinalMaterialDetailModal from './FinalMaterialDetailModal';
import SubmissionPreviewModal from './SubmissionPreviewModal';
import SubmitMaterialModal from './SubmitMaterialModal';
import SubmissionCard from './SubmissionCard';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';
import { hasPermission } from '../../utils/access-control';

const STATUS_LABEL = {
  pending_admin: 'Menunggu admin',
  pending_review: 'Menunggu reviewer',
  approved: 'Disetujui reviewer',
  rejected: 'Perlu diperbaiki',
  published: 'Terbit untuk student'
};

const TYPE_ICON = {
  text: FileText,
  video: PlayCircle,
  pdf: FileBadge,
  image: Image,
  document: File,
  audio: Headphones,
  link: Link2
};

const INPUT_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Halaman Persetujuan & Pengajuan Materi Pembelajaran.
 * Mengelola antrean pengajuan workflow serta katalog materi final yang sudah terbit ke student.
 */
export default function MaterialsPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canSubmitMaterial = hasPermission(user, 'materials.submit');
  const canSendToReviewer = hasPermission(user, 'materials.send_to_reviewer');
  const canReviewMaterial = hasPermission(user, 'materials.review');
  const canPublishMaterial = hasPermission(user, 'materials.publish');
  const canDeleteMaterial = hasPermission(user, 'materials.delete');
  const canBulkManage = isAdmin || canSendToReviewer || canPublishMaterial;

  const [submissions, setSubmissions] = useState([]);
  const [reviewers, setReviewers] = useState([]);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useQueryParamState('status', 'all');

  const [publishedMaterials, setPublishedMaterials] = useState([]);
  const [finalSearch, setFinalSearch] = useState('');
  const [finalTypeFilter, setFinalTypeFilter] = useState('all');

  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [loading, setLoading] = useState(true);
  const [selectedSubmissions, setSelectedSubmissions] = useState(() => new Set());
  const [selectedFinalMaterials, setSelectedFinalMaterials] = useState(() => new Set());

  const [showSubmitModal, setShowSubmitModal] = useState(false);
  const [previewingSubmission, setPreviewingSubmission] = useState(null);
  const [editingSubmission, setEditingSubmission] = useState(null);
  const [rejectingSubmission, setRejectingSubmission] = useState(null);
  const [rejectComment, setRejectComment] = useState('');
  const [reviewRejectingSubmission, setReviewRejectingSubmission] = useState(null);
  const [reviewRejectComment, setReviewRejectComment] = useState('');
  const [selectedDetailMaterial, setSelectedDetailMaterial] = useState(null);
  const [deletingMaterial, setDeletingMaterial] = useState(null);

  const filteredSubmissions = useMemo(
    () =>
      submissions.filter(
        (item) =>
          (status === 'all' || item.status === status) &&
          `${item.title} ${item.course_title} ${item.session_title} ${item.material_type} ${item.submitter_name} ${item.status}`
            .toLowerCase()
            .includes(search.trim().toLowerCase())
      ),
    [submissions, status, search]
  );

  const page = usePagination(filteredSubmissions, 9);

  const filteredFinalMaterials = useMemo(
    () =>
      publishedMaterials.filter(
        (item) =>
          (finalTypeFilter === 'all' || item.material_type === finalTypeFilter) &&
          `${item.title} ${item.course_title} ${item.course_code} ${item.session_title} ${item.material_type} ${item.submitter_name || ''} ${item.submitted_by || ''}`
            .toLowerCase()
            .includes(finalSearch.trim().toLowerCase())
      ),
    [publishedMaterials, finalTypeFilter, finalSearch]
  );

  const finalPage = usePagination(filteredFinalMaterials, 9);

  const summary = useMemo(
    () => ({
      waitingAdmin: submissions.filter((item) => item.status === 'pending_admin').length,
      waitingReview: submissions.filter((item) => item.status === 'pending_review').length,
      rejected: submissions.filter((item) => item.status === 'rejected').length,
      readyToPublish: submissions.filter((item) => item.status === 'approved').length,
      totalFinal: publishedMaterials.length
    }),
    [submissions, publishedMaterials]
  );

  async function load() {
    setLoading(true);
    try {
      const [subRes, pubRes] = await Promise.all([
        api('/material-workflow/submissions'),
        api('/material-workflow/published').catch(() => ({ materials: [] }))
      ]);

      setSubmissions(subRes.submissions || []);
      setPublishedMaterials(pubRes.materials || []);

      if (canSendToReviewer || hasPermission(user, 'reviewers.view')) {
        try {
          const revResult = await api('/material-workflow/reviewers');
          setReviewers(revResult.reviewers || []);
        } catch {
          /* daftar reviewer bersifat opsional */
        }
      }
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Data Materi', e.message);
    } finally {
      setLoading(false);
    }
  }

  const location = useLocation();

  useEffect(() => {
    load();
    window.addEventListener('dls_job_settled', load);
    return () => window.removeEventListener('dls_job_settled', load);
  }, []);

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    if (params.get('action') === 'submit' || location.hash === '#submit-material') {
      setShowSubmitModal(true);
    }
  }, [location.search, location.hash]);

  const selectableSubmissions = filteredSubmissions.filter((item) => item.status !== 'published');
  const selectedFilteredSubmissions = selectableSubmissions.filter((item) =>
    selectedSubmissions.has(String(item.submission_id))
  );
  const selectedFilteredFinalMaterials = filteredFinalMaterials.filter((item) =>
    selectedFinalMaterials.has(String(item.material_id))
  );

  function toggleSelected(setter, id) {
    setter((previous) => {
      const next = new Set(previous);
      const key = String(id);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  function toggleAllSelected(setter, items, selected) {
    setter((previous) => {
      const next = new Set(previous);
      const allSelected =
        items.length > 0 && items.every((item) => selected.has(String(item.id)));
      items.forEach((item) =>
        allSelected ? next.delete(String(item.id)) : next.add(String(item.id))
      );
      return next;
    });
  }

  async function bulkDeleteMaterials(items, kind, clearSelection) {
    if (!isAdmin || !items.length) return;
    const count = items.length;
    const label = kind === 'material_submission' ? 'pengajuan materi' : 'materi final';
    const confirmed = await showAlert.confirm({
      title: `Hapus ${count} ${label}?`,
      text:
        kind === 'material_submission'
          ? 'Pengajuan terpilih beserta file terkait akan dihapus. Materi yang sudah terbit dikelola dari daftar Materi Final.'
          : 'Materi akan ditarik dari LMS dan student. File serta materi terbit terkait akan ikut dihapus.',
      confirmText: `Proses ${count} Data`,
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api('/jobs/records/delete', {
        method: 'POST',
        body: JSON.stringify({
          items: items.map((item) => ({
            kind,
            id: item.id,
            label: `${item.title} (${item.course_title || item.course_code || item.submission_id || item.material_id})`
          }))
        })
      });
      clearSelection(new Set());
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `${result.total} ${label} akan diproses satu per satu. Progress tersimpan jika halaman dimuat ulang.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuat Antrean Hapus', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function sendReview(event, id) {
    event.preventDefault();
    const form = event.currentTarget;
    const reviewerEmail = new FormData(form).get('reviewerEmail');
    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api(`/material-workflow/submissions/${id}/send-review`, {
        method: 'POST',
        body: JSON.stringify({ reviewerEmail })
      });
      setNotice(result.message);
      showAlert.success(
        'Undangan Review Terkirim',
        result.message || 'Tautan peninjauan materi telah berhasil dikirimkan ke email reviewer.'
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
        result.message || 'Catatan perbaikan telah disimpan dan dikirim ke trainer.'
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

  async function handleReviewerApprove(submission) {
    const confirmed = await showAlert.confirm({
      title: 'Setujui Materi Sebagai Reviewer?',
      text: `Materi "${submission.title}" akan ditandai lolos review dan siap diterbitkan.`,
      confirmText: 'Ya, Setujui Materi',
      cancelText: 'Batal',
      icon: 'question'
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/material-workflow/reviewer/submissions/${submission.submission_id}/decision`, {
        method: 'POST',
        body: JSON.stringify({ decision: 'approved', note: 'Disetujui melalui Portal Staff LMS.' })
      });
      setNotice(res.message || 'Materi disetujui.');
      showAlert.success('Keputusan Review Tersimpan', res.message || 'Materi telah disetujui.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menyimpan Review', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleReviewerRejectSubmit(e) {
    e.preventDefault();
    if (!reviewRejectingSubmission || !reviewRejectComment.trim()) return;
    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(
        `/material-workflow/reviewer/submissions/${reviewRejectingSubmission.submission_id}/decision`,
        {
          method: 'POST',
          body: JSON.stringify({ decision: 'rejected', note: reviewRejectComment.trim() })
        }
      );
      setNotice(res.message || 'Materi dikembalikan untuk revisi.');
      showAlert.success('Catatan Revisi Terkirim', res.message || 'Materi dikembalikan ke pengaju.');
      setReviewRejectingSubmission(null);
      setReviewRejectComment('');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengembalikan Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function publish(id) {
    const confirmed = await showAlert.confirm({
      title: 'Terbitkan Materi ke Siswa?',
      text: 'Materi ini akan disinkronisasikan ke portal siswa dan langsung dapat dipelajari oleh seluruh siswa.',
      confirmText: 'Ya, Terbitkan Sekarang',
      cancelText: 'Batal',
      icon: 'question'
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api(`/material-workflow/submissions/${id}/publish`, {
        method: 'POST',
        body: '{}'
      });
      setNotice(result.message);
      showAlert.success(
        'Materi Berhasil Diterbitkan!',
        result.message || 'Materi telah aktif di portal student.'
      );
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menerbitkan Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleConfirmDeleteFinal() {
    if (!deletingMaterial) return;
    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/material-workflow/published/${deletingMaterial.material_id}`, {
        method: 'DELETE'
      });
      setNotice(res.message || 'Materi final berhasil dihapus dari LMS dan database student.');
      showAlert.success(
        'Materi Final Dihapus',
        res.message || 'Materi telah dihapus dari LMS dan ditarik dari database siswa.'
      );
      setDeletingMaterial(null);
      setSelectedDetailMaterial(null);
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleDeleteSubmission(item) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Pengajuan Materi?',
      text: `Apakah Anda yakin ingin menghapus antrean pengajuan materi "${item.title}"? Tindakan ini permanen.`,
      confirmText: 'Ya, Hapus',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/material-workflow/submissions/${item.submission_id}`, {
        method: 'DELETE'
      });
      setNotice(res.message || 'Pengajuan materi berhasil dihapus.');
      showAlert.success('Pengajuan Dihapus', res.message || 'Pengajuan materi berhasil dihapus.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus', e.message);
    } finally {
      setBusy(false);
    }
  }

  const statusOptions = [
    { value: 'all', label: 'Semua status antrean' },
    ...Array.from(new Set(submissions.map((item) => item.status).filter(Boolean))).map((value) => ({
      value,
      label: STATUS_LABEL[value] || value
    }))
  ];

  const typeFilterOptions = [
    { value: 'all', label: 'Semua tipe materi' },
    { value: 'text', label: 'Materi Teks' },
    { value: 'video', label: 'Video YouTube / File' },
    { value: 'pdf', label: 'Dokumen PDF' },
    { value: 'image', label: 'Gambar / Infografis' },
    { value: 'document', label: 'Dokumen Office / ODF' },
    { value: 'audio', label: 'Audio' },
    { value: 'link', label: 'Tautan HTTPS' }
  ];

  return (
    <div className="space-y-6 pb-8">
      <PageHeader
        eyebrow="Alur Persetujuan & Publikasi"
        title="Materi & Persetujuan"
        description="Tinjau pengajuan materi pengajar, kirim evaluasi ke reviewer, terbitkan konten disetujui, dan kelola seluruh materi final di student."
        actions={
          canSubmitMaterial ? (
            <Button
              variant="primary"
              icon={<Plus className="size-4" />}
              onClick={() => setShowSubmitModal(true)}
            >
              Ajukan Materi Baru
            </Button>
          ) : null
        }
      />

      {/* Ringkasan Pipeline */}
      <section aria-label="Ringkasan status materi" className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <StatCard
          label="Menunggu Admin"
          value={summary.waitingAdmin}
          icon={<Clock className="size-5" />}
          tone="warning"
          loading={loading}
        />
        <StatCard
          label="Sedang Direview"
          value={summary.waitingReview}
          icon={<Eye className="size-5" />}
          tone="info"
          loading={loading}
        />
        <StatCard
          label="Perlu Diperbaiki"
          value={summary.rejected}
          icon={<RotateCcw className="size-5" />}
          tone="primary"
          loading={loading}
        />
        <StatCard
          label="Siap Diterbitkan"
          value={summary.readyToPublish}
          icon={<UploadCloud className="size-5" />}
          tone="secondary"
          loading={loading}
        />
        <StatCard
          label="Materi Final Terbit"
          value={summary.totalFinal}
          icon={<CheckCircle2 className="size-5" />}
          tone="tertiary"
          loading={loading}
        />
      </section>

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

      {/* Bagian 1: Antrean & Pengajuan Materi */}
      <Card
        id="materials-submissions"
        className="scroll-mt-20"
        noPadding
        title="Antrean & Pengajuan Materi"
        subtitle="Materi terkunci selama ditinjau Admin atau Reviewer, dan dapat diperbaiki jika dikembalikan."
        action={
          <span className="rounded-full bg-surface-low px-3 py-1 text-xs font-semibold text-on-surface-variant dark:bg-slate-800 dark:text-slate-200">
            {filteredSubmissions.length} dari {submissions.length} antrean
          </span>
        }
      >
        <div className="border-b border-outline/40 p-5 pb-4 dark:border-slate-800">
          <ListFilterBar
            search={search}
            onSearch={setSearch}
            placeholder="Cari judul pengajuan, course, sesi, atau nama pengaju..."
            filter={status}
            onFilter={setStatus}
            label="Filter status antrean"
            options={statusOptions}
          />

          {canBulkManage && canDeleteMaterial && (
            <div className="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-outline/50 bg-surface-low/60 px-3.5 py-2.5 dark:border-slate-800 dark:bg-slate-800/50">
              <label className="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-on-surface-variant sm:text-sm">
                <input
                  type="checkbox"
                  disabled={!selectableSubmissions.length || busy}
                  checked={
                    selectableSubmissions.length > 0 &&
                    selectedFilteredSubmissions.length === selectableSubmissions.length
                  }
                  onChange={() =>
                    toggleAllSelected(
                      setSelectedSubmissions,
                      selectableSubmissions.map((item) => ({ id: item.submission_id })),
                      selectedSubmissions
                    )
                  }
                  className="size-4 rounded accent-primary"
                />
                Pilih semua pengajuan yang tampil ({selectableSubmissions.length})
              </label>

              {selectedFilteredSubmissions.length > 0 && (
                <Button
                  size="sm"
                  variant="danger"
                  disabled={busy}
                  icon={<Trash2 className="size-4" />}
                  onClick={() =>
                    bulkDeleteMaterials(
                      selectedFilteredSubmissions.map((item) => ({
                        ...item,
                        id: item.submission_id
                      })),
                      'material_submission',
                      setSelectedSubmissions
                    )
                  }
                >
                  Hapus Terpilih ({selectedFilteredSubmissions.length})
                </Button>
              )}
            </div>
          )}
        </div>

        {page.pagedItems.length ? (
          <div className="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
            {page.pagedItems.map((item) => (
              <div key={item.submission_id} className="relative">
                {canBulkManage && canDeleteMaterial && item.status !== 'published' && (
                  <label
                    className="absolute left-3 top-3 z-10 inline-flex size-8 cursor-pointer items-center justify-center rounded-md border border-outline/60 bg-surface-lowest shadow-xs dark:border-slate-700 dark:bg-slate-900"
                    title="Pilih pengajuan"
                  >
                    <input
                      type="checkbox"
                      aria-label={`Pilih pengajuan ${item.title}`}
                      disabled={busy}
                      checked={selectedSubmissions.has(String(item.submission_id))}
                      onChange={() => toggleSelected(setSelectedSubmissions, item.submission_id)}
                      className="size-4 accent-primary"
                    />
                  </label>
                )}

                <SubmissionCard
                  item={item}
                  currentUser={user}
                  isAdmin={isAdmin}
                  canSendToReviewer={canSendToReviewer}
                  canPublishMaterial={canPublishMaterial}
                  canReviewMaterial={canReviewMaterial}
                  canDeleteAll={canBulkManage && canDeleteMaterial}
                  reviewers={reviewers}
                  busy={busy}
                  onPreview={() => setPreviewingSubmission(item)}
                  onEdit={() => setEditingSubmission(item)}
                  onReject={() => {
                    setRejectingSubmission(item);
                    setRejectComment('');
                  }}
                  onSendReview={(event) => sendReview(event, item.submission_id)}
                  onReviewerApprove={() => handleReviewerApprove(item)}
                  onReviewerReject={() => {
                    setReviewRejectingSubmission(item);
                    setReviewRejectComment('');
                  }}
                  onPublish={() => publish(item.submission_id)}
                  onDelete={() => handleDeleteSubmission(item)}
                />
              </div>
            ))}
          </div>
        ) : (
          <div className="px-6 py-12 text-center">
            <div className="mx-auto grid size-12 place-items-center rounded-xl bg-surface-low text-muted dark:bg-slate-800">
              <Inbox className="size-6" />
            </div>
            <h3 className="mt-3 text-base font-bold text-on-surface">
              {submissions.length ? 'Tidak ada pengajuan yang cocok' : 'Antrean pengajuan masih kosong'}
            </h3>
            <p className="mx-auto mt-1 max-w-md text-xs text-muted">
              {submissions.length
                ? 'Ubah kata kunci pencarian atau pilih status lain untuk melihat data.'
                : 'Materi baru yang diajukan trainer akan tampil di sini untuk proses tinjauan.'}
            </p>
          </div>
        )}

        <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
      </Card>

      {/* Bagian 2: Materi Final / Terbit ke Student */}
      <div id="published-materials-section" className="scroll-mt-20">
      <Card
        noPadding
        title="Materi Final / Terbit ke Student"
        subtitle="Daftar materi resmi yang aktif di portal student. Mode baca-saja, dapat ditarik kembali bila diperlukan."
        action={
          <StatusBadge
            status="published"
            label={`${filteredFinalMaterials.length} materi final tersedia`}
            dot
          />
        }
      >
        <div className="border-b border-outline/40 bg-surface-low/40 p-5 dark:border-slate-800 dark:bg-slate-800/30">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="relative flex-1">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
              <input
                value={finalSearch}
                onChange={(e) => setFinalSearch(e.target.value)}
                placeholder="Cari materi final, judul, course, sesi, atau nama pengajar..."
                className={`${INPUT_CLASS} pl-9`}
              />
            </div>

            <select
              value={finalTypeFilter}
              onChange={(e) => setFinalTypeFilter(e.target.value)}
              aria-label="Filter tipe materi final"
              className="h-10 rounded-md border border-outline bg-surface-lowest px-3 text-xs font-semibold text-on-surface outline-none transition focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              {typeFilterOptions.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </div>

          {canBulkManage && canDeleteMaterial && (
            <div className="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-outline/50 bg-surface-lowest px-3.5 py-2.5 dark:border-slate-700 dark:bg-slate-900">
              <label className="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-on-surface-variant sm:text-sm">
                <input
                  type="checkbox"
                  disabled={!filteredFinalMaterials.length || busy}
                  checked={
                    filteredFinalMaterials.length > 0 &&
                    selectedFilteredFinalMaterials.length === filteredFinalMaterials.length
                  }
                  onChange={() =>
                    toggleAllSelected(
                      setSelectedFinalMaterials,
                      filteredFinalMaterials.map((item) => ({ id: item.material_id })),
                      selectedFinalMaterials
                    )
                  }
                  className="size-4 rounded accent-primary"
                />
                Pilih semua materi yang tampil ({filteredFinalMaterials.length})
              </label>

              {selectedFilteredFinalMaterials.length > 0 && (
                <Button
                  size="sm"
                  variant="danger"
                  disabled={busy}
                  icon={<Trash2 className="size-4" />}
                  onClick={() =>
                    bulkDeleteMaterials(
                      selectedFilteredFinalMaterials.map((item) => ({
                        ...item,
                        id: item.material_id
                      })),
                      'published_material',
                      setSelectedFinalMaterials
                    )
                  }
                >
                  Hapus Terpilih ({selectedFilteredFinalMaterials.length})
                </Button>
              )}
            </div>
          )}
        </div>

        {finalPage.pagedItems.length ? (
          <div className="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
            {finalPage.pagedItems.map((item) => {
              const IconComp = TYPE_ICON[item.material_type] || FileText;
              const canDelete =
                canDeleteMaterial &&
                (item.canDelete ??
                  (isAdmin ||
                    canBulkManage ||
                    item.submitted_by === user?.npk ||
                    item.assigned_trainer_npk === user?.npk ||
                    item.course_created_by === user?.npk));

              return (
                <article
                  key={item.material_id}
                  className="dls-card-surface group relative flex flex-col justify-between overflow-hidden rounded-xl transition hover:-translate-y-0.5 hover:shadow-md"
                >
                  <div className="h-1 w-full bg-tertiary" />

                  {canBulkManage && canDeleteMaterial && (
                    <label
                      className="absolute right-3 top-4 z-10 inline-flex size-8 cursor-pointer items-center justify-center rounded-md border border-outline/60 bg-surface-lowest shadow-xs dark:border-slate-700 dark:bg-slate-900"
                      title="Pilih materi final"
                    >
                      <input
                        type="checkbox"
                        aria-label={`Pilih materi ${item.title}`}
                        disabled={busy}
                        checked={selectedFinalMaterials.has(String(item.material_id))}
                        onChange={() =>
                          toggleSelected(setSelectedFinalMaterials, item.material_id)
                        }
                        className="size-4 accent-primary"
                      />
                    </label>
                  )}

                  <div className="p-5">
                    <div className="flex items-start justify-between gap-2 pr-8">
                      <span className="grid size-10 place-items-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <IconComp className="size-5" />
                      </span>
                      <div className="flex flex-wrap items-center gap-1.5">
                        <span className="rounded-md bg-surface-low px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-muted dark:bg-slate-800 dark:text-slate-300">
                          {item.material_type}
                        </span>
                        <StatusBadge status="published" label="Final" dot />
                      </div>
                    </div>

                    <h3 className="mt-3 line-clamp-2 font-bold leading-snug text-on-surface group-hover:text-primary">
                      {item.title}
                    </h3>
                    <p className="mt-1 truncate text-xs font-semibold text-secondary dark:text-slate-300">
                      {item.course_title || item.course_code} <span className="text-muted">/</span>{' '}
                      {item.session_title}
                    </p>

                    <div className="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-muted">
                      <span className="inline-flex items-center gap-1">
                        <User className="size-3.5" />
                        {item.submitter_name || item.submitted_by || 'Trainer'}
                      </span>
                      <span>·</span>
                      <span>
                        {item.created_at
                          ? new Date(item.created_at).toLocaleDateString('id-ID', {
                              dateStyle: 'medium'
                            })
                          : '-'}
                      </span>
                    </div>

                    {item.material_type === 'text' && item.content && (
                      <div className="mt-3 line-clamp-2 rounded-lg border border-outline/40 bg-surface-low/60 p-2.5 text-xs text-muted dark:border-slate-800 dark:bg-slate-900/60">
                        <div dangerouslySetInnerHTML={{ __html: item.content.slice(0, 160) }} />
                      </div>
                    )}
                  </div>

                  <div className="flex items-center justify-between gap-2 border-t border-outline/40 bg-surface-low/30 px-5 py-3 dark:border-slate-800 dark:bg-slate-800/30">
                    <Button
                      size="xs"
                      variant="outline"
                      icon={<Eye className="size-3.5" />}
                      onClick={() => setSelectedDetailMaterial(item)}
                      className="flex-1"
                    >
                      Lihat Detail
                    </Button>

                    {canDelete && (
                      <Button
                        size="xs"
                        variant="danger"
                        onClick={() => setDeletingMaterial(item)}
                        icon={<Trash2 className="size-3.5" />}
                        title="Hapus materi final ini dari LMS & student"
                        aria-label={`Hapus materi final ${item.title}`}
                      />
                    )}
                  </div>
                </article>
              );
            })}
          </div>
        ) : (
          <div className="px-6 py-12 text-center">
            <div className="mx-auto grid size-12 place-items-center rounded-xl bg-surface-low text-muted dark:bg-slate-800">
              <CheckCircle2 className="size-6" />
            </div>
            <h3 className="mt-3 text-base font-bold text-on-surface">
              {publishedMaterials.length
                ? 'Tidak ada materi final yang cocok'
                : 'Belum ada materi final'}
            </h3>
            <p className="mx-auto mt-1 max-w-md text-xs text-muted">
              {publishedMaterials.length
                ? 'Sesuaikan kata kunci pencarian atau pilih tipe materi lain.'
                : 'Materi yang telah disetujui reviewer dan diterbitkan admin akan tampil di sini.'}
            </p>
          </div>
        )}

        <Pagination
          {...finalPage}
          onPageChange={finalPage.setPage}
          onPageSizeChange={finalPage.setPageSize}
        />
      </Card>
      </div>

      {/* Modal Detail View-Only Materi Final */}
      {selectedDetailMaterial && (
        <FinalMaterialDetailModal
          material={selectedDetailMaterial}
          onClose={() => setSelectedDetailMaterial(null)}
          onDelete={(mat) => setDeletingMaterial(mat)}
          canDelete={
            canDeleteMaterial &&
            (selectedDetailMaterial.canDelete ??
              (isAdmin ||
                canBulkManage ||
                selectedDetailMaterial.submitted_by === user?.npk ||
                selectedDetailMaterial.assigned_trainer_npk === user?.npk ||
                selectedDetailMaterial.course_created_by === user?.npk))
          }
        />
      )}

      {/* Modal Konfirmasi Hapus Materi Final */}
      <Modal
        isOpen={Boolean(deletingMaterial)}
        onClose={() => setDeletingMaterial(null)}
        title="Hapus Materi Final"
        subtitle="Tindakan ini permanen dan langsung menyinkronkan penarikan materi di student."
        maxWidth="max-w-md"
      >
        {deletingMaterial && (
          <div className="space-y-4">
            <div className="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
              <AlertTriangle className="mt-0.5 size-4 shrink-0 text-red-600" />
              <p>
                Materi &ldquo;<strong>{deletingMaterial.title}</strong>&rdquo; akan dihapus dari LMS dan ditarik dari tampilan student.
              </p>
            </div>

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button type="button" variant="outline" onClick={() => setDeletingMaterial(null)}>
                Batal
              </Button>
              <Button
                type="button"
                variant="danger"
                loading={busy}
                disabled={busy}
                onClick={handleConfirmDeleteFinal}
              >
                Ya, Hapus Materi
              </Button>
            </div>
          </div>
        )}
      </Modal>

      {/* Modal Pratinjau Antrean Pengajuan */}
      {previewingSubmission && (
        <SubmissionPreviewModal
          submission={previewingSubmission}
          onClose={() => setPreviewingSubmission(null)}
          onEdit={(sub) => {
            setPreviewingSubmission(null);
            setEditingSubmission(sub);
          }}
          onOpenReject={(sub) => {
            setPreviewingSubmission(null);
            setRejectingSubmission(sub);
            setRejectComment('');
          }}
          onReviewerApprove={(sub) => {
            setPreviewingSubmission(null);
            handleReviewerApprove(sub);
          }}
          onReviewerReject={(sub) => {
            setPreviewingSubmission(null);
            setReviewRejectingSubmission(sub);
            setReviewRejectComment('');
          }}
          onPublish={(subId) => {
            setPreviewingSubmission(null);
            publish(subId);
          }}
          canEdit={
            (canSendToReviewer &&
              (previewingSubmission.status === 'pending_admin' ||
                previewingSubmission.status === 'rejected')) ||
            (!canSendToReviewer &&
              previewingSubmission.submitted_by === user?.npk &&
              previewingSubmission.status === 'rejected')
          }
          isAdmin={isAdmin}
          canSendToReviewer={canSendToReviewer}
          canReviewMaterial={canReviewMaterial}
          canPublishMaterial={canPublishMaterial}
          busy={busy}
        />
      )}

      {/* Modal Edit Pengajuan Materi */}
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

      {/* Modal Penolakan Admin */}
      <Modal
        isOpen={Boolean(rejectingSubmission)}
        onClose={() => setRejectingSubmission(null)}
        title="Tolak Materi & Minta Perbaikan"
        subtitle={
          rejectingSubmission
            ? `Materi "${rejectingSubmission.title}" akan dikembalikan ke trainer untuk revisi.`
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
              placeholder="Tuliskan bagian materi yang perlu diperbaiki oleh trainer..."
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

      {/* Modal Catatan Revisi Reviewer */}
      <Modal
        isOpen={Boolean(reviewRejectingSubmission)}
        onClose={() => setReviewRejectingSubmission(null)}
        title="Kembalikan Materi Dari Review"
        subtitle={
          reviewRejectingSubmission
            ? `Tuliskan catatan evaluasi Reviewer untuk materi "${reviewRejectingSubmission.title}".`
            : undefined
        }
        maxWidth="max-w-md"
      >
        <form onSubmit={handleReviewerRejectSubmit} className="space-y-4">
          <div>
            <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
              Catatan Reviewer *
            </label>
            <textarea
              required
              rows={3}
              value={reviewRejectComment}
              onChange={(e) => setReviewRejectComment(e.target.value)}
              placeholder="Tuliskan bagian materi yang perlu diperbaiki..."
              className="w-full rounded-md border border-outline bg-surface-lowest p-3 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
          </div>

          <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
            <Button type="button" variant="outline" onClick={() => setReviewRejectingSubmission(null)}>
              Batal
            </Button>
            <Button
              type="submit"
              variant="danger"
              loading={busy}
              disabled={busy || reviewRejectComment.trim().length < 3}
            >
              Kirim Revisi Review
            </Button>
          </div>
        </form>
      </Modal>

      {/* Modal Pengajuan Materi Baru */}
      {showSubmitModal && canSubmitMaterial && (
        <SubmitMaterialModal onClose={() => setShowSubmitModal(false)} onSuccess={load} />
      )}
    </div>
  );
}
