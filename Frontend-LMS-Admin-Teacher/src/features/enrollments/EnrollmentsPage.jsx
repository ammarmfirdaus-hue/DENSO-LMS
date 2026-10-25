import { useEffect, useRef, useState } from 'react';
import { api } from '../../api/client';
import showAlert from '../../utils/alert';
import { useOutletContext } from 'react-router-dom';

const input = 'min-h-11 w-full rounded-lg border border-outline/60 bg-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10';
const button = 'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-outline/60 bg-white px-3 text-sm font-semibold text-on-surface hover:bg-surface-low disabled:cursor-not-allowed disabled:opacity-50';
const statusText = { pending: 'Menunggu Admin', approved: 'Disetujui', rejected: 'Ditolak' };

function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? value : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
}

export default function EnrollmentsPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [requests, setRequests] = useState([]);
  const [courses, setCourses] = useState([]);
  const [students, setStudents] = useState([]);
  const [studentSearch, setStudentSearch] = useState('');
  const [selectedStudent, setSelectedStudent] = useState(null);
  const [courseCode, setCourseCode] = useState('');
  const [note, setNote] = useState('');
  const [filter, setFilter] = useState('all');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const searchVersion = useRef(0);

  async function loadRequests(silent = false) {
    try {
      const result = await api(`/enrollments/requests?status=${filter}`);
      setRequests(result.requests || []);
      setError('');
    } catch (loadError) { if (!silent) setError(loadError.message); }
    finally { if (!silent) setLoading(false); }
  }

  useEffect(() => { loadRequests(); }, [filter]);

  useEffect(() => {
    if (isAdmin) return undefined;
    let mounted = true;
    api('/enrollments/options').then(result => {
      if (!mounted) return;
      setCourses(result.courses || []);
      setStudents(result.students || []);
      setError('');
    }).catch(loadError => { if (mounted) setError(loadError.message); });
    return () => { mounted = false; };
  }, [isAdmin]);

  useEffect(() => {
    if (isAdmin || !studentSearch.trim() || selectedStudent) return undefined;
    const version = ++searchVersion.current;
    const timer = setTimeout(async () => {
      try {
        const result = await api(`/enrollments/options?studentSearch=${encodeURIComponent(studentSearch.trim())}`);
        if (version === searchVersion.current) setStudents(result.students || []);
      } catch (loadError) { setError(loadError.message); }
    }, 250);
    return () => clearTimeout(timer);
  }, [isAdmin, studentSearch, selectedStudent]);

  useEffect(() => {
    const timer = setInterval(() => { if (!document.hidden) loadRequests(true); }, 15000);
    const refresh = () => { if (!document.hidden) loadRequests(true); };
    window.addEventListener('focus', refresh);
    return () => { clearInterval(timer); window.removeEventListener('focus', refresh); };
  }, [filter]);

  async function recommend(event) {
    event.preventDefault();
    if (!selectedStudent || !courseCode) {
      setError('Pilih satu Student dan course terlebih dahulu.');
      return;
    }
    setBusy(true); setError('');
    try {
      await api('/enrollments/recommendations', { method: 'POST', body: JSON.stringify({ studentNpk: selectedStudent.npk, courseCode, note }) });
      setSelectedStudent(null); setStudentSearch(''); setCourseCode(''); setNote('');
      await loadRequests(true);
      showAlert.success('Rekomendasi Dikirim', 'Admin dapat meninjau rekomendasi ini pada halaman Persetujuan Course.');
    } catch (requestError) { setError(requestError.message); showAlert.error('Rekomendasi Gagal', requestError.message); }
    finally { setBusy(false); }
  }

  async function decide(request, action) {
    let decisionNote = '';
    if (action === 'rejected') {
      decisionNote = await showAlert.prompt({
        title: 'Alasan Penolakan', text: `${request.student_name} · ${request.course_title}`,
        inputPlaceholder: 'Jelaskan alasan penolakan (minimal 3 karakter)', confirmText: 'Tolak Permintaan', danger: true
      });
      if (decisionNote === null) return;
      if (decisionNote.length < 3) { showAlert.error('Alasan Belum Cukup', 'Masukkan alasan penolakan minimal 3 karakter.'); return; }
    } else {
      const confirmed = await showAlert.confirm({
        title: 'Setujui Akses Course?', text: `${request.student_name} akan mendapatkan akses ke ${request.course_title}.`,
        confirmText: 'Setujui Akses', cancelText: 'Periksa Kembali', icon: 'question'
      });
      if (!confirmed) return;
    }
    setBusy(true); setError('');
    try {
      await api(`/enrollments/${request.request_id}/decision`, { method: 'POST', body: JSON.stringify({ action, note: decisionNote }) });
      await loadRequests(true);
      showAlert.success(action === 'approved' ? 'Akses Course Disetujui' : 'Permintaan Ditolak', action === 'approved' ? 'Course sekarang tampil dan dapat dipelajari oleh Student.' : 'Alasan penolakan tersimpan dan terlihat oleh Student.');
    } catch (decisionError) { setError(decisionError.message); showAlert.error('Keputusan Gagal Disimpan', decisionError.message); }
    finally { setBusy(false); }
  }

  const pendingCount = requests.filter(request => request.status === 'pending').length;
  return <div>
    <p className="text-sm font-semibold text-primary">MANAJEMEN</p>
    <h1 className="mt-2 text-3xl font-bold">Persetujuan Course</h1>
    <p className="mt-2 max-w-3xl text-sm leading-6 text-muted">Student perlu persetujuan sebelum membuka materi. Teacher dapat merekomendasikan Student untuk course yang menjadi tanggung jawabnya; keputusan akses tetap berada di Admin.</p>
    {error && <p role="alert" className="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{error}</p>}

    {!isAdmin && <form onSubmit={recommend} className="mt-6 grid gap-4 rounded-2xl border border-outline/50 bg-white p-5 shadow-sm lg:grid-cols-2">
      <div className="lg:col-span-2"><h2 className="font-bold">Rekomendasikan Student</h2><p className="mt-1 text-sm text-muted">Rekomendasi akan masuk ke antrean persetujuan Admin.</p></div>
      <div className="relative">
        <label htmlFor="student-search" className="mb-1.5 block text-sm font-semibold">Cari Student aktif</label>
        {selectedStudent ? <div className="flex min-h-11 items-center justify-between gap-3 rounded-lg border border-primary/30 bg-primary/5 px-3 text-sm"><span className="truncate"><strong>{selectedStudent.fullname}</strong><span className="ml-2 text-muted">{selectedStudent.npk}</span></span><button type="button" className="font-semibold text-primary" onClick={() => { setSelectedStudent(null); setStudentSearch(''); }}>Ubah</button></div> : <>
          <input id="student-search" value={studentSearch} onChange={event => setStudentSearch(event.target.value)} className={input} placeholder="Masukkan NPK atau nama" autoComplete="off"/>
          {studentSearch.trim() && <div className="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-outline/60 bg-white shadow-lg">{students.length ? students.map(student => <button key={student.npk} type="button" onClick={() => { setSelectedStudent(student); setStudentSearch(`${student.fullname} (${student.npk})`); }} className="block w-full border-b border-outline/30 px-3 py-2.5 text-left text-sm last:border-0 hover:bg-surface-low"><span className="font-semibold">{student.fullname}</span><span className="ml-2 text-muted">{student.npk}</span></button>) : <p className="p-3 text-sm text-muted">Student aktif tidak ditemukan.</p>}</div>}
        </>}
      </div>
      <div><label htmlFor="course-select" className="mb-1.5 block text-sm font-semibold">Course dalam tanggung jawab Anda</label><select id="course-select" required value={courseCode} onChange={event => setCourseCode(event.target.value)} className={input}><option value="">Pilih course</option>{courses.map(course => <option key={course.course_code} value={course.course_code}>{course.title} ({course.course_code})</option>)}</select>{!courses.length && <p className="mt-1 text-xs text-muted">Belum ada course aktif yang ditugaskan atau dibuat oleh Anda.</p>}</div>
      <div className="lg:col-span-2"><label htmlFor="recommendation-note" className="mb-1.5 block text-sm font-semibold">Catatan untuk Admin (opsional)</label><textarea id="recommendation-note" maxLength={1000} value={note} onChange={event => setNote(event.target.value)} className={`${input} min-h-20 resize-y`} placeholder="Contoh: Student perlu mengikuti course ini untuk kompetensi pekerjaan."/></div>
      <div className="lg:col-span-2"><button disabled={busy || !courses.length} className="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"><span className="material-symbol" aria-hidden="true">send</span>{busy ? 'Mengirim...' : 'Kirim Rekomendasi'}</button></div>
    </form>}

    <section className="mt-6 overflow-hidden rounded-2xl border border-outline/50 bg-white shadow-sm">
      <div className="flex flex-wrap items-center justify-between gap-3 border-b border-outline/40 p-5"><div><h2 className="font-bold">{isAdmin ? 'Daftar Permintaan Akses' : 'Rekomendasi Saya'}</h2><p className="mt-1 text-sm text-muted">{isAdmin ? `${pendingCount} permintaan menunggu keputusan.` : 'Status rekomendasi diperbarui otomatis setiap 15 detik.'}</p></div><label className="flex items-center gap-2 text-sm"><span className="text-muted">Status</span><select value={filter} onChange={event => setFilter(event.target.value)} className="min-h-10 rounded-lg border border-outline/60 bg-white px-3"><option value="all">Semua</option><option value="pending">Menunggu</option><option value="approved">Disetujui</option><option value="rejected">Ditolak</option></select></label></div>
      {loading ? <p className="p-8 text-center text-sm text-muted">Memuat permintaan...</p> : !requests.length ? <div className="p-10 text-center"><span className="material-symbol text-3xl text-muted" aria-hidden="true">inbox</span><p className="mt-2 text-sm text-muted">Belum ada permintaan pada status ini.</p></div> : <div className="max-w-full overflow-x-auto overscroll-x-contain" role="region" aria-label="Tabel permintaan akses course" tabIndex={0}><table className="w-full min-w-[850px] border-collapse text-left text-sm"><thead className="bg-surface-low text-xs uppercase tracking-wide text-muted"><tr><th className="px-5 py-3">Student</th><th className="px-4 py-3">Course</th><th className="px-4 py-3">Sumber</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Tanggal</th><th className="px-5 py-3 text-right">Aksi</th></tr></thead><tbody className="divide-y divide-outline/40">{requests.map(request => <tr key={request.request_id} className="align-top"><td className="px-5 py-4"><p className="font-semibold">{request.student_name}</p><p className="mt-1 text-xs text-muted">NPK: {request.student_npk}</p>{request.request_note && <p className="mt-2 max-w-xs whitespace-pre-wrap text-xs leading-5 text-secondary">{request.request_note}</p>}{request.status === 'rejected' && request.decision_note && <p className="mt-2 max-w-xs rounded-md bg-red-50 px-2 py-1.5 text-xs text-red-800">Alasan: {request.decision_note}</p>}</td><td className="px-4 py-4"><p className="font-semibold">{request.course_title}</p><p className="mt-1 text-xs text-muted">{request.course_code}</p></td><td className="px-4 py-4"><p>{request.request_type === 'teacher_recommendation' ? 'Rekomendasi Teacher' : 'Permintaan Student'}</p><p className="mt-1 text-xs text-muted">{request.requested_by_npk}</p></td><td className="px-4 py-4"><span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${request.status === 'approved' ? 'bg-emerald-50 text-emerald-700' : request.status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-800'}`}>{statusText[request.status]}</span></td><td className="px-4 py-4 whitespace-nowrap text-xs text-muted">{formatDate(request.requested_at)}</td><td className="px-5 py-4 text-right">{isAdmin && request.status === 'pending' ? <div className="inline-flex flex-wrap justify-end gap-2"><button type="button" disabled={busy} onClick={() => decide(request, 'approved')} className="inline-flex min-h-9 items-center gap-1 rounded-lg bg-emerald-700 px-3 text-xs font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">check</span>Setujui</button><button type="button" disabled={busy} onClick={() => decide(request, 'rejected')} className="inline-flex min-h-9 items-center gap-1 rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">close</span>Tolak</button></div> : <span className="text-xs text-muted">{request.decision_by_npk ? `Diputus ${request.decision_by_npk}` : '—'}</span>}</td></tr>)}</tbody></table></div>}
    </section>
  </div>;
}
