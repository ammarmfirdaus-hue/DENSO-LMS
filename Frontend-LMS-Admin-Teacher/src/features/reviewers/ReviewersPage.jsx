import { useEffect, useMemo, useState } from 'react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import StatusBadge from '../../components/StatusBadge';
import Button from '../../components/Button';
import showAlert from '../../utils/alert';

export default function ReviewersPage() {
  const [reviewers, setReviewers] = useState([]);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [selectedReviewers, setSelectedReviewers] = useState(() => new Set());

  // Modal states
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editTarget, setEditTarget] = useState(null);
  const [emailTarget, setEmailTarget] = useState(null);

  async function load() {
    try {
      const result = await api('/reviewers');
      setReviewers(result.reviewers || []);
      setError('');
    } catch (e) {
      setError(e.message);
    }
  }

  useEffect(() => {
    load();
  }, []);

  useEffect(() => {
    window.addEventListener('dls_job_settled', load);
    return () => window.removeEventListener('dls_job_settled', load);
  }, []);

  const filteredReviewers = useMemo(() => {
    return reviewers.filter(r => {
      const matchStatus = statusFilter === 'all' || r.status === statusFilter;
      const term = `${r.fullname} ${r.employee_npk} ${r.email} ${r.department || ''} ${r.position || ''} ${r.phone_number || ''}`.toLowerCase();
      const matchSearch = term.includes(search.trim().toLowerCase());
      return matchStatus && matchSearch;
    });
  }, [reviewers, statusFilter, search]);

  const page = usePagination(filteredReviewers, 10);
  const eligibleReviewers = filteredReviewers.filter(reviewer => reviewer.status === 'active' && Number(reviewer.active_reviews) === 0);
  const selectedEligibleReviewers = eligibleReviewers.filter(reviewer => selectedReviewers.has(String(reviewer.employee_npk)));

  const summary = useMemo(() => ({
    total: reviewers.length,
    active: reviewers.filter(r => r.status === 'active').length,
    inactive: reviewers.filter(r => r.status === 'inactive').length,
    totalReviews: reviewers.reduce((sum, r) => sum + (Number(r.total_reviews) || 0), 0)
  }), [reviewers]);

  async function handleCreate(e) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const payload = {
      npk: formData.get('npk'),
      fullname: formData.get('fullname'),
      email: formData.get('email'),
      password: formData.get('password'),
      department: formData.get('department'),
      position: formData.get('position'),
      phoneNumber: formData.get('phoneNumber')
    };

    setBusy(true);
    setError('');
    setNotice('');

    try {
      const res = await api('/reviewers', {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      setNotice(res.message);
      showAlert.success('Reviewer Ditambahkan', res.message || 'Akun reviewer HRD/Manajemen berhasil didaftarkan.');
      setShowCreateModal(false);
      await load();
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menambah Reviewer', err.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleUpdate(e) {
    e.preventDefault();
    if (!editTarget) return;

    const formData = new FormData(e.currentTarget);
    const payload = {
      fullname: formData.get('fullname'),
      email: formData.get('email'),
      department: formData.get('department'),
      position: formData.get('position'),
      phoneNumber: formData.get('phoneNumber'),
      status: formData.get('status'),
      password: formData.get('password') || ''
    };

    setBusy(true);
    setError('');
    setNotice('');

    try {
      const res = await api(`/reviewers/${encodeURIComponent(editTarget.employee_npk)}`, {
        method: 'PUT',
        body: JSON.stringify(payload)
      });
      setNotice(res.message);
      showAlert.success('Perubahan Disimpan', res.message || 'Data akun reviewer berhasil diperbarui.');
      setEditTarget(null);
      await load();
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menyimpan Data Reviewer', err.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleDelete(reviewer) {
    const confirmed = await showAlert.confirm({
      title: 'Nonaktifkan Reviewer?',
      text: `Akun ${reviewer.fullname} tidak dapat digunakan lagi. Riwayat review tetap disimpan.`,
      confirmText: 'Ya, Nonaktifkan',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      const res = await api(`/reviewers/${encodeURIComponent(reviewer.employee_npk)}`, { method: 'DELETE' });
      setNotice(res.message || 'Akun reviewer dinonaktifkan.');
      await load();
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menghapus Reviewer', err.message);
    } finally { setBusy(false); }
  }

  async function handleHardDelete(reviewer) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Reviewer Permanen?',
      text: `Akun ${reviewer.fullname} akan dihapus jika tidak memiliki riwayat terkait. Jika ada riwayat, akun otomatis dinonaktifkan. Materi yang masih menunggu review tetap harus diselesaikan atau dialihkan terlebih dahulu.`,
      confirmText: 'Hapus / Nonaktifkan', cancelText: 'Batal', danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({ items: [{
          action: 'delete', npk: reviewer.employee_npk, role: 'reviewer',
          label: `${reviewer.fullname} (${reviewer.employee_npk})`
        }] })
      });
      showAlert.success('Penghapusan Masuk Antrean', `Penghapusan reviewer ${reviewer.fullname} diproses melalui antrean.`);
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menghapus Reviewer', err.message);
    } finally { setBusy(false); }
  }

  function toggleReviewerSelection(npk) {
    const key = String(npk);
    setSelectedReviewers(previous => {
      const next = new Set(previous);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  function toggleAllEligibleReviewers() {
    setSelectedReviewers(previous => {
      const next = new Set(previous);
      const allSelected = eligibleReviewers.length > 0 && eligibleReviewers.every(reviewer => next.has(String(reviewer.employee_npk)));
      eligibleReviewers.forEach(reviewer => allSelected
        ? next.delete(String(reviewer.employee_npk))
        : next.add(String(reviewer.employee_npk)));
      return next;
    });
  }

  async function handleBulkDelete() {
    if (!selectedEligibleReviewers.length) return;
    const count = selectedEligibleReviewers.length;
    const confirmed = await showAlert.confirm({
      title: `Hapus ${count} reviewer?`,
      text: 'Akun tanpa riwayat terkait akan dihapus permanen. Jika memiliki riwayat, akun otomatis dinonaktifkan agar catatan review tetap tersimpan. Reviewer dengan tugas review aktif tidak dapat dipilih.',
      confirmText: `Proses ${count} Reviewer`,
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError(''); setNotice('');
    try {
      const result = await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({ items: selectedEligibleReviewers.map(reviewer => ({
          action: 'delete', npk: reviewer.employee_npk, role: 'reviewer',
          label: `${reviewer.fullname} (${reviewer.employee_npk})`
        })) })
      });
      setSelectedReviewers(new Set());
      showAlert.success('Penghapusan Masuk Antrean', `${result.total || count} reviewer akan diproses satu per satu. Progress tetap tersimpan jika halaman dimuat ulang.`);
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Membuat Antrean Hapus', err.message);
    } finally { setBusy(false); }
  }

  async function handleReactivate(reviewer) {
    setBusy(true); setError(''); setNotice('');
    try {
      const res = await api(`/reviewers/${encodeURIComponent(reviewer.employee_npk)}`, {
        method: 'PUT',
        body: JSON.stringify({
          fullname: reviewer.fullname, email: reviewer.email,
          department: reviewer.department || '', position: reviewer.position || '',
          phoneNumber: reviewer.phone_number || '', status: 'active', password: ''
        })
      });
      setNotice(res.message || 'Akun reviewer diaktifkan kembali.');
      await load();
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Mengaktifkan Reviewer', err.message);
    } finally { setBusy(false); }
  }

  async function handleSendEmail(event) {
    event.preventDefault();
    if (!emailTarget) return;
    const password = new FormData(event.currentTarget).get('password');
    setBusy(true); setError(''); setNotice('');
    try {
      await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({ items: [{
          action: 'email', npk: emailTarget.employee_npk,
          fullname: emailTarget.fullname, email: emailTarget.email,
          role: 'reviewer', status: emailTarget.status,
          password,
          label: `${emailTarget.fullname} (${emailTarget.employee_npk})`
        }] })
      });
      setEmailTarget(null);
      showAlert.success('Email Masuk Antrean', `NPK dan kata sandi baru akan dikirim ke ${emailTarget.email}. Kata sandi akun reviewer akan diganti.`);
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Mengirim Email', err.message);
    } finally { setBusy(false); }
  }


  return (
    <div className="mx-auto max-w-7xl space-y-6">
      <PageHeader
        eyebrow="ADMINISTRASI & MANAJEMEN"
        title="Kelola Reviewer"
        description="Kelola data akun reviewer, penanggung jawab HRD, serta petinggi yang berwenang meninjau kelayakan materi sebelum diterbitkan ke student."
        actions={
          <Button
            type="button"
            icon="person_add"
            onClick={() => { setShowCreateModal(true); setError(''); setNotice(''); }}
          >
            Tambah Reviewer Baru
          </Button>
        }
      />

      {/* Summary Cards */}
      <section aria-label="Ringkasan Reviewer" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Total Reviewer"
          value={summary.total}
          icon="groups"
          tone="primary"
        />
        <StatCard
          label="Reviewer Aktif"
          value={summary.active}
          icon="verified_user"
          tone="emerald"
        />
        <StatCard
          label="Non-aktif"
          value={summary.inactive}
          icon="person_off"
          tone="amber"
        />
        <StatCard
          label="Total Materi Direview"
          value={summary.totalReviews}
          icon="task"
          tone="blue"
        />
      </section>

      {error && (
        <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">
          {error}
        </p>
      )}
      {notice && (
        <p role="status" className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
          {notice}
        </p>
      )}

      {/* Filter and List */}
      <section className="overflow-hidden rounded-2xl border border-outline/50 bg-white shadow-sm">
        <div className="flex flex-col gap-3 border-b border-outline/50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
          <div>
            <h2 className="text-lg font-bold">Daftar Akun Reviewer</h2>
            <p className="mt-1 text-sm text-muted">Data identitas HRD / Manajemen terkait persetujuan materi.</p>
          </div>
          <span className="w-fit rounded-full bg-surface-low px-3 py-1.5 text-xs font-bold text-secondary">
            {filteredReviewers.length} dari {reviewers.length} reviewer
          </span>
        </div>

        <div className="p-4 pb-1 sm:p-6 sm:pb-2">
          <ListFilterBar
            search={search}
            onSearch={setSearch}
            placeholder="Cari nama, NPK, email, jabatan, atau departemen..."
            filter={statusFilter}
            onFilter={setStatusFilter}
            label="Filter Status"
            options={[
              { value: 'all', label: 'Semua Status' },
              { value: 'active', label: 'Hanya Aktif' },
              { value: 'inactive', label: 'Hanya Non-aktif' }
            ]}
          />
          {selectedEligibleReviewers.length > 0 && <div className="mt-3 flex flex-col gap-3 rounded-xl border border-red-200 bg-red-50 p-3 sm:flex-row sm:items-center sm:justify-between dark:border-red-900 dark:bg-red-950/30">
            <p className="text-sm font-medium text-red-900 dark:text-red-200">{selectedEligibleReviewers.length} reviewer dipilih untuk dihapus.</p>
            <button type="button" disabled={busy} onClick={handleBulkDelete} className="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-red-700 bg-red-700 px-4 text-sm font-semibold text-white transition hover:bg-red-800 disabled:opacity-50 sm:w-fit">
              <span className="material-symbol" aria-hidden="true">delete_sweep</span>Hapus Terpilih ({selectedEligibleReviewers.length})
            </button>
          </div>}
        </div>

        {page.pagedItems.length ? (
          <div className="max-w-full overflow-x-auto overscroll-x-contain p-4 sm:p-6" role="region" aria-label="Tabel daftar reviewer" tabIndex={0}>
            <p className="mb-2 px-1 text-xs text-muted sm:hidden">Geser tabel ke samping untuk melihat semua kolom.</p>
            <table className="w-full min-w-[1120px] text-left text-sm">
              <thead className="border-b border-outline/40 bg-surface-low/50 text-xs font-bold uppercase text-secondary">
                <tr>
                  <th className="w-12 px-4 py-3"><input type="checkbox" aria-label="Pilih semua reviewer aktif tanpa tugas review pada hasil filter" disabled={!eligibleReviewers.length || busy} checked={eligibleReviewers.length > 0 && eligibleReviewers.every(reviewer => selectedReviewers.has(String(reviewer.employee_npk)))} onChange={toggleAllEligibleReviewers} className="size-4 accent-primary" /></th>
                  <th className="px-4 py-3">Reviewer / HRD</th>
                  <th className="px-4 py-3">Jabatan & Departemen</th>
                  <th className="px-4 py-3">Kontak</th>
                  <th className="px-4 py-3 text-center">Status</th>
                  <th className="px-4 py-3 text-center">Tugas Review</th>
                  <th className="px-4 py-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-outline/30">
                {page.pagedItems.map(reviewer => (
                  <tr key={reviewer.employee_npk} className="transition hover:bg-surface-low/40">
                    <td className="px-4 py-4"><input type="checkbox" aria-label={`Pilih ${reviewer.fullname}`} disabled={busy || reviewer.status !== 'active' || Number(reviewer.active_reviews) > 0} checked={selectedReviewers.has(String(reviewer.employee_npk)) && reviewer.status === 'active' && Number(reviewer.active_reviews) === 0} onChange={() => toggleReviewerSelection(reviewer.employee_npk)} className="size-4 accent-primary" /></td>
                    <td className="px-4 py-4">
                      <div className="flex items-center gap-3">
                        <div className="grid size-10 shrink-0 place-items-center rounded-xl bg-secondary-container/30 font-bold text-secondary">
                          {reviewer.fullname?.charAt(0)?.toUpperCase() || 'R'}
                        </div>
                        <div>
                          <p className="font-bold text-secondary">{reviewer.fullname}</p>
                          <p className="text-xs text-muted">NPK: {reviewer.employee_npk}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-4">
                      <p className="font-semibold text-secondary">{reviewer.position || '-'}</p>
                      <p className="text-xs text-muted">{reviewer.department || 'Departemen belum diisi'}</p>
                    </td>
                    <td className="px-4 py-4">
                      <p className="text-secondary">{reviewer.email}</p>
                      <p className="text-xs text-muted">{reviewer.phone_number || '-'}</p>
                    </td>
                    <td className="px-4 py-4 text-center">
                      <StatusBadge status={reviewer.status} />
                    </td>
                    <td className="px-4 py-4 text-center">
                      <span className="font-bold text-secondary">{reviewer.total_reviews || 0}</span>
                      {Number(reviewer.active_reviews) > 0 && (
                        <span className="ml-1.5 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                          {reviewer.active_reviews} aktif
                        </span>
                      )}
                    </td>
                    <td className="px-4 py-4 text-right"><div className="flex justify-end gap-2">
                      <button
                        type="button"
                        disabled={busy}
                        onClick={() => { setEditTarget(reviewer); setError(''); setNotice(''); }}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-outline/60 px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-surface-low hover:text-primary"
                      >
                        <span className="material-symbol text-base" aria-hidden="true">edit</span>
                        Edit
                      </button>
                      {reviewer.status === 'active' && <button type="button" disabled={busy || Number(reviewer.active_reviews) > 0} title={Number(reviewer.active_reviews) > 0 ? 'Selesaikan atau alihkan tugas review aktif terlebih dahulu.' : 'Nonaktifkan akses reviewer'} onClick={() => handleDelete(reviewer)} className="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"><span className="material-symbol" aria-hidden="true">person_off</span>Nonaktifkan</button>}
                      {reviewer.status !== 'active' && <button type="button" disabled={busy} onClick={() => handleReactivate(reviewer)} className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">check_circle</span>Aktifkan</button>}
                      {reviewer.status === 'active' && <button type="button" disabled={busy || !reviewer.email} onClick={() => setEmailTarget(reviewer)} className="inline-flex items-center gap-1.5 rounded-lg border border-outline/60 bg-white px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-surface-low disabled:opacity-50"><span className="material-symbol" aria-hidden="true">mail</span>Email</button>}
                      <button type="button" disabled={busy || Number(reviewer.active_reviews) > 0} title={Number(reviewer.active_reviews) > 0 ? 'Reviewer masih memiliki tugas review aktif.' : 'Hapus akun secara permanen jika tidak memiliki riwayat terkait.'} onClick={() => handleHardDelete(reviewer)} className="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"><span className="material-symbol" aria-hidden="true">delete</span>Hapus</button>
                    </div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="p-10 text-center sm:p-14">
            <span className="material-symbol mx-auto grid size-14 place-items-center rounded-2xl bg-surface-low text-3xl text-muted" aria-hidden="true">
              person_search
            </span>
            <h3 className="mt-4 font-bold">{reviewers.length ? 'Tidak ada reviewer yang cocok' : 'Belum ada akun reviewer'}</h3>
            <p className="mx-auto mt-1 max-w-md text-sm leading-6 text-muted">
              {reviewers.length ? 'Coba ubah kata kunci pencarian atau filter status.' : 'Tambahkan akun reviewer untuk memulai proses peninjauan materi.'}
            </p>
          </div>
        )}

        <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
      </section>

      {/* Modal Tambah Reviewer */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <div className="flex items-center justify-between border-b border-outline/40 pb-4">
              <div>
                <h3 className="text-lg font-bold">Tambah Reviewer Baru</h3>
                <p className="text-xs text-muted">Daftarkan akun reviewer (HRD / Petinggi perusahaan).</p>
              </div>
              <button
                type="button"
                onClick={() => setShowCreateModal(false)}
                aria-label="Tutup dialog"
                className="grid size-8 place-items-center rounded-lg text-muted hover:bg-surface-low"
              >
                <span className="material-symbol text-lg" aria-hidden="true">close</span>
              </button>
            </div>

            <form onSubmit={handleCreate} className="mt-4 space-y-3.5">
              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="block text-xs font-bold text-secondary">NPK *</label>
                  <input
                    name="npk"
                    required
                    maxLength="50"
                    placeholder="Contoh: DLS-REV-01"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-secondary">Nama Lengkap *</label>
                  <input
                    name="fullname"
                    required
                    maxLength="150"
                    placeholder="Contoh: Budi Hartono, S.Psi"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
              </div>

              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="block text-xs font-bold text-secondary">Email Kerja *</label>
                  <input
                    type="email"
                    name="email"
                    required
                    maxLength="254"
                    placeholder="reviewer@denso.co.id"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-secondary">Kata Sandi Awal *</label>
                  <input
                    type="password"
                    name="password"
                    required
                    minLength="12"
                    maxLength="200"
                    placeholder="Minimal 12 karakter"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
              </div>

              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="block text-xs font-bold text-secondary">Jabatan / Posisi</label>
                  <input
                    name="position"
                    maxLength="100"
                    placeholder="Contoh: HRD Manager / Section Head"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-secondary">Departemen / Divisi</label>
                  <input
                    name="department"
                    maxLength="100"
                    placeholder="Contoh: Human Resource & GA"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-secondary">Nomor Telepon / WhatsApp</label>
                <input
                  name="phoneNumber"
                  maxLength="30"
                  placeholder="Contoh: 081234567890"
                  className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                />
              </div>

              <div className="mt-5 flex justify-end gap-2 border-t border-outline/40 pt-4">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="rounded-lg border border-outline px-4 py-2 text-sm font-semibold text-secondary hover:bg-surface-low"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={busy}
                  className="rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white shadow-sm hover:brightness-110 disabled:opacity-60"
                >
                  {busy ? 'Menyimpan...' : 'Simpan Akun Reviewer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Edit Reviewer */}
      {editTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <div className="flex items-center justify-between border-b border-outline/40 pb-4">
              <div>
                <h3 className="text-lg font-bold">Edit Akun Reviewer</h3>
                <p className="text-xs text-muted">Perbarui data profil HRD / status reviewer ({editTarget.employee_npk}).</p>
              </div>
              <button
                type="button"
                onClick={() => setEditTarget(null)}
                aria-label="Tutup dialog"
                className="grid size-8 place-items-center rounded-lg text-muted hover:bg-surface-low"
              >
                <span className="material-symbol text-lg" aria-hidden="true">close</span>
              </button>
            </div>

            <form onSubmit={handleUpdate} className="mt-4 space-y-3.5">
              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="block text-xs font-bold text-secondary">NPK (Tetap)</label>
                  <input
                    disabled
                    value={editTarget.employee_npk}
                    className="mt-1 h-10 w-full rounded-lg border border-outline/40 bg-zinc-100 px-3 text-sm text-zinc-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-secondary">Nama Lengkap *</label>
                  <input
                    name="fullname"
                    required
                    defaultValue={editTarget.fullname}
                    maxLength="150"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
              </div>

              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="block text-xs font-bold text-secondary">Email Kerja *</label>
                  <input
                    type="email"
                    name="email"
                    required
                    defaultValue={editTarget.email}
                    maxLength="254"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-secondary">Status Akun</label>
                  <select
                    name="status"
                    defaultValue={editTarget.status}
                    className="mt-1 h-10 w-full rounded-lg border border-outline bg-white px-3 text-sm outline-none focus:border-primary"
                  >
                    <option value="active">Aktif (Dapat Ditugaskan)</option>
                    <option value="inactive">Non-aktif</option>
                  </select>
                </div>
              </div>

              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="block text-xs font-bold text-secondary">Jabatan / Posisi</label>
                  <input
                    name="position"
                    defaultValue={editTarget.position || ''}
                    maxLength="100"
                    placeholder="Contoh: HRD Manager"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-secondary">Departemen / Divisi</label>
                  <input
                    name="department"
                    defaultValue={editTarget.department || ''}
                    maxLength="100"
                    placeholder="Contoh: HRD"
                    className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-secondary">Nomor Telepon / WhatsApp</label>
                <input
                  name="phoneNumber"
                  defaultValue={editTarget.phone_number || ''}
                  maxLength="30"
                  placeholder="Contoh: 081234567890"
                  className="mt-1 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"
                />
              </div>

              <div className="rounded-xl border border-outline/50 bg-surface-low/50 p-3">
                <label className="block text-xs font-bold text-secondary">Ganti Kata Sandi (Opsional)</label>
                <input
                  type="password"
                  name="password"
                  minLength="12"
                  maxLength="200"
                  placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                  className="mt-1.5 h-10 w-full rounded-lg border border-outline bg-white px-3 text-sm outline-none focus:border-primary"
                />
                <p className="mt-1 text-[11px] text-muted">Minimal 12 karakter jika ingin mengganti kata sandi.</p>
              </div>

              <div className="mt-5 flex justify-end gap-2 border-t border-outline/40 pt-4">
                <button
                  type="button"
                  onClick={() => setEditTarget(null)}
                  className="rounded-lg border border-outline px-4 py-2 text-sm font-semibold text-secondary hover:bg-surface-low"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={busy}
                  className="rounded-lg bg-primary px-5 py-2 text-sm font-bold text-white shadow-sm hover:brightness-110 disabled:opacity-60"
                >
                  {busy ? 'Menyimpan...' : 'Perbarui Akun'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      {emailTarget && (
        <div className="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4">
          <form onSubmit={handleSendEmail} className="w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="reviewer-email-title">
            <div>
              <h2 id="reviewer-email-title" className="text-xl font-bold">Kirim Data Akun Reviewer</h2>
              <p className="mt-1 text-sm text-muted">{emailTarget.fullname} · NPK: {emailTarget.employee_npk}</p>
            </div>
            <p className="rounded-xl border border-outline/60 bg-surface-low p-4 text-sm leading-6 text-secondary">Email dikirim ke <strong>{emailTarget.email}</strong> beserta NPK dan kata sandi yang dapat langsung digunakan. Kata sandi lama tersimpan sebagai hash, jadi kata sandi baru di bawah ini akan menggantikannya.</p>
            <label className="block text-sm font-semibold text-secondary">Kata sandi baru<input required type="password" name="password" minLength="12" maxLength="200" autoComplete="new-password" placeholder="Minimal 12 karakter" className="mt-1.5 h-10 w-full rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary"/></label>
            <div className="flex justify-end gap-2">
              <button type="button" disabled={busy} onClick={() => setEmailTarget(null)} className="rounded-lg border border-outline px-4 py-2 text-sm font-semibold">Batal</button>
              <button type="submit" disabled={busy} className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{busy ? 'Memproses...' : 'Ganti & Kirim Email'}</button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
