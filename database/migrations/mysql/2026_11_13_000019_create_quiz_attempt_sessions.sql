ALTER TABLE course_quizzes
  ADD COLUMN duration_seconds INT UNSIGNED NOT NULL DEFAULT 1800;

CREATE TABLE IF NOT EXISTS course_quiz_attempt_sessions (
  session_token CHAR(36) NOT NULL PRIMARY KEY,
  quiz_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  started_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  status ENUM('active','submitted','expired','revoked') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_quiz_attempt_session_student (quiz_id, student_id, status, expires_at),
  CONSTRAINT fk_quiz_attempt_session_quiz FOREIGN KEY(quiz_id) REFERENCES course_quizzes(quiz_id) ON DELETE CASCADE,
  CONSTRAINT fk_quiz_attempt_session_student FOREIGN KEY(student_id) REFERENCES student_accounts(student_id) ON DELETE CASCADE
) ENGINE=InnoDB;
