IF COL_LENGTH(N'dbo.student_progress_snapshots', N'quiz_score') IS NULL
  ALTER TABLE dbo.student_progress_snapshots ADD quiz_score DECIMAL(5,2) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name=N'CK_student_progress_snapshots_quiz_score')
  ALTER TABLE dbo.student_progress_snapshots WITH CHECK ADD CONSTRAINT CK_student_progress_snapshots_quiz_score CHECK(quiz_score IS NULL OR (quiz_score >= 0 AND quiz_score <= 100));
GO
