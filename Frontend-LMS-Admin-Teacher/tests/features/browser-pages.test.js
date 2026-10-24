import assert from 'node:assert/strict';
import { createHmac } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { createServer } from 'node:net';
import { mkdtempSync, readFileSync, rmSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';

const enabled = process.env.DLS_RUN_BROWSER_SMOKE === '1' && Boolean(process.env.DLS_BROWSER_EXECUTABLE);
const testDirectory = path.dirname(fileURLToPath(import.meta.url));

function readEnvFile(file) {
  const values = {};
  for (const line of readFileSync(file, 'utf8').split(/\r?\n/)) {
    const match = line.match(/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/);
    if (match) values[match[1]] = match[2].trim().replace(/^['"]|['"]$/g, '');
  }
  return values;
}

function makeToken(env, role, extra = {}) {
  const now = Math.floor(Date.now() / 1000);
  const encode = value => Buffer.from(JSON.stringify(value)).toString('base64url');
  const header = encode({ typ: 'JWT', alg: 'HS256' });
  const payload = encode({ sub: `DLS-QA-${role.toUpperCase()}`, npk: `DLS-QA-${role.toUpperCase()}`, role, name: `QA ${role}`, ...extra, iss: env.JWT_ISSUER, aud: env.JWT_AUDIENCE, iat: now, exp: now + 180 });
  const content = `${header}.${payload}`;
  const signature = createHmac('sha256', env.JWT_SECRET).update(content).digest('base64url');
  return `${content}.${signature}`;
}

async function findFreePort() {
  const server = createServer();
  await new Promise((resolve, reject) => server.once('error', reject).listen(0, '127.0.0.1', resolve));
  const port = server.address().port;
  await new Promise(resolve => server.close(resolve));
  return port;
}

async function waitForDevTools(port, browser) {
  const deadline = Date.now() + 10000;
  while (Date.now() < deadline && browser.exitCode === null) {
    try {
      const response = await fetch(`http://127.0.0.1:${port}/json/list`);
      if (response.ok) {
        const targets = await response.json();
        const page = targets.find(target => target.type === 'page' && target.webSocketDebuggerUrl);
        if (page) return page.webSocketDebuggerUrl;
      }
    } catch { /* Wait for the local browser to finish startup. */ }
    await new Promise(resolve => setTimeout(resolve, 100));
  }
  throw new Error('Headless browser did not start its local DevTools endpoint.');
}

function connectDevTools(url) {
  return new Promise((resolve, reject) => {
    const socket = new WebSocket(url);
    const pending = new Map();
    const exceptions = [];
    const apiFailures = [];
    const apiResponses = [];
    let nextId = 0;
    socket.addEventListener('open', () => resolve({
      socket,
      exceptions,
      apiFailures,
      apiResponses,
      call(method, params = {}) {
        const id = ++nextId;
        return new Promise((done, fail) => {
          pending.set(id, { done, fail });
          socket.send(JSON.stringify({ id, method, params }));
        });
      }
    }));
    socket.addEventListener('error', reject, { once: true });
    socket.addEventListener('message', event => {
      const message = JSON.parse(event.data);
      if (message.method === 'Runtime.exceptionThrown') exceptions.push(message.params.exceptionDetails?.text || 'Runtime exception');
      if (message.method === 'Network.responseReceived' && message.params.response.url.includes('/api/v1/')) {
        const { url: responseUrl, status } = message.params.response;
        apiResponses.push({ url: responseUrl, status });
        if (status >= 400) apiFailures.push({ url: responseUrl, status });
      }
      if (!message.id) return;
      const waiter = pending.get(message.id);
      if (!waiter) return;
      pending.delete(message.id);
      if (message.error) waiter.fail(new Error(message.error.message));
      else waiter.done(message.result);
    });
  });
}

async function pageText(devtools) {
  const result = await devtools.call('Runtime.evaluate', { expression: 'document.body?.innerText || ""', returnByValue: true });
  return result.result.value || '';
}

async function waitForChildExit(child, timeoutMs = 2500) {
  if (child.exitCode !== null || child.signalCode !== null) return;
  await Promise.race([
    new Promise(resolve => child.once('exit', resolve)),
    new Promise(resolve => setTimeout(resolve, timeoutMs))
  ]);
}

async function navigateAndWait(devtools, url, expectedText) {
  await devtools.call('Page.navigate', { url });
  const deadline = Date.now() + 15000;
  while (Date.now() < deadline) {
    const text = await pageText(devtools);
    if (text.includes(expectedText)) return text;
    await new Promise(resolve => setTimeout(resolve, 100));
  }
  throw new Error(`Page ${url} did not render expected text: ${expectedText}; body: ${(await pageText(devtools)).slice(0, 300)}; API failures: ${JSON.stringify(devtools.apiFailures.slice(-5))}`);
}

async function navigateInAppAndWait(devtools, url, expectedText) {
  const target = new URL(url);
  const route = `${target.pathname}${target.search}${target.hash}`;
  await devtools.call('Runtime.evaluate', {
    expression: `history.pushState({}, '', ${JSON.stringify(route)}); window.dispatchEvent(new PopStateEvent('popstate'));`,
    returnByValue: true
  });
  const deadline = Date.now() + 10000;
  while (Date.now() < deadline) {
    const text = await pageText(devtools);
    if (text.includes(expectedText)) return text;
    await new Promise(resolve => setTimeout(resolve, 100));
  }
  throw new Error(`SPA route ${route} did not render expected text: ${expectedText}; body: ${(await pageText(devtools)).slice(0, 300)}`);
}

async function navigateAndWaitForDocument(devtools, url) {
  await devtools.call('Page.navigate', { url });
  const deadline = Date.now() + 12000;
  while (Date.now() < deadline) {
    const result = await devtools.call('Runtime.evaluate', {
      expression: '({ text: document.body?.innerText || "", href: location.href })', returnByValue: true
    });
    if (result.result.value.text.trim().length > 0) return result.result.value;
    await new Promise(resolve => setTimeout(resolve, 100));
  }
  throw new Error(`Document ${url} did not render body content.`);
}

test('Admin/Teacher web pages render in a browser and enforce admin-only page guards', {
  skip: !enabled && 'Set DLS_RUN_BROWSER_SMOKE=1 and DLS_BROWSER_EXECUTABLE to enable headless browser tests.'
}, async t => {
  assert.equal(process.platform, 'win32', 'the configured local browser test uses Windows Edge');
  const root = path.resolve(testDirectory, '../../..');
  const apiEnv = readEnvFile(path.join(root, 'Backend-LMS-Admin-Teacher', '.env'));
  const frontendEnv = readEnvFile(path.join(root, 'Frontend-LMS-Admin-Teacher', '.env'));
  assert.notEqual(apiEnv.NODE_ENV, 'production', 'browser smoke tests only run against development');
  const frontendOrigin = frontendEnv.VITE_FRONTEND_ORIGIN || apiEnv.FRONTEND_ORIGIN || 'http://127.0.0.1:5173';
  const apiBase = frontendEnv.VITE_API_BASE_URL || `http://127.0.0.1:${apiEnv.PORT || 3001}/api/v1`;
  const cookieName = apiEnv.SSO_COOKIE_NAME || 'dls_sso';
  const profile = mkdtempSync(path.join(os.tmpdir(), 'dls-lms-browser-smoke-'));
  assert.ok(path.resolve(profile).startsWith(`${path.resolve(os.tmpdir())}${path.sep}`), 'browser profile is isolated under the OS temp directory');
  const port = await findFreePort();
  const browser = spawn(process.env.DLS_BROWSER_EXECUTABLE, [
    '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    '--disable-background-mode',
    `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`, 'about:blank'
  ], { stdio: 'ignore', windowsHide: true });
  let devtools;

  try {
    devtools = await connectDevTools(await waitForDevTools(port, browser));
    await devtools.call('Page.enable');
    await devtools.call('Runtime.enable');
    await devtools.call('Network.enable');
    const cookie = await devtools.call('Network.setCookie', {
      url: `${apiBase}/`, name: cookieName, value: makeToken(apiEnv, 'admin'), path: '/', httpOnly: true, secure: false, sameSite: 'Lax'
    });
    assert.equal(cookie.success, true, 'browser accepts the signed local SSO cookie');

    const pages = [
      ['/', 'Dashboard'], ['/courses', 'Kelola Course'], ['/courses/1', 'Detail Course'],
      ['/materials', 'Materi & Persetujuan'], ['/activity', 'Aktivitas'], ['/grades', 'Kelola Nilai'],
      ['/forum', 'Forum Diskusi'], ['/notifications', 'Notifikasi'], ['/reviewers', 'Kelola Reviewer'], ['/users', 'Manajemen Pengguna']
    ];
    for (const [route, title] of pages) {
      const started = performance.now();
      await navigateAndWait(devtools, new URL(route, frontendOrigin).href, title);
      const elapsed = Math.round(performance.now() - started);
      assert.ok(elapsed < 8000, `${route} rendered in ${elapsed}ms`);
      t.diagnostic(`${route}: ${elapsed}ms`);
    }

    await navigateAndWait(devtools, new URL('/', frontendOrigin).href, 'Dashboard');
    await devtools.call('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    for (const [route, title] of pages) {
      await navigateInAppAndWait(devtools, new URL(route, frontendOrigin).href, title);
      const layout = await devtools.call('Runtime.evaluate', {
        expression: `(() => { const width = document.documentElement.clientWidth; const overflow = Array.from(document.querySelectorAll('body *')).map(el => ({ tag: el.tagName.toLowerCase(), id: el.id, className: typeof el.className === 'string' ? el.className.slice(0, 100) : '', right: Math.round(el.getBoundingClientRect().right), left: Math.round(el.getBoundingClientRect().left) })).filter(el => el.right > width + 1 || el.left < -1).slice(0, 6); return { width, contentWidth: document.documentElement.scrollWidth, overflow }; })()`,
        returnByValue: true
      });
      assert.ok(layout.result.value.contentWidth <= layout.result.value.width + 1,
        `${route} fits a 390px mobile viewport without page-level horizontal overflow (${JSON.stringify(layout.result.value)})`);
    }

    const teacherCookie = await devtools.call('Network.setCookie', {
      url: `${apiBase}/`, name: cookieName, value: makeToken(apiEnv, 'teacher'), path: '/', httpOnly: true, secure: false, sameSite: 'Lax'
    });
    assert.equal(teacherCookie.success, true);
    await navigateAndWait(devtools, new URL('/users', frontendOrigin).href, 'Dashboard');
    const currentUrl = await devtools.call('Runtime.evaluate', { expression: 'location.pathname', returnByValue: true });
    assert.equal(currentUrl.result.value, '/', 'Teacher is redirected away from user management');

    const studentEnv = readEnvFile(path.join(root, 'NATIVE-LMS-Student', '.env'));
    assert.notEqual(studentEnv.APP_ENV, 'production', 'browser smoke tests only run against a local Student environment');
    const studentOrigin = studentEnv.APP_URL || 'http://127.0.0.1:8082';
    const studentCookieName = studentEnv.SSO_COOKIE_NAME || cookieName;
    const studentCookie = await devtools.call('Network.setCookie', {
      url: `${studentOrigin}/`, name: studentCookieName,
      value: makeToken(apiEnv, 'student', { student_id: 1 }), path: '/', httpOnly: true, secure: false, sameSite: 'Lax'
    });
    assert.equal(studentCookie.success, true, 'browser accepts the signed Student SSO cookie');
    const ssoPage = await navigateAndWaitForDocument(devtools, new URL('/?route=/login', studentOrigin).href);
    assert.match(ssoPage.href, /route=%2Fstudent|route=\/student/, 'Student SSO opens the protected portal');

    const studentPages = [
      '/student', '/student/courses', '/student/courses/filters', '/student/riwayat', '/student/notifikasi',
      '/student/profil', '/student/profil/detail', '/student/profil/pengaturan', '/student/profil/bantuan',
      '/student/course/detail', '/student/forum/category', '/student/forum/thread', '/student/forum/new',
      '/student/riwayat/detail', '/student/learning', '/student/quiz'
    ];
    for (const route of studentPages) {
      const pageUrl = new URL('/', studentOrigin);
      pageUrl.searchParams.set('route', route);
      const page = await navigateAndWaitForDocument(devtools, pageUrl.href);
      const layout = await devtools.call('Runtime.evaluate', {
        expression: '({ width: document.documentElement.clientWidth, contentWidth: document.documentElement.scrollWidth })',
        returnByValue: true
      });
      assert.ok(layout.result.value.contentWidth <= layout.result.value.width + 1,
        `${route} fits a 390px Student viewport (${JSON.stringify(layout.result.value)}); rendered ${page.href}`);
    }

    const course = 'DLS-DEMO-QUAL-002';
    const session = '3';
    const categoryUrl = new URL('/', studentOrigin);
    categoryUrl.search = new URLSearchParams({
      route: '/student/forum/category', course_id: course, category_type: 'materi', session_id: session
    }).toString();
    await navigateAndWaitForDocument(devtools, categoryUrl.href);

    const newTopicUrl = new URL('/', studentOrigin);
    newTopicUrl.search = new URLSearchParams({
      route: '/student/forum/new', course_id: course, category_type: 'materi', session_id: session
    }).toString();
    await navigateAndWaitForDocument(devtools, newTopicUrl.href);
    await new Promise(resolve => setTimeout(resolve, 400));
    const forumFormState = await devtools.call('Runtime.evaluate', {
      expression: `({ hasForm: Boolean(document.querySelector('[data-forum-new-form]')), message: document.querySelector('[data-forum-message]')?.textContent?.trim() || '' })`,
      returnByValue: true
    });
    assert.equal(forumFormState.result.value.hasForm, true, 'Student can open the new discussion form from a real session');
    assert.notEqual(forumFormState.result.value.message, 'Endpoint tidak ditemukan.',
      `forum status request does not hit a missing API endpoint: ${JSON.stringify(devtools.apiFailures.slice(-8))}`);
    const expectedForumStatusUrl = `${apiBase}/forum/courses/${encodeURIComponent(course)}/sessions/${encodeURIComponent(session)}`;
    assert.ok(devtools.apiResponses.some(response => response.url === expectedForumStatusUrl && response.status === 200),
      `Student forum status request reaches the configured API: ${JSON.stringify(devtools.apiResponses.filter(response => response.url.includes('/forum/courses/')).slice(-4))}`);
    assert.deepEqual(devtools.exceptions, [], 'pages have no uncaught browser exceptions');
  } finally {
    if (devtools?.socket.readyState === WebSocket.OPEN) {
      try { await devtools.call('Browser.close'); } catch { /* The browser may already be closing. */ }
      devtools.socket.close();
    }
    await waitForChildExit(browser);
    if (devtools?.socket.readyState === WebSocket.OPEN) devtools.socket.close();
    if (browser.pid && browser.exitCode === null && browser.signalCode === null) {
      spawnSync('taskkill.exe', ['/PID', String(browser.pid), '/T', '/F'], { windowsHide: true, stdio: 'ignore' });
      await waitForChildExit(browser);
    }
    if (path.dirname(path.resolve(profile)) !== path.resolve(os.tmpdir()) || !path.basename(profile).startsWith('dls-lms-browser-smoke-')) {
      throw new Error('Refusing to remove a browser profile outside its isolated temporary directory.');
    }
    rmSync(profile, { recursive: true, force: true, maxRetries: 40, retryDelay: 250 });
  }
});
