require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const env = require('./config/env');
const { pool } = require('./config/database');
const authRoutes = require('./routes/auth');
const dashboardRoutes = require('./routes/dashboard');
const courseRoutes = require('./routes/courses');
const userRoutes = require('./routes/users');
const reviewerRoutes = require('./routes/reviewers');
const gradeRoutes = require('./routes/grades');
const forumRoutes = require('./routes/forum');
const notificationRoutes = require('./routes/notifications');
const materialWorkflowRoutes = require('./routes/material-workflow');
const jobRoutes = require('./routes/jobs');
const quizRoutes = require('./routes/quizzes');
const enrollmentRoutes = require('./routes/enrollments');
const settingsRoutes = require('./routes/settings');
const { notFound, errorHandler } = require('./middleware/errors');
const { apiLimiter, writeLimiter } = require('./middleware/rate-limit');
const { paginationMiddleware } = require('./middleware/pagination');
const { startStudentSync } = require('./services/student-sync');
const { closeStudentAuthPool } = require('./services/student-auth');
const { startNotificationMailWorker } = require('./services/notification-mail');
const { registerJobHandler, startAdminJobWorker } = require('./services/admin-jobs');
const { registerUserJobHandler } = require('./services/user-job-handler');
const { registerRecordDeleteJobHandler } = require('./services/record-delete-job-handler');
const { loadSystemSettings } = require('./services/system-settings');
const { closeQuizStore } = require('./services/quiz-store');

const app = express();
app.disable('x-powered-by');

// Pengukuran latensi respons (ms) dan header performa
app.use((req, res, next) => {
  const start = performance.now();
  const originalEnd = res.end;
  res.end = function (...args) {
    if (!res.headersSent) {
      const durationMs = (performance.now() - start).toFixed(2);
      res.setHeader('X-Response-Time', `${durationMs}ms`);
    }
    return originalEnd.apply(this, args);
  };
  next();
});

app.use(helmet({
  crossOriginEmbedderPolicy: false,
  referrerPolicy: { policy: 'strict-origin-when-cross-origin' }
}));
const trustedOrigins = new Set([env.FRONTEND_ORIGIN, env.STUDENT_APP_URL].map(value => {
  try { return new URL(value).origin; } catch { return ''; }
}));
app.use(cors({
  origin(origin, callback) {
    if (!origin || trustedOrigins.has(origin)) return callback(null, true);
    return callback(null, false);
  },
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
  allowedHeaders: ['Content-Type', 'Authorization', 'Last-Event-ID']
}));
app.use('/api/v1', apiLimiter, paginationMiddleware);
app.use('/api/v1', (req, res, next) => req.method === 'GET' ? next() : writeLimiter(req, res, next));
app.use(express.json({ limit: '8mb', strict: true }));
app.get('/health', (_req, res) => res.json({ status: 'ok' }));
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/dashboard', dashboardRoutes);
app.use('/api/v1/courses', courseRoutes);
app.use('/api/v1/users', userRoutes);
app.use('/api/v1/reviewers', reviewerRoutes);
app.use('/api/v1/grades', gradeRoutes);
app.use('/api/v1/forum', forumRoutes);
app.use('/api/v1/notifications', notificationRoutes);
app.use('/api/v1/material-workflow', materialWorkflowRoutes);
app.use('/api/v1/jobs', jobRoutes);
app.use('/api/v1/quizzes', quizRoutes);
app.use('/api/v1/enrollments', enrollmentRoutes);
app.use('/api/v1/settings', settingsRoutes);
app.use(notFound);
app.use(errorHandler);

async function start() {
  let stopSync = async () => {};
  let stopNotificationMail = async () => {};
  let stopAdminJobs = async () => {};

  try {
    await pool.connect();
    await loadSystemSettings();
  } catch (error) {
    console.error('Database connection failed. Check server configuration.');
    if (env.NODE_ENV !== 'production') console.error(error.message);
    process.exit(1);
    return;
  }

  stopNotificationMail = startNotificationMailWorker();
  registerUserJobHandler(registerJobHandler);
  registerRecordDeleteJobHandler(registerJobHandler);
  stopAdminJobs = await startAdminJobWorker();

  // Student sync is an optional integration. A missing Student DB credential
  // must not prevent Admin/Teacher authentication and pages from loading.
  try {
    stopSync = await startStudentSync();
  } catch (error) {
    console.error('Student database sync is unavailable; the Admin/Teacher API will continue without syncing Student data.');
    if (env.NODE_ENV !== 'production') console.error(error.message);
  }

  const server = app.listen(env.PORT, '127.0.0.1', () => console.log(`DLS API listening at http://127.0.0.1:${env.PORT}`));
  const stop = async () => {
    server.close();
    await stopSync();
    await stopAdminJobs();
    await stopNotificationMail();
    await closeStudentAuthPool();
    await closeQuizStore();
    await pool.close();
    process.exit(0);
  };
  process.on('SIGINT', stop);
  process.on('SIGTERM', stop);
}

start();
