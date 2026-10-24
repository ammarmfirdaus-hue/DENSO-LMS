# DLS Admin & Teacher API

Node.js + Express REST API backed by Microsoft SQL Server. Local API port: **3001**. The React client runs at port **5173**. The API binds to `127.0.0.1` in this development setup.

## Setup

On Windows, run `install.bat` from this folder to install Node.js dependencies and create `.env` with a fresh random JWT secret if it does not exist. Configure SQL Server credentials in `.env` before starting the API.

1. Install Node.js 20+ and SQL Server 2019+ with the SQL Server driver enabled.
2. Create a database login with access only to `DX_LMS_AdminTeacher`; avoid using `sa`.
3. Run `../database/admin&teacher.sql` in SQL Server Management Studio. This is the shared workspace schema file.
   Then run, in order, `../database/reviewer-profile-migration.sql`, `../database/material-workflow.sql`, `../database/Forum_Realtime_Migration.sql`, and `../database/Notifications_Migration.sql` against the same database.
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

- `GET /health` — process health only.
- `POST /api/v1/auth/login` — `{ "npk": "...", "password": "..." }`; rate-limited, returns a short-lived token.
- `GET /api/v1/auth/me` — authenticated account profile.
- `GET /api/v1/dashboard/summary` and `/activity` — teacher/admin summary and activity.
- `GET /api/v1/dashboard/courses`, `GET /api/v1/courses/:id/sessions` — course data.
- `POST /api/v1/courses` — create a course.
- `/api/v1/notifications` — role-scoped inbox, unread count, read actions, and Admin/Teacher notification composer. Notifications update over SSE; optional email is queued and retried by the backend worker.

Protected requests use the HttpOnly SSO cookie or `Authorization: Bearer <token>`. JWT verification pins HS256 and checks issuer, audience, and expiry. Forum access is checked against course assignments or student enrollment. CORS allows the Admin/Teacher and Student origins; forum writes require a trusted browser origin. Forum image uploads are limited to 5 MB and validated by file signature. Live forum updates use server-sent events; clients refresh the current session feed only when a forum event arrives.

Run `npm test` for the isolated security suites in `tests/`. They cover SSO/JWT validation, role authorization, hostile pagination parameters, spoofed uploads, and error-response redaction. The tests use test-only credentials and temporary files; they do not connect to or mutate the application database.

## Student database synchronization

The optional sync process copies course, session, and published material data from SQL Server into the PHP student's MySQL database, then copies learner enrollment/progress summaries back into `student_progress_snapshots` for teacher/admin review. It is disabled by default. To enable it, create a dedicated, least-privilege MySQL account, apply `../database/student.sql` (or run `../database/student-material-sync-migration.sql` against an existing database), set `STUDENT_SYNC_ENABLED=true` and `STUDENT_DB_*` in this project's private `.env`, and restart the API. Set `STUDENT_PUBLIC_API_URL` to the API URL the student browser can reach; published files are served from that API. Sync runs once a minute by default, so a newly published item can take up to one interval to appear.

For optional notification email to Student accounts, enable `STUDENT_AUTH_ENABLED=true` and configure `STUDENT_DB_*` for the active Student account database. Configure the SMTP settings described below. Email recipients are queued in SQL Server and delivered in small batches with bounded retries; web notifications remain available if email is not selected.

## Material approval and reviewer portal

1. Create reviewer and trainer accounts from **Pengguna** as an Admin. Give each account a unique NPK, work email, and password. Assign each course to its trainer in **Course & Materi**; trainers then see and upload only courses they own or have been assigned. Reviewer decisions are only available through the secure email link.
2. Set `MATERIAL_UPLOAD_DIR` (defaults to `uploads/materials` under this backend), `MATERIAL_MAX_UPLOAD_BYTES`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASSWORD`, and `SMTP_FROM` in the backend `.env`. Use a real reachable URL for `REVIEW_PORTAL_URL` (for local-only use it can be `http://127.0.0.1:5173`). Do not commit SMTP credentials.
3. Trainer adds text, a YouTube URL, HTTPS link, or a local PDF/image/Office/audio/video file under a course session. The upload stays in the backend's local `uploads/materials` directory and enters **Menunggu admin**.
4. Admin chooses an active reviewer and sends the invitation. The email contains a random, one-use link that expires after 48 hours. Opening it creates a two-hour reviewer session and displays that reviewer's assigned material list. The reviewer can preview supported material and approve or reject with a comment.
5. Admin can publish only approved materials. The next student sync copies the published course session and item into MySQL; the student course page displays its text, YouTube video, PDF, image, office document, audio, or link.

Allowed local files are PDF, common Office/ODF documents, MP4/WebM/MOV/OGV, MP3/WAV/OGG/M4A, and PNG/JPG/GIF/WebP. The default upload limit is 250 MB; tune it to available disk and PHP/browser constraints. Back up `uploads/materials` along with SQL Server. Rejected or unpublished material is never returned by the student file endpoint.

## Scope

The migrated API supports login, dashboard counts, course/session authoring, material submission/review/publication, activity listing, student progress snapshots, forum moderation, and admin staff/reviewer accounts. Quiz scoring, token refresh/revocation, and live grade sync are not implemented.
