import { useState } from 'react';
import showAlert from '../../utils/alert';

export default function LoginPage({ onLogin }) {
  const [npk, setNpk] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function submit(event) {
    event.preventDefault();
    setError('');
    setBusy(true);
    try {
      await onLogin(npk, password);
    } catch (loginError) {
      const msg = loginError.message || 'Kredensial tidak valid. Silakan periksa NPK dan kata sandi Anda.';
      setError(msg);
      showAlert.error('Gagal Masuk', msg);
    } finally {
      setBusy(false);
    }
  }

  function handleForgotPassword(e) {
    e.preventDefault();
    showAlert.info(
      'Lupa Kata Sandi?',
      'Silakan hubungi Administrator DENSO LMS melalui email admin.lms@denso.co.id atau Departemen HRD untuk reset kata sandi akun Anda.'
    );
  }

  function handleContactAdmin(e) {
    e.preventDefault();
    showAlert.info(
      'Bantuan Akun DENSO LMS',
      'Pendaftaran dan aktivasi akun dikelola terpusat oleh Admin HR & Trainer DENSO. Hubungi tim LMS untuk informasi akun Anda.'
    );
  }

  return (
    <main className="flex min-h-dvh w-full flex-col bg-surface antialiased lg:flex-row">
      <section className="relative hidden min-h-dvh flex-1 overflow-hidden bg-[#0b0e14] lg:flex">
        <div
          className="absolute inset-0 bg-cover bg-left bg-no-repeat"
          style={{ backgroundImage: "url('/assets/img/login/DX_LMS_M_Login.png')" }}
          role="img"
          aria-label="Karyawan DENSO menggunakan tablet di area produksi"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-[#0b0e14]/95 from-[20%] via-[#0b0e14]/70 to-[#0b0e14]/10" />

        <div className="relative z-10 mx-auto flex w-full max-w-2xl flex-col justify-end p-8 pb-10 md:p-12 lg:mx-0 lg:ml-auto lg:p-16 lg:px-20 lg:pb-12 xl:ml-0">
          <div className="mb-6 mt-auto">
            <div className="mb-6 flex items-center gap-4">
              <div className="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white p-1.5 shadow-lg"><img src="/assets/img/icon/denso-vector-logo.png" alt="DENSO" className="size-full object-contain" /></div>
              <h1 className="text-2xl font-bold leading-tight tracking-tight text-white lg:text-3xl">
                Learning Management System
              </h1>
            </div>
            <p className="mb-10 max-w-[460px] text-justify text-sm leading-relaxed text-gray-200 lg:text-[15px]">
              Platform pembelajaran digital terintegrasi untuk meningkatkan kompetensi, produktivitas, dan standar kualitas operasional secara berkelanjutan.
            </p>

            <div className="flex max-w-[460px] divide-x divide-white/20">
              <div className="flex flex-1 flex-col items-center pr-4 text-center lg:items-start lg:text-left">
                <span className="material-symbol mb-2 text-[28px] text-primary-container drop-shadow-md" aria-hidden="true">menu_book</span>
                <span className="text-[11px] font-semibold text-white/90 lg:text-xs">Learning<br />Path</span>
              </div>
              <div className="flex flex-1 flex-col items-center px-4 text-center lg:items-start lg:text-left">
                <span className="material-symbol mb-2 text-[28px] text-primary-container drop-shadow-md" aria-hidden="true">assignment_turned_in</span>
                <span className="text-[11px] font-semibold text-white/90 lg:text-xs">Assessment</span>
              </div>
              <div className="flex flex-1 flex-col items-center px-4 text-center lg:items-start lg:text-left">
                <span className="material-symbol mb-2 text-[28px] text-primary-container drop-shadow-md" aria-hidden="true">insights</span>
                <span className="text-[11px] font-semibold text-white/90 lg:text-xs">Progress<br />Tracking</span>
              </div>
              <div className="flex flex-1 flex-col items-center pl-4 text-center lg:items-start lg:text-left">
                <span className="material-symbol mb-2 text-[28px] text-primary-container drop-shadow-md" aria-hidden="true">workspace_premium</span>
                <span className="text-[11px] font-semibold text-white/90 lg:text-xs">Certification</span>
              </div>
            </div>
          </div>

          <footer>
            <div className="mb-4 h-px w-12 bg-white/30" />
            <p className="text-[11px] font-medium text-gray-300 lg:text-xs">© Learning Management System</p>
          </footer>
        </div>
      </section>

      <section className="flex min-h-dvh w-full flex-1 flex-col items-center justify-center bg-surface p-8 py-16 lg:py-8">
        <div className="mb-8 text-center lg:mb-8">
          <img src="/assets/img/icon/denso-vector-logo.png" alt="DENSO" className="mx-auto h-20 w-20 rounded-2xl bg-white object-contain shadow-sm ring-1 ring-black/5" />
          <div className="mt-3 text-[11px] font-semibold tracking-[0.15em] text-muted">LEARNING MANAGEMENT SYSTEM</div>
        </div>

        <div className="mb-8 w-full max-w-sm text-center">
          <h2 className="text-[22px] font-bold leading-7 tracking-tight text-on-surface">Selamat datang!</h2>
          <p className="mt-1 text-sm text-muted">Gunakan NPK atau email dan kata sandi akun DENSO Anda</p>
        </div>

        <form className="w-full max-w-sm space-y-4" onSubmit={submit}>
          {error && (
            <div role="alert" className="flex items-center gap-2 rounded-xl bg-red-50 p-3 text-sm text-red-800">
              <span className="material-symbol text-xl" aria-hidden="true">error</span>
              <span>{error}</span>
            </div>
          )}

          <label className="relative block w-full">
            <span className="sr-only">NPK atau Email</span>
            <span className="material-symbol absolute left-4 top-1/2 -translate-y-1/2 text-2xl text-muted" aria-hidden="true">person</span>
            <input
              required
              autoComplete="username"
              value={npk}
              onChange={event => setNpk(event.target.value)}
              className="h-[52px] w-full rounded-xl border border-outline bg-white pl-12 pr-4 text-base text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
              placeholder="NPK atau Email"
            />
          </label>

          <label className="relative block w-full">
            <span className="sr-only">Password</span>
            <span className="material-symbol absolute left-4 top-1/2 -translate-y-1/2 text-2xl text-muted" aria-hidden="true">lock</span>
            <input
              required
              type={showPassword ? 'text' : 'password'}
              autoComplete="current-password"
              value={password}
              onChange={event => setPassword(event.target.value)}
              className="h-[52px] w-full rounded-xl border border-outline bg-white pl-12 pr-12 text-base text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
              placeholder="Password"
            />
            <button
              type="button"
              className="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1 text-on-surface/60 transition hover:bg-surface-container"
              onClick={() => setShowPassword(value => !value)}
              aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
              aria-pressed={showPassword}
            >
              <span className="material-symbol text-xl" aria-hidden="true">{showPassword ? 'visibility' : 'visibility_off'}</span>
            </button>
          </label>

          <div className="text-right">
            <button type="button" onClick={handleForgotPassword} className="text-sm font-medium text-secondary hover:underline cursor-pointer bg-transparent border-0 p-0">Lupa password?</button>
          </div>

          <button
            type="submit"
            disabled={busy}
            className="h-12 w-full rounded-xl bg-primary text-sm font-semibold text-white shadow-[0_8px_20px_rgba(215,25,32,0.18)] transition hover:bg-primary-container active:scale-[0.99] disabled:cursor-wait disabled:opacity-60 cursor-pointer"
          >
            {busy ? 'Memeriksa…' : 'Masuk'}
          </button>
        </form>

        <div className="mt-8 text-center">
          <p className="text-sm text-muted">Belum punya akun? <button type="button" onClick={handleContactAdmin} className="font-medium text-secondary hover:underline cursor-pointer bg-transparent border-0 p-0">Hubungi Admin</button></p>
        </div>
      </section>
    </main>
  );
}
