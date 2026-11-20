import { Link } from 'react-router-dom';
import {
  HelpCircle,
  MapPin,
  Phone,
  Mail,
  ArrowRight
} from 'lucide-react';

export default function Footer() {
  return (
    <footer className="mt-16 border-t border-outline/60 bg-surface-lowest text-muted transition-colors dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-400">
      <div className="mx-auto w-full max-w-[1400px] px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid gap-8 lg:grid-cols-12">
          {/* Kolom 1: Profil Perusahaan DENSO (4 Kolom) */}
          <div className="space-y-3 lg:col-span-5">
            <div className="flex items-center gap-2.5">
              <span className="font-mono text-2xl font-black tracking-wider text-[#E60012]">
                DENSO
              </span>
              <span className="h-4 w-px bg-outline dark:bg-slate-700" />
              <span className="text-xs font-semibold uppercase tracking-wider text-on-surface dark:text-slate-200">
                Learning Management System
              </span>
            </div>

            <p className="max-w-md text-xs leading-relaxed text-muted dark:text-slate-400">
              Platform pembelajaran terpadu karyawan PT DENSO INDONESIA untuk mendukung pengembangan kompetensi teknis, keselamatan kerja, dan standar manufaktur global.
            </p>

            <div className="space-y-1.5 pt-1 text-xs text-muted dark:text-slate-400">
              <div className="flex items-center gap-4 pt-0.5 text-[11.5px]">
                <span className="flex items-center gap-1.5">
                  <Phone className="size-3.5 shrink-0 text-muted" />
                  <span>(021) 651-2279</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Mail className="size-3.5 shrink-0 text-muted" />
                  <span>lms-support@denso.co.id</span>
                </span>
              </div>
            </div>

            <div className="pt-2">
              <Link
                to="/locations"
                className="inline-flex items-center gap-1.5 rounded-lg border border-primary/30 bg-primary/5 px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary/10 dark:border-red-500/40 dark:bg-red-950/30 dark:text-red-300"
              >
                <MapPin className="size-3.5 text-red-500" />
                <span>Peta Lokasi PT DENSO (3D)</span>
                <ArrowRight className="size-3" />
              </Link>
            </div>
          </div>

          {/* Kolom 2: Menu Pembelajaran (2 Kolom) */}
          <div className="space-y-2.5 lg:col-span-2">
            <h4 className="text-xs font-bold uppercase tracking-wider text-on-surface dark:text-slate-200">
              Pembelajaran
            </h4>
            <ul className="space-y-1.5 text-xs">
              <li>
                <Link to="/" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Dashboard</Link>
              </li>
              <li>
                <Link to="/courses" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Katalog Course</Link>
              </li>
              <li>
                <Link to="/materials" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Materi &amp; Review</Link>
              </li>
              <li>
                <Link to="/quizzes" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Kelola Quiz</Link>
              </li>
              <li>
                <Link to="/grades" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Rekap Nilai</Link>
              </li>
              <li>
                <Link to="/certificates" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Sertifikat Digital</Link>
              </li>
            </ul>
          </div>

          {/* Kolom 3: Manajemen Portal (2 Kolom) */}
          <div className="space-y-2.5 lg:col-span-2">
            <h4 className="text-xs font-bold uppercase tracking-wider text-on-surface dark:text-slate-200">
              Administrasi
            </h4>
            <ul className="space-y-1.5 text-xs">
              <li>
                <Link to="/users" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Pengguna</Link>
              </li>
              <li>
                <Link to="/enrollments" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Persetujuan Course</Link>
              </li>
              <li>
                <Link to="/reviewers" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Tim Reviewer</Link>
              </li>
              <li>
                <Link to="/roles" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Hak Akses (RBAC)</Link>
              </li>
              <li>
                <Link to="/activity" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Audit Log</Link>
              </li>
            </ul>
          </div>

          {/* Kolom 4: Bantuan & Helpdesk (3 Kolom) */}
          <div className="space-y-2.5 lg:col-span-3">
            <h4 className="text-xs font-bold uppercase tracking-wider text-on-surface dark:text-slate-200">
              Bantuan &amp; Layanan
            </h4>
            <ul className="space-y-1.5 text-xs">
              <li>
                <Link to="/help" className="flex items-center gap-1.5 text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">
                  <HelpCircle className="size-3.5 text-primary" />
                  <span>Pusat Panduan LMS</span>
                </Link>
              </li>
              <li>
                <Link to="/help#faq" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">
                  Tanya Jawab (FAQ)
                </Link>
              </li>
              <li>
                <Link to="/support-chat" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">
                  Pusat Bantuan
                </Link>
              </li>
            </ul>

            <div className="pt-2 text-xs text-muted dark:text-slate-400">
              <span className="block font-semibold text-on-surface dark:text-slate-200">Layanan Helpdesk:</span>
              <span className="mt-0.5 block text-[11.5px]">Senin – Jumat: 07:30 – 16:30 WIB</span>
              <span className="mt-0.5 block text-[11px] text-muted/80 dark:text-slate-500">Dukungan operasional pelatihan</span>
            </div>
          </div>
        </div>

        {/* Bar Bagian Bawah: Hak Cipta */}
        <div className="mt-10 flex flex-col items-center justify-between gap-3 border-t border-outline/40 pt-6 text-xs text-muted sm:flex-row dark:border-slate-800/60 dark:text-slate-500">
          <p>
            &copy; {new Date().getFullYear()} <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi.
          </p>
          <div className="flex items-center gap-3">
            <Link to="/help" className="transition hover:text-primary dark:hover:text-slate-200">Panduan</Link>
            <span>&middot;</span>
            <Link to="/locations" className="transition hover:text-primary dark:hover:text-slate-200">Peta Fasilitas</Link>
            <span>&middot;</span>
            <Link to="/support-chat" className="transition hover:text-primary dark:hover:text-slate-200">Bantuan</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
