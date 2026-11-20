import { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import {
  BookOpen,
  CheckCircle2,
  ChevronDown,
  ChevronRight,
  FileCheck2,
  HelpCircle,
  Award,
  ShieldCheck,
  Users,
  UserCheck,
  MessagesSquare,
  Bell,
  Archive,
  Sliders,
  Activity,
  Search,
  ExternalLink,
  MapPin,
  Sparkles,
  ArrowRight,
  Headphones,
  Laptop
} from 'lucide-react';
import PageHeader from '../../components/PageHeader';
import Card from '../../components/Card';
import Button from '../../components/Button';
import { startUserGuide } from '../../utils/userGuide';

const ROLE_TABS = [
  { id: 'all', label: 'Semua Panduan' },
  { id: 'admin', label: 'Administrator' },
  { id: 'teacher', label: 'Teacher / Trainer' },
  { id: 'reviewer', label: 'Reviewer Materi' },
  { id: 'student', label: 'Student / Siswa' }
];

const GUIDES = [
  {
    id: 'course-management',
    title: '1. Manajemen Pembelajaran & Kurikulum (Courses)',
    roles: ['admin', 'teacher'],
    icon: BookOpen,
    summary: 'Panduan lengkap membuat kurikulum pelatihan, mengatur sesi pertemuan, mengunggah sampul, dan menugaskan pengampu.',
    steps: [
      {
        title: 'Membuat Course Baru',
        desc: 'Buka menu Pembelajaran > Kelola Course, lalu klik tombol "+ Buat Course Baru". Masukkan kode unik course (contoh: DENSO-MFG-01), judul lengkap, deskripsi tujuan pelatihan, kategori (Wajib bagi seluruh karyawan atau Opsional untuk pengembangan mandiri), dan pilih Trainer pengampu.'
      },
      {
        title: 'Menyusun Sesi Pertemuan & Modul',
        desc: 'Pada halaman Detail Course, gunakan panel "Manajemen Sesi Pertemuan" untuk menambahkan sesi pertemuan (Sesi 1, Sesi 2, dst.). Setiap sesi dapat memiliki judul fokus pembahasan dan urutan yang fleksibel.'
      },
      {
        title: 'Mengunggah Foto Sampul Course',
        desc: 'Klik tombol "Unggah Sampul" pada kartu header course. Sistem mendukung gambar format JPG, PNG, atau WebP. Gambar sampul akan otomatis dioptimasi dan ditampilkan pada katalog siswa.'
      }
    ]
  },
  {
    id: 'materials-workflow',
    title: '2. Alur Pengajuan & Persetujuan Materi (Materials)',
    roles: ['admin', 'teacher', 'reviewer'],
    icon: FileCheck2,
    summary: 'Siklus lengkap verifikasi materi mulai dari pengajuan trainer, pemeriksaan pra-review admin, review mandiri, hingga terbit.',
    steps: [
      {
        title: 'Pengajuan Materi oleh Trainer',
        desc: 'Trainer membuka menu Kelola Materi > "+ Ajukan Materi Baru". Pilih course dan sesi pertemuan tujuan, tentukan format materi (Video MP4/WebM hingga 250 MB, Dokumen PDF/Docx, Teks Pelatihan, atau Tautan Interaktif), isi ringkasan materi, lalu kirim ke antrean.'
      },
      {
        title: 'Pemeriksaan Pra-Review oleh Admin',
        desc: 'Admin memeriksa materi di tab "Menunggu Admin". Admin dapat mempratinjau isi konten dokumen/video, memastikan kelayakan awal, lalu menugaskan Reviewer spesifik dan mengirimkan tautan sesi review.'
      },
      {
        title: 'Sesi Review Mandiri oleh Reviewer (2 Jam)',
        desc: 'Reviewer mengakses tautan review khusus. Tautan berlaku selama 2 jam. Reviewer memeriksa materi di panel pratinjau, menuliskan catatan evaluasi konstruktif, lalu menekan "Setujui Materi" atau "Minta Revisi".'
      },
      {
        title: 'Penerbitan Materi Final ke Portal Siswa',
        desc: 'Setelah disetujui reviewer, Admin menekan tombol "Terbitkan Materi" untuk mempublikasikan materi ke katalog resmi yang langsung dapat diakses dan dipelajari oleh seluruh siswa terdaftar.'
      }
    ]
  },
  {
    id: 'quiz-and-grades',
    title: '3. Evaluasi Kuis, Rekap Nilai & Sertifikat (Assessment)',
    roles: ['admin', 'teacher'],
    icon: Award,
    summary: 'Tata cara membuat kuis evaluasi kelulusan, input nilai akhir instruktur, dan penerbitan sertifikat digital resmi.',
    steps: [
      {
        title: 'Menyusun Paket Quiz Course',
        desc: 'Pada menu Kelola Quiz, pilih course yang ingin dievaluasi lalu klik "Buat Quiz" atau "Edit Quiz". Atur judul kuis, petunjuk pengerjaan, nilai kelulusan minimal (Passing Score, misal 75), dan tambahkan pertanyaan pilihan ganda (opsi A hingga D) beserta kunci jawaban yang benar.'
      },
      {
        title: 'Pemantauan Capaian & Input Nilai Akhir Siswa',
        desc: 'Buka menu Kelola Nilai. Tabel menampilkan persentase progres belajar siswa, jumlah sesi tuntas, dan skor kuis. Instruktur atau Admin dapat mengisi kolom "Nilai Akhir (0–100)" secara langsung dan menyimpan hasilnya.'
      },
      {
        title: 'Penerbitan Sertifikat & Penghargaan Digital',
        desc: 'Siswa yang telah menuntaskan seluruh sesi dan lulus kuis dapat diterbitkan sertifikat digital melalui tombol "Kirim Sertifikat". Sertifikat dapat diunduh dalam format PDF resmi dengan nomor seri unik.'
      },
      {
        title: 'Ekspor Rekapitulasi Nilai ke Excel / CSV',
        desc: 'Gunakan tombol "Ekspor Laporan" di pojok kanan atas tabel nilai untuk mengunduh rekapitulasi capaian seluruh siswa ke dalam format Microsoft Excel (.xlsx) atau CSV yang rapi untuk arsip HRD.'
      }
    ]
  },
  {
    id: 'enrollments-approval',
    title: '4. Pendaftaran Siswa & Persetujuan Course (Enrollments)',
    roles: ['admin', 'teacher'],
    icon: UserCheck,
    summary: 'Mekanisme penerimaan siswa baik melalui permintaan mandiri maupun rekomendasi langsung instruktur.',
    steps: [
      {
        title: 'Permintaan Akses Mandiri oleh Siswa',
        desc: 'Siswa dapat mengajukan permintaan pendaftaran (enrollment request) pada kursus di portal belajar mereka. Permintaan akan langsung masuk ke tab "Menunggu Keputusan" pada portal staf.'
      },
      {
        title: 'Rekomendasi Langsung oleh Trainer',
        desc: 'Trainer dapat merekomendasikan siswa tertentu agar dimasukkan ke dalam kelas pelatihan dengan mengisi formulir "Rekomendasikan Student" pada halaman Persetujuan Course.'
      },
      {
        title: 'Persetujuan / Penolakan Akses',
        desc: 'Admin meninjau antrean permintaan. Admin dapat menyetujui atau menolak per baris data, atau menggunakan tombol "Setujui Semua Permintaan Pending" untuk memproses antrean sekaligus.'
      }
    ]
  },
  {
    id: 'forum-and-notifications',
    title: '5. Komunikasi Forum & Pengumuman Broadcast (Messaging)',
    roles: ['admin', 'teacher'],
    icon: MessagesSquare,
    summary: 'Mengelola forum diskusi interaktif real-time dan mengirimkan pengumuman penting kepada anggota.',
    steps: [
      {
        title: 'Manajemen Forum Diskusi per Sesi Pertemuan',
        desc: 'Pilih course dan sesi pertemuan pada menu Forum Diskusi. Instruktur dapat mengaktifkan sakelar "Forum Dibuka" agar peserta dapat bertanya. Pesan balasan masuk secara real-time melalui koneksi Server-Sent Events (SSE).'
      },
      {
        title: 'Kirim Notifikasi Broadcast & Checklist Anggota Spesifik',
        desc: 'Buka menu Notifikasi > "+ Kirim Notifikasi". Pengirim dapat memilih target audiens berbasis Peran (Semua Siswa/Teacher/Admin) atau memilih Anggota Spesifik melalui daftar checklist interaktif lengkap dengan peninjauan detail profil anggota.'
      },
      {
        title: 'Lampiran Gambar WebP & Opsi Popup Modal',
        desc: 'Unggah gambar lampiran yang otomatis dikompresi ke WebP hemat memori. Centang "Munculkan Popup Modal" agar pengumuman langsung muncul sebagai modal popup darurat di layar siswa.'
      }
    ]
  },
  {
    id: 'users-and-rbac',
    title: '6. Manajemen Pengguna & Hak Akses Granular (RBAC)',
    roles: ['admin'],
    icon: Users,
    summary: 'Administrasi direktori akun, impor data massal via CSV, dan konfigurasi 31 izin akses modular.',
    steps: [
      {
        title: 'Pendaftaran Akun Satuan & Impor Massal CSV',
        desc: 'Admin dapat mendaftarkan akun baru satu per satu atau mengunduh template CSV resmi untuk melakukan impor data ratusan pengguna sekaligus yang diproses oleh background worker terenkripsi AES-256-GCM.'
      },
      {
        title: 'Konfigurasi 31 Izin Akses Modular (RBAC)',
        desc: 'Buka menu Manajemen > Role & Hak Akses. Admin dapat mengatur 31 sakelar izin per modul untuk masing-masing peran, membandingkannya pada Matriks Hak Akses, serta meninjau riwayat audit perubahan.'
      },
      {
        title: 'Detail Profil Anggota pada Setiap Tabel',
        desc: 'Klik baris mana saja pada tabel pengguna, reviewer, nilai, atau aktivitas untuk membuka modal detail profil interaktif lengkap tanpa berpindah halaman.'
      }
    ]
  },
  {
    id: 'api-sync-and-backup',
    title: '7. Integrasi Sistem, Sinkronisasi MySQL & Backup (System)',
    roles: ['admin'],
    icon: Sliders,
    summary: 'Pengaturan endpoint API bersama, sinkronisasi otomatis SQL Server-MySQL, dan pencadangan dataset resmi.',
    steps: [
      {
        title: 'Uji Koneksi Server & Endpoint API',
        desc: 'Buka menu Pengaturan > Atur API untuk memeriksa URL endpoint API bersama, menguji koneksi server secara langsung, dan memastikan layanan backend beroperasi optimal.'
      },
      {
        title: 'Sinkronisasi Dua Arah MySQL Student Worker',
        desc: 'Sistem menyinkronkan data katalog kursus, pengguna, dan nilai antara SQL Server dan MySQL Student secara terjadwal. Admin dapat menekan "Sinkronkan Sekarang" untuk memicu pembaruan manual.'
      },
      {
        title: 'Pencadangan (Backup) & Pemulihan (Restore) Data JSON',
        desc: 'Admin dapat memilih dari 14 kelompok dataset tabel resmi DENSO LMS untuk mengunduh cadangan JSON terenkripsi, serta memulihkannya melalui menu Pemulihan Data dengan validasi daftar putih keamanan tabel.'
      }
    ]
  }
];

const FAQS = [
  {
    q: 'Bagaimana cara seorang instruktur mengajukan materi video berukuran besar?',
    a: 'Instruktur dapat mengunggah file video dalam format .mp4, .webm, atau .mov hingga batas ukuran 250 MB per materi. Sistem secara otomatis membuat streaming buffer yang efisien agar peserta dapat memutar video dengan lancar tanpa jeda.'
  },
  {
    q: 'Apakah reviewer materi memerlukan akun tetap di portal LMS?',
    a: 'Tidak harus. Admin dapat mendaftarkan reviewer internal maupun mengundang reviewer independen menggunakan tautan sesi sekali pakai yang berlaku selama 2 jam dengan perlindungan token kriptografi.'
  },
  {
    q: 'Kapan sertifikat kelulusan course dapat diterbitkan untuk peserta?',
    a: 'Sertifikat dapat diterbitkan setelah peserta menyelesaikan seluruh sesi pertemuan pada kursus terkait dan memperoleh nilai kuis di atas ambang batas kelulusan (passing score) yang ditetapkan.'
  },
  {
    q: 'Apakah bisa mengirim pengumuman darurat hanya kepada anggota tertentu?',
    a: 'Bisa. Pada formulir Kirim Notifikasi, pilih mode "Pilih Anggota Spesifik". Anda dapat mencari nama atau NPK anggota, mencentang penerima yang dituju, dan melihat profil anggota sebelum mengirim.'
  },
  {
    q: 'Bagaimana cara melihat lokasi pabrik dan fasilitas DENSO di Indonesia?',
    a: 'Anda dapat membuka menu "Lokasi PT DENSO (3D)" di sidebar atau pada tautan footer. Peta interaktif menampilkan visualisasi 3D satelit untuk seluruh pabrik DENSO di Sunter, Bekasi, Cikarang, Surabaya, Semarang, dan Medan.'
  }
];

export default function HelpCenterPage() {
  const [activeTab, setActiveTab] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [expandedGuide, setExpandedGuide] = useState('course-management');
  const [expandedFaq, setExpandedFaq] = useState(null);

  const filteredGuides = useMemo(() => {
    return GUIDES.filter((g) => {
      if (activeTab !== 'all' && !g.roles.includes(activeTab)) return false;
      if (!searchQuery.trim()) return true;
      const q = searchQuery.toLowerCase();
      return (
        g.title.toLowerCase().includes(q) ||
        g.summary.toLowerCase().includes(q) ||
        g.steps.some((s) => s.title.toLowerCase().includes(q) || s.desc.toLowerCase().includes(q))
      );
    });
  }, [activeTab, searchQuery]);

  const filteredFaqs = useMemo(() => {
    if (!searchQuery.trim()) return FAQS;
    const q = searchQuery.toLowerCase();
    return FAQS.filter((f) => f.q.toLowerCase().includes(q) || f.a.toLowerCase().includes(q));
  }, [searchQuery]);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="DOKUMENTASI OPERASIONAL RESMI"
        title="Pusat Panduan & Bantuan DENSO LMS"
        description="Panduan komprehensif alur operasional sistem pembelajaran, evaluasi, sertifikasi, administrasi hak akses, dan tata cara penggunaan portal."
        actions={
          <div className="flex flex-wrap items-center gap-2.5">
            <Button
              variant="outline"
              icon={<Sparkles className="size-4 text-primary" />}
              onClick={() => startUserGuide(window.location.pathname, 'admin')}
            >
              Mulai Tur Interaktif Halaman
            </Button>
            <a href="#denso-footer-map">
              <Button variant="primary" icon={<MapPin className="size-4" />}>
                Peta Fasilitas DENSO (3D)
              </Button>
            </a>
          </div>
        }
      />

      {/* Hero Search Bar */}
      <div className="dls-card-surface rounded-2xl p-6 sm:p-8 bg-gradient-to-br from-surface-low to-surface-lowest border border-outline/60 dark:from-slate-900 dark:to-slate-950">
        <div className="max-w-2xl mx-auto text-center space-y-3">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
            <HelpCircle className="size-3.5" /> Bantuan &amp; Dokumentasi
          </span>
          <h2 className="text-xl sm:text-2xl font-bold text-on-surface">
            Apa yang ingin Anda pelajari atau cari solusinya?
          </h2>
          <p className="text-xs sm:text-sm text-muted">
            Ketik kata kunci modul (Course, Materi, Kuis, Nilai, Pengguna, Notifikasi, Peta 3D) untuk menemukan panduan langkah demi langkah.
          </p>

          <div className="relative mt-4">
            <Search className="size-4 text-muted absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Cari panduan fitur, tata cara, atau jawaban kendala..."
              className="w-full h-11 pl-11 pr-4 text-sm rounded-xl border border-outline bg-white text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 shadow-xs"
            />
          </div>
        </div>
      </div>

      {/* Role Tab Filter Bar */}
      <div className="flex flex-wrap items-center justify-between gap-3 border-b border-outline/50 pb-3 dark:border-slate-800">
        <div className="flex flex-wrap gap-1.5">
          {ROLE_TABS.map((tab) => (
            <button
              key={tab.id}
              type="button"
              onClick={() => setActiveTab(tab.id)}
              className={`cursor-pointer rounded-lg px-3.5 py-2 text-xs font-semibold transition ${
                activeTab === tab.id
                  ? 'bg-primary text-white shadow-xs'
                  : 'bg-surface-low text-on-surface-variant hover:bg-surface-container dark:bg-slate-800'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
        <span className="text-xs text-muted font-medium">
          Menampilkan {filteredGuides.length} panduan modul
        </span>
      </div>

      {/* Accordion Daftar Panduan Fitur */}
      <section className="space-y-4" id="overview">
        {filteredGuides.length ? (
          filteredGuides.map((guide) => {
            const isExpanded = expandedGuide === guide.id;
            const Icon = guide.icon;
            return (
              <div
                key={guide.id}
                className="dls-card-surface rounded-xl border border-outline/50 overflow-hidden transition"
              >
                <button
                  type="button"
                  onClick={() => setExpandedGuide(isExpanded ? null : guide.id)}
                  className="w-full flex items-center justify-between gap-4 p-5 text-left cursor-pointer hover:bg-surface-low/50 transition"
                >
                  <div className="flex items-start gap-3.5 min-w-0">
                    <span className="grid size-10 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary">
                      <Icon className="size-5" />
                    </span>
                    <div className="min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <h3 className="font-bold text-sm sm:text-base text-on-surface">
                          {guide.title}
                        </h3>
                        <div className="flex gap-1">
                          {guide.roles.map((r) => (
                            <span
                              key={r}
                              className="rounded bg-surface-low px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted dark:bg-slate-800"
                            >
                              {r}
                            </span>
                          ))}
                        </div>
                      </div>
                      <p className="mt-1 text-xs text-muted leading-relaxed">
                        {guide.summary}
                      </p>
                    </div>
                  </div>
                  <ChevronDown
                    className={`size-5 text-muted shrink-0 transition-transform duration-200 ${
                      isExpanded ? 'rotate-180 text-primary' : ''
                    }`}
                  />
                </button>

                {isExpanded && (
                  <div className="border-t border-outline/40 bg-surface-low/30 p-5 space-y-4 dark:border-slate-800 dark:bg-slate-900/30">
                    <p className="text-xs font-semibold uppercase tracking-wider text-primary">
                      Tahapan &amp; Prosedur Pengoperasian:
                    </p>
                    <div className="grid gap-3 sm:grid-cols-2">
                      {guide.steps.map((step, idx) => (
                        <div
                          key={idx}
                          className="rounded-lg border border-outline/40 bg-surface-lowest p-4 text-xs dark:border-slate-800 dark:bg-slate-900"
                        >
                          <div className="flex items-center gap-2 font-bold text-on-surface">
                            <span className="grid size-5 place-items-center rounded-full bg-primary text-white text-[10px] font-mono shrink-0">
                              {idx + 1}
                            </span>
                            <span>{step.title}</span>
                          </div>
                          <p className="mt-2 text-muted leading-relaxed">
                            {step.desc}
                          </p>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            );
          })
        ) : (
          <div className="dls-card-surface p-12 text-center rounded-xl border border-dashed border-outline/60">
            <HelpCircle className="size-10 text-muted/60 mx-auto" />
            <p className="mt-3 text-sm font-bold text-on-surface">Tidak ada panduan yang cocok</p>
            <p className="mt-1 text-xs text-muted">Coba ubah kata kunci pencarian atau pilih tab peran lain.</p>
          </div>
        )}
      </section>

      {/* Bagian FAQ (Tanya Jawab Kendala & Panduan Operasional) */}
      <section className="space-y-4 pt-4" id="faq">
        <div>
          <h3 className="text-lg font-bold text-on-surface">Tanya Jawab yang Sering Diajukan (FAQ)</h3>
          <p className="text-xs text-muted">Solusi cepat atas pertanyaan umum seputar operasional portal DENSO LMS.</p>
        </div>

        <div className="space-y-2.5">
          {filteredFaqs.map((faq, index) => {
            const isOpen = expandedFaq === index;
            return (
              <div
                key={index}
                className="dls-card-surface rounded-xl border border-outline/40 overflow-hidden"
              >
                <button
                  type="button"
                  onClick={() => setExpandedFaq(isOpen ? null : index)}
                  className="w-full flex items-center justify-between gap-3 p-4 text-left cursor-pointer hover:bg-surface-low/50 transition"
                >
                  <span className="text-xs sm:text-sm font-semibold text-on-surface">
                    {faq.q}
                  </span>
                  <ChevronDown
                    className={`size-4 text-muted shrink-0 transition-transform duration-200 ${
                      isOpen ? 'rotate-180 text-primary' : ''
                    }`}
                  />
                </button>
                {isOpen && (
                  <div className="border-t border-outline/30 bg-surface-low/30 px-4 py-3 text-xs leading-relaxed text-muted dark:border-slate-800">
                    {faq.a}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </section>

      {/* Hub Kontak & Dukungan Cepat */}
      <section className="dls-card-surface rounded-2xl p-6 sm:p-7 border border-outline/60 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="flex items-center gap-4 text-center sm:text-left">
          <span className="grid size-12 shrink-0 place-items-center rounded-xl bg-red-600 text-white shadow-sm">
            <Headphones className="size-6" />
          </span>
          <div>
            <h4 className="font-bold text-base text-on-surface">Masih Memerlukan Bantuan Teknis?</h4>
            <p className="text-xs text-muted mt-0.5">
              Hubungi IT Helpdesk DENSO di <span className="font-semibold text-on-surface">helpdesk@denso.co.id</span> atau buka menu Pusat Bantuan (Live Chat).
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2 shrink-0">
          <Link to="/support-chat">
            <Button variant="primary" size="sm">
              Buka Live Chat
            </Button>
          </Link>
        </div>
      </section>
    </div>
  );
}

