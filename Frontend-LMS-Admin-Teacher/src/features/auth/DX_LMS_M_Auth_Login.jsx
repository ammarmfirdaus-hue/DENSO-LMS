import { useState } from 'react';
import { Loader2 } from 'lucide-react';
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
      const msg = loginError.message || 'NPK/email atau kata sandi salah. Silakan periksa kembali kredensial Anda.';
      setError(msg);
      showAlert.error('Gagal Masuk', msg);
    } finally {
      setBusy(false);
    }
  }

  function handleForgotPassword(e) {
    e.preventDefault();
    showAlert.info(
      'Reset Kata Sandi',
      'Untuk melakukan reset kata sandi, silakan hubungi Administrator LMS atau HRD DENSO.'
    );
  }

  function handleContactAdmin(e) {
    e.preventDefault();
    showAlert.info(
      'Bantuan Akses LMS',
      'Pembuatan akun dan hak akses dikelola terpusat oleh Admin LMS DENSO. Hubungi tim LMS untuk bantuan aktivasi.'
    );
  }

  return (
    <div className="min-h-[100dvh] flex flex-col lg:flex-row bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased w-full">
      {/* Left Image Panel / Branding Area (Identical to Native LMS Student) */}
      <div className="hidden lg:flex flex-col w-full lg:flex-1 bg-[#0b0e14] relative overflow-hidden min-h-[600px] lg:min-h-[100dvh]">
        {/* Background Image */}
        <div
          className="absolute inset-0 bg-cover bg-left bg-no-repeat z-0 opacity-100"
          style={{ backgroundImage: `url('/assets/img/login/DX_LMS_M_Login.png')` }}
        />

        {/* Premium Dark Overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-[#0b0e14]/95 from-[20%] via-[#0b0e14]/70 to-[#0b0e14]/10 z-10" />

        {/* Content */}
        <div className="relative z-20 flex flex-col justify-end h-full p-8 md:p-12 lg:p-16 lg:px-20 w-full max-w-2xl mx-auto lg:mx-0 lg:ml-auto xl:ml-0 pb-10 lg:pb-12">
          <div className="mt-auto mb-6">
            {/* Title & Logo (Side by side) */}
            <div className="flex items-center gap-4 mb-6">
              {/* Logo */}
              <div className="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white p-1.5 shadow-lg">
                <img
                  src="/assets/img/icon/denso-vector-logo.png"
                  alt="Logo DENSO"
                  className="size-full object-contain"
                />
              </div>
              {/* Title */}
              <h2 className="text-2xl lg:text-3xl font-bold text-white tracking-tight leading-tight">
                Learning Management System
              </h2>
            </div>

            {/* Description (Justified) */}
            <p className="text-[14px] lg:text-[15px] text-gray-200 leading-relaxed max-w-[460px] text-justify mb-10">
              Platform pembelajaran digital terintegrasi untuk meningkatkan kompetensi, produktivitas, dan standar kualitas operasional secara berkelanjutan.
            </p>

            {/* Highlights Grid with Dividers */}
            <div className="flex flex-row items-stretch max-w-[460px] gap-0 divide-x divide-white/20">
              <div className="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left pr-4">
                <span className="material-symbol text-[#DC2626] text-[28px] mb-2 drop-shadow-md">
                  menu_book
                </span>
                <span className="text-[11px] lg:text-[12px] font-semibold text-white/90">
                  Learning<br />Path
                </span>
              </div>
              <div className="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left px-4">
                <span className="material-symbol text-[#DC2626] text-[28px] mb-2 drop-shadow-md">
                  assignment_turned_in
                </span>
                <span className="text-[11px] lg:text-[12px] font-semibold text-white/90">
                  Assessment
                </span>
              </div>
              <div className="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left px-4">
                <span className="material-symbol text-[#DC2626] text-[28px] mb-2 drop-shadow-md">
                  insights
                </span>
                <span className="text-[11px] lg:text-[12px] font-semibold text-white/90">
                  Progress<br />Tracking
                </span>
              </div>
              <div className="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left pl-4">
                <span className="material-symbol text-[#DC2626] text-[28px] mb-2 drop-shadow-md">
                  workspace_premium
                </span>
                <span className="text-[11px] lg:text-[12px] font-semibold text-white/90">
                  Certification
                </span>
              </div>
            </div>
          </div>

          {/* Footer */}
          <div>
            <div className="w-12 h-[1px] bg-white/30 mb-4" />
            <p className="text-[11px] lg:text-xs text-gray-300 font-medium">© Learning Management System</p>
          </div>
        </div>
      </div>

      {/* Right Login Form (FoodDash Layout & Typography matching Native) */}
      <div className="flex-1 w-full flex flex-col justify-center items-center p-8 bg-white dark:bg-slate-950 py-16 lg:py-8 transition-colors">
        {/* Logo */}
        <div className="text-center mb-8">
          <img
            src="/assets/img/icon/denso-vector-logo.png"
            alt="Logo DENSO"
            className="mx-auto mb-3 size-20 rounded-2xl bg-white object-contain shadow-sm ring-1 ring-black/5 p-2"
          />
          <div className="font-sans text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-[0.15em] mt-1">
            LEARNING MANAGEMENT SYSTEM
          </div>
        </div>

        {/* Heading */}
        <div className="text-center mb-8 w-full max-w-sm">
          <h1 className="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
            Selamat datang!
          </h1>
          <p className="text-[14px] text-slate-600 dark:text-slate-400 mt-1">
            Gunakan NPK atau email dan kata sandi akun DENSO Anda
          </p>
        </div>

        {/* Login Form */}
        <form className="w-full max-w-sm space-y-4" onSubmit={submit}>
          {error && (
            <div className="flex items-center gap-[12px] rounded-[12px] bg-[#FEE2E2] dark:bg-red-950/40 p-[12px] text-[#EF4444] dark:text-red-400 border border-red-200 dark:border-red-900/50">
              <span className="material-symbol shrink-0 text-[20px]">error</span>
              <span className="text-[13px] font-medium leading-[18px]">{error}</span>
            </div>
          )}

          {/* NPK / Email Input */}
          <div className="w-full">
            <label htmlFor="login-npk" className="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              NPK atau Email
            </label>
            <div className="relative w-full">
              <span className="material-symbol absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[20px] pointer-events-none">
                person
              </span>
              <input
                id="login-npk"
                type="text"
                required
                autoComplete="username"
                value={npk}
                onChange={event => setNpk(event.target.value)}
                placeholder="Contoh: 123456 atau nama@denso.co.id"
                className="w-full h-[46px] pl-11 pr-4 rounded-[12px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-[14px] text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 shadow-[0_1px_3px_rgba(0,0,0,0.08)] outline-none focus:border-[#DC2626] focus:ring-2 focus:ring-[#DC2626]/20 transition"
              />
            </div>
          </div>

          {/* Password Input */}
          <div className="w-full">
            <label htmlFor="login-password" className="block text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Kata Sandi
            </label>
            <div className="relative w-full">
              <span className="material-symbol absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[20px] pointer-events-none">
                lock
              </span>
              <input
                id="login-password"
                type={showPassword ? 'text' : 'password'}
                required
                autoComplete="current-password"
                value={password}
                onChange={event => setPassword(event.target.value)}
                placeholder="Masukkan kata sandi"
                className="w-full h-[46px] pl-11 pr-12 rounded-[12px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-[14px] text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 shadow-[0_1px_3px_rgba(0,0,0,0.08)] outline-none focus:border-[#DC2626] focus:ring-2 focus:ring-[#DC2626]/20 transition"
              />
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => setShowPassword(val => !val)}
                className="absolute right-2.5 top-1/2 -translate-y-1/2 inline-flex size-8 items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors cursor-pointer"
                aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                aria-pressed={showPassword}
              >
                <span className="material-symbol text-[20px] leading-none">
                  {showPassword ? 'visibility' : 'visibility_off'}
                </span>
              </button>
            </div>
          </div>

          {/* Forgot Password Link */}
          <div className="text-left">
            <button
              type="button"
              data-dls-action-ignore="true"
              onClick={handleForgotPassword}
              className="text-[12px] leading-[18px] text-slate-500 dark:text-slate-400 hover:text-[#DC2626] dark:hover:text-red-400 transition cursor-pointer"
            >
              Perlu reset password? Hubungi Admin LMS.
            </button>
          </div>

          {/* Submit Button (FoodDash Pill LG CTA) */}
          <button
            type="submit"
            data-dls-action-ignore="true"
            disabled={busy}
            className="w-full h-[50px] rounded-full bg-[#DC2626] hover:bg-[#B91C1C] text-[16px] font-semibold text-white shadow-[0_1px_3px_rgba(0,0,0,0.08)] flex items-center justify-center gap-2 cursor-pointer transition active:scale-[0.99] disabled:opacity-60 disabled:cursor-wait"
          >
            {busy ? (
              <>
                <Loader2 className="h-5 w-5 animate-spin" aria-hidden="true" />
                <span>Memproses...</span>
              </>
            ) : (
              <span>Masuk ke Portal</span>
            )}
          </button>
        </form>

        {/* Footer */}
        <div className="mt-[24px] text-center">
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={handleContactAdmin}
            className="text-[13px] leading-[18px] text-slate-600 dark:text-slate-400 hover:text-[#DC2626] dark:hover:text-red-400 transition cursor-pointer"
          >
            Belum punya akun? Hubungi Admin LMS untuk pembuatan akses.
          </button>
        </div>
      </div>
    </div>
  );
}

