const crypto = require('crypto');

const RESOURCE_MAP = [
  { prefix: '/api/v1/auth/login', resource: 'AUTH', action: 'LOGIN' },
  { prefix: '/api/v1/auth/logout', resource: 'AUTH', action: 'LOGOUT' },
  { prefix: '/api/v1/auth/register', resource: 'AUTH', action: 'REGISTER' },
  { prefix: '/api/v1/auth', resource: 'AUTH' },
  { prefix: '/api/v1/users', resource: 'USER' },
  { prefix: '/api/v1/courses', resource: 'COURSE' },
  { prefix: '/api/v1/modules', resource: 'MODULE' },
  { prefix: '/api/v1/quizzes', resource: 'QUIZ' },
  { prefix: '/api/v1/enrollments', resource: 'ENROLLMENT' },
  { prefix: '/api/v1/notifications', resource: 'NOTIFICATION' },
  { prefix: '/api/v1/backups', resource: 'BACKUP' },
  { prefix: '/api/v1/settings', resource: 'SETTINGS' },
  { prefix: '/api/v1/reports', resource: 'REPORT' },
  { prefix: '/api/v1/activity', resource: 'ACTIVITY' },
  { prefix: '/api/v1/jobs', resource: 'JOB' },
  { prefix: '/api/v1/uploads', resource: 'FILE' },
  { prefix: '/health', resource: 'HEALTH', action: 'HEALTH' }
];

function formatTimestamp(date = new Date()) {
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} `
    + `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

function inferActionAndResource(method, originalUrl = '/') {
  const cleanPath = String(originalUrl).split('?')[0];
  const matched = RESOURCE_MAP.find((item) => cleanPath.startsWith(item.prefix));
  const resource = matched ? matched.resource : 'API';

  let action = matched && matched.action ? matched.action : null;
  if (!action) {
    if (cleanPath.includes('upload')) action = 'UPLOAD';
    else if (cleanPath.includes('download') || cleanPath.includes('export')) action = 'DOWNLOAD';
    else if (method === 'POST') action = 'CREATE';
    else if (method === 'PUT' || method === 'PATCH') action = 'UPDATE';
    else if (method === 'DELETE') action = 'DELETE';
    else action = 'READ';
  }

  return { action, resource, cleanPath };
}

function sanitizeMessage(text) {
  return String(text || '')
    .replace(/(password|token|secret|apikey|api_key|authorization)\s*[:=]\s*\S+/gi, '$1=[REDACTED]')
    .replace(/\r?\n+/g, ' ')
    .trim();
}

function logAppEvent({
  level = 'INFO',
  action = 'SYSTEM',
  resource = 'SERVER',
  message = '',
  requestId,
  user,
  durationMs,
  error
} = {}) {
  const ts = formatTimestamp();
  const lvl = String(level).toUpperCase();
  const act = String(action).toUpperCase();
  const res = String(resource).toUpperCase();

  const meta = [];
  if (requestId) meta.push(`req=${requestId}`);
  if (user) meta.push(`user=${user}`);
  if (durationMs !== undefined && durationMs !== null) meta.push(`${durationMs}ms`);

  const suffix = meta.length ? ` (${meta.join(', ')})` : '';
  const line = `[${ts}] [API] [${lvl}] [${act}] [${res}] ${sanitizeMessage(message)}${suffix}`;

  if (lvl === 'ERROR' || lvl === 'CRITICAL') {
    console.error(line);
    if (error && error.stack && process.env.NODE_ENV !== 'production') {
      const stackLines = String(error.stack).split('\n').slice(1, 5).map((l) => `  ${l.trim()}`);
      if (stackLines.length) console.error(`[${ts}] [API] [TRACE] [${act}] [${res}] ${stackLines.join(' | ')}`);
    }
  } else if (lvl === 'WARNING' || lvl === 'WARN') {
    console.warn(line);
  } else {
    console.log(line);
  }
}

function requestLoggerMiddleware() {
  return (req, res, next) => {
    const start = performance.now();
    const requestId = crypto.randomUUID().slice(0, 8);
    req.requestId = requestId;

    res.on('finish', () => {
      const durationMs = Number((performance.now() - start).toFixed(1));
      const { action, resource, cleanPath } = inferActionAndResource(req.method, req.originalUrl || req.url);

      if (cleanPath === '/health' && res.statusCode < 400 && process.env.LOG_HEALTH !== 'true') {
        return;
      }
      if (cleanPath.startsWith('/api/v1/jobs') && res.statusCode === 304 && process.env.LOG_POLLING !== 'true') {
        return;
      }

      const level = res.statusCode >= 500
        ? 'ERROR'
        : res.statusCode >= 400
          ? 'WARNING'
          : 'INFO';

      const user = req.user?.npk || req.user?.id || undefined;
      const message = `${req.method} ${cleanPath} -> ${res.statusCode}`;

      logAppEvent({
        level,
        action,
        resource,
        message,
        requestId,
        user,
        durationMs
      });
    });

    next();
  };
}

function installProcessErrorHandlers() {
  if (global.__dlsErrorHandlersInstalled) return;
  global.__dlsErrorHandlersInstalled = true;

  process.on('unhandledRejection', (reason) => {
    const err = reason instanceof Error ? reason : new Error(String(reason));
    logAppEvent({
      level: 'ERROR',
      action: 'PROCESS',
      resource: 'UNHANDLED_REJECTION',
      message: err.message || 'Unhandled Promise Rejection',
      error: err
    });
  });

  process.on('uncaughtException', (err) => {
    logAppEvent({
      level: 'CRITICAL',
      action: 'PROCESS',
      resource: 'UNCAUGHT_EXCEPTION',
      message: err.message || 'Uncaught Exception',
      error: err
    });
    process.exit(1);
  });
}

module.exports = {
  logAppEvent,
  inferActionAndResource,
  requestLoggerMiddleware,
  installProcessErrorHandlers
};

