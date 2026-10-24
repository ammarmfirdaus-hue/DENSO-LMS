# Katalog Fitur dan Hasil Audit DENSO LMS

Dokumen ini merangkum fitur yang ditemukan pada Admin/Teacher, backend API, dan Native Student, serta hasil pengujian lokal. Satu file `.drawio` disediakan untuk setiap alur utama di [`docs/diagrams`](diagrams/).

## Daftar fitur

### 1. Login, sesi, dan SSO

Admin dan teacher masuk melalui portal web. API memeriksa kredensial, menerapkan pembatasan percobaan login, lalu menerbitkan token HS256 dengan issuer, audience, dan waktu kedaluwarsa. Sesi browser menggunakan cookie HttpOnly. Student yang masuk lewat SSO diarahkan ke aplikasi PHP; token diverifikasi sebelum sesi PHP dibuat. Permintaan perubahan pada aplikasi Student dilindungi token CSRF. Logout menghapus sesi/token aktif.

Flow: [`01-login-sso.drawio`](diagrams/01-login-sso.drawio)

### 2. Manajemen pengguna

Admin dapat melihat, menambah, dan mengelola akun staff. Password disimpan dalam bentuk hash bcrypt. Halaman dan endpoint pengguna hanya boleh diakses admin; teacher diarahkan ke dashboard dan API mengembalikan 403.

Flow: [`02-user-management.drawio`](diagrams/02-user-management.drawio)

### 3. Manajemen reviewer

Admin mengelola akun reviewer dan statusnya. Reviewer menerima tautan review yang terikat ke submission. Token harus dikonsumsi melalui API sebelum akses reviewer diberikan; token yang tidak valid atau scope-nya keliru ditolak.

Flow: [`03-reviewer-management.drawio`](diagrams/03-reviewer-management.drawio)

### 4. Dashboard dan aktivitas

Dashboard mengambil ringkasan course, progres belajar, dan aktivitas dari API. Nilai ringkasan adalah data baca; akses tetap mengikuti sesi dan role yang valid.

Flow: [`04-dashboard-activity.drawio`](diagrams/04-dashboard-activity.drawio)

### 5. Course, sesi, dan materi course

Admin/teacher melihat course, detail, sesi, dan materi. Admin dapat menetapkan trainer. Pengguna yang berwenang membuat course/sesi dan menambahkan materi pada sesi. Data course yang dipublikasikan dapat disinkronkan ke Student sesuai konfigurasi sinkronisasi; sinkronisasi berjalan terjadwal dan bukan transaksi real-time.

Flow: [`05-course-session.drawio`](diagrams/05-course-session.drawio)

### 6. Antrean persetujuan materi

Teacher/admin mengirim materi ke antrean. Admin memeriksa kiriman, dapat meminta perbaikan, atau meneruskannya untuk review. Reviewer memberi keputusan; admin menerbitkan materi yang disetujui. Perubahan status dibatasi per role dan kepemilikan. Teacher hanya dapat mengubah materi miliknya pada status yang diizinkan; status workflow yang tidak dikenal sekarang ditolak secara default.

Flow: [`06-material-approval.drawio`](diagrams/06-material-approval.drawio)

### 7. Portal review eksternal

Reviewer membuka tautan terbatas, mengonsumsi token, memeriksa submission dan file, lalu mengirim keputusan beserta catatan. Tautan tidak memberikan akses ke submission lain. Keputusan kembali ke antrean Admin/Teacher.

Flow: [`07-reviewer-portal.drawio`](diagrams/07-reviewer-portal.drawio)

### 8. Belajar Student dan assessment

Student mencari course, memakai filter, membuka detail course, membaca materi, menyimpan progres, dan mengerjakan kuis bila tersedia. Riwayat belajar menampilkan hasil yang tersimpan. Halaman kontekstual seperti detail, thread, lesson, dan quiz memerlukan ID yang valid; permintaan tanpa konteks diarahkan kembali ke halaman yang aman.

Flow: [`08-student-learning.drawio`](diagrams/08-student-learning.drawio)

### 9. Forum diskusi

Admin/teacher mengatur sesi forum agar terbuka atau tertutup. Peserta membaca thread, mengirim post dan balasan, memberi like, serta menyertakan gambar. Gambar statis dikompres di browser menjadi WebP maksimal 1920 piksel dengan kualitas bertahap; GIF dan WebP animasi dipertahankan. Backend memeriksa signature berkas, tipe, ukuran, autentikasi, dan rate limit. Perubahan dikirim ke klien yang tersambung melalui SSE.

Flow: [`09-forum-discussion.drawio`](diagrams/09-forum-discussion.drawio)

### 10. Pusat notifikasi dan email

Admin dapat mengirim notifikasi ke Student maupun staff; teacher dibatasi mengirim kepada Student. Pengirim memilih kategori/prioritas, audiens, dan apakah email juga dikirim. Notifikasi web disimpan dan dihitung sebagai unread; SSE memperbarui badge. Pilihan email dimasukkan ke outbox dan alamat dinormalisasi, dideduplikasi, serta dibatasi sebelum pengiriman. Pengguna dapat menandai satu atau semua notifikasi sebagai dibaca.

Flow: [`10-notifications-email.drawio`](diagrams/10-notifications-email.drawio)

### 11. Nilai, progres, dan riwayat

Portal Admin/Teacher menampilkan data nilai, ringkasan progres, dan aktivitas yang tersedia di API. Student melihat progres course dan riwayat belajar/kuis. Sinkronisasi snapshot antar database mengikuti job terjadwal yang diaktifkan melalui konfigurasi; sinkronisasi bukan jalur real-time.

Flow: [`11-grades-progress-history.drawio`](diagrams/11-grades-progress-history.drawio)

### 12. Profil dan bantuan Student

Student dapat melihat ringkasan profil, detail karyawan, riwayat akses, pengaturan, dan bantuan. Halaman yang saat ini terdaftar adalah halaman baca/tampilan; dokumentasi ini tidak menyebut perubahan profil Student sebagai fitur simpan karena tidak ada aksi simpan profil Student yang ditemukan pada route catalog.

Flow: [`12-student-profile-support.drawio`](diagrams/12-student-profile-support.drawio)

## Struktur suite test

- `Backend-LMS-Admin-Teacher/tests/security/`: autentikasi/token, RBAC, error aman, pagination, upload, serta aturan workflow.
- `Backend-LMS-Admin-Teacher/tests/features/`: validasi workflow, payload notifikasi, email recipient, event SSE, signature gambar, dan smoke API lokal.
- `Frontend-LMS-Admin-Teacher/tests/security/`: akses halaman berdasarkan role.
- `Frontend-LMS-Admin-Teacher/tests/features/`: kompresi gambar forum dan smoke halaman browser.
- `NATIVE-LMS-Student/tests/security/`: CSRF/session, output escaping, pagination, router, dan SSO.
- `NATIVE-LMS-Student/tests/features/`: konsistensi katalog route Student dengan controller.

Perintah yang tersedia:

```powershell
# Backend API
cd Backend-LMS-Admin-Teacher
npm test
npm run test:security
npm run test:features
$env:DLS_RUN_LOCAL_SMOKE='1'; npm run test:features

# Frontend Admin/Teacher
cd ../Frontend-LMS-Admin-Teacher
npm test
npm run test:security
npm run test:features
$env:DLS_RUN_BROWSER_SMOKE='1'
$env:DLS_BROWSER_EXECUTABLE='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
npm run test:browser
npm run build

# Native Student
cd ../NATIVE-LMS-Student
php tests/run.php all
```

## Hasil pengujian lokal

Pengujian dijalankan pada 24 Oktober 2026 terhadap layanan development lokal.

- Backend: 60 test lulus; 4 test smoke dilewati pada run default. Run smoke lokal tambahan lulus 53 test, termasuk API read, batas role, guard request mutasi, SSE, SSO PHP, dan 16 route Student tanpa 5xx. Smoke juga membuka form diskusi dari course dan sesi nyata, menguji status sesi, dan memastikan endpoint create menolak payload kosong sebelum menulis data.
- Frontend: suite keamanan lulus 1 test dan suite fitur gambar lulus 4 test. Browser smoke Edge mencakup 10 halaman Admin/Teacher pada desktop dan viewport 390 px, serta 16 route Student melalui SSO pada viewport 390 px. Browser juga memeriksa form diskusi Student dengan course/sesi nyata dan memastikan tidak muncul exception JavaScript. Render halaman Admin/Teacher teramati sekitar 480–800 ms, dengan satu halaman detail mencapai sekitar 2,8 detik pada run terakhir.
- Student PHP: 13 pemeriksaan lulus, terdiri dari 12 security dan 1 route-feature check.
- Build frontend: berhasil. Pemecahan bundle per halaman menurunkan chunk awal dari sekitar 563 KB (153 KB gzip) menjadi 397 KB (118 KB gzip); halaman fitur dimuat sebagai chunk terpisah.
- Live API lokal merespons halaman/route baca dalam ambang smoke 2 detik; pengukuran yang teramati umumnya beberapa sampai puluhan milidetik. Startup dingin service dapat lebih lambat.

## Catatan cakupan

Smoke test menggunakan layanan dan database development lokal, JWT pengujian yang ditandatangani dengan konfigurasi lokal, request baca, serta koneksi SSE singkat. Suite tidak mengirim email eksternal dan tidak menjalankan create/update/delete ke data aplikasi aktif. Aturan validasi/otorisasi dan transisi workflow diuji lewat test fitur terisolasi. Pengiriman SMTP nyata, beban serentak, dan seluruh mutasi UI terhadap database belum diukur sebagai load test atau end-to-end produksi; lakukan pengujian tersebut pada staging dengan fixture khusus sebelum rilis.

Migrasi SQL Server untuk workflow, forum, dan notifikasi harus diterapkan di lingkungan baru sebelum route terkait dipakai. File migrasi ada di `database/`; panduan setup Backend mencantumkan urutannya.
