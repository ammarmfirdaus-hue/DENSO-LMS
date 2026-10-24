/* Add the reviewer account role without changing existing administrator/teacher accounts. */
DECLARE @dropRoleChecks NVARCHAR(MAX) = N'';
SELECT @dropRoleChecks += N'ALTER TABLE dbo.employee_accounts DROP CONSTRAINT ' + QUOTENAME(cc.name) + N';'
FROM sys.check_constraints cc
WHERE cc.parent_object_id = OBJECT_ID(N'dbo.employee_accounts')
  AND cc.definition LIKE N'%role%';
IF @dropRoleChecks <> N'' EXEC sys.sp_executesql @dropRoleChecks;
ALTER TABLE dbo.employee_accounts WITH CHECK ADD CONSTRAINT CK_employee_accounts_role
  CHECK (role IN ('admin','teacher','reviewer'));
GO

IF COL_LENGTH(N'dbo.courses', N'assigned_trainer_npk') IS NULL
  ALTER TABLE dbo.courses ADD assigned_trainer_npk VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name=N'FK_courses_assigned_trainer')
  ALTER TABLE dbo.courses WITH CHECK ADD CONSTRAINT FK_courses_assigned_trainer
    FOREIGN KEY(assigned_trainer_npk) REFERENCES dbo.employee_accounts(employee_npk);
GO
