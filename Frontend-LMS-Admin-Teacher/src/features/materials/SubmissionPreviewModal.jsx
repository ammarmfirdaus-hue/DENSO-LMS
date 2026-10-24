import React from 'react';

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';

function youtubeEmbed(url) {
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

const TYPE_CONFIG = {
  text:     { label: 'Materi Teks',          icon: 'article',        badge: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20' },
  video:    { label: 'Video YouTube / File', icon: 'play_circle',    badge: 'bg-indigo-50 text-indigo-800 ring-indigo-600/20' },
  pdf:      { label: 'Dokumen PDF',          icon: 'picture_as_pdf', badge: 'bg-rose-50 text-rose-800 ring-rose-600/20' },
  image:    { label: 'Gambar / Infografis',  icon: 'image',          badge: 'bg-amber-50 text-amber-800 ring-amber-600/20' },
  document: { label: 'Dokumen Office / ODF', icon: 'description',    badge: 'bg-blue-50 text-blue-800 ring-blue-600/20' },
  audio:    { label: 'Audio',                icon: 'headphones',     badge: 'bg-purple-50 text-purple-800 ring-purple-600/20' },
  link:     { label: 'Tautan HTTPS',         icon: 'link',           badge: 'bg-teal-50 text-teal-800 ring-teal-600/20' },
};

const STATUS_CONFIG = {
  pending_admin:  { label: 'Menunggu Persetujuan Admin', badge: 'bg-amber-50 text-amber-900 ring-amber-600/20' },
  pending_review: { label: 'Dalam Peninjauan Reviewer',  badge: 'bg-blue-50 text-blue-800 ring-blue-600/20' },
  approved:       { label: 'Disetujui Reviewer',         badge: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20' },
  rejected:       { label: 'Perlu Revisi / Ditolak',     badge: 'bg-red-50 text-red-800 ring-red-600/20' },
  published:      { label: 'Telah Terbit ke Student',    badge: 'bg-emerald-100 text-emerald-900 ring-emerald-700/20' },
};

/**
 * Modal Pratinjau Lengkap untuk Antrean & Pengajuan Materi.
 * Menampilkan preview interaktif: teks HTML, PDF, Video YouTube/File, Gambar, Audio, atau Dokumen.
 *
 * @param {{
 *   submission: Object,
 *   onClose: () => void,
 *   onEdit?: (submission: Object) => void,
 *   onOpenReject?: (submission: Object) => void,
 *   onPublish?: (submissionId: number|string) => void,
 *   canEdit?: boolean,
 *   isAdmin?: boolean,
 *   busy?: boolean
 * }} props
 */
export default function SubmissionPreviewModal({
  submission,
  onClose,
  onEdit,
  onOpenReject,
  onPublish,
  canEdit,
  isAdmin,
  busy
}) {
  if (!submission) return null;

  const typeMeta = TYPE_CONFIG[submission.material_type] || {
    label: submission.material_type,
    icon: 'draft',
    badge: 'bg-surface-low text-secondary ring-outline/30',
  };

  const statusMeta = STATUS_CONFIG[submission.status] || {
    label: submission.status,
    badge: 'bg-surface-low text-secondary ring-outline/30'
  };

  const fileUrl = `${API_BASE}/material-workflow/submissions/${submission.submission_id}/file`;
  const ytEmbedUrl = submission.material_type === 'video' ? youtubeEmbed(submission.content) : null;
  const hasFile = Boolean(submission.original_file_name || submission.file_path);

  return (
    <div
      role="dialog"
      aria-modal="true"
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div className="relative flex max-h-[92vh] w-full max-w-4xl flex-col rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
        
        {/* ── HEADER ── */}
        <div className="flex items-start justify-between border-b border-outline/40 px-6 py-5">
          <div className="flex items-center gap-3">
            <span className="grid size-11 place-items-center rounded-2xl bg-primary/10 text-primary">
              <span className="material-symbol text-2xl" aria-hidden="true">{typeMeta.icon}</span>
            </span>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <span className={`rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset ${typeMeta.badge}`}>
                  {typeMeta.label}
                </span>
                <span className={`rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset ${statusMeta.badge}`}>
                  {statusMeta.label}
                </span>
              </div>
              <h2 className="mt-1 text-lg font-bold text-on-surface sm:text-xl">
                {submission.title}
              </h2>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup modal"
            className="grid size-9 place-items-center rounded-xl text-muted transition hover:bg-surface-low hover:text-secondary cursor-pointer"
          >
            ✕
          </button>
        </div>

        {/* ── METADATA STRIP ── */}
        <div className="grid grid-cols-2 gap-3 border-b border-outline/30 bg-surface-low/50 px-6 py-3 text-xs sm:grid-cols-4">
          <div>
            <p className="text-muted">Course</p>
            <p className="font-semibold text-secondary truncate">{submission.course_title || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Sesi</p>
            <p className="font-semibold text-secondary truncate">{submission.session_title || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Diajukan Oleh</p>
            <p className="font-semibold text-secondary truncate">{submission.submitter_name || submission.submitted_by || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Reviewer</p>
            <p className="font-semibold text-secondary truncate">{submission.reviewer_name || 'Belum ditugaskan'}</p>
          </div>
        </div>

        {/* ── FEEDBACK ALERTS IF ANY ── */}
        {(submission.admin_comment || submission.reviewer_comment) && (
          <div className="space-y-2 border-b border-outline/30 bg-amber-50/50 p-4 px-6 text-xs">
            {submission.admin_comment && (
              <div className="rounded-xl border border-amber-300 bg-amber-50 p-3 text-amber-900">
                <span className="font-bold">Catatan Admin: </span>{submission.admin_comment}
              </div>
            )}
            {submission.reviewer_comment && (
              <div className="rounded-xl border border-red-300 bg-red-50 p-3 text-red-900">
                <span className="font-bold">Catatan Reviewer: </span>{submission.reviewer_comment}
              </div>
            )}
          </div>
        )}

        {/* ── BODY PRATINJAU KONTEN ── */}
        <div className="flex-1 overflow-y-auto p-6 space-y-4">

          {/* 1. Tipe Text (HTML dari TinyMCE) */}
          {submission.material_type === 'text' && (
            <div className="rounded-2xl border border-outline/50 bg-white p-6 shadow-xs">
              <div className="mb-3 flex items-center justify-between border-b border-outline/30 pb-2">
                <span className="text-xs font-bold uppercase tracking-wider text-muted">Isi Materi Bacaan / Artikel</span>
                <span className="text-xs text-muted">Format Teks Terstruktur (TinyMCE)</span>
              </div>
              {submission.content ? (
                <div
                  className="prose prose-sm max-w-none text-secondary leading-relaxed space-y-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:text-xl [&_h2]:font-bold [&_h3]:text-lg [&_h3]:font-bold [&_p]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_table]:w-full [&_table]:border-collapse [&_th]:border [&_th]:border-outline [&_th]:p-2 [&_td]:border [&_td]:border-outline [&_td]:p-2 [&_img]:max-w-full [&_img]:rounded-xl [&_blockquote]:border-l-4 [&_blockquote]:border-primary [&_blockquote]:pl-4 [&_blockquote]:italic"
                  dangerouslySetInnerHTML={{ __html: submission.content }}
                />
              ) : (
                <p className="text-sm italic text-muted">Materi ini belum memiliki isi konten teks.</p>
              )}
            </div>
          )}

          {/* 2. Tipe Video */}
          {submission.material_type === 'video' && (
            <div className="space-y-4">
              {ytEmbedUrl ? (
                <div className="overflow-hidden rounded-2xl border border-outline/50 bg-black shadow-md">
                  <div className="relative aspect-video w-full">
                    <iframe
                      src={ytEmbedUrl}
                      title={submission.title}
                      className="absolute inset-0 size-full border-0"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      allowFullScreen
                    />
                  </div>
                  <div className="p-3 bg-zinc-900 text-xs text-zinc-300 flex items-center justify-between">
                    <span>Sumber: YouTube</span>
                    <a href={submission.content} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">
                      Buka di YouTube ↗
                    </a>
                  </div>
                </div>
              ) : hasFile ? (
                <div className="overflow-hidden rounded-2xl border border-outline/50 bg-black shadow-md">
                  <video
                    controls
                    controlsList="nodownload"
                    className="max-h-[480px] w-full"
                    src={fileUrl}
                  >
                    Browser Anda tidak mendukung tag video.
                  </video>
                  <div className="p-3 bg-zinc-900 text-xs text-zinc-300 flex items-center justify-between">
                    <span>File Video: {submission.original_file_name || 'video'}</span>
                    <a href={fileUrl} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">
                      Buka di Tab Baru ↗
                    </a>
                  </div>
                </div>
              ) : (
                <div className="rounded-2xl border border-dashed border-outline p-8 text-center text-sm text-muted">
                  Tautan atau file video tidak ditemukan.
                </div>
              )}
            </div>
          )}

          {/* 3. Tipe PDF */}
          {submission.material_type === 'pdf' && (
            <div className="space-y-3">
              <div className="flex items-center justify-between rounded-xl border border-outline/50 bg-surface-low/50 px-4 py-2.5">
                <span className="text-xs font-semibold text-secondary truncate">
                  Dokumen: {submission.original_file_name || 'document.pdf'}
                </span>
                <a
                  href={fileUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:brightness-110"
                >
                  <span className="material-symbol text-sm" aria-hidden="true">open_in_new</span>
                  Buka di Tab Baru
                </a>
              </div>
              <div className="overflow-hidden rounded-2xl border border-outline/50 bg-white shadow-xs">
                <iframe
                  src={fileUrl}
                  title={submission.title}
                  className="h-[520px] w-full border-0"
                />
              </div>
            </div>
          )}

          {/* 4. Tipe Gambar */}
          {submission.material_type === 'image' && (
            <div className="overflow-hidden rounded-2xl border border-outline/50 bg-surface-low/30 p-4 text-center">
              <img
                src={fileUrl}
                alt={submission.title}
                className="mx-auto max-h-[500px] w-auto rounded-xl object-contain shadow-sm"
              />
              <p className="mt-3 text-xs text-muted">{submission.original_file_name}</p>
            </div>
          )}

          {/* 5. Tipe Audio */}
          {submission.material_type === 'audio' && (
            <div className="rounded-2xl border border-outline/50 bg-surface-low/50 p-6">
              <div className="flex items-center gap-4 mb-4">
                <span className="grid size-14 place-items-center rounded-2xl bg-purple-100 text-purple-700">
                  <span className="material-symbol text-3xl" aria-hidden="true">headphones</span>
                </span>
                <div>
                  <h4 className="font-bold text-secondary">{submission.title}</h4>
                  <p className="text-xs text-muted">{submission.original_file_name || 'Audio Pembelajaran'}</p>
                </div>
              </div>
              <audio controls className="w-full" src={fileUrl}>
                Browser Anda tidak mendukung pemutar audio.
              </audio>
            </div>
          )}

          {/* 6. Tipe Dokumen Office */}
          {submission.material_type === 'document' && (
            <div className="rounded-2xl border border-outline/50 bg-white p-6 shadow-xs text-center sm:text-left">
              <div className="flex flex-col sm:flex-row items-center gap-4">
                <span className="grid size-16 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700">
                  <span className="material-symbol text-4xl" aria-hidden="true">description</span>
                </span>
                <div className="flex-1 min-w-0">
                  <h4 className="font-bold text-secondary text-base">{submission.title}</h4>
                  <p className="text-xs text-muted mt-1 truncate">File: {submission.original_file_name || 'Dokumen Pelatihan'}</p>
                  <p className="text-xs text-muted mt-0.5">Dokumen Office / ODF dapat diunduh untuk dipelajari.</p>
                </div>
                <a
                  href={fileUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex shrink-0 items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:brightness-110"
                >
                  <span className="material-symbol text-base" aria-hidden="true">download</span>
                  Unduh Dokumen
                </a>
              </div>
            </div>
          )}

          {/* 7. Tipe Link */}
          {submission.material_type === 'link' && (
            <div className="rounded-2xl border border-outline/50 bg-white p-6 shadow-xs">
              <div className="flex items-start gap-4">
                <span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-teal-50 text-teal-700">
                  <span className="material-symbol text-2xl" aria-hidden="true">link</span>
                </span>
                <div className="flex-1 min-w-0">
                  <h4 className="font-bold text-secondary">{submission.title}</h4>
                  <p className="text-xs text-muted mt-1">Tautan Web Eksternal:</p>
                  <a
                    href={submission.content}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-2 inline-flex items-center gap-2 rounded-xl border border-teal-200 bg-teal-50/70 px-4 py-2 text-xs font-semibold text-teal-900 transition hover:bg-teal-100 break-all"
                  >
                    <span className="truncate">{submission.content}</span>
                    <span className="material-symbol text-sm shrink-0" aria-hidden="true">open_in_new</span>
                  </a>
                </div>
              </div>
            </div>
          )}

        </div>

        {/* ── FOOTER ACTIONS ── */}
        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-outline/40 bg-surface-low/30 px-6 py-4">
          <button
            type="button"
            onClick={onClose}
            className="rounded-xl border border-outline/70 bg-white px-4 py-2 text-xs font-semibold text-secondary hover:bg-surface-low cursor-pointer"
          >
            Tutup
          </button>

          <div className="flex flex-wrap items-center gap-2">
            {/* Tombol Edit jika diperbolehkan */}
            {canEdit && (
              <button
                type="button"
                onClick={() => { onClose(); onEdit?.(submission); }}
                className="inline-flex items-center gap-1.5 rounded-xl border border-primary/40 bg-primary/5 px-4 py-2 text-xs font-bold text-primary hover:bg-primary/10 cursor-pointer"
              >
                <span className="material-symbol text-base" aria-hidden="true">edit</span>
                {submission.status === 'rejected' ? 'Perbaiki Materi' : 'Edit Materi'}
              </button>
            )}

            {/* Admin: Tombol Tolak / Minta Revisi */}
            {isAdmin && submission.status === 'pending_admin' && (
              <button
                type="button"
                onClick={() => { onClose(); onOpenReject?.(submission); }}
                className="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-bold text-red-700 hover:bg-red-100 cursor-pointer"
              >
                <span className="material-symbol text-base" aria-hidden="true">cancel</span>
                Tolak / Minta Revisi
              </button>
            )}

            {/* Admin: Tombol Publish jika status approved */}
            {isAdmin && submission.status === 'approved' && (
              <button
                type="button"
                disabled={busy}
                onClick={() => { onClose(); onPublish?.(submission.submission_id); }}
                className="inline-flex items-center gap-1.5 rounded-xl bg-primary px-5 py-2 text-xs font-bold text-white shadow-sm hover:brightness-110 disabled:opacity-50 cursor-pointer"
              >
                <span className="material-symbol text-base" aria-hidden="true">cloud_upload</span>
                Unggah ke Database Student
              </button>
            )}
          </div>
        </div>

      </div>
    </div>
  );
}
