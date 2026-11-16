<?php
/**
 * Komponen Footer Bersama — Portal Siswa (DLS Student Portal).
 *
 * Menampilkan identitas resmi PT DENSO INDONESIA, tautan cepat navigasi modul belajar siswa,
 * informasi fasilitas pabrik Sunter & MM2100 Bekasi, jam operasional,
 * serta status kepatuhan keamanan.
 */

$footer_base_path = (string) ($base_path ?? '');
?>
<footer id="dlsStudentFooter" class="dls-student-footer mt-12 w-full rounded-2xl border border-slate-200/80 bg-slate-900 text-slate-300 dark:border-slate-800 dark:bg-[#0B0F15] transition-colors overflow-hidden" role="contentinfo">
    <div class="w-full px-5 py-8 sm:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-12">
            <!-- Kolom 1: Profil & Identitas DENSO (5 Kolom) -->
            <div class="lg:col-span-5 space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="font-mono text-2xl font-black tracking-wider text-[#E60012]">DENSO</span>
                    <span class="h-4 w-px bg-slate-700"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-200">Learning Portal</span>
                </div>
                <p class="text-xs leading-relaxed text-slate-400 max-w-md">
                    Portal pembelajaran mandiri karyawan PT DENSO INDONESIA untuk memperkuat keahlian teknis otomotif, kepatuhan keselamatan kerja (K3), dan penerapan semangat <em>Monozukuri</em> dalam industri manufaktur presisi berstandar global.
                </p>
                <div class="space-y-1.5 text-xs text-slate-400 pt-1">
                    <p class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-[#E60012] shrink-0 mt-0.5" aria-hidden="true">location_on</span>
                        <span><strong>Kantor Pusat:</strong> Jl. Gaya Motor I No. 6, Sunter II, Tanjung Priok, Jakarta Utara 14330</span>
                    </p>
                    <p class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-slate-500 shrink-0 mt-0.5" aria-hidden="true">factory</span>
                        <span><strong>Bekasi Plant:</strong> MM2100 Industrial Town Blok E, Cikarang Barat 17520</span>
                    </p>
                </div>
            </div>

            <!-- Kolom 2: Navigasi Belajar Siswa (3 Kolom) -->
            <div class="lg:col-span-3 space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Menu Belajar</h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="?route=/student" class="hover:text-white transition flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">home</span> Beranda Belajar</a></li>
                    <li><a href="?route=/student/courses" class="hover:text-white transition flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">school</span> Katalog Course</a></li>
                    <li><a href="?route=/student/nilai" class="hover:text-white transition flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">grade</span> Capaian &amp; Nilai</a></li>
                    <li><a href="?route=/student/riwayat" class="hover:text-white transition flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">history</span> Riwayat &amp; Sertifikat</a></li>
                    <li><a href="?route=/student/notifikasi" class="hover:text-white transition flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">notifications</span> Pusat Pengumuman</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Fasilitas & Bantuan (4 Kolom) -->
            <div class="lg:col-span-4 space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Bantuan &amp; Helpdesk</h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li>
                        <a href="?route=/student/profil/bantuan" class="hover:text-white transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">help</span>
                            <span>Panduan Penggunaan Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a href="?route=/student/profil/pengaturan" class="hover:text-white transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-slate-400" aria-hidden="true">settings</span>
                            <span>Pengaturan Akun &amp; Keamanan</span>
                        </a>
                    </li>
                </ul>
                <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-3 text-xs text-slate-400 mt-2">
                    <p class="font-semibold text-slate-200">Layanan Helpdesk Pelatihan:</p>
                    <p class="text-[11px] mt-0.5">Senin – Jumat: 07:30 – 16:30 WIB</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Dukungan teknis akun &amp; modul pembelajaran</p>
                </div>
            </div>
        </div>

        <!-- Bar Hak Cipta -->
        <div class="mt-8 border-t border-slate-800 pt-5 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi.</p>
            <p class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-emerald-400 font-medium">
                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">lock</span>
                    TLS 1.3 Terenkripsi
                </span>
                <span>&middot;</span>
                <span>SSO Validated</span>
                <span>&middot;</span>
                <span class="font-mono">v2.4.0</span>
            </p>
        </div>
    </div>
</footer>
<script>
(function() {
    function injectStudentFooter() {
        var footer = document.getElementById('dlsStudentFooter');
        var main = document.querySelector('main.dls-main, main.dls-main-sub, main[data-student-dashboard], main');
        if (footer && main && footer.parentElement !== main) {
            main.appendChild(footer);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectStudentFooter);
    } else {
        injectStudentFooter();
    }
})();
</script>
