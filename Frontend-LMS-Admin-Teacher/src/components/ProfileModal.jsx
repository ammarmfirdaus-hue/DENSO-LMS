import { useState } from 'react';
import { api } from '../api/client';
import showAlert from '../utils/alert';
import { getInitialTheme, toggleTheme } from '../utils/theme';

/**
 * Modal Pengaturan Profil & Keamanan Akun Pengelola (Admin & Teacher).
 * Menyediakan form ubah data, ganti password, dan toggle tema (Gelap/Terang).
 *
 * @param {{
 *   user: { npk: string, name: string, role: string, email?: string },
 *   onClose: () => void,
 *   onUserUpdated?: (updatedUser: Object) => void
 * }} props
 */
export default function ProfileModal({ user, onClose, onUserUpdated }) {
  const [fullname, setFullname] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const [isDark, setIsDark] = useState(() => getInitialTheme() === 'dark');

  function handleToggleTheme() {
    const next = toggleTheme();
    setIsDark(next === 'dark');
  }

  async function handleSave(e) {
    e.preventDefault();

    if (newPassword) {
      if (newPassword.length < 8) {
        showAlert.warning('Kata Sandi Terlalu Pendek', 'Kata sandi baru minimal harus 8 karakter.');
        return;
      }
      if (newPassword !== confirmPassword) {
        showAlert.warning('Kata Sandi Tidak Cocok', 'Konfirmasi kata sandi baru tidak cocok.');
        return;
      }
      if (!currentPassword) {
        showAlert.warning('Kata Sandi Saat Ini Diperlukan', 'Masukkan kata sandi saat ini untuk memverifikasi penggantian kata sandi.');
        return;
      }
    }

    setBusy(true);

    try {
      const payload = {
        fullname: fullname.trim(),
        email: email.trim(),
      };
      if (newPassword) {
        payload.currentPassword = currentPassword;
        payload.newPassword = newPassword;
      }

      const res = await api('/auth/profile', {
        method: 'PUT',
        body: JSON.stringify(payload),
      });

      showAlert.success('Profil Berhasil Diperbarui', res.message || 'Perubahan informasi profil Anda telah disimpan.');
      onUserUpdated?.(res.user);
      onClose();
    } catch (err) {
      showAlert.error('Gagal Memperbarui Profil', err.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div
      role="dialog"
      aria-modal="true"
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div className="relative flex max-h-[90vh] w-full max-w-lg flex-col rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#1e293b]">
        
        {/* Header */}
        <div className="flex items-center justify-between border-b border-outline/40 px-6 py-5">
          <div className="flex items-center gap-3">
            <div className="grid size-12 place-items-center rounded-2xl bg-primary/10 text-xl font-bold text-primary">
              {fullname.charAt(0).toUpperCase() || 'U'}
            </div>
            <div>
              <h2 className="text-lg font-bold text-on-surface sm:text-xl">
                Pengaturan Profil Akun
              </h2>
              <p className="text-xs text-muted">
                NPK: <span className="font-semibold text-secondary">{user?.npk}</span> · Peran: <span className="capitalize font-semibold text-primary">{user?.role}</span>
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup modal"
            className="grid size-9 place-items-center rounded-xl text-muted transition hover:bg-surface-low hover:text-secondary cursor-pointer"
          >
            ✕
          </button>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSave} className="flex-1 overflow-y-auto p-6 space-y-5">
          
          {/* Quick Theme Toggle Strip */}
          <div className="flex items-center justify-between rounded-2xl border border-outline/50 bg-surface-low/50 p-4">
            <div className="flex items-center gap-3">
              <span className={`grid size-10 place-items-center rounded-xl ${isDark ? 'bg-amber-400/20 text-amber-300' : 'bg-primary/10 text-primary'}`}>
                <span className="material-symbol text-xl" aria-hidden="true">
                  {isDark ? 'dark_mode' : 'light_mode'}
                </span>
              </span>
              <div>
                <p className="text-sm font-bold text-on-surface">Tampilan Portal LMS</p>
                <p className="text-xs text-muted">
                  {isDark ? 'Mode Gelap (Dark Mode) aktif' : 'Mode Terang (Normal) aktif'}
                </p>
              </div>
            </div>
            <button
              type="button"
              onClick={handleToggleTheme}
              className={`relative inline-flex h-7 w-13 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                isDark ? 'bg-primary' : 'bg-zinc-300'
              }`}
            >
              <span
                className={`pointer-events-none inline-block size-6 rounded-full bg-white shadow-md transform ring-0 transition duration-200 ease-in-out ${
                  isDark ? 'translate-x-6' : 'translate-x-0'
                }`}
              />
            </button>
          </div>

          {/* Data Identitas */}
          <div className="space-y-4">
            <h3 className="text-xs font-bold uppercase tracking-wider text-muted">Informasi Akun</h3>
            
            <div>
              <label className="block text-xs font-semibold text-secondary">NPK Karyawan</label>
              <input
                type="text"
                value={user?.npk || ''}
                disabled
                className="mt-1 h-10 w-full rounded-xl border border-outline/40 bg-surface-low/70 px-3 text-xs font-semibold text-muted outline-none cursor-not-allowed"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-secondary">Nama Lengkap *</label>
              <input
                type="text"
                required
                maxLength="150"
                value={fullname}
                onChange={e => setFullname(e.target.value)}
                placeholder="Masukkan nama lengkap Anda"
                className="mt-1 h-10 w-full rounded-xl border border-outline/70 bg-white px-3 text-xs outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-secondary">Alamat Email</label>
              <input
                type="email"
                maxLength="254"
                value={email}
                onChange={e => setEmail(e.target.value)}
                placeholder="email@denso.co.id"
                className="mt-1 h-10 w-full rounded-xl border border-outline/70 bg-white px-3 text-xs outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
              />
            </div>
          </div>

          {/* Keamanan & Password */}
          <div className="space-y-4 border-t border-outline/30 pt-4">
            <div>
              <h3 className="text-xs font-bold uppercase tracking-wider text-muted">Ganti Kata Sandi</h3>
              <p className="text-[11px] text-muted">Kosongkan jika Anda tidak ingin mengubah kata sandi.</p>
            </div>

            <div>
              <label className="block text-xs font-semibold text-secondary">Kata Sandi Saat Ini</label>
              <input
                type="password"
                value={currentPassword}
                onChange={e => setCurrentPassword(e.target.value)}
                placeholder="Masukkan kata sandi lama"
                className="mt-1 h-10 w-full rounded-xl border border-outline/70 bg-white px-3 text-xs outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
              />
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
              <div>
                <label className="block text-xs font-semibold text-secondary">Kata Sandi Baru</label>
                <input
                  type="password"
                  value={newPassword}
                  onChange={e => setNewPassword(e.target.value)}
                  placeholder="Minimal 8 karakter"
                  className="mt-1 h-10 w-full rounded-xl border border-outline/70 bg-white px-3 text-xs outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-secondary">Ulangi Kata Sandi Baru</label>
                <input
                  type="password"
                  value={confirmPassword}
                  onChange={e => setConfirmPassword(e.target.value)}
                  placeholder="Ulangi kata sandi baru"
                  className="mt-1 h-10 w-full rounded-xl border border-outline/70 bg-white px-3 text-xs outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                />
              </div>
            </div>
          </div>

          {/* Actions */}
          <div className="flex items-center justify-end gap-2 border-t border-outline/40 pt-4">
            <button
              type="button"
              onClick={onClose}
              className="rounded-xl border border-outline/70 bg-white px-4 py-2 text-xs font-semibold text-secondary hover:bg-surface-low cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              disabled={busy}
              className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:brightness-110 disabled:opacity-50 cursor-pointer"
            >
              {busy ? (
                <>
                  <span className="material-symbol animate-spin text-sm" aria-hidden="true">progress_activity</span>
                  Menyimpan...
                </>
              ) : (
                'Simpan Perubahan'
              )}
            </button>
          </div>
        </form>

      </div>
    </div>
  );
}
