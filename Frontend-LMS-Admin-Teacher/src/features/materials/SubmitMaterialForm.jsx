import { useEffect, useRef, useState } from 'react';
import {
  File,
  FileBadge,
  FileText,
  Film,
  Headphones,
  Image,
  Link2,
  PlayCircle,
  Send,
  UploadCloud
} from 'lucide-react';
import Button from '../../components/Button';
import TinyEditor from '../../components/TinyEditor';
import showAlert from '../../utils/alert';

const MATERIAL_TYPES = [
  {
    value: 'text',
    label: 'Materi Teks',
    Icon: FileText,
    desc: 'Konten artikel/bacaan dengan format HTML (Bold, Heading, tabel, dll.)'
  },
  {
    value: 'video',
    label: 'Video YouTube / File',
    Icon: PlayCircle,
    desc: 'Link YouTube atau unggah file video (mp4, webm, mov)'
  },
  {
    value: 'pdf',
    label: 'Dokumen PDF',
    Icon: FileBadge,
    desc: 'Unggah file PDF'
  },
  {
    value: 'image',
    label: 'Gambar / Infografis',
    Icon: Image,
    desc: 'Unggah gambar (PNG, JPG, WebP, GIF)'
  },
  {
    value: 'document',
    label: 'Dokumen Office / ODF',
    Icon: File,
    desc: 'Unggah file Word, PowerPoint, Excel, atau ODF'
  },
  {
    value: 'audio',
    label: 'Audio',
    Icon: Headphones,
    desc: 'Unggah file audio (MP3, WAV, OGG, M4A)'
  },
  {
    value: 'link',
    label: 'Tautan HTTPS',
    Icon: Link2,
    desc: 'Masukkan URL tautan eksternal (harus HTTPS)'
  }
];

const ACCEPT_MAP = {
  pdf: '.pdf',
  image: '.png,.jpg,.jpeg,.gif,.webp',
  video: '.mp4,.webm,.mov,.ogv',
  audio: '.mp3,.wav,.ogg,.m4a',
  document: '.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.ods,.odp'
};

const FIELD_CLASS =
  'mt-1.5 h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Komponen form pengajuan materi baru yang dinamis di dalam kartu sesi.
 * TinyMCE digunakan untuk tipe "text".
 */
export default function SubmitMaterialForm({ sessionId, busy, onSubmit }) {
  const [type, setType] = useState('text');
  const [videoSource, setVideoSource] = useState('youtube');
  const [htmlContent, setHtmlContent] = useState('');
  const [urlInput, setUrlInput] = useState('');
  const formRef = useRef(null);

  const needsFile =
    ['pdf', 'image', 'document', 'audio'].includes(type) ||
    (type === 'video' && videoSource === 'file');
  const needsUrl = type === 'link' || (type === 'video' && videoSource === 'youtube');
  const isText = type === 'text';

  function handleSubmit(e) {
    e.preventDefault();
    if (isText && !htmlContent.trim()) {
      showAlert.warning(
        'Isi Materi Diperlukan',
        'Silakan ketikkan konten materi pembelajaran pada editor teks TinyMCE.'
      );
      return;
    }
    if (type === 'video') {
      if (videoSource === 'youtube') {
        let normalizedUrl = urlInput.trim();
        if (!normalizedUrl) {
          showAlert.warning(
            'Tautan YouTube Diperlukan',
            'Silakan masukkan URL YouTube yang valid (diawali https://).'
          );
          return;
        }
        if (!/^https?:\/\//i.test(normalizedUrl)) {
          normalizedUrl = 'https://' + normalizedUrl;
          setUrlInput(normalizedUrl);
        }
        try {
          const parsed = new URL(normalizedUrl);
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
          showAlert.warning(
            'Tautan Tidak Valid',
            'Silakan periksa kembali format URL YouTube Anda.'
          );
          return;
        }
      } else if (videoSource === 'file') {
        const fileInput = formRef.current?.querySelector('input[name="file"]');
        if (!fileInput?.files?.length) {
          showAlert.warning(
            'File Video Diperlukan',
            'Silakan pilih file video (MP4/WebM) dari perangkat Anda.'
          );
          return;
        }
      }
    }
    if (type === 'link') {
      let normalizedUrl = urlInput.trim();
      if (!normalizedUrl) {
        showAlert.warning(
          'Tautan URL Diperlukan',
          'Silakan masukkan tautan eksternal yang valid (diawali https://).'
        );
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

  const prevBusy = useRef(busy);
  useEffect(() => {
    if (prevBusy.current && !busy) {
      setHtmlContent('');
      setUrlInput('');
    }
    prevBusy.current = busy;
  }, [busy]);

  const typeInfo = MATERIAL_TYPES.find((t) => t.value === type) || MATERIAL_TYPES[0];
  const ActiveIcon = typeInfo.Icon;

  return (
    <div className="border-t border-outline/40 bg-surface-lowest px-5 py-5 dark:border-slate-800 dark:bg-slate-900">
      <div className="mb-4 flex items-center gap-3">
        <span className="grid size-9 place-items-center rounded-lg bg-primary/10 text-primary dark:bg-red-950/60 dark:text-red-300">
          <ActiveIcon className="size-4" />
        </span>
        <div>
          <h4 className="text-sm font-bold text-on-surface">Ajukan Materi Baru</h4>
          <p className="text-xs text-muted">Dikirim ke antrean persetujuan Admin setelah diajukan.</p>
        </div>
      </div>

      <form ref={formRef} onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label
            htmlFor={`title-${sessionId}`}
            className="block text-xs font-semibold text-on-surface-variant"
          >
            Judul Materi <span className="text-primary">*</span>
          </label>
          <input
            id={`title-${sessionId}`}
            name="title"
            required
            maxLength="200"
            placeholder="Contoh: Pengenalan Keselamatan Kerja"
            className={FIELD_CLASS}
          />
        </div>

        <div>
          <p className="mb-2 text-xs font-semibold text-on-surface-variant">
            Tipe Materi <span className="text-primary">*</span>
          </p>
          <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
            {MATERIAL_TYPES.map((t) => {
              const IconComp = t.Icon;
              const selected = type === t.value;
              return (
                <label
                  key={t.value}
                  className={[
                    'flex cursor-pointer select-none items-center gap-2 rounded-lg border p-2.5 text-xs transition-all',
                    selected
                      ? 'border-primary bg-primary/10 font-bold text-primary ring-1 ring-primary/30 dark:bg-red-950/40 dark:text-red-300'
                      : 'border-outline/50 bg-surface-lowest text-on-surface-variant hover:border-primary/40 hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300'
                  ].join(' ')}
                >
                  <input
                    type="radio"
                    name="type"
                    value={t.value}
                    checked={selected}
                    onChange={() => setType(t.value)}
                    className="sr-only"
                  />
                  <IconComp className="size-4 shrink-0" />
                  <span className="leading-tight">{t.label}</span>
                </label>
              );
            })}
          </div>
          {typeInfo && <p className="mt-2 text-[11px] text-muted">{typeInfo.desc}</p>}
        </div>

        {type === 'video' && (
          <div className="rounded-xl border border-primary/20 bg-primary/5 p-3.5 dark:border-primary/30 dark:bg-primary/10">
            <p className="mb-2 text-xs font-bold text-on-surface-variant">
              Pilih Sumber Video <span className="text-primary">*</span>
            </p>
            <div className="grid grid-cols-2 gap-2">
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => setVideoSource('youtube')}
                className={`flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition ${
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
                className={`flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition ${
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
            <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
              Isi Materi <span className="text-primary">*</span>{' '}
              <span className="font-normal text-muted">(Bold, Heading, Tabel, dsb.)</span>
            </label>
            <input type="hidden" name="content" defaultValue="" />
            <TinyEditor value={htmlContent} onChange={setHtmlContent} />
            {!htmlContent.trim() && (
              <p className="mt-1.5 text-[11px] text-amber-700 dark:text-amber-300">
                Isi materi wajib diisi minimal satu paragraf.
              </p>
            )}
          </div>
        )}

        {needsUrl && (
          <div>
            <label
              htmlFor={`url-${sessionId}`}
              className="block text-xs font-semibold text-on-surface-variant"
            >
              {type === 'link' ? 'URL Tautan HTTPS *' : 'Link Video YouTube HTTPS *'}
            </label>
            <input
              id={`url-${sessionId}`}
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
            {type === 'video' && (
              <p className="mt-1 text-[11px] text-muted">
                Video akan langsung disematkan (embed) dari YouTube untuk student.
              </p>
            )}
          </div>
        )}

        {needsFile && (
          <div>
            <label className="block text-xs font-semibold text-on-surface-variant">
              Unggah File {type === 'video' ? 'Video' : typeInfo?.label}{' '}
              <span className="text-primary">*</span>
            </label>
            <div className="mt-1.5 rounded-xl border border-dashed border-outline bg-surface-low/50 p-4 transition hover:border-primary/50 dark:border-slate-700 dark:bg-slate-800/40">
              <div className="flex items-start gap-3">
                <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary">
                  <UploadCloud className="size-5" />
                </span>
                <div className="flex-1">
                  <p className="text-xs font-semibold text-primary">Pilih atau unggah berkas</p>
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
                    className="mt-2 block w-full text-xs text-on-surface-variant file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/20"
                  />
                </div>
              </div>
            </div>
            <p className="mt-1 text-[11px] text-muted">Berkas disimpan aman di server LMS.</p>
          </div>
        )}

        <div className="flex justify-end pt-1">
          <Button
            type="submit"
            variant="primary"
            loading={busy}
            disabled={busy || (isText && !htmlContent.trim())}
            icon={<Send className="size-4" />}
          >
            Kirim ke Antrean Persetujuan Admin
          </Button>
        </div>
      </form>
    </div>
  );
}
