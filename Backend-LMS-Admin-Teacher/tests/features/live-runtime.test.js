const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

const enabled = process.env.DLS_RUN_LOCAL_SMOKE === '1';
let env;
let jwt;

function setup() {
  if (env) return env;
  require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });
  env = require('../../src/config/env');
  jwt = require('jsonwebtoken');
  assert.notEqual(env.NODE_ENV, 'production', 'live smoke tests only run against development');
  return env;
}

function makeToken(role, sub = `DLS-QA-${role.toUpperCase()}`, extra = {}) {
  const config = setup();
  return jwt.sign({ sub, role, name: `QA ${role}`, ...extra }, config.JWT_SECRET, {
    algorithm: 'HS256', expiresIn: '3m', issuer: config.JWT_ISSUER, audience: config.JWT_AUDIENCE
  });
}

async function apiRequest(pathname, token, options = {}) {
  const config = setup();
  const response = await fetch(`${config.FRONTEND_ORIGIN.replace(/:\d+$/, '')}:${config.PORT}/api/v1${pathname}`, {
    ...options,
    headers: { Accept: 'application/json', ...(token ? { Authorization: `Bearer ${token}` } : {}), ...options.headers }
  });
  const body = await response.json().catch(() => ({}));
  return { status: response.status, body };
}

function assertFast(result, label, maxMs = 2000) {
  assert.ok(result.ms < maxMs, `${label} exceeded ${maxMs}ms: ${result.ms}ms`);
}

async function readSse(pathname, cookie) {
  const config = setup();
  const abort = new AbortController();
  const response = await fetch(`http://127.0.0.1:${config.PORT}/api/v1${pathname}`, {
    headers: { Accept: 'text/event-stream', Cookie: cookie },
    signal: abort.signal
  });
  assert.equal(response.status, 200);
  assert.match(response.headers.get('content-type') || '', /text\/event-stream/);
  const reader = response.body.getReader();
  const timeout = setTimeout(() => abort.abort(), 2000);
  try {
    const { value } = await reader.read();
    assert.match(new TextDecoder().decode(value), /event: ready/);
  } finally {
    clearTimeout(timeout);
    abort.abort();
    await reader.cancel().catch(() => {});
  }
}

test('local service entry points and API health are responsive', { skip: !enabled && 'Set DLS_RUN_LOCAL_SMOKE=1 to enable localhost tests.' }, async () => {
  const config = setup();
  const targets = [
    [`http://127.0.0.1:${config.PORT}/health`, 200],
    [config.FRONTEND_ORIGIN, 200],
    [`${config.REVIEW_PORTAL_URL}/review`, 200],
    [envValueFromStudent('APP_URL'), 200],
    [`${envValueFromStudent('APP_URL')}/?route=/student`, 303]
  ];

  for (const [url, expectedStatus] of targets) {
    const started = performance.now();
    const response = await fetch(url, { redirect: 'manual' });
    const ms = Math.round(performance.now() - started);
    assert.equal(response.status, expectedStatus, url);
    assertFast({ ms }, url);
  }
});

test('live API pages return expected data and role boundaries', { skip: !enabled && 'Set DLS_RUN_LOCAL_SMOKE=1 to enable localhost tests.' }, async t => {
  const admin = makeToken('admin');
  const teacher = makeToken('teacher');
  const reviewer = makeToken('reviewer');
  const student = makeToken('student', 'DLS-QA-STUDENT', { student_id: 1 });

  const anonymous = await apiRequest('/auth/me');
  assert.equal(anonymous.status, 401);

  const adminPaths = [
    '/auth/me', '/dashboard/summary', '/dashboard/courses', '/dashboard/activity', '/courses', '/users',
    '/reviewers', '/grades', '/material-workflow/reviewers', '/material-workflow/trainers',
    '/material-workflow/submissions', '/material-workflow/published', '/forum/management/courses',
    '/notifications', '/notifications/unread-count'
  ];
  for (const pathname of adminPaths) {
    await t.test(`Admin GET ${pathname}`, async () => {
      const started = performance.now();
      const result = await apiRequest(pathname, admin);
      const ms = Math.round(performance.now() - started);
      assert.equal(result.status, 200, JSON.stringify(result.body.error || {}));
      assertFast({ ms }, pathname);
    });
  }

  for (const pathname of ['/dashboard/summary', '/courses', '/grades', '/material-workflow/submissions', '/material-workflow/published', '/forum/management/courses', '/notifications']) {
    const result = await apiRequest(pathname, teacher);
    assert.equal(result.status, 200, `Teacher GET ${pathname}`);
  }
  for (const pathname of ['/users', '/reviewers', '/material-workflow/reviewers', '/material-workflow/trainers']) {
    const result = await apiRequest(pathname, teacher);
    assert.equal(result.status, 403, `Teacher must not access ${pathname}`);
  }

  const reviewerNoScope = await apiRequest('/material-workflow/reviewer/submissions', reviewer);
  assert.equal(reviewerNoScope.status, 403);
  assert.equal(reviewerNoScope.body.error?.code, 'REVIEW_SESSION_REQUIRED');
  assert.equal((await apiRequest('/notifications', student)).status, 200);

  await t.test('notification event stream sends its ready event', async () => {
    const cookieName = setup().SSO_COOKIE_NAME;
    await readSse('/notifications/events', `${cookieName}=${admin}`);
  });
});

test('Student SSO cookie opens every registered page without server errors', { skip: !enabled && 'Set DLS_RUN_LOCAL_SMOKE=1 to enable localhost tests.' }, async () => {
  const config = setup();
  const studentConfig = parseStudentEnv();
  assert.notEqual(studentConfig.APP_ENV, 'production', 'live smoke tests only run against development');
  const studentToken = makeToken('student', 'DLS-QA-STUDENT', { student_id: 1 });
  const appUrl = studentConfig.APP_URL || 'http://127.0.0.1:8082';
  const cookieName = studentConfig.SSO_COOKIE_NAME || 'dls_sso';
  const sessionName = studentConfig.SESSION_NAME || 'DLS_STUDENT';

  const login = await fetch(`${appUrl}/?route=/login`, {
    headers: { Cookie: `${cookieName}=${studentToken}` }, redirect: 'manual'
  });
  assert.equal(login.status, 303, 'PHP consumes the signed SSO cookie and starts a Student session');
  const cookies = login.headers.getSetCookie?.() || [login.headers.get('set-cookie') || ''];
  const sessionCookie = cookies.map(value => value.split(';')[0]).find(value => value.startsWith(`${sessionName}=`));
  assert.ok(sessionCookie, 'PHP session cookie is set');
  const cookie = `${cookieName}=${studentToken}; ${sessionCookie}`;

  const paths = [
    '/student', '/student/courses', '/student/courses/filters', '/student/riwayat', '/student/notifikasi',
    '/student/profil', '/student/profil/detail', '/student/profil/pengaturan', '/student/profil/bantuan',
    '/student/course/detail', '/student/forum/category', '/student/forum/thread', '/student/forum/new',
    '/student/riwayat/detail', '/student/learning', '/student/quiz'
  ];
  let courseHtml = '';
  for (const route of paths) {
    const url = new URL('/', appUrl);
    url.searchParams.set('route', route);
    const started = performance.now();
    const response = await fetch(url, { headers: { Cookie: cookie }, redirect: 'manual' });
    const ms = Math.round(performance.now() - started);
    const body = await response.text();
    assert.ok(response.status < 500, `${route} returned ${response.status}`);
    assertFast({ ms }, route);
    if (route === '/student/courses') courseHtml = body;
  }

  const courseIdMatch = courseHtml.match(/route=\/student\/course\/detail(?:&amp;|&)id=([^&'"\s]+)/);
  if (courseIdMatch) {
    const courseId = decodeURIComponent(courseIdMatch[1]);
    const detailUrl = new URL('/', appUrl);
    detailUrl.searchParams.set('route', '/student/course/detail');
    detailUrl.searchParams.set('id', courseId);
    detailUrl.searchParams.set('tab', 'forum');
    const detail = await fetch(detailUrl, { headers: { Cookie: cookie }, redirect: 'manual' });
    assert.equal(detail.status, 200, 'a course link rendered by Student Course opens its detail page');
    const detailHtml = await detail.text();
    const forumLinkMatch = detailHtml.match(/href="(\?route=\/student\/forum\/category[^"]*)"/);
    assert.ok(forumLinkMatch, 'course detail exposes a session forum link');

    const forumParams = new URLSearchParams(forumLinkMatch[1].replace(/&amp;/g, '&').slice(1));
    const forumCourse = forumParams.get('course_id');
    const forumSession = forumParams.get('session_id');
    assert.ok(forumCourse && forumSession, 'forum link includes course and session context');

    const forumPageUrl = new URL('/', appUrl);
    forumPageUrl.search = new URLSearchParams({
      route: '/student/forum/new', course_id: forumCourse, category_type: 'materi', session_id: forumSession
    }).toString();
    const forumPage = await fetch(forumPageUrl, { headers: { Cookie: cookie }, redirect: 'manual' });
    assert.equal(forumPage.status, 200, 'Student can open the discussion form for a session from Course detail');

    const forumStatus = await apiRequest(`/forum/courses/${encodeURIComponent(forumCourse)}/sessions/${encodeURIComponent(forumSession)}`, studentToken);
    assert.equal(forumStatus.status, 200, JSON.stringify(forumStatus.body.error || {}));
    assert.equal(typeof forumStatus.body.isOpen, 'boolean', 'forum status endpoint returns an explicit open/closed state');

    const forumCreate = await apiRequest(`/forum/courses/${encodeURIComponent(forumCourse)}/sessions/${encodeURIComponent(forumSession)}/threads`, studentToken, {
      method: 'POST', headers: { Origin: appUrl, 'Content-Type': 'application/json' }, body: '{}'
    });
    assert.equal(forumCreate.status, 400, 'discussion endpoint is reachable and rejects an empty form before writes');
    assert.notEqual(forumCreate.body.error?.message, 'Endpoint tidak ditemukan.');
  }
  assert.equal(typeof config.JWT_SECRET, 'string');
});

test('live mutation routes reject missing credentials and invalid role/input before touching data', {
  skip: !enabled && 'Set DLS_RUN_LOCAL_SMOKE=1 to enable localhost tests.'
}, async t => {
  const config = setup();
  const teacher = makeToken('teacher');
  const anonymousWrites = [
    ['/courses', 'POST'],
    ['/notifications', 'POST'],
    ['/forum/management/sessions/DLS-QA-NOOP/1', 'PATCH'],
    ['/material-workflow/sessions/1/submissions', 'POST']
  ];
  for (const [pathname, method] of anonymousWrites) {
    await t.test(`Unauthenticated ${method} ${pathname}`, async () => {
      const response = await apiRequest(pathname, null, { method, headers: { 'Content-Type': 'application/json' }, body: '{}' });
      assert.equal(response.status, 401);
    });
  }

  const forbiddenUserCreate = await apiRequest('/users', teacher, {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: '{}'
  });
  assert.equal(forbiddenUserCreate.status, 403, 'Teacher cannot create staff accounts');

  const forbiddenAudience = await apiRequest('/notifications', teacher, {
    method: 'POST', headers: { Origin: config.FRONTEND_ORIGIN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ title: 'DLS QA', message: 'No data should be written', targetRoles: ['admin'], sendEmail: false })
  });
  assert.equal(forbiddenAudience.status, 403, 'Teacher cannot send notification to admin');

  const invalidNotification = await apiRequest('/notifications', makeToken('admin'), {
    method: 'POST', headers: { Origin: config.FRONTEND_ORIGIN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ title: '', message: '', targetRoles: ['student'], sendEmail: false })
  });
  assert.equal(invalidNotification.status, 400, 'Invalid notification content is rejected before storage');

  const invalidForumStatus = await apiRequest('/forum/management/sessions/DLS-QA-NOOP/1', makeToken('admin'), {
    method: 'PATCH', headers: { Origin: config.FRONTEND_ORIGIN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ isOpen: 'yes' })
  });
  assert.equal(invalidForumStatus.status, 400, 'Forum session setting requires a boolean and does not change state');

  const studentConfig = parseStudentEnv();
  const student = makeToken('student', 'DLS-QA-STUDENT', { student_id: 1 });
  const forumOrigin = studentConfig.APP_URL || config.STUDENT_APP_URL;
  const invalidThread = await apiRequest('/forum/courses/DLS-QA-NOOP/sessions/1/threads', student, {
    method: 'POST', headers: { Origin: forumOrigin, 'Content-Type': 'application/json' }, body: '{}'
  });
  assert.equal(invalidThread.status, 400, 'Student thread creation endpoint is mounted and validates input before database writes');
  assert.notEqual(invalidThread.body.error?.message, 'Endpoint tidak ditemukan.');
});

function parseStudentEnv() {
  const envPath = path.resolve(__dirname, '../../../NATIVE-LMS-Student/.env');
  const values = {};
  if (!fs.existsSync(envPath)) return values;
  for (const line of fs.readFileSync(envPath, 'utf8').split(/\r?\n/)) {
    const match = line.match(/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/);
    if (match) values[match[1]] = match[2].trim().replace(/^['"]|['"]$/g, '');
  }
  return values;
}

function envValueFromStudent(key) {
  const config = parseStudentEnv();
  return key === 'APP_URL' ? (config.APP_URL || 'http://127.0.0.1:8082') : config[key];
}
