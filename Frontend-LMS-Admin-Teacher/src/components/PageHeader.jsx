import React from 'react';

/**
 * PageHeader Component.
 * Standard page title banner for all administrative and trainer views.
 *
 * @param {Object} props
 * @param {string} [props.eyebrow] - Uppercase category kicker (e.g. "PENGELOLAAN PEMBELAJARAN").
 * @param {string} props.title - Primary heading text (h1).
 * @param {string} [props.description] - Contextual explanation or subtitle.
 * @param {React.ReactNode} [props.actions] - Right-aligned action buttons or controls.
 * @param {React.ReactNode} [props.badge] - Optional badge or status indicator next to title.
 * @param {string} [props.className] - Additional wrapper CSS classes.
 */
export default function PageHeader({
  eyebrow,
  title,
  description,
  actions,
  badge,
  className = ''
}) {
  return (
    <header className={`mb-6 sm:mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between ${className}`}>
      <div className="min-w-0 flex-1">
        {eyebrow && (
          <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-primary dark:text-red-400">
            {eyebrow}
          </p>
        )}
        <div className="mt-1 flex flex-wrap items-center gap-3">
          <h1 className="text-2xl font-bold tracking-tight text-on-surface sm:text-3xl">
            {title}
          </h1>
          {badge && <div className="shrink-0">{badge}</div>}
        </div>
        {description && (
          <p className="mt-2 max-w-3xl text-sm leading-relaxed text-muted">
            {description}
          </p>
        )}
      </div>

      {actions && (
        <div className="flex shrink-0 flex-wrap items-center gap-2.5 sm:self-end">
          {actions}
        </div>
      )}
    </header>
  );
}

