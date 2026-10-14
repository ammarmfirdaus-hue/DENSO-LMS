-- =========================================================================================
-- DENSO LEARNING SYSTEM (DLS) — MASTER DATABASE DEFINITION LANGUAGE (DDL)
-- DBMS        : Microsoft SQL Server 2019 / 2022 (SSMS Compatible)
-- Standard    : 3rd Normal Form (3NF), OWASP Top 10 Cybersecurity Defenses
-- Target DB   : DX_LMS
-- Author      : Antigravity AI Engineering Team
-- =========================================================================================

-- 1. PEMBUATAN DATABASE & PENGATURAN LINGKUNGAN
IF NOT EXISTS (SELECT name FROM master.dbo.sysdatabases WHERE name = N'DX_LMS')
BEGIN
    CREATE DATABASE [DX_LMS] COLLATE Latin1_General_CI_AS;
END
GO

USE [DX_LMS];
GO

-- Set Safe Session Parameters
SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

-- =========================================================================================
-- BAGIAN I: PENGHAPUSAN OBJEK LAMA (CLEAN SLATE / RE-RUN IDEMPOTENT)
-- Dihapus berurutan dari level Child terbawah ke level Master teratas
-- =========================================================================================
IF OBJECT_ID('dbo.DX_LMS_T_Forum_Posts', 'V') IS NOT NULL DROP VIEW dbo.DX_LMS_T_Forum_Posts;
IF OBJECT_ID('dbo.DX_LMS_T_Forum_Threads', 'V') IS NOT NULL DROP VIEW dbo.DX_LMS_T_Forum_Threads;

IF OBJECT_ID('dbo.DX_LMS_T_Activity_Logs', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Activity_Logs;
IF OBJECT_ID('dbo.DX_LMS_T_Quiz_Answers', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Quiz_Answers;
IF OBJECT_ID('dbo.DX_LMS_M_Quiz_Choices', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Quiz_Choices;
IF OBJECT_ID('dbo.DX_LMS_T_Quiz_Attempts', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Quiz_Attempts;
IF OBJECT_ID('dbo.DX_LMS_M_Quiz_Questions', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Quiz_Questions;
IF OBJECT_ID('dbo.DX_LMS_T_Assignment_Submissions', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Assignment_Submissions;
IF OBJECT_ID('dbo.DX_LMS_T_Material_Progress', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Material_Progress;
IF OBJECT_ID('dbo.DX_LMS_T_Forum_Likes', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Forum_Likes;
IF OBJECT_ID('dbo.DX_LMS_T_Forum_Replies', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Forum_Replies;
IF OBJECT_ID('dbo.DX_LMS_T_Forum_Topics', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Forum_Topics;
IF OBJECT_ID('dbo.DX_LMS_M_Session_Assignments', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Session_Assignments;
IF OBJECT_ID('dbo.DX_LMS_M_Quizzes', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Quizzes;
IF OBJECT_ID('dbo.DX_LMS_M_Course_Materials', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Course_Materials;
IF OBJECT_ID('dbo.DX_LMS_T_Attendance', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Attendance;
IF OBJECT_ID('dbo.DX_LMS_T_Certificates', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Certificates;
IF OBJECT_ID('dbo.DX_LMS_T_Course_Histories', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Course_Histories;
IF OBJECT_ID('dbo.DX_LMS_T_Course_Enrollments', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Course_Enrollments;
IF OBJECT_ID('dbo.DX_LMS_M_Group_Members', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Group_Members;
IF OBJECT_ID('dbo.DX_LMS_M_Course_Groups', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Course_Groups;
IF OBJECT_ID('dbo.DX_LMS_M_Course_Targets', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Course_Targets;
IF OBJECT_ID('dbo.DX_LMS_M_Course_Sessions', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Course_Sessions;
IF OBJECT_ID('dbo.DX_LMS_T_Notifications', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_T_Notifications;
IF OBJECT_ID('dbo.DX_LMS_M_Courses', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Courses;
IF OBJECT_ID('dbo.DX_LMS_W_Staging_Import', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_W_Staging_Import;
IF OBJECT_ID('dbo.DX_LMS_M_Employees', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Employees;
IF OBJECT_ID('dbo.DX_LMS_M_Sections', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Sections;
IF OBJECT_ID('dbo.DX_LMS_M_Divisions', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Divisions;
IF OBJECT_ID('dbo.DX_LMS_M_Departments', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Departments;
IF OBJECT_ID('dbo.DX_LMS_M_Golongan', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Golongan;
IF OBJECT_ID('dbo.DX_LMS_M_Positions', 'U') IS NOT NULL DROP TABLE dbo.DX_LMS_M_Positions;
GO

-- =========================================================================================
-- LEVEL 0: MASTER STRUKTUR ORGANISASI & KLASIFIKASI KARYAWAN
-- =========================================================================================

-- 1. Tabel Master Jabatan
CREATE TABLE [dbo].[DX_LMS_M_Positions] (
    [position_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [position_name]   VARCHAR(100)         NOT NULL,
    [position_status] VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Positions] PRIMARY KEY CLUSTERED ([position_id])
);
GO

-- 2. Tabel Master Golongan (Independen: 1 Jabatan bisa beragam Golongan)
CREATE TABLE [dbo].[DX_LMS_M_Golongan] (
    [golongan_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [golongan_name]   VARCHAR(50)          NOT NULL,
    [golongan_level]  INT                  NOT NULL DEFAULT 1,
    [golongan_status] VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Golongan] PRIMARY KEY CLUSTERED ([golongan_id])
);
GO

-- 3. Tabel Master Departemen
CREATE TABLE [dbo].[DX_LMS_M_Departments] (
    [department_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [department_name]   VARCHAR(150)         NOT NULL,
    [department_status] VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Departments] PRIMARY KEY CLUSTERED ([department_id])
);
GO

-- 4. Tabel Master Divisi
CREATE TABLE [dbo].[DX_LMS_M_Divisions] (
    [division_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [division_name]   VARCHAR(150)         NOT NULL,
    [division_status] VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Divisions] PRIMARY KEY CLUSTERED ([division_id])
);
GO

-- 5. Tabel Master Seksi
CREATE TABLE [dbo].[DX_LMS_M_Sections] (
    [section_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [section_name]   VARCHAR(150)         NOT NULL,
    [section_status] VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Sections] PRIMARY KEY CLUSTERED ([section_id])
);
GO

-- =========================================================================================
-- LEVEL 1: MASTER PENGGUNA (EMPLOYEES) & STAGING IMPORT
-- =========================================================================================

-- 6. Tabel Master Karyawan / Pengguna Sistem
-- Fitur Keamanan: Password bcrypt hash 255 char, RBAC check constraints, Role Teacher TRE vs Associate
CREATE TABLE [dbo].[DX_LMS_M_Employees] (
    [employee_id]   BIGINT IDENTITY(1,1) NOT NULL,
    [npk]           VARCHAR(50)          NOT NULL, -- Nomor Pokok Karyawan (Min 7 Digit, e.g. 1234567, AD1234567)
    [fullname]      VARCHAR(150)         NOT NULL, -- Nama lengkap karyawan
    [golongan]      VARCHAR(50)          NOT NULL DEFAULT 'I-1', -- Format [huruf]-[angka] (e.g. I-1, II-2)
    [bu_code]       INT                  NOT NULL DEFAULT 100,   -- Business Unit Code
    [section]       VARCHAR(150)         NOT NULL DEFAULT 'Assembling',  -- Nama Seksi
    [department]    VARCHAR(150)         NOT NULL DEFAULT 'Manufacturing', -- Nama Departemen
    [division]      VARCHAR(150)         NOT NULL DEFAULT 'Produksi',    -- Nama Divisi
    [directorate]   VARCHAR(150)         NOT NULL DEFAULT 'Plant 1',     -- Nama Direktorat
    [email]         VARCHAR(150)         NOT NULL, -- Email karyawan
    [status]        VARCHAR(50)          NOT NULL DEFAULT 'Active', -- Status karyawan ('Active' / 'Inactive')

    -- Kolom Pelengkap Aplikasi LMS:
    [password]      VARCHAR(255)         NOT NULL, -- BCrypt Password Hash
    [jabatan]       VARCHAR(100)         NOT NULL DEFAULT 'Operator', -- Posisi fungsional (Operator, Leader, SPV, dll)
    [role]          VARCHAR(20)          NOT NULL DEFAULT 'student',  -- 'student', 'teacher', 'admin'
    [teacher_type]  VARCHAR(30)          NULL,     -- 'TRE' (Training & Education) atau 'Associate'
    [position_id]   BIGINT               NULL,
    [golongan_id]   BIGINT               NULL,
    [department_id] BIGINT               NULL,
    [division_id]   BIGINT               NULL,
    [section_id]    BIGINT               NULL,
    [created_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Employees] PRIMARY KEY CLUSTERED ([employee_id]),
    CONSTRAINT [UQ_DX_LMS_M_Employees_npk] UNIQUE ([npk]),
    CONSTRAINT [UQ_DX_LMS_M_Employees_email] UNIQUE ([email]),
    CONSTRAINT [CK_DX_LMS_M_Employees_role] CHECK ([role] IN ('student', 'teacher', 'admin')),
    CONSTRAINT [CK_DX_LMS_M_Employees_teacher_type] CHECK ([teacher_type] IN ('TRE', 'Associate') OR [teacher_type] IS NULL),
    CONSTRAINT [FK_DX_LMS_M_Employees_Position] FOREIGN KEY ([position_id]) REFERENCES [dbo].[DX_LMS_M_Positions] ([position_id]),
    CONSTRAINT [FK_DX_LMS_M_Employees_Golongan] FOREIGN KEY ([golongan_id]) REFERENCES [dbo].[DX_LMS_M_Golongan] ([golongan_id]),
    CONSTRAINT [FK_DX_LMS_M_Employees_Department] FOREIGN KEY ([department_id]) REFERENCES [dbo].[DX_LMS_M_Departments] ([department_id]),
    CONSTRAINT [FK_DX_LMS_M_Employees_Division] FOREIGN KEY ([division_id]) REFERENCES [dbo].[DX_LMS_M_Divisions] ([division_id]),
    CONSTRAINT [FK_DX_LMS_M_Employees_Section] FOREIGN KEY ([section_id]) REFERENCES [dbo].[DX_LMS_M_Sections] ([section_id])
);
GO

CREATE INDEX [IX_DX_LMS_M_Employees_npk] ON [dbo].[DX_LMS_M_Employees] ([npk]);
CREATE INDEX [IX_DX_LMS_M_Employees_role] ON [dbo].[DX_LMS_M_Employees] ([role]);
CREATE INDEX [IX_DX_LMS_M_Employees_bucode] ON [dbo].[DX_LMS_M_Employees] ([bu_code]);
GO

-- 7. Tabel Staging Data Import Massal (Work Table)
CREATE TABLE [dbo].[DX_LMS_W_Staging_Import] (
    [staging_id]      BIGINT IDENTITY(1,1) NOT NULL,
    [import_type]     VARCHAR(50)          NOT NULL,
    [raw_npk]         VARCHAR(50)          NULL,
    [raw_name]        VARCHAR(150)         NULL,
    [raw_email]       VARCHAR(150)         NULL,
    [raw_position]    VARCHAR(100)         NULL,
    [raw_golongan]    VARCHAR(50)          NULL,
    [raw_department]  VARCHAR(150)         NULL,
    [raw_division]    VARCHAR(150)         NULL,
    [raw_section]     VARCHAR(150)         NULL,
    [import_status]   VARCHAR(20)          NOT NULL DEFAULT 'pending',
    [error_message]   NVARCHAR(MAX)        NULL,
    [imported_by]     BIGINT               NULL,
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_W_Staging_Import] PRIMARY KEY CLUSTERED ([staging_id]),
    CONSTRAINT [FK_DX_LMS_W_Staging_Import_User] FOREIGN KEY ([imported_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- =========================================================================================
-- LEVEL 2: MASTER COURSE & NOTIFIKASI
-- =========================================================================================

-- 8. Tabel Master Course (Pelatihan)
CREATE TABLE [dbo].[DX_LMS_M_Courses] (
    [course_id]      BIGINT IDENTITY(1,1) NOT NULL,
    [course_code]    VARCHAR(20)          NOT NULL,
    [title]          NVARCHAR(255)        NOT NULL,
    [description]    NVARCHAR(MAX)        NULL,
    [category]       VARCHAR(50)          NOT NULL DEFAULT 'Wajib', -- 'Wajib', 'Opsional'
    [thumbnail]      NVARCHAR(500)        NULL,
    [course_status]  VARCHAR(30)          NOT NULL DEFAULT 'Draft', -- 'Draft', 'Submitted', 'Approved', 'Active', 'Archived', 'Rejected'
    [passing_grade]  INT                  NOT NULL DEFAULT 75,
    [created_by]     BIGINT               NOT NULL, -- FK ke Teacher Pembuat
    [updated_by]     BIGINT               NULL,
    [created_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Courses] PRIMARY KEY CLUSTERED ([course_id]),
    CONSTRAINT [UQ_DX_LMS_M_Courses_code] UNIQUE ([course_code]),
    CONSTRAINT [CK_DX_LMS_M_Courses_grade] CHECK ([passing_grade] >= 0 AND [passing_grade] <= 100),
    CONSTRAINT [FK_DX_LMS_M_Courses_created_by] FOREIGN KEY ([created_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_M_Courses_updated_by] FOREIGN KEY ([updated_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

CREATE INDEX [IX_DX_LMS_M_Courses_status] ON [dbo].[DX_LMS_M_Courses] ([course_status]);
GO

-- 9. Tabel Notifikasi Pengguna
CREATE TABLE [dbo].[DX_LMS_T_Notifications] (
    [notification_id]      BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]          BIGINT               NOT NULL,
    [notification_title]   NVARCHAR(255)        NOT NULL,
    [notification_message] NVARCHAR(MAX)        NOT NULL,
    [notification_type]    VARCHAR(50)          NOT NULL DEFAULT 'info',
    [is_read]              BIT                  NOT NULL DEFAULT 0,
    [read_at]              DATETIME2            NULL,
    [created_at]           DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]           DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Notifications] PRIMARY KEY CLUSTERED ([notification_id]),
    CONSTRAINT [FK_DX_LMS_T_Notifications_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- =========================================================================================
-- LEVEL 3: SESI KURSUS, PENUGASAN TARGET FLEKSIBEL, DAN MANAJEMEN KELOMPOK
-- =========================================================================================

-- 10. Tabel Sesi / Bab Pembelajaran (Course Sessions)
CREATE TABLE [dbo].[DX_LMS_M_Course_Sessions] (
    [session_id]          BIGINT IDENTITY(1,1) NOT NULL,
    [course_id]           BIGINT               NOT NULL,
    [session_title]       NVARCHAR(255)        NOT NULL,
    [session_description] NVARCHAR(MAX)        NULL,
    [session_order]       INT                  NOT NULL DEFAULT 1, -- Urutan Sesi 1, 2, dst.
    [session_status]      VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]          DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]          DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Course_Sessions] PRIMARY KEY CLUSTERED ([session_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Sessions_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id]) ON DELETE CASCADE
);
GO

CREATE INDEX [IX_DX_LMS_M_Course_Sessions_Course] ON [dbo].[DX_LMS_M_Course_Sessions] ([course_id]);
GO

-- 11. Tabel Target Kursus Fleksibel (Target Audience Teacher & Corporate Mandate Admin)
CREATE TABLE [dbo].[DX_LMS_M_Course_Targets] (
    [course_target_id] BIGINT IDENTITY(1,1) NOT NULL,
    [course_id]        BIGINT               NOT NULL,
    [position_id]      BIGINT               NULL, -- Nullable jika target berlaku untuk semua jabatan
    [golongan_id]      BIGINT               NULL, -- Nullable jika target berlaku untuk semua golongan
    [department_id]    BIGINT               NULL, -- Nullable jika target berlaku lintas departemen
    [assigned_by]      BIGINT               NOT NULL, -- FK ke Teacher pembuat atau Admin yang meng-assign
    [target_type]      VARCHAR(30)          NOT NULL DEFAULT 'Target_Audience', -- 'Target_Audience' atau 'Corporate_Mandate'
    [target_status]    VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]       DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]       DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Course_Targets] PRIMARY KEY CLUSTERED ([course_target_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Targets_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id]) ON DELETE CASCADE,
    CONSTRAINT [FK_DX_LMS_M_Course_Targets_Position] FOREIGN KEY ([position_id]) REFERENCES [dbo].[DX_LMS_M_Positions] ([position_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Targets_Golongan] FOREIGN KEY ([golongan_id]) REFERENCES [dbo].[DX_LMS_M_Golongan] ([golongan_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Targets_Dept] FOREIGN KEY ([department_id]) REFERENCES [dbo].[DX_LMS_M_Departments] ([department_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Targets_User] FOREIGN KEY ([assigned_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- 12. Tabel Kelompok Belajar Peserta (Course Groups)
CREATE TABLE [dbo].[DX_LMS_M_Course_Groups] (
    [group_id]    BIGINT IDENTITY(1,1) NOT NULL,
    [course_id]   BIGINT               NOT NULL,
    [group_name]  NVARCHAR(100)        NOT NULL, -- Contoh: 'Kelompok 1 (Line A)', 'Tim Kaizen Alpha'
    [group_code]  VARCHAR(50)          NOT NULL,
    [created_by]  BIGINT               NOT NULL, -- Teacher yang membentuk kelompok
    [created_at]  DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]  DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Course_Groups] PRIMARY KEY CLUSTERED ([group_id]),
    CONSTRAINT [UQ_DX_LMS_M_Course_Groups_code] UNIQUE ([group_code]),
    CONSTRAINT [FK_DX_LMS_M_Course_Groups_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Groups_Creator] FOREIGN KEY ([created_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- 13. Tabel Anggota Kelompok (Group Members) — Pencegahan BOLA/IDOR
CREATE TABLE [dbo].[DX_LMS_M_Group_Members] (
    [group_member_id] BIGINT IDENTITY(1,1) NOT NULL,
    [group_id]        BIGINT               NOT NULL,
    [employee_id]     BIGINT               NOT NULL,
    [is_leader]       BIT                  NOT NULL DEFAULT 0,
    [joined_at]       DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Group_Members] PRIMARY KEY CLUSTERED ([group_member_id]),
    CONSTRAINT [UQ_DX_LMS_M_Group_Members_Member] UNIQUE ([group_id], [employee_id]),
    CONSTRAINT [FK_DX_LMS_M_Group_Members_Group] FOREIGN KEY ([group_id]) REFERENCES [dbo].[DX_LMS_M_Course_Groups] ([group_id]) ON DELETE CASCADE,
    CONSTRAINT [FK_DX_LMS_M_Group_Members_Employee] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- 14. Tabel Pendaftaran Peserta (Course Enrollments) — Berlaku di Level Course
CREATE TABLE [dbo].[DX_LMS_T_Course_Enrollments] (
    [enrollment_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]       BIGINT               NOT NULL,
    [course_id]         BIGINT               NOT NULL,
    [enrollment_status] VARCHAR(30)          NOT NULL DEFAULT 'enrolled', -- 'enrolled', 'in_progress', 'completed', 'dropped'
    [enrolled_at]       DATETIME2            NOT NULL DEFAULT GETDATE(),
    [completed_at]      DATETIME2            NULL,
    [created_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Course_Enrollments] PRIMARY KEY CLUSTERED ([enrollment_id]),
    CONSTRAINT [UQ_DX_LMS_T_Course_Enrollments_User] UNIQUE ([employee_id], [course_id]),
    CONSTRAINT [FK_DX_LMS_T_Course_Enrollments_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Course_Enrollments_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id])
);
GO

-- 15. Tabel Riwayat Kelulusan & Rapor Global (Course Histories)
CREATE TABLE [dbo].[DX_LMS_T_Course_Histories] (
    [history_id]          BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]         BIGINT               NOT NULL,
    [course_id]           BIGINT               NOT NULL,
    [progress_percentage] INT                  NOT NULL DEFAULT 0,
    [completion_status]   VARCHAR(30)          NOT NULL DEFAULT 'In_Progress', -- 'In_Progress', 'Passed', 'Failed'
    [completed_session]   INT                  NOT NULL DEFAULT 0,
    [final_score]         DECIMAL(5,2)         NULL,
    [last_accessed_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),
    [completed_at]        DATETIME2            NULL,
    [created_at]          DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]          DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Course_Histories] PRIMARY KEY CLUSTERED ([history_id]),
    CONSTRAINT [UQ_DX_LMS_T_Course_Histories_User] UNIQUE ([employee_id], [course_id]),
    CONSTRAINT [FK_DX_LMS_T_Course_Histories_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Course_Histories_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id])
);
GO

-- 16. Tabel Sertifikat Kelulusan Resmi
CREATE TABLE [dbo].[DX_LMS_T_Certificates] (
    [certificate_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]        BIGINT               NOT NULL,
    [course_id]          BIGINT               NOT NULL,
    [certificate_number] VARCHAR(100)         NOT NULL,
    [certificate_file]   NVARCHAR(500)        NOT NULL,
    [issued_at]          DATETIME2            NOT NULL DEFAULT GETDATE(),
    [certificate_status] VARCHAR(20)          NOT NULL DEFAULT 'Valid',
    [created_at]         DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]         DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Certificates] PRIMARY KEY CLUSTERED ([certificate_id]),
    CONSTRAINT [UQ_DX_LMS_T_Certificates_number] UNIQUE ([certificate_number]),
    CONSTRAINT [FK_DX_LMS_T_Certificates_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Certificates_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id])
);
GO

-- 17. Tabel Presensi Kehadiran Sesi / Kelas
CREATE TABLE [dbo].[DX_LMS_T_Attendance] (
    [attendance_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]       BIGINT               NOT NULL,
    [course_id]         BIGINT               NOT NULL,
    [session_id]        BIGINT               NULL,
    [attendance_status] VARCHAR(20)          NOT NULL DEFAULT 'Present', -- 'Present', 'Absent', 'Excused'
    [attendance_date]   DATETIME2            NOT NULL DEFAULT GETDATE(),
    [created_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Attendance] PRIMARY KEY CLUSTERED ([attendance_id]),
    CONSTRAINT [FK_DX_LMS_T_Attendance_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Attendance_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id]),
    CONSTRAINT [FK_DX_LMS_T_Attendance_Session] FOREIGN KEY ([session_id]) REFERENCES [dbo].[DX_LMS_M_Course_Sessions] ([session_id])
);
GO

-- 18. Tabel Topik Diskusi Forum (Forum Topics)
CREATE TABLE [dbo].[DX_LMS_T_Forum_Topics] (
    [topic_id]          BIGINT IDENTITY(1,1) NOT NULL,
    [course_id]         BIGINT               NOT NULL,
    [session_id]        BIGINT               NULL,
    [employee_id]       BIGINT               NOT NULL,
    [category_type]     VARCHAR(50)          NOT NULL DEFAULT 'materi', -- 'materi', 'quiz', 'tugas', 'umum'
    [title]             NVARCHAR(255)        NOT NULL,
    [content]           NVARCHAR(MAX)        NOT NULL,
    [attachment_path]   NVARCHAR(500)        NULL,
    [status]            VARCHAR(20)          NOT NULL DEFAULT 'open',
    [moderation_status] VARCHAR(20)          NOT NULL DEFAULT 'approved',
    [likes_count]       INT                  NOT NULL DEFAULT 0,
    [replies_count]     INT                  NOT NULL DEFAULT 0,
    [is_pinned]         BIT                  NOT NULL DEFAULT 0,
    [is_hot]            BIT                  NOT NULL DEFAULT 0,
    [created_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Forum_Topics] PRIMARY KEY CLUSTERED ([topic_id]),
    CONSTRAINT [FK_DX_LMS_T_Forum_Topics_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id]),
    CONSTRAINT [FK_DX_LMS_T_Forum_Topics_Session] FOREIGN KEY ([session_id]) REFERENCES [dbo].[DX_LMS_M_Course_Sessions] ([session_id]),
    CONSTRAINT [FK_DX_LMS_T_Forum_Topics_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- =========================================================================================
-- LEVEL 4: KONTEN MATERI, KUIS INDUK, DAN DEFINISI TUGAS KELOMPOK PER SESI
-- =========================================================================================

-- 19. Tabel Master Materi Pembelajaran
CREATE TABLE [dbo].[DX_LMS_M_Course_Materials] (
    [material_id]          BIGINT IDENTITY(1,1) NOT NULL,
    [session_id]           BIGINT               NOT NULL,
    [material_title]       NVARCHAR(255)        NOT NULL,
    [material_description] NVARCHAR(MAX)        NULL,
    [material_type]        VARCHAR(20)          NOT NULL DEFAULT 'text', -- 'video', 'pdf', 'document', 'text'
    [file_path]            NVARCHAR(500)        NULL,
    [duration]             VARCHAR(50)          NULL,
    [material_order]       INT                  NOT NULL DEFAULT 1,
    [material_status]      VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]           DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]           DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Course_Materials] PRIMARY KEY CLUSTERED ([material_id]),
    CONSTRAINT [FK_DX_LMS_M_Course_Materials_Session] FOREIGN KEY ([session_id]) REFERENCES [dbo].[DX_LMS_M_Course_Sessions] ([session_id]) ON DELETE CASCADE
);
GO

-- 20. Tabel Master Kuis Sesi & Final Assessment
-- Kuis Sesi terikat session_id, Final Assessment terikat langsung course_id (session_id = NULL)
CREATE TABLE [dbo].[DX_LMS_M_Quizzes] (
    [quiz_id]            BIGINT IDENTITY(1,1) NOT NULL,
    [course_id]          BIGINT               NOT NULL,
    [session_id]         BIGINT               NULL, -- NULL untuk Final Assessment
    [quiz_type]          VARCHAR(30)          NOT NULL DEFAULT 'quiz', -- 'quiz', 'pre_test', 'post_test', 'final_assessment'
    [quiz_title]         NVARCHAR(255)        NOT NULL,
    [quiz_description]   NVARCHAR(MAX)        NULL,
    [quiz_duration]      INT                  NOT NULL DEFAULT 30, -- Menit
    [quiz_attempt_limit] INT                  NOT NULL DEFAULT 3,
    [quiz_passing_grade] INT                  NOT NULL DEFAULT 75,
    [quiz_status]        VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]         DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]         DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Quizzes] PRIMARY KEY CLUSTERED ([quiz_id]),
    CONSTRAINT [CK_DX_LMS_M_Quizzes_grade] CHECK ([quiz_passing_grade] >= 0 AND [quiz_passing_grade] <= 100),
    CONSTRAINT [FK_DX_LMS_M_Quizzes_Course] FOREIGN KEY ([course_id]) REFERENCES [dbo].[DX_LMS_M_Courses] ([course_id]),
    CONSTRAINT [FK_DX_LMS_M_Quizzes_Session] FOREIGN KEY ([session_id]) REFERENCES [dbo].[DX_LMS_M_Course_Sessions] ([session_id])
);
GO

-- 21. Tabel Definisi Tugas Kelompok per Sesi (Session Assignments)
CREATE TABLE [dbo].[DX_LMS_M_Session_Assignments] (
    [assignment_id]        BIGINT IDENTITY(1,1) NOT NULL,
    [session_id]           BIGINT               NOT NULL,
    [title]                NVARCHAR(255)        NOT NULL, -- Contoh: 'Tugas Kelompok Sesi 1: Audit 5S Lini Produksi'
    [description]          NVARCHAR(MAX)        NOT NULL,
    [instructions]         NVARCHAR(MAX)        NULL,
    [attachment_path]      NVARCHAR(500)        NULL,
    [max_score]            INT                  NOT NULL DEFAULT 100,
    [due_date]             DATETIME2            NULL,
    [assignment_status]    VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]           DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]           DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Session_Assignments] PRIMARY KEY CLUSTERED ([assignment_id]),
    CONSTRAINT [FK_DX_LMS_M_Session_Assignments_Session] FOREIGN KEY ([session_id]) REFERENCES [dbo].[DX_LMS_M_Course_Sessions] ([session_id]) ON DELETE CASCADE
);
GO

-- 22. Tabel Balasan Diskusi Forum (Forum Replies)
CREATE TABLE [dbo].[DX_LMS_T_Forum_Replies] (
    [reply_id]        BIGINT IDENTITY(1,1) NOT NULL,
    [topic_id]        BIGINT               NOT NULL,
    [parent_reply_id] BIGINT               NULL, -- Threading balasan bersarang
    [employee_id]     BIGINT               NOT NULL,
    [content]         NVARCHAR(MAX)        NOT NULL,
    [attachment_path] NVARCHAR(500)        NULL,
    [is_solution]     BIT                  NOT NULL DEFAULT 0, -- Tanda Solusi Terbaik dari Teacher
    [likes_count]     INT                  NOT NULL DEFAULT 0,
    [status]          VARCHAR(20)          NOT NULL DEFAULT 'active',
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Forum_Replies] PRIMARY KEY CLUSTERED ([reply_id]),
    CONSTRAINT [FK_DX_LMS_T_Forum_Replies_Topic] FOREIGN KEY ([topic_id]) REFERENCES [dbo].[DX_LMS_T_Forum_Topics] ([topic_id]) ON DELETE CASCADE,
    CONSTRAINT [FK_DX_LMS_T_Forum_Replies_Parent] FOREIGN KEY ([parent_reply_id]) REFERENCES [dbo].[DX_LMS_T_Forum_Replies] ([reply_id]),
    CONSTRAINT [FK_DX_LMS_T_Forum_Replies_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- =========================================================================================
-- LEVEL 5: BANK SOAL, PROGRESS MATERI, SUBMISSION TUGAS KELOMPOK, PERCOBAAN KUIS
-- =========================================================================================

-- 23. Tabel Master Butir Pertanyaan Kuis
CREATE TABLE [dbo].[DX_LMS_M_Quiz_Questions] (
    [question_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [quiz_id]         BIGINT               NOT NULL,
    [question_text]   NVARCHAR(MAX)        NOT NULL,
    [question_type]   VARCHAR(20)          NOT NULL DEFAULT 'multiple_choice',
    [question_order]  INT                  NOT NULL DEFAULT 1,
    [question_status] VARCHAR(20)          NOT NULL DEFAULT 'Active',
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Quiz_Questions] PRIMARY KEY CLUSTERED ([question_id]),
    CONSTRAINT [FK_DX_LMS_M_Quiz_Questions_Quiz] FOREIGN KEY ([quiz_id]) REFERENCES [dbo].[DX_LMS_M_Quizzes] ([quiz_id]) ON DELETE CASCADE
);
GO

-- 24. Tabel Progress Pembacaan Materi Peserta — Sequential Gate Prerequisite
CREATE TABLE [dbo].[DX_LMS_T_Material_Progress] (
    [progress_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]     BIGINT               NOT NULL,
    [material_id]     BIGINT               NOT NULL,
    [progress_status] VARCHAR(20)          NOT NULL DEFAULT 'completed', -- 'in_progress', 'completed'
    [is_completed]    BIT                  NOT NULL DEFAULT 1,
    [completed_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),
    [created_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Material_Progress] PRIMARY KEY CLUSTERED ([progress_id]),
    CONSTRAINT [UQ_DX_LMS_T_Material_Progress_User] UNIQUE ([employee_id], [material_id]),
    CONSTRAINT [FK_DX_LMS_T_Material_Progress_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Material_Progress_Material] FOREIGN KEY ([material_id]) REFERENCES [dbo].[DX_LMS_M_Course_Materials] ([material_id]) ON DELETE CASCADE
);
GO

-- 25. Tabel Pengumpulan Tugas Kelompok (BOLA/IDOR Defended: Terisolasi per Group)
CREATE TABLE [dbo].[DX_LMS_T_Assignment_Submissions] (
    [submission_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [assignment_id]     BIGINT               NOT NULL,
    [group_id]          BIGINT               NOT NULL,
    [submitted_by]      BIGINT               NOT NULL, -- Pengunggah perwakilan kelompok
    [file_path]         NVARCHAR(500)        NOT NULL,
    [file_name]         NVARCHAR(255)        NOT NULL,
    [notes]             NVARCHAR(MAX)        NULL,
    [score]             DECIMAL(5,2)         NULL,
    [graded_by]         BIGINT               NULL, -- Teacher penilai
    [feedback]          NVARCHAR(MAX)        NULL,
    [submission_status] VARCHAR(30)          NOT NULL DEFAULT 'Submitted', -- 'Submitted', 'Graded', 'Revision'
    [graded_at]         DATETIME2            NULL,
    [submitted_at]      DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]        DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Assignment_Submissions] PRIMARY KEY CLUSTERED ([submission_id]),
    CONSTRAINT [UQ_DX_LMS_T_Assignment_Submissions_Group] UNIQUE ([assignment_id], [group_id]),
    CONSTRAINT [CK_DX_LMS_T_Assignment_Submissions_score] CHECK ([score] IS NULL OR ([score] >= 0 AND [score] <= 100)),
    CONSTRAINT [FK_DX_LMS_T_Assignment_Submissions_Assignment] FOREIGN KEY ([assignment_id]) REFERENCES [dbo].[DX_LMS_M_Session_Assignments] ([assignment_id]),
    CONSTRAINT [FK_DX_LMS_T_Assignment_Submissions_Group] FOREIGN KEY ([group_id]) REFERENCES [dbo].[DX_LMS_M_Course_Groups] ([group_id]),
    CONSTRAINT [FK_DX_LMS_T_Assignment_Submissions_Uploader] FOREIGN KEY ([submitted_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Assignment_Submissions_Grader] FOREIGN KEY ([graded_by]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

-- 26. Tabel Percobaan Pengerjaan Kuis (Quiz Attempts)
CREATE TABLE [dbo].[DX_LMS_T_Quiz_Attempts] (
    [attempt_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]    BIGINT               NOT NULL,
    [quiz_id]        BIGINT               NOT NULL,
    [attempt_score]  DECIMAL(5,2)         NULL,
    [attempt_status] VARCHAR(20)          NOT NULL DEFAULT 'Submitted',
    [attempt_number] INT                  NOT NULL DEFAULT 1,
    [is_passed]      BIT                  NOT NULL DEFAULT 0, -- Kunci kelulusan sesi / pembuka sesi berikutnya
    [started_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),
    [submitted_at]   DATETIME2            NULL,
    [created_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]     DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Quiz_Attempts] PRIMARY KEY CLUSTERED ([attempt_id]),
    CONSTRAINT [CK_DX_LMS_T_Quiz_Attempts_score] CHECK ([attempt_score] IS NULL OR ([attempt_score] >= 0 AND [attempt_score] <= 100)),
    CONSTRAINT [FK_DX_LMS_T_Quiz_Attempts_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id]),
    CONSTRAINT [FK_DX_LMS_T_Quiz_Attempts_Quiz] FOREIGN KEY ([quiz_id]) REFERENCES [dbo].[DX_LMS_M_Quizzes] ([quiz_id])
);
GO

-- =========================================================================================
-- LEVEL 6: OPSI PILIHAN JAWABAN & LEMBAR AUDIT JAWABAN DETAIL
-- =========================================================================================

-- 27. Tabel Opsi Pilihan Jawaban Soal (Multiple Choice Choices)
CREATE TABLE [dbo].[DX_LMS_M_Quiz_Choices] (
    [choice_id]     BIGINT IDENTITY(1,1) NOT NULL,
    [question_id]   BIGINT               NOT NULL,
    [choice_text]   NVARCHAR(MAX)        NOT NULL,
    [is_correct]    BIT                  NOT NULL DEFAULT 0, -- 1 untuk Kunci Jawaban Benar
    [choice_order]  INT                  NOT NULL DEFAULT 1,
    [created_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),
    [updated_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_M_Quiz_Choices] PRIMARY KEY CLUSTERED ([choice_id]),
    CONSTRAINT [FK_DX_LMS_M_Quiz_Choices_Question] FOREIGN KEY ([question_id]) REFERENCES [dbo].[DX_LMS_M_Quiz_Questions] ([question_id]) ON DELETE CASCADE
);
GO

-- 28. Tabel Lembar Jawaban Detail per Butir Soal (Quiz Answers)
CREATE TABLE [dbo].[DX_LMS_T_Quiz_Answers] (
    [answer_id]          BIGINT IDENTITY(1,1) NOT NULL,
    [attempt_id]         BIGINT               NOT NULL,
    [question_id]        BIGINT               NOT NULL,
    [selected_choice_id] BIGINT               NULL,
    [is_correct]         BIT                  NOT NULL DEFAULT 0,
    [created_at]         DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Quiz_Answers] PRIMARY KEY CLUSTERED ([answer_id]),
    CONSTRAINT [FK_DX_LMS_T_Quiz_Answers_Attempt] FOREIGN KEY ([attempt_id]) REFERENCES [dbo].[DX_LMS_T_Quiz_Attempts] ([attempt_id]) ON DELETE CASCADE,
    CONSTRAINT [FK_DX_LMS_T_Quiz_Answers_Question] FOREIGN KEY ([question_id]) REFERENCES [dbo].[DX_LMS_M_Quiz_Questions] ([question_id]),
    CONSTRAINT [FK_DX_LMS_T_Quiz_Answers_Choice] FOREIGN KEY ([selected_choice_id]) REFERENCES [dbo].[DX_LMS_M_Quiz_Choices] ([choice_id])
);
GO

-- =========================================================================================
-- LEVEL 7: JEJAK AUDIT & KEAMANAN SISTEM (ACTIVITY LOGS)
-- =========================================================================================

-- 29. Tabel Log Jejak Aktivitas Pengguna (Audit Trail Compliance)
CREATE TABLE [dbo].[DX_LMS_T_Activity_Logs] (
    [log_id]        BIGINT IDENTITY(1,1) NOT NULL,
    [employee_id]   BIGINT               NOT NULL,
    [activity_type] VARCHAR(50)          NOT NULL, -- 'login', 'view_material', 'start_quiz', 'submit_assignment', 'role_switch'
    [reference_id]  VARCHAR(50)          NULL,     -- ID objek terkait (course_id, quiz_id, dll.)
    [description]   NVARCHAR(MAX)        NULL,
    [ip_address]    VARCHAR(45)          NULL,     -- IPv4 / IPv6
    [user_agent]    NVARCHAR(500)        NULL,
    [created_at]    DATETIME2            NOT NULL DEFAULT GETDATE(),

    CONSTRAINT [PK_DX_LMS_T_Activity_Logs] PRIMARY KEY CLUSTERED ([log_id]),
    CONSTRAINT [FK_DX_LMS_T_Activity_Logs_User] FOREIGN KEY ([employee_id]) REFERENCES [dbo].[DX_LMS_M_Employees] ([employee_id])
);
GO

CREATE INDEX [IX_DX_LMS_T_Activity_Logs_type] ON [dbo].[DX_LMS_T_Activity_Logs] ([activity_type]);
CREATE INDEX [IX_DX_LMS_T_Activity_Logs_user] ON [dbo].[DX_LMS_T_Activity_Logs] ([employee_id]);
GO

-- =========================================================================================
-- BAGIAN II: VIEW KOMPATIBILITAS (MENCEGAH ERROR NAMA TABEL PADA MODEL PHP & ERD)
-- =========================================================================================
CREATE VIEW [dbo].[DX_LMS_T_Forum_Threads] AS
SELECT 
    [topic_id]          AS [thread_id],
    [course_id]         AS [course_id],
    [session_id]        AS [session_id],
    [employee_id]       AS [employee_id],
    [title]             AS [thread_title],
    [content]           AS [thread_content],
    [status]            AS [thread_status],
    [created_at]        AS [created_at],
    [updated_at]        AS [updated_at]
FROM [dbo].[DX_LMS_T_Forum_Topics];
GO

CREATE VIEW [dbo].[DX_LMS_T_Forum_Posts] AS
SELECT 
    [reply_id]          AS [post_id],
    [topic_id]          AS [thread_id],
    [employee_id]       AS [employee_id],
    [content]           AS [post_content],
    [status]            AS [post_status],
    [is_solution]       AS [is_solution],
    [created_at]        AS [created_at],
    [updated_at]        AS [updated_at]
FROM [dbo].[DX_LMS_T_Forum_Replies];
GO

-- Standarisasi Nama Tabel Denso (LMS_M_*, LMS_T_*, LMS_W_*)
CREATE VIEW [dbo].[LMS_M_Employees] AS SELECT * FROM [dbo].[DX_LMS_M_Employees];
GO
CREATE VIEW [dbo].[LMS_M_Employee] AS SELECT * FROM [dbo].[DX_LMS_M_Employees];
GO
CREATE VIEW [dbo].[LMS_T_Course_History] AS SELECT * FROM [dbo].[DX_LMS_T_Course_Histories];
GO
CREATE VIEW [dbo].[LMS_W_Staging_Import] AS SELECT * FROM [dbo].[DX_LMS_W_Staging_Import];
GO

-- =========================================================================================
-- BAGIAN III: SEED DATA DUMMY AWAL (TERUJI & TERVERIFIKASI LOGIN)
-- Password untuk seluruh akun dummy: password123
-- Hash Bcrypt: $2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.
-- =========================================================================================

-- 1. Data Jabatan & Golongan
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Positions] ON;
INSERT INTO [dbo].[DX_LMS_M_Positions] ([position_id], [position_name]) VALUES 
(1, 'Operator'),
(2, 'Staff'),
(3, 'Leader'),
(4, 'SPV'),
(5, 'Manager');
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Positions] OFF;

SET IDENTITY_INSERT [dbo].[DX_LMS_M_Golongan] ON;
INSERT INTO [dbo].[DX_LMS_M_Golongan] ([golongan_id], [golongan_name], [golongan_level]) VALUES 
(1, 'Gol 1', 1),
(2, 'Gol 2', 2),
(3, 'Gol 3', 3),
(4, 'Gol 4', 4),
(5, 'Gol 5', 5);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Golongan] OFF;

SET IDENTITY_INSERT [dbo].[DX_LMS_M_Departments] ON;
INSERT INTO [dbo].[DX_LMS_M_Departments] ([department_id], [department_name]) VALUES 
(1, 'Manufacturing Line A'),
(2, 'People Development (HR & GA)'),
(3, 'Quality Assurance'),
(4, 'Production Engineering');
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Departments] OFF;

SET IDENTITY_INSERT [dbo].[DX_LMS_M_Divisions] ON;
INSERT INTO [dbo].[DX_LMS_M_Divisions] ([division_id], [division_name]) VALUES 
(1, 'Operation Division'),
(2, 'Human Capital Division'),
(3, 'Technical Division');
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Divisions] OFF;

SET IDENTITY_INSERT [dbo].[DX_LMS_M_Sections] ON;
INSERT INTO [dbo].[DX_LMS_M_Sections] ([section_id], [section_name]) VALUES 
(1, 'Production Section 1'),
(2, 'Training & Education (TRE)'),
(3, 'Quality Control Section');
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Sections] OFF;
GO

-- 2. Data Akun Pengguna (Lengkap dengan 10 Kolom Standar Denso & Kandidat Peserta Course)
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Employees] ON;
INSERT INTO [dbo].[DX_LMS_M_Employees] (
    [employee_id], [npk], [fullname], [golongan], [bu_code], 
    [section], [department], [division], [directorate], [email], 
    [password], [jabatan], [role], [teacher_type], [status],
    [position_id], [golongan_id], [department_id], [division_id], [section_id]
) VALUES 
-- 1. Student / Operator (Budi Santoso)
(1, '12345', 'Budi Santoso', 'I-1', 101, 
 'Assembling', 'Manufacturing', 'Produksi', 'Production Directorate', 'budi.santoso@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Operator', 'student', NULL, 'Active', 
 1, 1, 1, 1, 1),

-- 2. Teacher TRE (Ahmad Rizal - Divisi Khusus Pelatihan, Pendidikan & Kompetensi Karyawan)
(2, '67890', 'Ahmad Rizal', 'II-2', 101, 
 'Training & Education (TRE)', 'People Development (HR & GA)', 'Human Capital', 'Corporate Directorate', 'ahmad.rizal@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Leader', 'teacher', 'TRE', 'Active', 
 3, 2, 2, 2, 2),

-- 3. Teacher Associate (Maya Safitri - Instruktur Pendamping / Lini Operasional)
(3, '67891', 'Maya Safitri', 'II-1', 100, 
 'Quality Control Section', 'Quality Assurance', 'Technical Division', 'Technical Directorate', 'maya.safitri@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Staff', 'teacher', 'Associate', 'Active', 
 2, 2, 3, 3, 3),

-- 4. Admin Sistem (Tono Wijaya)
(4, '99887', 'Tono Wijaya', 'IV-2', 100, 
 'LMS Administration', 'People Development (HR & GA)', 'Human Capital', 'Corporate Directorate', 'tono.wijaya@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Manager', 'admin', NULL, 'Active', 
 5, 4, 2, 2, 2),

-- 5. Admin Super (99999)
(5, '99999', 'Admin System', 'IV-2', 100, 
 'Corporate IT', 'People Development (HR & GA)', 'Human Capital', 'Head Office', 'admin@denso.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Super Admin', 'admin', NULL, 'Active', 
 5, 4, 2, 2, 2),

-- 6. Kandidat Peserta Course: Citra Lestari
(6, '11223', 'Citra Lestari', 'III-1', 102, 
 'Pemeriksaan', 'Quality', 'QA', 'Quality Assurance Directorate', 'citra.lestari@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Supervisor', 'student', NULL, 'Active', 
 4, 3, 3, 3, 3),

-- 7. Kandidat Peserta Course: Dian Sastro
(7, '23456', 'Dian Sastro', 'IV-1', 103, 
 'Desain', 'Engineering', 'PE', 'Technical Directorate', 'dian.sastro@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Manager', 'student', NULL, 'Active', 
 5, 4, 4, 3, 1),

-- 8. Kandidat Peserta Course: Eko Prasetyo
(8, '34567', 'Eko Prasetyo', 'I-2', 101, 
 'Machining', 'Manufacturing', 'Produksi', 'Production Directorate', 'eko.prasetyo@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Operator', 'student', NULL, 'Active', 
 1, 1, 1, 1, 1),

-- 9. Kandidat Peserta Course: Fani Amelia
(9, '45678', 'Fani Amelia', 'II-1', 100, 
 'Rekrutmen', 'HR', 'Personalia', 'Corporate Directorate', 'fani.amelia@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Staff', 'student', NULL, 'Active', 
 2, 2, 2, 2, 2),

-- 10. Kandidat Peserta Course: Gilang Ramadhan
(10, '56789', 'Gilang Ramadhan', 'II-2', 100, 
 'Pajak', 'Finance', 'Akuntansi', 'Corporate Directorate', 'gilang.ramadhan@perusahaan.co.id', 
 '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.', 'Staff', 'student', NULL, 'Active', 
 2, 2, 2, 2, 2);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Employees] OFF;
GO

-- 3. Data Course Perdana & Sesi Pembelajaran
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Courses] ON;
INSERT INTO [dbo].[DX_LMS_M_Courses] (
    [course_id], [course_code], [title], [description], [category], [thumbnail], 
    [course_status], [passing_grade], [created_by]
) VALUES 
(1, 'CRS001', 'Dasar Keselamatan Kerja & 5S di Lini Produksi', 
 'Standar prosedur operasional keselamatan kerja dan penerapan prinsip 5S (Seiri, Seiton, Seiso, Seiketsu, Shitsuke) di pabrik.', 
 'Wajib', 'assets/img/courses/course_k3.jpg', 'Active', 75, 2);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Courses] OFF;

-- 4. Target Penugasan Fleksibel (Kombinasi Rekomendasi Teacher & Mandat Admin)
INSERT INTO [dbo].[DX_LMS_M_Course_Targets] (
    [course_id], [position_id], [golongan_id], [department_id], [assigned_by], [target_type]
) VALUES 
(1, 1, 1, 1, 2, 'Target_Audience'); -- Ditugaskan oleh Teacher TRE ke Operator Golongan 1 di Manufacturing Line A

-- 5. Sesi Pembelajaran
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Course_Sessions] ON;
INSERT INTO [dbo].[DX_LMS_M_Course_Sessions] ([session_id], [course_id], [session_title], [session_order]) VALUES 
(1, 1, 'Sesi 1: Pengenalan Budaya K3 & Alat Pelindung Diri (APD)', 1),
(2, 1, 'Sesi 2: Implementasi 5S di Stasiun Kerja', 2);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Course_Sessions] OFF;

-- 6. Materi Sesi
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Course_Materials] ON;
INSERT INTO [dbo].[DX_LMS_M_Course_Materials] (
    [material_id], [session_id], [material_title], [material_type], [file_path], [duration], [material_order]
) VALUES 
(1, 1, 'Video SOP Penggunaan APD Standar Denso', 'video', 'assets/materials/k3_apd.mp4', '15 Menit', 1),
(2, 1, 'Buku Panduan Keselamatan Kerja Pabrik (PDF)', 'pdf', 'assets/materials/buku_k3.pdf', '30 Menit', 2);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Course_Materials] OFF;

-- 7. Kuis Sesi & Final Assessment
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Quizzes] ON;
INSERT INTO [dbo].[DX_LMS_M_Quizzes] (
    [quiz_id], [course_id], [session_id], [quiz_type], [quiz_title], [quiz_duration], [quiz_passing_grade]
) VALUES 
(1, 1, 1, 'quiz', 'Kuis Evaluasi Sesi 1: APD & Bahaya Fisik', 20, 75),
(2, 1, NULL, 'final_assessment', 'Final Assessment: Ujian Sertifikasi K3 & 5S', 45, 80);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Quizzes] OFF;

-- 8. Tugas Kelompok di Akhir Sesi
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Session_Assignments] ON;
INSERT INTO [dbo].[DX_LMS_M_Session_Assignments] (
    [assignment_id], [session_id], [title], [description], [max_score]
) VALUES 
(1, 1, 'Tugas Kelompok Sesi 1: Analisis Potensi Bahaya di Mesin Stamping', 
 'Lakukan observasi bersama kelompok Anda di area stamping line, dokumentasikan 3 potensi bahaya APD, dan buat lembar rekomendasi.', 100);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Session_Assignments] OFF;

-- 9. Kelompok Belajar & Anggota
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Course_Groups] ON;
INSERT INTO [dbo].[DX_LMS_M_Course_Groups] ([group_id], [course_id], [group_name], [group_code], [created_by]) VALUES 
(1, 1, 'Kelompok Kaizen 1 (Line A)', 'GRP-K3-001', 2);
SET IDENTITY_INSERT [dbo].[DX_LMS_M_Course_Groups] OFF;

INSERT INTO [dbo].[DX_LMS_M_Group_Members] ([group_id], [employee_id], [is_leader]) VALUES 
(1, 1, 1); -- Budi Santoso tergabung sebagai Leader di Kelompok Kaizen 1

-- 10. Enrollment Peserta ke Course
INSERT INTO [dbo].[DX_LMS_T_Course_Enrollments] ([employee_id], [course_id], [enrollment_status]) VALUES 
(1, 1, 'enrolled');

-- 11. Initial Progress Pembelajaran
INSERT INTO [dbo].[DX_LMS_T_Course_Histories] ([employee_id], [course_id], [progress_percentage], [completion_status]) VALUES 
(1, 1, 0, 'In_Progress');
GO

-- =========================================================================================
-- SELESAI — SKRIP DDL MASTER DX_LMS SIAP DIEKSEKUSI DI SSMS (F5)
-- =========================================================================================
