import { useEffect, useMemo, useState } from 'react';
import {
  Activity,
  AlertCircle,
  BookOpen,
  CalendarCheck,
  CheckCircle2,
  ClipboardCheck,
  Clock,
  FileText,
  History,
  LogIn,
  PauseCircle,
  RefreshCw,
  User,
  XCircle
} from 'lucide-react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';
import { useQueryParamState } from '../../utils/useQueryParamState';

function getActivityIcon(type = '', source = '', status = '') {
  const value = String(type).toLowerCase();
  if (source === 'job' || value.includes('job') || value.includes('antrean')) {
    if (status === 'failed' || value.includes('failed') || value.includes('gagal')) return XCircle;
    if (status === 'stopped' || value.includes('stopped') || value.includes('henti')) {
      return PauseCircle;
    }
    return CheckCircle2;
  }
  if (value.includes('login')) return LogIn;
  if (value.includes('course')) return BookOpen;
  if (value.includes('material')) return FileText;
  if (value.includes('user') || value.includes('account')) return User;
  if (value.includes('quiz') || value.includes('grade')) return ClipboardCheck;
  return History;
}

function formatDate(value) {
  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? 'Waktu tidak tersedia'
    : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
}

/**
 * Halaman Aktivitas & Antrean untuk memantau log portal dan background job secara realtime.
 */
export default function ActivityPage() {
  const [items, setItems] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [type, setType] = useState('all');
  const [statusTab, setStatusTab] = useQueryParamState('tab', 'all');

  const load = () =>
    api('/dashboard/activity?limit=100')
      .then((value) => {
        setItems(value.activities || []);
        setError('');
      })
      .catch((e) => {
        setError(e.message);
        showAlert.error('Gagal Memuat Log Aktivitas', e.message);
      })
      .finally(() => {
        setLoading(false);
      });

  useEffect(() => {
    let active = true;
    load();
    const handleActivityChange = () => {
      if (active) load();
    };
    window.addEventListener('dls_activity_changed', handleActivityChange);
    return () => {
      active = false;
      window.removeEventListener('dls_activity_changed', handleActivityChange);
    };
  }, []);

  const types = useMemo(
    () => [...new Set(items.map((item) => item.activity_type).filter(Boolean))].sort(),
    [items]
  );

  const failedJobsCount = useMemo(
    () =>
      items.filter((item) => item.activity_source === 'job' && item.item_status === 'failed').length,
    [items]
  );

  const completedJobsCount = useMemo(
    () =>
      items.filter((item) => item.activity_source === 'job' && item.item_status === 'completed')
        .length,
    [items]
  );

  const todayCount = useMemo(
    () =>
      items.filter((item) => new Date(item.created_at).toDateString() === new Date().toDateString())
        .length,
    [items]
  );

  const filtered = useMemo(() => {
    const query = search.trim().toLowerCase();
    return items.filter((item) => {
      if (statusTab === 'jobs' && item.activity_source !== 'job') return false;
      if (
        statusTab === 'failed' &&
        item.item_status !== 'failed' &&
        !String(item.activity_type).toLowerCase().includes('gagal')
      ) {
        return false;
      }
      if (
        statusTab === 'completed' &&
        item.item_status !== 'completed' &&
        !String(item.activity_type).toLowerCase().includes('berhasil')
      ) {
        return false;
      }

      if (type !== 'all' && item.activity_type !== type) return false;

      if (!query) return true;
      const haystack = `${item.description || ''} ${item.activity_type || ''} ${item.item_label || ''} ${item.message || ''} ${item.job_id || ''} ${item.actor_name || ''} ${item.created_at || ''}`.toLowerCase();
      return haystack.includes(query);
    });
  }, [items, statusTab, type, search]);

  const page = usePagination(filtered, 10);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Pemantauan & Log"
        title="Aktivitas & Antrean"
        description="Pantau riwayat aktivitas pengguna portal dan progres antrean proses latar belakang secara realtime."
        actions={
          <div className="flex flex-wrap items-center gap-2.5">
            <span className="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
              <span className="relative flex size-2">
                <span className="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                <span className="relative inline-flex size-2 rounded-full bg-emerald-600" />
              </span>
              Pembaruan Realtime
            </span>
            <Button
              type="button"
              variant="outline"
              size="sm"
              loading={loading}
              icon={<RefreshCw className="size-3.5" />}
              onClick={() => {
                setLoading(true);
                load();
              }}
            >
              Segarkan
            </Button>
          </div>
        }
      />

      {/* Ringkasan KPI */}
      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Total Log Tercatat"
          value={items.length}
          icon={<Activity className="size-5" />}
          tone="secondary"
          subtext="Hingga 100 aktivitas terbaru"
          loading={loading}
        />
        <StatCard
          label="Aktivitas Hari Ini"
          value={todayCount}
          icon={<CalendarCheck className="size-5" />}
          tone="info"
          loading={loading}
        />
        <StatCard
          label="Antrean Berhasil"
          value={completedJobsCount}
          icon={<CheckCircle2 className="size-5" />}
          tone="tertiary"
          loading={loading}
        />
        <StatCard
          label="Kendala / Gagal"
          value={failedJobsCount}
          icon={<XCircle className="size-5" />}
          tone={failedJobsCount > 0 ? 'primary' : 'neutral'}
          loading={loading}
        />
      </section>

      {error && (
        <div
          role="alert"
          className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          {error}
        </div>
      )}

      <Card
        id="activity-list"
        className="scroll-mt-20"
        noPadding
        title="Log Aktivitas & Antrean"
        subtitle={`Menampilkan ${filtered.length} dari ${items.length} catatan aktivitas terbaru.`}
      >
        <div className="space-y-4 border-b border-outline/40 p-5 dark:border-slate-800">
          {/* Quick Filter Tabs */}
          <div className="flex flex-wrap gap-2" role="tablist" aria-label="Filter status aktivitas">
            <button
              type="button"
              role="tab"
              data-dls-action-ignore="true"
              aria-selected={statusTab === 'all'}
              onClick={() => setStatusTab('all')}
              className={`inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition ${
                statusTab === 'all'
                  ? 'bg-primary text-white shadow-xs'
                  : 'border border-outline/60 bg-surface-lowest text-on-surface-variant hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300'
              }`}
            >
              <History className="size-3.5" />
              Semua Catatan ({items.length})
            </button>

            <button
              type="button"
              role="tab"
              data-dls-action-ignore="true"
              aria-selected={statusTab === 'jobs'}
              onClick={() => setStatusTab('jobs')}
              className={`inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition ${
                statusTab === 'jobs'
                  ? 'bg-secondary text-white shadow-xs'
                  : 'border border-outline/60 bg-surface-lowest text-on-surface-variant hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300'
              }`}
            >
              <Activity className="size-3.5" />
              Log Antrean &amp; Job ({completedJobsCount + failedJobsCount})
            </button>

            <button
              type="button"
              role="tab"
              data-dls-action-ignore="true"
              aria-selected={statusTab === 'failed'}
              onClick={() => setStatusTab('failed')}
              className={`inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition ${
                statusTab === 'failed'
                  ? 'bg-red-700 text-white shadow-xs'
                  : 'border border-red-200 bg-red-50/70 text-red-700 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300'
              }`}
            >
              <XCircle className="size-3.5" />
              Kendala / Gagal ({failedJobsCount})
            </button>

            <button
              type="button"
              role="tab"
              data-dls-action-ignore="true"
              aria-selected={statusTab === 'completed'}
              onClick={() => setStatusTab('completed')}
              className={`inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition ${
                statusTab === 'completed'
                  ? 'bg-tertiary text-white shadow-xs'
                  : 'border border-emerald-200 bg-emerald-50/70 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300'
              }`}
            >
              <CheckCircle2 className="size-3.5" />
              Berhasil ({completedJobsCount})
            </button>
          </div>

          <ListFilterBar
            search={search}
            onSearch={setSearch}
            placeholder="Cari aktivitas, NPK, nomor antrean, kendala, atau tanggal..."
            filter={type}
            onFilter={setType}
            label="Filter jenis aktivitas"
            options={[
              { value: 'all', label: 'Semua jenis' },
              ...types.map((value) => ({ value, label: value }))
            ]}
          />
        </div>

        {/* List Aktivitas */}
        {page.pagedItems.length ? (
          <div className="divide-y divide-outline/30 dark:divide-slate-800">
            {page.pagedItems.map((item) => {
              const isJob = item.activity_source === 'job' || Boolean(item.job_id);
              const isFailed =
                item.item_status === 'failed' ||
                String(item.activity_type).toLowerCase().includes('gagal');
              const isCompleted =
                item.item_status === 'completed' ||
                String(item.activity_type).toLowerCase().includes('berhasil');
              const isStopped = item.item_status === 'stopped';
              const IconComp = getActivityIcon(
                item.activity_type,
                item.activity_source,
                item.item_status
              );

              return (
                <article
                  key={item.activity_id}
                  className={`flex gap-4 p-5 transition hover:bg-surface-low/50 dark:hover:bg-slate-800/40 ${
                    isFailed ? 'bg-red-50/30 dark:bg-red-950/15' : ''
                  }`}
                >
                  <span
                    className={`grid size-11 shrink-0 place-items-center rounded-xl ${
                      isFailed
                        ? 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300'
                        : isCompleted
                          ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                          : isStopped
                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                            : 'bg-primary/10 text-primary dark:bg-red-950/50 dark:text-red-300'
                    }`}
                  >
                    <IconComp className="size-5" />
                  </span>

                  <div className="min-w-0 flex-1 space-y-2">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                      <div className="flex flex-wrap items-center gap-2">
                        {isJob && (
                          <span className="inline-flex items-center gap-1 rounded-md bg-slate-900 px-2 py-0.5 font-mono text-xs font-bold text-white dark:bg-slate-700">
                            Antrean #{item.job_id}
                          </span>
                        )}

                        {isJob && item.item_label && (
                          <span className="inline-flex items-center gap-1 rounded-md border border-outline/40 bg-surface-low px-2 py-0.5 font-mono text-xs font-semibold text-on-surface dark:border-slate-700 dark:bg-slate-800">
                            <span className="text-muted">Item:</span> {item.item_label}
                          </span>
                        )}

                        {isFailed ? (
                          <StatusBadge status="failed" label="Gagal / Terkendala" dot />
                        ) : isCompleted ? (
                          <StatusBadge status="completed" label="Berhasil" dot />
                        ) : isStopped ? (
                          <StatusBadge status="pending" label="Dihentikan" dot />
                        ) : (
                          <span className="rounded-md bg-surface-low px-2.5 py-0.5 text-[11px] font-semibold capitalize text-on-surface-variant dark:bg-slate-800 dark:text-slate-300">
                            {item.activity_type || 'Umum'}
                          </span>
                        )}
                      </div>

                      <p className="flex items-center gap-1.5 text-xs text-muted">
                        <Clock className="size-3.5" />
                        {formatDate(item.created_at)}
                      </p>
                    </div>

                    <p className="break-words text-sm font-semibold leading-relaxed text-on-surface">
                      {item.description || 'Aktivitas tercatat'}
                    </p>

                    {isJob && item.message && (
                      <div
                        className={`rounded-lg p-3 font-mono text-xs leading-relaxed ${
                          isFailed
                            ? 'border border-red-200 bg-red-50/90 text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200'
                            : 'border border-outline/40 bg-surface-low/80 text-on-surface-variant dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300'
                        }`}
                      >
                        <div className="mb-1 flex items-center gap-1.5 font-bold">
                          <AlertCircle className="size-3.5" />
                          <span>
                            {isFailed
                              ? 'Keterangan Kendala / Alasan Gagal:'
                              : 'Keterangan Proses:'}
                          </span>
                        </div>
                        <p className="whitespace-pre-wrap break-words">{item.message}</p>
                      </div>
                    )}

                    <div className="flex flex-wrap items-center gap-x-2 pt-0.5 text-xs text-muted">
                      {item.actor_name && (
                        <span>
                          Operator:{' '}
                          <span className="font-semibold text-on-surface">{item.actor_name}</span>{' '}
                          <span className="capitalize">
                            (
                            {item.actor_role ||
                              (item.activity_source === 'student' ? 'student' : 'akun')}
                            )
                          </span>
                        </span>
                      )}
                      {item.employee_npk && (
                        <>
                          <span aria-hidden="true">·</span>
                          <span className="font-mono">NPK: {item.employee_npk}</span>
                        </>
                      )}
                    </div>
                  </div>
                </article>
              );
            })}
          </div>
        ) : (
          <div className="px-6 py-12 text-center">
            <div className="mx-auto grid size-12 place-items-center rounded-xl bg-surface-low text-muted dark:bg-slate-800">
              <History className="size-6" />
            </div>
            <h3 className="mt-3 font-bold text-on-surface">
              {loading
                ? 'Memuat aktivitas...'
                : items.length
                  ? 'Tidak ada catatan yang cocok'
                  : 'Belum ada catatan aktivitas'}
            </h3>
            <p className="mt-1 text-xs text-muted">
              {loading
                ? 'Mohon tunggu sebentar.'
                : 'Catatan aktivitas dan antrean background job akan tampil di sini secara realtime.'}
            </p>
          </div>
        )}

        <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
      </Card>
    </div>
  );
}
