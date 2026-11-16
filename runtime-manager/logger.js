const ANSI = {
  reset: '\x1b[0m',
  bold: '\x1b[1m',
  gray: '\x1b[90m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[94m',
  cyan: '\x1b[36m'
};

function formatClock(date = new Date()) {
  const pad = (n) => String(n).padStart(2, '0');
  return `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

function levelColor(level) {
  switch (String(level).toUpperCase()) {
    case 'ERROR':
    case 'CRITICAL':
      return ANSI.red;
    case 'WARNING':
    case 'WARN':
      return ANSI.yellow;
    case 'DEBUG':
      return ANSI.blue;
    case 'SUCCESS':
    case 'RUNNING':
    case 'HEALTHY':
    case 'INFO':
    default:
      return ANSI.green;
  }
}

function logSystem(level, message) {
  const ts = `${ANSI.gray}[${formatClock()}]${ANSI.reset}`;
  const tag = `${ANSI.cyan}[SYSTEM]${ANSI.reset}`;
  const lvl = String(level).toUpperCase();
  const lvlBadge = `${levelColor(lvl)}[${lvl}]${ANSI.reset}`;
  process.stdout.write(`${ts} ${tag} ${lvlBadge} ${message}\n`);
}

function logSection(title) {
  const bar = '============================================================';
  process.stdout.write(`\n${ANSI.cyan}${ANSI.bold}${bar}\n  ${title}\n${bar}${ANSI.reset}\n`);
}

function detectRawLevel(line, stream) {
  const lower = line.toLowerCase();
  if (/\[critical\]/i.test(line)) return 'CRITICAL';
  if (/\[error\]/i.test(line) || /\berror\b|uncaught|fatal error|exception|eaddrinuse|econnrefused|erequest|failed/i.test(lower)) {
    return 'ERROR';
  }
  if (/\[warning\]|\[warn\]/i.test(line) || /\bwarning\b|\bwarn\b|deprecated|unavailable/i.test(lower)) {
    return 'WARNING';
  }
  if (/\b(accepted|closing)\b/i.test(lower) && /\b127\.0\.0\.1:\d+\b/.test(lower)) {
    return 'DEBUG';
  }
  if (stream === 'stderr' && !/development server .* started/i.test(lower)) {
    return 'WARNING';
  }
  return 'INFO';
}

function stripLeadingAppPrefix(line) {
  // Strip our structured prefix `[YYYY-MM-DD HH:mm:ss] [API] [LEVEL]` so we don't duplicate timestamp/level
  const match = line.match(/^\[\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\]\s+\[(?:API|PHP)\]\s+\[(INFO|WARNING|WARN|ERROR|CRITICAL|DEBUG)\]\s+(.*)$/i);
  if (match) {
    return { level: match[1].toUpperCase(), rest: match[2] };
  }
  return null;
}

function logChildLine(tag, rawLine, stream = 'stdout') {
  const trimmed = String(rawLine || '').replace(/\r+$/, '');
  if (!trimmed.trim()) return;

  const parsed = stripLeadingAppPrefix(trimmed);
  const level = parsed ? parsed.level : detectRawLevel(trimmed, stream);
  const message = parsed ? parsed.rest : trimmed;

  const ts = `${ANSI.gray}[${formatClock()}]${ANSI.reset}`;
  const tagColored = `${ANSI.cyan}[${tag}]${ANSI.reset}`;
  const lvlColored = `${levelColor(level)}[${level}]${ANSI.reset}`;

  process.stdout.write(`${ts} ${tagColored} ${lvlColored} ${message}\n`);
}

function formatUptime(startedAt) {
  if (!startedAt) return '-';
  const secs = Math.max(0, Math.floor((Date.now() - startedAt) / 1000));
  if (secs < 60) return `${secs}s`;
  const mins = Math.floor(secs / 60);
  const rem = secs % 60;
  return `${mins}m ${rem}s`;
}

function renderRuntimeStatus(runtimes) {
  logSection('RUNTIME STATUS');
  for (const rt of runtimes) {
    const statusColor = rt.status === 'RUNNING'
      ? ANSI.green
      : rt.status === 'STARTING'
        ? ANSI.yellow
        : ANSI.red;
    const healthColor = rt.health === 'HEALTHY'
      ? ANSI.green
      : rt.health === 'CHECKING'
        ? ANSI.yellow
        : ANSI.red;

    const originNote = rt.reused ? ` ${ANSI.gray}(reused existing instance)${ANSI.reset}` : '';
    process.stdout.write(`${statusColor}[${rt.status}]${ANSI.reset} ${ANSI.bold}${rt.displayName}${ANSI.reset}${originNote}\n`);
    process.stdout.write(`  Runtime : ${ANSI.cyan}${rt.runtimeLabel}${ANSI.reset}\n`);
    process.stdout.write(`  PID     : ${rt.pid || '-'}\n`);
    if (rt.port) {
      process.stdout.write(`  Port    : ${rt.port}\n`);
    }
    if (rt.url) {
      process.stdout.write(`  URL     : ${rt.url}\n`);
    }
    process.stdout.write(`  Health  : ${healthColor}${rt.health}${ANSI.reset}\n`);
    process.stdout.write(`  Uptime  : ${ANSI.gray}${rt.reused ? 'external' : formatUptime(rt.startedAt)}${ANSI.reset}\n\n`);
  }
}

module.exports = {
  ANSI,
  logSystem,
  logSection,
  logChildLine,
  renderRuntimeStatus
};

