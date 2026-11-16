import { useEffect, useState } from 'react';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';

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
 * Menghasilkan susunan nomor halaman yang rapi dan mudah dibaca (dengan elipsis).
 */
function getPageNumbers(current, total) {
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }
  if (current <= 4) {
    return [1, 2, 3, 4, 5, '...', total];
  }
  if (current >= total - 3) {
    return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
  }
  return [1, '...', current - 1, current, current + 1, '...', total];
}

/**
 * Pagination controls yang rapi, modern, dan family-friendly.
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
  const [jumpPage, setJumpPage] = useState(String(page));

  useEffect(() => {
    setJumpPage(String(page));
  }, [page]);

  if (total <= 0) return null;

  const start = (page - 1) * pageSize + 1;
  const end = Math.min(page * pageSize, total);
  const pages = getPageNumbers(page, pageCount);

  const handleJumpSubmit = (e) => {
    e.preventDefault();
    const parsed = parseInt(jumpPage, 10);
    if (!isNaN(parsed)) {
      const target = Math.max(1, Math.min(pageCount, parsed));
      setJumpPage(String(target));
      if (target !== page) {
        onPageChange?.(target);
      }
    } else {
      setJumpPage(String(page));
    }
  };

  return (
    <nav
      aria-label="Navigasi halaman data"
      className="flex flex-col gap-3 border-t border-outline/60 bg-surface-lowest/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900/50"
    >
      {/* Sisi Kiri: Informasi Data & Baris Per Halaman */}
      <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted">
        <span aria-live="polite" className="select-none">
          Menampilkan <strong className="font-semibold text-on-surface">{start}–{end}</strong> dari{' '}
          <strong className="font-semibold text-on-surface">{total}</strong> data
        </span>

        <label className="flex items-center gap-1.5 select-none">
          <span>Tampilkan:</span>
          <select
            aria-label="Pilih jumlah data per halaman"
            value={pageSize}
            onChange={event => onPageSizeChange?.(Number(event.target.value))}
            className="h-7 cursor-pointer rounded-lg border border-outline bg-white px-2 text-xs font-semibold text-on-surface outline-none transition focus:border-primary dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200"
          >
            {pageSizes.map(size => (
              <option key={size} value={size}>
                {size} / hal
              </option>
            ))}
          </select>
        </label>
      </div>

      {/* Sisi Kanan: Lompat Halaman & Tombol Navigasi Angka */}
      <div className="flex flex-wrap items-center gap-2.5">
        {/* Form Cari/Lompat Nomor Halaman yang Terapit Rapi */}
        {pageCount > 1 && (
          <form onSubmit={handleJumpSubmit} className="flex items-center gap-1.5 text-xs text-muted">
            <span className="whitespace-nowrap font-medium text-on-surface-variant select-none">
              Ke hal:
            </span>
            <div className="flex items-center rounded-lg border border-outline bg-white p-0.5 transition focus-within:border-primary focus-within:ring-1 focus-within:ring-primary/20 dark:border-slate-700 dark:bg-slate-800">
              <input
                id="jump-page-input"
                type="number"
                min={1}
                max={pageCount}
                value={jumpPage}
                onChange={(e) => setJumpPage(e.target.value)}
                onBlur={() => {
                  if (!jumpPage || isNaN(parseInt(jumpPage, 10))) {
                    setJumpPage(String(page));
                  }
                }}
                placeholder={String(page)}
                title={`Ketik nomor halaman (1 - ${pageCount}) lalu tekan Enter`}
                aria-label="Cari nomor halaman"
                className="h-6 w-11 bg-transparent px-1 text-center text-xs font-semibold text-on-surface outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
              />
              <span className="pr-1 text-[11px] font-medium text-muted select-none">/ {pageCount}</span>
              <button
                type="submit"
                className="h-6 cursor-pointer rounded-md bg-surface-low px-2 text-[11px] font-semibold text-on-surface-variant transition hover:bg-primary hover:text-white active:scale-95 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-primary dark:hover:text-white"
                title="Buka halaman yang dicari"
              >
                Tuju
              </button>
            </div>
          </form>
        )}

        {/* Tombol Nomor & Panah Navigasi */}
        <div className="flex items-center gap-1" role="group" aria-label="Pilih nomor halaman">
          {/* Halaman Pertama (<<) */}
          <button
            type="button"
            data-dls-action-ignore="true"
            aria-label="Halaman pertama"
            disabled={page <= 1}
            onClick={() => onPageChange?.(1)}
            className="grid size-8 cursor-pointer place-items-center rounded-lg border border-outline/70 bg-white text-on-surface-variant transition hover:border-primary/50 hover:bg-surface-low hover:text-on-surface disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            title="Halaman pertama (awal)"
          >
            <ChevronsLeft className="size-3.5" aria-hidden="true" />
          </button>

          {/* Halaman Sebelumnya (<) */}
          <button
            type="button"
            data-dls-action-ignore="true"
            aria-label="Halaman sebelumnya"
            disabled={page <= 1}
            onClick={() => onPageChange?.(page - 1)}
            className="grid size-8 cursor-pointer place-items-center rounded-lg border border-outline/70 bg-white text-on-surface-variant transition hover:border-primary/50 hover:bg-surface-low hover:text-on-surface disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            title="Halaman sebelumnya"
          >
            <ChevronLeft className="size-3.5" aria-hidden="true" />
          </button>

          {/* Deretan Nomor Halaman */}
          {pages.map((value, idx) => {
            if (value === '...') {
              return (
                <span
                  key={`ellipsis-${idx}`}
                  className="grid size-8 select-none place-items-center text-xs font-semibold text-muted"
                  aria-hidden="true"
                >
                  …
                </span>
              );
            }

            const isActive = value === page;
            return (
              <button
                key={value}
                type="button"
                data-dls-action-ignore="true"
                aria-current={isActive ? 'page' : undefined}
                aria-label={`Halaman ${value}`}
                onClick={() => onPageChange?.(value)}
                className={`h-8 min-w-8 cursor-pointer rounded-lg border px-2 text-xs font-semibold transition ${
                  isActive
                    ? 'border-primary bg-primary text-white shadow-xs font-bold'
                    : 'border-outline/70 bg-white text-on-surface-variant hover:border-primary/50 hover:bg-surface-low hover:text-on-surface dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
                }`}
              >
                {value}
              </button>
            );
          })}

          {/* Halaman Berikutnya (>) */}
          <button
            type="button"
            data-dls-action-ignore="true"
            aria-label="Halaman berikutnya"
            disabled={page >= pageCount}
            onClick={() => onPageChange?.(page + 1)}
            className="grid size-8 cursor-pointer place-items-center rounded-lg border border-outline/70 bg-white text-on-surface-variant transition hover:border-primary/50 hover:bg-surface-low hover:text-on-surface disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            title="Halaman berikutnya"
          >
            <ChevronRight className="size-3.5" aria-hidden="true" />
          </button>

          {/* Halaman Terakhir (>>) */}
          <button
            type="button"
            data-dls-action-ignore="true"
            aria-label="Halaman terakhir"
            disabled={page >= pageCount}
            onClick={() => onPageChange?.(pageCount)}
            className="grid size-8 cursor-pointer place-items-center rounded-lg border border-outline/70 bg-white text-on-surface-variant transition hover:border-primary/50 hover:bg-surface-low hover:text-on-surface disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            title="Halaman terakhir (akhir)"
          >
            <ChevronsRight className="size-3.5" aria-hidden="true" />
          </button>
        </div>
      </div>
    </nav>
  );
}
