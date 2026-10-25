# Audit Cakupan Implementasi DENSO LMS

**Tanggal:** 25 Oktober 2026  
**Dasar audit:** route/controller, halaman React/PHP, service backend, migration, serta test suite yang tersedia. Dokumen ini memisahkan fitur yang terhubung ke penyimpanan/API dari layar demo dan mencatat gap agar dokumentasi tidak menjanjikan perilaku yang belum ada.

## Ringkasan runtime dan data

| Bagian | Implementasi | Persistensi utama |
|---|---|---|
| Admin/Teacher API | Node.js + Express; autentikasi, RBAC, validasi, rate limit, SSE, worker email dan job | SQL Server `DX_LMS_AdminTeacher` |
| Admin/Teacher portal | React + React Router + Vite; dashboard, course, materi, nilai, forum, notifikasi, reviewer, pengguna | Memanggil backend; tidak mengakses DB langsung |
| Student portal | PHP Native; halaman sesi terlindungi, katalog, pembelajaran, nilai, forum, notifikasi | MySQL `dls_student`; forum/notifikasi melalui backend API |
| SSO | Login backend memeriksa akun SQL Server dan Student MySQL bertipe `student`; setiap request token Student memeriksa lagi status/tipe akun aktif. Teacher mode memakai shadow identity dan token maksimum 30 menit; promosi menolak token Student lama | Pemeriksaan Student menambah satu query terindeks MySQL per request; secret di environment server |
| PWA | Manifest, ikon, prompt instalasi, service worker, offline fallback pada kedua portal | Cache hanya aset publik/offline page |

Frontend Admin/Teacher menggunakan cookie SSO untuk panggilan API. Token reviewer disimpan di `sessionStorage` selama sesi review tab. Middleware backend menerima bearer token atau cookie SSO. Logout membersihkan cookie untuk browser yang mengirim permintaan logout.

## Cakupan fitur yang dicocokkan dengan source

| Fitur | Route/UI utama | Data dan perilaku terverifikasi | Status/batas yang perlu diketahui |
|---|---|---|---|
| Dashboard dan aktivitas | React `/`, `/activity`; `/api/v1/dashboard/*` | Ringkasan dan aktivitas dibaca dari backend sesuai role/cakupan course | Beberapa grafik/angka visual merupakan placeholder; `/health` hanya memeriksa proses API |
| Course dan sesi | React `/courses`, `/courses/:id`; `/api/v1/courses` | Admin/Teacher melihat course sesuai kepemilikan/penugasan; keduanya dapat membuat course/sesi; Admin dapat menetapkan trainer | API tidak menyediakan update/delete umum course/sesi |
| Materi dan review | React `/materials`, detail course, `/review/:token`; `/api/v1/material-workflow/*` | Submission teks/link/file, validasi ukuran/format, status review, undangan sekali pakai, keputusan reviewer, publish Admin, akses file terbit | Materi terbit hanya tampil setelah publish/sinkron; penyimpanan upload lokal perlu backup; cek signature bukan antivirus |
| Pengguna | React `/users`; `/api/v1/users`, `/api/v1/jobs/users` | Admin semua role; Teacher hanya Student. Admin dapat mempromosikan Student ke Teacher dengan mempertahankan `student_id` dan riwayat; shadow MySQL dikeluarkan dari login Student biasa. CSV import/export, status, bulk delete/deactivate, email kredensial | Konversi role menyentuh dua database yang tidak berbagi transaksi; handler mencoba kompensasi SQL Server bila konversi MySQL gagal. Akun dengan relasi tidak dihapus permanen; akun sendiri dan Admin aktif terakhir dilindungi |
| Persetujuan course | React `/enrollments`; `/api/v1/enrollments/*`; Native `/student/course/detail` | Student request dan Teacher recommendation disimpan pada MySQL; rekomendasi dibatasi ke course yang dibuat/ditugaskan. Admin saja yang approve/reject; approve membuka enrollment, reject menyimpan alasan, notifikasi SSE terarah dikirim ke Student | Constraint unik menjaga satu permintaan pending per student-course. Katalog hanya membagikan metadata sebelum approve; semua materi, file, quiz, forum, dan progres memeriksa enrollment di server |
| Email data akun | Modal user/reviewer; job type `users` action `email` | Admin memasukkan kata sandi baru; worker mengganti password hash bcrypt dan SMTP mengirim NPK serta plaintext kata sandi yang sama | Hash lama tidak dapat dibalik untuk mengirim sandi lama. Job ditolak jika sandi baru tidak ada/minimum 12 karakter, akun tidak aktif, atau SMTP gagal |
| Reviewer | React `/reviewers`; `/api/v1/reviewers` dan job pengguna | Admin mengelola profil, aktif/nonaktif, mengaktifkan kembali, mengirim kredensial melalui job, dan menghapus melalui job | Reviewer dengan pekerjaan review aktif tidak dapat dihapus/nonaktif. Penghapusan yang terhalang riwayat berubah menjadi nonaktif; endpoint reviewer DELETE sendiri adalah deactivate |
| Nilai | React `/grades`; `/api/v1/grades` | Admin/Teacher membaca snapshot, edit nilai course 0-100/feedback. Nilai quiz otomatis ditampilkan terpisah; Admin dapat mengosongkan nilai course tanpa menghapus attempt/progres. Export `.xlsx` memuat kedua nilai | Teacher tidak dapat menghapus nilai course |
| Nilai Student | PHP `/student/nilai`; `apiStudentGrades()` | Halaman Student menampilkan hasil quiz dan progres per course; nilai course manual, feedback, dan tanggal penilaian tidak ditampilkan | Data quiz/progres dibaca controller PHP melalui PDO |
| Sinkron nilai/progres | `services/student-sync.js` | Sync periodik mengambil enrollment/progres dan quiz_score dari MySQL ke `student_progress_snapshots`; edit/hapus nilai course staff menjadwalkan update target ke MySQL | Sinkronisasi bukan transaksi atomik lintas DB |
| Notifikasi | React `/notifications`; PHP `/student/notifikasi`; `/api/v1/notifications` | Inbox/read/unread, notifikasi terarah, badge/event SSE. Admin dapat mengirim ke audiens yang diizinkan; Teacher hanya ke Student | Outbox mengirim email bila diminta dan SMTP aktif. Email eksternal tidak diuji oleh unit test |
| Notifikasi nilai/pengingat | React `/grades` + `POST /grades/:npk/:course/notify` | Membuat notifikasi Student individual; email opsional masuk outbox | Email tetap mengikuti status SMTP dan alamat Student; data pesan disimpan di SQL Server |
| Forum | React `/forum`, PHP `/student/forum/*`; `/api/v1/forum` | Thread/reply/like, lampiran gambar, moderasi, open/close per sesi, akses course/enrollment, feed SSE; client Student dan staff memakai backend SQL Server | EventEmitter SSE bersifat lokal pada satu proses; multi-instance membutuhkan broker/pub-sub bersama |
| Pembelajaran Student | PHP `/student/courses`, `/student/course/detail`, `/student/learning`, `/student/riwayat*` | Katalog metadata aktif dari MySQL; materi aktif baru dirender setelah enrollment disetujui; progres material/enrollment tersimpan dan disinkronkan; materi sumber terhapus diarsipkan | Sebagian statistik/detail riwayat masih memakai data fallback/demo |
| Quiz course | React `/quizzes`, Backend `/api/v1/quizzes`, PHP `/student/quiz` | Admin/Teacher mengelola quiz pilihan ganda per course; Student wajib menuntaskan semua materi, lalu hanya dapat submit satu kali. Attempt, score, dan jawaban tersimpan di MySQL; score tampil di `/grades` dan `/student/nilai`. Alur pre-test/post-test sesi telah dihapus. | Final assessment lama masih demo |
| Profil dan bantuan | PHP `/student/profil*` | Halaman informasi, pengaturan/tampilan, bantuan, dan riwayat | Belum ada CRUD profil production; beberapa isinya contoh atau sesi lokal |
| Job akun | `JobsCenter.jsx`; `/api/v1/jobs`; `admin-jobs.js` | Payload terenkripsi AES-GCM, status/event tersimpan di SQL Server, worker memproses item satu per satu, SSE progress/item, reload memuat snapshot, stop/resume mempertahankan item belum diproses | Browser perlu online untuk menerima progress/kontrol; server worker harus aktif untuk melanjutkan antrean |
| UI lintas portal | `showAlert`, `JobsCenter`, layout, action-controls | Konfirmasi/toast memakai SweetAlert2; modal antrean dapat disembunyikan/dipindah, layout responsif; sidebar portal staff punya overlay/close untuk mobile | Ini peningkatan interaksi, tidak mengubah hak API atau persistensi |
| PWA | React Admin/Teacher dan PHP Student | Install prompt per browser, manifest, ikon, worker; network-first navigasi dan offline fallback, cache aset statis | Tidak ada offline auth/data, push notification, background sync, atau antrean perubahan offline |

## Alur keamanan dan data yang perlu didokumentasikan

### Akun dan pengiriman sandi

1. Akun baru menerima kata sandi awal; API/worker menyimpan bcrypt hash.
2. Ekspor CSV tidak mengeluarkan hash maupun sandi.
3. Pengiriman email akun selalu memerlukan sandi baru yang dimasukkan Admin. Worker memperbarui hash akun lalu memasukkan NPK dan sandi plaintext yang sama ke email.
4. Kata sandi lama tidak dapat dipulihkan dari hash. Email akun bukan “ambil sandi lama”; tindakan itu adalah reset sandi yang berjalan sebagai job.
5. Akun Student dikelola pada database MySQL melalui service backend. Kegagalan koneksi Student dapat menggagalkan item Student tanpa menutup operasi staff SQL Server lainnya.

### Job yang bisa dihentikan

Job menyimpan payload terenkripsi dan item tersisa di SQL Server. Worker mencatat hasil per item; stop ditangani di batas item yang sedang berjalan. Item belum diproses tetap berada di payload dan status berhenti, lalu dapat di-resume. Snapshot/event diambil kembali saat portal dibuka ulang; SSE memberi update saat portal online. Item gagal dicatat terpisah dan tidak otomatis dibuang dari riwayat.

### Nilai dan quiz

Nilai staff adalah score/feedback per Student-course pada snapshot SQL Server dan salinannya pada MySQL enrollment. Nilai quiz course dihitung saat submit, disimpan sekali per Student/course di MySQL, dan disalin ke kolom quiz_score snapshot pada sync berikutnya. Nilai course manual dan nilai quiz tetap terpisah; hapus nilai manual tidak menghapus attempt quiz.

### Request enrollment dan role belajar

Request Student serta rekomendasi Teacher menggunakan tabel MySQL terpisah dari enrollment aktif. Constraint generated unique key mencegah duplikat pending, lalu transaksi keputusan Admin membuat/mengaktifkan `course_enrollments` dan mengubah status request. Endpoint akses materi/quiz/file/forum dan progres tetap menolak akun tanpa enrollment aktif/completed. Teacher mode memakai `teacher_shadow` dengan NPK yang sama dan `student_id` tersendiri, tetapi role pegawai di SQL Server tetap `teacher`; password login Student tidak menerima akun shadow.

## Gap dan batas yang tersisa

| Area | Kondisi source saat audit | Dampak |
|---|---|---|
| Course/sesi | API create/read/assign trainer, tanpa endpoint update/delete umum | Edit/hapus course/sesi belum dapat didokumentasikan sebagai CRUD lengkap |
| Quiz course | CRUD staff, gate kelengkapan materi, single attempt dan score tersimpan | Legacy final assessment masih berupa demo; pre-test/post-test sesi dihapus |
| Profil Student | Beberapa halaman presentasi/sample | Perubahan profil tidak tersimpan sebagai CRUD produksi |
| Realtime multi-instance | Forum/notifikasi memakai EventEmitter per proses | SSE lintas replica belum konsisten tanpa pub/sub eksternal |
| Revokasi sesi | JWT/cookie tanpa denylist/revocation endpoint; TTL cookie 8 jam | Perubahan status akun/logout dari device lain tidak selalu mematikan token yang sudah diterbitkan seketika |
| Teacher mode | Cookie mode belajar maksimum 30 menit; role/status Teacher diverifikasi saat mulai dan saat kembali | Penonaktifan Teacher di tengah sesi tidak mencabut token yang sudah diterbitkan sebelum masa habis |
| Promosi lintas database | SQL Server account dan tipe akun MySQL dikonversi dalam dua transaksi database berbeda, dengan kompensasi jika langkah kedua gagal | Gangguan proses tepat saat kompensasi dapat meninggalkan keadaan parsial; monitor operasi promosi dan rekonsiliasi NPK jika endpoint mengembalikan konflik |
| Email kredensial | Isi sandi plaintext dikirim lewat email setelah Admin menetapkan sandi baru | Perlakukan email sebagai kanal sensitif; gunakan SMTP/TLS dan kebijakan internal yang sesuai |
| Sync lintas DB | Operasi SQL Server dan MySQL bukan satu transaksi | Kegagalan sinkron bisa menimbulkan jeda/ketidaksamaan sampai sync berikutnya |
| Browser/deployment | Smoke browser, test email eksternal, dan load multi-instance memerlukan konfigurasi tambahan | Unit test lulus tidak menggantikan uji staging pada domain, DB, SMTP, dan perangkat tujuan |

## Diagram alur

Semua flow editable Draw.io berada di [`diagrams/`](diagrams/). Flow yang direvisi pada audit ini: login/SSO (01), pengguna dan operasi antrean (02/13), reviewer (03), course (05), belajar dan nilai quiz (08), notifikasi/email (10), nilai/progres dan sinkron (11), serta use case sistem. Flow persetujuan enrollment dan mode belajar Teacher ada pada 16. Flow PWA ada pada 14 dan arsitektur pada 15.

## Verifikasi audit

Pada 25 Oktober 2026, test default yang tidak mengubah database aktif memberikan hasil:

- Backend `npm test`: 72 lulus, 4 dilewati karena memerlukan server lokal; 0 gagal.
- Frontend `npm test`: 9 lulus, 1 dilewati karena memerlukan browser/headless browser; 0 gagal.
- Student `php tests/run.php all`: 14 lulus; PHP menampilkan warning karena `session.save_path` Laragon di luar workspace tidak bisa ditulis. Exit code tetap sukses.
- Frontend PWA production build sebelumnya berhasil; manifest JSON, ikon, XML diagram baru, dan tautan dokumen tervalidasi.

Test suite ini tidak membuktikan pengiriman SMTP, semua perubahan UI pada database aktif, perilaku PWA di setiap perangkat, atau kesiapan multi-instance.
