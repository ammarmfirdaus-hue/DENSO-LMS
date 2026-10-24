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
