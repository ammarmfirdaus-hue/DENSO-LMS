const http = require('http');

function checkHttpHealth(host, port, healthPath = '/', timeoutMs = 2000) {
  return new Promise((resolve) => {
    const req = http.get(
      {
        hostname: host,
        port,
        path: healthPath,
        timeout: timeoutMs
      },
      (res) => {
        res.resume();
        const healthy = res.statusCode >= 200 && res.statusCode < 500;
        resolve({ healthy, statusCode: res.statusCode });
      }
    );

    req.on('timeout', () => {
      req.destroy();
      resolve({ healthy: false, error: 'timeout' });
    });

    req.on('error', (err) => {
      resolve({ healthy: false, error: err.code || err.message });
    });
  });
}

async function waitForHealthy(host, port, healthPath = '/', maxWaitMs = 20000, intervalMs = 400, shouldAbort = () => false) {
  const deadline = Date.now() + maxWaitMs;
  while (Date.now() < deadline) {
    if (shouldAbort()) {
      return { healthy: false, error: 'aborted' };
    }
    const res = await checkHttpHealth(host, port, healthPath, 1500);
    if (res.healthy) return res;
    await new Promise((r) => setTimeout(r, intervalMs));
  }
  return { healthy: false, error: 'health check timed out' };
}

module.exports = {
  checkHttpHealth,
  waitForHealthy
};

