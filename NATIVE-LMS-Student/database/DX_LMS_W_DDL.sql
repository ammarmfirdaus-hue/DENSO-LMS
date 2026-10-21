-- ============================================================
-- DX_LMS — Database Definition Language (DDL)
-- Learning Management System
-- Standar: Gambar Referensi + AGENTS.md
-- DBMS: Microsoft SQL Server
-- ============================================================

-- Membuat Database
-- CREATE DATABASE DX_LMS;
-- GO
-- USE DX_LMS;
-- GO

-- ============================================================
-- 1. MASTER TABLES (M)
-- Data jarang berubah (user, course, dst.)
-- Prefix: DX_LMS_M_
-- ============================================================

-- ------------------------------------------------------------
-- 1.1 DX_LMS_M_Employees — Tabel Master Karyawan
-- Sumber: Gambar Referensi "Employee Master DB Scheme"
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Employees (
    npk             VARCHAR(50)     NOT NULL,   -- NPK Karyawan (bisa alphanumeric) - PRIMARY KEY
    fullname        VARCHAR(150)    NOT NULL,   -- String nama karyawan
    email           VARCHAR(150)    NOT NULL,   -- String email karyawan
    password        VARCHAR(255)    NOT NULL,   -- Password hash karyawan (bcrypt)
    position_id     BIGINT          NOT NULL,   -- Relasi ke Jabatan (Operator, Staff, Leader, SPV)
    golongan_id     BIGINT          NOT NULL,   -- Relasi ke Golongan (I, II, III, IV)
    department_id   BIGINT          NOT NULL,   -- Relasi ke Departemen
    division_id     BIGINT          NOT NULL,   -- Relasi ke Divisi
    section_id      BIGINT          NOT NULL,   -- Relasi ke Seksi
    status          VARCHAR(50)     NOT NULL DEFAULT 'Active', -- Active / Inactive
    role            VARCHAR(20)     NOT NULL DEFAULT 'student', -- Role: admin, teacher, student
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Employees PRIMARY KEY (npk)
);

CREATE INDEX IX_DX_LMS_M_Employees_email ON DX_LMS_M_Employees (email);

-- ------------------------------------------------------------
-- 1.2 DX_LMS_M_Positions — Tabel Master Jabatan
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Positions (
    position_id     BIGINT IDENTITY(1,1) NOT NULL,
    position_name   VARCHAR(100)    NOT NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Positions PRIMARY KEY (position_id)
);

-- ------------------------------------------------------------
-- 1.3 DX_LMS_M_Golongan — Tabel Master Golongan
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Golongan (
    golongan_id     BIGINT IDENTITY(1,1) NOT NULL,
    golongan_name   VARCHAR(50)     NOT NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Golongan PRIMARY KEY (golongan_id)
);

-- ------------------------------------------------------------
-- 1.4 DX_LMS_M_Departments — Tabel Master Departemen
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Departments (
    department_id   BIGINT IDENTITY(1,1) NOT NULL,
    department_name VARCHAR(150)    NOT NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Departments PRIMARY KEY (department_id)
);

-- ------------------------------------------------------------
-- 1.5 DX_LMS_M_Divisions — Tabel Master Divisi
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Divisions (
    division_id     BIGINT IDENTITY(1,1) NOT NULL,
    division_name   VARCHAR(150)    NOT NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Divisions PRIMARY KEY (division_id)
);

-- ------------------------------------------------------------
-- 1.6 DX_LMS_M_Sections — Tabel Master Seksi
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Sections (
    section_id      BIGINT IDENTITY(1,1) NOT NULL,
    section_name    VARCHAR(150)    NOT NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Sections PRIMARY KEY (section_id)
);

-- Tambahkan FK ke Employees setelah tabel master referensi dibuat
ALTER TABLE DX_LMS_M_Employees ADD CONSTRAINT FK_DX_LMS_M_Employees_Position FOREIGN KEY (position_id) REFERENCES DX_LMS_M_Positions(position_id);
ALTER TABLE DX_LMS_M_Employees ADD CONSTRAINT FK_DX_LMS_M_Employees_Golongan FOREIGN KEY (golongan_id) REFERENCES DX_LMS_M_Golongan(golongan_id);
ALTER TABLE DX_LMS_M_Employees ADD CONSTRAINT FK_DX_LMS_M_Employees_Department FOREIGN KEY (department_id) REFERENCES DX_LMS_M_Departments(department_id);
ALTER TABLE DX_LMS_M_Employees ADD CONSTRAINT FK_DX_LMS_M_Employees_Division FOREIGN KEY (division_id) REFERENCES DX_LMS_M_Divisions(division_id);
ALTER TABLE DX_LMS_M_Employees ADD CONSTRAINT FK_DX_LMS_M_Employees_Section FOREIGN KEY (section_id) REFERENCES DX_LMS_M_Sections(section_id);

-- ------------------------------------------------------------
-- 1.7 DX_LMS_M_Courses — Tabel Master Course
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Courses (
    course_id       BIGINT IDENTITY(1,1) NOT NULL,
    course_code     VARCHAR(20)     NOT NULL,       -- Kode unik course (CRS001)
    title           NVARCHAR(255)   NOT NULL,       -- Judul course
    description     NVARCHAR(MAX)   NULL,           -- Deskripsi course
    category        VARCHAR(50)     NOT NULL DEFAULT 'Wajib', -- Kategori: Wajib / Opsional
    thumbnail       NVARCHAR(500)   NULL,           -- URL gambar thumbnail
    modules_count   INT             NOT NULL DEFAULT 0,
    course_status   VARCHAR(20)     NOT NULL DEFAULT 'Active', -- Active / Inactive
    created_by      VARCHAR(50)     NULL,           -- FK ke Employees (NPK)
    updated_by      VARCHAR(50)     NULL,           -- FK ke Employees (NPK)
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Courses PRIMARY KEY (course_id),
    CONSTRAINT UQ_DX_LMS_M_Courses_code UNIQUE (course_code),
    CONSTRAINT FK_DX_LMS_M_Courses_created_by FOREIGN KEY (created_by) REFERENCES DX_LMS_M_Employees(npk),
    CONSTRAINT FK_DX_LMS_M_Courses_updated_by FOREIGN KEY (updated_by) REFERENCES DX_LMS_M_Employees(npk)
);

CREATE INDEX IX_DX_LMS_M_Courses_code ON DX_LMS_M_Courses (course_code);
CREATE INDEX IX_DX_LMS_M_Courses_status ON DX_LMS_M_Courses (course_status);

-- ------------------------------------------------------------
-- 1.8 DX_LMS_M_Course_Sessions — Tabel Master Sesi Course
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Course_Sessions (
    session_id      BIGINT IDENTITY(1,1) NOT NULL,
    course_id       BIGINT          NOT NULL,
    title           NVARCHAR(255)   NOT NULL,
    sort_order      INT             NOT NULL DEFAULT 0,
    session_status  VARCHAR(20)     NOT NULL DEFAULT 'Active',
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Course_Sessions PRIMARY KEY (session_id),
    CONSTRAINT FK_DX_LMS_M_Course_Sessions_course FOREIGN KEY (course_id) REFERENCES DX_LMS_M_Courses(course_id)
);

CREATE INDEX IX_DX_LMS_M_Course_Sessions_course ON DX_LMS_M_Course_Sessions (course_id);

-- ------------------------------------------------------------
-- 1.9 DX_LMS_M_Course_Materials — Tabel Master Materi
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Course_Materials (
    material_id     BIGINT IDENTITY(1,1) NOT NULL,
    session_id      BIGINT          NOT NULL,
    title           NVARCHAR(255)   NOT NULL,
    material_type   VARCHAR(20)     NOT NULL DEFAULT 'text', -- text, video, pdf, image
    content         NVARCHAR(MAX)   NULL,
    file_path       NVARCHAR(500)   NULL,
    duration        VARCHAR(50)     NULL,       -- Durasi estimasi (5 min, 10 halaman)
    sort_order      INT             NOT NULL DEFAULT 0,
    material_status VARCHAR(20)     NOT NULL DEFAULT 'Active',
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Course_Materials PRIMARY KEY (material_id),
    CONSTRAINT FK_DX_LMS_M_Course_Materials_session FOREIGN KEY (session_id) REFERENCES DX_LMS_M_Course_Sessions(session_id)
);

CREATE INDEX IX_DX_LMS_M_Course_Materials_session ON DX_LMS_M_Course_Materials (session_id);

-- ------------------------------------------------------------
-- 1.10 DX_LMS_M_Quizzes — Tabel Master Quiz
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Quizzes (
    quiz_id         BIGINT IDENTITY(1,1) NOT NULL,
    course_id       BIGINT          NOT NULL,
    session_id      BIGINT          NULL,           -- NULL untuk final_assessment
    quiz_type       VARCHAR(30)     NOT NULL,       -- pre_test, post_test, quiz, final_assessment
    title           NVARCHAR(255)   NOT NULL,
    passing_score   INT             NOT NULL DEFAULT 75,
    max_attempts    INT             NOT NULL DEFAULT 3,
    quiz_status     VARCHAR(20)     NOT NULL DEFAULT 'Active',
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Quizzes PRIMARY KEY (quiz_id),
    CONSTRAINT FK_DX_LMS_M_Quizzes_course FOREIGN KEY (course_id) REFERENCES DX_LMS_M_Courses(course_id),
    CONSTRAINT FK_DX_LMS_M_Quizzes_session FOREIGN KEY (session_id) REFERENCES DX_LMS_M_Course_Sessions(session_id)
);

CREATE INDEX IX_DX_LMS_M_Quizzes_course ON DX_LMS_M_Quizzes (course_id);
CREATE INDEX IX_DX_LMS_M_Quizzes_session ON DX_LMS_M_Quizzes (session_id);
CREATE INDEX IX_DX_LMS_M_Quizzes_type ON DX_LMS_M_Quizzes (quiz_type);

-- ------------------------------------------------------------
-- 1.11 DX_LMS_M_Quiz_Questions — Tabel Master Pertanyaan Quiz
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Quiz_Questions (
    question_id     BIGINT IDENTITY(1,1) NOT NULL,
    quiz_id         BIGINT          NOT NULL,
    question_text   NVARCHAR(MAX)   NOT NULL,
    sort_order      INT             NOT NULL DEFAULT 0,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Quiz_Questions PRIMARY KEY (question_id),
    CONSTRAINT FK_DX_LMS_M_Quiz_Questions_quiz FOREIGN KEY (quiz_id) REFERENCES DX_LMS_M_Quizzes(quiz_id)
);

CREATE INDEX IX_DX_LMS_M_Quiz_Questions_quiz ON DX_LMS_M_Quiz_Questions (quiz_id);

-- ------------------------------------------------------------
-- 1.12 DX_LMS_M_Quiz_Choices — Tabel Master Pilihan Jawaban
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Quiz_Choices (
    choice_id       BIGINT IDENTITY(1,1) NOT NULL,
    question_id     BIGINT          NOT NULL,
    choice_text     NVARCHAR(MAX)   NOT NULL,
    is_correct      BIT             NOT NULL DEFAULT 0,
    sort_order      INT             NOT NULL DEFAULT 0,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Quiz_Choices PRIMARY KEY (choice_id),
    CONSTRAINT FK_DX_LMS_M_Quiz_Choices_question FOREIGN KEY (question_id) REFERENCES DX_LMS_M_Quiz_Questions(question_id)
);

CREATE INDEX IX_DX_LMS_M_Quiz_Choices_question ON DX_LMS_M_Quiz_Choices (question_id);

-- ------------------------------------------------------------
-- 1.13 DX_LMS_M_Course_Targets — Target Jabatan/Golongan Course
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_M_Course_Targets (
    target_id       BIGINT IDENTITY(1,1) NOT NULL,
    course_id       BIGINT          NOT NULL,
    position_id     BIGINT          NULL,
    golongan_id     BIGINT          NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_M_Course_Targets PRIMARY KEY (target_id),
    CONSTRAINT FK_DX_LMS_M_Course_Targets_course FOREIGN KEY (course_id) REFERENCES DX_LMS_M_Courses(course_id),
    CONSTRAINT FK_DX_LMS_M_Course_Targets_position FOREIGN KEY (position_id) REFERENCES DX_LMS_M_Positions(position_id),
    CONSTRAINT FK_DX_LMS_M_Course_Targets_golongan FOREIGN KEY (golongan_id) REFERENCES DX_LMS_M_Golongan(golongan_id)
);

CREATE INDEX IX_DX_LMS_M_Course_Targets_course ON DX_LMS_M_Course_Targets (course_id);

-- ============================================================
-- 2. TRANSACTION TABLES (T)
-- Data sering berubah (course progress, enrollment, dll.)
-- Prefix: DX_LMS_T_
-- ============================================================

-- ------------------------------------------------------------
-- 2.1 DX_LMS_T_Course_Enrollments — Pendaftaran Student ke Course
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_T_Course_Enrollments (
    enrollment_id   BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    course_id       BIGINT          NOT NULL,
    enrollment_date DATETIME2       NOT NULL DEFAULT GETDATE(),
    enrollment_status VARCHAR(30)   NOT NULL DEFAULT 'active', -- active, completed, dropped
    progress        INT             NOT NULL DEFAULT 0,     -- 0-100
    completed_at    DATETIME2       NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_T_Course_Enrollments PRIMARY KEY (enrollment_id),
    CONSTRAINT FK_DX_LMS_T_Course_Enrollments_employee FOREIGN KEY (employee_npk) REFERENCES DX_LMS_M_Employees(npk),
    CONSTRAINT FK_DX_LMS_T_Course_Enrollments_course FOREIGN KEY (course_id) REFERENCES DX_LMS_M_Courses(course_id)
);

CREATE INDEX IX_DX_LMS_T_Course_Enrollments_employee ON DX_LMS_T_Course_Enrollments (employee_npk);
CREATE INDEX IX_DX_LMS_T_Course_Enrollments_course ON DX_LMS_T_Course_Enrollments (course_id);

-- ------------------------------------------------------------
-- 2.2 DX_LMS_T_Course_Histories — Histori Penyelesaian Course
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_T_Course_Histories (
    history_id      BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    course_id       BIGINT          NOT NULL,
    final_score     DECIMAL(5,2)    NULL,
    completion_date DATETIME2       NOT NULL DEFAULT GETDATE(),
    certificate_url NVARCHAR(500)   NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_T_Course_Histories PRIMARY KEY (history_id),
    CONSTRAINT FK_DX_LMS_T_Course_Histories_employee FOREIGN KEY (employee_npk) REFERENCES DX_LMS_M_Employees(npk),
    CONSTRAINT FK_DX_LMS_T_Course_Histories_course FOREIGN KEY (course_id) REFERENCES DX_LMS_M_Courses(course_id)
);

CREATE INDEX IX_DX_LMS_T_Course_Histories_employee ON DX_LMS_T_Course_Histories (employee_npk);

-- ------------------------------------------------------------
-- 2.3 DX_LMS_T_Material_Progress — Progress Materi per Student
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_T_Material_Progress (
    progress_id     BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    material_id     BIGINT          NOT NULL,
    progress_status VARCHAR(20)     NOT NULL DEFAULT 'not_started', -- not_started, in_progress, completed
    completed_at    DATETIME2       NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_T_Material_Progress PRIMARY KEY (progress_id),
    CONSTRAINT FK_DX_LMS_T_Material_Progress_employee FOREIGN KEY (employee_npk) REFERENCES DX_LMS_M_Employees(npk),
    CONSTRAINT FK_DX_LMS_T_Material_Progress_material FOREIGN KEY (material_id) REFERENCES DX_LMS_M_Course_Materials(material_id)
);

CREATE INDEX IX_DX_LMS_T_Material_Progress_employee ON DX_LMS_T_Material_Progress (employee_npk);

-- ------------------------------------------------------------
-- 2.4 DX_LMS_T_Quiz_Attempts — Percobaan Pengerjaan Quiz
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_T_Quiz_Attempts (
    attempt_id      BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    quiz_id         BIGINT          NOT NULL,
    score           DECIMAL(5,2)    NULL,
    attempt_number  INT             NOT NULL DEFAULT 1,
    started_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    completed_at    DATETIME2       NULL,

    CONSTRAINT PK_DX_LMS_T_Quiz_Attempts PRIMARY KEY (attempt_id),
    CONSTRAINT FK_DX_LMS_T_Quiz_Attempts_employee FOREIGN KEY (employee_npk) REFERENCES DX_LMS_M_Employees(npk),
    CONSTRAINT FK_DX_LMS_T_Quiz_Attempts_quiz FOREIGN KEY (quiz_id) REFERENCES DX_LMS_M_Quizzes(quiz_id)
);

CREATE INDEX IX_DX_LMS_T_Quiz_Attempts_employee ON DX_LMS_T_Quiz_Attempts (employee_npk);
CREATE INDEX IX_DX_LMS_T_Quiz_Attempts_quiz ON DX_LMS_T_Quiz_Attempts (quiz_id);

-- ------------------------------------------------------------
-- 2.5 DX_LMS_T_Quiz_Answers — Jawaban Detail per Soal
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_T_Quiz_Answers (
    answer_id       BIGINT IDENTITY(1,1) NOT NULL,
    attempt_id      BIGINT          NOT NULL,
    question_id     BIGINT          NOT NULL,
    selected_choice_id BIGINT       NULL,
    is_correct      BIT             NOT NULL DEFAULT 0,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_T_Quiz_Answers PRIMARY KEY (answer_id),
    CONSTRAINT FK_DX_LMS_T_Quiz_Answers_attempt FOREIGN KEY (attempt_id) REFERENCES DX_LMS_T_Quiz_Attempts(attempt_id),
    CONSTRAINT FK_DX_LMS_T_Quiz_Answers_question FOREIGN KEY (question_id) REFERENCES DX_LMS_M_Quiz_Questions(question_id),
    CONSTRAINT FK_DX_LMS_T_Quiz_Answers_choice FOREIGN KEY (selected_choice_id) REFERENCES DX_LMS_M_Quiz_Choices(choice_id)
);

CREATE INDEX IX_DX_LMS_T_Quiz_Answers_attempt ON DX_LMS_T_Quiz_Answers (attempt_id);

-- ------------------------------------------------------------
-- 2.6 DX_LMS_T_Activity_Logs — Log Aktivitas User
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_T_Activity_Logs (
    log_id          BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    activity_type   VARCHAR(50)     NOT NULL,   -- login, view_course, start_quiz, complete_material, dll.
    reference_id    VARCHAR(50)     NULL,       -- ID objek terkait (course_id, quiz_id, dll.)
    description     NVARCHAR(500)   NULL,
    ip_address      VARCHAR(45)     NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_T_Activity_Logs PRIMARY KEY (log_id),
    CONSTRAINT FK_DX_LMS_T_Activity_Logs_employee FOREIGN KEY (employee_npk) REFERENCES DX_LMS_M_Employees(npk)
);

CREATE INDEX IX_DX_LMS_T_Activity_Logs_employee ON DX_LMS_T_Activity_Logs (employee_npk);
CREATE INDEX IX_DX_LMS_T_Activity_Logs_type ON DX_LMS_T_Activity_Logs (activity_type);
CREATE INDEX IX_DX_LMS_T_Activity_Logs_date ON DX_LMS_T_Activity_Logs (created_at);


-- ============================================================
-- 3. WORK / TEMPORARY TABLES (W)
-- Data sementara yang tidak disimpan permanen
-- Prefix: DX_LMS_W_
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 DX_LMS_W_Staging_Import — Staging Data Import
-- ------------------------------------------------------------
CREATE TABLE DX_LMS_W_Staging_Import (
    staging_id      BIGINT IDENTITY(1,1) NOT NULL,
    import_type     VARCHAR(50)     NOT NULL,   -- employee, course, enrollment
    raw_data        NVARCHAR(MAX)   NULL,       -- Data mentah (JSON/CSV row)
    validation_status VARCHAR(20)   NOT NULL DEFAULT 'pending', -- pending, valid, invalid
    error_message   NVARCHAR(500)   NULL,
    imported_by     VARCHAR(50)     NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_DX_LMS_W_Staging_Import PRIMARY KEY (staging_id)
);


-- ============================================================
-- SELESAI — DDL DX_LMS
-- ============================================================
