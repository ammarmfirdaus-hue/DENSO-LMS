CREATE TABLE IF NOT EXISTS course_quiz_questions (
  question_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  quiz_id BIGINT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  options JSON NOT NULL,
  correct_option TINYINT UNSIGNED NOT NULL,
  points DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_course_quiz_question_order(quiz_id,sort_order),
  CONSTRAINT fk_course_quiz_question_quiz FOREIGN KEY(quiz_id) REFERENCES course_quizzes(quiz_id) ON DELETE CASCADE,
  CONSTRAINT ck_course_quiz_correct_option CHECK(correct_option <= 5),
  CONSTRAINT ck_course_quiz_question_points CHECK(points > 0 AND points <= 100)
) ENGINE=InnoDB;
