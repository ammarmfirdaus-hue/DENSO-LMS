CREATE TABLE IF NOT EXISTS course_sessions (
  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  source_session_id BIGINT NULL UNIQUE,
  course_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_sessions_course FOREIGN KEY(course_id) REFERENCES courses(course_id)
) ENGINE=InnoDB;
