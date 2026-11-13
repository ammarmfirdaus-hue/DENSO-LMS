import { useEffect, useRef, useState } from 'react';
import {
  CheckCircle2,
  File,
  FileBadge,
  FilePlus2,
  FileText,
  Film,
  Headphones,
  Image,
  Link2,
  PlayCircle,
  PlusCircle,
  Send,
  UploadCloud,
  X
} from 'lucide-react';
import { api } from '../../api/client';
import Button from '../../components/Button';
import TinyEditor from '../../components/TinyEditor';
import showAlert from '../../utils/alert';

const MATERIAL_TYPES = [
  { value: 'text', label: 'Materi Teks', Icon: FileText },
  { value: 'video', label: 'Video / YouTube', Icon: PlayCircle },
  { value: 'pdf', label: 'Dokumen PDF', Icon: FileBadge },
  { value: 'image', label: 'Gambar', Icon: Image },
  { value: 'document', label: 'Dokumen Office', Icon: File },
  { value: 'audio', label: 'Audio', Icon: Headphones },
  { value: 'link', label: 'Tautan HTTPS', Icon: Link2 }
];

const ACCEPT_MAP = {
  pdf: '.pdf',
  image: '.png,.jpg,.jpeg,.gif,.webp',
  video: '.mp4,.webm,.mov,.ogv',
  audio: '.mp3,.wav,.ogg,.m4a',
  document: '.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.ods,.odp'
};

const FIELD_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-xs font-medium text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Modal untuk menambahkan & mengajukan satu atau lebih materi baru ke antrean persetujuan Admin.
 */
export default function SubmitMaterialModal({
  onClose,
  onSuccess,
  initialCourseId = '',
  initialSessionId = ''
}) {
  const [courses, setCourses] = useState([]);
  const [loadingCourses, setLoadingCourses] = useState(true);
  const [selectedCourseId, setSelectedCourseId] = useState(
    initialCourseId ? String(initialCourseId) : ''
  );

  const [sessions, setSessions] = useState([]);
  const [loadingSessions, setLoadingSessions] = useState(false);
  const [selectedSessionId, setSelectedSessionId] = useState(
    initialSessionId ? String(initialSessionId) : ''
  );

  const [showNewSessionInput, setShowNewSessionInput] = useState(false);
  const [newSessionTitle, setNewSessionTitle] = useState('');
  const [creatingSession, setCreatingSession] = useState(false);

  const [title, setTitle] = useState('');
  const [type, setType] = useState('text');
  const [videoSource, setVideoSource] = useState('youtube');
  const [htmlContent, setHtmlContent] = useState('');
  const [urlInput, setUrlInput] = useState('');
  const [file, setFile] = useState(null);

  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [submittedCount, setSubmittedCount] = useState(0);
  const [keepOpenAfterSave, setKeepOpenAfterSave] = useState(false);

  const fileInputRef = useRef(null);
  const titleInputRef = useRef(null);

  useEffect(() => {
    let active = true;
    async function loadCourses() {
      try {
        setLoadingCourses(true);
        const res = await api('/courses');
        if (active) {
          const list = res.courses || [];
          setCourses(list);
          if (initialCourseId) {
            setSelectedCourseId(String(initialCourseId));
          } else if (list.length === 1) {
            setSelectedCourseId(String(list[0].course_id));
          }
        }
      } catch (err) {
        if (active) {
          const msg = err.message || 'Gagal memuat daftar course.';
          setError(msg);
          showAlert.error('Gagal Memuat Course', msg);
        }
      } finally {
        if (active) setLoadingCourses(false);
      }
    }
    loadCourses();
    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    if (!selectedCourseId) {
      setSessions([]);
      setSelectedSessionId('');
      return;
    }
    let active = true;
    async function loadSessions() {
      try {
        setLoadingSessions(true);
        const res = await api(`/courses/${selectedCourseId}/detail`);
        if (active) {
          const sessList = res.sessions || [];
          setSessions(sessList);
          if (
            initialSessionId &&
            String(selectedCourseId) === String(initialCourseId) &&
            sessList.some((s) => String(s.session_id) === String(initialSessionId))
          ) {
            setSelectedSessionId(String(initialSessionId));
            setShowNewSessionInput(false);
          } else if (sessList.length > 0) {
            setSelectedSessionId(String(sessList[0].session_id));
            setShowNewSessionInput(false);
          } else {
            setSelectedSessionId('');
            setShowNewSessionInput(true);
          }
        }
      } catch (err) {
        if (active) {
          const msg = err.message || 'Gagal memuat sesi course.';
          setError(msg);
          showAlert.error('Gagal Memuat Sesi', msg);
        }
      } finally {
        if (active) setLoadingSessions(false);
      }
    }
    loadSessions();
    return () => {
      active = false;
    };
  }, [selectedCourseId]);

  async function handleCreateSession(e) {
    e.preventDefault();
    if (!selectedCourseId || !newSessionTitle.trim()) return;
    setCreatingSession(true);
    try {
      await api(`/courses/${selectedCourseId}/sessions`, {
        method: 'POST',
        body: JSON.stringify({ title: newSessionTitle.trim() })
      });
      const res = await api(`/courses/${selectedCourseId}/detail`);
      const sessList = res.sessions || [];
      setSessions(sessList);
      const created =
        sessList.find((s) => s.title === newSessionTitle.trim()) || sessList[sessList.length - 1];
      if (created) setSelectedSessionId(String(created.session_id));
      setNewSessionTitle('');
      setShowNewSessionInput(false);
      showAlert.success('Sesi Berhasil Dibuat', 'Sesi pembelajaran baru telah ditambahkan ke course.');
    } catch (err) {
      showAlert.error('Gagal Membuat Sesi', err.message);
    } finally {
      setCreatingSession(false);
    }
  }

  const isText = type === 'text';
  const needsUrl = type === 'link' || (type === 'video' && videoSource === 'youtube');
  const needsFile =
    ['pdf', 'image', 'document', 'audio'].includes(type) ||
    (type === 'video' && videoSource === 'file');

  async function handleSubmit(e, continueAdding = false) {
    e.preventDefault();
    if (!selectedCourseId) {
      showAlert.warning('Course Belum Dipilih', 'Silakan pilih course terlebih dahulu.');
      return;
    }
    if (!selectedSessionId) {
      showAlert.warning('Sesi Belum Dipilih', 'Silakan pilih atau buat sesi pembelajaran terlebih dahulu.');
      return;
    }
    const trimmedTitle = title.trim();
    if (!trimmedTitle) {
      showAlert.warning('Judul Wajib Diisi', 'Silakan masukkan judul materi pembelajaran.');
      titleInputRef.current?.focus();
      return;
    }

    if (isText && !htmlContent.trim()) {
      showAlert.warning(
        'Isi Materi Diperlukan',
        'Silakan ketikkan konten materi pada editor teks TinyMCE.'
      );
      return;
    }

    let finalContent = isText ? htmlContent : '';

    if (type === 'video') {
      if (videoSource === 'youtube') {
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
      } else if (videoSource === 'file' && !file) {
        showAlert.warning('File Video Diperlukan', 'Silakan pilih file video (MP4/WebM) dari komputer.');
        return;
      }
    }

    if (type === 'link') {
      let normalized = urlInput.trim();
      if (!normalized) {
        showAlert.warning('Tautan URL Diperlukan', 'Silakan masukkan tautan web yang valid.');
        return;
      }
      if (!/^https?:\/\//i.test(normalized)) {
        normalized = 'https://' + normalized;
        setUrlInput(normalized);
      }
      finalContent = normalized;
    }

    if (['pdf', 'image', 'document', 'audio'].includes(type) && !file) {
      showAlert.warning('File Diperlukan', `Silakan unggah file ${type.toUpperCase()} materi.`);
      return;
    }

    setBusy(true);
    setError('');

    try {
      const formData = new FormData();
      formData.set('title', trimmedTitle);
      formData.set('type', type);
      formData.set('content', finalContent);
      formData.set('sortOrder', '0');
      if (needsFile && file) {
        formData.set('file', file);
      }

      await api(`/material-workflow/sessions/${selectedSessionId}/submissions`, {
        method: 'POST',
        body: formData
      });

      const nextCount = submittedCount + 1;
      setSubmittedCount(nextCount);
      onSuccess?.();

      if (continueAdding || keepOpenAfterSave) {
        setTitle('');
        setHtmlContent('');
        setUrlInput('');
        setFile(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
        setNotice(
          `Materi "${trimmedTitle}" berhasil diajukan (${nextCount} materi tersimpan). Form siap untuk materi berikutnya.`
        );
        showAlert.success(
          'Materi Berhasil Diajukan',
          `"${trimmedTitle}" telah diajukan. Silakan isi materi berikutnya.`
        );
        setTimeout(() => {
          titleInputRef.current?.focus();
        }, 100);
      } else {
        showAlert.success(
          'Materi Berhasil Diajukan',
          `Materi "${trimmedTitle}" telah berhasil dikirimkan ke antrean persetujuan Admin.`
        );
        onClose();
      }
    } catch (err) {
      setError(err.message || 'Gagal mengajukan materi.');
      showAlert.error('Gagal Mengajukan Materi', err.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div
      className="fixed inset-0 z-50"
      role="dialog"
      aria-modal="true"
      aria-label="Tambah materi pembelajaran"
    >
      <div
        className="absolute inset-0 bg-slate-950/60 backdrop-blur-xs"
        onClick={onClose}
        aria-hidden="true"
      />

      <div className="dls-card-surface absolute inset-x-0 bottom-0 flex max-h-[92dvh] w-full flex-col overflow-hidden rounded-t-2xl shadow-2xl sm:inset-0 sm:m-auto sm:max-h-[90dvh] sm:max-w-5xl sm:rounded-xl lg:max-w-6xl">
        <div className="flex shrink-0 justify-center pb-1 pt-2.5 sm:hidden" aria-hidden="true">
          <div className="h-1 w-10 rounded-full bg-outline" />
        </div>

        {/* Header */}
        <div className="flex shrink-0 items-center justify-between border-b border-outline/40 bg-surface-low/60 px-4 py-3 sm:px-6 sm:py-4 dark:border-slate-800 dark:bg-slate-800/60">
          <div className="flex items-center gap-3.5">
            <span className="grid size-10 place-items-center rounded-xl bg-primary/10 text-primary dark:bg-red-950/60 dark:text-red-300">
              <FilePlus2 className="size-5" />
            </span>
            <div>
              <div className="flex flex-wrap items-center gap-2.5">
                <h3 className="text-base font-bold text-on-surface sm:text-lg">
                  Tambah &amp; Ajukan Materi Pembelajaran
                </h3>
                {submittedCount > 0 && (
                  <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    <CheckCircle2 className="size-3.5" />
                    {submittedCount} materi tersimpan
                  </span>
                )}
              </div>
              <p className="text-xs text-muted">
                Pilih course &amp; sesi untuk mengajukan satu atau beberapa materi ke antrean persetujuan Admin.
              </p>
            </div>
          </div>

          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            className="grid size-8 place-items-center rounded-lg text-muted transition hover:bg-surface-container hover:text-on-surface dark:hover:bg-slate-700"
            aria-label="Tutup modal"
          >
            <X className="size-4" />
          </button>
        </div>

        {/* Body */}
        <div className="flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">
          {notice && (
            <div className="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
              <div className="flex min-w-0 items-center gap-2">
                <CheckCircle2 className="size-4 shrink-0 text-emerald-600" />
                <span className="break-words font-semibold">{notice}</span>
              </div>
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => setNotice('')}
                className="shrink-0 rounded-md px-2 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50"
              >
                Tutup
              </button>
            </div>
          )}

          {error && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
              {error}
            </div>
          )}

          {/* Pilih Course & Sesi */}
          <div className="rounded-xl border border-outline/40 bg-surface-low/50 p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-800/40">
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
                  Pilih Course Pembelajaran <span className="text-primary">*</span>
                </label>
                <select
                  value={selectedCourseId}
                  onChange={(e) => setSelectedCourseId(e.target.value)}
                  disabled={loadingCourses || busy}
                  className={FIELD_CLASS}
                >
                  <option value="">
                    {loadingCourses ? 'Memuat daftar course...' : '-- Pilih Course Pembelajaran --'}
                  </option>
                  {courses.map((c) => (
                    <option key={c.course_id} value={c.course_id}>
                      [{c.course_code}] {c.title}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <div className="mb-1.5 flex items-center justify-between">
                  <label className="block text-xs font-bold text-on-surface-variant">
                    Pilih Sesi Pembelajaran <span className="text-primary">*</span>
                  </label>
                  {selectedCourseId && !showNewSessionInput && (
                    <button
                      type="button"
                      data-dls-action-ignore="true"
                      onClick={() => setShowNewSessionInput(true)}
                      className="text-[11px] font-bold text-primary hover:underline"
                    >
                      + Buat Sesi Baru
                    </button>
                  )}
                </div>

                {!selectedCourseId ? (
                  <div className="flex h-10 items-center rounded-md border border-dashed border-outline bg-surface-lowest px-3 text-xs text-muted dark:border-slate-700 dark:bg-slate-900">
                    Pilih course terlebih dahulu
                  </div>
                ) : !showNewSessionInput ? (
                  <select
                    value={selectedSessionId}
                    onChange={(e) => setSelectedSessionId(e.target.value)}
                    disabled={loadingSessions || busy}
                    className={FIELD_CLASS}
                  >
                    <option value="">
                      {loadingSessions ? 'Memuat sesi...' : '-- Pilih Sesi Pembelajaran --'}
                    </option>
                    {sessions.map((s) => (
                      <option key={s.session_id} value={s.session_id}>
                        {s.title}
                      </option>
                    ))}
                  </select>
                ) : (
                  <div className="flex gap-2">
                    <input
                      type="text"
                      placeholder="Nama sesi baru, misal: Sesi 1 - Pengantar Kualitas"
                      value={newSessionTitle}
                      onChange={(e) => setNewSessionTitle(e.target.value)}
                      maxLength={200}
                      className={FIELD_CLASS}
                    />
                    <Button
                      type="button"
                      size="sm"
                      variant="secondary"
                      disabled={creatingSession || !newSessionTitle.trim()}
                      loading={creatingSession}
                      onClick={handleCreateSession}
                    >
                      Simpan Sesi
                    </Button>
                    {sessions.length > 0 && (
                      <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        onClick={() => setShowNewSessionInput(false)}
                      >
                        Batal
                      </Button>
                    )}
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Judul Materi */}
          <div>
            <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
              Judul Materi Pembelajaran <span className="text-primary">*</span>
            </label>
            <input
              ref={titleInputRef}
              type="text"
              required
              maxLength={200}
              placeholder="Contoh: Pengenalan Standar Kualitas 5S di Area Produksi"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="h-10 w-full rounded-md border border-outline bg-surface-lowest px-3.5 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
          </div>

          {/* Pilihan Tipe Materi */}
          <div>
            <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
              Pilih Tipe Materi <span className="text-primary">*</span>
            </label>
            <div className="grid grid-cols-2 gap-2.5 sm:grid-cols-4 lg:grid-cols-7">
              {MATERIAL_TYPES.map((t) => {
                const isSelected = type === t.value;
                const IconComp = t.Icon;
                return (
                  <button
                    key={t.value}
                    type="button"
                    data-dls-action-ignore="true"
                    onClick={() => setType(t.value)}
                    className={`flex flex-col items-center justify-center gap-1.5 rounded-lg border p-3 text-center text-xs transition ${
                      isSelected
                        ? 'border-primary bg-primary/10 font-bold text-primary ring-2 ring-primary/20 dark:bg-red-950/40 dark:text-red-300'
                        : 'border-outline/50 bg-surface-lowest text-on-surface-variant hover:border-outline-strong hover:bg-surface-low dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                    }`}
                  >
                    <IconComp className="size-4 shrink-0" />
                    <span className="line-clamp-2 text-[11px] leading-tight">{t.label}</span>
                  </button>
                );
              })}
            </div>
          </div>

          {/* Sumber Video */}
          {type === 'video' && (
            <div className="rounded-xl border border-primary/20 bg-primary/5 p-3.5 dark:border-primary/30 dark:bg-primary/10">
              <p className="mb-2 text-xs font-bold text-on-surface-variant">
                Pilih Sumber Video <span className="text-primary">*</span>
              </p>
              <div className="grid max-w-md grid-cols-2 gap-2.5">
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

          {/* Editor Teks */}
          {isText && (
            <div>
              <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
                Isi Materi Teks Pembelajaran <span className="text-primary">*</span>
                <span className="ml-1 font-normal text-muted">
                  (Mendukung heading, tabel, format teks, dan gambar)
                </span>
              </label>
              <div className="min-h-[300px]">
                <TinyEditor value={htmlContent} onChange={setHtmlContent} />
              </div>
            </div>
          )}

          {/* Input URL */}
          {needsUrl && (
            <div>
              <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
                {type === 'link' ? 'URL Tautan HTTPS *' : 'Link Video YouTube HTTPS *'}
              </label>
              <input
                type="url"
                required
                maxLength={2000}
                value={urlInput}
                onChange={(e) => setUrlInput(e.target.value)}
                placeholder={
                  type === 'link'
                    ? 'https://contoh.com/materi'
                    : 'https://www.youtube.com/watch?v=... atau https://youtu.be/...'
                }
                className={FIELD_CLASS}
              />
              <p className="mt-1 text-[11px] text-muted">
                {type === 'video'
                  ? 'Video YouTube akan disematkan langsung untuk dapat diputar oleh student.'
                  : 'Tautan eksternal yang dapat diakses langsung oleh student.'}
              </p>
            </div>
          )}

          {/* Input Upload File */}
          {needsFile && (
            <div>
              <label className="mb-1.5 block text-xs font-bold text-on-surface-variant">
                Unggah File {type === 'video' ? 'Video (MP4/WebM)' : type.toUpperCase()}{' '}
                <span className="text-primary">*</span>
              </label>
              <div
                onClick={() => fileInputRef.current?.click()}
                className="flex cursor-pointer items-center gap-3.5 rounded-xl border-2 border-dashed border-outline bg-surface-low/50 p-5 transition hover:border-primary/60 dark:border-slate-700 dark:bg-slate-800/40"
              >
                <span className="grid size-12 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                  <UploadCloud className="size-6" />
                </span>
                <div className="min-w-0 flex-1">
                  <p className="text-xs font-bold text-on-surface sm:text-sm">
                    {file ? file.name : 'Klik untuk memilih file materi'}
                  </p>
                  <p className="mt-0.5 text-[11px] text-muted">
                    {file
                      ? `${(file.size / 1024 / 1024).toFixed(2)} MB`
                      : `Format didukung: ${ACCEPT_MAP[type] || '*'} · Maksimal 50 MB`}
                  </p>
                </div>
                <input
                  ref={fileInputRef}
                  type="file"
                  accept={ACCEPT_MAP[type] || ''}
                  onChange={(e) => setFile(e.target.files?.[0] || null)}
                  className="hidden"
                />
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="flex shrink-0 flex-col-reverse items-stretch gap-2.5 border-t border-outline/40 bg-surface-low/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:px-6 sm:py-4 dark:border-slate-800 dark:bg-slate-800/60">
          <label className="inline-flex cursor-pointer select-none items-center gap-2 text-xs font-semibold text-on-surface-variant">
            <input
              type="checkbox"
              checked={keepOpenAfterSave}
              onChange={(e) => setKeepOpenAfterSave(e.target.checked)}
              className="size-4 rounded accent-primary"
            />
            <span>Tetap buka modal untuk isi materi berikutnya</span>
          </label>

          <div className="flex flex-wrap items-center justify-end gap-2">
            <Button type="button" variant="outline" size="sm" onClick={onClose} disabled={busy}>
              {submittedCount > 0 ? 'Selesai & Tutup' : 'Batal'}
            </Button>

            <Button
              type="button"
              variant="secondary"
              size="sm"
              disabled={busy || !selectedCourseId || !selectedSessionId || !title.trim()}
              icon={<PlusCircle className="size-4" />}
              onClick={(e) => handleSubmit(e, true)}
              title="Simpan materi ini dan bersihkan form untuk langsung menambah materi baru"
            >
              Simpan &amp; Tambah Lagi
            </Button>

            <Button
              type="button"
              variant="primary"
              size="sm"
              loading={busy}
              disabled={busy || !selectedCourseId || !selectedSessionId || !title.trim()}
              icon={<Send className="size-4" />}
              onClick={(e) => handleSubmit(e, false)}
            >
              Simpan &amp; Selesai
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}
