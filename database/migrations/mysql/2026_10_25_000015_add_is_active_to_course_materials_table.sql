SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_materials' AND COLUMN_NAME='is_active')=0,
  'ALTER TABLE course_materials ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;
