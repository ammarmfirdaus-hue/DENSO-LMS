CREATE TABLE IF NOT EXISTS course_enrollment_requests (
  request_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  course_id BIGINT UNSIGNED NOT NULL,
  request_type ENUM('student_request','teacher_recommendation') NOT NULL,
  requested_by_npk VARCHAR(50) NOT NULL,
  requested_by_role ENUM('student','teacher') NOT NULL,
  request_note VARCHAR(1000) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  decision_by_npk VARCHAR(50) NULL,
  decision_note VARCHAR(1000) NULL,
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decided_at DATETIME NULL,
  pending_key VARCHAR(50) GENERATED ALWAYS AS (
    CASE WHEN status='pending' THEN CONCAT(student_id, ':', course_id) ELSE NULL END
  ) STORED,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enrollment_request_pending(pending_key),
  KEY idx_enrollment_requests_status_requested(status,requested_at),
  KEY idx_enrollment_requests_student_course(student_id,course_id,request_id),
  KEY idx_enrollment_requests_requester(requested_by_npk,request_type,request_id),
  CONSTRAINT fk_enrollment_request_student FOREIGN KEY(student_id) REFERENCES student_accounts(student_id),
  CONSTRAINT fk_enrollment_request_course FOREIGN KEY(course_id) REFERENCES courses(course_id)
) ENGINE=InnoDB;
