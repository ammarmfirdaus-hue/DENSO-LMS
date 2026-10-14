-- =========================================================================================
-- DENSO LEARNING SYSTEM (DLS) — REFERENCE SCHEMA FOR SSMS
-- Ref: database/DX_LMS_W_DDL.sql
-- =========================================================================================

-- Merujuk ke skrip implementasi lengkap di DX_LMS_W_DDL.sql
-- Seluruh tabel telah dinormalisasi 3NF dengan pertahanan Cybersecurity OWASP Top 10.
-- Buka dan eksekusi file database/DX_LMS_W_DDL.sql di SSMS untuk membangun seluruh 28 tabel,
-- relasi Foreign Key, indeks performa, dan seed data dummy.

USE [DX_LMS];
GO

SELECT 
    t.name AS [Table_Name],
    c.name AS [Primary_Key_Column],
    i.name AS [Index_Name]
FROM sys.tables t
LEFT JOIN sys.indexes i ON t.object_id = i.object_id AND i.is_primary_key = 1
LEFT JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
LEFT JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
ORDER BY t.name;
GO
