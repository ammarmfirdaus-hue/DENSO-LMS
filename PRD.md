# PRODUCT REQUIREMENTS DOCUMENT (PRD)

# Learning Management System (LMS)

## PT Denso Indonesia

### Soft Launching Version 1.0

---

# 1. DOCUMENT INFORMATION

| Item                 | Description                      |
| -------------------- | -------------------------------- |
| Product Name         | Learning Management System (LMS) |
| Organization         | PT Denso Indonesia               |
| Version              | 1.0 (Soft Launching)             |
| Platform             | Web Based Application            |
| Document Type        | Product Requirements Document    |
| Development Approach | Incremental Development          |
| Target Release       | Soft Launching                   |
| Primary Users        | Teacher, Student                 |

---

# 2. PRODUCT OVERVIEW

Learning Management System (LMS) merupakan aplikasi berbasis web yang digunakan untuk mendukung proses pembelajaran internal PT Denso Indonesia.

Sistem ini dikembangkan untuk menyediakan platform pembelajaran yang terpusat sehingga proses distribusi materi, evaluasi pembelajaran, pemantauan progres peserta, dan pencatatan hasil pembelajaran dapat dilakukan secara lebih efektif, terstruktur, dan terdokumentasi.

Pada tahap Soft Launching, sistem difokuskan pada siklus utama pembelajaran antara Teacher dan Student agar seluruh proses pembelajaran digital dapat berjalan secara end-to-end sebelum pengembangan fitur lanjutan dilakukan.

---

# 3. BUSINESS PROBLEM

Berdasarkan kondisi saat ini, terdapat beberapa kendala yang ditemukan dalam proses pembelajaran internal perusahaan:

1. Materi pembelajaran belum tersimpan dalam satu platform terpusat.
2. Distribusi materi masih dilakukan secara manual.
3. Monitoring progres pembelajaran peserta belum terdokumentasi secara sistematis.
4. Hasil evaluasi pembelajaran sulit dipantau secara real-time.
5. Riwayat pembelajaran peserta belum tersimpan secara terstruktur.
6. Proses pelaporan hasil pembelajaran masih membutuhkan banyak pekerjaan administratif.

---

# 4. PRODUCT GOALS

Sistem LMS dikembangkan untuk mencapai tujuan berikut:

### Goal 1

Menyediakan media pembelajaran digital yang terpusat.

### Goal 2

Mempermudah Teacher dalam mengelola pembelajaran.

### Goal 3

Mempermudah Student dalam mengakses materi pembelajaran.

### Goal 4

Menyediakan mekanisme evaluasi pembelajaran yang terstruktur.

### Goal 5

Menyediakan data progres pembelajaran yang dapat dipantau secara real-time.

### Goal 6

Menyimpan histori pembelajaran secara terpusat.

---

# 5. PROJECT SCOPE

## In Scope (Soft Launching)

### Authentication

* Login
* Logout

### Teacher Features

* Kelola Course
* Kelola Session
* Kelola Material
* Kelola Quiz
* Kelola Final Assessment
* Monitoring Aktivitas Student
* Monitoring Nilai Student
* Monitoring Progress Student
* Kelola Profil

### Student Features

* Melihat Course
* Mengakses Materi
* Mengunduh Materi
* Mengerjakan Quiz
* Mengerjakan Final Assessment
* Melihat Progress Pembelajaran
* Melihat Riwayat Pembelajaran
* Kelola Profil

---

## Out of Scope (Future Release)

* Forum Discussion
* Attendance Management
* Certificate Generation
* Notification Center
* Dashboard Analytics
* Import Employee Data
* Admin Management
* Department Management
* Division Management
* Section Management

---

# 6. USER ROLES

## Teacher

Teacher merupakan pengguna yang bertanggung jawab dalam mengelola proses pembelajaran.

Hak akses Teacher:

* Membuat course
* Mengubah course
* Menghapus course
* Membuat session
* Mengelola materi
* Membuat quiz
* Membuat final assessment
* Melihat aktivitas student
* Melihat hasil pembelajaran student

---

## Student

Student merupakan pengguna yang mengikuti pembelajaran yang tersedia.

Hak akses Student:

* Mengikuti course
* Mempelajari materi
* Mengerjakan quiz
* Mengikuti final assessment
* Melihat progres pembelajaran
* Melihat riwayat pembelajaran

---

# 7. LEARNING FLOW

## Teacher Workflow

Teacher Login

↓

Membuat Course

↓

Menentukan Target Course

↓

Membuat Session

↓

Mengunggah Material

↓

Membuat Quiz

↓

Membuat Final Assessment

↓

Publish Course

↓

Monitoring Student

---

## Student Workflow

Student Login

↓

Melihat Course

↓

Mengakses Material

↓

Menyelesaikan Session

↓

Mengerjakan Quiz

↓

Mengerjakan Final Assessment

↓

Melihat Hasil Pembelajaran

---

# 8. COURSE STRUCTURE

Struktur pembelajaran pada sistem:

Course

↓

Session

↓

Material

↓

Quiz

↓

Final Assessment

---

Contoh:

Course:
Safety Training

Session 1:
Introduction Safety

Material:
Video Safety Introduction

Quiz:
Safety Quiz 1

Session 2:
Safety Equipment

Material:
PPE Guidelines

Quiz:
PPE Quiz

Final Assessment:
Safety Training Final Assessment

---

# 9. TARGET COURSE MECHANISM

Setiap course dapat ditujukan kepada kelompok karyawan tertentu.

Target course ditentukan berdasarkan:

### Position

Contoh:

* Operator
* Staff
* Leader
* Supervisor

### Golongan

Contoh:

Operator

* Golongan 1
* Golongan 2
* Golongan 3

Staff

* Golongan 1
* Golongan 2
* Golongan 3

---

# 10. PROGRESS CALCULATION

Progress pembelajaran dihitung berdasarkan penyelesaian session.

Formula:

Progress (%) =

Jumlah Session Selesai
÷
Total Session

× 100

Contoh:

Total Session = 5

Session Selesai = 3

Progress = 60%

---

# 11. QUIZ SCORING

Nilai Quiz dihitung berdasarkan jumlah jawaban benar.

Formula:

Nilai Quiz =

Jawaban Benar
÷
Jumlah Soal

× 100

---

# 12. FINAL ASSESSMENT SCORING

Final Assessment menggunakan mekanisme yang sama dengan quiz.

Perbedaan:

* Hanya tersedia satu kali per course.
* Menjadi evaluasi akhir pembelajaran.
* Digunakan sebagai indikator kelulusan course.

---

# 13. SUCCESS CRITERIA

Soft Launching dianggap berhasil apabila:

### Teacher

* Dapat membuat course.
* Dapat mengelola materi.
* Dapat membuat quiz.
* Dapat membuat final assessment.
* Dapat melihat aktivitas student.

### Student

* Dapat mengakses materi.
* Dapat mengerjakan quiz.
* Dapat mengerjakan final assessment.
* Dapat melihat progres pembelajaran.

### System

* Menyimpan histori pembelajaran.
* Menyimpan nilai quiz.
* Menyimpan nilai final assessment.
* Menampilkan progres pembelajaran secara akurat.

---

# 14. NON-FUNCTIONAL REQUIREMENTS

### Performance

* Waktu respon halaman maksimal 3 detik.

### Security

* Password disimpan menggunakan hashing.
* Session management aktif.
* Role-based access control.

### Availability

* Sistem dapat berjalan tanpa koneksi internet eksternal.

### Compatibility

* Google Chrome
* Microsoft Edge

### Deployment

* Full Offline Deployment

---

# 15. TECHNOLOGY STACK

Frontend

* HTML5
* CSS3
* Bootstrap 5 Offline
* Vanilla JavaScript

Backend

* PHP Native 8.3

Database

* Microsoft SQL Server

Database Driver

* SQLSRV
* PDO_SQLSRV

Web Server

* Apache HTTP Server

Supporting Libraries

* DataTables Offline
* Chart.js Offline
* DomPDF / mPDF
* Bootstrap 5 Modal
* Bootstrap 5 Toast
* Bootstrap 5 Alert

---

# 16. FUTURE ROADMAP

Version 2.0

* Forum Discussion
* Attendance Management
* Certificate Management
* Notification Center
* Admin Panel
* Import Employee Data
* Learning Analytics Dashboard
* Department Management
* Division Management
* Section Management

END OF DOCUMENT
