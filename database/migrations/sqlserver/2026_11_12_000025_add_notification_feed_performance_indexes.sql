-- Optimized Covering Index for Notification Feeds and Unread Counting
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.lms_notifications') AND name=N'IX_lms_notifications_feed_perf')
  CREATE INDEX IX_lms_notifications_feed_perf
    ON dbo.lms_notifications(audience_role, recipient_npk, created_at DESC, notification_id DESC)
    INCLUDE(title, message, category, priority, sender_npk, sender_name, sender_role, show_popup);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.lms_notification_reads') AND name=N'IX_lms_notification_reads_lookup_perf')
  CREATE INDEX IX_lms_notification_reads_lookup_perf
    ON dbo.lms_notification_reads(notification_id, recipient_role, recipient_npk);
GO

