import React from 'react';

const STATUS_STYLE_MAP = {
  // Amber / Yellow (Pending, Choice, Mock, Retry)
  pending: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  pending_admin: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  retry: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  warning: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  pilihan: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',

  // Blue (In Review / Development / Teacher / Wajib)
  pending_review: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  in_review: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  info: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  teacher: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  wajib: 'bg-red-50 text-primary border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',

  // Purple / Lavender (Approved, Reviewer, Integrated)
  approved: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/60',
  reviewer: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/60',
  integrated: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/60',

  // Emerald Green (Active, Published, Completed, Sent, Success)
  active: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  aktif: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  published: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  completed: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  selesai: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  sent: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  success: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  visible: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  lulus: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',

  // Soft Red (Rejected, Admin, Failed, Important, Locked)
  rejected: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  failed: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  gagal: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  admin: 'bg-red-50 text-primary border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  important: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  penting: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  locked: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  error: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  danger: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',

  // Gray / Neutral (Draft, Inactive, Stopped, Hidden, Dropped, Student)
  draft: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  inactive: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  nonaktif: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  stopped: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  hidden: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  dropped: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  student: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  neutral: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  default: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
};

const LABEL_MAP = {
  pending_admin: 'Antrean Admin',
  pending_review: 'Dalam Tinjauan',
  approved: 'Disetujui Reviewer',
  published: 'Dipublikasi',
  rejected: 'Perlu Revisi',
  pending: 'Menunggu',
  active: 'Aktif',
  inactive: 'Nonaktif',
  completed: 'Selesai',
  dropped: 'Dihentikan',
  draft: 'Draf',
  visible: 'Tampil',
  hidden: 'Disembunyikan',
  locked: 'Terkunci',
  sent: 'Terkirim',
  retry: 'Coba Ulang',
  failed: 'Gagal'
};

/**
 * StatusBadge Component (design.md soft semantic pill badges).
 */
export default function StatusBadge({
  status = 'neutral',
  label,
  dot = false,
  className = ''
}) {
  const key = String(status).toLowerCase();
  const styleClass = STATUS_STYLE_MAP[key] || STATUS_STYLE_MAP.default;
  const displayLabel = label ?? LABEL_MAP[key] ?? status;

  return (
    <span
      className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold leading-tight ${styleClass} ${className}`}
    >
      {dot && <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-85" />}
      <span>{displayLabel}</span>
    </span>
  );
}
