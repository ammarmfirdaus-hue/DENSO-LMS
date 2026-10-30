import { useEffect } from 'react';
import { Link } from 'react-router-dom';

/**
 * ModalNotif
 * Popup modal realtime untuk menampilkan notifikasi broadcast / pemberitahuan kepada pengguna.
 *
 * @param {{
 *   isOpen: boolean,
 *   notification: {
 *     notification_id: string|number,
 *     title: string,
 *     message: string,
 *     category?: string,
 *     priority?: string,
 *     sender_name?: string,
 *     sender_role?: string,
 *     created_at?: string
 *   }|null,
 *   onClose: () => void
 * }} props
 */
export default function ModalNotif({ isOpen, notification, onClose }) {
  useEffect(() => {
    if (!isOpen) return;
    function handleKeyDown(e) {
      if (e.key === 'Escape') onClose?.();
    }
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose]);

  if (!isOpen || !notification) return null;

  const isImportant = notification.priority === 'important';
  const categoryIcons = {
    course: 'school',
    assessment: 'assignment',
    forum: 'forum',
    system: 'settings',
    announcement: 'campaign'
  };
  const categoryNames = {
    course: 'Course',
    assessment: 'Assessment',
    forum: 'Forum',
    system: 'Sistem',
    announcement: 'Pengumuman'
  };

  const iconName = isImportant ? 'notifications_active' : (categoryIcons[notification.category] || 'campaign');
  const catLabel = categoryNames[notification.category] || 'Pemberitahuan';
  const dateFormatted = notification.created_at
    ? new Date(notification.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
    : 'Baru saja';

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-notif-title"
      className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-xs transition-opacity"
      onClick={e => {
        if (e.target === e.currentTarget) onClose?.();
      }}
    >
      <div className="relative flex w-full max-w-lg flex-col overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#1e293b] dark:ring-white/10">
        
        {/* Header */}
        <div className={`flex items-center justify-between border-b border-outline/30 px-6 py-5 ${isImportant ? 'bg-red-50/80 dark:bg-red-950/30' : 'bg-surface-low/50 dark:bg-slate-800/50'}`}>
          <div className="flex items-center gap-3.5 min-w-0">
            <span
              className={`grid size-12 shrink-0 place-items-center rounded-2xl shadow-sm ${
                isImportant
                  ? 'bg-red-600 text-white shadow-red-500/20'
                  : 'bg-primary/10 text-primary dark:bg-primary/20'
              }`}
            >
              <span className="material-symbol text-2xl" aria-hidden="true">
                {iconName}
              </span>
            </span>
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-2">
                <span className="rounded-md border border-outline/40 bg-white/80 px-2 py-0.5 text-xs font-semibold text-secondary shadow-xs dark:bg-slate-800 dark:text-slate-300">
                  {catLabel}
                </span>
                {isImportant && (
                  <span className="rounded-md bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                    Penting
                  </span>
                )}
                <span className="inline-flex items-center gap-1 rounded-md bg-amber-500/15 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                  <span className="material-symbol text-xs" aria-hidden="true">campaign</span>
                  Pemberitahuan
                </span>
              </div>
              <p className="mt-1 truncate text-xs text-muted">
                {notification.sender_name ? `Dari: ${notification.sender_name} · ` : ''}{dateFormatted}
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup modal notifikasi"
            className="grid size-9 shrink-0 place-items-center rounded-xl text-muted transition hover:bg-surface-low hover:text-secondary cursor-pointer dark:hover:bg-slate-700"
          >
            <span className="material-symbol text-lg" aria-hidden="true">close</span>
          </button>
        </div>

        {/* Content Body */}
        <div className="flex-1 overflow-y-auto p-6 space-y-4 max-h-[60vh]">
          <h3
            id="modal-notif-title"
            className="text-lg sm:text-xl font-bold text-on-surface break-words leading-snug"
          >
            {notification.title}
          </h3>

          <div className="rounded-2xl border border-outline/30 bg-surface-low/60 p-4 sm:p-5 text-sm sm:text-base leading-relaxed text-secondary whitespace-pre-wrap break-words dark:bg-slate-800/60 dark:text-slate-200">
            {notification.message}
          </div>
        </div>

        {/* Footer */}
        <div className="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 border-t border-outline/30 bg-surface-low/40 px-6 py-4 dark:bg-slate-800/40">
          <Link
            to="/notifications"
            onClick={onClose}
            className="text-xs font-semibold text-primary hover:underline py-1.5"
          >
            Buka Halaman Notifikasi
          </Link>
          <button
            type="button"
            onClick={onClose}
            className="w-full sm:w-auto min-h-11 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition cursor-pointer"
          >
            Saya Mengerti
          </button>
        </div>

      </div>
    </div>
  );
}
