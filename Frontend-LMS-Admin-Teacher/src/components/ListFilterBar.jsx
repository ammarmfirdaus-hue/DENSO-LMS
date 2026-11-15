import { useEffect, useMemo, useRef, useState } from 'react';
import { Search, X } from 'lucide-react';

/**
 * Reusable search & dropdown filter bar with interactive suggestion dropdown.
 */
export default function ListFilterBar({
  search = '',
  onSearch,
  placeholder = 'Cari data...',
  filter,
  onFilter,
  options = [],
  label = 'Semua kategori',
  suggestions
}) {
  const wrapRef = useRef(null);
  const [open, setOpen] = useState(false);
  const [autoItems, setAutoItems] = useState([]);
  const [activeIdx, setActiveIdx] = useState(-1);

  const collectAutoItems = () => {
    if (Array.isArray(suggestions)) return;
    const scope = wrapRef.current?.closest('section, main') || document.querySelector('main');
    if (!scope) return;
    const seen = new Set();
    const collected = [];
    const nodes = scope.querySelectorAll('table tbody tr td:first-child, table tbody tr td:nth-child(2), h2, h3');
    for (const el of nodes) {
      const txt = (el.textContent || '').replace(/\s+/g, ' ').trim();
      if (!txt || txt.length < 2 || txt.length > 90) continue;
      const key = txt.toLowerCase();
      if (seen.has(key)) continue;
      seen.add(key);
      collected.push(txt);
      if (collected.length >= 40) break;
    }
    setAutoItems(collected);
  };

  const filteredSuggestions = useMemo(() => {
    const q = String(search || '').trim().toLowerCase();
    if (!q) return [];
    const pool = Array.isArray(suggestions) ? suggestions : autoItems;
    return pool.filter(item => String(item).toLowerCase().includes(q)).slice(0, 6);
  }, [suggestions, autoItems, search]);

  useEffect(() => {
    const handleOutside = event => {
      if (wrapRef.current && !wrapRef.current.contains(event.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handleOutside);
    return () => document.removeEventListener('mousedown', handleOutside);
  }, []);

  const handleKeyDown = event => {
    if (!open || !filteredSuggestions.length) return;
    if (event.key === 'Escape') {
      setOpen(false);
      return;
    }
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      setActiveIdx(i => (i + 1) % filteredSuggestions.length);
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      setActiveIdx(i => (i - 1 + filteredSuggestions.length) % filteredSuggestions.length);
    } else if (event.key === 'Enter' && activeIdx >= 0 && filteredSuggestions[activeIdx]) {
      event.preventDefault();
      onSearch?.(filteredSuggestions[activeIdx]);
      setOpen(false);
    }
  };

  return (
    <div
      role="search"
      aria-label="Pencarian dan filter daftar data"
      className={`mb-4 grid gap-3 ${options.length > 0 ? 'sm:grid-cols-[minmax(0,1fr)_220px]' : 'grid-cols-1'}`}
    >
      <div className="relative block" ref={wrapRef}>
        <label htmlFor="list-search-input" className="sr-only">
          {placeholder}
        </label>
        <Search
          aria-hidden="true"
          className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-red-600"
        />
        <input
          id="list-search-input"
          type="search"
          data-has-suggestions="true"
          autoComplete="off"
          value={search}
          onFocus={() => {
            collectAutoItems();
            if (String(search || '').trim()) {
              setOpen(true);
              setActiveIdx(-1);
            }
          }}
          onChange={event => {
            onSearch?.(event.target.value);
            setOpen(Boolean(event.target.value.trim()));
            setActiveIdx(-1);
          }}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          className="h-10 w-full rounded-xl border border-red-200 bg-white pl-9 pr-9 text-sm text-on-surface outline-none transition placeholder:text-muted focus:border-red-600 focus:ring-2 focus:ring-red-500/20 dark:border-slate-700 dark:bg-slate-900"
        />
        {Boolean(search) && (
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={() => {
              onSearch?.('');
              setOpen(false);
            }}
            aria-label="Hapus kata kunci pencarian"
            title="Bersihkan"
            className="absolute right-2 top-1/2 grid size-6 -translate-y-1/2 cursor-pointer place-items-center rounded-lg text-red-600 transition hover:bg-red-50"
          >
            <X className="h-3.5 w-3.5" aria-hidden="true" />
          </button>
        )}

        {open && filteredSuggestions.length > 0 && (
          <div
            role="listbox"
            className="absolute left-0 right-0 top-[calc(100%+6px)] z-40 max-h-60 overflow-y-auto rounded-xl border border-red-200 bg-white p-1.5 shadow-xl dark:border-slate-700 dark:bg-slate-900"
          >
            {filteredSuggestions.map((item, idx) => (
              <button
                key={String(item)}
                type="button"
                role="option"
                aria-selected={idx === activeIdx}
                onMouseDown={event => {
                  event.preventDefault();
                  onSearch?.(String(item));
                  setOpen(false);
                }}
                className={`flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs font-medium transition ${
                  idx === activeIdx
                    ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300'
                    : 'text-slate-700 hover:bg-red-50 hover:text-red-700 dark:text-slate-200 dark:hover:bg-red-950/40'
                }`}
              >
                <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-red-500" />
                <span className="truncate">{item}</span>
              </button>
            ))}
          </div>
        )}
      </div>

      {options.length > 0 && (
        <div>
          <label htmlFor="list-filter-select" className="sr-only">
            {label}
          </label>
          <select
            id="list-filter-select"
            value={filter}
            onChange={event => onFilter?.(event.target.value)}
            className="h-10 w-full cursor-pointer rounded-xl border border-outline bg-white px-3 text-sm font-medium text-on-surface outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-500/15 dark:border-slate-700 dark:bg-slate-900"
          >
            {options.map(option => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </div>
      )}
    </div>
  );
}
