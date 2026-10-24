import { useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../../api/client';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

const REVIEW_TOKEN_KEY = 'dls_reviewer_portal_token';
const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';
let consumeLinkKey = '';
let consumeLinkPromise = null;

const statusLabel = {
  pending_review: 'Perlu ditinjau',
  approved: 'Disetujui',
  rejected: 'Ditolak'
};

function youtubeEmbed(value = '') {
  try {
    const url = new URL(value);
    let id = url.hostname.includes('youtu.be') ? url.pathname.slice(1) : url.searchParams.get('v');
    if (!id && url.pathname.startsWith('/embed/')) id = url.pathname.split('/')[2];
    return id && /^[A-Za-z0-9_-]{6,20}$/.test(id) ? `https://www.youtube-nocookie.com/embed/${id}` : null;
  } catch { return null; }
}

export default function ReviewerPortalPage() {
  const { token: linkToken } = useParams();
  const navigate = useNavigate();
  const [sessionToken, setSessionToken] = useState(() => sessionStorage.getItem(REVIEW_TOKEN_KEY));
  const [reviewer, setReviewer] = useState(null);
  const [items, setItems] = useState([]);
  const [selected, setSelected] = useState(null);
  const [fileUrl, setFileUrl] = useState('');
  const [comment, setComment] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('pending_review');
  const previewRequest = useRef(0);

  useEffect(() => {
    if (!linkToken) return undefined;
    let active = true;
    (async () => {
      try {
        if (consumeLinkKey !== linkToken || !consumeLinkPromise) {
          consumeLinkKey = linkToken;
          consumeLinkPromise = api(`/material-workflow/links/${encodeURIComponent(linkToken)}/consume`, { method: 'POST', body: '{}', token: null });
        }
        const result = await consumeLinkPromise;
        if (!active) return;
        sessionStorage.setItem(REVIEW_TOKEN_KEY, result.token);
        setSessionToken(result.token);
        setReviewer(result.reviewer);
        navigate('/review', { replace: true });
      } catch (e) {
        if (active) setError(e.message);
      }
    })();
    return () => { active = false; };
  }, [linkToken, navigate]);

  async function loadItems(token = sessionToken) {
    if (!token) return;
    setLoading(true);
    try {
      const result = await api('/material-workflow/reviewer/submissions', { token });
      setItems(result.submissions || []);
      setError('');
    } catch (e) {
      setError(e.message);
      sessionStorage.removeItem(REVIEW_TOKEN_KEY);
      setSessionToken(null);
      setSelected(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { if (!linkToken) loadItems(); }, [sessionToken, linkToken]);
  useEffect(() => () => { if (fileUrl) URL.revokeObjectURL(fileUrl); }, [fileUrl]);

  async function selectItem(item) {
    const requestId = ++previewRequest.current;
    setSelected(item);
    setFileUrl('');
    setComment(item.reviewer_comment || '');
    setError('');
    if (!sessionToken) return;
    try {
      const { submission } = await api(`/material-workflow/reviewer/submissions/${item.submission_id}`, { token: sessionToken });
      if (requestId !== previewRequest.current) return;
      setSelected(submission);
      if (submission.fileUrl) {
        const response = await fetch(`${API_BASE}${submission.fileUrl}`, { headers: { Authorization: `Bearer ${sessionToken}` } });
        if (!response.ok) throw new Error('Gagal memuat pratinjau file.');
        const objectUrl = URL.createObjectURL(await response.blob());
        if (requestId !== previewRequest.current) { URL.revokeObjectURL(objectUrl); return; }
        setFileUrl(objectUrl);
      }
    } catch (e) {
      if (requestId === previewRequest.current) setError(e.message);
    }
  }

  async function decide(decision) {
    if (!selected || !sessionToken || selected.status !== 'pending_review') return;

    if (decision === 'rejected' && (!comment || !comment.trim())) {
      showAlert.warning('Catatan Reviewer Diperlukan', 'Silakan tuliskan catatan evaluasi atau bagian mana yang perlu diperbaiki sebelum meminta revisi materi.');
      return;
    }

    const isApprove = decision === 'approved';
    const confirmed = await showAlert.confirm({
      title: isApprove ? 'Setujui Materi Pembelajaran?' : 'Minta Perbaikan Materi?',
      text: isApprove
        ? `Apakah Anda yakin menyetujui materi "${selected.title}"? Materi akan siap diterbitkan oleh Admin LMS ke portal siswa.`
        : `Materi "${selected.title}" akan dikembalikan ke trainer/guru beserta catatan perbaikan yang Anda masukkan.`,
      confirmText: isApprove ? 'Ya, Setujui' : 'Ya, Minta Perbaikan',
      cancelText: 'Batal',
      icon: isApprove ? 'question' : 'warning',
      danger: !isApprove
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    try {
      await api(`/material-workflow/reviewer/submissions/${selected.submission_id}/decision`, {
        method: 'POST',
        token: sessionToken,
        body: JSON.stringify({ decision, comment })
      });
      showAlert.success(
        isApprove ? 'Materi Berhasil Disetujui' : 'Catatan Perbaikan Dikirim',
        isApprove ? 'Materi telah disetujui dan siap untuk dipublikasikan.' : 'Materi telah dikembalikan ke pembuat materi untuk diperbaiki.'
      );
      setSelected(null);
      setFileUrl('');
      await loadItems();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menyimpan Keputusan', e.message);
    } finally {
      setBusy(false);
    }
  }


  function endSession() {
    previewRequest.current += 1;
    sessionStorage.removeItem(REVIEW_TOKEN_KEY);
    setSessionToken(null);
    setReviewer(null);
    setSelected(null);
    setItems([]);
    setFileUrl('');
    setError('');
    navigate('/review', { replace: true });
  }

  function renderPreview(item) {
    if (!item) return null;
    const type = item.material_type;
    if (type === 'text') {
      return (
        <div
          className="prose prose-sm max-h-[60vh] max-w-none overflow-auto rounded-2xl bg-surface-low p-4 text-sm leading-7 text-secondary sm:p-6 dark:prose-invert"
          dangerouslySetInnerHTML={{ __html: item.content || '<em>Belum ada konten teks.</em>' }}
        />
      );
    }
    if (type === 'video' && !item.fileUrl) {
      const embed = youtubeEmbed(item.content);
      return embed ? <div className="aspect-video overflow-hidden rounded-2xl bg-black"><iframe title={item.title} src={embed} className="size-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowFullScreen /></div> : <p className="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Tautan video tidak dapat ditampilkan.</p>;
    }
    if (!fileUrl) return item.content ? <a className="inline-flex min-h-10 items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white" href={item.content} target="_blank" rel="noreferrer">Buka materi</a> : <div className="grid min-h-48 place-items-center rounded-lg bg-surface-low text-sm text-muted">Memuat materi…</div>;
    if (type === 'image') return <img src={fileUrl} alt={item.title} className="mx-auto max-h-[60vh] max-w-full rounded-2xl object-contain" />;
    if (type === 'video') return <video src={fileUrl} controls className="max-h-[60vh] w-full rounded-2xl bg-black" />;
    if (type === 'audio') return <audio src={fileUrl} controls className="w-full" />;
    if (type === 'pdf') return <iframe title={item.title} src={fileUrl} className="h-[65vh] w-full rounded-2xl border border-outline" />;
    return <div className="rounded-lg bg-surface-low p-5"><p className="text-sm text-muted">Pratinjau browser tidak tersedia untuk format ini.</p><a className="mt-4 inline-flex min-h-10 items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white" href={fileUrl} download={item.original_file_name || item.title}>Unduh {item.original_file_name || 'dokumen'}</a></div>;
  }

  const filteredItems = useMemo(() => items.filter(item =>
    (status === 'all' || item.status === status) &&
    `${item.title} ${item.course_title} ${item.session_title} ${item.material_type} ${item.status}`.toLowerCase().includes(search.trim().toLowerCase())
  ), [items, status, search]);
  const reviewerPage = usePagination(filteredItems, 8);
  const pendingCount = items.filter(item => item.status === 'pending_review').length;
  const decidedCount = items.filter(item => item.status === 'approved' || item.status === 'rejected').length;

  if (linkToken && !sessionToken && !error) return <PortalMessage title="Menyiapkan sesi review" message="Tautan sedang diverifikasi dengan aman…" loading />;
  if (!sessionToken) return <PortalMessage title="Tautan review tidak tersedia" message={error || 'Buka tautan terbaru yang dikirim ke email reviewer. Tautan hanya dapat digunakan satu kali untuk memulai sesi.'} />;

  const listOrder = selected ? 'order-2 lg:order-1' : 'order-1 lg:order-1';
  const detailOrder = selected ? 'order-1 lg:order-2' : 'order-2 lg:order-2';

  return <main className="min-h-dvh bg-[#f4f6f8] px-3 py-4 sm:px-6 sm:py-6 lg:px-8">
    <div className="mx-auto max-w-[1320px] space-y-4">
      <header className="border-b border-outline/60 pb-4 sm:pb-5">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex min-w-0 items-center gap-3">
            <img src="/assets/img/icon/denso-vector-logo.png" alt="DENSO" className="size-10 shrink-0 object-contain" />
            <div className="min-w-0">
              <p className="text-xs font-medium text-muted">DENSO LMS <span className="px-1 text-outline">/</span> Portal reviewer</p>
              <h1 className="mt-1 text-xl font-semibold text-on-surface sm:text-2xl">Review materi</h1>
              <p className="mt-1 truncate text-sm text-muted">{reviewer?.name || 'Sesi review terbatas'} <span className="px-1 text-outline">·</span> Periksa materi sebelum diterbitkan</p>
            </div>
          </div>
          <div className="flex flex-wrap items-center gap-3 sm:justify-end">
            <span className="inline-flex items-center gap-2 text-xs text-muted"><span className="size-2 rounded-full bg-emerald-600" aria-hidden="true" />Sesi aktif · 2 jam</span>
            <button type="button" onClick={endSession} className="inline-flex min-h-9 items-center justify-center rounded-lg border border-outline/60 bg-white px-3 py-2 text-sm font-medium text-secondary transition hover:bg-surface-low cursor-pointer">Akhiri sesi</button>
          </div>
        </div>
      </header>

      <div className="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-muted">
        <span><strong className="font-semibold text-on-surface">{pendingCount}</strong> perlu ditinjau</span>
        <span><strong className="font-semibold text-on-surface">{decidedCount}</strong> sudah diputuskan</span>
      </div>

      {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{error}</p>}

      <div className="grid items-start gap-4 lg:grid-cols-[minmax(320px,360px)_minmax(0,1fr)] xl:gap-5">
        <section className={`${listOrder} min-w-0 overflow-hidden rounded-lg border border-outline/60 bg-white`}>
          <div className="border-b border-outline/50 bg-white p-4">
            <div className="flex items-start justify-between gap-3">
              <div><p className="text-xs font-medium text-muted">Antrean review</p><h2 className="mt-1 text-lg font-bold text-on-surface">Materi ditugaskan <span className="ml-1 text-sm font-medium text-muted">({filteredItems.length})</span></h2></div>
            </div>
            <label className="relative mt-4 block"><span className="sr-only">Cari materi</span><span className="material-symbol absolute left-3 top-1/2 -translate-y-1/2 text-lg text-muted" aria-hidden="true">search</span><input type="search" value={search} onChange={event => setSearch(event.target.value)} placeholder="Cari judul atau course…" className="h-10 w-full rounded-lg border border-outline/60 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15" /></label>
            <label className="mt-2 block"><span className="sr-only">Filter status materi</span><select value={status} onChange={event => setStatus(event.target.value)} className="h-10 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15"><option value="pending_review">Perlu ditinjau</option><option value="all">Semua materi</option><option value="approved">Disetujui</option><option value="rejected">Ditolak</option></select></label>
          </div>
          <div className="divide-y divide-outline/40">
            {reviewerPage.pagedItems.map(item => <button type="button" key={item.submission_id} onClick={() => selectItem(item)} aria-pressed={selected?.submission_id === item.submission_id} className={`block w-full border-l-[3px] p-4 text-left transition hover:bg-surface-low/50 focus-visible:outline-primary ${selected?.submission_id === item.submission_id ? 'border-primary bg-red-50/50' : 'border-transparent bg-white'}`}>
              <span className="flex items-start gap-3">
                <span className="material-symbol mt-0.5 text-lg text-muted" aria-hidden="true">{item.material_type === 'video' ? 'play_circle' : item.material_type === 'text' ? 'article' : 'description'}</span>
                <span className="min-w-0 flex-1">
                  <span className="block break-words font-bold text-on-surface">{item.title}</span>
                  <span className="mt-1 block text-sm leading-5 text-muted">{item.course_title} <span className="px-1">·</span> {item.session_title}</span>
                  <span className={`mt-2 inline-flex rounded-md px-2 py-1 text-[11px] font-semibold ${item.status === 'pending_review' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : item.status === 'approved' ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-red-50 text-red-800 dark:bg-red-950 dark:text-red-200'}`}>{statusLabel[item.status] || item.status}</span>
                </span>
              </span>
            </button>)}
            {!reviewerPage.pagedItems.length && <div className="p-7 text-center">
              <p className="text-sm font-semibold">{loading ? 'Memuat daftar materi…' : items.length ? 'Tidak ada materi yang cocok.' : 'Belum ada materi yang ditugaskan.'}</p>
              <p className="mt-1 text-xs leading-5 text-muted">Materi yang dikirim admin akan muncul di daftar ini.</p>
            </div>}
          </div>
          <Pagination {...reviewerPage} onPageChange={reviewerPage.setPage} onPageSizeChange={reviewerPage.setPageSize} pageSizes={[8, 16, 32]} />
        </section>

        <section className={`${detailOrder} min-w-0 overflow-hidden rounded-lg border border-outline/60 bg-white`}>
          {selected ? <>
            <div className="border-b border-outline/50 bg-white p-4 sm:p-6">
              <div className="flex flex-wrap items-center gap-2 text-xs font-semibold text-muted"><span>{selected.course_title}</span><span aria-hidden="true">/</span><span>{selected.session_title}</span><span aria-hidden="true">/</span><span className="rounded-full bg-surface-low px-2 py-1 uppercase text-secondary">{selected.material_type}</span></div>
              <h2 className="mt-3 break-words text-xl font-bold tracking-tight text-on-surface sm:text-2xl">{selected.title}</h2>
              <p className="mt-2 text-sm text-muted">Diajukan oleh <span className="font-semibold text-secondary">{selected.submitter_name}</span></p>
            </div>
            <div className="p-4 sm:p-6">
              <div className="mb-3 flex items-center justify-between gap-3"><h3 className="flex items-center gap-2 text-sm font-bold text-on-surface"><span className="material-symbol text-base text-muted" aria-hidden="true">visibility</span>Pratinjau materi</h3><span className="text-[11px] font-semibold uppercase tracking-wide text-muted">{selected.material_type}</span></div>
              <div className="rounded-2xl border border-outline/40 bg-surface p-3 sm:p-4">{renderPreview(selected)}</div>
              {selected.status === 'pending_review' ? <div className="mt-6 border-t border-outline/50 pt-5">
                <label className="block text-sm font-bold">Catatan review <span className="font-normal text-muted">(wajib saat menolak)</span><textarea value={comment} onChange={event => setComment(event.target.value)} maxLength={2000} className="mt-2 min-h-28 w-full rounded-xl border border-outline/60 p-3 text-sm leading-6 outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15" placeholder="Tulis alasan penolakan atau saran perbaikan…" /></label>
                <div className="mt-4 grid grid-cols-1 gap-2 sm:flex sm:justify-end">
                  <button type="button" disabled={busy || comment.trim().length < 3} onClick={() => decide('rejected')} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-outline/60 px-5 py-2.5 text-sm font-semibold text-secondary transition hover:bg-surface-low disabled:cursor-not-allowed disabled:opacity-40"><span className="material-symbol text-base" aria-hidden="true">close</span>Tolak materi</button>
                  <button type="button" disabled={busy} onClick={() => decide('approved')} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-wait disabled:opacity-50"><span className="material-symbol text-base" aria-hidden="true">check</span>Setujui materi</button>
                </div>
              </div> : <div className="mt-6 rounded-xl bg-surface-low p-4 text-sm"><p className="font-bold">Keputusan: {statusLabel[selected.status] || selected.status}</p>{selected.reviewer_comment && <p className="mt-1 whitespace-pre-wrap leading-6 text-muted">{selected.reviewer_comment}</p>}</div>}
            </div>
          </> : <div className="grid min-h-56 place-items-center p-6 text-center sm:min-h-64">
            <div><h2 className="text-base font-semibold text-on-surface">Pilih materi untuk ditinjau</h2><p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-muted">Pilih materi dari antrean untuk memeriksa isi dan memberikan keputusan.</p></div>
          </div>}
        </section>
      </div>
      <p className="px-1 text-center text-xs leading-5 text-muted">Sesi ini hanya dapat melihat dan memberi keputusan pada materi yang ditugaskan kepada Anda.</p>
    </div>
  </main>;
}

function PortalMessage({ title, message, loading = false }) {
  return <main className="grid min-h-dvh place-items-center bg-[#f4f6f8] p-4 sm:p-8"><section className="w-full max-w-lg rounded-3xl border border-outline/50 bg-white p-6 text-center shadow-lg sm:p-9">
    <img src="/assets/img/icon/denso-vector-logo.png" alt="DENSO" className="mx-auto size-16 rounded-2xl border border-outline/40 bg-white p-2 object-contain shadow-sm" />
    <p className="mt-5 text-xs font-bold uppercase tracking-[.18em] text-primary">DENSO LMS · Portal reviewer</p>
    <h1 className="mt-2 text-2xl font-bold">{title}</h1>
    <p className="mt-3 text-sm leading-6 text-muted">{message}</p>
    {loading && <span className="material-symbol mt-5 animate-spin text-2xl text-primary" aria-hidden="true">progress_activity</span>}
  </section></main>;
}
