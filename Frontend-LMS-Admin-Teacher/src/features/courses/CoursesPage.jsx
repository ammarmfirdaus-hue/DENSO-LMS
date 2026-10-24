import { useEffect, useMemo, useState } from 'react';
import { Link, useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

const field = 'h-11 w-full rounded-xl border border-outline/60 bg-white px-3 text-sm';

export default function CoursesPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [courses, setCourses] = useState([]);
  const [trainers, setTrainers] = useState([]);
  const [assignments, setAssignments] = useState({});
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [courseSearch, setCourseSearch] = useState('');
  const [category, setCategory] = useState('all');
  const filteredCourses = useMemo(() => courses.filter(course => (category === 'all' || course.category === category) && `${course.course_code} ${course.title} ${course.description || ''} ${course.assigned_trainer_name || ''}`.toLowerCase().includes(courseSearch.trim().toLowerCase())), [courses, category, courseSearch]);
  const coursePage = usePagination(filteredCourses, 9);

  async function load() {
    try {
      const courseResult = await api('/dashboard/courses');
      setCourses(courseResult.courses || []);
      if (isAdmin) {
        const trainerResult = await api('/material-workflow/trainers');
        setTrainers(trainerResult.trainers || []);
      }
      setError('');
    } catch (e) { setError(e.message); }
  }
  useEffect(() => { load(); }, []);

  async function createCourse(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = Object.fromEntries(new FormData(form));
    setBusy(true);
    setError('');
    try {
      await api('/courses', { method: 'POST', body: JSON.stringify(values) });
      form.reset();
      setNotice('Course berhasil dibuat. Buka detail course untuk menambah sesi dan materi.');
      showAlert.success('Course Berhasil Dibuat', `Course "${values.title}" telah dibuat. Buka detail course untuk menambahkan sesi dan materi.`);
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuat Course', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function assignTrainer(course) {
    setBusy(true);
    setError('');
    try {
      await api(`/courses/${course.course_id}/trainer`, {
        method: 'PUT',
        body: JSON.stringify({ trainerNpk: assignments[course.course_id] ?? course.assigned_trainer_npk ?? '' })
      });
      setNotice('Trainer course berhasil diperbarui.');
      showAlert.success('Trainer Berhasil Ditugaskan', `Penugasan trainer untuk course "${course.title}" telah diperbarui.`);
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menugaskan Trainer', e.message);
    } finally {
      setBusy(false);
    }
  }

  return <div className="mx-auto max-w-7xl">
    <header className="mb-7"><p className="text-xs font-bold uppercase tracking-[.18em] text-primary">PENGELOLAAN PEMBELAJARAN</p><h1 className="mt-2 text-3xl font-bold">Kelola course</h1><p className="mt-2 max-w-3xl text-sm leading-6 text-muted">Kelola katalog course, penugasan trainer, sesi, dan materi course. Pengajuan serta persetujuan materi tersedia di halaman Persetujuan Materi.</p></header>
    {error && <p role="alert" className="mb-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}{notice && <p role="status" className="mb-4 rounded-xl bg-green-50 p-4 text-green-800">{notice}</p>}
    {isAdmin && <section className="mb-7 rounded-2xl border border-outline/40 bg-white p-5 shadow-sm md:p-6"><div><h2 className="text-lg font-bold">Buat course baru</h2><p className="mt-1 text-sm text-muted">Course dan trainer yang ditugaskan akan tersinkron ke sistem student.</p></div><form onSubmit={createCourse} className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3"><input name="courseCode" required maxLength="30" placeholder="Kode course" className={field}/><input name="title" required maxLength="200" placeholder="Judul course" className={field}/><select name="category" className={field}><option>Wajib</option><option>Pilihan</option></select><input name="description" maxLength="4000" placeholder="Deskripsi course" className={field}/><select name="trainerNpk" className={field}><option value="">Pilih trainer (opsional)</option>{trainers.map(t => <option key={t.employee_npk} value={t.employee_npk}>{t.fullname}</option>)}</select><button disabled={busy} className="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white disabled:opacity-50">Buat course</button></form></section>}

    <section><div className="mb-4 flex items-center justify-between"><h2 className="text-xl font-bold">Semua course</h2><span className="rounded-full bg-white px-3 py-1 text-sm font-semibold text-muted shadow-sm">{filteredCourses.length} dari {courses.length} course</span></div><ListFilterBar search={courseSearch} onSearch={setCourseSearch} placeholder="Cari kode, judul, atau trainer..." filter={category} onFilter={setCategory} label="Filter kategori" options={[{value:'all',label:'Semua kategori'},...Array.from(new Set(courses.map(course => course.category).filter(Boolean))).map(value => ({value,label:value}))]}/>
      {filteredCourses.length ? <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">{coursePage.pagedItems.map((course,index) => <article key={course.course_id} className="group flex flex-col overflow-hidden rounded-2xl border border-outline/40 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"><div className="relative h-1.5 bg-primary/80" /><div className="flex flex-1 flex-col p-5"><div className="flex items-center justify-between gap-2"><span className="rounded-full bg-surface-low px-2.5 py-1 text-xs font-bold text-muted">{course.course_code}</span><span className="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">{course.category}</span></div><h3 className="mt-4 text-lg font-bold leading-snug"><Link to={`/courses/${course.course_id}`} className="outline-none group-hover:text-primary focus:text-primary">{course.title}</Link></h3><p className="mt-2 line-clamp-2 min-h-10 text-sm leading-5 text-muted">{course.description || 'Belum ada deskripsi course.'}</p><div className="mt-5 grid grid-cols-2 gap-2"><MiniStat label="Sesi" value={course.session_count}/><MiniStat label="Materi terbit" value={course.material_count}/></div>
        {isAdmin && <div className="mt-4 border-t border-outline/30 pt-4"><label className="text-xs font-semibold text-muted">Trainer yang ditugaskan</label><div className="mt-2 flex gap-2"><select aria-label={`Trainer ${course.title}`} value={assignments[course.course_id] ?? course.assigned_trainer_npk ?? ''} onChange={event => setAssignments(current => ({ ...current, [course.course_id]: event.target.value }))} className="h-10 min-w-0 flex-1 rounded-lg border border-outline/50 bg-white px-2 text-xs"><option value="">Tanpa trainer</option>{trainers.map(t => <option key={t.employee_npk} value={t.employee_npk}>{t.fullname}</option>)}</select><button disabled={busy} onClick={() => assignTrainer(course)} className="rounded-lg border border-outline/50 px-3 text-xs font-semibold hover:bg-surface-low">Simpan</button></div></div>}
        <Link to={`/courses/${course.course_id}`} className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#173e67] px-4 py-3 text-sm font-bold text-white transition group-hover:bg-primary">Buka detail course <span aria-hidden="true">→</span></Link>
      </div></article>)}</div> : <div className="rounded-2xl border border-dashed border-outline/50 bg-white p-10 text-center text-sm text-muted">{courses.length ? 'Tidak ada course yang cocok dengan pencarian/filter.' : 'Belum ada course yang tersedia untuk akun ini.'}</div>}<div className="mt-4 overflow-hidden rounded-2xl border border-outline/40 bg-white"><Pagination {...coursePage} onPageChange={coursePage.setPage} onPageSizeChange={coursePage.setPageSize} pageSizes={[9, 18, 36, 72]}/></div>
    </section>

  </div>;
}

function MiniStat({ label, value }) { return <div className="rounded-xl bg-surface-low px-3 py-2"><p className="text-lg font-bold">{value || 0}</p><p className="text-[11px] text-muted">{label}</p></div>; }
