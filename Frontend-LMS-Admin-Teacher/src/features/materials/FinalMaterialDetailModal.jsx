import {
  Download,
  ExternalLink,
  File,
  FileBadge,
  FileText,
  Headphones,
  Image,
  Link2,
  Lock,
  PlayCircle,
  Trash2,
  X
} from 'lucide-react';
import { API_BASE } from '../../api/client';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';

function youtubeEmbed(url) {
  if (!url) return null;
  try {
    const parsed = new URL(url);
    if (parsed.hostname.includes('youtu.be')) {
      const id = parsed.pathname.replace(/^\//, '');
      return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
    }
    if (parsed.hostname.includes('youtube.com')) {
      const id = parsed.searchParams.get('v');
      return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
    }
  } catch {
    return null;
  }
  return null;
}

const TYPE_CONFIG = {
  text: { label: 'Materi Teks', Icon: FileText },
  video: { label: 'Video YouTube / File', Icon: PlayCircle },
  pdf: { label: 'Dokumen PDF', Icon: FileBadge },
  image: { label: 'Gambar / Infografis', Icon: Image },
  document: { label: 'Dokumen Office / ODF', Icon: File },
  audio: { label: 'Audio', Icon: Headphones },
  link: { label: 'Tautan HTTPS', Icon: Link2 }
};

/**
 * Modal View-Only untuk melihat detail materi yang telah berstatus Final (Terbit ke Student).
 */
export default function FinalMaterialDetailModal({ material, onClose, onDelete, canDelete }) {
  if (!material) return null;

  const typeMeta = TYPE_CONFIG[material.material_type] || {
    label: material.material_type,
    Icon: FileText
  };
  const TypeIcon = typeMeta.Icon;

  const fileUrl = material.fileUrl
    ? material.fileUrl.startsWith('http')
      ? material.fileUrl
      : `${API_BASE}${material.fileUrl}`
    : null;

  const ytEmbedUrl = material.material_type === 'video' ? youtubeEmbed(material.content) : null;

  return (
    <div
      role="dialog"
      aria-modal="true"
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="dls-card-surface relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl shadow-2xl">
        {/* Header */}
        <div className="flex items-start justify-between gap-4 border-b border-outline/40 bg-surface-low/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/60">
          <div className="flex items-start gap-3">
            <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary dark:bg-red-950/60 dark:text-red-300">
              <TypeIcon className="size-5" />
            </span>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <span className="rounded-md bg-surface-lowest px-2 py-0.5 text-[11px] font-semibold text-on-surface-variant ring-1 ring-outline/40 dark:bg-slate-900 dark:text-slate-300">
                  {typeMeta.label}
                </span>
                <StatusBadge status="published" label="Final (Terbit ke Student)" dot />
              </div>
              <h2 className="mt-1.5 text-lg font-bold text-on-surface sm:text-xl">
                {material.title}
              </h2>
            </div>
          </div>

          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            aria-label="Tutup modal"
            className="grid size-8 shrink-0 place-items-center rounded-lg text-muted transition hover:bg-surface-container hover:text-on-surface dark:hover:bg-slate-700"
          >
            <X className="size-4" />
          </button>
        </div>

        {/* Metadata Info Strip */}
        <div className="grid grid-cols-2 gap-3 border-b border-outline/40 bg-surface-low/30 px-6 py-3 text-xs sm:grid-cols-4 dark:border-slate-800 dark:bg-slate-900/40">
          <div>
            <p className="text-muted">Course</p>
            <p className="truncate font-semibold text-on-surface">
              {material.course_title || material.course_code || '-'}
            </p>
          </div>
          <div>
            <p className="text-muted">Sesi</p>
            <p className="truncate font-semibold text-on-surface">{material.session_title || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Pengajar / Pembuat</p>
            <p className="truncate font-semibold text-on-surface">
              {material.submitter_name || material.submitted_by || 'Trainer LMS'}
            </p>
          </div>
          <div>
            <p className="text-muted">Tanggal Rilis</p>
            <p className="font-semibold text-on-surface">
              {material.created_at
                ? new Date(material.created_at).toLocaleDateString('id-ID', { dateStyle: 'medium' })
                : '-'}
            </p>
          </div>
        </div>

        {/* Content Body (View-Only) */}
        <div className="flex-1 overflow-y-auto px-6 py-5">
          {material.material_type === 'text' && (
            <div className="rounded-xl border border-outline/40 bg-surface-low/30 p-5 sm:p-7 dark:border-slate-800 dark:bg-slate-900/50">
              <div
                className="prose prose-slate max-w-none text-sm leading-relaxed text-on-surface dark:prose-invert [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:text-xl [&_h2]:font-bold [&_h3]:text-lg [&_h3]:font-semibold [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:my-2.5 [&_table]:my-4 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-outline/40 [&_td]:p-2.5 [&_th]:border [&_th]:border-outline/40 [&_th]:bg-surface-low [&_th]:p-2.5 [&_ul]:list-disc [&_ul]:pl-5"
                dangerouslySetInnerHTML={{
                  __html: material.content || '<p class="text-muted">Tidak ada teks materi.</p>'
                }}
              />
            </div>
          )}

          {material.material_type === 'video' && (
            <div className="space-y-4">
              {ytEmbedUrl ? (
                <div className="aspect-video w-full overflow-hidden rounded-xl bg-black shadow-sm">
                  <iframe
                    src={ytEmbedUrl}
                    title={material.title}
                    className="size-full border-0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowFullScreen
                  />
                </div>
              ) : fileUrl ? (
                <div className="overflow-hidden rounded-xl bg-black shadow-sm">
                  <video controls className="max-h-[480px] w-full">
                    <source src={fileUrl} />
                    Browser Anda tidak mendukung pemutar video HTML5.
                  </video>
                </div>
              ) : (
                <p className="text-sm text-muted">URL atau file video tidak tersedia.</p>
              )}
              {material.content && !ytEmbedUrl && (
                <p className="text-xs text-muted">Keterangan: {material.content}</p>
              )}
            </div>
          )}

          {material.material_type === 'pdf' && (
            <div className="space-y-4">
              {fileUrl ? (
                <>
                  <div className="flex items-center justify-between rounded-lg border border-outline/40 bg-surface-low/60 px-4 py-2.5 text-xs dark:border-slate-800 dark:bg-slate-800/60">
                    <span className="flex items-center gap-2 font-semibold text-on-surface">
                      <FileBadge className="size-4 text-primary" />
                      File PDF: {material.original_file_name || 'Dokumen materi'}
                    </span>
                    <a
                      href={fileUrl}
                      target="_blank"
                      rel="noreferrer"
                      className="inline-flex items-center gap-1.5 rounded-md bg-primary px-3 py-1.5 font-semibold text-white transition hover:bg-primary-container"
                    >
                      <ExternalLink className="size-3.5" />
                      Buka di Tab Baru
                    </a>
                  </div>
                  <iframe
                    src={`${fileUrl}#toolbar=1`}
                    title={material.title}
                    className="h-[520px] w-full rounded-xl border border-outline/40 dark:border-slate-800"
                  />
                </>
              ) : (
                <p className="text-sm text-muted">File PDF tidak ditemukan di server.</p>
              )}
            </div>
          )}

          {material.material_type === 'image' && (
            <div className="space-y-4 text-center">
              {fileUrl ? (
                <div className="overflow-hidden rounded-xl border border-outline/40 bg-surface-low/30 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                  <img
                    src={fileUrl}
                    alt={material.title}
                    className="mx-auto max-h-[500px] w-auto rounded-lg object-contain shadow-xs"
                  />
                </div>
              ) : (
                <p className="text-sm text-muted">File gambar tidak ditemukan.</p>
              )}
              {material.content && (
                <p className="text-left text-xs text-muted">Keterangan: {material.content}</p>
              )}
            </div>
          )}

          {material.material_type === 'audio' && (
            <div className="space-y-4 rounded-xl border border-outline/40 bg-surface-low/40 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="flex items-center gap-4">
                <span className="grid size-12 place-items-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                  <Headphones className="size-6" />
                </span>
                <div className="min-w-0 flex-1">
                  <p className="font-bold text-on-surface">{material.title}</p>
                  <p className="text-xs text-muted">
                    {material.original_file_name || 'File rekaman audio'}
                  </p>
                </div>
              </div>
              {fileUrl ? (
                <audio controls src={fileUrl} className="mt-2 w-full" />
              ) : (
                <p className="text-xs text-red-600">File audio tidak tersedia.</p>
              )}
            </div>
          )}

          {material.material_type === 'document' && (
            <div className="rounded-xl border border-outline/40 bg-surface-low/40 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3">
                  <span className="grid size-12 place-items-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                    <File className="size-6" />
                  </span>
                  <div>
                    <p className="font-bold text-on-surface">
                      {material.original_file_name || material.title}
                    </p>
                    <p className="text-xs text-muted">Dokumen Office / ODF materi resmi student</p>
                  </div>
                </div>
                {fileUrl && (
                  <a
                    href={fileUrl}
                    download
                    className="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary"
                  >
                    <Download className="size-4" />
                    Unduh Dokumen
                  </a>
                )}
              </div>
            </div>
          )}

          {material.material_type === 'link' && (
            <div className="rounded-xl border border-outline/40 bg-surface-low/40 p-6 dark:border-slate-800 dark:bg-slate-900/50">
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex min-w-0 items-center gap-3">
                  <span className="grid size-12 shrink-0 place-items-center rounded-xl bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300">
                    <Link2 className="size-6" />
                  </span>
                  <div className="min-w-0">
                    <p className="font-bold text-on-surface">{material.title}</p>
                    <a
                      href={material.content}
                      target="_blank"
                      rel="noreferrer"
                      className="block truncate text-xs text-primary underline"
                    >
                      {material.content}
                    </a>
                  </div>
                </div>
                <a
                  href={material.content}
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex min-h-9 shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white transition hover:bg-primary-container"
                >
                  <ExternalLink className="size-4" />
                  Kunjungi Tautan
                </a>
              </div>
            </div>
          )}

          {/* Note View Only */}
          <div className="mt-6 flex items-center gap-2 rounded-lg border border-outline/40 bg-surface-low/50 px-3.5 py-2.5 text-xs text-muted dark:border-slate-800 dark:bg-slate-900/40">
            <Lock className="size-4 shrink-0 text-secondary dark:text-slate-300" />
            <span>
              Materi ini telah berstatus <strong>Final</strong> dan aktif di database student. Mode{' '}
              <strong>View-Only</strong> aktif (tidak dapat diedit).
            </span>
          </div>
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between border-t border-outline/40 bg-surface-low/40 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40">
          <div>
            {canDelete && onDelete && (
              <Button
                type="button"
                variant="danger"
                size="sm"
                icon={<Trash2 className="size-3.5" />}
                onClick={() => onDelete(material)}
              >
                Hapus Materi Final
              </Button>
            )}
          </div>
          <Button type="button" variant="outline" size="sm" onClick={onClose}>
            Tutup
          </Button>
        </div>
      </div>
    </div>
  );
}
