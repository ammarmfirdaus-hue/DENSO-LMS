import { useEffect, useRef, useState } from 'react';

/**
 * Daftar tipe materi yang tersedia beserta label, ikon, dan metadata tampilan.
 */
const MATERIAL_TYPES = [
  { value: 'text',     label: 'Materi Teks',           icon: '\u{1F4DD}', desc: 'Konten artikel/bacaan dengan format HTML (Bold, Heading, tabel, dll.)' },
  { value: 'video',    label: 'Video YouTube / File',  icon: '\u{1F3AC}', desc: 'Link YouTube atau unggah file video (mp4, webm, mov)' },
  { value: 'pdf',      label: 'Dokumen PDF',           icon: '\u{1F4C4}', desc: 'Unggah file PDF' },
  { value: 'image',    label: 'Gambar / Infografis',   icon: '\u{1F5BC}', desc: 'Unggah gambar (PNG, JPG, WebP, GIF)' },
  { value: 'document', label: 'Dokumen Office / ODF',  icon: '\u{1F4CB}', desc: 'Unggah file Word, PowerPoint, Excel, atau ODF' },
  { value: 'audio',    label: 'Audio',                 icon: '\u{1F3B5}', desc: 'Unggah file audio (MP3, WAV, OGG, M4A)' },
  { value: 'link',     label: 'Tautan HTTPS',          icon: '\u{1F517}', desc: 'Masukkan URL tautan eksternal (harus HTTPS)' },
];

const ACCEPT_MAP = {
  pdf: '.pdf',
  image: '.png,.jpg,.jpeg,.gif,.webp',
  video: '.mp4,.webm,.mov,.ogv',
  audio: '.mp3,.wav,.ogg,.m4a',
  document: '.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.ods,.odp',
};

import TinyEditor from '../../components/TinyEditor';
import showAlert from '../../utils/alert';

/**
 * Komponen form pengajuan materi baru yang dinamis.
 * Field input berubah sesuai tipe materi. TinyMCE digunakan untuk tipe "text".
 * @param {{ sessionId: number|string, busy: boolean, onSubmit: Function }} props
 */
export default function SubmitMaterialForm({ sessionId, busy, onSubmit }) {
  const [type, setType] = useState('text');
  const [videoSource, setVideoSource] = useState('youtube'); // 'youtube' | 'file'
  const [htmlContent, setHtmlContent] = useState('');
  const [urlInput, setUrlInput] = useState('');
  const formRef = useRef(null);

  const needsFile = ['pdf', 'image', 'document', 'audio'].includes(type) || (type === 'video' && videoSource === 'file');
  const needsUrl  = type === 'link' || (type === 'video' && videoSource === 'youtube');
  const isText    = type === 'text';

  /** Inject HTML dari TinyMCE ke hidden input sebelum submit */
  function handleSubmit(e) {
    e.preventDefault();
    if (isText && !htmlContent.trim()) {
      showAlert.warning('Isi Materi Diperlukan', 'Silakan ketikkan konten materi pembelajaran pada editor teks TinyMCE.');
      return;
    }
    if (type === 'video') {
      if (videoSource === 'youtube') {
        let normalizedUrl = urlInput.trim();
        if (!normalizedUrl) {
          showAlert.warning('Tautan YouTube Diperlukan', 'Silakan masukkan URL YouTube yang valid (diawali https://).');
          return;
        }
        if (!/^https?:\/\//i.test(normalizedUrl)) {
          normalizedUrl = 'https://' + normalizedUrl;
          setUrlInput(normalizedUrl);
        }
        try {
          const parsed = new URL(normalizedUrl);
          const validHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com', 'youtube-nocookie.com'];
          if (!validHosts.includes(parsed.hostname.toLowerCase())) {
            showAlert.warning('URL YouTube Tidak Valid', 'Pastikan URL berasal dari YouTube (youtube.com atau youtu.be).');
            return;
          }
        } catch {
          showAlert.warning('Tautan Tidak Valid', 'Silakan periksa kembali format URL YouTube Anda.');
          return;
        }
      } else if (videoSource === 'file') {
        const fileInput = formRef.current?.querySelector('input[name="file"]');
        if (!fileInput?.files?.length) {
          showAlert.warning('File Video Diperlukan', 'Silakan pilih file video (MP4/WebM) dari perangkat Anda.');
          return;
        }
      }
    }
    if (type === 'link') {
      let normalizedUrl = urlInput.trim();
      if (!normalizedUrl) {
        showAlert.warning('Tautan URL Diperlukan', 'Silakan masukkan tautan eksternal yang valid (diawali https://).');
        return;
      }
      if (!/^https?:\/\//i.test(normalizedUrl)) {
        normalizedUrl = 'https://' + normalizedUrl;
        setUrlInput(normalizedUrl);
      }
    }
    if (['pdf', 'image', 'document', 'audio'].includes(type)) {
      const fileInput = formRef.current?.querySelector('input[name="file"]');
      if (!fileInput?.files?.length) {
        showAlert.warning('File Materi Diperlukan', 'Silakan pilih file materi yang ingin diunggah.');
        return;
      }
    }
    const hidden = formRef.current?.querySelector('input[name="content"]');
    if (hidden && isText) hidden.value = htmlContent;
    onSubmit(e, sessionId);
  }

  /** Reset editor HTML & URL saat submit berhasil (busy: true -> false) */
  const prevBusy = useRef(busy);
  useEffect(() => {
    if (prevBusy.current && !busy) {
      setHtmlContent('');
      setUrlInput('');
    }
    prevBusy.current = busy;
  }, [busy]);

  const typeInfo = MATERIAL_TYPES.find(t => t.value === type);

  return (
    <div className="border-t border-outline/30 bg-white dark:bg-slate-900 px-5 py-5">
      <div className="mb-4 flex items-center gap-3">
        <span className="grid size-9 place-items-center rounded-xl bg-primary/10 text-lg">{typeInfo?.icon}</span>
        <div>
          <h4 className="text-sm font-bold text-on-surface">Ajukan Materi Baru</h4>
          <p className="text-xs text-muted">Dikirim ke antrean persetujuan Admin setelah diklik.</p>
        </div>
      </div>

      <form ref={formRef} onSubmit={handleSubmit} className="space-y-4">

        {/* ── Judul ── */}
        <div>
          <label htmlFor={`title-${sessionId}`} className="block text-xs font-semibold text-secondary">
            Judul Materi <span className="text-red-500">*</span>
          </label>
          <input
            id={`title-${sessionId}`}
            name="title"
            required
            maxLength="200"
            placeholder="Contoh: Pengenalan Keselamatan Kerja"
            className="mt-1.5 h-10 w-full rounded-xl border border-outline/60 bg-white px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
          />
        </div>

        {/* ── Tipe Materi — radio visual ── */}
        <div>
          <p className="mb-2 text-xs font-semibold text-secondary">Tipe Materi <span className="text-red-500">*</span></p>
          <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
            {MATERIAL_TYPES.map(t => (
              <label
                key={t.value}
                className={[
                  'flex cursor-pointer items-center gap-2 rounded-xl border p-2.5 text-xs transition-all select-none',
                  type === t.value
                    ? 'border-primary bg-primary/8 font-bold text-primary shadow-sm ring-1 ring-primary/30'
                    : 'border-outline/40 bg-white text-secondary hover:border-primary/40 hover:bg-primary/5',
                ].join(' ')}
              >
                <input type="radio" name="type" value={t.value} checked={type === t.value}
                  onChange={() => setType(t.value)} className="sr-only" />
                <span className="shrink-0 text-base leading-none">{t.icon}</span>
                <span className="leading-tight">{t.label}</span>
              </label>
            ))}
          </div>
          {typeInfo && <p className="mt-2 text-[11px] text-muted">{typeInfo.desc}</p>}
        </div>

        {/* ── Pilihan Sumber Video jika memilih tipe Video (Link YouTube ATAU Upload File) ── */}
        {type === 'video' && (
          <div className="rounded-2xl border border-primary/20 bg-primary/5 p-3.5">
            <p className="mb-2 text-xs font-bold text-secondary">
              Pilih Sumber Video <span className="text-red-500">*</span> (Pilih salah satu):
            </p>
            <div className="grid grid-cols-2 gap-2">
              <button
                type="button"
                onClick={() => setVideoSource('youtube')}
                className={`flex items-center justify-center gap-2 rounded-xl py-2 px-3 text-xs font-bold transition ${
                  videoSource === 'youtube'
                    ? 'bg-primary text-white shadow-xs ring-2 ring-primary/20'
                    : 'border border-outline/50 bg-white text-secondary hover:bg-surface-low'
                }`}
              >
                <span className="text-base">▶</span>
                Link YouTube (HTTPS)
              </button>
              <button
                type="button"
                onClick={() => setVideoSource('file')}
                className={`flex items-center justify-center gap-2 rounded-xl py-2 px-3 text-xs font-bold transition ${
                  videoSource === 'file'
                    ? 'bg-primary text-white shadow-xs ring-2 ring-primary/20'
                    : 'border border-outline/50 bg-white text-secondary hover:bg-surface-low'
                }`}
              >
                <span className="text-base">📁</span>
                Unggah File Video (MP4)
              </button>
            </div>
          </div>
        )}

        {/* ── TinyMCE (teks) ── */}
        {isText && (
          <div>
            <label className="block text-xs font-semibold text-secondary">
              Isi Materi <span className="text-red-500">*</span>
              <span className="ml-2 font-normal text-muted">(Bold, Heading, Tabel, dsb.)</span>
            </label>
            <input type="hidden" name="content" defaultValue="" />
            <TinyEditor value={htmlContent} onChange={setHtmlContent} />
            {!htmlContent.trim() && (
              <p className="mt-1.5 text-[11px] text-amber-700">Isi materi wajib diisi minimal satu paragraf.</p>
            )}
          </div>
        )}

        {/* ── URL (link atau video YouTube) ── */}
        {needsUrl && (
          <div>
            <label htmlFor={`url-${sessionId}`} className="block text-xs font-semibold text-secondary">
              {type === 'link' ? (
                <>URL Tautan HTTPS <span className="text-red-500">*</span></>
              ) : (
                <>Link Video YouTube HTTPS <span className="text-red-500">*</span></>
              )}
            </label>
            <input
              id={`url-${sessionId}`}
              name="content"
              type="url"
              required
              maxLength="2000"
              value={urlInput}
              onChange={e => setUrlInput(e.target.value)}
              placeholder={type === 'link' ? 'https://contoh.com/halaman' : 'https://www.youtube.com/watch?v=... atau https://youtu.be/...'}
              className="mt-1.5 h-10 w-full rounded-xl border border-outline/60 bg-white px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
            />
            {type === 'video' && (
              <p className="mt-1 text-[11px] text-muted">Video akan langsung disematkan (embed) dari YouTube untuk student.</p>
            )}
          </div>
        )}

        {/* ── File upload (PDF, Image, Document, Audio, atau File Video) ── */}
        {needsFile && (
          <div>
            <label className="block text-xs font-semibold text-secondary">
              Unggah File {type === 'video' ? 'Video' : typeInfo?.label} <span className="text-red-500">*</span>
            </label>
            <div className="mt-1.5 rounded-xl border border-dashed border-outline/60 bg-surface-low/50 p-4 transition hover:border-primary/40">
              <div className="flex items-start gap-3">
                <span className="mt-0.5 text-2xl">
                  {type === 'pdf' ? '\u{1F4C4}' : type === 'image' ? '\u{1F5BC}' : type === 'video' ? '\u{1F3AC}' : type === 'audio' ? '\u{1F3B5}' : '\u{1F4CB}'}
                </span>
                <div className="flex-1">
                  <p className="text-xs font-semibold text-primary">Pilih atau seret file ke sini</p>
                  <p className="mt-0.5 text-[11px] text-muted">
                    {type === 'pdf' && 'Format: PDF'}
                    {type === 'image' && 'Format: PNG, JPG, WebP, GIF'}
                    {type === 'video' && 'Format: MP4, WebM, MOV'}
                    {type === 'audio' && 'Format: MP3, WAV, OGG, M4A'}
                    {type === 'document' && 'Format: Word, PowerPoint, Excel, ODF'}
                    {' · Maks. 50 MB'}
                  </p>
                  <input
                    type="file"
                    name="file"
                    required
                    accept={ACCEPT_MAP[type] || ''}
                    className="mt-2 block w-full text-xs text-secondary file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/15"
                  />
                </div>
              </div>
            </div>
            <p className="mt-1 text-[11px] text-muted">File disimpan di server lokal LMS.</p>
          </div>
        )}

        {/* ── Submit ── */}
        <div className="flex justify-end pt-1">
          <button
            type="submit"
            disabled={busy || (isText && !htmlContent.trim())}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:brightness-110 active:scale-95 disabled:opacity-50"
          >
            {busy ? (
              <>
                <span className="inline-block size-4 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                Mengirim…
              </>
            ) : (
              <>&#x2709; Kirim ke Antrean Persetujuan Admin</>
            )}
          </button>
        </div>
      </form>
    </div>
  );
}
