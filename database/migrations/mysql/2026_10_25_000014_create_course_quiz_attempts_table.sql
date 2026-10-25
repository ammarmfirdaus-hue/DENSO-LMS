CREATE TABLE IF NOT EXISTS course_quiz_attempts (
  attempt_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  quiz_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  score DECIMAL(5,2) NOT NULL,
  correct_count SMALLINT UNSIGNED NOT NULL,
  total_questions SMALLINT UNSIGNED NOT NULL,
  answers JSON NOT NULL,
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_course_quiz_attempt_once(quiz_id,student_id),
  KEY ix_course_quiz_attempt_student(student_id,submitted_at),
  CONSTRAINT fk_course_quiz_attempt_quiz FOREIGN KEY(quiz_id) REFERENCES course_quizzes(quiz_id) ON DELETE RESTRICT,
  CONSTRAINT fk_course_quiz_attempt_student FOREIGN KEY(student_id) REFERENCES student_accounts(student_id),
  CONSTRAINT ck_course_quiz_attempt_score CHECK(score >= 0 AND score <= 100)
) ENGINE=InnoDB;
