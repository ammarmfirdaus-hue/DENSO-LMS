import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Button from '../../components/Button';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

const blankQuestion = () => ({ text: '', options: ['', '', '', ''], correctOption: 0 });
const blankQuiz = () => ({ title: '', description: '', passingScore: 75, status: 'draft', questions: [blankQuestion()] });
const fieldClass = 'mt-1.5 min-h-11 w-full rounded-lg border border-outline/60 bg-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10';
const secondaryButton = 'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-outline/60 bg-white px-3 text-sm font-semibold text-on-surface hover:bg-surface-low disabled:opacity-50';

export default function QuizzesPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [courses, setCourses] = useState([]);
  const [editor, setEditor] = useState(null);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [selectedCourses, setSelectedCourses] = useState(() => new Set());

  async function load() {
    try {
      const result = await api('/quizzes');
      setCourses(result.courses || []);
      setError('');
    } catch (loadError) { setError(loadError.message); }
    finally { setLoading(false); }
  }

  useEffect(() => { load(); }, []);
  useEffect(() => {
    window.addEventListener('dls_job_settled', load);
    return () => window.removeEventListener('dls_job_settled', load);
  }, []);

  const filtered = useMemo(() => courses.filter(course =>
    `${course.title} ${course.course_code} ${course.quiz?.title || ''}`.toLowerCase().includes(search.trim().toLowerCase())
  ), [courses, search]);
  const quizPage = usePagination(filtered, 8);
  const filteredQuizzes = filtered.filter(course => course.quiz);
  const selectedFilteredQuizzes = filteredQuizzes.filter(course => selectedCourses.has(String(course.course_code)));

  function toggleCourseSelection(courseCode) {
    const key = String(courseCode);
    setSelectedCourses(previous => {
      const next = new Set(previous);
      if (next.has(key)) next.delete(key); else next.add(key);
      return next;
    });
  }

  function toggleAllFilteredQuizzes() {
    setSelectedCourses(previous => {
      const next = new Set(previous);
      const allSelected = filteredQuizzes.length > 0 && filteredQuizzes.every(course => next.has(String(course.course_code)));
      filteredQuizzes.forEach(course => allSelected ? next.delete(String(course.course_code)) : next.add(String(course.course_code)));
      return next;
    });
  }

  async function deleteSelectedQuizzes() {
    if (!isAdmin || !selectedFilteredQuizzes.length) return;
    const count = selectedFilteredQuizzes.length;
    const confirmed = await showAlert.confirm({
      title: `Hapus ${count} quiz?`,
      text: 'Quiz yang sudah pernah dikerjakan akan dinonaktifkan agar nilai dan riwayat student tetap tersimpan.',
      confirmText: `Proses ${count} Quiz`, cancelText: 'Batal', danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      const result = await api('/jobs/records/delete', { method: 'POST', body: JSON.stringify({ items: selectedFilteredQuizzes.map(course => ({
        kind: 'quiz', courseCode: course.course_code, label: `${course.title} (${course.course_code})`
      })) }) });
      setSelectedCourses(new Set());
      showAlert.success('Penghapusan Masuk Antrean', `${result.total} quiz akan diproses satu per satu. Progress tersimpan jika halaman dimuat ulang.`);
    } catch (deleteError) { setError(deleteError.message); showAlert.error('Gagal Membuat Antrean Hapus', deleteError.message); }
    finally { setBusy(false); }
  }

  async function openEditor(course) {
    setBusy(true); setError(''); setNotice('');
    try {
      const result = await api(`/quizzes/${encodeURIComponent(course.course_code)}`);
      const quiz = result.quiz;
      setEditor({
        course,
        quizId: quiz?.quiz_id || null,
        title: quiz?.title || '',
        description: quiz?.description || '',
        passingScore: quiz?.passing_score ?? 75,
        status: quiz?.status === 'published' ? 'published' : 'draft',
        questions: quiz?.questions?.length ? quiz.questions.map(question => ({
          text: question.text,
          options: Array.isArray(question.options) ? question.options : ['', ''],
          correctOption: Number(question.correct_option || 0)
        })) : [blankQuestion()]
      });
    } catch (openError) {
      setError(openError.message);
      showAlert.error('Quiz Tidak Dapat Dibuka', openError.message);
    } finally { setBusy(false); }
  }

  function updateQuestion(questionIndex, patch) {
    setEditor(current => ({ ...current, questions: current.questions.map((question, index) => index === questionIndex ? { ...question, ...patch } : question) }));
  }

  function updateOption(questionIndex, optionIndex, value) {
    const question = editor.questions[questionIndex];
    updateQuestion(questionIndex, { options: question.options.map((option, index) => index === optionIndex ? value : option) });
  }

  async function save(event) {
    event.preventDefault();
    if (!editor) return;
    if (editor.questions.some(question => question.options.filter(option => option.trim()).length < 2 || question.correctOption >= question.options.length || !question.options[question.correctOption]?.trim())) {
      setError('Setiap pertanyaan perlu minimal dua opsi dan satu jawaban benar.');
      return;
    }
    setBusy(true); setError(''); setNotice('');
    try {
      await api(`/quizzes/${encodeURIComponent(editor.course.course_code)}`, {
        method: editor.quizId ? 'PUT' : 'POST',
        body: JSON.stringify({
          title: editor.title,
          description: editor.description,
          passingScore: Number(editor.passingScore),
          status: editor.status,
          questions: editor.questions.map(question => ({
            text: question.text,
            options: question.options.map(option => option.trim()).filter(Boolean),
            correctOption: question.correctOption
          }))
        })
      });
      setEditor(null);
      setNotice('Quiz berhasil disimpan. Student hanya dapat mengirim satu jawaban setelah seluruh materi selesai.');
      await load();
    } catch (saveError) {
      setError(saveError.message);
      showAlert.error('Quiz Gagal Disimpan', saveError.message);
    } finally { setBusy(false); }
  }

  async function removeQuiz(course) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Quiz Course?',
      text: Number(course.quiz?.attempt_count || 0) > 0
        ? 'Quiz yang sudah dikerjakan akan dinonaktifkan agar nilai dan riwayat student tetap tersimpan.'
        : `Quiz untuk ${course.title} akan dihapus.`,
      confirmText: 'Ya, Hapus Quiz',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      const result = await api(`/quizzes/${encodeURIComponent(course.course_code)}`, { method: 'DELETE' });
      setNotice(result.message || 'Quiz berhasil dihapus.');
      await load();
    } catch (removeError) {
      setError(removeError.message);
      showAlert.error('Quiz Gagal Dihapus', removeError.message);
    } finally { setBusy(false); }
  }

  return (
    <div className="mx-auto min-w-0 max-w-7xl space-y-6">
      <PageHeader
        eyebrow="PEMBELAJARAN"
        title="Kelola Quiz"
        description="Buat dan kelola quiz pilihan ganda akhir course. Student baru dapat mengerjakan setelah semua materi ditandai selesai, dengan satu kali kesempatan attempt."
        actions={
          <div className="flex w-full max-w-xl flex-col gap-2 sm:flex-row sm:items-end">
            <label className="min-w-0 flex-1 text-xs font-semibold text-muted">
              Cari Course
              <input
                type="search"
                value={search}
                onChange={event => setSearch(event.target.value)}
                placeholder="Nama atau kode course..."
                className={fieldClass}
              />
            </label>
            {isAdmin && selectedFilteredQuizzes.length > 0 && (
              <Button
                variant="danger"
                disabled={busy}
                onClick={deleteSelectedQuizzes}
                icon="delete_sweep"
              >
                Hapus Terpilih ({selectedFilteredQuizzes.length})
              </Button>
            )}
          </div>
        }
      />

    {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</p>}
    {notice && <p role="status" className="rounded-xl border border-outline/50 bg-white p-4 text-sm text-on-surface">{notice}</p>}
    {loading ? <div className="rounded-2xl border border-outline/50 bg-white p-10 text-center text-sm text-muted">Memuat daftar course...</div>
      : filtered.length ? <><section className="mb-3 flex items-center gap-2 rounded-xl border border-outline/50 bg-white px-4 py-3 text-sm text-secondary"><input type="checkbox" aria-label="Pilih semua quiz hasil filter" disabled={!isAdmin || !filteredQuizzes.length || busy} checked={isAdmin && filteredQuizzes.length > 0 && selectedFilteredQuizzes.length === filteredQuizzes.length} onChange={toggleAllFilteredQuizzes} className="size-4 accent-primary" /><span>Pilih semua quiz pada hasil filter ({filteredQuizzes.length})</span>{selectedFilteredQuizzes.length > 0 && <span className="ml-auto font-semibold">{selectedFilteredQuizzes.length} dipilih</span>}</section><section className="grid gap-4 lg:grid-cols-2" aria-label="Daftar quiz per course">
        {quizPage.pagedItems.map(course => {
          const quiz = course.quiz;
          const status = { published: 'Terbit', draft: 'Draft', inactive: 'Nonaktif' }[quiz?.status] || 'Belum dibuat';
          return <article key={course.course_code} className="flex min-w-0 flex-col rounded-2xl border border-slate-200/90 bg-slate-100 p-5 shadow-xs dark:border-slate-800 dark:bg-slate-800/90">
            <div className="flex min-w-0 items-start justify-between gap-3"><div className="flex min-w-0 items-start gap-3">{isAdmin && quiz && <input type="checkbox" aria-label={`Pilih quiz ${course.title}`} disabled={busy} checked={selectedCourses.has(String(course.course_code))} onChange={() => toggleCourseSelection(course.course_code)} className="mt-1 size-4 shrink-0 accent-primary" />}<div className="min-w-0"><p className="truncate text-xs font-semibold text-muted">{course.course_code}</p><h2 className="mt-1 truncate text-lg font-bold">{course.title}</h2></div></div><span className="shrink-0 rounded-full border border-outline/60 bg-white dark:bg-slate-900 px-3 py-1 text-xs font-semibold">{status}</span></div>
            {quiz ? <div className="mt-4 grid grid-cols-2 gap-3 rounded-xl border border-slate-200/70 bg-white p-3 text-sm dark:border-slate-700/60 dark:bg-slate-900"><div><span className="block text-xs text-muted">Quiz</span><strong className="mt-1 block truncate">{quiz.title || 'Tanpa judul'}</strong></div><div><span className="block text-xs text-muted">Soal · percobaan</span><strong className="mt-1 block">{quiz.question_count} · {quiz.attempt_count} student</strong></div><div><span className="block text-xs text-muted">Nilai kelulusan</span><strong className="mt-1 block">{Number(quiz.passing_score).toLocaleString('id-ID')}</strong></div><div><span className="block text-xs text-muted">Terakhir diubah</span><strong className="mt-1 block">{quiz.updated_at ? new Date(quiz.updated_at).toLocaleDateString('id-ID') : '—'}</strong></div></div>
              : <p className="mt-4 rounded-xl border border-dashed border-outline/60 bg-white/70 dark:bg-slate-900/60 p-4 text-sm text-muted">Belum ada quiz untuk course ini.</p>}
            <div className="mt-auto flex flex-wrap gap-2 pt-5"><button type="button" disabled={busy} onClick={() => openEditor(course)} className="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:brightness-95 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">{quiz ? 'edit' : 'add'}</span>{quiz ? 'Edit Quiz' : 'Buat Quiz'}</button>{quiz && <button type="button" disabled={busy} onClick={() => removeQuiz(course)} className={secondaryButton}><span className="material-symbol text-primary" aria-hidden="true">delete</span>Hapus</button>}</div>
          </article>;
        })}
      </section>
      <div className="mt-4 overflow-hidden rounded-2xl border border-outline/40 bg-white dark:border-slate-800 dark:bg-[#1e293b]">
        <Pagination
          {...quizPage}
          onPageChange={quizPage.setPage}
          onPageSizeChange={quizPage.setPageSize}
          pageSizes={[8, 16, 32]}
        />
      </div>
      </> : <div className="rounded-2xl border border-outline/50 bg-white p-10 text-center text-sm text-muted">{courses.length ? 'Tidak ada course yang cocok dengan pencarian.' : 'Belum ada course yang dapat Anda kelola.'}</div>}

    {editor && <div className="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/50 p-3 sm:p-6"><form onSubmit={save} role="dialog" aria-modal="true" aria-labelledby="quiz-editor-title" className="my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-3xl overflow-y-auto rounded-2xl border border-outline/50 bg-white shadow-2xl">
      <header className="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-outline/40 bg-white p-4 sm:p-6"><div className="min-w-0"><p className="text-xs font-bold text-primary">{editor.course.course_code} · {editor.course.title}</p><h2 id="quiz-editor-title" className="mt-1 text-xl font-bold">{editor.quizId ? 'Edit Quiz' : 'Buat Quiz Course'}</h2><p className="mt-1 text-sm text-muted">Tambahkan pertanyaan dan tandai satu opsi sebagai jawaban benar.</p></div><button type="button" onClick={() => setEditor(null)} className="grid size-10 shrink-0 place-items-center rounded-lg border border-outline/50 bg-white hover:bg-surface-low" aria-label="Tutup form quiz"><span className="material-symbol" aria-hidden="true">close</span></button></header>
      <div className="space-y-5 p-4 sm:p-6">
        <div className="grid gap-4 sm:grid-cols-2"><label className="text-sm font-semibold">Judul quiz<input required minLength="2" maxLength="200" value={editor.title} onChange={event => setEditor({ ...editor, title: event.target.value })} placeholder="Contoh: Evaluasi akhir course" className={fieldClass} /></label><label className="text-sm font-semibold">Nilai kelulusan<input required type="number" min="0" max="100" step="0.01" value={editor.passingScore} onChange={event => setEditor({ ...editor, passingScore: event.target.value })} className={fieldClass} /></label></div>
        <label className="block text-sm font-semibold">Petunjuk<textarea maxLength="2000" rows="3" value={editor.description} onChange={event => setEditor({ ...editor, description: event.target.value })} placeholder="Jelaskan aturan mengerjakan quiz" className={fieldClass} /></label>
        <label className="block text-sm font-semibold">Status quiz<select value={editor.status} onChange={event => setEditor({ ...editor, status: event.target.value })} className={fieldClass}><option value="draft">Draft — belum tersedia untuk Student</option><option value="published">Terbit — tersedia setelah semua materi selesai</option></select></label>
        <div className="space-y-4"><div className="flex flex-wrap items-center justify-between gap-2"><h3 className="text-lg font-bold">Pertanyaan ({editor.questions.length})</h3><button type="button" disabled={editor.questions.length >= 100} onClick={() => setEditor({ ...editor, questions: [...editor.questions, blankQuestion()] })} className={secondaryButton}><span className="material-symbol" aria-hidden="true">add</span>Tambah pertanyaan</button></div>
          {editor.questions.map((question, questionIndex) => <section key={questionIndex} className="rounded-xl border border-outline/50 p-4 sm:p-5"><div className="flex items-center justify-between gap-3"><h4 className="font-bold">Pertanyaan {questionIndex + 1}</h4><button type="button" disabled={editor.questions.length <= 1} onClick={() => setEditor({ ...editor, questions: editor.questions.filter((_, index) => index !== questionIndex) })} className="inline-flex min-h-9 items-center gap-1 rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-primary hover:bg-red-50 disabled:opacity-40"><span className="material-symbol" aria-hidden="true">delete</span>Hapus soal</button></div>
            <label className="mt-3 block text-sm font-semibold">Pertanyaan<textarea required minLength="2" maxLength="2000" rows="3" value={question.text} onChange={event => updateQuestion(questionIndex, { text: event.target.value })} placeholder="Tulis pertanyaan" className={fieldClass} /></label>
            <fieldset className="mt-4 space-y-2"><legend className="mb-2 text-sm font-semibold">Opsi jawaban <span className="font-normal text-muted">(pilih jawaban benar)</span></legend>{question.options.map((option, optionIndex) => <div key={optionIndex} className="flex items-center gap-2"><input type="radio" name={`correct-${questionIndex}`} checked={question.correctOption === optionIndex} onChange={() => updateQuestion(questionIndex, { correctOption: optionIndex })} className="size-4 shrink-0 accent-primary" aria-label={`Tandai opsi ${optionIndex + 1} sebagai jawaban benar`} /><input required minLength="1" maxLength="500" value={option} onChange={event => updateOption(questionIndex, optionIndex, event.target.value)} placeholder={`Opsi ${optionIndex + 1}`} className="min-h-10 min-w-0 flex-1 rounded-lg border border-outline/60 px-3 text-sm outline-none focus:border-primary" /><button type="button" disabled={question.options.length <= 2} onClick={() => updateQuestion(questionIndex, { options: question.options.filter((_, index) => index !== optionIndex), correctOption: question.correctOption === optionIndex ? 0 : question.correctOption > optionIndex ? question.correctOption - 1 : question.correctOption })} className="grid size-10 shrink-0 place-items-center rounded-lg border border-outline/50 hover:bg-surface-low disabled:opacity-40" aria-label={`Hapus opsi ${optionIndex + 1}`}><span className="material-symbol text-base" aria-hidden="true">close</span></button></div>)}{question.options.length < 6 && <button type="button" onClick={() => updateQuestion(questionIndex, { options: [...question.options, ''] })} className="mt-1 inline-flex min-h-9 items-center gap-1 rounded-lg border border-outline/50 px-3 text-xs font-semibold hover:bg-surface-low"><span className="material-symbol" aria-hidden="true">add</span>Tambah opsi</button>}</fieldset>
          </section>)}
        </div>
      </div>
      <footer className="sticky bottom-0 flex flex-col-reverse gap-2 border-t border-outline/40 bg-white p-4 sm:flex-row sm:justify-end sm:p-6"><button type="button" onClick={() => setEditor(null)} className={secondaryButton}>Batal</button><button type="submit" disabled={busy} className="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-primary px-5 text-sm font-semibold text-white hover:brightness-95 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">save</span>{busy ? 'Menyimpan...' : 'Simpan Quiz'}</button></footer>
    </form></div>}
  </div>
  );
}
