import { useState } from 'react';
import { Link } from 'react-router-dom';

const statusLabel = {
  pending_admin: 'Menunggu admin',
  pending_review: 'Menunggu reviewer',
  approved: 'Disetujui reviewer',
  rejected: 'Perlu diperbaiki',
  published: 'Terbit untuk student'
};

const TYPE_ICONS = {
  text: 'article', video: 'play_circle', pdf: 'picture_as_pdf', image: 'image',
  document: 'description', audio: 'audiotrack', link: 'link'
};

function statusStyle(status) {
  if (status === 'approved' || status === 'published') return 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300';
  if (status === 'rejected') return 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300';
  if (status === 'pending_review') return 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300';
  return 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300';
}

function externalPreview(value = '') {
  try {
    const url = new URL(value);
    if (url.protocol !== 'https:') return null;
    const host = url.hostname.toLowerCase().replace(/^www\./, '');
    if (host === 'youtu.be' || host === 'youtube.com' || host === 'm.youtube.com' || host === 'youtube-nocookie.com') {
      const id = host === 'youtu.be' ? url.pathname.split('/').filter(Boolean)[0] :
        (url.searchParams.get('v') || url.pathname.match(/\/(?:embed|shorts)\/([^/?]+)/)?.[1]);
      if (id && /^[A-Za-z0-9_-]{6,20}$/.test(id)) {
        return { platform: 'YouTube', icon: 'smart_display', image: `https://i.ytimg.com/vi/${id}/hqdefault.jpg`, url: value };
      }
    }
    if ((host === 'tiktok.com' || host.endsWith('.tiktok.com'))) {
      const id = url.pathname.match(/\/video\/(\d+)/)?.[1];
      return { platform: 'TikTok', icon: 'music_note', embed: id ? `https://www.tiktok.com/player/v1/${id}?description=0&music_info=0` : null, url: value };
    }
    if (host === 'instagram.com' || host.endsWith('.instagram.com')) {
      const match = url.pathname.match(/^\/(reel|reels|p|tv)\/([A-Za-z0-9_-]+)/);
      const pathType = match?.[1] === 'reels' ? 'reel' : match?.[1];
      return { platform: 'Instagram', icon: 'photo_camera', embed: match ? `https://www.instagram.com/${pathType}/${match[2]}/embed/` : null, url: value };
    }
    return { platform: host, icon: 'link', url: value };
  } catch { return null; }
}

function MediaCover({ item, onPreview }) {
  const preview = externalPreview(item.content || '');

  // Shared wrapper class: compact fixed height on mobile, aspect-video on sm+
  const wrapClass = 'relative h-28 w-full overflow-hidden sm:h-auto sm:aspect-video';

  if (item.material_type === 'video' && item.file_path) {
    return (
      <button type="button" onClick={onPreview} className={`group block bg-slate-900 text-left ${wrapClass}`} aria-label={`Pratinjau video ${item.title}`}>
        <span className="absolute inset-0 grid place-items-center text-white">
          <span className="material-symbol grid size-12 place-items-center rounded-full bg-black/55 text-3xl sm:size-14 sm:text-4xl" aria-hidden="true">play_arrow</span>
        </span>
        <span className="absolute inset-x-0 bottom-0 flex items-center gap-2 bg-black/65 px-3 py-1.5 text-[11px] font-medium text-white sm:py-2 sm:text-xs">
          <span className="material-symbol text-sm" aria-hidden="true">movie</span>
          <span className="truncate">{item.original_file_name || 'File video'}</span>
        </span>
      </button>
    );
  }
  if (preview?.image) {
    return (
      <button type="button" onClick={onPreview} className={`group block bg-slate-900 text-left ${wrapClass}`} aria-label={`Buka pratinjau ${preview.platform}: ${item.title}`}>
        <img src={preview.image} alt={`Thumbnail ${item.title}`} loading="lazy" className="size-full object-cover transition duration-200 group-hover:scale-[1.02]" onError={event => { event.currentTarget.style.display = 'none'; }} />
        <span className="absolute inset-0 grid place-items-center bg-black/15 text-white">
          <span className="material-symbol grid size-10 place-items-center rounded-full bg-black/60 text-2xl sm:size-12 sm:text-3xl" aria-hidden="true">play_arrow</span>
        </span>
        <span className="absolute bottom-1.5 left-2 rounded-md bg-black/75 px-2 py-0.5 text-[10px] font-semibold text-white sm:bottom-2 sm:text-[11px]">YouTube</span>
      </button>
    );
  }
  if (preview?.embed) {
    return (
      <div className={`bg-slate-950 ${wrapClass}`}>
        <iframe title={`${preview.platform}: ${item.title}`} src={preview.embed} loading="lazy" referrerPolicy="strict-origin-when-cross-origin" allow="encrypted-media; picture-in-picture; web-share" allowFullScreen className="size-full border-0" />
      </div>
    );
  }

  const label = preview?.platform || (item.material_type === 'video' ? 'Video materi' : item.material_type === 'link' ? 'Tautan materi' : item.original_file_name || 'Konten materi');
  const icon = preview?.icon || TYPE_ICONS[item.material_type] || 'article';
  const color = preview?.platform === 'TikTok' ? 'bg-slate-950 text-white' : preview?.platform === 'Instagram' ? 'bg-fuchsia-700 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
  return (
    <button type="button" onClick={onPreview} className={`group flex flex-col items-center justify-center gap-2 px-4 text-center ${color} ${wrapClass}`} aria-label={`Buka pratinjau ${item.title}`}>
      <span className="material-symbol grid size-10 place-items-center rounded-full border border-current/20 bg-white/10 text-2xl sm:size-12 sm:text-3xl" aria-hidden="true">{icon}</span>
      <span className="max-w-full truncate text-[11px] font-semibold sm:text-xs">{label}</span>
      {preview?.url && <span className="max-w-full truncate text-[10px] opacity-80">Buka pratinjau materi</span>}
    </button>
  );
}

/* Shared button style for action buttons in the card footer */
const smallButton = 'inline-flex h-9 min-w-0 w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border px-2.5 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-50';

export default function SubmissionCard({ item, currentUser, isAdmin, reviewers, busy, onPreview, onEdit, onReject, onSendReview, onPublish, onDelete }) {
  const [reviewerEmail, setReviewerEmail] = useState('');
  const isSubmitter = item.submitted_by === currentUser?.npk;
  const canTeacherEdit = !isAdmin && isSubmitter && item.status === 'rejected';
  const canAdminEdit = isAdmin && (item.status === 'pending_admin' || item.status === 'rejected');
  const canEdit = canAdminEdit || canTeacherEdit;
  const canDeleteSubmission = isAdmin || (isSubmitter && item.status !== 'pending_review');
  const plainText = item.material_type === 'text' ? (item.content || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() : '';
  const chosenReviewer = reviewers.find(reviewer => reviewer.email === reviewerEmail);

  return (
    <article className="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-slate-100 shadow-sm transition hover:border-primary/40 hover:shadow-md dark:border-slate-700 dark:bg-slate-800/90">
      <MediaCover item={item} onPreview={onPreview} />
      {/* Compact padding on mobile (p-3), comfortable on desktop (sm:p-4) */}
      <div className="flex min-w-0 flex-1 flex-col p-3 sm:p-4">
        <div className="flex items-start gap-3">
          <span className="grid size-10 shrink-0 place-items-center rounded-xl border border-outline/50 bg-white text-secondary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
            <span className="material-symbol text-xl" aria-hidden="true">{TYPE_ICONS[item.material_type] || 'article'}</span>
          </span>
          <div className="min-w-0 flex-1">
            <Link to={`/courses/${item.course_id}`} className="block break-words text-sm font-bold leading-5 text-on-surface hover:text-primary" title={item.title}>{item.title}</Link>
            <span className={`mt-2 inline-flex max-w-full items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold ${statusStyle(item.status)}`}>
              <span className="truncate">{statusLabel[item.status] || item.status}</span>
            </span>
          </div>
        </div>

        <div className="mt-4 space-y-2 border-t border-outline/40 pt-3 dark:border-slate-700">
          <div className="flex items-start gap-2 text-xs">
            <span className="material-symbol mt-0.5 text-sm text-muted" aria-hidden="true">menu_book</span>
            <p className="min-w-0 break-words font-semibold text-secondary dark:text-slate-200">{item.course_title || 'Course'} <span className="px-1 text-muted">/</span> {item.session_title || 'Sesi'}</p>
          </div>
          <div className="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11px] text-muted">
            <span className="material-symbol text-sm" aria-hidden="true">person</span>
            <span>Diajukan oleh</span><span className="break-all font-medium text-secondary dark:text-slate-300">{item.submitter_name || item.submitted_by}</span>
            {item.reviewer_name && <><span aria-hidden="true">·</span><span>Reviewer: <strong className="text-secondary dark:text-slate-300">{item.reviewer_name}</strong></span></>}
          </div>
          {plainText && <p className="line-clamp-2 rounded-lg bg-white px-3 py-2 text-xs leading-5 text-muted dark:bg-slate-900 border border-slate-200/60 dark:border-slate-700/60">{plainText}</p>}
          {item.original_file_name && !['video', 'text'].includes(item.material_type) && <p className="flex min-w-0 items-center gap-2 rounded-lg bg-white px-3 py-2 text-xs text-secondary dark:bg-slate-900 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60"><span className="material-symbol shrink-0 text-base" aria-hidden="true">{TYPE_ICONS[item.material_type] || 'description'}</span><span className="truncate">{item.original_file_name}</span></p>}
        </div>

        {item.reviewer_comment && <div className="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs leading-5 text-red-900 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"><strong>Catatan Reviewer: </strong>{item.reviewer_comment}</div>}
        {item.admin_comment && <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200"><strong>Catatan Admin: </strong>{item.admin_comment}</div>}

        {item.status === 'pending_admin' && !isAdmin && <p className="mt-3 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200"><span className="material-symbol" aria-hidden="true">lock</span>Menunggu persetujuan Admin</p>}
        {item.status === 'pending_review' && <p className="mt-3 flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs leading-5 font-medium text-blue-900 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200"><span className="material-symbol mt-0.5" aria-hidden="true">lock_clock</span><span>Materi terkunci selama ditinjau Reviewer{item.reviewer_name ? ` (${item.reviewer_name})` : ''}.</span></p>}
        {item.status === 'rejected' && <p className="mt-3 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-900 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"><span className="material-symbol" aria-hidden="true">assignment_late</span>Perlu diperbaiki sebelum diajukan kembali.</p>}

        <div className="mt-auto border-t border-outline/40 pt-4 dark:border-slate-700">
          <div className="grid grid-cols-2 gap-2">
            <button type="button" onClick={onPreview} className={`${smallButton} border-slate-300 bg-white text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-650`}>
              <span className="material-symbol !text-base" aria-hidden="true">visibility</span><span className="truncate">Pratinjau</span>
            </button>

            {canEdit && <button type="button" onClick={onEdit} className={`${smallButton} border-primary bg-primary text-white hover:bg-red-700`}>
              <span className="material-symbol !text-base" aria-hidden="true">edit</span><span className="truncate">{item.status === 'rejected' ? 'Perbaiki' : 'Edit'}</span>
            </button>}
          </div>

          {isAdmin && item.status === 'pending_admin' && <button type="button" onClick={onReject} className={`${smallButton} mt-2 !w-fit border-red-700 bg-red-700 px-3 text-white hover:bg-red-800`}>
            <span className="material-symbol !text-base" aria-hidden="true">assignment_return</span><span>Minta revisi</span>
          </button>}

          {isAdmin && item.status === 'pending_admin' && <form onSubmit={onSendReview} className="mt-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
            <label htmlFor={`reviewer-select-${item.submission_id}`} className="block text-xs font-semibold text-secondary dark:text-slate-200">Kirim ke Reviewer</label>
            <div className="mt-2 flex min-w-0 flex-col gap-2 sm:flex-row">
              {reviewers.length > 0 ? <select id={`reviewer-select-${item.submission_id}`} name="reviewerEmail" required value={reviewerEmail} onChange={event => setReviewerEmail(event.target.value)} className="h-10 min-w-0 flex-1 rounded-lg border border-outline/70 bg-white px-3 text-xs text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Pilih reviewer...</option>{reviewers.map(reviewer => <option key={reviewer.employee_npk} value={reviewer.email}>{reviewer.fullname}{reviewer.position ? ` (${reviewer.position})` : ''}</option>)}
              </select> : <input id={`reviewer-email-${item.submission_id}`} name="reviewerEmail" type="email" autoComplete="email" required maxLength="254" placeholder="reviewer@denso.co.id" value={reviewerEmail} onChange={event => setReviewerEmail(event.target.value)} className="h-10 min-w-0 flex-1 rounded-lg border border-outline/70 bg-white px-3 text-xs outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" />}
              <button type="submit" disabled={busy} className={`${smallButton} !w-fit shrink-0 border-slate-800 bg-slate-800 px-3 text-white hover:bg-slate-900 dark:border-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600`}><span className="material-symbol !text-base" aria-hidden="true">send</span><span>Kirim</span></button>
            </div>
            {(chosenReviewer || (reviewers.length === 0 && reviewerEmail.trim())) && <p role="status" className="mt-2 flex min-w-0 items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs leading-5 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
              <span className="material-symbol mt-0.5 shrink-0 text-base" aria-hidden="true">check_circle</span>
              <span className="min-w-0 break-all"><strong>{chosenReviewer ? 'Reviewer dipilih: ' : 'Email reviewer: '}</strong>{chosenReviewer ? `${chosenReviewer.fullname} · ${chosenReviewer.email}` : reviewerEmail.trim()}</span>
            </p>}
          </form>}

          {isAdmin && item.status === 'approved' && <button type="button" disabled={busy} onClick={onPublish} className={`${smallButton} mt-2 !w-fit border-emerald-700 bg-emerald-700 px-3 text-white hover:bg-emerald-800`}>
            <span className="material-symbol !text-base" aria-hidden="true">publish</span><span>Terbitkan</span>
          </button>}

          {item.status === 'published' && <p className="mt-2 flex min-h-9 items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"><span className="material-symbol" aria-hidden="true">check_circle</span>Telah terbit ke Student</p>}

          {canDeleteSubmission && item.status !== 'published' && <button type="button" onClick={onDelete} className="mt-2 inline-flex h-9 w-fit items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-semibold text-red-700 transition hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600/30 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-950">
            <span className="material-symbol !text-base" aria-hidden="true">delete</span><span>Hapus pengajuan</span>
          </button>}
        </div>
      </div>
    </article>
  );
}
