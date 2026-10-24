# DENSO Learning Management System

Workspace ini berisi tiga runtime terpisah. Jalankan setiap proyek dari foldernya masing-masing atau gunakan launcher di bawah.

| Proyek | Teknologi | URL / port lokal | Database |
|---|---|---|---|
| `Backend-LMS-Admin-Teacher` | Node.js, Express, JWT | `http://127.0.0.1:3001` | Microsoft SQL Server (`DX_LMS_AdminTeacher`) |
| `Frontend-LMS-Admin-Teacher` | React, Vite, Tailwind CSS | `http://127.0.0.1:5173` | Mengakses API Node.js |
| `NATIVE-LMS-Student` | PHP Native, Tailwind CSS, GET JSON API | `http://127.0.0.1:8082` | MySQL (`dls_student`) |

## Menjalankan proyek

### Memasang environment di Windows

Jalankan [`install-runtime-all.bat`](install-runtime-all.bat) dari folder utama untuk memasang dependency ketiga proyek, atau jalankan installer di folder proyek yang ingin disiapkan saja:

- `NATIVE-LMS-Student\install.bat`: memeriksa PHP 8.1+ dan `pdo_mysql`, memasang dependency Tailwind, lalu membangun CSS.
- `Backend-LMS-Admin-Teacher\install.bat`: memeriksa Node.js 20+, memasang dependency API, dan membuat `.env` dengan JWT secret acak jika `.env` belum ada.
- `Frontend-LMS-Admin-Teacher\install.bat`: memeriksa Node.js 20+, memasang dependency React/Vite, dan membuat `.env` jika belum ada.

Installer mempertahankan `.env` yang sudah ada. Setelah selesai, sesuaikan kredensial database dan alamat API di setiap `.env`, lalu pasang skema database di bawah ini. Proyek Native PHP saat ini tidak memiliki `composer.json`, jadi tidak memerlukan Composer.

### Setup Database Mysql&MSSQL

Database Student dan Admin/Teacher terpisah. Jalankan masing-masing file SQL berikut satu kali menggunakan akun database yang memiliki izin membuat database dan tabel:

| Database | File SQL | Database yang dibuat |
|---|---|---|
| MySQL Student | [`database/student.sql`](database/student.sql) | `dls_student` |
| SQL Server Admin/Teacher | [`database/admin&teacher.sql`](<database/admin&teacher.sql>) | `DX_LMS_AdminTeacher` |

#### 1. Pasang skema MySQL Student

**Melalui phpMyAdmin:** mulai layanan MySQL, buka phpMyAdmin, pilih **Import**, pilih `database/student.sql`, lalu klik **Import/Go**. File tersebut membuat database `dls_student` sekaligus seluruh tabel Student.

**Melalui MySQL Workbench:** sambungkan ke server MySQL, buka file `database/student.sql` melalui **File → Open SQL Script**, lalu jalankan seluruh script dengan tombol petir.

Setelah skema dibuat, isi koneksi pada `NATIVE-LMS-Student/.env`. Contoh untuk MySQL lokal dengan akun `root` tanpa password:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=dls_student
DB_USER=root
DB_PASSWORD=
```

Jika akun MySQL Anda memiliki password atau memakai username lain, gunakan nilai akun tersebut. Untuk deployment, buat akun aplikasi khusus dengan hak minimum; jangan gunakan `root`.

#### 2. Pasang skema SQL Server Admin/Teacher

**Melalui SQL Server Management Studio (SSMS):** mulai layanan SQL Server, sambungkan ke instance yang dipakai aplikasi, buka `database/admin&teacher.sql` melalui **File → Open → File**, lalu jalankan seluruh script dengan **Execute**. Script membuat database `DX_LMS_AdminTeacher` dan tabel-tabelnya. `GO` di file tersebut adalah pemisah batch SQL Server; jalankan script di SSMS, bukan di PowerShell.

Buat login SQL Server khusus untuk API. Jalankan script berikut di jendela **New Query** SSMS menggunakan akun admin Windows/SQL Server. Ganti placeholder password sebelum menjalankan. Jika login sudah ada, baris `CREATE LOGIN` akan dilewati; pastikan password di `.env` tetap cocok dengan password login itu.

```sql
USE master;
GO
IF SUSER_ID(N'dls_lms_app') IS NULL
    CREATE LOGIN dls_lms_app WITH PASSWORD = N'<PASSWORD_KUAT_PILIHAN_ANDA>', CHECK_POLICY = ON;
GO

USE DX_LMS_AdminTeacher;
GO
IF DATABASE_PRINCIPAL_ID(N'dls_lms_app') IS NULL
    CREATE USER dls_lms_app FOR LOGIN dls_lms_app;
GO
IF DATABASE_PRINCIPAL_ID(N'dls_app_role') IS NULL
    CREATE ROLE dls_app_role;
GO
GRANT SELECT, INSERT, UPDATE ON SCHEMA::dbo TO dls_app_role;
GO
IF NOT EXISTS (
    SELECT 1
    FROM sys.database_role_members
    WHERE role_principal_id = DATABASE_PRINCIPAL_ID(N'dls_app_role')
      AND member_principal_id = DATABASE_PRINCIPAL_ID(N'dls_lms_app')
)
    ALTER ROLE dls_app_role ADD MEMBER dls_lms_app;
GO
```

Isi `Backend-LMS-Admin-Teacher/.env` dengan host/instance, port, dan kredensial SQL Server yang benar. Contoh untuk SQL Server lokal pada port `1433`:

```dotenv
DB_SERVER=localhost
DB_PORT=1433
DB_NAME=DX_LMS_AdminTeacher
DB_USER=dls_lms_app
DB_PASSWORD=<PASSWORD_YANG_SAMA_DENGAN_LOGIN_SQL_SERVER>
```

Nilai `DB_SERVER` dan `DB_PORT` harus menunjuk ke instance SQL Server tempat script skema dan login tadi dibuat. Password database ini berbeda dari password demo yang diminta seeder.

#### 3. Periksa hasil pemasangan

Di SSMS, pilih `DX_LMS_AdminTeacher` lalu jalankan:

```sql
SELECT name FROM sys.tables ORDER BY name;
```

Di phpMyAdmin, pilih `dls_student`; tabel-tabel Student akan terlihat di panel navigasi. Untuk memeriksa dari tab SQL:

```sql
USE dls_student;
SHOW TABLES;
```

Sesudah kedua database, skema, dan `.env` siap, jalankan `Start-All-DataDummy.bat` untuk mengisi data demo sekaligus memulai runtime. Gunakan hanya pada database pengembangan.


### Menjalankan ketiga runtime (Windows)

Untuk mengisi kedua database dengan data dummy lalu menjalankan server, gunakan [`Start-All-DataDummy.bat`](Start-All-DataDummy.bat). Launcher meminta satu password demo untuk akun dummy Admin/Teacher dan Student.

## Struktur proyek

- **Backend Admin/Teacher** menggunakan pemisahan `src/config`, `src/middleware`, `src/routes`, dan `src/services`; tiap domain API dikelompokkan menurut tanggung jawabnya.
- **Frontend Admin/Teacher** memakai `src/features/<fitur>` untuk auth, dashboard, courses, materials, activity, grades, forum, users, dan reviewer. API, layout, komponen umum, dan style disimpan terpisah.
- **Native Student** mengelompokkan controller dan view ke `Dashboard`, `Courses`, `Learning`, `Forum`, `History`, `Notifications`, dan `Profile`. URL halaman tetap sama.

Tema lintas aplikasi menggunakan merah DENSO untuk aksi utama, charcoal untuk navigasi dan teks, serta latar netral untuk konten.

Dokumentasi fitur, aktor, arsitektur data, keamanan, struktur folder, dan hasil tes tersedia di [Panduan Fitur dan Teknis](docs/PROJECT-GUIDE.md). Diagram use case dapat diedit di [Draw.io](docs/diagrams/DENSO-LMS-Use-Case.drawio).

## Pemisahan data

Database Teacher/Admin dan Student memang dipisahkan. Sinkronisasi opsional di backend dapat mengirim metadata course dari SQL Server ke MySQL dan mengembalikan ringkasan enrollment/progress ke SQL Server. Fitur ini nonaktif secara default dan memerlukan akun MySQL khusus dengan hak akses minimum. Sinkronisasi berjalan berkala, bukan real-time. Post forum dan nilai kuis belum disinkronkan.

## SSO Admin/Teacher dan Student

Login Admin/Teacher dan Native Student memakai backend Admin/Teacher sebagai penyedia sesi bersama. Backend memeriksa akun karyawan di SQL Server dan, jika diaktifkan, akun Student di MySQL. Pastikan NPK atau email dan kata sandi yang sama dipakai saat masuk dari kedua aplikasi.

Untuk development, aktifkan `STUDENT_AUTH_ENABLED=true` dan isi koneksi `STUDENT_DB_*` pada `Backend-LMS-Admin-Teacher/.env`. Salin nilai `JWT_SECRET` backend yang sama ke `SSO_JWT_SECRET` pada `NATIVE-LMS-Student/.env`; jangan masukkan rahasia ini ke file frontend atau commit ke Git. URL native lokal disetel melalui `STUDENT_APP_URL` dan `VITE_STUDENT_APP_URL`.

Backend menerbitkan cookie SSO `HttpOnly`, `SameSite=Lax`, dan berlaku 8 jam. Semua app production harus memakai HTTPS di bawah hostname/domain induk yang sama; setel `SSO_COOKIE_DOMAIN` dan samakan nilainya di konfigurasi backend dan native (contoh `.example.com`). Untuk production gunakan password DB khusus berhak minimum, sertifikat TLS SQL Server yang valid, dan URL HTTPS pada `APP_URL`, `SSO_API_URL`, `SSO_FRONTEND_URL`, `FRONTEND_ORIGIN`, serta `STUDENT_APP_URL`. Tombol keluar menghapus cookie bersama; halaman Native memeriksa sesi SSO itu pada setiap request terlindungi.

## Data database dummy

Setiap aplikasi memiliki seeder data dummy yang aman dijalankan berulang kali. Lihat [README Backend](Backend-LMS-Admin-Teacher/README.md) dan [README Student](NATIVE-LMS-Student/README.md) untuk perintah dan NPK akun demo. Launcher data dummy meminta password khusus demo; password tersebut diatur pada akun berawalan `DLS-DEMO-` setiap kali seeder dijalankan. Seeder tidak menghapus data dan tidak mengubah akun di luar akun demo. Jalankan hanya pada database pengembangan. Seed Admin/Teacher dan Student berjalan ke database terpisah tanpa memerlukan sinkronisasi Student.

## Catatan migrasi

Workspace React Teacher/Admin mencakup login, ringkasan dashboard, pengelolaan course/sesi/materi, aktivitas, progress pelajar tersinkron, moderasi forum, dan pengelolaan akun Admin. Workspace Student mempertahankan halaman dan styling yang ada; ringkasan dashboard/course memakai MySQL API, sementara sebagian halaman pembelajaran, kuis, notifikasi, dan forum masih berisi data contoh. README setiap proyek menjelaskan endpoint yang tersedia dan batas implementasinya.
