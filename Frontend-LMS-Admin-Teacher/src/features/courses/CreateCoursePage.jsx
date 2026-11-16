import { useEffect, useRef, useState } from 'react';
import { ArrowLeft, ImagePlus, Plus, X } from 'lucide-react';
import { Link, useNavigate, useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import Button from '../../components/Button';
import Card from '../../components/Card';
import PageHeader from '../../components/PageHeader';
import SearchableSelect from '../../components/SearchableSelect';
import showAlert from '../../utils/alert';
import { hasPermission } from '../../utils/access-control';
import { compressImageToWebP } from '../../utils/compress-forum-image';

const FIELD_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

export default function CreateCoursePage() {
  const user = useOutletContext();
  const navigate = useNavigate();
  const canCreateCourse = hasPermission(user, 'courses.create');
  const [trainers, setTrainers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [coverFile, setCoverFile] = useState(null);
  const [coverPreview, setCoverPreview] = useState('');
  const coverInputRef = useRef(null);

  useEffect(() => {
    if (!canCreateCourse) return;
    api('/material-workflow/trainers')
      .then((result) => setTrainers(result.trainers || []))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false));
  }, [canCreateCourse]);

  useEffect(() => () => coverPreview && URL.revokeObjectURL(coverPreview), [coverPreview]);

  async function handleSelectCover(event) {
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
    const optimized = await compressImageToWebP(file);
    if (coverPreview) URL.revokeObjectURL(coverPreview);
    setCoverFile(optimized);
    setCoverPreview(URL.createObjectURL(optimized));
  }

  function clearSelectedCover() {
    if (coverPreview) URL.revokeObjectURL(coverPreview);
    setCoverFile(null);
    setCoverPreview('');
    if (coverInputRef.current) coverInputRef.current.value = '';
  }

  async function createCourse(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = Object.fromEntries(new FormData(form));
    setBusy(true);
    setError('');
    try {
      const created = await api('/courses', { method: 'POST', body: JSON.stringify(values) });
      if (coverFile && created?.course?.course_id) {
        const fd = new FormData();
        fd.append('thumbnail', coverFile);
        await api(`/courses/${created.course.course_id}/thumbnail`, { method: 'POST', body: fd });
      }
      showAlert.success('Course Berhasil Dibuat', `Course "${values.title}" telah dibuat.`);
      navigate('/courses#courses-list');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuat Course', e.message);
    } finally {
      setBusy(false);
    }
  }

  if (!canCreateCourse) return null;

  return (
    <div className="mx-auto w-full max-w-5xl space-y-6">
      <PageHeader
        eyebrow="Manajemen Pembelajaran"
        title="Buat Course Baru"
        description="Lengkapi informasi course dan trainer penanggung jawab. Data akan tersinkron ke sistem student."
        actions={
          <Link to="/courses" className="inline-flex h-10 items-center gap-2 rounded-lg border border-red-300 bg-red-100/80 px-4 text-sm font-semibold text-red-800 transition hover:bg-red-200/80 dark:border-red-700/70 dark:bg-red-950/60 dark:text-red-200">
            <ArrowLeft className="size-4" />
            Kembali ke Course
          </Link>
        }
      />

      {error && <div role="alert" className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">{error}</div>}

      <Card title="Informasi Course" subtitle="Field bertanda * wajib diisi.">
        <form onSubmit={createCourse} className="grid gap-5 md:grid-cols-2">
          <label className="block text-xs font-semibold text-on-surface-variant">
            Kode Course *
            <input name="courseCode" required maxLength="30" placeholder="Contoh: DENSO-SEC-01" className={FIELD_CLASS} />
          </label>
          <label className="block text-xs font-semibold text-on-surface-variant">
            Judul Course *
            <input name="title" required maxLength="200" placeholder="Masukkan judul pelatihan" className={FIELD_CLASS} />
          </label>
          <label className="block text-xs font-semibold text-on-surface-variant">
            Kategori
            <select name="category" className={FIELD_CLASS}><option value="Wajib">Wajib</option><option value="Pilihan">Pilihan</option></select>
          </label>
          <label className="block text-xs font-semibold text-on-surface-variant">
            Trainer Penanggung Jawab
            <SearchableSelect name="trainerNpk" title="Pilih Trainer Penanggung Jawab" className={FIELD_CLASS} placeholder={loading ? 'Memuat trainer...' : 'Pilih trainer (opsional)'} options={trainers.map((t) => ({ value: t.employee_npk, label: `${t.fullname} (${t.employee_npk})` }))} />
          </label>
          <label className="block text-xs font-semibold text-on-surface-variant md:col-span-2">
            Deskripsi Ringkas
            <textarea name="description" maxLength="4000" rows="4" placeholder="Tujuan dan cakupan materi course" className={`${FIELD_CLASS} h-auto py-2.5`} />
          </label>
          <div className="md:col-span-2">
            <p className="text-xs font-semibold text-on-surface-variant">Foto Sampul Course <span className="font-normal text-muted">(Opsional · JPG/PNG/GIF/WebP, maks. 5 MB)</span></p>
            <div className="mt-1.5 flex flex-wrap items-center gap-3">
              <label className="inline-flex h-10 cursor-pointer items-center gap-2 rounded-md border border-outline bg-surface-lowest px-3.5 text-xs font-semibold text-on-surface transition hover:border-primary/50 hover:text-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <ImagePlus className="size-4 text-primary" />
                <span>{coverFile ? coverFile.name : 'Pilih Foto Sampul'}</span>
                <input ref={coverInputRef} type="file" accept="image/jpeg,image/png,image/gif,image/webp" onChange={handleSelectCover} className="sr-only" />
              </label>
              {coverPreview && <div className="flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-low p-1.5 dark:border-slate-700 dark:bg-slate-800/70"><img src={coverPreview} alt="Pratinjau sampul course" className="h-12 w-20 rounded object-cover" /><button type="button" onClick={clearSelectedCover} aria-label="Hapus foto sampul" className="grid size-7 place-items-center rounded text-muted hover:bg-red-500/10 hover:text-red-500"><X className="size-4" /></button></div>}
            </div>
          </div>
          <div className="flex justify-end border-t border-outline/40 pt-5 md:col-span-2 dark:border-slate-800">
            <Button type="submit" disabled={busy} loading={busy} icon={<Plus className="size-4" />} className="w-full sm:w-auto sm:min-w-48">Buat Course</Button>
          </div>
        </form>
      </Card>
    </div>
  );
}
