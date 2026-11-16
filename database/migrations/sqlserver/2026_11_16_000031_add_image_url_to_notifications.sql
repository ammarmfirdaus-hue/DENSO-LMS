IF COL_LENGTH(N'dbo.lms_notifications', N'image_url') IS NULL
BEGIN
  ALTER TABLE dbo.lms_notifications ADD image_url NVARCHAR(500) NULL;
END;
