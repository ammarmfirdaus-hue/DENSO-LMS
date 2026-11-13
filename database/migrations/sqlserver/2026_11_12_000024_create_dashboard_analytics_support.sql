IF OBJECT_ID(N'dbo.dashboard_daily_snapshots', N'U') IS NULL
BEGIN
  CREATE TABLE dbo.dashboard_daily_snapshots (
    snapshot_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    snapshot_date DATE NOT NULL,
    scope_key VARCHAR(64) NOT NULL,
    active_courses INT NOT NULL DEFAULT 0,
    active_employees INT NOT NULL DEFAULT 0,
    active_students INT NOT NULL DEFAULT 0,
    active_sessions INT NOT NULL DEFAULT 0,
    published_materials INT NOT NULL DEFAULT 0,
    pending_submissions INT NOT NULL DEFAULT 0,
    completed_enrollments INT NOT NULL DEFAULT 0,
    daily_activities INT NOT NULL DEFAULT 0,
    avg_progress DECIMAL(5,2) NOT NULL DEFAULT 0,
    avg_quiz_score DECIMAL(5,2) NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT UQ_dashboard_daily_snapshots_date_scope UNIQUE(snapshot_date, scope_key),
    CONSTRAINT CK_dashboard_daily_snapshots_counts CHECK(
      active_courses >= 0 AND active_employees >= 0 AND active_students >= 0
      AND active_sessions >= 0 AND published_materials >= 0 AND pending_submissions >= 0
      AND completed_enrollments >= 0 AND daily_activities >= 0
    ),
    CONSTRAINT CK_dashboard_daily_snapshots_progress CHECK(avg_progress >= 0 AND avg_progress <= 100),
    CONSTRAINT CK_dashboard_daily_snapshots_quiz_score CHECK(avg_quiz_score IS NULL OR (avg_quiz_score >= 0 AND avg_quiz_score <= 100))
  );
END;
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.dashboard_daily_snapshots') AND name=N'IX_dashboard_daily_snapshots_scope_date')
  CREATE INDEX IX_dashboard_daily_snapshots_scope_date
    ON dbo.dashboard_daily_snapshots(scope_key, snapshot_date DESC);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.activity_logs') AND name=N'IX_activity_logs_created_type')
  CREATE INDEX IX_activity_logs_created_type
    ON dbo.activity_logs(created_at DESC, activity_type)
    INCLUDE(employee_npk);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.student_progress_snapshots') AND name=N'IX_student_progress_snapshots_status_updated')
  CREATE INDEX IX_student_progress_snapshots_status_updated
    ON dbo.student_progress_snapshots(enrollment_status, source_updated_at DESC)
    INCLUDE(course_code, progress, score, quiz_score, student_npk);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.material_submissions') AND name=N'IX_material_submissions_type_status')
  CREATE INDEX IX_material_submissions_type_status
    ON dbo.material_submissions(material_type, status, created_at DESC)
    INCLUDE(session_id, submitted_by);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id=OBJECT_ID(N'dbo.course_materials') AND name=N'IX_course_materials_type_created')
  CREATE INDEX IX_course_materials_type_created
    ON dbo.course_materials(material_type, created_at DESC)
    INCLUDE(session_id);
GO

