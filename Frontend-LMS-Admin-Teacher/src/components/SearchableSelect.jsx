import { useMemo, useState } from 'react';
import { createPortal } from 'react-dom';
import { Check, ChevronDown, Search } from 'lucide-react';
import Modal from './Modal';
import useDebouncedValue from '../hooks/useDebouncedValue';

/**
 * Modal-based select for long data lists. The form value remains a native
 * hidden input, while the visible field stays compact and responsive.
 */
export default function SearchableSelect({
  options = [],
  value = '',
  onChange,
  name,
  id,
  placeholder = 'Ketik untuk mencari...',
  className = '',
  required = false,
  disabled = false,
  title = 'Pilih data',
  clearable = !required,
  'aria-label': ariaLabel,
  ...inputProps
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [query, setQuery] = useState('');
  const debouncedQuery = useDebouncedValue(query, 150);
  const [internalValue, setInternalValue] = useState(value ?? '');
  const currentValue = onChange ? value : internalValue;
  const selected = options.find((option) => String(option.value) === String(currentValue));
  const filteredOptions = useMemo(() => {
    const normalized = debouncedQuery.trim().toLowerCase();
    if (!normalized) return options;
    return options.filter((option) => `${option.label} ${option.value}`.toLowerCase().includes(normalized));
  }, [options, debouncedQuery]);

  function choose(nextValue) {
    if (!onChange) setInternalValue(nextValue);
    onChange?.({ target: { name, value: nextValue } });
    setIsOpen(false);
    setQuery('');
  }

  return (
    <>
      <input type="hidden" name={name} value={currentValue ?? ''} required={required} />
      <button
        {...inputProps}
        id={id}
        type="button"
        disabled={disabled}
        aria-label={ariaLabel}
        aria-haspopup="dialog"
        aria-expanded={isOpen}
        onClick={(event) => {
          event.preventDefault();
          event.stopPropagation();
          setIsOpen(true);
        }}
        className={`${className} flex items-center justify-between gap-2 text-left`}
      >
        <span className={`min-w-0 truncate ${selected ? 'text-on-surface dark:text-slate-100' : 'text-muted'}`}>
          {selected?.label || placeholder}
        </span>
        <ChevronDown className="size-4 shrink-0 text-muted" aria-hidden="true" />
      </button>

      {typeof document !== 'undefined' && createPortal(
        <Modal
        isOpen={isOpen}
        onClose={() => {
          setIsOpen(false);
          setQuery('');
        }}
        title={title}
        subtitle={`${options.length} pilihan tersedia`}
        maxWidth="lg"
        >
        <div className="space-y-3">
          <label className="relative block">
            <span className="sr-only">Cari {title.toLowerCase()}</span>
            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
            <input
              type="search"
              autoFocus
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              placeholder={`Cari ${title.toLowerCase()}...`}
              className="h-10 w-full rounded-lg border border-outline bg-surface-lowest pl-9 pr-3 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
          </label>

          {clearable && currentValue && (
            <button
              type="button"
              onClick={() => choose('')}
              className="w-full rounded-lg border border-dashed border-outline px-3 py-2 text-left text-xs font-semibold text-muted transition hover:border-primary hover:text-primary dark:border-slate-700"
            >
              Kosongkan pilihan
            </button>
          )}

          <div className="max-h-[min(50vh,22rem)] overflow-y-auto rounded-lg border border-outline/60 dark:border-slate-700">
            {filteredOptions.length ? filteredOptions.map((option) => {
              const isSelected = String(option.value) === String(currentValue);
              return (
                <button
                  type="button"
                  key={String(option.value)}
                  onClick={() => choose(option.value)}
                  className="flex w-full items-center justify-between gap-3 border-b border-outline/40 px-3 py-2.5 text-left text-sm transition last:border-b-0 hover:bg-primary/5 dark:border-slate-800 dark:hover:bg-slate-800"
                >
                  <span className="min-w-0 truncate text-on-surface dark:text-slate-100">{option.label}</span>
                  {isSelected && <Check className="size-4 shrink-0 text-primary" />}
                </button>
              );
            }) : (
              <p className="px-4 py-8 text-center text-sm text-muted">Data tidak ditemukan.</p>
            )}
          </div>
        </div>
        </Modal>,
        document.body
      )}
    </>
  );
}
