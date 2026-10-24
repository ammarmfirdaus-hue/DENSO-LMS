CREATE TABLE IF NOT EXISTS activity_logs (
  activity_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NULL,
  activity_type VARCHAR(50) NOT NULL,
  description VARCHAR(500) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_activity FOREIGN KEY(student_id) REFERENCES student_accounts(student_id)
) ENGINE=InnoDB;
