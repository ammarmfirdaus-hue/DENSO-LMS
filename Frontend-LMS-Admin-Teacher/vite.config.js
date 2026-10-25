import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const apiTarget = env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:3001';
  return {
    plugins: [react(), tailwindcss()],
    server: {
      host: '127.0.0.1',
      port: 5173,
      strictPort: true,
      proxy: {
        '/api': {
          target: apiTarget,
          changeOrigin: false,
          configure(proxy) {
            proxy.on('error', (_error, _request, response) => {
              if (!response || response.headersSent || typeof response.writeHead !== 'function') return;
              const body = JSON.stringify({
                error: {
                  code: 'API_UNAVAILABLE',
                  message: 'Backend API tidak dapat dihubungi. Pastikan Backend LMS berjalan, lalu coba kembali.'
                }
              });
              response.writeHead(503, { 'Content-Type': 'application/json; charset=utf-8' });
              response.end(body);
            });
          }
        },
        '/uploads': { target: apiTarget, changeOrigin: false }
      }
    },
    preview: { host: '127.0.0.1', port: 5173, strictPort: true }
  };
});
