import { writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const outputDirectory = path.dirname(fileURLToPath(import.meta.url));
const node = (id, actor, shape, label, x, y, w = 300, h = 64) => ({ id, actor, shape, label, x, y, w, h });
const edge = (from, to, label = '') => ({ from, to, label });

const diagrams = [
  {
    file: '01-login-sso.drawio', title: 'Login, Sesi, dan SSO', nodes: [
      node('start', 'Pengguna', 'start', 'Buka portal LMS', 470, 70, 240, 54),
      node('credentials', 'Pengguna', 'process', 'Masukkan NPK dan kata sandi', 440, 160),
      node('valid', 'API', 'decision', 'Kredensial valid?', 480, 260, 220, 100),
      node('reject', 'API', 'process', 'Tolak login; catat kegagalan dan terapkan batas percobaan', 45, 405, 310, 76),
      node('role', 'API', 'decision', 'Role akun?', 480, 415, 220, 100),
      node('student', 'Student', 'process', 'Redirect ke aplikasi PHP; verifikasi SSO dan buat sesi Student + token CSRF', 825, 565, 320, 86),
      node('staff', 'Admin / Teacher', 'process', 'Buka portal staff; API memeriksa role pada setiap endpoint', 440, 565, 300, 78),
      node('studentDone', 'Student', 'end', 'Sesi Student aktif', 865, 715, 240, 54),
      node('staffDone', 'Admin / Teacher', 'end', 'Dashboard staff aktif', 470, 715, 240, 54)
    ], edges: [edge('start', 'credentials'), edge('credentials', 'valid'), edge('valid', 'role', 'Ya'), edge('valid', 'reject', 'Tidak'), edge('reject', 'credentials', 'Coba lagi setelah jeda'), edge('role', 'student', 'Student'), edge('role', 'staff', 'Admin / Teacher'), edge('student', 'studentDone'), edge('staff', 'staffDone')]
  },
  {
    file: '02-user-management.drawio', title: 'Manajemen Pengguna Staff', nodes: [
      node('start', 'Admin', 'start', 'Buka Manajemen Pengguna', 470, 70, 260, 54),
      node('guard', 'API', 'decision', 'Role admin?', 490, 165, 220, 100),
      node('deny', 'API', 'end', 'Tolak akses: 403', 55, 330, 250, 54),
      node('list', 'API', 'process', 'Muat daftar akun dan status', 455, 315),
      node('action', 'Admin', 'decision', 'Aksi pengguna?', 495, 420, 220, 100),
      node('form', 'Admin', 'process', 'Isi data akun dan pilih role yang diizinkan', 50, 585, 320, 76),
      node('validate', 'API', 'decision', 'Data valid?', 495, 585, 220, 100),
      node('error', 'API', 'process', 'Tampilkan validasi; jangan simpan perubahan', 50, 735, 320, 70),
      node('save', 'API', 'process', 'Simpan akun; hash password dengan bcrypt', 820, 585, 320, 72),
      node('done', 'Admin', 'end', 'Daftar akun diperbarui', 820, 735, 280, 54)
    ], edges: [edge('start', 'guard'), edge('guard', 'list', 'Ya'), edge('guard', 'deny', 'Tidak'), edge('list', 'action'), edge('action', 'form', 'Tambah / ubah'), edge('action', 'done', 'Lihat daftar'), edge('form', 'validate'), edge('validate', 'save', 'Ya'), edge('validate', 'error', 'Tidak'), edge('error', 'form', 'Perbaiki'), edge('save', 'done')]
  },
  {
    file: '03-reviewer-management.drawio', title: 'Manajemen Reviewer', nodes: [
      node('start', 'Admin', 'start', 'Buka Kelola Reviewer', 470, 70, 260, 54),
      node('guard', 'API', 'decision', 'Role admin?', 490, 165, 220, 100),
      node('deny', 'API', 'end', 'Tolak akses: 403', 55, 330, 250, 54),
      node('list', 'API', 'process', 'Muat profil, email, dan status reviewer', 440, 315, 320),
      node('action', 'Admin', 'decision', 'Tambah atau ubah reviewer?', 490, 425, 220, 100),
      node('form', 'Admin', 'process', 'Isi nama, email, dan status akun', 50, 590, 310, 70),
      node('validate', 'API', 'decision', 'Data valid dan email unik?', 490, 580, 220, 100),
      node('error', 'API', 'process', 'Tampilkan error validasi', 50, 740, 300, 64),
      node('save', 'API', 'process', 'Simpan profil reviewer dengan status aktif/nonaktif', 820, 590, 330, 74),
      node('done', 'Admin', 'end', 'Reviewer siap dipilih pada alur review materi', 810, 740, 330, 64)
    ], edges: [edge('start', 'guard'), edge('guard', 'list', 'Ya'), edge('guard', 'deny', 'Tidak'), edge('list', 'action'), edge('action', 'form', 'Ya'), edge('action', 'done', 'Tidak'), edge('form', 'validate'), edge('validate', 'save', 'Ya'), edge('validate', 'error', 'Tidak'), edge('error', 'form', 'Perbaiki'), edge('save', 'done')]
  },
  {
    file: '04-dashboard-activity.drawio', title: 'Dashboard dan Aktivitas', nodes: [
      node('start', 'Admin / Teacher', 'start', 'Buka dashboard', 455, 70, 270, 54),
      node('auth', 'API', 'process', 'Validasi sesi dan role', 440, 160),
      node('load', 'API', 'process', 'Ambil ringkasan course, progres, dan aktivitas', 430, 260, 320, 72),
      node('ready', 'API', 'decision', 'Data tersedia?', 480, 375, 220, 100),
      node('error', 'Web', 'process', 'Tampilkan status gagal memuat dan opsi coba lagi', 50, 535, 320, 76),
      node('render', 'Web', 'process', 'Tampilkan kartu ringkasan dan daftar aktivitas', 820, 535, 320, 76),
      node('drill', 'Pengguna', 'decision', 'Buka fitur lain?', 490, 660, 220, 100),
      node('done', 'Web', 'end', 'Dashboard siap digunakan', 820, 805, 280, 54)
    ], edges: [edge('start', 'auth'), edge('auth', 'load'), edge('load', 'ready'), edge('ready', 'render', 'Ya'), edge('ready', 'error', 'Tidak'), edge('error', 'load', 'Muat ulang'), edge('render', 'drill'), edge('drill', 'done', 'Tidak'), edge('drill', 'done', 'Ya: navigasi ke fitur')]
  },
  {
    file: '05-course-session.drawio', title: 'Course, Sesi, dan Materi Course', nodes: [
      node('start', 'Admin / Teacher', 'start', 'Buka daftar course', 455, 60, 270, 54),
      node('list', 'API', 'process', 'Muat course dan detail sesi', 440, 150),
      node('action', 'Admin / Teacher', 'decision', 'Buat atau kelola course?', 490, 250, 220, 100),
      node('form', 'Admin / Teacher', 'process', 'Isi nama, deskripsi, dan kategori course', 45, 420, 330, 74),
      node('validate', 'API', 'decision', 'Data course valid?', 490, 415, 220, 100),
      node('saveCourse', 'API', 'process', 'Simpan course', 820, 425, 280, 64),
      node('assign', 'Admin', 'process', 'Tetapkan trainer (khusus admin)', 820, 545, 310, 70),
      node('session', 'Admin / Teacher', 'process', 'Tambah sesi dan materi pembelajaran', 440, 560, 330, 70),
      node('sync', 'Sinkronisasi', 'decision', 'Sinkronisasi Student aktif?', 490, 670, 220, 100),
      node('student', 'Student', 'process', 'Course dan sesi masuk ke katalog Student melalui job sinkronisasi', 820, 805, 340, 76),
      node('done', 'API', 'end', 'Perubahan tersimpan', 450, 825, 280, 54)
    ], edges: [edge('start', 'list'), edge('list', 'action'), edge('action', 'form', 'Buat course'), edge('action', 'session', 'Kelola course'), edge('form', 'validate'), edge('validate', 'saveCourse', 'Ya'), edge('validate', 'form', 'Tidak'), edge('saveCourse', 'assign'), edge('assign', 'session'), edge('session', 'sync'), edge('sync', 'student', 'Ya'), edge('sync', 'done', 'Tidak'), edge('student', 'done')]
  },
  {
    file: '06-material-approval.drawio', title: 'Pengajuan, Review, dan Publikasi Materi', nodes: [
      node('start', 'Teacher / Admin', 'start', 'Pilih sesi dan ajukan materi', 445, 50, 310, 54),
      node('validate', 'API', 'process', 'Validasi role, file, ukuran, dan data sesi', 430, 140, 340, 74),
      node('adminQueue', 'Admin', 'process', 'Periksa antrean materi', 445, 250),
      node('adminDecision', 'Admin', 'decision', 'Perlu perbaikan?', 485, 340, 220, 100),
      node('return', 'Teacher', 'process', 'Materi ditolak dengan catatan; pemilik memperbaiki kiriman', 45, 500, 330, 80),
      node('editGuard', 'API', 'decision', 'Pemilik dan status edit diizinkan?', 490, 500, 220, 100),
      node('sendReview', 'Admin', 'process', 'Tugaskan reviewer dan kirim tautan terbatas', 820, 500, 330, 76),
      node('review', 'Reviewer', 'process', 'Periksa isi dan kirim keputusan', 820, 630, 330, 72),
      node('approved', 'API', 'decision', 'Reviewer menyetujui?', 485, 640, 220, 100),
      node('publish', 'Admin', 'process', 'Admin menerbitkan materi yang disetujui', 445, 780, 340, 68),
      node('student', 'Student', 'end', 'Materi tampil setelah publish/sinkronisasi', 820, 800, 330, 64)
    ], edges: [edge('start', 'validate'), edge('validate', 'adminQueue'), edge('adminQueue', 'adminDecision'), edge('adminDecision', 'return', 'Ya'), edge('adminDecision', 'sendReview', 'Tidak'), edge('return', 'editGuard'), edge('editGuard', 'start', 'Ya: kirim ulang'), edge('editGuard', 'return', 'Tidak'), edge('sendReview', 'review'), edge('review', 'approved'), edge('approved', 'return', 'Tidak'), edge('approved', 'publish', 'Ya'), edge('publish', 'student')]
  },
  {
    file: '07-reviewer-portal.drawio', title: 'Portal Review Materi Eksternal', nodes: [
      node('start', 'Reviewer', 'start', 'Buka tautan review', 470, 65, 250, 54),
      node('consume', 'API', 'process', 'Konsumsi token dan ikat akses ke submission', 430, 155, 340, 74),
      node('valid', 'API', 'decision', 'Token valid, aktif, dan belum dipakai?', 490, 275, 220, 100),
      node('deny', 'API', 'end', 'Tolak akses; tampilkan tautan tidak berlaku', 45, 440, 320, 70),
      node('load', 'API', 'process', 'Muat submission dan file yang ditugaskan', 820, 435, 320, 72),
      node('inspect', 'Reviewer', 'process', 'Baca materi dan tulis catatan review', 820, 555, 320, 70),
      node('decision', 'Reviewer', 'decision', 'Setujui atau tolak?', 490, 555, 220, 100),
      node('save', 'API', 'process', 'Simpan keputusan pada submission terkait', 450, 710, 320, 70),
      node('done', 'Admin / Teacher', 'end', 'Keputusan muncul di antrean materi', 450, 825, 320, 64)
    ], edges: [edge('start', 'consume'), edge('consume', 'valid'), edge('valid', 'load', 'Ya'), edge('valid', 'deny', 'Tidak'), edge('load', 'inspect'), edge('inspect', 'decision'), edge('decision', 'save', 'Approve / Reject'), edge('save', 'done')]
  },
  {
    file: '08-student-learning.drawio', title: 'Belajar dan Assessment Student', nodes: [
      node('start', 'Student', 'start', 'Buka dashboard belajar', 470, 55, 260, 54),
      node('catalog', 'Student', 'process', 'Cari course atau gunakan filter kategori', 435, 150, 330, 70),
      node('detail', 'Student', 'process', 'Buka detail course dan pilih sesi', 440, 260, 320, 70),
      node('material', 'Student', 'process', 'Baca materi pada learning viewer', 440, 370, 320, 70),
      node('quiz', 'Student', 'decision', 'Ada kuis pada sesi?', 490, 480, 220, 100),
      node('takeQuiz', 'Student', 'process', 'Jawab soal dan kirim jawaban', 820, 625, 320, 70),
      node('score', 'Sistem', 'process', 'Hitung/simpan hasil dan progres', 435, 640, 330, 70),
      node('history', 'Student', 'process', 'Lihat progres, nilai, dan riwayat', 435, 755, 330, 70),
      node('done', 'Student', 'end', 'Sesi belajar selesai', 470, 865, 260, 54)
    ], edges: [edge('start', 'catalog'), edge('catalog', 'detail'), edge('detail', 'material'), edge('material', 'quiz'), edge('quiz', 'takeQuiz', 'Ya'), edge('quiz', 'score', 'Tidak'), edge('takeQuiz', 'score'), edge('score', 'history'), edge('history', 'done')]
  },
  {
    file: '09-forum-discussion.drawio', title: 'Forum Diskusi Real-time', nodes: [
      node('start', 'Admin / Teacher', 'start', 'Pilih course dan sesi forum', 450, 55, 300, 54),
      node('toggle', 'Admin / Teacher', 'process', 'Buka atau tutup forum sesi', 440, 145, 320, 66),
      node('open', 'API', 'decision', 'Sesi forum terbuka?', 490, 250, 220, 100),
      node('closed', 'Peserta', 'process', 'Forum tertutup: baca saja; aksi tulis ditolak', 45, 415, 330, 74),
      node('compose', 'Student / Staff', 'process', 'Tulis thread atau balasan; beri like', 820, 415, 320, 70),
      node('image', 'Browser', 'decision', 'Ada gambar statis?', 490, 415, 220, 100),
      node('compress', 'Browser', 'process', 'Resize maksimal 1920 px; encode WebP bertahap', 820, 555, 320, 74),
      node('validate', 'API', 'process', 'Periksa role, CSRF/origin, rate limit, size, dan signature file', 420, 590, 360, 82),
      node('save', 'API', 'process', 'Simpan post/reply/like dan metadata lampiran', 420, 710, 360, 70),
      node('events', 'SSE', 'process', 'Kirim event ke peserta yang tersambung', 420, 815, 360, 68),
      node('done', 'Peserta', 'end', 'Thread diperbarui tanpa reload halaman', 445, 915, 310, 54)
    ], edges: [edge('start', 'toggle'), edge('toggle', 'open'), edge('open', 'compose', 'Ya'), edge('open', 'closed', 'Tidak'), edge('compose', 'image'), edge('image', 'compress', 'Ya'), edge('image', 'validate', 'Tidak'), edge('compress', 'validate'), edge('validate', 'save'), edge('save', 'events'), edge('events', 'done')]
  },
  {
    file: '10-notifications-email.drawio', title: 'Notifikasi Web dan Email', nodes: [
      node('start', 'Admin / Teacher', 'start', 'Buat notifikasi atau reminder', 445, 50, 310, 54),
      node('audience', 'API', 'decision', 'Audiens sesuai role pengirim?', 490, 150, 220, 100),
      node('deny', 'API', 'end', 'Tolak audiens yang tidak diizinkan', 45, 320, 310, 64),
      node('form', 'Pengirim', 'process', 'Pilih penerima, kategori, prioritas, isi, dan opsi email', 820, 315, 330, 80),
      node('validate', 'API', 'process', 'Validasi payload; normalisasi dan deduplikasi penerima email', 430, 430, 340, 76),
      node('store', 'API', 'process', 'Simpan notifikasi web dan unread state', 435, 545, 330, 70),
      node('email', 'API / Email', 'decision', 'Checkbox email aktif?', 490, 650, 220, 100),
      node('outbox', 'Email worker', 'process', 'Masukkan email ke outbox dan kirim sesuai konfigurasi SMTP', 820, 805, 330, 78),
      node('publish', 'SSE', 'process', 'Kirim event; badge unread diperbarui', 430, 805, 340, 70),
      node('read', 'Penerima', 'process', 'Buka notifikasi lalu tandai satu/semua sebagai dibaca', 430, 925, 340, 74)
    ], edges: [edge('start', 'audience'), edge('audience', 'form', 'Ya'), edge('audience', 'deny', 'Tidak'), edge('form', 'validate'), edge('validate', 'store'), edge('store', 'email'), edge('email', 'outbox', 'Ya'), edge('email', 'publish', 'Tidak'), edge('outbox', 'publish'), edge('publish', 'read')]
  },
  {
    file: '11-grades-progress-history.drawio', title: 'Nilai, Progres, dan Riwayat', nodes: [
      node('start', 'Student', 'start', 'Belajar materi atau mengerjakan kuis', 430, 60, 340, 54),
      node('save', 'Student API', 'process', 'Simpan progres dan hasil kuis pada database Student', 420, 155, 360, 76),
      node('snapshot', 'Job sinkronisasi', 'process', 'Sinkronkan snapshot progres sesuai jadwal dan konfigurasi', 420, 275, 360, 76),
      node('open', 'Admin / Teacher', 'process', 'Buka dashboard, aktivitas, atau halaman nilai', 420, 395, 360, 70),
      node('authorize', 'API', 'decision', 'Sesi dan role boleh membaca?', 490, 500, 220, 100),
      node('deny', 'API', 'end', 'Tolak permintaan tidak terautentikasi/role salah', 45, 665, 330, 70),
      node('query', 'API', 'process', 'Ambil data progres/nilai dengan pagination', 820, 665, 320, 70),
      node('display', 'Portal', 'process', 'Tampilkan ringkasan staff atau riwayat Student', 420, 790, 360, 72),
      node('done', 'Pengguna', 'end', 'Data baca ditampilkan', 470, 900, 260, 54)
    ], edges: [edge('start', 'save'), edge('save', 'snapshot'), edge('snapshot', 'open'), edge('open', 'authorize'), edge('authorize', 'query', 'Ya'), edge('authorize', 'deny', 'Tidak'), edge('query', 'display'), edge('display', 'done')]
  },
  {
    file: '12-student-profile-support.drawio', title: 'Profil, Pengaturan, dan Bantuan Student', nodes: [
      node('start', 'Student', 'start', 'Buka menu profil', 470, 60, 260, 54),
      node('profile', 'Student app', 'process', 'Muat ringkasan profil dan statistik belajar', 430, 155, 340, 72),
      node('action', 'Student', 'decision', 'Halaman yang dipilih?', 490, 275, 220, 100),
      node('detail', 'Student app', 'process', 'Lihat detail karyawan dan riwayat karier', 45, 445, 330, 70),
      node('settings', 'Student app', 'process', 'Lihat pengaturan akun dan riwayat login', 435, 445, 330, 70),
      node('help', 'Student app', 'process', 'Buka panduan dan informasi bantuan', 825, 445, 320, 70),
      node('done', 'Student', 'end', 'Kembali ke halaman profil', 470, 595, 260, 54)
    ], edges: [edge('start', 'profile'), edge('profile', 'action'), edge('action', 'detail', 'Detail'), edge('action', 'settings', 'Pengaturan'), edge('action', 'help', 'Bantuan'), edge('detail', 'done'), edge('settings', 'done'), edge('help', 'done')]
  }
];

function xml(value) {
  return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll('\n', '&#xa;');
}

const fills = {
  'Pengguna': '#eff6ff', 'Admin': '#fff7ed', 'Teacher': '#eff6ff', 'Admin / Teacher': '#eff6ff',
  'Student': '#f0fdf4', 'Student app': '#f0fdf4', 'Reviewer': '#f5f3ff', 'API': '#f8fafc',
  'Browser': '#f8fafc', 'SSE': '#ecfeff', 'Email worker': '#fff7ed', 'API / Email': '#fff7ed',
  'Job sinkronisasi': '#f1f5f9', 'Student API': '#f0fdf4', 'Web': '#f8fafc', 'Portal': '#f8fafc',
  'Pengirim': '#eff6ff', 'Penerima': '#f0fdf4', 'Peserta': '#eff6ff', 'Sistem': '#f1f5f9'
};

function renderDiagram(diagram) {
  const cells = [
    '<mxCell id="0"/>',
    '<mxCell id="1" parent="0"/>',
    `<mxCell id="title" value="${xml(diagram.title)}" style="text;html=1;align=left;verticalAlign=middle;whiteSpace=wrap;rounded=0;strokeColor=none;fillColor=none;fontSize=23;fontStyle=1;fontColor=#172b4d;" vertex="1" parent="1"><mxGeometry x="40" y="20" width="1120" height="38" as="geometry"/></mxCell>`
  ];
  for (const item of diagram.nodes) {
    const shape = item.shape === 'decision' ? 'rhombus' : item.shape === 'start' || item.shape === 'end' ? 'ellipse' : 'rounded=1;arcSize=12';
    const shapeStyle = item.shape === 'decision' ? 'shape=rhombus;' : item.shape === 'start' || item.shape === 'end' ? 'shape=ellipse;' : 'rounded=1;arcSize=12;';
    const fill = item.shape === 'decision' ? '#fffbeb' : fills[item.actor] || '#ffffff';
    const value = `${item.actor}&#xa;${xml(item.label)}`;
    const style = `${shapeStyle}whiteSpace=wrap;html=1;align=center;verticalAlign=middle;strokeColor=#cbd5e1;strokeWidth=1.4;fillColor=${fill};fontColor=#1f2937;fontSize=14;spacing=8;shadow=0;`;
    cells.push(`<mxCell id="${item.id}" value="${value}" style="${style}" vertex="1" parent="1"><mxGeometry x="${item.x}" y="${item.y}" width="${item.w}" height="${item.h}" as="geometry"/></mxCell>`);
  }
  diagram.edges.forEach((item, index) => {
    const value = item.label ? ` value="${xml(item.label)}"` : '';
    cells.push(`<mxCell id="edge-${index + 1}"${value} style="edgeStyle=orthogonalEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#64748b;strokeWidth=1.5;fontColor=#475569;fontSize=12;labelBackgroundColor=#ffffff;endArrow=block;endFill=1;" edge="1" parent="1" source="${item.from}" target="${item.to}"><mxGeometry relative="1" as="geometry"/></mxCell>`);
  });
  return `<?xml version="1.0" encoding="UTF-8"?>\n<mxfile host="app.diagrams.net" modified="2026-10-24T00:00:00.000Z" agent="DENSO LMS QA" version="24.7.17" type="device"><diagram id="${diagram.file.replace('.drawio', '')}" name="${xml(diagram.title)}"><mxGraphModel dx="1400" dy="1000" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1200" pageHeight="1000" math="0" shadow="0"><root>${cells.join('')}</root></mxGraphModel></diagram></mxfile>\n`;
}

for (const diagram of diagrams) {
  writeFileSync(path.join(outputDirectory, diagram.file), renderDiagram(diagram), 'utf8');
}
console.log(`Generated ${diagrams.length} separate feature flowcharts.`);
