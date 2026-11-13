import React from 'react';
import { FolderOpen } from 'lucide-react';

/**
 * Standard DataTable component aligned with design.md neutral operational table specs:
 * 48px high header, uppercase tracked labels, clean horizontal row dividers, no heavy gridlines.
 */
export default function DataTable({
  columns = [],
  children,
  loading = false,
  empty = false,
  emptyMessage = 'Belum ada data untuk ditampilkan.',
  emptyAction,
  ariaLabel = 'Tabel data',
  className = ''
}) {
  return (
    <div className={`dls-table-wrap overflow-hidden rounded-xl border border-[#d5dce4] bg-surface-elevated shadow-xs dark:border-[#2f3c4f] ${className}`}>
      <div
        className="max-w-full overflow-x-auto overscroll-x-contain"
        role="region"
        aria-label={ariaLabel}
        tabIndex={0}
      >
        <table className="w-full min-w-[700px] border-collapse text-left text-sm">
          {columns.length > 0 && (
            <thead>
              <tr>
                {columns.map((col, index) => {
                  const alignClass =
                    col.align === 'right'
                      ? 'text-right'
                      : col.align === 'center'
                        ? 'text-center'
                        : 'text-left';
                  return (
                    <th
                      key={col.key || index}
                      scope="col"
                      className={`${alignClass} ${col.className || ''}`}
                    >
                      {col.label}
                    </th>
                  );
                })}
              </tr>
            </thead>
          )}

          <tbody>
            {loading ? (
              Array.from({ length: 5 }).map((_, idx) => (
                <tr key={idx} className="animate-pulse">
                  {columns.map((col, colIdx) => (
                    <td key={colIdx} className="px-4 py-3.5">
                      <div className="h-4 w-3/4 rounded bg-surface-container" />
                    </td>
                  ))}
                </tr>
              ))
            ) : empty ? (
              <tr>
                <td colSpan={columns.length || 1} className="px-6 py-12 text-center">
                  <div className="mx-auto flex max-w-sm flex-col items-center justify-center text-center">
                    <span
                      className="grid size-12 place-items-center rounded-xl bg-surface-low text-muted"
                      aria-hidden="true"
                    >
                      <FolderOpen className="h-6 w-6 stroke-[1.75]" />
                    </span>
                    <p className="mt-3 text-sm font-medium text-muted">
                      {emptyMessage}
                    </p>
                    {emptyAction && <div className="mt-4">{emptyAction}</div>}
                  </div>
                </td>
              </tr>
            ) : (
              children
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
