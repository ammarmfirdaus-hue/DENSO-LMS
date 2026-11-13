import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/**
 * Driver.js Configuration & Steps per Page for Frontend LMS (Admin & Teacher).
 * Comprehensive, detailed, and professional walkthroughs covering all modules and features.
 */

const TOUR_STEPS = {
  '/': [
    {
      element: '#tour-overview',
      popover: {
        title: 'Ringkasan Eksekutif & Metrik Pelatihan',
        description: 'Menampilkan 4 indikator performa utama: total course aktif, jumlah pengguna terdaftar, tingkat kelulusan peserta, dan antrean review materi yang sedang berjalan.',
        side: 'bottom',
        align: 'start'
      }
    },
    {
      element: '#tour-charts',
      popover: {
        title: 'Grafik Interaktif Tren Pembelajaran',
        description: 'Visualisasi analitik berkala mencakup tren penyelesaian materi mingguan, sebaran perolehan nilai quiz, dan rasio partisipasi aktif seluruh departemen.',
        side: 'top',
        align: 'center'
      }
    },
    {
      element: '#tour-recent-activity',
      popover: {
        title: 'Log Aktivitas & Audit Operasional',
        description: 'Rekam jejak terkini interaksi sistem: riwayat login, pengajuan materi baru, pembaruan status pendaftaran siswa, dan status background worker.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/courses': [
    {
      element: '#courses-list',
      popover: {
        title: 'Katalog Silabus & Pencarian Course',
        description: 'Daftar seluruh kurikulum pelatihan DENSO. Anda dapat mencari berdasarkan kode course, judul silabus, maupun memfilter kategori (Wajib/Opsional).',
        side: 'top',
        align: 'start'
      }
    },
    {
      element: '#create-course',
      popover: {
        title: 'Formulir Pembuatan Silabus Baru',
        description: 'Tambahkan modul pelatihan baru, tentukan kode unik, tetapkan kategori silabus, serta langsung tugaskan trainer penanggung jawab.',
        side: 'bottom',
        align: 'start'
      }
    }
  ],
  '/materials': [
    {
      element: '#materials-submissions',
      popover: {
        title: 'Alur Pengajuan & Verifikasi Materi',
        description: 'Tahapan tinjauan materi dari trainer sebelum dipublikasikan. Admin dapat meneruskan materi ke reviewer HRD/Manajemen atau meminta revisi.',
        side: 'top',
        align: 'start'
      }
    },
    {
      element: '#published-materials-section',
      popover: {
        title: 'Katalog Materi Final & Siap Belajar',
        description: 'Kumpulan modul pelatihan yang telah lolos uji kelayakan dan berstatus Terbit sehingga sudah dapat diakses siswa pada portal pembelajaran.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/quizzes': [
    {
      element: '#quizzes-list',
      popover: {
        title: 'Manajemen Evaluasi & Soal Quiz',
        description: 'Kelola instrumen asesmen pembelajaran per course. Atur passing score minimal, batas waktu pengerjaan, dan variasi pilihan ganda.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/grades': [
    {
      element: '#grades-list',
      popover: {
        title: 'Buku Nilai & Prestasi Peserta',
        description: 'Rekapitulasi lengkap hasil evaluasi: NPK siswa, judul pelatihan, skor quiz, status kelulusan (Lulus/Remedial), dan tanggal penyelesaian.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/users': [
    {
      element: '#users-list',
      popover: {
        title: 'Direktori Pengguna Terpusat',
        description: 'Kelola seluruh akun ekosistem LMS (Student, Teacher, Admin, Reviewer). Dilengkapi filter role, status aktif/nonaktif, serta ekspor-impor CSV.',
        side: 'top',
        align: 'start'
      }
    },
    {
      element: '#add-user',
      popover: {
        title: 'Registrasi & Pengiriman Kredensial',
        description: 'Daftarkan pengguna baru dengan NPK resmi DENSO. Sistem secara otomatis membuat akun dan dapat mengirimkan kredensial awal langsung ke email.',
        side: 'bottom',
        align: 'start'
      }
    }
  ],
  '/enrollments': [
    {
      element: '#enrollments-list',
      popover: {
        title: 'Persetujuan Pendaftaran Course',
        description: 'Tinjau antrean permohonan akses pelatihan dari siswa atau rekomendasi instruktur. Dilengkapi tombol persetujuan satuan dan eksekusi massal (batch queue).',
        side: 'top',
        align: 'start'
      }
    },
    {
      element: '#recommend-student',
      popover: {
        title: 'Rekomendasi Peserta Pelatihan',
        description: 'Fitur khusus Teacher untuk mengikutsertakan siswa binaan ke pelatihan tertentu agar langsung mendapatkan prioritas persetujuan.',
        side: 'bottom',
        align: 'start'
      }
    }
  ],
  '/reviewers': [
    {
      element: '#reviewers-list',
      popover: {
        title: 'Manajemen Reviewer Kualitas',
        description: 'Daftar penilai mutu kurikulum independen (HRD & Manajerial). Pantau jumlah materi yang sedang aktif diperiksa oleh masing-masing personil.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/forum': [
    {
      element: '#forum-threads',
      popover: {
        title: 'Ruang Tanya Jawab & Diskusi Terbuka',
        description: 'Pusat interaksi dua arah antara pengajar dan peserta per topik pelatihan. Dilengkapi pembaruan obrolan instan (live SSE stream).',
        side: 'top',
        align: 'start'
      }
    },
    {
      element: '#new-thread',
      popover: {
        title: 'Mulai Diskusi atau Pengumuman',
        description: 'Buat topik pembahasan baru, bagikan studi kasus operasional, atau sematkan lampiran materi pendukung untuk peserta.',
        side: 'bottom',
        align: 'start'
      }
    }
  ],
  '/notifications': [
    {
      element: '#notifications-list',
      popover: {
        title: 'Pusat Riwayat Pengumuman',
        description: 'Dokumentasi pesan siaran, peringatan jadwal evaluasi, dan pembaruan sistem yang telah dikirimkan ke pengguna.',
        side: 'top',
        align: 'start'
      }
    },
    {
      element: '#send-notification',
      popover: {
        title: 'Kirim Siaran (Broadcast) Massal',
        description: 'Kirim pengumuman penting berdasarkan kelompok audiens (Semua Siswa, Pengajar, atau Admin) dengan opsi modal popup dan antrean email.',
        side: 'bottom',
        align: 'start'
      }
    }
  ],
  '/activity': [
    {
      element: '#activity-list',
      popover: {
        title: 'Audit Log & Pemantauan Sistem',
        description: 'Pantau log aktivitas lengkap, jejak keamanan, dan riwayat pekerjaan latar belakang (background queue worker) secara transparan.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/roles': [
    {
      element: '#roles-overview',
      popover: {
        title: 'Manajemen Role & Hak Akses RBAC',
        description: 'Ringkasan seluruh role sistem (Admin, Teacher, Reviewer, Student), total izin aktif per modul, serta kontrol pembukaan akses Portal Staff.',
        side: 'bottom',
        align: 'start'
      }
    },
    {
      element: '#roles-editor-panel',
      popover: {
        title: 'Editor Granular, Matriks & Riwayat Audit',
        description: 'Aktifkan atau cabut izin per modul dengan cepat, terapkan preset siap pakai, bandingkan antar role di Matriks Perbandingan, dan pantau rekam audit perubahan.',
        side: 'top',
        align: 'start'
      }
    }
  ],
  '/settings/api': [
    {
      element: 'main',
      popover: {
        title: 'Konfigurasi Endpoint & Sinkronisasi',
        description: 'Pengaturan koneksi API backend, uji latency server, status sinkronisasi portal siswa, dan integritas database.',
        side: 'top',
        align: 'start'
      }
    }
  ]
};

export function startUserGuide(pathname) {
  // Normalize base route
  const currentPath = pathname.split('?')[0].split('#')[0] || '/';
  
  // Universal steps (Sidebar navigation & Floating bubble)
  const universalStart = [
    {
      element: 'aside[data-mobile-open]',
      popover: {
        title: 'Bilah Navigasi DENSO LMS',
        description: 'Akses menu navigasi utama untuk mengelola kursus, materi pelatihan, evaluasi quiz, pengguna, dan analitik pembelajaran.',
        side: 'right',
        align: 'start'
      }
    },
    {
      element: '#tour-guide-btn',
      popover: {
        title: 'Tombol Panduan Bantuan (?)',
        description: 'Tombol bantuan praktis yang dapat digeser ke sudut layar mana saja. Klik kapan pun Anda ingin memahami fitur pada halaman saat ini.',
        side: 'top',
        align: 'end'
      }
    }
  ];

  const pageSpecificSteps = (TOUR_STEPS[currentPath] || []).filter(step => {
    return !step.element || document.querySelector(step.element);
  });

  const allSteps = [...universalStart, ...pageSpecificSteps];

  const driverObj = driver({
    showProgress: true,
    animate: true,
    allowClose: true,
    overlayColor: 'rgba(15, 23, 42, 0.70)',
    nextBtnText: 'Lanjut &rarr;',
    prevBtnText: '&larr; Kembali',
    doneBtnText: 'Selesai Panduan',
    progressText: 'Langkah {{current}} dari {{total}}',
    steps: allSteps
  });

  driverObj.drive();
}
