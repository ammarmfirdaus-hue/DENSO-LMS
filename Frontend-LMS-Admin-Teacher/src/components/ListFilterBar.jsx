import { Search, X } from 'lucide-react';

/**
 * Reusable search & dropdown filter bar following design.md compact surface input geometry (6px radius, 40px height).
 */
export default function ListFilterBar({
  search = '',
  onSearch,
  placeholder = 'Cari data...',
  filter,
  onFilter,
  options = [],
  label = 'Semua kategori'
}) {
  return (
    <div
      role="search"
      aria-label="Pencarian dan filter daftar data"
      className={`mb-4 grid gap-3 ${options.length > 0 ? 'sm:grid-cols-[minmax(0,1fr)_220px]' : 'grid-cols-1'}`}
    >
      <div className="relative block">
        <label htmlFor="list-search-input" className="sr-only">
          {placeholder}
        </label>
        <Search
          aria-hidden="true"
          className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted"
        />
        <input
          id="list-search-input"
          type="search"
          value={search}
          onChange={event => onSearch?.(event.target.value)}
          placeholder={placeholder}
          className="h-10 w-full rounded-md border border-outline bg-white pl-9 pr-9 text-sm text-on-surface outline-none transition placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary/15"
        />
        {Boolean(search) && (
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={() => onSearch?.('')}
            aria-label="Hapus kata kunci pencarian"
            title="Bersihkan"
            className="absolute right-2 top-1/2 grid size-6 -translate-y-1/2 cursor-pointer place-items-center rounded text-muted transition hover:bg-surface-low hover:text-on-surface"
          >
            <X className="h-3.5 w-3.5" aria-hidden="true" />
          </button>
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
            className="h-10 w-full cursor-pointer rounded-md border border-outline bg-white px-3 text-sm font-medium text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
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
