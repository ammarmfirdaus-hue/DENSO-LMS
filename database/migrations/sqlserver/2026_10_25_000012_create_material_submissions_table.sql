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
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.material_submissions') AND name='IX_material_submissions_status_created')
  CREATE INDEX IX_material_submissions_status_created ON dbo.material_submissions(status, created_at DESC);
GO
