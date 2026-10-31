import React from 'react';

/**
 * Metric summary card for KPI counters across admin & teacher dashboards.
 *
 * @param {Object} props
 * @param {string} props.label - Description label (e.g. "Total Pengguna").
 * @param {string | number} props.value - Numeric or text stat value.
 * @param {string} props.icon - Material Symbol icon name.
 * @param {'primary' | 'emerald' | 'amber' | 'blue' | 'neutral' | string} [props.tone='primary'] - Accent tone.
 * @param {string | React.ReactNode} [props.subtext] - Optional helper/footer footnote.
 * @param {boolean} [props.loading=false]
 * @param {string} [props.className]
 */
export default function StatCard({
  label,
  value,
  icon,
  tone = 'primary',
  subtext,
  loading = false,
  className = ''
}) {
  const tonePresets = {
    primary: 'bg-primary/10 text-primary dark:bg-red-950/40 dark:text-red-300',
    emerald: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
    amber: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
    blue: 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
    neutral: 'bg-surface-low text-secondary dark:bg-slate-800 dark:text-slate-200'
  };

  const iconClasses = tonePresets[tone] || tone;

  return (
    <article
      className={`rounded-2xl border border-slate-200/90 bg-slate-100 p-5 shadow-xs transition hover:shadow-sm dark:border-slate-800 dark:bg-slate-800/90 ${className}`}
    >
      <div className="flex items-center justify-between gap-3">
        <div className="min-w-0 flex-1">
          <p className="truncate text-xs font-medium uppercase tracking-wider text-muted">
            {label}
          </p>
          <div className="mt-2 text-2xl font-bold tracking-tight text-on-surface sm:text-3xl">
            {loading ? (
              <span className="inline-block h-8 w-16 animate-pulse rounded-md bg-surface-low dark:bg-slate-800" />
            ) : (
              typeof value === 'number' ? value.toLocaleString('id-ID') : (value ?? '0')
            )}
          </div>
        </div>

        {icon && (
          <span
            className={`grid size-12 shrink-0 place-items-center rounded-2xl ${iconClasses}`}
            aria-hidden="true"
          >
            <span className="material-symbol text-2xl">{icon}</span>
          </span>
        )}
      </div>

      {subtext && (
        <div className="mt-3 flex items-center gap-1.5 border-t border-outline/30 pt-3 text-xs text-muted dark:border-slate-800">
          {subtext}
        </div>
      )}
    </article>
  );
}

