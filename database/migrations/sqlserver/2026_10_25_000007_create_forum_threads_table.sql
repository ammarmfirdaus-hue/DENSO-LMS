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
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.forum_threads') AND name='IX_forum_threads_course_session_created')
  CREATE INDEX IX_forum_threads_course_session_created ON dbo.forum_threads(course_code,session_code,created_at DESC) INCLUDE(status,author_npk);
GO
