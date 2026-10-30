/**
 * Komponen reusable baris pencarian dan filter dropdown.
 * Digunakan seragam pada tabel dan katalog (Course, Quiz, Pengguna, Nilai, dll).
 *
 * Mendukung dark mode sistemik, tombol reset pencarian instan, dan accessibility ARIA.
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
      className="mb-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px]"
    >
      {/* Input Pencarian */}
      <div className="relative block">
        <label htmlFor="list-search-input" className="sr-only">
          {placeholder}
        </label>
        <span
          aria-hidden="true"
          className="material-symbol absolute left-3.5 top-1/2 -translate-y-1/2 text-lg text-muted pointer-events-none select-none"
        >
          search
        </span>
        <input
          id="list-search-input"
          type="search"
          value={search}
          onChange={event => onSearch?.(event.target.value)}
          placeholder={placeholder}
          className="h-11 w-full rounded-xl border border-outline/50 bg-white pl-10 pr-9 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 placeholder:text-muted"
        />
        {Boolean(search) && (
          <button
            type="button"
            onClick={() => onSearch?.('')}
            aria-label="Hapus kata kunci pencarian"
            title="Bersihkan"
            className="absolute right-2.5 top-1/2 -translate-y-1/2 grid size-6 place-items-center rounded-md text-muted hover:bg-surface-low hover:text-secondary cursor-pointer dark:hover:bg-slate-800"
          >
            <span className="material-symbol text-sm" aria-hidden="true">close</span>
          </button>
        )}
      </div>

      {/* Dropdown Filter Kategori (opsional jika options diberikan) */}
      {options.length > 0 && (
        <div>
          <label htmlFor="list-filter-select" className="sr-only">
            {label}
          </label>
          <select
            id="list-filter-select"
            value={filter}
            onChange={event => onFilter?.(event.target.value)}
            className="h-11 w-full rounded-xl border border-outline/50 bg-white px-3 text-sm font-medium text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 cursor-pointer"
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
