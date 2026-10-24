import { useEffect, useMemo, useState } from 'react';
import { Link, useOutletContext, useParams } from 'react-router-dom';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import EditMaterialModal from '../materials/EditMaterialModal';
import SubmitMaterialForm from '../materials/SubmitMaterialForm';
import FinalMaterialDetailModal from '../materials/FinalMaterialDetailModal';
import showAlert from '../../utils/alert';

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';
const label = {
  pending_admin: 'Menunggu admin',
  pending_review: 'Menunggu reviewer',
  approved: 'Disetujui reviewer',
  rejected: 'Perlu diperbaiki',
  published: 'Terbit untuk student'
};
const inputClass = 'h-11 w-full rounded-xl border border-outline/60 bg-white px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15';

function youtubeEmbed(value = '') {
  try {
    const url = new URL(value);
    let id = url.hostname.includes('youtu.be') ? url.pathname.slice(1) : url.searchParams.get('v');
    if (!id && url.pathname.startsWith('/embed/')) id = url.pathname.split('/')[2];
    return id && /^[A-Za-z0-9_-]{6,20}$/.test(id) ? `https://www.youtube-nocookie.com/embed/${id}` : null;
  } catch { return null; }
}

export default function CourseDetailPage() {
  const { id } = useParams();
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [detail, setDetail] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [contentSearch, setContentSearch] = useState('');
  const [submissionStatus, setSubmissionStatus] = useState('all');

  // Modals
  const [editingSubmission, setEditingSubmission] = useState(null);
  const [rejectingSubmission, setRejectingSubmission] = useState(null);
  const [rejectComment, setRejectComment] = useState('');
  const [selectedDetailMaterial, setSelectedDetailMaterial] = useState(null);

  async function deleteFinalMaterial(mat) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Materi Final?',
      text: `Apakah Anda yakin ingin menghapus materi final "${mat.title}"? Materi ini akan ditarik dari tampilan student dan dihapus secara permanen.`,
      confirmText: 'Ya, Hapus Materi',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true); setError(''); setNotice('');
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
      setError('');
    } catch (e) { setError(e.message); }
  }
  useEffect(() => { load(); }, [id]);

  const submissionsBySession = useMemo(() => {
    const result = {};
    for (const item of detail?.submissions || []) (result[item.session_id] ||= []).push(item);
    return result;
  }, [detail]);

  const publishedCount = detail?.sessions?.reduce((sum, session) => sum + session.materials.length, 0) || 0;
  const pendingCount = detail?.submissions?.filter(item => ['pending_admin', 'pending_review', 'approved'].includes(item.status)).length || 0;
  const visibleSessions = useMemo(() => (detail?.sessions || []).filter(session =>
    `${session.title} ${session.materials.map(material => `${material.title} ${material.material_type} ${material.content || ''}`).join(' ')}`.toLowerCase().includes(contentSearch.trim().toLowerCase())
  ), [detail, contentSearch]);
  const submissionStatuses = useMemo(() => [...new Set((detail?.submissions || []).map(item => item.status).filter(Boolean))], [detail]);
  const sessionPage = usePagination(visibleSessions, 5);

  async function addSession(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const title = new FormData(form).get('title');
    setBusy(true); setError('');
    try {
      await api(`/courses/${id}/sessions`, { method: 'POST', body: JSON.stringify({ title }) });
      showAlert.success('Sesi Pembelajaran Dibuat', `Sesi "${title}" telah berhasil ditambahkan ke course ini.`);
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
    setBusy(true); setError(''); setNotice('');
    try {
      await api(`/material-workflow/sessions/${sessionId}/submissions`, { method: 'POST', body: new FormData(form) });
      form.reset();
      setNotice('Materi berhasil diajukan untuk persetujuan Admin.');
      showAlert.success('Materi Berhasil Diajukan', 'Materi telah dikirimkan ke antrean persetujuan Admin LMS.');
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
      const response = await fetch(`${API_BASE}/material-workflow/submissions/${submissionId}/file`, { credentials: 'include' });
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
    setBusy(true); setError(''); setNotice('');
    try {
      const response = await api(`/material-workflow/submissions/${submissionId}/send-review`, {
        method: 'POST',
        body: JSON.stringify({ reviewerEmail })
      });
      setNotice(response.message);
      showAlert.success('Undangan Review Terkirim', response.message || 'Tautan peninjauan materi telah dikirimkan ke reviewer.');
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

    setBusy(true); setError(''); setNotice('');
    try {
      const response = await api(`/material-workflow/submissions/${submissionId}/publish`, { method: 'POST', body: '{}' });
      setNotice(response.message);
      showAlert.success('Materi Diterbitkan', response.message || 'Materi berhasil dipublikasikan untuk student.');
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
      const result = await api(`/material-workflow/submissions/${rejectingSubmission.submission_id}/admin-reject`, {
        method: 'POST',
        body: JSON.stringify({ comment: rejectComment.trim() })
      });
      setNotice(result.message);
      showAlert.success('Materi Dikembalikan', result.message || 'Catatan perbaikan telah dikirim ke trainer.');
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


  if (error && !detail) return <div><Link to="/courses" className="text-sm font-semibold text-primary">← Kembali ke daftar course</Link><p role="alert" className="mt-5 rounded-xl bg-red-50 p-4 text-red-700">{error}</p></div>;
  if (!detail) return <div className="grid min-h-64 place-items-center text-sm text-muted">Memuat detail course…</div>;
  const { course, sessions } = detail;

  return (
    <div className="mx-auto max-w-6xl">
      <Link to="/courses" className="inline-flex items-center gap-2 text-sm font-semibold text-muted transition hover:text-primary"><span aria-hidden="true">←</span> Semua course</Link>
      {error && <p role="alert" className="mt-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}
      {notice && <p role="status" className="mt-4 rounded-xl bg-green-50 p-4 text-green-800">{notice}</p>}

      <section className="mt-4 rounded-2xl border border-slate-700/60 bg-[#1e252d] p-6 text-white shadow-sm md:p-8">
        <div>
          <div className="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
            <span className="rounded bg-primary/20 text-red-300 px-2 py-0.5 font-bold">COURSE DETAIL</span>
            <span className="text-slate-600">/</span>
            <span className="text-slate-300">{course.course_code}</span>
          </div>
          <h1 className="mt-3 max-w-3xl text-2xl font-bold leading-tight text-white md:text-3xl">{course.title}</h1>
          <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-300 md:text-base">{course.description || 'Belum ada deskripsi untuk course ini.'}</p>
          <div className="mt-5 flex flex-wrap items-center gap-2">
            <span className="rounded-lg bg-slate-800 border border-slate-700 px-3 py-1 text-xs font-medium text-slate-200">{course.category}</span>
            <span className="rounded-lg bg-slate-800 border border-slate-700 px-3 py-1 text-xs font-medium text-slate-200">{course.status}</span>
            <span className="rounded-lg bg-slate-800 border border-slate-700 px-3 py-1 text-xs font-medium text-slate-200">Trainer: {course.assigned_trainer_name || 'Belum ditugaskan'}</span>
          </div>
        </div>
      </section>

      <section className="mt-5 grid gap-3 sm:grid-cols-3">
        <Metric title="Sesi belajar" value={sessions.length} icon="▤"/>
        <Metric title="Materi terbit" value={publishedCount} icon="▶"/>
        <Metric title="Dalam alur persetujuan" value={pendingCount} icon="◷"/>
      </section>

      <section className="mt-8">
        <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
          <div>
            <p className="text-xs font-bold uppercase tracking-[.16em] text-primary">Struktur pembelajaran</p>
            <h2 className="mt-1 text-2xl font-bold">Sesi & materi</h2>
            <p className="mt-1 text-sm text-muted">Materi terbit disinkronkan ke student. Pengajuan dapat diedit saat status perlu perbaikan.</p>
          </div>
        </div>

        <ListFilterBar search={contentSearch} onSearch={setContentSearch} placeholder="Cari sesi, judul, atau isi materi..." />

        {visibleSessions.length ? (
          <div className="space-y-4">
            {sessionPage.pagedItems.map((session, index) => {
              const visibleMaterials = session.materials.filter(material => `${material.title} ${material.material_type} ${material.content || ''}`.toLowerCase().includes(contentSearch.trim().toLowerCase()));
              const visibleSubmissions = (submissionsBySession[session.session_id] || []).filter(item => (submissionStatus === 'all' || item.status === submissionStatus) && `${item.title} ${item.material_type} ${item.submitter_name} ${item.status}`.toLowerCase().includes(contentSearch.trim().toLowerCase()));

              return (
                <article key={session.session_id} className="overflow-hidden rounded-2xl border border-outline/40 bg-white shadow-sm">
                  <div className="flex flex-wrap items-center justify-between gap-3 border-b border-outline/40 bg-surface-low/60 px-5 py-4">
                    <div className="flex items-center gap-3">
                      <span className="grid size-10 place-items-center rounded-xl bg-primary/10 text-sm font-bold text-primary">{String((sessionPage.page - 1) * sessionPage.pageSize + index + 1).padStart(2,'0')}</span>
                      <div>
                        <h3 className="font-bold">{session.title}</h3>
                        <p className="text-xs text-muted">{visibleMaterials.length} materi terbit · {visibleSubmissions.length} pengajuan tampil</p>
                      </div>
                    </div>
                    <span className={`rounded-full px-3 py-1 text-xs font-semibold ${session.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-container text-muted'}`}>{session.status}</span>
                  </div>

                  <div className="divide-y divide-outline/30">
                    {visibleMaterials.map(material => (
                      <MaterialRow
                        key={material.material_id}
                        material={material}
                        onDetail={mat => setSelectedDetailMaterial({ ...mat, course_title: detail?.course?.title, session_title: session.title })}
                        onDelete={deleteFinalMaterial}
                        canDelete={isAdmin || detail?.course?.assigned_trainer_npk === user?.npk || detail?.course?.created_by === user?.npk}
                      />
                    ))}
                    {!visibleMaterials.length && <p className="px-5 py-4 text-sm text-muted">{contentSearch ? 'Tidak ada materi yang cocok dengan pencarian.' : 'Belum ada materi yang dipublikasikan pada sesi ini.'}</p>}
                  </div>

                  {/* Form Ajukan Materi Baru — komponen dinamis dengan TinyMCE */}
                  <SubmitMaterialForm
                    sessionId={session.session_id}
                    busy={busy}
                    onSubmit={submitMaterial}
                  />

                  {/* Riwayat Pengajuan Sesi */}
                  {!!(submissionsBySession[session.session_id] || []).length && (
                    <div className="border-t border-outline/30 px-5 py-4">
                      <div className="flex flex-wrap items-center justify-between gap-2">
                        <h4 className="text-sm font-bold">Alur Persetujuan & Pengajuan Materi</h4>
                        <select value={submissionStatus} onChange={event => setSubmissionStatus(event.target.value)} className="h-9 rounded-lg border border-outline/50 bg-white px-2 text-xs">
                          <option value="all">Semua status</option>
                          {submissionStatuses.map(value => <option key={value} value={value}>{label[value] || value}</option>)}
                        </select>
                      </div>
                      <div className="mt-3 space-y-3">
                        {visibleSubmissions.map(item => (
                          <SubmissionRow
                            key={item.submission_id}
                            item={item}
                            currentUser={user}
                            isAdmin={isAdmin}
                            busy={busy}
                            onFile={openSubmissionFile}
                            onSendReview={sendReview}
                            onPublish={publish}
                            onEdit={sub => setEditingSubmission(sub)}
                            onReject={sub => { setRejectingSubmission(sub); setRejectComment(''); }}
                          />
                        ))}
                      </div>
                      {!visibleSubmissions.length && <p className="py-3 text-sm text-muted">Tidak ada pengajuan yang cocok.</p>}
                    </div>
                  )}
                </article>
              );
            })}
          </div>
        ) : (
          <div className="rounded-2xl border border-dashed border-outline/60 bg-white p-10 text-center">
            <div className="mx-auto grid size-12 place-items-center rounded-2xl bg-primary/10 text-xl text-primary">▤</div>
            <h3 className="mt-3 font-bold">{sessions.length ? 'Tidak ada sesi atau materi yang cocok' : 'Belum ada sesi'}</h3>
            <p className="mt-1 text-sm text-muted">{sessions.length ? 'Ubah kata pencarian untuk melihat sesi lainnya.' : 'Tambahkan sesi pertama agar trainer dapat mulai mengajukan materi.'}</p>
          </div>
        )}

        <div className="mt-4 overflow-hidden rounded-2xl border border-outline/40 bg-white">
          <Pagination {...sessionPage} onPageChange={sessionPage.setPage} onPageSizeChange={sessionPage.setPageSize} pageSizes={[5, 10, 20, 50]}/>
        </div>

        <form onSubmit={addSession} className="mt-4 flex flex-col gap-2 rounded-2xl border border-outline/40 bg-white p-4 sm:flex-row">
          <input name="title" required maxLength="200" placeholder="Nama sesi baru, mis. Orientasi" className={inputClass}/>
          <button disabled={busy} className="rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white disabled:opacity-50">Tambah sesi</button>
        </form>
      </section>

      {/* Edit Modal */}
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

      {/* Detail Final Modal (View Only & Delete) */}
      {selectedDetailMaterial && (
        <FinalMaterialDetailModal
          material={selectedDetailMaterial}
          onClose={() => setSelectedDetailMaterial(null)}
          onDelete={deleteFinalMaterial}
          canDelete={isAdmin || detail?.course?.assigned_trainer_npk === user?.npk || detail?.course?.created_by === user?.npk}
        />
      )}

      {/* Admin Reject Modal */}
      {rejectingSubmission && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div className="flex items-center justify-between border-b border-outline/40 pb-3">
              <h3 className="text-base font-bold text-red-700">Tolak Materi & Minta Perbaikan</h3>
              <button type="button" onClick={() => setRejectingSubmission(null)} className="text-muted hover:text-secondary">✕</button>
            </div>
            <p className="mt-2 text-xs text-muted">
              Materi &ldquo;<span className="font-semibold text-secondary">{rejectingSubmission.title}</span>&rdquo; akan dikembalikan ke trainer untuk perbaikan.
            </p>
            <form onSubmit={handleAdminReject} className="mt-4">
              <label className="block text-xs font-bold text-secondary">Alasan Penolakan / Catatan Perbaikan *</label>
              <textarea
                required
                rows={3}
                value={rejectComment}
                onChange={e => setRejectComment(e.target.value)}
                placeholder="Tuliskan alasan penolakan..."
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
    </div>
  );
}

function Metric({ title, value, icon }) {
  return (
    <div className="flex items-center gap-3 rounded-2xl border border-outline/40 bg-white p-4">
      <span className="grid size-11 place-items-center rounded-xl bg-primary/10 text-xl text-primary">{icon}</span>
      <div><p className="text-2xl font-bold leading-none">{value}</p><p className="mt-1 text-xs text-muted">{title}</p></div>
    </div>
  );
}

function MaterialRow({ material, onDetail, onDelete, canDelete }) {
  const video = material.material_type === 'video' ? youtubeEmbed(material.content) : null;
  const url = material.fileUrl ? `${API_BASE}${material.fileUrl}` : (material.material_type === 'link' ? material.content : '');
  return (
    <div className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
      <div className="flex items-start gap-3 min-w-0 flex-1">
        <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-surface-low text-lg">
          {material.material_type === 'pdf' ? '▧' : material.material_type === 'video' ? '▷' : material.material_type === 'image' ? '▣' : '≡'}
        </span>
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <p className="font-semibold">{material.title}</p>
            <span className="rounded-full bg-surface-low px-2 py-0.5 text-[11px] font-semibold text-muted">{material.material_type}</span>
            <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">✓ Terbit (Final)</span>
          </div>
          {material.material_type === 'text' && material.content && (
            <div
              className="prose prose-sm prose-slate mt-2 line-clamp-2 max-w-none text-xs text-muted"
              dangerouslySetInnerHTML={{ __html: material.content }}
            />
          )}
          {video && (
            <div className="mt-3 aspect-video max-w-xl overflow-hidden rounded-xl bg-black">
              <iframe src={video} title={material.title} className="size-full" allowFullScreen/>
            </div>
          )}
          {material.material_type === 'video' && !video && <p className="mt-1 text-xs text-muted">Video diunggah sebagai file.</p>}
        </div>
      </div>

      <div className="flex shrink-0 items-center gap-2 pt-1 sm:pt-0">
        <button
          type="button"
          onClick={() => onDetail(material)}
          className="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary hover:bg-primary hover:text-white transition"
        >
          <span className="material-symbol text-sm" aria-hidden="true">visibility</span>
          Lihat Detail
        </button>

        {url && (
          <a
            href={url}
            target="_blank"
            rel="noreferrer"
            className="rounded-lg border border-outline/50 px-3 py-1.5 text-xs font-semibold text-secondary hover:bg-surface-low"
          >
            Buka ↗
          </a>
        )}

        {canDelete && (
          <button
            type="button"
            onClick={() => onDelete(material)}
            className="grid size-8 place-items-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 transition"
            title="Hapus materi final ini"
          >
            <span className="material-symbol text-base" aria-hidden="true">delete</span>
          </button>
        )}
      </div>
    </div>
  );
}

function SubmissionRow({ item, currentUser, isAdmin, busy, onFile, onSendReview, onPublish, onEdit, onReject }) {
  const previewText = item.material_type === 'text' ? item.content : item.material_type === 'video' || item.material_type === 'link' ? item.content : '';
  const isSubmitter = item.submitted_by === currentUser?.npk;
  const canTeacherEdit = !isAdmin && isSubmitter && item.status === 'rejected';
  const canAdminEdit = isAdmin && (item.status === 'pending_admin' || item.status === 'rejected');
  const canEdit = canAdminEdit || canTeacherEdit;

  return (
    <div className="flex flex-col gap-3 rounded-xl border border-outline/40 bg-white p-3.5 lg:flex-row lg:items-center lg:justify-between">
      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-2">
          <p className="font-semibold">{item.title}</p>
          <span className={`rounded-full px-2 py-1 text-[11px] font-semibold ${
            item.status === 'rejected'
              ? 'bg-red-50 text-red-700'
              : item.status === 'published' || item.status === 'approved'
              ? 'bg-emerald-50 text-emerald-700'
              : 'bg-amber-50 text-amber-800'
          }`}>
            {label[item.status] || item.status}
          </span>
        </div>

        <p className="mt-1 text-xs text-muted">
          {item.material_type} · diajukan {item.submitter_name}
          {item.reviewer_name ? ` · reviewer ${item.reviewer_name}` : ''}
        </p>

        {previewText && (
          item.material_type === 'text'
            ? <div
                className="mt-2 line-clamp-3 text-sm text-muted [&_*]:inline"
                dangerouslySetInnerHTML={{ __html: previewText }}
              />
            : <p className="mt-2 line-clamp-3 text-sm text-muted">{previewText}</p>
        )}

        {item.admin_comment && (
          <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs text-amber-900">
            <span className="font-bold">Catatan Admin: </span>{item.admin_comment}
          </div>
        )}
        {item.reviewer_comment && (
          <div className="mt-2 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs text-red-900">
            <span className="font-bold">Catatan Reviewer: </span>{item.reviewer_comment}
          </div>
        )}

        {/* Lock note */}
        {!isAdmin && item.status === 'pending_admin' && (
          <p className="mt-1.5 text-xs font-semibold text-amber-800">
            🔒 Terkunci: Menunggu persetujuan Admin sebelum materi dapat diedit kembali.
          </p>
        )}
        {item.status === 'pending_review' && (
          <p className="mt-1.5 text-xs font-semibold text-blue-800">
            🔒 Terkunci: Sedang ditinjau Reviewer.
          </p>
        )}
      </div>

      <div className="flex min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
        {item.original_file_name && (
          <button onClick={() => onFile(item.submission_id)} className="rounded-lg border border-outline/50 px-3 py-1.5 text-xs font-semibold hover:bg-surface-low">
            Pratinjau file
          </button>
        )}

        {/* Edit Button */}
        {canEdit && (
          <button
            type="button"
            onClick={() => onEdit(item)}
            className="rounded-lg border border-primary/50 bg-primary/5 px-3 py-1.5 text-xs font-bold text-primary hover:bg-primary/10"
          >
            {item.status === 'rejected' ? 'Perbaiki & Ajukan Ulang' : 'Edit Materi'}
          </button>
        )}

        {/* Admin Reject Button */}
        {isAdmin && item.status === 'pending_admin' && (
          <button
            type="button"
            onClick={() => onReject(item)}
            className="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100"
          >
            Tolak
          </button>
        )}

        {/* Admin Send to Reviewer */}
        {isAdmin && item.status === 'pending_admin' && (
          <form onSubmit={event => onSendReview(event, item.submission_id)} className="flex min-w-0 flex-col gap-2 rounded-lg bg-surface-low/70 p-1.5 sm:flex-row">
            <input
              type="email"
              name="reviewerEmail"
              autoComplete="email"
              required
              maxLength="254"
              placeholder="Email reviewer"
              aria-label={`Email reviewer untuk ${item.title}`}
              className="h-8 min-w-0 rounded-lg border border-outline bg-white px-2 text-xs sm:w-48"
            />
            <button disabled={busy} className="rounded-lg bg-secondary px-3 py-1.5 text-xs font-bold text-white disabled:opacity-50">
              Kirim review
            </button>
          </form>
        )}

        {/* Admin Publish */}
        {isAdmin && item.status === 'approved' && (
          <button disabled={busy} onClick={() => onPublish(item.submission_id)} className="rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white shadow-sm hover:brightness-110 disabled:opacity-50">
            Unggah ke course student
          </button>
        )}
      </div>
    </div>
  );
}
