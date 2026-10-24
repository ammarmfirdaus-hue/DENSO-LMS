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
