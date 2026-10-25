import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';
import { buildGradesXlsx } from './grade-export';

const inputClass = 'mt-1.5 h-11 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10';
const buttonClass = 'min-h-9 rounded-lg border border-outline/60 px-3 text-xs font-semibold hover:bg-surface-low disabled:opacity-50';

function exportGrades(rows) {
  const workbook = buildGradesXlsx(rows);
  const url = URL.createObjectURL(new Blob([workbook], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }));
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = `rekap-nilai-student-${new Date().toISOString().slice(0, 10)}.xlsx`;
  anchor.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

export default function GradesPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [rows, setRows] = useState([]);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('all');
  const [busy, setBusy] = useState(false);
  const [selectedGrades, setSelectedGrades] = useState(() => new Set());
  const [editing, setEditing] = useState(null);
  const [notifying, setNotifying] = useState(null);
  const [sendEmail, setSendEmail] = useState(false);

  async function load() {
    try { setRows((await api('/grades')).grades || []); setError(''); }
    catch (e) { setError(e.message); }
  }
  useEffect(() => { load(); }, []);
  useEffect(() => {
    window.addEventListener('dls_job_settled', load);
    return () => window.removeEventListener('dls_job_settled', load);
  }, []);

  const statuses = useMemo(() => [...new Set(rows.map(row => row.enrollment_status).filter(Boolean))].sort(), [rows]);
  const filtered = useMemo(() => rows.filter(row => (status === 'all' || row.enrollment_status === status)
    && `${row.student_npk} ${row.course_title || ''} ${row.course_code || ''} ${row.enrollment_status}`.toLowerCase().includes(search.trim().toLowerCase())), [rows, search, status]);
  const page = usePagination(filtered);
  const selectedFiltered = filtered.filter(row => selectedGrades.has(`${row.student_npk}::${row.course_code}`));

  function toggleGradeSelection(row) {
    const key = `${row.student_npk}::${row.course_code}`;
    setSelectedGrades(previous => {
      const next = new Set(previous);
      if (next.has(key)) next.delete(key); else next.add(key);
      return next;
    });
  }

  function toggleAllFilteredGrades() {
    setSelectedGrades(previous => {
      const next = new Set(previous);
      const allSelected = filtered.length > 0 && filtered.every(row => next.has(`${row.student_npk}::${row.course_code}`));
      filtered.forEach(row => {
        const key = `${row.student_npk}::${row.course_code}`;
        if (allSelected) next.delete(key); else next.add(key);
      });
      return next;
    });
  }

  async function deleteSelectedGrades() {
    if (!isAdmin || !selectedFiltered.length) return;
    const confirmed = await showAlert.confirm({
      title: `Hapus ${selectedFiltered.length} nilai?`,
      text: 'Nilai dan umpan balik akan dikosongkan. Data progres course student tetap disimpan.',
      confirmText: `Proses ${selectedFiltered.length} Nilai`, cancelText: 'Batal', danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      const result = await api('/jobs/records/delete', { method: 'POST', body: JSON.stringify({ items: selectedFiltered.map(row => ({
        kind: 'grade', npk: row.student_npk, courseCode: row.course_code,
        label: `${row.student_npk} · ${row.course_title || row.course_code}`
      })) }) });
      setSelectedGrades(new Set());
      showAlert.success('Penghapusan Masuk Antrean', `${result.total} nilai akan diproses satu per satu. Progress tersimpan jika halaman dimuat ulang.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Membuat Antrean Hapus', e.message); }
    finally { setBusy(false); }
  }

  async function saveGrade(event) {
    event.preventDefault();
    setBusy(true); setError(''); setNotice('');
    try {
      await api(`/grades/${encodeURIComponent(editing.student_npk)}/${encodeURIComponent(editing.course_code)}`, {
        method: 'PATCH', body: JSON.stringify({ score: Number(editing.score), feedback: editing.feedback || '' })
      });
      setEditing(null); setNotice('Nilai berhasil diperbarui.'); await load();
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function deleteGrade(row) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Nilai?',
      text: `Nilai ${row.student_npk} untuk ${row.course_title || row.course_code} akan dihapus. Progres course tetap disimpan.`,
      confirmText: 'Ya, Hapus Nilai',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      await api(`/grades/${encodeURIComponent(row.student_npk)}/${encodeURIComponent(row.course_code)}`, { method: 'DELETE' });
      setNotice('Nilai dihapus; data progres tetap tersimpan.'); await load();
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function sendNotification(event) {
    event.preventDefault();
    setBusy(true); setError(''); setNotice('');
    const form = new FormData(event.currentTarget);
    try {
      const result = await api(`/grades/${encodeURIComponent(notifying.student_npk)}/${encodeURIComponent(notifying.course_code)}/notify`, {
        method: 'POST', body: JSON.stringify({ title: form.get('title'), message: form.get('message'), sendEmail })
      });
      setNotifying(null); setSendEmail(false);
      setNotice(`Notifikasi terkirim ke ${notifying.student_npk}.${result.emailQueued ? ' Email masuk antrean.' : ''}`);
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  return <div className="mx-auto min-w-0 max-w-7xl">
    <p className="text-sm font-semibold text-primary">PEMBELAJARAN</p>
    <div className="mt-2 flex flex-wrap items-end justify-between gap-3">
      <div><h1 className="text-3xl font-bold">Kelola Nilai</h1><p className="mt-2 text-muted">Nilai kursus Student dan progres tersinkron.</p></div>
      <button type="button" onClick={() => exportGrades(filtered)} disabled={!filtered.length} className="min-h-11 rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50">Ekspor Excel</button>
    </div>
    {error && <p role="alert" className="mt-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}
    {notice && <p role="status" className="mt-4 rounded-xl bg-green-50 p-4 text-green-800">{notice}</p>}

    <div className="mt-7 overflow-hidden rounded-2xl border border-outline/50 bg-white">
      <div className="p-5 pb-1"><div className="mb-3 flex flex-wrap items-center justify-between gap-3"><p className="text-sm text-muted">Menampilkan {filtered.length} dari {rows.length} data nilai/progres</p>{isAdmin && selectedFiltered.length > 0 && <button type="button" disabled={busy} onClick={deleteSelectedGrades} className="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-red-700 bg-red-700 px-4 text-sm font-semibold text-white hover:bg-red-800 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">delete_sweep</span>Hapus Nilai Terpilih ({selectedFiltered.length})</button>}</div>
        <ListFilterBar search={search} onSearch={setSearch} placeholder="Cari NPK atau course..." filter={status} onFilter={setStatus} label="Filter status" options={[{ value: 'all', label: 'Semua status' }, ...statuses.map(value => ({ value, label: value }))]} />
      </div>
      <div className="max-w-full overflow-x-auto overscroll-x-contain" role="region" aria-label="Tabel nilai student" tabIndex={0}><table className="w-full min-w-[1160px] text-left text-sm">
        <thead className="bg-surface-low text-muted"><tr>{isAdmin && <th className="w-12 px-4 py-4"><input type="checkbox" aria-label="Pilih semua nilai hasil filter" disabled={!filtered.length || busy} checked={filtered.length > 0 && selectedFiltered.length === filtered.length} onChange={toggleAllFilteredGrades} className="size-4 accent-primary" /></th>}<th className="px-4 py-4">NPK</th><th className="px-4 py-4">Course</th><th className="px-4 py-4">Nilai Quiz</th><th className="px-4 py-4">Nilai Course</th><th className="px-4 py-4">Progres</th><th className="px-4 py-4">Status</th><th className="px-4 py-4">Diperbarui</th><th className="px-4 py-4">Aksi</th></tr></thead>
        <tbody className="divide-y divide-outline/40">{page.pagedItems.map((row, index) => <tr key={`${row.student_npk}-${row.course_code}-${index}`}>
          {isAdmin && <td className="px-4 py-4"><input type="checkbox" aria-label={`Pilih nilai ${row.student_npk} ${row.course_title || row.course_code}`} disabled={busy} checked={selectedGrades.has(`${row.student_npk}::${row.course_code}`)} onChange={() => toggleGradeSelection(row)} className="size-4 accent-primary" /></td>}
          <td className="px-4 py-4 font-semibold">{row.student_npk}</td><td className="px-4 py-4">{row.course_title || row.course_code}</td>
          <td className="px-4 py-4">{row.quiz_score == null ? <span className="text-muted">Belum dikerjakan</span> : <span className="font-bold text-primary">{Number(row.quiz_score).toLocaleString('id-ID')}</span>}</td>
          <td className="px-4 py-4">{row.score === null || row.score === undefined ? <span className="text-muted">Belum dinilai</span> : <span className="font-bold">{Number(row.score).toLocaleString('id-ID')}</span>}{row.grade_feedback && <p className="mt-1 max-w-xs truncate text-xs text-muted" title={row.grade_feedback}>{row.grade_feedback}</p>}</td>
          <td className="px-4 py-4">{row.progress}%</td><td className="px-4 py-4 capitalize">{row.enrollment_status}</td>
          <td className="px-4 py-4">{row.graded_at ? new Date(row.graded_at).toLocaleString('id-ID') : new Date(row.synced_at).toLocaleString('id-ID')}</td>
          <td className="px-4 py-3"><div className="flex flex-wrap gap-2">
            <button type="button" disabled={busy} onClick={() => setEditing({ ...row, score: row.score ?? '', feedback: row.grade_feedback || '' })} className={buttonClass}>Edit nilai</button>
            {isAdmin && <button type="button" disabled={busy} onClick={() => deleteGrade(row)} className={`${buttonClass} border-red-200 bg-white text-red-700 hover:bg-red-50 disabled:opacity-50`}>Hapus</button>}
            <button type="button" disabled={busy} onClick={() => { setNotifying(row); setSendEmail(false); }} className={buttonClass}>Kirim pesan</button>
          </div></td>
        </tr>)}</tbody>
      </table></div>
      {!filtered.length && <p className="p-8 text-center text-muted">{rows.length ? 'Tidak ada data yang cocok dengan pencarian/filter.' : 'Belum ada data progres tersinkron. Pastikan sinkronisasi Student aktif.'}</p>}
      <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
    </div>

    {editing && <div className="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" role="presentation"><form onSubmit={saveGrade} className="w-full max-w-lg rounded-2xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="edit-grade-title">
      <h2 id="edit-grade-title" className="text-xl font-bold">Edit nilai</h2><p className="mt-1 text-sm text-muted">{editing.student_npk} · {editing.course_title || editing.course_code}</p>
      <label className="mt-4 block text-sm font-semibold">Nilai (0–100)<input autoFocus required type="number" min="0" max="100" step="0.01" value={editing.score} onChange={event => setEditing({ ...editing, score: event.target.value })} className={inputClass} /></label>
      <label className="mt-4 block text-sm font-semibold">Umpan balik<textarea maxLength="1000" value={editing.feedback} onChange={event => setEditing({ ...editing, feedback: event.target.value })} rows="3" className="mt-1.5 w-full rounded-lg border border-outline/60 p-3 text-sm" /></label>
      <div className="mt-5 flex justify-end gap-2"><button type="button" onClick={() => setEditing(null)} className={buttonClass}>Batal</button><button disabled={busy} className="min-h-10 rounded-lg bg-primary px-4 text-sm font-semibold text-white">Simpan nilai</button></div>
    </form></div>}

    {notifying && <div className="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" role="presentation"><form onSubmit={sendNotification} className="w-full max-w-lg rounded-2xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="notify-grade-title">
      <h2 id="notify-grade-title" className="text-xl font-bold">Kirim pesan ke Student</h2><p className="mt-1 text-sm text-muted">Penerima: {notifying.student_npk} · {notifying.course_title || notifying.course_code}</p>
      <label className="mt-4 block text-sm font-semibold">Judul<input name="title" required minLength="3" maxLength="180" defaultValue={notifying.score == null ? `Pengingat course ${notifying.course_title || notifying.course_code}` : `Nilai course ${notifying.course_title || notifying.course_code}`} className={inputClass} /></label>
      <label className="mt-4 block text-sm font-semibold">Pesan<textarea name="message" required maxLength="2000" rows="4" defaultValue={notifying.score == null ? `Mohon selesaikan tugas pada course ${notifying.course_title || notifying.course_code}.` : `Nilai Anda untuk course ${notifying.course_title || notifying.course_code} adalah ${notifying.score}. ${notifying.grade_feedback || ''}`} className="mt-1.5 w-full rounded-lg border border-outline/60 p-3 text-sm" /></label>
      <label className="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-outline/40 p-3 text-sm"><input type="checkbox" checked={sendEmail} onChange={event => setSendEmail(event.target.checked)} className="mt-0.5 accent-primary" /><span><strong>Kirim juga melalui email</strong><span className="mt-0.5 block text-xs text-muted">Email masuk antrean jika SMTP aktif dan Student memiliki email.</span></span></label>
      <div className="mt-5 flex justify-end gap-2"><button type="button" onClick={() => setNotifying(null)} className={buttonClass}>Batal</button><button disabled={busy} className="min-h-10 rounded-lg bg-primary px-4 text-sm font-semibold text-white">Kirim</button></div>
    </form></div>}
  </div>;
}
