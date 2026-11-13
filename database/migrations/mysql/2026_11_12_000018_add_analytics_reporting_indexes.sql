-- Add reporting and telemetry indexes for Student analytics queries.
SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_enrollments' AND INDEX_NAME='idx_course_enrollments_status_updated')=0,
  'CREATE INDEX idx_course_enrollments_status_updated ON course_enrollments(enrollment_status, updated_at)',
  'SELECT 1'
);
PREPARE analytics_idx_stmt FROM @ddl; EXECUTE analytics_idx_stmt; DEALLOCATE PREPARE analytics_idx_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='activity_logs' AND INDEX_NAME='idx_student_activity_logs_created_type')=0,
  'CREATE INDEX idx_student_activity_logs_created_type ON activity_logs(created_at, activity_type)',
  'SELECT 1'
);
PREPARE analytics_idx_stmt FROM @ddl; EXECUTE analytics_idx_stmt; DEALLOCATE PREPARE analytics_idx_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_quiz_attempts' AND INDEX_NAME='idx_course_quiz_attempts_submitted_score')=0,
  'CREATE INDEX idx_course_quiz_attempts_submitted_score ON course_quiz_attempts(submitted_at, score)',
  'SELECT 1'
);
PREPARE analytics_idx_stmt FROM @ddl; EXECUTE analytics_idx_stmt; DEALLOCATE PREPARE analytics_idx_stmt;

SET @ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='course_materials' AND INDEX_NAME='idx_course_materials_active_type')=0,
  'CREATE INDEX idx_course_materials_active_type ON course_materials(is_active, material_type)',
  'SELECT 1'
);
PREPARE analytics_idx_stmt FROM @ddl; EXECUTE analytics_idx_stmt; DEALLOCATE PREPARE analytics_idx_stmt;

