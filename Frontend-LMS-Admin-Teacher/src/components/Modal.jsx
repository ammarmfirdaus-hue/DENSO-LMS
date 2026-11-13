import React, { useEffect, useRef } from 'react';
import { X } from 'lucide-react';

/**
 * Accessible Modal Dialog component (design.md dialog shadow + 12px radius + clean hairline header).
 */
export default function Modal({
  isOpen,
  onClose,
  title,
  subtitle,
  maxWidth = 'md',
  children,
  closeOnBackdrop = true,
  className = ''
}) {
  const dialogRef = useRef(null);

  useEffect(() => {
    if (!isOpen) return;
    const handleKeyDown = (e) => {
      if (e.key === 'Escape') {
        e.stopPropagation();
        onClose();
      }
    };
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose]);

  useEffect(() => {
    if (!isOpen) return;
    const originalStyle = window.getComputedStyle(document.body).overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = originalStyle;
    };
  }, [isOpen]);

  if (!isOpen) return null;

  const maxWidthClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl'
  };

  const handleBackdropClick = (e) => {
    if (closeOnBackdrop && e.target === e.currentTarget) {
      onClose();
    }
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/55 p-4 backdrop-blur-xs transition-opacity duration-200"
      onClick={handleBackdropClick}
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-headline-title"
    >
      <div
        ref={dialogRef}
        className={`w-full overflow-hidden rounded-xl border border-outline bg-white text-on-surface shadow-[0_20px_25px_rgba(0,0,0,0.12),0_8px_10px_rgba(0,0,0,0.06)] transition-all dark:border-slate-700/80 dark:bg-[#171e29] dark:text-slate-100 ${
          maxWidthClasses[maxWidth] || maxWidthClasses.md
        } ${className}`}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex items-start justify-between border-b border-outline/60 px-6 py-4 dark:border-slate-700/70">
          <div className="min-w-0 pr-4">
            <h2
              id="modal-headline-title"
              className="text-lg font-bold tracking-tight text-on-surface dark:text-slate-100"
            >
              {title}
            </h2>
            {subtitle && (
              <p className="mt-1 text-xs text-muted sm:text-sm dark:text-slate-400">
                {subtitle}
              </p>
            )}
          </div>
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            className="grid size-9 shrink-0 cursor-pointer place-items-center rounded-lg border border-outline/80 text-muted transition hover:bg-surface-low hover:text-on-surface dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
            aria-label="Tutup dialog"
          >
            <X className="h-4 w-4" aria-hidden="true" />
          </button>
        </div>

        <div className="max-h-[calc(90vh-140px)] overflow-y-auto p-6">
          {children}
        </div>
      </div>
    </div>
  );
}
