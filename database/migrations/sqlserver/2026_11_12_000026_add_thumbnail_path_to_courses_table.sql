IF COL_LENGTH('dbo.courses', 'thumbnail_path') IS NULL
BEGIN
  ALTER TABLE dbo.courses ADD thumbnail_path NVARCHAR(500) NULL;
END;

