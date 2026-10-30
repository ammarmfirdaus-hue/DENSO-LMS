import React, { useEffect, useRef } from 'react';

/**
 * Accessible Modal Dialog component.
 *
 * @param {Object} props
 * @param {boolean} props.isOpen - Controls visibility.
 * @param {() => void} props.onClose - Dismiss callback.
 * @param {string} props.title - Dialog title heading.
 * @param {string} [props.subtitle] - Contextual subtitle.
 * @param {'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl'} [props.maxWidth='md'] - Max width breakpoint.
 * @param {React.ReactNode} props.children - Dialog content.
 * @param {boolean} [props.closeOnBackdrop=true]
 * @param {string} [props.className]
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

  // Close on Escape key press
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

  // Lock body scroll when modal is open
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
      className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-xs transition-opacity duration-200"
      onClick={handleBackdropClick}
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-headline-title"
    >
      <div
        ref={dialogRef}
        className={`w-full overflow-hidden rounded-2xl border border-outline/50 bg-white shadow-2xl transition-all dark:border-slate-800 dark:bg-[#1e293b] ${maxWidthClasses[maxWidth] || maxWidthClasses.md} ${className}`}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex items-start justify-between border-b border-outline/40 p-5 sm:p-6 dark:border-slate-800">
          <div className="min-w-0 pr-4">
            <h2
              id="modal-headline-title"
              className="text-lg font-bold tracking-tight text-on-surface sm:text-xl"
            >
              {title}
            </h2>
            {subtitle && (
              <p className="mt-1 text-xs text-muted sm:text-sm">
                {subtitle}
              </p>
            )}
          </div>
          <button
            type="button"
            onClick={onClose}
            className="grid size-9 shrink-0 place-items-center rounded-xl border border-outline/50 text-muted transition hover:bg-surface-low hover:text-on-surface dark:border-slate-700 dark:hover:bg-slate-800"
            aria-label="Tutup dialog"
          >
            <span className="material-symbol text-lg" aria-hidden="true">close</span>
          </button>
        </div>

        <div className="max-h-[calc(90vh-140px)] overflow-y-auto p-5 sm:p-6">
          {children}
        </div>
      </div>
    </div>
  );
}

