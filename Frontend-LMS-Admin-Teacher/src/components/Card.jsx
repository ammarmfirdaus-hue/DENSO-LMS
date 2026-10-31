import React from 'react';

/**
 * Unified Card container with subcomponents for consistent page sections.
 */
export default function Card({ children, className = '', ...props }) {
  return (
    <section
      className={`rounded-2xl border border-slate-200/90 bg-slate-100 shadow-xs dark:border-slate-800 dark:bg-slate-800/90 ${className}`}
      {...props}
    >
      {children}
    </section>
  );
}

export function CardHeader({ children, className = '', ...props }) {
  return (
    <div
      className={`border-b border-outline/40 p-5 sm:p-6 dark:border-slate-800 ${className}`}
      {...props}
    >
      {children}
    </div>
  );
}

export function CardTitle({ children, className = '', ...props }) {
  return (
    <h2
      className={`text-lg font-bold tracking-tight text-on-surface sm:text-xl ${className}`}
      {...props}
    >
      {children}
    </h2>
  );
}

export function CardDescription({ children, className = '', ...props }) {
  return (
    <p
      className={`mt-1 text-sm text-muted ${className}`}
      {...props}
    >
      {children}
    </p>
  );
}

export function CardContent({ children, className = '', ...props }) {
  return (
    <div className={`p-5 sm:p-6 ${className}`} {...props}>
      {children}
    </div>
  );
}

export function CardFooter({ children, className = '', ...props }) {
  return (
    <div
      className={`border-t border-outline/40 bg-surface-low/30 p-4 sm:px-6 dark:border-slate-800 dark:bg-slate-900/30 ${className}`}
      {...props}
    >
      {children}
    </div>
  );
}

