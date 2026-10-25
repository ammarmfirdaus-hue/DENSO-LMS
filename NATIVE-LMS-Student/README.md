# DLS Native Student

PHP Native student portal with a same-origin, read-only GET JSON API. This project owns the student-facing experience and its MySQL database; it does not contain teacher or administrator controllers. Tailwind CSS 4 is compiled locally for both the PHP views and the small API-driven course/dashboard rendering script.

## Runtime

- PHP 8.1+ with `pdo_mysql`
- MySQL 8.0+
- Local port: `8082`

On Windows, run `install.bat` from this folder to check PHP 8.1+/`pdo_mysql` and Node.js 20+, install the Tailwind dependencies, and build the CSS. It creates `.env` from `.env.example` only when `.env` does not exist. This project currently has no `composer.json`, so Composer is not required.

From this directory, configure the environment values documented in `.env.example`, create the database, then run:

```powershell
node ..\database\migrate.js up student
php -S 127.0.0.1:8082 -t public public/router.php
```

Run the migration command from the repository root if the relative path does not resolve from your shell. Configure the MySQL migration credentials as described in [`../database/MIGRATIONS.md`](../database/MIGRATIONS.md).

Open `http://127.0.0.1:8082`. The same-origin, GET-only JSON endpoints are `GET /api/index.php?resource=me`, `GET /api/index.php?resource=dashboard`, and `GET /api/index.php?resource=courses`. They require the authenticated Student session. The nilai page reads the authenticated Student's MySQL enrollment directly through PHP/PDO; forum and notification clients call the backend API separately. The PHP portal keeps its page and JSON API same-origin.

### Backend API address

The Native Student app reads its active Backend API URL from the Admin-managed `dbo.lms_system_settings` value exposed by `GET /api/v1/settings/runtime-config`. Configure that URL from **Manajemen â†’ Atur API** in the Admin/Teacher portal. `SSO_API_URL` in this project's `.env` is only the bootstrap address used to retrieve that setting and the fallback if the backend cannot be reached; it is not the preferred active URL. Student database credentials remain in `.env`. The resolved non-secret URL is cached for up to 60 seconds in the system temporary directory.

### Install as an app (PWA)

The Student portal can be installed from a supported browser. Use HTTPS in deployments; localhost is allowed for development. Chrome/Edge show the install prompt from the portal, while iPhone/iPad users can use **Bagikan → Tambahkan ke Layar Utama**. The service worker caches static assets and a small offline page; private PHP pages and API responses are not cached.

Create student accounts by setting `STUDENT_PASSWORD` in the process environment (12+ characters), then run `php scripts/provision-student.php NPK "Full Name" employee@example.com`. The account password is stored with PHP `password_hash()`; do not store plaintext passwords or commit database credentials. Run `npm install` and `npm run build:css` when changing view utility classes. `APP_ENV=production` disables display of runtime errors.

### Demo data

Set `STUDENT_PASSWORD` to a demo-only password of at least 12 characters and run `php scripts/seed-demo.php` from this directory. In PowerShell:

```powershell
$env:STUDENT_PASSWORD = Read-Host 'Demo-only student password (12+ characters)'
php scripts/seed-demo.php
Remove-Item Env:STUDENT_PASSWORD
```

The idempotent seeder adds three student accounts, four courses, sessions/materials, enrollments, material progress, and activity history. Demo NPKs are `DLS-DEMO-STU-001`, `DLS-DEMO-STU-002`, and `DLS-DEMO-STU-003`; newly created accounts use the password supplied in `STUDENT_PASSWORD`. Existing account passwords are not reset. It leaves `login_rate_limits` empty because that table stores live security throttling state. Course codes and accounts use the `DLS-DEMO-` prefix; run this only against a development database. The seeder does not delete records.

## Security notes

- Database access uses PDO prepared statements and native prepares.
- Login failures are throttled per source address using a hashed address key and a database-backed 15-minute lock window.
- Session cookies are HttpOnly and SameSite=Lax; secure cookies are enabled automatically under HTTPS.
- All private student pages and API resources require a student session.
- No teacher/admin access or role switching is implemented here.
- Some deep learning screens still contain migrated sample content; the dashboard/course GET endpoints read the new MySQL schema. Populate the database before relying on these views for real learning records.
- `/student/nilai` displays quiz score and course score separately from MySQL `course_enrollments`.
- Published course quizzes are stored in `course_quizzes`, `course_quiz_questions`, and `course_quiz_attempts`. Students must mark every active course material complete before the quiz unlocks; a unique database constraint limits each student to one attempt. Quiz scores are copied to the Admin/Teacher SQL Server progress snapshot by the configured student sync.
- Students use the published course quiz after completing all active course materials; each student receives one recorded attempt.
- `/student/forum/*` and `/student/notifikasi` use the authenticated backend API and require an online connection. Forum and notification records live in SQL Server, not in Student MySQL tables.
- Student profile/settings screens do not yet provide production profile CRUD. See [`../docs/IMPLEMENTATION-AUDIT.md`](../docs/IMPLEMENTATION-AUDIT.md) for the complete feature matrix and known gaps.

### Security tests

Run `php tests/run.php` from this project directory. The split suites in `tests/test.secure.*.php` exercise SSO token validation, CSRF and session settings, controller path traversal, HTML output escaping, and hostile pagination input. They run without a database connection and use an isolated test-only SSO secret.
