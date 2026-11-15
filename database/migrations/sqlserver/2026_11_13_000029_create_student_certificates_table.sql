-- Migration: Create dbo.student_certificates for Admin/Teacher -> Student certificates, awards & documents
IF OBJECT_ID('dbo.student_certificates', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.student_certificates (
        certificate_id     BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        student_id         BIGINT NOT NULL,
        student_npk        VARCHAR(50) NOT NULL,
        student_name       NVARCHAR(150) NOT NULL,
        course_id          BIGINT NULL,
        course_title       NVARCHAR(200) NULL,
        title              NVARCHAR(200) NOT NULL,
        award_type         VARCHAR(40) NOT NULL CONSTRAINT DF_student_certificates_type DEFAULT 'certificate',
        certificate_no     VARCHAR(80) NULL,
        description        NVARCHAR(1000) NULL,
        file_path          NVARCHAR(500) NOT NULL,
        original_filename  NVARCHAR(255) NOT NULL,
        mime_type          VARCHAR(100) NOT NULL,
        file_size          BIGINT NOT NULL CONSTRAINT DF_student_certificates_size DEFAULT 0,
        issued_by_npk      VARCHAR(50) NOT NULL,
        issued_by_name     NVARCHAR(150) NOT NULL,
        issued_at          DATETIME2 NOT NULL CONSTRAINT DF_student_certificates_issued_at DEFAULT SYSUTCDATETIME()
    );

    CREATE INDEX IX_student_certificates_student_issued
        ON dbo.student_certificates (student_id, issued_at DESC);

    CREATE INDEX IX_student_certificates_issuer_issued
        ON dbo.student_certificates (issued_by_npk, issued_at DESC);
END
GO

