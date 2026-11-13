import { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useOutletContext } from 'react-router-dom';
import {
  ArrowRight,
  BookOpen,
  FileCheck2,
  ImagePlus,
  Layers,
  Plus,
  ShieldCheck,
  UserCheck,
  X
} from 'lucide-react';
import { api, resolveCourseThumbnailUrl } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';
import { hasPermission } from '../../utils/access-control';

const FIELD_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Halaman Manajemen Course untuk peran Admin dan Pengajar.
 * Menyediakan katalog, pembuatan course baru, serta penugasan trainer.
 */
export default function CoursesPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canCreateCourse = hasPermission(user, 'courses.create');
  const canAssignTrainer = hasPermission(user, 'courses.assign_trainer');
  const canUploadCover = hasPermission(user, 'courses.manage_cover');
  const [courses, setCourses] = useState([]);
  const [trainers, setTrainers] = useState([]);
  const [assignments, setAssignments] = useState({});
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [loading, setLoading] = useState(true);
  const [courseSearch, setCourseSearch] = useState('');
  const [category, setCategory] = useQueryParamState('category', 'all');
  const [coverFile, setCoverFile] = useState(null);
  const [coverPreview, setCoverPreview] = useState('');
  const coverInputRef = useRef(null);

  useEffect(() => {
    return () => {
      if (coverPreview) URL.revokeObjectURL(coverPreview);
    };
  }, [coverPreview]);

  function handleSelectCover(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
      showAlert.error('Format Tidak Didukung', 'Pilih file gambar JPG, PNG, GIF, atau WebP.');
      event.target.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showAlert.error('Ukuran Terlalu Besar', 'Ukuran foto sampul maksimal 5 MB.');
      event.target.value = '';
      return;
    }
    if (coverPreview) URL.revokeObjectURL(coverPreview);
    setCoverFile(file);
    setCoverPreview(URL.createObjectURL(file));
  }

  function clearSelectedCover() {
    if (coverPreview) URL.revokeObjectURL(coverPreview);
    setCoverFile(null);
    setCoverPreview('');
    if (coverInputRef.current) coverInputRef.current.value = '';
  }

  const filteredCourses = useMemo(
    () =>
      courses.filter(
        (course) =>
          (category === 'all' || course.category === category) &&
          `${course.course_code} ${course.title} ${course.description || ''} ${course.assigned_trainer_name || ''}`
            .toLowerCase()
            .includes(courseSearch.trim().toLowerCase())
      ),
    [courses, category, courseSearch]
  );

  const coursePage = usePagination(filteredCourses, 9);

  const summary = useMemo(() => {
    const mandatoryCount = courses.filter((c) => c.category === 'Wajib').length;
    const totalSessions = courses.reduce((acc, c) => acc + Number(c.session_count || 0), 0);
    const totalMaterials = courses.reduce((acc, c) => acc + Number(c.material_count || 0), 0);
    return {
      total: courses.length,
      mandatoryCount,
      totalSessions,
      totalMaterials
    };
  }, [courses]);

  async function load() {
    setLoading(true);
    try {
      const courseResult = await api('/dashboard/courses');
      setCourses(courseResult.courses || []);
      if (canCreateCourse || canAssignTrainer) {
        const trainerResult = await api('/material-workflow/trainers');
        setTrainers(trainerResult.trainers || []);
      }
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Daftar Course', e.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  async function createCourse(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = Object.fromEntries(new FormData(form));
    setBusy(true);
    setError('');
    try {
      const created = await api('/courses', { method: 'POST', body: JSON.stringify(values) });
      let uploadWarning = '';
      if (coverFile && created?.course?.course_id) {
        try {
          const fd = new FormData();
          fd.append('thumbnail', coverFile);
          await api(`/courses/${created.course.course_id}/thumbnail`, { method: 'POST', body: fd });
        } catch (uploadErr) {
          uploadWarning = uploadErr.message;
        }
      }
      form.reset();
      clearSelectedCover();
      if (uploadWarning) {
        setNotice(`Course dibuat, namun sampul gagal diunggah (${uploadWarning}). Anda dapat mengunggah ulang dari Detail Course.`);
        showAlert.warning(
          'Course Dibuat Tanpa Sampul',
          `Course "${values.title}" telah dibuat, tetapi foto sampul gagal diunggah: ${uploadWarning}`
        );
      } else {
        setNotice('Course berhasil dibuat. Buka detail course untuk menambah sesi dan materi.');
        showAlert.success(
          'Course Berhasil Dibuat',
          `Course "${values.title}" telah dibuat. Buka detail course untuk menambahkan sesi dan materi.`
        );
      }
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
        body: JSON.stringify({
          trainerNpk: assignments[course.course_id] ?? course.assigned_trainer_npk ?? ''
        })
      });
      setNotice('Trainer course berhasil diperbarui.');
      showAlert.success(
        'Trainer Berhasil Ditugaskan',
        `Penugasan trainer untuk course "${course.title}" telah diperbarui.`
      );
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menugaskan Trainer', e.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Manajemen Pembelajaran"
        title="Kelola Course"
        description="Kelola katalog course, penugasan trainer, struktur sesi, serta pantau kelengkapan materi terbit."
        actions={
          <div className="flex flex-wrap items-center gap-2">
            <span className="inline-flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-lowest px-3.5 py-2 text-xs font-semibold text-on-surface-variant dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
              <BookOpen className="size-3.5 text-primary" />
              Total {courses.length} course terdaftar
            </span>
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

      {notice && (
        <div
          role="status"
          className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
          {notice}
        </div>
      )}

      {/* Ringkasan Katalog */}
      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Total Course"
          value={summary.total}
          icon={<BookOpen className="size-5" />}
          tone="secondary"
          subtext="Seluruh course aktif di katalog"
          loading={loading}
        />
        <StatCard
          label="Kategori Wajib"
          value={summary.mandatoryCount}
          icon={<ShieldCheck className="size-5" />}
          tone="primary"
          subtext="Kurikulum pelatihan wajib"
          loading={loading}
        />
        <StatCard
          label="Total Sesi"
          value={summary.totalSessions}
          icon={<Layers className="size-5" />}
          tone="info"
          subtext="Pertemuan di semua course"
          loading={loading}
        />
        <StatCard
          label="Materi Terbit"
          value={summary.totalMaterials}
          icon={<FileCheck2 className="size-5" />}
          tone="tertiary"
          subtext="Siap diakses oleh peserta"
          loading={loading}
        />
      </section>

      {/* Form Pembuatan Course */}
      {canCreateCourse && (
        <div id="create-course" className="scroll-mt-20">
          <Card
            title="Buat Course Baru"
            subtitle="Course dan trainer yang ditugaskan akan langsung tersinkron ke sistem student."
          >
            <form onSubmit={createCourse} className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              <div>
                <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Kode Course *
                </label>
                <input
                  name="courseCode"
                  required
                  maxLength="30"
                  placeholder="Contoh: DENSO-SEC-01"
                  className={FIELD_CLASS}
                />
              </div>

              <div>
                <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Judul Course *
                </label>
                <input
                  name="title"
                  required
                  maxLength="200"
                  placeholder="Masukkan judul pelatihan"
                  className={FIELD_CLASS}
                />
              </div>

              <div>
                <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Kategori
                </label>
                <select name="category" className={FIELD_CLASS}>
                  <option value="Wajib">Wajib</option>
                  <option value="Pilihan">Pilihan</option>
                </select>
              </div>

              <div className="md:col-span-2 xl:col-span-1">
                <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Deskripsi Ringkas
                </label>
                <input
                  name="description"
                  maxLength="4000"
                  placeholder="Tujuan dan cakupan materi course"
                  className={FIELD_CLASS}
                />
              </div>

              <div>
                <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Trainer Penanggung Jawab
                </label>
                <select name="trainerNpk" className={FIELD_CLASS}>
                  <option value="">Pilih trainer (opsional)</option>
                  {trainers.map((t) => (
                    <option key={t.employee_npk} value={t.employee_npk}>
                      {t.fullname} ({t.employee_npk})
                    </option>
                  ))}
                </select>
              </div>

              <div className="md:col-span-2 xl:col-span-3">
                <label className="mb-1.5 block text-xs font-semibold text-on-surface-variant">
                  Foto Sampul Course (Opsional · JPG/PNG/GIF/WebP, maks. 5 MB)
                </label>
                <div className="flex flex-wrap items-center gap-3">
                  <label className="inline-flex h-10 cursor-pointer items-center gap-2 rounded-md border border-outline bg-surface-lowest px-3.5 text-xs font-semibold text-on-surface transition hover:border-primary/50 hover:text-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    <ImagePlus className="size-4 text-primary" />
                    <span>{coverFile ? coverFile.name : 'Pilih Foto Sampul'}</span>
                    <input
                      ref={coverInputRef}
                      type="file"
                      accept="image/jpeg,image/png,image/gif,image/webp"
                      onChange={handleSelectCover}
                      className="sr-only"
                    />
                  </label>
                  {coverPreview && (
                    <div className="flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-low p-1.5 dark:border-slate-700 dark:bg-slate-800/70">
                      <img
                        src={coverPreview}
                        alt="Pratinjau sampul course"
                        className="h-12 w-20 rounded object-cover"
                      />
                      <button
                        type="button"
                        onClick={clearSelectedCover}
                        data-dls-action-ignore="true"
                        aria-label="Hapus foto sampul terpilih"
                        className="grid size-7 place-items-center rounded text-muted transition hover:bg-red-500/10 hover:text-red-500"
                      >
                        <X className="size-4" />
                      </button>
                    </div>
                  )}
                </div>
              </div>

              <div className="flex items-end md:col-span-2 xl:col-span-3 xl:justify-end">
                <Button
                  type="submit"
                  disabled={busy}
                  loading={busy}
                  icon={<Plus className="size-4" />}
                  className="w-full xl:w-auto xl:min-w-44"
                >
                  Buat Course
                </Button>
              </div>
            </form>
          </Card>
        </div>
      )}

      {/* Katalog & Pencarian */}
      <section id="courses-list" className="scroll-mt-20 space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 className="text-lg font-bold text-on-surface">Katalog Course</h2>
            <p className="text-xs text-muted">
              Menampilkan {filteredCourses.length} dari {courses.length} course yang tersedia.
            </p>
          </div>
        </div>

        <ListFilterBar
          search={courseSearch}
          onSearch={setCourseSearch}
          placeholder="Cari kode course, judul, deskripsi, atau trainer..."
          filter={category}
          onFilter={setCategory}
          label="Filter kategori"
          options={[
            { value: 'all', label: 'Semua kategori' },
            ...Array.from(
              new Set(['Wajib', 'Opsional', ...courses.map((course) => course.category).filter(Boolean)])
            ).map((value) => ({ value, label: value }))
          ]}
        />

        {filteredCourses.length ? (
          <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            {coursePage.pagedItems.map((course) => (
              <article
                key={course.course_id}
                className="dls-card-surface group flex flex-col overflow-hidden rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
              >
                <CourseCoverThumbnail course={course} />

                <div className="flex flex-1 flex-col p-5">
                  <div className="flex items-center justify-between gap-2">
                    <span className="rounded-md border border-outline/40 bg-surface-low px-2.5 py-0.5 font-mono text-xs font-bold text-on-surface-variant dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                      {course.course_code}
                    </span>
                    <StatusBadge status={course.category} />
                  </div>

                  <h3 className="mt-3 text-base font-bold leading-snug text-on-surface">
                    <Link
                      to={`/courses/${course.course_id}`}
                      className="outline-none transition hover:text-primary focus-visible:text-primary"
                    >
                      {course.title}
                    </Link>
                  </h3>

                  <p className="mt-2 line-clamp-2 min-h-10 text-sm leading-relaxed text-muted">
                    {course.description || 'Belum ada deskripsi ringkas untuk course ini.'}
                  </p>

                  <div className="mt-4 grid grid-cols-2 gap-2.5">
                    <MiniStat label="Sesi Pertemuan" value={course.session_count} />
                    <MiniStat label="Materi Terbit" value={course.material_count} />
                  </div>

                  <div className="mt-4 flex items-center gap-2 rounded-lg bg-surface-low/80 px-3 py-2 text-xs text-muted dark:bg-slate-800/60">
                    <UserCheck className="size-3.5 shrink-0 text-secondary dark:text-slate-300" />
                    <span className="truncate">
                      Trainer:{' '}
                      <strong className="font-semibold text-on-surface">
                        {course.assigned_trainer_name || 'Belum ditugaskan'}
                      </strong>
                    </span>
                  </div>

                  {canAssignTrainer && (
                    <div className="mt-3 border-t border-outline/40 pt-3 dark:border-slate-800">
                      <label className="text-[11px] font-semibold uppercase tracking-wider text-muted">
                        Ubah Trainer
                      </label>
                      <div className="mt-1.5 flex gap-2">
                        <select
                          aria-label={`Trainer ${course.title}`}
                          value={assignments[course.course_id] ?? course.assigned_trainer_npk ?? ''}
                          onChange={(event) =>
                            setAssignments((current) => ({
                              ...current,
                              [course.course_id]: event.target.value
                            }))
                          }
                          className="h-9 min-w-0 flex-1 rounded-md border border-outline bg-surface-lowest px-2.5 text-xs text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                          <option value="">Tanpa trainer</option>
                          {trainers.map((t) => (
                            <option key={t.employee_npk} value={t.employee_npk}>
                              {t.fullname}
                            </option>
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
                    data-dls-action-ignore="true"
                    className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-secondary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary"
                  >
                    <span>Buka Detail Course</span>
                    <ArrowRight className="size-4" />
                  </Link>
                </div>
              </article>
            ))}
          </div>
        ) : (
          <Card className="border-dashed text-center">
            <div className="mx-auto flex max-w-md flex-col items-center py-8 text-muted">
              <BookOpen className="mb-3 size-10 opacity-40" />
              <p className="text-sm font-medium text-on-surface">
                {courses.length
                  ? 'Tidak ada course yang cocok dengan pencarian atau filter kategori.'
                  : 'Belum ada course yang terdaftar untuk akun ini.'}
              </p>
              <p className="mt-1 text-xs text-muted">
                Coba ubah kata kunci pencarian atau pilih kategori lain.
              </p>
            </div>
          </Card>
        )}

        <Card noPadding className="overflow-hidden">
          <Pagination
            {...coursePage}
            onPageChange={coursePage.setPage}
            onPageSizeChange={coursePage.setPageSize}
            pageSizes={[9, 18, 36, 72]}
          />
        </Card>
      </section>
    </div>
  );
}

/** Ringkasan angka sesi dan materi pada kartu katalog course */
function MiniStat({ label, value }) {
  return (
    <div className="rounded-lg border border-outline/40 bg-surface-low/60 px-3 py-2 dark:border-slate-700/60 dark:bg-slate-800/50">
      <p className="text-base font-bold leading-tight text-on-surface tabular-nums">
        {value || 0}
      </p>
      <p className="mt-0.5 text-[11px] font-medium text-muted">{label}</p>
    </div>
  );
}

/** Menampilkan gambar sampul course atau latar belakang ikon bawaan saat belum tersedia */
function CourseCoverThumbnail({ course }) {
  const [failed, setFailed] = useState(false);
  const src = resolveCourseThumbnailUrl(course.thumbnail_path);

  useEffect(() => {
    setFailed(false);
  }, [src]);

  return (
    <Link
      to={`/courses/${course.course_id}`}
      className="relative block aspect-video w-full overflow-hidden bg-slate-900"
    >
      {src && !failed ? (
        <img
          src={src}
          alt={`Sampul ${course.title}`}
          loading="lazy"
          decoding="async"
          onError={() => setFailed(true)}
          className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
        />
      ) : (
        <div
          className={`flex h-full w-full items-center justify-center ${
            course.category === 'Wajib'
              ? 'bg-gradient-to-br from-slate-900 via-[#2a1215] to-primary/80'
              : 'bg-gradient-to-br from-slate-900 via-slate-800 to-secondary/90'
          }`}
        >
          <span className="grid size-14 place-items-center rounded-2xl border border-white/15 bg-white/10 text-white/90 shadow-inner">
            <BookOpen className="size-7" />
          </span>
        </div>
      )}
      <div
        className={`absolute inset-x-0 top-0 h-1 ${
          course.category === 'Wajib' ? 'bg-primary' : 'bg-secondary'
        }`}
      />
    </Link>
  );
}
