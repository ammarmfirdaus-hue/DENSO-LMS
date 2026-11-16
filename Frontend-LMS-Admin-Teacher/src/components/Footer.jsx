import { Link } from 'react-router-dom';
import {
  Building2,
  ExternalLink,
  Globe2,
  HelpCircle,
  MapPin,
  Phone,
  ShieldCheck,
  Mail,
  Clock,
  ArrowRight
} from 'lucide-react';
import { DENSO_LOCATIONS } from '../data/densoLocations';

export default function Footer() {
  return (
    <footer className="mt-16 border-t border-outline/50 bg-[#10141a] text-slate-300">
      <div className="mx-auto w-full max-w-[1400px] px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid gap-8 lg:grid-cols-12">
          {/* Kolom 1: Profil Perusahaan DENSO (4 Kolom) */}
          <div className="lg:col-span-4 space-y-4">
            <div className="flex items-center gap-2.5">
              <span className="font-mono text-2xl font-black tracking-wider text-[#E60012]">
                DENSO
              </span>
              <span className="h-5 w-px bg-slate-700" />
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-300">
                Learning Management System
              </span>
            </div>

            <p className="text-xs leading-relaxed text-slate-400">
              Platform pembelajaran digital terpadu karyawan PT DENSO INDONESIA untuk mendukung pengembangan kompetensi teknis, inovasi manufaktur presisi otomotif (*Monozukuri*), dan pembinaan sumber daya manusia (*Hitozukuri*) berstandar global.
            </p>

            <div className="space-y-2 pt-1 text-xs text-slate-400">
              <div className="flex items-start gap-2">
                <MapPin className="size-4 shrink-0 text-[#E60012] mt-0.5" />
                <span>
                  <strong>Kantor Pusat:</strong> Jl. Gaya Motor I No. 6, Sunter II, Kel. Sungai Bambu, Kec. Tanjung Priok, Jakarta Utara 14330
                </span>
              </div>
              <div className="flex items-center gap-2">
                <Phone className="size-4 shrink-0 text-slate-500" />
                <span>(021) 651-2279 (Hunting)</span>
              </div>
              <div className="flex items-center gap-2">
                <Mail className="size-4 shrink-0 text-slate-500" />
                <span>lms-support@denso.co.id</span>
              </div>
            </div>

            <div className="pt-2">
              <Link
                to="/locations"
                className="inline-flex items-center gap-1.5 rounded-lg border border-red-500/40 bg-red-950/30 px-3 py-1.5 text-xs font-semibold text-red-300 transition hover:bg-red-900/50 hover:text-white"
              >
                <MapPin className="size-3.5 text-red-400" />
                <span>Peta 3D Fasilitas DENSO se-Indonesia</span>
                <ArrowRight className="size-3" />
              </Link>
            </div>
          </div>

          {/* Kolom 2: Menu Pembelajaran (2 Kolom) */}
          <div className="lg:col-span-2 space-y-3">
            <h4 className="text-xs font-bold uppercase tracking-wider text-white">
              Pembelajaran
            </h4>
            <ul className="space-y-2 text-xs text-slate-400">
              <li>
                <Link to="/" className="hover:text-white transition">Dashboard Operasional</Link>
              </li>
              <li>
                <Link to="/courses" className="hover:text-white transition">Katalog Course</Link>
              </li>
              <li>
                <Link to="/materials" className="hover:text-white transition">Materi &amp; Persetujuan</Link>
              </li>
              <li>
                <Link to="/quizzes" className="hover:text-white transition">Evaluasi &amp; Kuis</Link>
              </li>
              <li>
                <Link to="/grades" className="hover:text-white transition">Rekapitulasi Nilai</Link>
              </li>
              <li>
                <Link to="/certificates" className="hover:text-white transition">Sertifikat Digital</Link>
              </li>
            </ul>
          </div>

          {/* Kolom 3: Manajemen & Sistem (3 Kolom) */}
          <div className="lg:col-span-3 space-y-3">
            <h4 className="text-xs font-bold uppercase tracking-wider text-white">
              Administrasi Portal
            </h4>
            <ul className="space-y-2 text-xs text-slate-400">
              <li>
                <Link to="/users" className="hover:text-white transition">Direktori Pengguna</Link>
              </li>
              <li>
                <Link to="/enrollments" className="hover:text-white transition">Persetujuan Course</Link>
              </li>
              <li>
                <Link to="/reviewers" className="hover:text-white transition">Kelola Reviewer</Link>
              </li>
              <li>
                <Link to="/roles" className="hover:text-white transition">Role &amp; Hak Akses (RBAC)</Link>
              </li>
              <li>
                <Link to="/activity" className="hover:text-white transition">Audit Log &amp; Antrean</Link>
              </li>
              <li>
                <Link to="/settings/backup" className="hover:text-white transition">Pencadangan Data</Link>
              </li>
            </ul>
          </div>

          {/* Kolom 4: Jaringan Fasilitas DENSO & Bantuan (3 Kolom) */}
          <div className="lg:col-span-3 space-y-3">
            <h4 className="text-xs font-bold uppercase tracking-wider text-white">
              Bantuan &amp; Fasilitas
            </h4>
            <ul className="space-y-2 text-xs text-slate-400">
              <li>
                <Link to="/help" className="hover:text-white transition flex items-center gap-1.5">
                  <HelpCircle className="size-3.5 text-primary" />
                  <span>Pusat Panduan LMS</span>
                </Link>
              </li>
              <li>
                <Link to="/help#faq" className="hover:text-white transition">
                  Tanya Jawab (FAQ)
                </Link>
              </li>
              <li>
                <Link to="/locations" className="hover:text-white transition flex items-center gap-1.5">
                  <MapPin className="size-3.5 text-[#E60012]" />
                  <span>Peta Lokasi PT DENSO (3D)</span>
                </Link>
              </li>
              <li>
                <Link to="/support-chat" className="hover:text-white transition">
                  Pusat Bantuan (Live Chat)
                </Link>
              </li>
            </ul>

            <div className="rounded-lg border border-slate-800 bg-slate-900/60 p-3 text-xs text-slate-400 mt-3">
              <span className="block font-semibold text-slate-200">Jam Layanan Helpdesk:</span>
              <span className="mt-0.5 block text-[11px]">Senin – Jumat: 07:30 – 16:30 WIB</span>
              <span className="mt-0.5 block text-[11px] text-slate-500">Kecuali hari libur nasional</span>
            </div>
          </div>
        </div>

        {/* Bar Bagian Bawah: Hak Cipta & Standar Kepatuhan Keamanan */}
        <div className="mt-10 border-t border-slate-800/80 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <p>
            &copy; 2026 <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi Undang-Undang.
          </p>
          <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
            <span className="flex items-center gap-1">
              <ShieldCheck className="size-3.5 text-emerald-400" />
              <span>TLS 1.3 &amp; AES-256-GCM Encrypted</span>
            </span>
            <span aria-hidden="true">&middot;</span>
            <span>RBAC 31 Granular Permissions</span>
            <span aria-hidden="true">&middot;</span>
            <span className="font-mono">v2.4.0 Enterprise</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
