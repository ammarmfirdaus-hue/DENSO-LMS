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
    secondary: 'border border-slate-300 bg-slate-200 text-slate-800 hover:bg-slate-300 hover:text-slate-900 shadow-2xs dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 dark:hover:bg-slate-600',
    outline: 'border border-red-300 bg-red-100/80 text-red-800 font-semibold hover:bg-red-200/80 shadow-2xs dark:bg-red-950/60 dark:text-red-200 dark:border-red-700/70 dark:hover:bg-red-900/70',
    ghost: 'border border-slate-300/80 bg-slate-200/80 text-slate-800 hover:bg-slate-300/80 hover:text-slate-900 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700',
    info: 'bg-sky-600 text-white hover:bg-sky-700 shadow-xs border border-sky-700/30 dark:bg-sky-600 dark:hover:bg-sky-500',
    indigo: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-xs border border-indigo-700/30 dark:bg-indigo-600 dark:hover:bg-indigo-500',
    warning: 'bg-amber-600 text-white hover:bg-amber-700 shadow-xs border border-amber-700/30 dark:bg-amber-600 dark:hover:bg-amber-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 shadow-xs border border-red-700/30 dark:bg-red-600 dark:hover:bg-red-500',
    success: 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs border border-emerald-700/30 dark:bg-emerald-600 dark:hover:bg-emerald-500',
    neutral: 'bg-slate-600 text-white hover:bg-slate-700 shadow-xs border border-slate-700/30 dark:bg-slate-600 dark:hover:bg-slate-500'
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
