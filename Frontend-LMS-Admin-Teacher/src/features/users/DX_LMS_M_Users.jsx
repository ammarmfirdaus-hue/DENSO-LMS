import { useEffect, useMemo, useRef, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import {
  CheckCircle2,
  Download,
  FileCheck2,
  GraduationCap,
  Mail,
  Pencil,
  Plus,
  Presentation,
  ShieldCheck,
  Table,
  Trash2,
  Upload,
  UserCog,
  UserMinus,
  UserX,
  Users
} from 'lucide-react';
import { api } from '../../api/client';
import { hasPermission } from '../../utils/access-control';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import Button from '../../components/Button';
import DataTable from '../../components/DataTable';
import Modal from '../../components/Modal';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';

const FIELD_CLASS =
  'h-10 rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';
const IMPORT_COLUMNS = ['npk', 'fullname', 'email', 'role', 'status', 'password'];

function csvCell(value) {
  const text = String(value ?? '');
  return /[",\r\n]/.test(text) ? `"${text.replaceAll('"', '""')}"` : text;
}

function parseCsv(text) {
  const rows = [];
  let row = [];
  let cell = '';
  let quoted = false;
  for (let i = 0; i < text.length; i += 1) {
    const char = text[i];
    if (quoted && char === '"' && text[i + 1] === '"') {
      cell += '"';
      i += 1;
    } else if (char === '"') {
      quoted = !quoted;
    } else if (!quoted && char === ',') {
      row.push(cell);
      cell = '';
    } else if (!quoted && (char === '\n' || char === '\r')) {
      if (char === '\r' && text[i + 1] === '\n') i += 1;
      row.push(cell);
      if (row.some((value) => value.trim())) rows.push(row);
      row = [];
      cell = '';
    } else {
      cell += char;
    }
  }
  row.push(cell);
  if (row.some((value) => value.trim())) rows.push(row);
  if (quoted) throw new Error('Format CSV tidak valid: tanda kutip belum ditutup.');
  if (!rows.length) throw new Error('File CSV belum memiliki data.');
  const headers = rows.shift().map((value) => value.replace(/^\uFEFF/, '').trim().toLowerCase());
  const missing = IMPORT_COLUMNS.filter((column) => !headers.includes(column));
  if (missing.length) throw new Error(`Kolom wajib belum ada: ${missing.join(', ')}.`);
  return rows.map((values, index) => ({
    ...Object.fromEntries(
      headers.map((header, column) => [header, (values[column] || '').trim()])
    ),
    _row: index + 2
  }));
}

function downloadCsv(filename, rows) {
  const csv = `\uFEFF${rows.map((row) => row.map(csvCell).join(',')).join('\r\n')}`;
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
}

/**
 * Halaman Manajemen Pengguna Portal untuk peran Admin dan Teacher.
 */
export default function UsersPage() {
  const currentUser = useOutletContext();
  const isAdmin = currentUser?.role === 'admin';
  const canManageUserAccounts = hasPermission(currentUser, 'users.manage');
  const [users, setUsers] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [sendImportEmail, setSendImportEmail] = useState(false);
  const [selectedUsers, setSelectedUsers] = useState(() => new Set());
  const importInput = useRef(null);
  const [search, setSearch] = useState('');
  const [role, setRole] = useQueryParamState('role', 'all');
  const [editing, setEditing] = useState(null);
  const [emailTarget, setEmailTarget] = useState(null);

  const filteredUsers = useMemo(
    () =>
      users.filter(
        (user) =>
          (role === 'all' || user.role === role) &&
          `${user.fullname} ${user.employee_npk} ${user.email} ${user.role}`
            .toLowerCase()
            .includes(search.trim().toLowerCase())
      ),
    [users, role, search]
  );

  const summary = useMemo(
    () => ({
      total: users.length,
      active: users.filter((user) => user.status === 'active').length,
      inactive: users.filter((user) => user.status !== 'active').length,
      students: users.filter((user) => user.role === 'student').length,
      teachers: users.filter((user) => user.role === 'teacher').length,
      admins: users.filter((user) => user.role === 'admin').length,
      reviewers: users.filter((user) => user.role === 'reviewer').length
    }),
    [users]
  );

  const userPage = usePagination(filteredUsers);
  const activeFilteredUsers = filteredUsers.filter((user) => user.status === 'active');
  const selectedActiveUsers = activeFilteredUsers.filter((user) =>
    selectedUsers.has(String(user.employee_npk))
  );

  async function load() {
    setLoading(true);
    try {
      setUsers((await api('/users')).users || []);
      setError('');
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memuat Data Pengguna', e.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  useEffect(() => {
    const refreshAfterQueue = () => load();
    window.addEventListener('dls_job_settled', refreshAfterQueue);
    return () => window.removeEventListener('dls_job_settled', refreshAfterQueue);
  }, []);

  async function create(event) {
    event.preventDefault();
    const formElement = event.currentTarget;
    const data = Object.fromEntries(new FormData(formElement));
    data.sendEmail = data.sendEmail === 'on';
    setBusy(true);
    setError('');
    try {
      data.sendEmail
        ? await api('/jobs/users', {
            method: 'POST',
            body: JSON.stringify({
              items: [
                {
                  ...data,
                  action: 'upsert',
                  status: 'active',
                  label: `${data.fullname} (${data.npk})`
                }
              ]
            })
          })
        : await api('/users', { method: 'POST', body: JSON.stringify(data) });
      formElement.reset();
      await load();
      if (data.sendEmail) {
        showAlert.success(
          'Pembuatan Akun Masuk Antrean',
          `Akun ${data.npk} dan email data akun akan diproses berurutan.`
        );
      } else {
        showAlert.success(
          'Pengguna Berhasil Ditambahkan',
          `Akun untuk ${data.fullname} (${data.role.toUpperCase()}) telah berhasil dibuat.`
        );
      }
    } catch (e) {
      setError(e.message);
      showAlert.error(
        'Gagal Menambahkan Pengguna',
        e.message || 'Terjadi kesalahan saat membuat pengguna.'
      );
    } finally {
      setBusy(false);
    }
  }

  async function update(event) {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget));
    data.sendEmail = data.sendEmail === 'on';
    const isStudentPromotion = editing.role === 'student' && data.role === 'teacher';
    if (isStudentPromotion) {
      const confirmed = await showAlert.confirm({
        title: 'Promosikan Student menjadi Teacher?',
        text: `Akun ${editing.fullname} akan memakai role Teacher. Riwayat course tetap tersimpan dan bisa digunakan melalui Mode Belajar.`,
        confirmText: 'Promosikan ke Teacher',
        cancelText: 'Batal',
        icon: 'question'
      });
      if (!confirmed) return;
    }
    if (data.sendEmail && data.password.length < 12) {
      showAlert.error(
        'Kata Sandi Baru Diperlukan',
        'Isi kata sandi baru minimal 12 karakter agar email berisi kata sandi yang dapat langsung digunakan.'
      );
      return;
    }

    setBusy(true);
    setError('');
    try {
      const result =
        data.sendEmail && !isStudentPromotion
          ? await api('/jobs/users', {
              method: 'POST',
              body: JSON.stringify({
                items: [
                  {
                    ...data,
                    action: 'upsert',
                    npk: editing.employee_npk,
                    label: `${data.fullname} (${editing.employee_npk})`
                  }
                ]
              })
            })
          : await api(`/users/${encodeURIComponent(editing.employee_npk)}`, {
              method: 'PUT',
              body: JSON.stringify(data)
            });
      setEditing(null);
      await load();
      if (data.sendEmail && !isStudentPromotion) {
        showAlert.success(
          'Perubahan dan Email Masuk Antrean',
          `Perubahan akun ${editing.employee_npk} dan email kredensial akan diproses berurutan.`
        );
      } else if (isStudentPromotion) {
        const emailMessage = data.sendEmail
          ? result.email?.sent
            ? ' Email NPK dan kata sandi baru juga berhasil dikirim.'
            : ` Email belum terkirim: ${result.email?.reason || 'periksa konfigurasi SMTP.'}`
          : '';
        showAlert.success(
          'Student Dipromosikan',
          `Akun ${editing.employee_npk} sekarang ber-role Teacher. Riwayat belajarnya tetap tersimpan.${emailMessage}`
        );
      } else {
        showAlert.success('Perubahan Disimpan', `Akun ${data.fullname} berhasil diperbarui.`);
      }
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Memperbarui Pengguna', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function sendAccountEmail(event) {
    event.preventDefault();
    if (!emailTarget) return;
    const password = new FormData(event.currentTarget).get('password');
    setBusy(true);
    setError('');
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
              role: emailTarget.role,
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
        `NPK dan kata sandi baru akan dikirim ke ${emailTarget.email}. Kata sandi akun akan diganti.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengirim Email', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function deactivateUser(user) {
    const confirmed = await showAlert.confirm({
      title: 'Nonaktifkan Pengguna?',
      text: `Akun ${user.fullname} tidak dapat login. Data dan seluruh riwayatnya tetap disimpan.`,
      confirmText: 'Ya, Nonaktifkan',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true);
    setError('');
    try {
      await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({
          items: [
            {
              action: 'deactivate',
              npk: user.employee_npk,
              label: `${user.fullname} (${user.employee_npk})`,
              role: user.role
            }
          ]
        })
      });
      showAlert.success(
        'Penonaktifan Masuk Antrean',
        `Akun ${user.fullname} akan diproses dan statusnya tampil secara realtime.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Pengguna', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function deleteUser(user) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Pengguna Permanen?',
      text: `Akun ${user.fullname} akan dihapus jika tidak memiliki riwayat terkait. Jika ada riwayat, akun otomatis dinonaktifkan agar seluruh riwayat tetap tersimpan.`,
      confirmText: 'Hapus / Nonaktifkan',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true);
    setError('');
    try {
      await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({
          items: [
            {
              action: 'delete',
              npk: user.employee_npk,
              label: `${user.fullname} (${user.employee_npk})`,
              role: user.role
            }
          ]
        })
      });
      setSelectedUsers((previous) => {
        const next = new Set(previous);
        next.delete(String(user.employee_npk));
        return next;
      });
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `Penghapusan akun ${user.fullname} diproses; progresnya dapat dipantau di antrean.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menghapus Pengguna', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function reactivateUser(user) {
    const confirmed = await showAlert.confirm({
      title: 'Aktifkan Kembali Pengguna?',
      text: `Akun ${user.fullname} akan dapat login kembali menggunakan NPK dan kata sandi sebelumnya.`,
      confirmText: 'Ya, Aktifkan',
      cancelText: 'Batal',
      icon: 'question'
    });
    if (!confirmed) return;
    setBusy(true);
    setError('');
    try {
      await api(`/users/${encodeURIComponent(user.employee_npk)}`, {
        method: 'PUT',
        body: JSON.stringify({
          fullname: user.fullname,
          email: user.email,
          role: user.role,
          status: 'active',
          password: ''
        })
      });
      setSelectedUsers((previous) => {
        const next = new Set(previous);
        next.delete(String(user.employee_npk));
        return next;
      });
      await load();
      showAlert.success('Pengguna Diaktifkan', `Akun ${user.fullname} sekarang aktif kembali.`);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengaktifkan Pengguna', e.message);
    } finally {
      setBusy(false);
    }
  }

  function toggleUserSelection(npk) {
    setSelectedUsers((previous) => {
      const next = new Set(previous);
      if (next.has(String(npk))) next.delete(String(npk));
      else next.add(String(npk));
      return next;
    });
  }

  function toggleAllFilteredUsers() {
    setSelectedUsers((previous) => {
      const next = new Set(previous);
      const allSelected =
        activeFilteredUsers.length > 0 &&
        activeFilteredUsers.every((user) => next.has(String(user.employee_npk)));
      activeFilteredUsers.forEach((user) =>
        allSelected
          ? next.delete(String(user.employee_npk))
          : next.add(String(user.employee_npk))
      );
      return next;
    });
  }

  async function removeSelectedUsers() {
    if (!selectedActiveUsers.length) return;
    const confirmed = await showAlert.confirm({
      title: `Hapus ${selectedActiveUsers.length} pengguna?`,
      text: 'Akun tanpa riwayat terkait akan dihapus permanen. Akun yang memiliki riwayat otomatis dinonaktifkan agar riwayat tetap tersimpan.',
      confirmText: `Proses ${selectedActiveUsers.length} Akun`,
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true);
    setError('');
    try {
      const result = await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({
          items: selectedActiveUsers.map((user) => ({
            action: 'delete',
            npk: user.employee_npk,
            label: `${user.fullname} (${user.employee_npk})`,
            role: user.role
          }))
        })
      });
      setSelectedUsers(new Set());
      showAlert.success(
        'Penghapusan Masuk Antrean',
        `${result.total} akun akan diproses satu per satu. Progress tetap tersimpan jika halaman dimuat ulang.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Membuat Antrean Hapus', e.message);
    } finally {
      setBusy(false);
    }
  }

  async function deactivateSelectedUsers() {
    if (!selectedActiveUsers.length) return;
    const confirmed = await showAlert.confirm({
      title: `Nonaktifkan ${selectedActiveUsers.length} pengguna?`,
      text: 'Akun tidak dapat login kembali sampai diaktifkan. Riwayat tetap tersimpan.',
      confirmText: 'Nonaktifkan Terpilih',
      cancelText: 'Batal',
      danger: true
    });
    if (!confirmed) return;
    setBusy(true);
    setError('');
    try {
      const items = selectedActiveUsers.map((user) => ({
        action: 'deactivate',
        npk: user.employee_npk,
        role: user.role,
        label: `${user.fullname} (${user.employee_npk})`
      }));
      await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items }) });
      setSelectedUsers(new Set());
      showAlert.success(
        'Penonaktifan Masuk Antrean',
        `${items.length} akun akan diproses secara berurutan.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menonaktifkan Pengguna', e.message);
    } finally {
      setBusy(false);
    }
  }

  function exportUsers() {
    const columns = [
      'npk',
      'fullname',
      'email',
      'role',
      'status',
      'department',
      'position',
      'phone_number',
      'created_at',
      'password'
    ];
    const rows = [
      columns,
      ...filteredUsers.map((user) =>
        columns.map((column) =>
          column === 'npk' ? user.employee_npk : column === 'password' ? '' : user[column] ?? ''
        )
      )
    ];
    downloadCsv('kelola-pengguna.csv', rows);
  }

  function downloadImportTemplate() {
    downloadCsv('template-impor-pengguna.csv', [IMPORT_COLUMNS]);
  }

  async function importUsers(event) {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;
    setBusy(true);
    setError('');
    try {
      if (!file.name.toLowerCase().endsWith('.csv')) {
        throw new Error('Gunakan file CSV. Unduh template CSV dari halaman ini.');
      }
      const rows = parseCsv(await file.text());
      const seen = new Set();
      const existingByNpk = new Map(
        users.map((user) => [String(user.employee_npk).toLowerCase(), user])
      );
      for (const row of rows) {
        row.npk = row.npk.trim();
        row.fullname = row.fullname.trim();
        row.email = row.email.trim();
        row.role = row.role.toLowerCase() || 'student';
        row.status = row.status.toLowerCase() || 'active';
        const key = row.npk.toLowerCase();
        if (!row.npk || !row.fullname || !row.email) {
          throw new Error(`Baris ${row._row}: NPK, fullname, dan email wajib diisi.`);
        }
        if (seen.has(key)) {
          throw new Error(`Baris ${row._row}: NPK ${row.npk} muncul lebih dari sekali.`);
        }
        seen.add(key);
        if (!['student', 'teacher', 'admin', 'reviewer'].includes(row.role)) {
          throw new Error(`Baris ${row._row}: role harus student, teacher, admin, atau reviewer.`);
        }
        if (!['active', 'inactive'].includes(row.status)) {
          throw new Error(`Baris ${row._row}: status harus active atau inactive.`);
        }
        const current = existingByNpk.get(key);
        if (current && (current.role === 'student') !== (row.role === 'student')) {
          throw new Error(
            `Baris ${row._row}: akun tidak dapat dipindahkan antara database Student dan pegawai.`
          );
        }
        if (!current && row.password.length < 12) {
          throw new Error(`Baris ${row._row}: akun baru wajib memiliki password minimal 12 karakter.`);
        }
        if (sendImportEmail && (!row.password || row.status !== 'active')) {
          throw new Error(
            `Baris ${row._row}: email akun memerlukan password baru dan status active.`
          );
        }
      }
      const confirmed = await showAlert.confirm({
        title: 'Impor Data Pengguna?',
        text: `${rows.length} baris akan diproses. NPK yang sudah ada akan diperbarui; akun baru akan dibuat.`,
        confirmText: 'Ya, Impor',
        cancelText: 'Batal',
        icon: 'question'
      });
      if (!confirmed) return;
      const items = rows.map((row) => ({
        action: 'upsert',
        npk: row.npk,
        label: `${row.fullname} (${row.npk})`,
        fullname: row.fullname,
        email: row.email,
        role: row.role,
        status: row.status,
        password: row.password,
        sendEmail: sendImportEmail
      }));
      const result = await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items }) });
      showAlert.success(
        'Impor Masuk Antrean',
        `${result.total} baris akan diproses berurutan. ${sendImportEmail ? 'Email dikirim sesuai antrean.' : ''} Progress tersedia setelah halaman dimuat ulang.`
      );
    } catch (importError) {
      setError(importError.message);
      showAlert.error('Gagal Mengimpor Pengguna', importError.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Administrasi Akses"
        title="Manajemen Pengguna"
        description={
          isAdmin
            ? 'Kelola akun Student, Teacher, Admin, dan Reviewer secara terpusat.'
            : 'Kelola akun Student: perbarui data, aktifkan kembali, atau nonaktifkan akses.'
        }
        actions={
          <span className="inline-flex items-center gap-2 rounded-lg border border-outline/60 bg-surface-lowest px-3.5 py-2 text-xs font-semibold text-on-surface-variant dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <Users className="size-3.5 text-primary" />
            {summary.total} total akun terdaftar
          </span>
        }
      />

      {error && (
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          {error}
        </div>
      )}

      {/* Ringkasan Statistik Pengguna */}
      <section
        aria-label="Ringkasan pengguna"
        className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
      >
        <StatCard
          label="Total Akun"
          value={summary.total}
          icon={<Users className="size-5" />}
          tone="primary"
          loading={loading}
        />
        <StatCard
          label="Aktif"
          value={summary.active}
          icon={<ShieldCheck className="size-5" />}
          tone="tertiary"
          loading={loading}
        />
        <StatCard
          label="Nonaktif"
          value={summary.inactive}
          icon={<UserX className="size-5" />}
          tone="warning"
          loading={loading}
        />
        <StatCard
          label="Student"
          value={summary.students}
          icon={<GraduationCap className="size-5" />}
          tone="info"
          loading={loading}
        />
        <StatCard
          label="Teacher"
          value={summary.teachers}
          icon={<Presentation className="size-5" />}
          tone="tertiary"
          loading={loading}
        />
        <StatCard
          label="Admin"
          value={summary.admins}
          icon={<UserCog className="size-5" />}
          tone="primary"
          loading={loading}
        />
        <StatCard
          label="Reviewer"
          value={summary.reviewers}
          icon={<FileCheck2 className="size-5" />}
          tone="secondary"
          loading={loading}
        />
      </section>

      {/* Form Tambah Pengguna (Admin) */}
      {isAdmin && (
        <div id="add-user" className="scroll-mt-20">
          <Card
            title="Tambah Pengguna Baru"
            subtitle="Akun Student tersimpan ke database Student secara otomatis. Pastikan SMTP siap bila mengirim kredensial email."
          >
            <form onSubmit={create} className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              <input required name="npk" maxLength="50" placeholder="NPK" className={FIELD_CLASS} />
              <input
                required
                name="fullname"
                maxLength="150"
                placeholder="Nama lengkap"
                className={FIELD_CLASS}
              />
              <input
                required
                type="email"
                name="email"
                maxLength="254"
                placeholder="Email kerja"
                className={FIELD_CLASS}
              />
              <input
                required
                type="password"
                name="password"
                minLength="12"
                maxLength="200"
                placeholder="Kata sandi awal (min. 12 karakter)"
                className={FIELD_CLASS}
              />
              <select name="role" className={FIELD_CLASS}>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
                <option value="reviewer">Reviewer</option>
              </select>
              <Button
                type="submit"
                variant="primary"
                disabled={busy}
                loading={busy}
                icon={<Plus className="size-4" />}
              >
                {busy ? 'Menyimpan...' : 'Tambah Pengguna'}
              </Button>

              <label className="flex cursor-pointer items-center gap-2 text-xs font-medium text-on-surface-variant md:col-span-2 xl:col-span-3">
                <input type="checkbox" name="sendEmail" className="size-4 rounded accent-primary" />
                Kirim NPK dan kata sandi awal ke email pengguna
              </label>
            </form>
          </Card>
        </div>
      )}

      {/* Tabel Daftar Pengguna */}
      <Card
        id="users-list"
        className="scroll-mt-20"
        noPadding
        title="Daftar Pengguna"
        subtitle={`Menampilkan ${filteredUsers.length} dari ${users.length} akun.`}
        action={
          <div className="flex flex-wrap gap-2">
            {selectedActiveUsers.length > 0 && (
              <>
                <Button
                  type="button"
                  size="sm"
                  variant="danger"
                  disabled={busy}
                  icon={<Trash2 className="size-3.5" />}
                  onClick={removeSelectedUsers}
                >
                  Hapus Terpilih ({selectedActiveUsers.length})
                </Button>
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={busy}
                  icon={<UserMinus className="size-3.5 text-amber-600" />}
                  onClick={deactivateSelectedUsers}
                >
                  Nonaktifkan Terpilih
                </Button>
              </>
            )}

            {isAdmin && (
              <>
                <input
                  ref={importInput}
                  type="file"
                  accept=".csv,text/csv"
                  onChange={importUsers}
                  className="hidden"
                />
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={busy}
                  icon={<Upload className="size-3.5" />}
                  onClick={() => importInput.current?.click()}
                >
                  Impor CSV
                </Button>
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={busy}
                  icon={<Download className="size-3.5" />}
                  onClick={downloadImportTemplate}
                >
                  Template
                </Button>
                <Button
                  type="button"
                  size="sm"
                  variant="secondary"
                  disabled={busy || !filteredUsers.length}
                  icon={<Table className="size-3.5" />}
                  onClick={exportUsers}
                >
                  Ekspor CSV
                </Button>
              </>
            )}
          </div>
        }
      >
        <div className="space-y-3 border-b border-outline/40 p-5 pb-4 dark:border-slate-800">
          <p className="text-xs leading-5 text-muted">
            Berkas CSV dapat dibuka di Excel. Impor memakai kolom NPK, nama, email, role, status, dan password (minimal 12 karakter untuk akun baru).
          </p>

          {isAdmin && (
            <label className="flex cursor-pointer items-center gap-2 text-xs font-semibold text-on-surface-variant">
              <input
                type="checkbox"
                checked={sendImportEmail}
                onChange={(event) => setSendImportEmail(event.target.checked)}
                className="size-4 rounded accent-primary"
              />
              Kirim email NPK dan kata sandi untuk tiap baris impor (kata sandi baru wajib diisi)
            </label>
          )}

          <ListFilterBar
            search={search}
            onSearch={setSearch}
            placeholder="Cari nama, NPK, atau alamat email..."
            filter={role}
            onFilter={setRole}
            label="Filter role"
            options={[
              { value: 'all', label: 'Semua role' },
              { value: 'student', label: 'Student' },
              { value: 'teacher', label: 'Teacher' },
              { value: 'admin', label: 'Admin' },
              { value: 'reviewer', label: 'Reviewer' }
            ]}
          />
        </div>

        <DataTable
          columns={[
            {
              key: 'select',
              label: (
                <input
                  type="checkbox"
                  aria-label="Pilih semua pengguna aktif hasil filter"
                  disabled={!activeFilteredUsers.length || busy}
                  checked={
                    activeFilteredUsers.length > 0 &&
                    selectedActiveUsers.length === activeFilteredUsers.length
                  }
                  onChange={toggleAllFilteredUsers}
                  className="size-4 rounded accent-primary"
                />
              ),
              className: 'w-11'
            },
            { key: 'user', label: 'Pengguna' },
            { key: 'email', label: 'Email' },
            { key: 'role', label: 'Role' },
            { key: 'status', label: 'Status' },
            { key: 'actions', label: 'Aksi', className: 'text-right' }
          ]}
          loading={loading}
          empty={!filteredUsers.length}
          emptyMessage="Tidak ada pengguna yang cocok dengan pencarian atau filter."
          ariaLabel="Tabel daftar pengguna"
          className="mx-4 mb-4 rounded-lg shadow-none sm:mx-6 sm:mb-5"
        >
          {userPage.pagedItems.map((user) => (
            <tr key={user.employee_npk}>
              <td>
                {user.status === 'active' && (
                  <input
                    type="checkbox"
                    aria-label={`Pilih ${user.fullname}`}
                    disabled={busy}
                    checked={selectedUsers.has(String(user.employee_npk))}
                    onChange={() => toggleUserSelection(user.employee_npk)}
                    className="size-4 rounded accent-primary"
                  />
                )}
              </td>
              <td>
                <p className="font-semibold text-on-surface">{user.fullname}</p>
                <p className="mt-0.5 font-mono text-xs text-muted">NPK: {user.employee_npk}</p>
              </td>
              <td className="text-on-surface-variant">{user.email || '—'}</td>
              <td>
                <StatusBadge status={user.role} />
              </td>
              <td>
                <StatusBadge status={user.status} dot />
              </td>
              <td className="min-w-[232px] text-right align-middle">
                {canManageUserAccounts && (
                  <div className="ml-auto grid w-[220px] grid-cols-2 gap-1.5">
                    <Button
                      type="button"
                      size="xs"
                      variant="info"
                      disabled={busy}
                      icon={<Pencil className="size-3.5" />}
                      onClick={() => setEditing(user)}
                      className="w-full justify-center"
                    >
                      Edit
                    </Button>

                    {isAdmin && (
                      <Button
                        type="button"
                        size="xs"
                        variant="indigo"
                        disabled={busy}
                        icon={<Mail className="size-3.5" />}
                        onClick={() => setEmailTarget(user)}
                        className="w-full justify-center"
                      >
                        Email
                      </Button>
                    )}

                    {user.status === 'active' ? (
                      <Button
                        type="button"
                        size="xs"
                        variant="warning"
                        disabled={busy}
                        icon={<UserMinus className="size-3.5" />}
                        onClick={() => deactivateUser(user)}
                        className="w-full justify-center"
                      >
                        Nonaktif
                      </Button>
                    ) : (
                      <Button
                        type="button"
                        size="xs"
                        variant="success"
                        disabled={busy}
                        icon={<CheckCircle2 className="size-3.5" />}
                        onClick={() => reactivateUser(user)}
                        className="w-full justify-center"
                      >
                        Aktifkan
                      </Button>
                    )}

                    {(isAdmin || user.role === 'student') && (
                      <Button
                        type="button"
                        size="xs"
                        variant="danger"
                        disabled={busy}
                        icon={<Trash2 className="size-3.5" />}
                        onClick={() => deleteUser(user)}
                        title="Hapus akun pengguna"
                        className="w-full justify-center"
                      >
                        Hapus
                      </Button>
                    )}
                  </div>
                )}
              </td>
            </tr>
          ))}
        </DataTable>

        <Pagination
          {...userPage}
          onPageChange={userPage.setPage}
          onPageSizeChange={userPage.setPageSize}
        />
      </Card>

      {/* Modal Edit Pengguna */}
      <Modal
        isOpen={Boolean(editing)}
        onClose={() => setEditing(null)}
        title="Edit Pengguna"
        subtitle={editing ? `NPK: ${editing.employee_npk}` : ''}
        maxWidth="max-w-lg"
      >
        {editing && (
          <form onSubmit={update} className="space-y-4">
            <input
              required
              name="fullname"
              defaultValue={editing.fullname}
              maxLength="150"
              placeholder="Nama lengkap"
              className={`${FIELD_CLASS} w-full`}
            />
            <input
              required
              type="email"
              name="email"
              defaultValue={editing.email}
              maxLength="254"
              placeholder="Email kerja"
              className={`${FIELD_CLASS} w-full`}
            />

            <div className="grid gap-3 sm:grid-cols-2">
              {editing.role === 'student' && isAdmin ? (
                <select name="role" defaultValue="student" className={`${FIELD_CLASS} w-full`}>
                  <option value="student">Student</option>
                  <option value="teacher">Teacher</option>
                </select>
              ) : editing.role === 'student' ? (
                <>
                  <input type="hidden" name="role" value="student" />
                  <select disabled value="student" className={`${FIELD_CLASS} w-full opacity-60`}>
                    <option value="student">Student</option>
                  </select>
                </>
              ) : (
                <select name="role" defaultValue={editing.role} className={`${FIELD_CLASS} w-full`}>
                  <option value="teacher">Teacher</option>
                  <option value="admin">Admin</option>
                  <option value="reviewer">Reviewer</option>
                </select>
              )}

              <select name="status" defaultValue={editing.status} className={`${FIELD_CLASS} w-full`}>
                <option value="active">Aktif</option>
                <option value="inactive">Non-aktif</option>
              </select>
            </div>

            {editing.role === 'student' && isAdmin && (
              <p className="rounded-lg border border-primary/20 bg-primary/5 p-3 text-xs leading-5 text-on-surface-variant dark:bg-red-950/30">
                Mengubah role Student menjadi Teacher tetap mempertahankan enrollment dan riwayat belajar.
              </p>
            )}

            <input
              type="password"
              name="password"
              minLength="12"
              maxLength="200"
              autoComplete="new-password"
              placeholder="Kata sandi baru (opsional, min. 12 karakter)"
              className={`${FIELD_CLASS} w-full`}
            />

            {isAdmin && (
              <div className="space-y-1.5">
                <label className="flex cursor-pointer items-center gap-2 text-xs font-semibold text-on-surface-variant">
                  <input
                    type="checkbox"
                    name="sendEmail"
                    defaultChecked={editing.sendCredentials}
                    className="size-4 accent-primary"
                  />
                  Kirim NPK dan kata sandi baru saat menyimpan
                </label>
                <p className="text-xs leading-5 text-muted">
                  Kosongkan kata sandi bila tidak ingin mengganti. Bila kirim email dicentang, kata sandi baru minimal 12 karakter wajib diisi.
                </p>
              </div>
            )}

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button type="button" variant="outline" onClick={() => setEditing(null)}>
                Batal
              </Button>
              <Button type="submit" variant="primary" disabled={busy} loading={busy}>
                Simpan Perubahan
              </Button>
            </div>
          </form>
        )}
      </Modal>

      {/* Modal Kirim Data Akun */}
      <Modal
        isOpen={Boolean(emailTarget)}
        onClose={() => setEmailTarget(null)}
        title="Kirim Data Akun"
        subtitle={emailTarget ? `${emailTarget.fullname} · NPK: ${emailTarget.employee_npk}` : ''}
        maxWidth="max-w-md"
      >
        {emailTarget && (
          <form onSubmit={sendAccountEmail} className="space-y-4">
            <p className="rounded-lg border border-outline/50 bg-surface-low/60 p-3.5 text-xs leading-5 text-on-surface-variant dark:border-slate-700 dark:bg-slate-800/50">
              Email akan dikirim ke <strong>{emailTarget.email || '—'}</strong> berisi NPK dan kata sandi baru di bawah ini (kata sandi lama tersimpan terenkripsi sehingga akan diperbarui).
            </p>

            {emailTarget.status !== 'active' && (
              <p role="alert" className="text-xs font-semibold text-red-700 dark:text-red-300">
                Aktifkan akun terlebih dahulu sebelum mengirim kredensial.
              </p>
            )}

            <label className="block text-xs font-bold text-on-surface-variant">
              Kata Sandi Baru *
              <input
                required
                type="password"
                name="password"
                minLength="12"
                maxLength="200"
                autoComplete="new-password"
                placeholder="Minimal 12 karakter"
                className={`${FIELD_CLASS} mt-1.5 w-full`}
              />
            </label>

            <div className="flex justify-end gap-2 border-t border-outline/40 pt-3 dark:border-slate-800">
              <Button type="button" variant="outline" disabled={busy} onClick={() => setEmailTarget(null)}>
                Batal
              </Button>
              <Button
                type="submit"
                variant="primary"
                disabled={busy || !emailTarget.email || emailTarget.status !== 'active'}
                loading={busy}
              >
                {busy ? 'Memproses...' : 'Ganti & Kirim Email'}
              </Button>
            </div>
          </form>
        )}
      </Modal>
    </div>
  );
}
