IF COL_LENGTH(N'dbo.student_progress_snapshots', N'score') IS NULL
  ALTER TABLE dbo.student_progress_snapshots ADD score DECIMAL(5,2) NULL;
GO
IF COL_LENGTH(N'dbo.student_progress_snapshots', N'grade_feedback') IS NULL
  ALTER TABLE dbo.student_progress_snapshots ADD grade_feedback NVARCHAR(1000) NULL;
GO
IF COL_LENGTH(N'dbo.student_progress_snapshots', N'graded_by') IS NULL
  ALTER TABLE dbo.student_progress_snapshots ADD graded_by VARCHAR(50) NULL;
GO
IF COL_LENGTH(N'dbo.student_progress_snapshots', N'graded_at') IS NULL
  ALTER TABLE dbo.student_progress_snapshots ADD graded_at DATETIME2 NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name=N'CK_student_progress_snapshots_score')
  ALTER TABLE dbo.student_progress_snapshots WITH CHECK ADD CONSTRAINT CK_student_progress_snapshots_score CHECK(score IS NULL OR (score >= 0 AND score <= 100));
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name=N'FK_student_progress_snapshots_graded_by')
  ALTER TABLE dbo.student_progress_snapshots WITH CHECK ADD CONSTRAINT FK_student_progress_snapshots_graded_by FOREIGN KEY(graded_by) REFERENCES dbo.employee_accounts(employee_npk);
GO
