IF COL_LENGTH(N'dbo.lms_notifications', N'recipient_npk') IS NULL
  ALTER TABLE dbo.lms_notifications ADD recipient_npk VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.lms_notifications') AND name=N'IX_lms_notifications_recipient')
  CREATE INDEX IX_lms_notifications_recipient ON dbo.lms_notifications(audience_role,recipient_npk,created_at DESC,notification_id DESC);
GO
