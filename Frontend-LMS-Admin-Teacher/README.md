# DLS Admin & Teacher Web

React single-page app with Vite and Tailwind CSS. It communicates only with the Node API; it does not connect to SQL Server directly.

## Local runtime

- Requires Node.js 20+
- Vite listens at `http://127.0.0.1:5173` (strict port)
- API defaults to `http://127.0.0.1:3001/api/v1`

On Windows, run `install.bat` from this folder to install Node.js dependencies and create `.env` from `.env.example` if needed. Confirm `VITE_API_BASE_URL` points to the backend before starting the frontend.

```powershell
Copy-Item .env.example .env
npm install
npm run dev
```

Sign in using a teacher/admin account provisioned by the backend. Use `npm run build` to create the static production bundle in `dist/`.

The UI currently contains login, dashboard, course/session/material management, activity, synchronized learner progress, forum moderation, and admin account management. User management is limited to the admin role. Grade data is a learner-progress snapshot, not quiz scoring. Forum moderation displays records in the SQL Server forum table; student forum records are not synchronized into that table yet.

The access JWT is kept in `sessionStorage`, so it survives a page reload in the same tab and is cleared on logout or when the tab session ends. On startup, the frontend validates it with `GET /api/v1/auth/me`; an invalid or expired token returns the user to login. Do not store the token in `localStorage` or expose API secrets through `VITE_*` variables.

The layout references `design-reference/` Stitch exports; a copy of the teacher dashboard screenshot is in `public/design-reference/` for the login split panel. Local Inter and Material Symbols font files keep the design independent of font CDNs.
