import { useState, useMemo, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  HelpCircle,
  MapPin,
  Phone,
  Mail,
  ExternalLink,
  LocateFixed,
  Loader2
} from 'lucide-react';

const DEFAULT_COORDS = {
  lat: -6.2088,
  lng: 106.8456,
  label: 'Indonesia'
};

export default function Footer() {
  const [userCoords, setUserCoords] = useState(DEFAULT_COORDS);
  const [isDetected, setIsDetected] = useState(false);
  const [locating, setLocating] = useState(false);
  const [zoomLevel, setZoomLevel] = useState(14);

  function detectLocation() {
    if (!navigator.geolocation) return;
    setLocating(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        setUserCoords({
          lat: Number(pos.coords.latitude.toFixed(6)),
          lng: Number(pos.coords.longitude.toFixed(6)),
          label: 'Lokasi Anda'
        });
        setIsDetected(true);
        setLocating(false);
      },
      (err) => {
        console.warn('Geolocation:', err.message);
        setLocating(false);
      },
      { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
    );
  }

  useEffect(() => {
    detectLocation();
  }, []);

  const embedUrl = useMemo(() => {
    return `https://maps.google.com/maps?q=${userCoords.lat},${userCoords.lng}&hl=id&z=${zoomLevel}&ie=UTF8&iwloc=&output=embed`;
  }, [userCoords.lat, userCoords.lng, zoomLevel]);

  const directUrl = useMemo(() => {
    return `https://www.google.com/maps/search/?api=1&query=${userCoords.lat},${userCoords.lng}`;
  }, [userCoords.lat, userCoords.lng]);

  return (
    <footer className="mt-16 border-t border-outline/60 bg-surface-lowest text-muted transition-colors dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-400">
      <div className="mx-auto w-full max-w-[1400px] px-4 py-10 sm:px-6 lg:px-8 space-y-8">
        <div className="grid gap-8 lg:grid-cols-12">
          {/* Kolom 1: Profil Perusahaan DENSO (4 Kolom) */}
          <div className="space-y-3 lg:col-span-4">
            <div className="flex items-center gap-2.5">
              <span className="font-mono text-2xl font-black tracking-wider text-[#E60012]">
                DENSO
              </span>
              <span className="h-4 w-px bg-outline dark:bg-slate-700" />
              <span className="text-xs font-semibold uppercase tracking-wider text-on-surface dark:text-slate-200">
                Learning Portal
              </span>
            </div>

            <p className="max-w-sm text-xs leading-relaxed text-muted dark:text-slate-400">
              Platform pembelajaran digital karyawan PT DENSO INDONESIA untuk pengembangan kompetensi teknis dan standar industri manufaktur.
            </p>

            <div className="space-y-1.5 pt-1 text-xs text-muted dark:text-slate-400">
              <div className="flex items-center gap-4 text-[11.5px]">
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
          </div>

          {/* Kolom 2: Menu Pembelajaran (2 Kolom) */}
          <div className="space-y-2 lg:col-span-2">
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
                <Link to="/grades" className="text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">Rekap Nilai</Link>
              </li>
            </ul>
          </div>

          {/* Kolom 3: Layanan Bantuan (2 Kolom) */}
          <div className="space-y-2 lg:col-span-2">
            <h4 className="text-xs font-bold uppercase tracking-wider text-on-surface dark:text-slate-200">
              Bantuan
            </h4>
            <ul className="space-y-1.5 text-xs">
              <li>
                <Link to="/help" className="flex items-center gap-1.5 text-muted transition hover:text-primary dark:text-slate-400 dark:hover:text-white">
                  <HelpCircle className="size-3.5 text-primary" />
                  <span>Pusat Panduan</span>
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
          </div>

          {/* Kolom 4: Peta Lokasi Pengguna (4 Kolom) - Simpel & Profesional */}
          <div className="lg:col-span-4">
            <div className="rounded-xl border border-outline/60 bg-surface-low/50 p-3 shadow-xs dark:border-slate-800 dark:bg-slate-900/40">
              <div className="flex items-center justify-between pb-2 text-xs">
                <span className="flex items-center gap-1.5 font-semibold text-on-surface dark:text-slate-200">
                  <MapPin className="size-3.5 text-primary" />
                  <span>{isDetected ? 'Lokasi Anda Saat Ini' : 'Peta Lokasi'}</span>
                </span>
                <div className="flex items-center gap-1.5">
                  <button
                    type="button"
                    onClick={detectLocation}
                    disabled={locating}
                    title="Lacak ulang lokasi saya"
                    className="inline-flex items-center gap-1 rounded px-2 py-0.5 text-[11px] font-medium text-primary hover:bg-primary/10 transition cursor-pointer disabled:opacity-50"
                  >
                    {locating ? <Loader2 className="size-3 animate-spin" /> : <LocateFixed className="size-3" />}
                    <span>{locating ? 'Melacak...' : 'Lacak Saya'}</span>
                  </button>
                  <a
                    href={directUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    title="Buka di Google Maps"
                    className="text-muted hover:text-on-surface transition p-0.5"
                  >
                    <ExternalLink className="size-3.5" />
                  </a>
                </div>
              </div>

              {/* Viewport Peta Simpel */}
              <div className="relative h-44 w-full overflow-hidden rounded-lg bg-surface-container border border-outline/30 dark:border-slate-800">
                <iframe
                  key={`${userCoords.lat}-${userCoords.lng}-${zoomLevel}`}
                  src={embedUrl}
                  title="Peta Lokasi Pengguna"
                  className="h-full w-full border-0"
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                />
              </div>

              <div className="mt-2 flex items-center justify-between text-[11px] text-muted">
                <span>{isDetected ? 'Koordinat terdeteksi dari browser' : 'Klik "Lacak Saya" untuk titik presisi'}</span>
                <div className="flex items-center gap-1">
                  <button
                    type="button"
                    onClick={() => setZoomLevel((z) => Math.min(z + 1, 19))}
                    className="size-5 rounded border border-outline/50 grid place-items-center font-mono text-[10px] hover:bg-surface-high cursor-pointer"
                    title="Perbesar"
                  >
                    +
                  </button>
                  <button
                    type="button"
                    onClick={() => setZoomLevel((z) => Math.max(z - 1, 10))}
                    className="size-5 rounded border border-outline/50 grid place-items-center font-mono text-[10px] hover:bg-surface-high cursor-pointer"
                    title="Perkecil"
                  >
                    −
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Bar Bagian Bawah: Hak Cipta */}
        <div className="flex flex-col items-center justify-between gap-3 border-t border-outline/40 pt-5 text-xs text-muted sm:flex-row dark:border-slate-800/60 dark:text-slate-500">
          <p>
            &copy; {new Date().getFullYear()} <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi.
          </p>
          <div className="flex items-center gap-3">
            <Link to="/help" className="transition hover:text-primary dark:hover:text-slate-200">Panduan</Link>
            <span>&middot;</span>
            <Link to="/support-chat" className="transition hover:text-primary dark:hover:text-slate-200">Bantuan</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
