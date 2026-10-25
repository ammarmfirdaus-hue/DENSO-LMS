SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_enrollments' AND COLUMN_NAME='quiz_score')=0,
  'ALTER TABLE course_enrollments ADD COLUMN quiz_score DECIMAL(5,2) NULL AFTER score',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='course_enrollments' AND CONSTRAINT_NAME='ck_course_enrollments_quiz_score')=0,
  'ALTER TABLE course_enrollments ADD CONSTRAINT ck_course_enrollments_quiz_score CHECK(quiz_score IS NULL OR (quiz_score >= 0 AND quiz_score <= 100))',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;
