# DENSO Learning Management System (DENSO-LMS)

Repositori ini berisi sistem **DENSO Learning Management System (DENSO-LMS)** yang terdiri dari tiga komponen utama: layanan API backend, portal web pengelola dan pengajar, serta portal pembelajaran siswa.

| Komponen | Teknologi Utama | Alamat Lokal | Basis Data |
|---|---|---|---|
| `Backend-LMS-Admin-Teacher` | Node.js, Express, JWT (`HS256`) | `http://127.0.0.1:3001` | Microsoft SQL Server (`DX_LMS_AdminTeacher`) |
| `Frontend-LMS-Admin-Teacher` | React 19, Vite, Tailwind CSS | `http://127.0.0.1:5173` | Terhubung ke REST API Backend |
| `NATIVE-LMS-Student` | PHP 8.1+ Native MVC, Tailwind CSS | `http://127.0.0.1:8082` | MySQL 8+ (`dls_student`) |

---

## 1. Peran Pengguna & Ringkasan Fitur Utama

Sistem mendukung empat peran pengguna (`role`) dengan pembagian hak akses dinamis (**31 izin fungsional pada 12 modul**):

1. **Administrator (`admin`)**: Pengelolaan penuh kursus dan sesi, moderasi materi (pra-review, kirim ke reviewer, terbitkan), manajemen quiz, rekap & ekspor nilai, penerbitan sertifikat PDF, persetujuan enrollment, kelola pengguna (termasuk impor/ekspor CSV), kelola reviewer, pengaturan RBAC, konfigurasi API & sinkronisasi MySQL, backup/restore 14 dataset JSON, forum, broadcast notifikasi, serta konsol Live Support Chat.
2. **Pengajar / Trainer (`teacher`)**: Pengelolaan kursus ampuan dan sesi pertemuan, pengajuan & revisi materi, penyusunan quiz pilihan ganda, pengisian nilai akhir & penerbitan sertifikat kelas ampuan, rekomendasi enrollment siswa, forum diskusi sesi, pengingat belajar, serta simulasi **Mode Belajar Siswa** (30 menit).
3. **Peserta (`student`)**: Akses Portal Siswa untuk memantau progres belajar di Beranda, menjelajahi Katalog Course dan mengajukan enrollment, membaca materi berurutan hingga 100%, mengerjakan Quiz, melihat Nilai, mengunduh Sertifikat PDF di menu Riwayat, berdiskusi di Forum Sesi, membaca Notifikasi, dan menghubungi Admin lewat Live Chat.
4. **Peninjau Materi (`reviewer`)**: Mengakses portal tinjauan melalui tautan undangan sekali pakai (berlaku 2 jam setelah dibuka) untuk memeriksa pratinjau materi lalu memutuskan **Setujui Materi** atau **Minta Revisi**.

---

## 2. Keamanan Sistem

* **Autentikasi & Sesi**: JWT (`HS256`, TTL 15 menit) yang disimpan pada cookie SSO `HttpOnly` (`SameSite=Lax`), serta token undangan Reviewer sekali pakai yang di-hash dengan `SHA-256`.
* **Kata Sandi & Enkripsi**: Hashing kata sandi memakai `bcryptjs`; antrean impor pengguna massal dan pengacakan ID rute publik dienkripsi menggunakan `AES-256-GCM`.
* **Perlindungan HTTP & API**: Pembatasan percobaan login (`express-rate-limit` per IP + NPK), validasi input (`Zod`), CORS whitelist, `Helmet`, `X-Frame-Options: DENY`, `Content-Security-Policy`, pengecekan header `Origin`, token CSRF pada formulir Portal Siswa, serta *parameterized queries* dan daftar putih 14 tabel untuk Backup/Restore.

---

## 3. Instalasi dan Menjalankan Aplikasi (Windows)

### A. Pemasangan Dependensi
Jalankan skrip [`Install-Runtime-All.bat`](Install-Runtime-All.bat) dari direktori utama untuk memasang dependensi ketiga komponen:
- `Backend-LMS-Admin-Teacher\install.bat` (memeriksa Node.js 20+, memasang paket npm, menyiapkan `.env`).
- `Frontend-LMS-Admin-Teacher\install.bat` (memeriksa Node.js 20+, memasang paket npm, menyiapkan `.env`).
- `NATIVE-LMS-Student\install.bat` (memeriksa PHP 8.1+ & ekstensi `pdo_mysql`, memasang dependensi Tailwind, membangun CSS).

> Pastikan koneksi database SQL Server, MySQL, serta nilai `JWT_SECRET` (Backend) dan `SSO_JWT_SECRET` (Student) pada masing-masing berkas `.env` telah disesuaikan.

### B. Migrasi Basis Data
Panduan izin dan rincian skema tersedia di [`database/MIGRATIONS.md`](database/MIGRATIONS.md). Jalankan migrasi dari direktori utama:

```powershell
# Melalui skrip PowerShell interaktif:
.\Start-All-Migration.ps1

# Atau melalui Node CLI langsung:
node database/migrate.js status all
node database/migrate.js up all
```

### C. Pengisian Data Demo & Menjalankan Server
* Untuk mengisi data contoh berprefix `DLS-DEMO-*` ke kedua database:
  ```powershell
  .\Start-All-Datadummy.ps1
  ```
* Untuk menjalankan seluruh server (`3001`, `5173`, dan `8082`) secara bersamaan:
  ```bat
  Start-All-Server.bat
  ```

---

## 4. Struktur Direktori Utama

* `Backend-LMS-Admin-Teacher/` — REST API Express (`src/config`, `src/middleware`, `src/routes`, `src/services`, `tests/`).
* `Frontend-LMS-Admin-Teacher/` — Aplikasi SPA React (`src/features/<modul>`, `src/components`, `src/layouts`, `src/utils`).
* `NATIVE-LMS-Student/` — Portal Siswa PHP MVC (`Controllers/`, `Models/`, `Views/`, `Core/`, `includes/`, `public/`).
* `database/` — Skrip migrasi terkelola (`migrations/sqlserver/`, `migrations/mysql/`, `migrate.js`, `MIGRATIONS.md`).
* `postman/` — Koleksi pengujian API ([`DENSO-LMS-Backend.postman_collection.json`](postman/DENSO-LMS-Backend.postman_collection.json), 19 folder / 171 request) dan environment lokal ([`DENSO-LMS-Local.postman_environment.json`](postman/DENSO-LMS-Local.postman_environment.json)).
* `docs/` — Dokumentasi teknis, makalah, diagram alir Draw.io, dan skema ERD.

---

## 5. Daftar Dokumentasi & Diagram

* **Dokumentasi Lengkap & Laporan Pengujian**: [`docs/Makalah-Implementasi-DENSO-LMS.docx`](docs/Makalah-Implementasi-DENSO-LMS.docx) | [`docs/Makalah-Implementasi-DENSO-LMS.pdf`](docs/Makalah-Implementasi-DENSO-LMS.pdf)
* **Panduan Proyek & Instalasi**: [`docs/01-Project-Overview-and-Setup.docx`](docs/01-Project-Overview-and-Setup.docx) ([`docs/PROJECT-GUIDE.docx`](docs/PROJECT-GUIDE.docx))
* **Referensi Backend API**: [`docs/02-Backend-API-Reference.docx`](docs/02-Backend-API-Reference.docx) ([`docs/documentation-backend.docx`](docs/documentation-backend.docx))
* **Dokumentasi Frontend Admin & Teacher**: [`docs/03-Frontend-Admin-Teacher-Documentation.docx`](docs/03-Frontend-Admin-Teacher-Documentation.docx) ([`docs/documentation-frontend.docx`](docs/documentation-frontend.docx))
* **Dokumentasi Portal Siswa PHP**: [`docs/04-Student-Portal-PHP-Documentation.docx`](docs/04-Student-Portal-PHP-Documentation.docx) ([`docs/documentation-native-student.docx`](docs/documentation-native-student.docx))
* **Skema Basis Data & ERD**: [`docs/05-Database-Schema-ERD-and-Migrations.docx`](docs/05-Database-Schema-ERD-and-Migrations.docx) ([`docs/sdd-erd-architecture.docx`](docs/sdd-erd-architecture.docx)) | Diagram Draw.io: [`docs/uml/architecture/02-erd-dual-database.drawio`](docs/uml/architecture/02-erd-dual-database.drawio)
* **Matriks Fitur per Role & Audit QA**: [`docs/06-Role-Feature-Matrix-and-QA-Audit.docx`](docs/06-Role-Feature-Matrix-and-QA-Audit.docx) ([`docs/daftar-fitur-per-role.docx`](docs/daftar-fitur-per-role.docx))
* **Spesifikasi UML & Flowchart**: [`docs/07-UML-Architecture-and-Flow-Specification.docx`](docs/07-UML-Architecture-and-Flow-Specification.docx) | Berkas Draw.io di [`docs/uml/`](docs/uml/) dan [`docs/Flowchart/`](docs/Flowchart/)
