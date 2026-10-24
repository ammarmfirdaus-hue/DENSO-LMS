import React from 'react';

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';

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
  text:     { label: 'Materi Teks',          icon: 'article',        badge: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20' },
  video:    { label: 'Video YouTube / File', icon: 'play_circle',    badge: 'bg-indigo-50 text-indigo-800 ring-indigo-600/20' },
  pdf:      { label: 'Dokumen PDF',          icon: 'picture_as_pdf', badge: 'bg-rose-50 text-rose-800 ring-rose-600/20' },
  image:    { label: 'Gambar / Infografis',  icon: 'image',          badge: 'bg-amber-50 text-amber-800 ring-amber-600/20' },
  document: { label: 'Dokumen Office / ODF', icon: 'description',    badge: 'bg-blue-50 text-blue-800 ring-blue-600/20' },
  audio:    { label: 'Audio',                icon: 'headphones',     badge: 'bg-purple-50 text-purple-800 ring-purple-600/20' },
  link:     { label: 'Tautan HTTPS',         icon: 'link',           badge: 'bg-teal-50 text-teal-800 ring-teal-600/20' },
};

/**
 * Modal View-Only untuk melihat detail materi yang telah berstatus Final (Terbit ke Student).
 * Bersifat read-only: tidak menyediakan opsi edit atau penambahan materi baru.
 *
 * @param {{
 *   material: Object,
 *   onClose: () => void,
 *   onDelete?: (material: Object) => void,
 *   canDelete?: boolean
 * }} props
 */
export default function FinalMaterialDetailModal({ material, onClose, onDelete, canDelete }) {
  if (!material) return null;

  const typeMeta = TYPE_CONFIG[material.material_type] || {
    label: material.material_type,
    icon: 'draft',
    badge: 'bg-surface-low text-secondary ring-outline/30',
  };

  const fileUrl = material.fileUrl
    ? (material.fileUrl.startsWith('http') ? material.fileUrl : `${API_BASE}${material.fileUrl}`)
    : null;

  const ytEmbedUrl = material.material_type === 'video' ? youtubeEmbed(material.content) : null;

  return (
    <div
      role="dialog"
      aria-modal="true"
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div className="relative flex max-h-[90vh] w-full max-w-4xl flex-col rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
        
        {/* Header */}
        <div className="flex items-start justify-between border-b border-outline/40 px-6 py-5">
          <div className="flex items-center gap-3">
            <span className="grid size-11 place-items-center rounded-2xl bg-primary/10 text-primary">
              <span className="material-symbol text-2xl" aria-hidden="true">{typeMeta.icon}</span>
            </span>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <span className={`rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset ${typeMeta.badge}`}>
                  {typeMeta.label}
                </span>
                <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
                  ✓ Final (Terbit ke Student)
                </span>
              </div>
              <h2 className="mt-1 text-lg font-bold text-on-surface sm:text-xl">
                {material.title}
              </h2>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup modal"
            className="grid size-9 place-items-center rounded-xl text-muted transition hover:bg-surface-low hover:text-secondary"
          >
            ✕
          </button>
        </div>

        {/* Metadata info strip */}
        <div className="grid grid-cols-2 gap-3 border-b border-outline/30 bg-surface-low/50 px-6 py-3 text-xs sm:grid-cols-4">
          <div>
            <p className="text-muted">Course</p>
            <p className="font-semibold text-secondary truncate">{material.course_title || material.course_code || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Sesi</p>
            <p className="font-semibold text-secondary truncate">{material.session_title || '-'}</p>
          </div>
          <div>
            <p className="text-muted">Pengajar / Pembuat</p>
            <p className="font-semibold text-secondary truncate">{material.submitter_name || material.submitted_by || 'Trainer LMS'}</p>
          </div>
          <div>
            <p className="text-muted">Tanggal Rilis</p>
            <p className="font-semibold text-secondary">
              {material.created_at ? new Date(material.created_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-'}
            </p>
          </div>
        </div>

        {/* Content Body (View-Only) */}
        <div className="flex-1 overflow-y-auto px-6 py-5">
          
          {/* Teks / HTML Content */}
          {material.material_type === 'text' && (
            <div className="rounded-2xl border border-outline/40 bg-surface-low/30 p-5 sm:p-7">
              <div
                className="prose prose-slate max-w-none text-sm leading-relaxed text-secondary [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:text-xl [&_h2]:font-bold [&_h3]:text-lg [&_h3]:font-semibold [&_p]:my-2.5 [&_table]:my-4 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-outline/40 [&_td]:p-2.5 [&_th]:border [&_th]:border-outline/40 [&_th]:bg-surface-low [&_th]:p-2.5 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5"
                dangerouslySetInnerHTML={{ __html: material.content || '<p class="text-muted">Tidak ada teks materi.</p>' }}
              />
            </div>
          )}

          {/* Video (YouTube embed atau File Video) */}
          {material.material_type === 'video' && (
            <div className="space-y-4">
              {ytEmbedUrl ? (
                <div className="aspect-video w-full overflow-hidden rounded-2xl bg-black shadow-md">
                  <iframe
                    src={ytEmbedUrl}
                    title={material.title}
                    className="size-full"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowFullScreen
                  />
                </div>
              ) : fileUrl ? (
                <div className="overflow-hidden rounded-2xl bg-black shadow-md">
                  <video controls className="w-full max-h-[480px]">
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

          {/* PDF Viewer & Download */}
          {material.material_type === 'pdf' && (
            <div className="space-y-4">
              {fileUrl ? (
                <>
                  <div className="flex items-center justify-between rounded-xl bg-surface-low p-3 text-xs">
                    <span className="font-semibold text-secondary">
                      📄 File PDF: {material.original_file_name || 'Dokumen materi'}
                    </span>
                    <a
                      href={fileUrl}
                      target="_blank"
                      rel="noreferrer"
                      className="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 font-bold text-white shadow-xs hover:brightness-110"
                    >
                      <span className="material-symbol text-sm" aria-hidden="true">open_in_new</span>
                      Buka di Tab Baru
                    </a>
                  </div>
                  <iframe
                    src={`${fileUrl}#toolbar=1`}
                    title={material.title}
                    className="h-[520px] w-full rounded-2xl border border-outline/40 shadow-xs"
                  />
                </>
              ) : (
                <p className="text-sm text-muted">File PDF tidak ditemukan di server.</p>
              )}
            </div>
          )}

          {/* Gambar / Infografis */}
          {material.material_type === 'image' && (
            <div className="space-y-4 text-center">
              {fileUrl ? (
                <div className="overflow-hidden rounded-2xl border border-outline/40 bg-surface-low/30 p-2">
                  <img
                    src={fileUrl}
                    alt={material.title}
                    className="mx-auto max-h-[500px] w-auto rounded-xl object-contain shadow-sm"
                  />
                </div>
              ) : (
                <p className="text-sm text-muted">File gambar tidak ditemukan.</p>
              )}
              {material.content && (
                <p className="text-xs text-muted text-left">Keterangan: {material.content}</p>
              )}
            </div>
          )}

          {/* Audio */}
          {material.material_type === 'audio' && (
            <div className="space-y-4 rounded-2xl border border-outline/40 bg-surface-low/40 p-6">
              <div className="flex items-center gap-4">
                <span className="grid size-14 place-items-center rounded-2xl bg-purple-100 text-purple-700">
                  <span className="material-symbol text-3xl" aria-hidden="true">audiotrack</span>
                </span>
                <div className="min-w-0 flex-1">
                  <p className="font-bold text-secondary">{material.title}</p>
                  <p className="text-xs text-muted">{material.original_file_name || 'File rekaman audio'}</p>
                </div>
              </div>
              {fileUrl ? (
                <audio controls src={fileUrl} className="w-full mt-2" />
              ) : (
                <p className="text-xs text-red-600">File audio tidak tersedia.</p>
              )}
            </div>
          )}

          {/* Dokumen Office / ODF */}
          {material.material_type === 'document' && (
            <div className="rounded-2xl border border-outline/40 bg-surface-low/40 p-6">
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3">
                  <span className="grid size-12 place-items-center rounded-2xl bg-blue-100 text-blue-700">
                    <span className="material-symbol text-2xl" aria-hidden="true">description</span>
                  </span>
                  <div>
                    <p className="font-bold text-secondary">{material.original_file_name || material.title}</p>
                    <p className="text-xs text-muted">Dokumen Office / ODF materi resmi student</p>
                  </div>
                </div>
                {fileUrl && (
                  <a
                    href={fileUrl}
                    download
                    className="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-secondary px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-[#242c33]"
                  >
                    <span className="material-symbol text-base" aria-hidden="true">download</span>
                    Unduh Dokumen
                  </a>
                )}
              </div>
            </div>
          )}

          {/* Tautan HTTPS */}
          {material.material_type === 'link' && (
            <div className="rounded-2xl border border-outline/40 bg-surface-low/40 p-6">
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3 min-w-0">
                  <span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-teal-100 text-teal-700">
                    <span className="material-symbol text-2xl" aria-hidden="true">link</span>
                  </span>
                  <div className="min-w-0">
                    <p className="font-bold text-secondary">{material.title}</p>
                    <a
                      href={material.content}
                      target="_blank"
                      rel="noreferrer"
                      className="text-xs text-primary underline truncate block"
                    >
                      {material.content}
                    </a>
                  </div>
                </div>
                <a
                  href={material.content}
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white shadow-xs hover:brightness-110"
                >
                  <span className="material-symbol text-base" aria-hidden="true">open_in_new</span>
                  Kunjungi Tautan
                </a>
              </div>
            </div>
          )}

          {/* Note View Only */}
          <div className="mt-6 flex items-center gap-2 rounded-xl border border-outline/30 bg-surface-low/50 px-3.5 py-2.5 text-xs text-muted">
            <span className="material-symbol text-base text-secondary" aria-hidden="true">lock</span>
            <span>
              Materi ini telah berstatus <strong>Final</strong> dan aktif di database student. Mode <strong>View-Only</strong> aktif (tidak dapat diedit).
            </span>
          </div>
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between border-t border-outline/40 bg-surface-low/30 px-6 py-4">
          <div>
            {canDelete && onDelete && (
              <button
                type="button"
                onClick={() => onDelete(material)}
                className="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100"
              >
                <span className="material-symbol text-base" aria-hidden="true">delete</span>
                Hapus Materi Final
              </button>
            )}
          </div>
          <button
            type="button"
            onClick={onClose}
            className="rounded-xl border border-outline/60 bg-white px-5 py-2 text-xs font-semibold text-secondary hover:bg-surface-low"
          >
            Tutup
          </button>
        </div>

      </div>
    </div>
  );
}
