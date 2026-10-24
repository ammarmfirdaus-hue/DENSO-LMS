CREATE TABLE IF NOT EXISTS course_enrollments (
  enrollment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  course_id BIGINT UNSIGNED NOT NULL,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  enrollment_status ENUM('active','completed','dropped') NOT NULL DEFAULT 'active',
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_course(student_id,course_id),
  CONSTRAINT fk_enroll_student FOREIGN KEY(student_id) REFERENCES student_accounts(student_id),
  CONSTRAINT fk_enroll_course FOREIGN KEY(course_id) REFERENCES courses(course_id)
) ENGINE=InnoDB;
