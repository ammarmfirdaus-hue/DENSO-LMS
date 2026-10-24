import { useEffect, useState } from 'react';

const DEFAULT_PAGE_SIZES = [10, 25, 50, 100];

/**
 * Custom hook untuk mengelola state dan kalkulasi pagination data array di sisi client.
 *
 * @param {Array<any>} items Daftar array data yang akan dibagi per halaman.
 * @param {number} [initialPageSize=10] Jumlah item per halaman default.
 * @returns {object} Objek berisi state halaman aktif, fungsi pengubah, dan data pagedItems.
 */
export function usePagination(items, initialPageSize = 10) {
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(initialPageSize);
  const total = items.length;
  const pageCount = Math.max(1, Math.ceil(total / pageSize));

  useEffect(() => { setPage(1); }, [items]);
  useEffect(() => {
    if (page > pageCount) setPage(pageCount);
  }, [page, pageCount]);

  return {
    page,
    setPage,
    pageSize,
    setPageSize: value => { setPageSize(Number(value)); setPage(1); },
    total,
    pageCount,
    pagedItems: items.slice((page - 1) * pageSize, page * pageSize)
  };
}

/**
 * Komponen UI navigasi halaman dan pemilih jumlah baris data.
 */
export default function Pagination({ page, pageSize, total, pageCount, onPageChange, onPageSizeChange, pageSizes = DEFAULT_PAGE_SIZES }) {
  if (total <= 0) return null;
  const start = (page - 1) * pageSize + 1;
  const end = Math.min(page * pageSize, total);
  const pages = Array.from({ length: Math.min(pageCount, 5) }, (_, index) => {
    const first = Math.min(Math.max(page - 2, 1), Math.max(pageCount - 4, 1));
    return first + index;
  });

  return <nav aria-label="Navigasi halaman daftar" className="flex flex-col gap-3 border-t border-outline/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
    <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted">
      <span aria-live="polite">Menampilkan <strong className="text-on-surface">{start}–{end}</strong> dari <strong className="text-on-surface">{total}</strong></span>
      <label className="flex items-center gap-2">Baris per halaman<select aria-label="Baris per halaman" value={pageSize} onChange={event => onPageSizeChange(Number(event.target.value))} className="h-9 rounded-lg border border-outline/50 bg-white px-2 text-sm text-on-surface">{pageSizes.map(size => <option key={size} value={size}>{size}</option>)}</select></label>
    </div>
    <div className="flex items-center gap-1" role="group" aria-label="Pilih halaman">
      <button type="button" aria-label="Halaman sebelumnya" disabled={page <= 1} onClick={() => onPageChange(page - 1)} className="grid size-9 place-items-center rounded-lg border border-outline/50 text-sm disabled:cursor-not-allowed disabled:opacity-40">‹</button>
      {pages.map(value => <button key={value} type="button" aria-current={value === page ? 'page' : undefined} onClick={() => onPageChange(value)} className={`min-w-9 rounded-lg border px-2 py-2 text-xs font-semibold ${value === page ? 'border-primary bg-primary text-white' : 'border-outline/50 bg-white text-on-surface hover:bg-surface-low'}`}>{value}</button>)}
      <button type="button" aria-label="Halaman berikutnya" disabled={page >= pageCount} onClick={() => onPageChange(page + 1)} className="grid size-9 place-items-center rounded-lg border border-outline/50 text-sm disabled:cursor-not-allowed disabled:opacity-40">›</button>
    </div>
  </nav>;
}
