import { useState } from 'react';
import { Moon, Sun, X, Loader2 } from 'lucide-react';
import { api } from '../api/client';
import showAlert from '../utils/alert';
import { getInitialTheme, toggleTheme } from '../utils/theme';

/**
 * Modal Pengaturan Profil & Keamanan Akun Pengelola (Admin & Teacher).
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
      const payload = { fullname: fullname.trim(), email: email.trim() };
      if (newPassword) {
        payload.currentPassword = currentPassword;
        payload.newPassword = newPassword;
      }

      const res = await api('/auth/profile', {
        method: 'PUT',
        body: JSON.stringify(payload)
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
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
      onClick={e => { if (e.target === e.currentTarget) onClose?.(); }}
    >
      <div className="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-xl border border-outline bg-white shadow-[0_20px_25px_rgba(0,0,0,0.16)]">
        <div className="flex items-center justify-between border-b border-outline px-6 py-4">
          <div className="flex items-center gap-3">
            <div className="grid size-11 place-items-center rounded-lg bg-primary/10 text-lg font-bold text-primary">
              {fullname.charAt(0).toUpperCase() || 'U'}
            </div>
            <div>
              <h2 className="text-lg font-bold text-on-surface">
                Pengaturan Profil Akun
              </h2>
              <p className="text-xs text-muted">
                NPK: <span className="font-mono font-semibold text-on-surface">{user?.npk}</span> · Peran:{' '}
                <span className="font-semibold capitalize text-primary">{user?.role}</span>
              </p>
            </div>
          </div>
          <button
            type="button"
            data-dls-action-ignore="true"
            onClick={onClose}
            aria-label="Tutup modal"
            className="grid size-9 cursor-pointer place-items-center rounded-lg border border-outline text-muted transition hover:bg-surface-low hover:text-on-surface"
          >
            <X className="h-4 w-4" aria-hidden="true" />
          </button>
        </div>

        <form onSubmit={handleSave} className="flex-1 space-y-5 overflow-y-auto p-6">
          <div className="flex items-center justify-between rounded-lg border border-outline bg-surface-low/70 p-4">
            <div className="flex items-center gap-3">
              <span className={`grid size-10 place-items-center rounded-lg ${isDark ? 'bg-amber-400/20 text-amber-300' : 'bg-primary/10 text-primary'}`}>
                {isDark ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
              </span>
              <div>
                <p className="text-sm font-semibold text-on-surface">Tema Tampilan Workspace</p>
                <p className="text-xs text-muted">
                  {isDark ? 'Mode Gelap aktif untuk kenyamanan malam' : 'Mode Terang hangat aktif (Default)'}
                </p>
              </div>
            </div>
            <button
              type="button"
              data-dls-action-ignore="true"
              onClick={handleToggleTheme}
              aria-label="Ganti Tema"
              className={`relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ${
                isDark ? 'bg-primary' : 'bg-slate-300'
              }`}
            >
              <span
                className={`pointer-events-none inline-block size-5 transform rounded-full bg-white shadow transition duration-200 ${
                  isDark ? 'translate-x-5' : 'translate-x-0'
                }`}
              />
            </button>
          </div>

          <div className="space-y-4">
            <h3 className="text-xs font-semibold uppercase tracking-[0.05em] text-muted">Informasi Akun</h3>

            <div>
              <label className="block text-xs font-semibold text-on-surface">NPK Karyawan</label>
              <input
                type="text"
                value={user?.npk || ''}
                disabled
                className="mt-1.5 h-10 w-full cursor-not-allowed rounded-md border border-outline bg-surface-low px-3 font-mono text-sm font-medium text-muted outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-on-surface">Nama Lengkap *</label>
              <input
                type="text"
                required
                maxLength="150"
                value={fullname}
                onChange={e => setFullname(e.target.value)}
                placeholder="Masukkan nama lengkap Anda"
                className="mt-1.5 h-10 w-full rounded-md border border-outline bg-white px-3 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-on-surface">Alamat Email</label>
              <input
                type="email"
                maxLength="254"
                value={email}
                onChange={e => setEmail(e.target.value)}
                placeholder="email@denso.co.id"
                className="mt-1.5 h-10 w-full rounded-md border border-outline bg-white px-3 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
              />
            </div>
          </div>

          <div className="space-y-4 border-t border-outline pt-4">
            <div>
              <h3 className="text-xs font-semibold uppercase tracking-[0.05em] text-muted">Ganti Kata Sandi</h3>
              <p className="text-xs text-muted">Kosongkan jika tidak ingin mengubah kata sandi saat ini.</p>
            </div>

            <div>
              <label className="block text-xs font-semibold text-on-surface">Kata Sandi Saat Ini</label>
              <input
                type="password"
                value={currentPassword}
                onChange={e => setCurrentPassword(e.target.value)}
                placeholder="Masukkan kata sandi lama"
                className="mt-1.5 h-10 w-full rounded-md border border-outline bg-white px-3 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
              />
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
              <div>
                <label className="block text-xs font-semibold text-on-surface">Kata Sandi Baru</label>
                <input
                  type="password"
                  value={newPassword}
                  onChange={e => setNewPassword(e.target.value)}
                  placeholder="Minimal 8 karakter"
                  className="mt-1.5 h-10 w-full rounded-md border border-outline bg-white px-3 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-on-surface">Ulangi Kata Sandi Baru</label>
                <input
                  type="password"
                  value={confirmPassword}
                  onChange={e => setConfirmPassword(e.target.value)}
                  placeholder="Ulangi kata sandi baru"
                  className="mt-1.5 h-10 w-full rounded-md border border-outline bg-white px-3 text-sm text-on-surface outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
                />
              </div>
            </div>
          </div>

          <div className="flex items-center justify-end gap-2.5 border-t border-outline pt-4">
            <button
              type="button"
              data-dls-action-ignore="true"
              onClick={onClose}
              className="h-10 cursor-pointer rounded-lg border border-outline-strong bg-white px-4 text-sm font-medium text-on-surface hover:bg-surface-low"
            >
              Batal
            </button>
            <button
              type="submit"
              data-dls-action-ignore="true"
              disabled={busy}
              className="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary-container disabled:opacity-50"
            >
              {busy ? (
                <>
                  <Loader2 className="h-4 w-4 animate-spin" aria-hidden="true" />
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
