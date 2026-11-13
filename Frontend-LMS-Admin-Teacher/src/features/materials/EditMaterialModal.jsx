import { useState } from 'react';
import { Film, PlayCircle, Save, X } from 'lucide-react';
import { api } from '../../api/client';
import Button from '../../components/Button';
import TinyEditor from '../../components/TinyEditor';
import showAlert from '../../utils/alert';

const MATERIAL_TYPES = [
  { value: 'text', label: 'Materi Teks' },
  { value: 'video', label: 'Video (YouTube / File)' },
  { value: 'pdf', label: 'Dokumen PDF' },
  { value: 'document', label: 'Dokumen Office / ODF' },
  { value: 'image', label: 'Gambar' },
  { value: 'audio', label: 'Audio' },
  { value: 'link', label: 'Tautan HTTPS' }
];

const FIELD_CLASS =
  'mt-1.5 h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Modal untuk mengedit pengajuan materi yang ditolak atau dalam antrean Admin.
 * Mendukung TinyMCE untuk tipe "text".
 */
export default function EditMaterialModal({ submission, onClose, onSuccess }) {
  const [type, setType] = useState(submission?.material_type || 'text');
  const isInitialYt = Boolean(
    submission?.content && /^https?:\/\/(www\.)?(youtube\.com|youtu\.be)/i.test(submission.content)
  );
  const [videoSource, setVideoSource] = useState(
    isInitialYt ? 'youtube' : submission?.file_path ? 'file' : 'youtube'
  );
  const [htmlContent, setHtmlContent] = useState(submission?.content || '');
  const [urlInput, setUrlInput] = useState(submission?.content || '');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  if (!submission) return null;

  const isText = type === 'text';
  const showUrlInput = type === 'link' || (type === 'video' && videoSource === 'youtube');
  const showFileInput =
    ['pdf', 'document', 'image', 'audio'].includes(type) ||
    (type === 'video' && videoSource === 'file');

  async function handleSubmit(e) {
    e.preventDefault();
    if (isText && !htmlContent.trim()) {
      showAlert.warning(
        'Isi Materi Diperlukan',
        'Silakan ketikkan konten materi pembelajaran pada editor teks TinyMCE.'
      );
      return;
    }

    let finalContent = isText ? htmlContent : '';

    if (type === 'video' && videoSource === 'youtube') {
      let normalized = urlInput.trim();
      if (!normalized) {
        showAlert.warning(
          'Tautan YouTube Diperlukan',
          'Silakan masukkan URL YouTube yang valid (diawali https://).'
        );
        return;
      }
      if (!/^https?:\/\//i.test(normalized)) {
        normalized = 'https://' + normalized;
        setUrlInput(normalized);
      }
      try {
        const parsed = new URL(normalized);
        const validHosts = [
          'youtube.com',
          'www.youtube.com',
          'm.youtube.com',
          'youtu.be',
          'www.youtube-nocookie.com',
          'youtube-nocookie.com'
        ];
        if (!validHosts.includes(parsed.hostname.toLowerCase())) {
          showAlert.warning(
            'URL YouTube Tidak Valid',
            'Pastikan URL berasal dari YouTube (youtube.com atau youtu.be).'
          );
          return;
        }
      } catch {
        showAlert.warning('Tautan Tidak Valid', 'Format URL YouTube tidak valid.');
        return;
      }
      finalContent = normalized;
    } else if (type === 'link') {
      let normalized = urlInput.trim();
      if (!normalized) {
        showAlert.warning('Tautan Diperlukan', 'Silakan masukkan tautan eksternal yang valid.');
        return;
      }
      if (!/^https?:\/\//i.test(normalized)) {
        normalized = 'https://' + normalized;
        setUrlInput(normalized);
      }
      finalContent = normalized;
    }

    setBusy(true);
    setError('');

    const formData = new FormData(e.currentTarget);
    if (isText || showUrlInput) {
      formData.set('content', finalContent);
    }

    try {
      const result = await api(`/material-workflow/submissions/${submission.submission_id}`, {
        method: 'PUT',
        body: formData
      });
      showAlert.success('Perubahan Materi Disimpan', result.message || 'Materi berhasil diperbarui.');
      onSuccess(result.message);
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menyimpan Perubahan', err.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
      role="dialog"
      aria-modal="true"
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="dls-card-surface max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl shadow-2xl">
        <div className="flex items-start justify-between gap-4 border-b border-outline/40 bg-surface-low/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/60">
          <div>
            <h3 className="text-lg font-bold text-on-surface">Edit Materi Pembelajaran</h3>
            <p className="mt-0.5 text-xs text-muted">
              {submission.status === 'rejected'
                ? 'Perbaiki materi yang ditolak dan ajukan kembali untuk persetujuan Admin.'
                : 'Perbarui materi sebelum diteruskan ke reviewer.'}
            </p>
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

        <div className="p-6">
          {error && (
            <p
              role="alert"
              className="mb-3 rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
            >
              {error}
            </p>
          )}
          {submission.reviewer_comment && (
            <div className="mb-3 rounded-lg border border-red-200 bg-red-50/70 p-3 text-xs text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
              <span className="font-bold">Catatan Reviewer: </span>
              {submission.reviewer_comment}
            </div>
          )}
          {submission.admin_comment && (
            <div className="mb-3 rounded-lg border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
              <span className="font-bold">Catatan Admin: </span>
              {submission.admin_comment}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-bold text-on-surface-variant">
                Judul Materi *
              </label>
              <input
                name="title"
                required
                maxLength="200"
                defaultValue={submission.title}
                placeholder="Contoh: Pengenalan Safety DENSO"
                className={FIELD_CLASS}
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-on-surface-variant">
                Tipe Materi *
              </label>
              <select
                name="type"
                value={type}
                onChange={(e) => setType(e.target.value)}
                className={FIELD_CLASS}
              >
                {MATERIAL_TYPES.map((t) => (
                  <option key={t.value} value={t.value}>
                    {t.label}
                  </option>
                ))}
              </select>
            </div>

            {type === 'video' && (
              <div className="rounded-xl border border-primary/20 bg-primary/5 p-3 dark:border-primary/30 dark:bg-primary/10">
                <p className="mb-2 text-xs font-bold text-on-surface-variant">
                  Pilih Sumber Video *
                </p>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    data-dls-action-ignore="true"
                    onClick={() => setVideoSource('youtube')}
                    className={`flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition ${
                      videoSource === 'youtube'
                        ? 'bg-primary text-white shadow-xs'
                        : 'border border-outline bg-surface-lowest text-on-surface-variant hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
                    }`}
                  >
                    <PlayCircle className="size-4" />
                    Link YouTube (HTTPS)
                  </button>
                  <button
                    type="button"
                    data-dls-action-ignore="true"
                    onClick={() => setVideoSource('file')}
                    className={`flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition ${
                      videoSource === 'file'
                        ? 'bg-primary text-white shadow-xs'
                        : 'border border-outline bg-surface-lowest text-on-surface-variant hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
                    }`}
                  >
                    <Film className="size-4" />
                    Unggah File Video (MP4)
                  </button>
                </div>
              </div>
            )}

            {isText && (
              <div>
                <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
                  Isi Materi *{' '}
                  <span className="font-normal text-muted">(Bold, Heading, Tabel, dll.)</span>
                </label>
                <input type="hidden" name="content" value={htmlContent} readOnly />
                <TinyEditor value={htmlContent} onChange={setHtmlContent} />
                {!htmlContent.trim() && (
                  <p className="mt-1 text-[11px] text-amber-700 dark:text-amber-300">
                    Isi materi wajib diisi.
                  </p>
                )}
              </div>
            )}

            {showUrlInput && (
              <div>
                <label className="block text-xs font-bold text-on-surface-variant">
                  {type === 'link' ? 'Tautan HTTPS *' : 'Link YouTube HTTPS *'}
                </label>
                <input
                  name="content"
                  type="url"
                  required
                  maxLength="2000"
                  value={urlInput}
                  onChange={(e) => setUrlInput(e.target.value)}
                  placeholder={
                    type === 'link'
                      ? 'https://contoh.com/halaman'
                      : 'https://www.youtube.com/watch?v=... atau https://youtu.be/...'
                  }
                  className={FIELD_CLASS}
                />
              </div>
            )}

            {showFileInput && (
              <div>
                <label className="block text-xs font-bold text-on-surface-variant">
                  {type === 'video' ? 'Unggah File Video (MP4, WebM)' : 'Unggah File *'}
                </label>
                {submission.original_file_name && (
                  <p className="mt-1 text-xs text-muted">
                    File saat ini:{' '}
                    <span className="font-semibold text-primary">
                      {submission.original_file_name}
                    </span>
                    . Unggah berkas baru jika ingin mengganti.
                  </p>
                )}
                <input
                  type="file"
                  name="file"
                  required={!submission.file_path && ['pdf', 'document', 'image', 'audio'].includes(type)}
                  accept={
                    type === 'video'
                      ? '.mp4,.webm,.mov,.ogv'
                      : '.pdf,.png,.jpg,.jpeg,.gif,.webp,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.ods,.odp,.mp3,.wav,.ogg,.m4a'
                  }
                  className="mt-1.5 block w-full rounded-md border border-outline/60 bg-surface-lowest p-2 text-xs text-on-surface dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                />
              </div>
            )}

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-4 dark:border-slate-800">
              <Button type="button" variant="outline" size="sm" onClick={onClose}>
                Batal
              </Button>
              <Button
                type="submit"
                variant="primary"
                size="sm"
                loading={busy}
                disabled={busy || (isText && !htmlContent.trim())}
                icon={<Save className="size-3.5" />}
              >
                Simpan &amp; Ajukan Ulang
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
