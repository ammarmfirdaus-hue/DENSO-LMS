import { useEffect, useState } from 'react';
import { Server, RefreshCw, Save, Cable, CheckCircle2, AlertCircle } from 'lucide-react';
import { api, API_BASE, storeApiBaseUrl } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';

function editableApiBase() {
  return API_BASE.startsWith('/')
    ? `${window.location.protocol}//${window.location.hostname}:3001/api/v1`
    : API_BASE;
}

export default function ApiSettingsPage() {
  const [apiBaseUrl, setApiBaseUrl] = useState('');
  const [sync, setSync] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [syncing, setSyncing] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  async function load() {
    try {
      const config = await api('/settings/api');
      setApiBaseUrl(config.apiBaseUrl || editableApiBase());
      setSync(config.studentSync || null);
      setError('');
    } catch (loadError) {
      setError(loadError.message);
      showAlert.error('Gagal Memuat Pengaturan API', loadError.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { load(); }, []);

  async function testApi() {
    setMessage('');
    setError('');
    try {
      const base = new URL(apiBaseUrl.trim());
      const origin = base.origin;
      const response = await fetch(`${origin}/health`, { signal: AbortSignal.timeout(7000) });
      if (!response.ok) throw new Error(`Backend memberi HTTP ${response.status}.`);
      const result = await response.json();
      if (result.status !== 'ok') throw new Error('Backend merespons tetapi status health tidak valid.');
      const msg = `Backend terhubung dengan baik di ${origin}`;
      setMessage(msg);
      showAlert.success('Koneksi Berhasil', msg);
    } catch (testError) {
      const errMsg =
        testError.message || 'Tidak dapat menghubungi backend. Periksa URL dan izin CORS.';
      setError(errMsg);
      showAlert.error('Koneksi Backend Gagal', errMsg);
    }
  }

  async function save(event) {
    event.preventDefault();
    setSaving(true);
    setError('');
    setMessage('');
    try {
      const result = await api('/settings/api', { method: 'PUT', body: JSON.stringify({ apiBaseUrl }) });
      storeApiBaseUrl(result.apiBaseUrl);
      await showAlert.success(
        'Pengaturan API Tersimpan',
        'Alamat backend disimpan ke database. Halaman akan dimuat ulang memakai alamat baru.'
      );
      window.location.reload();
    } catch (saveError) {
      setError(saveError.message);
      showAlert.error('Gagal Menyimpan Pengaturan API', saveError.message);
    } finally {
      setSaving(false);
    }
  }

  async function syncStudent() {
    setSyncing(true);
    setError('');
    setMessage('');
    try {
      await api('/settings/api/student-sync', { method: 'POST', body: '{}' });
      const syncMsg = 'Sinkronisasi data Student berhasil dijalankan.';
      setMessage(syncMsg);
      showAlert.success('Sinkronisasi Berhasil', syncMsg);
      await load();
    } catch (syncError) {
      setError(syncError.message);
      showAlert.error('Sinkronisasi Gagal', syncError.message);
      await load();
    } finally {
      setSyncing(false);
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="ADMINISTRASI & INTEGRASI"
        title="Atur API"
        description="Kelola alamat Backend LMS bersama untuk portal Admin & Teacher serta aplikasi Native Student, serta jalankan sinkronisasi database."
      />

      {error && (
        <div role="alert" className="flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
          <span>{error}</span>
        </div>
      )}

      {message && (
        <div role="status" className="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
          <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
          <span>{message}</span>
        </div>
      )}

      <div className="grid gap-5 lg:grid-cols-2">
        <Card
          title="Backend API Admin, Teacher & Student"
          subtitle="URL ini disimpan terpusat di SQL Server agar seluruh klien memakai endpoint yang konsisten."
          action={
            <span className="grid size-9 place-items-center rounded-lg bg-primary/10 text-primary">
              <Server className="h-4 w-4" aria-hidden="true" />
            </span>
          }
        >
          <form onSubmit={save} className="space-y-4">
            <label className="block text-sm font-semibold text-on-surface">
              URL API Backend
              <input
                required
                type="url"
                maxLength="500"
                value={apiBaseUrl}
                onChange={event => setApiBaseUrl(event.target.value)}
                placeholder="https://lms.example.com/api/v1"
                disabled={loading || saving}
                className="mt-1.5 h-10 w-full rounded-md border border-outline bg-white px-3 font-mono text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
              />
            </label>

            <p className="text-xs leading-relaxed text-muted">
              Contoh lokal: <code className="rounded bg-surface-low px-1.5 py-0.5 font-mono">http://127.0.0.1:3001/api/v1</code>.
              Pastikan domain tersebut mendukung CORS serta cookie SSO untuk browser pengguna.
            </p>

            <div className="flex flex-wrap gap-2.5 pt-2">
              <Button
                variant="secondary"
                disabled={loading || saving || !apiBaseUrl}
                icon={<Cable className="h-4 w-4" />}
                onClick={testApi}
              >
                Uji Koneksi
              </Button>
              <Button
                type="submit"
                loading={saving}
                disabled={loading || !apiBaseUrl}
                icon={<Save className="h-4 w-4" />}
              >
                {saving ? 'Menyimpan...' : 'Simpan API'}
              </Button>
            </div>
          </form>
        </Card>

        <Card
          title="Sinkronisasi Native Student"
          subtitle="Sinkronisasi dua arah course, sesi, materi, progres belajar, dan nilai antara SQL Server & MySQL."
          action={
            <StatusBadge
              status={sync?.running ? 'active' : 'inactive'}
              label={sync?.running ? (sync.inFlight ? 'Sedang Sinkron' : 'Worker Aktif') : 'Nonaktif'}
              dot
            />
          }
        >
          {loading ? (
            <div className="space-y-3 animate-pulse">
              <div className="h-14 rounded-lg bg-surface-container" />
              <div className="h-14 rounded-lg bg-surface-container" />
            </div>
          ) : (
            <dl className="grid gap-3 sm:grid-cols-2">
              <InfoItem label="Status Worker" value={sync?.running ? (sync.inFlight ? 'Sinkronisasi berjalan' : 'Aktif') : 'Tidak aktif'} />
              <InfoItem label="Target MySQL" value={sync?.configured ? `${sync.host} / ${sync.database}` : 'Belum dikonfigurasi'} />
              <InfoItem
                label="Jadwal Berkala"
                value={sync?.enabled ? `Tiap ${Math.round((sync.intervalMs || 0) / 1000)} detik` : 'Nonaktif'}
              />
              <InfoItem
                label="Sinkronisasi Terakhir"
                value={sync?.lastSyncAt ? new Date(sync.lastSyncAt).toLocaleString('id-ID') : 'Belum ada data'}
              />
            </dl>
          )}

          {sync?.lastSyncError && (
            <p className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-medium text-red-800">
              Sinkronisasi terakhir gagal: {sync.lastSyncError}
            </p>
          )}

          <div className="mt-4 flex flex-col gap-3 border-t border-outline pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-xs leading-relaxed text-muted">
              Kredensial MySQL dikelola aman melalui file <code className="font-mono">.env</code> server.
            </p>
            <Button
              loading={syncing}
              disabled={loading || !sync?.configured}
              icon={<RefreshCw className="h-4 w-4" />}
              onClick={syncStudent}
            >
              {syncing ? 'Menyinkronkan...' : 'Sinkronkan Sekarang'}
            </Button>
          </div>
        </Card>
      </div>
    </div>
  );
}

function InfoItem({ label, value }) {
  return (
    <div className="min-w-0 rounded-lg border border-outline bg-surface-low/60 p-3">
      <dt className="text-xs font-medium text-muted">{label}</dt>
      <dd className="mt-1 break-words font-mono text-xs font-semibold text-on-surface">{value || '—'}</dd>
    </div>
  );
}
