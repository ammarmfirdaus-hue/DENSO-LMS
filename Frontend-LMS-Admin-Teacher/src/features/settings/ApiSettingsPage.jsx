import { useEffect, useState } from 'react';
import { api, API_BASE, storeApiBaseUrl } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Button from '../../components/Button';
import showAlert from '../../utils/alert';

const inputClass = 'mt-1.5 min-h-11 w-full rounded-lg border border-outline/60 bg-white px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10';
const buttonClass = 'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-outline/60 bg-white px-4 text-sm font-semibold text-secondary transition hover:bg-surface-low disabled:cursor-not-allowed disabled:opacity-50';

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
    } catch (loadError) { setError(loadError.message); }
    finally { setLoading(false); }
  }

  useEffect(() => { load(); }, []);

  async function testApi() {
    setMessage(''); setError('');
    try {
      const base = new URL(apiBaseUrl.trim());
      const origin = base.origin;
      const response = await fetch(`${origin}/health`, { signal: AbortSignal.timeout(7000) });
      if (!response.ok) throw new Error(`Backend memberi HTTP ${response.status}.`);
      const result = await response.json();
      if (result.status !== 'ok') throw new Error('Backend merespons tetapi status health tidak valid.');
      setMessage(`Backend terhubung: ${origin}`);
    } catch (testError) {
      setError(testError.message || 'Tidak dapat menghubungi backend. Periksa URL dan izin CORS.');
    }
  }

  async function save(event) {
    event.preventDefault();
    setSaving(true); setError(''); setMessage('');
    try {
      const result = await api('/settings/api', { method: 'PUT', body: JSON.stringify({ apiBaseUrl }) });
      storeApiBaseUrl(result.apiBaseUrl);
      await showAlert.success('Pengaturan API Tersimpan', 'Alamat backend disimpan ke database. Halaman akan dimuat ulang memakai alamat baru.');
      window.location.reload();
    } catch (saveError) { setError(saveError.message); }
    finally { setSaving(false); }
  }

  async function syncStudent() {
    setSyncing(true); setError(''); setMessage('');
    try {
      await api('/settings/api/student-sync', { method: 'POST', body: '{}' });
      setMessage('Sinkronisasi Student berhasil dijalankan.');
      await load();
    } catch (syncError) { setError(syncError.message); await load(); }
    finally { setSyncing(false); }
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6">
      <PageHeader
        eyebrow="ADMINISTRASI & MANAJEMEN"
        title="Atur API"
        description="Atur alamat Backend LMS untuk portal Admin & Teacher dan aplikasi Native PHP Student, serta jalankan sinkronisasi data Student."
      />
    {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{error}</p>}
    {message && <p role="status" className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{message}</p>}

    <section className="rounded-2xl border border-outline/50 bg-white p-5 shadow-sm sm:p-6">
      <div className="flex items-start gap-3"><span className="grid size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary"><span className="material-symbol" aria-hidden="true">api</span></span><div><h2 className="text-lg font-bold">Backend API Admin, Teacher &amp; Student</h2><p className="mt-1 text-sm leading-6 text-muted">Alamat ini disimpan di database backend dan dipakai portal Admin &amp; Teacher serta Native PHP Student. Gunakan URL yang dapat dijangkau server Student dan browser, termasuk <code className="rounded bg-surface-low px-1">/api/v1</code>.</p></div></div>
      <form onSubmit={save} className="mt-5 space-y-4">
        <label className="block text-sm font-semibold">URL API Backend<input required type="url" maxLength="500" value={apiBaseUrl} onChange={event => setApiBaseUrl(event.target.value)} placeholder="https://lms.example.com/api/v1" className={inputClass} disabled={loading || saving} /></label>
        <p className="text-xs leading-5 text-muted">Contoh lokal: <code>http://127.0.0.1:3001/api/v1</code>. Setelah disimpan, portal tersambung ulang menggunakan alamat ini. Browser perlu mengizinkan CORS dan cookie SSO pada domain tersebut.</p>
        <div className="flex flex-wrap gap-2"><button type="button" disabled={loading || saving || !apiBaseUrl} onClick={testApi} className={buttonClass}><span className="material-symbol" aria-hidden="true">lan</span>Uji koneksi</button><button type="submit" disabled={loading || saving || !apiBaseUrl} className="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:brightness-95 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">save</span>{saving ? 'Menyimpan...' : 'Simpan API'}</button></div>
      </form>
    </section>

    <section className="rounded-2xl border border-outline/50 bg-white p-5 shadow-sm sm:p-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div className="flex items-start gap-3"><span className="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><span className="material-symbol" aria-hidden="true">sync</span></span><div><h2 className="text-lg font-bold">Sinkronisasi Native Student</h2><p className="mt-1 max-w-2xl text-sm leading-6 text-muted">Backend menyinkronkan course, sesi, materi, progres, dan nilai antara SQL Server Admin/Teacher dan database MySQL yang dipakai aplikasi PHP Student.</p></div></div><button type="button" disabled={syncing || loading || !sync?.configured} onClick={syncStudent} className="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-50"><span className="material-symbol" aria-hidden="true">sync</span>{syncing ? 'Menyinkronkan...' : 'Sinkronkan sekarang'}</button></div>
      {loading ? <p className="mt-5 text-sm text-muted">Memuat status integrasi...</p> : <dl className="mt-5 grid gap-3 sm:grid-cols-2">
        <Info label="Status worker" value={sync?.running ? (sync.inFlight ? 'Sinkronisasi berjalan' : 'Aktif') : 'Tidak aktif'} />
        <Info label="Koneksi Student" value={sync?.configured ? `${sync.host} / ${sync.database}` : 'Belum dikonfigurasi'} />
        <Info label="Sinkronisasi berkala" value={sync?.enabled ? `Aktif · tiap ${Math.round(sync.intervalMs / 1000)} detik` : 'Nonaktif'} />
        <Info label="Sinkronisasi terakhir" value={sync?.lastSyncAt ? new Date(sync.lastSyncAt).toLocaleString('id-ID') : 'Belum ada data'} />
      </dl>}
      {sync?.lastSyncError && <p className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">Sinkronisasi terakhir gagal: {sync.lastSyncError}</p>}
      <p className="mt-4 rounded-lg border border-outline/40 bg-surface-low/50 p-3 text-xs leading-5 text-muted">Kredensial dan alamat database MySQL Student tetap dikelola lewat konfigurasi <code>.env</code> backend. Halaman ini tidak menampilkan atau menyimpan password database. URL backend yang disimpan di atas juga digunakan Native PHP Student; <code>SSO_API_URL</code> pada <code>.env</code> Student hanya menjadi alamat bootstrap/fallback.</p>
    </section>
  </div>
  );
}
function Info({ label, value }) {
  return <div className="min-w-0 rounded-xl border border-outline/40 bg-surface-low/40 p-3"><dt className="text-xs font-medium text-muted">{label}</dt><dd className="mt-1 break-words text-sm font-semibold text-secondary">{value || '—'}</dd></div>;
}
