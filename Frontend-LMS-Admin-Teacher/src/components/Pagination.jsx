import { useEffect, useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

const DEFAULT_PAGE_SIZES = [10, 25, 50, 100];

/**
 * Custom hook untuk mengelola kalkulasi pagination data array di sisi browser.
 */
export function usePagination(items, initialPageSize = 10) {
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(initialPageSize);
  const total = items.length;
  const pageCount = Math.max(1, Math.ceil(total / pageSize));

  useEffect(() => {
    setPage(1);
  }, [items]);

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
 * Pagination controls following design.md compact surface geometry.
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

  const pages = Array.from({ length: Math.min(pageCount, 5) }, (_, index) => {
    const first = Math.min(Math.max(page - 2, 1), Math.max(pageCount - 4, 1));
    return first + index;
  });

  return (
    <nav
      aria-label="Navigasi halaman data"
      className="flex flex-col gap-3 border-t border-outline px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between"
    >
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
            className="h-8 cursor-pointer rounded-md border border-outline bg-white px-2 text-xs font-semibold text-on-surface outline-none transition focus:border-primary"
          >
            {pageSizes.map(size => (
              <option key={size} value={size}>
                {size}
              </option>
            ))}
          </select>
        </label>
      </div>

      <div className="flex items-center gap-1" role="group" aria-label="Pilih nomor halaman">
        <button
          type="button"
          data-dls-action-ignore="true"
          aria-label="Halaman sebelumnya"
          disabled={page <= 1}
          onClick={() => onPageChange?.(page - 1)}
          className="grid size-9 cursor-pointer place-items-center rounded-lg border border-slate-300/80 bg-slate-100 text-slate-800 transition hover:bg-slate-200/90 disabled:cursor-not-allowed disabled:opacity-40 shadow-2xs dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
          title="Halaman sebelumnya"
        >
          <ChevronLeft className="h-4 w-4" aria-hidden="true" />
        </button>

        {pages.map(value => {
          const isActive = value === page;
          return (
            <button
              key={value}
              type="button"
              data-dls-action-ignore="true"
              aria-current={isActive ? 'page' : undefined}
              aria-label={`Halaman ${value}`}
              onClick={() => onPageChange?.(value)}
              className={`h-9 min-w-9 cursor-pointer rounded-lg border px-2.5 text-xs font-semibold transition ${
                isActive
                  ? 'border-primary bg-primary text-white shadow-xs'
                  : 'border-slate-300/80 bg-slate-100 text-slate-800 hover:bg-slate-200/90 shadow-2xs dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700'
              }`}
            >
              {value}
            </button>
          );
        })}

        <button
          type="button"
          data-dls-action-ignore="true"
          aria-label="Halaman berikutnya"
          disabled={page >= pageCount}
          onClick={() => onPageChange?.(page + 1)}
          className="grid size-9 cursor-pointer place-items-center rounded-lg border border-slate-300/80 bg-slate-100 text-slate-800 transition hover:bg-slate-200/90 disabled:cursor-not-allowed disabled:opacity-40 shadow-2xs dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
          title="Halaman berikutnya"
        >
          <ChevronRight className="h-4 w-4" aria-hidden="true" />
        </button>
      </div>
    </nav>
  );
}
