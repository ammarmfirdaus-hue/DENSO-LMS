# Database migrations

The applications use two independent databases and SQL dialects:

| Target | Engine | Connection settings |
| --- | --- | --- |
| Admin/Teacher API | Microsoft SQL Server | `Backend-LMS-Admin-Teacher/.env`: `DB_SERVER`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` |
| Native Student | MySQL 8+ | `Backend-LMS-Admin-Teacher/.env`: `STUDENT_DB_HOST`, `STUDENT_DB_PORT`, `STUDENT_DB_NAME`, `STUDENT_DB_USER`, `STUDENT_DB_PASSWORD` |

Set both databases in the backend `.env` before running the CLI. Keep the runtime accounts (`DB_USER` and `STUDENT_DB_USER`) restricted to application access. The migrator accepts optional, separate DDL credentials: `MIGRATION_DB_USER` / `MIGRATION_DB_PASSWORD` for SQL Server and `MIGRATION_STUDENT_DB_USER` / `MIGRATION_STUDENT_DB_PASSWORD` for MySQL. If unset, it falls back to the matching app credentials, which must then have DDL rights. Databases must already exist; the CLI deliberately does not create databases or change credentials. The Student app `.env` must point to the same MySQL database.

For SQL Server, create a dedicated login/user using an administrative SSMS session, then grant database-scoped migration rights. Replace the password before executing:

```sql
USE master;
GO
IF SUSER_ID(N'dls_lms_migrator') IS NULL
  CREATE LOGIN dls_lms_migrator WITH PASSWORD = N'<STRONG_PASSWORD>', CHECK_POLICY = ON;
GO
USE DX_LMS_AdminTeacher;
GO
IF DATABASE_PRINCIPAL_ID(N'dls_lms_migrator') IS NULL
  CREATE USER dls_lms_migrator FOR LOGIN dls_lms_migrator;
GO
GRANT CREATE TABLE TO dls_lms_migrator;
GRANT ALTER ON SCHEMA::dbo TO dls_lms_migrator;
GRANT REFERENCES ON SCHEMA::dbo TO dls_lms_migrator;
GRANT SELECT ON SCHEMA::dbo TO dls_lms_migrator;
IF OBJECT_ID(N'dbo.schema_migrations', N'U') IS NOT NULL
  GRANT SELECT, INSERT ON dbo.schema_migrations TO dls_lms_migrator;
GO
```

For MySQL, grant migration privileges to a dedicated account on only the Student database (replace host/password as appropriate):

```sql
CREATE USER IF NOT EXISTS 'dls_student_migrator'@'localhost' IDENTIFIED BY '<STRONG_PASSWORD>';
GRANT CREATE, ALTER, INDEX, REFERENCES, SELECT, INSERT, UPDATE ON dls_student.* TO 'dls_student_migrator'@'localhost';
```

Put the migrator credentials in the backend `.env` under the optional names above, then retry `node database/migrate.js up admin` (or `student`). The existing API user can remain restricted to its application permissions. SQL Server migrations are transactional, so a failed SQL Server migration rolls back; MySQL DDL may be partially applied and should be inspected before retrying.

If `schema_migrations` already exists and the CLI reports `SELECT permission was denied` for a table during constraint validation, grant read access to the migration account in SSMS as a database administrator. Keep the tracking table insert grant as well:

```sql
USE DX_LMS_AdminTeacher;
GO
GRANT SELECT ON SCHEMA::dbo TO dls_lms_migrator;
GRANT INSERT ON dbo.schema_migrations TO dls_lms_migrator;
GO
```

Run from the repository root:

```powershell
node database/migrate.js status all
node database/migrate.js up all
```

Run only one database with `admin` or `student` as the final argument. The backend also exposes `npm run db:migrate` and `npm run db:migrate:status` for the Admin/Teacher SQL Server database. Applied filenames are stored separately in each database's `schema_migrations` table. `up` skips recorded migrations; review SQL changes before deploying, and back up production databases first. SQL Server migrations run in a transaction. MySQL commits DDL as required by MySQL and records a migration after successful execution; failed partially applied MySQL DDL must be inspected and repaired before retrying.

## Current application schema inventory

The migration set is based on the tables actually queried by the two current apps and the existing feature migration scripts, rather than the separate legacy `DX_LMS_W_DDL.sql` schema.

### Admin/Teacher SQL Server

- `employee_accounts`: staff identity, credentials, admin/teacher/reviewer role, reviewer profile, active status, and timestamps.
- `courses`: catalog, category/status, creator/updater, and assigned trainer.
- `course_sessions`, `course_materials`: course structure and published learning content.
- `activity_logs`, `student_progress_snapshots`: staff activity and synchronized student progress/grades.
- `material_submissions`, `material_review_links`: material submission, moderation state, reviewer assignment, hashed one-use review links, comments, and publication reference.
- `forum_threads`, `forum_replies`, `forum_likes`, `forum_session_settings`: real-time discussion, attachments, visibility, likes, and per-session write state.
- `lms_notifications`, `lms_notification_reads`, `lms_notification_email_outbox`: role-targeted notifications, per-recipient read state, and retryable email delivery.
- `schema_migrations`: applied migration versions for this database.

### Student MySQL

- `student_accounts`: Student identity, credentials, profile fields, status, and timestamps.
- `courses`, `course_enrollments`: synchronized catalog and per-student enrollment/progress/completion.
- `course_sessions`, `course_materials`: synchronized course structure/content, including unique source IDs for safe upserts.
- `material_progress`: per-student material completion state.
- `activity_logs`: Student activity history.
- `login_rate_limits`: hashed-IP login throttling window and lock state.
- `schema_migrations`: applied migration versions for this database.

The Student feature code currently reads quiz/assessment and notification data from the Admin/Teacher API or renders them from existing session/course data; this repo's Student MySQL queries do not reference separate quiz, forum, or notification tables. Add a new versioned migration when a feature starts persisting those entities locally.

## Migration order

SQL Server runs `001_initial_schema`, `002_material_workflow`, `003_forum_realtime`, and `004_notifications` in filename order. MySQL currently runs `001_initial_schema`. Keep each new migration additive and uniquely numbered; don't edit a migration that has already been applied in a shared environment.
