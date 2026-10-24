IF OBJECT_ID('dbo.student_progress_snapshots','U') IS NULL CREATE TABLE dbo.student_progress_snapshots (
  snapshot_id BIGINT IDENTITY(1,1) PRIMARY KEY,
  student_npk VARCHAR(50) NOT NULL,
  course_code VARCHAR(30) NOT NULL,
  progress TINYINT NOT NULL,
  enrollment_status VARCHAR(20) NOT NULL,
  source_updated_at DATETIME2 NOT NULL,
  synced_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT UQ_student_progress_snapshot UNIQUE(student_npk,course_code)
);
