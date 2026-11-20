<?php
/**
 * Komponen Footer Bersama — Portal Siswa (DLS Student Portal).
 *
 * Menampilkan identitas resmi PT DENSO INDONESIA, navigasi cepat modul belajar siswa,
 * informasi fasilitas pabrik, dan jam layanan helpdesk.
 * Desain bersih, proporsional, dan adaptif tema terang/gelap (tanpa kotak hitam / AI-slop).
 */

$footer_base_path = (string) ($base_path ?? '');
$current_year = date('Y');
?>
<footer id="dlsStudentFooter" class="dls-student-footer mt-16 w-full border-t border-slate-200/80 pt-8 pb-24 text-slate-500 transition-colors lg:pb-10 dark:border-slate-800/80 dark:text-slate-400" role="contentinfo">
    <div class="mx-auto w-full max-w-5xl">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-12">
            <!-- Kolom 1: Profil & Identitas DENSO (6 Kolom) -->
            <div class="space-y-2.5 lg:col-span-6">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xl font-black tracking-wider text-[#E60012]">DENSO</span>
                    <span class="h-3.5 w-px bg-slate-300 dark:bg-slate-700"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Learning Portal</span>
                </div>
                <p class="max-w-md text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Platform pembelajaran dan pengembangan kompetensi mandiri karyawan PT DENSO INDONESIA.
                </p>
                <p class="pt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Kantor Pusat: Sunter, Jakarta Utara &middot; Pabrik: MM2100 Cikarang Barat
                </p>
            </div>

            <!-- Kolom 2: Navigasi Cepat (3 Kolom) -->
            <div class="space-y-2 lg:col-span-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Navigasi</h4>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="?route=/student" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Beranda Belajar</a></li>
                    <li><a href="?route=/student/courses" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Katalog Course</a></li>
                    <li><a href="?route=/student/nilai" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Capaian &amp; Nilai</a></li>
                    <li><a href="?route=/student/riwayat" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Riwayat &amp; Sertifikat</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Bantuan & Dukungan (3 Kolom) -->
            <div class="space-y-2 lg:col-span-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Bantuan</h4>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="?route=/student/profil/bantuan" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Panduan Siswa</a></li>
                    <li><a href="?route=/student/notifikasi" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Pusat Notifikasi</a></li>
                    <li><a href="?route=/student/profil/pengaturan" class="text-slate-500 hover:text-primary transition dark:text-slate-400 dark:hover:text-white">Pengaturan Akun</a></li>
                </ul>
                <p class="pt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Helpdesk: 07:30 – 16:30 WIB (Hari Kerja)
                </p>
            </div>
        </div>

        <!-- Bar Hak Cipta -->
        <div class="mt-8 flex flex-col items-center justify-between gap-3 border-t border-slate-200/60 pt-4 text-[11.5px] text-slate-400 sm:flex-row dark:border-slate-800/60 dark:text-slate-500">
            <p>&copy; <?= $current_year ?> PT DENSO INDONESIA. Hak Cipta Dilindungi.</p>
            <div class="flex items-center gap-3">
                <a href="?route=/student/profil/bantuan" class="hover:text-slate-700 transition dark:hover:text-slate-300">Pusat Bantuan</a>
                <span>&middot;</span>
                <a href="?route=/student/profil/pengaturan" class="hover:text-slate-700 transition dark:hover:text-slate-300">Keamanan</a>
            </div>
        </div>
    </div>
</footer>
<script>
(function() {
    function injectStudentFooter() {
        var footer = document.getElementById('dlsStudentFooter');
        if (!footer) return;
        var main = document.querySelector('main.dls-main, main.dls-main-sub, main[data-student-dashboard], main');
        if (main && footer.parentElement !== main) {
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
