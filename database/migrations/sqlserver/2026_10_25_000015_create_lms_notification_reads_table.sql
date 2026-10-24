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
