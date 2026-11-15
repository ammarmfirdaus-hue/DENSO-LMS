import React, { useEffect, useRef, useState } from 'react';
import { Loader2 } from 'lucide-react';

function extractButtonText(node) {
  if (node == null || typeof node === 'boolean') return '';
  if (typeof node === 'string' || typeof node === 'number') return String(node);
  if (Array.isArray(node)) return node.map(extractButtonText).join(' ');
  if (React.isValidElement(node)) return extractButtonText(node.props?.children);
  return '';
}

function resolveSemanticVariant(variant, labelText) {
  const explicitSemantic = new Set(['info', 'indigo', 'warning', 'danger', 'success', 'teal', 'purple', 'neutral']);
  if (explicitSemantic.has(variant)) return variant;

  const label = String(labelText || '').replace(/\s+/g, ' ').trim();
  if (!label) return variant;

  // Keep cancel/close/back/filter controls neutral slate
  if (/^(batal|tutup|kembali|cancel|close|reset filter|muat ulang|refresh|cetak)\b/i.test(label)) {
    return variant === 'primary' ? 'secondary' : variant;
  }

  // Generic/outline/secondary buttons adopt consistent semantic colors across all pages
  if (variant === 'outline' || variant === 'secondary' || variant === 'ghost') {
    if (/\b(edit|ubah|perbarui)\b/i.test(label)) return 'info';
    if (/\b(detail|lihat|pratinjau|preview|email)\b/i.test(label)) return 'indigo';
    if (/\b(simpan|setujui|terima|aktifkan|publish|publikasikan|selesai)\b/i.test(label)) return 'success';
    if (/\b(export|ekspor|download|unduh|alihkan|transfer)\b/i.test(label)) return 'teal';
    if (/\b(kirim|ajukan|reset|review)\b/i.test(label)) return 'warning';
    if (/\b(hapus|tolak|nonaktif|delete|remove)\b/i.test(label)) return 'danger';
  }

  // Even if caller left default variant="primary", keep Edit/Simpan/Export/Detail consistent
  if (variant === 'primary') {
    if (/^(edit|ubah)\b/i.test(label)) return 'info';
    if (/^(simpan|setujui|terima|aktifkan|publish|publikasikan)\b/i.test(label)) return 'success';
    if (/^(export|ekspor|download|unduh)\b/i.test(label)) return 'teal';
    if (/^(lihat|detail)\b/i.test(label)) return 'indigo';
  }

  return variant;
}

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
  onClick,
  className = '',
  children,
  ...props
}) {
  const effectiveVariant = resolveSemanticVariant(
    variant,
    `${extractButtonText(children)} ${props['aria-label'] || ''} ${props.title || ''}`
  );
  const [pending, setPending] = useState(false);
  const lockRef = useRef(false);
  const timeoutRef = useRef(null);
  const effectiveLoading = loading || pending;

  useEffect(() => () => {
    if (timeoutRef.current) window.clearTimeout(timeoutRef.current);
  }, []);

  // Semua Button memiliki guard ringan. Aksi async dikunci sampai selesai;
  // aksi sinkron diberi jeda singkat agar double-click tidak mengulang aksi.
  function handleClick(event) {
    if (lockRef.current || disabled || loading) {
      event.preventDefault();
      return;
    }
    lockRef.current = true;
    let result;
    try {
      result = onClick?.(event);
    } catch (error) {
      timeoutRef.current = window.setTimeout(() => {
        timeoutRef.current = null;
        lockRef.current = false;
      }, 300);
      throw error;
    }
    const isAsync = result && typeof result.then === 'function';
    const release = () => {
      if (timeoutRef.current) window.clearTimeout(timeoutRef.current);
      timeoutRef.current = null;
      setPending(false);
      lockRef.current = false;
    };
    if (isAsync) {
      setPending(true);
      // Network/API code has its own timeout; this is a final UI failsafe.
      timeoutRef.current = window.setTimeout(release, 30000);
      Promise.resolve(result).then(release, release);
    } else {
      timeoutRef.current = window.setTimeout(release, 300);
    }
  }

  const variants = {
    primary: 'bg-[#DC2626] text-white hover:bg-[#B91C1C] shadow-xs border border-[#B91C1C]',
    secondary: 'border border-slate-300 bg-slate-200 text-slate-800 hover:bg-slate-300 hover:text-slate-900 shadow-2xs dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 dark:hover:bg-slate-600',
    outline: 'border border-slate-300 bg-slate-200 text-slate-800 font-semibold hover:bg-slate-300 shadow-2xs dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 dark:hover:bg-slate-600',
    ghost: 'border border-slate-300 bg-slate-200 text-slate-800 hover:bg-slate-300 hover:text-slate-900 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700',
    info: 'bg-blue-600 text-white hover:bg-blue-700 shadow-xs border border-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500',
    indigo: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-xs border border-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500',
    warning: 'bg-amber-600 text-white hover:bg-amber-700 shadow-xs border border-amber-700 dark:bg-amber-600 dark:hover:bg-amber-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 shadow-xs border border-red-700 dark:bg-red-600 dark:hover:bg-red-500',
    success: 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs border border-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500',
    teal: 'bg-teal-600 text-white hover:bg-teal-700 shadow-xs border border-teal-700 dark:bg-teal-600 dark:hover:bg-teal-500',
    purple: 'bg-purple-600 text-white hover:bg-purple-700 shadow-xs border border-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500',
    neutral: 'bg-slate-600 text-white hover:bg-slate-700 shadow-xs border border-slate-700 dark:bg-slate-600 dark:hover:bg-slate-500'
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
      disabled={disabled || effectiveLoading}
      aria-busy={effectiveLoading || undefined}
      className={`dls-btn inline-flex min-w-0 shrink-0 cursor-pointer items-center justify-center whitespace-nowrap font-medium leading-none transition-colors active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50 ${
        variants[effectiveVariant] || variants.primary
      } ${sizes[size] || sizes.md} ${className}`}
      {...props}
      onClick={handleClick}
    >
      {effectiveLoading ? (
        <Loader2 className="h-4 w-4 animate-spin" />
      ) : icon ? (
        typeof icon === 'string' ? (
          <span className="material-symbol text-[18px] leading-none">{icon}</span>
        ) : (
          <span className="inline-flex shrink-0 items-center">{icon}</span>
        )
      ) : null}

      {children && <span>{children}</span>}

      {iconRight && !effectiveLoading && (
        typeof iconRight === 'string' ? (
          <span className="material-symbol text-[18px] leading-none">{iconRight}</span>
        ) : (
          <span className="inline-flex shrink-0 items-center">{iconRight}</span>
        )
      )}
    </button>
  );
}
