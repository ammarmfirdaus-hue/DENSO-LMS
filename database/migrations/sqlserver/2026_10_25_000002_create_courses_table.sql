IF OBJECT_ID('dbo.courses','U') IS NULL CREATE TABLE dbo.courses (
  course_id BIGINT IDENTITY(1,1) PRIMARY KEY, course_code VARCHAR(30) NOT NULL UNIQUE,
  title NVARCHAR(200) NOT NULL, description NVARCHAR(4000) NOT NULL DEFAULT '',
  category VARCHAR(20) NOT NULL DEFAULT 'Wajib', status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_by VARCHAR(50) NOT NULL, updated_by VARCHAR(50) NOT NULL,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(), updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_courses_creator FOREIGN KEY(created_by) REFERENCES dbo.employee_accounts(employee_npk),
  CONSTRAINT FK_courses_updater FOREIGN KEY(updated_by) REFERENCES dbo.employee_accounts(employee_npk)
);
