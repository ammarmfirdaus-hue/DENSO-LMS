# AGENTS.md

## Denso Learning System (DLS) — PT Denso Indonesia

Dokumen ini berfungsi sebagai panduan operasional dan teknis untuk AI Antigravity dan seluruh agen pengembang yang akan bekerja pada proyek **Denso Learning System (DLS)**.

Dokumen ini menjelaskan:

* konteks produk,
* tujuan pengembangan,
* batasan implementasi,
* standar teknis,
* standar desain,
* standar arsitektur,
* konvensi penamaan,
* struktur folder proyek,
* prioritas pengembangan,
* serta aturan kerja yang wajib diikuti.

---

# 1. PROJECT CONTEXT

## 1.1 Nama Produk

**Denso Learning System (DLS)**

## 1.2 Jenis Produk

Web-based Learning Management System untuk internal PT Denso Indonesia.

## 1.3 Tujuan Produk

Sistem ini dibuat untuk mendukung proses pembelajaran internal perusahaan secara terstruktur, terdokumentasi, dan dapat dipantau dengan baik.

## 1.4 Tahap Pengembangan Saat Ini

Tahap saat ini adalah **Soft Launching / MVP** dengan fokus pada siklus utama pembelajaran antara **Teacher** dan **Student**.

## 1.5 Fokus Fitur Soft Launching

Fitur utama yang harus diprioritaskan:

* Authentication
* Dashboard
* Course Management
* Session Management
* Material Management
* Quiz Management
* Final Assessment
* Progress Monitoring
* Student Activity Tracking
* Profile Management

## 1.6 Fitur yang Belum Menjadi Prioritas Utama

Fitur berikut belum menjadi fokus utama pada soft launching:

* Forum Discussion
* Attendance Module
* Certificate Module
* Notification Module
* Admin Import Workflow
* Full Administration Panel

Fitur yang belum prioritas boleh tetap disiapkan secara arsitektural, tetapi **tidak perlu dipaksakan menjadi inti MVP**.

---

# 2. PRODUCT PHILOSOPHY

## 2.1 Prinsip Utama

Produk harus memenuhi lima prinsip berikut:

1. Clarity
2. Consistency
3. Efficiency
4. Accessibility
5. Reliability

## 2.2 Karakter Produk

Antarmuka dan logika sistem harus mencerminkan karakter:

* profesional,
* enterprise,
* ringan,
* stabil,
* mudah dipahami pengguna internal,
* dan cocok untuk lingkungan perusahaan manufaktur.

## 2.3 Filosofi Desain

Desain mengikuti pendekatan **Corporate-Modern** dengan elemen:

* bersih,
* rapi,
* whitespace yang cukup,
* fokus pada data dan proses,
* tidak berlebihan secara visual,
* mudah dipindai dalam waktu singkat.

---

# 3. ROLE AND PERMISSION MODEL

## 3.1 Roles

Sistem memiliki tiga role utama:

* Admin
* Teacher
* Student

## 3.2 Soft Launching Scope

Pada soft launching, fokus utama ada pada:

* Teacher
* Student

Admin masih dipertahankan sebagai bagian struktur sistem, tetapi proses pembuatan akun karyawan dapat dilakukan dengan penyuntikan database untuk sementara waktu pada tahap MVP.

## 3.3 Role Responsibilities

### Admin

* mengelola akun user/karyawan,
* membuat course kosong,
* menetapkan teacher,
* menetapkan student,
* mengelola roadmap course,
* melakukan monitoring pembelajaran.

### Teacher

* membuat dan mengelola course,
* mengelola sesi,
* mengelola materi,
* mengelola quiz,
* membuat final assessment,
* melihat aktivitas student,
* melihat progress dan hasil pembelajaran.

### Student

* melihat course,
* mempelajari materi,
* mengerjakan quiz,
* mengerjakan final assessment,
* melihat progress,
* melihat riwayat pembelajaran.

---

# 4. FUNCTIONAL PRIORITY

## 4.1 Prioritas Tinggi

Semua fitur berikut harus didahulukan:

* Login
* Logout
* Role-based dashboard
* Course management
* Session management
* Material management
* Quiz management
* Final assessment
* Student progress tracking
* Student activity tracking
* Profile management

## 4.2 Prioritas Menengah

* Course target mapping
* Report generation
* PDF export
* DataTables integration
* Chart visualization

## 4.3 Prioritas Rendah / Future Enhancement

* Forum discussion
* Certificate module
* Attendance module
* Notification module
* Advanced analytics
* Full admin import pipeline

---

# 5. TECH STACK RULES

## 5.1 Required Stack

Proyek wajib menggunakan stack berikut:

* HTML5
* CSS3
* Bootstrap 5 Offline
* Vanilla JavaScript
* PHP Native 8.3
* Microsoft SQL Server
* SQLSRV / PDO_SQLSRV
* Apache HTTP Server
* DataTables Offline
* Chart.js Offline
* DomPDF / mPDF
* Bootstrap 5 Modal
* Bootstrap 5 Toast
* Bootstrap 5 Alert

## 5.2 Disallowed as Core Stack

Jangan menjadikan ini sebagai dependency utama untuk implementasi final:

* Tailwind CSS CDN
* Google Fonts CDN
* Material Symbols CDN
* SweetAlert as mandatory dependency
* React
* Vue
* Angular

## 5.3 Offline Requirement

Sistem harus dapat berjalan penuh tanpa koneksi internet eksternal.
Semua library, font, icon, dan asset harus tersedia secara lokal.

---

# 6. UI/UX RULES

## 6.1 Visual Direction

Gunakan desain:

* clean,
* corporate,
* modern,
* minimal,
* readable,
* mobile-friendly.

## 6.2 Layout Rules

* Gunakan 8px spacing system.
* Gunakan border radius konsisten.
* Gunakan card-based layout.
* Gunakan hierarchy visual yang jelas.
* Jangan memenuhi layar dengan elemen dekoratif yang tidak perlu.

## 6.3 Color Rules

Gunakan warna yang telah disepakati dalam DESIGN.md.

## 6.4 Typography Rules

Gunakan Inter atau fallback yang setara.
Pastikan hierarchy heading, body, label, dan metadata tetap konsisten.

## 6.5 Component Rules

Komponen harus seragam untuk seluruh sistem:

* Buttons
* Inputs
* Cards
* Tables
* Alerts
* Modals
* Progress bars
* Badges
* Tabs

## 6.6 Accessibility Rules

Setiap komponen harus:

* memiliki kontras yang baik,
* dapat diakses melalui keyboard,
* memiliki focus state,
* memiliki ukuran klik yang memadai,
* dapat dibaca dengan jelas di mobile.

## 6.7 Student Design Reference Priority

Untuk seluruh implementasi antarmuka yang berkaitan dengan **Student**, AI Antigravity wajib menjadikan isi folder **`Design Student - Denso`** sebagai referensi visual utama dan acuan implementasi paling tinggi. Seluruh halaman Student harus mengikuti desain yang tersedia di folder tersebut secara konsisten, termasuk struktur layout, urutan section, komposisi card, hierarchy visual, spacing, radius, warna, ikon, tipografi, dan pola interaksi antarelemen.

AI Antigravity tidak diperbolehkan melakukan redesign bebas terhadap halaman Student. Perubahan yang dilakukan harus bersifat implementatif dan konservatif, yaitu menerjemahkan desain yang sudah ada ke dalam kode tanpa mengubah identitas visual utama. Jika terdapat beberapa versi desain di dalam folder tersebut, maka AI harus memprioritaskan file yang paling lengkap, paling baru, dan paling konsisten dengan alur penggunaan Student.

Jika terdapat perbedaan antara desain Student dan dokumen lain seperti PRD, DESIGN.md, ERD, atau USECASE, maka untuk seluruh tampilan Student, desain pada folder `Design Student - Denso` tetap menjadi prioritas utama. AI harus mempertahankan gaya visual yang sama pada seluruh halaman Student, termasuk dashboard, course list, detail course, materi, quiz, final assessment, riwayat, notifikasi, dan profil.

AI juga wajib menjaga konsistensi antar halaman Student. Komponen yang sama harus memiliki tampilan, perilaku, dan proporsi yang seragam di seluruh sistem. Bila ada elemen yang belum tersedia secara eksplisit dalam desain referensi, AI harus memilih pendekatan yang paling aman, paling dekat dengan gaya desain yang sudah ada, dan tidak menciptakan pola visual baru yang bertentangan dengan karakter desain Student.

Responsiveness boleh disesuaikan untuk kebutuhan teknis, tetapi penyesuaian tersebut tidak boleh mengubah identitas desain utama. Pada layar mobile, desain harus tetap sangat menyerupai referensi. Pada layar yang lebih besar, AI dapat melakukan penyesuaian tata letak selama tetap menjaga hierarchy visual, proporsi komponen, dan pengalaman pengguna yang konsisten dengan folder desain.

Aturan ini wajib dipatuhi sebagai single source of truth untuk seluruh implementasi UI Student.

---

# 7. BUSINESS RULES

## 7.1 Soft Launching Learning Flow

Alur pembelajaran prioritas:

1. Teacher login
2. Teacher membuat course
3. Teacher membuat session
4. Teacher menambahkan materi
5. Teacher membuat quiz
6. Teacher membuat final assessment
7. Student login
8. Student melihat course
9. Student mempelajari materi
10. Student mengerjakan quiz
11. Student mengerjakan final assessment
12. Teacher memonitor hasil student

## 7.2 Forum Rule

Forum bukan fitur utama pada soft launching.
Jika ada implementasi forum, perlakukan sebagai fitur tambahan, bukan komponen inti.

## 7.3 Assessment Rule

Final assessment harus menjadi evaluasi akhir dari course.
Jika sistem menampilkan assessment, maka mekanismenya harus konsisten dengan quiz tetapi secara konsep dipisahkan secara logis.

## 7.4 Progress Rule

Progress student dihitung berdasarkan penyelesaian session dan aktivitas belajar yang ditetapkan dalam course.

## 7.5 Certificate Rule

Jika sertifikat belum menjadi fitur inti soft launching, maka arsitektur tabel dan layanan harus tetap siap untuk pengembangan berikutnya.

---

# 8. DOMAIN RULES

## 8.1 Employee Data

Employee memiliki atribut utama:

* NPK
* Nama
* Email
* Password
* Jabatan
* Golongan
* Departemen
* Divisi
* Seksi
* Role
* Status akun

## 8.2 Jabatan dan Golongan

Jabatan aktif saat ini:

* Operator
* Staff
* Leader
* SPV

Golongan harus terhubung ke jabatan.
Contoh:

* Operator → Gol 1, Gol 2, Gol 3

## 8.3 Profil User

Departemen, divisi, dan seksi masuk ke profil akun user.
Data ini ditampilkan di profil, namun target course tetap mengikuti kombinasi jabatan dan golongan.

## 8.4 Course Targeting

Course ditargetkan berdasarkan:

* jabatan
* golongan

## 8.5 Assessment Model

Assessment akhir sebaiknya menggunakan model quiz dengan tipe khusus, bukan membuat mekanisme yang benar-benar berbeda secara teknis.

---

# 9. DATA MANAGEMENT RULES

## 9.1 Database Platform

Gunakan Microsoft SQL Server.

## 9.2 Driver Rules

Gunakan salah satu:

* SQLSRV
* PDO_SQLSRV

Rekomendasi utama: **PDO_SQLSRV**.

## 9.3 Query Rules

* Gunakan prepared statement.
* Hindari query raw yang berisiko.
* Buat repository/service layer untuk memisahkan logika database dari controller.

## 9.4 Naming Rules

* Database diawali `DX_`
* Tabel diawali `DX_LMS_`
* Tabel master: `M`
* Tabel transaction: `T`
* Tabel work/temp: `W`
* Gunakan lowercase atau snake case secara konsisten pada nama field.

## 9.5 Auditability

Setiap proses penting harus bisa ditelusuri:

* siapa yang membuat,
* siapa yang mengubah,
* kapan perubahan dilakukan,
* data apa yang berubah.

# 9.6 DATABASE DESIGN RULES

## 9.6.1 Database Design Philosophy

Database harus dirancang dengan pendekatan enterprise yang menekankan:

* maintainability,
* scalability,
* auditability,
* normalization,
* performance,
* dan future extensibility.

Seluruh struktur database harus dapat mendukung perkembangan LMS dalam jangka panjang tanpa memerlukan redesign besar saat fitur baru ditambahkan.

---

## 9.6.2 ERD Compliance

Seluruh implementasi database wajib mengikuti ERD resmi proyek.

AI Antigravity tidak diperbolehkan:

* membuat tabel baru tanpa alasan yang jelas,
* mengubah relasi utama tanpa persetujuan,
* menghapus foreign key penting,
* mengganti struktur master data yang sudah disepakati,
* atau mencampur skema yang tidak sesuai dengan desain final.

Jika ada kebutuhan fitur baru, struktur harus tetap kompatibel dengan ERD yang telah ditetapkan.

---

## 9.6.3 Table Categorization Standard

Seluruh tabel wajib mengikuti kategori berikut:

### Master Table
Prefix:

* `DX_LMS_M_`

Contoh:

* `DX_LMS_M_Employees`
* `DX_LMS_M_Courses`
* `DX_LMS_M_Positions`

Master table digunakan untuk data referensi yang relatif stabil.

---

### Transaction Table
Prefix:

* `DX_LMS_T_`

Contoh:

* `DX_LMS_T_Course_Enrollments`
* `DX_LMS_T_Quiz_Attempts`
* `DX_LMS_T_Activity_Logs`

Transaction table digunakan untuk data aktivitas sistem yang berubah secara dinamis.

---

### Work Table
Prefix:

* `DX_LMS_W_`

Contoh:

* `DX_LMS_W_Staging_Import`

Work table digunakan untuk data sementara, staging, atau proses import.

---

## 9.6.4 Primary Key Standard

Seluruh tabel wajib menggunakan primary key bertipe:

* `BIGINT IDENTITY`

Format primary key harus konsisten dan mudah ditelusuri.

Contoh:

* `employee_id`
* `course_id`
* `quiz_id`
* `activity_log_id`

AI tidak diperbolehkan menggunakan UUID sebagai primary key utama kecuali ada kebutuhan khusus yang benar-benar disetujui.

---

## 9.6.5 Foreign Key Standard

Seluruh relasi antar tabel wajib menggunakan foreign key eksplisit.

Contoh:

* `position_id`
* `golongan_id`
* `department_id`
* `division_id`
* `section_id`
* `course_id`
* `session_id`
* `quiz_id`
* `question_id`
* `employee_id`

AI tidak diperbolehkan menyimpan relasi hanya dalam bentuk text bebas jika relasi tersebut seharusnya bersifat terstruktur.

---

## 9.6.6 Timestamp Standard

Seluruh tabel utama wajib memiliki:

* `created_at`
* `updated_at`

Tipe data yang digunakan adalah `DATETIME2`.

Untuk kebutuhan penghapusan lunak, bila diperlukan dapat ditambahkan:

* `deleted_at`

---

## 9.6.7 Audit Standard

Tabel yang bersifat penting wajib memiliki kolom audit tambahan seperti:

* `created_by`
* `updated_by`

Kolom tersebut harus mengacu ke:

* `DX_LMS_M_Employees.employee_id`

Audit harus memungkinkan sistem mengetahui:

* siapa yang membuat data,
* siapa yang mengubah data,
* kapan data diubah,
* dan apa yang diubah.

---

## 9.6.8 Soft Delete Rule

Data penting tidak boleh langsung dihapus permanen kecuali benar-benar diperlukan.

Gunakan salah satu pendekatan berikut:

* `status` field,
* `deleted_at`,
* atau kombinasi keduanya.

Contoh field status:

* `employee_status`
* `course_status`
* `quiz_status`
* `session_status`
* `material_status`

---

## 9.6.9 Normalization Rule

Database minimal harus memenuhi:

* Third Normal Form (3NF)

AI tidak diperbolehkan:

* menduplikasi data master secara tidak perlu,
* menyimpan data yang seharusnya bisa dihitung ulang,
* membuat kolom redundant tanpa alasan performa yang jelas,
* mencampur data referensi dengan data transaksi.

---

## 9.6.10 Employee Structure Rule

`DX_LMS_M_Employees` adalah entitas inti sistem.

Employee wajib terhubung dengan:

* `DX_LMS_M_Positions`
* `DX_LMS_M_Golongan`
* `DX_LMS_M_Departments`
* `DX_LMS_M_Divisions`
* `DX_LMS_M_Sections`

Struktur profil user harus mendukung:

* NPK,
* nama,
* email,
* password,
* jabatan,
* golongan,
* departemen,
* divisi,
* seksi,
* role,
* status akun.

---

## 9.6.11 Course Target Rule

Target course wajib disimpan pada tabel relasi khusus.

Jangan menyimpan target jabatan dan golongan dalam bentuk text bebas.

Gunakan:

* `DX_LMS_M_Course_Targets`

Relasi yang dipakai:

* course
* position
* golongan

Hal ini wajib agar course yang ditampilkan ke student sesuai dengan kombinasi jabatan dan golongan yang telah ditentukan.

---

## 9.6.12 Learning Structure Rule

Struktur inti pembelajaran wajib mengikuti urutan berikut:

* Course
* Session
* Material
* Quiz
* Final Assessment

Struktur ini harus tercermin pada ERD dan tidak boleh diacak.

---

## 9.6.13 Final Assessment Rule

Final assessment tidak wajib dibuat sebagai tabel yang benar-benar terpisah.

Pendekatan yang disarankan:

* assessment disimpan sebagai tipe khusus di tabel quiz,
* atau dibedakan melalui field seperti `quiz_type`.

Contoh nilai untuk `quiz_type`:

* `regular`
* `final_assessment`

Pendekatan ini menjaga database tetap sederhana, konsisten, dan mudah dipelihara.

---

## 9.6.14 Mandatory Master Tables

Tabel master minimum yang wajib ada adalah:

* `DX_LMS_M_Positions`
* `DX_LMS_M_Golongan`
* `DX_LMS_M_Departments`
* `DX_LMS_M_Divisions`
* `DX_LMS_M_Sections`
* `DX_LMS_M_Employees`
* `DX_LMS_M_Courses`
* `DX_LMS_M_Course_Targets`
* `DX_LMS_M_Course_Sessions`
* `DX_LMS_M_Course_Materials`
* `DX_LMS_M_Quizzes`
* `DX_LMS_M_Quiz_Questions`
* `DX_LMS_M_Quiz_Choices`

Setiap tabel master harus memiliki struktur yang jelas, konsisten, dan mengikuti naming convention proyek.

---

## 9.6.15 Mandatory Transaction Tables

Tabel transaction minimum yang wajib ada adalah:

* `DX_LMS_T_Course_Enrollments`
* `DX_LMS_T_Course_Histories`
* `DX_LMS_T_Material_Progress`
* `DX_LMS_T_Quiz_Attempts`
* `DX_LMS_T_Quiz_Answers`
* `DX_LMS_T_Activity_Logs`

### Fungsi masing-masing tabel

#### `DX_LMS_T_Course_Enrollments`
Menyimpan data pendaftaran employee ke course.

#### `DX_LMS_T_Course_Histories`
Menyimpan histori progress dan penyelesaian course.

#### `DX_LMS_T_Material_Progress`
Menyimpan progress per material atau per session agar pelacakan belajar lebih detail.

#### `DX_LMS_T_Quiz_Attempts`
Menyimpan setiap percobaan pengerjaan quiz atau assessment.

#### `DX_LMS_T_Quiz_Answers`
Menyimpan jawaban detail per soal agar hasil evaluasi dapat dianalisis secara rinci.

#### `DX_LMS_T_Activity_Logs`
Menyimpan log aktivitas user, seperti login, membuka course, melihat materi, mengerjakan quiz, dan menyelesaikan assessment.

---

## 9.6.16 Future Transaction Tables

Tabel berikut boleh disiapkan secara arsitektural untuk pengembangan versi berikutnya:

* `DX_LMS_T_Attendance`
* `DX_LMS_T_Certificates`
* `DX_LMS_T_Notifications`
* `DX_LMS_T_Forum_Threads`
* `DX_LMS_T_Forum_Posts`

Tabel ini tidak wajib menjadi inti soft launching, tetapi struktur folder, naming convention, dan desain database harus tetap siap untuk menampung pengembangannya.

---

## 9.6.17 Work Table Standard

Tabel work minimum yang wajib ada adalah:

* `DX_LMS_W_Staging_Import`

### Fungsi
Tabel ini digunakan untuk menampung data sementara hasil import sebelum dipindahkan ke tabel utama.

### Catatan
Work table tidak boleh diperlakukan sebagai data final.

---

## 9.6.18 SQL Server Compatibility

Seluruh struktur database dan query harus kompatibel dengan Microsoft SQL Server.

Gunakan tipe data yang sesuai, seperti:

* `BIGINT`
* `INT`
* `BIT`
* `VARCHAR`
* `NVARCHAR`
* `TEXT`
* `DATETIME2`
* `DECIMAL`

AI tidak diperbolehkan menghasilkan sintaks yang khusus untuk MySQL atau PostgreSQL kecuali diminta secara eksplisit.

---

## 9.6.19 Indexing Rule

Kolom yang sering digunakan untuk pencarian, relasi, atau filter harus diberi index yang sesuai.

Contoh:

* `employee_npk`
* `employee_email`
* `course_code`
* `course_id`
* `position_id`
* `golongan_id`
* `session_id`
* `quiz_id`

Index harus digunakan secara bijak agar performa tetap optimal tanpa membebani proses insert/update secara berlebihan.

---

## 9.6.20 Referential Integrity Rule

Seluruh foreign key wajib menjaga integritas data.

AI tidak diperbolehkan membuat relasi yang:

* menyebabkan orphan record,
* mengabaikan relasi parent-child,
* atau membuat data tidak konsisten antar tabel.

Jika parent data dihapus atau diubah, perilakunya harus ditentukan dengan jelas:

* restrict,
* cascade,
* atau set null,

sesuai kebutuhan bisnis dan dengan persetujuan struktur final.

---

## 9.6.21 Soft Launching Database Scope

Untuk tahap soft launching, tabel inti yang wajib aktif adalah:

* `DX_LMS_M_Positions`
* `DX_LMS_M_Golongan`
* `DX_LMS_M_Departments`
* `DX_LMS_M_Divisions`
* `DX_LMS_M_Sections`
* `DX_LMS_M_Employees`
* `DX_LMS_M_Courses`
* `DX_LMS_M_Course_Targets`
* `DX_LMS_M_Course_Sessions`
* `DX_LMS_M_Course_Materials`
* `DX_LMS_M_Quizzes`
* `DX_LMS_M_Quiz_Questions`
* `DX_LMS_M_Quiz_Choices`
* `DX_LMS_T_Course_Enrollments`
* `DX_LMS_T_Course_Histories`
* `DX_LMS_T_Material_Progress`
* `DX_LMS_T_Quiz_Attempts`
* `DX_LMS_T_Quiz_Answers`
* `DX_LMS_T_Activity_Logs`
* `DX_LMS_W_Staging_Import`

Tabel future module boleh tetap disiapkan secara konseptual, tetapi tidak wajib diimplementasikan terlebih dahulu.

---

## 9.6.22 Future Extensibility Rule

Database harus siap untuk perluasan fitur berikut tanpa perubahan besar pada struktur inti:

* Forum
* Attendance
* Certificate
* Notification
* Learning Analytics
* Export Report
* Learning Path
* Admin Workflow Enhancement

AI wajib menjaga kompatibilitas desain database dengan kebutuhan pengembangan jangka panjang.

---

# 10. ARCHITECTURE RULES

## 10.1 Application Architecture

Gunakan arsitektur modular berbasis fitur.
Setiap domain besar harus dipisah ke dalam modul yang jelas.

## 10.2 Separation of Concerns

Pisahkan:

* Controller
* Service
* Repository
* View
* Helper
* Middleware

## 10.3 Reusability

Gunakan komponen bersama untuk:

* layout,
* form,
* alert,
* table,
* card,
* navigation,
* modal.

## 10.4 Scalability

Arsitektur harus memungkinkan penambahan fitur berikut tanpa merombak total sistem:

* forum,
* attendance,
* certificate,
* notification,
* admin import,
* analytics.

---

# 11. QUALITY RULES

## 11.1 Code Quality

* Gunakan penamaan yang konsisten.
* Gunakan komentar yang jelas pada fungsi penting.
* Jangan menulis logic yang berulang jika bisa dipecah menjadi helper/service.

## 11.2 Maintainability

* Jangan menyatukan semua logic dalam satu file.
* Setiap modul harus mudah dicari dan dipahami.
* Struktur folder harus konsisten.

## 11.3 Debuggability

* Setiap error harus mudah dilacak.
* Logging diperlukan untuk proses penting.
* Jangan menelan error secara diam-diam.

## 11.4 Security

* Password harus di-hash.
* Session harus aman.
* Role access harus dibatasi.
* File upload harus divalidasi.
* Input pengguna harus disanitasi.

---

# 12. FULL FOLDER STRUCTURE

```text
DENSO-LMS/
│
├── app/
│   ├── Core/
│   │   ├── Application.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   ├── Auth.php
│   │   ├── Validation.php
│   │   ├── Upload.php
│   │   └── PdfGenerator.php
│   │
│   ├── Helpers/
│   │   ├── auth_helper.php
│   │   ├── date_helper.php
│   │   ├── file_helper.php
│   │   ├── string_helper.php
│   │   ├── upload_helper.php
│   │   └── response_helper.php
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── GuestMiddleware.php
│   │   ├── TeacherMiddleware.php
│   │   ├── StudentMiddleware.php
│   │   └── AdminMiddleware.php
│   │
│   ├── Repositories/
│   │   ├── EmployeeRepository.php
│   │   ├── CourseRepository.php
│   │   ├── SessionRepository.php
│   │   ├── MaterialRepository.php
│   │   ├── QuizRepository.php
│   │   ├── AssessmentRepository.php
│   │   ├── ProgressRepository.php
│   │   └── CertificateRepository.php
│   │
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── CourseService.php
│   │   ├── MaterialService.php
│   │   ├── QuizService.php
│   │   ├── AssessmentService.php
│   │   ├── ProgressService.php
│   │   ├── ReportService.php
│   │   └── CertificateService.php
│   │
│   └── Modules/
│       ├── Auth/
│       │   ├── Controllers/
│       │   │   ├── LoginController.php
│       │   │   └── LogoutController.php
│       │   └── Views/
│       │       └── login.php
│       │
│       ├── Dashboard/
│       │   ├── Controllers/
│       │   │   └── DashboardController.php
│       │   └── Views/
│       │       ├── teacher-dashboard.php
│       │       └── student-dashboard.php
│       │
│       ├── Course/
│       │   ├── Controllers/
│       │   │   └── CourseController.php
│       │   └── Views/
│       │       ├── index.php
│       │       ├── create.php
│       │       ├── edit.php
│       │       └── detail.php
│       │
│       ├── Session/
│       │   ├── Controllers/
│       │   │   └── SessionController.php
│       │   └── Views/
│       │       ├── create.php
│       │       ├── edit.php
│       │       └── detail.php
│       │
│       ├── Material/
│       │   ├── Controllers/
│       │   │   └── MaterialController.php
│       │   └── Views/
│       │       ├── create.php
│       │       ├── edit.php
│       │       ├── detail.php
│       │       └── viewer.php
│       │
│       ├── Quiz/
│       │   ├── Controllers/
│       │   │   └── QuizController.php
│       │   └── Views/
│       │       ├── index.php
│       │       ├── create.php
│       │       ├── question.php
│       │       ├── attempt.php
│       │       └── result.php
│       │
│       ├── Assessment/
│       │   ├── Controllers/
│       │   │   └── AssessmentController.php
│       │   └── Views/
│       │       ├── create.php
│       │       ├── attempt.php
│       │       └── result.php
│       │
│       ├── Progress/
│       │   ├── Controllers/
│       │   │   └── ProgressController.php
│       │   └── Views/
│       │       ├── activity.php
│       │       ├── history.php
│       │       └── report.php
│       │
│       ├── Profile/
│       │   ├── Controllers/
│       │   │   └── ProfileController.php
│       │   └── Views/
│       │       ├── profile.php
│       │       └── change-password.php
│       │
│       ├── Notification/
│       │   ├── Controllers/
│       │   └── Views/
│       │
│       ├── Certificate/
│       │   ├── Controllers/
│       │   └── Views/
│       │
│       ├── Forum/
│       │   ├── Controllers/
│       │   └── Views/
│       │
│       └── Administration/
│           ├── Controllers/
│           └── Views/
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── constants.php
│   ├── mail.php
│   └── paths.php
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   ├── backups/
│   └── scripts/
│
├── docs/
│   ├── PRD.md
│   ├── DESIGN.md
│   ├── USECASE.md
│   ├── FLOWCHART.md
│   ├── ERD.md
│   ├── API.md
│   ├── TESTING.md
│   └── DEPLOYMENT.md
│
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── bootstrap.min.css
│   │   │   ├── datatables.min.css
│   │   │   ├── style.css
│   │   │   └── responsive.css
│   │   ├── js/
│   │   │   ├── bootstrap.bundle.min.js
│   │   │   ├── chart.min.js
│   │   │   ├── datatables.min.js
│   │   │   ├── app.js
│   │   │   └── modules/
│   │   ├── icons/
│   │   │   └── bootstrap-icons/
│   │   ├── fonts/
│   │   ├── images/
│   │   │   ├── logo/
│   │   │   ├── course/
│   │   │   ├── profile/
│   │   │   └── system/
│   │   └── vendors/
│   ├── uploads/
│   │   ├── materials/
│   │   ├── thumbnails/
│   │   ├── profiles/
│   │   └── temporary/
│   └── exports/
│       ├── reports/
│       ├── certificates/
│       └── pdf/
│
├── resources/
│   ├── layouts/
│   │   ├── app.php
│   │   ├── teacher.php
│   │   ├── student.php
│   │   └── admin.php
│   ├── partials/
│   │   ├── navbar.php
│   │   ├── sidebar.php
│   │   ├── footer.php
│   │   ├── breadcrumb.php
│   │   └── modal.php
│   └── components/
│       ├── buttons/
│       ├── cards/
│       ├── forms/
│       ├── tables/
│       ├── charts/
│       └── alerts/
│
├── routes/
│   ├── web.php
│   ├── teacher.php
│   ├── student.php
│   └── admin.php
│
├── storage/
│   ├── logs/
│   ├── cache/
│   ├── sessions/
│   ├── temp/
│   ├── reports/
│   ├── certificates/
│   └── backups/
│
├── tests/
│   ├── Unit/
│   ├── Integration/
│   └── Feature/
│
├── vendor/
├── composer.json
├── composer.lock
├── .env
├── .gitignore
├── README.md
└── LICENSE
```

---

# 13. IMPLEMENTATION PRIORITY ORDER

When developing, prioritize in this order:

1. Authentication
2. Base layout
3. Teacher dashboard
4. Student dashboard
5. Course module
6. Session module
7. Material module
8. Quiz module
9. Assessment module
10. Progress module
11. Profile module
12. Future modules

---

# 14. AI AGENT INSTRUCTIONS

## 14.1 General Behavior

AI Antigravity must:

* respect the agreed project scope,
* avoid introducing unnecessary frameworks,
* maintain consistency with the design system,
* preserve offline-first architecture,
* prioritize maintainability.

## 14.2 Before Making Changes

Always verify:

* file structure,
* module ownership,
* role permissions,
* naming conventions,
* consistency with PRD, ERD, and design system.

## 14.3 When Generating Code

AI must:

* keep code modular,
* avoid monolithic files,
* separate business logic from view logic,
* use reusable components,
* preserve naming conventions,
* avoid breaking existing offline compatibility.

## 14.4 When Modifying UI

AI must:

* preserve corporate-modern aesthetic,
* keep spacing consistent,
* preserve mobile-first behavior,
* use existing design tokens,
* avoid visual clutter.

## 14.5 When Modifying Database Logic

AI must:

* follow master/transaction/work categorization,
* preserve table prefixes,
* avoid schema inconsistencies,
* maintain forward compatibility for future modules.

---

# 15. DOCUMENTATION RULES

Every major change should be reflected in:

* PRD.md
* DESIGN.md
* ERD.md
* USECASE.md
* FLOWCHART.md
* TESTING.md
* DEPLOYMENT.md

All significant functions, classes, and modules must be documented using clear inline comments or PHPDoc blocks.

---

# 16. FINAL NOTES

This project is not a generic school LMS.
It is a corporate internal learning platform for PT Denso Indonesia.

Therefore, all decisions must prioritize:

* enterprise clarity,
* internal usability,
* stable offline operation,
* clean modular architecture,
* and future scalability.

Any implementation that violates the above principles should be treated as non-standard unless explicitly approved.
:::


