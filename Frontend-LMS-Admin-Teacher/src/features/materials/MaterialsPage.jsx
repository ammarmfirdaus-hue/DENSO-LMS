import { useEffect, useMemo, useState } from 'react';
import { Link, useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import EditMaterialModal from './EditMaterialModal';
import FinalMaterialDetailModal from './FinalMaterialDetailModal';
import SubmissionPreviewModal from './SubmissionPreviewModal';
import SubmitMaterialModal from './SubmitMaterialModal';
import SubmissionCard from './SubmissionCard';
import showAlert from '../../utils/alert';

const statusLabel = {
  pending_admin: 'Menunggu admin',
  pending_review: 'Menunggu reviewer',
  approved: 'Disetujui reviewer',
  rejected: 'Perlu diperbaiki',
  published: 'Terbit untuk student'
};
const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';

function getYoutubeEmbed(url) {
  if (!url) return null;
  try {
    const parsed = new URL(url);
    if (parsed.hostname.includes('youtu.be')) {
      const id = parsed.pathname.replace(/^\//, '');
      return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
    }
    if (parsed.hostname.includes('youtube.com')) {
      const id = parsed.searchParams.get('v');
      return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
    }
  } catch {
    return null;
  }
  return null;
}

function resolveFileUrl(url) {
  if (!url) return null;
  if (/^https?:\/\//i.test(url)) return url;
  const origin = API_BASE.replace('/api/v1', '');
  return `${origin}${url.startsWith('/') ? '' : '/'}${url}`;
}

function statusStyle(status) {
  if (status === 'approved' || status === 'published') return 'bg-emerald-50 text-emerald-800 ring-emerald-600/15';
  if (status === 'rejected') return 'bg-red-50 text-red-800 ring-red-600/15';
  if (status === 'pending_review') return 'bg-blue-50 text-blue-800 ring-blue-600/15';
  return 'bg-amber-50 text-amber-900 ring-amber-600/15';
}

const TYPE_ICONS = {
  text: 'article',
  video: 'play_circle',
  pdf: 'picture_as_pdf',
  image: 'image',
  document: 'description',
  audio: 'audiotrack',
  link: 'link'
};

export default function MaterialsPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';

  // Submissions (Workflow Antrean)
  const [submissions, setSubmissions] = useState([]);
  const [reviewers, setReviewers] = useState([]);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('all');

  // Final / Published Materials (Semua Pengajar)
  const [publishedMaterials, setPublishedMaterials] = useState([]);
  const [finalSearch, setFinalSearch] = useState('');
  const [finalTypeFilter, setFinalTypeFilter] = useState('all');

  // General State
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);

  // Modals
  const [showSubmitModal, setShowSubmitModal] = useState(false);
  const [previewingSubmission, setPreviewingSubmission] = useState(null);
  const [editingSubmission, setEditingSubmission] = useState(null);
  const [rejectingSubmission, setRejectingSubmission] = useState(null);
  const [rejectComment, setRejectComment] = useState('');
  const [selectedDetailMaterial, setSelectedDetailMaterial] = useState(null);
  const [deletingMaterial, setDeletingMaterial] = useState(null);

  // Filter Submissions
  const filteredSubmissions = useMemo(() => submissions.filter(item =>
    (status === 'all' || item.status === status) &&
    `${item.title} ${item.course_title} ${item.session_title} ${item.material_type} ${item.submitter_name} ${item.status}`.toLowerCase().includes(search.trim().toLowerCase())
  ), [submissions, status, search]);

  const page = usePagination(filteredSubmissions, 9);

  // Filter Final Materials
  const filteredFinalMaterials = useMemo(() => publishedMaterials.filter(item =>
    (finalTypeFilter === 'all' || item.material_type === finalTypeFilter) &&
    `${item.title} ${item.course_title} ${item.course_code} ${item.session_title} ${item.material_type} ${item.submitter_name || ''} ${item.submitted_by || ''}`.toLowerCase().includes(finalSearch.trim().toLowerCase())
  ), [publishedMaterials, finalTypeFilter, finalSearch]);

  const finalPage = usePagination(filteredFinalMaterials, 9);

  const summary = useMemo(() => ({
    waitingAdmin: submissions.filter(item => item.status === 'pending_admin').length,
    waitingReview: submissions.filter(item => item.status === 'pending_review').length,
    rejected: submissions.filter(item => item.status === 'rejected').length,
    readyToPublish: submissions.filter(item => item.status === 'approved').length,
    totalFinal: publishedMaterials.length
  }), [submissions, publishedMaterials]);

  async function load() {
    try {
      const [subRes, pubRes] = await Promise.all([
        api('/material-workflow/submissions'),
        api('/material-workflow/published').catch(() => ({ materials: [] }))
      ]);

      setSubmissions(subRes.submissions || []);
      setPublishedMaterials(pubRes.materials || []);

      if (isAdmin) {
        try {
          const revResult = await api('/material-workflow/reviewers');
          setReviewers(revResult.reviewers || []);
        } catch { /* optional reviewers list */ }
      }
      setError('');
    } catch (e) {
      setError(e.message);
    }
  }

  useEffect(() => { load(); }, []);

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
      showAlert.success('Undangan Review Terkirim', result.message || 'Tautan peninjauan materi telah berhasil dikirimkan ke email reviewer.');
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
      const result = await api(`/material-workflow/submissions/${rejectingSubmission.submission_id}/admin-reject`, {
        method: 'POST',
        body: JSON.stringify({ comment: rejectComment.trim() })
      });
      setNotice(result.message);
      showAlert.success('Materi Dikembalikan', result.message || 'Catatan perbaikan telah disimpan dan dikirim ke trainer.');
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
      const result = await api(`/material-workflow/submissions/${id}/publish`, { method: 'POST', body: '{}' });
      setNotice(result.message);
      showAlert.success('Materi Berhasil Diterbitkan!', result.message || 'Materi telah aktif di portal student.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menerbitkan Materi', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function openFile(id) {
    try {
      const response = await fetch(`${API_BASE}/material-workflow/submissions/${id}/file`, { credentials: 'include' });
      if (!response.ok) throw new Error('File tidak dapat dibuka.');
      const url = URL.createObjectURL(await response.blob());
      window.open(url, '_blank', 'noopener');
      setTimeout(() => URL.revokeObjectURL(url), 60000);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuka File', e.message);
    }
  }

  /** Hapus materi final yang sudah terbit */
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
      showAlert.success('Materi Final Dihapus', res.message || 'Materi telah dihapus dari LMS dan ditarik dari database siswa.');
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

  /** Hapus pengajuan workflow yang belum final (rejected/draft) */
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
    ...Array.from(new Set(submissions.map(item => item.status).filter(Boolean))).map(value => ({ value, label: statusLabel[value] || value }))
  ];

  const typeFilterOptions = [
    { value: 'all', label: 'Semua tipe materi' },
    { value: 'text', label: 'Materi Teks' },
    { value: 'video', label: 'Video YouTube / File' },
    { value: 'pdf', label: 'Dokumen PDF' },
    { value: 'image', label: 'Gambar / Infografis' },
    { value: 'document', label: 'Dokumen Office / ODF' },
    { value: 'audio', label: 'Audio' },
    { value: 'link', label: 'Tautan HTTPS' },
  ];

  return (
    <div className="mx-auto max-w-7xl space-y-8 pb-12">
      {/* ── HEADER ── */}
      <header className="mb-1">
        <p className="text-xs font-bold uppercase tracking-[.18em] text-primary">Pengelolaan pembelajaran</p>
        <h1 className="mt-2 text-3xl font-bold tracking-tight">Antrean &amp; Persetujuan Materi</h1>
        <p className="mt-2 max-w-3xl text-sm leading-6 text-muted">
          Tinjau alur pengajuan materi, kelola perbaikan, kirim tautan evaluasi ke reviewer HRD/manajemen, terbitkan materi yang disetujui, serta pantau seluruh materi final yang sudah aktif di student.
        </p>
      </header>

      {/* ── RINGKASAN STATUS ── */}
      <section aria-label="Ringkasan status materi" className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <SummaryCard label="Menunggu admin" value={summary.waitingAdmin} icon="hourglass_top" />
        <SummaryCard label="Sedang direview" value={summary.waitingReview} icon="rate_review" />
        <SummaryCard label="Perlu diperbaiki" value={summary.rejected} icon="assignment_late" />
        <SummaryCard label="Siap diunggah" value={summary.readyToPublish} icon="publish" />
        <SummaryCard label="Materi final terbit" value={summary.totalFinal} icon="verified" highlight />
      </section>

      {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{error}</p>}
      {notice && <p role="status" className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{notice}</p>}

      {/* ── BAGIAN ATAS: ANTREAN & PENGAJUAN MATERI WORKFLOW ── */}
      <section className="overflow-hidden rounded-3xl border border-outline/50 bg-white shadow-sm">
        <div className="flex flex-col gap-3 border-b border-outline/50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
          <div>
            <h2 className="text-lg font-bold">Daftar Antrean &amp; Pengajuan Materi</h2>
            <p className="mt-0.5 text-xs text-muted">
              Materi yang sedang diproses persetujuan. Materi terkunci saat menunggu Admin atau Reviewer, dan dapat diedit kembali jika diminta perbaikan.
            </p>
          </div>
          <div className="flex flex-wrap items-center gap-2.5">
            <span className="w-fit rounded-full bg-surface-low px-3 py-1.5 text-xs font-bold text-secondary">
              {filteredSubmissions.length} dari {submissions.length} antrean
            </span>
            <button
              type="button"
              onClick={() => setShowSubmitModal(true)}
              className="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:brightness-110 active:scale-95 transition cursor-pointer"
            >
              <span className="material-symbol text-base">add_circle</span>
              Ajukan Materi Baru
            </button>
          </div>
        </div>

        <div className="p-4 pb-1 sm:p-6 sm:pb-2">
          <ListFilterBar
            search={search}
            onSearch={setSearch}
            placeholder="Cari pengajuan, course, sesi, atau pengaju..."
            filter={status}
            onFilter={setStatus}
            label="Filter status antrean"
            options={statusOptions}
          />
        </div>

        {page.pagedItems.length ? (
          <div className="grid gap-4 p-4 pt-2 sm:grid-cols-2 lg:grid-cols-3 sm:p-6 sm:pt-3">
            {page.pagedItems.map(item => (
              <SubmissionCard
                key={item.submission_id}
                item={item}
                currentUser={user}
                isAdmin={isAdmin}
                reviewers={reviewers}
                busy={busy}
                onPreview={() => setPreviewingSubmission(item)}
                onEdit={() => setEditingSubmission(item)}
                onReject={() => { setRejectingSubmission(item); setRejectComment(''); }}
                onSendReview={event => sendReview(event, item.submission_id)}
                onPublish={() => publish(item.submission_id)}
                onDelete={() => handleDeleteSubmission(item)}
              />
            ))}
          </div>
        ) : (
          <div className="p-10 text-center sm:p-14">
            <span className="material-symbol mx-auto grid size-14 place-items-center rounded-2xl bg-surface-low text-3xl text-muted" aria-hidden="true">inbox</span>
            <h3 className="mt-4 font-bold">{submissions.length ? 'Tidak ada hasil yang cocok' : 'Antrean masih kosong'}</h3>
            <p className="mx-auto mt-1 max-w-md text-sm leading-6 text-muted">
              {submissions.length ? 'Coba ubah kata pencarian atau filter status.' : 'Materi yang diajukan pengajar akan muncul di sini untuk ditinjau.'}
            </p>
          </div>
        )}

        <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
      </section>

      {/* ── BAGIAN BAWAH: MATERI FINAL / TERBIT KE STUDENT (SEMUA PENGAJAR) ── */}
      <section className="overflow-hidden rounded-3xl border border-outline/50 bg-white dark:bg-slate-900 shadow-sm ring-1 ring-black/5">
        <div className="flex flex-col gap-3 border-b border-outline/50 bg-white dark:bg-slate-900 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <span className="grid size-12 place-items-center rounded-2xl bg-emerald-600 text-white shadow-xs">
              <span className="material-symbol text-2xl" aria-hidden="true">verified</span>
            </span>
            <div>
              <div className="flex items-center gap-2">
                <h2 className="text-xl font-bold text-on-surface">Materi Final / Terbit ke Student</h2>
                <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                  Semua Pengajar
                </span>
              </div>
              <p className="mt-0.5 text-xs text-muted">
                Daftar materi resmi yang telah diunggah ke database student dari seluruh teacher &amp; admin. Materi bersifat <strong>View-Only</strong> dan dapat dihapus jika ditarik kembali.
              </p>
            </div>
          </div>
          <span className="w-fit shrink-0 rounded-full bg-white px-3.5 py-1.5 text-xs font-bold text-emerald-800 shadow-xs ring-1 ring-inset ring-emerald-600/20">
            {filteredFinalMaterials.length} materi final tersedia
          </span>
        </div>

        {/* Filter bar for final materials */}
        <div className="border-b border-outline/30 bg-surface-low/30 p-4 sm:px-6">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="relative flex-1">
              <span className="material-symbol absolute left-3 top-1/2 -translate-y-1/2 text-lg text-muted" aria-hidden="true">
                search
              </span>
              <input
                value={finalSearch}
                onChange={e => setFinalSearch(e.target.value)}
                placeholder="Cari materi final, judul, course, sesi, atau nama pengajar..."
                className="h-10 w-full rounded-xl border border-outline/60 bg-white pl-9 pr-3 text-xs outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
              />
            </div>
            <div className="flex items-center gap-2">
              <select
                value={finalTypeFilter}
                onChange={e => setFinalTypeFilter(e.target.value)}
                aria-label="Filter tipe materi final"
                className="h-10 rounded-xl border border-outline/60 bg-white px-3 text-xs font-semibold text-secondary outline-none transition focus:border-primary"
              >
                {typeFilterOptions.map(opt => (
                  <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
              </select>
            </div>
          </div>
        </div>

        {/* Final Materials Grid */}
        {finalPage.pagedItems.length ? (
          <div className="grid gap-4 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
            {finalPage.pagedItems.map(item => {
              const canDelete = item.canDelete ?? (
                isAdmin ||
                item.submitted_by === user?.npk ||
                item.assigned_trainer_npk === user?.npk ||
                item.course_created_by === user?.npk
              );

              return (
                <article
                  key={item.material_id}
                  className="group flex flex-col justify-between rounded-2xl border border-outline/40 bg-white p-5 shadow-xs transition hover:border-emerald-500/40 hover:shadow-md"
                >
                  <div>
                    {/* Header: Type icon & Badges */}
                    <div className="flex items-start justify-between gap-2">
                      <span className="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700">
                        <span className="material-symbol text-xl" aria-hidden="true">
                          {TYPE_ICONS[item.material_type] || 'article'}
                        </span>
                      </span>
                      <div className="flex items-center gap-1.5">
                        <span className="rounded-full bg-surface-low px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-muted">
                          {item.material_type}
                        </span>
                        <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
                          ✓ Final
                        </span>
                      </div>
                    </div>

                    {/* Judul & Course */}
                    <h3 className="mt-3 font-bold text-on-surface line-clamp-2 leading-snug group-hover:text-primary">
                      {item.title}
                    </h3>
                    <p className="mt-1 text-xs font-semibold text-secondary truncate">
                      {item.course_title || item.course_code} <span className="text-outline">/</span> {item.session_title}
                    </p>

                    {/* Submitter & Tanggal */}
                    <div className="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-muted">
                      <span className="inline-flex items-center gap-1">
                        <span className="material-symbol text-xs" aria-hidden="true">person</span>
                        {item.submitter_name || item.submitted_by || 'Trainer'}
                      </span>
                      <span>·</span>
                      <span>
                        {item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-'}
                      </span>
                    </div>

                    {/* Text Preview Snippet if Text */}
                    {item.material_type === 'text' && item.content && (
                      <div className="mt-3 line-clamp-2 rounded-lg bg-surface-low/50 p-2 text-xs text-muted">
                        <div dangerouslySetInnerHTML={{ __html: item.content.slice(0, 160) }} />
                      </div>
                    )}
                  </div>

                  {/* Actions: View Detail & Delete Only (No Edit, No Add) */}
                  <div className="mt-5 flex items-center justify-between gap-2 border-t border-outline/30 pt-3">
                    <button
                      type="button"
                      onClick={() => setSelectedDetailMaterial(item)}
                      className="inline-flex flex-1 min-h-9 items-center justify-center gap-1.5 rounded-xl bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-primary hover:text-white"
                    >
                      <span className="material-symbol text-base" aria-hidden="true">visibility</span>
                      Lihat Detail
                    </button>

                    {canDelete && (
                      <button
                        type="button"
                        onClick={() => setDeletingMaterial(item)}
                        title="Hapus materi final ini dari LMS & student"
                        className="grid size-9 shrink-0 place-items-center rounded-xl border border-red-200 text-red-600 transition hover:bg-red-50 hover:text-red-700"
                      >
                        <span className="material-symbol text-base" aria-hidden="true">delete</span>
                      </button>
                    )}
                  </div>
                </article>
              );
            })}
          </div>
        ) : (
          <div className="p-12 text-center">
            <span className="material-symbol mx-auto grid size-14 place-items-center rounded-2xl bg-surface-low text-3xl text-muted" aria-hidden="true">
              assignment_turned_in
            </span>
            <h3 className="mt-4 font-bold text-secondary">
              {publishedMaterials.length ? 'Tidak ada materi final yang cocok' : 'Belum ada materi final'}
            </h3>
            <p className="mx-auto mt-1 max-w-md text-xs leading-5 text-muted">
              {publishedMaterials.length
                ? 'Silakan sesuaikan kata kunci pencarian atau filter tipe materi.'
                : 'Materi yang disetujui reviewer dan diterbitkan admin ke student akan tampil secara otomatis di sini.'}
            </p>
          </div>
        )}

        <Pagination {...finalPage} onPageChange={finalPage.setPage} onPageSizeChange={finalPage.setPageSize} />
      </section>

      {/* ── MODAL VIEW-ONLY DETAIL MATERI FINAL ── */}
      {selectedDetailMaterial && (
        <FinalMaterialDetailModal
          material={selectedDetailMaterial}
          onClose={() => setSelectedDetailMaterial(null)}
          onDelete={mat => setDeletingMaterial(mat)}
          canDelete={
            selectedDetailMaterial.canDelete ?? (
              isAdmin ||
              selectedDetailMaterial.submitted_by === user?.npk ||
              selectedDetailMaterial.assigned_trainer_npk === user?.npk ||
              selectedDetailMaterial.course_created_by === user?.npk
            )
          }
        />
      )}

      {/* ── MODAL KONFIRMASI HAPUS MATERI FINAL ── */}
      {deletingMaterial && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-black/5">
            <div className="flex items-center gap-3">
              <span className="grid size-11 place-items-center rounded-2xl bg-red-100 text-red-700">
                <span className="material-symbol text-2xl" aria-hidden="true">warning</span>
              </span>
              <div>
                <h3 className="text-base font-bold text-red-800">Hapus Materi Final</h3>
                <p className="text-xs text-muted">Tindakan ini permanen.</p>
              </div>
            </div>

            <p className="mt-4 text-xs leading-relaxed text-secondary">
              Apakah Anda yakin ingin menghapus materi final &ldquo;<strong>{deletingMaterial.title}</strong>&rdquo;?
            </p>
            <div className="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-900">
              ⚠️ Materi ini akan dihapus dari sistem LMS dan langsung ditarik dari tampilan student di database.
            </div>

            <div className="mt-6 flex justify-end gap-2 border-t border-outline/40 pt-4">
              <button
                type="button"
                onClick={() => setDeletingMaterial(null)}
                className="rounded-xl border border-outline/60 px-4 py-2 text-xs font-semibold text-secondary hover:bg-surface-low"
              >
                Batal
              </button>
              <button
                type="button"
                disabled={busy}
                onClick={handleConfirmDeleteFinal}
                className="rounded-xl bg-red-700 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-800 disabled:opacity-50"
              >
                {busy ? 'Menghapus...' : 'Ya, Hapus Materi'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ── MODAL PRATINJAU ANTREAN & PENGAJUAN MATERI ── */}
      {previewingSubmission && (
        <SubmissionPreviewModal
          submission={previewingSubmission}
          onClose={() => setPreviewingSubmission(null)}
          onEdit={sub => {
            setPreviewingSubmission(null);
            setEditingSubmission(sub);
          }}
          onOpenReject={sub => {
            setPreviewingSubmission(null);
            setRejectingSubmission(sub);
            setRejectComment('');
          }}
          onPublish={subId => {
            setPreviewingSubmission(null);
            publish(subId);
          }}
          canEdit={
            (isAdmin && (previewingSubmission.status === 'pending_admin' || previewingSubmission.status === 'rejected')) ||
            (!isAdmin && previewingSubmission.submitted_by === user?.npk && previewingSubmission.status === 'rejected')
          }
          isAdmin={isAdmin}
          busy={busy}
        />
      )}

      {/* ── MODAL EDIT MATERI (WORKFLOW PERBAIKAN) ── */}
      {editingSubmission && (
        <EditMaterialModal
          submission={editingSubmission}
          onClose={() => setEditingSubmission(null)}
          onSuccess={msg => {
            setNotice(msg);
            setEditingSubmission(null);
            load();
          }}
        />
      )}

      {/* ── MODAL ADMIN REJECT ── */}
      {rejectingSubmission && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div className="flex items-center justify-between border-b border-outline/40 pb-3">
              <h3 className="text-base font-bold text-red-700">Tolak Materi &amp; Minta Perbaikan</h3>
              <button type="button" onClick={() => setRejectingSubmission(null)} className="text-muted hover:text-secondary">✕</button>
            </div>
            <p className="mt-2 text-xs text-muted">
              Materi &ldquo;<span className="font-semibold text-secondary">{rejectingSubmission.title}</span>&rdquo; akan dikembalikan ke trainer untuk direvisi.
            </p>
            <form onSubmit={handleAdminReject} className="mt-4">
              <label className="block text-xs font-bold text-secondary">Alasan Penolakan / Catatan Perbaikan *</label>
              <textarea
                required
                rows={3}
                value={rejectComment}
                onChange={e => setRejectComment(e.target.value)}
                placeholder="Tuliskan bagian materi yang perlu diperbaiki oleh trainer..."
                className="mt-1.5 w-full rounded-lg border border-outline p-3 text-sm outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600/30"
              />
              <div className="mt-4 flex justify-end gap-2 border-t border-outline/40 pt-3">
                <button
                  type="button"
                  onClick={() => setRejectingSubmission(null)}
                  className="rounded-lg border border-outline px-3 py-1.5 text-xs font-semibold text-secondary hover:bg-surface-low"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={busy || rejectComment.trim().length < 3}
                  className="rounded-lg bg-red-700 px-4 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-red-800 disabled:opacity-50"
                >
                  {busy ? 'Menyimpan...' : 'Tolak & Kembalikan'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Ajukan Materi Baru Langsung dengan Pemilihan Course & Sesi */}
      {showSubmitModal && (
        <SubmitMaterialModal
          onClose={() => setShowSubmitModal(false)}
          onSuccess={load}
        />
      )}
    </div>
  );
}

function SummaryCard({ label, value, icon, highlight }) {
  return (
    <div className={`flex items-center gap-3 rounded-2xl border p-4 shadow-sm ${
      highlight ? 'border-emerald-300 bg-emerald-50/50' : 'border-outline/50 bg-white'
    }`}>
      <span className={`grid size-10 shrink-0 place-items-center rounded-xl ${
        highlight ? 'bg-emerald-600 text-white' : 'bg-primary/10 text-primary'
      }`}>
        <span className="material-symbol" aria-hidden="true">{icon}</span>
      </span>
      <div className="min-w-0">
        <p className={`text-2xl font-bold leading-none ${highlight ? 'text-emerald-900' : ''}`}>{value}</p>
        <p className="mt-1 text-xs leading-5 text-muted">{label}</p>
      </div>
    </div>
  );
}
