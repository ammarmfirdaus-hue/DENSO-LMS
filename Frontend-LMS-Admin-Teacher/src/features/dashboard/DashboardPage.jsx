import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';

// Dashboard KPI counter definitions
const statConfigs = [
  { key: 'active_courses', label: 'Course Aktif', icon: 'menu_book', tone: 'primary' },
  { key: 'active_employees', label: 'Pengguna Aktif', icon: 'groups', tone: 'emerald' },
  { key: 'active_sessions', label: 'Sesi Belajar', icon: 'event_note', tone: 'amber' }
];

/**
 * Dashboard page for administrators and teachers.
 * Displays key operational metrics, course material distributions, and recent audit activity.
 */
export default function DashboardPage() {
  const [data, setData] = useState(null);
  const [courses, setCourses] = useState([]);
  const [activities, setActivities] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Fetch summary counters, course list, and activity log in parallel
  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([
      api('/dashboard/summary'),
      api('/dashboard/courses'),
      api('/dashboard/activity')
    ])
      .then(([summary, courseResult, activityResult]) => {
        if (!active) return;
        setData(summary.summary);
        setCourses(courseResult.courses || []);
        setActivities(activityResult.activities || []);
        setError('');
      })
      .catch(e => {
        if (active) setError(e.message || 'Gagal memuat ringkasan dashboard.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, []);

  // Aggregate material and session totals
  const materialTotal = courses.reduce((sum, course) => sum + Number(course.material_count || 0), 0);
  const sessionTotal = courses.reduce((sum, course) => sum + Number(course.session_count || 0), 0);
  const maxCount = Math.max(1, ...courses.slice(0, 6).map(course => Number(course.material_count || 0)));

  return (
    <div className="mx-auto max-w-7xl space-y-7">
      {/* Standard Unified Header */}
      <PageHeader
        eyebrow="RINGKASAN SISTEM"
        title="Dashboard"
        description="Pantau ringkasan aktivitas pembelajaran, progres materi, dan metrik portal DENSO LMS."
        actions={
          <div className="inline-flex items-center gap-2 rounded-xl border border-outline/60 bg-white px-3.5 py-2 text-xs font-semibold text-muted shadow-xs dark:border-slate-800 dark:bg-[#1e293b]">
            <span className="material-symbol text-primary text-base" aria-hidden="true">calendar_today</span>
            <span>Periode Operasional Aktif</span>
          </div>
        }
      />

      {/* Alert on API failure */}
      {error && (
        <div role="alert" className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
          {error}
        </div>
      )}

      {/* 3 Primary Stat Cards */}
      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Statistik Utama">
        {statConfigs.map(item => (
          <StatCard
            key={item.key}
            label={item.label}
            value={data?.[item.key] ?? 0}
            icon={item.icon}
            tone={item.tone}
            loading={loading}
            subtext={
              <>
                <span className="material-symbol text-xs text-primary" aria-hidden="true">verified</span>
                <span>Data tersinkronisasi dari database LMS</span>
              </>
            }
          />
        ))}
      </section>

      {/* Material Progress & Activity Feed */}
      <section className="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
        
        {/* Left: Material distribution progress */}
        <article className="rounded-2xl border border-outline/50 bg-white p-5 shadow-xs sm:p-6 dark:border-slate-800 dark:bg-[#1e293b]">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 className="text-lg font-bold text-on-surface">Materi per Course</h2>
              <p className="mt-1 text-sm text-muted">Jumlah materi terpublikasi pada setiap course pembelajaran.</p>
            </div>
            <span className="rounded-xl bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary dark:bg-red-950/40 dark:text-red-300">
              {materialTotal} materi total
            </span>
          </div>

          {loading ? (
            <div className="mt-6 space-y-4 animate-pulse">
              {[1, 2, 3, 4].map(idx => (
                <div key={idx} className="h-9 rounded-xl bg-surface-low dark:bg-slate-800" />
              ))}
            </div>
          ) : courses.length ? (
            <div className="mt-6 space-y-4">
              {courses.slice(0, 6).map(course => {
                const count = Number(course.material_count || 0);
                const percent = Math.max(count ? 8 : 0, (count / maxCount) * 100);
                return (
                  <div key={course.course_id} className="grid grid-cols-[minmax(0,1fr)_2fr_auto] items-center gap-3">
                    <div className="min-w-0">
                      <p className="truncate text-sm font-semibold text-on-surface">{course.title}</p>
                      <p className="text-xs text-muted">{course.course_code}</p>
                    </div>
                    <div className="h-2.5 overflow-hidden rounded-full bg-surface-low dark:bg-slate-800">
                      <div
                        className="h-full rounded-full bg-primary transition-all duration-500 ease-out"
                        style={{ width: `${percent}%` }}
                      />
                    </div>
                    <span className="w-8 text-right text-sm font-bold text-on-surface">{count}</span>
                  </div>
                );
              })}
            </div>
          ) : (
            <EmptyState text="Belum ada data course yang tersedia." />
          )}

          <div className="mt-6 flex flex-wrap items-center gap-4 border-t border-outline/40 pt-4 text-xs text-muted dark:border-slate-800">
            <span><strong className="font-semibold text-on-surface">{courses.length}</strong> course</span>
            <span aria-hidden="true">·</span>
            <span><strong className="font-semibold text-on-surface">{sessionTotal}</strong> sesi belajar</span>
            <Link to="/courses" className="ml-auto font-bold text-primary hover:underline inline-flex items-center gap-1">
              Kelola Course <span className="material-symbol text-base" aria-hidden="true">arrow_forward</span>
            </Link>
          </div>
        </article>

        {/* Right: Recent activity feed */}
        <article className="rounded-2xl border border-outline/50 bg-white p-5 shadow-xs sm:p-6 dark:border-slate-800 dark:bg-[#1e293b]">
          <div className="flex items-start justify-between gap-3">
            <div>
              <h2 className="text-lg font-bold text-on-surface">Aktivitas Terkini</h2>
              <p className="mt-1 text-sm text-muted">Log peristiwa dan progres antrean terbaru.</p>
            </div>
            <Link
              to="/activity"
              aria-label="Buka halaman semua aktivitas"
              className="text-xs font-bold text-primary hover:underline"
            >
              Lihat Semua
            </Link>
          </div>

          {loading ? (
            <div className="mt-4 divide-y divide-outline/40 space-y-3 dark:divide-slate-800 animate-pulse">
              {[1, 2, 3, 4].map(idx => (
                <div key={idx} className="h-10 rounded-xl bg-surface-low dark:bg-slate-800" />
              ))}
            </div>
          ) : activities.length ? (
            <div className="mt-4 divide-y divide-outline/30 dark:divide-slate-800">
              {activities.slice(0, 6).map((item, index) => {
                const isJob = item.activity_source === 'job' || Boolean(item.job_id);
                const isFailed = item.item_status === 'failed' || String(item.activity_type).toLowerCase().includes('gagal');
                return (
                  <div key={item.activity_id || index} className="flex gap-3 py-3 items-start">
                    <span
                      className={`mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg ${
                        isFailed
                          ? 'bg-red-500/15 text-red-600 dark:bg-red-950 dark:text-red-300'
                          : isJob
                            ? 'bg-green-500/15 text-green-700 dark:bg-green-950 dark:text-green-300'
                            : 'bg-surface-low text-secondary dark:bg-slate-800 dark:text-slate-200'
                      }`}
                    >
                      <span className="material-symbol text-base" aria-hidden="true">
                        {isFailed ? 'cancel' : isJob ? 'task_alt' : item.activity_type === 'login' ? 'login' : 'history'}
                      </span>
                    </span>
                    <div className="min-w-0 flex-1">
                      <p className="text-xs sm:text-sm font-semibold leading-5 text-on-surface line-clamp-2">
                        {item.description || item.activity_type}
                      </p>
                      <p className="mt-0.5 text-[11px] text-muted">
                        {item.created_at ? new Date(item.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : ''}
                      </p>
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <EmptyState text="Belum ada catatan aktivitas terbaru." />
          )}
        </article>
      </section>

      {/* Quick Course Shortcut Banner */}
      <section className="flex flex-col gap-4 rounded-2xl border border-outline/50 bg-white p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-slate-800 dark:bg-[#1e293b]">
        <div>
          <p className="text-xs font-bold uppercase tracking-[.16em] text-primary">Pintasan Cepat</p>
          <h2 className="mt-1 text-lg font-bold text-on-surface">Kelola Materi &amp; Course</h2>
          <p className="mt-1 text-sm text-muted">Buka menu kelola course untuk menambah sesi, materi, dan mengatur trainer.</p>
        </div>
        <Link
          to="/courses"
          className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-xs hover:brightness-110 active:scale-95 transition cursor-pointer"
        >
          <span>Buka Katalog Course</span>
          <span className="material-symbol text-lg" aria-hidden="true">arrow_forward</span>
        </Link>
      </section>
    </div>
  );
}

/** Placeholder state when dataset is empty */
function EmptyState({ text }) {
  return (
    <div className="mt-5 grid min-h-32 place-items-center rounded-xl bg-surface-low/70 px-4 text-center text-xs text-muted dark:bg-slate-800/50">
      {text}
    </div>
  );
}
