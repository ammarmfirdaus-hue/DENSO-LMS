/* Run in the DX_LMS_AdminTeacher database using a schema-authorized account. */
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
GO

IF OBJECT_ID('dbo.lms_notification_reads','U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_notification_reads (
    notification_id BIGINT NOT NULL,
    recipient_role VARCHAR(20) NOT NULL CHECK (recipient_role IN ('student','teacher','admin')),
    recipient_npk VARCHAR(50) NOT NULL,
    read_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT PK_lms_notification_reads PRIMARY KEY CLUSTERED(notification_id,recipient_role,recipient_npk),
    CONSTRAINT FK_lms_notification_reads_notification FOREIGN KEY(notification_id)
      REFERENCES dbo.lms_notifications(notification_id) ON DELETE CASCADE
  );
END;
GO

IF OBJECT_ID('dbo.lms_notification_email_outbox','U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_notification_email_outbox (
    email_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    notification_id BIGINT NOT NULL,
    recipient_email NVARCHAR(254) NOT NULL,
    recipient_name NVARCHAR(150) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
      CHECK (status IN ('pending','processing','retry','sent','failed')),
    attempts INT NOT NULL DEFAULT 0,
    next_attempt_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    locked_at DATETIME2 NULL,
    sent_at DATETIME2 NULL,
    last_error NVARCHAR(500) NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT FK_lms_notification_email_outbox_notification FOREIGN KEY(notification_id)
      REFERENCES dbo.lms_notifications(notification_id) ON DELETE CASCADE
  );
END;
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.lms_notifications') AND name='IX_lms_notifications_audience_created')
  CREATE INDEX IX_lms_notifications_audience_created ON dbo.lms_notifications(audience_role,created_at DESC,notification_id DESC)
    INCLUDE(title,category,priority,sender_name,sender_role,email_requested);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.lms_notification_reads') AND name='IX_lms_notification_reads_user')
  CREATE INDEX IX_lms_notification_reads_user ON dbo.lms_notification_reads(recipient_role,recipient_npk,notification_id);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.lms_notification_email_outbox') AND name='IX_lms_notification_email_outbox_queue')
  CREATE INDEX IX_lms_notification_email_outbox_queue ON dbo.lms_notification_email_outbox(status,next_attempt_at,email_id)
    INCLUDE(notification_id,recipient_email,recipient_name,attempts,locked_at);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID('dbo.lms_notification_email_outbox') AND name='UX_lms_notification_email_outbox_recipient')
  CREATE UNIQUE INDEX UX_lms_notification_email_outbox_recipient
    ON dbo.lms_notification_email_outbox(notification_id,recipient_email);
GO
