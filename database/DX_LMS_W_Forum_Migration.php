<?php
/**
 * Migration & Seeder Script for Forum Modul
 * Creates DX_LMS_T_Forum_Topics, DX_LMS_T_Forum_Replies, DX_LMS_T_Forum_Likes
 * and seeds realistic Denso training forum discussions.
 */

require_once __DIR__ . '/../includes/DX_LMS_M_Db.php';

try {
    $pdo = getDBConnection();
    echo "Connected to SQL Server successfully.\n";

    // 1. Ensure Admin exists in DX_LMS_M_Employees
    $adminCheck = dbQueryOne("SELECT employee_id FROM DX_LMS_M_Employees WHERE npk = '99999'");
    if (!$adminCheck) {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        dbExecute(
            "INSERT INTO DX_LMS_M_Employees (npk, fullname, email, password, golongan, jabatan, bu_code, section, department, division, directorate, role, status)
             VALUES ('99999', 'Admin System', 'admin@denso.co.id', ?, 'Gol 4', 'Super Admin', 100, 'Administration', 'HR & GA', 'Corporate', 'Head Office', 'admin', 'Active')",
            [$hash]
        );
        echo "Created admin employee (99999).\n";
    }

    // 2. Create DX_LMS_T_Forum_Topics table
    $sqlTopics = "
    IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[DX_LMS_T_Forum_Topics]') AND type in (N'U'))
    BEGIN
        CREATE TABLE [dbo].[DX_LMS_T_Forum_Topics] (
            [topic_id] BIGINT IDENTITY(1,1) PRIMARY KEY,
            [course_id] VARCHAR(50) NOT NULL,
            [session_id] VARCHAR(50) NULL,
            [category_type] VARCHAR(50) NOT NULL DEFAULT 'materi',
            [author_npk] VARCHAR(50) NOT NULL,
            [author_name] VARCHAR(150) NOT NULL,
            [author_role] VARCHAR(50) NOT NULL DEFAULT 'student',
            [author_avatar] VARCHAR(255) NULL,
            [author_dept] VARCHAR(150) NULL,
            [title] NVARCHAR(255) NOT NULL,
            [content] NVARCHAR(MAX) NOT NULL,
            [attachment_path] NVARCHAR(500) NULL,
            [status] VARCHAR(20) NOT NULL DEFAULT 'open',
            [moderation_status] VARCHAR(20) NOT NULL DEFAULT 'approved',
            [likes_count] INT NOT NULL DEFAULT 0,
            [replies_count] INT NOT NULL DEFAULT 0,
            [is_pinned] BIT NOT NULL DEFAULT 0,
            [is_hot] BIT NOT NULL DEFAULT 0,
            [created_at] DATETIME2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
            [updated_at] DATETIME2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
            [created_by] VARCHAR(50) NULL,
            [updated_by] VARCHAR(50) NULL
        );
        CREATE INDEX IX_DX_LMS_T_Forum_Topics_course ON [dbo].[DX_LMS_T_Forum_Topics] ([course_id]);
        CREATE INDEX IX_DX_LMS_T_Forum_Topics_session ON [dbo].[DX_LMS_T_Forum_Topics] ([session_id]);
        CREATE INDEX IX_DX_LMS_T_Forum_Topics_category ON [dbo].[DX_LMS_T_Forum_Topics] ([category_type]);
        CREATE INDEX IX_DX_LMS_T_Forum_Topics_status ON [dbo].[DX_LMS_T_Forum_Topics] ([status]);
        PRINT 'Table DX_LMS_T_Forum_Topics created.';
    END
    ";
    $pdo->exec($sqlTopics);
    echo "DX_LMS_T_Forum_Topics verified/created.\n";

    // 3. Create DX_LMS_T_Forum_Replies table
    $sqlReplies = "
    IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[DX_LMS_T_Forum_Replies]') AND type in (N'U'))
    BEGIN
        CREATE TABLE [dbo].[DX_LMS_T_Forum_Replies] (
            [reply_id] BIGINT IDENTITY(1,1) PRIMARY KEY,
            [topic_id] BIGINT NOT NULL,
            [parent_reply_id] BIGINT NULL,
            [author_npk] VARCHAR(50) NOT NULL,
            [author_name] VARCHAR(150) NOT NULL,
            [author_role] VARCHAR(50) NOT NULL DEFAULT 'student',
            [author_avatar] VARCHAR(255) NULL,
            [author_dept] VARCHAR(150) NULL,
            [content] NVARCHAR(MAX) NOT NULL,
            [attachment_path] NVARCHAR(500) NULL,
            [is_solution] BIT NOT NULL DEFAULT 0,
            [likes_count] INT NOT NULL DEFAULT 0,
            [status] VARCHAR(20) NOT NULL DEFAULT 'active',
            [created_at] DATETIME2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
            [updated_at] DATETIME2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
            [created_by] VARCHAR(50) NULL,
            [updated_by] VARCHAR(50) NULL,
            CONSTRAINT FK_DX_LMS_T_Forum_Replies_topic FOREIGN KEY ([topic_id]) 
                REFERENCES [dbo].[DX_LMS_T_Forum_Topics] ([topic_id]) ON DELETE CASCADE
        );
        CREATE INDEX IX_DX_LMS_T_Forum_Replies_topic ON [dbo].[DX_LMS_T_Forum_Replies] ([topic_id]);
        CREATE INDEX IX_DX_LMS_T_Forum_Replies_author ON [dbo].[DX_LMS_T_Forum_Replies] ([author_npk]);
        PRINT 'Table DX_LMS_T_Forum_Replies created.';
    END
    ";
    $pdo->exec($sqlReplies);
    echo "DX_LMS_T_Forum_Replies verified/created.\n";

    // 4. Forum Likes table is deprecated and dropped to simplify architecture
    $pdo->exec("DROP TABLE IF EXISTS [dbo].[DX_LMS_T_Forum_Likes];");

    // 5. Seed initial topic if table empty
    $countTopics = dbQueryOne("SELECT COUNT(*) AS cnt FROM DX_LMS_T_Forum_Topics");
    if (($countTopics['cnt'] ?? 0) == 0) {
        // Topic 1 by Student Budi Santoso on Course 1, Session 1
        $topic1Id = dbExecute(
            "INSERT INTO DX_LMS_T_Forum_Topics 
            (course_id, session_id, category_type, author_npk, author_name, author_role, author_avatar, author_dept, title, content, status, moderation_status, likes_count, replies_count, is_pinned, is_hot, created_at, updated_at, created_by)
            VALUES 
            ('1', '1-S1', 'materi', '12345', 'Budi Santoso', 'student', '', 'Operator Produksi · Line A', 
             'Bagaimana penerapan 5S yang efektif di area perakitan akhir?', 
             'Saya sering menemukan beberapa alat kerja tidak dikembalikan ke tempat asalnya (Seiton) setelah pergantian shift. Apakah ada tips atau metode visual management yang lebih baik agar semua anggota tim lebih disiplin menerapkan standar 5S?', 
             'open', 'approved', 5, 2, 1, 1, DATEADD(hour, -5, CURRENT_TIMESTAMP), CURRENT_TIMESTAMP, '12345')"
        );

        // Fetch inserted ID
        $t1 = dbQueryOne("SELECT TOP 1 topic_id FROM DX_LMS_T_Forum_Topics ORDER BY topic_id DESC");
        $t1Id = $t1['topic_id'];

        // Reply 1 from Teacher Ahmad Rizal (Marked as Solution)
        dbExecute(
            "INSERT INTO DX_LMS_T_Forum_Replies
            (topic_id, author_npk, author_name, author_role, author_avatar, author_dept, content, is_solution, likes_count, status, created_at, updated_at, created_by)
            VALUES
            (?, '67890', 'Ahmad Rizal', 'teacher', '', 'Leader · Production Engineering',
             'Halo Budi, pertanyaan yang sangat bagus. Di Line A, kita bisa terapkan Shadow Board (papan bayangan perkakas) dengan kode warna per regu. Selain itu, pastikan 5 menit sebelum shift berakhir dialokasikan khusus untuk ritual 5S bersama.',
             1, 8, 'active', DATEADD(hour, -3, CURRENT_TIMESTAMP), CURRENT_TIMESTAMP, '67890')",
            [$t1Id]
        );

        // Reply 2 from Admin
        dbExecute(
            "INSERT INTO DX_LMS_T_Forum_Replies
            (topic_id, author_npk, author_name, author_role, author_avatar, author_dept, content, is_solution, likes_count, status, created_at, updated_at, created_by)
            VALUES
            (?, '99999', 'Admin System', 'admin', '', 'Super Admin · Plant 1 HRD',
             'Standar SOP visual 5S terbaru juga sudah diunggah di modul referensi dokumen perusahaan. Silakan cek bagian Dokumen K3 untuk panduan cetak label standar.',
             0, 3, 'active', DATEADD(hour, -1, CURRENT_TIMESTAMP), CURRENT_TIMESTAMP, '99999')",
            [$t1Id]
        );

        // Topic 2 by Student on Quiz
        dbExecute(
            "INSERT INTO DX_LMS_T_Forum_Topics 
            (course_id, session_id, category_type, author_npk, author_name, author_role, author_avatar, author_dept, title, content, status, moderation_status, likes_count, replies_count, is_pinned, is_hot, created_at, updated_at, created_by)
            VALUES 
            ('1', NULL, 'quiz', '12345', 'Budi Santoso', 'student', '', 'Operator Produksi · Line A', 
             'Klarifikasi Soal No. 3 pada Kuis Fundamental 5S', 
             'Pada pertanyaan tentang tahapan Seiso (Resik), apakah pembersihan mesin termasuk inspeksi awal potensi kebocoran oli? Mohon konfirmasi dari pengajar.', 
             'open', 'approved', 2, 1, 0, 0, DATEADD(hour, -2, CURRENT_TIMESTAMP), CURRENT_TIMESTAMP, '12345')"
        );

        $t2 = dbQueryOne("SELECT TOP 1 topic_id FROM DX_LMS_T_Forum_Topics ORDER BY topic_id DESC");
        $t2Id = $t2['topic_id'];

        // Reply from Teacher
        dbExecute(
            "INSERT INTO DX_LMS_T_Forum_Replies
            (topic_id, author_npk, author_name, author_role, author_avatar, author_dept, content, is_solution, likes_count, status, created_at, updated_at, created_by)
            VALUES
            (?, '67890', 'Ahmad Rizal', 'teacher', '', 'Leader · Production Engineering',
             'Tepat sekali Budi. Di Denso Way, konsep Seiso adalah Pembersihan adalah Pemeriksaan (Cleaning is Inspection). Jadi saat membersihkan, operator wajib sekalian menginspeksi adanya anomali seperti kebocoran atau baut kendor.',
             1, 4, 'active', DATEADD(minute, -30, CURRENT_TIMESTAMP), CURRENT_TIMESTAMP, '67890')",
            [$t2Id]
        );

        echo "Seed data for course 1 forum topics & replies inserted successfully.\n";
    }

    echo "Migration completed successfully!\n";

} catch (Throwable $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
