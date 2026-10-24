USE DX_LMS_AdminTeacher;
GO

/* Tambahkan kolom detail profil reviewer (HRD / Petinggi) pada dbo.employee_accounts jika belum ada */
IF COL_LENGTH(N'dbo.employee_accounts', N'department') IS NULL
    ALTER TABLE dbo.employee_accounts ADD department NVARCHAR(100) NULL;
GO

IF COL_LENGTH(N'dbo.employee_accounts', N'position') IS NULL
    ALTER TABLE dbo.employee_accounts ADD position NVARCHAR(100) NULL;
GO

IF COL_LENGTH(N'dbo.employee_accounts', N'phone_number') IS NULL
    ALTER TABLE dbo.employee_accounts ADD phone_number VARCHAR(30) NULL;
GO
