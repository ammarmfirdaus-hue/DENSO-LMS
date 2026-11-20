<?php
/**
 * Komponen Footer Bersama — Portal Siswa (DLS Student Portal).
 *
 * Menampilkan identitas resmi PT DENSO INDONESIA, navigasi modul pembelajaran,
 * lokasi fasilitas pabrik, dan kontak layanan helpdesk.
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
            padding: 4px 10px;
            background: #F8FAFC;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 500;
            color: #475569;
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

            <div class="pt-1">
                <div class="dls-footer-badge">
                    <span class="material-symbols-outlined text-[15px] text-[#DC2626]">support_agent</span>
                    <span>Helpdesk: Senin – Jumat 07:30 – 16:30 WIB</span>
                </div>
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

    <!-- Bar Bagian Bawah: Hak Cipta -->
    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-[11.5px] text-[#94A3B8]">
        <p>&copy; <?= $current_year ?> <strong>PT DENSO INDONESIA</strong>. Hak Cipta Dilindungi.</p>
        <div class="flex items-center gap-3">
            <a href="?route=/student/profil/bantuan" class="text-[#64748B] hover:text-[#DC2626] transition dark:text-[#94A3B8] dark:hover:text-white">Panduan Penggunaan</a>
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
})();
</script>
