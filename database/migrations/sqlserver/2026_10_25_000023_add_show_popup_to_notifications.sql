IF COL_LENGTH(N'dbo.lms_notifications', N'show_popup') IS NULL
  ALTER TABLE dbo.lms_notifications ADD show_popup BIT NOT NULL CONSTRAINT DF_lms_notifications_show_popup DEFAULT 0;
GO

