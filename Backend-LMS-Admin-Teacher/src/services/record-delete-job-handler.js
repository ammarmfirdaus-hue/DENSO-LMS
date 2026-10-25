const fs = require('node:fs');
const path = require('node:path');
const { sql, pool } = require('../config/database');
const { getQuizStore } = require('./quiz-store');
const { syncStudentGradeNow, deleteStudentMaterial } = require('./student-sync');
const { uploadDirectory } = require('../middleware/material-upload');

function removeUploadedFile(name) {
  if (!name || path.basename(name) !== name) return;
  const resolved = path.resolve(uploadDirectory, name);
  if (resolved.startsWith(`${uploadDirectory}${path.sep}`)) fs.promises.unlink(resolved).catch(() => {});
}

async function deleteMaterial(item, ownerNpk) {
  const id = Number(item.id);
  const published = item.kind === 'published_material';
  const lookup = await pool.request().input('id', sql.BigInt, id).query(published
    ? `SELECT m.material_id,m.title,m.file_path,ms.submission_id,ms.file_path AS sub_file_path
       FROM dbo.course_materials m
       LEFT JOIN dbo.material_submissions ms ON ms.published_material_id=m.material_id AND ms.status='published'
       WHERE m.material_id=@id`
    : `SELECT submission_id,title,status,file_path,published_material_id FROM dbo.material_submissions WHERE submission_id=@id`);
  const row = lookup.recordset[0];
  if (!row) throw new Error('Materi tidak ditemukan atau sudah dihapus.');

  const tx = new sql.Transaction(pool);
  await tx.begin();
  try {
    if (published) {
      await new sql.Request(tx).input('id', sql.BigInt, id)
        .query('DELETE FROM dbo.material_submissions WHERE published_material_id=@id');
      await new sql.Request(tx).input('id', sql.BigInt, id)
        .query('DELETE FROM dbo.course_materials WHERE material_id=@id');
    } else {
      if (row.published_material_id) await new sql.Request(tx).input('id', sql.BigInt, row.published_material_id)
        .query('DELETE FROM dbo.course_materials WHERE material_id=@id');
      await new sql.Request(tx).input('id', sql.BigInt, id)
        .query('DELETE FROM dbo.material_submissions WHERE submission_id=@id');
    }
    await tx.commit();
  } catch (error) {
    await tx.rollback().catch(() => {});
    throw error;
  }

  if (published) removeUploadedFile(row.file_path || row.sub_file_path);
  else removeUploadedFile(row.file_path);
  const publishedId = published ? id : row.published_material_id;
  if (publishedId) await deleteStudentMaterial(publishedId);
  await pool.request().input('npk', sql.VarChar(50), ownerNpk)
    .input('description', sql.NVarChar(500), `Materi "${row.title}" dihapus melalui antrean admin.`)
    .query(`INSERT INTO dbo.activity_logs(employee_npk,activity_type,description)
      VALUES(@npk,'material_deleted',@description)`).catch(() => {});
  return 'Materi dihapus dan perubahan student disinkronkan.';
}

function registerRecordDeleteJobHandler(register) {
  register('records_delete', async (item, context) => {
    if (context.ownerRole !== 'admin') throw new Error('Bulk delete data hanya dapat dijalankan oleh Admin.');
    if (item.kind === 'grade') {
      const result = await pool.request().input('npk', sql.VarChar(50), item.npk)
        .input('code', sql.VarChar(30), item.courseCode)
        .query(`UPDATE dbo.student_progress_snapshots SET score=NULL,grade_feedback=NULL,graded_by=NULL,
          graded_at=NULL,synced_at=SYSUTCDATETIME() WHERE student_npk=@npk AND course_code=@code;
          SELECT @@ROWCOUNT AS affected`);
      if (!result.recordset[0]?.affected) throw new Error('Nilai tidak ditemukan atau sudah dihapus.');
      await syncStudentGradeNow(item.npk, item.courseCode);
      return 'Nilai dihapus; progres course tetap tersimpan.';
    }
    if (item.kind === 'quiz') {
      const store = getQuizStore();
      const [rows] = await store.execute(`SELECT q.quiz_id,COUNT(a.attempt_id) AS attempt_count
        FROM course_quizzes q JOIN courses c ON c.course_id=q.course_id
        LEFT JOIN course_quiz_attempts a ON a.quiz_id=q.quiz_id
        WHERE c.course_code=? GROUP BY q.quiz_id`, [item.courseCode]);
      if (!rows.length) throw new Error('Quiz tidak ditemukan atau sudah dihapus.');
      if (Number(rows[0].attempt_count) > 0) {
        await store.execute('UPDATE course_quizzes SET status=\'inactive\',updated_by=? WHERE quiz_id=?', [context.ownerNpk, rows[0].quiz_id]);
        return 'Quiz dinonaktifkan karena sudah memiliki percobaan student; nilai dan riwayat tetap tersimpan.';
      }
      await store.execute('DELETE FROM course_quizzes WHERE quiz_id=?', [rows[0].quiz_id]);
      return 'Quiz dihapus.';
    }
    if (item.kind === 'material_submission' || item.kind === 'published_material') {
      return deleteMaterial(item, context.ownerNpk);
    }
    throw new Error('Jenis data tidak didukung.');
  });
}

module.exports = { registerRecordDeleteJobHandler };
