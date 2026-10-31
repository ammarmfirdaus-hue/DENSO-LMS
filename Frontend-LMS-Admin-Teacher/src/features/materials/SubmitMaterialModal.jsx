import { useEffect, useRef, useState } from 'react';
import { api } from '../../api/client';
import TinyEditor from '../../components/TinyEditor';
import showAlert from '../../utils/alert';

const MATERIAL_TYPES = [
  { value: 'text',     label: 'Materi Teks',           icon: 'article',     desc: 'Artikel / modul teks dengan format TinyMCE' },
  { value: 'video',    label: 'Video YouTube / File',  icon: 'play_circle', desc: 'Link YouTube atau unggah file video (MP4/WebM)' },
  { value: 'pdf',      label: 'Dokumen PDF',           icon: 'picture_as_pdf', desc: 'Unggah file PDF materi' },
  { value: 'image',    label: 'Gambar / Infografis',   icon: 'image',       desc: 'Unggah gambar (PNG, JPG, WebP, GIF)' },
  { value: 'document', label: 'Dokumen Office / ODF',  icon: 'description', desc: 'Unggah file Word, Excel, PowerPoint, atau ODF' },
  { value: 'audio',    label: 'Audio',                 icon: 'audiotrack',  desc: 'Unggah file audio (MP3, WAV, OGG, M4A)' },
  { value: 'link',     label: 'Tautan HTTPS',          icon: 'link',        desc: 'Tautan eksternal / referensi web' },
];

const ACCEPT_MAP = {
  pdf: '.pdf',
  image: '.png,.jpg,.jpeg,.gif,.webp',
  video: '.mp4,.webm,.mov,.ogv',
  audio: '.mp3,.wav,.ogg,.m4a',
  document: '.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.ods,.odp',
};

export default function SubmitMaterialModal({ onClose, onSuccess }) {
  const [courses, setCourses] = useState([]);
  const [loadingCourses, setLoadingCourses] = useState(true);
  const [selectedCourseId, setSelectedCourseId] = useState('');
  
  const [sessions, setSessions] = useState([]);
  const [loadingSessions, setLoadingSessions] = useState(false);
  const [selectedSessionId, setSelectedSessionId] = useState('');
  
  // New session inline creation
  const [showNewSessionInput, setShowNewSessionInput] = useState(false);
  const [newSessionTitle, setNewSessionTitle] = useState('');
  const [creatingSession, setCreatingSession] = useState(false);

  // Material Form State
  const [title, setTitle] = useState('');
  const [type, setType] = useState('text');
  const [videoSource, setVideoSource] = useState('youtube'); // 'youtube' | 'file'
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

  // Load active courses on mount
  useEffect(() => {
    let active = true;
    async function loadCourses() {
      try {
        setLoadingCourses(true);
        const res = await api('/courses');
        if (active) {
          const list = res.courses || [];
          setCourses(list);
          if (list.length === 1) {
            setSelectedCourseId(String(list[0].course_id));
          }
        }
      } catch (err) {
        if (active) setError(err.message || 'Gagal memuat daftar course.');
      } finally {
        if (active) setLoadingCourses(false);
      }
    }
    loadCourses();
    return () => { active = false; };
  }, []);

  // When course changes, load its sessions
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
          if (sessList.length > 0) {
            setSelectedSessionId(String(sessList[0].session_id));
            setShowNewSessionInput(false);
          } else {
            setSelectedSessionId('');
            setShowNewSessionInput(true);
          }
        }
      } catch (err) {
        if (active) setError(err.message || 'Gagal memuat sesi course.');
      } finally {
        if (active) setLoadingSessions(false);
      }
    }
    loadSessions();
    return () => { active = false; };
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
      // reload sessions
      const res = await api(`/courses/${selectedCourseId}/detail`);
      const sessList = res.sessions || [];
      setSessions(sessList);
      const created = sessList.find(s => s.title === newSessionTitle.trim()) || sessList[sessList.length - 1];
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
  const needsFile = ['pdf', 'image', 'document', 'audio'].includes(type) || (type === 'video' && videoSource === 'file');

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
      showAlert.warning('Isi Materi Diperlukan', 'Silakan ketikkan konten materi pada editor teks TinyMCE.');
      return;
    }

    let finalContent = isText ? htmlContent : '';

    if (type === 'video') {
      if (videoSource === 'youtube') {
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
      } else if (videoSource === 'file') {
        if (!file) {
          showAlert.warning('File Video Diperlukan', 'Silakan pilih file video (MP4/WebM) dari komputer.');
          return;
        }
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
        body: formData,
      });

      const nextCount = submittedCount + 1;
      setSubmittedCount(nextCount);
      onSuccess?.();

      if (continueAdding || keepOpenAfterSave) {
        // Reset only the material fields, keep selected course and session intact
        setTitle('');
        setHtmlContent('');
        setUrlInput('');
        setFile(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
        setNotice(`Materi "${trimmedTitle}" berhasil diajukan (${nextCount} materi tersimpan). Form telah siap untuk materi berikutnya.`);
        showAlert.success('Materi Berhasil Diajukan', `"${trimmedTitle}" telah diajukan. Silakan isi materi berikutnya.`);
        setTimeout(() => {
          titleInputRef.current?.focus();
        }, 100);
      } else {
        showAlert.success('Materi Berhasil Diajukan', `Materi "${trimmedTitle}" telah berhasil dikirimkan ke antrean persetujuan Admin.`);
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
    <div className="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Tambah materi pembelajaran">
      {/* Backdrop — closes modal when clicked */}
      <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onClick={onClose} aria-hidden="true" />

      {/*
        Mobile  → bottom sheet (rounded-t-3xl, slides from bottom, max-h 92dvh)
        Desktop → centered modal (sm:rounded-3xl, sm:max-w-5xl, max-h 90dvh)
      */}
      <div className="absolute inset-x-0 bottom-0 flex max-h-[92dvh] w-full flex-col rounded-t-3xl border-t border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:inset-0 sm:m-auto sm:max-h-[90dvh] sm:max-w-5xl sm:rounded-3xl sm:border lg:max-w-6xl xl:max-w-7xl">
        
        {/* Drag handle pill — visible only on mobile (hidden on sm+) */}
        <div className="flex shrink-0 justify-center pt-2.5 pb-1 sm:hidden" aria-hidden="true">
          <div className="h-1 w-10 rounded-full bg-slate-300 dark:bg-slate-600" />
        </div>

        {/* Header Modal */}
        <div className="flex shrink-0 items-center justify-between border-b border-slate-200 dark:border-slate-800 px-4 py-3 sm:px-6 sm:py-4 bg-slate-50/70 dark:bg-slate-900">
          <div className="flex items-center gap-3.5">
            <span className="grid size-11 place-items-center rounded-2xl bg-primary/10 text-primary shadow-xs">
              <span className="material-symbol text-2xl">post_add</span>
            </span>
            <div>
              <div className="flex flex-wrap items-center gap-2.5">
                <h3 className="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">
                  Tambah &amp; Ajukan Materi Pembelajaran
                </h3>
                {submittedCount > 0 && (
                  <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    <span className="material-symbol text-xs">check_circle</span>
                    {submittedCount} materi tersimpan
                  </span>
                )}
              </div>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                Pilih course &amp; sesi untuk menambahkan satu atau beberapa materi ke antrean persetujuan Admin
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="grid size-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition cursor-pointer"
            aria-label="Tutup modal"
          >
            <span className="material-symbol text-xl">close</span>
          </button>
        </div>

        {/* Body — scrollable content area */}
        <div className="flex-1 overflow-y-auto px-4 py-4 space-y-5 sm:px-6 sm:py-5">
          {notice && (
            <div className="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/90 p-3.5 text-xs text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200 shadow-xs animate-fade-in">
              <div className="flex items-center gap-2.5 min-w-0">
                <span className="material-symbol text-emerald-600 text-lg shrink-0">check_circle</span>
                <span className="font-semibold break-words">{notice}</span>
              </div>
              <button
                type="button"
                onClick={() => setNotice('')}
                className="shrink-0 rounded-lg text-emerald-700 hover:bg-emerald-100 px-2 py-1 font-bold text-xs cursor-pointer dark:hover:bg-emerald-900"
              >
                Tutup
              </button>
            </div>
          )}

          {error && (
            <div className="rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs font-semibold text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
              {error}
            </div>
          )}

          {/* 1. Pilih Course & Sesi - Grid 2 Kolom di Desktop */}
          <div className="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-800/40">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
                  Pilih Course Pembelajaran <span className="text-primary">*</span>
                </label>
                <select
                  value={selectedCourseId}
                  onChange={e => setSelectedCourseId(e.target.value)}
                  disabled={loadingCourses || busy}
                  className="mt-1.5 h-10.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs font-medium text-slate-800 outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 disabled:opacity-50"
                >
                  <option value="">{loadingCourses ? 'Memuat daftar course...' : '-- Pilih Course Pembelajaran --'}</option>
                  {courses.map(c => (
                    <option key={c.course_id} value={c.course_id}>
                      [{c.course_code}] {c.title}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <div className="flex items-center justify-between">
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Pilih Sesi Pembelajaran <span className="text-primary">*</span>
                  </label>
                  {selectedCourseId && !showNewSessionInput && (
                    <button
                      type="button"
                      onClick={() => setShowNewSessionInput(true)}
                      className="text-[11px] font-bold text-primary hover:underline cursor-pointer"
                    >
                      + Buat Sesi Baru
                    </button>
                  )}
                </div>

                {!selectedCourseId ? (
                  <div className="mt-1.5 flex h-10.5 items-center rounded-xl border border-dashed border-slate-300 bg-white/60 px-3 text-xs text-slate-400 dark:border-slate-700 dark:bg-slate-900">
                    Pilih course terlebih dahulu
                  </div>
                ) : !showNewSessionInput ? (
                  <select
                    value={selectedSessionId}
                    onChange={e => setSelectedSessionId(e.target.value)}
                    disabled={loadingSessions || busy}
                    className="mt-1.5 h-10.5 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs font-medium text-slate-800 outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 disabled:opacity-50"
                  >
                    <option value="">{loadingSessions ? 'Memuat sesi...' : '-- Pilih Sesi Pembelajaran --'}</option>
                    {sessions.map(s => (
                      <option key={s.session_id} value={s.session_id}>
                        {s.title}
                      </option>
                    ))}
                  </select>
                ) : (
                  <div className="mt-1.5 flex gap-2">
                    <input
                      type="text"
                      placeholder="Nama sesi baru, misal: Sesi 1 - Pengantar Kualitas"
                      value={newSessionTitle}
                      onChange={e => setNewSessionTitle(e.target.value)}
                      maxLength={200}
                      className="h-10.5 flex-1 rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    />
                    <button
                      type="button"
                      disabled={creatingSession || !newSessionTitle.trim()}
                      onClick={handleCreateSession}
                      className="rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-800 disabled:opacity-50 cursor-pointer"
                    >
                      {creatingSession ? 'Menyimpan...' : 'Simpan Sesi'}
                    </button>
                    {sessions.length > 0 && (
                      <button
                        type="button"
                        onClick={() => setShowNewSessionInput(false)}
                        className="rounded-xl border border-slate-300 px-2.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 cursor-pointer"
                      >
                        Batal
                      </button>
                    )}
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* 2. Judul Materi */}
          <div>
            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Judul Materi Pembelajaran <span className="text-primary">*</span>
            </label>
            <input
              ref={titleInputRef}
              type="text"
              required
              maxLength={200}
              placeholder="Contoh: Pengenalan Standar Kualitas 5S di Area Produksi"
              value={title}
              onChange={e => setTitle(e.target.value)}
              className="mt-1.5 h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-800 outline-none transition focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            />
          </div>

          {/* 3. Tipe Materi Grid Responsif Luas */}
          <div>
            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
              Pilih Tipe Materi <span className="text-primary">*</span>
            </label>
            <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
              {MATERIAL_TYPES.map(t => {
                const isSelected = type === t.value;
                return (
                  <button
                    key={t.value}
                    type="button"
                    onClick={() => setType(t.value)}
                    className={`flex flex-col items-center justify-center gap-1.5 rounded-xl p-3 text-center text-xs transition cursor-pointer border ${
                      isSelected
                        ? 'border-primary bg-primary/10 text-primary font-bold ring-2 ring-primary/20'
                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300'
                    }`}
                  >
                    <span className="material-symbol text-xl shrink-0">{t.icon}</span>
                    <span className="text-[11px] leading-tight line-clamp-2">{t.label}</span>
                  </button>
                );
              })}
            </div>
          </div>

          {/* 4. Tipe Video: Sumber Video (YouTube / MP4) */}
          {type === 'video' && (
            <div className="rounded-xl border border-primary/20 bg-primary/5 p-3.5 dark:border-primary/30 dark:bg-primary/10">
              <p className="mb-2 text-xs font-bold text-slate-700 dark:text-slate-300">
                Pilih Sumber Video <span className="text-primary">*</span>
              </p>
              <div className="grid grid-cols-2 gap-2.5 max-w-md">
                <button
                  type="button"
                  onClick={() => setVideoSource('youtube')}
                  className={`flex items-center justify-center gap-2 rounded-xl py-2 px-3 text-xs font-bold transition cursor-pointer ${
                    videoSource === 'youtube'
                      ? 'bg-primary text-white shadow-xs'
                      : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
                  }`}
                >
                  <span className="material-symbol text-sm">smart_display</span>
                  Link YouTube (HTTPS)
                </button>
                <button
                  type="button"
                  onClick={() => setVideoSource('file')}
                  className={`flex items-center justify-center gap-2 rounded-xl py-2 px-3 text-xs font-bold transition cursor-pointer ${
                    videoSource === 'file'
                      ? 'bg-primary text-white shadow-xs'
                      : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
                  }`}
                >
                  <span className="material-symbol text-sm">movie</span>
                  Unggah File Video (MP4)
                </button>
              </div>
            </div>
          )}

          {/* 5. Konten Teks TinyMCE Luas */}
          {isText && (
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                Isi Materi Teks Pembelajaran <span className="text-primary">*</span>
                <span className="ml-1 text-[11px] font-normal text-slate-500">(Gunakan TinyMCE untuk heading, tabel, format teks, dan gambar)</span>
              </label>
              <div className="min-h-[300px]">
                <TinyEditor value={htmlContent} onChange={setHtmlContent} />
              </div>
            </div>
          )}

          {/* 6. URL Input (Link atau Video YouTube) */}
          {needsUrl && (
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
                {type === 'link' ? 'URL Tautan HTTPS *' : 'Link Video YouTube HTTPS *'}
              </label>
              <input
                type="url"
                required
                maxLength={2000}
                value={urlInput}
                onChange={e => setUrlInput(e.target.value)}
                placeholder={type === 'link' ? 'https://contoh.com/materi' : 'https://www.youtube.com/watch?v=... atau https://youtu.be/...'}
                className="mt-1.5 h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-800 outline-none transition focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
              />
              <p className="mt-1 text-[11px] text-slate-500">
                {type === 'video'
                  ? 'Video YouTube akan disematkan langsung untuk dapat diputar oleh student.'
                  : 'Tautan eksternal yang dapat diakses langsung oleh student.'}
              </p>
            </div>
          )}

          {/* 7. File Upload (PDF, Dokumen, Gambar, Audio, Video MP4) */}
          {needsFile && (
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Unggah File {type === 'video' ? 'Video (MP4/WebM)' : type.toUpperCase()} <span className="text-primary">*</span>
              </label>
              <div
                onClick={() => fileInputRef.current?.click()}
                className="mt-1.5 flex cursor-pointer items-center gap-3.5 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/70 p-5 hover:border-primary/50 dark:border-slate-700 dark:bg-slate-800/40 transition"
              >
                <span className="grid size-12 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                  <span className="material-symbol text-2xl">upload_file</span>
                </span>
                <div className="flex-1 min-w-0">
                  <p className="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">
                    {file ? file.name : 'Klik untuk memilih file materi'}
                  </p>
                  <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {file ? `${(file.size / 1024 / 1024).toFixed(2)} MB` : `Format didukung: ${ACCEPT_MAP[type] || '*'} · Maksimal 50 MB`}
                  </p>
                </div>
                <input
                  ref={fileInputRef}
                  type="file"
                  accept={ACCEPT_MAP[type] || ''}
                  onChange={e => setFile(e.target.files?.[0] || null)}
                  className="hidden"
                />
              </div>
            </div>
          )}

        </div>

        {/* Footer Actions */}
        <div className="flex shrink-0 flex-col-reverse items-stretch gap-2.5 border-t border-slate-200 dark:border-slate-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:px-6 sm:py-4 bg-slate-50/70 dark:bg-slate-900">
          <div className="flex flex-wrap items-center gap-3">
            <label className="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer select-none">
              <input
                type="checkbox"
                checked={keepOpenAfterSave}
                onChange={e => setKeepOpenAfterSave(e.target.checked)}
                className="size-4 rounded accent-primary cursor-pointer"
              />
              <span>Tetap buka modal untuk isi materi berikutnya</span>
            </label>
          </div>

          <div className="flex flex-wrap items-center justify-end gap-2.5">
            <button
              type="button"
              onClick={onClose}
              disabled={busy}
              className="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition cursor-pointer"
            >
              {submittedCount > 0 ? 'Selesai & Tutup' : 'Batal'}
            </button>

            <button
              type="button"
              onClick={e => handleSubmit(e, true)}
              disabled={busy || !selectedCourseId || !selectedSessionId || !title.trim()}
              className="inline-flex items-center gap-1.5 rounded-xl border border-primary/40 bg-primary/10 px-4 py-2 text-xs font-bold text-primary hover:bg-primary/20 active:scale-95 disabled:opacity-50 transition cursor-pointer"
              title="Simpan materi ini dan bersihkan form untuk langsung menambah materi baru di sesi ini"
            >
              <span className="material-symbol text-base">add_circle</span>
              Simpan &amp; Tambah Lagi
            </button>

            <button
              type="button"
              onClick={e => handleSubmit(e, false)}
              disabled={busy || !selectedCourseId || !selectedSessionId || !title.trim()}
              className="inline-flex items-center gap-1.5 rounded-xl bg-primary px-5 py-2 text-xs font-bold text-white shadow-xs hover:brightness-110 active:scale-95 disabled:opacity-50 transition cursor-pointer"
            >
              {busy ? (
                <>
                  <span className="size-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                  Mengajukan...
                </>
              ) : (
                <>
                  <span className="material-symbol text-base">send</span>
                  Simpan &amp; Selesai
                </>
              )}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}
