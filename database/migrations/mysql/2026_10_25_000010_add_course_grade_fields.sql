SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_enrollments' AND COLUMN_NAME='score')=0,
  'ALTER TABLE course_enrollments ADD COLUMN score DECIMAL(5,2) NULL AFTER progress',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_enrollments' AND COLUMN_NAME='grade_feedback')=0,
  'ALTER TABLE course_enrollments ADD COLUMN grade_feedback TEXT NULL AFTER score',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_enrollments' AND COLUMN_NAME='graded_at')=0,
  'ALTER TABLE course_enrollments ADD COLUMN graded_at DATETIME NULL AFTER grade_feedback',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;
