import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import {
  BookOpen,
  CheckCircle2,
  ClipboardCheck,
  FileEdit,
  Pencil,
  Plus,
  Save,
  Search,
  Trash2,
  Users,
  X
} from 'lucide-react';
import { api } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';
import { hasPermission } from '../../utils/access-control';

const blankQuestion = () => ({ text: '', options: ['', '', '', ''], correctOption: 0 });
const FIELD_CLASS =
  'mt-1.5 min-h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 py-2 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Halaman Manajemen Quiz akhir course untuk peran Admin dan Pengajar.
 */
export default function QuizzesPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canManageQuiz = hasPermission(user, 'quizzes.manage');
  const [courses, setCourses] = useState([]);
  const [editor, setEditor] = useState(null);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useQueryParamState('status', 'all');
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
    } catch (loadError) {
      setError(loadError.message);
      showAlert.error('Gagal Memuat Daftar Quiz', loadError.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  useEffect(() => {
    window.addEventListener('dls_job_settled', load);
    return () => window.removeEventListener('dls_job_settled', load);
  }, []);

  const filtered = useMemo(
    () =>
      courses.filter((course) => {
        const matchesSearch = `${course.title} ${course.course_code} ${course.quiz?.title || ''}`
          .toLowerCase()
          .includes(search.trim().toLowerCase());
        if (!matchesSearch) return false;
        if (statusFilter === 'published') return course.quiz?.status === 'published';
        if (statusFilter === 'draft') return !course.quiz || course.quiz.status !== 'published';
        return true;
      }),
    [courses, search, statusFilter]
  );

  const quizPage = usePagination(filtered, 8);
  const filteredQuizzes = filtered.filter((course) => course.quiz);
  const selectedFilteredQuizzes = filteredQuizzes.filter((course) =>
    selectedCourses.has(String(course.course_code))
  );

  const summary = useMemo(() => {
    const withQuiz = courses.filter((c) => Boolean(c.quiz));
    const published = withQuiz.filter((c) => c.quiz?.status === 'published').length;
    const draft = withQuiz.filter((c) => c.quiz?.status !== 'published').length;
    const attempts = withQuiz.reduce((sum, c) => sum + Number(c.quiz?.attempt_count || 0), 0);
    return {
      coursesTotal: courses.length,
      published,
      draft,
      attempts
    };
  }, [courses]);

  function toggleCourseSelection(courseCode) {
    const key = String(courseCode);
    setSelectedCourses((previous) => {
      const next = new Set(previous);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  function toggleAllFilteredQuizzes() {
    setSelectedCourses((previous) => {
      const next = new Set(previous);
      const allSelected =
        filteredQuizzes.length > 0 &&
        filteredQuizzes.every((course) => next.has(String(course.course_code)));
      filteredQuizzes.forEach((course) =>
        allSelected
          ? next.delete(String(course.course_code))
          : next.add(String(course.course_code))
      );
      return next;
    });
  }

  async function deleteSelectedQuizzes() {
    if (!isAdmin || !selectedFilteredQuizzes.length) return;
    const count = selectedFilteredQuizzes.length;
    const confirmed = await showAlert.confirm({
      title: `Hapus ${count} quiz?`,
      text: 'Quiz yang sudah pernah dikerjakan akan dinonaktifkan agar nilai dan riwayat student tetap tersimpan.',
      confirmText: `Proses ${count} Quiz`,
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api('/jobs/records/delete', {
        method: 'POST',
        body: JSON.stringify({
          items: selectedFilteredQuizzes.map((course) => ({
            kind: 'quiz',
            courseCode: course.course_code,
            label: `${course.title} (${course.course_code})`
          }))
        })
      });
      setSelectedCourses(new Set());
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `${result.total} quiz akan diproses satu per satu. Progress tersimpan jika halaman dimuat ulang.`
      );
    } catch (deleteError) {
      setError(deleteError.message);
      showAlert.error('Gagal Membuat Antrean Hapus', deleteError.message);
    } finally {
      setBusy(false);
    }
  }

  async function openEditor(course) {
    setBusy(true);
    setError('');
    setNotice('');
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
        questions: quiz?.questions?.length
          ? quiz.questions.map((question) => ({
              text: question.text,
              options: Array.isArray(question.options) ? question.options : ['', ''],
              correctOption: Number(question.correct_option || 0)
            }))
          : [blankQuestion()]
      });
    } catch (openError) {
      setError(openError.message);
      showAlert.error('Quiz Tidak Dapat Dibuka', openError.message);
    } finally {
      setBusy(false);
    }
  }

  function updateQuestion(questionIndex, patch) {
    setEditor((current) => ({
      ...current,
      questions: current.questions.map((question, index) =>
        index === questionIndex ? { ...question, ...patch } : question
      )
    }));
  }

  function updateOption(questionIndex, optionIndex, value) {
    const question = editor.questions[questionIndex];
    updateQuestion(questionIndex, {
      options: question.options.map((option, index) => (index === optionIndex ? value : option))
    });
  }

  async function save(event) {
    event.preventDefault();
    if (!editor) return;
    if (
      editor.questions.some(
        (question) =>
          question.options.filter((option) => option.trim()).length < 2 ||
          question.correctOption >= question.options.length ||
          !question.options[question.correctOption]?.trim()
      )
    ) {
      const msg = 'Setiap pertanyaan perlu minimal dua opsi dan satu jawaban benar.';
      setError(msg);
      showAlert.warning('Quiz Belum Lengkap', msg);
      return;
    }

    setBusy(true);
    setError('');
    setNotice('');
    try {
      await api(`/quizzes/${encodeURIComponent(editor.course.course_code)}`, {
        method: editor.quizId ? 'PUT' : 'POST',
        body: JSON.stringify({
          title: editor.title,
          description: editor.description,
          passingScore: Number(editor.passingScore),
          status: editor.status,
          questions: editor.questions.map((question) => ({
            text: question.text,
            options: question.options.map((option) => option.trim()).filter(Boolean),
            correctOption: question.correctOption
          }))
        })
      });
      setEditor(null);
      const saveMsg =
        'Quiz berhasil disimpan. Student hanya dapat mengerjakan satu kali setelah seluruh materi selesai.';
      setNotice(saveMsg);
      showAlert.success('Quiz Disimpan', saveMsg);
      await load();
    } catch (saveError) {
      setError(saveError.message);
      showAlert.error('Quiz Gagal Disimpan', saveError.message);
    } finally {
      setBusy(false);
    }
  }

  async function removeQuiz(course) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Quiz Course?',
      text:
        Number(course.quiz?.attempt_count || 0) > 0
          ? 'Quiz yang sudah dikerjakan akan dinonaktifkan agar nilai dan riwayat student tetap tersimpan.'
          : `Quiz untuk ${course.title} akan dihapus.`,
      confirmText: 'Ya, Hapus Quiz',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api(`/quizzes/${encodeURIComponent(course.course_code)}`, {
        method: 'DELETE'
      });
      const delMsg = result.message || 'Quiz berhasil dihapus.';
      setNotice(delMsg);
      showAlert.success('Quiz Dihapus', delMsg);
      await load();
    } catch (removeError) {
      setError(removeError.message);
      showAlert.error('Quiz Gagal Dihapus', removeError.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="min-w-0 space-y-6">
      <PageHeader
        eyebrow="Evaluasi Pembelajaran"
        title="Kelola Quiz"
        description="Buat dan kelola evaluasi pilihan ganda akhir course. Student baru dapat mengerjakan setelah semua materi selesai dengan satu kali kesempatan."
        actions={
          <div className="flex w-full max-w-xl flex-col gap-2 sm:flex-row sm:items-center">
            <div className="relative flex-1">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
              <input
                type="search"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder="Cari course atau judul quiz..."
                aria-label="Cari Course"
                className="h-10 w-full rounded-md border border-outline bg-surface-lowest pl-9 pr-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              />
            </div>
            <select
              value={statusFilter}
              onChange={(event) => setStatusFilter(event.target.value)}
              aria-label="Filter status quiz"
              className="h-10 rounded-md border border-outline bg-surface-lowest px-3 text-xs font-semibold text-on-surface outline-none transition focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            >
              <option value="all">Semua status</option>
              <option value="published">Quiz Terbit</option>
              <option value="draft">Draft / Belum Dibuat</option>
            </select>
            {isAdmin && selectedFilteredQuizzes.length > 0 && (
              <Button
                variant="danger"
                size="sm"
                disabled={busy}
                onClick={deleteSelectedQuizzes}
                icon={<Trash2 className="size-4" />}
              >
                Hapus Terpilih ({selectedFilteredQuizzes.length})
              </Button>
            )}
          </div>
        }
      />

      {/* Ringkasan Quiz */}
      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Course Terdaftar"
          value={summary.coursesTotal}
          icon={<BookOpen className="size-5" />}
          tone="secondary"
          loading={loading}
        />
        <StatCard
          label="Quiz Terbit"
          value={summary.published}
          icon={<CheckCircle2 className="size-5" />}
          tone="tertiary"
          loading={loading}
        />
        <StatCard
          label="Quiz Draft / Nonaktif"
          value={summary.draft}
          icon={<FileEdit className="size-5" />}
          tone="warning"
          loading={loading}
        />
        <StatCard
          label="Total Pengerjaan"
          value={summary.attempts}
          icon={<Users className="size-5" />}
          tone="info"
          subtext="Percobaan student"
          loading={loading}
        />
      </section>

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

      <div id="quizzes-list" className="scroll-mt-20 space-y-4">
      {loading ? (
        <Card className="py-12 text-center text-sm text-muted">Memuat daftar course...</Card>
      ) : filtered.length ? (
        <>
          {isAdmin && (
            <div className="dls-card-surface flex flex-wrap items-center gap-3 rounded-xl px-4 py-3 text-sm text-on-surface-variant">
              <label className="inline-flex cursor-pointer items-center gap-2 font-medium">
                <input
                  type="checkbox"
                  aria-label="Pilih semua quiz hasil filter"
                  disabled={!filteredQuizzes.length || busy}
                  checked={
                    filteredQuizzes.length > 0 &&
                    selectedFilteredQuizzes.length === filteredQuizzes.length
                  }
                  onChange={toggleAllFilteredQuizzes}
                  className="size-4 rounded accent-primary"
                />
                <span>Pilih semua quiz pada hasil filter ({filteredQuizzes.length})</span>
              </label>
              {selectedFilteredQuizzes.length > 0 && (
                <span className="ml-auto text-xs font-semibold text-primary">
                  {selectedFilteredQuizzes.length} dipilih
                </span>
              )}
            </div>
          )}

          <section className="grid gap-4 lg:grid-cols-2" aria-label="Daftar quiz per course">
            {quizPage.pagedItems.map((course) => {
              const quiz = course.quiz;
              const statusLabel =
                { published: 'Terbit', draft: 'Draft', inactive: 'Nonaktif' }[quiz?.status] ||
                'Belum dibuat';

              return (
                <article
                  key={course.course_code}
                  className="dls-card-surface flex min-w-0 flex-col rounded-xl p-5 transition hover:shadow-md"
                >
                  <div className="flex min-w-0 items-start justify-between gap-3">
                    <div className="flex min-w-0 items-start gap-3">
                      {isAdmin && quiz && (
                        <input
                          type="checkbox"
                          aria-label={`Pilih quiz ${course.title}`}
                          disabled={busy}
                          checked={selectedCourses.has(String(course.course_code))}
                          onChange={() => toggleCourseSelection(course.course_code)}
                          className="mt-1 size-4 shrink-0 rounded accent-primary"
                        />
                      )}
                      <div className="min-w-0">
                        <span className="inline-block rounded-md bg-surface-low px-2 py-0.5 font-mono text-[11px] font-bold text-on-surface-variant dark:bg-slate-800 dark:text-slate-300">
                          {course.course_code}
                        </span>
                        <h2 className="mt-1.5 truncate text-base font-bold text-on-surface">
                          {course.title}
                        </h2>
                      </div>
                    </div>
                    <StatusBadge status={quiz?.status || 'pending'} label={statusLabel} dot />
                  </div>

                  {quiz ? (
                    <div className="mt-4 grid grid-cols-2 gap-3 rounded-lg border border-outline/40 bg-surface-low/60 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                      <div>
                        <span className="block text-muted">Judul Quiz</span>
                        <strong className="mt-1 block truncate text-sm text-on-surface">
                          {quiz.title || 'Tanpa judul'}
                        </strong>
                      </div>
                      <div>
                        <span className="block text-muted">Soal · Percobaan</span>
                        <strong className="mt-1 block text-sm text-on-surface tabular-nums">
                          {quiz.question_count} soal · {quiz.attempt_count} student
                        </strong>
                      </div>
                      <div>
                        <span className="block text-muted">Nilai Kelulusan</span>
                        <strong className="mt-1 block text-sm text-primary tabular-nums">
                          {Number(quiz.passing_score).toLocaleString('id-ID')}
                        </strong>
                      </div>
                      <div>
                        <span className="block text-muted">Terakhir Diubah</span>
                        <strong className="mt-1 block text-sm text-on-surface">
                          {quiz.updated_at
                            ? new Date(quiz.updated_at).toLocaleDateString('id-ID')
                            : '—'}
                        </strong>
                      </div>
                    </div>
                  ) : (
                    <div className="mt-4 flex items-center gap-2 rounded-lg border border-dashed border-outline/60 bg-surface-low/40 p-4 text-xs text-muted dark:border-slate-700 dark:bg-slate-900/40">
                      <ClipboardCheck className="size-4 shrink-0" />
                      <span>Belum ada quiz untuk course ini. Klik Buat Quiz untuk memulai.</span>
                    </div>
                  )}

                  {canManageQuiz && (
                    <div className="mt-auto flex flex-wrap gap-2 pt-5">
                      <Button
                        variant="primary"
                        size="sm"
                        disabled={busy}
                        onClick={() => openEditor(course)}
                        icon={quiz ? <Pencil className="size-3.5" /> : <Plus className="size-3.5" />}
                      >
                        {quiz ? 'Edit Quiz' : 'Buat Quiz'}
                      </Button>
                      {quiz && (
                        <Button
                          variant="danger"
                          size="sm"
                          disabled={busy}
                          onClick={() => removeQuiz(course)}
                          icon={<Trash2 className="size-3.5" />}
                        >
                          Hapus
                        </Button>
                      )}
                    </div>
                  )}
                </article>
              );
            })}
          </section>

          <Card noPadding className="overflow-hidden">
            <Pagination
              {...quizPage}
              onPageChange={quizPage.setPage}
              onPageSizeChange={quizPage.setPageSize}
              pageSizes={[8, 16, 32]}
            />
          </Card>
        </>
      ) : (
        <Card className="py-10 text-center text-sm text-muted">
          {courses.length
            ? 'Tidak ada course yang cocok dengan pencarian.'
            : 'Belum ada course yang dapat Anda kelola.'}
        </Card>
      )}
      </div>

      {/* Modal Editor Quiz */}
      {editor && (
        <div
          className="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-slate-950/60 p-3 backdrop-blur-xs sm:p-6"
          onClick={(event) => {
            if (event.target === event.currentTarget) setEditor(null);
          }}
        >
          <form
            onSubmit={save}
            role="dialog"
            aria-modal="true"
            aria-labelledby="quiz-editor-title"
            className="dls-card-surface my-auto flex max-h-[calc(100dvh-1.5rem)] w-full max-w-3xl flex-col overflow-hidden rounded-xl shadow-2xl"
          >
            <header className="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-outline/40 bg-surface-low/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/60">
              <div className="min-w-0">
                <p className="font-mono text-xs font-bold text-primary">
                  {editor.course.course_code} · {editor.course.title}
                </p>
                <h2 id="quiz-editor-title" className="mt-1 text-lg font-bold text-on-surface">
                  {editor.quizId ? 'Edit Quiz Course' : 'Buat Quiz Course'}
                </h2>
                <p className="mt-0.5 text-xs text-muted">
                  Tambahkan pertanyaan dan tandai satu opsi sebagai jawaban yang benar.
                </p>
              </div>
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => setEditor(null)}
                className="grid size-8 shrink-0 place-items-center rounded-lg text-muted transition hover:bg-surface-container hover:text-on-surface dark:hover:bg-slate-700"
                aria-label="Tutup form quiz"
              >
                <X className="size-4" />
              </button>
            </header>

            <div className="flex-1 space-y-5 overflow-y-auto p-6">
              <div className="grid gap-4 sm:grid-cols-2">
                <label className="text-xs font-bold text-on-surface-variant">
                  Judul Quiz *
                  <input
                    required
                    minLength="2"
                    maxLength="200"
                    value={editor.title}
                    onChange={(event) => setEditor({ ...editor, title: event.target.value })}
                    placeholder="Contoh: Evaluasi akhir course"
                    className={FIELD_CLASS}
                  />
                </label>

                <label className="text-xs font-bold text-on-surface-variant">
                  Nilai Kelulusan (0–100) *
                  <input
                    required
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    value={editor.passingScore}
                    onChange={(event) =>
                      setEditor({ ...editor, passingScore: event.target.value })
                    }
                    className={FIELD_CLASS}
                  />
                </label>
              </div>

              <label className="block text-xs font-bold text-on-surface-variant">
                Petunjuk Pengerjaan
                <textarea
                  maxLength="2000"
                  rows="3"
                  value={editor.description}
                  onChange={(event) => setEditor({ ...editor, description: event.target.value })}
                  placeholder="Jelaskan aturan mengerjakan quiz"
                  className={FIELD_CLASS}
                />
              </label>

              <label className="block text-xs font-bold text-on-surface-variant">
                Status Quiz
                <select
                  value={editor.status}
                  onChange={(event) => setEditor({ ...editor, status: event.target.value })}
                  className={FIELD_CLASS}
                >
                  <option value="draft">Draft — belum tersedia untuk Student</option>
                  <option value="published">
                    Terbit — tersedia setelah semua materi selesai
                  </option>
                </select>
              </label>

              <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <h3 className="text-base font-bold text-on-surface">
                    Daftar Pertanyaan ({editor.questions.length})
                  </h3>
                  <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    disabled={editor.questions.length >= 100}
                    icon={<Plus className="size-4" />}
                    onClick={() =>
                      setEditor({
                        ...editor,
                        questions: [...editor.questions, blankQuestion()]
                      })
                    }
                  >
                    Tambah Pertanyaan
                  </Button>
                </div>

                {editor.questions.map((question, questionIndex) => (
                  <section
                    key={questionIndex}
                    className="rounded-xl border border-outline/50 bg-surface-low/30 p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900/40"
                  >
                    <div className="flex items-center justify-between gap-3">
                      <h4 className="text-sm font-bold text-on-surface">
                        Pertanyaan {questionIndex + 1}
                      </h4>
                      <Button
                        type="button"
                        size="xs"
                        variant="danger"
                        disabled={editor.questions.length <= 1}
                        icon={<Trash2 className="size-3.5" />}
                        onClick={() =>
                          setEditor({
                            ...editor,
                            questions: editor.questions.filter(
                              (_, index) => index !== questionIndex
                            )
                          })
                        }
                      >
                        Hapus Soal
                      </Button>
                    </div>

                    <label className="mt-3 block text-xs font-semibold text-on-surface-variant">
                      Teks Pertanyaan *
                      <textarea
                        required
                        minLength="2"
                        maxLength="2000"
                        rows="3"
                        value={question.text}
                        onChange={(event) =>
                          updateQuestion(questionIndex, { text: event.target.value })
                        }
                        placeholder="Tulis pertanyaan dengan jelas..."
                        className={FIELD_CLASS}
                      />
                    </label>

                    <fieldset className="mt-4 space-y-2">
                      <legend className="mb-2 text-xs font-semibold text-on-surface-variant">
                        Opsi Jawaban{' '}
                        <span className="font-normal text-muted">(tandai jawaban benar)</span>
                      </legend>

                      {question.options.map((option, optionIndex) => (
                        <div key={optionIndex} className="flex items-center gap-2">
                          <input
                            type="radio"
                            name={`correct-${questionIndex}`}
                            checked={question.correctOption === optionIndex}
                            onChange={() =>
                              updateQuestion(questionIndex, { correctOption: optionIndex })
                            }
                            className="size-4 shrink-0 accent-primary"
                            aria-label={`Tandai opsi ${optionIndex + 1} sebagai jawaban benar`}
                          />
                          <input
                            required
                            minLength="1"
                            maxLength="500"
                            value={option}
                            onChange={(event) =>
                              updateOption(questionIndex, optionIndex, event.target.value)
                            }
                            placeholder={`Opsi ${optionIndex + 1}`}
                            className="min-h-9 min-w-0 flex-1 rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                          />
                          <button
                            type="button"
                            data-dls-action-ignore="true"
                            disabled={question.options.length <= 2}
                            onClick={() =>
                              updateQuestion(questionIndex, {
                                options: question.options.filter(
                                  (_, index) => index !== optionIndex
                                ),
                                correctOption:
                                  question.correctOption === optionIndex
                                    ? 0
                                    : question.correctOption > optionIndex
                                      ? question.correctOption - 1
                                      : question.correctOption
                              })
                            }
                            className="grid size-9 shrink-0 place-items-center rounded-md border border-outline/60 text-muted hover:bg-surface-low disabled:opacity-40 dark:border-slate-700"
                            aria-label={`Hapus opsi ${optionIndex + 1}`}
                          >
                            <X className="size-3.5" />
                          </button>
                        </div>
                      ))}

                      {question.options.length < 6 && (
                        <Button
                          type="button"
                          size="xs"
                          variant="outline"
                          icon={<Plus className="size-3.5" />}
                          onClick={() =>
                            updateQuestion(questionIndex, {
                              options: [...question.options, '']
                            })
                          }
                        >
                          Tambah Opsi
                        </Button>
                      )}
                    </fieldset>
                  </section>
                ))}
              </div>
            </div>

            <footer className="sticky bottom-0 flex flex-col-reverse gap-2 border-t border-outline/40 bg-surface-low/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-800 dark:bg-slate-800/60">
              <Button type="button" variant="outline" onClick={() => setEditor(null)}>
                Batal
              </Button>
              <Button
                type="submit"
                variant="primary"
                disabled={busy}
                loading={busy}
                icon={<Save className="size-4" />}
              >
                Simpan Quiz
              </Button>
            </footer>
          </form>
        </div>
      )}
    </div>
  );
}
