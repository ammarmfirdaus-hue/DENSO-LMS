const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

function readPhpVersion() {
  try {
    const out = execFileSync('php', ['-r', 'echo PHP_VERSION;'], {
      encoding: 'utf8',
      stdio: ['ignore', 'pipe', 'ignore']
    });
    return out.trim() || 'PHP';
  } catch {
    return null;
  }
}

function readNodePackageVersion(pkgPath) {
  try {
    const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
    return pkg.version || '';
  } catch {
    return '';
  }
}

function detectRuntimes(rootDir, cliOptions = {}) {
  const runtimes = [];
  const phpVersion = readPhpVersion();

  const backendDir = path.join(rootDir, 'Backend-LMS-Admin-Teacher');
  if (fs.existsSync(path.join(backendDir, 'src', 'server.js'))) {
    const port = Number(cliOptions.apiPort || process.env.API_PORT || 3001);
    runtimes.push({
      id: 'api',
      tagBase: 'API',
      displayName: 'ADMIN/TEACHER BACKEND',
      runtimeLabel: `Node.js ${process.version} + Express`,
      cwd: backendDir,
      port,
      host: '127.0.0.1',
      healthPath: '/health',
      needsHttp: true,
      command: process.execPath,
      buildArgs: () => ['--watch', 'src/server.js'],
      buildEnv: (p) => ({ ...process.env, PORT: String(p) }),
      matchTokens: ['Backend-LMS-Admin-Teacher', 'src/server.js', 'src\\server.js'],
      requiredPaths: [
        path.join(backendDir, 'node_modules'),
        path.join(backendDir, '.env')
      ]
    });
  }

  const frontendDir = path.join(rootDir, 'Frontend-LMS-Admin-Teacher');
  if (fs.existsSync(path.join(frontendDir, 'package.json'))) {
    const port = Number(cliOptions.webPort || process.env.WEB_PORT || 5173);
    const vitePkg = readNodePackageVersion(path.join(frontendDir, 'node_modules', 'vite', 'package.json'));
    runtimes.push({
      id: 'web',
      tagBase: 'WEB',
      displayName: 'ADMIN/TEACHER FRONTEND',
      runtimeLabel: vitePkg ? `React + Vite v${vitePkg}` : 'React + Vite',
      cwd: frontendDir,
      port,
      host: '127.0.0.1',
      healthPath: '/',
      needsHttp: true,
      command: process.execPath,
      buildArgs: (p) => ['node_modules/vite/bin/vite.js', '--host', '127.0.0.1', '--port', String(p), '--strictPort'],
      buildEnv: () => ({ ...process.env }),
      matchTokens: ['Frontend-LMS-Admin-Teacher', 'vite.js', 'vite'],
      requiredPaths: [
        path.join(frontendDir, 'node_modules'),
        path.join(frontendDir, 'node_modules', 'vite', 'bin', 'vite.js')
      ]
    });
  }

  const studentDir = path.join(rootDir, 'NATIVE-LMS-Student');
  if (fs.existsSync(path.join(studentDir, 'public', 'router.php'))) {
    const port = Number(cliOptions.studentPort || process.env.STUDENT_PORT || 8082);
    runtimes.push({
      id: 'php',
      tagBase: 'PHP',
      displayName: 'STUDENT NATIVE PORTAL',
      runtimeLabel: phpVersion ? `PHP ${phpVersion} Built-in Server` : 'PHP Built-in Server',
      cwd: studentDir,
      port,
      host: '127.0.0.1',
      healthPath: '/',
      needsHttp: true,
      command: 'php',
      buildArgs: (p) => ['-S', `127.0.0.1:${p}`, '-t', 'public', 'public/router.php'],
      buildEnv: () => ({ ...process.env }),
      matchTokens: ['NATIVE-LMS-Student', 'public/router.php', 'public\\router.php'],
      requiredPaths: [
        path.join(studentDir, 'index.php'),
        path.join(studentDir, 'public', 'router.php')
      ],
      requiresPhp: true,
      phpAvailable: Boolean(phpVersion)
    });
  }

  return runtimes;
}

module.exports = {
  detectRuntimes
};

