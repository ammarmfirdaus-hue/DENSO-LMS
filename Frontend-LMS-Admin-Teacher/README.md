# DLS Admin & Teacher Web

React single-page app with Vite and Tailwind CSS. It communicates only with the Node API; it does not connect to SQL Server directly.

## Local runtime

- Requires Node.js 20+
- Vite listens at `http://127.0.0.1:5173` (strict port)
- In development, API requests use Vite's same-origin `/api/v1` proxy to `http://127.0.0.1:3001`.

On Windows, run `install.bat` from this folder to install Node.js dependencies and create `.env` from `.env.example` if needed. If the backend uses a different host or port, set `VITE_API_PROXY_TARGET` to its origin. `VITE_API_BASE_URL` remains available for production or a separately hosted API; local loopback values are routed through the Vite proxy automatically.

```powershell
Copy-Item .env.example .env
npm install
npm run dev
```

Sign in using a teacher/admin account provisioned by the backend. Use `npm run build` to create the static production bundle in `dist/`.

### Install as an app (PWA)

The portal is installable from supported browsers. Use HTTPS in deployments; `127.0.0.1` is allowed for local development. Chrome/Edge display the install action from the portal; iPhone/iPad users can use **Bagikan → Tambahkan ke Layar Utama**. Production hosting must serve `/sw.js` and `/manifest.webmanifest` from the site root and rewrite application routes such as `/grades` to `index.html`. The service worker caches public assets and an offline page only; API calls, uploads, and authenticated page responses remain online-only.

The portal includes login, dashboard, course/session/material management, activity, learner grades and progress, forum, notifications, reviewer management, and user management. Admin can manage all account roles; Teacher is limited to Student accounts and courses they own or are assigned. Both roles can edit grades; only Admin can clear a grade. Grade export creates `.xlsx`. Account email is a password reset: Admin enters a new password, the backend stores its bcrypt hash, and the queue emails that password with the NPK. Bulk operations persist progress and support stop/resume. Grade records are separate from Student quiz screens, which do not persist assessment scores. Student and staff forum clients use the backend forum API and its SQL Server tables.

Course/session creation and reading are available; the API currently has no general course/session edit or delete endpoints. See [`docs/IMPLEMENTATION-AUDIT.md`](../docs/IMPLEMENTATION-AUDIT.md) for the full capability and gap matrix.

The access JWT is kept in `sessionStorage`, so it survives a page reload in the same tab and is cleared on logout or when the tab session ends. On startup, the frontend validates it with `GET /api/v1/auth/me`; an invalid or expired token returns the user to login. Do not store the token in `localStorage` or expose API secrets through `VITE_*` variables.

The layout references `design-reference/` Stitch exports; a copy of the teacher dashboard screenshot is in `public/design-reference/` for the login split panel. Local Inter and Material Symbols font files keep the design independent of font CDNs.
