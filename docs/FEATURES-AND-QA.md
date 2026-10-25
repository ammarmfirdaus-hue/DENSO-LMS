# Katalog Fitur dan Hasil Audit DENSO LMS

Dokumen ini merangkum fitur yang ditemukan pada Admin/Teacher, backend API, dan Native Student. Setiap klaim dicocokkan dengan source; matriks detail dan gap ada di [IMPLEMENTATION-AUDIT.md](IMPLEMENTATION-AUDIT.md). Diagram `.drawio` editable ada di [`diagrams/`](diagrams/).

## Daftar fitur

### 1. Login, sesi, dan SSO

Login memeriksa kredensial dan status akun, memakai limiter percobaan, lalu backend membuat cookie SSO HttpOnly berdurasi 8 jam. Frontend staff mengirim cookie itu pada request API; Native memvalidasi cookie dan membuka sesi PHP. Teacher dapat menukar sesi aktif ke mode belajar Student selama 30 menit; backend memastikan role/status Teacher, dan token mode ini kembali ditukar ke role Teacher setelah keluar. Token reviewer punya alur konsumsi terpisah. Request mutasi Student memakai CSRF. PWA tidak mengubah otorisasi atau menyimpan data portal saat offline.

Flow: [`01-login-sso.drawio`](diagrams/01-login-sso.drawio)

### 2. Manajemen pengguna

Admin mengelola akun semua role dan dapat mempromosikan akun Student menjadi Teacher dari Edit Pengguna. Promosi mempertahankan `student_id` dan riwayat enrollment; akun MySQL ditandai `teacher_shadow` sehingga tidak bisa masuk menggunakan login Student biasa. Teacher dibatasi ke pengelolaan Student. CSV import/export, aktivasi, hapus, dan email mempunyai aturan per role. Email kredensial adalah reset: Admin memasukkan sandi baru, worker mengganti hash bcrypt, lalu mengirim NPK dan sandi tersebut ke alamat akun. Sandi lama tidak dapat dibaca kembali dari hash. Bulk delete/nonaktif/email berjalan sebagai job persisten yang dapat dihentikan dan dilanjutkan; delete yang terhalang riwayat dapat fallback ke nonaktif.

Flow: [`02-user-management.drawio`](diagrams/02-user-management.drawio)

### 3. Manajemen reviewer

Admin membuat/mengedit profil reviewer, mengaktifkan kembali/nonaktifkan, mengirim kredensial via antrean job, dan menghapus bila aman. Hapus memakai pemeriksaan pekerjaan aktif/relasi; riwayat dapat menyebabkan fallback nonaktif. Email mewajibkan sandi baru. Reviewer menerima tautan satu kali terikat submission; token yang salah, kedaluwarsa, atau scope-nya keliru ditolak.

Flow: [`03-reviewer-management.drawio`](diagrams/03-reviewer-management.drawio)

### 4. Dashboard dan aktivitas

Dashboard mengambil ringkasan course, progres belajar, dan aktivitas dari API. Nilai ringkasan adalah data baca; akses tetap mengikuti sesi dan role yang valid.

Flow: [`04-dashboard-activity.drawio`](diagrams/04-dashboard-activity.drawio)

### 5. Course, sesi, dan materi course

Admin/teacher melihat course yang dimiliki/ditugaskan dan membuat course serta sesi; hanya Admin menetapkan trainer. API belum menyediakan edit/hapus umum course atau sesi. Materi harus melalui workflow persetujuan; katalog/materi terbit dikirim ke Student lewat konfigurasi sync.

Flow: [`05-course-session.drawio`](diagrams/05-course-session.drawio)

### 6. Antrean persetujuan materi

Teacher/admin mengirim materi ke antrean. Admin memeriksa kiriman, dapat meminta perbaikan, atau meneruskannya untuk review. Reviewer memberi keputusan; admin menerbitkan materi yang disetujui. Perubahan status dibatasi per role dan kepemilikan. Teacher hanya dapat mengubah materi miliknya pada status yang diizinkan; status workflow yang tidak dikenal sekarang ditolak secara default.

Flow: [`06-material-approval.drawio`](diagrams/06-material-approval.drawio)

### 7. Portal review eksternal

Reviewer membuka tautan terbatas, mengonsumsi token, memeriksa submission dan file, lalu mengirim keputusan beserta catatan. Tautan tidak memberikan akses ke submission lain. Keputusan kembali ke antrean Admin/Teacher.

Flow: [`07-reviewer-portal.drawio`](diagrams/07-reviewer-portal.drawio)

### 8. Belajar Student dan assessment

Admin/Teacher mengelola satu quiz pilihan ganda per course pada `/quizzes`; course dibatasi sesuai kepemilikan atau penugasan. Quiz dapat disimpan sebagai draft atau diterbitkan. Student menandai setiap materi selesai; quiz hanya terbuka setelah semua materi course aktif selesai. Form menampilkan opsi jawaban tanpa kunci. Submit memeriksa kelengkapan di server dan constraint unik MySQL mencegah attempt kedua; nilai dihitung otomatis dan tetap tersedia setelah refresh. Hasil ada pada `/student/nilai` dan kolom Nilai Quiz di `/grades`. Quiz yang sudah memiliki attempt dinonaktifkan saat dihapus agar nilai/riwayat tetap utuh. Alur pre-test dan post-test sesi sudah dihapus dari katalog course, pembelajaran, riwayat, dan rute quiz.

Flow: [`08-student-learning.drawio`](diagrams/08-student-learning.drawio)

### 9. Forum diskusi

Admin/teacher mengatur sesi forum agar terbuka atau tertutup. Student/staff membaca thread, mengirim post/balasan, memberi like, dan menyertakan gambar. Gambar statis dikompres di browser; GIF/WebP animasi dipertahankan. Backend memeriksa signature, tipe, ukuran, autentikasi, enrollment, dan rate limit. SSE memberi update ke klien pada proses backend yang sama; multi-instance belum memakai event broker bersama.

Flow: [`09-forum-discussion.drawio`](diagrams/09-forum-discussion.drawio)

### 10. Pusat notifikasi dan email

Admin dapat mengirim notifikasi ke audiens yang diizinkan; Teacher hanya Student. Inbox menyimpan unread/read per recipient dan SSE memperbarui klien. Email opsional masuk outbox backend dengan validasi, deduplikasi, batch, serta retry terbatas. Email SMTP eksternal tidak dibuktikan oleh test lokal.

Flow: [`10-notifications-email.drawio`](diagrams/10-notifications-email.drawio)

### 11. Nilai, progres, dan riwayat

Portal Admin/Teacher menampilkan progres/enrollment dan nilai per course. Admin/Teacher dapat mengedit score course 0–100/feedback; hanya Admin mengosongkan nilai course (progres tetap ada). Nilai quiz pilihan ganda dihitung dari jawaban dan ditampilkan terpisah dari nilai course. Filter aktif diekspor sebagai workbook `.xlsx`. Notifikasi per nilai dapat menuju inbox Student dan email outbox. Nilai course disinkronkan ke MySQL; nilai quiz berasal dari attempt MySQL dan disinkronkan ke snapshot SQL Server. Halaman nilai Student membaca data miliknya.

Flow: [`11-grades-progress-history.drawio`](diagrams/11-grades-progress-history.drawio)

### 12. Profil dan bantuan Student

Student dapat melihat ringkasan profil, detail karyawan, riwayat akses, pengaturan, dan bantuan. Beberapa data contoh; belum ada penyimpanan perubahan profil production.

Flow: [`12-student-profile-support.drawio`](diagrams/12-student-profile-support.drawio)

### 13. Operasi akun dalam antrean

Impor, perubahan banyak akun, penghapusan/nonaktifkan, dan pengiriman email kredensial dapat dikirim sebagai job. Backend memvalidasi role dan cakupan akun, menyimpan status dan event item di SQL Server, lalu memproses item satu per satu. Pusat Antrean menampilkan snapshot awal dari API, menerima progres/item melalui SSE, dan memuat ulang daftar/event setelah halaman dibuka kembali. Job dapat dihentikan; item yang belum diproses tetap tertunda dan tersedia untuk dilanjutkan. Teacher dibatasi ke Student; email kredensial dan akun lintas role hanya dikelola Admin.

Flow: [`13-account-jobs-progress.drawio`](diagrams/13-account-jobs-progress.drawio)

### 14. Install dan mode offline PWA

Manifest dan ikon membuat kedua portal dapat dipasang dari browser yang mendukungnya. Chrome/Edge memakai prompt native yang dibuka melalui modal portal; iPhone/iPad mendapat petunjuk **Bagikan → Tambahkan ke Layar Utama**. Service worker mengambil halaman dari jaringan dan menggunakan offline page jika navigasi gagal; aset statis memakai cache lokal. API, upload, HTML terautentikasi, email, SSE, serta mutasi job tidak disimpan atau dijalankan oleh service worker. Operasi antrean di backend tetap berjalan tanpa tab browser, tetapi progress UI perlu online untuk menerima SSE atau memuat ulang status.

Flow: [`14-pwa-install-offline.drawio`](diagrams/14-pwa-install-offline.drawio) · Arsitektur: [`15-pwa-system-architecture.drawio`](diagrams/15-pwa-system-architecture.drawio)

### 15. Pola interaksi lintas halaman

Konfirmasi, error, dan notifikasi aksi memakai SweetAlert2. Dialog antrean mendukung minimize/hide, posisi yang dapat dipindah, dan layout responsif; saat halaman dimuat ulang, daftar/status/event job direkonstruksi dari backend. Portal staff menutup sidebar mobile melalui tombol/overlay dan membiarkan area konten memenuhi viewport. API client mengulang request baca yang aman ketika koneksi/gateway gagal sementara; request tulis tidak diulang otomatis untuk menghindari operasi ganda.

### 16. Persetujuan akses course dan mode belajar Teacher

Course aktif tampil di katalog, tetapi detail materi, file, quiz, forum, dan perubahan progres hanya tersedia jika `course_enrollments` sudah berstatus active/completed. Student dapat meminta akses; Teacher dapat merekomendasikan Student hanya untuk course yang dibuat atau ditugaskan kepadanya. Keduanya masuk ke `course_enrollment_requests`, sedangkan keputusan approve/reject dan alasan hanya dapat dikirim role Admin. Approve membuat atau mengaktifkan enrollment; reject menyimpan alasannya. Satu indeks unik mencegah lebih dari satu permintaan pending untuk kombinasi student-course. Keputusan Admin mengirim notifikasi Student terarah melalui SSE. Student dapat mengajukan kembali setelah penolakan.

Teacher masuk ke mode belajar lewat pertukaran SSO yang memeriksa ulang akun SQL Server aktif dan role teacher. Backend membuat atau memakai ulang identitas `teacher_shadow` dengan NPK yang sama, tetapi tidak mengubah role di `employee_accounts`; login password Student menolak jenis akun shadow. Backend memeriksa status dan tipe akun terhadap MySQL pada setiap request Student, dan endpoint forum/file memeriksa tipe akun serta enrollment lagi. Mode belajar memakai gate enrollment yang sama, memiliki cookie SSO maksimum 30 menit, dan tombol kembali memeriksa ulang status Teacher. Promosi Student ke Teacher oleh Admin mengubah akun MySQL lama menjadi shadow sambil mempertahankan kunci riwayat course, kemudian membuat akun pegawai Teacher di SQL Server; token Student lama ditolak oleh Native dan API setelah perubahan akun.

Flow: [`16-course-access-approval.drawio`](diagrams/16-course-access-approval.drawio)

Catatan data: SQL Server dan MySQL tidak mendukung transaksi atomik lintas database pada jalur promosi. Handler memakai kompensasi untuk menghapus insert SQL Server bila konversi akun MySQL gagal; integrasi ini tetap perlu pemantauan operasional. Penonaktifan Teacher saat sedang berada di mode belajar menghentikan pertukaran sesi berikutnya; token mode yang sudah terbit berakhir paling lambat 30 menit.

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

Run default pada 25 Oktober 2026:

- Backend `npm test`: 72 lulus, 4 smoke dilewati karena membutuhkan service lokal; 0 gagal.
- Frontend `npm test`: 9 lulus, 1 browser smoke dilewati karena perlu konfigurasi Edge; 0 gagal.
- Student `php tests/run.php all`: 14 pemeriksaan lulus; PHP menampilkan warning akses session path Laragon, tetapi exit code sukses.
- Frontend PWA production build berhasil; manifest, diagram XML dan tautan lokal tervalidasi.

## Catatan cakupan

Smoke browser/API eksternal tidak dijalankan pada run default ini; SMTP nyata, deployment domain HTTPS, beban serentak, dan mutasi UI terhadap database aktif belum diuji sebagai end-to-end produksi.

Migrasi SQL Server untuk workflow, forum, dan notifikasi harus diterapkan di lingkungan baru sebelum route terkait dipakai. File migrasi ada di `database/`; panduan setup Backend mencantumkan urutannya.
