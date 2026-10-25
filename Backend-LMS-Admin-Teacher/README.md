# DLS Admin & Teacher API

Node.js + Express REST API backed by Microsoft SQL Server. Local API port: **3001**. The React client runs at port **5173**. The API binds to `127.0.0.1` in this development setup.

## Setup

On Windows, run `install.bat` from this folder to install Node.js dependencies and create `.env` with a fresh random JWT secret if it does not exist. Configure SQL Server credentials in `.env` before starting the API.

1. Install Node.js 20+ and SQL Server 2019+ with the SQL Server driver enabled.
2. Create a database login with access only to `DX_LMS_AdminTeacher`; avoid using `sa`.
3. Create the empty databases, configure app and optional migrator credentials, then run `node database/migrate.js up all` from the repository root. Follow [`../database/MIGRATIONS.md`](../database/MIGRATIONS.md); do not apply the legacy combined DDL and managed migrations twice.
4. Copy `.env.example` to `.env`, configure the connection, and set a random JWT secret of at least 32 bytes. Generate one with `node -e "console.log(require('crypto').randomBytes(48).toString('base64url'))"`.
5. Run `npm install`, then provision the initial staff account through environment variables `SEED_NPK`, `SEED_NAME`, `SEED_EMAIL`, `SEED_PASSWORD`, `SEED_ROLE` and `npm run db:seed`. The seeder hashes passwords with bcrypt cost 12; it does not include default credentials.
6. Run `npm run dev` for local development or `npm start` for the API process.

### Demo data

For a local demo database, set `SEED_DEMO=true` and `SEED_DEMO_PASSWORD` (12-72 UTF-8 bytes), then run `npm run db:seed:demo`. In PowerShell:

```powershell
$env:SEED_DEMO = 'true'
$env:SEED_DEMO_PASSWORD = Read-Host 'Demo-only password (12-72 UTF-8 bytes)'
npm run db:seed:demo
Remove-Item Env:SEED_DEMO, Env:SEED_DEMO_PASSWORD
```

The idempotent seeder adds two staff accounts, three courses with sessions/materials, activity records, student progress snapshots, and forum threads. Staff NPKs are `DLS-DEMO-ADM-001` and `DLS-DEMO-TCH-001`; newly created accounts use the password supplied in `SEED_DEMO_PASSWORD`. Existing account passwords are not reset. Demo course codes start with `DLS-DEMO-`. Run only against a development database; the seeder does not delete existing records.

## API

- `/api/v1/quizzes` - Admin/Teacher quiz authoring scoped to courses they manage; quiz definitions and single Student attempts use the configured Student MySQL database.

- `GET /health` — process health only.
- `POST /api/v1/auth/login` — `{ "npk": "...", "password": "..." }`; rate-limited, returns a short-lived token.
- `GET /api/v1/auth/me` — authenticated account profile.
- `GET /api/v1/dashboard/summary` and `/activity` — teacher/admin summary and activity.
- `GET /api/v1/dashboard/courses`, `GET /api/v1/courses/:id/sessions` — course data.
- `/api/v1/courses` — read/create courses and sessions; Admin-only trainer assignment. General course/session update/delete endpoints are not implemented.
- `/api/v1/users`, `/api/v1/reviewers`, `/api/v1/jobs` — role-scoped account/reviewer management and persistent bulk jobs with SSE progress/stop/resume.
- `/api/v1/grades` — staff grade/progress view, edit, Admin-only clear, Student notification, and optional email.
- `/api/v1/forum` — Student/staff forum and session moderation; `/api/v1/notifications` — inbox, SSE, read state, and optional email outbox.

Protected requests use the HttpOnly SSO cookie or `Authorization: Bearer <token>`. JWT verification pins HS256 and checks issuer, audience, and expiry. Forum access is checked against course assignments or student enrollment. CORS allows the Admin/Teacher and Student origins; forum writes require a trusted browser origin. Forum image uploads are limited to 5 MB and validated by file signature. Live forum updates use server-sent events; clients refresh the current session feed only when a forum event arrives.

Run `npm test` for the isolated security suites in `tests/`. They cover SSO/JWT validation, role authorization, hostile pagination parameters, spoofed uploads, and error-response redaction. The tests use test-only credentials and temporary files; they do not connect to or mutate the application database.

## Student database synchronization

The optional sync process copies course, session, and published material data from SQL Server into the PHP Student MySQL database, then copies enrollment/progress and quiz-score snapshots back into `student_progress_snapshots`. It is disabled by default. Apply the Student migrations with `node database/migrate.js up student` from repository root, then set `STUDENT_SYNC_ENABLED=true` and `STUDENT_DB_*` in this project's private `.env` and restart the API. Published material file links use the Admin-managed API URL, falling back to `STUDENT_PUBLIC_API_URL` when no URL is saved. Periodic sync runs once a minute by default. Staff grade updates also schedule a targeted MySQL update.

Admins can open **Manajemen → Atur API** in the Admin/Teacher portal to set the shared API URL for the Admin/Teacher portal and Native PHP Student. The value is stored in `dbo.lms_system_settings`, discovered by both apps through the public `/api/v1/settings/runtime-config` endpoint, and can be checked from the browser before saving. Native Student's `SSO_API_URL` remains only the bootstrap/fallback address; Student MySQL credentials remain environment-based and are never exposed in the settings response. The page also shows Student sync health and can request an immediate sync. Run `node database/migrate.js up admin` after deploying the API settings migration.

For optional notification email to Student accounts, enable `STUDENT_AUTH_ENABLED=true` and configure `STUDENT_DB_*` for the active Student account database. Configure the SMTP settings described below. Email recipients are queued in SQL Server and delivered in small batches with bounded retries; web notifications remain available if email is not selected.

## Material approval and reviewer portal

1. Create reviewer and trainer accounts from **Pengguna** as an Admin. Give each account a unique NPK, work email, and password. Assign each course to its trainer in **Course & Materi**; trainers then see and upload only courses they own or have been assigned. Reviewer decisions are only available through the secure email link.
2. Set `MATERIAL_UPLOAD_DIR` (defaults to `uploads/materials` under this backend), `MATERIAL_MAX_UPLOAD_BYTES`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASSWORD`, and `SMTP_FROM` in the backend `.env`. Use a real reachable URL for `REVIEW_PORTAL_URL` (for local-only use it can be `http://127.0.0.1:5173`). Do not commit SMTP credentials.
3. Trainer adds text, a YouTube URL, HTTPS link, or a local PDF/image/Office/audio/video file under a course session. The upload stays in the backend's local `uploads/materials` directory and enters **Menunggu admin**.
4. Admin chooses an active reviewer and sends the invitation. The email contains a random, one-use link that expires after 48 hours. Opening it creates a two-hour reviewer session and displays that reviewer's assigned material list. The reviewer can preview supported material and approve or reject with a comment.
5. Admin can publish only approved materials. The next student sync copies the published course session and item into MySQL; the student course page displays its text, YouTube video, PDF, image, office document, audio, or link.

Allowed local files are PDF, common Office/ODF documents, MP4/WebM/MOV/OGV, MP3/WAV/OGG/M4A, and PNG/JPG/GIF/WebP. The default upload limit is 250 MB; tune it to available disk and PHP/browser constraints. Back up `uploads/materials` along with SQL Server. Rejected or unpublished material is never returned by the student file endpoint.

## Scope

The API supports SSO, course/material workflow, account/reviewer administration, persistent user jobs, grade editing/export support, student notifications/email outbox, forum, quiz authoring, and optional Student sync. Published course quiz attempts and automatic quiz scores are persisted by Student MySQL. General course/session edit/delete and global token revocation are not implemented. Forum/notification SSE events use an in-process EventEmitter, so multi-instance deployments need shared pub/sub for cross-instance delivery. See [`../docs/IMPLEMENTATION-AUDIT.md`](../docs/IMPLEMENTATION-AUDIT.md).
