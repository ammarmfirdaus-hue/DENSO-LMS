import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import { api, authToken } from './api/client';
import AppLayout from './layouts/AppLayout';
import LoginPage from './features/auth/LoginPage';
const DashboardPage = React.lazy(() => import('./features/dashboard/DashboardPage'));
const CoursesPage = React.lazy(() => import('./features/courses/CoursesPage'));
const MaterialsPage = React.lazy(() => import('./features/materials/MaterialsPage'));
const CourseDetailPage = React.lazy(() => import('./features/courses/CourseDetailPage'));
const ActivityPage = React.lazy(() => import('./features/activity/ActivityPage'));
const GradesPage = React.lazy(() => import('./features/grades/GradesPage'));
const ForumPage = React.lazy(() => import('./features/forum/ForumPage'));
const NotificationsPage = React.lazy(() => import('./features/notifications/NotificationsPage'));
const UsersPage = React.lazy(() => import('./features/users/UsersPage'));
const ReviewersPage = React.lazy(() => import('./features/reviewers/ReviewersPage'));
import ReviewerPortalPage from './features/reviewer/ReviewerPortalPage';
import { canAccessAdminManagement } from './utils/access-control';
import './styles/index.css';

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
        const result = await api('/auth/me');
        if (result.user.role === 'student') {
          authToken.clear();
          window.location.replace(import.meta.env.VITE_STUDENT_APP_URL || 'http://127.0.0.1:8082');
          return;
        }
        if (!['teacher', 'admin'].includes(result.user.role)) {
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

  async function login(npk, password) {
    const result = await api('/auth/login', { method: 'POST', body: JSON.stringify({ npk, password }) });
    if (result.user.role === 'student') {
      authToken.clear();
      window.location.assign(import.meta.env.VITE_STUDENT_APP_URL || 'http://127.0.0.1:8082');
      return;
    }
    if (!['teacher', 'admin'].includes(result.user.role)) throw new Error('Akun tidak memiliki akses ke portal ini.');
    setSession(result.user); navigate('/');
  }
  async function logout() {
    try { await api('/auth/logout', { method: 'POST', body: '{}' }); } catch { /* local logout still proceeds */ }
    authToken.clear(); setSession(null); navigate('/login');
  }
  if (isReviewerRoute) {
    return <Routes><Route path="/review" element={<ReviewerPortalPage />} /><Route path="/review/:token" element={<ReviewerPortalPage />} /><Route path="*" element={<Navigate to="/review" replace />} /></Routes>;
  }
  if (!authReady) return <main className="grid min-h-dvh place-items-center bg-surface text-sm text-muted">Memeriksa sesi...</main>;
  if (!session) return <Routes><Route path="/login" element={<LoginPage onLogin={login} />} /><Route path="*" element={<Navigate to="/login" replace />} /></Routes>;
  return <React.Suspense fallback={<main className="grid min-h-dvh place-items-center bg-surface text-sm text-muted">Memuat halaman...</main>}><Routes><Route element={<AppLayout user={session} onLogout={logout} />}><Route index element={<DashboardPage />} /><Route path="courses" element={<CoursesPage />} /><Route path="courses/:id" element={<CourseDetailPage />} /><Route path="materials" element={<MaterialsPage />} /><Route path="activity" element={<ActivityPage />} /><Route path="grades" element={<GradesPage />} /><Route path="forum" element={<ForumPage />} /><Route path="notifications" element={<NotificationsPage />} /><Route path="reviewers" element={canAccessAdminManagement(session.role) ? <ReviewersPage /> : <Navigate to="/" replace />} /><Route path="users" element={canAccessAdminManagement(session.role) ? <UsersPage /> : <Navigate to="/" replace />} /><Route path="*" element={<Navigate to="/" replace />} /></Route></Routes></React.Suspense>;
}

createRoot(document.getElementById('root')).render(<React.StrictMode><BrowserRouter><Application /></BrowserRouter></React.StrictMode>);
