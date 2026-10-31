import { useEffect, useMemo, useRef, useState } from 'react';
import { api, API_BASE } from '../api/client';

const activeStatuses = new Set(['queued', 'running', 'stopping']);
const statusLabels = { queued: 'Menunggu antrean', running: 'Sedang berjalan', stopping: 'Menghentikan antrean…', stopped: 'Dihentikan', completed: 'Selesai', completed_with_errors: 'Selesai dengan kendala', failed: 'Gagal' };
const positionStorageKey = 'dls_jobs_center_position';

function loadPosition() {
  try {
    const value = JSON.parse(localStorage.getItem(positionStorageKey) || 'null');
    return Number.isFinite(value?.left) && Number.isFinite(value?.top)
      ? { left: value.left, top: value.top }
      : null;
  } catch { return null; }
}

function clampPosition(left, top, element) {
  const margin = 12;
  const rect = element?.getBoundingClientRect();
  const width = rect?.width || Math.min(416, window.innerWidth - margin * 2);
  const height = rect?.height || Math.min(240, window.innerHeight - margin * 2);
  return {
    left: Math.max(margin, Math.min(left, window.innerWidth - width - margin)),
    top: Math.max(margin, Math.min(top, window.innerHeight - height - margin)),
  };
}

function mergeJobSnapshot(previous, incoming) {
  if (Number(previous?.job_id) !== Number(incoming?.job_id)) return incoming;
  const previousTime = Date.parse(previous?.updated_at || '');
  const incomingTime = Date.parse(incoming?.updated_at || '');
  if (Number.isFinite(previousTime) && Number.isFinite(incomingTime) && previousTime > incomingTime) return previous;
  return { ...previous, ...incoming };
}

function mergeJobEvents(previous, incoming) {
  const events = new Map();
  [...incoming, ...previous].forEach(event => events.set(String(event.event_id), event));
  return [...events.values()]
    .sort((first, second) => Number(first.event_id) - Number(second.event_id))
    .slice(-40);
}

function labelEvent(event) {
  if (event.item_status === 'completed') return 'Selesai';
  if (event.item_status === 'failed') return 'Gagal';
  if (event.item_status === 'stopped') return 'Dihentikan';
  return 'Diproses';
}

export default function JobsCenter({ role }) {
  const [jobs, setJobs] = useState([]);
  const [jobId, setJobId] = useState(() => {
    try { return Number(localStorage.getItem('dls_active_job_id')) || null; } catch { return null; }
  });
  const [job, setJob] = useState(null);
  const [events, setEvents] = useState([]);
  const [expanded, setExpanded] = useState(true);
  const [hidden, setHidden] = useState(() => {
    try { return localStorage.getItem('dls_jobs_center_hidden') === 'true'; } catch { return false; }
  });
  const [position, setPosition] = useState(loadPosition);
  const [busy, setBusy] = useState(false);
  const [typedCurrent, setTypedCurrent] = useState('');
  const [controlError, setControlError] = useState('');
  const panelRef = useRef(null);
  const restoreRef = useRef(null);
  const dragRef = useRef(null);

  const currentJob = useMemo(() => {
    if (Number(job?.job_id) === Number(jobId)) return job;
    return jobs.find(item => Number(item.job_id) === Number(jobId)) || job;
  }, [jobs, jobId, job]);

  async function refreshJobs() {
    try {
      const nextJobs = (await api('/jobs')).jobs || [];
      setJobs(nextJobs);
      const savedId = Number(jobId || localStorage.getItem('dls_active_job_id'));
      const selected = nextJobs.find(item => activeStatuses.has(item.status))
        || nextJobs.find(item => Number(item.job_id) === savedId) || nextJobs[0];
      if (selected) {
        setJobId(Number(selected.job_id));
        setJob(previous => mergeJobSnapshot(previous, selected));
        localStorage.setItem('dls_active_job_id', String(selected.job_id));
      }
    } catch { /* Login/session changes are handled by the application shell. */ }
  }

  useEffect(() => {
    refreshJobs();
    const timer = setInterval(refreshJobs, 3500);
    return () => clearInterval(timer);
  }, []);

  useEffect(() => {
    if (!jobId) return undefined;
    let mounted = true;
    setEvents([]);
    api(`/jobs/${jobId}/events?after=0`).then(result => {
      if (!mounted) return;
      setJob(previous => mergeJobSnapshot(previous, result.job));
      setEvents(previous => mergeJobEvents(previous, result.events || []));
    }).catch(() => {});
    const stream = new EventSource(`${API_BASE}/jobs/${jobId}/stream`, { withCredentials: true });
    stream.addEventListener('progress', event => {
      const value = JSON.parse(event.data);
      setJob(previous => mergeJobSnapshot(previous, { ...value, job_id: jobId }));
      if (['completed', 'completed_with_errors', 'stopped', 'failed'].includes(value.status)) refreshJobs();
    });
    stream.addEventListener('item', event => {
      const item = JSON.parse(event.data);
      setEvents(previous => mergeJobEvents(previous, [item]));
      if (item.item_status === 'completed' || item.item_status === 'failed') {
        window.dispatchEvent(new CustomEvent('dls_job_item_settled', { detail: { jobId, item } }));
      }
    });
    stream.addEventListener('settled', () => {
      stream.close();
      refreshJobs();
      window.dispatchEvent(new CustomEvent('dls_job_settled', { detail: { jobId } }));
    });
    return () => { mounted = false; stream.close(); };
  }, [jobId]);

  useEffect(() => {
    const text = currentJob?.current_item || '';
    setTypedCurrent('');
    if (!text) return undefined;
    let index = 0;
    const timer = setInterval(() => {
      index += 1;
      setTypedCurrent(text.slice(0, index));
      if (index >= text.length) clearInterval(timer);
    }, 24);
    return () => clearInterval(timer);
  }, [currentJob?.current_item]);

  useEffect(() => {
    try { localStorage.setItem('dls_jobs_center_hidden', String(hidden)); } catch { /* Storage may be unavailable. */ }
  }, [hidden]);

  useEffect(() => {
    try {
      if (position) localStorage.setItem(positionStorageKey, JSON.stringify(position));
      else localStorage.removeItem(positionStorageKey);
    } catch { /* Storage may be unavailable. */ }
  }, [position]);

  const isPositioned = position !== null;
  useEffect(() => {
    if (!isPositioned) return undefined;
    const reposition = () => {
      const element = hidden ? restoreRef.current : panelRef.current;
      setPosition(current => {
        if (!current) return current;
        const next = clampPosition(current.left, current.top, element);
        return next.left === current.left && next.top === current.top ? current : next;
      });
    };
    window.addEventListener('resize', reposition);
    const observer = !hidden && panelRef.current && 'ResizeObserver' in window
      ? new ResizeObserver(reposition)
      : null;
    if (observer) observer.observe(panelRef.current);
    reposition();
    return () => {
      window.removeEventListener('resize', reposition);
      observer?.disconnect();
    };
  }, [hidden, isPositioned]);

  async function control(action) {
    if (!currentJob) return;
    setBusy(true);
    setControlError('');
    try {
      await api(`/jobs/${currentJob.job_id}/${action}`, { method: 'POST' });
      await refreshJobs();
    } catch (error) { setControlError(error.message); }
    finally { setBusy(false); }
  }

  if (!currentJob) return null;
  const percent = currentJob.total_items ? Math.min(100, Math.round((currentJob.processed_items / currentJob.total_items) * 100)) : 0;
  const isActive = activeStatuses.has(currentJob.status);
  const floatingPosition = position ? { left: `${position.left}px`, top: `${position.top}px`, right: 'auto', bottom: 'auto' } : undefined;

  function startDrag(event) {
    const element = hidden ? restoreRef.current : panelRef.current;
    if ((event.pointerType === 'mouse' && event.button !== 0) || !element) return;
    event.preventDefault();
    const rect = element.getBoundingClientRect();
    dragRef.current = { pointerId: event.pointerId, startX: event.clientX, startY: event.clientY, left: rect.left, top: rect.top, element };
    setPosition(clampPosition(rect.left, rect.top, element));
    try {
      event.currentTarget.setPointerCapture?.(event.pointerId);
    } catch {}
  }

  function moveDrag(event) {
    const drag = dragRef.current;
    if (!drag || drag.pointerId !== event.pointerId) return;
    setPosition(clampPosition(drag.left + event.clientX - drag.startX, drag.top + event.clientY - drag.startY, drag.element));
  }

  function endDrag(event) {
    if (dragRef.current?.pointerId !== event.pointerId) return;
    dragRef.current = null;
    try {
      if (event.currentTarget.hasPointerCapture?.(event.pointerId)) event.currentTarget.releasePointerCapture(event.pointerId);
    } catch {}
  }

  function nudgePosition(event) {
    const offsets = { ArrowUp: [0, -16], ArrowDown: [0, 16], ArrowLeft: [-16, 0], ArrowRight: [16, 0] };
    const offset = offsets[event.key];
    const element = hidden ? restoreRef.current : panelRef.current;
    if (!offset || !element) return;
    event.preventDefault();
    const rect = element.getBoundingClientRect();
    const current = position || { left: rect.left, top: rect.top };
    setPosition(clampPosition(current.left + offset[0], current.top + offset[1], element));
  }

  if (hidden) return <div
    ref={restoreRef}
    style={floatingPosition}
    className="fixed bottom-3 right-3 z-40 inline-flex min-h-9 max-w-[calc(100vw-1.5rem)] items-center overflow-hidden rounded-full border border-primary bg-primary text-xs font-semibold text-white shadow-lg"
    aria-label="Antrean tersembunyi"
  >
    <button type="button" data-dls-action-ignore="true" onClick={() => setHidden(false)} className="inline-flex min-h-9 min-w-0 items-center gap-1.5 px-3 py-1.5 text-left text-white hover:bg-red-800" aria-label={`Tampilkan antrean ${currentJob.job_id}, progres ${percent}%`} title="Tampilkan progres antrean">
      <span className="material-symbol text-base text-white" aria-hidden="true">pending_actions</span>
      <span className="max-w-40 truncate">Antrean #{currentJob.job_id} · {percent}%</span>
    </button>
    <button
      type="button"
      data-dls-action-ignore="true"
      onPointerDown={startDrag}
      onPointerMove={moveDrag}
      onPointerUp={endDrag}
      onPointerCancel={endDrag}
      onKeyDown={nudgePosition}
      title="Seret untuk memindahkan; gunakan tombol panah untuk menggeser"
      aria-label="Pindahkan tombol antrean"
      className="inline-flex touch-none cursor-move self-stretch border-l border-white/40 px-2 text-white hover:bg-red-800 items-center"
    ><span className="material-symbol text-sm" aria-hidden="true">drag_indicator</span></button>
  </div>;

  return <aside ref={panelRef} style={floatingPosition} className="fixed bottom-3 right-3 z-40 w-[min(20rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950 text-xs" aria-label="Progress antrean">
    <div className="flex items-center justify-between gap-1.5 border-b border-primary bg-primary px-3 py-2 text-white">
      <button type="button" data-dls-action-ignore="true" onClick={() => setExpanded(value => !value)} className="flex min-w-0 flex-1 items-center gap-1.5 text-left text-white hover:text-white">
        <span className="material-symbol text-base text-white shrink-0" aria-hidden="true">{isActive ? 'pending_actions' : 'task_alt'}</span>
        <span className="min-w-0"><strong className="block truncate text-xs font-bold">Antrean #{currentJob.job_id}</strong><span className="block truncate text-[10px] text-white/90">{statusLabels[currentJob.status] || currentJob.status}</span></span>
      </button>
      <div className="flex shrink-0 items-center gap-0.5" data-dls-action-layout-ignore="true">
        <button
          type="button"
          data-dls-action-ignore="true"
          onPointerDown={startDrag}
          onPointerMove={moveDrag}
          onPointerUp={endDrag}
          onPointerCancel={endDrag}
          onKeyDown={nudgePosition}
          title="Seret untuk memindahkan; gunakan tombol panah untuk menggeser"
          aria-label="Pindahkan modal antrean"
          className="inline-flex touch-none cursor-move rounded-md border border-white/60 bg-white/10 p-1 text-white hover:bg-white hover:text-primary transition"
        ><span className="material-symbol text-xs" aria-hidden="true">drag_indicator</span></button>
        {['stopped', 'failed'].includes(currentJob.status) && currentJob.processed_items < currentJob.total_items && <button type="button" data-dls-action-ignore="true" disabled={busy} onClick={() => control('resume')} title="Lanjutkan antrean" aria-label="Lanjutkan antrean" className="rounded-md border border-white/60 bg-white/10 p-1 text-white hover:bg-white hover:text-primary disabled:opacity-50 transition"><span className="material-symbol text-xs">play_arrow</span></button>}
        {isActive && <button type="button" data-dls-action-ignore="true" disabled={busy || currentJob.status === 'stopping'} onClick={() => control('stop')} title="Hentikan antrean" aria-label="Hentikan antrean" className="rounded-md border border-white/60 bg-white/10 p-1 text-white hover:bg-white hover:text-primary disabled:opacity-50 transition"><span className="material-symbol text-xs">stop</span></button>}
        <button type="button" data-dls-action-ignore="true" onClick={() => setExpanded(value => !value)} title={expanded ? 'Ciutkan progress' : 'Tampilkan progress'} aria-label={expanded ? 'Ciutkan progress' : 'Tampilkan progress'} className="rounded-md border border-white/60 bg-white/10 p-1 text-white hover:bg-white hover:text-primary transition"><span className="material-symbol text-xs">{expanded ? 'expand_more' : 'expand_less'}</span></button>
        <button type="button" data-dls-action-ignore="true" onClick={() => setHidden(true)} title="Sembunyikan antrean" aria-label="Sembunyikan modal antrean" className="rounded-md border border-white/60 bg-white/10 p-1 text-white hover:bg-white hover:text-primary transition"><span className="material-symbol text-xs" aria-hidden="true">visibility_off</span></button>
      </div>
    </div>
    {expanded && <div className="bg-white p-3 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100">
      <div className="mb-1.5 flex justify-between text-[11px] font-semibold text-neutral-700 dark:text-neutral-300"><span>{currentJob.processed_items} dari {currentJob.total_items} diproses</span><span className="font-bold text-primary">{percent}%</span></div>
      <div className="h-2 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-800"><div className="h-full rounded-full bg-primary transition-all duration-300" style={{ width: `${percent}%` }}/></div>
      {currentJob.current_item && <p className="mt-2 truncate text-[11px] font-medium" title={currentJob.current_item}>Sedang diproses: {typedCurrent}{typedCurrent.length < currentJob.current_item.length ? '▍' : ''}</p>}
      <p className="mt-1 text-[10px] text-neutral-500 dark:text-neutral-400">{currentJob.success_count} berhasil · {currentJob.failure_count} gagal<span className="hidden sm:inline"> · item berikutnya tetap di antrean jika dihentikan.</span></p>
      {controlError && <p role="alert" className="mt-1 text-[11px] text-red-700">{controlError}</p>}
      {/* Log event detail: disembunyikan di mobile agar modal tidak besar di layar ponsel */}
      <ol className="hidden sm:block mt-2 max-h-28 space-y-1 overflow-y-auto border-t border-neutral-200 pt-2 text-[11px] dark:border-neutral-800" aria-live="polite">
        {events.filter(event => event.item_no > 0 && event.item_status !== 'processing').slice(-8).map(event => <li key={event.event_id} className="flex items-start justify-between gap-2">
          <span className="min-w-0"><strong className="mr-1 text-neutral-500 dark:text-neutral-400">{event.item_no}.</strong><span className="break-words">{event.item_label}</span>{event.message && <span className="block pl-3 text-[10px] text-neutral-500 dark:text-neutral-400">{event.message}</span>}</span>
          <span className={`shrink-0 font-semibold text-[10px] ${event.item_status === 'failed' ? 'text-primary' : 'text-neutral-600 dark:text-neutral-400'}`}>{labelEvent(event)}</span>
        </li>)}
        {!events.some(event => event.item_no > 0) && <li className="text-[10px] text-neutral-500 dark:text-neutral-400">Menunggu item pertama…</li>}
      </ol>
    </div>}
  </aside>;
}
