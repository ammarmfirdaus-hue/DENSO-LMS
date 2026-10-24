CREATE TABLE IF NOT EXISTS course_materials (
  material_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  source_material_id BIGINT NULL UNIQUE,
  session_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  material_type ENUM('text','video','pdf','image','link','audio','document') NOT NULL DEFAULT 'text',
  content MEDIUMTEXT NULL,
  file_path VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_material_session FOREIGN KEY(session_id) REFERENCES course_sessions(session_id)
) ENGINE=InnoDB;
