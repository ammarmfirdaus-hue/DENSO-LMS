const express = require('express');
const { z } = require('zod');
const { sql, pool } = require('../config/database');
const env = require('../config/env');
const { authenticate, requirePermission } = require('../middleware/auth');
const { getQuizStore } = require('../services/quiz-store');
const { paginateCollection } = require('../middleware/pagination');

const router = express.Router();
router.use(authenticate);

const courseCodeSchema = z.string().trim().min(2).max(30).regex(/^[A-Za-z0-9._-]+$/);
const questionSchema = z.object({
  text: z.string().trim().min(2).max(2000),
  options: z.array(z.string().trim().min(1).max(500)).min(2).max(6),
  correctOption: z.number().int().min(0).max(5)
}).strict().refine(question => question.correctOption < question.options.length, {
  message: 'Pilih jawaban benar dari opsi yang tersedia.'
});
const quizSchema = z.object({
  title: z.string().trim().min(2).max(200),
  description: z.string().trim().max(2000).default(''),
  passingScore: z.coerce.number().min(0).max(100).default(75),
  status: z.enum(['draft', 'published']).default('draft'),
  questions: z.array(questionSchema).min(1).max(100)
}).strict();

function trustedWriteOrigin(req, res, next) {
  const origin = req.get('origin');
  const allowed = [env.FRONTEND_ORIGIN].map(value => {
    try { return new URL(value).origin; } catch { return ''; }
  });
  if (!origin || !allowed.includes(origin)) return res.status(403).json({ error: { code: 'UNTRUSTED_ORIGIN', message: 'Permintaan pengelolaan quiz ditolak.' } });
  next();
}

async function accessibleCourse(courseCode, user) {
  const result = await pool.request()
    .input('code', sql.VarChar(30), courseCode)
    .input('npk', sql.VarChar(50), user.sub)
    .input('isAdmin', sql.Bit, user.role !== 'teacher')
    .query(`SELECT course_id,course_code,title FROM dbo.courses
      WHERE course_code=@code AND status='active'
        AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)`);
  return result.recordset[0] || null;
}

router.get('/', requirePermission('quizzes.view'), async (req, res, next) => {
  try {
    const result = await pool.request()
      .input('npk', sql.VarChar(50), req.user.sub)
      .input('isAdmin', sql.Bit, req.user.role !== 'teacher')
      .query(`SELECT course_code,title FROM dbo.courses
        WHERE status='active' AND (@isAdmin=1 OR created_by=@npk OR assigned_trainer_npk=@npk)
        ORDER BY title`);
    const courses = result.recordset;
    if (!courses.length) return res.json({ courses: [] });
    const codes = courses.map(course => course.course_code);
    const placeholders = codes.map(() => '?').join(',');
    const [quizzes] = await getQuizStore().execute(`SELECT c.course_code,q.quiz_id,q.title AS quiz_title,
        q.description,q.passing_score,q.status,q.updated_at,
        COUNT(DISTINCT question.question_id) AS question_count,
        COUNT(DISTINCT attempt.attempt_id) AS attempt_count
      FROM courses c LEFT JOIN course_quizzes q ON q.course_id=c.course_id
      LEFT JOIN course_quiz_questions question ON question.quiz_id=q.quiz_id
      LEFT JOIN course_quiz_attempts attempt ON attempt.quiz_id=q.quiz_id
      WHERE c.course_code IN (${placeholders})
      GROUP BY c.course_code,q.quiz_id,q.title,q.description,q.passing_score,q.status,q.updated_at`, codes);
    const quizByCode = new Map(quizzes.map(quiz => [quiz.course_code, {
      quiz_id: quiz.quiz_id || null,
      title: quiz.quiz_title || '',
      description: quiz.description || '',
      passing_score: quiz.passing_score === null ? 75 : Number(quiz.passing_score),
      status: quiz.status || 'missing',
      updated_at: quiz.updated_at || null,
      question_count: Number(quiz.question_count || 0),
      attempt_count: Number(quiz.attempt_count || 0)
    }]));
    const mapped = courses.map(course => ({ ...course, quiz: quizByCode.get(course.course_code) || null }));
    const page = paginateCollection(req, mapped);
    res.json({ courses: page.data, pagination: page.pagination });
  } catch (error) { next(error); }
});

router.get('/:courseCode', requirePermission('quizzes.view'), async (req, res, next) => {
  const parsedCode = courseCodeSchema.safeParse(req.params.courseCode);
  if (!parsedCode.success) return res.status(400).json({ error: { code: 'INVALID_COURSE', message: 'Kode course tidak valid.' } });
  try {
    const course = await accessibleCourse(parsedCode.data, req.user);
    if (!course) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan atau di luar akses Anda.' } });
    const [quizRows] = await getQuizStore().execute(`SELECT q.quiz_id,q.title,q.description,q.passing_score,q.status,q.updated_at
      FROM course_quizzes q JOIN courses c ON c.course_id=q.course_id WHERE c.course_code=? LIMIT 1`, [course.course_code]);
    if (!quizRows.length) return res.json({ course, quiz: null });
    const quiz = quizRows[0];
    const [questions] = await getQuizStore().execute(`SELECT question_id,question_text AS text,options,correct_option,sort_order
      FROM course_quiz_questions WHERE quiz_id=? ORDER BY sort_order,question_id`, [quiz.quiz_id]);
    res.json({ course, quiz: { ...quiz, questions: questions.map(question => ({
      ...question,
      options: typeof question.options === 'string' ? JSON.parse(question.options) : question.options,
      correct_option: Number(question.correct_option)
    })) } });
  } catch (error) { next(error); }
});

async function saveQuiz(req, res, next) {
  const parsedCode = courseCodeSchema.safeParse(req.params.courseCode);
  const parsedQuiz = quizSchema.safeParse(req.body);
  if (!parsedCode.success || !parsedQuiz.success) {
    return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'Isi judul, status, pertanyaan, dan minimal dua opsi untuk setiap pertanyaan.' } });
  }
  try {
    const course = await accessibleCourse(parsedCode.data, req.user);
    if (!course) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan atau di luar akses Anda.' } });
    const store = getQuizStore();
    const [courseRows] = await store.execute('SELECT course_id FROM courses WHERE course_code=? AND status=\'active\' LIMIT 1', [course.course_code]);
    if (!courseRows.length) return res.status(409).json({ error: { code: 'COURSE_NOT_SYNCED', message: 'Course belum tersedia di database Student. Sinkronkan course lalu coba lagi.' } });

    const connection = await store.getConnection();
    try {
      await connection.beginTransaction();
      const [existingRows] = await connection.execute('SELECT quiz_id FROM course_quizzes WHERE course_id=? FOR UPDATE', [courseRows[0].course_id]);
      if (req.method === 'POST' && existingRows.length) {
        await connection.rollback();
        return res.status(409).json({ error: { code: 'QUIZ_EXISTS', message: 'Quiz course ini sudah ada. Gunakan aksi edit.' } });
      }
      if (req.method === 'PUT' && !existingRows.length) {
        await connection.rollback();
        return res.status(404).json({ error: { code: 'QUIZ_NOT_FOUND', message: 'Quiz belum dibuat.' } });
      }
      let quizId;
      if (existingRows.length) {
        quizId = existingRows[0].quiz_id;
        await connection.execute(`UPDATE course_quizzes SET title=?,description=?,passing_score=?,status=?,updated_by=? WHERE quiz_id=?`,
          [parsedQuiz.data.title, parsedQuiz.data.description || null, parsedQuiz.data.passingScore, parsedQuiz.data.status, req.user.sub, quizId]);
        await connection.execute('DELETE FROM course_quiz_questions WHERE quiz_id=?', [quizId]);
      } else {
        const [created] = await connection.execute(`INSERT INTO course_quizzes(course_id,title,description,passing_score,status,created_by,updated_by)
          VALUES(?,?,?,?,?,?,?)`, [courseRows[0].course_id, parsedQuiz.data.title, parsedQuiz.data.description || null,
          parsedQuiz.data.passingScore, parsedQuiz.data.status, req.user.sub, req.user.sub]);
        quizId = created.insertId;
      }
      for (const [index, question] of parsedQuiz.data.questions.entries()) {
        await connection.execute(`INSERT INTO course_quiz_questions(quiz_id,question_text,options,correct_option,sort_order)
          VALUES(?,?,?,?,?)`, [quizId, question.text, JSON.stringify(question.options), question.correctOption, index + 1]);
      }
      await connection.commit();
      res.status(existingRows.length ? 200 : 201).json({ saved: true, quizId, status: parsedQuiz.data.status });
    } catch (error) {
      await connection.rollback().catch(() => {});
      throw error;
    } finally { connection.release(); }
  } catch (error) { next(error); }
}

router.post('/:courseCode', requirePermission('quizzes.manage'), trustedWriteOrigin, saveQuiz);
router.put('/:courseCode', requirePermission('quizzes.manage'), trustedWriteOrigin, saveQuiz);

router.delete('/:courseCode', requirePermission('quizzes.manage'), trustedWriteOrigin, async (req, res, next) => {
  const parsedCode = courseCodeSchema.safeParse(req.params.courseCode);
  if (!parsedCode.success) return res.status(400).json({ error: { code: 'INVALID_COURSE', message: 'Kode course tidak valid.' } });
  try {
    const course = await accessibleCourse(parsedCode.data, req.user);
    if (!course) return res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Course tidak ditemukan atau di luar akses Anda.' } });
    const store = getQuizStore();
    const [rows] = await store.execute(`SELECT q.quiz_id,COUNT(a.attempt_id) AS attempt_count
      FROM course_quizzes q JOIN courses c ON c.course_id=q.course_id
      LEFT JOIN course_quiz_attempts a ON a.quiz_id=q.quiz_id
      WHERE c.course_code=? GROUP BY q.quiz_id`, [course.course_code]);
    if (!rows.length) return res.status(404).json({ error: { code: 'QUIZ_NOT_FOUND', message: 'Quiz tidak ditemukan.' } });
    if (Number(rows[0].attempt_count) > 0) {
      await store.execute('UPDATE course_quizzes SET status=\'inactive\',updated_by=? WHERE quiz_id=?', [req.user.sub, rows[0].quiz_id]);
      return res.json({ deleted: false, archived: true, message: 'Quiz dinonaktifkan. Nilai dan riwayat student tetap tersimpan.' });
    }
    await store.execute('DELETE FROM course_quizzes WHERE quiz_id=?', [rows[0].quiz_id]);
    res.json({ deleted: true, archived: false });
  } catch (error) { next(error); }
});

module.exports = router;
