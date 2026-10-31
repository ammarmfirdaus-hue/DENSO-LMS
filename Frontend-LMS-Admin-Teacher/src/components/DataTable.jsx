import React from 'react';

/**
 * Standard DataTable component with accessible horizontal scroll container and consistent theming.
 *
 * @param {Object} props
 * @param {Array<{ key: string, label: React.ReactNode, align?: 'left'|'center'|'right', className?: string }>} props.columns
 * @param {React.ReactNode} props.children - Table body rows (<tr> elements).
 * @param {boolean} [props.loading=false]
 * @param {boolean} [props.empty=false]
 * @param {string} [props.emptyMessage='Belum ada data untuk ditampilkan.']
 * @param {React.ReactNode} [props.emptyAction]
 * @param {string} [props.ariaLabel='Tabel data']
 * @param {string} [props.className]
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
    <div
      className={`overflow-hidden rounded-2xl border border-outline/50 bg-white shadow-xs dark:border-slate-800 dark:bg-[#1e293b] ${className}`}
    >
      <div
        className="max-w-full overflow-x-auto overscroll-x-contain"
        role="region"
        aria-label={ariaLabel}
        tabIndex={0}
      >
        <table className="w-full min-w-[720px] border-collapse text-left text-sm">
          {columns.length > 0 && (
            <thead className="border-b border-outline/50 bg-surface-low text-xs font-semibold uppercase tracking-wider text-muted dark:border-slate-800 dark:bg-slate-900/60">
              <tr>
                {columns.map((col, index) => {
                  const alignClass = col.align === 'right' ? 'text-right' : col.align === 'left' ? 'text-left' : 'text-center';
                  return (
                    <th
                      key={col.key || index}
                      scope="col"
                      className={`px-4 py-3.5 ${alignClass} ${col.className || ''}`}
                    >
                      {col.label}
                    </th>
                  );
                })}
              </tr>
            </thead>
          )}

          <tbody className="divide-y divide-outline/40 dark:divide-slate-800/80">
            {loading ? (
              Array.from({ length: 5 }).map((_, idx) => (
                <tr key={idx} className="animate-pulse">
                  {columns.map((col, colIdx) => (
                    <td key={colIdx} className="px-4 py-4">
                      <div className="h-4 w-3/4 rounded bg-surface-low dark:bg-slate-800" />
                    </td>
                  ))}
                </tr>
              ))
            ) : empty ? (
              <tr>
                <td
                  colSpan={columns.length || 1}
                  className="px-6 py-12 text-center"
                >
                  <div className="mx-auto flex max-w-sm flex-col items-center justify-center text-center">
                    <span
                      className="grid size-12 place-items-center rounded-2xl bg-surface-low text-muted dark:bg-slate-800"
                      aria-hidden="true"
                    >
                      <span className="material-symbol text-2xl">folder_off</span>
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

