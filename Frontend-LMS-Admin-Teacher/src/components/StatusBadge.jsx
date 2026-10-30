import React from 'react';

/**
 * Standard StatusBadge for consistent state and role visualization across DENSO LMS.
 *
 * @param {Object} props
 * @param {string} props.status - Status or role key (e.g. 'active', 'pending_admin', 'student').
 * @param {string} [props.label] - Custom human-readable label override.
 * @param {'sm' | 'md'} [props.size='md']
 * @param {boolean} [props.showDot=true] - Whether to show the status indicator dot.
 * @param {string} [props.className]
 */
export default function StatusBadge({
  status = '',
  label,
  size = 'md',
  showDot = true,
  className = ''
}) {
  const normalized = String(status).toLowerCase().trim();

  // Status mapping with semantic badge styling and localized labels
  const configMap = {
    // Account / General status
    active: {
      text: 'Aktif',
      dot: 'bg-emerald-500',
      classes: 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
    },
    inactive: {
      text: 'Nonaktif',
      dot: 'bg-slate-400',
      classes: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
    },

    // Workflow / Submission status
    pending: {
      text: 'Menunggu',
      dot: 'bg-amber-500 animate-pulse',
      classes: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
    },
    pending_admin: {
      text: 'Menunggu Admin',
      dot: 'bg-amber-500 animate-pulse',
      classes: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
    },
    pending_review: {
      text: 'Dalam Review',
      dot: 'bg-blue-500 animate-pulse',
      classes: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800'
    },
    approved: {
      text: 'Disetujui',
      dot: 'bg-emerald-500',
      classes: 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
    },
    published: {
      text: 'Terbit',
      dot: 'bg-teal-500',
      classes: 'bg-teal-50 text-teal-800 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800'
    },
    rejected: {
      text: 'Ditolak',
      dot: 'bg-red-500',
      classes: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800'
    },
    draft: {
      text: 'Draf',
      dot: 'bg-zinc-400',
      classes: 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700'
    },

    // Job / Queue status
    queued: {
      text: 'Dalam Antrean',
      dot: 'bg-slate-400',
      classes: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
    },
    running: {
      text: 'Diproses',
      dot: 'bg-sky-500 animate-pulse',
      classes: 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800'
    },
    completed: {
      text: 'Selesai',
      dot: 'bg-emerald-500',
      classes: 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
    },
    failed: {
      text: 'Gagal',
      dot: 'bg-red-500',
      classes: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800'
    },
    stopped: {
      text: 'Dihentikan',
      dot: 'bg-amber-500',
      classes: 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
    },

    // Roles
    admin: {
      text: 'Admin',
      dot: 'bg-red-600',
      classes: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800'
    },
    teacher: {
      text: 'Teacher',
      dot: 'bg-blue-600',
      classes: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800'
    },
    reviewer: {
      text: 'Reviewer',
      dot: 'bg-purple-600',
      classes: 'bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800'
    },
    student: {
      text: 'Student',
      dot: 'bg-emerald-600',
      classes: 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
    }
  };

  const current = configMap[normalized] || {
    text: status || '—',
    dot: 'bg-slate-400',
    classes: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
  };

  const displayText = label || current.text;

  const sizeClass = size === 'sm'
    ? 'text-[11px] px-2 py-0.5 gap-1.5'
    : 'text-xs px-2.5 py-1 gap-1.5 font-semibold';

  return (
    <span
      className={`inline-flex items-center rounded-full border ${sizeClass} ${current.classes} ${className}`}
    >
      {showDot && (
        <span
          className={`size-1.5 shrink-0 rounded-full ${current.dot}`}
          aria-hidden="true"
        />
      )}
      <span className="truncate">{displayText}</span>
    </span>
  );
}

