import { useEffect, useMemo, useState } from 'react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import showAlert from '../../utils/alert';

export default function UsersPage() {
  const [users, setUsers] = useState([]); const [error, setError] = useState(''); const [busy, setBusy] = useState(false); const [search, setSearch] = useState(''); const [role, setRole] = useState('all');
  const filteredUsers = useMemo(() => users.filter(user => (role === 'all' || user.role === role) && `${user.fullname} ${user.employee_npk} ${user.email} ${user.role}`.toLowerCase().includes(search.trim().toLowerCase())), [users, role, search]);
  const userPage = usePagination(filteredUsers);
  async function load() { try { setUsers((await api('/users')).users); setError(''); } catch (e) { setError(e.message); } }
  useEffect(() => { load(); }, []);
  async function create(event) {
    event.preventDefault();
    const formElement = event.currentTarget;
    const data = Object.fromEntries(new FormData(formElement));
    setBusy(true);
    setError('');
    try {
      await api('/users', { method: 'POST', body: JSON.stringify(data) });
      formElement.reset();
      await load();
      showAlert.success(
        'Pengguna Berhasil Ditambahkan',
        `Akun untuk ${data.fullname} (${data.role.toUpperCase()}) telah berhasil dibuat.`
      );
    } catch (e) {
      setError(e.message);
      showAlert.error('Gagal Menambahkan Pengguna', e.message || 'Terjadi kesalahan saat membuat pengguna.');
    } finally {
      setBusy(false);
    }
  }
  return <div><p className="text-sm font-semibold text-primary">ADMINISTRASI</p><h1 className="mt-2 text-3xl font-bold">Pengguna</h1><p className="mt-2 text-muted">Kelola akun teacher, admin, dan reviewer.</p>{error && <p role="alert" className="mt-4 rounded-xl bg-red-50 p-4 text-red-700">{error}</p>}<form onSubmit={create} className="mt-7 grid gap-3 rounded-2xl border border-outline/50 bg-white p-5 md:grid-cols-2"><input required name="npk" maxLength="50" placeholder="NPK" className="h-11 rounded-lg border border-outline px-3"/><input required name="fullname" maxLength="150" placeholder="Nama lengkap" className="h-11 rounded-lg border border-outline px-3"/><input required type="email" name="email" maxLength="254" placeholder="Email kerja" className="h-11 rounded-lg border border-outline px-3"/><input required type="password" name="password" minLength="12" maxLength="200" placeholder="Kata sandi awal (min. 12 karakter)" className="h-11 rounded-lg border border-outline px-3"/><select name="role" className="h-11 rounded-lg border border-outline bg-white px-3"><option value="teacher">Teacher</option><option value="admin">Admin</option><option value="reviewer">Reviewer</option></select><button disabled={busy} className="rounded-lg bg-primary px-4 py-2 font-semibold text-white disabled:opacity-60">{busy ? 'Menyimpan…' : 'Tambah pengguna'}</button></form>
    <section className="mt-5"><div className="mb-3 flex items-center justify-between"><h2 className="font-bold">Daftar pengguna</h2><span className="text-sm text-muted">{filteredUsers.length} dari {users.length}</span></div><ListFilterBar search={search} onSearch={setSearch} placeholder="Cari nama, NPK, atau email..." filter={role} onFilter={setRole} label="Filter role" options={[{value:'all',label:'Semua role'},{value:'teacher',label:'Teacher'},{value:'admin',label:'Admin'},{value:'reviewer',label:'Reviewer'}]}/><div className="divide-y divide-outline/40 rounded-2xl border border-outline/50 bg-white">{userPage.pagedItems.map(user => <div key={user.employee_npk} className="flex items-center justify-between gap-3 px-5 py-4"><div><p className="font-semibold">{user.fullname}</p><p className="text-sm text-muted">{user.employee_npk} · {user.email}</p></div><span className="rounded-full bg-secondary-container/30 px-3 py-1 text-xs font-semibold capitalize text-secondary">{user.role}</span></div>)}{!filteredUsers.length && <p className="p-8 text-center text-sm text-muted">Tidak ada pengguna yang cocok dengan pencarian/filter.</p>}<Pagination {...userPage} onPageChange={userPage.setPage} onPageSizeChange={userPage.setPageSize}/></div></section>
  </div>;
}
