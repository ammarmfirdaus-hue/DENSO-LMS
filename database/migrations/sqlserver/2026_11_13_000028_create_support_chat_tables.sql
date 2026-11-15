IF OBJECT_ID(N'dbo.support_chat_messages', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.support_chat_messages (
        message_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        student_id BIGINT NOT NULL,
        student_npk VARCHAR(50) NOT NULL,
        student_name NVARCHAR(120) NOT NULL,
        sender_role VARCHAR(20) NOT NULL,
        sender_npk VARCHAR(50) NOT NULL,
        sender_name NVARCHAR(120) NOT NULL,
        message_text NVARCHAR(1000) NOT NULL,
        is_read BIT NOT NULL CONSTRAINT DF_support_chat_messages_is_read DEFAULT 0,
        created_at DATETIME2 NOT NULL CONSTRAINT DF_support_chat_messages_created_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT CK_support_chat_messages_sender_role CHECK (sender_role IN ('student', 'admin'))
    );
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_support_chat_student_created' AND object_id = OBJECT_ID(N'dbo.support_chat_messages')
)
BEGIN
    CREATE INDEX IX_support_chat_student_created
        ON dbo.support_chat_messages (student_id, created_at DESC, message_id DESC);
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_support_chat_unread' AND object_id = OBJECT_ID(N'dbo.support_chat_messages')
)
BEGIN
    CREATE INDEX IX_support_chat_unread
        ON dbo.support_chat_messages (sender_role, is_read, student_id);
END;
GO

IF OBJECT_ID(N'dbo.support_admin_presence', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.support_admin_presence (
        admin_npk VARCHAR(50) NOT NULL PRIMARY KEY,
        admin_name NVARCHAR(120) NOT NULL,
        last_seen_at DATETIME2 NOT NULL CONSTRAINT DF_support_admin_presence_last_seen DEFAULT SYSUTCDATETIME()
    );
END;
GO

