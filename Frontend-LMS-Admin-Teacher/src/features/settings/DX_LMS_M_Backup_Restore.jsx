import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import {
  ArrowLeft,
  Clock,
  FileJson,
  RefreshCw,
  RotateCcw,
  ShieldAlert,
  UploadCloud
} from 'lucide-react';
import { api, fetchApi } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';
import { DATASETS } from './DX_LMS_M_Backup';

const DATASET_LABEL_MAP = Object.fromEntries(DATASETS);

function formatBytes(bytes) {
  const num = Number(bytes || 0);
  if (num < 1024) return `${num} B`;
  if (num < 1024 * 1024) return `${(num / 1024).toFixed(1)} KB`;
  return `${(num / (1024 * 1024)).toFixed(2)} MB`;
}

export default function BackupRestorePage() {
  const [restoreFile, setRestoreFile] = useState(null);
  const [filePreview, setFilePreview] = useState(null);
  const [fileError, setFileError] = useState('');
  const [restoreBusy, setRestoreBusy] = useState(false);
  const [restoreJob, setRestoreJob] = useState(null);
  const [history, setHistory] = useState([]);
  const [loadingHistory, setLoadingHistory] = useState(true);

  const loadRestoreHistory = useCallback(async () => {
    setLoadingHistory(true);
    try {
      const result = await api('/jobs');
      const allJobs = Array.isArray(result.jobs) ? result.jobs : [];
      const restoreJobs = allJobs.filter((j) => j.job_type === 'backup_restore');
      setHistory(restoreJobs);
    } catch {
      /* Abaikan error sementara saat polling daftar job */
    } finally {
      setLoadingHistory(false);
    }
  }, []);

  useEffect(() => {
    loadRestoreHistory();
  }, [loadRestoreHistory]);

  useEffect(() => {
    if (!restoreJob?.jobId) return undefined;
    let active = true;
    const poll = async () => {
      try {
        const result = await fetchApi(`/jobs/${restoreJob.jobId}/events?after=0`, {
          credentials: 'include'
        });
        if (!result.ok) return;
        const body = await result.json();
        if (!active || !body.job) return;
        setRestoreJob((current) => ({ ...current, ...body.job }));
        if (['completed', 'failed', 'cancelled'].includes(body.job.status)) {
          loadRestoreHistory();
        }
      } catch {
        /* Fallback ke refresh manual jika polling gagal */
      }
    };
    poll();
    const timer = window.setInterval(poll, 1500);
    return () => {
      active = false;
      window.clearInterval(timer);
    };
  }, [restoreJob?.jobId, loadRestoreHistory]);

  async function handleFileChange(event) {
    const file = event.target.files?.[0] || null;
    setRestoreFile(file);
    setFilePreview(null);
    setFileError('');
    if (!file) return;

    try {
      const text = await file.text();
      const parsed = JSON.parse(text);
      if (!parsed || parsed.format !== 'dls-lms-json-backup' || !parsed.datasets || typeof parsed.datasets !== 'object') {
        setFileError('Format file tidak dikenali. Pastikan file berasal dari Backup Data DENSO LMS.');
        return;
      }
      const datasetSummary = Object.entries(parsed.datasets).map(([key, val]) => ({
        key,
        label: DATASET_LABEL_MAP[key] || key,
        count: Array.isArray(val?.rows) ? val.rows.length : Number(val?.count || 0)
      }));
      setFilePreview({
        exportedAt: parsed.exportedAt || null,
        exportedBy: parsed.exportedBy || '-',
        scope: parsed.scope || 'selected',
        datasets: datasetSummary
      });
    } catch {
      setFileError('File bukan JSON yang valid atau rusak.');
    }
  }

  async function restoreBackup() {
    if (!restoreFile) {
      return showAlert.warning('Pilih file', 'Pilih file backup JSON terlebih dahulu.');
    }
    if (fileError) {
      return showAlert.error('File tidak valid', fileError);
    }
    const confirmed = await showAlert.confirm({
      title: 'Mulai pemulihan data?',
      text: 'Data dari file backup akan diproses melalui antrean dan memperbarui data yang cocok.',
      confirmText: 'Ya, Pulihkan Data',
      cancelText: 'Batal',
      icon: 'warning'
    });
    if (!confirmed) return;

    setRestoreBusy(true);
    try {
      const formData = new FormData();
      formData.append('backup', restoreFile);
      const response = await fetchApi('/settings/backup/restore', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const body = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(body?.error?.message || `Restore gagal (HTTP ${response.status}).`);
      }
      setRestoreJob({
        jobId: body.jobId,
        status: 'queued',
        total_items: body.total,
        processed_items: 0,
        success_count: 0,
        failure_count: 0
      });
      showAlert.success('Pemulihan Masuk Antrean', 'Proses pemulihan sedang berjalan dan dapat dipantau di halaman ini.');
      loadRestoreHistory();
    } catch (error) {
      showAlert.error('Pemulihan Gagal', error);
    } finally {
      setRestoreBusy(false);
    }
  }

  return (
    <div className="min-w-0 space-y-6">
      <PageHeader
        eyebrow="Administrasi Data"
        title="Pemulihan Data"
        description="Unggah file JSON hasil backup untuk mengembalikan data LMS melalui antrean secara aman."
        actions={
          <Link
            to="/settings/backup"
            className="inline-flex h-10 items-center gap-2 rounded-lg border border-outline bg-surface-lowest px-3.5 text-xs font-semibold text-on-surface transition hover:border-primary hover:text-primary"
          >
            <ArrowLeft className="size-3.5" />
            <span>Kembali ke Backup Data</span>
          </Link>
        }
      />

      <div className="grid gap-5 lg:grid-cols-2 lg:items-start">
        <Card
          title="Unggah File Backup"
          subtitle="Pilih file `.json` hasil ekspor Backup Data untuk diperiksa sebelum dipulihkan."
          action={<UploadCloud className="size-5 text-primary" />}
        >
          <div className="space-y-4">
            <label className="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-outline bg-surface-low/50 p-6 text-center transition hover:border-primary/60 dark:border-slate-700 dark:bg-slate-800/40">
              <FileJson className="size-8 text-primary" />
              <div className="space-y-0.5">
                <p className="text-sm font-semibold text-on-surface">
                  {restoreFile ? restoreFile.name : 'Klik untuk memilih file backup (.json)'}
                </p>
                <p className="text-xs text-muted">
                  {restoreFile ? `Ukuran: ${formatBytes(restoreFile.size)}` : 'Maksimal ukuran file 25 MB'}
                </p>
              </div>
              <input
                type="file"
                accept="application/json,.json"
                onChange={handleFileChange}
                disabled={restoreBusy}
                className="sr-only"
              />
            </label>

            {fileError && (
              <div className="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-medium text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                {fileError}
              </div>
            )}

            {filePreview && (
              <div className="rounded-xl border border-outline/60 bg-surface-low/60 p-3.5 text-xs space-y-2 dark:border-slate-800 dark:bg-slate-800/40">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <span className="font-bold text-on-surface">Pratinjau Isi Backup</span>
                  <StatusBadge status="active" label="Format Valid" dot />
                </div>
                <p className="text-muted">
                  Diekspor oleh <strong className="text-on-surface">{filePreview.exportedBy}</strong>
                  {filePreview.exportedAt ? ` pada ${new Date(filePreview.exportedAt).toLocaleString('id-ID')}` : ''}
                </p>
                <div className="flex flex-wrap gap-1.5 pt-1">
                  {filePreview.datasets.map((ds) => (
                    <span
                      key={ds.key}
                      className="rounded-full border border-outline/60 bg-surface-lowest px-2.5 py-0.5 text-[11px] font-medium text-on-surface-variant dark:border-slate-700 dark:bg-slate-900"
                    >
                      {ds.label}: <strong className="font-mono">{ds.count}</strong>
                    </span>
                  ))}
                </div>
              </div>
            )}

            <div className="rounded-xl border border-amber-200/80 bg-amber-50/70 p-3 text-xs text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
              <div className="flex items-start gap-2">
                <ShieldAlert className="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                <span>
                  Pemulihan memproses baris per batch melalui antrean latar belakang agar server tetap stabil.
                </span>
              </div>
            </div>

            <Button
              className="w-full"
              loading={restoreBusy}
              disabled={restoreBusy || !restoreFile || Boolean(fileError)}
              onClick={restoreBackup}
              icon={<RotateCcw className="size-4" />}
            >
              Mulai Pulihkan Data
            </Button>
          </div>
        </Card>

        <Card
          title="Progres Pemulihan Aktif"
          subtitle="Pantau proses antrean restore yang baru saja dijalankan."
          action={
            restoreJob && <StatusBadge status={restoreJob.status} dot />
          }
        >
          {!restoreJob ? (
            <div className="py-10 text-center">
              <RotateCcw className="mx-auto mb-2.5 size-8 text-muted opacity-40" />
              <p className="text-sm font-semibold text-on-surface">Belum ada proses berjalan</p>
              <p className="mt-1 text-xs text-muted">
                Setelah Anda menekan &ldquo;Mulai Pulihkan Data&rdquo;, progres per batch akan muncul secara langsung di sini.
              </p>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="grid grid-cols-3 gap-2.5 text-center">
                <div className="rounded-xl border border-outline/50 bg-surface-low/60 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                  <span className="block text-[11px] text-muted">Diproses</span>
                  <strong className="font-mono text-lg font-bold text-on-surface">
                    {restoreJob.processed_items || 0}/{restoreJob.total_items || 0}
                  </strong>
                </div>
                <div className="rounded-xl border border-emerald-200/60 bg-emerald-50/60 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                  <span className="block text-[11px] text-emerald-700 dark:text-emerald-300">Berhasil</span>
                  <strong className="font-mono text-lg font-bold text-emerald-700 dark:text-emerald-300">
                    {restoreJob.success_count || 0}
                  </strong>
                </div>
                <div className="rounded-xl border border-red-200/60 bg-red-50/60 p-3 dark:border-red-900/50 dark:bg-red-950/30">
                  <span className="block text-[11px] text-red-700 dark:text-red-300">Gagal</span>
                  <strong className="font-mono text-lg font-bold text-red-700 dark:text-red-300">
                    {restoreJob.failure_count || 0}
                  </strong>
                </div>
              </div>

              <div>
                <div className="mb-1.5 flex items-center justify-between text-xs text-muted">
                  <span>Job #{restoreJob.jobId}</span>
                  <span className="font-mono font-semibold text-on-surface">
                    {Math.round(
                      ((restoreJob.processed_items || 0) / Math.max(1, restoreJob.total_items || 1)) * 100
                    )}%
                  </span>
                </div>
                <div className="h-2.5 overflow-hidden rounded-full bg-surface-container">
                  <div
                    className="h-full rounded-full bg-primary transition-all"
                    style={{
                      width: `${Math.min(
                        100,
                        ((restoreJob.processed_items || 0) / Math.max(1, restoreJob.total_items || 1)) * 100
                      )}%`
                    }}
                  />
                </div>
              </div>
            </div>
          )}
        </Card>
      </div>

      {/* Riwayat Pemulihan Data */}
      <Card
        title="Riwayat Pemulihan Data"
        subtitle="Daftar pekerjaan restore yang tercatat di antrean server."
        action={
          <Button
            type="button"
            size="sm"
            variant="outline"
            loading={loadingHistory}
            onClick={loadRestoreHistory}
            icon={<RefreshCw className="size-3.5" />}
          >
            Muat Ulang
          </Button>
        }
      >
        {history.length === 0 ? (
          <div className="py-8 text-center">
            <Clock className="mx-auto mb-2 size-8 text-muted opacity-50" />
            <p className="text-sm font-semibold text-on-surface">
              {loadingHistory ? 'Memuat riwayat pemulihan...' : 'Belum ada riwayat pemulihan data'}
            </p>
            <p className="mt-1 text-xs text-muted">
              Proses pemulihan yang dijalankan akan tercatat otomatis di sini.
            </p>
          </div>
        ) : (
          <div className="space-y-2.5">
            {history.map((job) => (
              <div
                key={job.job_id}
                className="flex flex-col gap-2 rounded-xl border border-outline/60 bg-surface-low/50 p-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-800/40"
              >
                <div className="min-w-0 space-y-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-mono text-xs font-bold text-on-surface">
                      Restore Job #{job.job_id}
                    </span>
                    <StatusBadge status={job.status || 'pending'} dot />
                  </div>
                  <p className="text-xs text-muted">
                    Diproses {job.processed_items || 0}/{job.total_items || 0} batch · Berhasil{' '}
                    <strong className="text-emerald-700 dark:text-emerald-300">
                      {job.success_count || 0}
                    </strong>{' '}
                    · Gagal{' '}
                    <strong className="text-red-700 dark:text-red-300">
                      {job.failure_count || 0}
                    </strong>
                    {job.owner_npk ? ` · Oleh ${job.owner_npk}` : ''}
                  </p>
                </div>

                <time className="shrink-0 text-xs text-muted">
                  {job.created_at ? new Date(job.created_at).toLocaleString('id-ID') : '-'}
                </time>
              </div>
            ))}
          </div>
        )}
      </Card>
    </div>
  );
}

