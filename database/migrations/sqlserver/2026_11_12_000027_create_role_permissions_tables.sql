IF OBJECT_ID(N'dbo.lms_role_definitions', N'U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_role_definitions (
    role_key VARCHAR(30) PRIMARY KEY,
    display_name NVARCHAR(100) NOT NULL,
    description NVARCHAR(500) NOT NULL DEFAULT N'',
    portal_target VARCHAR(30) NOT NULL DEFAULT 'staff',
    can_access_staff_portal BIT NOT NULL DEFAULT 0,
    updated_by VARCHAR(50) NULL,
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT CK_lms_role_definitions_portal CHECK (portal_target IN ('staff', 'reviewer', 'student'))
  );
END;

IF OBJECT_ID(N'dbo.lms_role_permissions', N'U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_role_permissions (
    role_key VARCHAR(30) NOT NULL,
    permission_key VARCHAR(80) NOT NULL,
    is_allowed BIT NOT NULL DEFAULT 0,
    updated_by VARCHAR(50) NULL,
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT PK_lms_role_permissions PRIMARY KEY (role_key, permission_key),
    CONSTRAINT FK_lms_role_permissions_role FOREIGN KEY (role_key)
      REFERENCES dbo.lms_role_definitions(role_key) ON DELETE CASCADE
  );
END;

IF OBJECT_ID(N'dbo.lms_role_audit_logs', N'U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_role_audit_logs (
    audit_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    role_key VARCHAR(30) NOT NULL,
    changed_by VARCHAR(50) NOT NULL,
    summary NVARCHAR(500) NOT NULL,
    changes_json NVARCHAR(MAX) NOT NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
  );
  CREATE INDEX IX_lms_role_audit_logs_role_created
    ON dbo.lms_role_audit_logs(role_key, created_at DESC);
END;

