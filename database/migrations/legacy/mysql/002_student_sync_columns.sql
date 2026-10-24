-- Keep existing Student installations compatible with catalog sync from Admin/Teacher.
SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_sessions' AND COLUMN_NAME='source_session_id')=0,
  'ALTER TABLE course_sessions ADD COLUMN source_session_id BIGINT NULL UNIQUE AFTER session_id',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_materials' AND COLUMN_NAME='source_material_id')=0,
  'ALTER TABLE course_materials ADD COLUMN source_material_id BIGINT NULL UNIQUE AFTER material_id',
  'SELECT 1'
);
PREPARE migration_stmt FROM @ddl; EXECUTE migration_stmt; DEALLOCATE PREPARE migration_stmt;

ALTER TABLE course_materials
  MODIFY material_type ENUM('text','video','pdf','image','link','audio','document') NOT NULL DEFAULT 'text';
