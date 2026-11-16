const net = require('net');
const readline = require('readline');
const { execFileSync } = require('child_process');
const { checkHttpHealth } = require('./health-check');
const { logSystem } = require('./logger');

function inspectPort(port) {
  const numericPort = Number(port);
  if (!Number.isInteger(numericPort) || numericPort <= 0) {
    return { inUse: false };
  }

  const psScript = [
    `$conn = Get-NetTCPConnection -LocalPort ${numericPort} -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1`,
    'if (-not $conn) { Write-Output "{}"; exit 0 }',
    '$p = Get-CimInstance Win32_Process -Filter "ProcessId=$($conn.OwningProcess)" -ErrorAction SilentlyContinue',
    '@{ inUse = $true; pid = [int]$conn.OwningProcess; processName = ($p.Name -as [string]); executablePath = ($p.ExecutablePath -as [string]); commandLine = ($p.CommandLine -as [string]) } | ConvertTo-Json -Compress'
  ].join('; ');

  try {
    const raw = execFileSync(
      'powershell.exe',
      ['-NoProfile', '-NonInteractive', '-Command', psScript],
      { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'], timeout: 4000 }
    ).trim();

    if (!raw || raw === '{}') return { inUse: false };
    const parsed = JSON.parse(raw);
    if (!parsed || !parsed.inUse) return { inUse: false };
    return {
      inUse: true,
      pid: parsed.pid || null,
      processName: parsed.processName || 'unknown',
      executablePath: parsed.executablePath || 'unknown',
      commandLine: parsed.commandLine || ''
    };
  } catch {
    return { inUse: false };
  }
}

function isPortBindable(port, host = '127.0.0.1') {
  return new Promise((resolve) => {
    const srv = net.createServer();
    srv.unref();
    srv.on('error', () => resolve(false));
    srv.listen(port, host, () => {
      srv.close(() => resolve(true));
    });
  });
}

async function findNextAvailablePort(startPort, host = '127.0.0.1', maxAttempts = 20) {
  for (let offset = 1; offset <= maxAttempts; offset++) {
    const candidate = startPort + offset;
    const inspected = inspectPort(candidate);
    if (!inspected.inUse && (await isPortBindable(candidate, host))) {
      return candidate;
    }
  }
  return null;
}

function isSameProjectProcess(procInfo, runtime) {
  if (!procInfo || !procInfo.inUse) return false;
  const cmd = String(procInfo.commandLine || '').toLowerCase();
  const exe = String(procInfo.executablePath || '').toLowerCase();
  const tokens = [runtime.cwd, ...(runtime.matchTokens || [])].map((t) => String(t).toLowerCase());

  if (tokens.some((t) => t && cmd.includes(t))) {
    return true;
  }

  // Fallback for Vite / PHP started from the same project when CommandLine is truncated
  if (runtime.id === 'php' && exe.endsWith('php.exe') && cmd.includes(`127.0.0.1:${runtime.port}`)) {
    return true;
  }
  return false;
}

function askChoice(question) {
  return new Promise((resolve) => {
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    rl.question(question, (answer) => {
      rl.close();
      resolve(String(answer || '').trim().toLowerCase());
    });
  });
}

async function resolveRuntimePort(runtime, options = {}) {
  const owner = inspectPort(runtime.port);
  if (!owner.inUse) {
    return { action: 'start', port: runtime.port };
  }

  const sameProject = isSameProjectProcess(owner, runtime);
  if (sameProject) {
    const health = await checkHttpHealth(runtime.host, runtime.port, runtime.healthPath, 2000);
    if (health.healthy) {
      logSystem(
        'INFO',
        `${runtime.displayName} sudah berjalan sehat pada port ${runtime.port} (PID ${owner.pid}, ${owner.processName}). Menggunakan instance yang aktif.`
      );
      return { action: 'reuse', port: runtime.port, owner };
    }

    logSystem(
      'WARNING',
      `Port ${runtime.port} dipakai oleh proses ${runtime.displayName} (PID ${owner.pid}), tetapi health check gagal (${health.error || health.statusCode}).`
    );
  } else {
    logSystem(
      'WARNING',
      `Konflik port ${runtime.port} untuk ${runtime.displayName}: sedang digunakan oleh PID ${owner.pid} (${owner.processName}) [${owner.executablePath}].`
    );
  }

  const altPort = await findNextAvailablePort(runtime.port, runtime.host);
  if (options.autoAltPort && altPort) {
    logSystem('INFO', `Menggunakan port alternatif ${altPort} untuk ${runtime.displayName}.`);
    return { action: 'start', port: altPort, owner };
  }

  if (process.stdin.isTTY && !options.nonInteractive) {
    const altLabel = altPort ? `[A] Pakai port alternatif ${altPort}, ` : '';
    const choice = await askChoice(
      `  -> Pilih aksi untuk ${runtime.displayName} (${altLabel}[S] Lewati runtime ini, [Q] Batalkan): `
    );
    if (choice === 'a' && altPort) {
      logSystem('INFO', `Menggunakan port alternatif ${altPort} untuk ${runtime.displayName}.`);
      return { action: 'start', port: altPort, owner };
    }
    if (choice === 'q') {
      return { action: 'abort', port: runtime.port, owner };
    }
    return { action: 'skip', port: runtime.port, owner };
  }

  logSystem(
    'ERROR',
    `Port ${runtime.port} tidak tersedia (PID ${owner.pid}, ${owner.processName}). Jalankan ulang dengan --alt-port atau bebaskan port tersebut.`
  );
  return { action: 'conflict', port: runtime.port, owner };
}

module.exports = {
  inspectPort,
  isSameProjectProcess,
  findNextAvailablePort,
  resolveRuntimePort
};

