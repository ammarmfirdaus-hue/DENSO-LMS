
CREATE TABLE IF NOT EXISTS student_accounts (
  student_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  npk VARCHAR(50) NOT NULL UNIQUE,
  fullname VARCHAR(150) NOT NULL,
  email VARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  department VARCHAR(150) NULL,
  position_name VARCHAR(100) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
  course_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  course_code VARCHAR(30) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  category ENUM('Wajib','Pilihan') NOT NULL DEFAULT 'Wajib',
  thumbnail_path VARCHAR(500) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS activity_logs (
  activity_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NULL,
  activity_type VARCHAR(50) NOT NULL,
  description VARCHAR(500) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_activity FOREIGN KEY(student_id) REFERENCES student_accounts(student_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_rate_limits (
  ip_hash CHAR(64) NOT NULL PRIMARY KEY,
  attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  window_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  locked_until DATETIME NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
