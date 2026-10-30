import { useEffect, useMemo, useState } from 'react';
import { Link, useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';

const field = 'h-11 w-full rounded-xl border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Course Management page for Admin and Teacher roles.
 * Allows catalog browsing, trainer assignment, and course creation.
 */
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

  const filteredCourses = useMemo(() => courses.filter(course => (
    category === 'all' || course.category === category
  ) && `${course.course_code} ${course.title} ${course.description || ''} ${course.assigned_trainer_name || ''}`.toLowerCase().includes(courseSearch.trim().toLowerCase())), [courses, category, courseSearch]);

  const coursePage = usePagination(filteredCourses, 9);

  // Load course catalog and trainer list
  async function load() {
    try {
      const courseResult = await api('/dashboard/courses');
      setCourses(courseResult.courses || []);
      if (isAdmin) {
        const trainerResult = await api('/material-workflow/trainers');
        setTrainers(trainerResult.trainers || []);
      }
      setError('');
    } catch (e) {
      setError(e.message);
    }
  }

  useEffect(() => {
    load();
  }, []);

  // Handler for creating a new course entry
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

  // Handler for assigning trainer to course
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

  return (
    <div className="mx-auto max-w-7xl">
      {/* Standard Unified Header */}
      <PageHeader
        eyebrow="PENGELOLAAN PEMBELAJARAN"
        title="Kelola Course"
        description="Kelola katalog course, penugasan trainer, sesi, dan materi course. Pengajuan serta persetujuan materi tersedia di halaman Persetujuan Materi."
        actions={
          <span className="rounded-xl border border-outline/50 bg-white px-3.5 py-2 text-xs font-semibold text-muted shadow-xs dark:border-slate-800 dark:bg-slate-900">
            Total {courses.length} course terdaftar
          </span>
        }
      />

      {error && (
        <p role="alert" className="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
          {error}
        </p>
      )}
      {notice && (
        <p role="status" className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
          {notice}
        </p>
      )}

      {/* Admin: Course Creation Section */}
      {isAdmin && (
        <section className="mb-7 rounded-2xl border border-outline/40 bg-white p-5 shadow-xs sm:p-6 dark:border-slate-800 dark:bg-[#1e293b]">
          <div>
            <h2 className="text-lg font-bold text-on-surface">Buat Course Baru</h2>
            <p className="mt-1 text-sm text-muted">Course dan trainer yang ditugaskan akan tersinkron ke sistem student.</p>
          </div>
          <form onSubmit={createCourse} className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <input name="courseCode" required maxLength="30" placeholder="Kode course (contoh: DENSO-SEC-01)" className={field} />
            <input name="title" required maxLength="200" placeholder="Judul course" className={field} />
            <select name="category" className={field}>
              <option value="Wajib">Wajib</option>
              <option value="Pilihan">Pilihan</option>
            </select>
            <input name="description" maxLength="4000" placeholder="Deskripsi ringkas course" className={field} />
            <select name="trainerNpk" className={field}>
              <option value="">Pilih trainer (opsional)</option>
              {trainers.map(t => <option key={t.employee_npk} value={t.employee_npk}>{t.fullname} ({t.employee_npk})</option>)}
            </select>
            <Button type="submit" disabled={busy} loading={busy} icon="add">
              Buat Course
            </Button>
          </form>
        </section>
      )}

      {/* Catalog & Filter Section */}
      <section>
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
          <h2 className="text-xl font-bold text-on-surface">Katalog Course</h2>
          <span className="rounded-full bg-white px-3 py-1 text-xs font-semibold text-muted shadow-xs dark:bg-slate-800">
            {filteredCourses.length} dari {courses.length} course
          </span>
        </div>

        <ListFilterBar
          search={courseSearch}
          onSearch={setCourseSearch}
          placeholder="Cari kode, judul, atau trainer..."
          filter={category}
          onFilter={setCategory}
          label="Filter kategori"
          options={[
            { value: 'all', label: 'Semua kategori' },
            ...Array.from(new Set(courses.map(course => course.category).filter(Boolean))).map(value => ({ value, label: value }))
          ]}
        />

        {filteredCourses.length ? (
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {coursePage.pagedItems.map(course => (
              <article
                key={course.course_id}
                className="group flex flex-col overflow-hidden rounded-2xl border border-outline/40 bg-white shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-[#1e293b]"
              >
                <div className="h-1.5 bg-primary/80" />
                <div className="flex flex-1 flex-col p-5">
                  <div className="flex items-center justify-between gap-2">
                    <span className="rounded-md bg-surface-low px-2 py-0.5 text-xs font-bold text-muted dark:bg-slate-800 dark:text-slate-300">
                      {course.course_code}
                    </span>
                    <span className="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary dark:bg-red-950/40 dark:text-red-300">
                      {course.category}
                    </span>
                  </div>

                  <h3 className="mt-3 text-lg font-bold leading-snug">
                    <Link to={`/courses/${course.course_id}`} className="outline-none group-hover:text-primary transition focus-visible:text-primary">
                      {course.title}
                    </Link>
                  </h3>
                  <p className="mt-2 line-clamp-2 min-h-10 text-sm leading-relaxed text-muted">
                    {course.description || 'Belum ada deskripsi spesifik untuk course ini.'}
                  </p>

                  <div className="mt-4 grid grid-cols-2 gap-2">
                    <MiniStat label="Sesi" value={course.session_count} />
                    <MiniStat label="Materi terbit" value={course.material_count} />
                  </div>

                  {isAdmin && (
                    <div className="mt-4 border-t border-outline/30 pt-3 dark:border-slate-800">
                      <label className="text-xs font-semibold text-muted">Trainer Penanggung Jawab</label>
                      <div className="mt-2 flex gap-2">
                        <select
                          aria-label={`Trainer ${course.title}`}
                          value={assignments[course.course_id] ?? course.assigned_trainer_npk ?? ''}
                          onChange={event => setAssignments(current => ({ ...current, [course.course_id]: event.target.value }))}
                          className="h-9 min-w-0 flex-1 rounded-lg border border-outline/50 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-900"
                        >
                          <option value="">Tanpa trainer</option>
                          {trainers.map(t => (
                            <option key={t.employee_npk} value={t.employee_npk}>{t.fullname}</option>
                          ))}
                        </select>
                        <Button
                          size="sm"
                          variant="outline"
                          disabled={busy}
                          onClick={() => assignTrainer(course)}
                        >
                          Simpan
                        </Button>
                      </div>
                    </div>
                  )}

                  <Link
                    to={`/courses/${course.course_id}`}
                    className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#173e67] px-4 py-2.5 text-sm font-bold text-white transition group-hover:bg-primary shadow-xs"
                  >
                    <span>Buka Detail Course</span>
                    <span className="material-symbol text-base" aria-hidden="true">arrow_forward</span>
                  </Link>
                </div>
              </article>
            ))}
          </div>
        ) : (
          <div className="rounded-2xl border border-dashed border-outline/50 bg-white p-12 text-center text-sm text-muted dark:border-slate-800 dark:bg-[#1e293b]">
            <span className="material-symbol text-3xl mb-2 text-muted" aria-hidden="true">menu_book</span>
            <p>{courses.length ? 'Tidak ada course yang cocok dengan pencarian/filter.' : 'Belum ada course yang tersedia untuk akun ini.'}</p>
          </div>
        )}

        <div className="mt-4 overflow-hidden rounded-2xl border border-outline/40 bg-white dark:border-slate-800 dark:bg-[#1e293b]">
          <Pagination
            {...coursePage}
            onPageChange={coursePage.setPage}
            onPageSizeChange={coursePage.setPageSize}
            pageSizes={[9, 18, 36, 72]}
          />
        </div>
      </section>
    </div>
  );
}

/** Stat snippet widget within course cards */
function MiniStat({ label, value }) {
  return (
    <div className="rounded-xl bg-surface-low px-3 py-2 dark:bg-slate-800/60">
      <p className="text-base font-bold text-on-surface">{value || 0}</p>
      <p className="text-[11px] text-muted">{label}</p>
    </div>
  );
}
