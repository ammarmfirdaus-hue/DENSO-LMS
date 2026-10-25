IF OBJECT_ID(N'dbo.lms_admin_jobs',N'U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_admin_jobs (
    job_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    job_type VARCHAR(40) NOT NULL,
    status VARCHAR(30) NOT NULL CONSTRAINT DF_lms_admin_jobs_status DEFAULT 'queued',
    created_by VARCHAR(50) NOT NULL,
    created_by_role VARCHAR(20) NOT NULL,
    payload_ciphertext VARBINARY(MAX) NULL,
    payload_iv VARBINARY(12) NULL,
    payload_tag VARBINARY(16) NULL,
    total_items INT NOT NULL,
    processed_items INT NOT NULL CONSTRAINT DF_lms_admin_jobs_processed DEFAULT 0,
    success_count INT NOT NULL CONSTRAINT DF_lms_admin_jobs_success DEFAULT 0,
    failure_count INT NOT NULL CONSTRAINT DF_lms_admin_jobs_failure DEFAULT 0,
    cancel_requested BIT NOT NULL CONSTRAINT DF_lms_admin_jobs_cancel DEFAULT 0,
    current_item NVARCHAR(300) NULL,
    created_at DATETIME2 NOT NULL CONSTRAINT DF_lms_admin_jobs_created DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NOT NULL CONSTRAINT DF_lms_admin_jobs_updated DEFAULT SYSUTCDATETIME(),
    started_at DATETIME2 NULL,
    finished_at DATETIME2 NULL,
    CONSTRAINT FK_lms_admin_jobs_creator FOREIGN KEY(created_by) REFERENCES dbo.employee_accounts(employee_npk),
    CONSTRAINT CK_lms_admin_jobs_status CHECK(status IN ('queued','running','stopping','stopped','completed','completed_with_errors','failed'))
  );
  CREATE INDEX IX_lms_admin_jobs_creator_created ON dbo.lms_admin_jobs(created_by,created_at DESC);
  CREATE INDEX IX_lms_admin_jobs_queue ON dbo.lms_admin_jobs(status,job_id);
END;
GO

IF OBJECT_ID(N'dbo.lms_admin_job_events',N'U') IS NULL
BEGIN
  CREATE TABLE dbo.lms_admin_job_events (
    event_id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    job_id BIGINT NOT NULL,
    item_no INT NOT NULL,
    item_label NVARCHAR(300) NOT NULL,
    item_status VARCHAR(20) NOT NULL,
    message NVARCHAR(500) NULL,
    created_at DATETIME2 NOT NULL CONSTRAINT DF_lms_admin_job_events_created DEFAULT SYSUTCDATETIME(),
    CONSTRAINT FK_lms_admin_job_events_job FOREIGN KEY(job_id) REFERENCES dbo.lms_admin_jobs(job_id) ON DELETE CASCADE,
    CONSTRAINT CK_lms_admin_job_events_status CHECK(item_status IN ('processing','completed','failed','stopped'))
  );
  CREATE INDEX IX_lms_admin_job_events_job_event ON dbo.lms_admin_job_events(job_id,event_id);
END;
GO
