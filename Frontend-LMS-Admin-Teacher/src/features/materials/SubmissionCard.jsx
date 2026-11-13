import { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  AlertCircle,
  BookOpen,
  Camera,
  CheckCircle2,
  Eye,
  File,
  FileBadge,
  FileText,
  Film,
  Headphones,
  Image,
  Link2,
  Lock,
  Music,
  Pencil,
  Play,
  PlayCircle,
  RotateCcw,
  Send,
  Trash2,
  Upload,
  User
} from 'lucide-react';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';

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

function externalPreview(value = '') {
  try {
    const url = new URL(value);
    if (url.protocol !== 'https:') return null;
    const host = url.hostname.toLowerCase().replace(/^www\./, '');
    if (
      host === 'youtu.be' ||
      host === 'youtube.com' ||
      host === 'm.youtube.com' ||
      host === 'youtube-nocookie.com'
    ) {
      const id =
        host === 'youtu.be'
          ? url.pathname.split('/').filter(Boolean)[0]
          : url.searchParams.get('v') || url.pathname.match(/\/(?:embed|shorts)\/([^/?]+)/)?.[1];
      if (id && /^[A-Za-z0-9_-]{6,20}$/.test(id)) {
        return {
          platform: 'YouTube',
          Icon: PlayCircle,
          image: `https://i.ytimg.com/vi/${id}/hqdefault.jpg`,
          url: value
        };
      }
    }
    if (host === 'tiktok.com' || host.endsWith('.tiktok.com')) {
      const id = url.pathname.match(/\/video\/(\d+)/)?.[1];
      return {
        platform: 'TikTok',
        Icon: Music,
        embed: id ? `https://www.tiktok.com/player/v1/${id}?description=0&music_info=0` : null,
        url: value
      };
    }
    if (host === 'instagram.com' || host.endsWith('.instagram.com')) {
      const match = url.pathname.match(/^\/(reel|reels|p|tv)\/([A-Za-z0-9_-]+)/);
      const pathType = match?.[1] === 'reels' ? 'reel' : match?.[1];
      return {
        platform: 'Instagram',
        Icon: Camera,
        embed: match ? `https://www.instagram.com/${pathType}/${match[2]}/embed/` : null,
        url: value
      };
    }
    return { platform: host, Icon: Link2, url: value };
  } catch {
    return null;
  }
}

function MediaCover({ item, onPreview }) {
  const preview = externalPreview(item.content || '');
  const wrapClass = 'relative h-28 w-full overflow-hidden sm:h-auto sm:aspect-video';

  if (item.material_type === 'video' && item.file_path) {
    return (
      <button
        type="button"
        data-dls-action-ignore="true"
        onClick={onPreview}
        className={`group block bg-slate-900 text-left ${wrapClass}`}
        aria-label={`Pratinjau video ${item.title}`}
      >
        <span className="absolute inset-0 grid place-items-center text-white">
          <span className="grid size-12 place-items-center rounded-full bg-black/55 transition group-hover:scale-105 sm:size-14">
            <Play className="ml-0.5 size-6 fill-current" />
          </span>
        </span>
        <span className="absolute inset-x-0 bottom-0 flex items-center gap-2 bg-black/65 px-3 py-1.5 text-[11px] font-medium text-white sm:py-2 sm:text-xs">
          <Film className="size-3.5 shrink-0" />
          <span className="truncate">{item.original_file_name || 'File video'}</span>
        </span>
      </button>
    );
  }

  if (preview?.image) {
    return (
      <button
        type="button"
        data-dls-action-ignore="true"
        onClick={onPreview}
        className={`group block bg-slate-900 text-left ${wrapClass}`}
        aria-label={`Buka pratinjau ${preview.platform}: ${item.title}`}
      >
        <img
          src={preview.image}
          alt={`Thumbnail ${item.title}`}
          loading="lazy"
          className="size-full object-cover transition duration-200 group-hover:scale-[1.02]"
          onError={(event) => {
            event.currentTarget.style.display = 'none';
          }}
        />
        <span className="absolute inset-0 grid place-items-center bg-black/20 text-white">
          <span className="grid size-10 place-items-center rounded-full bg-black/60 transition group-hover:scale-105 sm:size-12">
            <Play className="ml-0.5 size-5 fill-current" />
          </span>
        </span>
        <span className="absolute bottom-2 left-2 rounded-md bg-black/75 px-2 py-0.5 text-[10px] font-semibold text-white">
          YouTube
        </span>
      </button>
    );
  }

  if (preview?.embed) {
    return (
      <div className={`bg-slate-950 ${wrapClass}`}>
        <iframe
          title={`${preview.platform}: ${item.title}`}
          src={preview.embed}
          loading="lazy"
          referrerPolicy="strict-origin-when-cross-origin"
          allow="encrypted-media; picture-in-picture; web-share"
          allowFullScreen
          className="size-full border-0"
        />
      </div>
    );
  }

  const label =
    preview?.platform ||
    (item.material_type === 'video'
      ? 'Video materi'
      : item.material_type === 'link'
        ? 'Tautan materi'
        : item.original_file_name || 'Konten materi');
  const CoverIcon = preview?.Icon || TYPE_ICON[item.material_type] || FileText;
  const color =
    preview?.platform === 'TikTok'
      ? 'bg-slate-950 text-white'
      : preview?.platform === 'Instagram'
        ? 'bg-fuchsia-700 text-white'
        : 'bg-surface-low text-on-surface-variant dark:bg-slate-800 dark:text-slate-200';

  return (
    <button
      type="button"
      data-dls-action-ignore="true"
      onClick={onPreview}
      className={`group flex flex-col items-center justify-center gap-2 px-4 text-center ${color} ${wrapClass}`}
      aria-label={`Buka pratinjau ${item.title}`}
    >
      <span className="grid size-11 place-items-center rounded-full border border-current/20 bg-white/20 dark:bg-black/20">
        <CoverIcon className="size-5" />
      </span>
      <span className="max-w-full truncate text-[11px] font-semibold sm:text-xs">{label}</span>
      {preview?.url && (
        <span className="max-w-full truncate text-[10px] opacity-80">Buka pratinjau materi</span>
      )}
    </button>
  );
}

/**
 * Kartu item pengajuan materi dalam alur persetujuan Admin & Reviewer.
 */
export default function SubmissionCard({
  item,
  currentUser,
  isAdmin,
  canSendToReviewer,
  canPublishMaterial,
  canReviewMaterial,
  canDeleteAll,
  reviewers,
  busy,
  onPreview,
  onEdit,
  onReject,
  onSendReview,
  onReviewerApprove,
  onReviewerReject,
  onPublish,
  onDelete
}) {
  const [reviewerEmail, setReviewerEmail] = useState('');
  const allowSendReview = canSendToReviewer ?? isAdmin;
  const allowPublish = canPublishMaterial ?? isAdmin;
  const allowReview = Boolean(canReviewMaterial);
  const allowDeleteAll = canDeleteAll ?? isAdmin;
  const isSubmitter = item.submitted_by === currentUser?.npk;
  const canTeacherEdit = !allowSendReview && isSubmitter && item.status === 'rejected';
  const canAdminEdit = allowSendReview && (item.status === 'pending_admin' || item.status === 'rejected');
  const canEdit = canAdminEdit || canTeacherEdit;
  const canDeleteSubmission = allowDeleteAll || (isSubmitter && item.status !== 'pending_review');
  const plainText =
    item.material_type === 'text'
      ? (item.content || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
      : '';
  const chosenReviewer = reviewers.find((reviewer) => reviewer.email === reviewerEmail);
  const TypeIcon = TYPE_ICON[item.material_type] || FileText;

  return (
    <article className="dls-card-surface flex h-full min-w-0 flex-col overflow-hidden rounded-xl transition hover:-translate-y-0.5 hover:shadow-md">
      <MediaCover item={item} onPreview={onPreview} />

      <div className="flex min-w-0 flex-1 flex-col p-4">
        <div className="flex items-start gap-3">
          <span className="grid size-10 shrink-0 place-items-center rounded-lg border border-outline/40 bg-surface-low text-secondary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <TypeIcon className="size-5" />
          </span>
          <div className="min-w-0 flex-1">
            <Link
              to={`/courses/${item.course_id}`}
              className="block break-words text-sm font-bold leading-snug text-on-surface transition hover:text-primary"
              title={item.title}
            >
              {item.title}
            </Link>
            <div className="mt-1.5">
              <StatusBadge
                status={item.status}
                label={STATUS_LABEL[item.status] || item.status}
                dot
              />
            </div>
          </div>
        </div>

        <div className="mt-4 space-y-2 border-t border-outline/40 pt-3 dark:border-slate-800">
          <div className="flex items-start gap-2 text-xs">
            <BookOpen className="mt-0.5 size-3.5 shrink-0 text-muted" />
            <p className="min-w-0 break-words font-semibold text-on-surface-variant">
              {item.course_title || 'Course'} <span className="px-1 text-muted">/</span>{' '}
              {item.session_title || 'Sesi'}
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11px] text-muted">
            <User className="size-3.5 shrink-0" />
            <span>Diajukan:</span>
            <span className="break-all font-semibold text-on-surface">
              {item.submitter_name || item.submitted_by}
            </span>
            {item.reviewer_name && (
              <>
                <span aria-hidden="true">·</span>
                <span>
                  Reviewer: <strong className="text-on-surface">{item.reviewer_name}</strong>
                </span>
              </>
            )}
          </div>

          {plainText && (
            <p className="line-clamp-2 rounded-lg border border-outline/40 bg-surface-low/60 px-3 py-2 text-xs leading-relaxed text-muted dark:border-slate-800 dark:bg-slate-900/60">
              {plainText}
            </p>
          )}

          {item.original_file_name && !['video', 'text'].includes(item.material_type) && (
            <p className="flex min-w-0 items-center gap-2 rounded-lg border border-outline/40 bg-surface-low/60 px-3 py-2 text-xs text-on-surface-variant dark:border-slate-800 dark:bg-slate-900/60">
              <TypeIcon className="size-4 shrink-0 text-muted" />
              <span className="truncate">{item.original_file_name}</span>
            </p>
          )}
        </div>

        {item.reviewer_comment && (
          <div className="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs leading-5 text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
            <strong>Catatan Reviewer: </strong>
            {item.reviewer_comment}
          </div>
        )}

        {item.admin_comment && (
          <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
            <strong>Catatan Admin: </strong>
            {item.admin_comment}
          </div>
        )}

        {item.status === 'pending_admin' && !allowSendReview && (
          <p className="mt-3 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
            <Lock className="size-3.5 shrink-0" />
            Menunggu persetujuan Admin
          </p>
        )}

        {item.status === 'pending_review' && (
          <p className="mt-3 flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-medium leading-5 text-blue-900 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-200">
            <Lock className="mt-0.5 size-3.5 shrink-0" />
            <span>
              Materi terkunci selama ditinjau Reviewer
              {item.reviewer_name ? ` (${item.reviewer_name})` : ''}.
            </span>
          </p>
        )}

        {item.status === 'rejected' && (
          <p className="mt-3 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
            <AlertCircle className="size-3.5 shrink-0" />
            Perlu diperbaiki sebelum diajukan kembali.
          </p>
        )}

        <div className="mt-auto space-y-2 border-t border-outline/40 pt-4 dark:border-slate-800">
          <div className="grid grid-cols-2 gap-2">
            <Button
              size="sm"
              variant="outline"
              icon={<Eye className="size-3.5" />}
              onClick={onPreview}
              className="w-full"
            >
              Pratinjau
            </Button>

            {canEdit && (
              <Button
                size="sm"
                variant="primary"
                icon={<Pencil className="size-3.5" />}
                onClick={onEdit}
                className="w-full"
              >
                {item.status === 'rejected' ? 'Perbaiki' : 'Edit'}
              </Button>
            )}
          </div>

          {allowSendReview && item.status === 'pending_admin' && (
            <form
              onSubmit={onSendReview}
              className="rounded-lg border border-outline/50 bg-surface-low/60 p-3 dark:border-slate-700 dark:bg-slate-800/50"
            >
              <label
                htmlFor={`reviewer-select-${item.submission_id}`}
                className="block text-xs font-semibold text-on-surface-variant"
              >
                Kirim ke Reviewer
              </label>
              <div className="mt-2 flex min-w-0 flex-col gap-2 sm:flex-row">
                {reviewers.length > 0 ? (
                  <select
                    id={`reviewer-select-${item.submission_id}`}
                    name="reviewerEmail"
                    required
                    value={reviewerEmail}
                    onChange={(event) => setReviewerEmail(event.target.value)}
                    className="h-9 min-w-0 flex-1 rounded-md border border-outline bg-surface-lowest px-2.5 text-xs text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                  >
                    <option value="">Pilih reviewer...</option>
                    {reviewers.map((reviewer) => (
                      <option key={reviewer.employee_npk} value={reviewer.email}>
                        {reviewer.fullname}
                        {reviewer.position ? ` (${reviewer.position})` : ''}
                      </option>
                    ))}
                  </select>
                ) : (
                  <input
                    id={`reviewer-email-${item.submission_id}`}
                    name="reviewerEmail"
                    type="email"
                    autoComplete="email"
                    required
                    maxLength="254"
                    placeholder="reviewer@denso.co.id"
                    value={reviewerEmail}
                    onChange={(event) => setReviewerEmail(event.target.value)}
                    className="h-9 min-w-0 flex-1 rounded-md border border-outline bg-surface-lowest px-2.5 text-xs text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                  />
                )}
                <Button
                  type="submit"
                  size="sm"
                  variant="secondary"
                  disabled={busy}
                  icon={<Send className="size-3.5" />}
                >
                  Kirim
                </Button>
              </div>

              {(chosenReviewer || (reviewers.length === 0 && reviewerEmail.trim())) && (
                <p
                  role="status"
                  className="mt-2 flex min-w-0 items-start gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[11px] leading-4 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200"
                >
                  <CheckCircle2 className="mt-0.5 size-3.5 shrink-0" />
                  <span className="min-w-0 break-all">
                    <strong>{chosenReviewer ? 'Reviewer: ' : 'Email: '}</strong>
                    {chosenReviewer
                      ? `${chosenReviewer.fullname} · ${chosenReviewer.email}`
                      : reviewerEmail.trim()}
                  </span>
                </p>
              )}
            </form>
          )}

          <div className="flex flex-wrap items-center gap-2">
            {allowSendReview && item.status === 'pending_admin' && (
              <Button
                size="xs"
                variant="danger"
                icon={<RotateCcw className="size-3.5" />}
                onClick={onReject}
              >
                Minta Revisi
              </Button>
            )}

            {allowReview && item.status === 'pending_review' && (
              <>
                <Button
                  size="xs"
                  variant="success"
                  disabled={busy}
                  icon={<CheckCircle2 className="size-3.5" />}
                  onClick={onReviewerApprove}
                >
                  Setujui Review
                </Button>
                <Button
                  size="xs"
                  variant="danger"
                  disabled={busy}
                  icon={<RotateCcw className="size-3.5" />}
                  onClick={onReviewerReject}
                >
                  Tolak Review
                </Button>
              </>
            )}

            {allowPublish && item.status === 'approved' && (
              <Button
                size="xs"
                variant="success"
                disabled={busy}
                icon={<Upload className="size-3.5" />}
                onClick={onPublish}
              >
                Terbitkan
              </Button>
            )}

            {item.status === 'published' && (
              <p className="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                <CheckCircle2 className="size-3.5" />
                Telah terbit ke Student
              </p>
            )}

            {canDeleteSubmission && item.status !== 'published' && (
              <Button
                size="xs"
                variant="outline"
                onClick={onDelete}
                icon={<Trash2 className="size-3.5 text-red-600" />}
                className="text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-950/40"
              >
                Hapus
              </Button>
            )}
          </div>
        </div>
      </div>
    </article>
  );
}
