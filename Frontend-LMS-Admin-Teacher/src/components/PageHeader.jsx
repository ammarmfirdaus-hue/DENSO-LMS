import React from 'react';

/**
 * PageHeader Component (design.md compact operational headline: 1.5rem bold + 0.75rem tracked eyebrow).
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
    <header className={`mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between ${className}`}>
      <div className="min-w-0 flex-1">
        {eyebrow && (
          <p className="text-xs font-semibold uppercase tracking-[0.05em] text-primary dark:text-red-400">
            {eyebrow}
          </p>
        )}
        <div className="mt-1 flex flex-wrap items-center gap-3">
          <h1 className="text-2xl font-bold tracking-tight text-on-surface">
            {title}
          </h1>
          {badge && <div className="shrink-0">{badge}</div>}
        </div>
        {description && (
          <p className="mt-1.5 max-w-3xl text-sm leading-relaxed text-muted">
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
