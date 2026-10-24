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
  const fileInputRef = useRef(null);

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
        const res = await api(`/courses/${selectedCourseId}`);
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
      const res = await api(`/courses/${selectedCourseId}`);
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

  async function handleSubmit(e) {
    e.preventDefault();
    if (!selectedCourseId) {
      showAlert.warning('Course Belum Dipilih', 'Silakan pilih course terlebih dahulu.');
      return;
    }
    if (!selectedSessionId) {
      showAlert.warning('Sesi Belum Dipilih', 'Silakan pilih atau buat sesi pembelajaran terlebih dahulu.');
      return;
    }
    if (!title.trim()) {
      showAlert.warning('Judul Wajib Diisi', 'Silakan masukkan judul materi pembelajaran.');
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
      formData.set('title', title.trim());
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

      showAlert.success('Materi Berhasil Diajukan', 'Materi telah berhasil dikirimkan ke antrean persetujuan Admin.');
      onSuccess?.();
      onClose();
    } catch (err) {
      setError(err.message || 'Gagal mengajukan materi.');
      showAlert.error('Gagal Mengajukan Materi', err.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
      <div className="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        
        {/* Header Modal */}
        <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 px-6 py-4">
          <div className="flex items-center gap-3">
            <span className="grid size-9 place-items-center rounded-lg bg-primary/10 text-primary">
              <span className="material-symbol text-xl">post_add</span>
            </span>
            <div>
              <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">Ajukan Materi Pembelajaran Baru</h3>
              <p className="text-xs text-slate-500 dark:text-slate-400">Pilih course &amp; sesi untuk menambahkan materi ke antrean persetujuan</p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition"
          >
            <span className="material-symbol text-lg">close</span>
          </button>
        </div>

        {/* Body Modal */}
        <div className="flex-1 overflow-y-auto px-6 py-5 space-y-4">
          {error && (
            <div className="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
              {error}
            </div>
          )}

          {/* 1. Pilih Course & Sesi */}
          <div className="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40 space-y-3">
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Pilih Course <span className="text-primary">*</span>
              </label>
              <select
                value={selectedCourseId}
                onChange={e => setSelectedCourseId(e.target.value)}
                disabled={loadingCourses || busy}
                className="mt-1 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-800 outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 disabled:opacity-50"
              >
                <option value="">{loadingCourses ? 'Memuat course...' : '-- Pilih Course Pembelajaran --'}</option>
                {courses.map(c => (
                  <option key={c.course_id} value={c.course_id}>
                    [{c.course_code}] {c.title}
                  </option>
                ))}
              </select>
            </div>

            {selectedCourseId && (
              <div>
                <div className="flex items-center justify-between">
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Pilih Sesi Pembelajaran <span className="text-primary">*</span>
                  </label>
                  {!showNewSessionInput && (
                    <button
                      type="button"
                      onClick={() => setShowNewSessionInput(true)}
                      className="text-[11px] font-semibold text-primary hover:underline"
                    >
                      + Buat Sesi Baru
                    </button>
                  )}
                </div>

                {!showNewSessionInput ? (
                  <select
                    value={selectedSessionId}
                    onChange={e => setSelectedSessionId(e.target.value)}
                    disabled={loadingSessions || busy}
                    className="mt-1 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-800 outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 disabled:opacity-50"
                  >
                    <option value="">{loadingSessions ? 'Memuat sesi...' : '-- Pilih Sesi --'}</option>
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
                      placeholder="Nama sesi baru, mis: Modul 1 - Standar Keselamatan"
                      value={newSessionTitle}
                      onChange={e => setNewSessionTitle(e.target.value)}
                      maxLength={200}
                      className="h-9 flex-1 rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    />
                    <button
                      type="button"
                      disabled={creatingSession || !newSessionTitle.trim()}
                      onClick={handleCreateSession}
                      className="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-700 disabled:opacity-50"
                    >
                      {creatingSession ? 'Menyimpan...' : 'Simpan Sesi'}
                    </button>
                    {sessions.length > 0 && (
                      <button
                        type="button"
                        onClick={() => setShowNewSessionInput(false)}
                        className="rounded-lg border border-slate-300 px-2 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400"
                      >
                        Batal
                      </button>
                    )}
                  </div>
                )}
              </div>
            )}
          </div>

          {/* 2. Judul Materi */}
          <div>
            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Judul Materi Pembelajaran <span className="text-primary">*</span>
            </label>
            <input
              type="text"
              required
              maxLength={200}
              placeholder="Contoh: Pengenalan Standar Kualitas 5S di Area Produksi"
              value={title}
              onChange={e => setTitle(e.target.value)}
              className="mt-1 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none transition focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            />
          </div>

          {/* 3. Tipe Materi Grid */}
          <div>
            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
              Tipe Materi <span className="text-primary">*</span>
            </label>
            <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
              {MATERIAL_TYPES.map(t => {
                const isSelected = type === t.value;
                return (
                  <button
                    key={t.value}
                    type="button"
                    onClick={() => setType(t.value)}
                    className={`flex items-center gap-2 rounded-xl p-2.5 text-left text-xs transition cursor-pointer border ${
                      isSelected
                        ? 'border-primary bg-primary/10 text-primary font-bold ring-1 ring-primary/30'
                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300'
                    }`}
                  >
                    <span className="material-symbol text-base shrink-0">{t.icon}</span>
                    <span className="truncate">{t.label}</span>
                  </button>
                );
              })}
            </div>
          </div>

          {/* 4. Tipe Video: Sumber Video (YouTube / MP4) */}
          {type === 'video' && (
            <div className="rounded-xl border border-primary/20 bg-primary/5 p-3 dark:border-primary/30 dark:bg-primary/10">
              <p className="mb-2 text-xs font-bold text-slate-700 dark:text-slate-300">
                Pilih Sumber Video <span className="text-primary">*</span>
              </p>
              <div className="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  onClick={() => setVideoSource('youtube')}
                  className={`flex items-center justify-center gap-2 rounded-lg py-2 px-3 text-xs font-bold transition cursor-pointer ${
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
                  className={`flex items-center justify-center gap-2 rounded-lg py-2 px-3 text-xs font-bold transition cursor-pointer ${
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

          {/* 5. Konten Teks TinyMCE */}
          {isText && (
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Isi Materi Teks <span className="text-primary">*</span>
                <span className="ml-1 text-[11px] font-normal text-slate-500">(Format teks, daftar, tabel, dsb.)</span>
              </label>
              <TinyEditor value={htmlContent} onChange={setHtmlContent} />
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
                className="mt-1 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none transition focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
              />
              <p className="mt-1 text-[11px] text-slate-500">
                {type === 'video'
                  ? 'Video YouTube disematkan langsung untuk diputar oleh student.'
                  : 'Tautan eksternal dapat diakses langsung oleh student.'}
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
                className="mt-1 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 hover:border-primary/50 dark:border-slate-700 dark:bg-slate-800/40"
              >
                <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary">
                  <span className="material-symbol text-xl">upload_file</span>
                </span>
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-bold text-slate-800 dark:text-slate-200">
                    {file ? file.name : 'Klik untuk memilih file materi'}
                  </p>
                  <p className="text-[11px] text-slate-500 dark:text-slate-400">
                    {file ? `${(file.size / 1024 / 1024).toFixed(2)} MB` : `Format: ${ACCEPT_MAP[type] || '*'} · Maksimal 50 MB`}
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
        <div className="flex items-center justify-end gap-2 border-t border-slate-200 dark:border-slate-800 px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50">
          <button
            type="button"
            onClick={onClose}
            disabled={busy}
            className="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            onClick={handleSubmit}
            disabled={busy || !selectedCourseId || !selectedSessionId || !title.trim()}
            className="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-xs font-bold text-white shadow-xs hover:brightness-110 active:scale-95 disabled:opacity-50 transition cursor-pointer"
          >
            {busy ? (
              <>
                <span className="size-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white" />
                Mengajukan...
              </>
            ) : (
              <>
                <span className="material-symbol text-base">send</span>
                Kirim Pengajuan Materi
              </>
            )}
          </button>
        </div>

      </div>
    </div>
  );
}
