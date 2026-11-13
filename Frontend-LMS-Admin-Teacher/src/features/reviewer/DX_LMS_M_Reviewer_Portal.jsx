import { useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  Search,
  Eye,
  Check,
  X,
  PlayCircle,
  FileText,
  LogOut,
  Loader2,
  ClipboardCheck
} from 'lucide-react';
import { api, fetchApi } from '../../api/client';
import Pagination, { usePagination } from '../../components/Pagination';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';

const REVIEW_TOKEN_KEY = 'dls_reviewer_portal_token';
let consumeLinkKey = '';
let consumeLinkPromise = null;

const statusLabel = {
  pending_review: 'Perlu ditinjau',
  approved: 'Disetujui',
  rejected: 'Perlu revisi'
};

function youtubeEmbed(value = '') {
  try {
    const url = new URL(value);
    let id = url.hostname.includes('youtu.be') ? url.pathname.slice(1) : url.searchParams.get('v');
    if (!id && url.pathname.startsWith('/embed/')) id = url.pathname.split('/')[2];
    return id && /^[A-Za-z0-9_-]{6,20}$/.test(id) ? `https://www.youtube-nocookie.com/embed/${id}` : null;
  } catch {
    return null;
  }
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
          consumeLinkPromise = api(`/material-workflow/links/${encodeURIComponent(linkToken)}/consume`, {
            method: 'POST',
            body: '{}',
            token: null
          });
        }
        const result = await consumeLinkPromise;
        if (!active) return;
        sessionStorage.setItem(REVIEW_TOKEN_KEY, result.token);
        setSessionToken(result.token);
        setReviewer(result.reviewer);
        navigate('/review', { replace: true });
      } catch (e) {
        if (active) {
          setError(e.message);
          showAlert.error('Tautan Review Tidak Valid', e.message);
        }
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
      showAlert.error('Sesi Reviewer Berakhir', e.message);
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
        const response = await fetchApi(submission.fileUrl, { headers: { Authorization: `Bearer ${sessionToken}` }, timeoutMs: 60000 });
        if (!response.ok) throw new Error('Gagal memuat pratinjau file.');
        const objectUrl = URL.createObjectURL(await response.blob());
        if (requestId !== previewRequest.current) { URL.revokeObjectURL(objectUrl); return; }
        setFileUrl(objectUrl);
      }
    } catch (e) {
      if (requestId === previewRequest.current) {
        setError(e.message);
        showAlert.error('Gagal Memuat Pratinjau Materi', e.message);
      }
    }
  }

  async function decide(decision) {
    if (!selected || !sessionToken || selected.status !== 'pending_review') return;

    if (decision === 'rejected' && (!comment || !comment.trim())) {
      showAlert.warning('Catatan Reviewer Diperlukan', 'Silakan tuliskan catatan evaluasi atau bagian yang perlu diperbaiki.');
      return;
    }

    const isApprove = decision === 'approved';
    const confirmed = await showAlert.confirm({
      title: isApprove ? 'Setujui Materi Pembelajaran?' : 'Minta Perbaikan Materi?',
      text: isApprove
        ? `Apakah Anda yakin menyetujui materi "${selected.title}"? Materi akan siap diterbitkan oleh Admin LMS ke portal peserta.`
        : `Materi "${selected.title}" akan dikembalikan ke pengajar beserta catatan perbaikan Anda.`,
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
          className="prose prose-sm max-h-[60vh] max-w-none overflow-auto rounded-lg bg-white p-4 text-sm leading-7 text-on-surface sm:p-6 dark:prose-invert dark:bg-[#171e29]"
          dangerouslySetInnerHTML={{ __html: item.content || '<em>Belum ada konten teks.</em>' }}
        />
      );
    }
    if (type === 'video' && !item.fileUrl) {
      const embed = youtubeEmbed(item.content);
      return embed ? (
        <div className="aspect-video overflow-hidden rounded-lg bg-black">
          <iframe
            title={item.title}
            src={embed}
            className="size-full"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowFullScreen
          />
        </div>
      ) : (
        <p className="rounded-lg bg-amber-50 p-4 text-sm text-amber-900">Tautan video tidak dapat ditampilkan.</p>
      );
    }
    if (!fileUrl) {
      return item.content ? (
        <a
          className="inline-flex h-10 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white"
          href={item.content}
          target="_blank"
          rel="noreferrer"
        >
          Buka tautan materi
        </a>
      ) : (
        <div className="grid min-h-48 place-items-center rounded-lg bg-surface-low text-sm text-muted">
          Memuat materi…
        </div>
      );
    }
    if (type === 'image') return <img src={fileUrl} alt={item.title} loading="lazy" decoding="async" className="mx-auto max-h-[60vh] max-w-full rounded-lg object-contain" />;
    if (type === 'video') return <video src={fileUrl} controls className="max-h-[60vh] w-full rounded-lg bg-black" />;
    if (type === 'audio') return <audio src={fileUrl} controls className="w-full" />;
    if (type === 'pdf') return <iframe title={item.title} src={fileUrl} className="h-[65vh] w-full rounded-lg border border-outline" />;
    return (
      <div className="rounded-lg bg-surface-low p-5">
        <p className="text-sm text-muted">Pratinjau browser tidak tersedia untuk format ini.</p>
        <a
          className="mt-4 inline-flex h-10 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white"
          href={fileUrl}
          download={item.original_file_name || item.title}
        >
          Unduh {item.original_file_name || 'dokumen'}
        </a>
      </div>
    );
  }

  const filteredItems = useMemo(
    () =>
      items.filter(
        item =>
          (status === 'all' || item.status === status) &&
          `${item.title} ${item.course_title} ${item.session_title} ${item.material_type} ${item.status}`
            .toLowerCase()
            .includes(search.trim().toLowerCase())
      ),
    [items, status, search]
  );
  const reviewerPage = usePagination(filteredItems, 8);
  const pendingCount = items.filter(item => item.status === 'pending_review').length;
  const decidedCount = items.filter(item => item.status === 'approved' || item.status === 'rejected').length;

  if (linkToken && !sessionToken && !error) {
    return <PortalMessage title="Menyiapkan sesi review" message="Tautan sedang diverifikasi dengan aman…" loading />;
  }
  if (!sessionToken) {
    return (
      <PortalMessage
        title="Tautan review tidak tersedia"
        message={error || 'Buka tautan terbaru yang dikirim ke email reviewer. Tautan hanya dapat digunakan satu kali untuk memulai sesi.'}
      />
    );
  }

  const listOrder = selected ? 'order-2 lg:order-1' : 'order-1 lg:order-1';
  const detailOrder = selected ? 'order-1 lg:order-2' : 'order-2 lg:order-2';

  return (
    <div className="min-h-dvh overflow-x-clip bg-surface text-on-surface">
      {/* Dark Frosted Shell Header (design.md chrome) */}
      <header className="dls-shell-chrome sticky top-0 z-20 flex h-16 items-center justify-between border-b border-white/10 bg-[#10141a]/92 px-4 text-white backdrop-blur-md sm:px-6 lg:px-8">
        <div className="flex min-w-0 items-center gap-3">
          <img
            src="/assets/img/icon/denso-vector-logo.png"
            alt="DENSO"
            className="size-8 shrink-0 rounded-md bg-white p-0.5 object-contain"
          />
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-white">Portal Reviewer Materi</p>
            <p className="truncate text-xs text-white/65">
              {reviewer?.name || 'Sesi Review Terbatas'}
            </p>
          </div>
        </div>

        <div className="flex shrink-0 items-center gap-3">
          <span className="hidden items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-medium text-white/90 sm:inline-flex">
            <span className="size-2 rounded-full bg-emerald-400" aria-hidden="true" />
            Sesi aktif · 2 jam
          </span>
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={endSession}
            className="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 text-xs font-semibold text-white transition hover:bg-primary"
          >
            <LogOut className="h-3.5 w-3.5" />
            <span>Akhiri Sesi</span>
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-[1400px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.05em] text-primary">VALIDASI KONTEN</p>
            <h1 className="mt-1 text-2xl font-bold tracking-tight text-on-surface">Review Materi Pembelajaran</h1>
            <p className="mt-1 text-sm text-muted">
              Periksa kelayakan materi dan berikan persetujuan atau catatan perbaikan sebelum dipublikasikan ke peserta.
            </p>
          </div>
          <div className="flex items-center gap-3 text-xs">
            <span className="rounded-lg border border-outline bg-white px-3 py-2 font-medium text-on-surface">
              <strong className="font-mono font-bold text-primary">{pendingCount}</strong> perlu ditinjau
            </span>
            <span className="rounded-lg border border-outline bg-white px-3 py-2 font-medium text-on-surface">
              <strong className="font-mono font-bold text-emerald-700">{decidedCount}</strong> diputuskan
            </span>
          </div>
        </div>

        {error && (
          <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">
            {error}
          </p>
        )}

        <div className="grid items-start gap-5 lg:grid-cols-[minmax(320px,370px)_minmax(0,1fr)]">
          <section className={`${listOrder} dls-card-surface min-w-0 overflow-hidden rounded-xl`}>
            <div className="border-b border-outline p-5">
              <p className="text-xs font-semibold uppercase tracking-[0.05em] text-muted">Antrean Review</p>
              <h2 className="mt-1 text-base font-bold text-on-surface">
                Materi Ditugaskan <span className="font-mono text-sm font-semibold text-muted">({filteredItems.length})</span>
              </h2>
              <div className="relative mt-4">
                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" aria-hidden="true" />
                <input
                  type="search"
                  value={search}
                  onChange={event => setSearch(event.target.value)}
                  placeholder="Cari judul atau course…"
                  className="h-10 w-full rounded-md border border-outline bg-white pl-9 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
                />
              </div>
              <select
                value={status}
                onChange={event => setStatus(event.target.value)}
                aria-label="Filter status materi"
                className="mt-2.5 h-10 w-full cursor-pointer rounded-md border border-outline bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15"
              >
                <option value="pending_review">Perlu ditinjau</option>
                <option value="all">Semua materi</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Perlu revisi</option>
              </select>
            </div>

            <div className="divide-y divide-outline">
              {reviewerPage.pagedItems.map(item => {
                const isSelected = selected?.submission_id === item.submission_id;
                return (
                  <button
                    type="button"
                    key={item.submission_id}
                    data-dls-action-ignore="true"
                    onClick={() => selectItem(item)}
                    aria-pressed={isSelected}
                    className={`block w-full cursor-pointer border-l-[3px] p-4 text-left transition ${
                      isSelected
                        ? 'border-l-primary bg-primary/[0.05]'
                        : 'border-l-transparent bg-transparent hover:bg-surface-low/80'
                    }`}
                  >
                    <span className="flex items-start gap-3">
                      {item.material_type === 'video' ? (
                        <PlayCircle className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                      ) : (
                        <FileText className="mt-0.5 h-4 w-4 shrink-0 text-muted" />
                      )}
                      <span className="min-w-0 flex-1">
                        <span className="block break-words text-sm font-semibold text-on-surface">{item.title}</span>
                        <span className="mt-1 block text-xs text-muted">
                          {item.course_title} · {item.session_title}
                        </span>
                        <span className="mt-2 inline-block">
                          <StatusBadge status={item.status} label={statusLabel[item.status] || item.status} />
                        </span>
                      </span>
                    </span>
                  </button>
                );
              })}

              {!reviewerPage.pagedItems.length && (
                <div className="p-8 text-center">
                  <p className="text-sm font-semibold text-on-surface">
                    {loading ? 'Memuat daftar materi…' : items.length ? 'Tidak ada materi yang cocok.' : 'Belum ada materi yang ditugaskan.'}
                  </p>
                  <p className="mt-1 text-xs text-muted">Materi yang dikirim admin akan muncul di daftar ini.</p>
                </div>
              )}
            </div>

            <Pagination
              {...reviewerPage}
              onPageChange={reviewerPage.setPage}
              onPageSizeChange={reviewerPage.setPageSize}
              pageSizes={[8, 16, 32]}
            />
          </section>

          <section className={`${detailOrder} dls-card-surface min-w-0 overflow-hidden rounded-xl`}>
            {selected ? (
              <>
                <div className="border-b border-outline p-6">
                  <div className="flex flex-wrap items-center gap-2 text-xs font-medium text-muted">
                    <span>{selected.course_title}</span>
                    <span aria-hidden="true">/</span>
                    <span>{selected.session_title}</span>
                    <span className="rounded-full border border-outline bg-surface-low px-2.5 py-0.5 uppercase font-semibold text-on-surface">
                      {selected.material_type}
                    </span>
                  </div>
                  <h2 className="mt-2.5 break-words text-xl font-bold tracking-tight text-on-surface">
                    {selected.title}
                  </h2>
                  <p className="mt-1.5 text-sm text-muted">
                    Diajukan oleh <strong className="font-semibold text-on-surface">{selected.submitter_name}</strong>
                  </p>
                </div>

                <div className="p-6">
                  <div className="mb-3 flex items-center justify-between gap-3">
                    <h3 className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.05em] text-muted">
                      <Eye className="h-4 w-4" aria-hidden="true" />
                      Pratinjau Materi
                    </h3>
                  </div>

                  <div className="rounded-xl border border-outline bg-surface-low/60 p-4">{renderPreview(selected)}</div>

                  {selected.status === 'pending_review' ? (
                    <div className="mt-6 border-t border-outline pt-5">
                      <label className="block text-sm font-semibold text-on-surface">
                        Catatan Review <span className="font-normal text-muted">(wajib diisi saat meminta revisi)</span>
                        <textarea
                          value={comment}
                          onChange={event => setComment(event.target.value)}
                          maxLength={2000}
                          className="mt-2 min-h-28 w-full rounded-md border border-outline bg-white p-3 text-sm leading-6 outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15"
                          placeholder="Tulis alasan penolakan atau saran perbaikan untuk penyusun materi…"
                        />
                      </label>
                      <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <button
                          type="button"
                          data-dls-action-ignore="true"
                          disabled={busy || comment.trim().length < 3}
                          onClick={() => decide('rejected')}
                          className="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg border border-outline-strong bg-white px-4 text-sm font-semibold text-on-surface transition hover:bg-surface-low disabled:cursor-not-allowed disabled:opacity-45"
                        >
                          <X className="h-4 w-4" aria-hidden="true" />
                          Minta Perbaikan
                        </button>
                        <button
                          type="button"
                          data-dls-action-ignore="true"
                          disabled={busy}
                          onClick={() => decide('approved')}
                          className="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary-container disabled:cursor-wait disabled:opacity-50"
                        >
                          <Check className="h-4 w-4" aria-hidden="true" />
                          Setujui Materi
                        </button>
                      </div>
                    </div>
                  ) : (
                    <div className="mt-6 rounded-xl border border-outline bg-surface-low/70 p-4 text-sm">
                      <p className="font-bold text-on-surface">
                        Keputusan: {statusLabel[selected.status] || selected.status}
                      </p>
                      {selected.reviewer_comment && (
                        <p className="mt-1.5 whitespace-pre-wrap leading-6 text-muted">{selected.reviewer_comment}</p>
                      )}
                    </div>
                  )}
                </div>
              </>
            ) : (
              <div className="grid min-h-64 place-items-center p-8 text-center">
                <div>
                  <span className="mx-auto grid size-12 place-items-center rounded-xl bg-surface-low text-muted">
                    <ClipboardCheck className="h-6 w-6" />
                  </span>
                  <h2 className="mt-3 text-base font-bold text-on-surface">Pilih materi untuk ditinjau</h2>
                  <p className="mx-auto mt-1.5 max-w-sm text-sm leading-relaxed text-muted">
                    Klik salah satu item pada daftar di sebelah kiri untuk membaca isi materi dan memberikan keputusan.
                  </p>
                </div>
              </div>
            )}
          </section>
        </div>

        <p className="text-center text-xs text-muted">
          Sesi review ini bersifat terbatas dan hanya menampilkan materi yang ditugaskan kepada akun reviewer Anda.
        </p>
      </main>
    </div>
  );
}

function PortalMessage({ title, message, loading = false }) {
  return (
    <main className="grid min-h-dvh place-items-center bg-surface p-4 sm:p-8">
      <section className="dls-card-surface w-full max-w-lg rounded-xl p-8 text-center">
        <img
          src="/assets/img/icon/denso-vector-logo.png"
          alt="DENSO"
          className="mx-auto size-14 rounded-xl border border-outline bg-white p-1.5 object-contain"
        />
        <p className="mt-5 text-xs font-semibold uppercase tracking-[0.08em] text-primary">DENSO LMS · Portal Reviewer</p>
        <h1 className="mt-2 text-2xl font-bold text-on-surface">{title}</h1>
        <p className="mt-2.5 text-sm leading-relaxed text-muted">{message}</p>
        {loading && <Loader2 className="mx-auto mt-5 h-6 w-6 animate-spin text-primary" aria-hidden="true" />}
      </section>
    </main>
  );
}
