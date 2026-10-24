import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../../api/client';

const stats = [
  { key: 'active_courses', label: 'Course aktif', icon: 'menu_book', tone: 'bg-primary/10 text-primary' },
  { key: 'active_employees', label: 'Pengguna aktif', icon: 'groups', tone: 'bg-emerald-50 text-emerald-700' },
  { key: 'active_sessions', label: 'Sesi belajar', icon: 'event_note', tone: 'bg-amber-50 text-amber-700' }
];

export default function DashboardPage() {
  const [data, setData] = useState(null);
  const [courses, setCourses] = useState([]);
  const [activities, setActivities] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => {
    Promise.all([api('/dashboard/summary'), api('/dashboard/courses'), api('/dashboard/activity')])
      .then(([summary, courseResult, activityResult]) => {
        setData(summary.summary);
        setCourses(courseResult.courses || []);
        setActivities(activityResult.activities || []);
      })
      .catch(e => setError(e.message));
  }, []);

  const materialTotal = courses.reduce((sum, course) => sum + Number(course.material_count || 0), 0);
  const sessionTotal = courses.reduce((sum, course) => sum + Number(course.session_count || 0), 0);
  const maxCount = Math.max(1, ...courses.slice(0, 6).map(course => Number(course.material_count || 0)));

  return <div className="mx-auto max-w-7xl space-y-7">
    <header className="flex flex-wrap items-end justify-between gap-4">
      <div><p className="text-xs font-bold uppercase tracking-[.18em] text-primary">Ringkasan</p><h1 className="mt-2 text-3xl font-bold tracking-tight">Dashboard</h1><p className="mt-2 text-sm text-muted">Pantau aktivitas pembelajaran dan pengelolaan LMS.</p></div>
      <span className="rounded-xl border border-outline/60 bg-white px-4 py-2.5 text-sm text-muted"><span className="material-symbol mr-2 align-middle text-primary">calendar_today</span>Periode berjalan</span>
    </header>
    {error && <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{error}</p>}

    <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      {stats.map(item => <article key={item.key} className="rounded-2xl border border-outline/50 bg-white p-5 shadow-sm"><div className="flex items-center justify-between gap-3"><div><p className="text-sm text-muted">{item.label}</p><p className="mt-3 text-3xl font-bold">{data ? Number(data[item.key] || 0).toLocaleString('id-ID') : '—'}</p></div><span className={`grid size-12 place-items-center rounded-xl ${item.tone}`}><span className="material-symbol">{item.icon}</span></span></div><p className="mt-4 text-xs text-muted">Data terbaru dari LMS</p></article>)}
    </section>

    <section className="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
      <article className="rounded-2xl border border-outline/50 bg-white p-5 shadow-sm sm:p-6">
        <div className="flex flex-wrap items-start justify-between gap-3"><div><h2 className="text-lg font-bold">Materi per course</h2><p className="mt-1 text-sm text-muted">Jumlah materi yang sudah tersedia pada setiap course.</p></div><span className="rounded-xl bg-primary/10 px-3 py-2 text-sm font-bold text-primary">{materialTotal} materi</span></div>
        {courses.length ? <div className="mt-6 space-y-4">{courses.slice(0, 6).map(course => { const count = Number(course.material_count || 0); return <div key={course.course_id} className="grid grid-cols-[minmax(0,1fr)_2fr_auto] items-center gap-3"><div className="min-w-0"><p className="truncate text-sm font-semibold">{course.title}</p><p className="text-xs text-muted">{course.course_code}</p></div><div className="h-2.5 overflow-hidden rounded-full bg-surface-low"><div className="h-full rounded-full bg-primary transition-all" style={{ width: `${Math.max(count ? 8 : 0, count / maxCount * 100)}%` }} /></div><span className="w-8 text-right text-sm font-bold">{count}</span></div>; })}</div> : <Empty text="Data course belum tersedia." />}
        <div className="mt-6 flex flex-wrap gap-4 border-t border-outline/40 pt-4 text-sm"><span className="text-muted"><b className="text-on-surface">{courses.length}</b> course</span><span className="text-muted"><b className="text-on-surface">{sessionTotal}</b> sesi</span><Link to="/courses" className="ml-auto font-semibold text-primary hover:underline">Kelola course <span className="material-symbol align-middle">arrow_forward</span></Link></div>
      </article>

      <article className="rounded-2xl border border-outline/50 bg-white p-5 shadow-sm sm:p-6"><div className="flex items-start justify-between gap-3"><div><h2 className="text-lg font-bold">Aktivitas terbaru</h2><p className="mt-1 text-sm text-muted">Aktivitas terkini pada LMS.</p></div><span className="material-symbol text-primary">query_stats</span></div>
        {activities.length ? <div className="mt-4 divide-y divide-outline/40">{activities.slice(0, 6).map((item, index) => <div key={item.activity_id || index} className="flex gap-3 py-3"><span className="mt-1 grid size-8 shrink-0 place-items-center rounded-lg bg-surface-low text-secondary"><span className="material-symbol text-lg">{item.activity_type === 'login' ? 'login' : 'history'}</span></span><div className="min-w-0"><p className="text-sm leading-5">{item.description || item.activity_type}</p><p className="mt-1 text-xs text-muted">{item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : ''}</p></div></div>)}</div> : <Empty text="Belum ada aktivitas terbaru." />}
      </article>
    </section>

    <section className="flex flex-col gap-4 rounded-2xl border border-outline/50 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6"><div><p className="text-xs font-bold uppercase tracking-[.16em] text-primary">Pintasan</p><h2 className="mt-1 text-lg font-bold">Kelola pembelajaran</h2><p className="mt-1 text-sm text-muted">Buka course untuk memperbarui sesi dan materi.</p></div><Link to="/courses" className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white hover:brightness-110">Lihat course <span className="material-symbol">arrow_forward</span></Link></section>
  </div>;
}

function Empty({ text }) { return <div className="mt-5 grid min-h-36 place-items-center rounded-xl bg-surface-low px-4 text-center text-sm text-muted">{text}</div>; }
