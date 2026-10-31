import { useEffect, useMemo, useState } from 'react';
import { api } from '../../api/client';
import ListFilterBar from '../../components/ListFilterBar';
import Pagination, { usePagination } from '../../components/Pagination';
import PageHeader from '../../components/PageHeader';

function activityIcon(type = '', source = '', status = '') {
  const value = String(type).toLowerCase();
  if (source === 'job' || value.includes('job') || value.includes('antrean')) {
    if (status === 'failed' || value.includes('failed') || value.includes('gagal')) return 'cancel';
    if (status === 'stopped' || value.includes('stopped') || value.includes('henti')) return 'pause_circle';
    return 'task_alt';
  }
  if (value.includes('login')) return 'login';
  if (value.includes('course')) return 'menu_book';
  if (value.includes('material')) return 'description';
  if (value.includes('user') || value.includes('account')) return 'person';
  if (value.includes('quiz') || value.includes('grade')) return 'quiz';
  return 'history';
}

function formatDate(value) {
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? 'Waktu tidak tersedia' : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
}

export default function ActivityPage() {
  const [items, setItems] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [type, setType] = useState('all');
  const [statusTab, setStatusTab] = useState('all'); // 'all' | 'jobs' | 'failed' | 'completed'

  const load = () => {
    return api('/dashboard/activity?limit=100')
      .then(value => {
        setItems(value.activities || []);
        setError('');
      })
      .catch(e => {
        setError(e.message);
      })
      .finally(() => {
        setLoading(false);
      });
  };

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

  const types = useMemo(() => [...new Set(items.map(item => item.activity_type).filter(Boolean))].sort(), [items]);

  const failedJobsCount = useMemo(() => {
    return items.filter(item => item.activity_source === 'job' && item.item_status === 'failed').length;
  }, [items]);

  const completedJobsCount = useMemo(() => {
    return items.filter(item => item.activity_source === 'job' && item.item_status === 'completed').length;
  }, [items]);

  const todayCount = useMemo(() => {
    return items.filter(item => new Date(item.created_at).toDateString() === new Date().toDateString()).length;
  }, [items]);

  const filtered = useMemo(() => {
    const query = search.trim().toLowerCase();
    return items.filter(item => {
      // Filter tab
      if (statusTab === 'jobs' && item.activity_source !== 'job') return false;
      if (statusTab === 'failed' && (item.item_status !== 'failed' && !String(item.activity_type).toLowerCase().includes('gagal'))) return false;
      if (statusTab === 'completed' && (item.item_status !== 'completed' && !String(item.activity_type).toLowerCase().includes('berhasil'))) return false;

      // Dropdown type
      if (type !== 'all' && item.activity_type !== type) return false;

      // Search
      if (!query) return true;
      const haystack = `${item.description || ''} ${item.activity_type || ''} ${item.item_label || ''} ${item.message || ''} ${item.job_id || ''} ${item.actor_name || ''} ${item.created_at || ''}`.toLowerCase();
      return haystack.includes(query);
    });
  }, [items, statusTab, type, search]);

  const page = usePagination(filtered, 10);

  return (
    <div className="mx-auto max-w-7xl space-y-6">
      {/* Header */}
      <PageHeader
        eyebrow="PEMANTAUAN & LOG"
        title="Aktivitas & Antrean"
        description="Pantau riwayat aktivitas portal dan progres antrean background job secara realtime."
        actions={
          <div className="flex flex-wrap items-center gap-3">
            <div className="flex items-center gap-3 rounded-2xl border border-outline/50 bg-white px-4 py-2.5 shadow-xs dark:border-slate-800 dark:bg-[#1e293b]">
              <span className="grid size-9 place-items-center rounded-xl bg-primary/10 text-primary">
                <span className="material-symbol text-xl">event_available</span>
              </span>
              <div>
                <p className="text-lg font-bold leading-none">{todayCount}</p>
                <p className="mt-1 text-[11px] text-muted">Aktivitas hari ini</p>
              </div>
            </div>

            {failedJobsCount > 0 && (
              <div className="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50/70 px-4 py-2.5 shadow-xs dark:border-red-900 dark:bg-red-950/30">
                <span className="grid size-9 place-items-center rounded-xl bg-red-600 text-white shadow-xs">
                  <span className="material-symbol text-xl">error</span>
                </span>
                <div>
                  <p className="text-lg font-bold leading-none text-red-700 dark:text-red-400">{failedJobsCount}</p>
                  <p className="mt-1 text-[11px] font-semibold text-red-600 dark:text-red-300">Kendala / Gagal</p>
                </div>
              </div>
            )}

            <div className="flex items-center gap-2 rounded-xl border border-green-200 bg-white px-3 py-2 text-xs font-semibold text-green-800 dark:border-green-900 dark:bg-[#1e293b] dark:text-green-300">
              <span className="relative flex size-2.5">
                <span className="absolute inline-flex size-full animate-ping rounded-full bg-green-400 opacity-75" />
                <span className="relative inline-flex size-2.5 rounded-full bg-green-600" />
              </span>
              Pembaruan Realtime
            </div>
          </div>
        }
      />

      {error && (
        <p role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
          {error}
        </p>
      )}

      {/* Main Container */}
      <section className="overflow-hidden rounded-2xl border border-slate-200/90 bg-slate-100 shadow-xs dark:border-slate-800 dark:bg-slate-800/90">
        <div className="border-b border-outline/40 p-4 sm:p-6 space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 className="text-lg font-bold text-on-surface">Log Aktivitas & Antrean</h2>
              <p className="mt-1 text-sm text-muted">
                Menampilkan {filtered.length} dari {items.length} catatan aktivitas terbaru.
              </p>
            </div>
            <button
              type="button"
              onClick={() => { setLoading(true); load(); }}
              className="inline-flex items-center gap-1.5 rounded-xl border border-outline/50 bg-white px-3 py-2 text-xs font-semibold text-secondary hover:bg-surface-low cursor-pointer dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
            >
              <span className="material-symbol text-base" aria-hidden="true">refresh</span>
              Segarkan
            </button>
          </div>

          {/* Quick Filter Tabs */}
          <div className="flex flex-wrap gap-2 pt-1" role="tablist" aria-label="Filter status aktivitas">
            <button
              type="button"
              role="tab"
              aria-selected={statusTab === 'all'}
              onClick={() => setStatusTab('all')}
              className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition cursor-pointer ${
                statusTab === 'all'
                  ? 'bg-primary text-white shadow-xs'
                  : 'border border-outline/40 bg-surface-low/50 text-secondary hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300'
              }`}
            >
              <span className="material-symbol text-sm" aria-hidden="true">list_alt</span>
              Semua Catatan ({items.length})
            </button>

            <button
              type="button"
              role="tab"
              aria-selected={statusTab === 'jobs'}
              onClick={() => setStatusTab('jobs')}
              className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition cursor-pointer ${
                statusTab === 'jobs'
                  ? 'bg-primary text-white shadow-xs'
                  : 'border border-outline/40 bg-surface-low/50 text-secondary hover:bg-surface-low dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300'
              }`}
            >
              <span className="material-symbol text-sm" aria-hidden="true">dataset</span>
              Log Antrean & Job ({completedJobsCount + failedJobsCount})
            </button>

            <button
              type="button"
              role="tab"
              aria-selected={statusTab === 'failed'}
              onClick={() => setStatusTab('failed')}
              className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition cursor-pointer ${
                statusTab === 'failed'
                  ? 'bg-red-600 text-white shadow-xs'
                  : 'border border-red-200 bg-red-50/60 text-red-700 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300'
              }`}
            >
              <span className="material-symbol text-sm" aria-hidden="true">cancel</span>
              Kendala / Gagal ({failedJobsCount})
            </button>

            <button
              type="button"
              role="tab"
              aria-selected={statusTab === 'completed'}
              onClick={() => setStatusTab('completed')}
              className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition cursor-pointer ${
                statusTab === 'completed'
                  ? 'bg-green-600 text-white shadow-xs'
                  : 'border border-green-200 bg-green-50/60 text-green-700 hover:bg-green-100 dark:border-green-900/60 dark:bg-green-950/30 dark:text-green-300'
              }`}
            >
              <span className="material-symbol text-sm" aria-hidden="true">check_circle</span>
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
            options={[{ value: 'all', label: 'Semua jenis' }, ...types.map(value => ({ value, label: value }))]}
          />
        </div>

        {/* List of Activities */}
        {page.pagedItems.length ? (
          <div className="divide-y divide-outline/30 dark:divide-slate-800">
            {page.pagedItems.map(item => {
              const isJob = item.activity_source === 'job' || Boolean(item.job_id);
              const isFailed = item.item_status === 'failed' || String(item.activity_type).toLowerCase().includes('gagal');
              const isCompleted = item.item_status === 'completed' || String(item.activity_type).toLowerCase().includes('berhasil');
              const isStopped = item.item_status === 'stopped';

              return (
                <article
                  key={item.activity_id}
                  className={`flex gap-3.5 p-4 transition hover:bg-surface-low/50 sm:gap-4 sm:p-5 dark:hover:bg-slate-800/40 ${
                    isFailed ? 'bg-red-50/20 dark:bg-red-950/10' : ''
                  }`}
                >
                  <span
                    className={`grid size-11 shrink-0 place-items-center rounded-2xl sm:size-12 ${
                      isFailed
                        ? 'bg-red-500/15 text-red-600 dark:bg-red-950 dark:text-red-400'
                        : isCompleted
                          ? 'bg-green-500/15 text-green-700 dark:bg-green-950 dark:text-green-400'
                          : isStopped
                            ? 'bg-amber-500/15 text-amber-700 dark:bg-amber-950 dark:text-amber-400'
                            : 'bg-primary/10 text-primary dark:bg-primary/20'
                    }`}
                  >
                    <span className="material-symbol text-2xl" aria-hidden="true">
                      {activityIcon(item.activity_type, item.activity_source, item.item_status)}
                    </span>
                  </span>

                  <div className="min-w-0 flex-1 space-y-2">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                      <div className="flex flex-wrap items-center gap-2">
                        {isJob && (
                          <span className="inline-flex items-center gap-1 rounded-md bg-slate-900 px-2 py-0.5 text-xs font-mono font-bold text-white dark:bg-slate-700">
                            Antrean #{item.job_id}
                          </span>
                        )}

                        {isJob && item.item_label && (
                          <span className="inline-flex items-center gap-1 rounded-md border border-outline/40 bg-surface-low/80 px-2 py-0.5 text-xs font-mono font-semibold text-on-surface dark:bg-slate-800">
                            <span className="text-muted">Item:</span> {item.item_label}
                          </span>
                        )}

                        {isFailed ? (
                          <span className="inline-flex items-center gap-1 rounded-md bg-red-100 px-2.5 py-0.5 text-[11px] font-bold text-red-700 dark:bg-red-950 dark:text-red-300">
                            <span className="material-symbol text-xs" aria-hidden="true">cancel</span>
                            Gagal / Terkendala
                          </span>
                        ) : isCompleted ? (
                          <span className="inline-flex items-center gap-1 rounded-md bg-green-100 px-2.5 py-0.5 text-[11px] font-bold text-green-700 dark:bg-green-950 dark:text-green-300">
                            <span className="material-symbol text-xs" aria-hidden="true">check_circle</span>
                            Berhasil
                          </span>
                        ) : isStopped ? (
                          <span className="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            <span className="material-symbol text-xs" aria-hidden="true">pause_circle</span>
                            Dihentikan
                          </span>
                        ) : (
                          <span className="rounded-full bg-surface-low px-2.5 py-0.5 text-[11px] font-semibold capitalize text-secondary dark:bg-slate-800">
                            {item.activity_type || 'Umum'}
                          </span>
                        )}
                      </div>

                      <p className="flex items-center gap-1 text-xs text-muted">
                        <span className="material-symbol text-sm" aria-hidden="true">schedule</span>
                        {formatDate(item.created_at)}
                      </p>
                    </div>

                    <p className="break-words text-sm font-semibold leading-relaxed text-on-surface">
                      {item.description || 'Aktivitas tercatat'}
                    </p>

                    {/* Detailed message box for job event details / error explanations */}
                    {isJob && item.message && (
                      <div
                        className={`rounded-xl p-3 text-xs leading-relaxed font-mono ${
                          isFailed
                            ? 'border border-red-200 bg-red-50/90 text-red-900 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200'
                            : 'border border-outline/30 bg-surface-low/70 text-secondary dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300'
                        }`}
                      >
                        <div className="flex items-center gap-1 font-bold mb-1">
                          <span className="material-symbol text-sm" aria-hidden="true">
                            {isFailed ? 'report_problem' : 'info'}
                          </span>
                          <span>{isFailed ? 'Keterangan Kendala / Alasan Gagal:' : 'Keterangan Proses:'}</span>
                        </div>
                        <p className="whitespace-pre-wrap break-words">{item.message}</p>
                      </div>
                    )}

                    <div className="flex flex-wrap items-center gap-x-2 text-xs text-muted pt-0.5">
                      {item.actor_name && (
                        <span>
                          Operator: <span className="font-semibold text-secondary dark:text-slate-300">{item.actor_name}</span>{' '}
                          <span className="capitalize">({item.actor_role || (item.activity_source === 'student' ? 'student' : 'akun')})</span>
                        </span>
                      )}
                      {item.employee_npk && (
                        <>
                          <span aria-hidden="true">·</span>
                          <span>NPK: {item.employee_npk}</span>
                        </>
                      )}
                    </div>
                  </div>
                </article>
              );
            })}
          </div>
        ) : (
          <div className="p-10 text-center sm:p-14">
            <span className="material-symbol mx-auto grid size-14 place-items-center rounded-2xl bg-surface-low text-3xl text-muted dark:bg-slate-800">
              {loading ? 'hourglass_top' : 'history'}
            </span>
            <h3 className="mt-4 font-bold text-on-surface">
              {loading
                ? 'Memuat aktivitas…'
                : items.length
                  ? 'Tidak ada catatan yang cocok'
                  : 'Belum ada catatan aktivitas'}
            </h3>
            <p className="mt-1 text-sm text-muted">
              {loading
                ? 'Mohon tunggu sebentar.'
                : 'Catatan aktivitas dan antrean background job akan tampil di sini secara realtime.'}
            </p>
          </div>
        )}

        <Pagination {...page} onPageChange={page.setPage} onPageSizeChange={page.setPageSize} />
      </section>
    </div>
  );
}
