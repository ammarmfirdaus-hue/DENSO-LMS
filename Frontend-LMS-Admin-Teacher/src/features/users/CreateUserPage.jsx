import { useState } from 'react';
import {
  ArrowLeft,
  FileCheck2,
  GraduationCap,
  Mail,
  Presentation,
  ShieldCheck,
  UserPlus
} from 'lucide-react';
import { Link, Navigate, useNavigate, useOutletContext } from 'react-router-dom';
import { api } from '../../api/client';
import { hasPermission } from '../../utils/access-control';
import Button from '../../components/Button';
import Card from '../../components/Card';
import PageHeader from '../../components/PageHeader';
import showAlert from '../../utils/alert';

const FIELD_CLASS =
  'h-10 w-full rounded-md border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

const ROLE_OPTIONS = [
  {
    value: 'student',
    label: 'Student',
    description: 'Mengikuti course dan mengerjakan kuis.',
    Icon: GraduationCap,
    color: 'blue'
  },
  {
    value: 'teacher',
    label: 'Teacher',
    description: 'Mengelola course dan materi pembelajaran.',
    Icon: Presentation,
    color: 'emerald'
  },
  {
    value: 'admin',
    label: 'Admin',
    description: 'Mengelola pengguna dan pengaturan sistem.',
    Icon: ShieldCheck,
    color: 'red'
  },
  {
    value: 'reviewer',
    label: 'Reviewer',
    description: 'Meninjau materi sebelum diterbitkan.',
    Icon: FileCheck2,
    color: 'purple'
  }
];

const ROLE_COLOR = {
  blue: 'border-blue-300 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
  emerald: 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
  red: 'border-red-300 bg-red-50 text-red-700 dark:border-red-700 dark:bg-red-950/40 dark:text-red-300',
  purple: 'border-purple-300 bg-purple-50 text-purple-700 dark:border-purple-700 dark:bg-purple-950/40 dark:text-purple-300'
};

export default function CreateUserPage() {
  const user = useOutletContext();
  const navigate = useNavigate();
  const canManageUsers = hasPermission(user, 'users.manage');
  const [selectedRole, setSelectedRole] = useState('student');
  const [sendEmail, setSendEmail] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  if (!canManageUsers || user?.role !== 'admin') {
    return <Navigate to="/users" replace />;
  }

  async function createUser(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = Object.fromEntries(new FormData(form));
    values.role = selectedRole;
    values.sendEmail = sendEmail;
    setBusy(true);
    setError('');

    try {
      if (sendEmail) {
        await api('/jobs/users', {
          method: 'POST',
          body: JSON.stringify({
            items: [
              {
                ...values,
                action: 'upsert',
                status: 'active',
                label: `${values.fullname} (${values.npk})`
              }
            ]
          })
        });
        showAlert.success(
          'Pembuatan Akun Masuk Antrean',
          `Akun ${values.npk} akan dibuat dan kredensial dikirim ke ${values.email}.`
        );
      } else {
        await api('/users', { method: 'POST', body: JSON.stringify(values) });
        showAlert.success(
          'Pengguna Berhasil Ditambahkan',
          `Akun untuk ${values.fullname} berhasil dibuat.`
        );
      }
      navigate('/users#users-list');
    } catch (e) {
      setError(e.message || 'Terjadi kesalahan saat membuat pengguna.');
      showAlert.error('Gagal Menambahkan Pengguna', e.message || 'Silakan coba lagi.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="mx-auto w-full max-w-5xl space-y-6">
      <PageHeader
        eyebrow="Administrasi Pengguna"
        title="Tambah Pengguna Baru"
        description="Buat akun Student, Teacher, Admin, atau Reviewer dari satu halaman yang ringkas."
        actions={
          <Link
            to="/users"
            className="inline-flex h-10 items-center gap-2 rounded-lg border border-outline bg-surface-lowest px-4 text-sm font-semibold text-on-surface transition hover:border-primary/50 hover:text-primary"
          >
            <ArrowLeft className="size-4" />
            Kembali ke Pengguna
          </Link>
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

      <Card
        title="Data Akun"
        subtitle="Akun Student tersimpan ke database Student secara otomatis. Email diperlukan bila kredensial akan dikirim."
      >
        <form onSubmit={createUser} className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2">
            <label className="block text-xs font-semibold text-on-surface-variant">
              NPK *
              <input
                required
                name="npk"
                maxLength="50"
                inputMode="text"
                autoComplete="username"
                placeholder="Contoh: DLS-EMP-001"
                className={`${FIELD_CLASS} mt-1.5`}
              />
            </label>
            <label className="block text-xs font-semibold text-on-surface-variant">
              Nama lengkap *
              <input
                required
                name="fullname"
                maxLength="150"
                autoComplete="name"
                placeholder="Nama pengguna"
                className={`${FIELD_CLASS} mt-1.5`}
              />
            </label>
            <label className="block text-xs font-semibold text-on-surface-variant">
              Email pengguna *
              <input
                required
                type="email"
                name="email"
                maxLength="254"
                autoComplete="email"
                placeholder="nama@perusahaan.com"
                className={`${FIELD_CLASS} mt-1.5`}
              />
              <span className="mt-1 block text-[11px] font-normal text-muted">
                Gunakan email aktif untuk menerima informasi akun.
              </span>
            </label>
            <label className="block text-xs font-semibold text-on-surface-variant">
              Kata sandi awal *
              <input
                required
                type="password"
                name="password"
                minLength="12"
                maxLength="200"
                autoComplete="new-password"
                placeholder="Minimal 12 karakter"
                className={`${FIELD_CLASS} mt-1.5`}
              />
            </label>
          </div>

          <fieldset>
            <legend className="text-xs font-semibold text-on-surface-variant">Pilih peran *</legend>
            <div className="mt-2 grid gap-3 sm:grid-cols-2">
              {ROLE_OPTIONS.map(({ value, label, description, Icon, color }) => {
                const selected = selectedRole === value;
                return (
                  <label
                    key={value}
                    className={`flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition ${
                      selected
                        ? `${ROLE_COLOR[color]} ring-2 ring-primary/15`
                        : 'border-outline bg-surface-lowest hover:border-primary/40 dark:border-slate-700 dark:bg-slate-900'
                    }`}
                  >
                    <input
                      type="radio"
                      name="role"
                      value={value}
                      checked={selected}
                      onChange={() => setSelectedRole(value)}
                      className="sr-only"
                    />
                    <Icon className={`size-5 shrink-0 ${selected ? 'text-primary' : 'text-muted'}`} />
                    <span className="min-w-0">
                      <span className="block text-sm font-semibold text-on-surface">{label}</span>
                      <span className="mt-0.5 block text-xs font-normal text-muted">{description}</span>
                    </span>
                  </label>
                );
              })}
            </div>
          </fieldset>

          <label className="flex cursor-pointer items-start gap-3 rounded-xl border border-outline bg-surface-low p-3.5 dark:border-slate-700 dark:bg-slate-800/50">
            <input
              type="checkbox"
              name="sendEmail"
              checked={sendEmail}
              onChange={(event) => setSendEmail(event.target.checked)}
              className="mt-0.5 size-4 rounded accent-primary"
            />
            <span>
              <span className="flex items-center gap-1.5 text-sm font-semibold text-on-surface">
                <Mail className="size-4 text-primary" />
                Kirim NPK dan kata sandi awal melalui email
              </span>
              <span className="mt-1 block text-xs leading-relaxed text-muted">
                Pembuatan akun dan pengiriman email diproses melalui antrean. Pastikan SMTP sudah siap.
              </span>
            </span>
          </label>

          <div className="flex flex-col-reverse gap-2 border-t border-outline/40 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
            <Link
              to="/users"
              className="inline-flex h-10 items-center justify-center rounded-lg border border-outline px-4 text-sm font-semibold text-on-surface transition hover:border-primary/50 hover:text-primary"
            >
              Batal
            </Link>
            <Button
              type="submit"
              disabled={busy}
              loading={busy}
              icon={<UserPlus className="size-4" />}
              className="sm:min-w-48"
            >
              Tambah Pengguna
            </Button>
          </div>
        </form>
      </Card>
    </div>
  );
}
