IF OBJECT_ID('dbo.course_materials','U') IS NULL CREATE TABLE dbo.course_materials (
  material_id BIGINT IDENTITY(1,1) PRIMARY KEY, session_id BIGINT NOT NULL,
  title NVARCHAR(200) NOT NULL, material_type VARCHAR(20) NOT NULL,
  content NVARCHAR(MAX) NULL, file_path NVARCHAR(500) NULL, sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  CONSTRAINT FK_materials_session FOREIGN KEY(session_id) REFERENCES dbo.course_sessions(session_id)
);
