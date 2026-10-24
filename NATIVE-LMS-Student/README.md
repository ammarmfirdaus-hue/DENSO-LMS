# DLS Native Student

PHP Native student portal with a same-origin, read-only GET JSON API. This project owns the student-facing experience and its MySQL database; it does not contain teacher or administrator controllers. Tailwind CSS 4 is compiled locally for both the PHP views and the small API-driven course/dashboard rendering script.

## Runtime

- PHP 8.1+ with `pdo_mysql`
- MySQL 8.0+
- Local port: `8082`

On Windows, run `install.bat` from this folder to check PHP 8.1+/`pdo_mysql` and Node.js 20+, install the Tailwind dependencies, and build the CSS. It creates `.env` from `.env.example` only when `.env` does not exist. This project currently has no `composer.json`, so Composer is not required.

From this directory, configure the environment values documented in `.env.example`, create the database, then run:

```powershell
Get-Content ..\database\student.sql | mysql -u root -p
php -S 127.0.0.1:8082 -t public public/router.php
```

Open `http://127.0.0.1:8082`. The student API endpoints are `GET /api/index.php?resource=me`, `GET /api/index.php?resource=dashboard`, and `GET /api/index.php?resource=courses`. All require the authenticated student session cookie. Read endpoints do not mutate data. The PHP portal serves UI and API on the same origin to avoid broad CORS exposure.

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

### Security tests

Run `php tests/run.php` from this project directory. The split suites in `tests/test.secure.*.php` exercise SSO token validation, CSRF and session settings, controller path traversal, HTML output escaping, and hostile pagination input. They run without a database connection and use an isolated test-only SSO secret.
