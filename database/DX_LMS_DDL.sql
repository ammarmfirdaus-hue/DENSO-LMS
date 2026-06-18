-- ============================================================
-- DX_LMS — Database Definition Language (DDL)
-- PT Denso Indonesia — Learning Management System
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
-- ============================================================

-- ------------------------------------------------------------
-- 1.1 LMS_M_Employee — Tabel Master Karyawan
-- Sumber: Gambar Referensi "Employee Master DB Scheme"
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Employee (
    npk             VARCHAR(50)     NOT NULL,   -- PK, Min. 7 Digit, bisa angka/huruf (AD1234567, 7654321)
    fullname        VARCHAR(150)    NOT NULL,   -- String nama karyawan
    golongan        VARCHAR(50)     NOT NULL,   -- Bisa angka/huruf, format: [huruf]–[angka] (II-2)
    bu_code         INT             NOT NULL,   -- Business Unit Code
    section         VARCHAR(150)    NOT NULL,   -- String nama seksi
    department      VARCHAR(150)    NOT NULL,   -- String nama departemen
    division        VARCHAR(150)    NOT NULL,   -- String nama divisi
    directorate     VARCHAR(150)    NOT NULL,   -- String nama direktorat
    email           VARCHAR(150)    NOT NULL,   -- String email karyawan
    status          VARCHAR(50)     NOT NULL,   -- String status karyawan (Active / Inactive)
    password        VARCHAR(255)    NOT NULL,   -- Password hash karyawan (bcrypt)
    role            VARCHAR(20)     NOT NULL DEFAULT 'student', -- Role: admin, teacher, student
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Employee PRIMARY KEY (npk)
);

-- Index untuk pencarian umum
CREATE INDEX IX_LMS_M_Employee_email ON LMS_M_Employee (email);
CREATE INDEX IX_LMS_M_Employee_status ON LMS_M_Employee (status);
CREATE INDEX IX_LMS_M_Employee_bu_code ON LMS_M_Employee (bu_code);
CREATE INDEX IX_LMS_M_Employee_department ON LMS_M_Employee (department);

-- ------------------------------------------------------------
-- 1.2 LMS_M_Course — Tabel Master Course
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Course (
    course_id       BIGINT IDENTITY(1,1) NOT NULL,
    course_code     VARCHAR(20)     NOT NULL,       -- Kode unik course (CRS001)
    title           NVARCHAR(255)   NOT NULL,       -- Judul course
    description     NVARCHAR(MAX)   NULL,           -- Deskripsi course
    category        VARCHAR(50)     NOT NULL DEFAULT 'Wajib', -- Kategori: Wajib / Opsional
    thumbnail       NVARCHAR(500)   NULL,           -- URL gambar thumbnail
    modules_count   INT             NOT NULL DEFAULT 0,
    course_status   VARCHAR(20)     NOT NULL DEFAULT 'Active', -- Active / Inactive
    created_by      VARCHAR(50)     NULL,           -- NPK pembuat (FK ke LMS_M_Employee)
    updated_by      VARCHAR(50)     NULL,           -- NPK pengubah (FK ke LMS_M_Employee)
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Course PRIMARY KEY (course_id),
    CONSTRAINT UQ_LMS_M_Course_code UNIQUE (course_code),
    CONSTRAINT FK_LMS_M_Course_created_by FOREIGN KEY (created_by) REFERENCES LMS_M_Employee(npk),
    CONSTRAINT FK_LMS_M_Course_updated_by FOREIGN KEY (updated_by) REFERENCES LMS_M_Employee(npk)
);

CREATE INDEX IX_LMS_M_Course_code ON LMS_M_Course (course_code);
CREATE INDEX IX_LMS_M_Course_status ON LMS_M_Course (course_status);

-- ------------------------------------------------------------
-- 1.3 LMS_M_Course_Session — Tabel Master Sesi Course
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Course_Session (
    session_id      BIGINT IDENTITY(1,1) NOT NULL,
    course_id       BIGINT          NOT NULL,
    title           NVARCHAR(255)   NOT NULL,
    sort_order      INT             NOT NULL DEFAULT 0,
    session_status  VARCHAR(20)     NOT NULL DEFAULT 'Active',
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Course_Session PRIMARY KEY (session_id),
    CONSTRAINT FK_LMS_M_Course_Session_course FOREIGN KEY (course_id) REFERENCES LMS_M_Course(course_id)
);

CREATE INDEX IX_LMS_M_Course_Session_course ON LMS_M_Course_Session (course_id);

-- ------------------------------------------------------------
-- 1.4 LMS_M_Course_Material — Tabel Master Materi
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Course_Material (
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

    CONSTRAINT PK_LMS_M_Course_Material PRIMARY KEY (material_id),
    CONSTRAINT FK_LMS_M_Course_Material_session FOREIGN KEY (session_id) REFERENCES LMS_M_Course_Session(session_id)
);

CREATE INDEX IX_LMS_M_Course_Material_session ON LMS_M_Course_Material (session_id);

-- ------------------------------------------------------------
-- 1.5 LMS_M_Quiz — Tabel Master Quiz
-- quiz_type: 'pre_test', 'post_test', 'quiz', 'final_assessment'
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Quiz (
    quiz_id         BIGINT IDENTITY(1,1) NOT NULL,
    course_id       BIGINT          NOT NULL,
    session_id      BIGINT          NULL,           -- NULL untuk quiz course-level dan final assessment
    quiz_type       VARCHAR(30)     NOT NULL,       -- pre_test, post_test, quiz, final_assessment
    title           NVARCHAR(255)   NOT NULL,
    passing_score   INT             NOT NULL DEFAULT 75,
    max_attempts    INT             NOT NULL DEFAULT 3,
    quiz_status     VARCHAR(20)     NOT NULL DEFAULT 'Active',
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Quiz PRIMARY KEY (quiz_id),
    CONSTRAINT FK_LMS_M_Quiz_course FOREIGN KEY (course_id) REFERENCES LMS_M_Course(course_id),
    CONSTRAINT FK_LMS_M_Quiz_session FOREIGN KEY (session_id) REFERENCES LMS_M_Course_Session(session_id)
);

CREATE INDEX IX_LMS_M_Quiz_course ON LMS_M_Quiz (course_id);
CREATE INDEX IX_LMS_M_Quiz_session ON LMS_M_Quiz (session_id);
CREATE INDEX IX_LMS_M_Quiz_type ON LMS_M_Quiz (quiz_type);

-- ------------------------------------------------------------
-- 1.6 LMS_M_Quiz_Question — Tabel Master Pertanyaan Quiz
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Quiz_Question (
    question_id     BIGINT IDENTITY(1,1) NOT NULL,
    quiz_id         BIGINT          NOT NULL,
    question_text   NVARCHAR(MAX)   NOT NULL,
    sort_order      INT             NOT NULL DEFAULT 0,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Quiz_Question PRIMARY KEY (question_id),
    CONSTRAINT FK_LMS_M_Quiz_Question_quiz FOREIGN KEY (quiz_id) REFERENCES LMS_M_Quiz(quiz_id)
);

CREATE INDEX IX_LMS_M_Quiz_Question_quiz ON LMS_M_Quiz_Question (quiz_id);

-- ------------------------------------------------------------
-- 1.7 LMS_M_Quiz_Choice — Tabel Master Pilihan Jawaban
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Quiz_Choice (
    choice_id       BIGINT IDENTITY(1,1) NOT NULL,
    question_id     BIGINT          NOT NULL,
    choice_text     NVARCHAR(MAX)   NOT NULL,
    is_correct      BIT             NOT NULL DEFAULT 0,
    sort_order      INT             NOT NULL DEFAULT 0,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Quiz_Choice PRIMARY KEY (choice_id),
    CONSTRAINT FK_LMS_M_Quiz_Choice_question FOREIGN KEY (question_id) REFERENCES LMS_M_Quiz_Question(question_id)
);

CREATE INDEX IX_LMS_M_Quiz_Choice_question ON LMS_M_Quiz_Choice (question_id);

-- ------------------------------------------------------------
-- 1.8 LMS_M_Course_Target — Target Jabatan/Golongan Course
-- ------------------------------------------------------------
CREATE TABLE LMS_M_Course_Target (
    target_id       BIGINT IDENTITY(1,1) NOT NULL,
    course_id       BIGINT          NOT NULL,
    target_golongan VARCHAR(50)     NULL,       -- Golongan target (II-2, III-1, dst.)
    target_section  VARCHAR(150)    NULL,       -- Seksi target
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_M_Course_Target PRIMARY KEY (target_id),
    CONSTRAINT FK_LMS_M_Course_Target_course FOREIGN KEY (course_id) REFERENCES LMS_M_Course(course_id)
);

CREATE INDEX IX_LMS_M_Course_Target_course ON LMS_M_Course_Target (course_id);


-- ============================================================
-- 2. TRANSACTION TABLES (T)
-- Data sering berubah (course progress, enrollment, dll.)
-- ============================================================

-- ------------------------------------------------------------
-- 2.1 LMS_T_Course_Enrollment — Pendaftaran Student ke Course
-- ------------------------------------------------------------
CREATE TABLE LMS_T_Course_Enrollment (
    enrollment_id   BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    course_id       BIGINT          NOT NULL,
    enrollment_date DATETIME2       NOT NULL DEFAULT GETDATE(),
    enrollment_status VARCHAR(30)   NOT NULL DEFAULT 'active', -- active, completed, dropped
    progress        INT             NOT NULL DEFAULT 0,     -- 0-100
    completed_at    DATETIME2       NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_T_Course_Enrollment PRIMARY KEY (enrollment_id),
    CONSTRAINT FK_LMS_T_Enrollment_employee FOREIGN KEY (employee_npk) REFERENCES LMS_M_Employee(npk),
    CONSTRAINT FK_LMS_T_Enrollment_course FOREIGN KEY (course_id) REFERENCES LMS_M_Course(course_id)
);

CREATE INDEX IX_LMS_T_Enrollment_employee ON LMS_T_Course_Enrollment (employee_npk);
CREATE INDEX IX_LMS_T_Enrollment_course ON LMS_T_Course_Enrollment (course_id);

-- ------------------------------------------------------------
-- 2.2 LMS_T_Course_History — Histori Penyelesaian Course
-- Sumber: Gambar Referensi "LMS_T_Course_History"
-- ------------------------------------------------------------
CREATE TABLE LMS_T_Course_History (
    history_id      BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    course_id       BIGINT          NOT NULL,
    final_score     DECIMAL(5,2)    NULL,
    completion_date DATETIME2       NOT NULL DEFAULT GETDATE(),
    certificate_url NVARCHAR(500)   NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_T_Course_History PRIMARY KEY (history_id),
    CONSTRAINT FK_LMS_T_History_employee FOREIGN KEY (employee_npk) REFERENCES LMS_M_Employee(npk),
    CONSTRAINT FK_LMS_T_History_course FOREIGN KEY (course_id) REFERENCES LMS_M_Course(course_id)
);

CREATE INDEX IX_LMS_T_History_employee ON LMS_T_Course_History (employee_npk);

-- ------------------------------------------------------------
-- 2.3 LMS_T_Material_Progress — Progress Materi per Student
-- ------------------------------------------------------------
CREATE TABLE LMS_T_Material_Progress (
    progress_id     BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    material_id     BIGINT          NOT NULL,
    progress_status VARCHAR(20)     NOT NULL DEFAULT 'not_started', -- not_started, in_progress, completed
    completed_at    DATETIME2       NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    updated_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_T_Material_Progress PRIMARY KEY (progress_id),
    CONSTRAINT FK_LMS_T_MatProgress_employee FOREIGN KEY (employee_npk) REFERENCES LMS_M_Employee(npk),
    CONSTRAINT FK_LMS_T_MatProgress_material FOREIGN KEY (material_id) REFERENCES LMS_M_Course_Material(material_id)
);

CREATE INDEX IX_LMS_T_MatProgress_employee ON LMS_T_Material_Progress (employee_npk);

-- ------------------------------------------------------------
-- 2.4 LMS_T_Quiz_Attempt — Percobaan Pengerjaan Quiz
-- ------------------------------------------------------------
CREATE TABLE LMS_T_Quiz_Attempt (
    attempt_id      BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    quiz_id         BIGINT          NOT NULL,
    score           DECIMAL(5,2)    NULL,
    attempt_number  INT             NOT NULL DEFAULT 1,
    started_at      DATETIME2       NOT NULL DEFAULT GETDATE(),
    completed_at    DATETIME2       NULL,

    CONSTRAINT PK_LMS_T_Quiz_Attempt PRIMARY KEY (attempt_id),
    CONSTRAINT FK_LMS_T_QuizAttempt_employee FOREIGN KEY (employee_npk) REFERENCES LMS_M_Employee(npk),
    CONSTRAINT FK_LMS_T_QuizAttempt_quiz FOREIGN KEY (quiz_id) REFERENCES LMS_M_Quiz(quiz_id)
);

CREATE INDEX IX_LMS_T_QuizAttempt_employee ON LMS_T_Quiz_Attempt (employee_npk);
CREATE INDEX IX_LMS_T_QuizAttempt_quiz ON LMS_T_Quiz_Attempt (quiz_id);

-- ------------------------------------------------------------
-- 2.5 LMS_T_Quiz_Answer — Jawaban Detail per Soal
-- ------------------------------------------------------------
CREATE TABLE LMS_T_Quiz_Answer (
    answer_id       BIGINT IDENTITY(1,1) NOT NULL,
    attempt_id      BIGINT          NOT NULL,
    question_id     BIGINT          NOT NULL,
    selected_choice_id BIGINT       NULL,
    is_correct      BIT             NOT NULL DEFAULT 0,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_T_Quiz_Answer PRIMARY KEY (answer_id),
    CONSTRAINT FK_LMS_T_QuizAnswer_attempt FOREIGN KEY (attempt_id) REFERENCES LMS_T_Quiz_Attempt(attempt_id),
    CONSTRAINT FK_LMS_T_QuizAnswer_question FOREIGN KEY (question_id) REFERENCES LMS_M_Quiz_Question(question_id),
    CONSTRAINT FK_LMS_T_QuizAnswer_choice FOREIGN KEY (selected_choice_id) REFERENCES LMS_M_Quiz_Choice(choice_id)
);

CREATE INDEX IX_LMS_T_QuizAnswer_attempt ON LMS_T_Quiz_Answer (attempt_id);

-- ------------------------------------------------------------
-- 2.6 LMS_T_Activity_Log — Log Aktivitas User
-- ------------------------------------------------------------
CREATE TABLE LMS_T_Activity_Log (
    log_id          BIGINT IDENTITY(1,1) NOT NULL,
    employee_npk    VARCHAR(50)     NOT NULL,
    activity_type   VARCHAR(50)     NOT NULL,   -- login, view_course, start_quiz, complete_material, dll.
    reference_id    VARCHAR(50)     NULL,       -- ID objek terkait (course_id, quiz_id, dll.)
    description     NVARCHAR(500)   NULL,
    ip_address      VARCHAR(45)     NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_T_Activity_Log PRIMARY KEY (log_id),
    CONSTRAINT FK_LMS_T_ActivityLog_employee FOREIGN KEY (employee_npk) REFERENCES LMS_M_Employee(npk)
);

CREATE INDEX IX_LMS_T_ActivityLog_employee ON LMS_T_Activity_Log (employee_npk);
CREATE INDEX IX_LMS_T_ActivityLog_type ON LMS_T_Activity_Log (activity_type);
CREATE INDEX IX_LMS_T_ActivityLog_date ON LMS_T_Activity_Log (created_at);


-- ============================================================
-- 3. WORK / TEMPORARY TABLES (W)
-- Data sementara yang tidak disimpan permanen
-- Sumber: Gambar Referensi "LMS_W_Staging_Import"
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 LMS_W_Staging_Import — Staging Data Import
-- ------------------------------------------------------------
CREATE TABLE LMS_W_Staging_Import (
    staging_id      BIGINT IDENTITY(1,1) NOT NULL,
    import_type     VARCHAR(50)     NOT NULL,   -- employee, course, enrollment
    raw_data        NVARCHAR(MAX)   NULL,       -- Data mentah (JSON/CSV row)
    validation_status VARCHAR(20)   NOT NULL DEFAULT 'pending', -- pending, valid, invalid
    error_message   NVARCHAR(500)   NULL,
    imported_by     VARCHAR(50)     NULL,
    created_at      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_LMS_W_Staging_Import PRIMARY KEY (staging_id)
);


-- ============================================================
-- SELESAI — DDL DX_LMS
-- Total Tabel: 14
--   Master  (M): 8 tabel
--   Transaction (T): 6 tabel
--   Work    (W): 1 tabel
-- ============================================================
