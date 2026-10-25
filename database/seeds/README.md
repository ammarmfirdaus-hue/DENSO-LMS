# Demo data seeding

Run `Start-All-DataDummy.ps1` from the repository root in a local or staging environment. It seeds both databases used by the current applications:

| Database | Seeder | Example data |
| --- | --- | --- |
| SQL Server Admin/Teacher | `Backend-LMS-Admin-Teacher/scripts/seed-demo.js` | Admin, teachers, reviewers, assigned courses, learning progress and grades, forum activity, material review queues, notifications, simulated email delivery state, and completed bulk-job history |
| MySQL Student | `NATIVE-LMS-Student/scripts/seed-demo.php` | 100 Student accounts, 100 linked Teacher study-mode identities, 100 courses, enrollments, material completion, published/draft/inactive quizzes, single quiz attempts, access requests, and learning activity history |

The fixture creates 201 Admin/Teacher/Reviewer accounts (1 Admin, 100 Teachers, and 100 Reviewers), 100 Student accounts plus 100 linked Teacher study-mode identities, 100 courses, 200 sessions, 400 materials, about 600 enrollments and grade/progress snapshots, 2,400 material-progress rows, 100 quizzes with 500 questions and more than 100 eligible attempts, 100 course-access requests, 200 material submissions with 120 reviewer links, 100 forum discussions with 400 Student/Teacher replies and 100 likes, 100 in-app notifications with 100 read states, 100 simulated email notifications/outbox states, 100 completed queue-history jobs with 100 events, and more than 100 activity-log rows in each application database. Statuses cover active/inactive users, enrollment progress, quiz availability, request decisions, moderation stages, read/unread notifications, simulated sent/failed/deferred email states, and mixed queue outcomes. Re-running the command reuses deterministic demo keys and does not replace existing passwords.

## Requirements

1. Apply the latest schema to both databases first: `node database/migrate.js up all`.
2. Configure `Backend-LMS-Admin-Teacher/.env` and `NATIVE-LMS-Student/.env`. The backend `STUDENT_DB_NAME` and Student `DB_NAME` must match.
3. Ensure backend dependencies are installed and Node.js, npm, and PHP with PDO MySQL are available in `PATH`.
4. Run `./Start-All-DataDummy.ps1`. Confirm the displayed database names by typing `SEED DEMO`, then provide a demo-only password.

The seeders use bcrypt/PHP password hashing. New demo accounts share the password entered at the prompt; existing account passwords are preserved. Fixture email addresses use the reserved `.test` domain. Outbox sent/failed/retry states are simulated only; no message is delivered, and retry examples are deferred for 30 days. Review links store only a SHA-256 token hash and no usable token.

The PowerShell runner refuses database names containing a production/live marker, checks that both Student connections point at the same database host, port, and name, and asks for an explicit confirmation. Keep production databases separate and never seed dummy users into live customer data. `login_rate_limits` is intentionally not seeded because it represents active security throttling state. SQL inserts are kept in the application seeders instead of raw SQL dumps so credentials are hashed and each insert follows the application schema and foreign-key relationships.
