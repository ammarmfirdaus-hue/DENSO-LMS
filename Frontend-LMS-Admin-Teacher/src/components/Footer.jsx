import { useState, useMemo, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  HelpCircle,
  MapPin,
  Phone,
  Mail,
  ArrowRight,
  Compass,
  Globe2,
  ExternalLink,
  Layers,
  Check,
  Copy,
  Maximize2,
  Minimize2,
  Navigation
} from 'lucide-react';
import {
  DENSO_LOCATIONS,
  MAP_VIEW_MODES,
  buildGoogleMapsEmbedUrl,
  buildGoogleMapsDirectUrl
} from '../data/densoLocations';

export default function Footer() {
  const [selectedLocationId, setSelectedLocationId] = useState('sunter-plant');
  const [viewMode, setViewMode] = useState('satellite');
  const [zoomLevel, setZoomLevel] = useState(17);
  const [mapLoading, setMapLoading] = useState(false);
  const [copied, setCopied] = useState(false);
  const [isCinematic, setIsCinematic] = useState(false);

  const selectedLocation = useMemo(
    () => DENSO_LOCATIONS.find((loc) => loc.id === selectedLocationId) || DENSO_LOCATIONS[0],
    [selectedLocationId]
  );

  const embedUrl = useMemo(
    () => buildGoogleMapsEmbedUrl(selectedLocation, viewMode, zoomLevel),
    [selectedLocation, viewMode, zoomLevel]
  );

  const directUrl = useMemo(
    () => buildGoogleMapsDirectUrl(selectedLocation),
    [selectedLocation]
  );

  useEffect(() => {
    setMapLoading(true);
    const timer = setTimeout(() => setMapLoading(false), 1200);
    return () => clearTimeout(timer);
  }, [selectedLocationId, viewMode, zoomLevel]);

  function handleSelectLocation(id) {
    if (id === selectedLocationId) return;
    setMapLoading(true);
    setSelectedLocationId(id);
  }

  function handleCopyAddress() {
    navigator.clipboard.writeText(
      `${selectedLocation.name}\n${selectedLocation.address}\nKoordinat: ${selectedLocation.coordinates.lat}, ${selectedLocation.coordinates.lng}\nTelp: ${selectedLocation.phone}`
    );
    setCopied(true);
    setTimeout(() => setCopied(false), 2200);
  }

  return (
    <footer className="mt-16 border-t border-outline/60 bg-surface-lowest text-muted transition-colors dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-400">
      <div className="mx-auto w-full max-w-[1400px] px-4 py-12 sm:px-6 lg:px-8 space-y-12">

        {/* ── 3D MAPS OVERLAY WEBGL SECTION ── */}
        <section
          id="denso-footer-map"
          className="relative overflow-hidden rounded-2xl border border-slate-800 bg-[#070A11] p-1 shadow-2xl text-slate-200"
          aria-label="Peta 3D Fasilitas PT DENSO"
        >
          {/* WebGL Cockpit Corners / Viewfinder Brackets */}
          <div className="pointer-events-none absolute top-3 left-3 z-30 size-4 border-t-2 border-l-2 border-red-500/80" />
          <div className="pointer-events-none absolute top-3 right-3 z-30 size-4 border-t-2 border-r-2 border-red-500/80" />
          <div className="pointer-events-none absolute bottom-3 left-3 z-30 size-4 border-b-2 border-l-2 border-red-500/80" />
          <div className="pointer-events-none absolute bottom-3 right-3 z-30 size-4 border-b-2 border-r-2 border-red-500/80" />

          {/* Top HUD Telemetry & Location Controls */}
          <div className="relative z-20 flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/90 bg-[#0B0F19]/95 px-4 py-3 text-xs backdrop-blur-md">
            <div className="flex flex-wrap items-center gap-3">
              <span className="flex items-center gap-2">
                <span className="relative flex size-2.5">
                  <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                  <span className="relative inline-flex size-2.5 rounded-full bg-emerald-500" />
                </span>
                <span className="font-mono font-bold tracking-wider text-slate-100 uppercase">
                  DENSO GEOSPATIAL 3D
                </span>
                <span className="rounded bg-red-950/80 px-1.5 py-0.5 font-mono text-[10px] font-bold text-red-400 border border-red-800/60">
                  WEBGL PERSPECTIVE
                </span>
              </span>

              {/* Coordinates HUD Readout */}
              <span className="hidden font-mono text-[11px] text-slate-400 md:inline-flex items-center gap-1.5 border-l border-slate-800 pl-3">
                <Navigation className="size-3 text-red-400 rotate-45" />
                <span>LAT: {selectedLocation.coordinates.lat}°</span>
                <span className="text-slate-600">&middot;</span>
                <span>LNG: {selectedLocation.coordinates.lng}°</span>
                <span className="text-slate-600">&middot;</span>
                <span className="text-emerald-400">ALT: 14M</span>
              </span>
            </div>

            {/* Quick Actions (Mode switch, Zoom, Open Google Earth) */}
            <div className="flex flex-wrap items-center gap-2">
              {/* View Mode Selector */}
              <div className="flex items-center rounded-lg border border-slate-800 bg-slate-900/90 p-0.5">
                {MAP_VIEW_MODES.map((mode) => (
                  <button
                    key={mode.id}
                    type="button"
                    onClick={() => setViewMode(mode.id)}
                    className={`cursor-pointer rounded px-2 py-1 text-[11px] font-semibold transition ${
                      viewMode === mode.id
                        ? 'bg-red-600 text-white shadow-xs'
                        : 'text-slate-400 hover:text-slate-200'
                    }`}
                  >
                    {mode.id === 'satellite' ? '3D Satelit' : mode.id === 'hybrid' ? 'Hybrid' : 'Jalan'}
                  </button>
                ))}
              </div>

              {/* Zoom Buttons */}
              <div className="flex items-center rounded-lg border border-slate-800 bg-slate-900/90 p-0.5">
                <button
                  type="button"
                  onClick={() => setZoomLevel((z) => Math.min(z + 1, 20))}
                  title="Perbesar Peta (+)"
                  className="size-6 cursor-pointer rounded grid place-items-center font-mono text-xs font-bold text-slate-300 hover:bg-slate-800 hover:text-white"
                >
                  +
                </button>
                <button
                  type="button"
                  onClick={() => setZoomLevel((z) => Math.max(z - 1, 10))}
                  title="Perkecil Peta (-)"
                  className="size-6 cursor-pointer rounded grid place-items-center font-mono text-xs font-bold text-slate-300 hover:bg-slate-800 hover:text-white"
                >
                  −
                </button>
              </div>

              {/* Cinematic Expand Toggle */}
              <button
                type="button"
                onClick={() => setIsCinematic((prev) => !prev)}
                title={isCinematic ? 'Tampilan Ringkas' : 'Tampilan Penuh'}
                className="hidden sm:inline-flex size-7 cursor-pointer place-items-center justify-center rounded-lg border border-slate-800 bg-slate-900/90 text-slate-300 hover:bg-slate-800 hover:text-white"
              >
                {isCinematic ? <Minimize2 className="size-3.5" /> : <Maximize2 className="size-3.5" />}
              </button>

              {/* Direct Link to Google Maps 3D */}
              <a
                href={directUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-1.5 rounded-lg border border-red-500/40 bg-red-600/20 px-2.5 py-1 text-[11px] font-bold text-red-300 transition hover:bg-red-600 hover:text-white"
              >
                <span>Google Earth</span>
                <ExternalLink className="size-3" />
              </a>
            </div>
          </div>

          {/* Location Selector Chips Bar */}
          <div className="relative z-20 flex items-center gap-1.5 overflow-x-auto border-b border-slate-800/60 bg-[#080C14] px-4 py-2 text-xs scrollbar-none">
            <span className="shrink-0 font-mono text-[10.5px] uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1">
              <MapPin className="size-3 text-red-500" />
              <span>Lokasi Fasilitas:</span>
            </span>
            {DENSO_LOCATIONS.map((loc) => {
              const isActive = loc.id === selectedLocationId;
              return (
                <button
                  key={loc.id}
                  type="button"
                  onClick={() => handleSelectLocation(loc.id)}
                  className={`inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs ${
                    isActive
                      ? 'bg-red-600 text-white font-semibold shadow-xs'
                      : 'border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700 hover:text-slate-200'
                  }`}
                >
                  <span>{loc.shortName}</span>
                </button>
              );
            })}
          </div>

          {/* Main 3D Viewport with WebGL Reticle Overlay */}
          <div className={`relative w-full overflow-hidden bg-slate-950 transition-all duration-300 ${
            isCinematic ? 'h-[500px] sm:h-[580px]' : 'h-[360px] sm:h-[420px]'
          }`}>
            {/* Center HUD Reticle Crosshair (WebGL Atmospheric Visual) */}
            <div className="pointer-events-none absolute inset-0 z-10 grid place-items-center opacity-35">
              <div className="relative size-16 sm:size-20">
                <div className="absolute inset-0 rounded-full border border-dashed border-red-500/60" />
                <div className="absolute top-1/2 left-0 right-0 h-px bg-red-400/40" />
                <div className="absolute top-0 bottom-0 left-1/2 w-px bg-red-400/40" />
                <div className="absolute inset-4 rounded-full border border-cyan-400/40" />
              </div>
            </div>

            {/* Embedded Interactive 3D Perspective Map */}
            <iframe
              key={`${selectedLocation.id}-${viewMode}-${zoomLevel}`}
              src={embedUrl}
              title={`Peta 3D ${selectedLocation.name}`}
              className="h-full w-full border-0 bg-slate-950 filter contrast-[1.05]"
              allowFullScreen=""
              referrerPolicy="no-referrer-when-downgrade"
              onLoad={() => setMapLoading(false)}
              onError={() => setMapLoading(false)}
            />

            {/* Non-blocking Telemetry Loading Badge */}
            {mapLoading && (
              <div className="pointer-events-none absolute right-4 top-4 z-20 flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900/90 px-3 py-1.5 text-xs font-semibold text-white shadow-xl backdrop-blur-md">
                <Compass className="size-3.5 animate-spin text-red-500" />
                <span>Rendering Perspektif 3D...</span>
              </div>
            )}
          </div>

          {/* Bottom Telemetry & Facility Detail Strip */}
          <div className="relative z-20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-slate-800/90 bg-[#0B0F19]/95 px-4 py-3 text-xs">
            <div className="space-y-0.5">
              <div className="flex items-center gap-2">
                <strong className="text-sm font-bold text-white">{selectedLocation.name}</strong>
                <span className="rounded bg-slate-800 px-2 py-0.5 text-[10.5px] font-medium text-slate-300">
                  {selectedLocation.category}
                </span>
              </div>
              <p className="text-slate-400 text-[12px] max-w-3xl">
                {selectedLocation.address}
              </p>
            </div>

            <div className="flex flex-wrap items-center gap-2 shrink-0">
              <button
                type="button"
                onClick={handleCopyAddress}
                className="inline-flex items-center gap-1.5 rounded-lg border border-slate-800 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-slate-800 hover:text-white cursor-pointer"
              >
                {copied ? <Check className="size-3.5 text-emerald-400" /> : <Copy className="size-3.5 text-slate-400" />}
                <span>{copied ? 'Tersalin' : 'Salin Detail'}</span>
              </button>
            </div>
          </div>
        </section>

        {/* ── FOOTER NAVIGATION & DIRECTORY SECTION ── */}
        <div className="grid gap-8 lg:grid-cols-12 pt-4">
          {/* Kolom 1: Profil Perusahaan DENSO */}
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

            <div className="pt-1">
              <a
                href="#denso-footer-map"
                className="inline-flex items-center gap-1.5 rounded-lg border border-primary/30 bg-primary/5 px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary/10 dark:border-red-500/40 dark:bg-red-950/30 dark:text-red-300"
              >
                <MapPin className="size-3.5 text-red-500" />
                <span>Jelajahi Peta 3D Fasilitas DENSO</span>
                <ArrowRight className="size-3" />
              </a>
            </div>
          </div>

          {/* Kolom 2: Menu Pembelajaran */}
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

          {/* Kolom 3: Manajemen Portal */}
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

          {/* Kolom 4: Bantuan & Helpdesk */}
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
        <div className="flex flex-col items-center justify-between gap-3 border-t border-outline/40 pt-6 text-xs text-muted sm:flex-row dark:border-slate-800/60 dark:text-slate-500">
          <p>
            &copy; {new Date().getFullYear()} <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi.
          </p>
          <div className="flex items-center gap-3">
            <Link to="/help" className="transition hover:text-primary dark:hover:text-slate-200">Panduan</Link>
            <span>&middot;</span>
            <a href="#denso-footer-map" className="transition hover:text-primary dark:hover:text-slate-200">Peta 3D Fasilitas</a>
            <span>&middot;</span>
            <Link to="/support-chat" className="transition hover:text-primary dark:hover:text-slate-200">Bantuan</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
