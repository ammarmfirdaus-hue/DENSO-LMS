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
