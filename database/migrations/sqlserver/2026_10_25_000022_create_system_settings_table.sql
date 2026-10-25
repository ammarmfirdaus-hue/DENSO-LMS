IF OBJECT_ID(N'dbo.lms_system_settings', N'U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_system_settings (
    setting_key NVARCHAR(100) NOT NULL CONSTRAINT PK_lms_system_settings PRIMARY KEY,
    setting_value NVARCHAR(2000) NOT NULL,
    updated_by VARCHAR(50) NULL,
    updated_at DATETIME2 NOT NULL CONSTRAINT DF_lms_system_settings_updated_at DEFAULT SYSUTCDATETIME()
  );
END;
