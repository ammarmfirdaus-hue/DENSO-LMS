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
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.forum_replies') AND name='IX_forum_replies_thread_created')
  CREATE INDEX IX_forum_replies_thread_created ON dbo.forum_replies(thread_id,created_at,reply_id);
GO
