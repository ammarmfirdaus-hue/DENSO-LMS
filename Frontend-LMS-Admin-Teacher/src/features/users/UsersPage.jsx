import { useEffect, useMemo, useRef, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import StatusBadge from '../../components/StatusBadge';
import Button from '../../components/Button';
import showAlert from '../../utils/alert';

const field = 'h-11 rounded-lg border border-outline px-3 text-sm outline-none focus:border-primary';
const importColumns = ['npk', 'fullname', 'email', 'role', 'status', 'password'];

function csvCell(value) {
  const text = String(value ?? '');
  return /[",\r\n]/.test(text) ? `"${text.replaceAll('"', '""')}"` : text;
}

function parseCsv(text) {
  const rows = [];
  let row = [], cell = '', quoted = false;
  for (let i = 0; i < text.length; i += 1) {
    const char = text[i];
    if (quoted && char === '"' && text[i + 1] === '"') { cell += '"'; i += 1; }
    else if (char === '"') quoted = !quoted;
    else if (!quoted && char === ',') { row.push(cell); cell = ''; }
    else if (!quoted && (char === '\n' || char === '\r')) {
      if (char === '\r' && text[i + 1] === '\n') i += 1;
      row.push(cell); if (row.some(value => value.trim())) rows.push(row);
      row = []; cell = '';
    } else cell += char;
  }
  row.push(cell); if (row.some(value => value.trim())) rows.push(row);
  if (quoted) throw new Error('Format CSV tidak valid: tanda kutip belum ditutup.');
  if (!rows.length) throw new Error('File CSV belum memiliki data.');
  const headers = rows.shift().map(value => value.replace(/^\uFEFF/, '').trim().toLowerCase());
  const missing = importColumns.filter(column => !headers.includes(column));
  if (missing.length) throw new Error(`Kolom wajib belum ada: ${missing.join(', ')}.`);
  return rows.map((values, index) => ({
    ...Object.fromEntries(headers.map((header, column) => [header, (values[column] || '').trim()])),
    _row: index + 2
  }));
}

function downloadCsv(filename, rows) {
  const csv = `\uFEFF${rows.map(row => row.map(csvCell).join(',')).join('\r\n')}`;
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
  const link = document.createElement('a');
  link.href = url; link.download = filename; link.click();
  URL.revokeObjectURL(url);
}

export default function UsersPage() {
  const currentUser = useOutletContext();
  const isAdmin = currentUser?.role === 'admin';
  const [users, setUsers] = useState([]);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [sendImportEmail, setSendImportEmail] = useState(false);
  const [selectedUsers, setSelectedUsers] = useState(() => new Set());
  const importInput = useRef(null);
  const [search, setSearch] = useState('');
  const [role, setRole] = useState('all');
  const [editing, setEditing] = useState(null);
  const [emailTarget, setEmailTarget] = useState(null);
  const filteredUsers = useMemo(() => users.filter(user => (role === 'all' || user.role === role) && `${user.fullname} ${user.employee_npk} ${user.email} ${user.role}`.toLowerCase().includes(search.trim().toLowerCase())), [users, role, search]);
  const summary = useMemo(() => ({
    total: users.length,
    active: users.filter(user => user.status === 'active').length,
    inactive: users.filter(user => user.status !== 'active').length,
    students: users.filter(user => user.role === 'student').length,
    teachers: users.filter(user => user.role === 'teacher').length,
    admins: users.filter(user => user.role === 'admin').length,
    reviewers: users.filter(user => user.role === 'reviewer').length
  }), [users]);
  const userPage = usePagination(filteredUsers);
  const activeFilteredUsers = filteredUsers.filter(user => user.status === 'active');
  const selectedActiveUsers = activeFilteredUsers.filter(user => selectedUsers.has(String(user.employee_npk)));

  async function load() {
    try { setUsers((await api('/users')).users || []); setError(''); }
    catch (e) { setError(e.message); }
  }
  useEffect(() => { load(); }, []);
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
    setBusy(true); setError('');
    try {
      const result = data.sendEmail
        ? await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items: [{ ...data, action: 'upsert', status: 'active', label: `${data.fullname} (${data.npk})` }] }) })
        : await api('/users', { method: 'POST', body: JSON.stringify(data) });
      formElement.reset(); await load();
      if (data.sendEmail) showAlert.success('Pembuatan Akun Masuk Antrean', `Akun ${data.npk} dan email data akun akan diproses berurutan.`);
      else showAlert.success('Pengguna Berhasil Ditambahkan', `Akun untuk ${data.fullname} (${data.role.toUpperCase()}) telah berhasil dibuat.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Menambahkan Pengguna', e.message || 'Terjadi kesalahan saat membuat pengguna.'); }
    finally { setBusy(false); }
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
        confirmText: 'Promosikan ke Teacher', cancelText: 'Batal', icon: 'question'
      });
      if (!confirmed) return;
    }
    if (data.sendEmail && data.password.length < 12) {
      showAlert.error('Kata Sandi Baru Diperlukan', 'Isi kata sandi baru minimal 12 karakter agar email berisi kata sandi yang dapat langsung digunakan.');
      return;
    }
    setBusy(true); setError('');
    try {
      const result = data.sendEmail && !isStudentPromotion
        ? await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items: [{ ...data, action: 'upsert', npk: editing.employee_npk, label: `${data.fullname} (${editing.employee_npk})` }] }) })
        : await api(`/users/${encodeURIComponent(editing.employee_npk)}`, { method: 'PUT', body: JSON.stringify(data) });
      setEditing(null); await load();
      if (data.sendEmail && !isStudentPromotion) showAlert.success('Perubahan dan Email Masuk Antrean', `Perubahan akun ${editing.employee_npk} dan email kredensial akan diproses berurutan.`);
      else if (isStudentPromotion) {
        const emailMessage = data.sendEmail
          ? (result.email?.sent ? ' Email NPK dan kata sandi baru juga berhasil dikirim.' : ` Email belum terkirim: ${result.email?.reason || 'periksa konfigurasi SMTP.'}`)
          : '';
        showAlert.success('Student Dipromosikan', `Akun ${editing.employee_npk} sekarang ber-role Teacher. Riwayat belajarnya tetap tersimpan dan role Teacher dapat membuka Mode Belajar.${emailMessage}`);
      }
      else showAlert.success('Perubahan Disimpan', `Akun ${data.fullname} berhasil diperbarui.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Memperbarui Pengguna', e.message); }
    finally { setBusy(false); }
  }

  async function sendAccountEmail(event) {
    event.preventDefault();
    if (!emailTarget) return;
    const password = new FormData(event.currentTarget).get('password');
    setBusy(true); setError('');
    try {
      await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items: [{
        action: 'email', npk: emailTarget.employee_npk, fullname: emailTarget.fullname,
        email: emailTarget.email, role: emailTarget.role, status: emailTarget.status,
        password,
        label: `${emailTarget.fullname} (${emailTarget.employee_npk})`
      }] }) });
      setEmailTarget(null);
      showAlert.success('Email Masuk Antrean', `NPK dan kata sandi baru akan dikirim ke ${emailTarget.email}. Kata sandi akun akan diganti.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Mengirim Email', e.message); }
    finally { setBusy(false); }
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
    setBusy(true); setError('');
    try {
      await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items: [{ action: 'deactivate', npk: user.employee_npk, label: `${user.fullname} (${user.employee_npk})`, role: user.role }] }) });
      showAlert.success('Penonaktifan Masuk Antrean', `Akun ${user.fullname} akan diproses dan statusnya tampil secara realtime.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Menghapus Pengguna', e.message); }
    finally { setBusy(false); }
  }

  async function deleteUser(user) {
    const confirmed = await showAlert.confirm({
      title: 'Hapus Pengguna Permanen?',
      text: `Akun ${user.fullname} akan dihapus jika tidak memiliki riwayat terkait. Jika ada riwayat, akun otomatis dinonaktifkan agar seluruh riwayat tetap tersimpan.`,
      confirmText: 'Hapus / Nonaktifkan', cancelText: 'Batal', danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError('');
    try {
      await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items: [{ action: 'delete', npk: user.employee_npk, label: `${user.fullname} (${user.employee_npk})`, role: user.role }] }) });
      setSelectedUsers(previous => { const next = new Set(previous); next.delete(String(user.employee_npk)); return next; });
      showAlert.success('Penghapusan Masuk Antrean', `Penghapusan akun ${user.fullname} diproses; progresnya dapat dipantau di antrean.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Menghapus Pengguna', e.message); }
    finally { setBusy(false); }
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
    setBusy(true); setError('');
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
      setSelectedUsers(previous => {
        const next = new Set(previous);
        next.delete(String(user.employee_npk));
        return next;
      });
      await load();
      showAlert.success('Pengguna Diaktifkan', `Akun ${user.fullname} sekarang aktif kembali.`);
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Mengaktifkan Pengguna', e.message);
    } finally { setBusy(false); }
  }

  function toggleUserSelection(npk) {
    setSelectedUsers(previous => {
      const next = new Set(previous);
      if (next.has(String(npk))) next.delete(String(npk));
      else next.add(String(npk));
      return next;
    });
  }

  function toggleAllFilteredUsers() {
    setSelectedUsers(previous => {
      const next = new Set(previous);
      const allSelected = activeFilteredUsers.length > 0 && activeFilteredUsers.every(user => next.has(String(user.employee_npk)));
      activeFilteredUsers.forEach(user => allSelected ? next.delete(String(user.employee_npk)) : next.add(String(user.employee_npk)));
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
    setBusy(true); setError('');
    try {
      const result = await api('/jobs/users', {
        method: 'POST',
        body: JSON.stringify({ items: selectedActiveUsers.map(user => ({
          action: 'delete', npk: user.employee_npk, label: `${user.fullname} (${user.employee_npk})`, role: user.role
        })) })
      });
      setSelectedUsers(new Set());
      showAlert.success('Penghapusan Masuk Antrean', `${result.total} akun akan diproses satu per satu. Progress tetap tersimpan jika halaman dimuat ulang.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Membuat Antrean Hapus', e.message); }
    finally { setBusy(false); }
  }

  async function deactivateSelectedUsers() {
    if (!selectedActiveUsers.length) return;
    const confirmed = await showAlert.confirm({
      title: `Nonaktifkan ${selectedActiveUsers.length} pengguna?`,
      text: 'Akun tidak dapat login kembali sampai diaktifkan. Riwayat tetap tersimpan.',
      confirmText: 'Nonaktifkan Terpilih', cancelText: 'Batal', danger: true
    });
    if (!confirmed) return;
    setBusy(true); setError('');
    try {
      const items = selectedActiveUsers.map(user => ({ action: 'deactivate', npk: user.employee_npk, role: user.role, label: `${user.fullname} (${user.employee_npk})` }));
      await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items }) });
      setSelectedUsers(new Set());
      showAlert.success('Penonaktifan Masuk Antrean', `${items.length} akun akan diproses secara berurutan.`);
    } catch (e) { setError(e.message); showAlert.error('Gagal Menonaktifkan Pengguna', e.message); }
    finally { setBusy(false); }
  }

  function exportUsers() {
    const columns = ['npk', 'fullname', 'email', 'role', 'status', 'department', 'position', 'phone_number', 'created_at', 'password'];
    const rows = [columns, ...filteredUsers.map(user => columns.map(column => column === 'npk' ? user.employee_npk : column === 'password' ? '' : user[column] ?? ''))];
    downloadCsv('kelola-pengguna.csv', rows);
  }

  function downloadImportTemplate() {
    downloadCsv('template-impor-pengguna.csv', [importColumns]);
  }

  async function importUsers(event) {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;
    setBusy(true); setError('');
    try {
      if (!file.name.toLowerCase().endsWith('.csv')) throw new Error('Gunakan file CSV. Unduh template CSV dari halaman ini.');
      const rows = parseCsv(await file.text());
      const seen = new Set();
      const existingByNpk = new Map(users.map(user => [String(user.employee_npk).toLowerCase(), user]));
      for (const row of rows) {
        row.npk = row.npk.trim(); row.fullname = row.fullname.trim(); row.email = row.email.trim();
        row.role = row.role.toLowerCase() || 'student'; row.status = row.status.toLowerCase() || 'active';
        const key = row.npk.toLowerCase();
        if (!row.npk || !row.fullname || !row.email) throw new Error(`Baris ${row._row}: NPK, fullname, dan email wajib diisi.`);
        if (seen.has(key)) throw new Error(`Baris ${row._row}: NPK ${row.npk} muncul lebih dari sekali.`);
        seen.add(key);
        if (!['student', 'teacher', 'admin', 'reviewer'].includes(row.role)) throw new Error(`Baris ${row._row}: role harus student, teacher, admin, atau reviewer.`);
        if (!['active', 'inactive'].includes(row.status)) throw new Error(`Baris ${row._row}: status harus active atau inactive.`);
        const current = existingByNpk.get(key);
        if (current && (current.role === 'student') !== (row.role === 'student')) throw new Error(`Baris ${row._row}: akun tidak dapat dipindahkan antara database Student dan pegawai.`);
        if (!current && row.password.length < 12) throw new Error(`Baris ${row._row}: akun baru wajib memiliki password minimal 12 karakter.`);
        if (sendImportEmail && (!row.password || row.status !== 'active')) throw new Error(`Baris ${row._row}: email akun memerlukan password baru dan status active.`);
      }
      const confirmed = await showAlert.confirm({
        title: 'Impor Data Pengguna?',
        text: `${rows.length} baris akan diproses. NPK yang sudah ada akan diperbarui; akun baru akan dibuat.`,
        confirmText: 'Ya, Impor', cancelText: 'Batal', icon: 'question'
      });
      if (!confirmed) return;
      const items = rows.map(row => ({
        action: 'upsert', npk: row.npk, label: `${row.fullname} (${row.npk})`, fullname: row.fullname,
        email: row.email, role: row.role, status: row.status, password: row.password, sendEmail: sendImportEmail
      }));
      const result = await api('/jobs/users', { method: 'POST', body: JSON.stringify({ items }) });
      showAlert.success('Impor Masuk Antrean', `${result.total} baris akan diproses berurutan. ${sendImportEmail ? 'Email dikirim sesuai antrean.' : ''} Progress dan hasil tetap tersedia setelah halaman dimuat ulang.`);
    } catch (importError) {
      setError(importError.message);
      showAlert.error('Gagal Mengimpor Pengguna', importError.message);
    } finally { setBusy(false); }
  }

  return (
    <div className="mx-auto max-w-7xl space-y-6">
      <PageHeader
        eyebrow="MANAJEMEN PENGGUNA"
        title="Kelola Pengguna"
        description={isAdmin ? "Kelola akun Student, Teacher, Admin, dan Reviewer." : "Kelola akun Student: ubah data, aktifkan kembali, atau hapus akses."}
        actions={
          <span className="rounded-xl border border-outline/50 bg-white px-3 py-1.5 text-xs font-semibold text-muted shadow-xs dark:border-slate-800 dark:bg-slate-900">
            {summary.total} total akun terdaftar
          </span>
        }
      />
      {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">{error}</p>}
      <section aria-label="Ringkasan pengguna" className="grid gap-3 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-7">
        <StatCard label="Total Akun" value={summary.total} icon="groups" tone="primary" />
        <StatCard label="Aktif" value={summary.active} icon="verified_user" tone="emerald" />
        <StatCard label="Nonaktif" value={summary.inactive} icon="person_off" tone="amber" />
        <StatCard label="Student" value={summary.students} icon="school" tone="blue" />
        <StatCard label="Teacher" value={summary.teachers} icon="co_present" tone="emerald" />
        <StatCard label="Admin" value={summary.admins} icon="admin_panel_settings" tone="primary" />
        <StatCard label="Reviewer" value={summary.reviewers} icon="fact_check" tone="blue" />
      </section>



    {isAdmin && <form onSubmit={create} className="mt-7 grid gap-3 rounded-2xl border border-outline/50 bg-white p-5 md:grid-cols-2"><input required name="npk" maxLength="50" placeholder="NPK" className={field}/><input required name="fullname" maxLength="150" placeholder="Nama lengkap" className={field}/><input required type="email" name="email" maxLength="254" placeholder="Email kerja" className={field}/><input required type="password" name="password" minLength="12" maxLength="200" placeholder="Kata sandi awal (min. 12 karakter)" className={field}/><select name="role" className={`${field} bg-white`}><option value="student">Student</option><option value="teacher">Teacher</option><option value="admin">Admin</option><option value="reviewer">Reviewer</option></select><button disabled={busy} className="rounded-lg bg-primary px-4 py-2 font-semibold text-white disabled:opacity-60">{busy ? 'Menyimpan...' : 'Tambah pengguna'}</button><label className="flex items-center gap-2 text-sm text-secondary md:col-span-2"><input type="checkbox" name="sendEmail" className="size-4 accent-primary"/>Kirim NPK dan kata sandi awal ke email pengguna</label><p className="text-xs text-muted md:col-span-2">Akun Student disimpan ke database MySQL Student. Pastikan STUDENT_DB_PASSWORD sudah dikonfigurasi dan SMTP tersedia untuk email.</p></form>}
    <section className="mt-5"><div className="mb-3 flex flex-wrap items-center justify-between gap-3"><div><h2 className="font-bold">Daftar pengguna</h2><span className="text-sm text-muted">{filteredUsers.length} dari {users.length}</span></div><div className="flex flex-wrap gap-2">{selectedActiveUsers.length > 0 && <button type="button" disabled={busy} onClick={removeSelectedUsers} className="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">delete_sweep</span>Hapus Terpilih ({selectedActiveUsers.length})</button>}{selectedActiveUsers.length > 0 && <button type="button" disabled={busy} onClick={deactivateSelectedUsers} className="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">person_off</span>Nonaktifkan Terpilih</button>}{isAdmin && <><input ref={importInput} type="file" accept=".csv,text/csv" onChange={importUsers} className="hidden"/><button type="button" disabled={busy} onClick={() => importInput.current?.click()} className="inline-flex items-center justify-center gap-2 rounded-lg border border-outline bg-white px-3 py-2 text-sm font-semibold hover:bg-surface-low disabled:opacity-50"><span className="material-symbol" aria-hidden="true">upload_file</span>Impor CSV</button><button type="button" disabled={busy} onClick={downloadImportTemplate} className="inline-flex items-center justify-center gap-2 rounded-lg border border-outline bg-white px-3 py-2 text-sm font-semibold hover:bg-surface-low disabled:opacity-50"><span className="material-symbol" aria-hidden="true">download</span>Template</button><button type="button" disabled={busy || !filteredUsers.length} onClick={exportUsers} className="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-primary-container disabled:opacity-50"><span className="material-symbol" aria-hidden="true">table_view</span>Ekspor CSV</button></>}</div></div><p className="mb-3 text-xs leading-5 text-muted">CSV dapat dibuka di Excel. Impor memakai kolom NPK, nama, email, role, status, dan password. Password wajib untuk akun baru; kosongkan untuk mempertahankan password akun yang diperbarui. Ekspor mengikuti hasil filter dan tidak menyertakan password.</p>{isAdmin && <label className="mb-3 flex items-center gap-2 text-sm text-secondary"><input type="checkbox" checked={sendImportEmail} onChange={event => setSendImportEmail(event.target.checked)} className="size-4 accent-primary"/>Kirim email NPK dan kata sandi untuk tiap baris impor (password baru wajib diisi)</label>}<ListFilterBar search={search} onSearch={setSearch} placeholder="Cari nama, NPK, atau email..." filter={role} onFilter={setRole} label="Filter role" options={[{value:'all',label:'Semua role'},{value:'student',label:'Student'},{value:'teacher',label:'Teacher'},{value:'admin',label:'Admin'},{value:'reviewer',label:'Reviewer'}]}/><div className="overflow-hidden rounded-2xl border border-outline/50 bg-white"><div className="max-w-full overflow-x-auto overscroll-x-contain" role="region" aria-label="Tabel daftar pengguna" tabIndex={0}><table className="w-full min-w-[900px] border-collapse text-left text-sm"><thead className="bg-surface-low text-xs uppercase tracking-wide text-muted"><tr><th scope="col" className="w-12 px-4 py-3"><input type="checkbox" aria-label="Pilih semua pengguna aktif hasil filter" disabled={!activeFilteredUsers.length || busy} checked={activeFilteredUsers.length > 0 && selectedActiveUsers.length === activeFilteredUsers.length} onChange={toggleAllFilteredUsers} className="size-4 accent-primary"/></th><th scope="col" className="px-5 py-3 font-semibold">Pengguna</th><th scope="col" className="px-4 py-3 font-semibold">Email</th><th scope="col" className="px-4 py-3 font-semibold">Role</th><th scope="col" className="px-4 py-3 font-semibold">Status</th><th scope="col" className="px-5 py-3 text-right font-semibold">Aksi</th></tr></thead><tbody className="divide-y divide-outline/40">{userPage.pagedItems.map(user => <tr key={user.employee_npk} className="align-middle hover:bg-surface-low/50"><td className="px-4 py-4">{user.status === 'active' && <input type="checkbox" aria-label={`Pilih ${user.fullname}`} disabled={busy} checked={selectedUsers.has(String(user.employee_npk))} onChange={() => toggleUserSelection(user.employee_npk)} className="size-4 accent-primary"/>}</td><td className="px-5 py-4"><p className="font-semibold text-on-surface">{user.fullname}</p><p className="mt-1 text-xs text-muted">NPK: {user.employee_npk}</p></td><td className="px-4 py-4 text-secondary">{user.email || '—'}</td><td className="px-4 py-4"><StatusBadge status={user.role} /></td><td className="px-4 py-4"><StatusBadge status={user.status} /></td><td className="px-5 py-4"><div className="flex justify-end gap-2"><button type="button" disabled={busy} onClick={() => setEditing(user)} className="rounded-lg border border-outline/60 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-surface-low disabled:opacity-50">Edit</button>{isAdmin && <button type="button" disabled={busy} onClick={() => setEmailTarget(user)} className="rounded-lg border border-outline/60 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-surface-low disabled:opacity-50"><span className="material-symbol" aria-hidden="true">mail</span>Email</button>}{user.status === 'active' && <button type="button" disabled={busy} onClick={() => deactivateUser(user)} className="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">delete</span>Nonaktifkan</button>}{user.status !== 'active' && <button type="button" disabled={busy} onClick={() => reactivateUser(user)} className="rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">check_circle</span>Aktifkan</button>}{(isAdmin || user.role === 'student') && <button type="button" disabled={busy} onClick={() => deleteUser(user)} className="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50"><span className="material-symbol" aria-hidden="true">delete</span>Hapus</button>}</div></td></tr>)}</tbody></table>{!filteredUsers.length && <p className="p-8 text-center text-sm text-muted">Tidak ada pengguna yang cocok dengan pencarian/filter.</p>}</div><Pagination {...userPage} onPageChange={userPage.setPage} onPageSizeChange={userPage.setPageSize}/></div></section>
    {editing && <div className="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4"><form onSubmit={update} className="w-full max-w-lg space-y-3 rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="edit-user-title"><div><h2 id="edit-user-title" className="text-xl font-bold">Edit Pengguna</h2><p className="text-sm text-muted">NPK: {editing.employee_npk}</p></div><input required name="fullname" defaultValue={editing.fullname} maxLength="150" placeholder="Nama lengkap" className={`${field} w-full`}/><input required type="email" name="email" defaultValue={editing.email} maxLength="254" placeholder="Email kerja" className={`${field} w-full`}/><div className="grid gap-3 sm:grid-cols-2">{editing.role === 'student' && isAdmin ? <select name="role" defaultValue="student" className={`${field} bg-white`}><option value="student">Student</option><option value="teacher">Teacher</option></select> : editing.role === 'student' ? <><input type="hidden" name="role" value="student"/><select disabled value="student" className={`${field} bg-zinc-100`}><option value="student">Student</option></select></> : <select name="role" defaultValue={editing.role} className={`${field} bg-white`}><option value="teacher">Teacher</option><option value="admin">Admin</option><option value="reviewer">Reviewer</option></select>}<select name="status" defaultValue={editing.status} className={`${field} bg-white`}><option value="active">Aktif</option><option value="inactive">Non-aktif</option></select></div>{editing.role === 'student' && isAdmin && <p className="rounded-lg border border-primary/20 bg-primary/5 p-3 text-xs leading-5 text-secondary sm:col-span-2">Mengubah role Student menjadi Teacher mempertahankan enrollment dan riwayat course. Teacher akan belajar melalui mode terpisah tanpa mengubah role pegawainya.</p>}<input type="password" name="password" minLength="12" maxLength="200" autoComplete="new-password" placeholder="Kata sandi baru (opsional, min. 12 karakter)" className={`${field} w-full`}/>{isAdmin && <><label className="flex items-center gap-2 text-sm text-secondary"><input type="checkbox" name="sendEmail" defaultChecked={editing.sendCredentials} className="size-4 accent-primary"/>Kirim NPK dan kata sandi baru saat menyimpan</label><p className="text-xs leading-5 text-muted">Tanpa email, kosongkan kata sandi untuk mempertahankan sandi akun. Jika email dicentang, isi kata sandi baru minimal 12 karakter; sandi itu akan menggantikan sandi lama dan dicantumkan di email.</p></>}<div className="flex justify-end gap-2 pt-2"><button type="button" onClick={() => setEditing(null)} className="rounded-lg border border-outline px-4 py-2 text-sm font-semibold">Batal</button><button disabled={busy} className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Simpan</button></div></form></div>}
    {emailTarget && <div className="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4"><form onSubmit={sendAccountEmail} className="w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="send-user-email-title"><div><h2 id="send-user-email-title" className="text-xl font-bold">Kirim Data Akun</h2><p className="mt-1 text-sm text-muted">{emailTarget.fullname} · NPK: {emailTarget.employee_npk}</p></div><p className="rounded-xl border border-outline/60 bg-surface-low p-4 text-sm leading-6 text-secondary">Email dikirim ke <strong>{emailTarget.email || '—'}</strong> beserta NPK dan kata sandi yang dapat langsung digunakan. Kata sandi lama tersimpan sebagai hash, jadi kata sandi baru di bawah ini akan menggantikannya.</p>{emailTarget.status !== 'active' && <p role="alert" className="text-sm text-red-700">Aktifkan akun terlebih dahulu sebelum mengirim data akun.</p>}<label className="block text-sm font-semibold text-secondary">Kata sandi baru<input required type="password" name="password" minLength="12" maxLength="200" autoComplete="new-password" placeholder="Minimal 12 karakter" className={`${field} mt-1.5 w-full`}/></label><div className="flex justify-end gap-2"><button type="button" disabled={busy} onClick={() => setEmailTarget(null)} className="rounded-lg border border-outline px-4 py-2 text-sm font-semibold">Batal</button><button type="submit" disabled={busy || !emailTarget.email || emailTarget.status !== 'active'} className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{busy ? 'Memproses...' : 'Ganti & Kirim Email'}</button></div></form></div>}
  </div>
  );
}
