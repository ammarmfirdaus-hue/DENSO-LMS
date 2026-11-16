import { useCallback, useEffect, useState } from 'react';
import { Link, useOutletContext } from 'react-router-dom';
import {
  BookOpen,
  Users,
  Layers,
  Award,
  ArrowRight,
  RefreshCw,
  CheckCircle2,
  XCircle,
  Clock,
  FileCheck2,
  UserPlus,
  PlusCircle,
  ClipboardCheck,
  BarChart3
} from 'lucide-react';
import { api } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import Button from '../../components/Button';
import showAlert from '../../utils/alert';
import { hasPermission } from '../../utils/access-control';
import {
  ActivityTrendChart,
  CoursePerformanceChart,
  MaterialPipelineChart,
  ProgressBucketChart,
  MaterialTypeChart
} from '../../components/charts/DashboardCharts';

const WINDOW_OPTIONS = [
  { value: 7, label: '7 Hari' },
  { value: 14, label: '14 Hari' },
  { value: 30, label: '30 Hari' }
];

function computeSeriesDelta(values = []) {
  if (!Array.isArray(values) || values.length < 2) return null;
  const first = Number(values[0] || 0);
  const last = Number(values[values.length - 1] || 0);
  const diff = last - first;
  if (diff > 0) return { text: `+${diff}`, tone: 'positive' };
  if (diff < 0) return { text: `${diff}`, tone: 'negative' };
  return { text: 'Stabil', tone: 'neutral' };
}

/**
 * Operational Command Center Dashboard backed by real SQL Server + MySQL telemetry and Chart.js.
 */
export default function DashboardPage() {
  const user = useOutletContext();
  const isAdmin = user?.role === 'admin';
  const [days, setDays] = useState(7);
  const [analytics, setAnalytics] = useState(null);
  const [fallbackSummary, setFallbackSummary] = useState(null);
  const [courses, setCourses] = useState([]);
  const [activities, setActivities] = useState([]);
  const [distTab, setDistTab] = useState('progress'); // 'progress' | 'formats'
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadDashboard = useCallback(async ({ silent = false } = {}) => {
    if (silent) setRefreshing(true);
    else setLoading(true);

    try {
      const [analyticsRes, summaryRes, courseRes, activityRes] = await Promise.allSettled([
        api(`/dashboard/analytics?days=${days}`),
        api('/dashboard/summary'),
        api('/dashboard/courses?limit=8'),
        api('/dashboard/activity?limit=12')
      ]);

      if (analyticsRes.status === 'fulfilled') {
        setAnalytics(analyticsRes.value);
      }
      if (summaryRes.status === 'fulfilled') {
        setFallbackSummary(summaryRes.value.summary || null);
      }
      if (courseRes.status === 'fulfilled') {
        setCourses(courseRes.value.courses || []);
      }
      if (activityRes.status === 'fulfilled') {
        setActivities(activityRes.value.activities || []);
      }

      if (analyticsRes.status === 'rejected' && summaryRes.status === 'rejected') {
        const msg =
          analyticsRes.reason?.message ||
          summaryRes.reason?.message ||
          'Gagal memuat ringkasan dashboard.';
        setError(msg);
        showAlert.error('Gagal Memuat Dashboard', msg);
      } else {
        setError('');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [days]);

  useEffect(() => {
    loadDashboard();
  }, [loadDashboard]);

  useEffect(() => {
    const onActivity = () => loadDashboard({ silent: true });
    window.addEventListener('dls_activity_changed', onActivity);
    return () => window.removeEventListener('dls_activity_changed', onActivity);
  }, [loadDashboard]);

  const s = analytics?.summary || {};
  const sp = analytics?.sparklines || {};
  const activeCoursesValue = s.activeCourses ?? fallbackSummary?.active_courses ?? 0;
  const activeUsersValue = s.activeUsersTotal ?? fallbackSummary?.active_employees ?? 0;
  const activeSessionsValue = s.activeSessions ?? fallbackSummary?.active_sessions ?? 0;
  const avgProgressValue = s.avgProgress ?? 0;

  const courseDelta = computeSeriesDelta(sp.activeCourses);
  const userDelta = computeSeriesDelta(sp.activeUsers);
  const sessionDelta = computeSeriesDelta(sp.activeSessions);

  const pipeline = analytics?.materialPipeline || {};
  const pipelinePendingTotal = Number(pipeline.pending_admin || 0) + Number(pipeline.pending_review || 0);

  const quickActions = [
    hasPermission(user, 'materials.view') && {
      key: 'materials',
      to: '/materials#materials-submissions',
      title: 'Tinjau materi',
      description: 'Periksa materi yang menunggu persetujuan.',
      count: pipelinePendingTotal,
      countLabel: 'menunggu',
      Icon: FileCheck2,
      iconColor: 'text-blue-600 dark:text-blue-400'
    },
    hasPermission(user, 'materials.submit') && {
      key: 'submit-material',
      to: '/materials?action=submit#submit-material',
      title: 'Ajukan materi',
      description: 'Kirim materi baru untuk ditinjau oleh reviewer.',
      Icon: PlusCircle,
      iconColor: 'text-cyan-600 dark:text-cyan-400'
    },
    hasPermission(user, 'courses.create') && {
      key: 'course-create',
      to: '/courses/new',
      title: 'Buat course',
      description: 'Tambahkan course baru ke katalog pembelajaran.',
      Icon: PlusCircle,
      iconColor: 'text-emerald-600 dark:text-emerald-400'
    },
    hasPermission(user, 'courses.view') && !hasPermission(user, 'courses.create') && {
      key: 'courses',
      to: '/courses#courses-list',
      title: 'Buka katalog course',
      description: 'Lihat course aktif dan sesi pembelajaran.',
      count: activeCoursesValue,
      countLabel: 'course aktif',
      Icon: BookOpen,
      iconColor: 'text-emerald-600 dark:text-emerald-400'
    },
    hasPermission(user, 'enrollments.view') && {
      key: 'enrollments',
      to: '/enrollments#enrollments-list',
      title: 'Persetujuan peserta',
      description: 'Tinjau permintaan akses ke course.',
      count: Number(s.pendingEnrollmentRequests || 0),
      countLabel: 'permintaan',
      Icon: ClipboardCheck,
      iconColor: 'text-amber-600 dark:text-amber-400'
    },
    isAdmin && hasPermission(user, 'users.manage') && {
      key: 'users',
      to: '/users/new',
      title: 'Tambah pengguna',
      description: 'Buat akun Student, Teacher, Admin, atau Reviewer.',
      Icon: UserPlus,
      iconColor: 'text-purple-600 dark:text-purple-400'
    },
    hasPermission(user, 'quizzes.view') && {
      key: 'quizzes',
      to: '/quizzes#quizzes-list',
      title: 'Kelola quiz',
      description: 'Buat atau periksa quiz dalam course.',
      Icon: Award,
      iconColor: 'text-indigo-600 dark:text-indigo-400'
    },
    hasPermission(user, 'grades.view') && {
      key: 'grades',
      to: '/grades#grades-list',
      title: 'Lihat nilai',
      description: 'Pantau nilai dan progres peserta.',
      Icon: BarChart3,
      iconColor: 'text-slate-600 dark:text-slate-400'
    }
  ].filter(Boolean);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="RINGKASAN OPERASIONAL"
        title="Dashboard"
        description="Pantau statistik pelatihan, keaktifan pengguna, persetujuan materi, dan capaian peserta langsung dari database LMS."
        actions={
          <div className="flex flex-wrap items-center gap-2">
            <div
              className="inline-flex rounded-lg border border-outline bg-white p-1 dark:bg-[#171e29]"
              role="group"
              aria-label="Rentang waktu grafik"
            >
              {WINDOW_OPTIONS.map(opt => (
                <button
                  key={opt.value}
                  type="button"
                  data-dls-action-ignore="true"
                  onClick={() => setDays(opt.value)}
                  className={`h-8 cursor-pointer rounded-md px-3 text-xs font-semibold transition ${
                    days === opt.value
                      ? 'bg-primary text-white shadow-xs'
                      : 'text-muted hover:text-on-surface'
                  }`}
                >
                  {opt.label}
                </button>
              ))}
            </div>

            <Button
              variant="secondary"
              size="sm"
              loading={refreshing}
              icon={<RefreshCw className="h-3.5 w-3.5" />}
              onClick={() => loadDashboard({ silent: true })}
            >
              Muat Ulang
            </Button>
          </div>
        }
      />

      {error && (
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          {error}
        </div>
      )}

      {/* Row 1: 4 Telemetry StatCards with real Chart.js sparklines */}
      <section id="tour-overview" className="scroll-mt-20 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistik Utama">
        <StatCard
          label="Course Aktif"
          to="/courses#courses-list"
          value={activeCoursesValue}
          icon={<BookOpen className="h-5 w-5" />}
          tone="primary"
          loading={loading}
          delta={courseDelta?.text}
          deltaTone={courseDelta?.tone || 'neutral'}
          deltaLabel={`${days} hari`}
          sparkline={sp.activeCourses}
          sparklineLabels={sp.labels}
          sparklineUnit="course"
          subtext={
            s.totalCourses !== undefined
              ? `${s.wajibCourses ?? 0} Wajib · ${s.pilihanCourses ?? 0} Pilihan`
              : 'Katalog course aktif'
          }
        />

        <StatCard
          label="Pengguna Aktif"
          to="/users?status=active#users-list"
          value={activeUsersValue}
          icon={<Users className="h-5 w-5" />}
          tone="secondary"
          loading={loading}
          delta={userDelta?.text}
          deltaTone={userDelta?.tone || 'neutral'}
          deltaLabel={`${days} hari`}
          sparkline={sp.activeUsers}
          sparklineLabels={sp.labels}
          sparklineUnit="akun"
          subtext={
            isAdmin
              ? `${s.activeEmployees ?? 0} Staff · ${s.activeStudents ?? 0} Student`
              : `${s.activeStudents ?? 0} peserta di course Anda`
          }
        />

        <StatCard
          label="Sesi Belajar"
          to="/courses#courses-list"
          value={activeSessionsValue}
          icon={<Layers className="h-5 w-5" />}
          tone="info"
          loading={loading}
          delta={sessionDelta?.text}
          deltaTone={sessionDelta?.tone || 'neutral'}
          deltaLabel={`${days} hari`}
          sparkline={sp.activeSessions}
          sparklineLabels={sp.labels}
          sparklineUnit="sesi"
          subtext={`${s.publishedMaterials ?? 0} materi terpublikasi`}
        />

        <StatCard
          label="Rata-rata Progres"
          to="/grades#grades-list"
          value={`${avgProgressValue}%`}
          icon={<Award className="h-5 w-5" />}
          tone="tertiary"
          loading={loading}
          delta={s.avgScore !== null && s.avgScore !== undefined ? `Nilai ${s.avgScore}` : 'Aktif'}
          deltaTone={Number(avgProgressValue) >= 50 ? 'positive' : 'neutral'}
          deltaLabel="Aktivitas"
          sparkline={sp.dailyActivities}
          sparklineLabels={sp.labels}
          sparklineUnit="aktivitas"
          subtext={`${s.completedEnrollments ?? 0} selesai dari ${s.totalEnrollments ?? 0}`}
        />
      </section>

      {/* Row 2: Activity Trend Line Chart + Material Moderation Doughnut */}
      <section id="tour-charts" className="scroll-mt-20 grid gap-4 lg:grid-cols-[1.65fr_1fr]">
        <Card
          title="Tren Aktivitas & Pembelajaran Harian"
          subtitle={`Perkembangan interaksi peserta, audit staff, dan pengajuan materi dalam ${days} hari terakhir.`}
          action={
            <span className="rounded-full border border-outline bg-surface-low px-2.5 py-0.5 font-mono text-xs font-semibold text-on-surface">
              {(analytics?.trends || []).reduce((sum, d) => sum + d.activities + d.studentUpdates, 0)} kejadian
            </span>
          }
        >
          {loading ? (
            <div className="h-72 animate-pulse rounded-lg bg-surface-container" />
          ) : (
            <div className="h-72 w-full">
              <ActivityTrendChart trends={analytics?.trends || []} />
            </div>
          )}
        </Card>

        <Card
          title="Alur Persetujuan Materi"
          subtitle="Distribusi status pengajuan materi oleh trainer ke admin dan reviewer."
          action={
            <Link
              to="/materials"
              className="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
            >
              <span>Kelola</span>
              <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          }
          footer={
            <div className="flex flex-wrap items-center justify-between gap-2">
              <span>
                <strong className="font-semibold text-on-surface">{pipelinePendingTotal}</strong> materi menunggu tindakan
              </span>
              {s.pendingEnrollmentRequests > 0 && (
                <Link to="/enrollments" className="font-semibold text-primary hover:underline">
                  {s.pendingEnrollmentRequests} permintaan akses course →
                </Link>
              )}
            </div>
          }
        >
          {loading ? (
            <div className="h-64 animate-pulse rounded-lg bg-surface-container" />
          ) : (
            <div className="h-64 w-full">
              <MaterialPipelineChart pipeline={pipeline} />
            </div>
          )}
        </Card>
      </section>

      {/* Row 3: Top Course Performance + Progress Distribution / Material Formats */}
      <section className="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <Card
          title="Perbandingan Course Teraktif"
          subtitle="Jumlah peserta terdaftar, peserta tuntas, dan materi terbit pada course teratas."
          action={
            <Link
              to="/courses"
              className="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
            >
              <span>Lihat Katalog</span>
              <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          }
        >
          {loading ? (
            <div className="h-72 animate-pulse rounded-lg bg-surface-container" />
          ) : (
            <div className="h-72 w-full">
              <CoursePerformanceChart courses={analytics?.courseBreakdown || []} />
            </div>
          )}
        </Card>

        <Card
          title={distTab === 'progress' ? 'Sebaran Progres Belajar' : 'Format Materi Terpublikasi'}
          subtitle={
            distTab === 'progress'
              ? 'Distribusi capaian pembelajaran peserta pada seluruh course aktif.'
              : 'Ragam jenis konten pembelajaran yang tersedia bagi peserta.'
          }
          action={
            <div className="inline-flex rounded-lg border border-outline bg-surface-low p-0.5">
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => setDistTab('progress')}
                className={`cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold transition ${
                  distTab === 'progress' ? 'bg-white text-on-surface shadow-2xs dark:bg-slate-800' : 'text-muted hover:text-on-surface'
                }`}
              >
                Progres
              </button>
              <button
                type="button"
                data-dls-action-ignore="true"
                onClick={() => setDistTab('formats')}
                className={`cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold transition ${
                  distTab === 'formats' ? 'bg-white text-on-surface shadow-2xs dark:bg-slate-800' : 'text-muted hover:text-on-surface'
                }`}
              >
                Format
              </button>
            </div>
          }
          footer={
            <div className="grid grid-cols-3 gap-2 text-center">
              <div>
                <span className="block text-[11px] uppercase tracking-[0.04em] text-muted">Aktif</span>
                <strong className="font-mono text-sm font-bold text-on-surface">
                  {analytics?.progressDistribution?.status?.active ?? 0}
                </strong>
              </div>
              <div>
                <span className="block text-[11px] uppercase tracking-[0.04em] text-muted">Lulus</span>
                <strong className="font-mono text-sm font-bold text-emerald-700 dark:text-emerald-400">
                  {analytics?.progressDistribution?.status?.completed ?? 0}
                </strong>
              </div>
              <div>
                <span className="block text-[11px] uppercase tracking-[0.04em] text-muted">Rata Nilai</span>
                <strong className="font-mono text-sm font-bold text-primary">
                  {s.avgScore !== null && s.avgScore !== undefined ? s.avgScore : '—'}
                </strong>
              </div>
            </div>
          }
        >
          {loading ? (
            <div className="h-56 animate-pulse rounded-lg bg-surface-container" />
          ) : distTab === 'progress' ? (
            <div className="h-56 w-full">
              <ProgressBucketChart buckets={analytics?.progressDistribution?.buckets || []} />
            </div>
          ) : (
            <div className="h-56 w-full">
              <MaterialTypeChart materialTypes={analytics?.materialTypes || []} />
            </div>
          )}
        </Card>
      </section>

      {/* Row 4: Course Summary Table & Live Activity Feed */}
      <section className="grid gap-4 xl:grid-cols-[1.2fr_1fr]">
        <Card
          title="Ringkasan Course Terbaru"
          subtitle="Akses cepat detail modul, jumlah sesi, dan materi yang tersedia."
          noPadding
          action={
            <Link
              to="/courses"
              className="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
            >
              <span>Kelola Course</span>
              <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          }
        >
          {loading ? (
            <div className="space-y-3 p-6 animate-pulse">
              {[1, 2, 3, 4].map(idx => (
                <div key={idx} className="h-10 rounded-lg bg-surface-container" />
              ))}
            </div>
          ) : courses.length ? (
            <div className="divide-y divide-outline">
              {courses.slice(0, 6).map(course => (
                <Link
                  key={course.course_id}
                  to={`/courses/view/${course.public_id || course.course_id}`}
                  className="flex items-center justify-between gap-4 px-6 py-3.5 transition hover:bg-surface-low/70"
                >
                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                      <span className="font-mono text-xs font-semibold text-muted">{course.course_code}</span>
                      <StatusBadge status={course.category} label={course.category} />
                    </div>
                    <p className="mt-1 truncate text-sm font-semibold text-on-surface">{course.title}</p>
                    <p className="mt-0.5 text-xs text-muted">
                      Trainer: {course.assigned_trainer_name || course.assigned_trainer_npk || 'Belum ditentukan'}
                    </p>
                  </div>
                  <div className="flex shrink-0 items-center gap-4 text-right text-xs">
                    <div>
                      <span className="block font-mono text-sm font-bold text-on-surface">
                        {course.session_count ?? 0}
                      </span>
                      <span className="text-muted">Sesi</span>
                    </div>
                    <div>
                      <span className="block font-mono text-sm font-bold text-primary">
                        {course.material_count ?? 0}
                      </span>
                      <span className="text-muted">Materi</span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <div className="p-8 text-center text-sm text-muted">Belum ada course yang tersedia.</div>
          )}
        </Card>

        <div id="tour-recent-activity" className="scroll-mt-20">
          <Card
            title="Aktivitas Terkini"
            subtitle="Catatan perubahan sistem, persetujuan materi, dan pembaruan peserta."
            noPadding
            action={
              <Link
                to="/activity"
                aria-label="Buka halaman semua aktivitas"
                className="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
              >
                <span>Lihat Semua</span>
                <ArrowRight className="h-3.5 w-3.5" />
              </Link>
            }
          >
          {loading ? (
            <div className="space-y-3 p-6 animate-pulse">
              {[1, 2, 3, 4].map(idx => (
                <div key={idx} className="h-10 rounded-lg bg-surface-container" />
              ))}
            </div>
          ) : activities.length ? (
            <div className="divide-y divide-outline">
              {activities.slice(0, 6).map((item, index) => {
                const isJob = item.activity_source === 'job' || Boolean(item.job_id);
                const isFailed =
                  item.item_status === 'failed' ||
                  String(item.activity_type || '').toLowerCase().includes('gagal');
                return (
                  <div key={item.activity_id || index} className="flex items-start gap-3 px-6 py-3.5">
                    <span className="mt-0.5 flex shrink-0 items-center justify-center">
                      {isFailed ? (
                        <XCircle className="size-5 text-red-600 dark:text-red-400" aria-hidden="true" />
                      ) : isJob ? (
                        <CheckCircle2 className="size-5 text-emerald-600 dark:text-emerald-400" aria-hidden="true" />
                      ) : (
                        <Clock className="size-5 text-slate-400 dark:text-slate-500" aria-hidden="true" />
                      )}
                    </span>
                    <div className="min-w-0 flex-1">
                      <p className="line-clamp-2 text-sm font-medium leading-snug text-on-surface">
                        {item.description || item.activity_type}
                      </p>
                      <p className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">
                        {item.actor_name && <span className="font-medium text-on-surface/80">{item.actor_name}</span>}
                        <span>
                          {item.created_at
                            ? new Date(item.created_at).toLocaleString('id-ID', {
                                dateStyle: 'short',
                                timeStyle: 'short'
                              })
                            : ''}
                        </span>
                      </p>
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="p-8 text-center text-sm text-muted">Belum ada catatan aktivitas terbaru.</div>
          )}
        </Card>
      </div>
    </section>

      {/* Quick Action Cards */}
      <section
        id="quick-actions"
        aria-labelledby="quick-actions-title"
        className="dls-card-surface rounded-xl p-4 sm:p-5"
      >
        <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p className="text-xs font-semibold text-primary">Langkah cepat</p>
            <h2 id="quick-actions-title" className="mt-0.5 text-lg font-bold text-on-surface">
              Mulai dari sini
            </h2>
            <p className="mt-1 text-sm text-muted">Akses menu yang paling sering digunakan.</p>
          </div>
          <span className="text-xs text-muted">{quickActions.length} pilihan</span>
        </div>

        <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {quickActions.map(({ key, to, title, description, count, countLabel, Icon, iconColor }) => (
            <Link
              key={key}
              to={to}
              className="dls-card-surface dls-card-interactive group flex min-h-32 flex-col rounded-xl p-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            >
              <Icon className={`size-6 shrink-0 ${iconColor}`} aria-hidden="true" />
              <span className="mt-3 flex items-start justify-between gap-2">
                <span className="text-sm font-semibold text-on-surface">{title}</span>
                <ArrowRight className="mt-0.5 size-4 shrink-0 text-muted transition-transform group-hover:translate-x-0.5 group-hover:text-primary" aria-hidden="true" />
              </span>
              <span className="mt-1 text-xs leading-relaxed text-muted">{description}</span>
              {count !== undefined && (
                <span className="mt-auto pt-3 text-xs font-semibold text-on-surface-variant">
                  {count} {countLabel}
                </span>
              )}
            </Link>
          ))}
        </div>
      </section>
    </div>
  );
}
