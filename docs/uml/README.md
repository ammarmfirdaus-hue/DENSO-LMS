# DENSO-LMS Flowcharts & UML Diagrams (`.drawio`)

Direktori ini berisi seluruh **flowchart sistem ISO 5807 monokrom (`#ffffff` / `#000000`)** yang dipisah terstruktur per domain dan per fitur, dilengkapi master multi-tab workbook `.drawio`, ekspor `.png` siap baca di `exports/`, serta diagram UML use case.

## Struktur Direktori

```text
docs/uml/
├── backend-frontend-admin/
│   ├── frontend-admin/          # 10 Flowchart fitur React Admin & Teacher PWA + workbook
│   └── backend-engine/          # 4 Flowchart internal Express 5 Backend Engine + workbook
├── native-student/              # 8 Flowchart fitur Portal Native PHP Student + workbook
├── exports/                     # Ekspor PNG (1680x1160) yang mencerminkan struktur folder di atas
├── backend-usecase.drawio       # UML Use Case Backend API & Admin/Teacher/Reviewer
└── student-usecase.drawio       # UML Use Case Portal Native Student
```

Standar visual seluruh flowchart:
- **Monokrom murni:** latar putih `#ffffff`, garis/teks/simbol hitam `#000000` (tanpa warna lain).
- **Kejelasan label & garis:** label cabang panah bebas kotak (`labelBorderColor=none`), tanpa lingkaran konektor berkode atau tag sintaks yang membingungkan, dan tervalidasi **0 overlap** garis, label, kolom, maupun bentuk.

---

## 1. Folder `backend-frontend-admin/frontend-admin/` (Admin & Teacher PWA)

Workbook multi-tab: [`frontend-admin-all-flows.drawio`](./backend-frontend-admin/frontend-admin/frontend-admin-all-flows.drawio)

| No | File `.drawio` | Preview PNG | Cakupan Fitur |
|---|---|---|---|
| 01 | [`01-flow-login-auth-sso.drawio`](./backend-frontend-admin/frontend-admin/01-flow-login-auth-sso.drawio) | [`01-flow-login-auth-sso.png`](./exports/backend-frontend-admin/frontend-admin/01-flow-login-auth-sso.png) | Login dual-DB, JWT cookie SSO, RBAC role routing, Teacher Study Mode ke Student |
| 02 | [`02-flow-dashboard-activity-jobscenter.drawio`](./backend-frontend-admin/frontend-admin/02-flow-dashboard-activity-jobscenter.drawio) | [`02-flow-dashboard-activity-jobscenter.png`](./exports/backend-frontend-admin/frontend-admin/02-flow-dashboard-activity-jobscenter.png) | Dashboard Admin/Teacher, feed aktivitas real-time SSE & Jobs Center terenkripsi |
| 03 | [`03-flow-kelola-course-sesi.drawio`](./backend-frontend-admin/frontend-admin/03-flow-kelola-course-sesi.drawio) | [`03-flow-kelola-course-sesi.png`](./exports/backend-frontend-admin/frontend-admin/03-flow-kelola-course-sesi.png) | Manajemen katalog course, struktur sesi, inspeksi prasyarat, serta sinkronisasi dual-DB |
| 04 | [`04-flow-workflow-review-publish-materi.drawio`](./backend-frontend-admin/frontend-admin/04-flow-workflow-review-publish-materi.drawio) | [`04-flow-workflow-review-publish-materi.png`](./exports/backend-frontend-admin/frontend-admin/04-flow-workflow-review-publish-materi.png) | Alur 5 status pengajuan materi, persetujuan Reviewer token eksternal, hingga publish |
| 05 | [`05-flow-kelola-quiz-bank-soal.drawio`](./backend-frontend-admin/frontend-admin/05-flow-kelola-quiz-bank-soal.drawio) | [`05-flow-kelola-quiz-bank-soal.png`](./exports/backend-frontend-admin/frontend-admin/05-flow-kelola-quiz-bank-soal.png) | Pengelolaan bank soal quiz, konfigurasi passing score, aktivasi, dan sinkronisasi MySQL |
| 06 | [`06-flow-enrollment-persetujuan.drawio`](./backend-frontend-admin/frontend-admin/06-flow-enrollment-persetujuan.drawio) | [`06-flow-enrollment-persetujuan.png`](./exports/backend-frontend-admin/frontend-admin/06-flow-enrollment-persetujuan.png) | Pengajuan akses course, persetujuan/penolakan enrollment, dan pembukaan kunci akses |
| 07 | [`07-flow-nilai-grading-export.drawio`](./backend-frontend-admin/frontend-admin/07-flow-nilai-grading-export.drawio) | [`07-flow-nilai-grading-export.png`](./exports/backend-frontend-admin/frontend-admin/07-flow-nilai-grading-export.png) | Penilaian manual/otomatis, rekap snapshot nilai siswa, serta ekspor laporan |
| 08 | [`08-flow-forum-diskusi-moderasi.drawio`](./backend-frontend-admin/frontend-admin/08-flow-forum-diskusi-moderasi.drawio) | [`08-flow-forum-diskusi-moderasi.png`](./exports/backend-frontend-admin/frontend-admin/08-flow-forum-diskusi-moderasi.png) | Moderasi forum sesi (buka/tutup switch, kunci/sembunyikan topik), balasan, & SSE |
| 09 | [`09-flow-kelola-users-reviewers.drawio`](./backend-frontend-admin/frontend-admin/09-flow-kelola-users-reviewers.drawio) | [`09-flow-kelola-users-reviewers.png`](./exports/backend-frontend-admin/frontend-admin/09-flow-kelola-users-reviewers.png) | CRUD akun Admin/Teacher/Student, import CSV massal via antrean job, dan Reviewer |
| 10 | [`10-flow-notifikasi-settings-api.drawio`](./backend-frontend-admin/frontend-admin/10-flow-notifikasi-settings-api.drawio) | [`10-flow-notifikasi-settings-api.png`](./exports/backend-frontend-admin/frontend-admin/10-flow-notifikasi-settings-api.png) | Pengiriman notifikasi email SMTP & SSE, konfigurasi sistem, serta monitoring/health |

---

## 2. Folder `backend-frontend-admin/backend-engine/` (Express 5 Backend Internals)

Workbook multi-tab: [`backend-engine-all-flows.drawio`](./backend-frontend-admin/backend-engine/backend-engine-all-flows.drawio)

| No | File `.drawio` | Preview PNG | Cakupan Mekanisme |
|---|---|---|---|
| 01 | [`01-flow-backend-middleware-jwt-rbac.drawio`](./backend-frontend-admin/backend-engine/01-flow-backend-middleware-jwt-rbac.drawio) | [`01-flow-backend-middleware-jwt-rbac.png`](./exports/backend-frontend-admin/backend-engine/01-flow-backend-middleware-jwt-rbac.png) | Pipeline Helmet, CORS, rate-limiter bertingkat, JSON strict 8MB, verifikasi JWT & RBAC |
| 02 | [`02-flow-backend-material-workflow-engine.drawio`](./backend-frontend-admin/backend-engine/02-flow-backend-material-workflow-engine.drawio) | [`02-flow-backend-material-workflow-engine.png`](./exports/backend-frontend-admin/backend-engine/02-flow-backend-material-workflow-engine.png) | Engine transisi status materi (`pending_admin` s.d. `published`), lock edit, dan cleanup file |
| 03 | [`03-flow-backend-aes-job-worker-sse.drawio`](./backend-frontend-admin/backend-engine/03-flow-backend-aes-job-worker-sse.drawio) | [`03-flow-backend-aes-job-worker-sse.png`](./exports/backend-frontend-admin/backend-engine/03-flow-backend-aes-job-worker-sse.png) | Enkripsi payload AES-256-GCM, claim lock worker SQL Server, stop/resume aman & SSE |
| 04 | [`04-flow-backend-dual-db-sync-engine.drawio`](./backend-frontend-admin/backend-engine/04-flow-backend-dual-db-sync-engine.drawio) | [`04-flow-backend-dual-db-sync-engine.png`](./exports/backend-frontend-admin/backend-engine/04-flow-backend-dual-db-sync-engine.png) | Sinkronisasi katalog MSSQL ke MySQL (`student-sync`) & MERGE snapshot progres kembali ke MSSQL |

---

## 3. Folder `native-student/` (Portal Native PHP Student)

Workbook multi-tab: [`native-student-all-flows.drawio`](./native-student/native-student-all-flows.drawio)

| No | File `.drawio` | Preview PNG | Cakupan Fitur |
|---|---|---|---|
| 01 | [`01-flow-student-login-sso-security.drawio`](./native-student/01-flow-student-login-sso-security.drawio) | [`01-flow-student-login-sso-security.png`](./exports/native-student/01-flow-student-login-sso-security.png) | Verifikasi SSO cookie/JWT, rate-limit sesi PHP, guard CSRF, dan routing MVC Student |
| 02 | [`02-flow-student-dashboard-katalog.drawio`](./native-student/02-flow-student-dashboard-katalog.drawio) | [`02-flow-student-dashboard-katalog.png`](./exports/native-student/02-flow-student-dashboard-katalog.png) | Dashboard progres belajar aktif (`sedang_dipelajari`), filter & katalog `/student/courses` |
| 03 | [`03-flow-student-course-detail-enrollment.drawio`](./native-student/03-flow-student-course-detail-enrollment.drawio) | [`03-flow-student-course-detail-enrollment.png`](./exports/native-student/03-flow-student-course-detail-enrollment.png) | Detail course & 5 tab, gate `has_access`, serta form ajukan enrollment dengan CSRF |
| 04 | [`04-flow-student-learning-materi.drawio`](./native-student/04-flow-student-learning-materi.drawio) | [`04-flow-student-learning-materi.png`](./exports/native-student/04-flow-student-learning-materi.png) | Mode belajar PDF/Video/YouTube, POST `apiCompleteStudentMaterial()` + CSRF, & prev/next sesi |
| 05 | [`05-flow-student-quiz-assessment.drawio`](./native-student/05-flow-student-quiz-assessment.drawio) | [`05-flow-student-quiz-assessment.png`](./exports/native-student/05-flow-student-quiz-assessment.png) | Quiz & final assessment, prasyarat materi/sesi, submit jawaban CSRF & skor otomatis |
| 06 | [`06-flow-student-forum-diskusi.drawio`](./native-student/06-flow-student-forum-diskusi.drawio) | [`06-flow-student-forum-diskusi.png`](./exports/native-student/06-flow-student-forum-diskusi.png) | Kategori forum materi, buat thread WebP, balasan & like, switch moderasi, serta SSE |
| 07 | [`07-flow-student-nilai-riwayat-notifikasi.drawio`](./native-student/07-flow-student-nilai-riwayat-notifikasi.drawio) | [`07-flow-student-nilai-riwayat-notifikasi.png`](./exports/native-student/07-flow-student-nilai-riwayat-notifikasi.png) | Rekap nilai `/student/nilai`, riwayat course selesai `/student/riwayat` & notifikasi SSE |
| 08 | [`08-flow-student-profil-teacher-return-api.drawio`](./native-student/08-flow-student-profil-teacher-return-api.drawio) | [`08-flow-student-profil-teacher-return-api.png`](./exports/native-student/08-flow-student-profil-teacher-return-api.png) | Halaman profil/karir/pengaturan/bantuan, pengembalian sesi Teacher, & endpoint JSON internal |

---

## 4. Diagram UML Use Case

- [`backend-usecase.drawio`](./backend-usecase.drawio) — Use Case Backend API & Admin/Teacher/Reviewer.
- [`student-usecase.drawio`](./student-usecase.drawio) — Use Case Portal Native Student.
