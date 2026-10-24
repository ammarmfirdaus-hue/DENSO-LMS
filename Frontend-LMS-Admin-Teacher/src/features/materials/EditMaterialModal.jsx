import { useEffect, useRef, useState } from 'react';
import { api } from '../../api/client';

const materialTypes = [
  { value: 'text',     label: 'Materi Teks' },
  { value: 'video',    label: 'Video (YouTube / File)' },
  { value: 'pdf',      label: 'Dokumen PDF' },
  { value: 'document', label: 'Dokumen Office / ODF' },
  { value: 'image',    label: 'Gambar' },
  { value: 'audio',    label: 'Audio' },
  { value: 'link',     label: 'Tautan HTTPS' },
];

import TinyEditor from '../../components/TinyEditor';
import showAlert from '../../utils/alert';

/**
 * Modal untuk mengedit pengajuan materi yang ditolak atau dalam antrean Admin.
 * Mendukung TinyMCE untuk tipe "text".
 */
export default function EditMaterialModal({ submission, onClose, onSuccess }) {
  const [type, setType] = useState(submission?.material_type || 'text');
  const isInitialYt = Boolean(
    submission?.content && /^https?:\/\/(www\.)?(youtube\.com|youtu\.be)/i.test(submission.content)
  );
  const [videoSource, setVideoSource] = useState(isInitialYt ? 'youtube' : (submission?.file_path ? 'file' : 'youtube'));
  const [htmlContent, setHtmlContent] = useState(submission?.content || '');
  const [urlInput, setUrlInput] = useState(submission?.content || '');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  if (!submission) return null;

  const isText = type === 'text';
  const showUrlInput = type === 'link' || (type === 'video' && videoSource === 'youtube');
  const showFileInput = ['pdf', 'document', 'image', 'audio'].includes(type) || (type === 'video' && videoSource === 'file');

  async function handleSubmit(e) {
    e.preventDefault();
    if (isText && !htmlContent.trim()) {
      showAlert.warning('Isi Materi Diperlukan', 'Silakan ketikkan konten materi pembelajaran pada editor teks TinyMCE.');
      return;
    }

    let finalContent = isText ? htmlContent : '';

    if (type === 'video' && videoSource === 'youtube') {
      let normalized = urlInput.trim();
      if (!normalized) {
        showAlert.warning('Tautan YouTube Diperlukan', 'Silakan masukkan URL YouTube yang valid (diawali https://).');
        return;
      }
      if (!/^https?:\/\//i.test(normalized)) {
        normalized = 'https://' + normalized;
        setUrlInput(normalized);
      }
      try {
        const parsed = new URL(normalized);
        const validHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com', 'youtube-nocookie.com'];
        if (!validHosts.includes(parsed.hostname.toLowerCase())) {
          showAlert.warning('URL YouTube Tidak Valid', 'Pastikan URL berasal dari YouTube (youtube.com atau youtu.be).');
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

    setBusy(true); setError('');

    const formData = new FormData(e.currentTarget);
    if (isText || showUrlInput) {
      formData.set('content', finalContent);
    }

    try {
      const result = await api(`/material-workflow/submissions/${submission.submission_id}`, {
        method: 'PUT',
        body: formData,
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
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">

        <div className="flex items-center justify-between border-b border-outline/40 pb-4">
          <div>
            <h3 className="text-lg font-bold">Edit Materi Pembelajaran</h3>
            <p className="text-xs text-muted">
              {submission.status === 'rejected'
                ? 'Perbaiki materi yang ditolak dan kirimkan kembali untuk persetujuan Admin.'
                : 'Perbarui materi sebelum diteruskan ke reviewer.'}
            </p>
          </div>
          <button type="button" onClick={onClose} className="grid size-8 place-items-center rounded-lg text-muted hover:bg-surface-low">x</button>
        </div>

        {error && (
          <p role="alert" className="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-800">{error}</p>
        )}
        {submission.reviewer_comment && (
          <div className="mt-3 rounded-xl border border-red-200 bg-red-50/70 p-3 text-xs text-red-900">
            <span className="font-bold">Catatan Reviewer: </span>{submission.reviewer_comment}
          </div>
        )}
        {submission.admin_comment && (
          <div className="mt-3 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-900">
            <span className="font-bold">Catatan Admin: </span>{submission.admin_comment}
          </div>
        )}

        <form onSubmit={handleSubmit} className="mt-4 space-y-4">

          <div>
            <label className="block text-xs font-bold text-secondary">Judul Materi *</label>
            <input name="title" required maxLength="200" defaultValue={submission.title}
              placeholder="Contoh: Pengenalan Safety DENSO"
              className="mt-1 h-10 w-full rounded-lg border border-outline/60 px-3 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary/20"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-secondary">Tipe Materi *</label>
            <select name="type" value={type} onChange={e => setType(e.target.value)}
              className="mt-1 h-10 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
              {materialTypes.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
            </select>
          </div>

          {/* ── Sub-selector jika Tipe Video: YouTube atau Upload File ── */}
          {type === 'video' && (
            <div className="rounded-xl border border-primary/20 bg-primary/5 p-3">
              <p className="mb-2 text-xs font-bold text-secondary">
                Pilih Sumber Video * (Pilih salah satu):
              </p>
              <div className="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  onClick={() => setVideoSource('youtube')}
                  className={`flex items-center justify-center gap-1.5 rounded-lg py-2 px-3 text-xs font-bold transition ${
                    videoSource === 'youtube'
                      ? 'bg-primary text-white shadow-xs'
                      : 'border border-outline/50 bg-white text-secondary hover:bg-surface-low'
                  }`}
                >
                  <span>▶</span> Link YouTube (HTTPS)
                </button>
                <button
                  type="button"
                  onClick={() => setVideoSource('file')}
                  className={`flex items-center justify-center gap-1.5 rounded-lg py-2 px-3 text-xs font-bold transition ${
                    videoSource === 'file'
                      ? 'bg-primary text-white shadow-xs'
                      : 'border border-outline/50 bg-white text-secondary hover:bg-surface-low'
                  }`}
                >
                  <span>📁</span> Unggah File Video (MP4)
                </button>
              </div>
            </div>
          )}

          {isText && (
            <div>
              <label className="block text-xs font-bold text-secondary">
                Isi Materi * <span className="font-normal text-muted">(Bold, Heading, Tabel, dll.)</span>
              </label>
              <input type="hidden" name="content" value={htmlContent} readOnly />
              <TinyEditor value={htmlContent} onChange={setHtmlContent} />
              {!htmlContent.trim() && (
                <p className="mt-1 text-[11px] text-amber-700">Isi materi wajib diisi.</p>
              )}
            </div>
          )}

          {showUrlInput && (
            <div>
              <label className="block text-xs font-bold text-secondary">
                {type === 'link' ? 'Tautan HTTPS *' : 'Link YouTube HTTPS *'}
              </label>
              <input
                name="content"
                type="url"
                required
                maxLength="2000"
                value={urlInput}
                onChange={e => setUrlInput(e.target.value)}
                placeholder={type === 'link' ? 'https://contoh.com/halaman' : 'https://www.youtube.com/watch?v=... atau https://youtu.be/...'}
                className="mt-1 h-10 w-full rounded-lg border border-outline/60 px-3 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary/20"
              />
            </div>
          )}

          {showFileInput && (
            <div>
              <label className="block text-xs font-bold text-secondary">
                {type === 'video' ? 'Unggah File Video (MP4, WebM)' : 'Unggah File *'}
              </label>
              {submission.original_file_name && (
                <p className="mt-1 text-xs text-secondary">
                  File saat ini: <span className="font-semibold text-primary">{submission.original_file_name}</span>. Unggah baru jika ingin mengganti.
                </p>
              )}
              <input
                type="file"
                name="file"
                required={!submission.file_path && ['pdf', 'document', 'image', 'audio'].includes(type)}
                accept={type === 'video' ? '.mp4,.webm,.mov,.ogv' : '.pdf,.png,.jpg,.jpeg,.gif,.webp,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.ods,.odp,.mp3,.wav,.ogg,.m4a'}
                className="mt-1.5 block w-full rounded-lg border border-outline/50 bg-white p-2 text-xs"
              />
            </div>
          )}

          <div className="mt-5 flex justify-end gap-2 border-t border-outline/40 pt-4">
            <button type="button" onClick={onClose}
              className="rounded-lg border border-outline px-4 py-2 text-sm font-semibold text-secondary hover:bg-surface-low">
              Batal
            </button>
            <button type="submit" disabled={busy || (isText && !htmlContent.trim())}
              className="rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white shadow-sm hover:brightness-110 disabled:opacity-60">
              {busy ? 'Menyimpan...' : 'Simpan & Ajukan Ulang'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
