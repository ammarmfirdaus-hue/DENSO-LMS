import React from 'react';

/**
 * Standard Button component for DENSO LMS Admin & Teacher portal.
 * Enforces unified styling, focus rings, loading states, and accessibility across all views.
 *
 * @param {Object} props
 * @param {'primary' | 'secondary' | 'outline' | 'danger' | 'danger-outline' | 'success' | 'ghost'} [props.variant='primary']
 * @param {'xs' | 'sm' | 'md' | 'lg'} [props.size='md']
 * @param {string | React.ReactNode} [props.icon] - Material Symbol icon name or custom element.
 * @param {'start' | 'end'} [props.iconPosition='start']
 * @param {boolean} [props.loading=false]
 * @param {boolean} [props.disabled=false]
 * @param {React.ReactNode} props.children
 * @param {string} [props.className]
 */
export default function Button({
  variant = 'primary',
  size = 'md',
  icon,
  iconPosition = 'start',
  loading = false,
  disabled = false,
  children,
  className = '',
  type = 'button',
  ...rest
}) {
  const baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition duration-150 cursor-pointer select-none outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98]';

  const sizeClasses = {
    xs: 'min-h-7 px-2.5 py-1 text-xs gap-1.5',
    sm: 'min-h-8 px-3 py-1.5 text-xs gap-1.5',
    md: 'min-h-10 px-4 py-2 text-sm gap-2',
    lg: 'min-h-11 px-5 py-2.5 text-sm sm:text-base gap-2.5'
  };

  const variantClasses = {
    primary: 'bg-primary text-white hover:bg-[#b9141b] shadow-xs focus-visible:ring-primary/40 dark:bg-red-600 dark:hover:bg-red-700',
    secondary: 'bg-[#173e67] text-white hover:bg-[#112d4d] shadow-xs focus-visible:ring-secondary/40',
    outline: 'border border-outline/70 bg-white text-secondary hover:bg-surface-low hover:border-outline focus-visible:ring-primary/20 dark:border-slate-700 dark:bg-slate-850 dark:text-slate-200 dark:hover:bg-slate-800',
    danger: 'bg-red-600 text-white hover:bg-red-700 shadow-xs focus-visible:ring-red-500/40',
    'danger-outline': 'border border-red-200 bg-white text-red-700 hover:bg-red-50 focus-visible:ring-red-400/30 dark:border-red-900/60 dark:bg-slate-900 dark:text-red-300 dark:hover:bg-red-950/40',
    success: 'border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 focus-visible:ring-emerald-400/30 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60',
    ghost: 'text-secondary hover:bg-surface-low hover:text-on-surface focus-visible:ring-primary/20 dark:text-slate-300 dark:hover:bg-slate-800'
  };

  const currentSize = sizeClasses[size] || sizeClasses.md;
  const currentVariant = variantClasses[variant] || variantClasses.primary;

  const renderIcon = () => {
    if (loading) {
      return (
        <svg
          className="size-4 animate-spin text-current"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <circle
            className="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            strokeWidth="4"
          />
          <path
            className="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
          />
        </svg>
      );
    }

    if (!icon) return null;

    if (typeof icon === 'string') {
      return <span className="material-symbol text-base sm:text-lg leading-none" aria-hidden="true">{icon}</span>;
    }

    return icon;
  };

  return (
    <button
      type={type}
      disabled={disabled || loading}
      className={`${baseClasses} ${currentSize} ${currentVariant} ${className}`}
      {...rest}
    >
      {iconPosition === 'start' && renderIcon()}
      {children}
      {iconPosition === 'end' && renderIcon()}
    </button>
  );
}

