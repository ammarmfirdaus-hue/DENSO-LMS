import React from 'react';

export const STATUS_STYLE_MAP = {
  // Ghost Dashed Slate (Belum dibuat, Belum dimulai, Kosong, Belum ditugaskan)
  not_created: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  belum_dibuat: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  belum_ada: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  not_started: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  belum_mulai: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  unassigned: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  empty: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',
  none: 'bg-white/70 text-slate-500 border-dashed border-slate-300 dark:bg-slate-900/30 dark:text-slate-400 dark:border-slate-600',

  // Sky / Indigo (Draft)
  draft: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-200 dark:border-sky-800/60',
  draf: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-200 dark:border-sky-800/60',

  // Amber / Yellow (Pending, Menunggu, Antrean, Retry, Warning, Pilihan, Terjadwal)
  pending: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  pending_admin: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  waiting: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  menunggu: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  retry: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  warning: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  pilihan: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  scheduled: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  terjadwal: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  under_review: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',
  award: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/60',

  // Blue (In Review, In Progress, Running, Info, Teacher, Dokumen)
  pending_review: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  in_review: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  in_progress: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  processing: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  running: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  berjalan: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  syncing: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  info: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  teacher: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',
  document: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/60',

  // Purple / Lavender (Approved, Reviewer, Integrated)
  approved: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/60',
  reviewer: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/60',
  integrated: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/60',

  // Emerald Green (Active, Open, Published, Completed, Sent, Success, Visible, Lulus)
  active: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  aktif: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  open: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  terbuka: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  published: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  terbit: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  completed: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  selesai: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  sent: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  terkirim: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  success: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  berhasil: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  visible: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  tampil: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  lulus: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',
  passed: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/60',

  // Soft Red (Rejected, Failed, Remedial, Locked, Important, Wajib, Admin, Certificate)
  rejected: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  ditolak: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  failed: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  gagal: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  remedial: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  admin: 'bg-red-50 text-primary border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  wajib: 'bg-red-50 text-primary border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  important: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  penting: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  locked: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  terkunci: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  error: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  danger: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  revoked: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  suspended: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',
  certificate: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/60',

  // Solid Cool Slate (Inactive/Nonaktif, Closed, Stopped, Hidden, Dropped, Archived, Student)
  inactive: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  nonaktif: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  non_aktif: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  disabled: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  off: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  closed: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  ditutup: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  stopped: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  dihentikan: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  hidden: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  disembunyikan: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  dropped: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  archived: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  diarsipkan: 'bg-slate-200/80 text-slate-800 border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
  student: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  neutral: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  default: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
};

export const LABEL_MAP = {
  not_created: 'Belum dibuat',
  belum_dibuat: 'Belum dibuat',
  belum_ada: 'Belum ada',
  not_started: 'Belum dimulai',
  belum_mulai: 'Belum dimulai',
  unassigned: 'Belum ditugaskan',
  pending_admin: 'Antrean Admin',
  pending_review: 'Dalam Tinjauan',
  in_review: 'Dalam Tinjauan',
  under_review: 'Dalam Tinjauan',
  in_progress: 'Berlangsung',
  processing: 'Diproses',
  running: 'Berjalan',
  approved: 'Disetujui Reviewer',
  published: 'Dipublikasi',
  rejected: 'Perlu Revisi',
  pending: 'Menunggu',
  waiting: 'Menunggu',
  scheduled: 'Terjadwal',
  active: 'Aktif',
  aktif: 'Aktif',
  open: 'Terbuka',
  inactive: 'Nonaktif',
  nonaktif: 'Nonaktif',
  non_aktif: 'Nonaktif',
  disabled: 'Nonaktif',
  closed: 'Ditutup',
  completed: 'Selesai',
  dropped: 'Dihentikan',
  stopped: 'Dihentikan',
  archived: 'Diarsipkan',
  draft: 'Draf',
  visible: 'Tampil',
  hidden: 'Disembunyikan',
  locked: 'Terkunci',
  sent: 'Terkirim',
  retry: 'Coba Ulang',
  failed: 'Gagal',
  remedial: 'Remedial',
  lulus: 'Lulus',
  passed: 'Lulus',
  certificate: 'Sertifikat',
  award: 'Penghargaan',
  document: 'Dokumen'
};

const HOLLOW_DOT_KEYS = new Set([
  'not_created',
  'belum_dibuat',
  'belum_ada',
  'not_started',
  'belum_mulai',
  'unassigned',
  'empty',
  'none'
]);

/**
 * StatusBadge Component (consistent soft semantic pill badges across all pages).
 */
export default function StatusBadge({
  status = 'neutral',
  label,
  dot = false,
  className = ''
}) {
  const key = String(status || 'neutral').trim().toLowerCase().replace(/[\s-]+/g, '_');
  const styleClass = STATUS_STYLE_MAP[key] || STATUS_STYLE_MAP.default;
  const displayLabel = label ?? LABEL_MAP[key] ?? status;
  const isHollow = HOLLOW_DOT_KEYS.has(key);

  return (
    <span
      className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold leading-tight whitespace-nowrap ${styleClass} ${className}`}
    >
      {dot && (
        <span
          className={`h-1.5 w-1.5 shrink-0 rounded-full ${
            isHollow ? 'border-[1.5px] border-current bg-transparent' : 'bg-current opacity-85'
          }`}
        />
      )}
      <span>{displayLabel}</span>
    </span>
  );
}
