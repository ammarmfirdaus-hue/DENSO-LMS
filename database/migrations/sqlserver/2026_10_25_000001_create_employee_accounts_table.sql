IF OBJECT_ID('dbo.employee_accounts','U') IS NULL CREATE TABLE dbo.employee_accounts (
  employee_npk VARCHAR(50) NOT NULL PRIMARY KEY,
  fullname NVARCHAR(150) NOT NULL,
  email NVARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL CHECK(role IN ('admin','teacher')),
  department NVARCHAR(100) NULL,
  position NVARCHAR(100) NULL,
  phone_number VARCHAR(30) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK(status IN ('active','inactive')),
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(), updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);
