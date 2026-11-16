const { spawn, execFileSync } = require('child_process');
const { logSystem, logChildLine } = require('./logger');
const { waitForHealthy } = require('./health-check');

const MAX_RESTARTS = 2;
const RESTART_DELAY_MS = 2000;

function attachLineStream(stream, tag, streamName) {
  if (!stream) return;
  let buffer = '';
  stream.setEncoding('utf8');
  stream.on('data', (chunk) => {
    buffer += chunk;
    const lines = buffer.split(/\r?\n/);
    buffer = lines.pop() ?? '';
    for (const line of lines) {
      logChildLine(tag, line, streamName);
    }
  });
  stream.on('end', () => {
    if (buffer.trim()) {
      logChildLine(tag, buffer, streamName);
      buffer = '';
    }
  });
}

class ProcessManager {
  constructor() {
    this.states = [];
    this.shuttingDown = false;
  }

  registerState(runtime, initial) {
    const state = {
      id: runtime.id,
      tag: runtime.port ? `${runtime.tagBase} ${runtime.port}` : runtime.tagBase,
      displayName: runtime.displayName,
      runtimeLabel: runtime.runtimeLabel,
      port: runtime.port,
      url: runtime.needsHttp && runtime.port ? `http://${runtime.host}:${runtime.port}` : null,
      pid: null,
      status: 'STARTING',
      health: 'CHECKING',
      reused: false,
      startedAt: null,
      restartCount: 0,
      child: null,
      runtime,
      ...initial
    };
    this.states.push(state);
    return state;
  }

  async startRuntime(runtime, port) {
    const effectiveRuntime = { ...runtime, port };
    const state = this.registerState(effectiveRuntime, {
      port,
      tag: port ? `${runtime.tagBase} ${port}` : runtime.tagBase,
      url: runtime.needsHttp ? `http://${runtime.host}:${port}` : null,
      status: 'STARTING',
      health: 'CHECKING'
    });

    this.spawnChild(state);

    if (effectiveRuntime.needsHttp) {
      const check = await waitForHealthy(
        effectiveRuntime.host,
        port,
        effectiveRuntime.healthPath,
        20000,
        400,
        () => this.shuttingDown || state.status === 'ERROR' || state.status === 'STOPPED'
      );

      if (check.healthy) {
        state.status = 'RUNNING';
        state.health = 'HEALTHY';
        logSystem('INFO', `${state.displayName} siap di ${state.url} (PID ${state.pid})`);
      } else {
        state.status = 'ERROR';
        state.health = 'UNHEALTHY';
        logSystem('ERROR', `${state.displayName} gagal melewati health check (${check.error || check.statusCode}).`);
      }
    } else if (state.child && !state.child.killed) {
      state.status = 'RUNNING';
      state.health = 'HEALTHY';
    }

    return state;
  }

  registerReusedRuntime(runtime, port, owner) {
    return this.registerState(runtime, {
      port,
      tag: `${runtime.tagBase} ${port}`,
      url: runtime.needsHttp ? `http://${runtime.host}:${port}` : null,
      pid: owner.pid || null,
      status: 'RUNNING',
      health: 'HEALTHY',
      reused: true,
      startedAt: Date.now()
    });
  }

  registerSkippedRuntime(runtime, reason = 'SKIPPED') {
    return this.registerState(runtime, {
      port: runtime.port,
      url: null,
      pid: null,
      status: 'STOPPED',
      health: reason,
      reused: false
    });
  }

  spawnChild(state) {
    const rt = state.runtime;
    const args = rt.buildArgs(state.port);
    const env = rt.buildEnv(state.port);

    const child = spawn(rt.command, args, {
      cwd: rt.cwd,
      env,
      shell: false,
      windowsHide: true,
      stdio: ['ignore', 'pipe', 'pipe']
    });

    state.child = child;
    state.pid = child.pid || null;
    state.startedAt = Date.now();

    attachLineStream(child.stdout, state.tag, 'stdout');
    attachLineStream(child.stderr, state.tag, 'stderr');

    child.on('error', (err) => {
      state.status = 'ERROR';
      state.health = 'UNHEALTHY';
      logSystem('ERROR', `[${state.tag}] Gagal menjalankan proses: ${err.message}`);
    });

    child.on('exit', (code, signal) => {
      const ranMs = state.startedAt ? Date.now() - state.startedAt : 0;
      if (ranMs > 60000) {
        state.restartCount = 0;
      }

      if (this.shuttingDown) {
        state.status = 'STOPPED';
        state.health = 'STOPPED';
        return;
      }

      if (code === 0) {
        state.status = 'STOPPED';
        state.health = 'STOPPED';
        logSystem('WARNING', `[${state.tag}] Proses berhenti normal (exit 0).`);
        return;
      }

      state.status = 'ERROR';
      state.health = 'CRASHED';
      logSystem(
        'ERROR',
        `[${state.tag}] Proses crash (code=${code ?? 'null'}, signal=${signal ?? 'none'}). Runtime lain tetap berjalan.`
      );

      if (state.restartCount < MAX_RESTARTS) {
        state.restartCount += 1;
        logSystem(
          'WARNING',
          `[${state.tag}] Mencoba restart aman (${state.restartCount}/${MAX_RESTARTS}) dalam ${RESTART_DELAY_MS / 1000}s...`
        );
        setTimeout(async () => {
          if (this.shuttingDown) return;
          state.status = 'STARTING';
          state.health = 'CHECKING';
          this.spawnChild(state);
          if (rt.needsHttp) {
            const check = await waitForHealthy(rt.host, state.port, rt.healthPath, 15000, 400, () => this.shuttingDown);
            state.status = check.healthy ? 'RUNNING' : 'ERROR';
            state.health = check.healthy ? 'HEALTHY' : 'UNHEALTHY';
          } else {
            state.status = 'RUNNING';
            state.health = 'HEALTHY';
          }
        }, RESTART_DELAY_MS);
      } else {
        logSystem(
          'ERROR',
          `[${state.tag}] Batas restart tercapai (${MAX_RESTARTS}x). Auto-restart dihentikan untuk mencegah loop.`
        );
      }
    });
  }

  async shutdownAll() {
    if (this.shuttingDown) return;
    this.shuttingDown = true;

    const spawned = this.states.filter((s) => !s.reused && s.child && !s.child.killed && s.child.exitCode === null);
    if (spawned.length === 0) {
      logSystem('INFO', 'Tidak ada proses internal yang perlu dihentikan.');
      return;
    }

    logSystem('INFO', `Menghentikan ${spawned.length} runtime secara graceful...`);
    for (const s of spawned) {
      try {
        s.child.kill('SIGTERM');
      } catch {
        // Ignore if already exited
      }
    }

    const deadline = Date.now() + 2500;
    while (Date.now() < deadline) {
      const stillAlive = spawned.filter((s) => s.child.exitCode === null);
      if (stillAlive.length === 0) break;
      await new Promise((r) => setTimeout(r, 150));
    }

    for (const s of spawned) {
      if (s.child.exitCode === null && s.pid) {
        logSystem('WARNING', `[${s.tag}] Force-terminating PID ${s.pid} beserta child tree...`);
        try {
          execFileSync('taskkill', ['/PID', String(s.pid), '/T', '/F'], { stdio: 'ignore' });
        } catch {
          // Process already exited
        }
      }
      s.status = 'STOPPED';
      s.health = 'STOPPED';
    }

    const reusedCount = this.states.filter((s) => s.reused).length;
    if (reusedCount > 0) {
      logSystem('INFO', `${reusedCount} runtime eksternal (reused) dibiarkan tetap berjalan.`);
    }
    logSystem('INFO', 'Semua runtime yang dikelola telah berhenti bersih.');
  }
}

module.exports = {
  ProcessManager
};

