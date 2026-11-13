# DENSO-LMS Backend API — Analisa Lengkap, Referensi Kompleks & Postman Test Suite

Suite **Postman Collection v2.1.0** yang diperbarui penuh sesuai seluruh implementasi backend **DENSO-LMS** saat ini:
- [`DENSO-LMS-Backend.postman_collection.json`](./DENSO-LMS-Backend.postman_collection.json) — **130 request** dalam **16 folder**:
  - **Folder `01`–`15` (90 endpoint aktif):** **86 route Express 5** (`GET /health` + `/api/v1/*` pada 13 router domain) + **1 PHP Session Bootstrap** (`GET /?route=/login`) + **3 JSON resource PHP** (`NATIVE-LMS-Student/api/index.php?resource=me|dashboard|courses`).
  - **Folder `16` (40 skenario uji kompleks):** Variasi body test positif & negatif (*role escalation*, Zod `.strict()`, magic-byte/link check, state lock, promosikan student → teacher shadow, batch job 4 action, bulk delete 4 kind, dan pagination/HTTP method guard).
- [`DENSO-LMS-Local.postman_environment.json`](./DENSO-LMS-Local.postman_environment.json) — **40 variabel environment** (termasuk token terpisah `admin_token`, `teacher_token`, `student_token`, `reviewer_token`, serta data demo selaras `seed-demo.js` & `seed-demo.php`).

---

## 1. Analisa Penuh Backend DENSO-LMS (Arsitektur & Temuan Kode Aktual)

### 1.1 Perbaikan Bug Collection & Environment Sebelumnya
Pada audit mendalam terhadap implementasi backend (`Backend-LMS-Admin-Teacher/src/` + `NATIVE-LMS-Student/` + `scripts/seed-demo.*`), ditemukan dan diperbaiki beberapa celah pada versi Postman sebelumnya:
1. **`PUT /api/v1/auth/profile` (Body Rusak Diperbaiki):** Skema `auth.js` mendefinisikan `newPassword: z.string().min(8).max(200).optional()`. Mengirim `"newPassword": ""` menyebabkan error `400 INVALID_INPUT`. Body default kini hanya mengirim `fullname` & `email`, sedangkan skenario penggantian password dipisahkan ke Folder `16.1`.
2. **`reviewer_email` Environment Diperbaiki:** `seed-demo.js` mendaftarkan `DLS-DEMO-REV-001` dengan email `reviewer1.demo@example.test` (bukan `reviewer.demo@denso-lms.local`). Nilai environment sudah disesuaikan agar `POST /material-workflow/submissions/:id/send-review` langsung cocok.
3. **`POST /api/v1/jobs/users` Password Guard Diperbaiki:** Handler `user-job-handler.js` mewajibkan akun baru pada aksi `upsert` memiliki sandi `>= 12` karakter. Request ini kini dibekali *Pre-request Script* pembuat sandi kuat `{{effective_user_password}}` otomatis.
4. **Handshake SSO → PHP Session untuk `NATIVE-LMS-Student/api/index.php`:** Pemeriksaan `requireStudent()` di `NATIVE-LMS-Student/includes/Security.php` mewajibkan **cookie `dls_sso` DAN `$_SESSION['student_id']`**. Ditambahkan `[Bootstrap Session] Exchange dls_sso Cookie -> PHP Session` pada Folder `15` agar uji API Student berhasil (`200 OK`) langsung dari Postman.
5. **100% Cakupan Assertion Test (`pm.test`):** Meningkat dari hanya 23 request menjadi **seluruh 130 request** ber-script test otomatis dan rantai variabel.

### 1.2 Peta Layanan & Perimeter Keamanan
| Lapisan | Teknologi & Port | Database | Kontrol Keamanan Utama |
|---|---|---|---|
| **Backend Admin/Teacher API** | Node.js ≥20, Express 5 (`127.0.0.1:3001`) | SQL Server `DX_LMS_AdminTeacher` (18 tabel, `dbo.*`) + MySQL 8+ `dls_student` (12 tabel) | `Helmet`, `CORS` whitelist (`FRONTEND_ORIGIN`, `STUDENT_APP_URL`), payload JSON maks `8 MB` (`strict: true`), `X-Response-Time` |
| **Native Student Portal & API** | PHP 8+ Native (`127.0.0.1:8082`) | MySQL 8+ `dls_student` (`PDO`) | `DX_LMS_M_Request_Middleware` (**180 req/60 detik**), `HttpOnly + SameSite=Lax`, CSRF 32-byte, CSP ketat, `X-Frame-Options: DENY` |

### 1.3 Mekanisme Autentikasi, RBAC & Guardrails Lintas Database
1. **Login Timing-Safe & Dual Session:** `POST /api/v1/auth/login` mencari akun aktif di SQL Server (`dbo.employee_accounts`), lalu (bila `STUDENT_AUTH_ENABLED=true`) di MySQL `student_accounts`. Akun tidak ditemukan tetap melewati `bcrypt.compare(..., DUMMY_PASSWORD_HASH)` untuk mencegah timing attack. Response mengeluarkan JWT singkat (`JWT_TTL`, default `15m`) serta cookie HttpOnly `dls_sso` (`8h`).
2. **Teacher Study Mode (`teacher_shadow`) & Promosi Student:**
   - `POST /auth/student-mode` (`Origin: {{frontend_origin}}`) menerbitkan cookie `dls_sso` berumur **30 menit** (`mode: 'teacher_student'`, `source_role: 'teacher'`). Jika NPK Teacher sama dengan akun `student` murni di MySQL, ditolak `409 STUDENT_PROMOTION_REQUIRED`.
   - Admin dapat mempromosikan Student → Teacher via `PUT /users/:npk` (`role: 'teacher'`), yang menulis ke SQL Server (`SERIALIZABLE`) dan mengonversi `account_type='teacher_shadow'` di MySQL — disertai **kompensasi `DELETE` otomatis** pada SQL Server bila langkah MySQL gagal.
3. **Trusted Origin Enforcement (`UNTRUSTED_ORIGIN`):**
   - Route tulis sensitif (`auth/student-mode`, `auth/teacher-mode`, `quizzes`, `enrollments`, `grades`, `forum`, `notifications`) menolak request tanpa header `Origin` yang persis cocok dengan `FRONTEND_ORIGIN` (`http://127.0.0.1:5173`) atau `STUDENT_APP_URL` (`http://127.0.0.1:8082`).
4. **Rate Limit (`draft-8` Headers):**
   - Global `/api/v1`: **300 req / 1 menit**
   - Write non-GET `/api/v1`: **90 req / 15 menit**
   - Login (`POST /auth/login`): **8 req / 15 menit** per `IP + SHA-256(npk)`
   - Upload materi (`/material-workflow`) & gambar forum (`/forum`): **20 req / 15 menit**
   - Broadcast notifikasi (`POST /notifications`): **10 req / 1 jam** per `role:npk`
   - SSE Concurrency: maks **2 stream** (`dashboard/activity/events`) dan **3 stream** (`forum/events`, `notifications/events`) per `role:npk`.
5. **Inspeksi Berkas Magic-Bytes & Isolasi Upload:**
   - Materi (`/material-workflow`): maks **`250 MB`** (`MATERIAL_MAX_UPLOAD_BYTES`), validasi ekstensi **plus** pengecekan 16 byte header asli file (`%PDF-`, PNG, JPG, GIF, WEBP, MP4/MOV/M4A `ftyp`, WebM, Ogg, MP3, WAV, Office OOXML `PK` / OLE2). Ketidakcocokan menghasilkan `415 INVALID_FILE_CONTENT`.
   - Lampiran Forum (`/forum`): gambar `JPG/PNG/GIF/WebP` di memori maks **`5 MB`** (`parseForumImageBuffer`).
6. **Workflow Persetujuan Materi 5 Status & Magic-Link Reviewer:**
   - Alur status: `pending_admin` → (`send-review`) `pending_review` → (`decision`) `approved` / `rejected` → (`publish`) `published`.
   - Teacher hanya dapat mengedit ulang saat status `'rejected'` (`CANNOT_EDIT_PENDING_APPROVAL`, `CANNOT_EDIT_IN_REVIEW`, `CANNOT_EDIT_FINAL`).
   - Magic link reviewer berlaku **48 jam** dan disimpan sebagai hash **SHA-256** sekali pakai (`consumed_at`); ditukar di `POST /links/:token/consume` menjadi JWT scope `reviewer_portal` (`2h`). Jika SMTP gagal saat `send-review`, status otomatis rollback ke `'pending_admin'`.
7. **Background Jobs Terenkripsi `AES-256-GCM` & Pause/Resume Aman:**
   - `POST /jobs/users` (`upsert`, `email`, `deactivate`, `delete`) dan `POST /jobs/records/delete` (`grade`, `quiz`, `material_submission`, `published_material`) mengenkripsi array `items` (`1..5000`) menggunakan `AES-256-GCM` (kunci SHA-256 dari `JWT_SECRET`).
   - Worker mengklaim job secara atomik (`WITH (UPDLOCK, READPAST, ROWLOCK)`), mengenkripsi ulang sisa item setelah setiap item selesai, dan mendukung `/stop` (`stopping` → `stopped`) serta `/resume` tanpa kehilangan progres.

---

## 2. Daftar Folder Collection (`01`–`16`)

| Folder | Cakupan Endpoint / Skenario | Jumlah Item |
|---|---|---:|
| `01 - System & Health` | `GET /health`, `GET /settings/runtime-config` | 2 |
| `02 - Authentication & Session` | Login, Switch Study/Teacher Mode, `/me`, Update Profile, Logout | 6 |
| `03 - Dashboard & Activity` | Summary KPI, Dashboard Courses, 3-Source Activity Feed & SSE | 4 |
| `04 - Courses & Sessions` | CRUD Course, Assign Trainer, Sessions, Course Full Detail, Legacy 410 | 7 |
| `05 - Material Workflow & Reviewer Portal` | Submission Multipart, Admin Reject, Send Review (SHA-256), Portal Reviewer JWT, Publish + Sync, Delete | 18 |
| `06 - Quizzes` | Summary, Detail Soal, Create/Update `.strict()` (MySQL), Archive/Delete | 5 |
| `07 - Enrollments` | Form Options Teacher, Requests List, Recommendation, Admin Decision + Notif | 4 |
| `08 - Grades` | Snapshot Grades, Patch Score `DECIMAL(5,2)` + Reverse Sync, Delete, Notify Student | 4 |
| `09 - Users Management` | Combined SQL Server+MySQL Users, Create, Update/Promote Shadow, Delete | 4 |
| `10 - Reviewers Management` | List Stats Review, Create/Update HRD Profile, Soft Deactivate | 4 |
| `11 - Forum` | SSE Forum, Manage Courses/Threads, Open/Close Session, Multipart Threads/Replies, Moderation, Likes, Image Download | 12 |
| `12 - Notifications` | Unread Count, SSE Popup Events, List Paginated, Multi-Role Blast + Email Outbox, Read/Read-All `MERGE` | 6 |
| `13 - Background Jobs` | List Jobs, Enqueue Users & Bulk Delete (`AES-256-GCM`), Events, Stream SSE, Stop & Resume | 7 |
| `14 - System Settings & Sync` | API & Sync Diagnostics, Auto-Normalized URL Update, Manual Dual-DB Sync Trigger | 3 |
| `15 - Native Student PHP API` | SSO→PHP Session Bootstrap + `?resource=me`, `?resource=dashboard`, `?resource=courses` | 4 |
| `16 - Complex Test Scenarios & Body Variants` | **7 sub-folder** berisi **40 payload test** skenario positif, edge case & negatif (`400/401/403/405/409/423`) | 40 |

---

## 3. Contoh Katalog Test Body Siap Pakai (Highlights)

### A. Batch Users Job — Semua 4 Aksi (`POST /api/v1/jobs/users`)
```json
{
  "items": [
    {
      "action": "upsert",
      "npk": "DLS-PM-JOB-001",
      "label": "1. Buat Student Uji",
      "fullname": "Postman Job Tester",
      "email": "pm.job001@example.test",
      "role": "student",
      "status": "active",
      "password": "PasswordKuat#2026",
      "sendEmail": false
    },
    {
      "action": "email",
      "npk": "DLS-PM-JOB-001",
      "label": "2. Kirim Kredensial",
      "fullname": "Postman Job Tester",
      "email": "pm.job001@example.test",
      "role": "student",
      "status": "active",
      "password": "PasswordKuat#2026"
    },
    { "action": "deactivate", "npk": "DLS-PM-JOB-001", "label": "3. Nonaktifkan", "role": "student" },
    { "action": "delete", "npk": "DLS-PM-JOB-001", "label": "4. Hapus Bersih", "role": "student" }
  ]
}
```

### B. Bulk Records Delete Job — 4 Discriminated Union (`POST /api/v1/jobs/records/delete`)
```json
{
  "items": [
    { "kind": "grade", "npk": "DLS-DEMO-STU-001", "courseCode": "DLS-DEMO-SAFE-001", "label": "Hapus Nilai" },
    { "kind": "quiz", "courseCode": "DLS-DEMO-SAFE-001", "label": "Hapus/Arsipkan Quiz" },
    { "kind": "material_submission", "id": 1, "label": "Hapus Submission #1" },
    { "kind": "published_material", "id": 1, "label": "Hapus Materi Final #1" }
  ]
}
```

### C. Promosi Student → Teacher Shadow (`PUT /api/v1/users/DLS-DEMO-STU-100`)
```json
{
  "fullname": "Peserta Demo 100 (Promoted Teacher)",
  "email": "student100.demo@example.test",
  "role": "teacher",
  "status": "active",
  "password": "",
  "sendEmail": false
}
```

---

## 4. Cara Menjalankan di Postman
1. **Import** [`DENSO-LMS-Backend.postman_collection.json`](./DENSO-LMS-Backend.postman_collection.json) dan [`DENSO-LMS-Local.postman_environment.json`](./DENSO-LMS-Local.postman_environment.json).
2. Pilih environment **`DENSO-LMS - Local Environment`** dan isi `admin_password` (serta opsional `teacher_password` & `student_password` sesuai `SEED_DEMO_PASSWORD` / akun lokal).
3. Jalankan Login Admin (`02`) — seluruh variabel token & ID (`course_id`, `course_code`, `session_id`, `submission_id`, `thread_id`, `job_id`, `notification_id`) akan terisi otomatis lewat tab **Scripts / Tests** di tiap request.
4. Untuk menguji **Folder `16`**, jalankan juga kedua login peran tambahan di Folder `16.1` (`teacher_token` dan `student_token`), lalu jalankan seluruh skenario sesuai kebutuhan.
