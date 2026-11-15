import { useEffect, useState } from 'react';
import { Archive, Database, Download, ShieldCheck } from 'lucide-react';
import { fetchApi } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import showAlert from '../../utils/alert';

const DATASETS = [
  ['employees', 'Pengguna'], ['courses', 'Course'], ['sessions', 'Sesi course'],
  ['materials', 'Materi'], ['submissions', 'Pengajuan materi'], ['reviewLinks', 'Tugas reviewer'],
  ['enrollments', 'Progres student'], ['forumThreads', 'Diskusi forum'], ['forumReplies', 'Balasan forum'],
  ['notifications', 'Notifikasi'], ['activity', 'Aktivitas'], ['settings', 'Pengaturan sistem'],
  ['roleDefinitions', 'Definisi role'], ['rolePermissions', 'Hak akses']
];

export default function BackupPage() {
  const [selected, setSelected] = useState(DATASETS.map(([key]) => key));
  const [mode, setMode] = useState('selected');
  const [busy, setBusy] = useState(false);
  const [restoreBusy, setRestoreBusy] = useState(false);
  const [restoreFile, setRestoreFile] = useState(null);
  const [restoreJob, setRestoreJob] = useState(null);
  const [lastBackup, setLastBackup] = useState('');

  function toggle(key) {
    setSelected(current => current.includes(key) ? current.filter(item => item !== key) : [...current, key]);
  }

  async function downloadBackup() {
    const keys = mode === 'all' ? DATASETS.map(([key]) => key) : selected;
    if (!keys.length) return showAlert.warning('Pilih data', 'Pilih minimal satu jenis data.');
    const confirmed = await showAlert.confirm({
      title: 'Buat backup sekarang?',
      text: mode === 'all' ? 'Semua data yang tersedia akan diunduh.' : `${keys.length} jenis data akan diunduh.`,
      confirmText: 'Buat backup', cancelText: 'Batal', icon: 'question'
    });
    if (!confirmed) return;
    setBusy(true);
    try {
      const response = await fetchApi('/settings/backup', {
        method: 'POST', body: JSON.stringify({ scope: mode, datasets: keys }),
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' }, credentials: 'include'
      });
      if (!response.ok) {
        const body = await response.json().catch(() => ({}));
        throw new Error(body?.error?.message || `Backup gagal (HTTP ${response.status}).`);
      }
      const blob = await response.blob();
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `dls-lms-backup-${new Date().toISOString().slice(0, 10)}.json`;
      document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(url);
      setLastBackup(new Date().toLocaleString('id-ID'));
      showAlert.success('Backup siap', 'File backup sudah diunduh ke perangkat Anda.');
    } catch (error) { showAlert.error('Backup gagal', error); }
    finally { setBusy(false); }
  }

  useEffect(() => {
    if (!restoreJob?.jobId) return undefined;
    let active = true;
    const poll = async () => {
      try {
        const result = await fetchApi(`/jobs/${restoreJob.jobId}/events?after=0`, { credentials: 'include' });
        if (!result.ok) return;
        const body = await result.json();
        if (!active || !body.job) return;
        setRestoreJob(current => ({ ...current, ...body.job }));
      } catch { /* JobsCenter remains the fallback for transient polling errors. */ }
    };
    poll();
    const timer = window.setInterval(poll, 1500);
    return () => { active = false; window.clearInterval(timer); };
  }, [restoreJob?.jobId]);

  async function restoreBackup() {
    if (!restoreFile) return showAlert.warning('Pilih file', 'Pilih file backup JSON terlebih dahulu.');
    const confirmed = await showAlert.confirm({
      title: 'Pulihkan data?',
      text: 'Data pada file akan diproses melalui antrean dan memperbarui data yang sama.',
      confirmText: 'Masukkan antrean', cancelText: 'Batal', icon: 'warning'
    });
    if (!confirmed) return;
    setRestoreBusy(true);
    try {
      const formData = new FormData();
      formData.append('backup', restoreFile);
      const response = await fetchApi('/settings/backup/restore', { method: 'POST', body: formData, credentials: 'include' });
      const body = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(body?.error?.message || `Restore gagal (HTTP ${response.status}).`);
      setRestoreJob({ jobId: body.jobId, status: 'queued', total_items: body.total, processed_items: 0, success_count: 0, failure_count: 0 });
      showAlert.success('Restore masuk antrean', 'Progress dapat dipantau di panel antrean.');
    } catch (error) { showAlert.error('Restore gagal', error); }
    finally { setRestoreBusy(false); }
  }

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="ADMINISTRASI" title="Backup Data" description="Simpan salinan data LMS secara aman." />
      <div className="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
        <Card title="Jenis backup" subtitle="Pilih cakupan data yang ingin disimpan." action={<Archive className="size-5 text-primary" />}>
          <div className="space-y-2">
            <label className={`flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition ${mode === 'selected' ? 'border-primary bg-primary/5' : 'border-outline'}`}>
              <input type="radio" name="backup-mode" checked={mode === 'selected'} onChange={() => setMode('selected')} className="mt-1 accent-primary" />
              <span><strong className="block text-sm text-on-surface">Data pilihan</strong><span className="text-xs text-muted">Pilih tabel yang diperlukan.</span></span>
            </label>
            <label className={`flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition ${mode === 'all' ? 'border-primary bg-primary/5' : 'border-outline'}`}>
              <input type="radio" name="backup-mode" checked={mode === 'all'} onChange={() => setMode('all')} className="mt-1 accent-primary" />
              <span><strong className="block text-sm text-on-surface">Semua data</strong><span className="text-xs text-muted">Backup lengkap dari data LMS.</span></span>
            </label>
          </div>
          <div className="mt-5 rounded-xl bg-surface-low p-3 text-xs text-muted">
            <div className="flex items-start gap-2"><ShieldCheck className="mt-0.5 size-4 shrink-0 text-emerald-600" /><span>File hanya dibuat saat diminta dan tidak disimpan di server.</span></div>
          </div>
          <Button className="mt-5 w-full" loading={busy} disabled={busy} onClick={downloadBackup} icon={<Download className="size-4" />}>Unduh backup</Button>
          {lastBackup && <p className="mt-3 text-center text-xs text-muted">Terakhir: {lastBackup}</p>}
        </Card>
        <Card title="Data yang tersedia" subtitle={mode === 'all' ? 'Semua jenis data akan disertakan.' : `${selected.length} jenis dipilih.`} action={<Database className="size-5 text-primary" />}>
          <div className={`grid gap-2 sm:grid-cols-2 ${mode === 'all' ? 'pointer-events-none opacity-60' : ''}`}>
            {DATASETS.map(([key, label]) => <label key={key} className="flex items-center gap-2 rounded-lg border border-outline px-3 py-2.5 text-sm text-on-surface transition hover:border-primary">
              <input type="checkbox" checked={mode === 'all' || selected.includes(key)} onChange={() => toggle(key)} className="size-4 accent-primary" />{label}
            </label>)}
          </div>
          <p className="mt-4 text-xs text-muted">Format file: JSON. Simpan di lokasi yang aman dan batasi aksesnya.</p>
        </Card>
      </div>
      <Card title="Pulihkan backup" subtitle="Unggah file JSON backup untuk diproses melalui antrean." action={<Archive className="size-5 text-primary" />}>
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
          <label className="min-w-0 flex-1 text-sm font-semibold text-on-surface">
            File backup JSON
            <input type="file" accept="application/json,.json" onChange={event => setRestoreFile(event.target.files?.[0] || null)} disabled={restoreBusy} className="mt-1.5 block w-full rounded-lg border border-outline bg-surface-lowest p-2 text-xs text-on-surface file:mr-3 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary" />
          </label>
          <Button loading={restoreBusy} disabled={restoreBusy || !restoreFile} onClick={restoreBackup} icon={<Archive className="size-4" />}>Pulihkan data</Button>
        </div>
        {restoreJob && <div className="mt-4 rounded-xl border border-outline bg-surface-low p-3 text-xs text-muted">
          <div className="flex items-center justify-between gap-3"><span className="font-semibold text-on-surface">Status restore: {restoreJob.status}</span><span>{restoreJob.processed_items || 0}/{restoreJob.total_items || 0}</span></div>
          <div className="mt-2 h-2 overflow-hidden rounded-full bg-surface-container"><div className="h-full rounded-full bg-primary transition-all" style={{ width: `${Math.min(100, ((restoreJob.processed_items || 0) / Math.max(1, restoreJob.total_items || 1)) * 100)}%` }} /></div>
        </div>}
      </Card>
    </div>
  );
}
