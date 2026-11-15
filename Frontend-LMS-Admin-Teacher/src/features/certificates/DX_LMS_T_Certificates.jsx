import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Award,
  CheckCircle2,
  Download,
  ExternalLink,
  FileText,
  Filter,
  Plus,
  Search,
  Trash2,
  Upload
} from 'lucide-react';
import { API_BASE, api } from '../../api/client';
import Button from '../../components/Button';
import Modal from '../../components/Modal';
import SearchableSelect from '../../components/SearchableSelect';
import showAlert from '../../utils/alert';

const AWARD_TYPE_OPTIONS = [
  { value: 'certificate', label: 'Sertifikat Pelatihan' },
  { value: 'award', label: 'Penghargaan / Apresiasi' },
  { value: 'document', label: 'Dokumen Resmi / Lampiran' }
];

const AWARD_TYPE_META = {
  certificate: {
    label: 'Sertifikat',
    badgeClass: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-900/60'
  },
  award: {
    label: 'Penghargaan',
    badgeClass: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/60'
  },
  document: {
    label: 'Dokumen',
    badgeClass: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900/60'
  }
};

function formatFileSize(bytes) {
  const num = Number(bytes || 0);
  if (!Number.isFinite(num) || num <= 0) return '-';
  if (num < 1024) return `${num} B`;
  if (num < 1024 * 1024) return `${(num / 1024).toFixed(1)} KB`;
  return `${(num / (1024 * 1024)).toFixed(2)} MB`;
}

function formatDateTime(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '-';
  return date.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

export default function DX_LMS_T_Certificates({ session }) {
  const [items, setItems] = useState([]);
  const [students, setStudents] = useState([]);
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  const [searchQuery, setSearchQuery] = useState('');
  const [typeFilter, setTypeFilter] = useState('');

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [studentId, setStudentId] = useState('');
  const [courseId, setCourseId] = useState('');
  const [awardType, setAwardType] = useState('certificate');
  const [title, setTitle] = useState('');
  const [certificateNo, setCertificateNo] = useState('');
  const [description, setDescription] = useState('');
  const [file, setFile] = useState(null);

  const loadCertificates = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (typeFilter) params.set('award_type', typeFilter);
      if (searchQuery.trim()) params.set('search', searchQuery.trim());
      const query = params.toString();
      const data = await api(`/certificates${query ? `?${query}` : ''}`);
      setItems(Array.isArray(data?.items) ? data.items : []);
    } catch (err) {
      showAlert.error('Gagal Memuat Data', err.message || 'Tidak dapat memuat daftar sertifikat.');
    } finally {
      setLoading(false);
    }
  }, [typeFilter, searchQuery]);

  const loadLookups = useCallback(async () => {
    try {
      const [studentsRes, coursesRes] = await Promise.all([
        api('/certificates/students').catch(() => ({ items: [] })),
        api('/courses').catch(() => ({ items: [], courses: [] }))
      ]);
      setStudents(Array.isArray(studentsRes?.items) ? studentsRes.items : []);
      const courseList = Array.isArray(coursesRes?.items)
        ? coursesRes.items
        : (Array.isArray(coursesRes?.courses) ? coursesRes.courses : []);
      setCourses(courseList);
    } catch {
      // Lookups are handled gracefully with empty fallback.
    }
  }, []);

  useEffect(() => {
    loadLookups();
  }, [loadLookups]);

  useEffect(() => {
    const timer = setTimeout(() => {
      loadCertificates();
    }, 200);
    return () => clearTimeout(timer);
  }, [loadCertificates]);

  const studentOptions = useMemo(
    () =>
      students.map((s) => ({
        value: String(s.student_id),
        label: `${s.fullname} (${s.employee_npk})`
      })),
    [students]
  );

  const courseOptions = useMemo(
    () => [
      { value: '', label: 'Tanpa Course Tertentu (Umum)' },
      ...courses.map((c) => ({
        value: String(c.course_id),
        label: `${c.title}${c.course_code ? ` (${c.course_code})` : ''}`
      }))
    ],
    [courses]
  );

  const stats = useMemo(() => {
    const total = items.length;
    const certificates = items.filter((item) => item.award_type === 'certificate').length;
    const awardsAndDocs = items.filter((item) => item.award_type !== 'certificate').length;
    return { total, certificates, awardsAndDocs };
  }, [items]);

  function resetForm() {
    setStudentId('');
    setCourseId('');
    setAwardType('certificate');
    setTitle('');
    setCertificateNo('');
    setDescription('');
    setFile(null);
  }

  function openSendModal() {
    resetForm();
    setIsModalOpen(true);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    if (!studentId) {
      showAlert.warning('Pilih Student', 'Silakan pilih student penerima terlebih dahulu.');
      return;
    }
    if (title.trim().length < 3) {
      showAlert.warning('Judul Terlalu Pendek', 'Judul sertifikat/penghargaan minimal 3 karakter.');
      return;
    }
    if (!file) {
      showAlert.warning('Lampiran Wajib', 'Silakan unggah file sertifikat, penghargaan, atau dokumen.');
      return;
    }

    setSubmitting(true);
    try {
      const formData = new FormData();
      formData.append('student_id', String(studentId));
      if (courseId) formData.append('course_id', String(courseId));
      formData.append('award_type', awardType);
      formData.append('title', title.trim());
      if (certificateNo.trim()) formData.append('certificate_no', certificateNo.trim());
      if (description.trim()) formData.append('description', description.trim());
      formData.append('file', file);

      await api('/certificates', {
        method: 'POST',
        body: formData
      });

      setIsModalOpen(false);
      resetForm();
      showAlert.success('Berhasil Dikirim', 'Sertifikat / penghargaan telah dikirim ke student dan notifikasi telah diterbitkan.');
      await loadCertificates();
    } catch (err) {
      showAlert.error('Gagal Mengirim', err.message || 'Terjadi kesalahan saat mengirim sertifikat.');
    } finally {
      setSubmitting(false);
    }
  }

  async function handleDelete(item) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Sertifikat / Penghargaan?',
      text: `Data "${item.title}" untuk ${item.student_name} (${item.student_npk}) akan dihapus permanen beserta file lampirannya.`,
      confirmText: 'Ya, Hapus',
      cancelText: 'Batal',
      danger: true,
      icon: 'warning'
    });
    if (!confirmed) return;

    try {
      await api(`/certificates/${item.certificate_id}`, { method: 'DELETE' });
      showAlert.success('Terhapus', 'Sertifikat / penghargaan berhasil dihapus.');
      await loadCertificates();
    } catch (err) {
      showAlert.error('Gagal Menghapus', err.message || 'Tidak dapat menghapus data.');
    }
  }

  const currentRole = String(session?.role || '').toLowerCase();
  const currentNpk = String(session?.employee_npk || '').trim();

  return (
    <div className="space-y-6">
      {/* Header & CTA */}
      <div className="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-700 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div className="flex items-start gap-3.5">
          <div className="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-100 dark:border-red-900/60 flex items-center justify-center text-[#E60012] shrink-0">
            <Award className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
              Sertifikat & Penghargaan
            </h1>
            <p className="text-sm text-gray-500 dark:text-slate-400 mt-0.5">
              Kirim sertifikat pelatihan, piagam penghargaan, atau dokumen resmi langsung ke akun student.
            </p>
          </div>
        </div>
        <Button
          variant="primary"
          icon={<Plus className="w-4 h-4" />}
          onClick={openSendModal}
          className="shrink-0"
        >
          Kirim Sertifikat / Penghargaan
        </Button>
      </div>

      {/* KPI Summary */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-700 p-4 shadow-sm">
          <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
            Total Terkirim
          </p>
          <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">
            {stats.total}
          </p>
        </div>
        <div className="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-700 p-4 shadow-sm">
          <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
            Sertifikat
          </p>
          <p className="text-2xl font-bold text-[#E60012] mt-1">
            {stats.certificates}
          </p>
        </div>
        <div className="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-700 p-4 shadow-sm">
          <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
            Penghargaan & Dokumen
          </p>
          <p className="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
            {stats.awardsAndDocs}
          </p>
        </div>
      </div>

      {/* Filters & Table */}
      <div className="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div className="p-4 sm:p-5 border-b border-gray-200 dark:border-slate-700 flex flex-col sm:flex-row gap-3">
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Cari nama student, NPK, judul, atau nomor sertifikat..."
              className="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#E60012]"
            />
          </div>
          <div className="relative sm:w-56">
            <Filter className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <select
              value={typeFilter}
              onChange={(e) => setTypeFilter(e.target.value)}
              className="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#E60012]"
            >
              <option value="">Semua Jenis</option>
              {AWARD_TYPE_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </div>
        </div>

        {loading ? (
          <div className="p-8 text-center text-sm text-gray-500 dark:text-slate-400">
            Memuat daftar sertifikat & penghargaan...
          </div>
        ) : items.length === 0 ? (
          <div className="p-10 text-center">
            <Award className="w-10 h-10 text-gray-300 dark:text-slate-600 mx-auto mb-3" />
            <p className="text-sm font-semibold text-gray-700 dark:text-slate-200">
              Belum ada sertifikat atau penghargaan yang dikirim
            </p>
            <p className="text-xs text-gray-500 dark:text-slate-400 mt-1">
              Klik tombol &ldquo;Kirim Sertifikat / Penghargaan&rdquo; untuk mengirim ke student.
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-900 text-white text-xs uppercase tracking-wider">
                  <th className="py-3 px-4 font-semibold">Judul & Nomor</th>
                  <th className="py-3 px-4 font-semibold">Jenis</th>
                  <th className="py-3 px-4 font-semibold">Penerima (Student)</th>
                  <th className="py-3 px-4 font-semibold">Lampiran</th>
                  <th className="py-3 px-4 font-semibold">Dikirim Oleh</th>
                  <th className="py-3 px-4 font-semibold text-right">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                {items.map((item) => {
                  const meta = AWARD_TYPE_META[item.award_type] || AWARD_TYPE_META.certificate;
                  const canDelete =
                    currentRole === 'admin' ||
                    (currentRole === 'teacher' && currentNpk && String(item.issued_by_npk || '') === currentNpk);
                  const viewUrl = `${API_BASE}/certificates/${item.certificate_id}/file`;
                  const downloadUrl = `${API_BASE}/certificates/${item.certificate_id}/file?download=1`;

                  return (
                    <tr key={item.certificate_id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                      <td className="py-3.5 px-4 align-top">
                        <div className="font-semibold text-gray-900 dark:text-white">
                          {item.title}
                        </div>
                        {item.certificate_no && (
                          <div className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            No: {item.certificate_no}
                          </div>
                        )}
                        {item.course_title && (
                          <div className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Course: {item.course_title}
                          </div>
                        )}
                        {item.description && (
                          <div className="text-xs text-gray-500 dark:text-slate-400 mt-1 line-clamp-2 max-w-xs">
                            {item.description}
                          </div>
                        )}
                      </td>
                      <td className="py-3.5 px-4 align-top">
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ${meta.badgeClass}`}>
                          {meta.label}
                        </span>
                      </td>
                      <td className="py-3.5 px-4 align-top">
                        <div className="font-medium text-gray-900 dark:text-white">
                          {item.student_name}
                        </div>
                        <div className="text-xs text-gray-500 dark:text-slate-400">
                          NPK: {item.student_npk}
                        </div>
                      </td>
                      <td className="py-3.5 px-4 align-top">
                        <div className="flex items-center gap-1.5 text-gray-700 dark:text-slate-300 text-xs font-medium">
                          <FileText className="w-3.5 h-3.5 text-gray-400 shrink-0" />
                          <span className="truncate max-w-[160px]" title={item.original_filename}>
                            {item.original_filename}
                          </span>
                        </div>
                        <div className="text-[11px] text-gray-400 mt-0.5">
                          {formatFileSize(item.file_size)}
                        </div>
                      </td>
                      <td className="py-3.5 px-4 align-top">
                        <div className="text-gray-800 dark:text-slate-200 font-medium text-xs">
                          {item.issued_by_name}
                        </div>
                        <div className="text-[11px] text-gray-500 dark:text-slate-400 mt-0.5">
                          {formatDateTime(item.issued_at)}
                        </div>
                      </td>
                      <td className="py-3.5 px-4 align-top text-right">
                        <div className="inline-flex items-center justify-end gap-1.5 flex-wrap">
                          <a href={viewUrl} target="_blank" rel="noopener noreferrer">
                            <Button variant="info" size="sm" icon={<ExternalLink className="w-3.5 h-3.5" />}>
                              Lihat
                            </Button>
                          </a>
                          <a href={downloadUrl} download={item.original_filename || true}>
                            <Button variant="teal" size="sm" icon={<Download className="w-3.5 h-3.5" />}>
                              Unduh
                            </Button>
                          </a>
                          {canDelete && (
                            <Button
                              variant="danger"
                              size="sm"
                              icon={<Trash2 className="w-3.5 h-3.5" />}
                              onClick={() => handleDelete(item)}
                            >
                              Hapus
                            </Button>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal Kirim Sertifikat / Penghargaan */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => !submitting && setIsModalOpen(false)}
        title="Kirim Sertifikat / Penghargaan"
        subtitle="Pilih student penerima dan unggah file sertifikat, penghargaan, atau dokumen pendukung."
        maxWidth="lg"
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
              Student Penerima <span className="text-[#E60012]">*</span>
            </label>
            <SearchableSelect
              options={studentOptions}
              value={studentId}
              onChange={(e) => setStudentId(e.target.value)}
              placeholder="Cari nama atau NPK student..."
              title="Pilih Student Penerima"
              required
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                Jenis Kiriman <span className="text-[#E60012]">*</span>
              </label>
              <select
                value={awardType}
                onChange={(e) => setAwardType(e.target.value)}
                className="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#E60012]"
              >
                {AWARD_TYPE_OPTIONS.map((opt) => (
                  <option key={opt.value} value={opt.value}>
                    {opt.label}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                Course Terkait (Opsional)
              </label>
              <SearchableSelect
                options={courseOptions}
                value={courseId}
                onChange={(e) => setCourseId(e.target.value)}
                placeholder="Pilih course terkait..."
                title="Pilih Course Terkait"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                Judul Sertifikat / Penghargaan <span className="text-[#E60012]">*</span>
              </label>
              <input
                type="text"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                placeholder="Contoh: Sertifikat Kelulusan Safety Line A"
                maxLength={200}
                required
                className="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#E60012]"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                Nomor Sertifikat (Opsional)
              </label>
              <input
                type="text"
                value={certificateNo}
                onChange={(e) => setCertificateNo(e.target.value)}
                placeholder="Contoh: CERT-2026-001"
                maxLength={80}
                className="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#E60012]"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
              Catatan / Deskripsi (Opsional)
            </label>
            <textarea
              rows={2}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Pesan apresiasi atau keterangan dokumen untuk student..."
              maxLength={1000}
              className="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#E60012]"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
              File Lampiran (PDF, Gambar, Office) <span className="text-[#E60012]">*</span>
            </label>
            <label className="flex flex-col items-center justify-center w-full p-4 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl cursor-pointer hover:border-[#E60012] dark:hover:border-red-500 bg-gray-50/70 dark:bg-slate-800/50 transition-colors">
              <Upload className="w-6 h-6 text-gray-400 mb-1.5" />
              <span className="text-xs font-semibold text-gray-700 dark:text-slate-200 text-center">
                {file ? file.name : 'Klik untuk memilih file (.pdf, .png, .jpg, .docx, .pptx, .xlsx)'}
              </span>
              {file && (
                <span className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                  <CheckCircle2 className="w-3.5 h-3.5" /> {formatFileSize(file.size)} siap diunggah
                </span>
              )}
              <input
                type="file"
                accept=".pdf,.png,.jpg,.jpeg,.webp,.doc,.docx,.ppt,.pptx,.xls,.xlsx"
                onChange={(e) => setFile(e.target.files?.[0] || null)}
                className="hidden"
              />
            </label>
          </div>

          <div className="pt-2 flex items-center justify-end gap-2.5 border-t border-gray-200 dark:border-slate-700">
            <Button
              type="button"
              variant="secondary"
              onClick={() => setIsModalOpen(false)}
              disabled={submitting}
            >
              Batal
            </Button>
            <Button
              type="submit"
              variant="success"
              icon={<Award className="w-4 h-4" />}
              loading={submitting}
            >
              {submitting ? 'Mengirim...' : 'Kirim Sekarang'}
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}

