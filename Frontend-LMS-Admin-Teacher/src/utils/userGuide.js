import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/**
 * Driver.js Interactive & Role-Aware User Guide
 * untuk Portal Admin, Teacher, dan Reviewer (DENSO-LMS).
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

  if (currentPath === '/courses/new') {
    return {
      pageName: 'Buat Course Baru',
      roleSummary: isTeacher
        ? 'Teacher dapat membuat kursus baru yang otomatis terhubung ke profil pengampu Anda.'
        : 'Admin dapat membuat kursus baru, menentukan kategori, dan menugaskan Trainer pengampu.',
      steps: [
        {
          label: 'Formulir Course',
          element: 'main form',
          popover: {
            title: 'Formulir Pembuatan Course Baru',
            description: 'Lengkapi kode unik course, judul pelatihan, deskripsi, kategori (Wajib/Pilihan), foto sampul, lalu simpan untuk mulai mengisi sesi pertemuan.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  if (currentPath.startsWith('/courses/')) {
    return {
      pageName: 'Detail Course & Sesi Pertemuan',
      roleSummary: isTeacher
        ? 'Kelola sesi pertemuan, unggah foto sampul, dan pantau kesiapan materi pada kursus yang Anda ampu.'
        : 'Kelola detail silabus, penugasan Trainer, foto sampul, dan seluruh sesi pertemuan pada kursus ini.',
      steps: [
        {
          label: 'Info & Sampul Course',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Informasi Utama & Foto Sampul Course',
            description: 'Lihat identitas course, kategori, dan trainer pengampu. Anda dapat langsung menekan tombol Edit atau Unggah Sampul pada area ini.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Sesi Pertemuan',
          element: '#course-sessions',
          popover: {
            title: '2. Manajemen Sesi Pertemuan & Materi',
            description: 'Tambah sesi baru, atur urutan pertemuan, ubah judul sesi, dan periksa daftar materi yang terhubung ke setiap sesi.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  if (currentPath === '/users/new') {
    return {
      pageName: 'Registrasi Pengguna Baru',
      roleSummary: 'Admin dapat mendaftarkan akun baru untuk role Admin, Teacher, Reviewer, maupun Student.',
      steps: [
        {
          label: 'Formulir Akun Baru',
          element: 'main form',
          popover: {
            title: 'Formulir Registrasi Akun',
            description: 'Masukkan NPK, nama lengkap, email, departemen, kata sandi awal, dan pilih role pengguna sebelum menyimpan ke sistem.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  if (currentPath.startsWith('/review')) {
    return {
      pageName: 'Portal Reviewer Materi',
      roleSummary: 'Reviewer memeriksa isi materi (dokumen/video/teks), menulis catatan evaluasi, lalu memutuskan Setujui atau Minta Revisi.',
      steps: [
        {
          label: 'Info Sesi Review',
          element: 'header, main section:first-of-type',
          popover: {
            title: '1. Sesi Undangan Review Sekali Pakai',
            description: 'Tautan review berlaku terbatas (2 jam setelah dibuka). Gunakan tombol Akhiri Sesi jika seluruh pemeriksaan sudah selesai.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Antrean & Pratinjau Materi',
          element: 'main section:last-of-type, main',
          popover: {
            title: '2. Daftar Antrean & Panel Keputusan',
            description: 'Pilih materi di daftar antrean, periksa pratinjau kontennya, tulis catatan hasil tinjauan, lalu klik Setujui Materi atau Minta Revisi.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    };
  }

  const PAGES = {
    '/': {
      pageName: 'Dashboard Operasional',
      roleSummary: isTeacher
        ? 'Menampilkan statistik khusus kursus yang Anda ampu serta pintasan Mode Belajar Siswa (simulasi 30 menit).'
        : 'Menampilkan ringkasan seluruh data LMS secara real-time, tren aktivitas harian, alur persetujuan materi, dan aksi cepat.',
      steps: [
        {
          label: '4 Kartu KPI Utama',
          element: '#tour-overview',
          popover: {
            title: '1. Kartu Ringkasan Operasional',
            description: isTeacher
              ? 'Pantau Total Course Ampuan, Pengajuan Materi Anda, antrean Review, dan jumlah peserta aktif pada kelas Anda.'
              : 'Pantau 4 indikator utama sistem: Total Course, Pengajuan Materi, Menunggu Review, dan Total Pengguna terdaftar.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Grafik Tren & Status',
          element: '#tour-charts',
          popover: {
            title: '2. Grafik Tren Aktivitas & Alur Materi',
            description: 'Coba klik filter rentang waktu (7 / 14 / 30 hari) untuk melihat perubahan tren aktivitas harian dan distribusi status persetujuan materi.',
            side: 'top',
            align: 'center'
          }
        },
        {
          label: 'Aktivitas Terbaru',
          element: '#tour-recent-activity',
          popover: {
            title: '3. Log Aktivitas Terbaru',
            description: 'Menampilkan kejadian terkini di sistem secara langsung (login, pengajuan materi, keputusan review, dan enrollment).',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Aksi Cepat & Mode Belajar',
          element: '#quick-actions',
          popover: {
            title: '4. Panel Aksi Cepat',
            description: isTeacher
              ? 'Gunakan pintasan ini untuk langsung mengajukan materi, mengelola kuis, atau masuk ke Mode Belajar Siswa.'
              : 'Pintasan cepat untuk membuat course, memeriksa materi masuk, menyetujui enrollment, atau membuka pengaturan sistem.',
            side: 'top',
            align: 'end'
          }
        }
      ]
    },
    '/courses': {
      pageName: 'Kelola Course',
      roleSummary: isTeacher
        ? 'Teacher dapat membuat course baru dan mengelola seluruh sesi pada kursus yang diampu.'
        : 'Admin dapat melihat seluruh course, mencari & memfilter kategori, membuat course, menugaskan Trainer, hingga menghapus course.',
      steps: [
        {
          label: 'Ringkasan & Pencarian',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Statistik, Pencarian & Filter Kategori',
            description: 'Lihat total course Wajib dan Opsional. Coba ketik kode/judul course atau pilih filter kategori untuk menyaring daftar di bawah.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tombol Buat Course',
          element: '#create-course',
          popover: {
            title: '2. Tambah Course Baru',
            description: 'Klik tombol Buat Course Baru untuk menambahkan kurikulum baru lengkap dengan foto sampul dan penugasan trainer.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Kartu Course',
          element: '#courses-list',
          popover: {
            title: '3. Daftar & Aksi per Course',
            description: 'Klik judul atau tombol Kelola Sesi pada kartu course untuk mengatur pertemuan, atau gunakan tombol Ubah/Hapus sesuai hak akses.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/materials': {
      pageName: 'Kelola Materi',
      roleSummary: isTeacher
        ? 'Teacher mengajukan materi baru (teks/PDF/video <= 250 MB) dan mengirim ulang perbaikan apabila berstatus Perlu Revisi.'
        : 'Admin memeriksa pra-review materi masuk, mengirim tautan review ke Reviewer, mengalihkan reviewer, hingga menerbitkan materi final ke Portal Siswa.',
      steps: [
        {
          label: '5 Kartu Status Materi',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Ringkasan 5 Status Alur Materi',
            description: 'Pantau jumlah materi pada tiap tahap: Menunggu Admin, Sedang Direview, Perlu Revisi, Materi Final Terbit, dan Total Pengajuan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Antrean & Moderasi',
          element: '#materials-submissions',
          popover: {
            title: '2. Antrean Pengajuan & Tombol Moderasi',
            description: isTeacher
              ? 'Gunakan filter status untuk melacak pengajuan Anda, baca catatan koreksi reviewer, lalu klik Kirim Revisi jika diperlukan.'
              : 'Periksa pratinjau dokumen/video, lalu pilih Kirim ke Reviewer, Tolak Pra-Review, Alihkan Reviewer, atau Terbitkan materi.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Materi Final Terbit',
          element: '#published-materials-section',
          popover: {
            title: '3. Katalog Materi Final Terbit',
            description: 'Berisi seluruh materi yang sudah lolos verifikasi dan langsung tersedia bagi siswa di ruang belajar.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/quizzes': {
      pageName: 'Kelola Quiz',
      roleSummary: isTeacher
        ? 'Teacher menyusun soal pilihan ganda, batas nilai kelulusan, dan durasi ujian untuk kursus ampuan.'
        : 'Admin dapat mengelola paket soal quiz pada seluruh kursus, menerbitkan, maupun menghapus evaluasi.',
      steps: [
        {
          label: 'Statistik & Filter Quiz',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Statistik Evaluasi & Pencarian',
            description: 'Lihat jumlah quiz Terbit, Draft/Kosong, total butir soal, dan rata-rata batas kelulusan. Gunakan filter untuk memilih kursus.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar Paket Quiz',
          element: '#quizzes-list',
          popover: {
            title: '2. Daftar Quiz per Course',
            description: 'Klik salah satu course untuk membuka editor quiz, mengatur status Terbit/Draft, atau meninjau jumlah soalnya.',
            side: 'top',
            align: 'start'
          }
        },
        {
          label: 'Editor Butir Soal',
          element: '#quiz-editor-title',
          popover: {
            title: '3. Editor Pengaturan & Soal Pilihan Ganda',
            description: 'Atur judul quiz, passing score, durasi menit, tambah/edit pertanyaan pilihan ganda, lalu simpan dan terbitkan.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/grades': {
      pageName: 'Kelola Nilai',
      roleSummary: isTeacher
        ? 'Teacher memantau progres & skor quiz siswa pada kelas ampuan, mengisi Nilai Akhir (0–100), mengekspor CSV, dan menerbitkan sertifikat.'
        : 'Admin dapat meninjau seluruh nilai peserta lintas kursus, memperbarui Nilai Akhir, mengekspor laporan Excel/CSV, dan mengirim sertifikat.',
      steps: [
        {
          label: 'Statistik & Ekspor Nilai',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Ringkasan Capaian, Filter & Ekspor Laporan',
            description: 'Pantau total baris nilai, course selesai, rata-rata progres & nilai quiz. Gunakan filter course/status atau tombol Ekspor Excel/CSV.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Nilai & Sertifikat',
          element: '#grades-list',
          popover: {
            title: '2. Tabel Rekap Nilai & Input Nilai Akhir',
            description: 'Isi atau perbarui Nilai Akhir Course (0–100) per siswa, lalu klik Simpan atau Kirim Sertifikat bila peserta telah menuntaskan kursus.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/certificates': {
      pageName: 'Sertifikat & Penghargaan',
      roleSummary: isTeacher
        ? 'Teacher dapat menerbitkan dan mengirim sertifikat kelulusan kepada siswa pada kursus yang diampu.'
        : 'Admin dapat mengirim, mempratinjau, mengunduh ulang, dan menghapus seluruh sertifikat & penghargaan siswa.',
      steps: [
        {
          label: 'Kirim Sertifikat Baru',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Formulir Penerbitan Sertifikat',
            description: 'Pilih siswa, course, jenis dokumen (Sertifikat/Penghargaan), lalu unggah berkas PDF/gambar untuk dikirim langsung ke menu Riwayat siswa.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Arsip Sertifikat Terbit',
          element: 'main section:last-of-type',
          popover: {
            title: '2. Daftar Sertifikat & Penghargaan Terbit',
            description: 'Cari sertifikat berdasarkan nama/NPK siswa, pratinjau dokumen, unduh ulang PDF, atau hapus jika ada kesalahan unggah.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/users': {
      pageName: 'Kelola Pengguna',
      roleSummary: isAdmin
        ? 'Admin memiliki akses penuh untuk menambah, mengubah, menghapus, serta mengimpor/mengekspor pengguna via CSV.'
        : 'Teacher dapat melihat daftar peserta yang terdaftar pada kursus binaan Anda.',
      steps: [
        {
          label: 'Statistik & Filter Akun',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Ringkasan Akun & Filter Pencarian',
            description: 'Lihat distribusi akun per role dan status aktif. Ketik nama/NPK atau pilih tab role untuk menyaring daftar.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tambah & Impor CSV',
          element: '#add-user',
          popover: {
            title: '2. Tambah Pengguna, Impor Massal & Ekspor CSV',
            description: 'Daftarkan akun baru satuan, unduh template CSV, impor massal (diproses background worker terenkripsi AES-256-GCM), atau ekspor data CSV.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Direktori Pengguna',
          element: '#users-list',
          popover: {
            title: '3. Tabel Data Pengguna',
            description: 'Lihat detail NPK, email, departemen, role, dan gunakan tombol aksi di sebelah kanan untuk mengedit atau menonaktifkan akun.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/enrollments': {
      pageName: 'Persetujuan Course',
      roleSummary: isTeacher
        ? 'Teacher dapat mengajukan rekomendasi enrollment siswa agar masuk ke antrean persetujuan Admin.'
        : 'Admin meninjau permintaan akses mandiri dari siswa maupun rekomendasi dari Teacher, lalu menyetujui/menolak per baris atau sekaligus.',
      steps: [
        {
          label: 'Statistik Permintaan',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Status Permintaan Enrollment',
            description: 'Pantau jumlah permintaan Menunggu Keputusan, Disetujui, dan Ditolak, serta gunakan filter status di bagian ini.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Rekomendasi Siswa',
          element: '#recommend-student',
          popover: {
            title: '2. Formulir Rekomendasi Peserta',
            description: 'Cari NPK/nama siswa, pilih course tujuan, tambahkan catatan singkat, lalu klik Kirim Rekomendasi.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Antrean Persetujuan',
          element: '#enrollments-list',
          popover: {
            title: '3. Tabel Keputusan Enrollment',
            description: 'Setujui atau tolak permintaan satu per satu, atau klik Setujui Semua untuk memproses seluruh antrean pending sekaligus.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/reviewers': {
      pageName: 'Kelola Reviewer',
      roleSummary: 'Admin mengelola akun peninjau materi independen, mengirim ulang kredensial email, dan mengalihkan tugas review aktif.',
      steps: [
        {
          label: 'Statistik & Tambah Reviewer',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Ringkasan Reviewer & Tambah Akun Baru',
            description: 'Pantau total Reviewer Aktif, Nonaktif, dan materi yang sedang ditugaskan, atau klik Tambah Reviewer Baru.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Daftar & Alih Tugas',
          element: '#reviewers-list',
          popover: {
            title: '2. Tabel Reviewer & Pengalihan Tugas',
            description: 'Ubah status aktif, kirim ulang detail login via email, alihkan seluruh tugas review aktif ke reviewer lain, atau hapus akun.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/forum': {
      pageName: 'Forum Diskusi',
      roleSummary: isTeacher
        ? 'Teacher dapat membuka/menutup diskusi sesi pada kelas ampuan, membuat topik arahan, dan menjawab pertanyaan peserta.'
        : 'Admin dapat memoderasi seluruh diskusi sesi, membuka/menutup forum, mengunci atau menyembunyikan utas, dan membalas pertanyaan.',
      steps: [
        {
          label: 'Kontrol Sesi & Topik Baru',
          element: '#new-thread',
          popover: {
            title: '1. Pilih Sesi, Sakelar Forum & Buat Topik',
            description: 'Pilih course dan sesi pertemuan, atur apakah diskusi sesi Dibuka/Ditutup, dan buat topik baru lengkap dengan lampiran gambar.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Utas & Moderasi',
          element: '#forum-threads',
          popover: {
            title: '2. Daftar Utas & Balasan Real-Time',
            description: 'Baca pertanyaan siswa, kirim balasan instan (live SSE), serta gunakan tombol Kunci/Sembunyikan/Hapus untuk moderasi diskusi.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/notifications': {
      pageName: 'Notifikasi & Pengumuman',
      roleSummary: isTeacher
        ? 'Teacher dapat mengirim pengingat belajar kepada peserta pada kursus yang Anda ampu.'
        : 'Admin dapat mengirim pengumuman broadcast ke seluruh role beserta opsi popup modal dan antrean email outbox.',
      steps: [
        {
          label: 'Kirim Pengumuman',
          element: '#send-notification',
          popover: {
            title: '1. Formulir Kirim Notifikasi / Broadcast',
            description: 'Pilih target audiens, atur tingkat prioritas, isi pesan pengumuman, lalu aktifkan opsi Tampilkan Popup atau Kirim Email bila perlu.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Riwayat Terkirim',
          element: '#notifications-list',
          popover: {
            title: '2. Riwayat Notifikasi Terkirim',
            description: 'Periksa kembali seluruh pengumuman yang sudah dikirim beserta target role, waktu kirim, dan status prioritasnya.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/support-chat': {
      pageName: 'Pusat Bantuan (Live Chat)',
      roleSummary: 'Admin memantau pertanyaan masuk dari widget Live Chat Portal Siswa dan membalas kendala secara real-time.',
      steps: [
        {
          label: 'Daftar Siswa & Status Online',
          element: 'main aside, main > div > div:first-child',
          popover: {
            title: '1. Antrean Percakapan & Status Online',
            description: 'Pilih nama siswa di sebelah kiri untuk membuka riwayat chat. Kehadiran Anda otomatis terbaca sebagai Admin Online di Portal Siswa.',
            side: 'right',
            align: 'start'
          }
        },
        {
          label: 'Panel Balas Live Chat',
          element: 'main section, main > div > div:last-child',
          popover: {
            title: '2. Ruang Balas & Bersihkan Riwayat',
            description: 'Ketik balasan dan tekan Kirim untuk menjawab siswa secara langsung, atau bersihkan riwayat obrolan jika kendala telah selesai.',
            side: 'left',
            align: 'start'
          }
        }
      ]
    },
    '/activity': {
      pageName: 'Log Aktivitas',
      roleSummary: 'Pantau seluruh jejak audit operasi pengguna dan riwayat pekerjaan latar belakang sistem secara transparan.',
      steps: [
        {
          label: 'Filter & Pencarian Log',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Filter Jenis Aksi & Pencarian Audit',
            description: 'Saring catatan berdasarkan tipe aktivitas, NPK pelaku, atau kata kunci kejadian tertentu.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Tabel Log Aktivitas',
          element: '#activity-list',
          popover: {
            title: '2. Tabel Riwayat Aktivitas Sistem',
            description: 'Menampilkan detail pelaku, jenis tindakan, entitas terdampak, serta stempel waktu setiap aksi penting.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/roles': {
      pageName: 'Role & Hak Akses (RBAC)',
      roleSummary: 'Eksklusif Admin: atur 31 izin granular per role, bandingkan pada matriks, dan periksa riwayat audit perubahan hak akses.',
      steps: [
        {
          label: 'Ringkasan Role & Login Portal',
          element: '#roles-overview',
          popover: {
            title: '1. Pemilihan Role & Sakelar Login Portal Staf',
            description: 'Pilih role yang ingin dikonfigurasi dan aktif/nonaktifkan izin masuk ke Portal Staf untuk role tersebut.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Editor 31 Izin & Matriks',
          element: '#roles-editor-panel',
          popover: {
            title: '2. Editor Granular, Preset, Matriks & Log Audit',
            description: 'Ubah 31 sakelar izin per modul, terapkan preset cepat, buka tab Matriks Perbandingan, atau tinjau tab Riwayat Audit.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/settings/api': {
      pageName: 'Atur API & Sinkronisasi',
      roleSummary: 'Eksklusif Admin: kelola URL layanan API bersama, uji koneksi server, dan picu sinkronisasi data MySQL Student.',
      steps: [
        {
          label: 'Endpoint & Tes Koneksi',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Konfigurasi Endpoint & Tes Server',
            description: 'Atur alamat endpoint API bersama, simpan konfigurasi, lalu jalankan Tes Koneksi untuk memeriksa kesehatan layanan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Sinkronisasi MySQL',
          element: 'main section:last-of-type',
          popover: {
            title: '2. Status Worker & Sinkronisasi MySQL',
            description: 'Pantau jadwal sinkronisasi katalog kursus & progres siswa antara SQL Server dan MySQL, atau klik Sinkronkan Sekarang.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    },
    '/settings/backup': {
      pageName: 'Backup & Restore Data',
      roleSummary: 'Eksklusif Admin: unduh cadangan JSON untuk 14 dataset resmi sistem dan pulihkan data dari berkas backup.',
      steps: [
        {
          label: 'Ekspor Cadangan JSON',
          element: 'main section:first-of-type',
          popover: {
            title: '1. Pilih Dataset & Unduh Backup JSON',
            description: 'Centang semua data atau pilih dataset spesifik dari 14 kelompok tabel resmi, lalu unduh berkas JSON cadangan.',
            side: 'bottom',
            align: 'start'
          }
        },
        {
          label: 'Pulihkan Data (Restore)',
          element: 'main section:last-of-type',
          popover: {
            title: '2. Unggah & Pulihkan Data Backup',
            description: 'Pilih berkas JSON hasil backup resmi untuk memulihkan data dengan validasi keamanan daftar putih tabel.',
            side: 'top',
            align: 'start'
          }
        }
      ]
    }
  };

  return PAGES[currentPath] || {
    pageName: 'Halaman Aplikasi',
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
    const sections = Array.from(document.querySelectorAll('main section, main form')).filter(isElementVisible).slice(0, 4);
    sections.forEach((sec, idx) => {
      const heading = sec.querySelector('h1, h2, h3')?.textContent?.trim();
      visibleSteps.push({
        label: heading || `Bagian ${idx + 1}`,
        element: sec,
        popover: {
          title: heading || `Bagian Halaman ${idx + 1}`,
          description: 'Bagian ini menampilkan data, filter, atau formulir utama pada halaman yang sedang dibuka.',
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

export function startUserGuide(pathname, role, startIndex = 0, includeChrome = true) {
  const info = getPageGuideInfo(pathname, role);

  const navEl = findVisibleElement('aside[data-mobile-open]');
  const headerEl = findVisibleElement('header');
  const bubbleEl = findVisibleElement('#tour-guide-btn');

  const chromeStart = includeChrome
    ? [
        navEl && {
          element: navEl,
          popover: {
            title: 'Navigasi Utama Portal',
            description: `Anda masuk sebagai ${info.roleLabel}. Sidebar ini hanya menampilkan menu yang sesuai dengan hak akses role Anda.`,
            side: resolveResponsiveSide('right'),
            align: 'start'
          }
        },
        headerEl && {
          element: headerEl,
          popover: {
            title: 'Bilah Atas, Pencarian & Profil',
            description: 'Akses pencarian cepat, tombol Mode Belajar Siswa (khusus Teacher), profil akun, dan tombol keluar.',
            side: 'bottom',
            align: 'center'
          }
        }
      ].filter(Boolean)
    : [];

  const endBubble =
    includeChrome && bubbleEl
      ? [
          {
            element: bubbleEl,
            popover: {
              title: 'Tombol Panduan Interaktif (?)',
              description: 'Klik kapan saja untuk membuka daftar bagian halaman ini, membaca peran role Anda, atau melompat langsung ke bagian tertentu.',
              side: 'top',
              align: 'end'
            }
          }
        ]
      : [];

  const allSteps = [...chromeStart, ...info.steps, ...endBubble];
  if (!allSteps.length) return;

  const targetStepIndex = Math.min(Math.max(0, startIndex), allSteps.length - 1);

  const driverObj = driver({
    showProgress: true,
    animate: true,
    allowClose: true,
    disableActiveInteraction: false,
    overlayColor: 'rgba(15, 23, 42, 0.72)',
    nextBtnText: 'Lanjut &rarr;',
    prevBtnText: '&larr; Kembali',
    doneBtnText: 'Selesai Panduan',
    progressText: 'Langkah {{current}} dari {{total}}',
    onHighlightStarted: (element) => {
      if (element && typeof element.scrollIntoView === 'function') {
        try {
          element.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
        } catch {}
      }
    },
    steps: allSteps
  });

  driverObj.drive(targetStepIndex);
}
