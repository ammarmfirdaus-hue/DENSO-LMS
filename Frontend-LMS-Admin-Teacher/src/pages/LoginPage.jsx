import { useState } from 'react';

export default function LoginPage({ onLogin }) {
  const [npk, setNpk] = useState(''); const [password, setPassword] = useState(''); const [error, setError] = useState(''); const [busy, setBusy] = useState(false);
  async function submit(event) { event.preventDefault(); setError(''); setBusy(true); try { await onLogin(npk, password); } catch (e) { setError(e.message); } finally { setBusy(false); } }
  return <main className="grid min-h-screen bg-white lg:grid-cols-2">
    <section className="relative hidden overflow-hidden bg-[#f4efee] lg:block">
      <img src="/design-reference/dashboard_teacher_integrated_role_switcher/screen.png" alt="Tampilan LMS Denso" className="absolute inset-0 h-full w-full object-cover object-top opacity-75" />
      <div className="absolute inset-0 bg-gradient-to-t from-[#240a0b]/80 via-transparent to-[#240a0b]/10" />
      <div className="absolute bottom-14 left-14 max-w-lg text-white"><p className="mb-3 text-sm font-semibold uppercase tracking-[.3em]">DENSO Learning System</p><h1 className="text-4xl font-bold leading-tight">Kelola pembelajaran. Kembangkan potensi.</h1><p className="mt-4 text-white/80">Satu ruang untuk pengajar dan administrator membangun pengalaman belajar yang lebih baik.</p></div>
    </section>
    <section className="flex items-center justify-center px-6 py-12"><div className="w-full max-w-md">
      <div className="mb-10 flex items-center gap-3"><div className="grid size-12 place-items-center rounded-2xl bg-primary text-xl font-bold text-white">D</div><div><p className="text-xl font-bold">DENSO LMS</p><p className="text-sm text-muted">Portal Teacher & Admin</p></div></div>
      <h2 className="text-3xl font-bold">Masuk ke akun Anda</h2><p className="mt-2 text-muted">Gunakan akun internal yang telah didaftarkan.</p>
      <form className="mt-8 space-y-5" onSubmit={submit}>
        <label className="block text-sm font-semibold">NPK<input required autoComplete="username" value={npk} onChange={e => setNpk(e.target.value)} className="mt-2 h-12 w-full rounded-xl border border-outline bg-white px-4 font-normal outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15" placeholder="Masukkan NPK" /></label>
        <label className="block text-sm font-semibold">Kata sandi<input required type="password" autoComplete="current-password" value={password} onChange={e => setPassword(e.target.value)} className="mt-2 h-12 w-full rounded-xl border border-outline bg-white px-4 font-normal outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15" placeholder="Masukkan kata sandi" /></label>
        {error && <p role="alert" className="rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</p>}
        <button disabled={busy} className="h-12 w-full rounded-xl bg-primary font-semibold text-white shadow-md shadow-primary/20 transition hover:bg-primary-container disabled:opacity-60">{busy ? 'Memeriksa…' : 'Masuk'}</button>
      </form><p className="mt-8 text-center text-xs text-muted">Akses hanya untuk akun dengan role teacher atau admin.</p>
    </div></section>
  </main>;
}
