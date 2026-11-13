import {
  CheckCircle2,
  Download,
  ExternalLink,
  File,
  FileBadge,
  FileText,
  Headphones,
  Image,
  Link2,
  Pencil,
  PlayCircle,
  Upload,
  X,
  XCircle
} from 'lucide-react';
import { API_BASE } from '../../api/client';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';

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
  text: { label: 'Materi Teks', Icon: FileText },
  video: { label: 'Video YouTube / File', Icon: PlayCircle },
  pdf: { label: 'Dokumen PDF', Icon: FileBadge },
  image: { label: 'Gambar / Infografis', Icon: Image },
  document: { label: 'Dokumen Office / ODF', Icon: File },
  audio: { label: 'Audio', Icon: Headphones },
  link: { label: 'Tautan HTTPS', Icon: Link2 }
};

const STATUS_LABEL = {
  pending_admin: 'Menunggu Persetujuan Admin',
  pending_review: 'Dalam Peninjauan Reviewer',
  approved: 'Disetujui Reviewer',
  rejected: 'Perlu Revisi / Ditolak',
  published: 'Telah Terbit ke Student'
};

/**
 * Modal pratinjau lengkap untuk antrean pengajuan materi.
 */
export default function SubmissionPreviewModal({
  submission,
  onClose,
  onEdit,
  onOpenReject,
  onReviewerApprove,
  onReviewerReject,
  onPublish,
  canEdit,
  isAdmin,
  canSendToReviewer,
  canReviewMaterial,
  canPublishMaterial,
  busy
}) {
  if (!submission) return null;

  const typeMeta = TYPE_CONFIG[submission.material_type] || {
    label: submission.material_type,
    Icon: FileText
  };
  const TypeIcon = typeMeta.Icon;

  const fileUrl = `${API_BASE}/material-workflow/submissions/${submission.submission_id}/file`;
  const ytEmbedUrl = submission.material_type === 'video' ? youtubeEmbed(submission.content) : null;
  const hasFile = Boolean(submission.original_file_name || submission.file_path);

  return (
    <div
      role="dialog"
      aria-modal="true"
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="dls-card-surface relative flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl shadow-2xl">
        {/* Header */}
        <div className="flex items-start justify-between gap-4 border-b border-outline/40 bg-surface-low/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/60">
          <div className="flex items-start gap-3">
            <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary dark:bg-red-950/60 dark:text-red-300">
              <TypeIcon className="size-5" />
            </span>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <span className="rounded-md bg-surface-lowest px-2 py-0.5 text-[11px] font-semibold text-on-surface-variant ring-1 ring-outline/40 dark:bg-slate-900 dark:text-slate-300">
                  {typeMeta.label}
                </span>
                <StatusBadge
                  status={submission.status}
                  label={STATUS_LABEL[submission.status] || submission.status}
                  dot
                />
              </div>
              <h2 className="mt-1.5 text-lg font-bold text-on-surface sm:text-xl">
                {submission.title}
              </h2>
            </div>
          </div>

          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            aria-label="Tutup modal"
            className="grid size-8 shrink-0 place-items-center rounded-lg text-muted transition hover:bg-surface-container hover:text-on-surface dark:hover:bg-slate-700"
          >
            <X className="size-4" />
          </button>
        </div>

        {/* Metadata Strip */}
        <div className="grid grid-cols-2 gap-3 border-b border-outline/40 bg-surface-low/30 px-6 py-3 text-xs sm:grid-cols-4 dark:border-slate-800 dark:bg-slate-900/40">
          <div>
            <p className="text-muted">Course</p>
            <p className="truncate font-semibold text-on-surface">{submission.course_title || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Sesi</p>
            <p className="truncate font-semibold text-on-surface">
              {submission.session_title || '-'}
            </p>
          </div>
          <div>
            <p className="text-muted">Diajukan Oleh</p>
            <p className="truncate font-semibold text-on-surface">
              {submission.submitter_name || submission.submitted_by || '-'}
            </p>
          </div>
          <div>
            <p className="text-muted">Reviewer</p>
            <p className="truncate font-semibold text-on-surface">
              {submission.reviewer_name || 'Belum ditugaskan'}
            </p>
          </div>
        </div>

        {/* Feedback Comments */}
        {(submission.admin_comment || submission.reviewer_comment) && (
          <div className="space-y-2 border-b border-outline/40 px-6 py-3 text-xs dark:border-slate-800">
            {submission.admin_comment && (
              <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                <span className="font-bold">Catatan Admin: </span>
                {submission.admin_comment}
              </div>
            )}
            {submission.reviewer_comment && (
              <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                <span className="font-bold">Catatan Reviewer: </span>
                {submission.reviewer_comment}
              </div>
            )}
          </div>
        )}

        {/* Body Pratinjau Konten */}
        <div className="flex-1 space-y-4 overflow-y-auto p-6">
          {submission.material_type === 'text' && (
            <div className="rounded-xl border border-outline/50 bg-surface-low/30 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="mb-3 flex items-center justify-between border-b border-outline/30 pb-2 dark:border-slate-800">
                <span className="text-xs font-bold uppercase tracking-wider text-muted">
                  Isi Materi Bacaan / Artikel
                </span>
                <span className="text-xs text-muted">Format Teks Terstruktur</span>
              </div>
              {submission.content ? (
                <div
                  className="prose prose-sm max-w-none space-y-3 leading-relaxed text-on-surface dark:prose-invert [&_blockquote]:border-l-4 [&_blockquote]:border-primary [&_blockquote]:pl-4 [&_blockquote]:italic [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:text-xl [&_h2]:font-bold [&_h3]:text-lg [&_h3]:font-bold [&_img]:max-w-full [&_img]:rounded-xl [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:my-2 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-outline/50 [&_td]:p-2 [&_th]:border [&_th]:border-outline/50 [&_th]:p-2 [&_ul]:list-disc [&_ul]:pl-5"
                  dangerouslySetInnerHTML={{ __html: submission.content }}
                />
              ) : (
                <p className="text-sm italic text-muted">Materi ini belum memiliki isi konten teks.</p>
              )}
            </div>
          )}

          {submission.material_type === 'video' && (
            <div className="space-y-4">
              {ytEmbedUrl ? (
                <div className="overflow-hidden rounded-xl border border-outline/50 bg-black shadow-sm">
                  <div className="relative aspect-video w-full">
                    <iframe
                      src={ytEmbedUrl}
                      title={submission.title}
                      className="absolute inset-0 size-full border-0"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      allowFullScreen
                    />
                  </div>
                  <div className="flex items-center justify-between bg-slate-900 px-4 py-2.5 text-xs text-slate-300">
                    <span>Sumber: YouTube</span>
                    <a
                      href={submission.content}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 font-semibold text-red-400 hover:underline"
                    >
                      Buka di YouTube
                      <ExternalLink className="size-3.5" />
                    </a>
                  </div>
                </div>
              ) : hasFile ? (
                <div className="overflow-hidden rounded-xl border border-outline/50 bg-black shadow-sm">
                  <video
                    controls
                    controlsList="nodownload"
                    className="max-h-[480px] w-full"
                    src={fileUrl}
                  >
                    Browser Anda tidak mendukung pemutar video HTML5.
                  </video>
                  <div className="flex items-center justify-between bg-slate-900 px-4 py-2.5 text-xs text-slate-300">
                    <span>File Video: {submission.original_file_name || 'video'}</span>
                    <a
                      href={fileUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 font-semibold text-red-400 hover:underline"
                    >
                      Buka di Tab Baru
                      <ExternalLink className="size-3.5" />
                    </a>
                  </div>
                </div>
              ) : (
                <div className="rounded-xl border border-dashed border-outline p-8 text-center text-sm text-muted">
                  Tautan atau file video tidak ditemukan.
                </div>
              )}
            </div>
          )}

          {submission.material_type === 'pdf' && (
            <div className="space-y-3">
              <div className="flex items-center justify-between rounded-lg border border-outline/50 bg-surface-low/60 px-4 py-2.5 dark:border-slate-800 dark:bg-slate-800/60">
                <span className="truncate text-xs font-semibold text-on-surface">
                  Dokumen: {submission.original_file_name || 'document.pdf'}
                </span>
                <a
                  href={fileUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1.5 rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-container"
                >
                  <ExternalLink className="size-3.5" />
                  Buka di Tab Baru
                </a>
              </div>
              <div className="overflow-hidden rounded-xl border border-outline/50 bg-white dark:border-slate-800">
                <iframe
                  src={fileUrl}
                  title={submission.title}
                  className="h-[520px] w-full border-0"
                />
              </div>
            </div>
          )}

          {submission.material_type === 'image' && (
            <div className="overflow-hidden rounded-xl border border-outline/50 bg-surface-low/30 p-4 text-center dark:border-slate-800 dark:bg-slate-900/40">
              <img
                src={fileUrl}
                alt={submission.title}
                className="mx-auto max-h-[500px] w-auto rounded-lg object-contain shadow-xs"
              />
              <p className="mt-3 text-xs text-muted">{submission.original_file_name}</p>
            </div>
          )}

          {submission.material_type === 'audio' && (
            <div className="rounded-xl border border-outline/50 bg-surface-low/50 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="mb-4 flex items-center gap-4">
                <span className="grid size-12 place-items-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                  <Headphones className="size-6" />
                </span>
                <div>
                  <h4 className="font-bold text-on-surface">{submission.title}</h4>
                  <p className="text-xs text-muted">
                    {submission.original_file_name || 'Audio Pembelajaran'}
                  </p>
                </div>
              </div>
              <audio controls className="w-full" src={fileUrl}>
                Browser Anda tidak mendukung pemutar audio.
              </audio>
            </div>
          )}

          {submission.material_type === 'document' && (
            <div className="rounded-xl border border-outline/50 bg-surface-low/30 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="flex flex-col items-center gap-4 sm:flex-row">
                <span className="grid size-14 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                  <File className="size-7" />
                </span>
                <div className="min-w-0 flex-1 text-center sm:text-left">
                  <h4 className="text-base font-bold text-on-surface">{submission.title}</h4>
                  <p className="mt-1 truncate text-xs text-muted">
                    File: {submission.original_file_name || 'Dokumen Pelatihan'}
                  </p>
                  <p className="mt-0.5 text-xs text-muted">
                    Dokumen Office / ODF dapat diunduh untuk ditinjau.
                  </p>
                </div>
                <a
                  href={fileUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary-container"
                >
                  <Download className="size-4" />
                  Unduh Dokumen
                </a>
              </div>
            </div>
          )}

          {submission.material_type === 'link' && (
            <div className="rounded-xl border border-outline/50 bg-surface-low/30 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="flex items-start gap-4">
                <span className="grid size-12 shrink-0 place-items-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300">
                  <Link2 className="size-6" />
                </span>
                <div className="min-w-0 flex-1">
                  <h4 className="font-bold text-on-surface">{submission.title}</h4>
                  <p className="mt-1 text-xs text-muted">Tautan Web Eksternal:</p>
                  <a
                    href={submission.content}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-2 inline-flex max-w-full items-center gap-2 break-all rounded-lg border border-teal-200 bg-teal-50/70 px-3.5 py-2 text-xs font-semibold text-teal-900 transition hover:bg-teal-100 dark:border-teal-900/60 dark:bg-teal-950/40 dark:text-teal-200"
                  >
                    <span className="truncate">{submission.content}</span>
                    <ExternalLink className="size-3.5 shrink-0" />
                  </a>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Footer Actions */}
        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-outline/40 bg-surface-low/40 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40">
          <Button type="button" variant="outline" size="sm" onClick={onClose}>
            Tutup
          </Button>

          <div className="flex flex-wrap items-center gap-2">
            {canEdit && (
              <Button
                type="button"
                variant="secondary"
                size="sm"
                icon={<Pencil className="size-3.5" />}
                onClick={() => {
                  onClose();
                  onEdit?.(submission);
                }}
              >
                {submission.status === 'rejected' ? 'Perbaiki Materi' : 'Edit Materi'}
              </Button>
            )}

            {(canSendToReviewer ?? isAdmin) && submission.status === 'pending_admin' && (
              <Button
                type="button"
                variant="danger"
                size="sm"
                icon={<XCircle className="size-3.5" />}
                onClick={() => {
                  onClose();
                  onOpenReject?.(submission);
                }}
              >
                Tolak / Minta Revisi
              </Button>
            )}

            {canReviewMaterial && submission.status === 'pending_review' && (
              <>
                <Button
                  type="button"
                  variant="danger"
                  size="sm"
                  disabled={busy}
                  icon={<XCircle className="size-3.5" />}
                  onClick={() => {
                    onClose();
                    onReviewerReject?.(submission);
                  }}
                >
                  Revisi Reviewer
                </Button>
                <Button
                  type="button"
                  variant="success"
                  size="sm"
                  disabled={busy}
                  icon={<CheckCircle2 className="size-3.5" />}
                  onClick={() => {
                    onClose();
                    onReviewerApprove?.(submission);
                  }}
                >
                  Setujui Review
                </Button>
              </>
            )}

            {(canPublishMaterial ?? isAdmin) && submission.status === 'approved' && (
              <Button
                type="button"
                variant="primary"
                size="sm"
                disabled={busy}
                loading={busy}
                icon={<Upload className="size-3.5" />}
                onClick={() => {
                  onClose();
                  onPublish?.(submission.submission_id);
                }}
              >
                Terbitkan ke Database Student
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
