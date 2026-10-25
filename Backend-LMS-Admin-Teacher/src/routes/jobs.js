const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const { authenticate, allowRoles } = require('../middleware/auth');
const { createJob, stopJob, resumeJob } = require('../services/admin-jobs');
const { listStudentAccounts } = require('../services/student-auth');

const router = express.Router();
router.use(authenticate, allowRoles('admin', 'teacher'));

function accessClause(req) { return req.user.role === 'admin' ? '' : ' AND created_by=@owner'; }
function addOwner(request, req) {
  if (req.user.role !== 'admin') request.input('owner', sql.VarChar(50), req.user.sub);
  return request;
}

router.get('/', async (req, res, next) => {
  try {
    const request = addOwner(pool.request(), req);
    const result = await request.query(`SELECT TOP (30) job_id,job_type,status,created_by,created_by_role,total_items,processed_items,
      success_count,failure_count,current_item,created_at,updated_at,finished_at FROM dbo.lms_admin_jobs
      WHERE 1=1${accessClause(req)} ORDER BY job_id DESC`);
    res.json({ jobs: result.recordset });
  } catch (error) { next(error); }
});

const inputSchema = z.object({
  items: z.array(z.object({
    action: z.enum(['upsert', 'delete', 'deactivate', 'email']),
    npk: z.string().trim().min(1).max(50),
    label: z.string().trim().min(1).max(300).optional(),
    fullname: z.string().trim().min(2).max(150).optional(),
    email: z.string().trim().email().max(254).optional(),
    role: z.enum(['student', 'admin', 'teacher', 'reviewer']).optional(),
    status: z.enum(['active', 'inactive']).optional(),
    password: z.string().max(200).optional().default(''),
    sendEmail: z.boolean().optional().default(false)
  }).strict()).min(1).max(5000)
}).strict();

router.post('/users', async (req, res, next) => {
  const parsed = inputSchema.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_JOB', message: 'Data job pengguna tidak valid.' } });
  try {
    const items = parsed.data.items;
    if (req.user.role === 'teacher') {
      const students = new Set((await listStudentAccounts()).map(user => String(user.employee_npk).toLowerCase()));
      for (const item of items) {
        if (!students.has(item.npk.toLowerCase()) || !['upsert', 'delete', 'deactivate'].includes(item.action) || item.role && item.role !== 'student' || item.sendEmail) {
          return res.status(403).json({ error: { code: 'TEACHER_USER_SCOPE', message: 'Teacher hanya dapat mengubah atau menghapus akun Student yang sudah ada.' } });
        }
      }
    }
    for (const item of items) {
      if (item.action === 'upsert' && (!item.fullname || !item.email || !item.role || !item.status)) {
        return res.status(400).json({ error: { code: 'INVALID_USER_ITEM', message: 'Nama, email, role, dan status wajib untuk perubahan akun.' } });
      }
      if (['delete', 'deactivate'].includes(item.action) && !item.role) {
        return res.status(400).json({ error: { code: 'INVALID_USER_ITEM', message: 'Role akun wajib disertakan saat menghapus atau menonaktifkan.' } });
      }
      if (item.action === 'upsert' && item.sendEmail && item.status !== 'active') {
        return res.status(400).json({ error: { code: 'EMAIL_REQUIRES_ACTIVE_ACCOUNT', message: 'Email data akun hanya dapat dikirim untuk akun aktif.' } });
      }
      if (item.action === 'email' && (!item.fullname || !item.email || !item.role || item.status !== 'active')) {
        return res.status(400).json({ error: { code: 'EMAIL_DATA_REQUIRED', message: 'Email akun memerlukan data penerima dan akun aktif.' } });
      }
      if (item.action === 'email' && (!item.password || item.password.length < 12)) {
        return res.status(400).json({ error: { code: 'EMAIL_PASSWORD_REQUIRED', message: 'Isi kata sandi baru minimal 12 karakter agar email memuat kata sandi yang dapat langsung digunakan.' } });
      }
      if (item.action === 'upsert' && item.sendEmail && (!item.password || item.password.length < 12)) {
        return res.status(400).json({ error: { code: 'EMAIL_PASSWORD_REQUIRED', message: 'Isi kata sandi baru minimal 12 karakter agar email memuat kata sandi yang dapat langsung digunakan.' } });
      }
    }
    const jobId = await createJob({ ownerNpk: req.user.sub, ownerRole: req.user.role, type: 'users', items });
    res.status(202).json({ jobId, status: 'queued', total: items.length });
  } catch (error) { next(error); }
});

router.get('/:id/events', async (req, res, next) => {
  const jobId = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!jobId.success) return res.status(400).json({ error: { code: 'INVALID_JOB_ID', message: 'ID job tidak valid.' } });
  try {
    const owner = addOwner(pool.request().input('id', sql.BigInt, jobId.data), req);
    const job = await owner.query(`SELECT job_id,status,total_items,processed_items,success_count,failure_count,current_item,updated_at
      FROM dbo.lms_admin_jobs WHERE job_id=@id${accessClause(req)}`);
    if (!job.recordset.length) return res.status(404).json({ error: { code: 'JOB_NOT_FOUND', message: 'Job tidak ditemukan.' } });
    const after = Math.max(0, Number(req.query.after || req.get('Last-Event-ID') || 0));
    const events = await pool.request().input('id', sql.BigInt, jobId.data).input('after', sql.BigInt, after)
      .query(`SELECT event_id,item_no,item_label,item_status,message,created_at FROM dbo.lms_admin_job_events
        WHERE job_id=@id AND event_id>@after ORDER BY event_id`);
    res.json({ job: job.recordset[0], events: events.recordset });
  } catch (error) { next(error); }
});

router.get('/:id/stream', async (req, res, next) => {
  const jobId = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!jobId.success) return res.status(400).end();
  try {
    const request = addOwner(pool.request().input('id', sql.BigInt, jobId.data), req);
    const found = await request.query(`SELECT job_id FROM dbo.lms_admin_jobs WHERE job_id=@id${accessClause(req)}`);
    if (!found.recordset.length) return res.status(404).end();
    res.set({ 'Content-Type': 'text/event-stream', 'Cache-Control': 'no-cache, no-transform', Connection: 'keep-alive', 'X-Accel-Buffering': 'no' });
    res.flushHeaders?.();
    res.write('retry: 1500\n\n');
    let after = Math.max(0, Number(req.get('Last-Event-ID') || 0));
    let polling = false;
    let closed = false;
    let timer;
    const cleanup = () => {
      closed = true;
      if (timer) clearInterval(timer);
    };
    const poll = async () => {
      if (polling || closed || res.writableEnded) return;
      polling = true;
      try {
        const status = await pool.request().input('id', sql.BigInt, jobId.data)
          .query(`SELECT status,total_items,processed_items,success_count,failure_count,current_item,updated_at FROM dbo.lms_admin_jobs WHERE job_id=@id`);
        const events = await pool.request().input('id', sql.BigInt, jobId.data).input('after', sql.BigInt, after)
          .query(`SELECT event_id,item_no,item_label,item_status,message,created_at FROM dbo.lms_admin_job_events WHERE job_id=@id AND event_id>@after ORDER BY event_id`);
        if (!status.recordset.length) { res.write('event: gone\ndata: {}\n\n'); res.end(); cleanup(); return; }
        res.write(`event: progress\ndata: ${JSON.stringify(status.recordset[0])}\n\n`);
        for (const event of events.recordset) {
          after = Number(event.event_id);
          res.write(`id: ${after}\nevent: item\ndata: ${JSON.stringify(event)}\n\n`);
        }
        if (['stopped', 'completed', 'completed_with_errors', 'failed'].includes(status.recordset[0].status)) {
          res.write('event: settled\ndata: {}\n\n'); res.end(); cleanup();
        }
      } catch {
        if (!closed && !res.writableEnded && !res.destroyed) res.write(': keep-alive\n\n');
      }
      finally { polling = false; }
    };
    res.on('close', cleanup);
    res.on('error', cleanup);
    req.on('aborted', cleanup);
    timer = setInterval(poll, 1000);
    poll();
  } catch (error) { next(error); }
});

router.post('/:id/stop', async (req, res, next) => {
  const jobId = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!jobId.success) return res.status(400).json({ error: { code: 'INVALID_JOB_ID', message: 'ID job tidak valid.' } });
  try {
    const stopped = await stopJob(jobId.data, req.user.sub, req.user.role === 'admin');
    if (!stopped) return res.status(409).json({ error: { code: 'JOB_NOT_STOPPABLE', message: 'Job tidak ditemukan atau sudah selesai.' } });
    res.json({ stopped: true });
  } catch (error) { next(error); }
});

router.post('/:id/resume', async (req, res, next) => {
  const jobId = z.coerce.number().int().positive().safeParse(req.params.id);
  if (!jobId.success) return res.status(400).json({ error: { code: 'INVALID_JOB_ID', message: 'ID job tidak valid.' } });
  try {
    const resumed = await resumeJob(jobId.data, req.user.sub, req.user.role === 'admin');
    if (!resumed) return res.status(409).json({ error: { code: 'JOB_NOT_RESUMABLE', message: 'Job tidak ditemukan atau tidak memiliki item tersisa.' } });
    res.json({ resumed: true });
  } catch (error) { next(error); }
});

module.exports = router;
