import { useEffect, useMemo, useState } from 'react';
import {
  Building2,
  Compass,
  ExternalLink,
  Globe2,
  Layers,
  MapPin,
  Maximize2,
  Navigation,
  Phone,
  Clock,
  Search,
  Check,
  Copy,
  ChevronRight,
  ShieldCheck,
  Factory
} from 'lucide-react';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import showAlert from '../../utils/alert';
import {
  DENSO_LOCATIONS,
  MAP_VIEW_MODES,
  buildGoogleMapsEmbedUrl,
  buildGoogleMapsDirectUrl
} from '../../data/densoLocations';

export default function DensoLocationsPage() {
  const [selectedId, setSelectedId] = useState('sunter-plant');
  const [viewMode, setViewMode] = useState('satellite');
  const [zoomLevel, setZoomLevel] = useState(17);
  const [provinceFilter, setProvinceFilter] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [copied, setCopied] = useState(false);
  const [mapLoading, setMapLoading] = useState(false);

  const selectedLocation = useMemo(
    () => DENSO_LOCATIONS.find((loc) => loc.id === selectedId) || DENSO_LOCATIONS[0],
    [selectedId]
  );

  const provinces = useMemo(() => {
    return ['all', ...new Set(DENSO_LOCATIONS.map((loc) => loc.province))];
  }, []);

  const filteredLocations = useMemo(() => {
    return DENSO_LOCATIONS.filter((loc) => {
      if (provinceFilter !== 'all' && loc.province !== provinceFilter) return false;
      if (!searchQuery.trim()) return true;
      const q = searchQuery.toLowerCase();
      return (
        loc.name.toLowerCase().includes(q) ||
        loc.city.toLowerCase().includes(q) ||
        loc.province.toLowerCase().includes(q) ||
        loc.address.toLowerCase().includes(q) ||
        loc.category.toLowerCase().includes(q)
      );
    });
  }, [provinceFilter, searchQuery]);

  const embedUrl = useMemo(() => {
    return buildGoogleMapsEmbedUrl(selectedLocation, viewMode, zoomLevel);
  }, [selectedLocation, viewMode, zoomLevel]);

  const directUrl = useMemo(() => {
    return buildGoogleMapsDirectUrl(selectedLocation);
  }, [selectedLocation]);

  useEffect(() => {
    setMapLoading(true);
    const timer = setTimeout(() => {
      setMapLoading(false);
    }, 1200);
    return () => clearTimeout(timer);
  }, [selectedId, viewMode, zoomLevel]);

  function handleSelectLocation(id) {
    if (id === selectedId) return;
    setMapLoading(true);
    setSelectedId(id);
  }

  function handleCopyAddress() {
    navigator.clipboard.writeText(`${selectedLocation.name}\n${selectedLocation.address}\nTelp: ${selectedLocation.phone}`);
    setCopied(true);
    showAlert.success('Alamat Berhasil Disalin', `Detail lokasi ${selectedLocation.shortName} siap ditempel.`);
    setTimeout(() => setCopied(false), 2500);
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="JARINGAN FASILITAS RESMI"
        title="Peta Lokasi PT DENSO di Seluruh Indonesia"
        description="Jelajahi kantor pusat, pabrik manufaktur (Plant Sunter, Bekasi, Fajar), dan technical hub pelatihan DENSO di seluruh Indonesia melalui visualisasi 3D Satelit interaktif."
        actions={
          <div className="flex flex-wrap items-center gap-2.5">
            <a href={directUrl} target="_blank" rel="noopener noreferrer">
              <Button variant="primary" icon={<Navigation className="size-4" />}>
                Petunjuk Arah (Google Maps)
              </Button>
            </a>
          </div>
        }
      />

      {/* Kontrol Filter & Mode Peta */}
      <div className="dls-card-surface flex flex-col gap-4 rounded-xl p-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex flex-wrap items-center gap-2">
          <span className="text-xs font-semibold text-muted">Wilayah:</span>
          <div className="flex flex-wrap gap-1">
            {provinces.map((prov) => (
              <button
                key={prov}
                type="button"
                onClick={() => setProvinceFilter(prov)}
                className={`cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold transition ${
                  provinceFilter === prov
                    ? 'bg-primary text-white shadow-xs'
                    : 'bg-surface-low text-on-surface-variant hover:bg-surface-container dark:bg-slate-800'
                }`}
              >
                {prov === 'all' ? 'Semua Wilayah' : prov}
              </button>
            ))}
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <span className="text-xs font-semibold text-muted">Tampilan:</span>
          <div className="inline-flex rounded-lg border border-outline/50 bg-surface-low p-0.5 dark:border-slate-700 dark:bg-slate-800">
            {MAP_VIEW_MODES.map((mode) => (
              <button
                key={mode.id}
                type="button"
                onClick={() => {
                  setMapLoading(true);
                  setViewMode(mode.id);
                }}
                className={`cursor-pointer rounded-md px-3 py-1.5 text-xs font-semibold transition ${
                  viewMode === mode.id
                    ? 'bg-white text-on-surface shadow-2xs dark:bg-slate-700 dark:text-white'
                    : 'text-muted hover:text-on-surface'
                }`}
                title={mode.hint}
              >
                {mode.label}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Grid Utama: Frame Peta 3D & Detail Fasilitas Terpilih */}
      <div className="grid gap-6 lg:grid-cols-12">
        {/* Frame Peta 3D (8 Kolom) */}
        <section className="lg:col-span-8 flex flex-col space-y-3">
          <div className="dls-card-surface relative flex h-[480px] w-full flex-col overflow-hidden rounded-2xl border border-outline/60 shadow-lg sm:h-[540px]">
            {/* Header Mini Peta */}
            <div className="absolute left-3 top-3 z-10 flex max-w-[calc(100%-1.5rem)] flex-wrap items-center gap-2 rounded-xl bg-slate-900/85 px-3 py-2 text-white shadow-md backdrop-blur-md">
              <span className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-red-600 text-white">
                <Factory className="size-4" />
              </span>
              <div className="min-w-0 flex-1">
                <p className="truncate text-xs font-bold">{selectedLocation.shortName}</p>
                <p className="truncate text-[10px] text-slate-300">
                  {selectedLocation.city}, {selectedLocation.province} · {selectedLocation.coordinates.lat},{' '}
                  {selectedLocation.coordinates.lng}
                </p>
              </div>
            </div>

            {/* Kontrol Zoom Cepat */}
            <div className="absolute bottom-4 right-4 z-10 flex flex-col gap-1 rounded-lg bg-slate-900/80 p-1 shadow-md backdrop-blur-md">
              <button
                type="button"
                onClick={() => {
                  setMapLoading(true);
                  setZoomLevel((z) => Math.min(z + 1, 20));
                }}
                title="Perbesar Peta"
                className="grid size-8 cursor-pointer place-items-center rounded bg-slate-800 text-xs font-bold text-white hover:bg-slate-700"
              >
                +
              </button>
              <button
                type="button"
                onClick={() => {
                  setMapLoading(true);
                  setZoomLevel((z) => Math.max(z - 1, 10));
                }}
                title="Perkecil Peta"
                className="grid size-8 cursor-pointer place-items-center rounded bg-slate-800 text-xs font-bold text-white hover:bg-slate-700"
              >
                −
              </button>
            </div>

            {/* Iframe Peta Google Maps Embed */}
            <iframe
              key={`${selectedLocation.id}-${viewMode}-${zoomLevel}`}
              src={embedUrl}
              title={`Peta 3D ${selectedLocation.name}`}
              className="h-full w-full border-0 bg-slate-900"
              allowFullScreen=""
              referrerPolicy="no-referrer-when-downgrade"
              onLoad={() => setMapLoading(false)}
              onError={() => setMapLoading(false)}
            />

            {/* Floating Status Loading (non-blocking) */}
            {mapLoading && (
              <div className="pointer-events-none absolute right-4 top-16 z-20 flex items-center gap-2 rounded-xl bg-slate-900/90 px-3.5 py-2 text-xs font-semibold text-white shadow-xl backdrop-blur-md transition-opacity">
                <Compass className="size-4 animate-spin text-red-500" />
                <span>Memuat perspektif 3D...</span>
              </div>
            )}
          </div>

          <div className="flex flex-wrap items-center justify-between gap-2 px-1 text-xs text-muted">
            <span className="flex items-center gap-1.5">
              <Globe2 className="size-3.5 text-primary" />
              Navigasi citra 3D satelit berlisensi Google Maps Platform.
            </span>
            <a
              href={directUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1 font-semibold text-primary hover:underline"
            >
              <span>Buka Tampilan Penuh di Google Maps</span>
              <ExternalLink className="size-3" />
            </a>
          </div>
        </section>

        {/* Panel Detail Fasilitas Terpilih (4 Kolom) */}
        <section className="lg:col-span-4 flex flex-col space-y-4">
          <div className="dls-card-surface flex h-full flex-col rounded-2xl p-5 shadow-sm">
            <div className="flex items-start justify-between gap-3">
              <span className="rounded-md bg-primary/10 px-2.5 py-1 text-xs font-bold text-primary">
                {selectedLocation.category}
              </span>
              <button
                type="button"
                onClick={handleCopyAddress}
                className="inline-flex items-center gap-1 rounded-md border border-outline/50 bg-surface-low px-2 py-1 text-xs font-medium text-on-surface hover:bg-surface-container cursor-pointer transition"
                title="Salin alamat lengkap"
              >
                {copied ? <Check className="size-3 text-emerald-600" /> : <Copy className="size-3 text-muted" />}
                <span>{copied ? 'Tersalin' : 'Salin Alamat'}</span>
              </button>
            </div>

            <h3 className="mt-3 text-lg font-bold text-on-surface leading-snug">
              {selectedLocation.name}
            </h3>

            <div className="mt-4 space-y-3 text-xs border-y border-outline/40 py-4 dark:border-slate-800">
              <div className="flex items-start gap-2.5">
                <MapPin className="size-4 shrink-0 text-primary mt-0.5" />
                <p className="leading-relaxed text-on-surface-variant font-medium">
                  {selectedLocation.address}
                </p>
              </div>

              <div className="flex items-center gap-2.5">
                <Phone className="size-4 shrink-0 text-muted" />
                <span className="font-mono text-on-surface font-semibold">{selectedLocation.phone}</span>
                {selectedLocation.fax && (
                  <span className="text-muted">(Fax: {selectedLocation.fax})</span>
                )}
              </div>

              <div className="flex items-center gap-2.5">
                <Clock className="size-4 shrink-0 text-muted" />
                <span className="text-muted">{selectedLocation.operatingHours}</span>
              </div>
            </div>

            <div className="mt-4">
              <p className="text-xs font-semibold text-muted uppercase tracking-wider">Peran &amp; Spesialisasi:</p>
              <p className="mt-1.5 text-xs leading-relaxed text-on-surface-variant">
                {selectedLocation.description}
              </p>
            </div>

            {selectedLocation.highlights && (
              <div className="mt-4 space-y-1.5">
                <p className="text-xs font-semibold text-muted uppercase tracking-wider">Fasilitas Utama:</p>
                {selectedLocation.highlights.map((h, i) => (
                  <div key={i} className="flex items-center gap-2 text-xs text-on-surface">
                    <span className="size-1.5 rounded-full bg-primary shrink-0" />
                    <span>{h}</span>
                  </div>
                ))}
              </div>
            )}

            <div className="mt-auto pt-5">
              <a
                href={directUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-xs font-semibold text-white transition hover:bg-primary-container shadow-xs"
              >
                <Navigation className="size-4" />
                <span>Navigasi &amp; Petunjuk Arah</span>
              </a>
            </div>
          </div>
        </section>
      </div>

      {/* Grid Switcher: Daftar Fasilitas DENSO Seluruh Indonesia */}
      <section className="space-y-3 pt-2">
        <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 className="text-base font-bold text-on-surface">
              Daftar Lokasi Pabrik &amp; Hub Regional di Indonesia
            </h3>
            <p className="text-xs text-muted">
              Pilih fasilitas di bawah ini untuk mengalihkan peta 3D secara instan.
            </p>
          </div>
          <div className="relative sm:w-64">
            <Search className="size-3.5 text-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Cari kota, nama pabrik, atau produk..."
              className="w-full h-8 pl-8 pr-3 text-xs rounded-lg border border-outline bg-surface-lowest text-on-surface outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-900"
            />
          </div>
        </div>

        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {filteredLocations.map((loc) => {
            const isSelected = loc.id === selectedId;
            return (
              <button
                key={loc.id}
                type="button"
                onClick={() => handleSelectLocation(loc.id)}
                className={`dls-card-surface text-left p-4 rounded-xl cursor-pointer transition border flex flex-col justify-between ${
                  isSelected
                    ? 'border-primary ring-2 ring-primary/20 bg-primary/5 dark:bg-red-950/20'
                    : 'border-outline/50 hover:border-primary/50 hover:bg-surface-low'
                }`}
              >
                <div>
                  <div className="flex items-center justify-between gap-2">
                    <span className="text-[10px] font-bold uppercase tracking-wider text-primary">
                      {loc.city}
                    </span>
                    {isSelected && (
                      <span className="inline-flex items-center gap-1 rounded bg-primary px-1.5 py-0.5 text-[10px] font-bold text-white">
                        Aktif
                      </span>
                    )}
                  </div>
                  <h4 className="mt-1 font-bold text-sm text-on-surface leading-snug">
                    {loc.shortName}
                  </h4>
                  <p className="mt-1 text-xs text-muted line-clamp-2 leading-relaxed">
                    {loc.address}
                  </p>
                </div>

                <div className="mt-4 pt-2.5 border-t border-outline/30 flex items-center justify-between text-xs text-muted dark:border-slate-800">
                  <span className="font-mono text-[11px]">{loc.phone}</span>
                  <span className="inline-flex items-center gap-0.5 font-semibold text-primary">
                    Lihat Peta <ChevronRight className="size-3" />
                  </span>
                </div>
              </button>
            );
          })}
        </div>
      </section>
    </div>
  );
}

