IF OBJECT_ID('dbo.lms_notification_deletes','U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_notification_deletes (
    notification_id BIGINT NOT NULL,
    recipient_role VARCHAR(20) NOT NULL,
    recipient_npk VARCHAR(50) NOT NULL,
    deleted_at DATETIME2 NOT NULL CONSTRAINT DF_lms_notification_deletes_at DEFAULT SYSUTCDATETIME(),
    CONSTRAINT PK_lms_notification_deletes PRIMARY KEY CLUSTERED(notification_id, recipient_role, recipient_npk),
    CONSTRAINT FK_lms_notification_deletes_notification FOREIGN KEY(notification_id)
      REFERENCES dbo.lms_notifications(notification_id) ON DELETE CASCADE
  );
END;

IF NOT EXISTS (
  SELECT 1
  FROM sys.indexes
  WHERE object_id = OBJECT_ID('dbo.lms_notification_deletes')
    AND name = 'IX_lms_notification_deletes_user'
)
BEGIN
  CREATE INDEX IX_lms_notification_deletes_user
    ON dbo.lms_notification_deletes(recipient_role, recipient_npk, notification_id);
END;

