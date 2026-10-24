IF OBJECT_ID('dbo.forum_likes','U') IS NULL CREATE TABLE dbo.forum_likes (
  like_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  target_type VARCHAR(10) NOT NULL CHECK(target_type IN ('thread','reply')),
  target_id BIGINT NOT NULL,
  author_npk VARCHAR(50) NOT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT UQ_forum_likes_user_target UNIQUE(target_type,target_id,author_npk)
);
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.forum_likes') AND name='IX_forum_likes_target')
  CREATE INDEX IX_forum_likes_target ON dbo.forum_likes(target_type,target_id);
GO
