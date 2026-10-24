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
