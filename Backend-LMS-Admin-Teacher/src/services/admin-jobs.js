const crypto = require('node:crypto');
const { sql, pool } = require('../config/database');
const env = require('../config/env');

const handlers = new Map();
let running = false;
let inFlight = false;
let lastQueueErrorAt = 0;

function key() { return crypto.createHash('sha256').update(env.JWT_SECRET).digest(); }
function encrypt(value) {
  const iv = crypto.randomBytes(12);
  const cipher = crypto.createCipheriv('aes-256-gcm', key(), iv);
  const ciphertext = Buffer.concat([cipher.update(JSON.stringify(value), 'utf8'), cipher.final()]);
  return { ciphertext, iv, tag: cipher.getAuthTag() };
}
function decrypt(ciphertext, iv, tag) {
  const decipher = crypto.createDecipheriv('aes-256-gcm', key(), iv);
  decipher.setAuthTag(tag);
  return JSON.parse(Buffer.concat([decipher.update(ciphertext), decipher.final()]).toString('utf8'));
}

async function appendEvent(jobId, itemNo, label, status, message = '') {
  await pool.request().input('job', sql.BigInt, jobId).input('no', sql.Int, itemNo)
    .input('label', sql.NVarChar(300), String(label).slice(0, 300))
    .input('status', sql.VarChar(20), status).input('message', sql.NVarChar(500), String(message).slice(0, 500))
    .query(`INSERT INTO dbo.lms_admin_job_events(job_id,item_no,item_label,item_status,message)
      VALUES(@job,@no,@label,@status,NULLIF(@message,''))`);
}

async function createJob({ ownerNpk, ownerRole, type, items }) {
  if (!Array.isArray(items) || !items.length || items.length > 5000) throw Object.assign(new Error('Jumlah data job harus antara 1 sampai 5.000.'), { status: 400 });
  if (!handlers.has(type)) throw Object.assign(new Error('Jenis job tidak didukung.'), { status: 400 });
  const protectedPayload = encrypt(items);
  const result = await pool.request().input('type', sql.VarChar(40), type)
    .input('owner', sql.VarChar(50), ownerNpk).input('ciphertext', sql.VarBinary(sql.MAX), protectedPayload.ciphertext)
    .input('iv', sql.VarBinary(12), protectedPayload.iv).input('tag', sql.VarBinary(16), protectedPayload.tag)
    .input('role', sql.VarChar(20), ownerRole).input('total', sql.Int, items.length)
    .query(`INSERT INTO dbo.lms_admin_jobs(job_type,created_by,created_by_role,payload_ciphertext,payload_iv,payload_tag,total_items)
      OUTPUT INSERTED.job_id VALUES(@type,@owner,@role,@ciphertext,@iv,@tag,@total)`);
  const id = result.recordset[0].job_id;
  await appendEvent(id, 0, `Job ${type} masuk antrean (${items.length} item)`, 'processing', 'Menunggu worker.');
  return id;
}

function registerJobHandler(type, handler) { handlers.set(type, handler); }

async function claimJob() {
  const result = await pool.request().query(`;WITH next_job AS (
      SELECT TOP (1) * FROM dbo.lms_admin_jobs WITH (UPDLOCK,READPAST,ROWLOCK)
      WHERE status='queued' ORDER BY job_id
    )
    UPDATE next_job SET status='running',started_at=COALESCE(started_at,SYSUTCDATETIME()),updated_at=SYSUTCDATETIME()
    OUTPUT INSERTED.job_id,INSERTED.job_type,INSERTED.payload_ciphertext,INSERTED.payload_iv,INSERTED.payload_tag,
      INSERTED.created_by,INSERTED.created_by_role,INSERTED.processed_items,INSERTED.success_count,INSERTED.failure_count;`);
  return result.recordset[0] || null;
}

async function isStopping(jobId) {
  const result = await pool.request().input('id', sql.BigInt, jobId)
    .query(`SELECT status FROM dbo.lms_admin_jobs WHERE job_id=@id`);
  return !result.recordset.length || result.recordset[0].status === 'stopping';
}

async function updateJobAfterItem(job, items, label, status, message, successes, failures) {
  const protectedPayload = items.length ? encrypt(items) : null;
  const processed = Number(job.processed_items) + 1;
  await pool.request().input('id', sql.BigInt, job.job_id)
    .input('ciphertext', sql.VarBinary(sql.MAX), protectedPayload?.ciphertext || null)
    .input('iv', sql.VarBinary(12), protectedPayload?.iv || null).input('tag', sql.VarBinary(16), protectedPayload?.tag || null)
    .input('processed', sql.Int, processed).input('success', sql.Int, successes).input('failure', sql.Int, failures)
    .input('current', sql.NVarChar(300), label)
    .query(`UPDATE dbo.lms_admin_jobs SET payload_ciphertext=@ciphertext,payload_iv=@iv,payload_tag=@tag,
      processed_items=@processed,success_count=@success,failure_count=@failure,current_item=@current,
      updated_at=SYSUTCDATETIME() WHERE job_id=@id`);
  job.processed_items = processed;
  job.success_count = successes;
  job.failure_count = failures;
  await appendEvent(job.job_id, processed, label, status, message);
}

async function processJob(job) {
  const items = decrypt(job.payload_ciphertext, job.payload_iv, job.payload_tag);
  const handler = handlers.get(job.job_type);
  let successes = Number(job.success_count);
  let failures = Number(job.failure_count);
  while (items.length) {
    if (!running) {
      await pool.request().input('id', sql.BigInt, job.job_id)
        .query(`UPDATE dbo.lms_admin_jobs SET status='queued',current_item=NULL,updated_at=SYSUTCDATETIME() WHERE job_id=@id`);
      await appendEvent(job.job_id, 0, 'Worker berhenti; antrean disimpan', 'processing', `${items.length} item tersisa akan dilanjutkan saat server aktif.`);
      return;
    }
    if (await isStopping(job.job_id)) {
      await pool.request().input('id', sql.BigInt, job.job_id)
        .query(`UPDATE dbo.lms_admin_jobs SET status='stopped',cancel_requested=0,finished_at=SYSUTCDATETIME(),updated_at=SYSUTCDATETIME() WHERE job_id=@id`);
      await appendEvent(job.job_id, 0, 'Antrean dihentikan oleh pengguna', 'stopped', `${items.length} item berikutnya tetap tersimpan untuk dilanjutkan.`);
      return;
    }
    const item = items.shift();
    const itemNo = Number(job.processed_items) + 1;
    const label = String(item.label || item.npk || item.id || `Item ${itemNo}`);
    await pool.request().input('id', sql.BigInt, job.job_id).input('current', sql.NVarChar(300), label)
      .query(`UPDATE dbo.lms_admin_jobs SET current_item=@current,updated_at=SYSUTCDATETIME() WHERE job_id=@id`);
    await appendEvent(job.job_id, itemNo, label, 'processing', 'Sedang diproses.');
    try {
      const message = await handler(item, { ownerNpk: job.created_by, jobId: job.job_id });
      successes += 1;
      await updateJobAfterItem(job, items, label, 'completed', message || 'Selesai.', successes, failures);
    } catch (error) {
      failures += 1;
      await updateJobAfterItem(job, items, label, 'failed', error.message || 'Operasi gagal.', successes, failures);
    }
  }
  const status = failures ? 'completed_with_errors' : 'completed';
  await pool.request().input('id', sql.BigInt, job.job_id)
    .query(`UPDATE dbo.lms_admin_jobs SET status='${status}',payload_ciphertext=NULL,payload_iv=NULL,payload_tag=NULL,
      current_item=NULL,finished_at=SYSUTCDATETIME(),updated_at=SYSUTCDATETIME() WHERE job_id=@id`);
}

async function drainQueue() {
  if (!running || inFlight) return;
  inFlight = true;
  try {
    const job = await claimJob();
    if (job) {
      try { await processJob(job); }
      catch (error) {
        await pool.request().input('id', sql.BigInt, job.job_id)
          .query(`UPDATE dbo.lms_admin_jobs SET status='failed',finished_at=SYSUTCDATETIME(),updated_at=SYSUTCDATETIME() WHERE job_id=@id`);
        await appendEvent(job.job_id, Number(job.processed_items), 'Job gagal', 'failed', error.code || 'Kesalahan worker.');
        console.error('Admin job failed:', error.code || 'JOB_ERROR');
      }
    }
  } catch (error) {
    if (Date.now() - lastQueueErrorAt > 60000) {
      lastQueueErrorAt = Date.now();
      console.error('Admin job queue unavailable:', error.code || 'DATABASE_ERROR');
    }
  }
  finally { inFlight = false; }
}

async function startAdminJobWorker() {
  // Recover interrupted jobs before allowing this worker to claim any job.
  // Otherwise this UPDATE can race the first claim and put that fresh claim
  // back into the queue while its handler is already running.
  try {
    await pool.request().query(`UPDATE dbo.lms_admin_jobs SET status='queued',updated_at=SYSUTCDATETIME()
      WHERE status='running'`);
  } catch (error) {
    console.error('Admin job recovery failed:', error.code || 'DATABASE_ERROR');
  }
  running = true;
  const timer = setInterval(drainQueue, 750);
  timer.unref?.();
  drainQueue();
  return async () => {
    running = false;
    clearInterval(timer);
    while (inFlight) await new Promise(resolve => setTimeout(resolve, 50));
  };
}

async function stopJob(jobId, ownerNpk, isAdmin) {
  const result = await pool.request().input('id', sql.BigInt, jobId).input('owner', sql.VarChar(50), ownerNpk)
    .input('isAdmin', sql.Bit, isAdmin)
    .query(`UPDATE dbo.lms_admin_jobs SET status=CASE WHEN status='queued' THEN 'stopped' ELSE 'stopping' END,
      cancel_requested=1,updated_at=SYSUTCDATETIME(),finished_at=CASE WHEN status='queued' THEN SYSUTCDATETIME() ELSE finished_at END
      OUTPUT INSERTED.job_id,INSERTED.status WHERE job_id=@id AND (@isAdmin=1 OR created_by=@owner)
        AND status IN ('queued','running')`);
  if (!result.recordset.length) return false;
  if (result.recordset[0].status === 'stopped') await appendEvent(jobId, 0, 'Antrean dihentikan oleh pengguna', 'stopped', 'Item berikutnya tetap tersimpan untuk dilanjutkan.');
  return true;
}

async function resumeJob(jobId, ownerNpk, isAdmin) {
  const result = await pool.request().input('id', sql.BigInt, jobId).input('owner', sql.VarChar(50), ownerNpk)
    .input('isAdmin', sql.Bit, isAdmin)
      .query(`UPDATE dbo.lms_admin_jobs SET status='queued',cancel_requested=0,finished_at=NULL,updated_at=SYSUTCDATETIME()
      OUTPUT INSERTED.job_id WHERE job_id=@id AND (@isAdmin=1 OR created_by=@owner) AND status IN ('stopped','failed') AND payload_ciphertext IS NOT NULL`);
  if (!result.recordset.length) return false;
  await appendEvent(jobId, 0, 'Antrean dilanjutkan', 'processing', 'Worker akan meneruskan item yang tersisa.');
  drainQueue();
  return true;
}

module.exports = { createJob, registerJobHandler, startAdminJobWorker, stopJob, resumeJob };
