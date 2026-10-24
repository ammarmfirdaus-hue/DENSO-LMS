IF OBJECT_ID('dbo.activity_logs','U') IS NULL CREATE TABLE dbo.activity_logs (
  activity_id BIGINT IDENTITY(1,1) PRIMARY KEY, employee_npk VARCHAR(50) NULL,
  activity_type VARCHAR(50) NOT NULL, description NVARCHAR(500) NOT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_activity_employee FOREIGN KEY(employee_npk) REFERENCES dbo.employee_accounts(employee_npk)
);
