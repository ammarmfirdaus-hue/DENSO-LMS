IF OBJECT_ID('dbo.lms_notifications','U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_notifications (
    notification_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    audience_role VARCHAR(20) NOT NULL CHECK (audience_role IN ('student','teacher','admin')),
    title NVARCHAR(180) NOT NULL,
    message NVARCHAR(2000) NOT NULL,
    category VARCHAR(30) NOT NULL DEFAULT 'announcement'
      CHECK (category IN ('announcement','course','assessment','forum','system')),
    priority VARCHAR(20) NOT NULL DEFAULT 'normal' CHECK (priority IN ('normal','important')),
    sender_npk VARCHAR(50) NOT NULL,
    sender_role VARCHAR(20) NOT NULL CHECK (sender_role IN ('admin','teacher')),
    sender_name NVARCHAR(150) NOT NULL,
    email_requested BIT NOT NULL DEFAULT 0,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
  );
END;
