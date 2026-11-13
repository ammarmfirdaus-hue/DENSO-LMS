import { useEffect, useMemo, useState } from 'react';
import { useLocation } from 'react-router-dom';
import {
  CheckCircle2,
  ClipboardCheck,
  Edit3,
  Mail,
  ShieldCheck,
  Trash2,
  UserPlus,
  Users,
  UserX
} from 'lucide-react';
import { api } from '../../api/client';
import Button from '../../components/Button';
import Card from '../../components/Card';
import DataTable from '../../components/DataTable';
import ListFilterBar from '../../components/ListFilterBar';
import Modal from '../../components/Modal';
import PageHeader from '../../components/PageHeader';
import Pagination, { usePagination } from '../../components/Pagination';
import StatCard from '../../components/StatCard';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';

const FIELD_CLASS =
  'mt-1 h-10 w-full rounded-md border border-outline/80 bg-white px-3 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

const DISABLED_FIELD_CLASS =
  'mt-1 h-10 w-full cursor-not-allowed rounded-md border border-outline/50 bg-surface-low px-3 text-sm text-muted dark:border-slate-800 dark:bg-slate-800/70 dark:text-slate-400';

export default function ReviewersPage() {
  const [reviewers, setReviewers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useQueryParamState('status', 'all');
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [busy, setBusy] = useState(false);
  const [selectedReviewers, setSelectedReviewers] = useState(() => new Set());

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
      showAlert.error('Gagal Memuat Daftar Reviewer', e.message);
    } finally {
      setLoading(false);
    }
  }

  const location = useLocation();

  useEffect(() => {
    load();
  }, []);

  useEffect(() => {
    window.addEventListener('dls_job_settled', load);
    return () => window.removeEventListener('dls_job_settled', load);
  }, []);

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    if (params.get('action') === 'create' || location.hash === '#create-reviewer') {
      setShowCreateModal(true);
    }
  }, [location.search, location.hash]);

  const filteredReviewers = useMemo(() => {
    const keyword = search.trim().toLowerCase();
    return reviewers.filter((r) => {
      const matchStatus = statusFilter === 'all' || r.status === statusFilter;
      const term = `${r.fullname} ${r.employee_npk} ${r.email} ${r.department || ''} ${r.position || ''} ${r.phone_number || ''}`.toLowerCase();
      return matchStatus && (!keyword || term.includes(keyword));
    });
  }, [reviewers, statusFilter, search]);

  const page = usePagination(filteredReviewers, 10);
  const eligibleReviewers = useMemo(
    () => filteredReviewers.filter((reviewer) => reviewer.status === 'active' && Number(reviewer.active_reviews) === 0),
    [filteredReviewers]
  );
  const selectedEligibleReviewers = useMemo(
    () => eligibleReviewers.filter((reviewer) => selectedReviewers.has(String(reviewer.employee_npk))),
    [eligibleReviewers, selectedReviewers]
  );

  const summary = useMemo(
    () => ({
      total: reviewers.length,
      active: reviewers.filter((r) => r.status === 'active').length,
      inactive: reviewers.filter((r) => r.status === 'inactive').length,
      totalReviews: reviewers.reduce((sum, r) => sum + (Number(r.total_reviews) || 0), 0)
    }),
    [reviewers]
  );

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
      showAlert.success(
        'Reviewer Ditambahkan',
        res.message || 'Akun reviewer HRD/Manajemen berhasil didaftarkan.'
      );
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
      showAlert.success(
        'Perubahan Disimpan',
        res.message || 'Data akun reviewer berhasil diperbarui.'
      );
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

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/reviewers/${encodeURIComponent(reviewer.employee_npk)}`, {
        method: 'DELETE'
      });
      const msg = res.message || 'Akun reviewer dinonaktifkan.';
      setNotice(msg);
      showAlert.success('Reviewer Dinonaktifkan', msg);
      await load();
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menghapus Reviewer', err.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleHardDelete(reviewer) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Reviewer Permanen?',
      text: `Akun ${reviewer.fullname} akan dihapus jika tidak memiliki riwayat terkait. Jika ada riwayat, akun otomatis dinonaktifkan. Materi yang masih menunggu review tetap harus diselesaikan atau dialihkan terlebih dahulu.`,
      confirmText: 'Hapus / Nonaktifkan',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;

    setBusy(true);
    setError('');
    setNotice('');
    try {
      await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({
          items: [
            {
              action: 'delete',
              npk: reviewer.employee_npk,
              role: 'reviewer',
              label: `${reviewer.fullname} (${reviewer.employee_npk})`
            }
          ]
        })
      });
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `Penghapusan reviewer ${reviewer.fullname} diproses melalui antrean.`
      );
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Menghapus Reviewer', err.message);
    } finally {
      setBusy(false);
    }
  }

  function toggleReviewerSelection(npk) {
    const key = String(npk);
    setSelectedReviewers((previous) => {
      const next = new Set(previous);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  function toggleAllEligibleReviewers() {
    setSelectedReviewers((previous) => {
      const next = new Set(previous);
      const allSelected =
        eligibleReviewers.length > 0 &&
        eligibleReviewers.every((reviewer) => next.has(String(reviewer.employee_npk)));
      eligibleReviewers.forEach((reviewer) =>
        allSelected
          ? next.delete(String(reviewer.employee_npk))
          : next.add(String(reviewer.employee_npk))
      );
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

    setBusy(true);
    setError('');
    setNotice('');
    try {
      const result = await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({
          items: selectedEligibleReviewers.map((reviewer) => ({
            action: 'delete',
            npk: reviewer.employee_npk,
            role: 'reviewer',
            label: `${reviewer.fullname} (${reviewer.employee_npk})`
          }))
        })
      });
      setSelectedReviewers(new Set());
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `${result.total || count} reviewer akan diproses satu per satu. Progress tetap tersimpan jika halaman dimuat ulang.`
      );
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Membuat Antrean Hapus', err.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleReactivate(reviewer) {
    setBusy(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/reviewers/${encodeURIComponent(reviewer.employee_npk)}`, {
        method: 'PUT',
        body: JSON.stringify({
          fullname: reviewer.fullname,
          email: reviewer.email,
          department: reviewer.department || '',
          position: reviewer.position || '',
          phoneNumber: reviewer.phone_number || '',
          status: 'active',
          password: ''
        })
      });
      const msg = res.message || 'Akun reviewer diaktifkan kembali.';
      setNotice(msg);
      showAlert.success('Reviewer Diaktifkan', msg);
      await load();
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Mengaktifkan Reviewer', err.message);
    } finally {
      setBusy(false);
    }
  }

  async function handleSendEmail(event) {
    event.preventDefault();
    if (!emailTarget) return;
    const password = new FormData(event.currentTarget).get('password');

    setBusy(true);
    setError('');
    setNotice('');
    try {
      await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({
          items: [
            {
              action: 'email',
              npk: emailTarget.employee_npk,
              fullname: emailTarget.fullname,
              email: emailTarget.email,
              role: 'reviewer',
              status: emailTarget.status,
              password,
              label: `${emailTarget.fullname} (${emailTarget.employee_npk})`
            }
          ]
        })
      });
      setEmailTarget(null);
      showAlert.success(
        'Email Masuk Antrean',
        `NPK dan kata sandi baru akan dikirim ke ${emailTarget.email}. Kata sandi akun reviewer akan diganti.`
      );
    } catch (err) {
      setError(err.message);
      showAlert.error('Gagal Mengirim Email', err.message);
    } finally {
      setBusy(false);
    }
  }

  const allEligibleSelected =
    eligibleReviewers.length > 0 &&
    eligibleReviewers.every((reviewer) => selectedReviewers.has(String(reviewer.employee_npk)));

  const columns = [
    {
      key: 'select',
      className: 'w-11 px-4',
      label: (
        <input
          type="checkbox"
          aria-label="Pilih semua reviewer aktif tanpa tugas review pada hasil filter"
          disabled={!eligibleReviewers.length || busy}
          checked={allEligibleSelected}
          onChange={toggleAllEligibleReviewers}
          className="h-4 w-4 rounded border-outline accent-primary"
        />
      )
    },
    { key: 'reviewer', label: 'Reviewer / HRD' },
    { key: 'role', label: 'Jabatan & Departemen' },
    { key: 'contact', label: 'Kontak' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'reviews', label: 'Tugas Review', align: 'center' },
    { key: 'actions', label: 'Aksi', align: 'right' }
  ];

  return (
    <div className="mx-auto max-w-7xl space-y-6">
      <PageHeader
        eyebrow="Administrasi & Manajemen"
        title="Kelola Reviewer"
        description="Kelola data akun reviewer, penanggung jawab HRD, serta pimpinan yang berwenang meninjau kelayakan materi sebelum diterbitkan ke student."
        actions={
          <Button
            type="button"
            icon={<UserPlus className="h-4 w-4" />}
            onClick={() => {
              setShowCreateModal(true);
              setError('');
              setNotice('');
            }}
          >
            Tambah Reviewer Baru
          </Button>
        }
      />

      <section aria-label="Ringkasan Reviewer" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Total Reviewer"
          value={summary.total}
          icon={<Users className="h-5 w-5" />}
          tone="primary"
          subtext="Terdaftar sebagai HRD & manajemen"
          loading={loading}
        />
        <StatCard
          label="Reviewer Aktif"
          value={summary.active}
          icon={<ShieldCheck className="h-5 w-5" />}
          tone="tertiary"
          subtext="Siap menerima penugasan review"
          loading={loading}
        />
        <StatCard
          label="Non-aktif"
          value={summary.inactive}
          icon={<UserX className="h-5 w-5" />}
          tone="warning"
          subtext="Akses portal review dinonaktifkan"
          loading={loading}
        />
        <StatCard
          label="Total Materi Direview"
          value={summary.totalReviews}
          icon={<ClipboardCheck className="h-5 w-5" />}
          tone="secondary"
          subtext="Akumulasi tinjauan materi"
          loading={loading}
        />
      </section>

      {error && (
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/70 dark:bg-red-950/40 dark:text-red-200"
        >
          {error}
        </div>
      )}
      {notice && (
        <div
          role="status"
          className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-200"
        >
          {notice}
        </div>
      )}

      <Card
        id="reviewers-list"
        className="scroll-mt-20"
        title="Daftar Akun Reviewer"
        subtitle="Data identitas HRD / Manajemen terkait persetujuan materi."
        noPadding
        action={
          <span className="inline-flex items-center rounded-full border border-outline/40 bg-surface-low px-3 py-1 text-xs font-semibold tabular-nums text-secondary dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300">
            {filteredReviewers.length} dari {reviewers.length} reviewer
          </span>
        }
        footer={
          <div className="px-2">
            <Pagination
              {...page}
              onPageChange={page.setPage}
              onPageSizeChange={page.setPageSize}
            />
          </div>
        }
      >
        <div className="space-y-4 p-4 sm:p-6">
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

          {selectedEligibleReviewers.length > 0 && (
            <div className="flex flex-col gap-3 rounded-xl border border-red-200 bg-red-50/90 p-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-red-900/70 dark:bg-red-950/35">
              <p className="text-sm font-medium text-red-900 dark:text-red-200">
                {selectedEligibleReviewers.length} reviewer dipilih untuk diproses dalam antrean hapus.
              </p>
              <Button
                type="button"
                variant="danger"
                size="sm"
                icon={<Trash2 className="h-4 w-4" />}
                disabled={busy}
                onClick={handleBulkDelete}
                className="w-full sm:w-auto"
              >
                Hapus Terpilih ({selectedEligibleReviewers.length})
              </Button>
            </div>
          )}
        </div>

        <DataTable
          columns={columns}
          loading={loading}
          empty={!page.pagedItems.length}
          emptyMessage={
            reviewers.length
              ? 'Tidak ada reviewer yang cocok dengan kata kunci atau filter status saat ini.'
              : 'Belum ada akun reviewer. Tambahkan akun reviewer untuk memulai proses peninjauan materi.'
          }
          emptyAction={
            !reviewers.length ? (
              <Button
                type="button"
                size="sm"
                icon={<UserPlus className="h-4 w-4" />}
                onClick={() => setShowCreateModal(true)}
              >
                Tambah Reviewer Baru
              </Button>
            ) : null
          }
          ariaLabel="Tabel daftar reviewer"
          className="mx-4 mb-4 rounded-lg shadow-none sm:mx-6 sm:mb-5"
        >
          {page.pagedItems.map((reviewer) => {
            const hasActiveReviews = Number(reviewer.active_reviews) > 0;
            const isSelectable = reviewer.status === 'active' && !hasActiveReviews;
            const isChecked =
              selectedReviewers.has(String(reviewer.employee_npk)) && isSelectable;

            return (
              <tr
                key={reviewer.employee_npk}
                className="transition-colors hover:bg-surface-low/60 dark:hover:bg-slate-800/40"
              >
                <td className="px-4 py-3.5">
                  <input
                    type="checkbox"
                    aria-label={`Pilih ${reviewer.fullname}`}
                    disabled={busy || !isSelectable}
                    checked={isChecked}
                    onChange={() => toggleReviewerSelection(reviewer.employee_npk)}
                    className="h-4 w-4 rounded border-outline accent-primary disabled:opacity-40"
                  />
                </td>
                <td className="px-4 py-3.5">
                  <div className="flex items-center gap-3">
                    <div className="grid size-9 shrink-0 place-items-center rounded-lg bg-[#173e67]/10 text-sm font-bold text-[#173e67] dark:bg-sky-900/40 dark:text-sky-300">
                      {reviewer.fullname?.charAt(0)?.toUpperCase() || 'R'}
                    </div>
                    <div className="min-w-0">
                      <p className="font-semibold text-on-surface">{reviewer.fullname}</p>
                      <p className="font-mono text-xs text-muted">
                        NPK: {reviewer.employee_npk}
                      </p>
                    </div>
                  </div>
                </td>
                <td className="px-4 py-3.5">
                  <p className="font-medium text-on-surface">{reviewer.position || '-'}</p>
                  <p className="text-xs text-muted">
                    {reviewer.department || 'Departemen belum diisi'}
                  </p>
                </td>
                <td className="px-4 py-3.5">
                  <p className="text-on-surface">{reviewer.email}</p>
                  <p className="text-xs text-muted">{reviewer.phone_number || '-'}</p>
                </td>
                <td className="px-4 py-3.5 text-center">
                  <StatusBadge status={reviewer.status} dot />
                </td>
                <td className="px-4 py-3.5 text-center tabular-nums">
                  <span className="font-bold text-on-surface">
                    {reviewer.total_reviews || 0}
                  </span>
                  {hasActiveReviews && (
                    <span className="ml-1.5 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                      {reviewer.active_reviews} aktif
                    </span>
                  )}
                </td>
                <td className="px-4 py-3.5 text-right whitespace-nowrap">
                  <div className="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                    <Button
                      type="button"
                      variant="outline"
                      size="xs"
                      icon={<Edit3 className="h-3.5 w-3.5" />}
                      disabled={busy}
                      onClick={() => {
                        setEditTarget(reviewer);
                        setError('');
                        setNotice('');
                      }}
                    >
                      Edit
                    </Button>

                    {reviewer.status === 'active' ? (
                      <Button
                        type="button"
                        variant="outline"
                        size="xs"
                        icon={<UserX className="h-3.5 w-3.5" />}
                        disabled={busy || hasActiveReviews}
                        title={
                          hasActiveReviews
                            ? 'Selesaikan atau alihkan tugas review aktif terlebih dahulu.'
                            : 'Nonaktifkan akses reviewer'
                        }
                        onClick={() => handleDelete(reviewer)}
                        className="border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900/60 dark:text-red-300 dark:hover:bg-red-950/40"
                      >
                        Nonaktifkan
                      </Button>
                    ) : (
                      <Button
                        type="button"
                        variant="outline"
                        size="xs"
                        icon={<CheckCircle2 className="h-3.5 w-3.5" />}
                        disabled={busy}
                        onClick={() => handleReactivate(reviewer)}
                        className="border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-900/60 dark:text-emerald-300 dark:hover:bg-emerald-950/40"
                      >
                        Aktifkan
                      </Button>
                    )}

                    {reviewer.status === 'active' && (
                      <Button
                        type="button"
                        variant="outline"
                        size="xs"
                        icon={<Mail className="h-3.5 w-3.5" />}
                        disabled={busy || !reviewer.email}
                        onClick={() => setEmailTarget(reviewer)}
                      >
                        Email
                      </Button>
                    )}

                    <Button
                      type="button"
                      variant="outline"
                      size="xs"
                      icon={<Trash2 className="h-3.5 w-3.5" />}
                      disabled={busy || hasActiveReviews}
                      title={
                        hasActiveReviews
                          ? 'Reviewer masih memiliki tugas review aktif.'
                          : 'Hapus akun secara permanen jika tidak memiliki riwayat terkait.'
                      }
                      onClick={() => handleHardDelete(reviewer)}
                      className="border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900/60 dark:text-red-300 dark:hover:bg-red-950/40"
                    >
                      Hapus
                    </Button>
                  </div>
                </td>
              </tr>
            );
          })}
        </DataTable>
      </Card>

      {/* Modal Tambah Reviewer */}
      <Modal
        isOpen={showCreateModal}
        onClose={() => !busy && setShowCreateModal(false)}
        title="Tambah Reviewer Baru"
        subtitle="Daftarkan akun reviewer (HRD / Pimpinan perusahaan)."
        maxWidth="lg"
      >
        <form onSubmit={handleCreate} className="space-y-4">
          <div className="grid gap-3.5 sm:grid-cols-2">
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                NPK *
              </span>
              <input
                name="npk"
                required
                maxLength="50"
                placeholder="Contoh: DLS-REV-01"
                className={FIELD_CLASS}
              />
            </label>
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Nama Lengkap *
              </span>
              <input
                name="fullname"
                required
                maxLength="150"
                placeholder="Contoh: Budi Hartono, S.Psi"
                className={FIELD_CLASS}
              />
            </label>
          </div>

          <div className="grid gap-3.5 sm:grid-cols-2">
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Email Kerja *
              </span>
              <input
                type="email"
                name="email"
                required
                maxLength="254"
                placeholder="reviewer@denso.co.id"
                className={FIELD_CLASS}
              />
            </label>
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Kata Sandi Awal *
              </span>
              <input
                type="password"
                name="password"
                required
                minLength="12"
                maxLength="200"
                placeholder="Minimal 12 karakter"
                className={FIELD_CLASS}
              />
            </label>
          </div>

          <div className="grid gap-3.5 sm:grid-cols-2">
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Jabatan / Posisi
              </span>
              <input
                name="position"
                maxLength="100"
                placeholder="Contoh: HRD Manager / Section Head"
                className={FIELD_CLASS}
              />
            </label>
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Departemen / Divisi
              </span>
              <input
                name="department"
                maxLength="100"
                placeholder="Contoh: Human Resource & GA"
                className={FIELD_CLASS}
              />
            </label>
          </div>

          <label className="block">
            <span className="text-xs font-semibold text-secondary dark:text-slate-300">
              Nomor Telepon / WhatsApp
            </span>
            <input
              name="phoneNumber"
              maxLength="30"
              placeholder="Contoh: 081234567890"
              className={FIELD_CLASS}
            />
          </label>

          <div className="flex justify-end gap-2 border-t border-outline/40 pt-4 dark:border-slate-700/70">
            <Button
              type="button"
              variant="outline"
              disabled={busy}
              onClick={() => setShowCreateModal(false)}
            >
              Batal
            </Button>
            <Button type="submit" loading={busy} disabled={busy}>
              {busy ? 'Menyimpan...' : 'Simpan Akun Reviewer'}
            </Button>
          </div>
        </form>
      </Modal>

      {/* Modal Edit Reviewer */}
      <Modal
        isOpen={Boolean(editTarget)}
        onClose={() => !busy && setEditTarget(null)}
        title="Edit Akun Reviewer"
        subtitle={
          editTarget
            ? `Perbarui data profil HRD / status reviewer (${editTarget.employee_npk}).`
            : ''
        }
        maxWidth="lg"
      >
        {editTarget && (
          <form key={editTarget.employee_npk} onSubmit={handleUpdate} className="space-y-4">
            <div className="grid gap-3.5 sm:grid-cols-2">
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  NPK (Tetap)
                </span>
                <input disabled value={editTarget.employee_npk} className={DISABLED_FIELD_CLASS} />
              </label>
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  Nama Lengkap *
                </span>
                <input
                  name="fullname"
                  required
                  defaultValue={editTarget.fullname}
                  maxLength="150"
                  className={FIELD_CLASS}
                />
              </label>
            </div>

            <div className="grid gap-3.5 sm:grid-cols-2">
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  Email Kerja *
                </span>
                <input
                  type="email"
                  name="email"
                  required
                  defaultValue={editTarget.email}
                  maxLength="254"
                  className={FIELD_CLASS}
                />
              </label>
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  Status Akun
                </span>
                <select
                  name="status"
                  defaultValue={editTarget.status}
                  className={FIELD_CLASS}
                >
                  <option value="active">Aktif (Dapat Ditugaskan)</option>
                  <option value="inactive">Non-aktif</option>
                </select>
              </label>
            </div>

            <div className="grid gap-3.5 sm:grid-cols-2">
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  Jabatan / Posisi
                </span>
                <input
                  name="position"
                  defaultValue={editTarget.position || ''}
                  maxLength="100"
                  placeholder="Contoh: HRD Manager"
                  className={FIELD_CLASS}
                />
              </label>
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  Departemen / Divisi
                </span>
                <input
                  name="department"
                  defaultValue={editTarget.department || ''}
                  maxLength="100"
                  placeholder="Contoh: HRD"
                  className={FIELD_CLASS}
                />
              </label>
            </div>

            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Nomor Telepon / WhatsApp
              </span>
              <input
                name="phoneNumber"
                defaultValue={editTarget.phone_number || ''}
                maxLength="30"
                placeholder="Contoh: 081234567890"
                className={FIELD_CLASS}
              />
            </label>

            <div className="rounded-xl border border-outline/60 bg-surface-low/60 p-3.5 dark:border-slate-700/70 dark:bg-slate-800/40">
              <label className="block">
                <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                  Ganti Kata Sandi (Opsional)
                </span>
                <input
                  type="password"
                  name="password"
                  minLength="12"
                  maxLength="200"
                  placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                  className={FIELD_CLASS}
                />
              </label>
              <p className="mt-1.5 text-[11px] text-muted">
                Minimal 12 karakter jika ingin mengganti kata sandi.
              </p>
            </div>

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-4 dark:border-slate-700/70">
              <Button
                type="button"
                variant="outline"
                disabled={busy}
                onClick={() => setEditTarget(null)}
              >
                Batal
              </Button>
              <Button type="submit" loading={busy} disabled={busy}>
                {busy ? 'Menyimpan...' : 'Perbarui Akun'}
              </Button>
            </div>
          </form>
        )}
      </Modal>

      {/* Modal Kirim Kredensial Email */}
      <Modal
        isOpen={Boolean(emailTarget)}
        onClose={() => !busy && setEmailTarget(null)}
        title="Kirim Data Akun Reviewer"
        subtitle={emailTarget ? `${emailTarget.fullname} · NPK: ${emailTarget.employee_npk}` : ''}
        maxWidth="md"
      >
        {emailTarget && (
          <form key={emailTarget.employee_npk} onSubmit={handleSendEmail} className="space-y-4">
            <p className="rounded-xl border border-outline/60 bg-surface-low/70 p-4 text-sm leading-6 text-secondary dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300">
              Email dikirim ke <strong className="text-on-surface">{emailTarget.email}</strong> beserta NPK dan kata sandi yang dapat langsung digunakan. Kata sandi lama tersimpan sebagai hash, jadi kata sandi baru di bawah ini akan menggantikannya.
            </p>
            <label className="block">
              <span className="text-xs font-semibold text-secondary dark:text-slate-300">
                Kata sandi baru *
              </span>
              <input
                required
                type="password"
                name="password"
                minLength="12"
                maxLength="200"
                autoComplete="new-password"
                placeholder="Minimal 12 karakter"
                className={FIELD_CLASS}
              />
            </label>
            <div className="flex justify-end gap-2 border-t border-outline/40 pt-4 dark:border-slate-700/70">
              <Button
                type="button"
                variant="outline"
                disabled={busy}
                onClick={() => setEmailTarget(null)}
              >
                Batal
              </Button>
              <Button type="submit" loading={busy} disabled={busy}>
                {busy ? 'Memproses...' : 'Ganti & Kirim Email'}
              </Button>
            </div>
          </form>
        )}
      </Modal>
    </div>
  );
}
