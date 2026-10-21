# DLS Admin & Teacher API

Node.js + Express REST API backed by Microsoft SQL Server. Local API port: **3001**. The React client runs at port **5173**. The API binds to `127.0.0.1` in this development setup.

## Setup

1. Install Node.js 20+ and SQL Server 2019+ with the SQL Server driver enabled.
2. Create a database login with access only to `DX_LMS_AdminTeacher`; avoid using `sa`.
3. Run `../database/admin&teacher.sql` in SQL Server Management Studio. This is the shared workspace schema file.
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

Protected requests use `Authorization: Bearer <token>`. JWT verification pins HS256 and checks issuer, audience, expiry, and role. The React app holds the token in memory instead of localStorage; a page reload requires signing in again. CORS allows only `FRONTEND_ORIGIN`; JSON request bodies have a 32 KB limit; SQL values are parameterized; secrets are excluded from API error responses.

## Student database synchronization

The optional sync process copies course metadata from SQL Server into the PHP student's MySQL database, then copies learner enrollment/progress summaries back into `student_progress_snapshots` for teacher/admin review. It is disabled by default. To enable it, create a dedicated, least-privilege MySQL account, apply `../database/student.sql`, set `STUDENT_SYNC_ENABLED=true` and `STUDENT_DB_*` in this project's private `.env`, and restart the API. The default interval is one minute. It is a periodic snapshot sync, not a real-time transaction, forum, or quiz-grade feed; monitor the server log for sync errors.

## Scope

The migrated API supports login, dashboard counts, course/session/material authoring, activity listing, student progress snapshots, forum moderation, and admin staff accounts. Quiz scoring, binary material uploads, token refresh/revocation, and live grade sync are not implemented. Course material file paths must point to a separately secured storage service; never serve arbitrary filesystem paths from this API.
