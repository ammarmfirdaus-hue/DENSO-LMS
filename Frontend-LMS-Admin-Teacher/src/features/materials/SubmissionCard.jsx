import { Link } from 'react-router-dom';

const statusLabel = {
  pending_admin: 'Menunggu admin',
  pending_review: 'Menunggu reviewer',
  approved: 'Disetujui reviewer',
  rejected: 'Perlu diperbaiki',
  published: 'Terbit untuk student'
};

function statusStyle(status) {
  if (status === 'approved' || status === 'published') return 'bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-950/30 dark:text-emerald-300';
  if (status === 'rejected') return 'bg-red-50 text-red-800 ring-red-600/20 dark:bg-red-950/30 dark:text-red-300';
  if (status === 'pending_review') return 'bg-blue-50 text-blue-800 ring-blue-600/20 dark:bg-blue-950/30 dark:text-blue-300';
  return 'bg-amber-50 text-amber-900 ring-amber-600/20 dark:bg-amber-950/30 dark:text-amber-300';
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

export default function SubmissionCard({
  item,
  currentUser,
  isAdmin,
  reviewers,
  busy,
  onPreview,
  onEdit,
  onReject,
  onSendReview,
  onPublish,
  onDelete
}) {
  const isSubmitter = item.submitted_by === currentUser?.npk;
  const canTeacherEdit = !isAdmin && isSubmitter && item.status === 'rejected';
  const canAdminEdit = isAdmin && (item.status === 'pending_admin' || item.status === 'rejected');
  const canEdit = canAdminEdit || canTeacherEdit;
  const canDeleteSubmission = isAdmin || (isSubmitter && item.status !== 'pending_review');

  return (
    <article className="flex min-w-0 flex-col rounded-2xl border border-outline/50 bg-white dark:bg-slate-900 p-4 transition hover:border-primary/40 hover:shadow-xs">
      <div className="flex h-full min-w-0 flex-col gap-3">
        <div className="min-w-0 flex-1 space-y-2.5">
          
          {/* Header: Type icon, title, status badge */}
          <div className="flex items-start gap-2.5">
            <span className="grid size-9 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
              <span className="material-symbol text-lg" aria-hidden="true">
                {TYPE_ICONS[item.material_type] || 'article'}
              </span>
            </span>
            <div className="min-w-0 flex-1">
              <Link
                to={`/courses/${item.course_id}`}
                className="block font-bold text-sm text-on-surface hover:text-primary transition line-clamp-1"
                title={item.title}
              >
                {item.title}
              </Link>
              <span className={`inline-block mt-1 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset ${statusStyle(item.status)}`}>
                {statusLabel[item.status] || item.status}
              </span>
            </div>
          </div>

          {/* Course & Session info */}
          <div>
            <p className="text-xs font-semibold text-secondary dark:text-slate-200 line-clamp-1" title={`${item.course_title || 'Course'} / ${item.session_title || 'Sesi'}`}>
              {item.course_title || 'Course'} <span className="px-1 text-outline">/</span> {item.session_title || 'Sesi'}
            </p>
            <p className="mt-0.5 text-[11px] text-muted">
              Tipe: <span className="font-semibold uppercase text-secondary dark:text-slate-300">{item.material_type}</span> <span className="px-0.5">·</span> diajukan oleh {item.submitter_name || item.submitted_by}
            </p>
            {item.reviewer_name && (
              <p className="text-[11px] font-medium text-slate-700 dark:text-slate-300">
                Reviewer: <span className="font-bold">{item.reviewer_name}</span>
              </p>
            )}
          </div>

          {/* Compact Content Preview Strip */}
          <div className="rounded-xl border border-outline/40 bg-surface-low/40 dark:bg-slate-800/40 p-2.5 text-xs">
            {item.material_type === 'video' && (
              item.content ? (
                <div className="flex items-center gap-1.5 text-[11px] text-muted">
                  <span className="material-symbol text-base text-red-600 shrink-0">smart_display</span>
                  <span className="truncate">YouTube: {item.content}</span>
                </div>
              ) : (
                <div className="flex items-center gap-1.5 text-[11px] text-muted">
                  <span className="material-symbol text-base text-indigo-600 shrink-0">movie</span>
                  <span className="truncate">{item.original_file_name || 'File Video MP4'}</span>
                </div>
              )
            )}

            {item.material_type === 'text' && (
              <div className="flex items-start gap-1.5 text-[11px] text-secondary dark:text-slate-300">
                <span className="material-symbol text-base text-emerald-600 shrink-0">article</span>
                <p className="line-clamp-2 italic text-muted">
                  {item.content ? item.content.replace(/<[^>]+>/g, ' ').slice(0, 100) : 'Konten teks TinyMCE.'}
                </p>
              </div>
            )}

            {item.material_type === 'pdf' && (
              <div className="flex items-center justify-between gap-2 text-[11px]">
                <div className="flex items-center gap-1.5 min-w-0">
                  <span className="material-symbol text-base text-red-600 shrink-0">picture_as_pdf</span>
                  <span className="truncate font-medium text-slate-800 dark:text-slate-200">
                    {item.original_file_name || 'Dokumen PDF'}
                  </span>
                </div>
                <span className="text-[10px] font-bold text-red-600 dark:text-red-400 shrink-0">PDF</span>
              </div>
            )}

            {item.material_type === 'image' && (
              <div className="flex items-center gap-1.5 text-[11px] text-muted">
                <span className="material-symbol text-base text-amber-600 shrink-0">image</span>
                <span className="truncate">{item.original_file_name || 'File Gambar'}</span>
              </div>
            )}

            {item.material_type === 'document' && (
              <div className="flex items-center gap-1.5 text-[11px] text-muted">
                <span className="material-symbol text-base text-blue-600 shrink-0">description</span>
                <span className="truncate">{item.original_file_name || 'Dokumen Office / ODF'}</span>
              </div>
            )}

            {item.material_type === 'audio' && (
              <div className="flex items-center gap-1.5 text-[11px] text-muted">
                <span className="material-symbol text-base text-purple-600 shrink-0">audiotrack</span>
                <span className="truncate">{item.original_file_name || 'File Audio'}</span>
              </div>
            )}

            {item.material_type === 'link' && (
              <div className="flex items-center gap-1.5 text-[11px] text-teal-700 dark:text-teal-400">
                <span className="material-symbol text-base shrink-0">link</span>
                <span className="truncate">{item.content}</span>
              </div>
            )}
          </div>

          {/* Admin & Reviewer Notes */}
          {item.reviewer_comment && (
            <div className="rounded-lg border border-red-200 bg-red-50/80 dark:border-red-900/40 dark:bg-red-950/20 px-2.5 py-1.5 text-[11px] text-red-900 dark:text-red-300">
              <span className="font-bold">Catatan Reviewer: </span>{item.reviewer_comment}
            </div>
          )}
          {item.admin_comment && (
            <div className="rounded-lg border border-amber-200 bg-amber-50/80 dark:border-amber-900/40 dark:bg-amber-950/20 px-2.5 py-1.5 text-[11px] text-amber-900 dark:text-amber-300">
              <span className="font-bold">Catatan Admin: </span>{item.admin_comment}
            </div>
          )}

          {/* Lock indicator */}
          {!isAdmin && item.status === 'pending_admin' && (
            <div className="flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50/70 p-2 text-[11px] font-semibold text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/25 dark:text-amber-300">
              <span className="material-symbol text-sm shrink-0">lock</span>
              <span>Menunggu persetujuan Admin</span>
            </div>
          )}
          {item.status === 'pending_review' && (
            <div className="flex items-start gap-1.5 rounded-lg border border-blue-200 bg-blue-50/70 p-2 text-[11px] font-semibold text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/25 dark:text-blue-300">
              <span className="material-symbol text-sm shrink-0 mt-0.5">lock_clock</span>
              <span>Materi Terkunci: Sedang ditinjau Reviewer {item.reviewer_name ? `(${item.reviewer_name})` : ''}. Materi terkunci dari perubahan sampai ada evaluasi.</span>
            </div>
          )}
          {item.status === 'rejected' && (
            <div className="flex items-start gap-1.5 rounded-lg border border-red-200 bg-red-50/70 p-2 text-[11px] font-semibold text-red-900 dark:border-red-900/40 dark:bg-red-950/25 dark:text-red-300">
              <span className="material-symbol text-sm shrink-0 mt-0.5">assignment_late</span>
              <span>Perlu perbaikan sebelum diajukan kembali.</span>
            </div>
          )}

        </div>

        {/* Action Buttons Footer */}
        <div className="mt-auto flex min-w-0 flex-col gap-2 pt-2 border-t border-outline/30">
          {/* Tombol Pratinjau Lengkap */}
          <button
            type="button"
            onClick={onPreview}
            className="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-xl bg-surface-low border border-outline/70 px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-primary/10 hover:border-primary/40 hover:text-primary cursor-pointer"
          >
            <span className="material-symbol text-base text-primary" aria-hidden="true">visibility</span>
            Lihat Pratinjau &amp; Detail
          </button>

          {/* Edit Button */}
          {canEdit && (
            <button
              type="button"
              onClick={onEdit}
              className="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-xl border border-primary/50 bg-primary/5 px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-primary/10 cursor-pointer"
            >
              <span className="material-symbol text-base" aria-hidden="true">edit</span>
              {item.status === 'rejected' ? 'Perbaiki & Ajukan Ulang' : 'Edit Materi'}
            </button>
          )}

          {/* Admin Reject Button */}
          {isAdmin && item.status === 'pending_admin' && (
            <button
              type="button"
              onClick={onReject}
              className="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-100 cursor-pointer"
            >
              <span className="material-symbol text-base" aria-hidden="true">cancel</span>
              Tolak / Minta Revisi
            </button>
          )}

          {/* Admin Send to Reviewer Form */}
          {isAdmin && item.status === 'pending_admin' && (
            <form onSubmit={onSendReview} className="rounded-xl bg-surface-low/70 p-2.5">
              <label htmlFor={`reviewer-select-${item.submission_id}`} className="block text-[11px] font-bold text-secondary">
                Kirim ke Reviewer
              </label>
              <div className="mt-1.5 flex flex-col gap-1.5">
                {reviewers.length > 0 ? (
                  <select
                    id={`reviewer-select-${item.submission_id}`}
                    name="reviewerEmail"
                    required
                    className="h-8 min-w-0 rounded-lg border border-outline/70 bg-white px-2 text-xs outline-none transition focus:border-primary dark:bg-slate-900"
                  >
                    <option value="">-- Pilih Reviewer --</option>
                    {reviewers.map(r => (
                      <option key={r.employee_npk} value={r.email}>
                        {r.fullname} {r.position ? `(${r.position})` : ''}
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
                    className="h-8 min-w-0 rounded-lg border border-outline/70 bg-white px-2.5 text-xs outline-none transition placeholder:text-muted/70 focus:border-primary dark:bg-slate-900"
                  />
                )}
                <button
                  disabled={busy}
                  className="inline-flex min-h-8 items-center justify-center gap-1.5 rounded-lg bg-secondary px-3 py-1 text-xs font-bold text-white transition hover:bg-[#242c33] disabled:opacity-50 cursor-pointer"
                >
                  <span className="material-symbol text-sm" aria-hidden="true">mail</span>
                  Kirim link review
                </button>
              </div>
            </form>
          )}

          {/* Admin Publish Button */}
          {isAdmin && item.status === 'approved' && (
            <button
              type="button"
              disabled={busy}
              onClick={onPublish}
              className="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-xs font-bold text-white shadow-xs transition hover:brightness-110 disabled:opacity-50 cursor-pointer"
            >
              <span className="material-symbol text-base" aria-hidden="true">cloud_upload</span>
              Unggah ke Database Student
            </button>
          )}

          {/* Status Published Indicator */}
          {item.status === 'published' && (
            <div className="flex items-center justify-center gap-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 px-3 py-1.5 text-xs font-bold text-emerald-800 dark:text-emerald-300">
              <span className="material-symbol text-base" aria-hidden="true">check_circle</span>
              Telah terbit ke student
            </div>
          )}

          {/* Delete unapproved submission */}
          {canDeleteSubmission && item.status !== 'published' && (
            <button
              type="button"
              onClick={onDelete}
              className="inline-flex items-center justify-center gap-1 text-[11px] font-semibold text-red-600 hover:text-red-800 hover:underline pt-0.5 cursor-pointer"
            >
              <span className="material-symbol text-sm" aria-hidden="true">delete</span>
              Hapus pengajuan ini
            </button>
          )}
        </div>
      </div>
    </article>
  );
}
