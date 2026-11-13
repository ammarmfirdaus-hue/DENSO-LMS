import React from 'react';

/**
 * Card Component (design.md quiet analytical surface: 12px radius, hairline outline, 24px padding).
 */
export default function Card({
  id,
  title,
  subtitle,
  action,
  footer,
  noPadding = false,
  className = '',
  children,
  ...rest
}) {
  return (
    <section
      id={id}
      className={`dls-card-surface flex flex-col overflow-hidden rounded-xl ${className}`}
      {...rest}
    >
      {(title || subtitle || action) && (
        <div className="flex flex-wrap items-start justify-between gap-3 border-b border-outline px-6 py-4">
          <div className="min-w-0 flex-1">
            {title && (
              <h2 className="text-base font-bold leading-snug text-on-surface sm:text-[1.0625rem]">
                {title}
              </h2>
            )}
            {subtitle && (
              <p className="mt-0.5 text-xs leading-relaxed text-muted sm:text-[13px]">
                {subtitle}
              </p>
            )}
          </div>
          {action && <div className="flex shrink-0 items-center gap-2">{action}</div>}
        </div>
      )}

      <div className={noPadding ? 'flex-1' : 'flex-1 p-6'}>{children}</div>

      {footer && (
        <div className="border-t border-outline bg-surface-low/50 px-6 py-3 text-xs text-muted">
          {footer}
        </div>
      )}
    </section>
  );
}
