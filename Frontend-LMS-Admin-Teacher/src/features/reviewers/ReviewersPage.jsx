import { useEffect, useMemo, useState } from 'react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

export default function ReviewersPage() {
  const [reviewers, setReviewers] = useState([]);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);

  // Modal states
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editTarget, setEditTarget] = useState(null);

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

  const filteredReviewers = useMemo(() => {
    return reviewers.filter(r => {
      const matchStatus = statusFilter === 'all' || r.status === statusFilter;
      const term = `${r.fullname} ${r.employee_npk} ${r.email} ${r.department || ''} ${r.position || ''} ${r.phone_number || ''}`.toLowerCase();
      const matchSearch = term.includes(search.trim().toLowerCase());
      return matchStatus && matchSearch;
    });
  }, [reviewers, statusFilter, search]);

  const page = usePagination(filteredReviewers, 10);

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


  return (
    <div className="mx-auto max-w-7xl space-y-6">
      <header className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-xs font-bold uppercase tracking-[.18em] text-primary">Administrasi & Manajemen</p>
          <h1 className="mt-1 text-3xl font-bold tracking-tight">Kelola Reviewer</h1>
          <p className="mt-1 max-w-2xl text-sm leading-6 text-muted">
            Kelola data akun reviewer, penanggung jawab HRD, serta petinggi yang berwenang meninjau kelayakan materi sebelum diterbitkan ke student.
          </p>
        </div>
        <button
          type="button"
          onClick={() => { setShowCreateModal(true); setError(''); setNotice(''); }}
          className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:brightness-110"
        >
          <span className="material-symbol" aria-hidden="true">person_add</span>
          Tambah Reviewer Baru
        </button>
      </header>

      {/* Summary Cards */}
      <section aria-label="Ringkasan Reviewer" className="grid gap-3 sm:grid-cols-4">
        <div className="flex items-center gap-3 rounded-2xl border border-outline/50 bg-white p-4 shadow-sm">
          <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-xl text-primary">
            <span className="material-symbol">groups</span>
          </span>
          <div className="min-w-0">
            <p className="text-2xl font-bold leading-none">{summary.total}</p>
            <p className="mt-1 text-xs text-muted">Total Reviewer</p>
          </div>
        </div>

        <div className="flex items-center gap-3 rounded-2xl border border-outline/50 bg-white p-4 shadow-sm">
          <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-xl text-emerald-700">
            <span className="material-symbol">verified_user</span>
          </span>
          <div className="min-w-0">
            <p className="text-2xl font-bold leading-none">{summary.active}</p>
            <p className="mt-1 text-xs text-muted">Reviewer Aktif</p>
          </div>
        </div>

        <div className="flex items-center gap-3 rounded-2xl border border-outline/50 bg-white p-4 shadow-sm">
          <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-xl text-amber-700">
            <span className="material-symbol">person_off</span>
          </span>
          <div className="min-w-0">
            <p className="text-2xl font-bold leading-none">{summary.inactive}</p>
            <p className="mt-1 text-xs text-muted">Non-aktif</p>
          </div>
        </div>

        <div className="flex items-center gap-3 rounded-2xl border border-outline/50 bg-white p-4 shadow-sm">
          <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-xl text-blue-700">
            <span className="material-symbol">task</span>
          </span>
          <div className="min-w-0">
            <p className="text-2xl font-bold leading-none">{summary.totalReviews}</p>
            <p className="mt-1 text-xs text-muted">Total Materi Direview</p>
          </div>
        </div>
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
        </div>

        {page.pagedItems.length ? (
          <div className="overflow-x-auto p-4 sm:p-6">
            <table className="w-full text-left text-sm">
              <thead className="border-b border-outline/40 bg-surface-low/50 text-xs font-bold uppercase text-secondary">
                <tr>
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
                      <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-bold ${
                        reviewer.status === 'active'
                          ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20'
                          : 'bg-zinc-100 text-zinc-600 ring-1 ring-inset ring-zinc-500/20'
                      }`}>
                        {reviewer.status === 'active' ? 'Aktif' : 'Non-aktif'}
                      </span>
                    </td>
                    <td className="px-4 py-4 text-center">
                      <span className="font-bold text-secondary">{reviewer.total_reviews || 0}</span>
                      {Number(reviewer.active_reviews) > 0 && (
                        <span className="ml-1.5 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                          {reviewer.active_reviews} aktif
                        </span>
                      )}
                    </td>
                    <td className="px-4 py-4 text-right">
                      <button
                        type="button"
                        onClick={() => { setEditTarget(reviewer); setError(''); setNotice(''); }}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-outline/60 px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-surface-low hover:text-primary"
                      >
                        <span className="material-symbol text-base" aria-hidden="true">edit</span>
                        Edit
                      </button>
                    </td>
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
                className="grid size-8 place-items-center rounded-lg text-muted hover:bg-surface-low"
              >
                ✕
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
                className="grid size-8 place-items-center rounded-lg text-muted hover:bg-surface-low"
              >
                ✕
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
    </div>
  );
}
