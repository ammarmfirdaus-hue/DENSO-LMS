# DENSO-LMS Postman API Test Suite & Full Backend Analysis

Direktori ini berisi koleksi **Postman v2.1.0** dan environment lokal untuk menguji seluruh endpoint backend **DENSO-LMS** secara menyeluruh:

1. [`DENSO-LMS-Backend.postman_collection.json`](./DENSO-LMS-Backend.postman_collection.json) — **171 request terstruktur** dalam **19 folder utama**:
   - **Folder `01` s/d `17`**: **113 endpoint Express 5 Backend API** (`Backend-LMS-Admin-Teacher`, mencakup `GET /health` dan seluruh 16 domain router pada `/api/v1/*`)
   - **Folder `18`**: **6 endpoint Native Student PHP Portal & API** (`NATIVE-LMS-Student`, mencakup pertukaran sesi SSO, JSON API `me`, `dashboard`, `courses`, halaman sertifikat, dan ruang belajar kursus)
   - **Folder `19`**: **52 skenario uji kompleks (positif & negatif)** yang dikelompokkan dalam 8 sub-folder (`19.1` s/d `19.8`) dengan skrip pengujian otomatis (`pm.test`)
2. [`DENSO-LMS-Local.postman_environment.json`](./DENSO-LMS-Local.postman_environment.json) — **42 variabel environment** prakonfigurasi untuk pengembangan dan pengujian lokal.

---

## 1. Hasil Analisa Arsitektur Backend Terkini

| Aspek Sistem | Spesifikasi Implementasi Aktual |
|---|---|
| **Runtime & Framework** | Node.js CommonJS + Express `5.2.1` (`Backend-LMS-Admin-Teacher`) & PHP `8.2` (`NATIVE-LMS-Student`) |
| **Basis Data Ganda** | **SQL Server (`DLS`)**: 25 tabel operasional utama + **MySQL (`denso_lms_student`)**: 13 tabel portal siswa, kuis, dan sertifikat |
| **Autentikasi & SSO** | Dual Auth: Header `Authorization: Bearer <jwt>` (`JWT_TTL`, default `15m`) dan HttpOnly Cookie `dls_sso` (`8h`, HS256) |
| **Otorisasi RBAC Dinamis** | **4 peran** (`admin`, `teacher`, `reviewer`, `student`) dengan **31 izin granular** pada 12 modul (`dbo.lms_role_definitions`, `dbo.lms_role_permissions`, `dbo.lms_role_audit_logs`) |
| **Proteksi Origin (`CSRF/Origin`)** | Middleware `trustedWriteOrigin` & `trustedStaffOrigin` memvalidasi header `Origin` pada rute tulis (`auth/*-mode`, `quizzes`, `enrollments`, `grades`, `certificates`, `roles`, `forum`, `notifications`, `support-chat`) |
| **Validasi Unggahan Berkas** | Pemeriksaan *magic-byte signature* pada unggahan materi (`250 MB`), sertifikat (`15 MB`), sampul kursus (`5 MB`), dan gambar forum (`5 MB`) |
| **Antrean Pekerjaan (`Jobs`)** | Pemrosesan latar belakang terenkripsi **AES-256-GCM** (`dbo.lms_admin_jobs` & `dbo.lms_admin_job_items`) untuk `users`, `records_delete`, `enrollments_decision`, `notifications_broadcast`, dan `backup_restore` |
| **Komunikasi Realtime (SSE)** | 5 kanal Server-Sent Events (`text/event-stream`): Dashboard Activity, Forum Diskusi, Notifikasi, Live Support Chat, dan Progress Background Job |

---

## 2. Inventaris Lengkap Folder & Endpoint Koleksi Postman

| Folder | Domain / Prefix | Jumlah Request | Ringkasan Cakupan Endpoint |
|---|---|---:|---|
| **01 - System & Health** | `/health`, `/api/v1/settings/runtime-config` | 2 | Pemeriksaan kesehatan server Express dan pengambilan URL API runtime dari database |
| **02 - Authentication & Session** | `/api/v1/auth` | 6 | Login JWT + SSO cookie, perpindahan Teacher <-> Student Study Mode (`teacher_shadow`), profil sesi `/me`, pembaruan profil/sandi, dan logout |
| **03 - Dashboard & Activity** | `/api/v1/dashboard` | 5 | Analitik tren (`GET /analytics`), ringkasan KPI (`GET /summary`), daftar kursus, log aktivitas, dan stream SSE aktivitas |
| **04 - Courses & Sessions** | `/api/v1/courses` | 10 | Daftar & pembuatan kursus, unggah/unduh/hapus foto sampul (`thumbnail`), penugasan pengajar, pengelolaan sesi, detail kursus, dan guard `410` |
| **05 - Material Workflow & Reviewer Portal** | `/api/v1/material-workflow` | 19 | Siklus 5 status materi (`pending_admin` -> `pending_review` -> `approved`/`rejected` -> `published`), magic link SHA-256 reviewer, dan unduhan berkas |
| **06 - Quizzes** | `/api/v1/quizzes` | 5 | Ringkasan kuis per kursus, detail soal pilihan ganda, pembuatan, pembaruan/publikasi, dan penghapusan/pengarsipan kuis di MySQL |
| **07 - Enrollments** | `/api/v1/enrollments` | 4 | Opsi rekomendasi pengajar, daftar permintaan akses kursus, pengajuan rekomendasi oleh Teacher, dan keputusan setuju/tolak oleh Admin |
| **08 - Grades** | `/api/v1/grades` | 4 | Daftar nilai & progres siswa, pembaruan skor/umpan balik (`0-100`), pengosongan nilai, dan pengiriman notifikasi hasil evaluasi |
| **09 - Certificates & Awards** | `/api/v1/certificates` | 5 | Daftar siswa aktif penerima, daftar/pencarian sertifikat & penghargaan, penerbitan sertifikat (`multipart/form-data`), pratinjau/unduh file, dan hapus |
| **10 - Users Management** | `/api/v1/users` | 4 | Daftar akun gabungan SQL Server + MySQL, pembuatan akun lintas peran, pembaruan/promosi `student -> teacher`, dan penghapusan berpelindung |
| **11 - Reviewers Management** | `/api/v1/reviewers` | 5 | Daftar reviewer beserta beban review aktif, registrasi reviewer, pembaruan profil, pengalihan tugas review (`transfer-review-tasks`), dan penonaktifan |
| **12 - Roles & RBAC Matrix** | `/api/v1/roles` | 5 | Profil izin sesi (`/me`), matriks penuh 31 izin & preset, riwayat audit perubahan peran (`/audit`), pembaruan izin peran, dan penerapan preset |
| **13 - Forum** | `/api/v1/forum` | 12 | Stream SSE forum, moderasi buka/tutup sesi, pembuatan topik oleh staf & siswa, moderasi status topik, balasan berlampiran gambar, dan fitur suka (`likes`) |
| **14 - Notifications** | `/api/v1/notifications` | 6 | Penghitung belum dibaca (`unread-count`), stream SSE notifikasi, daftar notifikasi, pengiriman broadcast/popup/email, serta tandai dibaca |
| **15 - Live Support Chat** | `/api/v1/support-chat` | 7 | Status kehadiran Admin (`presence`), detak kehadiran (`heartbeat`), daftar percakapan siswa, daftar/kirim pesan, hapus riwayat, dan stream SSE chat |
| **16 - Background Jobs** | `/api/v1/jobs` | 9 | Daftar job, antrean massal `enrollments`, `notifications`, `records/delete`, dan `users`, polling event inkremental, stream SSE, jeda (`stop`), dan lanjutkan (`resume`) |
| **17 - System Settings, Sync & Backup** | `/api/v1/settings` | 5 | Status API & sinkronisasi MySQL Student, simpan URL API runtime, pemicu sinkronisasi manual, ekspor backup JSON (`14 dataset`), dan antrean restore backup |
| **18 - Native Student PHP Portal & API** | `NATIVE-LMS-Student` | 6 | Pertukaran cookie `dls_sso` ke sesi PHP, JSON API `me`, `dashboard`, `courses`, halaman sertifikat siswa, dan ruang belajar kursus |
| **19 - Complex Test Scenarios** | Sub-folder `19.1` – `19.8` | 52 | Skenario uji positif & negatif terotomasi mencakup validasi Zod `.strict()`, guard `Origin`, RBAC, sertifikat, live chat, dan backup restore |

---

## 3. Cara Mengimpor dan Menjalankan Pengujian di Postman

1. Buka aplikasi **Postman** -> klik **Import**.
2. Pilih kedua berkas di dalam folder ini:
   - `DENSO-LMS-Backend.postman_collection.json`
   - `DENSO-LMS-Local.postman_environment.json`
3. Aktifkan environment **DENSO-LMS Local Environment** pada pemilih environment di pojok kanan atas.
4. Jalankan request **`02 - Authentication & Session -> [Public] Login (JWT + SSO Cookie)`** terlebih dahulu:
   - Skrip pengujian pada request tersebut otomatis menyimpan token JWT ke variabel `{{access_token}}` dan `{{admin_token}}`, serta menyimpan cookie `dls_sso` pada Cookie Jar Postman.
5. Setelah login berhasil, seluruh folder (`01` s/d `19`) dapat dijalankan secara individual maupun berurutan melalui **Collection Runner**.
