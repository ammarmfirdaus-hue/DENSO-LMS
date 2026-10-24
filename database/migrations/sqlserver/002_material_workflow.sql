/* Add the reviewer account role without changing existing administrator/teacher accounts. */
DECLARE @dropRoleChecks NVARCHAR(MAX) = N'';
SELECT @dropRoleChecks += N'ALTER TABLE dbo.employee_accounts DROP CONSTRAINT ' + QUOTENAME(cc.name) + N';'
FROM sys.check_constraints cc
WHERE cc.parent_object_id = OBJECT_ID(N'dbo.employee_accounts')
  AND cc.definition LIKE N'%role%';
IF @dropRoleChecks <> N'' EXEC sys.sp_executesql @dropRoleChecks;
ALTER TABLE dbo.employee_accounts WITH CHECK ADD CONSTRAINT CK_employee_accounts_role
  CHECK (role IN ('admin','teacher','reviewer'));
GO

IF COL_LENGTH(N'dbo.courses', N'assigned_trainer_npk') IS NULL
  ALTER TABLE dbo.courses ADD assigned_trainer_npk VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name=N'FK_courses_assigned_trainer')
  ALTER TABLE dbo.courses WITH CHECK ADD CONSTRAINT FK_courses_assigned_trainer
    FOREIGN KEY(assigned_trainer_npk) REFERENCES dbo.employee_accounts(employee_npk);
GO

IF OBJECT_ID(N'dbo.material_submissions', N'U') IS NULL
CREATE TABLE dbo.material_submissions (
  submission_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
  session_id BIGINT NOT NULL,
  title NVARCHAR(200) NOT NULL,
  material_type VARCHAR(20) NOT NULL CHECK (material_type IN ('text','video','pdf','image','link','audio','document')),
  content NVARCHAR(MAX) NULL,
  file_path NVARCHAR(500) NULL,
  original_file_name NVARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status VARCHAR(24) NOT NULL DEFAULT 'pending_admin'
    CHECK (status IN ('pending_admin','pending_review','rejected','approved','published')),
  submitted_by VARCHAR(50) NOT NULL,
  reviewer_npk VARCHAR(50) NULL,
  reviewer_comment NVARCHAR(2000) NULL,
  admin_comment NVARCHAR(2000) NULL,
  reviewed_at DATETIME2 NULL,
  published_material_id BIGINT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_material_submissions_session FOREIGN KEY(session_id) REFERENCES dbo.course_sessions(session_id),
  CONSTRAINT FK_material_submissions_submitter FOREIGN KEY(submitted_by) REFERENCES dbo.employee_accounts(employee_npk),
  CONSTRAINT FK_material_submissions_reviewer FOREIGN KEY(reviewer_npk) REFERENCES dbo.employee_accounts(employee_npk)
);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_material_submissions_status_created' AND object_id=OBJECT_ID(N'dbo.material_submissions'))
  CREATE INDEX IX_material_submissions_status_created ON dbo.material_submissions(status, created_at DESC);
GO

IF OBJECT_ID(N'dbo.material_review_links', N'U') IS NULL
CREATE TABLE dbo.material_review_links (
  review_link_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
  submission_id BIGINT NOT NULL,
  reviewer_npk VARCHAR(50) NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME2 NOT NULL,
  consumed_at DATETIME2 NULL,
  revoked_at DATETIME2 NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_material_review_links_submission FOREIGN KEY(submission_id) REFERENCES dbo.material_submissions(submission_id),
  CONSTRAINT FK_material_review_links_reviewer FOREIGN KEY(reviewer_npk) REFERENCES dbo.employee_accounts(employee_npk)
);
GO
