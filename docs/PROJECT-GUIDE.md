# DENSO LMS — Panduan Fitur dan Teknis

**Status:** dokumentasi implementasi saat ini  
**Tanggal analisis:** 23 Oktober 2026  
**Cakupan:** Backend Admin/Teacher, Frontend Admin/Teacher, Native Student, database, sinkronisasi, keamanan, dan verifikasi yang tercatat.

Dokumen ini merangkum perilaku yang terlihat pada source code dan skema yang ada. **Live** berarti jalur fitur membaca/menulis ke API atau database aplikasi. **Parsial/demo** berarti layar atau contoh alur tersedia, tetapi belum seluruhnya tersimpan dan diproses sebagai data produksi.

## 1. Ringkasan sistem

DENSO LMS terdiri dari tiga aplikasi yang berkomunikasi melalui API dan berbagi login SSO. Data Admin/Teacher disimpan di SQL Server. Data Student disimpan di MySQL. Backend menyediakan autentikasi, API Admin/Teacher, workflow review materi, email reviewer, serta sinkronisasi opsional di antara kedua database.

| Komponen | Teknologi dan fungsi | Runtime lokal | Sumber data |
|---|---|---|---|
| Backend Admin/Teacher | Node.js, Express, JWT; API, SSO, workflow persetujuan, email reviewer, sinkronisasi | http://127.0.0.1:3001 | SQL Server DX_LMS_AdminTeacher; koneksi MySQL opsional |
| Frontend Admin/Teacher | React, React Router, Vite, Tailwind CSS; portal administrasi dan reviewer | http://127.0.0.1:5173 | Backend API |
| Native Student | PHP Native, Tailwind CSS; halaman peserta dan JSON API same-origin | http://127.0.0.1:8082 | MySQL dls_student; SSO dari backend |

Diagram use case yang dapat diedit: [DENSO-LMS-Use-Case.drawio](diagrams/DENSO-LMS-Use-Case.drawio). Buka file tersebut menggunakan diagrams.net/draw.io.

## 2. Aktor dan hak akses

| Aktor | Kemampuan utama | Batasan penting |
|---|---|---|
| **Admin** | Mengelola akun Admin, Teacher, dan Reviewer; membuat course; menetapkan trainer dan reviewer; melihat ringkasan, aktivitas, progres, dan forum; mengirim materi untuk review; memublikasikan materi yang sudah disetujui. | Hanya Admin yang dapat mengelola akun, menetapkan trainer/reviewer, dan menerbitkan materi. |
| **Teacher/Trainer** | Melihat course yang dibuat atau ditugaskan; mengelola sesi course; mengajukan materi; melihat status pengajuan dan progres peserta pada course yang dapat diakses. | Tidak dapat menetapkan trainer, membuat akun, mengirim undangan review, atau memublikasikan materi. Akses course dibatasi berdasarkan pembuat/penugasan. |
| **Reviewer** | Masuk melalui undangan email sekali pakai; melihat daftar materi yang ditugaskan; membuka pratinjau; menyetujui atau menolak disertai catatan. | Token undangan berlaku terbatas dan ditukar dengan sesi reviewer dua jam. Reviewer hanya dapat memutuskan materi yang ditugaskan kepadanya. Tidak memakai login portal biasa. |
| **Student** | Masuk melalui SSO; melihat dashboard, katalog course aktif, materi terbit, serta data enrollment/progres yang tersedia. | API Native saat ini hanya menyediakan pembacaan me, dashboard, dan course. Penulisan enrollment/progres, hasil kuis, forum, dan notifikasi produksi belum terhubung penuh. |
| **Layanan SMTP** | Mengirim email undangan berisi tautan reviewer. | Memerlukan konfigurasi SMTP di environment backend; pengiriman email nyata belum termasuk tes lokal. |
| **Scheduler sinkronisasi** | Menjalankan sinkronisasi berkala antara SQL Server dan MySQL ketika fitur diaktifkan. | Sinkronisasi nonaktif secara default dan tidak real-time. |

## 3. Fitur per aplikasi

### 3.1 Frontend Admin/Teacher

| Halaman | Kemampuan | Data/status |
|---|---|---|
| /login | Login memakai NPK/email dan password melalui backend SSO. Akun Student dialihkan ke Native Student. | Live |
| / | Ringkasan course, sesi, dan pegawai/peserta sesuai cakupan role. Area grafik aktivitas saat ini berupa placeholder visual. | Ringkasan Live; grafik placeholder |
| /courses | Mencari dan memfilter course; Admin membuat course dan menetapkan trainer. | Live |
| /courses/:id | Melihat detail course, sesi, materi terbit dan pengajuan; menambah sesi; mengajukan materi. Admin mengirim materi ke reviewer dan memublikasikan materi yang disetujui. | Live |
| /materials | Antrean pengajuan; pencarian/filter status; Admin mengedit/menolak pengajuan atau memilih reviewer dan mengirim email. Setelah disetujui reviewer, Admin menerbitkan materi ke student. | Live; email memerlukan SMTP |
| /reviewers | Kelola akun reviewer lengkap dengan profil HRD / petinggi (NPK, nama, email, jabatan, departemen, no. telp, status). | Live; hanya Admin |
| /activity | Melihat, mencari, dan memfilter aktivitas yang tercatat pada backend. | Live |
| /grades | Melihat snapshot progres dan status enrollment yang disinkron dari MySQL. Halaman ini bukan buku nilai kuis. | Live untuk snapshot; hasil kuis belum disinkronkan |
| /forum | Melihat topik dan mengubah status visible, hidden, atau locked. | Live untuk data forum_threads di SQL Server |
| /users | Admin membuat dan melihat akun Admin, Teacher, dan Reviewer. | Live; hanya Admin |
| /review atau /review/:token | Reviewer membuka undangan, melihat materi yang ditugaskan, mempratinjau, approve/reject, dan menambahkan catatan. | Live; sesi reviewer terpisah |

Pencarian, filter, pagination, dan pilihan jumlah baris tersedia pada beberapa daftar. Tidak semua daftar menggunakan pagination server-side; endpoint yang mendukung pagination menerapkan batas input di backend.

### 3.2 Backend Admin/Teacher

Backend mengelompokkan API per domain. Semua route /api/v1 mendapat rate limit dan middleware pagination; operasi selain GET mendapat batas write tambahan. API login memiliki limiter tersendiri.

| Grup route | Operasi penting | Hak akses |
|---|---|---|
| /health | Status proses API | Publik; bukan pemeriksaan koneksi database |
| /api/v1/auth | POST /login, GET /me, POST /logout | Login publik dengan pembatasan percobaan; profil memerlukan sesi |
| /api/v1/dashboard | GET /summary, /courses, /activity | Admin dan Teacher; hasil dibatasi menurut kepemilikan/penugasan |
| /api/v1/courses | Membuat course; menetapkan trainer; membaca detail/sesi; menambah sesi | Admin/Teacher; penetapan trainer hanya Admin |
| /api/v1/users | Daftar dan buat akun | Admin |
| /api/v1/reviewers | GET, POST, PUT akun reviewer berserta jabatan HRD/petinggi, departemen, telepon, status | Admin |
| /api/v1/grades | Membaca snapshot progres Student | Admin/Teacher; cakupan course dibatasi |
| /api/v1/forum | Membaca topik dan mengganti status moderasi | Admin/Teacher; cakupan course dibatasi |
| /api/v1/material-workflow | Upload submission, edit submission (PUT), admin reject, daftar submission, kirim undangan review, konsumsi link, review/decision, publish, akses file | Dibagi menurut role Admin, Teacher, Reviewer, dan Student |

Route lama POST /api/v1/courses/sessions/:sessionId/materials mengembalikan 410 APPROVAL_REQUIRED. Materi harus melewati workflow persetujuan; endpoint tersebut tidak lagi memasukkan materi langsung ke course.

#### Alur persetujuan dan aturan edit materi

1. Teacher mengirim teks, tautan YouTube/HTTPS, atau berkas lokal yang didukung untuk sesi course (status: `pending_admin`).
2. **Kondisi Menunggu Admin (`pending_admin`)**: Teacher terkunci tidak dapat mengedit materi. Admin dapat mengedit materi, menolak materi disertai catatan perbaikan (`POST /submissions/:id/admin-reject`), atau memilih Reviewer dan mengirim undangan melalui email.
3. **Kondisi Dalam Review (`pending_review`)**: Baik Teacher maupun Admin terkunci dan tidak dapat mengedit materi sampai ada keputusan dari Reviewer.
4. **Kondisi Ditolak (`rejected`)**: Terjadi jika Admin atau Reviewer menolak materi. Kunci pengeditan terbuka bagi Teacher (pengaju) dan Admin untuk memperbaiki materi (`PUT /submissions/:id`). Setelah diperbaiki dan dikirim ulang, status kembali menjadi `pending_admin`.
5. **Kondisi Disetujui (`approved`)**: Reviewer menyetujui materi. Reviewer tidak langsung mempublikasikan ke student; hak penerbitan akhir dipegang oleh Admin.
6. **Penerbitan Final (`published`)**: Admin mengklik "Unggah ke Database Student". Backend memasukkan data ke tabel `dbo.course_materials` dan memicu sinkronisasi langsung ke MySQL Student (`syncStudentNow()`).

Tipe berkas yang diizinkan backend meliputi PDF, format Office/ODF umum, video, audio, dan gambar. Berkas disimpan di direktori upload lokal backend yang dikonfigurasi. Validasi signature mengenali header format umum; ini bukan pemindaian antivirus atau validasi penuh isi dokumen.

### 3.3 Native Student

#### Halaman PHP

Route halaman didefinisikan di NATIVE-LMS-Student/index.php. Halaman Student dilindungi sesi Student dan token SSO yang valid.

| Route | Halaman/kemampuan | Status data |
|---|---|---|
| /login, /auth/process, /auth/logout | Login, validasi CSRF, logout sesi lokal dan cookie SSO | Live melalui backend; limiter lokal juga digunakan |
| /student | Dashboard ringkasan dan course aktif | Jumlah enrollment/progres course Live; beberapa statistik lain bersifat contoh atau nilai default |
| /student/courses, /student/courses/filters | Katalog course dan filter | Course aktif dibaca dari MySQL untuk pengguna terautentikasi |
| /student/course/detail, /student/learning | Detail course dan pembaca materi teks/video/dokumen/link | Course dan materi terbit dibaca dari MySQL; akses berkas dibatasi enrollment |
| /student/riwayat, /student/riwayat/detail | Riwayat course selesai dan detail | Status enrollment dapat berasal dari MySQL; detail penilaian/riwayat kuis belum terhubung penuh |
| /student/quiz | Tampilan pre-test/post-test dan bank soal | UI dan bank soal contoh; penyimpanan hasil kuis belum diimplementasikan |
| /student/forum/* | Kategori, thread, dan form topik | Contoh/mock; belum terhubung dengan operasi tulis forum Student |
| /student/notifikasi | Pusat notifikasi | Data contoh; belum merupakan feed notifikasi tersimpan |
| /student/profil/* | Profil, detail, pengaturan, bantuan, riwayat karier | Sebagian besar data profil/riwayat adalah contoh dan belum menjadi pengelolaan profil produksi |

Model DX_LMS_M_Student_Data memiliki fallback dataset demonstrasi untuk pengembangan. Pengguna terautentikasi membaca course dari MySQL; kegagalan pembacaan live tidak otomatis dianggap sebagai data contoh produksi.

#### JSON API Student

NATIVE-LMS-Student/api/index.php hanya menerima GET dan melindungi resource menggunakan sesi Student:

- GET /api/index.php?resource=me
- GET /api/index.php?resource=dashboard
- GET /api/index.php?resource=courses (mendukung page dan limit)

API ini same-origin dan read-only. Belum tersedia endpoint Native untuk membuat enrollment, mengirim progres/penyelesaian materi, menyimpan nilai kuis, menulis post forum, atau mengelola status notifikasi.

## 4. Aliran autentikasi dan data

### SSO

Backend memverifikasi akun karyawan pada SQL Server dan dapat memeriksa akun Student pada MySQL jika STUDENT_AUTH_ENABLED=true. Backend menerbitkan token HS256 dengan issuer/audience terkonfigurasi dan cookie SSO HttpOnly. Frontend memakai API backend; Native memverifikasi token cookie bersama dan membentuk sesi PHP Student. Secret SSO dibagikan melalui environment server, bukan frontend.

### Sinkronisasi database

| Arah | Data | Pemicu |
|---|---|---|
| SQL Server → MySQL | Course, sesi, serta materi yang berada di tabel course materials | Periodik jika STUDENT_SYNC_ENABLED=true; dapat dicoba langsung setelah publish |
| MySQL → SQL Server | Enrollment/progres per Student dan course ke student_progress_snapshots | Bersamaan dengan job sync |

Sinkronisasi opsional, berkala, dan bukan transaksi real-time. Hasil kuis, konten forum Student, notifikasi, dan perubahan profil tidak termasuk kontrak sinkronisasi saat ini.

### Tabel data utama

- **SQL Server Admin/Teacher:** employee_accounts, courses, course_sessions, course_materials, activity_logs, student_progress_snapshots, forum_threads.
- **SQL Server workflow:** material_submissions, material_review_links.
- **MySQL Student:** student_accounts, courses, course_enrollments, course_sessions, course_materials, material_progress, activity_logs, login_rate_limits.

DDL utama terdapat di database/admin&teacher.sql dan database/student.sql. Tabel workflow dan perubahan sinkronisasi mempunyai migration tersendiri.

## 5. Struktur folder

    DENSO-LMS/
    ├── Backend-LMS-Admin-Teacher/
    │   ├── src/config/          # Validasi environment dan pool SQL Server
    │   ├── src/middleware/      # Auth/role, limit, pagination, upload, error
    │   ├── src/routes/          # Endpoint auth, course, user, workflow, dan lain-lain
    │   ├── src/services/        # Auth, email review, auth Student, sinkronisasi
    │   ├── scripts/             # Seeder akun dan data demo
    │   └── tests/               # Tes keamanan per area risiko
    ├── Frontend-LMS-Admin-Teacher/
    │   ├── src/api/             # Klien API dan token/session browser
    │   ├── src/components/      # Sidebar, filter daftar, pagination
    │   ├── src/features/        # Auth, dashboard, course, materi, reviewer, dll.
    │   ├── src/layouts/         # Layout portal
    │   ├── src/styles/          # Token dan style bersama Tailwind
    │   └── public/assets/       # Logo, ikon, gambar, font
    ├── NATIVE-LMS-Student/
    │   ├── Core/                # Router, controller base, request middleware
    │   ├── Controllers/         # Auth dan modul Student per fitur
    │   ├── Models/Student/      # Query adapter dan dataset demo Student
    │   ├── Views/               # Template login dan halaman per fitur
    │   ├── includes/            # Config, PDO, security, API, pagination, komponen
    │   ├── api/                 # JSON API read-only
    │   ├── public/assets/       # Aset publik terbangun
    │   ├── assets/css/          # Sumber Tailwind CSS
    │   ├── scripts/             # Seeder/provisioning Student
    │   └── tests/               # Runner PHP dan suite keamanan per area
    ├── database/                # DDL SQL Server/MySQL dan migration
    └── docs/                    # Dokumentasi sistem dan diagram Draw.io

## 6. Keamanan yang diterapkan

- JWT mematok algoritma HS256 serta memeriksa issuer, audience, masa berlaku, dan role.
- Middleware role memisahkan kewenangan Admin, Teacher, Reviewer, dan Student.
- Cookie SSO bersifat HttpOnly dan SameSite; konfigurasi production mensyaratkan HTTPS dan secret kuat.
- Login, API umum, operasi tulis, upload, dan Native request memiliki rate limit.
- Input API divalidasi dengan Zod; SQL API memakai parameter binding/prepared statements.
- Upload dibatasi ukuran, jumlah, dan ekstensi; nama file server-side acak; signature dasar dicek.
- Link reviewer sekali pakai, terikat pada akun reviewer, dibatasi masa berlaku; sesi reviewer berumur pendek.
- Native memakai session strict mode, CSRF untuk form sensitif, escaping HTML, header keamanan, PDO native prepares, dan router controller allowlist.
- File Student hanya dikirim untuk materi yang sudah terbit serta enrollment yang berhak.

Daftar ini menjelaskan kontrol yang terlihat di source, bukan sertifikasi keamanan. Konfigurasi deployment, izin SQL, TLS, backup, dan pengelolaan secret tetap harus disesuaikan operator.

## 7. Verifikasi dan tes

| Proyek | Perintah/cek | Yang diperiksa | Hasil terakhir |
|---|---|---|---|
| Backend | npm test | JWT valid/tamper/expiry/issuer/audience/algoritma; auth tanpa token; role; input pagination adversarial; signature file palsu; storage upload; sanitasi error | 31 tes lulus |
| Native | php tests/run.php | HS256 SSO; token rusak/expired/audience/issuer/role; CSRF/session flags; router traversal; output XSS escaping; pagination ekstrem | 12 pemeriksaan lulus |
| Frontend | npm run build | Kompilasi Vite production dan transformasi modul React/CSS | Berhasil; 56 modul ditransformasi |
| Native CSS | npm run build:css | Build Tailwind dari sumber CSS Native ke aset publik | Berhasil |
| PHP | php -l untuk seluruh Native PHP | Sintaks file PHP | 57 file lulus |

Tes di atas adalah unit/security checks lokal dan build checks. 

## 9. Menjalankan lingkungan lokal

Gunakan instruksi instalasi dan DDL pada README utama dan README tiap aplikasi. Perintah umum:

- Backend: npm install, atur .env, lalu npm run dev.
- Frontend: npm install, atur VITE_API_BASE_URL, lalu npm run dev.
- Native: siapkan PHP dengan pdo_mysql, atur .env, npm install, npm run build:css, lalu jalankan server PHP sesuai README Native.

Jalankan seeder hanya pada database pengembangan. File .env berisi kredensial privat dan tidak boleh dimasukkan ke Git.

