CREATE TABLE IF NOT EXISTS course_quizzes (
  quiz_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  course_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  passing_score DECIMAL(5,2) NOT NULL DEFAULT 75.00,
  status ENUM('draft','published','inactive') NOT NULL DEFAULT 'draft',
  created_by VARCHAR(50) NOT NULL,
  updated_by VARCHAR(50) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_course_quiz(course_id),
  CONSTRAINT fk_course_quiz_course FOREIGN KEY(course_id) REFERENCES courses(course_id),
  CONSTRAINT ck_course_quiz_passing_score CHECK(passing_score >= 0 AND passing_score <= 100)
) ENGINE=InnoDB;
