/**
 * Switch sederhana untuk pilihan aktif/nonaktif.
 * Menjaga ukuran, warna, dan akses keyboard tetap konsisten di seluruh portal.
 */
export default function ToggleSwitch({
  checked = false,
  disabled = false,
  onChange,
  ariaLabel,
  compact = false,
  title,
  ...buttonProps
}) {
  const statusLabel = checked ? 'Aktif' : 'Nonaktif';

  return (
    <span className="inline-flex items-center gap-2">
      {!compact && (
        <span
          className={`text-[11px] font-semibold ${
            checked ? 'text-emerald-700 dark:text-emerald-300' : 'text-muted'
          }`}
        >
          {statusLabel}
        </span>
      )}
      <button
        {...buttonProps}
        type="button"
        role="switch"
        data-dls-action-ignore="true"
        aria-checked={checked}
        aria-label={ariaLabel}
        title={title || `${statusLabel}. Klik untuk ${checked ? 'menonaktifkan' : 'mengaktifkan'}.`}
        disabled={disabled}
        onClick={() => !disabled && onChange?.(!checked)}
        className={`dls-toggle-switch relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent p-0 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-55 ${
          checked
            ? '!bg-emerald-600 hover:!bg-emerald-700 dark:!bg-emerald-500 dark:hover:!bg-emerald-400'
            : '!bg-slate-300 hover:!bg-slate-400 dark:!bg-slate-600 dark:hover:!bg-slate-500'
        }`}
      >
        <span
          aria-hidden="true"
          className={`pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-sm transition-transform ${
            checked ? 'translate-x-5' : 'translate-x-0'
          }`}
        />
      </button>
    </span>
  );
}
