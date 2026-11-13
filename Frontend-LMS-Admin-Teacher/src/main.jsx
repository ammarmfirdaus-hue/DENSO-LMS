import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import { api, authToken, initializeApiBase } from './api/client';
import AppLayout from './layouts/AppLayout';
import LoginPage from './features/auth/DX_LMS_M_Auth_Login';
const DashboardPage = React.lazy(() => import('./features/dashboard/DX_LMS_T_Dashboard'));
const CoursesPage = React.lazy(() => import('./features/courses/DX_LMS_T_Courses'));
const MaterialsPage = React.lazy(() => import('./features/materials/DX_LMS_T_Materials'));
const CourseDetailPage = React.lazy(() => import('./features/courses/DX_LMS_T_Course_Detail'));
const ActivityPage = React.lazy(() => import('./features/activity/DX_LMS_T_Activity'));
const GradesPage = React.lazy(() => import('./features/grades/DX_LMS_T_Grades'));
const QuizzesPage = React.lazy(() => import('./features/quizzes/DX_LMS_T_Quizzes'));
const ForumPage = React.lazy(() => import('./features/forum/DX_LMS_T_Forum'));
const NotificationsPage = React.lazy(() => import('./features/notifications/DX_LMS_T_Notifications'));
const UsersPage = React.lazy(() => import('./features/users/DX_LMS_M_Users'));
const ReviewersPage = React.lazy(() => import('./features/reviewers/DX_LMS_M_Reviewers'));
const EnrollmentsPage = React.lazy(() => import('./features/enrollments/DX_LMS_T_Enrollments'));
const RolesPage = React.lazy(() => import('./features/roles/DX_LMS_M_Role_Access'));
const ApiSettingsPage = React.lazy(() => import('./features/settings/DX_LMS_M_Settings_Api'));
const ReviewerPortalPage = React.lazy(() => import('./features/reviewer/DX_LMS_M_Reviewer_Portal'));
import { canAccessStaffPortal, hasPermission } from './utils/access-control';
import PwaInstallPrompt from './components/PwaInstallPrompt';
import { registerPwaServiceWorker } from './pwa/registerServiceWorker';
import './styles/index.css';
import './utils/action-controls.js';

registerPwaServiceWorker();

function Application() {
  const [session, setSession] = useState(null);
  const [authReady, setAuthReady] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const isReviewerRoute = location.pathname === '/review' || location.pathname.startsWith('/review/');

  useEffect(() => {
    if (isReviewerRoute) {
      setAuthReady(true);
      return undefined;
    }
    let mounted = true;
    setAuthReady(false);

    async function restoreSession() {
      try {
        await initializeApiBase();
        const result = await api('/auth/me');
        if (result.user.role === 'student') {
          authToken.clear();
          window.location.replace(import.meta.env.VITE_STUDENT_APP_URL || 'http://127.0.0.1:8082');
          return;
        }
        if (!canAccessStaffPortal(result.user)) {
          authToken.clear();
          return;
        }
        if (mounted) setSession(result.user);
      } catch {
        authToken.clear();
      } finally {
        if (mounted) setAuthReady(true);
      }
    }

    restoreSession();
    return () => { mounted = false; };
  }, [isReviewerRoute]);

  async function refreshSession() {
    try {
      const result = await api('/auth/me');
      if (canAccessStaffPortal(result.user)) {
        setSession(result.user);
      }
    } catch { /* ignore transient error */ }
  }

  async function login(npk, password) {
    await initializeApiBase();
    const result = await api('/auth/login', { method: 'POST', body: JSON.stringify({ npk, password }) });
    if (result.user.role === 'student') {
      authToken.clear();
      window.location.assign(import.meta.env.VITE_STUDENT_APP_URL || 'http://127.0.0.1:8082');
      return;
    }
    if (!canAccessStaffPortal(result.user)) throw new Error('Akun tidak memiliki akses ke portal ini.');
    setSession(result.user); navigate('/');
  }
  async function logout() {
    try { await api('/auth/logout', { method: 'POST', body: '{}' }); } catch { /* local logout still proceeds */ }
    authToken.clear(); setSession(null); navigate('/login');
  }
  if (isReviewerRoute) {
    return (
      <React.Suspense fallback={<div className="grid min-h-screen place-items-center bg-surface text-sm text-muted dark:bg-[#0f141c] dark:text-slate-400">Memuat portal reviewer...</div>}>
        <Routes>
          <Route path="/review" element={<ReviewerPortalPage />} />
          <Route path="/review/:token" element={<ReviewerPortalPage />} />
          <Route path="*" element={<Navigate to="/review" replace />} />
        </Routes>
      </React.Suspense>
    );
  }
  if (!authReady) return <main className="grid min-h-dvh place-items-center bg-surface text-sm text-muted dark:bg-[#0f141c] dark:text-slate-400">Memeriksa sesi...</main>;
  if (!session) return <Routes><Route path="/login" element={<LoginPage onLogin={login} />} /><Route path="*" element={<Navigate to="/login" replace />} /></Routes>;

  const fallbackRoute = (() => {
    if (hasPermission(session, 'dashboard.view')) return null;
    const candidates = [
      ['/materials', 'materials.view'],
      ['/courses', 'courses.view'],
      ['/quizzes', 'quizzes.view'],
      ['/grades', 'grades.view'],
      ['/forum', 'forum.view'],
      ['/notifications', 'notifications.view'],
      ['/enrollments', ['enrollments.view', 'enrollments.decide', 'enrollments.recommend']],
      ['/activity', 'activity.view'],
      ['/users', ['users.view', 'users.manage']],
      ['/reviewers', ['reviewers.view', 'reviewers.manage']],
      ['/roles', 'roles.manage'],
      ['/settings/api', 'settings.api']
    ];
    return candidates.find(([, perm]) => hasPermission(session, perm))?.[0] || null;
  })();

  const protect = (perm, element) => (hasPermission(session, perm) ? element : <Navigate to={fallbackRoute || '/'} replace />);

  return (
    <React.Suspense fallback={<div className="grid min-h-screen place-items-center bg-surface text-sm text-muted dark:bg-[#0f141c] dark:text-slate-400">Memuat portal LMS...</div>}>
      <Routes>
        <Route element={<AppLayout user={session} onLogout={logout} />}>
          <Route index element={hasPermission(session, 'dashboard.view') ? <DashboardPage /> : (fallbackRoute ? <Navigate to={fallbackRoute} replace /> : <DashboardPage />)} />
          <Route path="courses" element={protect('courses.view', <CoursesPage />)} />
          <Route path="courses/:id" element={protect('courses.view', <CourseDetailPage />)} />
          <Route path="materials" element={protect('materials.view', <MaterialsPage />)} />
          <Route path="quizzes" element={protect('quizzes.view', <QuizzesPage />)} />
          <Route path="activity" element={protect('activity.view', <ActivityPage />)} />
          <Route path="grades" element={protect('grades.view', <GradesPage />)} />
          <Route path="forum" element={protect('forum.view', <ForumPage />)} />
          <Route path="notifications" element={protect('notifications.view', <NotificationsPage />)} />
          <Route path="enrollments" element={protect(['enrollments.view', 'enrollments.decide', 'enrollments.recommend'], <EnrollmentsPage />)} />
          <Route path="reviewers" element={protect(['reviewers.view', 'reviewers.manage'], <ReviewersPage />)} />
          <Route path="users" element={protect(['users.view', 'users.manage'], <UsersPage />)} />
          <Route path="roles" element={protect('roles.manage', <RolesPage onPermissionsUpdated={refreshSession} />)} />
          <Route path="settings/api" element={protect('settings.api', <ApiSettingsPage />)} />
          <Route path="*" element={<Navigate to={fallbackRoute || '/'} replace />} />
        </Route>
      </Routes>
    </React.Suspense>
  );
}

createRoot(document.getElementById('root')).render(<React.StrictMode><BrowserRouter><Application /></BrowserRouter><PwaInstallPrompt /></React.StrictMode>);
