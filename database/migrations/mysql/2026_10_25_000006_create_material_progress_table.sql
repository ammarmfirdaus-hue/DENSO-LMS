CREATE TABLE IF NOT EXISTS material_progress (
  progress_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  material_id BIGINT UNSIGNED NOT NULL,
  progress_status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  completed_at DATETIME NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_material(student_id,material_id),
  CONSTRAINT fk_progress_student FOREIGN KEY(student_id) REFERENCES student_accounts(student_id),
  CONSTRAINT fk_progress_material FOREIGN KEY(material_id) REFERENCES course_materials(material_id)
) ENGINE=InnoDB;
