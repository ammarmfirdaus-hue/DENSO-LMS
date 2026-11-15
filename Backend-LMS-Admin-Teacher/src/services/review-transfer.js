const crypto = require('crypto');
const { sql, pool: defaultPool } = require('../config/database');
const { sendReviewInvitation } = require('./review-mail');
const env = require('../config/env');

async function transferReviewerTasks({ sourceNpk, targetReviewerNpk, tx = null, pool = defaultPool }) {
  if (!sourceNpk || !targetReviewerNpk) {
    const error = new Error('Reviewer asal dan reviewer tujuan harus ditentukan.');
    error.status = 400;
    error.code = 'INVALID_INPUT';
    throw error;
  }
  if (sourceNpk === targetReviewerNpk) {
    const error = new Error('Reviewer tujuan harus berbeda dari reviewer asal.');
    error.status = 400;
    error.code = 'SAME_REVIEWER';
    throw error;
  }

  const ownsTransaction = !tx;
  const transaction = tx || new sql.Transaction(pool);
  if (ownsTransaction) {
    await transaction.begin(sql.ISOLATION_LEVEL.SERIALIZABLE);
  }

  const rawToken = crypto.randomBytes(32).toString('base64url');
  const tokenHash = crypto.createHash('sha256').update(rawToken).digest('hex');
  const expiresAt = new Date(Date.now() + 48 * 60 * 60 * 1000);
  const revokedAt = new Date();

  try {
    const sourceReq = new sql.Request(transaction).input('npk', sql.VarChar(50), sourceNpk);
    const source = await sourceReq.query(`
      SELECT employee_npk, fullname FROM dbo.employee_accounts WITH (UPDLOCK, HOLDLOCK)
      WHERE employee_npk = @npk AND role = 'reviewer'
    `);
    if (!source.recordset[0]) {
      const error = new Error('Reviewer asal tidak ditemukan.');
      error.status = 404;
      error.code = 'REVIEWER_NOT_FOUND';
      throw error;
    }

    const targetReq = new sql.Request(transaction).input('npk', sql.VarChar(50), targetReviewerNpk);
    const target = await targetReq.query(`
      SELECT employee_npk, fullname, email FROM dbo.employee_accounts WITH (UPDLOCK, HOLDLOCK)
      WHERE employee_npk = @npk AND role = 'reviewer' AND status = 'active'
    `);
    if (!target.recordset[0]) {
      const error = new Error('Reviewer tujuan tidak ditemukan atau tidak aktif.');
      error.status = 409;
      error.code = 'TARGET_REVIEWER_NOT_READY';
      throw error;
    }

    const tasksReq = new sql.Request(transaction).input('source', sql.VarChar(50), sourceNpk);
    const tasks = await tasksReq.query(`
      SELECT ms.submission_id, ms.title, c.title AS course_title
      FROM dbo.material_submissions ms WITH (UPDLOCK, HOLDLOCK)
      JOIN dbo.course_sessions s ON s.session_id = ms.session_id
      JOIN dbo.courses c ON c.course_id = s.course_id
      WHERE ms.reviewer_npk = @source AND ms.status = 'pending_review'
      ORDER BY ms.submission_id
    `);

    if (!tasks.recordset.length) {
      if (ownsTransaction) await transaction.commit();
      return { transferred: 0, message: 'Tidak ada tugas review aktif yang perlu dialihkan.' };
    }

    const firstTask = tasks.recordset[0];
    await new sql.Request(transaction)
      .input('source', sql.VarChar(50), sourceNpk)
      .input('target', sql.VarChar(50), target.recordset[0].employee_npk)
      .input('revokedAt', sql.DateTime2, revokedAt)
      .query(`
        UPDATE rl SET revoked_at = @revokedAt
        FROM dbo.material_review_links rl
        JOIN dbo.material_submissions ms ON ms.submission_id = rl.submission_id
        WHERE ms.reviewer_npk = @source AND ms.status = 'pending_review' AND rl.revoked_at IS NULL;

        UPDATE dbo.material_submissions SET reviewer_npk = @target, updated_at = SYSUTCDATETIME()
        WHERE reviewer_npk = @source AND status = 'pending_review';
      `);

    await new sql.Request(transaction)
      .input('submissionId', sql.BigInt, firstTask.submission_id)
      .input('target', sql.VarChar(50), target.recordset[0].employee_npk)
      .input('hash', sql.Char(64), tokenHash)
      .input('expires', sql.DateTime2, expiresAt)
      .query(`
        INSERT INTO dbo.material_review_links (submission_id, reviewer_npk, token_hash, expires_at)
        VALUES (@submissionId, @target, @hash, @expires)
      `);

    if (ownsTransaction) {
      await transaction.commit();
    }

    const reviewUrl = `${env.REVIEW_PORTAL_URL.replace(/\/$/, '')}/review/${rawToken}`;
    let emailSent = false;
    try {
      await sendReviewInvitation({
        to: target.recordset[0].email,
        reviewerName: target.recordset[0].fullname,
        materialTitle: `${tasks.recordset.length} tugas review yang dialihkan`,
        courseTitle: firstTask.course_title,
        reviewUrl,
        expiresAt
      });
      emailSent = true;
    } catch (mailErr) {
      console.error('Transferred reviewer invitation email failed:', mailErr.message);
    }

    return {
      transferred: tasks.recordset.length,
      emailSent,
      expiresAt,
      target: target.recordset[0],
      source: source.recordset[0],
      message: `${tasks.recordset.length} tugas review berhasil dialihkan ke ${target.recordset[0].fullname}.`
    };
  } catch (err) {
    if (ownsTransaction) {
      await transaction.rollback().catch(() => {});
    }
    throw err;
  }
}

module.exports = { transferReviewerTasks };

