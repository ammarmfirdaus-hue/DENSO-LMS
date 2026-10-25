-- Teacher study-mode accounts use the same learning tables but cannot sign in
-- with a Student password. Existing rows remain ordinary Student accounts.
SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='student_accounts' AND COLUMN_NAME='account_type')=0,
  'ALTER TABLE student_accounts ADD COLUMN account_type ENUM(''student'',''teacher_shadow'') NOT NULL DEFAULT ''student'' AFTER email',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='student_accounts' AND INDEX_NAME='idx_student_accounts_type_status')=0,
  'CREATE INDEX idx_student_accounts_type_status ON student_accounts(account_type,status,student_id)',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;
