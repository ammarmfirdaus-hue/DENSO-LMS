<?php
/**
 * Komponen Footer Bersama — Portal Siswa (DLS Student Portal).
 *
 * Menampilkan identitas resmi PT DENSO INDONESIA, navigasi modul pembelajaran,
 * kontak layanan helpdesk, dan Peta 3D Fasilitas DENSO (WebGL perspective overlay).
 * Menggunakan gaya lms-card yang konsisten dengan seluruh kartu di dashboard siswa.
 */

$footer_base_path = (string) ($base_path ?? '');
$current_year = date('Y');
?>
<footer id="dlsStudentFooter" class="lms-card dls-student-footer mt-8 mb-4 p-5 sm:p-7 transition-colors" role="contentinfo">
    <style>
        .dls-student-footer {
            width: 100%;
            box-sizing: border-box;
        }
        .dls-footer-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr;
            gap: 28px;
            align-items: start;
        }
        @media (max-width: 960px) {
            .dls-footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 24px;
            }
            .dls-footer-col-brand {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 600px) {
            .dls-footer-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        .dls-footer-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #181824;
            margin-bottom: 12px;
        }
        html.dark .dls-footer-title {
            color: #F1F5F9;
        }
        .dls-footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }
        .dls-footer-links a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            color: #64748B;
            text-decoration: none;
            transition: color 0.16s ease, transform 0.16s ease;
        }
        .dls-footer-links a:hover {
            color: #DC2626;
            transform: translateX(2px);
        }
        html.dark .dls-footer-links a {
            color: #94A3B8;
        }
        html.dark .dls-footer-links a:hover {
            color: #EF4444;
        }
        .dls-footer-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            background: #F8FAFC;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
        }
        html.dark .dls-footer-badge {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: #CBD5E1;
        }
    </style>

    <div class="dls-footer-grid">
        <!-- Kolom 1: Profil Perusahaan DENSO (Lebar) -->
        <div class="dls-footer-col-brand space-y-3">
            <div class="flex items-center gap-2.5">
                <img src="<?= htmlspecialchars($footer_base_path . 'assets/img/icon/denso-vector-logo.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo DENSO" class="w-8 h-8 object-contain shrink-0 dark:hidden">
                <img src="<?= htmlspecialchars($footer_base_path . 'assets/img/icon/denso-vector-logo-dark.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo DENSO" class="w-8 h-8 object-contain shrink-0 hidden dark:block">
                <div>
                    <span class="block font-bold text-[14.5px] tracking-tight text-[#181824] dark:text-white leading-tight">DENSO Learning Management</span>
                    <span class="block text-[10.5px] font-semibold text-[#DC2626] uppercase tracking-wider">PT DENSO INDONESIA</span>
                </div>
            </div>

            <p class="text-[12px] leading-relaxed text-[#64748B] dark:text-[#94A3B8] max-w-md">
                Platform pembelajaran dan pelatihan digital mandiri karyawan untuk mendukung keunggulan operasional, keselamatan kerja (K3), dan inovasi berkelanjutan.
            </p>

            <div class="pt-1 flex flex-wrap items-center gap-2">
                <div class="dls-footer-badge">
                    <span class="material-symbols-outlined text-[15px] text-[#DC2626]">support_agent</span>
                    <span>Helpdesk: Senin – Jumat 07:30 – 16:30 WIB</span>
                </div>
                <button
                    type="button"
                    onclick="window.dlsToggleStudent3DMap && window.dlsToggleStudent3DMap()"
                    class="dls-footer-badge cursor-pointer hover:border-[#DC2626] hover:text-[#DC2626] transition"
                    title="Buka / Tutup Peta 3D Satelit"
                >
                    <span class="material-symbols-outlined text-[15px] text-[#DC2626]">view_in_ar</span>
                    <span id="dlsStudent3DMapBtnText">Peta 3D Fasilitas (WebGL)</span>
                </button>
            </div>
        </div>

        <!-- Kolom 2: Navigasi Pembelajaran -->
        <div>
            <h4 class="dls-footer-title">Navigasi Belajar</h4>
            <ul class="dls-footer-links">
                <li>
                    <a href="?route=/student">
                        <span class="material-symbols-outlined text-[15px]">home</span>
                        <span>Beranda Belajar</span>
                    </a>
                </li>
                <li>
                    <a href="?route=/student/courses">
                        <span class="material-symbols-outlined text-[15px]">school</span>
                        <span>Katalog Course</span>
                    </a>
                </li>
                <li>
                    <a href="?route=/student/nilai">
                        <span class="material-symbols-outlined text-[15px]">grade</span>
                        <span>Capaian &amp; Nilai</span>
                    </a>
                </li>
                <li>
                    <a href="?route=/student/riwayat">
                        <span class="material-symbols-outlined text-[15px]">history</span>
                        <span>Riwayat &amp; Sertifikat</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Kolom 3: Dukungan & Akun -->
        <div>
            <h4 class="dls-footer-title">Bantuan &amp; Akun</h4>
            <ul class="dls-footer-links">
                <li>
                    <a href="?route=/student/profil/bantuan">
                        <span class="material-symbols-outlined text-[15px]">help</span>
                        <span>Panduan Siswa</span>
                    </a>
                </li>
                <li>
                    <a href="?route=/student/notifikasi">
                        <span class="material-symbols-outlined text-[15px]">notifications</span>
                        <span>Pusat Notifikasi</span>
                    </a>
                </li>
                <li>
                    <a href="?route=/student/profil/pengaturan">
                        <span class="material-symbols-outlined text-[15px]">manage_accounts</span>
                        <span>Pengaturan Akun</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" onclick="window.dlsOpenSupportChat && window.dlsOpenSupportChat()">
                        <span class="material-symbols-outlined text-[15px]">chat</span>
                        <span>Hubungi Admin</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ── 3D MAPS OVERLAY WEBGL SECTION (COLLAPSIBLE / EMBEDDABLE) ── -->
    <div id="dlsStudent3DMapSection" class="hidden mt-6 border-t border-slate-100 dark:border-slate-800/80 pt-6">
        <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-[#070A11] p-1 shadow-2xl text-slate-200">
            <!-- WebGL Cockpit Corners / Viewfinder Brackets -->
            <div class="pointer-events-none absolute top-3 left-3 z-30 size-4 border-t-2 border-l-2 border-red-500/80"></div>
            <div class="pointer-events-none absolute top-3 right-3 z-30 size-4 border-t-2 border-r-2 border-red-500/80"></div>
            <div class="pointer-events-none absolute bottom-3 left-3 z-30 size-4 border-b-2 border-l-2 border-red-500/80"></div>
            <div class="pointer-events-none absolute bottom-3 right-3 z-30 size-4 border-b-2 border-r-2 border-red-500/80"></div>

            <!-- Top HUD Telemetry & Location Controls -->
            <div class="relative z-20 flex flex-wrap items-center justify-between gap-3 border-b border-slate-800/90 bg-[#0B0F19]/95 px-4 py-3 text-xs backdrop-blur-md">
                <div className="flex items-center gap-3">
                    <span class="flex items-center gap-2">
                        <span class="relative flex size-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2.5 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="font-mono font-bold tracking-wider text-slate-100 uppercase">
                            DENSO GEOSPATIAL 3D
                        </span>
                        <span class="rounded bg-red-950/80 px-1.5 py-0.5 font-mono text-[10px] font-bold text-red-400 border border-red-800/60">
                            WEBGL PERSPECTIVE
                        </span>
                    </span>
                    <span id="dlsStudentMapCoords" class="hidden font-mono text-[11px] text-slate-400 sm:inline-block border-l border-slate-800 pl-3">
                        LAT: -6.1362° · LNG: 106.8834° · ALT: 14M
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        id="dlsStudentMapDirectLink"
                        href="https://www.google.com/maps/search/?api=1&query=PT+Denso+Indonesia+Sunter+Plant"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/40 bg-red-600/20 px-2.5 py-1 text-[11px] font-bold text-red-300 transition hover:bg-red-600 hover:text-white"
                    >
                        <span>Google Earth 3D</span>
                        <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                    </a>
                </div>
            </div>

            <!-- Location Selector Chips Bar -->
            <div class="relative z-20 flex items-center gap-1.5 overflow-x-auto border-b border-slate-800/60 bg-[#080C14] px-4 py-2 text-xs scrollbar-none">
                <span class="shrink-0 font-mono text-[10.5px] uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-red-500">location_on</span>
                    <span>Pilih Pabrik / Hub:</span>
                </span>
                <button
                    type="button"
                    class="dls-map-loc-btn inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs"
                    style="background-color:#DC2626; color:#FFFFFF;"
                    data-lat="-6.1362"
                    data-lng="106.8834"
                    onclick="window.dlsChangeStudentMapLocation(-6.1362, 106.8834, 'PT Denso Indonesia - Sunter Plant (Head Office)', 'Jl. Gaya Motor I No. 6, Sunter II, Jakarta Utara 14330')"
                >
                    Plant 1 Sunter (HQ)
                </button>
                <button
                    type="button"
                    class="dls-map-loc-btn inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700 hover:text-slate-200"
                    data-lat="-6.3048"
                    data-lng="107.0988"
                    onclick="window.dlsChangeStudentMapLocation(-6.3048, 107.0988, 'PT Denso Indonesia - Bekasi Plant (MM2100)', 'Jl. Kalimantan Blok E 1-2 No. 2, Kawasan Industri MM2100, Cikarang Barat 17520')"
                >
                    Plant 2 Bekasi (MM2100)
                </button>
                <button
                    type="button"
                    class="dls-map-loc-btn inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700 hover:text-slate-200"
                    data-lat="-6.3122"
                    data-lng="107.1065"
                    onclick="window.dlsChangeStudentMapLocation(-6.3122, 107.1065, 'PT Denso Indonesia - Fajar Plant (Plant 3)', 'Jl. Irian Blok JJ-11, Kawasan Industri MM2100, Cikarang Barat 17520')"
                >
                    Plant 3 Fajar (MM2100)
                </button>
                <button
                    type="button"
                    class="dls-map-loc-btn inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700 hover:text-slate-200"
                    data-lat="-7.3184"
                    data-lng="112.7381"
                    onclick="window.dlsChangeStudentMapLocation(-7.3184, 112.7381, 'Denso Service Center & Hub Surabaya', 'Jl. Raya Jemursari No. 238, Surabaya, Jawa Timur 60237')"
                >
                    Regional Hub Surabaya
                </button>
                <button
                    type="button"
                    class="dls-map-loc-btn inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700 hover:text-slate-200"
                    data-lat="-6.9826"
                    data-lng="110.4208"
                    onclick="window.dlsChangeStudentMapLocation(-6.9826, 110.4208, 'Denso Regional Center Semarang', 'Jl. Gajahmada No. 142, Semarang, Jawa Tengah 50134')"
                >
                    Regional Center Semarang
                </button>
                <button
                    type="button"
                    class="dls-map-loc-btn inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 font-medium transition cursor-pointer text-xs border border-slate-800 bg-slate-900/60 text-slate-400 hover:border-slate-700 hover:text-slate-200"
                    data-lat="3.5786"
                    data-lng="98.6325"
                    onclick="window.dlsChangeStudentMapLocation(3.5786, 98.6325, 'Denso Regional Center Medan', 'Jl. Gagak Hitam No. 7, Medan Sunggal, Medan, Sumatera Utara 20122')"
                >
                    Regional Hub Medan
                </button>
            </div>

            <!-- Viewport 3D Satellite -->
            <div class="relative w-full h-[360px] sm:h-[400px] overflow-hidden bg-slate-950">
                <!-- Center HUD Reticle Crosshair (WebGL Atmospheric Visual) -->
                <div class="pointer-events-none absolute inset-0 z-10 grid place-items-center opacity-30">
                    <div class="relative size-16 sm:size-20">
                        <div class="absolute inset-0 rounded-full border border-dashed border-red-500/60"></div>
                        <div class="absolute top-1/2 left-0 right-0 h-px bg-red-400/40"></div>
                        <div class="absolute top-0 bottom-0 left-1/2 w-px bg-red-400/40"></div>
                        <div class="absolute inset-4 rounded-full border border-cyan-400/40"></div>
                    </div>
                </div>

                <iframe
                    id="dlsStudent3DMapIframe"
                    src="https://maps.google.com/maps?q=-6.1362,106.8834&hl=id&t=k&z=17&ie=UTF8&iwloc=&output=embed"
                    title="Peta 3D Fasilitas Denso"
                    class="h-full w-full border-0 bg-slate-950 filter contrast-[1.05]"
                    allowfullscreen=""
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>

            <!-- Bottom Strip -->
            <div class="relative z-20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-slate-800/90 bg-[#0B0F19]/95 px-4 py-3 text-xs">
                <div class="space-y-0.5">
                    <strong id="dlsStudentMapTitle" class="text-sm font-bold text-white">PT Denso Indonesia - Sunter Plant (Head Office)</strong>
                    <p id="dlsStudentMapAddr" class="text-slate-400 text-[12px] max-w-3xl">Jl. Gaya Motor I No. 6, Sunter II, Jakarta Utara 14330</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Bagian Bawah: Hak Cipta -->
    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-[11.5px] text-[#94A3B8]">
        <p>&copy; <?= $current_year ?> <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi.</p>
        <div class="flex items-center gap-3">
            <a href="?route=/student/profil/bantuan" class="text-[#64748B] hover:text-[#DC2626] transition dark:text-[#94A3B8] dark:hover:text-white">Panduan Penggunaan</a>
            <span>&middot;</span>
            <a href="javascript:void(0)" onclick="window.dlsToggleStudent3DMap && window.dlsToggleStudent3DMap()" class="text-[#64748B] hover:text-[#DC2626] transition dark:text-[#94A3B8] dark:hover:text-white">Peta 3D Fasilitas</a>
            <span>&middot;</span>
            <a href="?route=/student/profil/pengaturan" class="text-[#64748B] hover:text-[#DC2626] transition dark:text-[#94A3B8] dark:hover:text-white">Keamanan Akun</a>
        </div>
    </div>
</footer>

<script>
(function() {
    function injectStudentFooter() {
        var footer = document.getElementById('dlsStudentFooter');
        if (!footer) return;
        var container = document.querySelector('main:not([data-student-dashboard]) > div.max-w-5xl, main:not([data-student-dashboard]) > div.max-w-4xl, main:not([data-student-dashboard]) > div.max-w-3xl, main:not([data-student-dashboard]) > div[class*="max-w-"]');
        var target = container || document.querySelector('main.dls-main, main.dls-main-sub, main[data-student-dashboard], main');
        if (target && footer.parentElement !== target) {
            target.appendChild(footer);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectStudentFooter);
    } else {
        injectStudentFooter();
    }

    window.dlsToggleStudent3DMap = function() {
        var sec = document.getElementById('dlsStudent3DMapSection');
        var txt = document.getElementById('dlsStudent3DMapBtnText');
        if (!sec) return;
        var isHidden = sec.classList.contains('hidden');
        if (isHidden) {
            sec.classList.remove('hidden');
            if (txt) txt.textContent = 'Tutup Peta 3D';
            sec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            sec.classList.add('hidden');
            if (txt) txt.textContent = 'Peta 3D Fasilitas (WebGL)';
        }
    };

    window.dlsChangeStudentMapLocation = function(lat, lng, name, address) {
        var iframe = document.getElementById('dlsStudent3DMapIframe');
        var titleEl = document.getElementById('dlsStudentMapTitle');
        var addrEl = document.getElementById('dlsStudentMapAddr');
        var coordsEl = document.getElementById('dlsStudentMapCoords');
        var directLink = document.getElementById('dlsStudentMapDirectLink');
        if (iframe) {
            iframe.src = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&hl=id&t=k&z=17&ie=UTF8&iwloc=&output=embed';
        }
        if (titleEl) titleEl.textContent = name;
        if (addrEl) addrEl.textContent = address;
        if (coordsEl) coordsEl.textContent = 'LAT: ' + lat + '° · LNG: ' + lng + '°';
        if (directLink) directLink.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(name + ' ' + address);
        document.querySelectorAll('.dls-map-loc-btn').forEach(function(b) {
            var match = b.getAttribute('data-lat') === String(lat);
            b.style.backgroundColor = match ? '#DC2626' : '';
            b.style.color = match ? '#FFFFFF' : '';
        });
    };
})();
</script>
