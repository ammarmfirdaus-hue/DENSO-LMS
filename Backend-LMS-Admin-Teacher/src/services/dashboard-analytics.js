const { sql, pool } = require('../config/database');
const { getQuizStore } = require('./quiz-store');
const {
  normalizeDaysWindow,
  toIsoDateUtc,
  buildDateWindow,
  parseMaterialTypes
} = require('./dashboard-analytics-helpers');

async function fetchMysqlTelemetry({ isAdmin, courseCodes, days }) {
  const timeoutMs = 1200;
  let timer;
  try {
    const queryPromise = (async () => {
      const store = getQuizStore();
      const [[studentRow]] = await store.execute(
        `SELECT COUNT(*) AS active_students
         FROM student_accounts
         WHERE account_type = 'student' AND status = 'active'`
      );

      let enrollmentPending = 0;
      try {
        if (isAdmin || courseCodes.length) {
          const courseFilter = isAdmin
            ? ''
            : `AND c.course_code IN (${courseCodes.map(() => '?').join(',')})`;
          const params = isAdmin ? [] : courseCodes;
          const [[pendingRow]] = await store.execute(
            `SELECT COUNT(*) AS pending_requests
             FROM course_enrollment_requests r
             JOIN courses c ON c.course_id = r.course_id
             WHERE r.status = 'pending' ${courseFilter}`,
            params
          );
          enrollmentPending = Number(pendingRow?.pending_requests || 0);
        }
      } catch {
        enrollmentPending = 0;
      }

      let quizzesPublished = 0;
      let quizAttempts = 0;
      let quizPassed = 0;
      try {
        const courseFilter = isAdmin || !courseCodes.length
          ? ''
          : `AND c.course_code IN (${courseCodes.map(() => '?').join(',')})`;
        const params = isAdmin || !courseCodes.length ? [] : courseCodes;
        if (isAdmin || courseCodes.length) {
          const [[quizSummary]] = await store.execute(
            `SELECT COUNT(DISTINCT CASE WHEN q.status = 'published' THEN q.quiz_id END) AS published_quizzes,
                    COUNT(a.attempt_id) AS attempts_total,
                    SUM(CASE WHEN a.score >= q.passing_score THEN 1 ELSE 0 END) AS attempts_passed
             FROM courses c
             LEFT JOIN course_quizzes q ON q.course_id = c.course_id
             LEFT JOIN course_quiz_attempts a ON a.quiz_id = q.quiz_id
               AND a.submitted_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? DAY)
             WHERE 1=1 ${courseFilter}`,
            [days, ...params]
          );
          quizzesPublished = Number(quizSummary?.published_quizzes || 0);
          quizAttempts = Number(quizSummary?.attempts_total || 0);
          quizPassed = Number(quizSummary?.attempts_passed || 0);
        }
      } catch {
        quizzesPublished = 0;
      }

      return {
        available: true,
        activeStudentsAccountCount: Number(studentRow?.active_students || 0),
        pendingEnrollmentRequests: enrollmentPending,
        publishedQuizzes: quizzesPublished,
        quizAttempts,
        quizPassRate: quizAttempts > 0 ? Math.round((quizPassed / quizAttempts) * 1000) / 10 : null
      };
    })();

    return await Promise.race([
      queryPromise,
      new Promise(resolve => {
        timer = setTimeout(() => resolve({ available: false }), timeoutMs);
      })
    ]);
  } catch {
    return { available: false };
  } finally {
    if (timer) clearTimeout(timer);
  }
}

async function upsertTodaySnapshot({ scopeKey, summary, dailyActivities }) {
  try {
    const todayIso = toIsoDateUtc(new Date());
    await pool.request()
      .input('snapshotDate', sql.Date, todayIso)
      .input('scopeKey', sql.VarChar(64), scopeKey)
      .input('activeCourses', sql.Int, summary.activeCourses)
      .input('activeEmployees', sql.Int, summary.activeEmployees)
      .input('activeStudents', sql.Int, summary.activeStudents)
      .input('activeSessions', sql.Int, summary.activeSessions)
      .input('publishedMaterials', sql.Int, summary.publishedMaterials)
      .input('pendingSubmissions', sql.Int, summary.pendingSubmissions)
      .input('completedEnrollments', sql.Int, summary.completedEnrollments)
      .input('dailyActivities', sql.Int, dailyActivities)
      .input('avgProgress', sql.Decimal(5, 2), summary.avgProgress)
      .input('avgQuizScore', sql.Decimal(5, 2), summary.avgQuizScore)
      .query(`IF OBJECT_ID(N'dbo.dashboard_daily_snapshots', N'U') IS NOT NULL
        BEGIN
          MERGE dbo.dashboard_daily_snapshots AS target
          USING (SELECT @snapshotDate AS snapshot_date, @scopeKey AS scope_key) AS source
          ON target.snapshot_date = source.snapshot_date AND target.scope_key = source.scope_key
          WHEN MATCHED THEN
            UPDATE SET active_courses = @activeCourses,
                       active_employees = @activeEmployees,
                       active_students = @activeStudents,
                       active_sessions = @activeSessions,
                       published_materials = @publishedMaterials,
                       pending_submissions = @pendingSubmissions,
                       completed_enrollments = @completedEnrollments,
                       daily_activities = @dailyActivities,
                       avg_progress = @avgProgress,
                       avg_quiz_score = @avgQuizScore,
                       updated_at = SYSUTCDATETIME()
          WHEN NOT MATCHED THEN
            INSERT(snapshot_date, scope_key, active_courses, active_employees, active_students,
                   active_sessions, published_materials, pending_submissions, completed_enrollments,
                   daily_activities, avg_progress, avg_quiz_score)
            VALUES(@snapshotDate, @scopeKey, @activeCourses, @activeEmployees, @activeStudents,
                   @activeSessions, @publishedMaterials, @pendingSubmissions, @completedEnrollments,
                   @dailyActivities, @avgProgress, @avgQuizScore);
        END;`);
  } catch {
    // Non-blocking: analytics reads continue even if snapshot table has restricted DDL/write rights.
  }
}

async function getDashboardAnalytics({ user, days = 7 }) {
  const normalizedDays = normalizeDaysWindow(days);
  const isAdmin = user.role === 'admin' ? 1 : 0;
  const npk = String(user.sub || '');
  const scopeKey = isAdmin ? 'global' : `teacher:${npk.slice(0, 50)}`;
  const dateWindow = buildDateWindow(normalizedDays);
  const startIso = dateWindow[0].isoDate;
  const endIso = dateWindow[dateWindow.length - 1].isoDate;

  const corePromise = pool.request()
    .input('isAdmin', sql.Bit, isAdmin)
    .input('npk', sql.VarChar(50), npk)
    .query(`
      SELECT
        COUNT_BIG(*) AS total_courses,
        SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END) AS active_courses,
        SUM(CASE WHEN c.status = 'active' AND c.category = 'Wajib' THEN 1 ELSE 0 END) AS wajib_courses,
        SUM(CASE WHEN c.status = 'active' AND c.category = 'Pilihan' THEN 1 ELSE 0 END) AS pilihan_courses
      FROM dbo.courses c
      WHERE (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk);

      SELECT
        (SELECT COUNT_BIG(*) FROM dbo.employee_accounts WHERE status = 'active') AS active_employees,
        (SELECT COUNT_BIG(*) FROM dbo.employee_accounts WHERE status = 'active' AND role = 'teacher') AS active_teachers,
        (SELECT COUNT_BIG(*) FROM dbo.employee_accounts WHERE status = 'active' AND role = 'reviewer') AS active_reviewers,
        (SELECT COUNT_BIG(*) FROM dbo.course_sessions s JOIN dbo.courses c ON c.course_id = s.course_id
          WHERE s.status = 'active' AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)) AS active_sessions,
        (SELECT COUNT_BIG(*) FROM dbo.course_materials m
          JOIN dbo.course_sessions s ON s.session_id = m.session_id
          JOIN dbo.courses c ON c.course_id = s.course_id
          WHERE (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)) AS published_materials,
        (SELECT COUNT_BIG(*) FROM dbo.forum_threads t
          JOIN dbo.courses c ON c.course_code = t.course_code
          WHERE t.status = 'visible' AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)) AS visible_threads;

      SELECT
        COUNT_BIG(DISTINCT p.student_npk) AS distinct_students,
        COUNT_BIG(*) AS total_enrollments,
        SUM(CASE WHEN p.enrollment_status = 'completed' THEN 1 ELSE 0 END) AS completed_enrollments,
        SUM(CASE WHEN p.enrollment_status = 'active' THEN 1 ELSE 0 END) AS active_enrollments,
        SUM(CASE WHEN p.enrollment_status = 'dropped' THEN 1 ELSE 0 END) AS dropped_enrollments,
        COALESCE(AVG(CAST(p.progress AS FLOAT)), 0) AS avg_progress,
        AVG(CAST(p.score AS FLOAT)) AS avg_score,
        AVG(CAST(p.quiz_score AS FLOAT)) AS avg_quiz_score,
        SUM(CASE WHEN p.progress BETWEEN 0 AND 25 THEN 1 ELSE 0 END) AS b0_25,
        SUM(CASE WHEN p.progress BETWEEN 26 AND 50 THEN 1 ELSE 0 END) AS b26_50,
        SUM(CASE WHEN p.progress BETWEEN 51 AND 75 THEN 1 ELSE 0 END) AS b51_75,
        SUM(CASE WHEN p.progress >= 76 THEN 1 ELSE 0 END) AS b76_100,
        SUM(CASE WHEN p.score >= 85 THEN 1 ELSE 0 END) AS score_excellent,
        SUM(CASE WHEN p.score >= 70 AND p.score < 85 THEN 1 ELSE 0 END) AS score_good,
        SUM(CASE WHEN p.score IS NOT NULL AND p.score < 70 THEN 1 ELSE 0 END) AS score_attention,
        SUM(CASE WHEN p.score IS NULL THEN 1 ELSE 0 END) AS score_ungraded
      FROM dbo.student_progress_snapshots p
      JOIN dbo.courses c ON c.course_code = p.course_code
      WHERE (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk);

      SELECT c.course_code
      FROM dbo.courses c
      WHERE c.status = 'active' AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk);
    `);

  const breakdownsPromise = pool.request()
    .input('isAdmin', sql.Bit, isAdmin)
    .input('npk', sql.VarChar(50), npk)
    .query(`
      SELECT ms.status, COUNT_BIG(*) AS total
      FROM dbo.material_submissions ms
      JOIN dbo.course_sessions s ON s.session_id = ms.session_id
      JOIN dbo.courses c ON c.course_id = s.course_id
      WHERE (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk OR ms.submitted_by = @npk)
      GROUP BY ms.status;

      SELECT m.material_type, COUNT_BIG(*) AS total
      FROM dbo.course_materials m
      JOIN dbo.course_sessions s ON s.session_id = m.session_id
      JOIN dbo.courses c ON c.course_id = s.course_id
      WHERE (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)
      GROUP BY m.material_type;

      SELECT TOP 7
        c.course_id,
        c.course_code,
        c.title,
        c.category,
        COUNT(DISTINCT s.session_id) AS session_count,
        COUNT(DISTINCT m.material_id) AS material_count,
        COALESCE(prog.student_count, 0) AS student_count,
        COALESCE(prog.completed_count, 0) AS completed_count,
        COALESCE(prog.avg_progress, 0) AS avg_progress,
        prog.avg_score
      FROM dbo.courses c
      LEFT JOIN dbo.course_sessions s ON s.course_id = c.course_id AND s.status = 'active'
      LEFT JOIN dbo.course_materials m ON m.session_id = s.session_id
      LEFT JOIN (
        SELECT course_code,
               COUNT_BIG(*) AS student_count,
               SUM(CASE WHEN enrollment_status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
               AVG(CAST(progress AS FLOAT)) AS avg_progress,
               AVG(CAST(score AS FLOAT)) AS avg_score
        FROM dbo.student_progress_snapshots
        GROUP BY course_code
      ) prog ON prog.course_code = c.course_code
      WHERE c.status = 'active'
        AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)
      GROUP BY c.course_id, c.course_code, c.title, c.category, c.updated_at,
               prog.student_count, prog.completed_count, prog.avg_progress, prog.avg_score
      ORDER BY COALESCE(prog.student_count, 0) DESC, COUNT(DISTINCT m.material_id) DESC, c.updated_at DESC;
    `);

  const timeSeriesPromise = pool.request()
    .input('isAdmin', sql.Bit, isAdmin)
    .input('npk', sql.VarChar(50), npk)
    .input('startDate', sql.Date, startIso)
    .input('endDateNext', sql.Date, toIsoDateUtc(new Date(Date.parse(`${endIso}T00:00:00Z`) + 86400000)))
    .input('scopeKey', sql.VarChar(64), scopeKey)
    .query(`
      SELECT CONVERT(VARCHAR(10), CAST(a.created_at AS DATE), 23) AS d,
             COUNT_BIG(*) AS total
      FROM dbo.activity_logs a
      LEFT JOIN dbo.employee_accounts e ON e.employee_npk = a.employee_npk
      WHERE a.created_at >= CAST(@startDate AS DATETIME2)
        AND a.created_at < CAST(@endDateNext AS DATETIME2)
        AND (@isAdmin = 1 OR e.role = 'teacher' OR a.employee_npk = @npk)
      GROUP BY CAST(a.created_at AS DATE);

      SELECT CONVERT(VARCHAR(10), CAST(p.source_updated_at AS DATE), 23) AS d,
             COUNT_BIG(*) AS student_updates,
             SUM(CASE WHEN p.enrollment_status = 'completed' THEN 1 ELSE 0 END) AS completed_updates
      FROM dbo.student_progress_snapshots p
      JOIN dbo.courses c ON c.course_code = p.course_code
      WHERE p.source_updated_at >= CAST(@startDate AS DATETIME2)
        AND p.source_updated_at < CAST(@endDateNext AS DATETIME2)
        AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)
      GROUP BY CAST(p.source_updated_at AS DATE);

      SELECT CONVERT(VARCHAR(10), CAST(ms.created_at AS DATE), 23) AS d,
             COUNT_BIG(*) AS submissions
      FROM dbo.material_submissions ms
      JOIN dbo.course_sessions s ON s.session_id = ms.session_id
      JOIN dbo.courses c ON c.course_id = s.course_id
      WHERE ms.created_at >= CAST(@startDate AS DATETIME2)
        AND ms.created_at < CAST(@endDateNext AS DATETIME2)
        AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk OR ms.submitted_by = @npk)
      GROUP BY CAST(ms.created_at AS DATE);

      SELECT CONVERT(VARCHAR(10), CAST(c.created_at AS DATE), 23) AS d,
             COUNT_BIG(*) AS total
      FROM dbo.courses c
      WHERE c.status = 'active'
        AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)
      GROUP BY CAST(c.created_at AS DATE);

      SELECT CONVERT(VARCHAR(10), CAST(s.created_at AS DATE), 23) AS d,
             COUNT_BIG(*) AS total
      FROM dbo.course_sessions s
      JOIN dbo.courses c ON c.course_id = s.course_id
      WHERE s.status = 'active'
        AND (@isAdmin = 1 OR c.created_by = @npk OR c.assigned_trainer_npk = @npk)
      GROUP BY CAST(s.created_at AS DATE);

      SELECT CONVERT(VARCHAR(10), CAST(created_at AS DATE), 23) AS d,
             COUNT_BIG(*) AS total
      FROM dbo.employee_accounts
      WHERE status = 'active'
      GROUP BY CAST(created_at AS DATE);

      IF OBJECT_ID(N'dbo.dashboard_daily_snapshots', N'U') IS NOT NULL
        SELECT CONVERT(VARCHAR(10), snapshot_date, 23) AS d,
               active_courses, active_employees, active_students, active_sessions,
               published_materials, pending_submissions, completed_enrollments,
               daily_activities, avg_progress
        FROM dbo.dashboard_daily_snapshots
        WHERE scope_key = @scopeKey
          AND snapshot_date >= @startDate
          AND snapshot_date <= @endDateNext
        ORDER BY snapshot_date ASC;
      ELSE
        SELECT TOP 0 NULL AS d;
    `);

  const [coreResult, breakdownResult, timeSeriesResult] = await Promise.all([
    corePromise,
    breakdownsPromise,
    timeSeriesPromise
  ]);

  const courseCounts = coreResult.recordsets[0]?.[0] || {};
  const assetCounts = coreResult.recordsets[1]?.[0] || {};
  const progressAgg = coreResult.recordsets[2]?.[0] || {};
  const accessibleCodes = (coreResult.recordsets[3] || []).map(r => r.course_code);

  const mysqlTelemetry = await fetchMysqlTelemetry({
    isAdmin: Boolean(isAdmin),
    courseCodes: accessibleCodes,
    days: normalizedDays
  });

  const pipelineMap = { pending_admin: 0, pending_review: 0, rejected: 0, approved: 0, published: 0 };
  for (const row of breakdownResult.recordsets[0] || []) {
    if (row.status in pipelineMap) pipelineMap[row.status] = Number(row.total || 0);
  }
  const pendingSubmissions = pipelineMap.pending_admin + pipelineMap.pending_review;

  const activeCourses = Number(courseCounts.active_courses || 0);
  const activeEmployees = Number(assetCounts.active_employees || 0);
  const distinctStudents = Number(progressAgg.distinct_students || 0);
  const activeStudents = isAdmin && mysqlTelemetry.available
    ? Math.max(distinctStudents, mysqlTelemetry.activeStudentsAccountCount || 0)
    : distinctStudents;
  const activeUsersTotal = isAdmin ? (activeEmployees + activeStudents) : activeStudents;
  const activeSessions = Number(assetCounts.active_sessions || 0);
  const publishedMaterials = Number(assetCounts.published_materials || 0);
  const completedEnrollments = Number(progressAgg.completed_enrollments || 0);
  const avgProgress = Math.round(Number(progressAgg.avg_progress || 0) * 10) / 10;
  const avgScore = progressAgg.avg_score === null || progressAgg.avg_score === undefined
    ? null
    : Math.round(Number(progressAgg.avg_score) * 10) / 10;
  const avgQuizScore = progressAgg.avg_quiz_score === null || progressAgg.avg_quiz_score === undefined
    ? null
    : Math.round(Number(progressAgg.avg_quiz_score) * 10) / 10;

  const dailyActivityMap = new Map((timeSeriesResult.recordsets[0] || []).map(r => [r.d, Number(r.total || 0)]));
  const dailyStudentMap = new Map((timeSeriesResult.recordsets[1] || []).map(r => [r.d, {
    studentUpdates: Number(r.student_updates || 0),
    completedUpdates: Number(r.completed_updates || 0)
  }]));
  const dailySubmissionMap = new Map((timeSeriesResult.recordsets[2] || []).map(r => [r.d, Number(r.submissions || 0)]));
  const snapshotMap = new Map((timeSeriesResult.recordsets[6] || []).filter(r => r.d).map(r => [r.d, r]));

  function buildCumulativeByDate(rows, currentTotal) {
    const dated = (rows || []).filter(r => r.d).map(r => [r.d, Number(r.total || 0)]);
    return isoDate => {
      let laterCreated = 0;
      for (const [d, count] of dated) {
        if (d > isoDate) laterCreated += count;
      }
      return Math.max(0, currentTotal - laterCreated);
    };
  }

  const cumulativeCoursesFn = buildCumulativeByDate(timeSeriesResult.recordsets[3], activeCourses);
  const cumulativeSessionsFn = buildCumulativeByDate(timeSeriesResult.recordsets[4], activeSessions);
  const cumulativeEmployeesFn = buildCumulativeByDate(timeSeriesResult.recordsets[5], activeEmployees);

  const trends = dateWindow.map(({ isoDate, label }) => {
    const snap = snapshotMap.get(isoDate);
    const stu = dailyStudentMap.get(isoDate);
    const liveActivityCount = dailyActivityMap.get(isoDate) || 0;
    const snapActivities = snap ? Number(snap.daily_activities || 0) : 0;
    const activities = isoDate === endIso ? liveActivityCount : Math.max(liveActivityCount, snapActivities);
    const snapCourses = snap ? Number(snap.active_courses || 0) : cumulativeCoursesFn(isoDate);
    const snapSessions = snap ? Number(snap.active_sessions || 0) : cumulativeSessionsFn(isoDate);
    const snapUsers = snap
      ? (isAdmin ? Number(snap.active_employees || 0) + Number(snap.active_students || 0) : Number(snap.active_students || 0))
      : (isAdmin ? cumulativeEmployeesFn(isoDate) + activeStudents : activeStudents);

    return {
      date: isoDate,
      label,
      activities,
      studentUpdates: stu?.studentUpdates || 0,
      completedUpdates: stu?.completedUpdates || 0,
      submissions: dailySubmissionMap.get(isoDate) || 0,
      activeCourses: snapCourses,
      activeUsers: snapUsers,
      activeSessions: snapSessions
    };
  });

  const summaryPayload = {
    activeCourses,
    totalCourses: Number(courseCounts.total_courses || 0),
    wajibCourses: Number(courseCounts.wajib_courses || 0),
    pilihanCourses: Number(courseCounts.pilihan_courses || 0),
    activeEmployees,
    activeTeachers: Number(assetCounts.active_teachers || 0),
    activeReviewers: Number(assetCounts.active_reviewers || 0),
    activeStudents,
    activeUsersTotal,
    activeSessions,
    publishedMaterials,
    pendingSubmissions,
    visibleThreads: Number(assetCounts.visible_threads || 0),
    totalEnrollments: Number(progressAgg.total_enrollments || 0),
    completedEnrollments,
    activeEnrollments: Number(progressAgg.active_enrollments || 0),
    droppedEnrollments: Number(progressAgg.dropped_enrollments || 0),
    avgProgress,
    avgScore,
    avgQuizScore,
    publishedQuizzes: mysqlTelemetry.available ? mysqlTelemetry.publishedQuizzes : null,
    quizAttempts: mysqlTelemetry.available ? mysqlTelemetry.quizAttempts : null,
    quizPassRate: mysqlTelemetry.available ? mysqlTelemetry.quizPassRate : null,
    pendingEnrollmentRequests: mysqlTelemetry.available ? mysqlTelemetry.pendingEnrollmentRequests : 0
  };

  const todayActivities = trends[trends.length - 1]?.activities || 0;
  upsertTodaySnapshot({ scopeKey, summary: summaryPayload, dailyActivities: todayActivities });

  return {
    days: normalizedDays,
    role: user.role,
    generatedAt: new Date().toISOString(),
    summary: summaryPayload,
    sparklines: {
      labels: trends.map(t => t.label),
      activeCourses: trends.map(t => t.activeCourses),
      activeUsers: trends.map(t => t.activeUsers),
      activeSessions: trends.map(t => t.activeSessions),
      dailyActivities: trends.map(t => t.activities + t.studentUpdates)
    },
    trends,
    courseBreakdown: (breakdownResult.recordsets[2] || []).map(row => ({
      course_id: row.course_id,
      course_code: row.course_code,
      title: row.title,
      category: row.category,
      session_count: Number(row.session_count || 0),
      material_count: Number(row.material_count || 0),
      student_count: Number(row.student_count || 0),
      completed_count: Number(row.completed_count || 0),
      avg_progress: Math.round(Number(row.avg_progress || 0) * 10) / 10,
      avg_score: row.avg_score === null || row.avg_score === undefined ? null : Math.round(Number(row.avg_score) * 10) / 10
    })),
    materialPipeline: pipelineMap,
    materialTypes: parseMaterialTypes(breakdownResult.recordsets[1]),
    progressDistribution: {
      buckets: [
        { label: '0–25%', count: Number(progressAgg.b0_25 || 0) },
        { label: '26–50%', count: Number(progressAgg.b26_50 || 0) },
        { label: '51–75%', count: Number(progressAgg.b51_75 || 0) },
        { label: '76–100%', count: Number(progressAgg.b76_100 || 0) }
      ],
      status: {
        active: summaryPayload.activeEnrollments,
        completed: summaryPayload.completedEnrollments,
        dropped: summaryPayload.droppedEnrollments
      },
      scoreBrackets: {
        excellent: Number(progressAgg.score_excellent || 0),
        good: Number(progressAgg.score_good || 0),
        needsAttention: Number(progressAgg.score_attention || 0),
        ungraded: Number(progressAgg.score_ungraded || 0)
      }
    }
  };
}

module.exports = {
  buildDateWindow,
  normalizeDaysWindow,
  parseMaterialTypes,
  getDashboardAnalytics
};
