import { useEffect, useState } from 'react';

const DEFAULT_PAGE_SIZES = [10, 25, 50, 100];

/**
 * Custom hook untuk mengelola kalkulasi pagination data array di sisi browser.
 *
 * @param {Array<any>} items Daftar array data yang akan dibagi per halaman.
 * @param {number} [initialPageSize=10] Jumlah item per halaman default.
 * @returns {object} Objek helper berisi state halaman aktif, fungsi pengubah, dan data pagedItems.
 */
export function usePagination(items, initialPageSize = 10) {
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(initialPageSize);
  const total = items.length;
  const pageCount = Math.max(1, Math.ceil(total / pageSize));

  // Reset ke halaman 1 saat data items berubah
  useEffect(() => {
    setPage(1);
  }, [items]);

  // Jaga agar halaman aktif tidak melebihi total halaman baru
  useEffect(() => {
    if (page > pageCount) setPage(pageCount);
  }, [page, pageCount]);

  return {
    page,
    setPage,
    pageSize,
    setPageSize: value => {
      setPageSize(Number(value));
      setPage(1);
    },
    total,
    pageCount,
    pagedItems: items.slice((page - 1) * pageSize, page * pageSize)
  };
}

/**
 * Komponen UI navigasi halaman dan pemilih limit baris data.
 * Memenuhi standar a11y (ARIA landmarks, touch targets >= 38px, keyboard focus).
 */
export default function Pagination({
  page,
  pageSize,
  total,
  pageCount,
  onPageChange,
  onPageSizeChange,
  pageSizes = DEFAULT_PAGE_SIZES
}) {
  if (total <= 0) return null;

  const start = (page - 1) * pageSize + 1;
  const end = Math.min(page * pageSize, total);

  // Tampilkan maksimal 5 tombol angka halaman di sekitar halaman aktif
  const pages = Array.from({ length: Math.min(pageCount, 5) }, (_, index) => {
    const first = Math.min(Math.max(page - 2, 1), Math.max(pageCount - 4, 1));
    return first + index;
  });

  return (
    <nav
      aria-label="Navigasi halaman data"
      className="flex flex-col gap-3.5 border-t border-outline/40 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
    >
      {/* Informasi Baris & Dropdown Page Size */}
      <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted">
        <span aria-live="polite">
          Menampilkan <strong className="font-semibold text-on-surface">{start}–{end}</strong> dari{' '}
          <strong className="font-semibold text-on-surface">{total}</strong> data
        </span>

        <label className="flex items-center gap-2">
          <span>Baris:</span>
          <select
            aria-label="Pilih jumlah baris per halaman"
            value={pageSize}
            onChange={event => onPageSizeChange?.(Number(event.target.value))}
            className="h-8.5 rounded-lg border border-outline/50 bg-white px-2 text-xs font-semibold text-on-surface outline-none transition focus:border-primary dark:border-slate-700 dark:bg-slate-900 cursor-pointer"
          >
            {pageSizes.map(size => (
              <option key={size} value={size}>
                {size}
              </option>
            ))}
          </select>
        </label>
      </div>

      {/* Kontrol Navigasi Angka Halaman */}
      <div className="flex items-center gap-1.5" role="group" aria-label="Pilih nomor halaman">
        {/* Tombol Sebelumnya */}
        <button
          type="button"
          aria-label="Halaman sebelumnya"
          disabled={page <= 1}
          onClick={() => onPageChange?.(page - 1)}
          className="grid size-9 place-items-center rounded-xl border border-outline/50 bg-white text-secondary transition hover:bg-surface-low disabled:cursor-not-allowed disabled:opacity-35 cursor-pointer dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          title="Halaman sebelumnya"
        >
          <span className="material-symbol text-base" aria-hidden="true">chevron_left</span>
        </button>

        {/* Tombol Angka Halaman */}
        {pages.map(value => {
          const isActive = value === page;
          return (
            <button
              key={value}
              type="button"
              aria-current={isActive ? 'page' : undefined}
              aria-label={`Halaman ${value}`}
              onClick={() => onPageChange?.(value)}
              className={`min-w-9 h-9 rounded-xl border px-2.5 text-xs font-bold transition cursor-pointer ${
                isActive
                  ? 'border-primary bg-primary text-white shadow-xs'
                  : 'border-outline/50 bg-white text-on-surface hover:bg-surface-low dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800'
              }`}
            >
              {value}
            </button>
          );
        })}

        {/* Tombol Berikutnya */}
        <button
          type="button"
          aria-label="Halaman berikutnya"
          disabled={page >= pageCount}
          onClick={() => onPageChange?.(page + 1)}
          className="grid size-9 place-items-center rounded-xl border border-outline/50 bg-white text-secondary transition hover:bg-surface-low disabled:cursor-not-allowed disabled:opacity-35 cursor-pointer dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          title="Halaman berikutnya"
        >
          <span className="material-symbol text-base" aria-hidden="true">chevron_right</span>
        </button>
      </div>
    </nav>
  );
}
