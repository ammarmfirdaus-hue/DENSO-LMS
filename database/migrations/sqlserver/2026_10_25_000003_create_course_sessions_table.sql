IF OBJECT_ID('dbo.course_sessions','U') IS NULL CREATE TABLE dbo.course_sessions (
  session_id BIGINT IDENTITY(1,1) PRIMARY KEY, course_id BIGINT NOT NULL,
  title NVARCHAR(200) NOT NULL, sort_order INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'active', created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_sessions_course FOREIGN KEY(course_id) REFERENCES dbo.courses(course_id)
);
