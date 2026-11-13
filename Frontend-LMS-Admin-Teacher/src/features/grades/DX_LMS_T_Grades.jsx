import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import {
  Award,
  CheckCircle2,
  ClipboardList,
  Download,
  Mail,
  Pencil,
  Trash2,
  TrendingUp
} from 'lucide-react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import DataTable from '../../components/DataTable';
import Modal from '../../components/Modal';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';
import { buildGradesXlsx } from './grade-export';
import { hasPermission } from '../../utils/access-control';

const FIELD_CLASS =
  'mt-1.5 h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

function exportGrades(rows) {
  const workbook = buildGradesXlsx(rows);
  const url = URL.createObjectURL(
    new Blob([workbook], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
  );
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = `rekap-nilai-student-${new Date().toISOString().slice(0, 10)}.xlsx`;
  anchor.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

/**
 * Halaman Kelola Nilai untuk memantau nilai quiz, nilai course, progres belajar,
 * pengiriman notifikasi, dan ekspor laporan XLSX.
 */
export default function GradesPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const canEditGrades = hasPermission(user, 'grades.edit');
  const canSendNotification = hasPermission(user, 'notifications.send');
  const [rows, setRows] = useState([]);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [search, setSearch] = useState('');
  const [status, setStatus] = useQueryParamState('status', 'all');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [selectedGrades, setSelectedGrades] = useState(() => new Set());
  const [editing, setEditing] = useState(null);
  const [notifying, setNotifying] = useState(null);
  const [sendEmail, setSendEmail] = useState(false);

  async function load() {
    setLoading(true);
    try {
      setRows((await api('/grades')).grades || []);
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Data Nilai', e.message);
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

  const statuses = useMemo(
    () =>
      [...new Set(['completed', 'in_progress', ...rows.map((row) => row.enrollment_status).filter(Boolean)])].sort(),
    [rows]
  );

  const filtered = useMemo(
    () =>
      rows.filter(
        (row) =>
          (status === 'all' || row.enrollment_status === status) &&
          `${row.student_npk} ${row.course_title || ''} ${row.course_code || ''} ${row.enrollment_status}`
            .toLowerCase()
            .includes(search.trim().toLowerCase())
      ),
    [rows, search, status]
  );

  const page = usePagination(filtered);
  const selectedFiltered = filtered.filter((row) =>
    selectedGrades.has(`${row.student_npk}::${row.course_code}`)
  );

  const summary = useMemo(() => {
    const avgProgress = rows.length
      ? Math.round(rows.reduce((sum, r) => sum + Number(r.progress || 0), 0) / rows.length)
      : 0;
    const quizRows = rows.filter((r) => r.quiz_score != null);
    const avgQuiz = quizRows.length
      ? Math.round(
          (quizRows.reduce((sum, r) => sum + Number(r.quiz_score || 0), 0) / quizRows.length) * 10
        ) / 10
      : null;
    const completed = rows.filter((r) => r.enrollment_status === 'completed').length;
    return {
      total: rows.length,
      avgProgress,
      avgQuiz,
      completed
    };
  }, [rows]);

  function toggleGradeSelection(row) {
    const key = `${row.student_npk}::${row.course_code}`;
    setSelectedGrades((previous) => {
      const next = new Set(previous);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  function toggleAllFilteredGrades() {
    setSelectedGrades((previous) => {
      const next = new Set(previous);
      const allSelected =
        filtered.length > 0 &&
        filtered.every((row) => next.has(`${row.student_npk}::${row.course_code}`));
      filtered.forEach((row) => {
        const key = `${row.student_npk}::${row.course_code}`;
        if (allSelected) next.delete(key);
        else next.add(key);
      });
      return next;
    });
  }

  async function deleteSelectedGrades() {
    if (!isAdmin || !selectedFiltered.length) return;
    const confirmed = await showAlert.confirm({
      title: `Hapus ${selectedFiltered.length} nilai?`,
      text: 'Nilai dan umpan balik akan dikosongkan. Data progres course student tetap disimpan.',
      confirmText: `Proses ${selectedFiltered.length} Nilai`,
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
          items: selectedFiltered.map((row) => ({
            kind: 'grade',
            npk: row.student_npk,
            courseCode: row.course_code,
            label: `${row.student_npk} · ${row.course_title || row.course_code}`
          }))
        })
      });
      setSelectedGrades(new Set());
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `${result.total} nilai akan diproses satu per satu. Progress tersimpan jika halaman dimuat ulang.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuat Antrean Hapus', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function saveGrade(event) {
    event.preventDefault();
    setBusy(true);
    setError('');
    setNotice('');
    try {
      await api(
        `/grades/${encodeURIComponent(editing.student_npk)}/${encodeURIComponent(editing.course_code)}`,
        {
          method: 'PATCH',
          body: JSON.stringify({ score: Number(editing.score), feedback: editing.feedback || '' })
        }
      );
      setEditing(null);
      setNotice('Nilai berhasil diperbarui.');
      showAlert.success('Nilai Diperbarui', 'Nilai peserta telah berhasil diperbarui.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memperbarui Nilai', e.message);
    } finally {
      setBusy(false);
    }
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

    setBusy(true);
    setError('');
    setNotice('');
    try {
      await api(
        `/grades/${encodeURIComponent(row.student_npk)}/${encodeURIComponent(row.course_code)}`,
        { method: 'DELETE' }
      );
      setNotice('Nilai dihapus; data progres tetap tersimpan.');
      showAlert.success('Nilai Dihapus', 'Nilai peserta dihapus; data progres pembelajaran tetap tersimpan.');
      await load();
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Nilai', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function sendNotification(event) {
    event.preventDefault();
    setBusy(true);
    setError('');
    setNotice('');
    const form = new FormData(event.currentTarget);
    try {
      const result = await api(
        `/grades/${encodeURIComponent(notifying.student_npk)}/${encodeURIComponent(notifying.course_code)}/notify`,
        {
          method: 'POST',
          body: JSON.stringify({
            title: form.get('title'),
            message: form.get('message'),
            sendEmail
          })
        }
      );
      const targetNpk = notifying.student_npk;
      setNotifying(null);
      setSendEmail(false);
      const msg = `Notifikasi terkirim ke ${targetNpk}.${result.emailQueued ? ' Email masuk antrean.' : ''}`;
      setNotice(msg);
      showAlert.success('Notifikasi Terkirim', msg);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengirim Notifikasi', e.message);
    } finally {
      setBusy(false);
    }
  }

  const tableColumns = [
    ...(isAdmin
      ? [
          {
            key: 'select',
            label: (
              <input
                type="checkbox"
                aria-label="Pilih semua nilai hasil filter"
                disabled={!filtered.length || busy}
                checked={filtered.length > 0 && selectedFiltered.length === filtered.length}
                onChange={toggleAllFilteredGrades}
                className="size-4 rounded accent-primary"
              />
            ),
            className: 'w-11'
          }
        ]
      : []),
    { key: 'npk', label: 'NPK Student' },
    { key: 'course', label: 'Course' },
    { key: 'quiz', label: 'Nilai Quiz' },
    { key: 'score', label: 'Nilai Course' },
    { key: 'progress', label: 'Progres Belajar' },
    { key: 'status', label: 'Status' },
    { key: 'updated', label: 'Diperbarui' },
    { key: 'actions', label: 'Aksi', className: 'text-right' }
  ];

  return (
    <div className="min-w-0 space-y-6">
      <PageHeader
        eyebrow="Penilaian & Progres"
        title="Kelola Nilai"
        description="Pantau snapshot nilai quiz, penilaian course, dan progres belajar student yang tersinkronisasi."
        actions={
          <Button
            type="button"
            variant="primary"
            icon={<Download className="size-4" />}
            disabled={!filtered.length}
            onClick={() => exportGrades(filtered)}
          >
            Ekspor Excel
          </Button>
        }
      />

      {/* Ringkasan Statistik */}
      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Total Rekaman"
          value={summary.total}
          icon={<ClipboardList className="size-5" />}
          tone="secondary"
          subtext="Data enrollment tersinkron"
          loading={loading}
        />
        <StatCard
          label="Rata-rata Progres"
          value={`${summary.avgProgress}%`}
          icon={<TrendingUp className="size-5" />}
          tone="info"
          subtext="Penyelesaian materi course"
          loading={loading}
        />
        <StatCard
          label="Rata-rata Nilai Quiz"
          value={summary.avgQuiz == null ? '—' : summary.avgQuiz.toLocaleString('id-ID')}
          icon={<Award className="size-5" />}
          tone="tertiary"
          subtext="Dari percobaan quiz student"
          loading={loading}
        />
        <StatCard
          label="Status Selesai"
          value={summary.completed}
          icon={<CheckCircle2 className="size-5" />}
          tone="primary"
          subtext="Peserta menyelesaikan course"
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

      <Card id="grades-list" className="scroll-mt-20" noPadding>
        <div className="border-b border-outline/40 p-5 pb-4 dark:border-slate-800">
          <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
            <p className="text-xs font-semibold text-muted">
              Menampilkan <strong className="text-on-surface">{filtered.length}</strong> dari{' '}
              <strong className="text-on-surface">{rows.length}</strong> data nilai dan progres
            </p>
            {isAdmin && selectedFiltered.length > 0 && (
              <Button
                type="button"
                variant="danger"
                size="sm"
                disabled={busy}
                onClick={deleteSelectedGrades}
                icon={<Trash2 className="size-4" />}
              >
                Hapus Nilai Terpilih ({selectedFiltered.length})
              </Button>
            )}
          </div>

          <ListFilterBar
            search={search}
            onSearch={setSearch}
            placeholder="Cari NPK student atau judul/kode course..."
            filter={status}
            onFilter={setStatus}
            label="Filter status"
            options={[
              { value: 'all', label: 'Semua status' },
              ...statuses.map((value) => ({ value, label: value }))
            ]}
          />
        </div>

        <DataTable
          columns={tableColumns}
          loading={loading}
          empty={!filtered.length}
          emptyMessage={
            rows.length
              ? 'Tidak ada data yang cocok dengan pencarian atau filter.'
              : 'Belum ada data progres tersinkron. Pastikan sinkronisasi Student aktif.'
          }
          ariaLabel="Tabel nilai student"
          className="mx-4 mb-4 rounded-lg shadow-none sm:mx-6 sm:mb-5"
        >
          {page.pagedItems.map((row, index) => (
            <tr key={`${row.student_npk}-${row.course_code}-${index}`}>
              {isAdmin && (
                <td>
                  <input
                    type="checkbox"
                    aria-label={`Pilih nilai ${row.student_npk} ${row.course_title || row.course_code}`}
                    disabled={busy}
                    checked={selectedGrades.has(`${row.student_npk}::${row.course_code}`)}
                    onChange={() => toggleGradeSelection(row)}
                    className="size-4 rounded accent-primary"
                  />
                </td>
              )}
              <td className="font-mono text-xs font-bold text-on-surface">{row.student_npk}</td>
              <td>
                <p className="font-semibold text-on-surface">
                  {row.course_title || row.course_code}
                </p>
                {row.course_title && (
                  <p className="font-mono text-[11px] text-muted">{row.course_code}</p>
                )}
              </td>
              <td>
                {row.quiz_score == null ? (
                  <span className="text-xs text-muted">Belum dikerjakan</span>
                ) : (
                  <span className="font-bold text-primary tabular-nums">
                    {Number(row.quiz_score).toLocaleString('id-ID')}
                  </span>
                )}
              </td>
              <td>
                {row.score === null || row.score === undefined ? (
                  <span className="text-xs text-muted">Belum dinilai</span>
                ) : (
                  <span className="font-bold text-on-surface tabular-nums">
                    {Number(row.score).toLocaleString('id-ID')}
                  </span>
                )}
                {row.grade_feedback && (
                  <p
                    className="mt-0.5 max-w-[200px] truncate text-xs text-muted"
                    title={row.grade_feedback}
                  >
                    {row.grade_feedback}
                  </p>
                )}
              </td>
              <td>
                <div className="flex items-center gap-2">
                  <div className="h-1.5 w-20 overflow-hidden rounded-full bg-surface-container dark:bg-slate-700">
                    <div
                      className="h-full rounded-full bg-secondary dark:bg-sky-400"
                      style={{ width: `${Math.min(100, Math.max(0, Number(row.progress || 0)))}%` }}
                    />
                  </div>
                  <span className="text-xs font-semibold tabular-nums text-on-surface">
                    {row.progress}%
                  </span>
                </div>
              </td>
              <td>
                <StatusBadge status={row.enrollment_status} dot />
              </td>
              <td className="whitespace-nowrap text-xs text-muted">
                {row.graded_at
                  ? new Date(row.graded_at).toLocaleString('id-ID')
                  : new Date(row.synced_at).toLocaleString('id-ID')}
              </td>
              <td className="text-right whitespace-nowrap">
                <div className="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                  {canEditGrades && (
                    <Button
                      type="button"
                      size="xs"
                      variant="outline"
                      disabled={busy}
                      icon={<Pencil className="size-3.5" />}
                      onClick={() =>
                        setEditing({
                          ...row,
                          score: row.score ?? '',
                          feedback: row.grade_feedback || ''
                        })
                      }
                    >
                      Edit Nilai
                    </Button>
                  )}

                  {canSendNotification && (
                    <Button
                      type="button"
                      size="xs"
                      variant="outline"
                      disabled={busy}
                      icon={<Mail className="size-3.5" />}
                      onClick={() => {
                        setNotifying(row);
                        setSendEmail(false);
                      }}
                    >
                      Kirim Pesan
                    </Button>
                  )}

                  {isAdmin && (
                    <Button
                      type="button"
                      size="xs"
                      variant="danger"
                      disabled={busy}
                      icon={<Trash2 className="size-3.5" />}
                      onClick={() => deleteGrade(row)}
                      title="Hapus nilai"
                    />
                  )}
                </div>
              </td>
            </tr>
          ))}
        </DataTable>

        <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
      </Card>

      {/* Modal Edit Nilai */}
      <Modal
        isOpen={Boolean(editing)}
        onClose={() => setEditing(null)}
        title="Edit Nilai Course"
        subtitle={
          editing ? `${editing.student_npk} · ${editing.course_title || editing.course_code}` : ''
        }
        maxWidth="max-w-lg"
      >
        {editing && (
          <form onSubmit={saveGrade} className="space-y-4">
            <label className="block text-xs font-bold text-on-surface-variant">
              Nilai (0–100) *
              <input
                autoFocus
                required
                type="number"
                min="0"
                max="100"
                step="0.01"
                value={editing.score}
                onChange={(event) => setEditing({ ...editing, score: event.target.value })}
                className={FIELD_CLASS}
              />
            </label>

            <label className="block text-xs font-bold text-on-surface-variant">
              Umpan Balik
              <textarea
                maxLength="1000"
                value={editing.feedback}
                onChange={(event) => setEditing({ ...editing, feedback: event.target.value })}
                rows="3"
                className="mt-1.5 w-full rounded-md border border-outline bg-surface-lowest p-3 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              />
            </label>

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button type="button" variant="outline" onClick={() => setEditing(null)}>
                Batal
              </Button>
              <Button type="submit" variant="primary" disabled={busy} loading={busy}>
                Simpan Nilai
              </Button>
            </div>
          </form>
        )}
      </Modal>

      {/* Modal Kirim Pesan */}
      <Modal
        isOpen={Boolean(notifying)}
        onClose={() => setNotifying(null)}
        title="Kirim Pesan ke Student"
        subtitle={
          notifying
            ? `Penerima: ${notifying.student_npk} · ${notifying.course_title || notifying.course_code}`
            : ''
        }
        maxWidth="max-w-lg"
      >
        {notifying && (
          <form onSubmit={sendNotification} className="space-y-4">
            <label className="block text-xs font-bold text-on-surface-variant">
              Judul *
              <input
                name="title"
                required
                minLength="3"
                maxLength="180"
                defaultValue={
                  notifying.score == null
                    ? `Pengingat course ${notifying.course_title || notifying.course_code}`
                    : `Nilai course ${notifying.course_title || notifying.course_code}`
                }
                className={FIELD_CLASS}
              />
            </label>

            <label className="block text-xs font-bold text-on-surface-variant">
              Pesan *
              <textarea
                name="message"
                required
                maxLength="2000"
                rows="4"
                defaultValue={
                  notifying.score == null
                    ? `Mohon selesaikan tugas pada course ${notifying.course_title || notifying.course_code}.`
                    : `Nilai Anda untuk course ${notifying.course_title || notifying.course_code} adalah ${notifying.score}. ${notifying.grade_feedback || ''}`
                }
                className="mt-1.5 w-full rounded-md border border-outline bg-surface-lowest p-3 text-sm text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              />
            </label>

            <label className="flex cursor-pointer items-start gap-3 rounded-lg border border-outline/50 bg-surface-low/50 p-3 text-xs dark:border-slate-800 dark:bg-slate-800/40">
              <input
                type="checkbox"
                checked={sendEmail}
                onChange={(event) => setSendEmail(event.target.checked)}
                className="mt-0.5 size-4 rounded accent-primary"
              />
              <span>
                <strong className="text-on-surface">Kirim juga melalui email</strong>
                <span className="mt-0.5 block text-muted">
                  Email masuk antrean jika SMTP aktif dan Student memiliki alamat email.
                </span>
              </span>
            </label>

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button type="button" variant="outline" onClick={() => setNotifying(null)}>
                Batal
              </Button>
              <Button type="submit" variant="primary" disabled={busy} loading={busy}>
                Kirim Pesan
              </Button>
            </div>
          </form>
        )}
      </Modal>
    </div>
  );
}
