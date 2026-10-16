/**
 * Konfigurasi Utama Tailwind CSS untuk Learning Management System (LMS).
 *
 * Objek konfigurasi ini bertindak sebagai fondasi arsitektur antarmuka pengguna (UI),
 * mendefinisikan seluruh aturan dan parameter pengaturan untuk kerangka kerja Tailwind CSS.
 * Konfigurasi ini mencakup ruang lingkup pemindaian kelas utilitas (content routing),
 * manajemen mode tampilan (dark mode strategy), pengecualian proses purifikasi CSS (safelist),
 * serta penyesuaian tema desain visual (theme customization) yang secara spesifik dirancang
 * untuk memenuhi standar identitas visual aplikasi LMS tingkat korporat.
 *
 * @type {import('tailwindcss').Config}
 */
module.exports = {
    /**
     * Penentuan Ruang Lingkup Berkas Sumber (Content Paths / Purge Strategy).
     *
     * Menginstruksikan kompilator (compiler) Tailwind CSS mengenai lokasi eksak
     * dari seluruh berkas kode sumber (seperti skrip PHP dan JavaScript) yang perlu
     * dipindai. Proses ini bertujuan untuk mengekstraksi secara otomatis setiap
     * utilitas kelas CSS yang aktif digunakan di dalam sistem. Melalui penentuan
     * ruang lingkup yang presisi, ukuran berkas keluaran CSS dapat diminimalkan
     * secara signifikan dengan cara menghapus (purge) kelas yang tidak terpakai,
     * sehingga mengoptimalkan kinerja pemuatan halaman secara ekstensif.
     */
    content: ['./*.php', './Views/**/*.php', './includes/**/*.php', './Controllers/**/*.php', './assets/js/**/*.js'],

    /**
     * Strategi Implementasi Mode Gelap (Dark Mode Strategy).
     *
     * Mendefinisikan pendekatan sistem dalam menangani pergantian tema tampilan
     * antara mode terang (light mode) dan mode gelap (dark mode). Penggunaan
     * nilai 'class' mengindikasikan bahwa mode gelap tidak bergantung pada preferensi
     * sistem operasi pengguna, melainkan dikendalikan secara eksplisit dan manual
     * melalui penyisipan kelas CSS `dark` pada elemen hierarki tertinggi dokumen (elemen HTML).
     */
    darkMode: 'class',

    /**
     * Daftar Kelas Utilitas yang Dikecualikan dari Pembersihan (Safelist).
     *
     * Berfungsi sebagai pendaftar eksklusif untuk kelas-kelas CSS utilitas yang
     * dihasilkan atau dikonstruksi secara dinamis saat waktu berjalan (runtime),
     * baik melalui skrip sisi server (PHP) maupun sisi klien (JavaScript).
     * Oleh karena kompilator statis Tailwind tidak dapat mendeteksi nama kelas
     * yang tidak tertulis secara utuh, daftar ini menjamin bahwa seluruh kelas
     * yang tercantum akan senantiasa disertakan ke dalam berkas CSS akhir dan
     * terlindungi dari proses penghapusan otomatis (tree-shaking).
     */
    safelist: [
        /** Tata Letak Grid (Diaplikasikan secara dinamis melalui intervensi filter JavaScript) */
        'grid',
        'grid-cols-1',
        'md:grid-cols-2',
        'lg:grid-cols-3',
        'gap-gutter',
        'hidden',
        'dls-hidden',

        /** Palet Gradien Kartu Kursus (Diinisialisasi secara dinamis melalui variabel PHP) */
        'from-orange-400',
        'to-orange-600',
        'from-green-400',
        'to-emerald-600',
        'from-teal-400',
        'to-cyan-600',
        'from-blue-400',
        'to-blue-600',
        'from-violet-400',
        'to-purple-600',
        'from-sky-400',
        'to-indigo-600',
        'from-pink-400',
        'to-rose-600',
        'from-amber-400',
        'to-yellow-600',
        'from-red-400',
        'to-rose-700',
        'from-lime-400',
        'to-green-600',

        /** Warna Indikator Status Kritis (Diinisialisasi secara dinamis melalui logika PHP) */
        'text-green-600',
        'text-orange-600',
        'border-green-300',
        'border-red-300',

        /** Kelas Utilitas Esensial untuk Render Latar Belakang Bergradien */
        'bg-gradient-to-br'
    ],

    /**
     * Konfigurasi Fundamental Tema Desain (Theme Architecture).
     *
     * Bertindak sebagai repositori pusat untuk seluruh variabel desain (design tokens).
     * Bagian ini mengesampingkan atau memperluas (extend) pengaturan bawaan Tailwind
     * untuk mendefinisikan bahasa visual (visual language) yang distandardisasi bagi sistem LMS.
     * Parameter yang diatur meliputi palet warna korporat, hierarki tipografi struktural,
     * skala ukuran spasial, proporsi lengkungan antarmuka, hingga titik henti responsivitas
     * untuk berbagai dimensi perangkat pengakses.
     */
    theme: {
        extend: {
            /**
             * Token Palet Warna Sistem Desain (Color Design Tokens).
             *
             * Merupakan implementasi arsitektur warna berbasis Material Design 3
             * yang disesuaikan secara komprehensif untuk mematuhi kebutuhan identitas visual LMS.
             * Setiap token memetakan peran warna secara semantik, mencakup warna primer,
             * sekunder, warna peringatan (error), hingga penempatan warna dasar pada permukaan
             * latar belakang (surface and background). Pendekatan semantik ini
             * memastikan konsistensi estetika serta aksesibilitas kontras secara menyeluruh.
             */
            colors: {
                primary: '#b4000b',
                'on-primary': '#ffffff',
                'primary-container': '#e20613',
                'on-primary-container': '#fff3f2',
                'primary-fixed': '#ffdad5',
                'primary-fixed-dim': '#ffb4aa',
                'on-primary-fixed': '#410001',
                'on-primary-fixed-variant': '#930007',
                'inverse-primary': '#ffb4aa',
                'surface-tint': '#c0000d',

                secondary: '#4355b9',
                'on-secondary': '#ffffff',
                'secondary-container': '#8596ff',
                'on-secondary-container': '#11278e',
                'secondary-fixed': '#dee0ff',
                'secondary-fixed-dim': '#bac3ff',
                'on-secondary-fixed': '#00105c',
                'on-secondary-fixed-variant': '#293ca0',

                tertiary: '#00671a',
                'on-tertiary': '#ffffff',
                'tertiary-container': '#178229',
                'on-tertiary-container': '#deffd6',
                'tertiary-fixed': '#94f990',
                'tertiary-fixed-dim': '#78dc77',
                'on-tertiary-fixed': '#002204',
                'on-tertiary-fixed-variant': '#005313',

                error: '#ba1a1a',
                'on-error': '#ffffff',
                'error-container': '#ffdad6',
                'on-error-container': '#93000a',

                warning: '#d97706',
                'on-warning': '#ffffff',
                'warning-container': '#fef3c7',
                'on-warning-container': '#78350f',

                background: '#fcf9f8',
                'on-background': '#1b1c1c',
                surface: '#fcf9f8',
                'on-surface': '#1b1c1c',
                'surface-variant': '#e5e2e1',
                'on-surface-variant': '#5e3f3b',
                'surface-bright': '#fcf9f8',
                'surface-dim': '#dcd9d9',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f6f3f2',
                'surface-container': '#f0eded',
                'surface-container-high': '#eae7e7',
                'surface-container-highest': '#e5e2e1',
                'inverse-surface': '#303030',
                'inverse-on-surface': '#f3f0ef',

                outline: '#936e69',
                'outline-variant': '#e9bcb6'
            },

            /**
             * Skala Radius Lengkungan (Border Radius Metrics).
             *
             * Menetapkan spesifikasi matematis absolut untuk tingkat kelengkungan sudut pada
             * seluruh komponen antarmuka pengguna. Standardisasi metrik ini diaplikasikan
             * pada elemen antarmuka seperti wadah kartu (cards), tombol interaktif (buttons),
             * serta kotak dialog (modals), guna memastikan penciptaan visual komponen
             * yang kohesif, ergonomis, dan merepresentasikan pedoman UI aplikasi korporat.
             */
            borderRadius: {
                DEFAULT: '0.25rem',
                lg: '0.5rem',
                xl: '0.75rem',
                '2xl': '1rem',
                '3xl': '1.5rem',
                full: '9999px'
            },

            /**
             * Metrik Spasial dan Presisi Tata Letak (Spatial & Layout Metrics).
             *
             * Menentukan skala mutlak untuk pendistribusian pengaturan jarak internal (padding)
             * dan jarak eksternal (margin). Definisi spasi struktural ini, yang mencakup jeda antar-komponen,
             * lebar rongga pemisah tata letak (gutter), serta unit dasar spasial (base), adalah
             * parameter krusial untuk menegakkan integritas proporsional halaman serta
             * merawat ritme visual sesuai dengan metodologi desain sistem tingkat atas.
             */
            spacing: {
                'container-margin': '16px',
                'stack-sm': '8px',
                'stack-md': '16px',
                'stack-lg': '24px',
                gutter: '16px',
                base: '8px'
            },

            /**
             * Arsitektur Tipografi Dasar (Typography Architecture).
             *
             * Menstandardisasi integrasi rupa huruf (typeface) esensial yang akan
             * memandu penyajian visual seluruh lapisan tekstual aplikasi. Mengutamakan pemanggilan
             * keluarga fon web modern spesifik guna menyuguhkan kejelasan baca maksimal (legibility)
             * pada perangkat masa kini, didukung oleh implementasi fondasi jenis huruf sistem
             * operasi sebagai protokol keamanan penyediaan tampilan cadangan (fallback system).
             */
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                'headline-lg': ['Inter', 'sans-serif'],
                'headline-md': ['Inter', 'sans-serif'],
                'headline-sm': ['Inter', 'sans-serif'],
                'body-lg': ['Inter', 'sans-serif'],
                'body-md': ['Inter', 'sans-serif'],
                'label-sm': ['Inter', 'sans-serif'],
                'label-md': ['Inter', 'sans-serif']
            },

            /**
             * Spesifikasi Skala Dimensi Tipografi (Typography Dimensional Scale).
             *
             * Menjabarkan pemetaan kalibrasi hierarki visual teks secara sistematis.
             * Atribut yang dikendalikan dengan ketat melalui struktur ini antara lain ialah
             * dimensi ukuran absolut (font size), kalkulasi spasi vertikal antar baris
             * (line height), jarak interval antarkarakter (letter spacing), dan modulasi
             * bobot tebal tipografi (font weight) demi menegakkan struktur informasi yang berkelas.
             */
            fontSize: {
                'headline-lg': ['28px', { lineHeight: '34px', letterSpacing: '-0.02em', fontWeight: '700' }],
                'headline-md': ['22px', { lineHeight: '28px', letterSpacing: '-0.01em', fontWeight: '700' }],
                'headline-sm': ['18px', { lineHeight: '24px', fontWeight: '700' }],
                'body-lg': ['16px', { lineHeight: '24px', fontWeight: '400' }],
                'body-md': ['14px', { lineHeight: '20px', fontWeight: '400' }],
                'label-sm': ['12px', { lineHeight: '16px', letterSpacing: '0.02em', fontWeight: '500' }],
                'label-md': ['14px', { lineHeight: '20px', fontWeight: '500' }]
            },

            /**
             * Titik Henti Resolusi Perangkat (Responsive Breakpoints).
             *
             * Mengkalibrasi ukuran patokan area pandang (viewport) presisi. Titik henti ini
             * diutilisasi oleh sistem 'media queries' arsitektur CSS untuk mengendalikan transisi
             * tata letak cairan, menjamin agar elemen presentasi beradaptasi serta meluruh dengan
             * keanggunan mekanis pada perangkat telpon pintar, perangkat tablet, maupun layar desktop.
             */
            screens: {
                xs: '375px',
                sm: '640px',
                md: '768px',
                lg: '1024px',
                xl: '1280px'
            }
        }
    },

    /**
     * Modul Ekstensi Pihak Ketiga (Plugin Architecture).
     *
     * Merupakan lapisan kerangka kerja arsitektural yang didedikasikan untuk
     * menginjeksi komponen dan utilitas pihak ketiga tambahan. Lingkungan perluasan ini
     * menjembatani utilitas fungsional spesifik sistem seperti dukungan format formulir
     * ekstensif, tipografi terstruktur, maupun lapisan interaktif lanjutan lain.
     */
    plugins: []
};
