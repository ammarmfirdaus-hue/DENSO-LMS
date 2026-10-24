IF DB_ID(N'DX_LMS_AdminTeacher') IS NULL CREATE DATABASE DX_LMS_AdminTeacher;
GO
USE DX_LMS_AdminTeacher;
GO

IF OBJECT_ID('dbo.employee_accounts','U') IS NULL CREATE TABLE dbo.employee_accounts (
  employee_npk VARCHAR(50) NOT NULL PRIMARY KEY,
  fullname NVARCHAR(150) NOT NULL,
  email NVARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL CHECK(role IN ('admin','teacher')),
  department NVARCHAR(100) NULL,
  position NVARCHAR(100) NULL,
  phone_number VARCHAR(30) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK(status IN ('active','inactive')),
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(), updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);
GO
IF OBJECT_ID('dbo.courses','U') IS NULL CREATE TABLE dbo.courses (
  course_id BIGINT IDENTITY(1,1) PRIMARY KEY, course_code VARCHAR(30) NOT NULL UNIQUE,
  title NVARCHAR(200) NOT NULL, description NVARCHAR(4000) NOT NULL DEFAULT '',
  category VARCHAR(20) NOT NULL DEFAULT 'Wajib', status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_by VARCHAR(50) NOT NULL, updated_by VARCHAR(50) NOT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(), updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_courses_creator FOREIGN KEY(created_by) REFERENCES dbo.employee_accounts(employee_npk),
  CONSTRAINT FK_courses_updater FOREIGN KEY(updated_by) REFERENCES dbo.employee_accounts(employee_npk)
);
GO
IF OBJECT_ID('dbo.course_sessions','U') IS NULL CREATE TABLE dbo.course_sessions (
  session_id BIGINT IDENTITY(1,1) PRIMARY KEY, course_id BIGINT NOT NULL,
  title NVARCHAR(200) NOT NULL, sort_order INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'active', created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_sessions_course FOREIGN KEY(course_id) REFERENCES dbo.courses(course_id)
);
GO
IF OBJECT_ID('dbo.course_materials','U') IS NULL CREATE TABLE dbo.course_materials (
  material_id BIGINT IDENTITY(1,1) PRIMARY KEY, session_id BIGINT NOT NULL,
  title NVARCHAR(200) NOT NULL, material_type VARCHAR(20) NOT NULL,
  content NVARCHAR(MAX) NULL, file_path NVARCHAR(500) NULL, sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_materials_session FOREIGN KEY(session_id) REFERENCES dbo.course_sessions(session_id)
);
GO
IF OBJECT_ID('dbo.activity_logs','U') IS NULL CREATE TABLE dbo.activity_logs (
  activity_id BIGINT IDENTITY(1,1) PRIMARY KEY, employee_npk VARCHAR(50) NULL,
  activity_type VARCHAR(50) NOT NULL, description NVARCHAR(500) NOT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_activity_employee FOREIGN KEY(employee_npk) REFERENCES dbo.employee_accounts(employee_npk)
);
GO
IF OBJECT_ID('dbo.student_progress_snapshots','U') IS NULL CREATE TABLE dbo.student_progress_snapshots (
  snapshot_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  student_npk VARCHAR(50) NOT NULL,
  course_code VARCHAR(30) NOT NULL,
  progress TINYINT NOT NULL,
  enrollment_status VARCHAR(20) NOT NULL,
  source_updated_at DATETIME2 NOT NULL,
  synced_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT UQ_student_progress_snapshot UNIQUE(student_npk,course_code)
);
GO
IF OBJECT_ID('dbo.forum_threads','U') IS NULL CREATE TABLE dbo.forum_threads (
  thread_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  author_npk VARCHAR(50) NOT NULL,
  author_name NVARCHAR(150) NULL,
  author_role VARCHAR(20) NOT NULL DEFAULT 'student',
  course_code VARCHAR(30) NOT NULL,
  session_code VARCHAR(50) NULL,
  title NVARCHAR(200) NOT NULL,
  body NVARCHAR(MAX) NOT NULL,
  attachment_path NVARCHAR(260) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'visible' CHECK(status IN ('visible','hidden','locked')),
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_forum_threads_course FOREIGN KEY(course_code) REFERENCES dbo.courses(course_code)
);
GO
IF OBJECT_ID('dbo.forum_replies','U') IS NULL CREATE TABLE dbo.forum_replies (
  reply_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  thread_id BIGINT NOT NULL,
  author_npk VARCHAR(50) NOT NULL,
  author_name NVARCHAR(150) NOT NULL,
  author_role VARCHAR(20) NOT NULL,
  body NVARCHAR(2000) NOT NULL,
  attachment_path NVARCHAR(260) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_forum_replies_thread FOREIGN KEY(thread_id) REFERENCES dbo.forum_threads(thread_id) ON DELETE CASCADE
);
CREATE INDEX IX_forum_replies_thread_created ON dbo.forum_replies(thread_id,created_at,reply_id);
GO
IF OBJECT_ID('dbo.forum_likes','U') IS NULL CREATE TABLE dbo.forum_likes (
  like_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  target_type VARCHAR(10) NOT NULL CHECK(target_type IN ('thread','reply')),
  target_id BIGINT NOT NULL,
  author_npk VARCHAR(50) NOT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT UQ_forum_likes_user_target UNIQUE(target_type,target_id,author_npk)
);
CREATE INDEX IX_forum_likes_target ON dbo.forum_likes(target_type,target_id);
GO
IF OBJECT_ID('dbo.forum_session_settings','U') IS NULL CREATE TABLE dbo.forum_session_settings (
  setting_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  course_code VARCHAR(30) NOT NULL,
  session_code VARCHAR(50) NOT NULL,
  is_open BIT NOT NULL DEFAULT 1,
  updated_by VARCHAR(50) NOT NULL,
  updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT UQ_forum_session_settings_course_session UNIQUE(course_code,session_code),
  CONSTRAINT FK_forum_session_settings_course FOREIGN KEY(course_code) REFERENCES dbo.courses(course_code)
);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.forum_threads') AND name='IX_forum_threads_course_session_created')
  CREATE INDEX IX_forum_threads_course_session_created ON dbo.forum_threads(course_code,session_code,created_at DESC) INCLUDE(status,author_npk);
GO
