const express = require('express');
const { sql, pool } = require('../config/database');
const { authenticate, requirePermission } = require('../middleware/auth');
const { paginateCollection } = require('../middleware/pagination');
const { getActivityFeed } = require('../services/activity-feed');
const { getDashboardAnalytics } = require('../services/dashboard-analytics');

const router = express.Router();
router.use(authenticate);

router.get('/analytics', requirePermission('dashboard.view'), async (req, res, next) => {
  try {
    const scopeUser = req.user.role === 'teacher' ? req.user : { ...req.user, role: 'admin' };
    const analytics = await getDashboardAnalytics({ user: scopeUser, days: req.query.days });
    res.set('Cache-Control', 'private, no-store');
    res.json(analytics);
  } catch (error) { next(error); }
});

router.get('/summary', requirePermission('dashboard.view'), async (req, res, next) => {
  try {
    const isWideReader = req.user.role !== 'teacher';
    const result = await pool.request().input('isAdmin', sql.Bit, isWideReader).input('npk', sql.VarChar(50), req.user.sub)
      .query(`SELECT (SELECT COUNT_BIG(*) FROM dbo.courses c WHERE c.status = 'active' AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)) AS active_courses,
                     CASE WHEN @isAdmin=1 THEN (SELECT COUNT_BIG(*) FROM dbo.employee_accounts WHERE status='active')
                       ELSE (SELECT COUNT_BIG(DISTINCT p.student_npk) FROM dbo.student_progress_snapshots p
                         JOIN dbo.courses c ON c.course_code=p.course_code WHERE c.created_by=@npk OR c.assigned_trainer_npk=@npk) END AS active_employees,
                     (SELECT COUNT_BIG(*) FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id=s.course_id
                       WHERE s.status='active' AND (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)) AS active_sessions`);
    res.json({ summary: result.recordset[0] });
  } catch (error) { next(error); }
});

router.get('/courses', requirePermission('courses.view'), async (_req, res, next) => {
  try {
    const isWideReader = _req.user.role !== 'teacher';
    const request = pool.request().input('isAdmin', sql.Bit, isWideReader).input('npk', sql.VarChar(50), _req.user.sub);
    const result = await request.query(`SELECT c.course_id, c.course_code, c.title, c.description, c.category, c.thumbnail_path, c.assigned_trainer_npk,
      trainer.fullname AS assigned_trainer_name,
      c.status, c.updated_at, COUNT(DISTINCT s.session_id) AS session_count,
      COUNT(DISTINCT m.material_id) AS material_count
      FROM dbo.courses c LEFT JOIN dbo.course_sessions s ON s.course_id = c.course_id
      LEFT JOIN dbo.course_materials m ON m.session_id = s.session_id
      LEFT JOIN dbo.employee_accounts trainer ON trainer.employee_npk=c.assigned_trainer_npk
      WHERE (@isAdmin=1 OR c.created_by=@npk OR c.assigned_trainer_npk=@npk)
      GROUP BY c.course_id, c.course_code, c.title, c.description, c.category, c.thumbnail_path, c.assigned_trainer_npk,trainer.fullname,c.status,c.updated_at
      ORDER BY c.updated_at DESC`);
    const page = paginateCollection(_req, result.recordset);
    res.json({ courses: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

const activeActivityStreams = new Map();
router.get('/activity/events', requirePermission('activity.view'), async (req, res, next) => {
  const streamKey = `${req.user.role}:${req.user.sub}`;
  const current = activeActivityStreams.get(streamKey) || 0;
  if (current >= 2) return res.status(429).json({ error: { code: 'STREAM_LIMIT', message: 'Batas koneksi aktivitas realtime tercapai.' } });
  try {
    const initial = await getActivityFeed(req.user.role, 1);
    let lastActivityKey = initial[0]?.activity_id || '';
    activeActivityStreams.set(streamKey, current + 1);
    res.status(200).set({
      'Content-Type': 'text/event-stream; charset=utf-8',
      'Cache-Control': 'no-cache, no-transform',
      Connection: 'keep-alive',
      'X-Accel-Buffering': 'no'
    });
    res.flushHeaders?.();
    res.write('event: ready\ndata: {}\n\n');

    let checking = false;
    const poll = async () => {
      if (checking || res.writableEnded) return;
      checking = true;
      try {
        const latest = (await getActivityFeed(req.user.role, 1))[0];
        if (latest && latest.activity_id !== lastActivityKey) {
          lastActivityKey = latest.activity_id;
          res.write(`id: ${latest.activity_id}\nevent: activity\ndata: ${JSON.stringify(latest)}\n\n`);
        }
      } catch (error) {
        console.warn('[activity-feed] Realtime check failed:', error.message);
      } finally { checking = false; }
    };
    const pollTimer = setInterval(poll, 4000);
    const heartbeat = setInterval(() => res.write(': keep-alive\n\n'), 25000);
    const remainingSessionMs = Math.max(1000, ((Number(req.user.exp) || Math.floor(Date.now() / 1000) + 8 * 60 * 60) * 1000) - Date.now());
    const expiry = setTimeout(() => res.end(), remainingSessionMs);
    res.on('close', () => {
      clearInterval(pollTimer);
      clearInterval(heartbeat);
      clearTimeout(expiry);
      const nextCount = Math.max(0, (activeActivityStreams.get(streamKey) || 1) - 1);
      if (nextCount) activeActivityStreams.set(streamKey, nextCount);
      else activeActivityStreams.delete(streamKey);
    });
  } catch (error) { next(error); }
});

router.get('/activity', requirePermission('activity.view'), async (_req, res, next) => {
  try {
    const activities = await getActivityFeed(_req.user.role, 100);
    const page = paginateCollection(_req, activities);
    res.set('Cache-Control', 'private, no-store');
    res.json({ activities: page.data, ...(page.pagination ? { pagination: page.pagination } : {}) });
  } catch (error) { next(error); }
});

module.exports = router;
