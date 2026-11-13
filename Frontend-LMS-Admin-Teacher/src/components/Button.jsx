import React from 'react';
import { Loader2 } from 'lucide-react';

/**
 * Button Component (design.md 8px radius, compact operational heights, DENSO red primary CTA).
 */
export default function Button({
  variant = 'primary',
  size = 'md',
  loading = false,
  icon,
  iconRight,
  disabled,
  className = '',
  children,
  ...props
}) {
  const variants = {
    primary: 'bg-primary text-white hover:bg-primary-container shadow-xs border border-primary/20',
    secondary: 'border border-slate-300/80 bg-slate-100 text-slate-800 hover:bg-slate-200/90 hover:text-slate-900 shadow-2xs dark:bg-slate-800/90 dark:text-slate-100 dark:border-slate-700 dark:hover:bg-slate-700',
    outline: 'border border-primary/40 bg-red-50/80 text-primary font-semibold hover:bg-red-100/90 shadow-2xs dark:bg-red-950/40 dark:text-red-300 dark:border-red-500/50 dark:hover:bg-red-900/50',
    ghost: 'bg-slate-100/60 text-slate-700 hover:bg-slate-200/80 hover:text-slate-900 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100',
    danger: 'bg-red-600 text-white hover:bg-red-700 shadow-xs border border-red-700/30',
    success: 'bg-tertiary text-white hover:opacity-90 shadow-xs border border-tertiary/30'
  };

  const sizes = {
    xs: 'h-7 px-2.5 text-xs gap-1.5 rounded-md',
    sm: 'h-8 px-3 text-xs gap-1.5 rounded-lg',
    md: 'h-10 px-4 text-sm gap-2 rounded-lg',
    lg: 'h-11 px-5 text-sm gap-2 rounded-lg'
  };

  return (
    <button
      type={props.type || 'button'}
      disabled={disabled || loading}
      className={`inline-flex shrink-0 cursor-pointer items-center justify-center font-medium transition-colors active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50 ${
        variants[variant] || variants.primary
      } ${sizes[size] || sizes.md} ${className}`}
      {...props}
    >
      {loading ? (
        <Loader2 className="h-4 w-4 animate-spin" />
      ) : icon ? (
        typeof icon === 'string' ? (
          <span className="material-symbol text-[18px] leading-none">{icon}</span>
        ) : (
          <span className="inline-flex shrink-0 items-center">{icon}</span>
        )
      ) : null}

      {children && <span>{children}</span>}

      {iconRight && !loading && (
        typeof iconRight === 'string' ? (
          <span className="material-symbol text-[18px] leading-none">{iconRight}</span>
        ) : (
          <span className="inline-flex shrink-0 items-center">{iconRight}</span>
        )
      )}
    </button>
  );
}
