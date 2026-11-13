# DENSO LMS — Panduan Fitur dan Teknis

**Status:** dokumentasi implementasi saat ini
**Tanggal analisis:** 25 Oktober 2026
**Cakupan:** Backend Admin/Teacher, Frontend Admin/Teacher, Native Student, database, sinkronisasi, keamanan, bata fitur, dan verifikasi audit 25 Oktober 2026.

Dokumen ini merangkum perilaku yang terlihat pada source code dan skema yang ada. **Live** berarti jalur fitur membaca/menulis ke API atau database aplikasi. **Parsial/demo** berarti layar atau contoh alur tersedia, tetapi belum seluruhnya tersimpan dan diproses sebagai data produksi.

## 1. Ringkasan sistem

DENSO LMS terdiri dari tiga runtime yang memakai Backend Node sebagai API dan penyedia SSO. Akun/course/workflow/nilai snapshot/forum/notifikasi/job disimpan di SQL Server; akun Student, course hasil sync, enrollment/nilai/progres berada di MySQL. Native Student memakai backend yang sama untuk SSO, forum, dan notifikasi. Sinkronisasi katalog dan nilai/progres dikendalikan konfigurasi backend.

| Komponen               | Teknologi dan fungsi                                                                    | Runtime lokal         | Sumber data                                            |
| ---------------------- | --------------------------------------------------------------------------------------- | --------------------- | ------------------------------------------------------ |
| Backend Admin/Teacher  | Node.js, Express, JWT; API, SSO, workflow persetujuan, email reviewer, sinkronisasi     | http://127.0.0.1:3001 | SQL Server DX_LMS_AdminTeacher; koneksi MySQL opsional |
| Frontend Admin/Teacher | React, React Router, Vite, Tailwind CSS; portal administrasi, reviewer, PWA installable | http://127.0.0.1:5173 | Backend API                                            |
| Native Student         | PHP Native, Tailwind CSS; halaman peserta, PWA installable, JSON API same-origin        | http://127.0.0.1:8082 | MySQL dls_student; SSO dari backend                    |

Diagram use case yang dapat diedit: [DENSO-LMS-Use-Case.drawio](diagrams/DENSO-LMS-Use-Case.drawio). Flow login, akun, reviewer, course, belajar, notifikasi, nilai, antrean, dan PWA ada di [FEATURES-AND-QA.md](FEATURES-AND-QA.md#daftar-fitur). Audit source per fitur ada di [IMPLEMENTATION-AUDIT.md](IMPLEMENTATION-AUDIT.md); panduan deployment ada di [PWA-AND-DEPLOYMENT.md](PWA-AND-DEPLOYMENT.md).

## 2. Aktor dan hak akses

| Aktor                            | Kemampuan utama                                                                                                                                                                                                 | Batasan penting                                                                                                                                                                                                                                                  |
| -------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Admin**                  | Mengelola semua role akun, course, trainer, reviewer, nilai, notifikasi, aktivitas, progres, dan forum; mengajukan serta menerbitkan materi yang disetujui; memutus permintaan enrollment.                      | Admin berwenang mengelola akun lintas role, mempromosikan Student menjadi Teacher, menyetujui/menolak akses course, dan menghapus nilai. Akun dengan relasi/riwayat mungkin hanya dapat dinonaktifkan.                                                           |
| **Teacher/Trainer**        | Melihat course yang dibuat atau ditugaskan; mengelola sesi, mengajukan materi, membuat/mengubah/menghapus quiz course, mengedit nilai, merekomendasikan Student untuk course, dan membuka mode belajar Student. | Tidak dapat mengelola akun staff/reviewer, memutus akses course, mengirim kredensial akun, menghapus nilai course, menetapkan trainer, atau menerbitkan materi. Course rekomendasi dibatasi menurut pembuat/penugasan. Role SQL tetap Teacher pada mode belajar. |
| **Reviewer**               | Masuk melalui undangan email sekali pakai; melihat daftar materi yang ditugaskan; membuka pratinjau; menyetujui atau menolak disertai catatan.                                                                  | Token undangan berlaku terbatas dan ditukar dengan sesi reviewer dua jam. Reviewer hanya dapat memutuskan materi yang ditugaskan kepadanya. Tidak memakai login portal biasa.                                                                                    |
| **Student**                | Masuk melalui SSO; melihat katalog course, mengajukan akses, lalu membuka materi terbit setelah enrollment disetujui Admin; melihat quiz course, nilai/progres sendiri, forum, dan notifikasi.                  | Materi, quiz, forum, file, dan progres dilindungi pemeriksaan enrollment di server. Setiap quiz course hanya dapat dikirim satu kali; submit diperiksa dan dikunci di server. PWA tetap memerlukan koneksi untuk data pribadi dan submit.                        |
| **Layanan SMTP**           | Mengirim undangan reviewer, kredensial akun, serta email notifikasi/nilai yang diminta.                                                                                                                         | Memerlukan konfigurasi SMTP; tes lokal menggunakan stub, bukan pengiriman ke alamat eksternal.                                                                                                                                                                   |
| **Scheduler sinkronisasi** | Menjalankan sinkronisasi berkala antara SQL Server dan MySQL ketika fitur diaktifkan.                                                                                                                           | Sinkronisasi nonaktif secara default dan tidak real-time.                                                                                                                                                                                                        |

## 3. Fitur per aplikasi

### 3.1 Frontend Admin/Teacher

| Halaman                     | Kemampuan                                                                                                                                                                                                                                                                                                  | Data/status                                                                 |
| --------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------- |
| /login                      | Login memakai NPK/email dan password melalui backend SSO. Akun Student dialihkan ke Native Student.                                                                                                                                                                                                        | Live                                                                        |
| /                           | Ringkasan course, sesi, dan pegawai/peserta sesuai cakupan role. Area grafik aktivitas saat ini berupa placeholder visual.                                                                                                                                                                                 | Ringkasan Live; grafik placeholder                                          |
| /courses dan /courses/:id   | Admin/Teacher melihat course yang dibuat atau ditugaskan dan dapat membuat course/sesi. Admin menetapkan trainer. Halaman detail menampilkan materi terbit dan submission serta membuka alur pengajuan.                                                                                                    | Live; API saat ini belum menyediakan edit/hapus umum untuk course atau sesi |
| /materials                  | Antrean submission; filter status; Admin memperbaiki/menolak, memilih reviewer dan mengirim undangan. Admin memublikasikan materi approved; aksi hapus tunduk relasi/riwayat.                                                                                                                              | Live; email memerlukan SMTP                                                 |
| /quizzes                    | Admin/Teacher membuat, mengedit, menyimpan draft/menerbitkan, atau menghapus quiz pilihan ganda pada course yang mereka kelola. Quiz yang memiliki attempt dinonaktifkan untuk menjaga nilai.                                                                                                              | Live; definisi quiz disimpan di MySQL Student                               |
| /reviewers                  | Admin mengelola profil reviewer, status, aktivasi kembali, pengiriman kredensial, dan penghapusan lewat antrean. Email meminta kata sandi baru; penghapusan dapat menjadi penonaktifan jika ada relasi, dan tidak diizinkan saat masih ada review aktif.                                                   | Live; hanya Admin                                                           |
| /activity                   | Melihat, mencari, dan memfilter aktivitas yang tercatat pada backend.                                                                                                                                                                                                                                      | Live                                                                        |
| /grades                     | Menampilkan snapshot enrollment/progres, nilai quiz otomatis, dan nilai course; edit nilai course 0-100/umpan balik; Admin dapat mengosongkan nilai course tanpa menghapus progres atau nilai quiz. Ekspor `.xlsx` memuat kedua nilai.                                                                   | Live; nilai quiz ikut sync berkala dari Student                             |
| /forum                      | Moderasi thread, membaca diskusi Student/Staff, membuka/menutup forum per sesi, membuat post/balasan, like, lampiran gambar, dan menerima pembaruan SSE.                                                                                                                                                   | Live; event realtime memakai event bus dalam proses backend                 |
| /notifications              | Inbox per role/penerima, badge unread, SSE, baca satu/semua; Admin dapat memilih audiens sesuai aturan, Teacher hanya Student. Email opsional diantrikan di outbox.                                                                                                                                        | Live; email perlu SMTP                                                      |
| /users                      | Admin mengelola semua role; Teacher terbatas pada Student. Admin impor/ekspor CSV, create/edit/status, bulk delete/deactivate, dan email akun. Password akun baru atau email kredensial memakai password baru yang di-hash bcrypt di database; salinan plaintext hanya dikirim ke email pada job tersebut. | Live; operasi massal tersimpan sebagai job                                  |
| /enrollments                | Admin menyetujui/menolak permintaan Student dan rekomendasi Teacher; Teacher mencari Student aktif dan merekomendasikan course sesuai scope. Status rekomendasi dipantau berkala; keputusan mengirim SSE notifikasi terarah ke Student.                                                                    | Live; request dan enrollment di MySQL, keputusan hanya role Admin           |
| /review atau /review/:token | Reviewer membuka undangan, melihat materi yang ditugaskan, mempratinjau, approve/reject, dan menambahkan catatan.                                                                                                                                                                                          | Live; sesi reviewer terpisah                                                |

Pencarian, filter, pagination, dan pilihan jumlah baris tersedia pada beberapa daftar. Tidak semua daftar menggunakan pagination server-side; endpoint yang mendukung pagination menerapkan batas input di backend.

### 3.2 Backend Admin/Teacher

Backend mengelompokkan API per domain. Semua route /api/v1 mendapat rate limit dan middleware pagination; operasi selain GET mendapat batas write tambahan. API login memiliki limiter tersendiri.

| Grup route                | Operasi penting                                                                                                                                       | Hak akses                                                                                 |
| ------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| /health                   | Status proses API                                                                                                                                     | Publik; bukan pemeriksaan koneksi database                                                |
| /api/v1/auth              | POST /login, GET /me, PUT /profile, POST /logout                                                                                                      | Login publik dengan limiter; profil dan logout memerlukan sesi                            |
| /api/v1/dashboard         | GET /summary, /courses, /activity                                                                                                                     | Admin dan Teacher; hasil dibatasi menurut kepemilikan/penugasan                           |
| /api/v1/courses           | Membuat course; menetapkan trainer; membaca detail/sesi; menambah sesi                                                                                | Admin/Teacher; penetapan trainer hanya Admin                                              |
| /api/v1/users             | Daftar, buat, edit, hapus/nonaktifkan akun, email kredensial                                                                                          | Admin penuh; Teacher hanya Student                                                        |
| /api/v1/enrollments       | Daftar permintaan, opsi course/student Teacher, rekomendasi Teacher, approve/reject Admin                                                             | RBAC pada backend; satu pending request per Student/course dijaga constraint MySQL        |
| /api/v1/reviewers         | GET, POST, PUT profil/status reviewer; DELETE pada route ini menonaktifkan                                                                            | Admin; email kredensial dan hard-delete dikerjakan melalui job pengguna                   |
| /api/v1/grades            | Baca progres/nilai; edit nilai; hapus nilai; kirim notifikasi/email Student                                                                           | Admin/Teacher; course dibatasi; delete nilai Admin                                        |
| /api/v1/quizzes           | Daftar course dan quiz; baca/tulis pertanyaan; hapus atau arsip quiz                                                                                  | Admin/Teacher; course dibatasi menurut pembuat/penugasan; mutasi memerlukan origin portal |
| /api/v1/jobs              | Buat job akun, daftar/snapshot/event, stream SSE, stop/resume                                                                                         | Admin/Teacher; data job dibatasi pemilik/role dan Teacher hanya Student                   |
| /api/v1/forum             | Feed/moderasi, sesi open/close, thread/reply/like, lampiran, event SSE                                                                                | Staff dan Student; scope course/enrollment diperiksa                                      |
| /api/v1/notifications     | Inbox, unread, SSE, baca, kirim ke role/Student, outbox email                                                                                         | Role dan target recipient dibatasi; Teacher hanya mengirim ke Student                     |
| /api/v1/material-workflow | Upload submission, edit submission (PUT), admin reject, daftar submission, kirim undangan review, konsumsi link, review/decision, publish, akses file | Dibagi menurut role Admin, Teacher, Reviewer, dan Student                                 |

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

| Route                                                   | Halaman/kemampuan                                                                                | Status data                                                                                                |
| ------------------------------------------------------- | ------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| /login, /auth/process, /auth/logout, /auth/teacher-mode | Login, validasi CSRF, logout; pertukaran sesi mode belajar Teacher kembali ke portal staff       | Live melalui backend; Teacher mode berumur maksimum 30 menit                                               |
| /student                                                | Dashboard ringkasan dan course aktif                                                             | Jumlah enrollment/progres course Live; beberapa statistik lain bersifat contoh atau nilai default          |
| /student/courses, /student/courses/filters              | Katalog course dan filter; detail akses menampilkan status pending/rejected atau form permintaan | Metadata course aktif dibaca dari MySQL; materi tidak dikirim sebelum enrollment active/completed          |
| /student/nilai                                          | Rekap nilai quiz, nilai course, umpan balik, progres, dan status enrollment milik Student        | Live dari MySQL `course_enrollments`, dibaca controller PHP                                              |
| /student/course/detail, /student/learning               | Detail course dan pembaca materi teks/video/dokumen/link; Student menandai setiap materi selesai | Course, materi, serta progress material dibaca/ditulis di MySQL; quiz terkunci sampai semua materi selesai |
| /student/riwayat, /student/riwayat/detail               | Riwayat course selesai dan detail                                                                | Status enrollment dapat berasal dari MySQL; detail penilaian/riwayat kuis belum terhubung penuh            |
| /student/quiz                                           | Quiz pilihan ganda course, pemeriksaan jawaban, nilai, dan tanda satu kali pengerjaan            | Quiz course/attempt tersimpan di MySQL dan dibuka setelah semua materi aktif selesai                       |
| /student/forum/*                                        | Kategori, thread, balasan, like, dan lampiran                                                    | Backend API dan SQL Server; perlu jaringan dan enrollment/course/session sah                               |
| /student/notifikasi                                     | Inbox, unread, baca satu/semua, badge realtime                                                   | Backend API; event SSE saat koneksi tersedia                                                               |
| /student/profil/*                                       | Profil, detail karyawan, pengaturan, bantuan, riwayat akses/karier                               | Campuran sesi/demo; belum tersedia CRUD profil produksi                                                    |

Kedua portal Student dan Admin/Teacher dapat dipasang sebagai PWA. Service worker hanya menyimpan aset statis, manifest, ikon, dan halaman offline; layar akun, data API, file upload, serta perubahan data tetap membutuhkan koneksi. Lihat [PWA-AND-DEPLOYMENT.md](PWA-AND-DEPLOYMENT.md).

Model DX_LMS_M_Student_Data memiliki fallback dataset demonstrasi untuk pengembangan. Pengguna terautentikasi membaca course dari MySQL; kegagalan pembacaan live tidak otomatis dianggap sebagai data contoh produksi.

#### JSON API Student

NATIVE-LMS-Student/api/index.php hanya menerima GET dan melindungi resource menggunakan sesi Student:

- GET /api/index.php?resource=me
- GET /api/index.php?resource=dashboard
- GET /api/index.php?resource=courses (mendukung page dan limit)

API Native `api/index.php` same-origin dan GET-only untuk `me`, `dashboard`, dan `courses`. Nilai dan submit quiz dibaca/ditulis controller PHP lewat PDO; forum/notifikasi memakai Backend API.

## 4. Aliran autentikasi dan data

### SSO

Backend memverifikasi akun karyawan pada SQL Server dan akun Student `account_type=student` pada MySQL jika `STUDENT_AUTH_ENABLED=true`. Setiap request dengan token Student juga memeriksa status dan jenis akun melalui query berindeks agar token Student lama tidak berlaku setelah akun dinonaktifkan/dipromosikan. Login membuat cookie SSO HttpOnly berdurasi 8 jam; portal staff mengirim cookie pada panggilan API. Teacher mode memeriksa ulang role/status pegawai aktif, membuat/memakai ulang akun MySQL `teacher_shadow` non-login, dan menerbitkan cookie Student maksimum 30 menit. Sesi kembali memeriksa Teacher aktif sebelum menerbitkan cookie Teacher. Middleware juga mendukung bearer token. Native memvalidasi cookie SSO dan mode klaim sebelum membentuk sesi PHP. Token review terpisah disimpan di `sessionStorage` selama sesi review. Semua secret dibagikan lewat environment server.

### Sinkronisasi database

| Arah                | Data                                                                                                                                                      | Pemicu                                                                         |
| ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| SQL Server → MySQL | Course, sesi, serta materi yang berada di tabel course materials; materi sumber yang dihapus diarsipkan nonaktif agar progres dan attempt lama tetap utuh | Periodik jika STUDENT_SYNC_ENABLED=true; dapat dicoba langsung setelah publish |
| MySQL ke SQL Server | Enrollment/progres dan quiz_score per Student/course ke student_progress_snapshots                                                                        | Berkala saat STUDENT_SYNC_ENABLED aktif                                        |

Sinkronisasi course/material dan progres/nilai adalah integrasi opsional/berkala; perubahan nilai yang dibuat staff juga menjadwalkan update target ke MySQL. Quiz dan attempt disimpan di MySQL Student; nilai quiz masuk ke snapshot SQL Server pada sync berikutnya. Ini bukan transaksi tunggal lintas database. Forum dan notifikasi memakai API/backend SQL Server langsung, bukan disalin melalui sync katalog.

### Tabel data utama

- **SQL Server Admin/Teacher:** employee_accounts, courses, course_sessions, course_materials, activity_logs, student_progress_snapshots (nilai/progres), forum_threads, notifikasi/outbox, lms_admin_jobs, lms_admin_job_events.
- **SQL Server workflow:** material_submissions, material_review_links.
- **MySQL Student:** student_accounts (`account_type` student/teacher_shadow), courses, course_enrollments (nilai/progres/akses aktif), course_enrollment_requests (permintaan/rekomendasi dan keputusan), course_sessions, course_materials, material_progress, activity_logs, login_rate_limits.

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
- PWA tidak men-cache HTML akun, API, upload, atau form; cache service worker dibatasi ke aset statis dan offline fallback.

Daftar ini menjelaskan kontrol yang terlihat di source, bukan sertifikasi keamanan. Konfigurasi deployment, izin SQL, TLS, backup, dan pengelolaan secret tetap harus disesuaikan operator.

## 7. Batas implementasi yang ditemukan

- Kuis/assessment Student belum menyimpan attempt, jawaban, atau skor ke tabel produksi. Nilai resmi adalah nilai course yang diedit Admin/Teacher, bukan hasil quiz.
- Halaman profil Student sebagian berupa data tampilan/demo; belum ada alur simpan perubahan profil.
- Course/sesi dapat dibuat dan dilihat, tetapi API saat ini tidak menyediakan endpoint edit/hapus umum untuk course/sesi. Kelola materi dan statusnya memiliki alur terpisah.
- Email kredensial selalu memerlukan kata sandi baru yang diketahui saat job dibuat. Database hanya menyimpan hash bcrypt, sehingga kata sandi lama tidak dapat dipulihkan/dikirim ulang. Email antrean mengubah password akun ke kata sandi baru lalu mengirim nilai yang sama kepada pengguna.
- Penghapusan akun dapat ditolak oleh relasi. Untuk job bulk, kasus riwayat tertentu dialihkan ke nonaktif agar data tetap tersimpan. Reviewer dengan pekerjaan review aktif tidak bisa dihapus/dinonaktifkan.
- Event forum dan notifikasi realtime memakai event emitter lokal proses. Deploy multi-instance memerlukan pub/sub bersama agar perubahan yang masuk ke instance lain sampai ke seluruh SSE client.
- JWT/cookie tidak memiliki mekanisme revokasi global. Logout menghapus cookie pada browser saat ini; token lain yang sudah diterbitkan berlaku sampai kedaluwarsa.
- PWA menyediakan aset publik dan offline fallback saja. Operasi, API, SSE, dan data pengguna tetap online-only.

Rincian alur, route, lokasi source, data tersimpan, dan batasan tiap fitur ada di [IMPLEMENTATION-AUDIT.md](IMPLEMENTATION-AUDIT.md). Diagram lama yang diperbarui ditautkan di [FEATURES-AND-QA.md](FEATURES-AND-QA.md).

## 8. Verifikasi dan tes

| Proyek          | Perintah/cek                                                             | Yang diperiksa                                                                                  | Hasil terakhir                                                                                             |
| --------------- | ------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| Backend         | `npm test`                                                             | Auth/RBAC, workflow, job recovery/SSE, email credential, notification rules, upload, pagination | 72 lulus, 4 dilewati (smoke memerlukan runtime lokal), 0 gagal                                             |
| Frontend        | `npm test`                                                             | API retry/timeout, akses role, kompresi gambar                                                  | 9 lulus, 1 dilewati (browser smoke perlu konfigurasi), 0 gagal                                             |
| Native          | `php tests/run.php all`                                                | SSO/CSRF, router, escaping, pagination, route/controller map                                    | 14 lulus, 0 gagal; PHP menampilkan warning session path Laragon yang tak dapat diakses di lingkungan audit |
| Frontend PWA    | `npm.cmd run build -- --configLoader runner --outDir .pwa-build-check` | Build production dan penyalinan manifest, service worker, offline page, ikon                    | Berhasil; folder pemeriksaan sementara sudah dihapus                                                       |
| Dokumentasi PWA | JSON/XML/link check                                                      | Manifest valid, tiga diagram XML valid, target tautan lokal ada                                 | Lulus                                                                                                      |

Tes ini memakai test isolation dan tidak membuktikan email SMTP eksternal, semua alur mutasi ke database produksi, beban multi-instance, atau pemasangan pada setiap browser/perangkat. Smoke API dan browser yang diberi tanda skip perlu dijalankan terpisah saat runtime dan browser tersedia.

## 9. Menjalankan lingkungan lokal

Gunakan instruksi instalasi dan DDL pada README utama dan README tiap aplikasi. Perintah umum:

- Backend: npm install, atur .env, lalu npm run dev.
- Frontend: npm install, lalu npm run dev. Development memakai proxy same-origin `/api/v1` ke backend lokal; set `VITE_API_PROXY_TARGET` bila backend memakai origin lain. Untuk frontend yang di-host terpisah, set `VITE_API_BASE_URL` sesuai origin API.
- Native: siapkan PHP dengan pdo_mysql, atur .env, npm install, npm run build:css, lalu jalankan server PHP sesuai README Native.

Jalankan seeder hanya pada database pengembangan. File .env berisi kredensial privat dan tidak boleh dimasukkan ke Git.
