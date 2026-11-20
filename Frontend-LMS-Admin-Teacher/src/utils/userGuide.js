import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/**
 * Driver.js Interactive & Role-Aware In-Depth User Guide
 * untuk Seluruh Halaman & Modul Portal DENSO-LMS (Admin, Teacher, Reviewer, Student).
 */

function normalizeRole(role) {
  const r = String(role || '').toLowerCase();
  if (r.includes('admin')) return 'admin';
  if (r.includes('teacher') || r.includes('trainer')) return 'teacher';
  if (r.includes('reviewer')) return 'reviewer';
  return 'staff';
}

function getRoleLabel(role) {
  const r = normalizeRole(role);
  if (r === 'admin') return 'Administrator (Admin)';
  if (r === 'teacher') return 'Pengajar / Trainer (Teacher)';
  if (r === 'reviewer') return 'Peninjau Materi (Reviewer)';
  return 'Pengguna Portal Staf';
}

function buildPageConfig(currentPath, role) {
  const r = normalizeRole(role);
  const isAdmin = r === 'admin';
  const isTeacher = r === 'teacher';

  // 1. Dashboard
  if (currentPath === '/') {
    return {
      pageName: 'Dashboard Operasional',
      roleSummary: isTeacher
        ? 'Menampilkan statistik khusus kursus ampuan Anda, pergerakan siswa, alur materi, dan pintasan cepat.'
        : 'Menampilkan ringkasan seluruh data ekosistem LMS secara real-time, tren aktivitas, persetujuan materi, dan aksi operasional.',
      steps: [
        {
          label: 'Header & Rentang Waktu',
          element: 'main header, main > div > div:first-child',
          popover: {
            title: '1. Ringkasan Operasional & Filter Waktu',
            description: 'Pantau tanggal hari ini dan gunakan tombol filter rentang waktu (7 Hari / 14 Hari / 30 Hari) untuk menyesuaikan cakupan seluruh grafik analitik di halaman ini.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '4 Kartu KPI Utama',
          element: '#tour-overview',
          popover: {
            title: '2. Empat Indikator Kinerja Utama (KPI)',
            description: isTeacher
              ? 'Pantau total kursus yang Anda ampu, total pengajuan materi Anda, materi yang sedang direview, dan jumlah peserta aktif pada kelas Anda.'
              : 'Pantau empat metrik utama portal: Total Kursus Aktif, Pengajuan Materi Baru, Antrean Reviewer, dan Total Pengguna Terdaftar.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Grafik Tren Aktivitas',
          element: '#tour-charts',
          popover: {
            title: '3. Grafik Tren Aktivitas & Keterlibatan',
            description: 'Grafik interaktif yang mencatat frekuensi login pengguna, aktivitas penyelesaian sesi, dan progres materi harian dalam kurun waktu yang dipilih.',
            side: 'top',
            align: 'center'
          }
        },
        {
          label: 'Alur Persetujuan Materi',
          element: 'main section:nth-of-type(2) > div:last-child, main section:has(canvas):first-of-type',
          popover: {
            title: '4. Distribusi Status Alur Persetujuan Materi',
            description: 'Memvisualisasikan materi pada tahap Menunggu Tindakan Admin, Sedang Ditinjau Reviewer, Perlu Revisi Trainer, dan Materi Final Terbit.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Perbandingan Kursus Teraktif',
          element: 'main section:nth-of-type(3) > div:first-child',
          popover: {
            title: '5. Peringkat Kursus Paling Aktif',
            description: 'Menganalisis perbandingan jumlah peserta terdaftar, peserta yang telah tuntas, dan total materi yang tersedia pada modul-modul pelatihan teratas.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Sebaran Progres & Format Konten',
          element: 'main section:nth-of-type(3) > div:last-child',
          popover: {
            title: '6. Sebaran Progres Belajar & Format Materi',
            description: 'Gunakan sakelar tab "Progres / Format" untuk beralih antara grafik capaian kelulusan peserta dan keragaman format konten (Video, PDF, Teks, Link).',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Ringkasan Kursus Terbaru',
          element: 'main section:nth-of-type(4) > div:first-child',
          popover: {
            title: '7. Daftar Modul Kursus Terkini',
            description: 'Tabel cepat untuk melihat kode kursus, kategori (Wajib/Opsional), trainer pengampu, jumlah sesi pertemuan, dan total materi yang diterbitkan.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Feed Aktivitas Real-Time',
          element: '#tour-recent-activity',
          popover: {
            title: '8. Catatan Audit Aktivitas Langsung',
            description: 'Feed pencatatan realtime yang mendeteksi setiap proses sistem (antrean background job, login staf, pengajuan materi baru, dan keputusan review).',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Panel Langkah Cepat',
          element: '#quick-actions',
          popover: {
            title: '9. Akses Cepat Fitur Utama',
            description: 'Pintasan satu klik untuk langsung meninjau materi, mengajukan modul, menambah pengguna, menyetujui peserta, mengelola kuis, dan memeriksa nilai.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 2. Courses List
  if (currentPath === '/courses') {
    return {
      pageName: 'Katalog & Manajemen Course',
      roleSummary: isTeacher
        ? 'Kelola kurikulum kursus ampuan Anda, perbarui silabus sesi, dan pantau ketersediaan materi.'
        : 'Admin mengelola seluruh katalog pelatihan DENSO, menugaskan Trainer, mengatur kategori Wajib/Opsional, dan memoderasi kursus.',
      steps: [
        {
          label: 'Header & Buat Course Baru',
          element: 'main header',
          popover: {
            title: '1. Ringkasan & Tombol Tambah Kursus',
            description: 'Menampilkan judul manajemen kursus dan tombol "+ Buat Course Baru" untuk menyiapkan modul kurikulum pelatihan baru.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '3 Kartu KPI Kursus',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Ringkasan Total & Kategori Kursus',
            description: 'Pantau total kursus terdaftar, kursus berkategori Wajib (kompetensi dasar industri), dan kursus Opsional (pengembangan keahlian mandiri).',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Filter Kategori & Pencarian',
          element: '#courses-list',
          popover: {
            title: '3. Filter Kategori & Pencarian Cepat',
            description: 'Gunakan tab kategori (Semua, Wajib, Opsional) serta kolom pencarian untuk menyaring kursus berdasarkan kode modul atau judul pelatihan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Grid Kartu Kursus',
          element: '#courses-list + div, main .grid:has(article), main article',
          popover: {
            title: '4. Kartu Kursus & Informasi Pengampu',
            description: 'Setiap kartu menampilkan foto sampul resmi, kode unik, kategori, trainer pengampu, jumlah sesi pertemuan, dan total materi aktif.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Aksi Kursus & Sesi Pertemuan',
          element: 'main article a, main article button',
          popover: {
            title: '5. Kelola Sesi Pertemuan & Opsi Kursus',
            description: 'Klik judul kursus atau tombol "Kelola Sesi" untuk masuk ke silabus sesi pertemuan. Gunakan tombol Ubah / Hapus sesuai hak akses Anda.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Pagination Kontrol',
          element: 'nav[aria-label="Navigasi halaman"], main [class*="Pagination"]',
          popover: {
            title: '6. Navigasi Halaman & Jumlah Tampilan',
            description: 'Atur jumlah kartu yang ditampilkan per halaman (8, 16, 32 kursus) dan navigasi antar halaman dengan mudah.',
            side: 'top',
            align: 'center'
          }
        }
      ]
    };
  }

  // 3. Create Course Form
  if (currentPath === '/courses/new') {
    return {
      pageName: 'Buat Course Baru',
      roleSummary: isTeacher
        ? 'Trainer dapat menyusun silabus kursus baru yang otomatis terhubung ke profil pengampu Anda.'
        : 'Admin dapat mendaftarkan modul kursus baru, menentukan kategori Wajib/Opsional, dan menugaskan Trainer pengampu.',
      steps: [
        {
          label: 'Formulir Registrasi Kursus',
          element: 'main form',
          popover: {
            title: '1. Formulir Kurikulum Pelatihan Baru',
            description: 'Lengkapi seluruh data kurikulum baru: Kode Unik (contoh: DENSO-MFG-01), Judul Kursus, Deskripsi Silabus, Kategori (Wajib/Opsional), dan Penugasan Trainer.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Unggah Sampul & Simpan',
          element: 'main form button[type="submit"]',
          popover: {
            title: '2. Simpan & Mulai Mengisi Sesi',
            description: 'Tekan tombol "Simpan Course" untuk mendaftarkan modul ke database. Setelah tersimpan, Anda akan langsung diarahkan ke halaman pengelolaan sesi pertemuan.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 4. Course Detail & Sessions
  if (currentPath.startsWith('/courses/view/') || currentPath.startsWith('/courses/')) {
    return {
      pageName: 'Detail Kursus & Manajemen Sesi',
      roleSummary: isTeacher
        ? 'Kelola sesi pertemuan, unggah foto sampul, dan pantau kesiapan materi pembelajaran pada kelas binaan Anda.'
        : 'Admin dapat meninjau detail silabus, mengganti penugasan Trainer, mengedit sampul, dan mengelola seluruh sesi pertemuan.',
      steps: [
        {
          label: 'Banner & Sampul Kursus',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Informasi Kurikulum & Foto Sampul',
            description: 'Menampilkan kode unik kursus, kategori pelatihan, nama trainer pengampu, dan foto sampul resmi. Klik tombol "Edit Kursus" atau "Unggah Sampul" untuk memperbarui data.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Manajemen Sesi Pertemuan',
          element: '#course-sessions',
          popover: {
            title: '2. Daftar Sesi & Urutan Pertemuan',
            description: 'Kelola sesi pertemuan (Sesi 1, Sesi 2, dst.). Anda dapat menambahkan sesi baru, menyusun urutan materi, dan mengubah judul fokus sesi.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Accordion Materi Pembelajaran',
          element: '#course-sessions article, #course-sessions div:has(h3)',
          popover: {
            title: '3. Konten Materi per Sesi',
            description: 'Buka accordion setiap sesi untuk melihat materi yang terhubung (Video MP4, Dokumen PDF, Teks Pelatihan, atau Link Interaktif). Klik judul materi untuk melihat pratinjaunya.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // 5. Materials Management & Approvals
  if (currentPath === '/materials') {
    return {
      pageName: 'Kelola Materi & Alur Persetujuan',
      roleSummary: isTeacher
        ? 'Trainer mengajukan materi pelatihan baru (video <= 250 MB, PDF, teks), memantau status tinjauan, dan mengirimkan revisi perbaikan.'
        : 'Admin memeriksa pra-review materi, menugaskan Reviewer independen, mengalihkan reviewer, dan menerbitkan materi final ke portal siswa.',
      steps: [
        {
          label: 'Header & Ajukan Materi Baru',
          element: 'main header',
          popover: {
            title: '1. Header Operasional & Pengajuan Materi',
            description: isTeacher
              ? 'Klik tombol "+ Ajukan Materi Baru" untuk mengunggah materi pembelajaran baru (Video, Dokumen PDF, Teks Pelatihan, atau Tautan Interaktif).'
              : 'Admin dapat memantau antrean persetujuan materi yang diajukan oleh trainer seluruh divisi.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '5 Kartu Alur Status Materi',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Ringkasan Siklus Verifikasi 5 Tahap',
            description: 'Pantau materi pada setiap tahap: Menunggu Admin, Sedang Direview, Perlu Revisi, Materi Final Terbit, dan Total Seluruh Pengajuan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tab Filter Status & Pencarian',
          element: '#materials-submissions',
          popover: {
            title: '3. Tab Filter Alur & Pencarian Cepat',
            description: 'Gunakan tab status untuk menyaring antrean berdasarkan statusnya, atau gunakan kolom pencarian untuk melacak judul materi atau nama trainer.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Kartu Pengajuan Materi (Klik untuk Pratinjau)',
          element: '#materials-submissions article',
          popover: {
            title: '4. Kartu Materi Interaktif & Media Cover',
            description: 'Klik di mana saja pada kartu materi untuk membuka modal pratinjau lengkap (pemutar video, pembaca PDF, atau teks materi). Anda juga dapat membaca catatan evaluasi reviewer di sini.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Katalog Materi Final Terbit',
          element: '#published-materials-section',
          popover: {
            title: '5. Katalog Materi Final Terbit',
            description: 'Kumpulan seluruh materi yang telah diverifikasi dan disetujui, siap diakses dan dipelajari langsung oleh seluruh peserta pelatihan di portal belajar.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Kontrol Pagination Materi',
          element: 'main [class*="Pagination"]',
          popover: {
            title: '6. Navigasi Halaman Materi',
            description: 'Pindah halaman antrean pengajuan atau katalog materi terbit dengan navigasi pagination yang bersih dan responsif.',
            side: 'top',
            align: 'center'
          }
        }
      ]
    };
  }

  // 6. Quizzes Management
  if (currentPath === '/quizzes') {
    return {
      pageName: 'Kelola Quiz & Evaluasi Akhir',
      roleSummary: isTeacher
        ? 'Trainer menyusun butir soal pilihan ganda, batas nilai kelulusan (passing score), dan memantau percobaan siswa.'
        : 'Admin dapat meninjau seluruh paket kuis kursus, menerbitkan kuis, atau menghapus evaluasi yang sudah usang.',
      steps: [
        {
          label: 'Header & Ringkasan Quiz',
          element: 'main header',
          popover: {
            title: '1. Manajemen Evaluasi Akhir Kursus',
            description: 'Kelola kuis pilihan ganda yang wajib diselesaikan siswa sebagai syarat kelulusan dan penerbitan sertifikat.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '4 Kartu KPI Evaluasi',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Metrik Kuis, Status & Percobaan Peserta',
            description: 'Pantau total kursus terdaftar, kuis yang berstatus Terbit, kuis Draft/Belum Dibuat, dan akumulasi total percobaan pengerjaan oleh siswa.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Pencarian & Filter Status',
          element: '#quizzes-list',
          popover: {
            title: '3. Filter Status (Terbit / Draft) & Pencarian',
            description: 'Saring kursus yang kuisnya sudah siap diuji atau yang masih berstatus draft, serta cari berdasarkan judul atau kode kursus.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Kartu Quiz per Course (Klik untuk Detail)',
          element: '#quizzes-list article',
          popover: {
            title: '4. Kartu Quiz Interaktif & Detail Butir Soal',
            description: 'Klik pada kartu untuk membuka Modal Detail Quiz: lihat judul kuis, passing score, jumlah soal, total peserta, dan rincian opsi pilihan ganda A-D beserta kunci jawaban yang benar.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Tombol Buat / Edit Quiz',
          element: '#quizzes-list button:has(svg)',
          popover: {
            title: '5. Editor Pengaturan & Soal Pilihan Ganda',
            description: 'Klik tombol "Buat Quiz" atau "Edit Quiz" untuk membuka editor: atur passing score, tulis soal, lengkapi pilihan jawaban A-D, dan tentukan opsi jawaban yang benar.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // 7. Grades Management
  if (currentPath === '/grades') {
    return {
      pageName: 'Kelola Nilai & Capaian Peserta',
      roleSummary: isTeacher
        ? 'Trainer memantau progres belajar siswa pada kelas ampuan, menginput Nilai Akhir (0–100), mengekspor Excel, dan mengirim sertifikat.'
        : 'Admin dapat meninjau rekapitulasi nilai seluruh peserta lintas kursus, menginput nilai, mengekspor laporan, dan memvalidasi kelulusan.',
      steps: [
        {
          label: 'Header & Ekspor Laporan Excel/CSV',
          element: 'main header',
          popover: {
            title: '1. Header Rekapitulasi & Ekspor Laporan',
            description: 'Gunakan tombol "Ekspor Laporan" untuk mengunduh rekapitulasi nilai seluruh peserta ke format Microsoft Excel (.xlsx) atau CSV yang siap diarsipkan ke HRD.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '4 Kartu KPI Nilai & Progres',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Statistik Capaian & Rata-rata Skor',
            description: 'Pantau total baris rekap nilai, jumlah peserta yang telah menuntaskan kursus, rata-rata persentase progres belajar, dan rata-rata skor kuis.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Filter Kursus & Status Kelulusan',
          element: '#grades-list',
          popover: {
            title: '3. Filter Kursus, Status Kelulusan & Pencarian',
            description: 'Pilih kursus spesifik dari dropdown, saring status (Course Selesai vs Sedang Berjalan), dan cari nama siswa atau NPK tertentu.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Rekap Nilai Peserta (Klik Baris untuk Detail)',
          element: '#grades-list table tbody tr',
          popover: {
            title: '4. Baris Data Interaktif & Modal Detail Nilai',
            description: 'Klik baris siswa mana saja untuk membuka Modal Detail Nilai: periksa capaian skor kuis, progres persentase belajar, catatan feedback instruktur, dan waktu pembaruan.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Input Nilai Akhir Instruktur',
          element: '#grades-list input[type="number"], #grades-list td:has(input)',
          popover: {
            title: '5. Input Langsung Nilai Akhir (0–100)',
            description: 'Ketik nilai evaluasi akhir kursus langsung pada kolom input tabel, lalu tekan tombol centang simpan untuk memperbarui nilai siswa ke sistem.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // 8. Certificates & Awards
  if (currentPath === '/certificates') {
    return {
      pageName: 'Sertifikat & Dokumen Penghargaan',
      roleSummary: isTeacher
        ? 'Trainer dapat menerbitkan dan mengirim sertifikat pelatihan digital kepada siswa yang lulus pada kelas ampuan Anda.'
        : 'Admin dapat menerbitkan sertifikat/penghargaan, mempratinjau dokumen, mengunduh arsip PDF, dan memvalidasi keabsahan nomor sertifikat.',
      steps: [
        {
          label: 'Header & Kirim Sertifikat Baru',
          element: 'main > div > div:first-child',
          popover: {
            title: '1. Formulir Penerbitan Sertifikat / Penghargaan',
            description: 'Klik tombol "+ Kirim Sertifikat / Penghargaan" untuk memilih siswa penerima, kursus terkait, mengunggah berkas PDF/gambar, dan memasukkan nomor seri unik sertifikat.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '3 Kartu KPI Sertifikat',
          element: 'main .grid-cols-1.sm\\:grid-cols-3',
          popover: {
            title: '2. Ringkasan Dokumen Terkirim',
            description: 'Pantau total dokumen yang telah diterbitkan, total sertifikat kelulusan kursus, serta piagam penghargaan dan dokumen resmi lainnya.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Pencarian & Filter Jenis Dokumen',
          element: 'main input[placeholder*="nomor sertifikat"], main select',
          popover: {
            title: '3. Pencarian Berkas & Filter Kategori Dokumen',
            description: 'Cari berdasarkan nama siswa, NPK, nomor sertifikat, atau saring jenis penghargaan (Sertifikat Pelatihan, Penghargaan / Apresiasi, Dokumen Resmi).',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Sertifikat Terbit (Klik Baris untuk Detail)',
          element: 'main table tbody tr',
          popover: {
            title: '4. Baris Interaktif & Modal Detail Dokumen',
            description: 'Klik baris data untuk membuka Modal Detail Dokumen: lihat nomor seri, nama siswa penerima & NPK, kursus terkait, instruktur penerbit, tanggal terbit, dan deskripsi.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Aksi Buka, Unduh & Hapus Dokumen',
          element: 'main table td:last-child',
          popover: {
            title: '5. Aksi Buka File, Download PDF, dan Hapus',
            description: 'Gunakan tombol "Lihat" untuk membuka dokumen pada tab peramban baru, tombol "Unduh" untuk menyimpan berkas PDF, atau tombol "Hapus" jika terjadi kekeliruan.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 9. Users Directory
  if (currentPath === '/users') {
    return {
      pageName: 'Manajemen Pengguna & Akun',
      roleSummary: 'Admin memiliki akses penuh untuk mendaftarkan akun baru, impor massal via CSV, ekspor data, mengedit profil, dan mengatur status aktif.',
      steps: [
        {
          label: 'Header & Tambah Pengguna Baru',
          element: 'main header',
          popover: {
            title: '1. Direktori Pengguna & Pendaftaran Akun',
            description: 'Menampilkan judul kelola pengguna dan tombol "+ Tambah Pengguna" untuk mendaftarkan akun baru satuan ke sistem LMS.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '4 Kartu KPI Distribusi Akun',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Statistik Pengguna per Peran Sistem',
            description: 'Pantau jumlah total akun terdaftar, akun Siswa (Student), Pengajar (Teacher), Peninjau (Reviewer), dan Administrator.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tab Filter Role & Pencarian',
          element: '#users-list',
          popover: {
            title: '3. Filter Tab Peran & Pencarian Cepat',
            description: 'Beralih antara tab role (Semua, Student, Teacher, Reviewer, Admin) dan ketik NPK, nama lengkap, atau departemen pada kolom pencarian.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Impor Massal & Ekspor CSV',
          element: '#add-user, main .flex.flex-wrap:has(button)',
          popover: {
            title: '4. Impor Massal CSV & Ekspor Data Pengguna',
            description: 'Unduh template CSV resmi, lakukan impor data ratusan karyawan sekaligus yang diproses background worker aman, atau ekspor data ke format CSV.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Direktori Pengguna (Klik Baris untuk Detail)',
          element: '#users-list table tbody tr',
          popover: {
            title: '5. Baris Interaktif & Modal Detail Profil Lengkap',
            description: 'Klik baris pengguna mana saja untuk membuka Modal Detail Profil: lihat inisial avatar, NPK, nama lengkap, role, email, nomor HP, departemen, tanggal dibuat, dan tombol ubah.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Aksi Ubah & Nonaktifkan Akun',
          element: '#users-list td:last-child',
          popover: {
            title: '6. Menu Aksi Pengguna',
            description: 'Gunakan tombol di ujung kanan baris untuk mengubah data profil, mereset kata sandi, atau menonaktifkan status akses akun.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 10. Enrollments Approvals
  if (currentPath === '/enrollments') {
    return {
      pageName: 'Persetujuan Pendaftaran Course',
      roleSummary: isTeacher
        ? 'Trainer dapat mengajukan rekomendasi pendaftaran siswa binaan agar masuk ke antrean persetujuan Admin.'
        : 'Admin meninjau permintaan akses mandiri dari siswa maupun rekomendasi Trainer, lalu menyetujui atau menolak per baris atau sekaligus.',
      steps: [
        {
          label: 'Header & Ringkasan Antrean',
          element: 'main header',
          popover: {
            title: '1. Manajemen Persetujuan Akses Pelatihan',
            description: 'Memoderasi pendaftaran kursus karyawan sebelum peserta dapat mengakses materi pembelajaran dan kuis.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '3 Kartu Status Permintaan',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Indikator Status Permintaan Pendaftaran',
            description: 'Pantau jumlah pendaftaran yang Menunggu Keputusan (Pending), Disetujui (Approved), dan Ditolak (Rejected).',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Formulir Rekomendasi Siswa',
          element: '#recommend-student',
          popover: {
            title: '3. Formulir Rekomendasi Peserta oleh Trainer',
            description: 'Trainer dapat memilih siswa dan kursus tujuan, menambahkan catatan instruktur, lalu mengirim rekomendasi agar siswa langsung didaftarkan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Permintaan (Klik Baris untuk Detail)',
          element: '#enrollments-list table tbody tr',
          popover: {
            title: '4. Baris Interaktif & Modal Detail Permintaan',
            description: 'Klik baris permintaan untuk membuka Modal Detail Enrollment: periksa NPK siswa, judul kursus, jalur pendaftaran, catatan pengajuan siswa, dan catatan keputusan admin.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Tombol Aksi Setujui / Tolak',
          element: '#enrollments-list td:last-child, #enrollments-list button',
          popover: {
            title: '5. Eksekusi Keputusan Satuan atau Sekaligus',
            description: 'Klik tombol centang hijau untuk menyetujui atau tombol silang merah untuk menolak, atau gunakan tombol "Setujui Semua Permintaan Pending" untuk memproses massal.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 11. Reviewers Management
  if (currentPath === '/reviewers') {
    return {
      pageName: 'Kelola Reviewer Materi',
      roleSummary: 'Admin mengelola akun peninjau materi independen, mengirim ulang kredensial akses via email, dan mengalihkan tugas review aktif.',
      steps: [
        {
          label: 'Header & Tambah Reviewer Baru',
          element: 'main header',
          popover: {
            title: '1. Kelola Peninjau Materi Independen',
            description: 'Klik tombol "+ Tambah Reviewer Baru" untuk mendaftarkan peninjau materi dari divisi Quality Assurance atau manajemen mutu.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '3 Kartu KPI Reviewer',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Beban Kerja & Status Reviewer',
            description: 'Pantau jumlah Reviewer Aktif, Reviewer Nonaktif, dan akumulasi total tugas peninjauan materi yang telah diselesaikan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Reviewer (Klik Baris untuk Detail)',
          element: '#reviewers-list table tbody tr',
          popover: {
            title: '3. Baris Interaktif & Modal Detail Reviewer',
            description: 'Klik baris reviewer untuk membuka Modal Detail Reviewer: lihat nama lengkap, jabatan, departemen, email, no HP, beban review aktif, dan total riwayat review.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Aksi Alih Tugas & Kirim Email Kredensial',
          element: '#reviewers-list td:last-child',
          popover: {
            title: '4. Menu Aksi Reviewer',
            description: 'Ubah profil reviewer, kirim ulang detail login via email resmi, atau gunakan fitur "Alihkan Tugas" untuk memindahkan seluruh review yang sedang pending ke reviewer lain bila reviewer berhalangan.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 12. Forum Discussion
  if (currentPath === '/forum') {
    return {
      pageName: 'Forum Diskusi Sesi Pelatihan',
      roleSummary: isTeacher
        ? 'Trainer mengontrol sakelar buka/tutup diskusi sesi, membuat topik arahan, dan menjawab pertanyaan siswa secara real-time.'
        : 'Admin dapat memoderasi seluruh diskusi sesi pelatihan, mengunci utas, menyembunyikan konten tidak pantas, dan membalas pesan.',
      steps: [
        {
          label: 'Pemilihan Kursus & Sesi Pertemuan',
          element: 'main select, #forum-course-select',
          popover: {
            title: '1. Pilih Ruang Diskusi Kursus & Sesi',
            description: 'Pilih kursus dan sesi pertemuan spesifik yang ingin Anda buka atau periksa diskusinya.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Sakelar Buka / Tutup Forum',
          element: 'main label:has(input[type="checkbox"])',
          popover: {
            title: '2. Kontrol Akses Forum Sesi',
            description: 'Aktifkan atau nonaktifkan sakelar ini. Jika ditutup, siswa hanya dapat membaca arsip diskusi tanpa bisa mengirimkan pertanyaan baru.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Mulai Topik Utas Baru (WebP Image)',
          element: '#new-thread',
          popover: {
            title: '3. Buat Utas Baru dengan Lampiran Gambar',
            description: 'Buat topik diskusi baru lengkap dengan lampiran gambar (diagram mesin, alur SOP). Sistem otomatis mengonversi gambar ke format WebP yang efisien.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Utas & Balasan Real-Time (SSE)',
          element: '#forum-threads',
          popover: {
            title: '4. Aliran Diskusi & Balasan Langsung',
            description: 'Setiap pertanyaan dan balasan masuk secara instan tanpa perlu memuat ulang halaman berkat integrasi Server-Sent Events (SSE).',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Tombol Moderasi Utas',
          element: '#forum-threads button:has(svg)',
          popover: {
            title: '5. Moderasi: Kunci, Sembunyikan, atau Hapus',
            description: 'Gunakan tombol moderasi pada utas untuk mengunci diskusi (mencegah balasan baru), menyembunyikan sementara, atau menghapus utas.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 13. Notifications Center
  if (currentPath === '/notifications') {
    return {
      pageName: 'Pusat Notifikasi & Siaran Pengumuman',
      roleSummary: isTeacher
        ? 'Trainer dapat mengirim pengingat belajar kepada peserta pada kursus binaan Anda.'
        : 'Admin dapat menyiarkan pengumuman broadcast ke seluruh peran atau checklist anggota spesifik beserta popup modal dan email outbox.',
      steps: [
        {
          label: 'Header & Kirim Notifikasi Baru',
          element: 'main header',
          popover: {
            title: '1. Pusat Komunikasi & Pengumuman',
            description: 'Menampilkan penghitung notifikasi belum dibaca dan tombol "+ Kirim Notifikasi" untuk membuat siaran baru.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Formulir Target Audiens & Checklist Anggota',
          element: 'main header button:has(svg)',
          popover: {
            title: '2. Kirim ke Peran atau Checklist Anggota Spesifik',
            description: 'Pilih target berdasarkan Peran (Siswa, Teacher, Admin) ATAU pilih anggota spesifik melalui checklist interaktif lengkap dengan peninjauan detail profil anggota.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Opsi Lampiran WebP, Popup Modal & Email',
          element: 'main [role="dialog"]',
          popover: {
            title: '3. Lampiran Gambar WebP & Opsi Darurat',
            description: 'Sertakan foto pengumuman (otomatis dikonversi ke WebP). Anda dapat mencentang "Munculkan Popup Modal" agar pengumuman langsung tampil di layar siswa, atau "Kirim melalui Email".',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Filter Tab Kategori Notifikasi',
          element: 'main div:has(button[role="tab"])',
          popover: {
            title: '4. Tab Kategori Pemberitahuan',
            description: 'Saring notifikasi berdasarkan kategori: Semua, Belum Dibaca, Penting, Pengumuman, Course, Forum, atau Sistem.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Notifikasi (Klik Kartu untuk Popup Detail)',
          element: '#notifications-list article',
          popover: {
            title: '5. Kartu Notifikasi & Modal Detail Lengkap',
            description: 'Klik kartu notifikasi mana saja untuk membuka Modal Detail Pengumuman lengkap dengan pratinjau gambar resolusi tinggi dan isi pesan resmi.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Tandai Semua Dibaca & Hapus Riwayat',
          element: '#notifications-list header, main .flex:has(button[variant="outline"])',
          popover: {
            title: '6. Pemeliharaan Riwayat Pesan',
            description: 'Gunakan tombol "Tandai Semua Dibaca" untuk membersihkan tanda pesan baru, atau "Hapus Riwayat" untuk mengosongkan arsip notifikasi lama.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 14. Activity Logs
  if (currentPath === '/activity') {
    return {
      pageName: 'Log Aktivitas & Antrean Job Real-Time',
      roleSummary: 'Pantau audit jejak operasi seluruh pengguna portal dan progres eksekusi background worker secara transparan dan realtime.',
      steps: [
        {
          label: 'Header & Segarkan Log',
          element: 'main header',
          popover: {
            title: '1. Pemantauan Log Audit Sistem',
            description: 'Tekan tombol "Segarkan" untuk mengambil pembaruan log aktivitas terbaru dari server secara instan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: '4 Kartu KPI Log Aktivitas',
          element: 'main section:first-of-type',
          popover: {
            title: '2. Statistik Aktivitas & Keberhasilan Job',
            description: 'Pantau total catatan log, aktivitas hari ini, jumlah antrean background job yang berhasil, dan catatan kendala/kegagalan sistem.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tab Filter Status & Tipe Kejadian',
          element: '#activity-list',
          popover: {
            title: '3. Filter Status (Semua / Job / Gagal / Berhasil)',
            description: 'Gunakan tab filter untuk mengisolasi kendala sistem atau memeriksa nomor antrean job latar belakang, serta filter dropdown jenis aktivitas.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Log Aktivitas (Klik Baris untuk Detail)',
          element: '#activity-list article',
          popover: {
            title: '4. Baris Interaktif & Modal Detail Log Sistem',
            description: 'Klik baris aktivitas mana saja untuk membuka Modal Detail Log: periksa ID log, nomor antrean job, nama operator & NPK, target item, timestamp, dan pesan error/log lengkap.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // 15. Roles & RBAC
  if (currentPath === '/roles') {
    return {
      pageName: 'Role & Hak Akses Modular (RBAC)',
      roleSummary: 'Eksklusif Admin: konfigurasikan 31 izin granular per modul, terapkan preset instan, bandingkan pada matriks, dan periksa jejak audit keamanan.',
      steps: [
        {
          label: 'Pilihan Role & Sakelar Login Staf',
          element: '#roles-overview',
          popover: {
            title: '1. Pemilihan Peran & Sakelar Portal Staf',
            description: 'Pilih role yang ingin diatur (Student, Teacher, Reviewer, Admin), serta aktif/nonaktifkan izin masuk ke Portal Staf Pengelola bagi role tersebut.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Editor 31 Izin Akses Granular',
          element: '#roles-editor-panel',
          popover: {
            title: '2. Panel 31 Izin Modular per Modul',
            description: 'Sesuaikan hak akses spesifik per modul: Dashboard, Kursus, Materi, Kuis, Nilai, Forum, Notifikasi, Pengguna, Reviewer, hingga Backup. Gunakan tombol preset cepat untuk konfigurasi standar.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Matriks Perbandingan & Log Audit',
          element: 'button:has-text("Matriks"), [role="tab"]:last-child',
          popover: {
            title: '3. Matriks Perbandingan & Riwayat Perubahan',
            description: 'Buka tab Matriks Hak Akses untuk membandingkan izin seluruh role secara berdampingan, atau tinjau tab Riwayat Audit untuk melihat siapa admin yang mengubah hak akses.',
            side: 'top',
            align: 'center'
          }
        }
      ]
    };
  }

  // 16. Settings API & Sync
  if (currentPath === '/settings/api') {
    return {
      pageName: 'Pengaturan API & Sinkronisasi MySQL',
      roleSummary: 'Eksklusif Admin: kelola alamat endpoint API bersama, jalankan uji koneksi server, dan pantau sinkronisasi database MySQL Student.',
      steps: [
        {
          label: 'Endpoint API Bersama & Tes Server',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Konfigurasi Endpoint API & Uji Server',
            description: 'Tentukan URL dasar layanan backend bersama, simpan konfigurasi, lalu klik "Tes Koneksi" untuk memastikan server online dan merespons dengan cepat.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Sinkronisasi Dua Arah MySQL Student',
          element: 'main section:last-of-type',
          popover: {
            title: '2. Status Sinkronisasi MySQL Student',
            description: 'Pantau status background worker yang menyelaraskan data kursus, modul, dan progres siswa antara SQL Server dan MySQL Student. Klik "Sinkronkan Sekarang" untuk memicu sinkronisasi manual.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // 17. Settings Backup
  if (currentPath === '/settings/backup') {
    return {
      pageName: 'Pencadangan Data Sistem (Backup)',
      roleSummary: 'Eksklusif Admin: unduh berkas cadangan JSON untuk 14 kelompok tabel resmi DENSO LMS dengan perlindungan integritas.',
      steps: [
        {
          label: 'Pilihan 14 Dataset Tabel Resmi',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Pilih Dataset yang Ingin Dicadangkan',
            description: 'Centang dataset tabel yang ingin dicadangkan (Pengguna, Kursus, Sesi, Materi, Kuis, Nilai, Enrollment, Log, Konfigurasi RBAC), lalu klik Unduh Backup JSON.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Pintasan Pemulihan Data (Restore)',
          element: 'main section:last-of-type',
          popover: {
            title: '2. Menu Pemulihan Data Cadangan',
            description: 'Buka menu Pemulihan Data jika Anda perlu mengembalikan database dari berkas JSON cadangan yang telah dibuat sebelumnya.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // 18. Settings Restore
  if (currentPath === '/settings/backup/restore') {
    return {
      pageName: 'Pemulihan Data Cadangan (Restore)',
      roleSummary: 'Eksklusif Admin: unggah berkas JSON cadangan resmi untuk memulihkan data dengan validasi keamanan daftar putih tabel terenkripsi.',
      steps: [
        {
          label: 'Unggah Berkas Backup JSON',
          element: 'main form, main label:has(input[type="file"])',
          popover: {
            title: '1. Unggah Berkas JSON Cadangan',
            description: 'Pilih berkas JSON hasil ekspor backup resmi DENSO LMS. Sistem secara ketat memeriksa integritas struktur data sebelum proses pemulihan dimulai.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Validasi & Eksekusi Pemulihan',
          element: 'main button[type="submit"]',
          popover: {
            title: '2. Konfirmasi & Pulihkan Database',
            description: 'Tekan tombol "Pulihkan Data". Data yang dipulihkan akan diselaraskan secara aman tanpa merusak integritas foreign key sistem.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    };
  }

  // 19. Support Chat (Live Chat)
  if (currentPath === '/support-chat') {
    return {
      pageName: 'Pusat Bantuan (Live Chat Siswa)',
      roleSummary: 'Admin memantau pertanyaan masuk dari widget Live Chat siswa dan memberikan solusi teknis secara real-time.',
      steps: [
        {
          label: 'Antrean Percakapan Siswa',
          element: 'main aside',
          popover: {
            title: '1. Daftar Percakapan & Status Online',
            description: 'Pilih nama siswa di panel kiri untuk membuka riwayat pesan. Titik hijau menandakan siswa sedang aktif berada di portal belajar.',
            side: 'right',
            align: 'start'
          }
        },
        {
          label: 'Ruang Obrolan & Balas Pesan',
          element: 'main section',
          popover: {
            title: '2. Panel Balasan Real-Time & Riwayat Obrolan',
            description: 'Ketik pesan balasan dan tekan Kirim untuk menjawab pertanyaan siswa secara langsung, atau gunakan tombol bersihkan obrolan bila kendala telah selesai.',
            side: 'left',
            align: 'start'
          }
        }
      ]
    };
  }

  // 20. Help Center Page
  if (currentPath === '/help') {
    return {
      pageName: 'Pusat Panduan & Bantuan DENSO LMS',
      roleSummary: 'Dokumentasi operasional terpadu yang memandu seluruh prosedur pembelajaran, evaluasi, administrasi, dan tanya jawab (FAQ).',
      steps: [
        {
          label: 'Header & Tur Interaktif',
          element: 'main header',
          popover: {
            title: '1. Pusat Dokumentasi Terpadu',
            description: 'Gunakan tombol "Mulai Tur Interaktif Halaman" untuk mendapatkan panduan visual langkah demi langkah pada setiap halaman portal.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Pencarian Dokumentasi & Prosedur',
          element: 'main .border:has(input[type="text"]), main input[placeholder*="panduan"]',
          popover: {
            title: '2. Pencarian Instan Solusi Kendala',
            description: 'Ketik modul yang ingin dipelajari (misal: "Course", "Materi", "Quiz", "Nilai", "Sertifikat", "RBAC") untuk langsung menemukan panduannya.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Filter Peran Pengguna',
          element: 'main .flex:has(button)',
          popover: {
            title: '3. Filter Panduan Berdasarkan Peran',
            description: 'Saring panduan khusus untuk Administrator, Teacher/Trainer, Reviewer Materi, atau Student/Siswa.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Accordion Panduan Modul',
          element: '#overview',
          popover: {
            title: '4. Panduan Prosedur Langkah demi Langkah',
            description: 'Buka modul yang diinginkan untuk membaca uraian tahapan, alur kerja, dan tips operasional sistem secara gamblang.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Tanya Jawab yang Sering Diajukan (FAQ)',
          element: '#faq',
          popover: {
            title: '5. Tanya Jawab Cepat (FAQ)',
            description: 'Kumpulan jawaban atas pertanyaan operasional yang paling sering ditanyakan mengenai batas file video, sertifikat, dan akun.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  // Fallback generic
  return {
    pageName: 'Halaman Portal LMS',
    roleSummary: `Anda masuk sebagai ${getRoleLabel(role)}.`,
    steps: []
  };
}

function isElementVisible(el) {
  if (!el) return false;
  const style = window.getComputedStyle(el);
  if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0) {
    return false;
  }
  const rect = el.getBoundingClientRect();
  return rect.width > 0 && rect.height > 0;
}

function findVisibleElement(selectorOrEl) {
  if (!selectorOrEl) return null;
  if (typeof selectorOrEl !== 'string') {
    return isElementVisible(selectorOrEl) ? selectorOrEl : null;
  }
  const nodes = document.querySelectorAll(selectorOrEl);
  for (const node of nodes) {
    if (isElementVisible(node)) return node;
  }
  return null;
}

function resolveResponsiveSide(side) {
  const isMobile = window.innerWidth < 1024;
  if (isMobile && (side === 'left' || side === 'right')) {
    return 'bottom';
  }
  return side || 'bottom';
}

export function getPageGuideInfo(pathname, role) {
  const currentPath = (pathname || '/').split('?')[0].split('#')[0] || '/';
  const config = buildPageConfig(currentPath, role);

  const visibleSteps = [];
  for (const step of config.steps) {
    const el = step.element ? findVisibleElement(step.element) : null;
    if (!step.element || el) {
      visibleSteps.push({
        ...step,
        element: el || undefined,
        popover: {
          ...step.popover,
          side: resolveResponsiveSide(step.popover?.side)
        }
      });
    }
  }

  if (visibleSteps.length === 0) {
    const sections = Array.from(document.querySelectorAll('main section, main form, main article, main table')).filter(isElementVisible).slice(0, 6);
    sections.forEach((sec, idx) => {
      const heading = sec.querySelector('h1, h2, h3, h4')?.textContent?.trim();
      visibleSteps.push({
        label: heading || `Bagian ${idx + 1}`,
        element: sec,
        popover: {
          title: heading || `Bagian Halaman ${idx + 1}`,
          description: 'Bagian ini memuat data operasional, filter pencarian, tabel informasi, atau formulir interaktif pada modul yang sedang Anda buka.',
          side: 'top',
          align: 'start'
        }
      });
    });
  }

  return {
    pageName: config.pageName,
    roleLabel: getRoleLabel(role),
    roleSummary: config.roleSummary,
    sections: visibleSteps.map((s, idx) => ({
      index: idx,
      label: s.label || s.popover?.title || `Bagian ${idx + 1}`
    })),
    steps: visibleSteps
  };
}

let activeDriver = null;
let isDestroyingTour = false;

function animateTourSuckBack(driverInstance) {
  if (isDestroyingTour) return;
  isDestroyingTour = true;

  const finishDestroy = () => {
    try {
      driverInstance.destroy();
    } catch {}
    if (activeDriver === driverInstance) {
      activeDriver = null;
    }
    isDestroyingTour = false;
  };

  const popoverEl = document.querySelector('.driver-popover');
  const overlayEl = document.querySelector('.driver-overlay');
  const bubbleEl = document.getElementById('tour-guide-btn');

  if (!popoverEl || !bubbleEl) {
    finishDestroy();
    return;
  }

  const pRect = popoverEl.getBoundingClientRect();
  const bRect = bubbleEl.getBoundingClientRect();

  const dx = (bRect.left + bRect.width / 2) - (pRect.left + pRect.width / 2);
  const dy = (bRect.top + bRect.height / 2) - (pRect.top + pRect.height / 2);

  popoverEl.style.transition = 'transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease';
  popoverEl.style.transform = `translate(${dx}px, ${dy}px) scale(0.08)`;
  popoverEl.style.opacity = '0';

  if (overlayEl) {
    overlayEl.style.transition = 'opacity 0.35s ease';
    overlayEl.style.opacity = '0';
  }

  setTimeout(finishDestroy, 400);
}

export function startUserGuide(pathname, role, startIndex = 0) {
  if (activeDriver) {
    try {
      activeDriver.destroy();
    } catch {}
    activeDriver = null;
  }

  const guideInfo = getPageGuideInfo(pathname, role);
  if (!guideInfo.steps.length) return;

  const tourSteps = guideInfo.steps.map((step) => {
    return {
      element: step.element,
      popover: {
        title: step.popover?.title || step.label,
        description: step.popover?.description || '',
        side: step.popover?.side || 'bottom',
        align: step.popover?.align || 'start'
      }
    };
  });

  const d = driver({
    showProgress: true,
    animate: true,
    overlayColor: 'rgba(15, 20, 28, 0.72)',
    allowClose: true,
    nextBtnText: 'Lanjut →',
    prevBtnText: '← Kembali',
    doneBtnText: 'Selesai Panduan ✓',
    steps: tourSteps,
    onDestroyStarted: () => {
      animateTourSuckBack(d);
    }
  });

  activeDriver = d;
  d.drive(startIndex >= 0 && startIndex < tourSteps.length ? startIndex : 0);
}
