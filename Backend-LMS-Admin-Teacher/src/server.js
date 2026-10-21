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
const gradeRoutes = require('./routes/grades');
const forumRoutes = require('./routes/forum');
const { notFound, errorHandler } = require('./middleware/errors');
const { startStudentSync } = require('./services/student-sync');

const app = express();
app.disable('x-powered-by');
app.use(helmet());
app.use(cors({ origin: env.FRONTEND_ORIGIN, methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], allowedHeaders: ['Content-Type', 'Authorization'] }));
app.use(express.json({ limit: '32kb', strict: true }));
app.get('/health', (_req, res) => res.json({ status: 'ok' }));
app.use('/api/v1/auth', authRoutes);
app.use('/api/v1/dashboard', dashboardRoutes);
app.use('/api/v1/courses', courseRoutes);
app.use('/api/v1/users', userRoutes);
app.use('/api/v1/grades', gradeRoutes);
app.use('/api/v1/forum', forumRoutes);
app.use(notFound);
app.use(errorHandler);

async function start() {
  try {
    await pool.connect();
    const stopSync = await startStudentSync();
    const server = app.listen(env.PORT, '127.0.0.1', () => console.log(`DLS API listening at http://127.0.0.1:${env.PORT}`));
    const stop = async () => { server.close(); await stopSync(); await pool.close(); process.exit(0); };
    process.on('SIGINT', stop); process.on('SIGTERM', stop);
  } catch (error) { console.error('Database connection failed. Check server configuration.'); if (env.NODE_ENV !== 'production') console.error(error.message); process.exit(1); }
}

start();
