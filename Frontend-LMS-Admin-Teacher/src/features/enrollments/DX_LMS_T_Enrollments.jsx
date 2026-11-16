import { useEffect, useRef, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import { Check, ClipboardCheck, Send, UserPlus, X } from 'lucide-react';
import { api } from '../../api/client';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';
import { hasPermission } from '../../utils/access-control';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import SearchableSelect from '../../components/SearchableSelect';
import StatusBadge from '../../components/StatusBadge';
import DataTable from '../../components/DataTable';
import Modal from '../../components/Modal';
import Pagination, { usePagination } from '../../components/Pagination';

const INPUT_CLASS =
  'min-h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 py-2 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? value
    : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
}

/**
 * Halaman Persetujuan Course & Rekomendasi Pendaftaran Student.
 */
export default function EnrollmentsPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canRecommend = hasPermission(user, 'enrollments.recommend') && !isAdmin;
  const canDecide = hasPermission(user, 'enrollments.decide');
  const [requests, setRequests] = useState([]);
  const [courses, setCourses] = useState([]);
  const [students, setStudents] = useState([]);
  const [studentSearch, setStudentSearch] = useState('');
  const [selectedStudent, setSelectedStudent] = useState(null);
  const [courseCode, setCourseCode] = useState('');
  const [note, setNote] = useState('');
  const [filter, setFilter] = useQueryParamState('status', 'all');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [viewingRequest, setViewingRequest] = useState(null);
  const searchVersion = useRef(0);

  async function loadRequests(silent = false) {
    try {
      const result = await api(`/enrollments/requests?status=${filter}`);
      setRequests(result.requests || []);
      setError('');
    } catch (loadError) {
      if (!silent) {
        setError(loadError.message);
        showAlert.error('Gagal Memuat Permintaan Course', loadError.message);
      }
    } finally {
      if (!silent) setLoading(false);
    }
  }

  useEffect(() => {
    loadRequests();
  }, [filter]);

  useEffect(() => {
    if (!canRecommend) return undefined;
    let mounted = true;
    api('/enrollments/options')
      .then((result) => {
        if (!mounted) return;
        setCourses(result.courses || []);
        setStudents(result.students || []);
        setError('');
      })
      .catch((loadError) => {
        if (mounted) {
          setError(loadError.message);
          showAlert.error('Gagal Memuat Opsi Pendaftaran', loadError.message);
        }
      });
    return () => {
      mounted = false;
    };
  }, [canRecommend]);

  useEffect(() => {
    if (!canRecommend || !studentSearch.trim() || selectedStudent) return undefined;
    const version = ++searchVersion.current;
    const timer = setTimeout(async () => {
      try {
        const result = await api(
          `/enrollments/options?studentSearch=${encodeURIComponent(studentSearch.trim())}`
        );
        if (version === searchVersion.current) setStudents(result.students || []);
      } catch (loadError) {
        setError(loadError.message);
      }
    }, 250);
    return () => clearTimeout(timer);
  }, [canRecommend, studentSearch, selectedStudent]);

  useEffect(() => {
    const timer = setInterval(() => {
      if (!document.hidden) loadRequests(true);
    }, 15000);
    const refresh = () => {
      if (!document.hidden) loadRequests(true);
    };
    window.addEventListener('focus', refresh);
    return () => {
      clearInterval(timer);
      window.removeEventListener('focus', refresh);
    };
  }, [filter]);

  async function recommend(event) {
    event.preventDefault();
    if (!selectedStudent || !courseCode) {
      const msg = 'Pilih satu Student dan course terlebih dahulu.';
      setError(msg);
      showAlert.warning('Data Belum Lengkap', msg);
      return;
    }
    setBusy(true);
    setError('');
    try {
      await api('/enrollments/recommendations', {
        method: 'POST',
        body: JSON.stringify({ studentNpk: selectedStudent.npk, courseCode, note })
      });
      setSelectedStudent(null);
      setStudentSearch('');
      setCourseCode('');
      setNote('');
      await loadRequests(true);
      showAlert.success(
        'Rekomendasi Dikirim',
        'Admin dapat meninjau rekomendasi ini pada halaman Persetujuan Course.'
      );
    } catch (requestError) {
      setError(requestError.message);
      showAlert.error('Rekomendasi Gagal', requestError.message);
    } finally {
      setBusy(false);
    }
  }

  async function decide(request, action) {
    let decisionNote = '';
    if (action === 'rejected') {
      decisionNote = await showAlert.prompt({
        title: 'Alasan Penolakan',
        text: `${request.student_name} · ${request.course_title}`,
        inputPlaceholder: 'Jelaskan alasan penolakan (minimal 3 karakter)',
        confirmText: 'Tolak Permintaan',
        danger: true
      });
      if (decisionNote === null) return;
      if (decisionNote.length < 3) {
        showAlert.error('Alasan Belum Cukup', 'Masukkan alasan penolakan minimal 3 karakter.');
        return;
      }
    } else {
      const confirmed = await showAlert.confirm({
        title: 'Setujui Akses Course?',
        text: `${request.student_name} akan mendapatkan akses ke ${request.course_title}.`,
        confirmText: 'Setujui Akses',
        cancelText: 'Periksa Kembali',
        icon: 'question'
      });
      if (!confirmed) return;
    }

    setBusy(true);
    setError('');
    try {
      await api(`/enrollments/${request.request_id}/decision`, {
        method: 'POST',
        body: JSON.stringify({ action, note: decisionNote })
      });
      await loadRequests(true);
      showAlert.success(
        action === 'approved' ? 'Akses Course Disetujui' : 'Permintaan Ditolak',
        action === 'approved'
          ? 'Course sekarang tampil dan dapat dipelajari oleh Student.'
          : 'Alasan penolakan tersimpan dan terlihat oleh Student.'
      );
    } catch (decisionError) {
      setError(decisionError.message);
      showAlert.error('Keputusan Gagal Disimpan', decisionError.message);
    } finally {
      setBusy(false);
    }
  }

  async function queueBatchApprove() {
    const pendingRequests = requests.filter(r => r.status === 'pending');
    if (!pendingRequests.length) {
      showAlert.error('Tidak Ada Permintaan', 'Tidak ada permintaan pending untuk disetujui.');
      return;
    }
    const confirmed = await showAlert.confirm({
      title: 'Proses Antrean Persetujuan Massal?',
      text: `${pendingRequests.length} permintaan pending akan diproses di latar belakang secara realtime.`,
      confirmText: 'Jalankan Antrean',
      icon: 'question'
    });
    if (!confirmed) return;

    setBusy(true);
    try {
      const result = await api('/jobs/enrollments', {
        method: 'POST',
        body: JSON.stringify({
          items: pendingRequests.map(item => ({
            requestId: item.request_id,
            action: 'approved',
            note: 'Disetujui massal melalui antrean Admin',
            label: `${item.student_name} (${item.course_code})`
          }))
        })
      });
      try {
        localStorage.setItem('dls_active_job_id', String(result.jobId));
        localStorage.setItem('dls_jobs_center_hidden', 'false');
      } catch {}
      showAlert.success(
        'Antrean Dimulai',
        `Antrean #${result.jobId} untuk ${result.total} pendaftaran course sedang diproses secara realtime.`
      );
      await loadRequests(true);
    } catch (jobErr) {
      setError(jobErr.message);
      showAlert.error('Gagal Memulai Antrean', jobErr.message);
    } finally {
      setBusy(false);
    }
  }

  const pendingCount = requests.filter((request) => request.status === 'pending').length;
  const page = usePagination(requests, 10);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Manajemen Pendaftaran"
        title="Persetujuan Course"
        description="Student memerlukan persetujuan sebelum membuka materi course. Teacher dapat merekomendasikan peserta pada course binaan; keputusan akses dikelola Admin."
        actions={
          <div className="flex flex-wrap items-center gap-2">
            <span className="inline-flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-lowest px-3.5 py-2 text-xs font-semibold text-on-surface-variant dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
              <ClipboardCheck className="size-3.5 text-primary" />
              {pendingCount} permintaan menunggu keputusan
            </span>
            {canDecide && pendingCount > 0 && (
              <Button
                variant="primary"
                onClick={queueBatchApprove}
                disabled={busy}
                className="gap-1.5 row-auto" 
                title="Proses seluruh permintaan pending lewat antrean background realtime"
              >
                <Check className="size-4" />
                <span>Setujui Semua ({pendingCount})</span>
              </Button>
            )}
          </div>
        }
      />

      {error && (
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          {error}
        </div>
      )}

      {canRecommend && (
        <div id="recommend-student" className="scroll-mt-20">
          <Card
            title="Rekomendasikan Student"
            subtitle="Ajukan rekomendasi peserta untuk bergabung ke course yang Anda ampu."
          >
            <form onSubmit={recommend} className="grid gap-4 lg:grid-cols-2">
              <div className="relative">
                <label htmlFor="student-search" className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Cari Student Aktif *
                </label>
                {selectedStudent ? (
                  <div className="flex min-h-10 items-center justify-between gap-3 rounded-md border border-primary/40 bg-primary/5 px-3 text-sm dark:bg-red-950/30">
                    <span className="truncate">
                      <strong className="text-on-surface">{selectedStudent.fullname}</strong>
                      <span className="ml-2 font-mono text-xs text-muted">
                        {selectedStudent.npk}
                      </span>
                    </span>
                    <Button
                      type="button"
                      size="xs"
                      variant="ghost"
                      onClick={() => {
                        setSelectedStudent(null);
                        setStudentSearch('');
                      }}
                    >
                      Ubah
                    </Button>
                  </div>
                ) : (
                  <>
                    <input
                      id="student-search"
                      value={studentSearch}
                      onChange={(event) => setStudentSearch(event.target.value)}
                      className={INPUT_CLASS}
                      placeholder="Masukkan NPK atau nama student..."
                      autoComplete="off"
                    />
                    {studentSearch.trim() && (
                      <div className="dls-card-surface absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg shadow-xl">
                        {students.length ? (
                          students.map((student) => (
                            <button
                              key={student.npk}
                              type="button"
                              data-dls-action-ignore="true"
                              onClick={() => {
                                setSelectedStudent(student);
                                setStudentSearch(`${student.fullname} (${student.npk})`);
                              }}
                              className="block w-full border-b border-outline/30 px-3 py-2.5 text-left text-sm last:border-0 hover:bg-surface-low dark:border-slate-800 dark:hover:bg-slate-800"
                            >
                              <span className="font-semibold text-on-surface">
                                {student.fullname}
                              </span>
                              <span className="ml-2 font-mono text-xs text-muted">
                                {student.npk}
                              </span>
                            </button>
                          ))
                        ) : (
                          <p className="p-3 text-sm text-muted">Student aktif tidak ditemukan.</p>
                        )}
                      </div>
                    )}
                  </>
                )}
              </div>

              <div>
                <label htmlFor="course-select" className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Course dalam Tanggung Jawab Anda *
                </label>
                <SearchableSelect
                  id="course-select"
                  title="Pilih Course Tanggung Jawab"
                  required
                  value={courseCode}
                  onChange={(event) => setCourseCode(event.target.value)}
                  options={courses.map((course) => ({
                    value: course.course_code,
                    label: `${course.title} (${course.course_code})`
                  }))}
                  placeholder="Cari course berdasarkan nama atau kode..."
                  className={INPUT_CLASS}
                />
                {!courses.length && (
                  <p className="mt-1 text-xs text-muted">
                    Belum ada course aktif yang ditugaskan atau dibuat oleh Anda.
                  </p>
                )}
              </div>

              <div className="lg:col-span-2">
                <label htmlFor="recommendation-note" className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Catatan untuk Admin (opsional)
                </label>
                <textarea
                  id="recommendation-note"
                  maxLength={1000}
                  value={note}
                  onChange={(event) => setNote(event.target.value)}
                  className={`${INPUT_CLASS} min-h-20 resize-y`}
                  placeholder="Contoh: Student perlu mengikuti course ini untuk kompetensi area produksi."
                />
              </div>

              <div className="flex justify-end lg:col-span-2">
                <Button
                  type="submit"
                  variant="primary"
                  disabled={busy || !courses.length}
                  loading={busy}
                  icon={<Send className="size-4" />}
                >
                  Kirim Rekomendasi
                </Button>
              </div>
            </form>
          </Card>
        </div>
      )}

      <Card
        id="enrollments-list"
        className="scroll-mt-20"
        noPadding
        title={isAdmin ? 'Daftar Permintaan Akses Course' : 'Rekomendasi Saya'}
        subtitle={
          isAdmin
            ? `${pendingCount} permintaan sedang menunggu tinjauan Admin.`
            : 'Status rekomendasi diperbarui otomatis setiap 15 detik.'
        }
        action={
          <label className="flex items-center gap-2 text-xs font-semibold text-on-surface-variant">
            <span>Status</span>
            <select
              value={filter}
              onChange={(event) => setFilter(event.target.value)}
              className="h-9 rounded-md border border-outline bg-surface-lowest px-3 text-xs text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              <option value="all">Semua</option>
              <option value="pending">Menunggu</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </label>
        }
      >
        <DataTable
          columns={[
            { key: 'student', label: 'Student', className: 'w-[240px] min-w-[240px]' },
            { key: 'course', label: 'Course' },
            { key: 'source', label: 'Sumber Permintaan' },
            { key: 'status', label: 'Status' },
            { key: 'date', label: 'Tanggal' },
            { key: 'action', label: 'Keputusan / Aksi', className: 'text-right' }
          ]}
          loading={loading}
          empty={!requests.length}
          emptyMessage="Belum ada permintaan akses pada filter status ini."
          ariaLabel="Tabel permintaan akses course"
          className="mx-4 my-4 rounded-lg shadow-none sm:mx-6 sm:my-5"
        >
          {page.pagedItems.map((request) => (
            <tr
              key={request.request_id}
              onClick={(e) => {
                if (e.target.closest('input, button, a, select')) return;
                setViewingRequest(request);
              }}
              className="align-top cursor-pointer transition hover:bg-surface-low/70 dark:hover:bg-slate-800/40"
              title="Klik untuk melihat detail permintaan"
            >
              <td className="w-[240px] min-w-[240px]">
                <p className="font-semibold text-on-surface">{request.student_name}</p>
                <p className="mt-0.5 font-mono text-xs text-muted">NPK: {request.student_npk}</p>
                {request.request_note && (
                  <p className="mt-2 max-w-xs whitespace-pre-wrap text-xs leading-5 text-on-surface-variant">
                    {request.request_note}
                  </p>
                )}
                {request.status === 'rejected' && request.decision_note && (
                  <p className="mt-2 max-w-xs rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                    Alasan: {request.decision_note}
                  </p>
                )}
              </td>
              <td>
                <p className="font-semibold text-on-surface">{request.course_title}</p>
                <p className="mt-0.5 font-mono text-xs text-muted">{request.course_code}</p>
              </td>
              <td>
                <span className="inline-flex items-center gap-1.5 text-xs font-medium text-on-surface">
                  <UserPlus className="size-3.5 text-muted" />
                  {request.request_type === 'teacher_recommendation'
                    ? 'Rekomendasi Teacher'
                    : 'Permintaan Student'}
                </span>
                <p className="mt-0.5 font-mono text-xs text-muted">{request.requested_by_npk}</p>
              </td>
              <td>
                <StatusBadge status={request.status} dot />
              </td>
              <td className="whitespace-nowrap text-xs text-muted">
                {formatDate(request.requested_at)}
              </td>
              <td className="text-right">
                {canDecide && request.status === 'pending' ? (
                  <div className="ml-auto grid w-[190px] grid-cols-2 gap-1.5">
                    <Button
                      size="xs"
                      variant="success"
                      disabled={busy}
                      icon={<Check className="size-3.5" />}
                      onClick={() => decide(request, 'approved')}
                      className="w-full justify-center"
                    >
                      Setujui
                    </Button>
                    <Button
                      size="xs"
                      variant="danger"
                      disabled={busy}
                      icon={<X className="size-3.5" />}
                      onClick={() => decide(request, 'rejected')}
                      className="w-full justify-center"
                    >
                      Tolak
                    </Button>
                  </div>
                ) : (
                  <span className="text-xs text-muted">
                    {request.decision_by_npk ? `Diputus ${request.decision_by_npk}` : '—'}
                  </span>
                )}
              </td>
            </tr>
          ))}
        </DataTable>

        {Boolean(requests.length) && (
          <Pagination
            {...page}
            onPageChange={page.setPage}
            onPageSizeChange={page.setPageSize}
            pageSizes={[10, 25, 50]}
          />
        )}
      </Card>

      {/* Modal Detail Permintaan Akses Course */}
      <Modal
        isOpen={Boolean(viewingRequest)}
        onClose={() => setViewingRequest(null)}
        title="Detail Permintaan Akses Course"
        subtitle="Informasi lengkap pengajuan akses course pembelajaran."
        maxWidth="lg"
      >
        {viewingRequest && (
          <div className="space-y-5">
            <div className="flex items-center gap-4 rounded-xl border border-outline/60 bg-surface-low/40 p-4 dark:border-slate-800 dark:bg-slate-800/40">
              <div className="grid size-12 shrink-0 place-items-center rounded-xl bg-primary/10 text-lg font-bold text-primary dark:bg-red-950/40 dark:text-red-300">
                {viewingRequest.student_name?.charAt(0)?.toUpperCase() || 'S'}
              </div>
              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <h3 className="text-base font-bold text-on-surface truncate">{viewingRequest.student_name}</h3>
                  <StatusBadge status={viewingRequest.status} dot />
                </div>
                <p className="mt-0.5 font-mono text-xs text-muted">NPK: {viewingRequest.student_npk}</p>
              </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-2 text-xs">
              <div className="rounded-lg border border-outline/50 bg-surface-lowest p-3 dark:border-slate-800 dark:bg-slate-900 sm:col-span-2">
                <span className="text-muted block">Course Dituju</span>
                <span className="font-semibold text-on-surface text-sm mt-0.5 block">
                  {viewingRequest.course_title}
                </span>
                <span className="font-mono text-muted text-[11px] block mt-0.5">
                  Kode: {viewingRequest.course_code}
                </span>
              </div>

              <div className="rounded-lg border border-outline/50 bg-surface-lowest p-3 dark:border-slate-800 dark:bg-slate-900">
                <span className="text-muted block">Sumber Pengajuan</span>
                <span className="font-semibold text-on-surface mt-0.5 block">
                  {viewingRequest.request_type === 'teacher_recommendation'
                    ? 'Rekomendasi Teacher'
                    : 'Permintaan Mandiri Student'}
                </span>
                <span className="font-mono text-muted text-[11px] block mt-0.5">
                  Oleh NPK: {viewingRequest.requested_by_npk || '—'}
                </span>
              </div>

              <div className="rounded-lg border border-outline/50 bg-surface-lowest p-3 dark:border-slate-800 dark:bg-slate-900">
                <span className="text-muted block">Waktu Pengajuan</span>
                <span className="font-semibold text-on-surface mt-0.5 block">
                  {formatDate(viewingRequest.requested_at)}
                </span>
              </div>

              {viewingRequest.request_note && (
                <div className="rounded-lg border border-outline/50 bg-surface-lowest p-3 dark:border-slate-800 dark:bg-slate-900 sm:col-span-2">
                  <span className="text-muted block">Catatan Pengajuan</span>
                  <p className="mt-1 whitespace-pre-wrap font-medium text-on-surface leading-5">
                    {viewingRequest.request_note}
                  </p>
                </div>
              )}

              {viewingRequest.decision_note && (
                <div className="rounded-lg border border-red-200 bg-red-50/70 p-3 dark:border-red-900/60 dark:bg-red-950/30 sm:col-span-2">
                  <span className="text-red-700 dark:text-red-300 font-semibold block">Catatan Keputusan</span>
                  <p className="mt-1 whitespace-pre-wrap text-red-900 dark:text-red-200 leading-5">
                    {viewingRequest.decision_note}
                  </p>
                  {viewingRequest.decision_by_npk && (
                    <span className="text-muted text-[11px] block mt-1">
                      Diputus oleh NPK: {viewingRequest.decision_by_npk}
                    </span>
                  )}
                </div>
              )}
            </div>

            <div className="flex flex-wrap items-center justify-between gap-2 border-t border-outline/40 pt-4 dark:border-slate-800">
              <div className="flex gap-2">
                {canDecide && viewingRequest.status === 'pending' && (
                  <>
                    <Button
                      size="sm"
                      variant="success"
                      disabled={busy}
                      icon={<Check className="size-3.5" />}
                      onClick={() => {
                        const req = viewingRequest;
                        setViewingRequest(null);
                        decide(req, 'approved');
                      }}
                    >
                      Setujui
                    </Button>
                    <Button
                      size="sm"
                      variant="danger"
                      disabled={busy}
                      icon={<X className="size-3.5" />}
                      onClick={() => {
                        const req = viewingRequest;
                        setViewingRequest(null);
                        decide(req, 'rejected');
                      }}
                    >
                      Tolak
                    </Button>
                  </>
                )}
              </div>
              <Button
                type="button"
                variant="secondary"
                size="sm"
                onClick={() => setViewingRequest(null)}
              >
                Tutup
              </Button>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}
