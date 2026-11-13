import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { BellRing, BookOpen, ClipboardCheck, MessageSquare, Settings, Megaphone, X } from 'lucide-react';

/**
 * ModalNotif — popup modal realtime untuk notifikasi broadcast / pemberitahuan.
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
  const categoryMeta = {
    course: { label: 'Course', Icon: BookOpen },
    assessment: { label: 'Assessment', Icon: ClipboardCheck },
    forum: { label: 'Forum', Icon: MessageSquare },
    system: { label: 'Sistem', Icon: Settings },
    announcement: { label: 'Pengumuman', Icon: Megaphone }
  };

  const activeCategory = categoryMeta[notification.category] || categoryMeta.announcement;
  const IconComp = isImportant ? BellRing : activeCategory.Icon;
  const dateFormatted = notification.created_at
    ? new Date(notification.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
    : 'Baru saja';

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-notif-title"
      className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-xs"
      onClick={e => {
        if (e.target === e.currentTarget) onClose?.();
      }}
    >
      <div className="relative flex w-full max-w-lg flex-col overflow-hidden rounded-xl border border-outline bg-white shadow-[0_20px_25px_rgba(0,0,0,0.16)]">
        <div
          className={`flex items-center justify-between border-b border-outline px-6 py-4 ${
            isImportant ? 'bg-red-50/75 dark:bg-red-950/30' : 'bg-surface-low/60'
          }`}
        >
          <div className="flex min-w-0 items-center gap-3">
            <span
              className={`grid size-11 shrink-0 place-items-center rounded-lg ${
                isImportant
                  ? 'bg-primary text-white'
                  : 'bg-primary/10 text-primary dark:bg-primary/20'
              }`}
            >
              <IconComp className="h-5 w-5" aria-hidden="true" />
            </span>
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-2">
                <span className="rounded-full border border-outline bg-white px-2.5 py-0.5 text-xs font-semibold text-on-surface">
                  {activeCategory.label}
                </span>
                {isImportant && (
                  <span className="rounded-full bg-primary px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.05em] text-white">
                    Penting
                  </span>
                )}
              </div>
              <p className="mt-1 truncate text-xs text-muted">
                {notification.sender_name ? `Dari ${notification.sender_name} · ` : ''}
                {dateFormatted}
              </p>
            </div>
          </div>

          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            aria-label="Tutup modal notifikasi"
            className="grid size-9 shrink-0 cursor-pointer place-items-center rounded-lg border border-outline text-muted transition hover:bg-surface-low hover:text-on-surface"
          >
            <X className="h-4 w-4" aria-hidden="true" />
          </button>
        </div>

        <div className="max-h-[60vh] flex-1 space-y-4 overflow-y-auto p-6">
          <h3 id="modal-notif-title" className="break-words text-lg font-bold leading-snug text-on-surface">
            {notification.title}
          </h3>

          <div className="whitespace-pre-wrap break-words rounded-lg border border-outline bg-surface-low/60 p-4 text-sm leading-relaxed text-on-surface">
            {notification.message}
          </div>
        </div>

        <div className="flex flex-col-reverse items-center justify-between gap-3 border-t border-outline bg-surface-low/50 px-6 py-4 sm:flex-row">
          <Link
            to="/notifications"
            onClick={onClose}
            className="py-1 text-xs font-semibold text-primary hover:underline"
          >
            Buka Halaman Notifikasi
          </Link>
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            className="h-10 w-full cursor-pointer rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary-container sm:w-auto"
          >
            Saya Mengerti
          </button>
        </div>
      </div>
    </div>
  );
}
