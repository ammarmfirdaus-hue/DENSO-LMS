import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Archive, ArrowRight, CheckCircle2, Clock, Database, Download, ShieldCheck, Trash2 } from 'lucide-react';
import { fetchApi } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';

const BACKUP_HISTORY_KEY = 'dls_lms_backup_history_v1';

export const DATASETS = [
  ['employees', 'Pengguna'],
  ['courses', 'Course'],
  ['sessions', 'Sesi course'],
  ['materials', 'Materi'],
  ['submissions', 'Pengajuan materi'],
  ['reviewLinks', 'Tugas reviewer'],
  ['enrollments', 'Progres student'],
  ['forumThreads', 'Diskusi forum'],
  ['forumReplies', 'Balasan forum'],
  ['notifications', 'Notifikasi'],
  ['activity', 'Aktivitas'],
  ['settings', 'Pengaturan sistem'],
  ['roleDefinitions', 'Definisi role'],
  ['rolePermissions', 'Hak akses']
];

const DATASET_LABEL_MAP = Object.fromEntries(DATASETS);

function readBackupHistory() {
  try {
    const raw = localStorage.getItem(BACKUP_HISTORY_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function writeBackupHistory(entries) {
  try {
    localStorage.setItem(BACKUP_HISTORY_KEY, JSON.stringify(entries.slice(0, 20)));
  } catch {}
}

function formatBytes(bytes) {
  const num = Number(bytes || 0);
  if (num < 1024) return `${num} B`;
  if (num < 1024 * 1024) return `${(num / 1024).toFixed(1)} KB`;
  return `${(num / (1024 * 1024)).toFixed(2)} MB`;
}

export default function BackupPage() {
  const [selected, setSelected] = useState([]);
  const [mode, setMode] = useState('selected');
  const [busy, setBusy] = useState(false);
  const [history, setHistory] = useState(() => readBackupHistory());

  function toggle(key) {
    setSelected((current) =>
      current.includes(key) ? current.filter((item) => item !== key) : [...current, key]
    );
  }

  async function downloadBackup() {
    const keys = mode === 'all' ? DATASETS.map(([key]) => key) : selected;
    if (!keys.length) {
      return showAlert.warning('Pilih data terlebih dahulu', 'Centang minimal satu jenis data pada mode Data pilihan.');
    }
    const confirmed = await showAlert.confirm({
      title: 'Buat salinan backup sekarang?',
      text: mode === 'all'
        ? 'Semua jenis data LMS yang tersedia akan diunduh.'
        : `${keys.length} jenis data pilihan Anda akan diunduh ke file JSON.`,
      confirmText: 'Ya, Unduh Backup',
      cancelText: 'Batal',
      icon: 'question'
    });
    if (!confirmed) return;

    setBusy(true);
    try {
      const response = await fetchApi('/settings/backup', {
        method: 'POST',
        body: JSON.stringify({ scope: mode, datasets: keys }),
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        credentials: 'include'
      });
      if (!response.ok) {
        const body = await response.json().catch(() => ({}));
        throw new Error(body?.error?.message || `Backup gagal (HTTP ${response.status}).`);
      }

      const blob = await response.blob();
      const filename = `dls-lms-backup-${new Date().toISOString().slice(0, 10)}.json`;
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);

      const entry = {
        id: `${Date.now()}`,
        createdAt: new Date().toISOString(),
        filename,
        scope: mode,
        datasets: keys,
        sizeBytes: blob.size
      };
      const nextHistory = [entry, ...history].slice(0, 20);
      setHistory(nextHistory);
      writeBackupHistory(nextHistory);

      showAlert.success('Backup Berhasil Diunduh', 'File JSON backup sudah tersimpan di perangkat Anda.');
    } catch (error) {
      showAlert.error('Backup Gagal', error);
    } finally {
      setBusy(false);
    }
  }

  async function clearHistory() {
    const ok = await showAlert.confirm({
      title: 'Bersihkan riwayat backup?',
      text: 'Catatan riwayat unduhan di browser ini akan dihapus.',
      confirmText: 'Bersihkan',
      cancelText: 'Batal',
      icon: 'warning'
    });
    if (!ok) return;
    setHistory([]);
    writeBackupHistory([]);
  }

  return (
    <div className="min-w-0 space-y-6">
      <PageHeader
        eyebrow="Administrasi Data"
        title="Backup Data"
        description="Unduh salinan data LMS dalam format JSON secara aman sesuai kebutuhan Anda."
        actions={
          <Link
            to="/settings/backup/restore"
            className="inline-flex h-10 items-center gap-2 rounded-lg border border-outline bg-surface-lowest px-3.5 text-xs font-semibold text-on-surface transition hover:border-primary hover:text-primary"
          >
            <span>Buka Pemulihan Data</span>
            <ArrowRight className="size-3.5" />
          </Link>
        }
      />

      <div className="grid gap-5 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <Card
          title="Pengaturan Backup"
          subtitle="Tentukan apakah ingin mencadangkan data tertentu atau seluruh data."
          action={<Archive className="size-5 text-primary" />}
        >
          <div className="space-y-2.5">
            <label
              className={`flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition ${
                mode === 'selected'
                  ? 'border-primary bg-primary/5'
                  : 'border-outline hover:border-primary/50'
              }`}
            >
              <input
                type="radio"
                name="backup-mode"
                checked={mode === 'selected'}
                onChange={() => setMode('selected')}
                className="mt-1 accent-primary"
              />
              <span>
                <strong className="block text-sm text-on-surface">Data pilihan (Manual)</strong>
                <span className="text-xs text-muted">
                  Centang sendiri jenis data yang ingin dicadangkan.
                </span>
              </span>
            </label>

            <label
              className={`flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition ${
                mode === 'all'
                  ? 'border-primary bg-primary/5'
                  : 'border-outline hover:border-primary/50'
              }`}
            >
              <input
                type="radio"
                name="backup-mode"
                checked={mode === 'all'}
                onChange={() => setMode('all')}
                className="mt-1 accent-primary"
              />
              <span>
                <strong className="block text-sm text-on-surface">Semua data LMS</strong>
                <span className="text-xs text-muted">
                  Salinan lengkap seluruh {DATASETS.length} tabel utama sistem.
                </span>
              </span>
            </label>
          </div>

          <div className="mt-4 rounded-xl border border-emerald-200/70 bg-emerald-50/70 p-3 text-xs text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
            <div className="flex items-start gap-2">
              <ShieldCheck className="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
              <span>
                File backup dibuat langsung saat Anda menekan tombol unduh dan tidak ditinggalkan di server.
              </span>
            </div>
          </div>

          <Button
            className="mt-5 w-full"
            loading={busy}
            disabled={busy}
            onClick={downloadBackup}
            icon={<Download className="size-4" />}
          >
            {mode === 'all'
              ? `Unduh Semua Data (${DATASETS.length})`
              : `Unduh Data Pilihan (${selected.length})`}
          </Button>
        </Card>

        <Card
          title="Daftar Data Tersedia"
          subtitle={
            mode === 'all'
              ? 'Semua jenis data akan disertakan otomatis.'
              : `${selected.length} dari ${DATASETS.length} jenis data dipilih secara manual.`
          }
          action={
            mode === 'selected' ? (
              <div className="flex items-center gap-1.5">
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={selected.length === DATASETS.length}
                  onClick={() => setSelected(DATASETS.map(([key]) => key))}
                >
                  Pilih Semua
                </Button>
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={selected.length === 0}
                  onClick={() => setSelected([])}
                >
                  Kosongkan
                </Button>
              </div>
            ) : (
              <Database className="size-5 text-primary" />
            )
          }
        >
          <div
            className={`grid gap-2.5 sm:grid-cols-2 ${
              mode === 'all' ? 'pointer-events-none opacity-65' : ''
            }`}
          >
            {DATASETS.map(([key, label]) => {
              const isChecked = mode === 'all' || selected.includes(key);
              return (
                <label
                  key={key}
                  className={`flex cursor-pointer items-center justify-between gap-2 rounded-lg border px-3 py-2.5 text-sm text-on-surface transition ${
                    isChecked
                      ? 'border-primary bg-primary/5 font-medium'
                      : 'border-outline hover:border-primary/60'
                  }`}
                >
                  <span className="flex items-center gap-2.5">
                    <input
                      type="checkbox"
                      checked={isChecked}
                      onChange={() => toggle(key)}
                      className="size-4 accent-primary"
                    />
                    <span>{label}</span>
                  </span>
                  {isChecked && <CheckCircle2 className="size-4 shrink-0 text-primary" />}
                </label>
              );
            })}
          </div>
          <p className="mt-4 text-xs text-muted">
            Format keluaran: JSON terstruktur (`dls-lms-json-backup`). Simpan di folder aman dan batasi aksesnya.
          </p>
        </Card>
      </div>

      {/* Riwayat Backup */}
      <Card
        title="Riwayat Backup"
        subtitle="Daftar file backup yang pernah Anda unduh dari perangkat ini."
        action={
          history.length > 0 && (
            <Button
              type="button"
              size="sm"
              variant="outline"
              onClick={clearHistory}
              icon={<Trash2 className="size-3.5" />}
            >
              Bersihkan Riwayat
            </Button>
          )
        }
      >
        {history.length === 0 ? (
          <div className="py-8 text-center">
            <Clock className="mx-auto mb-2 size-8 text-muted opacity-50" />
            <p className="text-sm font-semibold text-on-surface">Belum ada riwayat backup</p>
            <p className="mt-1 text-xs text-muted">
              Setiap file backup yang selesai diunduh akan tercatat otomatis di sini.
            </p>
          </div>
        ) : (
          <div className="space-y-2.5">
            {history.map((item) => (
              <div
                key={item.id}
                className="flex flex-col gap-2 rounded-xl border border-outline/60 bg-surface-low/50 p-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-800/40"
              >
                <div className="min-w-0 space-y-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="truncate font-mono text-xs font-bold text-on-surface">
                      {item.filename}
                    </span>
                    <StatusBadge status="completed" label="Berhasil Diunduh" dot />
                    <StatusBadge
                      status={item.scope === 'all' ? 'info' : 'neutral'}
                      label={item.scope === 'all' ? 'Semua Data' : 'Data Pilihan'}
                    />
                  </div>
                  <p className="text-xs text-muted">
                    {(item.datasets || []).map((k) => DATASET_LABEL_MAP[k] || k).join(', ')}
                  </p>
                </div>

                <div className="flex shrink-0 items-center gap-3 text-xs text-muted sm:text-right">
                  <span className="font-mono font-semibold text-on-surface-variant">
                    {formatBytes(item.sizeBytes)}
                  </span>
                  <span>·</span>
                  <time>{new Date(item.createdAt).toLocaleString('id-ID')}</time>
                </div>
              </div>
            ))}
          </div>
        )}
      </Card>
    </div>
  );
}
