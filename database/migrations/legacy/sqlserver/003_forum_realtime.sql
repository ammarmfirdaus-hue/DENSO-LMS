/* Run once in the same SQL Server database as database/admin&teacher.sql. */
IF COL_LENGTH('dbo.forum_threads','session_code') IS NULL
  ALTER TABLE dbo.forum_threads ADD session_code VARCHAR(50) NULL;
IF COL_LENGTH('dbo.forum_threads','author_name') IS NULL
  ALTER TABLE dbo.forum_threads ADD author_name NVARCHAR(150) NULL;
IF COL_LENGTH('dbo.forum_threads','author_role') IS NULL
  ALTER TABLE dbo.forum_threads ADD author_role VARCHAR(20) NOT NULL CONSTRAINT DF_forum_threads_author_role DEFAULT 'student';
IF COL_LENGTH('dbo.forum_threads','attachment_path') IS NULL
  ALTER TABLE dbo.forum_threads ADD attachment_path NVARCHAR(260) NULL;
GO

IF OBJECT_ID('dbo.forum_replies','U') IS NULL
BEGIN
  CREATE TABLE dbo.forum_replies (
    reply_id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_forum_replies PRIMARY KEY,
    thread_id BIGINT NOT NULL,
    author_npk VARCHAR(50) NOT NULL,
    author_name NVARCHAR(150) NOT NULL,
    author_role VARCHAR(20) NOT NULL,
    body NVARCHAR(2000) NOT NULL,
    attachment_path NVARCHAR(260) NULL,
    status VARCHAR(20) NOT NULL CONSTRAINT DF_forum_replies_status DEFAULT 'active',
    created_at DATETIME2 NOT NULL CONSTRAINT DF_forum_replies_created_at DEFAULT SYSUTCDATETIME(),
    CONSTRAINT FK_forum_replies_thread FOREIGN KEY(thread_id) REFERENCES dbo.forum_threads(thread_id) ON DELETE CASCADE
  );
  CREATE INDEX IX_forum_replies_thread_created ON dbo.forum_replies(thread_id,created_at,reply_id);
END;
GO

IF OBJECT_ID('dbo.forum_likes','U') IS NULL
BEGIN
  CREATE TABLE dbo.forum_likes (
    like_id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_forum_likes PRIMARY KEY,
    target_type VARCHAR(10) NOT NULL,
    target_id BIGINT NOT NULL,
    author_npk VARCHAR(50) NOT NULL,
    created_at DATETIME2 NOT NULL CONSTRAINT DF_forum_likes_created_at DEFAULT SYSUTCDATETIME(),
    CONSTRAINT CK_forum_likes_target CHECK(target_type IN ('thread','reply')),
    CONSTRAINT UQ_forum_likes_user_target UNIQUE(target_type,target_id,author_npk)
  );
  CREATE INDEX IX_forum_likes_target ON dbo.forum_likes(target_type,target_id);
END;
GO

IF OBJECT_ID('dbo.forum_session_settings','U') IS NULL
BEGIN
  CREATE TABLE dbo.forum_session_settings (
    setting_id BIGINT IDENTITY(1,1) NOT NULL CONSTRAINT PK_forum_session_settings PRIMARY KEY,
    course_code VARCHAR(30) NOT NULL,
    session_code VARCHAR(50) NOT NULL,
    is_open BIT NOT NULL CONSTRAINT DF_forum_session_settings_is_open DEFAULT 1,
    updated_by VARCHAR(50) NOT NULL,
    updated_at DATETIME2 NOT NULL CONSTRAINT DF_forum_session_settings_updated_at DEFAULT SYSUTCDATETIME(),
    CONSTRAINT UQ_forum_session_settings_course_session UNIQUE(course_code,session_code),
    CONSTRAINT FK_forum_session_settings_course FOREIGN KEY(course_code) REFERENCES dbo.courses(course_code)
  );
END;
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.forum_threads') AND name='IX_forum_threads_course_session_created')
  CREATE INDEX IX_forum_threads_course_session_created ON dbo.forum_threads(course_code,session_code,created_at DESC) INCLUDE(status,author_npk);
GO
