import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes, useNavigate } from 'react-router-dom';
import { api, authToken } from './api/client';
import AppLayout from './layouts/AppLayout';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import CoursesPage from './pages/CoursesPage';
import PlaceholderPage from './pages/PlaceholderPage';
import ActivityPage from './pages/ActivityPage';
import GradesPage from './pages/GradesPage';
import ForumPage from './pages/ForumPage';
import UsersPage from './pages/UsersPage';
import './styles/index.css';

function Application() {
  const [session, setSession] = useState(null); const navigate = useNavigate();
  async function login(npk, password) {
    const result = await api('/auth/login', { method: 'POST', body: JSON.stringify({ npk, password }) });
    if (!['teacher', 'admin'].includes(result.user.role)) throw new Error('Akun tidak memiliki akses ke portal ini.');
    authToken.set(result.token); setSession(result.user); navigate('/');
  }
  function logout() { authToken.clear(); setSession(null); navigate('/login'); }
  if (!session) return <Routes><Route path="/login" element={<LoginPage onLogin={login} />} /><Route path="*" element={<Navigate to="/login" replace />} /></Routes>;
  return <Routes><Route element={<AppLayout user={session} onLogout={logout} />}><Route index element={<DashboardPage />} /><Route path="courses" element={<CoursesPage />} /><Route path="activity" element={<ActivityPage />} /><Route path="grades" element={<GradesPage />} /><Route path="forum" element={<ForumPage />} /><Route path="users" element={session.role === 'admin' ? <UsersPage /> : <Navigate to="/" replace />} /><Route path="*" element={<Navigate to="/" replace />} /></Route></Routes>;
}

createRoot(document.getElementById('root')).render(<React.StrictMode><BrowserRouter><Application /></BrowserRouter></React.StrictMode>);
