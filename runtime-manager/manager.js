const fs = require('fs');
const path = require('path');
const { detectRuntimes } = require('./config');
const { resolveRuntimePort } = require('./port-manager');
const { ProcessManager } = require('./process-manager');
const { logSystem, logSection, renderRuntimeStatus } = require('./logger');

function parseArgs(argv) {
  const args = argv.slice(2);
  const opts = {
    autoAltPort: args.includes('--alt-port'),
    noBrowser: args.includes('--no-browser'),
    nonInteractive: args.includes('--non-interactive'),
    statusOnly: args.includes('--status-only')
  };
  return opts;
}

function validateDependencies(runtimes) {
  let ok = true;
  for (const rt of runtimes) {
    if (rt.requiresPhp && !rt.phpAvailable) {
      logSystem('ERROR', `${rt.displayName}: PHP CLI tidak ditemukan di PATH.`);
      ok = false;
    }
    for (const reqPath of rt.requiredPaths || []) {
      if (!fs.existsSync(reqPath)) {
        logSystem('ERROR', `${rt.displayName}: dependency/config tidak ditemukan (${reqPath}).`);
        ok = false;
      }
    }
  }
  return ok;
}

async function main() {
  const rootDir = path.resolve(__dirname, '..');
  const options = parseArgs(process.argv);
  const pm = new ProcessManager();

  const handleSignal = async (sig) => {
    process.stdout.write('\n');
    logSystem('INFO', `Menerima sinyal ${sig}. Memulai shutdown...`);
    await pm.shutdownAll();
    logSection('SHUTDOWN SUMMARY');
    for (const s of pm.states) {
      logSystem('INFO', `${s.displayName}: ${s.reused ? 'EXTERNAL (TETAP AKTIF)' : 'STOPPED'}`);
    }
    process.exit(0);
  };

  process.on('SIGINT', () => handleSignal('SIGINT'));
  process.on('SIGTERM', () => handleSignal('SIGTERM'));

  logSystem('INFO', 'Initializing DENSO-LMS development environment...');
  logSystem('INFO', `Workspace: ${rootDir}`);
  logSystem('INFO', 'Detecting project runtimes...');

  const runtimes = detectRuntimes(rootDir, options);
  if (runtimes.length === 0) {
    logSystem('ERROR', 'Tidak ada runtime DENSO-LMS yang terdeteksi di workspace.');
    process.exit(1);
  }
  logSystem('INFO', `Terdeteksi ${runtimes.length} runtime: ${runtimes.map((r) => r.displayName).join(', ')}`);

  logSystem('INFO', 'Checking dependencies and configuration...');
  if (!validateDependencies(runtimes)) {
    logSystem('ERROR', 'Validasi dependency gagal. Perbaiki item di atas sebelum menjalankan server.');
    process.exit(1);
  }

  logSystem('INFO', 'Checking ports and existing instances...');
  const plan = [];
  for (const rt of runtimes) {
    const decision = await resolveRuntimePort(rt, options);
    if (decision.action === 'abort') {
      logSystem('WARNING', 'Startup dibatalkan oleh pengguna.');
      process.exit(1);
    }
    plan.push({ runtime: rt, decision });
  }

  if (options.statusOnly) {
    for (const { runtime, decision } of plan) {
      if (decision.action === 'reuse') {
        pm.registerReusedRuntime(runtime, decision.port, decision.owner);
      } else {
        pm.registerSkippedRuntime(runtime, decision.action.toUpperCase());
      }
    }
    renderRuntimeStatus(pm.states);
    process.exit(0);
  }

  logSystem('INFO', 'Starting runtimes and running health checks...');
  for (const { runtime, decision } of plan) {
    if (decision.action === 'reuse') {
      pm.registerReusedRuntime(runtime, decision.port, decision.owner);
    } else if (decision.action === 'start') {
      await pm.startRuntime(runtime, decision.port);
    } else {
      pm.registerSkippedRuntime(runtime, decision.action.toUpperCase());
    }
  }

  renderRuntimeStatus(pm.states);
  logSection('LIVE APPLICATION LOGS');
  logSystem('INFO', 'Streaming real-time logs. Tekan Ctrl+C untuk menghentikan runtime yang dikelola.');

  const hasSpawned = pm.states.some((s) => !s.reused && s.status === 'RUNNING');
  if (!hasSpawned) {
    logSystem('INFO', 'Semua runtime menggunakan instance yang sudah berjalan. Menunggu Ctrl+C...');
    setInterval(() => {}, 60000);
  }
}

main().catch((err) => {
  logSystem('CRITICAL', `Runtime manager error: ${err.message}`);
  process.exit(1);
});

